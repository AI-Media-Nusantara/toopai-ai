<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Fastmoss_model extends CI_Model
{
    private $baseUrl = 'https://www.fastmoss.com';

    public function get_shop_creators($shopId, $page = 1, $pageSize = 5)
    {
        $time = time();
        $cnonce = rand(10000000, 99999999);

        $url = $this->baseUrl . "/api/shop/v3/author"
            . "?id={$shopId}"
            . "&page={$page}"
            . "&order=2,2"
            . "&d_type=28"
            . "&author_product_type=3"
            . "&pagesize={$pageSize}"
            . "&_time={$time}"
            . "&cnonce={$cnonce}";

        $json = $this->request_json(
            $url,
            $this->headers('https://www.fastmoss.com/id/shop-marketing/detail/' . $shopId)
        );

        return $json['data']['list']
            ?? $json['data']['rows']
            ?? $json['data']
            ?? [];
    }

    public function get_product_base($productId) {
    $time = time();
    $cnonce = rand(10000000, 99999999);

    $url = $this->baseUrl . "/api/goods/v3/base"
        . "?product_id={$productId}"
        . "&_time={$time}"
        . "&cnonce={$cnonce}";

    $json = $this->request_json(
        $url,
        $this->headers('https://www.fastmoss.com/id/e-commerce/detail/' . $productId)
    );

    // 🔥 CEK APAKAH PRODUK DITEMUKAN
    if (isset($json['code']) && $json['code'] !== 200) {
        log_message('debug', 'FastMoss product base error: ' . ($json['msg'] ?? 'Unknown error'));
        return [];
    }

    // 🔥 CEK APAKAH PRODUK VALID (region Indonesia)
    $product = $json['data']['product'] ?? [];
    if (!empty($product)) {
        $region = $product['region'] ?? $product['region_name'] ?? '';
        if (strtoupper($region) !== 'ID' && strtoupper($region) !== 'INDONESIA') {
            log_message('debug', 'FastMoss product region is not Indonesia: ' . $region);
            return [];
        }
    }

    return $product;
}

    public function get_product_creators($productId, $page = 1, $pageSize = 10)
    {
        if ($pageSize > 10) {
            $pageSize = 10;
        }

        $time = time();
        $cnonce = rand(10000000, 99999999);

        $url = $this->baseUrl . "/api/goods/v3/author"
            . "?product_id={$productId}"
            . "&order=2,2"
            . "&pagesize={$pageSize}"
            . "&ecommerce_type=all"
            . "&_time={$time}"
            . "&cnonce={$cnonce}";

        $json = $this->request_json(
            $url,
            $this->headers('https://www.fastmoss.com/id/e-commerce/detail/' . $productId)
        );

        $list = $json['data']['list'] ?? [];

        return array_values(array_filter($list, function ($row) {
            return strtoupper($row['region'] ?? '') === 'ID'
                || ($row['region_name'] ?? '') === 'Indonesia';
        }));
    }

    public function get_creator_goods($uid, $page = 1, $pageSize = 10)
    {
        if ($pageSize > 10) {
            $pageSize = 10;
        }

        $time = time();
        $cnonce = rand(10000000, 99999999);

        $url = $this->baseUrl . "/api/author/v3/detail/goodsList"
            . "?page={$page}"
            . "&uid={$uid}"
            . "&date_type=28"
            . "&order=sold_count,2"
            . "&pagesize={$pageSize}"
            . "&_time={$time}"
            . "&cnonce={$cnonce}";

        $json = $this->request_json(
            $url,
            $this->headers('https://www.fastmoss.com/id/influencer/detail/' . $uid)
        );

        return $json['data']['list']
            ?? $json['data']['rows']
            ?? $json['data']['data']
            ?? [];
    }

    public function is_creator_joined_affiliate($creatorUid)
    {
        return false;
    }

    public function debug_product_creators($productId)
    {
        $time = time();
        $cnonce = rand(10000000, 99999999);

        $url = $this->baseUrl . "/api/goods/v3/author"
            . "?product_id={$productId}"
            . "&order=2,2"
            . "&pagesize=10"
            . "&ecommerce_type=all"
            . "&_time={$time}"
            . "&cnonce={$cnonce}";

        $json = $this->request_json(
            $url,
            $this->headers('https://www.fastmoss.com/id/e-commerce/detail/' . $productId)
        );

        return [
            'url' => $url,
            'response_code' => $json['code'] ?? null,
            'message' => $json['msg'] ?? null,
            'is_login' => $json['ext']['is_login'] ?? null,
            'raw' => $json
        ];
    }
    public function get_creator_products_by_uid($creatorUid, $page = 1, $pageSize = 50)
    {
        $time = time();
        $cnonce = rand(10000000, 99999999);

        $url = $this->baseUrl . "/api/author/v3/detail/goodsList"
            . "?page={$page}"
            . "&uid={$creatorUid}"
            . "&date_type=28"  // 28 hari terakhir
            . "&order=sold_count,2"  // Urutkan berdasarkan penjualan
            . "&pagesize={$pageSize}"
            . "&_time={$time}"
            . "&cnonce={$cnonce}";

        $headers = $this->headers(
            'https://www.fastmoss.com/id/influencer/detail/' . $creatorUid
        );

        $json = $this->request_json($url, $headers);

        return $json['data']['list'] 
            ?? $json['data']['rows'] 
            ?? $json['data']['data'] 
            ?? [];
    }

    /**
     * Get ALL products from creator (paginated - get all pages)
     * @param string $creatorUid - FastMoss creator UID
     * @param int $maxPages - Maximum pages to fetch
     * @return array
     */
    public function get_all_creator_products_by_uid($creatorUid, $maxPages = 5)
    {
        $allProducts = [];
        $page = 1;
        $pageSize = 50;

        do {
            $products = $this->get_creator_products_by_uid($creatorUid, $page, $pageSize);
            
            if (empty($products)) {
                break;
            }

            $allProducts = array_merge($allProducts, $products);
            $page++;
            
        } while (count($products) == $pageSize && $page <= $maxPages);

        return $allProducts;
    }
    public function search_creator($username, $region = 'ID')
    {
        $time = time();
        $cnonce = rand(10000000, 99999999);

        $url = $this->baseUrl . "/api/author/search"
            . "?name=" . urlencode($username)
            . "&region={$region}"
            . "&page=1"
            . "&pagesize=10"
            . "&_time={$time}"
            . "&cnonce={$cnonce}";

        $json = $this->request_json(
            $url,
            $this->headers('https://www.fastmoss.com/id/influencer')
        );

        log_message('debug', '[FastMoss][search_creator] raw_json=' . json_encode($json));

        return $json['data']['author_list']
            ?? $json['data']['list']
            ?? $json['data']['rows']
            ?? $json['data']['data']
            ?? [];
    }

    /**
     * Resolve FastMoss numeric UID dari username TikTok.
     *
     * Strategi (berurutan sampai berhasil):
     *   1. Coba pakai username langsung sebagai uid di endpoint baseInfo
     *      (FastMoss menerima unique_id di beberapa versi API)
     *   2. Coba search_creator dengan cookie (endpoint /api/author/search)
     *   3. Coba search_creator tanpa cookie
     *
     * Return string UID jika ditemukan, null jika tidak.
     */
    public function resolve_uid_by_username($username)
    {
        $username = ltrim(trim($username), '@');
        if (empty($username)) return null;

        // ── Strategi 1: username sebagai uid langsung di baseInfo ──
        $uid = $this->_try_uid($username);
        if ($uid) return $uid;

        // ── Strategi 2: search dengan cookie ──────────────────────
        $time   = time();
        $cnonce = rand(10000000, 99999999);
        $url    = $this->baseUrl . "/api/author/search"
            . "?name=" . urlencode($username)
            . "&region=ID"
            . "&page=1"
            . "&pagesize=10"
            . "&_time={$time}"
            . "&cnonce={$cnonce}";

        $json = $this->request_json($url, $this->headers('https://www.fastmoss.com/id/influencer'));
        $list = $json['data']['author_list'] ?? $json['data']['list'] ?? $json['data']['rows'] ?? $json['data']['data'] ?? [];

        if (!empty($list)) {
            foreach ($list as $row) {
                // Cocokkan username secara eksak dulu
                $row_uid = $row['uid'] ?? $row['author_id'] ?? $row['user_id'] ?? null;
                $row_uname = strtolower($row['unique_id'] ?? $row['username'] ?? $row['name'] ?? '');
                if ($row_uid && $row_uname === strtolower($username)) {
                    return (string)$row_uid;
                }
            }
            // Jika tidak ada exact match, ambil UID pertama
            $first = $list[0];
            $uid   = $first['uid'] ?? $first['author_id'] ?? $first['user_id'] ?? null;
            if ($uid) return (string)$uid;
        }

        // ── Strategi 3: search tanpa cookie ───────────────────────
        $json2 = $this->request_json_no_cookie($url, $this->headers('https://www.fastmoss.com/id/influencer'));
        $list2 = $json2['data']['author_list'] ?? $json2['data']['list'] ?? $json2['data']['rows'] ?? $json2['data']['data'] ?? [];

        if (!empty($list2)) {
            foreach ($list2 as $row) {
                $row_uid   = $row['uid'] ?? $row['author_id'] ?? $row['user_id'] ?? null;
                $row_uname = strtolower($row['unique_id'] ?? $row['username'] ?? $row['name'] ?? '');
                if ($row_uid && $row_uname === strtolower($username)) {
                    return (string)$row_uid;
                }
            }
            $first2 = $list2[0];
            $uid2   = $first2['uid'] ?? $first2['author_id'] ?? $first2['user_id'] ?? null;
            if ($uid2) return (string)$uid2;
        }

        return null;
    }

    /**
     * Coba gunakan nilai $candidate sebagai uid di endpoint baseInfo.
     * Jika response valid (code=200, data ada), kembalikan uid yang diterima API.
     */
    private function _try_uid($candidate)
    {
        $time   = time();
        $cnonce = rand(10000000, 99999999);
        $url    = $this->baseUrl . "/api/author/v3/detail/baseInfo"
            . "?uid="    . urlencode($candidate)
            . "&_time="  . $time
            . "&cnonce=" . $cnonce;

        // Coba tanpa cookie dulu
        $json = $this->request_json_no_cookie(
            $url,
            $this->headers('https://www.fastmoss.com/id/influencer/detail/' . $candidate)
        );

        $code = $json['code'] ?? null;
        $data = $json['data'] ?? [];

        if ($code == 200 && !empty($data)) {
            // Ambil uid yang dikembalikan API (bisa numeric)
            $returned_uid = $data['uid']       ?? $data['author_id']
                         ?? $data['user']['uid'] ?? $data['user']['author_id']
                         ?? null;
            if ($returned_uid) return (string)$returned_uid;
        }

        // Coba dengan cookie
        $json2 = $this->request_json(
            $url,
            $this->headers('https://www.fastmoss.com/id/influencer/detail/' . $candidate)
        );
        $code2 = $json2['code'] ?? null;
        $data2 = $json2['data'] ?? [];

        if ($code2 == 200 && !empty($data2)) {
            $returned_uid2 = $data2['uid']       ?? $data2['author_id']
                          ?? $data2['user']['uid'] ?? $data2['user']['author_id']
                          ?? null;
            if ($returned_uid2) return (string)$returned_uid2;
        }

        return null;
    }
    private function request_json($url, $headers = [])
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_ENCODING => '',
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_COOKIE => $this->cookie_string()
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            return [
                'error' => true,
                'message' => $error,
                'raw' => $response
            ];
        }

        $json = json_decode($response, true);

        return is_array($json) ? $json : [
            'error' => true,
            'message' => 'Invalid JSON',
            'raw' => $response
        ];
    }

    private function headers($referer)
    {
        return [
            'accept: application/json, text/plain, */*',
            'accept-language: id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
            'lang: ID_ID',
            'region: ID',
            'source: pc',
            'referer: ' . $referer,
            'user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36'
        ];
    }

    /**
     * ============================================================
     * GET CREATOR BASE INFO (Total GMV, Followers, dll)
     * Mengambil informasi ringkasan creator dari FastMoss.
     *
     * Endpoint: /api/author/v3/detail/summary
     * Tidak memerlukan cookie — data tersedia publik.
     *
     * Field kunci yang dikembalikan:
     *   goods_max_sale_amount → GMV produk terlaris creator (28 hari)
     *   goods_max_sold_count  → jumlah unit terjual terbanyak (28 hari)
     *   goods_sale_country_rank → peringkat penjualan di negara
     *
     * @param  string $uid  FastMoss creator UID (tiktok_open_id)
     * @return array  atau [] jika gagal
     * ============================================================
     */
    public function get_creator_base_info($uid)
    {
        if (empty($uid)) {
            return [];
        }

        $time   = time();
        $cnonce = rand(10000000, 99999999);

        // Gunakan endpoint summary — bisa diakses tanpa cookie
        $url = $this->baseUrl . '/api/author/v3/detail/summary'
            . '?uid='    . urlencode($uid)
            . '&_time='  . $time
            . '&cnonce=' . $cnonce;

        $referer = 'https://www.fastmoss.com/id/influencer/detail/' . $uid;

        // Coba tanpa cookie dulu (tersedia publik)
        $json = $this->request_json_no_cookie($url, $this->headers($referer));

        // Fallback dengan cookie jika gagal
        if (empty($json['data']) || $json['code'] !== 200) {
            $json = $this->request_json($url, $this->headers($referer));
        }

        log_message('debug', '[FastMoss][summary] uid=' . $uid
            . ' code='     . ($json['code'] ?? 'N/A')
            . ' has_data=' . (!empty($json['data']) ? 'yes' : 'no')
            . ' msg='      . ($json['msg'] ?? $json['message'] ?? '')
        );
        log_message('debug', '[FastMoss][summary] raw_json=' . json_encode($json));

        if ($json['code'] !== 200 || empty($json['data'])) {
            return [];
        }

        $d = $json['data'];
        $gmv_28d = floatval($d['goods_max_sale_amount'] ?? 0);

        // total_sales dalam 28 hari
        $sales_28d = intval($d['goods_max_sold_count'] ?? 0);

        // Peringkat penjualan di negara
        $country_rank = intval($d['goods_sale_country_rank'] ?? 0);

        return [
            'uid'          => (string)($d['uid'] ?? $uid),
            'username'     => $d['unique_id']  ?? '',
            'nickname'     => $d['nickname']   ?? '',
            'avatar'       => '',
            'follower_count' => 0,
            'total_gmv'    => 0,      // tidak tersedia tanpa login
            'gmv_28d'      => $gmv_28d,
            'total_sales'  => 0,
            'sales_28d'    => $sales_28d,
            'country_rank' => $country_rank,
            'region'       => $d['region'] ?? 'ID',
            'raw'          => $d,
        ];
    }

    /**
     * ============================================================
     * GET CREATOR BRAND COLLABORATIONS
     * Mengambil daftar brand/shop yang pernah dikolaborasikan creator
     * beserta GMV dan jumlah produk dari setiap brand.
     *
     * Endpoint: /api/author/v3/detail/shopList
     * Tidak memerlukan cookie — cukup _time + cnonce + browser headers
     *
     * @param  string $uid       FastMoss creator UID (tiktok_open_id)
     * @param  int    $page
     * @param  int    $pageSize  Maks 20
     * @return array  ['brands' => [...], 'total' => int, 'raw' => array]
     * ============================================================
     */
    public function get_creator_brand_collabs($uid, $page = 1, $pageSize = 20)
    {
        if (empty($uid)) {
            return ['brands' => [], 'total' => 0, 'error' => 'UID kosong'];
        }

        $time   = time();
        $cnonce = rand(10000000, 99999999);

        $url = $this->baseUrl . '/api/author/v3/detail/shopList'
            . '?uid='      . urlencode($uid)
            . '&page='     . intval($page)
            . '&pagesize=' . intval($pageSize)
            . '&order=gmv,2'       // urutkan by GMV descending
            . '&date_type=28'      // 28 hari terakhir
            . '&_time='    . $time
            . '&cnonce='   . $cnonce;

        $json = $this->request_json_no_cookie(
            $url,
            $this->headers('https://www.fastmoss.com/id/influencer/detail/' . $uid)
        );

        // Gagal / tidak login  → coba fallback pakai cookie
        $is_login = $json['ext']['is_login'] ?? $json['data']['is_login'] ?? null;
        if ($is_login === false || $is_login === 0) {
            log_message('debug', '[FastMoss] shopList: not logged-in, fallback to cookie');
            $json = $this->request_json(
                $url,
                $this->headers('https://www.fastmoss.com/id/influencer/detail/' . $uid)
            );
        }

        // Debug: log response untuk investigasi
        log_message('debug', '[FastMoss][shopList] uid=' . $uid
            . ' code=' . ($json['code'] ?? 'N/A')
            . ' is_login=' . json_encode($json['ext']['is_login'] ?? $json['data']['is_login'] ?? null)
            . ' list_count=' . count($json['data']['list'] ?? $json['data']['rows'] ?? [])
            . ' msg=' . ($json['msg'] ?? $json['message'] ?? '')
        );
        log_message('debug', '[FastMoss][shopList] raw_json=' . json_encode($json));

        if (!empty($json['error'])) {
            return ['brands' => [], 'total' => 0, 'error' => $json['message'] ?? 'Request error'];
        }

        $list  = $json['data']['list'] ?? $json['data']['rows'] ?? $json['data'] ?? [];
        $total = $json['data']['total'] ?? $json['data']['count'] ?? count($list);

        // Normalise setiap baris brand — field names sesuai response FastMoss shopList
        $brands = [];
        foreach ($list as $row) {
            $rating_raw = floatval($row['shop_rating'] ?? 0);
            $rating_val = $rating_raw > 5 ? round($rating_raw / 10, 1) : $rating_raw;
            $brands[] = [
                'shop_id'       => $row['id']            ?? '',
                'shop_name'     => $row['name']          ?? $row['shop_name'] ?? '',
                'shop_logo'     => $row['img']           ?? $row['shop_logo'] ?? '',
                'product_count' => intval($row['product_count'] ?? $row['product_cnt'] ?? $row['goods_count'] ?? 0),
                'sales_count'   => intval($row['sold_count']    ?? $row['sales_count']  ?? 0),
                'gmv'           => floatval($row['sale_amount'] ?? $row['gmv']          ?? $row['sales_amount'] ?? 0),
                'day7_gmv'      => floatval($row['day7_sale_amount'] ?? $row['day7_gmv'] ?? 0),
                'shop_rating'   => $rating_val,
                'region'        => $row['region']        ?? 'ID',
            ];
        }

        return [
            'brands' => $brands,
            'total'  => intval($total),
            'raw'    => $json,
        ];
    }

    /**
     * Ambil semua halaman brand collab (maks $maxPages halaman)
     *
     * @param  string $uid
     * @param  int    $maxPages
     * @return array  flat list of brand rows
     */
    public function get_all_creator_brand_collabs($uid, $maxPages = 5)
    {
        $all      = [];
        $pageSize = 20;

        for ($page = 1; $page <= $maxPages; $page++) {
            $result = $this->get_creator_brand_collabs($uid, $page, $pageSize);

            if (!empty($result['error']) || empty($result['brands'])) {
                break;
            }

            $all = array_merge($all, $result['brands']);

            // Kalau halaman ini lebih sedikit dari pageSize, sudah halaman terakhir
            if (count($result['brands']) < $pageSize) {
                break;
            }
        }

        return $all;
    }

    /**
     * Request JSON ke FastMoss TANPA cookie.
     * Cukup mengandalkan _time, cnonce, dan browser-like headers.
     */
    private function request_json_no_cookie($url, $headers = [])
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_ENCODING       => '',
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            // Sengaja TIDAK kirim cookie
        ]);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['error' => true, 'message' => $error, 'raw' => $response];
        }

        $json = json_decode($response, true);
        return is_array($json) ? $json : ['error' => true, 'message' => 'Invalid JSON', 'raw' => $response];
    }

  private function cookie_string()
{
    // Ambil cookie dari database app_config jika tersedia
    $CI =& get_instance();
    if (isset($CI->db)) {
        $q = $CI->db->select('value')
            ->where('key', 'fastmoss_cookie')
            ->get('app_config')
            ->row();
        if ($q && !empty($q->value)) {
            return $q->value;
        }
    }

    return implode('; ', [
        // Cookie dari FastMoss
        '_rdt_uuid=1778516149665.a640431f-c702-4476-8425-2e96d07c3edc',
        'fd_tk=5d352d1f51f89349473231fca2e44106',
        'fp_visid=79e88b5af28cec684aa089798511c36e',
        'gclid=CjwKCAjw9NjRBhATEiwA_p2J8QGFEzyymWi1J33m2z0DCwjZ8nvrt0-X2mBxqJv730ZH4jlHZq-dWxoCw3QQAvD_BwE',
        'gg_client_id=1771915314.1778516149',
        'NEXT_LOCALE=id',
        'region=ID',
        'TDC_itoken=1048091192%3A1783756943',
        'userTimeZone=Asia%2FJakarta',
        'utm_country=ID',
        'utm_id=ggdnyplace1',
        'utm_lang=id',
        'utm_origin=sa',
        'utm_south=google'
    ]);
}

    /**
     * Public alias untuk cookie_string() — dipakai oleh debug controller.
     */
    public function get_cookie_string_public()
    {
        return $this->cookie_string();
    }
}