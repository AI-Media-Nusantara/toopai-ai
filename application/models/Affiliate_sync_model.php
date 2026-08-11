<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Affiliate_sync_model extends CI_Model {
    
    private $timezone = 'Asia/Jakarta';
        private $table = 'affiliate_products'; 
    public function __construct() {
        parent::__construct();
        date_default_timezone_set($this->timezone);
    }
    
    /**
     * Get current time in WIB
     */
    public function get_current_time() {
        return date('Y-m-d H:i:s');
    }
    
    /**
     * Save campaign data
     */
    /**
 * Save campaign data - LENGKAP DENGAN GAMBAR
 */
public function save_campaign($campaign) {
    $now = $this->get_current_time();
    
    // Ambil campaign_name
    $campaign_name = '';
    if (isset($campaign['name'])) {
        $campaign_name = $campaign['name'];
    } elseif (isset($campaign['campaign_name'])) {
        $campaign_name = $campaign['campaign_name'];
    } elseif (isset($campaign['title'])) {
        $campaign_name = $campaign['title'];
    }
    
    // Konversi timestamp ke datetime
    $start_date = null;
    $end_date = null;
    
    if (isset($campaign['campaign_start_time']) && $campaign['campaign_start_time']) {
        $start_date = date('Y-m-d H:i:s', $campaign['campaign_start_time']);
    } elseif (isset($campaign['start_time']) && $campaign['start_time']) {
        $start_date = date('Y-m-d H:i:s', $campaign['start_time']);
    }
    
    if (isset($campaign['campaign_end_time']) && $campaign['campaign_end_time']) {
        $end_date = date('Y-m-d H:i:s', $campaign['campaign_end_time']);
    } elseif (isset($campaign['end_time']) && $campaign['end_time']) {
        $end_date = date('Y-m-d H:i:s', $campaign['end_time']);
    }
    
    // 🔥 AMBIL GAMBAR CAMPAIGN DARI BERBAGAI FIELD
    $campaign_image = '';
    $campaign_icon = '';
    $cover_image = '';
    
    // Cek dari avatar_icon_list
    if (isset($campaign['avatar_icon_list']) && is_array($campaign['avatar_icon_list']) && !empty($campaign['avatar_icon_list'])) {
        $campaign_icon = $campaign['avatar_icon_list'][0]['web_uri'] ?? '';
        if (!empty($campaign_icon)) {
            $campaign_image = $campaign_icon;
        }
    }
    
    // Cek dari media_info_list
    if (isset($campaign['media_info_list']) && is_array($campaign['media_info_list']) && !empty($campaign['media_info_list'])) {
        foreach ($campaign['media_info_list'] as $media) {
            if (!empty($media['image_info']) && is_array($media['image_info'])) {
                foreach ($media['image_info'] as $img) {
                    $web_uri = $img['web_uri'] ?? '';
                    if (!empty($web_uri)) {
                        $cover_image = $web_uri;
                        if (empty($campaign_image)) {
                            $campaign_image = $cover_image;
                        }
                        break 2;
                    }
                }
            }
        }
    }
    
    // Cek dari image_url langsung
    if (empty($campaign_image) && isset($campaign['image_url']) && !empty($campaign['image_url'])) {
        $campaign_image = $campaign['image_url'];
    }
    
    // Cek dari cover_image_url
    if (empty($campaign_image) && isset($campaign['cover_image_url']) && !empty($campaign['cover_image_url'])) {
        $campaign_image = $campaign['cover_image_url'];
    }
    
    // Cek dari banner_url
    if (empty($campaign_image) && isset($campaign['banner_url']) && !empty($campaign['banner_url'])) {
        $campaign_image = $campaign['banner_url'];
    }
    
    $data = [
        'campaign_id' => $campaign['id'],
        'campaign_name' => $campaign_name,
        'campaign_image' => $campaign_image,
        'campaign_icon' => $campaign_icon,
        'cover_image' => $cover_image,
        'status' => $campaign['status'] ?? 'ONGOING',
        'start_date' => $start_date,
        'end_date' => $end_date,
        'budget' => $campaign['budget'] ?? 0,
        'total_gmv' => $campaign['total_gmv'] ?? 0,
        'total_orders' => $campaign['total_orders'] ?? 0,
        'total_creators' => $campaign['total_creators'] ?? 0,
        'commission_rate' => $campaign['commission_rate'] ?? 0,
        'raw_data' => json_encode($campaign),
        'last_sync' => $now,
        'updated_at' => $now
    ];
    
    // Cek apakah sudah ada
    $existing = $this->db->where('campaign_id', $campaign['id'])->get($this->table)->row();
    
    if ($existing) {
        $this->db->where('campaign_id', $campaign['id'])->update($this->table, $data);
        return $existing->id;
    } else {
        $data['created_at'] = $now;
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }
}
   
 
/**
 * Save product data - FIXED VERSION dengan pengecekan kolom
 */
public function save_product($product, $campaign_id) {
    $now = $this->get_current_time();
    
    $product_id = $product['id'] ?? '';
    if (empty($product_id)) {
        log_message('error', 'Product ID is missing: ' . json_encode($product));
        return false;
    }
    
    // 🔥 SIMPAN DALAM BENTUK RAW (CENTS), JANGAN DIKONVERSI
    // API mengembalikan nilai dalam cents: 700 = 7.00%, 8000 = 80.00%
    $open_commission = 0;
    if (isset($product['open_collaboration_commission_rate'])) {
        $open_commission = floatval($product['open_collaboration_commission_rate']);
    }
    
    $partner_commission = 0;
    if (isset($product['partner_commission_rate'])) {
        $partner_commission = floatval($product['partner_commission_rate']);
    }
    
    $creator_commission = 0;
    if (isset($product['creator_commission_rate'])) {
        $creator_commission = floatval($product['creator_commission_rate']);
    }
    
    $total_commission = 0;
    if (isset($product['total_commission_rate'])) {
        $total_commission = floatval($product['total_commission_rate']);
    }
    // 🔥 EKSTRAK HARGA DARI AMOUNT
    $price = 0;
    
    // Prioritas 1: lowest_price.amount
    if (isset($product['lowest_price']) && is_array($product['lowest_price'])) {
        $price = floatval($product['lowest_price']['amount'] ?? 0);
    } 
    // Prioritas 2: highest_price.amount
    elseif (isset($product['highest_price']) && is_array($product['highest_price'])) {
        $price = floatval($product['highest_price']['amount'] ?? 0);
    }
    // Prioritas 3: price (jika langsung ada)
    elseif (isset($product['price']) && !is_array($product['price'])) {
        $price = floatval($product['price']);
    }
    // ADS COLLABORATION (Kolaborasi Terbuka) - dalam %
    $ads_collaboration = 0;
    if (isset($product['partner_shop_ads_commission_rate'])) {
        $ads_collaboration = floatval($product['partner_shop_ads_commission_rate']);
    }
    
    // SHOP ADS (Iklan Toko) - dalam %
    $shop_ads = 0;
    if (isset($product['shop_ads_commission_rate'])) {
        $shop_ads = floatval($product['shop_ads_commission_rate']);
    } 
    // 🔥 DATA LENGKAP
    $data = [
        'product_id' => $product_id,
        'campaign_id' => $campaign_id,
        'product_name' => $product['name'] ?? '',
         'price' => $price,
         'lowest_price' =>floatval($product['lowest_price']['amount'] ?? 0),
         'highest_price' => floatval($product['highest_price']['amount'] ?? 0),
        'image_url' => $product['main_image_url'] ?? '',
        'shop_name' => $product['shop_name'] ?? '',
        'review_status' => $product['review_status'] ?? 'PENDING',
        'open_commission_rate' => $open_commission,      // SIMPAN RAW (700)
        'partner_commission_rate' => $partner_commission,  // SIMPAN RAW
        'creator_commission_rate' => $creator_commission,  // SIMPAN RAW
        'total_commission_rate' => $total_commission,      // SIMPAN RAW
         'shop_ads' => $shop_ads,                   
        'ads_collaboration' => $ads_collaboration,  
        'inventory' => $product['inventory'] ?? 0,
        'sales_count' => $product['product_sales'] ?? 0,
        'sample_quota' => $product['sample_quota'] ?? 0,
        'category' => $product['category_name'] ?? '',
        'last_sync' => $now,
        'updated_at' => $now,
        'raw_data' => json_encode($product)
    ];
    
    $existing = $this->db->where('product_id', $product_id)
                         ->where('campaign_id', $campaign_id)
                         ->get('affiliate_products')
                         ->row();
    
    if ($existing) {
        $this->db->where('id', $existing->id)->update('affiliate_products', $data);
    } else {
        $data['created_at'] = $now;
        $this->db->insert('affiliate_products', $data);
    }
}
   
    /**
     * Save creator link
     */
    public function save_creator_link($link_data) {
    $now = $this->get_current_time();
    
    // Pastikan data yang diperlukan ada
    if (empty($link_data['affiliate_link']) || empty($link_data['creator_username'])) {
        log_message('error', 'Missing required data for creator link: ' . json_encode($link_data));
        return false;
    }
    
    $data = [
        'link_id' => $link_data['link_id'] ?? md5($link_data['affiliate_link']),
        'campaign_id' => $link_data['campaign_id'] ?? '',
        'product_id' => $link_data['product_id'] ?? '',
        'product_name' => $link_data['product_name'] ?? '',
        'creator_username' => $link_data['creator_username'],
        'creator_hid' => $link_data['creator_hid'] ?? null,
        'affiliate_link' => $link_data['affiliate_link'],
        'commission_rate' => $link_data['commission_rate'] ?? 0,
        'shared_date' => $link_data['shared_date'] ?? $now,
        'expire_date' => $link_data['expire_date'] ?? null,
        'status' => $link_data['status'] ?? 'ACTIVE',
        'total_clicks' => $link_data['total_clicks'] ?? 0,
        'total_orders' => $link_data['total_orders'] ?? 0,
        'total_gmv' => $link_data['total_gmv'] ?? 0,
        'total_commission' => $link_data['total_commission'] ?? 0,
        'last_sync' => $now,
        'updated_at' => $now
    ];
    
    // Cek apakah sudah ada link untuk kombinasi campaign, product, creator
    $existing = $this->db->where('campaign_id', $data['campaign_id'])
                         ->where('product_id', $data['product_id'])
                         ->where('creator_username', $data['creator_username'])
                         ->get('affiliate_creator_links')
                         ->row();
    
    if ($existing) {
        // Update existing - hanya update statistik, bukan linknya
        $this->db->where('id', $existing->id);
        $update_data = [
            'total_clicks' => $data['total_clicks'],
            'total_orders' => $data['total_orders'],
            'total_gmv' => $data['total_gmv'],
            'total_commission' => $data['total_commission'],
            'last_sync' => $now,
            'updated_at' => $now
        ];
        $this->db->update('affiliate_creator_links', $update_data);
        return $existing->id;
    } else {
        // Insert new link
        $data['created_at'] = $now;
        $this->db->insert('affiliate_creator_links', $data);
        return $this->db->insert_id();
    }
}
/**
 * Update creator link statistics based on orders
 * Method ini dipanggil setelah sync orders
 */
public function update_creator_link_stats() {
    $sql = "
        UPDATE affiliate_creator_links l
        SET 
            l.total_orders = (
                SELECT COUNT(*) 
                FROM affiliate_orders o 
                WHERE o.creator_username = l.creator_username 
                    AND o.campaign_id = l.campaign_id
                    AND o.product_id = l.product_id
                    AND o.order_status NOT IN ('CANCELLED', 'REFUNDED')
            ),
            l.total_gmv = (
                SELECT COALESCE(SUM(o.gmv), 0)
                FROM affiliate_orders o 
                WHERE o.creator_username = l.creator_username 
                    AND o.campaign_id = l.campaign_id
                    AND o.product_id = l.product_id
                    AND o.order_status NOT IN ('CANCELLED', 'REFUNDED')
            ),
            l.total_commission = (
                SELECT COALESCE(SUM(o.actual_commission), 0)
                FROM affiliate_orders o 
                WHERE o.creator_username = l.creator_username 
                    AND o.campaign_id = l.campaign_id
                    AND o.product_id = l.product_id
                    AND o.order_status NOT IN ('CANCELLED', 'REFUNDED')
            ),
            l.last_sync = NOW()
        WHERE l.status = 'ACTIVE'
    ";
    
    $this->db->query($sql);
    
    log_message('info', 'Updated creator link statistics');
}

 /**
 * Get orders grouped by creator link
 * Menampilkan order berdasarkan link yang dibagikan ke creator
 */
public function get_orders_by_creator_link($creator_username = null, $campaign_id = null) {
    $this->db->select('
        l.link_id,
        l.creator_username,
        l.campaign_id,
        c.campaign_name,
        l.product_id,
        l.product_name,
        l.affiliate_link,
        l.shared_date,
        l.total_clicks,
        l.total_orders,
        l.total_gmv,
        l.total_commission,
        COUNT(o.order_id) as actual_orders,
        SUM(o.gmv) as actual_gmv,
        SUM(o.actual_commission) as actual_commission
    ');
    $this->db->from('affiliate_creator_links l');
    $this->db->join('affiliate_campaigns c', 'l.campaign_id = c.campaign_id', 'left');
    $this->db->join('affiliate_orders o', 'l.creator_username = o.creator_username AND l.campaign_id = o.campaign_id AND l.product_id = o.product_id', 'left');
    
    if ($creator_username) {
        $this->db->where('l.creator_username', $creator_username);
    }
    if ($campaign_id) {
        $this->db->where('l.campaign_id', $campaign_id);
    }
    
    $this->db->group_by('l.id');
    $this->db->order_by('l.total_gmv', 'DESC');
    
    return $this->db->get()->result();
}   
    /**
     * Save order detail
     */
  public function save_order($order) {
    $now = $this->get_current_time();
    
    // ID Order
    $order_id = $order['order_id'] ?? '';
    if (empty($order_id)) {
        log_message('error', 'Order ID is missing: ' . json_encode($order));
        return false;
    }
    
     $campaign_id = $order['campaign_id'] ?? '';
    $product_id = $order['product_id'] ?? '';
    
    if (empty($campaign_id) && !empty($product_id)) {
        // 🔥 AMBIL DARI TABEL affiliate_products
        $product = $this->db->select('campaign_id')
                            ->where('product_id', $product_id)
                            ->limit(1)
                            ->get('affiliate_products')
                            ->row();
        if ($product && !empty($product->campaign_id)) {
            $campaign_id = $product->campaign_id;
            log_message('info', "Campaign ID filled from product: $campaign_id for order $order_id");
        }
    }
    
    //  ORDER TIME - Gunakan create_time_formatted yang sudah dalam format siap pakai
    $order_time = null;
    if (isset($order['create_time_formatted']) && !empty($order['create_time_formatted'])) {
        $order_time = $order['create_time_formatted'];
    } elseif (isset($order['create_time_utc']) && is_numeric($order['create_time_utc'])) {
        $order_time = date('Y-m-d H:i:s', $order['create_time_utc']);
    }
    
    // ðŸ”¥ TANGGAL LOCAL untuk grouping
    $order_date_local = $order['create_date_local'] ?? null;
    
    // ðŸ”¥ GMV
    $gmv = floatval($order['affiliate_gmv'] ?? 0);
    if ($gmv == 0 && isset($order['price']) && isset($order['quantity'])) {
        $gmv = floatval($order['price']) * intval($order['quantity']);
    }
    
    // ðŸ”¥ COMMISSION
    $estimated_commission = floatval($order['estimated_affiliate_commission'] ?? 0);
    $actual_commission = floatval($order['actual_affiliate_commission'] ?? 0);
    
    // ðŸ”¥ QUANTITY & PRICE
    $quantity = intval($order['quantity'] ?? $order['items_sold'] ?? 1);
    $price = floatval($order['price'] ?? 0);
    
    // ðŸ”¥ COMMISSION RATE (hitung dari estimated commission)
    $commission_rate = 0;
    if ($gmv > 0 && $estimated_commission > 0) {
        $commission_rate = ($estimated_commission / $gmv) * 100;
    } elseif (isset($order['estimated_creator_commission']) && $order['estimated_creator_commission'] > 0 && $gmv > 0) {
        // Alternative dari creator commission
        $commission_rate = ($order['estimated_creator_commission'] / $gmv) * 100;
    }
    
    $data = [
        'order_id' => $order_id,
         'campaign_id' => $campaign_id,
         'sku_id' => $order['sku_id'] ?? '', 
        'campaign_name' => $order['campaign_name'] ?? '',
        'product_id' => $order['product_id'] ?? '',
        'product_name' => $order['product_name'] ?? '',
        'creator_username' => $order['creator_username'] ?? '',
        'creator_hid' => $order['creator_hid'] ?? null,
        'order_status' => $order['order_status'] ?? 'PROCESSING',
        'quantity' => $quantity,
        'price' => $price,
        'gmv' => $gmv,
        'estimated_commission' => $estimated_commission,
        'actual_commission' => $actual_commission,
        'commission_rate' => $commission_rate,
        'order_time' => $order_time,
        'order_date_local' => $order_date_local,  // Simpan tanggal lokal
        'raw_data' => json_encode($order),
        'last_sync' => $now,
        'updated_at' => $now
    ];
    
    
       $existing = $this->db
        ->where('order_id', $order_id)
        ->where('sku_id', $data['sku_id'])
        ->where('product_id', $data['product_id'])
        ->get('affiliate_orders')
        ->row();
    
    if ($existing) {
        // Update yang ketemu
        $this->db->where('id', $existing->id)->update('affiliate_orders', $data);
    } else {
        // Cek apakah order_id sudah ada tapi sku_id berbeda
        $existing_order = $this->db
            ->where('order_id', $order_id)
            ->get('affiliate_orders')
            ->row();
        
        if ($existing_order) {
            // Update dengan menambahkan sku_id
            $this->db->where('id', $existing_order->id)->update('affiliate_orders', $data);
        } else {
            // Insert baru
            $data['created_at'] = $now;
            $this->db->insert('affiliate_orders', $data);
        }
    }
}
    
    /**
     * Log sync activity
     */
    public function log_sync_start($sync_type) {
        $data = [
            'sync_type' => $sync_type,
            'status' => 'running',
            'start_time' => $this->get_current_time()
        ];
        $this->db->insert('affiliate_sync_logs', $data);
        return $this->db->insert_id();
    }
    
    public function log_sync_end($log_id, $status, $processed = 0, $success = 0, $failed = 0, $error = null) {
        $this->db->where('id', $log_id)->update('affiliate_sync_logs', [
            'status' => $status,
            'end_time' => $this->get_current_time(),
            'total_processed' => $processed,
            'total_success' => $success,
            'total_failed' => $failed,
            'error_message' => $error
        ]);
    }
    
    /**
     * Add to retry queue
     */
    public function add_to_queue($sync_type, $campaign_id = null, $start_date = null, $end_date = null, $priority = 0) {
        $existing = $this->db->where('sync_type', $sync_type)
                             ->where('status', 'pending')
                             ->where('campaign_id', $campaign_id)
                             ->get('affiliate_sync_queue')
                             ->row();
        
        if (!$existing) {
            $this->db->insert('affiliate_sync_queue', [
                'sync_type' => $sync_type,
                'campaign_id' => $campaign_id,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'priority' => $priority,
                'status' => 'pending',
                'scheduled_time' => date('Y-m-d H:i:s', strtotime('+5 minutes'))
            ]);
        }
    }
    
    /**
     * Get pending queue items
     */
    public function get_pending_queue() {
        return $this->db->where('status', 'pending')
                        ->where('scheduled_time <=', $this->get_current_time())
                        ->order_by('priority DESC, created_at ASC')
                        ->limit(10)
                        ->get('affiliate_sync_queue')
                        ->result();
    }
    
    /**
     * Update queue status
     */
    public function update_queue_status($id, $status, $error = null) {
        $this->db->where('id', $id)->update('affiliate_sync_queue', [
            'status' => $status,
            'last_error' => $error,
            'retry_count' => 'retry_count + 1',
            'updated_at' => $this->get_current_time()
        ]);
    }
    
    /**
     * Get campaign summary (FIXED - tanpa campaign_name di SELECT)
     */
public function get_campaign_summary($campaign_id = null, $date_filter = null) {
    if (!$date_filter) {
        $date_filter = date('Y-m-d');
    }
    
    $sql = "
        SELECT 
            c.campaign_id,
            c.campaign_name,
            c.status,
            c.last_sync as campaign_last_sync,
            COALESCE(SUM(o.gmv), 0) as actual_gmv,
            COUNT(DISTINCT o.order_id) as actual_orders,
            COALESCE(SUM(o.estimated_commission), 0) as total_commission,
            COUNT(DISTINCT o.creator_username) as actual_creators
        FROM affiliate_campaigns c
        LEFT JOIN affiliate_orders o 
            ON c.campaign_id = o.campaign_id 
            AND o.order_date_local = ?
            AND o.order_status IN ('SETTLED', 'PENDING', 'PROCESSING')
            AND o.creator_username IS NOT NULL 
            AND o.creator_username != ''
        WHERE c.status = 'ONGOING'
    ";
    
    if ($campaign_id) {
        $sql .= " AND c.campaign_id = ?";
        $sql .= " GROUP BY c.campaign_id, c.campaign_name, c.status, c.last_sync ORDER BY actual_gmv DESC";
        return $this->db->query($sql, [$date_filter, $campaign_id])->row();
    } else {
        $sql .= " GROUP BY c.campaign_id, c.campaign_name, c.status, c.last_sync ORDER BY actual_gmv DESC";
        return $this->db->query($sql, [$date_filter])->result();
    }
}
    /**
     * Get GMV breakdown by campaign (FIXED)
     */
    public function get_gmv_breakdown($days = 30) {
        $sql = "
            SELECT 
                DATE(o.order_time) as date,
                o.campaign_id,
                c.campaign_name,
                COALESCE(SUM(o.gmv), 0) as daily_gmv,
                COUNT(DISTINCT o.order_id) as daily_orders,
                COALESCE(SUM(o.actual_commission), 0) as daily_commission
            FROM affiliate_orders o
            LEFT JOIN affiliate_campaigns c ON o.campaign_id = c.campaign_id
            WHERE o.order_time >= DATE_SUB(NOW(), INTERVAL ? DAY)
                AND (o.order_status NOT IN ('CANCELLED', 'REFUNDED') OR o.order_status IS NULL)
            GROUP BY DATE(o.order_time), o.campaign_id
            ORDER BY date DESC, daily_gmv DESC
        ";
        
        $result = $this->db->query($sql, [$days])->result();
        
        // Log untuk debugging
        log_message('info', 'GMV Breakdown Query executed for ' . $days . ' days, got ' . count($result) . ' rows');
        
        return $result;
    }
    
    /**
     * Get active creators with links (FIXED)
     */
    public function get_active_creators() {
    // Cek apakah affiliate_creator_links kosong
    $count = $this->db->count_all('affiliate_creator_links');
    
    if ($count == 0) {
        // Jika kosong, ambil langsung dari orders
        $sql = "
            SELECT 
                o.creator_username,
                COUNT(DISTINCT o.campaign_id) as active_campaigns,
                COUNT(DISTINCT o.product_id) as total_products,
                COUNT(*) as total_orders,
                SUM(o.gmv) as total_gmv,
                SUM(o.actual_commission) as total_commission,
                MAX(o.order_time) as last_active
            FROM affiliate_orders o
            WHERE o.creator_username IS NOT NULL 
                AND o.creator_username != ''
                AND o.order_status NOT IN ('CANCELLED', 'REFUNDED')
            GROUP BY o.creator_username
            ORDER BY total_gmv DESC
        ";
        
        return $this->db->query($sql)->result();
    }
    
    // Jika ada data di affiliate_creator_links
    $sql = "
        SELECT 
            l.creator_username,
            COUNT(DISTINCT l.campaign_id) as active_campaigns,
            COUNT(DISTINCT l.product_id) as total_products,
            SUM(l.total_orders) as total_orders,
            SUM(l.total_gmv) as total_gmv,
            SUM(l.total_commission) as total_commission,
            MAX(l.last_sync) as last_active
        FROM affiliate_creator_links l
        WHERE l.status = 'ACTIVE'
        GROUP BY l.creator_username
        ORDER BY total_gmv DESC
    ";
    
    return $this->db->query($sql)->result();
}
    
    /**
     * Get total GMV for all campaigns
     */
    public function get_total_gmv() {
        $result = $this->db->select('COALESCE(SUM(gmv), 0) as total')
                           ->from('affiliate_orders')
                           ->where('order_status NOT IN ("CANCELLED", "REFUNDED")', null, false)
                           ->get()
                           ->row();
        
        return $result->total ?? 0;
    }
    
    /**
     * Get total orders
     */
    public function get_total_orders() {
        $result = $this->db->select('COUNT(DISTINCT order_id) as total')
                           ->from('affiliate_orders')
                           ->where('order_status NOT IN ("CANCELLED", "REFUNDED")', null, false)
                           ->get()
                           ->row();
        
        return $result->total ?? 0;
    }
    
    /**
     * Get recent orders
     */
    public function get_recent_orders($limit = 10) {
        return $this->db->select('order_id, product_name, creator_username, gmv, actual_commission, order_time')
                        ->from('affiliate_orders')
                        ->order_by('order_time', 'DESC')
                        ->limit($limit)
                        ->get()
                        ->result();
    }
    
    /**
     * Get last sync time
     */
    public function get_last_sync() {
        $result = $this->db->select('MAX(end_time) as last_sync')
                           ->from('affiliate_sync_logs')
                           ->where('status', 'success')
                           ->get()
                           ->row();
        
        return $result->last_sync ?? null;
    }
    
    /**
     * Get queue pending count
     */
    public function get_queue_pending_count() {
        return $this->db->where('status', 'pending')
                        ->count_all_results('affiliate_sync_queue');
    }
    
    /**
     * Get campaign by ID with details
     */
    public function get_campaign_detail($campaign_id) {
    $campaign = $this->db->where('campaign_id', $campaign_id)
                         ->get('affiliate_campaigns')
                         ->row();
    
    if (!$campaign) {
        return null;
    }
    
    // Perbaiki query products - pastikan mengambil data yang benar
    $products = $this->db->select('
            product_id,
            product_name,
            price,
            commission_rate,
            sales_count,
            gmv,
            image_url,
            status,
            last_sync
        ')
        ->where('campaign_id', $campaign_id)
        ->order_by('gmv', 'DESC')
        ->limit(10)
        ->get('affiliate_products')
        ->result();
    
    // Format products dengan benar
    $formatted_products = [];
    foreach ($products as $product) {
        $formatted_products[] = (object)[
            'product_id' => $product->product_id,
            'product_name' => $product->product_name,
            'price' => floatval($product->price),  // Pastikan numeric
            'commission_rate' => floatval($product->commission_rate),
            'sales_count' => intval($product->sales_count),
            'gmv' => floatval($product->gmv),
            'image_url' => $product->image_url
        ];
    }
    
    // Perbaiki query top creators - hitung commission dengan benar
    $top_creators = $this->db->select('
            o.creator_username,
            COUNT(DISTINCT o.order_id) as total_orders,
            SUM(o.gmv) as total_gmv,
            SUM(o.actual_commission) as total_commission,
            AVG(o.commission_rate) as avg_commission_rate,
            MIN(o.order_time) as first_order,
            MAX(o.order_time) as last_order
        ')
        ->from('affiliate_orders o')
        ->where('o.campaign_id', $campaign_id)
        ->where('o.creator_username IS NOT NULL')
        ->where('o.creator_username !=', '')
        ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
        ->group_by('o.creator_username')
        ->order_by('total_gmv', 'DESC')
        ->limit(10)
        ->get()
        ->result();
    
    // Format creators dengan commission yang benar
    $formatted_creators = [];
    foreach ($top_creators as $creator) {
        $formatted_creators[] = (object)[
            'creator_username' => $creator->creator_username,
            'total_orders' => intval($creator->total_orders),
            'total_gmv' => floatval($creator->total_gmv),
            'total_commission' => floatval($creator->total_commission),
            'avg_commission_rate' => floatval($creator->avg_commission_rate)
        ];
    }
    
    // Daily stats
    $daily_stats = $this->db->select('
            DATE(order_time) as date,
            SUM(gmv) as daily_gmv,
            COUNT(DISTINCT order_id) as daily_orders,
            SUM(actual_commission) as daily_commission
        ')
        ->from('affiliate_orders')
        ->where('campaign_id', $campaign_id)
        ->where('order_time >=', date('Y-m-d H:i:s', strtotime('-30 days')))
        ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
        ->group_by('DATE(order_time)')
        ->order_by('date', 'DESC')
        ->get()
        ->result();
    
    return (object)[
        'campaign' => $campaign,
        'products' => $formatted_products,
        'top_creators' => $formatted_creators,
        'daily_stats' => $daily_stats
    ];
}


public function save_creator_content_statistics($campaign_id, $product_id, $creator_id, $creator_username, $creator_temp_id, $stat) {
    $now = $this->get_current_time();
    
    $data = [
        'campaign_id' => $campaign_id,
        'product_id' => $product_id,
        'creator_id' => $creator_id,
        'creator_username' => $creator_username,
        'creator_temp_id' => $creator_temp_id,
        'content_type' => $stat['content_type'] ?? 'VIDEO',
        'cover_img_url' => $stat['cover_img_url'] ?? null,
        'source_url' => $stat['source_url'] ?? null,
        'linked_tiktok_video' => $stat['linked_tiktok_video'] ?? null,
        'view_count' => intval($stat['view_count'] ?? 0),
        'like_count' => intval($stat['like_count'] ?? 0),
        'comment_count' => intval($stat['comment_num'] ?? 0),
        'paid_order_count' => intval($stat['paid_order_num'] ?? 0),
        'paid_amount' => floatval($stat['paid_amount'] ?? 0),
        'published_date' => $stat['published_date'] ?? null,
        'content_end_date' => $stat['content_end_date'] ?? null,
        'last_sync' => $now,
        'updated_at' => $now
    ];
    
    // Cek apakah sudah ada
    $existing = $this->db->where('campaign_id', $campaign_id)
                         ->where('product_id', $product_id)
                         ->where('creator_id', $creator_id)
                         ->where('content_type', $data['content_type'])
                         ->get('creator_content_statistics')
                         ->row();
    
    if ($existing) {
        $this->db->where('id', $existing->id)->update('creator_content_statistics', $data);
        return $existing->id;
    } else {
        $data['created_at'] = $now;
        $this->db->insert('creator_content_statistics', $data);
        return $this->db->insert_id();
    }
}
public function sync_all_creator_content_statistics() {
    $campaigns = $this->db->select('campaign_id')
                         ->where('status', 'ONGOING')
                         ->get('affiliate_campaigns')
                         ->result();
    
    $total_synced = 0;
    
    foreach ($campaigns as $campaign) {
        // Get all active affiliate links for this campaign
        $links = $this->db->select('acl.*, c.creator_hid')
                         ->from('affiliate_creator_links acl')
                         ->join('creators c', 'acl.creator_id = c.id', 'left')
                         ->where('acl.campaign_id', $campaign->campaign_id)
                         ->where('acl.status', 'ACTIVE')
                         ->get()
                         ->result();
        
        foreach ($links as $link) {
            if (!empty($link->creator_hid)) {
                // Call API to get content statistics
                $result = $this->jsm_api->get_creator_content_statistics(
                    $campaign->campaign_id,
                    $link->product_id,
                    $link->creator_hid
                );
                
                if ($result['success'] && !empty($result['data']['creator_content_statistics'])) {
                    foreach ($result['data']['creator_content_statistics'] as $stat) {
                        $this->save_creator_content_statistics(
                            $campaign->campaign_id,
                            $link->product_id,
                            $link->creator_id,
                            $link->creator_username,
                            $link->creator_hid,
                            $stat
                        );
                        $total_synced++;
                    }
                }
            }
        }
    }
    
    return $total_synced;
}
/**
 * Save campaign creator performance data
 */
public function save_campaign_creator_performance($data) {
    $now = $this->get_current_time();
    
    $existing = $this->db->where('campaign_id', $data['campaign_id'])
                         ->where('product_id', $data['product_id'])
                         ->where('creator_open_id', $data['creator_open_id'])
                         ->get('campaign_creator_performance')
                         ->row();
    
    $save_data = [
        'campaign_id' => $data['campaign_id'],
        'product_id' => $data['product_id'],
        'product_name' => $data['product_name'] ?? '',
        'affiliate_product_id' => $data['affiliate_product_id'] ?? '',
        'creator_open_id' => $data['creator_open_id'],
        'creator_username' => $data['creator_username'] ?? '',
        'creator_nick_name' => $data['creator_nick_name'] ?? '',
        'creator_avatar' => $data['creator_avatar'] ?? '',
        'follower_count' => intval($data['follower_count'] ?? 0),
        'commission' => intval($data['commission'] ?? 0),
        'paid_amount' => floatval($data['paid_amount'] ?? 0),
        'video_count' => intval($data['video_count'] ?? 0),
        'room_count' => intval($data['room_count'] ?? 0),
        'free_sample_status' => $data['free_sample_status'] ?? '',
        'effective_start_time' => $data['effective_start_time'] ?? null,
        'effective_end_time' => $data['effective_end_time'] ?? null,
        'is_active' => 1,
        'last_sync' => $now,
        'updated_at' => $now
    ];
    
    if ($existing) {
        $this->db->where('id', $existing->id)->update('campaign_creator_performance', $save_data);
        return $existing->id;
    } else {
        $save_data['created_at'] = $now;
        $this->db->insert('campaign_creator_performance', $save_data);
        return $this->db->insert_id();
    }
}

/**
 * Sync all campaign creator performance for active campaigns
 */
public function sync_all_campaign_creator_performance() {
    $campaigns = $this->db->select('campaign_id')
                         ->where('status', 'ONGOING')
                         ->get('affiliate_campaigns')
                         ->result();
    
    $total_synced = 0;
    
    foreach ($campaigns as $campaign) {
        echo "Processing campaign: {$campaign->campaign_id}\n";
        
        // Ambil semua produk APPROVED di campaign ini
        $products = $this->db->select('product_id, product_name')
                             ->from('affiliate_products')
                             ->where('campaign_id', $campaign->campaign_id)
                             ->where('review_status', 'APPROVED')
                             ->get()
                             ->result();
        
        foreach ($products as $product) {
            $creators = $this->jsm_api->get_all_activated_creators($campaign->campaign_id);
            
            foreach ($creators as $creator) {
                $this->save_campaign_creator_performance($creator);
                $total_synced++;
            }
        }
    }
    
    return $total_synced;
}

/**
 * Get creators who activated link today
 */
public function get_creators_activated_today() {
    $today = date('Y-m-d');
    
    $sql = "
        SELECT 
            ccp.*,
            COUNT(DISTINCT ccp.product_id) as total_products,
            GROUP_CONCAT(DISTINCT ccp.product_name) as product_names
        FROM campaign_creator_performance ccp
        WHERE DATE(ccp.effective_start_time) = ?
        GROUP BY ccp.creator_open_id
        ORDER BY ccp.effective_start_time DESC
    ";
    
    return $this->db->query($sql, [$today])->result();
}

/**
 * Get count of creators who activated link today
 */
public function get_creators_activated_today_count() {
    $today = date('Y-m-d');
    
    $result = $this->db->select('COUNT(DISTINCT creator_open_id) as total')
                       ->from('campaign_creator_performance')
                       ->where('DATE(effective_start_time)', $today)
                       ->get()
                       ->row();
    
    return intval($result->total ?? 0);
}

public function get_real_gmv($date = null, $settle_filter = 'all') {
    if (!$date) {
        $date = date('Y-m-d');
    }
    
    $this->db->select('
        COALESCE(SUM(gmv), 0) as total_gmv,
        COUNT(DISTINCT order_id) as total_orders,
        COALESCE(SUM(estimated_commission), 0) as total_estimated_commission,
        COALESCE(SUM(actual_commission), 0) as total_actual_commission,
        COUNT(DISTINCT creator_username) as total_creators
    ');
    $this->db->from('affiliate_orders');
    $this->db->where('order_date_local', $date);
    $this->db->where('creator_username IS NOT NULL');
    $this->db->where("creator_username != ''");
    
    // 🔥 Filter status
    switch ($settle_filter) {
        case 'settled':
            $this->db->where('order_status', 'SETTLED');
            break;
        case 'valid':
            $this->db->where_in('order_status', ['SETTLED', 'PENDING', 'PROCESSING']);
            break;
        case 'all':
        default:
            // 🔥 Samakan: exclude CANCELLED & REFUNDED, include NULL
            $this->db->group_start()
                ->where_not_in('order_status', ['CANCELLED', 'REFUNDED'])
                ->or_where('order_status IS NULL')
            ->group_end();
            break;
    }
    
    return $this->db->get()->row();
}

/**
 * Get GMV comparison (today vs yesterday)
 */
public function get_gmv_comparison() {
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $current_time = date('H:i:s');
    
    // 🔥 UBAH 'valid' MENJADI 'all'
    $today_data = $this->get_real_gmv_time_range($today, '00:00:00', $current_time, 'all');
    $yesterday_data = $this->get_real_gmv_time_range($yesterday, '00:00:00', $current_time, 'all');
    
    $today_gmv = floatval($today_data->total_gmv ?? 0);
    $yesterday_gmv = floatval($yesterday_data->total_gmv ?? 0);
    
    return [
        'today' => [
            'gmv' => $today_gmv,
            'orders' => intval($today_data->total_orders ?? 0),
            'estimated_commission' => floatval($today_data->total_estimated_commission ?? 0),
            'actual_commission' => floatval($today_data->total_actual_commission ?? 0),
            'creators' => intval($today_data->total_creators ?? 0)
        ],
        'yesterday' => [
            'gmv' => $yesterday_gmv,
            'orders' => intval($yesterday_data->total_orders ?? 0),
            'estimated_commission' => floatval($yesterday_data->total_estimated_commission ?? 0),
            'actual_commission' => floatval($yesterday_data->total_actual_commission ?? 0),
            'creators' => intval($yesterday_data->total_creators ?? 0)
        ],
        'growth_percent' => $yesterday_gmv > 0 ? round((($today_gmv - $yesterday_gmv) / $yesterday_gmv) * 100, 1) : ($today_gmv > 0 ? 100 : 0)
    ];
}

/**
 * Get REAL GMV dengan filter jam (time range)
 */
public function get_real_gmv_time_range($date = null, $time_from = '00:00:00', $time_to = '23:59:59', $settle_filter = 'all') {
    if (!$date) {
        $date = date('Y-m-d');
    }
    
    $this->db->select('
        COALESCE(SUM(gmv), 0) as total_gmv,
        COUNT(DISTINCT order_id) as total_orders,
        COALESCE(SUM(estimated_commission), 0) as total_estimated_commission,
        COALESCE(SUM(actual_commission), 0) as total_actual_commission,
        COUNT(DISTINCT creator_username) as total_creators
    ');
    $this->db->from('affiliate_orders');
    $this->db->where('order_date_local', $date);
    $this->db->where('order_time >=', $date . ' ' . $time_from);
    $this->db->where('order_time <=', $date . ' ' . $time_to);
    
    // 🔥 HAPUS filter creator_username untuk case 'all'
    // Filter creator hanya untuk case 'valid' dan 'settled'
    
    switch ($settle_filter) {
        case 'settled':
            $this->db->where('order_status', 'SETTLED');
            $this->db->where('creator_username IS NOT NULL');
            $this->db->where("creator_username != ''");
            break;
        case 'valid':
            $this->db->where_in('order_status', ['SETTLED', 'PENDING', 'PROCESSING']);
            $this->db->where('creator_username IS NOT NULL');
            $this->db->where("creator_username != ''");
            break;
        case 'all':
        default:
            $this->db->group_start()
                ->where_not_in('order_status', ['CANCELLED', 'REFUNDED'])
                ->or_where('order_status IS NULL')
            ->group_end();
            // 🔥 TANPA filter creator
            break;
    }
    
    return $this->db->get()->row();
}

/**
 * Get top creators by GMV (today)
 */
public function get_top_creators_today($limit = 10) {
    $today = date('Y-m-d');
    
    $sql = "
        SELECT 
            creator_username,
            COUNT(DISTINCT order_id) as total_orders,
            SUM(gmv) as total_gmv,
            SUM(estimated_commission) as total_estimated_commission,
            SUM(actual_commission) as total_actual_commission,
            COUNT(DISTINCT campaign_id) as total_campaigns,
            COUNT(DISTINCT product_id) as total_products
        FROM affiliate_orders
        WHERE order_date_local = ?
            AND creator_username IS NOT NULL 
            AND creator_username != ''
            AND order_status IN ('SETTLED', 'PENDING','PROCESSING')
        GROUP BY creator_username
        ORDER BY total_gmv DESC
        LIMIT ?
    ";
    
    return $this->db->query($sql, [$today, $limit])->result();
}

/**
 * Get recent orders dengan filter yang benar
 */
public function get_recent_orders_valid($limit = 20) {
    return $this->db->select('
            o.order_id, 
            o.product_id,
            o.campaign_id,
            o.product_name, 
            o.creator_username, 
            o.gmv, 
            o.estimated_commission, 
            o.actual_commission, 
            o.order_status,
            o.order_time, 
            o.order_date_local,
            MAX(COALESCE(p1.image_url, p2.image_url)) as image_url
        ', false)
        ->from('affiliate_orders o')
        ->join(
            'affiliate_products p1',
            'p1.product_id = o.product_id AND p1.campaign_id = o.campaign_id',
            'left'
        )
        ->join(
            'affiliate_products p2',
            'p2.product_id = o.product_id',
            'left'
        )
        ->where('o.order_date_local >=', date('Y-m-d', strtotime('-7 days')))
        ->where_in('o.order_status', ['SETTLED', 'PENDING', 'PROCESSING'])
        ->where('o.creator_username IS NOT NULL')
        ->where("o.creator_username != ''")
        ->group_by([
            'o.order_id', 
            'o.product_id',
            'o.campaign_id',
            'o.product_name', 
            'o.creator_username', 
            'o.gmv', 
            'o.estimated_commission', 
            'o.actual_commission', 
            'o.order_status',
            'o.order_time', 
            'o.order_date_local'
        ])
        ->order_by('o.order_time', 'DESC')
        ->limit($limit)
        ->get()
        ->result();
}
public function get_top_brands_today($limit = 10) {

    $today = date('Y-m-d');

    $sql = "
        SELECT
            p.shop_name,
            COUNT(DISTINCT o.order_id) as total_orders,
            SUM(o.gmv) as total_gmv,
            SUM(o.estimated_commission) as total_commission,
            COUNT(DISTINCT o.creator_username) as total_creators
        FROM affiliate_orders o

        LEFT JOIN affiliate_products p
            ON p.product_id = o.product_id

        WHERE o.order_date_local >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            AND o.order_status IN ('SETTLED', 'PENDING', 'PROCESSING')
            AND p.shop_name IS NOT NULL
            AND p.shop_name != ''

        GROUP BY p.shop_name

        ORDER BY total_gmv DESC

        LIMIT ?
    ";

    return $this->db->query($sql, [$limit])->result();
}
public function get_top_creators_30_days($limit = 10) {
    $sql = "
        SELECT 
            creator_username,
            COUNT(DISTINCT order_id) as total_orders,
            SUM(gmv) as total_gmv,
            SUM(estimated_commission) as total_estimated_commission,
            SUM(actual_commission) as total_actual_commission,
            COUNT(DISTINCT campaign_id) as total_campaigns,
            COUNT(DISTINCT product_id) as total_products
        FROM affiliate_orders
        WHERE order_date_local >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            AND creator_username IS NOT NULL 
            AND creator_username != ''
            AND order_status IN ('SETTLED', 'PENDING', 'PROCESSING')
        GROUP BY creator_username
        ORDER BY total_gmv DESC
        LIMIT ?
    ";

    return $this->db->query($sql, [$limit])->result();
}
/**
 * Get brand & creator metrics dengan time range
 */
public function get_brand_creator_metrics($date, $time_to = '23:59:59') {
    $data = new stdClass();
    
    // 🔥 Brands joined
    $brands = $this->db->select('COUNT(DISTINCT shop_name) as total')
        ->from('affiliate_products')
        ->where('DATE(approved_at)', $date)
        ->where('TIME(approved_at) <=', $time_to)
        ->where('review_status', 'APPROVED')
        ->get()
        ->row();
    $data->brands_joined = intval($brands->total ?? 0);
    
    // 🔥 Creators with links
    $links = $this->db->select('COUNT(DISTINCT creator_username) as total')
        ->from('affiliate_creator_links')
        ->where('DATE(created_at)', $date)
        ->where('TIME(created_at) <=', $time_to)
        ->where('status', 'ACTIVE')
        ->get()
        ->row();
    $data->creators_with_links = intval($links->total ?? 0);
    
    // 🔥 Creators activated
    $activated = $this->db->select('COUNT(DISTINCT creator_open_id) as total')
        ->from('campaign_creator_performance')
        ->where('DATE(effective_start_time)', $date)
        ->where('TIME(effective_start_time) <=', $time_to)
        ->get()
        ->row();
    $data->creators_activated = intval($activated->total ?? 0);
    
    // 🔥 Creators with content
    $content = $this->db->select('
            COUNT(DISTINCT creator_id) as creators_with_content,
            COUNT(*) as total_contents,
            SUM(view_count) as total_views
        ')
        ->from('creator_content_statistics')
        ->where('DATE(published_date)', $date)
        ->where('TIME(published_date) <=', $time_to)
        ->get()
        ->row();
    $data->creators_with_content = intval($content->creators_with_content ?? 0);
    $data->total_contents = intval($content->total_contents ?? 0);
    $data->total_views = intval($content->total_views ?? 0);
    
    return $data;
}

}