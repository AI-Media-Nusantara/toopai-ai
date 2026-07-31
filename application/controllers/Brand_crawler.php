<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Brand_crawler extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');
        $this->load->database();
        $this->load->library('Tiktok_partner_crawler');
    }

    public function test_search()
    {
        $keyword = $this->input->get('keyword') ?: 'hanasui';
        return $this->json($this->crawl_brand($keyword));
    }
public function test_contact()
{
    $seller_id = $this->input->get('seller_id');

    if (empty($seller_id)) {
        return $this->json([
            'success' => false,
            'message' => 'seller_id required'
        ]);
    }

    // Jangan langsung call TikTok contact.
    // Ambil dari DB dulu.
    $contact = $this->get_contact_from_db($seller_id);

    if ($contact) {
        return $this->json([
            'success' => true,
            'status' => 'CONTACT_CACHED',
            'seller_id' => $seller_id,
            'contact' => $contact
        ]);
    }

    // Kalau belum ada, masukkan queue.
    $this->queue_contact_fetch($seller_id, '');

    return $this->json([
        'success' => true,
        'status' => 'CONTACT_PENDING',
        'seller_id' => $seller_id,
        'message' => 'Contact belum tersedia. Masuk queue untuk diproses.',
        'contact' => [
            'email' => '',
            'whatsapp' => ''
        ]
    ]);
}

private function get_contact_from_db($seller_id)
{
    if (!$this->db->table_exists('brand_contacts')) {
        return null;
    }

    $row = $this->db
        ->where('seller_id', $seller_id)
        ->get('brand_contacts')
        ->row_array();

    if (!$row) {
        return null;
    }

    return [
        'email' => $row['email'] ?? '',
        'whatsapp' => $row['whatsapp'] ?? '',
        'updated_at' => $row['updated_at'] ?? null
    ];
}
    public function cek($keyword = '')
    {
        if (empty($keyword)) {
            $keyword = $this->input->get('keyword') ?: 'hanasui';
        }
        return $this->json($this->crawl_brand($keyword));
    }

    private function crawl_brand($keyword)
    {
        $search = $this->tiktok_partner_crawler->search_brand_product($keyword, 1, 1);

      if (!empty($search['requires_captcha'])) {
    $local = $this->get_brand_from_db_or_cache($keyword);

    if ($local) {
        $this->queue_brand_search($keyword);

        return array_merge($local, [
            'success' => true,
            'status' => 'SEARCH_FROM_CACHE',
            'message' => 'TikTok search sedang verifikasi, memakai data lokal/cache.',
            'requires_captcha' => true
        ]);
    }

    $this->queue_brand_search($keyword);

    return [
        'success' => false,
        'keyword' => $keyword,
        'status' => 'SEARCH_PENDING',
        'message' => 'TikTok search sedang verifikasi. Search dimasukkan queue.',
        'requires_captcha' => true
    ];
}

        if (empty($search['success'])) {
            return [
                'success' => false,
                'keyword' => $keyword,
                'status' => 'SEARCH_FAILED',
                'message' => $search['error'] ?? 'Search failed',
                'http_code' => $search['http_code'] ?? null,
                'raw' => $search['data'] ?? null,
            ];
        }

        $products = $search['data']['data']['products'] ?? [];

if (empty($products)) {
    return [
        'success' => false,
        'keyword' => $keyword,
        'status' => 'NOT_FOUND',
        'http_code' => $search['http_code'] ?? null,
        'api_code' => $search['data']['code'] ?? null,
        'api_msg' => $search['data']['msg'] ?? null,
        'message' => 'No products found for keyword: ' . $keyword
    ];
}

        $product = $products[0];
        $shop = $product['shop_info'] ?? [];
        $seller_id = $shop['seller_id'] ?? $shop['global_seller_id'] ?? null;

        if (!$seller_id) {
            return [
                'success' => false,
                'keyword' => $keyword,
                'status' => 'SELLER_NOT_FOUND',
                'message' => 'Seller ID not found for product',
                'from_cache' => !empty($search['from_cache']),
            ];
        }

        $contact = [
    'email' => $shop['contact_info']['email'] ?? '',
    'whatsapp' => $shop['contact_info']['whatsapp'] ?? '',
    'status' => 'CONTACT_PENDING'
];

$this->queue_contact_fetch($seller_id, $shop['shop_name'] ?? '');

        return [
            'success' => true,
            'keyword' => $keyword,
            'shop_name' => $shop['shop_name'] ?? null,
            'seller_id' => $seller_id,
            'whatsapp' => $contact['whatsapp'] ?? null,
            'email' => $contact['email'] ?? null,
            'search_http_code' => $search['http_code'] ?? null,
            'contact_http_code' => $contact_response['http_code'] ?? null,
            'raw_contact' => $contact_response['data'] ?? null,
            'from_cache' => !empty($search['from_cache']),
            'requires_captcha' => false,
        ];
    }

    public function get_brand_products()
    {
        $seller_id = $this->input->get('seller_id');
        $shop_name = $this->input->get('shop_name');

        if (empty($seller_id) || empty($shop_name)) {
            return $this->json([
                'success' => false,
                'message' => 'Seller ID dan shop_name required',
                'products' => [],
            ]);
        }

        $search_result = $this->tiktok_partner_crawler->search_brand_product($shop_name, 1, 50);

        if (!empty($search_result['requires_captcha'])) {
            return $this->json([
                'success' => false,
                'status' => 'CAPTCHA_REQUIRED',
                'message' => 'TikTok meminta verifikasi manual. Refresh session, lalu coba lagi.',
                'requires_captcha' => true,
                'products' => [],
                'http_code' => $search_result['http_code'] ?? null,
            ]);
        }

        if (empty($search_result['success'])) {
            return $this->json([
                'success' => false,
                'status' => 'SEARCH_FAILED',
                'message' => $search_result['error'] ?? 'Search failed',
                'products' => [],
                'http_code' => $search_result['http_code'] ?? null,
                'raw' => $search_result['data'] ?? null,
            ]);
        }

        $products = [];
        $highest_commission = 0;
        $highest_commission_product = null;
        $total_commission_sum = 0;
        $raw_products = $search_result['data']['data']['products'] ?? [];

        foreach ($raw_products as $product) {
            $product_seller_id = $product['shop_info']['seller_id'] ?? '';
            if ($product_seller_id != $seller_id) {
                continue;
            }

            $commission_rate_raw = $product['commission_rate'] ?? '0';
            $commission_rate = floatval($commission_rate_raw) / 100;
            $sales_raw = $product['sales'] ?? '0';
            $sales_numeric = $this->parseSalesToNumeric($sales_raw);
            $price = $product['price']['format_price'] ?? 'Rp0';
            $price_amount = $product['price']['amount'] ?? 0;

            $product_data = [
                'product_id' => $product['product_id'] ?? '',
                'title' => $product['title'] ?? '',
                'shop_name' => $product['shop_info']['shop_name'] ?? $shop_name,
                'seller_id' => $product_seller_id,
                'price' => $price,
                'price_amount' => $price_amount,
                'price_formatted' => $price,
                'image_url' => $product['cover_url'] ?? '',
                'commission_rate_raw' => $commission_rate_raw,
                'commission_rate' => $commission_rate,
                'commission_percent' => $commission_rate . '%',
                'sales_raw' => $sales_raw,
                'sales_numeric' => $sales_numeric,
                'sales_display' => $sales_raw,
                'whatsapp' => $product['shop_info']['contact_info']['whatsapp'] ?? '',
                'email' => $product['shop_info']['contact_info']['email'] ?? '',
                'product_rating' => $product['product_rating'] ?? 0,
                'stock_status' => $product['stock_status'] ?? 0,
            ];

            $products[] = $product_data;
            $total_commission_sum += $commission_rate;

            if ($commission_rate > $highest_commission) {
                $highest_commission = $commission_rate;
                $highest_commission_product = $product_data;
            }
        }

        usort($products, function ($a, $b) {
            return $b['sales_numeric'] <=> $a['sales_numeric'];
        });

        $avg_commission = count($products) > 0 ? round($total_commission_sum / count($products), 2) : 0;

        return $this->json([
            'success' => true,
            'products' => $products,
            'total' => count($products),
            'seller_id' => $seller_id,
            'shop_name' => $shop_name,
            'highest_commission' => $highest_commission,
            'highest_commission_product' => $highest_commission_product,
            'avg_commission' => $avg_commission,
            'from_cache' => !empty($search_result['from_cache']),
        ]);
    }

    public function check_session()
    {
        return $this->json([
            'success' => true,
            'session_valid' => $this->tiktok_partner_crawler->is_session_valid(),
            'session_status' => $this->tiktok_partner_crawler->get_session_status(),
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    }

    public function update_session()
    {
        $payload = json_decode($this->input->raw_input_stream, true);

        if (!is_array($payload)) {
            return $this->json([
                'success' => false,
                'message' => 'Invalid JSON payload',
            ]);
        }

        $result = $this->tiktok_partner_crawler->save_session($payload);
        return $this->json($result);
    }

    public function clear_cache()
    {
        $this->tiktok_partner_crawler->clear_cache();
        return $this->json([
            'success' => true,
            'message' => 'Cache cleared',
        ]);
    }

    public function refresh_session()
    {
        return $this->json([
            'success' => false,
            'message' => 'Manual refresh required. Ambil session lengkap dari browser aktif lalu POST ke /brand_crawler/update_session.',
            'required_fields' => [
                'cookie', 'ms_token', 'x_bogus', 'signature', 'bsid', 'fp',
                'user_agent', 'browser_platform', 'browser_language', 'timezone_name',
                'screen_width', 'screen_height', 'device_id',
                'turing.xmsi', 'turing.xmst', 'turing.version_web_id_ID', 'turing.version_bdturing_en'
            ],
        ]);
    }

    public function test_raw()
    {
        $keyword = $this->input->get('keyword') ?: 'kime';
        $search_result = $this->tiktok_partner_crawler->search_brand_product($keyword, 1, 50);

        $data = $search_result['data'] ?? [];
        $inner_data = $data['data'] ?? [];
        $products = $inner_data['products'] ?? [];

        return $this->json([
            'debug_info' => [
                'timestamp' => date('Y-m-d H:i:s'),
                'keyword' => $keyword,
                'endpoint' => 'search_brand_product',
            ],
            'response_status' => [
                'success' => $search_result['success'] ?? false,
                'http_code' => $search_result['http_code'] ?? null,
                'errno' => $search_result['errno'] ?? null,
                'error' => $search_result['error'] ?? null,
                'requires_captcha' => $search_result['requires_captcha'] ?? false,
                'from_cache' => $search_result['from_cache'] ?? false,
            ],
            'request_payload' => $search_result['payload'] ?? null,
            'top_level_structure' => [
                'keys' => is_array($data) ? array_keys($data) : [],
                'has_code' => isset($data['code']),
                'code_value' => $data['code'] ?? null,
                'has_msg' => isset($data['msg']),
                'msg_value' => $data['msg'] ?? null,
                'has_data' => isset($data['data']),
            ],
            'data_structure' => [
                'keys' => is_array($inner_data) ? array_keys($inner_data) : [],
                'has_products' => isset($inner_data['products']),
                'total_products' => count($products),
                'has_total' => isset($inner_data['total']),
                'total_value' => $inner_data['total'] ?? null,
                'has_next_page_token' => isset($inner_data['next_page_token']),
                'next_page_token' => $inner_data['next_page_token'] ?? null,
            ],
            'all_products_summary' => $this->summarize_products($products),
            'raw' => $data,
        ]);
    }

    public function test_raw_contact()
    {
        $seller_id = $this->input->get('seller_id') ?: '7494474196244400831';
        $contact_result = $this->tiktok_partner_crawler->get_brand_contact($seller_id);

        return $this->json([
            'debug_info' => [
                'timestamp' => date('Y-m-d H:i:s'),
                'seller_id' => $seller_id,
            ],
            'response_status' => [
                'success' => $contact_result['success'] ?? false,
                'http_code' => $contact_result['http_code'] ?? null,
                'errno' => $contact_result['errno'] ?? null,
                'error' => $contact_result['error'] ?? null,
                'requires_captcha' => $contact_result['requires_captcha'] ?? false,
                'from_cache' => $contact_result['from_cache'] ?? false,
            ],
            'raw_response' => $contact_result['raw'] ?? null,
            'parsed_data' => $contact_result['data'] ?? null,
            'parsed_contact' => $this->tiktok_partner_crawler->parse_brand_contact($contact_result),
        ]);
    }

    private function summarize_products($products)
    {
        $summary = [];
        foreach ($products as $idx => $prod) {
            $summary[] = [
                'index' => $idx + 1,
                'product_id' => $prod['product_id'] ?? null,
                'title' => substr($prod['title'] ?? '', 0, 80),
                'shop_name' => $prod['shop_info']['shop_name'] ?? null,
                'seller_id' => $prod['shop_info']['seller_id'] ?? null,
                'price' => $prod['price']['format_price'] ?? null,
                'commission_rate' => $prod['commission_rate'] ?? null,
                'sales' => $prod['sales'] ?? null,
                'rating' => $prod['product_rating'] ?? null,
            ];
        }
        return $summary;
    }

    private function parseSalesToNumeric($sales_string)
    {
        if (empty($sales_string)) return 0;

        $sales_string = strtolower($sales_string);
        $sales_string = str_replace(['sold', 'terjual', ' '], '', $sales_string);
        $sales_string = str_replace(',', '.', $sales_string);

        $multiplier = 1;
        if (strpos($sales_string, 'jt') !== false) {
            $multiplier = 1000000;
            $sales_string = str_replace('jt', '', $sales_string);
        } elseif (strpos($sales_string, 'rb') !== false) {
            $multiplier = 1000;
            $sales_string = str_replace('rb', '', $sales_string);
        }

        return intval(floatval($sales_string) * $multiplier);
    }

    private function json($data)
    {
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
    
    private function queue_contact_fetch($seller_id, $shop_name = '')
{
    if (empty($seller_id)) {
        return;
    }

    if (!$this->db->table_exists('brand_contact_queue')) {
        return;
    }

    $exists = $this->db
        ->where('seller_id', $seller_id)
        ->where_in('status', ['pending', 'processing'])
        ->get('brand_contact_queue')
        ->row();

    if ($exists) {
        return;
    }

    $this->db->insert('brand_contact_queue', [
        'seller_id' => $seller_id,
        'shop_name' => $shop_name,
        'status' => 'pending',
        'attempts' => 0,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
}

// public function process_contact_queue()
// {
//     if (!$this->db->table_exists('brand_contact_queue')) {
//         return $this->json([
//             'success' => false,
//             'message' => 'brand_contact_queue table not found'
//         ]);
//     }

//     // Recovery job yang nyangkut lebih dari 10 menit
//     $this->db
//         ->where('status', 'processing')
//         ->where('updated_at <', date('Y-m-d H:i:s', strtotime('-10 minutes')))
//         ->update('brand_contact_queue', [
//             'status' => 'pending',
//             'last_error' => 'Recovered stuck processing job',
//             'updated_at' => date('Y-m-d H:i:s')
//         ]);

//     // Ambil maksimal 3 queue sekaligus
//     $jobs = $this->db
//         ->where('status', 'pending')
//         ->order_by('created_at', 'ASC')
//         ->limit(3)
//         ->get('brand_contact_queue')
//         ->result();

//     if (empty($jobs)) {
//         return $this->json([
//             'success' => true,
//             'message' => 'No pending contact queue'
//         ]);
//     }

//     $results = [];

//     foreach ($jobs as $job) {

//         $this->db->where('id', $job->id)->update('brand_contact_queue', [
//             'status' => 'processing',
//             'updated_at' => date('Y-m-d H:i:s')
//         ]);

//         $result = $this->tiktok_partner_crawler->get_brand_contact($job->seller_id);

//         if (!empty($result['requires_captcha']) || !$result['success']) {

//             $this->db->where('id', $job->id)->update('brand_contact_queue', [
//                 'status' => 'need_refresh',
//                 'attempts' => intval($job->attempts) + 1,
//                 'last_error' => json_encode($result),
//                 'updated_at' => date('Y-m-d H:i:s')
//             ]);

//             $results[] = [
//                 'seller_id' => $job->seller_id,
//                 'status' => 'need_refresh'
//             ];

//             continue;
//         }

//         $contact = $this->tiktok_partner_crawler->parse_brand_contact($result);

//         $this->db->replace('brand_contacts', [
//             'seller_id' => $job->seller_id,
//             'shop_name' => $job->shop_name,
//             'email' => $contact['email'] ?? '',
//             'whatsapp' => $contact['whatsapp'] ?? '',
//             'raw_response' => json_encode($result['data'] ?? []),
//             'updated_at' => date('Y-m-d H:i:s')
//         ]);

//         $this->db->where('id', $job->id)->update('brand_contact_queue', [
//             'status' => 'done',
//             'last_error' => null,
//             'updated_at' => date('Y-m-d H:i:s')
//         ]);

//         $results[] = [
//             'seller_id' => $job->seller_id,
//             'status' => 'done',
//             'contact' => $contact
//         ];
//     }

//     return $this->json([
//         'success' => true,
//         'processed' => count($results),
//         'results' => $results
//     ]);
// }
public function process_contact_queue() {
    if (!$this->db->table_exists('brand_contact_queue')) {
        return $this->json([
            'success' => false,
            'message' => 'brand_contact_queue table not found'
        ]);
    }
    
    // Recovery job yang nyangkut lebih dari 10 menit
    $this->db
        ->where('status', 'processing')
        ->where('updated_at <', date('Y-m-d H:i:s', strtotime('-10 minutes')))
        ->update('brand_contact_queue', [
            'status' => 'pending',
            'last_error' => 'Recovered stuck processing job',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    
    // Ambil job
    $jobs = $this->db
        ->where('status', 'pending')
        ->order_by('created_at', 'ASC')
        ->limit(3)
        ->get('brand_contact_queue')
        ->result();
    
    if (empty($jobs)) {
        return $this->json([
            'success' => true,
            'message' => 'No pending contact queue'
        ]);
    }
    
    $results = [];
    
    foreach ($jobs as $job) {
        $this->db->where('id', $job->id)->update('brand_contact_queue', [
            'status' => 'processing',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        // 🔥 GET CONTACT DARI SELLER ID
        $result = $this->tiktok_partner_crawler->get_brand_contact($job->seller_id);
        
        if (!empty($result['requires_captcha']) || !$result['success']) {
            $this->db->where('id', $job->id)->update('brand_contact_queue', [
                'status' => 'need_refresh',
                'attempts' => intval($job->attempts) + 1,
                'last_error' => json_encode($result),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            $results[] = [
                'seller_id' => $job->seller_id,
                'status' => 'need_refresh'
            ];
            continue;
        }
        
        // 🔥 PARSE CONTACT
        $contact = $this->tiktok_partner_crawler->parse_brand_contact($result);
        
        // 🔥 SIMPAN KE brand_contacts
        $this->db->replace('brand_contacts', [
            'seller_id' => $job->seller_id,
            'shop_name' => $job->shop_name,
            'email' => $contact['email'] ?? '',
            'whatsapp' => $contact['whatsapp'] ?? '',
            'raw_response' => json_encode($result['data'] ?? []),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        // 🔥 UPDATE BRANDS dengan whatsapp
        if (!empty($contact['whatsapp'])) {
            $this->db->where('seller_id', $job->seller_id)->update('brands', [
                'whatsapp_number' => $contact['whatsapp'],
                'email' => $contact['email'] ?? '',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        // 🔥 UPDATE QUEUE
        $this->db->where('id', $job->id)->update('brand_contact_queue', [
            'status' => 'done',
            'last_error' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        $results[] = [
            'seller_id' => $job->seller_id,
            'status' => 'done',
            'contact' => $contact
        ];
    }
    
    return $this->json([
        'success' => true,
        'processed' => count($results),
        'results' => $results
    ]);
}
private function queue_brand_search($keyword)
{
    if (!$this->db->table_exists('brand_search_queue')) {
        return;
    }

    $exists = $this->db
        ->where('keyword', $keyword)
        ->where_in('status', ['pending', 'processing'])
        ->get('brand_search_queue')
        ->row();

    if ($exists) {
        return;
    }

    $this->db->insert('brand_search_queue', [
        'keyword' => $keyword,
        'status' => 'pending',
        'attempts' => 0,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
}

private function get_brand_from_db_or_cache($keyword)
{
    if (!$this->db->table_exists('brand')) {
        return null;
    }

    $row = $this->db
        ->like('brand_name', $keyword)
        ->or_like('shop_name', $keyword)
        ->order_by('updated_at', 'DESC')
        ->get('brand')
        ->row_array();

    if (!$row) {
        return null;
    }

    return [
        'keyword' => $keyword,
        'shop_name' => $row['shop_name'] ?? $row['brand_name'] ?? '',
        'seller_id' => $row['seller_id'] ?? '',
        'whatsapp' => $row['whatsapp_number'] ?? '',
        'email' => $row['email'] ?? '',
        'contact_status' => 'LOCAL_CACHE'
    ];
}

public function process_search_queue() {
    if (!$this->db->table_exists('brand_search_queue')) {
        return $this->json([
            'success' => false,
            'message' => 'brand_search_queue table not found'
        ]);
    }
    
    // Recovery job yang nyangkut lebih dari 10 menit
    $this->db
        ->where('status', 'processing')
        ->where('updated_at <', date('Y-m-d H:i:s', strtotime('-10 minutes')))
        ->update('brand_search_queue', [
            'status' => 'pending',
            'last_error' => 'Recovered stuck processing job',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    
    // 🔥 AMBIL JOB: pending + failed + need_retry + not_found (dengan batas attempts < 3)
    $jobs = $this->db
        ->where_in('status', ['pending', 'failed', 'need_retry', 'not_found'])
        ->where('attempts <', 3)  // Maksimal 3 kali percobaan
        ->order_by('attempts ASC, created_at ASC')
        ->limit(5)
        ->get('brand_search_queue')
        ->result();
    
    if (empty($jobs)) {
        return $this->json([
            'success' => true,
            'message' => 'No pending search queue'
        ]);
    }
    
    $results = [];
    
    foreach ($jobs as $job) {
        // Update status ke processing + increment attempts
        $new_attempts = intval($job->attempts) + 1;
        $this->db->where('id', $job->id)->update('brand_search_queue', [
            'status' => 'processing',
            'attempts' => $new_attempts,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        // 🔥 SEARCH BRAND DI TIKTOK
        $search_result = $this->tiktok_partner_crawler->search_brand_product($job->keyword, 1, 10);
        
        // Cek captcha
        if (!empty($search_result['requires_captcha'])) {
            $this->db->where('id', $job->id)->update('brand_search_queue', [
                'status' => 'need_retry',
                'last_error' => 'Captcha required',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            $results[] = [
                'keyword' => $job->keyword,
                'status' => 'need_retry',
                'attempts' => $new_attempts,
                'requires_captcha' => true
            ];
            continue;
        }
        
        // Cek apakah search berhasil
        if (!$search_result['success']) {
            // Jika attempts sudah 3, tandai sebagai failed_permanent
            if ($new_attempts >= 3) {
                $status = 'failed_permanent';
                $last_error = 'Max attempts reached: ' . ($search_result['error'] ?? 'Search failed');
            } else {
                $status = 'failed';
                $last_error = $search_result['error'] ?? 'Search failed';
            }
            
            $this->db->where('id', $job->id)->update('brand_search_queue', [
                'status' => $status,
                'last_error' => $last_error,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            $results[] = [
                'keyword' => $job->keyword,
                'status' => $status,
                'attempts' => $new_attempts,
                'message' => $last_error
            ];
            continue;
        }
        
        // 🔥 EKSTRAK SELLER ID
        $products = $search_result['data']['data']['products'] ?? [];
        $seller_id = null;
        $shop_name = null;
        
        foreach ($products as $product) {
            $shop = $product['shop_info'] ?? [];
            $current_seller_id = $shop['seller_id'] ?? null;
            $current_shop_name = $shop['shop_name'] ?? '';
            
            // Cek apakah shop_name mengandung keyword brand
            if ($current_seller_id && stripos($current_shop_name, $job->keyword) !== false) {
                $seller_id = $current_seller_id;
                $shop_name = $current_shop_name;
                break;
            }
        }
        
        // Jika tidak ada yang match, ambil produk pertama
        if (!$seller_id && !empty($products)) {
            $shop = $products[0]['shop_info'] ?? [];
            $seller_id = $shop['seller_id'] ?? null;
            $shop_name = $shop['shop_name'] ?? '';
        }
        
        if ($seller_id) {
            // 🔥 UPDATE BRAND dengan seller_id
            $this->db->where('name', $job->keyword)->update('brands', [
                'seller_id' => $seller_id,
                'shop_name' => $shop_name ?: $job->keyword,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // 🔥 MASUKKAN KE CONTACT QUEUE
            $this->db->insert('brand_contact_queue', [
                'seller_id' => $seller_id,
                'shop_name' => $shop_name ?: $job->keyword,
                'status' => 'pending',
                'attempts' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Update queue status
            $this->db->where('id', $job->id)->update('brand_search_queue', [
                'status' => 'completed',
                'last_error' => 'Seller ID found: ' . $seller_id,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            $results[] = [
                'keyword' => $job->keyword,
                'status' => 'completed',
                'seller_id' => $seller_id,
                'attempts' => $new_attempts,
                'message' => 'Seller ID found and added to contact queue'
            ];
            
            log_message('info', "Search queue: Found seller_id {$seller_id} for keyword '{$job->keyword}'");
            
        } else {
            // Tidak ditemukan seller_id
            if ($new_attempts >= 3) {
                $status = 'not_found_permanent';
                $last_error = 'Max attempts reached: Seller ID not found';
            } else {
                $status = 'not_found';
                $last_error = 'Seller ID not found';
            }
            
            $this->db->where('id', $job->id)->update('brand_search_queue', [
                'status' => $status,
                'last_error' => $last_error,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            $results[] = [
                'keyword' => $job->keyword,
                'status' => $status,
                'attempts' => $new_attempts,
                'message' => $last_error
            ];
        }
    }
    
    return $this->json([
        'success' => true,
        'processed' => count($results),
        'results' => $results
    ]);
}

}
