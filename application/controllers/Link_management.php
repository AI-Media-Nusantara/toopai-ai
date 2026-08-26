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
        
        // Ambil filter dari request
        $search = $this->input->get('search') ?: '';
        $search_type = $this->input->get('search_type') ?: 'shop';
        $campaign_filter = $this->input->get('campaign_id') ?: 'all';
        
        // Count total rows dengan filter yang aktif
        $this->db->from('bd_affiliate_links l')
                 ->join('affiliate_campaigns c', 'c.campaign_id = l.campaign_id', 'left')
                 ->join('affiliate_products ap', 'ap.product_id = l.product_id AND ap.campaign_id = l.campaign_id', 'left');
                 
        if ($campaign_filter !== 'all') {
            $this->db->where('l.campaign_id', $campaign_filter);
        }
        
        if ($search !== '') {
            if ($search_type === 'shop') {
                $this->db->group_start()
                         ->like('ap.shop_name', $search)
                         ->or_like('l.product_name', $search)
                         ->group_end();
            } else {
                $this->db->where('l.product_id', $search);
            }
        }
        
        $total_rows = $this->db->count_all_results();
        
        // Setup CodeIgniter Pagination
        $this->load->library('pagination');
        
        $config['base_url'] = base_url('link_management/dashboard');
        $config['total_rows'] = $total_rows;
        $config['per_page'] = 10;
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'page';
        $config['reuse_query_string'] = TRUE;
        
        // Styling pagination Bootstrap
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['first_link'] = 'First';
        $config['first_tag_open'] = '<li class="page-item">';
        $config['first_tag_close'] = '</li>';
        $config['last_link'] = 'Last';
        $config['last_tag_open'] = '<li class="page-item">';
        $config['last_tag_close'] = '</li>';
        $config['next_link'] = '&raquo;';
        $config['next_tag_open'] = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        $config['prev_link'] = '&laquo;';
        $config['prev_tag_open'] = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="page-item active"><a class="page-link" href="#">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';
        $config['attributes'] = array('class' => 'page-link');
        
        $this->pagination->initialize($config);
        $pagination_links = $this->pagination->create_links();
        
        // Offset / Halaman aktif
        $page = $this->input->get('page') ? intval($this->input->get('page')) : 0;
        if ($page < 0) $page = 0;
        
        // Ambil data link terpaginasi
        $this->db->select('
                l.*, 
                c.campaign_name,
                ap.shop_name,
                ap.image_url,
                ap.open_commission_rate as product_open_commission
            ')
            ->from('bd_affiliate_links l')
            ->join('affiliate_campaigns c', 'c.campaign_id = l.campaign_id', 'left')
            ->join('affiliate_products ap', 'ap.product_id = l.product_id AND ap.campaign_id = l.campaign_id', 'left');
            
        if ($campaign_filter !== 'all') {
            $this->db->where('l.campaign_id', $campaign_filter);
        }
        
        if ($search !== '') {
            if ($search_type === 'shop') {
                $this->db->group_start()
                         ->like('ap.shop_name', $search)
                         ->or_like('l.product_name', $search)
                         ->group_end();
            } else {
                $this->db->where('l.product_id', $search);
            }
        }
        
        $links = $this->db->order_by('l.created_at', 'DESC')
                          ->limit(10, $page)
                          ->get()
                          ->result();
        
        // Update statistik hanya untuk 10 data terpaginasi (hemat query!)
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
        
        // Ambil daftar campaign untuk dropdown filter
        $campaigns = $this->db->select('campaign_id, campaign_name, status')
                              ->from('affiliate_campaigns')
                              ->where('status', 'ONGOING')
                              ->order_by('created_at', 'DESC')
                              ->get()
                              ->result();
                              
        // Hitung total gmv & order global secara efisien
        $global_stats = $this->db->select('
                COALESCE(SUM(gmv), 0) as total_gmv,
                COUNT(DISTINCT order_id) as total_orders
            ')
            ->from('affiliate_orders')
            ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->where('product_id IN (SELECT DISTINCT product_id FROM bd_affiliate_links)')
            ->get()
            ->row();
        
        $data = [
            'title' => 'Link Management - Toopai BD',
            'active_menu' => 'link_management',
            'links' => $links,
            'campaigns' => $campaigns,
            'search' => $search,
            'search_type' => $search_type,
            'campaign_filter' => $campaign_filter,
            'pagination_links' => $pagination_links,
            'total_links' => $total_rows,
            'total_gmv' => $global_stats->total_gmv ?? 0,
            'total_orders' => $global_stats->total_orders ?? 0
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
        $product_ids_raw = $this->input->post('product_ids');
        $commission_rate = $this->input->post('commission_rate');
        $notes = $this->input->post('notes');
        $special_case = $this->input->post('special_case');
        
        // Parse product IDs
        $product_ids = [];
        if (is_string($product_ids_raw)) {
            $decoded = json_decode($product_ids_raw, true);
            if (is_array($decoded)) {
                $product_ids = $decoded;
            } else {
                $product_ids = array_filter(explode(',', $product_ids_raw));
            }
        } elseif (is_array($product_ids_raw)) {
            $product_ids = $product_ids_raw;
        }
        
        if (!$campaign_id || empty($product_ids)) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Campaign and at least one Product are required'
            ]));
        }
        
        // Fetch products from database
        $db_products = $this->db->select('product_id, product_name, open_commission_rate')
                                ->where('campaign_id', $campaign_id)
                                ->where_in('product_id', $product_ids)
                                ->get('affiliate_products')
                                ->result();
                                
        if (empty($db_products)) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'No valid products found in this campaign.'
            ]));
        }
        
        // Validasi commission rate
        $submitted_commission = floatval($commission_rate);
        if ($submitted_commission < 1 || $submitted_commission > 50) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Commission rate must be between 1% and 50%'
            ]));
        }
        
        try {
            // Jika HANYA 1 produk terpilih, jalankan proses link standard
            if (count($db_products) === 1) {
                $product = $db_products[0];
                $link_result = $this->jsm_api->generate_promotion_link($campaign_id, $product->product_id, $submitted_commission);
                
                if (!$link_result['success']) {
                    log_message('error', "create_link single failed for product_id={$product->product_id} ({$product->product_name}): " . json_encode($link_result));
                    return $this->output->set_output(json_encode([
                        'success' => false,
                        'message' => $link_result['message'] ?? 'Failed to generate link'
                    ]));
                }
                
                // Konversi open_commission_rate ke persen
                $openCommissionRaw = floatval($product->open_commission_rate ?? 0);
                if ($openCommissionRaw > 20) {
                    $openCommissionPercent = $openCommissionRaw / 100;
                } else {
                    $openCommissionPercent = $openCommissionRaw;
                }
                
                $link_id = md5($campaign_id . $product->product_id . time() . rand(1000, 9999));
                
                $data = [
                    'link_id' => $link_id,
                    'campaign_id' => $campaign_id,
                    'product_id' => $product->product_id,
                    'product_name' => $product->product_name,
                    'affiliate_link' => $link_result['link'],
                    'commission_rate' => $submitted_commission,
                    'open_commission_rate' => $openCommissionPercent,
                    'created_by' => $user_id,
                    'created_by_name' => $username,
                    'status' => 'ACTIVE',
                    'expire_at' => $link_result['expire_at'] ?? null,
                    'notes' => $notes,
                    'special_case' => $special_case ? 1 : 0,
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
                    "Created affiliate link for product: {$product->product_name} (Campaign: $campaign_id) with commission $submitted_commission% (Open: $openCommissionPercent%)" . ($special_case ? ' [SPECIAL CASE]' : '')
                );
                
                return $this->output->set_output(json_encode([
                    'success' => true,
                    'message' => 'Link created successfully',
                    'link_id' => $insert_id,
                    'link' => $link_result['link'],
                    'commission_rate' => $submitted_commission,
                    'open_commission_rate' => $openCommissionPercent
                ]));
            } else {
                // MULTIPLE PRODUCTS: Buat satu Palette Link!
                $palette_products = [];
                $failed_products = [];
                
                foreach ($db_products as $product) {
                    $link_result = $this->jsm_api->generate_promotion_link($campaign_id, $product->product_id, $submitted_commission);
                    
                    if ($link_result['success'] && !empty($link_result['link'])) {
                        $palette_products[] = [
                            'product_id' => $product->product_id,
                            'product_name' => $product->product_name,
                            'link' => $link_result['link']
                        ];
                    } else {
                        $failed_products[] = $product->product_name;
                        log_message('error', "create_link palette failed for product_id={$product->product_id} ({$product->product_name}): " . json_encode($link_result));
                    }
                }
                
                if (empty($palette_products)) {
                    log_message('error', "create_link palette failed for all selected products. Failed list: " . implode(', ', $failed_products));
                    return $this->output->set_output(json_encode([
                        'success' => false,
                        'message' => 'Failed to generate affiliate links for all selected products. ' . (!empty($failed_products) ? 'Error on: ' . implode(', ', $failed_products) : '')
                    ]));
                }
                
                // Buat unique link_id untuk palette
                $palette_id = md5($campaign_id . implode('', array_column($palette_products, 'product_id')) . time() . rand(1000, 9999));
                $palette_url = base_url('palette/v/' . $palette_id);
                
                // Cari nama brand dari produk pertama
                $brand_name = 'Multiple Products';
                $first_prod_check = $this->db->select('shop_name')
                                             ->where('product_id', $palette_products[0]['product_id'])
                                             ->where('campaign_id', $campaign_id)
                                             ->get('affiliate_products')
                                             ->row();
                if ($first_prod_check && !empty($first_prod_check->shop_name)) {
                    $brand_name = $first_prod_check->shop_name;
                }
                
                // Simpan record utama ke bd_affiliate_links
                $data = [
                    'link_id' => $palette_id,
                    'campaign_id' => $campaign_id,
                    'product_id' => 'palette_' . $palette_id, // Demarkasi tipe palette
                    'product_name' => 'Palette Link - ' . $brand_name,
                    'affiliate_link' => $palette_url,
                    'commission_rate' => $submitted_commission,
                    'open_commission_rate' => $submitted_commission,
                    'created_by' => $user_id,
                    'created_by_name' => $username,
                    'status' => 'ACTIVE',
                    'link_type' => 'multi',
                    'notes' => $notes,
                    'special_case' => $special_case ? 1 : 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $this->db->insert('bd_affiliate_links', $data);
                $insert_id = $this->db->insert_id();
                
                // Simpan daftar produk pemetaan ke bd_palette_products
                foreach ($palette_products as $p) {
                    $this->db->insert('bd_palette_products', [
                        'link_id' => $palette_id,
                        'product_id' => $p['product_id'],
                        'product_name' => $p['product_name'],
                        'affiliate_link' => $p['link'],
                        'commission_rate' => $submitted_commission
                    ]);
                }
                
                // Log activity
                $this->load->model('User_log_model');
                $this->User_log_model->log(
                    $user_id,
                    $username,
                    'BD',
                    'CREATE_PALETTE_LINK',
                    "Created Palette Link for brand $brand_name (Campaign: $campaign_id) containing " . count($palette_products) . " products."
                );
                
                $msg = 'Palette link created successfully with ' . count($palette_products) . ' products.';
                if (!empty($failed_products)) {
                    $msg .= ' (Failed products: ' . implode(', ', $failed_products) . ')';
                }
                
                return $this->output->set_output(json_encode([
                    'success' => true,
                    'message' => $msg,
                    'link_id' => $insert_id,
                    'link' => $palette_url,
                    'commission_rate' => $submitted_commission
                ]));
            }
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