<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test_api extends CI_Controller {

    public function __construct() {
        parent::__construct();
        
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
        
        $this->load->library('Jsm_api');
    }

    public function index() {
        $data['title'] = 'Test TikTok API';
        
        $this->load->view('templates/header', $data);
        $this->load->view('test_api/index', $data);
        $this->load->view('templates/footer');
    }

    // ========== 1. TEST GET TOKEN STATUS ==========
    public function token_status() {
        $this->output->set_content_type('application/json');
        
        try {
            $this->load->model('Jsm_token_model');
            
            $affiliate_token = $this->Jsm_token_model->get_latest_token_by_type(3);
            $seller_token = $this->Jsm_token_model->get_latest_token_by_type(2);
            $creator_token = $this->Jsm_token_model->get_latest_token_by_type(1);
            
            $result = [
                'success' => true,
                'cipher_used' => $this->jsm_api->get_default_cipher(),
                'affiliate_token' => $affiliate_token ? [
                    'exists' => true,
                    'expires_at' => date('Y-m-d H:i:s', $affiliate_token->access_token_expire),
                    'user_type' => 3,
                    'note' => 'Dapat mengakses: campaigns, products (read), affiliate orders, creators search'
                ] : ['exists' => false, 'note' => 'Perlu authorize Affiliate Partner'],
                'seller_token' => $seller_token ? [
                    'exists' => true,
                    'expires_at' => date('Y-m-d H:i:s', $seller_token->access_token_expire),
                    'user_type' => 2,
                    'note' => 'Dapat mengakses: sample requests, product management'
                ] : ['exists' => false, 'note' => 'Perlu authorize Seller'],
                'creator_token' => $creator_token ? [
                    'exists' => true,
                    'expires_at' => date('Y-m-d H:i:s', $creator_token->access_token_expire),
                    'user_type' => 1,
                    'note' => 'Dapat mengakses: affiliate links, performance'
                ] : ['exists' => false, 'note' => 'Perlu authorize Creator']
            ];
            
            echo json_encode($result, JSON_PRETTY_PRINT);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_PRETTY_PRINT);
        }
    }

    // ========== 2. TEST GET ONGOING CAMPAIGNS (Affiliate Partner Only) ==========
    public function get_campaigns() {
        $this->output->set_content_type('application/json');
        
        try {
            $result = $this->jsm_api->get_ongoing_campaigns(['page_size' => 20]);
            
            if ($result['success']) {
                echo json_encode([
                    'success' => true,
                    'total' => $result['total'],
                    'campaigns' => $result['data'],
                    'note' => '✅ Affiliate Partner token berhasil mengakses campaigns'
                ], JSON_PRETTY_PRINT);
            } else {
                echo json_encode([
                    'success' => false, 
                    'error' => $result['message'],
                    'note' => '❌ Pastikan Anda sudah authorize Affiliate Partner (user_type=3)'
                ], JSON_PRETTY_PRINT);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_PRETTY_PRINT);
        }
    }

    // ========== 3. TEST GET CAMPAIGN PRODUCTS (Affiliate Partner Only) ==========
    public function get_products() {
        $this->output->set_content_type('application/json');
        
        $campaign_id = $this->input->get('campaign_id');
        
        if (!$campaign_id) {
            echo json_encode(['success' => false, 'error' => 'campaign_id parameter required'], JSON_PRETTY_PRINT);
            return;
        }
        
        try {
            $result = $this->jsm_api->get_campaign_products($campaign_id, ['page_size' => 20]);
            
            if ($result['success']) {
                echo json_encode([
                    'success' => true,
                    'total' => $result['total'],
                    'products' => $result['data'],
                    'note' => '✅ Affiliate Partner token berhasil mengakses products'
                ], JSON_PRETTY_PRINT);
            } else {
                echo json_encode([
                    'success' => false, 
                    'error' => $result['message'],
                    'note' => '❌ Pastikan campaign_id benar dan token valid'
                ], JSON_PRETTY_PRINT);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_PRETTY_PRINT);
        }
    }

    // ========== 4. TEST SEARCH PRODUCTS (Affiliate Partner Only) ==========
    public function search_products() {
        $this->output->set_content_type('application/json');
        
        $keyword = $this->input->get('keyword');
        $category = $this->input->get('category');
        
        if (!$keyword) {
            echo json_encode(['success' => false, 'error' => 'keyword parameter required'], JSON_PRETTY_PRINT);
            return;
        }
        
        try {
            $params = [
                'keyword' => $keyword,
                'page_size' => 10,
                'has_open_collaboration' => true
            ];
            
            if ($category) {
                $params['category'] = $category;
            }
            
            $result = $this->jsm_api->search_products($params);
            
            if ($result['success']) {
                echo json_encode([
                    'success' => true,
                    'total' => $result['total'],
                    'products' => $result['data'],
                    'note' => '✅ Affiliate Partner token berhasil mencari products'
                ], JSON_PRETTY_PRINT);
            } else {
                echo json_encode([
                    'success' => false, 
                    'error' => $result['message'],
                    'note' => '❌ Pastikan token Affiliate Partner valid'
                ], JSON_PRETTY_PRINT);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_PRETTY_PRINT);
        }
    }

    // ========== 5. TEST SEARCH ORDERS (Affiliate Partner Only) ==========
    public function get_orders() {
        $this->output->set_content_type('application/json');
        
        $campaign_id = $this->input->get('campaign_id');
        $days = $this->input->get('days') ?: 30;
        
        try {
            $params = [
                'create_time_ge' => strtotime("-$days days"),
                'create_time_lt' => time(),
                'page_size' => 50
            ];
            
            if ($campaign_id) {
                $params['campaign_id'] = $campaign_id;
            }
            
            $result = $this->jsm_api->search_affiliate_orders($params);
            
            if ($result['success']) {
                $total_gmv = 0;
                foreach ($result['data'] as $order) {
                    $total_gmv += $order['affiliate_gmv'];
                }
                
                echo json_encode([
                    'success' => true,
                    'total_orders' => $result['total'],
                    'total_gmv' => $total_gmv,
                    'orders' => $result['data'],
                    'note' => '✅ Affiliate Partner token berhasil mengakses orders'
                ], JSON_PRETTY_PRINT);
            } else {
                echo json_encode([
                    'success' => false, 
                    'error' => $result['message'],
                    'note' => '❌ Pastikan token Affiliate Partner valid'
                ], JSON_PRETTY_PRINT);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_PRETTY_PRINT);
        }
    }

    // ========== 6. TEST SEARCH CREATORS (Affiliate Partner Only) ==========
    public function get_creators() {
        $this->output->set_content_type('application/json');
        
        $keyword = $this->input->get('keyword');
        $category = $this->input->get('category');
        
        try {
            $params = ['page_size' => 20];
            if ($keyword) $params['keyword'] = $keyword;
            if ($category) $params['category'] = $category;
            
            $result = $this->jsm_api->search_creators($params);
            
            if ($result['success']) {
                echo json_encode([
                    'success' => true,
                    'total' => $result['total'],
                    'creators' => $result['data'],
                    'note' => '✅ Affiliate Partner token berhasil mencari creators'
                ], JSON_PRETTY_PRINT);
            } else {
                echo json_encode([
                    'success' => false, 
                    'error' => $result['message'],
                    'note' => '❌ Endpoint creators/search mungkin memerlukan scope tambahan'
                ], JSON_PRETTY_PRINT);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_PRETTY_PRINT);
        }
    }

    // ========== 7. TEST GENERATE AFFILIATE LINK (Affiliate Partner Only) ==========
    public function generate_link() {
        $this->output->set_content_type('application/json');
        
        $campaign_id = $this->input->get('campaign_id');
        $product_id = $this->input->get('product_id');
        $commission = $this->input->get('commission') ?: 10;
        
        if (!$campaign_id || !$product_id) {
            echo json_encode(['success' => false, 'error' => 'campaign_id and product_id required'], JSON_PRETTY_PRINT);
            return;
        }
        
        try {
            $result = $this->jsm_api->generate_promotion_link($campaign_id, $product_id, $commission);
            
            if ($result['success']) {
                echo json_encode([
                    'success' => true,
                    'link' => $result['link'],
                    'expire_at' => $result['expire_at'],
                    'note' => '✅ Affiliate link berhasil digenerate'
                ], JSON_PRETTY_PRINT);
            } else {
                echo json_encode([
                    'success' => false, 
                    'error' => $result['message'],
                    'note' => '❌ Pastikan campaign_id, product_id benar dan token valid'
                ], JSON_PRETTY_PRINT);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_PRETTY_PRINT);
        }
    }

    // ========== 8. TEST TOKEN SCOPE (Cek scope yang dimiliki) ==========
    public function token_info() {
        $this->output->set_content_type('application/json');
        
        try {
            $this->load->model('Jsm_token_model');
            $token = $this->Jsm_token_model->get_latest_token();
            
            if ($token) {
                echo json_encode([
                    'success' => true,
                    'user_type' => 3,
                    'token_expires' => date('Y-m-d H:i:s', $token->access_token_expire),
                    'refresh_expires' => date('Y-m-d H:i:s', $token->refresh_token_expire),
                    'scope' => $token->scope ?? 'affiliate_platform.seller',
                    'note' => 'Scope menentukan endpoint apa yang bisa diakses',
                    'available_endpoints' => [
                        'GET /affiliate_partner/202405/campaigns' => 'Get campaigns',
                        'GET /affiliate_partner/202405/campaigns/{id}/products' => 'Get products',
                        'POST /affiliate_partner/202411/orders/search' => 'Search orders',
                        'POST /affiliate_partner/202405/campaigns/{id}/products/{id}/promotion_link/generate' => 'Generate link',
                        'GET /affiliate_partner/202405/products/search' => 'Search products'
                    ]
                ], JSON_PRETTY_PRINT);
            } else {
                echo json_encode(['success' => false, 'error' => 'No token found'], JSON_PRETTY_PRINT);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_PRETTY_PRINT);
        }
    }

    // ========== 9. TEST SEMUA ENDPOINT YANG VALID UNTUK AFFILIATE ==========
    public function test_all() {
        $this->output->set_content_type('application/json');
        
        $results = [];
        
        // Test token status
        $this->load->model('Jsm_token_model');
        $affiliate_token = $this->Jsm_token_model->get_latest_token_by_type(3);
        $results['token_status'] = $affiliate_token ? 'OK - Token exists (user_type=3)' : 'FAILED - No affiliate token';
        
        if ($affiliate_token) {
            $results['token_expires'] = date('Y-m-d H:i:s', $affiliate_token->access_token_expire);
        }
        
        // Test get campaigns (Affiliate endpoint)
        $campaigns = $this->jsm_api->get_ongoing_campaigns(['page_size' => 5]);
        $results['campaigns'] = $campaigns['success'] ? 'OK - Found ' . $campaigns['total'] . ' campaigns' : 'FAILED - ' . $campaigns['message'];
        
        // Test search products (Affiliate endpoint)
        $products = $this->jsm_api->search_products(['keyword' => 'skincare', 'page_size' => 5]);
        $results['search_products'] = $products['success'] ? 'OK - Found ' . $products['total'] . ' products' : 'FAILED - ' . $products['message'];
        
        // Test search orders (Affiliate endpoint)
        $orders = $this->jsm_api->search_affiliate_orders(['create_time_ge' => strtotime('-30 days'), 'page_size' => 5]);
        $results['orders'] = $orders['success'] ? 'OK - Found ' . $orders['total'] . ' orders' : 'FAILED - ' . $orders['message'];
        
        echo json_encode([
            'success' => true,
            'cipher_used' => $this->jsm_api->get_default_cipher(),
            'app_key' => $this->jsm_api->get_app_key(),
            'service_id' => $this->jsm_api->get_service_id(),
            'user_type' => 3,
            'note' => 'Affiliate Partner token hanya bisa mengakses endpoint tertentu',
            'results' => $results
        ], JSON_PRETTY_PRINT);
    }
    
    
    /**
 * CEK LANGSUNG SEMUA ORDER DARI API (TANPA FILTER TANGGAL)
 * Usage: php index.php Test_api all_orders
 */
public function all_orders() {
    echo "========================================\n";
    echo "CEK SEMUA ORDER DARI API TIKTOK\n";
    echo "========================================\n\n";
    
    // Cek token
    $token = $this->Jsm_token_model->get_latest_token_by_type(3);
    if (!$token) {
        echo "❌ No affiliate token found. Please authorize first.\n";
        return;
    }
    
    echo "✅ Token found\n";
    echo "Token expires: " . date('Y-m-d H:i:s', $token->access_token_expire) . "\n\n";
    
    // Ambil semua order tanpa filter tanggal (pakai range yang lebar)
    // Gunakan timestamp dari 2024 sampai 2026
    $start_time = strtotime('2024-01-01 00:00:00');
    $end_time = time();
    
    echo "Date range: " . date('Y-m-d H:i:s', $start_time) . " to " . date('Y-m-d H:i:s', $end_time) . "\n";
    echo "Start timestamp: $start_time\n";
    echo "End timestamp: $end_time\n\n";
    
    $all_orders = [];
    $page_token = null;
    $page = 1;
    
    do {
        $params = [
            'create_time_ge' => $start_time,
            'create_time_lt' => $end_time,
            'page_size' => 100
        ];
        
        if ($page_token) {
            $params['page_token'] = $page_token;
        }
        
        echo "📄 Fetching page $page...\n";
        
        $result = $this->jsm_api->search_affiliate_orders($params);
        
        if (!$result['success']) {
            echo "❌ API Error: " . ($result['message'] ?? 'Unknown') . "\n";
            if (isset($result['raw_response'])) {
                echo "Raw: " . json_encode($result['raw_response'], JSON_PRETTY_PRINT) . "\n";
            }
            break;
        }
        
        $orders = $result['data'];
        $total_count = $result['total_count'] ?? null;
        
        echo "   Page $page: " . count($orders) . " orders\n";
        
        foreach ($orders as $order) {
            $order_id = $order['order_id'] ?? $order['id'];
            $create_time = $order['create_time'] ?? 0;
            $create_local = $create_time ? date('Y-m-d H:i:s', $create_time) : 'N/A';
            $campaign_id = $order['campaign_id'] ?? 'N/A';
            $product_name = $order['product_name'] ?? 'N/A';
            $creator = $order['creator_username'] ?? 'N/A';
            $gmv = $order['gmv'] ?? 0;
            $status = $order['order_status'] ?? $order['status'] ?? 'N/A';
            
            $all_orders[] = $order;
            
            echo "      - Order: $order_id\n";
            echo "        Time: $create_local UTC\n";
            echo "        Campaign: $campaign_id\n";
            echo "        Product: $product_name\n";
            echo "        Creator: $creator\n";
            echo "        GMV: $gmv\n";
            echo "        Status: $status\n";
            echo "        ---\n";
        }
        
        $next_page_token = $result['next_page_token'] ?? null;
        
        if ($total_count) {
            echo "   Total available: $total_count orders\n";
        }
        
        if ($next_page_token) {
            $page_token = $next_page_token;
            $page++;
            echo "   Next page token found, continuing...\n\n";
            usleep(200000);
        } else {
            $page_token = null;
        }
        
    } while ($page_token !== null && $page <= 50);
    
    echo "\n========================================\n";
    echo "TOTAL ORDER DARI API: " . count($all_orders) . "\n";
    echo "========================================\n";
    
    if (!empty($all_orders)) {
        echo "\n📊 RINGKASAN:\n";
        
        // Group by date
        $by_date = [];
        foreach ($all_orders as $order) {
            $create_time = $order['create_time'] ?? 0;
            $date = $create_time ? date('Y-m-d', $create_time) : 'Unknown';
            if (!isset($by_date[$date])) {
                $by_date[$date] = ['count' => 0, 'gmv' => 0];
            }
            $by_date[$date]['count']++;
            $by_date[$date]['gmv'] += ($order['gmv'] ?? 0);
        }
        
        echo "\nPer Tanggal (UTC):\n";
        foreach ($by_date as $date => $stats) {
            echo "  $date: {$stats['count']} orders, GMV: " . number_format($stats['gmv']) . "\n";
        }
        
        echo "\nPer Campaign:\n";
        $by_campaign = [];
        foreach ($all_orders as $order) {
            $campaign_id = $order['campaign_id'] ?? 'Unknown';
            if (!isset($by_campaign[$campaign_id])) {
                $by_campaign[$campaign_id] = ['count' => 0, 'gmv' => 0];
            }
            $by_campaign[$campaign_id]['count']++;
            $by_campaign[$campaign_id]['gmv'] += ($order['gmv'] ?? 0);
        }
        
        foreach ($by_campaign as $campaign_id => $stats) {
            echo "  $campaign_id: {$stats['count']} orders, GMV: " . number_format($stats['gmv']) . "\n";
        }
    }
}

/**
 * CEK ORDER UNTUK RANGE TANGGAL DENGAN TIMEZONE YANG BENAR
 * Usage: php index.php Test_api check_date 2026-05-01
 */
public function check_date($date = null) {
    if (!$date) {
        $date = date('Y-m-d');
    }
    
    echo "========================================\n";
    echo "CEK ORDER UNTUK TANGGAL: $date (WIB)\n";
    echo "========================================\n\n";
    
    // Konversi ke UTC
    $local_tz = new DateTimeZone('Asia/Jakarta');
    $utc_tz = new DateTimeZone('UTC');
    
    $start_local = new DateTime($date . ' 00:00:00', $local_tz);
    $end_local = new DateTime($date . ' 23:59:59', $local_tz);
    
    $start_utc = clone $start_local;
    $start_utc->setTimezone($utc_tz);
    $end_utc = clone $end_local;
    $end_utc->setTimezone($utc_tz);
    
    echo "Local time (WIB):\n";
    echo "  Start: " . $start_local->format('Y-m-d H:i:s') . "\n";
    echo "  End:   " . $end_local->format('Y-m-d H:i:s') . "\n\n";
    
    echo "UTC time:\n";
    echo "  Start: " . $start_utc->format('Y-m-d H:i:s') . "\n";
    echo "  End:   " . $end_utc->format('Y-m-d H:i:s') . "\n\n";
    
    $start_timestamp = $start_utc->getTimestamp();
    $end_timestamp = $end_utc->getTimestamp();
    
    echo "Timestamps:\n";
    echo "  create_time_ge: $start_timestamp\n";
    echo "  create_time_lt: $end_timestamp\n\n";
    
    // Panggil API
    $params = [
        'create_time_ge' => $start_timestamp,
        'create_time_lt' => $end_timestamp,
        'page_size' => 100
    ];
    
    echo "Calling API...\n";
    $result = $this->jsm_api->search_affiliate_orders($params);
    
    if (!$result['success']) {
        echo "❌ API Error: " . ($result['message'] ?? 'Unknown') . "\n";
        if (isset($result['raw_response'])) {
            echo "\nRaw response:\n";
            echo json_encode($result['raw_response'], JSON_PRETTY_PRINT) . "\n";
        }
        return;
    }
    
    $orders = $result['data'];
    $total_count = $result['total_count'] ?? count($orders);
    
    echo "\n✅ API Response:\n";
    echo "  Total orders: $total_count\n";
    echo "  Page size: " . count($orders) . "\n\n";
    
    if (empty($orders)) {
        echo "Tidak ada order untuk tanggal $date\n";
        echo "\nKemungkinan:\n";
        echo "1. Memang belum ada transaksi\n";
        echo "2. Order sudah masuk tapi timestamp-nya berbeda\n";
        echo "3. Order untuk campaign tertentu yang tidak di-scope\n";
        return;
    }
    
    echo "Daftar Order:\n";
    echo str_repeat("-", 50) . "\n";
    
    foreach ($orders as $order) {
        $order_id = $order['order_id'] ?? $order['id'];
        $create_time = $order['create_time'] ?? 0;
        $create_local = $create_time ? date('Y-m-d H:i:s', $create_time) . ' UTC' : 'N/A';
        $create_wib = $create_time ? date('Y-m-d H:i:s', $create_time + 7*3600) . ' WIB' : 'N/A';
        $campaign_id = $order['campaign_id'] ?? 'N/A';
        $product_name = $order['product_name'] ?? 'N/A';
        $creator = $order['creator_username'] ?? 'N/A';
        $gmv = $order['gmv'] ?? 0;
        $status = $order['order_status'] ?? $order['status'] ?? 'N/A';
        
        echo "Order ID: $order_id\n";
        echo "  Created (UTC): $create_local\n";
        echo "  Created (WIB): $create_wib\n";
        echo "  Campaign ID: $campaign_id\n";
        echo "  Product: $product_name\n";
        echo "  Creator: $creator\n";
        echo "  GMV: " . number_format($gmv) . "\n";
        echo "  Status: $status\n";
        echo str_repeat("-", 30) . "\n";
    }
}

/**
 * CEK SEMUA CAMPAIGN DAN PRODUCTS
 * Usage: php index.php Test_api campaigns_full
 */
public function campaigns_full() {
    echo "========================================\n";
    echo "CEK SEMUA CAMPAIGN\n";
    echo "========================================\n\n";
    
    $result = $this->jsm_api->get_ongoing_campaigns(['page_size' => 50]);
    
    if (!$result['success']) {
        echo "❌ Error: " . ($result['message'] ?? 'Unknown') . "\n";
        return;
    }
    
    $campaigns = $result['data'];
    echo "Total campaigns: " . count($campaigns) . "\n\n";
    
    foreach ($campaigns as $campaign) {
        echo "Campaign: " . ($campaign['name'] ?? 'N/A') . "\n";
        echo "  ID: " . ($campaign['id'] ?? 'N/A') . "\n";
        echo "  Status: " . ($campaign['status'] ?? 'N/A') . "\n";
        
        // Cek products untuk campaign ini
        $products_result = $this->jsm_api->get_campaign_products($campaign['id'], [
            'page_size' => 10,
            'review_status' => 'APPROVED'
        ]);
        
        if ($products_result['success']) {
            $products = $products_result['data'];
            echo "  Products: " . count($products) . "\n";
        } else {
            echo "  Products: Error - " . ($products_result['message'] ?? 'Unknown') . "\n";
        }
        
        echo "\n";
    }
}
/**
 * Lihat RAW response dari API - DEBUGGING
 * Usage: php index.php Test_api raw_response
 */
public function raw_response() {
    echo "========================================\n";
    echo "RAW API RESPONSE DEBUG\n";
    echo "========================================\n\n";
    
    // Cek token
    $token = $this->Jsm_token_model->get_latest_token_by_type(3);
    if (!$token) {
        echo "❌ No affiliate token found\n";
        return;
    }
    
    echo "✅ Token found\n\n";
    
    // Parameter sederhana untuk test
    $params = [
        'create_time_ge' => strtotime('2026-04-30 00:00:00'),
        'create_time_lt' => strtotime('2026-05-01 00:00:00'),
        'page_size' => 5
    ];
    
    echo "Request Params:\n";
    echo json_encode($params, JSON_PRETTY_PRINT) . "\n\n";
    
    echo "Calling API...\n";
    
    // Panggil langsung method search_affiliate_orders
    $result = $this->jsm_api->search_affiliate_orders($params);
    
    echo "\n========================================\n";
    echo "RESPONSE:\n";
    echo "========================================\n";
    
    if (!$result['success']) {
        echo "❌ API Error:\n";
        echo "  Message: " . ($result['message'] ?? 'Unknown') . "\n";
        if (isset($result['code'])) {
            echo "  Code: " . $result['code'] . "\n";
        }
        if (isset($result['raw_response'])) {
            echo "\nRaw Response:\n";
            echo json_encode($result['raw_response'], JSON_PRETTY_PRINT) . "\n";
        }
        return;
    }
    
    echo "✅ API Success\n";
    echo "Total orders: " . count($result['data']) . "\n";
    echo "Next page token: " . ($result['next_page_token'] ?? 'null') . "\n\n";
    
    if (empty($result['data'])) {
        echo "No orders found\n";
        return;
    }
    
    echo "========================================\n";
    echo "FIRST ORDER RAW DATA:\n";
    echo "========================================\n";
    
    $first_order = $result['data'][0];
    echo json_encode($first_order, JSON_PRETTY_PRINT) . "\n";
    
    echo "\n========================================\n";
    echo "FIELDS AVAILABLE:\n";
    echo "========================================\n";
    
    $fields = array_keys($first_order);
    foreach ($fields as $field) {
        $value = $first_order[$field];
        if (is_array($value)) {
            echo "  - $field: (array) " . json_encode($value) . "\n";
        } else {
            echo "  - $field: " . var_export($value, true) . "\n";
        }
    }
}


/**
 * DEBUG: Lihat data mentah API untuk creator tertentu
 * Usage: php index.php Cron debug_creator_order erfinanh 2026-05-26
 */
public function debug_creator_order($creator_username = 'erfinanh', $date = '2026-05-26') {
    echo "========================================\n";
    echo "DEBUG RAW API ORDER DATA (UNFORMATTED)\n";
    echo "Date: $date\n";
    echo "========================================\n\n";
    
    $utc_range = $this->get_utc_range_from_local_date($date, 'Asia/Jakarta');
    
    $params = [
        'create_time_ge' => $utc_range['start'],
        'create_time_lt' => $utc_range['end'],
        'page_size' => 50  // Ambil lebih banyak
    ];
    
    // 🔥 GUNAKAN METHOD RAW
    $result = $this->jsm_api->search_affiliate_orders_raw($params);
    
    echo "API Response Success: " . ($result['success'] ? 'YES' : 'NO') . "\n";
    
    if (!$result['success']) {
        echo "Error: " . ($result['message'] ?? 'Unknown') . "\n";
        return;
    }
    
    $all_orders = $result['data'] ?? [];
    echo "Total orders from API: " . count($all_orders) . "\n\n";
    
    // ==========================================
    // TAMPILKAN SEMUA CREATOR YANG ADA
    // ==========================================
    echo "========================================\n";
    echo "ALL CREATORS FOUND IN API RESPONSE\n";
    echo "========================================\n";
    
    $creators = [];
    foreach ($all_orders as $order) {
        $c = $order['creator_username'] ?? '(empty)';
        if (!isset($creators[$c])) {
            $creators[$c] = [
                'count' => 0,
                'sample_order_id' => $order['id'] ?? 'N/A',
                'product_name' => $order['product_name'] ?? 'N/A',
                'price' => json_encode($order['price'] ?? 'N/A'),
                'settle_status' => $order['settle_status'] ?? 'NOT FOUND'
            ];
        }
        $creators[$c]['count']++;
    }
    
    foreach ($creators as $name => $info) {
        echo "  Creator: '$name'\n";
        echo "    Orders: {$info['count']}\n";
        echo "    Sample Order ID: {$info['sample_order_id']}\n";
        echo "    Product: {$info['product_name']}\n";
        echo "    Price (raw): {$info['price']}\n";
        echo "    settle_status: {$info['settle_status']}\n";
        echo "\n";
    }
    
    // ==========================================
    // TAMPILKAN DATA MENTAH ORDER PERTAMA
    // ==========================================
    if (!empty($all_orders)) {
        $first_order = $all_orders[0];
        
        echo "========================================\n";
        echo "ALL RAW FIELDS FROM FIRST ORDER\n";
        echo "========================================\n";
        foreach ($first_order as $key => $value) {
            if (is_array($value)) {
                echo "  $key: " . json_encode($value, JSON_UNESCAPED_UNICODE) . "\n";
            } else {
                echo "  $key: $value\n";
            }
        }
        
        echo "\n========================================\n";
        echo "CRITICAL FIELD CHECK\n";
        echo "========================================\n";
        echo "  id: " . ($first_order['id'] ?? 'NOT FOUND') . "\n";
        echo "  settle_status: " . ($first_order['settle_status'] ?? '❌ NOT FOUND') . "\n";
        echo "  order_status: " . ($first_order['order_status'] ?? 'NOT FOUND') . "\n";
        echo "  price (raw): " . json_encode($first_order['price'] ?? 'NOT FOUND', JSON_UNESCAPED_UNICODE) . "\n";
        echo "  quantity: " . ($first_order['quantity'] ?? 'NOT FOUND') . "\n";
        echo "  estimated_commission_base: " . (isset($first_order['estimated_commission_base']) ? json_encode($first_order['estimated_commission_base'], JSON_UNESCAPED_UNICODE) : '❌ NOT FOUND') . "\n";
        echo "  actual_commission_base: " . (isset($first_order['actual_commission_base']) ? json_encode($first_order['actual_commission_base'], JSON_UNESCAPED_UNICODE) : '❌ NOT FOUND') . "\n";
        echo "  estimated_partner_standard_commission: " . (isset($first_order['estimated_partner_standard_commission']) ? json_encode($first_order['estimated_partner_standard_commission'], JSON_UNESCAPED_UNICODE) : '❌ NOT FOUND') . "\n";
        echo "  actual_partner_standard_commission: " . (isset($first_order['actual_partner_standard_commission']) ? json_encode($first_order['actual_partner_standard_commission'], JSON_UNESCAPED_UNICODE) : '❌ NOT FOUND') . "\n";
        echo "  estimated_creator_standard_commission: " . (isset($first_order['estimated_creator_standard_commission']) ? json_encode($first_order['estimated_creator_standard_commission'], JSON_UNESCAPED_UNICODE) : '❌ NOT FOUND') . "\n";
        echo "  actual_creator_standard_commission: " . (isset($first_order['actual_creator_standard_commission']) ? json_encode($first_order['actual_creator_standard_commission'], JSON_UNESCAPED_UNICODE) : '❌ NOT FOUND') . "\n";
        echo "  fully_return: " . ($first_order['fully_return'] ?? 'NOT FOUND') . "\n";
        echo "  sku_id: " . ($first_order['sku_id'] ?? 'NOT FOUND') . "\n";
        echo "  content_type: " . ($first_order['content_type'] ?? 'NOT FOUND') . "\n";
    }
    
    // Simpan SEMUA data mentah ke file
    $debug_file = FCPATH . "debug_raw_all_{$date}.json";
    file_put_contents($debug_file, json_encode($all_orders, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "\n✅ Full raw data saved to: $debug_file\n";
}
    private function get_utc_range_from_local_date($local_date, $timezone = 'Asia/Jakarta') {
        $date = new DateTime($local_date, new DateTimeZone($timezone));
        $date->setTime(0, 0, 0);
        $date->setTimezone(new DateTimeZone('UTC'));
        $start_utc = $date->getTimestamp();
        $end_utc = $start_utc + 86400;
        
        return [
            'start' => $start_utc,
            'end' => $end_utc,
            'start_formatted' => gmdate('Y-m-d H:i:s', $start_utc) . ' UTC',
            'end_formatted' => gmdate('Y-m-d H:i:s', $end_utc) . ' UTC',
            'local_date' => $local_date
        ];
    }
    
    
    
     /**
 * DEBUG: Get authorized category assets (chipset)
 * URL: /tts/debug_category_assets
 */
public function debug_category_assets() {
    $this->output->set_content_type('application/json');
    
    // 🔥 PERBAIKAN: gunakan $this->jsm_api (huruf kecil), bukan $this->Jsm_api
    $result = $this->jsm_api->get_authorized_category_assets();
    
    if ($result['success']) {
        $assets = $result['data']['category_assets'] ?? [];
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'total_assets' => count($assets),
            'category_assets' => $assets,
            'default_cipher' => $this->jsm_api->default_cipher,
            'note' => 'Gunakan category_asset_cipher yang sesuai untuk multi link'
        ], JSON_PRETTY_PRINT));
    } else {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $result['message'],
            'code' => $result['code'] ?? null
        ], JSON_PRETTY_PRINT));
    }
}


/**
 * DEBUG: Bandingkan Single Link dan Multi Link dengan product yang sama
 * URL: /test_api/compare_single_vs_multi?campaign_id=xxx&product_id=xxx
 */
public function compare_single_vs_multi() {
    $this->output->set_content_type('application/json');
    
    $campaign_id = $this->input->get('campaign_id');
    $product_id = $this->input->get('product_id');
    
    if (empty($campaign_id) || empty($product_id)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'campaign_id and product_id required'
        ]));
    }
    
    $results = [];
    
    // 1. Test SINGLE LINK
    log_message('debug', 'Testing SINGLE LINK for product: ' . $product_id);
    $single_result = $this->jsm_api->generate_promotion_link($campaign_id, $product_id, 10);
    $results['single_link'] = [
        'endpoint' => '/affiliate_partner/202405/campaigns/{id}/products/{id}/promotion_link/generate',
        'product_id_type' => 'string (single)',
        'success' => $single_result['success'],
        'code' => $single_result['code'] ?? null,
        'message' => $single_result['message'] ?? null,
        'link' => $single_result['link'] ?? null
    ];
    
    // 2. Test MULTI LINK dengan 1 product (harusnya sama hasilnya)
    log_message('debug', 'Testing MULTI LINK with same product: ' . $product_id);
    $multi_result = $this->jsm_api->generate_multi_affiliate_links($campaign_id, [$product_id], $this->jsm_api->default_cipher);
    $results['multi_link_1_product'] = [
        'endpoint' => '/affiliate_partner/202505/campaigns/{id}/products/promotion_links/generate_batch',
        'product_ids_type' => 'array of strings (1 item)',
        'product_ids' => [$product_id],
        'success' => $multi_result['success'],
        'code' => $multi_result['code'] ?? null,
        'message' => $multi_result['message'] ?? null
    ];
    
    // 3. Test MULTI LINK dengan 2 product
    $product_id2 = $this->input->get('product_id2');
    if ($product_id2) {
        $multi_result2 = $this->jsm_api->generate_multi_affiliate_links($campaign_id, [$product_id, $product_id2], $this->jsm_api->default_cipher);
        $results['multi_link_2_products'] = [
            'product_ids' => [$product_id, $product_id2],
            'success' => $multi_result2['success'],
            'code' => $multi_result2['code'] ?? null,
            'message' => $multi_result2['message'] ?? null
        ];
    }
    
    return $this->output->set_output(json_encode([
        'campaign_id' => $campaign_id,
        'cipher_used' => $this->jsm_api->default_cipher,
        'results' => $results,
        'analysis' => [
            'single_link_works' => $results['single_link']['success'],
            'multi_link_with_same_product_works' => $results['multi_link_1_product']['success'],
            'conclusion' => ($results['single_link']['success'] && !$results['multi_link_1_product']['success']) 
                ? 'Multi link endpoint mungkin tidak diaktifkan untuk akun ini' 
                : 'Keduanya berhasil atau gagal bersama'
        ]
    ], JSON_PRETTY_PRINT));
}
public function debug_multi_int() {
    $this->output->set_content_type('application/json');
    
    $campaign_id = $this->input->get('campaign_id');
    $product_ids_str = $this->input->get('product_ids');
    
    if (empty($campaign_id) || empty($product_ids_str)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'campaign_id and product_ids required'
        ]));
    }
    
    $product_ids_raw = explode(',', $product_ids_str);
    
    // 🔥 FORMAT INTEGER (seperti contoh curl)
    $product_ids_int = array_map('intval', $product_ids_raw);
    
    $cipher = 'ROW_fyGlKwAAAAB6jCmj_Z8Zc6uknZJUdZAi';
    
    log_message('debug', 'Testing multi link with INTEGER format: ' . json_encode($product_ids_int));
    
    $result = $this->jsm_api->generate_multi_affiliate_links($campaign_id, $product_ids_int, $cipher);
    
    return $this->output->set_output(json_encode([
        'campaign_id' => $campaign_id,
        'cipher' => $cipher,
        'product_ids_as_int' => $product_ids_int,
        'result' => $result
    ], JSON_PRETTY_PRINT));
}


}