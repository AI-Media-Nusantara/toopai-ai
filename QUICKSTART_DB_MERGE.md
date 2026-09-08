# Quick Start Guide - Database Merge

## 5-Minute Setup

### 1. Setup Config
```bash
cp .env.merge.example .env.merge
nano .env.merge  # Edit dengan kredensial database Anda
```

### 2. Dry Run (Preview)
```bash
php db_merge_sync.php --dry-run
```

### 3. Execute
```bash
php db_merge_sync.php
```

---

## Common Commands

```bash
# Preview tanpa eksekusi
php db_merge_sync.php --dry-run

# Sync schema saja (no data)
php db_merge_sync.php --phase=2

# Test satu tabel
php db_merge_sync.php --table=brands

# Full migration
php db_merge_sync.php

# Full migration tanpa auto-backup
php db_merge_sync.php --skip-backup
```

---

## Recommended Step-by-Step

### First Time:
```bash
# Step 1: Preview
php db_merge_sync.php --dry-run

# Step 2: Schema only
php db_merge_sync.php --phase=2

# Step 3: Test brands
php db_merge_sync.php --table=brands --skip-backup

# Step 4: Full migration
php db_merge_sync.php
```

### Subsequent Runs (Safe Idempotent):
```bash
php db_merge_sync.php --skip-backup
```

---

## What to Check After Execution

1. **Log file:**
   ```bash
   tail -100 logs/merge_report_*.log
   ```

2. **Backup created:**
   ```bash
   ls -lh backups/
   ```

3. **Stats summary** (displayed at end of execution):
   - Tables created
   - Columns added
   - Records inserted
   - Records skipped (duplicates)
   - Errors (should be 0)

4. **Verify in MySQL:**
   ```sql
   -- Check new columns exist
   SHOW COLUMNS FROM brands LIKE 'tap_%';
   
   -- Check brand count
   SELECT COUNT(*) FROM brands;
   
   -- Check no orphans
   SELECT COUNT(*) FROM brand_claims c
   LEFT JOIN brands b ON c.brand_id = b.id
   WHERE b.id IS NULL;
   ```

---

## Troubleshooting Quick Fixes

### "Connection failed"
Check `.env.merge` credentials and database access.

### "Column already exists"
Normal — script is idempotent. Will skip automatically.

### "Duplicate entry"
Normal — script will skip existing records.

### "Foreign key constraint fails"
Run phase by phase:
```bash
php db_merge_sync.php --phase=2  # Schema first
php db_merge_sync.php --phase=3  # Then data
```

---

## Emergency Rollback

If something goes wrong:

```bash
# Find latest backup
ls -lt backups/ | head -5

# Restore
mysql -u root -p holasync_toopai_before_merge < backups/holasync_toopai_before_merge_backup_TIMESTAMP.sql
```

---

## Need More Details?

Read full documentation: [README_DB_MERGE.md](README_DB_MERGE.md)
