<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model {
    
    private $table = 'affiliate_products';
    private $brand_products_table = 'brand_products';
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Get products by brand - HANDLE BOTH MANUAL AND API PRODUCTS
     */
   public function get_products_by_brand($brand_id) {
    // Query untuk produk manual (dari brand_products)
    $manual_products = $this->db->select('
            bp.product_id,
            bp.product_name,
            bp.price,
            bp.commission_rate,
            0 as sales_count,
            0 as gmv,
            bp.image_url,
            "" as shop_name,
            0 as total_orders,
            0 as total_gmv,
            0 as total_commission,
            bp.affiliate_link,
            "manual" as source
        ', FALSE)
        ->from($this->brand_products_table . ' bp')
        ->where('bp.brand_id', $brand_id)
        ->where('(bp.source = "manual" OR bp.source IS NULL)', NULL, FALSE)
        ->get()
        ->result();
    
    // Query untuk produk dari API (affiliate_products)
    $api_products = $this->db->select('
            ap.product_id,
            ap.product_name,
            ap.price,
            ap.commission_rate,
            ap.sales_count,
            ap.gmv,
            ap.image_url,
            ap.shop_name,
            COUNT(o.order_id) as total_orders,
            SUM(o.gmv) as total_gmv,
            SUM(o.actual_commission) as total_commission,
            "" as affiliate_link,
            "api" as source
        ', FALSE)
        ->from($this->table . ' ap')
        ->join($this->brand_products_table . ' bp', 'ap.product_id = bp.product_id', 'inner')
        ->join('affiliate_orders o', 'ap.product_id = o.product_id AND ap.campaign_id = o.campaign_id', 'left')
        ->where('bp.brand_id', $brand_id)
        ->where('bp.source', 'api')
        ->group_by('ap.product_id, ap.campaign_id')
        ->order_by('total_gmv', 'DESC')
        ->get()
        ->result();
    
    // Gabungkan kedua hasil
    $all_products = array_merge($manual_products, $api_products);
    
    return $all_products;
}
    
    /**
     * Get brand products with stats - FIXED VERSION
     */
    public function get_brand_products_with_stats($brand_id) {
        // Untuk produk manual (yang diinput BD)
        $manual_sql = "
            SELECT 
                bp.product_id,
                bp.product_name,
                bp.price,
                NULL as commission_rate,
                0 as sales_count,
                0 as gmv,
                bp.image_url,
                NULL as shop_name,
                bp.affiliate_link,
                'manual' as source,
                0 as total_orders,
                0 as total_gmv,
                0 as total_commission
            FROM brand_products bp
            WHERE bp.brand_id = ? AND (bp.source = 'manual' OR bp.source IS NULL)
        ";
        
        $manual_products = $this->db->query($manual_sql, [$brand_id])->result();
        
        // Untuk produk dari API (yang terdeteksi dari affiliate_products)
        $api_sql = "
            SELECT 
                ap.product_id,
                ap.product_name,
                ap.price,
                ap.commission_rate,
                ap.sales_count,
                ap.gmv,
                ap.image_url,
                ap.shop_name,
                NULL as affiliate_link,
                'api' as source,
                COALESCE(SUM(o.gmv), 0) as total_gmv,
                COUNT(DISTINCT o.order_id) as total_orders,
                COALESCE(SUM(o.actual_commission), 0) as total_commission
            FROM affiliate_products ap
            INNER JOIN brand_products bp ON ap.product_id = bp.product_id
            LEFT JOIN affiliate_orders o ON ap.product_id = o.product_id AND ap.campaign_id = o.campaign_id
            WHERE bp.brand_id = ? AND bp.source = 'api'
            GROUP BY ap.product_id, ap.campaign_id
            ORDER BY total_gmv DESC
        ";
        
        $api_products = $this->db->query($api_sql, [$brand_id])->result();
        
        // Gabungkan
        return array_merge($manual_products, $api_products);
    }
    
    /**
     * Assign product to brand (manual input)
     */
    public function assign_to_brand($brand_id, $product_data) {
        // Cek apakah sudah ada
        $existing = $this->db->where('brand_id', $brand_id)
                             ->where('product_name', $product_data['product_name'])
                             ->get($this->brand_products_table)
                             ->row();
        
        $data = [
            'brand_id' => $brand_id,
            'product_id' => $product_data['product_id'] ?? 'manual_' . time(),
            'product_name' => $product_data['product_name'],
            'price' => $product_data['price'] ?? 0,
            'image_url' => $product_data['image_url'] ?? null,
            'affiliate_link' => $product_data['affiliate_link'] ?? null,
            'source' => 'manual',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        if ($existing) {
            $this->db->where('id', $existing->id)->update($this->brand_products_table, $data);
            return $existing->id;
        } else {
            $this->db->insert($this->brand_products_table, $data);
            return $this->db->insert_id();
        }
    }
    
    /**
     * Sync product from API to affiliate_products AND link to brand
     */
    public function sync_product($product, $campaign_id, $brand_id = null) {
        // Simpan ke affiliate_products
        $existing = $this->db->where('product_id', $product['id'])
                             ->where('campaign_id', $campaign_id)
                             ->get($this->table)
                             ->row();
        
        $data = [
            'product_id' => $product['id'],
            'campaign_id' => $campaign_id,
            'product_name' => $product['title'] ?? $product['name'] ?? '',
            'price' => $product['price'] ?? 0,
            'commission_rate' => $product['commission_rate'] ?? $product['open_collab'] ?? 0,
            'sales_count' => $product['sales_count'] ?? 0,
            'gmv' => $product['gmv'] ?? ($product['price'] * $product['sales_count'] ?? 0),
            'image_url' => $product['image_url'] ?? $product['main_image_url'] ?? '',
            'category' => $product['category'] ?? '',
            'shop_name' => $product['shop_name'] ?? '',
            'status' => 'ACTIVE',
            'last_sync' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($existing) {
            $this->db->where('id', $existing->id)->update($this->table, $data);
            $product_db_id = $existing->id;
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert($this->table, $data);
            $product_db_id = $this->db->insert_id();
        }
        
        // Jika brand_id diberikan, link ke brand_products
        if ($brand_id) {
            $this->link_product_to_brand($brand_id, $product['id'], $campaign_id);
        }
        
        return $product_db_id;
    }
    
    /**
     * Link API product to brand
     */
    public function link_product_to_brand($brand_id, $product_id, $campaign_id) {
        $existing = $this->db->where('brand_id', $brand_id)
                             ->where('product_id', $product_id)
                             ->where('source', 'api')
                             ->get($this->brand_products_table)
                             ->row();
        
        if (!$existing) {
            $data = [
                'brand_id' => $brand_id,
                'product_id' => $product_id,
                'campaign_id' => $campaign_id,
                'source' => 'api',
                'created_at' => date('Y-m-d H:i:s')
            ];
            return $this->db->insert($this->brand_products_table, $data);
        }
        return true;
    }
    
    /**
     * Get product by ID (from affiliate_products)
     */
    public function get_product_by_id($product_id) {
        return $this->db->where('product_id', $product_id)
                        ->get($this->table)
                        ->row();
    }
    
    /**
     * Get unassigned products (products without brand)
     */
    public function get_unassigned_products($limit = 10) {
        $sql = "
            SELECT ap.* 
            FROM affiliate_products ap
            LEFT JOIN brand_products bp ON ap.product_id = bp.product_id
            WHERE bp.id IS NULL
            ORDER BY ap.gmv DESC
            LIMIT ?
        ";
        
        return $this->db->query($sql, [$limit])->result();
    }
    
    /**
     * Remove product from brand
     */
    public function remove_from_brand($product_id, $brand_id) {
        return $this->db->where('product_id', $product_id)
                        ->where('brand_id', $brand_id)
                        ->delete($this->brand_products_table);
    }
    
    /**
     * Get all products for a campaign
     */
    public function get_products_by_campaign($campaign_id) {
        return $this->db->where('campaign_id', $campaign_id)
                        ->order_by('gmv', 'DESC')
                        ->get($this->table)
                        ->result();
    }
    
    /**
     * Search products
     */
    public function search_products($keyword, $limit = 20) {
        return $this->db->like('product_name', $keyword)
                        ->or_like('shop_name', $keyword)
                        ->limit($limit)
                        ->order_by('gmv', 'DESC')
                        ->get($this->table)
                        ->result();
    }
    
    /**
     * Get top products by GMV
     */
    public function get_top_products($limit = 10) {
        return $this->db->order_by('gmv', 'DESC')
                        ->limit($limit)
                        ->get($this->table)
                        ->result();
    }
}