<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migrasi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        
        // Hanya bisa diakses via CLI atau admin
        if (!$this->input->is_cli_request()) {
            $this->load->library('session');
            if (!$this->session->userdata('logged_in')) {
                redirect('auth/login');
            }
            if (!in_array($this->session->userdata('role'), ['admin', 'IS'])) {
                show_error('Access denied', 403);
            }
        }
        
        $this->load->database();
        $this->load->model('Creator_model');
    }

    // ========== HALAMAN IMPORT ==========
    public function index() {
        $data = [
            'title' => 'Migrasi Data Creator - Toopai',
            'active_menu' => 'migrasi'
        ];
        
        $this->load->view('templates/header', $data);
        $this->load->view('migrasi/import_assigned', $data);
        $this->load->view('templates/footer');
    }

    // ========== PREVIEW CSV ==========
    public function preview_csv() {
        $this->output->set_content_type('application/json');
        
        if (empty($_FILES['csv_file']['name'])) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'File tidak ditemukan'
            ]));
        }
        
        $file = $_FILES['csv_file']['tmp_name'];
        $delimiter = $this->input->post('delimiter') ?: 'tab';
        
        try {
            $rows = $this->parse_csv($file, $delimiter);
            
            if (count($rows) < 2) {
                return $this->output->set_output(json_encode([
                    'success' => false,
                    'message' => 'File tidak memiliki data'
                ]));
            }
            
            $headers = $rows[0];
            $data = [];
            
            for ($i = 1; $i < count($rows); $i++) {
                $row = [];
                foreach ($headers as $idx => $header) {
                    $key = strtolower(trim($header));
                    $row[$key] = trim($rows[$i][$idx] ?? '');
                }
                
                // Skip baris kosong
                if (empty($row['username_creator']) && empty($row['username creator']) && empty($row['username'])) {
                    continue;
                }
                
                $data[] = $row;
            }
            
            return $this->output->set_output(json_encode([
                'success' => true,
                'headers' => $headers,
                'data' => $data,
                'total' => count($data),
                'message' => count($data) . ' creators ditemukan'
            ]));
            
        } catch (Exception $e) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]));
        }
    }

    // ========== PROSES IMPORT ==========
    public function process_import() {
        $this->output->set_content_type('application/json');
        
        $json_data = $this->input->post('data');
        $data = json_decode($json_data, true);
        
        if (empty($data)) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'No data provided'
            ]));
        }
        
        $inserted = 0;
        $updated = 0;
        $errors = [];
        $now = date('Y-m-d H:i:s');
        $user_id = $this->session->userdata('user_id');
        
        // 🔥 AMBIL SEMUA USER IS
        $is_users = $this->db->select('id, username, full_name')
                             ->where('role', 'IS')
                             ->get('users')
                             ->result();
        
        $is_mapping = [];
        foreach ($is_users as $u) {
            $is_mapping[strtolower(trim($u->full_name))] = $u->id;
            $is_mapping[strtolower(trim($u->username))] = $u->id;
        }
        
        // 🔥 MANUAL MAPPING
        $manual_mapping = [
            'Azka Okta Ramadhani' => 'Azka Okta Ramadhani',
            'azka okta ramadhani' => 'Azka Okta Ramadhani',
            'Azka' => 'Azka Okta Ramadhani',
            'azka' => 'Azka Okta Ramadhani',
            
            'dwiaprilia putri' => 'Dwiaprilia Putri',
            'Dwiaprilia Putri' => 'Dwiaprilia Putri',
            'dwiapriiliaputri' => 'Dwiaprilia Putri',
            'Dwiapriiliaputri' => 'Dwiaprilia Putri',
            'dwiaprilia' => 'Dwiaprilia Putri',
            
            'Irna Kirana' => 'Irna Kirana',
            'irna kirana' => 'Irna Kirana',
            'Irna' => 'Irna Kirana',
            'irna' => 'Irna Kirana',
            
            'Muhamad Ihsan Kamil' => 'Muhamad Ihsan Kamil',
            'muhamad ihsan kamil' => 'Muhamad Ihsan Kamil',
            'Muhammad Ihsan Kamil' => 'Muhamad Ihsan Kamil',
            'muhammad ihsan kamil' => 'Muhamad Ihsan Kamil',
            'Muhamad Ihsan' => 'Muhamad Ihsan Kamil',
            'Ihsan Kamil' => 'Muhamad Ihsan Kamil',
            'ihsan' => 'Muhamad Ihsan Kamil',
            
            'Muhammad Aprial' => 'Muhammad Aprial',
            'muhammad aprial' => 'Muhammad Aprial',
            'Aprial' => 'Muhammad Aprial',
            'aprial' => 'Muhammad Aprial',
            
            'Nova Amalia' => 'Nova Amalia',
            'nova amalia' => 'Nova Amalia',
            'Nova Amalia ' => 'Nova Amalia',
            'Nova' => 'Nova Amalia',
            'nova' => 'Nova Amalia',
            
            'Rafliadiputra' => 'Rafliadiputra',
            'rafliadiputra' => 'Rafliadiputra',
            'Rafli' => 'Rafliadiputra',
            'rafli' => 'Rafliadiputra',
        ];
        
        foreach ($data as $row) {
            // 🔥 AMBIL USERNAME CREATOR (beberapa variasi key)
            $username_creator = '';
            foreach (['username_creator', 'username creator', 'username', 'creator'] as $key) {
                if (!empty($row[$key])) {
                    $username_creator = ltrim(trim($row[$key]), '@');
                    break;
                }
            }
            
            // 🔥 AMBIL CREATED BY
            $created_by = '';
            foreach (['created_by', 'created by', 'is', 'assigned to'] as $key) {
                if (!empty($row[$key])) {
                    $created_by = trim($row[$key]);
                    break;
                }
            }
            
            if (empty($username_creator)) {
                $errors[] = "Username creator kosong";
                continue;
            }
            
            if (empty($created_by)) {
                $errors[] = "Created By kosong untuk @{$username_creator}";
                continue;
            }
            
            // 🔥 CARI IS_ID
            $is_id = null;
            $created_by_clean = strtolower(trim($created_by));
            
            // 1. Manual mapping
            $mapped_name = $manual_mapping[$created_by_clean] ?? null;
            if ($mapped_name) {
                $is_id = $is_mapping[strtolower(trim($mapped_name))] ?? null;
            }
            
            // 2. Exact match
            if (!$is_id && isset($is_mapping[$created_by_clean])) {
                $is_id = $is_mapping[$created_by_clean];
            }
            
            // 3. Partial match
            if (!$is_id) {
                foreach ($is_mapping as $name => $id) {
                    if (strpos($created_by_clean, $name) !== false || strpos($name, $created_by_clean) !== false) {
                        $is_id = $id;
                        break;
                    }
                }
            }
            
            // 4. Compact match
            if (!$is_id) {
                $created_by_compact = preg_replace('/[^a-z0-9]/', '', $created_by_clean);
                foreach ($is_mapping as $name => $id) {
                    if (preg_replace('/[^a-z0-9]/', '', $name) === $created_by_compact) {
                        $is_id = $id;
                        break;
                    }
                }
            }
            
            if (!$is_id) {
                $errors[] = "IS tidak ditemukan: '{$created_by}' untuk @{$username_creator}";
                continue;
            }
            $phone = '';
    foreach (['phone', 'nomor_hp', 'no_hp', 'no_wa', 'whatsapp', 'kontak'] as $key) {
        if (!empty($row[$key])) {
            $phone = trim($row[$key]);
            break;
        }
    }
    
    $penerima = '';
    foreach (['penerima', 'nama_penerima', 'recipient', 'nama'] as $key) {
        if (!empty($row[$key]) && $key !== 'nama') {  // Skip 'nama' kalau sudah dipakai full_name
            $penerima = trim($row[$key]);
            break;
        }
    }
    
    $alamat = '';
    foreach (['alamat', 'shipping_address', 'address', 'alamat_pengiriman'] as $key) {
        if (!empty($row[$key])) {
            $alamat = trim($row[$key]);
            break;
        }
    }
            // 🔥 PARSE FOLLOWERS
            $followers_raw = $row['followers'] ?? '0';
            $followers_raw = str_replace(['K', 'k'], '000', $followers_raw);
            $followers_raw = str_replace(['M', 'm'], '000000', $followers_raw);
            $followers_raw = str_replace(['.', ','], '', $followers_raw);
            $followers = intval(preg_replace('/[^0-9]/', '', $followers_raw));
            
            // 🔥 DATA CREATOR
         $creator_data = [
        'username' => $username_creator,
        'full_name' => $row['full_name'] ?? $row['nama'] ?? $username_creator,
        'category' => $row['category'] ?? 'Lifestyle',
        'is_id' => $is_id,
        'status' => 'ACTIVE',
        'source' => 'import_assigned',
        'imported_followers' => $followers,
        'phone' => $phone ?: null,
        'penerima' => $penerima ?: null,
        'alamat' => $alamat ?: null,
        'updated_at' => $now
    ];
            
            // 🔥 CEK EXISTING
            $existing = $this->db->where('username', $username_creator)
                                 ->get('creators')
                                 ->row();
            
            if ($existing) {
                $update_data = [
                    'status' => 'LINK_SENT',
                    'is_id' => $is_id,
                    'updated_at' => $now
                ];
                
                if ($followers > ($existing->imported_followers ?? 0)) {
                    $update_data['imported_followers'] = $followers;
                }
                if (empty($existing->full_name) && !empty($creator_data['full_name'])) {
                    $update_data['full_name'] = $creator_data['full_name'];
                }
                if (empty($existing->category) || $existing->category == 'Lifestyle') {
                    if (!empty($creator_data['category']) && $creator_data['category'] != 'Lifestyle') {
                        $update_data['category'] = $creator_data['category'];
                    }
                }
                
                if (!empty($creator_data['phone'])) {
                    $update_data['phone'] = $creator_data['phone'];
                }
                if (!empty($creator_data['penerima'])) {
                    $update_data['penerima'] = $creator_data['penerima'];
                }
                if (!empty($creator_data['alamat'])) {
                    $update_data['alamat'] = $creator_data['alamat'];
                }
                $this->db->where('id', $existing->id)->update('creators', $update_data);
                $updated++;
            } else {
                $creator_data['created_at'] = $now;
                $this->db->insert('creators', $creator_data);
                $inserted++;
            }
        }
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'inserted' => $inserted,
            'updated' => $updated,
            'errors' => $errors,
            'message' => "Import selesai! {$inserted} baru, {$updated} diupdate ke LINK_SENT" . 
                         (count($errors) > 0 ? ". ⚠️ " . count($errors) . " errors" : "")
        ]));
    }

    // ========== HELPER: Parse CSV ==========
    private function parse_csv($file_path, $delimiter = 'tab') {
        $delimiters = [
            'tab' => "\t",
            'comma' => ',',
            'semicolon' => ';',
            'pipe' => '|'
        ];
        
        $delim = $delimiters[$delimiter] ?? "\t";
        
        // Auto-detect jika perlu
        if ($delim == 'auto') {
            $handle = fopen($file_path, 'r');
            $first_line = fgets($handle);
            fclose($handle);
            
            $tab_count = substr_count($first_line, "\t");
            $comma_count = substr_count($first_line, ',');
            $semicolon_count = substr_count($first_line, ';');
            
            if ($tab_count > $comma_count && $tab_count > $semicolon_count) {
                $delim = "\t";
            } elseif ($semicolon_count > $comma_count) {
                $delim = ';';
            } else {
                $delim = ',';
            }
        }
        
        $rows = [];
        $handle = fopen($file_path, 'r');
        
        while (($row = fgetcsv($handle, 0, $delim)) !== false) {
            // Bersihkan BOM
            if (!empty($row[0])) {
                $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', $row[0]);
                $row[0] = str_replace('﻿', '', $row[0]);
            }
            
            // Skip baris kosong total
            if (empty(array_filter($row, function($v) { return !empty(trim($v)); }))) {
                continue;
            }
            
            $rows[] = array_map('trim', $row);
        }
        
        fclose($handle);
        
        return $rows;
    }

    // ========== CLI: Import dari command line ==========
    public function cli_import($file_path = null) {
        if (!$this->input->is_cli_request()) {
            die('CLI only');
        }
        
        if (!$file_path || !file_exists($file_path)) {
            die("File not found: $file_path\n");
        }
        
        echo "Reading file: $file_path\n";
        $rows = $this->parse_csv($file_path, 'auto');
        echo "Found " . count($rows) . " rows\n";
        
        $headers = $rows[0];
        echo "Headers: " . implode(', ', $headers) . "\n\n";
        
        $data = [];
        for ($i = 1; $i < count($rows); $i++) {
            $row = [];
            foreach ($headers as $idx => $header) {
                $key = strtolower(trim($header));
                $row[$key] = trim($rows[$i][$idx] ?? '');
            }
            $data[] = $row;
        }
        
        // Process import
        $_POST['data'] = json_encode($data);
        $result = $this->process_import();
        $response = json_decode($result, true);
        
        echo "Result:\n";
        echo "  Inserted: {$response['inserted']}\n";
        echo "  Updated: {$response['updated']}\n";
        echo "  Errors: " . count($response['errors']) . "\n";
        
        if (!empty($response['errors'])) {
            echo "\nErrors:\n";
            foreach ($response['errors'] as $e) {
                echo "  - $e\n";
            }
        }
    }
}