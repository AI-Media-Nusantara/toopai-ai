<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * SampleProduct_model
 *
 * Model untuk mengelola seluruh logika fitur F:
 * Pengiriman Sample Product Creator.
 */
class SampleProduct_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // =====================================================================
    // F.3 — REKOMENDASI PRODUK SAMPLE
    // =====================================================================

    /**
     * Ambil rekomendasi produk sample untuk creator.
     *
     * Algoritma:
     *  1. Ambil kategori & brand dari produk yang dimiliki creator:
     *       a. affiliate_creator_links (link afiliasi aktif)
     *       b. affiliate_orders (transaksi nyata / keranjang kuning)
     *          — join ke affiliate_products tanpa filter campaign_id agar
     *            tetap menemukan data meski tabel menyimpan composite key.
     *       c. Fallback: creators.category jika dua sumber di atas kosong.
     *  2. Cari produk dari affiliate_products yang:
     *       - Kategori SAMA dengan produk creator.
     *       - Brand/shop_name BERBEDA dari brand yang sudah dimiliki.
     *       - Status APPROVED.
     *       - Produk belum pernah dimiliki/ditransaksikan creator ini.
     *  3. Fallback bertingkat:
     *       - Jika nihil (brand berbeda) → coba kategori sama tanpa filter brand.
     *       - Jika masih nihil (tidak ada data sama sekali) → top-sellers global,
     *         tetapi tetap EXCLUDE brand yang sudah dimiliki creator.
     *  4. Urutkan berdasarkan sales_count DESC.
     *
     * @param int $creator_id
     * @param int $limit
     * @return array
     */
    public function get_sample_recommendation($creator_id, $limit = 20) {
        $creator = $this->db->select('id, username, category')->where('id', $creator_id)->get('creators')->row();
        $username         = $creator ? $creator->username : '';
        $creator_category = $creator ? $creator->category : ''; // kategori profil creator

        // ----------------------------------------------------------------
        // 1a. Produk dari affiliate_creator_links (link afiliasi aktif)
        // ----------------------------------------------------------------
        $existing_links = $this->db
            ->select('acl.product_id, ap.category, ap.shop_name')
            ->from('affiliate_creator_links acl')
            ->join('affiliate_products ap', 'acl.product_id = ap.product_id AND acl.campaign_id = ap.campaign_id', 'left')
            ->where('acl.creator_id', $creator_id)
            ->where('acl.status', 'ACTIVE')
            ->get()
            ->result();

        // ----------------------------------------------------------------
        // 1b. Produk dari transaksi nyata (keranjang kuning)
        //     Pakai raw query agar kondisi NOT IN dan GROUP BY aman.
        // ----------------------------------------------------------------
        $existing_orders = [];
        if (!empty($username)) {
            $sql = "SELECT DISTINCT ao.product_id, ap.category, ap.shop_name
                    FROM affiliate_orders ao
                    LEFT JOIN affiliate_products ap ON ao.product_id = ap.product_id
                    WHERE ao.creator_username = ?
                      AND ao.order_status NOT IN ('CANCELLED', 'REFUNDED')";
            $query = $this->db->query($sql, [$username]);
            $existing_orders = $query ? $query->result() : [];
        }

        $creator_product_ids = [];
        $creator_brands      = [];
        $creator_categories  = [];

        $all_products = array_merge($existing_links, $existing_orders);

        foreach ($all_products as $item) {
            if (!empty($item->product_id)) {
                $creator_product_ids[] = $item->product_id;
            }
            if (!empty($item->shop_name)) {
                $creator_brands[] = strtolower(trim($item->shop_name));
            }
            if (!empty($item->category)) {
                $creator_categories[] = $item->category;
            }
        }

        $creator_product_ids = array_values(array_unique($creator_product_ids));
        $creator_brands      = array_values(array_unique($creator_brands));
        $creator_categories  = array_values(array_unique($creator_categories));

        // ----------------------------------------------------------------
        // 1c. Fallback sumber kategori: gunakan creators.category
        //     Ini penting untuk creator yang belum punya affiliate link
        //     maupun transaksi, tetapi profil-nya sudah memiliki kategori.
        // ----------------------------------------------------------------
        if (empty($creator_categories) && !empty($creator_category)) {
            // creators.category bisa berisi beberapa kategori dipisah koma
            $raw = array_map('trim', explode(',', $creator_category));
            $creator_categories = array_filter($raw); // buang string kosong
            $creator_categories = array_values($creator_categories);

            log_message('debug', "[SampleRec] creator_id={$creator_id}: kategori dari profil creator digunakan: " . implode(', ', $creator_categories));
        }

        log_message('debug', "[SampleRec] creator_id={$creator_id} username={$username} | product_ids=" . count($creator_product_ids) . " brands=" . count($creator_brands) . " categories=" . count($creator_categories) . " | brands=[" . implode(',', $creator_brands) . "] | categories=[" . implode(',', $creator_categories) . "]");

        $select_fields = 'ap.*, b.id as brand_db_id, b.name as brand_display_name, b.sample_type, b.sample_method, COALESCE(b.is_bestseller, 0) as is_bestseller, COALESCE(b.is_trending, 0) as is_trending, COALESCE(b.day7_gmv, 0) as brand_day7_gmv, COALESCE(b.total_gmv, 0) as brand_total_gmv';

        // ----------------------------------------------------------------
        // 2. Query utama: kategori SAMA, brand BERBEDA, produk BERBEDA
        // ----------------------------------------------------------------
        if (!empty($creator_categories)) {
            $this->db->select($select_fields);
            $this->db->from('affiliate_products ap');
            $this->db->join('brands b', 'LOWER(TRIM(ap.shop_name)) = LOWER(TRIM(b.shop_name))', 'left');
            $this->db->where('ap.review_status', 'APPROVED');
            $this->db->where_in('ap.category', $creator_categories);

            if (!empty($creator_product_ids)) {
                $this->db->where_not_in('ap.product_id', $creator_product_ids);
            }

            if (!empty($creator_brands)) {
                $this->_exclude_brands($creator_brands);
            }

            $this->db->order_by('ap.sales_count', 'DESC');
            $this->db->limit($limit);
            $recs = $this->db->get()->result();

            if (!empty($recs)) {
                log_message('debug', "[SampleRec] creator_id={$creator_id}: query utama berhasil, " . count($recs) . " produk");
                return [
                    'recommendations'     => $this->_tag_and_sort_recommendations($recs, $creator_categories),
                    'creator_brands'      => $creator_brands,
                    'creator_categories'  => $creator_categories,
                    'creator_product_ids' => $creator_product_ids,
                ];
            }

            // ------------------------------------------------------------
            // Fallback 1: kategori sama, tanpa filter brand
            // ------------------------------------------------------------
            log_message('debug', "[SampleRec] creator_id={$creator_id}: fallback 1 — hapus filter brand");

            $this->db->select($select_fields);
            $this->db->from('affiliate_products ap');
            $this->db->join('brands b', 'LOWER(TRIM(ap.shop_name)) = LOWER(TRIM(b.shop_name))', 'left');
            $this->db->where('ap.review_status', 'APPROVED');
            $this->db->where_in('ap.category', $creator_categories);

            if (!empty($creator_product_ids)) {
                $this->db->where_not_in('ap.product_id', $creator_product_ids);
            }

            $this->db->order_by('ap.sales_count', 'DESC');
            $this->db->limit($limit);
            $recs = $this->db->get()->result();

            if (!empty($recs)) {
                return [
                    'recommendations'     => $this->_tag_and_sort_recommendations($recs, $creator_categories),
                    'creator_brands'      => $creator_brands,
                    'creator_categories'  => $creator_categories,
                    'creator_product_ids' => $creator_product_ids,
                ];
            }
        }

        // ----------------------------------------------------------------
        // Fallback 2: tidak ada produk sekategori sama sekali
        //             → top-sellers global, tapi tetap exclude brand creator
        // ----------------------------------------------------------------
        log_message('debug', "[SampleRec] creator_id={$creator_id}: fallback 2 — top-sellers global (exclude brand creator)");

        $this->db->select($select_fields);
        $this->db->from('affiliate_products ap');
        $this->db->join('brands b', 'LOWER(TRIM(ap.shop_name)) = LOWER(TRIM(b.shop_name))', 'left');
        $this->db->where('ap.review_status', 'APPROVED');

        if (!empty($creator_product_ids)) {
            $this->db->where_not_in('ap.product_id', $creator_product_ids);
        }

        if (!empty($creator_brands)) {
            $this->_exclude_brands($creator_brands);
        }

        $this->db->order_by('ap.sales_count', 'DESC');
        $this->db->limit($limit);
        $recs = $this->db->get()->result();

        return [
            'recommendations'     => $this->_tag_and_sort_recommendations($recs, $creator_categories),
            'creator_brands'      => $creator_brands,
            'creator_categories'  => $creator_categories,
            'creator_product_ids' => $creator_product_ids,
        ];
    }

    /**
     * Tag dan urutkan produk rekomendasi berdasarkan Best Seller, Trending, Recommended
     */
    private function _tag_and_sort_recommendations(array $recs, array $creator_categories) {
        if (empty($recs)) return [];

        // Deduplicate recommendations by product_id / id to prevent duplicate card entries
        $unique_recs = [];
        foreach ($recs as $r) {
            $pid = !empty($r->product_id) ? $r->product_id : (!empty($r->id) ? $r->id : md5($r->product_name));
            if (!isset($unique_recs[$pid])) {
                $unique_recs[$pid] = $r;
            }
        }
        $recs = array_values($unique_recs);

        $creator_cat_lower = array_map(function($c) { return strtolower(trim($c)); }, $creator_categories);

        // Find max sales_count in recommendations list
        $max_sales = 0;
        foreach ($recs as $p) {
            $sc = intval($p->sales_count ?? 0);
            if ($sc > $max_sales) $max_sales = $sc;
        }

        foreach ($recs as &$p) {
            $sc = intval($p->sales_count ?? 0);

            // Tentukan metode pengiriman produk berdasarkan konfigurasi Brand
            $st = strtolower(trim($p->sample_type ?? ''));
            $sm = strtolower(trim($p->sample_method ?? ''));
            if ($st === 'auto' || $st === 'system' || $sm === 'auto' || $sm === 'system') {
                $p->delivery_method = 'system';
                $p->delivery_method_label = 'By System (TAP)';
            } else {
                $p->delivery_method = 'manual';
                $p->delivery_method_label = 'Manual';
            }

            // Best Seller: Brand Bestseller di TAP ATAU produk top sales (minimal 30% dari sales tertinggi & min 1000 sales)
            $is_bestseller = (!empty($p->is_bestseller) && intval($p->is_bestseller) === 1)
                          || ($max_sales > 0 && $sc >= ($max_sales * 0.3) && $sc >= 1000);

            // Trending: Brand Trending 7-Hari di FastMoss/TAP
            $is_trending = (!$is_bestseller) && ((!empty($p->is_trending) && intval($p->is_trending) === 1) || floatval($p->brand_day7_gmv ?? 0) > 0);

            // Recommended: Kategori produk cocok dengan kategori profil creator
            $is_recommended = (!$is_bestseller && !$is_trending) && (!empty($creator_cat_lower) && !empty($p->category) && in_array(strtolower(trim($p->category)), $creator_cat_lower));

            if ($is_bestseller) {
                $p->badge_type = 'bestseller';
                $p->badge_label = 'Best Seller';
                $p->badge_icon = 'fa-fire';
                $p->badge_color = '#f87171';
                $p->badge_bg = 'rgba(239,68,68,0.15)';
                $p->badge_border = 'rgba(239,68,68,0.3)';
                $p->sort_rank = 1;
            } elseif ($is_trending) {
                $p->badge_type = 'trending';
                $p->badge_label = 'Trending';
                $p->badge_icon = 'fa-chart-line';
                $p->badge_color = '#34d399';
                $p->badge_bg = 'rgba(16,185,129,0.15)';
                $p->badge_border = 'rgba(16,185,129,0.3)';
                $p->sort_rank = 2;
            } elseif ($is_recommended) {
                $p->badge_type = 'recommended';
                $p->badge_label = 'Recommended';
                $p->badge_icon = 'fa-bullseye';
                $p->badge_color = '#a78bfa';
                $p->badge_bg = 'rgba(139,92,246,0.15)';
                $p->badge_border = 'rgba(139,92,246,0.3)';
                $p->sort_rank = 3;
            } else {
                $p->badge_type = '';
                $p->badge_label = '';
                $p->badge_icon = '';
                $p->sort_rank = 4;
            }
        }
        unset($p);

        usort($recs, function($a, $b) {
            if ($a->sort_rank == $b->sort_rank) {
                return (intval($a->sales_count ?? 0) < intval($b->sales_count ?? 0)) ? 1 : -1;
            }
            return ($a->sort_rank > $b->sort_rank) ? 1 : -1;
        });

        return $recs;
    }

    /**
     * Tambahkan kondisi WHERE NOT IN untuk brand ke active query.
     * Dipisah jadi method agar bisa akses $this->db dengan benar.
     *
     * @param array $brands  Array shop_name (sudah lowercase+trimmed)
     */
    private function _exclude_brands(array $brands) {
        $escaped_brands = array_map(function($b) {
            return $this->db->escape_str($b);
        }, $brands);
        $in_list = implode("', '", $escaped_brands);
        $this->db->where("LOWER(TRIM(ap.shop_name)) NOT IN ('" . $in_list . "')", null, false);
    }

    // =====================================================================
    // F.2 — SIMPAN KONFIRMASI KESEDIAAN + REQUEST SAMPLE
    // =====================================================================

    /**
     * Simpan konfirmasi kesediaan sample creator ke sample_requests.
     *
     * @param array $data [creator_id, campaign_id, willing (1|0), notes]
     * @return array
     */
    public function save_sample_willingness($data) {
        $insert = [
            'creator_id'       => $data['creator_id'],
            'campaign_id'      => $data['campaign_id'] ?? null,
            'product_id'       => null,
            'quantity'         => 0,
            'shipping_address' => null,
            'status'           => $data['willing'] ? 'WILLING' : 'NOT_WILLING',
            'willing'          => $data['willing'] ? 1 : 0,
            'notes'            => $data['notes'] ?? null,
            'requested_at'     => date('Y-m-d H:i:s'),
        ];

        $this->db->insert('sample_requests', $insert);
        $id = $this->db->insert_id();

        return [
            'success' => $id > 0,
            'id'      => $id,
            'message' => $id > 0 ? 'Konfirmasi tersimpan' : 'Gagal menyimpan konfirmasi',
        ];
    }

    /**
     * Simpan data pengiriman sample (manual maupun by system).
     * Defensif: hanya insert kolom yang sudah ada di tabel.
     *
     * @param array $data
     * @return array
     */
    public function save_sample_delivery($data) {
        // Kolom dasar yang pasti ada di semua versi tabel
        $insert = [
            'creator_id'       => $data['creator_id'],
            'product_id'       => $data['product_id'],
            'quantity'         => $data['quantity'] ?? 1,
            'status'           => 'PENDING',
            'requested_at'     => date('Y-m-d H:i:s'),
        ];

        // Tambahkan kolom opsional hanya jika ada di tabel
        $existing_cols = $this->db->list_fields('sample_requests');

        $optional = [
            'campaign_id'      => $data['campaign_id'] ?? null,
            'shipping_address' => $data['shipping_address'] ?? null,
            'willing'          => 1,
            'delivery_method'  => $data['delivery_method'] ?? 'manual',
            'tap_request_id'   => $data['tap_request_id'] ?? null,
            'brand_id'         => $data['brand_id'] ?? null,
            'brand_name'       => $data['brand_name'] ?? null,
            'notes'            => $data['notes'] ?? null,
            'video_status'     => 'no_video',
        ];

        foreach ($optional as $col => $val) {
            if (in_array($col, $existing_cols)) {
                $insert[$col] = $val;
            }
        }

        $this->db->insert('sample_requests', $insert);
        $id = $this->db->insert_id();

        if (!$id) {
            log_message('error', 'save_sample_delivery DB error: ' . json_encode($this->db->error()));
        }

        return [
            'success'    => $id > 0,
            'request_id' => $id,
            'message'    => $id > 0 ? 'Sample request tersimpan' : 'Gagal menyimpan',
        ];
    }

    // =====================================================================
    // F.5 — MONITORING: HISTORY SAMPLE
    // =====================================================================

    /**
     * Ambil riwayat semua sample yang pernah dikirim ke creator.
     *
     * @param int $creator_id
     * @return array
     */
    public function get_creator_sample_history($creator_id) {
        return $this->db
            ->select('sr.*, ap.product_name as ap_product_name, ap.image_url, ap.category')
            ->from('sample_requests sr')
            ->join('affiliate_products ap', 'sr.product_id = ap.product_id', 'left')
            ->where('sr.creator_id', $creator_id)
            ->where_in('sr.status', ['PENDING', 'SHIPPED', 'DELIVERED', 'COMPLETED'])
            ->where('sr.product_id IS NOT NULL')
            ->order_by('sr.requested_at', 'DESC')
            ->get()
            ->result();
    }

    /**
     * Summary efektivitas sample satu creator.
     *
     * @param int $creator_id
     * @return array
     */
    public function get_creator_sample_summary($creator_id) {
        $rows = $this->db
            ->select('video_status, COUNT(*) as total')
            ->from('sample_requests')
            ->where('creator_id', $creator_id)
            ->where('product_id IS NOT NULL')
            ->where_in('status', ['SHIPPED', 'DELIVERED', 'COMPLETED'])
            ->group_by('video_status')
            ->get()
            ->result();

        $total_sent = 0;
        $has_video  = 0;
        $no_video   = 0;

        foreach ($rows as $r) {
            $total_sent += (int)$r->total;
            if ($r->video_status === 'has_video') {
                $has_video = (int)$r->total;
            } else {
                $no_video = (int)$r->total;
            }
        }

        return compact('total_sent', 'has_video', 'no_video');
    }

    /**
     * Update link video ke sample_request tertentu.
     *
     * @param int    $sample_id
     * @param string $video_url
     * @return bool
     */
    public function update_sample_video_status($sample_id, $video_url) {
        return $this->db
            ->where('id', $sample_id)
            ->update('sample_requests', [
                'video_url'    => $video_url,
                'video_status' => 'has_video',
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
    }

    // =====================================================================
    // F.5 — MONITORING: GMV BREAKDOWN
    // =====================================================================

    /**
     * GMV breakdown per produk untuk satu creator.
     *
     * @param string $creator_username
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    public function get_gmv_breakdown($creator_username, $start_date = null, $end_date = null) {
        if (!$start_date) $start_date = date('Y-m-d', strtotime('-30 days'));
        if (!$end_date)   $end_date   = date('Y-m-d');

        $rows = $this->db
            ->select('
                ao.product_id,
                ao.product_name,
                COUNT(DISTINCT ao.order_id) as total_orders,
                COALESCE(SUM(ao.quantity), 0) as total_sold,
                COALESCE(SUM(ao.gmv), 0)      as gmv
            ')
            ->from('affiliate_orders ao')
            ->where('ao.creator_username', $creator_username)
            ->where('ao.order_date_local >=', $start_date)
            ->where('ao.order_date_local <=', $end_date)
            ->where('ao.order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->group_by(array('ao.product_id', 'ao.product_name'))
            ->order_by('gmv', 'DESC')
            ->get()
            ->result();

        $total_gmv    = 0;
        $total_sold   = 0;
        $total_orders = 0;

        foreach ($rows as $r) {
            $total_gmv    += floatval($r->gmv);
            $total_sold   += intval($r->total_sold);
            $total_orders += intval($r->total_orders);
        }

        return compact('rows', 'total_gmv', 'total_sold', 'total_orders');
    }

    // =====================================================================
    // F.5 — MONITORING: KERANJANG KUNING
    // =====================================================================

    /**
     * Ambil produk yang sudah masuk keranjang kuning creator
     * (berdasarkan kemunculan di affiliate_orders = transaksi nyata).
     *
     * @param string $creator_username
     * @return array
     */
    public function get_keranjang_kuning($creator_username) {
        return $this->db
            ->select('
                ao.product_id,
                ao.product_name,
                MIN(ao.order_date_local)    as first_used,
                MAX(ao.order_date_local)    as last_used,
                COUNT(DISTINCT ao.order_id) as total_orders,
                COALESCE(SUM(ao.gmv), 0)    as total_gmv,
                acl.affiliate_link,
                ap.image_url,
                ap.shop_name,
                ap.category
            ')
            ->from('affiliate_orders ao')
            ->join(
                'affiliate_creator_links acl',
                'acl.product_id = ao.product_id AND LOWER(TRIM(acl.creator_username)) = LOWER(TRIM(ao.creator_username))',
                'left'
            )
            ->join('affiliate_products ap', 'ap.product_id = ao.product_id', 'left')
            ->where('ao.creator_username', $creator_username)
            ->where('ao.order_status NOT IN ("CANCELLED", "REFUNDED")')
            ->group_by(array('ao.product_id', 'ao.product_name', 'acl.affiliate_link', 'ap.image_url', 'ap.shop_name', 'ap.category'))
            ->order_by('total_gmv', 'DESC')
            ->get()
            ->result();
    }

    // =====================================================================
    // F.5 — MONITORING: VIDEO CREATOR
    // =====================================================================

    /**
     * Ambil video creator dari creator_content_statistics (sync JSM API)
     * dan dari creator_videos (manual input).
     *
     * @param int    $creator_id
     * @param string $creator_username
     * @param int    $limit
     * @return array
     */
    public function get_creator_videos($creator_id, $creator_username, $limit = 20) {
        $videos = [];
        $tables = $this->db->list_tables();

        // Dari creator_content_statistics (data API otomatis)
        if (in_array('creator_content_statistics', $tables)) {
            $api_videos = $this->db
                ->select('ccs.id as video_id, "" as title, COALESCE(ccs.source_url, ccs.linked_tiktok_video) as video_url, ccs.view_count, ccs.like_count, ccs.comment_count, 0 as share_count, ccs.published_date as create_time, ccs.product_id, MAX(ap.product_name) as product_name')
                ->from('creator_content_statistics ccs')
                ->join('affiliate_products ap', 'ccs.product_id = ap.product_id', 'left')
                ->where('ccs.creator_username', $creator_username)
                ->group_by(array('ccs.id', 'ccs.source_url', 'ccs.linked_tiktok_video', 'ccs.view_count', 'ccs.like_count', 'ccs.comment_count', 'ccs.published_date', 'ccs.product_id'))
                ->order_by('ccs.published_date', 'DESC')
                ->limit($limit)
                ->get()
                ->result();

            foreach ($api_videos as $v) {
                $videos[] = [
                    'video_id'     => $v->video_id,
                    'video_url'    => $v->video_url ?: ('https://www.tiktok.com/@' . $creator_username . '/video/' . $v->video_id),
                    'title'        => $v->title,
                    'views'        => intval($v->view_count),
                    'likes'        => intval($v->like_count),
                    'product_id'   => $v->product_id,
                    'product_name' => $v->product_name,
                    'posted_at'    => $v->create_time,
                    'source'       => 'api',
                ];
            }
        }

        // Dari creator_videos (manual input)
        if (in_array('creator_videos', $tables)) {
            $manual_videos = $this->db
                ->select('*')
                ->from('creator_videos')
                ->where('creator_id', $creator_id)
                ->order_by('posted_at', 'DESC')
                ->limit($limit)
                ->get()
                ->result();

            foreach ($manual_videos as $v) {
                $videos[] = [
                    'video_id'     => 'manual_' . $v->id,
                    'video_url'    => $v->video_url,
                    'title'        => $v->product_name ?? 'Video Manual',
                    'views'        => intval($v->views),
                    'likes'        => intval($v->likes),
                    'product_id'   => $v->product_id,
                    'product_name' => $v->product_name,
                    'posted_at'    => $v->posted_at,
                    'source'       => 'manual',
                ];
            }
        }

        // Urutkan gabungan berdasarkan posted_at DESC
        usort($videos, function ($a, $b) {
            return strtotime($b['posted_at'] ?? '0') - strtotime($a['posted_at'] ?? '0');
        });

        return array_slice($videos, 0, $limit);
    }

    /**
     * Simpan video manual (input oleh tim CA).
     *
     * @param array $data
     * @return array
     */
    public function save_manual_video($data) {
        $insert = [
            'creator_id'       => $data['creator_id'],
            'creator_username' => $data['creator_username'],
            'video_url'        => $data['video_url'],
            'product_id'       => $data['product_id'] ?? null,
            'product_name'     => $data['product_name'] ?? null,
            'posted_at'        => $data['posted_at'] ?? date('Y-m-d H:i:s'),
            'views'            => $data['views'] ?? 0,
            'likes'            => $data['likes'] ?? 0,
            'gmv'              => $data['gmv'] ?? 0,
            'source'           => 'manual',
            'created_at'       => date('Y-m-d H:i:s'),
        ];

        $this->db->insert('creator_videos', $insert);
        $id = $this->db->insert_id();

        return [
            'success' => $id > 0,
            'id'      => $id,
            'message' => $id > 0 ? 'Video berhasil ditambahkan' : 'Gagal menyimpan video',
        ];
    }

    // =====================================================================
    // F.5 — MONITORING: DATA LENGKAP SATU CREATOR
    // =====================================================================

    /**
     * Ambil semua data monitoring satu creator dalam satu call.
     *
     * @param int    $creator_id
     * @param string $creator_username
     * @return array
     */
    public function get_full_monitoring_data($creator_id, $creator_username) {
        $gmv_data    = $this->get_gmv_breakdown($creator_username);
        $keranjang   = $this->get_keranjang_kuning($creator_username);
        $videos      = $this->get_creator_videos($creator_id, $creator_username);
        $sample_hist = $this->get_creator_sample_history($creator_id);
        $sample_sum  = $this->get_creator_sample_summary($creator_id);

        return [
            'gmv'            => $gmv_data,
            'keranjang'      => $keranjang,
            'videos'         => $videos,
            'sample_history' => $sample_hist,
            'sample_summary' => $sample_sum,
        ];
    }

    /**
     * Cek konfirmasi kesediaan sample terakhir untuk creator.
     *
     * @param int $creator_id
     * @return object|null
     */
    public function get_last_willingness($creator_id) {
        return $this->db
            ->where('creator_id', $creator_id)
            ->where_in('status', ['WILLING', 'NOT_WILLING'])
            ->order_by('requested_at', 'DESC')
            ->limit(1)
            ->get('sample_requests')
            ->row();
    }
}
