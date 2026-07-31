<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();
        
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
        
        $this->load->library('Jsm_api');
        $this->load->model(['Campaign_model', 'Brand_model', 'Product_model', 'User_model', 'Jsm_token_model', 'Creator_model', 'Affiliate_sync_model']);
        $this->load->helper('number');
        
        // Set timezone Indonesia
        date_default_timezone_set('Asia/Jakarta');
    }

    public function index() {
        $role = $this->session->userdata('role');
        
        if ($role == 'BD') {
            redirect('bd/dashboard');
        } elseif ($role == 'IS') {
            redirect('is/dashboard');
        } else {
            $this->admin_dashboard();
        }
    }

    private function admin_dashboard() {
        // Get realtime data from sync tables
        $realtime_data = $this->get_realtime_stats();
        $bd_performance = $this->get_bd_performance();
    $ca_performance = $this->get_ca_performance();
        $data = [
            'title' => 'Admin Dashboard - Toopai',
            'active_menu' => 'dashboard',
            'total_campaigns' => count($this->Campaign_model->get_all_campaigns()),
            'total_brands' => count($this->Brand_model->get_all_brands()),
            'total_creators' => count($this->Creator_model->get_all_creators()),
            'bd_users' => $this->User_model->get_all_bd(),
            'is_users' => $this->User_model->get_all_is(),
            // Realtime TikTok Data
            'realtime' => $realtime_data,
             'bd_performance' => $bd_performance,
        'ca_performance' => $ca_performance
        ];
        
        $this->load->view('templates/new/header', $data);
        $this->load->view('admin/dashboard', $data);
        $this->load->view('templates/new/footer');
    }
    
    
private function get_realtime_stats() {
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $current_time = date('H:i:s');
    
    // ðŸ”¥ TODAY FULL DAY (ALL orders) - untuk tampilan
    $today_full = $this->db->select('
            COALESCE(SUM(gmv), 0) as total_gmv,
            COUNT(DISTINCT order_id) as total_orders,
            COALESCE(SUM(estimated_commission), 0) as total_estimated_commission,
            COALESCE(SUM(actual_commission), 0) as total_actual_commission,
            COUNT(DISTINCT creator_username) as total_creators
        ')
        ->from('affiliate_orders')
        ->where('order_date_local', $today)
        ->group_start()
            ->where_not_in('order_status', ['CANCELLED', 'REFUNDED'])
            ->or_where('order_status IS NULL')
        ->group_end()
        ->get()
        ->row();
    
    // ðŸ”¥ YESTERDAY FULL DAY (ALL orders) - untuk tampilan
    $yesterday_full = $this->db->select('
            COALESCE(SUM(gmv), 0) as total_gmv,
            COUNT(DISTINCT order_id) as total_orders,
            COALESCE(SUM(estimated_commission), 0) as total_estimated_commission,
            COALESCE(SUM(actual_commission), 0) as total_actual_commission,
            COUNT(DISTINCT creator_username) as total_creators
        ')
        ->from('affiliate_orders')
        ->where('order_date_local', $yesterday)
        ->group_start()
            ->where_not_in('order_status', ['CANCELLED', 'REFUNDED'])
            ->or_where('order_status IS NULL')
        ->group_end()
        ->get()
        ->row();
    
    // ðŸ”¥ GROWTH: hour-to-hour (today 00:00-sekarang vs yesterday 00:00-sekarang)
    $gmv_comparison = $this->Affiliate_sync_model->get_gmv_comparison();
    
    // ðŸ”¥ Yesterday FULL DAY metrics (untuk view row 2)
    $yesterday_full_metrics = $this->Affiliate_sync_model->get_brand_creator_metrics($yesterday, '23:59:59');
    
    // ðŸ”¥ TODAY metrics (hour-to-now untuk growth row 2)
    $today_metrics = $this->Affiliate_sync_model->get_brand_creator_metrics($today, $current_time);
    
    // ðŸ”¥ SETTLED STATS
    $settled_today = $this->Affiliate_sync_model->get_real_gmv($today, 'settled');
    
    // ðŸ”¥ Top creators 30 days
    $top_creators = $this->Affiliate_sync_model->get_top_creators_30_days(10);
    
    // ðŸ”¥ Recent orders
    $recent_orders = $this->Affiliate_sync_model->get_recent_orders_valid(20);
    
    // ðŸ”¥ Campaign summary
    $campaign_summary = $this->Affiliate_sync_model->get_campaign_summary(null, $today);
    
    // ðŸ”¥ Active creators
    $active_creators = $this->get_active_creators_from_orders();
    
    // ðŸ”¥ GMV breakdown 30 hari
    $gmv_breakdown = $this->Affiliate_sync_model->get_gmv_breakdown(30);
    
    // ðŸ”¥ Last sync
    $last_sync_result = $this->db->select('MAX(end_time) as last_sync')
                                 ->from('affiliate_sync_logs')
                                 ->where('status', 'success')
                                 ->get()
                                 ->row();
    
    // ðŸ”¥ Queue pending
    $queue_pending = $this->db->where('status', 'pending')
                               ->count_all_results('affiliate_sync_queue');
    
    // ðŸ”¥ Top brands
    $top_brands = $this->Affiliate_sync_model->get_top_brands_today(10);
    
    // ðŸ”¥ GROWTH dari get_gmv_comparison (HOUR-TO-HOUR)
    $gmv_growth = $gmv_comparison['growth_percent'];
    $order_growth = $gmv_comparison['yesterday']['orders'] > 0 
        ? round((($gmv_comparison['today']['orders'] - $gmv_comparison['yesterday']['orders']) / $gmv_comparison['yesterday']['orders']) * 100, 1) 
        : ($gmv_comparison['today']['orders'] > 0 ? 100 : 0);
    $commission_growth = $gmv_comparison['yesterday']['estimated_commission'] > 0 
        ? round((($gmv_comparison['today']['estimated_commission'] - $gmv_comparison['yesterday']['estimated_commission']) / $gmv_comparison['yesterday']['estimated_commission']) * 100, 1) 
        : ($gmv_comparison['today']['estimated_commission'] > 0 ? 100 : 0);
    $creator_growth = $gmv_comparison['yesterday']['creators'] > 0 
        ? round((($gmv_comparison['today']['creators'] - $gmv_comparison['yesterday']['creators']) / $gmv_comparison['yesterday']['creators']) * 100, 1) 
        : ($gmv_comparison['today']['creators'] > 0 ? 100 : 0);
    
    // ðŸ”¥ GROWTH Row 2 (hour-to-hour)
    $yesterday_metrics_growth = $this->Affiliate_sync_model->get_brand_creator_metrics($yesterday, $current_time);
    
    $brand_growth = $yesterday_metrics_growth->brands_joined > 0 
        ? round((($today_metrics->brands_joined - $yesterday_metrics_growth->brands_joined) / $yesterday_metrics_growth->brands_joined) * 100, 1) 
        : ($today_metrics->brands_joined > 0 ? 100 : 0);
    
    $links_growth = $yesterday_metrics_growth->creators_with_links > 0 
        ? round((($today_metrics->creators_with_links - $yesterday_metrics_growth->creators_with_links) / $yesterday_metrics_growth->creators_with_links) * 100, 1) 
        : ($today_metrics->creators_with_links > 0 ? 100 : 0);
    
    $activated_growth = $yesterday_metrics_growth->creators_activated > 0 
        ? round((($today_metrics->creators_activated - $yesterday_metrics_growth->creators_activated) / $yesterday_metrics_growth->creators_activated) * 100, 1) 
        : ($today_metrics->creators_activated > 0 ? 100 : 0);
    
    $content_growth = $yesterday_metrics_growth->creators_with_content > 0 
        ? round((($today_metrics->creators_with_content - $yesterday_metrics_growth->creators_with_content) / $yesterday_metrics_growth->creators_with_content) * 100, 1) 
        : ($today_metrics->creators_with_content > 0 ? 100 : 0);
    
    // Format campaigns
    $formatted_campaigns = [];
    foreach ($campaign_summary as $camp) {
        $formatted_campaigns[] = (object)[
            'campaign_id' => $camp->campaign_id ?? '',
            'campaign_name' => $camp->campaign_name ?? 'Unknown',
            'status' => $camp->status ?? 'ONGOING',
            'actual_gmv' => $camp->actual_gmv ?? 0,
            'actual_orders' => $camp->actual_orders ?? 0,
            'actual_creators' => $camp->actual_creators ?? 0,
            'total_creators' => $camp->actual_creators ?? 0,
            'last_sync' => $camp->campaign_last_sync ?? $camp->last_sync ?? null
        ];
    }
    
    return [
        // ðŸ”¥ TAMPILAN: FULL DAY
        'today_gmv' => floatval($today_full->total_gmv ?? 0),
        'today_orders' => intval($today_full->total_orders ?? 0),
        'today_estimated_commission' => floatval($today_full->total_estimated_commission ?? 0),
        'today_actual_commission' => floatval($today_full->total_actual_commission ?? 0),
        'today_creators' => intval($today_full->total_creators ?? 0),
        
        'yesterday_gmv' => floatval($yesterday_full->total_gmv ?? 0),
        'yesterday_orders' => intval($yesterday_full->total_orders ?? 0),
        'yesterday_estimated_commission' => floatval($yesterday_full->total_estimated_commission ?? 0),
        'yesterday_actual_commission' => floatval($yesterday_full->total_actual_commission ?? 0),
        'yesterday_creators' => intval($yesterday_full->total_creators ?? 0),
        
        // ðŸ”¥ GROWTH: HOUR-TO-HOUR
        'gmv_growth' => $gmv_growth,
        'order_growth' => $order_growth,
        'commission_growth' => $commission_growth,
        'creator_growth' => $creator_growth,
        
        'settled_gmv' => floatval($settled_today->total_gmv ?? 0),
        'settled_commission' => floatval($settled_today->total_actual_commission ?? 0),
        
        'top_creators' => $top_creators,
        'top_brands' => $top_brands,
        'recent_orders' => $recent_orders,
        
        'active_campaigns' => count($campaign_summary),
        'campaigns' => $formatted_campaigns,
        
        'brands_joined_today' => $today_metrics->brands_joined,
        'creators_with_links_today' => $today_metrics->creators_with_links,
        'creators_activated_today' => $today_metrics->creators_activated,
        'creators_with_content_today' => $today_metrics->creators_with_content,
        'total_contents_today' => $today_metrics->total_contents,
        'total_views_today' => $today_metrics->total_views,
        
        'brands_joined_yesterday' => $yesterday_full_metrics->brands_joined,
        'creators_with_links_yesterday' => $yesterday_full_metrics->creators_with_links,
        'creators_activated_yesterday' => $yesterday_full_metrics->creators_activated,
        'creators_with_content_yesterday' => $yesterday_full_metrics->creators_with_content,
        
        'brand_growth' => $brand_growth,
        'links_growth' => $links_growth,
        'activated_growth' => $activated_growth,
        'content_growth' => $content_growth,
        
        'active_creators' => intval($today_full->total_creators ?? 0),
        'gmv_breakdown' => $gmv_breakdown,
        'last_sync' => $last_sync_result->last_sync ?? null,
        'queue_pending' => $queue_pending,
        'server_time' => date('Y-m-d H:i:s'),
        
        'total_gmv' => floatval($today_full->total_gmv ?? 0),
        'total_orders' => intval($today_full->total_orders ?? 0),
        'total_estimated_commission' => floatval($today_full->total_estimated_commission ?? 0),
    ];
}
private function get_active_creators_from_orders() {
    $sql = "
        SELECT 
            o.creator_username,
            COUNT(DISTINCT o.campaign_id) as active_campaigns,
            COUNT(DISTINCT o.product_id) as total_products,
            COUNT(*) as total_orders,
            SUM(o.gmv) as total_gmv,
            SUM(o.estimated_commission) as total_estimated_commission,
            SUM(o.actual_commission) as total_actual_commission,
            MAX(o.order_time) as last_active
        FROM affiliate_orders o
        WHERE DATE(o.order_time) = CURDATE()
            AND o.creator_username IS NOT NULL 
            AND o.creator_username != ''
            AND o.order_status IN ('SETTLED', 'PENDING', 'PROCESSING')
        GROUP BY o.creator_username
        ORDER BY total_gmv DESC
        LIMIT 10
    ";
    
    $result = $this->db->query($sql)->result();
    
    $formatted = [];
    foreach ($result as $item) {
        $formatted[] = (object)[
            'creator_username' => $item->creator_username,
            'total_gmv' => $item->total_gmv,
            'total_orders' => $item->total_orders,
            'total_estimated_commission' => $item->total_estimated_commission,
            'total_actual_commission' => $item->total_actual_commission,
            'last_active' => $item->last_active
        ];
    }
    
    return $formatted;
}
    /**
     * AJAX endpoint for realtime data refresh
     */
    public function ajax_realtime_data() {
        $this->output->set_content_type('application/json');
        
        $data = $this->get_realtime_stats();
        $data['success'] = true;
        
        return $this->output->set_output(json_encode($data));
    }
    
    /**
     * AJAX endpoint for campaign detail
     */
    public function ajax_campaign_detail($campaign_id) {
    $this->output->set_content_type('application/json');
    
    try {
        // Validasi campaign_id
        if (empty($campaign_id)) {
            echo json_encode(['success' => false, 'message' => 'Campaign ID is required']);
            return;
        }
        
        // Ambil data campaign detail
        $detail = $this->Affiliate_sync_model->get_campaign_detail($campaign_id);
        
        if (!$detail || !$detail->campaign) {
            echo json_encode(['success' => false, 'message' => 'Campaign not found']);
            return;
        }
        
        // Format response dengan aman
        $response = [
            'success' => true,
            'campaign' => [
                'campaign_id' => $detail->campaign->campaign_id ?? '',
                'campaign_name' => $detail->campaign->campaign_name ?? 'Unknown',
                'status' => $detail->campaign->status ?? 'ONGOING',
                'total_gmv' => floatval($detail->campaign->total_gmv ?? 0),
                'total_orders' => intval($detail->campaign->total_orders ?? 0),
                'total_creators' => intval($detail->campaign->total_creators ?? 0),
                'last_sync' => $detail->campaign->last_sync ?? null
            ],
            'products' => [],
            'top_creators' => []
        ];
        
        // Format products
        if (!empty($detail->products)) {
            foreach ($detail->products as $product) {
                $response['products'][] = [
                    'product_id' => $product->product_id ?? '',
                    'product_name' => $product->product_name ?? 'Unknown Product',
                    'price' => floatval($product->price ?? 0),
                    'commission_rate' => floatval($product->commission_rate ?? 0),
                    'sales_count' => intval($product->sales_count ?? 0),
                    'gmv' => floatval($product->gmv ?? 0),
                    'image_url' => $product->image_url ?? ''
                ];
            }
        }
        
        // Format top creators
        if (!empty($detail->top_creators)) {
            foreach ($detail->top_creators as $creator) {
                $response['top_creators'][] = [
                    'creator_username' => $creator->creator_username ?? 'Unknown',
                    'total_orders' => intval($creator->total_orders ?? 0),
                    'total_gmv' => floatval($creator->total_gmv ?? 0),
                    'total_commission' => floatval($creator->total_commission ?? 0)
                ];
            }
        }
        
        // Kirim response
        echo json_encode($response);
        
    } catch (Exception $e) {
        log_message('error', 'Error in ajax_campaign_detail: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to load campaign details: ' . $e->getMessage()
        ]);
    }
}
    
    /**
     * Trigger manual sync
     */
    public function trigger_sync() {
        if (!$this->input->is_cli_request() && $this->session->userdata('role') !== 'admin') {
            show_error('Access denied');
        }
        
        $this->load->library('Cron');
        
        $type = $this->input->post('type') ?? 'all';
        
        ob_start();
        $this->cron->sync_all();
        $output = ob_get_clean();
        
        echo json_encode([
            'success' => true,
            'message' => 'Sync triggered',
            'output' => $output
        ]);
    }

    // ========== Existing AJAX Endpoints ==========
    
    public function scout_match_brand() {
        $this->output->set_content_type('application/json');
        
        $brand_name = $this->input->post('brand_name');
        $category = $this->input->post('category');
        
        if (empty($brand_name)) {
            return $this->output->set_output(json_encode(['success' => false, 'message' => 'Brand name is required']));
        }
        
        $search_result = $this->jsm_api->search_products([
            'keyword' => $brand_name,
            'category' => $category,
            'page_size' => 10
        ]);
        
        if (!$search_result['success']) {
            return $this->output->set_output(json_encode(['success' => false, 'message' => $search_result['message']]));
        }
        
        if (!empty($search_result['data'])) {
            $first_product = $search_result['data'][0];
            $brand_id = $this->Brand_model->sync_brand_from_product($first_product);
            $this->Brand_model->assign_bd_to_brand($brand_id, $this->session->userdata('user_id'));
            
            return $this->output->set_output(json_encode([
                'success' => true,
                'message' => 'Brand scouted successfully!',
                'data' => [
                    'brand' => $first_product['shop_name'],
                    'product' => $first_product['title'],
                    'commission' => $first_product['open_collab']
                ]
            ]));
        }
        
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'No products found for this brand']));
    }
    
    public function task_hunting() {
        $this->output->set_content_type('application/json');
        
        $brand_name = $this->input->post('brand_name');
        
        $email_content = "Hi {$brand_name} Marketing Team,\n\n" .
            "Toopai is a curated network of highly-converting TikTok/Shopee affiliates. " .
            "Our analysis shows your products have massive untapped potential.\n\n" .
            "We currently have **150+ verified creators** whose audiences perfectly match your demographic. " .
            "We'd like to propose an exclusive 15% commission campaign.\n\n" .
            "Are you open to allocating a small sample quota to test this?\n\n" .
            "Best regards,\nToopai BD Team";
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'email_content' => $email_content,
            'next_stage' => 'outreach'
        ]));
    }
    
    public function task_outreach() {
        $this->output->set_content_type('application/json');
        return $this->output->set_output(json_encode(['success' => true, 'next_stage' => 'deal']));
    }
    
    public function task_deal() {
        $this->output->set_content_type('application/json');
        return $this->output->set_output(json_encode(['success' => true, 'next_stage' => 'onboarding']));
    }
    
    public function task_onboarding() {
        $this->output->set_content_type('application/json');
        return $this->output->set_output(json_encode(['success' => true, 'next_stage' => 'campaign_setup']));
    }
    
    public function task_launch() {
        $this->output->set_content_type('application/json');
        return $this->output->set_output(json_encode(['success' => true, 'next_stage' => 'retention']));
    }
    
    public function task_retention() {
        $this->output->set_content_type('application/json');
        return $this->output->set_output(json_encode(['success' => true, 'next_stage' => 'completed']));
    }
    
    public function assign_link_to_creator() {
    $this->output->set_content_type('application/json');
    
    $campaign_id = $this->input->post('campaign_id');
    $product_id = $this->input->post('product_id');
    $creator_username = $this->input->post('creator_username');
    $commission_rate = $this->input->post('commission_rate');
    
    // Generate affiliate link
    $link_result = $this->jsm_api->generate_promotion_link(
        $campaign_id,
        $product_id,
        $commission_rate ?? 8
    );
    
    if (!$link_result['success']) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $link_result['message']
        ]));
    }
    
    // Simpan ke creator_link_assignments
    $assignment_data = [
        'assignment_id' => uniqid('link_'),
        'campaign_id' => $campaign_id,
        'product_id' => $product_id,
        'product_name' => $this->input->post('product_name'),
        'creator_username' => $creator_username,
        'creator_email' => $this->input->post('creator_email'),
        'affiliate_link' => $link_result['link'],
        'commission_rate' => $commission_rate ?? 8,
        'shared_date' => date('Y-m-d H:i:s'),
        'expire_date' => $link_result['expire_at'] ?? null,
        'assigned_by' => $this->session->userdata('user_id'),
        'notes' => $this->input->post('notes')
    ];
    
    $this->db->insert('creator_link_assignments', $assignment_data);
    $assignment_id = $this->db->insert_id();
    
    // Juga simpan ke affiliate_creator_links
    $this->Affiliate_sync_model->save_creator_link($assignment_data);
    
    // Kirim notifikasi ke creator (email/telegram)
    $this->send_link_to_creator($creator_username, $link_result['link'], $campaign_id);
    
    echo json_encode([
        'success' => true,
        'message' => 'Link assigned successfully',
        'assignment_id' => $assignment_id,
        'affiliate_link' => $link_result['link']
    ]);
}

public function sync_creators_from_orders() {
    $this->output->set_content_type('application/json');
    
    // Ambil semua creator unik dari orders
    $creators = $this->db->query("
        SELECT DISTINCT creator_username 
        FROM affiliate_orders 
        WHERE creator_username IS NOT NULL AND creator_username != ''
    ")->result();
    
    $total_creators = 0;
    
    foreach ($creators as $creator) {
        // Hitung statistik per creator
        $stats = $this->db->query("
            SELECT 
                COUNT(*) as total_orders,
                SUM(gmv) as total_gmv,
                SUM(actual_commission) as total_commission,
                COUNT(DISTINCT campaign_id) as total_campaigns
            FROM affiliate_orders
            WHERE creator_username = ?
                AND order_status NOT IN ('CANCELLED', 'REFUNDED')
        ", [$creator->creator_username])->row();
        
        // Simpan ke affiliate_creator_links jika perlu
        // Atau update data creator di tabel creators
        $this->db->where('username', $creator->creator_username);
        $existing = $this->db->get('creators')->row();
        
        if ($existing) {
            $this->db->where('username', $creator->creator_username)->update('creators', [
                'total_gmv' => $stats->total_gmv,
                'total_orders' => $stats->total_orders,
                'total_commission' => $stats->total_commission,
                'last_active' => date('Y-m-d H:i:s')
            ]);
        } else {
            $this->db->insert('creators', [
                'username' => $creator->creator_username,
                'total_gmv' => $stats->total_gmv,
                'total_orders' => $stats->total_orders,
                'total_commission' => $stats->total_commission,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        $total_creators++;
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Synced $total_creators creators from orders",
        'creators' => $creators
    ]);
}
/**
 * Get BD Performance Leaderboard
 */
private function get_bd_performance() {
    // Ambil semua BD
    $bd_users = $this->db->select('id, username, full_name')
                        ->where('role', 'BD')
                        ->get('users')
                        ->result();
    
    $result = [];
    foreach ($bd_users as $bd) {
        // Hitung GMV 30 hari untuk BD ini
        $gmv = $this->db->select('COALESCE(SUM(o.gmv), 0) as total_gmv')
            ->from('affiliate_orders o')
            ->join('affiliate_products ap', 'o.product_id = ap.product_id AND o.campaign_id = ap.campaign_id')
            ->join('brands b', 'ap.shop_name = b.name', 'inner')
            ->where('b.bd_id', $bd->id)
            ->where('o.order_date_local >=', date('Y-m-d', strtotime('-30 days')))
            ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->get()
            ->row()
            ->total_gmv ?? 0;
        
        // Hitung total orders
        $orders = $this->db->select('COUNT(DISTINCT o.order_id) as total_orders')
            ->from('affiliate_orders o')
            ->join('affiliate_products ap', 'o.product_id = ap.product_id AND o.campaign_id = ap.campaign_id')
            ->join('brands b', 'ap.shop_name = b.name', 'inner')
            ->where('b.bd_id', $bd->id)
            ->where('o.order_date_local >=', date('Y-m-d', strtotime('-30 days')))
            ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->get()
            ->row()
            ->total_orders ?? 0;
        
        // Hitung total commission
        $commission = $this->db->select('COALESCE(SUM(o.estimated_commission), 0) as total_commission')
            ->from('affiliate_orders o')
            ->join('affiliate_products ap', 'o.product_id = ap.product_id AND o.campaign_id = ap.campaign_id')
            ->join('brands b', 'ap.shop_name = b.name', 'inner')
            ->where('b.bd_id', $bd->id)
            ->where('o.order_date_local >=', date('Y-m-d', strtotime('-30 days')))
            ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->get()
            ->row()
            ->total_commission ?? 0;
        
        // Hitung total brands
        $total_brands = $this->db->where('bd_id', $bd->id)
                                ->where('status', 'ACTIVE')
                                ->count_all_results('brands');
        
        // Hitung conversion rate (estimated)
        $conversion = $orders > 0 ? min(100, round(($orders / 100) * 1.5, 2)) : 0;
        
        $result[] = (object)[
            'username' => $bd->username,
            'full_name' => $bd->full_name,
            'total_gmv' => floatval($gmv),
            'total_orders' => intval($orders),
            'total_commission' => floatval($commission),
            'total_brands' => intval($total_brands),
            'conversion' => $conversion
        ];
    }
    
    // Sort by GMV descending
    usort($result, function($a, $b) {
        return $b->total_gmv <=> $a->total_gmv;
    });
    
    return array_slice($result, 0, 10);
}

/**
 * Get CA Performance Leaderboard
 */
private function get_ca_performance() {
    // Ambil semua CA (IS)
    $ca_users = $this->db->select('id, username, full_name')
                         ->where('role', 'IS')
                         ->get('users')
                         ->result();
    
    $result = [];
    foreach ($ca_users as $ca) {
        // 🔥 PERBAIKAN: JOIN dengan creators (bukan brands.input_by)
        // Hitung GMV 30 hari untuk CA ini melalui creator yang di-assign ke CA
        $gmv = $this->db->select('COALESCE(SUM(o.gmv), 0) as total_gmv')
            ->from('affiliate_orders o')
            ->join('creators c', 'o.creator_username = c.username', 'inner')
            ->where('c.is_id', $ca->id)  // 🔥 PAKAI is_id DARI creators
            ->where('o.order_date_local >=', date('Y-m-d', strtotime('-30 days')))
            ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->get()
            ->row()
            ->total_gmv ?? 0;
        
        // Hitung total orders
        $orders = $this->db->select('COUNT(DISTINCT o.order_id) as total_orders')
            ->from('affiliate_orders o')
            ->join('creators c', 'o.creator_username = c.username', 'inner')
            ->where('c.is_id', $ca->id)
            ->where('o.order_date_local >=', date('Y-m-d', strtotime('-30 days')))
            ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->get()
            ->row()
            ->total_orders ?? 0;
        
        // Hitung total commission
        $commission = $this->db->select('COALESCE(SUM(o.estimated_commission), 0) as total_commission')
            ->from('affiliate_orders o')
            ->join('creators c', 'o.creator_username = c.username', 'inner')
            ->where('c.is_id', $ca->id)
            ->where('o.order_date_local >=', date('Y-m-d', strtotime('-30 days')))
            ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->get()
            ->row()
            ->total_commission ?? 0;
        
        // Hitung total brands yang di-handle oleh CA ini (dari creators)
        $total_brands = $this->db->select('COUNT(DISTINCT c.brand_id) as total_brands')
            ->from('creators c')
            ->where('c.is_id', $ca->id)
            ->get()
            ->row()
            ->total_brands ?? 0;
        
        // 🔥 HITUNG TOTAL CREATOR YANG DI-HANDLE
        $total_creators = $this->db->where('is_id', $ca->id)
            ->count_all_results('creators');
        
        // Hitung conversion rate
        $conversion = $orders > 0 ? min(100, round(($orders / 100) * 1.2, 2)) : 0;
        
        $result[] = (object)[
            'username' => $ca->username,
            'full_name' => $ca->full_name,
            'total_gmv' => floatval($gmv),
            'total_orders' => intval($orders),
            'total_commission' => floatval($commission),
            'total_brands' => intval($total_brands),
            'total_creators' => intval($total_creators),
            'conversion' => $conversion
        ];
    }
    
    // Sort by GMV descending
    usort($result, function($a, $b) {
        return $b->total_gmv <=> $a->total_gmv;
    });
    
    return array_slice($result, 0, 10);
}


}