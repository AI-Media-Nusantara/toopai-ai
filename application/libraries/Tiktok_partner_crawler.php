<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tiktok_partner_crawler
{
    protected $CI;

    protected $session = [];

    protected $find_url;
    protected $contact_url;
    protected $product_search_url;
    protected $brand_contact_url;
    protected $partner_id;

    protected $delay_min = 15;
    protected $delay_max = 45;
    protected $cache_ttl = 21600;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->config->load('tiktok_partner');

        $this->find_url           = $this->CI->config->item('tiktok_partner_find_url');
        $this->contact_url        = $this->CI->config->item('tiktok_partner_contact_url');
        $this->product_search_url = $this->CI->config->item('tiktok_partner_product_search_url');
        $this->brand_contact_url  = $this->CI->config->item('tiktok_partner_brand_contact_url');
        $this->partner_id         = $this->CI->config->item('tiktok_partner_partner_id');

        $delay = $this->CI->config->item('tiktok_partner_request_delay');
        if (is_array($delay)) {
            $this->delay_min = intval($delay['min'] ?? 15);
            $this->delay_max = intval($delay['max'] ?? 45);
        }

        $this->cache_ttl = intval($this->CI->config->item('tiktok_partner_cache_ttl') ?? 21600);
        $this->session = $this->CI->config->item('tiktok_partner_default_session') ?: [];

        $this->load_session_from_database();
    }

    private function load_session_from_database()
    {
        if (!$this->CI->db->table_exists('app_config')) {
            return;
        }

        $row = $this->CI->db
            ->where('key', 'tiktok_partner_session')
            ->get('app_config')
            ->row();

        if (!$row || empty($row->value)) {
            return;
        }

        $saved = json_decode($row->value, true);
        if (!is_array($saved)) {
            return;
        }

        $this->session = array_replace_recursive($this->session, $saved);
    }

    public function save_session(array $session)
    {
        if (!$this->CI->db->table_exists('app_config')) {
            return [
                'success' => false,
                'message' => 'Table app_config belum ada'
            ];
        }

        $required = ['cookie', 'ms_token', 'x_bogus', 'signature', 'bsid', 'fp'];
        foreach ($required as $field) {
            if (empty($session[$field])) {
                return [
                    'success' => false,
                    'message' => $field . ' wajib diisi'
                ];
            }
        }

        if (empty($session['turing']) || !is_array($session['turing'])) {
            return [
                'success' => false,
                'message' => 'turing wajib diisi sebagai object'
            ];
        }

        $required_turing = ['xmsi', 'xmst', 'version_web_id_ID', 'version_bdturing_en'];
        foreach ($required_turing as $field) {
            if (empty($session['turing'][$field])) {
                return [
                    'success' => false,
                    'message' => 'turing.' . $field . ' wajib diisi'
                ];
            }
        }

        $current = $this->session;
        $payload = array_replace_recursive($current, [
            'cookie' => trim($session['cookie']),
            'ms_token' => trim($session['ms_token']),
            'x_bogus' => trim($session['x_bogus']),
            'signature' => trim($session['signature']),
            'bsid' => trim($session['bsid']),
            'fp' => trim($session['fp']),
            'user_agent' => trim($session['user_agent'] ?? ($current['user_agent'] ?? '')),
            'browser_platform' => trim($session['browser_platform'] ?? ($current['browser_platform'] ?? 'MacIntel')),
            'browser_language' => trim($session['browser_language'] ?? ($current['browser_language'] ?? 'id-ID')),
            'timezone_name' => trim($session['timezone_name'] ?? ($current['timezone_name'] ?? 'Asia/Jakarta')),
            'screen_width' => strval($session['screen_width'] ?? ($current['screen_width'] ?? '1920')),
            'screen_height' => strval($session['screen_height'] ?? ($current['screen_height'] ?? '1080')),
            'browser_online' => strval($session['browser_online'] ?? ($current['browser_online'] ?? 'true')),
            'device_id' => strval($session['device_id'] ?? ($current['device_id'] ?? '0')),
            'turing' => $session['turing'],
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->CI->db->where('key', 'tiktok_partner_session')->delete('app_config');
        $ok = $this->CI->db->insert('app_config', [
            'key' => 'tiktok_partner_session',
            'value' => json_encode($payload),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if ($ok) {
            $this->session = $payload;
            $this->clear_cache();
        }

        return [
            'success' => (bool) $ok,
            'message' => $ok ? 'Session TikTok berhasil disimpan' : 'Gagal menyimpan session'
        ];
    }

    public function get_session_status()
    {
        $turing = $this->session['turing'] ?? [];

        return [
            'has_cookie' => !empty($this->session['cookie']),
            'has_ms_token' => !empty($this->session['ms_token']),
            'has_x_bogus' => !empty($this->session['x_bogus']),
            'has_signature' => !empty($this->session['signature']),
            'has_bsid' => !empty($this->session['bsid']),
            'has_fp' => !empty($this->session['fp']),
            'has_turing_xmsi' => !empty($turing['xmsi']),
            'has_turing_xmst' => !empty($turing['xmst']),
            'has_version_web_id_ID' => !empty($turing['version_web_id_ID']),
            'has_version_bdturing_en' => !empty($turing['version_bdturing_en']),
            'delay' => [
                'min' => $this->delay_min,
                'max' => $this->delay_max,
            ],
            'cache_ttl' => $this->cache_ttl,
            'updated_at' => $this->session['updated_at'] ?? null,
        ];
    }

private function apply_delay($type = 'search')
{
    // 🚀 DELAY MAKSIMAL 1 DETIK (1000ms)
    // Untuk menghindari rate limiting tapi tetap responsif
    
    $hour = (int) date('H');
    $is_peak_hour = ($hour >= 9 && $hour <= 22);
    
    if ($type === 'contact') {
        // Contact: delay 200-800ms (maks 1 detik)
        $base = $is_peak_hour ? rand(400, 800) : rand(200, 600);
        $jitter = rand(-100, 100);
        $delay_ms = max(100, min(1000, $base + $jitter));
        usleep($delay_ms * 1000); // konversi ke microdetik
        return;
    }
    
    if ($type === 'search') {
        // Search: delay 100-500ms (maks 1 detik)
        $base = $is_peak_hour ? rand(250, 500) : rand(100, 300);
        $jitter = rand(-50, 50);
        $delay_ms = max(50, min(1000, $base + $jitter));
        usleep($delay_ms * 1000);
        return;
    }
}

    private function clean_cookie($cookie)
    {
        $cookie = str_replace(["\r", "\n", "\t"], '', $cookie);
        $cookie = preg_replace('/\s*;\s*/', '; ', $cookie);
        return trim($cookie);
    }


    private function headers()
{
    $ua = $this->session['user_agent'] ?? '';
    $platform = $this->session['browser_platform'] ?? 'MacIntel';
    
    // Cookie lengkap dengan oec_lucifer
    $cookie = $this->clean_cookie($this->session['cookie']);
    if (!empty($this->session['cookie_oec_lucifer'])) {
        $cookie .= '; oec_lucifer=' . $this->session['cookie_oec_lucifer'];
    }
    
    $headers = [
        'Accept: application/json, text/plain, */*',
        'Accept-Language: id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
        'Content-Type: application/json',
        'Origin: https://partner.tiktokshop.com',
        'Referer: https://partner.tiktokshop.com/',
        'Sec-Ch-Ua: "Google Chrome";v="149", "Chromium";v="149", "Not)A;Brand";v="24"',
        'Sec-Ch-Ua-Mobile: ?0',
        'Sec-Ch-Ua-Platform: "macOS"',
        'Sec-Fetch-Dest: empty',
        'Sec-Fetch-Mode: cors',
        'Sec-Fetch-Site: same-site',
        'User-Agent: ' . $ua,
        'Priority: u=1, i',
        'Cookie: ' . $cookie,
    ];
    
    // Tambahan dari PowerShell
    $headers[] = 'authority: api-partner-sg.tiktokshop.com';
    $headers[] = 'scheme: https';
    
    $turing = $this->session['turing'] ?? [];
    if (!empty($turing['xmsi'])) {
        $headers[] = 'X-Ms-Request-Index: ' . $turing['xmsi'];
    }
    if (!empty($turing['xmst'])) {
        $headers[] = 'X-Ms-Request-Token: ' . $turing['xmst'];
    }
    
    return $headers;
}

    private function common_query(array $extra = [])
    {
        $turing = $this->session['turing'] ?? [];
        $browser_language = $this->session['browser_language'] ?? 'id-ID';
        $timezone_name = $this->session['timezone_name'] ?? 'Asia/Jakarta';
        $browser_platform = $this->session['browser_platform'] ?? 'MacIntel';
        $browser_version = $this->browser_version_string();

        $query = [
            'user_language' => 'id-ID',
            'partner_id' => $this->partner_id,
            'aid' => '360019',
            'app_name' => 'i18n_ecom_alliance',
            'device_id' => $this->session['device_id'] ?? '0',
            'fp' => $this->session['fp'] ?? '',
            'device_platform' => 'web',
            'cookie_enabled' => 'true',
            'screen_width' => $this->session['screen_width'] ?? '1920',
            'screen_height' => $this->session['screen_height'] ?? '1080',
            'browser_language' => $browser_language,
            'browser_platform' => $browser_platform,
            'browser_name' => 'Mozilla',
            'browser_version' => $browser_version,
            'browser_online' => $this->session['browser_online'] ?? 'true',
            'timezone_name' => $timezone_name,
            'X-Tts-Oec-Bsid' => $this->session['bsid'] ?? '',
            'msToken' => $this->session['ms_token'] ?? '',
            'X-Bogus' => $this->session['x_bogus'] ?? '',
            '_signature' => $this->session['signature'] ?? '',
        ];

        if (!empty($turing['version_dynamic_form_en_US'])) {
            $query['version.dynamic-form.en-US'] = $turing['version_dynamic_form_en_US'];
        }
        if (!empty($turing['version_dynamic_form_id_ID'])) {
            $query['version.dynamic-form.id-ID'] = $turing['version_dynamic_form_id_ID'];
        }
        if (!empty($turing['version_web_id_ID'])) {
            $query['version.web.id-ID'] = $turing['version_web_id_ID'];
        }
        if (!empty($turing['version_bdturing_en'])) {
            $query['version.BDTuringEn.en'] = $turing['version_bdturing_en'];
        }

        $query = array_merge($query, $extra);
        return http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }

    private function is_verification_required($data, $raw = '')
    {
        $raw_lc = strtolower((string) $raw);
        if (strpos($raw_lc, 'captcha') !== false || strpos($raw_lc, 'puzzle') !== false || strpos($raw_lc, 'verify') !== false) {
            return true;
        }

        if (!is_array($data)) {
            return false;
        }

        $msg = strtolower($data['msg'] ?? $data['message'] ?? '');
        $code = intval($data['code'] ?? $data['status_code'] ?? 0);

        $keywords = ['captcha', 'puzzle', 'verify', 'verification', 'challenge', 'validation', 'bot', 'blocked', 'risk', 'too many', 'rate limit'];
        foreach ($keywords as $keyword) {
            if (strpos($msg, $keyword) !== false) {
                return true;
            }
        }

        if (isset($data['data']['captcha']) || isset($data['data']['puzzle']) || isset($data['captcha']) || isset($data['puzzle'])) {
            return true;
        }

        return in_array($code, [1003, 1004, 1005, 1006, 1007, 1008, 1009, 1010, 1011, 1012, 20001], true);
    }

    private function request_post($url, array $payload = [], $type = 'search')
    {
       $this->apply_delay($type);

        $url = trim(str_replace(["\r", "\n", "\t"], '', $url));
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES);
        if (!$body) {
            $body = '{}';
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $this->headers(),
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($response, true);

        if ($this->is_verification_required($decoded, $response)) {
            return [
                'success' => false,
                'http_code' => $http,
                'errno' => $errno,
                'error' => $error,
                'url' => $url,
                'payload' => $payload,
                'data' => $decoded,
                'raw' => $response,
                'requires_captcha' => true,
                'message' => 'TikTok membutuhkan verifikasi manual. Request dihentikan.'
            ];
        }

        return [
            'success' => !$error && $http >= 200 && $http < 300,
            'http_code' => $http,
            'errno' => $errno,
            'error' => $error,
            'url' => $url,
            'payload' => $payload,
            'data' => $decoded,
            'raw' => $response,
            'requires_captcha' => false,
        ];
    }
private function browser_version_string()
{
    $ua = $this->session['user_agent'] ?? '';
    if (!$ua) return '';
    
    // URL encode dengan cara yang sama seperti PowerShell
    return urlencode($ua);
}

    private function request_get($url, $type = 'search')
    {
          $this->apply_delay($type);

        $url = trim(str_replace(["\r", "\n", "\t"], '', $url));
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPGET => true,
            CURLOPT_HTTPHEADER => $this->headers(),
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($response, true);

        if ($this->is_verification_required($decoded, $response)) {
            return [
                'success' => false,
                'http_code' => $http,
                'errno' => $errno,
                'error' => $error,
                'url' => $url,
                'data' => $decoded,
                'raw' => $response,
                'requires_captcha' => true,
                'message' => 'TikTok membutuhkan verifikasi manual. Request dihentikan.'
            ];
        }

        return [
            'success' => !$error && $http >= 200 && $http < 300,
            'http_code' => $http,
            'errno' => $errno,
            'error' => $error,
            'url' => $url,
            'data' => $decoded,
            'raw' => $response,
            'requires_captcha' => false,
        ];
    }

    private function get_cache($key)
    {
        if (!$this->CI->db->table_exists('app_cache')) {
            return null;
        }

        $row = $this->CI->db
            ->where('cache_key', $key)
            ->where('created_at >=', date('Y-m-d H:i:s', time() - $this->cache_ttl))
            ->get('app_cache')
            ->row();

        if (!$row) {
            return null;
        }

        $value = json_decode($row->cache_value, true);
        return is_array($value) ? $value : null;
    }

    private function set_cache($key, $value)
    {
        if (!$this->CI->db->table_exists('app_cache')) {
            return;
        }

        $this->CI->db->replace('app_cache', [
            'cache_key' => $key,
            'cache_value' => json_encode($value),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function clear_cache()
    {
        if ($this->CI->db->table_exists('app_cache')) {
            $this->CI->db->truncate('app_cache');
        }
    }

    public function find_creator($username, $page = 0, $size = 12)
    {
        $username = trim($username);
        $cache_key = 'creator_find_' . md5($username . '|' . $page . '|' . $size);
        $cached = $this->get_cache($cache_key);
    if ($cached) {
    $products = $cached['data']['data']['products'] ?? [];

    if (!empty($cached['requires_captcha']) || empty($products)) {
        // cache buruk, jangan dipakai
        $this->delete_cache($cache_key);
    } else {
        $cached['from_cache'] = true;
        return $cached;
    }
}

        $url = $this->find_url . '?' . $this->common_query();
        $payload = [
            'query' => $username,
            'pagination' => [
                'size' => intval($size),
                'page' => intval($page),
            ],
            'query_type' => 1,
            'filter_params' => new stdClass(),
            'algorithm' => 1,
        ];

        $result = $this->request_post($url, $payload, 'search');
        if ($result['success']) {
            $this->set_cache($cache_key, $result);
        }
        return $result;
    }

    public function parse_creator_result($response, $username)
    {
        $data = $response['data'] ?? [];
        $target = strtolower(trim($username));
        $candidates = [];

        if (!empty($data['creator_profile_list']) && is_array($data['creator_profile_list'])) {
            $candidates = array_merge($candidates, $data['creator_profile_list']);
        }
        if (!empty($data['data']['creator_profile_list']) && is_array($data['data']['creator_profile_list'])) {
            $candidates = array_merge($candidates, $data['data']['creator_profile_list']);
        }
        if (!empty($data['data']['profiles']) && is_array($data['data']['profiles'])) {
            $candidates = array_merge($candidates, $data['data']['profiles']);
        }

        if (empty($candidates)) {
            return $this->find_creator_recursive($data, $target);
        }

        foreach ($candidates as $creator) {
            if ($this->extract_creator_handle($creator) === $target) {
                return $creator;
            }
        }

        foreach ($candidates as $creator) {
            $handle = $this->extract_creator_handle($creator);
            if ($handle && strpos($handle, $target) !== false) {
                return $creator;
            }
        }

        return $candidates[0] ?? null;
    }

    private function extract_creator_handle($creator)
    {
        $handle = $creator['handle']['value']
            ?? $creator['handle']
            ?? $creator['username']['value']
            ?? $creator['username']
            ?? $creator['unique_id']['value']
            ?? $creator['unique_id']
            ?? '';

        return strtolower(trim($handle));
    }

    private function find_creator_recursive($node, $target)
    {
        if (!is_array($node)) {
            return null;
        }

        $handle = $this->extract_creator_handle($node);
        if ($handle === $target) {
            return $node;
        }

        foreach ($node as $child) {
            if (is_array($child)) {
                $found = $this->find_creator_recursive($child, $target);
                if ($found) {
                    return $found;
                }
            }
        }

        return null;
    }

    public function extract_creator_oecuid($creator)
    {
        return $creator['creator_oecuid']['value']
            ?? $creator['creator_oecuid']
            ?? $creator['oecuid']
            ?? null;
    }

    public function get_contact($creator_oecuid)
    {
        $creator_oecuid = trim($creator_oecuid);
        $cache_key = 'creator_contact_' . md5($creator_oecuid);
        $cached = $this->get_cache($cache_key);
   if ($cached) {
    $products = $cached['data']['data']['products'] ?? [];

    if (!empty($cached['requires_captcha']) || empty($products)) {
        // cache buruk, jangan dipakai
        $this->delete_cache($cache_key);
    } else {
        $cached['from_cache'] = true;
        return $cached;
    }
}

        $url = $this->contact_url
            . '?creator_oecuid=' . rawurlencode($creator_oecuid)
            . '&scene=20'
            . '&' . $this->common_query();

        $result = $this->request_get($url, 'contact');
        if ($result['success']) {
            $this->set_cache($cache_key, $result);
        }
        return $result;
    }

    public function get_creator_contact($creator_oecuid)
    {
        return $this->get_contact($creator_oecuid);
    }

    public function parse_contact($contact_response)
    {
        $data = $contact_response['data'] ?? [];
        $whatsapp = null;
        $email = null;

        if (!empty($data['contact_info']) && is_array($data['contact_info'])) {
            foreach ($data['contact_info'] as $item) {
                $field = intval($item['field'] ?? 0);
                $value = trim($item['value'] ?? '');
                if ($value === '') continue;
                if ($field === 1) $whatsapp = $value;
                if ($field === 2) $email = $value;
            }
        }

        if (!$whatsapp && isset($data['data']['contact_info']['whatsapp'])) {
            $whatsapp = trim($data['data']['contact_info']['whatsapp']);
        }
        if (!$email && isset($data['data']['contact_info']['email'])) {
            $email = trim($data['data']['contact_info']['email']);
        }

        return [
            'whatsapp' => $whatsapp ?: null,
            'email' => $email ?: null,
            'raw' => $data,
        ];
    }

    public function parse_creator_contact($contact_response)
    {
        return $this->parse_contact($contact_response);
    }

    public function search_brand_product($keyword, $page = 1, $page_size = 20)
    {
        $keyword = trim($keyword);
        $page = max(1, intval($page));
        $page_size = min(max(1, intval($page_size)), 50);

        $cache_key = 'brand_product_search_' . md5($keyword . '|' . $page . '|' . $page_size);
        $cached = $this->get_cache($cache_key);
       if ($cached) {
    $products = $cached['data']['data']['products'] ?? [];

    if (!empty($cached['requires_captcha']) || empty($products)) {
        // cache buruk, jangan dipakai
        $this->delete_cache($cache_key);
    } else {
        $cached['from_cache'] = true;
        return $cached;
    }
}

        $url = $this->product_search_url . '?' . $this->common_query();

        // Format payload ini mengikuti file lama yang sudah terbukti dipakai endpoint product search.
        $payload = [
            'keyword' => $keyword,
            'filter' => [
                'single_filters' => [],
                'double_filters' => [],
            ],
            'page_size' => $page_size,
            'cur_page' => $page,
        ];

        $result = $this->request_post($url, $payload, 'search');
        if (
            isset($result['data']['code']) &&
            intval($result['data']['code']) !== 0
        ) {
            $result['success'] = false;
            $result['api_code'] = intval($result['data']['code']);
            $result['message'] = $result['data']['msg'] ?? 'TikTok API returned non-zero code';
            return $result;
        }
        if ($result['success']) {
            $this->set_cache($cache_key, $result);
        }
        return $result;
    }

    public function get_brand_contact($seller_id)
    {
        $seller_id = trim($seller_id);
        $cache_key = 'brand_contact_' . md5($seller_id);
        $cached = $this->get_cache($cache_key);
     if ($cached) {
    $products = $cached['data']['data']['products'] ?? [];

    if (!empty($cached['requires_captcha']) || empty($products)) {
        // cache buruk, jangan dipakai
        $this->delete_cache($cache_key);
    } else {
        $cached['from_cache'] = true;
        return $cached;
    }
}

        $url = $this->brand_contact_url
            . '?seller_id=' . rawurlencode($seller_id)
            . '&' . $this->common_query();

        $result = $this->request_get($url, 'contact');
        if ($result['success']) {
            $this->set_cache($cache_key, $result);
        }
        return $result;
    }

    public function parse_brand_contact($response)
    {
        $data = $response['data'] ?? [];
        $contact = $data['data']['contact_info']
            ?? $data['contact_info']
            ?? [];

        return [
            'email' => !empty($contact['email']) ? trim($contact['email']) : null,
            'whatsapp' => !empty($contact['whatsapp']) ? trim($contact['whatsapp']) : null,
            'raw' => $contact,
        ];
    }

    public function is_session_valid()
    {
        $result = $this->search_brand_product('test', 1, 1);
        return !empty($result['success']) && empty($result['requires_captcha']);
    }
    
    private function delete_cache($key)
{
    if (!$this->CI->db->table_exists('app_cache')) {
        return;
    }

    $this->CI->db
        ->where('cache_key', $key)
        ->delete('app_cache');
}




}
