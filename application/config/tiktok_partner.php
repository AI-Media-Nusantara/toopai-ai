<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| TikTok Partner Crawler Configuration
|--------------------------------------------------------------------------
| Safe rewrite:
| - Endpoint creator + brand lengkap.
| - Session/token aktif tidak di-hardcode di sini.
| - Session lengkap disimpan di DB via /partner_crawler/update_session.
| - Jika TikTok meminta verification/captcha/puzzle, request dihentikan.
*/

$config['tiktok_partner_partner_id'] = '8665164061102737169';

// Creator Marketplace endpoints
$config['tiktok_partner_find_url'] = 'https://api-partner-sg.tiktokshop.com/api/v1/oec/affiliate/creator/marketplace/4partner/find';
$config['tiktok_partner_contact_url'] = 'https://api-partner-sg.tiktokshop.com/api_sens/v1/affiliate/cmp/contact';

// Brand / Product endpoints
$config['tiktok_partner_product_search_url'] = 'https://api-partner-sg.tiktokshop.com/api/v1/affiliate/partner/product/search';
$config['tiktok_partner_brand_contact_url'] = 'https://api-partner-sg.tiktokshop.com/api/v1/affiliate/partner/product/contact';

// Request pacing. Jangan terlalu kecil.
$config['tiktok_partner_request_delay'] = [
    'min' => 15,
    'max' => 45,
];

// Cache TTL untuk mengurangi request berulang.
$config['tiktok_partner_cache_ttl'] = 21600; // 6 jam

// Stop request saat verification muncul. Tidak retry captcha.
$config['tiktok_partner_stop_on_verification'] = true;

// Default browser/session values. Akan dioverride oleh DB.
$config['tiktok_partner_default_session'] = [
    'cookie' => 'd_ticket=2af0c06ae43cad92be0a6d0034b9ebd4df3b3; _ga=GA1.1.1354372700.1769752534; _tt_enable_cookie=1; _ttp=01KK906DFCT81J51Q58DFWBP7G_.tt.1; ttcsid=1773049951728::yueCDgeWDkkPpUNWUtsi.1.1773049951946.0; ttcsid_C70N19O394AQ13GK2OV0=1773049951728::WvHS9JAV3iBnPrg43iM6.1.1773049951946.0; _ga_BZBQ2QHQSP=GS1.1.1773049950.8.0.1773049954.0.0.1705369916; passport_csrf_token=a887f91d878cdb89c90036522fedec39; passport_csrf_token_default=a887f91d878cdb89c90036522fedec39; store-country-sign=MEIEDG4qZlzaQt9roejI6AQg5BlzAAf5J_HWTxAJQOPmCxcW7BUlaUMRHMJ09Osfal8EEFzhIINQDMikEl8DQmXccpE; uid_tt=59bd665f72446f751f12854803c0b7fb7b3c86917b5825ed30e4b1df04038b0e; uid_tt_ss=59bd665f72446f751f12854803c0b7fb7b3c86917b5825ed30e4b1df04038b0e; sid_tt=cb6882cfb98c4d1c699a9b5c885f94c0; sessionid=cb6882cfb98c4d1c699a9b5c885f94c0; sessionid_ss=cb6882cfb98c4d1c699a9b5c885f94c0; msToken=zliD11cG-qCuXdlgToqWbbI_J6KTE_opH1tTmLN8H7Z3zE0fjHVPOss8vkv_7TgI90_NqeJryzaARf6KFESVC1XRmuz8KhVgkNUSqRO7eFUk41kfU-NWoTHY6rN1rQ==; TTSPC_Side_Menu_Role_US=1; TTSPC_Side_Menu_Role=0; sid_guard=cb6882cfb98c4d1c699a9b5c885f94c0%7C1781252698%7C259200%7CMon%2C+15-Jun-2026+08%3A24%3A58+GMT; sid_ucp_v1=1.0.1-KGMxNmU5NGJhOTE1ZDI0YTdjYTZkNDM4NmVmNjUxYzExOWJlNDBiYzIKGwiRiIq03vz4j2oQ2oSv0QYYofoVIAw4AUDrBxADGgJteSIgY2I2ODgyY2ZiOThjNGQxYzY5OWE5YjVjODg1Zjk0YzAyTgogipNuRr18bgZ6T7jUiZAPa7EAf9RUW2LbxYUhTt2kBIsSIDfJD1oCQ-NSX2iSWustrcFImSzV_m-KsF_mGBnOPO8ZGAIiBnRpa3Rvaw; ssid_ucp_v1=1.0.1-KGMxNmU5NGJhOTE1ZDI0YTdjYTZkNDM4NmVmNjUxYzExOWJlNDBiYzIKGwiRiIq03vz4j2oQ2oSv0QYYofoVIAw4AUDrBxADGgJteSIgY2I2ODgyY2ZiOThjNGQxYzY5OWE5YjVjODg1Zjk0YzAyTgogipNuRr18bgZ6T7jUiZAPa7EAf9RUW2LbxYUhTt2kBIsSIDfJD1oCQ-NSX2iSWustrcFImSzV_m-KsF_mGBnOPO8ZGAIiBnRpa3Rvaw; tt_session_tlb_tag=sttt%7C5%7Cy2iCz7mMTRxpmptciF-UwP________-_EnlWlaOUGixBMopejYkk94dmnifKDjGZ5zNHYQuGMQE%3D; ttwid=1%7CkGa1qo1qQka7yPw96ws4P6fG9qdZkTDFPh3pNHFrEx4%7C1781258744%7C48575d52410e23eedff164b649f4980b065195012e7510f4171763d0396de73d; odin_tt=e79e7942731fc83bde8c2b3acb2b313f7b95a911cb53eaa5827957b71b2b1c38b92be49e1ddb24b2cbad6c0fa2accc1d2cd1387755bd370982275d59d436c10e',
    'ms_token' => 'W4tN2VuYcVU5pEkNlPJ9hlzomFYWGzJwoOOtrC8jTWepVJCO3CCIxDD07Vj9xAXTym5xNpAZIRE4eBnTRb2FhNBPolvrI_JjIlTeTCQrkIp1Sbz4YGsD5Vl9MksUf1i7f6Wm7Hm9zT0=',
    'x_bogus' => 'DFSzswVYhqtANylzC2q5EU9WcBr7',
    'signature' => '_02B4Z6wo00001DOXnMQAAIDAM5ecxuy1z6Qzl5hAAGbP2d',
    'bsid' => '9a8c00002710271000079ab927100000019ebb62d5fa000000077a1648ec57dea3f92c7d0b4e8165a3a2a2a3a2a2a3a20c2d171361fcb31936a8009c016d571580b9a3d8ce9c677ff0b7598c4df54b40379113cf60ce4452ee2d302689b7d69bfe738e1d000000001124a4ec',
    'fp' => 'verify_mpf4rqxs_zFcqBzGK_1jjJ_4NX5_8m4d_pkoiGNtsY6Ti',
    'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36',
    'browser_platform' => 'MacIntel',
    'browser_language' => 'id-ID',
    'timezone_name' => 'Asia/Jakarta',
    'screen_width' => '1920',
    'screen_height' => '1080',
    'browser_online' => 'true',
    'device_id' => '0',
    'turing' => [
        'xmsi' => '960',
        'xmst' => 'Um4oSHY8x1eNzOT4pq8uIsJxwWk3b4-8G5UTUHav5IC_iqYV9qHhzQ0dr8tXYQ6baOuboZY1nx_i89UhxwKYdxaQQ-GhWOWDbTfIx7z6HbuLMJXo-vTw-sZi2Lf4gx_wWDiBQm04rA==',
        'xmstr' => '{"sTm":1781260426774,"acc":985}',
        'version_dynamic_form_en_US' => '1770625456083335000',
        'version_dynamic_form_id_ID' => '1777525767468722000',
        'version_web_id_ID' => '1780529970886879000',
        'version_bdturing_en' => '1727422735073483000',
    ],
];