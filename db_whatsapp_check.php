#!/usr/bin/env php
<?php
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', 0);
$env = parse_ini_file('.env.merge');
$src_db = $env['SOURCE_DB'];
$tgt_db = $env['TARGET_DB'];
$src = new mysqli($env['SOURCE_HOST'], $env['SOURCE_USER'], $env['SOURCE_PASS'], $src_db);
$tgt = new mysqli($env['TARGET_HOST'], $env['TARGET_USER'], $env['TARGET_PASS'], $tgt_db);
$src->set_charset('utf8mb4');
$tgt->set_charset('utf8mb4');

$new_ids = '8595,8596,8597,8598,8599,8600,8601,8602,8603,8604,8605,8606,8607,8608,8609,8610,8611,8612,8613,8614,8615,8616,8617,8618,8619,8620,8621,8622,8623,8624,8625,8626,8627,8628,8629,8630,8631,8632,8633,8634,8635,8636,8637,8638,8639,8640,8641,8642,8643,8644,8645,8646,8647,8648,8649,8650,8651,8652,8653,8654,8655,8656,8657,8658,8659,8660';

// SOURCE whatsapp_logs for new brand ids
echo "=== whatsapp_logs di SOURCE untuk brand baru ===\n";
$r = $src->query("SELECT id, brand_id FROM whatsapp_logs WHERE brand_id IN ($new_ids)");
while ($row = $r->fetch_assoc()) {
    $bname_r = $src->query("SELECT name FROM brands WHERE id = {$row['brand_id']}");
    $bname = $bname_r->fetch_assoc()['name'] ?? '?';
    echo "  wlog id={$row['id']} | brand_id={$row['brand_id']} ({$bname})\n";
}

// TARGET whatsapp_logs for same brand_ids
echo "\n=== whatsapp_logs di TARGET untuk brand_id yang sama ===\n";
$r2 = $tgt->query("SELECT COUNT(*) as c FROM whatsapp_logs WHERE brand_id IN ($new_ids)");
echo "  Count: " . $r2->fetch_assoc()['c'] . "\n";

// Remaining checks
echo "\n=== Konfirmasi: id 8595-8660 di TARGET ===\n";
$r4 = $tgt->query("SELECT MIN(id), MAX(id), COUNT(*) FROM brands WHERE id BETWEEN 8595 AND 8660");
$row4 = $r4->fetch_row();
echo "  MIN={$row4[0]}, MAX={$row4[1]}, COUNT={$row4[2]}\n";

// Final: confirm safe to INSERT with original IDs
echo "\n=== SAFE to INSERT brands 8595-8660 dengan original ID? ===\n";
$r5 = $tgt->query("SELECT COUNT(*) as c FROM brands WHERE id IN ($new_ids)");
$conflict = $r5->fetch_assoc()['c'];
if ($conflict == 0) {
    echo "  ✅ YA — tidak ada konflik PK di TARGET untuk ID-ID tersebut\n";
    echo "  → Dapat INSERT dengan INSERT IGNORE INTO ... SELECT ... FROM source WITH original IDs\n";
    echo "  → whatsapp_logs brand_id akan tetap valid karena brand ids dipertahankan\n";
} else {
    echo "  ❌ ADA KONFLIK: $conflict IDs sudah ada di TARGET\n";
}

// Get ENUM def for status di target
echo "\n=== STATUS ENUM di TARGET brands ===\n";
$r6 = $tgt->query("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$tgt_db' AND TABLE_NAME='brands' AND COLUMN_NAME='status'");
echo "  " . $r6->fetch_assoc()['COLUMN_TYPE'] . "\n";

echo "\n=== Status distribution pada brands yang akan diinsert ===\n";
$r7 = $src->query("SELECT status, COUNT(*) as c FROM brands WHERE id IN ($new_ids) GROUP BY status");
while ($row7 = $r7->fetch_assoc()) {
    echo "  status='{$row7['status']}': {$row7['c']}\n";
}

// Also check affiliate_creator_links showcase_status column diff
echo "\n=== affiliate_creator_links showcase_status: src vs tgt column def ===\n";
$r8 = $src->query("SELECT COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$src_db' AND TABLE_NAME='affiliate_creator_links' AND COLUMN_NAME='showcase_status'");
$src_def = $r8->fetch_assoc();
echo "  SOURCE: {$src_def['COLUMN_TYPE']} NULL={$src_def['IS_NULLABLE']} DEFAULT={$src_def['COLUMN_DEFAULT']}\n";

$r9 = $tgt->query("SELECT COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$tgt_db' AND TABLE_NAME='affiliate_creator_links' AND COLUMN_NAME='showcase_status'");
$tgt_def = $r9->fetch_assoc();
echo "  TARGET: {$tgt_def['COLUMN_TYPE']} NULL={$tgt_def['IS_NULLABLE']} DEFAULT={$tgt_def['COLUMN_DEFAULT']}\n";
