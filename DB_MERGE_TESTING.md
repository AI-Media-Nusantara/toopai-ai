# Database Merge Tool - Testing Guide

## Pre-requisites Check

```bash
# Check PHP version (need 7.4+)
php -v

# Check mysqli extension
php -m | grep mysqli

# Check mysqldump available
which mysqldump
mysqldump --version

# Check disk space (need 3x database size)
df -h .

# Check permissions
ls -la db_merge_sync.php
# Should be: -rwxr-xr-x (executable)
```

---

## Test 1: Syntax & Help

```bash
# Syntax check
php -l db_merge_sync.php
# Expected: "No syntax errors detected"

# Help display
php db_merge_sync.php --help
# Expected: Usage guide displayed
```

**Status:** ✅ PASS

---

## Test 2: Configuration

```bash
# Copy config
cp .env.merge.example .env.merge

# Edit with real credentials
nano .env.merge

# Test connection (will fail if DB not exist, that's OK)
php db_merge_sync.php --dry-run 2>&1 | head -20
# Expected: "Connecting to SOURCE database" or connection error
```

**Status:** ⏳ Pending (requires database setup)

---

## Test 3: Dry Run (Full)

```bash
php db_merge_sync.php --dry-run
```

**Expected Output:**
```
=== DATABASE MERGE TOOL STARTED ===
Mode: DRY RUN (no actual changes)
Connecting to SOURCE database: holasync_dev_toopai_before_merge
Connecting to TARGET database: holasync_toopai_before_merge
✓ Database connections established

=== PHASE 1: PRE-FLIGHT CHECKS & BACKUP ===
[DRY-RUN] Would create backup of TARGET database

=== PHASE 2: SCHEMA SYNCHRONIZATION ===
--- Running Custom Migrations ---
→ Executing: 001_add_creator_gmv_cache_columns.sql
[DRY-RUN] Would execute migration: ...

--- Processing table: brands ---
→ Schema already in sync
...

=== PHASE 3: DATA MIGRATION ===
--- Migrating data: brands ---
→ Migrating brands with deduplication...
[DRY-RUN] Would insert brand: ...
...

=== PHASE 4: POST-SYNC VERIFICATION ===
--- Foreign Key Integrity Check ---
✓ No orphaned records in brand_claims
...

=== EXECUTION SUMMARY ===
Tables created: 0
Columns added: 0
Records inserted: 0
...

✓ Log saved to: logs/merge_report_*.log
```

**Checks:**
- [ ] No fatal errors
- [ ] All 4 phases executed
- [ ] Log file created
- [ ] "DRY RUN" mode respected (no actual changes)

---

## Test 4: Schema Sync Only

```bash
php db_merge_sync.php --phase=2
```

**Expected:**
- Backup created
- Custom migrations executed
- New tables created (if any)
- New columns added (if any)
- New indexes added (if any)
- No data migration

**Verification:**
```sql
-- Check new columns exist
SHOW COLUMNS FROM creators LIKE 'tap_%';
-- Expected: 5 rows (tap_live_pct, tap_video_pct, tap_product_card_pct, tap_gmv_total, tap_gmv_synced_at)

-- Check index created
SHOW INDEXES FROM creators WHERE Key_name = 'idx_tap_gmv_synced';
-- Expected: 1 row
```

**Checks:**
- [ ] Backup file created in `backups/`
- [ ] New columns added to TARGET
- [ ] Log file shows details
- [ ] No errors

---

## Test 5: Single Table Migration

```bash
php db_merge_sync.php --table=brands --skip-backup
```

**Expected:**
- Schema sync for `brands` table only
- Brand deduplication logic executed
- Brand ID mapping created
- No other tables touched

**Verification:**
```sql
-- Count brands in SOURCE
SELECT COUNT(*) FROM holasync_dev_toopai_before_merge.brands;

-- Count brands in TARGET (before)
SELECT COUNT(*) FROM holasync_toopai_before_merge.brands;

-- Count brands in TARGET (after)
SELECT COUNT(*) FROM holasync_toopai_before_merge.brands;
-- Expected: increased by number of new brands from SOURCE
```

**Checks:**
- [ ] No duplicate brands created
- [ ] Log shows "X matched, Y inserted"
- [ ] Brand ID mapping logged

---

## Test 6: Brand Relations Migration

```bash
php db_merge_sync.php --table=brand_claims --skip-backup
```

**Expected:**
- FK remapping using brand ID mapping
- Duplicate check before insert
- Orphaned records skipped

**Verification:**
```sql
-- Check no orphaned claims
SELECT COUNT(*) FROM brand_claims c
LEFT JOIN brands b ON c.brand_id = b.id
WHERE b.id IS NULL;
-- Expected: 0

-- Check no duplicate active claims
SELECT brand_id, COUNT(*) FROM brand_claims
WHERE status = 'active'
GROUP BY brand_id
HAVING COUNT(*) > 1;
-- Expected: 0 rows
```

**Checks:**
- [ ] No orphaned records
- [ ] No duplicate active claims
- [ ] FK remapping logged

---

## Test 7: Full Migration

```bash
php db_merge_sync.php
```

**Expected:**
- All 4 phases executed
- Backup created
- Schema synced
- Data migrated
- Verification passed
- Summary logged

**Verification:**
```sql
-- Check all creators have valid IDs
SELECT COUNT(*) FROM creators WHERE id IS NULL;
-- Expected: 0

-- Check all brand_claims reference existing brands
SELECT COUNT(*) FROM brand_claims c
LEFT JOIN brands b ON c.brand_id = b.id
WHERE b.id IS NULL;
-- Expected: 0

-- Check all brand_products reference existing brands & products
SELECT COUNT(*) FROM brand_products bp
LEFT JOIN brands b ON bp.brand_id = b.id
WHERE b.id IS NULL;
-- Expected: 0
```

**Checks:**
- [ ] Backup file exists & has reasonable size
- [ ] Log file shows 0 errors
- [ ] All stats reasonable (inserted > 0, skipped > 0)
- [ ] No orphaned records reported
- [ ] Application works with merged database

---

## Test 8: Idempotency (Run Twice)

```bash
# First run
php db_merge_sync.php --skip-backup

# Second run (should skip all duplicates)
php db_merge_sync.php --skip-backup
```

**Expected (Second Run):**
```
=== EXECUTION SUMMARY ===
Tables created: 0
Columns added: 0
Records inserted: 0
Records skipped: [large number]
Errors: 0
```

**Checks:**
- [ ] Second run inserts 0 new records
- [ ] All records skipped as duplicates
- [ ] No errors
- [ ] Database unchanged after second run

---

## Test 9: Error Recovery

### 9.1 Connection Error
```bash
# Break TARGET credentials in .env.merge
nano .env.merge  # Set wrong password

php db_merge_sync.php
```

**Expected:**
```
[FATAL ERROR] TARGET connection failed: Access denied for user...
```

**Checks:**
- [ ] Graceful error message
- [ ] No partial changes
- [ ] Exit code non-zero

### 9.2 Missing Table
```bash
# Try to sync non-existent table
php db_merge_sync.php --table=non_existent_table
```

**Expected:**
```
⚠ Table 'non_existent_table' not found in SOURCE
```

**Checks:**
- [ ] Warning logged
- [ ] No crash
- [ ] Continue execution

---

## Test 10: Performance

```bash
# Run with time measurement
time php db_merge_sync.php --skip-backup

# Check execution time
# Expected: < 10 minutes for typical dataset
```

**Checks:**
- [ ] Execution time reasonable
- [ ] Memory usage < 512MB (check log)
- [ ] No timeout errors

---

## Rollback Test

```bash
# Find latest backup
ls -lt backups/ | head -1

# Restore
BACKUP_FILE="backups/holasync_toopai_before_merge_backup_TIMESTAMP.sql"
mysql -u root -p holasync_toopai_before_merge < $BACKUP_FILE

# Verify restoration
mysql -u root -p holasync_toopai_before_merge -e "SELECT COUNT(*) FROM brands;"
```

**Checks:**
- [ ] Backup file valid SQL
- [ ] Restoration successful
- [ ] Data matches pre-migration state

---

## Edge Cases

### Case 1: NULL Values
```sql
-- Insert brand with NULL fields in SOURCE
INSERT INTO holasync_dev_toopai_before_merge.brands (name, email)
VALUES ('Test Brand', NULL);

-- Run migration
php db_merge_sync.php --table=brands --skip-backup

-- Check NULL preserved
SELECT * FROM holasync_toopai_before_merge.brands WHERE name = 'Test Brand';
```

**Expected:** NULL values preserved, not converted to empty string.

### Case 2: Unicode/Emoji
```sql
-- Insert brand with emoji in SOURCE
INSERT INTO holasync_dev_toopai_before_merge.brands (name)
VALUES ('Brand 🚀 Emoji');

-- Run migration
php db_merge_sync.php --table=brands --skip-backup

-- Check emoji preserved
SELECT * FROM holasync_toopai_before_merge.brands WHERE name LIKE '%🚀%';
```

**Expected:** Emoji preserved (utf8mb4 charset).

### Case 3: Large Text Fields
```sql
-- Insert brand with large description (10MB)
INSERT INTO holasync_dev_toopai_before_merge.brands (name, description)
VALUES ('Large Brand', REPEAT('Lorem ipsum ', 100000));

-- Run migration
php db_merge_sync.php --table=brands --skip-backup
```

**Expected:** Large text migrated without truncation.

---

## Acceptance Criteria

### Must Pass:
- [x] Test 1: Syntax & Help
- [ ] Test 3: Dry Run (Full)
- [ ] Test 4: Schema Sync Only
- [ ] Test 5: Single Table Migration
- [ ] Test 7: Full Migration
- [ ] Test 8: Idempotency

### Should Pass:
- [ ] Test 6: Brand Relations Migration
- [ ] Test 9: Error Recovery
- [ ] Test 10: Performance

### Nice to Have:
- [ ] Rollback Test
- [ ] Edge Cases

---

## Test Results Template

```markdown
## Test Execution: [DATE]

**Environment:**
- PHP Version: 
- MySQL Version: 
- OS: 
- SOURCE DB Size: 
- TARGET DB Size: 

**Results:**
| Test | Status | Notes |
|------|--------|-------|
| Syntax & Help | ✅ | - |
| Configuration | ✅ | - |
| Dry Run | ✅ | - |
| Schema Sync | ✅ | Added 5 columns to creators |
| Single Table | ✅ | 10 brands matched, 5 inserted |
| Brand Relations | ✅ | No orphans detected |
| Full Migration | ✅ | Completed in 4m 32s |
| Idempotency | ✅ | 0 records inserted on 2nd run |
| Error Recovery | ✅ | Graceful error handling |
| Performance | ✅ | 4m 32s for 10k records |

**Issues Found:**
- None

**Conclusion:**
✅ READY FOR PRODUCTION
```

---

## Production Testing Checklist

Before production deployment:

- [ ] All tests passed in staging
- [ ] Manual backup created: `manual_backup_[date].sql`
- [ ] Disk space verified (3x database size available)
- [ ] `.env.merge` configured with production credentials
- [ ] Maintenance window scheduled
- [ ] Stakeholders notified
- [ ] Rollback procedure documented
- [ ] Post-migration verification queries prepared
- [ ] Application test plan prepared

During production deployment:

- [ ] Dry-run executed on production
- [ ] Log reviewed for warnings
- [ ] Full migration executed
- [ ] Backup verified (file size reasonable)
- [ ] Log shows 0 errors
- [ ] Integrity checks passed
- [ ] Application smoke test passed

Post-deployment:

- [ ] Log file archived
- [ ] Backup file archived (30-day retention)
- [ ] Performance monitored (24 hours)
- [ ] Business logic verified
- [ ] Stakeholders notified (success)

---

## Support Contacts

If issues arise during testing:
1. Check log file first: `logs/merge_report_*.log`
2. Review relevant test case above
3. Check README_DB_MERGE.md for troubleshooting
4. Escalate with full error details + log file
