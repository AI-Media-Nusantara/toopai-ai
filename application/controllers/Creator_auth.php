<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Creator_auth extends CI_Controller {

    private $app_key = '6jo4rjnr8ouc9';
    private $app_secret = '8ceeae7a9ba3726cb9f2e9d831020c91fad4d99c';
    
 
    private $tiktok_client_key = '6jo4rjnr8ouc9';
    private $tiktok_client_secret = '8ceeae7a9ba3726cb9f2e9d831020c91fad4d99c';
    private $tiktok_redirect_uri = '';
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper('url');
        
        // Set redirect URI
        $this->tiktok_redirect_uri = base_url('creator_auth/tiktok_callback');
    }
    
    // ========== REGISTER PAGE ==========
    public function register() {
        if ($this->session->userdata('creator_logged_in')) {
            redirect('creator/dashboard');
        }
        
        $data['title'] = 'Creator Registration - Toopai';
        $this->load->view('creator/register', $data);
    }
    
    // ========== REGISTER PROCESS ==========
    public function do_register() {
        $this->output->set_content_type('application/json');
        
        $username = $this->input->post('username');
        $email = $this->input->post('email');
        $password = $this->input->post('password');
        $confirm_password = $this->input->post('confirm_password');
        $full_name = $this->input->post('full_name');
        $phone = $this->input->post('phone');
        $category = $this->input->post('category');
        
        if (empty($username) || empty($email) || empty($password)) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Username, email, and password are required'
            ]));
        }
        
        if ($password !== $confirm_password) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Password and confirm password do not match'
            ]));
        }
        
        if (strlen($password) < 6) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Password must be at least 6 characters'
            ]));
        }
        
        $existing = $this->db->where('username', $username)
                             ->or_where('email', $email)
                             ->get('creators')
                             ->row();
        
        if ($existing) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Username or email already exists'
            ]));
        }
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $data = [
            'username' => $username,
            'email' => $email,
            'password' => $hashed_password,
            'full_name' => $full_name,
            'phone' => $phone,
            'category' => $category,
            'status' => 'PENDING',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('creators', $data);
        $creator_id = $this->db->insert_id();
        
        $this->session->set_userdata([
            'creator_logged_in' => true,
            'creator_id' => $creator_id,
            'creator_username' => $username,
            'creator_email' => $email,
            'creator_full_name' => $full_name
        ]);
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'message' => 'Registration successful!',
            'redirect' => base_url('creator/dashboard')
        ]));
    }
    
    // ========== LOGIN PAGE ==========
    public function login() {
        if ($this->session->userdata('creator_logged_in')) {
            redirect('creator/dashboard');
        }
        
        $data['title'] = 'Creator Login - Toopai';
        $this->load->view('creator/login', $data);
    }
    
    // ========== LOGIN PROCESS (Email/Password) ==========
    public function do_login() {
        $this->output->set_content_type('application/json');
        
        $email = $this->input->post('email');
        $password = $this->input->post('password');
        
        if (empty($email) || empty($password)) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Email and password are required'
            ]));
        }
        
        $creator = $this->db->where('email', $email)
                            ->or_where('username', $email)
                            ->get('creators')
                            ->row();
        
        if (!$creator) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Invalid email/username or password'
            ]));
        }
        
        if (!password_verify($password, $creator->password)) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Invalid email/username or password'
            ]));
        }
        
        $this->db->where('id', $creator->id)->update('creators', [
            'last_login' => date('Y-m-d H:i:s')
        ]);
        
        $this->session->set_userdata([
            'creator_logged_in' => true,
            'creator_id' => $creator->id,
            'creator_username' => $creator->username,
            'creator_email' => $creator->email,
            'creator_full_name' => $creator->full_name,
            'creator_category' => $creator->category,
            'creator_hid' => $creator->creator_hid
        ]);
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'message' => 'Login successful!',
            'redirect' => base_url('creator/dashboard')
        ]));
    }
    
    // ========== TIKTOK LOGIN REDIRECT ==========
    public function tiktok_login() {
        $state = bin2hex(random_bytes(16));
        $this->session->set_userdata('tiktok_login_state', $state);
        
        // Scopes yang diperlukan
        $scopes = [
            'user.info.basic',
            'user.info.profile'
        ];
        
        $scope_string = implode(',', $scopes);
        
        // TikTok Authorization URL
        $auth_url = 'https://www.tiktok.com/v2/auth/authorize/?' . http_build_query([
            'client_key' => $this->tiktok_client_key,
            'response_type' => 'code',
            'scope' => $scope_string,
            'redirect_uri' => $this->tiktok_redirect_uri,
            'state' => $state
        ]);
        
        log_message('info', 'TikTok Login redirect: ' . $auth_url);
        redirect($auth_url);
    }
    
    // ========== TIKTOK CALLBACK ==========
 public function tiktok_callback() {
    log_message('debug', '=== TIKTOK CALLBACK (LOGIN) ===');
    log_message('debug', 'GET params: ' . json_encode($this->input->get()));
    
    $code = $this->input->get('code');
    $state = $this->input->get('state');
    $error = $this->input->get('error');
    $error_description = $this->input->get('error_description');
    
    $session_state = $this->session->userdata('tiktok_auth_state');
    
    // Handle error dari TikTok
    if (!empty($error)) {
        log_message('error', 'TikTok OAuth error: ' . $error . ' - ' . $error_description);
        $this->session->set_flashdata('tiktok_error', 'TikTok authorization failed: ' . ($error_description ?? $error));
        redirect('creator_auth/authorize_tiktok');
        return;
    }
    
    // Validasi state
    if ($state !== $session_state) {
        log_message('error', 'State mismatch! URL: ' . $state . ', Session: ' . $session_state);
        $this->session->set_flashdata('tiktok_error', 'Invalid state parameter. Please try again.');
        redirect('creator_auth/authorize_tiktok');
        return;
    }
    
    if (empty($code)) {
        log_message('error', 'Authorization code is missing');
        $this->session->set_flashdata('tiktok_error', 'Authorization code is missing. Please try again.');
        redirect('creator_auth/authorize_tiktok');
        return;
    }
    
    try {
        // Exchange code untuk access token
        $token_data = $this->exchange_tiktok_code_for_token($code);
        
        if (!$token_data || empty($token_data['access_token'])) {
            throw new Exception('Failed to get access token from TikTok');
        }
        
        log_message('debug', 'Access token obtained successfully');
        
        // Get user info from TikTok
        $user_info = $this->get_tiktok_user_info($token_data['access_token']);
        
        if (!$user_info || empty($user_info['open_id'])) {
            throw new Exception('Failed to get user info from TikTok');
        }
        
        log_message('debug', 'User info obtained: ' . json_encode($user_info));
        
        // Cek apakah user sudah terdaftar berdasarkan open_id
        $existing = $this->db->where('tiktok_open_id', $user_info['open_id'])
                             ->get('creators')
                             ->row();
        
        if ($existing) {
            // User sudah ada, langsung login
            log_message('debug', 'Existing user found: ' . $existing->username);
            
            // Update token
            $this->db->where('id', $existing->id)->update('creators', [
                'tiktok_access_token' => $token_data['access_token'],
                'tiktok_refresh_token' => $token_data['refresh_token'],
                'tiktok_token_expire' => date('Y-m-d H:i:s', time() + $token_data['expires_in']),
                'tiktok_avatar' => $user_info['avatar_url'],
                'tiktok_display_name' => $user_info['display_name'],
                'last_login' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Set session
            $this->session->set_userdata([
                'creator_logged_in' => true,
                'creator_id' => $existing->id,
                'creator_username' => $existing->username,
                'creator_email' => $existing->email,
                'creator_full_name' => $existing->full_name,
                'creator_category' => $existing->category,
                'creator_hid' => $existing->creator_hid
            ]);
            
            $this->session->set_flashdata('tiktok_success', 'Welcome back, ' . $existing->username . '! Login successful.');
            redirect('creator/dashboard');
            
        } else {
            // User baru, arahkan ke form registrasi dengan data pre-filled
            log_message('debug', 'New user, redirecting to registration');
            
            $this->session->set_userdata([
                'tiktok_temp_open_id' => $user_info['open_id'],
                'tiktok_temp_union_id' => $user_info['union_id'] ?? '',
                'tiktok_temp_avatar' => $user_info['avatar_url'],
                'tiktok_temp_display_name' => $user_info['display_name'],
                'tiktok_temp_username' => $this->generate_username_from_tiktok($user_info['display_name']),
                'tiktok_temp_email' => $user_info['display_name'] . '@tiktok.user',
                'tiktok_temp_access_token' => $token_data['access_token'],
                'tiktok_temp_refresh_token' => $token_data['refresh_token'],
                'tiktok_temp_token_expire' => time() + $token_data['expires_in']
            ]);
            
            $this->session->set_flashdata('tiktok_success', 'TikTok account verified! Please complete your registration.');
            redirect('creator_auth/complete_registration');
        }
        
    } catch (Exception $e) {
        log_message('error', 'TikTok callback error: ' . $e->getMessage());
        $this->session->set_flashdata('tiktok_error', 'TikTok login failed: ' . $e->getMessage());
        redirect('creator_auth/authorize_tiktok');
    }
}
    /**
     * Exchange authorization code for access token (Manual cURL)
     */
    private function exchange_tiktok_code_for_token($code) {
        $url = 'https://open.tiktokapis.com/v2/oauth/token/';
        
        $post_fields = [
            'client_key' => $this->tiktok_client_key,
            'client_secret' => $this->tiktok_client_secret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->tiktok_redirect_uri
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_POSTFIELDS => http_build_query($post_fields),
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        log_message('debug', 'TikTok token response: ' . $response);
        
        if ($http_code !== 200) {
            throw new Exception('Token request failed with HTTP ' . $http_code);
        }
        
        $result = json_decode($response, true);
        
        if (isset($result['error'])) {
            throw new Exception($result['error_description'] ?? $result['error']);
        }
        
        // Response dari TikTok bisa berbeda format
        if (isset($result['access_token'])) {
            return [
                'access_token' => $result['access_token'],
                'refresh_token' => $result['refresh_token'] ?? '',
                'expires_in' => $result['expires_in'] ?? 7200
            ];
        } elseif (isset($result['data']['access_token'])) {
            return [
                'access_token' => $result['data']['access_token'],
                'refresh_token' => $result['data']['refresh_token'] ?? '',
                'expires_in' => $result['data']['expires_in'] ?? 7200
            ];
        }
        
        throw new Exception('Invalid token response format');
    }
    
    /**
     * Get TikTok user info using access token (Manual cURL)
     */
    private function get_tiktok_user_info($access_token) {
        $url = 'https://open.tiktokapis.com/v2/user/info/?fields=open_id,union_id,avatar_url,display_name,username';
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $access_token,
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code !== 200) {
            throw new Exception('User info request failed with HTTP ' . $http_code);
        }
        
        $result = json_decode($response, true);
        
        if (isset($result['error']['code']) && $result['error']['code'] !== 'ok') {
            throw new Exception($result['error']['message'] ?? 'Failed to get user info');
        }
        
        // Response dari TikTok bisa berbeda format
        $user = [];
        if (isset($result['data']['user'])) {
            $user = $result['data']['user'];
        } elseif (isset($result['user'])) {
            $user = $result['user'];
        }
        
        return [
            'open_id' => $user['open_id'] ?? '',
            'union_id' => $user['union_id'] ?? '',
            'avatar_url' => $user['avatar_url'] ?? '',
            'display_name' => $user['display_name'] ?? '',
            'username' => $user['username'] ?? ''
        ];
    }
    
    /**
     * Generate username from TikTok display name
     */
    private function generate_username_from_tiktok($display_name) {
        $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $display_name));
        if (empty($username)) {
            $username = 'user_' . bin2hex(random_bytes(4));
        }
        
        // Cek apakah username sudah ada
        $existing = $this->db->where('username', $username)->get('creators')->row();
        if ($existing) {
            $username = $username . '_' . rand(100, 999);
        }
        
        return $username;
    }
    
    // ========== COMPLETE REGISTRATION ==========
    public function complete_registration() {
        if (!$this->session->userdata('tiktok_temp_open_id')) {
            redirect('creator_auth/register');
        }
        
        $data = [
            'title' => 'Complete Registration - Toopai',
            'tiktok_avatar' => $this->session->userdata('tiktok_temp_avatar'),
            'tiktok_display_name' => $this->session->userdata('tiktok_temp_display_name'),
            'suggested_username' => $this->session->userdata('tiktok_temp_username'),
            'suggested_email' => $this->session->userdata('tiktok_temp_email')
        ];
        
        $this->load->view('creator/complete_registration', $data);
    }
    
    // ========== PROCESS COMPLETE REGISTRATION ==========
    public function do_complete_registration() {
        $this->output->set_content_type('application/json');
        
        $username = $this->input->post('username');
        $email = $this->input->post('email');
        $full_name = $this->input->post('full_name');
        $phone = $this->input->post('phone');
        $category = $this->input->post('category');
        $password = $this->input->post('password');
        
        if (empty($username) || empty($email)) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Username and email are required'
            ]));
        }
        
        $existing = $this->db->where('username', $username)
                             ->or_where('email', $email)
                             ->get('creators')
                             ->row();
        
        if ($existing) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Username or email already exists'
            ]));
        }
        
        $hashed_password = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;
        
        $data = [
            'username' => $username,
            'email' => $email,
            'password' => $hashed_password,
            'full_name' => $full_name ?: $this->session->userdata('tiktok_temp_display_name'),
            'phone' => $phone,
            'category' => $category,
            'tiktok_open_id' => $this->session->userdata('tiktok_temp_open_id'),
            'tiktok_union_id' => $this->session->userdata('tiktok_temp_union_id'),
            'tiktok_avatar' => $this->session->userdata('tiktok_temp_avatar'),
            'tiktok_display_name' => $this->session->userdata('tiktok_temp_display_name'),
            'tiktok_access_token' => $this->session->userdata('tiktok_temp_access_token'),
            'tiktok_refresh_token' => $this->session->userdata('tiktok_temp_refresh_token'),
            'tiktok_token_expire' => date('Y-m-d H:i:s', $this->session->userdata('tiktok_temp_token_expire')),
            'status' => 'ACTIVE',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('creators', $data);
        $creator_id = $this->db->insert_id();
        
        // Clear TikTok session data
        $this->session->unset_userdata([
            'tiktok_temp_open_id', 'tiktok_temp_union_id', 'tiktok_temp_avatar',
            'tiktok_temp_display_name', 'tiktok_temp_username', 'tiktok_temp_email',
            'tiktok_temp_access_token', 'tiktok_temp_refresh_token', 'tiktok_temp_token_expire'
        ]);
        
        $this->session->set_userdata([
            'creator_logged_in' => true,
            'creator_id' => $creator_id,
            'creator_username' => $username,
            'creator_email' => $email,
            'creator_full_name' => $data['full_name'],
            'creator_category' => $category
        ]);
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'message' => 'Registration completed! Welcome to Toopai!',
            'redirect' => base_url('creator/dashboard')
        ]));
    }
    
    // ========== LOGOUT ==========
    public function logout() {
        $this->session->sess_destroy();
        redirect('creator_auth/login');
    }
    
    // ========== FORGOT PASSWORD ==========
    public function forgot_password() {
        $data['title'] = 'Forgot Password - Toopai';
        $this->load->view('creator/forgot_password', $data);
    }
    
    // ========== TIKTOK AUTHORIZATION ==========

/**
 * Halaman authorize TikTok untuk creator
 * URL: /creator/authorize_tiktok
 */
public function authorize_tiktok() {
    // Cek apakah sudah login
    if ($this->session->userdata('creator_logged_in')) {
        redirect('creator/dashboard');
    }
    
    // Cek error dari session
    $error = $this->session->flashdata('tiktok_error');
    $success = $this->session->flashdata('tiktok_success');
    
    $data = [
        'title' => 'Authorize TikTok - Toopai Creator',
        'error' => $error,
        'success' => $success
    ];
    
    $this->load->view('creator/auth_authorize_tiktok', $data);
}


/**
 * Proses authorize TikTok (redirect ke TikTok OAuth)
 * URL: /creator/do_authorize_tiktok
 */
public function do_authorize_tiktok() {
    // Generate state untuk keamanan
    $state = bin2hex(random_bytes(16));
    $this->session->set_userdata('tiktok_auth_state', $state);
    
    // Redirect URI untuk callback
    $redirect_uri = base_url('creator_auth/tiktok_callback');
    
    // Scope untuk creator (basic info untuk login)
    $scope = urlencode('user.info.basic');
    
    // Client Key
    $client_key = '6jo4rjnr8ouc9';
    
    // URL authorize TikTok
    $auth_url = "https://www.tiktok.com/auth/authorize/"
              . "?client_key=" . $client_key
              . "&response_type=code"
              . "&scope=" . $scope
              . "&redirect_uri=" . urlencode($redirect_uri)
              . "&state=" . $state;
    
    log_message('debug', 'Redirecting to TikTok OAuth: ' . $auth_url);
    
    // Simpan log
    $this->session->set_flashdata('tiktok_info', 'Redirecting to TikTok for authorization...');
    
    redirect($auth_url);
}


/**
 * Refresh TikTok token
 * URL: /creator/refresh_tiktok_token
 */
public function refresh_tiktok_token() {
    $this->output->set_content_type('application/json');
    
    $creator_id = $this->session->userdata('creator_id');
    $creator = $this->db->where('id', $creator_id)->get('creators')->row();
    
    if (!$creator || empty($creator->tiktok_refresh_token)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'No refresh token available'
        ]));
    }
    
    $token_url = "https://open.tiktokapis.com/v2/oauth/token/";
    
    $post_data = [
        'client_key' => $this->config->item('tiktok_client_key'),
        'client_secret' => $this->config->item('tiktok_secret'),
        'refresh_token' => $creator->tiktok_refresh_token,
        'grant_type' => 'refresh_token'
    ];
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $token_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($post_data)
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if ($http_code == 200 && isset($result['access_token'])) {
        $expires_in = $result['expires_in'] ?? 86400;
        
        $this->db->where('id', $creator_id);
        $this->db->update('creators', [
            'tiktok_access_token' => $result['access_token'],
            'tiktok_token_expire' => date('Y-m-d H:i:s', time() + $expires_in),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'expires_at' => date('Y-m-d H:i:s', time() + $expires_in)
        ]));
    }
    
    return $this->output->set_output(json_encode([
        'success' => false,
        'message' => $result['error'] ?? 'Failed to refresh token'
    ]));
}

/**
 * Revoke TikTok token
 * URL: /creator/revoke_tiktok_token
 */
public function revoke_tiktok_token() {
    $this->output->set_content_type('application/json');
    
    $creator_id = $this->session->userdata('creator_id');
    $creator = $this->db->where('id', $creator_id)->get('creators')->row();
    
    if (!$creator || empty($creator->tiktok_access_token)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'No token to revoke'
        ]));
    }
    
    // Optional: Call TikTok revoke endpoint
    // https://open.tiktokapis.com/v2/oauth/revoke/
    
    // Clear tokens from database
    $this->db->where('id', $creator_id);
    $this->db->update('creators', [
        'tiktok_access_token' => null,
        'tiktok_refresh_token' => null,
        'tiktok_token_expire' => null,
        'tiktok_open_id' => null,
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'message' => 'TikTok token revoked successfully'
    ]));
}



}