-- =============================================================================
-- FIX: Brand Collaborator Per Creator
-- Database  : holasync_toopai (dev & production)
-- Dibuat    : 2026-08-18
-- Masalah   : affiliate_creator_links.creator_id = NULL untuk semua baris lama,
--             sehingga query brand collaborator per creator tidak mengembalikan data.
-- =============================================================================

-- Jalankan dalam urutan STEP 1 → 2 → 3 → 4
-- Aman untuk dijalankan berulang kali (idempotent).

-- -------------------------------------------------------------------------
-- STEP 1: Isi creator_id di affiliate_creator_links berdasarkan creator_username
-- Ini adalah fix utama. Setelah ini query brand per creator akan langsung bekerja.
-- -------------------------------------------------------------------------
UPDATE affiliate_creator_links acl
    INNER JOIN creators c ON LOWER(TRIM(c.username)) = LOWER(TRIM(acl.creator_username))
SET acl.creator_id  = c.id,
    acl.updated_at  = NOW()
WHERE acl.creator_id IS NULL
  AND acl.creator_username IS NOT NULL
  AND acl.creator_username != '';

-- Verifikasi hasil STEP 1:
-- SELECT 
--     COUNT(*) as total,
--     SUM(CASE WHEN creator_id IS NULL THEN 1 ELSE 0 END) as still_null,
--     SUM(CASE WHEN creator_id IS NOT NULL THEN 1 ELSE 0 END) as filled
-- FROM affiliate_creator_links;


-- -------------------------------------------------------------------------
-- STEP 2: Tambah kolom tiktok_open_id jika belum ada
-- (Jika kolom sudah ada, statement ini akan error — bisa diabaikan)
-- -------------------------------------------------------------------------
ALTER TABLE creators 
    ADD COLUMN IF NOT EXISTS tiktok_open_id VARCHAR(100) DEFAULT NULL,
    ADD INDEX IF NOT EXISTS idx_tiktok_open_id (tiktok_open_id);

-- Jika database MySQL < 8.0 (tidak support IF NOT EXISTS pada ALTER TABLE):
-- SET @col_exists = (
--     SELECT COUNT(*) FROM information_schema.columns 
--     WHERE table_schema = DATABASE() AND table_name = 'creators' AND column_name = 'tiktok_open_id'
-- );
-- -- Jalankan manual jika kolom belum ada:
-- ALTER TABLE creators ADD COLUMN tiktok_open_id VARCHAR(100) DEFAULT NULL;


-- -------------------------------------------------------------------------
-- STEP 3: Tambah kolom fastmoss_cookie di app_config jika belum ada
-- (Simpan cookie FastMoss untuk request yang butuh autentikasi)
-- -------------------------------------------------------------------------
INSERT INTO app_config (`key`, `value`, `updated_at`)
VALUES ('fastmoss_cookie', '', NOW())
ON DUPLICATE KEY UPDATE updated_at = updated_at;
-- Catatan: value dikosongkan, isi melalui UI Admin → /is/update_fastmoss_cookie


-- -------------------------------------------------------------------------
-- STEP 4: Index tambahan untuk performa query brand collaborator
-- -------------------------------------------------------------------------

-- Index untuk affiliate_creator_links.creator_username (fallback query)
ALTER TABLE affiliate_creator_links 
    ADD INDEX IF NOT EXISTS idx_creator_username (creator_username);

-- Index untuk affiliate_orders.creator_username (Sumber A di brand collab)
-- (Biasanya sudah ada, ini hanya memastikan)
ALTER TABLE affiliate_orders
    ADD INDEX IF NOT EXISTS idx_creator_username_orders (creator_username);

-- Jika MySQL < 8.0, ganti ADD INDEX IF NOT EXISTS dengan:
-- CREATE INDEX idx_creator_username ON affiliate_creator_links (creator_username);
-- (hanya jika belum ada)


-- -------------------------------------------------------------------------
-- VERIFIKASI AKHIR
-- Jalankan query di bawah untuk memastikan data sudah terhubung dengan benar.
-- -------------------------------------------------------------------------

-- 1. Cek berapa baris di affiliate_creator_links yang sudah punya creator_id
SELECT 
    COUNT(*) as total_links,
    SUM(CASE WHEN creator_id IS NOT NULL THEN 1 ELSE 0 END) as creator_id_filled,
    SUM(CASE WHEN creator_id IS NULL THEN 1 ELSE 0 END) as creator_id_null
FROM affiliate_creator_links;

-- 2. Sample: cek brand collaborator untuk 1 creator (ganti 'username_creator' dengan username asli)
-- SELECT 
--     ap.shop_name,
--     COUNT(DISTINCT acl.product_id) as total_products,
--     SUM(acl.total_gmv) as total_gmv
-- FROM affiliate_creator_links acl
-- INNER JOIN affiliate_products ap 
--     ON acl.product_id = ap.product_id AND acl.campaign_id = ap.campaign_id
-- WHERE (acl.creator_id = (SELECT id FROM creators WHERE username = 'username_creator' LIMIT 1)
--        OR acl.creator_username = 'username_creator')
--   AND ap.shop_name IS NOT NULL
-- GROUP BY ap.shop_name
-- ORDER BY total_gmv DESC;

-- 3. Cek creator yang belum punya tiktok_open_id
-- SELECT COUNT(*) as creators_without_tiktok_open_id
-- FROM creators
-- WHERE tiktok_open_id IS NULL OR tiktok_open_id = '';
