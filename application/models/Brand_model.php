<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Brand_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    
    public function get_all_brands() {
        $this->db->order_by('total_gmv', 'DESC');
        return $this->db->get('brands')->result();
    }
    
   /**
     * Get brands for Task 1: HUNTING (status PENDING)
     */
    public function get_hunting_brands($limit = 1000) {
        return $this->get_brands_by_status('PENDING', $limit);
    }
    
    /**
     * Get brands for Task 2: DEAL & NEGOSIASI (status NEGOTIATING)
     */
    public function get_deal_brands($limit = 1000) {
        return $this->get_brands_by_status('NEGOTIATING', $limit);
    }
    
    /**
     * Get brands for Task 3: SETUP CAMPAIGN (status DEAL_CLOSED atau CAMPAIGN_READY)
     */
    public function get_setup_brands($limit = 1000) {
        $this->db->where_in('status', ['DEAL_CLOSED', 'CAMPAIGN_READY']);
        $this->db->limit($limit);
        $this->db->order_by('updated_at', 'DESC');
        return $this->db->get('brands')->result();
    }
    
    /**
     * Get brands for Task 4: MONITORING (status ACTIVE)
     */
    public function get_monitoring_brands($limit = 1000) {
        return $this->db->get_where('brands', ['status' => 'ACTIVE', 'current_task' => 4])->limit($limit)->get()->result();
    }
    
    /**
     * Update brand status dan current_task
     */
    public function update_brand_progress($brand_id, $status, $task = null) {
        $update_data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($task !== null) {
            $update_data['current_task'] = $task;
        }
        
        // Tambahkan timestamp khusus berdasarkan status
        if ($status == 'NEGOTIATING') {
            $update_data['last_contact'] = date('Y-m-d H:i:s');
        } elseif ($status == 'DEAL_CLOSED') {
            $update_data['deal_closed_at'] = date('Y-m-d H:i:s');
        } elseif ($status == 'ACTIVE') {
            $update_data['campaign_launched_at'] = date('Y-m-d H:i:s');
        }
        
        $this->db->where('id', $brand_id);
        return $this->db->update('brands', $update_data);
    }
    
    /**
     * Move brand to next task
     */
    public function move_to_next_task($brand_id) {
        $brand = $this->get_brand_by_id($brand_id);
        if (!$brand) return false;
        
        $next_status = '';
        $next_task = $brand->current_task + 1;
        
        switch ($brand->current_task) {
            case 1:
                $next_status = 'NEGOTIATING';
                break;
            case 2:
                $next_status = 'DEAL_CLOSED';
                break;
            case 3:
                $next_status = 'ACTIVE';
                break;
            case 4:
                $next_status = 'COMPLETED';
                break;
            default:
                return false;
        }
        
        return $this->update_brand_progress($brand_id, $next_status, $next_task);
    }
    
    
    /**
     * Get brand by ID
     */
    public function get_brand_by_id($id) {
        return $this->db->get_where('brands', ['id' => $id])->row();
    }
    
    /**
     * Get brands by status
     */
    public function get_brands_by_status($status, $limit = null) {
        if (is_array($status)) {
            $this->db->where_in('status', $status);
        } else {
            $this->db->where('status', $status);
        }
        if ($limit) {
            $this->db->limit($limit);
        }
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('brands')->result();
    }
   
    
    /**
     * Get scouting items (brands pending)
     */
public function get_scouting_items($limit =1000) {
    $bd_id = $this->session->userdata('user_id');
    
    // Debug: log session
    log_message('debug', 'get_scouting_items - BD ID from session: ' . $bd_id);
    
    if (!$bd_id) {
        log_message('error', 'get_scouting_items - No BD ID in session');
        return [];
    }
    
    // Query dengan debug
    $this->db->select('*');
    $this->db->from('brands');
    $this->db->where('bd_id', $bd_id);
    $this->db->where('status', 'PENDING');
    $this->db->order_by('created_at', 'DESC');
    $this->db->limit($limit);
    
    $query = $this->db->get();
    
    // Debug: log query dan hasil
    log_message('debug', 'get_scouting_items SQL: ' . $this->db->last_query());
    log_message('debug', 'get_scouting_items Result count: ' . $query->num_rows());
    
    $results = $query->result();
    
    // Jika hasil kosong, coba ambil semua brand untuk debug
    if (empty($results)) {
        $all_brands = $this->db->get('brands')->result();
        log_message('debug', 'Total brands in database: ' . count($all_brands));
        foreach ($all_brands as $b) {
            log_message('debug', 'Brand: ' . $b->name . ', status: ' . $b->status . ', bd_id: ' . $b->bd_id);
        }
    }
    
    return $results;
}
    
    /**
     * Sync brand from product data (from TikTok API)
     */
    public function sync_brand_from_product($product) {
        $shop_name = $product['shop_name'] ?? 'Unknown Brand';
        
        $existing = $this->db->get_where('brands', ['shop_name' => $shop_name])->row();
        
        if ($existing) {
            return $existing->id;
        } else {
            $data = [
                'name' => $shop_name,
                'shop_name' => $shop_name,
                'category' => $product['category'] ?? 'General',
                'status' => 'PENDING',
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->db->insert('brands', $data);
            return $this->db->insert_id();
        }
    }
    
    /**
     * Assign BD to brand
     */
    public function assign_bd_to_brand($brand_id, $bd_id) {
        $this->db->where('id', $brand_id);
        $this->db->update('brands', [
            'bd_id' => $bd_id,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Update all brands GMV from orders
     * Method ini penting untuk sync data
     */
    public function update_all_brands_gmv() {
        // Get all products with their brands and calculate GMV from orders
        $sql = "
            SELECT 
                b.id as brand_id,
                COALESCE(SUM(o.affiliate_gmv), 0) as total_gmv,
                COUNT(DISTINCT o.order_id) as total_orders,
                COUNT(DISTINCT o.creator_username) as total_creators
            FROM brands b
            LEFT JOIN products p ON p.brand_id = b.id
            LEFT JOIN orders o ON o.product_id = p.id
            GROUP BY b.id
        ";
        
        $query = $this->db->query($sql);
        $results = $query->result();
        
        foreach ($results as $row) {
            $roas = ($row->total_gmv > 0) ? round($row->total_gmv / ($row->total_gmv * 0.1), 2) : 0;
            
            $this->db->where('id', $row->brand_id);
            $this->db->update('brands', [
                'total_gmv' => $row->total_gmv,
                'total_orders' => $row->total_orders,
                'total_creators' => $row->total_creators,
                'roas' => $roas,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        return true;
    }
    
  
    
    /**
     * Update brand terms (commission & samples)
     */
    public function update_brand_terms($brand_id, $commission, $samples) {
        $this->db->where('id', $brand_id);
        return $this->db->update('brands', [
            'proposed_commission' => $commission,
            'samples_allocated' => $samples,
            'status' => 'DEAL_CLOSED',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Update brand API status for onboarding
     */
    public function update_onboarding_status($brand_id, $api_status) {
        $this->db->where('id', $brand_id);
        return $this->db->update('brands', [
            'api_status' => $api_status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Scout new brand (create from AI scout)
     */
    public function scout_brand($data) {
        $existing = $this->db->get_where('brands', ['name' => $data['name']])->row();
        
        $insert_data = [
            'name' => $data['name'],
            'shop_name' => $data['name'],
            'category' => $data['category'] ?? null,
            'bd_id' => $data['bd_id'] ?? null,
            'selected_product_id' => $data['selected_product_id'] ?? null,
            'selected_product_name' => $data['selected_product_name'] ?? null,
            'proposed_commission' => $data['proposed_commission'] ?? 8,
            'match_score' => $data['match_score'] ?? 0,
            'whatsapp_number' => $data['whatsapp_number'] ?? null,
            'affiliate_link' => $data['affiliate_link'] ?? null,
            'status' => 'PENDING',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        if ($existing) {
            $this->db->where('id', $existing->id);
            $this->db->update('brands', $insert_data);
            return $existing->id;
        } else {
            $this->db->insert('brands', $insert_data);
            return $this->db->insert_id();
        }
    }
    
    /**
     * Update brand stats
     */
    public function update_brand_stats($brand_id, $gmv, $orders, $creators) {
        $roas = ($gmv > 0) ? round($gmv / ($gmv * 0.1), 2) : 0;
        $this->db->where('id', $brand_id);
        return $this->db->update('brands', [
            'total_gmv' => $gmv,
            'total_orders' => $orders,
            'total_creators' => $creators,
            'roas' => $roas,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    /**
     * Get all brands assigned to BD
     */
public function get_brands_by_bd($bd_id = null, $include_all = false) {
    // Jika include_all = true, ambil semua brand tanpa filter BD
    if ($include_all) {
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('brands')->result();
    }
    
    // Jika bd_id tidak dikirim, gunakan dari session
    if (!$bd_id) {
        $bd_id = $this->session->userdata('user_id');
    }
    
    if (!$bd_id) {
        return [];
    }
    
    $this->db->where('bd_id', $bd_id);
    $this->db->order_by('created_at', 'DESC');
    return $this->db->get('brands')->result();
}

public function update_brand_gmv($brand_id, $gmv) {
        $this->db->where('id', $brand_id);
        return $this->db->update('brands', ['total_gmv' => $gmv, 'updated_at' => date('Y-m-d H:i:s')]);
    }
    
   
    
   
    
    /**
     * Get top brands by GMV
     */
    public function get_top_brands($limit = 5) {
        $this->db->where('total_gmv >', 0);
        $this->db->order_by('total_gmv', 'DESC');
        $this->db->limit($limit);
        return $this->db->get('brands')->result();
    }
    
    
    /**
     * Update brand status
     */
    public function update_brand_status($brand_id, $status) {
        $this->db->where('id', $brand_id);
        return $this->db->update('brands', [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Update brand deal terms
     */
    public function update_deal_terms($brand_id, $commission, $samples = null) {
        $update_data = [
            'proposed_commission' => $commission,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        if ($samples !== null) {
            $update_data['samples_allocated'] = $samples;
        }
        $this->db->where('id', $brand_id);
        return $this->db->update('brands', $update_data);
    }
    
    /**
     * Update brand API status for onboarding
     */
    public function update_api_status($brand_id, $api_status) {
        $this->db->where('id', $brand_id);
        return $this->db->update('brands', [
            'api_status' => $api_status,
            'status' => 'DEAL_CLOSED',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
  
   
    
    /**
     * Get outreach items (brands in negotiation)
     */
    public function get_outreach_items($limit = 3) {
        $this->db->where('status', 'NEGOTIATING');
        $this->db->order_by('updated_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get('brands')->result();
    }
    
    /**
     * Get deal items (brands with counter offers)
     */
    public function get_deal_items($limit = 2) {
        $this->db->where('status', 'COUNTER_OFFER');
        $this->db->order_by('updated_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get('brands')->result();
    }
    
    /**
     * Get onboarding items
     */
    public function get_onboarding_items($limit = 2) {
        $this->db->where('status', 'DEAL_CLOSED');
        $this->db->where('api_status !=', 'VERIFIED');
        $this->db->order_by('updated_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get('brands')->result();
    }
}