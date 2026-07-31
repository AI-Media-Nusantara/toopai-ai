<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Creator_crawler extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        date_default_timezone_set('Asia/Jakarta');

        $this->load->library('Tiktok_partner_crawler');
        $this->load->model('Creator_contact_model');
    }

   public function test()
{
    $username = 'evaanurr';

    $find = $this->tiktok_partner_crawler->find_creator($username);
    $creator = $this->tiktok_partner_crawler->parse_creator_result($find, $username);

    if (!$creator) {
        echo json_encode([
            'success' => false,
            'username' => $username,
            'error' => 'CREATOR_NOT_FOUND',
            'raw' => $find['data'] ?? null,
        ], JSON_PRETTY_PRINT);
        return;
    }

    $creator_oecuid = $this->tiktok_partner_crawler->extract_creator_oecuid($creator);

    $contact_response = $this->tiktok_partner_crawler->get_contact($creator_oecuid);
    $contact = $this->tiktok_partner_crawler->parse_contact($contact_response);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => ($contact['whatsapp'] || $contact['email']) ? true : false,
        'username' => $username,
        'creator_oecuid' => $creator_oecuid,
        'whatsapp' => $contact['whatsapp'],
        'email' => $contact['email'],
        'contact_raw' => $contact_response['data'] ?? $contact_response['raw'] ?? null,
    ], JSON_PRETTY_PRINT);
}
    public function cek($uname)
    {
        $username = $uname;

        $result = $this->crawl_one($username);

        header('Content-Type: application/json');
        echo json_encode($result, JSON_PRETTY_PRINT);
    }

    public function run($limit = 10)
    {
        $limit = intval($limit);

        if ($limit <= 0) {
            $limit = 10;
        }

        $creators = $this->Creator_contact_model->get_pending_from_creators($limit);

        $results = [];

        foreach ($creators as $creator) {
            $results[] = $this->crawl_one($creator->username);
           sleep(rand(8, 20));
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'total' => count($results),
            'results' => $results,
        ], JSON_PRETTY_PRINT);
    }

    private function crawl_one($username)
    {
        $find = $this->tiktok_partner_crawler->find_creator($username);

        $creator = $this->tiktok_partner_crawler->parse_creator_result($find, $username);

        if (!$creator) {
            $save = [
                'creator_username' => $username,
                'status' => 'NOT_FOUND',
                'raw_response' => $find['raw'] ?? null,
                'crawled_at' => date('Y-m-d H:i:s'),
            ];

            $this->Creator_contact_model->upsert($save);

            return [
                'success' => false,
                'username' => $username,
                'status' => 'NOT_FOUND',
                'find_http_code' => $find['http_code'] ?? null,
                'error' => $find['error'] ?? null,
            ];
        }

        $creator_oecuid = $this->tiktok_partner_crawler->extract_creator_oecuid($creator);

        $contact_response = null;
        $contact = [
            'whatsapp' => null,
            'email' => null,
            'raw' => null,
        ];

if (!empty($creator_oecuid)) {
    $contact_response = $this->tiktok_partner_crawler->get_contact($creator_oecuid);
    
    
    // HAPUS atau COMMENT block ini:
    /*
    header('Content-Type: application/json');
    echo json_encode([...], JSON_PRETTY_PRINT);
    exit;
    */
    $contact = $this->tiktok_partner_crawler->parse_contact($contact_response);
}

        $avatar_url = $creator['avatar']['value']['thumb_url_list'][0]
            ?? $creator['avatar']['value']['url_list'][0]
            ?? null;

        $save = [
            'creator_username' => $username,
            'creator_oecuid' => $creator_oecuid,
            'display_name' => $creator['nickname']['value'] ?? null,
            'whatsapp' => $contact['whatsapp'],
            'email' => $contact['email'],
            'avatar_url' => $avatar_url,
            'status' => ($contact['whatsapp'] || $contact['email']) ? 'FOUND' : 'NO_CONTACT',
            'raw_response' => json_encode([
                'creator' => $creator,
                'contact' => $contact['raw'],
                'find_http_code' => $find['http_code'] ?? null,
                'contact_http_code' => $contact_response['http_code'] ?? null,
                'contact_raw' => $contact_response['raw'] ?? null,
            ]),
            'crawled_at' => date('Y-m-d H:i:s'),
        ];

        $this->Creator_contact_model->upsert($save);

        return [
            'success' => true,
            'username' => $username,
            'creator_oecuid' => $creator_oecuid,
            'display_name' => $save['display_name'],
            'status' => $save['status'],
            'whatsapp' => $save['whatsapp'],
            'email' => $save['email'],
            'find_http_code' => $find['http_code'] ?? null,
            'contact_http_code' => $contact_response['http_code'] ?? null,
        ];
    }
    
    public function test_raw($username='HONNETE')
{
    $find = $this->tiktok_partner_crawler->find_creator($username);

    header('Content-Type: application/json');
    echo json_encode($find, JSON_PRETTY_PRINT);
}

public function contact_by_oecuid()
{
    $creator_oecuid = $this->input->get('creator_oecuid');
    $username = $this->input->get('username');

    if (empty($creator_oecuid)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'creator_oecuid kosong',
            'username' => $username,
        ], JSON_PRETTY_PRINT);
        return;
    }

    $contact_response = $this->tiktok_partner_crawler->get_contact($creator_oecuid);
    $contact = $this->tiktok_partner_crawler->parse_contact($contact_response);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => ($contact['whatsapp'] || $contact['email']) ? true : false,
        'username' => $username,
        'creator_oecuid' => $creator_oecuid,
        'whatsapp' => $contact['whatsapp'],
        'email' => $contact['email'],
        'raw' => $contact_response['data'] ?? $contact_response['raw'] ?? null,
    ], JSON_PRETTY_PRINT);
}


}