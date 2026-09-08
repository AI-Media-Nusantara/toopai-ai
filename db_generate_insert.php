#!/usr/bin/env php
<?php
/**
 * Generate 03_insert_data.sql
 * Menghasilkan INSERT statements yang aman untuk 66 brands
 * yang ada di SOURCE tapi belum ada di TARGET
 */
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', 0);
ini_set('memory_limit', '256M');

$env     = parse_ini_file('.env.merge');
$src_db  = $env['SOURCE_DB'];
$tgt_db  = $env['TARGET_DB'];

$src = new mysqli($env['SOURCE_HOST'], $env['SOURCE_USER'], $env['SOURCE_PASS'], $src_db);
$tgt = new mysqli($env['TARGET_HOST'], $env['TARGET_USER'], $env['TARGET_PASS'], $tgt_db);
$src->set_charset('utf8mb4');
$tgt->set_charset('utf8mb4');

// ── Helpers ──────────────────────────────────────────────────────────────────
function quote($conn, $val) {
    if ($val === null) return 'NULL';
    return "'" . $conn->real_escape_string($val) . "'";
}

function row_to_insert($conn, $table, $row, $exclude_cols = []) {
    $cols = [];
    $vals = [];
    foreach ($row as $col => $val) {
        if (in_array($col, $exclude_cols)) continue;
        $cols[] = "`$col`";
        $vals[] = quote($conn, $val);
    }
    return "INSERT INTO `$table` (" . implode(', ', $cols) . ")\n  VALUES (" . implode(', ', $vals) . ");";
}

// ── Ambil semua kolom TARGET brands ──────────────────────────────────────────
$tgt_cols_r = $tgt->query("SHOW COLUMNS FROM `brands`");
$tgt_cols   = [];
while ($row = $tgt_cols_r->fetch_assoc()) $tgt_cols[] = $row['Field'];

// ── Ambil 66 brands yang hilang ───────────────────────────────────────────────
$missing = $src->query("
    SELECT s.*
    FROM `brands` s
    LEFT JOIN `{$tgt_db}`.`brands` t ON s.id = t.id
    WHERE t.id IS NULL
    ORDER BY s.id
");

$missing_ids  = [];
$insert_rows  = [];
while ($row = $missing->fetch_assoc()) {
    $missing_ids[] = (int)$row['id'];

    // Hanya ambil kolom yang ada di TARGET
    $filtered = [];
    foreach ($tgt_cols as $col) {
        $filtered[$col] = isset($row[$col]) ? $row[$col] : null;
    }
    $insert_rows[] = $filtered;
}

$total        = count($insert_rows);
$id_list_str  = implode(', ', $missing_ids);
$min_id       = min($missing_ids);
$max_id       = max($missing_ids);

// ── Tulis file SQL ────────────────────────────────────────────────────────────
$out = [];

$out[] = "-- =============================================================================";
$out[] = "-- FILE: 03_insert_data.sql";
$out[] = "-- Memasukkan data brands dari SOURCE yang belum ada di TARGET";
$out[] = "-- Source : $src_db";
$out[] = "-- Target : $tgt_db";
$out[] = "-- Generated: " . date('Y-m-d H:i:s');
$out[] = "-- =============================================================================";
$out[] = "-- EKSEKUSI:";
$out[] = "--   Jalankan 02_alter_schema.sql DULU, lalu file ini.";
$out[] = "--   Pastikan backup sudah dibuat sebelum eksekusi.";
$out[] = "--";
$out[] = "-- DATA YANG AKAN DIINSERT:";
$out[] = "--   Tabel  : brands";
$out[] = "--   Jumlah : $total baris (id $min_id — $max_id)";
$out[] = "--   Kunci  : INSERT dengan original ID (tidak ada konflik PK)";
$out[] = "--   Child  : Tidak ada child records di SOURCE untuk brand-brand ini";
$out[] = "--            (brand_creators=0, brand_products=0, creator_scouting=0,";
$out[] = "--             sample_requests=0, bd_task_progress=0, brand_contacts=0)";
$out[] = "--   Note   : whatsapp_logs.brand_id=8656 (LVK outfit) akan otomatis valid";
$out[] = "--            setelah brand id=8656 diinsert";
$out[] = "-- =============================================================================";
$out[] = "";
$out[] = "USE `{$tgt_db}`;";
$out[] = "";

// ── Pre-flight checks ─────────────────────────────────────────────────────────
$out[] = "-- ---------------------------------------------------------------------------";
$out[] = "-- PRE-FLIGHT: Pastikan tidak ada konflik ID sebelum INSERT";
$out[] = "-- ---------------------------------------------------------------------------";
$out[] = "-- Jalankan query ini dulu. Expected: conflict_count = 0";
$out[] = "-- SELECT COUNT(*) AS conflict_count FROM `brands` WHERE id IN ($id_list_str);";
$out[] = "";
$out[] = "-- Count sebelum insert (catat untuk verifikasi):";
$out[] = "-- SELECT COUNT(*) AS brands_before FROM `brands`;";
$out[] = "-- Expected: 4513";
$out[] = "";

// ── START TRANSACTION ─────────────────────────────────────────────────────────
$out[] = "-- ---------------------------------------------------------------------------";
$out[] = "-- EKSEKUSI";
$out[] = "-- ---------------------------------------------------------------------------";
$out[] = "SET FOREIGN_KEY_CHECKS = 0;";
$out[] = "START TRANSACTION;";
$out[] = "";

// ── INSERT brands ─────────────────────────────────────────────────────────────
$out[] = "-- $total brands baru (id $min_id — $max_id)";
$out[] = "-- Menggunakan INSERT IGNORE agar idempotent (aman dijalankan ulang)";
$out[] = "";

foreach ($insert_rows as $i => $row) {
    $cols = array_keys($row);
    $vals = array_map(fn($v) => quote($tgt, $v), array_values($row));

    $out[] = "-- Brand #" . ($i + 1) . ": id={$row['id']} name=" . ($row['name'] ?? 'NULL') . " status=" . ($row['status'] ?? 'NULL');
    $out[] = "INSERT IGNORE INTO `brands`";
    $out[] = "  (" . implode(', ', array_map(fn($c) => "`$c`", $cols)) . ")";
    $out[] = "  VALUES";
    $out[] = "  (" . implode(', ', $vals) . ");";
    $out[] = "";
}

// ── Set AUTO_INCREMENT ────────────────────────────────────────────────────────
$out[] = "-- Setel AUTO_INCREMENT ke nilai setelah max SOURCE (8661)";
$out[] = "-- agar INSERT baru di production tidak bentrok dengan ID-ID dari dev";
$out[] = "ALTER TABLE `brands` AUTO_INCREMENT = 8661;";
$out[] = "";

// ── COMMIT ────────────────────────────────────────────────────────────────────
$out[] = "COMMIT;";
$out[] = "SET FOREIGN_KEY_CHECKS = 1;";
$out[] = "";

// ── Post-insert verification inline ──────────────────────────────────────────
$out[] = "-- ---------------------------------------------------------------------------";
$out[] = "-- POST-INSERT QUICK CHECK (jalankan setelah COMMIT)";
$out[] = "-- ---------------------------------------------------------------------------";
$out[] = "-- SELECT COUNT(*) AS brands_after FROM `brands`;";
$out[] = "-- Expected: 4579  (4513 + 66)";
$out[] = "";
$out[] = "-- Cek brands yang baru diinsert:";
$out[] = "-- SELECT id, name, status FROM `brands` WHERE id IN ($id_list_str) ORDER BY id;";
$out[] = "-- Expected: $total rows";
$out[] = "";
$out[] = "-- Cek whatsapp_logs orphan (harus 0 setelah insert):";
$out[] = "-- SELECT COUNT(*) FROM whatsapp_logs w";
$out[] = "--   LEFT JOIN brands b ON w.brand_id = b.id";
$out[] = "--   WHERE w.brand_id IS NOT NULL AND b.id IS NULL;";
$out[] = "";
$out[] = "-- =============================================================================";
$out[] = "-- END OF 03_insert_data.sql";
$out[] = "-- =============================================================================";

$sql = implode("\n", $out);
file_put_contents('03_insert_data.sql', $sql);
echo "[OK] File 03_insert_data.sql dibuat ($total INSERT statements)\n";
echo "[OK] ID range: $min_id — $max_id\n";
echo "[OK] Brands yang akan diinsert:\n";
foreach ($insert_rows as $r) {
    printf("     id=%-5d  status=%-15s  name=%s\n", $r['id'], $r['status'], $r['name']);
}
