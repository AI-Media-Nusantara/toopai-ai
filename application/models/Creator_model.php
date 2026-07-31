<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Creator_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Get all creators
     */
    public function get_all_creators() {
        $this->db->order_by('total_gmv', 'DESC');
        return $this->db->get('creators')->result();
    }
    
    /**
     * Get creators by IS (Influencer Success)
     */
    public function get_creators_by_is($is_id = null) {
        if ($is_id) {
            $this->db->where('is_id', $is_id);
        }
        $this->db->order_by('total_gmv', 'DESC');
        return $this->db->get('creators')->result();
    }
    
    /**
     * Get creators by status
     */
  public function get_creators_by_status($status, $limit = null) {
        $this->db->where('status', $status);
        if ($limit) {
            $this->db->limit($limit);
        }
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('creators')->result();
    }
    
    /**
     * Get top creators by GMV
     */
    public function get_top_creators($limit = 5) {
        return $this->db->order_by('total_gmv', 'DESC')->limit($limit)->get('creators')->result();
    }
    
    /**
     * Get creator by ID
     */
      public function get_creator_by_id($id) {
        return $this->db->get_where('creators', ['id' => $id])->row();
    }
    
    /**
     * Get creator by username
     */
    public function get_creator_by_username($username) {
        return $this->db->get_where('creators', ['username' => $username])->row();
    }
    
    /**
     * Add new creator
     */
    public function add_creator($data) {
        $existing = $this->db->get_where('creators', ['username' => $data['username']])->row();
        if ($existing) {
            return ['success' => false, 'message' => 'Username already exists'];
        }
        
        $creator_data = [
            'username' => $data['username'],
            'full_name' => $data['full_name'] ?? null,
            'category' => $data['category'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'is_id' => $data['is_id'] ?? null,
            'status' => 'PENDING',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $this->db->insert('creators', $creator_data);
        
        if ($result) {
            return ['success' => true, 'id' => $this->db->insert_id()];
        }
        return ['success' => false, 'message' => $this->db->error()['message']];
    }
    
    
    /**
     * Update creator
     */
    public function update_creator($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update('creators', $data);
    }
    
    /**
     * Delete creator
     */
    public function delete_creator($id) {
        $this->db->where('id', $id);
        return $this->db->delete('creators');
    }
    
     /**
     * Assign creator to campaign
     */
    public function assign_to_campaign($creator_id, $campaign_id) {
        $existing = $this->db->get_where('creator_campaigns', [
            'creator_id' => $creator_id,
            'campaign_id' => $campaign_id
        ])->row();
        
        if (!$existing) {
            $this->db->insert('creator_campaigns', [
                'creator_id' => $creator_id,
                'campaign_id' => $campaign_id,
                'status' => 'INVITED',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        return true;
    }
    
     
    /**
     * Get creator's campaigns
     */
    public function get_creator_campaigns($creator_id) {
        return $this->db->select('c.*, cc.status as member_status, cc.joined_at')
            ->from('creator_campaigns cc')
            ->join('campaigns c', 'c.id = cc.campaign_id')
            ->where('cc.creator_id', $creator_id)
            ->get()
            ->result();
    }
    
    public function generate_affiliate_link($creator_id, $campaign_id, $product_id, $product_name, $commission_rate) {
        // Call TikTok API to generate link
        $this->load->library('Jsm_api');
        $link_result = $this->jsm_api->generate_promotion_link($campaign_id, $product_id, $commission_rate);
        
        if ($link_result['success']) {
            $data = [
                'creator_id' => $creator_id,
                'campaign_id' => $campaign_id,
                'product_id' => $product_id,
                'product_name' => $product_name,
                'affiliate_link' => $link_result['link'],
                'commission_rate' => $commission_rate,
                'expires_at' => $link_result['expire_at'],
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->insert('creator_affiliate_links', $data);
            return ['success' => true, 'link' => $link_result['link']];
        }
        
        return ['success' => false, 'message' => $link_result['message']];
    }
     /**
     * Get creator's affiliate links
     */
public function get_creator_affiliate_links($creator_id) {
    // Gunakan tabel yang benar: affiliate_creator_links
    $links = $this->db->select('acl.*, cp.campaign_name')
        ->from('affiliate_creator_links acl')
        ->join('affiliate_campaigns cp', 'cp.campaign_id = acl.campaign_id', 'left')
        ->where('acl.creator_id', $creator_id)
        ->order_by('acl.created_at', 'DESC')
        ->get()
        ->result();
    
    // Jika kolom creator_id belum ada di affiliate_creator_links, gunakan creator_username
    if (empty($links)) {
        // Cari creator berdasarkan username
        $creator = $this->db->select('username')->where('id', $creator_id)->get('creators')->row();
        if ($creator) {
            $links = $this->db->select('acl.*, cp.campaign_name')
                ->from('affiliate_creator_links acl')
                ->join('affiliate_campaigns cp', 'cp.campaign_id = acl.campaign_id', 'left')
                ->where('acl.creator_username', $creator->username)
                ->order_by('acl.created_at', 'DESC')
                ->get()
                ->result();
        }
    }
    
    return $links;
}

/**
 * Get creator affiliate links by username - ALTERNATIVE
 */
public function get_creator_links_by_username($username) {
    return $this->db->select('acl.*, cp.campaign_name')
        ->from('affiliate_creator_links acl')
        ->join('affiliate_campaigns cp', 'cp.campaign_id = acl.campaign_id', 'left')
        ->where('acl.creator_username', $username)
        ->order_by('acl.created_at', 'DESC')
        ->get()
        ->result();
}

    
    
    /**
     * Sync creator from order data
     */
    public function sync_creator_from_order($order) {
        if (empty($order['creator_username'])) {
            return null;
        }
        
        $existing = $this->db->get_where('creators', ['username' => $order['creator_username']])->row();
        
        if (!$existing) {
            $data = [
                'username' => $order['creator_username'],
                'creator_id' => $order['creator_hid'] ?? null,
                'status' => 'ACTIVE',
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->db->insert('creators', $data);
            $creator_id = $this->db->insert_id();
        } else {
            $creator_id = $existing->id;
            
            // Update total GMV
            $total_gmv = $this->db->select_sum('affiliate_gmv')
                ->get_where('realtime_orders', ['creator_username' => $order['creator_username']])
                ->row()
                ->affiliate_gmv ?? 0;
            
            $this->db->where('id', $creator_id);
            $this->db->update('creators', ['total_gmv' => $total_gmv, 'updated_at' => date('Y-m-d H:i:s')]);
        }
        
        return $creator_id;
    }
    
    
    /**
 * Get approved creators for monitoring (aktif, punya campaign, punya GMV)
 */
public function get_monitoring_creators($limit = 10) {
        $is_id = $this->session->userdata('user_id');
        
        $this->db->select('c.*, 
            COALESCE(SUM(pd.gmv), 0) as total_gmv,
            COUNT(DISTINCT pd.id) as days_active,
            GROUP_CONCAT(DISTINCT cc.campaign_id) as campaign_ids')
            ->from('creators c')
            ->join('creator_campaigns cc', 'cc.creator_id = c.id', 'left')
            ->join('performance_data pd', 'pd.creator_id = c.id', 'left')
            ->where('c.status', 'ACTIVE')
            ->where('c.is_id', $is_id)
            ->group_by('c.id')
            ->order_by('total_gmv', 'DESC')
            ->limit($limit);
        
        return $this->db->get()->result();
    }

/**
 * Get monitoring stats untuk dashboard
 */
 public function get_monitoring_stats() {
        $is_id = $this->session->userdata('user_id');
        
        $total_approved = $this->db->where('status', 'ACTIVE')
            ->where('is_id', $is_id)
            ->count_all_results('creators');
        
        $total_gmv = $this->db->select('SUM(pd.gmv) as total')
            ->from('creators c')
            ->join('performance_data pd', 'pd.creator_id = c.id')
            ->where('c.status', 'ACTIVE')
            ->where('c.is_id', $is_id)
            ->get()
            ->row()
            ->total ?? 0;
        
        $top_performer = $this->db->select('c.username, SUM(pd.gmv) as total_gmv')
            ->from('creators c')
            ->join('performance_data pd', 'pd.creator_id = c.id')
            ->where('c.status', 'ACTIVE')
            ->where('c.is_id', $is_id)
            ->group_by('c.id')
            ->order_by('total_gmv', 'DESC')
            ->limit(1)
            ->get()
            ->row();
        
        return [
            'total_approved' => $total_approved,
            'total_gmv' => $total_gmv,
            'top_performer' => $top_performer
        ];
    }

/**
 * Approve creator (pindah ke status ACTIVE)
 */
public function approve_creator($creator_id) {
    $this->db->where('id', $creator_id);
    return $this->db->update('creators', [
        'status' => 'ACTIVE',
        'updated_at' => date('Y-m-d H:i:s')
    ]);
}

public function update_creator_status($creator_id, $status) {
    $update_data = [
        'status' => $status,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    if ($status == 'ACTIVE') {
        $update_data['approved_at'] = date('Y-m-d H:i:s');
    }
    
    $this->db->where('id', $creator_id);
    return $this->db->update('creators', $update_data);
}
/**
 * Assign creator ke campaign
 */
public function assign_creator_to_campaign($creator_id, $campaign_id) {
    $data = [
        'campaign_id' => $campaign_id,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $this->db->where('id', $creator_id);
    return $this->db->update('creators', $data);
}


 public function get_creator_products($uid, $page = 1, $pageSize = 50)
    {
        $time = time();
        $cnonce = rand(10000000, 99999999);

        $url = $this->baseUrl . "/api/author/v3/detail/goodsList"
            . "?page={$page}"
            . "&uid={$uid}"
            . "&date_type=28"  // 28 hari terakhir
            . "&order=sold_count,2"  // Urutkan berdasarkan penjualan
            . "&pagesize={$pageSize}"
            . "&_time={$time}"
            . "&cnonce={$cnonce}";

        $headers = $this->headers(
            'https://www.fastmoss.com/id/influencer/detail/' . $uid
        );

        $json = $this->request_json($url, $headers);

        return $json['data']['list'] 
            ?? $json['data']['rows'] 
            ?? $json['data']['data'] 
            ?? [];
    }

    /**
     * Get all products sold by a creator (paginated - get all pages)
     */
    public function get_all_creator_products($uid, $maxPages = 5)
    {
        $allProducts = [];
        $page = 1;
        $pageSize = 50;

        do {
            $products = $this->get_creator_products($uid, $page, $pageSize);
            
            if (empty($products)) {
                break;
            }

            $allProducts = array_merge($allProducts, $products);
            $page++;
            
        } while (count($products) == $pageSize && $page <= $maxPages);

        return $allProducts;
    }
    
    public function get_creators_with_fastmoss_uid($limit = 10)
    {
        return $this->db->query("
            SELECT 
                c.id,
                c.username,
                c.full_name,
                c.phone,
                c.email,
                c.tiktok_open_id,
                c.fastmoss_uid,
                c.brand_id,
                c.avatar_url
            FROM creators c
            LEFT JOIN creator_contacts cc 
                ON cc.creator_username = c.username
            WHERE 
                c.fastmoss_uid IS NOT NULL
                AND c.fastmoss_uid != ''
                AND (
                    cc.id IS NULL
                    OR cc.status != 'COMPLETE'
                )
            GROUP BY c.username
            ORDER BY c.created_at ASC
            LIMIT ?
        ", [$limit])->result();
    }

    /**
     * Get creators that already have products synced
     */
    public function get_creators_with_products_synced($limit = 10)
    {
        return $this->db->query("
            SELECT 
                c.id,
                c.username,
                c.fastmoss_uid,
                COUNT(bcp.id) as product_count
            FROM creators c
            JOIN brand_creators bc ON bc.creator_username = c.username
            JOIN brand_creator_products bcp ON bcp.brand_creator_id = bc.id
            WHERE c.fastmoss_uid IS NOT NULL
            GROUP BY c.id
            HAVING product_count > 0
            ORDER BY product_count DESC
            LIMIT ?
        ", [$limit])->result();
    }
    
    
    /**
 * Get performance for all creators including unassigned (no handler)
 */
public function get_team_performance_with_unassigned($start_date = null, $end_date = null) {
    if (!$start_date) $start_date = date('Y-m-d', strtotime('-30 days'));
    if (!$end_date) $end_date = date('Y-m-d');
    
    // 1. Ambil semua IS
    $is_users = $this->db->select('id, username, full_name')
                         ->where('role', 'IS')
                         ->get('users')
                         ->result();
    
    $result = [];
    $total_gmv_all = 0;
    $total_orders_all = 0;
    $total_creators_all = 0;
    
    foreach ($is_users as $is) {
        // Hitung performa per IS
        $stats = $this->db->select('
                COALESCE(SUM(o.gmv), 0) as total_gmv,
                COUNT(DISTINCT o.order_id) as total_orders,
                COUNT(DISTINCT c.id) as total_creators
            ')
            ->from('creators c')
            ->join('affiliate_orders o', 'c.username = o.creator_username', 'inner')
            ->where('c.is_id', $is->id)
            ->where('o.order_date_local >=', $start_date)
            ->where('o.order_date_local <=', $end_date)
            ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->get()
            ->row();
        
        $result[] = (object)[
            'id' => $is->id,
            'username' => $is->username,
            'full_name' => $is->full_name,
            'role' => 'IS',
            'is_handler' => true,
            'total_gmv' => floatval($stats->total_gmv ?? 0),
            'total_orders' => intval($stats->total_orders ?? 0),
            'total_creators' => intval($stats->total_creators ?? 0),
            'is_unassigned' => false
        ];
        
        $total_gmv_all += floatval($stats->total_gmv ?? 0);
        $total_orders_all += intval($stats->total_orders ?? 0);
        $total_creators_all += intval($stats->total_creators ?? 0);
    }
    
    // 2. 🔥 HITUNG UNASSIGNED CREATORS (tanpa handler)
    $unassigned_stats = $this->db->select('
            COALESCE(SUM(o.gmv), 0) as total_gmv,
            COUNT(DISTINCT o.order_id) as total_orders,
            COUNT(DISTINCT c.id) as total_creators
        ')
        ->from('creators c')
        ->join('affiliate_orders o', 'c.username = o.creator_username', 'inner')
        ->where('c.is_id IS NULL')
        ->where('o.order_date_local >=', $start_date)
        ->where('o.order_date_local <=', $end_date)
        ->where('o.order_status NOT IN ("CANCELLED", "REFUNDED")')
        ->get()
        ->row();
    
    // 3. 🔥 TAMBAHKAN ROW UNASSIGNED
    $unassigned_gmv = floatval($unassigned_stats->total_gmv ?? 0);
    $unassigned_orders = intval($unassigned_stats->total_orders ?? 0);
    $unassigned_creators = intval($unassigned_stats->total_creators ?? 0);
    
    if ($unassigned_gmv > 0 || $unassigned_creators > 0) {
        $result[] = (object)[
            'id' => 0,
            'username' => 'unassigned',
            'full_name' => '⚠️ Non Handler (Unassigned)',
            'role' => 'UNASSIGNED',
            'is_handler' => false,
            'total_gmv' => $unassigned_gmv,
            'total_orders' => $unassigned_orders,
            'total_creators' => $unassigned_creators,
            'is_unassigned' => true
        ];
    }
    
    // Sort: Unassigned first (biar keliatan), lalu berdasarkan GMV
    usort($result, function($a, $b) {
        // Unassigned di paling atas
        if ($a->is_unassigned && !$b->is_unassigned) return -1;
        if (!$a->is_unassigned && $b->is_unassigned) return 1;
        return $b->total_gmv <=> $a->total_gmv;
    });
    
    // Total keseluruhan
    $summary = (object)[
        'total_members' => count($result),
        'total_gmv' => $total_gmv_all + $unassigned_gmv,
        'total_orders' => $total_orders_all + $unassigned_orders,
        'total_creators' => $total_creators_all + $unassigned_creators,
        'unassigned_gmv' => $unassigned_gmv,
        'unassigned_creators' => $unassigned_creators,
        'unassigned_orders' => $unassigned_orders
    ];
    
    return [
        'members' => $result,
        'summary' => $summary
    ];
}




}