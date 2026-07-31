<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Performance extends CI_Controller {

    public function __construct() {
        parent::__construct();
        
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
        
        if ($this->session->userdata('role') != 'IS') {
            show_error('Access denied. IS only area.', 403);
        }
        
        $this->load->library('Jsm_api');
        $this->load->model('Jsm_token_model');
        $this->load->database();
    }

    // ========== MAIN PAGE ==========
    public function index() {
        $data = [
            'title' => 'Performance - Toopai IS',
            'active_menu' => 'performance',
            'time_slots' => ['7D', '30D', '90D', 'CUSTOM']
        ];
        
        $this->load->view('templates/header', $data);
        $this->load->view('performance/index', $data);
        $this->load->view('templates/footer');
    }

    // ========== GET ALL CAMPAIGNS ==========
    public function get_campaigns() {
        $this->output->set_content_type('application/json');
        
        try {
            $result = $this->jsm_api->get_ongoing_campaigns(['page_size' => 50]);
            
            if ($result['success'] && !empty($result['data'])) {
                $campaigns = [];
                foreach ($result['data'] as $campaign) {
                    $campaigns[] = [
                        'id' => $campaign['id'],
                        'name' => $campaign['name'],
                        'status' => $campaign['status']
                    ];
                }
                
                return $this->output->set_output(json_encode([
                    'success' => true,
                    'data' => $campaigns
                ]));
            }
            
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => $result['message'] ?? 'No campaigns found'
            ]));
        } catch (Exception $e) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]));
        }
    }

    // ========== GET PERFORMANCE DATA (CREATORS WITH FULFILLMENT) ==========
    public function get_performance_data() {
        $this->output->set_content_type('application/json');
        
        $campaign_id = $this->input->post('campaign_id');
        
        if (!$campaign_id) {
            // Get first ongoing campaign
            $campaigns_result = $this->jsm_api->get_ongoing_campaigns(['page_size' => 1]);
            if ($campaigns_result['success'] && !empty($campaigns_result['data'])) {
                $campaign_id = $campaigns_result['data'][0]['id'];
            } else {
                return $this->output->set_output(json_encode([
                    'success' => false,
                    'message' => 'No active campaign found'
                ]));
            }
        }
        
        try {
            // 1. Get all products in campaign dengan creator fulfillment
            $all_creators = [];
            $campaign_products = [];
            $page_token = null;
            $page = 1;
            
            do {
                $fulfillment_result = $this->jsm_api->get_campaign_creator_fulfillment($campaign_id, 50, $page_token);
                
                if (!$fulfillment_result['success']) {
                    log_message('error', 'Failed to get creator fulfillment: ' . ($fulfillment_result['message'] ?? 'Unknown error'));
                    break;
                }
                
                if (!empty($fulfillment_result['data']['campaign_product_statistics'])) {
                    foreach ($fulfillment_result['data']['campaign_product_statistics'] as $stat) {
                        $product_detail = $stat['campaign_product_detail'] ?? [];
                        $product_id = $product_detail['product_id'] ?? '';
                        $product_name = $product_detail['product_name'] ?? '';
                        $product_thumbnail = $product_detail['product_thumbnail']['url_list'][0] ?? '';
                        
                        $product_data = [
                            'product_id' => $product_id,
                            'product_name' => $product_name,
                            'product_thumbnail' => $product_thumbnail,
                            'creator_sales_num' => $stat['creator_sales_num'] ?? 0,
                            'collaborated_creators_num' => $stat['collaborated_creators_num'] ?? 0,
                            'promoted_creator_num' => $stat['promoted_creator_num'] ?? 0,
                            'sample_requested_creator_num' => $stat['sample_requested_creator_num'] ?? 0
                        ];
                        
                        $campaign_products[] = $product_data;
                        
                        // Get creators for this product
                        $product_creators_result = $this->jsm_api->get_campaign_product_creator_performance($campaign_id, $product_id, 50);
                        
                        if ($product_creators_result['success'] && !empty($product_creators_result['data']['promotion_creators'])) {
                            foreach ($product_creators_result['data']['promotion_creators'] as $creator) {
                                $creator_data = $creator['creator'] ?? [];
                                $all_creators[] = [
                                    'campaign_id' => $campaign_id,
                                    'product_id' => $product_id,
                                    'product_name' => $product_name,
                                    'affiliate_product_id' => $creator['affiliate_product_id'] ?? '',
                                    'creator_open_id' => $creator_data['creator_open_id'] ?? '',
                                    'creator_nick_name' => $creator_data['nick_name'] ?? '',
                                    'creator_username' => $creator_data['user_name'] ?? '',
                                    'creator_avatar' => $creator_data['avatar_url'] ?? '',
                                    'follower_count' => $creator_data['follower_num'] ?? 0,
                                    'commission' => $creator['commission'] ?? 0,
                                    'paid_amount' => $creator['paid_amount']['amount'] ?? 0,
                                    'video_count' => $creator['video_count'] ?? 0,
                                    'room_count' => $creator['room_count'] ?? 0,
                                    'free_sample_status' => $creator['free_sample_status'] ?? '',
                                    'effective_start_time' => isset($creator['effective_start_time']) ? date('Y-m-d H:i:s', intval($creator['effective_start_time']) / 1000) : null,
                                    'effective_end_time' => isset($creator['effective_end_time']) ? date('Y-m-d H:i:s', intval($creator['effective_end_time']) / 1000) : null
                                ];
                            }
                        }
                    }
                }
                
                $page_token = $fulfillment_result['data']['next_page_token'] ?? null;
                $page++;
                
                if ($page_token) {
                    usleep(100000);
                }
                
            } while ($page_token && $page <= 20);
            
            // 2. Calculate statistics
            $creators_with_posts = 0;
            $creators_with_sales = 0;
            $creators_with_both = 0;
            $total_gmv = 0;
            
            $creator_performance = [];
            foreach ($all_creators as $creator) {
                $has_videos = ($creator['video_count'] ?? 0) > 0;
                $has_sales = ($creator['paid_amount'] ?? 0) > 0;
                
                if ($has_videos) $creators_with_posts++;
                if ($has_sales) $creators_with_sales++;
                if ($has_videos && $has_sales) $creators_with_both++;
                
                $total_gmv += $creator['paid_amount'];
                
                $creator_performance[] = [
                    'creator_open_id' => $creator['creator_open_id'],
                    'creator_nick_name' => $creator['creator_nick_name'],
                    'creator_username' => $creator['creator_username'],
                    'creator_avatar' => $creator['creator_avatar'],
                    'follower_count' => $creator['follower_count'],
                    'paid_amount' => $creator['paid_amount'],
                    'video_count' => $creator['video_count'],
                    'room_count' => $creator['room_count'],
                    'commission' => $creator['commission'],
                    'free_sample_status' => $creator['free_sample_status'],
                    'product_name' => $creator['product_name']
                ];
            }
            
            // Remove duplicates by creator_open_id
            $unique_creators = [];
            $seen = [];
            foreach ($creator_performance as $creator) {
                if (!in_array($creator['creator_open_id'], $seen)) {
                    $seen[] = $creator['creator_open_id'];
                    $unique_creators[] = $creator;
                }
            }
            
            // Sort by paid_amount
            usort($unique_creators, function($a, $b) {
                return $b['paid_amount'] <=> $a['paid_amount'];
            });
            
            // 3. Get recent orders
            $orders_result = $this->jsm_api->search_affiliate_orders([
                'campaign_id' => $campaign_id,
                'page_size' => 50
            ]);
            
            $recent_orders = [];
            if ($orders_result['success'] && isset($orders_result['data'])) {
                foreach ($orders_result['data'] as $order) {
                    $recent_orders[] = [
                        'order_id' => $order['order_id'] ?? '',
                        'create_time' => $order['create_time_formatted'] ?? '',
                        'product_name' => $order['product_name'] ?? '',
                        'creator_username' => $order['creator_username'] ?? '',
                        'gmv' => $order['affiliate_gmv'] ?? 0,
                        'status' => $order['order_status'] ?? ''
                    ];
                }
            }
            
            // 4. Get content statistics for top creators (videos)
            $detailed_creators = [];
            foreach (array_slice($unique_creators, 0, 10) as $creator) {
                // Find product_id for this creator
                $creator_product_id = '';
                foreach ($all_creators as $ac) {
                    if ($ac['creator_open_id'] == $creator['creator_open_id']) {
                        $creator_product_id = $ac['product_id'];
                        break;
                    }
                }
                
                if ($creator_product_id && $campaign_id) {
                    $content_stats = $this->get_creator_content_stats($campaign_id, $creator_product_id, $creator['creator_open_id']);
                    $detailed_creators[] = array_merge($creator, ['content_stats' => $content_stats]);
                } else {
                    $detailed_creators[] = array_merge($creator, ['content_stats' => []]);
                }
            }
            
            return $this->output->set_output(json_encode([
                'success' => true,
                'data' => [
                    'campaign_id' => $campaign_id,
                    'campaign_products' => $campaign_products,
                    'stats' => [
                        'creators_with_posts' => $creators_with_posts,
                        'creators_with_sales' => $creators_with_sales,
                        'creators_with_both' => $creators_with_both,
                        'total_creators' => count($unique_creators),
                        'total_gmv' => $total_gmv,
                        'total_products' => count($campaign_products)
                    ],
                    'top_creators' => array_slice($unique_creators, 0, 10),
                    'detailed_creators' => $detailed_creators,
                    'recent_orders' => array_slice($recent_orders, 0, 20)
                ]
            ]));
            
        } catch (Exception $e) {
            log_message('error', 'Error in get_performance_data: ' . $e->getMessage());
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]));
        }
    }
    
    // ========== GET CREATOR CONTENT STATISTICS ==========
    private function get_creator_content_stats($campaign_id, $product_id, $creator_open_id) {
        try {
            $result = $this->jsm_api->get_creator_content_statistics($campaign_id, $product_id, $creator_open_id);
            
            if ($result['success'] && isset($result['data']['creator_content_statistics'])) {
                $videos = [];
                foreach ($result['data']['creator_content_statistics'] as $video) {
                    $videos[] = [
                        'content_type' => $video['content_type'] ?? 'VIDEO',
                        'cover_img_url' => $video['cover_img_url'] ?? '',
                        'source_url' => $video['source_url'] ?? '',
                        'view_count' => $video['view_count'] ?? 0,
                        'like_count' => $video['like_count'] ?? 0,
                        'comment_num' => $video['comment_num'] ?? 0,
                        'paid_order_num' => $video['paid_order_num'] ?? 0,
                        'paid_amount' => $video['paid_amount'] ?? 0,
                        'published_date' => $video['published_date'] ?? ''
                    ];
                }
                return $videos;
            }
        } catch (Exception $e) {
            log_message('error', 'Error getting content stats: ' . $e->getMessage());
        }
        
        return [];
    }
    
    // ========== GET CREATOR DETAIL ==========
   public function get_creator_detail() {
    $this->output->set_content_type('application/json');
    
    $creator_open_id = $this->input->post('creator_open_id');
    $campaign_id = $this->input->post('campaign_id');
    $product_id = $this->input->post('product_id');
    $creator_name = $this->input->post('creator_name');
    $video_count = $this->input->post('video_count');
    $room_count = $this->input->post('room_count');
    
    if (!$creator_open_id && !$creator_name) {
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => 'Creator open_id or name required'
        ]));
    }
    
    try {
        $creator_detail = null;
        $total_gmv = 0;
        $total_orders = 0;
        
        // 1. Try to get creator detail from marketplace API
        if (!empty($creator_open_id)) {
            $detail_result = $this->jsm_api->get_creator_detail_by_id($creator_open_id);
            if ($detail_result['success'] && isset($detail_result['data']['creator'])) {
                $c = $detail_result['data']['creator'];
                $creator_detail = [
                    'username' => $c['username'] ?? '',
                    'nickname' => $c['nickname'] ?? '',
                    'avatar_url' => $c['avatar']['url'] ?? '',
                    'followers' => $c['follower_count'] ?? 0,
                    'gmv' => $c['gmv']['amount'] ?? 0,
                    'units_sold' => $c['units_sold'] ?? 0,
                    'promoted_product_num' => $c['promoted_product_num'] ?? 0,
                    'bio' => $c['bio_description'] ?? ''
                ];
            }
        }
        
        // 2. Get orders for this creator (to get accurate GMV)
        $search_name = $creator_detail['username'] ?? $creator_name;
        $recent_orders = [];
        
        if (!empty($search_name)) {
            $orders_result = $this->jsm_api->search_affiliate_orders([
                'creator_username' => $search_name,
                'page_size' => 50
            ]);
            
            if ($orders_result['success'] && isset($orders_result['data'])) {
                foreach ($orders_result['data'] as $order) {
                    $total_gmv += $order['affiliate_gmv'];
                    $total_orders++;
                    $recent_orders[] = [
                        'order_id' => $order['order_id'] ?? '',
                        'create_time' => $order['create_time_formatted'] ?? '',
                        'product_name' => $order['product_name'] ?? '',
                        'gmv' => $order['affiliate_gmv'] ?? 0,
                        'status' => $order['order_status'] ?? ''
                    ];
                }
            }
        }
        
        // 3. Get content statistics for this creator (videos)
        $content_stats = [];
        if ($campaign_id && $product_id && !empty($creator_open_id)) {
            $stats_result = $this->jsm_api->get_creator_content_statistics($campaign_id, $product_id, $creator_open_id);
            if ($stats_result['success'] && isset($stats_result['data']['creator_content_statistics'])) {
                foreach ($stats_result['data']['creator_content_statistics'] as $stat) {
                    $content_stats[] = [
                        'content_type' => $stat['content_type'] ?? 'VIDEO',
                        'cover_img_url' => $stat['cover_img_url'] ?? '',
                        'source_url' => $stat['source_url'] ?? '',
                        'view_count' => $stat['view_count'] ?? 0,
                        'like_count' => $stat['like_count'] ?? 0,
                        'comment_num' => $stat['comment_num'] ?? 0,
                        'paid_order_num' => $stat['paid_order_num'] ?? 0,
                        'paid_amount' => $stat['paid_amount'] ?? 0,
                        'published_date' => $stat['published_date'] ?? ''
                    ];
                }
            }
        }
        
        // Use video_count from parameter if content_stats is empty
        $final_video_count = count($content_stats);
        if ($final_video_count == 0 && $video_count > 0) {
            $final_video_count = intval($video_count);
        }
        
        return $this->output->set_output(json_encode([
            'success' => true,
            'data' => [
                'creator_detail' => $creator_detail ?: [
                    'username' => $creator_name,
                    'nickname' => $creator_name,
                    'followers' => 0,
                    'gmv' => $total_gmv,
                    'units_sold' => $total_orders
                ],
                'content_stats' => $content_stats,
                'recent_orders' => $recent_orders,
                'total_videos' => $final_video_count,
                'total_orders' => $total_orders,
                'total_gmv' => $total_gmv,
                'video_count' => $video_count,
                'room_count' => $room_count
            ]
        ]));
        
    } catch (Exception $e) {
        log_message('error', 'Error in get_creator_detail: ' . $e->getMessage());
        return $this->output->set_output(json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]));
    }
}
    // ========== GET ORDER DETAIL ==========
    public function get_order_detail() {
        $this->output->set_content_type('application/json');
        
        $order_id = $this->input->post('order_id');
        
        if (!$order_id) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Order ID required'
            ]));
        }
        
        try {
            $result = $this->jsm_api->get_order_detail($order_id);
            
            if ($result['success']) {
                return $this->output->set_output(json_encode([
                    'success' => true,
                    'data' => $result['data']
                ]));
            } else {
                return $this->output->set_output(json_encode([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to fetch order detail'
                ]));
            }
        } catch (Exception $e) {
            return $this->output->set_output(json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]));
        }
    }
}