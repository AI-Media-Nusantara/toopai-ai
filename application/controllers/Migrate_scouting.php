<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migrate_scouting — helper controller untuk setup & diagnosa fitur Auto Creator Scouting.
 * Akses via browser dengan token yang benar, lalu hapus file ini setelah selesai.
 */
class Migrate_scouting extends CI_Controller {

    private $token = 'Toopai2026?_12345';

    private function _check_token() {
        $token = $this->input->get('token');
        if ($token !== $this->token) die('Access denied');
    }

    // ================================================================
    // RUN MIGRATION — buat tabel creator_scouting
    // Akses: /migrate_scouting/run?token=Toopai2026?_12345
    // ================================================================
    public function run() {
        $this->_check_token();
        header('Content-Type: text/plain');

        $sql_create = "
            CREATE TABLE IF NOT EXISTS `creator_scouting` (
              `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              `username`        VARCHAR(100) NOT NULL,
              `full_name`       VARCHAR(200) DEFAULT NULL,
              `avatar_url`      TEXT DEFAULT NULL,
              `phone`           VARCHAR(50) DEFAULT NULL,
              `category`        VARCHAR(100) DEFAULT NULL,
              `follower_count`  INT DEFAULT 0,
              `brand_id`        INT DEFAULT NULL,
              `brand_name`      VARCHAR(200) DEFAULT NULL,
              `campaign_id`     VARCHAR(100) DEFAULT NULL,
              `campaign_name`   VARCHAR(200) DEFAULT NULL,
              `product_id`      VARCHAR(100) DEFAULT NULL,
              `product_name`    TEXT DEFAULT NULL,
              `product_image`   TEXT DEFAULT NULL,
              `gmv`             DECIMAL(15,2) DEFAULT 0,
              `sales_count`     INT DEFAULT 0,
              `commission_rate` DECIMAL(5,2) DEFAULT 0,
              `source`          ENUM('affiliate_orders','fastmoss','tiktok_api') NOT NULL DEFAULT 'affiliate_orders',
              `status`          ENUM('pending','contacted','onboarded','ignored') NOT NULL DEFAULT 'pending',
              `contacted_by`    INT DEFAULT NULL,
              `contacted_at`    DATETIME DEFAULT NULL,
              `onboarded_by`    INT DEFAULT NULL,
              `onboarded_at`    DATETIME DEFAULT NULL,
              `creator_id`      INT DEFAULT NULL,
              `created_at`      DATETIME NOT NULL,
              `updated_at`      DATETIME DEFAULT NULL,
              UNIQUE KEY `uq_username_brand` (`username`, `brand_id`),
              INDEX `idx_status` (`status`),
              INDEX `idx_brand_id` (`brand_id`),
              INDEX `idx_gmv` (`gmv`),
              INDEX `idx_source` (`source`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        $this->db->query($sql_create);

        // ALTER TABLE jika kolom belum ada
        if ($this->db->table_exists('creator_scouting')) {
            $fields = $this->db->list_fields('creator_scouting');
            if (!in_array('contacted_by', $fields)) {
                $this->db->query("ALTER TABLE `creator_scouting` ADD `contacted_by` INT DEFAULT NULL AFTER `status` ");
            }
            if (!in_array('contacted_at', $fields)) {
                $this->db->query("ALTER TABLE `creator_scouting` ADD `contacted_at` DATETIME DEFAULT NULL AFTER `contacted_by` ");
            }
            $this->db->query("ALTER TABLE `creator_scouting` MODIFY `status` ENUM('pending','contacted','onboarded','ignored') NOT NULL DEFAULT 'pending'");
        }

        $error = $this->db->error();
        if (!empty($error['message'])) {
            echo "[ERROR] Buat tabel creator_scouting: " . $error['message'] . "\n";
        } else {
            echo "[OK] Tabel creator_scouting siap.\n";
        }

        echo "\nMigration selesai. Hapus file Migrate_scouting.php dari server.\n";
    }

    // ================================================================
    // DIAGNOSE — cek kondisi data yang dibutuhkan scouting
    // Akses: /migrate_scouting/diagnose?token=Toopai2026?_12345
    // ================================================================
    public function diagnose() {
        $this->_check_token();
        header('Content-Type: application/json');

        $out = [];

        // 1. Jumlah data tiap tabel kunci
        $out['table_counts'] = [
            'affiliate_orders'    => $this->db->count_all('affiliate_orders'),
            'affiliate_campaigns' => $this->db->count_all('affiliate_campaigns'),
            'affiliate_products'  => $this->db->count_all('affiliate_products'),
            'brands'              => $this->db->count_all('brands'),
            'creators'            => $this->db->count_all('creators'),
            'creator_scouting'    => $this->db->table_exists('creator_scouting')
                                         ? $this->db->count_all('creator_scouting')
                                         : 'TABLE NOT EXISTS - jalankan /migrate_scouting/run',
        ];

        // 1b. Jumlah creator_scouting per status
        if ($this->db->table_exists('creator_scouting')) {
            $cs_statuses = $this->db->select('status, COUNT(*) as total')
                ->group_by('status')->get('creator_scouting')->result();
            $out['creator_scouting_by_status'] = $cs_statuses;

            // Sample 5 baris dari creator_scouting
            $out['creator_scouting_sample'] = $this->db->select('id, username, brand_name, gmv, status, created_at')
                ->order_by('gmv', 'DESC')->limit(5)->get('creator_scouting')->result();

            // Debug: cek apakah LEFT JOIN mengeksklusi semua rows
            $join_check = $this->db->query("
                SELECT
                    COUNT(*) AS total_scouting,
                    SUM(CASE WHEN c.id IS NULL THEN 1 ELSE 0 END) AS no_handler,
                    SUM(CASE WHEN c.id IS NOT NULL THEN 1 ELSE 0 END) AS has_handler
                FROM creator_scouting cs
                LEFT JOIN creators c
                    ON  c.username = cs.username
                    AND c.brand_id = cs.brand_id
                    AND c.is_id IS NOT NULL
                    AND c.is_id > 0
                WHERE cs.status = 'pending'
            ");
            $out['left_join_debug'] = $join_check ? $join_check->row() : null;

            // Debug tambahan: cek tanpa LOWER/TRIM vs dengan LOWER/TRIM
            $lower_check = $this->db->query("
                SELECT
                    SUM(CASE WHEN c2.id IS NULL THEN 1 ELSE 0 END) AS no_handler_lower_trim
                FROM creator_scouting cs
                LEFT JOIN creators c2
                    ON  LOWER(TRIM(c2.username)) = LOWER(TRIM(cs.username))
                    AND c2.brand_id = cs.brand_id
                    AND c2.is_id IS NOT NULL
                    AND c2.is_id > 0
                WHERE cs.status = 'pending'
            ");
            $out['left_join_lower_trim_debug'] = $lower_check ? $lower_check->row() : null;
        }

        // 2. Distribusi status campaign
        $out['campaign_statuses'] = $this->db->select('status, COUNT(*) as total')
            ->group_by('status')->get('affiliate_campaigns')->result();

        // 3. Distribusi status brand
        $out['brand_statuses'] = $this->db->select('status, COUNT(*) as total')
            ->group_by('status')->get('brands')->result();

        // 4. Cek match shop_name antara affiliate_products dan brands (ACTIVE)
        $match_q = $this->db->query("
            SELECT COUNT(DISTINCT ap.product_id) as matched_products,
                   COUNT(DISTINCT b.id) as matched_brands
            FROM affiliate_products ap
            JOIN brands b ON TRIM(ap.shop_name) = TRIM(b.shop_name)
            WHERE b.status = 'ACTIVE'
        ");
        $out['shop_name_match_active_brands'] = $match_q ? $match_q->row() : 'QUERY FAILED';

        // 5. Sample creator dari orders TANPA filter status (cek apakah JOIN bisa jalan)
        $sample_any = $this->db->query("
            SELECT o.creator_username, b.name AS brand_name,
                   ac.campaign_name, ac.status AS campaign_status,
                   b.status AS brand_status, SUM(o.gmv) AS total_gmv
            FROM affiliate_orders o
            JOIN affiliate_campaigns ac ON o.campaign_id = ac.campaign_id
            JOIN affiliate_products ap  ON o.product_id  = ap.product_id
            JOIN brands b               ON TRIM(ap.shop_name) = TRIM(b.shop_name)
            WHERE o.order_status NOT IN ('CANCELLED','REFUNDED')
              AND o.creator_username IS NOT NULL AND o.creator_username != ''
            GROUP BY o.creator_username, b.id, ac.campaign_id
            HAVING SUM(o.gmv) > 0
            ORDER BY total_gmv DESC
            LIMIT 10
        ");
        $out['sample_creators_any_status'] = $sample_any
            ? $sample_any->result()
            : 'QUERY FAILED: ' . $this->db->error()['message'];

        // 6. Sample creator dengan filter ONGOING + ACTIVE (yang dipakai scouting)
        $sample_strict = $this->db->query("
            SELECT o.creator_username, b.name AS brand_name,
                   ac.campaign_name, SUM(o.gmv) AS total_gmv
            FROM affiliate_orders o
            JOIN affiliate_campaigns ac ON o.campaign_id = ac.campaign_id
            JOIN affiliate_products ap  ON o.product_id  = ap.product_id
            JOIN brands b               ON TRIM(ap.shop_name) = TRIM(b.shop_name)
            WHERE o.order_status NOT IN ('CANCELLED','REFUNDED')
              AND o.creator_username IS NOT NULL AND o.creator_username != ''
              AND ac.status = 'ONGOING'
              AND b.status  = 'ACTIVE'
            GROUP BY o.creator_username, b.id, ac.campaign_id
            HAVING SUM(o.gmv) > 0
            ORDER BY total_gmv DESC
            LIMIT 10
        ");
        $out['sample_creators_ongoing_active'] = $sample_strict
            ? $sample_strict->result()
            : 'QUERY FAILED: ' . $this->db->error()['message'];

        // 7. Cek sample shop_name tidak match (apa saja yang gagal join)
        $no_match = $this->db->query("
            SELECT DISTINCT ap.shop_name AS product_shop_name,
                   b.shop_name AS brand_shop_name
            FROM affiliate_products ap
            LEFT JOIN brands b ON TRIM(ap.shop_name) = TRIM(b.shop_name)
            WHERE b.id IS NULL
              AND ap.shop_name IS NOT NULL AND ap.shop_name != ''
            LIMIT 20
        ");
        $out['shop_name_no_match_sample'] = $no_match ? $no_match->result() : [];

        // 8. DB error terakhir
        $out['last_db_error'] = $this->db->error();

        echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // ================================================================
    // TEST FITUR E — Auto Detect Creator Link Usage
    // Akses: /migrate_scouting/test_feature_e?token=Toopai2026?_12345
    // ================================================================
    public function test_feature_e() {
        $this->_check_token();
        header('Content-Type: application/json');

        $out = [];

        // -------------------------------------------------------
        // STEP 1: Kondisi creators yang sedang dalam pipeline
        // (status PENDING / LINK_SENT / LINK_SWAPPING + punya is_id)
        // -------------------------------------------------------
        $pipeline = $this->db->query("
            SELECT
                c.id,
                c.username,
                c.status,
                c.is_id,
                c.brand_id,
                b.name AS brand_name,
                u.username AS ca_name,
                c.created_at
            FROM creators c
            LEFT JOIN brands b ON c.brand_id = b.id
            LEFT JOIN users u  ON c.is_id    = u.id
            WHERE c.status IN ('PENDING','LINK_SENT','LINK_SWAPPING')
              AND c.is_id IS NOT NULL
              AND c.is_id > 0
            ORDER BY c.updated_at DESC
            LIMIT 20
        ")->result();
        $out['step1_pipeline_creators'] = [
            'total'   => count($pipeline),
            'note'    => 'Creator di Step 1 Scouting yang sudah punya handler CA',
            'data'    => $pipeline
        ];

        // -------------------------------------------------------
        // STEP 2: Dari pipeline di atas, berapa yang sudah ada order?
        // Ini yang akan di-detect oleh auto_detect_creator_link_usage
        // -------------------------------------------------------
        $detected = $this->db->query("
            SELECT
                c.id,
                c.username,
                c.status,
                c.brand_id,
                b.name AS brand_name,
                COUNT(DISTINCT o.order_id) AS total_orders,
                SUM(o.gmv) AS total_gmv
            FROM creators c
            LEFT JOIN brands b ON c.brand_id = b.id
            JOIN affiliate_orders o
                ON LOWER(TRIM(o.creator_username)) = LOWER(TRIM(c.username))
            JOIN affiliate_products ap
                ON o.product_id  = ap.product_id
               AND o.campaign_id = ap.campaign_id
            JOIN brands b2
                ON TRIM(ap.shop_name) = TRIM(b2.shop_name)
               AND b2.id = c.brand_id
            WHERE c.status IN ('PENDING','LINK_SENT','LINK_SWAPPING')
              AND c.is_id IS NOT NULL
              AND c.is_id > 0
              AND o.order_status NOT IN ('CANCELLED','REFUNDED')
            GROUP BY c.id, c.username, c.status, c.brand_id, b.name
            ORDER BY total_gmv DESC
            LIMIT 20
        ")->result();
        $out['step2_will_be_detected'] = [
            'total' => count($detected),
            'note'  => 'Creator yang akan di-ACTIVE-kan saat auto_detect berjalan',
            'data'  => $detected
        ];

        // -------------------------------------------------------
        // STEP 3: Jalankan auto_detect sekarang (dry-run = false = betulan)
        // -------------------------------------------------------
        $action = $this->input->get('action');
        if ($action === 'run') {
            $this->load->model('CreatorScouting_model');
            $results = $this->CreatorScouting_model->run_auto_detection();
            $out['step3_auto_detect_result'] = [
                'note'                => 'Hasil run_auto_detection() SEKARANG',
                'scouting_onboarded'  => $results['scouting_onboarded'],
                'creators_activated'  => $results['creators_activated'],
            ];
        } else {
            $out['step3_auto_detect_result'] = [
                'note' => 'Tambahkan ?action=run ke URL untuk menjalankan auto_detect sekarang',
                'url'  => 'migrate_scouting/test_feature_e?token=Toopai2026?_12345&action=run'
            ];
        }

        // -------------------------------------------------------
        // STEP 4: Creator yang sudah ACTIVE (sudah berhasil di-detect sebelumnya)
        // -------------------------------------------------------
        $activated = $this->db->query("
            SELECT
                c.id, c.username, c.status, c.brand_id,
                b.name AS brand_name,
                u.username AS ca_name,
                c.approved_at
            FROM creators c
            LEFT JOIN brands b ON c.brand_id = b.id
            LEFT JOIN users u  ON c.is_id    = u.id
            WHERE c.status = 'ACTIVE'
              AND c.source = 'scouted'
            ORDER BY c.approved_at DESC
            LIMIT 10
        ")->result();
        $out['step4_already_activated_from_scouting'] = [
            'total' => count($activated),
            'note'  => 'Creator dari Scouting yang sudah di-ACTIVE-kan sebelumnya',
            'data'  => $activated
        ];

        // -------------------------------------------------------
        // STEP 5: Ringkasan status creators by source
        // -------------------------------------------------------
        $summary = $this->db->query("
            SELECT source, status, COUNT(*) AS total
            FROM creators
            GROUP BY source, status
            ORDER BY source, status
        ")->result();
        $out['step5_creators_summary'] = $summary;

        echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
