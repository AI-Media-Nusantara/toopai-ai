<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Import_monitoring extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper('file');
        
        // Cek login
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
        
        // Hanya admin/supervisor yang bisa import
        $user_id = $this->session->userdata('user_id');
        if (!in_array($user_id, [1, 2])) {
            show_error('Access denied. Only admin can import.', 403);
        }
    }
    
    public function index() {
        $data['title'] = 'Import Creator to Monitoring (Task 3)';
        $data['brands'] = $this->db->select('id, name, shop_name')->get('brands')->result();
        
        $this->load->view('templates/header', $data);
        $this->load->view('import_monitoring', $data);
        $this->load->view('templates/footer');
    }
    
    public function process() {
        $this->output->set_content_type('application/json');
        
        // Cek file upload
        if (empty($_FILES['csv_file']['name'])) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'File CSV tidak ditemukan'
            ]));
        }
        
        $file = $_FILES['csv_file']['tmp_name'];
        $filename = $_FILES['csv_file']['name'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if ($extension !== 'csv') {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Format file harus CSV'
            ]));
        }
        
        // 🔥 BACA FILE DENGAN DELIMITER ;
        $handle = fopen($file, 'r');
        
        // 🔥 HAPUS BOM (Byte Order Mark) di awal file
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            // Jika bukan BOM, reset pointer ke awal
            rewind($handle);
        }
        
        // 🔥 BACA HEADER DENGAN DELIMITER ;
        $headers = fgetcsv($handle, 0, ';');
        
        // 🔥 DEBUG - lihat header yang terbaca
        error_log('Headers: ' . print_r($headers, true));
        
        // 🔥 MAPPING KOLOM - SESUAIKAN DENGAN HEADER
        $col_map = [
            'date' => array_search('Date', $headers),
            'username' => array_search('Username', $headers),
            'whatsapp' => array_search('Whatsapp', $headers),
            'id_is' => array_search('id_is', $headers),
            'is' => array_search('is', $headers),
            'brand' => array_search('Brand', $headers)
        ];
        
        // 🔥 DEBUG - lihat mapping
        error_log('Column mapping: ' . print_r($col_map, true));
        
        // 🔥 CEK APAKAH KOLOM ADA - JIKA TIDAK, COBA CARI ALTERNATIF
        foreach ($col_map as $key => $index) {
            if ($index === false) {
                // Coba cari dengan case insensitive
                foreach ($headers as $i => $h) {
                    if (strtolower(trim($h)) == strtolower($key)) {
                        $col_map[$key] = $i;
                        break;
                    }
                }
                // Jika masih tidak ditemukan
                if ($col_map[$key] === false) {
                    fclose($handle);
                    return $this->output->set_output(json_encode([
                        'success' => false,
                        'message' => "Kolom '$key' tidak ditemukan di file CSV. Header yang ditemukan: " . implode(', ', $headers)
                    ]));
                }
            }
        }
        
        $inserted = 0;
        $skipped = 0;
        $errors = 0;
        $error_rows = [];
        $row_num = 0;
        
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $row_num++;
            
            // 🔥 SKIP ROW KOSONG
            if (empty($row[$col_map['username']]) || trim($row[$col_map['username']]) == '') {
                continue;
            }
            
            $username = trim($row[$col_map['username']]);
            // Hapus @ di depan
            $username = ltrim($username, '@');
            $username = trim($username);
            
            $phone = trim($row[$col_map['whatsapp']] ?? '');
            $brand_name = trim($row[$col_map['brand']] ?? '');
            $is_id = trim($row[$col_map['id_is']] ?? '');
            $is_name = trim($row[$col_map['is']] ?? '');
            $date = trim($row[$col_map['date']] ?? '');
            
            // 🔥 SKIP JIKA USERNAME KOSONG
            if (empty($username)) {
                $errors++;
                $error_rows[] = "Row {$row_num}: Username kosong";
                continue;
            }
            
            // Format phone
            $phone = $this->_format_phone($phone);
            
            // Cari brand_id
            $brand_id = null;
            if (!empty($brand_name)) {
                $brand = $this->db->select('id, name, shop_name')
                    ->group_start()
                        ->where('name', $brand_name)
                        ->or_where('shop_name', $brand_name)
                        ->or_like('name', $brand_name, 'both')
                        ->or_like('shop_name', $brand_name, 'both')
                    ->group_end()
                    ->limit(1)
                    ->get('brands')
                    ->row();
                
                if ($brand) {
                    $brand_id = $brand->id;
                }
            }
            
            // 🔥 CEK APAKAH CREATOR SUDAH ADA
            $existing = $this->db->where('username', $username)
                                 ->where('brand_id', $brand_id)
                                 ->get('creators')
                                 ->row();
            
            if ($existing) {
                // Skip jika sudah ada CA owner lain untuk brand yang sama
                if (!empty($existing->is_id) && $existing->is_id != $is_id) {
                    $errors++;
                    $error_rows[] = "Row {$row_num}: Skip @{$username} untuk brand '{$brand_name}': sudah dikelola oleh CA dengan ID {$existing->is_id}";
                    continue;
                }

                // UPDATE menjadi ACTIVE (Task 3)
                $update_data = [
                    'status' => 'ACTIVE',
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                if (!empty($phone)) {
                    $update_data['phone'] = $phone;
                }
                if (!empty($is_id)) {
                    $update_data['is_id'] = $is_id;
                }
                
                $this->db->where('id', $existing->id)->update('creators', $update_data);
                $inserted++;
            } else {
                // INSERT langsung ACTIVE (Task 3)
                $insert_data = [
                    'username' => $username,
                    'full_name' => $username,
                    'phone' => $phone,
                    'brand_id' => $brand_id,
                    'is_id' => $is_id ?: 1,
                    'status' => 'ACTIVE',
                    'source' => 'imported',
                    'approved_at' => date('Y-m-d H:i:s'),
                    'approved_by' => $this->session->userdata('user_id'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                // Tambahkan shop_name jika ada brand
                if ($brand_id) {
                    $brand = $this->db->select('shop_name')->where('id', $brand_id)->get('brands')->row();
                    if ($brand && $brand->shop_name) {
                        $insert_data['shop_name'] = $brand->shop_name;
                    }
                }
                
                // Filter null values
                $insert_data = array_filter($insert_data, function($value) {
                    return $value !== null && $value !== '';
                });
                
                $this->db->insert('creators', $insert_data);
                $inserted++;
            }
        }
        
        fclose($handle);
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'inserted' => $inserted,
            'skipped' => $skipped,
            'errors' => $errors,
            'error_rows' => $error_rows,
            'message' => "Import selesai! $inserted creator berhasil di-import ke Monitoring (Task 3)."
        ]));
    }
    
    private function _format_phone($phone) {
        if (empty($phone)) return null;
        
        // Hapus spasi, strip, titik
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Jika ada "dm tiktok" atau text, skip
        if (preg_match('/[a-zA-Z]/', $phone)) {
            return null;
        }
        
        // Format untuk Indonesia
        if (preg_match('/^0/', $phone)) {
            $phone = '62' . substr($phone, 1);
        } elseif (preg_match('/^\+/', $phone)) {
            $phone = substr($phone, 1);
        } elseif (!preg_match('/^62/', $phone) && strlen($phone) > 0) {
            $phone = '62' . $phone;
        }
        
        return $phone;
    }
}