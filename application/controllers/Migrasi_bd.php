<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migrasi_bd extends CI_Controller {

    private $temp_data_path = './uploads/temp/migrasi_data.json';

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper('file');
        $this->load->library('upload');
        
        // if (!$this->session->userdata('logged_in')) {
        //     redirect('auth/login');
        // }
    }

    public function index() {
        $data['title'] = 'Import Data BD - Toopai';
        $data['active_menu'] = 'migrasi';
        $data['bd_list'] = $this->get_bd_list();
        
        $this->load->view('templates/header', $data);
        $this->load->view('migrasi/import_bd', $data);
        $this->load->view('templates/footer');
    }

    public function import_csv() {
        $this->output->set_content_type('application/json');
        
        if (empty($_FILES['csv_file']['name'])) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'File CSV tidak ditemukan'
            ]));
        }
        
        $config['upload_path'] = './uploads/temp/';
        $config['allowed_types'] = 'csv';
        $config['max_size'] = 10240;
        
        if (!is_dir('./uploads/temp/')) {
            mkdir('./uploads/temp/', 0777, true);
        }
        
        $this->upload->initialize($config);
        
        if (!$this->upload->do_upload('csv_file')) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => $this->upload->display_errors('', '')
            ]));
        }
        
        $upload_data = $this->upload->data();
        $file_path = $upload_data['full_path'];
        
        $csv_content = file_get_contents($file_path);
        $csv_content = preg_replace('/^\xEF\xBB\xBF/', '', $csv_content);
        
        $lines = explode("\n", $csv_content);
        $headers = str_getcsv(array_shift($lines), ';');
        
        $header_map = $this->map_headers($headers);
        $bd_map = $this->get_bd_map();
        
        $all_raw_data = [];
        $preview_data = [];
        $preview_limit = 10;
        
        foreach ($lines as $line_num => $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $row = str_getcsv($line, ';');
            if (count($row) < 3) continue;
            
            $raw_handler = trim($row[$header_map['handler']] ?? '');
            $brand_name = trim($row[$header_map['brand_name']] ?? '');
            $unit_sold = trim($row[$header_map['unit_sold']] ?? '');
            $contact = trim($row[$header_map['contact']] ?? '');
            $status = trim($row[$header_map['status']] ?? '');
            
            if (preg_match('/^(hndler|handler|nama brand|unit sold|contact|status)$/i', $brand_name)) {
                continue;
            }
            
            if (empty($brand_name)) continue;
            
            $handler = $this->extract_handler_name($raw_handler);
            
            $all_raw_data[] = [
                'raw_handler' => $raw_handler,
                'handler' => $handler,
                'brand_name' => $brand_name,
                'unit_sold' => $unit_sold,
                'contact' => $contact,
                'status' => $status
            ];
            
            if (count($preview_data) < $preview_limit) {
                $bd_info = $this->find_bd_id($handler, $bd_map);
                $preview_data[] = [
                    'handler' => $handler,
                    'brand_name' => $brand_name,
                    'unit_sold' => $unit_sold,
                    'contact' => $contact,
                    'status' => $status,
                    'bd_id' => $bd_info['id'],
                    'bd_name' => $bd_info['name'],
                    'is_online' => stripos($status, 'ONLINE') !== false || stripos($status, 'ONBOARD') !== false
                ];
            }
        }
        
        // Simpan ke file JSON (bukan session)
        file_put_contents($this->temp_data_path, json_encode($all_raw_data));
        
        // Hitung statistik
        $total_data = count($all_raw_data);
        $active_count = 0;
        $pending_count = 0;
        $no_bd_count = 0;
        
        foreach ($all_raw_data as $item) {
            $handler = $item['handler'];
            $status = $item['status'];
            $bd_info = $this->find_bd_id($handler, $bd_map);
            
            if (stripos($status, 'ONLINE') !== false || stripos($status, 'ONBOARD') !== false) {
                $active_count++;
            } else {
                $pending_count++;
            }
            
            if (!$bd_info['id']) {
                $no_bd_count++;
            }
        }
        
        unlink($file_path);
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'data' => $preview_data,
            'total' => $total_data,
            'stats' => [
                'total' => $total_data,
                'active' => $active_count,
                'pending' => $pending_count,
                'no_bd' => $no_bd_count
            ]
        ]));
    }

    public function process_import() {
        $this->output->set_content_type('application/json');
        
        set_time_limit(300);
        
        // Baca dari file JSON
        if (!file_exists($this->temp_data_path)) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Tidak ada data untuk diimport. Upload ulang file CSV.'
            ]));
        }
        
        $all_raw_data = json_decode(file_get_contents($this->temp_data_path), true);
        
        if (empty($all_raw_data)) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Data kosong'
            ]));
        }
        
        $bd_map = $this->get_bd_map();
        $results = [
            'success_count' => 0,
            'skip_count' => 0,
            'fail_count' => 0,
            'active_count' => 0,
            'pending_count' => 0,
            'duplicate_count' => 0,
            'errors' => []
        ];
        
        $batch_size = 50;
        $total_items = count($all_raw_data);
        
        for ($i = 0; $i < $total_items; $i += $batch_size) {
            $batch = array_slice($all_raw_data, $i, $batch_size);
            
            foreach ($batch as $item) {
                $handler = $item['handler'];
                $brand_name = $item['brand_name'];
                $unit_sold = $item['unit_sold'];
                $contact = $item['contact'];
                $status = $item['status'];
                
                $bd_info = $this->find_bd_id($handler, $bd_map);
                
                if (!$bd_info['id']) {
                    $results['skip_count']++;
                    continue;
                }
                
                $existing = $this->db->where('shop_name', $brand_name)
                    ->where('bd_id', $bd_info['id'])
                    ->get('brands')
                    ->row();
                
                if ($existing) {
                    $results['skip_count']++;
                    continue;
                }
                
                $whatsapp = $this->format_whatsapp($contact);
                $is_online = (stripos($status, 'ONLINE') !== false || stripos($status, 'ONBOARD') !== false);
                $status_db = $is_online ? 'ACTIVE' : 'PENDING';
                $current_task = $is_online ? 4 : 1;
                
                $existing_other = $this->db->where('shop_name', $brand_name)
                    ->where('bd_id !=', $bd_info['id'])
                    ->get('brands')
                    ->row();
                
                $insert_data = [
                    'name' => $brand_name,
                    'shop_name' => $brand_name,
                    'category' => $this->guess_category($brand_name),
                    'status' => $status_db,
                    'current_task' => $current_task,
                    'bd_id' => $bd_info['id'],
                    'whatsapp_number' => $whatsapp,
                    'total_orders' => $this->parse_unit_sold($unit_sold),
                    'proposed_commission' => 10,
                    'source' => 'migrasi',
                    'input_by' => $handler,
                    'input_by_name' => $handler,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                if ($existing_other) {
                    $insert_data['is_duplicate'] = 1;
                    $insert_data['duplicate_of'] = $existing_other->id;
                    $results['duplicate_count']++;
                }
                
                $this->db->insert('brands', $insert_data);
                
                if ($this->db->affected_rows() > 0) {
                    $results['success_count']++;
                    if ($is_online) {
                        $results['active_count']++;
                    } else {
                        $results['pending_count']++;
                    }
                } else {
                    $results['fail_count']++;
                    if (count($results['errors']) < 20) {
                        $results['errors'][] = $brand_name;
                    }
                }
            }
            
            $this->db->flush_cache();
        }
        
        // Hapus file temp setelah selesai
        unlink($this->temp_data_path);
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'results' => $results
        ]));
    }

    public function check() {
        $data['title'] = 'Cek Data Migrasi - Toopai';
        $data['active_menu'] = 'migrasi';
        
        $data['migrated'] = $this->db->where('source', 'migrasi')
            ->order_by('created_at', 'DESC')
            ->limit(100)
            ->get('brands')
            ->result();
        
        $data['total'] = $this->db->where('source', 'migrasi')->count_all_results('brands');
        $data['active_count'] = $this->db->where('source', 'migrasi')->where('status', 'ACTIVE')->count_all_results('brands');
        $data['pending_count'] = $this->db->where('source', 'migrasi')->where('status', 'PENDING')->count_all_results('brands');
        
        $this->load->view('templates/header', $data);
        $this->load->view('migrasi/check_bd', $data);
        $this->load->view('templates/footer');
    }

    public function rollback() {
        $count = $this->db->where('source', 'migrasi')->count_all_results('brands');
        
        if ($count > 0) {
            $this->db->where('source', 'migrasi')->delete('brands');
            $this->session->set_flashdata('success', "Berhasil menghapus {$count} data migrasi");
        } else {
            $this->session->set_flashdata('error', 'Tidak ada data migrasi');
        }
        
        redirect('migrasi_bd/check');
    }

    // ============= HELPER FUNCTIONS (sama seperti sebelumnya) =============
    
    private function get_bd_list() {
        return $this->db->select('id, username, full_name')
            ->where('role', 'BD')
            ->order_by('id', 'ASC')
            ->get('users')
            ->result();
    }

    private function get_bd_map() {
        $bds = $this->get_bd_list();
        $map = [];
        
        foreach ($bds as $bd) {
            $name = strtolower($bd->full_name ?: $bd->username);
            $map[$name] = [
                'id' => $bd->id,
                'name' => $bd->full_name ?: $bd->username
            ];
        }
        
         $manual_map = [
        // Musa Kazim / Kazim Haddad
        'musa kazim' => ['id' => 1, 'name' => 'Musa Kazim'],
        'kazim haddad' => ['id' => 1, 'name' => 'Musa Kazim'],  // <-- TAMBAHKAN
        'kazim' => ['id' => 1, 'name' => 'Musa Kazim'],         // <-- TAMBAHKAN
        
        // Dea Ferlina
        'dea ferlina' => ['id' => 2, 'name' => 'Dea Ferlina'],
        
        // Ulfina Adhaa - TAMBAHKAN VARIASI
        'ulfina adhaa' => ['id' => 3, 'name' => 'Ulfina Adhaa'],
        'ulfina adha' => ['id' => 3, 'name' => 'Ulfina Adhaa'],   // <-- TAMBAHKAN (tanpa 'a')
        'ulfina' => ['id' => 3, 'name' => 'Ulfina Adhaa'],        // <-- TAMBAHKAN
        
        // Audy
        'audy' => ['id' => 4, 'name' => 'AUDY'],
        
        // Muhammad Iqbal
        'muhammad iqbal' => ['id' => 5, 'name' => 'Muhammad Iqbal'],
        
        // Avip Krisdiantoro
        'avip krisdiantoro' => ['id' => 6, 'name' => 'Avip Krisdiantoro'],
        
        // Tiffany
        'tiffany' => ['id' => 7, 'name' => 'Tiffany'],
    ];
        
        foreach ($manual_map as $key => $value) {
            $map[$key] = $value;
        }
        
        return $map;
    }

    private function find_bd_id($handler, $bd_map) {
    if (empty($handler)) {
        return $bd_map['tiffany'] ?? ['id' => null, 'name' => null];
    }
    
    $handler_lower = strtolower(trim($handler));
    
    // Direct match
    if (isset($bd_map[$handler_lower])) {
        return $bd_map[$handler_lower];
    }
    
    // Fuzzy matching untuk kasus khusus
    
    // Kazim Haddad -> Musa Kazim
    if (strpos($handler_lower, 'kazim') !== false) {
        return $bd_map['musa kazim'] ?? $bd_map['kazim haddad'] ?? ['id' => null, 'name' => null];
    }
    
    // Ulfina adha (tanpa 'a') -> Ulfina Adhaa
    if (strpos($handler_lower, 'ulfina adha') !== false && strpos($handler_lower, 'ulfina adhaa') === false) {
        return $bd_map['ulfina adhaa'] ?? ['id' => null, 'name' => null];
    }
    
    // Partial match untuk BD yang dikenal
    $known_patterns = [
        'musa' => 'musa kazim',
        'kazim' => 'musa kazim',
        'dea' => 'dea ferlina',
        'ulfina' => 'ulfina adhaa',
        'audy' => 'audy',
        'iqbal' => 'muhammad iqbal',
        'avip' => 'avip krisdiantoro',
        'tiffany' => 'tiffany',
    ];
    
    foreach ($known_patterns as $pattern => $map_key) {
        if (strpos($handler_lower, $pattern) !== false) {
            return $bd_map[$map_key] ?? ['id' => null, 'name' => null];
        }
    }
    
    // Fallback ke Tiffany
    return $bd_map['tiffany'] ?? ['id' => null, 'name' => null];
}

    private function extract_handler_name($raw) {
        if (empty($raw)) return '';
        
        $parts = preg_split('/[;,]/', $raw);
        $first = trim($parts[0]);
        
        if (preg_match('/\d{1,2}\/\d{1,2}\/\d{2,4}/', $first)) {
            if (isset($parts[1])) {
                $first = trim($parts[1]);
            }
        }
        
        $first = preg_replace('/\d{1,2}\/\d{1,2}\/\d{2,4}/', '', $first);
        $first = preg_replace('/\d+[.,]\d+[KMBJT]/i', '', $first);
        $first = trim($first);
        
        if (strlen($first) < 3) {
            return $raw;
        }
        
        return $first;
    }

    private function map_headers($headers) {
        $map = [
            'handler' => 0,
            'brand_name' => 1,
            'unit_sold' => 2,
            'contact' => 3,
            'status' => 4
        ];
        
        foreach ($headers as $idx => $header) {
            $h = strtolower(trim($header));
            if (strpos($h, 'hndler') !== false || $h == 'handler') $map['handler'] = $idx;
            if (strpos($h, 'nama brand') !== false || $h == 'brand' || $h == 'nama_brand') $map['brand_name'] = $idx;
            if (strpos($h, 'unit sold') !== false || $h == 'unit_sold') $map['unit_sold'] = $idx;
            if (strpos($h, 'contact') !== false || $h == 'whatsapp') $map['contact'] = $idx;
            if (strpos($h, 'status') !== false) $map['status'] = $idx;
        }
        
        return $map;
    }

    private function format_whatsapp($contact) {
        if (empty($contact)) return '';
        if (strpos($contact, 'DM') !== false) return '';
        if (strpos($contact, 'EMAIL') !== false) return '';
        if (strpos($contact, 'TIKTOK') !== false) return '';
        if (strpos($contact, 'IG') !== false) return '';
        if (strpos($contact, '@') !== false) return '';
        
        $number = preg_replace('/[^0-9]/', '', $contact);
        
        if (preg_match('/^62/', $number)) {
            $number = '0' . substr($number, 2);
        }
        if (preg_match('/^0[0-9]{9,12}$/', $number)) {
            return $number;
        }
        if (preg_match('/^[0-9]{9,12}$/', $number) && !preg_match('/^0/', $number)) {
            return '0' . $number;
        }
        
        return $number;
    }

    private function parse_unit_sold($unit_sold) {
        if (empty($unit_sold)) return 0;
        
        $unit_sold = str_replace(',', '.', $unit_sold);
        $unit_sold = strtoupper($unit_sold);
        
        if (strpos($unit_sold, 'JT') !== false) {
            $value = floatval(str_replace('JT', '', $unit_sold));
            return intval($value * 1000000);
        }
        if (strpos($unit_sold, 'RB') !== false) {
            $value = floatval(str_replace('RB', '', $unit_sold));
            return intval($value * 1000);
        }
        if (strpos($unit_sold, 'K') !== false) {
            $value = floatval(str_replace('K', '', $unit_sold));
            return intval($value * 1000);
        }
        if (strpos($unit_sold, 'M') !== false) {
            $value = floatval(str_replace('M', '', $unit_sold));
            return intval($value * 1000000);
        }
        
        return intval(preg_replace('/[^0-9]/', '', $unit_sold));
    }

    private function guess_category($brand_name) {
        $brand_lower = strtolower($brand_name);
        
        $categories = [
            'BEAUTY' => ['beauty', 'skincare', 'cosmetic', 'makeup', 'glow', 'skin', 'face', 'lotion', 'serum', 'cosmetics', 'perfume', 'parfume'],
            'FASHION' => ['fashion', 'hijab', 'clothing', 'apparel', 'shoes', 'bag', 'wear', 'style', 'outfit', 'jeans', 'store'],
            'FOOD' => ['food', 'snack', 'drink', 'coffee', 'tea', 'cake', 'bakery', 'resto', 'cafe', 'makanan', 'cemilan', 'chocolate'],
            'ELECTRONICS' => ['electronic', 'gadget', 'phone', 'laptop', 'computer', 'tech'],
            'MOM_BABY' => ['baby', 'mom', 'child', 'kids', 'parenting', 'mama', 'mother', 'bunda'],
            'HEALTH' => ['health', 'herbal', 'vitamin', 'supplement', 'wellness', 'care', 'sehat'],
            'HOME_LIVING' => ['home', 'furniture', 'decor', 'kitchen', 'household', 'rumah', 'living']
        ];
        
        foreach ($categories as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($brand_lower, $keyword) !== false) {
                    return $category;
                }
            }
        }
        
        return 'OTHER';
    }
}