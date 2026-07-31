<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_log_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Log user activity
     */
    public function log($user_id, $username, $role, $action, $description = null) {
        $data = [
            'user_id' => $user_id,
            'username' => $username,
            'role' => $role,
            'action' => $action,
            'description' => $description,
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent()
        ];
        
        return $this->db->insert('user_logs', $data);
    }
    
    /**
     * Get logs by user
     */
    public function get_logs_by_user($user_id, $limit = 50) {
        return $this->db->where('user_id', $user_id)
                        ->order_by('created_at', 'DESC')
                        ->limit($limit)
                        ->get('user_logs')
                        ->result();
    }
    
    /**
     * Get all logs (admin only)
     */
    public function get_all_logs($limit = 100, $offset = 0, $filters = []) {
        if (!empty($filters['role'])) {
            $this->db->where('role', $filters['role']);
        }
        if (!empty($filters['action'])) {
            $this->db->where('action', $filters['action']);
        }
        if (!empty($filters['start_date'])) {
            $this->db->where('created_at >=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $this->db->where('created_at <=', $filters['end_date'] . ' 23:59:59');
        }
        
        return $this->db->order_by('created_at', 'DESC')
                        ->limit($limit, $offset)
                        ->get('user_logs')
                        ->result();
    }
    
    /**
     * Count logs
     */
    public function count_logs($filters = []) {
        if (!empty($filters['role'])) {
            $this->db->where('role', $filters['role']);
        }
        if (!empty($filters['action'])) {
            $this->db->where('action', $filters['action']);
        }
        if (!empty($filters['start_date'])) {
            $this->db->where('created_at >=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $this->db->where('created_at <=', $filters['end_date'] . ' 23:59:59');
        }
        
        return $this->db->count_all_results('user_logs');
    }
}