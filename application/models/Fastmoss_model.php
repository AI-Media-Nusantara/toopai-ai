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

  private function cookie_string()
{
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


}