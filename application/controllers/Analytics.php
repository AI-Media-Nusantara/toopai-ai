<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Analytics extends CI_Controller {

    public function __construct() {
        parent::__construct();
        
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
        
        $this->load->library('Jsm_api');
        $this->load->model('Jsm_token_model');
        $this->load->database();
    }

    // ========== DASHBOARD ==========
    public function bd() {
        $categories = $this->get_cached_categories();
        
        $data = [
            'title' => 'Analytics - Toopai BD',
            'active_menu' => 'analytics',
            'time_slots' => ['7D', '30D', '90D', 'CUSTOM'],
            'categories' => $categories
        ];
        
        $this->load->view('templates/header', $data);
        $this->load->view('analytics/bd_dashboard', $data);
        $this->load->view('templates/footer');
    }

    private function get_cached_categories() {
        $cache_dir = APPPATH . 'cache/';
        if (!is_dir($cache_dir)) mkdir($cache_dir, 0755, true);
        
        $cache_file = $cache_dir . 'categories.json';
        
        if (file_exists($cache_file) && (time() - filemtime($cache_file) < 86400)) {
            $cached = file_get_contents($cache_file);
            $categories = json_decode($cached, true);
            if ($categories && is_array($categories)) return $categories;
        }
        
        try {
            $result = $this->jsm_api->get_product_categories();
            if ($result['success'] && isset($result['data']['categories'])) {
                $categories = ['All'];
                foreach ($result['data']['categories'] as $cat) {
                    $cat_name = $cat['local_name'] ?? '';
                    if (!empty($cat_name) && $cat_name != 'All' && !in_array($cat_name, $categories)) {
                        $categories[] = $cat_name;
                    }
                }
                $categories = array_slice(array_unique($categories), 0, 30);
                file_put_contents($cache_file, json_encode($categories));
                return $categories;
            }
        } catch (Exception $e) {
            log_message('error', 'Failed to fetch categories: ' . $e->getMessage());
        }
        
        return ['All', 'Beauty', 'Fashion', 'Electronics', 'Home & Living', 'Food & Beverage', 'Sports', 'Toys & Hobbies'];
    }

    public function get_categories() {
        $this->output->set_content_type('application/json');
        
        try {
            $result = $this->jsm_api->get_product_categories();
            if ($result['success'] && isset($result['data']['categories'])) {
                $categories = [];
                foreach ($result['data']['categories'] as $cat) {
                    $cat_name = $cat['local_name'] ?? '';
                    if (!empty($cat_name) && strlen($cat_name) > 1) {
                        $categories[] = [
                            'id' => $cat['id'],
                            'name' => $cat_name,
                            'parent_id' => $cat['parent_id'] ?? 0,
                            'is_leaf' => $cat['is_leaf'] ?? false
                        ];
                    }
                }
                usort($categories, function($a, $b) { return strcmp($a['name'], $b['name']); });
                return $this->output->set_output(json_encode(['success' => true, 'data' => $categories, 'total' => count($categories)]));
            }
        } catch (Exception $e) {
            log_message('error', 'Error getting categories: ' . $e->getMessage());
        }
        return $this->output->set_output(json_encode(['success' => false, 'data' => [], 'total' => 0]));
    }

    private function get_latest_available_date() {
        return '2026-05-17';
    }

    // ========== 1. BESTSELLING PRODUCTS ==========
    public function get_bestselling_products() {
        $this->output->set_content_type('application/json');
        
        $time_slot = $this->input->post('time_slot') ?: '7D';
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        $category = $this->input->post('category');
        $limit = $this->input->post('limit') ?: 50;
        
        $latest_available_date = $this->get_latest_available_date();
        
        try {
            $params = ['time_slot' => $time_slot, 'page_size' => 100];
            if ($time_slot === 'CUSTOM' && $start_date && $end_date) {
                $end = ($end_date > $latest_available_date) ? $latest_available_date : $end_date;
                $start = ($start_date > $end) ? $end : $start_date;
                $params['start_date'] = $start;
                $params['end_date'] = $end;
            } else {
                $params['date'] = $latest_available_date;
            }
            
            $result = $this->jsm_api->get_bestselling_products($params);
            
            if ($result['success'] && isset($result['data']['products'])) {
                $products = [];
                foreach ($result['data']['products'] as $item) {
                    $gmv_range = $item['gmv_range'] ?? '';
                    $min_gmv = 0; $max_gmv = 0;
                    if (preg_match('/IDR([0-9.]+)~IDR([0-9.]+)/', $gmv_range, $matches)) {
                        $min_gmv = floatval($matches[1]);
                        $max_gmv = floatval($matches[2]);
                    }
                    
                    $open_commission = $item['open_collaboration_commission_rate'] ?? $item['commission_rate'] ?? 0;
                    
                    $product = [
                        'product_id' => $item['id'] ?? '',
                        'product_name' => $item['name'] ?? 'Unknown Product',
                        'shop_name' => $item['shop_name'] ?? '',
                        'open_commission' => $open_commission,
                        'gmv_display' => $max_gmv > 0 ? $max_gmv : $min_gmv,
                        'gmv_range' => $gmv_range,
                        'rank' => $item['rank'] ?? 0,
                        'category' => $item['category'] ?? 'General'
                    ];
                    
                    if ($category && $category != 'All') {
                        if (stripos($product['category'], $category) === false && 
                            stripos($product['product_name'], $category) === false) {
                            continue;
                        }
                    }
                    $products[] = $product;
                }
                
                return $this->output->set_output(json_encode([
                    'success' => true,
                    'data' => array_slice($products, 0, $limit),
                    'total' => count($products)
                ]));
            }
            
            return $this->output->set_output(json_encode(['success' => false, 'message' => 'Failed to fetch products', 'data' => []]));
        } catch (Exception $e) {
            return $this->output->set_output(json_encode(['success' => false, 'message' => $e->getMessage(), 'data' => []]));
        }
    }

    // ========== 2. BESTSELLING CREATORS ==========
    public function get_bestselling_creators() {
        $this->output->set_content_type('application/json');
        
        $time_slot = $this->input->post('time_slot') ?: '7D';
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        $limit = $this->input->post('limit') ?: 30;
        
        $latest_available_date = $this->get_latest_available_date();
        
        try {
            $params = ['time_slot' => $time_slot, 'page_size' => 100, 'author_type' => 'ALL'];
            if ($time_slot === 'CUSTOM' && $start_date && $end_date) {
                $end = ($end_date > $latest_available_date) ? $latest_available_date : $end_date;
                $start = ($start_date > $end) ? $end : $start_date;
                $params['start_date'] = $start;
                $params['end_date'] = $end;
            } else {
                $params['date'] = $latest_available_date;
            }
            
            $result = $this->jsm_api->get_bestselling_creators($params);
            
            if ($result['success'] && isset($result['data']['creators'])) {
                $creators = [];
                foreach ($result['data']['creators'] as $item) {
                    $gmv_range = $item['gmv_range'] ?? '';
                    $min_gmv = 0; $max_gmv = 0;
                    if (preg_match('/IDR([0-9.]+)~IDR([0-9.]+)/', $gmv_range, $matches)) {
                        $min_gmv = floatval($matches[1]);
                        $max_gmv = floatval($matches[2]);
                    }
                    
                    $creators[] = [
                        'open_id' => $item['open_id'] ?? '',
                        'creator_name' => $item['nick_name'] ?? $item['user_name'] ?? 'Unknown',
                        'username' => $item['user_name'] ?? '',
                        'followers' => intval($item['followers_count'] ?? 0),
                        'likes' => intval($item['likes_count'] ?? 0),
                        'gmv_display' => $max_gmv > 0 ? $max_gmv : $min_gmv,
                        'gmv_range' => $gmv_range,
                        'rank' => $item['rank'] ?? 0,
                        'avatar_url' => $item['avatar_url'] ?? ''
                    ];
                }
                
                return $this->output->set_output(json_encode([
                    'success' => true,
                    'data' => array_slice($creators, 0, $limit),
                    'total' => count($creators)
                ]));
            }
            
            return $this->output->set_output(json_encode(['success' => false, 'message' => 'Failed to fetch creators', 'data' => []]));
        } catch (Exception $e) {
            return $this->output->set_output(json_encode(['success' => false, 'message' => $e->getMessage(), 'data' => []]));
        }
    }

    // ========== 3. BESTSELLING VIDEOS ==========
    public function get_bestselling_videos() {
        $this->output->set_content_type('application/json');
        
        $time_slot = $this->input->post('time_slot') ?: '7D';
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        $limit = $this->input->post('limit') ?: 30;
        
        $latest_available_date = $this->get_latest_available_date();
        
        try {
            $params = ['time_slot' => $time_slot, 'page_size' => 100];
            if ($time_slot === 'CUSTOM' && $start_date && $end_date) {
                $end = ($end_date > $latest_available_date) ? $latest_available_date : $end_date;
                $start = ($start_date > $end) ? $end : $start_date;
                $params['start_date'] = $start;
                $params['end_date'] = $end;
            } else {
                $params['date'] = $latest_available_date;
            }
            
            $result = $this->jsm_api->get_bestselling_videos($params);
            
            if ($result['success'] && isset($result['data']['videos'])) {
                $videos = [];
                foreach ($result['data']['videos'] as $item) {
                    $gmv_range = $item['gmv_range'] ?? '';
                    $min_gmv = 0; $max_gmv = 0;
                    if (preg_match('/IDR([0-9.]+)~IDR([0-9.]+)/', $gmv_range, $matches)) {
                        $min_gmv = floatval($matches[1]);
                        $max_gmv = floatval($matches[2]);
                    }
                    
                    $videos[] = [
                        'video_id' => $item['id'] ?? '',
                        'creator_name' => $item['nick_name'] ?? '-',
                        'views' => intval($item['views'] ?? 0),
                        'likes' => intval($item['likes'] ?? 0),
                        'comments' => intval($item['comments'] ?? 0),
                        'shares' => intval($item['shares'] ?? 0),
                        'duration' => intval($item['duration'] ?? 0),
                        'publish_time' => isset($item['publish_time']) ? date('Y-m-d H:i', $item['publish_time']) : '',
                        'gmv_display' => $max_gmv > 0 ? $max_gmv : $min_gmv,
                        'gmv_range' => $gmv_range,
                        'rank' => $item['rank'] ?? 0,
                        'video_url' => "https://www.tiktok.com/@" . ($item['nick_name'] ?? '') . "/video/" . ($item['id'] ?? ''),
                        'shop_name' => $item['shop_name'] ?? '',
                        'product_infos' => $item['product_infos'] ?? []
                    ];
                }
                
                return $this->output->set_output(json_encode([
                    'success' => true,
                    'data' => array_slice($videos, 0, $limit),
                    'total' => count($videos)
                ]));
            }
            
            return $this->output->set_output(json_encode(['success' => false, 'message' => 'Failed to fetch videos', 'data' => []]));
        } catch (Exception $e) {
            return $this->output->set_output(json_encode(['success' => false, 'message' => $e->getMessage(), 'data' => []]));
        }
    }

    // ========== 4. PRODUCT DETAIL (RELASI DARI VIDEOS) ==========
public function get_product_detail() {
    $this->output->set_content_type('application/json');
    
    $product_id = $this->input->post('product_id');
    $product_name = $this->input->post('product_name');
    
    if (!$product_id) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Product ID required']));
    }
    
    $latest_available_date = $this->get_latest_available_date();
    
    try {
        // 🔥 GUNakan endpoint product performance untuk mendapatkan top_creators
        $perf_params = [
            'start_date_ge' => date('Y-m-d', strtotime('-30 days')),
            'end_date_lt' => $latest_available_date,
            'granularity' => 'ALL'
        ];
        $perf_result = $this->jsm_api->get_product_performance($product_id, $perf_params);
        
        $related_creators = [];
        if ($perf_result['success'] && isset($perf_result['data']['top_creators'])) {
            foreach ($perf_result['data']['top_creators'] as $creator) {
                $related_creators[] = [
                    'open_id' => $creator['creator_open_id'] ?? '',
                    'creator_name' => $creator['creator_name'] ?? '',
                    'gmv' => $creator['gmv']['amount'] ?? 0,
                    'items_sold' => $creator['items_sold'] ?? 0
                ];
            }
        }
        
        // 🔥 Cari video yang mengandung product ini dari videos API
        $videos_params = ['time_slot' => '30D', 'date' => $latest_available_date, 'page_size' => 100];
        $videos_result = $this->jsm_api->get_bestselling_videos($videos_params);
        
        $related_videos = [];
        if ($videos_result['success'] && isset($videos_result['data']['videos'])) {
            foreach ($videos_result['data']['videos'] as $video) {
                if (isset($video['product_infos'])) {
                    foreach ($video['product_infos'] as $pi) {
                        if ($pi['product_id'] == $product_id) {
                            $gmv_range = $video['gmv_range'] ?? '';
                            $min_gmv = 0; $max_gmv = 0;
                            if (preg_match('/IDR([0-9.]+)~IDR([0-9.]+)/', $gmv_range, $matches)) {
                                $min_gmv = floatval($matches[1]);
                                $max_gmv = floatval($matches[2]);
                            }
                            
                            $related_videos[] = [
                                'id' => $video['id'] ?? '',
                                'nick_name' => $video['nick_name'] ?? '',
                                'views' => $video['views'] ?? 0,
                                'likes' => $video['likes'] ?? 0,
                                'gmv_display' => $max_gmv > 0 ? $max_gmv : $min_gmv,
                                'duration' => $video['duration'] ?? 0,
                                'video_url' => "https://www.tiktok.com/@" . ($video['nick_name'] ?? '') . "/video/" . ($video['id'] ?? '')
                            ];
                            break;
                        }
                    }
                }
            }
        }
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'product_id' => $product_id,
            'product_name' => $product_name,
            'creators' => $related_creators,
            'videos' => $related_videos,
            'total_creators' => count($related_creators),
            'total_videos' => count($related_videos)
        ]));
    } catch (Exception $e) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => $e->getMessage()]));
    }
}
    // ========== 5. CREATOR DETAIL (RELASI DARI VIDEOS) ==========
public function get_creator_detail() {
    $this->output->set_content_type('application/json');
    
    $open_id = $this->input->post('open_id');
    $creator_name = $this->input->post('creator_name');
    
    if (!$open_id && !$creator_name) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Creator open_id or name required']));
    }
    
    try {
        // 🔥 GUNakan endpoint marketplace creators untuk mendapatkan detail creator
        $creator_data = null;
        $creator_products = [];
        $creator_videos = [];
        
        if (!empty($open_id)) {
            $detail_result = $this->jsm_api->get_creator_detail_by_id($open_id);
            if ($detail_result['success'] && isset($detail_result['data']['creator'])) {
                $creator = $detail_result['data']['creator'];
                $creator_data = [
                    'username' => $creator['username'] ?? '',
                    'nickname' => $creator['nickname'] ?? '',
                    'avatar_url' => $creator['avatar']['url'] ?? '',
                    'followers' => $creator['follower_count'] ?? 0,
                    'gmv' => $creator['gmv']['amount'] ?? 0,
                    'units_sold' => $creator['units_sold'] ?? 0,
                    'promoted_product_num' => $creator['promoted_product_num'] ?? 0
                ];
            }
        }
        
        // 🔥 Jika tidak ada open_id atau gagal, cari dari videos API
        $latest_available_date = $this->get_latest_available_date();
        $videos_params = ['time_slot' => '30D', 'date' => $latest_available_date, 'page_size' => 100];
        $videos_result = $this->jsm_api->get_bestselling_videos($videos_params);
        
        $search_name = strtolower($creator_name);
        $product_ids = [];
        
        if ($videos_result['success'] && isset($videos_result['data']['videos'])) {
            foreach ($videos_result['data']['videos'] as $video) {
                if (strtolower($video['nick_name'] ?? '') == $search_name) {
                    $gmv_range = $video['gmv_range'] ?? '';
                    $min_gmv = 0; $max_gmv = 0;
                    if (preg_match('/IDR([0-9.]+)~IDR([0-9.]+)/', $gmv_range, $matches)) {
                        $min_gmv = floatval($matches[1]);
                        $max_gmv = floatval($matches[2]);
                    }
                    
                    $creator_videos[] = [
                        'id' => $video['id'] ?? '',
                        'nick_name' => $video['nick_name'] ?? '',
                        'views' => $video['views'] ?? 0,
                        'likes' => $video['likes'] ?? 0,
                        'gmv_display' => $max_gmv > 0 ? $max_gmv : $min_gmv,
                        'duration' => $video['duration'] ?? 0,
                        'video_url' => "https://www.tiktok.com/@" . ($video['nick_name'] ?? '') . "/video/" . ($video['id'] ?? '')
                    ];
                    
                    if (isset($video['product_infos'])) {
                        foreach ($video['product_infos'] as $pi) {
                            $pid = $pi['product_id'] ?? '';
                            if (!empty($pid) && !in_array($pid, $product_ids)) {
                                $product_ids[] = $pid;
                            }
                        }
                    }
                }
            }
        }
        
        // Ambil detail produk
        if (!empty($product_ids)) {
            $products_params = ['time_slot' => '30D', 'date' => $latest_available_date, 'page_size' => 100];
            $products_result = $this->jsm_api->get_bestselling_products($products_params);
            
            if ($products_result['success'] && isset($products_result['data']['products'])) {
                foreach ($products_result['data']['products'] as $product) {
                    if (in_array($product['id'], $product_ids)) {
                        $gmv_range = $product['gmv_range'] ?? '';
                        $min_gmv = 0; $max_gmv = 0;
                        if (preg_match('/IDR([0-9.]+)~IDR([0-9.]+)/', $gmv_range, $matches)) {
                            $min_gmv = floatval($matches[1]);
                            $max_gmv = floatval($matches[2]);
                        }
                        
                        $creator_products[] = [
                            'product_id' => $product['id'] ?? '',
                            'product_name' => $product['name'] ?? '',
                            'shop_name' => $product['shop_name'] ?? '',
                            'gmv' => $max_gmv > 0 ? $max_gmv : $min_gmv
                        ];
                    }
                }
            }
        }
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'creator_name' => $creator_name,
            'open_id' => $open_id,
            'creator_detail' => $creator_data,
            'products' => $creator_products,
            'videos' => $creator_videos,
            'total_products' => count($creator_products),
            'total_videos' => count($creator_videos)
        ]));
    } catch (Exception $e) {
        return $this->output->set_output(json_encode(['success' => false, 'message' => $e->getMessage()]));
    }
}

    // ========== 6. VIDEO DETAIL (RELASI DARI PRODUCT_INFOS) ==========
    public function get_video_detail() {
        $this->output->set_content_type('application/json');
        
        $video_id = $this->input->post('video_id');
        $creator_name = $this->input->post('creator_name');
        
        if (!$video_id && !$creator_name) {
            return $this->output->set_output(json_encode(['success' => false, 'message' => 'Video ID or creator name required']));
        }
        
        $latest_available_date = $this->get_latest_available_date();
        
        try {
            // Cari video detail
            $video_detail = null;
            $videos_params = ['time_slot' => '7D', 'date' => $latest_available_date, 'page_size' => 100];
            $videos_result = $this->jsm_api->get_bestselling_videos($videos_params);
            
            if ($videos_result['success'] && isset($videos_result['data']['videos'])) {
                foreach ($videos_result['data']['videos'] as $video) {
                    if ($video_id && $video['id'] == $video_id) {
                        $video_detail = $video;
                        break;
                    } elseif ($creator_name && strtolower($video['nick_name'] ?? '') == strtolower($creator_name)) {
                        $video_detail = $video;
                        break;
                    }
                }
            }
            
            // Ambil products dari product_infos video
            $video_products = [];
            if ($video_detail && isset($video_detail['product_infos'])) {
                $products_params = ['time_slot' => '7D', 'date' => $latest_available_date, 'page_size' => 100];
                $products_result = $this->jsm_api->get_bestselling_products($products_params);
                
                if ($products_result['success'] && isset($products_result['data']['products'])) {
                    // Buat map product untuk quick lookup
                    $product_map = [];
                    foreach ($products_result['data']['products'] as $product) {
                        $product_map[$product['id']] = $product;
                    }
                    
                    foreach ($video_detail['product_infos'] as $pi) {
                        $pid = $pi['product_id'] ?? '';
                        $product = $product_map[$pid] ?? null;
                        
                        if ($product) {
                            $gmv_range = $product['gmv_range'] ?? '';
                            $min_gmv = 0; $max_gmv = 0;
                            if (preg_match('/IDR([0-9.]+)~IDR([0-9.]+)/', $gmv_range, $matches)) {
                                $min_gmv = floatval($matches[1]);
                                $max_gmv = floatval($matches[2]);
                            }
                            
                            $video_products[] = [
                                'product_id' => $pid,
                                'product_name' => $pi['product_name'] ?? $product['name'] ?? 'Unknown',
                                'shop_name' => $product['shop_name'] ?? '',
                                'gmv_display' => $max_gmv > 0 ? $max_gmv : $min_gmv
                            ];
                        } else {
                            $video_products[] = [
                                'product_id' => $pid,
                                'product_name' => $pi['product_name'] ?? 'Unknown Product',
                                'shop_name' => '',
                                'gmv_display' => 0
                            ];
                        }
                    }
                }
            }
            
            // Format video detail
            $formatted_detail = null;
            if ($video_detail) {
                $gmv_range = $video_detail['gmv_range'] ?? '';
                $min_gmv = 0; $max_gmv = 0;
                if (preg_match('/IDR([0-9.]+)~IDR([0-9.]+)/', $gmv_range, $matches)) {
                    $min_gmv = floatval($matches[1]);
                    $max_gmv = floatval($matches[2]);
                }
                
                $formatted_detail = [
                    'id' => $video_detail['id'] ?? '',
                    'nick_name' => $video_detail['nick_name'] ?? '',
                    'views' => $video_detail['views'] ?? 0,
                    'likes' => $video_detail['likes'] ?? 0,
                    'comments' => $video_detail['comments'] ?? 0,
                    'shares' => $video_detail['shares'] ?? 0,
                    'duration' => $video_detail['duration'] ?? 0,
                    'gmv_display' => $max_gmv > 0 ? $max_gmv : $min_gmv,
                    'video_url' => "https://www.tiktok.com/@" . ($video_detail['nick_name'] ?? '') . "/video/" . ($video_detail['id'] ?? ''),
                    'shop_name' => $video_detail['shop_name'] ?? ''
                ];
            }
            
            return $this->output->set_output(json_encode([
                'success' => true,
                'video_id' => $video_id,
                'creator_name' => $creator_name,
                'video_detail' => $formatted_detail,
                'products' => array_values($video_products),
                'total_products' => count($video_products)
            ]));
        } catch (Exception $e) {
            return $this->output->set_output(json_encode(['success' => false, 'message' => $e->getMessage()]));
        }
    }

    // ========== SYNC SHOP CIPHER ==========
    public function sync_shop_cipher() {
        $this->output->set_content_type('application/json');
        
        $token = $this->Jsm_token_model->get_latest_affiliate_token();
        if (!$token) {
            $token = $this->Jsm_token_model->get_latest_token_by_type(2);
        }
        
        if (!$token) {
            return $this->output->set_output(json_encode(['success' => false, 'message' => 'No token found']));
        }
        
        $result = $this->jsm_api->get_shop_detail();
        
        if ($result['success'] && isset($result['data']['shops']) && !empty($result['data']['shops'])) {
            $first_shop = $result['data']['shops'][0];
            $shop_cipher = $first_shop['cipher'] ?? '';
            
            if (!empty($shop_cipher)) {
                $this->db->where('user_type', 2)->where('tap_type', 'TOOPAI')->update('tts_tokens', ['shop_id' => $shop_cipher]);
                return $this->output->set_output(json_encode(['success' => true, 'shop_cipher' => $shop_cipher, 'message' => 'Shop cipher updated']));
            }
        }
        
        return $this->output->set_output(json_encode(['success' => false, 'message' => 'Failed to get shop cipher']));
    }

    public function get_latest_date() {
        $this->output->set_content_type('application/json');
        return $this->output->set_output(json_encode([
            'success' => true,
            'latest_available_date' => $this->get_latest_available_date(),
            'current_date' => date('Y-m-d')
        ]));
    }
    
    public function debug_creator_relasi() {
    $this->output->set_content_type('application/json');
    
    $creator_name = 'Ischa&Indy'; // Ganti dengan creator yang ingin di-test
    $latest_available_date = $this->get_latest_available_date();
    
    // 1. Ambil semua videos
    $videos_params = ['time_slot' => '7D', 'date' => $latest_available_date, 'page_size' => 100];
    $videos_result = $this->jsm_api->get_bestselling_videos($videos_params);
    
    // 2. Filter videos by creator
    $creator_videos = [];
    $product_ids = [];
    $search_name = strtolower($creator_name);
    
    if ($videos_result['success'] && isset($videos_result['data']['videos'])) {
        foreach ($videos_result['data']['videos'] as $video) {
            $video_creator = strtolower($video['nick_name'] ?? '');
            if ($video_creator == $search_name || strpos($video_creator, $search_name) !== false) {
                $creator_videos[] = $video;
                if (isset($video['product_infos'])) {
                    foreach ($video['product_infos'] as $pi) {
                        $pid = $pi['product_id'] ?? '';
                        if (!empty($pid) && !in_array($pid, $product_ids)) {
                            $product_ids[] = $pid;
                        }
                    }
                }
            }
        }
    }
    
    // 3. Ambil semua products
    $products_params = ['time_slot' => '7D', 'date' => $latest_available_date, 'page_size' => 100];
    $products_result = $this->jsm_api->get_bestselling_products($products_params);
    
    // 4. Filter products by product_ids
    $creator_products = [];
    if ($products_result['success'] && isset($products_result['data']['products'])) {
        foreach ($products_result['data']['products'] as $product) {
            if (in_array($product['id'], $product_ids)) {
                $creator_products[] = $product;
            }
        }
    }
    
    return $this->output->set_output(json_encode([
        'debug_info' => [
            'creator_name' => $creator_name,
            'search_name' => $search_name,
            'total_videos_from_api' => count($videos_result['data']['videos'] ?? [])
        ],
        'videos_found' => [
            'count' => count($creator_videos),
            'sample' => array_slice($creator_videos, 0, 3)
        ],
        'product_ids_found' => $product_ids,
        'products_found' => [
            'count' => count($creator_products),
            'sample' => array_slice($creator_products, 0, 3)
        ]
    ], JSON_PRETTY_PRINT));
}
    
    
    
}