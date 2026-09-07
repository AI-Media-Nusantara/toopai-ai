<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Fastmoss_scraper extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Fastmoss_model');
    }

    public function run_shop($brand = 'HONNETE', $shopId = '7496287899690829928')
    {
        header('Content-Type: application/json');

        $creators = $this->Fastmoss_model->get_shop_creators($shopId, 1, 5);
        $result = [];

        foreach ($creators as $creator) {
            $uid = $creator['uid'] ?? $creator['author_id'] ?? $creator['id'] ?? null;

            if (!$uid) {
                continue;
            }

            if ($this->Fastmoss_model->is_creator_joined_affiliate($uid)) {
                continue;
            }

            $products = $this->Fastmoss_model->get_creator_goods($uid, 1, 10);

            $creatorRow = [
                'brand' => $brand,
                'shop_id' => $shopId,
                'creator_uid' => $uid,
                'creator_name' => $creator['nickname'] ?? $creator['name'] ?? $creator['author_name'] ?? null,
                'creator_sold_28d' => $creator['sold_count'] ?? $creator['sales'] ?? 0,
                'creator_gmv_28d' => $creator['gmv'] ?? $creator['sale_amount'] ?? 0,
                'influencer_url' => 'https://www.fastmoss.com/id/influencer/detail/' . $uid,
                'products' => []
            ];

            foreach ($products as $product) {
                $creatorRow['products'][] = [
                    'product_id' => $product['goods_id'] ?? $product['product_id'] ?? $product['id'] ?? null,
                    'product_name' => $product['goods_name'] ?? $product['product_name'] ?? $product['title'] ?? $product['name'] ?? null,
                    'product_sold' => $product['sold_count'] ?? $product['sales'] ?? $product['sold'] ?? 0,
                    'product_gmv' => $product['gmv'] ?? $product['sales_amount'] ?? $product['sale_amount'] ?? $product['amount'] ?? 0
                ];
            }

            $result[] = $creatorRow;
        }

        echo json_encode([
            'status' => true,
            'type' => 'shop',
            'brand' => $brand,
            'shop_id' => $shopId,
            'total_creators' => count($result),
            'rows' => $result
        ], JSON_PRETTY_PRINT);
    }

    public function run_product()
{
    header('Content-Type: application/json');

    $productId = $this->input->post('product_id', true);

    if (!$productId) {
        echo json_encode([
            'status' => false,
            'message' => 'product_id wajib diisi'
        ], JSON_PRETTY_PRINT);
        return;
    }

    $product = $this->Fastmoss_model->get_product_base($productId);
    $creators = $this->Fastmoss_model->get_product_creators($productId, 1, 10);

    $rows = [];

    foreach ($creators as $creator) {
        $uid = $creator['uid'] ?? null;

        $rows[] = [
            'product_id' => $productId,
            'product_name' => $product['title'] ?? null,
            'product_region' => $product['region_name'] ?? $product['region'] ?? null,
            'creator_uid' => $uid,
            'creator_username' => $creator['unique_id'] ?? null,
            'creator_name' => $creator['nickname'] ?? null,
            'sold_from_this_product' => $creator['sold_count'] ?? 0,
            'gmv_from_this_product' => $creator['sale_amount'] ?? 0,
            'gmv_from_this_product_show' => $creator['sale_amount_show'] ?? null,
            'followers' => $creator['follower_count'] ?? 0,
            'followers_show' => $creator['follower_count_show'] ?? null,
            'region' => $creator['region_name'] ?? $creator['region'] ?? null,
            'start_promoting' => $creator['start_promoting'] ?? null,
            'influencer_url' => $uid
                ? 'https://www.fastmoss.com/id/influencer/detail/' . $uid
                : null
        ];
    }

    echo json_encode([
        'status' => true,
        'type' => 'product',
        'source_product_id' => $productId,
        'product_name' => $product['title'] ?? null,
        'product_region' => $product['region_name'] ?? $product['region'] ?? null,
        'product_sold_total' => $product['sold_count'] ?? 0,
        'product_gmv_total' => $product['sale_amount'] ?? 0,
        'product_gmv_total_show' => $product['sale_amount_show'] ?? null,
        'total_creators' => count($rows),
        'rows' => $rows
    ], JSON_PRETTY_PRINT);
}

    public function debug_product_creators($productId = '1735421655417849149')
    {
        header('Content-Type: application/json');

        echo json_encode(
            $this->Fastmoss_model->debug_product_creators($productId),
            JSON_PRETTY_PRINT
        );
    }

    public function test_product_base($productId = '1735421655417849149')
    {
        header('Content-Type: application/json');

        echo json_encode([
            'status' => true,
            'product_id' => $productId,
            'data' => $this->Fastmoss_model->get_product_base($productId)
        ], JSON_PRETTY_PRINT);
    }

    public function test_creator_goods($uid = '7412633772170331152')
    {
        header('Content-Type: application/json');

        $products = $this->Fastmoss_model->get_creator_goods($uid, 1, 10);

        echo json_encode([
            'status' => true,
            'uid' => $uid,
            'total' => count($products),
            'rows' => $products
        ], JSON_PRETTY_PRINT);
    }

    public function debug_base_info($uid = '7091513277024715803')
    {
        header('Content-Type: application/json');

        $time   = time();
        $cnonce = rand(10000000, 99999999);
        $url    = 'https://www.fastmoss.com/api/author/v3/detail/baseInfo'
            . '?uid='    . urlencode($uid)
            . '&_time='  . $time
            . '&cnonce=' . $cnonce;

        $this->load->model('Fastmoss_model');

        // Raw response dengan cookie
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'accept: application/json, text/plain, */*',
                'accept-language: id-ID,id;q=0.9',
                'lang: ID_ID',
                'region: ID',
                'source: pc',
                'referer: https://www.fastmoss.com/id/influencer/detail/' . $uid,
                'user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36',
            ],
            CURLOPT_ENCODING       => '',
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_COOKIE         => $this->Fastmoss_model->get_cookie_string_public(),
        ]);
        $raw_with_cookie = curl_exec($ch);
        curl_close($ch);

        // Raw response tanpa cookie
        $ch2 = curl_init();
        curl_setopt_array($ch2, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'accept: application/json, text/plain, */*',
                'accept-language: id-ID,id;q=0.9',
                'lang: ID_ID',
                'region: ID',
                'source: pc',
                'referer: https://www.fastmoss.com/id/influencer/detail/' . $uid,
                'user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36',
            ],
            CURLOPT_ENCODING       => '',
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        $raw_no_cookie = curl_exec($ch2);
        curl_close($ch2);

        // Hasil parse
        $parsed_result = $this->Fastmoss_model->get_creator_base_info($uid);

        echo json_encode([
            'uid'              => $uid,
            'url'              => $url,
            'parsed_result'    => $parsed_result,
            'raw_with_cookie'  => json_decode($raw_with_cookie, true),
            'raw_no_cookie'    => json_decode($raw_no_cookie, true),
        ], JSON_PRETTY_PRINT);
    }
}