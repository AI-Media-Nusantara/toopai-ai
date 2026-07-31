<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Task_progress_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->create_tables_if_not_exist();
    }
    
    private function create_tables_if_not_exist() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `bd_task_progress` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `bd_id` int(11) NOT NULL,
            `campaign_id` varchar(100) DEFAULT NULL,
            `brand_id` int(11) DEFAULT NULL,
            `stage` int(11) NOT NULL,
            `status` enum('PENDING','IN_PROGRESS','COMPLETED','SKIPPED') DEFAULT 'PENDING',
            `action_type` varchar(50) DEFAULT NULL,
            `action_data` text,
            `completed_at` datetime DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_bd_id` (`bd_id`),
            KEY `idx_stage` (`stage`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        
        $this->db->query("CREATE TABLE IF NOT EXISTS `activity_logs` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `user_role` varchar(10) DEFAULT NULL,
            `action` varchar(100) NOT NULL,
            `target_type` varchar(50) DEFAULT NULL,
            `target_id` varchar(100) DEFAULT NULL,
            `details` text,
            `ip_address` varchar(45) DEFAULT NULL,
            `user_agent` text,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        
        $this->db->query("CREATE TABLE IF NOT EXISTS `whatsapp_logs` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `brand_id` int(11) DEFAULT NULL,
            `creator_id` int(11) DEFAULT NULL,
            `phone_number` varchar(50) NOT NULL,
            `country_code` varchar(10) DEFAULT NULL,
            `message` text NOT NULL,
            `status` enum('PENDING','SENT','DELIVERED','FAILED') DEFAULT 'SENT',
            `sent_at` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        
        $this->db->query("CREATE TABLE IF NOT EXISTS `ai_scout_results` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `brand_name` varchar(255) NOT NULL,
            `category` varchar(100) DEFAULT NULL,
            `shop_link` text,
            `matched_product_id` varchar(100) DEFAULT NULL,
            `matched_product_name` varchar(500) DEFAULT NULL,
            `matched_product_price` decimal(15,2) DEFAULT 0,
            `matched_commission` decimal(5,2) DEFAULT 0,
            `status` enum('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING',
            `created_by` int(11) DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }
    
    public function update_task_progress($bd_id, $stage, $status, $action_data = null) {
        $existing = $this->db->get_where('bd_task_progress', [
            'bd_id' => $bd_id,
            'stage' => $stage
        ])->row();
        
        $data = [
            'bd_id' => $bd_id,
            'stage' => $stage,
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($action_data) {
            $data['action_data'] = is_array($action_data) ? json_encode($action_data) : $action_data;
        }
        
        if ($status == 'COMPLETED') {
            $data['completed_at'] = date('Y-m-d H:i:s');
        }
        
        if ($existing) {
            $this->db->where('id', $existing->id);
            return $this->db->update('bd_task_progress', $data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            return $this->db->insert('bd_task_progress', $data);
        }
    }
    
    public function get_task_progress($bd_id) {
        $this->db->order_by('stage', 'ASC');
        return $this->db->get_where('bd_task_progress', ['bd_id' => $bd_id])->result();
    }
    
    public function is_stage_completed($bd_id, $stage) {
        $this->db->where('bd_id', $bd_id);
        $this->db->where('stage', $stage);
        $this->db->where('status', 'COMPLETED');
        return $this->db->get('bd_task_progress')->num_rows() > 0;
    }
    
   public function log_activity($user_id, $user_role, $action, $target_type, $target_id, $details = null) {
    // Simpan tanpa user_role (atau simpan di kolom details)
    $data = [
        'user_id' => $user_id,
        'action' => $action,
        'target_type' => $target_type,
        'target_id' => $target_id,
        'details' => is_array($details) ? json_encode($details) : $details,
        'ip_address' => $this->input->ip_address(),
        'user_agent' => $this->input->user_agent(),
        'created_at' => date('Y-m-d H:i:s')
    ];
    return $this->db->insert('activity_logs', $data);
}
    
    public function log_whatsapp($user_id, $brand_id, $phone_number, $message, $status = 'SENT') {
        // Detect country code
        $country_code = $this->detect_country_code($phone_number);
        
        $data = [
            'user_id' => $user_id,
            'brand_id' => $brand_id,
            'phone_number' => $phone_number,
            'country_code' => $country_code,
            'message' => $message,
            'status' => $status,
            'sent_at' => date('Y-m-d H:i:s')
        ];
        return $this->db->insert('whatsapp_logs', $data);
    }
    
    private function detect_country_code($phone) {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        if (preg_match('/^\+62|^62|^0/', $phone)) {
            return 'ID';
        } elseif (preg_match('/^\+1|^1/', $phone)) {
            return 'US';
        } elseif (preg_match('/^\+44|^44/', $phone)) {
            return 'UK';
        } elseif (preg_match('/^\+65|^65/', $phone)) {
            return 'SG';
        } elseif (preg_match('/^\+60|^60/', $phone)) {
            return 'MY';
        } else {
            return 'INTL';
        }
    }
    
    public function save_ai_scout_result($data) {
        return $this->db->insert('ai_scout_results', $data);
    }
}