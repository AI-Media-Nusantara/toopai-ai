<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jsm_api
{
    // ========== PUBLIC PROPERTIES ==========
    public $app_key;
    public $app_secret;
    public $service_id;
    public $default_cipher;
    
    // ========== PRIVATE PROPERTIES ==========
    private $openapi_base = 'https://open-api.tiktokglobalshop.com';
    private $auth_base = 'https://auth.tiktok-shops.com';
    private $partner_auth = 'https://partner.tiktokshop.com/open/authorize';
    private $CI;
    private $api_type;
    
    // ========== KONFIGURASI ==========
    private $api_configs = [
        'TOOPAI' => [
            'app_key' => '6jo4rjnr8ouc9',
            'app_secret' => '8ceeae7a9ba3726cb9f2e9d831020c91fad4d99c',
            'service_id' => '7630671107655157524',
            'cipher' => 'ROW_fyGlKwAAAAB6jCmj_Z8Zc6uknZJUdZAi'
        ]
    ];
    
    // ========== CONSTRUCTOR ==========
    public function __construct($config = [])
    {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->model('Jsm_token_model');
        
        $this->api_type = $config['api_type'] ?? 'TOOPAI';
        
        if (isset($this->api_configs[$this->api_type])) {
            $api_config = $this->api_configs[$this->api_type];
            $this->app_key = $api_config['app_key'];
            $this->app_secret = $api_config['app_secret'];
            $this->service_id = $api_config['service_id'];
            $this->default_cipher = $api_config['cipher'];
        }
    }
    
    // ========== AUTHENTICATION METHODS ==========
    
    /**
     * Get authorize URL untuk Affiliate Partner
     */
    public function get_authorize_url($redirect_uri, $state)
    {
        $scope = urlencode('affiliate.product.search,affiliate.order.list,affiliate.campaign.read,affiliate.creator.read,seller.product.basic');
        
        $url = "{$this->partner_auth}?"
             . "service_id={$this->service_id}"
             . "&state={$state}"
             . "&redirect_uri=" . urlencode($redirect_uri)
             . "&scope={$scope}";
        
        return $url;
    }
    
    public function get_seller_authorize_url($redirect_uri, $state)
{
    $scope = urlencode('shop.read,product.read,order.read,analytics.read,product.basic');
    
    $url = "{$this->partner_auth}?"
         . "service_id={$this->service_id}"
         . "&state={$state}"
         . "&redirect_uri=" . urlencode($redirect_uri)
         . "&scope={$scope}";
    
    return $url;
}
    
    /**
     * Handle OAuth callback - exchange code for token
     */
    public function handle_callback($code, $state, $redirect_uri)
    {
        if (!$code || $code == 'null') {
            throw new Exception("Authorization code is missing");
        }
        
        $token_url = $this->auth_base . "/api/v2/token/get?" . http_build_query([
            "app_key" => $this->app_key,
            "app_secret" => $this->app_secret,
            "auth_code" => $code,
            "grant_type" => "authorized_code",
            "redirect_uri" => $redirect_uri
        ]);
        
        log_message('info', "Token URL: " . preg_replace('/app_secret=[^&]+/', 'app_secret=HIDDEN', $token_url));
        
        $response = $this->_curl_get($token_url);
        $token = json_decode($response, true);
        
        if (!isset($token['code']) || $token['code'] != 0) {
            log_message('error', "Get token failed: " . $response);
            throw new Exception("Failed to get token: " . ($token['message'] ?? $response));
        }
        
        return $token['data'];
    }
    
    // ========== TOKEN MANAGEMENT ==========
    
    /**
     * Get valid affiliate token (user_type = 2 atau 3)
     */
    public function get_valid_token()
    {
        $token = $this->CI->Jsm_token_model->get_latest_affiliate_token();
        
        if (!$token) {
            throw new Exception("No affiliate token found. Please authorize first.");
        }
        
        if ($token->access_token_expire <= time() + 300) {
            return $this->_refresh_token($token);
        }
        
        return $token->access_token;
    }
    
    /**
     * Get valid seller token (user_type = 2)
     */
    public function get_valid_seller_token()
    {
        $token = $this->CI->Jsm_token_model->get_latest_token_by_type(2);
        
        if (!$token) {
            throw new Exception("No seller token found. Please authorize as seller first.");
        }
        
        if ($token->access_token_expire <= time() + 300) {
            return $this->_refresh_seller_token($token);
        }
        
        return $token->access_token;
    }
    
    /**
     * Refresh affiliate token
     */
    private function _refresh_token($token)
    {
        if ($token->refresh_token_expire < time()) {
            throw new Exception("Refresh token expired. Please re-authorize.");
        }
        
        $url = $this->auth_base . "/api/v2/token/refresh?" . http_build_query([
            "app_key" => $this->app_key,
            "app_secret" => $this->app_secret,
            "refresh_token" => $token->refresh_token,
            "grant_type" => "refresh_token"
        ]);
        
        $response = $this->_curl_get($url);
        $result = json_decode($response, true);
        
        if (!isset($result['code']) || $result['code'] != 0) {
            throw new Exception("Failed to refresh token: " . ($result['message'] ?? 'Unknown error'));
        }
        
        $this->_save_token($result['data'], $token->user_type);
        
        return $result['data']['access_token'];
    }
    
    /**
     * Refresh seller token
     */
    private function _refresh_seller_token($token)
    {
        if ($token->refresh_token_expire < time()) {
            throw new Exception("Seller refresh token expired. Please re-authorize.");
        }
        
        $url = $this->auth_base . "/api/v2/token/refresh?" . http_build_query([
            "app_key" => $this->app_key,
            "app_secret" => $this->app_secret,
            "refresh_token" => $token->refresh_token,
            "grant_type" => "refresh_token"
        ]);
        
        $response = $this->_curl_get($url);
        $result = json_decode($response, true);
        
        if (!isset($result['code']) || $result['code'] != 0) {
            throw new Exception("Failed to refresh seller token: " . ($result['message'] ?? 'Unknown error'));
        }
        
        $this->_save_token($result['data'], 2);
        
        return $result['data']['access_token'];
    }
    
    /**
     * Save token to database
     */
    private function _save_token($data, $user_type = 3)
    {
        $shop_cipher = $data['shop_cipher'] ?? $data['seller_id'] ?? $data['open_id'] ?? $data['shop_id'] ?? 'AFFILIATE_' . time();
        
        $token_data = [
            'shop_id' => $shop_cipher,
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'access_token_expire' => time() + ($data['access_token_expire_in'] ?? 7200),
            'refresh_token_expire' => time() + ($data['refresh_token_expire_in'] ?? 2592000),
            'user_type' => $user_type,
            'scope' => $data['scope'] ?? '',
            'tap_type' => $this->api_type,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $existing = $this->CI->db->where('user_type', $user_type)
                               ->where('tap_type', $this->api_type)
                               ->get('tts_tokens')
                               ->row();
        
        if ($existing) {
            $this->CI->db->where('id', $existing->id)->update('tts_tokens', $token_data);
        } else {
            $token_data['created_at'] = date('Y-m-d H:i:s');
            $this->CI->db->insert('tts_tokens', $token_data);
        }
        
        log_message('info', "Token saved for user_type={$user_type}");
    }
    
    // ========== CAMPAIGN MANAGEMENT ==========
    
    /**
     * Get ongoing campaigns
     */
    public function get_ongoing_campaigns($filters = [])
    {
        $path = "/affiliate_partner/202405/campaigns";
        
        $params = array_merge([
            'category_asset_cipher' => $this->default_cipher,
            'page_size' => 50,
            'status' => 'ONGOING'
        ], $filters);
        
        $result = $this->_api_request($path, $params);
        
        if (!$result['success']) {
            return $result;
        }
        
        $campaigns = $result['data']['campaigns'] ?? [];
        $ongoing_campaigns = array_filter($campaigns, function($camp) {
            return isset($camp['status']) && $camp['status'] == 'ONGOING';
        });
        
        return [
            'success' => true,
            'data' => array_values($ongoing_campaigns),
            'total' => count($ongoing_campaigns),
            'next_page_token' => $result['data']['next_page_token'] ?? null
        ];
    }
    
    /**
     * Get all campaigns (with pagination)
     */
    public function get_all_campaigns($filters = [])
    {
        $path = "/affiliate_partner/202405/campaigns";
        
        $params = array_merge([
            'category_asset_cipher' => $this->default_cipher,
            'page_size' => 50
        ], $filters);
        
        $result = $this->_api_request($path, $params);
        
        if (!$result['success']) {
            return $result;
        }
        
        return [
            'success' => true,
            'data' => $result['data']['campaigns'] ?? [],
            'total' => $result['data']['total_count'] ?? 0,
            'next_page_token' => $result['data']['next_page_token'] ?? null
        ];
    }
    
    /**
     * Get campaign by ID
     */
    public function get_campaign_by_id($campaign_id)
    {
        $path = "/affiliate_partner/202405/campaigns/{$campaign_id}";
        $params = ['category_asset_cipher' => $this->default_cipher];
        $result = $this->_api_request($path, $params);
        
        if (!$result['success']) {
            return $result;
        }
        
        return [
            'success' => true,
            'data' => $result['data']['campaign'] ?? null
        ];
    }
    
    /**
     * Get campaign detail (alias)
     */
    public function get_campaign_detail($campaign_id)
    {
        return $this->get_campaign_by_id($campaign_id);
    }
    
    /**
     * Get campaign orders summary
     */
    public function get_campaign_orders_summary($campaign_id, $start_time = null, $end_time = null)
    {
        if (!$start_time) $start_time = strtotime('-30 days');
        if (!$end_time) $end_time = time();
        
        $filters = [
            'campaign_id' => $campaign_id,
            'create_time_ge' => $start_time,
            'create_time_lt' => $end_time,
            'page_size' => 100
        ];
        
        $orders_result = $this->search_affiliate_orders($filters);
        $orders = $orders_result['success'] ? $orders_result['data'] : [];
        
        $total_gmv = 0;
        $total_orders = 0;
        $total_items_sold = 0;
        $total_est_partner_comm = 0;
        $total_act_partner_comm = 0;
        $total_est_creator_comm = 0;
        $total_act_creator_comm = 0;
        
        foreach ($orders as $order) {
            $total_gmv += $order['affiliate_gmv'];
            $total_orders++;
            $total_items_sold += $order['items_sold'];
            $total_est_partner_comm += $order['estimated_affiliate_commission'];
            $total_act_partner_comm += $order['actual_affiliate_commission'];
            $total_est_creator_comm += $order['estimated_creator_commission'];
            $total_act_creator_comm += $order['actual_creator_commission'];
        }
        
        return [
            'total_gmv' => $total_gmv,
            'total_orders' => $total_orders,
            'total_items_sold' => $total_items_sold,
            'total_est_partner_commission' => $total_est_partner_comm,
            'total_act_partner_commission' => $total_act_partner_comm,
            'total_est_creator_commission' => $total_est_creator_comm,
            'total_act_creator_commission' => $total_act_creator_comm,
            'roas' => $total_est_partner_comm > 0 ? round($total_gmv / $total_est_partner_comm, 2) : 0
        ];
    }
    
    // ========== PRODUCT MANAGEMENT ==========
    
    /**
     * Get campaign products
     */
   public function get_campaign_products($campaign_id, $filters = [], $retry_count = 0) {
    $max_retries = 2;
    
    try {
        $all_products = [];
        $page_token = null;
        $page = 1;
        
        do {
            $params = array_merge([
                'page_size' => 100,
            ], $filters);
            
            if ($page_token) {
                $params['page_token'] = $page_token;
            }
            
            $path = "/affiliate_partner/202405/campaigns/{$campaign_id}/products";
            $result = $this->_api_request($path, $params);
            
            if (!$result['success']) {
                if ($retry_count < $max_retries) {
                    sleep(2);
                    return $this->get_campaign_products($campaign_id, $filters, $retry_count + 1);
                }
                return $result;
            }
            
            if (!isset($result['data']['products']) || !is_array($result['data']['products'])) {
                break;
            }
            
            foreach ($result['data']['products'] as $prod) {
        if (empty($prod) || empty($prod['id'])) {
            continue;
        }
        
        // 🔥 AMBIL LOWEST_PRICE DENGAN KONVERSI AMOUNT KE FLOAT
        $lowest_price = null;
        if (isset($prod['lowest_price']) && is_array($prod['lowest_price'])) {
            $lowest_price = [
                'amount' => floatval($prod['lowest_price']['amount'] ?? 0),
                'currency' => $prod['lowest_price']['currency'] ?? 'IDR'
            ];
        }
        
        $highest_price = null;
        if (isset($prod['highest_price']) && is_array($prod['highest_price'])) {
            $highest_price = [
                'amount' => floatval($prod['highest_price']['amount'] ?? 0),
                'currency' => $prod['highest_price']['currency'] ?? 'IDR'
            ];
        }
        
        $formatted = [
            'id' => $prod['id'],
            'name' => $prod['name'] ?? '',
            'price' => 0,
            'lowest_price' => $lowest_price,
            'highest_price' => $highest_price,
            'commission_rate' => $prod['commission_rate'] ?? 0,
            'partner_commission_rate' => $prod['partner_commission_rate'] ?? 0,
            'creator_commission_rate' => $prod['creator_commission_rate'] ?? 0,
            'open_collaboration_commission_rate' => $prod['open_collaboration_commission_rate'] ?? 0,
            'total_commission_rate' => $prod['total_commission_rate'] ?? 0,
                // 🔥 TAMBAHKAN INI!
    'shop_ads_commission_rate' => $prod['shop_ads_commission_rate'] ?? 0,
    'partner_shop_ads_commission_rate' => $prod['partner_shop_ads_commission_rate'] ?? 0,
            'product_sales' => $prod['product_sales'] ?? 0,
            'sales_count' => $prod['sales_count'] ?? 0,
            'gmv' => $prod['gmv'] ?? 0,
            'main_image_url' => $prod['main_image_url'] ?? '',
            'image_url' => $prod['image_url'] ?? '',
            'category' => $prod['category'] ?? [],
            'shop_name' => $prod['shop_name'] ?? '',
            'shop_ads' => $prod['shop_ads'] ?? false,
            'sample_quota' => $prod['sample_quota'] ?? 0,
            'available_sample_quantity' => $prod['available_sample_quantity'] ?? 0,
            'sample_quantity' => $prod['sample_quantity'] ?? 0,
            'available_sample' => $prod['available_sample'] ?? false,
            'review_status' => $prod['review_status'] ?? 'PENDING',
            'status' => $prod['status'] ?? 'ACTIVE',
            'inventory' => $prod['inventory'] ?? 0,
            'is_available' => $prod['is_available'] ?? true
        ];
        
        $all_products[] = $formatted;
    }
            
            $page_token = $result['data']['next_page_token'] ?? null;
            $page++;
            
        } while ($page_token && $page <= 10);
        
        return [
            'success' => true,
            'data' => $all_products,
            'total' => count($all_products)
        ];
        
    } catch (Exception $e) {
        if ($retry_count < $max_retries) {
            sleep(2);
            return $this->get_campaign_products($campaign_id, $filters, $retry_count + 1);
        }
        
        return [
            'success' => false,
            'message' => $e->getMessage(),
            'data' => []
        ];
    }
}
    
    /**
     * Search products
     */
    public function search_products($filters = [])
    {
        $path = "/affiliate_partner/202405/products/search";
        
        $params = array_merge([
            'category_asset_cipher' => $this->default_cipher,
            'page_size' => 100,
            'has_open_collaboration' => true
        ], $filters);
        
        $result = $this->_api_request($path, $params);
        
        if (!$result['success']) {
            return $result;
        }
        
        $products = [];
        foreach ($result['data']['products'] ?? [] as $prod) {
            $products[] = $this->_format_product_data($prod);
        }
        
        return [
            'success' => true,
            'data' => $products,
            'total' => $result['data']['total_count'] ?? 0,
            'next_page_token' => $result['data']['next_page_token'] ?? null
        ];
    }
    
    /**
     * Get product detail
     */
    public function get_product_detail($product_id)
    {
        $path = "/affiliate_partner/202405/products/{$product_id}";
        $params = ['category_asset_cipher' => $this->default_cipher];
        $result = $this->_api_request($path, $params);
        
        if (!$result['success']) {
            return $result;
        }
        
        return [
            'success' => true,
            'data' => $this->_format_product_data($result['data']['product'] ?? [])
        ];
    }
    
    /**
     * Generate product promotion link
     */
    public function generate_promotion_link($campaign_id, $product_id, $commission_percent)
    {
        $path = "/affiliate_partner/202405/campaigns/{$campaign_id}/products/{$product_id}/promotion_link/generate";
        
        $params = ['category_asset_cipher' => $this->default_cipher];
        $body = ['creator_commission_rate' => (int)($commission_percent * 100)];
        
        $result = $this->_api_request($path, $params, 'POST', $body);
        
        if (!$result['success']) {
            return $result;
        }
        
        return [
            'success' => true,
            'link' => $result['data']['product_promotion_link'] ?? '',
            'expire_at' => $result['data']['expire_at'] ?? null
        ];
    }
    
/**
 * Generate Multi Affiliate Campaign Product Link (Batch)
 * API: /affiliate_partner/202505/campaigns/{campaign_id}/products/promotion_links/generate_batch
 * 
 * Required scope: partner.tap_campaign.read
 * 
 * @param string $campaign_id Campaign ID
 * @param array $product_ids Array of product IDs (will be converted to strings)
 * @param string $category_asset_cipher Category asset cipher (optional)
 * @return array Result with success/failure status and data
 */
public function generate_multi_affiliate_links($campaign_id, $product_ids, $category_asset_cipher = '')
{
    // ============================================================
    // 1. VALIDASI PARAMETER
    // ============================================================
    if (empty($campaign_id)) {
        return [
            'success' => false,
            'message' => 'Campaign ID is required',
            'code' => 'MISSING_CAMPAIGN_ID'
        ];
    }
    
    if (empty($product_ids)) {
        return [
            'success' => false,
            'message' => 'Product IDs are required',
            'code' => 'MISSING_PRODUCT_IDS'
        ];
    }
    
    // ============================================================
    // 2. KONVERSI PRODUCT IDS KE ARRAY
    // ============================================================
    // Jika product_ids adalah string JSON, decode dulu
    if (is_string($product_ids)) {
        $decoded = json_decode($product_ids, true);
        if (is_array($decoded)) {
            $product_ids = $decoded;
        } else {
            // Jika string biasa seperti "123,456,789"
            $product_ids = explode(',', $product_ids);
        }
    }
    
    // Pastikan product_ids adalah array
    if (!is_array($product_ids)) {
        return [
            'success' => false,
            'message' => 'Product IDs must be an array or JSON string',
            'code' => 'INVALID_PRODUCT_IDS_FORMAT'
        ];
    }
    
    // ============================================================
    // 3. FILTER DAN VALIDASI PRODUCT IDS
    // ============================================================
    // Hapus ID kosong atau invalid
    $product_ids = array_filter($product_ids, function($id) {
        return !empty($id) && $id !== '0' && $id !== 0;
    });
    
    // 🔥 KRUSIAL: Konversi ke string untuk menghindari scientific notation
    // Contoh: 1729452955340867901 menjadi "1729452955340867901"
    // BUKAN "1.729453e+18"
    $product_ids = array_map('strval', array_values($product_ids));
    
    // Hapus duplikat
    $product_ids = array_values(array_unique($product_ids));
    
    // Cek apakah masih ada product IDs
    if (count($product_ids) === 0) {
        return [
            'success' => false,
            'message' => 'No valid product IDs provided after filtering',
            'code' => 'EMPTY_PRODUCT_IDS'
        ];
    }
    
    // Batasi maksimal 50 (sesuai dokumentasi)
    if (count($product_ids) > 50) {
        return [
            'success' => false,
            'message' => 'Maximum 50 product IDs allowed, got ' . count($product_ids),
            'code' => 'TOO_MANY_PRODUCT_IDS'
        ];
    }
    
    // ============================================================
    // 4. GUNAKAN DEFAULT CIPHER JIKA TIDAK DISEDIAKAN
    // ============================================================
    if (empty($category_asset_cipher) && property_exists($this, 'default_cipher')) {
        $category_asset_cipher = $this->default_cipher;
    }
    
    if (empty($category_asset_cipher)) {
        return [
            'success' => false,
            'message' => 'Category asset cipher is required',
            'code' => 'MISSING_CIPHER'
        ];
    }
    
    // ============================================================
    // 5. BUILD REQUEST
    // ============================================================
    $path = "/affiliate_partner/202505/campaigns/{$campaign_id}/products/promotion_links/generate_batch";
    
    // Query parameters (tanpa sign dulu)
    $params = [
        'category_asset_cipher' => $category_asset_cipher
    ];
    
    // 🔥 BODY: product_ids sebagai STRING (bukan integer)
    $body = ['product_ids' => $product_ids];
    
    // Log untuk debugging
    log_message('debug', '=== MULTI LINK API ===');
    log_message('debug', 'Campaign ID: ' . $campaign_id);
    log_message('debug', 'Product IDs (as string): ' . json_encode($product_ids));
    log_message('debug', 'Category Asset Cipher: ' . $category_asset_cipher);
    log_message('debug', 'Body JSON: ' . json_encode($body));
    
    // ============================================================
    // 6. EKSEKUSI API REQUEST
    // ============================================================
    $result = $this->_api_request($path, $params, 'POST', $body);
    
    // ============================================================
    // 7. PROSES RESPONSE
    // ============================================================
    if ($result['success']) {
        // Extract data dari response
        $response_data = $result['data'] ?? [];
        
        // Extract product_promotion_links
        $product_promotion_links = [];
        $links = $response_data['product_promotion_links'] ?? $response_data['promotion_links'] ?? [];
        
        foreach ($links as $link) {
            $product_promotion_links[] = [
                'product_id' => (string)($link['product_id'] ?? ''),
                'link' => $link['link'] ?? $link['promotion_link'] ?? '',
                'expire_at' => $link['expire_at'] ?? null
            ];
        }
        
        // 🔥 FORMAT RESPONSE SESUAI DOKUMENTASI
        return [
            'success' => true,
            'code' => 0,
            'message' => 'Success',
            'data' => [
                'product_promotion_links' => $product_promotion_links,
                'failed_product_ids' => $response_data['failed_product_ids'] ?? []
            ],
            'request_id' => $result['request_id'] ?? null,
            // Additional info for convenience
            'total_success' => count($product_promotion_links),
            'total_failed' => count($response_data['failed_product_ids'] ?? [])
        ];
    }
    
    // ============================================================
    // 8. HANDLE ERROR
    // ============================================================
    log_message('error', 'Multi Link API Error: ' . json_encode($result));
    
    // Tambahkan informasi product_ids ke response error untuk debugging
    return [
        'success' => false,
        'code' => $result['code'] ?? 'unknown',
        'message' => $result['message'] ?? 'Unknown API Error',
        'data' => null,
        'request_id' => $result['request_id'] ?? null,
        'product_ids_sent' => $product_ids,
        'raw_response' => $result['data'] ?? null
    ];
}

/**
 * Get list of product IDs from a campaign
 * Endpoint: GET /affiliate_partner/202405/campaigns/{campaign_id}/products
 * 
 * @param string $campaign_id Campaign ID
 * @param array $filters Additional filters (page_size, review_status, etc.)
 * @return array List of product IDs as strings
 */
public function get_campaign_product_ids($campaign_id, $filters = [])
{
    $default_filters = [
        'page_size' => 100,
        'review_status' => 'APPROVED' // Hanya ambil yang APPROVED
    ];
    
    $params = array_merge($default_filters, $filters);
    $result = $this->get_campaign_products($campaign_id, $params);
    
    if (!$result['success'] || empty($result['data'])) {
        return [
            'success' => false,
            'message' => 'Failed to get products: ' . ($result['message'] ?? 'Unknown error'),
            'product_ids' => [],
            'total' => 0
        ];
    }
    
    // 🔥 Ekstrak product IDs sebagai string
    $product_ids = [];
    foreach ($result['data'] as $product) {
        // Hanya ambil product yang tersedia dan ACTIVE
        if (isset($product['is_available']) && $product['is_available'] === true &&
            isset($product['status']) && $product['status'] === 'ACTIVE') {
            $product_ids[] = (string)$product['id'];
        }
    }
    
    return [
        'success' => true,
        'product_ids' => $product_ids,
        'total' => count($product_ids),
        'products' => $result['data']
    ];
}

/**
 * Generate Multi Affiliate Campaign Product Link - RAW Response
 * Menampilkan response murni dari API tanpa modifikasi
 * 
 * @param string $campaign_id Campaign ID
 * @param array $product_ids Array of product IDs (will be converted to strings)
 * @param string $category_asset_cipher Category asset cipher (optional)
 * @return array Raw API response
 */
public function generate_multi_affiliate_links_raw($campaign_id, $product_ids, $category_asset_cipher = '')
{
    // ============================================================
    // 1. VALIDASI PARAMETER
    // ============================================================
    if (empty($campaign_id)) {
        return [
            'error' => true,
            'message' => 'Campaign ID is required'
        ];
    }
    
    if (empty($product_ids)) {
        return [
            'error' => true,
            'message' => 'Product IDs are required'
        ];
    }
    
    // ============================================================
    // 2. KONVERSI PRODUCT IDS KE ARRAY STRING
    // ============================================================
    if (is_string($product_ids)) {
        $decoded = json_decode($product_ids, true);
        if (is_array($decoded)) {
            $product_ids = $decoded;
        } else {
            $product_ids = explode(',', $product_ids);
        }
    }
    
    if (!is_array($product_ids)) {
        return [
            'error' => true,
            'message' => 'Product IDs must be an array or JSON string'
        ];
    }
    
    // 🔥 Filter dan konversi ke string
    $product_ids = array_filter($product_ids, function($id) {
        return !empty($id) && $id !== '0' && $id !== 0;
    });
    $product_ids = array_map('strval', array_values($product_ids));
    $product_ids = array_values(array_unique($product_ids));
    
    if (count($product_ids) === 0) {
        return [
            'error' => true,
            'message' => 'No valid product IDs provided'
        ];
    }
    
    if (count($product_ids) > 50) {
        return [
            'error' => true,
            'message' => 'Maximum 50 product IDs allowed, got ' . count($product_ids)
        ];
    }
    
    // ============================================================
    // 3. GUNAKAN DEFAULT CIPHER
    // ============================================================
    if (empty($category_asset_cipher) && property_exists($this, 'default_cipher')) {
        $category_asset_cipher = $this->default_cipher;
    }
    
    if (empty($category_asset_cipher)) {
        return [
            'error' => true,
            'message' => 'Category asset cipher is required'
        ];
    }
    
    // ============================================================
    // 4. DAPATKAN ACCESS TOKEN
    // ============================================================
    try {
        $access_token = $this->get_valid_token();
    } catch (Exception $e) {
        return [
            'error' => true,
            'message' => 'Failed to get access token: ' . $e->getMessage()
        ];
    }
    
    // ============================================================
    // 5. BUILD REQUEST
    // ============================================================
    $timestamp = time();
    $path = "/affiliate_partner/202505/campaigns/{$campaign_id}/products/promotion_links/generate_batch";
    
    // Query parameters
    $query_params = [
        'app_key' => $this->app_key,
        'timestamp' => $timestamp,
        'category_asset_cipher' => $category_asset_cipher
    ];
    
    ksort($query_params);
    
    // Build param string untuk signature
    $param_string = '';
    foreach ($query_params as $key => $value) {
        $param_string .= $key . $value;
    }
    
    // 🔥 BODY: product_ids sebagai STRING
    $body_data = ['product_ids' => $product_ids];
    $body_json = json_encode($body_data);
    
    // Generate signature
    $string_to_sign = $this->app_secret . $path . $param_string . $body_json . $this->app_secret;
    $query_params['sign'] = hash_hmac('sha256', $string_to_sign, $this->app_secret);
    
    // Build URL
    $url = $this->openapi_base . $path . '?' . http_build_query($query_params);
    
    // ============================================================
    // 6. EKSEKUSI CURL - RAW RESPONSE
    // ============================================================
    $headers = [
        "x-tts-access-token: " . $access_token,
        "Content-Type: application/json",
        "Accept: application/json"
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
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_VERBOSE => true
    ]);
    
    // Capture verbose output untuk debugging
    $verbose = fopen('php://temp', 'w+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);
    
    // Eksekusi
    $response_body = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    $info = curl_getinfo($ch);
    
    // Ambil verbose log
    rewind($verbose);
    $verbose_log = stream_get_contents($verbose);
    fclose($verbose);
    
    curl_close($ch);
    
    // ============================================================
    // 7. RETURN RAW RESPONSE
    // ============================================================
    // Decode response untuk ditampilkan
    $decoded_response = json_decode($response_body, true);
    
    return [
        // === REQUEST INFO ===
        'request' => [
            'url' => $url,
            'method' => 'POST',
            'headers' => $headers,
            'body' => $body_json,
            'timestamp' => $timestamp,
            'signature' => $query_params['sign'],
            'string_to_sign' => $string_to_sign,
            'product_ids_sent' => $product_ids,
            'campaign_id' => $campaign_id,
            'category_asset_cipher' => $category_asset_cipher
        ],
        
        // === RESPONSE INFO ===
        'response' => [
            'http_code' => $http_code,
            'content_type' => $info['content_type'] ?? 'unknown',
            'total_time' => $info['total_time'] ?? 0,
            'body_raw' => $response_body,
            'body_decoded' => $decoded_response,
            'curl_error' => $curl_error
        ],
        
        // === VERBOSE LOG ===
        'verbose_log' => $verbose_log,
        
        // === STATUS ===
        'success' => ($http_code == 200 && $decoded_response && isset($decoded_response['code']) && $decoded_response['code'] == 0)
    ];
}
    // ========== ORDER MANAGEMENT ==========
    
    /**
     * Search affiliate orders
     */
    public function search_affiliate_orders($filters = [])
{
    $path = "/affiliate_partner/202603/orders/search";
    
    $valid_params = [
        'create_time_ge', 'create_time_lt', 'campaign_id', 'page_size', 
        'page_token', 'order_status', 'product_id', 'creator_username'
    ];
    
    $params = [];
    foreach ($filters as $key => $value) {
        if (in_array($key, $valid_params) && $value !== '' && $value !== null) {
            $params[$key] = $value;
        }
    }
    
    $params['category_asset_cipher'] = $this->default_cipher;
    
    if (!isset($params['page_size'])) {
        $params['page_size'] = 100;
    }
    
    // 🔥 PERBAIKI: JANGAN kirim params sebagai body!
    // API order search menggunakan GET dengan query params, bukan POST dengan body
    $result = $this->_api_request($path, $params, 'POST', null);  // ← body = null
    
    if (!$result['success']) {
        return $result;
    }
    
    // 🔥 Juga cek 'sku_orders' selain 'orders'
  $raw_orders = $result['data']['sku_orders'] ?? $result['data']['orders'] ?? [];

$orders = [];
foreach ($raw_orders as $order) {
    $orders[] = $this->_format_order_data($order);
}
    
    return [
        'success' => true,
        'data' => $orders,
        'total' => count($orders),
        'next_page_token' => $result['data']['next_page_token'] ?? null,
        'total_count' => $result['data']['total_count'] ?? 0
    ];
}
    
    /**
 * Search affiliate orders - RETURN RAW DATA (tanpa format)
 * Khusus untuk debugging
 */
public function search_affiliate_orders_raw($filters = []) {
    $path = "/affiliate_partner/202603/orders/search";
    
    $valid_params = [
        'create_time_ge', 'create_time_lt', 'campaign_id', 'page_size', 
        'page_token', 'order_status', 'product_id', 'creator_username'
    ];
    
    $params = [];
    foreach ($filters as $key => $value) {
        if (in_array($key, $valid_params) && $value !== '' && $value !== null) {
            $params[$key] = $value;
        }
    }
    
    $params['category_asset_cipher'] = $this->default_cipher;
    
    if (!isset($params['page_size'])) {
        $params['page_size'] = 100;
    }
    
    // 🔥 PERBAIKI: body = null
    $result = $this->_api_request($path, $params, 'POST', null);
    
    if (!$result['success']) {
        return $result;
    }
    
    return [
        'success' => true,
        'data' => $result['data']['sku_orders'] ?? $result['data']['orders'] ?? [],
        'total_count' => $result['data']['total_count'] ?? 0,
        'next_page_token' => $result['data']['next_page_token'] ?? null
    ];
}
    
    /**
     * Get order detail
     */
    public function get_order_detail($order_id)
    {
        $path = "/affiliate_partner/202411/orders/{$order_id}";
        $params = ['category_asset_cipher' => $this->default_cipher];
        $result = $this->_api_request($path, $params);
        
        if (!$result['success']) {
            return $result;
        }
        
        return [
            'success' => true,
            'data' => $this->_format_order_data($result['data']['order'] ?? [])
        ];
    }
    
    // ========== CREATOR MANAGEMENT ==========
    
    /**
     * Search creators
     */
    public function search_creators($filters = [])
    {
        $path = "/affiliate_partner/202405/creators/search";
        
        $valid_params = [
            'keyword', 'category', 'page_size', 'page_token',
            'min_follower_count', 'max_follower_count', 'min_gmv', 'sort_by'
        ];
        
        $params = [];
        foreach ($filters as $key => $value) {
            if (in_array($key, $valid_params) && $value !== '' && $value !== null) {
                $params[$key] = $value;
            }
        }
        
        $params['category_asset_cipher'] = $this->default_cipher;
        
        if (!isset($params['page_size'])) {
            $params['page_size'] = 50;
        }
        
        $result = $this->_api_request($path, $params);
        
        if (!$result['success']) {
            return $result;
        }
        
        $creators = [];
        foreach ($result['data']['creators'] ?? [] as $creator) {
            $creators[] = $this->_format_creator_data($creator);
        }
        
        return [
            'success' => true,
            'data' => $creators,
            'total' => $result['data']['total_count'] ?? 0,
            'next_page_token' => $result['data']['next_page_token'] ?? null
        ];
    }
    
   
    /**
     * Get creator detail
     */
    public function get_creator_detail($creator_id)
    {
        $path = "/affiliate_partner/202405/creators/{$creator_id}";
        $params = ['category_asset_cipher' => $this->default_cipher];
        $result = $this->_api_request($path, $params);
        
        if (!$result['success']) {
            return $result;
        }
        
        return [
            'success' => true,
            'data' => $this->_format_creator_data($result['data']['creator'] ?? [])
        ];
    }
    
    // ========== SAMPLE MANAGEMENT (SELLER) ==========
    
    /**
     * Get sample requests (as Seller)
     */
    public function get_seller_sample_requests($filters = [])
    {
        $path = "/affiliate_seller/202508/sample_applications/search";
        
        $body = [];
        if (!empty($filters['product_id'])) $body['product_id'] = $filters['product_id'];
        if (!empty($filters['username'])) $body['username'] = $filters['username'];
        if (!empty($filters['target_collabration_id'])) $body['target_collabration_id'] = $filters['target_collabration_id'];
        if (!empty($filters['status'])) $body['status'] = $filters['status'];
        
        $params = ['page_size' => $filters['page_size'] ?? 50];
        if (!empty($filters['page_token'])) $params['page_token'] = $filters['page_token'];
        
        $result = $this->_api_request_seller($path, $params, 'POST', $body);
        
        if (!$result['success']) {
            return [
                'success' => false,
                'message' => $result['message'] ?? 'Failed to get sample requests',
                'data' => []
            ];
        }
        
        $samples = [];
        foreach ($result['data']['sample_applications'] ?? [] as $sample) {
            $samples[] = $this->_format_seller_sample_data($sample);
        }
        
        return [
            'success' => true,
            'data' => $samples,
            'total' => count($samples),
            'next_page_token' => $result['data']['next_page_token'] ?? null
        ];
    }
    
    /**
     * Approve sample request (as Seller)
     */
    public function approve_seller_sample_request($sample_request_id, $campaign_id, $product_id, $creator_hid, $sku_id = null)
    {
        $path = "/affiliate_seller/202507/sample_applications/{$sample_request_id}/review";
        
        $body = ['review_result' => 'APPROVE'];
        
        $result = $this->_api_request_seller($path, [], 'POST', $body);
        
        if (!$result['success']) {
            return $result;
        }
        
        return [
            'success' => true,
            'message' => 'Sample request approved successfully',
            'data' => $result['data'] ?? []
        ];
    }
    
    /**
     * Reject sample request (as Seller)
     */
    public function reject_seller_sample_request($sample_request_id, $campaign_id, $product_id, $creator_hid, $reason = '', $sku_id = null)
    {
        $path = "/affiliate_seller/202507/sample_applications/{$sample_request_id}/review";
        
        $body = ['review_result' => 'REJECT'];
        if (!empty($reason)) {
            $body['reject_reason'] = $reason;
        }
        
        $result = $this->_api_request_seller($path, [], 'POST', $body);
        
        if (!$result['success']) {
            return $result;
        }
        
        return [
            'success' => true,
            'message' => 'Sample request rejected',
            'data' => $result['data'] ?? []
        ];
    }
    
    /**
     * Get sample request deeplink (as Seller)
     */
    public function get_sample_request_deeplink($product_id, $campaign_id, $sku_id = null, $valid_days = 7)
    {
        $path = "/seller/202512/sample_applications/deeplink";
        
        $params = [
            'product_id' => $product_id,
            'campaign_id' => $campaign_id,
            'valid_days' => min(14, max(1, $valid_days))
        ];
        
        if ($sku_id) {
            $params['sku_id'] = $sku_id;
        }
        
        $result = $this->_api_request($path, $params);
        
        if (!$result['success']) {
            return $result;
        }
        
        return [
            'success' => true,
            'deeplink' => $result['data']['deeplink'] ?? '',
            'expire_at' => $result['data']['expire_at'] ?? null
        ];
    }
    
    // ========== BRAND & TOP PERFORMERS ==========
    
    /**
     * Get top brands from products and orders
     */
    public function get_top_brands_from_products($products, $orders_by_product)
    {
        $brands = [];
        
        foreach ($products as $product) {
            $shop_name = $product['shop_name'] ?: 'Unknown';
            
            if (!isset($brands[$shop_name])) {
                $brands[$shop_name] = [
                    'name' => $shop_name,
                    'total_gmv' => 0,
                    'total_orders' => 0,
                    'creator_count' => 0,
                    'products' => []
                ];
            }
            
            $product_id = $product['id'];
            if (isset($orders_by_product[$product_id])) {
                $brands[$shop_name]['total_gmv'] += $orders_by_product[$product_id]['gmv'];
                $brands[$shop_name]['total_orders'] += $orders_by_product[$product_id]['orders'];
                $brands[$shop_name]['creator_count'] += $orders_by_product[$product_id]['unique_creators'];
            }
            
            $brands[$shop_name]['products'][] = $product;
        }
        
        foreach ($brands as &$brand) {
            $brand['roas'] = $brand['total_gmv'] > 0 ? round($brand['total_gmv'] / ($brand['total_gmv'] * 0.1), 2) : 0;
        }
        
        uasort($brands, function($a, $b) {
            return $b['total_gmv'] <=> $a['total_gmv'];
        });
        
        return array_values($brands);
    }
    
    // ========== PRIVATE API REQUEST METHODS ==========
    
    /**
     * API Request untuk Affiliate Partner endpoints
     */
    private function _api_request($path, $params = [], $method = 'GET', $body = null)
    {
        try {
            $access_token = $this->get_valid_token();
            $timestamp = time();
            
            $query = [
            'app_key' => $this->app_key,
            'timestamp' => $timestamp,
            'category_asset_cipher' => $this->default_cipher
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
            
            $string_to_sign = $this->app_secret . $path . $param_string . $this->app_secret;
            
            if ($method === 'POST' && $body !== null) {
                $body_json = is_array($body) ? json_encode($body) : $body;
                $string_to_sign = $this->app_secret . $path . $param_string . $body_json . $this->app_secret;
            }
            
            $query['sign'] = hash_hmac('sha256', $string_to_sign, $this->app_secret);
            
            $url = $this->openapi_base . $path . '?' . http_build_query($query);
            
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
                
            } elseif ($method === 'PUT') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($body) ? json_encode($body) : $body);
            } elseif ($method === 'DELETE') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            }
            
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if (curl_error($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                return ['success' => false, 'message' => "cURL Error: {$error}"];
            }
            
            curl_close($ch);
            
            $decoded = json_decode($response, true);
            
            if (!$decoded) {
                return ['success' => false, 'message' => 'Invalid JSON response'];
            }
            
            if (!isset($decoded['code']) || $decoded['code'] != 0) {
                return [
                    'success' => false,
                    'message' => $decoded['message'] ?? 'Unknown API Error',
                    'code' => $decoded['code'] ?? 'unknown'
                ];
            }
            
            return [
                'success' => true,
                'data' => $decoded['data'] ?? [],
                'http_code' => $http_code
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * API Request khusus untuk Seller endpoints
     */
    private function _api_request_seller($path, $params = [], $method = 'GET', $body = null, $require_shop_cipher = true)
{
    try {
        $access_token = $this->get_valid_seller_token();
        
        $timestamp = time();
        
        $query = [
            'app_key' => $this->app_key,
            'timestamp' => $timestamp
        ];
        
        // 🔥 HANYA tambahkan shop_cipher jika diperlukan
        if ($require_shop_cipher) {
            $seller_token = $this->CI->Jsm_token_model->get_latest_token_by_type(2);
            $shop_cipher = $seller_token->shop_id ?? '';
            $query['shop_cipher'] = $shop_cipher;
            log_message('debug', 'Using shop_cipher: ' . $shop_cipher);
        } else {
            log_message('debug', 'Endpoint does not require shop_cipher');
        }
        
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
        
        $string_to_sign = $this->app_secret . $path . $param_string . $this->app_secret;
        
        if ($method === 'POST' && $body !== null) {
            $body_json = is_array($body) ? json_encode($body) : $body;
            $string_to_sign = $this->app_secret . $path . $param_string . $body_json . $this->app_secret;
        }
        
        $query['sign'] = hash_hmac('sha256', $string_to_sign, $this->app_secret);
        
        $url = $this->openapi_base . $path . '?' . http_build_query($query);
        
        log_message('debug', 'Seller API URL: ' . preg_replace('/app_secret=[^&]+/', 'app_secret=HIDDEN', $url));
        
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
        
        if (curl_error($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            log_message('error', 'cURL Error: ' . $error);
            return ['success' => false, 'message' => "cURL Error: {$error}"];
        }
        
        curl_close($ch);
        
        log_message('debug', 'Seller API Response: ' . substr($response, 0, 1000));
        
        $decoded = json_decode($response, true);
        
        if (!$decoded) {
            return ['success' => false, 'message' => 'Invalid JSON response'];
        }
        
        if (!isset($decoded['code']) || $decoded['code'] != 0) {
            return [
                'success' => false,
                'message' => $decoded['message'] ?? 'Unknown API Error',
                'code' => $decoded['code'] ?? 'unknown'
            ];
        }
        
        return [
            'success' => true,
            'data' => $decoded['data'] ?? [],
            'http_code' => $http_code
        ];
        
    } catch (Exception $e) {
        log_message('error', 'Seller API Exception: ' . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
    
    /**
     * Simple curl get untuk auth
     */
    private function _curl_get($url)
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $response = curl_exec($ch);
        
        if (curl_error($ch)) {
            throw new Exception("cURL Error: " . curl_error($ch));
        }
        
        curl_close($ch);
        return $response;
    }
    
    // ========== GETTER METHODS ==========
    
    public function get_default_cipher()
    {
        return $this->default_cipher;
    }
public function get_openapi_base() {
    return $this->openapi_base;
}
public function get_app_secret() {
    return $this->app_secret;
}
    public function get_app_key()
    {
        return $this->app_key;
    }
    
    public function get_service_id()
    {
        return $this->service_id;
    }
    
    public function get_api_type()
    {
        return $this->api_type;
    }
    
    // ========== FORMATTING METHODS ==========
    
    /**
     * Format product data dari API response
     */
   private function _format_product_data($prod)
{
    $product_id = $prod['id'] ?? '';
    $title = $prod['name'] ?? $prod['title'] ?? $prod['product_name'] ?? 'Unknown Product';
    
    // Get price
    $price = 0;
    if (isset($prod['lowest_price'])) {
        if (is_array($prod['lowest_price'])) {
            $price = floatval($prod['lowest_price']['amount'] ?? 0);
        } else {
            $price = floatval($prod['lowest_price']);
        }
    } elseif (isset($prod['sku_information_list']) && is_array($prod['sku_information_list'])) {
        foreach ($prod['sku_information_list'] as $sku) {
            if (isset($sku['base_price']['sale_price'])) {
                $price = floatval($sku['base_price']['sale_price']);
                break;
            }
        }
    }
    
    // Get commission rates
    $open_collab_raw = isset($prod['open_collaboration_commission_rate']) ? floatval($prod['open_collaboration_commission_rate']) : 0;
    $open_collab_percent = $open_collab_raw > 0 ? ($open_collab_raw > 100 ? round($open_collab_raw / 100, 2) : round($open_collab_raw, 2)) : 8;
    
    // Category
    $category = 'General';
    if (isset($prod['category'])) {
        if (is_array($prod['category'])) {
            $category = $prod['category']['name'] ?? 'General';
        } else {
            $category = $prod['category'];
        }
    }
    
    $review_status = $prod['review_status'] ?? 'PENDING';
    $is_available = $prod['is_available'] ?? true;
    $is_approved = ($review_status === 'APPROVED');
    
    
    return [
        'id' => $product_id,
        'title' => $title,
        'price' => (int)$price,
        'image_url' => $prod['main_image_url'] ?? $prod['image_url'] ?? '',
        'category' => $category,
        'open_collab' => $open_collab_percent,
        'commission_rate' => $open_collab_percent,
        'sales_count' => intval($prod['product_sales'] ?? $prod['sales_count'] ?? 0),
        'shop_name' => $prod['shop_name'] ?? '',
        'review_status' => $review_status,
        'is_available' => $is_available,
        'is_approved' => $is_approved,
        'is_valid' => $is_approved && $is_available,
         // 🔥 TAMBAHKAN INI!
        'shop_ads_commission_rate' => $prod['shop_ads_commission_rate'] ?? 0,
        'partner_shop_ads_commission_rate' => $prod['partner_shop_ads_commission_rate'] ?? 0,
        'raw_data' => json_encode($prod)
    ];
}

    /**
     * Format order data dari API response
     */
    private function _format_order_data($order)
{
    $create_time = isset($order['create_time']) ? (int)$order['create_time'] : 0;
    $create_date_local = $create_time ? date('Y-m-d', $create_time) : date('Y-m-d');
    
    // 🔥 v202603: Semua field ada di ROOT order, bukan di 'skus'
    $campaign_id = $order['campaign_id'] ?? '';
    $product_id = $order['product_id'] ?? '';
    $product_name = $order['product_name'] ?? '';
    $creator_username = $order['creator_username'] ?? '';
    $quantity = (int)($order['quantity'] ?? 1);
      $sku_order = $order; // Jika sudah dari dalam sku_orders
    
    // Atau jika masih wrapper, ambil dari sku_orders
    if (isset($order['sku_orders']) && is_array($order['sku_orders'])) {
        // Jika masih dalam wrapper response, ambil yang pertama
        $sku_order = $order['sku_orders'][0] ?? $order;
    }
      // 🔥 AMBIL SKU_ID dari sku_orders
    $sku_id = $sku_order['sku_id'] ?? '';

    // 🔥 HARGA - dari price.amount
    $price_amount = 0;
    if (isset($order['price']) && is_array($order['price'])) {
        $price_amount = floatval($order['price']['amount'] ?? 0);
    } else {
        $price_amount = floatval($order['price'] ?? 0);
    }
    
    // 🔥 GMV: estimated_commission_base.amount
    $commission_base = floatval($order['estimated_commission_base']['amount'] ?? 0);
    $gmv = $commission_base > 0 ? $commission_base : ($price_amount * $quantity);
    
    // 🔥 SETTLE STATUS
    $settle_status = $order['settle_status'] ?? $order['status'] ?? '';
    
    // 🔥 KOMISI TAP
    $estimated_tap = 
        floatval($order['estimated_partner_standard_commission']['amount'] ?? 0) +
        floatval($order['estimated_partner_shop_ads_commission']['amount'] ?? 0) +
        floatval($order['estimated_partner_tap_bonus_commission']['amount'] ?? 0);
    
    $actual_tap = 
        floatval($order['actual_partner_standard_commission']['amount'] ?? 0) +
        floatval($order['actual_partner_shop_ads_commission']['amount'] ?? 0) +
        floatval($order['actual_partner_tap_bonus_commission']['amount'] ?? 0);
    
    // 🔥 KOMISI CREATOR
    $estimated_creator = 
        floatval($order['estimated_creator_standard_commission']['amount'] ?? 0) +
        floatval($order['estimated_creator_shop_ads_commission']['amount'] ?? 0) +
        floatval($order['estimated_creator_tap_bonus_commission']['amount'] ?? 0);
    
    $actual_creator = 
        floatval($order['actual_creator_standard_commission']['amount'] ?? 0) +
        floatval($order['actual_creator_shop_ads_commission']['amount'] ?? 0) +
        floatval($order['actual_creator_tap_bonus_commission']['amount'] ?? 0);
    
    return [
        'order_id' => $order['id'] ?? '',
          'sku_id' => $sku_id, 
        'campaign_id' => $campaign_id,
        'campaign_name' => $order['campaign_name'] ?? '',
        'product_id' => $product_id,
        'product_name' => $product_name,
        'creator_username' => $creator_username,
        'creator_hid' => $order['creator_hid'] ?? '',
        'order_status' => $settle_status,
        'create_time_utc' => $create_time,
        'create_time_formatted' => $create_time ? date('Y-m-d H:i:s', $create_time) : '',
        'create_date_local' => $create_date_local,
        'price' => $price_amount,
        'quantity' => $quantity,
        'affiliate_gmv' => $gmv,
        'estimated_affiliate_commission' => $estimated_tap,
        'actual_affiliate_commission' => $actual_tap,
        'estimated_creator_commission' => $estimated_creator,
        'actual_creator_commission' => $actual_creator,
        'items_sold' => $quantity
    ];
}
    
    /**
     * Format seller sample data
     */
    private function _format_seller_sample_data($sample)
    {
        $status_map = [
            'PENDING' => 'PENDING',
            'AWAITING_SHIPMENT' => 'APPROVED',
            'SHIPPED' => 'SHIPPED',
            'DELIVERED' => 'DELIVERED',
            'COMPLETED' => 'COMPLETED',
            'REJECTED' => 'REJECTED',
            'CANCELLED' => 'CANCELLED'
        ];
        
        $raw_status = $sample['status'] ?? 'PENDING';
        $display_status = $status_map[$raw_status] ?? 'PENDING';
        
        return [
            'sample_request_id' => $sample['application_id'] ?? $sample['id'] ?? '',
            'product_id' => $sample['product_id'] ?? '',
            'product_name' => $sample['product_title'] ?? $sample['product_name'] ?? 'Unknown Product',
            'product_image' => $sample['product_image'] ?? '',
            'campaign_id' => $sample['campaign_id'] ?? '',
            'campaign_name' => $sample['campaign_name'] ?? '',
            'creator_username' => $sample['username'] ?? '',
            'status' => $display_status,
            'request_date' => $sample['create_time'] ?? '',
            'expire_date' => $sample['expire_time'] ?? '',
            'available_samples' => $sample['sample_quantity'] ?? 0,
            'tracking_number' => $sample['tracking_number'] ?? ''
        ];
    }
    
    /**
     * Format creator data
     */
    private function _format_creator_data($creator)
    {
        return [
            'id' => $creator['id'] ?? '',
            'username' => $creator['username'] ?? '',
            'display_name' => $creator['display_name'] ?? $creator['username'] ?? '',
            'avatar_url' => $creator['avatar_url'] ?? '',
            'follower_count' => (int)($creator['follower_count'] ?? 0),
            'total_gmv' => (float)($creator['total_gmv'] ?? 0),
            'categories' => $creator['categories'] ?? [],
            'engagement_rate' => (float)($creator['engagement_rate'] ?? 0)
        ];
    }
    
    /**
 * Review a product submission for a campaign (Approve or Reject)

 */
public function review_campaign_product($campaign_id, $product_id, $review_result, $reject_reasons = null) {
    $path = "/affiliate_partner/202405/campaigns/{$campaign_id}/products/{$product_id}/review";
    
    $body = [
        'review_result' => strtoupper($review_result) // 'APPROVE' or 'REJECT'
    ];
    
    if ($review_result === 'REJECT' && !empty($reject_reasons)) {
        $body['reject_reasons'] = $reject_reasons;
    }
    
    // Gunakan method _api_request yang sudah ada di library Jsm_api
    // Pastikan method ini handle method POST
    $result = $this->_api_request($path, [], 'POST', $body);
    
    return $result;
}

/**
 * Get Bestselling Products
 * Endpoint: GET /analytics/202511/products/bestselling
 */
public function get_bestselling_products($params = []) {
    $path = "/analytics/202511/products/bestselling";
    
    // 🔥 Gunakan tanggal maksimal yang diketahui valid
    $latest_available_date = '2026-05-17';
    
    // Bersihkan parameter
    $clean_params = [];
    
    if (isset($params['time_slot'])) {
        $clean_params['time_slot'] = $params['time_slot'];
    } else {
        $clean_params['time_slot'] = '7D';
    }
    
    // Untuk custom date range
    if (isset($params['start_date']) && isset($params['end_date'])) {
        $start = $params['start_date'];
        $end = $params['end_date'];
        
        if ($end > $latest_available_date) {
            $end = $latest_available_date;
        }
        if ($start > $end) {
            $start = $end;
        }
        
        $clean_params['start_date'] = $start;
        $clean_params['end_date'] = $end;
        unset($clean_params['time_slot']);
        $clean_params['time_slot'] = 'CUSTOM';
    } else {
        // Gunakan date parameter dengan latest_available_date
        $clean_params['date'] = $latest_available_date;
    }
    
    if (isset($params['page_size'])) {
        $clean_params['page_size'] = $params['page_size'];
    } else {
        $clean_params['page_size'] = 50;
    }
    
    if (isset($params['page_token'])) {
        $clean_params['page_token'] = $params['page_token'];
    }
    
    log_message('debug', 'get_bestselling_products params: ' . json_encode($clean_params));
    
    return $this->_api_request_analytics($path, $clean_params);
}
/**
 * API Request khusus untuk Analytics endpoints
 * Analytics endpoints mungkin tidak memerlukan category_asset_cipher
 */
private function _api_request_analytics($path, $params = [], $method = 'GET', $body = null)
{
    try {
        // Gunakan SELLER TOKEN (user_type=2)
        $access_token = $this->get_valid_seller_token();
        log_message('debug', 'Analytics using SELLER token');
        
        // AMBIL SHOP_CIPHER dari database
        $seller_token = $this->CI->Jsm_token_model->get_latest_token_by_type(2);
        $shop_cipher = $seller_token->shop_id ?? $this->default_cipher;
        
        log_message('debug', 'Using shop_cipher: ' . $shop_cipher);
        
        // 🔥 FIX: Gunakan tanggal maksimal yang diketahui valid (2026-05-17)
        // Data setelah tanggal ini belum tersedia di API
        $latest_available_date = '2026-05-17';
        $today = date('Y-m-d');
        
        // Buat parameter baru yang sudah dipastikan valid
        $clean_params = [];
        
        // Handle date parameter
        if (isset($params['date'])) {
            // Jika date > latest_available_date, paksa ke latest_available_date
            if ($params['date'] > $latest_available_date) {
                $clean_params['date'] = $latest_available_date;
                log_message('debug', 'Date changed from ' . $params['date'] . ' to ' . $latest_available_date);
            } else {
                $clean_params['date'] = $params['date'];
            }
        }
        
        // Handle start_date dan end_date untuk custom range
        if (isset($params['start_date']) && isset($params['end_date'])) {
            $start = $params['start_date'];
            $end = $params['end_date'];
            
            // Pastikan end_date tidak melebihi latest_available_date
            if ($end > $latest_available_date) {
                $end = $latest_available_date;
                log_message('debug', 'end_date changed to ' . $latest_available_date);
            }
            
            // Pastikan start_date tidak lebih besar dari end_date
            if ($start > $end) {
                $start = $end;
            }
            
            $clean_params['start_date'] = $start;
            $clean_params['end_date'] = $end;
            $clean_params['time_slot'] = 'CUSTOM';
        }
        
        // Handle time_slot jika tidak ada custom range
        if (!isset($clean_params['time_slot']) && !isset($clean_params['start_date'])) {
            $time_slot = $params['time_slot'] ?? '7D';
            $clean_params['time_slot'] = $time_slot;
            // Jika date belum diset, gunakan latest_available_date
            if (!isset($clean_params['date'])) {
                $clean_params['date'] = $latest_available_date;
            }
        }
        
        // Pastikan author_type untuk creators
        if (isset($params['author_type'])) {
            $clean_params['author_type'] = $params['author_type'];
        }
        
        // Pastikan page_size
        if (isset($params['page_size'])) {
            $clean_params['page_size'] = $params['page_size'];
        } else {
            $clean_params['page_size'] = 50;
        }
        
        // Pastikan page_token jika ada
        if (isset($params['page_token'])) {
            $clean_params['page_token'] = $params['page_token'];
        }
        
        $timestamp = time();
        
        // Query parameters dengan shop_cipher WAJIB
        $query = [
            'app_key' => $this->app_key,
            'timestamp' => $timestamp,
            'shop_cipher' => $shop_cipher
        ];
        
        foreach ($clean_params as $key => $value) {
            if ($value !== '' && $value !== null) {
                $query[$key] = $value;
            }
        }
        
        ksort($query);
        
        $param_string = '';
        foreach ($query as $key => $value) {
            $param_string .= $key . $value;
        }
        
        $string_to_sign = $this->app_secret . $path . $param_string . $this->app_secret;
        
        if ($method === 'POST' && $body !== null) {
            $body_json = is_array($body) ? json_encode($body) : $body;
            $string_to_sign = $this->app_secret . $path . $param_string . $body_json . $this->app_secret;
        }
        
        $query['sign'] = hash_hmac('sha256', $string_to_sign, $this->app_secret);
        
        $url = $this->openapi_base . $path . '?' . http_build_query($query);
        
        log_message('debug', 'Analytics Final URL: ' . preg_replace('/app_secret=[^&]+/', 'app_secret=HIDDEN', $url));
        log_message('debug', 'Analytics Final Params: ' . json_encode($clean_params));
        
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
        
        if (curl_error($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            log_message('error', 'cURL Error: ' . $error);
            return ['success' => false, 'message' => "cURL Error: {$error}"];
        }
        
        curl_close($ch);
        
        log_message('debug', 'Analytics Response HTTP Code: ' . $http_code);
        log_message('debug', 'Analytics Response: ' . substr($response, 0, 500));
        
        $decoded = json_decode($response, true);
        
        if (!$decoded) {
            return ['success' => false, 'message' => 'Invalid JSON response', 'raw_response' => substr($response, 0, 500)];
        }
        
        if (!isset($decoded['code']) || $decoded['code'] != 0) {
            return [
                'success' => false,
                'message' => $decoded['message'] ?? 'Unknown API Error',
                'code' => $decoded['code'] ?? 'unknown',
                'raw_response' => $response
            ];
        }
        
        return [
            'success' => true,
            'data' => $decoded['data'] ?? [],
            'http_code' => $http_code
        ];
        
    } catch (Exception $e) {
        log_message('error', 'Analytics API Exception: ' . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
/**
 * Get Bestselling Creators
 * Endpoint: GET /analytics/202511/creators/bestselling
 */
public function get_bestselling_creators($params = []) {
    $path = "/analytics/202511/creators/bestselling";
    
    // 🔥 Gunakan tanggal maksimal yang diketahui valid
    $latest_available_date = '2026-05-17';
    
    // Bersihkan parameter
    $clean_params = [];
    
    if (isset($params['time_slot'])) {
        $clean_params['time_slot'] = $params['time_slot'];
    } else {
        $clean_params['time_slot'] = '7D';
    }
    
    // Untuk custom date range
    if (isset($params['start_date']) && isset($params['end_date'])) {
        $start = $params['start_date'];
        $end = $params['end_date'];
        
        if ($end > $latest_available_date) {
            $end = $latest_available_date;
        }
        if ($start > $end) {
            $start = $end;
        }
        
        $clean_params['start_date'] = $start;
        $clean_params['end_date'] = $end;
        unset($clean_params['time_slot']);
        $clean_params['time_slot'] = 'CUSTOM';
    } else {
        // Gunakan date parameter dengan latest_available_date
        $clean_params['date'] = $latest_available_date;
    }
    
    if (isset($params['page_size'])) {
        $clean_params['page_size'] = $params['page_size'];
    } else {
        $clean_params['page_size'] = 20;
    }
    
    if (isset($params['page_token'])) {
        $clean_params['page_token'] = $params['page_token'];
    }
    
    // Wajib ada author_type
    $clean_params['author_type'] = $params['author_type'] ?? 'ALL';
    
    log_message('debug', 'get_bestselling_creators params: ' . json_encode($clean_params));
    
    return $this->_api_request_analytics($path, $clean_params);
}
/**
 * Get Bestselling Videos
 * Endpoint: GET /analytics/202511/videos/bestselling
 */
public function get_bestselling_videos($params = []) {
    $path = "/analytics/202511/videos/bestselling";
    
    // 🔥 Gunakan tanggal maksimal yang diketahui valid
    $latest_available_date = '2026-05-17';
    
    // Bersihkan parameter
    $clean_params = [];
    
    if (isset($params['time_slot'])) {
        $clean_params['time_slot'] = $params['time_slot'];
    } else {
        $clean_params['time_slot'] = '7D';
    }
    
    // Untuk custom date range
    if (isset($params['start_date']) && isset($params['end_date'])) {
        $start = $params['start_date'];
        $end = $params['end_date'];
        
        if ($end > $latest_available_date) {
            $end = $latest_available_date;
        }
        if ($start > $end) {
            $start = $end;
        }
        
        $clean_params['start_date'] = $start;
        $clean_params['end_date'] = $end;
        unset($clean_params['time_slot']);
        $clean_params['time_slot'] = 'CUSTOM';
    } else {
        // Gunakan date parameter dengan latest_available_date
        $clean_params['date'] = $latest_available_date;
    }
    
    if (isset($params['page_size'])) {
        $clean_params['page_size'] = $params['page_size'];
    } else {
        $clean_params['page_size'] = 100;
    }
    
    if (isset($params['page_token'])) {
        $clean_params['page_token'] = $params['page_token'];
    }
    
    log_message('debug', 'get_bestselling_videos params: ' . json_encode($clean_params));
    
    return $this->_api_request_analytics($path, $clean_params);
}


/**
 * Get products by review status
 * Endpoint: GET /affiliate_partner/202405/campaigns/{campaign_id}/products
 */
public function get_products_by_review_status($campaign_id, $review_status = 'PENDING', $page_size = 50) {
    $path = "/affiliate_partner/202405/campaigns/{$campaign_id}/products";
    
    $params = [
        'page_size' => $page_size,
        'review_status' => $review_status
    ];
    
    return $this->_api_request($path, $params);
}

/**
 * Search open collaboration products by shop name (langsung)
 * Endpoint: POST /affiliate_seller/202405/open_collaborations/products/search
 * 
 * @param string $shop_name Nama shop yang dicari
 * @param int $page_size Jumlah produk per page
 * @return array API response
 */
public function search_products_by_shop_name($shop_name, $page_size = 50) {
    $path = "/affiliate_seller/202405/open_collaborations/products/search";
    
    $params = ['page_size' => min($page_size, 20)]; // Max 20 sesuai dokumentasi
    
    $body = [
        'title_keywords' => [$shop_name]
    ];
    
    log_message('debug', 'Searching products by shop name: ' . $shop_name);
    
    $result = $this->_api_request_seller($path, $params, 'POST', $body);
    
    // Filter hasil berdasarkan shop.name yang exact match
    if ($result['success'] && isset($result['data']['products'])) {
        $filtered_products = [];
        foreach ($result['data']['products'] as $product) {
            $product_shop_name = $product['shop']['name'] ?? '';
            // Case-insensitive match
            if (strtolower(trim($product_shop_name)) === strtolower(trim($shop_name))) {
                $filtered_products[] = $product;
            }
        }
        $result['data']['products'] = $filtered_products;
        $result['data']['total_count'] = count($filtered_products);
    }
    
    return $result;
}
public function get_creator_products($creator_open_id, $page_size = 50) {
    $path = "/affiliate_seller/202508/marketplace_creators/{$creator_open_id}/products";
    
    $params = [
        'page_size' => min($page_size, 50)
    ];
    
    log_message('debug', 'Getting products for creator: ' . $creator_open_id);
    
    $result = $this->_api_request_seller($path, $params, 'GET');
    
    // Log untuk debugging
    if ($result['success']) {
        log_message('debug', 'Products found: ' . count($result['data']['products'] ?? []));
    } else {
        log_message('error', 'Failed to get products: ' . ($result['message'] ?? 'Unknown error'));
    }
    
    return $result;
}
/**
 * Get campaign creator fulfillment status (creator yang sudah aktifkan link)
 * Endpoint: GET /affiliate_partner/202501/campaigns/{campaign_id}/products/performance
 * 
 * @param string $campaign_id - Campaign ID
 * @param int $page_size - Jumlah data per page
 * @param string $page_token - Token untuk pagination
 * @return array API response
 */
public function get_campaign_creator_fulfillment($campaign_id, $page_size = 50, $page_token = null) {
    $path = "/affiliate_partner/202501/campaigns/{$campaign_id}/products/performance";
    
    $params = [
        'page_size' => $page_size
    ];
    
    if ($page_token) {
        $params['page_token'] = $page_token;
    }
    
    log_message('debug', '=== get_campaign_creator_fulfillment ===');
    log_message('debug', 'Path: ' . $path);
    log_message('debug', 'Params: ' . json_encode($params));
    
    return $this->_api_request($path, $params);
}

/**
 * Get campaign product detail with creator performance
 * Endpoint: GET /affiliate_partner/202508/campaigns/{campaign_id}/products/{product_id}/performance
 * 
 * @param string $campaign_id - Campaign ID
 * @param string $product_id - Product ID
 * @param int $page_size - Jumlah data per page
 * @param string $page_token - Token untuk pagination
 * @return array API response
 */
public function get_campaign_product_creator_performance($campaign_id, $product_id, $page_size = 50, $page_token = null) {
    $path = "/affiliate_partner/202508/campaigns/{$campaign_id}/products/{$product_id}/performance";
    
    $params = [
        'page_size' => $page_size
    ];
    
    if ($page_token) {
        $params['page_token'] = $page_token;
    }
    
    log_message('debug', '=== get_campaign_product_creator_performance ===');
    log_message('debug', 'Path: ' . $path);
    log_message('debug', 'Params: ' . json_encode($params));
    
    return $this->_api_request($path, $params);
}


/**
 * Get creator content statistics
 * Endpoint: GET /affiliate_partner/202508/campaigns/{campaign_id}/products/{product_id}/creator/{creator_temp_id}/content/statistics
 */
public function get_creator_content_statistics($campaign_id, $product_id, $creator_temp_id, $content_type = 'VIDEO') {
    $path = "/affiliate_partner/202508/campaigns/{$campaign_id}/products/{$product_id}/creator/{$creator_temp_id}/content/statistics";
    
    // 🔥 WAJIB: Parameter affiliate_product_id sama dengan product_id
    $params = [
        'affiliate_product_id' => $product_id,  // ← KUNCI! Parameter wajib
        'content_type' => $content_type
    ];
    
    log_message('debug', '=== get_creator_content_statistics ===');
    log_message('debug', 'Path: ' . $path);
    log_message('debug', 'Params: ' . json_encode($params));
    
    $result = $this->_api_request($path, $params);
    
    log_message('debug', 'Response: ' . json_encode($result));
    
    return $result;
}
public function get_all_creator_content_statistics($campaign_id) {
    $results = [];
    
    // Get all products in campaign
    $products_result = $this->get_products_by_review_status($campaign_id, 'APPROVED', 100);
    
    if (!$products_result['success'] || empty($products_result['data']['products'])) {
        return $results;
    }
    
    foreach ($products_result['data']['products'] as $product) {
        $product_id = $product['id'];
        
        // Get creators who have links for this product
        $creators = $this->db->select('creator_hid, creator_id, creator_username')
                             ->from('affiliate_creator_links')
                             ->where('campaign_id', $campaign_id)
                             ->where('product_id', $product_id)
                             ->where('status', 'ACTIVE')
                             ->get()
                             ->result();
        
        foreach ($creators as $creator) {
            if (!empty($creator->creator_hid)) {
                $stats = $this->get_creator_content_statistics($campaign_id, $product_id, $creator->creator_hid);
                
                if ($stats['success'] && !empty($stats['data']['creator_content_statistics'])) {
                    foreach ($stats['data']['creator_content_statistics'] as $stat) {
                        $results[] = [
                            'campaign_id' => $campaign_id,
                            'product_id' => $product_id,
                            'creator_id' => $creator->creator_id,
                            'creator_username' => $creator->creator_username,
                            'creator_temp_id' => $creator->creator_hid,
                            'stat' => $stat
                        ];
                    }
                }
            }
        }
    }
    
    return $results;
}
/**
 * Get campaign creator fulfillment status (creator yang sudah aktifkan link)
 * Endpoint: GET /affiliate_partner/202508/campaigns/{campaign_id}/products/{product_id}/performance
 * 
 * @param string $campaign_id - Campaign ID
 * @param string $product_id - Product ID (affiliate_product_id)
 * @param int $page_size - Jumlah data per page
 * @param string $page_token - Token untuk pagination
 * @return array API response
 */
public function get_campaign_creator_performance($campaign_id, $product_id, $page_size = 50, $page_token = null) {
    $path = "/affiliate_partner/202508/campaigns/{$campaign_id}/products/{$product_id}/performance";
    
    $params = [
        'page_size' => $page_size
    ];
    
    if ($page_token) {
        $params['page_token'] = $page_token;
    }
    
    log_message('debug', '=== get_campaign_creator_performance ===');
    log_message('debug', 'Path: ' . $path);
    log_message('debug', 'Params: ' . json_encode($params));
    
    return $this->_api_request($path, $params);
}

/**
 * Get all creators who have activated links for a campaign
 * Loop through all products and pagination
 */
public function get_all_activated_creators($campaign_id) {
    $all_creators = [];
    $products_result = $this->get_products_by_review_status($campaign_id, 'APPROVED', 100);
    
    if (!$products_result['success'] || empty($products_result['data']['products'])) {
        log_message('error', 'No products found for campaign: ' . $campaign_id);
        return $all_creators;
    }
    
    foreach ($products_result['data']['products'] as $product) {
        $product_id = $product['id'];
        $page_token = null;
        $page = 1;
        
        do {
            $result = $this->get_campaign_creator_performance($campaign_id, $product_id, 100, $page_token);
            
            if (!$result['success']) {
                log_message('error', 'Failed to get performance for product: ' . $product_id . ' - ' . ($result['message'] ?? 'Unknown error'));
                break;
            }
            
            if (!empty($result['data']['promotion_creators'])) {
                foreach ($result['data']['promotion_creators'] as $creator) {
                    // Simpan data creator yang aktif
                    $all_creators[] = [
                        'campaign_id' => $campaign_id,
                        'product_id' => $product_id,
                        'product_name' => $product['name'] ?? '',
                        'affiliate_product_id' => $creator['affiliate_product_id'] ?? $product_id,
                        'creator_open_id' => $creator['creator']['creator_open_id'] ?? '',
                        'creator_nick_name' => $creator['creator']['nick_name'] ?? '',
                        'creator_username' => $creator['creator']['user_name'] ?? '',
                        'creator_avatar' => $creator['creator']['avatar_url'] ?? '',
                        'follower_count' => $creator['creator']['follower_num'] ?? 0,
                        'commission' => $creator['commission'] ?? 0,
                        'paid_amount' => $creator['paid_amount']['amount'] ?? 0,
                        'video_count' => $creator['video_count'] ?? 0,
                        'room_count' => $creator['room_count'] ?? 0,
                        'free_sample_status' => $creator['free_sample_status'] ?? '',
                        'effective_start_time' => isset($creator['effective_start_time']) ? date('Y-m-d H:i:s', intval($creator['effective_start_time']) / 1000) : null,
                        'effective_end_time' => isset($creator['effective_end_time']) ? date('Y-m-d H:i:s', intval($creator['effective_end_time']) / 1000) : null
                    ];
                }
            }
            
            $page_token = $result['data']['next_page_token'] ?? null;
            $page++;
            
            if ($page_token) {
                usleep(100000); // Delay 0.1 detik
            }
            
        } while ($page_token && $page <= 100);
    }
    
    return $all_creators;
}

/**
 * Get shop detail (untuk mendapatkan shop_cipher yang valid)
 * Endpoint: GET /seller/202309/shops
 */
public function get_shop_detail()
{
    $path = "/authorization/202309/shops";
    
    try {
        // Gunakan access token yang ada (bisa dari affiliate atau seller)
        $access_token = $this->get_valid_seller_token(); // Gunakan affiliate token
        $timestamp = time();
        
        $query = [
            'app_key' => $this->app_key,
            'timestamp' => $timestamp
        ];
        
        ksort($query);
        
        $param_string = '';
        foreach ($query as $key => $value) {
            $param_string .= $key . $value;
        }
        
        $string_to_sign = $this->app_secret . $path . $param_string . $this->app_secret;
        $query['sign'] = hash_hmac('sha256', $string_to_sign, $this->app_secret);
        
        $url = $this->openapi_base . $path . '?' . http_build_query($query);
        
        log_message('debug', 'Get Authorized Shops URL: ' . $url);
        
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
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            log_message('error', 'cURL Error: ' . $error);
            return ['success' => false, 'message' => "cURL Error: {$error}"];
        }
        
        curl_close($ch);
        
        log_message('debug', 'Get Authorized Shops Response: ' . substr($response, 0, 1000));
        
        $decoded = json_decode($response, true);
        
        if (!$decoded) {
            return ['success' => false, 'message' => 'Invalid JSON response'];
        }
        
        if (!isset($decoded['code']) || $decoded['code'] != 0) {
            return [
                'success' => false,
                'message' => $decoded['message'] ?? 'Unknown API Error',
                'code' => $decoded['code'] ?? 'unknown'
            ];
        }
        
        return [
            'success' => true,
            'data' => $decoded['data'] ?? [],
            'http_code' => $http_code
        ];
        
    } catch (Exception $e) {
        log_message('error', 'Get Authorized Shops Exception: ' . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get product categories from TikTok API
 * Endpoint: GET /product/202309/categories
 */
public function get_product_categories($params = []) {
    $path = "/product/202309/categories";
    
    try {
        // Gunakan SELLER TOKEN
        $access_token = $this->get_valid_seller_token();
        
        $timestamp = time();
        
        // AMBIL SHOP_CIPHER dari database
        $seller_token = $this->CI->Jsm_token_model->get_latest_token_by_type(2);
        $shop_cipher = $seller_token->shop_id ?? $this->default_cipher;
        
        $query = [
            'app_key' => $this->app_key,
            'timestamp' => $timestamp,
            'shop_cipher' => $shop_cipher
        ];
        
        // Parameter opsional
        $allowed_params = ['locale', 'keyword', 'category_version', 'listing_platform', 'include_prohibited_categories'];
        foreach ($params as $key => $value) {
            if (in_array($key, $allowed_params) && !empty($value)) {
                $query[$key] = $value;
            }
        }
        
        // Default values
        if (!isset($query['locale'])) {
            $query['locale'] = 'en-US';
        }
        if (!isset($query['category_version'])) {
            $query['category_version'] = 'v1';
        }
        if (!isset($query['listing_platform'])) {
            $query['listing_platform'] = 'TIKTOK_SHOP';
        }
        
        ksort($query);
        
        $param_string = '';
        foreach ($query as $key => $value) {
            $param_string .= $key . $value;
        }
        
        $string_to_sign = $this->app_secret . $path . $param_string . $this->app_secret;
        $query['sign'] = hash_hmac('sha256', $string_to_sign, $this->app_secret);
        
        $url = $this->openapi_base . $path . '?' . http_build_query($query);
        
        log_message('debug', 'Get Categories URL: ' . preg_replace('/app_secret=[^&]+/', 'app_secret=HIDDEN', $url));
        
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
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            log_message('error', 'cURL Error: ' . $error);
            return ['success' => false, 'message' => "cURL Error: {$error}"];
        }
        
        curl_close($ch);
        
        log_message('debug', 'Get Categories Response: ' . substr($response, 0, 500));
        
        $decoded = json_decode($response, true);
        
        if (!$decoded) {
            return ['success' => false, 'message' => 'Invalid JSON response'];
        }
        
        if (!isset($decoded['code']) || $decoded['code'] != 0) {
            return [
                'success' => false,
                'message' => $decoded['message'] ?? 'Unknown API Error',
                'code' => $decoded['code'] ?? 'unknown'
            ];
        }
        
        return [
            'success' => true,
            'data' => $decoded['data'] ?? [],
            'http_code' => $http_code
        ];
        
    } catch (Exception $e) {
        log_message('error', 'Get Categories Exception: ' . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}



/**
 * Get product performance detail
 * Endpoint: GET /analytics/202509/shop_products/{product_id}/performance
 */
public function get_product_performance($product_id, $params = []) {
    $path = "/analytics/202509/shop_products/{$product_id}/performance";
    
    try {
        $access_token = $this->get_valid_seller_token();
        $seller_token = $this->CI->Jsm_token_model->get_latest_token_by_type(2);
        $shop_cipher = $seller_token->shop_id ?? $this->default_cipher;
        
        $timestamp = time();
        
        $query = [
            'app_key' => $this->app_key,
            'timestamp' => $timestamp,
            'shop_cipher' => $shop_cipher
        ];
        
        // Parameter yang didukung
        $allowed_params = ['start_date_ge', 'end_date_lt', 'granularity', 'currency'];
        foreach ($params as $key => $value) {
            if (in_array($key, $allowed_params) && !empty($value)) {
                $query[$key] = $value;
            }
        }
        
        // Default values
        if (!isset($query['granularity'])) {
            $query['granularity'] = 'ALL';
        }
        if (!isset($query['currency'])) {
            $query['currency'] = 'IDR';
        }
        
        ksort($query);
        
        $param_string = '';
        foreach ($query as $key => $value) {
            $param_string .= $key . $value;
        }
        
        $string_to_sign = $this->app_secret . $path . $param_string . $this->app_secret;
        $query['sign'] = hash_hmac('sha256', $string_to_sign, $this->app_secret);
        
        $url = $this->openapi_base . $path . '?' . http_build_query($query);
        
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
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['success' => false, 'message' => "cURL Error: {$error}"];
        }
        
        curl_close($ch);
        
        $decoded = json_decode($response, true);
        
        if (!$decoded) {
            return ['success' => false, 'message' => 'Invalid JSON response'];
        }
        
        if (!isset($decoded['code']) || $decoded['code'] != 0) {
            return [
                'success' => false,
                'message' => $decoded['message'] ?? 'Unknown API Error',
                'code' => $decoded['code'] ?? 'unknown'
            ];
        }
        
        return [
            'success' => true,
            'data' => $decoded['data'] ?? [],
            'http_code' => $http_code
        ];
        
    } catch (Exception $e) {
        log_message('error', 'Get Product Performance Exception: ' . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}


public function get_product_performance_2($product_id, $params = []) {
    $path = "/analytics/202509/shop_products/{$product_id}/performance";
    
    try {
        // 🔥 VALIDASI SEMUA PARAMETER WAJIB
        $required_params = ['start_date_ge', 'end_date_lt', 'granularity', 'currency'];
        $missing = [];
        foreach ($required_params as $param) {
            if (empty($params[$param])) {
                $missing[] = $param;
            }
        }
        
        if (!empty($missing)) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: ' . implode(', ', $missing),
                'code' => 'MISSING_PARAMETER'
            ];
        }
        
        // 🔥 Gunakan SELLER TOKEN
        $access_token = $this->get_valid_seller_token();
        
        // 🔥 AMBIL SHOP_CIPHER dari database
        $seller_token = $this->CI->Jsm_token_model->get_latest_token_by_type(2);
        $shop_cipher = $seller_token->shop_id ?? $this->default_cipher;
        
        if (empty($shop_cipher)) {
            return [
                'success' => false,
                'message' => 'No valid shop cipher found. Please authorize as seller first.'
            ];
        }
        
        $timestamp = time();
        
        // 🔥 BUILD QUERY PARAMETERS - SEMUA WAJIB
        $query = [
            'app_key' => $this->app_key,
            'timestamp' => $timestamp,
            'shop_cipher' => $shop_cipher,
            'start_date_ge' => $params['start_date_ge'],
            'end_date_lt' => $params['end_date_lt'],
            'granularity' => $params['granularity'],
            'currency' => $params['currency']
        ];
        
        // 🔥 SIGNATURE
        ksort($query);
        
        $param_string = '';
        foreach ($query as $key => $value) {
            $param_string .= $key . $value;
        }
        
        $string_to_sign = $this->app_secret . $path . $param_string . $this->app_secret;
        $query['sign'] = hash_hmac('sha256', $string_to_sign, $this->app_secret);
        
        $url = $this->openapi_base . $path . '?' . http_build_query($query);
        
        log_message('debug', 'Product Performance URL: ' . preg_replace('/app_secret=[^&]+/', 'app_secret=HIDDEN', $url));
        
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
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['success' => false, 'message' => "cURL Error: {$error}"];
        }
        
        curl_close($ch);
        
        $decoded = json_decode($response, true);
        
        if (!$decoded) {
            return ['success' => false, 'message' => 'Invalid JSON response'];
        }
        
        if (!isset($decoded['code']) || $decoded['code'] != 0) {
            return [
                'success' => false,
                'message' => $decoded['message'] ?? 'Unknown API Error',
                'code' => $decoded['code'] ?? 'unknown',
                'raw_response' => $decoded
            ];
        }
        
        return [
            'success' => true,
            'data' => $decoded['data'] ?? [],
            'latest_available_date' => $decoded['data']['latest_available_date'] ?? null,
            'http_code' => $http_code
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
/**
 * Get creator detail by user ID
 * Endpoint: GET /affiliate_seller/202508/marketplace_creators/{creator_user_id}
 */
public function get_creator_detail_by_id($creator_open_id) {
    // 🔥 Gunakan endpoint versi 202509
    $path = "/affiliate_seller/202509/marketplace_creators/{$creator_open_id}";
    
    try {
        $access_token = $this->get_valid_seller_token();
        
        $seller_token = $this->CI->Jsm_token_model->get_latest_token_by_type(2);
        $shop_cipher = $seller_token->shop_id ?? $this->default_cipher;
        
        $timestamp = time();
        
        $query = [
            'app_key' => $this->app_key,
            'timestamp' => $timestamp,
            'shop_cipher' => $shop_cipher
        ];
        
        ksort($query);
        
        $param_string = '';
        foreach ($query as $key => $value) {
            $param_string .= $key . $value;
        }
        
        $string_to_sign = $this->app_secret . $path . $param_string . $this->app_secret;
        $query['sign'] = hash_hmac('sha256', $string_to_sign, $this->app_secret);
        
        $url = $this->openapi_base . $path . '?' . http_build_query($query);
        
        log_message('debug', 'Get Creator Detail URL (v202509): ' . $url);
        
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
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['success' => false, 'message' => "cURL Error: {$error}"];
        }
        
        curl_close($ch);
        
        $decoded = json_decode($response, true);
        
        if (!$decoded) {
            return ['success' => false, 'message' => 'Invalid JSON response'];
        }
        
        if (!isset($decoded['code']) || $decoded['code'] != 0) {
            return [
                'success' => false,
                'message' => $decoded['message'] ?? 'Unknown API Error',
                'code' => $decoded['code'] ?? 'unknown'
            ];
        }
        
        return [
            'success' => true,
            'data' => $decoded['data'] ?? [],
            'http_code' => $http_code
        ];
        
    } catch (Exception $e) {
        log_message('error', 'Get Creator Detail Exception: ' . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
public function get_global_product_detail($global_product_id) {
    $path = "/product/202309/global_products/{$global_product_id}";
    
    try {
        $access_token = $this->get_valid_seller_token();
        
        $timestamp = time();
        
        // 🔥 PERBAIKAN: HAPUS shop_cipher dari query parameter
        // Endpoint ini tidak memerlukan shop_cipher
        $query = [
            'app_key' => $this->app_key,
            'timestamp' => $timestamp
        ];
        
        // Optional parameters
        $query['locale'] = 'id-ID';  // Set ke bahasa Indonesia
        
        ksort($query);
        
        $param_string = '';
        foreach ($query as $key => $value) {
            $param_string .= $key . $value;
        }
        
        $string_to_sign = $this->app_secret . $path . $param_string . $this->app_secret;
        $query['sign'] = hash_hmac('sha256', $string_to_sign, $this->app_secret);
        
        $url = $this->openapi_base . $path . '?' . http_build_query($query);
        
        log_message('debug', 'Get Global Product URL: ' . preg_replace('/app_secret=[^&]+/', 'app_secret=HIDDEN', $url));
        
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
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            log_message('error', 'cURL Error: ' . $error);
            return ['success' => false, 'message' => "cURL Error: {$error}"];
        }
        
        curl_close($ch);
        
        log_message('debug', 'Get Global Product Response HTTP: ' . $http_code);
        log_message('debug', 'Get Global Product Response: ' . substr($response, 0, 500));
        
        $decoded = json_decode($response, true);
        
        if (!$decoded) {
            return ['success' => false, 'message' => 'Invalid JSON response'];
        }
        
        if (!isset($decoded['code']) || $decoded['code'] != 0) {
            return [
                'success' => false,
                'message' => $decoded['message'] ?? 'Unknown API Error',
                'code' => $decoded['code'] ?? 'unknown'
            ];
        }
        
        return [
            'success' => true,
            'data' => $decoded['data'] ?? [],
            'http_code' => $http_code
        ];
        
    } catch (Exception $e) {
        log_message('error', 'Get Global Product Detail Exception: ' . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get video performance detail
 * Endpoint: GET /analytics/202509/shop_videos/{video_id}/performance
 */
public function get_video_performance($video_id, $params = []) {
    $path = "/analytics/202509/shop_videos/{$video_id}/performance";
    
    try {
        $access_token = $this->get_valid_seller_token();
        $seller_token = $this->CI->Jsm_token_model->get_latest_token_by_type(2);
        $shop_cipher = $seller_token->shop_id ?? $this->default_cipher;
        
        $timestamp = time();
        
        $query = [
            'app_key' => $this->app_key,
            'timestamp' => $timestamp,
            'shop_cipher' => $shop_cipher
        ];
        
        $allowed_params = ['start_date_ge', 'end_date_lt', 'granularity', 'currency'];
        foreach ($params as $key => $value) {
            if (in_array($key, $allowed_params) && !empty($value)) {
                $query[$key] = $value;
            }
        }
        
        if (!isset($query['granularity'])) {
            $query['granularity'] = 'ALL';
        }
        if (!isset($query['currency'])) {
            $query['currency'] = 'IDR';
        }
        
        ksort($query);
        
        $param_string = '';
        foreach ($query as $key => $value) {
            $param_string .= $key . $value;
        }
        
        $string_to_sign = $this->app_secret . $path . $param_string . $this->app_secret;
        $query['sign'] = hash_hmac('sha256', $string_to_sign, $this->app_secret);
        
        $url = $this->openapi_base . $path . '?' . http_build_query($query);
        
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
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['success' => false, 'message' => "cURL Error: {$error}"];
        }
        
        curl_close($ch);
        
        $decoded = json_decode($response, true);
        
        if (!$decoded) {
            return ['success' => false, 'message' => 'Invalid JSON response'];
        }
        
        if (!isset($decoded['code']) || $decoded['code'] != 0) {
            return [
                'success' => false,
                'message' => $decoded['message'] ?? 'Unknown API Error',
                'code' => $decoded['code'] ?? 'unknown'
            ];
        }
        
        return [
            'success' => true,
            'data' => $decoded['data'] ?? [],
            'http_code' => $http_code
        ];
        
    } catch (Exception $e) {
        log_message('error', 'Get Video Performance Exception: ' . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}







public function search_marketplace_creators($filters = [], $page_size = 20, $page_token = null) {
     $path = "/affiliate_seller/202508/marketplace_creators/search";
    
    $params = ['page_size' => $page_size];
    if ($page_token) {
        $params['page_token'] = $page_token;
    }
    
    $body = [];
    
    // Filter yang didukung
    $allowed_filters = ['keyword', 'category_ids', 'min_follower_count', 'max_follower_count', 
                        'min_gmv', 'max_gmv', 'country_codes', 'creator_languages'];
    
    foreach ($filters as $key => $value) {
        if (in_array($key, $allowed_filters) && !empty($value)) {
            $body[$key] = $value;
        }
    }
    
    // 🔥 COBA: Tambahkan flag untuk include contact info (jika didukung)
    $body['include_contact_info'] = true;
    
    // 🔥 COBA: Tambahkan filter untuk hanya creator yang menerima kontak
    $body['only_contactable'] = true;
    
    log_message('debug', 'Search with contact: ' . json_encode($body));
    
    return $this->_api_request_seller($path, $params, 'POST', $body);
}
public function get_marketplace_creator_detail($creator_open_id) {
    $path = "/affiliate_seller/202508/marketplace_creators/{$creator_open_id}";
    $result = $this->_api_request_seller($path, [], 'GET');
    
    if ($result['success']) {
        $creator = $result['data']['creator'] ?? [];
        return [
            'success' => true,
            'data' => [
                'gmv' => floatval($creator['gmv']['amount'] ?? 0),
                'video_gmv' => floatval($creator['video_gmv']['amount'] ?? 0),
                'live_gmv' => floatval($creator['live_gmv']['amount'] ?? 0),
                'units_sold' => intval($creator['units_sold'] ?? 0),
                'gpm' => floatval($creator['gpm']['amount'] ?? 0),
                'avg_commission_rate' => intval($creator['avg_commission_rate'] ?? 0),
                'ec_video_count' => intval($creator['ec_video_count'] ?? 0),
                'ec_live_count' => intval($creator['ec_live_count'] ?? 0),
                'follower_count' => intval($creator['follower_count'] ?? 0),
                'username' => $creator['username'] ?? '',
                'nickname' => $creator['nickname'] ?? '',
                'avatar_url' => $creator['avatar']['url'] ?? '',
                'bio' => $creator['bio_description'] ?? ''
            ]
        ];
    }
    
    return $result;
}



public function search_seller_open_products($shop_name, $page_size = 100, $page_token = null) {
    $path = "/affiliate_seller/202405/open_collaborations/products/search";
    
       
    if ($page_size > 20) {
        $page_size = 100;
    }
    if ($page_size < 1) {
        $page_size = 100;
    }
    
    $params = [
        'page_size' => $page_size
    ];
    
    if ($page_token) {
        $params['page_token'] = $page_token;
    }
    
    
    $body = [
        'shop_name_keyword' => $shop_name 
    ];
    
    log_message('debug', 'Searching open collaboration products for shop: ' . $shop_name);
    log_message('debug', 'Params: ' . json_encode($params));
    log_message('debug', 'Body: ' . json_encode($body));
    
    return $this->_api_request_seller($path, $params, 'POST', $body);
}

public function get_shop_by_username($username, $page_size = 100) {
    $path = "/affiliate_seller/202405/shops/search";
    
    $params = ['page_size' => $page_size];
    
    $body = [
        'shop_name_keyword' => $username
    ];
    
    log_message('debug', 'Searching shop by username: ' . $username);
    
    return $this->_api_request_seller($path, $params, 'POST', $body);
}
public function get_seller_with_products($creator_open_id) {
    $path = "/affiliate_seller/202508/marketplace_creators/{$creator_open_id}";
    
    // 🔥 Parameter untuk include products
    $params = [
        'include_products' => true,
        'product_page_size' => 100
    ];
    
    return $this->_api_request_seller($path, $params, 'GET');
}

/**
 * Get marketplace creator detail WITH products
 * Endpoint: GET /affiliate_seller/202508/marketplace_creators/{creator_open_id}
 */
public function get_marketplace_creator_detail_with_products($creator_open_id, $product_page_size = 100) {
    $path = "/affiliate_seller/202508/marketplace_creators/{$creator_open_id}";
    
    $params = [
        'include_products' => true,
        'product_page_size' => min($product_page_size, 100)
    ];
    
    log_message('debug', 'Getting creator detail with products: ' . $creator_open_id);
    
    return $this->_api_request_seller($path, $params, 'GET');
}
/**
 * Search Open Collaboration products from specific seller (API Version 202412)
 * Endpoint: POST /affiliate_seller/202412/open_collaborations/search
 * 
 * @param string $shop_name Nama shop/seller yang dicari
 * @param int $page_size Jumlah produk per page (max 50)
 * @param string $page_token Token untuk pagination
 * @return array API response
 */
public function search_open_collaborations_by_product($product_id) {
    $path = "/affiliate_seller/202412/open_collaborations/search";
    
    $params = [
        'page_size' => 10
    ];
    
    $body = [
        'keyword' => $product_id,
        'keyword_type' => 'PRODUCT_ID'  // 🔥 Mencari berdasarkan PRODUCT_ID
    ];
    
    log_message('debug', 'Searching open collaborations for product_id: ' . $product_id);
    log_message('debug', 'Body: ' . json_encode($body));
    
    return $this->_api_request_seller($path, $params, 'POST', $body);
}

/**
 * Search Open Collaboration Products directly (API Version 202405)
 * Endpoint: POST /affiliate_seller/202405/open_collaborations/products/search
 * 
 * @param string $shop_name Nama shop yang dicari
 * @param int $page_size Jumlah produk per page (max 20)
 * @param string $page_token Token untuk pagination
 * @return array API response
 */
public function search_open_collaboration_products($shop_name, $page_size = 20, $page_token = null) {
    $path = "/affiliate_seller/202405/open_collaborations/products/search";
    
    if ($page_size > 20) {
        $page_size = 20;
    }
    if ($page_size < 1) {
        $page_size = 20;
    }
    
    $params = [
        'page_size' => $page_size
    ];
    
    if ($page_token) {
        $params['page_token'] = $page_token;
    }
    
    $body = [
        'title_keywords' => [$shop_name]
    ];
    
    log_message('debug', 'Searching open collaboration products for shop: ' . $shop_name);
    log_message('debug', 'Params: ' . json_encode($params));
    log_message('debug', 'Body: ' . json_encode($body));
    
    return $this->_api_request_seller($path, $params, 'POST', $body);
}
/**
 * Get target collaboration detail (termasuk contact info seller)
 * Endpoint: GET /affiliate_seller/202508/target_collaborations/{target_collaboration_id}
 * 
 * @param string $target_collaboration_id ID dari target collaboration
 * @return array API response
 */
public function get_target_collaboration_detail($target_collaboration_id) {
    $path = "/affiliate_seller/202508/target_collaborations/{$target_collaboration_id}";
    
    log_message('debug', 'Getting target collaboration detail: ' . $target_collaboration_id);
    
    return $this->_api_request_seller($path, [], 'GET');
}

/**
 * Get seller contact info from target collaboration
 * 
 * @param string $target_collaboration_id ID dari target collaboration
 * @return array Contact info (email, phone_number, whatsapp, telegram, line)
 */
public function get_seller_contact_info($target_collaboration_id) {
    $result = $this->get_target_collaboration_detail($target_collaboration_id);
    
    if ($result['success'] && isset($result['data']['target_collaboration']['seller_contact_info'])) {
        return [
            'success' => true,
            'contact_info' => $result['data']['target_collaboration']['seller_contact_info']
        ];
    }
    
    return [
        'success' => false,
        'message' => 'Contact info not available'
    ];
}
/**
 * Search target collaborations (untuk mendapatkan ID)
 * Endpoint: POST /affiliate_seller/202508/target_collaborations/search
 */
public function search_target_collaborations($filters = [], $page_size = 20) {
    $path = "/affiliate_seller/202508/target_collaborations/search";
    
    $params = [
        'page_size' => min($page_size, 50)
    ];
    
    if (isset($filters['page_token'])) {
        $params['page_token'] = $filters['page_token'];
    }
    
    // 🔥 BODY REQUEST SESUAI DOKUMENTASI
    $body = [];
    
    // Collaboration status: ONGOING, EXPIRED, STOPPED
    if (isset($filters['collaboration_status'])) {
        $body['collaboration_status'] = $filters['collaboration_status'];
    } else {
        $body['collaboration_status'] = 'ONGOING'; // Default ONGOING
    }
    
    // Creator accept status: ACCEPT, ALL
    if (isset($filters['creator_accept_status'])) {
        $body['creator_accept_status'] = $filters['creator_accept_status'];
    } else {
        $body['creator_accept_status'] = 'ALL'; // Default ALL
    }
    
    // Free sample setting: WITH_FREE_SAMPLE, WITHOUT_FREE_SAMPLE
    if (isset($filters['free_sample_setting'])) {
        $body['free_sample_setting'] = $filters['free_sample_setting'];
    }
    
    // Search param untuk keyword
    if (isset($filters['keyword']) && !empty($filters['keyword'])) {
        $body['search_param'] = [
            'keyword_type' => $filters['keyword_type'] ?? 'TARGET_COLLABORATION_NAME',
            'keyword' => $filters['keyword']
        ];
    }
    
    // Filter berdasarkan product_id
    if (isset($filters['product_id']) && !empty($filters['product_id'])) {
        $body['product_id'] = $filters['product_id'];
    }
    
    // Filter berdasarkan creator_open_id
    if (isset($filters['creator_open_id']) && !empty($filters['creator_open_id'])) {
        $body['creator_user_open_id'] = $filters['creator_open_id'];
    }
    
    log_message('debug', 'Searching target collaborations: ' . json_encode($body));
    
    return $this->_api_request_seller($path, $params, 'POST', $body);
}
/**
 * Get product detail by ID (lengkap dengan responsible_person_ids)
 * Endpoint: GET /product/202309/products/{product_id}
 * 
 * @param string $product_id Product ID
 * @return array API response
 */
public function get_product_detail_by_id($product_id) {
    $path = "/product/202309/products/{$product_id}";
    
    // 🔥 PERBAIKAN: Hanya pilih salah satu, tidak boleh keduanya
    // Kita pilih return_under_review_version = false (ambil versi live)
    $params = [
        'return_under_review_version' => 'false',  // Ambil versi live
        // 'return_draft_version' => 'false',     // Jangan kirim keduanya
        'locale' => 'id'
    ];
    
    log_message('debug', 'Getting product detail for product_id: ' . $product_id);
    log_message('debug', 'Params: ' . json_encode($params));
    
    return $this->_api_request_seller($path, $params, 'GET');
}
/**
 * Get seller contact info from product ID
 * 
 * @param string $product_id Product ID
 * @return array Contact info (name, email, phone_number, address)
 */
public function get_seller_contact_from_product($product_id) {
    // Step 1: Get product detail
    $product_detail = $this->get_product_detail_by_id($product_id);
    
    if (!$product_detail['success'] || empty($product_detail['data']['responsible_person_ids'])) {
        return [
            'success' => false,
            'message' => 'No responsible person found for this product'
        ];
    }
    
    $responsible_person_ids = $product_detail['data']['responsible_person_ids'];
    
    // Step 2: Search responsible persons
    $responsible_persons = $this->search_responsible_persons([
        'responsible_person_ids' => $responsible_person_ids
    ], 10);
    
    if (!$responsible_persons['success'] || empty($responsible_persons['data']['responsible_persons'])) {
        return [
            'success' => false,
            'message' => 'No responsible person details found'
        ];
    }
    
    // Step 3: Extract contact info
    $contact_info = [];
    foreach ($responsible_persons['data']['responsible_persons'] as $person) {
        foreach ($person['regional_profiles'] ?? [] as $profile) {
            $contact_info[] = [
                'id' => $person['id'],
                'name' => $profile['name'] ?? '',
                'email' => $profile['email'] ?? '',
                'phone_number' => isset($profile['phone_number']) 
                    ? ($profile['phone_number']['country_code'] ?? '') . ($profile['phone_number']['local_number'] ?? '')
                    : '',
                'address' => $profile['address'] ?? []
            ];
        }
    }
    
    return [
        'success' => true,
        'contact_info' => $contact_info,
        'responsible_person_ids' => $responsible_person_ids
    ];
}
public function search_responsible_persons($filters = [], $page_size = 20) {
    $path = "/product/202501/compliance/responsible_persons/search";
    
    $params = ['page_size' => min($page_size, 50)];
    
    $body = [];
    if (isset($filters['responsible_person_ids'])) {
        $body['responsible_person_ids'] = $filters['responsible_person_ids'];
    }
    if (isset($filters['keyword'])) {
        $body['keyword'] = $filters['keyword'];
    }
    
    return $this->_api_request_seller($path, $params, 'POST', $body);
}

////////////////////CREATOR SEARCH ////////
/**
 * Search creators on marketplace - khusus untuk IS
 */
public function search_creators_by_is($keyword, $page_token = null, $page_size = 20) {
    $path = "/affiliate_seller/202508/marketplace_creators/search";
    
    $params = [
        'page_size' => min($page_size, 50)
    ];
    
    if ($page_token && $page_token != '') {
        $params['page_token'] = $page_token;
    }
    
    // 🔥 Body request - GUNAKAN keyword langsung
    $body = [
        'keyword' => $keyword
    ];
    
    log_message('debug', 'Search marketplace creators: ' . $path);
    log_message('debug', 'Params: ' . json_encode($params));
    log_message('debug', 'Body: ' . json_encode($body));
    
    // 🔥 GUNAKAN SELLER TOKEN + SHOP CIPHER
    $result = $this->_api_request_seller($path, $params, 'POST', $body);
    
    if (!$result['success']) {
        log_message('error', 'Search creators failed: ' . json_encode($result));
        return $result;
    }
    
    // Format response
    $creators = [];
    foreach ($result['data']['creators'] ?? [] as $creator) {
        $creators[] = [
            'username' => $creator['username'] ?? '',
            'nickname' => $creator['nickname'] ?? '',
            'creator_open_id' => $creator['creator_open_id'] ?? '',
            'avatar' => [
                'url' => $creator['avatar']['url'] ?? ''
            ],
            'follower_count' => (int)($creator['follower_count'] ?? 0),
            'selection_region' => $creator['selection_region'] ?? '',
            'category_ids' => $creator['category_ids'] ?? [],
            'avg_ec_live_uv' => (int)($creator['avg_ec_live_uv'] ?? 0),
            'avg_ec_video_view_count' => (int)($creator['avg_ec_video_view_count'] ?? 0),
            'gmv' => [
                'currency' => $creator['gmv']['currency'] ?? 'USD',
                'amount' => $creator['gmv']['amount'] ?? '0'
            ],
            'gmv_range' => $creator['gmv_range'] ?? null
        ];
    }
    
    return [
        'success' => true,
        'data' => [
            'creators' => $creators,
            'next_page_token' => $result['data']['next_page_token'] ?? null,
            'total_count' => $result['data']['total_count'] ?? count($creators)
        ]
    ];
}

/**
 * Format marketplace creator data
 */
private function _format_marketplace_creator($creator) {
    return [
        'username' => $creator['username'] ?? '',
        'nickname' => $creator['nickname'] ?? '',
        'creator_open_id' => $creator['creator_open_id'] ?? '',
        
        'avatar' => [
            'url' => $creator['avatar']['url'] ?? ''
        ],
        'follower_count' => (int)($creator['follower_count'] ?? 0),
        'selection_region' => $creator['selection_region'] ?? '',
        'category_ids' => $creator['category_ids'] ?? [],
        'avg_ec_live_uv' => (int)($creator['avg_ec_live_uv'] ?? 0),
        'avg_ec_video_view_count' => (int)($creator['avg_ec_video_view_count'] ?? 0),
        'gmv' => [
            'currency' => $creator['gmv']['currency'] ?? 'USD',
            'amount' => $creator['gmv']['amount'] ?? '0'
        ],
        'gmv_range' => $creator['gmv_range'] ?? null
    ];
}

public function get_marketplace_creator_performance($creator_open_id) {
    $path = "/affiliate_seller/202508/marketplace_creators/{$creator_open_id}";
    
    $result = $this->_api_request_seller($path, [], 'GET');
    
    if (!$result['success']) {
        return $result;
    }
    
    // 🔥 AMBIL DATA CREATOR DARI RESPONSE (mungkin di dalam object 'creator')
    $creator = $result['data']['creator'] ?? $result['data'];
    
    if (empty($creator)) {
        return [
            'success' => false,
            'message' => 'Creator data not found in response'
        ];
    }
    
    // 🔥 PASTIKAN SEMUA FIELD YANG DIPERLUKAN ADA
    $formatted = [
        // Basic Info
        'username' => $creator['username'] ?? '',
        'nickname' => $creator['nickname'] ?? '',
        'avatar_url' => $creator['avatar']['url'] ?? '',
        'bio' => $creator['bio_description'] ?? '',
        'follower_count' => intval($creator['follower_count'] ?? 0),
        'selection_region' => $creator['selection_region'] ?? '',
        
        // GMV Fields (dikonversi dari object ke float)
        'gmv' => floatval($creator['gmv']['amount'] ?? 0),
        'gmv_currency' => $creator['gmv']['currency'] ?? 'USD',
        'video_gmv' => floatval($creator['video_gmv']['amount'] ?? 0),
        'live_gmv' => floatval($creator['live_gmv']['amount'] ?? 0),
        'gpm' => floatval($creator['gpm']['amount'] ?? 0),
        'video_gpm' => floatval($creator['video_gpm']['amount'] ?? 0),
        'live_gpm' => floatval($creator['live_gpm']['amount'] ?? 0),
        
        // Sales & Collaboration - 🔥 YANG PENTING
        'units_sold' => intval($creator['units_sold'] ?? 0),
        'brand_collaboration_count' => intval($creator['brand_collaboration_count'] ?? 0),
        'promoted_product_num' => intval($creator['promoted_product_num'] ?? 0),
        'top_collaborated_brand_ids' => $creator['top_collaborated_brand_ids'] ?? [],  // 🔥 INI YANG DIPERLUKAN
        
        // Content
        'ec_video_count' => intval($creator['ec_video_count'] ?? 0),
        'ec_live_count' => intval($creator['ec_live_count'] ?? 0),
        'avg_ec_video_play_count' => intval($creator['avg_ec_video_play_count'] ?? 0),
        'avg_ec_live_view_count' => intval($creator['avg_ec_live_view_count'] ?? 0),
        
        // Engagement (konversi dari basis poin ke persen, 6000 = 60%)
        'ec_video_engagement_rate' => intval($creator['ec_video_engagement_rate'] ?? 0) / 100,
        'ec_live_engagement_rate' => intval($creator['ec_live_engagement_rate'] ?? 0) / 100,
        'avg_commission_rate' => intval($creator['avg_commission_rate'] ?? 0) / 100,
        'post_rate' => $creator['post_rate'] ?? '0',
        'rating' => $creator['rating'] ?? '0',
        'pps' => $creator['pps'] ?? '0',
        
        // Live Interaction
        'avg_ec_live_like_count' => intval($creator['avg_ec_live_like_count'] ?? 0),
        'avg_ec_live_comment_count' => intval($creator['avg_ec_live_comment_count'] ?? 0),
        'avg_ec_live_share_count' => intval($creator['avg_ec_live_share_count'] ?? 0),
        
        // Demographics
        'follower_age' => $creator['follower_age'] ?? [],
        'follower_gender' => $creator['follower_gender'] ?? [],
        'follower_location' => $creator['follower_location'] ?? [],
        
        // Distribution
        'category_gmv_distribution' => $creator['category_gmv_distribution'] ?? [],
        'content_gmv_distribution' => $creator['content_gmv_distribution'] ?? []
    ];
    
    return [
        'success' => true,
        'data' => $formatted
    ];
}

public function debug_raw_request($path, $params = [], $method = 'GET', $body = null) {
    return $this->_api_request_seller($path, $params, $method, $body);
}




/**
 * Search sellers/shops (jika endpoint tersedia)
 * Endpoint: GET /seller/202309/shops/search
 */
public function search_shops($keyword, $page_size = 20) {
    $path = "/seller/202309/shops/search";
    
    $params = [
        'page_size' => min($page_size, 50),
        'shop_name' => $keyword
    ];
    
    log_message('debug', 'Searching shops with keyword: ' . $keyword);
    
    return $this->_api_request_seller($path, $params, 'GET');
}

/**
 * Search Open Collaboration by Product ID (API Version 202412)
 * Endpoint: POST /affiliate_seller/202412/open_collaborations/search
 * 
 * @param string $product_id Product ID
 * @return array API response
 */
public function search_open_collaboration_by_product_id($product_id) {
    $path = "/affiliate_seller/202412/open_collaborations/search";
    
    $params = [
        'page_size' => 20
    ];
    
    $body = [
        'keyword' => (string)$product_id,
        'keyword_type' => 'PRODUCT_ID'
    ];
    
    log_message('debug', 'Searching open collaboration for product_id: ' . $product_id);
    log_message('debug', 'Body: ' . json_encode($body));
    
    return $this->_api_request_seller($path, $params, 'POST', $body);
}



/**
 * Extract price from product data (helper method)
 */
private function _extract_price($product)
{
    if (isset($product['price'])) {
        return is_array($product['price']) ? ($product['price']['amount'] ?? 0) : (int)$product['price'];
    }
    if (isset($product['original_price']['minimum_amount'])) {
        return (int)$product['original_price']['minimum_amount'];
    }
    if (isset($product['lowest_price']['amount'])) {
        return (int)$product['lowest_price']['amount'];
    }
    return 0;
}
/**
 * Search Open Collaboration by Product Name (untuk mencari berdasarkan nama produk)
 * Endpoint: POST /affiliate_seller/202412/open_collaborations/search
 * 
 * @param string $product_name Nama produk
 * @return array API response
 */
public function search_open_collaboration_by_product_name($product_name) {
    $path = "/affiliate_seller/202412/open_collaborations/search";
    
    $params = [
        'page_size' => 20
    ];
    
    $body = [
        'keyword' => $product_name,
        'keyword_type' => 'PRODUCT_NAME'
    ];
    
    log_message('debug', 'Searching open collaboration for product_name: ' . $product_name);
    
    return $this->_api_request_seller($path, $params, 'POST', $body);
}


/**
 * Get Authorized Category Assets
 * API: GET /authorization/202405/category_assets
 * 
 * @return array Result with category_asset_cipher
 */
public function get_authorized_category_assets() {
    $path = "/authorization/202405/category_assets";
    
    // 🔥 Gunakan AFFILIATE PARTNER TOKEN (user_type = 3)
    $access_token = $this->get_valid_token();
    
    $timestamp = time();
    
    $query = [
        'app_key' => $this->app_key,
        'timestamp' => $timestamp
    ];
    
    ksort($query);
    
    $param_string = '';
    foreach ($query as $key => $value) {
        $param_string .= $key . $value;
    }
    
    $string_to_sign = $this->app_secret . $path . $param_string . $this->app_secret;
    $query['sign'] = hash_hmac('sha256', $string_to_sign, $this->app_secret);
    
    $url = $this->openapi_base . $path . '?' . http_build_query($query);
    
    log_message('debug', 'Get Authorized Category Assets URL: ' . $url);
    
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
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_error($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return ['success' => false, 'message' => "cURL Error: {$error}"];
    }
    
    curl_close($ch);
    
    $decoded = json_decode($response, true);
    
    log_message('debug', 'Get Authorized Category Assets Response: ' . json_encode($decoded));
    
    if (!$decoded) {
        return ['success' => false, 'message' => 'Invalid JSON response'];
    }
    
    if (!isset($decoded['code']) || $decoded['code'] != 0) {
        return [
            'success' => false,
            'message' => $decoded['message'] ?? 'Unknown API Error',
            'code' => $decoded['code'] ?? 'unknown'
        ];
    }
    
    return [
        'success' => true,
        'data' => $decoded['data'] ?? [],
        'http_code' => $http_code
    ];
}

/**
 * Search Open Collaboration by Product ID - dengan format creator list
 * Endpoint: POST /affiliate_seller/202412/open_collaborations/search
 * 
 * @param string $product_id Product ID
 * @return array API response with formatted creator list
 */
public function search_open_collab_creators($product_id) {
    $path = "/affiliate_seller/202412/open_collaborations/search";
    
    $params = [
        'page_size' => 50
    ];
    
    $body = [
        'keyword' => (string)$product_id,
        'keyword_type' => 'PRODUCT_ID'
    ];
    
    log_message('debug', 'Searching open collaboration for product_id: ' . $product_id);
    
    $result = $this->_api_request_seller($path, $params, 'POST', $body);
    
    if (!$result['success']) {
        return $result;
    }
    
    // 🔥 FORMAT CREATOR LIST
    $creators = [];
    $collaborations = $result['data']['open_collaborations'] ?? [];
    
    foreach ($collaborations as $collab) {
        // Ambil GMV dari berbagai kemungkinan struktur
        $gmv_amount = 0;
        $gmv_currency = 'IDR';
        
        if (isset($collab['gmv'])) {
            if (is_array($collab['gmv'])) {
                $gmv_amount = floatval($collab['gmv']['amount'] ?? 0);
                $gmv_currency = $collab['gmv']['currency'] ?? 'IDR';
            } else {
                $gmv_amount = floatval($collab['gmv']);
            }
        }
        
        // Ambil items_sold
        $items_sold = intval($collab['items_sold'] ?? $collab['product_sales'] ?? 0);
        
        // Ambil follower_count
        $follower_count = intval($collab['follower_count'] ?? $collab['follower_num'] ?? 0);
        
        $creators[] = [
            'creator_open_id' => $collab['creator_open_id'] ?? $collab['creator_id'] ?? '',
            'creator_username' => $collab['creator_username'] ?? $collab['user_name'] ?? $collab['handle'] ?? '',
            'creator_nickname' => $collab['creator_nickname'] ?? $collab['nick_name'] ?? $collab['nickname'] ?? '',
            'avatar_url' => $collab['avatar_url'] ?? $collab['avatar'] ?? '',
            'follower_count' => $follower_count,
            'gmv' => [
                'amount' => $gmv_amount,
                'currency' => $gmv_currency,
                'formatted' => 'Rp' . number_format($gmv_amount, 0, ',', '.')
            ],
            'items_sold' => $items_sold,
            'commission_rate' => floatval($collab['commission_rate'] ?? 0),
            'product_id' => $collab['product_id'] ?? $product_id,
            'product_name' => $collab['product_name'] ?? $collab['title'] ?? '',
            'campaign_id' => $collab['campaign_id'] ?? ''
        ];
    }
    
    // 🔥 SORT by GMV descending
    usort($creators, function($a, $b) {
        return $b['gmv']['amount'] <=> $a['gmv']['amount'];
    });
    
    return [
        'success' => true,
        'data' => [
            'product_id' => $product_id,
            'total_creators' => count($creators),
            'creators' => $creators,
            'raw_collaborations' => $collaborations
        ]
    ];
}

}