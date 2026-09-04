<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CreatorScouting_model extends CI_Model {

    private $table = 'creator_scouting';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // ================================================================
    // POPULATE — dari affiliate_orders
    // ================================================================

    public function populate_from_orders($brand_ids = []) {
        $stats = [
            'inserted'          => 0,
            'skipped_duplicate' => 0,
            'skipped_existing'  => 0,
            'debug'             => []
        ];

        if (!$this->_table_exists()) {
            $stats['debug'][] = 'ERROR: Tabel creator_scouting belum ada. Jalankan /migrate_scouting/run';
            return $stats;
        }

        $ongoing_count = $this->db->where('status', 'ONGOING')
                                   ->count_all_results('affiliate_campaigns');
        $stats['debug'][] = "Campaigns ONGOING: {$ongoing_count}";

        $active_brand_count = $this->db->where('status', 'ACTIVE')
                                        ->count_all_results('brands');
        $stats['debug'][] = "Brands ACTIVE: {$active_brand_count}";

        $order_count = $this->db
            ->where('creator_username IS NOT NULL')
            ->where('creator_username !=', '')
            ->where_not_in('order_status', ['CANCELLED', 'REFUNDED'])
            ->count_all_results('affiliate_orders');
        $stats['debug'][] = "Orders dengan creator_username: {$order_count}";

        if ($order_count === 0) {
            $stats['debug'][] = 'Tidak ada data orders.';
            return $stats;
        }

        $match_q = $this->db->query("
            SELECT COUNT(DISTINCT ap.product_id) as cnt
            FROM affiliate_products ap
            JOIN brands b ON TRIM(ap.shop_name) = TRIM(b.shop_name)
        ");
        $match_count = ($match_q) ? intval($match_q->row()->cnt) : 0;
        $stats['debug'][] = "Products matched ke brands via shop_name: {$match_count}";

        $order_cols          = $this->db->list_fields('affiliate_orders');
        $has_order_shop_name = in_array('shop_name', $order_cols);
        $stats['debug'][]    = "Kolom shop_name di affiliate_orders: " . ($has_order_shop_name ? 'ADA' : 'TIDAK ADA');

        $brand_id_filter = '';
        if (!empty($brand_ids)) {
            $ids             = implode(',', array_map('intval', $brand_ids));
            $brand_id_filter = "AND b.id IN ({$ids})";
        }

        $rows = [];

        // --------------------------------------------------------
        // STRATEGI 1: JOIN via affiliate_products.shop_name
        // Ambil semua creator yang pernah order di brand aktif.
        // Tidak ada filter ke tabel creators — itu urusan get_scouting_list (Fitur C).
        // --------------------------------------------------------
        if ($match_count > 0) {
            $campaign_filter     = $ongoing_count > 0      ? "AND ac.status = 'ONGOING'" : '';
            $brand_status_filter = $active_brand_count > 0 ? "AND b.status = 'ACTIVE'"  : '';

            $sql = "
                SELECT
                    o.creator_username          AS username,
                    MAX(o.product_name)         AS product_name,
                    MAX(ap.image_url)           AS product_image,
                    MAX(ap.product_id)          AS product_id,
                    b.id                        AS brand_id,
                    b.name                      AS brand_name,
                    ac.campaign_id              AS campaign_id,
                    ac.campaign_name            AS campaign_name,
                    SUM(o.gmv)                  AS gmv,
                    COUNT(o.order_id)           AS sales_count
                FROM affiliate_orders o
                JOIN affiliate_campaigns ac ON o.campaign_id = ac.campaign_id
                JOIN affiliate_products  ap ON o.product_id  = ap.product_id
                JOIN brands b               ON TRIM(ap.shop_name) = TRIM(b.shop_name)
                WHERE
                    o.order_status NOT IN ('CANCELLED','REFUNDED')
                    AND o.creator_username IS NOT NULL
                    AND o.creator_username != ''
                    {$campaign_filter}
                    {$brand_status_filter}
                    {$brand_id_filter}
                GROUP BY o.creator_username, b.id, ac.campaign_id
                HAVING SUM(o.gmv) > 0
                ORDER BY gmv DESC
                LIMIT 500
            ";

            $q = $this->db->query($sql);
            if ($q) {
                $rows             = $q->result();
                $stats['debug'][] = "Strategi 1: " . count($rows) . " rows";
            } else {
                $stats['debug'][] = "Strategi 1 GAGAL: " . $this->db->error()['message'];
            }
        }

        // --------------------------------------------------------
        // STRATEGI 2: Fallback via order.shop_name
        // --------------------------------------------------------
        if (empty($rows) && $has_order_shop_name) {
            $stats['debug'][] = "Strategi 2: JOIN via order.shop_name...";

            $sql2 = "
                SELECT
                    o.creator_username                     AS username,
                    MAX(o.product_name)                    AS product_name,
                    NULL                                   AS product_image,
                    MAX(o.product_id)                      AS product_id,
                    COALESCE(b.id, 0)                      AS brand_id,
                    COALESCE(b.name, MAX(o.shop_name))     AS brand_name,
                    o.campaign_id                          AS campaign_id,
                    ac.campaign_name                       AS campaign_name,
                    SUM(o.gmv)                             AS gmv,
                    COUNT(o.order_id)                      AS sales_count
                FROM affiliate_orders o
                JOIN affiliate_campaigns ac ON o.campaign_id  = ac.campaign_id
                LEFT JOIN brands b          ON TRIM(b.shop_name) = TRIM(o.shop_name)
                WHERE
                    o.order_status NOT IN ('CANCELLED','REFUNDED')
                    AND o.creator_username IS NOT NULL
                    AND o.creator_username != ''
                GROUP BY o.creator_username, o.campaign_id
                HAVING SUM(o.gmv) > 0
                ORDER BY gmv DESC
                LIMIT 500
            ";

            $q2 = $this->db->query($sql2);
            if ($q2) {
                $rows             = $q2->result();
                $stats['debug'][] = "Strategi 2: " . count($rows) . " rows";
            } else {
                $stats['debug'][] = "Strategi 2 GAGAL: " . $this->db->error()['message'];
            }
        }

        // --------------------------------------------------------
        // STRATEGI 3: Last resort — tanpa brand JOIN
        // --------------------------------------------------------
        if (empty($rows)) {
            $stats['debug'][] = "Strategi 3: tanpa brand JOIN...";

            $sql3 = "
                SELECT
                    o.creator_username         AS username,
                    MAX(o.product_name)        AS product_name,
                    NULL                       AS product_image,
                    MAX(o.product_id)          AS product_id,
                    NULL                       AS brand_id,
                    MAX(ac.campaign_name)      AS brand_name,
                    o.campaign_id              AS campaign_id,
                    ac.campaign_name           AS campaign_name,
                    SUM(o.gmv)                 AS gmv,
                    COUNT(o.order_id)          AS sales_count
                FROM affiliate_orders o
                JOIN affiliate_campaigns ac ON o.campaign_id = ac.campaign_id
                WHERE
                    o.order_status NOT IN ('CANCELLED','REFUNDED')
                    AND o.creator_username IS NOT NULL
                    AND o.creator_username != ''
                GROUP BY o.creator_username, o.campaign_id
                HAVING SUM(o.gmv) > 0
                ORDER BY gmv DESC
                LIMIT 500
            ";

            $q3 = $this->db->query($sql3);
            if ($q3) {
                $rows             = $q3->result();
                $stats['debug'][] = "Strategi 3: " . count($rows) . " rows";
            } else {
                $stats['debug'][] = "Strategi 3 GAGAL: " . $this->db->error()['message'];
            }
        }

        if (empty($rows)) {
            $stats['debug'][] = "Tidak ada creator kandidat.";
            return $stats;
        }

        // INSERT / UPDATE ke creator_scouting
        // - Jika belum ada (username + brand_id): INSERT baru
        // - Jika sudah ada dengan status 'pending': UPDATE gmv & product_name
        // - Jika sudah 'onboarded' atau 'ignored': skip
        foreach ($rows as $row) {
            $brand_id_val = !empty($row->brand_id) ? intval($row->brand_id) : null;
            $username_val = strtolower(trim($row->username));

            $existing = $this->db
                ->where('LOWER(username)', $username_val)
                ->where('brand_id', $brand_id_val)
                ->get($this->table)
                ->row();

            if ($existing) {
                if ($existing->status === 'pending') {
                    // Update GMV terbaru
                    $this->db->where('id', $existing->id)->update($this->table, [
                        'gmv'          => floatval($row->gmv),
                        'sales_count'  => intval($row->sales_count),
                        'product_name' => $row->product_name,
                        'updated_at'   => date('Y-m-d H:i:s'),
                    ]);
                }
                $stats['skipped_duplicate']++;
                continue;
            }

            $this->db->insert($this->table, [
                'username'      => $username_val,
                'product_id'    => $row->product_id,
                'product_name'  => $row->product_name,
                'product_image' => $row->product_image ?? null,
                'brand_id'      => $brand_id_val,
                'brand_name'    => $row->brand_name,
                'campaign_id'   => $row->campaign_id,
                'campaign_name' => $row->campaign_name,
                'gmv'           => floatval($row->gmv),
                'sales_count'   => intval($row->sales_count),
                'source'        => 'affiliate_orders',
                'status'        => 'pending',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
            $stats['inserted']++;
        }

        return $stats;
    }

    // ================================================================
    // POPULATE — dari FastMoss
    // ================================================================

    public function populate_from_fastmoss($product_id, $brand_id, $creators, $campaign_id = null, $campaign_name = null) {
        $stats = ['inserted' => 0, 'skipped_duplicate' => 0];

        if (!$this->_table_exists()) return $stats;

        $brand      = $this->db->select('name')->where('id', $brand_id)->get('brands')->row();
        $brand_name = $brand->name ?? '';

        foreach ($creators as $c) {
            $username = strtolower(trim(ltrim($c['unique_id'] ?? $c['username'] ?? '', '@')));
            if (empty($username)) continue;

            $in_creators = $this->db->where('LOWER(username)', $username)
                                     ->where('brand_id', $brand_id)
                                     ->count_all_results('creators');
            if ($in_creators > 0) continue;

            $in_scouting = $this->db->where('LOWER(username)', $username)
                                     ->where('brand_id', $brand_id)
                                     ->count_all_results($this->table);
            if ($in_scouting > 0) {
                $stats['skipped_duplicate']++;
                continue;
            }

            $this->db->insert($this->table, [
                'username'        => $username,
                'full_name'       => $c['nickname'] ?? $username,
                'avatar_url'      => $c['avatar'] ?? null,
                'follower_count'  => intval($c['follower_count'] ?? 0),
                'category'        => is_array($c['category'] ?? null) ? implode(', ', $c['category']) : ($c['category'] ?? null),
                'product_id'      => $product_id,
                'product_name'    => $c['product_name'] ?? null,
                'brand_id'        => $brand_id,
                'brand_name'      => $brand_name,
                'campaign_id'     => $campaign_id,
                'campaign_name'   => $campaign_name,
                'gmv'             => floatval($c['sale_amount'] ?? $c['gmv'] ?? 0),
                'sales_count'     => intval($c['sold_count'] ?? 0),
                'commission_rate' => floatval($c['commission_rate'] ?? 0),
                'source'          => 'fastmoss',
                'status'          => 'pending',
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ]);
            $stats['inserted']++;
        }

        return $stats;
    }

    // ================================================================
    // READ — ambil 15 creator GMV tertinggi dari creator_scouting
    // ================================================================

    public function get_scouting_list($filters = []) {
        if (!$this->_table_exists()) return [];

        $status   = $filters['status']   ?? 'pending';
        $limit    = intval($filters['limit']  ?? 50);
        $offset   = intval($filters['offset'] ?? 0);
        $search   = $filters['search']   ?? '';
        $brand_id = $filters['brand_id'] ?? null;
        $source   = $filters['source']   ?? null;

        $where_clauses = [];
        $bindings      = [];

        if (is_array($status)) {
            $where_clauses[] = "cs.status IN (" . implode(',', array_fill(0, count($status), '?')) . ")";
            foreach ($status as $s) {
                $bindings[] = $s;
            }
        } else {
            $where_clauses[] = "cs.status = ?";
            $bindings[]      = $status;
        }

        if ($brand_id) {
            $where_clauses[] = "cs.brand_id = ?";
            $bindings[]      = intval($brand_id);
        }

        if ($source) {
            $where_clauses[] = "cs.source = ?";
            $bindings[]      = $source;
        }

        if (!empty($search)) {
            $s               = '%' . $this->db->escape_like_str($search) . '%';
            $where_clauses[] = "(cs.username LIKE ? OR cs.full_name LIKE ? OR cs.product_name LIKE ? OR cs.brand_name LIKE ?)";
            $bindings[]      = $s;
            $bindings[]      = $s;
            $bindings[]      = $s;
            $bindings[]      = $s;
        }

        $where_sql = implode(' AND ', $where_clauses);

        // Fitur C: sembunyikan creator yang kombinasi username+brand_id-nya
        // sudah dimiliki CA lain di tabel creators (is_id terisi).
        // Creator yang sama untuk brand BERBEDA tetap tampil (poin 6).
        $sql = "
            SELECT cs.*, b.name AS brand_label, u.full_name AS contacted_by_name
            FROM {$this->table} cs
            LEFT JOIN brands b ON cs.brand_id = b.id
            LEFT JOIN users u ON cs.contacted_by = u.id
            WHERE {$where_sql}
              AND NOT EXISTS (
                  SELECT 1 FROM creators c
                  WHERE LOWER(TRIM(c.username)) = LOWER(TRIM(cs.username))
                    AND c.brand_id  = cs.brand_id
                    AND c.is_id     IS NOT NULL
                    AND c.is_id     > 0
              )
            ORDER BY cs.gmv DESC
            LIMIT ? OFFSET ?
        ";

        $bindings[] = $limit;
        $bindings[] = $offset;

        $q = $this->db->query($sql, $bindings);
        return $q ? $q->result() : [];
    }

    public function get_scouting_count($status = 'pending', $brand_id = null) {
        if (!$this->_table_exists()) return 0;

        $where_clauses = [];
        $bindings      = [];

        if (is_array($status)) {
            $where_clauses[] = "cs.status IN (" . implode(',', array_fill(0, count($status), '?')) . ")";
            foreach ($status as $s) {
                $bindings[] = $s;
            }
        } else {
            $where_clauses[] = "cs.status = ?";
            $bindings[]      = $status;
        }

        if ($brand_id) {
            $where_clauses[] = "cs.brand_id = ?";
            $bindings[]      = intval($brand_id);
        }

        $where_sql = implode(' AND ', $where_clauses);

        $sql = "
            SELECT COUNT(*) AS total
            FROM {$this->table} cs
            WHERE {$where_sql}
              AND NOT EXISTS (
                  SELECT 1 FROM creators c
                  WHERE LOWER(TRIM(c.username)) = LOWER(TRIM(cs.username))
                    AND c.brand_id  = cs.brand_id
                    AND c.is_id     IS NOT NULL
                    AND c.is_id     > 0
              )
        ";

        $q = $this->db->query($sql, $bindings);
        return $q ? intval($q->row()->total) : 0;
    }

    public function get_by_id($id) {
        if (!$this->_table_exists()) return null;
        return $this->db->where('id', $id)->get($this->table)->row();
    }

    // ================================================================
    // AKSI IS
    // ================================================================

    public function onboard_creator($scouting_id, $is_id) {
        if (!$this->_table_exists()) return ['success' => false, 'message' => 'Tabel scouting belum tersedia.'];

        // Fast-check sebelum transaction
        $item = $this->get_by_id($scouting_id);
        if (!$item) return ['success' => false, 'message' => 'Scouting item tidak ditemukan'];
        if ($item->status !== 'pending') {
            return [
                'success' => false,
                'message' => 'Creator @' . $item->username . ' untuk brand ini sudah diambil CA lain. Silakan pilih creator lain.'
            ];
        }

        // START TRANSACTION + SELECT FOR UPDATE (lock row ini saja)
        $this->db->trans_start();

        $locked = $this->db->query(
            "SELECT id, status FROM {$this->table} WHERE id = ? FOR UPDATE",
            [$scouting_id]
        );

        if (!$locked) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Gagal mengunci data. Coba lagi.'];
        }

        $locked_row = $locked->row();

        // Re-check status setelah lock
        if (!$locked_row || $locked_row->status !== 'pending') {
            $this->db->trans_rollback();
            return [
                'success' => false,
                'message' => 'Creator @' . $item->username . ' baru saja diambil CA lain. Silakan pilih creator lain.'
            ];
        }

        // Cek apakah kombinasi creator+brand sudah ada di creators
        $existing = $this->db->query(
            "SELECT id, is_id FROM creators WHERE LOWER(TRIM(username)) = ? AND brand_id = ? LIMIT 1",
            [strtolower($item->username), $item->brand_id]
        )->row();

        $creator_id = null;

        if ($existing && $existing->is_id) {
            $this->db->trans_rollback();
            return [
                'success' => false,
                'message' => 'Creator @' . $item->username . ' untuk brand ini sudah dimiliki CA lain.'
            ];
        } elseif ($existing) {
            $this->db->where('id', $existing->id)->update('creators', [
                'is_id'      => $is_id,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $creator_id = $existing->id;
        } else {
            $brand_row = $this->db->select('shop_name')->where('id', $item->brand_id)->get('brands')->row();

            $insert = array_filter([
                'username'           => $item->username,
                'full_name'          => $item->full_name ?: $item->username,
                'avatar_url'         => $item->avatar_url,
                'phone'              => $item->phone,
                'category'           => $item->category,
                'follower_count'     => $item->follower_count,
                'brand_id'           => $item->brand_id,
                'shop_name'          => $brand_row->shop_name ?? null,
                'is_id'              => $is_id,
                'source'             => 'scouted',
                'status'             => 'PENDING',
                'imported_gmv'       => $item->gmv,
                'imported_followers' => $item->follower_count,
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ], fn($v) => $v !== null && $v !== '');

            $this->db->insert('creators', $insert);
            $creator_id = $this->db->insert_id();
        }

        // Tandai onboarded, lalu commit
        $this->db->where('id', $scouting_id)->update($this->table, [
            'status'       => 'onboarded',
            'onboarded_by' => $is_id,
            'onboarded_at' => date('Y-m-d H:i:s'),
            'creator_id'   => $creator_id,
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return ['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan. Coba lagi.'];
        }

        return [
            'success'         => true,
            'creator_id'      => $creator_id,
            'already_existed' => $existing ? true : false,
            'message'         => '✅ @' . $item->username . ' berhasil di-onboard ke Task 1 (Scouting)!'
        ];
    }

    public function ignore_creator($scouting_id) {
        if (!$this->_table_exists()) return ['success' => false, 'message' => 'Tabel scouting belum tersedia.'];

        $item = $this->get_by_id($scouting_id);
        if (!$item) return ['success' => false, 'message' => 'Item tidak ditemukan'];

        $this->db->where('id', $scouting_id)->update($this->table, [
            'status'     => 'ignored',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return ['success' => true, 'message' => 'Creator diabaikan'];
    }

    public function is_already_scouted($username, $brand_id) {
        if (!$this->_table_exists()) return false;
        return $this->db->where('LOWER(username)', strtolower($username))
                        ->where('brand_id', $brand_id)
                        ->count_all_results($this->table) > 0;
    }

    public function get_brands_in_scouting() {
        // Ambil brand Step 4 Monitoring di user BA:
        // status=ACTIVE dan tidak punya affiliate_products dengan review_status=PENDING
        // Logika identik dengan Task 4 di Bd.php
        $q = $this->db->query("
            SELECT
                b.id  AS brand_id,
                COALESCE(NULLIF(b.shop_name, ''), b.name) AS brand_name
            FROM brands b
            LEFT JOIN affiliate_products ap
                   ON TRIM(b.name) = TRIM(ap.shop_name)
                  AND ap.review_status = 'PENDING'
            WHERE b.status = 'ACTIVE'
              AND ap.id IS NULL
            GROUP BY b.id
            ORDER BY brand_name ASC
        ");

        if ($q && $q->num_rows() > 0) {
            return $q->result();
        }

        if (!$this->_table_exists()) return [];

        $query = $this->db->select('brand_id, brand_name')
                          ->where('status', 'pending')
                          ->where('brand_id IS NOT NULL')
                          ->group_by('brand_id, brand_name')
                          ->order_by('brand_name', 'ASC')
                          ->get($this->table);

        return $query ? $query->result() : [];
    }

    // ================================================================
    // AUTO DETECTION DETECT LINK USAGE
    // ================================================================
    public function run_auto_detection() {
        $stats = [
            'scouting_onboarded' => 0,
            'creators_activated' => 0
        ];

        if (!$this->_table_exists()) return $stats;

        // 1. Ambil creator dari scouting dengan status 'contacted'
        $contacted_items = $this->db->where('status', 'contacted')
                                    ->where('contacted_by IS NOT NULL')
                                    ->get($this->table)
                                    ->result();

        foreach ($contacted_items as $item) {
            $username_clean = strtolower(trim($item->username));
            $brand_id = intval($item->brand_id);
            $used_link = false;

            // Cek di campaign_creator_performance
            $ccp_check = $this->db->query("
                SELECT 1 FROM campaign_creator_performance ccp
                JOIN affiliate_products ap ON ccp.product_id = ap.product_id AND ccp.campaign_id = ap.campaign_id
                JOIN brands b ON TRIM(ap.shop_name) = TRIM(b.shop_name)
                WHERE LOWER(TRIM(ccp.creator_username)) = ? AND b.id = ?
                LIMIT 1
            ", [$username_clean, $brand_id])->row();

            if ($ccp_check) {
                $used_link = true;
            } else {
                // Cek di affiliate_orders
                $order_check = $this->db->query("
                    SELECT 1 FROM affiliate_orders o
                    JOIN affiliate_products ap ON o.product_id = ap.product_id AND o.campaign_id = ap.campaign_id
                    JOIN brands b ON TRIM(ap.shop_name) = TRIM(b.shop_name)
                    WHERE LOWER(TRIM(o.creator_username)) = ? AND b.id = ?
                    LIMIT 1
                ", [$username_clean, $brand_id])->row();

                if ($order_check) {
                    $used_link = true;
                }
            }

            if ($used_link) {
                // Buat atau aktifkan relationship di creators table
                $existing = $this->db->query(
                    "SELECT id FROM creators WHERE LOWER(TRIM(username)) = ? AND brand_id = ? LIMIT 1",
                    [$username_clean, $brand_id]
                )->row();

                $creator_id = null;
                $brand_row = $this->db->select('shop_name')->where('id', $brand_id)->get('brands')->row();

                if ($existing) {
                    $this->db->where('id', $existing->id)->update('creators', [
                        'is_id'       => $item->contacted_by,
                        'status'      => 'ACTIVE',
                        'approved_at' => date('Y-m-d H:i:s'),
                        'updated_at'  => date('Y-m-d H:i:s')
                    ]);
                    $creator_id = $existing->id;
                } else {
                    $insert_data = [
                        'username'           => $item->username,
                        'full_name'          => $item->full_name ?: $item->username,
                        'avatar_url'         => $item->avatar_url,
                        'phone'              => $item->phone,
                        'category'           => $item->category,
                        'follower_count'     => $item->follower_count,
                        'brand_id'           => $brand_id,
                        'shop_name'          => $brand_row->shop_name ?? null,
                        'is_id'              => $item->contacted_by,
                        'source'             => 'scouted',
                        'status'             => 'ACTIVE',
                        'imported_gmv'       => $item->gmv,
                        'imported_followers' => $item->follower_count,
                        'approved_at'        => date('Y-m-d H:i:s'),
                        'created_at'         => date('Y-m-d H:i:s'),
                        'updated_at'         => date('Y-m-d H:i:s')
                    ];
                    $this->db->insert('creators', $insert_data);
                    $creator_id = $this->db->insert_id();
                }

                // Ubah status scouting ke 'onboarded'
                $this->db->where('id', $item->id)->update($this->table, [
                    'status'       => 'onboarded',
                    'onboarded_by' => $item->contacted_by,
                    'onboarded_at' => date('Y-m-d H:i:s'),
                    'creator_id'   => $creator_id,
                    'updated_at'   => date('Y-m-d H:i:s')
                ]);

                $stats['scouting_onboarded']++;
            }
        }

        // 2. Ambil creator di tabel creators dengan status PENDING atau LINK_SENT atau LINK_SWAPPING yang sudah memiliki is_id
        $pending_creators = $this->db->where_in('status', ['PENDING', 'LINK_SENT', 'LINK_SWAPPING'])
                                     ->where('is_id IS NOT NULL')
                                     ->get('creators')
                                     ->result();

        foreach ($pending_creators as $creator) {
            $username_clean = strtolower(trim($creator->username));
            $brand_id = intval($creator->brand_id);
            $used_link = false;

            // Cek di campaign_creator_performance
            $ccp_check = $this->db->query("
                SELECT 1 FROM campaign_creator_performance ccp
                JOIN affiliate_products ap ON ccp.product_id = ap.product_id AND ccp.campaign_id = ap.campaign_id
                JOIN brands b ON TRIM(ap.shop_name) = TRIM(b.shop_name)
                WHERE LOWER(TRIM(ccp.creator_username)) = ? AND b.id = ?
                LIMIT 1
            ", [$username_clean, $brand_id])->row();

            if ($ccp_check) {
                $used_link = true;
            } else {
                // Cek di affiliate_orders
                $order_check = $this->db->query("
                    SELECT 1 FROM affiliate_orders o
                    JOIN affiliate_products ap ON o.product_id = ap.product_id AND o.campaign_id = ap.campaign_id
                    JOIN brands b ON TRIM(ap.shop_name) = TRIM(b.shop_name)
                    WHERE LOWER(TRIM(o.creator_username)) = ? AND b.id = ?
                    LIMIT 1
                ", [$username_clean, $brand_id])->row();

                if ($order_check) {
                    $used_link = true;
                }
            }

            if ($used_link) {
                // Update ke ACTIVE
                $this->db->where('id', $creator->id)->update('creators', [
                    'status'      => 'ACTIVE',
                    'approved_at' => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s')
                ]);
                $stats['creators_activated']++;
            }
        }

        return $stats;
    }

    // ================================================================
    // PRIVATE HELPERS
    // ================================================================

    private function _table_exists() {
        return $this->db->table_exists($this->table);
    }
}
