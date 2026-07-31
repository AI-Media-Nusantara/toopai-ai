<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->create_tables_if_not_exist();
    }
    
    private function create_tables_if_not_exist() {
        if (!$this->db->table_exists('users')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `users` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `username` varchar(100) NOT NULL,
                `email` varchar(255) NOT NULL,
                `password` varchar(255) NOT NULL,
                `role` enum('BD','IS','ADMIN') NOT NULL DEFAULT 'IS',
                `full_name` varchar(255) DEFAULT NULL,
                `avatar` varchar(255) DEFAULT NULL,
                `is_active` tinyint(1) DEFAULT 1,
                `last_login` datetime DEFAULT NULL,
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
            
            // Insert default users
            $default_users = [
                [
                    'username' => 'budi_bd',
                    'email' => 'bd@toopai.com',
                    'password' => password_hash('password123', PASSWORD_DEFAULT),
                    'role' => 'BD',
                    'full_name' => 'Budi Santoso (BD)',
                    'is_active' => 1
                ],
                [
                    'username' => 'sari_is',
                    'email' => 'is@toopai.com',
                    'password' => password_hash('password123', PASSWORD_DEFAULT),
                    'role' => 'IS',
                    'full_name' => 'Sari Wijaya (IS)',
                    'is_active' => 1
                ],
                [
                    'username' => 'admin',
                    'email' => 'admin@toopai.com',
                    'password' => password_hash('password123', PASSWORD_DEFAULT),
                    'role' => 'ADMIN',
                    'full_name' => 'Administrator',
                    'is_active' => 1
                ]
            ];
            
            foreach ($default_users as $user) {
                $this->db->insert('users', $user);
            }
        }
    }
    
    /**
     * Login user
     */
    public function login($email, $password) {
        $this->db->where('email', $email);
        $this->db->where('is_active', 1);
        $query = $this->db->get('users');
        
        if ($query->num_rows() == 1) {
            $user = $query->row();
            if (password_verify($password, $user->password)) {
                $this->db->where('id', $user->id);
                $this->db->update('users', ['last_login' => date('Y-m-d H:i:s')]);
                return $user;
            }
        }
        return false;
    }
    
    /**
     * Get user by ID
     */
    public function get_user_by_id($id) {
        return $this->db->get_where('users', ['id' => $id])->row();
    }
    
    /**
     * Get user by email
     */
    public function get_user_by_email($email) {
        return $this->db->get_where('users', ['email' => $email])->row();
    }
    
    /**
     * Get all BD users
     */
    public function get_all_bd() {
        return $this->db->get_where('users', ['role' => 'BD'])->result();
    }
    
    /**
     * Get all IS users
     */
    public function get_all_is() {
        return $this->db->get_where('users', ['role' => 'IS'])->result();
    }
    
    /**
     * Get all users
     */
    public function get_all_users() {
        return $this->db->get('users')->result();
    }
    
    /**
     * Create user
     */
    public function create_user($data) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert('users', $data);
    }
    
    /**
     * Update user
     */
    public function update_user($id, $data) {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        $this->db->where('id', $id);
        return $this->db->update('users', $data);
    }
}