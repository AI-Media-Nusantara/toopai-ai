<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cron Contact Controller
 * Khusus untuk mengambil kontak creator dari TikTok API
 * HANYA memproses creator yang sudah punya tiktok_open_id
 * 
 * Cara panggil:
 *   - CLI: php index.php cron_contact process
 *   - URL (curl): /cron_contact/process?token=TOKEN&cron=1
 */
class Cron_contact extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        
        // Load library dan model
        $this->load->library('Tiktok_partner_crawler');
        $this->load->model('Creator_contact_model');
         $this->load->model('BrandCreator_model');
        $this->load->model('User_log_model');
        $this->load->database();
        
        // Set time limit
        set_time_limit(0);
        ini_set('memory_limit', '512M');
    }
    
    /**
     * MAIN CRON JOB - Proses ambil kontak creator
     * HANYA untuk creator yang sudah punya tiktok_open_id
     * 
     * Cara panggil via curl:
     * curl "https://www.toopai.ai/cron_contact/process?token=Toopai2026?_12345&cron=1"
     */
  public function process() {
        // 🔒 Security check
        $is_cli = $this->input->is_cli_request();
        $token = $this->input->get('token');
        $is_cron = $this->input->get('cron') == 1;
        $cron_token = CRON_SECRET_TOKEN ?? 'Toopai2026?_12345';
        
        if (!$is_cli && ($token !== $cron_token || !$is_cron)) {
            $this->_log_error('Access denied - Invalid token');
            die('Access denied');
        }
        
        // Log start
        $start_time = microtime(true);
        $start_date = date('Y-m-d H:i:s');
        $this->_log("[START] {$start_date} - Starting full discovery process\n");
        echo "[START] {$start_date} - Starting full discovery process\n";
        
        // ========== STEP 1: AMBIL CREATOR YANG PUNYA FASTMOSS_UID ==========
        $limit = 10; // Ambil 10 creator per run (karena prosesnya berat)
        $creators = $this->Creator_contact_model->get_creators_with_fastmoss_uid($limit);
        
        if (empty($creators)) {
            $message = "[{$start_date}] No creators with fastmoss_uid found\n";
            $this->_log($message);
            echo $message;
            
            $this->User_log_model->log(
                1,
                'system',
                'SYSTEM',
                'CRON_CONTACT',
                "No creators with fastmoss_uid found"
            );
            return;
        }
        
        $message = "[{$start_date}] Found " . count($creators) . " creators with fastmoss_uid\n";
        $this->_log($message);
        echo $message;
        
        // ========== STATISTIK ==========
        $total_processed = 0;
        $total_products_found = 0;
        $total_products_saved = 0;
        $total_contact_found = 0;
        $total_no_contact = 0;
        $total_errors = 0;
        $error_details = [];
        $creators_detail = [];
        
        // ========== PROSES SETIAP CREATOR ==========
        foreach ($creators as $creator) {
            $creator_detail = [
                'username' => $creator->username,
                'fastmoss_uid' => $creator->fastmoss_uid,
                'products_found' => 0,
                'products_saved' => 0,
                'tiktok_oecuid' => null,
                'whatsapp' => null,
                'email' => null,
                'status' => 'processing'
            ];
            
            $log_msg = "\n" . str_repeat('=', 60) . "\n";
            $log_msg .= "📦 Processing Creator: @" . $creator->username . "\n";
            $log_msg .= "   FastMoss UID: " . ($creator->fastmoss_uid ?? 'N/A') . "\n";
            $log_msg .= "   Brand ID: " . ($creator->brand_id ?? 'N/A') . "\n";
            $log_msg .= str_repeat('=', 60) . "\n";
            $this->_log($log_msg);
            echo $log_msg;
            
            try {
                // ============================================================
                // STEP 2: AMBIL SEMUA PRODUK DARI FASTMOSS
                // ============================================================
                $msg = "\n📡 [1/4] Fetching products from FastMoss...\n";
                $this->_log($msg);
                echo $msg;
                
                $products = $this->BrandCreator_model->get_creator_all_products($creator->fastmoss_uid);
                $product_count = count($products);
                $creator_detail['products_found'] = $product_count;
                $total_products_found += $product_count;
                
                if (!empty($products)) {
                    $msg = "   ✅ Found " . $product_count . " products\n";
                    $this->_log($msg);
                    echo $msg;
                    
                    // Tampilkan sample produk
                    $sample = array_slice($products, 0, 3);
                    foreach ($sample as $idx => $p) {
                        $msg = "      " . ($idx+1) . ". " . substr($p['product_name'] ?? 'Unknown', 0, 50) . " - Sales: " . ($p['sales_count'] ?? 0) . "\n";
                        $this->_log($msg);
                        echo $msg;
                    }
                    if ($product_count > 3) {
                        $msg = "      ... and " . ($product_count - 3) . " more products\n";
                        $this->_log($msg);
                        echo $msg;
                    }
                    
                    // ============================================================
                    // STEP 3: SIMPAN PRODUK KE DATABASE
                    // ============================================================
                    $msg = "\n💾 [2/4] Saving products to database...\n";
                    $this->_log($msg);
                    echo $msg;
                    
                    // Cari brand_creator_id
                    $brand_creator = $this->db->where('brand_id', $creator->brand_id)
                        ->where('creator_username', $creator->username)
                        ->get('brand_creators')
                        ->row();
                    
                    if ($brand_creator) {
                        $saved = $this->BrandCreator_model->save_creator_products($brand_creator->id, $products);
                        $creator_detail['products_saved'] = $saved;
                        $total_products_saved += $saved;
                        
                        $msg = "   ✅ Saved " . $saved . " new products (updated existing ones)\n";
                        $this->_log($msg);
                        echo $msg;
                    } else {
                        $msg = "   ⚠️ No brand_creator found, cannot save products\n";
                        $this->_log($msg);
                        echo $msg;
                    }
                    
                } else {
                    $msg = "   ⚠️ No products found for this creator\n";
                    $this->_log($msg);
                    echo $msg;
                }
                
                // ============================================================
                // STEP 4: CARI CREATOR DI TIKTOK (untuk dapat oecuid)
                // ============================================================
                $msg = "\n🔍 [3/4] Searching creator on TikTok...\n";
                $this->_log($msg);
                echo $msg;
                
                // Cek apakah sudah punya tiktok_open_id
                if (!empty($creator->tiktok_open_id)) {
                    $creator_oecuid = $creator->tiktok_open_id;
                    $msg = "   ✅ Using existing tiktok_open_id: {$creator_oecuid}\n";
                    $this->_log($msg);
                    echo $msg;
                } else {
                    // Cari di TikTok
                    $find_result = $this->tiktok_partner_crawler->find_creator($creator->username);
                    $found_creator = $this->tiktok_partner_crawler->parse_creator_result($find_result, $creator->username);
                    
                    if (!$found_creator) {
                        $msg = "   ❌ Creator not found on TikTok\n";
                        $this->_log($msg);
                        echo $msg;
                        
                        // Update status
                        $this->Creator_contact_model->upsert([
                            'creator_username' => $creator->username,
                            'fastmoss_uid' => $creator->fastmoss_uid,
                            'status' => 'NOT_FOUND',
                            'crawled_at' => date('Y-m-d H:i:s')
                        ]);
                        
                        $creator_detail['status'] = 'not_found';
                        $total_no_contact++;
                        $total_processed++;
                        continue;
                    }
                    
                    // Extract creator_oecuid
                    $creator_oecuid = $this->tiktok_partner_crawler->extract_creator_oecuid($found_creator);
                    
                    if (empty($creator_oecuid)) {
                        $msg = "   ⚠️ No creator_oecuid found for username: {$creator->username}\n";
                        $this->_log($msg);
                        echo $msg;
                        
                        $creator_detail['status'] = 'no_oecuid';
                        $total_no_contact++;
                        $total_processed++;
                        continue;
                    }
                    
                    // Update tiktok_open_id di database
                    $this->db->where('username', $creator->username)
                             ->update('creators', [
                                 'tiktok_open_id' => $creator_oecuid,
                                 'updated_at' => date('Y-m-d H:i:s')
                             ]);
                    
                    $msg = "   ✅ Found TikTok oecuid: {$creator_oecuid}\n";
                    $this->_log($msg);
                    echo $msg;
                }
                
                $creator_detail['tiktok_oecuid'] = $creator_oecuid;
                
                // ============================================================
                // STEP 5: AMBIL KONTAK DARI TIKTOK API
                // ============================================================
                $msg = "\n📞 [4/4] Fetching contact from TikTok API...\n";
                $this->_log($msg);
                echo $msg;
                
                $contact_response = $this->tiktok_partner_crawler->get_contact($creator_oecuid);
                $contact = $this->tiktok_partner_crawler->parse_contact($contact_response);
                
                $whatsapp = $contact['whatsapp'] ?? null;
                $email = $contact['email'] ?? null;
                $has_contact = !empty($whatsapp) || !empty($email);
                
                if ($has_contact) {
                    $msg = "   ✅ Found contact!\n";
                    $msg .= "      WhatsApp: " . ($whatsapp ?: '-') . "\n";
                    $msg .= "      Email: " . ($email ?: '-') . "\n";
                    $this->_log($msg);
                    echo $msg;
                    $total_contact_found++;
                } else {
                    $msg = "   ⚠️ No contact found (WhatsApp/Email not available)\n";
                    $this->_log($msg);
                    echo $msg;
                    $total_no_contact++;
                }
                
                $creator_detail['whatsapp'] = $whatsapp;
                $creator_detail['email'] = $email;
                $creator_detail['status'] = $has_contact ? 'complete' : 'no_contact';
                
                // ============================================================
                // STEP 6: SIMPAN SEMUA KE DATABASE
                // ============================================================
                $msg = "\n💾 Saving all data to database...\n";
                $this->_log($msg);
                echo $msg;
                
                // 6a. Simpan ke creator_contacts
                $save_data = [
                    'creator_username' => $creator->username,
                    'creator_oecuid' => $creator_oecuid,
                    'fastmoss_uid' => $creator->fastmoss_uid,
                    'display_name' => $creator->full_name ?? $creator->username,
                    'whatsapp' => $whatsapp,
                    'email' => $email,
                    'avatar_url' => $creator->avatar_url ?? null,
                    'status' => $has_contact ? 'COMPLETE' : 'NO_CONTACT',
                    'raw_response' => json_encode([
                        'contact' => $contact['raw'],
                        'contact_http_code' => $contact_response['http_code'] ?? null,
                    ]),
                    'crawled_at' => date('Y-m-d H:i:s')
                ];
                
                $this->Creator_contact_model->upsert($save_data);
                
                // 6b. Update tabel creators
                $update_data = ['updated_at' => date('Y-m-d H:i:s')];
                if ($whatsapp) {
                    $update_data['phone'] = $whatsapp;
                }
                if ($email) {
                    $update_data['email'] = $email;
                }
                
                $this->db->where('username', $creator->username)
                         ->update('creators', $update_data);
                
                // 6c. Update brand_creators status
                $this->db->where('brand_id', $creator->brand_id)
                         ->where('creator_username', $creator->username)
                         ->update('brand_creators', [
                             'status' => $has_contact ? 'ACTIVE' : 'APPROVED',
                             'updated_at' => date('Y-m-d H:i:s')
                         ]);
                
                $msg = "   ✅ All data saved successfully!\n";
                $this->_log($msg);
                echo $msg;
                
                $total_processed++;
                $creators_detail[] = $creator_detail;
                
            } catch (Exception $e) {
                $total_errors++;
                $error_msg = "\n❌ ERROR: " . $e->getMessage() . "\n";
                $error_msg .= "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
                $this->_log($error_msg);
                echo $error_msg;
                
                $error_details[] = [
                    'username' => $creator->username,
                    'fastmoss_uid' => $creator->fastmoss_uid,
                    'error' => $e->getMessage()
                ];
                
                $creator_detail['status'] = 'error';
                $creators_detail[] = $creator_detail;
            }
            
            // 🔥 SLEEP untuk menghindari rate limit
            $sleep_time = rand(5, 12);
            $msg = "\n⏳ Sleeping for {$sleep_time} seconds...\n";
            $this->_log($msg);
            echo $msg;
            sleep($sleep_time);
        }
        
        // ========== SUMMARY ==========
        $end_time = microtime(true);
        $duration = round($end_time - $start_time, 2);
        
        $summary = "\n" . str_repeat('=', 60) . "\n";
        $summary .= "📊 SUMMARY\n";
        $summary .= str_repeat('=', 60) . "\n";
        $summary .= "Total creators processed: " . $total_processed . "\n";
        $summary .= "Total products found: " . $total_products_found . "\n";
        $summary .= "Total products saved: " . $total_products_saved . "\n";
        $summary .= "Contacts found: " . $total_contact_found . "\n";
        $summary .= "No contact: " . $total_no_contact . "\n";
        $summary .= "Errors: " . $total_errors . "\n";
        $summary .= "Duration: " . $duration . " seconds\n";
        $summary .= str_repeat('=', 60) . "\n";
        
        $this->_log($summary);
        echo $summary;
        
        // Log ke database
        $this->User_log_model->log(
            1,
            'system',
            'SYSTEM',
            'CRON_CONTACT',
            $summary . ( !empty($error_details) ? ' | Errors: ' . json_encode($error_details) : '' )
        );
        
        // Detail per creator
        if (!empty($creators_detail)) {
            $detail_log = "\n📋 DETAIL PER CREATOR:\n";
            $detail_log .= str_repeat('-', 40) . "\n";
            foreach ($creators_detail as $detail) {
                $detail_log .= "  @" . $detail['username'] . "\n";
                $detail_log .= "    FastMoss UID: " . ($detail['fastmoss_uid'] ?? 'N/A') . "\n";
                $detail_log .= "    Products: " . $detail['products_found'] . " found, " . $detail['products_saved'] . " saved\n";
                $detail_log .= "    TikTok oecuid: " . ($detail['tiktok_oecuid'] ?? 'N/A') . "\n";
                $detail_log .= "    WhatsApp: " . ($detail['whatsapp'] ?? '-') . "\n";
                $detail_log .= "    Email: " . ($detail['email'] ?? '-') . "\n";
                $detail_log .= "    Status: " . $detail['status'] . "\n";
                $detail_log .= str_repeat('-', 40) . "\n";
            }
            $this->_log($detail_log);
            echo $detail_log;
        }
        
        $final = "\n[" . date('Y-m-d H:i:s') . "] PROCESS COMPLETED\n";
        $this->_log($final);
        echo $final;
    }
    
    
    /**
     * Process single creator (for testing)
     * 
     * Cara panggil: /cron_contact/single?username=evanurr&token=TOKEN&cron=1
     */
    public function single() {
        $token = $this->input->get('token');
        $cron_token = CRON_SECRET_TOKEN ?? 'Toopai2026?_12345';
        
        if ($token !== $cron_token) {
            die('Access denied');
        }
        
        $username = $this->input->get('username');
        if (empty($username)) {
            echo "Username required. Example: /cron_contact/single?username=evanurr&token=TOKEN\n";
            return;
        }
        
        header('Content-Type: application/json');
        
        // Cari creator di database (harus punya tiktok_open_id)
        $creator = $this->db->select('id, username, full_name, phone, email, tiktok_open_id, avatar_url')
            ->where('username', $username)
            ->where('tiktok_open_id IS NOT NULL')
            ->where('tiktok_open_id !=', '')
            ->get('creators')
            ->row();
        
        if (!$creator) {
            echo json_encode([
                'error' => 'Creator not found or no tiktok_open_id',
                'username' => $username
            ], JSON_PRETTY_PRINT);
            return;
        }
        
        $result = $this->_process_single_creator($creator);
        echo json_encode($result, JSON_PRETTY_PRINT);
    }
    
    /**
     * Process single creator (internal)
     */
    private function _process_single_creator($creator) {
        // Ambil kontak
        $contact_response = $this->tiktok_partner_crawler->get_contact($creator->tiktok_open_id);
        $contact = $this->tiktok_partner_crawler->parse_contact($contact_response);
        
        $whatsapp = $contact['whatsapp'] ?? null;
        $email = $contact['email'] ?? null;
        $has_contact = !empty($whatsapp) || !empty($email);
        
        // Simpan ke Creator_contact_model
        $save_data = [
            'creator_username' => $creator->username,
            'creator_oecuid' => $creator->tiktok_open_id,
            'display_name' => $creator->full_name ?? $creator->username,
            'whatsapp' => $whatsapp,
            'email' => $email,
            'avatar_url' => $creator->avatar_url ?? null,
            'status' => $has_contact ? 'FOUND' : 'NO_CONTACT',
            'raw_response' => json_encode([
                'contact' => $contact['raw'],
                'contact_http_code' => $contact_response['http_code'] ?? null,
            ]),
            'crawled_at' => date('Y-m-d H:i:s')
        ];
        
        $this->Creator_contact_model->upsert($save_data);
        
        // Update tabel creators
        $update_data = ['updated_at' => date('Y-m-d H:i:s')];
        if ($whatsapp) {
            $update_data['phone'] = $whatsapp;
        }
        if ($email) {
            $update_data['email'] = $email;
        }
        
        $this->db->where('username', $creator->username)
                 ->update('creators', $update_data);
        
        return [
            'success' => true,
            'username' => $creator->username,
            'creator_oecuid' => $creator->tiktok_open_id,
            'whatsapp' => $whatsapp,
            'email' => $email,
            'status' => $has_contact ? 'FOUND' : 'NO_CONTACT'
        ];
    }
    
    /**
     * Get status
     * 
     * Cara panggil: /cron_contact/status?token=TOKEN
     */
    public function status() {
        $token = $this->input->get('token');
        $cron_token = CRON_SECRET_TOKEN ?? 'Toopai2026?_12345';
        
        if ($token !== $cron_token) {
            die('Access denied');
        }
        
        header('Content-Type: application/json');
        
        // Ambil statistik dari Creator_contact_model
        $stats = $this->Creator_contact_model->get_stats();
        
        // Tambahkan info dari tabel creators
        $total_creators = $this->db->count_all('creators');
        $has_tiktok_open_id = $this->db->where('tiktok_open_id IS NOT NULL')->where('tiktok_open_id !=', '')->count_all_results('creators');
        $has_phone = $this->db->where('phone IS NOT NULL')->where('phone !=', '')->count_all_results('creators');
        $has_email = $this->db->where('email IS NOT NULL')->where('email !=', '')->count_all_results('creators');
        
        $pending_contact = $this->db->where('tiktok_open_id IS NOT NULL')
            ->where('tiktok_open_id !=', '')
            ->group_start()
                ->where('phone IS NULL')
                ->or_where('phone', '')
                ->or_where('email IS NULL')
                ->or_where('email', '')
            ->group_end()
            ->count_all_results('creators');
        
        $last_run = $this->db->select('MAX(created_at) as last')
            ->from('user_logs')
            ->where('action', 'CRON_CONTACT')
            ->get()
            ->row();
        
        echo json_encode([
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s'),
            'contact_stats' => $stats,
            'creator_stats' => [
                'total_creators' => $total_creators,
                'has_tiktok_open_id' => $has_tiktok_open_id,
                'pending_contact' => $pending_contact,
                'has_phone' => $has_phone,
                'has_email' => $has_email,
                'has_contact' => $this->db->where('phone IS NOT NULL')->where('phone !=', '')->or_where('email IS NOT NULL')->where('email !=', '')->count_all_results('creators')
            ],
            'last_run' => $last_run->last ?? 'Never'
        ], JSON_PRETTY_PRINT);
    }
    
    private function _log($message) {
        $log_dir = APPPATH . 'logs/sync/';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0777, true);
        }
        
        $log_file = $log_dir . 'contact_cron.log';
        file_put_contents($log_file, $message, FILE_APPEND);
    }
    
    private function _log_error($message) {
        $log_dir = APPPATH . 'logs/sync/';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0777, true);
        }
        
        $log_file = $log_dir . 'contact_cron_error.log';
        $log_msg = "[" . date('Y-m-d H:i:s') . "] ERROR: " . $message . "\n";
        file_put_contents($log_file, $log_msg, FILE_APPEND);
    }
}