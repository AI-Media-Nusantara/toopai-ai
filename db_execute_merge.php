#!/usr/bin/env php
<?php
/**
 * db_execute_merge.php
 * Eksekusi merge: backup → snapshot → insert → alter → verify
 */
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', 0);
ini_set('memory_limit', '512M');
set_time_limit(0);

$env    = parse_ini_file('.env.merge');
$src_db = $env['SOURCE_DB'];
$tgt_db = $env['TARGET_DB'];

$tgt = new mysqli($env['TARGET_HOST'], $env['TARGET_USER'], $env['TARGET_PASS'], $tgt_db);
if ($tgt->connect_error) die("[FATAL] TARGET connect failed: " . $tgt->connect_error . "\n");
$tgt->set_charset('utf8mb4');

$src = new mysqli($env['SOURCE_HOST'], $env['SOURCE_USER'], $env['SOURCE_PASS'], $src_db);
if ($src->connect_error) die("[FATAL] SOURCE connect failed: " . $src->connect_error . "\n");
$src->set_charset('utf8mb4');

function log_msg(string $msg, string $level = 'INFO'): void {
    $ts = date('H:i:s');
    echo "[$ts][$level] $msg\n";
}

function abort(string $msg): never {
    log_msg($msg, 'FATAL');
    log_msg("Eksekusi dihentikan. Tidak ada perubahan di production.", 'FATAL');
    exit(1);
}

// ─────────────────────────────────────────────────────────────────────────────
// STEP 1: BACKUP (PHP-based, karena mysqldump tidak tersedia di mesin ini)
// ─────────────────────────────────────────────────────────────────────────────
log_msg("=== STEP 1: BACKUP TARGET DATABASE ===");

if (!is_dir('backups')) mkdir('backups', 0755, true);
$backup_file = "backups/{$tgt_db}_backup_" . date('Ymd_His') . ".sql";

log_msg("Membuat backup PHP-based: $backup_file");

$fp = fopen($backup_file, 'w');
if (!$fp) abort("Tidak bisa membuat file backup: $backup_file");

fwrite($fp, "-- Backup: $tgt_db\n");
fwrite($fp, "-- Created: " . date('Y-m-d H:i:s') . "\n");
fwrite($fp, "-- Method: PHP mysqli (mysqldump not available)\n");
fwrite($fp, "-- Scope: Full dump — schema + data\n\n");
fwrite($fp, "SET FOREIGN_KEY_CHECKS = 0;\n");
fwrite($fp, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n\n");

$tables_r = $tgt->query("SHOW TABLES");
$all_tables = [];
while ($row = $tables_r->fetch_array()) $all_tables[] = $row[0];

foreach ($all_tables as $table) {
    // Schema
    $create_r = $tgt->query("SHOW CREATE TABLE `$table`");
    $create_row = $create_r->fetch_assoc();
    fwrite($fp, "-- Table: $table\n");
    fwrite($fp, "DROP TABLE IF EXISTS `$table`;\n");
    fwrite($fp, $create_row['Create Table'] . ";\n\n");

    // Data
    $data_r = $tgt->query("SELECT * FROM `$table`");
    if (!$data_r || $data_r->num_rows === 0) continue;

    $fields_r = $tgt->query("SHOW COLUMNS FROM `$table`");
    $fields = [];
    while ($f = $fields_r->fetch_assoc()) $fields[] = "`{$f['Field']}`";
    $fields_str = implode(', ', $fields);

    $batch = [];
    $batch_size = 100;
    while ($data_row = $data_r->fetch_assoc()) {
        $vals = array_map(function($v) use ($tgt) {
            return $v === null ? 'NULL' : "'" . $tgt->real_escape_string($v) . "'";
        }, array_values($data_row));
        $batch[] = "(" . implode(', ', $vals) . ")";

        if (count($batch) >= $batch_size) {
            fwrite($fp, "INSERT INTO `$table` ($fields_str) VALUES\n  " . implode(",\n  ", $batch) . ";\n");
            $batch = [];
        }
    }
    if (!empty($batch)) {
        fwrite($fp, "INSERT INTO `$table` ($fields_str) VALUES\n  " . implode(",\n  ", $batch) . ";\n");
    }
    fwrite($fp, "\n");
}

fwrite($fp, "SET FOREIGN_KEY_CHECKS = 1;\n");
fwrite($fp, "-- End of backup\n");
fclose($fp);

$size_mb = number_format(filesize($backup_file) / 1024 / 1024, 2);
log_msg("✅ Backup selesai: $backup_file ($size_mb MB)");


// ─────────────────────────────────────────────────────────────────────────────
// STEP 2: SNAPSHOT SEBELUM MERGE
// ─────────────────────────────────────────────────────────────────────────────
log_msg("");
log_msg("=== STEP 2: SNAPSHOT SEBELUM MERGE ===");

$snap_brands_before    = $tgt->query("SELECT COUNT(*) as c FROM `brands`")->fetch_assoc()['c'];
$snap_max_id_before    = $tgt->query("SELECT MAX(id) as m FROM `brands`")->fetch_assoc()['m'];
$snap_auto_inc_before  = $tgt->query("SELECT AUTO_INCREMENT as a FROM information_schema.TABLES WHERE TABLE_SCHEMA='$tgt_db' AND TABLE_NAME='brands'")->fetch_assoc()['a'];

// Conflict check — WAJIB 0
$conflict = $tgt->query("SELECT COUNT(*) as c FROM `brands` WHERE id BETWEEN 8595 AND 8660")->fetch_assoc()['c'];

log_msg("brands COUNT sebelum  : $snap_brands_before  (expected: 4513)");
log_msg("brands MAX(id) sebelum: $snap_max_id_before  (expected: 8594)");
log_msg("brands AUTO_INCREMENT : $snap_auto_inc_before (expected: 8595)");
log_msg("PK conflict check     : $conflict  (HARUS 0)");

if ((int)$conflict !== 0) {
    abort("Ada $conflict ID conflict di range 8595-8660! Eksekusi dibatalkan.");
}
if ((int)$snap_brands_before !== 4513) {
    log_msg("⚠️  COUNT brands bukan 4513 (got $snap_brands_before). Lanjut dengan jumlah aktual.", 'WARN');
}

log_msg("✅ Pre-flight check lulus — tidak ada konflik PK");


// ─────────────────────────────────────────────────────────────────────────────
// STEP 3: INSERT 66 BRANDS
// ─────────────────────────────────────────────────────────────────────────────
log_msg("");
log_msg("=== STEP 3: INSERT 66 BRANDS ===");

// Ambil 66 brands dari SOURCE yang belum ada di TARGET
$tgt_cols_r = $tgt->query("SHOW COLUMNS FROM `brands`");
$tgt_cols   = [];
while ($row = $tgt_cols_r->fetch_assoc()) $tgt_cols[] = $row['Field'];

$missing_r = $src->query("
    SELECT s.*
    FROM `brands` s
    LEFT JOIN `{$tgt_db}`.`brands` t ON s.id = t.id
    WHERE t.id IS NULL
    ORDER BY s.id
");

$inserted   = 0;
$skipped    = 0;
$errors     = [];
$insert_ids = [];

$tgt->query("SET FOREIGN_KEY_CHECKS = 0");
$tgt->begin_transaction();

try {
    while ($row = $missing_r->fetch_assoc()) {
        $brand_id = (int)$row['id'];
        $insert_ids[] = $brand_id;

        // Filter hanya kolom yang ada di TARGET
        $cols = [];
        $vals = [];
        foreach ($tgt_cols as $col) {
            $val = isset($row[$col]) ? $row[$col] : null;
            $cols[] = "`$col`";
            $vals[] = $val === null ? 'NULL' : "'" . $tgt->real_escape_string($val) . "'";
        }

        $sql = "INSERT IGNORE INTO `brands` (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ")";

        if ($tgt->query($sql)) {
            if ($tgt->affected_rows > 0) {
                $inserted++;
                log_msg("  ✓ Inserted id=$brand_id name=" . ($row['name'] ?? 'NULL') . " status=" . ($row['status'] ?? 'NULL'));
            } else {
                $skipped++;
                log_msg("  ~ Skipped id=$brand_id (already exists — INSERT IGNORE)", 'WARN');
            }
        } else {
            $errors[] = "id=$brand_id: " . $tgt->error;
            log_msg("  ✗ Error id=$brand_id: " . $tgt->error, 'ERROR');
        }
    }

    if (!empty($errors)) {
        $tgt->rollback();
        $tgt->query("SET FOREIGN_KEY_CHECKS = 1");
        abort("Ada " . count($errors) . " error saat INSERT. ROLLBACK dilakukan:\n" . implode("\n", $errors));
    }

    $tgt->commit();
    log_msg("✅ COMMIT berhasil — inserted=$inserted, skipped=$skipped, errors=0");

} catch (Exception $e) {
    $tgt->rollback();
    $tgt->query("SET FOREIGN_KEY_CHECKS = 1");
    abort("Exception: " . $e->getMessage());
}

$tgt->query("SET FOREIGN_KEY_CHECKS = 1");


// ─────────────────────────────────────────────────────────────────────────────
// STEP 4: ALTER AUTO_INCREMENT
// ─────────────────────────────────────────────────────────────────────────────
log_msg("");
log_msg("=== STEP 4: ALTER AUTO_INCREMENT = 8661 ===");

if ($tgt->query("ALTER TABLE `brands` AUTO_INCREMENT = 8661")) {
    log_msg("✅ AUTO_INCREMENT brands disetel ke 8661");
} else {
    log_msg("⚠️  ALTER AUTO_INCREMENT gagal: " . $tgt->error . " (tidak fatal)", 'WARN');
}


// ─────────────────────────────────────────────────────────────────────────────
// STEP 5: VERIFIKASI SESUDAH MERGE
// ─────────────────────────────────────────────────────────────────────────────
log_msg("");
log_msg("=== STEP 5: VERIFIKASI HASIL MERGE ===");

$snap_brands_after   = (int)$tgt->query("SELECT COUNT(*) as c FROM `brands`")->fetch_assoc()['c'];
$snap_max_id_after   = $tgt->query("SELECT MAX(id) as m FROM `brands`")->fetch_assoc()['m'];
$snap_auto_inc_after = $tgt->query("SELECT AUTO_INCREMENT as a FROM information_schema.TABLES WHERE TABLE_SCHEMA='$tgt_db' AND TABLE_NAME='brands'")->fetch_assoc()['a'];
$expected_after      = (int)$snap_brands_before + $inserted;

// B1: Count
$b1_ok = $snap_brands_after === $expected_after;
log_msg("B1 brands COUNT sesudah : $snap_brands_after (expected: $expected_after) " . ($b1_ok ? '✅' : '❌'));

// B2: Brands baru exist
$id_list_str = implode(',', $insert_ids);
$new_brands_count = (int)$tgt->query("SELECT COUNT(*) as c FROM `brands` WHERE id IN ($id_list_str)")->fetch_assoc()['c'];
$b2_ok = $new_brands_count === $inserted;
log_msg("B2 brands baru di TARGET: $new_brands_count (expected: $inserted) " . ($b2_ok ? '✅' : '❌'));

// B3: MAX id
log_msg("B3 brands MAX(id)       : $snap_max_id_after (expected: 8660)");

// B4: AUTO_INCREMENT
log_msg("B4 AUTO_INCREMENT       : $snap_auto_inc_after (expected: 8661)");

// B5: Data production lama utuh (id <= 8594)
$old_count = (int)$tgt->query("SELECT COUNT(*) as c FROM `brands` WHERE id <= 8594")->fetch_assoc()['c'];
$b5_ok = $old_count === (int)$snap_brands_before;
log_msg("B5 Data lama utuh       : $old_count (expected: $snap_brands_before) " . ($b5_ok ? '✅' : '❌'));

// B6: FK integrity — whatsapp_logs orphan
$orphan_wlog = (int)$tgt->query("
    SELECT COUNT(*) as c FROM `whatsapp_logs` w
    LEFT JOIN `brands` b ON w.brand_id = b.id
    WHERE w.brand_id IS NOT NULL AND b.id IS NULL
")->fetch_assoc()['c'];
$b6_ok = $orphan_wlog === 0;
log_msg("B6 Orphan whatsapp_logs : $orphan_wlog (expected: 0) " . ($b6_ok ? '✅' : '⚠️'));

// B7: FK integrity — brand_creators orphan
$orphan_bc = (int)$tgt->query("
    SELECT COUNT(*) as c FROM `brand_creators` bc
    LEFT JOIN `brands` b ON bc.brand_id = b.id
    WHERE b.id IS NULL
")->fetch_assoc()['c'];
log_msg("B7 Orphan brand_creators: $orphan_bc (expected: 0) " . ($orphan_bc === 0 ? '✅' : '⚠️'));

// B8: FK integrity — creator_scouting
$orphan_cs = (int)$tgt->query("
    SELECT COUNT(*) as c FROM `creator_scouting` cs
    LEFT JOIN `brands` b ON cs.brand_id = b.id
    WHERE b.id IS NULL
")->fetch_assoc()['c'];
log_msg("B8 Orphan creator_scout : $orphan_cs (expected: 0) " . ($orphan_cs === 0 ? '✅' : '⚠️'));

// B9: FK integrity — sample_requests
$orphan_sr = (int)$tgt->query("
    SELECT COUNT(*) as c FROM `sample_requests` sr
    LEFT JOIN `brands` b ON sr.brand_id = b.id
    WHERE b.id IS NULL
")->fetch_assoc()['c'];
log_msg("B9 Orphan sample_req    : $orphan_sr (expected: 0) " . ($orphan_sr === 0 ? '✅' : '⚠️'));


// ─────────────────────────────────────────────────────────────────────────────
// LAPORAN AKHIR
// ─────────────────────────────────────────────────────────────────────────────
log_msg("");
log_msg("=== LAPORAN AKHIR ===");
log_msg("┌─────────────────────────────────────────────────┐");
log_msg("│               HASIL MERGE                       │");
log_msg("├──────────────────────────┬───────────┬──────────┤");
log_msg("│ Metrik                   │ Sebelum   │ Sesudah  │");
log_msg("├──────────────────────────┼───────────┼──────────┤");
log_msg(sprintf("│ brands COUNT             │ %-9s │ %-8s │", $snap_brands_before, $snap_brands_after));
log_msg(sprintf("│ brands MAX(id)           │ %-9s │ %-8s │", $snap_max_id_before, $snap_max_id_after));
log_msg(sprintf("│ brands AUTO_INCREMENT    │ %-9s │ %-8s │", $snap_auto_inc_before, $snap_auto_inc_after));
log_msg("├──────────────────────────┼───────────┼──────────┤");
log_msg(sprintf("│ Brands diinsert          │ %-9s │ %-8s │", '-', $inserted));
log_msg(sprintf("│ Brands di-skip           │ %-9s │ %-8s │", '-', $skipped));
log_msg(sprintf("│ Orphan whatsapp_logs     │ '?'       │ %-8s │", $orphan_wlog));
log_msg("└──────────────────────────┴───────────┴──────────┘");

$all_ok = $b1_ok && $b2_ok && $b5_ok && $b6_ok && ($orphan_bc === 0) && ($orphan_cs === 0) && ($orphan_sr === 0);
log_msg("");
if ($all_ok) {
    log_msg("✅✅✅ MERGE BERHASIL — semua verifikasi lulus ✅✅✅");
} else {
    log_msg("⚠️  MERGE SELESAI DENGAN PERINGATAN — periksa item yang bertanda ❌/⚠️ di atas", 'WARN');
}
log_msg("Backup tersimpan di: $backup_file");

$src->close();
$tgt->close();
