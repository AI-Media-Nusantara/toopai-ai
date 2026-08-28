<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Is extends CI_Controller {

    public function __construct() {
        parent::__construct();
        
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
        
        if ($this->session->userdata('role') != 'IS') {
            show_error('Access denied. IS only area.', 403);
        }
        $this->load->helper('excel');
        $this->load->library('Jsm_api');
        $this->load->model(['Campaign_model', 'Brand_model', 'Product_model', 'User_model', 'Jsm_token_model', 'Creator_model', 'Task_progress_model']);
        $this->load->helper('number');
        $this->load->database();
    }

    public function index() {
        redirect('is/dashboard');
    }
    
// ========================================================================
// DASHBOARD UTAMA - IS (3 TASK) - TANPA KOMENTAR DI QUERY
// ========================================================================
public function dashboard() {
    $user_id = $this->session->userdata('user_id');
    $is_supervisor = ($user_id == 2);
    
    // ====================================================================
    // 🔥 TASK 1: SCOUTING - AUTO GENERATE LINK
    // ====================================================================
    $task1_creators = $this->db->select('
            c.*,
            b.name as brand_name,
            b.shop_name,
            u.username as is_username,
            u.full_name as is_full_name,
            (SELECT COUNT(DISTINCT acl.id) 
             FROM affiliate_creator_links acl 
             WHERE acl.creator_id = c.id 
               AND acl.status = "ACTIVE") as total_links,
            (SELECT MAX(acl.created_at) 
             FROM affiliate_creator_links acl 
             WHERE acl.creator_id = c.id 
               AND acl.status = "ACTIVE") as last_link_created,
            (SELECT COALESCE(SUM(o.gmv), 0) 
             FROM affiliate_orders o 
             WHERE o.creator_username = c.username 
               AND o.order_date_local >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
               AND o.order_status NOT IN ("CANCELLED", "REFUNDED")) as total_gmv_30d
        ')
        ->from('creators c')
        ->join('brands b', 'c.brand_id = b.id', 'left')
        ->join('users u', 'c.is_id = u.id', 'left')
        ->where_in('c.status', ['PENDING', 'LINK_SWAPPING'])
        ->order_by('(CASE WHEN c.is_id = ' . intval($user_id) . ' THEN 1 ELSE 0 END)', 'DESC')
        ->order_by('(CASE WHEN c.phone IS NOT NULL AND c.phone != "" AND c.phone != "no_phone" THEN 1 ELSE 0 END)', 'DESC')
        ->order_by('c.imported_gmv', 'DESC')
        ->limit(100)
        ->get()
        ->result();
    
    // ====================================================================
    // 🔥 TASK 2: WAITING HANDLER - DEAL READY (LAMPU HIJAU)
    // ====================================================================
 $task2_sql = "
        -- PART 1: Creator yang sudah ada di tabel creators tapi is_id NULL
        SELECT 
            c.id,
            c.username,
            c.full_name,
            c.avatar_url,
            c.category,
            c.phone,
            c.alamat,
            c.penerima,
            c.created_at,
            c.imported_gmv,
            c.is_id as handler_id,
            u.full_name as handler_name,
            b.name as brand_name,
            b.shop_name,
            (SELECT COUNT(DISTINCT acl.id) 
             FROM affiliate_creator_links acl 
             WHERE (acl.creator_id = c.id OR LOWER(TRIM(acl.creator_username)) = LOWER(TRIM(c.username)))
               AND acl.status = 'ACTIVE') as total_active_links,
            (SELECT COALESCE(SUM(o.gmv), 0) 
             FROM affiliate_orders o 
             WHERE LOWER(TRIM(o.creator_username)) = LOWER(TRIM(c.username))
               AND o.order_date_local >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
               AND o.order_status NOT IN ('CANCELLED', 'REFUNDED')) as total_gmv_30d,
            (SELECT product_name 
             FROM affiliate_orders o2 
             WHERE LOWER(TRIM(o2.creator_username)) = LOWER(TRIM(c.username))
               AND o2.order_status NOT IN ('CANCELLED', 'REFUNDED')
             GROUP BY o2.product_name 
             ORDER BY SUM(o2.gmv) DESC 
             LIMIT 1) as top_product,
            (SELECT MAX(ap.image_url) 
             FROM affiliate_products ap 
             JOIN affiliate_orders o3 ON ap.product_id = o3.product_id AND ap.campaign_id = o3.campaign_id
             WHERE LOWER(TRIM(o3.creator_username)) = LOWER(TRIM(c.username))
               AND o3.order_status NOT IN ('CANCELLED', 'REFUNDED')
             GROUP BY ap.product_id 
             ORDER BY SUM(o3.gmv) DESC 
             LIMIT 1) as top_product_image,
            CASE 
                WHEN c.is_id IS NOT NULL AND c.is_id != {$user_id} THEN 'claimed'
                WHEN (c.is_id IS NULL OR c.is_id = {$user_id}) AND EXISTS (
                    SELECT 1 FROM affiliate_creator_links acl2
                    WHERE (acl2.creator_id = c.id OR LOWER(TRIM(acl2.creator_username)) = LOWER(TRIM(c.username)))
                      AND acl2.status = 'ACTIVE'
                      AND (acl2.total_clicks > 0 OR acl2.total_orders > 0 OR acl2.showcase_status = 'added')
                ) THEN 'ready'
                WHEN c.is_id IS NULL OR c.is_id = {$user_id} THEN 'no_handler'
                ELSE 'no_link'
            END AS deal_status,
            'registered' as source_type
        FROM creators c
        LEFT JOIN brands b ON c.brand_id = b.id
        LEFT JOIN users u ON c.is_id = u.id
        WHERE c.status = 'LINK_SENT'
        
        UNION ALL
        
        -- PART 2: Creator yang TIDAK ADA di tabel creators (unregistered)
        SELECT 
            NULL as id,
            o.creator_username as username,
            o.creator_username as full_name,
            NULL as avatar_url,
            NULL as category,
            NULL as phone,
            NULL as alamat,
            NULL as penerima,
            NULL as created_at,
            0 as imported_gmv,
            NULL as handler_id,
            NULL as handler_name,
            NULL as brand_name,
            NULL as shop_name,
            0 as total_active_links,
            SUM(o.gmv) as total_gmv_30d,
            MAX(o.product_name) as top_product,
            NULL as top_product_image,
            'no_handler' as deal_status,
            'unregistered' as source_type
        FROM affiliate_orders o
        WHERE o.order_date_local >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
          AND o.order_status NOT IN ('CANCELLED', 'REFUNDED')
          AND o.creator_username IS NOT NULL 
          AND o.creator_username != ''
          AND o.creator_username NOT IN (
              SELECT DISTINCT username 
              FROM creators 
              WHERE username IS NOT NULL AND username != ''
          )
        GROUP BY o.creator_username
        HAVING SUM(o.gmv) > 0
        
    ORDER BY 
        CASE WHEN source_type = 'unregistered' THEN 0 ELSE 1 END,
        total_gmv_30d DESC
    LIMIT 100
";

$task2_creators = $this->db->query($task2_sql)->result();
    
    // ====================================================================
    // 🔥 TASK 3: MONITORING - CREATOR AKTIF
    // ====================================================================
    $task3_creators = $this->db->select('
            c.id,
            c.username,
            c.full_name,
            c.avatar_url,
            c.category,
            c.phone,
            c.alamat,
            c.penerima,
            c.created_at,
            c.approved_at,
            c.imported_gmv,
            c.is_id as handler_id,
            u.full_name as handler_name,
            b.name as brand_name,
            b.shop_name,
            (SELECT COUNT(DISTINCT acl.id) 
             FROM affiliate_creator_links acl 
             WHERE acl.creator_id = c.id 
               AND acl.status = "ACTIVE") as total_links,
            (SELECT COALESCE(SUM(o.gmv), 0) 
             FROM affiliate_orders o 
             WHERE o.creator_username = c.username 
               AND o.order_date_local >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
               AND o.order_status NOT IN ("CANCELLED", "REFUNDED")) as total_gmv_30d,
            (SELECT COUNT(DISTINCT o.order_id) 
             FROM affiliate_orders o 
             WHERE o.creator_username = c.username 
               AND o.order_date_local >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
               AND o.order_status NOT IN ("CANCELLED", "REFUNDED")) as total_orders_30d,
            (SELECT COALESCE(SUM(o.estimated_commission), 0) 
             FROM affiliate_orders o 
             WHERE o.creator_username = c.username 
               AND o.order_date_local >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
               AND o.order_status NOT IN ("CANCELLED", "REFUNDED")) as total_commission_30d,
            (SELECT product_name 
             FROM affiliate_orders o2 
             WHERE o2.creator_username = c.username 
               AND o2.order_status NOT IN ("CANCELLED", "REFUNDED")
             GROUP BY o2.product_name 
             ORDER BY SUM(o2.gmv) DESC 
             LIMIT 1) as top_product,
            (SELECT MAX(ap.image_url) 
             FROM affiliate_products ap 
             JOIN affiliate_orders o3 ON ap.product_id = o3.product_id AND ap.campaign_id = o3.campaign_id
             WHERE o3.creator_username = c.username 
               AND o3.order_status NOT IN ("CANCELLED", "REFUNDED")
             GROUP BY ap.product_id 
             ORDER BY SUM(o3.gmv) DESC 
             LIMIT 1) as top_product_image
        ')
        ->from('creators c')
        ->join('brands b', 'c.brand_id = b.id', 'left')
        ->join('users u', 'c.is_id = u.id', 'left')
        ->where_in('c.status', ['ACTIVE', 'SAMPLE_SENT'])
        ->order_by('total_gmv_30d', 'DESC')
        ->limit(100)
        ->get()
        ->result();
    
    // ====================================================================
    // 🔥 STATISTIK DASHBOARD
    // ====================================================================
    
    $task1_count = $this->db->where_in('status', ['PENDING', 'LINK_SWAPPING'])->count_all_results('creators');
    
   $task2_count = 0;
foreach ($task2_creators as $c) {
    if ($c->deal_status == 'ready' || $c->deal_status == 'no_handler') {
        $task2_count++;
    }
}

    
    $task3_count = $this->db->where_in('status', ['ACTIVE', 'SAMPLE_SENT'])->count_all_results('creators');
    
    $today = date('Y-m-d');
    $today_gmv = $this->db->select('COALESCE(SUM(gmv), 0) as total')
        ->from('affiliate_orders')
        ->where('order_date_local', $today)
        ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
        ->get()
        ->row()
        ->total ?? 0;
    
    $today_orders = $this->db->where('order_date_local', $today)
        ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
        ->count_all_results('affiliate_orders');
    
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $yesterday_gmv = $this->db->select('COALESCE(SUM(gmv), 0) as total')
        ->from('affiliate_orders')
        ->where('order_date_local', $yesterday)
        ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
        ->get()
        ->row()
        ->total ?? 0;
    
    $gmv_growth = $yesterday_gmv > 0 ? (($today_gmv - $yesterday_gmv) / $yesterday_gmv * 100) : ($today_gmv > 0 ? 100 : 0);
    
    $data = [
        'title' => 'IS Dashboard - Toopai',
        'task1_creators' => $task1_creators,
        'task2_creators' => $task2_creators,
        'task3_creators' => $task3_creators,
        'task1_count' => $task1_count,
        'task2_count' => $task2_count,
        'task3_count' => $task3_count,
        'today_gmv' => $today_gmv,
        'today_orders' => $today_orders,
        'gmv_growth' => round($gmv_growth, 1),
        'total_creators' => $this->db->count_all_results('creators'),
        'is_supervisor' => $is_supervisor,
    ];
    
    $this->load->view('templates/new/header', $data);
    $this->load->view('is/dashboard', $data);
    $this->load->view('templates/new/footer');
}

// ========================================================================
// GET TASK 2 CREATORS (AJAX) - TANPA KOMENTAR
// ========================================================================
public function get_task2_creators() {
    $this->output->set_content_type('application/json');
    
    $creators = $this->db->select('
            c.id,
            c.username,
            c.full_name,
            c.avatar_url,
            c.category,
            c.phone,
            c.alamat,
            c.penerima,
            c.created_at,
            c.imported_gmv,
            c.is_id as handler_id,
            u.full_name as handler_name,
            b.name as brand_name,
            b.shop_name,
            (SELECT COUNT(DISTINCT acl.id) 
             FROM affiliate_creator_links acl 
             WHERE acl.creator_id = c.id 
               AND acl.status = "ACTIVE") as total_active_links,
            (SELECT COALESCE(SUM(o.gmv), 0) 
             FROM affiliate_orders o 
             WHERE o.creator_username = c.username 
               AND o.order_date_local >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
               AND o.order_status NOT IN ("CANCELLED", "REFUNDED")) as total_gmv_30d,
            (SELECT product_name 
             FROM affiliate_orders o2 
             WHERE o2.creator_username = c.username 
               AND o2.order_status NOT IN ("CANCELLED", "REFUNDED")
             GROUP BY o2.product_name 
             ORDER BY SUM(o2.gmv) DESC 
             LIMIT 1) as top_product,
            (SELECT MAX(ap.image_url) 
             FROM affiliate_products ap 
             JOIN affiliate_orders o3 ON ap.product_id = o3.product_id AND ap.campaign_id = o3.campaign_id
             WHERE o3.creator_username = c.username 
               AND o3.order_status NOT IN ("CANCELLED", "REFUNDED")
             GROUP BY ap.product_id 
             ORDER BY SUM(o3.gmv) DESC 
             LIMIT 1) as top_product_image,
            CASE 
                WHEN EXISTS (
                    SELECT 1 FROM affiliate_creator_links acl2
                    WHERE acl2.creator_id = c.id
                      AND acl2.status = "ACTIVE"
                      AND (acl2.created_by_user_id IS NULL OR acl2.created_by_user_id = 0)
                ) THEN "ready"
                WHEN EXISTS (
                    SELECT 1 FROM affiliate_creator_links acl2
                    WHERE acl2.creator_id = c.id
                      AND acl2.created_by_user_id IS NOT NULL
                      AND acl2.created_by_user_id > 0
                ) THEN "claimed"
                ELSE "no_link"
            END AS deal_status
        ')
        ->from('creators c')
        ->join('brands b', 'c.brand_id = b.id', 'left')
        ->join('users u', 'c.is_id = u.id', 'left')
        ->where('c.status', 'LINK_SWAPPING')
        ->having('deal_status', 'ready')
        ->order_by('total_gmv_30d', 'DESC')
        ->limit(100)
        ->get()
        ->result();
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'creators' => $creators,
        'total' => count($creators)
    ]));
}

// ========================================================================
// GET TASK 3 CREATORS (AJAX) - TANPA KOMENTAR
// ========================================================================
public function get_task3_creators() {
    $this->output->set_content_type('application/json');
    
    $creators = $this->db->select('
            c.id,
            c.username,
            c.full_name,
            c.avatar_url,
            c.category,
            c.phone,
            c.alamat,
            c.penerima,
            c.created_at,
            c.approved_at,
            c.imported_gmv,
            c.is_id as handler_id,
            u.full_name as handler_name,
            b.name as brand_name,
            b.shop_name,
            (SELECT COUNT(DISTINCT acl.id) 
             FROM affiliate_creator_links acl 
             WHERE acl.creator_id = c.id 
               AND acl.status = "ACTIVE") as total_links,
            (SELECT COALESCE(SUM(o.gmv), 0) 
             FROM affiliate_orders o 
             WHERE o.creator_username = c.username 
               AND o.order_date_local >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
               AND o.order_status NOT IN ("CANCELLED", "REFUNDED")) as total_gmv_30d,
            (SELECT COUNT(DISTINCT o.order_id) 
             FROM affiliate_orders o 
             WHERE o.creator_username = c.username 
               AND o.order_date_local >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
               AND o.order_status NOT IN ("CANCELLED", "REFUNDED")) as total_orders_30d,
            (SELECT COALESCE(SUM(o.estimated_commission), 0) 
             FROM affiliate_orders o 
             WHERE o.creator_username = c.username 
               AND o.order_date_local >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
               AND o.order_status NOT IN ("CANCELLED", "REFUNDED")) as total_commission_30d,
            (SELECT product_name 
             FROM affiliate_orders o2 
             WHERE o2.creator_username = c.username 
               AND o2.order_status NOT IN ("CANCELLED", "REFUNDED")
             GROUP BY o2.product_name 
             ORDER BY SUM(o2.gmv) DESC 
             LIMIT 1) as top_product,
            (SELECT MAX(ap.image_url) 
             FROM affiliate_products ap 
             JOIN affiliate_orders o3 ON ap.product_id = o3.product_id AND ap.campaign_id = o3.campaign_id
             WHERE o3.creator_username = c.username 
               AND o3.order_status NOT IN ("CANCELLED", "REFUNDED")
             GROUP BY ap.product_id 
             ORDER BY SUM(o3.gmv) DESC 
             LIMIT 1) as top_product_image
        ')
        ->from('creators c')
        ->join('brands b', 'c.brand_id = b.id', 'left')
        ->join('users u', 'c.is_id = u.id', 'left')
        ->where_in('c.status', ['ACTIVE', 'SAMPLE_SENT'])
        ->order_by('total_gmv_30d', 'DESC')
        ->limit(100)
        ->get()
        ->result();
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'creators' => $creators,
        'total' => count($creators)
    ]));
}

// ========================================================================
// CLAIM DEAL
// ========================================================================
public function claim_deal() {
    $this->output->set_content_type('application/json');

    // 🔒 Validasi session
    if (!$this->session->userdata('logged_in')) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Session expired. Silakan login ulang.'
        ]));
    }

    $creator_id       = $this->input->post('creator_id');
    $creator_username = $this->input->post('creator_username');
    $user_id          = $this->session->userdata('user_id');
    $full_name        = $this->session->userdata('full_name');
    $username         = $this->session->userdata('username');

    // 🔥 Jika creator_id kosong dan ada username, coba auto-register
    if (empty($creator_id) && !empty($creator_username)) {
        $existing = $this->db->where('username', $creator_username)->get('creators')->row();

        if (!$existing) {
            // 🚫 Blokir auto-register untuk creator tanpa link aktif maupun order
            // (creator "no_handler" murni yang tidak pernah diberi link oleh CA)
            $has_order = $this->db
                ->where('creator_username', $creator_username)
                ->where('order_status NOT IN (\'CANCELLED\', \'REFUNDED\')')
                ->count_all_results('affiliate_orders') > 0;

            if (!$has_order) {
                return $this->output->set_output(json_encode([
                    'success' => false,
                    'message' => 'Creator belum menggunakan link dari CA. DEAL hanya bisa dilakukan setelah creator menggunakan link yang diberikan.'
                ]));
            }

            // Auto-register creator yang sudah ada order
            $insert_data = [
                'username'   => $creator_username,
                'full_name'  => $creator_username,
                'is_id'      => $user_id,
                'status'     => 'ACTIVE',
                'source'     => 'auto_register',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->db->insert('creators', $insert_data);
            $new_id = $this->db->insert_id();

            $this->load->model('User_log_model');
            $this->User_log_model->log(
                $user_id, $username, 'IS',
                'AUTO_REGISTER_CLAIM',
                "Auto-register and claim creator @{$creator_username} (ID: {$new_id})"
            );

            return $this->output->set_output(json_encode([
                'success'          => true,
                'message'          => "✅ Berhasil register dan claim @{$creator_username}! Creator pindah ke Monitoring (Task 3).",
                'creator_id'       => $new_id,
                'creator_username' => $creator_username,
                'claimed_by'       => $full_name ?: $username,
                'auto_registered'  => true
            ]));
        }

        $creator_id = $existing->id;
    }

    if (empty($creator_id)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Creator ID atau username required'
        ]));
    }

    // 🔥 CEK APAKAH CREATOR VALID DAN BELUM DI-CLAIM
    $check = $this->db->select('c.id, c.username, c.is_id')
        ->from('creators c')
        ->where('c.id', $creator_id)
        ->where('c.is_id IS NULL')
        ->get()
        ->row();

    if (!$check) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Creator sudah di-claim oleh IS lain atau tidak valid'
        ]));
    }

    // 🔥 VALIDASI: Creator harus sudah menggunakan link dari CA
    // (ditandai dengan affiliate_creator_links AKTIF atau ada order di affiliate_orders)
    $has_active_link = $this->db
        ->where('creator_id', $creator_id)
        ->where('status', 'ACTIVE')
        ->count_all_results('affiliate_creator_links') > 0;

    $has_order = false;
    if (!$has_active_link) {
        $has_order = $this->db
            ->where('LOWER(TRIM(creator_username))', 'LOWER(TRIM(\'' . $check->username . '\'))', false)
            ->where('order_status NOT IN (\'CANCELLED\', \'REFUNDED\')')
            ->count_all_results('affiliate_orders') > 0;
    }

    if (!$has_active_link && !$has_order) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => '⚠️ DEAL tidak bisa dilakukan. Creator @' . $check->username . ' belum menggunakan link dari tim CA (belum ada link aktif atau order yang masuk).'
        ]));
    }

    // 🔥 LOCK TABLE untuk mencegah race condition
    $this->db->trans_start();

    $this->db->where('id', $creator_id)
        ->where('is_id IS NULL')
        ->update('creators', [
            'is_id'       => $user_id,
            'status'      => 'ACTIVE',
            'approved_at' => date('Y-m-d H:i:s'),
            'approved_by' => $user_id,
            'updated_at'  => date('Y-m-d H:i:s')
        ]);

    $affected = $this->db->affected_rows();

    if ($affected == 0) {
        $this->db->trans_rollback();
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Maaf, creator sudah di-claim oleh IS lain! Silakan refresh halaman.'
        ]));
    }

    $this->db->trans_complete();

    if ($this->db->trans_status() === FALSE) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Terjadi kesalahan sistem saat melakukan DEAL.'
        ]));
    }

    // Log aktivitas
    $this->load->model('User_log_model');
    $this->User_log_model->log(
        $user_id, $username, 'IS',
        'CLAIM_DEAL',
        "Claimed creator @{$check->username} (ID: {$creator_id})"
    );

    return $this->output->set_output(json_encode([
        'success'          => true,
        'message'          => "✅ Berhasil claim @{$check->username}! Creator pindah ke Monitoring (Task 3).",
        'creator_id'       => $creator_id,
        'creator_username' => $check->username,
        'claimed_by'       => $full_name ?: $username
    ]));
}
public function add_creator_task3() {
    $this->output->set_content_type('application/json');
    
    if (!$this->session->userdata('logged_in')) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Session expired'
        ]));
    }
    
    $user_id      = $this->session->userdata('user_id');
    $username     = strtolower(trim(ltrim($this->input->post('username'), '@')));
    $full_name    = $this->input->post('full_name');
    $category     = $this->input->post('category');
    $phone        = $this->input->post('phone');
    $email        = $this->input->post('email');
    $brand_id     = $this->input->post('brand_id');
    $shop_name    = $this->input->post('shop_name');
    $avatar_url   = $this->input->post('avatar_url');
    $follower_count = $this->input->post('follower_count');
    $force_save   = $this->input->post('force_save') === '1'; // bypass phone duplicate

    if (empty($username)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Username TikTok wajib diisi'
        ]));
    }

    // Cek duplikat username pada brand yang sama
    $existing = $this->db->select('c.*, u.full_name AS owner_name, b.name AS brand_name_label')
                         ->from('creators c')
                         ->join('brands b', 'c.brand_id = b.id', 'left')
                         ->join('users u', 'c.is_id = u.id', 'left')
                         ->where('LOWER(c.username)', $username)
                         ->where('c.brand_id', $brand_id)
                         ->get()
                         ->row();

    if ($existing) {
        if (!empty($existing->is_id)) {
            $owner_name = $existing->owner_name ?: 'CA lain';
            $brand_label = $existing->brand_name_label ?: 'brand ini';
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => "Creator @{$username} untuk brand {$brand_label} sudah dikelola oleh {$owner_name}."
            ]));
        } else {
            // Ada record tapi belum ada ownership (is_id NULL). Kita bisa update is_id
            $update_data = [
                'is_id'      => $user_id,
                'status'     => 'ACTIVE',
                'updated_at' => date('Y-m-d H:i:s'),
                'approved_at' => date('Y-m-d H:i:s'),
                'approved_by' => $user_id
            ];
            if (!empty($full_name)) $update_data['full_name'] = $full_name;
            if (!empty($category)) $update_data['category'] = $category;
            if (!empty($phone)) $update_data['phone'] = $phone;
            if (!empty($email)) $update_data['email'] = $email;
            if (!empty($avatar_url)) $update_data['avatar_url'] = $avatar_url;
            if (!empty($follower_count)) $update_data['imported_followers'] = $follower_count;

            $this->db->where('id', $existing->id)->update('creators', $update_data);

            // Log aktivitas
            $this->load->model('User_log_model');
            $this->User_log_model->log(
                $user_id,
                $this->session->userdata('username'),
                'IS',
                'CLAIM_CREATOR_TASK3',
                "Claimed ownership of creator @{$username} for brand ID {$brand_id}"
            );

            return $this->output->set_output(json_encode([
                'success'    => true,
                'message'    => '✅ @' . $username . ' berhasil ditambahkan ke Task 3 (Monitoring)!',
                'creator_id' => $existing->id,
                'username'   => $username
            ]));
        }
    }

    // Cek duplikat nomor HP (kecuali force_save)
    if (!$force_save && !empty($phone)) {
        $normalized_input = preg_replace('/[^0-9]/', '', $phone);
        $input_tail = substr($normalized_input, -9);

        if (strlen($input_tail) === 9) {
            $all_creators = $this->db->select('id, username, full_name, phone, status')
                ->where('phone IS NOT NULL')
                ->where('phone !=', '')
                ->get('creators')
                ->result();

            $phone_matches = [];
            foreach ($all_creators as $c) {
                $db_tail = substr(preg_replace('/[^0-9]/', '', $c->phone), -9);
                if ($db_tail === $input_tail) {
                    $phone_matches[] = [
                        'id'        => $c->id,
                        'username'  => $c->username,
                        'full_name' => $c->full_name,
                        'phone'     => $c->phone,
                        'status'    => $c->status,
                    ];
                }
            }

            if (!empty($phone_matches)) {
                return $this->output->set_output(json_encode([
                    'success'       => false,
                    'phone_duplicate' => true,
                    'message'       => 'Nomor HP ini sudah terdaftar untuk creator lain.',
                    'matches'       => $phone_matches
                ]));
            }
        }
    }

    // INSERT LANGSUNG KE TASK 3 (STATUS ACTIVE)
    $insert_data = [
        'username'      => $username,
        'full_name'     => $full_name ?: $username,
        'category'      => $category ?: 'Lifestyle',
        'phone'         => $phone,
        'email'         => $email,
        'is_id'         => $user_id,
        'brand_id'      => $brand_id,
        'shop_name'     => $shop_name,
        'source'        => 'manual_task3',
        'status'        => 'ACTIVE',
        'avatar_url'    => $avatar_url,
        'imported_followers' => $follower_count,
        'approved_at'   => date('Y-m-d H:i:s'),
        'approved_by'   => $user_id,
        'created_at'    => date('Y-m-d H:i:s'),
        'updated_at'    => date('Y-m-d H:i:s')
    ];

    if ($this->db->insert('creators', $insert_data)) {
        $new_id = $this->db->insert_id();

        $this->load->model('User_log_model');
        $this->User_log_model->log(
            $user_id,
            $this->session->userdata('username'),
            'IS',
            'ADD_CREATOR_TASK3',
            "Added creator @{$username} directly to Task 3 (Monitoring)" . ($force_save ? ' [force - phone duplicate bypassed]' : '')
        );

        return $this->output->set_output(json_encode([
            'success'    => true,
            'message'    => '✅ @' . $username . ' berhasil ditambahkan ke Task 3 (Monitoring)!',
            'creator_id' => $new_id,
            'username'   => $username
        ]));
    }

    return $this->output->set_output(json_encode([
        'success' => false,
        'message' => 'Gagal menambahkan creator'
    ]));
}

public function get_brands_for_select() {
    $this->output->set_content_type('application/json');
    
    $brands = $this->db->select('id, name, shop_name')
        ->where('status', 'ACTIVE')
        ->order_by('name', 'ASC')
        ->get('brands')
        ->result();
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'brands' => $brands
    ]));
}

// ========================================================================
// GET CREATOR DETAIL FOR MODAL - PERBAIKAN
// ========================================================================
public function get_creator_detail_for_is() {
    $this->output->set_content_type('application/json');
    
    $creator_id = $this->input->post('creator_id');
    
    if (!$creator_id) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Creator ID required'
        ]));
    }
    
    $creator = $this->db->select('
            c.*,
            b.name as brand_name,
            b.shop_name,
            u.full_name as is_name,
            u.username as is_username
        ')
        ->from('creators c')
        ->join('brands b', 'c.brand_id = b.id', 'left')
        ->join('users u', 'c.is_id = u.id', 'left')
        ->where('c.id', $creator_id)
        ->get()
        ->row();
    
    if (!$creator) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Creator not found'
        ]));
    }
    
    $links = $this->db->select('
            acl.*,
            cp.campaign_name
        ')
        ->from('affiliate_creator_links acl')
        ->join('affiliate_campaigns cp', 'acl.campaign_id = cp.campaign_id', 'left')
        ->where('acl.creator_id', $creator_id)
        ->where('acl.status', 'ACTIVE')
        ->order_by('acl.created_at', 'DESC')
        ->get()
        ->result();
    
    $performance = $this->db->select('
            COALESCE(SUM(gmv), 0) as total_gmv,
            COUNT(DISTINCT order_id) as total_orders,
            COALESCE(SUM(estimated_commission), 0) as total_commission,
            MIN(order_date_local) as first_order_date,
            MAX(order_date_local) as last_order_date
        ')
        ->from('affiliate_orders')
        ->where('creator_username', $creator->username)
        ->where('order_date_local >=', date('Y-m-d', strtotime('-30 days')))
        ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
        ->get()
        ->row();
    
    $top_products = $this->db->select('
            product_name,
            product_id,
            SUM(gmv) as total_gmv,
            COUNT(DISTINCT order_id) as total_orders
        ')
        ->from('affiliate_orders')
        ->where('creator_username', $creator->username)
        ->where('order_date_local >=', date('Y-m-d', strtotime('-30 days')))
        ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
        ->group_by('product_id, product_name')
        ->order_by('total_gmv', 'DESC')
        ->limit(5)
        ->get()
        ->result();
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'creator' => $creator,
        'links' => $links,
        'performance' => $performance,
        'top_products' => $top_products
    ]));
}
   
   
   /**
 * Get creator detail for Task 1 (SCOUTING) dengan semua info
 */
public function get_creator_task1_detail() {
    // Set header JSON
    $this->output->set_content_type('application/json');
    
    try {
        // Ambil creator_id dari POST
        $creator_id = $this->input->post('creator_id');
        
        // Debug log
        log_message('debug', '=== get_creator_task1_detail START ===');
        log_message('debug', 'creator_id: ' . $creator_id);
        
        if (empty($creator_id)) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Creator ID required'
            ]));
        }
        
        // ============================================================
        // 1. AMBIL DATA CREATOR
        // ============================================================
        $creator = $this->db->select('
            c.id,
            c.username,
            c.full_name,
            c.avatar_url,
            c.category,
            c.phone,
            c.email,
            c.alamat,
            c.penerima,
            c.status,
            c.imported_gmv,
            c.created_at,
            c.updated_at,
            c.source,
            c.brand_id,
            c.follower_count,
            c.total_gmv,
            c.total_orders,
            c.shop_name,
            c.tiktok_open_id
        ')
        ->from('creators c')
        ->where('c.id', $creator_id)
        ->get()
        ->row();
        
        if (!$creator) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Creator not found'
            ]));
        }
        
        log_message('debug', 'Creator found: ' . $creator->username);

        // Auto-resolve tiktok_open_id and sync FastMoss products if empty
        if (empty($creator->tiktok_open_id) && !empty($creator->username)) {
            try {
                $this->load->model('BrandCreator_model');
                $fastmoss_uid = $this->BrandCreator_model->find_creator_in_fastmoss($creator->username);
                if ($fastmoss_uid) {
                    $this->db->where('id', $creator->id)->update('creators', [
                        'tiktok_open_id' => $fastmoss_uid,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    $creator->tiktok_open_id = $fastmoss_uid;
                }
            } catch (Exception $e) {
                log_message('error', 'Auto-resolve tiktok_open_id failed: ' . $e->getMessage());
            }
        }

        if (!empty($creator->tiktok_open_id)) {
            try {
                $has_products = $this->db->where('creator_id', $creator_id)->count_all_results('creator_products');
                if ($has_products < 5) {
                    $this->load->model('BrandCreator_model');
                    $this->BrandCreator_model->sync_creator_products_to_db($creator->id, $creator->username, $creator->tiktok_open_id);
                }
            } catch (Exception $e) {
                log_message('error', 'Auto-sync FastMoss products failed: ' . $e->getMessage());
            }
        }
        
        // ============================================================
        // 2. AMBIL BRAND NAME
        // ============================================================
        $brand_name = '';
        $brand_shop_name = '';
        if (!empty($creator->brand_id)) {
            $brand = $this->db->select('name, shop_name')
                ->where('id', $creator->brand_id)
                ->get('brands')
                ->row();
            if ($brand) {
                $brand_name = $brand->name;
                $brand_shop_name = $brand->shop_name;
            }
        }
        $creator->brand_name = $brand_name;
        $creator->brand_shop_name = $brand_shop_name;
        
        // ============================================================
        // 3. AMBIL USER IS (CREATOR ASSIGNED TO)
        // ============================================================
        $is_name = '';
        $is_username = '';
        if (!empty($creator->is_id)) {
            $user = $this->db->select('full_name, username')
                ->where('id', $creator->is_id)
                ->get('users')
                ->row();
            if ($user) {
                $is_name = $user->full_name;
                $is_username = $user->username;
            }
        }
        $creator->is_name = $is_name;
        $creator->is_username = $is_username;
        
        // ============================================================
        // 4. AMBIL PRODUCTS DARI affiliate_creator_links
        // ============================================================
        $products = [];
        $total_gmv = floatval($creator->imported_gmv ?? 0);
        
        try {
            // Cek apakah ada link afiliasi — fallback ke creator_username jika creator_id NULL
            $has_links = $this->db
                ->group_start()
                    ->where('creator_id', $creator_id)
                    ->or_where('creator_username', $creator->username)
                ->group_end()
                ->where('status', 'ACTIVE')
                ->count_all_results('affiliate_creator_links');
            
            log_message('debug', 'Has affiliate_creator_links: ' . $has_links);
            
            if ($has_links > 0) {
                $products_query = $this->db->select('
                    acl.product_id,
                    acl.product_name,
                    acl.commission_rate,
                    acl.affiliate_link,
                    acl.total_gmv as product_gmv,
                    acl.total_orders as product_orders,
                    ap.price,
                    ap.image_url,
                    ap.shop_name,
                    ap.category,
                    ap.sales_count
                ')
                ->from('affiliate_creator_links acl')
                ->join('affiliate_products ap', 'acl.product_id = ap.product_id AND acl.campaign_id = ap.campaign_id', 'left')
                ->group_start()
                    ->where('acl.creator_id', $creator_id)
                    ->or_where('acl.creator_username', $creator->username)
                ->group_end()
                ->where('acl.status', 'ACTIVE')
                ->order_by('acl.total_gmv', 'DESC')
                ->limit(20)
                ->get();
                
                $products = $products_query->result();
                log_message('debug', 'Products found: ' . count($products));
                
                // Hitung total GMV dari products
                if (!empty($products)) {
                    $total_gmv = array_sum(array_column($products, 'product_gmv'));
                }
            }
        } catch (Exception $e) {
            log_message('error', 'Error getting products: ' . $e->getMessage());
        }
        
        // ============================================================
        // 5. BRANDS (Kombinasi dari orders, affiliate links, dan brand creator)
        // ============================================================
        $brands_map = [];

        // A. Ambil brand dari affiliate_orders (berdasarkan sales/orders history)
        if (!empty($creator->username)) {
            try {
                $this->db->select('
                    o.shop_name,
                    COUNT(DISTINCT o.product_id) as total_products,
                    SUM(o.gmv) as total_gmv
                ')
                ->from('affiliate_orders o')
                ->where('o.creator_username', $creator->username)
                ->where_not_in('o.order_status', ['CANCELLED', 'REFUNDED'])
                ->where('o.shop_name IS NOT NULL', NULL, FALSE)
                ->where('o.shop_name !=', '')
                ->group_by('o.shop_name');

                $q = $this->db->get();
                if ($q) {
                    $orders_brands = $q->result();
                    foreach ($orders_brands as $ob) {
                        $s_name = trim($ob->shop_name);
                        $key = strtolower($s_name);
                        if (!empty($s_name)) {
                            $brands_map[$key] = [
                                'brand_id' => null,
                                'brand_name' => $s_name,
                                'shop_name' => $s_name,
                                'category' => '',
                                'total_products' => intval($ob->total_products),
                                'total_gmv' => floatval($ob->total_gmv)
                            ];
                        }
                    }
                } else {
                    log_message('error', 'Orders brands query failed: ' . json_encode($this->db->error()));
                }
            } catch (Exception $e) {
                log_message('error', 'Error getting brands from orders: ' . $e->getMessage());
            }
        }

        // B. Ambil brand dari affiliate_creator_links (baik yang ACTIVE maupun status lainnya)
        // Gunakan creator_username sebagai fallback jika creator_id NULL di tabel (data lama/migrasi)
        try {
            $this->db->select('
                ap.shop_name,
                COUNT(DISTINCT acl.product_id) as total_products,
                SUM(acl.total_gmv) as total_gmv
            ')
            ->from('affiliate_creator_links acl')
            ->join('affiliate_products ap', 'acl.product_id = ap.product_id AND acl.campaign_id = ap.campaign_id', 'inner')
            ->group_start()
                ->where('acl.creator_id', $creator_id)
                ->or_where('acl.creator_username', $creator->username)
            ->group_end()
            ->where('ap.shop_name IS NOT NULL', NULL, FALSE)
            ->where('ap.shop_name !=', '')
            ->group_by('ap.shop_name');

            $q = $this->db->get();
            if ($q) {
                $links_brands = $q->result();
                foreach ($links_brands as $lb) {
                    $s_name = trim($lb->shop_name);
                    $key = strtolower($s_name);
                    if (!empty($s_name)) {
                        if (isset($brands_map[$key])) {
                            $brands_map[$key]['total_gmv'] = max($brands_map[$key]['total_gmv'], floatval($lb->total_gmv));
                            $brands_map[$key]['total_products'] = max($brands_map[$key]['total_products'], intval($lb->total_products));
                        } else {
                            $brands_map[$key] = [
                                'brand_id' => null,
                                'brand_name' => $s_name,
                                'shop_name' => $s_name,
                                'category' => '',
                                'total_products' => intval($lb->total_products),
                                'total_gmv' => floatval($lb->total_gmv)
                            ];
                        }
                    }
                }
            } else {
                log_message('error', 'Links brands query failed: ' . json_encode($this->db->error()));
            }
        } catch (Exception $e) {
            log_message('error', 'Error getting brands from links: ' . $e->getMessage());
        }

        // C. Ambil brand dari creator_products (data FastMoss/Tiktok Shop yang disinkronkan)
        if ($this->db->table_exists('creator_products')) {
            try {
                $this->db->select('
                    cp.shop_name,
                    COUNT(DISTINCT cp.product_id) as total_products,
                    SUM(cp.gmv) as total_gmv
                ')
                ->from('creator_products cp')
                ->where('cp.creator_id', $creator_id)
                ->where('cp.shop_name IS NOT NULL', NULL, FALSE)
                ->where('cp.shop_name !=', '')
                ->where('cp.gmv >', 0)
                ->group_by('cp.shop_name');

                $q = $this->db->get();
                if ($q) {
                    $fm_brands = $q->result();
                    foreach ($fm_brands as $fb) {
                        $s_name = trim($fb->shop_name);
                        $key = strtolower($s_name);
                        if (!empty($s_name)) {
                            if (isset($brands_map[$key])) {
                                $brands_map[$key]['total_gmv'] = max($brands_map[$key]['total_gmv'], floatval($fb->total_gmv));
                                $brands_map[$key]['total_products'] = max($brands_map[$key]['total_products'], intval($fb->total_products));
                            } else {
                                $brands_map[$key] = [
                                    'brand_id' => null,
                                    'brand_name' => $s_name,
                                    'shop_name' => $s_name,
                                    'category' => '',
                                    'total_products' => intval($fb->total_products),
                                    'total_gmv' => floatval($fb->total_gmv)
                                ];
                            }
                        }
                    }
                } else {
                    log_message('error', 'Creator products brands query failed: ' . json_encode($this->db->error()));
                }
            } catch (Exception $e) {
                log_message('error', 'Error getting brands from creator_products: ' . $e->getMessage());
            }
        }

        // D. Ambil brand dari creator\'s brand_id (jika ada)
        if (!empty($creator->brand_id) && !empty($brand_name)) {
            $key = strtolower(trim($brand_name));
            $key_shop = !empty($brand_shop_name) ? strtolower(trim($brand_shop_name)) : $key;
            
            $found_key = null;
            if (isset($brands_map[$key_shop])) {
                $found_key = $key_shop;
            } elseif (isset($brands_map[$key])) {
                $found_key = $key;
            }

            if ($found_key) {
                $brands_map[$found_key]['brand_id'] = $creator->brand_id;
                $brands_map[$found_key]['brand_name'] = $brand_name;
                if (!empty($brand_shop_name)) {
                    $brands_map[$found_key]['shop_name'] = $brand_shop_name;
                }
            } else {
                $brands_map[$key_shop] = [
                    'brand_id' => $creator->brand_id,
                    'brand_name' => $brand_name,
                    'shop_name' => !empty($brand_shop_name) ? $brand_shop_name : $brand_name,
                    'category' => $creator->category ?? '',
                    'total_products' => 0,
                    'total_gmv' => floatval($creator->imported_gmv ?? 0)
                ];
            }
        }

        // D. Untuk semua brand di map, coba cari id dan nama brand aslinya dari tabel `brands` jika brand_id masih null
        $shop_names = array_column($brands_map, 'shop_name');
        if (!empty($shop_names)) {
            try {
                $this->db->select('id, name, shop_name')
                    ->where_in('shop_name', $shop_names)
                    ->from('brands');
                $q = $this->db->get();
                if ($q) {
                    $db_brands = $q->result();
                    foreach ($db_brands as $db_b) {
                        $key = strtolower(trim($db_b->shop_name));
                        if (isset($brands_map[$key])) {
                            $brands_map[$key]['brand_id'] = $db_b->id;
                            $brands_map[$key]['brand_name'] = $db_b->name;
                        }
                    }
                } else {
                    log_message('error', 'Matching database brands query failed: ' . json_encode($this->db->error()));
                }
            } catch (Exception $e) {
                log_message('error', 'Error matching brands to database: ' . $e->getMessage());
            }
        }

        // Ubah ke array of objects
        $brands = [];
        foreach ($brands_map as $b) {
            $brands[] = (object)$b;
        }

        // Urutkan berdasarkan total_gmv DESC agar brand dengan kontribusi tertinggi muncul pertama
        usort($brands, function($a, $b) {
            return $b->total_gmv <=> $a->total_gmv;
        });

        // Kalkulasikan keseluruhan GMV dari seluruh brand yang terhubung
        $total_gmv_sum = 0;
        foreach ($brands as $b) {
            $total_gmv_sum += floatval($b->total_gmv);
        }
        if ($total_gmv_sum > 0) {
            $total_gmv = $total_gmv_sum;
            $creator->total_gmv = $total_gmv_sum;
        }

        // ============================================================
        // 5.5 ENRICH DARI FASTMOSS — ambil semua brand collab creator
        // Dipanggil selalu; data FastMoss di-merge ke brands_map lokal.
        // Brand yang sudah ada di lokal diperbarui GMV-nya jika FastMoss
        // memberikan nilai lebih besar. Brand baru dari FastMoss ditambahkan.
        // ============================================================
        try {
            $this->load->model('Fastmoss_model');

            // Pastikan punya FastMoss UID (= tiktok_open_id di kolom creators)
            $fm_uid = $creator->tiktok_open_id ?? null;

            if (empty($fm_uid) && !empty($creator->username)) {
                // Gunakan resolve_uid_by_username yang lebih robust
                // (coba username langsung sebagai uid, kemudian search dengan/tanpa cookie)
                $fm_uid = $this->Fastmoss_model->resolve_uid_by_username($creator->username);
                if ($fm_uid) {
                    $this->db->where('id', $creator_id)
                             ->update('creators', [
                                 'tiktok_open_id' => $fm_uid,
                                 'updated_at'     => date('Y-m-d H:i:s')
                             ]);
                    $creator->tiktok_open_id = $fm_uid;
                }
            }

            if (!empty($fm_uid)) {
                $fm_brands = $this->Fastmoss_model->get_all_creator_brand_collabs($fm_uid, 5);

                log_message('debug', '[task1_detail] FastMoss returned ' . count($fm_brands) . ' brands for uid=' . $fm_uid);

                foreach ($fm_brands as $fb) {
                    $s_name = trim($fb['shop_name'] ?? '');
                    if (empty($s_name)) continue;

                    $key = strtolower($s_name);

                    if (isset($brands_map[$key])) {
                        // Brand sudah ada di lokal — ambil nilai GMV tertinggi
                        $brands_map[$key]['total_gmv']     = max(
                            floatval($brands_map[$key]['total_gmv']),
                            floatval($fb['gmv'])
                        );
                        $brands_map[$key]['total_products'] = max(
                            intval($brands_map[$key]['total_products']),
                            intval($fb['product_count'])
                        );
                        $brands_map[$key]['_source'] = 'merged';
                    } else {
                        // Brand baru — hanya dari FastMoss
                        $brands_map[$key] = [
                            'brand_id'      => null,
                            'brand_name'    => $s_name,
                            'shop_name'     => $s_name,
                            'shop_logo'     => $fb['shop_logo'] ?? '',
                            'category'      => '',
                            'total_products'=> intval($fb['product_count']),
                            'total_gmv'     => floatval($fb['gmv']),
                            '_source'       => 'fastmoss',
                        ];
                    }
                }

                // Rebuild $brands array dari brands_map yang sudah di-enrich
                $brands = [];
                foreach ($brands_map as $b) {
                    $brands[] = (object)$b;
                }
                usort($brands, function($a, $b) {
                    return $b->total_gmv <=> $a->total_gmv;
                });

                log_message('debug', '[task1_detail] Final brands after merge: ' . count($brands));

                // Hitung ulang total GMV
                $total_gmv_sum = 0;
                foreach ($brands as $b) {
                    $total_gmv_sum += floatval($b->total_gmv);
                }
                if ($total_gmv_sum > 0) {
                    $total_gmv = $total_gmv_sum;
                    $creator->total_gmv = $total_gmv_sum;
                }
            }
        } catch (Exception $e) {
            // Jangan gagalkan seluruh response jika FastMoss error
            log_message('error', '[task1_detail] FastMoss enrich error: ' . $e->getMessage());
        }
        
        // ============================================================
        // 6. FOLLOW UP COUNT - PERBAIKAN (tanpa link_type)
        // ============================================================
        $follow_up_count = 0;
        try {
            if ($this->db->table_exists('whatsapp_logs')) {
                // Cek dulu apakah kolom link_type ada
                $has_link_type = $this->db->query("SHOW COLUMNS FROM whatsapp_logs LIKE 'link_type'")->num_rows() > 0;
                
                if ($has_link_type) {
                    // Jika ada kolom link_type
                    $follow_up_count = $this->db->where('creator_id', $creator_id)
                        ->where('link_type', 'follow_up')
                        ->count_all_results('whatsapp_logs');
                } else {
                    // Jika tidak ada, gunakan status
                    $follow_up_count = $this->db->where('creator_id', $creator_id)
                        ->where('status', 'FOLLOW_UP')
                        ->count_all_results('whatsapp_logs');
                }
            }
        } catch (Exception $e) {
            log_message('error', 'Error getting follow_up_count: ' . $e->getMessage());
        }
        $creator->follow_up_count = $follow_up_count;
        
        // ============================================================
        // 7. WHATSAPP LOGS - PERBAIKAN (tanpa link_type)
        // ============================================================
        $whatsapp_logs = [];
        try {
            if ($this->db->table_exists('whatsapp_logs')) {
                $whatsapp_logs = $this->db->select('*')
                    ->from('whatsapp_logs')
                    ->where('creator_id', $creator_id)
                    ->order_by('sent_at', 'DESC')
                    ->limit(10)
                    ->get()
                    ->result();
            }
        } catch (Exception $e) {
            log_message('error', 'Error getting whatsapp logs: ' . $e->getMessage());
        }
        
        // ============================================================
        // 8. MULTI LINKS
        // ============================================================
        $multi_links = [];
        try {
            // Cek apakah ada tabel creator_multi_links
            if ($this->db->table_exists('creator_multi_links')) {
                $multi_links_data = $this->db->select('*')
                    ->from('creator_multi_links')
                    ->where('creator_id', $creator_id)
                    ->order_by('created_at', 'DESC')
                    ->limit(5)
                    ->get()
                    ->result();
                
                foreach ($multi_links_data as $ml) {
                    $multi_links[] = (object)[
                        'brand_name' => 'Multi Link',
                        'total_products' => count(json_decode($ml->product_ids ?? '[]', true)),
                        'multi_link' => $ml->links ?? '',
                        'created_at' => $ml->created_at
                    ];
                }
            }
        } catch (Exception $e) {
            log_message('error', 'Error getting multi_links: ' . $e->getMessage());
        }
        
        // ============================================================
        // 8.5 TANDAI is_partner — brand yang sudah bekerja sama
        // (ada di tabel brands dengan status ACTIVE)
        // Matching via: brand_id sudah terisi, ATAU shop_name/brand_name
        // cocok dengan brands.shop_name atau brands.name
        // ============================================================
        if (!empty($brands)) {
            // Kumpulkan semua nama/shop yang belum punya brand_id
            $unmatched_names = [];
            foreach ($brands as $b) {
                if (empty($b->brand_id)) {
                    if (!empty($b->shop_name))  $unmatched_names[] = trim($b->shop_name);
                    if (!empty($b->brand_name) && $b->brand_name !== $b->shop_name) {
                        $unmatched_names[] = trim($b->brand_name);
                    }
                }
            }

            // Satu query untuk ambil semua brand aktif yang namanya cocok
            $partner_names = [];
            if (!empty($unmatched_names)) {
                $q = $this->db->select('id, name, shop_name')
                    ->where('status', 'ACTIVE')
                    ->group_start()
                        ->where_in('name', $unmatched_names)
                        ->or_where_in('shop_name', $unmatched_names)
                    ->group_end()
                    ->get('brands');
                if ($q) {
                    foreach ($q->result() as $pb) {
                        $partner_names[strtolower(trim($pb->name))]      = intval($pb->id);
                        $partner_names[strtolower(trim($pb->shop_name))] = intval($pb->id);
                    }
                }
            }

            // Kumpulkan semua brand_id yang sudah terisi untuk di-validasi status ACTIVE-nya
            $existing_brand_ids = [];
            foreach ($brands as $b) {
                if (!empty($b->brand_id)) {
                    $existing_brand_ids[] = intval($b->brand_id);
                }
            }
            // Ambil brand_id yang benar-benar ACTIVE dari DB
            $active_brand_ids = [];
            if (!empty($existing_brand_ids)) {
                $qActive = $this->db->select('id')
                    ->where('status', 'ACTIVE')
                    ->where_in('id', $existing_brand_ids)
                    ->get('brands');
                if ($qActive) {
                    foreach ($qActive->result() as $ab) {
                        $active_brand_ids[intval($ab->id)] = true;
                    }
                }
            }

            foreach ($brands as $b) {
                if (!empty($b->brand_id) && isset($active_brand_ids[intval($b->brand_id)])) {
                    // brand_id ada DAN statusnya ACTIVE di DB
                    $b->is_partner = true;
                } else {
                    $key_shop  = strtolower(trim($b->shop_name  ?? ''));
                    $key_brand = strtolower(trim($b->brand_name ?? ''));
                    if (isset($partner_names[$key_shop]) || isset($partner_names[$key_brand])) {
                        $b->is_partner = true;
                        // Isi brand_id jika ketemu
                        $b->brand_id = $partner_names[$key_shop] ?? $partner_names[$key_brand] ?? null;
                    } else {
                        $b->is_partner = false;
                    }
                }
            }

            // Re-sort: partner duluan, lalu prospect, masing-masing by GMV desc
            $partners  = array_filter($brands, fn($b) => $b->is_partner);
            $prospects = array_filter($brands, fn($b) => !$b->is_partner);
            usort($partners,  fn($a, $b) => $b->total_gmv <=> $a->total_gmv);
            usort($prospects, fn($a, $b) => $b->total_gmv <=> $a->total_gmv);
            $brands = array_values(array_merge($partners, $prospects));
        }

        // ============================================================
        // 8.7 AUTO-FETCH PHONE FROM TAP (jika phone masih kosong)
        // Dilakukan secara silent di background — tidak memblokir response
        // ============================================================
        $phone_source = 'database';
        if (empty($creator->phone)) {
            $open_id_for_phone = $creator->tiktok_open_id ?? null;

            // Pastikan tiktok_open_id tersedia
            if (empty($open_id_for_phone) && !empty($creator->username)) {
                try {
                    $search_result = $this->jsm_api->search_creators_by_is($creator->username, null, 20);
                    if ($search_result['success'] && !empty($search_result['data']['creators'])) {
                        foreach ($search_result['data']['creators'] as $tc) {
                            if (strtolower($tc['username'] ?? '') === strtolower($creator->username)) {
                                if (!empty($tc['creator_open_id'])) {
                                    $open_id_for_phone = $tc['creator_open_id'];
                                    $this->db->where('id', $creator_id)->update('creators', [
                                        'tiktok_open_id' => $open_id_for_phone,
                                        'updated_at'     => date('Y-m-d H:i:s')
                                    ]);
                                    $creator->tiktok_open_id = $open_id_for_phone;
                                    break;
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    log_message('error', 'task1_detail auto-phone: search open_id failed: ' . $e->getMessage());
                }
            }

            if (!empty($open_id_for_phone)) {
                try {
                    $tap_detail = $this->jsm_api->get_creator_detail_by_id($open_id_for_phone);
                    if ($tap_detail['success']) {
                        $tap_creator_raw = $tap_detail['data']['creator'] ?? $tap_detail['data'];
                        // Prioritas 1: field phone langsung di response
                        $phone_fields = [
                            'phone_number', 'phone', 'mobile', 'whatsapp', 'wa_number',
                            'contact_phone', 'contact_number', 'telephone'
                        ];
                        $fetched_phone = '';
                        foreach ($phone_fields as $pf) {
                            if (!empty($tap_creator_raw[$pf])) {
                                $fetched_phone = $tap_creator_raw[$pf];
                                log_message('debug', 'task1_detail auto-phone: found phone in field "' . $pf . '"');
                                break;
                            }
                        }
                        // Prioritas 2: sub-object contact_info
                        if (empty($fetched_phone) && !empty($tap_creator_raw['contact_info'])) {
                            foreach ($phone_fields as $pf) {
                                if (!empty($tap_creator_raw['contact_info'][$pf])) {
                                    $fetched_phone = $tap_creator_raw['contact_info'][$pf];
                                    log_message('debug', 'task1_detail auto-phone: found phone in contact_info.' . $pf);
                                    break;
                                }
                            }
                        }
                        // Prioritas 3: parse dari bio_description (Endorsement di TAP UI)
                        if (empty($fetched_phone)) {
                            $bio_text = $tap_creator_raw['bio_description']
                                ?? $tap_creator_raw['bio']
                                ?? $tap_creator_raw['description']
                                ?? '';
                            if (!empty($bio_text)) {
                                $fetched_phone = $this->extractPhoneFromBio($bio_text);
                                if (!empty($fetched_phone)) {
                                    log_message('info', 'task1_detail auto-phone: found phone in bio_description: ' . $fetched_phone);
                                }
                            }
                        }

                        if (!empty($fetched_phone)) {
                            // Format nomor ke standar 62xxx
                            $fetched_phone = preg_replace('/[^0-9+]/', '', $fetched_phone);
                            if (preg_match('/^0/', $fetched_phone)) {
                                $fetched_phone = '62' . substr($fetched_phone, 1);
                            } elseif (preg_match('/^\+/', $fetched_phone)) {
                                $fetched_phone = substr($fetched_phone, 1);
                            } elseif (!preg_match('/^62/', $fetched_phone) && strlen($fetched_phone) > 0) {
                                $fetched_phone = '62' . $fetched_phone;
                            }

                            $creator->phone = $fetched_phone;
                            $phone_source = 'tap_api';
                            $this->db->where('id', $creator_id)->update('creators', [
                                'phone'      => $fetched_phone,
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                            log_message('info', 'task1_detail auto-phone: saved phone=' . $fetched_phone . ' for creator_id=' . $creator_id);
                        } else {
                            log_message('debug', 'task1_detail auto-phone: no phone found. bio_desc="'
                                . substr($tap_creator_raw['bio_description'] ?? '', 0, 100)
                                . '" keys=' . implode(', ', array_keys($tap_creator_raw)));
                        }
                    }
                } catch (Exception $e) {
                    log_message('error', 'task1_detail auto-phone: TAP detail failed: ' . $e->getMessage());
                }
            }
        }

        // ============================================================
        // 9. KIRIM RESPONSE
        // ============================================================
        $response = [
            'success' => true,
            'creator' => $creator,
            'brands' => $brands,
            'products' => $products,
            'whatsapp_logs' => $whatsapp_logs,
            'multi_links' => $multi_links,
            'total_gmv' => $total_gmv,
            'total_products' => count($products),
            'total_brands' => count($brands),
            'phone_source' => $phone_source
        ];
        
        log_message('debug', '=== get_creator_task1_detail SUCCESS ===');
        
        return $this->output->set_output(json_encode($response));
        
    } catch (Exception $e) {
        log_message('error', '=== get_creator_task1_detail EXCEPTION ===');
        log_message('error', 'Message: ' . $e->getMessage());
        log_message('error', 'File: ' . $e->getFile() . ':' . $e->getLine());
        log_message('error', 'Trace: ' . $e->getTraceAsString());
        
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage()
        ]));
    }
}

/**
 * Generate multi link internal
 */
private function generate_multi_link_internal($campaign_id, $product_ids, $creator_id) {
    if (empty($campaign_id) || empty($product_ids)) {
        return null;
    }
    
    try {
        $category_asset_cipher = $this->jsm_api->default_cipher ?? '';
        $result = $this->jsm_api->generate_multi_affiliate_links($campaign_id, $product_ids, $category_asset_cipher);
        
        if ($result['success'] && isset($result['data']['promotion_links'])) {
            // Simpan ke creator_multi_links
            $group_id = 'multi_' . $campaign_id . '_' . time() . '_' . $creator_id;
            
            $this->db->insert('creator_multi_links', [
                'creator_id' => $creator_id,
                'campaign_id' => $campaign_id,
                'group_id' => $group_id,
                'product_ids' => json_encode($product_ids),
                'links' => json_encode($result['data']['promotion_links']),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            return $result['data']['promotion_links'];
        }
        return null;
    } catch (Exception $e) {
        log_message('error', 'Generate multi link error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Send link via WhatsApp (Task 1)
 */
public function send_link_to_creator() {
    $this->output->set_content_type('application/json');
    
    if (!$this->session->userdata('logged_in')) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Session expired'
        ]));
    }
    
    $creator_id = $this->input->post('creator_id');
    $message = $this->input->post('message');
    $link = $this->input->post('link');
    $link_type = $this->input->post('link_type') ?: 'single';
    
    if (!$creator_id || !$message || !$link) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Missing required data'
        ]));
    }
    
    $creator = $this->db->where('id', $creator_id)->get('creators')->row();
    if (!$creator) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Creator not found'
        ]));
    }
    
    if (empty($creator->phone)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Creator has no WhatsApp number'
        ]));
    }
    
    // Format phone
    $phone = preg_replace('/[^0-9+]/', '', $creator->phone);
    if (preg_match('/^0/', $phone)) {
        $phone = '+62' . substr($phone, 1);
    } elseif (!preg_match('/^\+/', $phone)) {
        $phone = '+' . $phone;
    }
    $cleanPhone = ltrim($phone, '+');
    
    // Log WhatsApp
    $this->db->insert('whatsapp_logs', [
        'creator_id' => $creator_id,
        'user_id' => $this->session->userdata('user_id'),
        'user_name' => $this->session->userdata('full_name') ?: $this->session->userdata('username'),
        'phone_number' => $phone,
        'message' => $message,
        'link' => $link,
        'link_type' => $link_type,
        'status' => 'SENT',
        'sent_at' => date('Y-m-d H:i:s')
    ]);
    
    // Update status creator ke LINK_SENT jika masih PENDING
    if ($creator->status == 'PENDING') {
        $this->db->where('id', $creator_id)->update('creators', [
            'status' => 'LINK_SENT',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    $whatsapp_url = "https://wa.me/{$cleanPhone}?text=" . urlencode($message);
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'message' => 'WhatsApp opened',
        'redirect_url' => $whatsapp_url,
        'phone' => $cleanPhone
    ]));
}


/**
 * Follow up (send message again)
 */
public function follow_up_creator() {
    $this->output->set_content_type('application/json');
    
    if (!$this->session->userdata('logged_in')) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Session expired'
        ]));
    }
    
    $creator_id = $this->input->post('creator_id');
    $message = $this->input->post('message');
    
    if (!$creator_id || !$message) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Missing required data'
        ]));
    }
    
    $creator = $this->db->where('id', $creator_id)->get('creators')->row();
    if (!$creator) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Creator not found'
        ]));
    }
    
    if (empty($creator->phone)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Creator has no WhatsApp number'
        ]));
    }
    
    // Format phone
    $phone = preg_replace('/[^0-9+]/', '', $creator->phone);
    if (preg_match('/^0/', $phone)) {
        $phone = '+62' . substr($phone, 1);
    } elseif (!preg_match('/^\+/', $phone)) {
        $phone = '+' . $phone;
    }
    $cleanPhone = ltrim($phone, '+');
    
    // Log WhatsApp sebagai FOLLOW_UP
    $this->db->insert('whatsapp_logs', [
        'creator_id' => $creator_id,
        'user_id' => $this->session->userdata('user_id'),
        'user_name' => $this->session->userdata('full_name') ?: $this->session->userdata('username'),
        'phone_number' => $phone,
        'message' => $message,
        'link' => '',
        'link_type' => 'follow_up',
        'status' => 'FOLLOW_UP',
        'sent_at' => date('Y-m-d H:i:s')
    ]);
    
    // 🔥 UPDATE follow_up_count - PASTIKAN KOLOM ADA
    // Cek apakah kolom follow_up_count ada
    $columns = $this->db->query("SHOW COLUMNS FROM creators LIKE 'follow_up_count'")->num_rows();
    if ($columns > 0) {
        $this->db->where('id', $creator_id)
                 ->set('follow_up_count', 'follow_up_count + 1', FALSE)
                 ->update('creators');
    } else {
        // Fallback: update manual
        $current = $this->db->where('id', $creator_id)->get('creators')->row();
        $new_count = ($current->follow_up_count ?? 0) + 1;
        $this->db->where('id', $creator_id)->update('creators', ['follow_up_count' => $new_count]);
    }
    
    $whatsapp_url = "https://wa.me/{$cleanPhone}?text=" . urlencode($message);
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'message' => 'Follow up sent',
        'redirect_url' => $whatsapp_url,
        'phone' => $cleanPhone
    ]));
}
   
   
// ========== CREATOR MANAGEMENT ==========
public function creators() {
    $user_id = $this->session->userdata('user_id');
    $is_supervisor = ($user_id == 2);
    
    // 🔥 Ambil filter tanggal dari request
    $start_date = $this->input->get('start_date') ?: date('Y-m-d');
    $end_date = $this->input->get('end_date') ?: date('Y-m-d');
    
    // ============================================================
    // 🔥 AMBIL SEMUA CREATOR DARI TABEL creators
    // ============================================================
    $all_creators = $this->db->select('
            c.id,
            c.username,
            c.full_name,
            c.avatar_url,
            c.category,
            c.phone,
            c.email,
            c.alamat,
            c.penerima,
            c.status,
            c.is_id,
            c.created_at,
            c.approved_at,
            c.imported_gmv,
            c.follower_count,
            u.full_name as handler_name,
            u.username as handler_username,
            b.name as brand_name,
            b.shop_name
        ')
        ->from('creators c')
        ->join('users u', 'c.is_id = u.id', 'left')
        ->join('brands b', 'c.brand_id = b.id', 'left')
        ->order_by('c.created_at', 'DESC')
        ->get()
        ->result();
    
    // ============================================================
    // 🔥 BUILD MAP CREATOR
    // ============================================================
    $creators_map = [];
    
    foreach ($all_creators as $c) {
        $username = $c->username;
        
        // Hitung GMV 30 hari dari affiliate_orders
        $order_stats = $this->db->select('
                COALESCE(SUM(gmv), 0) as total_gmv,
                COUNT(DISTINCT order_id) as total_orders,
                COALESCE(SUM(estimated_commission), 0) as total_commission
            ')
            ->from('affiliate_orders')
            ->where('creator_username', $username)
            ->where('order_date_local >=', $start_date)
            ->where('order_date_local <=', $end_date)
            ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->get()
            ->row();
        
        // Ambil campaign dari campaign_creator_performance
        $campaigns = $this->db->select('
                ccp.campaign_id,
                cp.campaign_name,
                ccp.product_id,
                ccp.product_name,
                ccp.paid_amount,
                ccp.video_count,
                ccp.commission,
                ap.image_url as product_image
            ')
            ->from('campaign_creator_performance ccp')
            ->join('affiliate_campaigns cp', 'ccp.campaign_id = cp.campaign_id', 'left')
            ->join('affiliate_products ap', 'ccp.product_id = ap.product_id AND ccp.campaign_id = ap.campaign_id', 'left')
            ->where('ccp.creator_username', $username)
            ->where('ccp.is_active', 1)
            ->get()
            ->result();
        
        // Hitung active campaigns
        $active_campaigns = count($campaigns);
        
        $creators_map[$username] = (object)[
            'id' => $c->id,
            'username' => $c->username,
            'full_name' => $c->full_name ?: $c->username,
            'avatar' => $c->avatar_url,
            'follower_count' => intval($c->follower_count ?? 0),
            'category' => $c->category,
            'phone' => $c->phone,
            'email' => $c->email,
            'status' => $c->status,
            'is_id' => $c->is_id,
            'created_at' => $c->created_at,
            'approved_at' => $c->approved_at,
            'handler_name' => $c->handler_name,
            'handler_username' => $c->handler_username,
            'brand_name' => $c->brand_name,
            'shop_name' => $c->shop_name,
            'imported_gmv' => floatval($c->imported_gmv ?? 0),
            // Performance
            'total_gmv' => floatval($order_stats->total_gmv ?? 0),
            'total_orders' => intval($order_stats->total_orders ?? 0),
            'total_commission' => floatval($order_stats->total_commission ?? 0),
            'total_videos' => array_sum(array_column($campaigns, 'video_count')),
            'active_campaigns' => $active_campaigns,
            'campaigns' => $campaigns,
            'last_active' => $c->approved_at,
            // Flags
            'has_handler' => !empty($c->is_id),
            'is_task3' => ($c->status == 'ACTIVE'),
            'is_unassigned' => empty($c->is_id),
            'is_active' => ($c->status == 'ACTIVE' || floatval($order_stats->total_gmv ?? 0) > 0)
        ];
    }
    
    // ============================================================
    // 🔥 TAMBAHKAN UNASSIGNED CREATOR (TIDAK ADA DI TABEL creators)
    // ============================================================
    $all_usernames = array_keys($creators_map);
    
    $unassigned_sql = "
        SELECT 
            o.creator_username,
            COALESCE(SUM(o.gmv), 0) as total_gmv,
            COUNT(DISTINCT o.order_id) as total_orders,
            COALESCE(SUM(o.estimated_commission), 0) as total_commission,
            COUNT(DISTINCT o.campaign_id) as active_campaigns,
            MAX(o.product_name) as top_product
        FROM affiliate_orders o
        WHERE o.order_date_local >= ?
          AND o.order_date_local <= ?
          AND o.order_status NOT IN ('CANCELLED', 'REFUNDED')
          AND o.creator_username IS NOT NULL 
          AND o.creator_username != ''
          AND o.creator_username NOT IN (
              SELECT DISTINCT username 
              FROM creators 
              WHERE username IS NOT NULL AND username != ''
          )
        GROUP BY o.creator_username
        HAVING total_gmv > 0
    ";
    
    $unassigned_results = $this->db->query($unassigned_sql, [$start_date, $end_date])->result();
    
    foreach ($unassigned_results as $u) {
        $username = $u->creator_username;
        if (!isset($creators_map[$username])) {
            $creators_map[$username] = (object)[
                'id' => null,
                'username' => $username,
                'full_name' => $username . ' (Unassigned)',
                'avatar' => null,
                'follower_count' => 0,
                'category' => null,
                'phone' => null,
                'email' => null,
                'status' => 'ACTIVE',
                'is_id' => null,
                'created_at' => null,
                'approved_at' => null,
                'handler_name' => null,
                'handler_username' => null,
                'brand_name' => null,
                'shop_name' => null,
                'imported_gmv' => 0,
                'total_gmv' => floatval($u->total_gmv ?? 0),
                'total_orders' => intval($u->total_orders ?? 0),
                'total_commission' => floatval($u->total_commission ?? 0),
                'total_videos' => 0,
                'active_campaigns' => intval($u->active_campaigns ?? 0),
                'campaigns' => [],
                'last_active' => null,
                'has_handler' => false,
                'is_task3' => false,
                'is_unassigned' => true,
                'is_active' => true
            ];
        }
    }
    
    // ============================================================
    // 🔥 CONVERT KE ARRAY DAN SORT
    // ============================================================
    $creator_list = array_values($creators_map);
    usort($creator_list, function($a, $b) {
        return $b->total_gmv <=> $a->total_gmv;
    });
    
    // ============================================================
    // 🔥 STATISTIK SUMMARY - SAMA DENGAN TEAM PERFORMANCE
    // ============================================================
    $total_creators = count($creator_list);
    
    // ✅ Sama dengan Team Performance: total creators = semua yang ada di tabel creators
    $total_creators_from_table = count($all_creators);
    
    // Active creators = yang punya GMV > 0 atau status ACTIVE
    $active_creators = count(array_filter($creator_list, function($c) { 
        return $c->total_gmv > 0 || $c->status == 'ACTIVE';
    }));
    
    // Task 3 = status ACTIVE
    $task3_count = count(array_filter($creator_list, function($c) { 
        return $c->status == 'ACTIVE';
    }));
    
    // Unassigned = is_id NULL atau tidak ada di tabel creators
    $unassigned_count = count(array_filter($creator_list, function($c) { 
        return $c->is_unassigned || empty($c->is_id);
    }));
    
    // ✅ Total GMV = semua GMV termasuk unassigned
    $total_gmv = array_sum(array_column($creator_list, 'total_gmv'));
    
    // ✅ Total Orders = semua orders termasuk unassigned
    $total_orders = array_sum(array_column($creator_list, 'total_orders'));
    
    $summary = [
        'total_creators' => $total_creators,           // TOTAL SEMUA CREATOR (sama dengan team performance)
        'active_creators' => $active_creators,         // CREATOR AKTIF
        'task3_creators' => $task3_count,              // CREATOR DI TASK 3 (STATUS ACTIVE)
        'unassigned_count' => $unassigned_count,       // CREATOR TANPA HANDLER
        'total_gmv' => $total_gmv,
        'total_commission' => array_sum(array_column($creator_list, 'total_commission')),
        'total_orders' => $total_orders
    ];
    
    // ============================================================
    // 🔥 KIRIM KE VIEW
    // ============================================================
    $data = [
        'title' => 'Creators Management - Toopai IS',
        'active_menu' => 'creators',
        'creators' => $creator_list,
        'summary' => $summary,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'show_unassigned' => true
    ];
    
    $this->load->view('templates/header', $data);
    $this->load->view('is/creators', $data);
    $this->load->view('templates/footer');
}

// ========== PERFORMANCE PAGE ==========
public function performance() {
    $user_id = $this->session->userdata('user_id');
    
    // 🔥 Ambil filter tanggal dari request (default hari ini)
    $start_date = $this->input->get('start_date') ?: date('Y-m-d');
    $end_date = $this->input->get('end_date') ?: date('Y-m-d');
    
    // Pastikan end_date tidak melebihi hari ini
    $today = date('Y-m-d');
    if ($end_date > $today) $end_date = $today;
    
    // 🔥 AMBIL CREATOR MILIK IS INI
    $creators = $this->Creator_model->get_creators_by_is($user_id);
    $creator_usernames = array_column($creators, 'username');
    
    // ========== TOTAL STATS berdasarkan filter tanggal ==========
    $total_stats = (object)['total_gmv' => 0, 'total_commission' => 0, 'total_orders' => 0];
    
    if (!empty($creator_usernames)) {
        $total_stats = $this->db->select('
                COALESCE(SUM(o.gmv), 0) as total_gmv,
                COALESCE(SUM(o.estimated_commission), 0) as total_commission,
                COUNT(DISTINCT o.order_id) as total_orders
            ')
            ->from('affiliate_orders o')
            ->where_in('o.creator_username', $creator_usernames)
            ->where('o.order_date_local >=', $start_date)
            ->where('o.order_date_local <=', $end_date)
            ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->get()
            ->row();
    }
    
    // ========== PREVIOUS PERIOD STATS (untuk growth) ==========
    $days_diff = (strtotime($end_date) - strtotime($start_date)) / 86400 + 1;
    $prev_start_date = date('Y-m-d', strtotime("-$days_diff days", strtotime($start_date)));
    $prev_end_date = date('Y-m-d', strtotime('-1 day', strtotime($start_date)));
    
    $prev_stats = (object)['total_gmv' => 0, 'total_orders' => 0];
    
    if (!empty($creator_usernames) && $prev_start_date <= $prev_end_date) {
        $prev_stats = $this->db->select('
                COALESCE(SUM(o.gmv), 0) as total_gmv,
                COUNT(DISTINCT o.order_id) as total_orders
            ')
            ->from('affiliate_orders o')
            ->where_in('o.creator_username', $creator_usernames)
            ->where('o.order_date_local >=', $prev_start_date)
            ->where('o.order_date_local <=', $prev_end_date)
            ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->get()
            ->row();
    }
    
    $gmv_growth = $prev_stats->total_gmv > 0 ? (($total_stats->total_gmv - $prev_stats->total_gmv) / $prev_stats->total_gmv * 100) : ($total_stats->total_gmv > 0 ? 100 : 0);
    $orders_growth = $prev_stats->total_orders > 0 ? (($total_stats->total_orders - $prev_stats->total_orders) / $prev_stats->total_orders * 100) : ($total_stats->total_orders > 0 ? 100 : 0);
    
    // ========== DAILY PERFORMANCE (30 hari terakhir) ==========
    $daily_performance = [];
    if (!empty($creator_usernames)) {
        $daily_performance = $this->db->select('
                order_date_local as date,
                COUNT(DISTINCT order_id) as daily_orders,
                SUM(gmv) as daily_gmv,
                SUM(estimated_commission) as daily_commission
            ')
            ->from('affiliate_orders')
            ->where_in('creator_username', $creator_usernames)
            ->where('order_date_local >=', date('Y-m-d', strtotime('-30 days')))
            ->where('order_date_local <=', $end_date)
            ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->group_by('order_date_local')
            ->order_by('date', 'ASC')
            ->get()
            ->result();
    }
    
    // ========== CAMPAIGN PERFORMANCE berdasarkan filter tanggal ==========
    $campaign_performance = [];
    if (!empty($creator_usernames)) {
        $campaign_performance = $this->db->select('
                o.campaign_id,
                c.campaign_name,
                COUNT(DISTINCT o.order_id) as total_orders,
                SUM(o.gmv) as total_gmv,
                SUM(o.estimated_commission) as total_commission,
                COUNT(DISTINCT o.creator_username) as total_creators
            ')
            ->from('affiliate_orders o')
            ->join('affiliate_campaigns c', 'c.campaign_id = o.campaign_id', 'left')
            ->where_in('o.creator_username', $creator_usernames)
            ->where('o.order_date_local >=', $start_date)
            ->where('o.order_date_local <=', $end_date)
            ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->group_by('o.campaign_id')
            ->order_by('total_gmv', 'DESC')
            ->get()
            ->result();
    }
    
    // ========== CREATOR PERFORMANCE (TOP) berdasarkan filter tanggal ==========
    $creator_performance = [];
    if (!empty($creator_usernames)) {
        $creator_performance = $this->db->select('
                o.creator_username,
                COUNT(DISTINCT o.order_id) as total_orders,
                SUM(o.gmv) as total_gmv,
                SUM(o.estimated_commission) as total_commission,
                COUNT(DISTINCT o.campaign_id) as total_campaigns
            ')
            ->from('affiliate_orders o')
            ->where_in('o.creator_username', $creator_usernames)
            ->where('o.order_date_local >=', $start_date)
            ->where('o.order_date_local <=', $end_date)
            ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->group_by('o.creator_username')
            ->order_by('total_gmv', 'DESC')
            ->limit(10)
            ->get()
            ->result();
        
        // Tambahkan data dari campaign_creator_performance untuk tambahan info
        foreach ($creator_performance as $creator) {
            $ccp_data = $this->db->select('
                    SUM(video_count) as total_videos,
                    SUM(follower_count) as total_followers
                ')
                ->from('campaign_creator_performance')
                ->where('creator_username', $creator->creator_username)
                ->where('is_active', 1)
                ->get()
                ->row();
            
            $creator->total_videos = $ccp_data->total_videos ?? 0;
            $creator->total_followers = $ccp_data->total_followers ?? 0;
        }
    }
    
    // ========== TOP PRODUCTS berdasarkan filter tanggal ==========
    $top_products = [];
    if (!empty($creator_usernames)) {
        $top_products = $this->db->select('
                o.product_id,
                o.product_name,
                COUNT(DISTINCT o.order_id) as total_orders,
                SUM(o.gmv) as total_gmv,
                SUM(o.estimated_commission) as total_commission,
                COUNT(DISTINCT o.creator_username) as total_creators
            ')
            ->from('affiliate_orders o')
            ->where_in('o.creator_username', $creator_usernames)
            ->where('o.order_date_local >=', $start_date)
            ->where('o.order_date_local <=', $end_date)
            ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->group_by('o.product_id')
            ->order_by('total_gmv', 'DESC')
            ->limit(10)
            ->get()
            ->result();
    }
    
    // Hitung jumlah creator yang aktif di periode ini
    $creators_count = count(array_unique(array_column($creator_performance, 'creator_username')));
    
    $data = [
        'title' => 'Performance - Toopai IS',
        'active_menu' => 'performance',
        
        // Filter info
        'start_date' => $start_date,
        'end_date' => $end_date,
        
        // Stats
        'total_stats' => $total_stats,
        'daily_performance' => $daily_performance,
        'campaign_performance' => $campaign_performance,
        'creator_performance' => $creator_performance,
        'top_products' => $top_products,
        'creators_count' => $creators_count,
        
        // Growth
        'gmv_growth' => round($gmv_growth, 1),
        'orders_growth' => round($orders_growth, 1),
        'prev_stats' => $prev_stats,
        'prev_period' => [
            'start' => $prev_start_date,
            'end' => $prev_end_date
        ]
    ];
    
    $this->load->view('templates/header', $data);
    $this->load->view('is/performance', $data);
    $this->load->view('templates/footer');
}
    
    // ========== ADD CREATOR ==========
   public function add_creator() {
    $this->output->set_content_type('application/json');
    
    if (!$this->session->userdata('logged_in')) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Session expired'
        ]));
    }
    
    $user_id      = $this->session->userdata('user_id');
    $username     = strtolower(trim(ltrim($this->input->post('username'), '@')));
    $full_name    = $this->input->post('full_name');
    $category     = $this->input->post('category');
    $phone        = $this->input->post('phone');
    $email        = $this->input->post('email');
    $brand_id     = $this->input->post('brand_id');
    $shop_name    = $this->input->post('shop_name');
    $avatar_url   = $this->input->post('avatar_url');
    $follower_count = $this->input->post('follower_count');
    $gmv_28days   = $this->input->post('gmv');
    $force_save   = $this->input->post('force_save') === '1'; // bypass phone duplicate

    // Validasi
    if (empty($username)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Username TikTok wajib diisi'
        ]));
    }

    // Cek duplikat username pada brand yang sama
    $existing = $this->db->select('c.*, u.full_name AS owner_name, b.name AS brand_name_label')
                         ->from('creators c')
                         ->join('brands b', 'c.brand_id = b.id', 'left')
                         ->join('users u', 'c.is_id = u.id', 'left')
                         ->where('LOWER(c.username)', $username)
                         ->where('c.brand_id', $brand_id)
                         ->get()
                         ->row();

    if ($existing) {
        if (!empty($existing->is_id)) {
            $owner_name = $existing->owner_name ?: 'CA lain';
            $brand_label = $existing->brand_name_label ?: 'brand ini';
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => "Creator @{$username} untuk brand {$brand_label} sudah dikelola oleh {$owner_name}."
            ]));
        } else {
            // Ada record tapi belum ada ownership (is_id NULL). Kita bisa update is_id
            $update_data = [
                'is_id'      => $user_id,
                'status'     => 'PENDING',
                'updated_at' => date('Y-m-d H:i:s')
            ];
            if (!empty($full_name)) $update_data['full_name'] = $full_name;
            if (!empty($category)) $update_data['category'] = $category;
            if (!empty($phone)) $update_data['phone'] = $phone;
            if (!empty($email)) $update_data['email'] = $email;
            if (!empty($avatar_url)) $update_data['avatar_url'] = $avatar_url;
            if (!empty($follower_count)) $update_data['imported_followers'] = $follower_count;
            if (!empty($gmv_28days)) $update_data['imported_gmv'] = $gmv_28days;

            $this->db->where('id', $existing->id)->update('creators', $update_data);

            // Log aktivitas
            $this->load->model('User_log_model');
            $this->User_log_model->log(
                $user_id,
                $this->session->userdata('username'),
                'IS',
                'CLAIM_CREATOR_TASK1',
                "Claimed ownership of creator @{$username} for brand ID {$brand_id} to Task 1"
            );

            return $this->output->set_output(json_encode([
                'success'    => true,
                'message'    => '✅ @' . $username . ' berhasil ditambahkan ke Task 1 (Scouting)!',
                'creator_id' => $existing->id,
                'username'   => $username
            ]));
        }
    }

    // Cek duplikat nomor HP (kecuali force_save)
    if (!$force_save && !empty($phone)) {
        $normalized_input = preg_replace('/[^0-9]/', '', $phone);
        $input_tail = substr($normalized_input, -9);

        if (strlen($input_tail) === 9) {
            $all_creators = $this->db->select('id, username, full_name, phone, status')
                ->where('phone IS NOT NULL')
                ->where('phone !=', '')
                ->get('creators')
                ->result();

            $phone_matches = [];
            foreach ($all_creators as $c) {
                $db_tail = substr(preg_replace('/[^0-9]/', '', $c->phone), -9);
                if ($db_tail === $input_tail) {
                    $phone_matches[] = [
                        'id'        => $c->id,
                        'username'  => $c->username,
                        'full_name' => $c->full_name,
                        'phone'     => $c->phone,
                        'status'    => $c->status,
                    ];
                }
            }

            if (!empty($phone_matches)) {
                return $this->output->set_output(json_encode([
                    'success'         => false,
                    'phone_duplicate' => true,
                    'message'         => 'Nomor HP ini sudah terdaftar untuk creator lain.',
                    'matches'         => $phone_matches
                ]));
            }
        }
    }

    // INSERT KE TASK 1 (STATUS PENDING)
    $insert_data = [
        'username'      => $username,
        'full_name'     => $full_name ?: $username,
        'category'      => $category ?: 'Lifestyle',
        'phone'         => $phone,
        'email'         => $email,
        'is_id'         => $user_id,
        'brand_id'      => $brand_id,
        'shop_name'     => $shop_name,
        'source'        => 'manual',
        'status'        => 'PENDING',
        'avatar_url'    => $avatar_url,
        'imported_followers' => $follower_count,
        'imported_gmv'  => $gmv_28days,
        'created_at'    => date('Y-m-d H:i:s'),
        'updated_at'    => date('Y-m-d H:i:s')
    ];

    // Filter null/empty values
    $insert_data = array_filter($insert_data, function($value) {
        return $value !== null && $value !== '';
    });

    if ($this->db->insert('creators', $insert_data)) {
        $new_id = $this->db->insert_id();

        // Log aktivitas
        $this->load->model('User_log_model');
        $this->User_log_model->log(
            $user_id,
            $this->session->userdata('username'),
            'IS',
            'ADD_CREATOR_TASK1',
            "Added creator @{$username} to Task 1 (Scouting)" . ($force_save ? ' [force - phone duplicate bypassed]' : '')
        );

        return $this->output->set_output(json_encode([
            'success'    => true,
            'message'    => '✅ @' . $username . ' berhasil ditambahkan ke Task 1 (Scouting)!',
            'creator_id' => $new_id,
            'username'   => $username
        ]));
    }

    return $this->output->set_output(json_encode([
        'success' => false,
        'message' => 'Gagal menambahkan creator'
    ]));
}


 public function get_creator_detail() {
    $this->output->set_content_type('application/json');
    
    $creator_id = $this->input->post('creator_id');
    $start_date = $this->input->post('start_date') ?: date('Y-m-d', strtotime('-30 days'));
    $end_date = $this->input->post('end_date') ?: date('Y-m-d');
    
    if (!$creator_id) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Creator ID required'
        ]));
    }
    
    // AMBIL DATA CREATOR DARI TABEL creators
    $creator = $this->db->select('id, username, full_name, category, phone, email, status, created_at, approved_at, avatar_url, imported_followers, imported_gmv, source')
                        ->where('id', $creator_id)
                        ->get('creators')
                        ->row();
    
    if (!$creator) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Creator not found'
        ]));
    }
    
    // AMBIL STATISTIK DARI affiliate_orders (REALTIME)
    $order_stats = $this->db->select('
            COALESCE(SUM(gmv), 0) as total_gmv,
            COALESCE(SUM(estimated_commission), 0) as total_commission,
            COUNT(DISTINCT order_id) as total_orders
        ')
        ->from('affiliate_orders')
        ->where('creator_username', $creator->username)
        ->where('order_date_local >=', $start_date)
        ->where('order_date_local <=', $end_date)
        ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
        ->get()
        ->row();
    
    // AMBIL CAMPAIGN YANG DIIKUTI CREATOR
    $campaigns = $this->db->select('
            acl.campaign_id,
            acl.product_id,
            acl.product_name,
            acl.commission_rate,
            acl.created_at as link_created_at,
            cp.campaign_name,
            cp.status as campaign_status
        ')
        ->from('affiliate_creator_links acl')
        ->join('affiliate_campaigns cp', 'cp.campaign_id = acl.campaign_id', 'left')
        ->group_start()
            ->where('acl.creator_id', $creator_id)
            ->or_where('acl.creator_username', $creator->username)
        ->group_end()
        ->order_by('acl.created_at', 'DESC')
        ->get()
        ->result();
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'data' => [
            'id' => $creator->id,
            'username' => $creator->username,
            'full_name' => $creator->full_name,
            'category' => $creator->category,
            'phone' => $creator->phone,
            'email' => $creator->email,
            'status' => $creator->status,
            'created_at' => $creator->created_at,
            'approved_at' => $creator->approved_at,
            'avatar_url' => $creator->avatar_url,
            'imported_followers' => $creator->imported_followers,
            'imported_gmv' => $creator->imported_gmv,
            'source' => $creator->source,
            'total_gmv' => floatval($order_stats->total_gmv ?? 0),
            'total_commission' => floatval($order_stats->total_commission ?? 0),
            'total_orders' => intval($order_stats->total_orders ?? 0),
            'campaigns' => $campaigns
        ]
    ]));
}
    
   // ========== UPDATE CREATOR STATUS ==========
public function update_creator_status() {
    $this->output->set_content_type('application/json');
    
    $creator_id = $this->input->post('creator_id');
    $status = $this->input->post('status');
    
    if (!$creator_id) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Creator ID required']));
    }
    
    $allowed_status = ['PENDING', 'ACTIVE', 'REJECTED', 'INACTIVE', 'LINK_SENT', 'SAMPLE_SENT'];
    if (!in_array($status, $allowed_status)) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Invalid status']));
    }
    
    $update_data = [
        'status' => $status,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Jika status menjadi ACTIVE, tambahkan approved_at
    if ($status == 'ACTIVE') {
        $update_data['approved_at'] = date('Y-m-d H:i:s');
        $update_data['approved_by'] = $this->session->userdata('user_id');
    }
    
    $this->db->where('id', $creator_id);
    $result = $this->db->update('creators', $update_data);
    
    if ($result) {
        return $this->output->set_output(json_encode(['success' => true, 'message' => 'Status updated']));
    } else {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Failed to update status']));
    }
}
    
  public function approve_creator() {
    $this->output->set_content_type('application/json');
    
    $creator_id = $this->input->post('creator_id');
    
    if (!$creator_id) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Creator ID required']));
    }
    
    // 🔥 Ubah status menjadi LINK_SWAPPING (bukan ACTIVE)
    $this->db->where('id', $creator_id);
    $result = $this->db->update('creators', [
        'status' => 'LINK_SWAPPING',  // 🔥 STATUS BARU
        'approved_at' => date('Y-m-d H:i:s'),
        'approved_by' => $this->session->userdata('user_id'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    if ($result) {
        return $this->output->set_output(json_encode([
            'success' => true,
            'message' => 'Creator approved! Now ready for link assignment in Task 2.'
        ]));
    } else {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Failed to approve creator'
        ]));
    }
}

    
    // ========== GENERATE & SEND AFFILIATE LINKS ==========
    
    public function generate_affiliate_links() {
    $this->output->set_content_type('application/json');
    
    $creator_id = $this->input->post('creator_id');
    $campaign_id = $this->input->post('campaign_id');
    $products = json_decode($this->input->post('products'), true);
    
    if (!$creator_id || !$campaign_id || empty($products)) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Missing required data']));
    }
    
    $generated_links = [];
    $failed = [];
    
    foreach ($products as $product) {
        $commission = $product['commission'] ?? 10;
        $result = $this->Creator_model->generate_affiliate_link(
            $creator_id,
            $campaign_id,
            $product['id'],
            $product['name'],
            $commission
        );
        
        if ($result['success']) {
            $generated_links[] = [
                'product_name' => $product['name'],
                'link' => $result['link']
            ];
        } else {
            $failed[] = $product['name'];
        }
    }
    
    // Update creator status ke LINK_SENT setelah link digenerate
    $this->db->where('id', $creator_id);
    $this->db->update('creators', [
        'status' => 'LINK_SENT',
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'generated' => $generated_links,
        'failed' => $failed,
        'message' => count($generated_links) . ' links generated successfully'
    ]));
}
    
    public function send_affiliate_links_whatsapp() {
        $this->output->set_content_type('application/json');
        
        $creator_id = $this->input->post('creator_id');
        $phone_number = $this->input->post('phone_number');
        $message = $this->input->post('message');
        
        if (empty($phone_number) || empty($message)) {
            return $this->output->set_output(json_encode(['success' => false, 'message' => 'Phone and message required']));
        }
        
        // Format phone number
        $phone = preg_replace('/[^0-9+]/', '', $phone_number);
        if (preg_match('/^0/', $phone)) {
            $phone = '+62' . substr($phone, 1);
        } elseif (!preg_match('/^\+/', $phone)) {
            $phone = '+' . $phone;
        }
        $cleanPhone = ltrim($phone, '+');
        
        // Log WhatsApp
        $this->db->insert('whatsapp_logs', [
            'user_id' => $this->session->userdata('user_id'),
            'creator_id' => $creator_id,
            'phone_number' => $phone,
            'message' => $message,
            'status' => 'SENT',
            'sent_at' => date('Y-m-d H:i:s')
        ]);
        
        $whatsapp_url = "https://wa.me/{$cleanPhone}?text=" . urlencode($message);
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'message' => 'Opening WhatsApp...',
            'redirect_url' => $whatsapp_url
        ]));
    }
    
    // ========== SAMPLE REQUESTS ==========
    
    public function request_sample() {
        $this->output->set_content_type('application/json');
        
        $creator_id = $this->input->post('creator_id');
        $campaign_id = $this->input->post('campaign_id');
        $product_id = $this->input->post('product_id');
        $quantity = $this->input->post('quantity') ?: 1;
        $shipping_address = $this->input->post('shipping_address');
        
        $data = [
            'creator_id' => $creator_id,
            'campaign_id' => $campaign_id,
            'product_id' => $product_id,
            'quantity' => $quantity,
            'shipping_address' => $shipping_address,
            'status' => 'PENDING',
            'requested_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('sample_requests', $data);
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'message' => 'Sample request submitted'
        ]));
    }
    
    public function update_sample_tracking() {
        $this->output->set_content_type('application/json');
        
        $request_id = $this->input->post('request_id');
        $tracking_number = $this->input->post('tracking_number');
        $carrier = $this->input->post('carrier');
        
        $this->db->where('id', $request_id);
        $this->db->update('sample_requests', [
            'tracking_number' => $tracking_number,
            'carrier' => $carrier,
            'status' => 'SHIPPED',
            'shipped_at' => date('Y-m-d H:i:s')
        ]);
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'message' => 'Tracking updated'
        ]));
    }
     // ========== PERFORMANCE MONITORING ==========
    
    public function get_creator_performance() {
        $this->output->set_content_type('application/json');
        
        $creator_id = $this->input->post('creator_id');
        $days = $this->input->post('days') ?: 30;
        
        $start_date = date('Y-m-d', strtotime("-$days days"));
        
        $performance = $this->db->select('date, gmv, orders, commission')
            ->from('performance_data')
            ->where('creator_id', $creator_id)
            ->where('date >=', $start_date)
            ->order_by('date', 'DESC')
            ->get()
            ->result();
        
        $summary = [
            'total_gmv' => array_sum(array_column($performance, 'gmv')),
            'total_orders' => array_sum(array_column($performance, 'orders')),
            'total_commission' => array_sum(array_column($performance, 'commission')),
            'avg_daily_gmv' => count($performance) > 0 ? array_sum(array_column($performance, 'gmv')) / count($performance) : 0
        ];
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'summary' => $summary,
            'daily' => $performance
        ]));
    }
    // ========== SEND WHATSAPP TO CREATOR ==========
    public function send_whatsapp() {
        $this->output->set_content_type('application/json');
        
        $creator_id = $this->input->post('creator_id');
        $phone_number = $this->input->post('phone_number');
        $message = $this->input->post('message');
        $stage = $this->input->post('stage');
        
        if (empty($phone_number) || empty($message)) {
            return $this->output->set_output(json_encode(['success' => false, 'message' => 'Phone and message required']));
        }
        
        // Format nomor
        $phone = preg_replace('/[^0-9+]/', '', $phone_number);
        if (preg_match('/^0/', $phone)) {
            $phone = '+62' . substr($phone, 1);
        } elseif (!preg_match('/^\+/', $phone)) {
            $phone = '+' . $phone;
        }
        $cleanPhone = ltrim($phone, '+');
        
        // Log WhatsApp
        $this->db->insert('whatsapp_logs', [
            'user_id' => $this->session->userdata('user_id'),
            'creator_id' => $creator_id,
            'phone_number' => $phone,
            'message' => $message,
            'status' => 'SENT',
            'sent_at' => date('Y-m-d H:i:s')
        ]);
        
        // Update status creator berdasarkan stage
        if ($stage == 1) {
            $this->db->where('id', $creator_id);
            $this->db->update('creators', ['status' => 'LINK_SENT']);
        } elseif ($stage == 2) {
            $this->db->where('id', $creator_id);
            $this->db->update('creators', ['status' => 'SAMPLE_SENT']);
        }
        
        $whatsapp_url = "https://wa.me/{$cleanPhone}?text=" . urlencode($message);
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'message' => 'Opening WhatsApp...',
            'redirect_url' => $whatsapp_url
        ]));
    }
    
    // ========== GET CREATORS BY STATUS ==========
public function get_creators_by_status() {
    $this->output->set_content_type('application/json');
    
    $status = $this->input->post('status');
    $user_id = $this->session->userdata('user_id');
    $is_supervisor = ($user_id == 2);
    
    if (!$status) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Status required']));
    }
    
    // 🔥 Mapping status untuk Task 2
    if ($status == 'LINK_SWAPPING') {
        $status = 'LINK_SWAPPING';
    }
    
    if ($is_supervisor) {
        $creators = $this->db->select('c.*, u.username as is_username, u.full_name as is_full_name, b.name as brand_name, b.shop_name')
            ->from('creators c')
            ->join('users u', 'c.is_id = u.id', 'left')
            ->join('brands b', 'c.brand_id = b.id', 'left')
            ->where('c.status', $status)
            ->order_by('c.created_at', 'DESC')
            ->get()
            ->result();
    } else {
        $creators = $this->db->select('c.*, u.username as is_username, u.full_name as is_full_name, b.name as brand_name, b.shop_name')
            ->from('creators c')
            ->join('users u', 'c.is_id = u.id', 'left')
            ->join('brands b', 'c.brand_id = b.id', 'left')
            ->where('c.status', $status)
            ->where('c.is_id', $user_id)
            ->order_by('c.created_at', 'DESC')
            ->get()
            ->result();
    }
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'data' => $creators,
        'count' => count($creators),
        'is_supervisor' => $is_supervisor
    ]));
}


// ========== TASK 2: GET CAMPAIGN PRODUCTS FOR CREATOR ==========
public function get_campaign_products() {
    $this->output->set_content_type('application/json');
    
    $campaign_id = $this->input->post('campaign_id');
    $creator_category = $this->input->post('creator_category');
    
    if (!$campaign_id) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Campaign ID required']));
    }
    
    try {
        // Ambil produk dari campaign via API
        $products_result = $this->jsm_api->get_campaign_products($campaign_id, [
            'page_size' => 100,
            'review_status' => 'APPROVED'
        ]);
        
        if (!$products_result['success']) {
            return $this->output->set_output(json_encode([
                'success' => false, 
                'message' => $products_result['message'] ?? 'Failed to fetch products'
            ]));
        }
        
        $products = $products_result['data'];
        
        // Debug: log sample product untuk lihat struktur
        if (!empty($products)) {
            log_message('debug', 'Sample product from API: ' . json_encode($products[0]));
        }
        
        // 🔥 PERBAIKAN: Format untuk response dengan parsing yang benar
        $formatted_products = [];
        foreach ($products as $product) {
            // Ambil nama produk
            $product_name = '';
            if (isset($product['name'])) {
                $product_name = $product['name'];
            } elseif (isset($product['product_name'])) {
                $product_name = $product['product_name'];
            } elseif (isset($product['title'])) {
                $product_name = $product['title'];
            }
            
            // 🔥 AMBIL HARGA DARI LOWEST_PRICE ATAU HIGHEST_PRICE
            $price = 0;
            if (isset($product['lowest_price']) && is_array($product['lowest_price'])) {
                $price = floatval($product['lowest_price']['amount'] ?? 0);
            } elseif (isset($product['highest_price']) && is_array($product['highest_price'])) {
                $price = floatval($product['highest_price']['amount'] ?? 0);
            } elseif (isset($product['price'])) {
                if (is_array($product['price'])) {
                    $price = floatval($product['price']['amount'] ?? 0);
                } else {
                    $price = floatval($product['price']);
                }
            }
            
            // 🔥 AMBIL COMMISSION RATE (open_collaboration_commission_rate)
            $commission_rate = 0;
            if (isset($product['open_collaboration_commission_rate'])) {
                $commission_rate = floatval($product['open_collaboration_commission_rate']);
            } elseif (isset($product['commission_rate'])) {
                $commission_rate = floatval($product['commission_rate']);
            } elseif (isset($product['open_collab'])) {
                $commission_rate = floatval($product['open_collab']);
            }
            
            // 🔥 JIKA COMMISSION RATE MASIH 0, COBA DARI PARTNER_COMMISSION_RATE
            if ($commission_rate == 0 && isset($product['partner_commission_rate'])) {
                $commission_rate = floatval($product['partner_commission_rate']);
            }
            
            // 🔥 AMBIL GAMBAR
            $image_url = '';
            if (isset($product['main_image_url'])) {
                $image_url = $product['main_image_url'];
            } elseif (isset($product['image_url'])) {
                $image_url = $product['image_url'];
            } elseif (isset($product['cover_image_url'])) {
                $image_url = $product['cover_image_url'];
            }
            
            // 🔥 AMBIL SHOP NAME
            $shop_name = $product['shop_name'] ?? '';
            
            // 🔥 AMBIL CATEGORY
            $category = '';
            if (isset($product['category'])) {
                if (is_array($product['category'])) {
                    $category = $product['category']['name'] ?? '';
                } else {
                    $category = $product['category'];
                }
            }
            
            $formatted_products[] = [
                'product_id' => $product['id'],
                'product_name' => $product_name,
                'price' => $price,
                'commission_rate' => $commission_rate,
                'image_url' => $image_url,
                'sales_count' => $product['product_sales'] ?? $product['sales_count'] ?? 0,
                'shop_name' => $shop_name,
                'category' => $category
            ];
        }
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'products' => $formatted_products,
            'total' => count($formatted_products),
            'applied_filter' => $creator_category
        ]));
        
    } catch (Exception $e) {
        log_message('error', 'Error in get_campaign_products: ' . $e->getMessage());
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]));
    }
}


public function update_creator_phone() {
    $this->output->set_content_type('application/json');
    
    $creator_id = $this->input->post('creator_id');
    $phone = $this->input->post('phone');
    
    if (!$creator_id) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Creator ID required']));
    }
    
    // Format phone
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    if (preg_match('/^0/', $phone)) {
        $phone = '62' . substr($phone, 1);
    } elseif (preg_match('/^\+/', $phone)) {
        $phone = substr($phone, 1);
    }
    
    $this->db->where('id', $creator_id);
    $this->db->update('creators', [
        'phone' => $phone,
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'phone' => $phone,
        'message' => 'Nomor WhatsApp berhasil diupdate'
    ]));
}
// ========== TASK 2: GENERATE AFFILIATE LINK FOR CREATOR ==========
public function generate_creator_affiliate_link() {
    $this->output->set_content_type('application/json');
    
    $user_id = $this->session->userdata('user_id');
    $user_role = $this->session->userdata('role');
    
    // 🔥 HANYA USER DENGAN ID 1, 2, ATAU 3 YANG BISA GENERATE LINK LANGSUNG
    $allowed_ids = [1, 2, 3];
    
    if (!in_array($user_id, $allowed_ids)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Anda tidak memiliki akses untuk generate link. Silakan tunggu BD/BA generate link terlebih dahulu.',
            'can_generate' => false,
            'requires_bd' => true
        ]));
    }
    
    $creator_id = $this->input->post('creator_id');
    $campaign_id = $this->input->post('campaign_id');
    $product_id = $this->input->post('product_id');
    $product_name = $this->input->post('product_name');
    $commission_rate = $this->input->post('commission_rate') ?: 10;
    
    if (!$creator_id || !$campaign_id || !$product_id) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Missing required data'
        ]));
    }
    
    try {
        $link_result = $this->jsm_api->generate_promotion_link($campaign_id, $product_id, $commission_rate);
        
        if (!$link_result['success']) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => $link_result['message'] ?? 'Failed to generate link'
            ]));
        }
        
        $creator = $this->Creator_model->get_creator_by_id($creator_id);
        
        $existing = $this->db->where('creator_id', $creator_id)
                             ->where('campaign_id', $campaign_id)
                             ->where('product_id', $product_id)
                             ->get('affiliate_creator_links')
                             ->row();
        
        $link_data = [
            'creator_id' => $creator_id,
            'creator_username' => $creator->username,
            'campaign_id' => $campaign_id,
            'product_id' => $product_id,
            'product_name' => $product_name,
            'affiliate_link' => $link_result['link'],
            'commission_rate' => $commission_rate,
            'shared_date' => date('Y-m-d H:i:s'),
            'expire_at' => $link_result['expire_at'] ?? null,
            'status' => 'ACTIVE',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($existing) {
            $this->db->where('id', $existing->id)->update('affiliate_creator_links', $link_data);
            $message = 'Affiliate link updated successfully';
        } else {
            $link_data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('affiliate_creator_links', $link_data);
            $message = 'Affiliate link generated successfully';
        }
        
        $this->load->model('User_log_model');
        $this->User_log_model->log(
            $user_id,
            $this->session->userdata('username'),
            'IS',
            'GENERATE_AFFILIATE_LINK',
            "Generated affiliate link for creator @{$creator->username}, product: $product_name, commission: $commission_rate%"
        );
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'link' => $link_result['link'],
            'commission_rate' => $commission_rate,
            'message' => $message,
            'can_generate' => true
        ]));
        
    } catch (Exception $e) {
        log_message('error', 'Error in generate_creator_affiliate_link: ' . $e->getMessage());
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]));
    }
}


// ========== TASK 2: SEND AFFILIATE LINKS BULK (DENGAN CEK HAK AKSES) ==========
// public function send_affiliate_links_bulk() {
//     $this->output->set_content_type('application/json');
    
//     $user_id = $this->session->userdata('user_id');
//     $creator_id = $this->input->post('creator_id');
//     $campaign_id = $this->input->post('campaign_id');
//     $products = json_decode($this->input->post('products'), true);
    
//     if (!$creator_id || !$campaign_id || empty($products)) {
//         return $this->output->set_output(json_encode(['success' => false, 'message' => 'Missing required data']));
//     }
    
//     $creator = $this->Creator_model->get_creator_by_id($creator_id);
//     $generated_links = [];
//     $failed = [];
    
//     // 🔥 HANYA USER DENGAN ID 1,2,3 YANG BISA GENERATE LINK LANGSUNG
//     $allowed_ids = [1, 2, 3];
//     $can_generate_direct = in_array($user_id, $allowed_ids);
    
//     foreach ($products as $product) {
//         $product_name = $product['product_name'] ?? '';
//         $product_id = $product['product_id'] ?? '';
//         $commission = $product['custom_commission'] ?? $product['commission_rate'] ?? 10;
        
//         if ($can_generate_direct) {
//             // 🔥 User dengan ID 1,2,3: Generate link langsung via API
//             try {
//                 $link_result = $this->jsm_api->generate_promotion_link($campaign_id, $product_id, $commission);
                
//                 if ($link_result['success']) {
//                     $generated_links[] = [
//                         'product_name' => $product_name,
//                         'link' => $link_result['link'],
//                         'commission_rate' => $commission,
//                         'source' => 'generated'
//                     ];
                    
//                     $this->save_affiliate_link_to_db($creator_id, $creator->username, $campaign_id, $product_id, $product_name, $link_result['link'], $commission);
//                 } else {
//                     $failed[] = $product_name;
//                 }
//             } catch (Exception $e) {
//                 $failed[] = $product_name;
//             }
//         } else {
//             // 🔥 User selain ID 1,2,3: Ambil link dari bd_affiliate_links
//             $bd_link = $this->db->select('affiliate_link, commission_rate, created_by_name')
//                                 ->from('bd_affiliate_links')
//                                 ->where('product_id', $product_id)
//                                 ->where('campaign_id', $campaign_id)
//                                 ->where('status', 'ACTIVE')
//                                 ->order_by('created_at', 'DESC')
//                                 ->limit(1)
//                                 ->get()
//                                 ->row();
            
//             if ($bd_link) {
//                 $generated_links[] = [
//                     'product_name' => $product_name,
//                     'link' => $bd_link->affiliate_link,
//                     'commission_rate' => $bd_link->commission_rate,
//                     'source' => 'BD',
//                     'created_by' => $bd_link->created_by_name
//                 ];
                
//                 $this->save_affiliate_link_to_db($creator_id, $creator->username, $campaign_id, $product_id, $product_name, $bd_link->affiliate_link, $bd_link->commission_rate);
//             } else {
//                 $failed[] = $product_name;
//             }
//         }
//     }
    
//     // Update status creator ke LINK_SENT jika ada link yang berhasil
//     if (!empty($generated_links)) {
//         $this->db->where('id', $creator_id)->update('creators', [
//             'status' => 'LINK_SENT',
//             'updated_at' => date('Y-m-d H:i:s')
//         ]);
//     }
    
//     // Tampilkan pesan khusus
//     $message = count($generated_links) . ' links berhasil diproses';
//     if (!empty($failed) && !$can_generate_direct) {
//         $message .= '. ⚠️ Link untuk produk berikut belum tersedia: ' . implode(', ', $failed) . '. Silakan minta BA/BD generate link terlebih dahulu.';
//     } elseif (!empty($failed)) {
//         $message .= '. ' . count($failed) . ' produk gagal.';
//     }
    
//     return $this->output->set_output(json_encode([
//         'success' => true,
//         'generated' => $generated_links,
//         'failed' => $failed,
//         'message' => $message,
//         'can_generate' => $can_generate_direct
//     ]));
// }

public function send_affiliate_links_bulk() {
    $this->output->set_content_type('application/json');
    
    $user_id = $this->session->userdata('user_id');
    $creator_id = $this->input->post('creator_id');
    $campaign_id = $this->input->post('campaign_id');
    $products = json_decode($this->input->post('products'), true);
    
    if (!$creator_id || !$campaign_id || empty($products)) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Missing required data']));
    }
    
    $creator = $this->Creator_model->get_creator_by_id($creator_id);
    $generated_links = [];
    $failed = [];
    
    foreach ($products as $product) {
        $product_name = $product['product_name'] ?? '';
        $product_id = $product['product_id'] ?? '';
        $commission = $product['custom_commission'] ?? $product['commission_rate'] ?? 10;
        
        // Ambil link dari bd_affiliate_links
        $bd_link = $this->db->select('affiliate_link, commission_rate, created_by_name, product_name as bd_product_name')
                            ->from('bd_affiliate_links')
                            ->where('product_id', $product_id)
                            ->where('campaign_id', $campaign_id)
                            ->where('status', 'ACTIVE')
                            ->order_by('created_at', 'DESC')
                            ->limit(1)
                            ->get()
                            ->row();
        
        if ($bd_link) {
            $generated_links[] = [
                'product_id' => $product_id,
                'product_name' => $product_name,
                'link' => $bd_link->affiliate_link,
                'commission_rate' => $bd_link->commission_rate,
                'source' => 'BD',
                'created_by' => $bd_link->created_by_name
            ];
            
            $this->save_creator_affiliate_link($creator_id, $creator->username, $campaign_id, $product_id, $product_name, $bd_link->affiliate_link, $bd_link->commission_rate);
        } else {
            $failed[] = $product_name;
        }
    }
    
    // 🔥 Update status creator ke LINK_SENT (dari LINK_SWAPPING)
    if (!empty($generated_links)) {
        $this->db->where('id', $creator_id)->update('creators', [
            'status' => 'LINK_SENT',  // 🔥 Berubah dari LINK_SWAPPING ke LINK_SENT
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    $message = count($generated_links) . ' links berhasil diambil dari database';
    if (!empty($failed)) {
        $message .= '. ⚠️ Link untuk: ' . implode(', ', $failed) . ' belum tersedia. Silakan minta BA/BD generate link terlebih dahulu.';
    }
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'generated' => $generated_links,
        'failed' => $failed,
        'message' => $message,
        'can_generate' => false
    ]));
}
// 🔥 Helper function untuk menyimpan link ke affiliate_creator_links
private function save_creator_affiliate_link($creator_id, $creator_username, $campaign_id, $product_id, $product_name, $affiliate_link, $commission_rate) {
    $existing = $this->db->where('creator_id', $creator_id)
                         ->where('campaign_id', $campaign_id)
                         ->where('product_id', $product_id)
                         ->get('affiliate_creator_links')
                         ->row();
    
    $link_data = [
        'creator_id' => $creator_id,
        'creator_username' => $creator_username,
        'campaign_id' => $campaign_id,
        'product_id' => $product_id,
        'product_name' => $product_name,
        'affiliate_link' => $affiliate_link,
        'commission_rate' => $commission_rate,
        'shared_date' => date('Y-m-d H:i:s'),
        'status' => 'ACTIVE',
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    if ($existing) {
        $this->db->where('id', $existing->id)->update('affiliate_creator_links', $link_data);
    } else {
        $link_data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('affiliate_creator_links', $link_data);
    }
}

// ========== TASK 3: GET SAMPLE REQUESTS ==========
public function get_sample_requests() {
    $this->output->set_content_type('application/json');
    
    $creator_id = $this->input->post('creator_id');
    
    $this->db->select('sr.*, c.username, c.full_name, cp.campaign_name, p.product_name')
             ->from('sample_requests sr')
             ->join('creators c', 'c.id = sr.creator_id')
             ->join('affiliate_campaigns cp', 'cp.campaign_id = sr.campaign_id')
             ->join('affiliate_products p', 'p.product_id = sr.product_id AND p.campaign_id = sr.campaign_id', 'left')
             ->order_by('sr.requested_at', 'DESC');
    
    if ($creator_id) {
        $this->db->where('sr.creator_id', $creator_id);
    }
    
    $requests = $this->db->get()->result();
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'requests' => $requests
    ]));
}

// ========== TASK 3: REQUEST SAMPLE FROM SELLER ==========
public function request_sample_from_seller() {
    $this->output->set_content_type('application/json');
    
    $creator_id = $this->input->post('creator_id');
    $campaign_id = $this->input->post('campaign_id');
    $product_id = $this->input->post('product_id');
    $quantity = $this->input->post('quantity') ?: 1;
    $shipping_address = $this->input->post('shipping_address');
    $courier = $this->input->post('courier');
    
    if (!$creator_id || !$campaign_id || !$product_id) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Missing required data']));
    }
    
    // Cek apakah sudah pernah request
    $existing = $this->db->where('creator_id', $creator_id)
                         ->where('campaign_id', $campaign_id)
                         ->where('product_id', $product_id)
                         ->where('status !=', 'REJECTED')
                         ->get('sample_requests')
                         ->row();
    
    if ($existing) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Sample request already exists for this product'
        ]));
    }
    
    $data = [
        'creator_id' => $creator_id,
        'campaign_id' => $campaign_id,
        'product_id' => $product_id,
        'quantity' => $quantity,
        'shipping_address' => $shipping_address,
        'courier' => $courier,
        'status' => 'PENDING',
        'requested_at' => date('Y-m-d H:i:s')
    ];
    
    $this->db->insert('sample_requests', $data);
    $request_id = $this->db->insert_id();
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'request_id' => $request_id,
        'message' => 'Sample request submitted successfully'
    ]));
}

// ========== TASK 4: GET CREATOR PERFORMANCE FROM REALTIME API ==========
public function get_creator_realtime_performance() {
    $this->output->set_content_type('application/json');
    
    $creator_id = $this->input->post('creator_id');
    $days = $this->input->post('days') ?: 30;
    
    if (!$creator_id) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Creator ID required']));
    }
    
    $creator = $this->Creator_model->get_creator_by_id($creator_id);
    
    if (!$creator) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Creator not found']));
    }
    
    // Ambil data dari affiliate_orders (realtime dari cron job)
    $orders = $this->db->select('
            order_id,
            campaign_id,
            product_name,
            order_status,
            gmv,
            estimated_commission,
            actual_commission,
            order_time,
            DATE(order_time) as order_date
        ')
        ->from('affiliate_orders')
        ->where('creator_username', $creator->username)
        ->where('order_time >=', date('Y-m-d H:i:s', strtotime("-$days days")))
        ->order_by('order_time', 'DESC')
        ->get()
        ->result();
    
    // Hitung summary
    $summary = [
        'total_gmv' => 0,
        'total_orders' => 0,
        'total_estimated_commission' => 0,
        'total_actual_commission' => 0,
        'completed_orders' => 0,
        'processing_orders' => 0
    ];
    
    foreach ($orders as $order) {
        $summary['total_gmv'] += $order->gmv;
        $summary['total_orders']++;
        $summary['total_estimated_commission'] += $order->estimated_commission;
        $summary['total_actual_commission'] += $order->actual_commission;
        
        if ($order->order_status == 'COMPLETED') {
            $summary['completed_orders']++;
        } elseif ($order->order_status == 'PROCESSING') {
            $summary['processing_orders']++;
        }
    }
    
    // Daily performance
    $daily = $this->db->select('
            DATE(order_time) as date,
            COUNT(*) as orders,
            SUM(gmv) as gmv,
            SUM(estimated_commission) as estimated_commission,
            SUM(actual_commission) as actual_commission
        ')
        ->from('affiliate_orders')
        ->where('creator_username', $creator->username)
        ->where('order_time >=', date('Y-m-d H:i:s', strtotime("-$days days")))
        ->group_by('DATE(order_time)')
        ->order_by('date', 'DESC')
        ->get()
        ->result();
    
    // Campaign breakdown
    $campaign_breakdown = $this->db->select('
            campaign_id,
            campaign_name,
            COUNT(*) as orders,
            SUM(gmv) as gmv,
            SUM(estimated_commission) as estimated_commission
        ')
        ->from('affiliate_orders')
        ->where('creator_username', $creator->username)
        ->where('order_time >=', date('Y-m-d H:i:s', strtotime("-$days days")))
        ->group_by('campaign_id')
        ->order_by('gmv', 'DESC')
        ->get()
        ->result();
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'creator' => [
            'id' => $creator->id,
            'username' => $creator->username,
            'full_name' => $creator->full_name,
            'category' => $creator->category,
            'status' => $creator->status
        ],
        'summary' => $summary,
        'daily_performance' => $daily,
        'campaign_breakdown' => $campaign_breakdown,
        'recent_orders' => array_slice($orders, 0, 20)
    ]));
}

// ========== GET AFFILIATE LINKS FOR CREATOR ==========
public function get_creator_affiliate_links() {
    $this->output->set_content_type('application/json');
    
    $creator_id = $this->input->post('creator_id');
    
    if (!$creator_id) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Creator ID required']));
    }

    // Ambil username untuk fallback
    $creator_row = $this->db->select('username')->where('id', $creator_id)->get('creators')->row();
    $creator_username = $creator_row->username ?? '';

    $links = $this->db->select('
            acl.*,
            cp.campaign_name,
            cp.status as campaign_status
        ')
        ->from('affiliate_creator_links acl')
        ->join('affiliate_campaigns cp', 'cp.campaign_id = acl.campaign_id', 'left')
        ->group_start()
            ->where('acl.creator_id', $creator_id)
            ->or_where('acl.creator_username', $creator_username)
        ->group_end()
        ->order_by('acl.created_at', 'DESC')
        ->get()
        ->result();
    
    // Debug log
    log_message('debug', 'get_creator_affiliate_links for creator_id: ' . $creator_id . ', found: ' . count($links));
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'links' => $links,
        'total' => count($links)
    ]));
}

// ========== TEAM PERFORMANCE ==========
public function team_performance() {
    $user_id = $this->session->userdata('user_id');
    $role = $this->session->userdata('role');
    
    $start_date = $this->input->get('start_date') ?: date('Y-m-d');
    $end_date = $this->input->get('end_date') ?: date('Y-m-d');
    
    // ============================================================
    // AMBIL SEMUA TEAM MEMBER IS
    // ============================================================
    if ($role == 'admin') {
        $team_members = $this->db->select('id, username, full_name, role, created_at')
                                 ->where('role', 'IS')
                                 ->order_by('created_at', 'ASC')
                                 ->get('users')
                                 ->result();
    } else {
        $team_members = $this->db->select('id, username, full_name, role, created_at')
                                 ->group_start()
                                     ->where('id', $user_id)
                                     ->or_where('parent_id', $user_id)
                                 ->group_end()
                                 ->where('role', 'IS')
                                 ->order_by('created_at', 'ASC')
                                 ->get('users')
                                 ->result();
    }
    
    $all_members = [];
    
    foreach ($team_members as $member) {
        // Jumlah creator yang ditangani
        $member->total_creators = $this->db->where('is_id', $member->id)
                                           ->count_all_results('creators');
        
        // Creator yang AKTIF (punya penjualan)
        $active_stats = $this->db->select('COUNT(DISTINCT c.id) as total')
            ->from('creators c')
            ->join('affiliate_orders o', 'LOWER(TRIM(c.username)) = LOWER(TRIM(o.creator_username))', 'inner')
            ->where('c.is_id', $member->id)
            ->where('o.order_date_local >=', $start_date)
            ->where('o.order_date_local <=', $end_date)
            ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->get()
            ->row();
        
        $member->active_creators = intval($active_stats->total ?? 0);
        
        // TOTAL GMV
        $gmv_stats = $this->db->select('
                COALESCE(SUM(o.gmv), 0) as total_gmv,
                COUNT(DISTINCT o.order_id) as total_orders,
                COALESCE(SUM(o.estimated_commission), 0) as total_commission
            ')
            ->from('creators c')
            ->join('affiliate_orders o', 'LOWER(TRIM(c.username)) = LOWER(TRIM(o.creator_username))', 'inner')
            ->where('c.is_id', $member->id)
            ->where('o.order_date_local >=', $start_date)
            ->where('o.order_date_local <=', $end_date)
            ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->get()
            ->row();
        
        $member->total_gmv = floatval($gmv_stats->total_gmv ?? 0);
        $member->total_orders = intval($gmv_stats->total_orders ?? 0);
        $member->total_commission = floatval($gmv_stats->total_commission ?? 0);
        
        // Task stats
        $member->task_stats = [
            'scouting' => $this->db->where('is_id', $member->id)->where('status', 'PENDING')->count_all_results('creators'),
            'link_swapping' => $this->db->where('is_id', $member->id)->where('status', 'LINK_SWAPPING')->count_all_results('creators'),
            'link_sent' => $this->db->where('is_id', $member->id)->where('status', 'LINK_SENT')->count_all_results('creators'),
            'sample_sent' => $this->db->where('is_id', $member->id)->where('status', 'SAMPLE_SENT')->count_all_results('creators'),
            'monitoring' => $member->active_creators
        ];
        
        $member->progress = $member->total_creators > 0 
            ? round(($member->active_creators / $member->total_creators) * 100, 1) 
            : 0;
        
        $member->is_unassigned = false;
        $all_members[] = $member;
    }
    
    // ============================================================
    // 🔥 TAMBAHKAN UNASSIGNED - PERBAIKAN DENGAN SQL LANGSUNG
    // ============================================================
    $unassigned_sql = "
        SELECT 
            COALESCE(SUM(o.gmv), 0) as total_gmv,
            COUNT(DISTINCT o.order_id) as total_orders,
            COUNT(DISTINCT o.creator_username) as total_creators,
            COUNT(DISTINCT o.creator_username) as active_creators
        FROM affiliate_orders o
        WHERE o.order_date_local >= ?
          AND o.order_date_local <= ?
          AND o.order_status NOT IN ('CANCELLED', 'REFUNDED')
          AND (
              o.creator_username NOT IN (
                  SELECT DISTINCT username 
                  FROM creators 
                  WHERE username IS NOT NULL AND username != ''
              )
              OR o.creator_username IN (
                  SELECT DISTINCT username 
                  FROM creators 
                  WHERE is_id IS NULL
              )
          )
    ";
    
    $unassigned_result = $this->db->query($unassigned_sql, [$start_date, $end_date])->row();
    
    $unassigned_gmv = floatval($unassigned_result->total_gmv ?? 0);
    $unassigned_orders = intval($unassigned_result->total_orders ?? 0);
    $unassigned_creators = intval($unassigned_result->total_creators ?? 0);
    $unassigned_active = intval($unassigned_result->active_creators ?? 0);
    
    // DEBUG: Log hasil unassigned
    log_message('debug', 'UNASSIGNED - GMV: ' . $unassigned_gmv . ', Creators: ' . $unassigned_creators);
    
    // Buat object unassigned jika ada data
    if ($unassigned_gmv > 0 || $unassigned_creators > 0) {
        $unassigned = new stdClass();
        $unassigned->id = 0;
        $unassigned->username = 'unassigned';
        $unassigned->full_name = '⚠️ Non Handler (Unassigned)';
        $unassigned->role = 'UNASSIGNED';
        $unassigned->created_at = null;
        $unassigned->total_creators = $unassigned_creators;
        $unassigned->active_creators = $unassigned_active;
        $unassigned->total_gmv = $unassigned_gmv;
        $unassigned->total_orders = $unassigned_orders;
        $unassigned->total_commission = 0;
        $unassigned->task_stats = [
            'scouting' => 0,
            'link_swapping' => 0,
            'link_sent' => 0,
            'sample_sent' => 0,
            'monitoring' => $unassigned_active
        ];
        $unassigned->progress = $unassigned_creators > 0 
            ? round(($unassigned_active / $unassigned_creators) * 100, 1) 
            : 0;
        $unassigned->is_unassigned = true;
        
        // Tambahkan ke array di AWAL
        array_unshift($all_members, $unassigned);
    }
    
    // ============================================================
    // URUTKAN
    // ============================================================
    usort($all_members, function($a, $b) {
        if (isset($a->is_unassigned) && $a->is_unassigned) return -1;
        if (isset($b->is_unassigned) && $b->is_unassigned) return 1;
        return $b->total_gmv <=> $a->total_gmv;
    });
    
    // ============================================================
    // TOTAL KESELURUHAN
    // ============================================================
    $team_summary = [
        'total_members' => count($all_members),
        'total_gmv' => array_sum(array_column($all_members, 'total_gmv')),
        'total_orders' => array_sum(array_column($all_members, 'total_orders')),
        'total_commission' => array_sum(array_column($all_members, 'total_commission')),
        'total_creators' => array_sum(array_column($all_members, 'total_creators')),
        'active_creators' => array_sum(array_column($all_members, 'active_creators')),
        'unassigned_gmv' => $unassigned_gmv,
        'unassigned_creators' => $unassigned_creators,
        'unassigned_orders' => $unassigned_orders
    ];
    
    $data = [
        'title' => 'Team Performance - Toopai IS',
        'active_menu' => 'team_performance',
        'team_members' => $all_members,
        'team_summary' => $team_summary,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'my_role' => $role,
        'show_unassigned' => ($unassigned_gmv > 0 || $unassigned_creators > 0)
    ];
    
    $this->load->view('templates/header', $data);
    $this->load->view('is/team_performance', $data);
    $this->load->view('templates/footer');
}


// ========== CHECK IF IS CAN GENERATE LINK (Only user ID=1) ==========
public function can_generate_link() {
    $this->output->set_content_type('application/json');
    
    $user_id = $this->session->userdata('user_id');
    $role = $this->session->userdata('role');
    $username = $this->session->userdata('username');
    
    // 🔥 HANYA USER DENGAN ID 1,2,3 YANG BISA GENERATE LINK
    $allowed_ids = [1, 2, 3];
    $can_generate = in_array($user_id, $allowed_ids);
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'can_generate' => $can_generate,
        'user_id' => $user_id,
        'role' => $role,
        'username' => $username,
        'allowed_ids' => $allowed_ids,
        'message' => $can_generate ? 'Anda dapat generate link afiliasi' : 'Anda hanya dapat mengambil link yang sudah digenerate oleh BA/BD'
    ]));
}

// ========== GET BD AFFILIATE LINK FOR IS ==========
public function get_bd_affiliate_link() {
    $this->output->set_content_type('application/json');
    
    $product_id = $this->input->post('product_id');
    $campaign_id = $this->input->post('campaign_id');
    
    if (!$product_id || !$campaign_id) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'has_link' => false,
            'message' => 'Product ID and Campaign ID required'
        ]));
    }
    
    // 🔥 AMBIL DARI bd_affiliate_links
    $link = $this->db->select('affiliate_link, commission_rate, created_by_name, created_at, open_commission_rate')
                     ->from('bd_affiliate_links')
                     ->where('product_id', $product_id)
                     ->where('campaign_id', $campaign_id)
                     ->where('status', 'ACTIVE')
                     ->order_by('created_at', 'DESC')
                     ->limit(1)
                     ->get()
                     ->row();
    
    if ($link) {
        // Konversi komisi jika perlu
        $commission = floatval($link->commission_rate);
        if ($commission > 100) {
            $commission = $commission / 100;
        }
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'has_link' => true,
            'link' => $link->affiliate_link,
            'commission_rate' => $commission,
            'open_commission_rate' => floatval($link->open_commission_rate),
            'created_by' => $link->created_by_name,
            'created_at' => $link->created_at
        ]));
    } else {
        return $this->output->set_output(json_encode([
            'success' => true,
            'has_link' => false,
            'message' => 'Link afiliasi belum tersedia. Silakan minta BA (BD) untuk generate link terlebih dahulu.'
        ]));
    }
}

// ========== SAVE BD AFFILIATE LINK TO CREATOR ==========
public function save_bd_affiliate_link() {
    $this->output->set_content_type('application/json');
    
    $creator_id = $this->input->post('creator_id');
    $campaign_id = $this->input->post('campaign_id');
    $product_id = $this->input->post('product_id');
    $product_name = $this->input->post('product_name');
    $affiliate_link = $this->input->post('affiliate_link');
    $commission_rate = $this->input->post('commission_rate');
    
    if (!$creator_id || !$campaign_id || !$product_id || !$affiliate_link) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Missing required data'
        ]));
    }
    
    $creator = $this->Creator_model->get_creator_by_id($creator_id);
    
    // Cek apakah sudah ada link untuk kombinasi ini
    $existing = $this->db->where('creator_id', $creator_id)
                         ->where('campaign_id', $campaign_id)
                         ->where('product_id', $product_id)
                         ->get('affiliate_creator_links')
                         ->row();
    
    $link_data = [
        'creator_id' => $creator_id,
        'creator_username' => $creator->username,
        'campaign_id' => $campaign_id,
        'product_id' => $product_id,
        'product_name' => $product_name,
        'affiliate_link' => $affiliate_link,
        'commission_rate' => $commission_rate,
        'shared_date' => date('Y-m-d H:i:s'),
        'status' => 'ACTIVE',
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    if ($existing) {
        $this->db->where('id', $existing->id)->update('affiliate_creator_links', $link_data);
    } else {
        $link_data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('affiliate_creator_links', $link_data);
    }
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'message' => 'Link saved successfully'
    ]));
}

// Helper function untuk save link ke database
private function save_affiliate_link_to_db($creator_id, $creator_username, $campaign_id, $product_id, $product_name, $affiliate_link, $commission_rate) {
    $existing = $this->db->where('creator_id', $creator_id)
                         ->where('campaign_id', $campaign_id)
                         ->where('product_id', $product_id)
                         ->get('affiliate_creator_links')
                         ->row();
    
    $link_data = [
        'creator_id' => $creator_id,
        'creator_username' => $creator_username,
        'campaign_id' => $campaign_id,
        'product_id' => $product_id,
        'product_name' => $product_name,
        'affiliate_link' => $affiliate_link,
        'commission_rate' => $commission_rate,
        'shared_date' => date('Y-m-d H:i:s'),
        'status' => 'ACTIVE',
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    if ($existing) {
        $this->db->where('id', $existing->id)->update('affiliate_creator_links', $link_data);
    } else {
        $link_data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('affiliate_creator_links', $link_data);
    }
}

// ========== EXPORT PERFORMANCE DATA (JSON/CSV) ==========
public function export_performance() {
    $this->output->set_content_type('application/json');
    
    $user_id = $this->session->userdata('user_id');
    $type = $this->input->get('type');
    $start_date = $this->input->get('start_date') ?: date('Y-m-d', strtotime('-30 days'));
    $end_date = $this->input->get('end_date') ?: date('Y-m-d');
    
    $creators = $this->Creator_model->get_creators_by_is($user_id);
    $creator_usernames = array_column($creators, 'username');
    
    if (empty($creator_usernames)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'No creators found'
        ]));
    }
    
    $result = [];
    
    switch ($type) {
        case 'campaigns':
            $result = $this->db->select('
                    o.campaign_id,
                    c.campaign_name,
                    COUNT(DISTINCT o.order_id) as total_orders,
                    SUM(o.gmv) as total_gmv,
                    SUM(o.estimated_commission) as total_commission,
                    COUNT(DISTINCT o.creator_username) as total_creators
                ')
                ->from('affiliate_orders o')
                ->join('affiliate_campaigns c', 'c.campaign_id = o.campaign_id', 'left')
                ->where_in('o.creator_username', $creator_usernames)
                ->where('o.order_date_local >=', $start_date)
                ->where('o.order_date_local <=', $end_date)
                ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
                ->group_by('o.campaign_id')
                ->order_by('total_gmv', 'DESC')
                ->get()
                ->result();
            break;
            
        case 'creators':
            $result = $this->db->select('
                    o.creator_username,
                    COUNT(DISTINCT o.order_id) as total_orders,
                    SUM(o.gmv) as total_gmv,
                    SUM(o.estimated_commission) as total_commission,
                    COUNT(DISTINCT o.campaign_id) as total_campaigns
                ')
                ->from('affiliate_orders o')
                ->where_in('o.creator_username', $creator_usernames)
                ->where('o.order_date_local >=', $start_date)
                ->where('o.order_date_local <=', $end_date)
                ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
                ->group_by('o.creator_username')
                ->order_by('total_gmv', 'DESC')
                ->get()
                ->result();
            break;
            
        case 'products':
            $result = $this->db->select('
                    o.product_id,
                    o.product_name,
                    COUNT(DISTINCT o.order_id) as total_orders,
                    SUM(o.gmv) as total_gmv,
                    SUM(o.estimated_commission) as total_commission,
                    COUNT(DISTINCT o.creator_username) as total_creators
                ')
                ->from('affiliate_orders o')
                ->where_in('o.creator_username', $creator_usernames)
                ->where('o.order_date_local >=', $start_date)
                ->where('o.order_date_local <=', $end_date)
                ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
                ->group_by('o.product_id')
                ->order_by('total_gmv', 'DESC')
                ->limit(50)
                ->get()
                ->result();
            break;
            
        default:
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Invalid export type'
            ]));
    }
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'data' => $result,
        'count' => count($result)
    ]));
}
public function check_creator_exists() {
    $this->output->set_content_type('application/json');
    
    $username = $this->input->post('username');
    
    if (empty($username)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'exists' => false
        ]));
    }
    
    // 🔥 JOIN dengan tabel users untuk ambil data CA
    $creator = $this->db->select('
            c.id, 
            c.username, 
            c.full_name, 
            c.phone, 
            c.email, 
            c.category, 
            c.status, 
            c.created_at, 
            c.source,
            c.is_id,
            u.username as ca_username,
            u.full_name as ca_full_name,
            u.role as ca_role
        ')
        ->from('creators c')
        ->join('users u', 'c.is_id = u.id', 'left', false) // 🔥 LEFT JOIN
        ->where('c.username', $username)
        ->get()
        ->row();
    
    // 🔥 DEBUG - log hasil query
    log_message('debug', 'check_creator_exists result: ' . json_encode($creator));
    
    if ($creator) {
        // 🔥 Format CA name dengan benar
        $caName = '-';
        $caUsername = '-';
        
        // 🔥 CEK APAKAH is_id ADA DAN USER DITEMUKAN
        if (!empty($creator->is_id) && $creator->is_id > 0) {
            // 🔥 PAKAI DATA DARI JOIN
            if (!empty($creator->ca_full_name) && $creator->ca_full_name != '') {
                $caName = $creator->ca_full_name;
            } elseif (!empty($creator->ca_username) && $creator->ca_username != '') {
                $caName = $creator->ca_username;
            }
            
            if (!empty($creator->ca_username) && $creator->ca_username != '') {
                $caUsername = $creator->ca_username;
            }
        }
        
        $result = [
            'success' => true,
            'exists' => true,
            'creator' => [
                'id' => $creator->id,
                'username' => $creator->username,
                'full_name' => $creator->full_name,
                'phone' => $creator->phone,
                'email' => $creator->email,
                'category' => $creator->category,
                'status' => $creator->status,
                'created_at' => $creator->created_at,
                'source' => $creator->source,
                'is_id' => $creator->is_id,
                'ca_name' => $caName,
                'ca_username' => $caUsername,
                // 🔥 DEBUG - kirim raw data juga
                '_raw_ca_full_name' => $creator->ca_full_name,
                '_raw_ca_username' => $creator->ca_username,
                '_raw_is_id' => $creator->is_id
            ]
        ];
        
        log_message('debug', 'check_creator_exists response: ' . json_encode($result));
        
        return $this->output->set_output(json_encode($result));
    }
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'exists' => false
    ]));
}
// ========== GET PERFORMANCE DATA FOR CHART (AJAX) ==========
public function get_performance_chart_data() {
    $this->output->set_content_type('application/json');
    
    $user_id = $this->session->userdata('user_id');
    $daterange = $this->input->get('daterange') ?: '30days';
    
    $creators = $this->Creator_model->get_creators_by_is($user_id);
    $creator_usernames = array_column($creators, 'username');
    
    if (empty($creator_usernames)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'data' => []
        ]));
    }
    
    // Tentukan range tanggal berdasarkan parameter
    switch ($daterange) {
        case 'today':
            $start_date = date('Y-m-d');
            $end_date = date('Y-m-d');
            break;
        case 'week':
            $start_date = date('Y-m-d', strtotime('-6 days'));
            $end_date = date('Y-m-d');
            break;
        case 'month':
            $start_date = date('Y-m-d', strtotime('-29 days'));
            $end_date = date('Y-m-d');
            break;
        default:
            $start_date = date('Y-m-d', strtotime('-29 days'));
            $end_date = date('Y-m-d');
    }
    
    $daily_data = $this->db->select('
            order_date_local as date,
            SUM(gmv) as daily_gmv,
            COUNT(DISTINCT order_id) as daily_orders
        ')
        ->from('affiliate_orders')
        ->where_in('creator_username', $creator_usernames)
        ->where('order_date_local >=', $start_date)
        ->where('order_date_local <=', $end_date)
        ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
        ->group_by('order_date_local')
        ->order_by('date', 'ASC')
        ->get()
        ->result();
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'data' => $daily_data,
        'daterange' => $daterange
    ]));
}



// ========== IMPORT CREATORS FROM EXCEL ==========

/**
 * Show import modal and process import
 */
public function import_creators() {
    $this->output->set_content_type('application/json');
    
    $user_id = $this->session->userdata('user_id');
    $action = $this->input->post('action');
    
    if ($action == 'preview') {
        // Handle preview file
        return $this->preview_import_file();
    } elseif ($action == 'process') {
        // Handle process import
        return $this->process_import_creators();
    }
    
    return $this->output->set_output(json_encode([
        'success' => false,
        'message' => 'Invalid action'
    ]));
}

/**
 * Preview data from uploaded file
 */
private function preview_import_file() {
    $this->output->set_content_type('application/json');
    
    if (empty($_FILES['import_file']['name'])) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'File tidak ditemukan'
        ]));
    }
    
    $brand_id = $this->input->post('brand_id');
    if (empty($brand_id)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Pilih brand terlebih dahulu'
        ]));
    }
    
    // Ambil info brand
    $brand = $this->db->select('id, name, shop_name')->where('id', $brand_id)->get('brands')->row();
    if (!$brand) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Brand tidak ditemukan'
        ]));
    }
    
    $file = $_FILES['import_file']['tmp_name'];
    $filename = $_FILES['import_file']['name'];
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    // Validasi ekstensi
    if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Format file tidak support. Gunakan .xlsx, .xls, atau .csv'
        ]));
    }
    
    // Ambil mapping kolom dari request (dengan default)
    $col_mapping = [
        'username' => intval($this->input->post('col_username') !== null ? $this->input->post('col_username') : 1),
        'full_name' => intval($this->input->post('col_fullname') !== null ? $this->input->post('col_fullname') : 0),
        'category' => intval($this->input->post('col_category') !== null ? $this->input->post('col_category') : 3),
        'gmv' => intval($this->input->post('col_gmv') !== null ? $this->input->post('col_gmv') : 9),
        'followers' => intval($this->input->post('col_followers') !== null ? $this->input->post('col_followers') : 7),
        'email' => intval($this->input->post('col_email') !== null ? $this->input->post('col_email') : 13),
        'phone' => intval($this->input->post('col_phone') !== null ? $this->input->post('col_phone') : 14),
        'total_products' => intval($this->input->post('col_products') !== null ? $this->input->post('col_products') : 12),
        'total_sales' => intval($this->input->post('col_sales') !== null ? $this->input->post('col_sales') : 8)
    ];
    
    try {
        // Baca file CSV dengan delimiter yang benar
        $rows = $this->read_csv_with_detected_delimiter($file);
        
        if (empty($rows) || count($rows) < 2) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'File tidak memiliki data'
            ]));
        }
        
        // Ambil header dan bersihkan BOM
        $headers = $rows[0];
        if (!empty($headers[0])) {
            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
            $headers[0] = str_replace('﻿', '', $headers[0]);
        }
        
        // ========== DETEKSI TIPE FILE ==========
        $is_fastmoss = false;
        $is_kalodata = false;
        
        // Cek FastMoss: header contains "达人昵称" atau "达人ID"
        if (isset($headers[0]) && (strpos($headers[0], '达人昵称') !== false || $headers[0] == '达人昵称')) {
            $is_fastmoss = true;
            log_message('debug', 'Detected FastMoss file format');
        } elseif (isset($headers[1]) && $headers[1] == '达人ID') {
            $is_fastmoss = true;
            log_message('debug', 'Detected FastMoss file format by达人ID column');
        }
        // Cek Kalodata: header contains "Handle" atau "Revenue(Rp)" atau "Nickname" atau "Date Range"
        elseif ((isset($headers[2]) && $headers[2] == 'Handle') || 
                (isset($headers[4]) && strpos($headers[4], 'Revenue') !== false) ||
                (isset($headers[1]) && $headers[1] == 'Nickname') ||
                (isset($headers[0]) && strpos($headers[0], 'Date Range') !== false)) {
            $is_kalodata = true;
            log_message('debug', 'Detected Kalodata file format');
        }
        // Fallback: jika kolom cukup banyak, asumsikan FastMoss
        elseif (!$is_fastmoss && !$is_kalodata && count($headers) >= 10) {
            $is_fastmoss = true;
            log_message('debug', 'Assuming FastMoss format by column count');
        }
        
        // ========== SET DEFAULT MAPPING BERDASARKAN TIPE FILE ==========
        if ($this->input->post('col_username') === null) {
    if ($is_fastmoss) {
        // FastMoss: kolom 9 adalah GMV ([28天]带货销售额)
        $col_mapping = [
            'username' => 1,        // 达人ID
            'full_name' => 0,       // 达人昵称
            'category' => 3,        // 达人分类 (美妆等)
            'gmv' => 9,             // 🔥 [28天]带货销售额 - GMV
            'followers' => 7,       // 粉丝数
            'email' => 13,          // 达人邮箱
            'phone' => 14,          // 达人其他联系方式
            'total_products' => 12, // 此店铺商品数
            'total_sales' => 8      // [28天]带货销量 - Total Sales
        ];
    } elseif ($is_kalodata) {
        // Kalodata: kolom 4 adalah Revenue(Rp)
        $col_mapping = [
            'username' => 2,        // Handle
            'full_name' => 1,       // Nickname
            'category' => -1,       // Tidak ada, auto-detect
            'gmv' => 4,             // 🔥 Revenue(Rp) - KOLOM 4
            'followers' => 3,       // Followers
            'email' => 18,          // Email
            'phone' => 23,          // whatsapp
            'total_products' => 9,  // ProductCount
            'total_sales' => 5      // Item Sold
        ];
    }
}

        
        $creators = [];
        $errors = [];
        $row_num = 0;
        
        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // Skip header
            if (empty(array_filter($row))) continue; // Skip empty rows
            
            $row_num++;
            
            // Bersihkan row dari kolom kosong di akhir
            while (count($row) > 0 && empty($row[count($row) - 1])) {
                array_pop($row);
            }
            
            // Parse row berdasarkan tipe file
            $creator = $this->parse_import_row($row, $col_mapping, $is_fastmoss, $is_kalodata);
            
            if (empty($creator['username'])) {
                $errors[] = "Baris {$row_num}: Username tidak boleh kosong";
                continue;
            }
            
            if (strlen($creator['username']) < 3) {
                $errors[] = "Baris {$row_num}: Username '{$creator['username']}' terlalu pendek";
                continue;
            }
            
            // Check if creator already exists in database
            $existing = $this->db->where('username', $creator['username'])->get('creators')->row();
            
            $creator['brand_id'] = $brand_id;
            $creator['brand_name'] = $brand->name;
            $creator['shop_name'] = $brand->shop_name ?: $brand->name;
            $creator['status'] = $existing ? 'EXISTS' : 'NEW';
            $creator['existing_status'] = $existing ? $existing->status : null;
            
            $creators[] = $creator;
        }
        
        // Limit preview to 20 rows
        $preview = array_slice($creators, 0, 20);
        
        // Prepare header preview for frontend
        $header_preview = array_slice($headers, 0, min(20, count($headers)));
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'brand_info' => [
                'id' => $brand->id,
                'name' => $brand->name,
                'shop_name' => $brand->shop_name
            ],
            'total_rows' => count($creators),
            'new_count' => count(array_filter($creators, function($c) { return $c['status'] == 'NEW'; })),
            'exists_count' => count(array_filter($creators, function($c) { return $c['status'] == 'EXISTS'; })),
            'errors' => $errors,
            'preview' => $preview,
            'headers' => $header_preview,
            'file_type' => $is_fastmoss ? 'fastmoss' : ($is_kalodata ? 'kalodata' : 'unknown'),
            'col_mapping' => $col_mapping,
            'detected_mapping' => [
                'username_col' => $col_mapping['username'],
                'fullname_col' => $col_mapping['full_name'],
                'category_col' => $col_mapping['category'],
                'gmv_col' => $col_mapping['gmv'],
                'followers_col' => $col_mapping['followers'],
                'email_col' => $col_mapping['email'],
                'phone_col' => $col_mapping['phone'],
                'products_col' => $col_mapping['total_products'],
                'sales_col' => $col_mapping['total_sales']
            ]
        ]));
        
    } catch (Exception $e) {
        log_message('error', 'Import preview error: ' . $e->getMessage());
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Error membaca file: ' . $e->getMessage()
        ]));
    }
    
    
}


/**
 * Parse single row from import file
 */
/**
 * Parse single row from import file (FastMoss atau Kalodata)
 */
private function parse_import_row($row, $col_mapping, $is_fastmoss, $is_kalodata) {
    $result = [
        'full_name' => '',
        'username' => '',
        'category' => 'Lifestyle',
        'phone' => null,
        'email' => null,
        'gmv_28days' => 0,
        'total_products' => 0,
        'total_sales' => 0,
        'followers' => 0
    ];
    
    // ========== KHUSUS FAST MOSS ==========
    // Header FastMoss (15 kolom):
    // 0:达人昵称, 1:达人ID, 2:达人分类(negara), 3:达人分类(kategori), 4:国家, 
    // 5:达人TikTok主页链接, 6:达人FastMoss详情页链接, 7:粉丝数, 
    // 8:[28天]带货销量 (TOTAL SALES - BUKAN GMV), 
    // 9:[28天]带货销售额 (GMV - YANG BENAR), 
    // 10:直播GPM, 11:视频GPM, 12:此店铺商品数, 13:达人邮箱, 14:达人其他联系方式
    if ($is_fastmoss) {
        // Username dari kolom 1 (达人ID)
        if (isset($row[1])) {
            $username = trim($row[1]);
            if (preg_match('/tiktok\.com\/@([a-zA-Z0-9_\.]+)/', $username, $matches)) {
                $username = $matches[1];
            }
            $result['username'] = ltrim($username, '@');
            $result['username'] = trim($result['username']);
        }
        
        // Full name dari kolom 0 (达人昵称)
        if (isset($row[0])) {
            $result['full_name'] = trim($row[0]);
            $result['full_name'] = preg_replace('/^\xEF\xBB\xBF/', '', $result['full_name']);
            $result['full_name'] = str_replace('﻿', '', $result['full_name']);
        }
        
        // Kategori dari kolom 3 (达人分类 yang berisi美妆, fashion, dll)
        if (isset($row[3])) {
            $category_raw = trim($row[3]);
            $result['category'] = $this->map_category_native($category_raw);
        }
        
        // 🔥 GMV dari kolom 9 ([28天]带货销售额) - INI YANG BENAR
        if (isset($row[8]) && !empty($row[8])) {
            $gmv_raw = $row[8];
            log_message('debug', "FastMoss GMV RAW (kolom 9 - [28天]带货销售额): '{$gmv_raw}'");
            
            // Handle format dengan "Rp" dan "万" (contoh: "Rp18.77万Rp50.77万")
            if (strpos($gmv_raw, 'Rp') !== false) {
                preg_match_all('/Rp([0-9,\.]+)/', $gmv_raw, $matches);
                if (!empty($matches[1])) {
                    $gmv_raw = str_replace(',', '', $matches[1][0]);
                } else {
                    $gmv_raw = '0';
                }
            }
            
            // Handle format dengan titik sebagai pemisah ribuan (contoh: "1.234.567")
            $gmv_raw = str_replace('.', '', $gmv_raw);
            
            // Handle format dengan koma sebagai desimal
            $gmv_raw = str_replace(',', '', $gmv_raw);
            
            // Hapus semua karakter non-numeric
            $result['gmv_28days'] = floatval(preg_replace('/[^0-9]/', '', $gmv_raw));
            log_message('debug', "FastMoss GMV CLEAN (kolom 9): {$result['gmv_28days']}");
        } else {
            log_message('debug', "FastMoss: Kolom 9 tidak tersedia atau kosong");
        }
        
        // Followers dari kolom 7 (粉丝数)
        if (isset($row[7])) {
            $result['followers'] = intval(preg_replace('/[^0-9]/', '', $row[7]));
        }
        
        // Email dari kolom 13 (达人邮箱)
        if (isset($row[13]) && !empty($row[13])) {
            $email = trim($row[13]);
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $result['email'] = $email;
            }
        }
        
        // Phone dari kolom 14 (达人其他联系方式)
        if (isset($row[14]) && !empty($row[14])) {
            $phone_raw = trim($row[14]);
            if (preg_match('/(\+?62[0-9\s\-]{8,15})/', $phone_raw, $phone_match)) {
                $result['phone'] = $phone_match[1];
            } elseif (preg_match('/(08[0-9]{8,12})/', $phone_raw, $phone_match)) {
                $result['phone'] = $phone_match[1];
            } else {
                $result['phone'] = substr($phone_raw, 0, 50);
            }
        }
        
        // Total Products dari kolom 12 (此店铺商品数)
        if (isset($row[12])) {
            $result['total_products'] = intval(preg_replace('/[^0-9]/', '', $row[12]));
        }
        
        // Total Sales dari kolom 8 ([28天]带货销量)
        if (isset($row[8])) {
            $result['total_sales'] = intval(preg_replace('/[^0-9]/', '', $row[8]));
        }
        
        return $result;
    }
    
    // ========== KHUSUS KALODATA ==========
    // Header Kalodata (24 kolom):
    // 0:Date Range, 1:Nickname, 2:Handle, 3:Followers, 4:Revenue(Rp), 5:Item Sold, 
    // 6:Avg. Unit Price, 7:Engagement Rate, 8:New Followers, 9:ProductCount, 
    // 10:LiveNum, 11:LiveGmv(Rp), 12:VideoNum, 13:VideoGmv(Rp), 14:Views, 
    // 15:CreatorDebutTime, 16:KalodataUrl, 17:TikTokUrl, 18:Email, 
    // 19:Facebook, 20:Instagram, 21:YouTube, 22:X(Twitter), 23:whatsapp
    if ($is_kalodata) {
        // Username dari kolom 2 (Handle)
        if (isset($row[2])) {
            $username = trim($row[2]);
            if (preg_match('/tiktok\.com\/@([a-zA-Z0-9_\.]+)/', $username, $matches)) {
                $username = $matches[1];
            }
            $result['username'] = ltrim($username, '@');
            $result['username'] = trim($result['username']);
        }
        
        // Full name dari kolom 1 (Nickname)
        if (isset($row[1])) {
            $result['full_name'] = trim($row[1]);
        }
        
        // Kategori: Kalodata tidak punya kolom kategori, auto-detect dari nickname
        if (!empty($result['full_name'])) {
            $result['category'] = $this->auto_detect_category_native($result['full_name']);
        } elseif (!empty($result['username'])) {
            $result['category'] = $this->auto_detect_category_native($result['username']);
        }
        
        // 🔥 GMV dari kolom 4 (Revenue(Rp)) - INI YANG BENAR UNTUK KALODATA
        if (isset($row[4]) && !empty($row[4])) {
            $gmv_raw = $row[4];
            log_message('debug', "Kalodata GMV RAW (kolom 4 - Revenue): '{$gmv_raw}'");
            
            // Konversi koma ke titik untuk desimal, lalu ambil bagian integer
            $gmv_raw = str_replace(',', '.', $gmv_raw);
            if (strpos($gmv_raw, '.') !== false) {
                $gmv_raw = explode('.', $gmv_raw)[0];
            }
            
            // Hapus karakter non-numeric
            $result['gmv_28days'] = floatval(preg_replace('/[^0-9]/', '', $gmv_raw));
            log_message('debug', "Kalodata GMV CLEAN (kolom 4): {$result['gmv_28days']}");
        } else {
            log_message('debug', "Kalodata: Kolom 4 tidak tersedia atau kosong");
        }
        
        // Followers dari kolom 3
        if (isset($row[3])) {
            $result['followers'] = intval(preg_replace('/[^0-9]/', '', $row[3]));
        }
        
        // Email dari kolom 18
        if (isset($row[18]) && !empty($row[18])) {
            $email = trim($row[18]);
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $result['email'] = $email;
            }
        }
        
        // WhatsApp dari kolom 23
        if (isset($row[23]) && !empty($row[23])) {
            $phone_raw = trim($row[23]);
            if (preg_match('/(\+?62[0-9\s\-]{8,15})/', $phone_raw, $phone_match)) {
                $result['phone'] = $phone_match[1];
            } elseif (preg_match('/(08[0-9]{8,12})/', $phone_raw, $phone_match)) {
                $result['phone'] = $phone_match[1];
            } else {
                $result['phone'] = substr($phone_raw, 0, 50);
            }
        }
        
        // Total Products dari kolom 9
        if (isset($row[9])) {
            $result['total_products'] = intval(preg_replace('/[^0-9]/', '', $row[9]));
        }
        
        // Total Sales (Item Sold) dari kolom 5
        if (isset($row[5])) {
            $result['total_sales'] = intval(preg_replace('/[^0-9]/', '', $row[5]));
        }
        
        return $result;
    }
    
    // ========== FALLBACK: menggunakan mapping kolom dari user ==========
    // Ambil username
    if ($col_mapping['username'] >= 0 && isset($row[$col_mapping['username']])) {
        $username = trim($row[$col_mapping['username']]);
        if (preg_match('/tiktok\.com\/@([a-zA-Z0-9_\.]+)/', $username, $matches)) {
            $username = $matches[1];
        }
        $result['username'] = ltrim($username, '@');
        $result['username'] = trim($result['username']);
    }
    
    // Ambil full name
    if ($col_mapping['full_name'] >= 0 && isset($row[$col_mapping['full_name']])) {
        $result['full_name'] = trim($row[$col_mapping['full_name']]);
        $result['full_name'] = preg_replace('/^\xEF\xBB\xBF/', '', $result['full_name']);
        $result['full_name'] = str_replace('﻿', '', $result['full_name']);
    }
    
    // Ambil kategori
    if ($col_mapping['category'] >= 0 && isset($row[$col_mapping['category']])) {
        $category_raw = trim($row[$col_mapping['category']]);
        $result['category'] = $this->map_category_native($category_raw);
    }
    
    // Ambil GMV dari mapping
    if ($col_mapping['gmv'] >= 0 && isset($row[$col_mapping['gmv']]) && !empty($row[$col_mapping['gmv']])) {
        $gmv_raw = $row[$col_mapping['gmv']];
        log_message('debug', "Fallback GMV RAW (kolom {$col_mapping['gmv']}): '{$gmv_raw}'");
        
        if (strpos($gmv_raw, 'Rp') !== false) {
            preg_match_all('/Rp([0-9,\.]+)/', $gmv_raw, $matches);
            if (!empty($matches[1])) {
                $gmv_raw = str_replace(',', '', $matches[1][0]);
            } else {
                $gmv_raw = '0';
            }
        }
        
        $gmv_raw = str_replace('.', '', $gmv_raw);
        $gmv_raw = str_replace(',', '', $gmv_raw);
        $result['gmv_28days'] = floatval(preg_replace('/[^0-9]/', '', $gmv_raw));
        log_message('debug', "Fallback GMV CLEAN: {$result['gmv_28days']}");
    }
    
    // Ambil followers
    if ($col_mapping['followers'] >= 0 && isset($row[$col_mapping['followers']])) {
        $result['followers'] = intval(preg_replace('/[^0-9]/', '', $row[$col_mapping['followers']]));
    }
    
    // Ambil email
    if ($col_mapping['email'] >= 0 && isset($row[$col_mapping['email']])) {
        $email = trim($row[$col_mapping['email']]);
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $result['email'] = $email;
        }
    }
    
    // Ambil phone
    if ($col_mapping['phone'] >= 0 && isset($row[$col_mapping['phone']])) {
        $phone_raw = trim($row[$col_mapping['phone']]);
        if (!empty($phone_raw)) {
            if (preg_match('/(\+?62[0-9\s\-]{8,15})/', $phone_raw, $phone_match)) {
                $result['phone'] = $phone_match[1];
            } elseif (preg_match('/(08[0-9]{8,12})/', $phone_raw, $phone_match)) {
                $result['phone'] = $phone_match[1];
            } else {
                $result['phone'] = substr($phone_raw, 0, 50);
            }
        }
    }
    
    // Ambil total products
    if ($col_mapping['total_products'] >= 0 && isset($row[$col_mapping['total_products']])) {
        $result['total_products'] = intval(preg_replace('/[^0-9]/', '', $row[$col_mapping['total_products']]));
    }
    
    // Ambil total sales
    if ($col_mapping['total_sales'] >= 0 && isset($row[$col_mapping['total_sales']])) {
        $result['total_sales'] = intval(preg_replace('/[^0-9]/', '', $row[$col_mapping['total_sales']]));
    }
    
    return $result;
}


/**
 * Baca CSV dengan auto-detect delimiter
 */
private function read_csv_with_detected_delimiter($file_path) {
    $data = [];
    $delimiters = [';', ',', "\t", '|'];
    $best_delimiter = ';';
    $best_row_count = 0;
    
    // Test setiap delimiter untuk menemukan yang terbaik
    foreach ($delimiters as $delim) {
        $handle = fopen($file_path, 'r');
        if ($handle === false) continue;
        
        $test_rows = 0;
        while (($row = fgetcsv($handle, 0, $delim)) !== false && $test_rows < 5) {
            if (count($row) > 1 && !empty(array_filter($row))) {
                $test_rows++;
            }
        }
        fclose($handle);
        
        if ($test_rows > $best_row_count) {
            $best_row_count = $test_rows;
            $best_delimiter = $delim;
        }
    }
    
    log_message('debug', 'Best delimiter: "' . $best_delimiter . '" with ' . $best_row_count . ' test rows');
    
    // Baca full file dengan delimiter terbaik
    $handle = fopen($file_path, 'r');
    if ($handle === false) {
        return [];
    }
    
    while (($row = fgetcsv($handle, 0, $best_delimiter)) !== false) {
        // Bersihkan setiap cell
        foreach ($row as &$cell) {
            $cell = trim($cell);
        }
        $data[] = $row;
    }
    fclose($handle);
    
    log_message('debug', 'Total rows read: ' . count($data));
    
    return $data;
}

/**
 * Parse row based on file type - NATIVE VERSION
 */
private function parse_creator_row_native($row, $is_fastmoss, $is_kalodata) {
    // Untuk file FastMoss dengan delimiter ;
    if ($is_fastmoss || count($row) >= 7) {
        // FastMoss format: [昵称, username, 分类, TikTok链接, FastMoss链接, GMV, 商品数]
        $username = $row[1] ?? '';
        
        // Extract username dari URL jika perlu
        if (preg_match('/tiktok\.com\/@([a-zA-Z0-9_\.]+)/', $username, $matches)) {
            $username = $matches[1];
        }
        
        // Bersihkan username dari karakter aneh
        $username = trim($username);
        $username = str_replace('@', '', $username);
        
        // Ambil full name dari kolom 0
        $full_name = $row[0] ?? '';
        $full_name = preg_replace('/^\xEF\xBB\xBF/', '', $full_name);
        $full_name = str_replace('﻿', '', $full_name);
        
        // Mapping kategori dari Chinese ke English
        $category_raw = $row[2] ?? '';
        $category = $this->map_category_native($category_raw);
        
        return [
            'full_name' => trim($full_name),
            'username' => $username,
            'category' => $category,
            'tiktok_url' => $row[3] ?? '',
            'gmv_28days' => isset($row[5]) ? floatval(preg_replace('/[^0-9]/', '', $row[5])) : 0,
            'total_products' => isset($row[6]) ? intval($row[6]) : 0
        ];
    }
    
    // Fallback untuk Kalodata
    if ($is_kalodata && count($row) >= 3) {
        $username = $row[2] ?? '';
        if (preg_match('/tiktok\.com\/@([a-zA-Z0-9_\.]+)/', $username, $matches)) {
            $username = $matches[1];
        }
        $username = trim($username);
        $username = str_replace('@', '', $username);
        
        return [
            'full_name' => $row[1] ?? '',
            'username' => $username,
            'category' => $this->auto_detect_category_native($row[1] ?? $username),
            'tiktok_url' => $row[13] ?? '',
            'followers' => isset($row[3]) ? intval(preg_replace('/[^0-9]/', '', $row[3])) : 0,
            'gmv_28days' => isset($row[4]) ? floatval(preg_replace('/[^0-9]/', '', $row[4])) : 0,
            'total_products' => isset($row[5]) ? intval($row[5]) : 0
        ];
    }
    
    // Default: coba cari username dari cell mana pun
    $username = '';
    $full_name = '';
    
    foreach ($row as $cell) {
        if (preg_match('/tiktok\.com\/@([a-zA-Z0-9_\.]+)/', $cell, $matches)) {
            $username = $matches[1];
            break;
        }
    }
    
    return [
        'full_name' => $full_name,
        'username' => $username,
        'category' => 'Lifestyle',
        'tiktok_url' => '',
        'gmv_28days' => 0,
        'total_products' => 0
    ];
}
/**
 * Map category from Chinese/Indonesian to English
 */
private function map_category_native($cat) {
    $cat = trim($cat);
    
    $cat_map = [
    // Beauty
    '美妆' => 'Beauty',
    '美妆个护' => 'Beauty',
    '化妆' => 'Beauty',
    '护肤' => 'Beauty',
    'Beauty' => 'Beauty',
    'Skincare' => 'Beauty',

    // Fashion
    '时尚' => 'Fashion',
    '服装' => 'Fashion',
    '穿搭' => 'Fashion',
    '鞋包' => 'Fashion',
    'Fashion' => 'Fashion',

    // Home & Living
    '家居' => 'Home & Living',
    '家居生活' => 'Home & Living',
    '家具' => 'Home & Living',
    'Home' => 'Home & Living',

    // Electronics & Tech
    '家电' => 'Electronics',
    '电器' => 'Electronics',
    'Electronics' => 'Electronics',
    '数码' => 'Tech',
    '科技' => 'Tech',
    'Tech' => 'Tech',
    'Digital' => 'Tech',

    // Food
    '食品' => 'Food',
    '美食' => 'Food',
    '零食' => 'Food',
    'Food' => 'Food',
    'Gourmet' => 'Food',

    // Baby & Kids
    '母婴' => 'Baby & Kids',
    '亲子' => 'Baby & Kids',
    'Baby' => 'Baby & Kids',
    'Kids' => 'Baby & Kids',

    // Lifestyle & Others
    '其他' => 'Lifestyle',
    '生活' => 'Lifestyle',
    'Lifestyle' => 'Lifestyle',
    'Other' => 'Lifestyle',

    // Travel, Sports, Gaming
    '旅游' => 'Travel',
    '旅行' => 'Travel',
    'Travel' => 'Travel',
    '运动' => 'Sports',
    '健身' => 'Sports',
    'Sports' => 'Sports',
    '游戏' => 'Gaming',
    'Gaming' => 'Gaming',
    
    // Penambahan Baru
    '宠物' => 'Pets',
    'Pets' => 'Pets',
    '汽车' => 'Automotive',
    'Automotive' => 'Automotive',
    '健康' => 'Health',
    'Health' => 'Health'
];
    
    return isset($cat_map[$cat]) ? $cat_map[$cat] : (ucfirst($cat) ?: 'Lifestyle');
}

/**
 * Auto detect category from nickname
 */
private function auto_detect_category_native($text) {
    $text_lower = strtolower($text);
    
    if (preg_match('/(beauty|makeup|skincare|cosmetic|lip|美妆|化妆|kecantikan|hanasui)/i', $text_lower)) {
        return 'Beauty';
    } elseif (preg_match('/(fashion|style|outfit|clothing|wear|时尚|服装|fashion|busana)/i', $text_lower)) {
        return 'Fashion';
    } elseif (preg_match('/(food|recipe|cooking|eat|美食|烹饪|makan|kuliner)/i', $text_lower)) {
        return 'Food';
    } elseif (preg_match('/(tech|gadget|phone|laptop|数码|科技|teknologi|gaming|game)/i', $text_lower)) {
        return 'Tech';
    } elseif (preg_match('/(travel|journey|旅游|wisata|liburan)/i', $text_lower)) {
        return 'Travel';
    } elseif (preg_match('/(sport|fitness|gym|workout|体育|健身|olahraga)/i', $text_lower)) {
        return 'Sports';
    } elseif (preg_match('/(baby|kids|children|母婴|anak|bayi)/i', $text_lower)) {
        return 'Baby & Kids';
    } elseif (preg_match('/(home|living|decor|家居|rumah|dekorasi)/i', $text_lower)) {
        return 'Home & Living';
    }
    
    return 'Lifestyle';
}
/**
 * Parse row based on file type
 */
private function parse_creator_row($row, $is_fastmoss, $is_kalodata) {
    if ($is_fastmoss) {
        // FastMoss format: [昵称, username, 分类, TikTok链接, FastMoss链接, GMV, 商品数]
        return [
            'full_name' => $row[0] ?? '',
            'username' => $this->extract_username_from_url($row[1] ?? '') ?: ($row[1] ?? ''),
            'category' => $this->map_category($row[2] ?? ''),
            'tiktok_url' => $row[3] ?? '',
            'gmv_28days' => isset($row[5]) ? floatval($row[5]) : 0,
            'total_products' => isset($row[6]) ? intval($row[6]) : 0
        ];
    } elseif ($is_kalodata) {
        // Kalodata format: [Date Range, Nickname, Username, Followers, Revenue, ProductCount, ...]
        return [
            'full_name' => $row[1] ?? '',
            'username' => $this->extract_username_from_url($row[2] ?? '') ?: ($row[2] ?? ''),
            'category' => $this->auto_detect_category($row[1] ?? ''),
            'followers' => isset($row[3]) ? intval($row[3]) : 0,
            'gmv_28days' => isset($row[4]) ? floatval($row[4]) : 0,
            'total_products' => isset($row[5]) ? intval($row[5]) : 0,
            'tiktok_url' => $row[13] ?? ''
        ];
    }
    
    // Default: try to detect
    $username = '';
    $full_name = '';
    $category = '';
    
    foreach ($row as $cell) {
        if (preg_match('/@?([a-zA-Z0-9_\.]+)/', $cell, $matches)) {
            if (strlen($matches[1]) > 3 && !preg_match('/http/', $cell)) {
                $username = $matches[1];
            }
        }
        if (preg_match('/tiktok\.com\/@([a-zA-Z0-9_\.]+)/', $cell, $matches)) {
            $username = $matches[1];
        }
    }
    
    return [
        'full_name' => $full_name,
        'username' => $username,
        'category' => $category,
        'tiktok_url' => '',
        'gmv_28days' => 0,
        'total_products' => 0
    ];
}

/**
 * Extract username from TikTok URL
 */
private function extract_username_from_url($url) {
    if (preg_match('/tiktok\.com\/@([a-zA-Z0-9_\.]+)/', $url, $matches)) {
        return $matches[1];
    }
    return '';
}

/**
 * Map category from Chinese/Indonesian to English
 */
private function map_category($cat) {
    $cat_map = [
        '美妆' => 'Beauty',
        '美妆个护' => 'Beauty',
        '化妆' => 'Beauty',
        '时尚' => 'Fashion',
        '服装' => 'Fashion',
        '家居' => 'Home & Living',
        '家电' => 'Electronics',
        '数码' => 'Tech',
        '食品' => 'Food',
        '美食' => 'Food',
        '母婴' => 'Baby & Kids',
        '其他' => 'Lifestyle'
    ];
    
    return $cat_map[$cat] ?? ucfirst($cat);
}

/**
 * Auto detect category from nickname
 */
private function auto_detect_category($nickname) {
    $nickname_lower = strtolower($nickname);
    
    if (preg_match('/(beauty|makeup|skincare|cosmetic|lip|美妆)/i', $nickname_lower)) {
        return 'Beauty';
    } elseif (preg_match('/(fashion|style|outfit|clothing|wear|时尚|服装)/i', $nickname_lower)) {
        return 'Fashion';
    } elseif (preg_match('/(food|recipe|cooking|eat|美食|烹饪)/i', $nickname_lower)) {
        return 'Food';
    } elseif (preg_match('/(tech|gadget|phone|laptop|数码|科技)/i', $nickname_lower)) {
        return 'Tech';
    } elseif (preg_match('/(game|gaming|play|游戏)/i', $nickname_lower)) {
        return 'Gaming';
    } elseif (preg_match('/(travel|journey|旅游)/i', $nickname_lower)) {
        return 'Travel';
    } elseif (preg_match('/(sport|fitness|gym|workout|体育|健身)/i', $nickname_lower)) {
        return 'Sports';
    }
    
    return 'Lifestyle';
}

/**
 * Process import and save to database
 */
private function process_import_creators() {
    if (empty($_FILES['import_file']['name'])) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'File tidak ditemukan'
        ]));
    }
    
    $user_id = $this->session->userdata('user_id');
    $brand_id = $this->input->post('brand_id');
    $skip_existing = $this->input->post('skip_existing') === 'true';
    $file = $_FILES['import_file']['tmp_name'];
    
    if (empty($brand_id)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Pilih brand terlebih dahulu'
        ]));
    }
    
    // Ambil info brand
    $brand = $this->db->select('id, name, shop_name')->where('id', $brand_id)->get('brands')->row();
    if (!$brand) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Brand tidak ditemukan'
        ]));
    }
    
    // Ambil mapping kolom dari request
    $col_mapping = [
        'username' => intval($this->input->post('col_username') !== null ? $this->input->post('col_username') : 1),
        'full_name' => intval($this->input->post('col_fullname') !== null ? $this->input->post('col_fullname') : 0),
        'category' => intval($this->input->post('col_category') !== null ? $this->input->post('col_category') : 3),
        'gmv' => intval($this->input->post('col_gmv') !== null ? $this->input->post('col_gmv') : 9),
        'followers' => intval($this->input->post('col_followers') !== null ? $this->input->post('col_followers') : 7),
        'email' => intval($this->input->post('col_email') !== null ? $this->input->post('col_email') : 13),
        'phone' => intval($this->input->post('col_phone') !== null ? $this->input->post('col_phone') : 14),
        'total_products' => intval($this->input->post('col_products') !== null ? $this->input->post('col_products') : 12),
        'total_sales' => intval($this->input->post('col_sales') !== null ? $this->input->post('col_sales') : 8)
    ];
    
    try {
        $rows = $this->read_csv_with_detected_delimiter($file);
        
        if (empty($rows) || count($rows) < 2) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'File tidak memiliki data'
            ]));
        }
        
        // Deteksi tipe file
        $headers = $rows[0];
        if (!empty($headers[0])) {
            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
            $headers[0] = str_replace('﻿', '', $headers[0]);
        }
        
        $is_fastmoss = false;
        $is_kalodata = false;
        
        if (isset($headers[0]) && (strpos($headers[0], '达人昵称') !== false || $headers[0] == '达人昵称')) {
            $is_fastmoss = true;
        } elseif (isset($headers[1]) && $headers[1] == '达人ID') {
            $is_fastmoss = true;
        } elseif ((isset($headers[2]) && $headers[2] == 'Handle') || 
                  (isset($headers[4]) && strpos($headers[4], 'Revenue') !== false) ||
                  (isset($headers[1]) && $headers[1] == 'Nickname') ||
                  (isset($headers[0]) && strpos($headers[0], 'Date Range') !== false)) {
            $is_kalodata = true;
        }
        
        $inserted = 0;
        $skipped = 0;
        $failed = 0;
        $failed_rows = [];
        $now = date('Y-m-d H:i:s');
        
        foreach ($rows as $index => $row) {
            if ($index == 0) continue;
            if (empty(array_filter($row))) continue;
            
            // Bersihkan row
            while (count($row) > 0 && empty($row[count($row) - 1])) {
                array_pop($row);
            }
            
            // Parse row
            $creator = $this->parse_import_row($row, $col_mapping, $is_fastmoss, $is_kalodata);
            
            if (empty($creator['username'])) {
                $failed++;
                $failed_rows[] = "Baris " . ($index + 1) . ": Username tidak valid";
                continue;
            }
            
            // Cek existing untuk brand yang sama
            $existing = $this->db->where('username', $creator['username'])
                                 ->where('brand_id', $brand_id)
                                 ->get('creators')
                                 ->row();
            
            if ($existing) {
                if ($skip_existing) {
                    $skipped++;
                    continue;
                } else {
                    // UPDATE existing creator
                    $update_data = [
                        'full_name' => $creator['full_name'] ?: $existing->full_name,
                        'category' => $creator['category'] ?: $existing->category,
                        'phone' => $creator['phone'] ?: $existing->phone,
                        'email' => $creator['email'] ?: $existing->email,
                        'brand_id' => $brand_id,
                        'shop_name' => $brand->shop_name ?: $brand->name,
                        'source' => 'imported',
                        'imported_gmv' => $creator['gmv_28days'],
                        'imported_products_count' => $creator['total_products'],
                        'imported_followers' => $creator['followers'],
                        'imported_sales_count' => $creator['total_sales'],
                        'updated_at' => $now
                    ];
                    $this->db->where('id', $existing->id)->update('creators', $update_data);
                    $inserted++;
                    continue;
                }
            }
            
            // INSERT new creator
            $insert_data = [
                'username' => $creator['username'],
                'full_name' => $creator['full_name'],
                'category' => $creator['category'] ?: 'Lifestyle',
                'phone' => $creator['phone'],
                'email' => $creator['email'],
                'is_id' => $user_id,
                'brand_id' => $brand_id,
                'shop_name' => $brand->shop_name ?: $brand->name,
                'source' => 'imported',
                'status' => 'PENDING',
                'imported_gmv' => $creator['gmv_28days'],
                'imported_products_count' => $creator['total_products'],
                'imported_followers' => $creator['followers'],
                'imported_sales_count' => $creator['total_sales'],
                'created_at' => $now,
                'updated_at' => $now
            ];
            
            if ($this->db->insert('creators', $insert_data)) {
                $inserted++;
            } else {
                $failed++;
                $failed_rows[] = "Baris " . ($index + 1) . ": Gagal insert " . $creator['username'];
            }
        }
        
        // Log activity
        $this->load->model('User_log_model');
        $this->User_log_model->log(
            $user_id,
            $this->session->userdata('username'),
            'IS',
            'IMPORT_CREATORS',
            "Import creators to brand '{$brand->name}': {$inserted} inserted, {$skipped} skipped, {$failed} failed"
        );
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'inserted' => $inserted,
            'skipped' => $skipped,
            'failed' => $failed,
            'failed_rows' => $failed_rows,
            'brand_name' => $brand->name,
            'message' => "Import ke brand '{$brand->name}' selesai! {$inserted} creator baru ditambahkan, {$skipped} dilewati, {$failed} gagal."
        ]));
        
    } catch (Exception $e) {
        log_message('error', 'Import process error: ' . $e->getMessage());
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Error processing file: ' . $e->getMessage()
        ]));
    }
}

// ========== SEARCH CREATOR FROM TIKTOK API (IS SPECIFIC) ==========
public function search_creators_by_is() {
    $this->output->set_content_type('application/json');
    
    if (!$this->session->userdata('logged_in')) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Session expired'
        ]));
    }
    
    $keyword = $this->input->post('keyword');
    $page_token = $this->input->post('page_token');
    
    if (empty($keyword)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Keyword is required'
        ]));
    }
    
    try {
        // 🔥 PANGGIL API YANG SUDAH DIPERBAIKI
        $result = $this->jsm_api->search_creators_by_is($keyword, $page_token);
        
        if ($result['success'] && isset($result['data']['creators'])) {
            $creators = [];
            foreach ($result['data']['creators'] as $creator) {
                $avatar_url = $creator['avatar']['url'] ?? '';
                $gmv = floatval($creator['gmv']['amount'] ?? 0);
                
                $creators[] = [
                    'username' => $creator['username'],
                    'nickname' => $creator['nickname'],
                    'creator_open_id' => $creator['creator_open_id'],
                    'avatar_url' => $avatar_url,
                    'follower_count' => $creator['follower_count'],
                    'gmv' => $gmv,
                    'gmv_formatted' => 'Rp ' . number_format($gmv, 0, ',', '.'),
                    'avg_live_uv' => $creator['avg_ec_live_uv'],
                    'avg_video_views' => $creator['avg_ec_video_view_count'],
                    'category' => $this->map_category_from_id($creator['category_ids'][0] ?? ''),
                    'selection_region' => $creator['selection_region']
                ];
            }
            
            return $this->output->set_output(json_encode([
                'success' => true,
                'creators' => $creators,
                'next_page_token' => $result['data']['next_page_token'] ?? null,
                'total' => count($creators)
            ]));
        }
        
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $result['message'] ?? 'No creators found'
        ]));
        
    } catch (Exception $e) {
        log_message('error', 'search_creators_by_is error: ' . $e->getMessage());
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]));
    }
}

/**
 * Get creator content statistics (videos/links)
 */
public function get_creator_content_stats() {
    $this->output->set_content_type('application/json');
    
    $creator_id = $this->input->post('creator_id');
    
    if (!$creator_id) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Creator ID required'
        ]));
    }
    
    $creator = $this->Creator_model->get_creator_by_id($creator_id);
    if (!$creator) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Creator not found'
        ]));
    }
    
    // Ambil dari creator_content_statistics
    $contents = $this->db->select('
            content_type,
            cover_img_url,
            source_url,
            linked_tiktok_video,
            view_count,
            like_count,
            comment_count,
            paid_order_count,
            paid_amount,
            published_date
        ')
        ->from('creator_content_statistics')
        ->where('creator_username', $creator->username)
        ->order_by('published_date', 'DESC')
        ->limit(10)
        ->get()
        ->result();
    
    // Juga ambil dari affiliate_creator_links
    $links = $this->db->select('product_name, affiliate_link, commission_rate, total_gmv, total_orders')
        ->from('affiliate_creator_links')
        ->where('creator_id', $creator_id)
        ->where('status', 'ACTIVE')
        ->order_by('total_gmv', 'DESC')
        ->limit(10)
        ->get()
        ->result();
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'contents' => $contents,
        'links' => $links
    ]));
}

public function assign_link_with_handler() {
    $this->output->set_content_type('application/json');
    
    $user_id = $this->session->userdata('user_id');
    $username = $this->session->userdata('username');
    $full_name = $this->session->userdata('full_name');
    
    $creator_id = $this->input->post('creator_id');
    $campaign_id = $this->input->post('campaign_id');
    $products = json_decode($this->input->post('products'), true);
    
    if (!$creator_id || !$campaign_id || empty($products)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Missing required data'
        ]));
    }
    
    $creator = $this->Creator_model->get_creator_by_id($creator_id);
    $generated = [];
    $failed = [];
    
    foreach ($products as $product) {
        $product_id = $product['product_id'] ?? '';
        $product_name = $product['product_name'] ?? '';
        $commission = $product['commission_rate'] ?? 10;
        
        // Cek existing link
        $existing = $this->db->where('creator_id', $creator_id)
                             ->where('campaign_id', $campaign_id)
                             ->where('product_id', $product_id)
                             ->get('affiliate_creator_links')
                             ->row();
        
        $link_data = [
            'creator_id' => $creator_id,
            'creator_username' => $creator->username,
            'campaign_id' => $campaign_id,
            'product_id' => $product_id,
            'product_name' => $product_name,
            'affiliate_link' => $product['affiliate_link'] ?? 'pending_generate',
            'commission_rate' => $commission,
            'shared_date' => date('Y-m-d H:i:s'),
            'status' => 'ACTIVE',
            'handler_id' => $user_id,       // 🔥 PETUGAS YANG HANDLE
            'handler_name' => $full_name,   // 🔥 NAMA PETUGAS
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($existing) {
            $link_data['link_id'] = $existing->link_id ?: md5($creator->username . $campaign_id . $product_id);
            $this->db->where('id', $existing->id)->update('affiliate_creator_links', $link_data);
        } else {
            $link_data['link_id'] = md5($creator->username . $campaign_id . $product_id);
            $link_data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('affiliate_creator_links', $link_data);
        }
        
        $generated[] = [
            'product_name' => $product_name,
            'commission_rate' => $commission
        ];
    }
    
    // Update status creator ke LINK_SENT
    $this->db->where('id', $creator_id)->update('creators', [
        'status' => 'LINK_SENT',
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    // Log activity
    $this->load->model('User_log_model');
    $this->User_log_model->log(
        $user_id,
        $username,
        'IS',
        'ASSIGN_LINK',
        "Assign {$generated} links to @{$creator->username} in campaign {$campaign_id}"
    );
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'generated' => $generated,
        'failed' => $failed,
        'handler' => $full_name,
        'message' => count($generated) . ' links berhasil di-assign oleh ' . $full_name
    ]));
}

/**
 * Add product to existing monitoring creator (Task 4)
 */
public function add_product_to_monitoring() {
    $this->output->set_content_type('application/json');
    
    $user_id = $this->session->userdata('user_id');
    $full_name = $this->session->userdata('full_name');
    $username = $this->session->userdata('username');
    
    $creator_id = $this->input->post('creator_id');
    $campaign_id = $this->input->post('campaign_id');
    $products = json_decode($this->input->post('products'), true);
    
    if (!$creator_id || !$campaign_id || empty($products)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Missing required data'
        ]));
    }
    
    $creator = $this->Creator_model->get_creator_by_id($creator_id);
    $added = [];
    
    foreach ($products as $product) {
        $product_id = $product['product_id'] ?? '';
        $product_name = $product['product_name'] ?? '';
        $commission = $product['commission_rate'] ?? 10;
        
        $existing = $this->db->where('creator_id', $creator_id)
                             ->where('campaign_id', $campaign_id)
                             ->where('product_id', $product_id)
                             ->get('affiliate_creator_links')
                             ->row();
        
        $link_data = [
            'creator_id' => $creator_id,
            'creator_username' => $creator->username,
            'campaign_id' => $campaign_id,
            'product_id' => $product_id,
            'product_name' => $product_name,
            'affiliate_link' => $product['affiliate_link'] ?? 'pending_generate',
            'commission_rate' => $commission,
            'shared_date' => date('Y-m-d H:i:s'),
            'status' => 'ACTIVE',
            'handler_id' => $user_id,
            'handler_name' => $full_name,
            'source' => 'monitoring_add',  // 🔥 DARI TASK 4
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($existing) {
            $link_data['link_id'] = $existing->link_id ?: md5($creator->username . $campaign_id . $product_id);
            $this->db->where('id', $existing->id)->update('affiliate_creator_links', $link_data);
        } else {
            $link_data['link_id'] = md5($creator->username . $campaign_id . $product_id);
            $link_data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('affiliate_creator_links', $link_data);
        }
        
        $added[] = $product_name;
    }
    
    // Log activity
    $this->load->model('User_log_model');
    $this->User_log_model->log(
        $user_id,
        $username,
        'IS',
        'MONITORING_ADD_PRODUCT',
        "Add " . count($added) . " products to monitoring creator @{$creator->username}"
    );
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'added' => $added,
        'handler' => $full_name,
        'message' => count($added) . ' produk ditambahkan ke monitoring oleh ' . $full_name
    ]));
}

/**
 * Get creator monitoring detail (videos, links, performance)
 */
public function get_monitoring_detail() {
    $this->output->set_content_type('application/json');
    
    $creator_id = $this->input->post('creator_id');
    
    if (!$creator_id) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Creator ID required'
        ]));
    }
    
    $creator = $this->Creator_model->get_creator_by_id($creator_id);
    if (!$creator) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Creator not found'
        ]));
    }
    
    // Performance dari affiliate_orders (30 hari)
    $performance = $this->db->select('
            COALESCE(SUM(gmv), 0) as total_gmv,
            COUNT(DISTINCT order_id) as total_orders,
            COALESCE(SUM(estimated_commission), 0) as total_commission,
            MAX(order_time) as last_order,
            MIN(order_time) as first_order
        ')
        ->from('affiliate_orders')
        ->where('creator_username', $creator->username)
        ->where('order_time >=', date('Y-m-d', strtotime('-30 days')))
        ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
        ->get()
        ->row();
    
    // Links
    $links = $this->db->select('
            acl.*,
            cp.campaign_name,
            COALESCE(acl.total_gmv, 0) as link_gmv,
            COALESCE(acl.total_orders, 0) as link_orders
        ')
        ->from('affiliate_creator_links acl')
        ->join('affiliate_campaigns cp', 'cp.campaign_id = acl.campaign_id', 'left')
        ->where('acl.creator_id', $creator_id)
        ->where('acl.status', 'ACTIVE')
        ->order_by('acl.total_gmv', 'DESC')
        ->get()
        ->result();
    
    // Content videos
    $contents = $this->db->select('*')
        ->from('creator_content_statistics')
        ->where('creator_username', $creator->username)
        ->order_by('published_date', 'DESC')
        ->limit(10)
        ->get()
        ->result();
    
    // Daily performance
    $daily = $this->db->select('
            order_date_local as date,
            COUNT(DISTINCT order_id) as orders,
            SUM(gmv) as gmv,
            SUM(estimated_commission) as commission
        ')
        ->from('affiliate_orders')
        ->where('creator_username', $creator->username)
        ->where('order_date_local >=', date('Y-m-d', strtotime('-30 days')))
        ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
        ->group_by('order_date_local')
        ->order_by('date', 'ASC')
        ->get()
        ->result();
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'creator' => [
            'id' => $creator->id,
            'username' => $creator->username,
            'full_name' => $creator->full_name,
            'category' => $creator->category,
            'phone' => $creator->phone,
            'status' => $creator->status
        ],
        'performance' => [
            'total_gmv' => floatval($performance->total_gmv ?? 0),
            'total_orders' => intval($performance->total_orders ?? 0),
            'total_commission' => floatval($performance->total_commission ?? 0),
            'last_order' => $performance->last_order,
            'first_order' => $performance->first_order,
            'roas' => ($performance->total_commission > 0) ? round($performance->total_gmv / $performance->total_commission, 1) : 0
        ],
        'links' => $links,
        'contents' => $contents,
        'daily_performance' => $daily
    ]));
}

/**
 * Update creator data (edit scouting) - TERMASUK USERNAME
 */
public function update_creator_data() {
    $this->output->set_content_type('application/json');
    
    $creator_id = $this->input->post('creator_id');
    
    if (!$creator_id) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Creator ID required'
        ]));
    }
    
    $update_data = [];
    
    // Field yang boleh diupdate (TERMASUK USERNAME)
    $allowed_fields = ['username', 'full_name', 'phone', 'email', 'category', 'penerima', 'alamat', 'notes'];
    
    foreach ($allowed_fields as $field) {
        $value = $this->input->post($field);
        if ($value !== null) {
            $update_data[$field] = $value;
        }
    }
    
    if (empty($update_data)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'No data to update'
        ]));
    }
    
    // 🔥 Kalau username diubah, cek duplikat
    if (!empty($update_data['username'])) {
        $new_username = ltrim($update_data['username'], '@');
        $existing = $this->db->where('username', $new_username)
                             ->where('id !=', $creator_id)
                             ->get('creators')
                             ->row();
        if ($existing) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Username @' . $new_username . ' sudah digunakan oleh creator lain'
            ]));
        }
        $update_data['username'] = $new_username;
        
        // 🔥 Update juga username di affiliate_orders
        $old_creator = $this->db->where('id', $creator_id)->get('creators')->row();
        if ($old_creator && $old_creator->username !== $new_username) {
            $this->db->where('creator_username', $old_creator->username)
                     ->update('affiliate_orders', ['creator_username' => $new_username]);
            $this->db->where('creator_username', $old_creator->username)
                     ->update('affiliate_creator_links', ['creator_username' => $new_username]);
            $this->db->where('creator_username', $old_creator->username)
                     ->update('creator_content_statistics', ['creator_username' => $new_username]);
            $this->db->where('creator_username', $old_creator->username)
                     ->update('campaign_creator_performance', ['creator_username' => $new_username]);
        }
    }
    
    $update_data['updated_at'] = date('Y-m-d H:i:s');
    
    $this->db->where('id', $creator_id)->update('creators', $update_data);
    
    // Log activity
    $this->load->model('User_log_model');
    $this->User_log_model->log(
        $this->session->userdata('user_id'),
        $this->session->userdata('username'),
        'IS',
        'EDIT_CREATOR',
        "Edit creator ID: {$creator_id} - Fields: " . implode(', ', array_keys($update_data))
    );
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'message' => 'Data creator berhasil diupdate',
        'updated_fields' => array_keys($update_data)
    ]));
}
/**
 * Get creator performance detail for modal
 */
public function get_creator_performance_detail() {
    $this->output->set_content_type('application/json');
    
    $creator_open_id = $this->input->post('creator_open_id');
    
    if (empty($creator_open_id)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Creator Open ID required'
        ]));
    }
    
    try {
        $result = $this->jsm_api->get_marketplace_creator_performance($creator_open_id);
        
        // 🔥 DEBUG - simpan ke log
        log_message('debug', '=== PERFORMANCE API RAW ===');
        log_message('debug', 'Creator ID: ' . $creator_open_id);
        log_message('debug', 'Full response: ' . json_encode($result));
        
        if ($result['success']) {
            // 🔥 Cek GMV fields
            log_message('debug', 'gmv: ' . json_encode($result['data']['gmv'] ?? 'NOT SET'));
            log_message('debug', 'video_gmv: ' . json_encode($result['data']['video_gmv'] ?? 'NOT SET'));
            log_message('debug', 'live_gmv: ' . json_encode($result['data']['live_gmv'] ?? 'NOT SET'));
            log_message('debug', 'gpm: ' . json_encode($result['data']['gpm'] ?? 'NOT SET'));
            
            return $this->output->set_output(json_encode([
                'success' => true,
                'data' => $result['data']
            ]));
        }
        
        return $this->output->set_output(json_encode($result));
        
    } catch (Exception $e) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]));
    }
}
/**
 * Format number to short (e.g., 1.2K, 2.3M)
 */
private function format_number_short($num) {
    if ($num >= 1000000) {
        return round($num / 1000000, 1) . 'M';
    }
    if ($num >= 1000) {
        return round($num / 1000, 1) . 'K';
    }
    return (string)$num;
}

/**
 * Map category ID to category name
 */
private function map_category_from_id($category_id) {
    $category_map = [
        '600001' => 'Beauty',
        '600002' => 'Fashion',
        '600003' => 'Electronics',
        '600004' => 'Home & Living',
        '600005' => 'Sports',
        '600006' => 'Food & Beverage',
        '600007' => 'Health',
        '600008' => 'Toys & Hobbies',
        '600009' => 'Automotive',
        '600010' => 'Pets',
        '600011' => 'Baby & Kids',
        '600012' => 'Books & Media',
        '600013' => 'Gaming',
        '600014' => 'Travel',
        '600015' => 'Lifestyle'
    ];
    
    return $category_map[$category_id] ?? 'Lifestyle';
}
/**
 * Get available products for link assignment (dari bd_affiliate_links)
 */
public function get_available_products_for_link() {
    $this->output->set_content_type('application/json');
    
    $campaign_id = $this->input->post('campaign_id');
    $creator_category = $this->input->post('creator_category');
    
    if (empty($campaign_id)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Campaign ID required',
            'products' => []
        ]));
    }
    
    // 🔥 AMBIL DARI bd_affiliate_links (link yang sudah digenerate oleh BD)
    $this->db->select('
        bal.product_id,
        bal.product_name,
        bal.affiliate_link,
        bal.commission_rate,
        bal.open_commission_rate,
        bal.created_by_name as bd_creator,
        ap.price,
        ap.image_url,
        ap.shop_name,
        ap.category,
        ap.sales_count
    ');
    $this->db->from('bd_affiliate_links bal');
    $this->db->join('affiliate_products ap', 'bal.product_id = ap.product_id AND bal.campaign_id = ap.campaign_id', 'left');
    $this->db->where('bal.campaign_id', $campaign_id);
    $this->db->where('bal.status', 'ACTIVE');
    $this->db->order_by('bal.created_at', 'DESC');
    
    $products = $this->db->get()->result();
    
    // 🔥 FORMAT RESPONSE
    $formatted_products = [];
    foreach ($products as $p) {
        // Konversi komisi (jika dalam cents)
        $commission = floatval($p->commission_rate);
        if ($commission > 100) {
            $commission = $commission / 100;
        }
        
        $formatted_products[] = [
            'product_id' => $p->product_id,
            'product_name' => $p->product_name,
            'affiliate_link' => $p->affiliate_link,
            'commission_rate' => $commission,
            'open_commission_rate' => floatval($p->open_commission_rate),
            'price' => floatval($p->price ?? 0),
            'image_url' => $p->image_url ?? '',
            'shop_name' => $p->shop_name ?? '',
            'category' => $p->category ?? '',
            'sales_count' => intval($p->sales_count ?? 0),
            'bd_creator' => $p->bd_creator ?? 'BD'
        ];
    }
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'products' => $formatted_products,
        'total' => count($formatted_products),
        'campaign_id' => $campaign_id
    ]));
}

// ========== DEBUG: TEST SEARCH CREATORS API ==========
public function debug_search_creators() {
    // Set header untuk debugging
    header('Content-Type: text/html');
    
    $keyword = $this->input->get('keyword') ?: 'beauty';
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Debug Search Creators API</title>
        <style>
            body { font-family: monospace; background: #0f0f1a; color: #e2f0e8; padding: 20px; }
            .container { max-width: 1200px; margin: 0 auto; }
            .box { background: #1a1f2e; border-radius: 12px; padding: 16px; margin-bottom: 20px; border: 1px solid #2a3346; }
            .success { color: #4ade80; }
            .error { color: #ef4444; }
            .warning { color: #f59e0b; }
            pre { background: #0f1420; padding: 12px; border-radius: 8px; overflow-x: auto; font-size: 11px; }
            img { max-width: 50px; border-radius: 50%; }
            table { width: 100%; border-collapse: collapse; }
            th, td { padding: 10px; text-align: left; border-bottom: 1px solid #2a3346; }
            th { color: #8b5cf6; }
            .creator-card { display: flex; gap: 16px; padding: 12px; border-bottom: 1px solid #2a3346; align-items: center; }
            .creator-avatar { width: 50px; height: 50px; border-radius: 50%; overflow: hidden; background: #1e293b; }
            .creator-info { flex: 1; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>🔍 Debug Search Creators API</h1>
            
            <div class='box'>
                <form method='GET'>
                    <label>Keyword: </label>
                    <input type='text' name='keyword' value='".htmlspecialchars($keyword)."' style='padding: 8px 12px; background:#0f1420; border:1px solid #2a3346; color:#e2f0e8; border-radius:8px; width:200px;'>
                    <button type='submit' style='background:#4ade80; color:#0a0e17; padding:8px 16px; border:none; border-radius:8px; cursor:pointer;'>Search</button>
                </form>
            </div>";
    
    try {
        // 🔥 PANGGIL API VIA LIBRARY
        echo "<div class='box'>
            <h3>📡 1. Call API via Jsm_api->search_creators_by_is()</h3>";
        
        $result = $this->jsm_api->search_creators_by_is($keyword, null);
        
        echo "<pre>";
        echo "Success: " . ($result['success'] ? '✅ true' : '❌ false') . "\n";
        if (isset($result['message'])) {
            echo "Message: " . $result['message'] . "\n";
        }
        echo "</pre>";
        
        if ($result['success'] && isset($result['data']['creators'])) {
            $creators = $result['data']['creators'];
            echo "<h4>✅ Found " . count($creators) . " creators</h4>";
            
            echo "<table>
                <thead>
                    <tr>
                        <th>Avatar</th>
                        <th>Username</th>
                        <th>Nickname</th>
                        <th>Followers</th>
                        <th>GMV</th>
                        <th>Avatar URL</th>
                    </tr>
                </thead>
                <tbody>";
            
            foreach ($creators as $creator) {
                $avatar_url = '';
                if (isset($creator['avatar']['url'])) {
                    $avatar_url = $creator['avatar']['url'];
                }
                
                $avatar_preview = '';
                if (!empty($avatar_url)) {
                    // Test if image is accessible
                    $headers = @get_headers($avatar_url, 1);
                    $image_accessible = ($headers && strpos($headers[0], '200'));
                    $avatar_preview = "<img src='" . htmlspecialchars($avatar_url) . "' onerror='this.style.display=\"none\"'>";
                    if (!$image_accessible) {
                        $avatar_preview .= "<span class='warning'>⚠️ Image not accessible</span>";
                    }
                } else {
                    $avatar_preview = "<span class='warning'>No avatar URL</span>";
                }
                
                $gmv = '';
                if (isset($creator['gmv']['amount'])) {
                    $gmv = 'Rp ' . number_format($creator['gmv']['amount'], 0, ',', '.');
                } elseif (isset($creator['gmv_range']['minimum_amount'])) {
                    $gmv = 'Rp ' . number_format($creator['gmv_range']['minimum_amount'], 0, ',', '.');
                }
                
                echo "<tr>
                    <td>{$avatar_preview}</td>
                    <td>@" . htmlspecialchars($creator['username'] ?? '-') . "</td>
                    <td>" . htmlspecialchars($creator['nickname'] ?? '-') . "</td>
                    <td>" . number_format($creator['follower_count'] ?? 0) . "</td>
                    <td>{$gmv}</td>
                    <td style='font-size:10px; word-break:break-all;'>" . htmlspecialchars(substr($avatar_url, 0, 80)) . "...</td>
                 </tr>";
            }
            
            echo "</tbody></table>";
            
        } else {
            echo "<div class='error'>❌ No creators found or API error</div>";
        }
        
        echo "</div>";
        
        // 🔥 TAMPILKAN RAW RESPONSE DARI CURL
        echo "<div class='box'>
            <h3>📡 2. Raw API Response (Direct CURL)</h3>";
        
        $token = $this->jsm_api->get_valid_token();
        $search_key = base64_encode($keyword);
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://api.tiktokshop.com/affiliate/seller/202508/creators/search');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode([
            'search_key' => $search_key,
            'keyword' => $keyword,
            'page_size' => 5
        ]));
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
            'x-tts-access-token: ' . $token
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);
        
        echo "<p>HTTP Status Code: <strong>" . $httpCode . "</strong></p>";
        
        if ($curlError) {
            echo "<p class='error'>CURL Error: " . $curlError . "</p>";
        }
        
        $responseData = json_decode($response, true);
        
        echo "<details>
            <summary>📄 View Raw JSON Response</summary>
            <pre>" . htmlspecialchars(json_encode($responseData, JSON_PRETTY_PRINT)) . "</pre>
        </details>";
        
        // 🔥 CEK STRUKTUR AVATAR
        if ($responseData && isset($responseData['data']['creators'])) {
            echo "<h4>🖼️ Avatar URL Check:</h4>";
            foreach ($responseData['data']['creators'] as $idx => $creator) {
                $avatar_url = $creator['avatar']['url'] ?? 'NOT FOUND';
                $is_valid = false;
                
                if ($avatar_url && $avatar_url != 'NOT FOUND') {
                    $headers = @get_headers($avatar_url, 1);
                    $is_valid = ($headers && strpos($headers[0], '200'));
                }
                
                $status = $is_valid ? '✅ Valid' : '❌ Invalid / Not Accessible';
                $color = $is_valid ? '#4ade80' : '#ef4444';
                
                echo "<div style='margin-bottom: 8px;'>";
                echo "Creator " . ($idx+1) . ": ";
                echo "<span style='color:{$color}'>{$status}</span><br>";
                echo "<span style='font-size:11px; word-break:break-all;'>URL: " . htmlspecialchars($avatar_url) . "</span>";
                echo "</div>";
            }
        }
        
        echo "</div>";
        
        // 🔥 TEST IMAGE ACCESSIBILITY
        if (!empty($creators)) {
            echo "<div class='box'>
                <h3>🖼️ 3. Test Avatar URL Accessibility</h3>";
            
            foreach ($creators as $idx => $creator) {
                $avatar_url = $creator['avatar']['url'] ?? '';
                if (!empty($avatar_url)) {
                    $ch = curl_init($avatar_url);
                    curl_setopt($ch, CURLOPT_NOBODY, true);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                    curl_exec($ch);
                    $httpCodeImg = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    
                    $status = $httpCodeImg == 200 ? '✅ OK' : '❌ HTTP ' . $httpCodeImg;
                    $color = $httpCodeImg == 200 ? '#4ade80' : '#ef4444';
                    
                    echo "<div style='margin-bottom: 8px;'>";
                    echo "Creator " . ($idx+1) . " Avatar: <span style='color:{$color}'>{$status}</span><br>";
                    echo "<span style='font-size:11px;'>URL: " . htmlspecialchars($avatar_url) . "</span>";
                    echo "</div>";
                }
            }
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='box error'>";
        echo "<h3>❌ Error</h3>";
        echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
        echo "</div>";
    }
    
    echo "</div></body></html>";
}




public function debug_raw_api_response() {
    $creator_open_id = $this->input->get('open_id') ?: 'Jf_MhwAAAAAGJ7MdyGGSDCVTTa8g8YRT97b5xm_km-jsSH0GMf8Z8g';
    
    echo "<h3>🔍 Debug Raw API Response</h3>";
    echo "<p>Creator Open ID: <b>" . htmlspecialchars($creator_open_id) . "</b></p>";
    echo "<hr>";
    
    // 🔥 GUNAKAN METHOD PUBLIC
    $path = "/affiliate_seller/202508/marketplace_creators/{$creator_open_id}";
    $result = $this->jsm_api->debug_raw_request($path, [], 'GET', null);
    
    echo "<h4>📡 Full Response (Unparsed):</h4>";
    echo "<pre style='background:#1a1a2e; color:#e2e8f0; padding:16px; border-radius:8px; overflow-x:auto; font-size:12px; max-height:600px;'>";
    echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "</pre>";
    
    // 🔥 HANYA DATA CREATOR
    if ($result['success'] && isset($result['data']['creator'])) {
        $creator = $result['data']['creator'];
        
        echo "<h4>👤 Creator Object Only:</h4>";
        echo "<pre style='background:#1a1a2e; color:#e2e8f0; padding:16px; border-radius:8px; overflow-x:auto; font-size:12px; max-height:600px;'>";
        echo htmlspecialchars(json_encode($creator, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "</pre>";
        
        // 🔥 LIST SEMUA FIELD & TIPE DATA
        echo "<h4>📋 All Fields & Types (Red = 0/empty):</h4>";
        echo "<table style='width:100%; border-collapse:collapse; background:#1a1a2e; color:#e2e8f0;'>";
        echo "<tr style='background:#16213e;'><th style='padding:8px; text-align:left;'>Field</th><th style='padding:8px; text-align:left;'>Type</th><th style='padding:8px; text-align:left;'>Value</th></tr>";
        
        foreach ($creator as $key => $value) {
            $type = gettype($value);
            $display = '';
            
            if (is_array($value)) {
                $display = json_encode($value, JSON_UNESCAPED_UNICODE);
                if (strlen($display) > 150) $display = substr($display, 0, 150) . '...';
            } elseif (is_null($value)) {
                $display = 'NULL';
            } elseif ($value === '') {
                $display = '(empty string)';
            } elseif ($value === 0 || $value === '0') {
                $display = "'$value' (zero)";
            } else {
                $display = (string)$value;
                if (strlen($display) > 150) $display = substr($display, 0, 150) . '...';
            }
            
            $is_empty = ($value === 0 || $value === '0' || is_null($value) || $value === '' || (is_array($value) && empty($value)));
            $color = $is_empty ? '#ef4444' : '#4ade80';
            
            echo "<tr style='border-bottom:1px solid #2a3346;'>";
            echo "<td style='padding:8px;'><b>$key</b></td>";
            echo "<td style='padding:8px; color:#8b5cf6;'>$type</td>";
            echo "<td style='padding:8px; color:$color; word-break:break-all;'>" . htmlspecialchars($display) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<h4 style='color:#ef4444;'>❌ API Error: " . ($result['message'] ?? 'Unknown') . "</h4>";
    }
}

// ========== TARGET PLAN REQUEST ==========

/**
 * Show target plan request page
 */
public function target_plan() {
    $user_id = $this->session->userdata('user_id');
    $role = $this->session->userdata('role');
    $is_supervisor = ($user_id == 2 || $role == 'admin');
    
    $data = [
        'title' => 'Target Plan Request - Toopai IS',
        'active_menu' => 'target_plan',
        'is_supervisor' => $is_supervisor,
        'campaigns' => $this->Campaign_model->get_ongoing_campaigns()
    ];
    
    $this->load->view('templates/header', $data);
    $this->load->view('is/target_plan', $data);
    $this->load->view('templates/footer');
}

/**
 * Submit target plan request
 */
public function submit_target_request() {
    $this->output->set_content_type('application/json');
    
    $creator_id = $this->input->post('creator_id');
    $creator_username = $this->input->post('creator_username');
    $creator_name = $this->input->post('creator_name');
    $creator_phone = $this->input->post('creator_phone');
    $campaign_id = $this->input->post('campaign_id');
    $campaign_name = $this->input->post('campaign_name');
    $products = json_decode($this->input->post('products'), true);
    $current_commission = $this->input->post('current_commission');
    $requested_commission = $this->input->post('requested_commission');
    $reason = $this->input->post('reason');
    
    if (!$creator_id || !$campaign_id || empty($products) || !$requested_commission) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Missing required data'
        ]));
    }
    
    $user_id = $this->session->userdata('user_id');
    $username = $this->session->userdata('username');
    $full_name = $this->session->userdata('full_name');
    
    $request_code = 'TP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    
    $data = [
        'request_code' => $request_code,
        'creator_id' => $creator_id,
        'creator_username' => $creator_username,
        'creator_name' => $creator_name,
        'creator_phone' => $creator_phone,
        'campaign_id' => $campaign_id,
        'campaign_name' => $campaign_name,
        'products' => json_encode($products),
        'current_commission' => $current_commission,
        'requested_commission' => $requested_commission,
        'reason' => $reason,
        'status' => 'PENDING',
        'created_by' => $user_id,
        'created_by_name' => $full_name ?: $username,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $this->db->insert('target_plan_requests', $data);
    $request_id = $this->db->insert_id();
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'message' => 'Target plan request submitted successfully',
        'request_id' => $request_id,
        'request_code' => $request_code
    ]));
}

/**
 * Get target plan requests (for Leader IS)
 */
public function get_target_requests() {
    $this->output->set_content_type('application/json');
    
    $status = $this->input->post('status');
    $user_id = $this->session->userdata('user_id');
    $role = $this->session->userdata('role');
    $is_supervisor = ($user_id == 2 || $role == 'admin');
    
    $this->db->select('tpr.*, c.username, c.full_name as creator_full_name, c.phone as creator_phone')
             ->from('target_plan_requests tpr')
             ->join('creators c', 'tpr.creator_id = c.id', 'left');
    
    if (!$is_supervisor) {
        $this->db->where('tpr.created_by', $user_id);
    }
    
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
 * Approve target request (Leader IS)
 */
public function approve_target_request_is() {
    $this->output->set_content_type('application/json');
    
    $request_id = $this->input->post('request_id');
    
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
                 'status' => 'IS_APPROVED',
                 'is_approved' => 1,
                 'approved_by_is' => $full_name ?: $username,
                 'approved_at_is' => date('Y-m-d H:i:s'),
                 'updated_at' => date('Y-m-d H:i:s')
             ]);
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'message' => 'Request approved successfully'
    ]));
}

/**
 * Reject target request (Leader IS)
 */
public function reject_target_request_is() {
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
                 'status' => 'IS_REJECTED',
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

public function target_plan_dashboard() {
    $user_id = $this->session->userdata('user_id');
    $role = $this->session->userdata('role');
    $is_supervisor = ($user_id == 2 || $role == 'admin');
    
    $data = [
        'title' => 'Target Plan - Toopai IS',
        'active_menu' => 'target_plan',
        'is_supervisor' => $is_supervisor,
        'campaigns' => $this->Campaign_model->get_ongoing_campaigns()
    ];
    
    $this->load->view('templates/header', $data);
    $this->load->view('is/target_plan_dashboard', $data);
    $this->load->view('templates/footer');
}


/**
 * DEBUG: Tampilkan semua row/data dari API get_creator_performance_detail
 * URL: /is/debug_creator_performance?creator_open_id=Fmwn_gAAAAAGJ7MdyGGSDCVTTa8g8YRTwsNRaHsRGqtC6u8XkWYePg
 */
public function debug_creator_performance() {
    // Set header untuk debugging (tampilkan HTML)
    header('Content-Type: text/html');
    
    $creator_open_id = $this->input->get('creator_open_id');
    
    if (empty($creator_open_id)) {
        // Ambil sample creator open_id dari database jika ada
        $sample = $this->db->select('creator_open_id, username')
            ->where('creator_open_id IS NOT NULL')
            ->where('creator_open_id !=', '')
            ->limit(1)
            ->get('campaign_creator_performance')
            ->row();
        
        if ($sample) {
            $creator_open_id = $sample->creator_open_id;
            $sample_username = $sample->username;
        }
    }
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Debug Creator Performance API</title>
        <style>
            body { font-family: monospace; background: #0f0f1a; color: #e2f0e8; padding: 20px; margin: 0; }
            .container { max-width: 1400px; margin: 0 auto; }
            .box { background: #1a1f2e; border-radius: 12px; padding: 16px; margin-bottom: 20px; border: 1px solid #2a3346; overflow-x: auto; }
            .success { color: #4ade80; }
            .error { color: #ef4444; }
            .warning { color: #f59e0b; }
            .info { color: #8b5cf6; }
            pre { background: #0f1420; padding: 12px; border-radius: 8px; overflow-x: auto; font-size: 11px; white-space: pre-wrap; word-break: break-all; }
            table { width: 100%; border-collapse: collapse; font-size: 12px; }
            th, td { padding: 8px; text-align: left; border-bottom: 1px solid #2a3346; }
            th { color: #8b5cf6; }
            .field-key { color: #fbbf24; font-weight: 600; }
            .field-value { color: #4ade80; }
            .field-empty { color: #ef4444; }
            h3 { color: #4ade80; margin-top: 0; }
            .badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 10px; margin-left: 8px; }
            .badge-green { background: rgba(74,222,128,0.15); color: #4ade80; }
            .badge-purple { background: rgba(139,92,246,0.15); color: #8b5cf6; }
            .badge-orange { background: rgba(245,158,11,0.15); color: #f59e0b; }
            .two-columns { display: flex; gap: 16px; flex-wrap: wrap; }
            .two-columns > div { flex: 1; min-width: 280px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>🔍 Debug Creator Performance API</h1>
            <div class='box'>
                <form method='GET' style='display: flex; gap: 10px; align-items: center; flex-wrap: wrap;'>
                    <label>Creator Open ID:</label>
                    <input type='text' name='creator_open_id' value='".htmlspecialchars($creator_open_id)."' 
                           style='flex:1; padding:8px 12px; background:#0f1420; border:1px solid #2a3346; border-radius:8px; color:#e2f0e8;'>
                    <button type='submit' style='background:#4ade80; color:#0a0e17; padding:8px 20px; border:none; border-radius:8px; cursor:pointer;'>Debug</button>
                </form>
                " . (empty($creator_open_id) ? "<p class='warning'>⚠️ Masukkan Creator Open ID atau pastikan ada data di campaign_creator_performance</p>" : "") . "
            </div>";
    
    if (!empty($creator_open_id)) {
        echo "<div class='box'>
            <h3>📡 1. Raw API Response dari is/get_creator_performance_detail</h3>";
        
        try {
            // Panggil method yang sama dengan yang digunakan di frontend
            $result = $this->get_creator_performance_detail_raw($creator_open_id);
            
            echo "<pre style='max-height: 500px; overflow-y: auto;'>";
            echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            echo "</pre>";
            
            if ($result['success'] && isset($result['data'])) {
                $data = $result['data'];
                
                // ========== FIELD YANG AKAN DICEK (termasuk top_collaborated_brand_ids) ==========
                $fields_to_check = [
                    // Basic Info
                    'username' => 'Username',
                    'nickname' => 'Nickname',
                    'avatar_url' => 'Avatar URL',
                    'bio' => 'Bio',
                    'follower_count' => 'Followers',
                    'selection_region' => 'Region',
                    
                    // GMV Fields
                    'gmv' => 'GMV (USD)',
                    'gmv_currency' => 'GMV Currency',
                    'video_gmv' => 'Video GMV (USD)',
                    'live_gmv' => 'Live GMV (USD)',
                    'gpm' => 'GPM',
                    'video_gpm' => 'Video GPM',
                    'live_gpm' => 'Live GPM',
                    
                    // Sales & Collaboration
                    'units_sold' => 'Units Sold',
                    'brand_collaboration_count' => 'Brand Collaboration Count',
                    'promoted_product_num' => 'Promoted Products',
                    'top_collaborated_brand_ids' => '🔥 Top Collaborated Brand IDs',  // 🔥 TAMBAHKAN INI
                    
                    // Content
                    'ec_video_count' => 'Video Count',
                    'ec_live_count' => 'Live Count',
                    'avg_ec_video_play_count' => 'Avg Video Views',
                    'avg_ec_live_view_count' => 'Avg Live Views',
                    
                    // Engagement
                    'ec_video_engagement_rate' => 'Video Engagement Rate',
                    'ec_live_engagement_rate' => 'Live Engagement Rate',
                    'avg_commission_rate' => 'Avg Commission Rate',
                    'post_rate' => 'Post Rate',
                    'rating' => 'Rating',
                    'pps' => 'PPS',
                    
                    // Live Interaction
                    'avg_ec_live_like_count' => 'Avg Live Likes',
                    'avg_ec_live_comment_count' => 'Avg Live Comments',
                    'avg_ec_live_share_count' => 'Avg Live Shares',
                    
                    // Demographics
                    'follower_age' => 'Follower Age Distribution',
                    'follower_gender' => 'Follower Gender Distribution',
                    'follower_location' => 'Follower Location',
                    
                    // Distribution
                    'category_gmv_distribution' => 'Category GMV Distribution',
                    'content_gmv_distribution' => 'Content GMV Distribution'
                ];
                
                // ========== ANALISIS STRUKTUR DATA ==========
                echo "<h3>📊 2. Analisis Struktur Data</h3>";
                echo "<div style='overflow-x: auto;'>";
                echo "<table>";
                echo " hilab<th>Field</th><th>Type</th><th>Value / Sample</th><th>Status</th> </tr>";
                
                foreach ($fields_to_check as $field => $label) {
                    $exists = isset($data[$field]);
                    $value = $exists ? $data[$field] : null;
                    $type = gettype($value);
                    $display = '';
                    $status_class = '';
                    
                    if (!$exists) {
                        $display = '<span class="field-empty">❌ Not Set</span>';
                        $status_class = 'error';
                    } elseif (is_array($value)) {
                        if (empty($value)) {
                            $display = '<span class="field-empty">⚠️ Empty Array</span>';
                            $status_class = 'warning';
                        } else {
                            $display = '<span class="field-value">📦 Array(' . count($value) . ')</span>';
                            // Tampilkan preview untuk top_collaborated_brand_ids
                            if ($field == 'top_collaborated_brand_ids') {
                                $display .= '<div style="margin-top:4px; font-size:10px;">IDs: ' . htmlspecialchars(implode(', ', array_slice($value, 0, 5))) . '</div>';
                            }
                            $status_class = 'success';
                        }
                    } elseif (is_numeric($value) && $value == 0) {
                        $display = '<span class="field-empty">0 (Zero)</span>';
                        $status_class = 'warning';
                    } elseif (empty($value)) {
                        $display = '<span class="field-empty">❌ Empty</span>';
                        $status_class = 'error';
                    } else {
                        if (is_string($value) && strlen($value) > 100) {
                            $display = '<span class="field-value">' . htmlspecialchars(substr($value, 0, 100)) . '...</span>';
                        } else {
                            $display = '<span class="field-value">' . htmlspecialchars(json_encode($value, JSON_UNESCAPED_UNICODE)) . '</span>';
                        }
                        $status_class = 'success';
                    }
                    
                    // Special highlight untuk top_collaborated_brand_ids
                    $row_class = ($field == 'top_collaborated_brand_ids') ? 'style="background:rgba(139,92,246,0.15);"' : '';
                    
                    echo "<tr $row_class>";
                    echo "<td class='field-key'>" . htmlspecialchars($label) . "<br><span style='font-size:10px; color:#6b7280;'>$field</span></td>";
                    echo "<td style='color:#8b5cf6;'>$type</td>";
                    echo "<td>$display</td>";
                    echo "<td><span class='badge badge-$status_class'>" . ($status_class == 'success' ? '✅ Ada' : ($status_class == 'warning' ? '⚠️ Nol' : '❌ Kosong')) . "</span></td>";
                    echo "</tr>";
                }
                
                echo "</table>";
                echo "</div>";
                
                // ========== TOP COLLABORATED BRAND IDs DETAIL ==========
                if (isset($data['top_collaborated_brand_ids']) && !empty($data['top_collaborated_brand_ids'])) {
                    echo "<div class='box' style='border-color: #8b5cf6;'>";
                    echo "<h3>🏪 3. Top Collaborated Brand IDs <span class='badge badge-purple'>DETAIL</span></h3>";
                    echo "<div class='two-columns'>";
                    
                    $brand_ids = $data['top_collaborated_brand_ids'];
                    echo "<div>";
                    echo "<h4>📋 List Brand IDs:</h4>";
                    echo "<ul>";
                    foreach ($brand_ids as $idx => $bid) {
                        echo "<li>#" . ($idx+1) . ": <code style='color:#4ade80;'>" . htmlspecialchars($bid) . "</code></li>";
                    }
                    echo "</ul>";
                    echo "</div>";
                    
                    // Coba cek info brand dari database
                    echo "<div>";
                    echo "<h4>🔍 Info Brand dari Database (jika ada):</h4>";
                    echo "<table style='font-size:11px;'>";
                    echo "<tr><th>Brand ID</th><th>Nama Brand</th><th>Shop Name</th><th>Status</th></tr>";
                    
                    foreach ($brand_ids as $bid) {
                        $brand = $this->db->select('id, name, shop_name, status')
                            ->where('seller_id', $bid)
                            ->or_where('id', $bid)
                            ->limit(1)
                            ->get('brands')
                            ->row();
                        
                        if ($brand) {
                            echo "<tr>";
                            echo "<td><code>" . htmlspecialchars($bid) . "</code></td>";
                            echo "<td>" . htmlspecialchars($brand->name) . "</td>";
                            echo "<td>" . htmlspecialchars($brand->shop_name ?? '-') . "</td>";
                            echo "<td><span class='badge badge-green'>" . htmlspecialchars($brand->status) . "</span></td>";
                            echo "</tr>";
                        } else {
                            echo "<tr>";
                            echo "<td><code>" . htmlspecialchars($bid) . "</code></td>";
                            echo "<td colspan='3' class='warning'>❌ Brand belum terdaftar di database</td>";
                            echo "</tr>";
                        }
                    }
                    echo "</table>";
                    echo "</div>";
                    
                    echo "</div>";
                    echo "</div>";
                }
                
                // ========== SEMUA FIELD YANG TERSEDIA ==========
                echo "<h3>📋 4. Semua Field yang Tersedia (Keys)</h3>";
                echo "<div class='box'>";
                $all_keys = array_keys($data);
                sort($all_keys);
                echo "<p><strong>Total fields: " . count($all_keys) . "</strong></p>";
                echo "<div style='display: flex; flex-wrap: wrap; gap: 8px;'>";
                foreach ($all_keys as $key) {
                    $is_top_brand = ($key == 'top_collaborated_brand_ids');
                    $style = $is_top_brand ? 'background:rgba(139,92,246,0.2); border:1px solid #8b5cf6;' : 'background:#1e293b;';
                    echo "<code style='padding:4px 10px; border-radius:16px; font-size:11px; $style'>" . htmlspecialchars($key) . "</code>";
                }
                echo "</div>";
                echo "</div>";
                
                // ========== RAW DATA PER FIELD ==========
                echo "<h3>🔍 5. Raw Data per Field</h3>";
                echo "<div class='box' style='max-height: 600px; overflow-y: auto;'>";
                echo "<table>";
                echo "<tr><th>Field</th><th>Raw Data</th></tr>";
                foreach ($data as $key => $value) {
                    $is_top_brand = ($key == 'top_collaborated_brand_ids');
                    $row_style = $is_top_brand ? 'style="background:rgba(139,92,246,0.1);"' : '';
                    echo "<tr $row_style>";
                    echo "<td class='field-key' style='vertical-align: top;'>" . htmlspecialchars($key) . ($is_top_brand ? " <span class='badge badge-purple'>🔥</span>" : "") . "</td>";
                    echo "<td><pre style='margin:0; background:transparent; padding:0;'>" . htmlspecialchars(json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre></td>";
                    echo "</tr>";
                }
                echo "</table>";
                echo "</div>";
                
                // ========== KONVERSI DATA UNTUK FORM TAMBAH CREATOR ==========
                echo "<h3>🔄 6. Data Siap untuk Form Tambah Creator</h3>";
                echo "<div class='box'>";
                
                $gmv_usd = floatval($data['gmv'] ?? 0);
                $exchange_rate = 16000;
                $gmv_idr = round($gmv_usd * $exchange_rate);
                
                $creator_form_data = [
                    'username' => $data['username'] ?? '',
                    'nickname' => $data['nickname'] ?? '',
                    'avatar_url' => $data['avatar_url'] ?? '',
                    'follower_count' => $data['follower_count'] ?? 0,
                    'gmv_usd' => $gmv_usd,
                    'gmv_idr' => $gmv_idr,
                    'gmv_formatted' => 'Rp ' . number_format($gmv_idr, 0, ',', '.'),
                    'category' => 'Lifestyle (auto-detect)',
                    'avg_video_views' => $data['avg_ec_video_play_count'] ?? 0,
                    'avg_live_uv' => $data['avg_ec_live_view_count'] ?? 0,
                    'creator_open_id' => $creator_open_id,
                    'top_collaborated_brand_ids' => $data['top_collaborated_brand_ids'] ?? [],
                    'brand_collaboration_count' => $data['brand_collaboration_count'] ?? 0,
                    'promoted_product_num' => $data['promoted_product_num'] ?? 0,
                    'engagement_rate' => ($data['ec_video_engagement_rate'] ?? 0) / 100,
                    'phone' => $this->extractPhoneFromBio($data['bio'] ?? ''),
                    'email' => ''
                ];
                
                echo "<pre>";
                echo "// Data untuk dikirim ke showAddCreatorFromSearchForm()\n";
                echo json_encode($creator_form_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                echo "</pre>";
                
                echo "<h4>📝 Catatan:</h4>";
                echo "<ul>";
                echo "<li><code>top_collaborated_brand_ids</code> - Brand IDs yang paling sering diajak kolaborasi oleh creator ini</li>";
                echo "<li><code>brand_collaboration_count</code> - Total jumlah brand yang pernah diajak kolaborasi</li>";
                echo "<li><code>promoted_product_num</code> - Total jumlah produk yang pernah dipromosikan</li>";
                echo "</ul>";
                
                echo "</div>";
                
            } else {
                echo "<div class='error'>❌ API Error: " . htmlspecialchars($result['message'] ?? 'Unknown error') . "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='error'>❌ Exception: " . htmlspecialchars($e->getMessage()) . "</div>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        }
        
        echo "</div>";
    }
    
    echo "</div></body></html>";
}

/**
 * Get creator performance detail - RAW (tanpa format ulang)
 */
private function get_creator_performance_detail_raw($creator_open_id) {
    try {
        // 🔥 PANGGIL API DARI LIBRARY
        $result = $this->jsm_api->get_marketplace_creator_performance($creator_open_id);
        
        // 🔥 TAMBAHKAN DATA TOP COLLABORATED BRAND IDS (jika ada di response)
        if ($result['success'] && isset($result['data'])) {
            // Cek apakah ada field top_collaborated_brand_ids
            if (!isset($result['data']['top_collaborated_brand_ids'])) {
                // Coba cari dari sumber lain
                $result['data']['top_collaborated_brand_ids'] = [];
                
                // Jika ada brand_collaboration_count > 0, mungkin bisa diambil dari response lain
                // Tapi TikTok API mungkin tidak langsung memberikan list brand IDs
                log_message('debug', 'top_collaborated_brand_ids not found in API response');
            }
        }
        
        return $result;
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Helper: Extract phone number from bio
 */
private function extractPhoneFromBio($bio) {
    if (!$bio) return '';
    
    $patterns = [
        '/(\+?62[-\s]?8[1-9][0-9]{1,3}[-\s]?[0-9]{3,4}[-\s]?[0-9]{3,4})/i',
        '/(08[1-9][0-9]{1,3}[-\s]?[0-9]{3,4}[-\s]?[0-9]{3,4})/i',
        '/(08[1-9][0-9]{8,11})/i',
        '/(\+62[0-9]{9,13})/i',
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $bio, $matches)) {
            return preg_replace('/[-\s]/', '', $matches[1]);
        }
    }
    
    return '';
}

public function debug_creator_raw() {
   $this->output->set_content_type('application/json');
    
    $creator_open_id = $this->input->get('creator_open_id');
    
    if (empty($creator_open_id)) {
        // Ambil sample dari database
        $sample = $this->db->select('creator_open_id')
            ->where('creator_open_id IS NOT NULL')
            ->where('creator_open_id !=', '')
            ->limit(1)
            ->get('campaign_creator_performance')
            ->row();
        
        if ($sample) {
            $creator_open_id = $sample->creator_open_id;
        } else {
            return $this->output->set_output(json_encode([
                'error' => 'No creator_open_id provided and no sample found',
                'usage' => '/is/debug_creator_raw_full?creator_open_id=xxx'
            ], JSON_PRETTY_PRINT));
        }
    }
    
    // 🔥 PANGGIL API LANGSUNG TANPA FORMAT
    $path = "/affiliate_seller/202508/marketplace_creators/{$creator_open_id}";
    $result = $this->jsm_api->debug_raw_request($path, [], 'GET', null);
    
    $output = [
        'debug_info' => [
            'timestamp' => date('Y-m-d H:i:s'),
            'creator_open_id' => $creator_open_id,
            'endpoint' => $path
        ],
        'api_response' => $result,
        'top_collaborated_brand_ids_analysis' => []
    ];
    
    // 🔥 ANALISIS top_collaborated_brand_ids
    if ($result['success'] && isset($result['data'])) {
        $data = $result['data'];
        
        // Cek di berbagai kemungkinan path
        $possible_paths = [
            $data['creator']['top_collaborated_brand_ids'] ?? null,
            $data['top_collaborated_brand_ids'] ?? null,
            $data['data']['creator']['top_collaborated_brand_ids'] ?? null,
            $data['data']['top_collaborated_brand_ids'] ?? null
        ];
        
        foreach ($possible_paths as $idx => $value) {
            if ($value !== null) {
                $output['top_collaborated_brand_ids_analysis'][] = [
                    'path_found' => $idx,
                    'value' => $value,
                    'type' => gettype($value),
                    'count' => is_array($value) ? count($value) : null
                ];
            }
        }
        
        // Jika ditemukan, cari info brand
        if (!empty($output['top_collaborated_brand_ids_analysis'])) {
            $top_brands = $output['top_collaborated_brand_ids_analysis'][0]['value'];
            if (is_array($top_brands) && !empty($top_brands)) {
                $brands_info = [];
                foreach ($top_brands as $brand_id) {
                    $brand = $this->db->select('id, name, shop_name, seller_id, category')
                        ->where('seller_id', $brand_id)
                        ->or_where('id', $brand_id)
                        ->get('brands')
                        ->row();
                    
                    $brands_info[] = [
                        'brand_id' => $brand_id,
                        'in_database' => !empty($brand),
                        'brand_info' => $brand
                    ];
                }
                $output['brands_info'] = $brands_info;
            }
        }
    }
    
    return $this->output->set_output(json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

/**
 * Helper: Get sample value for debugging
 */
private function getSampleValue($value) {
    if (is_null($value)) {
        return null;
    }
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }
    if (is_array($value)) {
        if (empty($value)) {
            return '[]';
        }
        // Return first few items
        $sample = array_slice($value, 0, 3);
        return $sample;
    }
    if (is_string($value) && strlen($value) > 200) {
        return substr($value, 0, 200) . '...';
    }
    return $value;
}

/**
 * Helper: Get brand info by ID
 */
private function getBrandInfoById($brand_id) {
    // Cari di tabel brands
    $brand = $this->db->select('id, name, shop_name, seller_id, category, status')
        ->where('id', $brand_id)
        ->or_where('seller_id', $brand_id)
        ->get('brands')
        ->row();
    
    if ($brand) {
        return [
            'exists_in_db' => true,
            'id' => $brand->id,
            'name' => $brand->name,
            'shop_name' => $brand->shop_name,
            'seller_id' => $brand->seller_id,
            'category' => $brand->category,
            'status' => $brand->status
        ];
    }
    
    // Coba cari di affiliate_products
    $product = $this->db->select('shop_name, seller_id')
        ->where('seller_id', $brand_id)
        ->or_where('shop_name LIKE', '%' . $brand_id . '%')
        ->limit(1)
        ->get('affiliate_products')
        ->row();
    
    if ($product) {
        return [
            'exists_in_db' => false,
            'found_in_products' => true,
            'shop_name' => $product->shop_name,
            'seller_id' => $product->seller_id,
            'note' => 'Brand belum terdaftar di tabel brands, tapi ada di affiliate_products'
        ];
    }
    
    return [
        'exists_in_db' => false,
        'note' => 'Brand tidak ditemukan di database'
    ];
}

public function get_brand_by_seller_id() {
    $this->output->set_content_type('application/json');
    
    $seller_id = $this->input->get('seller_id');
    
    if (empty($seller_id)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Seller ID required'
        ]));
    }
    
    try {
        // 🔥 COBA CARI DI DATABASE TERLEBIH DAHULU
        $brand = $this->db->select('id, name, shop_name, seller_id, category, whatsapp_number, email, status')
            ->where('seller_id', $seller_id)
            ->or_where('id', $seller_id)
            ->get('brands')
            ->row();
        
        if ($brand) {
            return $this->output->set_output(json_encode([
                'success' => true,
                'source' => 'database',
                'brand' => $brand
            ]));
        }
        
        // 🔥 JIKA TIDAK ADA, CRAWL VIA TIKTOK API
        // Kita perlu mencari produk dari seller_id ini untuk mendapatkan nama toko
        $this->load->library('Tiktok_partner_crawler');
        
        // Coba cari produk berdasarkan seller_id (jika endpoint tersedia)
        // Atau kita bisa coba search dengan keyword dari seller_id
        $search_result = $this->tiktok_partner_crawler->search_brand_product($seller_id, 1, 10);
        
        if ($search_result['success'] && !empty($search_result['data']['data']['products'])) {
            $product = $search_result['data']['data']['products'][0];
            $shop_info = $product['shop_info'] ?? [];
            $shop_name = $shop_info['shop_name'] ?? '';
            
            // Ambil kontak
            $contact_response = $this->tiktok_partner_crawler->get_brand_contact($seller_id);
            $contact = $this->tiktok_partner_crawler->parse_brand_contact($contact_response);
            
            return $this->output->set_output(json_encode([
                'success' => true,
                'source' => 'api_crawl',
                'brand' => [
                    'seller_id' => $seller_id,
                    'shop_name' => $shop_name,
                    'name' => $shop_name,
                    'whatsapp' => $contact['whatsapp'] ?? null,
                    'email' => $contact['email'] ?? null,
                    'contact_http_code' => $contact_response['http_code'] ?? null
                ]
            ]));
        }
        
        // 🔥 FALLBACK: Coba cari dengan endpoint get_brand_contact saja
        $contact_response = $this->tiktok_partner_crawler->get_brand_contact($seller_id);
        $contact = $this->tiktok_partner_crawler->parse_brand_contact($contact_response);
        
        if ($contact['whatsapp'] || $contact['email']) {
            return $this->output->set_output(json_encode([
                'success' => true,
                'source' => 'api_contact_only',
                'brand' => [
                    'seller_id' => $seller_id,
                    'whatsapp' => $contact['whatsapp'] ?? null,
                    'email' => $contact['email'] ?? null
                ]
            ]));
        }
        
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Brand not found for seller_id: ' . $seller_id
        ]));
        
    } catch (Exception $e) {
        log_message('error', 'get_brand_by_seller_id error: ' . $e->getMessage());
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]));
    }
}

/**
 * Get multiple brands info by seller_ids (batch)
 * URL: /is/get_brands_by_seller_ids?seller_ids=7181208741260592901,7181503935945557766
 */
public function get_brands_by_seller_ids() {
    $this->output->set_content_type('application/json');
    
    $seller_ids_str = $this->input->get('seller_ids');
    
    if (empty($seller_ids_str)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Seller IDs required (comma separated)'
        ]));
    }
    
    $seller_ids = explode(',', $seller_ids_str);
    $results = [];
    
    foreach ($seller_ids as $seller_id) {
        $seller_id = trim($seller_id);
        
        // Cari di database dulu
        $brand = $this->db->select('id, name, shop_name, seller_id, category, whatsapp_number, email, status')
            ->where('seller_id', $seller_id)
            ->or_where('id', $seller_id)
            ->get('brands')
            ->row();
        
        if ($brand) {
            $results[] = [
                'seller_id' => $seller_id,
                'found' => true,
                'source' => 'database',
                'name' => $brand->name,
                'shop_name' => $brand->shop_name,
                'category' => $brand->category,
                'whatsapp' => $brand->whatsapp_number,
                'email' => $brand->email,
                'status' => $brand->status
            ];
        } else {
            $results[] = [
                'seller_id' => $seller_id,
                'found' => false,
                'source' => null,
                'message' => 'Brand not found in database'
            ];
        }
    }
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'total' => count($results),
        'brands' => $results
    ]));
}

public function debug_top_brands() {
    $this->output->set_content_type('application/json');
    
    $creator_open_id = $this->input->get('creator_open_id');
    
    if (empty($creator_open_id)) {
        return $this->output->set_output(json_encode([
            'error' => 'Creator Open ID required',
            'usage' => '/is/debug_top_brands?creator_open_id=xxx'
        ]));
    }
    
    // Ambil data creator
    $creator_result = $this->jsm_api->get_marketplace_creator_performance($creator_open_id);
    
    if (!$creator_result['success'] || empty($creator_result['data']['top_collaborated_brand_ids'])) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'No top_collaborated_brand_ids found for this creator'
        ]));
    }
    
    $top_brand_ids = $creator_result['data']['top_collaborated_brand_ids'];
    $brands_info = [];
    
    $this->load->library('Tiktok_partner_crawler');
    
    foreach ($top_brand_ids as $brand_id) {
        $brand_data = [
            'brand_id' => $brand_id,
            'in_database' => false,
            'from_crawl' => null
        ];
        
        // Cek di database
        $db_brand = $this->db->select('id, name, shop_name, seller_id, category, status')
            ->where('seller_id', $brand_id)
            ->or_where('id', $brand_id)
            ->get('brands')
            ->row();
        
        if ($db_brand) {
            $brand_data['in_database'] = true;
            $brand_data['database_info'] = $db_brand;
        } else {
            // Coba crawl via API
            try {
                $search_result = $this->tiktok_partner_crawler->search_brand_product($brand_id, 1, 5);
                
                if ($search_result['success'] && !empty($search_result['data']['data']['products'])) {
                    $product = $search_result['data']['data']['products'][0];
                    $shop_info = $product['shop_info'] ?? [];
                    
                    $brand_data['from_crawl'] = [
                        'shop_name' => $shop_info['shop_name'] ?? null,
                        'seller_id' => $shop_info['seller_id'] ?? null,
                        'shop_rating' => $shop_info['shop_rating'] ?? null,
                        'product_count' => count($search_result['data']['data']['products']),
                        'sample_product' => [
                            'name' => $product['title'] ?? null,
                            'price' => $product['price']['format_price'] ?? null,
                            'commission' => $product['commission_rate'] ?? null
                        ]
                    ];
                }
                
                // Coba ambil kontak
                $contact_response = $this->tiktok_partner_crawler->get_brand_contact($brand_id);
                $contact = $this->tiktok_partner_crawler->parse_brand_contact($contact_response);
                
                if ($contact['whatsapp'] || $contact['email']) {
                    $brand_data['from_crawl']['contact'] = [
                        'whatsapp' => $contact['whatsapp'],
                        'email' => $contact['email']
                    ];
                }
                
            } catch (Exception $e) {
                $brand_data['crawl_error'] = $e->getMessage();
            }
        }
        
        $brands_info[] = $brand_data;
    }
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'creator_open_id' => $creator_open_id,
        'total_brands' => count($top_brand_ids),
        'brand_ids' => $top_brand_ids,
        'brands_detail' => $brands_info
    ], JSON_PRETTY_PRINT));
}

// ========== TASK 3: KONFIRMASI SAMPLE DENGAN VARIAN & CATATAN ==========
public function confirm_sample_with_details() {
    $this->output->set_content_type('application/json');
    
    try {
        $creator_id = $this->input->post('creator_id');
        $products = json_decode($this->input->post('products'), true);
        
        log_message('debug', 'confirm_sample_with_details - creator_id: ' . $creator_id);
        log_message('debug', 'confirm_sample_with_details - products: ' . json_encode($products));
        
        if (!$creator_id || empty($products)) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Missing required data'
            ]));
        }
        
        $creator = $this->db->where('id', $creator_id)->get('creators')->row();
        if (!$creator) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Creator not found'
            ]));
        }
        
        // ... (kode grouping brand sama seperti sebelumnya) ...
        
        // 🔥 Update status creator ke SAMPLE_SENT (dari LINK_SENT)
        $this->db->where('id', $creator_id)->update('creators', [
            'status' => 'SAMPLE_SENT',  // 🔥 Berubah dari LINK_SENT ke SAMPLE_SENT
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        // ... (kode insert sample_requests) ...
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'message' => 'Sample request confirmed!',
            // ... data lainnya
        ]));
        
    } catch (Exception $e) {
        log_message('error', 'confirm_sample_with_details error: ' . $e->getMessage());
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage()
        ]));
    }
}


// ========== GENERATE PRINT OUT SAMPLE REQUEST ==========
public function generate_sample_printout() {
    $this->output->set_content_type('application/json');
    
    $creator_id = $this->input->post('creator_id');
    $brand_groups = json_decode($this->input->post('brand_groups'), true);
    
    if (!$creator_id || empty($brand_groups)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Missing required data'
        ]));
    }
    
    $creator = $this->Creator_model->get_creator_by_id($creator_id);
    if (!$creator) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Creator not found'
        ]));
    }
    
    // 🔥 FORMAT DATA UNTUK PRINT OUT - SESUAI DENGAN VIEW
    $printout_data = [
        'request_code' => 'SMP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6)),
        'request_date' => date('Y-m-d H:i:s'),
        'creator' => [
            'username' => $creator->username,
            'full_name' => $creator->full_name,
            'phone' => $creator->phone,
            'email' => $creator->email,
            'alamat' => $creator->alamat ?? '-',
            'penerima' => $creator->penerima ?? $creator->full_name
        ],
        // 🔥 KIRIM SEBAGAI brand (TUNGGAL) ATAU brand_groups (MULTIPLE)
        'brand' => !empty($brand_groups) ? $brand_groups[0] : null,
        'brand_groups' => $brand_groups,
        'products' => []
    ];
    
    // 🔥 FLATTEN PRODUCTS DARI SEMUA BRAND
    foreach ($brand_groups as $group) {
        if (!empty($group['products'])) {
            foreach ($group['products'] as $product) {
                $printout_data['products'][] = [
                    'product_name' => $product['product_name'] ?? '',
                    'varian' => $product['varian'] ?? '',
                    'product_notes' => $product['product_notes'] ?? '',
                    'commission_rate' => $product['commission_rate'] ?? 0
                ];
            }
        }
    }
    
    // Simpan ke session
    $this->session->set_userdata('sample_printout_data', $printout_data);
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'data' => $printout_data
    ]));
}


// ========== VIEW PRINT OUT SAMPLE REQUEST ==========
public function view_sample_printout() {
    // Cek apakah ada data printout di session
    $printout_data = $this->session->userdata('sample_printout_data');
    
    if (!$printout_data) {
        show_error('No printout data found. Please generate printout first.', 404);
    }
    
    $data = [
        'title' => 'Sample Request Printout',
        'printout' => $printout_data
    ];
    
    $this->load->view('is/printout_sample', $data);
}

// ========== GET BRAND SHIPPING ADDRESS ==========
public function get_brand_shipping_address() {
    $this->output->set_content_type('application/json');
    
    $brand_id = $this->input->post('brand_id');
    
    if (!$brand_id) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Brand ID required'
        ]));
    }
    
    $brand = $this->db->select('id, name, shop_name, address, city, province, postal_code, phone, pic_name')
        ->where('id', $brand_id)
        ->get('brands')
        ->row();
    
    if ($brand) {
        // Format alamat lengkap
        $full_address = $brand->address ?? '';
        if ($brand->city) $full_address .= ', ' . $brand->city;
        if ($brand->province) $full_address .= ', ' . $brand->province;
        if ($brand->postal_code) $full_address .= ' ' . $brand->postal_code;
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'brand' => [
                'id' => $brand->id,
                'name' => $brand->name,
                'shop_name' => $brand->shop_name,
                'address' => $brand->address,
                'city' => $brand->city,
                'province' => $brand->province,
                'postal_code' => $brand->postal_code,
                'full_address' => $full_address,
                'phone' => $brand->phone,
                'pic_name' => $brand->pic_name
            ]
        ]));
    }
    
    return $this->output->set_output(json_encode([
        'success' => false,
        'message' => 'Brand not found'
    ]));
}

// ========== GET CREATOR SHIPPING ADDRESS ==========
public function get_creator_shipping_address() {
    $this->output->set_content_type('application/json');
    
    try {
        $creator_id = $this->input->post('creator_id');
        
        log_message('debug', 'get_creator_shipping_address called with creator_id: ' . $creator_id);
        
        if (!$creator_id) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Creator ID required'
            ]));
        }
        
        // 🔥 QUERY - HANYA KOLOM YANG ADA DI TABEL brands
        $creator = $this->db->select('
                c.id, 
                c.username, 
                c.full_name, 
                c.phone, 
                c.email, 
                c.alamat, 
                c.penerima, 
                c.brand_id,
                b.name as brand_name, 
                b.shop_name, 
                b.sample_type,
                b.whatsapp_number,
                b.email as brand_email,
                b.shop_id,
                b.status as brand_status
            ')
            ->from('creators c')
            ->join('brands b', 'c.brand_id = b.id', 'left')
            ->where('c.id', $creator_id)
            ->get()
            ->row();
        
        log_message('debug', 'Query result: ' . json_encode($creator));
        
        if ($creator) {
            // 🔥 SAMPLE TYPE - enum('manual','auto')
            $sample_type = $creator->sample_type ?? 'manual';
            $sample_type_label = 'Manual';
            $sample_type_icon = '📦';
            
            if ($sample_type == 'auto') {
                $sample_type_label = 'Auto (System TikTok)';
                $sample_type_icon = '🤖';
            } else {
                $sample_type_label = 'Manual';
                $sample_type_icon = '📦';
            }
            
            // 🔥 ALAMAT BRAND - hanya shop_name + shop_id (karena tidak ada kolom alamat terpisah)
            $brand_address = $creator->shop_name ?? '';
            if ($creator->shop_id) {
                $brand_address .= ($brand_address ? ' - ' : '') . 'Shop ID: ' . $creator->shop_id;
            }
            
            return $this->output->set_output(json_encode([
                'success' => true,
                'creator' => [
                    'id' => $creator->id,
                    'username' => $creator->username,
                    'full_name' => $creator->full_name,
                    'phone' => $creator->phone,
                    'email' => $creator->email,
                    'alamat' => $creator->alamat ?? '-',
                    'penerima' => $creator->penerima ?? $creator->full_name,
                    'brand_id' => $creator->brand_id,
                    'brand_name' => $creator->brand_name ?? 'Tidak ada brand',
                    'brand_shop_name' => $creator->shop_name ?? '',
                    'brand_shop_id' => $creator->shop_id ?? '',
                    'sample_type' => $sample_type,
                    'sample_type_label' => $sample_type_label,
                    'sample_type_icon' => $sample_type_icon,
                    'brand_address' => $brand_address ?: 'Alamat brand tidak tersedia',
                    'brand_whatsapp' => $creator->whatsapp_number ?? '',
                    'brand_email' => $creator->brand_email ?? '',
                    'brand_status' => $creator->brand_status ?? ''
                ]
            ]));
        }
        
        // 🔥 FALLBACK: cari creator tanpa join brand
        $creator_fallback = $this->db->select('*')
            ->from('creators')
            ->where('id', $creator_id)
            ->get()
            ->row();
        
        if ($creator_fallback) {
            return $this->output->set_output(json_encode([
                'success' => true,
                'creator' => [
                    'id' => $creator_fallback->id,
                    'username' => $creator_fallback->username,
                    'full_name' => $creator_fallback->full_name,
                    'phone' => $creator_fallback->phone,
                    'email' => $creator_fallback->email,
                    'alamat' => $creator_fallback->alamat ?? '-',
                    'penerima' => $creator_fallback->penerima ?? $creator_fallback->full_name,
                    'brand_id' => $creator_fallback->brand_id,
                    'brand_name' => 'Tidak ada brand',
                    'brand_shop_name' => '',
                    'brand_shop_id' => '',
                    'sample_type' => 'manual',
                    'sample_type_label' => 'Manual',
                    'sample_type_icon' => '📦',
                    'brand_address' => 'Brand tidak ditemukan',
                    'brand_whatsapp' => '',
                    'brand_email' => '',
                    'brand_status' => ''
                ]
            ]));
        }
        
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Creator not found'
        ]));
        
    } catch (Exception $e) {
        log_message('error', 'get_creator_shipping_address error: ' . $e->getMessage());
        log_message('error', 'get_creator_shipping_address trace: ' . $e->getTraceAsString());
        
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage()
        ]));
    }
}


// ========== TASK 3: GET PRODUCTS FOR SAMPLE ==========
public function get_sample_products() {
    $this->output->set_content_type('application/json');
    
    try {
        $creator_id = $this->input->post('creator_id');
        
        if (!$creator_id) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Creator ID required'
            ]));
        }
        
        // Ambil creator
        $creator = $this->db->select('
                c.id, 
                c.username, 
                c.full_name, 
                c.phone, 
                c.email, 
                c.alamat, 
                c.penerima,
                c.brand_id
            ')
            ->from('creators c')
            ->where('c.id', $creator_id)
            ->get()
            ->row();
            
        if (!$creator) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Creator not found'
            ]));
        }
        
        // 🔥 AMBIL AFFILIATE LINKS CREATOR + JOIN affiliate_products untuk ambil shop_name
        $links = $this->db->select('
                acl.product_id,
                acl.product_name,
                acl.campaign_id,
                acl.commission_rate,
                acl.creator_id,
                acl.creator_username,
                acl.source,
                ap.shop_name as product_shop_name,
                ap.image_url,
                ap.price
            ')
            ->from('affiliate_creator_links acl')
            ->join('affiliate_products ap', 'acl.product_id = ap.product_id AND acl.campaign_id = ap.campaign_id', 'left')
            ->where('acl.creator_id', $creator_id)
            ->where('acl.status', 'ACTIVE')
            ->get()
            ->result();
        
        if (empty($links)) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Belum ada link afiliasi untuk creator ini. Silakan assign campaign terlebih dahulu di Task 2.'
            ]));
        }
        
        // 🔥 AMBIL CAMPAIGN INFO
        $campaign_ids = array_unique(array_column($links, 'campaign_id'));
        $campaigns = $this->db->select('campaign_id, campaign_name, status')
            ->from('affiliate_campaigns')
            ->where_in('campaign_id', $campaign_ids)
            ->get()
            ->result();
        
        $campaign_map = [];
        foreach ($campaigns as $camp) {
            $campaign_map[$camp->campaign_id] = $camp;
        }
        
        // 🔥 AMBIL SEMUA BRAND DARI DATABASE UNTUK MAPPING shop_name -> brand
        $all_brands = $this->db->select('id, name, shop_name, sample_type, whatsapp_number, email')
            ->from('brands')
            ->get()
            ->result();
        
        $brand_map = [];
        foreach ($all_brands as $b) {
            // Mapping by shop_name (case insensitive)
            if ($b->shop_name) {
                $brand_map[strtolower(trim($b->shop_name))] = $b;
            }
            // Mapping by name juga
            if ($b->name) {
                $brand_map[strtolower(trim($b->name))] = $b;
            }
        }
        
        // 🔥 GROUPING BERDASARKAN shop_name DARI affiliate_products
        $brands = [];
        foreach ($links as $link) {
            $shop_name = $link->product_shop_name ?: 'Brand Tidak Diketahui';
            $shop_key = strtolower(trim($shop_name));
            
            // Cari brand di database berdasarkan shop_name
            $brand_info = $brand_map[$shop_key] ?? null;
            
            // Jika tidak ditemukan, coba cari berdasarkan nama yang mirip
            if (!$brand_info) {
                foreach ($all_brands as $b) {
                    if (stripos($shop_name, $b->shop_name) !== false || stripos($b->shop_name, $shop_name) !== false) {
                        $brand_info = $b;
                        break;
                    }
                }
            }
            
            $brand_key = $brand_info->id ?? $shop_key;
            
            if (!isset($brands[$brand_key])) {
                $brands[$brand_key] = [
                    'brand_id' => $brand_info->id ?? null,
                    'brand_name' => $brand_info->name ?? $shop_name,
                    'brand_shop_name' => $brand_info->shop_name ?? $shop_name,
                    'sample_type' => $brand_info->sample_type ?? 'manual',
                    'brand_whatsapp' => $brand_info->whatsapp_number ?? '',
                    'brand_email' => $brand_info->email ?? '',
                    'products' => []
                ];
            }
            
            $campaign = $campaign_map[$link->campaign_id] ?? null;
            
            $brands[$brand_key]['products'][] = [
'product_id' => $link->product_id,
                'product_name' => $link->product_name,
                'campaign_id' => $link->campaign_id,
                'commission_rate' => $link->commission_rate,
                'campaign_name' => $campaign->campaign_name ?? '',
                'source' => $link->source ?? 'self_generated',
                'image_url' => $link->image_url ?? '',
                'price' => $link->price ?? 0
            ];
        }
        
        $brands_list = array_values($brands);
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'brands' => $brands_list,
            'total_brands' => count($brands_list),
            'total_products' => count($links),
            'creator' => [
                'id' => $creator->id,
                'username' => $creator->username,
                'full_name' => $creator->full_name,
                'phone' => $creator->phone,
                'alamat' => $creator->alamat,
                'penerima' => $creator->penerima
            ]
        ]));
        
    } catch (Exception $e) {
        log_message('error', 'get_sample_products error: ' . $e->getMessage());
        log_message('error', 'get_sample_products trace: ' . $e->getTraceAsString());
        
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage()
        ]));
    }
}


// ========== SEARCH CREATORS BY TASK ==========
public function search_creators_by_task() {
    $this->output->set_content_type('application/json');
    
    try {
        $keyword = $this->input->post('keyword');
        $task = $this->input->post('task'); // '2' atau '3'
        $limit = intval($this->input->post('limit') ?: 50);
        
        log_message('debug', 'search_creators_by_task - keyword: ' . $keyword . ', task: ' . $task . ', limit: ' . $limit);
        
        if (empty($keyword) || strlen($keyword) < 2) {
            return $this->output->set_output(json_encode([
                'success' => true,
                'data' => [],
                'total' => 0,
                'message' => 'Minimal 2 karakter'
            ]));
        }
        
        $user_id = $this->session->userdata('user_id');
        $is_supervisor = ($user_id == 2);
        $like_keyword = '%' . $keyword . '%';
        $results = [];
        
        $task_num = intval($task);
        
        if ($task_num == 2) {
            // ============================================================
            // TASK 2: WAITING HANDLER - SEARCH
            // ============================================================
            
            // 🔥 PART 1: Cari creator yang sudah terdaftar di tabel creators
            $registered = $this->db->select('
                    c.id,
                    c.username,
                    c.full_name,
                    c.avatar_url,
                    c.category,
                    c.phone,
                    c.alamat,
                    c.penerima,
                    c.created_at,
                    c.imported_gmv,
                    c.is_id as handler_id,
                    u.full_name as handler_name,
                    b.name as brand_name,
                    b.shop_name,
                    "registered" as source_type,
                    (SELECT COUNT(DISTINCT acl.id) 
                     FROM affiliate_creator_links acl 
                     WHERE (acl.creator_id = c.id OR LOWER(TRIM(acl.creator_username)) = LOWER(TRIM(c.username)))
                       AND acl.status = "ACTIVE") as total_active_links,
                    CASE 
                        WHEN c.is_id IS NOT NULL AND c.is_id != ' . intval($user_id) . ' THEN "claimed"
                        WHEN (c.is_id IS NULL OR c.is_id = ' . intval($user_id) . ') AND EXISTS (
                            SELECT 1 FROM affiliate_creator_links acl2
                            WHERE (acl2.creator_id = c.id OR LOWER(TRIM(acl2.creator_username)) = LOWER(TRIM(c.username)))
                              AND acl2.status = "ACTIVE"
                              AND (acl2.total_clicks > 0 OR acl2.total_orders > 0 OR acl2.showcase_status = "added")
                        ) THEN "ready"
                        ELSE "no_handler"
                    END as deal_status
                ')
                ->from('creators c')
                ->join('brands b', 'c.brand_id = b.id', 'left')
                ->join('users u', 'c.is_id = u.id', 'left')
                ->group_start()
                    ->like('c.username', $keyword, 'both')
                    ->or_like('c.full_name', $keyword, 'both')
                    ->or_like('c.category', $keyword, 'both')
                ->group_end()
                ->where('c.status', 'LINK_SENT')
                ->limit($limit)
                ->get()
                ->result();
            
            // 🔥 PART 2: Cari creator yang belum terdaftar (unregistered)
            $unregistered_sql = "
                SELECT 
                    NULL as id,
                    o.creator_username as username,
                    o.creator_username as full_name,
                    NULL as avatar_url,
                    NULL as category,
                    NULL as phone,
                    NULL as alamat,
                    NULL as penerima,
                    NULL as created_at,
                    0 as imported_gmv,
                    NULL as handler_id,
                    NULL as handler_name,
                    NULL as brand_name,
                    NULL as shop_name,
                    'unregistered' as source_type,
                    'no_handler' as deal_status
                FROM affiliate_orders o
                WHERE o.order_date_local >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                  AND o.order_status NOT IN ('CANCELLED', 'REFUNDED')
                  AND o.creator_username IS NOT NULL 
                  AND o.creator_username != ''
                  AND o.creator_username LIKE ?
                  AND o.creator_username NOT IN (
                      SELECT DISTINCT username 
                      FROM creators 
                      WHERE username IS NOT NULL AND username != ''
                  )
                GROUP BY o.creator_username
                LIMIT ?
            ";
            
            $unregistered = $this->db->query($unregistered_sql, [$like_keyword, intval($limit)])->result();
            
            // Gabungkan hasil
            $results = array_merge($registered, $unregistered);
            
            // Tambahkan data detail untuk registered & unregistered
            foreach ($results as $item) {
                if ($item->source_type == 'registered' && !empty($item->id)) {
                    $gmv_query = "
                        SELECT COALESCE(SUM(gmv), 0) as total
                        FROM affiliate_orders
                        WHERE LOWER(TRIM(creator_username)) = LOWER(TRIM(?))
                          AND order_date_local >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                          AND order_status NOT IN ('CANCELLED', 'REFUNDED')
                     ";
                    $gmv = $this->db->query($gmv_query, [$item->username])->row();
                     
                    $item->total_gmv_30d = floatval($gmv->total ?? 0);
                } else {
                    // Unregistered: hitung dari orders
                    $gmv_query = "
                        SELECT COALESCE(SUM(gmv), 0) as total, MAX(product_name) as top_product
                        FROM affiliate_orders
                        WHERE LOWER(TRIM(creator_username)) = LOWER(TRIM(?))
                          AND order_date_local >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                          AND order_status NOT IN ('CANCELLED', 'REFUNDED')
                    ";
                    $gmv = $this->db->query($gmv_query, [$item->username])->row();
                    $item->total_gmv_30d = floatval($gmv->total ?? 0);
                    $item->total_active_links = 0;
                    $item->top_product = $gmv->top_product ?? '';
                    $item->top_product_image = '';
                }
            }
            
        } else {
            // ============================================================
            // TASK 3: MONITORING - SEARCH
            // ============================================================
            $results = $this->db->select('
                    c.id,
                    c.username,
                    c.full_name,
                    c.avatar_url,
                    c.category,
                    c.phone,
                    c.alamat,
                    c.penerima,
                    c.created_at,
                    c.approved_at,
                    c.imported_gmv,
                    c.is_id as handler_id,
                    u.full_name as handler_name,
                    b.name as brand_name,
                    b.shop_name,
                    "registered" as source_type,
                    "monitoring" as deal_status
                ')
                ->from('creators c')
                ->join('brands b', 'c.brand_id = b.id', 'left')
                ->join('users u', 'c.is_id = u.id', 'left')
                ->group_start()
                    ->like('c.username', $keyword, 'both')
                    ->or_like('c.full_name', $keyword, 'both')
                    ->or_like('c.category', $keyword, 'both')
                ->group_end()
                ->where('c.status', 'ACTIVE')
                ->limit($limit)
                ->get()
                ->result();
            
            // Tambahkan performa untuk setiap creator
            foreach ($results as $item) {
                // Total GMV 30 hari
                $gmv_query = "
                    SELECT COALESCE(SUM(gmv), 0) as total_gmv
                    FROM affiliate_orders
                    WHERE LOWER(TRIM(creator_username)) = LOWER(TRIM(?))
                      AND order_date_local >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                      AND order_status NOT IN ('CANCELLED', 'REFUNDED')
                ";
                $gmv = $this->db->query($gmv_query, [$item->username])->row();
                $item->total_gmv_30d = floatval($gmv->total_gmv ?? 0);
                
                // Total orders 30 hari
                $orders_query = "
                    SELECT COUNT(DISTINCT order_id) as total_orders
                    FROM affiliate_orders
                    WHERE LOWER(TRIM(creator_username)) = LOWER(TRIM(?))
                      AND order_date_local >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                      AND order_status NOT IN ('CANCELLED', 'REFUNDED')
                ";
                $orders = $this->db->query($orders_query, [$item->username])->row();
                $item->total_orders_30d = intval($orders->total_orders ?? 0);
                
                // Total links
                $links_query = "
                    SELECT COUNT(DISTINCT id) as total_links
                    FROM affiliate_creator_links
                    WHERE creator_id = ?
                      AND status = 'ACTIVE'
                ";
                $links = $this->db->query($links_query, [$item->id])->row();
                $item->total_links = intval($links->total_links ?? 0);
                
                // Top product
                $top_product_query = "
                    SELECT product_name
                    FROM affiliate_orders
                    WHERE LOWER(TRIM(creator_username)) = LOWER(TRIM(?))
                      AND order_status NOT IN ('CANCELLED', 'REFUNDED')
                    GROUP BY product_name
                    ORDER BY SUM(gmv) DESC
                    LIMIT 1
                ";
                $top_product = $this->db->query($top_product_query, [$item->username])->row();
                $item->top_product = $top_product->product_name ?? '';
                $item->top_product_image = '';
            }
        }
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'data' => $results,
            'total' => count($results),
            'keyword' => $keyword,
            'task' => $task
        ]));
        
    } catch (Exception $e) {
        log_message('error', 'search_creators_by_task error: ' . $e->getMessage());
        log_message('error', 'search_creators_by_task trace: ' . $e->getTraceAsString());
        
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'data' => [],
            'total' => 0
        ]));
    }
}

// ========== GET USER BY ID (untuk CA info) ==========
public function get_user($user_id)
{
    $this->output->set_content_type('application/json');
    
    if (!$user_id) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'User ID required'
        ]));
    }
    
    $user = $this->db->select('id, username, full_name, email, role')
        ->where('id', $user_id)
        ->get('users')
        ->row();
    
    if ($user) {
        return $this->output->set_output(json_encode([
            'success' => true,
            'user' => $user
        ]));
    }
    
    return $this->output->set_output(json_encode([
        'success' => false,
        'message' => 'User not found'
    ]));
}


// Di Is.php - tambahkan method ini

/**
 * Get products for assign link modal (Task 2)
 * AJAX endpoint
 */
public function get_assign_link_products() {
    $this->output->set_content_type('application/json');
    
    $creator_id = $this->input->post('creator_id');
    
    if (!$creator_id) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Creator ID required',
            'products' => []
        ]));
    }
    
    $this->load->model('BrandCreator_model');
    
    $result = $this->BrandCreator_model->get_creator_products_for_assign($creator_id);
    
    // Log untuk debugging
    log_message('debug', 'Assign link products for creator ' . $creator_id . ': ' . json_encode([
        'fastmoss_uid' => $result['fastmoss_uid'] ?? 'NULL',
        'total' => $result['total'],
        'new_products' => count($result['new_products'] ?? []),
        'product_ids' => array_column($result['products'], 'product_id')
    ]));
    
    return $this->output->set_output(json_encode($result));
}

/**
 * Assign product link to creator (Task 2)
 */
public function assign_product_link() {
    $this->output->set_content_type('application/json');
    
    $creator_id = $this->input->post('creator_id');
    $product_id = $this->input->post('product_id');
    $campaign_id = $this->input->post('campaign_id');
    $commission_rate = $this->input->post('commission_rate') ?: 10;
    $affiliate_link = $this->input->post('affiliate_link');
    
    if (!$creator_id || !$product_id || !$campaign_id) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Missing required data'
        ]));
    }
    
    // Ambil data creator
    $creator = $this->db->select('id, username, full_name')
        ->where('id', $creator_id)
        ->get('creators')
        ->row();
    
    if (!$creator) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Creator not found'
        ]));
    }
    
    // Jika affiliate_link tidak dikirim, ambil dari bd_affiliate_links
    if (empty($affiliate_link)) {
        $bd_link = $this->db->select('affiliate_link, commission_rate')
            ->where('product_id', $product_id)
            ->where('campaign_id', $campaign_id)
            ->where('status', 'ACTIVE')
            ->order_by('created_at', 'DESC')
            ->limit(1)
            ->get('bd_affiliate_links')
            ->row();
        
        if (!$bd_link) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Affiliate link not available. Please ask BD to generate link first.'
            ]));
        }
        
        $affiliate_link = $bd_link->affiliate_link;
        $commission_rate = $bd_link->commission_rate;
    }
    
    // Ambil nama produk
    $product = $this->db->select('product_name')
        ->where('product_id', $product_id)
        ->get('affiliate_products')
        ->row();
    
    $product_name = $product->product_name ?? '';
    
    // Cek apakah sudah diassign
    $existing = $this->db->where('creator_id', $creator_id)
        ->where('product_id', $product_id)
        ->where('campaign_id', $campaign_id)
        ->get('affiliate_creator_links')
        ->row();
    
    $link_data = [
        'creator_id' => $creator_id,
        'creator_username' => $creator->username,
        'campaign_id' => $campaign_id,
        'product_id' => $product_id,
        'product_name' => $product_name,
        'affiliate_link' => $affiliate_link,
        'commission_rate' => $commission_rate,
        'shared_date' => date('Y-m-d H:i:s'),
        'status' => 'ACTIVE',
        'handler_id' => $this->session->userdata('user_id'),
        'handler_name' => $this->session->userdata('full_name'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    if ($existing) {
        $this->db->where('id', $existing->id)->update('affiliate_creator_links', $link_data);
        $message = 'Link updated successfully';
    } else {
        $link_data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('affiliate_creator_links', $link_data);
        $message = 'Link assigned successfully';
    }
    
    // Update status creator ke LINK_SENT jika masih PENDING
    $this->db->where('id', $creator_id)
             ->where('status', 'PENDING')
             ->update('creators', [
                 'status' => 'LINK_SENT',
                 'updated_at' => date('Y-m-d H:i:s')
             ]);
    
    // Log activity
    $this->load->model('User_log_model');
    $this->User_log_model->log(
        $this->session->userdata('user_id'),
        $this->session->userdata('username'),
        'IS',
        'ASSIGN_PRODUCT_LINK',
        "Assigned link for product {$product_name} to @{$creator->username} in campaign {$campaign_id}"
    );
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'message' => $message,
        'link' => $affiliate_link
    ]));
}

public function get_assign_products_by_creator() {
    $this->output->set_content_type('application/json');
    
    try {
        $creator_id = $this->input->post('creator_id');
        
        if (!$creator_id) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Creator ID required',
                'products' => []
            ]));
        }
        
        // Ambil creator
        $creator = $this->db->select('id, username, full_name, tiktok_open_id, brand_id, category, phone')
            ->where('id', $creator_id)
            ->get('creators')
            ->row();
        
        if (!$creator) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Creator not found',
                'products' => []
            ]));
        }
        
        $this->load->model('CreatorProduct_model');
        
        // ========== AMBIL PRODUK YANG MATCH ==========
        $matched_products = $this->CreatorProduct_model->get_matched_for_assign($creator->username);
        
        // ========== PROSES PRODUK ==========
        $products = [];
        foreach ($matched_products as $product) {
            // Cek apakah sudah diassign ke creator ini
            $assigned = $this->db->where('creator_id', $creator_id)
                ->where('product_id', $product->product_id)
                ->where('status', 'ACTIVE')
                ->get('affiliate_creator_links')
                ->row();
            
            // Cek apakah ada link di bd_affiliate_links (sudah dari query)
            $has_link = !empty($product->affiliate_link);
            
            $products[] = (object) [
                'product_id' => $product->product_id,
                'product_name' => $product->product_name,
                'price' => $product->affiliate_price ?? $product->price ?? 0,
                'image_url' => $product->affiliate_image ?? $product->image_url ?? '',
                'shop_name' => $product->affiliate_shop ?? $product->shop_name ?? '',
                'category' => $product->affiliate_category ?? $product->category ?? '',
                'sales_count' => $product->sales_count ?? 0,
                'sales_from_creator' => $product->sales_count ?? 0,
                'gmv_from_creator' => $product->gmv ?? 0,
                'commission_rate' => $product->commission_rate ?? null,
                'open_commission_rate' => $product->open_commission_rate ?? 0,
                'has_link' => $has_link,
                'affiliate_link' => $product->affiliate_link ?? null,
                'link_commission' => $product->link_commission ?? null,
                'link_created_by' => $product->link_created_by ?? null,
                'link_campaign_id' => $product->link_campaign_id ?? null,
                'link_status' => $product->link_status ?? null,
                'is_assigned' => !empty($assigned),
                'assigned_link' => $assigned->affiliate_link ?? null,
                'assigned_at' => $assigned->created_at ?? null,
                'in_database' => true
            ];
        }
        
        // ========== STATISTIK ==========
        $stats = $this->CreatorProduct_model->get_stats_by_username($creator->username);
        
        // ========== CAMPAIGN AKTIF ==========
        $campaigns = $this->db->select('campaign_id, campaign_name, status')
            ->where('status', 'ONGOING')
            ->order_by('created_at', 'DESC')
            ->get('affiliate_campaigns')
            ->result();
        
        // 🔥 LOG UNTUK DEBUG
        log_message('debug', 'get_assign_products_by_creator - creator: ' . $creator->username);
        log_message('debug', 'get_assign_products_by_creator - products count: ' . count($products));
        log_message('debug', 'get_assign_products_by_creator - campaigns count: ' . count($campaigns));
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'creator' => $creator,
            'products' => $products,
            'total' => count($products),
            'stats' => $stats,
            'campaigns' => $campaigns,
            'has_products' => count($products) > 0,
            'message' => count($products) > 0 ? 
                "Found " . count($products) . " products from this creator" : 
                "No matching products found in our database"
        ]));
        
    } catch (Exception $e) {
        log_message('error', 'get_assign_products_by_creator error: ' . $e->getMessage());
        log_message('error', 'get_assign_products_by_creator trace: ' . $e->getTraceAsString());
        
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'products' => []
        ]));
    }
}
public function get_active_campaigns() {
    $this->output->set_content_type('application/json');
    
    // Ambil campaign yang statusnya ONGOING
    $campaigns = $this->db->select('campaign_id, campaign_name, status')
        ->where('status', 'ONGOING')
        ->order_by('created_at', 'DESC')
        ->get('affiliate_campaigns')
        ->result();
    
    // Format response
    $formatted = [];
    foreach ($campaigns as $camp) {
        $formatted[] = [
            'campaign_id' => $camp->campaign_id,
            'campaign_name' => $camp->campaign_name,
            'status' => $camp->status
        ];
    }
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'campaigns' => $formatted,
        'total' => count($formatted)
    ]));
}

public function get_campaign_products_for_assign() {
    $this->output->set_content_type('application/json');
    
    try {
        $campaign_id = $this->input->post('campaign_id');
        $creator_category = $this->input->post('creator_category');
        
        if (!$campaign_id) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Campaign ID required',
                'products' => []
            ]));
        }
        
        log_message('debug', '=== get_campaign_products_for_assign ===');
        log_message('debug', 'campaign_id from POST: ' . $campaign_id);
        log_message('debug', 'creator_category: ' . $creator_category);
        
        // 🔥 CEK CAMPAIGN - coba cari di affiliate_campaigns
        $campaign = $this->db->where('campaign_id', $campaign_id)
            ->get('affiliate_campaigns')
            ->row();
        
        log_message('debug', 'Campaign found in affiliate_campaigns: ' . ($campaign ? 'yes' : 'no'));
        
        if (!$campaign) {
            // Coba cari berdasarkan nama
            $campaign = $this->db->like('campaign_name', $campaign_id)
                ->get('affiliate_campaigns')
                ->row();
            
            if ($campaign) {
                log_message('debug', 'Campaign found by name: ' . $campaign->campaign_id);
                $campaign_id = $campaign->campaign_id;
            } else {
                return $this->output->set_output(json_encode([
                    'success' => false,
                    'message' => 'Campaign not found',
                    'products' => []
                ]));
            }
        }
        
        // 🔥 CEK APAKAH ADA PRODUK DENGAN campaign_id INI
        $check_sql = "SELECT COUNT(*) as total FROM affiliate_products WHERE campaign_id = ? AND review_status = 'APPROVED'";
        $check_result = $this->db->query($check_sql, [$campaign_id])->row();
        $total_approved = $check_result->total ?? 0;
        
        log_message('debug', 'Total APPROVED products for campaign ' . $campaign_id . ': ' . $total_approved);
        
        if ($total_approved == 0) {
            // 🔥 COBA CEK APAKAH ADA PRODUK DENGAN CAMPAIGN_ID YANG MIRIP (case insensitive)
            $like_sql = "SELECT COUNT(*) as total FROM affiliate_products WHERE campaign_id LIKE ? AND review_status = 'APPROVED'";
            $like_result = $this->db->query($like_sql, ['%' . $campaign_id . '%'])->row();
            $like_total = $like_result->total ?? 0;
            
            log_message('debug', 'Products with LIKE campaign_id: ' . $like_total);
            
            if ($like_total > 0) {
                // Ambil produk dengan LIKE
                $products = $this->db->query("
                    SELECT 
                        ap.product_id, 
                        ap.product_name, 
                        ap.price, 
                        ap.image_url, 
                        ap.shop_name, 
                        ap.category, 
                        ap.open_commission_rate, 
                        ap.sales_count,
                        ap.review_status,
                        ap.created_at,
                        ap.campaign_id as actual_campaign_id
                    FROM affiliate_products ap
                    WHERE ap.campaign_id LIKE ?
                    AND ap.review_status = 'APPROVED'
                    ORDER BY ap.sales_count DESC
                    LIMIT 100
                ", ['%' . $campaign_id . '%'])->result();
                
                log_message('debug', 'Products found with LIKE: ' . count($products));
            } else {
                // 🔥 CEK APAKAH ADA PRODUK DI CAMPAIGN LAIN (debug)
                $all_approved = $this->db->query("
                    SELECT COUNT(*) as total, campaign_id 
                    FROM affiliate_products 
                    WHERE review_status = 'APPROVED' 
                    GROUP BY campaign_id 
                    LIMIT 10
                ")->result();
                
                log_message('debug', 'Approved products by campaign: ' . json_encode($all_approved));
                
                return $this->output->set_output(json_encode([
                    'success' => true,
                    'products' => [],
                    'total' => 0,
                    'campaign_name' => $campaign->campaign_name,
                    'has_products' => false,
                    'product_count_db' => $total_approved,
                    'debug' => [
                        'campaign_id' => $campaign_id,
                        'all_approved_by_campaign' => $all_approved
                    ],
                    'message' => 'Tidak ada produk APPROVED di campaign ini. Coba sync data terlebih dahulu.'
                ]));
            }
        } else {
            // 🔥 QUERY NORMAL: Ambil produk APPROVED
            $products = $this->db->query("
                SELECT 
                    ap.product_id, 
                    ap.product_name, 
                    ap.price, 
                    ap.image_url, 
                    ap.shop_name, 
                    ap.category, 
                    ap.open_commission_rate, 
                    ap.sales_count,
                    ap.review_status,
                    ap.created_at
                FROM affiliate_products ap
                WHERE ap.campaign_id = ?
                AND ap.review_status = 'APPROVED'
                ORDER BY ap.sales_count DESC
                LIMIT 100
            ", [$campaign_id])->result();
            
            log_message('debug', 'Products found: ' . count($products));
        }
        
        // 🔥 AMBIL LINK DARI bd_affiliate_links
        $links = $this->db->query("
            SELECT product_id, affiliate_link, commission_rate, created_by_name, status
            FROM bd_affiliate_links
            WHERE campaign_id = ?
            AND status = 'ACTIVE'
        ", [$campaign_id])->result();
        
        $link_map = [];
        foreach ($links as $link) {
            $link_map[$link->product_id] = $link;
        }
        
        log_message('debug', 'Links found: ' . count($links));
        
        $formatted = [];
        if (!empty($products)) {
            foreach ($products as $p) {
                $has_link = isset($link_map[$p->product_id]);
                $link_data = $has_link ? $link_map[$p->product_id] : null;
                
                $formatted[] = [
                    'product_id' => $p->product_id,
                    'product_name' => $p->product_name,
                    'price' => floatval($p->price ?? 0),
                    'image_url' => $p->image_url ?? '',
                    'shop_name' => $p->shop_name ?? '',
                    'category' => $p->category ?? '',
                    'open_commission_rate' => floatval($p->open_commission_rate ?? 0),
                    'commission_rate' => $has_link ? floatval($link_data->commission_rate ?? 0) : 0,
                    'sales_count' => intval($p->sales_count ?? 0),
                    'affiliate_link' => $has_link ? $link_data->affiliate_link : null,
                    'has_link' => $has_link,
                    'link_status' => $has_link ? $link_data->status : null,
                    'link_created_by' => $has_link ? $link_data->created_by_name : null,
                    'review_status' => $p->review_status
                ];
            }
        }
        
        $has_products = count($formatted) > 0;
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'products' => $formatted,
            'total' => count($formatted),
            'campaign_name' => $campaign->campaign_name,
            'campaign_id' => $campaign_id,
            'has_products' => $has_products,
            'product_count_db' => $total_approved ?? 0,
            'link_count' => count($links),
            'message' => $has_products ? 
                count($formatted) . ' produk APPROVED ditemukan. ' . count($links) . ' produk memiliki link afiliasi.' : 
                'Tidak ada produk APPROVED di campaign ini'
        ]));
        
    } catch (Exception $e) {
        log_message('error', 'get_campaign_products_for_assign error: ' . $e->getMessage());
        log_message('error', 'get_campaign_products_for_assign trace: ' . $e->getTraceAsString());
        
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'products' => []
        ]));
    }
}

// ========================================================================
// GET PRODUCTS WITH LINKS FOR CREATOR (TASK 1 SEND LINK)
// ========================================================================
public function get_creator_products_with_links() {
    // Set header JSON
    $this->output->set_content_type('application/json');
    
    // 🔥 CEK SESSION
    if (!$this->session->userdata('logged_in')) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Session expired. Please login again.',
            'redirect' => base_url('auth/login')
        ]));
    }
    
    $creator_id = $this->input->post('creator_id');
    
    if (!$creator_id) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Creator ID required'
        ]));
    }
    
    // Ambil data creator termasuk kategori
    $creator = $this->db->select('id, username, full_name, phone, category, brand_id')
        ->where('id', $creator_id)
        ->get('creators')
        ->row();
    
    if (!$creator) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Creator not found'
        ]));
    }
    
    // ============================================================
    // 🔥 SOURCE 1: PRODUCT YANG SUDAH DI-ASSIGN KE CREATOR
    // ============================================================
    $assigned_products = $this->db->select('
            acl.product_id,
            acl.product_name,
            acl.campaign_id,
            acl.affiliate_link,
            acl.commission_rate,
            acl.created_at as assigned_at,
            acl.created_by as handler_name,
            acl.created_by_user_id,
            ap.price,
            ap.image_url,
            ap.shop_name,
            ap.category,
            ap.sales_count
        ')
        ->from('affiliate_creator_links acl')
        ->join('affiliate_products ap', 'acl.product_id = ap.product_id AND acl.campaign_id = ap.campaign_id', 'left')
        ->where('acl.creator_id', $creator_id)
        ->where('acl.status', 'ACTIVE')
        ->get()
        ->result();
    
    // ============================================================
    // 🔥 SOURCE 2: PRODUCT DARI creator_products (YANG MATCH)
    // ============================================================
    $creator_products = [];
    if ($this->db->table_exists('creator_products')) {
        $creator_products = $this->db->select('
                cp.product_id,
                cp.product_name,
                cp.price,
                cp.sales_count,
                cp.gmv,
                cp.commission_rate,
                cp.image_url,
                cp.shop_name,
                cp.category,
                cp.is_matched,
                cp.matched_product_id
            ')
            ->from('creator_products cp')
            ->where('cp.creator_id', $creator_id)
            ->where('cp.is_matched', 1)
            ->order_by('cp.gmv', 'DESC')
            ->limit(50)
            ->get()
            ->result();
    }
    
    // ============================================================
    // 🔥 SOURCE 3: PRODUCT REKOMENDASI DARI BD AFFILIATE LINKS
    // ============================================================
    $recommended_products = [];
    if ($this->db->table_exists('bd_affiliate_links')) {
        // Prioritize brand's category if available (Fetched first to avoid Query Builder state pollution)
        $brand_category = null;
        if (!empty($creator->brand_id)) {
            $brand = $this->db->select('category')->where('id', $creator->brand_id)->get('brands')->row();
            if ($brand && !empty($brand->category)) {
                $brand_category = $brand->category;
            }
        }

        // Determine effective category for keyword filtering:
        // 1. Try brand category first
        // 2. If brand category gives no usable keywords (e.g. 'OTHER'), fall back to creator's own category
        // 3. Final fallback: 'Lifestyle'
        $category = null;
        if (!empty($brand_category)) {
            $test_keywords = $this->getCategoryKeywords($brand_category);
            if (!empty($test_keywords)) {
                $category = $brand_category;
            }
        }
        if (empty($category)) {
            $category = $creator->category ?: 'Lifestyle';
        }

        $category_keywords = $this->getCategoryKeywords($category);
        
        // Also get the brand's shop_name for precise brand-based filtering
        $brand_shop_name = null;
        if (!empty($creator->brand_id)) {
            $brand_shop = $this->db->select('shop_name')->where('id', $creator->brand_id)->get('brands')->row();
            if ($brand_shop && !empty($brand_shop->shop_name)) {
                $brand_shop_name = $brand_shop->shop_name;
            }
        }

        if (!empty($brand_shop_name)) {
            // PRIORITY FILTER: Products from the exact brand shop come first
            // Use a UNION: brand shop products first, then keyword-matched products
            $brand_sql = "
                SELECT bal.product_id, bal.product_name, bal.campaign_id, bal.affiliate_link,
                       bal.commission_rate, bal.open_commission_rate, bal.created_by_name,
                       bal.campaign_name, bal.link_type, ap.price, ap.image_url, ap.sales_count,
                       ap.category as product_category, ap.shop_name as product_shop_name,
                       1 as priority_order
                FROM bd_affiliate_links bal
                LEFT JOIN affiliate_products ap ON bal.product_id = ap.product_id AND bal.campaign_id = ap.campaign_id
                WHERE bal.status = 'ACTIVE'
                  AND LOWER(ap.shop_name) = LOWER(?)
            ";
            
            $keyword_conditions = [];
            $keyword_binds = [];
            foreach ($category_keywords as $kw) {
                $kw_lower = strtolower($kw);
                $keyword_conditions[] = "LOWER(ap.category) LIKE ?";
                $keyword_conditions[] = "LOWER(bal.product_name) LIKE ?";
                $keyword_conditions[] = "LOWER(ap.shop_name) LIKE ?";
                $keyword_binds[] = "%{$kw_lower}%";
                $keyword_binds[] = "%{$kw_lower}%";
                $keyword_binds[] = "%{$kw_lower}%";
            }

            if (!empty($keyword_conditions)) {
                $keyword_where = implode(' OR ', $keyword_conditions);
                $other_sql = "
                    SELECT bal.product_id, bal.product_name, bal.campaign_id, bal.affiliate_link,
                           bal.commission_rate, bal.open_commission_rate, bal.created_by_name,
                           bal.campaign_name, bal.link_type, ap.price, ap.image_url, ap.sales_count,
                           ap.category as product_category, ap.shop_name as product_shop_name,
                           2 as priority_order
                    FROM bd_affiliate_links bal
                    LEFT JOIN affiliate_products ap ON bal.product_id = ap.product_id AND bal.campaign_id = ap.campaign_id
                    WHERE bal.status = 'ACTIVE'
                      AND LOWER(ap.shop_name) != LOWER(?)
                      AND ({$keyword_where})
                ";
                $combined_sql = "({$brand_sql}) UNION ({$other_sql}) ORDER BY priority_order ASC, product_shop_name ASC LIMIT 50";
                $binds = array_merge([$brand_shop_name], [$brand_shop_name], $keyword_binds);
                $recommended_products = $this->db->query($combined_sql, $binds)->result();
            } else {
                // No keyword filter available, just show brand shop products
                $recommended_products = $this->db->query($brand_sql . " LIMIT 50", [$brand_shop_name])->result();
            }
        } elseif (!empty($category_keywords)) {
            // No specific brand shop, filter by category keywords only
            $this->db->select('
                    bal.product_id, bal.product_name, bal.campaign_id, bal.affiliate_link,
                    bal.commission_rate, bal.open_commission_rate, bal.created_by_name,
                    bal.campaign_name, bal.link_type, ap.price, ap.image_url, ap.sales_count,
                    ap.category as product_category, ap.shop_name as product_shop_name
                ')
                ->from('bd_affiliate_links bal')
                ->join('affiliate_products ap', 'bal.product_id = ap.product_id AND bal.campaign_id = ap.campaign_id', 'left')
                ->where('bal.status', 'ACTIVE');
            $this->db->group_start();
            foreach ($category_keywords as $keyword) {
                $this->db->or_like('LOWER(ap.category)', strtolower($keyword));
                $this->db->or_like('LOWER(bal.product_name)', strtolower($keyword));
                $this->db->or_like('LOWER(ap.shop_name)', strtolower($keyword));
            }
            $this->db->group_end();

            $recommended_products = $this->db->order_by('bal.created_at', 'DESC')
                ->limit(50)
                ->get()
                ->result();
        } else {
            // Fallback: no filter, return latest 50 active products
            $recommended_products = $this->db->select('
                    bal.product_id, bal.product_name, bal.campaign_id, bal.affiliate_link,
                    bal.commission_rate, bal.open_commission_rate, bal.created_by_name,
                    bal.campaign_name, bal.link_type, ap.price, ap.image_url, ap.sales_count,
                    ap.category as product_category, ap.shop_name as product_shop_name
                ')
                ->from('bd_affiliate_links bal')
                ->join('affiliate_products ap', 'bal.product_id = ap.product_id AND bal.campaign_id = ap.campaign_id', 'left')
                ->where('bal.status', 'ACTIVE')
                ->order_by('bal.created_at', 'DESC')
                ->limit(50)
                ->get()
                ->result();
        }
    }
    
    // ============================================================
    // 🔥 AMBIL DAFTAR BRAND YANG PERNAH BEKERJA SAMA (COLLABORATED BRANDS)
    // ============================================================
    $collaborated_shops = [];

    // A. Dari affiliate_orders (berdasarkan history penjualan)
    if (!empty($creator->username)) {
        try {
            $qOrders = $this->db->select('DISTINCT(ap.shop_name) as shop_name')
                ->from('affiliate_orders o')
                ->join('affiliate_products ap', 'o.product_id = ap.product_id AND o.campaign_id = ap.campaign_id', 'inner')
                ->where('o.creator_username', $creator->username)
                ->where('ap.shop_name !=', '')
                ->get();
            if ($qOrders) {
                foreach ($qOrders->result() as $row) {
                    $collaborated_shops[strtolower(trim($row->shop_name))] = true;
                }
            }
        } catch (Exception $e) {
            log_message('error', 'get_creator_products_with_links: Error getting brands from orders: ' . $e->getMessage());
        }
    }

    // B. Dari affiliate_creator_links (ACTIVE)
    try {
        $qLinks = $this->db->select('DISTINCT(ap.shop_name) as shop_name')
            ->from('affiliate_creator_links acl')
            ->join('affiliate_products ap', 'acl.product_id = ap.product_id AND acl.campaign_id = ap.campaign_id', 'inner')
            ->group_start()
                ->where('acl.creator_id', $creator_id)
                ->or_where('acl.creator_username', $creator->username)
            ->group_end()
            ->where('ap.shop_name !=', '')
            ->get();
        if ($qLinks) {
            foreach ($qLinks->result() as $row) {
                $collaborated_shops[strtolower(trim($row->shop_name))] = true;
            }
        }
    } catch (Exception $e) {
        log_message('error', 'get_creator_products_with_links: Error getting brands from links: ' . $e->getMessage());
    }

    // C. Dari creator_products (Data FastMoss)
    if ($this->db->table_exists('creator_products')) {
        try {
            $qCreatorProds = $this->db->select('DISTINCT(shop_name) as shop_name')
                ->from('creator_products')
                ->where('creator_id', $creator_id)
                ->where('shop_name !=', '')
                ->get();
            if ($qCreatorProds) {
                foreach ($qCreatorProds->result() as $row) {
                    $collaborated_shops[strtolower(trim($row->shop_name))] = true;
                }
            }
        } catch (Exception $e) {
            log_message('error', 'get_creator_products_with_links: Error getting brands from creator_products: ' . $e->getMessage());
        }
    }

    // D. Dari creator's brand_id (Brand utama creator)
    if (!empty($creator->brand_id)) {
        try {
            $brandObj = $this->db->select('name, shop_name')->where('id', $creator->brand_id)->get('brands')->row();
            if ($brandObj) {
                if (!empty($brandObj->shop_name)) $collaborated_shops[strtolower(trim($brandObj->shop_name))] = true;
                if (!empty($brandObj->name)) $collaborated_shops[strtolower(trim($brandObj->name))] = true;
            }
        } catch (Exception $e) {
            log_message('error', 'get_creator_products_with_links: Error getting brand by id: ' . $e->getMessage());
        }
    }

    // ============================================================
    // 🔥 GABUNGKAN SEMUA PRODUK
    // ============================================================
    $all_products = [];
    $processed_ids = [];
    
    // 1. Tambahkan product yang sudah di-assign (prioritas tertinggi)
    foreach ($assigned_products as $p) {
        $p->is_assigned = true;
        $p->is_available = true;
        $p->is_recommended = false;
        $p->source = 'assigned';
        $p->gmv = 0;
        $all_products[] = $p;
        $processed_ids[] = $p->product_id;
    }
    
    // 2. Tambahkan product dari creator_products yang match (belum di-assign)
    foreach ($creator_products as $p) {
        if (!in_array($p->product_id, $processed_ids)) {
            $p->is_assigned = false;
            $p->is_available = true;
            $p->is_recommended = false;
            $p->source = 'creator_product';
            $all_products[] = $p;
            $processed_ids[] = $p->product_id;
        }
    }
    
    // 3. Tambahkan product rekomendasi (belum di-assign dan belum match)
    foreach ($recommended_products as $p) {
        if (!in_array($p->product_id, $processed_ids)) {
            $p->is_assigned = false;
            $p->is_available = true;
            $p->is_recommended = true;
            $p->source = 'recommended';
            $p->gmv = 0;
            // Gunakan category dan shop_name dari affiliate_products
            $p->category = $p->product_category ?? '';
            $p->shop_name = $p->product_shop_name ?? '';
            $all_products[] = $p;
            $processed_ids[] = $p->product_id;
        }
    }
    
    // ============================================================
    // 🔥 FORMAT RESPONSE
    // ============================================================
    $formatted_products = [];
    foreach ($all_products as $p) {
        $shop_key = strtolower(trim($p->shop_name ?? ''));
        $has_collab = ($p->is_assigned ?? false) || (!empty($shop_key) && isset($collaborated_shops[$shop_key]));

        $formatted_products[] = [
            'product_id' => $p->product_id,
            'product_name' => $p->product_name,
            'price' => floatval($p->price ?? 0),
            'image_url' => $p->image_url ?? '',
            'shop_name' => $p->shop_name ?? '',
            'category' => $p->category ?? '',
            'sales_count' => intval($p->sales_count ?? 0),
            'gmv' => floatval($p->gmv ?? 0),
            'commission_rate' => floatval($p->commission_rate ?? 0),
            'is_assigned' => $p->is_assigned ?? false,
            'is_available' => $p->is_available ?? true,
            'is_recommended' => $p->is_recommended ?? false,
            'source' => $p->source ?? 'unknown',
            'source_label' => $this->getSourceLabel($p->source ?? 'unknown'),
            'campaign_id' => $p->campaign_id ?? null,
            'affiliate_link' => $p->affiliate_link ?? null,
            'link_type' => $p->link_type ?? null,
            'bd_created_by' => $p->created_by_name ?? null,
            'campaign_name' => $p->campaign_name ?? null,
            'is_matched' => isset($p->is_matched) ? intval($p->is_matched) : 0,
            'matched_product_id' => $p->matched_product_id ?? null,
            'handler_name' => $p->handler_name ?? null,
            'created_by_user_id' => $p->created_by_user_id ?? null,
            'has_collaborated_brand' => $has_collab
        ];
    }
    
    // ============================================================
    // 🔥 URUTKAN: ASSIGNED > CREATOR_PRODUCT > RECOMMENDED
    // ============================================================
    usort($formatted_products, function($a, $b) {
        $priority = [
            'assigned' => 0,
            'creator_product' => 1,
            'recommended' => 2,
            'unknown' => 3
        ];
        $pa = $priority[$a['source']] ?? 3;
        $pb = $priority[$b['source']] ?? 3;
        if ($pa != $pb) return $pa - $pb;
        return ($b['gmv'] ?? 0) - ($a['gmv'] ?? 0);
    });
    
    $assigned_count = count(array_filter($formatted_products, function($p) { return $p['is_assigned']; }));
    $creator_count = count(array_filter($formatted_products, function($p) { return $p['source'] == 'creator_product' && !$p['is_assigned']; }));
    $recommended_count = count(array_filter($formatted_products, function($p) { return $p['source'] == 'recommended' && !$p['is_assigned']; }));
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'creator' => [
            'id' => $creator->id,
            'username' => $creator->username,
            'full_name' => $creator->full_name,
            'phone' => $creator->phone,
            'category' => $creator->category,
            'brand_id' => $creator->brand_id
        ],
        'products' => $formatted_products,
        'total' => count($formatted_products),
        'assigned_count' => $assigned_count,
        'creator_count' => $creator_count,
        'recommended_count' => $recommended_count,
        'category_keywords' => $category_keywords ?? [],
        'has_links' => count($formatted_products) > 0,
        'message' => count($formatted_products) > 0 ? 
            "Found " . count($formatted_products) . " products (" . $assigned_count . " assigned, " . $creator_count . " from creator, " . $recommended_count . " recommended)" : 
            "No products found"
    ]));
}

/**
 * Get source label
 */
private function getSourceLabel($source) {
    $labels = [
        'assigned' => 'Sudah Di-Assign',
        'creator_product' => 'Dari Creator',
        'recommended' => 'Rekomendasi',
        'unknown' => 'Unknown'
    ];
    return $labels[$source] ?? $source;
}

/**
 * Get category keywords for filtering
 */
private function getCategoryKeywords($category) {
    $category_map = [
        'Beauty' => [
            // Brand/shop names yang pasti beauty
            'hanasui', 'somethinc', 'scarlett', 'wardah', 'emina', 'ms glow', 'makeupuccino',
            'whitelab', 'npure', 'hiqween', 'lumiwhite', 'purbasari', 'skintific',
            // Kategori produk beauty (bahasa Indonesia)
            'skincare', 'makeup', 'kosmetik', 'kecantikan', 'perawatan wajah', 'perawatan kulit',
            'serum', 'toner', 'moisturizer', 'sunscreen', 'pelembab', 'pembersih wajah',
            'lipstik', 'lipstick', 'cushion', 'foundation', 'bedak', 'blush', 'eyeshadow',
            'maskara', 'mascara', 'eyeliner', 'alis', 'lipliner', 'lulur', 'scrub badan',
            'micellar', 'face wash', 'face mask', 'sheet mask', 'retinol', 'niacinamide',
            'brightening', 'whitening', 'glowing', 'acne', 'jerawat',
            // Kategori TikTok shop yang umum untuk beauty
            'suplemen kecantikan', 'serum & essence', 'facial sunscreen', 'perawatan jerawat',
            'pembersih wajah', 'makeup remover', 'moisturiser', 'concealer', 'lipstick',
            'pensil & gel alis', 'perawatan mata', 'blusher', 'eyeshadow', 'toner',
            'kit perawatan kulit', 'perawatan bibir', 'semprotan fixer', 'face scrub',
        ],
        'Fashion' => [
            // Kategori fashion (Indonesian context)
            'fashion', 'pakaian', 'busana', 'baju', 'kemeja', 'dress', 'rok', 'celana',
            'jaket', 'hoodie', 'sweater', 'kaos', 'blouse', 'gamis', 'hijab', 'pashmina',
            'kerudung', 'jilbab', 'abaya', 'tunik', 'kaftan',
            // Alas kaki
            'sepatu', 'sandal', 'sneakers', 'heels', 'boots', 'flat shoes', 'wedges',
            // Aksesori fashion
            'tas wanita', 'tas pria', 'dompet', 'ikat pinggang', 'topi', 'kacamata',
            'anting', 'kalung', 'gelang', 'cincin', 'jam tangan',
            // Kategori TikTok shop
            'sepatu kasual', 'sepatu mary jane', 'sandal & sandal jepit', 'ransel', 'tote bag',
            'tas selempang', 'tas perjalanan', 'setelan pakaian', 'bra', 'knicker',
        ],
        'Tech' => [
            'elektronik', 'gadget', 'smartphone', 'handphone', 'laptop', 'tablet', 'komputer',
            'kamera', 'speaker', 'earphone', 'headphone', 'headset', 'charger', 'powerbank',
            'smartwatch', 'gaming', 'peripheral', 'keyboard', 'mouse', 'monitor',
            'earphone & headphone', 'aksesoris hp', 'casing hp',
        ],
        'Lifestyle' => [
            'lifestyle', 'dekorasi', 'rumah', 'interior', 'furnitur', 'lampu',
            'peralatan rumah', 'perlengkapan rumah', 'storage', 'organizer', 'rak',
            'wewangian rumah', 'lilin aromaterapi', 'diffuser',
        ],
        'Gaming' => ['gaming', 'game', 'console', 'controller', 'headset gaming', 'keyboard gaming', 'mouse gaming', 'gamer'],
        'Food' => [
            'makanan', 'minuman', 'snack', 'camilan', 'kopi', 'teh', 'coklat', 'biskuit',
            'keripik', 'mie', 'bumbu', 'saus', 'kecap', 'minyak goreng', 'tepung',
            'susu', 'jus', 'minuman kesehatan', 'suplemen makanan',
            'makanan beku', 'frozen food', 'bakery', 'roti', 'kue',
            'pasta & bumbu masak', 'penambah rasa', 'saus masak', 'kacang-kacangan',
            'alat pemroses kopi', 'pewarna makanan',
        ],
        'Travel' => [
            'travel', 'wisata', 'liburan', 'koper', 'backpack ransel', 'travel bag',
            'luggage', 'paspor', 'dompet travel',
        ],
        'Sports' => [
            'olahraga', 'sport', 'fitness', 'gym', 'yoga', 'lari', 'sepeda', 'renang',
            'sepatu olahraga', 'sepatu lari', 'baju olahraga', 'peralatan olahraga',
            'suplemen olahraga', 'protein', 'whey', 'dumbbell', 'matras yoga',
        ],
        'Home & Living' => [
            'peralatan dapur', 'peralatan masak', 'kitchen', 'dapur',
            'panci', 'wajan', 'pisau dapur', 'talenan', 'spatula', 'sodet',
            'botol minum', 'tumbler', 'termos', 'gelas', 'piring', 'mangkuk',
            'pembersih rumah', 'pembersih lantai', 'sabun cuci', 'pel',
            'set blok pisau', 'peralatan memasak', 'botol air', 'talenan', 'termos vakum',
            'sikat & kop sedot toilet', 'pembersih rumah tangga', 'pelindung percikan',
        ],
        'Health' => [
            'kesehatan', 'vitamin', 'suplemen', 'obat', 'herbal', 'apotek',
            'masker medis', 'sarung tangan', 'termometer', 'tensimeter',
            'vitamin, mineral & suplemen', 'suplemen kesehatan',
        ],
        'Baby & Kids' => [
            'bayi', 'anak', 'balita', 'baby', 'kids', 'mainan anak', 'popok', 'susu bayi',
            'mpasi', 'perlengkapan bayi', 'stroller', 'gendongan',
            'perawatan kulit bayi', 'sabun bayi', 'lotion bayi', 'bedak bayi',
        ],
    ];
    
    $category_lower = strtolower($category);
    if ($category_lower === 'electronics') {
        $category_lower = 'tech';
    } elseif ($category_lower === 'mom_baby') {
        $category_lower = 'baby & kids';
    }
    
    foreach ($category_map as $key => $keywords) {
        if (strpos($category_lower, strtolower($key)) !== false || $category_lower == strtolower($key)) {
            return $keywords;
        }
    }
    
    return [];
}

// ========================================================================
// UPDATE CREATOR PHONE (TASK 1)
// ========================================================================
public function update_creator_phone_task1() {
    $this->output->set_content_type('application/json');
    
    if (!$this->session->userdata('logged_in')) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Session expired'
        ]));
    }
    
    $creator_id = $this->input->post('creator_id');
    $phone = $this->input->post('phone');
    
    if (!$creator_id) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Creator ID required'
        ]));
    }
    
    if (empty($phone)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Nomor WhatsApp tidak boleh kosong'
        ]));
    }
    
    // Format phone
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    if (preg_match('/^0/', $phone)) {
        $phone = '62' . substr($phone, 1);
    } elseif (preg_match('/^\+/', $phone)) {
        $phone = substr($phone, 1);
    } elseif (!preg_match('/^62/', $phone) && strlen($phone) > 0) {
        $phone = '62' . $phone;
    }
    
    $this->db->where('id', $creator_id);
    $result = $this->db->update('creators', [
        'phone' => $phone,
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    if ($result) {
        return $this->output->set_output(json_encode([
            'success' => true,
            'phone' => $phone,
            'message' => 'Nomor WhatsApp berhasil diupdate'
        ]));
    } else {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Gagal update nomor WhatsApp'
        ]));
    }
}

// ========================================================================
// FETCH PHONE/WA FROM TAP API (TASK 1) 
// ========================================================================
public function get_creator_phone_from_tap() {
    $this->output->set_content_type('application/json');

    if (!$this->session->userdata('logged_in')) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Session expired'
        ]));
    }

    $creator_id = $this->input->post('creator_id');

    if (!$creator_id) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Creator ID required'
        ]));
    }

    // Ambil data creator dari DB
    $creator = $this->db->select('id, username, phone, tiktok_open_id')
        ->where('id', $creator_id)
        ->get('creators')
        ->row();

    if (!$creator) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Creator not found'
        ]));
    }

    // Jika sudah ada nomor WA, return langsung
    if (!empty($creator->phone)) {
        return $this->output->set_output(json_encode([
            'success' => true,
            'phone' => $creator->phone,
            'source' => 'database',
            'message' => 'Nomor WA sudah tersedia di database'
        ]));
    }

    // Pastikan tiktok_open_id tersedia — coba resolve dulu jika kosong
    $tiktok_open_id = $creator->tiktok_open_id;

    if (empty($tiktok_open_id) && !empty($creator->username)) {
        try {
            $this->load->model('BrandCreator_model');
            $fastmoss_uid = $this->BrandCreator_model->find_creator_in_fastmoss($creator->username);
            if ($fastmoss_uid) {
                $this->db->where('id', $creator_id)->update('creators', [
                    'tiktok_open_id' => $fastmoss_uid,
                    'updated_at'     => date('Y-m-d H:i:s')
                ]);
                $tiktok_open_id = $fastmoss_uid;
                log_message('debug', 'get_creator_phone_from_tap: resolved tiktok_open_id=' . $tiktok_open_id);
            }
        } catch (Exception $e) {
            log_message('error', 'get_creator_phone_from_tap: resolve open_id failed: ' . $e->getMessage());
        }
    }

    if (empty($tiktok_open_id)) {
        // Fallback: cari via TAP marketplace search dengan username
        try {
            $search_result = $this->jsm_api->search_creators_by_is($creator->username, null, 20);
            if ($search_result['success'] && !empty($search_result['data']['creators'])) {
                foreach ($search_result['data']['creators'] as $tap_creator) {
                    if (strtolower($tap_creator['username'] ?? '') === strtolower($creator->username)) {
                        if (!empty($tap_creator['creator_open_id'])) {
                            $tiktok_open_id = $tap_creator['creator_open_id'];
                            $this->db->where('id', $creator_id)->update('creators', [
                                'tiktok_open_id' => $tiktok_open_id,
                                'updated_at'     => date('Y-m-d H:i:s')
                            ]);
                            log_message('debug', 'get_creator_phone_from_tap: found open_id via search=' . $tiktok_open_id);
                            break;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            log_message('error', 'get_creator_phone_from_tap: search fallback failed: ' . $e->getMessage());
        }
    }

    if (empty($tiktok_open_id)) {
        return $this->output->set_output(json_encode([
            'success'  => false,
            'message'  => 'TikTok Open ID tidak ditemukan untuk creator ini. Nomor WA tidak bisa diambil dari TAP.',
            'username' => $creator->username
        ]));
    }

    // Ambil detail creator dari TAP API (v202509)
    try {
        $tap_result = $this->jsm_api->get_creator_detail_by_id($tiktok_open_id);

        log_message('debug', 'get_creator_phone_from_tap TAP response: ' . json_encode($tap_result));

        if (!$tap_result['success']) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'TAP API error: ' . ($tap_result['message'] ?? 'Unknown error'),
                'tap_code' => $tap_result['code'] ?? null
            ]));
        }

        $tap_data    = $tap_result['data'] ?? [];
        $tap_creator = $tap_data['creator'] ?? $tap_data;

        // Prioritas 1: field phone langsung di response
        $phone = '';
        $phone_fields = [
            'phone_number', 'phone', 'mobile', 'whatsapp', 'wa_number',
            'contact_phone', 'contact_number', 'telephone'
        ];

        foreach ($phone_fields as $field) {
            if (!empty($tap_creator[$field])) {
                $phone = $tap_creator[$field];
                log_message('debug', 'get_creator_phone_from_tap: found phone in field "' . $field . '": ' . $phone);
                break;
            }
        }

        // Prioritas 2: sub-object contact_info
        if (empty($phone) && !empty($tap_creator['contact_info'])) {
            $contact = $tap_creator['contact_info'];
            foreach ($phone_fields as $field) {
                if (!empty($contact[$field])) {
                    $phone = $contact[$field];
                    log_message('debug', 'get_creator_phone_from_tap: found phone in contact_info.' . $field . ': ' . $phone);
                    break;
                }
            }
        }

        // Prioritas 3: parse nomor telepon dari bio_description (tampil sebagai "Endorsement" di TAP UI)
        // Creator sering menyimpan nomor WA, IG, dll di bio mereka dalam format teks bebas
        if (empty($phone)) {
            $bio_text = $tap_creator['bio_description']
                ?? $tap_creator['bio']
                ?? $tap_creator['description']
                ?? '';
            if (!empty($bio_text)) {
                $phone = $this->extractPhoneFromBio($bio_text);
                if (!empty($phone)) {
                    log_message('info', 'get_creator_phone_from_tap: extracted phone from bio_description: ' . $phone);
                }
            }
        }

        if (empty($phone)) {
            $bio_preview = substr($tap_creator['bio_description'] ?? $tap_creator['bio'] ?? '', 0, 100);
            log_message('info', 'get_creator_phone_from_tap: no phone found. bio="' . $bio_preview . '" keys=' . implode(', ', array_keys($tap_creator)));
            
            // Simpan 'no_phone' ke DB agar CA team tahu harus mencari manual
            $this->db->where('id', $creator_id)->update('creators', [
                'phone'      => 'no_phone',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            return $this->output->set_output(json_encode([
                'success'          => false,
                'phone'            => 'no_phone',
                'message'          => 'Nomor WA tidak ditemukan di profil TAP creator ini (tidak ada di bio/deskripsi)',
                'tap_bio_preview'  => $bio_preview,
                'tap_fields_found' => array_keys($tap_creator),
                'username'         => $creator->username
            ]));
        }

        // Format nomor
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        if (preg_match('/^0/', $phone)) {
            $phone = '62' . substr($phone, 1);
        } elseif (preg_match('/^\+/', $phone)) {
            $phone = substr($phone, 1);
        } elseif (!preg_match('/^62/', $phone) && strlen($phone) > 0) {
            $phone = '62' . $phone;
        }

        // Simpan ke DB
        $this->db->where('id', $creator_id)->update('creators', [
            'phone'      => $phone,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        log_message('info', 'get_creator_phone_from_tap: saved phone=' . $phone . ' for creator_id=' . $creator_id);

        return $this->output->set_output(json_encode([
            'success' => true,
            'phone'   => $phone,
            'source'  => 'tap_api',
            'message' => 'Nomor WA berhasil diambil dari TAP API'
        ]));

    } catch (Exception $e) {
        log_message('error', 'get_creator_phone_from_tap exception: ' . $e->getMessage());
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Exception: ' . $e->getMessage()
        ]));
    }
}

// ========================================================================
// SEND LINK TO CREATOR (TASK 1)
// ========================================================================

// ========================================================================
// BATCH FETCH PHONE/WA FROM TAP API (AUTO ON PAGE LOAD)
// POST: creator_ids[] — max 20 creator per request
// ========================================================================
public function batch_fetch_phones() {
    $this->output->set_content_type('application/json');

    if (!$this->session->userdata('logged_in')) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Session expired'
        ]));
    }

    $creator_ids = $this->input->post('creator_ids');

    if (empty($creator_ids) || !is_array($creator_ids)) {
        return $this->output->set_output(json_encode([
            'success' => true,
            'results' => [],
            'message' => 'No creator IDs provided'
        ]));
    }

    // Batasi max 20 per request agar tidak timeout
    $creator_ids = array_slice(array_map('intval', $creator_ids), 0, 20);

    // Ambil semua creator sekaligus (1 query)
    $creators = $this->db->select('id, username, phone, tiktok_open_id')
        ->where_in('id', $creator_ids)
        ->where('(phone IS NULL OR phone = \'\')', null, false)
        ->get('creators')
        ->result();

    if (empty($creators)) {
        return $this->output->set_output(json_encode([
            'success' => true,
            'results' => [],
            'message' => 'All creators already have phone numbers'
        ]));
    }

    $phone_fields = [
        'phone_number', 'phone', 'mobile', 'whatsapp', 'wa_number',
        'contact_phone', 'contact_number', 'telephone'
    ];

    $results = [];

    foreach ($creators as $creator) {
        $result_item = [
            'id'       => $creator->id,
            'username' => $creator->username,
            'phone'    => null,
            'found'    => false,
            'source'   => null,
        ];

        $open_id = $creator->tiktok_open_id;

        // Resolve tiktok_open_id jika kosong
        if (empty($open_id) && !empty($creator->username)) {
            try {
                $search = $this->jsm_api->search_creators_by_is($creator->username, null, 20);
                if ($search['success'] && !empty($search['data']['creators'])) {
                    foreach ($search['data']['creators'] as $tc) {
                        if (strtolower($tc['username'] ?? '') === strtolower($creator->username)) {
                            if (!empty($tc['creator_open_id'])) {
                                $open_id = $tc['creator_open_id'];
                                $this->db->where('id', $creator->id)->update('creators', [
                                    'tiktok_open_id' => $open_id,
                                    'updated_at'     => date('Y-m-d H:i:s')
                                ]);
                                log_message('debug', 'batch_fetch_phones: resolved open_id for ' . $creator->username . ' → ' . $open_id);
                                break;
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                log_message('error', 'batch_fetch_phones: search open_id failed for ' . $creator->username . ': ' . $e->getMessage());
            }
        }

        if (empty($open_id)) {
            $result_item['source'] = 'no_open_id';
            $results[] = $result_item;
            continue;
        }

        // Ambil detail dari TAP API
        try {
            $tap_result = $this->jsm_api->get_creator_detail_by_id($open_id);

            if (!$tap_result['success']) {
                log_message('debug', 'batch_fetch_phones: TAP API failed for ' . $creator->username . ': ' . ($tap_result['message'] ?? ''));
                $result_item['source'] = 'tap_error';
                $results[] = $result_item;
                continue;
            }

            $tap_data    = $tap_result['data'] ?? [];
            $tap_creator = $tap_data['creator'] ?? $tap_data;

            $phone = '';

            // Prioritas 1: field phone langsung
            foreach ($phone_fields as $field) {
                if (!empty($tap_creator[$field])) {
                    $phone = $tap_creator[$field];
                    break;
                }
            }

            // Prioritas 2: sub-object contact_info
            if (empty($phone) && !empty($tap_creator['contact_info'])) {
                foreach ($phone_fields as $field) {
                    if (!empty($tap_creator['contact_info'][$field])) {
                        $phone = $tap_creator['contact_info'][$field];
                        break;
                    }
                }
            }

            // Prioritas 3: parse dari bio_description / teks bio
            if (empty($phone)) {
                $bio_text = $tap_creator['bio_description']
                    ?? $tap_creator['bio']
                    ?? $tap_creator['description']
                    ?? '';
                if (!empty($bio_text)) {
                    $phone = $this->extractPhoneFromBio($bio_text);
                }
            }

            if (!empty($phone)) {
                // Format ke standar 62xxx
                $phone = preg_replace('/[^0-9+]/', '', $phone);
                if (preg_match('/^0/', $phone)) {
                    $phone = '62' . substr($phone, 1);
                } elseif (preg_match('/^\+/', $phone)) {
                    $phone = substr($phone, 1);
                } elseif (!preg_match('/^62/', $phone) && strlen($phone) > 0) {
                    $phone = '62' . $phone;
                }

                // Simpan ke DB
                $this->db->where('id', $creator->id)->update('creators', [
                    'phone'      => $phone,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                log_message('info', 'batch_fetch_phones: saved phone=' . $phone . ' for creator_id=' . $creator->id);

                $result_item['phone']  = $phone;
                $result_item['found']  = true;
                $result_item['source'] = 'tap_api';
            } else {
                log_message('debug', 'batch_fetch_phones: no phone found for ' . $creator->username . ', saving "no_phone" to DB');
                // Simpan 'no_phone' ke DB agar CA team tahu harus mencari manual
                $this->db->where('id', $creator->id)->update('creators', [
                    'phone'      => 'no_phone',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                $result_item['phone']  = 'no_phone';
                $result_item['found']  = false;
                $result_item['source'] = 'not_found';
            }

        } catch (Exception $e) {
            log_message('error', 'batch_fetch_phones: exception for ' . $creator->username . ': ' . $e->getMessage());
            $result_item['source'] = 'exception';
        }

        $results[] = $result_item;
        
        // Jeda 1.5 detik untuk menghindari rate limit API TikTok (Too many requests downstream)
        usleep(1500000);
    }

    $found_count = count(array_filter($results, fn($r) => $r['found']));

    return $this->output->set_output(json_encode([
        'success'     => true,
        'processed'   => count($results),
        'found'       => $found_count,
        'results'     => $results,
        'message'     => "Berhasil menemukan $found_count dari " . count($results) . " nomor WA"
    ]));
}


public function send_link_task1() {
    $this->output->set_content_type('application/json');
    
    if (!$this->session->userdata('logged_in')) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Session expired'
        ]));
    }
    
    $creator_id = $this->input->post('creator_id');
    $product_id = $this->input->post('product_id');
    $link = $this->input->post('link');
    $message = $this->input->post('message');
    $campaign_id = $this->input->post('campaign_id');
    
    if (!$creator_id || !$product_id || !$link) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Missing required data'
        ]));
    }
    
    $creator = $this->db->where('id', $creator_id)->get('creators')->row();
    if (!$creator) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Creator not found'
        ]));
    }
    
    if (empty($creator->phone)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Creator has no WhatsApp number'
        ]));
    }
    
    // Format phone
    $phone = preg_replace('/[^0-9+]/', '', $creator->phone);
    if (preg_match('/^0/', $phone)) {
        $phone = '+62' . substr($phone, 1);
    } elseif (!preg_match('/^\+/', $phone)) {
        $phone = '+' . $phone;
    }
    $cleanPhone = ltrim($phone, '+');
    
    // Simpan/Dapatkan link_id untuk link saat ini
    $existing = $this->db->where('creator_id', $creator_id)
        ->where('product_id', $product_id)
        ->where('campaign_id', $campaign_id)
        ->get('affiliate_creator_links')
        ->row();
    
    $link_id = $existing ? $existing->link_id : md5($creator->username . $campaign_id . $product_id);
    if (empty($link_id)) {
        $link_id = md5($creator->username . $campaign_id . $product_id);
    }
    
    if (!$existing) {
        $product = $this->db->select('product_name')
            ->where('product_id', $product_id)
            ->get('affiliate_products')
            ->row();
        
        $link_data = [
            'link_id' => $link_id,
            'creator_id' => $creator_id,
            'creator_username' => $creator->username,
            'campaign_id' => $campaign_id,
            'product_id' => $product_id,
            'product_name' => $product->product_name ?? '',
            'affiliate_link' => $link,
            'shared_date' => date('Y-m-d H:i:s'),
            'status' => 'ACTIVE',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('affiliate_creator_links', $link_data);
    } else if (empty($existing->link_id)) {
        $this->db->where('id', $existing->id)->update('affiliate_creator_links', [
            'link_id' => $link_id,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    // Ganti raw link dengan redirect link di dalam pesan WhatsApp
    $creator_links = $this->db->where('creator_id', $creator_id)->get('affiliate_creator_links')->result();
    foreach ($creator_links as $cl) {
        if (!empty($cl->link_id)) {
            $redirect_url = base_url('r/' . $cl->link_id);
            $message = str_replace($cl->affiliate_link, $redirect_url, $message);
        }
    }
    // Backup: Ganti link saat ini juga jika belum masuk DB
    $redirect_url = base_url('r/' . $link_id);
    $message = str_replace($link, $redirect_url, $message);

    // Log WhatsApp
    $this->db->insert('whatsapp_logs', [
        'creator_id' => $creator_id,
        'user_id' => $this->session->userdata('user_id'),
        'user_name' => $this->session->userdata('full_name') ?: $this->session->userdata('username'),
        'phone_number' => $phone,
        'message' => $message,
        'link' => $redirect_url,
        'link_type' => 'task1_send_link',
        'status' => 'SENT',
        'sent_at' => date('Y-m-d H:i:s')
    ]);
    
    // Update status creator ke LINK_SENT jika masih PENDING
    if ($creator->status == 'PENDING') {
        $this->db->where('id', $creator_id)->update('creators', [
            'status' => 'LINK_SENT',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    $whatsapp_url = "https://wa.me/{$cleanPhone}?text=" . urlencode($message);
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'message' => 'WhatsApp opened',
        'redirect_url' => $whatsapp_url,
        'phone' => $cleanPhone
    ]));
}

// =====================================================================
// AUTO CREATOR SCOUTING — IS ENDPOINTS
// =====================================================================

/**
 * AJAX — Ambil scouting list
 */
public function get_scouting_list() {
    $this->output->set_content_type('application/json');

    if (!$this->session->userdata('logged_in')) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Session expired']));
    }

    $this->load->model('CreatorScouting_model');

    $filters = [
        'status'   => ['pending', 'contacted'],
        'brand_id' => $this->input->get('brand_id') ?: null,
        'source'   => $this->input->get('source')   ?: null,
        'search'   => $this->input->get('search')   ?: '',
        'limit'    => 50,
        'offset'   => intval($this->input->get('offset') ?: 0),
    ];

    $list   = $this->CreatorScouting_model->get_scouting_list($filters);
    $total  = $this->CreatorScouting_model->get_scouting_count(['pending', 'contacted'], $filters['brand_id']);
    $brands = $this->CreatorScouting_model->get_brands_in_scouting();

    return $this->output->set_output(json_encode([
        'success' => true,
        'total'   => $total,
        'data'    => $list,
        'brands'  => $brands,
    ]));
}

/**
 * AJAX — Hubungi creator dari scouting list (WhatsApp redirect & update status)
 * POST params: scouting_id, phone (optional)
 */
public function get_scouting_contact_link() {
    $this->output->set_content_type('application/json');

    if (!$this->session->userdata('logged_in')) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Session expired']));
    }

    $scouting_id = $this->input->post('scouting_id');
    $input_phone = $this->input->post('phone');

    if (!$scouting_id) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Scouting ID wajib diisi']));
    }

    $this->load->model('CreatorScouting_model');
    $item = $this->CreatorScouting_model->get_by_id($scouting_id);

    if (!$item) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Scouting item tidak ditemukan']));
    }

    if ($input_phone) {
        $this->db->where('id', $scouting_id)->update('creator_scouting', [
            'phone'      => $input_phone,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        $item->phone = $input_phone;
    }

    if (empty($item->phone)) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Creator tidak memiliki nomor WhatsApp']));
    }

    // Cari link di bd_affiliate_links
    $link_row = $this->db->select('affiliate_link')
                         ->from('bd_affiliate_links')
                         ->where('campaign_id', $item->campaign_id)
                         ->where('product_id', $item->product_id)
                         ->where('status', 'ACTIVE')
                         ->limit(1)
                         ->get()
                         ->row();

    // Fallback: cari link lain dari campaign_id yang sama
    if (!$link_row) {
        $link_row = $this->db->select('affiliate_link')
                             ->from('bd_affiliate_links')
                             ->where('campaign_id', $item->campaign_id)
                             ->where('status', 'ACTIVE')
                             ->limit(1)
                             ->get()
                             ->row();
    }

    if (!$link_row) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Belum ada link afiliasi aktif untuk campaign ini. Silakan hubungi BD/BA untuk membuat link terlebih dahulu.'
        ]));
    }

    $affiliate_link = $link_row->affiliate_link;

    // Update status di creator_scouting ke 'contacted'
    $user_id = $this->session->userdata('user_id');
    $this->db->where('id', $scouting_id)->update('creator_scouting', [
        'status'       => 'contacted',
        'contacted_by' => $user_id,
        'contacted_at' => date('Y-m-d H:i:s'),
        'updated_at'   => date('Y-m-d H:i:s')
    ]);

    // Format phone
    $phone = preg_replace('/[^0-9+]/', '', $item->phone);
    if (preg_match('/^0/', $phone)) {
        $phone = '+62' . substr($phone, 1);
    } elseif (!preg_match('/^\+/', $phone)) {
        $phone = '+' . $phone;
    }
    $cleanPhone = ltrim($phone, '+');

    $ca_name = $this->session->userdata('full_name') ?: $this->session->userdata('username');
    $message = "Halo Kak @" . $item->username . ",\n\nPerkenalkan saya " . $ca_name . " dari Toopai. Kami sangat menyukai konten Kakak dan ingin mengundang Kakak untuk bekerja sama dalam campaign *{$item->campaign_name}* untuk brand *{$item->brand_name}*.\n\nBerikut adalah Product Palette Link (Link Produk) untuk ditambahkan ke Showcase Kakak:\n" . $affiliate_link . "\n\nTerima kasih, ditunggu kabarnya ya Kak! 😊";

    // Catat data ke whatsapp_logs untuk tracking
    $log_data = [
        'user_id'      => $user_id,
        'brand_id'     => $item->brand_id,
        'phone_number' => $phone,
        'message'      => $message,
        'status'       => 'SENT',
        'sent_at'      => date('Y-m-d H:i:s')
    ];

    $columns = $this->db->list_fields('whatsapp_logs');
    if (in_array('creator_id', $columns)) {
        $log_data['creator_id'] = 0;
    }
    if (in_array('link_type', $columns)) {
        $log_data['link_type'] = 'scouting_contact';
    }
    if (in_array('link', $columns)) {
        $log_data['link'] = $affiliate_link;
    }

    $this->db->insert('whatsapp_logs', $log_data);

    $whatsapp_url = "https://wa.me/{$cleanPhone}?text=" . urlencode($message);

    return $this->output->set_output(json_encode([
        'success'      => true,
        'redirect_url' => $whatsapp_url,
        'message'      => 'WhatsApp url generated successfully'
    ]));
}

/**
 * AJAX — Onboard creator dari scouting list ke Task 1 (PENDING)
 * POST: scouting_id
 */
public function onboard_creator_from_scouting() {
    $this->output->set_content_type('application/json');

    if (!$this->session->userdata('logged_in')) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Session expired']));
    }

    $scouting_id = $this->input->post('scouting_id');
    if (!$scouting_id) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Scouting ID wajib diisi']));
    }

    $this->load->model('CreatorScouting_model');
    $user_id = $this->session->userdata('user_id');

    $result = $this->CreatorScouting_model->onboard_creator($scouting_id, $user_id);

    if ($result['success']) {
        $this->load->model('User_log_model');
        $this->User_log_model->log(
            $user_id,
            $this->session->userdata('username'),
            'IS',
            'ONBOARD_SCOUTING',
            "Onboard creator from scouting ID={$scouting_id}, creator_id={$result['creator_id']}"
        );
    }

    return $this->output->set_output(json_encode($result));
}

/**
 * AJAX — Abaikan creator dari scouting list
 * POST: scouting_id
 */
public function ignore_scouting_creator() {
    $this->output->set_content_type('application/json');

    if (!$this->session->userdata('logged_in')) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Session expired']));
    }

    $scouting_id = $this->input->post('scouting_id');
    if (!$scouting_id) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Scouting ID wajib diisi']));
    }

    $this->load->model('CreatorScouting_model');
    $result = $this->CreatorScouting_model->ignore_creator($scouting_id);

    return $this->output->set_output(json_encode($result));
}

/**
 * Trigger manual populate scouting list (opsional, bisa dari tombol UI)
 */
public function refresh_scouting_list() {
    $this->output->set_content_type('application/json');

    if (!$this->session->userdata('logged_in')) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Session expired']));
    }

    $this->load->model('CreatorScouting_model');

    try {
        $stats = $this->CreatorScouting_model->populate_from_orders();

        $this->load->model('User_log_model');
        $this->User_log_model->log(
            $this->session->userdata('user_id'),
            $this->session->userdata('username'),
            'IS',
            'REFRESH_SCOUTING',
            "Manual refresh: inserted={$stats['inserted']}, skipped_dup={$stats['skipped_duplicate']}"
        );

        $msg = $stats['inserted'] > 0
            ? "✅ Scouting list diperbarui. {$stats['inserted']} creator baru ditemukan."
            : "ℹ️ 0 creator baru. " . implode(' | ', $stats['debug'] ?? []);

        return $this->output->set_output(json_encode([
            'success' => true,
            'message' => $msg,
            'stats'   => $stats,
        ]));
    } catch (Exception $e) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => $e->getMessage()]));
    }
}


// ============================================================================
// FITUR F: PENGIRIMAN SAMPLE PRODUCT CREATOR
// ============================================================================

// --------------------------------------------------------------------------
// F.2 — KONFIRMASI KESEDIAAN CREATOR MENERIMA SAMPLE
// --------------------------------------------------------------------------

/**
 * Konfirmasi apakah creator bersedia menerima sample.
 * Dipanggil dari modal Detail Creator via AJAX POST.
 *
 * POST params: creator_id, willing (1|0), notes
 *
 * Jika NOT willing (0):
 *   → status creator diupdate ke ACTIVE (masuk Monitoring langsung)
 * Jika willing (1):
 *   → status tetap LINK_SENT/SAMPLE_SENT, lanjut ke pemilihan produk
 */
public function confirm_sample_willingness() {
    $this->output->set_content_type('application/json');

    try {
        $creator_id = $this->input->post('creator_id');
        $willing    = intval($this->input->post('willing')); // 1 = Ya, 0 = Tidak
        $notes      = $this->input->post('notes') ?? '';

        if (!$creator_id) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Creator ID required'
            ]));
        }

        $creator = $this->db->where('id', $creator_id)->get('creators')->row();
        if (!$creator) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Creator not found'
            ]));
        }

        $this->load->model('SampleProduct_model');

        // Simpan konfirmasi ke sample_requests
        $this->SampleProduct_model->save_sample_willingness([
            'creator_id'  => $creator_id,
            'campaign_id' => null,
            'willing'     => $willing,
            'notes'       => $notes,
        ]);

        // Jika creator TIDAK bersedia → langsung masuk Monitoring (status ACTIVE)
        if (!$willing) {
            $this->db->where('id', $creator_id)->update('creators', [
                'status'     => 'ACTIVE',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            return $this->output->set_output(json_encode([
                'success'    => true,
                'willing'    => false,
                'new_status' => 'ACTIVE',
                'message'    => 'Creator tidak bersedia menerima sample. Creator dipindahkan ke Monitoring.',
            ]));
        }

        // Jika bersedia → biarkan status saat ini, kembalikan konfirmasi
        return $this->output->set_output(json_encode([
            'success' => true,
            'willing' => true,
            'message' => 'Creator bersedia. Lanjutkan ke pemilihan produk sample.',
        ]));

    } catch (Exception $e) {
        log_message('error', 'confirm_sample_willingness error: ' . $e->getMessage());
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage()
        ]));
    }
}

// --------------------------------------------------------------------------
// F.3 — REKOMENDASI PRODUK SAMPLE
// --------------------------------------------------------------------------

/**
 * Ambil rekomendasi produk sample untuk creator.
 * Berbasis kategori sama, brand berbeda dari produk yang sudah dimiliki.
 *
 * POST params: creator_id
 */
public function get_sample_recommendations() {
    $this->output->set_content_type('application/json');

    try {
        $creator_id = $this->input->post('creator_id');

        if (!$creator_id) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Creator ID required'
            ]));
        }

        $this->load->model('SampleProduct_model');
        $result = $this->SampleProduct_model->get_sample_recommendation($creator_id, 30);

        return $this->output->set_output(json_encode([
            'success'             => true,
            'recommendations'     => $result['recommendations'],
            'creator_brands'      => $result['creator_brands'],
            'creator_categories'  => $result['creator_categories'],
            'total'               => count($result['recommendations']),
        ]));

    } catch (Exception $e) {
        log_message('error', 'get_sample_recommendations error: ' . $e->getMessage());
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage()
        ]));
    }
}

// --------------------------------------------------------------------------
// F.4 — SIMPAN PENGIRIMAN SAMPLE (MANUAL)
// --------------------------------------------------------------------------

/**
 * Simpan data pengiriman sample manual ke database.
 * Menggantikan pencatatan di Google Sheets.
 *
 * POST params: creator_id, product_id, campaign_id, quantity,
 *              shipping_address, brand_id, brand_name, notes, delivery_method
 */
public function save_sample_delivery() {
    $this->output->set_content_type('application/json');

    try {
        $creator_id      = $this->input->post('creator_id');
        $products        = json_decode($this->input->post('products'), true);
        $shipping_address= $this->input->post('shipping_address');
        $delivery_method = $this->input->post('delivery_method') ?: 'manual';
        $tap_request_id  = $this->input->post('tap_request_id') ?: null;

        if (!$creator_id || empty($products)) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Creator ID dan produk wajib diisi'
            ]));
        }

        $this->load->model('SampleProduct_model');

        $saved       = 0;
        $request_ids = [];
        $errors      = [];

        foreach ($products as $product) {
            // Validasi: product_id wajib ada
            if (empty($product['product_id'])) {
                $errors[] = 'product_id kosong, produk dilewati';
                continue;
            }

            $result = $this->SampleProduct_model->save_sample_delivery([
                'creator_id'       => $creator_id,
                'product_id'       => $product['product_id'],
                'campaign_id'      => $product['campaign_id'] ?? null,
                'quantity'         => $product['quantity'] ?? 1,
                'shipping_address' => $shipping_address,
                'delivery_method'  => $delivery_method,
                'tap_request_id'   => $tap_request_id,
                'brand_id'         => $product['brand_id'] ?? null,
                'brand_name'       => $product['brand_name'] ?? null,
                'notes'            => $product['notes'] ?? null,
            ]);

            if ($result['success']) {
                $saved++;
                $request_ids[] = $result['request_id'];
            } else {
                $errors[] = 'Gagal insert product_id=' . $product['product_id'] . ': ' . ($result['message'] ?? '?');
                log_message('error', 'save_sample_delivery: gagal insert product_id=' . $product['product_id']);
            }
        }

        if ($saved === 0) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Tidak ada produk yang berhasil disimpan. ' . implode('; ', $errors),
            ]));
        }

        // Update status creator ke SAMPLE_SENT
        $this->db->where('id', $creator_id)->update('creators', [
            'status'     => 'SAMPLE_SENT',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->output->set_output(json_encode([
            'success'     => true,
            'saved'       => $saved,
            'request_ids' => $request_ids,
            'message'     => "{$saved} produk sample berhasil dicatat. Status creator diperbarui ke SAMPLE_SENT.",
        ]));

    } catch (Exception $e) {
        log_message('error', 'save_sample_delivery error: ' . $e->getMessage());
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage()
        ]));
    }
}

// --------------------------------------------------------------------------
// F.5 — HALAMAN MONITORING CREATOR (PAGE RENDER)
// --------------------------------------------------------------------------

/**
 * Render halaman dedicated Monitoring Creator.
 * URL: /is/monitoring
 */
public function monitoring() {
    $user_id      = $this->session->userdata('user_id');
    $is_supervisor = ($user_id == 2);

    // Ambil semua creator ACTIVE yang di-handle IS ini (atau semua jika supervisor)
    // Urutkan berdasarkan total GMV tertinggi dalam 30 hari terakhir
    $thirty_days_ago = date('Y-m-d', strtotime('-30 days'));
    
    $query = $this->db
        ->select('c.id, c.username, c.full_name, c.avatar_url, c.category, c.status,
                  c.brand_id, c.is_id, b.name as brand_name, u.full_name as is_name,
                  COALESCE(SUM(ao.gmv), 0) as total_gmv_30d,
                  COUNT(DISTINCT ao.order_id) as total_orders_30d')
        ->from('creators c')
        ->join('brands b', 'c.brand_id = b.id', 'left')
        ->join('users u', 'c.is_id = u.id', 'left')
        ->join('affiliate_orders ao', "c.username = ao.creator_username AND ao.order_date_local >= '{$thirty_days_ago}' AND ao.order_status NOT IN ('CANCELLED', 'REFUNDED')", 'left')
        ->where_in('c.status', ['ACTIVE', 'SAMPLE_SENT']);

    if (!$is_supervisor) {
        $query->where('c.is_id', $user_id);
    }

    $creators = $query->group_by(array('c.id', 'b.name', 'u.full_name'))
                      ->order_by('total_gmv_30d', 'DESC')
                      ->limit(200)
                      ->get()
                      ->result();

    // Hitung statistik ringkas per creator
    foreach ($creators as &$creator) {
        $creator->total_gmv_30d   = floatval($creator->total_gmv_30d);
        $creator->total_orders_30d = intval($creator->total_orders_30d);

        // Jumlah sample yang sudah dikirim
        $creator->sample_count = $this->db
            ->where('creator_id', $creator->id)
            ->where('product_id IS NOT NULL')
            ->count_all_results('sample_requests');

        // Jumlah video
        $creator->video_count = 0;
        $tables = $this->db->list_tables();
        if (in_array('creator_content_statistics', $tables)) {
            $creator->video_count = $this->db
                ->where('creator_username', $creator->username)
                ->count_all_results('creator_content_statistics');
        }

        // Apakah ada trigger keranjang kuning (ada transaksi)
        $creator->has_orders = $creator->total_orders_30d > 0;
    }
    unset($creator);

    $data = [
        'title'         => 'Monitoring Creator - Toopai',
        'creators'      => $creators,
        'is_supervisor' => $is_supervisor,
        'total_creators'=> count($creators),
    ];

    $this->load->view('templates/new/header', $data);
    $this->load->view('is/monitoring', $data);
    $this->load->view('templates/new/footer');
}

// --------------------------------------------------------------------------
// F.5 — AJAX: DATA DETAIL MONITORING SATU CREATOR
// --------------------------------------------------------------------------

/**
 * Ambil semua data monitoring detail satu creator (AJAX).
 * Meliputi: GMV breakdown, video, keranjang kuning, sample history & summary.
 *
 * POST params: creator_id, start_date (opt), end_date (opt)
 */
public function get_monitoring_creator_detail() {
    $this->output->set_content_type('application/json');

    try {
        $creator_id = $this->input->post('creator_id');
        $start_date = $this->input->post('start_date') ?: date('Y-m-d', strtotime('-30 days'));
        $end_date   = $this->input->post('end_date')   ?: date('Y-m-d');

        if (!$creator_id) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Creator ID required'
            ]));
        }

        $creator = $this->db
            ->select('c.*, b.name as brand_name, b.shop_name, u.full_name as is_name')
            ->from('creators c')
            ->join('brands b', 'c.brand_id = b.id', 'left')
            ->join('users u', 'c.is_id = u.id', 'left')
            ->where('c.id', $creator_id)
            ->get()->row();

        if (!$creator) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Creator not found'
            ]));
        }

        $this->load->model('SampleProduct_model');

        // GMV Breakdown
        $gmv_data = $this->SampleProduct_model->get_gmv_breakdown(
            $creator->username, $start_date, $end_date
        );

        // Keranjang Kuning
        $keranjang = $this->SampleProduct_model->get_keranjang_kuning($creator->username);

        // Video
        $videos = $this->SampleProduct_model->get_creator_videos($creator_id, $creator->username, 30);

        // Sample History & Summary
        $sample_history = $this->SampleProduct_model->get_creator_sample_history($creator_id);
        $sample_summary = $this->SampleProduct_model->get_creator_sample_summary($creator_id);

        // Konfirmasi kesediaan terakhir
        $last_willing = $this->SampleProduct_model->get_last_willingness($creator_id);

        return $this->output->set_output(json_encode([
            'success'        => true,
            'creator'        => $creator,
            'gmv'            => $gmv_data,
            'keranjang'      => $keranjang,
            'videos'         => $videos,
            'sample_history' => $sample_history,
            'sample_summary' => $sample_summary,
            'last_willing'   => $last_willing,
            'date_range'     => ['start' => $start_date, 'end' => $end_date],
        ]));

    } catch (Exception $e) {
        log_message('error', 'get_monitoring_creator_detail error: ' . $e->getMessage());
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage()
        ]));
    }
}

// --------------------------------------------------------------------------
// F.5 — AJAX: GMV BREAKDOWN PER PRODUK (POPUP)
// --------------------------------------------------------------------------

/**
 * Ambil GMV breakdown per produk untuk ditampilkan di pop-up.
 *
 * POST params: creator_id, start_date, end_date
 */
public function get_creator_gmv_breakdown() {
    $this->output->set_content_type('application/json');

    try {
        $creator_id = $this->input->post('creator_id');
        $start_date = $this->input->post('start_date') ?: date('Y-m-d', strtotime('-30 days'));
        $end_date   = $this->input->post('end_date')   ?: date('Y-m-d');

        $creator = $this->db->where('id', $creator_id)->get('creators')->row();
        if (!$creator) {
            return $this->output->set_output(json_encode(['success' => false, 'message' => 'Creator not found']));
        }

        $this->load->model('SampleProduct_model');
        $data = $this->SampleProduct_model->get_gmv_breakdown($creator->username, $start_date, $end_date);

        return $this->output->set_output(json_encode([
            'success'  => true,
            'products' => $data['rows'],
            'total_gmv'   => $data['total_gmv'],
            'total_sold'  => $data['total_sold'],
            'total_orders'=> $data['total_orders'],
        ]));

    } catch (Exception $e) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => $e->getMessage()]));
    }
}

// --------------------------------------------------------------------------
// F.5 — AJAX: TAMBAH VIDEO MANUAL
// --------------------------------------------------------------------------

/**
 * Simpan link video creator yang diinput manual oleh tim CA.
 *
 * POST params: creator_id, video_url, product_id (opt), product_name (opt), posted_at (opt)
 */
public function add_creator_video() {
    $this->output->set_content_type('application/json');

    try {
        $creator_id = $this->input->post('creator_id');
        $video_url  = trim($this->input->post('video_url'));

        if (!$creator_id || empty($video_url)) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Creator ID dan URL video wajib diisi'
            ]));
        }

        $creator = $this->db->where('id', $creator_id)->get('creators')->row();
        if (!$creator) {
            return $this->output->set_output(json_encode(['success' => false, 'message' => 'Creator not found']));
        }

        $this->load->model('SampleProduct_model');

        $result = $this->SampleProduct_model->save_manual_video([
            'creator_id'       => $creator_id,
            'creator_username' => $creator->username,
            'video_url'        => $video_url,
            'product_id'       => $this->input->post('product_id'),
            'product_name'     => $this->input->post('product_name'),
            'posted_at'        => $this->input->post('posted_at') ?: date('Y-m-d H:i:s'),
            'views'            => intval($this->input->post('views') ?? 0),
            'likes'            => intval($this->input->post('likes') ?? 0),
        ]);

        return $this->output->set_output(json_encode($result));

    } catch (Exception $e) {
        log_message('error', 'add_creator_video error: ' . $e->getMessage());
        return $this->output->set_output(json_encode(['success' => false, 'message' => $e->getMessage()]));
    }
}

// --------------------------------------------------------------------------
// F.5 — AJAX: UPDATE LINK VIDEO KE SAMPLE REQUEST
// --------------------------------------------------------------------------

/**
 * Update link video creator ke sample_request tertentu
 * untuk tracking efektivitas sample.
 *
 * POST params: sample_id, video_url
 */
public function update_sample_video_link() {
    $this->output->set_content_type('application/json');

    try {
        $sample_id = $this->input->post('sample_id');
        $video_url = trim($this->input->post('video_url'));

        if (!$sample_id || empty($video_url)) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Sample ID dan URL video wajib diisi'
            ]));
        }

        $this->load->model('SampleProduct_model');
        $ok = $this->SampleProduct_model->update_sample_video_status($sample_id, $video_url);

        return $this->output->set_output(json_encode([
            'success' => (bool)$ok,
            'message' => $ok ? 'Video link berhasil diupdate' : 'Gagal mengupdate video link',
        ]));

    } catch (Exception $e) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => $e->getMessage()]));
    }
}

// --------------------------------------------------------------------------
// F.2 — AJAX: CEK TRIGGER KERANJANG KUNING (apakah siap proses sample)
// --------------------------------------------------------------------------

/**
 * Cek apakah creator sudah layak masuk proses pengiriman sample.
 * Kriteria: sudah ada transaksi nyata di affiliate_orders untuk brand yang relevan.
 *
 * POST params: creator_id
 */
public function get_sample_keranjang_trigger() {
    $this->output->set_content_type('application/json');

    try {
        $creator_id = $this->input->post('creator_id');

        if (!$creator_id) {
            return $this->output->set_output(json_encode(['success' => false, 'message' => 'Creator ID required']));
        }

        $creator = $this->db->where('id', $creator_id)->get('creators')->row();
        if (!$creator) {
            return $this->output->set_output(json_encode(['success' => false, 'message' => 'Creator not found']));
        }

        // Cek apakah ada transaksi nyata (keranjang kuning = order nyata)
        $has_orders = $this->db
            ->where('creator_username', $creator->username)
            ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->count_all_results('affiliate_orders');

        // Cek konfirmasi kesediaan sebelumnya
        $this->load->model('SampleProduct_model');
        $last_willing = $this->SampleProduct_model->get_last_willingness($creator_id);

        // Cek sample yang sudah pernah dikirim
        $sample_count = $this->db
            ->where('creator_id', $creator_id)
            ->where('product_id IS NOT NULL')
            ->count_all_results('sample_requests');

        return $this->output->set_output(json_encode([
            'success'      => true,
            'has_orders'   => $has_orders > 0,
            'order_count'  => $has_orders,
            'last_willing' => $last_willing,
            'sample_count' => $sample_count,
            'creator'      => [
                'id'       => $creator->id,
                'username' => $creator->username,
                'status'   => $creator->status,
            ],
            'can_process_sample' => $has_orders > 0,
            'message' => $has_orders > 0
                ? "Creator sudah memiliki {$has_orders} transaksi. Siap proses sample."
                : "Creator belum memiliki transaksi. Tunggu creator menggunakan link terlebih dahulu.",
        ]));

    } catch (Exception $e) {
        log_message('error', 'get_sample_keranjang_trigger error: ' . $e->getMessage());
        return $this->output->set_output(json_encode(['success' => false, 'message' => $e->getMessage()]));
    }
}

    // ============================================================
    // SCOUTING CREATOR DETAIL — Brand Collaboration & GMV
    // ============================================================
    /**
     * AJAX endpoint: ambil brand kolaborasi + GMV dari FastMoss
     * untuk creator tertentu di scouting list.
     *
     * POST params:
     *   scouting_id  — ID baris di tabel creator_scouting
     *   username     — username creator (fallback jika UID tidak ditemukan)
     *
     * Response JSON:
     *   success, creator {username, full_name, avatar_url, follower_count, gmv},
     *   brands [{shop_name, shop_logo, product_count, sales_count, gmv}],
     *   total_gmv, total_brands
     */
    public function get_scouting_creator_detail() {
        $this->output->set_content_type('application/json');

        if (!$this->session->userdata('logged_in')) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Session expired'
            ]));
        }

        $scouting_id = $this->input->post('scouting_id');
        $username    = trim($this->input->post('username') ?? '');

        if (empty($scouting_id)) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'scouting_id wajib diisi'
            ]));
        }

        // ── 1. Ambil baris scouting ──────────────────────────────
        $this->load->model('CreatorScouting_model');
        $item = $this->CreatorScouting_model->get_by_id($scouting_id);

        if (!$item) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Scouting item tidak ditemukan'
            ]));
        }

        $username = $username ?: ($item->username ?? '');

        // ── 2. Cari FastMoss UID (tiktok_open_id) ────────────────
        // Prioritas: creators table → BrandCreator_model->find_creator_in_fastmoss()
        $fastmoss_uid = null;

        $creator_row = $this->db
            ->select('id, username, full_name, avatar_url, follower_count, tiktok_open_id, total_gmv, imported_gmv, category, phone')
            ->from('creators')
            ->where('LOWER(username)', strtolower($username))
            ->limit(1)
            ->get()
            ->row();

        if ($creator_row && !empty($creator_row->tiktok_open_id)) {
            $fastmoss_uid = $creator_row->tiktok_open_id;
        }

        // Tidak ada UID di DB → cari dari FastMoss (search by username)
        if (empty($fastmoss_uid) && !empty($username)) {
            try {
                $this->load->model('BrandCreator_model');
                $fastmoss_uid = $this->BrandCreator_model->find_creator_in_fastmoss($username);

                // Simpan ke creators table kalau creator sudah ada
                if ($fastmoss_uid && $creator_row) {
                    $this->db->where('id', $creator_row->id)
                             ->update('creators', [
                                 'tiktok_open_id' => $fastmoss_uid,
                                 'updated_at'     => date('Y-m-d H:i:s')
                             ]);
                }
            } catch (Exception $e) {
                log_message('error', '[ScoutingDetail] find_creator_in_fastmoss error: ' . $e->getMessage());
            }
        }

        // ── 3. Fetch brand collab dari FastMoss ──────────────────
        $brands    = [];
        $total_gmv = 0;
        $fm_error  = null;

        if (!empty($fastmoss_uid)) {
            try {
                $this->load->model('Fastmoss_model');
                $brands = $this->Fastmoss_model->get_all_creator_brand_collabs($fastmoss_uid, 5);

                foreach ($brands as $b) {
                    $total_gmv += floatval($b['gmv']);
                }

                // Sort by GMV desc (sudah dari API, tapi pastikan)
                usort($brands, function($a, $b) {
                    return $b['gmv'] <=> $a['gmv'];
                });

            } catch (Exception $e) {
                $fm_error = $e->getMessage();
                log_message('error', '[ScoutingDetail] get_all_creator_brand_collabs error: ' . $e->getMessage());
            }
        }

        // ── 4. Fallback GMV: dari scouting row atau creators table ─
        // Jika FastMoss tidak return data, pakai data lokal sebagai referensi
        $fallback_gmv = floatval(
            $item->gmv
            ?? $creator_row->total_gmv
            ?? $creator_row->imported_gmv
            ?? 0
        );

        if (empty($brands) && $fallback_gmv > 0) {
            // Tampilkan 1 entry fallback dari data lokal
            $brands = [[
                'shop_id'       => '',
                'shop_name'     => $item->brand_name ?? 'Brand',
                'shop_logo'     => '',
                'product_count' => intval($item->sales_count ?? 0),
                'sales_count'   => intval($item->sales_count ?? 0),
                'gmv'           => $fallback_gmv,
                'region'        => 'ID',
                '_source'       => 'local',
            ]];
            $total_gmv = $fallback_gmv;
        }

        // ── 5. Susun response ─────────────────────────────────────
        $creator_info = [
            'username'       => $username,
            'full_name'      => $creator_row->full_name      ?? $item->full_name ?? $username,
            'avatar_url'     => $creator_row->avatar_url     ?? $item->avatar_url ?? null,
            'follower_count' => intval($creator_row->follower_count ?? $item->follower_count ?? 0),
            'category'       => $creator_row->category       ?? null,
            'phone'          => $creator_row->phone          ?? $item->phone ?? null,
            'fastmoss_uid'   => $fastmoss_uid,
            'gmv_local'      => floatval($creator_row->total_gmv ?? $creator_row->imported_gmv ?? $item->gmv ?? 0),
        ];

        return $this->output->set_output(json_encode([
            'success'      => true,
            'creator'      => $creator_info,
            'brands'       => $brands,
            'total_gmv'    => $total_gmv,
            'total_brands' => count($brands),
            'has_fastmoss' => !empty($fastmoss_uid),
            'error_detail' => $fm_error,
        ]));
    }

    public function update_fastmoss_cookie() {
        $input = $this->input->post('cookie_data');
        if (empty($input)) {
            return $this->output->set_output(json_encode(['success' => false, 'message' => 'Input kosong']));
        }
        
        $cookie = trim($input);
        
        // Cek jika input berupa cURL command
        if (stripos($cookie, 'curl') !== false) {
            if (preg_match('/-b\s+[\'"]([^\'"]+)[\'"]/', $cookie, $matches)) {
                $cookie = $matches[1];
            } elseif (preg_match('/--cookie\s+[\'"]([^\'"]+)[\'"]/', $cookie, $matches)) {
                $cookie = $matches[1];
            } elseif (preg_match('/-H\s+[\'"]cookie:\s*([^\'"]+)[\'"]/i', $cookie, $matches)) {
                $cookie = $matches[1];
            }
        }
        
        // Simpan ke database
        $this->db->query("
            INSERT INTO app_config (`key`, `value`, `updated_at`) 
            VALUES ('fastmoss_cookie', ?, NOW())
            ON DUPLICATE KEY UPDATE `value` = ?, `updated_at` = NOW()
        ", [$cookie, $cookie]);
        
        return $this->output->set_output(json_encode([
            'success' => true, 
            'message' => 'Cookie FastMoss berhasil diperbarui!',
            'cookie' => substr($cookie, 0, 30) . '...'
        ]));
    }


    /**
     * Batch populate tiktok_open_id untuk semua creator yang belum punya UID.
     * Panggil sekali dari browser: /is/populate_tiktok_open_ids
     * Bisa diakses oleh role IS atau ADMIN.
     */
    public function populate_tiktok_open_ids()
    {
        $this->output->set_content_type('application/json');

        // Role IS sudah lolos constructor, tidak perlu cek tambahan

        $this->load->model('Fastmoss_model');

        // Ambil semua creator yang tiktok_open_id masih kosong, limit per-batch 50
        $creators = $this->db
            ->select('id, username')
            ->group_start()
                ->where('tiktok_open_id IS NULL', null, false)
                ->or_where('tiktok_open_id', '')
            ->group_end()
            ->limit(50)
            ->get('creators')
            ->result();

        $total    = count($creators);
        $resolved = 0;
        $failed   = [];

        foreach ($creators as $c) {
            if (empty($c->username)) {
                $failed[] = ['id' => $c->id, 'reason' => 'username kosong'];
                continue;
            }

            $uid = $this->Fastmoss_model->resolve_uid_by_username($c->username);

            if ($uid) {
                $this->db->where('id', $c->id)
                         ->update('creators', [
                             'tiktok_open_id' => $uid,
                             'updated_at'     => date('Y-m-d H:i:s')
                         ]);
                $resolved++;
                log_message('info', '[populate_tiktok_open_ids] Resolved: ' . $c->username . ' → ' . $uid);
            } else {
                $failed[] = ['id' => $c->id, 'username' => $c->username, 'reason' => 'tidak ditemukan di FastMoss'];
                log_message('debug', '[populate_tiktok_open_ids] Not found: ' . $c->username);
            }

            // Jeda kecil agar tidak di-rate-limit FastMoss
            usleep(300000); // 0.3 detik
        }

        // Hitung sisa yang belum ter-resolve
        $remaining = $this->db
            ->group_start()
                ->where('tiktok_open_id IS NULL', null, false)
                ->or_where('tiktok_open_id', '')
            ->group_end()
            ->count_all_results('creators');

        return $this->output->set_output(json_encode([
            'success'   => true,
            'processed' => $total,
            'resolved'  => $resolved,
            'failed'    => count($failed),
            'remaining' => $remaining,
            'details'   => $failed,
            'message'   => "Berhasil resolve $resolved dari $total creator. Sisa yang belum: $remaining. "
                         . ($remaining > 0 ? 'Panggil endpoint ini lagi untuk batch berikutnya.' : 'Semua selesai!')
        ]));
    }

} // end class Is
