<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Link_management extends CI_Controller {

    public function __construct() {
        parent::__construct();
        
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
        
        if ($this->session->userdata('role') != 'BD') {
            show_error('Access denied. BD only area.', 403);
        }
        
        $this->load->library('Jsm_api');
        $this->load->model(['Campaign_model', 'Product_model', 'Jsm_token_model']);
        $this->load->helper('number');
        $this->load->database();
    }

    public function index() {
        redirect('link_management/dashboard');
    }
    
    // ========== DASHBOARD LINK MANAGEMENT ==========
    public function dashboard() {
        $user_id = $this->session->userdata('user_id');
        
        // ðŸ”¥ AMBIL LINK DENGAN JOIN affiliate_products (untuk shop_name dan image_url)
        $links = $this->db->select('
                l.*, 
                c.campaign_name,
                ap.shop_name,
                ap.image_url,
                ap.open_commission_rate as product_open_commission
            ')
            ->from('bd_affiliate_links l')
            ->join('affiliate_campaigns c', 'c.campaign_id = l.campaign_id', 'left')
            ->join('affiliate_products ap', 'ap.product_id = l.product_id AND ap.campaign_id = l.campaign_id', 'left')
            ->order_by('l.created_at', 'DESC')
            ->get()
            ->result();
        
        // Update statistik dari affiliate_orders
        foreach ($links as $link) {
            $stats = $this->db->select('
                    COUNT(DISTINCT order_id) as total_orders,
                    COALESCE(SUM(gmv), 0) as total_gmv,
                    COALESCE(SUM(estimated_commission), 0) as total_commission
                ')
                ->from('affiliate_orders')
                ->where('product_id', $link->product_id)
                ->where('campaign_id', $link->campaign_id)
                ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
                ->get()
                ->row();
            
            $link->total_orders = $stats->total_orders ?? 0;
            $link->total_gmv = $stats->total_gmv ?? 0;
            $link->total_commission = $stats->total_commission ?? 0;
        }
        
        // Ambil daftar campaign untuk dropdown
        $campaigns = $this->db->select('campaign_id, campaign_name, status')
                              ->from('affiliate_campaigns')
                              ->where('status', 'ONGOING')
                              ->order_by('created_at', 'DESC')
                              ->get()
                              ->result();
        
        $data = [
            'title' => 'Link Management - Toopai BD',
            'active_menu' => 'link_management',
            'links' => $links,
            'campaigns' => $campaigns,
            'total_links' => count($links),
            'total_gmv' => array_sum(array_column($links, 'total_gmv')),
            'total_orders' => array_sum(array_column($links, 'total_orders'))
        ];
        
        $this->load->view('templates/header', $data);
        $this->load->view('bd/link_management', $data);
        $this->load->view('templates/footer');
    }
    public function can_generate_link() {
    $this->output->set_content_type('application/json');
    
    $user_id = $this->session->userdata('user_id');
    $username = $this->session->userdata('username');
    
    // 🔥 HANYA USER ID = 1 (TIFFANY) YANG BISA GENERATE LINK
    $can_generate = ($user_id == 1);
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'can_generate' => $can_generate,
        'user_id' => $user_id,
        'username' => $username,
        'message' => $can_generate ? 'Anda dapat generate link afiliasi' : 'Hanya Head BA yang dapat generate link afiliasi.'
    ]));
}
    // ========== CREATE NEW LINK ==========
    public function create_link() {
    $this->output->set_content_type('application/json');
     $user_id = $this->session->userdata('user_id');
    $username = $this->session->userdata('username');
    
    // 🔥 HANYA USER ID = 1 (TIFFANY) YANG BISA GENERATE LINK
    if ($user_id != 1) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Anda tidak memiliki akses untuk membuat link afiliasi. Hanya Head BA yang dapat generate link.',
            'can_generate' => false
        ]));
    }
    $campaign_id = $this->input->post('campaign_id');
    $product_id = $this->input->post('product_id');
    $product_name = $this->input->post('product_name');
    $commission_rate = $this->input->post('commission_rate');
    $notes = $this->input->post('notes');
    $special_case = $this->input->post('special_case'); // ðŸ”¥ TAMBAHKAN special_case
    
    if (!$campaign_id || !$product_id || !$product_name) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Campaign, Product, and Product Name are required'
        ]));
    }
    
    // ðŸ”¥ VALIDASI: Pastikan product benar-benar berada di campaign yang dipilih
    $product_check = $this->db->select('open_commission_rate, shop_name, image_url')
                              ->where('product_id', $product_id)
                              ->where('campaign_id', $campaign_id)
                              ->get('affiliate_products')
                              ->row();
    
    if (!$product_check) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Product not found in this campaign. Please select a valid product.'
        ]));
    }
    
    // ðŸ”¥ HAPUS VALIDASI "link already exists" - BOLEH MULTIPLE LINKS
    // if ($existing) { ... } â†’ DIHAPUS
    
    // ðŸ”¥ VALIDASI: Pastikan commission rate tidak melebihi batas wajar
    $submitted_commission = floatval($commission_rate);
    
    if ($submitted_commission < 1) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Commission rate minimal 1%'
        ]));
    }
    
    if ($submitted_commission > 50) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Commission rate cannot exceed 50%'
        ]));
    }
    
    try {
        // ðŸ”¥ GENERATE LINK DENGAN COMMISSION RATE YANG DIINPUT USER
        $link_result = $this->jsm_api->generate_promotion_link($campaign_id, $product_id, $submitted_commission);
        
        if (!$link_result['success']) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => $link_result['message'] ?? 'Failed to generate link'
            ]));
        }
        
        $user_id = $this->session->userdata('user_id');
        $username = $this->session->userdata('username');
        
        // Konversi open_commission_rate ke persen
        $openCommissionRaw = floatval($product_check->open_commission_rate ?? 0);
        if ($openCommissionRaw > 20) {
            $openCommissionPercent = $openCommissionRaw / 100;
        } else {
            $openCommissionPercent = $openCommissionRaw;
        }
        
        // ðŸ”¥ BUAT LINK ID UNIK (pakai timestamp + random)
        $link_id = md5($campaign_id . $product_id . time() . rand(1000, 9999));
        
        $data = [
            'link_id' => $link_id,
            'campaign_id' => $campaign_id,
            'product_id' => $product_id,
            'product_name' => $product_name,
            'affiliate_link' => $link_result['link'],
            'commission_rate' => $submitted_commission,
            'open_commission_rate' => $openCommissionPercent,
            'created_by' => $user_id,
            'created_by_name' => $username,
            'status' => 'ACTIVE',
            'expire_at' => $link_result['expire_at'] ?? null,
            'notes' => $notes,
            'special_case' => $special_case ? 1 : 0,  // ðŸ”¥ TAMBAHKAN special_case flag
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('bd_affiliate_links', $data);
        $insert_id = $this->db->insert_id();
        
        $this->load->model('User_log_model');
        $this->User_log_model->log(
            $user_id,
            $username,
            'BD',
            'CREATE_AFFILIATE_LINK',
            "Created affiliate link for product: $product_name (Campaign: $campaign_id) with commission $submitted_commission% (Open: $openCommissionPercent%)" . ($special_case ? ' [SPECIAL CASE]' : '')
        );
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'message' => 'Link created successfully',
            'link_id' => $insert_id,
            'link' => $link_result['link'],
            'commission_rate' => $submitted_commission,
            'open_commission_rate' => $openCommissionPercent
        ]));
        
    } catch (Exception $e) {
        log_message('error', 'Error in create_link: ' . $e->getMessage());
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]));
    }
}

    
    // ========== UPDATE LINK COMMISSION ==========
    public function update_link() {
        $this->output->set_content_type('application/json');
        
        $link_id = $this->input->post('link_id');
        $commission_rate = $this->input->post('commission_rate');
        $status = $this->input->post('status');
        $notes = $this->input->post('notes');
        
        if (!$link_id) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Link ID required'
            ]));
        }
        
        $update_data = [];
        if ($commission_rate !== null) $update_data['commission_rate'] = $commission_rate;
        if ($status !== null) $update_data['status'] = $status;
        if ($notes !== null) $update_data['notes'] = $notes;
        $update_data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->where('id', $link_id)->update('bd_affiliate_links', $update_data);
        
        // Log activity
        $this->load->model('User_log_model');
        $this->User_log_model->log(
            $this->session->userdata('user_id'),
            $this->session->userdata('username'),
            'BD',
            'UPDATE_AFFILIATE_LINK',
            "Updated affiliate link ID: $link_id"
        );
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'message' => 'Link updated successfully'
        ]));
    }
    
    // ========== DELETE/DISABLE LINK ==========
    public function delete_link() {
        $this->output->set_content_type('application/json');
        
        $link_id = $this->input->post('link_id');
        
        if (!$link_id) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Link ID required'
            ]));
        }
        
        // Soft delete - set status to INACTIVE
        $this->db->where('id', $link_id)->update('bd_affiliate_links', [
            'status' => 'INACTIVE',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        // Log activity
        $this->load->model('User_log_model');
        $this->User_log_model->log(
            $this->session->userdata('user_id'),
            $this->session->userdata('username'),
            'BD',
            'DELETE_AFFILIATE_LINK',
            "Disabled affiliate link ID: $link_id"
        );
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'message' => 'Link disabled successfully'
        ]));
    }
    
    // ========== SEARCH PRODUCT ==========
    public function search_product() {
        $this->output->set_content_type('application/json');
        
        $keyword = $this->input->post('keyword');
        $campaign_id = $this->input->post('campaign_id');
        $search_type = $this->input->post('search_type') ?: 'shop'; // shop or product_id
        
        if (!$keyword) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'products' => []
            ]));
        }
        
        $this->db->select('product_id, product_name, price, commission_rate, open_commission_rate, shop_name, image_url, review_status')
                 ->from('affiliate_products')
                 ->where('review_status', 'APPROVED');
        
        // Filter berdasarkan campaign_id
        if (!empty($campaign_id)) {
            $this->db->where('campaign_id', $campaign_id);
        }
        
        // Search berdasarkan tipe
        if ($search_type === 'product_id') {
            $this->db->where('product_id', $keyword);
        } else {
            // Search by shop_name atau product_name
            $this->db->group_start()
                     ->like('shop_name', $keyword)
                     ->or_like('product_name', $keyword)
                     ->group_end();
        }
        
        $products = $this->db->limit(20)
                             ->order_by('sales_count', 'DESC')
                             ->get()
                             ->result();
        
        // Format response
        $formatted_products = [];
        foreach ($products as $p) {
            $formatted_products[] = [
                'product_id' => $p->product_id,
                'product_name' => $p->product_name,
                'price' => floatval($p->price),
                'commission_rate' => floatval($p->commission_rate),
                'open_commission_rate' => floatval($p->open_commission_rate),
                'shop_name' => $p->shop_name,
                'image_url' => $p->image_url
            ];
        }
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'products' => $formatted_products
        ]));
    }
    
    // ========== GET LINK STATISTICS ==========
    public function get_link_stats() {
        $this->output->set_content_type('application/json');
        
        $link_id = $this->input->post('link_id');
        
        if (!$link_id) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Link ID required'
            ]));
        }
        
        // ðŸ”¥ Ambil link dengan JOIN affiliate_products (untuk shop_name dan image_url)
        $link = $this->db->select('
                l.*, 
                c.campaign_name,
                ap.shop_name,
                ap.image_url
            ')
            ->from('bd_affiliate_links l')
            ->join('affiliate_campaigns c', 'c.campaign_id = l.campaign_id', 'left')
            ->join('affiliate_products ap', 'ap.product_id = l.product_id AND ap.campaign_id = l.campaign_id', 'left')
            ->where('l.id', $link_id)
            ->get()
            ->row();
        
        if (!$link) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Link not found'
            ]));
        }
        
        // Ambil statistik dari affiliate_orders
        $stats = $this->db->select('
                COUNT(DISTINCT order_id) as total_orders,
                COALESCE(SUM(gmv), 0) as total_gmv,
                COALESCE(SUM(estimated_commission), 0) as total_commission,
                COUNT(DISTINCT creator_username) as total_creators
            ')
            ->from('affiliate_orders')
            ->where('product_id', $link->product_id)
            ->where('campaign_id', $link->campaign_id)
            ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->get()
            ->row();
        
        // Ambil performa per hari
        $daily_stats = $this->db->select('
                DATE(order_time) as date,
                COUNT(DISTINCT order_id) as daily_orders,
                SUM(gmv) as daily_gmv
            ')
            ->from('affiliate_orders')
            ->where('product_id', $link->product_id)
            ->where('campaign_id', $link->campaign_id)
            ->where('order_time >=', date('Y-m-d H:i:s', strtotime('-30 days')))
            ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->group_by('DATE(order_time)')
            ->order_by('date', 'DESC')
            ->get()
            ->result();
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'link' => $link,
            'stats' => $stats,
            'daily_stats' => $daily_stats
        ]));
    }
}