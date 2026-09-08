# Database Merge & Sync Tool

Automated CLI script untuk sync schema & data dari Development DB (`holasync_dev_toopai_before_merge`) ke Production DB (`holasync_toopai_before_merge`).

## Features

✅ **Safe & Idempotent** — Tidak menghapus data existing, dapat dijalankan berulang kali  
✅ **Auto Backup** — Backup TARGET database sebelum eksekusi  
✅ **Schema Sync** — Tambahkan tabel baru, kolom baru, index baru otomatis  
✅ **Smart Deduplication** — Match brand berdasarkan business key (name, email, slug)  
✅ **FK Remapping** — Translasi brand_id dari SOURCE ke TARGET  
✅ **Integrity Check** — Validasi orphaned records & duplicate constraints  
✅ **Dry Run Mode** — Preview perubahan tanpa eksekusi  
✅ **Detailed Logging** — Log lengkap tersimpan di `logs/merge_report_[timestamp].log`

---

## Prerequisites

- PHP 7.4+ dengan extension `mysqli`
- MySQL 5.7+ atau MariaDB 10.3+
- `mysqldump` binary (untuk backup otomatis)
- Akses read ke SOURCE database
- Akses write ke TARGET database

---

## Installation

1. **Copy file konfigurasi:**
   ```bash
   cp .env.merge.example .env.merge
   ```

2. **Edit `.env.merge` dengan kredensial database Anda:**
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

3. **Buat direktori backup & logs:**
   ```bash
   mkdir -p backups logs
   chmod +x db_merge_sync.php
   ```

---

## Usage

### 🔹 Step 1: Dry Run (Preview Only)

Jalankan dry-run untuk melihat perubahan yang akan dilakukan **tanpa** mengubah database:

```bash
php db_merge_sync.php --dry-run
```

Review output dan log file di `logs/` untuk memastikan tidak ada issue.

---

### 🔹 Step 2: Schema Sync Only (Phase 2)

Sync struktur tabel/kolom/index saja, tanpa migrasi data:

```bash
php db_merge_sync.php --phase=2
```

Ini akan:
- Membuat tabel baru yang belum ada di TARGET
- Menambahkan kolom baru ke tabel existing
- Menambahkan index/unique key baru
- Melaporkan perbedaan definisi kolom (tidak auto-modify)

---

### 🔹 Step 3: Test Single Table

Test migrasi data untuk satu tabel saja (misal: brands):

```bash
php db_merge_sync.php --table=brands
```

Ini akan sync schema + data untuk tabel `brands` saja.

---

### 🔹 Step 4: Full Migration (All Phases)

Jalankan seluruh proses (backup → schema sync → data migration → verification):

```bash
php db_merge_sync.php
```

Ini akan:
1. **Phase 1:** Backup TARGET database → `backups/holasync_toopai_before_merge_backup_[timestamp].sql`
2. **Phase 2:** Sync schema (tabel, kolom, index)
3. **Phase 3:** Migrasi data dengan deduplication & FK remapping
4. **Phase 4:** Integrity check & laporan ringkasan

---

### 🔹 Step 5: Skip Backup (Production)

Jika sudah yakin dan tidak perlu backup otomatis:

```bash
php db_merge_sync.php --skip-backup
```

⚠️ **WARNING:** Backup manual direkomendasikan sebelum jalankan di production!

---

## CLI Options

| Option | Description |
|--------|-------------|
| `--dry-run` | Preview mode — tidak melakukan perubahan actual |
| `--table=TABLE` | Limit sync ke tabel tertentu saja |
| `--phase=N` | Jalankan phase tertentu (1=backup, 2=schema, 3=data, 4=verify) |
| `--skip-backup` | Skip backup otomatis (not recommended) |
| `--help` | Tampilkan bantuan |

---

## Migration Order

Data dimigrasikan dalam urutan prioritas untuk menjaga referential integrity:

1. **Master Tables:** `categories`, `statuses`, `config`, `roles`, `permissions`
2. **Brands:** `brands` (dengan deduplication)
3. **Brand Relations:** `brand_claims`, `brand_products`, `brand_collaborators`, dll
4. **Creators:** `creators`, `creator_categories`, `creator_notes`
5. **Products:** `products`, `product_categories`
6. **Other Tables:** Sisa tabel yang belum diproses

---

## Deduplication Logic

### Brand Matching

Brand dari SOURCE akan di-match dengan TARGET berdasarkan **business unique key**:
- `name` (exact match)
- `email` (exact match)
- `slug` (exact match)

Jika match ditemukan → mapping `source_brand_id → target_brand_id` disimpan.  
Jika tidak match → brand baru di-insert ke TARGET.

### Brand Relations

Semua tabel dengan `brand_id` FK akan:
1. Remap `brand_id` menggunakan mapping yang sudah dibuat
2. Skip record jika `brand_id` tidak ada di mapping (orphan)
3. Check duplicate berdasarkan unique constraints sebelum insert

---

## Output & Logging

### Terminal Output
Real-time progress ditampilkan di terminal dengan format:
```
[2024-03-15 10:30:15] [INFO] Processing table: brands
[2024-03-15 10:30:16] [INFO] ✓ Brands: 15 matched, 5 inserted
```

### Log File
Full log disimpan di: `logs/merge_report_[timestamp].log`

### Backup File
Backup TARGET disimpan di: `backups/holasync_toopai_before_merge_backup_[timestamp].sql`

---

## Safety Features

### ✅ What Script WILL DO:
- ✓ Create new tables from SOURCE
- ✓ Add new columns to existing tables
- ✓ Add new indexes/keys
- ✓ Insert new records (deduplicated)
- ✓ Create backup before changes
- ✓ Report schema differences

### ❌ What Script WILL NOT DO:
- ✗ Drop tables or columns
- ✗ Delete existing data
- ✗ Modify existing records
- ✗ Auto-change column definitions (only report)
- ✗ Overwrite TARGET data

---

## Troubleshooting

### Error: "Call to a member function row() on false"
Column baru belum ada di TARGET. Jalankan Phase 2 dulu:
```bash
php db_merge_sync.php --phase=2
```

### Error: "Duplicate entry for key"
Record sudah ada di TARGET. Script akan skip otomatis (idempotent).

### Error: "Foreign key constraint fails"
FK target belum ada. Pastikan parent record sudah dimigrasi dulu (misal: brands sebelum brand_claims).

### Backup Failed
Cek kredensial `mysqldump` dan pastikan binary tersedia:
```bash
which mysqldump
mysqldump --version
```

---

## Phase Detail

### Phase 1: Pre-flight & Backup
- Connect ke SOURCE & TARGET database
- Backup TARGET database (skip jika `--skip-backup`)
- Disable `FOREIGN_KEY_CHECKS` di TARGET

### Phase 2: Schema Sync
- Compare `information_schema` antara SOURCE & TARGET
- Create new tables (dengan `SHOW CREATE TABLE`)
- Add new columns (dengan `ALTER TABLE ADD COLUMN`)
- Add new indexes (dengan `ALTER TABLE ADD KEY/UNIQUE`)
- Report column definition changes (tidak auto-modify)

### Phase 3: Data Migration
- Migrate master/lookup tables (deduplicate by unique key)
- Migrate brands (deduplicate by name/email/slug)
- Build brand ID mapping: `source_id → target_id`
- Migrate brand relations (remap brand_id)
- Migrate other tables

### Phase 4: Verification
- Check orphaned records (FK tidak valid)
- Check duplicate active claims per brand
- Display summary: tables, columns, records, errors
- Display brand ID mapping
- Save log file

---

## Recommended Workflow

### Development Environment:
```bash
# 1. Preview changes
php db_merge_sync.php --dry-run

# 2. Sync schema only
php db_merge_sync.php --phase=2

# 3. Test brand migration
php db_merge_sync.php --table=brands

# 4. Test brand relations
php db_merge_sync.php --table=brand_claims

# 5. Full migration
php db_merge_sync.php
```

### Production Environment:
```bash
# 1. Manual backup first
mysqldump -u root -p holasync_toopai_before_merge > manual_backup.sql

# 2. Dry run in production
php db_merge_sync.php --dry-run

# 3. Execute with auto-backup
php db_merge_sync.php

# 4. Verify results
tail -f logs/merge_report_*.log
```

---

## Post-Migration Checklist

- [ ] Review log file di `logs/merge_report_[timestamp].log`
- [ ] Check no orphaned records reported
- [ ] Check no duplicate active claims
- [ ] Verify brand count: `SELECT COUNT(*) FROM brands`
- [ ] Test aplikasi dengan TARGET database
- [ ] Review column definition warnings (manual fix if needed)
- [ ] Backup final state setelah migration sukses

---

## Support

Jika ada issue atau pertanyaan:
1. Review log file lengkap
2. Jalankan dengan `--dry-run` dan `--table=PROBLEMATIC_TABLE`
3. Check error detail di log
4. Verify database credentials di `.env.merge`

---

## License

Internal tool untuk ToopAI database migration.
