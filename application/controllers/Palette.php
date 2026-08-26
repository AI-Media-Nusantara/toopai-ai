<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Palette extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper('url');
    }

    /**
     * View Palette Link (Public page for Creators)
     * Route: palette/v/:link_id
     */
    public function v($link_id = NULL) {
        if (empty($link_id)) {
            show_404();
        }

        // Fetch palette main link
        $palette = $this->db->select('bal.*, c.campaign_name')
                            ->from('bd_affiliate_links bal')
                            ->join('affiliate_campaigns c', 'c.campaign_id = bal.campaign_id', 'left')
                            ->where('bal.link_id', $link_id)
                            ->where('bal.link_type', 'multi')
                            ->where('bal.status', 'ACTIVE')
                            ->get()
                            ->row();

        if (!$palette) {
            $this->load->view('public/palette_not_found');
            return;
        }

        // Fetch products inside this palette using a fail-safe two-step query to bypass collation mismatch errors
        $palette_items = $this->db->where('link_id', $link_id)->get('bd_palette_products')->result();
        
        $products = [];
        if (!empty($palette_items)) {
            $product_ids = array_column($palette_items, 'product_id');
            
            $product_details = $this->db->select('product_id, image_url, shop_name, price, sales_count')
                                        ->where_in('product_id', $product_ids)
                                        ->where('campaign_id', $palette->campaign_id)
                                        ->get('affiliate_products')
                                        ->result();
                                        
            $details_map = [];
            foreach ($product_details as $detail) {
                $details_map[$detail->product_id] = $detail;
            }
            
            foreach ($palette_items as $item) {
                $detail = $details_map[$item->product_id] ?? null;
                $item->image_url = $detail ? $detail->image_url : null;
                $item->shop_name = $detail ? $detail->shop_name : null;
                $item->price = $detail ? $detail->price : null;
                $item->sales_count = $detail ? $detail->sales_count : null;
                $products[] = $item;
            }
        }

        // Get Brand info if available from first product's shop_name
        $brand_name = 'Campaign Partner';
        if (!empty($products)) {
            foreach ($products as $p) {
                if (!empty($p->shop_name)) {
                    $brand_name = $p->shop_name;
                    break;
                }
            }
        }

        $data = [
            'palette' => $palette,
            'products' => $products,
            'brand_name' => $brand_name,
            'title' => 'Product Showcase Palette - Toopai'
        ];

        $this->load->view('public/palette_view', $data);
    }
}
