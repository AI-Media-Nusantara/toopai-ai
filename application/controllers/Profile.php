<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
        $this->load->model('User_model');
        // Set timezone Indonesia
        date_default_timezone_set('Asia/Jakarta');
    }

    /**
     * Tampilan utama dashboard profil
     */
    public function index() {
        $user_id = $this->session->userdata('user_id');
        $role = $this->session->userdata('role');
        $username = $this->session->userdata('username');

        // Ambil data user saat ini
        $user = $this->db->get_where('users', ['id' => $user_id])->row();

        // Ambil data user management berdasarkan hak akses
        if ($role === 'ADMIN') {
            // Owner / Admin melihat semua user
            $managed_users = $this->db->select('id, username, full_name, email, role, is_active, created_at')
                                      ->get('users')
                                      ->result();
        } else {
            // Admin role tertentu melihat bawahan yang dicreate olehnya
            $managed_users = $this->db->select('id, username, full_name, email, role, is_active, created_at')
                                      ->where('parent_id', $user_id)
                                      ->where('role', $role)
                                      ->get('users')
                                      ->result();
        }

        // Ambil data sample requests (hanya yang berisi produk/pengiriman nyata)
        if ($role === 'ADMIN' || $user_id == 2) {
            $sample_requests = $this->db->select('sr.*, c.username as creator_username, ap.product_name, ap.image_url')
                                         ->from('sample_requests sr')
                                         ->join('creators c', 'sr.creator_id = c.id', 'left')
                                         ->join('affiliate_products ap', 'sr.product_id = ap.product_id', 'left')
                                         ->where('sr.product_id IS NOT NULL')
                                         ->order_by('sr.requested_at', 'DESC')
                                         ->get()
                                         ->result();
        } else {
            $sample_requests = $this->db->select('sr.*, c.username as creator_username, ap.product_name, ap.image_url')
                                         ->from('sample_requests sr')
                                         ->join('creators c', 'sr.creator_id = c.id', 'left')
                                         ->join('affiliate_products ap', 'sr.product_id = ap.product_id', 'left')
                                         ->where('c.is_id', $user_id)
                                         ->where('sr.product_id IS NOT NULL')
                                         ->order_by('sr.requested_at', 'DESC')
                                         ->get()
                                         ->result();
        }

        // Ambil data creator untuk Monitoring Creator
        $is_supervisor = ($user_id == 2 || $role === 'ADMIN');
        $thirty_days_ago = date('Y-m-d', strtotime('-30 days'));
        
        $creators_query = $this->db
            ->select('c.id, c.username, c.full_name, c.avatar_url, c.category, c.status,
                      c.brand_id, c.is_id, b.name as brand_name, u.full_name as is_name,
                      COALESCE(SUM(ao.gmv), 0) as total_gmv_30d,
                      COUNT(DISTINCT ao.order_id) as total_orders_30d')
            ->from('creators c')
            ->join('brands b', 'c.brand_id = b.id', 'left')
            ->join('users u', 'c.is_id = u.id', 'left')
            ->join('affiliate_orders ao', "c.username = ao.creator_username AND ao.order_date_local >= '{$thirty_days_ago}' AND ao.order_status NOT IN ('CANCELLED', 'REFUNDED')", 'left')
            ->where_in('c.status', ['ACTIVE', 'SAMPLE_SENT']);

        if (!$is_supervisor) {
            $creators_query->where('c.is_id', $user_id);
        }

        $creators = $creators_query->group_by(array('c.id', 'b.name', 'u.full_name'))
                          ->order_by('total_gmv_30d', 'DESC')
                          ->limit(200)
                          ->get()
                          ->result();

        foreach ($creators as &$creator) {
            $creator->total_gmv_30d   = floatval($creator->total_gmv_30d);
            $creator->total_orders_30d = intval($creator->total_orders_30d);

            // Jumlah sample yang sudah dikirim
            $creator->sample_count = $this->db
                ->where('creator_id', $creator->id)
                ->where('product_id IS NOT NULL')
                ->count_all_results('sample_requests');

            // Jumlah video
            $creator->video_count = 0;
            $tables = $this->db->list_tables();
            if (in_array('creator_content_statistics', $tables)) {
                $creator->video_count = $this->db
                    ->where('creator_username', $creator->username)
                    ->count_all_results('creator_content_statistics');
            }

            // Apakah ada trigger keranjang kuning (ada transaksi)
            $creator->has_orders = $creator->total_orders_30d > 0;
        }
        unset($creator);

        $data = [
            'title' => 'Dashboard Profile & Management - Toopai',
            'user' => $user,
            'managed_users' => $managed_users,
            'sample_requests' => $sample_requests,
            'creators' => $creators,
            'is_supervisor' => $is_supervisor,
            'role' => $role
        ];

        $this->load->view('templates/new/header', $data);
        $this->load->view('profile/dashboard', $data);
        $this->load->view('templates/new/footer');
    }

    /**
     * AJAX: Simpan pembaruan profil user
     */
    public function save() {
        $this->output->set_content_type('application/json');
        
        $user_id = $this->session->userdata('user_id');
        $full_name = $this->input->post('full_name');
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        if (!$full_name || !$email) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Nama lengkap dan Email harus diisi'
            ]));
        }

        // Cek email duplikat
        $existing = $this->db->where('email', $email)->where('id !=', $user_id)->get('users')->row();
        if ($existing) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Email sudah digunakan oleh akun lain'
            ]));
        }

        $update_data = [
            'full_name' => $full_name,
            'email' => $email
        ];

        if (!empty($password)) {
            if (strlen($password) < 6) {
                return $this->output->set_output(json_encode([
                    'success' => false,
                    'message' => 'Password minimal 6 karakter'
                ]));
            }
            $update_data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $this->db->where('id', $user_id)->update('users', $update_data);

        // Update session
        $this->session->set_userdata('full_name', $full_name);
        $this->session->set_userdata('email', $email);

        return $this->output->set_output(json_encode([
            'success' => true,
            'message' => 'Profil berhasil diperbarui'
        ]));
    }

    /**
     * AJAX: Simpan/Tambah User Bawahan Baru
     */
    public function add_managed_user() {
        $this->output->set_content_type('application/json');

        $username = $this->input->post('username');
        $password = $this->input->post('password');
        $full_name = $this->input->post('full_name');
        $email = $this->input->post('email');
        $role = $this->input->post('role');

        $parent_id = $this->session->userdata('user_id');
        $parent_role = $this->session->userdata('role');

        if (!$username || !$password || !$email || !$role) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Semua kolom wajib diisi'
            ]));
        }

        // Cek hak akses role
        if ($parent_role !== 'ADMIN' && $role !== $parent_role) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Anda hanya bisa menambahkan user dengan role yang sama (' . $parent_role . ')'
            ]));
        }

        // Cek username terdaftar
        $existing_user = $this->db->where('username', $username)->get('users')->row();
        if ($existing_user) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Username sudah digunakan'
            ]));
        }

        // Cek email terdaftar
        $existing_email = $this->db->where('email', $email)->get('users')->row();
        if ($existing_email) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Email sudah terdaftar'
            ]));
        }

        $data = [
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'full_name' => $full_name,
            'email' => $email,
            'role' => $role,
            'parent_id' => $parent_id,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('users', $data);

        return $this->output->set_output(json_encode([
            'success' => true,
            'message' => 'User berhasil ditambahkan'
        ]));
    }

    /**
     * AJAX: Toggle status aktif user
     */
    public function toggle_status() {
        $this->output->set_content_type('application/json');

        $user_id = $this->input->post('user_id');
        $is_active = $this->input->post('is_active');

        $parent_id = $this->session->userdata('user_id');
        $parent_role = $this->session->userdata('role');

        // Ambil user target
        $target = $this->db->get_where('users', ['id' => $user_id])->row();
        if (!$target) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ]));
        }

        // Validasi hak akses
        if ($parent_role !== 'ADMIN' && $target->parent_id != $parent_id) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengubah user ini'
            ]));
        }

        $this->db->where('id', $user_id)->update('users', ['is_active' => $is_active]);

        return $this->output->set_output(json_encode([
            'success' => true,
            'message' => 'Status user berhasil diperbarui'
        ]));
    }
}
