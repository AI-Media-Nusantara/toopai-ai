<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * TikTok Shop Unified Controller
 * Menggabungkan fitur TTS (baru) dan JSM_API (lama)
 */
class Tts extends CI_Controller {

    private $service_id = '7630671107655157524';  // SERVICE ID TOOPAI
    private $app_key = '6jo4rjnr8ouc9';
    private $app_secret = '8ceeae7a9ba3726cb9f2e9d831020c91fad4d99c';
    
    public function __construct() {
        parent::__construct();
        $this->load->library('Jsm_api');
        $this->load->model('Jsm_token_model');
        $this->load->helper('url');
    }

    // ========== AFFILIATE PARTNER AUTHORIZATION (PERBAIKAN) ==========
    
    /**
     * Authorize untuk Affiliate Partner (TikTok Shop Partner)
     * Menggunakan endpoint partner.tiktokshop.com
     */
    public function authorize_affiliate() {
        // Regenerasi session ID untuk keamanan
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true);
        
        $state = bin2hex(random_bytes(16));
        $this->session->set_userdata('tts_state_affiliate', $state);
        
        // Log untuk debugging
        log_message('info', '=== AUTHORIZE AFFILIATE PARTNER ===');
        log_message('info', 'State: ' . $state);
        log_message('info', 'Service ID: ' . $this->service_id);
        log_message('info', 'Session ID: ' . session_id());
        
        // ✅ ENDPOINT YANG BENAR UNTUK AFFILIATE PARTNER
        $redirect_uri = base_url('tts/callback_affiliate');
        
        // Scope untuk Affiliate Partner
        // creator.showcase.read diperlukan untuk cek produk showcase/keranjang kuning creator
        $scope = urlencode('affiliate.product.search,affiliate.order.list,affiliate.campaign.read,affiliate.creator.read,affiliate.creator.search,affiliate.sample_application,partner.tap_campaign.read,creator.showcase.read');
        
        $auth_url = "https://partner.tiktokshop.com/open/authorize?"
                  . "service_id=" . $this->service_id
                  . "&state=" . $state
                  . "&redirect_uri=" . urlencode($redirect_uri)
                  . "&scope=" . $scope;
        
        log_message('info', 'Redirect to TikTok: ' . $auth_url);
        
        redirect($auth_url);
    }
    
    /**
     * Callback untuk Affiliate Partner
     */
    public function callback_affiliate() {
        log_message('info', '=== CALLBACK AFFILIATE PARTNER ===');
        log_message('info', 'Full URL: ' . current_url());
        log_message('info', 'Query String: ' . ($_SERVER['QUERY_STRING'] ?? 'empty'));
        log_message('info', 'Session ID: ' . session_id());
        
        $code = $this->input->get('code');
        $state = $this->input->get('state');
        $session_state = $this->session->userdata('tts_state_affiliate');
        
        log_message('info', 'Code: ' . ($code ? substr($code, 0, 20) . '...' : 'empty'));
        log_message('info', 'State from URL: ' . $state);
        log_message('info', 'State from Session: ' . $session_state);
        
        if (empty($code)) {
            $this->_show_error_page('Authorization code is missing', 'affiliate');
            return;
        }
        
        // Validasi state
        if (!empty($session_state) && $state !== $session_state) {
            log_message('error', 'State mismatch! URL: ' . $state . ', Session: ' . $session_state);
            $this->_show_error_page('Invalid state parameter. Please try again.', 'affiliate');
            return;
        }
        
        try {
            // ✅ ENDPOINT TOKEN YANG BENAR UNTUK AFFILIATE PARTNER
            $redirect_uri = base_url('tts/callback_affiliate');
            
            $token_url = "https://auth.tiktok-shops.com/api/v2/token/get?" . http_build_query([
                'app_key' => $this->app_key,
                'app_secret' => $this->app_secret,
                'auth_code' => $code,
                'grant_type' => 'authorized_code',
                'redirect_uri' => $redirect_uri
            ]);
            
            log_message('info', 'Token URL: ' . preg_replace('/app_secret=[^&]+/', 'app_secret=HIDDEN', $token_url));
            
            $response = $this->_curl_get($token_url);
            $result = json_decode($response, true);
            
            log_message('info', 'Token Response: ' . json_encode($result));
            
            if (!isset($result['code']) || $result['code'] != 0) {
                throw new Exception($result['message'] ?? 'Failed to get affiliate token');
            }
            
            // Simpan token
            // Ambil scope dari response — bisa berada di data.scope atau data.auth_scope
            $scope_value = $result['data']['scope'] 
                        ?? $result['data']['auth_scope'] 
                        ?? $result['data']['scopes'] 
                        ?? '';
            // Jika scope array, ubah ke string
            if (is_array($scope_value)) {
                $scope_value = implode(',', $scope_value);
            }
            
            $token_data = [
                'shop_id' => $result['data']['shop_cipher'] ?? $result['data']['seller_id'] ?? $result['data']['open_id'] ?? 'AFFILIATE_' . time(),
                'access_token' => $result['data']['access_token'],
                'refresh_token' => $result['data']['refresh_token'],
                'access_token_expire' => time() + ($result['data']['access_token_expire_in'] ?? 7200),
                'refresh_token_expire' => time() + ($result['data']['refresh_token_expire_in'] ?? 2592000),
                'user_type' => $result['data']['user_type'] ?? 3, // Affiliate Partner
                'scope' => $scope_value,
                'tap_type' => 'TOOPAI',
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            log_message('info', 'Affiliate scope saved: ' . $scope_value);
            
            $this->Jsm_token_model->save_token($token_data);
            
            log_message('info', 'Affiliate token saved successfully. User Type: ' . $token_data['user_type']);
            
            $this->session->set_flashdata('success', 'Affiliate Partner authorization successful!');
            
            // Tampilkan success page
            $this->_show_success_page('affiliate', $token_data['user_type'], $token_data['scope']);
            
        } catch (Exception $e) {
            log_message('error', 'Callback Affiliate Error: ' . $e->getMessage());
            $this->_show_error_page('Affiliate authorization failed: ' . $e->getMessage(), 'affiliate');
        }
    }
    
    // ========== SELLER AUTHORIZATION (TIKTOK SHOP SELLER) ==========
    
    /**
     * Authorize untuk Seller (TikTok Shop Seller)
     * Menggunakan endpoint services.tiktokshop.com
     */
    public function authorize_seller() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true);
        
        $state = bin2hex(random_bytes(16));
        $this->session->set_userdata('tts_state_seller', $state);
        
        log_message('info', '=== AUTHORIZE SELLER ===');
        log_message('info', 'State: ' . $state);
        log_message('info', 'Service ID: ' . $this->service_id);
        
        $redirect_uri = base_url('tts/callback_seller');
        
        // Scope untuk Seller
            $scope = urlencode('product.list,product.create,order.list,shop.info,affiliate.sample_application,analytics.read,shop.read,product.read,affiliate.creator.read');
        
        $auth_url = "https://services.tiktokshop.com/open/authorize?"
                  . "service_id=" . $this->service_id
                  . "&state=" . $state
                  . "&redirect_uri=" . urlencode($redirect_uri)
                  . "&scope=" . $scope;
        
        log_message('info', 'Redirect to Seller Auth: ' . $auth_url);
        redirect($auth_url);
    }
    
    /**
     * Callback untuk Seller
     */
    public function callback_seller() {
    log_message('info', '=== CALLBACK SELLER ===');
    
    $code = $this->input->get('code');
    $state = $this->input->get('state');
    $session_state = $this->session->userdata('tts_state_seller');
    
    if (empty($code)) {
        $this->_show_error_page('Authorization code is missing', 'seller');
        return;
    }
    
    if ($state !== $session_state) {
        log_message('error', 'State mismatch for seller');
        $this->_show_error_page('Invalid state parameter', 'seller');
        return;
    }
    
    try {
        $redirect_uri = base_url('tts/callback_seller');
        
        $token_url = "https://auth.tiktok-shops.com/api/v2/token/get?" . http_build_query([
            'app_key' => $this->app_key,
            'app_secret' => $this->app_secret,
            'auth_code' => $code,
            'grant_type' => 'authorized_code',
            'redirect_uri' => $redirect_uri
        ]);
        
        log_message('info', 'Seller Token URL: ' . preg_replace('/app_secret=[^&]+/', 'app_secret=HIDDEN', $token_url));
        
        $response = $this->_curl_get($token_url);
        $result = json_decode($response, true);
        
        log_message('info', 'Seller Token Response (full): ' . json_encode($result));
        
        if (!isset($result['code']) || $result['code'] != 0) {
            throw new Exception($result['message'] ?? 'Failed to get seller token');
        }
        
        // 🔥 EXTRAK SHOP_CIPHER dari response
        $shop_cipher = '';
        $shop_id = '';
        
        // Coba berbagai kemungkinan field yang berisi shop_cipher
        if (isset($result['data']['shop_cipher'])) {
            $shop_cipher = $result['data']['shop_cipher'];
        } elseif (isset($result['data']['seller_id'])) {
            $shop_cipher = $result['data']['seller_id'];
        } elseif (isset($result['data']['open_id'])) {
            $shop_cipher = $result['data']['open_id'];
        } elseif (isset($result['data']['shop_id'])) {
            $shop_cipher = $result['data']['shop_id'];
        }
        
        // 🔥 JIKA TIDAK ADA, GUNAKAN DEFAULT
        if (empty($shop_cipher)) {
            log_message('warning', 'No shop_cipher found in response, using placeholder');
            $shop_cipher = 'SELLER_' . time();
        }
        
        $token_data = [
            'shop_id' => $shop_cipher,  // Simpan shop_cipher di kolom shop_id
            'access_token' => $result['data']['access_token'],
            'refresh_token' => $result['data']['refresh_token'],
            'access_token_expire' => time() + ($result['data']['access_token_expire_in'] ?? 7200),
            'refresh_token_expire' => time() + ($result['data']['refresh_token_expire_in'] ?? 2592000),
            'user_type' => 2, // Seller
            'scope' => $result['data']['scope'] ?? '',
            'tap_type' => 'TOOPAI',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Cek apakah sudah ada
        $existing = $this->db->where('user_type', 2)
                            ->where('tap_type', 'TOOPAI')
                            ->get('tts_tokens')
                            ->row();
        
        if ($existing) {
            $this->db->where('id', $existing->id)->update('tts_tokens', $token_data);
        } else {
            $token_data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('tts_tokens', $token_data);
        }
        
        log_message('info', 'Seller token saved successfully. Shop Cipher: ' . $shop_cipher);
        
        $this->session->set_flashdata('success', 'Seller authorization successful!');
        $this->_show_success_page('seller', 2, $result['data']['scope'] ?? '');
        
    } catch (Exception $e) {
        log_message('error', 'Callback Seller Error: ' . $e->getMessage());
        $this->_show_error_page('Seller authorization failed: ' . $e->getMessage(), 'seller');
    }
}
    
    // ========== CREATOR AUTHORIZATION ==========
    
    /**
     * Authorize untuk Creator (TikTok Alliance)
     */
    public function authorize_creator() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    session_regenerate_id(true);
    
    $state = bin2hex(random_bytes(16));
    $this->session->set_userdata('tts_state_creator', $state);
    
    log_message('info', '=== AUTHORIZE CREATOR (FIXED) ===');
    log_message('info', 'State generated: ' . $state);
    
    // 🔥 URL SESUAI DOKUMENTASI - TANPA redirect_uri dan region
    $auth_url = "https://shop.tiktok.com/alliance/creator/auth?"
              . "app_key=" . $this->app_key
              . "&state=" . urlencode($state);
    
    log_message('info', 'Creator Auth URL: ' . $auth_url);
    
    redirect($auth_url);
}
    
    /**
     * Callback untuk Creator
     */
    public function callback_creator() {
    log_message('info', '=== CALLBACK CREATOR ===');
    
    $code = $this->input->get('code');
    $state = $this->input->get('state');
    $session_state = $this->session->userdata('tts_state_creator');
    
    log_message('info', 'Code: ' . ($code ? substr($code, 0, 20) . '...' : 'empty'));
    log_message('info', 'State from URL: ' . $state);
    log_message('info', 'State from Session: ' . $session_state);
    
    // Validasi code
    if (empty($code)) {
        $this->_show_error_page('Authorization code is missing', 'creator');
        return;
    }
    
    // 🔥 VALIDASI STATE - WAJIB!
    if (empty($session_state) || $state !== $session_state) {
        log_message('error', 'State mismatch! URL: ' . $state . ', Session: ' . $session_state);
        $this->_show_error_page('Invalid state parameter. Please try again.', 'creator');
        return;
    }
    
    try {
        $redirect_uri = base_url('tts/callback_creator');
        
        $token_url = "https://auth.tiktok-shops.com/api/v2/token/get?" . http_build_query([
            'app_key' => $this->app_key,
            'app_secret' => $this->app_secret,
            'auth_code' => $code,
            'grant_type' => 'authorized_code',
            'redirect_uri' => $redirect_uri
        ]);
        
        $response = $this->_curl_get($token_url);
        $result = json_decode($response, true);
        
        if (!isset($result['code']) || $result['code'] != 0) {
            throw new Exception($result['message'] ?? 'Failed to get creator token');
        }
        
        // Simpan token
        $token_data = [
            'shop_id' => $result['data']['creator_id'] ?? $result['data']['open_id'] ?? 'CREATOR_' . time(),
            'access_token' => $result['data']['access_token'],
            'refresh_token' => $result['data']['refresh_token'],
            'access_token_expire' => time() + ($result['data']['access_token_expire_in'] ?? 7200),
            'refresh_token_expire' => time() + ($result['data']['refresh_token_expire_in'] ?? 2592000),
            'user_type' => 1, // Creator
            'scope' => $result['data']['scope'] ?? '',
            'tap_type' => 'TOOPAI',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->Jsm_token_model->save_token($token_data);
        
        log_message('info', 'Creator token saved successfully');
        
        $this->session->set_flashdata('success', 'Creator authorization successful!');
        $this->_show_success_page('creator', $token_data['user_type'], $token_data['scope']);
        
    } catch (Exception $e) {
        log_message('error', 'Callback Creator Error: ' . $e->getMessage());
        $this->_show_error_page('Creator authorization failed: ' . $e->getMessage(), 'creator');
    }
}
    
    // ========== STATUS PAGE ==========
    
    public function status() {
        $affiliate_token = $this->Jsm_token_model->get_latest_token_by_type(3);
        $seller_token = $this->Jsm_token_model->get_latest_token_by_type(2);
        $creator_token = $this->Jsm_token_model->get_latest_token_by_type(1);
        
        $data = [
            'title' => 'Authorization Status - Toopai',
            'affiliate_token' => $affiliate_token,
            'seller_token' => $seller_token,
            'creator_token' => $creator_token,
            'app_key' => $this->app_key,
            'service_id' => $this->service_id,
            'api_type' => 'TOOPAI'
        ];
        
        $this->load->view('templates/header', $data);
        $this->load->view('tts/status', $data);
        $this->load->view('templates/footer');
    }
    
    // ========== TOKEN MANAGEMENT ==========
    
    public function refresh_token($user_type = 3) {
        header('Content-Type: application/json');
        
        try {
            $token = $this->Jsm_token_model->get_latest_token_by_type($user_type);
            
            if (!$token) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Token not found for user type: ' . $user_type
                ]);
                return;
            }
            
            if ($token->refresh_token_expire < time()) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Refresh token expired. Please re-authorize.',
                    'authorize_url' => base_url('tts/authorize_' . $this->_get_user_type_name($user_type))
                ]);
                return;
            }
            
            $url = "https://auth.tiktok-shops.com/api/v2/token/refresh?" . http_build_query([
                "app_key" => $this->app_key,
                "app_secret" => $this->app_secret,
                "refresh_token" => $token->refresh_token,
                "grant_type" => "refresh_token"
            ]);
            
            $response = $this->_curl_get($url);
            $result = json_decode($response, true);
            
            if (!isset($result['code']) || $result['code'] != 0) {
                throw new Exception($result['message'] ?? 'Refresh failed');
            }
            
            $update_data = [
                'access_token' => $result['data']['access_token'],
                'refresh_token' => $result['data']['refresh_token'],
                'access_token_expire' => time() + ($result['data']['access_token_expire_in'] ?? 7200),
                'refresh_token_expire' => time() + ($result['data']['refresh_token_expire_in'] ?? 2592000),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->where('id', $token->id)->update('tts_tokens', $update_data);
            
            echo json_encode([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'new_expiry' => date('Y-m-d H:i:s', time() + ($result['data']['access_token_expire_in'] ?? 7200))
            ], JSON_PRETTY_PRINT);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function clear_tokens() {
        $this->Jsm_token_model->clear_all_tokens();
        $this->session->set_flashdata('success', 'All tokens cleared. Please re-authorize.');
        redirect('tts/status');
    }
    
    // ========== PRIVATE METHODS ==========
    
    private function _curl_get($url) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true
        ]);
        
        $response = curl_exec($ch);
        
        if (curl_error($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("cURL Error: " . $error);
        }
        
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code >= 400) {
            throw new Exception("HTTP Error: {$http_code}");
        }
        
        return $response;
    }
    
    private function _get_user_type_name($type) {
        $map = [1 => 'creator', 2 => 'seller', 3 => 'affiliate'];
        return $map[$type] ?? 'affiliate';
    }
    
    private function _show_error_page($message, $type) {
        $type_name = $this->_get_user_type_name($type);
        $authorize_url = base_url('tts/authorize_' . $type);
        
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Error - " . ucfirst($type_name) . " Authorization</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
                .container { max-width: 600px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .error { color: #dc3545; }
                .info { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0; }
                .btn { display: inline-block; padding: 10px 20px; margin: 10px 5px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
                .btn:hover { background: #0056b3; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h1 class='error'>❌ Authorization Failed</h1>
                <div class='info'>
                    <p><strong>Type:</strong> " . ucfirst($type_name) . "</p>
                    <p><strong>Error:</strong> " . htmlspecialchars($message) . "</p>
                </div>
                <div>
                    <a href='" . $authorize_url . "' class='btn'>Try Again</a>
                    <a href='" . base_url('tts/status') . "' class='btn' style='background:#6c757d;'>Back to Status</a>
                </div>
            </div>
        </body>
        </html>";
    }
    
    private function _show_success_page($type, $user_type, $scope) {
        $type_name = $this->_get_user_type_name($type);
        
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Success - " . ucfirst($type_name) . " Authorization</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
                .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .success { color: #28a745; }
                .info { background: #e8f5e9; padding: 15px; border-radius: 5px; margin: 15px 0; }
                .btn { display: inline-block; padding: 10px 20px; margin: 10px 5px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
                .btn:hover { background: #0056b3; }
                pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h1 class='success'>✅ Authorization Successful - " . ucfirst($type_name) . "</h1>
                
                <div class='info'>
                    <h3>Token Information:</h3>
                    <p><strong>API Type:</strong> TOOPAI</p>
                    <p><strong>User Type:</strong> <span style='font-weight:bold;color:green'>" . $user_type . " (" . ucfirst($type_name) . ")</span></p>
                    <p><strong>Service ID:</strong> " . $this->service_id . "</p>
                    <p><strong>Scope:</strong></p>
                    <pre>" . htmlspecialchars($scope) . "</pre>
                </div>
                
                <div style='margin-top: 20px;'>
                    <a href='" . base_url('tts/status') . "' class='btn'>📊 Status Dashboard</a>
                    <a href='" . base_url('creator') . "' class='btn' style='background:#28a745;'>🎨 Creator Dashboard</a>
                </div>
            </div>
        </body>
        </html>";
    }
    
    
    
    // ========== WEBHOOK HANDLERS ==========

/**
 * Webhook handler untuk menerima event dari TikTok Shop
 * Endpoint: POST /tts/webhook
 * Konfigurasi di Developer Portal: https://partner.tiktokshop.com/webhook
 */
public function webhook() {
    // Log semua request untuk debugging
    log_message('info', '=== WEBHOOK RECEIVED ===');
    log_message('info', 'Method: ' . $_SERVER['REQUEST_METHOD']);
    log_message('info', 'Headers: ' . json_encode($this->_get_headers()));
    
    // Hanya menerima POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    // Baca raw input
    $raw_input = file_get_contents('php://input');
    log_message('info', 'Raw Webhook Payload: ' . $raw_input);
    
    $data = json_decode($raw_input, true);
    
    if (!$data) {
        log_message('error', 'Invalid JSON payload');
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON']);
        return;
    }
    
    // Verifikasi signature (jika diperlukan)
    // TikTok biasanya mengirim signature di header 'x-tiktok-signature'
    $signature = $this->input->get_request_header('X-TikTok-Signature', true);
    if ($signature && !$this->_verify_webhook_signature($raw_input, $signature)) {
        log_message('error', 'Webhook signature verification failed');
        http_response_code(401);
        echo json_encode(['error' => 'Invalid signature']);
        return;
    }
    
    // Proses berdasarkan event type
    $event_type = $data['event_type'] ?? $data['type'] ?? $data['event'] ?? '';
    
    log_message('info', 'Webhook Event Type: ' . $event_type);
    
    $response = ['status' => 'success', 'message' => 'Event received'];
    
    try {
        switch ($event_type) {
            // ========== ORDER EVENTS ==========
            case 'order.create':
            case 'order.new':
                $this->_handle_order_created($data);
                break;
                
            case 'order.update':
            case 'order.status_change':
                $this->_handle_order_updated($data);
                break;
                
            case 'order.cancel':
                $this->_handle_order_cancelled($data);
                break;
                
            case 'order.paid':
                $this->_handle_order_paid($data);
                break;
                
            case 'order.ship':
            case 'order.shipped':
                $this->_handle_order_shipped($data);
                break;
                
            case 'order.delivered':
                $this->_handle_order_delivered($data);
                break;
                
            case 'order.completed':
                $this->_handle_order_completed($data);
                break;
                
            // ========== PRODUCT EVENTS ==========
            case 'product.create':
            case 'product.new':
                $this->_handle_product_created($data);
                break;
                
            case 'product.update':
                $this->_handle_product_updated($data);
                break;
                
            case 'product.delete':
                $this->_handle_product_deleted($data);
                break;
                
            case 'product.stock_update':
                $this->_handle_product_stock_update($data);
                break;
                
            // ========== AFFILIATE EVENTS ==========
            case 'affiliate.order.settlement':
                $this->_handle_affiliate_settlement($data);
                break;
                
            case 'affiliate.creator.apply':
                $this->_handle_affiliate_creator_apply($data);
                break;
                
            case 'affiliate.campaign.update':
                $this->_handle_affiliate_campaign_update($data);
                break;
                
            case 'affiliate.sample.order':
                $this->_handle_affiliate_sample_order($data);
                break;
                
            // ========== SHOP EVENTS ==========
            case 'shop.update':
                $this->_handle_shop_update($data);
                break;
                
            case 'shop.disable':
                $this->_handle_shop_disable($data);
                break;
                
            // ========== REFUND/RETURN EVENTS ==========
            case 'refund.create':
            case 'return.create':
                $this->_handle_refund_created($data);
                break;
                
            case 'refund.update':
                $this->_handle_refund_updated($data);
                break;
                
            // ========== REVIEW EVENTS ==========
            case 'review.create':
                $this->_handle_review_created($data);
                break;
                
            // ========== DEFAULT ==========
            default:
                log_message('info', 'Unhandled webhook event type: ' . $event_type);
                $this->_handle_unknown_event($data);
                break;
        }
    } catch (Exception $e) {
        log_message('error', 'Webhook processing error: ' . $e->getMessage());
        $response = ['status' => 'error', 'message' => $e->getMessage()];
    }
    
    // Selalu return 200 OK ke TikTok (jangan return error kecuali signature invalid)
    http_response_code(200);
    echo json_encode($response);
}

/**
 * Verifikasi signature webhook
 * @param string $payload Raw payload
 * @param string $signature Signature dari header
 * @return bool
 */
private function _verify_webhook_signature($payload, $signature) {
    // Cara implementasi tergantung dokumentasi TikTok
    // Biasanya: HMAC-SHA256 dengan app_secret
    $expected = hash_hmac('sha256', $payload, $this->app_secret);
    return hash_equals($expected, $signature);
}

/**
 * Mendapatkan semua headers
 */
private function _get_headers() {
    $headers = [];
    foreach ($_SERVER as $name => $value) {
        if (substr($name, 0, 5) == 'HTTP_') {
            $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
        }
    }
    return $headers;
}

// ========== WEBHOOK EVENT HANDLERS ==========

/**
 * Handle order created event
 */
private function _handle_order_created($data) {
    log_message('info', 'Processing order created: ' . json_encode($data));
    
    $order_id = $data['order_id'] ?? $data['data']['order_id'] ?? '';
    $shop_id = $data['shop_id'] ?? $data['data']['shop_cipher'] ?? '';
    
    if ($order_id) {
        // Simpan ke database order_log
        $this->_save_webhook_log('order_created', $order_id, $data);
        
        // Bisa trigger notifikasi ke admin
        // $this->_send_webhook_notification('New Order #' . $order_id, $data);
        
        // Bisa panggil API untuk mengambil detail order
        // $this->_fetch_order_detail($order_id);
    }
}

/**
 * Handle order updated event
 */
private function _handle_order_updated($data) {
    log_message('info', 'Processing order updated: ' . json_encode($data));
    
    $order_id = $data['order_id'] ?? $data['data']['order_id'] ?? '';
    $status = $data['status'] ?? $data['data']['order_status'] ?? '';
    
    if ($order_id) {
        $this->_save_webhook_log('order_updated', $order_id, $data);
        
        // Update status order di database lokal
        $this->db->where('order_id', $order_id)->update('local_orders', [
            'order_status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}

/**
 * Handle order cancelled event
 */
private function _handle_order_cancelled($data) {
    log_message('info', 'Processing order cancelled: ' . json_encode($data));
    
    $order_id = $data['order_id'] ?? $data['data']['order_id'] ?? '';
    $cancel_reason = $data['cancel_reason'] ?? $data['data']['cancel_reason'] ?? '';
    
    if ($order_id) {
        $this->_save_webhook_log('order_cancelled', $order_id, $data);
        
        $this->db->where('order_id', $order_id)->update('local_orders', [
            'order_status' => 'cancelled',
            'cancel_reason' => $cancel_reason,
            'cancelled_at' => date('Y-m-d H:i:s')
        ]);
    }
}

/**
 * Handle order paid event
 */
private function _handle_order_paid($data) {
    log_message('info', 'Processing order paid: ' . json_encode($data));
    
    $order_id = $data['order_id'] ?? $data['data']['order_id'] ?? '';
    $payment_amount = $data['payment_amount'] ?? $data['data']['total_amount'] ?? 0;
    
    if ($order_id) {
        $this->_save_webhook_log('order_paid', $order_id, $data);
        
        $this->db->where('order_id', $order_id)->update('local_orders', [
            'payment_status' => 'paid',
            'payment_amount' => $payment_amount,
            'paid_at' => date('Y-m-d H:i:s')
        ]);
    }
}

/**
 * Handle order shipped event
 */
private function _handle_order_shipped($data) {
    log_message('info', 'Processing order shipped: ' . json_encode($data));
    
    $order_id = $data['order_id'] ?? $data['data']['order_id'] ?? '';
    $tracking_number = $data['tracking_number'] ?? $data['data']['tracking_number'] ?? '';
    $carrier = $data['carrier'] ?? $data['data']['carrier'] ?? '';
    
    if ($order_id) {
        $this->_save_webhook_log('order_shipped', $order_id, $data);
        
        $this->db->where('order_id', $order_id)->update('local_orders', [
            'order_status' => 'shipped',
            'tracking_number' => $tracking_number,
            'carrier' => $carrier,
            'shipped_at' => date('Y-m-d H:i:s')
        ]);
    }
}

/**
 * Handle order delivered event
 */
private function _handle_order_delivered($data) {
    log_message('info', 'Processing order delivered: ' . json_encode($data));
    
    $order_id = $data['order_id'] ?? $data['data']['order_id'] ?? '';
    
    if ($order_id) {
        $this->_save_webhook_log('order_delivered', $order_id, $data);
        
        $this->db->where('order_id', $order_id)->update('local_orders', [
            'order_status' => 'delivered',
            'delivered_at' => date('Y-m-d H:i:s')
        ]);
    }
}

/**
 * Handle order completed event
 */
private function _handle_order_completed($data) {
    log_message('info', 'Processing order completed: ' . json_encode($data));
    
    $order_id = $data['order_id'] ?? $data['data']['order_id'] ?? '';
    
    if ($order_id) {
        $this->_save_webhook_log('order_completed', $order_id, $data);
        
        $this->db->where('order_id', $order_id)->update('local_orders', [
            'order_status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s')
        ]);
    }
}

/**
 * Handle product created event
 */
private function _handle_product_created($data) {
    log_message('info', 'Processing product created: ' . json_encode($data));
    
    $product_id = $data['product_id'] ?? $data['data']['product_id'] ?? '';
    $product_name = $data['product_name'] ?? $data['data']['product_name'] ?? '';
    
    if ($product_id) {
        $this->_save_webhook_log('product_created', $product_id, $data);
        
        // Simpan atau update produk di database lokal
        $product_data = [
            'product_id' => $product_id,
            'product_name' => $product_name,
            'raw_data' => json_encode($data),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('local_products', $product_data);
    }
}

/**
 * Handle product updated event
 */
private function _handle_product_updated($data) {
    log_message('info', 'Processing product updated: ' . json_encode($data));
    
    $product_id = $data['product_id'] ?? $data['data']['product_id'] ?? '';
    
    if ($product_id) {
        $this->_save_webhook_log('product_updated', $product_id, $data);
        
        $this->db->where('product_id', $product_id)->update('local_products', [
            'raw_data' => json_encode($data),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}

/**
 * Handle product deleted event
 */
private function _handle_product_deleted($data) {
    log_message('info', 'Processing product deleted: ' . json_encode($data));
    
    $product_id = $data['product_id'] ?? $data['data']['product_id'] ?? '';
    
    if ($product_id) {
        $this->_save_webhook_log('product_deleted', $product_id, $data);
        
        // Soft delete atau hapus dari database lokal
        $this->db->where('product_id', $product_id)->update('local_products', [
            'is_deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s')
        ]);
    }
}

/**
 * Handle product stock update event
 */
private function _handle_product_stock_update($data) {
    log_message('info', 'Processing product stock update: ' . json_encode($data));
    
    $product_id = $data['product_id'] ?? $data['data']['product_id'] ?? '';
    $stock_quantity = $data['stock'] ?? $data['data']['stock_quantity'] ?? 0;
    
    if ($product_id) {
        $this->_save_webhook_log('product_stock_update', $product_id, $data);
        
        $this->db->where('product_id', $product_id)->update('local_products', [
            'stock_quantity' => $stock_quantity,
            'last_stock_update' => date('Y-m-d H:i:s')
        ]);
    }
}

/**
 * Handle affiliate settlement event
 */
private function _handle_affiliate_settlement($data) {
    log_message('info', 'Processing affiliate settlement: ' . json_encode($data));
    
    $settlement_id = $data['settlement_id'] ?? $data['data']['settlement_id'] ?? '';
    $amount = $data['amount'] ?? $data['data']['commission_amount'] ?? 0;
    
    if ($settlement_id) {
        $this->_save_webhook_log('affiliate_settlement', $settlement_id, $data);
        
        // Simpan data settlement affiliate
        $this->db->insert('affiliate_settlements', [
            'settlement_id' => $settlement_id,
            'amount' => $amount,
            'raw_data' => json_encode($data),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}

/**
 * Handle affiliate creator apply event
 */
private function _handle_affiliate_creator_apply($data) {
    log_message('info', 'Processing affiliate creator apply: ' . json_encode($data));
    
    $creator_id = $data['creator_id'] ?? $data['data']['creator_id'] ?? '';
    
    if ($creator_id) {
        $this->_save_webhook_log('affiliate_creator_apply', $creator_id, $data);
        
        // Notifikasi ke admin
        $this->_send_admin_notification('New affiliate creator application', [
            'creator_id' => $creator_id,
            'data' => $data
        ]);
    }
}

/**
 * Handle affiliate campaign update event
 */
private function _handle_affiliate_campaign_update($data) {
    log_message('info', 'Processing affiliate campaign update: ' . json_encode($data));
    
    $campaign_id = $data['campaign_id'] ?? $data['data']['campaign_id'] ?? '';
    
    if ($campaign_id) {
        $this->_save_webhook_log('affiliate_campaign_update', $campaign_id, $data);
    }
}

/**
 * Handle affiliate sample order event
 */
private function _handle_affiliate_sample_order($data) {
    log_message('info', 'Processing affiliate sample order: ' . json_encode($data));
    
    $sample_order_id = $data['sample_order_id'] ?? $data['data']['sample_order_id'] ?? '';
    
    if ($sample_order_id) {
        $this->_save_webhook_log('affiliate_sample_order', $sample_order_id, $data);
        
        // Notifikasi admin untuk proses sample order
        $this->_send_admin_notification('New sample order request', [
            'sample_order_id' => $sample_order_id,
            'data' => $data
        ]);
    }
}

/**
 * Handle shop update event
 */
private function _handle_shop_update($data) {
    log_message('info', 'Processing shop update: ' . json_encode($data));
    
    $shop_id = $data['shop_id'] ?? $data['data']['shop_cipher'] ?? '';
    
    if ($shop_id) {
        $this->_save_webhook_log('shop_update', $shop_id, $data);
    }
}

/**
 * Handle shop disable event
 */
private function _handle_shop_disable($data) {
    log_message('info', 'Processing shop disable: ' . json_encode($data));
    
    $shop_id = $data['shop_id'] ?? $data['data']['shop_cipher'] ?? '';
    
    if ($shop_id) {
        $this->_save_webhook_log('shop_disable', $shop_id, $data);
        
        // Update status shop jadi inactive
        $this->db->where('shop_id', $shop_id)->update('tts_tokens', [
            'is_active' => 0,
            'disabled_at' => date('Y-m-d H:i:s')
        ]);
    }
}

/**
 * Handle refund created event
 */
private function _handle_refund_created($data) {
    log_message('info', 'Processing refund created: ' . json_encode($data));
    
    $refund_id = $data['refund_id'] ?? $data['data']['refund_id'] ?? '';
    $order_id = $data['order_id'] ?? $data['data']['order_id'] ?? '';
    
    if ($refund_id) {
        $this->_save_webhook_log('refund_created', $refund_id, $data);
        
        $this->db->insert('refund_requests', [
            'refund_id' => $refund_id,
            'order_id' => $order_id,
            'status' => 'pending',
            'raw_data' => json_encode($data),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}

/**
 * Handle refund updated event
 */
private function _handle_refund_updated($data) {
    log_message('info', 'Processing refund updated: ' . json_encode($data));
    
    $refund_id = $data['refund_id'] ?? $data['data']['refund_id'] ?? '';
    $status = $data['status'] ?? $data['data']['refund_status'] ?? '';
    
    if ($refund_id) {
        $this->_save_webhook_log('refund_updated', $refund_id, $data);
        
        $this->db->where('refund_id', $refund_id)->update('refund_requests', [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}

/**
 * Handle review created event
 */
private function _handle_review_created($data) {
    log_message('info', 'Processing review created: ' . json_encode($data));
    
    $review_id = $data['review_id'] ?? $data['data']['review_id'] ?? '';
    $product_id = $data['product_id'] ?? $data['data']['product_id'] ?? '';
    $rating = $data['rating'] ?? $data['data']['rating'] ?? 0;
    
    if ($review_id) {
        $this->_save_webhook_log('review_created', $review_id, $data);
        
        $this->db->insert('product_reviews', [
            'review_id' => $review_id,
            'product_id' => $product_id,
            'rating' => $rating,
            'raw_data' => json_encode($data),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Update rating rata-rata produk
        $this->_update_product_average_rating($product_id);
    }
}

/**
 * Handle unknown event (fallback)
 */
private function _handle_unknown_event($data) {
    log_message('info', 'Unknown webhook event received');
    
    // Simpan raw data untuk analisa nanti
    $this->db->insert('unknown_webhooks', [
        'raw_data' => json_encode($data),
        'received_at' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Save webhook log ke database
 */
private function _save_webhook_log($event_type, $reference_id, $data) {
    // Pastikan tabel webhook_logs ada
    // CREATE TABLE IF NOT EXISTS webhook_logs (
    //     id INT AUTO_INCREMENT PRIMARY KEY,
    //     event_type VARCHAR(100),
    //     reference_id VARCHAR(255),
    //     payload TEXT,
    //     processed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    // );
    
    $this->db->insert('webhook_logs', [
        'event_type' => $event_type,
        'reference_id' => $reference_id,
        'payload' => json_encode($data),
        'processed_at' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Send notification to admin (email, telegram, webhook, etc)
 */
private function _send_admin_notification($subject, $data) {
    // Implementasi sesuai kebutuhan
    // Contoh: kirim email ke admin
    // $this->load->library('email');
    // $this->email->to('admin@example.com');
    // $this->email->subject($subject);
    // $this->email->message(json_encode($data, JSON_PRETTY_PRINT));
    // $this->email->send();
    
    log_message('info', 'Admin notification: ' . $subject . ' - ' . json_encode($data));
}

/**
 * Send webhook notification ke external system
 */
private function _send_webhook_notification($subject, $data) {
    // Implementasi untuk forward webhook ke sistem lain
    // $this->load->library('Curl');
    // curl_post('https://your-system.com/webhook', $data);
}

/**
 * Update product average rating
 */
private function _update_product_average_rating($product_id) {
    $this->db->select_avg('rating');
    $this->db->where('product_id', $product_id);
    $query = $this->db->get('product_reviews');
    $avg_rating = $query->row()->rating ?? 0;
    
    $this->db->where('product_id', $product_id)->update('local_products', [
        'average_rating' => round($avg_rating, 2),
        'total_reviews' => $this->db->where('product_id', $product_id)->count_all_results('product_reviews')
    ]);
}

// ========== WEBHOOK TEST ENDPOINT ==========

/**
 * Test endpoint untuk mengirim webhook manual (development only)
 */
public function webhook_test() {
    if (ENVIRONMENT !== 'development') {
        show_404();
        return;
    }
    
    $test_payload = [
        'event_type' => 'order.create',
        'order_id' => 'TEST_ORDER_' . time(),
        'shop_id' => 'TEST_SHOP',
        'data' => [
            'order_id' => 'TEST_ORDER_' . time(),
            'total_amount' => 100000,
            'order_status' => 'created'
        ]
    ];
    
    // Simulate POST request
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $GLOBALS['HTTP_RAW_POST_DATA'] = json_encode($test_payload);
    
    $this->webhook();
}

// ========== WEBHOOK CONFIGURATION ENDPOINT ==========

/**
 * Get webhook configuration info
 * Untuk membantu konfigurasi di TikTok Developer Portal
 */
public function webhook_info() {
    $webhook_url = base_url('tts/webhook');
    
    $info = [
        'webhook_url' => $webhook_url,
        'method' => 'POST',
        'format' => 'JSON',
        'supported_events' => [
            'order.create', 'order.update', 'order.cancel', 'order.paid',
            'order.ship', 'order.delivered', 'order.completed',
            'product.create', 'product.update', 'product.delete', 'product.stock_update',
            'refund.create', 'refund.update',
            'review.create',
            'affiliate.order.settlement', 'affiliate.creator.apply',
            'affiliate.campaign.update', 'affiliate.sample.order',
            'shop.update', 'shop.disable'
        ],
        'setup_instructions' => [
            '1. Go to TikTok Seller/Partner Center',
            '2. Navigate to Developer Portal > Your App > Webhook',
            '3. Enter URL: ' . $webhook_url,
            '4. Subscribe to desired events',
            '5. Save configuration'
        ]
    ];
    
    header('Content-Type: application/json');
    echo json_encode($info, JSON_PRETTY_PRINT);
}
    
    
  
    
    
}