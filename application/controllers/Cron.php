<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller {
    
    private $max_retries = 3;
    private $retry_delays = [60, 300, 900]; // 1 menit, 5 menit, 15 menit
    
    public function __construct() {
        parent::__construct();
        $this->load->library('Jsm_api');
        $this->load->model('Affiliate_sync_model');
        $this->load->model('Jsm_token_model');
        
        // Set timezone ke Indonesia
        date_default_timezone_set('Asia/Jakarta');
        
        // Security: Only allow CLI access or specific IP
        if (!$this->input->is_cli_request() && $_SERVER['REMOTE_ADDR'] !== '127.0.0.1') {
            die('Access denied');
        }
    }
    /**
 * GET UTC RANGE dari local date (sama persis dengan fungsi manual yang berfungsi)
 */
private function get_utc_range_from_local_date($local_date, $timezone = 'Asia/Jakarta')
{
    $date = new DateTime($local_date, new DateTimeZone($timezone));
    $date->setTime(0, 0, 0);
    $date->setTimezone(new DateTimeZone('UTC'));
    $start_utc = $date->getTimestamp();
    $end_utc = $start_utc + 86400;
    
    return [
        'start' => $start_utc,
        'end' => $end_utc,
        'start_formatted' => gmdate('Y-m-d H:i:s', $start_utc) . ' UTC',
        'end_formatted' => gmdate('Y-m-d H:i:s', $end_utc) . ' UTC',
        'local_date' => $local_date
    ];
}
    /**
     * Main sync function - called by cron every 5 minutes
     
     */
    public function sync_all() {
    echo "[" . date('Y-m-d H:i:s') . "] ========== STARTING FULL SYNC ==========\n";
    
    // 1. Sync campaigns dari API
    echo "\n[1/7] Syncing campaigns...\n";
    $this->sync_campaigns();
    
    // 2. 🔥 SYNC PENDING PRODUCTS (untuk Task 2)
    echo "\n[2/7] Syncing pending products...\n";
    $this->sync_pending_products();
    
    // 3. 🔥 SYNC APPROVED PRODUCTS (untuk Task 3)
    echo "\n[3/7] Syncing approved products...\n";
    $this->sync_approved_products();
    
    // 4. Sync orders (last 7 days)
    echo "\n[4/7] Syncing orders (last 7 days)...\n";
    $this->sync_orders(date('Y-m-d', strtotime('-7 days')), date('Y-m-d'));
    
    // 5. Sync creator performance
    echo "\n[5/7] Syncing creator performance...\n";
    $this->sync_creator_performance();
    
    // 6. Sync brands data
    echo "\n[6/7] Syncing brands data...\n";
    $this->sync_brands_data();
    
    // 7. Update campaign totals
    echo "\n[7/7] Updating campaign totals...\n";
    $this->update_campaign_totals();
    
    // Process queue items
    echo "\nProcessing queue items...\n";
    $this->process_queue();
    
    // 8. Auto-detect creator link usage
    echo "\n[8/7] Running auto-detection of creator link usage...\n";
    $this->auto_detect_creator_link_usage();

    echo "\n[" . date('Y-m-d H:i:s') . "] ========== FULL SYNC COMPLETED ==========\n";
}

/**
 * Auto-detect creator link usage (onboard from contacted list when they use the link)
 */
public function auto_detect_creator_link_usage() {
    echo "[" . date('Y-m-d H:i:s') . "] Starting auto-detection of creator link usage...\n";
    $this->load->model('CreatorScouting_model');
    $results = $this->CreatorScouting_model->run_auto_detection();
    echo "  Processed " . $results['scouting_onboarded'] . " new onboardings from contacted Scouting List.\n";
    echo "  Activated " . $results['creators_activated'] . " creators in creators table.\n";
    echo "[" . date('Y-m-d H:i:s') . "] Auto-detection completed.\n";
}
    
    /**
     * Sync campaigns from TikTok API
     */
    public function sync_campaigns($retry_count = 0) {
    $log_id = $this->Affiliate_sync_model->log_sync_start('campaigns');
    $success_count = 0;
    $failed_count = 0;
    
    try {
        echo "[" . date('Y-m-d H:i:s') . "] Syncing campaigns...\n";
        
        $result = $this->jsm_api->get_ongoing_campaigns(['page_size' => 50]);
        
        if (!$result['success']) {
            throw new Exception($result['message'] ?? 'Failed to fetch campaigns');
        }
        
        $campaigns = $result['data'];
        
        foreach ($campaigns as $campaign) {
            try {
                // Save campaign with images
                $this->Affiliate_sync_model->save_campaign($campaign);
                
                // 🔥 SYNC COMPLETE PRODUCTS (ALL REVIEW STATUS)
                $this->sync_campaign_products_complete($campaign['id']);
                
                $success_count++;
                echo "  ✓ Campaign: " . ($campaign['name'] ?? $campaign['id']) . "\n";
                
            } catch (Exception $e) {
                $failed_count++;
                echo "  ✗ Failed to sync campaign: " . $e->getMessage() . "\n";
                $this->Affiliate_sync_model->add_to_queue('campaign_products', $campaign['id']);
            }
        }
        
        $this->Affiliate_sync_model->log_sync_end($log_id, 'success', $success_count + $failed_count, $success_count, $failed_count);
        
    } catch (Exception $e) {
        $error_msg = $e->getMessage();
        echo "[" . date('Y-m-d H:i:s') . "] ERROR syncing campaigns: $error_msg\n";
        $this->Affiliate_sync_model->log_sync_end($log_id, 'failed', 0, 0, 0, $error_msg);
        
        if ($retry_count < $this->max_retries) {
            $delay = $this->retry_delays[$retry_count];
            echo "Retrying in {$delay} seconds...\n";
            sleep($delay);
            $this->sync_campaigns($retry_count + 1);
        }
    }
}
    
  /**
 * Sync products for a specific campaign - CRON JOB (FIXED)
 */
public function sync_campaign_products($campaign_id, $retry_count = 0) {
    try {
        echo "  Syncing products for campaign: $campaign_id\n";
        
        // 🔥 SAMA PERSIS dengan manual sync di Bd.php
        $result = $this->jsm_api->get_campaign_products($campaign_id, [
            'page_size' => 100,
            'review_status' => 'APPROVED'
        ]);
        
        if (!$result['success']) {
            throw new Exception($result['message'] ?? 'Failed to fetch products');
        }
        
        // 🔥 LANGSUNG PAKAI $result['data'] TANPA DIUBAH
        $products = $result['data'];
        $success_count = 0;
        
        // Debug sample product pertama
        if (!empty($products)) {
            echo "    Sample product data (raw from API):\n";
            $sample = $products[0];
            echo "      - ID: " . ($sample['id'] ?? 'N/A') . "\n";
            echo "      - Name: " . ($sample['name'] ?? 'N/A') . "\n";
            echo "      - lowest_price: " . json_encode($sample['lowest_price'] ?? 'NULL') . "\n";
            echo "      - partner_commission_rate: " . ($sample['partner_commission_rate'] ?? 0) . "\n";
            echo "      - open_collaboration_commission_rate: " . ($sample['open_collaboration_commission_rate'] ?? 0) . "\n";
        }
        
        // 🔥 LANGSUNG LOOP DAN SIMPAN, TANPA MODIFIKASI FORMAT
        foreach ($products as $product) {
            // 🔥 TIDAK ADA FORMAT ULANG! LANGSUNG KIRIM KE SAVE_PRODUCT
            $this->Affiliate_sync_model->save_product($product, $campaign_id);
            $success_count++;
        }
        
        echo "    ✓ Synced $success_count products\n";
        
    } catch (Exception $e) {
        echo "    ✗ Failed to sync products: " . $e->getMessage() . "\n";
        
        if ($retry_count < $this->max_retries) {
            $delay = $this->retry_delays[$retry_count];
            sleep($delay);
            $this->sync_campaign_products($campaign_id, $retry_count + 1);
        } else {
            $this->Affiliate_sync_model->add_to_queue('campaign_products', $campaign_id);
        }
    }
}
/**
 * Sync pending products from API to database (untuk Task 2)
 * Called every 5-10 minutes
 */
public function sync_pending_products() {
    echo "[" . date('Y-m-d H:i:s') . "] ===== SYNC PENDING PRODUCTS =====\n";
    
    // Ambil semua campaign yang ongoing
    $campaigns = $this->db->select('campaign_id, campaign_name')
                           ->where('status', 'ONGOING')
                           ->get('affiliate_campaigns')
                           ->result();
    
    if (empty($campaigns)) {
        echo "  No ongoing campaigns found.\n";
        return;
    }
    
    $total_pending = 0;
    $total_new = 0;
    $total_updated = 0;
    
    foreach ($campaigns as $campaign) {
        echo "  Processing campaign: {$campaign->campaign_name} ({$campaign->campaign_id})\n";
        
        try {
            $result = $this->jsm_api->get_products_by_review_status(
                $campaign->campaign_id, 
                'PENDING', 
                100
            );
            
            if (!$result['success']) {
                echo "    ✗ Failed to fetch pending products: " . ($result['message'] ?? 'Unknown error') . "\n";
                continue;
            }
            
            $products = $result['data']['products'] ?? [];
            $pending_count = count($products);
            $total_pending += $pending_count;
            
            echo "    Found {$pending_count} pending products\n";
            
            foreach ($products as $product) {
                $product_id = $product['id'] ?? '';
                $campaign_id = $campaign->campaign_id;
                
                // 🔥 SIMPAN LANGSUNG RAW DARI API, TANPA KONVERSI
                $open_commission = $product['open_collaboration_commission_rate'] ?? 0;
                $partner_commission = $product['partner_commission_rate'] ?? 0;
                $creator_commission = $product['creator_commission_rate'] ?? 0;
                $total_commission = $product['total_commission_rate'] ?? 0;
                $shop_ads = $product['shop_ads_commission_rate'] ?? 0;
                $price = 0;
if (isset($product['lowest_price']) && is_array($product['lowest_price'])) {
    $price = floatval($product['lowest_price']['amount'] ?? 0);
} elseif (isset($product['highest_price']) && is_array($product['highest_price'])) {
    $price = floatval($product['highest_price']['amount'] ?? 0);
} elseif (isset($product['price']) && !is_array($product['price'])) {
    $price = floatval($product['price']);
}
// 🔥 SHOP ADS
        $shop_ads = $product['shop_ads_commission_rate'] ?? 0;
        
        // 🔥 ADS COLLABORATION (TAMBAHKAN INI!)
        $ads_collaboration = $product['partner_shop_ads_commission_rate'] ?? 0;
        
                // 🔥 GUNAKAN NAMA KOLOM YANG BENAR
                $product_data = [
                    'product_id' => $product_id,
                    'campaign_id' => $campaign_id,
                    'product_name' => $product['name'] ?? '',
                    'price' => $price,
                    'lowest_price' =>floatval($product['lowest_price']['amount'] ?? 0),
                    'highest_price' => floatval($product['highest_price']['amount'] ?? 0),
                    'image_url' => $product['main_image_url'] ?? '',
                    'shop_name' => $product['shop_name'] ?? '',
                    'review_status' => $product['review_status'] ?? 'PENDING',
                    'open_commission_rate' => $open_commission,
                    'partner_commission_rate' => $partner_commission,
                    'creator_commission_rate' => $creator_commission,
                    'total_commission_rate' => $total_commission,
                    'shop_ads' => $shop_ads,
                    'ads_collaboration' => $ads_collaboration,
                    'inventory' => $product['inventory'] ?? 0,
                    'sales_count' => $product['product_sales'] ?? 0,
                    'sample_quota' => $product['sample_quota'] ?? 0,
                    'last_sync' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'raw_data' => json_encode($product)
                ];
                
                // Cek apakah sudah ada
                $existing = $this->db->where('product_id', $product_id)
                                     ->where('campaign_id', $campaign_id)
                                     ->get('affiliate_products')
                                     ->row();
                
                if ($existing) {
                    // Update jika ada perubahan
                    $this->db->where('id', $existing->id)->update('affiliate_products', $product_data);
                    $total_updated++;
                } else {
                    $product_data['created_at'] = date('Y-m-d H:i:s');
                    $this->db->insert('affiliate_products', $product_data);
                    $total_new++;
                    echo "      ➕ New product: {$product_id}\n";
                }
            }
            
        } catch (Exception $e) {
            echo "    ✗ Error: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n========================================\n";
    echo "✅ Pending products sync completed:\n";
    echo "   - Total pending from API: {$total_pending}\n";
    echo "   - New products inserted: {$total_new}\n";
    echo "   - Products updated: {$total_updated}\n";
    echo "========================================\n";
}
/**
 * Sync approved products from API to database (untuk Task 3 Monitoring)
 */
public function sync_approved_products() {
    echo "[" . date('Y-m-d H:i:s') . "] ===== SYNC APPROVED PRODUCTS =====\n";
    
    $campaigns = $this->db->select('campaign_id, campaign_name')
                           ->where('status', 'ONGOING')
                           ->get('affiliate_campaigns')
                           ->result();
    
    if (empty($campaigns)) {
        echo "  No ongoing campaigns found.\n";
        return;
    }
    
    $total_approved = 0;
    $total_updated = 0;
    
    foreach ($campaigns as $campaign) {
        echo "  Processing campaign: {$campaign->campaign_name} ({$campaign->campaign_id})\n";
        
        try {
            $result = $this->jsm_api->get_products_by_review_status(
                $campaign->campaign_id, 
                'APPROVED', 
                100
            );
            
            if (!$result['success']) {
                echo "    ✗ Failed to fetch approved products: " . ($result['message'] ?? 'Unknown error') . "\n";
                continue;
            }
            
            $products = $result['data']['products'] ?? [];
            $approved_count = count($products);
            $total_approved += $approved_count;
            
            echo "    Found {$approved_count} approved products\n";
            
            foreach ($products as $product) {
                $product_id = $product['id'] ?? '';
                $campaign_id = $campaign->campaign_id;
                
                // 🔥 SIMPAN LANGSUNG RAW DARI API
                $open_commission = $product['open_collaboration_commission_rate'] ?? 0;
                $partner_commission = $product['partner_commission_rate'] ?? 0;
                $creator_commission = $product['creator_commission_rate'] ?? 0;
                $total_commission = $product['total_commission_rate'] ?? 0;
                $shop_ads = $product['shop_ads_commission_rate'] ?? 0;
                
                $product_data = [
                    'product_id' => $product_id,
                    'campaign_id' => $campaign_id,
                    'product_name' => $product['name'] ?? '',
                    'price' => $product['price'] ?? 0,
                    'image_url' => $product['main_image_url'] ?? '',
                    'shop_name' => $product['shop_name'] ?? '',
                    'review_status' => 'APPROVED',
                    'open_commission_rate' => $open_commission,
                    'partner_commission_rate' => $partner_commission,
                    'creator_commission_rate' => $creator_commission,
                    'total_commission_rate' => $total_commission,
                    'shop_ads' => $shop_ads,
                    'inventory' => $product['inventory'] ?? 0,
                    'sales_count' => $product['product_sales'] ?? 0,
                    'sample_quota' => $product['sample_quota'] ?? 0,
                    'approved_at' => date('Y-m-d H:i:s'),
                    'last_sync' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $existing = $this->db->where('product_id', $product_id)
                                     ->where('campaign_id', $campaign_id)
                                     ->get('affiliate_products')
                                     ->row();
                
                if ($existing) {
                    $this->db->where('id', $existing->id)->update('affiliate_products', $product_data);
                    $total_updated++;
                } else {
                    $product_data['created_at'] = date('Y-m-d H:i:s');
                    $this->db->insert('affiliate_products', $product_data);
                    echo "      ➕ New approved product: {$product_id}\n";
                }
            }
            
        } catch (Exception $e) {
            echo "    ✗ Error: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n========================================\n";
    echo "✅ Approved products sync completed:\n";
    echo "   - Total approved from API: {$total_approved}\n";
    echo "   - Products updated to APPROVED: {$total_updated}\n";
    echo "========================================\n";
}



    
    /**
     * Sync orders within date range (OPTIMIZED FOR LAST 7 DAYS)
     */
    public function sync_orders($start_date = null, $end_date = null, $retry_count = 0) {
    $log_id = $this->Affiliate_sync_model->log_sync_start('orders');
    
    if (!$start_date) {
        $start_date = date('Y-m-d', strtotime('-7 days'));
    }
    if (!$end_date) {
        $end_date = date('Y-m-d');
    }
    
    echo "[" . date('Y-m-d H:i:s') . "] =========================================\n";
    echo "Syncing orders from $start_date to $end_date\n";
    echo "Timezone: Asia/Jakarta (WIB)\n";
    echo "=========================================\n\n";
    
    $success_count = 0;
    $failed_count = 0;
    $updated_count = 0;
    
    try {
        $current = strtotime($start_date);
        $end = strtotime($end_date);
        
        while ($current <= $end) {
            $date = date('Y-m-d', $current);
            $utc_range = $this->get_utc_range_from_local_date($date, 'Asia/Jakarta');
            
            echo "  📅 Date: $date (Local)\n";
            echo "     UTC Range: {$utc_range['start_formatted']} → {$utc_range['end_formatted']}\n";
            
            // 🔥 PAGINATION LENGKAP SEPERTI fetch_orders_with_retry
            $page_token = null;
            $page = 1;
            $max_pages = 100;  // Maksimal 100 page (10,000 orders)
            $daily_count = 0;
            
            do {
                $params = [
                    'create_time_ge' => $utc_range['start'],
                    'create_time_lt' => $utc_range['end'],
                    'page_size' => 100  // Maksimal 100 order per page
                ];
                
                if ($page_token) {
                    $params['page_token'] = $page_token;
                }
                
                echo "     📄 Fetching page $page...\n";
                
                $result = $this->jsm_api->search_affiliate_orders($params);
                
                if (!$result['success']) {
                    throw new Exception($result['message'] ?? 'Failed to fetch orders');
                }
                
                $orders = $result['data'];
                echo "        API returned: " . count($orders) . " orders\n";
                
                // Proses setiap order di halaman ini
                foreach ($orders as $order) {
                    // Debug untuk lihat order_time
    $order_time_utc = $order['create_time_utc'] ?? 'N/A';
    $order_time_local = $order['create_date_local'] ?? 'N/A';
    
    echo "        Order: {$order['order_id']}\n";
    echo "          UTC: $order_time_utc\n";
    echo "          Local: $order_time_local\n";
    echo "          GMV: {$order['affiliate_gmv']}\n";
                    try {
                        $order_id = $order['order_id'] ?? $order['id'];
                        $existing = $this->db->where('order_id', $order_id)
                                             ->get('affiliate_orders')
                                             ->row();
                        
                        $order_time = $order['create_time'] ?? null;
                        $order_time_local = $order_time ? date('Y-m-d H:i:s', $order_time) : 'N/A';
                        
                        if ($existing) {
                            $this->Affiliate_sync_model->save_order($order);
                            $updated_count++;
                            echo "        🔄 Updated order: $order_id (created: $order_time_local)\n";
                        } else {
                            $this->Affiliate_sync_model->save_order($order);
                            $success_count++;
                            echo "        ✅ New order: $order_id (created: $order_time_local)\n";
                        }
                        $daily_count++;
                        
                    } catch (Exception $e) {
                        $failed_count++;
                        echo "        ❌ Failed: " . $e->getMessage() . "\n";
                    }
                }
                
                // 🔥 CEK NEXT PAGE TOKEN (PENTING!)
                $next_page_token = $result['next_page_token'] ?? null;
                $total_count = $result['total_count'] ?? null;
                
                if ($next_page_token && $next_page_token !== $page_token && $page < $max_pages) {
                    $page_token = $next_page_token;
                    $page++;
                    echo "        📌 Next page token found, fetching page $page...\n";
                    usleep(200000); // Delay 0.2 detik
                } else {
                    $page_token = null;
                    echo "        ✅ No more pages. Total orders for $date: $daily_count\n";
                }
                
            } while ($page_token !== null);
            
            echo "     ✓ Date $date: $daily_count orders processed\n\n";
            
            $current = strtotime('+1 day', $current);
            
            if ($current <= $end) {
                sleep(1);
            }
        }
        
        $this->Affiliate_sync_model->log_sync_end($log_id, 'success', $success_count + $failed_count + $updated_count, $success_count, $failed_count);
        
        echo "=========================================\n";
        echo "✅ Orders synced: $success_count new, $updated_count updated, $failed_count failed\n";
        echo "=========================================\n\n";
        
        $this->update_campaign_totals();
        
    } catch (Exception $e) {
        $error_msg = $e->getMessage();
        echo "❌ ERROR syncing orders: $error_msg\n";
        $this->Affiliate_sync_model->log_sync_end($log_id, 'failed', 0, 0, 0, $error_msg);
        
        if (strpos($error_msg, 'token') !== false) {
            $this->handle_token_expired();
        }
        
        if ($retry_count < $this->max_retries) {
            $delay = $this->retry_delays[$retry_count];
            echo "Retrying in {$delay} seconds...\n";
            sleep($delay);
            $this->sync_orders($start_date, $end_date, $retry_count + 1);
        }
    }
}
/**
 * Test UTC conversion
 * Usage: php index.php Cron test_utc 2026-05-01
 */
public function test_utc($date = null) {
    if (!$date) {
        $date = date('Y-m-d');
    }
    
    echo "========================================\n";
    echo "TEST UTC CONVERSION\n";
    echo "========================================\n";
    echo "Local date: $date (Asia/Jakarta)\n\n";
    
    $utc_range = $this->get_utc_range_from_local_date($date, 'Asia/Jakarta');
    
    echo "UTC Range:\n";
    echo "  Start: {$utc_range['start']} ({$utc_range['start_formatted']})\n";
    echo "  End:   {$utc_range['end']} ({$utc_range['end_formatted']})\n\n";
    
    // Test API call with these timestamps
    echo "Testing API call with these timestamps...\n";
    
    $params = [
        'create_time_ge' => $utc_range['start'],
        'create_time_lt' => $utc_range['end'],
        'page_size' => 10
    ];
    
    echo "Params: " . json_encode($params) . "\n\n";
    
    $result = $this->jsm_api->search_affiliate_orders($params);
    
    if (!$result['success']) {
        echo "❌ API Error: " . ($result['message'] ?? 'Unknown') . "\n";
        if (isset($result['raw_response'])) {
            echo "Raw: " . json_encode($result['raw_response']) . "\n";
        }
    } else {
        echo "✅ API returned " . count($result['data']) . " orders\n";
        
        foreach ($result['data'] as $order) {
            $order_id = $order['order_id'] ?? $order['id'] ?? 'Unknown';
            $create_time = $order['create_time'] ?? 'N/A';
            $create_time_local = is_numeric($create_time) ? date('Y-m-d H:i:s', $create_time) : $create_time;
            echo "  - Order: $order_id, Created: $create_time_local\n";
        }
    }
}

    /**
     * Sync only today's orders (for real-time updates)
     * Called every 5 minutes
     */
  public function sync_today_orders() {
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    
    echo "[" . date('Y-m-d H:i:s') . "] ===== REAL-TIME SYNC (FULL PAGINATION) =====\n";
    echo "  Syncing orders for:\n";
    echo "    - Yesterday: $yesterday\n";
    echo "    - Today: $today\n";
    echo "  (Fetching ALL pages from API, max 100 orders per page)\n";
    
    // Sync kedua tanggal dengan pagination lengkap
    $this->sync_orders($yesterday, $today);
    echo "\n[" . date('Y-m-d H:i:s') . "] Syncing creator performance after orders...\n";
    $this->sync_creator_performance();
}

    
    /**
     * Sync last 7 days orders (full accuracy)
     * Called every hour
     */
    public function sync_last_7_days() {
    $start_date = date('Y-m-d', strtotime('-7 days'));
    $end_date = date('Y-m-d');
    
    echo "[" . date('Y-m-d H:i:s') . "] ===== FULL WEEK SYNC =====\n";
    echo "  Period: $start_date to $end_date\n";
    echo "  (Always fetches fresh data from API)\n";
    
    // ALWAYS sync, never skip
    $this->sync_orders($start_date, $end_date);
}
/**
 * Force sync untuk tanggal tertentu (misal jika ada data yang tidak sinkron)
 * Usage: php index.php Cron force_sync 2026-05-01
 */
public function force_sync($date = null) {
    if (!$date) {
        $date = date('Y-m-d');
    }
    
    echo "[" . date('Y-m-d H:i:s') . "] FORCE SYNC for date: $date\n";
    echo "  This will fetch ALL orders for $date from API and update database\n";
    
    // Hapus data lama untuk tanggal tersebut (opsional)
    // $this->db->where('DATE(order_time)', $date)->delete('affiliate_orders');
    
    // Sync ulang
    $this->sync_orders($date, $date);
    
    // Update campaign totals
    $this->update_campaign_totals();
    
    echo "Force sync completed for $date\n";
}
    
    /**
     * Sync missing data from last X days
     */
    public function sync_missing_data($days = 7) {
        echo "[" . date('Y-m-d H:i:s') . "] Checking for missing data in last $days days\n";
        
        // Check which dates have data
        $sql = "
            SELECT DISTINCT DATE(order_time) as order_date
            FROM affiliate_orders
            WHERE order_time >= DATE_SUB(NOW(), INTERVAL ? DAY)
            ORDER BY order_date DESC
        ";
        
        $existing_dates = $this->db->query($sql, [$days])->result();
        $existing_dates_array = array_column($existing_dates, 'order_date');
        
        // Find missing dates
        $missing_dates = [];
        for ($i = 0; $i < $days; $i++) {
            $date = date('Y-m-d', strtotime("-$i days"));
            if (!in_array($date, $existing_dates_array)) {
                $missing_dates[] = $date;
            }
        }
        
        if (!empty($missing_dates)) {
            echo "  Missing data for dates: " . implode(', ', $missing_dates) . "\n";
            foreach ($missing_dates as $date) {
                echo "  Syncing missing date: $date\n";
                $this->sync_orders($date, $date);
            }
        } else {
            echo "  No missing data found\n";
        }
    }
    
    /**
     * Update campaign totals based on orders
     */
    private function update_campaign_totals() {
        echo "  Updating campaign totals...\n";
        
        $campaigns = $this->Affiliate_sync_model->get_campaign_summary();
        
        foreach ($campaigns as $camp) {
            $this->db->where('campaign_id', $camp->campaign_id)
                     ->update('affiliate_campaigns', [
                         'total_gmv' => $camp->actual_gmv ?? 0,
                         'total_orders' => $camp->actual_orders ?? 0,
                         'updated_at' => $this->Affiliate_sync_model->get_current_time()
                     ]);
        }
        
        echo "  âœ“ Updated " . count($campaigns) . " campaign totals\n";
    }
    
    /**
     * Process queue items (retry failed syncs)
     */
    public function process_queue() {
        echo "[" . date('Y-m-d H:i:s') . "] Processing queue...\n";
        
        $queue_items = $this->Affiliate_sync_model->get_pending_queue();
        
        foreach ($queue_items as $item) {
            echo "  Processing: {$item->sync_type} - {$item->campaign_id}\n";
            
            try {
                switch ($item->sync_type) {
                    case 'campaign_products':
                        $this->sync_campaign_products($item->campaign_id);
                        break;
                    case 'orders':
                        $start = $item->start_date ?? date('Y-m-d', strtotime('-7 days'));
                        $end = $item->end_date ?? date('Y-m-d');
                        $this->sync_orders($start, $end);
                        break;
                    case 'links':
                        $this->sync_creator_links($item->campaign_id);
                        break;
                }
                
                $this->Affiliate_sync_model->update_queue_status($item->id, 'completed');
                echo "    âœ“ Completed\n";
                
            } catch (Exception $e) {
                $new_retry = $item->retry_count + 1;
                
                if ($new_retry >= $item->max_retry) {
                    $this->Affiliate_sync_model->update_queue_status($item->id, 'failed', $e->getMessage());
                    echo "    âœ— Failed permanently: " . $e->getMessage() . "\n";
                } else {
                    $this->Affiliate_sync_model->update_queue_status($item->id, 'pending', $e->getMessage());
                    echo "    âš  Will retry later: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    /**
     * Sync creator links for active campaigns
     */
    public function sync_creator_links($campaign_id = null, $retry_count = 0) {
    $log_id = $this->Affiliate_sync_model->log_sync_start('links');
    
    try {
        echo "[" . date('Y-m-d H:i:s') . "] Syncing creator links...\n";
        
        $total_links = 0;
        
        // STEP 1: Sync dari creator_link_assignments (jika ada)
        $assignments = $this->db->select('*')
                                ->from('creator_link_assignments')
                                ->where('status', 'ACTIVE')
                                ->get()
                                ->result();
        
        if (!empty($assignments)) {
            echo "  Found " . count($assignments) . " assignments from creator_link_assignments\n";
            
            foreach ($assignments as $assign) {
                // Hitung statistik dari orders yang sudah ada
                $stats = $this->db->select('
                        COUNT(*) as total_orders,
                        COALESCE(SUM(gmv), 0) as total_gmv,
                        COALESCE(SUM(actual_commission), 0) as total_commission
                    ')
                    ->from('affiliate_orders')
                    ->where('creator_username', $assign->creator_username)
                    ->where('campaign_id', $assign->campaign_id)
                    ->where('product_id', $assign->product_id)
                    ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
                    ->get()
                    ->row();
                
                $link_data = [
                    'link_id' => $assign->assignment_id,
                    'campaign_id' => $assign->campaign_id,
                    'product_id' => $assign->product_id,
                    'product_name' => $assign->product_name,
                    'creator_username' => $assign->creator_username,
                    'affiliate_link' => $assign->affiliate_link,
                    'commission_rate' => $assign->commission_rate,
                    'shared_date' => $assign->shared_date,
                    'expire_date' => $assign->expire_date,
                    'status' => $assign->status,
                    'total_orders' => $stats->total_orders ?? 0,
                    'total_gmv' => $stats->total_gmv ?? 0,
                    'total_commission' => $stats->total_commission ?? 0
                ];
                
                $this->Affiliate_sync_model->save_creator_link($link_data);
                $total_links++;
            }
        } else {
            echo "  No assignments found in creator_link_assignments\n";
        }
        
        // STEP 2: Sync dari orders yang sudah ada (untuk link yang belum tercatat)
        // Ini menangani kasus di mana order sudah masuk tapi link belum didaftarkan
        $sql = "
            SELECT DISTINCT 
                o.creator_username,
                o.campaign_id,
                o.product_id,
                o.product_name,
                o.commission_rate,
                MIN(o.order_time) as first_order_date,
                COUNT(*) as total_orders,
                SUM(o.gmv) as total_gmv,
                SUM(o.actual_commission) as total_commission
            FROM affiliate_orders o
            WHERE o.creator_username IS NOT NULL 
                AND o.creator_username != ''
                AND o.order_status NOT IN ('CANCELLED', 'REFUNDED')
                AND NOT EXISTS (
                    SELECT 1 FROM affiliate_creator_links l 
                    WHERE l.creator_username = o.creator_username 
                        AND l.campaign_id = o.campaign_id 
                        AND l.product_id = o.product_id
                )
            GROUP BY o.creator_username, o.campaign_id, o.product_id, o.product_name, o.commission_rate
        ";
        
        $unassigned_links = $this->db->query($sql)->result();
        
        if (!empty($unassigned_links)) {
            echo "  Found " . count($unassigned_links) . " unassigned links from orders\n";
            
            foreach ($unassigned_links as $item) {
                $link_data = [
                    'link_id' => md5($item->creator_username . $item->campaign_id . $item->product_id),
                    'campaign_id' => $item->campaign_id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'creator_username' => $item->creator_username,
                    'affiliate_link' => 'auto_detected_from_order_' . $item->creator_username,
                    'commission_rate' => $item->commission_rate ?? 0,
                    'shared_date' => $item->first_order_date,
                    'status' => 'ACTIVE',
                    'total_orders' => $item->total_orders,
                    'total_gmv' => $item->total_gmv,
                    'total_commission' => $item->total_commission
                ];
                
                $this->Affiliate_sync_model->save_creator_link($link_data);
                $total_links++;
            }
        }
        
        // STEP 3: Update statistik untuk semua link berdasarkan order terbaru
        $this->Affiliate_sync_model->update_creator_link_stats();
        
        echo "[" . date('Y-m-d H:i:s') . "] Synced $total_links creator links\n";
        $this->Affiliate_sync_model->log_sync_end($log_id, 'success', $total_links, $total_links, 0);
        
    } catch (Exception $e) {
        $error_msg = $e->getMessage();
        echo "[" . date('Y-m-d H:i:s') . "] ERROR syncing links: $error_msg\n";
        $this->Affiliate_sync_model->log_sync_end($log_id, 'failed', 0, 0, 0, $error_msg);
        
        if ($retry_count < $this->max_retries) {
            sleep($this->retry_delays[$retry_count]);
            $this->sync_creator_links($campaign_id, $retry_count + 1);
        }
    }
}

/**
 * Sync links dari tabel assignment (jika ada)
 * Misalnya BD/Admin mencatat pembagian link ke creator di tabel terpisah
 */
private function sync_links_from_assignments() {
    // Cek apakah ada tabel assignment
    $table_exists = $this->db->table_exists('creator_link_assignments');
    
    if (!$table_exists) {
        return;
    }
    
    $assignments = $this->db->select('*')
                            ->from('creator_link_assignments')
                            ->where('status', 'ACTIVE')
                            ->get()
                            ->result();
    
    foreach ($assignments as $assign) {
        $link_data = [
            'link_id' => $assign->link_id,
            'campaign_id' => $assign->campaign_id,
            'product_id' => $assign->product_id,
            'product_name' => $assign->product_name,
            'creator_username' => $assign->creator_username,
            'affiliate_link' => $assign->affiliate_link,
            'commission_rate' => $assign->commission_rate,
            'shared_date' => $assign->shared_date,
            'expire_date' => $assign->expire_date,
            'status' => $assign->status
        ];
        
        $this->Affiliate_sync_model->save_creator_link($link_data);
    }
}
    
    /**
     * Handle token expired - try refresh or notify
     */
    private function handle_token_expired() {
        echo "  Token expired, attempting to refresh...\n";
        
        try {
            // Try to refresh token
            $token = $this->Jsm_token_model->get_latest_token_by_type(3);
            
            if ($token && $token->refresh_token_expire > time()) {
                $refresh_result = $this->refresh_affiliate_token($token);
                
                if ($refresh_result['success']) {
                    echo "  âœ“ Token refreshed successfully\n";
                    return;
                }
            }
            
            // If refresh failed, add to queue and notify
            echo "  âœ— Cannot refresh token, please re-authorize\n";
            $this->send_alert('TikTok Affiliate Token Expired', 'Please re-authorize at ' . base_url('tts/authorize_affiliate'));
            
        } catch (Exception $e) {
            echo "  âœ— Token refresh failed: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Refresh affiliate token
     */
    private function refresh_affiliate_token($token) {
        try {
            $url = "https://auth.tiktok-shops.com/api/v2/token/refresh?" . http_build_query([
                "app_key" => $this->jsm_api->get_app_key(),
                "app_secret" => $this->jsm_api->app_secret,
                "refresh_token" => $token->refresh_token,
                "grant_type" => "refresh_token"
            ]);
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false
            ]);
            
            $response = curl_exec($ch);
            $result = json_decode($response, true);
            curl_close($ch);
            
            if (isset($result['code']) && $result['code'] == 0) {
                $this->db->where('id', $token->id)->update('tts_tokens', [
                    'access_token' => $result['data']['access_token'],
                    'refresh_token' => $result['data']['refresh_token'],
                    'access_token_expire' => time() + ($result['data']['access_token_expire_in'] ?? 7200),
                    'refresh_token_expire' => time() + ($result['data']['refresh_token_expire_in'] ?? 2592000),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                return ['success' => true];
            }
            
            return ['success' => false, 'message' => $result['message'] ?? 'Unknown error'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Send alert (email/telegram/webhook)
     */
    private function send_alert($subject, $message) {
        log_message('error', "$subject: $message");
        // Implement your alert system here
    }
    
    /**
     * Get sync status (for monitoring)
     */
    public function sync_status() {
        header('Content-Type: application/json');
        
        $last_sync = $this->db->select('sync_type, status, start_time, end_time, total_success, total_failed')
                              ->from('affiliate_sync_logs')
                              ->order_by('start_time DESC')
                              ->limit(20)
                              ->get()
                              ->result();
        
        $queue_pending = $this->db->where('status', 'pending')->count_all_results('affiliate_sync_queue');
        
        $campaign_summary = $this->Affiliate_sync_model->get_campaign_summary();
        
        // Get last 7 days order count
        $last_7_days_orders = $this->db->where('order_time >=', date('Y-m-d H:i:s', strtotime('-7 days')))
                                       ->count_all_results('affiliate_orders');
        
        echo json_encode([
            'success' => true,
            'last_sync' => $last_sync,
            'queue_pending' => $queue_pending,
            'campaigns' => $campaign_summary,
            'last_7_days_orders' => $last_7_days_orders,
            'server_time' => date('Y-m-d H:i:s'),
            'timezone' => 'Asia/Jakarta'
        ], JSON_PRETTY_PRINT);
    }
    
    /**
     * Manual trigger for missing data
     */
    public function resync($type, $campaign_id = null, $days = 7) {
        if (!$this->input->is_cli_request()) {
            die('CLI only');
        }
        
        echo "Manual resync: $type\n";
        
        switch ($type) {
            case 'campaigns':
                $this->sync_campaigns();
                break;
            case 'products':
                if ($campaign_id) {
                    $this->sync_campaign_products($campaign_id);
                }
                break;
            case 'orders':
                $this->sync_orders(date('Y-m-d', strtotime("-$days days")), date('Y-m-d'));
                break;
            case 'today':
                $this->sync_today_orders();
                break;
            case 'week':
                $this->sync_last_7_days();
                break;
            case 'missing':
                $this->sync_missing_data($days);
                break;
            case 'links':
                $this->sync_creator_links($campaign_id);
                break;
            case 'all':
                $this->sync_all();
                break;
        }
        
        echo "Resync completed\n";
    }
    
    public function debug_api() {
    echo "========================================\n";
    echo "DEBUG API FROM CRON\n";
    echo "========================================\n\n";
    
    $params = [
        'create_time_ge' => strtotime('2026-04-30 00:00:00'),
        'create_time_lt' => strtotime('2026-05-01 00:00:00'),
        'page_size' => 5
    ];
    
    echo "Params: " . json_encode($params) . "\n\n";
    
    $result = $this->jsm_api->search_affiliate_orders($params);
    
    echo "Success: " . ($result['success'] ? 'Yes' : 'No') . "\n";
    
    if (!$result['success']) {
        echo "Error: " . ($result['message'] ?? 'Unknown') . "\n";
        if (isset($result['raw_response'])) {
            echo "Raw: " . json_encode($result['raw_response']) . "\n";
        }
    } else {
        echo "Total orders: " . count($result['data']) . "\n";
        if (!empty($result['data'])) {
            echo "\nFirst order keys:\n";
            $first = $result['data'][0];
            echo json_encode(array_keys($first), JSON_PRETTY_PRINT) . "\n";
        }
    }
}


public function sync_campaign_products_complete($campaign_id, $retry_count = 0) {
    try {
        echo "  Syncing complete products for campaign: $campaign_id\n";
        
        // Get products with PENDING status (need review)
        $pending_result = $this->jsm_api->get_products_by_review_status($campaign_id, 'PENDING', 100);
        if ($pending_result['success'] && !empty($pending_result['data']['products'])) {
            foreach ($pending_result['data']['products'] as $product) {
                $this->Affiliate_sync_model->save_product($product, $campaign_id);
            }
            echo "    ✓ Synced " . count($pending_result['data']['products']) . " pending products\n";
        }
        
        // Get products with APPROVED status
        $approved_result = $this->jsm_api->get_products_by_review_status($campaign_id, 'APPROVED', 100);
        if ($approved_result['success'] && !empty($approved_result['data']['products'])) {
            foreach ($approved_result['data']['products'] as $product) {
                $this->Affiliate_sync_model->save_product($product, $campaign_id);
            }
            echo "    ✓ Synced " . count($approved_result['data']['products']) . " approved products\n";
        }
        
        // Get products with REJECTED status
        $rejected_result = $this->jsm_api->get_products_by_review_status($campaign_id, 'REJECTED', 100);
        if ($rejected_result['success'] && !empty($rejected_result['data']['products'])) {
            foreach ($rejected_result['data']['products'] as $product) {
                $this->Affiliate_sync_model->save_product($product, $campaign_id);
            }
            echo "    ✓ Synced " . count($rejected_result['data']['products']) . " rejected products\n";
        }
        
    } catch (Exception $e) {
        echo "    ✗ Failed to sync complete products: " . $e->getMessage() . "\n";
        
        if ($retry_count < $this->max_retries) {
            $delay = $this->retry_delays[$retry_count];
            sleep($delay);
            $this->sync_campaign_products_complete($campaign_id, $retry_count + 1);
        }
    }
}

/**
 * Sync creator content statistics for approved products
 */
public function sync_creator_content_stats() {
    echo "[" . date('Y-m-d H:i:s') . "] Syncing creator content statistics...\n";
    
    $total = $this->Affiliate_sync_model->sync_all_creator_content_statistics();
    
    echo "[" . date('Y-m-d H:i:s') . "] Synced $total content statistics\n";
}

private function save_creator_content_stat($campaign_id, $product_id, $creator_temp_id, $stat) {
    $now = date('Y-m-d H:i:s');
    
    // Cari creator_id dari creator_temp_id
    $creator = $this->db->where('creator_hid', $creator_temp_id)->get('creators')->row();
    
    $data = [
        'campaign_id' => $campaign_id,
        'product_id' => $product_id,
        'creator_id' => $creator->id ?? null,
        'creator_username' => $creator->username ?? null,
        'creator_temp_id' => $creator_temp_id,
        'content_type' => $stat['content_type'] ?? null,
        'cover_img_url' => $stat['cover_img_url'] ?? null,
        'source_url' => $stat['source_url'] ?? null,
        'linked_tiktok_video' => $stat['linked_tiktok_video'] ?? null,
        'view_count' => intval($stat['view_count'] ?? 0),
        'like_count' => intval($stat['like_count'] ?? 0),
        'comment_count' => intval($stat['comment_num'] ?? 0),
        'paid_order_count' => intval($stat['paid_order_num'] ?? 0),
        'paid_amount' => floatval($stat['paid_amount'] ?? 0),
        'published_date' => $stat['published_date'] ?? null,
        'content_end_date' => $stat['content_end_date'] ?? null,
        'last_sync' => $now,
        'updated_at' => $now
    ];
    
    $existing = $this->db->where('campaign_id', $campaign_id)
                         ->where('product_id', $product_id)
                         ->where('creator_temp_id', $creator_temp_id)
                         ->where('content_type', $data['content_type'])
                         ->get('creator_content_statistics')
                         ->row();
    
    if ($existing) {
        $this->db->where('id', $existing->id)->update('creator_content_statistics', $data);
    } else {
        $data['created_at'] = $now;
        $this->db->insert('creator_content_statistics', $data);
    }
}
/**
 * Sync campaign creator performance (creator yang aktifkan link)
 * Called every hour
 */
public function sync_creator_performance() {
    echo "[" . date('Y-m-d H:i:s') . "] Syncing campaign creator performance...\n";
    
    // Ambil semua campaign yang aktif (ONGOING)
    $campaigns = $this->db->select('campaign_id, campaign_name')
                           ->where('status', 'ONGOING')
                           ->get('affiliate_campaigns')
                           ->result();
    
    if (empty($campaigns)) {
        echo "  No ongoing campaigns found.\n";
        return;
    }
    
    $total_synced = 0;
    $total_creators = 0;
    
    foreach ($campaigns as $campaign) {
        echo "  Processing campaign: {$campaign->campaign_name} ({$campaign->campaign_id})\n";
        
        // 🔥 PAKAI METHOD YANG SUDAH ADA DI Jsm_api
        $creators = $this->jsm_api->get_all_activated_creators($campaign->campaign_id);
        
        if (empty($creators)) {
            echo "    No activated creators found for this campaign.\n";
            continue;
        }
        
        echo "    Found " . count($creators) . " activated creators\n";
        
        foreach ($creators as $creator_data) {
            // Simpan ke tabel campaign_creator_performance
            $this->save_campaign_creator_performance($creator_data);
            $total_creators++;
        }
        
        $total_synced++;
    }
    
    echo "[" . date('Y-m-d H:i:s') . "] Synced {$total_creators} creator performance records from {$total_synced} campaigns\n";
}

/**
 * Save campaign creator performance ke database (helper untuk sync_creator_performance)
 */
private function save_campaign_creator_performance($data) {
    $now = date('Y-m-d H:i:s');
    
    // Cek apakah sudah ada
    $existing = $this->db->where('campaign_id', $data['campaign_id'])
                         ->where('product_id', $data['product_id'])
                         ->where('creator_open_id', $data['creator_open_id'])
                         ->get('campaign_creator_performance')
                         ->row();
    
    $save_data = [
        'campaign_id' => $data['campaign_id'],
        'product_id' => $data['product_id'],
        'product_name' => $data['product_name'] ?? '',
        'affiliate_product_id' => $data['affiliate_product_id'] ?? '',
        'creator_open_id' => $data['creator_open_id'],
        'creator_username' => $data['creator_username'] ?? '',
        'creator_nick_name' => $data['creator_nick_name'] ?? '',
        'creator_avatar' => $data['creator_avatar'] ?? '',
        'follower_count' => intval($data['follower_count'] ?? 0),
        'commission' => intval($data['commission'] ?? 0),
        'paid_amount' => floatval($data['paid_amount'] ?? 0),
        'video_count' => intval($data['video_count'] ?? 0),
        'room_count' => intval($data['room_count'] ?? 0),
        'free_sample_status' => $data['free_sample_status'] ?? '',
        'effective_start_time' => $data['effective_start_time'] ?? null,
        'effective_end_time' => $data['effective_end_time'] ?? null,
        'is_active' => 1,
        'last_sync' => $now,
        'updated_at' => $now
    ];
    
    if ($existing) {
        $this->db->where('id', $existing->id)->update('campaign_creator_performance', $save_data);
        // echo "      Updated creator: {$data['creator_username']}\n";
    } else {
        $save_data['created_at'] = $now;
        $this->db->insert('campaign_creator_performance', $save_data);
        // echo "      Inserted creator: {$data['creator_username']}\n";
    }
}
public function debug_order_api() {
    $today = date('Y-m-d');
    $utc_range = $this->get_utc_range_from_local_date($today, 'Asia/Jakarta');
    
    echo "Testing API for: $today\n";
    echo "UTC Range: {$utc_range['start']} - {$utc_range['end']}\n\n";
    
    $params = [
        'create_time_ge' => $utc_range['start'],
        'create_time_lt' => $utc_range['end'],
        'page_size' => 10
    ];
    
    // 🔥 PANGGIL API DAN LIHAT RESPONSE MENTAH
    $result = $this->jsm_api->search_affiliate_orders($params);
    
    echo "Success: " . ($result['success'] ? 'YES' : 'NO') . "\n";
    echo "Message: " . ($result['message'] ?? 'N/A') . "\n";
    echo "Data count: " . count($result['data'] ?? []) . "\n\n";
    
    // 🔥 TAMPILKAN RAW RESPONSE
    echo "RAW RESPONSE:\n";
    print_r($result);
    
    // 🔥 Cek token
    echo "\n\nToken check:\n";
    try {
        $token = $this->jsm_api->get_valid_token();
        echo "Token: " . substr($token, 0, 20) . "...\n";
    } catch (Exception $e) {
        echo "Token ERROR: " . $e->getMessage() . "\n";
    }
}

public function debug_order_today() {
    $today = date('Y-m-d');
    $utc_range = $this->get_utc_range_from_local_date($today, 'Asia/Jakarta');
    
    echo "========================================\n";
    echo "DEBUG ORDER - $today\n";
    echo "========================================\n\n";
    
    echo "📅 Local Date: $today (WIB)\n";
    echo "🕐 UTC Range:\n";
    echo "   Start: {$utc_range['start']} = " . date('Y-m-d H:i:s', $utc_range['start']) . " UTC\n";
    echo "   End:   {$utc_range['end']} = " . date('Y-m-d H:i:s', $utc_range['end']) . " UTC\n\n";
    
    // ==========================================
    // TEST 1: API dengan range normal
    // ==========================================
    echo "📡 TEST 1: API Call (Normal Range)\n";
    echo "----------------------------------------\n";
    
    $params = [
        'create_time_ge' => $utc_range['start'],
        'create_time_lt' => $utc_range['end'],
        'page_size' => 5
    ];
    
    $result = $this->jsm_api->search_affiliate_orders($params);
    
    echo "Success: " . ($result['success'] ? '✅ YES' : '❌ NO') . "\n";
    echo "Data count: " . count($result['data'] ?? []) . "\n";
    echo "Next page token: " . ($result['next_page_token'] ?? 'NONE') . "\n";
    echo "Total count: " . ($result['total_count'] ?? 'N/A') . "\n\n";
    
    if (!empty($result['data'])) {
        echo "First order:\n";
        print_r($result['data'][0]);
    }
    
    // ==========================================
    // TEST 2: API dengan range LEBIH LEBAR (3 hari)
    // ==========================================
    echo "\n📡 TEST 2: API Call (3 Days Range)\n";
    echo "----------------------------------------\n";
    
    $wide_start = strtotime('-3 days', strtotime($today . ' 00:00:00'));
    $wide_end = strtotime($today . ' 23:59:59');
    
    $params2 = [
        'create_time_ge' => $wide_start,
        'create_time_lt' => $wide_end,
        'page_size' => 5
    ];
    
    $result2 = $this->jsm_api->search_affiliate_orders($params2);
    
    echo "Range: " . date('Y-m-d H:i:s', $wide_start) . " → " . date('Y-m-d H:i:s', $wide_end) . "\n";
    echo "Success: " . ($result2['success'] ? '✅ YES' : '❌ NO') . "\n";
    echo "Data count: " . count($result2['data'] ?? []) . "\n\n";
    
    // ==========================================
    // TEST 3: Database
    // ==========================================
    echo "🗄️ TEST 3: Database Orders\n";
    echo "----------------------------------------\n";
    
    $db_today = $this->db->select('COUNT(*) as total, SUM(gmv) as total_gmv, MIN(order_time) as first, MAX(order_time) as last')
                         ->from('affiliate_orders')
                         ->where('order_date_local', $today)
                         ->get()
                         ->row();
    
    echo "Orders today: {$db_today->total}\n";
    echo "GMV today: Rp " . number_format($db_today->total_gmv ?? 0, 0, ',', '.') . "\n";
    echo "First order: {$db_today->first}\n";
    echo "Last order: {$db_today->last}\n\n";
    
    // ==========================================
    // TEST 4: Cek per jam
    // ==========================================
    echo "📊 TEST 4: Orders Per Hour (Today)\n";
    echo "----------------------------------------\n";
    
    $per_hour = $this->db->select('HOUR(order_time) as hour, COUNT(*) as total')
                         ->from('affiliate_orders')
                         ->where('order_date_local', $today)
                         ->group_by('HOUR(order_time)')
                         ->order_by('hour', 'ASC')
                         ->get()
                         ->result();
    
    foreach ($per_hour as $h) {
        $bar = str_repeat('█', min($h->total, 20));
        echo sprintf("  %02d:00 - %3d orders %s\n", $h->hour, $h->total, $bar);
    }
    
    // ==========================================
    // KESIMPULAN
    // ==========================================
    echo "\n========================================\n";
    echo "📋 KESIMPULAN\n";
    echo "========================================\n";
    
    if ($db_today->total > 0 && count($result['data'] ?? []) == 0) {
        echo "❌ MASALAH: Ada {$db_today->total} order di database tapi API return 0!\n";
        echo "   → TikTok API delay/bug. Data akan muncul nanti.\n";
    } elseif ($db_today->total == 0) {
        echo "✅ NORMAL: Belum ada order hari ini.\n";
    } else {
        echo "✅ NORMAL: API mengembalikan data.\n";
    }
}





}