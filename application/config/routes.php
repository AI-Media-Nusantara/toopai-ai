<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'auth/login';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Auth
$route['auth/login'] = 'auth/login';
$route['auth/logout'] = 'auth/logout';

// Dashboard (redirect ke role masing-masing)
$route['dashboard'] = 'dashboard/index';
// Cipher test routes
$route['tts/get_cipher'] = 'tts/get_cipher';
$route['tts/refresh_cipher'] = 'tts/refresh_cipher';
// BD Routes
$route['bd'] = 'bd/index';
$route['bd/dashboard'] = 'bd/dashboard';
$route['bd/brands'] = 'bd/brands';
$route['bd/brand_detail/(:any)'] = 'bd/brand_detail/$1';
$route['bd/campaigns'] = 'bd/campaigns';
$route['bd/campaign_detail/(:any)'] = 'bd/campaign_detail/$1';

$route['bd/sync'] = 'bd/sync';

// BD AJAX Endpoints
$route['bd/scout_match_brand'] = 'bd/scout_match_brand';
$route['bd/task_hunting'] = 'bd/task_hunting';
$route['bd/task_outreach'] = 'bd/task_outreach';
$route['bd/task_deal'] = 'bd/task_deal';
$route['bd/task_onboarding'] = 'bd/task_onboarding';
$route['bd/task_launch'] = 'bd/task_launch';
$route['bd/task_retention'] = 'bd/task_retention';

// IS Routes
$route['is'] = 'is/index';
$route['is/dashboard'] = 'is/dashboard';
$route['is/creators'] = 'is/creators';
$route['is/add_creator'] = 'is/add_creator';
$route['is/check_phone_exists'] = 'is/check_phone_exists';

// IS — Auto Creator Scouting
$route['is/get_scouting_list']              = 'is/get_scouting_list';
$route['is/onboard_creator_from_scouting']  = 'is/onboard_creator_from_scouting';
$route['is/ignore_scouting_creator']        = 'is/ignore_scouting_creator';
$route['is/refresh_scouting_list']          = 'is/refresh_scouting_list';
$route['is/get_creator_detail'] = 'is/get_creator_detail';
$route['is/approve_creator'] = 'is/approve_creator';
$route['is/generate_affiliate_links'] = 'is/generate_affiliate_links';
$route['is/send_affiliate_links_whatsapp'] = 'is/send_affiliate_links_whatsapp';
$route['is/request_sample'] = 'is/request_sample';
$route['is/update_sample_tracking'] = 'is/update_sample_tracking';
$route['is/get_creator_performance'] = 'is/get_creator_performance';


$route['bd/search_brand_products'] = 'bd/search_brand_products';
$route['bd/generate_affiliate_link'] = 'bd/generate_affiliate_link';
$route['bd/scout_match_brand'] = 'bd/scout_match_brand';
$route['bd/send_whatsapp'] = 'bd/send_whatsapp';
$route['bd/complete_task'] = 'bd/complete_task';

// TTS Authorization Routes
// Cron Discovery — Auto Scouting
$route['cron_discovery/populate_scouting_list'] = 'cron_discovery/populate_scouting_list';

$route['tts/authorize_affiliate']  = 'tts/authorize_affiliate';
$route['tts/callback_affiliate']   = 'tts/callback_affiliate';
$route['tts/authorize_seller']     = 'tts/authorize_seller';
$route['tts/callback_seller']      = 'tts/callback_seller';
$route['tts/authorize_creator']    = 'tts/authorize_creator';
$route['tts/callback_creator']     = 'tts/callback_creator';
$route['tts/status']               = 'tts/status';
$route['tts/refresh']              = 'tts/refresh';



$route['link_management'] = 'link_management/dashboard';
$route['link_management/(:any)'] = 'link_management/$1';

// Redirect Tracker Route
$route['r/(:any)'] = 'r/index/$1';



// Di config/routes.php
$route['creator'] = 'creator/dashboard';
$route['creator/dashboard'] = 'creator/dashboard';
$route['creator/campaigns'] = 'creator/campaigns';
$route['creator/performance'] = 'creator/performance';
$route['creator/profile'] = 'creator/profile';
$route['creator/logout'] = 'creator/logout';

// Creator Auth
$route['creator_auth'] = 'creator_auth/login';
$route['creator_auth/register'] = 'creator_auth/register';
$route['creator_auth/login'] = 'creator_auth/login';
$route['creator_auth/do_register'] = 'creator_auth/do_register';
$route['creator_auth/do_login'] = 'creator_auth/do_login';
$route['creator_auth/authorize_tiktok'] = 'creator_auth/authorize_tiktok';
$route['creator_auth/callback_tiktok'] = 'creator_auth/callback_tiktok';
$route['creator_auth/logout'] = 'creator_auth/logout';



$route['is/search_creators_by_is'] = 'is/search_creators_by_is';
$route['is/search_creators_by_is/(:any)'] = 'is/search_creators_by_is/$1';


// IS Routes
$route['is/target_plan_dashboard'] = 'is/target_plan_dashboard';
$route['is/target_plan'] = 'is/target_plan';
$route['is/target_plan_requests'] = 'is/target_plan_requests';

// BD Routes
$route['bd/target_plan_dashboard'] = 'bd/target_plan_dashboard';
$route['bd/get_target_requests_bd'] = 'bd/get_target_requests_bd';
$route['bd/approve_target_request_bd'] = 'bd/approve_target_request_bd';
$route['bd/reject_target_request_bd'] = 'bd/reject_target_request_bd';

// Target Plan API
$route['is/get_target_requests'] = 'is/get_target_requests';
$route['is/submit_target_request'] = 'is/submit_target_request';
$route['is/approve_target_request_is'] = 'is/approve_target_request_is';
$route['is/reject_target_request_is'] = 'is/reject_target_request_is';
$route['is/mark_target_request_sent'] = 'is/mark_target_request_sent';
$route['is/get_pending_target_requests_for_is'] = 'is/get_pending_target_requests_for_is';


$route['message_template/get'] = 'message_template/get';
$route['message_template/get_all'] = 'message_template/get_all';
$route['message_template/save'] = 'message_template/save';
$route['message_template/delete'] = 'message_template/delete';


// IS - Sample & Shipping Address
$route['is/get_creator_shipping_address'] = 'is/get_creator_shipping_address';
$route['is/confirm_sample_with_details'] = 'is/confirm_sample_with_details';
$route['is/generate_sample_printout'] = 'is/generate_sample_printout';
$route['is/view_sample_printout'] = 'is/view_sample_printout';
$route['is/get_sample_products'] = 'is/get_sample_products';

// IS - Fitur F: Sample Otomatis & Monitoring
$route['is/monitoring']                      = 'is/monitoring';
$route['is/confirm_sample_willingness']      = 'is/confirm_sample_willingness';
$route['is/get_sample_recommendations']      = 'is/get_sample_recommendations';
$route['is/save_sample_delivery']            = 'is/save_sample_delivery';
$route['is/get_monitoring_creator_detail']   = 'is/get_monitoring_creator_detail';
$route['is/get_creator_gmv_breakdown']       = 'is/get_creator_gmv_breakdown';
$route['is/debug_fastmoss_base_info/(:any)'] = 'is/debug_fastmoss_base_info/$1';
$route['is/add_creator_video']               = 'is/add_creator_video';
$route['is/update_sample_video_link']        = 'is/update_sample_video_link';
$route['is/get_sample_keranjang_trigger']    = 'is/get_sample_keranjang_trigger';

// Profile & Management User Dashboard
$route['profile'] = 'profile';
$route['profile/save'] = 'profile/save';
$route['profile/add_managed_user'] = 'profile/add_managed_user';
$route['profile/toggle_status'] = 'profile/toggle_status';


