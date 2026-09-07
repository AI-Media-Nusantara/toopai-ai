<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Showcase_checker extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('Fastmoss_model');
    }

    /**
     * Check showcase for a single link ID (AJAX)
     */
    public function check_single() {
        $this->output->set_content_type('application/json');

        // Check auth
        if (!$this->session->userdata('logged_in')) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Unauthorized'
            ]));
        }

        $link_id = $this->input->post('link_id');
        if (empty($link_id)) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Link ID is required'
            ]));
        }

        $result = $this->process_check($link_id);
        return $this->output->set_output(json_encode($result));
    }

    /**
     * CLI/Cron entry to check all pending active links (showcase_status = 'unknown' or checked > 24 hours ago)
     */
    public function check_all_pending() {
        // Only allow CLI or admin session
        if (!is_cli() && !$this->session->userdata('logged_in')) {
            show_error('Access denied.', 403);
        }

        // Find active links where status is ACTIVE and showcase_status is 'unknown' or checked_at is older than 24 hours
        $one_day_ago = date('Y-m-d H:i:s', strtotime('-24 hours'));
        $links = $this->db->select('id, link_id, creator_username')
            ->from('affiliate_creator_links')
            ->where('status', 'ACTIVE')
            ->group_start()
                ->where('showcase_status', 'unknown')
                ->or_where('showcase_checked_at <', $one_day_ago)
                ->or_where('showcase_checked_at IS NULL')
            ->group_end()
            ->order_by('showcase_checked_at', 'ASC')
            ->limit(20) // process in batches of 20 to avoid rate limits
            ->get()
            ->result();

        if (is_cli()) {
            echo "Found " . count($links) . " links to check.\n";
        }

        $success_count = 0;
        foreach ($links as $link) {
            if (is_cli()) {
                echo "Checking link ID: {$link->id} (creator: {$link->creator_username})... ";
            }
            $res = $this->process_check($link->id);
            if ($res['success']) {
                if (is_cli()) {
                    echo "SUCCESS: Status is now {$res['showcase_status']}\n";
                }
                $success_count++;
            } else {
                if (is_cli()) {
                    echo "FAILED: {$res['message']}\n";
                }
            }
            sleep(1); // sleep to respect rate limits
        }

        if (is_cli()) {
            echo "Showcase check completed. Processed: " . count($links) . ", Succeeded: {$success_count}\n";
        } else {
            $this->output->set_content_type('application/json');
            return $this->output->set_output(json_encode([
                'success' => true,
                'message' => "Showcase check completed. Processed: " . count($links) . ", Succeeded: {$success_count}"
            ]));
        }
    }

    /**
     * Core logic to check and update showcase status for a specific link ID (primary key `id` of affiliate_creator_links)
     */
    private function process_check($link_pk_id) {
        // 1. Get creator assignment link row
        $link = $this->db->get_where('affiliate_creator_links', ['id' => $link_pk_id])->row();
        if (!$link) {
            return ['success' => false, 'message' => 'Link not found'];
        }

        // 2. Get creator open ID (FastMoss UID)
        $creator = $this->db->get_where('creators', ['username' => $link->creator_username])->row();
        if (!$creator) {
            // Try matching by creator_id if username failed
            $creator = $this->db->get_where('creators', ['id' => $link->creator_id])->row();
        }

        if (!$creator) {
            return ['success' => false, 'message' => 'Creator profile not found in database'];
        }

        $fastmoss_uid = $creator->tiktok_open_id ?? '';

        // Auto-resolve UID using FastMoss if empty
        if (empty($fastmoss_uid)) {
            $fastmoss_uid = $this->Fastmoss_model->resolve_uid_by_username($link->creator_username);
            if (!empty($fastmoss_uid)) {
                $this->db->where('id', $creator->id)->update('creators', [
                    'tiktok_open_id' => $fastmoss_uid,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                return ['success' => false, 'message' => 'Unable to resolve FastMoss UID for creator username ' . $link->creator_username];
            }
        }

        // 3. Compile all target product IDs to check
        // Check if there is a main product ID
        $target_product_ids = [];
        if (!empty($link->product_id)) {
            $target_product_ids[] = $link->product_id;
        }

        // Also query bd_palette_products matching the link_id (in case of a multi product palette link)
        if (!empty($link->link_id)) {
            $palette_products = $this->db->select('product_id')
                ->get_where('bd_palette_products', ['link_id' => $link->link_id])
                ->result();
            foreach ($palette_products as $p) {
                if (!empty($p->product_id)) {
                    $target_product_ids[] = $p->product_id;
                }
            }
        }

        // Remove duplicates and empty values
        $target_product_ids = array_unique(array_filter($target_product_ids));

        if (empty($target_product_ids)) {
            return ['success' => false, 'message' => 'No target products found for this link'];
        }

        // 4. Fetch showcase products from FastMoss
        // Fetch up to 2 pages to search
        $showcase_products = [];
        for ($page = 1; $page <= 2; $page++) {
            $products = $this->Fastmoss_model->get_creator_products_by_uid($fastmoss_uid, $page, 50);
            if (empty($products)) {
                break;
            }
            $showcase_products = array_merge($showcase_products, $products);
            if (count($products) < 50) {
                break;
            }
        }

        // 5. Check if any target product exists in showcase
        $found = false;
        $matched_product_id = null;

        // Compile showcase product IDs
        $showcase_product_ids = [];
        foreach ($showcase_products as $sp) {
            $sp_id = $sp['goods_id'] ?? $sp['product_id'] ?? null;
            if (!empty($sp_id)) {
                $showcase_product_ids[] = strval($sp_id);
            }
        }

        foreach ($target_product_ids as $t_id) {
            if (in_array(strval($t_id), $showcase_product_ids)) {
                $found = true;
                $matched_product_id = $t_id;
                break;
            }
        }

        // 6. Update showcase status in database
        $showcase_status = $found ? 'added' : 'not_added';
        $update_data = [
            'showcase_status' => $showcase_status,
            'showcase_checked_at' => date('Y-m-d H:i:s'),
            'tiktok_product_id' => $matched_product_id
        ];
        
        $this->db->where('id', $link_pk_id)->update('affiliate_creator_links', $update_data);

        return [
            'success' => true,
            'showcase_status' => $showcase_status,
            'matched_product_id' => $matched_product_id,
            'checked_at' => $update_data['showcase_checked_at']
        ];
    }

    /**
     * Check showcase for a specific link ID via CLI
     */
    public function check_cli_single($link_id) {
        if (!is_cli()) {
            show_error('Access denied.', 403);
        }
        $res = $this->process_check($link_id);
        print_r($res);
    }
}
