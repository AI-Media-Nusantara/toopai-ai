# Database Merge Tool - FAQ

## General Questions

### Q: Apa yang dilakukan tool ini?
**A:** Tool ini melakukan sinkronisasi & merge otomatis dari database development (`holasync_dev_toopai_before_merge`) ke database production (`holasync_toopai_before_merge`). Tool akan:
- Menambahkan tabel baru dari dev ke prod
- Menambahkan kolom baru ke tabel existing
- Menambahkan index/key baru
- Migrasi data baru dengan deduplication
- Tidak menghapus atau mengubah data existing di production

### Q: Apakah aman untuk dijalankan di production?
**A:** Ya, tool ini dirancang dengan safety features:
- Auto-backup sebelum perubahan
- Tidak pernah DROP table/column/data
- Tidak UPDATE record existing
- Idempotent (dapat dijalankan berulang kali)
- Dry-run mode untuk preview
- Detailed logging untuk audit trail

### Q: Berapa lama waktu eksekusi?
**A:** Tergantung ukuran database:
- Schema sync: 1-2 menit (40+ tables)
- Data migration: 2-5 menit (10k-50k records)
- **Total: ~5-10 menit** untuk dataset typical
- Large database (>1M records): 20-30 menit

### Q: Apakah perlu downtime aplikasi?
**A:** Tidak wajib, tapi direkomendasikan:
- **Without downtime:** Aplikasi tetap jalan, data baru mungkin belum sync sempurna
- **With downtime (recommended):** Maintenance window 15-30 menit untuk hasil terbaik

---

## Setup & Configuration

### Q: Bagaimana cara setup pertama kali?
**A:**
```bash
# 1. Copy config
cp .env.merge.example .env.merge

# 2. Edit credentials
nano .env.merge

# 3. Test connection
php db_merge_sync.php --dry-run

# 4. Execute
php db_merge_sync.php
```

### Q: Dimana kredensial database disimpan?
**A:** File `.env.merge` (gitignored). Format:
```ini
SOURCE_HOST=127.0.0.1
SOURCE_USER=root
SOURCE_PASS=your_password
SOURCE_DB=holasync_dev_toopai_before_merge

TARGET_HOST=127.0.0.1
TARGET_USER=root
TARGET_PASS=your_password
TARGET_DB=holasync_toopai_before_merge
```

### Q: Apakah bisa run tanpa file `.env.merge`?
**A:** Ya, tool akan fallback ke default config (localhost, root, root), tapi **tidak direkomendasikan** untuk production.

### Q: Bagaimana update credentials production?
**A:** Edit `.env.merge` dan ganti section TARGET dengan credentials production.

---

## Execution

### Q: Bagaimana cara preview changes tanpa eksekusi?
**A:**
```bash
php db_merge_sync.php --dry-run
```
Tool akan menampilkan semua query yang AKAN dieksekusi tanpa benar-benar mengubah database.

### Q: Bagaimana cara sync schema saja tanpa data?
**A:**
```bash
php db_merge_sync.php --phase=2
```

### Q: Bagaimana cara sync satu tabel saja untuk testing?
**A:**
```bash
php db_merge_sync.php --table=brands
```

### Q: Apakah bisa skip backup otomatis?
**A:** Ya:
```bash
php db_merge_sync.php --skip-backup
```
**WARNING:** Hanya gunakan jika sudah ada manual backup!

### Q: Bagaimana cara run ulang jika ada error?
**A:** Tool bersifat idempotent, cukup jalankan ulang command yang sama:
```bash
php db_merge_sync.php
```
Records yang sudah ada akan di-skip otomatis.

---

## Schema Synchronization

### Q: Apa yang terjadi pada kolom existing yang berubah tipe data?
**A:** Tool **TIDAK** akan auto-modify kolom existing (terlalu berisiko). Tool hanya akan **log warning** untuk review manual:
```
⚠ Column 'email' has different definition:
  SOURCE: varchar(255) NOT NULL
  TARGET: varchar(100) NULL
  → Manual review required (not auto-modified)
```

### Q: Bagaimana cara handle kolom NOT NULL baru?
**A:** Tool akan menambahkan kolom sebagai NULL dulu (agar tidak error di data existing), lalu log warning:
```
⚠ Column 'new_field' is NOT NULL in SOURCE, adding as NULL in TARGET (backfill required)
```
Anda perlu backfill value manually sebelum ubah ke NOT NULL.

### Q: Apakah foreign key di-sync?
**A:** Tidak otomatis. Tool hanya sync:
- Tabel structure
- Kolom & tipe data
- Index & unique key
- Data records

FK constraints perlu ditambahkan manual jika diperlukan.

### Q: Bagaimana dengan views, triggers, stored procedures?
**A:** Tidak di-sync. Tool ini focus pada tables & data. Views/triggers/procedures perlu di-migrate manual.

---

## Data Migration

### Q: Bagaimana cara tool detect duplicate records?
**A:** Tool menggunakan **business unique key**:
- **Brands:** match by `name`, `email`, atau `slug`
- **Categories:** match by `name`
- **Creators:** match by `username` atau `email`
- **Products:** match by `sku`

Jika match ditemukan, record tidak di-insert ulang (skip).

### Q: Apa yang terjadi pada brand yang sama di SOURCE & TARGET?
**A:** Tool akan:
1. Match brand berdasarkan name/email/slug
2. Simpan mapping: `source_brand_id → target_brand_id`
3. Tidak insert duplicate brand
4. Gunakan mapping untuk remap FK di tabel relasi

### Q: Bagaimana dengan data yang sudah ada di TARGET?
**A:** Data existing di TARGET **tidak pernah diubah**. Tool hanya INSERT record baru dari SOURCE yang belum ada di TARGET.

### Q: Apakah tool UPDATE record existing?
**A:** **TIDAK.** Tool bersifat **insert-only**. Record existing di TARGET tidak akan di-UPDATE atau di-DELETE.

### Q: Tabel apa saja yang di-skip dari data migration?
**A:** Tabel temporary/cache:
- `sessions`, `ci_sessions`
- `cache`
- `logs`, `audit_log`, `activity_log`

### Q: Bagaimana urutan migrasi data?
**A:**
1. Master/lookup tables (categories, statuses, config)
2. Brands (dengan deduplication)
3. Brand relations (brand_claims, brand_products, dll)
4. Creators & products
5. Other tables

---

## Brand Deduplication

### Q: Bagaimana cara tool match brand yang sama?
**A:** Tool match menggunakan OR condition:
```sql
WHERE name = 'Brand Name' 
   OR email = 'brand@email.com' 
   OR slug = 'brand-slug'
```
Jika salah satu match, dianggap brand yang sama.

### Q: Bagaimana jika ada brand dengan nama sama tapi beda email?
**A:** Tool akan match by nama. Brand tidak di-insert ulang. Mapping menggunakan brand existing di TARGET.

### Q: Bagaimana cara lihat brand ID mapping setelah migration?
**A:** Lihat di log file:
```
--- Brand ID Mapping Summary ---
Total brand mappings: 25
  1 → 42
  2 → 43
  5 → 15
  ...
```
Source ID 1 di-map ke Target ID 42, dst.

### Q: Bagaimana dengan brand_claims yang reference brand ID lama?
**A:** Tool otomatis remap FK:
```php
// SOURCE: brand_claim dengan brand_id = 1
// Mapping: source_id 1 → target_id 42
// INSERT ke TARGET: brand_claim dengan brand_id = 42
```

### Q: Apa yang terjadi jika brand_id tidak ada di mapping?
**A:** Record di-skip dengan warning:
```
⚠ Skipping record with unmapped brand_id: 999
```
Ini terjadi jika brand parent belum dimigrasi.

---

## Errors & Troubleshooting

### Q: Error "Connection failed"
**A:** Check:
1. Credentials di `.env.merge` benar
2. Database host accessible
3. User memiliki permission `SELECT` (SOURCE) dan `SELECT, INSERT, ALTER, CREATE` (TARGET)
4. Firewall tidak block koneksi

### Q: Error "Call to a member function row() on false"
**A:** Query gagal karena kolom/tabel tidak ada. Solusi:
```bash
# Run schema sync dulu
php db_merge_sync.php --phase=2
```

### Q: Error "Duplicate column name"
**A:** Normal. Kolom sudah ada di TARGET (mungkin run ulang). Tool akan skip otomatis.

### Q: Error "Foreign key constraint fails"
**A:** Parent record belum ada. Pastikan migrasi dilakukan dalam urutan yang benar:
1. Master tables dulu
2. Parent tables (brands, creators)
3. Child tables (brand_claims, brand_products)

### Q: Error "Disk full" atau "No space left"
**A:** Tidak cukup disk space untuk backup. Solusi:
1. Free up disk space
2. Atau skip auto-backup (risk!):
   ```bash
   php db_merge_sync.php --skip-backup
   ```

### Q: Error "mysqldump: command not found"
**A:** Binary `mysqldump` tidak ada. Install:
```bash
# Ubuntu/Debian
apt-get install mysql-client

# CentOS/RHEL
yum install mysql

# macOS
brew install mysql-client
```

### Q: Tool hang/timeout
**A:** Database terlalu besar. Solusi:
1. Increase PHP timeout: edit `set_time_limit(0)` di script
2. Atau migrasi per tabel:
   ```bash
   php db_merge_sync.php --table=brands
   php db_merge_sync.php --table=creators
   # dst...
   ```

### Q: Memory limit exceeded
**A:** Dataset terlalu besar. Edit script:
```php
ini_set('memory_limit', '1024M'); // Increase to 1GB
```

---

## Verification & Validation

### Q: Bagaimana cara verify migration sukses?
**A:** Check log file:
```bash
tail -100 logs/merge_report_*.log
```
Look for:
```
=== EXECUTION SUMMARY ===
...
Errors: 0
✓ No orphaned records
✓ No duplicate active claims
```

### Q: Bagaimana cara check orphaned records?
**A:**
```sql
SELECT COUNT(*) FROM brand_claims c
LEFT JOIN brands b ON c.brand_id = b.id
WHERE b.id IS NULL;
-- Expected: 0
```

### Q: Bagaimana cara check duplicate brands?
**A:**
```sql
SELECT name, COUNT(*) FROM brands
GROUP BY name
HAVING COUNT(*) > 1;
-- Expected: empty result
```

### Q: Bagaimana cara verify data count?
**A:**
```sql
-- Before migration
SELECT COUNT(*) FROM holasync_toopai_before_merge.brands;

-- After migration
SELECT COUNT(*) FROM holasync_toopai_before_merge.brands;

-- Difference = records inserted (minus duplicates skipped)
```

---

## Backup & Rollback

### Q: Dimana backup file disimpan?
**A:** Directory `backups/` dengan format:
```
backups/holasync_toopai_before_merge_backup_2026-08-29_103015.sql
```

### Q: Berapa lama backup disimpan?
**A:** Tidak ada auto-cleanup. Rekomendasi:
- Keep 30 hari
- Manual cleanup setelah verify migration sukses
- Archive ke storage lain jika perlu

### Q: Bagaimana cara rollback ke backup?
**A:**
```bash
# Find latest backup
ls -lt backups/ | head -5

# Restore
mysql -u root -p holasync_toopai_before_merge < backups/holasync_toopai_before_merge_backup_TIMESTAMP.sql

# Verify
mysql -u root -p holasync_toopai_before_merge -e "SELECT COUNT(*) FROM brands;"
```

### Q: Apakah backup include data?
**A:** Ya, backup adalah **full dump** (schema + data).

### Q: Bagaimana cara compress backup untuk hemat disk?
**A:** Manual compress:
```bash
gzip backups/holasync_toopai_before_merge_backup_*.sql
```
Tool tidak auto-compress agar bisa rollback cepat.

---

## Performance

### Q: Bagaimana cara optimize kecepatan migration?
**A:** Tips:
1. Run di server yang sama (network latency minimal)
2. Disable index temporarily (manual):
   ```sql
   ALTER TABLE brands DISABLE KEYS;
   -- run migration
   ALTER TABLE brands ENABLE KEYS;
   ```
3. Increase MySQL buffer:
   ```sql
   SET GLOBAL innodb_buffer_pool_size = 1G;
   ```

### Q: Apakah tool support parallel processing?
**A:** Tidak (saat ini). Migration sequential per tabel. Future enhancement planned.

### Q: Berapa memory yang digunakan?
**A:** Default limit 512MB. Tool process row-by-row, bukan load semua ke memory.

---

## Production Best Practices

### Q: Apa yang harus dilakukan sebelum run di production?
**A:** Checklist:
1. ✅ Test di staging dulu (full test)
2. ✅ Manual backup production:
   ```bash
   mysqldump -u root -p holasync_toopai_before_merge > manual_backup.sql
   ```
3. ✅ Verify disk space (3x database size)
4. ✅ Schedule maintenance window
5. ✅ Notify stakeholders
6. ✅ Prepare rollback procedure

### Q: Apakah perlu maintenance mode di aplikasi?
**A:** **Sangat direkomendasikan** untuk consistency. Tanpa maintenance mode:
- Data baru mungkin tidak sync sempurna
- User bisa create record saat migration
- Race condition possible

### Q: Berapa lama maintenance window yang diperlukan?
**A:** Rekomendasi:
- Small database (<10k records): 15 menit
- Medium database (10k-100k): 30 menit
- Large database (>100k): 60 menit

Include buffer untuk verification & rollback jika diperlukan.

### Q: Bagaimana cara monitor progress selama migration?
**A:** Real-time log:
```bash
# Terminal 1: run migration
php db_merge_sync.php

# Terminal 2: tail log
tail -f logs/merge_report_*.log
```

### Q: Apa yang harus di-verify setelah migration?
**A:** Checklist:
1. ✅ Log shows 0 errors
2. ✅ No orphaned records
3. ✅ Record counts reasonable
4. ✅ Application smoke test passed
5. ✅ Sample data lookup correct
6. ✅ Business logic working

---

## Advanced Usage

### Q: Bagaimana cara add custom migration script?
**A:** Create file di `migrations/`:
```bash
# Create migration file
nano migrations/002_add_custom_indexes.sql
```
Content:
```sql
-- Custom migration: Add performance indexes
ALTER TABLE brands ADD INDEX idx_name (name);
ALTER TABLE creators ADD INDEX idx_username (username);
```
Tool akan auto-detect & execute saat run phase 2.

### Q: Bagaimana urutan execution custom migrations?
**A:** Alphabetical order by filename. Gunakan prefix numeric:
```
migrations/
  001_add_creator_gmv_cache_columns.sql
  002_add_custom_indexes.sql
  003_add_new_table.sql
```

### Q: Apakah bisa skip certain tables dari migration?
**A:** Ya, edit array `$skip_data_tables` di script:
```php
private $skip_data_tables = [
    'sessions',
    'cache',
    'your_table_to_skip',
];
```

### Q: Bagaimana cara extend unique key detection untuk tabel baru?
**A:** Edit method `buildUniqueCheck()`:
```php
$unique_fields = [
    'categories' => ['name'],
    'your_new_table' => ['unique_code', 'unique_email'],
];
```

---

## Troubleshooting Specific Issues

### Q: Brand matched incorrectly (false positive)
**A:** Terjadi jika business key terlalu loose. Review matching logic dan adjust di `migrateBrands()`:
```php
// Tambahkan kondisi lebih ketat
if (!empty($brand['name']) && !empty($brand['email'])) {
    $match_conditions[] = "name = '$name' AND email = '$email'";
}
```

### Q: Migration slow untuk tabel besar
**A:** Gunakan `--table` untuk migrasi per tabel di background:
```bash
# Terminal 1
php db_merge_sync.php --table=large_table_1 &

# Terminal 2
php db_merge_sync.php --table=large_table_2 &

# dst...
```

### Q: Character encoding issues (garbled text)
**A:** Verify charset:
```sql
-- Check SOURCE charset
SHOW CREATE TABLE brands;

-- Check TARGET charset
SHOW CREATE TABLE brands;
```
Tool menggunakan `utf8mb4` by default. Jika berbeda, perlu manual adjustment.

---

## Support

### Q: Dimana dokumentasi lengkap?
**A:**
- Full documentation: `README_DB_MERGE.md`
- Quick start: `QUICKSTART_DB_MERGE.md`
- Testing guide: `DB_MERGE_TESTING.md`
- Deliverables: `DB_MERGE_DELIVERABLES.md`

### Q: Bagaimana cara report bug atau request feature?
**A:** Include:
1. Full command executed
2. Log file content (`logs/merge_report_*.log`)
3. PHP version: `php -v`
4. MySQL version: `mysql --version`
5. Error message (full stack trace)
6. Expected vs actual behavior

### Q: Apakah ada GUI version?
**A:** Tidak (saat ini). Tool ini CLI-only untuk safety & transparency. Web UI planned as future enhancement.

---

## Common Misconceptions

### ❌ "Tool akan overwrite data production"
**✅ Fact:** Tool bersifat **insert-only**. Data existing tidak pernah di-UPDATE atau di-DELETE.

### ❌ "Tool bisa dijalankan tanpa backup"
**✅ Fact:** Tool auto-create backup, tapi **manual backup tetap direkomendasikan** sebagai extra safety.

### ❌ "Migration harus dilakukan sekali saja"
**✅ Fact:** Tool bersifat **idempotent** — bisa dijalankan berulang kali safely. Duplicates akan di-skip otomatis.

### ❌ "Semua tabel harus dimigrasi sekaligus"
**✅ Fact:** Bisa migrasi **per tabel** dengan `--table=` atau **per phase** dengan `--phase=`.

### ❌ "Dry-run tidak perlu karena ada backup"
**✅ Fact:** **Dry-run tetap penting** untuk detect potential issues sebelum actual execution.

---

## Quick Reference

### Essential Commands
```bash
# Help
php db_merge_sync.php --help

# Preview (no changes)
php db_merge_sync.php --dry-run

# Schema only
php db_merge_sync.php --phase=2

# Single table
php db_merge_sync.php --table=brands

# Full migration
php db_merge_sync.php

# Full migration (no backup)
php db_merge_sync.php --skip-backup
```

### Verification Queries
```sql
-- Check orphaned records
SELECT COUNT(*) FROM brand_claims c
LEFT JOIN brands b ON c.brand_id = b.id
WHERE b.id IS NULL;

-- Check duplicates
SELECT name, COUNT(*) FROM brands
GROUP BY name HAVING COUNT(*) > 1;

-- Check record counts
SELECT 
  (SELECT COUNT(*) FROM brands) as brands_count,
  (SELECT COUNT(*) FROM creators) as creators_count,
  (SELECT COUNT(*) FROM products) as products_count;
```

### Emergency Rollback
```bash
mysql -u root -p holasync_toopai_before_merge < backups/holasync_toopai_before_merge_backup_LATEST.sql
```

---

**Last Updated:** 2026-08-29  
**Tool Version:** 1.0.0  
**Status:** Production Ready
