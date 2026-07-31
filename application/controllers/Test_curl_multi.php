<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test_curl_multi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('Jsm_api', ['api_type' => 'TOOPAI']);
    }

    /**
     * Step 1: Lihat daftar produk di campaign
     */
    public function list_products() {
        echo "<h1>📋 List Products in Campaign</h1>";
        echo "<pre>";
        
        $campaign_id = "7626279878945982225";
        
        // Gunakan method dari Jsm_api
        $result = $this->jsm_api->get_campaign_products($campaign_id, ['page_size' => 50]);
        
        echo "Result:\n";
        print_r($result);
        
        if ($result['success'] && !empty($result['data'])) {
            echo "\n✅ Found " . count($result['data']) . " products:\n";
            echo str_repeat("=", 80) . "\n";
            
            $product_ids = [];
            $valid_ids = [];
            
            foreach ($result['data'] as $idx => $product) {
                $product_ids[] = $product['id'];
                $is_valid = ($product['status'] === 'ACTIVE' && $product['review_status'] === 'APPROVED');
                if ($is_valid) {
                    $valid_ids[] = $product['id'];
                }
                
                echo sprintf("[%d] ID: %s\n", $idx + 1, $product['id']);
                echo "    Name: " . $product['name'] . "\n";
                echo "    Status: " . $product['status'] . "\n";
                echo "    Review: " . $product['review_status'] . "\n";
                echo "    Valid: " . ($is_valid ? '✅ YES' : '❌ NO') . "\n";
                echo str_repeat("-", 80) . "\n";
            }
            
            echo "\n📋 ALL Product IDs:\n";
            echo json_encode($product_ids, JSON_PRETTY_PRINT) . "\n";
            
            echo "\n✅ VALID Product IDs (ACTIVE + APPROVED):\n";
            echo json_encode($valid_ids, JSON_PRETTY_PRINT) . "\n";
            
            // Auto-generate links jika ada valid IDs
            if (!empty($valid_ids)) {
                echo "\n🚀 Auto-generating links for valid products...\n";
                $this->_generate_links($campaign_id, array_slice($valid_ids, 0, 5));
            }
            
        } else {
            echo "❌ No products found or error: " . ($result['message'] ?? 'Unknown error');
        }
        
        echo "</pre>";
    }

    /**
     * Step 2: Generate links dengan product ID yang valid otomatis
     */
    public function generate_valid_links() {
        echo "<h1>🚀 Generate Links with Valid Product IDs</h1>";
        echo "<pre>";
        
        $campaign_id = "7626279878945982225";
        
        // Ambil product ID dari campaign
        $products_result = $this->jsm_api->get_campaign_products($campaign_id, ['page_size' => 50]);
        
        if (!$products_result['success'] || empty($products_result['data'])) {
            die("❌ No products found in campaign. Please check campaign ID.\n");
        }
        
        // Ambil product ID yang statusnya ACTIVE dan APPROVED
        $valid_product_ids = [];
        foreach ($products_result['data'] as $product) {
            if ($product['status'] === 'ACTIVE' && $product['review_status'] === 'APPROVED') {
                $valid_product_ids[] = $product['id'];
            }
        }
        
        if (empty($valid_product_ids)) {
            // Fallback: ambil semua product ID
            foreach ($products_result['data'] as $product) {
                $valid_product_ids[] = $product['id'];
            }
        }
        
        if (empty($valid_product_ids)) {
            die("❌ No valid product IDs found.\n");
        }
        
        // Batasi maksimal 5 produk untuk testing
        $test_ids = array_slice($valid_product_ids, 0, 5);
        
        echo "✅ Total valid products: " . count($valid_product_ids) . "\n";
        echo "✅ Testing with: " . json_encode($test_ids) . "\n\n";
        
        $this->_generate_links($campaign_id, $test_ids);
        
        echo "</pre>";
    }

    /**
     * Generate links untuk product IDs tertentu
     */
    private function _generate_links($campaign_id, $product_ids) {
        if (empty($product_ids)) {
            echo "❌ No product IDs provided.\n";
            return;
        }
        
        // Konversi ke integer (sesuai kebutuhan API)
        $int_ids = array_map('intval', $product_ids);
        
        echo "Generating links for " . count($int_ids) . " products...\n";
        echo "Product IDs (int): " . json_encode($int_ids) . "\n\n";
        
        $result = $this->jsm_api->generate_multi_affiliate_links($campaign_id, $int_ids);
        
        echo "Result:\n";
        print_r($result);
        
        if ($result['success']) {
            echo "\n✅ SUCCESS! Links generated:\n";
            echo str_repeat("=", 80) . "\n";
            
            foreach ($result['data']['promotion_links'] ?? [] as $link) {
                echo "Product ID: " . $link['product_id'] . "\n";
                echo "Link: " . $link['promotion_link'] . "\n";
                echo str_repeat("-", 80) . "\n";
            }
            
            if (!empty($result['failed_product_ids'])) {
                echo "\n⚠️ Failed product IDs: " . json_encode($result['failed_product_ids']) . "\n";
            }
        } else {
            echo "\n❌ Failed: " . ($result['message'] ?? 'Unknown error');
            if (isset($result['code'])) {
                echo "\nError Code: " . $result['code'];
            }
            if (isset($result['failed_product_ids'])) {
                echo "\nFailed product IDs: " . json_encode($result['failed_product_ids']);
            }
        }
    }

    /**
     * Test dengan product ID manual
     * URL: /test_curl_multi/test_manual/1735573002271360326
     */
    public function test_manual($product_id = null) {
        echo "<h1>🔧 Test Manual Single Product</h1>";
        echo "<pre>";
        
        if (empty($product_id)) {
            echo "❌ Please provide product ID in URL.\n";
            echo "Example: /test_curl_multi/test_manual/1735573002271360326\n";
            echo "</pre>";
            return;
        }
        
        $campaign_id = "7626279878945982225";
        $product_ids = [intval($product_id)];
        
        echo "Campaign ID: $campaign_id\n";
        echo "Product ID: $product_id\n\n";
        
        $result = $this->jsm_api->generate_multi_affiliate_links($campaign_id, $product_ids);
        
        echo "Result:\n";
        print_r($result);
        
        if ($result['success']) {
            echo "\n✅ SUCCESS!\n";
            foreach ($result['data']['promotion_links'] ?? [] as $link) {
                echo "Link: " . $link['promotion_link'] . "\n";
            }
        } else {
            echo "\n❌ Failed: " . ($result['message'] ?? 'Unknown error');
        }
        
        echo "</pre>";
    }

    /**
     * Test multi dengan product ID manual
     * URL: /test_curl_multi/test_multi/1735573002271360326,1735573134095910214
     */
    public function test_multi($ids = null) {
        echo "<h1>🔧 Test Manual Multi Products</h1>";
        echo "<pre>";
        
        if (empty($ids)) {
            echo "❌ Please provide product IDs in URL (comma separated).\n";
            echo "Example: /test_curl_multi/test_multi/1735573002271360326,1735573134095910214\n";
            echo "</pre>";
            return;
        }
        
        $campaign_id = "7626279878945982225";
        $product_ids = array_map('intval', explode(',', $ids));
        
        echo "Campaign ID: $campaign_id\n";
        echo "Product IDs: " . json_encode($product_ids) . "\n\n";
        
        $result = $this->jsm_api->generate_multi_affiliate_links($campaign_id, $product_ids);
        
        echo "Result:\n";
        print_r($result);
        
        if ($result['success']) {
            echo "\n✅ SUCCESS!\n";
            foreach ($result['data']['promotion_links'] ?? [] as $link) {
                echo "Product ID: " . $link['product_id'] . "\n";
                echo "Link: " . $link['promotion_link'] . "\n\n";
            }
        } else {
            echo "\n❌ Failed: " . ($result['message'] ?? 'Unknown error');
        }
        
        echo "</pre>";
    }

    /**
     * Debug campaign products dengan detail lengkap
     */
    public function debug_campaign_products() {
        echo "<h1>🐛 Debug Campaign Products (Raw)</h1>";
        echo "<pre>";
        
        $campaign_id = "7626279878945982225";
        $access_token = $this->get_valid_token();
        $cipher = $this->jsm_api->default_cipher;
        $app_key = "6jo4rjnr8ouc9";
        $app_secret = "8ceeae7a9ba3726cb9f2e9d831020c91fad4d99c";
        $timestamp = time();
        
        $path = "/affiliate_partner/202405/campaigns/{$campaign_id}/products";
        
        $query_params = [
            'app_key' => $app_key,
            'timestamp' => $timestamp,
            'category_asset_cipher' => $cipher,
            'page_size' => 50
        ];
        
        ksort($query_params);
        
        $param_string = '';
        foreach ($query_params as $key => $value) {
            $param_string .= $key . $value;
        }
        
        $string_to_sign = $app_secret . $path . $param_string . $app_secret;
        $query_params['sign'] = hash_hmac('sha256', $string_to_sign, $app_secret);
        
        $url = "https://open-api.tiktokglobalshop.com" . $path . '?' . http_build_query($query_params);
        
        echo "URL: $url\n\n";
        echo "Cipher used: $cipher\n";
        echo "Token: " . substr($access_token, 0, 50) . "...\n\n";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "x-tts-access-token: {$access_token}",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        
        // Capture verbose
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        
        rewind($verbose);
        $verbose_log = stream_get_contents($verbose);
        fclose($verbose);
        
        curl_close($ch);
        
        echo "HTTP Code: $http_code\n";
        if ($curl_error) {
            echo "cURL Error: $curl_error\n";
        }
        echo "\nVerbose:\n$verbose_log\n";
        
        $decoded = json_decode($response, true);
        if ($decoded) {
            echo "Response:\n";
            print_r($decoded);
            
            if (isset($decoded['data']['products']) && !empty($decoded['data']['products'])) {
                echo "\n✅ Product IDs found:\n";
                $ids = [];
                $valid_ids = [];
                foreach ($decoded['data']['products'] as $product) {
                    $id = $product['id'];
                    $ids[] = $id;
                    
                    $is_valid = false;
                    if (isset($product['status']) && $product['status'] === 'ACTIVE' && 
                        isset($product['review_status']) && $product['review_status'] === 'APPROVED') {
                        $is_valid = true;
                        $valid_ids[] = $id;
                    }
                    
                    echo "- ID: $id\n";
                    echo "  Name: " . ($product['name'] ?? 'No name') . "\n";
                    echo "  Status: " . ($product['status'] ?? 'Unknown') . "\n";
                    echo "  Review: " . ($product['review_status'] ?? 'Unknown') . "\n";
                    echo "  Valid: " . ($is_valid ? '✅' : '❌') . "\n";
                    echo "  ---\n";
                }
                
                echo "\n📋 ALL IDs:\n" . json_encode($ids) . "\n";
                echo "\n✅ VALID IDs:\n" . json_encode($valid_ids) . "\n";
            }
        } else {
            echo "Raw Response:\n$response\n";
        }
        
        echo "</pre>";
    }
    
    private function get_valid_token() {
        $this->db->where('user_type', 3);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get('tts_tokens');
        
        if ($query->num_rows() == 0) {
            throw new Exception("No affiliate token found");
        }
        
        return $query->row()->access_token;
    }

    /**
     * Test direct curl multi link (mirip dengan script awal)
     */
    public function test_direct_curl() {
    echo "<h1>🔗 Test Direct cURL Multi Link</h1>";
    echo "<pre>";
    
    $campaign_id = "7626279878945982225";
    
    // 🔥 GANTI DENGAN PRODUCT ID YANG SUDAH APPROVED
    $product_ids = ["1729452955340867901", "1729514565807540541"]; // ← SUDAH STRING!
    
    echo "✅ Using APPROVED product IDs: " . json_encode($product_ids) . "\n\n";
    
    $access_token = $this->get_valid_token();
    $cipher = $this->jsm_api->default_cipher;
    $app_key = "6jo4rjnr8ouc9";
    $app_secret = "8ceeae7a9ba3726cb9f2e9d831020c91fad4d99c";
    $timestamp = time();
    
    $path = "/affiliate_partner/202505/campaigns/{$campaign_id}/products/promotion_links/generate_batch";
    
    $query_params = [
        'app_key' => $app_key,
        'timestamp' => $timestamp,
        'category_asset_cipher' => $cipher
    ];
    
    ksort($query_params);
    
    $param_string = '';
    foreach ($query_params as $key => $value) {
        $param_string .= $key . $value;
    }
    
    // 🔥 PERBAIKAN: HAPUS JSON_FORCE_OBJECT
    $body_data = ['product_ids' => $product_ids];
    $body_json = json_encode($body_data); // ← TANPA JSON_FORCE_OBJECT
    
    $string_to_sign = $app_secret . $path . $param_string . $body_json . $app_secret;
    $query_params['sign'] = hash_hmac('sha256', $string_to_sign, $app_secret);
    
    $url = "https://open-api.tiktokglobalshop.com" . $path . '?' . http_build_query($query_params);
    
    echo "URL: $url\n";
    echo "Body: $body_json\n";
    echo "Timestamp: $timestamp\n";
    echo "Signature: " . $query_params['sign'] . "\n\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body_json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "x-tts-access-token: {$access_token}",
        "Content-Type: application/json",
        "Accept: application/json"
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    echo "HTTP Code: $http_code\n";
    
    if ($curl_error) {
        echo "❌ cURL Error: $curl_error\n";
    }
    
    $decoded = json_decode($response, true);
    if ($decoded) {
        echo "\nResponse:\n";
        print_r($decoded);
        
        if (isset($decoded['code']) && $decoded['code'] == 0) {
            echo "\n✅ SUCCESS! Links generated:\n";
            echo str_repeat("=", 80) . "\n";
            foreach ($decoded['data']['product_promotion_links'] ?? [] as $link) {
                echo "Product ID: " . $link['product_id'] . "\n";
                echo "Link: " . $link['link'] . "\n";
                echo str_repeat("-", 80) . "\n";
            }
        } else {
            echo "\n❌ Error Code: " . ($decoded['code'] ?? 'Unknown') . "\n";
            echo "❌ Message: " . ($decoded['message'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "Raw Response:\n$response\n";
    }
    
    echo "</pre>";
}

public function debug_campaign_products_raw() {
    echo "<h1>🐛 Debug Campaign Products RAW</h1>";
    echo "<pre>";
    
    $campaign_id = "7626279878945982225";
    
    // Gunakan CURL manual untuk melihat response asli
    $access_token = $this->get_valid_token();
    $cipher = $this->jsm_api->default_cipher;
    $app_key = "6jo4rjnr8ouc9";
    $app_secret = "8ceeae7a9ba3726cb9f2e9d831020c91fad4d99c";
    $timestamp = time();
    
    $path = "/affiliate_partner/202405/campaigns/{$campaign_id}/products";
    
    $query_params = [
        'app_key' => $app_key,
        'timestamp' => $timestamp,
        'category_asset_cipher' => $cipher,
        'page_size' => 50
    ];
    
    ksort($query_params);
    
    $param_string = '';
    foreach ($query_params as $key => $value) {
        $param_string .= $key . $value;
    }
    
    $string_to_sign = $app_secret . $path . $param_string . $app_secret;
    $query_params['sign'] = hash_hmac('sha256', $string_to_sign, $app_secret);
    
    $url = "https://open-api.tiktokglobalshop.com" . $path . '?' . http_build_query($query_params);
    
    echo "=== CAMPAIGN PRODUCTS REQUEST ===\n";
    echo "URL: $url\n\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "x-tts-access-token: {$access_token}",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Code: $http_code\n\n";
    
    $decoded = json_decode($response, true);
    
    if ($decoded) {
        echo "=== RESPONSE STRUCTURE ===\n";
        echo "Code: " . ($decoded['code'] ?? 'N/A') . "\n";
        echo "Message: " . ($decoded['message'] ?? 'N/A') . "\n";
        
        if (isset($decoded['data']['products'])) {
            $products = $decoded['data']['products'];
            echo "\nTotal Products: " . count($products) . "\n";
            echo str_repeat("=", 100) . "\n";
            
            $all_ids = [];
            foreach ($products as $index => $product) {
                $product_id = $product['id'];
                $all_ids[] = (string)$product_id;
                
                echo sprintf("[%d] ID: %s\n", $index + 1, $product_id);
                echo "    Name: " . substr($product['name'] ?? 'N/A', 0, 50) . "...\n";
                echo "    Review Status: " . ($product['review_status'] ?? 'N/A') . "\n";
                echo "    Status: " . ($product['status'] ?? 'N/A') . "\n";
                echo "    is_available: " . ($product['is_available'] ? 'true' : 'false') . "\n";
                echo str_repeat("-", 100) . "\n";
            }
            
            echo "\n📋 ALL PRODUCT IDs (as strings):\n";
            echo json_encode($all_ids, JSON_PRETTY_PRINT) . "\n";
            
            // 🔥 Coba multi-link dengan product IDs dari response
            echo "\n" . str_repeat("=", 100) . "\n";
            echo "🚀 TESTING MULTI-LINK WITH PRODUCTS FROM API\n";
            
            // Ambil 2 product pertama
            $test_ids = array_slice($all_ids, 0, 2);
            echo "Product IDs: " . json_encode($test_ids) . "\n\n";
            
            $this->_test_multi_link($campaign_id, $test_ids);
            
        } else {
            echo "\n❌ No products found in response\n";
            echo "Full response:\n";
            print_r($decoded);
        }
    } else {
        echo "Raw Response:\n$response\n";
    }
    
    echo "</pre>";
}

public function verify_products_in_campaign() {
    echo "<h1>🔍 Verifikasi Product di Campaign</h1>";
    echo "<pre>";
    
    $campaign_id = "7626279878945982225";
    $product_ids_to_check = ["1729452955340867901", "1729514565807540541"];
    
    // Ambil semua product dari campaign
    $products_result = $this->jsm_api->get_campaign_products($campaign_id, ['page_size' => 100]);
    
    if (!$products_result['success'] || empty($products_result['data'])) {
        die("❌ No products found in campaign.\n");
    }
    
    echo "Campaign ID: $campaign_id\n";
    echo "Total products in campaign: " . count($products_result['data']) . "\n\n";
    
    // Cari product yang dimaksud
    $found_products = [];
    foreach ($products_result['data'] as $product) {
        if (in_array((string)$product['id'], $product_ids_to_check)) {
            $found_products[] = $product;
        }
    }
    
    if (empty($found_products)) {
        echo "❌ Product IDs NOT FOUND in campaign:\n";
        echo "   " . implode("\n   ", $product_ids_to_check) . "\n\n";
        
        echo "📋 Available APPROVED products in campaign:\n";
        foreach ($products_result['data'] as $product) {
            if ($product['review_status'] === 'APPROVED' && $product['is_available']) {
                echo "- ID: " . $product['id'] . " | Name: " . substr($product['name'], 0, 50) . "...\n";
            }
        }
        echo "</pre>";
        return;
    }
    
    echo "✅ Products found in campaign:\n";
    foreach ($found_products as $product) {
        echo "- ID: " . $product['id'] . "\n";
        echo "  Name: " . $product['name'] . "\n";
        echo "  Review Status: " . $product['review_status'] . "\n";
        echo "  Status: " . $product['status'] . "\n";
        echo "  is_available: " . ($product['is_available'] ? 'true' : 'false') . "\n";
        echo "  ---\n";
    }
    
    echo "</pre>";
}

public function check_token_scope_via_api() {
    echo "<h1>🔍 Cek Scope Token via API</h1>";
    echo "<pre>";
    
    // Ambil token dari database
    $this->db->where('user_type', 3);
    $this->db->order_by('id', 'DESC');
    $this->db->limit(1);
    $query = $this->db->get('tts_tokens');
    
    if ($query->num_rows() == 0) {
        die("❌ No affiliate token found.\n");
    }
    
    $token = $query->row_array();
    $access_token = $token['access_token'];
    
    echo "📋 Token Info:\n";
    echo "Access Token: " . substr($access_token, 0, 50) . "...\n";
    echo "Token Expire: " . date('Y-m-d H:i:s', $token['access_token_expire']) . "\n\n";
    
    // 🔥 CEK SCOPE VIA API GET AUTHORIZED CATEGORY ASSETS
    $app_key = "6jo4rjnr8ouc9";
    $app_secret = "8ceeae7a9ba3726cb9f2e9d831020c91fad4d99c";
    $timestamp = time();
    
    $path = "/authorization/202405/category_assets";
    
    $query_params = [
        'app_key' => $app_key,
        'timestamp' => $timestamp
    ];
    
    ksort($query_params);
    
    $param_string = '';
    foreach ($query_params as $key => $value) {
        $param_string .= $key . $value;
    }
    
    $string_to_sign = $app_secret . $path . $param_string . $app_secret;
    $query_params['sign'] = hash_hmac('sha256', $string_to_sign, $app_secret);
    
    $url = "https://open-api.tiktokglobalshop.com" . $path . '?' . http_build_query($query_params);
    
    echo "=== REQUEST TO GET SCOPE ===\n";
    echo "URL: $url\n\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "x-tts-access-token: {$access_token}",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Code: $http_code\n\n";
    
    $decoded = json_decode($response, true);
    
    if ($decoded) {
        echo "Response:\n";
        print_r($decoded);
        
        // Cek apakah ada error code
        if (isset($decoded['code'])) {
            if ($decoded['code'] == 0) {
                echo "\n✅ Token VALID! Scope yang dimiliki:\n";
                // Scope biasanya tercantum dalam response atau bisa dilihat dari akses yang diberikan
                if (isset($decoded['data']['category_assets'])) {
                    echo "Category Assets: " . json_encode($decoded['data']['category_assets']) . "\n";
                }
                
                // Cek apakah token bisa mengakses multi-link dengan mencoba endpoint
                echo "\n🔍 Mencoba akses multi-link untuk verifikasi scope...\n";
                $this->_test_multi_link_access($access_token);
                
            } else if ($decoded['code'] == 106011) {
                echo "\n❌ Error 106011: Invalid shop_cipher - Token mungkin tidak memiliki scope yang cukup\n";
                echo "   Ini menunjukkan token TIDAK memiliki scope 'partner.tap_campaign.read'\n";
            } else {
                echo "\n❌ Error Code: " . $decoded['code'] . "\n";
                echo "Message: " . ($decoded['message'] ?? 'Unknown') . "\n";
            }
        }
    } else {
        echo "Raw Response:\n$response\n";
    }
    
    echo "</pre>";
}

/**
 * Test akses multi-link untuk verifikasi scope
 */
private function _test_multi_link_access($access_token) {
    $campaign_id = "7626279878945982225";
    $product_ids = ["1729452955340867901"];
    
    $app_key = "6jo4rjnr8ouc9";
    $app_secret = "8ceeae7a9ba3726cb9f2e9d831020c91fad4d99c";
    $cipher = "ROW_fyGlKwAAAAB6jCmj_Z8Zc6uknZJUdZAi";
    $timestamp = time();
    
    $path = "/affiliate_partner/202505/campaigns/{$campaign_id}/products/promotion_links/generate_batch";
    
    $query_params = [
        'app_key' => $app_key,
        'timestamp' => $timestamp,
        'category_asset_cipher' => $cipher
    ];
    
    ksort($query_params);
    
    $param_string = '';
    foreach ($query_params as $key => $value) {
        $param_string .= $key . $value;
    }
    
    $body_data = ['product_ids' => $product_ids];
    $body_json = json_encode($body_data);
    
    $string_to_sign = $app_secret . $path . $param_string . $body_json . $app_secret;
    $query_params['sign'] = hash_hmac('sha256', $string_to_sign, $app_secret);
    
    $url = "https://open-api.tiktokglobalshop.com" . $path . '?' . http_build_query($query_params);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body_json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "x-tts-access-token: {$access_token}",
        "Content-Type: application/json",
        "Accept: application/json"
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $decoded = json_decode($response, true);
    
    echo "Multi-Link Test Result:\n";
    echo "HTTP Code: $http_code\n";
    if ($decoded) {
        echo "Code: " . ($decoded['code'] ?? 'N/A') . "\n";
        echo "Message: " . ($decoded['message'] ?? 'N/A') . "\n";
        
        if (isset($decoded['code']) && $decoded['code'] == 0) {
            echo "✅ Token memiliki akses ke multi-link!\n";
        } else if ($decoded['code'] == 16032001) {
            echo "❌ Token TIDAK memiliki akses ke multi-link (error 16032001)\n";
            echo "   Ini berarti token tidak memiliki scope 'partner.tap_campaign.read'\n";
        }
    }
}
public function generate_multi_links_fixed() {
    echo "<h1>✅ Generate Multi Links (FIXED - String Format)</h1>";
    echo "<pre>";
    
    $campaign_id = "7626279878945982225";
    
    // 🔥 KRUSIAL: Kirim sebagai STRING, BUKAN INTEGER!
    $product_ids = ["1729452955340867901", "1729514565807540541"];
    
    echo "Campaign ID: $campaign_id\n";
    echo "Product IDs (as string): " . json_encode($product_ids) . "\n\n";
    
    // Ambil token
    $this->db->where('user_type', 3);
    $this->db->order_by('id', 'DESC');
    $this->db->limit(1);
    $query = $this->db->get('tts_tokens');
    
    if ($query->num_rows() == 0) {
        die("❌ No affiliate token found.\n");
    }
    
    $token = $query->row_array();
    $access_token = $token['access_token'];
    
    $app_key = "6jo4rjnr8ouc9";
    $app_secret = "8ceeae7a9ba3726cb9f2e9d831020c91fad4d99c";
    $cipher = "ROW_fyGlKwAAAAB6jCmj_Z8Zc6uknZJUdZAi";
    $timestamp = time();
    
    $path = "/affiliate_partner/202505/campaigns/{$campaign_id}/products/promotion_links/generate_batch";
    
    $query_params = [
        'app_key' => $app_key,
        'timestamp' => $timestamp,
        'category_asset_cipher' => $cipher
    ];
    
    ksort($query_params);
    
    $param_string = '';
    foreach ($query_params as $key => $value) {
        $param_string .= $key . $value;
    }
    
    // 🔥 PASTIKAN BODY MENGGUNAKAN STRING
    $body_data = ['product_ids' => $product_ids];
    $body_json = json_encode($body_data);
    
    $string_to_sign = $app_secret . $path . $param_string . $body_json . $app_secret;
    $query_params['sign'] = hash_hmac('sha256', $string_to_sign, $app_secret);
    
    $url = "https://open-api.tiktokglobalshop.com" . $path . '?' . http_build_query($query_params);
    
    echo "=== REQUEST ===\n";
    echo "Body: $body_json\n";
    echo "Signature: " . $query_params['sign'] . "\n\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body_json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "x-tts-access-token: {$access_token}",
        "Content-Type: application/json",
        "Accept: application/json"
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Code: $http_code\n";
    
    $decoded = json_decode($response, true);
    if ($decoded) {
        echo "\nResponse:\n";
        print_r($decoded);
        
        if (isset($decoded['code']) && $decoded['code'] == 0) {
            echo "\n✅ SUCCESS! Multi-link berhasil!\n";
            foreach ($decoded['data']['product_promotion_links'] ?? [] as $link) {
                echo "Product ID: " . $link['product_id'] . "\n";
                echo "Link: " . $link['link'] . "\n\n";
            }
        } else {
            echo "\n❌ Error Code: " . ($decoded['code'] ?? 'Unknown') . "\n";
            echo "❌ Message: " . ($decoded['message'] ?? 'Unknown error') . "\n";
        }
    }
    
    echo "</pre>";
}

public function debug_product_ids_comparison() {
    echo "<h1>🐛 Debug: Bandingkan Product IDs</h1>";
    echo "<pre>";
    
    $campaign_id = "7626279878945982225";
    
    // Product IDs yang Anda gunakan sebelumnya
    $manual_ids = ["1729452955340867901", "1729514565807540541"];
    
    // Product IDs dari campaign
    $products_data = $this->get_product_ids_from_campaign($campaign_id, 50);
    $campaign_ids = $products_data['product_ids'] ?? [];
    
    echo "📋 MANUAL PRODUCT IDs:\n";
    echo json_encode($manual_ids, JSON_PRETTY_PRINT) . "\n\n";
    
    echo "📋 CAMPAIGN PRODUCT IDs (first 10):\n";
    echo json_encode(array_slice($campaign_ids, 0, 10), JSON_PRETTY_PRINT) . "\n\n";
    
    // Cek apakah manual_ids ada di campaign_ids
    echo "🔍 VERIFIKASI:\n";
    foreach ($manual_ids as $mid) {
        $found = in_array($mid, $campaign_ids);
        echo "Product ID $mid: " . ($found ? '✅ FOUND in campaign' : '❌ NOT FOUND in campaign') . "\n";
    }
    
    // Jika ditemukan, coba generate
    $found_ids = array_intersect($manual_ids, $campaign_ids);
    if (!empty($found_ids)) {
        echo "\n🚀 Testing with found IDs: " . json_encode(array_values($found_ids)) . "\n";
        $result = $this->jsm_api->generate_multi_affiliate_links($campaign_id, array_values($found_ids));
        echo "\nResult:\n";
        print_r($result);
    }
    
    echo "</pre>";
}
public function test_multi_raw() {
    echo "<h1>🔗 Test Multi-Link - RAW RESPONSE</h1>";
    echo "<pre>";
    
    $campaign_id = "7626279878945982225";
    $product_ids = ["1729452955340867901", "1729514565807540541"];
    
    echo "Campaign ID: $campaign_id\n";
    echo "Product IDs: " . json_encode($product_ids) . "\n\n";
    echo str_repeat("=", 100) . "\n\n";
    
    // Panggil method raw
    $result = $this->jsm_api->generate_multi_affiliate_links_raw($campaign_id, $product_ids);
    
    // === TAMPILKAN REQUEST ===
    echo "📤 REQUEST:\n";
    echo str_repeat("-", 80) . "\n";
    echo "URL: " . $result['request']['url'] . "\n";
    echo "Method: " . $result['request']['method'] . "\n";
    echo "Body: " . $result['request']['body'] . "\n";
    echo "Timestamp: " . $result['request']['timestamp'] . "\n";
    echo "Signature: " . $result['request']['signature'] . "\n";
    echo "String to sign: " . $result['request']['string_to_sign'] . "\n";
    echo "Product IDs sent: " . json_encode($result['request']['product_ids_sent']) . "\n";
    echo "Category Asset Cipher: " . $result['request']['category_asset_cipher'] . "\n";
    
    echo "\n📥 RESPONSE:\n";
    echo str_repeat("-", 80) . "\n";
    echo "HTTP Code: " . $result['response']['http_code'] . "\n";
    echo "Content Type: " . $result['response']['content_type'] . "\n";
    echo "Total Time: " . $result['response']['total_time'] . "s\n";
    
    if (!empty($result['response']['curl_error'])) {
        echo "cURL Error: " . $result['response']['curl_error'] . "\n";
    }
    
    echo "\n📄 RAW BODY:\n";
    echo str_repeat("-", 80) . "\n";
    echo $result['response']['body_raw'] . "\n";
    
    echo "\n📊 DECODED BODY:\n";
    echo str_repeat("-", 80) . "\n";
    print_r($result['response']['body_decoded']);
    
    echo "\n📋 VERBOSE LOG:\n";
    echo str_repeat("-", 80) . "\n";
    echo $result['verbose_log'];
    
    echo "\n" . str_repeat("=", 100) . "\n";
    echo "Status: " . ($result['success'] ? '✅ SUCCESS' : '❌ FAILED') . "\n";
    
    echo "</pre>";
}


}