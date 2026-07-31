<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CreatorProduct_model extends CI_Model {
    
    private $table = 'creator_products';
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Save creator products from FastMoss
     * @param int $creator_id - ID dari tabel creators
     * @param string $creator_username - Username creator
     * @param array $products - Array produk dari FastMoss
     * @return array
     */
 public function save_products($creator_id, $creator_username, $products) {
        $result = [
            'success' => false,
            'total_processed' => 0,
            'saved' => 0,
            'updated' => 0,
            'matched' => 0,
            'not_matched' => 0,
            'errors' => 0,
            'details' => []
        ];

        // Ambil semua product_id dari affiliate_products untuk match
        $affiliate_products = $this->db->select('product_id')
            ->where('review_status', 'APPROVED')
            ->get('affiliate_products')
            ->result();
        
        $affiliate_product_ids = array_column($affiliate_products, 'product_id');
        
        foreach ($products as $product) {
            $product_id = $product['product_id'] ?? null;
            if (empty($product_id)) {
                $result['errors']++;
                continue;
            }

            $result['total_processed']++;
            
            // Cek apakah produk sudah ada
            $existing = $this->db->where('creator_id', $creator_id)
                ->where('product_id', $product_id)
                ->get($this->table)
                ->row();

            // Cek apakah produk ada di affiliate_products (match)
            $is_matched = in_array($product_id, $affiliate_product_ids);
            
            // Category handling (bisa array atau string)
            $category = $product['category'] ?? '';
            if (is_array($category)) {
                $category = implode(', ', $category);
            }
            
            // Format commission_rate (bisa string dengan %)
            $commission = $product['commission_rate'] ?? null;
            if (is_numeric($commission)) {
                $commission = $commission . '%';
            }
            
            // Data untuk disimpan - SESUAI DENGAN STRUKTUR TABEL
            $data = [
                'creator_id' => $creator_id,
                'creator_username' => $creator_username,
                'product_id' => $product_id,
                'product_name' => $product['product_name'] ?? '',
                'price' => $product['price'] ?? 0,
                'sales_count' => $product['sales_count'] ?? 0,
                'gmv' => $product['gmv'] ?? 0,
                'commission_rate' => $commission,
                'image_url' => $product['image_url'] ?? '',
                'shop_name' => $product['shop_name'] ?? '',
                'category' => $category,
                'inventory' => $product['inventory'] ?? 0,
                'product_url' => $product['product_url'] ?? null,
                'raw_data' => json_encode($product),
                'is_matched' => $is_matched ? 1 : 0,
                'matched_product_id' => $is_matched ? $product_id : null,
                'last_sync' => date('Y-m-d H:i:s')
            ];

            if ($existing) {
                // Update existing - hapus created_at dari update
                unset($data['created_at']);
                $this->db->where('id', $existing->id)->update($this->table, $data);
                $result['updated']++;
                $result['details'][] = [
                    'product_id' => $product_id,
                    'action' => 'updated',
                    'is_matched' => $is_matched
                ];
            } else {
                // Insert new
                $data['created_at'] = date('Y-m-d H:i:s');
                $this->db->insert($this->table, $data);
                $result['saved']++;
                $result['details'][] = [
                    'product_id' => $product_id,
                    'action' => 'inserted',
                    'is_matched' => $is_matched
                ];
            }

            if ($is_matched) {
                $result['matched']++;
            } else {
                $result['not_matched']++;
            }
        }

        $result['success'] = true;
        return $result;
    }
    /**
     * Get products by creator_id
     */
 public function get_by_creator($creator_id, $only_matched = false) {
        $this->db->where('creator_id', $creator_id);
        if ($only_matched) {
            $this->db->where('is_matched', 1);
        }
        return $this->db->order_by('sales_count', 'DESC')
            ->get($this->table)
            ->result();
    }

    /**
     * Get matched products for assign link (Task 2)
     * Hanya produk yang match dengan affiliate_products
     */
    public function get_matched_for_assign($creator_username) {
    $sql = "
        SELECT 
            cp.product_id,
            cp.product_name,
            cp.sales_count,
            cp.gmv,
            cp.commission_rate,
            cp.image_url,
            cp.shop_name,
            cp.category,
            cp.is_matched,
            ap.product_name as affiliate_product_name,
            ap.price as affiliate_price,
            ap.image_url as affiliate_image,
            ap.shop_name as affiliate_shop,
            ap.category as affiliate_category,
            ap.open_commission_rate,
            bal.affiliate_link,
            bal.commission_rate as link_commission,
            bal.created_by_name as link_created_by,
            bal.campaign_id as link_campaign_id,
            bal.status as link_status
        FROM creator_products cp
        LEFT JOIN affiliate_products ap 
            ON cp.product_id COLLATE utf8mb4_general_ci = ap.product_id COLLATE utf8mb4_general_ci
        LEFT JOIN bd_affiliate_links bal
            ON cp.product_id COLLATE utf8mb4_general_ci = bal.product_id COLLATE utf8mb4_general_ci
            AND bal.status = 'ACTIVE'
        WHERE cp.creator_username COLLATE utf8mb4_general_ci = ?
            AND cp.is_matched = 1
            AND ap.review_status = 'APPROVED'
        ORDER BY cp.sales_count DESC
    ";
    
    $query = $this->db->query($sql, [$creator_username]);
    return $query->result();
}
public function get_stats_by_username($creator_username) {
    $total = $this->db->where('creator_username', $creator_username)->count_all_results($this->table);
    $matched = $this->db->where('creator_username', $creator_username)->where('is_matched', 1)->count_all_results($this->table);
    $not_matched = $this->db->where('creator_username', $creator_username)->where('is_matched', 0)->count_all_results($this->table);
    $total_gmv = $this->db->select_sum('gmv')->where('creator_username', $creator_username)->get($this->table)->row()->gmv ?? 0;
    $total_sales = $this->db->select_sum('sales_count')->where('creator_username', $creator_username)->get($this->table)->row()->sales_count ?? 0;
    
    return [
        'total' => $total,
        'matched' => $matched,
        'not_matched' => $not_matched,
        'total_gmv' => $total_gmv,
        'total_sales' => $total_sales
    ];
}

    /**
     * Get stats for a creator
     */
   public function get_stats($creator_id) {
        $total = $this->db->where('creator_id', $creator_id)->count_all_results($this->table);
        $matched = $this->db->where('creator_id', $creator_id)->where('is_matched', 1)->count_all_results($this->table);
        $not_matched = $this->db->where('creator_id', $creator_id)->where('is_matched', 0)->count_all_results($this->table);
        $total_gmv = $this->db->select_sum('gmv')->where('creator_id', $creator_id)->get($this->table)->row()->gmv ?? 0;
        $total_sales = $this->db->select_sum('sales_count')->where('creator_id', $creator_id)->get($this->table)->row()->sales_count ?? 0;
        
        return [
            'total' => $total,
            'matched' => $matched,
            'not_matched' => $not_matched,
            'total_gmv' => $total_gmv,
            'total_sales' => $total_sales
        ];
    }
    
}