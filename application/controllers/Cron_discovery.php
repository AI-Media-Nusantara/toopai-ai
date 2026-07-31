<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cron Discovery Controller
 * Khusus untuk menjalankan discovery creator dari FastMoss
 * 
 * Cara panggil:
 *   - CLI: php index.php cron_discovery process_approved_products
 *   - URL: /cron_discovery/process_approved_products?token=YOUR_TOKEN
 */
class Cron_discovery extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        
        $this->load->database();
        $this->load->model('Fastmoss_model');
        $this->load->model('BrandCreator_model');
        $this->load->model('User_log_model');
        
        set_time_limit(0);
        ini_set('memory_limit', '512M');
    }
    
    /**
     * Index - Menampilkan status (optional)
     */
    public function index() {
        $token = $this->input->get('token');
        $cron_token = CRON_SECRET_TOKEN ?? 'Toopai2026?_12345';
        
        if ($token !== $cron_token && !$this->input->is_cli_request()) {
            die('Access denied');
        }
        
        echo "=== Cron Discovery Controller ===\n";
        echo "Available methods:\n";
        echo "  - process_approved_products\n";
        echo "  - process_single\n";
        echo "  - check_product\n";
        echo "  - status\n";
        echo "  - test_connection\n";
    }
    
    /**
     * ============================================================
     * CHECK PRODUCT - Cek status product di database
     * ============================================================
     * 
     * Cara panggil: /cron_discovery/check_product?product_id=xxx&token=YOUR_TOKEN
     */
    public function check_product() {
        $token = $this->input->get('token');
        $cron_token = CRON_SECRET_TOKEN ?? 'Toopai2026?_12345';
        
        if ($token !== $cron_token) {
            die('Access denied');
        }
        
        $product_id = $this->input->get('product_id');
        if (empty($product_id)) {
            die('Product ID required');
        }
        
        header('Content-Type: application/json');
        
        // Ambil product dari affiliate_products
        $product = $this->db->select('product_id, product_name, shop_name, review_status, discovery_processed, discovery_processed_at, created_at, updated_at')
            ->where('product_id', $product_id)
            ->get('affiliate_products')
            ->row();
        
        if (!$product) {
            echo json_encode([
                'success' => false,
                'error' => 'Product not found in affiliate_products'
            ], JSON_PRETTY_PRINT);
            return;
        }
        
        // Cek di brand_creators
        $brand_creators = $this->db->select('bc.id, bc.creator_username, bc.creator_nickname, bcp.product_id, bcp.product_name, bcp.sales_count')
            ->from('brand_creators bc')
            ->join('brand_creator_products bcp', 'bc.id = bcp.brand_creator_id')
            ->where('bcp.product_id', $product_id)
            ->get()
            ->result();
        
        // Cek apakah ada brand
        $brand = $this->db->select('id, name, shop_name')
            ->where('shop_name', $product->shop_name)
            ->get('brands')
            ->row();
        
        echo json_encode([
            'success' => true,
            'product' => $product,
            'brand' => $brand,
            'brand_creators_count' => count($brand_creators),
            'brand_creators' => $brand_creators,
            'can_process' => ($product->review_status === 'APPROVED' && $product->discovery_processed == 0 && !empty($brand))
        ], JSON_PRETTY_PRINT);
    }
    
    /**
     * ============================================================
     * PROCESS SINGLE PRODUCT - Force process 1 product
     * ============================================================
     * 
     * Cara panggil: /cron_discovery/process_single?product_id=xxx&token=YOUR_TOKEN
     */
    public function process_single() {
        $token = $this->input->get('token');
        $cron_token = CRON_SECRET_TOKEN ?? 'Toopai2026?_12345';
        
        if ($token !== $cron_token) {
            die('Access denied');
        }
        
        $product_id = $this->input->get('product_id');
        if (empty($product_id)) {
            die('Product ID required');
        }
        
        header('Content-Type: application/json');
        
        // Ambil product dari affiliate_products
        $product = $this->db->select('ap.*, b.id as brand_id, b.name as brand_name, b.shop_name')
            ->from('affiliate_products ap')
            ->join('brands b', 'ap.shop_name = b.shop_name', 'left')
            ->where('ap.product_id', $product_id)
            ->get()
            ->row();
        
        if (!$product) {
            echo json_encode([
                'success' => false,
                'error' => 'Product not found in affiliate_products'
            ], JSON_PRETTY_PRINT);
            return;
        }
        
        // Jika review_status bukan APPROVED, skip
        if ($product->review_status !== 'APPROVED') {
            echo json_encode([
                'success' => false,
                'error' => 'Product is not APPROVED (status: ' . $product->review_status . ')'
            ], JSON_PRETTY_PRINT);
            return;
        }
        
        // Jika tidak ada brand_id
        if (empty($product->brand_id)) {
            echo json_encode([
                'success' => false,
                'error' => 'No brand found for shop: ' . $product->shop_name
            ], JSON_PRETTY_PRINT);
            return;
        }
        
        // Force reset discovery_processed
        $this->db->where('product_id', $product_id)
                 ->update('affiliate_products', [
                     'discovery_processed' => 0,
                     'discovery_processed_at' => null
                 ]);
        
        // Proses
        $result = $this->_process_single_product($product);
        
        echo json_encode($result, JSON_PRETTY_PRINT);
    }
    
    /**
     * Process single product (internal)
     */
    private function _process_single_product($product) {
        $result = [
            'success' => false,
            'product_id' => $product->product_id,
            'product_name' => $product->product_name,
            'shop_name' => $product->shop_name,
            'brand_id' => $product->brand_id,
            'review_status' => $product->review_status,
            'discovery_processed' => $product->discovery_processed ?? 0,
            'steps' => []
        ];
        
        try {
            // STEP 1: Ambil dari FastMoss
            $result['steps'][] = 'Fetching creators from FastMoss...';
            
            $fastmoss_result = $this->BrandCreator_model->get_product_creators($product->product_id);
            
            if (!$fastmoss_result['status'] || empty($fastmoss_result['creators'])) {
                $result['steps'][] = 'No creators found on FastMoss';
                $result['status'] = 'no_creators';
                $result['message'] = 'No creators found for this product on FastMoss';
                $this->BrandCreator_model->mark_product_processed($product->product_id);
                return $result;
            }
            
            $creators = $fastmoss_result['creators'];
            $result['steps'][] = 'Found ' . count($creators) . ' creators';
            $result['creators_found'] = count($creators);
            
            // STEP 2: Simpan ke database
            $brand_id = $product->brand_id;
            if ($brand_id) {
                $result['steps'][] = 'Saving creators to brand_creators (brand_id: ' . $brand_id . ')';
                
                $save_result = $this->BrandCreator_model->save_creators_from_fastmoss(
                    $brand_id,
                    $creators,
                    1 // Toopai
                );
                
                $result['saved'] = $save_result['saved'];
                $result['added_to_creators'] = $save_result['added_to_creators'];
                $result['skipped'] = $save_result['skipped'];
                $result['steps'][] = 'Saved: ' . $save_result['saved'] . ' | Added to creators: ' . $save_result['added_to_creators'];
            } else {
                $result['steps'][] = 'No brand_id found, cannot save';
            }
            
            // STEP 3: Mark as processed
            $this->BrandCreator_model->mark_product_processed($product->product_id);
            $result['steps'][] = 'Marked as processed';
            $result['success'] = true;
            $result['status'] = 'success';
            $result['message'] = 'Product processed successfully';
            
        } catch (Exception $e) {
            $result['steps'][] = 'ERROR: ' . $e->getMessage();
            $result['success'] = false;
            $result['status'] = 'error';
            $result['message'] = $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * ============================================================
     * MAIN CRON JOB: Process approved products
     * ============================================================
     * 
     * Cara panggil:
     *   CLI: php index.php cron_discovery process_approved_products
     *   URL: /cron_discovery/process_approved_products?token=YOUR_TOKEN
     */
    public function process_approved_products() {
        // 🔒 Security check
        $is_cli = $this->input->is_cli_request();
        $token = $this->input->get('token');
        $cron_token = CRON_SECRET_TOKEN ?? 'Toopai2026?_12345';
        
        if (!$is_cli && $token !== $cron_token) {
            $this->_log_error('Access denied - Invalid token');
            die('Access denied');
        }
        
        // Log start
        $start_time = microtime(true);
        $start_date = date('Y-m-d H:i:s');
        $this->_log("[START] {$start_date} - Starting discovery process");
        
        // ========== AMBIL PRODUCT YANG BARU APPROVED ==========
        $products = $this->BrandCreator_model->get_newly_approved_products(20);
        
        if (empty($products)) {
            $message = "[{$start_date}] No newly approved products found\n";
            $this->_log($message);
            echo $message;
            
            $this->User_log_model->log(
                1,
                'system',
                'SYSTEM',
                'CRON_DISCOVERY',
                "No newly approved products found"
            );
            return;
        }
        
        $message = "[{$start_date}] Found " . count($products) . " newly approved products\n";
        $this->_log($message);
        echo $message;
        
        $total_creators = 0;
        $total_added = 0;
        $processed = 0;
        $errors = 0;
        $error_details = [];
        
        foreach ($products as $product) {
            $log_msg = "\n--- Processing: {$product->product_id} | " . substr($product->product_name, 0, 50) . "...\n";
            $this->_log($log_msg);
            echo $log_msg;
            
            try {
                // Cek sudah diproses
                if ($this->BrandCreator_model->is_product_processed($product->product_id)) {
                    $msg = "  ⚠️ Already processed, skipping...\n";
                    $this->_log($msg);
                    echo $msg;
                    continue;
                }
                
                // Ambil dari FastMoss
                $msg = "  📡 Fetching from FastMoss...\n";
                $this->_log($msg);
                echo $msg;
                
                $result = $this->BrandCreator_model->get_product_creators($product->product_id);
                
                if (!$result['status'] || empty($result['creators'])) {
                    $msg = "  ⚠️ No creators found\n";
                    $this->_log($msg);
                    echo $msg;
                    $this->BrandCreator_model->mark_product_processed($product->product_id);
                    $processed++;
                    continue;
                }
                
                $creators = $result['creators'];
                $msg = "  ✅ Found " . count($creators) . " creators\n";
                $this->_log($msg);
                echo $msg;
                
                // Simpan ke database
                $brand_id = $product->brand_id;
                if (!$brand_id) {
                    $msg = "  ⚠️ No brand_id for shop: {$product->shop_name}\n";
                    $this->_log($msg);
                    echo $msg;
                    $this->BrandCreator_model->mark_product_processed($product->product_id);
                    $processed++;
                    continue;
                }
                
                $save_result = $this->BrandCreator_model->save_creators_from_fastmoss(
                    $brand_id,
                    $creators,
                    1 // Toopai
                );
                
                $total_creators += count($creators);
                $total_added += $save_result['added_to_creators'];
                
                $msg = "  💾 Saved: {$save_result['saved']} | Added to creators: {$save_result['added_to_creators']} | Skipped: {$save_result['skipped']}\n";
                $this->_log($msg);
                echo $msg;
                
                // Mark as processed
                $this->BrandCreator_model->mark_product_processed($product->product_id);
                $processed++;
                
            } catch (Exception $e) {
                $errors++;
                $error_msg = "  ❌ Error: " . $e->getMessage() . "\n";
                $this->_log($error_msg);
                echo $error_msg;
                $error_details[] = [
                    'product_id' => $product->product_id,
                    'product_name' => $product->product_name,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        $end_time = microtime(true);
        $duration = round($end_time - $start_time, 2);
        $summary = "Processed {$processed} products, found {$total_creators} creators, added {$total_added} to creators, skipped " . ($total_creators - $total_added) . ", errors {$errors}, duration {$duration}s";
        
        // Log ke database
        $this->User_log_model->log(
            1,
            'system',
            'SYSTEM',
            'CRON_DISCOVERY',
            $summary . ( !empty($error_details) ? ' | Errors: ' . json_encode($error_details) : '' )
        );
        
        $final = "\n[" . date('Y-m-d H:i:s') . "] DONE: {$summary}\n";
        $this->_log($final);
        echo $final;
    }
    
    /**
     * Get status - Cek jumlah produk yang pending
     * 
     * Cara panggil: /cron_discovery/status?token=YOUR_TOKEN
     */
    public function status() {
        $token = $this->input->get('token');
        $cron_token = CRON_SECRET_TOKEN ?? 'Toopai2026?_12345';
        
        if ($token !== $cron_token && !$this->input->is_cli_request()) {
            die('Access denied');
        }
        
        header('Content-Type: application/json');
        
        $pending = $this->db->select('COUNT(*) as total')
            ->from('affiliate_products')
            ->where('review_status', 'APPROVED')
            ->where('discovery_processed', 0)
            ->where('shop_name IS NOT NULL')
            ->where('shop_name !=', '')
            ->get()
            ->row();
        
        $total_creators = $this->db->count_all('brand_creators');
        $total_in_creators = $this->db->where('source', 'imported')
            ->count_all_results('creators');
        
        $last_run = $this->db->select('MAX(created_at) as last')
            ->from('user_logs')
            ->where('action', 'CRON_DISCOVERY')
            ->get()
            ->row();
        
        echo json_encode([
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s'),
            'pending_products' => intval($pending->total ?? 0),
            'total_creators_discovered' => $total_creators,
            'total_creators_in_task1' => $total_in_creators,
            'last_run' => $last_run->last ?? 'Never'
        ], JSON_PRETTY_PRINT);
    }
    
    /**
     * Test connection - Cek koneksi ke FastMoss
     * 
     * Cara panggil: /cron_discovery/test_connection?token=YOUR_TOKEN
     */
    public function test_connection() {
        $token = $this->input->get('token');
        $cron_token = CRON_SECRET_TOKEN ?? 'Toopai2026?_12345';
        
        if ($token !== $cron_token && !$this->input->is_cli_request()) {
            die('Access denied');
        }
        
        header('Content-Type: application/json');
        
        $result = [
            'status' => 'testing',
            'timestamp' => date('Y-m-d H:i:s'),
            'fastmoss' => [
                'status' => 'unknown',
                'test_product_id' => '1735421655417849149'
            ],
            'database' => [
                'status' => 'connected',
                'tables' => []
            ]
        ];
        
        // Test database
        $tables = ['affiliate_products', 'brands', 'brand_creators', 'creators'];
        foreach ($tables as $table) {
            $count = $this->db->count_all($table);
            $result['database']['tables'][$table] = $count;
        }
        
        // Test FastMoss
        try {
            $test_result = $this->Fastmoss_model->get_product_base('1735421655417849149');
            if (!empty($test_result)) {
                $result['fastmoss']['status'] = 'connected';
                $result['fastmoss']['product_name'] = $test_result['title'] ?? 'Unknown';
            } else {
                $result['fastmoss']['status'] = 'error';
                $result['fastmoss']['message'] = 'No data returned';
            }
        } catch (Exception $e) {
            $result['fastmoss']['status'] = 'error';
            $result['fastmoss']['message'] = $e->getMessage();
        }
        
        echo json_encode($result, JSON_PRETTY_PRINT);
    }
    
    private function _log($message) {
        $log_dir = APPPATH . 'logs/sync/';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0777, true);
        }
        
        $log_file = $log_dir . 'process_fastmoss.log';
        file_put_contents($log_file, $message, FILE_APPEND);
    }
    
    private function _log_error($message) {
        $log_dir = APPPATH . 'logs/sync/';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0777, true);
        }
        
        $log_file = $log_dir . 'process_fastmoss_error.log';
        $log_msg = "[" . date('Y-m-d H:i:s') . "] ERROR: " . $message . "\n";
        file_put_contents($log_file, $log_msg, FILE_APPEND);
    }
    
     /**
     * ============================================================
     * SYNC SINGLE CREATOR PRODUCTS - Ambil semua produk dari 1 creator
     * ============================================================
     * 
     * Cara panggil: /cron_discovery/sync_creator?creator_id=xxx&token=YOUR_TOKEN
     */
    public function sync_creator() {
    $token = $this->input->get('token');
    $cron_token = CRON_SECRET_TOKEN ?? 'Toopai2026?_12345';
    
    if ($token !== $cron_token) {
        die('Access denied');
    }
    
    $creator_id = $this->input->get('creator_id');
    if (empty($creator_id)) {
        die('Creator ID required');
    }
    
    header('Content-Type: application/json');
    
    // Ambil creator
    $creator = $this->db->select('id, username, full_name, tiktok_open_id, brand_id')
        ->where('id', $creator_id)
        ->get('creators')
        ->row();
    
    if (!$creator) {
        echo json_encode(['error' => 'Creator not found'], JSON_PRETTY_PRINT);
        return;
    }
    
    if (empty($creator->tiktok_open_id)) {
        echo json_encode([
            'error' => 'Creator has no fastmoss_uid (tiktok_open_id)',
            'creator' => $creator
        ], JSON_PRETTY_PRINT);
        return;
    }
    
    // Sync ke creator_products
    $result = $this->BrandCreator_model->sync_creator_products_to_db(
        $creator->id,
        $creator->username,
        $creator->tiktok_open_id
    );
    
    echo json_encode($result, JSON_PRETTY_PRINT);
}

    /**
     * ============================================================
     * SYNC ALL CREATOR PRODUCTS - Ambil semua produk dari semua creator
     * ============================================================
     * 
     * Cara panggil: /cron_discovery/sync_creator_products?token=YOUR_TOKEN
     */
    public function sync_creator_products() {
        $token = $this->input->get('token');
        $cron_token = CRON_SECRET_TOKEN ?? 'Toopai2026?_12345';
        
        if ($token !== $cron_token && !$this->input->is_cli_request()) {
            die('Access denied');
        }
        
        $start_time = microtime(true);
        $start_date = date('Y-m-d H:i:s');
        
        $this->_log("[START] {$start_date} - Starting sync creator products\n");
        echo "[START] {$start_date} - Starting sync creator products\n";
        
        // ========== AMBIL CREATOR YANG PUNYA FASTMOSS_UID ==========
        $limit = 5;
        $creators = $this->BrandCreator_model->get_creators_with_missing_products($limit);
        
        if (empty($creators)) {
            $message = "[{$start_date}] No creators with missing products found\n";
            $this->_log($message);
            echo $message;
            return;
        }
        
        $message = "[{$start_date}] Found " . count($creators) . " creators with missing products\n";
        $this->_log($message);
        echo $message;
        
        $total_products = 0;
        $processed = 0;
        $errors = 0;
        $error_details = [];
        
        foreach ($creators as $creator) {
            $log_msg = "\n" . str_repeat('=', 60) . "\n";
            $log_msg .= "📦 Processing Creator: @" . $creator->username . "\n";
            $log_msg .= "   FastMoss UID: " . ($creator->fastmoss_uid ?? 'N/A') . "\n";
            $log_msg .= "   Brand ID: " . ($creator->brand_id ?? 'N/A') . "\n";
            $log_msg .= "   Current Products: " . ($creator->product_count ?? 0) . "\n";
            $log_msg .= str_repeat('=', 60) . "\n";
            
            $this->_log($log_msg);
            echo $log_msg;
            
            try {
                $result = $this->BrandCreator_model->get_products_by_creator_uid($creator->fastmoss_uid);
                
                if ($result['success']) {
                    $total_products += $result['total_found'];
                    $processed++;
                    
                    $msg = "   ✅ Found: {$result['total_found']} products\n";
                    
                    // Tampilkan sample produk
                    if (!empty($result['products'])) {
                        $sample = array_slice($result['products'], 0, 3);
                        foreach ($sample as $idx => $p) {
                            $msg .= "      " . ($idx+1) . ". " . substr($p['product_name'] ?? 'Unknown', 0, 50) . " - Sales: " . ($p['sales_count'] ?? 0) . "\n";
                        }
                        if (count($result['products']) > 3) {
                            $msg .= "      ... and " . (count($result['products']) - 3) . " more products\n";
                        }
                    }
                    
                    $this->_log($msg);
                    echo $msg;
                } else {
                    $msg = "   ❌ Failed: " . $result['message'] . "\n";
                    $this->_log($msg);
                    echo $msg;
                }
                
            } catch (Exception $e) {
                $errors++;
                $error_msg = "   ❌ Error: " . $e->getMessage() . "\n";
                $this->_log($error_msg);
                echo $error_msg;
                $error_details[] = [
                    'creator_id' => $creator->id,
                    'username' => $creator->username,
                    'error' => $e->getMessage()
                ];
            }
            
            sleep(rand(3, 8));
        }
        
        $end_time = microtime(true);
        $duration = round($end_time - $start_time, 2);
        
        $summary = "\n" . str_repeat('=', 60) . "\n";
        $summary .= "📊 SUMMARY\n";
        $summary .= str_repeat('=', 60) . "\n";
        $summary .= "Creators processed: {$processed}\n";
        $summary .= "Total products found: {$total_products}\n";
        $summary .= "Errors: {$errors}\n";
        $summary .= "Duration: {$duration}s\n";
        $summary .= str_repeat('=', 60) . "\n";
        
        $this->_log($summary);
        echo $summary;
        
        $this->User_log_model->log(
            1,
            'system',
            'SYSTEM',
            'CRON_SYNC_CREATOR_PRODUCTS',
            $summary . ( !empty($error_details) ? ' | Errors: ' . json_encode($error_details) : '' )
        );
    }
    
    public function test_fastmoss_product() {
    $token = $this->input->get('token');
    $cron_token = CRON_SECRET_TOKEN ?? 'Toopai2026?_12345';
    
    if ($token !== $cron_token) {
        die('Access denied');
    }
    
    header('Content-Type: application/json');
    
    $product_id = $this->input->get('product_id') ?: '1734321798647809081';
    
    $result = $this->Fastmoss_model->get_product_creators($product_id);
    
    echo json_encode([
        'product_id' => $product_id,
        'result' => $result
    ], JSON_PRETTY_PRINT);
}

}