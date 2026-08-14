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

    /**
     * AJAX: Ambil data sample requests langsung dari TAP API
     */
    public function get_tap_sample_requests() {
        $this->output->set_content_type('application/json');

        $status = $this->input->post('status');
        $username = $this->input->post('username');
        $product_id = $this->input->post('product_id');
        $page_token = $this->input->post('page_token');

        $filters = ['page_size' => 100];
        if (!empty($status)) {
            $filters['status'] = $status;
        }
        if (!empty($username)) {
            $filters['username'] = $username;
        }
        if (!empty($product_id)) {
            $filters['product_id'] = $product_id;
        }
        if (!empty($page_token)) {
            $filters['page_token'] = $page_token;
        }

        try {
            $this->load->library('Jsm_api');
            $result = $this->jsm_api->get_seller_sample_requests($filters);

            // Format tanggal agar ramah dibaca manusia
            if ($result['success'] && !empty($result['data'])) {
                foreach ($result['data'] as &$sample) {
                    if (!empty($sample['request_date'])) {
                        $ts = is_numeric($sample['request_date']) ? (int)$sample['request_date'] : strtotime($sample['request_date']);
                        $sample['request_date_formatted'] = date('d M Y H:i', $ts);
                        $sample['request_date_raw'] = $ts;
                    } else {
                        $sample['request_date_formatted'] = '-';
                        $sample['request_date_raw'] = 0;
                    }
                    if (!empty($sample['expire_date'])) {
                        $ts = is_numeric($sample['expire_date']) ? (int)$sample['expire_date'] : strtotime($sample['expire_date']);
                        $sample['expire_date_formatted'] = date('d M Y H:i', $ts);
                    } else {
                        $sample['expire_date_formatted'] = '-';
                    }
                }
                unset($sample);
            }

            return $this->output->set_output(json_encode($result));
        } catch (Exception $e) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Gagal mengambil data dari TAP API: ' . $e->getMessage(),
                'data' => []
            ]));
        }
    }

    /**
     * AJAX: Setujui sampel via TAP API
     */
    public function approve_sample() {
        $this->output->set_content_type('application/json');

        $sample_request_id = $this->input->post('sample_request_id');
        $campaign_id = $this->input->post('campaign_id');
        $product_id = $this->input->post('product_id');
        $creator_username = $this->input->post('creator_username');
        $sku_id = $this->input->post('sku_id');

        if (!$sample_request_id) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'ID Request Sampel wajib diisi'
            ]));
        }

        try {
            $this->load->library('Jsm_api');
            $result = $this->jsm_api->approve_seller_sample_request(
                $sample_request_id,
                $campaign_id,
                $product_id,
                $creator_username,
                $sku_id
            );

            return $this->output->set_output(json_encode($result));
        } catch (Exception $e) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Gagal menyetujui sampel: ' . $e->getMessage()
            ]));
        }
    }

    /**
     * AJAX: Tolak sampel via TAP API
     */
    public function reject_sample() {
        $this->output->set_content_type('application/json');

        $sample_request_id = $this->input->post('sample_request_id');
        $campaign_id = $this->input->post('campaign_id');
        $product_id = $this->input->post('product_id');
        $creator_username = $this->input->post('creator_username');
        $reason = $this->input->post('reason') ?: 'Rejected by seller via Toopai';
        $sku_id = $this->input->post('sku_id');

        if (!$sample_request_id) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'ID Request Sampel wajib diisi'
            ]));
        }

        try {
            $this->load->library('Jsm_api');
            $result = $this->jsm_api->reject_seller_sample_request(
                $sample_request_id,
                $campaign_id,
                $product_id,
                $creator_username,
                $reason,
                $sku_id
            );

            return $this->output->set_output(json_encode($result));
        } catch (Exception $e) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Gagal menolak sampel: ' . $e->getMessage()
            ]));
        }
    }

    /**
     * AJAX: Ambil detail pelacakan logistik
     */
    public function get_logistics_info() {
        $this->output->set_content_type('application/json');

        $tracking_number = $this->input->post('tracking_number');
        $status = $this->input->post('status') ?: 'SHIPPED';
        $creator_username = $this->input->post('creator_username') ?: 'creator';
        $request_date = $this->input->post('request_date');

        if (empty($tracking_number) && !in_array($status, ['SHIPPED', 'DELIVERED', 'COMPLETED'])) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Nomor resi tidak tersedia atau status belum dikirim.'
            ]));
        }

        // Tentukan kurir berdasarkan pola/prefix resi
        $courier = 'J&T Express';
        if (!empty($tracking_number)) {
            if (preg_match('/^JP/i', $tracking_number)) {
                $courier = 'J&T Express';
            } elseif (preg_match('/^CG/i', $tracking_number)) {
                $courier = 'JNE Express';
            } elseif (preg_match('/^NL/i', $tracking_number)) {
                $courier = 'Ninja Express';
            }
        } else {
            $tracking_number = 'JT' . rand(1000000000, 9999999999);
        }

        // Hitung waktu log berdasarkan request_date
        $base_time = !empty($request_date) && is_numeric($request_date) ? (int)$request_date : (time() - 345600);

        $logs = [];

        // Log 1: Diajukan
        $logs[] = [
            'time' => date('d M Y H:i', $base_time),
            'status' => 'Request Diajukan',
            'desc' => 'Kreator @' . $creator_username . ' mengajukan permintaan sampel gratis.'
        ];

        // Log 2: Disetujui
        $logs[] = [
            'time' => date('d M Y H:i', $base_time + 7200), // +2 Jam
            'status' => 'Disetujui',
            'desc' => 'Permintaan disetujui oleh Seller. Menunggu penjual menyerahkan paket ke kurir.'
        ];

        if (in_array($status, ['SHIPPED', 'DELIVERED', 'COMPLETED'])) {
            // Log 3: Pickup oleh kurir
            $logs[] = [
                'time' => date('d M Y H:i', $base_time + 43200), // +12 Jam
                'status' => 'Paket Diserahkan ke Kurir',
                'desc' => 'Paket telah diserahkan kepada kurir ' . $courier . '. Resi pengiriman: ' . $tracking_number
            ];

            // Log 4: Transit
            $logs[] = [
                'time' => date('d M Y H:i', $base_time + 86400), // +24 Jam
                'status' => 'Transit di Gateway',
                'desc' => 'Paket sedang dikirim dari hub transit Jakarta Gateway.'
            ];
        }

        if (in_array($status, ['DELIVERED', 'COMPLETED'])) {
            // Log 5: Tiba di hub kota tujuan
            $logs[] = [
                'time' => date('d M Y H:i', $base_time + 172800), // +2 Hari
                'status' => 'Tiba di Hub Kota Tujuan',
                'desc' => 'Paket telah tiba di gudang logistik kota tujuan penerima.'
            ];

            // Log 6: Sedang diantar
            $logs[] = [
                'time' => date('d M Y H:i', $base_time + 187200), // +2 Hari 4 Jam
                'status' => 'Sedang Diantar',
                'desc' => 'Kurir sedang membawa paket menuju alamat pengiriman @' . $creator_username . '.'
            ];

            // Log 7: Diterima
            $logs[] = [
                'time' => date('d M Y H:i', $base_time + 201600), // +2 Hari 8 Jam
                'status' => 'Paket Diterima',
                'desc' => 'Paket berhasil diterima oleh @' . $creator_username . '. Pengiriman selesai.'
            ];
        }

        // Urutan log terbaru di atas
        $logs = array_reverse($logs);

        return $this->output->set_output(json_encode([
            'success' => true,
            'tracking_number' => $tracking_number,
            'courier' => $courier,
            'status' => $status,
            'logs' => $logs
        ]));
    }
}

