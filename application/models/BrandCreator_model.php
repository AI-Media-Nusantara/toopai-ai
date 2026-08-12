<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class BrandCreator_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Fastmoss_model');
          $this->load->model('CreatorProduct_model');
        
        // Cek dan tambahkan kolom jika belum ada
        $this->_ensure_columns();
    }
    
    
     public function sync_creator_products_to_db($creator_id, $creator_username, $fastmoss_uid) {
        $result = [
            'success' => false,
            'creator_id' => $creator_id,
            'creator_username' => $creator_username,
            'fastmoss_uid' => $fastmoss_uid,
            'total_found' => 0,
            'saved' => 0,
            'updated' => 0,
            'matched' => 0,
            'not_matched' => 0,
            'message' => ''
        ];

        try {
            // Ambil produk dari FastMoss
            $products = $this->Fastmoss_model->get_all_creator_products_by_uid($fastmoss_uid, 5);
            
            if (empty($products)) {
                $result['message'] = 'No products found on FastMoss';
                return $result;
            }

            $result['total_found'] = count($products);
            
            // Format produk
            $formatted_products = [];
            foreach ($products as $product) {
                $formatted_products[] = [
                    'product_id' => $product['goods_id'] ?? $product['product_id'] ?? null,
                    'product_name' => $product['goods_name'] ?? $product['product_name'] ?? $product['title'] ?? null,
                    'price' => $product['price'] ?? $product['sales_price'] ?? 0,
                    'sales_count' => $product['sold_count'] ?? $product['sales'] ?? 0,
                    'gmv' => $product['gmv'] ?? $product['sale_amount'] ?? 0,
                    'commission_rate' => $product['commission_rate'] ?? $product['open_collaboration_commission_rate'] ?? null,
                    'image_url' => $product['image_url'] ?? $product['main_image_url'] ?? '',
                    'shop_name' => $product['shop_name'] ?? $product['shop']['name'] ?? '',
                    'category' => $product['category'] ?? $product['category_name'] ?? '',
                    'inventory' => $product['inventory'] ?? 0,
                    'product_url' => $product['detail_link'] ?? $product['url'] ?? null,
                ];
            }

            // Simpan ke creator_products
            $save_result = $this->CreatorProduct_model->save_products(
                $creator_id,
                $creator_username,
                $formatted_products
            );

            $result['success'] = $save_result['success'];
            $result['saved'] = $save_result['saved'];
            $result['updated'] = $save_result['updated'];
            $result['matched'] = $save_result['matched'];
            $result['not_matched'] = $save_result['not_matched'];
            $result['message'] = "Saved {$save_result['saved']} new, updated {$save_result['updated']}, matched {$save_result['matched']}, not matched {$save_result['not_matched']}";

        } catch (Exception $e) {
            $result['message'] = 'Error: ' . $e->getMessage();
            log_message('error', 'sync_creator_products_to_db error: ' . $e->getMessage());
        }

        return $result;
    }



    /**
     * Pastikan kolom discovery_processed ada
     */
    private function _ensure_columns() {
        $columns = $this->db->list_fields('affiliate_products');
        if (!in_array('discovery_processed', $columns)) {
            $this->db->query("ALTER TABLE `affiliate_products` 
                ADD COLUMN `discovery_processed` TINYINT(1) DEFAULT 0 AFTER `review_status`");
            $this->db->query("ALTER TABLE `affiliate_products` 
                ADD COLUMN `discovery_processed_at` DATETIME NULL AFTER `discovery_processed`");
            $this->db->query("ALTER TABLE `affiliate_products` 
                ADD INDEX `idx_discovery_processed` (`discovery_processed`)");
        }
    }
    
    /**
     * Get products that just got approved (review_status changed to APPROVED)
     * Only get products that haven't been processed for discovery
     */
public function get_newly_approved_products($limit = 50) {
    $this->_ensure_columns();
    
    $this->db->select('ap.*, b.id as brand_id, b.name as brand_name, b.shop_name')
        ->from('affiliate_products ap')
        ->join('brands b', 'ap.shop_name = b.shop_name', 'left')
        ->where('ap.review_status', 'APPROVED')
        ->where('ap.discovery_processed', 0)
        ->where('ap.shop_name IS NOT NULL')
        ->where('ap.shop_name !=', '')
        // ðŸ”¥ FILTER: HANYA PRODUK YANG APPROVED DALAM 7 HARI TERAKHIR
        ->where('ap.approved_at >=', date('Y-m-d H:i:s', strtotime('-7 days')))
        // ðŸ”¥ ATAU GUNAKAN updated_at
        // ->where('ap.updated_at >=', date('Y-m-d H:i:s', strtotime('-7 days')))
        ->order_by('ap.approved_at', 'DESC')  // ðŸ”¥ URUTKAN BERDASARKAN approved_at
        ->limit($limit);
    
    return $this->db->get()->result();
}
    
    /**
     * Mark product as processed for discovery
     */
    public function mark_product_processed($product_id) {
        $this->_ensure_columns();
        
        $this->db->where('product_id', $product_id)
                 ->update('affiliate_products', [
                     'discovery_processed' => 1,
                     'discovery_processed_at' => date('Y-m-d H:i:s')
                 ]);
    }
    
    /**
     * Check if product already processed
     */
    public function is_product_processed($product_id) {
        $this->_ensure_columns();
        
        $result = $this->db->select('discovery_processed')
            ->where('product_id', $product_id)
            ->get('affiliate_products')
            ->row();
        
        return $result && $result->discovery_processed == 1;
    }
    
    
 public function find_creator_in_fastmoss($username) {
        // Cari di FastMoss menggunakan username
        // FastMoss biasanya punya endpoint search
        $search_result = $this->Fastmoss_model->search_creator($username);
        
        if (!empty($search_result)) {
            // Ambil UID pertama yang ditemukan
            $uid = $search_result[0]['uid'] ?? $search_result[0]['author_id'] ?? null;
            if ($uid) {
                return $uid;
            }
        }
        return false;
    }
    /**
     * Get creators from FastMoss for a product
     */
    public function get_product_creators($product_id) {
    // 🔥 CEK PRODUCT BASE TERLEBIH DAHULU
    $product = $this->Fastmoss_model->get_product_base($product_id);
    
    // Jika product tidak ditemukan atau region bukan Indonesia
    if (empty($product)) {
        return [
            'status' => false,
            'message' => 'Product not found on FastMoss',
            'creators' => []
        ];
    }
    
    // Cek region produk
    $region = $product['region'] ?? $product['region_name'] ?? '';
    if (strtoupper($region) !== 'ID' && strtoupper($region) !== 'INDONESIA') {
        return [
            'status' => false,
            'message' => 'Product is not from Indonesia (region: ' . $region . ')',
            'creators' => []
        ];
    }
    
    // Ambil creator dari FastMoss
    $creators = $this->Fastmoss_model->get_product_creators($product_id, 1, 100);
    
    // 🔥 Filter creator Indonesia
    $filtered = array_values(array_filter($creators, function ($row) {
        return strtoupper($row['region'] ?? '') === 'ID'
            || ($row['region_name'] ?? '') === 'Indonesia';
    }));
    
    if (empty($filtered)) {
        return [
            'status' => false,
            'message' => 'No Indonesian creators found for this product',
            'creators' => []
        ];
    }
    
    return [
        'status' => true,
        'product_id' => $product_id,
        'product_name' => $product['title'] ?? null,
        'product_region' => $product['region_name'] ?? $product['region'] ?? null,
        'creators' => $filtered,
        'total_creators' => count($filtered)
    ];
}
    
    /**
     * Save creators from FastMoss to database
     */
public function save_creators_from_fastmoss($brand_id, $creators_data, $is_id = 1) {
    $result = [
        'saved' => 0,
        'skipped' => 0,
        'added_to_creators' => 0,
        'updated_creators' => 0,
        'products_saved' => 0,
        'details' => []
    ];
    
    foreach ($creators_data as $data) {
        // 🔥 AMBIL DATA DENGAN DEFAULT VALUE
        $username = $data['unique_id'] ?? '';
        $username = ltrim($username, '@');
        $username = trim($username);
        
        if (empty($username)) {
            $username = $data['nickname'] ?? '';
            $username = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $username));
            $username = trim($username, '_');
        }
        
        if (empty($username)) {
            $result['skipped']++;
            continue;
        }
        
        // 🔥 DEFAULT VALUE UNTUK SEMUA VARIABEL
        $gmv_from_this_product = floatval($data['sale_amount'] ?? 0);
        $sales_from_this_product = intval($data['sold_count'] ?? 0);
        $product_id = $data['product_id'] ?? null;
        $product_name = $data['product_name'] ?? null;
        $commission_rate = $data['commission_rate'] ?? $data['open_commission_rate'] ?? 0;
        $follower_count = intval($data['follower_count'] ?? 0);
        $avatar_url = $data['avatar'] ?? null;
        $uid = $data['uid'] ?? null;
        $nickname = $data['nickname'] ?? $username;
        $category = $data['category'] ?? [];
        $price = floatval($data['price'] ?? 0);
        $inventory = intval($data['inventory'] ?? 0);
        
        // CEK APAKAH SUDAH ADA DI creators
        $existing_creator = $this->db->where('username', $username)
            ->where('brand_id', $brand_id)
            ->get('creators')
            ->row();
        
        $creator_id = null;
        
        if ($existing_creator) {
            // UPDATE creator yang sudah ada
            $new_gmv = floatval($existing_creator->imported_gmv ?? 0) + $gmv_from_this_product;
            $new_sales = intval($existing_creator->imported_sales_count ?? 0) + $sales_from_this_product;
            $new_followers = max(
                intval($existing_creator->imported_followers ?? 0),
                $follower_count
            );
            
            $this->db->where('id', $existing_creator->id)
                ->update('creators', [
                    'imported_gmv' => $new_gmv,
                    'imported_sales_count' => $new_sales,
                    'imported_followers' => $new_followers,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            
            $creator_id = $existing_creator->id;
            $result['updated_creators']++;
            $result['saved']++;
            
        } else {
            // INSERT creator baru
            $insert_creator = [
                'username' => $username,
                'full_name' => $nickname,
                'phone' => null,
                'email' => null,
                'category' => $this->_detect_category_from_creator($data),
                'is_id' => $is_id,
                'brand_id' => $brand_id,
                'shop_name' => $this->_get_brand_shop_name($brand_id),
                'source' => 'imported',
                'status' => 'PENDING',
                'avatar_url' => $avatar_url,
                'imported_followers' => $follower_count,
                'imported_gmv' => $gmv_from_this_product,
                'imported_sales_count' => $sales_from_this_product,
                'tiktok_open_id' => $uid,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $insert_creator = array_filter($insert_creator, function($value) {
                return $value !== null;
            });
            
            $this->db->insert('creators', $insert_creator);
            $creator_id = $this->db->insert_id();
            $result['added_to_creators']++;
            $result['saved']++;
        }
        
        // 🔥 SIMPAN PRODUK KE creator_products
        if ($creator_id && $product_id) {
            $product_data = [
                'creator_id' => $creator_id,
                'creator_username' => $username,
                'product_id' => $product_id,
                'product_name' => $product_name ?? $data['product_name'] ?? '',
                'price' => $price,
                'sales_count' => $sales_from_this_product,
                'gmv' => $gmv_from_this_product,
                'commission_rate' => $commission_rate,
                'image_url' => $avatar_url ?? $data['image_url'] ?? '',
                'shop_name' => $this->_get_brand_shop_name($brand_id),
                'category' => $this->_detect_category_from_creator($data),
                'inventory' => $inventory,
                'last_sync' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // CEK APAKAH SUDAH ADA
            $existing_product = $this->db->where('creator_id', $creator_id)
                ->where('product_id', $product_id)
                ->get('creator_products')
                ->row();
            
            if ($existing_product) {
                $this->db->where('id', $existing_product->id)
                    ->update('creator_products', $product_data);
            } else {
                $product_data['created_at'] = date('Y-m-d H:i:s');
                $this->db->insert('creator_products', $product_data);
                $result['products_saved']++;
            }
            
            // MATCHING
            $this->_match_product($creator_id, $product_id, $product_data);
        }
    }
    
    return $result;
}
    private function _generate_username($nickname) {
    if (empty($nickname)) {
        return 'user_' . substr(md5(uniqid()), 0, 8);
    }
    
    // Bersihkan nickname
    $username = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $nickname));
    $username = trim($username, '_');
    
    // Jika hasil kosong atau terlalu pendek
    if (strlen($username) < 3) {
        $username = 'user_' . substr(md5($nickname), 0, 8);
    }
    
    // Cek apakah username sudah ada di database
    $existing = $this->db->where('username', $username)->get('creators')->row();
    if ($existing) {
        // Tambahkan angka random
        $username = $username . '_' . rand(100, 999);
    }
    
    return $username;
}

private function _detect_category_from_creator($data) {
    $categories = $data['category'] ?? [];
    if (!empty($categories) && is_array($categories)) {
        $cat_map = [
            'Kecantikan' => 'Beauty',
            'Fashion' => 'Fashion',
            'Pakaian & Aksesori' => 'Fashion',
            'Elektronik' => 'Electronics',
            'Makanan' => 'Food',
            'Rumah Tangga' => 'Home & Living',
            'Olahraga' => 'Sports',
            'Bayi & Anak' => 'Baby & Kids',
            'Kesehatan' => 'Health',
            'Game' => 'Gaming',
            'Hewan Peliharaan' => 'Pets',
            'Perjalanan' => 'Travel',
            'Lainnya' => 'Lifestyle'
        ];
        
        foreach ($categories as $cat) {
            if (isset($cat_map[$cat])) {
                return $cat_map[$cat];
            }
        }
    }
    return 'Lifestyle';
}




    /**
 * Match product dengan affiliate_products
 */
private function _match_product($creator_id, $product_id, $product_data) {
    // Cek di affiliate_products
    $affiliate_product = $this->db->where('product_id', $product_id)
        ->get('affiliate_products')
        ->row();
    
    if ($affiliate_product) {
        // Update is_matched = 1
        $this->db->where('creator_id', $creator_id)
            ->where('product_id', $product_id)
            ->update('creator_products', [
                'is_matched' => 1,
                'matched_product_id' => $product_id,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    } else {
        // Coba cari berdasarkan product_name
        $product_name = $product_data['product_name'] ?? '';
        if (!empty($product_name)) {
            $similar = $this->db->like('product_name', $product_name, 'both')
                ->where('review_status', 'APPROVED')
                ->limit(1)
                ->get('affiliate_products')
                ->row();
            
            if ($similar) {
                $this->db->where('creator_id', $creator_id)
                    ->where('product_id', $product_id)
                    ->update('creator_products', [
                        'is_matched' => 1,
                        'matched_product_id' => $similar->product_id,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
            }
        }
    }
}
    private function _detect_category($product_name) {
        $product_lower = strtolower($product_name);
        $categories = [
            'Beauty' => ['beauty', 'makeup', 'skincare', 'cosmetic', 'lipstick', 'foundation', 'perfume', 'hair', 'face', 'cream', 'serum', 'toner', 'moisturizer'],
            'Fashion' => ['fashion', 'clothing', 'apparel', 'wear', 'dress', 'shirt', 'pants', 'jacket', 'bag', 'shoes', 'accessories'],
            'Electronics' => ['tech', 'electronics', 'gadget', 'phone', 'laptop', 'computer', 'camera', 'audio', 'smartphone'],
            'Food' => ['food', 'snack', 'beverage', 'drink', 'meal', 'cooking', 'bakery', 'candy', 'chocolate', 'coffee', 'tea'],
            'Home & Living' => ['home', 'living', 'decor', 'furniture', 'kitchen', 'household'],
            'Sports' => ['sport', 'fitness', 'workout', 'gym', 'exercise', 'yoga', 'running'],
            'Baby & Kids' => ['baby', 'kids', 'children', 'toy', 'infant', 'parenting'],
            'Health' => ['health', 'wellness', 'vitamin', 'supplement', 'medical'],
            'Gaming' => ['gaming', 'game', 'console', 'controller', 'headset', 'keyboard'],
            'Pets' => ['pet', 'animal', 'dog', 'cat', 'pet food'],
            'Travel' => ['travel', 'journey', 'tour', 'vacation'],
        ];
        
        foreach ($categories as $cat => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($product_lower, $keyword) !== false) {
                    return $cat;
                }
            }
        }
        return 'Lifestyle';
    }
    
private function _get_brand_shop_name($brand_id) {
    if (empty($brand_id)) return null;
    
    $brand = $this->db->select('shop_name, name')
        ->where('id', $brand_id)
        ->get('brands')
        ->row();
    
    return $brand->shop_name ?? $brand->name ?? null;
}
    
    
public function get_creator_promoted_products($creator_username, $tiktok_open_id = null) {
        $result = [
            'products' => [],
            'fastmoss_products' => [],
            'matched_products' => [],
            'total' => 0,
            'fastmoss_uid' => null
        ];

        // ========== STEP 1: PASTIKAN PUNYA FASTMOSS_UID ==========
        $fastmoss_uid = $tiktok_open_id;
        
        // Jika tiktok_open_id NULL, cari dari FastMoss
        if (empty($fastmoss_uid)) {
            $fastmoss_uid = $this->find_creator_in_fastmoss($creator_username);
            
            if ($fastmoss_uid) {
                // Update tiktok_open_id di database
                $this->db->where('username', $creator_username)
                         ->update('creators', [
                             'tiktok_open_id' => $fastmoss_uid,
                             'updated_at' => date('Y-m-d H:i:s')
                         ]);
                
                $result['fastmoss_uid'] = $fastmoss_uid;
            }
        } else {
            $result['fastmoss_uid'] = $fastmoss_uid;
        }

        // Jika tetap tidak ada, return empty
        if (empty($fastmoss_uid)) {
            return $result;
        }

        // ========== STEP 2: AMBIL SEMUA PRODUCT_ID DARI AFFILIATE_PRODUCTS ==========
        $db_products = $this->db->select('product_id, product_name, price, image_url, shop_name, category, sales_count, review_status, open_commission_rate')
            ->from('affiliate_products')
            ->where('review_status', 'APPROVED')
            ->get()
            ->result();

        // Buat map product_id dari database
        $db_product_map = [];
        foreach ($db_products as $p) {
            $db_product_map[$p->product_id] = $p;
        }

        // ========== STEP 3: AMBIL PRODUK DARI FASTMOSS ==========
        $fastmoss_products = $this->get_creator_all_products($fastmoss_uid);
        $result['fastmoss_products'] = $fastmoss_products;

        // ========== STEP 4: FILTER - HANYA YANG ADA DI DATABASE ==========
        $new_products = [];
        foreach ($fastmoss_products as $fm_product) {
            $fm_product_id = $fm_product['product_id'] ?? null;
            
            if (empty($fm_product_id)) continue;
            
            // Cek apakah produk ada di database
            if (isset($db_product_map[$fm_product_id])) {
                // Ambil data dari database
                $db_product = $db_product_map[$fm_product_id];
                
                $matched_product = (object) [
                    'product_id' => $fm_product_id,
                    'product_name' => $db_product->product_name,
                    'price' => $db_product->price,
                    'image_url' => $db_product->image_url,
                    'shop_name' => $db_product->shop_name,
                    'category' => $db_product->category,
                    'sales_count' => $fm_product['sales_count'] ?? $db_product->sales_count,
                    'review_status' => $db_product->review_status,
                    'open_commission_rate' => $fm_product['commission_rate'] ?? $db_product->open_commission_rate,
                    'gmv_from_creator' => $fm_product['gmv'] ?? 0,
                    'sales_from_creator' => $fm_product['sales_count'] ?? 0,
                    'in_database' => true,
                    'from_fastmoss' => true
                ];
                
                $result['matched_products'][] = $matched_product;
                $result['products'][] = $matched_product;
            } else {
                // ========== PRODUK BELUM ADA DI DATABASE, SIMPAN ==========
                $insert_data = [
                    'product_id' => $fm_product_id,
                    'product_name' => $fm_product['product_name'] ?? '',
                    'price' => $fm_product['price'] ?? 0,
                    'image_url' => $fm_product['image_url'] ?? '',
                    'shop_name' => $fm_product['shop_name'] ?? '',
                    'category' => $fm_product['category'] ?? '',
                    'sales_count' => $fm_product['sales_count'] ?? 0,
                    'open_commission_rate' => $fm_product['commission_rate'] ?? 0,
                    'review_status' => 'APPROVED',
                    'status' => 'ACTIVE',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'last_sync' => date('Y-m-d H:i:s')
                ];
                
                $this->db->insert('affiliate_products', $insert_data);
                $new_products[] = $insert_data;
                
                // Tambahkan ke hasil
                $result['products'][] = (object) $insert_data;
            }
        }

        // ========== STEP 5: SORT BY SALES ==========
        usort($result['products'], function($a, $b) {
            return ($b->sales_from_creator ?? 0) - ($a->sales_from_creator ?? 0);
        });

        $result['total'] = count($result['products']);
        $result['new_products'] = $new_products;
        return $result;
    }


    /**
     * Get all products sold by a creator from FastMoss
     */
public function get_creator_all_products($fastmoss_uid, $maxPages = 3) {
        $allProducts = [];
        $page = 1;
        $pageSize = 50;

        do {
            $products = $this->Fastmoss_model->get_creator_products($fastmoss_uid, $page, $pageSize);
            
            if (empty($products)) {
                break;
            }

            foreach ($products as $product) {
                $allProducts[] = [
                    'product_id' => $product['goods_id'] ?? $product['product_id'] ?? null,
                    'product_name' => $product['goods_name'] ?? $product['product_name'] ?? $product['title'] ?? null,
                    'price' => $product['price'] ?? $product['sales_price'] ?? 0,
                    'sales_count' => $product['sold_count'] ?? $product['sales'] ?? 0,
                    'gmv' => $product['gmv'] ?? $product['sale_amount'] ?? 0,
                    'commission_rate' => $product['commission_rate'] ?? $product['open_collaboration_commission_rate'] ?? 0,
                    'image_url' => $product['image_url'] ?? $product['main_image_url'] ?? '',
                    'shop_name' => $product['shop_name'] ?? $product['shop']['name'] ?? '',
                    'category' => $product['category'] ?? $product['category_name'] ?? '',
                    'inventory' => $product['inventory'] ?? 0,
                ];
            }
            
            $page++;
            
        } while (count($products) == $pageSize && $page <= $maxPages);

        return $allProducts;
    }



    /**
     * Get creator promoted products for IS Task 2 (Assign Link)
     * 
     * @param int $creator_id - ID creator
     * @return array
     */
      public function get_creator_products_for_assign($creator_id) {
        $creator = $this->db->select('id, username, full_name, tiktok_open_id, brand_id')
            ->where('id', $creator_id)
            ->get('creators')
            ->row();

        if (!$creator) {
            return [
                'success' => false,
                'message' => 'Creator not found',
                'products' => []
            ];
        }

        // Ambil produk yang match dari creator_products
        $products = $this->CreatorProduct_model->get_matched_for_assign($creator_id);

        // Tambahkan info link
        foreach ($products as &$product) {
            // Cek link dari BD
            $link = $this->db->select('affiliate_link, commission_rate, created_at')
                ->from('bd_affiliate_links')
                ->where('product_id', $product->product_id)
                ->where('status', 'ACTIVE')
                ->order_by('created_at', 'DESC')
                ->limit(1)
                ->get()
                ->row();
            
            $product->has_link = !empty($link);
            $product->affiliate_link = $link->affiliate_link ?? null;
            $product->link_commission = $link->commission_rate ?? null;
            
            // Cek apakah sudah diassign
            $assigned = $this->db->where('creator_id', $creator_id)
                ->where('product_id', $product->product_id)
                ->where('status', 'ACTIVE')
                ->get('affiliate_creator_links')
                ->row();
            
            $product->is_assigned = !empty($assigned);
            $product->assigned_link = $assigned->affiliate_link ?? null;
        }

        $stats = $this->CreatorProduct_model->get_stats($creator_id);

        return [
            'success' => true,
            'creator' => $creator,
            'products' => $products,
            'total' => count($products),
            'stats' => $stats,
            'message' => count($products) > 0 ? 
                "Found " . count($products) . " matched products" : 
                "No matched products found"
        ];
    }

    /**
     * Save creator products to database
     */
    public function save_creator_products($brand_creator_id, $products)
    {
        $saved = 0;
        
        foreach ($products as $product) {
            if (empty($product['product_id'])) continue;
            
            // Cek apakah sudah ada
            $existing = $this->db->where('brand_creator_id', $brand_creator_id)
                                 ->where('product_id', $product['product_id'])
                                 ->get('brand_creator_products')
                                 ->row();
            
            $product_data = [
                'brand_creator_id' => $brand_creator_id,
                'product_id' => $product['product_id'],
                'product_name' => $product['product_name'],
                'price' => $product['price'],
                'sales_count' => $product['sales_count'],
                'gmv' => $product['gmv'],
                'commission_rate' => $product['commission_rate'],
                'image_url' => $product['image_url'],
                'shop_name' => $product['shop_name'],
                'category' => $product['category'],
                'inventory' => $product['inventory'],
                'last_sync' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($existing) {
                $this->db->where('id', $existing->id)->update('brand_creator_products', $product_data);
            } else {
                $product_data['created_at'] = date('Y-m-d H:i:s');
                $this->db->insert('brand_creator_products', $product_data);
                $saved++;
            }
        }
        
        return $saved;
    }

    /**
     * Get creator products from database
     */
    public function get_creator_products_from_db($brand_creator_id)
    {
        return $this->db->where('brand_creator_id', $brand_creator_id)
            ->order_by('sales_count', 'DESC')
            ->get('brand_creator_products')
            ->result();
    }

 public function get_products_by_creator_uid($creator_uid) {
        $result = [
            'success' => false,
            'creator_uid' => $creator_uid,
            'total_found' => 0,
            'products' => [],
            'message' => ''
        ];

        try {
            // Ambil semua produk dari FastMoss
            $products = $this->Fastmoss_model->get_all_creator_products_by_uid($creator_uid, 5);
            
            if (empty($products)) {
                $result['message'] = 'No products found for this creator';
                return $result;
            }

            $formatted_products = [];
            foreach ($products as $product) {
                $formatted_products[] = [
                    'product_id' => $product['goods_id'] ?? $product['product_id'] ?? null,
                    'product_name' => $product['goods_name'] ?? $product['product_name'] ?? $product['title'] ?? null,
                    'price' => $product['price'] ?? $product['sales_price'] ?? 0,
                    'sales_count' => $product['sold_count'] ?? $product['sales'] ?? 0,
                    'gmv' => $product['gmv'] ?? $product['sale_amount'] ?? 0,
                    'commission_rate' => $product['commission_rate'] ?? $product['open_collaboration_commission_rate'] ?? 0,
                    'image_url' => $product['image_url'] ?? $product['main_image_url'] ?? '',
                    'shop_name' => $product['shop_name'] ?? $product['shop']['name'] ?? '',
                    'category' => $product['category'] ?? $product['category_name'] ?? '',
                    'inventory' => $product['inventory'] ?? 0,
                    'created_at' => $product['create_time'] ?? $product['created_at'] ?? null,
                ];
            }

            $result['success'] = true;
            $result['total_found'] = count($formatted_products);
            $result['products'] = $formatted_products;

        } catch (Exception $e) {
            $result['message'] = 'Error: ' . $e->getMessage();
            log_message('error', 'get_products_by_creator_uid error: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Get creator products that match with affiliate_products
     * @param string $creator_uid - FastMoss creator UID
     * @return array
     */
    public function get_matched_products_by_creator($creator_uid) {
        $result = [
            'success' => false,
            'creator_uid' => $creator_uid,
            'total_found' => 0,
            'total_matched' => 0,
            'matched_products' => [],
            'unmatched_products' => [],
            'message' => ''
        ];

        try {
            // Ambil semua produk dari FastMoss
            $creator_products = $this->get_products_by_creator_uid($creator_uid);
            
            if (!$creator_products['success'] || empty($creator_products['products'])) {
                $result['message'] = 'No products found for this creator';
                return $result;
            }

            $result['total_found'] = $creator_products['total_found'];
            $result['products'] = $creator_products['products'];

            // Ambil semua product_id dari affiliate_products
            $db_products = $this->db->select('product_id, product_name, price, image_url, shop_name, category, sales_count, review_status, open_commission_rate')
                ->from('affiliate_products')
                ->where('review_status', 'APPROVED')
                ->get()
                ->result();

            $db_product_ids = array_column($db_products, 'product_id');
            $db_product_map = [];
            foreach ($db_products as $p) {
                $db_product_map[$p->product_id] = $p;
            }

            // Match produk
            foreach ($creator_products['products'] as $fm_product) {
                $fm_product_id = $fm_product['product_id'] ?? null;
                if (empty($fm_product_id)) continue;

                if (isset($db_product_map[$fm_product_id])) {
                    $db_product = $db_product_map[$fm_product_id];
                    $result['matched_products'][] = (object) [
                        'product_id' => $fm_product_id,
                        'product_name' => $db_product->product_name,
                        'price' => $db_product->price,
                        'image_url' => $db_product->image_url,
                        'shop_name' => $db_product->shop_name,
                        'category' => $db_product->category,
                        'sales_count' => $fm_product['sales_count'] ?? $db_product->sales_count,
                        'review_status' => $db_product->review_status,
                        'open_commission_rate' => $fm_product['commission_rate'] ?? $db_product->open_commission_rate,
                        'sales_from_creator' => $fm_product['sales_count'] ?? 0,
                        'gmv_from_creator' => $fm_product['gmv'] ?? 0,
                        'in_database' => true
                    ];
                } else {
                    $result['unmatched_products'][] = $fm_product;
                }
            }

            $result['success'] = true;
            $result['total_matched'] = count($result['matched_products']);

        } catch (Exception $e) {
            $result['message'] = 'Error: ' . $e->getMessage();
            log_message('error', 'get_matched_products_by_creator error: ' . $e->getMessage());
        }

        return $result;
    }


}