<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Creator extends CI_Controller {
    
    private $gemini_api_keys = [
        'AIzaSyAimgbIISxJgWKV9ZM3ApIXNwT_2dFcXyo',
        'AIzaSyD1twUjCWv37oiZJT7b7SWds77eI8sGF78'
    ];
    private $current_key_index = 0;
    // 🔥 PERBAIKAN: Gunakan model Gemini 3 Flash (yang paling canggih)
    private $gemini_models = [
        'gemini-2.5-flash',      // Gemini 3 Flash (latest)
        'gemini-2.0-flash-exp'   // Fallback
    ];
    private $gemini_url = 'https://generativelanguage.googleapis.com/v1beta/models/';
    
    private $last_error = null;
    
    public function __construct() {
        parent::__construct();
        
        if (!$this->session->userdata('creator_logged_in')) {
            redirect('creator_auth/login');
        }
        
        $this->load->database();
        $this->load->helper('url');
        $this->load->library('session');
        
        $cache_dir = APPPATH . 'cache/gemini/';
        if (!is_dir($cache_dir)) mkdir($cache_dir, 0777, true);
    }
    
     private function get_next_api_key() {
        $key = $this->gemini_api_keys[$this->current_key_index];
        $this->current_key_index = ($this->current_key_index + 1) % count($this->gemini_api_keys);
        return $key;
    }
    private function get_api_usage($creator_id) {
        $today = date('Y-m-d');
        $usage_today = $this->db->where('creator_id', $creator_id)
                                ->where('DATE(created_at)', $today)
                                ->count_all_results('ai_hook_generations');
        
        $total_usage = $this->db->where('creator_id', $creator_id)
                               ->count_all_results('ai_hook_generations');
        
        $is_premium = $this->db->where('id', $creator_id)
                               ->get('creators')
                               ->row()
                               ->is_premium ?? 0;
        
        return [
            'usage_today' => $usage_today,
            'total_usage' => $total_usage,
            'is_premium' => $is_premium,
            'remaining' => $is_premium ? PHP_INT_MAX : max(0, 3 - $usage_today),
            'show_upgrade_popup' => $usage_today >= 2 && !$is_premium
        ];
    }
    public function dashboard() {
    $creator_id = $this->session->userdata('creator_id');
    $creator = $this->db->where('id', $creator_id)->get('creators')->row();
    
    // Get stats
    $stats = $this->db->select('
            COALESCE(SUM(gmv), 0) as total_gmv,
            COUNT(DISTINCT order_id) as total_orders,
            COALESCE(SUM(estimated_commission), 0) as total_commission
        ')
        ->from('affiliate_orders')
        ->where('creator_username', $creator->username)
        ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
        ->get()
        ->row();
    
    // 🔥 CAMPAIGN CARD (bukan product card) - Top Campaign by GMV
    $top_campaign = $this->db->select('c.campaign_id, c.campaign_name, c.total_gmv, COUNT(o.order_id) as total_orders')
                             ->from('affiliate_campaigns c')
                             ->join('affiliate_orders o', 'c.campaign_id = o.campaign_id', 'left')
                             ->group_by('c.campaign_id')
                             ->order_by('c.total_gmv', 'DESC')
                             ->limit(1)
                             ->get()
                             ->row();
    
    // 🔥 BEST SELLER PRODUCT (tetap untuk product card)
    $top_product = $this->db->select('p.product_id, p.product_name, p.price, p.commission_rate, p.image_url, p.sales_count, p.gmv, p.campaign_id, c.campaign_name')
                           ->from('affiliate_products p')
                           ->join('affiliate_campaigns c', 'c.campaign_id = p.campaign_id', 'left')
                           ->order_by('p.sales_count', 'DESC')
                           ->limit(1)
                           ->get()
                           ->row();
    
    // Campaign banner untuk campaign card
    $campaign_banner = '';
    $campaign_products = [];
    if ($top_campaign) {
        // Ambil gambar dari produk pertama di campaign
        $first_product = $this->db->select('image_url')
                                  ->where('campaign_id', $top_campaign->campaign_id)
                                  ->where('image_url IS NOT NULL')
                                  ->where('image_url !=', '')
                                  ->limit(1)
                                  ->get('affiliate_products')
                                  ->row();
        if ($first_product) {
            $campaign_banner = $first_product->image_url;
        }
        
        // Ambil produk untuk ditampilkan di campaign card
        $campaign_products = $this->db->select('product_name, price, image_url')
                                     ->where('campaign_id', $top_campaign->campaign_id)
                                     ->order_by('sales_count', 'DESC')
                                     ->limit(3)
                                     ->get('affiliate_products')
                                     ->result();
    }
    
    // Data untuk product card (best seller)
    $product_id = '';
    $product_name = '';
    $product_price = 0;
    $product_commission = 0;
    $product_image = '';
    $product_gmv = 0;
    $product_sales = 0;
    $campaign_id = '';
    $campaign_link_available = false;
    $campaign_affiliate_link = '';
    
    if ($top_product) {
        $product_id = $top_product->product_id;
        $product_name = $top_product->product_name;
        $product_price = $top_product->price;
        $product_commission = $top_product->commission_rate;
        $product_image = $top_product->image_url;
        $product_gmv = $top_product->gmv;
        $product_sales = $top_product->sales_count;
        $campaign_id = $top_product->campaign_id;
        
        if (!empty($campaign_id)) {
            $bd_link = $this->db->select('affiliate_link, commission_rate')
                                ->from('bd_affiliate_links')
                                ->where('product_id', $product_id)
                                ->where('campaign_id', $campaign_id)
                                ->where('status', 'ACTIVE')
                                ->order_by('created_at', 'DESC')
                                ->limit(1)
                                ->get()
                                ->row();
            
            if ($bd_link) {
                $campaign_link_available = true;
                $campaign_affiliate_link = $bd_link->affiliate_link;
                $product_commission = $bd_link->commission_rate;
            }
        }
    }
    
    // Fast campaigns
    $fast_campaigns = $this->db->select('p.product_id, p.product_name, p.commission_rate, p.image_url, p.campaign_id, p.sales_count')
                               ->from('affiliate_products p')
                               ->where('p.product_id !=', $product_id)
                               ->order_by('p.sales_count', 'DESC')
                               ->limit(6)
                               ->get()
                               ->result();
    
    foreach ($fast_campaigns as $fc) {
        $bd_link = $this->db->select('affiliate_link, commission_rate')
                            ->from('bd_affiliate_links')
                            ->where('product_id', $fc->product_id)
                            ->where('campaign_id', $fc->campaign_id)
                            ->where('status', 'ACTIVE')
                            ->limit(1)
                            ->get()
                            ->row();
        $fc->link_available = !empty($bd_link);
        $fc->affiliate_link = $bd_link->affiliate_link ?? '';
        $fc->link_commission = $bd_link->commission_rate ?? $fc->commission_rate;
    }
    
    // 🔥 CREATORS BY CATEGORY (pengganti Top Switcher)
    $creators_by_category = $this->db->select('category, COUNT(*) as total_creators, SUM(gmv) as total_gmv')
                                    ->from('creators c')
                                    ->join('affiliate_orders o', 'c.username = o.creator_username', 'left')
                                    ->where('c.status', 'ACTIVE')
                                    ->group_by('c.category')
                                    ->order_by('total_gmv', 'DESC')
                                    ->limit(5)
                                    ->get()
                                    ->result();
    
    $data = [
        'title' => 'Creator Hub - Toopai',
        'creator' => $creator,
        'total_gmv' => $stats->total_gmv,
        'total_orders' => $stats->total_orders,
        'total_commission' => $stats->total_commission,
        // Data Campaign Card
        'top_campaign' => $top_campaign,
        'campaign_banner' => $campaign_banner,
        'campaign_products' => $campaign_products,
        // Data Product Card (Best Seller)
        'product_id' => $product_id,
        'product_name' => $product_name,
        'product_price' => $product_price,
        'product_commission' => $product_commission,
        'product_image' => $product_image,
        'product_gmv' => $product_gmv,
        'product_sales' => $product_sales,
        'campaign_id' => $campaign_id,
        'campaign_link_available' => $campaign_link_available,
        'fast_campaigns' => $fast_campaigns,
        'creators_by_category' => $creators_by_category
    ];
    
    $this->load->view('creator/dashboard', $data);
}
 
    //  GENERATE LINK - Ambil dari bd_affiliate_links
   public function get_affiliate_link() {
    $this->output->set_content_type('application/json');
    
    $product_id = $this->input->post('product_id');
    $campaign_id = $this->input->post('campaign_id');
    
    // Log untuk debugging
    log_message('debug', '=== get_affiliate_link called ===');
    log_message('debug', 'POST data: ' . json_encode($this->input->post()));
    log_message('debug', 'product_id: ' . $product_id);
    log_message('debug', 'campaign_id: ' . $campaign_id);
    
    if (empty($product_id) || empty($campaign_id)) {
        return $this->output->set_output(json_encode([
            'success' => false, 
            'message' => 'Product ID and Campaign ID required',
            'available' => false,
            'debug' => ['product_id' => $product_id, 'campaign_id' => $campaign_id]
        ]));
    }
    
    // Cari link dari bd_affiliate_links
    $bd_link = $this->db->select('affiliate_link, commission_rate, created_by_name, status')
                        ->from('bd_affiliate_links')
                        ->where('product_id', $product_id)
                        ->where('campaign_id', $campaign_id)
                        ->where('status', 'ACTIVE')
                        ->order_by('created_at', 'DESC')
                        ->limit(1)
                        ->get()
                        ->row();
    
    log_message('debug', 'SQL Query: ' . $this->db->last_query());
    log_message('debug', 'BD Link found: ' . ($bd_link ? 'YES' : 'NO'));
    
    if ($bd_link) {
        return $this->output->set_output(json_encode([
            'success' => true,
            'link' => $bd_link->affiliate_link,
            'commission_rate' => $bd_link->commission_rate,
            'available' => true
        ]));
    } else {
        // Cek apakah product dan campaign ada di database
        $product_exists = $this->db->where('product_id', $product_id)
                                   ->where('campaign_id', $campaign_id)
                                   ->get('affiliate_products')
                                   ->row();
        
        $message = 'Link afiliasi belum tersedia. ';
        if (!$product_exists) {
            $message = 'Product tidak ditemukan dalam campaign ini. ';
        }
        $message .= 'Akan segera tersedia oleh tim kami.';
        
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $message,
            'available' => false,
            'debug' => [
                'product_exists' => !empty($product_exists),
                'product_id' => $product_id,
                'campaign_id' => $campaign_id
            ]
        ]));
    }
}
    
    /**
     * Call Gemini API dengan Gemini 3 Flash
     */
    private function call_gemini($prompt, $cache_key = null, $cache_ttl = 3600) {
        // Check cache
        if ($cache_key) {
            $cached = $this->get_cache($cache_key);
            if ($cached !== null) {
                return $cached;
            }
        }
        
        // Try each API key and model
        for ($attempt = 0; $attempt < count($this->gemini_api_keys); $attempt++) {
            $api_key = $this->get_next_api_key();
            
            foreach ($this->gemini_models as $model) {
                $url = $this->gemini_url . $model . ':generateContent?key=' . $api_key;
                
                $data = [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.8,
                        'maxOutputTokens' => 800,
                        'topP' => 0.95,
                        'topK' => 40
                    ]
                ];
                
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                    CURLOPT_POSTFIELDS => json_encode($data),
                    CURLOPT_TIMEOUT => 60
                ]);
                
                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($http_code === 200) {
                    $result = json_decode($response, true);
                    $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    
                    if ($text) {
                        $text = preg_replace('/```json\s*|\s*```/', '', $text);
                        if ($cache_key) {
                            $this->set_cache($cache_key, $text, $cache_ttl);
                        }
                        return $text;
                    }
                }
            }
        }
        
        return null;
    }
    
    
    /**
     * Parse JSON response dengan aman
     */
   private function parse_gemini_json_response($response, $default = []) {
        if (empty($response) || !is_string($response)) {
            return $default;
        }
        
        if (preg_match('/\[[\s\S]*\]/', $response, $matches)) {
            $parsed = json_decode($matches[0], true);
            if (is_array($parsed) && !empty($parsed)) {
                return $parsed;
            }
        }
        
        $parsed = json_decode($response, true);
        if (is_array($parsed) && !empty($parsed)) {
            return $parsed;
        }
        
        return $default;
    }
    
    private function get_cache($key) {
        $cache_dir = APPPATH . 'cache/gemini/';
        $cache_file = $cache_dir . md5($key) . '.json';
        if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 3600) {
            $data = json_decode(file_get_contents($cache_file), true);
            return $data['data'] ?? null;
        }
        return null;
    }
    
    private function set_cache($key, $data, $ttl = 3600) {
        $cache_dir = APPPATH . 'cache/gemini/';
        if (!is_dir($cache_dir)) mkdir($cache_dir, 0777, true);
        $cache_file = $cache_dir . md5($key) . '.json';
        file_put_contents($cache_file, json_encode([
            'data' => $data,
            'expires' => time() + $ttl
        ]));
    }
    
    /**
     * Get trending music dengan play link dari YouTube
     */
     public function get_trending_audio() {
        $this->output->set_content_type('application/json');
        
        $creator_id = $this->session->userdata('creator_id');
        $usage = $this->get_api_usage($creator_id);
        
        $prompt = "Berikan 3 lagu yang paling viral di TikTok Indonesia saat ini. 
Format response: [{\"title\": \"Judul Lagu - Artist\", \"usage\": \"1.2M\", \"trending_level\": \"🔥\"}]
Pastikan response berupa JSON array yang valid.";
        
        $result = $this->call_gemini($prompt, 'trending_audio_' . date('Y-m-d'), 43200);
        $audio = $this->parse_gemini_json_response($result, []);
        
        // Fallback data yang lebih segar
        $fallback_audio = [
            ['title' => 'Kicau Mania - Remix / Gas Pol Ndangak', 'usage' => '1.2M', 'trending_level' => '🔥'],
            ['title' => 'Tak Segampang Itu - Anggi Marito', 'usage' => '892K', 'trending_level' => '🔥'],
            ['title' => 'Komang - Raim Laode', 'usage' => '654K', 'trending_level' => '⭐'],
            ['title' => 'Janji Manis - Masdo', 'usage' => '521K', 'trending_level' => '⭐']
        ];
        
        if (empty($audio)) {
            $audio = $fallback_audio;
        }
        
        foreach ($audio as &$song) {
            $search_query = urlencode($song['title'] . ' TikTok');
            $song['youtube_search'] = "https://www.youtube.com/results?search_query=" . $search_query;
        }
        
        echo json_encode([
            'success' => true, 
            'audio' => $audio,
            'show_upgrade_popup' => $usage['show_upgrade_popup']
        ]);
    }
    
    /**
     * Generate AI Hook
     */
    public function generate_hook() {
        $this->output->set_content_type('application/json');
        
        $creator_id = $this->session->userdata('creator_id');
        $product_name = $this->input->post('product_name');
        $category = $this->input->post('category');
        
        if (empty($product_name)) {
            $product_name = "produk ini";
        }
        
        $usage = $this->get_api_usage($creator_id);
        
        // 🔥 POPUP UPGRADE: Generate ke-2
        if ($usage['show_upgrade_popup'] && !$usage['is_premium']) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'show_upgrade_popup' => true,
                'message' => '🎉 YUK VERIFIKASI AKUN KAMU! 🎉',
                'sub_message' => 'Kamu sudah menggunakan 2 dari 3 hook gratis hari ini!',
                'upgrade_message' => '🚀 Upgrade ke VIP sekarang dan dapatkan:',
                'benefits' => [
                    '✨ Unlimited AI Hook Generator',
                    '🔥 Akses semua trending music',
                    '📊 Insight produk eksklusif',
                    '💰 GMV 3x lebih besar!'
                ],
                'gmv_promise' => 'Rata-rata creator VIP mendapatkan GMV 3-5x lipat!',
                'upgrade_url' => base_url('creator/upgrade')
            ]));
        }
        
        // Jika quota habis
        if ($usage['remaining'] <= 0 && !$usage['is_premium']) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'error' => 'QUOTA_EXCEEDED',
                'message' => '⚠️ QUOTA HABIS! ⚠️',
                'sub_message' => 'Kamu sudah menggunakan 3 hook gratis hari ini.',
                'upgrade_message' => '💎 Daftar VIP sekarang untuk hook unlimited!',
                'upgrade_url' => base_url('creator/upgrade')
            ]));
        }
        
        $prompt = "Buatkan 3 kalimat hook TikTok untuk produk '$product_name' di kategori '$category'. 
Hook harus engaging, membuat penonton penasaran, dan menggunakan gaya bahasa anak muda Indonesia.
Format response: JSON array of strings. Contoh: [\"Hook 1\", \"Hook 2\", \"Hook 3\"]";
        
        $result = $this->call_gemini($prompt, 'hook_' . md5($product_name . $category . date('Y-m-d')), 7200);
        $hooks = $this->parse_gemini_json_response($result, []);
        
        // Fallback hooks yang lebih menarik
        if (empty($hooks)) {
            $hooks = [
                "⚠️ WAJIB NONTON! Baru tau produk ini sekarang, nyesel banget 😭",
                "Review jujur! Ini dia produk yang lagi viral di TikTok ✨",
                "Yang lagi cari solusi untuk masalahmu, wajib simak ini!"
            ];
        }
        
        // Record usage
        $this->db->insert('ai_hook_generations', [
            'creator_id' => $creator_id,
            'product_name' => $product_name,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        echo json_encode([
            'success' => true,
            'hooks' => $hooks,
            'remaining' => $usage['remaining'] - 1,
            'total_used' => $usage['usage_today'] + 1,
            'daily_limit' => 3,
            'show_upgrade_popup' => ($usage['usage_today'] + 1) >= 2 && !$usage['is_premium']
        ]);
    }
    
    private function check_hook_quota($creator_id) {
        $today = date('Y-m-d');
        $usage_today = $this->db->where('creator_id', $creator_id)
                                ->where('DATE(created_at)', $today)
                                ->count_all_results('ai_hook_generations');
        
        $is_premium = $this->db->where('id', $creator_id)
                               ->get('creators')
                               ->row()
                               ->is_premium ?? 0;
        
        $daily_limit = $is_premium ? 20 : 3;
        
        return [
            'can_generate' => $usage_today < $daily_limit,
            'remaining_today' => $daily_limit - $usage_today,
            'daily_limit' => $daily_limit,
            'is_premium' => $is_premium,
            'usage_today' => $usage_today
        ];
    }
    
    private function record_hook_generation($creator_id, $product_name) {
        $this->db->insert('ai_hook_generations', [
            'creator_id' => $creator_id,
            'product_name' => $product_name,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Campaign page
     */
    public function campaigns() {
        $creator_id = $this->session->userdata('creator_id');
        $creator = $this->db->where('id', $creator_id)->get('creators')->row();
        
        $campaign = $this->db->select('campaign_id, campaign_name, status')
                             ->order_by('created_at', 'DESC')
                             ->limit(1)
                             ->get('affiliate_campaigns')
                             ->row();
        
        $product_id = '';
        $campaign_id = '';
        $campaign_banner = '';
        $campaign_gmv = 0;
        $campaign_orders = 0;
        $campaign_commission = 40;
        $campaign_link_available = false;
        $product_name = '';
        
        if ($campaign) {
            $campaign_id = $campaign->campaign_id;
            $campaign_gmv = $this->db->select('COALESCE(SUM(gmv), 0) as total')
                                      ->from('affiliate_orders')
                                      ->where('campaign_id', $campaign_id)
                                      ->get()
                                      ->row()
                                      ->total ?? 0;
            
            $campaign_orders = $this->db->where('campaign_id', $campaign_id)
                                        ->count_all_results('affiliate_orders');
            
            $product = $this->db->select('product_id, product_name, commission_rate, image_url')
                                ->where('campaign_id', $campaign_id)
                                ->limit(1)
                                ->get('affiliate_products')
                                ->row();
            
            if ($product) {
                $product_id = $product->product_id;
                $product_name = $product->product_name;
                $campaign_commission = $product->commission_rate;
                $campaign_banner = $product->image_url;
                
                $bd_link = $this->db->select('affiliate_link')
                                    ->from('bd_affiliate_links')
                                    ->where('product_id', $product_id)
                                    ->where('campaign_id', $campaign_id)
                                    ->where('status', 'ACTIVE')
                                    ->limit(1)
                                    ->get()
                                    ->row();
                $campaign_link_available = !empty($bd_link);
            }
        }
        
        $category = $creator->category ?? 'Lifestyle';
        
        // 🔥 AI INSIGHT - Real dari Gemini
        $insight_prompt = "Berikan insight 1 kalimat mengapa produk '$product_name' di kategori '$category' cocok dipromosikan sekarang. Bahasa Indonesia, engaging, pakai emoji. Maksimal 100 karakter.";
        $ai_insight = $this->call_gemini($insight_prompt, 'insight_' . md5($product_name . date('Y-m-d')), 43200, false);
        if (!$ai_insight || strlen($ai_insight) < 10) {
            $ai_insight = "✨ Produk ini sedang viral! Waktunya kamu ikutan trend sekarang juga! 🔥";
        }
        
        // 🔥 TRENDING TAGS
        $tags_prompt = "Berikan 5 hashtag TikTok yang viral untuk konten '$category'. Format: JSON array of strings. Contoh: [\"#FYP\", \"#Viral\"]";
        $tags_result = $this->call_gemini($tags_prompt, 'trending_tags_' . $category . '_' . date('Y-m-d'), 43200);
        $trending_tags = $this->parse_gemini_json_response($tags_result, ['#FYP', '#Viral', '#TikTokIndonesia', '#Reels', '#Trending']);
        
        // 🔥 TRENDING MUSIC
        $music_prompt = "Berikan 4 lagu viral TikTok Indonesia saat ini (format: Judul - Artist). Format: JSON array of strings.";
        $music_result = $this->call_gemini($music_prompt, 'trending_music_' . date('Y-m-d'), 43200);
        $trending_music = $this->parse_gemini_json_response($music_result, [
            'Kicau Mania - Remix / Gas Pol Ndangak', 'Tak Segampang Itu - Anggi Marito', 
            'Komang - Raim Laode', 'Janji Manis - Masdo'
        ]);
        
        // 🔥 VIRAL HOOKS
        $hooks_prompt = "Buatkan 4 hook TikTok untuk produk '$product_name'. Format: JSON array of strings.";
        $hooks_result = $this->call_gemini($hooks_prompt, 'viral_hooks_' . md5($product_name . date('Y-m-d')), 86400);
        $viral_hooks = $this->parse_gemini_json_response($hooks_result, [
            "⚠️ WAJIB NONTON! Baru tau produk ini sekarang, nyesel banget 😭",
            "Review jujur! Ini dia produk yang lagi viral di TikTok ✨",
            "Yang lagi cari solusi, wajib simak ini!",
            "Dijamin cocok! Holy grail versi aku 💖"
        ]);
        
        // Hook quota
        $hook_usage_today = $this->db->where('creator_id', $creator_id)
                                     ->where('DATE(created_at)', date('Y-m-d'))
                                     ->count_all_results('ai_hook_generations');
        
        $is_premium = $creator->is_premium ?? 0;
        $hook_daily_limit = $is_premium ? 20 : 3;
        
        $data = [
            'title' => 'Campaign - Toopai Creator',
            'creator' => $creator,
            'campaign' => $campaign,
            'product_id' => $product_id,
            'product_name' => $product_name,
            'campaign_id' => $campaign_id,
            'campaign_banner' => $campaign_banner,
            'campaign_gmv' => $campaign_gmv,
            'campaign_orders' => $campaign_orders,
            'campaign_commission' => $campaign_commission,
            'campaign_link_available' => $campaign_link_available,
            'ai_insight' => $ai_insight,
            'trending_tags' => $trending_tags,
            'trending_music' => $trending_music,
            'viral_hooks' => $viral_hooks,
            'hook_usage_today' => $hook_usage_today,
            'hook_daily_limit' => $hook_daily_limit,
            'is_premium' => $is_premium
        ];
        
        $this->load->view('creator/campaigns', $data);
    }
    
public function leaderboard() {
    $creator_id = $this->session->userdata('creator_id');
    $creator = $this->db->where('id', $creator_id)->get('creators')->row();
    
    // 🔥 QUERY YANG BENAR - Langsung dari affiliate_orders
    $sql = "SELECT 
                creator_username, 
                SUM(gmv) as total_gmv, 
                COUNT(DISTINCT order_id) as total_orders
            FROM affiliate_orders 
            WHERE order_status NOT IN ('CANCELLED', 'REFUNDED')
                AND creator_username IS NOT NULL 
                AND creator_username != ''
            GROUP BY creator_username 
            ORDER BY total_gmv DESC 
            LIMIT 50";
    
    $top_gmv = $this->db->query($sql)->result();
    
    // Debug: log untuk memastikan
    log_message('debug', '=== LEADERBOARD DATA ===');
    foreach ($top_gmv as $idx => $c) {
        log_message('debug', ($idx+1) . '. ' . $c->creator_username . ' - Rp ' . $c->total_gmv);
    }
    
    // Get current creator's rank and GMV
    $current_rank = 0;
    $current_gmv = 0;
    $first_gmv = !empty($top_gmv) ? $top_gmv[0]->total_gmv : 0;
    $second_gmv = !empty($top_gmv) && isset($top_gmv[1]) ? $top_gmv[1]->total_gmv : 0;
    $third_gmv = !empty($top_gmv) && isset($top_gmv[2]) ? $top_gmv[2]->total_gmv : 0;
    
    // Cari rank creator saat ini
    foreach ($top_gmv as $index => $c) {
        if ($c->creator_username == $creator->username) {
            $current_rank = $index + 1;
            $current_gmv = $c->total_gmv;
            break;
        }
    }
    
    // Jika tidak ada di top 50, cari dari semua data
    if ($current_rank == 0) {
        $all_sql = "SELECT 
                        creator_username, 
                        SUM(gmv) as total_gmv
                    FROM affiliate_orders 
                    WHERE order_status NOT IN ('CANCELLED', 'REFUNDED')
                        AND creator_username IS NOT NULL 
                        AND creator_username != ''
                    GROUP BY creator_username 
                    ORDER BY total_gmv DESC";
        
        $all_creators = $this->db->query($all_sql)->result();
        
        foreach ($all_creators as $index => $c) {
            if ($c->creator_username == $creator->username) {
                $current_rank = $index + 1;
                $current_gmv = $c->total_gmv;
                break;
            }
        }
    }
    
    $gap_to_third = $third_gmv - $current_gmv;
    if ($gap_to_third < 0) $gap_to_third = 0;
    
    // Progress percentage to top 3
    $progress_percent = $third_gmv > 0 ? min(100, ($current_gmv / $third_gmv) * 100) : 0;
    
    // GMV 30 hari terakhir untuk your rank card
    $thirty_days_ago = date('Y-m-d', strtotime('-30 days'));
    $current_gmv_30d = $this->db->select('COALESCE(SUM(gmv), 0) as total')
                                 ->from('affiliate_orders')
                                 ->where('creator_username', $creator->username)
                                 ->where('order_date_local >=', $thirty_days_ago)
                                 ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
                                 ->get()
                                 ->row()
                                 ->total ?? 0;
    
    $data = [
        'title' => 'Leaderboard - Toopai Creator',
        'creator' => $creator,
        'top_gmv' => $top_gmv,
        'current_rank' => $current_rank,
        'current_gmv' => $current_gmv,
        'current_gmv_30d' => $current_gmv_30d,
        'first_gmv' => $first_gmv,
        'second_gmv' => $second_gmv,
        'third_gmv' => $third_gmv,
        'gap_to_third' => $gap_to_third,
        'progress_percent' => $progress_percent
    ];
    
    $this->load->view('creator/leaderboard', $data);
}

   public function get_leaderboard_data($type = 'today') {
    $this->output->set_content_type('application/json');
    
    if ($type == 'today') {
        $date_filter = date('Y-m-d');
        $label = 'Hari Ini';
    } elseif ($type == 'week') {
        $date_filter = date('Y-m-d', strtotime('-7 days'));
        $label = '7 Hari';
    } elseif ($type == 'month') {
        $date_filter = date('Y-m-d', strtotime('-30 days'));
        $label = '30 Hari';
    } else {
        $date_filter = null;
        $label = 'Semua Waktu';
    }
    
    $query = $this->db->select('creator_username, SUM(gmv) as total_gmv, COUNT(DISTINCT order_id) as total_orders')
                       ->from('affiliate_orders')
                       ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
                       ->where('creator_username IS NOT NULL')
                       ->where('creator_username !=', '');
    
    if ($date_filter) {
        if ($type == 'today') {
            $query->where('order_date_local', $date_filter);
        } else {
            $query->where('order_date_local >=', $date_filter);
        }
    }
    
    $top_gmv = $query->group_by('creator_username')
                     ->order_by('total_gmv', 'DESC')
                     ->limit(20)
                     ->get()
                     ->result();
    
    // Get top switcher (creators with most campaigns)
    $top_switcher = $this->db->select('creator_username, COUNT(DISTINCT campaign_id) as campaign_count, SUM(gmv) as total_gmv')
                             ->from('affiliate_orders')
                             ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
                             ->where('creator_username IS NOT NULL')
                             ->where('creator_username !=', '')
                             ->group_by('creator_username')
                             ->order_by('campaign_count', 'DESC')
                             ->limit(5)
                             ->get()
                             ->result();
    
    echo json_encode([
        'success' => true,
        'top_gmv' => $top_gmv,
        'top_switcher' => $top_switcher,
        'label' => $label
    ]);
}

    
    public function profile() {
        $creator_id = $this->session->userdata('creator_id');
        $creator = $this->db->where('id', $creator_id)->get('creators')->row();
        
        // Hitung progress ke VIP
        $total_campaigns = $this->db->where('creator_username', $creator->username)
                                    ->count_all_results('affiliate_creator_links');
        
        $total_proof = $this->db->where('creator_username', $creator->username)
                                ->count_all_results('affiliate_orders'); // count orders as proof
        
        $total_gmv = $this->db->select('COALESCE(SUM(gmv), 0) as total')
                              ->from('affiliate_orders')
                              ->where('creator_username', $creator->username)
                              ->get()
                              ->row()
                              ->total ?? 0;
        
        $active_campaigns = $this->db->where('creator_username', $creator->username)
                                     ->count_all_results('affiliate_creator_links');
        
        // Target VIP
        $target_campaigns = 3;
        $target_proof = 2;
        $target_gmv = 1000000;
        $target_active_campaigns = 3;
        
        $check_campaigns = $total_campaigns >= $target_campaigns;
        $check_proof = $total_proof >= $target_proof;
        $check_gmv = $total_gmv >= $target_gmv;
        $check_campaign_active = $active_campaigns >= $target_active_campaigns;
        
        $completed = ($check_campaigns ? 1 : 0) + ($check_proof ? 1 : 0) + ($check_gmv ? 1 : 0) + ($check_campaign_active ? 1 : 0);
        $progress_percent = round(($completed / 4) * 100);
        
        $gmv_remaining = $target_gmv - $total_gmv;
        if ($gmv_remaining < 0) $gmv_remaining = 0;
        
        $campaign_remaining = $target_active_campaigns - $active_campaigns;
        if ($campaign_remaining < 0) $campaign_remaining = 0;
        
        $data = [
            'title' => 'Profile - Toopai Creator',
            'creator' => $creator,
            'progress_percent' => $progress_percent,
            'check_campaigns' => $check_campaigns,
            'check_proof' => $check_proof,
            'check_gmv' => $check_gmv,
            'check_campaign_active' => $check_campaign_active,
            'gmv_remaining' => $gmv_remaining,
            'campaign_remaining' => $campaign_remaining
        ];
        
        $this->load->view('creator/profile', $data);
    }
    
    public function generate_link($campaign_id) {
        $this->output->set_content_type('application/json');
        
        $creator_id = $this->session->userdata('creator_id');
        $creator = $this->db->where('id', $creator_id)->get('creators')->row();
        
        // Get product from campaign
        $product = $this->db->select('product_id')
                            ->where('campaign_id', $campaign_id)
                            ->limit(1)
                            ->get('affiliate_products')
                            ->row();
        
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'No product found']);
            return;
        }
        
        // Generate demo link
        $link = 'https://toopai.com/affiliate/' . $campaign_id . '/' . $creator->username;
        
        echo json_encode(['success' => true, 'link' => $link]);
    }
    
    public function logout() {
        $this->session->sess_destroy();
        redirect('creator_auth/login');
    }
    
    // ========== GANTI PASSWORD ==========
public function change_password() {
    $this->output->set_content_type('application/json');
    
    $creator_id = $this->session->userdata('creator_id');
    $current_password = $this->input->post('current_password');
    $new_password = $this->input->post('new_password');
    $confirm_password = $this->input->post('confirm_password');
    
    if (!$current_password || !$new_password || !$confirm_password) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Semua field harus diisi'
        ]));
    }
    
    if ($new_password !== $confirm_password) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Password baru tidak sama'
        ]));
    }
    
    if (strlen($new_password) < 6) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Password minimal 6 karakter'
        ]));
    }
    
    // Cek password lama
    $creator = $this->db->where('id', $creator_id)->get('creators')->row();
    
    if (!password_verify($current_password, $creator->password)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Password saat ini salah'
        ]));
    }
    
    // Update password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $this->db->where('id', $creator_id)->update('creators', [
        'password' => $hashed_password,
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    // Log activity
    $this->load->model('User_log_model');
    $this->User_log_model->log(
        $creator_id,
        $creator->username,
        'CREATOR',
        'CHANGE_PASSWORD',
        'Creator changed password'
    );
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'message' => 'Password berhasil diubah'
    ]));
}


// ========== TIKTOK AUTHORIZATION METHODS ==========

/**
 * Halaman authorize TikTok untuk creator
 * URL: /creator/authorize_tiktok
 */
public function authorize_tiktok() {
    $creator_id = $this->session->userdata('creator_id');
    $creator = $this->db->where('id', $creator_id)->get('creators')->row();
    
    if (!$creator) {
        redirect('creator_auth/login');
    }
    
    $data = [
        'title' => 'Authorize TikTok - Toopai Creator',
        'creator' => $creator,
        'has_token' => !empty($creator->tiktok_access_token),
        'token_expire' => $creator->tiktok_token_expire,
        'is_expired' => (!empty($creator->tiktok_token_expire) && strtotime($creator->tiktok_token_expire) < time())
    ];
    
    $this->load->view('creator/authorize_tiktok', $data);
}

/**
 * Proses authorize TikTok (redirect ke TikTok OAuth)
 * URL: /creator/do_authorize_tiktok
 */
public function do_authorize_tiktok() {
    $creator_id = $this->session->userdata('creator_id');
    $creator = $this->db->where('id', $creator_id)->get('creators')->row();
    
    if (!$creator) {
        redirect('creator_auth/login');
    }
    
    // Generate state untuk keamanan
    $state = bin2hex(random_bytes(16));
    $this->session->set_userdata('tiktok_auth_state', $state);
    
    // Redirect URI untuk callback
    $redirect_uri = base_url('creator/tiktok_callback');
    
    // Scope untuk creator
    $scope = urlencode('user.info.basic');
    
    // 🔥 Gunakan Client Key dari konfigurasi
    $client_key = $this->config->item('tiktok_client_key') ?: '6jo4rjnr8ouc9';
    
    // URL authorize TikTok
    $auth_url = "https://www.tiktok.com/auth/authorize/"
              . "?client_key=" . $client_key
              . "&response_type=code"
              . "&scope=" . $scope
              . "&redirect_uri=" . urlencode($redirect_uri)
              . "&state=" . $state;
    
    log_message('debug', 'Redirecting to TikTok: ' . $auth_url);
    redirect($auth_url);
}

/**
 * Callback dari TikTok setelah authorize
 * URL: /creator/tiktok_callback
 */
public function tiktok_callback() {
    log_message('debug', 'TikTok callback called');
    
    $code = $this->input->get('code');
    $state = $this->input->get('state');
    $error = $this->input->get('error');
    $error_description = $this->input->get('error_description');
    
    $session_state = $this->session->userdata('tiktok_auth_state');
    
    if (!empty($error)) {
        log_message('error', 'TikTok auth error: ' . $error . ' - ' . $error_description);
        $this->session->set_flashdata('error', 'TikTok authorization failed: ' . $error_description);
        redirect('creator/authorize_tiktok');
    }
    
    if ($state !== $session_state) {
        log_message('error', 'State mismatch: ' . $state . ' vs ' . $session_state);
        $this->session->set_flashdata('error', 'Invalid state parameter');
        redirect('creator/authorize_tiktok');
    }
    
    if (empty($code)) {
        $this->session->set_flashdata('error', 'Authorization code missing');
        redirect('creator/authorize_tiktok');
    }
    
    // Exchange code untuk access token
    $client_key = $this->config->item('tiktok_client_key') ?: '6jo4rjnr8ouc9';
    $client_secret = $this->config->item('tiktok_secret') ?: '8ceeae7a9ba3726cb9f2e9d831020c91fad4d99c';
    $redirect_uri = base_url('creator/tiktok_callback');
    
    $token_url = "https://open.tiktokapis.com/v2/oauth/token/";
    
    $post_fields = [
        'client_key' => $client_key,
        'client_secret' => $client_secret,
        'code' => $code,
        'grant_type' => 'authorization_code',
        'redirect_uri' => $redirect_uri
    ];
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $token_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_POSTFIELDS => http_build_query($post_fields),
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    log_message('debug', 'TikTok token response HTTP: ' . $http_code);
    log_message('debug', 'TikTok token response body: ' . substr($response, 0, 500));
    
    $result = json_decode($response, true);
    
    if ($http_code !== 200 || isset($result['error'])) {
        $error_msg = $result['error_description'] ?? $result['error'] ?? 'Unknown error';
        $this->session->set_flashdata('error', 'Failed to get access token: ' . $error_msg);
        redirect('creator/authorize_tiktok');
    }
    
    $access_token = $result['access_token'] ?? $result['data']['access_token'] ?? null;
    $refresh_token = $result['refresh_token'] ?? $result['data']['refresh_token'] ?? null;
    $expires_in = $result['expires_in'] ?? $result['data']['expires_in'] ?? 86400;
    
    if (empty($access_token)) {
        $this->session->set_flashdata('error', 'No access token received');
        redirect('creator/authorize_tiktok');
    }
    
    // Get user info
    $user_info = $this->get_tiktok_user_info($access_token);
    
    $creator_id = $this->session->userdata('creator_id');
    
    if ($user_info) {
        $this->db->where('id', $creator_id);
        $this->db->update('creators', [
            'tiktok_access_token' => $access_token,
            'tiktok_refresh_token' => $refresh_token,
            'tiktok_token_expire' => date('Y-m-d H:i:s', time() + $expires_in),
            'tiktok_open_id' => $user_info['open_id'],
            'tiktok_avatar' => $user_info['avatar_url'],
            'tiktok_display_name' => $user_info['display_name'],
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    } else {
        $this->db->where('id', $creator_id);
        $this->db->update('creators', [
            'tiktok_access_token' => $access_token,
            'tiktok_refresh_token' => $refresh_token,
            'tiktok_token_expire' => date('Y-m-d H:i:s', time() + $expires_in),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    $this->session->set_flashdata('success', 'TikTok account authorized successfully!');
    redirect('creator/authorize_tiktok');
}

/**
 * Get TikTok user info
 */
private function get_tiktok_user_info($access_token) {
    $url = 'https://open.tiktokapis.com/v2/user/info/?fields=open_id,union_id,avatar_url,display_name,username';
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json'
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        log_message('error', 'Failed to get user info: HTTP ' . $http_code);
        return null;
    }
    
    $result = json_decode($response, true);
    
    if (isset($result['data']['user'])) {
        return $result['data']['user'];
    }
    
    return null;
}

/**
 * Refresh TikTok token
 */
public function refresh_tiktok_token() {
    $this->output->set_content_type('application/json');
    
    $creator_id = $this->session->userdata('creator_id');
    $creator = $this->db->where('id', $creator_id)->get('creators')->row();
    
    if (!$creator || empty($creator->tiktok_refresh_token)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'No refresh token available'
        ]));
    }
    
    $client_key = $this->config->item('tiktok_client_key') ?: '6jo4rjnr8ouc9';
    $client_secret = $this->config->item('tiktok_secret') ?: '8ceeae7a9ba3726cb9f2e9d831020c91fad4d99c';
    
    $token_url = "https://open.tiktokapis.com/v2/oauth/token/";
    
    $post_data = [
        'client_key' => $client_key,
        'client_secret' => $client_secret,
        'refresh_token' => $creator->tiktok_refresh_token,
        'grant_type' => 'refresh_token'
    ];
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $token_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($post_data)
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if ($http_code == 200 && isset($result['access_token'])) {
        $expires_in = $result['expires_in'] ?? 86400;
        
        $this->db->where('id', $creator_id);
        $this->db->update('creators', [
            'tiktok_access_token' => $result['access_token'],
            'tiktok_token_expire' => date('Y-m-d H:i:s', time() + $expires_in),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'expires_at' => date('Y-m-d H:i:s', time() + $expires_in)
        ]));
    }
    
    return $this->output->set_output(json_encode([
        'success' => false,
        'message' => $result['error'] ?? 'Failed to refresh token'
    ]));
}

/**
 * Revoke TikTok token
 */
public function revoke_tiktok_token() {
    $this->output->set_content_type('application/json');
    
    $creator_id = $this->session->userdata('creator_id');
    $creator = $this->db->where('id', $creator_id)->get('creators')->row();
    
    if (!$creator || empty($creator->tiktok_access_token)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'No token to revoke'
        ]));
    }
    
    // Clear tokens from database
    $this->db->where('id', $creator_id);
    $this->db->update('creators', [
        'tiktok_access_token' => null,
        'tiktok_refresh_token' => null,
        'tiktok_token_expire' => null,
        'tiktok_open_id' => null,
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    return $this->output->set_output(json_encode([
        'success' => true,
        'message' => 'TikTok token revoked successfully'
    ]));
}

/**
 * Get TikTok user info (AJAX)
 */
public function get_tiktok_user_info_ajax() {
    $this->output->set_content_type('application/json');
    
    $creator_id = $this->session->userdata('creator_id');
    $creator = $this->db->where('id', $creator_id)->get('creators')->row();
    
    if (!$creator || empty($creator->tiktok_access_token)) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Not authorized'
        ]));
    }
    
    // Cek token expiry
    if (!empty($creator->tiktok_token_expire) && strtotime($creator->tiktok_token_expire) < time()) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Token expired',
            'expired' => true
        ]));
    }
    
    $user_info = $this->get_tiktok_user_info($creator->tiktok_access_token);
    
    if ($user_info) {
        return $this->output->set_output(json_encode([
            'success' => true,
            'data' => [
                'username' => $user_info['username'] ?? null,
                'display_name' => $user_info['display_name'] ?? null,
                'avatar_url' => $user_info['avatar_url'] ?? null,
                'follower_count' => $user_info['follower_count'] ?? 0
            ]
        ]));
    }
    
    return $this->output->set_output(json_encode([
        'success' => false,
        'message' => 'Failed to get user info'
    ]));
}
    
    
    
    
}