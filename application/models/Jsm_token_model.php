<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jsm_token_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->create_tables_if_not_exist();
    }
    
    private function create_tables_if_not_exist() {
        if (!$this->db->table_exists('tts_tokens')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `tts_tokens` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `shop_id` varchar(255) DEFAULT NULL,
                `access_token` text NOT NULL,
                `refresh_token` text NOT NULL,
                `access_token_expire` bigint(20) NOT NULL,
                `refresh_token_expire` bigint(20) NOT NULL,
                `user_type` tinyint(1) DEFAULT 0 COMMENT '1=Creator,2=Seller,3=Affiliate',
                `scope` text DEFAULT NULL,
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_user_type` (`user_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        }
    }
    
    /**
     * Get latest token for Affiliate Partner (user_type = 3)
     */
    public function get_latest_token() {
        $this->db->select('*');
        $this->db->from('tts_tokens');
        $this->db->where('user_type', 3);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return null;
    }
      public function get_latest_affiliate_token() {
        $this->db->where_in('user_type', [3]); // Affiliate partner
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        return $this->db->get('tts_tokens')->row();
    }
    public function get_latest_token_by_type($user_type) {
        $this->db->where('user_type', $user_type);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        return $this->db->get('tts_tokens')->row();
    }
    
    /**
     * Get latest seller token (user_type = 2)
     */
    public function get_latest_seller_token() {
        $this->db->select('*');
        $this->db->from('tts_tokens');
        $this->db->where('user_type', 2);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return null;
    }
    
    /**
     * Get latest creator token (user_type = 1)
     */
    public function get_latest_creator_token() {
        $this->db->select('*');
        $this->db->from('tts_tokens');
        $this->db->where('user_type', 1);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return null;
    }
    
    /**
     * Get token by user type
     */
    public function get_token_by_user_type($user_type) {
        $this->db->select('*');
        $this->db->from('tts_tokens');
        $this->db->where('user_type', $user_type);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return null;
    }
    
    /**
     * Save token to database
     */
    public function save_token($data) {
        $user_type = $data['user_type'] ?? 0;
        
        $existing = $this->db->where('user_type', $user_type)
                            ->where('tap_type', $data['tap_type'] ?? 'TOOPAI')
                            ->get('tts_tokens')
                            ->row();
        
        if ($existing) {
            $this->db->where('id', $existing->id);
            return $this->db->update('tts_tokens', $data);
        } else {
            return $this->db->insert('tts_tokens', $data);
        }
    }
    
    /**
     * Delete token by user type
     */
    public function delete_token_by_type($user_type) {
        $this->db->where('user_type', $user_type);
        return $this->db->delete('tts_tokens');
    }
    
    /**
     * Clear all tokens
     */
    public function clear_all_tokens() {
        return $this->db->truncate('tts_tokens');
    }
    
    
  


/**
 * Get cipher from database
 */
public function get_cipher() {
    // Coba ambil dari app_config dulu
    if ($this->db->table_exists('app_config')) {
        $row = $this->db->select('cipher')->from('app_config')->where('id', 1)->get()->row();
        if ($row && $row->cipher) {
            return $row->cipher;
        }
    }
    return null;
}


/**
 * Save cipher to database
 */
public function save_cipher($cipher) {
    // Buat tabel app_config jika belum ada
    if (!$this->db->table_exists('app_config')) {
        $this->db->query("CREATE TABLE IF NOT EXISTS `app_config` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `cipher` varchar(255) DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }
    
    $this->db->where('id', 1);
    $exists = $this->db->get('app_config')->row();
    
    if ($exists) {
        $this->db->where('id', 1);
        $this->db->update('app_config', ['cipher' => $cipher, 'updated_at' => date('Y-m-d H:i:s')]);
    } else {
        $this->db->insert('app_config', ['id' => 1, 'cipher' => $cipher, 'created_at' => date('Y-m-d H:i:s')]);
    }
}



}