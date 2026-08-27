<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class R extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper('url');
    }

    /**
     * Redirect route: r/:link_id
     */
    public function index($link_id = NULL) {
        if (empty($link_id)) {
            show_404();
        }

        // 1. Cari di affiliate_creator_links
        $link = $this->db->where('link_id', $link_id)->get('affiliate_creator_links')->row();
        
        if ($link) {
            // Increment clicks
            $this->db->where('id', $link->id)->set('total_clicks', 'total_clicks + 1', FALSE)->update('affiliate_creator_links');
            
            // Redirect ke link afiliasi asli
            redirect($link->affiliate_link);
            return;
        }

        // 2. Cari di bd_affiliate_links
        $bd_link = $this->db->where('link_id', $link_id)->get('bd_affiliate_links')->row();
        if ($bd_link) {
            // Redirect
            redirect($bd_link->affiliate_link);
            return;
        }

        // Fallback: Jika tidak ditemukan, redirect ke dashboard/home
        redirect('dashboard');
    }
}
