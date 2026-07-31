<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Campaign_model extends CI_Model {
    
    private $table = 'affiliate_campaigns';  // ← Gunakan tabel affiliate_campaigns
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Get all campaigns
     */
    public function get_all_campaigns() {
        return $this->db->order_by('created_at', 'DESC')
                        ->get($this->table)
                        ->result();
    }
    
    /**
     * Get campaign by ID
     */
    public function get_campaign_by_id($campaign_id) {
        // $campaign_id bisa berupa id (int) atau campaign_id (varchar)
        if (is_numeric($campaign_id)) {
            return $this->db->where('id', $campaign_id)
                            ->get($this->table)
                            ->row();
        } else {
            return $this->db->where('campaign_id', $campaign_id)
                            ->get($this->table)
                            ->row();
        }
    }
    
    /**
     * Get campaign by campaign_id (string)
     */
    public function get_campaign_by_campaign_id($campaign_id) {
        return $this->db->where('campaign_id', $campaign_id)
                        ->get($this->table)
                        ->row();
    }
    
    /**
     * Get ongoing campaigns
     */
    public function get_ongoing_campaigns($limit = null) {
        $this->db->where('status', 'ONGOING')
                 ->order_by('created_at', 'DESC');
        
        if ($limit) {
            $this->db->limit($limit);
        }
        
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get campaigns by status
     */
    public function get_campaigns_by_status($status, $limit = null) {
        $this->db->where('status', $status)
                 ->order_by('created_at', 'DESC');
        
        if ($limit) {
            $this->db->limit($limit);
        }
        
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Sync campaign from API data
     */
    public function sync_campaign($campaign_data, $api_type = 'TOOPAI') {
        // Cek apakah sudah ada berdasarkan campaign_id
        $existing = $this->db->where('campaign_id', $campaign_data['id'])
                             ->get($this->table)
                             ->row();
        
        // Konversi timestamp ke datetime
        $start_date = null;
        $end_date = null;
        
        if (isset($campaign_data['campaign_start_time']) && $campaign_data['campaign_start_time']) {
            $start_date = date('Y-m-d H:i:s', $campaign_data['campaign_start_time']);
        } elseif (isset($campaign_data['start_time']) && $campaign_data['start_time']) {
            $start_date = date('Y-m-d H:i:s', $campaign_data['start_time']);
        }
        
        if (isset($campaign_data['campaign_end_time']) && $campaign_data['campaign_end_time']) {
            $end_date = date('Y-m-d H:i:s', $campaign_data['campaign_end_time']);
        } elseif (isset($campaign_data['end_time']) && $campaign_data['end_time']) {
            $end_date = date('Y-m-d H:i:s', $campaign_data['end_time']);
        }
        
        $data = [
            'campaign_id' => $campaign_data['id'],
            'campaign_name' => $campaign_data['name'] ?? $campaign_data['campaign_name'] ?? '',
            'status' => $campaign_data['status'] ?? 'ONGOING',
            'start_date' => $start_date,
            'end_date' => $end_date,
            'budget' => $campaign_data['budget'] ?? 0,
            'total_gmv' => $campaign_data['total_gmv'] ?? 0,
            'total_orders' => $campaign_data['total_orders'] ?? 0,
            'total_creators' => $campaign_data['total_creators'] ?? 0,
            'commission_rate' => $campaign_data['commission_rate'] ?? 0,
            'raw_data' => json_encode($campaign_data),
            'last_sync' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($existing) {
            $this->db->where('id', $existing->id)->update($this->table, $data);
            return $existing->id;
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
    }
    
    /**
     * Update campaign stats (GMV, Orders, Creators)
     */
    public function update_campaign_stats($campaign_id, $total_gmv, $total_orders) {
        // Hitung jumlah creator unik dari orders campaign ini
        $total_creators = $this->db->select('COUNT(DISTINCT creator_username) as total')
                                   ->from('affiliate_orders')
                                   ->where('campaign_id', $campaign_id)
                                   ->where('creator_username IS NOT NULL')
                                   ->where('creator_username !=', '')
                                   ->get()
                                   ->row()
                                   ->total ?? 0;
        
        $this->db->where('campaign_id', $campaign_id)
                 ->update($this->table, [
                     'total_gmv' => $total_gmv,
                     'total_orders' => $total_orders,
                     'total_creators' => $total_creators,
                     'updated_at' => date('Y-m-d H:i:s')
                 ]);
    }
    
    /**
     * Get campaign summary with real data from orders
     */
    public function get_campaign_summary($campaign_id = null) {
        $this->db->select('
            c.id,
            c.campaign_id,
            c.campaign_name,
            c.status,
            c.start_date,
            c.end_date,
            c.total_gmv as api_total_gmv,
            c.total_orders as api_total_orders,
            c.total_creators,
            c.last_sync,
            COUNT(DISTINCT o.order_id) as actual_orders,
            COALESCE(SUM(o.gmv), 0) as actual_gmv,
            COALESCE(SUM(o.actual_commission), 0) as total_commission,
            COUNT(DISTINCT o.creator_username) as actual_creators
        ');
        $this->db->from($this->table . ' c');
        $this->db->join('affiliate_orders o', 'c.campaign_id = o.campaign_id', 'left');
        
        if ($campaign_id) {
            $this->db->where('c.campaign_id', $campaign_id);
        }
        
        $this->db->group_by('c.id, c.campaign_id, c.campaign_name, c.status, c.start_date, c.end_date, c.total_gmv, c.total_orders, c.total_creators, c.last_sync');
        $this->db->order_by('c.last_sync', 'DESC');
        
        return $campaign_id ? $this->db->get()->row() : $this->db->get()->result();
    }
    
    /**
     * Get top campaigns by GMV
     */
    public function get_top_campaigns($limit = 5) {
        return $this->db->select('campaign_id, campaign_name, total_gmv, total_orders, total_creators')
                        ->from($this->table)
                        ->where('status', 'ONGOING')
                        ->order_by('total_gmv', 'DESC')
                        ->limit($limit)
                        ->get()
                        ->result();
    }
    
    /**
     * Get campaign performance chart data (last 30 days)
     */
    public function get_campaign_performance($campaign_id, $days = 30) {
        $sql = "
            SELECT 
                DATE(order_time) as date,
                COUNT(DISTINCT order_id) as daily_orders,
                SUM(gmv) as daily_gmv,
                SUM(actual_commission) as daily_commission,
                COUNT(DISTINCT creator_username) as daily_creators
            FROM affiliate_orders
            WHERE campaign_id = ?
                AND order_time >= DATE_SUB(NOW(), INTERVAL ? DAY)
                AND order_status NOT IN ('CANCELLED', 'REFUNDED')
            GROUP BY DATE(order_time)
            ORDER BY date ASC
        ";
        
        return $this->db->query($sql, [$campaign_id, $days])->result();
    }
}