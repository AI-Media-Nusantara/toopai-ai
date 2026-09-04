<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bd extends CI_Controller {

        private $api_type = 'TOOPAI';

    public function __construct() {
        parent::__construct();
        
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
        
        if ($this->session->userdata('role') != 'BD') {
            show_error('Access denied. BD only area.', 403);
        }
        
        // Load library dengan api_type yang benar
        $this->load->library('Jsm_api', ['api_type' => $this->api_type]);
        $this->load->model(['Campaign_model', 'Brand_model', 'Product_model', 'User_model', 'Jsm_token_model', 'Creator_model', 'Task_progress_model']);
        $this->load->helper('number');
        $this->load->database();
    }
    
    // ========== DASHBOARD UTAMA ==========
    public function index() {
        redirect('bd/dashboard');
    }
    
public function dashboard() {
    $user_id = $this->session->userdata('user_id');
    $role = $this->session->userdata('role');
    $is_supervisor = ($user_id == 1 || $role == 'admin');
    $today = date('Y-m-d');
    $seven_days_ago = date('Y-m-d', strtotime('-7 days'));
    
    // ========== 🔥 TOTAL COUNT PER TASK (HANYA ANGKA) ==========
    
    // Task 1: HUNTING (status PENDING)
    $this->db->where('status', 'PENDING');
    if (!$is_supervisor) {
        $this->db->where('bd_id', $user_id);
    }
    $total_hunting = $this->db->count_all_results('brands');
    
    // Task 2: FOLLOW UP (status FOLLOW_UP)
    $this->db->where('status', 'FOLLOW_UP');
    if (!$is_supervisor) {
        $this->db->where('bd_id', $user_id);
    }
    $total_followup = $this->db->count_all_results('brands');
    
    // Task 3: SETUP CAMPAIGN (status CAMPAIGN_READY + NEED_CLAIM + ACTIVE dengan produk pending)
    $this->db->where_in('status', ['CAMPAIGN_READY', 'NEED_CLAIM']);
    if (!$is_supervisor) {
        // Hitung brand milik BD ini (termasuk is_duplicate=1) + NEED_CLAIM yang pernah dihubungi
        $this->db->group_start()
            ->where('bd_id', $user_id)
            ->or_group_start()
                ->where('is_duplicate', 0)
                ->where('status', 'NEED_CLAIM')
                ->where("id IN (SELECT DISTINCT(duplicate_of) FROM brands WHERE bd_id = $user_id AND is_duplicate = 1)", NULL, FALSE)
            ->group_end()
        ->group_end();
    }
    $total_setup = $this->db->count_all_results('brands');
    
    // Tambah ACTIVE dengan pending (pakai INNER JOIN)
    $active_with_pending_count = $this->db->select('b.id')
        ->from('brands b')
        ->join('affiliate_products ap', 'b.name = ap.shop_name AND ap.review_status = "PENDING"', 'inner')
        ->where('b.status', 'ACTIVE')
        ->group_by('b.id');
    
    if (!$is_supervisor) {
        $active_with_pending_count->where('b.bd_id', $user_id);
    }
    
    $total_setup += $active_with_pending_count->count_all_results();
    
    // Task 4: MONITORING (status ACTIVE - yang tidak punya pending)
    $active_brands = $this->db->select('b.id')
        ->from('brands b')
        ->join('affiliate_products ap', 'b.name = ap.shop_name AND ap.review_status = "PENDING"', 'left')
        ->where('b.status', 'ACTIVE')
        ->where('ap.id IS NULL')
        ->group_by('b.id');
    
    if (!$is_supervisor) {
        $active_brands->where('b.bd_id', $user_id);
    }
    
    $total_monitoring = $active_brands->count_all_results();
    
    // ========== AMBIL LIST BD (UNTUK SUPERVISOR) ==========
    if ($is_supervisor) {
        $bd_list = $this->db->select('id, username, full_name')
                            ->where('role', 'BD')
                            ->order_by('id', 'ASC')
                            ->get('users')
                            ->result();
        
        foreach ($bd_list as $bd) {
            $today_gmv_bd = $this->db->select('COALESCE(SUM(o.gmv), 0) as total')
                ->from('brands b')
                ->join('brand_products bp', 'b.id = bp.brand_id')
                ->join('affiliate_orders o', 'bp.product_id = o.product_id')
                ->where('b.bd_id', $bd->id)
                ->where('o.order_date_local', $today)
                ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
                ->get()
                ->row()
                ->total ?? 0;
            
            $bd->today_gmv = floatval($today_gmv_bd);
        }
        
        usort($bd_list, function($a, $b) {
            return $b->today_gmv <=> $a->today_gmv;
        });
        
    } else {
        $bd_list = [];
    }
    
    // ========== 🔥 AUTO-UPDATE: FOLLOW_UP -> CAMPAIGN_READY ==========
    // Brand yang sudah konfirmasi deal (deal_confirmed_at terisi) otomatis pindah ke Step 3
    // Pengecekan produk TIDAK dilakukan di sini karena produk baru disubmit saat Step 3
    $updated_brands = $this->db
        ->where('status', 'FOLLOW_UP')
        ->where('deal_confirmed_at IS NOT NULL')
        ->update('brands', [
            'status'       => 'CAMPAIGN_READY',
            'current_task' => 3,
            'updated_at'   => date('Y-m-d H:i:s')
        ]);

    if ($this->db->affected_rows() > 0) {
        log_message('info', 'Auto-updated ' . $this->db->affected_rows() . ' brands from FOLLOW_UP to CAMPAIGN_READY on dashboard load');
    }
    
    // ========== 🔥 AUTO-UPDATE: CAMPAIGN_READY -> ACTIVE ==========
    $campaign_ready_brands = $this->db->select('id, name')
        ->where('status', 'CAMPAIGN_READY')
        ->get('brands')
        ->result();

    $moved_to_active = 0;
    foreach ($campaign_ready_brands as $brand) {
        $pending_count = $this->db->select('COUNT(*) as total')
            ->from('affiliate_products')
            ->where('shop_name', $brand->name)
            ->where('review_status', 'PENDING')
            ->get()
            ->row()
            ->total ?? 0;
        
        $approved_count = $this->db->select('COUNT(*) as total')
            ->from('affiliate_products')
            ->where('shop_name', $brand->name)
            ->where('review_status', 'APPROVED')
            ->get()
            ->row()
            ->total ?? 0;
        
        $total_products = $this->db->select('COUNT(*) as total')
            ->from('affiliate_products')
            ->where('shop_name', $brand->name)
            ->get()
            ->row()
            ->total ?? 0;
        
        if ($total_products > 0 && $pending_count == 0 && $approved_count > 0) {
            $this->db->where('id', $brand->id)
                     ->update('brands', [
                         'status' => 'ACTIVE',
                         'current_task' => 4,
                         'campaign_launched_at' => date('Y-m-d H:i:s'),
                         'updated_at' => date('Y-m-d H:i:s')
                     ]);
            $moved_to_active++;
        }
    }

    if ($moved_to_active > 0) {
        log_message('info', "Auto-updated {$moved_to_active} brands from CAMPAIGN_READY to ACTIVE (all products approved)");
    }
    
    // ========== AMBIL SEMUA BRAND ==========
    if ($is_supervisor) {
        $brands = $this->db->select('b.*, u.username as bd_username, u.full_name as bd_name, b.input_by, b.input_by_name')
                           ->from('brands b')
                           ->join('users u', 'b.bd_id = u.id', 'left')
                           ->where_not_in('b.status', ['DELETED', 'DUPLICATE_DEAL'])
                           ->order_by('b.created_at', 'DESC')
                           ->get()
                           ->result();
    } else {
        $brands = $this->db->select('b.*, u.username as bd_username, u.full_name as bd_name, b.input_by, b.input_by_name')
                           ->from('brands b')
                           ->join('users u', 'b.bd_id = u.id', 'left')
                           ->where('b.bd_id', $user_id)
                           ->where_not_in('b.status', ['DELETED', 'DUPLICATE_DEAL'])
                           ->order_by('b.created_at', 'DESC')
                           ->get()
                           ->result();
    }
    
    // ========== TOTAL GMV HARI INI ==========
    $today_gmv = $this->db->select('COALESCE(SUM(gmv), 0) as total')
                          ->from('affiliate_orders')
                          ->where('order_date_local', $today)
                          ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
                          ->get()
                          ->row()
                          ->total ?? 0;
    
    $yesterday_gmv = $this->db->select('COALESCE(SUM(gmv), 0) as total')
                              ->from('affiliate_orders')
                              ->where('order_date_local', date('Y-m-d', strtotime('-1 day')))
                              ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
                              ->get()
                              ->row()
                              ->total ?? 0;
    
    $gmv_growth = $yesterday_gmv > 0 ? (($today_gmv - $yesterday_gmv) / $yesterday_gmv * 100) : ($today_gmv > 0 ? 100 : 0);
    
    // ========== HITUNG GMV PER BRAND UNTUK HARI INI ==========
    foreach ($brands as $brand) {
        $stats = $this->db->select('
                COALESCE(SUM(o.gmv), 0) as total_gmv,
                COUNT(DISTINCT o.order_id) as total_orders,
                COUNT(DISTINCT o.creator_username) as total_creators
            ')
            ->from('affiliate_orders o')
            ->join('affiliate_products ap', 'o.product_id = ap.product_id AND o.campaign_id = ap.campaign_id')
            ->where('ap.shop_name', $brand->name)
            ->where('ap.review_status', 'APPROVED')
            ->where('o.order_date_local', $today)
            ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->get()
            ->row();
        
        $brand->today_gmv = floatval($stats->total_gmv ?? 0);
        $brand->total_orders = intval($stats->total_orders ?? 0);
        $brand->total_creators = intval($stats->total_creators ?? 0);
    }
    
    // ========== TASK 1: HUNTING (status PENDING) ==========
    if ($is_supervisor) {
        $hunting_items = $this->db->select('b.*, u.username as bd_username, u.full_name as bd_name, b.input_by, b.input_by_name')
            ->from('brands b')
            ->join('users u', 'b.bd_id = u.id', 'left')
            ->where('b.status', 'PENDING')
            ->order_by('b.created_at', 'DESC')
            ->limit(1000)
            ->get()
            ->result();
    } else {
        $hunting_items = $this->db->select('b.*, u.username as bd_username, u.full_name as bd_name, b.input_by, b.input_by_name')
            ->from('brands b')
            ->join('users u', 'b.bd_id = u.id', 'left')
            ->where('b.bd_id', $user_id)
            ->where('b.status', 'PENDING')
            ->order_by('b.created_at', 'DESC')
            ->limit(1000)
            ->get()
            ->result();
    }
    
    // ========== TASK 2: FOLLOW UP (status FOLLOW_UP) ==========
    if ($is_supervisor) {
        $followup_items = $this->db->select('
                b.*, 
                u.username as bd_username, 
                u.full_name as bd_name, 
                b.input_by, 
                b.input_by_name,
                COUNT(DISTINCT wl.id) as whatsapp_count
            ')
            ->from('brands b')
            ->join('users u', 'b.bd_id = u.id', 'left')
            ->join('whatsapp_logs wl', 'b.id = wl.brand_id', 'left')
            ->where('b.status', 'FOLLOW_UP')
            ->group_by('b.id')
            ->order_by('b.deal_confirmed_at', 'DESC')
            ->limit(1000)
            ->get()
            ->result();
    } else {
        $followup_items = $this->db->select('
                b.*, 
                u.username as bd_username, 
                u.full_name as bd_name, 
                b.input_by, 
                b.input_by_name,
                COUNT(DISTINCT wl.id) as whatsapp_count
            ')
            ->from('brands b')
            ->join('users u', 'b.bd_id = u.id', 'left')
            ->join('whatsapp_logs wl', 'b.id = wl.brand_id', 'left')
            ->where('b.bd_id', $user_id)
            ->where('b.status', 'FOLLOW_UP')
            ->group_by('b.id')
            ->order_by('b.deal_confirmed_at', 'DESC')
            ->limit(1000)
            ->get()
            ->result();
    }

    // Tambahkan info produk dan klik count untuk tampilan di view
    foreach ($followup_items as $item) {
        $product_count = $this->db->select('COUNT(*) as total')
            ->from('affiliate_products')
            ->where('shop_name', $item->name)
            ->get()
            ->row()
            ->total ?? 0;
        
        $item->has_products    = $product_count > 0;
        $item->follow_up_click_count = intval($item->follow_up_click_count ?? 0);
    }
    
    // ========== 🔥 TASK 3: SETUP CAMPAIGN ==========
    // Ambil brand CAMPAIGN_READY
    if ($is_supervisor) {
        $setup_items_campaign_ready = $this->db->select('b.*, u.username as bd_username, u.full_name as bd_name, b.input_by, b.input_by_name')
            ->from('brands b')
            ->join('users u', 'b.bd_id = u.id', 'left')
            ->where('b.is_duplicate', 0)
            ->where_in('b.status', ['CAMPAIGN_READY', 'NEED_CLAIM'])
            ->order_by('b.updated_at', 'DESC')
            ->limit(1000)
            ->get()
            ->result();
    } else {
        // Ambil brand milik BD sendiri (is_duplicate 0 atau 1),
        // ATAU brand NEED_CLAIM (is_duplicate=0) yang pernah dihubungi user via entry duplikatnya
        $setup_items_campaign_ready = $this->db->select('b.*, u.username as bd_username, u.full_name as bd_name, b.input_by, b.input_by_name')
            ->from('brands b')
            ->join('users u', 'b.bd_id = u.id', 'left')
            ->group_start()
                // Brand yang langsung milik BD ini (terlepas is_duplicate)
                ->where('b.bd_id', $user_id)
                ->or_group_start()
                    // Brand NEED_CLAIM milik BD lain, tapi BD ini pernah input sebagai duplikat
                    ->where('b.is_duplicate', 0)
                    ->where('b.status', 'NEED_CLAIM')
                    ->where("b.id IN (SELECT DISTINCT(duplicate_of) FROM brands WHERE bd_id = $user_id AND is_duplicate = 1)", NULL, FALSE)
                ->group_end()
            ->group_end()
            ->where_in('b.status', ['CAMPAIGN_READY', 'NEED_CLAIM'])
            ->order_by('b.updated_at', 'DESC')
            ->limit(1000)
            ->get()
            ->result();
    }
    
    // Ambil brand ACTIVE yang memiliki produk PENDING (pakai INNER JOIN)
    if ($is_supervisor) {
        $setup_items_active_pending = $this->db->select('b.*, u.username as bd_username, u.full_name as bd_name, b.input_by, b.input_by_name')
            ->from('brands b')
            ->join('users u', 'b.bd_id = u.id', 'left')
            ->join('affiliate_products ap', 'b.name = ap.shop_name AND ap.review_status = "PENDING"', 'inner')
            ->where('b.status', 'ACTIVE')
            ->group_by('b.id')
            ->order_by('b.updated_at', 'DESC')
            ->limit(1000)
            ->get()
            ->result();
    } else {
        $setup_items_active_pending = $this->db->select('b.*, u.username as bd_username, u.full_name as bd_name, b.input_by, b.input_by_name')
            ->from('brands b')
            ->join('users u', 'b.bd_id = u.id', 'left')
            ->join('affiliate_products ap', 'b.name = ap.shop_name AND ap.review_status = "PENDING"', 'inner')
            ->where('b.bd_id', $user_id)
            ->where('b.status', 'ACTIVE')
            ->group_by('b.id')
            ->order_by('b.updated_at', 'DESC')
            ->limit(1000)
            ->get()
            ->result();
    }
    
    // 🔥 GABUNGKAN KEDUA HASIL
    $setup_items = array_merge($setup_items_campaign_ready, $setup_items_active_pending);
    
    // 🔥 PROSES SETUP ITEMS: TAMBAHKAN INFORMASI
    foreach ($setup_items as $item) {
        // Cek pending products
        $pending_count = $this->db->select('COUNT(*) as total')
            ->from('affiliate_products')
            ->where('shop_name', $item->name)
            ->where('review_status', 'PENDING')
            ->get()
            ->row()
            ->total ?? 0;
        
        $item->pending_products_count = intval($pending_count);
        
        // Cek approved products
        $approved_count = $this->db->select('COUNT(*) as total')
            ->from('affiliate_products')
            ->where('shop_name', $item->name)
            ->where('review_status', 'APPROVED')
            ->get()
            ->row()
            ->total ?? 0;
        
        $item->approved_products_count = intval($approved_count);
        
        // Cek total products
        $total_products = $this->db->select('COUNT(*) as total')
            ->from('affiliate_products')
            ->where('shop_name', $item->name)
            ->get()
            ->row()
            ->total ?? 0;
        
        $item->has_submitted_products = $total_products > 0;
        $item->is_active_brand = ($item->status == 'ACTIVE');
        
        // Cek requirement
        $has_requirements = $this->db->select('creator_level, creator_gmv, content_type, sample_method, requirements_filled_at')
            ->where('id', $item->id)
            ->where('creator_level IS NOT NULL')
            ->where('creator_gmv IS NOT NULL')
            ->where('content_type IS NOT NULL')
            ->get('brands')
            ->row();
        
        $item->has_requirements = !empty($has_requirements);
        $item->requirements_data = $has_requirements;
    }
    
    // ========== TASK 4: MONITORING (status ACTIVE - tanpa pending) ==========
   if ($is_supervisor) {
    $monitoring_items = $this->db->select('b.*, u.username as bd_username, u.full_name as bd_name, b.input_by, b.input_by_name')
        ->from('brands b')
        ->join('users u', 'b.bd_id = u.id', 'left')
        ->join('affiliate_products ap', 'b.name = ap.shop_name AND ap.review_status = "PENDING"', 'left')
        ->where('b.status', 'ACTIVE')
        ->where('ap.id IS NULL')  // 🔥 KRUSIAL: HANYA YANG TIDAK PUNYA PENDING
        ->group_by('b.id')
        ->order_by('b.updated_at', 'DESC')
        ->limit(1000)
        ->get()
        ->result();
} else {
    $monitoring_items = $this->db->select('b.*, u.username as bd_username, u.full_name as bd_name, b.input_by, b.input_by_name')
        ->from('brands b')
        ->join('users u', 'b.bd_id = u.id', 'left')
        ->join('affiliate_products ap', 'b.name = ap.shop_name AND ap.review_status = "PENDING"', 'left')
        ->where('b.bd_id', $user_id)
        ->where('b.status', 'ACTIVE')
        ->where('ap.id IS NULL')  // 🔥 KRUSIAL: HANYA YANG TIDAK PUNYA PENDING
        ->group_by('b.id')
        ->order_by('b.updated_at', 'DESC')
        ->limit(1000)
        ->get()
        ->result();
}
    // Hitung today_gmv untuk monitoring items
    foreach ($monitoring_items as $item) {
        $stats = $this->db->select('COALESCE(SUM(o.gmv), 0) as total_gmv')
            ->from('affiliate_orders o')
            ->join('affiliate_products ap', 'o.product_id = ap.product_id AND o.campaign_id = ap.campaign_id')
            ->where('ap.shop_name', $item->name)
            ->where('ap.review_status', 'APPROVED')
            ->where('o.order_date_local', $today)
            ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->get()
            ->row();
        
        $item->today_gmv = floatval($stats->total_gmv ?? 0);
        $item->approved_products_count = $this->db->where('shop_name', $item->name)
            ->where('review_status', 'APPROVED')
            ->count_all_results('affiliate_products');
    }
    
    // ========== PESANAN TERBARU (HARI INI) ==========
    if ($is_supervisor) {
        $orders = $this->db->select('o.order_id, o.product_name, o.creator_username, o.gmv, o.estimated_commission, o.order_date_local, o.order_time')
                           ->from('affiliate_orders o')
                           ->where('o.order_date_local', $today)
                           ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
                           ->order_by('o.order_time', 'DESC')
                           ->limit(100)
                           ->get()
                           ->result();
    } else {
        $orders = $this->db->select('o.order_id, o.product_name, o.creator_username, o.gmv, o.estimated_commission, o.order_date_local, o.order_time')
                           ->from('affiliate_orders o')
                           ->join('affiliate_products ap', 'o.product_id = ap.product_id AND o.campaign_id = ap.campaign_id')
                           ->join('brands b', 'ap.shop_name = b.name', 'inner')
                           ->where('b.bd_id', $user_id)
                           ->where('o.order_date_local', $today)
                           ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
                           ->order_by('o.order_time', 'DESC')
                           ->limit(100)
                           ->get()
                           ->result();
    }
    
    // ========== BONUS & ROAS ==========
    $total_commission_today = $this->db->select('COALESCE(SUM(estimated_commission), 0) as total')
                                      ->from('affiliate_orders')
                                      ->where('order_date_local', $today)
                                      ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
                                      ->get()
                                      ->row()
                                      ->total ?? 0;
    
    $roas = ($total_commission_today > 0) ? round($today_gmv / $total_commission_today, 2) : 0;
    $deal_bonus_amount = $today_gmv * 0.0015;
    
    // ========== STATUS BRAND (AKTIF VS TIDAK AKTIF) ==========
    if ($is_supervisor) {
        $all_brands_status = $this->db->select('b.*, u.username as bd_username, u.full_name as bd_name, b.input_by, b.input_by_name')
            ->from('brands b')
            ->join('users u', 'b.bd_id = u.id', 'left')
            ->where_not_in('b.status', ['DELETED', 'DUPLICATE_DEAL'])
            ->order_by('b.created_at', 'DESC')
            ->get()
            ->result();
    } else {
        $all_brands_status = $this->db->select('b.*, u.username as bd_username, u.full_name as bd_name, b.input_by, b.input_by_name')
            ->from('brands b')
            ->join('users u', 'b.bd_id = u.id', 'left')
            ->where('b.bd_id', $user_id)
            ->where_not_in('b.status', ['DELETED', 'DUPLICATE_DEAL'])
            ->order_by('b.created_at', 'DESC')
            ->get()
            ->result();
    }
    
    $inactive_brands = [];
    $active_brands_list = [];
    
    foreach ($all_brands_status as $brand) {
        if ($brand->status == 'ACTIVE') {
            $gmv_stats = $this->db->select('COALESCE(SUM(o.gmv), 0) as total_gmv')
                ->from('affiliate_orders o')
                ->join('affiliate_products ap', 'o.product_id = ap.product_id AND o.campaign_id = ap.campaign_id')
                ->where('ap.shop_name', $brand->name)
                ->where('ap.review_status', 'APPROVED')
                ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
                ->get()
                ->row();
            $brand->total_gmv_status = floatval($gmv_stats->total_gmv ?? 0);
            $active_brands_list[] = $brand;
        } else {
            $inactive_brands[] = $brand;
        }
    }
    
    usort($active_brands_list, function($a, $b) {
        return $b->total_gmv_status <=> $a->total_gmv_status;
    });
    
    // ========== LEADERBOARD TOP BRANDS (7 HARI) ==========
    $top_brands = $this->db->select('
            b.id as brand_id,
            b.name as brand_name,
            b.category,
            b.input_by,
            b.input_by_name,
            u.username as bd_username,
            u.full_name as bd_name,
            COALESCE(SUM(o.gmv), 0) as total_gmv,
            COUNT(DISTINCT o.order_id) as total_orders,
            COUNT(DISTINCT o.creator_username) as total_creators,
            COALESCE(SUM(o.estimated_commission), 0) as total_commission
        ')
        ->from('affiliate_orders o')
        ->join('affiliate_products ap', 'o.product_id = ap.product_id AND o.campaign_id = ap.campaign_id')
        ->join('brands b', 'ap.shop_name = b.name', 'left')
        ->join('users u', 'b.bd_id = u.id', 'left')
        ->where('o.order_date_local >=', $seven_days_ago)
        ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
        ->where('ap.review_status', 'APPROVED')
        ->where('ap.shop_name IS NOT NULL')
        ->where('ap.shop_name !=', '')
        ->group_by('b.id, b.name, b.category, b.input_by, b.input_by_name, u.username, u.full_name')
        ->order_by('total_gmv', 'DESC')
        ->limit(10)
        ->get()
        ->result();
    
    $leaderboard_brands = [];
    $rank = 1;
    foreach ($top_brands as $brand) {
        $roas_brand = ($brand->total_commission > 0) ? round($brand->total_gmv / $brand->total_commission, 2) : 0;
        $leaderboard_brands[] = [
            'rank' => $rank++,
            'brand_id' => $brand->brand_id,
            'brand_name' => $brand->brand_name,
            'category' => $brand->category ?? 'GENERAL',
            'total_gmv' => floatval($brand->total_gmv),
            'total_orders' => intval($brand->total_orders),
            'total_creators' => intval($brand->total_creators),
            'total_commission' => floatval($brand->total_commission),
            'roas' => $roas_brand,
            'bd_username' => $brand->bd_username ?? '-',
            'bd_name' => $brand->bd_name ?? '-',
            'input_by' => $brand->input_by ?? '-'
        ];
    }
    
    // ========== TOP 3 BD LEADERBOARD (7 HARI) ==========
    $all_bd = $this->db->select('id, username, full_name, role')
                       ->where('role', 'BD')
                       ->get('users')
                       ->result();
    
    $top_bd_list = [];
    foreach ($all_bd as $bd) {
        $bd_gmv = $this->db->select('COALESCE(SUM(o.gmv), 0) as total_gmv')
            ->from('affiliate_orders o')
            ->join('affiliate_products ap', 'o.product_id = ap.product_id AND o.campaign_id = ap.campaign_id')
            ->join('brands b', 'ap.shop_name = b.name', 'inner')
            ->where('b.bd_id', $bd->id)
            ->where('o.order_date_local >=', $seven_days_ago)
            ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->where('ap.review_status', 'APPROVED')
            ->get()
            ->row()
            ->total_gmv ?? 0;
        
        $bd->total_gmv_7d = floatval($bd_gmv);
        $top_bd_list[] = $bd;
    }
    
    usort($top_bd_list, function($a, $b) {
        return $b->total_gmv_7d <=> $a->total_gmv_7d;
    });
    
    $top_3_bd = array_slice($top_bd_list, 0, 3);
    
    // ========== CAMPAIGN (UNTUK REFERENSI) ==========
    $campaigns = $this->db->select('campaign_id, campaign_name, status')
                          ->where('status', 'ONGOING')
                          ->limit(10)
                          ->get('affiliate_campaigns')
                          ->result();
    
    // ========== DATA YANG DIKIRIM KE VIEW ==========
    $data = [
        'title' => 'BD Dashboard - Toopai',
        'active_menu' => 'dashboard',
        
        'total_brands' => count($brands),
        'total_gmv' => $today_gmv,
        'gmv_growth' => round($gmv_growth, 1),
        'deal_bonus_amount' => $deal_bonus_amount,
        
        // 🔥 TOTAL COUNT PER TASK (HANYA ANGKA)
        'total_hunting' => $total_hunting,
        'total_followup' => $total_followup,
        'total_setup' => $total_setup,
        'total_monitoring' => $total_monitoring,
        
        // 🔥 DATA ITEMS
        'hunting_items' => $hunting_items,
        'followup_items' => $followup_items,
        'setup_items' => $setup_items,
        'monitoring_items' => $monitoring_items,
        
        'roas' => $roas,
        'campaigns' => $campaigns,
        'brands' => $brands,
        'orders' => $orders,
        
        // STATUS BRAND
        'inactive_brands' => $inactive_brands,
        'active_brands_list' => $active_brands_list,
        
        // LEADERBOARD
        'leaderboard_brands' => $leaderboard_brands,
        'top_3_bd' => $top_3_bd,
        
        // SUPERVISOR
        'is_supervisor' => $is_supervisor,
        'bd_list' => $bd_list,
    ];
    
    $this->load->view('templates/new/header', $data);
    $this->load->view('bd/dashboard', $data);
    $this->load->view('templates/new/footer');
}


/**
 * Increment follow up click count
 */
public function increment_followup_click() {
    $this->output->set_content_type('application/json');
    
    $brand_id = $this->input->post('brand_id');
    
    if (!$brand_id) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Brand ID required']));
    }
    
    $this->db->set('follow_up_click_count', 'follow_up_click_count + 1', FALSE);
    $this->db->where('id', $brand_id);
    $this->db->update('brands');
    
    return $this->output->set_output(json_encode(['success' => true]));
}
/**
 * Get pending products for brand from DATABASE
 */
public function get_pending_products_for_brand() {
    $this->output->set_content_type('application/json');
    
    $brand_id = $this->input->post('brand_id');
    $brand_name = $this->input->post('brand_name');
    
    if (!$brand_id && !$brand_name) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Brand ID or Brand Name required',
            'products' => []
        ]));
    }
    
    try {
        // Ambil brand name
        if ($brand_id && !$brand_name) {
            $brand = $this->db->select('name')->where('id', $brand_id)->get('brands')->row();
            $brand_name = $brand->name ?? '';
        }
        
        if (empty($brand_name)) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Brand name not found',
                'products' => []
            ]));
        }
        
        // 🔥 PERBAIKAN: Gunakan sales_count, bukan product_sales
        $products = $this->db->select('
        ap.product_id,
        ap.campaign_id,
        ap.product_name,
        ap.price,
        ap.image_url,
        ap.shop_name,
        ap.category,
        ap.inventory,
        ap.sales_count as product_sales,
        ap.sample_quota,
        ap.review_status,
        ap.open_commission_rate,
        ap.partner_commission_rate,
        ap.creator_commission_rate,
        ap.total_commission_rate,
        ap.shop_ads as shop_ads_rate,
        ac.campaign_name
    ')
    ->from('affiliate_products ap')
    ->join('affiliate_campaigns ac', 'ap.campaign_id = ac.campaign_id', 'left')
    ->where('ap.shop_name', $brand_name)
    ->where('ap.review_status', 'PENDING')
    ->order_by('ap.created_at', 'DESC')
    ->get()
    ->result();
        
        // 🔥 CEK APAKAH ADA PRODUK APPROVED
        $has_approved = $this->db->select('COUNT(*) as total')
            ->from('affiliate_products')
            ->where('shop_name', $brand_name)
            ->where('review_status', 'APPROVED')
            ->get()
            ->row()
            ->total > 0;
        
        // 🔥 CEK APAKAH PERNAH SUBMIT PRODUK
        $has_submitted = $this->db->select('COUNT(*) as total')
            ->from('affiliate_products')
            ->where('shop_name', $brand_name)
            ->get()
            ->row()
            ->total > 0;
        
        $formatted_products = [];
        foreach ($products as $p) {
            $formatted_products[] = [
                'product_id' => $p->product_id,
                'campaign_id' => $p->campaign_id,
                'campaign_name' => $p->campaign_name,
                'product_name' => $p->product_name,
                'price' => floatval($p->price),
                'lowest_price' => floatval($p->price),
                'image_url' => $p->image_url,
                'shop_name' => $p->shop_name,
                'category' => $p->category,
                'inventory' => intval($p->inventory),
                'product_sales' => intval($p->product_sales ?? 0),
                'sample_quota' => intval($p->sample_quota),
                'review_status' => $p->review_status,
                'open_commission_rate' => floatval($p->open_commission_rate),
                'partner_commission_rate' => floatval($p->partner_commission_rate),
                'creator_commission_rate' => floatval($p->creator_commission_rate),
                'total_commission_rate' => floatval($p->total_commission_rate),
                'shop_ads_rate' => floatval($p->shop_ads_rate)
            ];
        }
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'products' => $formatted_products,
            'total_pending' => count($formatted_products),
            'has_approved' => $has_approved,
            'has_submitted' => $has_submitted,
            'brand_name' => $brand_name
        ]));
        
    } catch (Exception $e) {
        log_message('error', 'Error in get_pending_products_for_brand: ' . $e->getMessage());
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'products' => []
        ]));
    }
}
public function approve_product() {
    $this->output->set_content_type('application/json');
    
    $campaign_id = $this->input->post('campaign_id');
    $product_id = $this->input->post('product_id');
    $review_result = $this->input->post('review_result');
    
    if (!$campaign_id || !$product_id) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Missing required fields'
        ]));
    }
    
    try {
        // Ambil data product dari database
        $product = $this->db->select('review_status, shop_name, product_name, open_commission_rate')
            ->from('affiliate_products')
            ->where('product_id', $product_id)
            ->where('campaign_id', $campaign_id)
            ->get()
            ->row();
        
        if (!$product) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Product not found in database'
            ]));
        }
        
        // 🔥 PANGGIL API TIKTOK UNTUK REVIEW
        $api_result = $this->jsm_api->review_campaign_product(
            $campaign_id,
            $product_id,
            $review_result
        );
        
        if (!$api_result['success']) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => $api_result['message'] ?? 'Failed to review product via API'
            ]));
        }
        
        // 🔥 UPDATE DATABASE
        $new_status = $review_result == 'APPROVE' ? 'APPROVED' : 'REJECTED';
        $this->db->where('product_id', $product_id)
                 ->where('campaign_id', $campaign_id)
                 ->update('affiliate_products', [
                     'review_status' => $new_status,
                     'updated_at' => date('Y-m-d H:i:s')
                 ]);
        
        // 🔥 GENERATE AFFILIATE LINK MENGGUNAKAN generate_bd_affiliate_link
        $affiliate_link = '';
        $link_generated = false;
        
        if ($review_result == 'APPROVE') {
            // Konversi open_commission_rate dari cents ke persen (700 = 7%)
            $open_commission_rate = $product->open_commission_rate / 100;
            
            // 🔥 PANGGIL METHOD generate_bd_affiliate_link
            $link_result = $this->generate_bd_affiliate_link_internal(
                $campaign_id,
                $product_id,
                $product->product_name,
                $open_commission_rate
            );
            
            if ($link_result['success'] && !empty($link_result['link'])) {
                $affiliate_link = $link_result['link'];
                $link_generated = true;
                
                // Update affiliate_link di database (jika kolom ada)
                $columns = $this->db->list_fields('affiliate_products');
                if (in_array('affiliate_link', $columns)) {
                    $this->db->where('product_id', $product_id)
                             ->where('campaign_id', $campaign_id)
                             ->update('affiliate_products', [
                                 'affiliate_link' => $affiliate_link
                             ]);
                }
                
                log_message('info', "Affiliate link generated for product {$product_id}: {$affiliate_link}");
            } else {
                log_message('error', "Failed to generate affiliate link for product {$product_id}: " . ($link_result['message'] ?? 'Unknown error'));
            }
        }
        
        // 🔥 CEK APAKAH MASIH ADA PRODUK PENDING
        $pending_count = $this->db->select('COUNT(*) as total')
            ->from('affiliate_products')
            ->where('shop_name', $product->shop_name)
            ->where('review_status', 'PENDING')
            ->get()
            ->row()
            ->total;
        
        // 🔥 CEK APAKAH ADA PRODUK APPROVED
        $approved_count = $this->db->select('COUNT(*) as total')
            ->from('affiliate_products')
            ->where('shop_name', $product->shop_name)
            ->where('review_status', 'APPROVED')
            ->get()
            ->row()
            ->total;
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'message' => 'Product ' . strtolower($review_result) . 'd successfully',
            'new_status' => $new_status,
            'has_more_pending' => $pending_count > 0,
            'pending_count' => $pending_count,
            'has_approved' => $approved_count > 0,
            'approved_count' => $approved_count,
            'affiliate_link' => $affiliate_link,
            'link_generated' => $link_generated
        ]));
        
    } catch (Exception $e) {
        log_message('error', 'Error in approve_product: ' . $e->getMessage());
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]));
    }
}

/**
 * Internal function to generate affiliate link (sama seperti generate_bd_affiliate_link)
 */
private function generate_bd_affiliate_link_internal($campaign_id, $product_id, $product_name, $open_commission_rate) {
    // Komisi default = +1% dari open plan
    $commission_rate = floatval($open_commission_rate);
    
    $user_id = $this->session->userdata('user_id');
    $username = $this->session->userdata('username');
    
    try {
        // Cek apakah sudah ada link
        $existing_link = $this->db->where('campaign_id', $campaign_id)
                                  ->where('product_id', $product_id)
                                  ->get('bd_affiliate_links')
                                  ->row();
        
        // Generate link via API
        $link_result = $this->jsm_api->generate_promotion_link($campaign_id, $product_id, $commission_rate);
        
        if (!$link_result['success']) {
            return [
                'success' => false,
                'message' => $link_result['message'] ?? 'Failed to generate link'
            ];
        }
        
        $now = date('Y-m-d H:i:s');
        
        if ($existing_link) {
            // Update data yang sudah ada
            $update_data = [
                'affiliate_link' => $link_result['link'],
                'commission_rate' => $commission_rate,
                'open_commission_rate' => $open_commission_rate,
                'updated_at' => $now,
                'status' => 'ACTIVE'
            ];
            
            $this->db->where('campaign_id', $campaign_id)
                     ->where('product_id', $product_id)
                     ->update('bd_affiliate_links', $update_data);
            
            // Update juga di brand_products
            $this->db->where('product_id', $product_id)
                     ->update('brand_products', [
                         'affiliate_link' => $link_result['link']
                     ]);
            
            $message = 'Affiliate link updated successfully';
            $link_id = $existing_link->id;
            
        } else {
            // Insert data baru
            $link_id = md5($campaign_id . $product_id . time());
            
            $insert_data = [
                'link_id' => $link_id,
                'campaign_id' => $campaign_id,
                'product_id' => $product_id,
                'product_name' => $product_name,
                'affiliate_link' => $link_result['link'],
                'commission_rate' => $commission_rate,
                'open_commission_rate' => $open_commission_rate,
                'created_by' => $user_id,
                'created_by_name' => $username,
                'status' => 'ACTIVE',
                'expire_at' => $link_result['expire_at'] ?? null,
                'created_at' => $now,
                'updated_at' => $now
            ];
            
            $this->db->insert('bd_affiliate_links', $insert_data);
            
            // Update juga di brand_products
            $this->db->where('product_id', $product_id)
                     ->update('brand_products', [
                         'affiliate_link' => $link_result['link']
                     ]);
            
            $message = 'Affiliate link generated successfully';
        }
        
        // Log activity
        $this->load->model('User_log_model');
        $this->User_log_model->log(
            $user_id, 
            $username, 
            'BD', 
            'GENERATE_AFFILIATE_LINK', 
            "Generated/Updated affiliate link for product: $product_name (Campaign: $campaign_id) with commission $commission_rate%"
        );
        
        return [
            'success' => true,
            'link' => $link_result['link'],
            'commission_rate' => $commission_rate,
            'link_id' => $link_id,
            'message' => $message,
            'is_update' => !empty($existing_link)
        ];
        
    } catch (Exception $e) {
        log_message('error', 'Error generating affiliate link: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

public function approve_product_with_commission() {
    $this->output->set_content_type('application/json');
     $user_id = $this->session->userdata('user_id');
    $username = $this->session->userdata('username');
    
    // 🔥 HANYA USER ID = 1 (TIFFANY) YANG BISA GENERATE LINK
    if ($user_id != 1) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Anda tidak memiliki akses untuk generate link. Hanya Head BA yang dapat generate link afiliasi.',
            'can_generate' => false
        ]));
    }
    $campaign_id = $this->input->post('campaign_id');
    $product_id = $this->input->post('product_id');
    $review_result = $this->input->post('review_result');
    $commission_rate = $this->input->post('commission_rate') ?: 10; // 🔥 KOMISI DARI INPUT
    
    if (!$campaign_id || !$product_id) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Missing required fields'
        ]));
    }
    
    try {
        // Ambil data product dari database
        $product = $this->db->select('review_status, shop_name, product_name, open_commission_rate')
            ->from('affiliate_products')
            ->where('product_id', $product_id)
            ->where('campaign_id', $campaign_id)
            ->get()
            ->row();
        
        if (!$product) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Product not found in database'
            ]));
        }
        
        // 🔥 PANGGIL API TIKTOK UNTUK REVIEW
        $api_result = $this->jsm_api->review_campaign_product(
            $campaign_id,
            $product_id,
            $review_result
        );
        
        if (!$api_result['success']) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => $api_result['message'] ?? 'Failed to review product via API'
            ]));
        }
        
        // 🔥 UPDATE DATABASE
        $new_status = $review_result == 'APPROVE' ? 'APPROVED' : 'REJECTED';
        $this->db->where('product_id', $product_id)
                 ->where('campaign_id', $campaign_id)
                 ->update('affiliate_products', [
                     'review_status' => $new_status,
                     'updated_at' => date('Y-m-d H:i:s')
                 ]);
        
        // 🔥 GENERATE AFFILIATE LINK DENGAN KOMISI CUSTOM
        $affiliate_link = '';
        $link_generated = false;
        
        if ($review_result == 'APPROVE') {
            // 🔥 GUNAKAN KOMISI DARI INPUT USER
            $link_result = $this->generate_bd_affiliate_link_internal(
                $campaign_id,
                $product_id,
                $product->product_name,
                $commission_rate  // 🔥 KOMISI CUSTOM
            );
            
            if ($link_result['success'] && !empty($link_result['link'])) {
                $affiliate_link = $link_result['link'];
                $link_generated = true;
                
                // Update affiliate_link di database
                $columns = $this->db->list_fields('affiliate_products');
                if (in_array('affiliate_link', $columns)) {
                    $this->db->where('product_id', $product_id)
                             ->where('campaign_id', $campaign_id)
                             ->update('affiliate_products', [
                                 'affiliate_link' => $affiliate_link
                             ]);
                }
                
                log_message('info', "Affiliate link generated for product {$product_id} with commission {$commission_rate}%");
            }
        }
        
        // 🔥 CEK APAKAH MASIH ADA PRODUK PENDING
        $pending_count = $this->db->select('COUNT(*) as total')
            ->from('affiliate_products')
            ->where('shop_name', $product->shop_name)
            ->where('review_status', 'PENDING')
            ->get()
            ->row()
            ->total;
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'message' => 'Product approved successfully',
            'new_status' => $new_status,
            'has_more_pending' => $pending_count > 0,
            'pending_count' => $pending_count,
            'affiliate_link' => $affiliate_link,
            'link_generated' => $link_generated,
            'commission_rate' => $commission_rate
        ]));
        
    } catch (Exception $e) {
        log_message('error', 'Error in approve_product_with_commission: ' . $e->getMessage());
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]));
    }
}

public function refresh_leaderboard() {
    $this->output->set_content_type('application/json');
    
    try {
        // Sync data terbaru dari API
        $this->sync_data();
        
        // Ambil data leaderboard 7 hari terakhir
        $seven_days_ago = date('Y-m-d', strtotime('-7 days'));
        $today = date('Y-m-d');
        
        $top_brands = $this->db->select('
                b.id as brand_id,
                b.name as brand_name,
                b.category,
                b.input_by,
                b.input_by_name,
                u.username as bd_username,
                u.full_name as bd_name,
                COALESCE(SUM(o.gmv), 0) as total_gmv,
                COUNT(DISTINCT o.order_id) as total_orders,
                COUNT(DISTINCT o.creator_username) as total_creators,
                COALESCE(SUM(o.estimated_commission), 0) as total_commission
            ')
            ->from('affiliate_orders o')
            ->join('affiliate_products ap', 'o.product_id = ap.product_id AND o.campaign_id = ap.campaign_id')
            ->join('brands b', 'ap.shop_name = b.name', 'left')
            ->join('users u', 'b.bd_id = u.id', 'left')
            ->where('o.order_date_local >=', $seven_days_ago)
            ->where('o.order_date_local <=', $today)
            ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->where('ap.review_status', 'APPROVED')
            ->where('ap.shop_name IS NOT NULL')
            ->where('ap.shop_name !=', '')
            ->group_by('b.id, b.name, b.category, b.input_by, b.input_by_name, u.username, u.full_name')
            ->order_by('total_gmv', 'DESC')
            ->limit(10)
            ->get()
            ->result();
        
        $leaderboard_data = [];
        $rank = 1;
        foreach ($top_brands as $brand) {
            $roas = ($brand->total_commission > 0) ? round($brand->total_gmv / $brand->total_commission, 2) : 0;
            
            $leaderboard_data[] = [
                'rank' => $rank++,
                'brand_name' => $brand->brand_name,
                'category' => $brand->category ?? 'GENERAL',
                'total_gmv' => floatval($brand->total_gmv),
                'total_orders' => intval($brand->total_orders),
                'total_creators' => intval($brand->total_creators),
                'roas' => $roas,
                'bd_name' => $brand->bd_name ?? '-',
                'bd_username' => $brand->bd_username ?? '-',
                'input_by' => $brand->input_by ?? '-'
            ];
        }
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'message' => 'Leaderboard refreshed successfully',
            'data' => $leaderboard_data,
            'period' => [
                'start_date' => $seven_days_ago,
                'end_date' => $today
            ]
        ]));
        
    } catch (Exception $e) {
        log_message('error', 'Refresh leaderboard error: ' . $e->getMessage());
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]));
    }
}
    // ========== BRANDS MENU ==========
 public function brands() {
    $user_id = $this->session->userdata('user_id');
    
    // 🔥 Ambil filter tanggal dari request (default hari ini)
    $filter_date = $this->input->get('date') ?: date('Y-m-d');
    $start_date = $this->input->get('start_date') ?: $filter_date;
    $end_date = $this->input->get('end_date') ?: $filter_date;
    
    // 🔥 AMBIL BRAND DARI affiliate_products (yang sudah APPROVED dan punya shop_name)
    $brands_query = $this->db->select('
            ap.shop_name as name,
            ap.category,
            "api" as source,
            COALESCE(SUM(o.gmv), 0) as total_gmv,
            COUNT(DISTINCT o.order_id) as total_orders,
            COUNT(DISTINCT o.creator_username) as total_creators
        ')
        ->from('affiliate_products ap')
        ->join('affiliate_orders o', 'ap.product_id = o.product_id AND ap.campaign_id = o.campaign_id', 'left')
        ->where('ap.review_status', 'APPROVED')
        ->where('ap.shop_name IS NOT NULL')
        ->where('ap.shop_name !=', '')
        ->group_by('ap.shop_name, ap.category')
        ->order_by('total_gmv', 'DESC');
    
    // Filter berdasarkan tanggal
    if ($start_date && $end_date) {
        $brands_query->where('o.order_date_local >=', $start_date)
                     ->where('o.order_date_local <=', $end_date)
                     ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")');
    }
    
    $brands = $brands_query->get()->result();
    
    // Format brand data
    $all_brands = [];
    foreach ($brands as $b) {
        $all_brands[] = [
            'id' => 'discovered_' . md5($b->name),
            'name' => $b->name,
            'total_gmv' => floatval($b->total_gmv ?? 0),
            'total_orders' => intval($b->total_orders ?? 0),
            'total_creators' => intval($b->total_creators ?? 0),
            'status' => 'DISCOVERED',
            'source' => 'api',
            'category' => $b->category ?? ''
        ];
    }
    
    // Tambahkan manual brands dari tabel brands (jika ada)
    $manual_brands = $this->db->select('
            b.id,
            b.name,
            b.category,
            "manual" as source,
            b.status,
            COALESCE(SUM(o.gmv), 0) as total_gmv,
            COUNT(DISTINCT o.order_id) as total_orders,
            COUNT(DISTINCT o.creator_username) as total_creators
        ')
        ->from('brands b')
        ->join('brand_products bp', 'b.id = bp.brand_id', 'left')
        ->join('affiliate_products ap', 'bp.product_id = ap.product_id', 'left')
        ->join('affiliate_orders o', 'ap.product_id = o.product_id AND ap.campaign_id = o.campaign_id', 'left')
        ->where('b.bd_id', $user_id)
        ->group_by('b.id')
        ->get()
        ->result();
    
    foreach ($manual_brands as $mb) {
        $all_brands[] = [
            'id' => $mb->id,
            'name' => $mb->name,
            'total_gmv' => floatval($mb->total_gmv ?? 0),
            'total_orders' => intval($mb->total_orders ?? 0),
            'total_creators' => intval($mb->total_creators ?? 0),
            'status' => $mb->status ?? 'PENDING',
            'source' => 'manual',
            'category' => $mb->category ?? ''
        ];
    }
    
    // Hitung total statistik berdasarkan filter tanggal
    $total_gmv = array_sum(array_column($all_brands, 'total_gmv'));
    $total_orders = array_sum(array_column($all_brands, 'total_orders'));
    $total_creators = array_sum(array_column($all_brands, 'total_creators'));
    
    $data = [
        'title' => 'Brands Management - Toopai BD',
        'active_menu' => 'brands',
        'brands' => $all_brands,
        'total_brands' => count($all_brands),
        'total_gmv' => $total_gmv,
        'total_orders' => $total_orders,
        'total_creators' => $total_creators,
        'filter_date' => $filter_date,
        'start_date' => $start_date,
        'end_date' => $end_date
    ];
    
    $this->load->view('templates/header', $data);
    $this->load->view('bd/brands', $data);
    $this->load->view('templates/footer');
}

    /**
 * Force add brand (override for brands being hunted by other BD)
 */
public function force_add_brand() {
    $this->output->set_content_type('application/json');
    
    $brand_name = $this->input->post('brand_name');
    $category = $this->input->post('category');
    $whatsapp_number = $this->input->post('whatsapp_number');
    $commission = $this->input->post('commission') ?: 0;
    
    if (empty($brand_name) || empty($category)) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Nama brand dan kategori harus diisi']));
    }
    
    $bd_id = $this->session->userdata('user_id');
    $bd_username = $this->session->userdata('username');
    $bd_fullname = $this->session->userdata('full_name');
    
    // CEK APAKAH BRAND SUDAH DEAL
    $existing_deal = $this->db->select('id')
        ->from('brands')
        ->where('name', $brand_name)
        ->where_in('status', ['CAMPAIGN_READY', 'ACTIVE', 'DEAL_CLOSED'])
        ->get()
        ->row();
    
    if ($existing_deal) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => "Brand '{$brand_name}' sudah di-deal. Tidak bisa ditambahkan."
        ]));
    }
    
    // Simpan brand baru (force add, meskipun ada yang pending)
    $brand_data = [
        'name' => $brand_name,
        'shop_name' => $brand_name,
        'category' => $category,
        'bd_id' => $bd_id,
        'whatsapp_number' => $whatsapp_number,
        'proposed_commission' => $commission,
        'status' => 'PENDING',
        'current_task' => 1,
        'input_by' => $bd_username,
        'input_by_name' => $bd_fullname,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $this->db->insert('brands', $brand_data);
    $brand_id = $this->db->insert_id();
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'message' => 'Brand berhasil ditambahkan!',
        'brand_id' => $brand_id,
        'brand' => $brand_name
    ]));
}
    public function brand_detail($brand_identifier) {
    // Cek apakah ini discovered brand (dari API)
    if (strpos($brand_identifier, 'discovered_') === 0) {
        // Ini adalah discovered brand dari API, tidak ada di database
        // Redirect ke halaman discover atau tampilkan error
        $this->session->set_flashdata('error', 'Discovered brands cannot be accessed directly. Please add this brand to your portfolio first.');
        redirect('bd/brands');
        return;
    }
    
    // Cari brand di database
    $brand = $this->Brand_model->get_brand_by_id($brand_identifier);
    
    if (!$brand) {
        show_404('Brand not found');
    }
    
    // Ambil produk dari brand
    $products = $this->Product_model->get_products_by_brand($brand->id);
    
    // Ambil creators dari affiliate_orders untuk brand ini
    $creators = $this->db->select('
            o.creator_username,
            COUNT(DISTINCT o.order_id) as total_orders,
            SUM(o.gmv) as total_gmv,
            SUM(o.actual_commission) as total_commission,
            MAX(o.order_time) as last_active
        ')
        ->from('affiliate_orders o')
        ->join('brand_products bp', 'o.product_id = bp.product_id', 'inner')
        ->where('bp.brand_id', $brand->id)
        ->where('o.creator_username IS NOT NULL')
        ->group_by('o.creator_username')
        ->order_by('total_gmv', 'DESC')
        ->get()
        ->result();
    
    // Ambil recent orders
    $recent_orders = $this->db->select('o.*')
        ->from('affiliate_orders o')
        ->join('brand_products bp', 'o.product_id = bp.product_id', 'inner')
        ->where('bp.brand_id', $brand->id)
        ->order_by('o.order_time', 'DESC')
        ->limit(20)
        ->get()
        ->result();
    
    $data = [
        'title' => $brand->name . ' - Brand Detail',
        'active_menu' => 'brands',
        'brand' => $brand,
        'products' => $products,
        'creators' => $creators,
        'recent_orders' => $recent_orders
    ];
    
    $this->load->view('templates/header', $data);
    $this->load->view('bd/brand_detail', $data);
    $this->load->view('templates/footer');
}
public function scout_match_brand() {
    $this->output->set_content_type('application/json');
    
    $brand_name = $this->input->post('brand_name');
    $category = $this->input->post('category');
    $whatsapp_number = $this->input->post('whatsapp_number');
    $email = $this->input->post('email');
    $seller_id = $this->input->post('seller_id');
    $commission = $this->input->post('commission') ?: 0;
    $open_commission_rate = $this->input->post('open_commission_rate') ?: 0;
    
    // 🔥 DEBUG LOG
    log_message('debug', 'scout_match_brand - POST data: ' . json_encode([
        'brand_name' => $brand_name,
        'category' => $category,
        'whatsapp_number' => $whatsapp_number,
        'email' => $email,
        'seller_id' => $seller_id,
        'commission' => $commission
    ]));
    
    if (empty($brand_name) || empty($category)) {
        return $this->output->set_output(json_encode([
            'success' => false, 
            'message' => 'Nama brand dan kategori harus diisi'
        ]));
    }
    
    $bd_id = $this->session->userdata('user_id');
    $bd_username = $this->session->userdata('username');
    $bd_fullname = $this->session->userdata('full_name');
    
    // ========== 🔥 CEK APAKAH BRAND SUDAH PERNAH DEAL ==========
    $existing_deal = $this->db->select('b.*, u.username as owner_username')
        ->from('brands b')
        ->join('users u', 'b.bd_id = u.id', 'left')
        ->where('b.name', $brand_name)
        ->where_in('b.status', ['CAMPAIGN_READY', 'ACTIVE', 'DEAL_CLOSED'])
        ->get()
        ->row();
    
    if ($existing_deal) {
        return $this->output->set_output(json_encode([
            'success' => false, 
            'already_deal' => true,
            'message' => "Brand '{$brand_name}' sudah di-deal oleh {$existing_deal->owner_username}. Anda tidak bisa menginput brand yang sudah deal."
        ]));
    }
    
    // ========== 🔥 CEK APAKAH BRAND SUDAH ADA MILIK BD YANG SAMA ==========
    $existing_my_brand = $this->db->get_where('brands', [
        'name' => $brand_name, 
        'bd_id' => $bd_id
    ])->row();
    
    if ($existing_my_brand) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'already_exists' => true,
            'my_duplicate' => true,
            'message' => "Anda sudah memiliki brand '{$brand_name}' di daftar hunting Anda. Tidak bisa menambahkan brand yang sama dua kali."
        ]));
    }
    
    // ========== 🔥 CEK BRAND DENGAN SHOP_NAME YANG SAMA (UNTUK AMBIL DATA) ==========
    // Cari brand dengan shop_name yang sama (case insensitive) yang sudah punya seller_id dan whatsapp
    $existing_brand_data = $this->db->select('id, name, shop_name, whatsapp_number, email, seller_id, status, bd_id')
        ->from('brands')
        ->where('LOWER(shop_name) =', strtolower($brand_name))
        ->where('whatsapp_number IS NOT NULL')
        ->where('whatsapp_number !=', '')
        ->where('seller_id IS NOT NULL')
        ->where('seller_id !=', '')
        ->order_by('updated_at', 'DESC')
        ->limit(1)
        ->get()
        ->row();
    
    // ========== 🔥 CEK APAKAH BRAND SUDAH ADA MILIK BD LAIN ==========
    // ========== 🔥 CEK APAKAH BRAND SUDAH ADA MILIK BD LAIN ==========
    // Ambil entry ORIGINAL (bukan duplikat) dari brand yang sama milik BD lain
    // Ini memastikan duplicate_of selalu menunjuk ke entry utama
    $existing_other_brand = $this->db->get_where('brands', [
        'name'     => $brand_name,
        'bd_id !=' => $bd_id,
        'duplicate_of' => NULL   // hanya entry original, bukan duplikat dari duplikat
    ])->row();

    // Fallback: jika semua entry punya duplicate_of (edge case), ambil yang paling lama
    if (!$existing_other_brand) {
        $existing_other_brand = $this->db->select('*')
            ->from('brands')
            ->where('name', $brand_name)
            ->where('bd_id !=', $bd_id)
            ->order_by('id', 'ASC')
            ->limit(1)
            ->get()
            ->row();
        // Kalau dia sendiri adalah duplikat, ikuti ke root original
        if ($existing_other_brand && !empty($existing_other_brand->duplicate_of)) {
            $root = $this->db->where('id', $existing_other_brand->duplicate_of)->get('brands')->row();
            if ($root) $existing_other_brand = $root;
        }
    }
    
    // ========== 🔥 PREPARE BRAND DATA ==========
    // PENTING: jangan copy field requirements/deal dari brand lain
    // Hanya field identitas kontak yang boleh di-share antar entry brand
    $brand_data = [
        'name'               => $brand_name,
        'shop_name'          => $brand_name,
        'category'           => $category,
        'bd_id'              => $bd_id,
        'whatsapp_number'    => $whatsapp_number,
        'email'              => $email,
        'seller_id'          => $seller_id,
        'proposed_commission' => $commission,
        'open_commission_rate' => $open_commission_rate,
        'status'             => 'PENDING',
        'current_task'       => 1,
        'input_by'           => $bd_username,
        'input_by_name'      => $bd_fullname,
        // Field requirements & deal selalu NULL/kosong untuk entry baru
        'deal_confirmed_at'       => NULL,
        'requirements_filled_at'  => NULL,
        'requirements_filled_by'  => NULL,
        'creator_level'           => NULL,
        'creator_gmv'             => NULL,
        'content_type'            => NULL,
        'sample_method'           => NULL,
        'campaign_notes'          => NULL,
        'owner_id'                => NULL,
        'created_at'             => date('Y-m-d H:i:s')
    ];
    
    // 🔥 FLAG UNTUK MENGETAHUI SUMBER DATA
    $data_source = 'new';
    $data_source_message = '';
    
    // 🔥 JIKA ADA BRAND EXISTING DENGAN SHOP_NAME SAMA DAN PUNYA DATA LENGKAP
    if ($existing_brand_data) {
        // Ambil data dari brand existing (yang sudah punya seller_id & whatsapp)
        $brand_data['whatsapp_number'] = $existing_brand_data->whatsapp_number;
        $brand_data['email'] = $existing_brand_data->email ?: $email;
        $brand_data['seller_id'] = $existing_brand_data->seller_id;
        $brand_data['shop_name'] = $existing_brand_data->shop_name ?: $brand_name;
        
        $data_source = 'existing_brand';
        $data_source_message = "📋 Data WhatsApp & seller_id otomatis diambil dari brand yang sudah ada (ID: {$existing_brand_data->id}).";
        
        log_message('info', "Brand '{$brand_name}' menggunakan data dari brand existing ID: {$existing_brand_data->id} (WA: {$existing_brand_data->whatsapp_number}, Seller: {$existing_brand_data->seller_id})");
    }
    // 🔥 JIKA TIDAK ADA BRAND EXISTING, TAPI USER MENGIRIMKAN SELLER_ID & WHATSAPP
    elseif (!empty($seller_id) && !empty($whatsapp_number)) {
        $data_source = 'user_input';
        $data_source_message = "✅ Data WhatsApp & seller_id dari input user.";
    }
    // 🔥 JIKA TIDAK ADA DATA SAMA SEKALI
    else {
        $data_source = 'new';
        $data_source_message = "🆕 Brand baru tanpa data kontak. Akan diproses oleh sistem.";
    }
    
    // Tambahkan informasi duplikat jika brand sudah ada milik BD lain
    if ($existing_other_brand) {
        $brand_data['is_duplicate'] = 1;
        $brand_data['duplicate_of'] = $existing_other_brand->id;
    } else {
        $brand_data['is_duplicate'] = 0;
    }
    
    // ========== 🔥 SIMPAN BRAND ==========
    $this->db->insert('brands', $brand_data);
    $brand_id = $this->db->insert_id();
    
    // ========== 🔥 CEK APAKAH PERLU MASUK QUEUE ==========
    $has_whatsapp = !empty($brand_data['whatsapp_number']);
    $has_seller_id = !empty($brand_data['seller_id']);
    
    if (!$has_whatsapp && !$has_seller_id) {
        // Tidak punya WA dan tidak punya seller_id → masuk search queue
        $this->db->insert('brand_search_queue', [
            'keyword' => $brand_name,
            'status' => 'pending',
            'attempts' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        log_message('info', "Brand '{$brand_name}' (ID: {$brand_id}) added to search queue (no WA, no seller_id)");
    } 
    elseif ($has_seller_id && !$has_whatsapp) {
        // Punya seller_id tapi tidak punya WA → masuk contact queue
        $this->db->insert('brand_contact_queue', [
            'seller_id' => $brand_data['seller_id'],
            'shop_name' => $brand_data['shop_name'] ?: $brand_name,
            'status' => 'pending',
            'attempts' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        log_message('info', "Brand '{$brand_name}' (ID: {$brand_id}) added to contact queue (has seller_id, no WA)");
    }
    
    // ========== 🔥 RESPONSE ==========
    $message = "✅ Brand '{$brand_name}' berhasil ditambahkan!";
    
    if ($data_source == 'existing_brand') {
        $message .= "\n\n{$data_source_message}";
        if (!empty($brand_data['whatsapp_number'])) {
            $message .= "\n WhatsApp: {$brand_data['whatsapp_number']}";
        }
        if (!empty($brand_data['seller_id'])) {
            $message .= "\n Seller ID: {$brand_data['seller_id']}";
        }
    }
    
    if ($existing_other_brand) {
        $owner_name = $existing_other_brand->input_by ?? $existing_other_brand->bd_username ?? 'BD lain';
        $message = "✅ Brand '{$brand_name}' berhasil ditambahkan!\n\n⚠️ PERINGATAN: Brand ini sedang di-hunt oleh {$owner_name}. Siapa yang deal duluan yang akan mendapatkan brand ini.";
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'is_duplicate' => true,
            'message' => $message,
            'brand_id' => $brand_id,
            'brand' => $brand_name,
            'warning' => true,
            'competing_with' => $owner_name,
            'data_source' => $data_source,
            'whatsapp_found' => $has_whatsapp,
            'seller_id_found' => $has_seller_id
        ]));
    }
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'message' => $message,
        'brand_id' => $brand_id,
        'brand' => $brand_name,
        'data_source' => $data_source,
        'whatsapp_found' => $has_whatsapp,
        'seller_id_found' => $has_seller_id,
        'existing_data_used' => ($data_source == 'existing_brand')
    ]));
}
/**
 * Helper: Parse sales string ke numeric
 * Contoh: "2,5JT sold" -> 2500000, "147,4RB sold" -> 147400, "717 sold" -> 717
 */
private function parseSalesToNumeric($sales_string) {
    if (empty($sales_string)) return 0;
    
    $sales_string = str_replace(' sold', '', $sales_string);
    $sales_string = str_replace(',', '.', $sales_string);
    
    $multiplier = 1;
    if (strpos($sales_string, 'JT') !== false) {
        $multiplier = 1000000;
        $sales_string = str_replace('JT', '', $sales_string);
    } elseif (strpos($sales_string, 'RB') !== false) {
        $multiplier = 1000;
        $sales_string = str_replace('RB', '', $sales_string);
    }
    
    $sales_float = floatval($sales_string);
    return intval($sales_float * $multiplier);
}
/**
 * Add discovered brand to BD portfolio
 */
public function add_discovered_brand() {
    $this->output->set_content_type('application/json');
    
    $brand_name = $this->input->post('brand_name');
    $category = $this->input->post('category');
    $commission = $this->input->post('commission') ?: 10;
    $bd_id = $this->session->userdata('user_id');
    
    if (!$brand_name) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Brand name required'
        ]));
    }
    
    // Cek apakah sudah ada
    $existing = $this->db->where('name', $brand_name)->get('brands')->row();
    
    if ($existing) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'already_exists' => true,
            'message' => 'Brand already exists!'
        ]));
    }
    
    // Simpan brand
    $brand_data = [
        'name' => $brand_name,
        'shop_name' => $brand_name,
        'category' => $category,
        'bd_id' => $bd_id,
        'proposed_commission' => $commission,
        'status' => 'PENDING',
        'source' => 'manual',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $this->db->insert('brands', $brand_data);
    $brand_id = $this->db->insert_id();
    
    // Link products dari shop_name ini ke brand_products
    $products = $this->db->select('product_id, campaign_id, product_name, price, image_url')
                        ->from('affiliate_products')
                        ->where('shop_name', $brand_name)
                        ->where('review_status', 'APPROVED')
                        ->get()
                        ->result();
    
    foreach ($products as $product) {
        $this->db->insert('brand_products', [
            'brand_id' => $brand_id,
            'product_id' => $product->product_id,
            'campaign_id' => $product->campaign_id,
            'product_name' => $product->product_name,
            'price' => $product->price,
            'image_url' => $product->image_url,
            'source' => 'api',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'message' => 'Brand added successfully!',
        'brand_id' => $brand_id,
        'products_linked' => count($products),
        'redirect' => base_url('bd/brand_detail/' . $brand_id)
    ]));
}

public function get_campaign_poster() {
    $this->output->set_content_type('application/json');
    
    $brand_id = $this->input->get('brand_id');
    if (!$brand_id) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Brand ID required'
        ]));
    }
    
    // Ambil campaign poster dari database
    $campaign = $this->db->select('campaign_image')
        ->from('affiliate_campaigns')
        ->where('brand_id', $brand_id)
        ->order_by('created_at', 'DESC')
        ->limit(1)
        ->get()
        ->row();
    
    if ($campaign && $campaign->campaign_image) {
        return $this->output->set_output(json_encode([
            'success' => true,
            'poster_url' => $campaign->campaign_image
        ]));
    }
    
    // Fallback ke logo brand jika ada
    $brand = $this->db->select('logo_url')
        ->from('brands')
        ->where('id', $brand_id)
        ->get()
        ->row();
    
    if ($brand && $brand->logo_url) {
        return $this->output->set_output(json_encode([
            'success' => true,
            'poster_url' => $brand->logo_url
        ]));
    }
    
    return $this->output->set_output(json_encode([
        'success' => false,
        'message' => 'No poster found'
    ]));
}
/**
 * Link products from discovered brand to brand_products
 */
private function link_discovered_products($shop_name, $brand_id) {
    // Cari semua produk dari shop_name ini
    $products = $this->db->select('product_id, campaign_id')
                         ->from('affiliate_products')
                         ->where('shop_name', $shop_name)
                         ->get()
                         ->result();
    
    foreach ($products as $product) {
        // Cek apakah sudah ter-link
        $existing = $this->db->where('brand_id', $brand_id)
                             ->where('product_id', $product->product_id)
                             ->get('brand_products')
                             ->row();
        
        if (!$existing) {
            $this->db->insert('brand_products', [
                'brand_id' => $brand_id,
                'product_id' => $product->product_id,
                'campaign_id' => $product->campaign_id,
                'source' => 'api',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
}
    // ========== CAMPAIGNS MENU ==========
   public function campaigns() {
    $campaigns = $this->db->select('
            campaign_id,
            campaign_name,
            status,
            start_date,
            end_date,
            total_gmv,
            total_orders,
            total_creators,
            last_sync
        ')
        ->from('affiliate_campaigns')
        ->order_by('created_at', 'DESC')
        ->get()
        ->result();
    
    // Hitung total products per campaign
    foreach ($campaigns as $camp) {
        $camp->total_products = $this->db->where('campaign_id', $camp->campaign_id)
                                         ->count_all_results('affiliate_products');
    }
    
    // Hitung stat untuk badges
    $ongoing_count = $this->db->where('status', 'ONGOING')
                              ->count_all_results('affiliate_campaigns');
    $completed_count = $this->db->where('status', 'COMPLETED')
                                ->count_all_results('affiliate_campaigns');
    
    $data = [
        'title' => 'Campaigns Management - Toopai BD',
        'active_menu' => 'campaigns',
        'campaigns' => $campaigns,
        'ongoing_count' => $ongoing_count,
        'completed_count' => $completed_count
    ];
    
    $this->load->view('templates/header', $data);
    $this->load->view('bd/campaigns', $data);
    $this->load->view('templates/footer');
}
    
   // ========== CAMPAIGN DETAIL ==========
public function campaign_detail($campaign_identifier) {
    // ðŸ”¥ Ambil filter tanggal dari request (default hari ini)
    $start_date = $this->input->get('start_date') ?: date('Y-m-d');
    $end_date = $this->input->get('end_date') ?: date('Y-m-d');
    
    // Cari campaign berdasarkan campaign_id
    $campaign = $this->db->where('campaign_id', $campaign_identifier)
                         ->get('affiliate_campaigns')
                         ->row();
    
    // Jika tidak ditemukan, coba cari berdasarkan id (int) sebagai fallback
    if (!$campaign && is_numeric($campaign_identifier)) {
        $campaign = $this->db->where('id', $campaign_identifier)
                             ->get('affiliate_campaigns')
                             ->row();
    }
    
    if (!$campaign) {
        show_404('Campaign not found');
    }
    
    // ðŸ”¥ Ambil products dari affiliate_products (tanpa filter tanggal)
    $products = $this->db->where('campaign_id', $campaign->campaign_id)
                         ->order_by('gmv', 'DESC')
                         ->limit(50)
                         ->get('affiliate_products')
                         ->result();
    
    // Ambil orders dengan filter tanggal
    $orders = $this->db->select('order_id, product_name, creator_username, gmv, estimated_commission, order_status, order_date_local, order_time')
                       ->from('affiliate_orders')
                       ->where('campaign_id', $campaign->campaign_id)
                        ->where('o.order_date_local', $today)
                       ->where('order_date_local <=', $end_date)
                       ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
                       ->order_by('order_time', 'DESC')
                       ->limit(100)
                       ->get()
                       ->result();
    
    // ðŸ”¥ Ambil top creators dengan filter tanggal
    $top_creators = $this->db->select('
            creator_username,
            COUNT(DISTINCT order_id) as total_orders,
            SUM(gmv) as total_gmv,
            SUM(estimated_commission) as total_commission
        ')
        ->from('affiliate_orders')
        ->where('campaign_id', $campaign->campaign_id)
        ->where('order_date_local >=', $start_date)
        ->where('order_date_local <=', $end_date)
        ->where('creator_username IS NOT NULL')
        ->where('creator_username !=', '')
        ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
        ->group_by('creator_username')
        ->order_by('total_gmv', 'DESC')
        ->limit(20)
        ->get()
        ->result();
    
    // ðŸ”¥ Hitung total GMV campaign dengan filter tanggal
    $total_gmv = $this->db->select('COALESCE(SUM(gmv), 0) as total')
                          ->from('affiliate_orders')
                          ->where('campaign_id', $campaign->campaign_id)
                          ->where('order_date_local >=', $start_date)
                          ->where('order_date_local <=', $end_date)
                          ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
                          ->get()
                          ->row()
                          ->total ?? 0;
    
    // ðŸ”¥ Hitung total orders dengan filter tanggal
    $total_orders = $this->db->where('campaign_id', $campaign->campaign_id)
                              ->where('order_date_local >=', $start_date)
                              ->where('order_date_local <=', $end_date)
                              ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
                              ->count_all_results('affiliate_orders');
    
    // ðŸ”¥ Hitung total creators dengan filter tanggal
    $total_creators = $this->db->select('COUNT(DISTINCT creator_username) as total')
                              ->from('affiliate_orders')
                              ->where('campaign_id', $campaign->campaign_id)
                              ->where('order_date_local >=', $start_date)
                              ->where('order_date_local <=', $end_date)
                              ->where('creator_username IS NOT NULL')
                              ->where('creator_username !=', '')
                              ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
                              ->get()
                              ->row()
                              ->total ?? 0;
    
    $data = [
        'title' => $campaign->campaign_name . ' - Campaign Detail',
        'active_menu' => 'campaigns',
        'campaign' => $campaign,
        'products' => $products,
        'orders' => $orders,
        'top_creators' => $top_creators,
        'total_gmv' => $total_gmv,
        'total_orders' => $total_orders,
        'total_creators' => $total_creators,
        'start_date' => $start_date,
        'end_date' => $end_date
    ];
    
    $this->load->view('templates/header', $data);
    $this->load->view('bd/campaign_detail', $data);
    $this->load->view('templates/footer');
}

    // ========== SYNC DATA ==========
    public function sync() {
        $this->sync_data();
        $this->session->set_flashdata('success', 'Data synced successfully from TikTok API');
        redirect('bd/dashboard');
    }

    // ========== TASK METHODS (6 task original) ==========
    
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

    // ========== PRIVATE METHODS ==========
    /**
 * Sync campaigns from TikTok API (AJAX)
 */
public function sync_campaigns() {
    $this->output->set_content_type('application/json');
    
    try {
        // Ambil data campaign dari API
        $campaigns_result = $this->jsm_api->get_ongoing_campaigns(['page_size' => 50]);
        
        if (!$campaigns_result['success']) {
            throw new Exception($campaigns_result['message'] ?? 'Failed to fetch campaigns');
        }
        
        $campaigns = $campaigns_result['data'];
        $synced_count = 0;
        
        foreach ($campaigns as $campaign_data) {
            // Simpan campaign ke database
            $campaign_id = $this->Campaign_model->sync_campaign($campaign_data, $this->api_type);
            
            if ($campaign_id) {
                $synced_count++;
                
                // Sync products untuk campaign ini
                $products_result = $this->jsm_api->get_campaign_products($campaign_data['id'], [
                    'page_size' => 100,
                    'review_status' => 'APPROVED'
                ]);
                
                if ($products_result['success'] && !empty($products_result['data'])) {
                    foreach ($products_result['data'] as $product) {
                        // Cari brand dari product
                        $brand_id = $this->Brand_model->sync_brand_from_product($product);
                        // Sync product
                        $this->Product_model->sync_product($product, $campaign_id, $brand_id);
                    }
                }
            }
        }
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'message' => "Successfully synced {$synced_count} campaigns",
            'campaigns' => $campaigns
        ]));
        
    } catch (Exception $e) {
        log_message('error', 'Sync campaigns error: ' . $e->getMessage());
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]));
    }
}

public function get_all_pending_products_for_brand() {
    $this->output->set_content_type('application/json');
    
    $brand_id = $this->input->post('brand_id');
    $brand_name = $this->input->post('brand_name');
    
    if (!$brand_id && !$brand_name) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Brand ID or Brand Name required',
            'products' => []
        ]));
    }
    
    try {
        // 🔥 Ambil brand name dari database jika hanya ada brand_id
        if ($brand_id && !$brand_name) {
            $brand = $this->db->select('name')->where('id', $brand_id)->get('brands')->row();
            $brand_name = $brand->name ?? '';
        }
        
        if (empty($brand_name)) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Brand name not found',
                'products' => []
            ]));
        }
        
        // 🔥 Ambil semua campaign yang ongoing
        $campaigns = $this->db->select('campaign_id, campaign_name')
                              ->where('status', 'ONGOING')
                              ->get('affiliate_campaigns')
                              ->result();
        
        // Jika tidak ada campaign, coba sync dulu
        if (empty($campaigns)) {
            // Trigger sync campaigns
            $this->sync_data();
            $campaigns = $this->db->select('campaign_id, campaign_name')
                                  ->where('status', 'ONGOING')
                                  ->get('affiliate_campaigns')
                                  ->result();
        }
        
        $all_products = [];
        
        foreach ($campaigns as $campaign) {
            // Panggil API TikTok untuk get campaign products dengan status PENDING
            $api_result = $this->jsm_api->get_campaign_products($campaign->campaign_id, [
                'page_size' => 50,
                'review_status' => 'PENDING'
            ]);
            
            // Log untuk debugging
            log_message('debug', 'Campaign ' . $campaign->campaign_id . ' - API success: ' . ($api_result['success'] ? 'yes' : 'no'));
            
            if ($api_result['success'] && !empty($api_result['data'])) {
                foreach ($api_result['data'] as $prod) {
                    $product_shop_name = $prod['shop_name'] ?? '';
                    
                    // 🔥 FILTER: Hanya produk dari brand yang dipilih
                    if (stripos($product_shop_name, $brand_name) === false) {
                        continue;
                    }
                    
                    // Extract data
                    $lowest_price = $prod['lowest_price']['amount'] ?? 0;
                    $highest_price = $prod['highest_price']['amount'] ?? 0;
                    
                    $partner_commission_rate = isset($prod['partner_commission_rate']) ? ($prod['partner_commission_rate'] / 100) : 0;
                    $creator_commission_rate = isset($prod['creator_commission_rate']) ? ($prod['creator_commission_rate'] / 100) : 0;
                    $total_commission_rate = isset($prod['total_commission_rate']) ? ($prod['total_commission_rate'] / 100) : 0;
                    $open_collaboration_commission_rate = isset($prod['open_collaboration_commission_rate']) ? ($prod['open_collaboration_commission_rate'] / 100) : 0;
                    
                    $all_products[] = [
                        'product_id' => $prod['id'],
                        'campaign_id' => $campaign->campaign_id,
                        'campaign_name' => $campaign->campaign_name,
                        'product_name' => $prod['name'] ?? '',
                        'price' => floatval($lowest_price ?: $highest_price),
                        'lowest_price' => floatval($lowest_price),
                        'highest_price' => floatval($highest_price),
                        'partner_commission_rate' => $partner_commission_rate,
                        'creator_commission_rate' => $creator_commission_rate,
                        'total_commission_rate' => $total_commission_rate,
                        'open_commission_rate' => $open_collaboration_commission_rate,
                        'shop_ads_rate' => $prod['shop_ads_commission_rate'] ?? 0,
                        'inventory' => intval($prod['inventory'] ?? 0),
                        'sample_quota' => intval($prod['sample_quota'] ?? 0),
                        'product_sales' => intval($prod['product_sales'] ?? 0),
                        'image_url' => $prod['main_image_url'] ?? '',
                        'shop_name' => $product_shop_name,
                        'category' => $prod['category']['name'] ?? '',
                        'review_status' => $prod['review_status'] ?? 'PENDING',
                        'is_available' => $prod['is_available'] ?? true
                    ];
                }
            }
        }
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'products' => $all_products,
            'total' => count($all_products),
            'brand_name' => $brand_name,
            'campaigns_checked' => count($campaigns)
        ]));
        
    } catch (Exception $e) {
        log_message('error', 'Error in get_all_pending_products_for_brand: ' . $e->getMessage());
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'products' => []
        ]));
    }
}
/**
 * Check if brand has ever submitted products for review
 */
public function check_brand_has_submitted_products() {
    $this->output->set_content_type('application/json');
    
    $brand_id = $this->input->post('brand_id');
    $brand_name = $this->input->post('brand_name');
    
    if (!$brand_id && !$brand_name) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'has_submitted' => false,
            'message' => 'Brand ID or Brand Name required'
        ]));
    }
    
    try {
        if ($brand_id && !$brand_name) {
            $brand = $this->db->select('name')->where('id', $brand_id)->get('brands')->row();
            $brand_name = $brand->name ?? '';
        }
        
        if (empty($brand_name)) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'has_submitted' => false,
                'message' => 'Brand name not found'
            ]));
        }
        
        // Cek di database apakah ada produk dari brand ini yang pernah diajukan
        $has_submitted = $this->db->select('COUNT(*) as total')
            ->from('affiliate_products')
            ->like('shop_name', $brand_name)
            ->where('review_status !=', '')
            ->get()
            ->row()
            ->total > 0;
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'has_submitted' => $has_submitted,
            'brand_name' => $brand_name
        ]));
        
    } catch (Exception $e) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'has_submitted' => false,
            'message' => $e->getMessage()
        ]));
    }
}
public function sync_campaigns_page() {
    $this->sync_data();
    $this->session->set_flashdata('success', 'Campaigns synced successfully from TikTok API');
    redirect('bd/campaigns');
}
    private function sync_data() {
    log_message('info', 'Starting BD data sync from TikTok API');
    
    // 1. Sync campaigns ke affiliate_campaigns
    $campaigns_result = $this->jsm_api->get_ongoing_campaigns(['page_size' => 50]);
    
    if ($campaigns_result['success'] && !empty($campaigns_result['data'])) {
        foreach ($campaigns_result['data'] as $campaign_data) {
            // Sync campaign ke affiliate_campaigns
            $existing = $this->db->where('campaign_id', $campaign_data['id'])
                                 ->get('affiliate_campaigns')
                                 ->row();
            
            $start_date = null;
            $end_date = null;
            
            if (isset($campaign_data['campaign_start_time'])) {
                $start_date = date('Y-m-d H:i:s', $campaign_data['campaign_start_time']);
            }
            if (isset($campaign_data['campaign_end_time'])) {
                $end_date = date('Y-m-d H:i:s', $campaign_data['campaign_end_time']);
            }
            
            $data = [
                'campaign_id' => $campaign_data['id'],
                'campaign_name' => $campaign_data['name'] ?? '',
                'status' => $campaign_data['status'] ?? 'ONGOING',
                'start_date' => $start_date,
                'end_date' => $end_date,
                'raw_data' => json_encode($campaign_data),
                'last_sync' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($existing) {
                $this->db->where('id', $existing->id)->update('affiliate_campaigns', $data);
                $campaign_db_id = $existing->id;
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                $this->db->insert('affiliate_campaigns', $data);
                $campaign_db_id = $this->db->insert_id();
            }
            
            // 2. Sync products ke affiliate_products
            $products_result = $this->jsm_api->get_campaign_products($campaign_data['id'], [
                'page_size' => 100,
                'review_status' => 'APPROVED'
            ]);
            
            if ($products_result['success'] && !empty($products_result['data'])) {
                foreach ($products_result['data'] as $product) {
                    $product_data = [
                        'product_id' => $product['id'],
                        'campaign_id' => $campaign_data['id'],
                        'product_name' => $product['name'] ?? $product['title'] ?? '',
                        'price' => $product['price'] ?? 0,
                        'commission_rate' => $product['open_collaboration_commission_rate'] ?? 0,
                        'sales_count' => $product['product_sales'] ?? 0,
                        'image_url' => $product['main_image_url'] ?? '',
                        'category' => $product['category_name'] ?? '',
                        'shop_name' => $product['shop_name'] ?? '',
                        'last_sync' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $existing_product = $this->db->where('product_id', $product['id'])
                                                ->where('campaign_id', $campaign_data['id'])
                                                ->get('affiliate_products')
                                                ->row();
                    
                    if ($existing_product) {
                        $this->db->where('id', $existing_product->id)->update('affiliate_products', $product_data);
                    } else {
                        $product_data['created_at'] = date('Y-m-d H:i:s');
                        $this->db->insert('affiliate_products', $product_data);
                    }
                }
            }
        }
    }
    
    log_message('info', 'BD data sync completed');
}
    
    private function get_campaign_gmv($campaign_id) {
        $result = $this->jsm_api->search_affiliate_orders([
            'campaign_id' => $campaign_id,
            'create_time_ge' => strtotime('-30 days'),
            'create_time_lt' => time(),
            'page_size' => 100
        ]);
        
        $total_gmv = 0;
        if ($result['success'] && !empty($result['data'])) {
            foreach ($result['data'] as $order) {
                $total_gmv += $order['affiliate_gmv'];
            }
        }
        return $total_gmv;
    }
    
    // ========== NEW METHODS FOR 4 TASK ==========
    
    /**
     * Get brand detail by ID
     */
 public function get_brand_detail() {
    $this->output->set_content_type('application/json');
    
    $brand_id = $this->input->post('brand_id');
    
    $this->db->select('b.*, u.username as bd_username, u.full_name as bd_name');
    $this->db->from('brands b');
    $this->db->join('users u', 'b.bd_id = u.id', 'left');
    $this->db->where('b.id', $brand_id);
    $brand = $this->db->get()->row();
    
    if ($brand) {
        // Tambahkan input_by jika ada
        if (!isset($brand->input_by) && isset($brand->bd_username)) {
            $brand->input_by = $brand->bd_username;
        }
        return $this->output->set_output(json_encode(['success' => true, 'data' => $brand]));
    }
    return $this->output->set_output(json_encode(['success' => false, 'message' => 'Brand not found']));
}
    /**
     * Update brand status (for task progression)
     */
public function update_brand_status() {
    $this->output->set_content_type('application/json');
    
    $brand_id = $this->input->post('brand_id');
    $status = $this->input->post('status');
    $commission = $this->input->post('commission');
    $samples = $this->input->post('samples');
    
    if (!$brand_id || !$status) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Missing required fields']));
    }
    
    // Ambil brand yang sedang diupdate
    $current_brand = $this->db->get_where('brands', ['id' => $brand_id])->row();
    
    if (!$current_brand) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Brand not found']));
    }
    
    // 🔥 CEK JIKA STATUS AKAN BERUBAH MENJADI DEAL (CAMPAIGN_READY atau ACTIVE)
    $is_deal_status = in_array($status, ['CAMPAIGN_READY', 'ACTIVE', 'DEAL_CLOSED']);
    
    if ($is_deal_status) {
        // 🔥 CEK APAKAH BRAND DENGAN NAMA SAMA SUDAH DEAL OLEH BD LAIN
        $existing_deal = $this->db->select('id, bd_id, status')
            ->from('brands')
            ->where('name', $current_brand->name)
            ->where_in('status', ['CAMPAIGN_READY', 'ACTIVE', 'DEAL_CLOSED'])
            ->where('id !=', $brand_id)
            ->get()
            ->row();
        
        if ($existing_deal) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'already_deal' => true,
                'message' => "Brand '{$current_brand->name}' sudah di-deal oleh BD lain. Anda tidak bisa deal lagi."
            ]));
        }
        
        // 🔥 HAPUS SEMUA BRAND DENGAN NAMA SAMA YANG MASIH PENDING (BUKAN MILIK BD YANG DEAL)
        $deleted_count = 0;
        $duplicate_brands = $this->db->select('id, bd_id, input_by')
            ->from('brands')
            ->where('name', $current_brand->name)
            ->where('status', 'PENDING')
            ->where('id !=', $brand_id)
            ->get()
            ->result();
        
        foreach ($duplicate_brands as $dup) {
            // Log siapa yang kehapus brandnya
            log_message('info', "Menghapus brand duplikat ID {$dup->id} (milik BD {$dup->bd_id} / {$dup->input_by}) karena brand '{$current_brand->name}' sudah di-deal oleh BD {$current_brand->bd_id}");
            
            // 🔥 HARD DELETE (hapus permanent)
            $this->db->where('id', $dup->id)->delete('brands');
            $deleted_count++;
        }
        
        if ($deleted_count > 0) {
            log_message('info', "Berhasil menghapus {$deleted_count} brand duplikat untuk '{$current_brand->name}'");
        }
    }
    
    $update_data = [
        'status' => $status,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Tentukan current_task berdasarkan status (3 TASK)
    $task_map = [
        'PENDING' => 1,
        'FOLLOW_UP' => 2,      
        'CAMPAIGN_READY' => 3, 
        'ACTIVE' => 4,         
        'COMPLETED' => 5
    ];
    
    if (isset($task_map[$status])) {
        $update_data['current_task'] = $task_map[$status];
    }
    
    if ($status == 'CAMPAIGN_READY') {
        $update_data['deal_closed_at'] = date('Y-m-d H:i:s');
    } elseif ($status == 'ACTIVE') {
        $update_data['campaign_launched_at'] = date('Y-m-d H:i:s');
    }
    
    if ($commission !== null && $commission !== '') {
        $update_data['proposed_commission'] = $commission;
    }
    if ($samples !== null && $samples !== '') {
        $update_data['samples_allocated'] = $samples;
    }
    
    $this->db->where('id', $brand_id);
    $this->db->update('brands', $update_data);
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'message' => $is_deal_status ? '✅ Deal berhasil! Brand menjadi milik Anda.' : 'Status updated successfully',
        'new_status' => $status,
        'new_task' => $update_data['current_task'] ?? null,
        'is_deal' => $is_deal_status,
        'deleted_duplicates' => $deleted_count ?? 0
    ]));
}

    
    /**
     * Send WhatsApp and log
     */
    public function send_whatsapp() {
    $this->output->set_content_type('application/json');
    
    $brand_id = $this->input->post('brand_id');
    $phone_number = $this->input->post('phone_number');
    $message = $this->input->post('message');
    $stage = $this->input->post('stage');
    
    if (empty($phone_number) || empty($message)) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Nomor dan pesan harus diisi']));
    }
    
    // Format nomor WhatsApp
    $phone = preg_replace('/[^0-9+]/', '', $phone_number);
    if (preg_match('/^0/', $phone)) {
        $phone = '+62' . substr($phone, 1);
    } elseif (!preg_match('/^\+/', $phone)) {
        $phone = '+' . $phone;
    }
    $cleanPhone = ltrim($phone, '+');
    
    // Simpan log WhatsApp
    $this->db->insert('whatsapp_logs', [
        'user_id' => $this->session->userdata('user_id'),
        'brand_id' => $brand_id,
        'phone_number' => $phone,
        'message' => $message,
        'status' => 'SENT',
        'sent_at' => date('Y-m-d H:i:s')
    ]);
    
    // ✅ UPDATE STATUS BRAND BERDASARKAN STAGE (tanpa ubah komisi)
    if ($stage == 1) {
        // Dari Task 1 Hunting -> pindah ke Task 2 (NEGOTIATING)
        $this->db->where('id', $brand_id);
        $this->db->update('brands', [
            'status' => 'NEGOTIATING', 
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        log_message('debug', 'Brand ID ' . $brand_id . ' status updated to NEGOTIATING');
    } elseif ($stage == 2) {
        // Dari Task 2 Deal -> pindah ke Task 3 (DEAL_CLOSED)
        $this->db->where('id', $brand_id);
        $this->db->update('brands', [
            'status' => 'DEAL_CLOSED', 
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    } elseif ($stage == 3) {
        // Dari Task 3 Setup -> pindah ke Task 4 (CAMPAIGN_READY)
        $this->db->where('id', $brand_id);
        $this->db->update('brands', [
            'status' => 'CAMPAIGN_READY', 
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    // Return URL untuk redirect ke WhatsApp
    $whatsapp_url = "https://wa.me/{$cleanPhone}?text=" . urlencode($message);
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'message' => 'Membuka WhatsApp...',
        'redirect_url' => $whatsapp_url,
        'phone' => $phone,
        'status_updated' => ($stage == 1) ? true : false
    ]));
}
    
    /**
     * Complete task and update brand status
     */
    public function complete_task() {
        $this->output->set_content_type('application/json');
        
        $stage = $this->input->post('stage');
        $brand_id = $this->input->post('brand_id');
        
        // Update status brand berdasarkan task
        if ($brand_id) {
            if ($stage == 2) {
                $status = 'DEAL_CLOSED';
            } elseif ($stage == 3) {
                $status = 'CAMPAIGN_READY';
            } elseif ($stage == 4) {
                $status = 'COMPLETED';
            } else {
                $status = 'NEGOTIATING';
            }
            
            $this->db->where('id', $brand_id);
            $this->db->update('brands', ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
        }
        
        // Update task progress
        $this->Task_progress_model->update_task_progress(
            $this->session->userdata('user_id'),
            $stage,
            'COMPLETED',
            ['brand_id' => $brand_id, 'completed_at' => date('Y-m-d H:i:s')]
        );
        
        return $this->output->set_output(json_encode(['success' => true, 'message' => 'Task completed!']));
    }
    
    /**
     * Log WhatsApp only (without redirect)
     */
    public function log_whatsapp_only() {
    $this->output->set_content_type('application/json');
    
    $brand_id = $this->input->post('brand_id');
    $phone_number = $this->input->post('phone_number');
    $message = $this->input->post('message');
    $stage = $this->input->post('stage');
    
    $phone = preg_replace('/[^0-9+]/', '', $phone_number);
    if (preg_match('/^0/', $phone)) {
        $phone = '+62' . substr($phone, 1);
    } elseif (!preg_match('/^\+/', $phone)) {
        $phone = '+' . $phone;
    }
    
    $this->db->insert('whatsapp_logs', [
        'user_id' => $this->session->userdata('user_id'),
        'brand_id' => $brand_id,
        'phone_number' => $phone,
        'message' => $message,
        'status' => 'SENT',
        'sent_at' => date('Y-m-d H:i:s')
    ]);
    
    return $this->output->set_output(json_encode(['success' => true]));
}

// ========== TASK 2: FOLLOW UP (BARU) ==========

/**
 * Get brand detail for Follow Up modal (Task 2)
 */
public function get_brand_followup_detail() {
    $this->output->set_content_type('application/json');
    
    $brand_id = $this->input->post('brand_id');
    
    if (!$brand_id) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Brand ID required']));
    }
    
    $brand = $this->db->select('b.*, u.username as bd_username')
        ->from('brands b')
        ->join('users u', 'b.bd_id = u.id', 'left')
        ->where('b.id', $brand_id)
        ->get()
        ->row();
    
    if (!$brand) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Brand not found']));
    }
    
    // Ambil open commission rate dari database
    $open_commission_rate = floatval($brand->open_commission_rate ?? 0);
    
    // 🔥 REKOMENDASI KOMISI
    if ($open_commission_rate > 0) {
        $recommended_min = $open_commission_rate + 2;
        $recommended_max = $open_commission_rate + 12;
    } else {
        // Open plan 0: rekomendasi 5% - 25%
        $recommended_min = 5;
        $recommended_max = 25;
    }
    
    // Batasi minimum
    if ($recommended_min < 3) $recommended_min = 3;
    
    // Current commission (ambil dari proposed_commission atau recommended_min)
    $current_commission = $brand->proposed_commission ?? $recommended_min;
    if ($current_commission < $recommended_min) $current_commission = $recommended_min;
    
    // 🔥 MAX FLEKSIBEL: Ambil dari database jika ada, atau pakai recommended_max
    $max_commission = $brand->commission_range_max ?? $recommended_max;
    if ($max_commission < $recommended_max) $max_commission = $recommended_max;
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'brand' => $brand,
        'open_commission_rate' => $open_commission_rate,
        'recommended_commission_min' => $recommended_min,
        'recommended_commission_max' => $recommended_max,
        'current_commission' => $current_commission,
        'max_commission' => $max_commission  // 🔥 KIRIM MAX FLEKSIBEL
    ]));
}
/**
 * Save Follow Up dengan campaign_id
 */
public function save_follow_up() {
    $this->output->set_content_type('application/json');
    
    $brand_id = $this->input->post('brand_id');
    $commission_min = $this->input->post('commission_min');
    $commission_max = $this->input->post('commission_max');
    
    $campaign_id = $this->input->post('campaign_id');
    $notes = $this->input->post('notes');
    
    if (!$brand_id || !$commission_min || !$commission_max) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Range komisi wajib diisi']));
    }
    
    // Ambil brand
    $brand = $this->db->select('name, whatsapp_number')->where('id', $brand_id)->get('brands')->row();
    
    // Cek apakah brand sudah registrasi
    $product_count = $this->db->select('COUNT(*) as total')
        ->from('affiliate_products')
        ->where('shop_name', $brand->name)
        ->get()
        ->row()
        ->total ?? 0;
    $has_products = $product_count > 0;
    
    // 🔥 UPDATE DATA BRAND
    $update_data = [
        'proposed_commission' => $commission_max, // Simpan maksimal sebagai komisi utama
        'commission_range_min' => $commission_min,
        'commission_range_max' => $commission_max,
        'campaign_id' => $campaign_id,
        'follow_up_notes' => $notes,
        'follow_up_at' => date('Y-m-d H:i:s'),
        'deal_confirmed_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Pindah ke CAMPAIGN_READY setelah deal dikonfirmasi (tidak perlu tunggu produk)
    // Produk akan disubmit oleh brand saat berada di Step 3
    $update_data['status']       = 'CAMPAIGN_READY';
    $update_data['current_task'] = 3;
    $message = '✅ Deal dikonfirmasi! Brand dipindahkan ke Setup Campaign (Task 3).';
    if ($has_products) {
        $message .= ' Brand sudah memiliki ' . $product_count . ' produk.';
    }
    
    $this->db->where('id', $brand_id);
    $this->db->update('brands', $update_data);
    
    // Log WhatsApp
    $this->db->insert('whatsapp_logs', [
        'user_id' => $this->session->userdata('user_id'),
        'brand_id' => $brand_id,
        'phone_number' => $brand->whatsapp_number ?? '',
        'message' => $notes,
        'status' => 'SENT',
        'sent_at' => date('Y-m-d H:i:s')
    ]);
    
    return $this->output->set_output(json_encode([
        'success'       => true,
        'message'       => $message,
        'has_products'  => $has_products,
        'product_count' => $product_count,
        'new_status'    => 'CAMPAIGN_READY'
    ]));
} 

/**
 * Check brand registration status (ada produk atau tidak)
 */
public function check_brand_registration() {
    $this->output->set_content_type('application/json');
    
    $brand_id = $this->input->post('brand_id');
    
    if (!$brand_id) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Brand ID required']));
    }
    
    $brand = $this->db->select('name, status')->where('id', $brand_id)->get('brands')->row();
    
    if (!$brand) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Brand not found']));
    }
    
    // 🔥 CEK PRODUK DI AFFILIATE_PRODUCTS
    $product_count = $this->db->select('COUNT(*) as total')
        ->from('affiliate_products')
        ->where('shop_name', $brand->name)
        ->get()
        ->row()
        ->total ?? 0;
    
    $has_products = $product_count > 0;
    
    // 🔥 JIKA MASIH DI FOLLOW_UP TAPI SUDAH ADA PRODUK, AUTO-UPDATE KE CAMPAIGN_READY
    // (fallback manual check — seharusnya sudah dipindahkan saat deal dikonfirmasi)
    if ($brand->status == 'FOLLOW_UP') {
        $deal_confirmed = $this->db->select('deal_confirmed_at')
            ->where('id', $brand_id)
            ->get('brands')->row()->deal_confirmed_at ?? null;
        if (!empty($deal_confirmed) || $has_products) {
            $this->db->where('id', $brand_id)
                     ->update('brands', [
                         'status'       => 'CAMPAIGN_READY',
                         'current_task' => 3,
                         'updated_at'   => date('Y-m-d H:i:s')
                     ]);
            $brand->status = 'CAMPAIGN_READY';
        }
    }
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'brand_name' => $brand->name,
        'status' => $brand->status,
        'has_products' => $has_products,
        'product_count' => $product_count,
        'is_registered' => $has_products,
        'message' => $has_products ? 'Brand sudah registrasi dan siap setup campaign' : 'Brand belum registrasi, menunggu registrasi campaign'
    ]));
}

// ========== TASK 3: SETUP CAMPAIGN (UPDATE DENGAN REKOMENDASI PRODUK & MULTI LINK) ==========

public function get_pending_products_with_recommendations() {
    $this->output->set_content_type('application/json');
    
    $brand_id = $this->input->post('brand_id');
    $brand_name = $this->input->post('brand_name');
    
    if (!$brand_id && !$brand_name) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Brand ID or Name required']));
    }
    
    try {
        // Cek kepemilikan brand sebelum load
        if ($brand_id) {
            $this->_check_brand_ownership($brand_id);
        }
        
        // Ambil brand name dan category dari database
        if ($brand_id && !$brand_name) {
            $brand = $this->db->select('name, category, status, owner_id, is_duplicate, duplicate_of')->where('id', $brand_id)->get('brands')->row();
            $brand_name = $brand->name ?? '';
            $brand_category = $brand->category ?? '';
        } else {
            $brand = $this->db->select('category, status, owner_id, is_duplicate, duplicate_of')->where('name', $brand_name)->get('brands')->row();
            $brand_category = $brand->category ?? '';
        }
        
        // 🔥 AMBIL PRODUK PENDING DENGAN FIELD LENGKAP
        $pending_products = $this->db->select('
            ap.product_id,
            ap.campaign_id,
            ap.product_name,
            ap.price,
            ap.lowest_price,
            ap.highest_price,
            ap.image_url,
            ap.shop_name,
            ap.category,
            ap.inventory,
            ap.sales_count as product_sales,
            ap.sample_quota,
            ap.sample_quantity,
            ap.review_status,
            ap.open_commission_rate,
            ap.partner_commission_rate,
            ap.creator_commission_rate,
            ap.total_commission_rate,
            ap.shop_ads as shop_ads_rate,
            ac.campaign_name,
            DATEDIFF(NOW(), ap.created_at) as days_since_created
        ')
        ->from('affiliate_products ap')
        ->join('affiliate_campaigns ac', 'ap.campaign_id = ac.campaign_id', 'left')
        ->where('ap.shop_name', $brand_name)
        ->where('ap.review_status', 'PENDING')
        ->order_by('ap.created_at', 'DESC')
        ->get()
        ->result();
        
        // 🔥 KONVERSI KOMISI DARI CENTS KE PERSEN & TAMBAHKAN FIELD LAIN
        foreach ($pending_products as $product) {
            // total_commission_rate (Total persentase komisi)
            if ($product->total_commission_rate > 100 && $product->total_commission_rate < 10000) {
                $product->total_commission_rate = $product->total_commission_rate / 100;
            }
            
            // open_commission_rate (vs kolaborasi terbuka)
            if ($product->open_commission_rate > 100 && $product->open_commission_rate < 10000) {
                $product->open_commission_rate = $product->open_commission_rate / 100;
            }
            
            // partner_commission_rate
            if ($product->partner_commission_rate > 100 && $product->partner_commission_rate < 10000) {
                $product->partner_commission_rate = $product->partner_commission_rate / 100;
            }
            
            // shop_ads_rate
            if ($product->shop_ads_rate > 100 && $product->shop_ads_rate < 10000) {
                $product->shop_ads_rate = $product->shop_ads_rate / 100;
            }
            
            // Jika kategori kosong, gunakan kategori brand
            if (empty($product->category) || $product->category === '' || $product->category === null) {
                $product->category = $brand_category;
            }
            
            // is_new: produk dalam 7 hari terakhir
            $product->is_new = ($product->days_since_created <= 7);
        }
        
        // 2. Ambil produk rekomendasi dari brand lain
        $recommendations = $this->get_product_recommendations_by_category($brand_category, $brand_name, 10);
        
        // 3. Hitung affiliate commission (Open Plan + 1%)
        $default_commission = 10;
        if (!empty($pending_products)) {
            $open_rate = floatval($pending_products[0]->open_commission_rate);
            if ($open_rate > 0) {
                $default_commission = $open_rate + 1;
            }
        }
        
        // 4. Cek apakah sudah ada approved products
        $has_approved = $this->db->select('COUNT(*) as total')
            ->from('affiliate_products')
            ->where('shop_name', $brand_name)
            ->where('review_status', 'APPROVED')
            ->get()
            ->row()
            ->total > 0;
        
        // Format produk pending untuk JavaScript
        $formatted_products = [];
        foreach ($pending_products as $p) {
              $affiliate_commission = floatval($p->open_commission_rate) + 1;
            $formatted_products[] = [
                'product_id' => $p->product_id,
                'campaign_id' => $p->campaign_id,
                'campaign_name' => $p->campaign_name,
                'product_name' => $p->product_name,
                'price' => floatval($p->price),
                'lowest_price' => floatval($p->lowest_price ?: $p->price),
                'highest_price' => floatval($p->highest_price ?: $p->price),
                'image_url' => $p->image_url,
                'shop_name' => $p->shop_name,
                'category' => !empty($p->category) ? $p->category : $brand_category,
                'inventory' => intval($p->inventory),
                'product_sales' => intval($p->product_sales ?? 0),
                'sample_quota' => intval($p->sample_quota),
                'sample_quantity' => intval($p->sample_quantity),
                'open_commission_rate' => floatval($p->open_commission_rate),
                'partner_commission_rate' => floatval($p->partner_commission_rate),
                'total_commission_rate' => floatval($p->total_commission_rate),
                'shop_ads_rate' => floatval($p->shop_ads_rate),
                'is_new' => $p->is_new,
                'days_since_created' => $p->days_since_created,
                'affiliate_commission' => $affiliate_commission,
            ];
        }
        
        // Ambil daftar BA yang pernah menghubungi brand ini
        $contacted_bas = [];
        if (isset($brand)) {
            $original_id = ($brand->is_duplicate && $brand->duplicate_of) ? $brand->duplicate_of : ($brand_id ?: $brand->id);
            $bas_query = $this->db->select('u.full_name, u.username')
                                 ->from('brands b')
                                 ->join('users u', 'b.bd_id = u.id')
                                 ->group_start()
                                     ->where('b.id', $original_id)
                                     ->or_where('b.duplicate_of', $original_id)
                                 ->group_end()
                                 ->get()
                                 ->result();
            foreach ($bas_query as $bq) {
                $contacted_bas[] = $bq->full_name . ' (@' . $bq->username . ')';
            }
            // hilangkan duplikasi nama jika ada
            $contacted_bas = array_values(array_unique($contacted_bas));
        }

        // Cek riwayat kontak untuk menentukan hak claim
        $user_id = $this->session->userdata('user_id');
        $can_claim = false;
        
        if (isset($brand) && $brand->status == 'NEED_CLAIM') {
            $original_id = ($brand->is_duplicate && $brand->duplicate_of) ? $brand->duplicate_of : ($brand_id ?: $brand->id);
            $contacted = $this->db->where('bd_id', $user_id)
                                  ->group_start()
                                      ->where('id', $original_id)
                                      ->or_where('duplicate_of', $original_id)
                                  ->group_end()
                                  ->count_all_results('brands');
            $can_claim = ($contacted > 0);
        }

        return $this->output->set_output(json_encode([
            'success' => true,
            'brand_status' => (isset($brand) && isset($brand->status)) ? $brand->status : '',
            'owner_id' => (isset($brand) && isset($brand->owner_id)) ? $brand->owner_id : null,
            'can_claim' => $can_claim,
            'contacted_bas' => $contacted_bas,
            'brand_name' => $brand_name,
            'brand_category' => $brand_category,
            'pending_products' => $formatted_products,
            'recommendations' => $recommendations,
            'default_affiliate_commission' => $default_commission,
            'has_approved' => $has_approved,
            'total_pending' => count($formatted_products)
        ]));
        
    } catch (Exception $e) {
        log_message('error', 'Error in get_pending_products_with_recommendations: ' . $e->getMessage());
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]));
    }
}

/**
 * Get product recommendations based on category (from brands table)
 */
private function get_product_recommendations_by_category($category, $exclude_brand_name = '', $limit = 10) {
    try {
        // Jika kategori kosong, cari produk dengan GMV tertinggi secara umum
        $this->db->select('
            ap.product_id,
            ap.product_name,
            ap.price,
            ap.image_url,
            ap.shop_name,
            ap.category,
            ap.open_commission_rate,
            ap.sales_count,
            COALESCE(SUM(o.gmv), 0) as total_gmv,
            COUNT(DISTINCT o.order_id) as total_orders
        ');
        $this->db->from('affiliate_products ap');
        $this->db->join('affiliate_orders o', 'ap.product_id = o.product_id AND ap.campaign_id = o.campaign_id', 'left');
        $this->db->where('ap.review_status', 'APPROVED');
        $this->db->where('ap.shop_name !=', $exclude_brand_name);
        
        // 🔥 Jika kategori tersedia, filter berdasarkan kategori
        if (!empty($category)) {
            $this->db->where('ap.category', $category);
        }
        
        $this->db->group_by(['ap.product_id', 'ap.product_name', 'ap.price', 'ap.image_url', 'ap.shop_name', 'ap.category', 'ap.open_commission_rate', 'ap.sales_count']);
        $this->db->order_by('total_gmv', 'DESC');
        $this->db->limit($limit);
        
        $products = $this->db->get()->result();
        
        // Jika tidak ada produk dengan kategori yang sama, cari produk dengan GMV tertinggi
        if (empty($products) && !empty($category)) {
            $this->db->select('
                ap.product_id,
                ap.product_name,
                ap.price,
                ap.image_url,
                ap.shop_name,
                ap.category,
                ap.open_commission_rate,
                ap.sales_count,
                COALESCE(SUM(o.gmv), 0) as total_gmv,
                COUNT(DISTINCT o.order_id) as total_orders
            ');
            $this->db->from('affiliate_products ap');
            $this->db->join('affiliate_orders o', 'ap.product_id = o.product_id AND ap.campaign_id = o.campaign_id', 'left');
            $this->db->where('ap.review_status', 'APPROVED');
            $this->db->where('ap.shop_name !=', $exclude_brand_name);
            $this->db->group_by(['ap.product_id', 'ap.product_name', 'ap.price', 'ap.image_url', 'ap.shop_name', 'ap.category', 'ap.open_commission_rate', 'ap.sales_count']);
            $this->db->order_by('total_gmv', 'DESC');
            $this->db->limit($limit);
            $products = $this->db->get()->result();
        }
        
        $recommendations = [];
        foreach ($products as $p) {
            // Konversi open_commission_rate dari cents ke persen
            $open_rate = floatval($p->open_commission_rate);
            $open_rate_percent = $open_rate > 0 ? $open_rate / 100 : 0;
            
            $recommendations[] = [
                'product_id' => $p->product_id,
                'product_name' => $p->product_name,
                'price' => floatval($p->price),
                'image_url' => $p->image_url,
                'shop_name' => $p->shop_name,
                'category' => $p->category,
                'open_commission_rate' => $open_rate_percent,
                'sales_count' => intval($p->sales_count),
                'total_gmv' => floatval($p->total_gmv),
                'total_orders' => intval($p->total_orders)
            ];
        }
        
        return $recommendations;
        
    } catch (Exception $e) {
        log_message('error', 'get_product_recommendations_by_category error: ' . $e->getMessage());
        return [];
    }
}
/**
 * Get product recommendations based on GMV (same category)
 * 
 * @param string $category Brand category
 * @param string $exclude_brand_name Exclude this brand's products
 * @param int $limit Max number of recommendations
 * @return array List of recommended products
 */
public function get_product_recommendations_by_gmv($category, $exclude_brand_name = '', $limit = 10) {
    try {
        $this->db->select('
            ap.product_id,
            ap.product_name,
            ap.price,
            ap.image_url,
            ap.shop_name,
            ap.category,
            ap.open_commission_rate,
            ap.sales_count,
            COALESCE(SUM(o.gmv), 0) as total_gmv,
            COUNT(DISTINCT o.order_id) as total_orders
        ');
        $this->db->from('affiliate_products ap');
        $this->db->join('affiliate_orders o', 'ap.product_id = o.product_id AND ap.campaign_id = o.campaign_id', 'left');
        $this->db->where('ap.review_status', 'APPROVED');
        $this->db->where('ap.shop_name !=', $exclude_brand_name);
        
        if (!empty($category)) {
            $this->db->where('ap.category', $category);
        }
        
        $this->db->group_by(['ap.product_id', 'ap.product_name', 'ap.price', 'ap.image_url', 'ap.shop_name', 'ap.category', 'ap.open_commission_rate', 'ap.sales_count']);
        $this->db->order_by('total_gmv', 'DESC');
        $this->db->limit($limit);
        
        $products = $this->db->get()->result();
        
        $recommendations = [];
        foreach ($products as $p) {
            $recommendations[] = [
                'product_id' => $p->product_id,
                'product_name' => $p->product_name,
                'price' => floatval($p->price),
                'image_url' => $p->image_url,
                'shop_name' => $p->shop_name,
                'category' => $p->category,
                'open_commission_rate' => floatval($p->open_commission_rate),
                'sales_count' => intval($p->sales_count),
                'total_gmv' => floatval($p->total_gmv),
                'total_orders' => intval($p->total_orders)
            ];
        }
        
        return $recommendations;
        
    } catch (Exception $e) {
        log_message('error', 'get_product_recommendations_by_gmv error: ' . $e->getMessage());
        return [];
    }
}


public function get_brand_requirements() {
    $this->output->set_content_type('application/json');
    
    $brand_id = $this->input->post('brand_id');
    
    if (!$brand_id) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Brand ID required']));
    }
    
    $brand = $this->db->select('
            creator_level, 
            creator_gmv, 
            content_type, 
            sample_method, 
            campaign_notes, 
            requirements_filled_by, 
            requirements_filled_at
        ')
        ->where('id', $brand_id)
        ->get('brands')
        ->row();
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'data' => $brand
    ]));
}

/**
 * Save brand requirements (untuk staff BA)
 */
public function save_brand_requirements() {
    $this->output->set_content_type('application/json');

    $user_id     = $this->session->userdata('user_id');
    $brand_id    = $this->input->post('brand_id');
    $creator_level  = $this->input->post('creator_level');
    $creator_gmv    = $this->input->post('creator_gmv');
    $content_type   = $this->input->post('content_type');
    $sample_method  = $this->input->post('sample_method');
    $campaign_notes = $this->input->post('campaign_notes');

    if (!$brand_id || !$creator_level || !$creator_gmv || !$content_type || !$sample_method) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Semua field requirement wajib diisi'
        ]));
    }

    // ========== 🔥 VALIDASI: BRAND PERLU DI-CLAIM DULU SEBELUM ISI REQUIREMENTS ==========
    $brand = $this->db->select('id, status, owner_id, bd_id, is_duplicate, duplicate_of')
        ->where('id', $brand_id)
        ->get('brands')
        ->row();

    if (!$brand) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Brand tidak ditemukan.'
        ]));
    }

    if ($brand->status === 'NEED_CLAIM') {
        // Jika brand NEED_CLAIM, hanya BD yang sudah menjadi owner yang boleh isi requirements
        if (empty($brand->owner_id)) {
            return $this->output->set_output(json_encode([
                'success'      => false,
                'need_claim'   => true,
                'message'      => 'Brand ini perlu di-claim terlebih dahulu sebelum mengisi requirements. Klik tombol Claim Brand.'
            ]));
        }
        if ($brand->owner_id != $user_id) {
            return $this->output->set_output(json_encode([
                'success'    => false,
                'need_claim' => true,
                'message'    => 'Brand ini sudah di-claim oleh BA lain. Anda tidak bisa mengisi requirements.'
            ]));
        }
    }

    // ========== 🔥 VALIDASI: HANYA BD PEMILIK BRAND INI YANG BOLEH ISI ==========
    // BD hanya boleh update entry brand miliknya sendiri
    if ($brand->bd_id != $user_id) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Anda tidak memiliki akses untuk mengisi requirements brand ini.'
        ]));
    }

    $update_data = [
        'creator_level'          => $creator_level,
        'creator_gmv'            => $creator_gmv,
        'content_type'           => $content_type,
        'sample_method'          => $sample_method,
        'campaign_notes'         => $campaign_notes,
        'requirements_filled_by' => $user_id,
        'requirements_filled_at' => date('Y-m-d H:i:s'),
        'updated_at'             => date('Y-m-d H:i:s')
    ];

    $this->db->where('id', $brand_id);
    $this->db->update('brands', $update_data);

    // Log activity
    $this->load->model('User_log_model');
    $this->User_log_model->log(
        $user_id,
        $this->session->userdata('username'),
        'BD',
        'SAVE_BRAND_REQUIREMENTS',
        "Saved requirements for brand ID {$brand_id}: Level={$creator_level}, GMV={$creator_gmv}, Content={$content_type}"
    );

    return $this->output->set_output(json_encode([
        'success' => true,
        'message' => 'Requirement berhasil disimpan'
    ]));
}
/**
 * Generate MULTI LINK (batch) untuk produk yang dipilih (termasuk rekomendasi)
 */
public function generate_multi_link() {
    $this->output->set_content_type('application/json');
    
    $user_id = $this->session->userdata('user_id');
    $username = $this->session->userdata('username');
    
    if ($user_id != 1) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Hanya Head BA yang dapat generate multi link afiliasi.'
        ]));
    }
    
    $campaign_id = $this->input->post('campaign_id');
    $product_ids = $this->input->post('product_ids');
    $brand_id = $this->input->post('brand_id');
    $brand_name = $this->input->post('brand_name');
    $commission_rates = $this->input->post('commission_rates');
    
    if (!$campaign_id || empty($product_ids)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Campaign ID and Product IDs required'
        ]));
    }
    
    // Decode product_ids
    if (is_string($product_ids)) {
        $decoded = json_decode($product_ids, true);
        if (is_array($decoded)) {
            $product_ids = $decoded;
        } else {
            $product_ids = explode(',', $product_ids);
        }
    }
    
    if (!is_array($product_ids)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Product IDs must be an array'
        ]));
    }
    
    $product_ids = array_values(array_filter($product_ids));
    
    if (count($product_ids) === 0) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'No valid product IDs provided'
        ]));
    }
    
    // Decode commission rates
    $commission_rates_array = [];
    if (!empty($commission_rates)) {
        if (is_string($commission_rates)) {
            $decoded_rates = json_decode($commission_rates, true);
            if (is_array($decoded_rates)) {
                $commission_rates_array = $decoded_rates;
            }
        } elseif (is_array($commission_rates)) {
            $commission_rates_array = $commission_rates;
        }
    }
    
    // Pastikan jumlah commission rates sesuai
    while (count($commission_rates_array) < count($product_ids)) {
        $commission_rates_array[] = 10;
    }
    
    try {
        $category_asset_cipher = $this->jsm_api->default_cipher ?? '';
        
        log_message('debug', 'Generate Multi Link - Campaign: ' . $campaign_id);
        log_message('debug', 'Generate Multi Link - Products: ' . json_encode($product_ids));
        log_message('debug', 'Generate Multi Link - Cipher: ' . $category_asset_cipher);
        
        $api_result = $this->jsm_api->generate_multi_affiliate_links(
            $campaign_id, 
            $product_ids,
            $category_asset_cipher
        );
        
        if (!$api_result['success']) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => $api_result['message'] ?? 'Gagal generate multi link',
                'code' => $api_result['code'] ?? null
            ]));
        }
        
        $group_id = 'multi_' . $campaign_id . '_' . time() . '_' . uniqid();
        $generated_links = [];
        
        if (isset($api_result['data']['promotion_links']) && is_array($api_result['data']['promotion_links'])) {
            foreach ($api_result['data']['promotion_links'] as $index => $link_data) {
                $link_id = md5($campaign_id . $link_data['product_id'] . time() . rand());
                
                // Ambil komisi untuk produk ini
                $product_commission = isset($commission_rates_array[$index]) ? floatval($commission_rates_array[$index]) : 10;
                
                // Ambil nama produk
                $product_name = '';
                $product = $this->db->select('product_name')
                    ->from('affiliate_products')
                    ->where('product_id', $link_data['product_id'])
                    ->limit(1)
                    ->get()
                    ->row();
                if ($product) {
                    $product_name = $product->product_name;
                }
                
                $insert_data = [
                    'link_id' => $link_id,
                    'campaign_id' => $campaign_id,
                    'campaign_name' => $this->input->post('campaign_name') ?? '',
                    'product_id' => $link_data['product_id'],
                    'product_name' => $product_name,
                    'affiliate_link' => $link_data['promotion_link'],
                    'commission_rate' => $product_commission,
                    'open_commission_rate' => $product_commission - 1,
                    'created_by' => $user_id,
                    'created_by_name' => $username,
                    'status' => 'ACTIVE',
                    'link_type' => 'multi',
                    'group_id' => $group_id,
                    'expire_at' => $link_data['expire_at'] ?? null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $this->db->insert('bd_affiliate_links', $insert_data);
                
                $generated_links[] = [
                    'product_id' => $link_data['product_id'],
                    'product_name' => $product_name,
                    'affiliate_link' => $link_data['promotion_link'],
                    'commission_rate' => $product_commission
                ];
            }
        }
        
        // Log jika ada failed product IDs
        if (!empty($api_result['data']['failed_product_ids'])) {
            log_message('warning', 'Multi Link - Failed products: ' . json_encode($api_result['data']['failed_product_ids']));
        }
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'message' => 'Multi link berhasil digenerate!',
            'group_id' => $group_id,
            'total_links' => count($generated_links),
            'generated_links' => $generated_links,
            'failed_product_ids' => $api_result['data']['failed_product_ids'] ?? []
        ]));
        
    } catch (Exception $e) {
        log_message('error', 'generate_multi_link error: ' . $e->getMessage());
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]));
    }
}

/**
 * Get brands for Task 2 (FOLLOW_UP status)
 */
public function get_followup_brands() {
    $this->output->set_content_type('application/json');
    
    $user_id = $this->session->userdata('user_id');
    $is_supervisor = ($user_id == 1);
    
    $this->db->select('b.*, u.username as bd_username')
        ->from('brands b')
        ->join('users u', 'b.bd_id = u.id', 'left')
        ->where('b.status', 'FOLLOW_UP');
    
    if (!$is_supervisor) {
        $this->db->where('b.bd_id', $user_id);
    }
    
    $brands = $this->db->order_by('b.follow_up_at', 'DESC')->get()->result();
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'brands' => $brands,
        'count' => count($brands)
    ]));
}

/**
 *END V2 DASHBOARD
 */
    
    /**
     * Get message template for WhatsApp
     */
    public function get_message_template() {
        $this->output->set_content_type('application/json');
        
        $stage = $this->input->post('stage');
        $brand_id = $this->input->post('brand_id');
        
        $brand = $this->db->get_where('brands', ['id' => $brand_id])->row();
        
        $messages = [
            1 => "Hi {$brand->name} Team,\n\nKami dari Toopai ingin menawarkan kerjasama affiliate untuk produk Anda dengan komisi {$brand->proposed_commission}%.\n\nTertarik untuk diskusi lebih lanjut?",
            2 => "Hi {$brand->name} Team,\n\nKami setuju dengan proposal kerjasama. Komisi {$brand->proposed_commission}% dan 100 pcs sample.\n\nSiap launch campaign!",
            3 => "Hi {$brand->name} Team,\n\nCampaign sudah siap! Sample akan segera kami kirim. Link afiliasi sudah kami generate.\n\nTerima kasih!",
            4 => "Hi {$brand->name} Team,\n\nPerforma campaign sangat baik! Total GMV mencapai Rp 125,000,000 dengan ROAS 4.2x.\n\nKami menawarkan upsell dengan komisi 20% untuk bulan depan."
        ];
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'content' => $messages[$stage] ?? $messages[1]
        ]));
    }
    
    // ========== GET BRAND DETAIL LENGKAP (DENGAN PRODUK & WHATSAPP LOGS) ==========
public function get_brand_detail_full() {
    $this->output->set_content_type('application/json');
    
    $brand_id = $this->input->post('brand_id');
    
    // Ambil data brand
    $brand = $this->db->get_where('brands', ['id' => $brand_id])->row();
    if (!$brand) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Brand not found']));
    }
    
    // Ambil produk brand
    $products = $this->db->select('p.*')
        ->from('brand_products bp')
        ->join('products p', 'p.id = bp.product_id')
        ->where('bp.brand_id', $brand_id)
        ->get()
        ->result();
    
    // Ambil riwayat WhatsApp
    $whatsapp_logs = $this->db->select('*')
        ->from('whatsapp_logs')
        ->where('brand_id', $brand_id)
        ->order_by('sent_at', 'DESC')
        ->limit(10)
        ->get()
        ->result();
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'data' => [
            'id' => $brand->id,
            'name' => $brand->name,
            'category' => $brand->category,
            'whatsapp_number' => $brand->whatsapp_number,
            'proposed_commission' => $brand->proposed_commission,
            'samples_allocated' => $brand->samples_allocated,
            'status' => $brand->status,
            'products' => $products,
            'whatsapp_logs' => $whatsapp_logs
        ]
    ]));
}

// ========== GET BRAND PRODUCTS ==========
public function get_brand_products() {
    $this->output->set_content_type('application/json');
    
    $brand_id = $this->input->post('brand_id');
    
    if (!$brand_id) {
        return $this->output->set_output(json_encode([
            'success' => false, 
            'message' => 'Brand ID required',
            'data' => []
        ]));
    }
    
    // Ambil produk dari tabel brand_products
    $products = $this->db->select('*')
        ->from('brand_products')
        ->where('brand_id', $brand_id)
        ->order_by('id', 'DESC')
        ->get()
        ->result();
    
    // Format response
    $formatted_products = [];
    foreach ($products as $p) {
        $formatted_products[] = [
            'id' => $p->product_id,
            'name' => $p->product_name,
            'price' => (float)$p->price,
            'image' => $p->image_url,
            'affiliate_link' => $p->affiliate_link,
            'db_id' => $p->id
        ];
    }
    
    // Debug log
    log_message('debug', 'get_brand_products - brand_id: ' . $brand_id . ', count: ' . count($formatted_products));
    
    return $this->output->set_output(json_encode([
        'success' => true, 
        'data' => $formatted_products,
        'count' => count($formatted_products)
    ]));
}


// ========== ADD BRAND PRODUCT ==========
public function add_brand_product() {
    $this->output->set_content_type('application/json');
    
    $brand_id = $this->input->post('brand_id');
    $product_name = $this->input->post('product_name');
    $product_price = $this->input->post('product_price');
    $commission_rate = $this->input->post('commission_rate');
    $product_link = $this->input->post('product_link');
    
    if (!$brand_id || !$product_name) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Missing required fields']));
    }
    
    $product_data = [
        'product_id' => 'manual_' . time() . '_' . rand(1000, 9999),
        'product_name' => $product_name,
        'price' => $product_price ?: 0,
        'commission_rate' => $commission_rate ?: 10,
        'affiliate_link' => $product_link,
        'source' => 'manual'
    ];
    
    $result = $this->Product_model->assign_to_brand($brand_id, $product_data);
    
    if ($result) {
        return $this->output->set_output(json_encode([
            'success' => true,
            'message' => 'Product added successfully',
            'insert_id' => $result
        ]));
    } else {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Failed to add product'
        ]));
    }
}

// ========== REMOVE BRAND PRODUCT ==========
public function remove_brand_product() {
    $this->output->set_content_type('application/json');
    
    $brand_id = $this->input->post('brand_id');
    $product_id = $this->input->post('product_id');
    
    if (!$brand_id || !$product_id) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Missing required fields']));
    }
    
    $this->db->where('brand_id', $brand_id);
    $this->db->where('product_id', $product_id);
    $result = $this->db->delete('brand_products');
    
    if ($result) {
        log_message('debug', 'Product removed: ' . $product_id . ' for brand ' . $brand_id);
        return $this->output->set_output(json_encode(['success' => true, 'message' => 'Product removed']));
    } else {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Failed to remove product']));
    }
}
// ========== GENERATE AFFILIATE LINK FOR PRODUCT ==========
public function generate_affiliate_link_for_product() {
    $this->output->set_content_type('application/json');
    
    $product_id = $this->input->post('product_id');
    $commission = $this->input->post('commission') ?: 10;
    
    $campaigns = $this->Campaign_model->get_ongoing_campaigns();
    if (empty($campaigns)) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'No active campaign']));
    }
    
    $campaign_id = $campaigns[0]->id;
    $link_result = $this->jsm_api->generate_promotion_link($campaign_id, $product_id, $commission);
    
    if ($link_result['success']) {
        $this->db->where('id', $product_id);
        $this->db->update('products', ['affiliate_link' => $link_result['link']]);
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'link' => $link_result['link']
        ]));
    }
    
    return $this->output->set_output(json_encode(['success' => false, 'message' => $link_result['message']]));
}
// ========== SEARCH BRANDS PER TASK ==========

/**
 * Search brands di Task 1 (HUNTING) - status PENDING
 */
public function search_hunting_brands() {
    $this->output->set_content_type('application/json');
    
    $keyword = $this->input->post('keyword');
    $user_id = $this->session->userdata('user_id');
    $is_supervisor = ($user_id == 1);
    
    // 🔥 DEBUG: Log keyword yang diterima
    log_message('debug', 'search_hunting_brands - keyword: ' . $keyword);
    
    if (empty($keyword)) {
        return $this->output->set_output(json_encode([
            'success' => false, 
            'message' => 'Keyword required',
            'brands' => [],
            'total' => 0
        ]));
    }
    
    try {
        $this->db->select('b.*, u.username as bd_username, u.full_name as bd_name, b.input_by, b.input_by_name')
            ->from('brands b')
            ->join('users u', 'b.bd_id = u.id', 'left')
            ->where('b.status', 'PENDING')
            ->group_start()
                ->like('b.name', $keyword)
                ->or_like('b.shop_name', $keyword)
                ->or_like('b.whatsapp_number', $keyword)
                ->or_like('b.input_by', $keyword)
            ->group_end()
            ->order_by('b.created_at', 'DESC')
            ->limit(100);
        
        if (!$is_supervisor) {
            $this->db->group_start()
                ->where('b.bd_id', $user_id)
                ->or_group_start()
                    ->where('b.status', 'NEED_CLAIM')
                    ->where("b.id IN (SELECT DISTINCT(duplicate_of) FROM brands WHERE bd_id = $user_id AND is_duplicate = 1)", NULL, FALSE)
                ->group_end()
            ->group_end();
        }
        
        $brands = $this->db->get()->result();
        
        // 🔥 DEBUG: Log jumlah hasil
        log_message('debug', 'search_hunting_brands - found: ' . count($brands));
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'brands' => $brands,
            'total' => count($brands),
            'keyword' => $keyword
        ]));
        
    } catch (Exception $e) {
        log_message('error', 'search_hunting_brands error: ' . $e->getMessage());
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'brands' => [],
            'total' => 0
        ]));
    }
}

/**
 * Search brands di Task 2 (FOLLOW UP) - status FOLLOW_UP
 */
public function search_followup_brands() {
    $this->output->set_content_type('application/json');
    
    $keyword = $this->input->post('keyword');
    $user_id = $this->session->userdata('user_id');
    $is_supervisor = ($user_id == 1);
    
    if (empty($keyword)) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Keyword required']));
    }
    
    $this->db->select('b.*, u.username as bd_username, u.full_name as bd_name, b.input_by, b.input_by_name')
        ->from('brands b')
        ->join('users u', 'b.bd_id = u.id', 'left')
        ->where('b.status', 'FOLLOW_UP')
        ->group_start()
            ->like('b.name', $keyword)
            ->or_like('b.shop_name', $keyword)
            ->or_like('b.whatsapp_number', $keyword)
            ->or_like('b.input_by', $keyword)
        ->group_end()
        ->order_by('b.follow_up_at', 'DESC')
        ->limit(100);
    
    if (!$is_supervisor) {
        $this->db->where('b.bd_id', $user_id);
    }
    
    $brands = $this->db->get()->result();
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'brands' => $brands,
        'total' => count($brands),
        'keyword' => $keyword
    ]));
}

/**
 * Search brands di Task 3 (SETUP CAMPAIGN) - status CAMPAIGN_READY
 */
public function search_setup_brands() {
    $this->output->set_content_type('application/json');
    
    $keyword = $this->input->post('keyword');
    $user_id = $this->session->userdata('user_id');
    $is_supervisor = ($user_id == 1);
    
    if (empty($keyword)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Keyword required',
            'brands' => [],
            'total' => 0
        ]));
    }
    
    try {
        $brands = [];
        $seen_ids = [];
        
        // ============================================================
        // 1. AMBIL BRAND CAMPAIGN_READY
        // ============================================================
        $this->db->select('b.*, u.username as bd_username, u.full_name as bd_name, b.input_by, b.input_by_name')
            ->from('brands b')
            ->join('users u', 'b.bd_id = u.id', 'left')
            ->where_in('b.status', ['CAMPAIGN_READY', 'NEED_CLAIM'])
            ->group_start()
                ->like('b.name', $keyword)
                ->or_like('b.shop_name', $keyword)
                ->or_like('b.whatsapp_number', $keyword)
                ->or_like('b.input_by', $keyword)
            ->group_end()
            ->order_by('b.updated_at', 'DESC')
            ->limit(100);
        
        if (!$is_supervisor) {
            $this->db->group_start()
                ->where('b.bd_id', $user_id)
                ->or_group_start()
                    ->where('b.is_duplicate', 0)
                    ->where('b.status', 'NEED_CLAIM')
                    ->where("b.id IN (SELECT DISTINCT(duplicate_of) FROM brands WHERE bd_id = $user_id AND is_duplicate = 1)", NULL, FALSE)
                ->group_end()
            ->group_end();
        }
        
        $campaign_ready = $this->db->get()->result();
        
        foreach ($campaign_ready as $brand) {
            if (!in_array($brand->id, $seen_ids)) {
                $brands[] = $brand;
                $seen_ids[] = $brand->id;
            }
        }
        
        // ============================================================
        // 2. AMBIL BRAND ACTIVE YANG PUNYA PRODUK PENDING
        // ============================================================
        $this->db->select('b.*, u.username as bd_username, u.full_name as bd_name, b.input_by, b.input_by_name')
            ->from('brands b')
            ->join('users u', 'b.bd_id = u.id', 'left')
            ->join('affiliate_products ap', 'b.name = ap.shop_name AND ap.review_status = "PENDING"', 'inner')
            ->where('b.status', 'ACTIVE')
            ->group_start()
                ->like('b.name', $keyword)
                ->or_like('b.shop_name', $keyword)
                ->or_like('b.whatsapp_number', $keyword)
                ->or_like('b.input_by', $keyword)
            ->group_end()
            ->group_by('b.id')
            ->order_by('b.updated_at', 'DESC')
            ->limit(100);
        
        if (!$is_supervisor) {
            $this->db->group_start()
                ->where('b.bd_id', $user_id)
                ->or_group_start()
                    ->where('b.status', 'NEED_CLAIM')
                    ->where("b.id IN (SELECT DISTINCT(duplicate_of) FROM brands WHERE bd_id = $user_id AND is_duplicate = 1)", NULL, FALSE)
                ->group_end()
            ->group_end();
        }
        
        $active_with_pending = $this->db->get()->result();
        
        foreach ($active_with_pending as $brand) {
            if (!in_array($brand->id, $seen_ids)) {
                $brands[] = $brand;
                $seen_ids[] = $brand->id;
            }
        }
        
        // ============================================================
        // 3. TAMBAHKAN INFORMASI UNTUK SETIAP BRAND
        // ============================================================
        foreach ($brands as $brand) {
            // Pending products
            $pending_count = $this->db->select('COUNT(*) as total')
                ->from('affiliate_products')
                ->where('shop_name', $brand->name)
                ->where('review_status', 'PENDING')
                ->get()
                ->row()
                ->total ?? 0;
            $brand->pending_products_count = intval($pending_count);
            
            // Approved products
            $approved_count = $this->db->select('COUNT(*) as total')
                ->from('affiliate_products')
                ->where('shop_name', $brand->name)
                ->where('review_status', 'APPROVED')
                ->get()
                ->row()
                ->total ?? 0;
            $brand->approved_products_count = intval($approved_count);
            
            // Total products
            $total_products = $this->db->select('COUNT(*) as total')
                ->from('affiliate_products')
                ->where('shop_name', $brand->name)
                ->get()
                ->row()
                ->total ?? 0;
            $brand->has_submitted_products = $total_products > 0;
            $brand->is_active_brand = ($brand->status == 'ACTIVE');
            $brand->is_active_with_pending = ($brand->status == 'ACTIVE' && $pending_count > 0);
            
            // Cek requirement
            $has_requirements = $this->db->select('creator_level')
                ->where('id', $brand->id)
                ->where('creator_level IS NOT NULL')
                ->get('brands')
                ->row();
            $brand->has_requirements = !empty($has_requirements);
        }
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'brands' => $brands,
            'total' => count($brands),
            'keyword' => $keyword
        ]));
        
    } catch (Exception $e) {
        log_message('error', 'search_setup_brands error: ' . $e->getMessage());
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'brands' => [],
            'total' => 0
        ]));
    }
}

/**
 * Search brands di Task 4 (MONITORING) - status ACTIVE
 */
public function search_monitoring_brands() {
    $this->output->set_content_type('application/json');
    
    $keyword = $this->input->post('keyword');
    $user_id = $this->session->userdata('user_id');
    $is_supervisor = ($user_id == 1);
    $today = date('Y-m-d');
    
    if (empty($keyword)) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Keyword required']));
    }
    
    $this->db->select('b.*, u.username as bd_username, u.full_name as bd_name, b.input_by, b.input_by_name')
        ->from('brands b')
        ->join('users u', 'b.bd_id = u.id', 'left')
        ->where('b.status', 'ACTIVE')
        ->group_start()
            ->like('b.name', $keyword)
            ->or_like('b.shop_name', $keyword)
            ->or_like('b.whatsapp_number', $keyword)
            ->or_like('b.input_by', $keyword)
        ->group_end()
        ->order_by('b.updated_at', 'DESC')
        ->limit(100);
    
    if (!$is_supervisor) {
        $this->db->where('b.bd_id', $user_id);
    }
    
    $brands = $this->db->get()->result();
    
    // Hitung GMV hari ini dan approved products untuk setiap brand
    foreach ($brands as $brand) {
        $stats = $this->db->select('COALESCE(SUM(o.gmv), 0) as total_gmv')
            ->from('affiliate_orders o')
            ->join('affiliate_products ap', 'o.product_id = ap.product_id AND o.campaign_id = ap.campaign_id')
            ->where('ap.shop_name', $brand->name)
            ->where('ap.review_status', 'APPROVED')
            ->where('o.order_date_local', $today)
            ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->get()
            ->row();
        
        $brand->today_gmv = floatval($stats->total_gmv ?? 0);
        $brand->approved_products_count = $this->db->where('shop_name', $brand->name)
            ->where('review_status', 'APPROVED')
            ->count_all_results('affiliate_products');
        $brand->roas = $brand->today_gmv > 0 ? round($brand->today_gmv / ($brand->today_gmv * 0.1), 2) : 0;
    }
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'brands' => $brands,
        'total' => count($brands),
        'keyword' => $keyword
    ]));
}
public function fetch_product_by_link() {
    $this->output->set_content_type('application/json');
    
    $link = $this->input->post('link');
    
    if (empty($link)) {
        return $this->output->set_output(json_encode([
            'success' => false, 
            'message' => 'Link required',
            'requires_manual' => false
        ]));
    }
    
    $product_data = [
        'id' => 'manual_' . time(),
        'name' => '',
        'price' => 0,
        'image' => null
    ];
    
    // ========== EKSTRAK DARI URL TOKOPEDIA ==========
    if (strpos($link, 'tokopedia.com') !== false || strpos($link, 'vt.tokopedia') !== false) {
        // Follow redirect untuk short link
        $final_url = $this->follow_redirect($link);
        
        // Ekstrak product name dari URL (slug)
        // Contoh: /xiaomi-redmi-watch-5-active-2-0-lcd-140-mode-olahraga-tahan-air-5atm-baterai-hingga-18-hari/
        if (preg_match('/pdp\/([^\/]+)/', $final_url, $matches)) {
            $slug = $matches[1];
            // Convert slug ke nama produk: ganti - dengan spasi, capitalize
            $product_name = str_replace('-', ' ', $slug);
            $product_name = ucwords($product_name);
            $product_data['name'] = $product_name;
            $product_data['id'] = 'tokopedia_' . md5($slug);
        }
        
        // Ekstrak product ID dari URL
        if (preg_match('/(\d{10,})/', $final_url, $id_matches)) {
            $product_data['product_id_number'] = $id_matches[1];
        }
        
        // Coba dapatkan harga dari meta description (opsional, via Google Shopping API)
        $price = $this->get_price_from_google($product_data['name']);
        if ($price > 0) {
            $product_data['price'] = $price;
        }
    }
    
    // ========== EKSTRAK DARI URL TIKTOK ==========
    if (strpos($link, 'tiktok.com') !== false) {
        preg_match('/product\/(\d+)/', $link, $matches);
        if (isset($matches[1])) {
            $product_data['id'] = $matches[1];
            $product_data['name'] = "TikTok Product ID: {$matches[1]}";
            
            // Coba cari via TikTok API
            $search_result = $this->jsm_api->search_products([
                'keyword' => $matches[1],
                'page_size' => 1
            ]);
            if ($search_result['success'] && !empty($search_result['data'])) {
                $product_data['name'] = $search_result['data'][0]['title'];
                $product_data['price'] = $search_result['data'][0]['price'];
                $product_data['image'] = $search_result['data'][0]['image_url'];
            }
        }
    }
    
    // Jika nama produk berhasil diekstrak dari URL
    if (!empty($product_data['name']) && $product_data['name'] != '') {
        return $this->output->set_output(json_encode([
            'success' => true,
            'data' => $product_data,
            'extracted_from_url' => true
        ]));
    }
    
    // Fallback ke manual input
    return $this->output->set_output(json_encode([
        'success' => false,
        'requires_manual' => true,
        'message' => 'Tidak dapat mengekstrak data dari link. Silakan input manual.',
        'suggested_name' => $product_data['name'] ?? ''
    ]));
}
private function extract_keyword_from_url($url) {
    // Extract keyword dari URL produk
    $patterns = [
        '/product\/([^\/?]+)/',
        '/pdp\/([^\/?]+)/',
        '/item\/([^\/?]+)/'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $matches)) {
            $keyword = str_replace('-', ' ', $matches[1]);
            $keyword = preg_replace('/[0-9]+/', '', $keyword);
            return trim($keyword);
        }
    }
    return null;
}
private function fetch_url_with_curl($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: id-ID,id;q=0.9,en;q=0.8',
            'Cache-Control: no-cache'
        ]
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}
private function follow_redirect($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_NOBODY => true,
        CURLOPT_HEADER => true
    ]);
    curl_exec($ch);
    $final_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    return $final_url;
}

// ========== UPDATE BRAND DATA ONLY (TANPA UBAH STATUS) ==========
public function update_brand_data() {
    $this->output->set_content_type('application/json');
    
    $brand_id = $this->input->post('brand_id');
    $name = $this->input->post('name');
    $whatsapp_number = $this->input->post('whatsapp_number');
    $commission = $this->input->post('commission');
    
    if (!$brand_id) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Brand ID required']));
    }
    
    $update_data = ['updated_at' => date('Y-m-d H:i:s')];
    
    if ($name !== null && $name !== '') {
        $update_data['name'] = $name;
        $update_data['shop_name'] = $name;
    }
    
    if ($whatsapp_number !== null) {
        // Format nomor WhatsApp
        $phone = preg_replace('/[^0-9+]/', '', $whatsapp_number);
        if (preg_match('/^0/', $phone)) {
            $phone = '62' . substr($phone, 1);
        } elseif (preg_match('/^\+/', $phone)) {
            $phone = substr($phone, 1);
        }
        $update_data['whatsapp_number'] = $phone;
    }
    
    if ($commission !== null && $commission !== '') {
        $update_data['proposed_commission'] = $commission;
    }
    
    $this->db->where('id', $brand_id);
    $this->db->update('brands', $update_data);
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'message' => 'Brand data updated successfully'
    ]));
}


public function get_discovered_brand_products() {
    $this->output->set_content_type('application/json');
    
    $shop_name = $this->input->post('shop_name');
    $limit = $this->input->post('limit') ?: 20;
    
    if (!$shop_name) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Shop name required']));
    }
    
    $products = $this->db->select('
            product_id,
            product_name,
            gmv,
            price,
            sales_count,
            image_url,
            commission_rate,
            review_status
        ')
        ->from('affiliate_products')
        ->where('shop_name', $shop_name)
        ->where('review_status', 'APPROVED')
        ->order_by('gmv', 'DESC')
        ->limit($limit)
        ->get()
        ->result();
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'products' => $products,
        'count' => count($products)
    ]));
}


/**
 * Get creators for discovered brand
 */
public function get_discovered_brand_creators() {
    $this->output->set_content_type('application/json');
    
    $shop_name = $this->input->post('shop_name');
    $limit = $this->input->post('limit') ?: 20;
    
    if (!$shop_name) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Shop name required']));
    }
    
    $creators = $this->db->select('
            o.creator_username,
            SUM(o.gmv) as total_gmv,
            COUNT(DISTINCT o.order_id) as total_orders,
            SUM(o.estimated_commission) as total_commission
        ')
        ->from('affiliate_orders o')
        ->join('affiliate_products ap', 'o.product_id = ap.product_id AND o.campaign_id = ap.campaign_id')
        ->where('ap.shop_name', $shop_name)
        ->where('ap.review_status', 'APPROVED')
        ->where('o.creator_username IS NOT NULL')
        ->where('o.creator_username !=', '')
        ->group_by('o.creator_username')
        ->order_by('total_gmv', 'DESC')
        ->limit($limit)
        ->get()
        ->result();
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'creators' => $creators,
        'count' => count($creators)
    ]));
}


public function get_campaigns_list() {
    $this->output->set_content_type('application/json');
    
    try {
        $campaigns = $this->db->select('campaign_id, campaign_name, campaign_image, status')
                              ->from('affiliate_campaigns')
                              ->where('status', 'ONGOING')
                              ->order_by('created_at', 'DESC')
                              ->limit(50)
                              ->get()
                              ->result();
        
        // Format response dengan gambar
        $formatted_campaigns = [];
        foreach ($campaigns as $camp) {
            $formatted_campaigns[] = [
                'campaign_id' => $camp->campaign_id,
                'campaign_name' => $camp->campaign_name,
                'campaign_image' => $camp->campaign_image ?? null,
                'status' => $camp->status
            ];
        }
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'campaigns' => $formatted_campaigns
        ]));
        
    } catch (Exception $e) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'campaigns' => []
        ]));
    }
}


/**
 * Get products for a campaign (for Task 3 modal)
 */
public function get_campaign_products_for_review() {
    $this->output->set_content_type('application/json');
    
    $campaign_id = $this->input->post('campaign_id');
    $brand_id = $this->input->post('brand_id');  // 🔥 TAMBAHKAN brand_id
    
    if (!$campaign_id) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Campaign ID required',
            'products' => []
        ]));
    }
    
    try {
        // 🔥 Ambil brand name
        $brand_name = '';
        if ($brand_id) {
            $brand = $this->db->select('name')->where('id', $brand_id)->get('brands')->row();
            $brand_name = $brand->name ?? '';
        }
        
        // 🔥 Ambil dari API langsung
        $api_result = $this->jsm_api->get_campaign_products($campaign_id, [
            'page_size' => 50,
            'review_status' => 'PENDING'
        ]);
        
        $products = [];
        
        if ($api_result['success'] && !empty($api_result['data'])) {
            foreach ($api_result['data'] as $prod) {
                $product_shop_name = $prod['shop_name'] ?? '';
                
                // 🔥 FILTER: Hanya tampilkan produk dari brand/seller yang dipilih
                if ($brand_name && stripos($product_shop_name, $brand_name) === false) {
                    continue; // Skip produk dari seller lain
                }
                
                // Extract data
                $lowest_price = $prod['lowest_price']['amount'] ?? 0;
                $highest_price = $prod['highest_price']['amount'] ?? 0;
                
                $partner_commission_rate = ($prod['partner_commission_rate'] ?? 0) / 100;
                $creator_commission_rate = ($prod['creator_commission_rate'] ?? 0) / 100;
                $total_commission_rate = ($prod['total_commission_rate'] ?? 0) / 100;
                $open_collaboration_commission_rate = ($prod['open_collaboration_commission_rate'] ?? 0) / 100;
                $shop_ads_rate = $partner_commission_rate;
                
                $products[] = [
                    'product_id' => $prod['id'],
                    'product_name' => $prod['name'] ?? '',
                    'price' => floatval($lowest_price ?: $highest_price),
                    'lowest_price' => floatval($lowest_price),
                    'highest_price' => floatval($highest_price),
                    'partner_commission_rate' => $partner_commission_rate,
                    'creator_commission_rate' => $creator_commission_rate,
                    'total_commission_rate' => $total_commission_rate,
                    'open_commission_rate' => $open_collaboration_commission_rate,
                    'shop_ads_rate' => $prod['shop_ads_commission_rate'] ?? 0,
                    'inventory' => intval($prod['inventory'] ?? 0),
                    'sample_quota' => intval($prod['sample_quota'] ?? 0),
                    'product_sales' => intval($prod['product_sales'] ?? 0),
                    'image_url' => $prod['main_image_url'] ?? '',
                    'shop_name' => $product_shop_name,
                    'category' => $prod['category']['name'] ?? '',
                    'review_status' => $prod['review_status'] ?? 'PENDING',
                    'is_available' => $prod['is_available'] ?? true
                ];
            }
        }
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'products' => $products,
            'total' => count($products),
            'filtered_by' => $brand_name ?: 'all'
        ]));
        
    } catch (Exception $e) {
        log_message('error', 'Error in get_campaign_products_for_review: ' . $e->getMessage());
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'products' => []
        ]));
    }
}

/**
 * Extract price from product data
 */
private function extractPriceFromProduct($product) {
    $price = 0;
    if (isset($product['lowest_price']) && is_array($product['lowest_price'])) {
        $price = floatval($product['lowest_price']['amount'] ?? 0);
    } elseif (isset($product['price'])) {
        if (is_array($product['price'])) {
            $price = floatval($product['price']['amount'] ?? 0);
        } else {
            $price = floatval($product['price']);
        }
    }
    return $price;
}



/**
 * Review product (Approve/Reject) for Task 3
 */
public function review_product() {
    $this->output->set_content_type('application/json');
    
    $campaign_id = $this->input->post('campaign_id');
    $product_id = $this->input->post('product_id');
    $review_result = $this->input->post('review_result'); // APPROVE or REJECT
    $reject_reasons = $this->input->post('reject_reasons');
    
    if (!$campaign_id || !$product_id || !$review_result) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Missing required fields'
        ]));
    }
    
    try {
        // Panggil API TikTok untuk review
        $result = $this->jsm_api->review_campaign_product(
            $campaign_id,
            $product_id,
            $review_result,
            $reject_reasons ? [$reject_reasons] : null
        );
        
        if ($result['success']) {
            // Update status di database
            $this->db->where('campaign_id', $campaign_id)
                     ->where('product_id', $product_id)
                     ->update('affiliate_products', [
                         'review_status' => $review_result == 'APPROVE' ? 'APPROVED' : 'REJECTED',
                         'updated_at' => date('Y-m-d H:i:s')
                     ]);
            
            return $this->output->set_output(json_encode([
                'success' => true,
                'message' => 'Product ' . strtolower($review_result) . 'd successfully'
            ]));
        } else {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to review product'
            ]));
        }
        
    } catch (Exception $e) {
        log_message('error', 'Error in review_product: ' . $e->getMessage());
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]));
    }
}
// ========== GET BRAND PERFORMANCE FOR TASK 4 MONITORING ==========
public function get_brand_performance() {
    $this->output->set_content_type('application/json');
    
    $brand_id = $this->input->post('brand_id');
    
    if (!$brand_id) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Brand ID required']));
    }
    
    // Ambil data brand
    $brand = $this->db->where('id', $brand_id)->get('brands')->row();
    
    if (!$brand) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Brand not found']));
    }
    
    // ðŸ”¥ Ambil filter tanggal dari request (default hari ini)
    $start_date = $this->input->post('start_date') ?: date('Y-m-d');
    $end_date = $this->input->post('end_date') ?: date('Y-m-d');
    
    // ðŸ”¥ Cari semua product_id untuk brand ini dari affiliate_products berdasarkan shop_name
    $product_ids = $this->db->select('product_id')
                            ->from('affiliate_products')
                            ->where('shop_name', $brand->name)
                            ->where('review_status', 'APPROVED')
                            ->get()
                            ->result();
    
    $product_id_array = array_column($product_ids, 'product_id');
    
    // ðŸ”¥ Jika tidak ada product_id, coba cari berdasarkan nama produk
    if (empty($product_id_array)) {
        $product_ids = $this->db->select('product_id')
                                ->from('affiliate_products')
                                ->like('product_name', $brand->name)
                                ->where('review_status', 'APPROVED')
                                ->get()
                                ->result();
        $product_id_array = array_column($product_ids, 'product_id');
    }
    
    // ðŸ”¥ Jika masih tidak ada, coba cari berdasarkan shop_name di affiliate_orders langsung
    if (empty($product_id_array)) {
        // Ambil order langsung dari affiliate_orders berdasarkan product_name yang mengandung brand name
        $stats = $this->db->select('
                COALESCE(SUM(gmv), 0) as total_gmv,
                COUNT(DISTINCT order_id) as total_orders,
                COALESCE(SUM(estimated_commission), 0) as total_commission,
                COUNT(DISTINCT creator_username) as total_creators
            ')
            ->from('affiliate_orders')
            ->like('product_name', $brand->name)
            ->where('order_date_local >=', $start_date)
            ->where('order_date_local <=', $end_date)
            ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->get()
            ->row();
        
        // Ambil products
        $products = $this->db->select('
                product_id,
                product_name,
                NULL as price,
                NULL as commission_rate,
                COALESCE(SUM(gmv), 0) as gmv,
                COUNT(DISTINCT order_id) as sales_count,
                MAX(order_time) as last_sold
            ')
            ->from('affiliate_orders')
            ->like('product_name', $brand->name)
            ->where('order_date_local >=', $start_date)
            ->where('order_date_local <=', $end_date)
            ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->group_by('product_id')
            ->order_by('gmv', 'DESC')
            ->limit(10)
            ->get()
            ->result();
        
        // Ambil creators
        $creators = $this->db->select('
                creator_username,
                COUNT(DISTINCT order_id) as total_orders,
                SUM(gmv) as total_gmv,
                SUM(estimated_commission) as total_commission
            ')
            ->from('affiliate_orders')
            ->like('product_name', $brand->name)
            ->where('order_date_local >=', $start_date)
            ->where('order_date_local <=', $end_date)
            ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->where('creator_username IS NOT NULL')
            ->group_by('creator_username')
            ->order_by('total_gmv', 'DESC')
            ->limit(10)
            ->get()
            ->result();
        
        $roas = ($stats->total_commission > 0) ? round($stats->total_gmv / $stats->total_commission, 2) : 0;
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'data' => [
                'brand' => [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'category' => $brand->category,
                    'status' => $brand->status
                ],
                'total_gmv' => floatval($stats->total_gmv ?? 0),
                'total_orders' => intval($stats->total_orders ?? 0),
                'total_commission' => floatval($stats->total_commission ?? 0),
                'total_creators' => intval($stats->total_creators ?? 0),
                'roas' => $roas,
                'products' => $products,
                'creators' => $creators,
                'period' => [
                    'start_date' => $start_date,
                    'end_date' => $end_date
                ]
            ]
        ]));
    }
    
    // ðŸ”¥ Jika ada product_id, hitung statistik berdasarkan product_id
    if (!empty($product_id_array)) {
        // ðŸ”¥ PERBAIKAN: Query tanpa JOIN yang kompleks, cukup WHERE IN
        $stats = $this->db->select('
                COALESCE(SUM(gmv), 0) as total_gmv,
                COUNT(DISTINCT order_id) as total_orders,
                COALESCE(SUM(estimated_commission), 0) as total_commission,
                COUNT(DISTINCT creator_username) as total_creators
            ')
            ->from('affiliate_orders')
            ->where_in('product_id', $product_id_array)
            ->where('order_date_local >=', $start_date)
            ->where('order_date_local <=', $end_date)
            ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->get()
            ->row();
        
        // ðŸ”¥ Ambil top products
        $products = $this->db->select('
                product_id,
                product_name,
                price,
                commission_rate,
                COALESCE(SUM(gmv), 0) as gmv,
                COUNT(DISTINCT order_id) as sales_count,
                MAX(order_time) as last_sold
            ')
            ->from('affiliate_orders')
            ->where_in('product_id', $product_id_array)
            ->where('order_date_local >=', $start_date)
            ->where('order_date_local <=', $end_date)
            ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->group_by('product_id')
            ->order_by('gmv', 'DESC')
            ->limit(10)
            ->get()
            ->result();
        
        // ðŸ”¥ Ambil top creators
        $creators = $this->db->select('
                creator_username,
                COUNT(DISTINCT order_id) as total_orders,
                SUM(gmv) as total_gmv,
                SUM(estimated_commission) as total_commission
            ')
            ->from('affiliate_orders')
            ->where_in('product_id', $product_id_array)
            ->where('order_date_local >=', $start_date)
            ->where('order_date_local <=', $end_date)
            ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->where('creator_username IS NOT NULL')
            ->group_by('creator_username')
            ->order_by('total_gmv', 'DESC')
            ->limit(10)
            ->get()
            ->result();
        
        // ðŸ”¥ Ambil detail produk dari affiliate_products untuk menambah informasi
        foreach ($products as $product) {
            $product_detail = $this->db->select('price, commission_rate, image_url')
                                       ->from('affiliate_products')
                                       ->where('product_id', $product->product_id)
                                       ->limit(1)
                                       ->get()
                                       ->row();
            if ($product_detail) {
                $product->price = $product_detail->price ?? 0;
                $product->commission_rate = $product_detail->commission_rate ?? 0;
            }
        }
        
        $roas = ($stats->total_commission > 0) ? round($stats->total_gmv / $stats->total_commission, 2) : 0;
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'data' => [
                'brand' => [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'category' => $brand->category,
                    'status' => $brand->status
                ],
                'total_gmv' => floatval($stats->total_gmv ?? 0),
                'total_orders' => intval($stats->total_orders ?? 0),
                'total_commission' => floatval($stats->total_commission ?? 0),
                'total_creators' => intval($stats->total_creators ?? 0),
                'roas' => $roas,
                'products' => $products,
                'creators' => $creators,
                'period' => [
                    'start_date' => $start_date,
                    'end_date' => $end_date
                ]
            ]
        ]));
    }
    
    // Jika tidak ada data sama sekali
    return $this->output->set_output(json_encode([
        'success' => true,
        'data' => [
            'brand' => [
                'id' => $brand->id,
                'name' => $brand->name,
                'category' => $brand->category,
                'status' => $brand->status
            ],
            'total_gmv' => 0,
            'total_orders' => 0,
            'total_commission' => 0,
            'total_creators' => 0,
            'roas' => 0,
            'products' => [],
            'creators' => [],
            'period' => [
                'start_date' => $start_date,
                'end_date' => $end_date
            ]
        ]
    ]));
}



// ========== TEAM PERFORMANCE ==========
public function team_performance() {
    $user_id = $this->session->userdata('user_id');
    $role = $this->session->userdata('role');
    
    // 🔥 Ambil filter tanggal (default hari ini)
    $start_date = $this->input->get('start_date') ?: date('Y-m-d');
    $end_date = $this->input->get('end_date') ?: date('Y-m-d');
    
    // 🔥 Hitung periode sebelumnya (untuk growth)
    $days_diff = (strtotime($end_date) - strtotime($start_date)) / 86400 + 1;
    $prev_start_date = date('Y-m-d', strtotime("-$days_diff days", strtotime($start_date)));
    $prev_end_date = date('Y-m-d', strtotime('-1 day', strtotime($start_date)));
    
    // 🔥 SUPERVISOR (user_id = 1) bisa lihat semua, yang lain hanya lihat sendiri
    $is_supervisor = ($user_id == 1);
    
    // ========== AMBIL SEMUA TEAM MEMBER BD ==========
    if ($is_supervisor) {
        $team_members = $this->db->select('id, username, full_name, role, created_at')
                                 ->where('role', 'BD')
                                 ->order_by('id', 'ASC')
                                 ->get('users')
                                 ->result();
    } else {
        $team_members = $this->db->select('id, username, full_name, role, created_at')
                                 ->where('id', $user_id)
                                 ->where('role', 'BD')
                                 ->get('users')
                                 ->result();
    }
    
    // ========== HITUNG TOTAL BRAND AKTIF (status ACTIVE) ==========
    // 🔥 PERBAIKAN: Total brand aktif = status ACTIVE (bukan semua brand)
    if ($is_supervisor) {
        $total_active_brands = $this->db->where('status', 'ACTIVE')->count_all_results('brands');
    } else {
        $total_active_brands = $this->db->where('bd_id', $user_id)
                                        ->where('status', 'ACTIVE')
                                        ->count_all_results('brands');
    }
    
    // ========== HITUNG BRAND DENGAN SALES (GMV > 0) ==========
    $brands_with_sales = 0;
    $brands_with_sales_prev = 0;
    
    // Ambil brand ACTIVE
    $active_brands = $this->db->select('b.id, b.name')
        ->from('brands b')
        ->where('b.status', 'ACTIVE');
    
    if (!$is_supervisor) {
        $active_brands->where('b.bd_id', $user_id);
    }
    
    $active_brands = $active_brands->get()->result();
    
    foreach ($active_brands as $brand) {
        // Cek GMV current period
        $gmv_current = $this->db->select('COALESCE(SUM(o.gmv), 0) as total')
            ->from('affiliate_orders o')
            ->join('affiliate_products ap', 'o.product_id = ap.product_id AND o.campaign_id = ap.campaign_id')
            ->where('ap.shop_name', $brand->name)
            ->where('ap.review_status', 'APPROVED')
            ->where('o.order_date_local >=', $start_date)
            ->where('o.order_date_local <=', $end_date)
            ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->get()
            ->row()
            ->total ?? 0;
        
        if ($gmv_current > 0) {
            $brands_with_sales++;
        }
        
        // Cek GMV previous period
        $gmv_prev = $this->db->select('COALESCE(SUM(o.gmv), 0) as total')
            ->from('affiliate_orders o')
            ->join('affiliate_products ap', 'o.product_id = ap.product_id AND o.campaign_id = ap.campaign_id')
            ->where('ap.shop_name', $brand->name)
            ->where('ap.review_status', 'APPROVED')
            ->where('o.order_date_local >=', $prev_start_date)
            ->where('o.order_date_local <=', $prev_end_date)
            ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->get()
            ->row()
            ->total ?? 0;
        
        if ($gmv_prev > 0) {
            $brands_with_sales_prev++;
        }
    }
    
    // ========== HITUNG PERFORMA SETIAP TEAM MEMBER ==========
    $total_gmv_current = 0;
    $total_orders_current = 0;
    $total_commission_current = 0;
    $total_gmv_previous = 0;
    $total_orders_previous = 0;
    
    foreach ($team_members as $member) {
        // 🔥 Jumlah brand yang ditangani (hanya yang berstatus ACTIVE)
        $member->total_brands = $this->db->where('bd_id', $member->id)
                                         ->where('status', 'ACTIVE')
                                         ->count_all_results('brands');
        
        // 🔥 AMBIL SEMUA SHOP_NAME dari brand ACTIVE milik member ini
        $brands = $this->db->select('name, shop_name')
            ->where('bd_id', $member->id)
            ->where('status', 'ACTIVE')
            ->get('brands')
            ->result();
        
        $shop_names = [];
        foreach ($brands as $brand) {
            if (!empty($brand->shop_name)) $shop_names[] = $brand->shop_name;
            if (!empty($brand->name)) $shop_names[] = $brand->name;
        }
        $shop_names = array_unique($shop_names);
        
        // 🔥 CURRENT PERIOD: GMV, Orders, Commission, Brands with Sales
        $current_stats = (object)[
            'total_gmv' => 0,
            'total_orders' => 0,
            'total_commission' => 0,
            'brands_with_sales' => 0
        ];
        
        if (!empty($shop_names)) {
            $stats = $this->db->select('
                    COALESCE(SUM(o.gmv), 0) as total_gmv,
                    COUNT(DISTINCT o.order_id) as total_orders,
                    COALESCE(SUM(o.estimated_commission), 0) as total_commission,
                    COUNT(DISTINCT ap.shop_name) as brands_with_sales
                ')
                ->from('affiliate_orders o')
                ->join('affiliate_products ap', 'o.product_id = ap.product_id AND o.campaign_id = ap.campaign_id', 'inner')
                ->where_in('ap.shop_name', $shop_names)
                ->where('ap.review_status', 'APPROVED')
                ->where('o.order_date_local >=', $start_date)
                ->where('o.order_date_local <=', $end_date)
                ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
                ->get()
                ->row();
            
            $current_stats = $stats;
        }
        
        $member->total_gmv = floatval($current_stats->total_gmv ?? 0);
        $member->total_orders = intval($current_stats->total_orders ?? 0);
        $member->total_commission = floatval($current_stats->total_commission ?? 0);
        $member->brands_with_sales = intval($current_stats->brands_with_sales ?? 0);
        
        // 🔥 PREVIOUS PERIOD
        $prev_stats = (object)['total_gmv' => 0, 'total_orders' => 0];
        if (!empty($shop_names)) {
            $prev = $this->db->select('
                    COALESCE(SUM(o.gmv), 0) as total_gmv,
                    COUNT(DISTINCT o.order_id) as total_orders
                ')
                ->from('affiliate_orders o')
                ->join('affiliate_products ap', 'o.product_id = ap.product_id AND o.campaign_id = ap.campaign_id', 'inner')
                ->where_in('ap.shop_name', $shop_names)
                ->where('ap.review_status', 'APPROVED')
                ->where('o.order_date_local >=', $prev_start_date)
                ->where('o.order_date_local <=', $prev_end_date)
                ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
                ->get()
                ->row();
            $prev_stats = $prev;
        }
        
        $member->prev_gmv = floatval($prev_stats->total_gmv ?? 0);
        $member->prev_orders = intval($prev_stats->total_orders ?? 0);
        
        // 🔥 Hitung growth (PERBAIKAN: division by zero handling)
        if ($member->prev_gmv > 0) {
            $member->gmv_growth = round((($member->total_gmv - $member->prev_gmv) / $member->prev_gmv) * 100, 1);
        } elseif ($member->total_gmv > 0) {
            $member->gmv_growth = 100; // Naik 100% karena sebelumnya 0
        } else {
            $member->gmv_growth = 0;
        }
        
        if ($member->prev_orders > 0) {
            $member->orders_growth = round((($member->total_orders - $member->prev_orders) / $member->prev_orders) * 100, 1);
        } elseif ($member->total_orders > 0) {
            $member->orders_growth = 100;
        } else {
            $member->orders_growth = 0;
        }
        
        // 🔥 TASK STATS (semua brand, termasuk yang tidak aktif)
        $member->task_stats = [
            'hunting' => $this->db->where('bd_id', $member->id)
                ->where('status', 'PENDING')
                ->count_all_results('brands'),
            'followup' => $this->db->where('bd_id', $member->id)
                ->where('status', 'FOLLOW_UP')
                ->count_all_results('brands'),
            'setup' => $this->db->where('bd_id', $member->id)
                ->where('status', 'CAMPAIGN_READY')
                ->count_all_results('brands'),
            'monitoring' => $this->db->where('bd_id', $member->id)
                ->where('status', 'ACTIVE')
                ->count_all_results('brands')
        ];
        
        // 🔥 Progress (persentase brand yang sudah ACTIVE dari total brand)
        $total_brand = $member->task_stats['hunting'] + $member->task_stats['followup'] + 
                       $member->task_stats['setup'] + $member->task_stats['monitoring'];
        $member->progress = $total_brand > 0 
            ? round(($member->task_stats['monitoring'] / $total_brand) * 100, 1) 
            : 0;
        
        // 🔥 Akumulasi total
        $total_gmv_current += $member->total_gmv;
        $total_orders_current += $member->total_orders;
        $total_commission_current += $member->total_commission;
        $total_gmv_previous += $member->prev_gmv;
        $total_orders_previous += $member->prev_orders;
    }
    
    // 🔥 Hitung growth total
    $total_gmv_growth = 0;
    if ($total_gmv_previous > 0) {
        $total_gmv_growth = round((($total_gmv_current - $total_gmv_previous) / $total_gmv_previous) * 100, 1);
    } elseif ($total_gmv_current > 0) {
        $total_gmv_growth = 100;
    }
    
    $total_orders_growth = 0;
    if ($total_orders_previous > 0) {
        $total_orders_growth = round((($total_orders_current - $total_orders_previous) / $total_orders_previous) * 100, 1);
    } elseif ($total_orders_current > 0) {
        $total_orders_growth = 100;
    }
    
    // ========== URUTKAN BERDASARKAN TOTAL GMV ==========
    usort($team_members, function($a, $b) {
        return $b->total_gmv <=> $a->total_gmv;
    });
    
    // ========== TEAM SUMMARY ==========
    $team_summary = [
        'total_members' => count($team_members),
        'total_gmv' => $total_gmv_current,
        'total_gmv_previous' => $total_gmv_previous,
        'total_gmv_growth' => $total_gmv_growth,
        'total_orders' => $total_orders_current,
        'total_orders_previous' => $total_orders_previous,
        'total_orders_growth' => $total_orders_growth,
        'total_commission' => $total_commission_current,
        'total_brands_with_sales' => $brands_with_sales,
        'total_active_brands' => $total_active_brands,  // 🔥 PERBAIKAN: total brand aktif
    ];
    
    $data = [
        'title' => 'Team Performance - Toopai BD',
        'active_menu' => 'team_performance',
        'team_members' => $team_members,
        'team_summary' => $team_summary,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'prev_start_date' => $prev_start_date,
        'prev_end_date' => $prev_end_date,
        'my_role' => $role,
        'is_supervisor' => $is_supervisor
    ];
    
    $this->load->view('templates/new/header', $data);
    $this->load->view('bd/team_performance', $data);
    $this->load->view('templates/new/footer');
}
// ========== GENERATE AFFILIATE LINK (TANPA CREATOR) UNTUK IS (TASK 3 AFTER APPROVE)  ==========
public function generate_bd_affiliate_link() {
    $this->output->set_content_type('application/json');
     $user_id = $this->session->userdata('user_id');
    $username = $this->session->userdata('username');
    
    // 🔥 HANYA USER ID = 1 (TIFFANY) YANG BISA GENERATE LINK
    if ($user_id != 1) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Anda tidak memiliki akses untuk generate link. Hanya Head BA yang dapat generate link afiliasi.',
            'can_generate' => false
        ]));
    }
    $campaign_id = $this->input->post('campaign_id');
    $product_id = $this->input->post('product_id');
    $product_name = $this->input->post('product_name');
    $open_commission_rate = $this->input->post('open_commission_rate') ?: 0;
    
    // Komisi default = +1% dari open plan
    $commission_rate = floatval($open_commission_rate) + 1;
    
    $user_id = $this->session->userdata('user_id');
    $username = $this->session->userdata('username');
    
    if (!$campaign_id || !$product_id) {
        return $this->output->set_output(json_encode([
            'success' => false, 
            'message' => 'Campaign ID and Product ID required'
        ]));
    }
    
    try {
        // 🔥 CEK APAKAH SUDAH ADA LINK UNTUK CAMPAIGN + PRODUCT INI
        $existing_link = $this->db->where('campaign_id', $campaign_id)
                                  ->where('product_id', $product_id)
                                  ->get('bd_affiliate_links')
                                  ->row();
        
        // Generate link via API (tetap generate ulang untuk dapat link terbaru)
        $link_result = $this->jsm_api->generate_promotion_link($campaign_id, $product_id, $commission_rate);
        
        if (!$link_result['success']) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => $link_result['message'] ?? 'Failed to generate link'
            ]));
        }
        
        $now = date('Y-m-d H:i:s');
        
        if ($existing_link) {
            // 🔥 UPDATE DATA YANG SUDAH ADA
            $update_data = [
                'affiliate_link' => $link_result['link'],
                'commission_rate' => $commission_rate,
                'open_commission_rate' => $open_commission_rate,
                'updated_at' => $now,
                'status' => 'ACTIVE'
            ];
            
            $this->db->where('campaign_id', $campaign_id)
                     ->where('product_id', $product_id)
                     ->update('bd_affiliate_links', $update_data);
            
            $message = 'Affiliate link updated successfully';
            $link_id = $existing_link->id;
            
        } else {
            // 🔥 INSERT DATA BARU
            $link_id = md5($campaign_id . $product_id . time());
            
            $insert_data = [
                'link_id' => $link_id,
                'campaign_id' => $campaign_id,
                'product_id' => $product_id,
                'product_name' => $product_name,
                'affiliate_link' => $link_result['link'],
                'commission_rate' => $commission_rate,
                'open_commission_rate' => $open_commission_rate,
                'created_by' => $user_id,
                'created_by_name' => $username,
                'status' => 'ACTIVE',
                'expire_at' => $link_result['expire_at'] ?? null,
                'created_at' => $now,
                'updated_at' => $now
            ];
            
            $this->db->insert('bd_affiliate_links', $insert_data);
            $message = 'Affiliate link generated successfully';
        }
        
        // Log activity
        $this->load->model('User_log_model');
        $this->User_log_model->log(
            $user_id, 
            $username, 
            'BD', 
            'GENERATE_AFFILIATE_LINK', 
            "Generated/Updated affiliate link for product: $product_name (Campaign: $campaign_id) with commission $commission_rate%"
        );
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'link' => $link_result['link'],
            'commission_rate' => $commission_rate,
            'link_id' => $link_id,
            'message' => $message,
            'is_update' => !empty($existing_link)
        ]));
        
    } catch (Exception $e) {
        log_message('error', 'Error in generate_bd_affiliate_link: ' . $e->getMessage());
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]));
    }
}

// ========== GET BD AFFILIATE LINKS FOR IS (Task 2) ==========
public function get_bd_affiliate_links_for_is() {
    $this->output->set_content_type('application/json');
    
    $product_id = $this->input->post('product_id');
    $campaign_id = $this->input->post('campaign_id');
    
    if (!$product_id || !$campaign_id) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Product ID and Campaign ID required',
            'has_link' => false
        ]));
    }
    
    // Cari link dari tabel bd_affiliate_links
    $link = $this->db->select('affiliate_link, commission_rate, created_by_name, created_at')
                     ->from('bd_affiliate_links')
                     ->where('product_id', $product_id)
                     ->where('campaign_id', $campaign_id)
                     ->where('status', 'ACTIVE')
                     ->order_by('created_at', 'DESC')
                     ->limit(1)
                     ->get()
                     ->row();
    
    if ($link) {
        return $this->output->set_output(json_encode([
            'success' => true,
            'has_link' => true,
            'link' => $link->affiliate_link,
            'commission_rate' => $link->commission_rate,
            'created_by' => $link->created_by_name,
            'created_at' => $link->created_at
        ]));
    } else {
        return $this->output->set_output(json_encode([
            'success' => true,
            'has_link' => false,
            'message' => 'Link belum tersedia. Silakan minta BD untuk generate link.'
        ]));
    }
}

// ========== CHECK IF BD CAN GENERATE LINK (User ID = 1) ==========
public function can_generate_link() {
    $this->output->set_content_type('application/json');
    
    $user_id = $this->session->userdata('user_id');
    $role = $this->session->userdata('role');
    $username = $this->session->userdata('username');
    
    // 🔥 HANYA USER ID = 1 (TIFFANY) YANG BISA GENERATE LINK
    $can_generate = ($user_id == 1);
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'can_generate' => $can_generate,
        'user_id' => $user_id,
        'username' => $username,
        'message' => $can_generate ? 'Anda dapat generate link afiliasi' : 'Head BA yang dapat generate link afiliasi. Anda hanya dapat menggunakan link yang sudah tersedia.'
    ]));
}


/**
 * 
 * Debug raw response dari API marketplace creators search
 * URL: /bd/raw_marketplace_creators?keyword=beauty
 */
public function raw_marketplace_creators() {
    $this->output->set_content_type('application/json');
    
    $keyword = $this->input->get('keyword') ?: '';
    // 🔥 Page size HARUS 12 atau 20
    $limit = (int)$this->input->get('limit') ?: 20;
    
    // Validasi limit
    if ($limit != 12 && $limit != 20) {
        $limit = 20;
    }
    
    $filters = [];
    if (!empty($keyword)) {
        $filters['keyword'] = $keyword;
    }
    
    // Panggil API dengan page_size yang benar
    $result = $this->jsm_api->search_marketplace_creators($filters, $limit);
    
    // Return RAW response dari API
    return $this->output->set_output(json_encode($result, JSON_PRETTY_PRINT));
}


/**
 * Debug lengkap untuk mengecek contact information dari TikTok Marketplace API
 * URL: /bd/debug_contact_information
 */
public function debug_contact_information() {
    $this->output->set_content_type('application/json');
    
    $results = [
        'debug_info' => [
            'timestamp' => date('Y-m-d H:i:s'),
            'endpoint_tested' => [
                'search_marketplace_creators',
                'get_marketplace_creator_detail'
            ]
        ],
        'tests' => []
    ];
    
    // ==================== TEST 1: Search tanpa filter ====================
    $test1 = $this->jsm_api->search_marketplace_creators([], 12);
    $results['tests']['test1_search_no_filters'] = [
        'description' => 'Search marketplace creators without any filters',
        'params' => ['filters' => [], 'page_size' => 12],
        'success' => $test1['success'] ?? false,
        'total_creators' => isset($test1['data']['creators']) ? count($test1['data']['creators']) : 0,
        'has_contact_info' => $this->has_contact_info_in_search_response($test1),
        'has_next_page' => isset($test1['data']['next_page_token']) && !empty($test1['data']['next_page_token']),
        'available_fields' => $this->get_available_fields_from_search($test1),
        'sample_creator' => $this->get_first_creator_sample($test1)
    ];
    
    // ==================== TEST 2: Search dengan keyword ====================
    $test2 = $this->jsm_api->search_marketplace_creators(['keyword' => 'beauty'], 20);
    $results['tests']['test2_search_keyword_beauty'] = [
        'description' => 'Search marketplace creators with keyword "beauty"',
        'params' => ['filters' => ['keyword' => 'beauty'], 'page_size' => 20],
        'success' => $test2['success'] ?? false,
        'total_creators' => isset($test2['data']['creators']) ? count($test2['data']['creators']) : 0,
        'has_contact_info' => $this->has_contact_info_in_search_response($test2),
        'has_next_page' => isset($test2['data']['next_page_token']) && !empty($test2['data']['next_page_token']),
        'available_fields' => $this->get_available_fields_from_search($test2),
        'sample_creator' => $this->get_first_creator_sample($test2)
    ];
    
    // ==================== TEST 3: Search dengan country ID ====================
    $test3 = $this->jsm_api->search_marketplace_creators(['country_codes' => ['ID']], 12);
    $results['tests']['test3_search_country_id'] = [
        'description' => 'Search marketplace creators with country code ID',
        'params' => ['filters' => ['country_codes' => ['ID']], 'page_size' => 12],
        'success' => $test3['success'] ?? false,
        'total_creators' => isset($test3['data']['creators']) ? count($test3['data']['creators']) : 0,
        'has_contact_info' => $this->has_contact_info_in_search_response($test3),
        'has_next_page' => isset($test3['data']['next_page_token']) && !empty($test3['data']['next_page_token']),
        'available_fields' => $this->get_available_fields_from_search($test3)
    ];
    
    // ==================== TEST 4: Search dengan min followers ====================
    $test4 = $this->jsm_api->search_marketplace_creators([
        'keyword' => 'beauty',
        'min_follower_count' => 1000,
        'country_codes' => ['ID']
    ], 20);
    $results['tests']['test4_search_min_followers_1000'] = [
        'description' => 'Search marketplace creators with min_follower_count = 1000',
        'params' => ['filters' => ['keyword' => 'beauty', 'min_follower_count' => 1000, 'country_codes' => ['ID']], 'page_size' => 20],
        'success' => $test4['success'] ?? false,
        'total_creators' => isset($test4['data']['creators']) ? count($test4['data']['creators']) : 0,
        'has_contact_info' => $this->has_contact_info_in_search_response($test4),
        'has_next_page' => isset($test4['data']['next_page_token']) && !empty($test4['data']['next_page_token']),
        'available_fields' => $this->get_available_fields_from_search($test4),
        'sample_creator' => $this->get_first_creator_sample($test4)
    ];
    
    // ==================== TEST 5: Detail creator dengan sample open_id ====================
    // Ambil sample open_id dari test2 jika ada
    $sample_open_id = null;
    if ($test2['success'] && !empty($test2['data']['creators'])) {
        $sample_open_id = $test2['data']['creators'][0]['creator_open_id'] ?? null;
    }
    
    if ($sample_open_id) {
        $test5 = $this->jsm_api->get_marketplace_creator_detail($sample_open_id);
        $results['tests']['test5_creator_detail'] = [
            'description' => 'Get creator detail by open_id (with include_contact=true)',
            'creator_open_id' => $sample_open_id,
            'creator_username' => $test2['data']['creators'][0]['username'] ?? 'unknown',
            'success' => $test5['success'] ?? false,
            'has_contact_info' => $this->has_contact_info_in_detail_response($test5),
            'has_data' => isset($test5['data']) && !empty($test5['data']),
            'available_fields' => $test5['success'] ? array_keys($test5['data']) : [],
            'contact_information' => $test5['success'] ? ($test5['data']['contact_information'] ?? null) : null,
            'raw_data_sample' => $test5['success'] ? $this->get_safe_sample($test5['data']) : null
        ];
    } else {
        $results['tests']['test5_creator_detail'] = [
            'description' => 'Get creator detail by open_id',
            'error' => 'No sample creator_open_id available from previous tests',
            'success' => false
        ];
    }
    
    // ==================== TEST 6: Coba endpoint alternatif dengan parameter berbeda ====================
    // Test dengan creator yang memiliki follower tinggi (mungkin lebih mungkin memiliki contact info)
    $high_follower_creator = null;
    if ($test4['success'] && !empty($test4['data']['creators'])) {
        // Cari creator dengan follower tertinggi
        usort($test4['data']['creators'], function($a, $b) {
            return ($b['follower_count'] ?? 0) - ($a['follower_count'] ?? 0);
        });
        $high_follower_creator = $test4['data']['creators'][0] ?? null;
    }
    
    if ($high_follower_creator) {
        $test6 = $this->jsm_api->get_marketplace_creator_detail($high_follower_creator['creator_open_id']);
        $results['tests']['test6_creator_detail_high_follower'] = [
            'description' => 'Get creator detail for high follower creator',
            'creator_open_id' => $high_follower_creator['creator_open_id'],
            'creator_username' => $high_follower_creator['username'] ?? 'unknown',
            'follower_count' => $high_follower_creator['follower_count'] ?? 0,
            'success' => $test6['success'] ?? false,
            'has_contact_info' => $this->has_contact_info_in_detail_response($test6),
            'contact_information' => $test6['success'] ? ($test6['data']['contact_information'] ?? null) : null
        ];
    }
    
    // ==================== SUMMARY ====================
    $results['summary'] = [
        'total_tests' => count($results['tests']),
        'tests_with_contact_info' => 0,
        'observations' => [],
        'recommendations' => []
    ];
    
    foreach ($results['tests'] as $key => $test) {
        if (isset($test['has_contact_info']) && $test['has_contact_info'] === true) {
            $results['summary']['tests_with_contact_info']++;
        }
    }
    
    // Tambahkan observations berdasarkan hasil
    if ($results['summary']['tests_with_contact_info'] == 0) {
        $results['summary']['observations'][] = 'No contact information found in any API response';
        $results['summary']['observations'][] = 'This could be because:';
        $results['summary']['observations'][] = '  1. No creators have enabled contact sharing in their settings';
        $results['summary']['observations'][] = '  2. Contact information is only available for specific API versions';
        $results['summary']['observations'][] = '  3. Your seller account may need additional permissions';
        $results['summary']['observations'][] = '  4. The endpoint might require different parameters';
        
        $results['summary']['recommendations'] = [
            '1. Check TikTok Seller Center UI - Look for creators with "Contact" button',
            '2. Verify your seller account has approved affiliate status',
            '3. Try using different API version (202509)',
            '4. Contact TikTok support about contact information access',
            '5. Consider using collaboration invitation flow instead'
        ];
    } else {
        $results['summary']['observations'][] = 'Contact information IS available in some responses!';
        $results['summary']['recommendations'][] = 'Review which test cases returned contact info and use that approach';
    }
    
    // Cek apakah ada error code tertentu
    $error_codes = $this->check_for_error_codes($test1, $test2, $test3, $test4);
    if (!empty($error_codes)) {
        $results['summary']['error_codes_found'] = $error_codes;
    }
    
    return $this->output->set_output(json_encode($results, JSON_PRETTY_PRINT));
}

/**
 * Helper: Cek apakah ada contact info di search response
 */
private function has_contact_info_in_search_response($response) {
    if (!$response['success'] || !isset($response['data']['creators'])) {
        return false;
    }
    
    foreach ($response['data']['creators'] as $creator) {
        if (isset($creator['contact_information'])) {
            return true;
        }
        // Cek juga kemungkinan field lain
        if (isset($creator['whatsapp']) || isset($creator['phone']) || 
            isset($creator['email']) || isset($creator['contact_info'])) {
            return true;
        }
    }
    return false;
}

/**
 * Helper: Cek apakah ada contact info di detail response
 */
private function has_contact_info_in_detail_response($response) {
    if (!$response['success'] || !isset($response['data'])) {
        return false;
    }
    
    $data = $response['data'];
    
    return isset($data['contact_information']) || 
           isset($data['whatsapp']) || 
           isset($data['phone']) ||
           isset($data['email']) ||
           isset($data['contact_info']);
}

/**
 * Helper: Ambil semua field yang tersedia dari search response
 */
private function get_available_fields_from_search($response) {
    if (!$response['success'] || empty($response['data']['creators'])) {
        return [];
    }
    
    $first_creator = $response['data']['creators'][0];
    return array_keys($first_creator);
}

/**
 * Helper: Ambil sample creator pertama (tanpa data besar)
 */
private function get_first_creator_sample($response) {
    if (!$response['success'] || empty($response['data']['creators'])) {
        return null;
    }
    
    $creator = $response['data']['creators'][0];
    
    // Return hanya field penting untuk debugging
    return [
        'username' => $creator['username'] ?? null,
        'nickname' => $creator['nickname'] ?? null,
        'follower_count' => $creator['follower_count'] ?? null,
        'has_contact_information' => isset($creator['contact_information']),
        'available_fields' => array_keys($creator)
    ];
}

/**
 * Helper: Ambil sample data yang aman (tanpa data besar)
 */
private function get_safe_sample($data, $max_depth = 2) {
    if (!is_array($data)) {
        return $data;
    }
    
    $sample = [];
    $count = 0;
    foreach ($data as $key => $value) {
        if ($count >= 10) break;
        
        if (is_array($value) && $max_depth > 0) {
            $sample[$key] = $this->get_safe_sample($value, $max_depth - 1);
        } else if (!is_array($value)) {
            // Limit string length
            if (is_string($value) && strlen($value) > 100) {
                $sample[$key] = substr($value, 0, 100) . '...';
            } else {
                $sample[$key] = $value;
            }
        } else {
            $sample[$key] = '[array]';
        }
        $count++;
    }
    
    if (count($data) > 10) {
        $sample['..._and_more'] = (count($data) - 10) . ' more fields';
    }
    
    return $sample;
}

/**
 * Helper: Cek error codes dari responses
 */
private function check_for_error_codes(...$responses) {
    $error_codes = [];
    foreach ($responses as $response) {
        if (isset($response['code']) && $response['code'] != 0) {
            $error_codes[] = [
                'code' => $response['code'],
                'message' => $response['message'] ?? 'Unknown error'
            ];
        }
    }
    return $error_codes;
}


/**
 * Get brand open commission dari TikTok Shop
 * Mencari produk berdasarkan SHOP NAME (bukan title)
 */
public function get_brand_open_commission() {
    $this->output->set_content_type('application/json');
    
    $brand_name = $this->input->post('brand_name');
    $debug_mode = $this->input->post('debug') || $this->input->get('debug');
    
    if (empty($brand_name)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Brand name required'
        ]));
    }
    
    try {
        $search_keyword = trim($brand_name);
        
        // ========== CARI PRODUK BERDASARKAN KEYWORD (DI TITLE) ==========
        // Kita harus mencari banyak produk dulu, lalu filter berdasarkan shop_name
        $result = $this->jsm_api->search_open_collaboration_products('', 100); // Cari banyak produk
        
        if ($debug_mode) {
            return $this->output->set_output(json_encode([
                'debug' => true,
                'search_keyword' => $search_keyword,
                'api_response' => $result
            ], JSON_PRETTY_PRINT));
        }
        
        if (!$result['success']) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Gagal mencari produk: ' . ($result['message'] ?? 'Unknown error'),
                'commission' => 10
            ]));
        }
        
        $all_products = $result['data']['products'] ?? [];
        
        // ========== FILTER: HANYA AMBIL PRODUK DENGAN SHOP_NAME EXACT MATCH ==========
        $matched_products = [];
        foreach ($all_products as $product) {
            $shop_name = $product['shop']['name'] ?? '';
            
            // EXACT MATCH (case insensitive)
            if (strtolower(trim($shop_name)) === strtolower($search_keyword)) {
                $matched_products[] = $product;
            }
        }
        
        if (empty($matched_products)) {
            // Coba cari dengan pagination berikutnya
            $next_token = $result['data']['next_page_token'] ?? null;
            $page = 2;
            
            while ($next_token && count($matched_products) === 0 && $page <= 5) {
                $next_result = $this->jsm_api->search_open_collaboration_products('', 100, $next_token);
                if ($next_result['success']) {
                    $next_products = $next_result['data']['products'] ?? [];
                    foreach ($next_products as $product) {
                        $shop_name = $product['shop']['name'] ?? '';
                        if (strtolower(trim($shop_name)) === strtolower($search_keyword)) {
                            $matched_products[] = $product;
                        }
                    }
                    $next_token = $next_result['data']['next_page_token'] ?? null;
                } else {
                    break;
                }
                $page++;
            }
        }
        
        if (empty($matched_products)) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => "Tidak ditemukan shop dengan nama '{$search_keyword}' yang memiliki Open Collaboration",
                'commission' => 10,
                'has_open_collab' => false,
                'shop_not_found' => true
            ]));
        }
        
        // ========== AMBIL KOMISI DARI PRODUK PERTAMA ==========
        $first_product = $matched_products[0];
        $shop_name = $first_product['shop']['name'] ?? '';
        $product_title = $first_product['title'] ?? '';
        $product_image = $first_product['main_image_url'] ?? '';
        $detail_link = $first_product['detail_link'] ?? '';
        $commission_rate_raw = $first_product['commission']['rate'] ?? 0;
        
        // Konversi komisi (1150 -> 1.15%? atau 11.5%?)
        // Dari data: rate 1150, amount 5750, price 50000 -> 5750/50000 = 11.5%
        // Jadi rate 1150 = 11.5% (dibagi 100)
        $commission_percent = 0;
        if ($commission_rate_raw > 0) {
            $commission_percent = $commission_rate_raw / 100;
            if ($commission_percent < 1) $commission_percent = 5;
            if ($commission_percent > 50) $commission_percent = 50;
        }
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'has_open_collab' => true,
            'commission' => $commission_percent,
            'shop_name' => $shop_name,
            'product_title' => $product_title,
            'product_image' => $product_image,
            'detail_link' => $detail_link,
            'total_products_found' => count($matched_products),
            'message' => "Shop '{$shop_name}' memiliki Open Collaboration dengan komisi {$commission_percent}%"
        ]));
        
    } catch (Exception $e) {
        log_message('error', 'Error: ' . $e->getMessage());
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'commission' => 10
        ]));
    }
}


public function get_seller_contact() {
    $this->output->set_content_type('application/json');
    
    $shop_name = $this->input->get('shop_name');
    if (empty($shop_name)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Shop name required'
        ]));
    }
    
    try {
        // Step 1: Cari target collaboration berdasarkan shop name
        $search_result = $this->jsm_api->search_target_collaborations([
            'keyword' => $shop_name,
            'status' => 'NORMAL'
        ], 10);
        
        if (!$search_result['success'] || empty($search_result['data']['target_collaborations'])) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'No target collaboration found for shop: ' . $shop_name
            ]));
        }
        
        // Step 2: Ambil collaboration pertama yang match
        $collaborations = $search_result['data']['target_collaborations'];
        $target_collab_id = null;
        
        foreach ($collaborations as $collab) {
            // Cek apakah shop name match dengan produk di collaboration
            if (isset($collab['products'])) {
                foreach ($collab['products'] as $product) {
                    if (stripos($product['title'] ?? '', $shop_name) !== false) {
                        $target_collab_id = $collab['id'];
                        break 2;
                    }
                }
            }
        }
        
        if (!$target_collab_id) {
            $target_collab_id = $collaborations[0]['id'] ?? null;
        }
        
        if (!$target_collab_id) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Target collaboration ID not found'
            ]));
        }
        
        // Step 3: Ambil detail collaboration termasuk contact info
        $detail_result = $this->jsm_api->get_target_collaboration_detail($target_collab_id);
        
        if (!$detail_result['success']) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Failed to get collaboration detail'
            ]));
        }
        
        $target_collaboration = $detail_result['data']['target_collaboration'] ?? [];
        $contact_info = $target_collaboration['seller_contact_info'] ?? [];
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'target_collaboration_id' => $target_collab_id,
            'collaboration_name' => $target_collaboration['name'] ?? '',
            'contact_info' => $contact_info,
            'has_contact' => !empty($contact_info),
            'message' => !empty($contact_info) ? 'Contact info found' : 'No contact info available'
        ]));
        
    } catch (Exception $e) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]));
    }
}

/**
 * Get brand open commission AND contact info dari seller
 */
public function get_brand_details() {
    $this->output->set_content_type('application/json');
    
    $brand_name = $this->input->post('brand_name');
    
    if (empty($brand_name)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Brand name required'
        ]));
    }
    
    try {
        $search_keyword = ltrim($brand_name, '@');
        
        // ========== 1. DAPATKAN KOMISI DARI OPEN COLLAB PRODUCTS ==========
        $commission_result = $this->get_brand_open_commission_from_api($search_keyword);
        
        // ========== 2. DAPATKAN KONTAK SELLER ==========
        $contact_result = $this->get_seller_contact_from_api($search_keyword);
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'brand_name' => $search_keyword,
            'commission' => $commission_result,
            'contact_info' => $contact_result['contact_info'] ?? null,
            'has_contact' => !empty($contact_result['contact_info']),
            'message' => 'Data retrieved successfully'
        ]));
        
    } catch (Exception $e) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]));
    }
}
/**
 * Extract commission rates from seller detail response
 */
private function extract_commission_rates_from_seller($seller_data) {
    $commission_rates = [];
    
    // Coba ambil dari berbagai kemungkinan struktur response
    if (isset($seller_data['products']) && is_array($seller_data['products'])) {
        foreach ($seller_data['products'] as $product) {
            $commission = $product['open_collaboration_commission_rate'] ?? 
                         $product['commission_rate'] ?? 0;
            
            if ($commission > 0) {
                if ($commission < 1) $commission = $commission * 100;
                if ($commission <= 50) $commission_rates[] = $commission;
            }
        }
    }
    
    // Coba dari creator_commission_rates
    if (isset($seller_data['creator_commission_rates']) && is_array($seller_data['creator_commission_rates'])) {
        foreach ($seller_data['creator_commission_rates'] as $rate) {
            $commission = $rate['commission_rate'] ?? 0;
            if ($commission > 0 && $commission <= 50) {
                $commission_rates[] = $commission;
            }
        }
    }
    
    // Coba dari campaign_products
    if (isset($seller_data['campaign_products']) && is_array($seller_data['campaign_products'])) {
        foreach ($seller_data['campaign_products'] as $product) {
            $commission = $product['open_collaboration_commission_rate'] ?? 0;
            if ($commission > 0 && $commission <= 50) {
                $commission_rates[] = $commission;
            }
        }
    }
    
    return $commission_rates;
}

/**
 * DEBUG: Cek raw response dari Open Collaboration Products API
 * URL: /bd/debug_open_collab?keyword=FILA
 */
public function debug_open_collab() {
    $this->output->set_content_type('application/json');
    
    $keyword = $this->input->get('keyword');
    if (empty($keyword)) {
        $keyword = $this->input->post('keyword');
    }
    
    if (empty($keyword)) {
        return $this->output->set_output(json_encode([
            'error' => 'Parameter keyword required',
            'usage' => '/bd/debug_open_collab?keyword=FILA'
        ]));
    }
    
    $results = [];
    
    // ========== TEST 1: Open Collaboration Products Search ==========
    $results['test_1_open_collab_products'] = $this->jsm_api->search_open_collaboration_products($keyword, 20);
    
    // ========== TEST 2: Regular Products Search (sebagai pembanding) ==========
    $results['test_2_regular_products_search'] = $this->jsm_api->search_products([
        'keyword' => $keyword,
        'page_size' => 20,
        'has_open_collaboration' => true
    ]);
    
    // ========== TEST 3: Search Marketplace Creators ==========
    $results['test_3_marketplace_creators'] = $this->jsm_api->search_marketplace_creators([
        'keyword' => $keyword,
        'country_codes' => ['ID'],
        'page_size' => 10
    ]);
    
    // ========== RINGKASAN ==========
    $summary = [
        'keyword' => $keyword,
        'open_collab_products_count' => isset($results['test_1_open_collab_products']['data']['products']) ? count($results['test_1_open_collab_products']['data']['products']) : 0,
        'regular_products_count' => isset($results['test_2_regular_products_search']['data']) ? count($results['test_2_regular_products_search']['data']) : 0,
        'creators_found' => isset($results['test_3_marketplace_creators']['data']['creators']) ? count($results['test_3_marketplace_creators']['data']['creators']) : 0,
        'sample_shops_from_open_collab' => [],
        'sample_shops_from_regular' => []
    ];
    
    // Ambil sample shop names dari open collab
    if (isset($results['test_1_open_collab_products']['data']['products'])) {
        $shops = [];
        foreach ($results['test_1_open_collab_products']['data']['products'] as $p) {
            $shop = $p['shop']['name'] ?? '';
            if (!empty($shop) && !in_array($shop, $shops)) {
                $shops[] = $shop;
            }
            if (count($shops) >= 10) break;
        }
        $summary['sample_shops_from_open_collab'] = $shops;
    }
    
    // Ambil sample shop names dari regular search
    if (isset($results['test_2_regular_products_search']['data'])) {
        $shops = [];
        foreach ($results['test_2_regular_products_search']['data'] as $p) {
            $shop = $p['shop_name'] ?? '';
            if (!empty($shop) && !in_array($shop, $shops)) {
                $shops[] = $shop;
            }
            if (count($shops) >= 10) break;
        }
        $summary['sample_shops_from_regular'] = $shops;
    }
    
    $results['summary'] = $summary;
    
    return $this->output->set_output(json_encode($results, JSON_PRETTY_PRINT));
}


/**
 * Get seller contact info by shop name (private helper)
 */
private function get_seller_contact_info_by_shop($shop_name) {
    try {
        // 🔥 Cari target collaboration dengan parameter yang benar
        $search_result = $this->jsm_api->search_target_collaborations([
            'keyword' => $shop_name,
            'keyword_type' => 'TARGET_COLLABORATION_NAME',
            'collaboration_status' => 'ONGOING',
            'creator_accept_status' => 'ALL'
        ], 10);
        
        if (!$search_result['success']) {
            log_message('debug', 'Search target collaborations failed: ' . ($search_result['message'] ?? 'Unknown error'));
            return null;
        }
        
        $collaborations = $search_result['data']['target_collaborations'] ?? [];
        
        if (empty($collaborations)) {
            log_message('debug', 'No target collaborations found for shop: ' . $shop_name);
            return null;
        }
        
        // Ambil collaboration pertama
        $target_collab_id = $collaborations[0]['id'] ?? null;
        
        if (!$target_collab_id) {
            return null;
        }
        
        // Ambil detail collaboration
        $detail_result = $this->jsm_api->get_target_collaboration_detail($target_collab_id);
        
        if (!$detail_result['success']) {
            log_message('debug', 'Get collaboration detail failed: ' . ($detail_result['message'] ?? 'Unknown error'));
            return null;
        }
        
        $target_collaboration = $detail_result['data']['target_collaboration'] ?? [];
        $contact_info = $target_collaboration['seller_contact_info'] ?? [];
        
        if (!empty($contact_info)) {
            // Format nomor WhatsApp
            if (isset($contact_info['whatsapp']) && !empty($contact_info['whatsapp'])) {
                $contact_info['whatsapp_formatted'] = $this->format_whatsapp_number($contact_info['whatsapp']);
            }
            if (isset($contact_info['phone_number']) && !empty($contact_info['phone_number'])) {
                $contact_info['phone_formatted'] = $this->format_whatsapp_number($contact_info['phone_number']);
            }
            return $contact_info;
        }
        
        return null;
        
    } catch (Exception $e) {
        log_message('error', 'Error getting contact info: ' . $e->getMessage());
        return null;
    }
}

/**
 * Format nomor WhatsApp (tambahkan +62 jika perlu)
 */
private function format_whatsapp_number($number) {
    if (empty($number)) return '';
    
    // Hapus karakter non-digit
    $number = preg_replace('/[^0-9]/', '', $number);
    
    // Jika dimulai dengan 0, ganti dengan +62
    if (preg_match('/^0/', $number)) {
        $number = '+62' . substr($number, 1);
    }
    // Jika tidak dimulai dengan +, tambahkan +
    elseif (!preg_match('/^\+/', $number) && !preg_match('/^62/', $number)) {
        $number = '+' . $number;
    }
    // Jika dimulai dengan 62 tanpa +, tambahkan +
    elseif (preg_match('/^62/', $number) && !preg_match('/^\+/', $number)) {
        $number = '+' . $number;
    }
    
    return $number;
}


/**
 * DEBUG: Cek contact info untuk shop tertentu
 * URL: /bd/debug_contact?shop_name=HSD.IDN
 */

public function debug_product_detail() {
    $this->output->set_content_type('application/json');
    
    $product_id = $this->input->get('product_id');
    if (empty($product_id)) {
        return $this->output->set_output(json_encode([
            'error' => 'Parameter product_id required',
            'usage' => '/bd/debug_product_detail?product_id=1730001421726747512'
        ]));
    }
    
    $results = [];
    
    // ========== TEST 1: Parameter sebagai boolean (true/false) ==========
    $results['test_1_boolean_params'] = $this->_call_product_detail_api($product_id, [
        'return_under_review_version' => 'true',
        'return_draft_version' => 'true',
        'locale' => 'id'
    ]);
    
    // ========== TEST 2: Parameter sebagai integer (1/0) ==========
    $results['test_2_int_params'] = $this->_call_product_detail_api($product_id, [
        'return_under_review_version' => 1,
        'return_draft_version' => 1,
        'locale' => 'id'
    ]);
    
    // ========== TEST 3: Tanpa parameter tambahan ==========
    $results['test_3_no_extra_params'] = $this->_call_product_detail_api($product_id, []);
    
    // ========== TEST 4: Hanya dengan locale ==========
    $results['test_4_only_locale'] = $this->_call_product_detail_api($product_id, [
        'locale' => 'id'
    ]);
    
    // ========== TEST 5: Parameter sebagai string "true" ==========
    $results['test_5_string_true'] = $this->_call_product_detail_api($product_id, [
        'return_under_review_version' => 'true',
        'return_draft_version' => 'true'
    ]);
    
    // ========== TEST 6: Cek apakah shop_cipher bermasalah ==========
    // Ambil shop_cipher dari database
    $seller_token = $this->Jsm_token_model->get_latest_token_by_type(2);
    $shop_cipher = $seller_token->shop_id ?? '';
    
    $results['shop_cipher_info'] = [
        'shop_cipher' => $shop_cipher,
        'shop_cipher_length' => strlen($shop_cipher),
        'has_shop_cipher' => !empty($shop_cipher)
    ];
    
    return $this->output->set_output(json_encode($results, JSON_PRETTY_PRINT));
}

/**
 * Helper untuk memanggil API product detail
 */
private function _call_product_detail_api($product_id, $extra_params = []) {
    $path = "/product/202309/products/{$product_id}";
    
    try {
        // Ambil token seller
        $access_token = $this->jsm_api->get_valid_seller_token();
        
        // Ambil shop_cipher dari database
        $seller_token = $this->Jsm_token_model->get_latest_token_by_type(2);
        $shop_cipher = $seller_token->shop_id ?? '';
        
        $timestamp = time();
        
        // Query parameters dasar
        $query = [
            'app_key' => $this->jsm_api->get_app_key(),
            'timestamp' => $timestamp,
            'shop_cipher' => $shop_cipher
        ];
        
        // Tambahkan parameter tambahan
        foreach ($extra_params as $key => $value) {
            $query[$key] = $value;
        }
        
        // Sort query untuk signature
        ksort($query);
        
        // Buat string untuk signature
        $param_string = '';
        foreach ($query as $key => $value) {
            $param_string .= $key . $value;
        }
        
        // Buat signature - gunakan getter
        $string_to_sign = $this->jsm_api->get_app_secret() . $path . $param_string . $this->jsm_api->get_app_secret();
        $query['sign'] = hash_hmac('sha256', $string_to_sign, $this->jsm_api->get_app_secret());
        
        // Buat URL - gunakan getter
        $url = $this->jsm_api->get_openapi_base() . $path . '?' . http_build_query($query);
        
        // Log untuk debug
        log_message('debug', 'Product Detail URL: ' . $url);
        log_message('debug', 'Query params: ' . json_encode($query));
        
        // Headers
        $headers = [
            "x-tts-access-token: " . $access_token,
            "Content-Type: application/json"
        ];
        
        // Execute curl
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return [
                'success' => false, 
                'message' => "cURL Error: {$error}",
                'http_code' => $http_code
            ];
        }
        
        curl_close($ch);
        
        $decoded = json_decode($response, true);
        
        return [
            'success' => isset($decoded['code']) && $decoded['code'] == 0,
            'code' => $decoded['code'] ?? null,
            'message' => $decoded['message'] ?? null,
            'http_code' => $http_code,
            'params_sent' => $query,
            'response' => $decoded
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false, 
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ];
    }
}




/**
 * DEBUG LENGKAP: Tampilkan semua raw response dari API
 * URL: /bd/debug_full?keyword=hanasui
 */
public function debug_full() {
    $this->output->set_content_type('application/json');
    
    $keyword = $this->input->get('keyword');
    if (empty($keyword)) {
        return $this->output->set_output(json_encode([
            'error' => 'Parameter keyword required',
            'usage' => '/bd/debug_full?keyword=hanasui'
        ]));
    }
    
    $results = [];
    
    // ========== 1. SEARCH OPEN COLLABORATION PRODUCTS (LENGKAP DENGAN SHARELINK) ==========
    $open_collab = $this->jsm_api->search_open_collaboration_products($keyword, 50);
    
    if ($open_collab['success'] && isset($open_collab['data']['products'])) {
        $products_detail = [];
        foreach ($open_collab['data']['products'] as $p) {
            $products_detail[] = [
                'id' => $p['id'] ?? '',
                'title' => $p['title'] ?? '',
                'shop_name' => $p['shop']['name'] ?? '',
                'shop_id' => $p['shop']['id'] ?? '',
                'commission_rate' => $p['commission']['rate'] ?? 0,
                'commission_amount' => $p['commission']['amount'] ?? 0,
                'commission_currency' => $p['commission']['currency'] ?? '',
                'price_min' => $p['sales_price']['minimum_amount'] ?? 0,
                'price_max' => $p['sales_price']['maximum_amount'] ?? 0,
                'original_price_min' => $p['original_price']['minimum_amount'] ?? 0,
                'original_price_max' => $p['original_price']['maximum_amount'] ?? 0,
                'currency' => $p['sales_price']['currency'] ?? 'IDR',
                'image_url' => $p['main_image_url'] ?? '',
                'detail_link' => $p['detail_link'] ?? '',  // 🔥 SHARELINK
                'has_inventory' => $p['has_inventory'] ?? false,
                'units_sold' => $p['units_sold'] ?? 0,
                'sale_region' => $p['sale_region'] ?? '',
                'category' => $p['category_chains'][0]['local_name'] ?? ''
            ];
        }
        $results['open_collaboration_products'] = [
            'success' => true,
            'total_count' => $open_collab['data']['total_count'] ?? 0,
            'products' => $products_detail
        ];
    } else {
        $results['open_collaboration_products'] = $open_collab;
    }
    
    // ========== 2. SEARCH MARKETPLACE CREATORS ==========
    $creators_result = $this->jsm_api->search_marketplace_creators([
        'keyword' => $keyword,
        'country_codes' => ['ID'],
        'page_size' => 50
    ]);
    
    if ($creators_result['success'] && isset($creators_result['data']['creators'])) {
        $creators_detail = [];
        foreach ($creators_result['data']['creators'] as $c) {
            $creators_detail[] = [
                'username' => $c['username'] ?? '',
                'nickname' => $c['nickname'] ?? '',
                'follower_count' => $c['follower_count'] ?? 0,
                'creator_open_id' => $c['creator_open_id'] ?? '',
                'avatar_url' => $c['avatar']['url'] ?? '',
                'selection_region' => $c['selection_region'] ?? ''
            ];
        }
        $results['marketplace_creators'] = [
            'success' => true,
            'creators' => $creators_detail
        ];
    } else {
        $results['marketplace_creators'] = $creators_result;
    }
    
    // ========== 3. SEARCH TARGET COLLABORATIONS ==========
    $results['target_collaborations'] = $this->jsm_api->search_target_collaborations([
        'keyword' => $keyword,
        'collaboration_status' => 'ONGOING',
        'creator_accept_status' => 'ALL'
    ], 20);
    
    // ========== 4. RINGKASAN ==========
    $summary = [
        'keyword' => $keyword,
        'open_collab_products_count' => count($results['open_collaboration_products']['products'] ?? []),
        'creators_count' => count($results['marketplace_creators']['creators'] ?? []),
        'shop_names_found' => [],
        'exact_match_found' => false,
        'sample_sharelinks' => []
    ];
    
    // Ambil semua shop name dan sharelink
    if (isset($results['open_collaboration_products']['products'])) {
        $shops = [];
        foreach ($results['open_collaboration_products']['products'] as $p) {
            if ($p['shop_name'] && !in_array($p['shop_name'], $shops)) {
                $shops[] = $p['shop_name'];
            }
            if (count($summary['sample_sharelinks']) < 5) {
                $summary['sample_sharelinks'][] = [
                    'shop_name' => $p['shop_name'],
                    'product_title' => substr($p['title'], 0, 50),
                    'detail_link' => $p['detail_link']
                ];
            }
        }
        $summary['shop_names_found'] = $shops;
        $summary['exact_match_found'] = in_array(strtolower($keyword), array_map('strtolower', $shops));
    }
    
    $results['summary'] = $summary;
    
    return $this->output->set_output(json_encode($results, JSON_PRETTY_PRINT));
}
/**
 * Helper untuk mencari shop (custom)
 */
private function debug_search_shops($keyword) {
    // Coba berbagai kemungkinan endpoint
    $results = [];
    
    // Endpoint 1: /seller/202309/shops
    $path1 = "/seller/202309/shops";
    $results['endpoint_shops'] = $this->try_api_request($path1, ['shop_name' => $keyword], 'GET');
    
    // Endpoint 2: /affiliate_seller/202405/shops/search
    $path2 = "/affiliate_seller/202405/shops/search";
    $body2 = ['shop_name_keyword' => $keyword];
    $results['endpoint_shops_search'] = $this->try_api_request($path2, [], 'POST', $body2);
    
    return $results;
}

/**
 * Helper untuk mencoba API request
 */
private function try_api_request($path, $params = [], $method = 'GET', $body = null) {
    try {
        $access_token = $this->jsm_api->get_valid_seller_token();
        $timestamp = time();
        
        $seller_token = $this->Jsm_token_model->get_latest_token_by_type(2);
        $shop_cipher = $seller_token->shop_id ?? '';
        
        $query = [
            'app_key' => $this->jsm_api->get_app_key(),
            'timestamp' => $timestamp,
            'shop_cipher' => $shop_cipher
        ];
        
        foreach ($params as $key => $value) {
            if ($value !== '' && $value !== null) {
                $query[$key] = $value;
            }
        }
        
        ksort($query);
        
        $param_string = '';
        foreach ($query as $key => $value) {
            $param_string .= $key . $value;
        }
        
        $string_to_sign = $this->jsm_api->get_app_secret() . $path . $param_string . $this->jsm_api->get_app_secret();
        
        if ($method === 'POST' && $body !== null) {
            $body_json = is_array($body) ? json_encode($body) : $body;
            $string_to_sign = $this->jsm_api->get_app_secret() . $path . $param_string . $body_json . $this->jsm_api->get_app_secret();
        }
        
        $query['sign'] = hash_hmac('sha256', $string_to_sign, $this->jsm_api->get_app_secret());
        
        $url = $this->jsm_api->get_openapi_base() . $path . '?' . http_build_query($query);
        
        $headers = [
            "x-tts-access-token: " . $access_token,
            "Content-Type: application/json"
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($body) ? json_encode($body) : $body);
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        return json_decode($response, true);
        
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

// ========== TARGET PLAN REQUEST (BD) ==========

/**
 * Get target plan requests for BD (Leader BD)
 */
public function get_target_requests_bd() {
    $this->output->set_content_type('application/json');
    
    $status = $this->input->post('status');
    
    $this->db->select('tpr.*, c.username, c.full_name as creator_full_name, c.phone as creator_phone')
             ->from('target_plan_requests tpr')
             ->join('creators c', 'tpr.creator_id = c.id', 'left')
             ->where_in('tpr.status', ['IS_APPROVED', 'BD_APPROVED', 'BD_REJECTED']);
    
    if ($status && $status != 'all') {
        $this->db->where('tpr.status', $status);
    }
    
    $requests = $this->db->order_by('tpr.created_at', 'DESC')->get()->result();
    
    foreach ($requests as $req) {
        $req->products = json_decode($req->products, true);
        $req->generated_links = json_decode($req->generated_links, true);
    }
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'requests' => $requests,
        'count' => count($requests)
    ]));
}

/**
 * Approve target request and generate links (Leader BD)
 */
public function approve_target_request_bd() {
    $this->output->set_content_type('application/json');
    
    $request_id = $this->input->post('request_id');
    
    if (!$request_id) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Request ID required'
        ]));
    }
    
    $request = $this->db->get_where('target_plan_requests', ['id' => $request_id])->row();
    
    if (!$request) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Request not found'
        ]));
    }
    
    $user_id = $this->session->userdata('user_id');
    $username = $this->session->userdata('username');
    $full_name = $this->session->userdata('full_name');
    
    $products = json_decode($request->products, true);
    $generated_links = [];
    
    // Generate affiliate links for each product
    foreach ($products as $product) {
        $link_result = $this->jsm_api->generate_promotion_link(
            $request->campaign_id,
            $product['product_id'],
            $request->requested_commission
        );
        
        if ($link_result['success']) {
            $generated_links[] = [
                'product_id' => $product['product_id'],
                'product_name' => $product['product_name'],
                'affiliate_link' => $link_result['link'],
                'commission_rate' => $request->requested_commission
            ];
            
            // Save to affiliate_creator_links
            $this->db->insert('affiliate_creator_links', [
                'creator_id' => $request->creator_id,
                'creator_username' => $request->creator_username,
                'campaign_id' => $request->campaign_id,
                'product_id' => $product['product_id'],
                'product_name' => $product['product_name'],
                'affiliate_link' => $link_result['link'],
                'commission_rate' => $request->requested_commission,
                'shared_date' => date('Y-m-d H:i:s'),
                'status' => 'ACTIVE',
                'source' => 'target_plan',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    $this->db->where('id', $request_id)
             ->update('target_plan_requests', [
                 'status' => 'BD_APPROVED',
                 'bd_approved' => 1,
                 'approved_by_bd' => $full_name ?: $username,
                 'approved_at_bd' => date('Y-m-d H:i:s'),
                 'generated_links' => json_encode($generated_links),
                 'updated_at' => date('Y-m-d H:i:s')
             ]);
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'message' => 'Request approved and links generated',
        'generated_links' => $generated_links
    ]));
}

/**
 * Reject target request (Leader BD)
 */
public function reject_target_request_bd() {
    $this->output->set_content_type('application/json');
    
    $request_id = $this->input->post('request_id');
    $reject_reason = $this->input->post('reject_reason');
    
    if (!$request_id) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Request ID required'
        ]));
    }
    
    $user_id = $this->session->userdata('user_id');
    $username = $this->session->userdata('username');
    $full_name = $this->session->userdata('full_name');
    
    $this->db->where('id', $request_id)
             ->update('target_plan_requests', [
                 'status' => 'BD_REJECTED',
                 'reject_reason' => $reject_reason,
                 'rejected_by' => $full_name ?: $username,
                 'rejected_at' => date('Y-m-d H:i:s'),
                 'updated_at' => date('Y-m-d H:i:s')
             ]);
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'message' => 'Request rejected'
    ]));
}

/**
 * Get pending target requests for IS dashboard (to send)
 */
public function get_pending_target_requests_for_is() {
    $this->output->set_content_type('application/json');
    
    $user_id = $this->session->userdata('user_id');
    $role = $this->session->userdata('role');
    $is_supervisor = ($user_id == 2 || $role == 'admin');
    
    $this->db->select('tpr.*, c.username, c.full_name as creator_full_name, c.phone as creator_phone')
             ->from('target_plan_requests tpr')
             ->join('creators c', 'tpr.creator_id = c.id', 'left')
             ->where('tpr.status', 'BD_APPROVED')
             ->where('tpr.is_sent', 0);
    
    if (!$is_supervisor) {
        $this->db->where('tpr.created_by', $user_id);
    }
    
    $requests = $this->db->order_by('tpr.created_at', 'ASC')->get()->result();
    
    foreach ($requests as $req) {
        $req->products = json_decode($req->products, true);
        $req->generated_links = json_decode($req->generated_links, true);
    }
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'requests' => $requests,
        'count' => count($requests)
    ]));
}

/**
 * Mark target request as sent (IS)
 */
public function mark_target_request_sent() {
    $this->output->set_content_type('application/json');
    
    $request_id = $this->input->post('request_id');
    
    if (!$request_id) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Request ID required'
        ]));
    }
    
    $this->db->where('id', $request_id)
             ->update('target_plan_requests', [
                 'status' => 'COMPLETED',
                 'is_sent' => 1,
                 'sent_at' => date('Y-m-d H:i:s'),
                 'updated_at' => date('Y-m-d H:i:s')
             ]);
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'message' => 'Request marked as sent'
    ]));
}
public function target_plan_dashboard() {
    $user_id = $this->session->userdata('user_id');
    $role = $this->session->userdata('role');
    $is_supervisor = ($user_id == 1 || $role == 'admin');
    
    $data = [
        'title' => 'Target Plan - Toopai BD',
        'active_menu' => 'target_plan',
        'is_supervisor' => $is_supervisor
    ];
    
    $this->load->view('templates/header', $data);
    $this->load->view('bd/target_plan_dashboard', $data);
    $this->load->view('templates/footer');
}

/**
 * DEBUG: Test multi link dengan berbagai format sekaligus
 * URL: /bd/debug_multi_all?campaign_id=7626279878945982225&product_ids=1735648652347540806,1735648432511681862
 */
public function debug_multi_all() {
    $this->output->set_content_type('application/json');
    
    $campaign_id = $this->input->get('campaign_id');
    $product_ids_str = $this->input->get('product_ids');
    
    if (empty($campaign_id) || empty($product_ids_str)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Campaign ID and Product IDs required'
        ]));
    }
    
    $product_ids_raw = explode(',', $product_ids_str);
    $category_asset_cipher = $this->jsm_api->default_cipher ?? '';
    
    // 🔥 BEBERAPA FORMAT YANG AKAN DICOBA
    $test_cases = [
        'integer' => array_map('intval', $product_ids_raw),
        'string' => array_map('strval', $product_ids_raw),
        'scientific_1digit' => [],
        'scientific_2digit' => [],
        'scientific_full' => [],
    ];
    
    // Buat scientific notation untuk setiap product_id
    foreach ($product_ids_raw as $pid) {
        $pid_str = (string)$pid;
        $len = strlen($pid_str);
        $test_cases['scientific_1digit'][] = $pid_str[0] . '.' . substr($pid_str, 1, 1) . 'e+' . ($len - 1);
        $test_cases['scientific_2digit'][] = $pid_str[0] . '.' . substr($pid_str, 1, 2) . 'e+' . ($len - 1);
        $test_cases['scientific_full'][] = $pid_str[0] . '.' . substr($pid_str, 1, 15) . 'e+' . ($len - 1);
    }
    
    $results = [];
    
    foreach ($test_cases as $format_name => $product_ids) {
        $result = $this->_call_multi_link_api_raw($campaign_id, $product_ids, $category_asset_cipher);
        $results[$format_name] = [
            'product_ids' => $product_ids,
            'success' => $result['success'],
            'code' => $result['code'],
            'message' => $result['message'],
            'http_code' => $result['http_code']
        ];
        
        if ($result['success']) {
            return $this->output->set_output(json_encode([
                'success' => true,
                'working_format' => $format_name,
                'working_product_ids' => $product_ids,
                'response' => $result,
                'all_tests' => $results
            ], JSON_PRETTY_PRINT));
        }
    }
    
    return $this->output->set_output(json_encode([
        'success' => false,
        'campaign_id' => $campaign_id,
        'category_asset_cipher' => $category_asset_cipher,
        'results' => $results,
        'note' => 'Semua format gagal. Kemungkinan endpoint multi link tidak diaktifkan untuk akun ini.'
    ], JSON_PRETTY_PRINT));
}

private function _call_multi_link_api_raw($campaign_id, $product_ids, $category_asset_cipher) {
    try {
        $access_token = $this->jsm_api->get_valid_token();
        $timestamp = time();
        
        $path = "/affiliate_partner/202505/campaigns/{$campaign_id}/products/promotion_links/generate_batch";
        
        $query = [
            'app_key' => $this->jsm_api->app_key,
            'timestamp' => $timestamp
        ];
        
        if (!empty($category_asset_cipher)) {
            $query['category_asset_cipher'] = $category_asset_cipher;
        }
        
        ksort($query);
        
        $body = ['product_ids' => $product_ids];
        $body_json = json_encode($body);
        
        $param_string = '';
        foreach ($query as $key => $value) {
            $param_string .= $key . $value;
        }
        
        $string_to_sign = $this->jsm_api->app_secret . $path . $param_string . $body_json . $this->jsm_api->app_secret;
        $query['sign'] = hash_hmac('sha256', $string_to_sign, $this->jsm_api->app_secret);
        
        $url = $this->jsm_api->get_openapi_base() . $path . '?' . http_build_query($query);
        
        $headers = [
            "x-tts-access-token: " . $access_token,
            "Content-Type: application/json"
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body_json,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $decoded = json_decode($response, true);
        
        return [
            'success' => (isset($decoded['code']) && $decoded['code'] == 0),
            'code' => $decoded['code'] ?? null,
            'message' => $decoded['message'] ?? 'Unknown',
            'http_code' => $http_code,
            'response' => $decoded
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Get active campaigns untuk dropdown di Task 2
 */
public function get_active_campaigns() {
    $this->output->set_content_type('application/json');
    
    $campaigns = $this->db->select('campaign_id, campaign_name, status')
        ->where('status', 'ONGOING')
        ->order_by('created_at', 'DESC')
        ->limit(50)
        ->get('affiliate_campaigns')
        ->result();
    
    // Tambahkan link campaign (opsional)
    foreach ($campaigns as $camp) {
        $camp->campaign_link = 'https://partner.tiktokshop.com/campaign/' . $camp->campaign_id;
    }
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'campaigns' => $campaigns,
        'total' => count($campaigns)
    ]));
}

/**
 * Get campaign info by campaign_id
 */
public function get_campaign_info() {
    $this->output->set_content_type('application/json');
    
    $campaign_id = $this->input->post('campaign_id');
    
    if (!$campaign_id) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Campaign ID required'
        ]));
    }
    
    $campaign = $this->db->select('campaign_id, campaign_name, status')
        ->where('campaign_id', $campaign_id)
        ->get('affiliate_campaigns')
        ->row();
    
    if ($campaign) {
        return $this->output->set_output(json_encode([
            'success' => true,
            'data' => $campaign
        ]));
    }
    
    return $this->output->set_output(json_encode([
        'success' => false,
        'message' => 'Campaign not found'
    ]));
}

/**
 * Check if all products are approved and move brand to ACTIVE (Task 4)
 * Called from JavaScript after approve action
 */
public function check_and_move_to_active() {
    $this->output->set_content_type('application/json');
    
    $brand_id = $this->input->post('brand_id');
    $brand_name = $this->input->post('brand_name');
    
    if (!$brand_id && !$brand_name) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Brand ID or Brand Name required'
        ]));
    }
    
    // Ambil brand name jika hanya ada brand_id
    if ($brand_id && !$brand_name) {
        $brand = $this->db->select('name, status')->where('id', $brand_id)->get('brands')->row();
        if (!$brand) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Brand not found'
            ]));
        }
        $brand_name = $brand->name;
        $current_status = $brand->status;
    } else {
        $brand = $this->db->select('id, status')->where('name', $brand_name)->get('brands')->row();
        if (!$brand) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Brand not found'
            ]));
        }
        $brand_id = $brand->id;
        $current_status = $brand->status;
    }
    
    // 🔥 CEK PRODUK DI AFFILIATE_PRODUCTS
    $pending_count = $this->db->select('COUNT(*) as total')
        ->from('affiliate_products')
        ->where('shop_name', $brand_name)
        ->where('review_status', 'PENDING')
        ->get()
        ->row()
        ->total ?? 0;
    
    $approved_count = $this->db->select('COUNT(*) as total')
        ->from('affiliate_products')
        ->where('shop_name', $brand_name)
        ->where('review_status', 'APPROVED')
        ->get()
        ->row()
        ->total ?? 0;
    
    $total_products = $this->db->select('COUNT(*) as total')
        ->from('affiliate_products')
        ->where('shop_name', $brand_name)
        ->get()
        ->row()
        ->total ?? 0;
    
    $has_submitted = $total_products > 0;
    $has_pending = $pending_count > 0;
    $has_approved = $approved_count > 0;
    $all_approved = $has_submitted && !$has_pending && $has_approved;
    
    // 🔥 AUTO-UPDATE: Jika semua produk sudah approve
    if ($all_approved && $current_status != 'ACTIVE') {
        $this->db->where('id', $brand_id)
                 ->update('brands', [
                     'status' => 'ACTIVE',
                     'current_task' => 4,
                     'campaign_launched_at' => date('Y-m-d H:i:s'),
                     'updated_at' => date('Y-m-d H:i:s')
                 ]);
        
        log_message('info', "Brand '{$brand_name}' (ID: {$brand_id}) auto-moved to ACTIVE (all products approved)");
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'moved_to_active' => true,
            'brand_name' => $brand_name,
            'brand_id' => $brand_id,
            'new_status' => 'ACTIVE',
            'message' => '✅ Semua produk sudah approve! Brand pindah ke Task 4 (MONITORING).',
            'pending_count' => $pending_count,
            'approved_count' => $approved_count,
            'total_products' => $total_products
        ]));
    }
    
    // 🔥 JIKA MASIH ADA PENDING
    if ($has_pending) {
        return $this->output->set_output(json_encode([
            'success' => true,
            'moved_to_active' => false,
            'brand_name' => $brand_name,
            'brand_id' => $brand_id,
            'current_status' => $current_status,
            'message' => "⏳ Masih ada {$pending_count} produk pending. Approve semua produk untuk pindah ke Task 4.",
            'pending_count' => $pending_count,
            'approved_count' => $approved_count,
            'total_products' => $total_products
        ]));
    }
    
    // 🔥 BELUM ADA PRODUK SAMA SEKALI
    if (!$has_submitted) {
        return $this->output->set_output(json_encode([
            'success' => true,
            'moved_to_active' => false,
            'brand_name' => $brand_name,
            'brand_id' => $brand_id,
            'current_status' => $current_status,
            'message' => "⚠️ Belum ada produk yang diajukan. Tunggu brand registrasi campaign.",
            'pending_count' => $pending_count,
            'approved_count' => $approved_count,
            'total_products' => $total_products
        ]));
    }
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'moved_to_active' => false,
        'brand_name' => $brand_name,
        'brand_id' => $brand_id,
        'current_status' => $current_status,
        'message' => "Status brand: {$current_status}",
        'pending_count' => $pending_count,
        'approved_count' => $approved_count,
        'total_products' => $total_products
    ]));
}

/**
 * Get list of active brands (status ACTIVE)
 * For modal popup
 */
public function get_active_brands_list() {
    $this->output->set_content_type('application/json');
    
    $user_id = $this->session->userdata('user_id');
    $is_supervisor = ($user_id == 1);
    $today = date('Y-m-d');
    
    // Ambil semua brand dengan status ACTIVE
    $this->db->select('b.id, b.name, b.status, b.deal_confirmed_at, b.campaign_launched_at, b.input_by, b.input_by_name, u.full_name as handler, u.username as bd_username')
        ->from('brands b')
        ->join('users u', 'b.bd_id = u.id', 'left')
        ->where('b.status', 'ACTIVE')
        ->order_by('b.name', 'ASC');
    
    if (!$is_supervisor) {
        $this->db->where('b.bd_id', $user_id);
    }
    
    $brands = $this->db->get()->result();
    
    $result = [];
    foreach ($brands as $brand) {
        // Hitung produk APPROVED
        $approved_count = $this->db->select('COUNT(*) as total')
            ->from('affiliate_products')
            ->where('shop_name', $brand->name)
            ->where('review_status', 'APPROVED')
            ->get()
            ->row()
            ->total ?? 0;
        
        // Hitung produk PENDING
        $pending_count = $this->db->select('COUNT(*) as total')
            ->from('affiliate_products')
            ->where('shop_name', $brand->name)
            ->where('review_status', 'PENDING')
            ->get()
            ->row()
            ->total ?? 0;
        
        // Hitung GMV hari ini
        $gmv_stats = $this->db->select('COALESCE(SUM(o.gmv), 0) as total_gmv')
            ->from('affiliate_orders o')
            ->join('affiliate_products ap', 'o.product_id = ap.product_id AND o.campaign_id = ap.campaign_id')
            ->where('ap.shop_name', $brand->name)
            ->where('ap.review_status', 'APPROVED')
            ->where('o.order_date_local', $today)
            ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->get()
            ->row();
        
        $result[] = [
            'id' => $brand->id,
            'name' => $brand->name,
            'status' => $brand->status,
            'deal_confirmed_at' => $brand->deal_confirmed_at,
            'campaign_launched_at' => $brand->campaign_launched_at,
            'input_by' => $brand->input_by,
            'input_by_name' => $brand->input_by_name,
            'handler' => $brand->handler,
            'bd_username' => $brand->bd_username,
            'approved_products_count' => intval($approved_count),
            'pending_products_count' => intval($pending_count),
            'has_pending' => $pending_count > 0,
            'today_gmv' => floatval($gmv_stats->total_gmv ?? 0),
        ];
    }
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'brands' => $result,
        'total' => count($result)
    ]));
}

    public function reject_brand() {
        $this->output->set_content_type('application/json');
        
        $brand_id = $this->input->post('brand_id');
        $brand_name = $this->input->post('brand_name');
        
        if (!$brand_id && !$brand_name) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Brand ID or Brand Name required'
            ]));
        }
        
        if ($brand_id && !$brand_name) {
            $brand = $this->db->select('name')->where('id', $brand_id)->get('brands')->row();
            if ($brand) {
                $brand_name = $brand->name;
            }
        } elseif (!$brand_id && $brand_name) {
            $brand = $this->db->select('id')->where('name', $brand_name)->get('brands')->row();
            if ($brand) {
                $brand_id = $brand->id;
            }
        }
        
        if (!$brand_id) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Brand not found'
            ]));
        }
        
        $this->db->trans_start();
        
        // 1. Update status brand kembali ke FOLLOW_UP dan task ke 2
        $this->db->where('id', $brand_id)
                 ->update('brands', [
                     'status' => 'FOLLOW_UP',
                     'current_task' => 2,
                     'updated_at' => date('Y-m-d H:i:s')
                 ]);
                 
        // 2. Hapus produk-produk pending dari brand di affiliate_products agar bersih
        if ($brand_name) {
            $this->db->where('shop_name', $brand_name)
                     ->where('review_status', 'PENDING')
                     ->delete('affiliate_products');
        }
        
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Gagal memproses penolakan brand'
            ]));
        }
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'message' => 'Brand berhasil ditolak dan dikembalikan ke Step 2 (Follow Up).'
        ]));
    }
    

    /**
     * CLAIM BRAND FEATURE
     */
    private function _check_brand_ownership($brand_id) {
        $brand = $this->db->select('id, name, is_duplicate, duplicate_of, owner_id, status')->where('id', $brand_id)->get('brands')->row();
        if (!$brand) return;

        if ($brand->owner_id) return; // Already claimed

        // Gunakan duplicate_of sebagai indicator duplikat meskipun is_duplicate=0
        // (ini menangani kasus di mana is_duplicate tidak ter-set dengan benar saat insert)
        $is_dup = ($brand->is_duplicate || !empty($brand->duplicate_of));
        $original_id = ($is_dup && $brand->duplicate_of) ? $brand->duplicate_of : $brand->id;

        // Pastikan is_duplicate konsisten di database
        if (!$brand->is_duplicate && !empty($brand->duplicate_of)) {
            $this->db->where('id', $brand_id)->update('brands', ['is_duplicate' => 1]);
        }

        // Ambil semua unique bd_id yang pernah input brand ini (original + semua duplikat)
        $bds = $this->db->select('DISTINCT(bd_id) as bd_id')
                         ->group_start()
                             ->where('id', $original_id)
                             ->or_where('duplicate_of', $original_id)
                         ->group_end()
                         ->get('brands')->result();

        $unique_bds = [];
        foreach ($bds as $b) {
            if ($b->bd_id && !in_array($b->bd_id, $unique_bds)) {
                $unique_bds[] = $b->bd_id;
            }
        }

        if (count($unique_bds) == 1) {
            // Hanya 1 BD yang kontak → auto assign ownership ke semua entry brand ini
            $this->db->group_start()
                         ->where('id', $original_id)
                         ->or_where('duplicate_of', $original_id)
                     ->group_end()
                     ->update('brands', ['owner_id' => $unique_bds[0]]);
            return ['status' => 'AUTO_ASSIGNED', 'owner_id' => $unique_bds[0]];
        } else if (count($unique_bds) > 1) {
            // Lebih dari 1 BD kontak → semua entry yang CAMPAIGN_READY harus berubah ke NEED_CLAIM
            $this->db->group_start()
                         ->where('id', $original_id)
                         ->or_where('duplicate_of', $original_id)
                     ->group_end()
                     ->where('status', 'CAMPAIGN_READY')
                     ->update('brands', ['status' => 'NEED_CLAIM']);
            return ['status' => 'NEED_CLAIM', 'owner_id' => null];
        }

        return null;
    }

    public function claim_brand() {
        $this->output->set_content_type('application/json');
        
        $user_id = $this->session->userdata('user_id');
        $brand_id = $this->input->post('brand_id');
        
        if (!$brand_id) {
            return $this->output->set_output(json_encode(['success' => false, 'message' => 'Brand ID required']));
        }
        
        $brand = $this->db->select('id, is_duplicate, duplicate_of, owner_id, status')->where('id', $brand_id)->get('brands')->row();
        
        if (!$brand) {
            return $this->output->set_output(json_encode(['success' => false, 'message' => 'Brand not found']));
        }
        
        if ($brand->owner_id) {
            return $this->output->set_output(json_encode(['success' => false, 'message' => 'Brand sudah di-claim oleh BA lain.']));
        }
        
        // Find original ID
        $original_id = ($brand->is_duplicate && $brand->duplicate_of) ? $brand->duplicate_of : $brand->id;
        
        // Check if this BA is in the list of contacters
        $contacted = $this->db->where('bd_id', $user_id)
                              ->group_start()
                                  ->where('id', $original_id)
                                  ->or_where('duplicate_of', $original_id)
                              ->group_end()
                              ->count_all_results('brands');
                              
        if ($contacted == 0) {
            return $this->output->set_output(json_encode(['success' => false, 'message' => 'Anda tidak memiliki riwayat kontak dengan Brand ini sehingga tidak berhak melakukan claim.']));
        }
        
        // Lock Ownership
        $this->db->trans_start();
        
        $this->db->where('id', $original_id)->or_where('duplicate_of', $original_id)->update('brands', [
            'owner_id' => $user_id
        ]);
        
        // Set original brand back to CAMPAIGN_READY to resume Step 3
        if ($brand->status == 'NEED_CLAIM') {
            $this->db->where('id', $brand_id)->update('brands', [
                'status' => 'CAMPAIGN_READY',
                'current_task' => 3
            ]);
        }
        
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            return $this->output->set_output(json_encode(['success' => false, 'message' => 'Gagal melakukan claim brand.']));
        }
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'message' => 'Berhasil! Anda sekarang adalah Owner dari Brand ini.'
        ]));
    }

}



