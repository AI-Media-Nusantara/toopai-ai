<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        
       
        $this->load->model('User_model');
        $this->load->library('form_validation');
    }
    
    public function login() {
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
        }
        
        $data['title'] = 'Login - Toopai Affiliate Platform';
        
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required');
        
        if ($this->form_validation->run() == FALSE) {
            $this->load->view('auth/login', $data);
        } else {
            $email = $this->input->post('email');
            $password = $this->input->post('password');
            
            $user = $this->User_model->login($email, $password);
            
            if ($user) {
                $session_data = [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                    'full_name' => $user->full_name,
                    'logged_in' => TRUE
                ];
                $this->session->set_userdata($session_data);
                redirect('dashboard');
            } else {
                $this->session->set_flashdata('error', 'Invalid email or password');
                redirect('auth/login');
            }
        }
    }
    // ========== CHANGE PASSWORD ==========
public function change_password() {
    $this->output->set_content_type('application/json');
    
    $current_password = $this->input->post('current_password');
    $new_password = $this->input->post('new_password');
    $confirm_password = $this->input->post('confirm_password');
    
    if (!$current_password || !$new_password || !$confirm_password) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'All fields are required'
        ]));
    }
    
    if ($new_password !== $confirm_password) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'New password and confirm password do not match'
        ]));
    }
    
    if (strlen($new_password) < 6) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Password must be at least 6 characters'
        ]));
    }
    
    $user_id = $this->session->userdata('user_id');
    $username = $this->session->userdata('username');
    $role = $this->session->userdata('role');
    
    $user = $this->db->where('id', $user_id)->get('users')->row();
    
    if (!$user || !password_verify($current_password, $user->password)) {
        $this->load->model('User_log_model');
        $this->User_log_model->log($user_id, $username, $role, 'CHANGE_PASSWORD_FAILED', 'Failed to change password - wrong current password');
        
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Current password is incorrect'
        ]));
    }
    
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    $this->db->where('id', $user_id)->update('users', [
        'password' => $hashed_password,
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    $this->load->model('User_log_model');
    $this->User_log_model->log($user_id, $username, $role, 'CHANGE_PASSWORD', 'Password changed successfully');
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'message' => 'Password changed successfully'
    ]));
}

// ========== ADD USER (BD/IS can add team members) ==========
public function add_user() {
    $this->output->set_content_type('application/json');
    
    $username = $this->input->post('username');
    $password = $this->input->post('password');
    $full_name = $this->input->post('full_name');
    $email = $this->input->post('email');
    $role = $this->input->post('role');
    $parent_id = $this->session->userdata('user_id');
    $parent_role = $this->session->userdata('role');
    
    // Validasi role yang bisa ditambahkan
    if ($parent_role == 'BD' && $role != 'BD') {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'BD can only add BD members'
        ]));
    }
    
    if ($parent_role == 'IS' && $role != 'IS') {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'IS can only add IS members'
        ]));
    }
    
    if (!$username || !$password || !$role) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Username, password, and role are required'
        ]));
    }
    
    // Cek username sudah ada
    $existing = $this->db->where('username', $username)->get('users')->row();
    if ($existing) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Username already exists'
        ]));
    }
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $data = [
        'username' => $username,
        'password' => $hashed_password,
        'full_name' => $full_name,
        'email' => $email,
        'role' => $role,
        'parent_id' => $parent_id,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $this->db->insert('users', $data);
    $new_user_id = $this->db->insert_id();
    
    $this->load->model('User_log_model');
    $this->User_log_model->log(
        $parent_id, 
        $this->session->userdata('username'), 
        $parent_role, 
        'ADD_USER', 
        "Added new user: $username ($role)"
    );
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'message' => "User $username added successfully",
        'user_id' => $new_user_id
    ]));
}

// ========== GET TEAM MEMBERS ==========
public function get_team_members() {
    $this->output->set_content_type('application/json');
    
    $parent_id = $this->session->userdata('user_id');
    $role = $this->session->userdata('role');
    
    $users = $this->db->select('id, username, full_name, email, role, created_at')
                      ->where('parent_id', $parent_id)
                      ->order_by('created_at', 'DESC')
                      ->get('users')
                      ->result();
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'users' => $users,
        'my_role' => $role
    ]));
}
// ========== GET USER LOGS ==========
public function get_user_logs() {
    $this->output->set_content_type('application/json');
    
    $user_id = $this->session->userdata('user_id');
    $role = $this->session->userdata('role');
    
    $this->load->model('User_log_model');
    
    if ($role == 'admin') {
        $logs = $this->User_log_model->get_all_logs(100);
    } else {
        $logs = $this->User_log_model->get_logs_by_user($user_id, 100);
    }
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'logs' => $logs
    ]));
}
    public function logout() {
        $this->session->sess_destroy();
        redirect('auth/login');
    }
}