<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test_link extends CI_Controller {

    public function __construct() {
        parent::__construct();
        
        $this->load->library('Jsm_api', ['api_type' => 'TOOPAI']);
        $this->load->model('Jsm_token_model');
    }

    /**
     * ============================================================
     * RAW API RESPONSE - Menampilkan response API mentah tanpa olahan
     * 
     * URL: /test_link/raw?type=open_collab&product_id=1732477696021005670
     * URL: /test_link/raw?type=seller_products&shop_name=MOSSDOOM
     * URL: /test_link/raw?type=global_product&product_id=1732477696021005670
     * URL: /test_link/raw?type=search_products&keyword=MOSSDOOM
     * URL: /test_link/raw?type=search_products&keyword=1732477696021005670
     * ============================================================
     */
    public function raw() {
        $this->output->set_content_type('application/json');
        
        $type = $this->input->get('type');
        
        if (empty($type)) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'error' => 'Parameter "type" is required',
                'available_types' => [
                    'open_collab' => ['product_id'],
                    'seller_products' => ['shop_name'],
                    'global_product' => ['product_id'],
                    'search_products' => ['keyword']
                ],
                'examples' => [
                    '/test_link/raw?type=open_collab&product_id=1732477696021005670',
                    '/test_link/raw?type=seller_products&shop_name=MOSSDOOM',
                    '/test_link/raw?type=global_product&product_id=1732477696021005670',
                    '/test_link/raw?type=search_products&keyword=MOSSDOOM'
                ]
            ], JSON_PRETTY_PRINT));
        }
        
        $result = null;
        $params_used = [];
        
        switch ($type) {
            case 'open_collab':
                $product_id = $this->input->get('product_id');
                if (empty($product_id)) {
                    return $this->output->set_output(json_encode([
                        'success' => false,
                        'error' => 'Parameter "product_id" is required for type=open_collab'
                    ], JSON_PRETTY_PRINT));
                }
                $params_used = ['product_id' => $product_id];
                $result = $this->jsm_api->search_open_collaboration_by_product_id($product_id);
                break;
                
            case 'seller_products':
                $shop_name = $this->input->get('shop_name');
                if (empty($shop_name)) {
                    return $this->output->set_output(json_encode([
                        'success' => false,
                        'error' => 'Parameter "shop_name" is required for type=seller_products'
                    ], JSON_PRETTY_PRINT));
                }
                $params_used = ['shop_name' => $shop_name];
                $result = $this->jsm_api->search_seller_open_products($shop_name, 50);
                break;
                
            case 'global_product':
                $product_id = $this->input->get('product_id');
                if (empty($product_id)) {
                    return $this->output->set_output(json_encode([
                        'success' => false,
                        'error' => 'Parameter "product_id" is required for type=global_product'
                    ], JSON_PRETTY_PRINT));
                }
                $params_used = ['product_id' => $product_id];
                $result = $this->jsm_api->get_global_product_detail($product_id);
                break;
                
            case 'search_products':
                $keyword = $this->input->get('keyword');
                if (empty($keyword)) {
                    return $this->output->set_output(json_encode([
                        'success' => false,
                        'error' => 'Parameter "keyword" is required for type=search_products'
                    ], JSON_PRETTY_PRINT));
                }
                $page_size = $this->input->get('page_size');
                if (empty($page_size)) {
                    $page_size = 20;
                }
                $params_used = ['keyword' => $keyword, 'page_size' => $page_size];
                $result = $this->jsm_api->search_products([
                    'keyword' => $keyword,
                    'page_size' => $page_size
                ]);
                break;
                
            default:
                return $this->output->set_output(json_encode([
                    'success' => false,
                    'error' => 'Invalid type: ' . $type,
                    'available_types' => ['open_collab', 'seller_products', 'global_product', 'search_products']
                ], JSON_PRETTY_PRINT));
        }
        
        // TAMPILKAN RAW RESPONSE API TANPA OLAHAN APAPUN
        return $this->output->set_output(json_encode([
            'success' => true,
            'type' => $type,
            'params' => $params_used,
            'raw_response' => $result
        ], JSON_PRETTY_PRINT));
    }

    /**
     * ============================================================
     * EXTRACT FROM URL - Ambil product_id dari shortlink
     * URL: /test_link/extract?url=https://vt.tokopedia.com/t/xxx
     * ============================================================
     */
    public function extract() {
        $this->output->set_content_type('application/json');
        
        $url = $this->input->get('url');
        if (empty($url)) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'error' => 'URL required'
            ], JSON_PRETTY_PRINT));
        }
        
        // Follow redirect
        $final_url = $this->_follow_redirect($url);
        
        // Extract product_id
        $product_id = $this->_extract_product_id($final_url);
        
        // Extract og_info dari URL
        $title = null;
        $image_url = null;
        if (preg_match('/og_info=([^&]+)/', $final_url, $matches)) {
            $og_info_encoded = $matches[1];
            $og_info_json = urldecode($og_info_encoded);
            $og_info = json_decode($og_info_json, true);
            if ($og_info) {
                $title = isset($og_info['title']) ? urldecode($og_info['title']) : null;
                $image_url = isset($og_info['image']) ? urldecode($og_info['image']) : null;
            }
        }
        
        // Extract unique_id
        $unique_id = null;
        if (preg_match('/unique_id=([^&]+)/', $final_url, $matches)) {
            $unique_id = urldecode($matches[1]);
        }
        
        // Extract user_id
        $user_id = null;
        if (preg_match('/user_id=(\d+)/', $final_url, $matches)) {
            $user_id = $matches[1];
        }
        
        // Extract timestamp
        $timestamp = null;
        if (preg_match('/timestamp=(\d+)/', $final_url, $matches)) {
            $timestamp = $matches[1];
        }
        
        return $this->output->set_output(json_encode([
            'success' => !empty($product_id),
            'original_url' => $url,
            'final_url' => $final_url,
            'extracted' => [
                'product_id' => $product_id,
                'title' => $title,
                'image_url' => $image_url,
                'unique_id' => $unique_id,
                'user_id' => $user_id,
                'timestamp' => $timestamp,
                'share_date' => $timestamp ? date('Y-m-d H:i:s', $timestamp) : null
            ]
        ], JSON_PRETTY_PRINT));
    }

    /**
     * ============================================================
     * FOLLOW REDIRECT ONLY - Lihat URL tujuan
     * URL: /test_link/follow?url=https://vt.tokopedia.com/t/xxx
     * ============================================================
     */
    public function follow() {
        $this->output->set_content_type('application/json');
        
        $url = $this->input->get('url');
        if (empty($url)) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'error' => 'URL required'
            ], JSON_PRETTY_PRINT));
        }
        
        $final_url = $this->_follow_redirect($url);
        
        return $this->output->set_output(json_encode([
            'success' => !empty($final_url),
            'original_url' => $url,
            'final_url' => $final_url
        ], JSON_PRETTY_PRINT));
    }

    /**
     * ============================================================
     * PRIVATE METHODS
     * ============================================================
     */

    /**
     * Follow redirect and get final URL
     */
    private function _follow_redirect($url) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            CURLOPT_HEADER => false,
            CURLOPT_NOBODY => true
        ]);
        
        curl_exec($ch);
        $final_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code == 405 || !$final_url) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]);
            curl_exec($ch);
            $final_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            curl_close($ch);
        }
        
        return $final_url;
    }

    /**
     * Extract product ID from URL with multiple patterns
     */
    private function _extract_product_id($url) {
        $patterns = [
            '/product\/(\d+)/',
            '/(\d{15,})/',
            '/[?&]p=(\d+)/',
            '/[?&]id=(\d+)/',
            '/[?&]product_id=(\d+)/',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
    
    /**
 * ============================================================
 * TEST ALL APIs - Menjalankan semua endpoint API sekaligus
 * URL: /test_link/test_all?product_id=1732477696021005670&shop_name=MOSSDOOM
 * ============================================================
 */
public function test_all() {
    $this->output->set_content_type('application/json');
    
    $product_id = $this->input->get('product_id');
    $shop_name = $this->input->get('shop_name');
    $keyword = $this->input->get('keyword');
    
    // Jika tidak ada parameter, gunakan default dari URL sebelumnya
    if (empty($product_id)) {
        $product_id = '1732477696021005670';
    }
    if (empty($shop_name)) {
        $shop_name = 'MOSSDOOM';
    }
    if (empty($keyword)) {
        $keyword = 'MOSSDOOM';
    }
    
    $results = [];
    
    // ============================================================
    // 1. Open Collaboration API (by product_id)
    // ============================================================
    $results['1_open_collab_by_product_id'] = [
        'endpoint' => 'search_open_collaboration_by_product_id',
        'params' => ['product_id' => $product_id],
        'response' => $this->jsm_api->search_open_collaboration_by_product_id($product_id)
    ];
    
    // ============================================================
    // 2. Search Products API (by product_id as keyword)
    // ============================================================
    $results['2_search_products_by_product_id'] = [
        'endpoint' => 'search_products',
        'params' => ['keyword' => $product_id, 'page_size' => 20],
        'response' => $this->jsm_api->search_products([
            'keyword' => $product_id,
            'page_size' => 20
        ])
    ];
    
    // ============================================================
    // 3. Search Products API (by shop_name as keyword)
    // ============================================================
    $results['3_search_products_by_shop_name'] = [
        'endpoint' => 'search_products',
        'params' => ['keyword' => $shop_name, 'page_size' => 20],
        'response' => $this->jsm_api->search_products([
            'keyword' => $shop_name,
            'page_size' => 20
        ])
    ];
    
    // ============================================================
    // 4. Seller Open Products API (by shop_name MOSSDOOM)
    // ============================================================
    $results['4_seller_products_by_shop_name'] = [
        'endpoint' => 'search_seller_open_products',
        'params' => ['shop_name' => $shop_name, 'page_size' => 50],
        'response' => $this->jsm_api->search_seller_open_products($shop_name, 50)
    ];
    
    // ============================================================
    // 5. Seller Open Products API (by unique_id jennifermuliyanto)
    // ============================================================
    $unique_id = 'jennifermuliyanto';
    $results['5_seller_products_by_unique_id'] = [
        'endpoint' => 'search_seller_open_products',
        'params' => ['shop_name' => $unique_id, 'page_size' => 50],
        'response' => $this->jsm_api->search_seller_open_products($unique_id, 50)
    ];
    
    // ============================================================
    // 6. Global Product API
    // ============================================================
    $results['6_global_product_by_id'] = [
        'endpoint' => 'get_global_product_detail',
        'params' => ['product_id' => $product_id],
        'response' => $this->jsm_api->get_global_product_detail($product_id)
    ];
    
    // ============================================================
    // SUMMARY
    // ============================================================
    $summary = [];
    foreach ($results as $key => $value) {
        $success = $value['response']['success'] ?? false;
        $has_data = false;
        
        if ($success) {
            if ($key == '1_open_collab_by_product_id') {
                $has_data = !empty($value['response']['data']['open_collaborations']);
            } elseif ($key == '2_search_products_by_product_id' || $key == '3_search_products_by_shop_name') {
                $has_data = !empty($value['response']['data']);
            } elseif ($key == '4_seller_products_by_shop_name' || $key == '5_seller_products_by_unique_id') {
                $has_data = !empty($value['response']['data']['products']);
            } elseif ($key == '6_global_product_by_id') {
                $has_data = !empty($value['response']['data']);
            }
        }
        
        $summary[$key] = [
            'success' => $success,
            'has_data' => $has_data,
            'message' => $value['response']['message'] ?? null
        ];
    }
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'params_used' => [
            'product_id' => $product_id,
            'shop_name' => $shop_name,
            'unique_id' => $unique_id,
            'keyword' => $keyword
        ],
        'summary' => $summary,
        'detailed_responses' => $results
    ], JSON_PRETTY_PRINT));
}

/**
 * ============================================================
 * TEST PRODUCT PERFORMANCE
 * URL: /test_link/performance?product_id=1732477696021005670
 * ============================================================
 */
public function performance() {
    $this->output->set_content_type('application/json');
    
    $product_id = $this->input->get('product_id');
    if (empty($product_id)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'error' => 'Parameter "product_id" is required'
        ], JSON_PRETTY_PRINT));
    }
    
    // 🔥 AMBIL latest_available_date terlebih dahulu
    $test_params = [
        'start_date_ge' => date('Y-m-d', strtotime('-1 day')),
        'end_date_lt' => date('Y-m-d'),
        'granularity' => 'ALL',
        'currency' => 'LOCAL'
    ];
    
    $test_result = $this->jsm_api->get_product_performance_2($product_id, $test_params);
    
    $latest_date = $test_result['data']['latest_available_date'] ?? date('Y-m-d');
    
    // 🔥 BATASI RENTANG MAKSIMUM 90 HARI
    $days_back = (int)$this->input->get('days') ?: 90;
    if ($days_back > 90) {
        $days_back = 90;
    }
    
    // 🔥 SEMUA PARAMETER WAJIB
    $params = [
        'start_date_ge' => date('Y-m-d', strtotime($latest_date . " -{$days_back} days")),
        'end_date_lt' => $latest_date,
        'granularity' => 'ALL',
        'currency' => $this->input->get('currency') ?: 'LOCAL'
    ];
    
    $result = $this->jsm_api->get_product_performance_2($product_id, $params);
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'params_used' => $params,
        'product_id' => $product_id,
        'latest_available_date' => $latest_date,
        'days_back' => $days_back,
        'response' => $result
    ], JSON_PRETTY_PRINT));
}

/**
 * ============================================================
 * GET CREATORS BY PRODUCT ID (Open Collaboration)
 * URL: /test_link/creators?product_id=1734366911314297960
 * ============================================================
 */
public function creators() {
    $this->output->set_content_type('application/json');
    
    $product_id = $this->input->get('product_id');
    if (empty($product_id)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'error' => 'Parameter "product_id" is required',
            'example' => '/test_link/creators?product_id=1734366911314297960'
        ], JSON_PRETTY_PRINT));
    }
    
    $result = $this->jsm_api->search_open_collab_creators($product_id);
    
    return $this->output->set_output(json_encode([
        'success' => $result['success'],
        'product_id' => $product_id,
        'total_creators' => $result['data']['total_creators'] ?? 0,
        'creators' => $result['data']['creators'] ?? [],
        'raw_response' => $result
    ], JSON_PRETTY_PRINT));
}

/**
 * ============================================================
 * GET CREATORS SUMMARY - Ringkasan cepat
 * URL: /test_link/creators_summary?product_id=1734366911314297960
 * ============================================================
 */
public function creators_summary() {
    $this->output->set_content_type('application/json');
    
    $product_id = $this->input->get('product_id');
    if (empty($product_id)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'error' => 'Parameter "product_id" is required'
        ], JSON_PRETTY_PRINT));
    }
    
    $result = $this->jsm_api->search_open_collab_creators($product_id);
    
    $summary = [];
    foreach ($result['data']['creators'] ?? [] as $creator) {
        $summary[] = [
            'username' => $creator['creator_username'],
            'nickname' => $creator['creator_nickname'],
            'gmv' => $creator['gmv']['formatted'],
            'items_sold' => $creator['items_sold'],
            'follower_count' => number_format($creator['follower_count'])
        ];
    }
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'product_id' => $product_id,
        'total_creators' => count($summary),
        'creators' => $summary
    ], JSON_PRETTY_PRINT));
}


}