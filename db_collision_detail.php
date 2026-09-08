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

// ── 1. Range ID collision lengkap ───────────────────────────────────
echo "=== ID collision range ===\n";
$r = $src->query("
    SELECT s.id, s.name as src_name, t.name as tgt_name
    FROM brands s
    JOIN {$tgt_db}.brands t ON s.id = t.id
    WHERE s.name != t.name
    ORDER BY s.id
");
$collisions = [];
while ($row = $r->fetch_assoc()) {
    $collisions[] = $row;
}
$first_id = $collisions[0]['id'] ?? 'N/A';
$last_id  = end($collisions)['id'] ?? 'N/A';
echo "Total collision: " . count($collisions) . "\n";
echo "Range ID collision: $first_id — $last_id\n";

// Tunjukkan pola: apakah ada offset/shift?
echo "\nSample collision (10 pertama):\n";
foreach (array_slice($collisions, 0, 10) as $row) {
    echo "  id={$row['id']} | SOURCE:'{$row['src_name']}' vs TARGET:'{$row['tgt_name']}'\n";
}

// ── 2. Hitung berapa ID yang match persis di kedua sisi ─────────────
echo "\n=== ID yang sama & nama SAMA (data identik) ===\n";
$r = $src->query("
    SELECT COUNT(*) as c FROM brands s
    JOIN {$tgt_db}.brands t ON s.id = t.id
    WHERE s.name = t.name
");
$same = $r->fetch_assoc()['c'];
echo "Brands dengan id & name sama: $same\n";

// ── 3. Apakah brands target id=8595-8660 memang kosong? ─────────────
echo "\n=== Apakah id 8595-8660 ada di TARGET? ===\n";
$r = $tgt->query("SELECT COUNT(*) as c FROM brands WHERE id BETWEEN 8595 AND 8660");
$exists = $r->fetch_assoc()['c'];
echo "Brands id 8595-8660 di TARGET: $exists\n";

// ── 4. MAX id di TARGET sebelum collision zone ──────────────────────
echo "\n=== ID max bersih di TARGET (sebelum collision zone) ===\n";
$r = $tgt->query("SELECT MAX(id) as max_id FROM brands");
echo "MAX(id) di TARGET: " . $r->fetch_assoc()['max_id'] . "\n";

// ── 5. Cek apakah brands SOURCE id < 8465 ada di TARGET juga ────────
echo "\n=== ID di SOURCE id < 8465 yang TIDAK ada di TARGET ===\n";
$r = $src->query("
    SELECT COUNT(*) as c FROM brands s
    LEFT JOIN {$tgt_db}.brands t ON s.id = t.id
    WHERE t.id IS NULL AND s.id < 8465
");
echo "Brands id < 8465 di source tapi tidak di target: " . $r->fetch_assoc()['c'] . "\n";

// ── 6. Cek nama brands dari TARGET id 8465-8594 apakah ada di SOURCE dengan id beda ─
echo "\n=== Nama brands TARGET id>=8465 — apakah ada di SOURCE dengan id berbeda? (sample 20) ===\n";
$r = $tgt->query("SELECT id, name FROM brands WHERE id >= 8465 ORDER BY id LIMIT 20");
while ($row = $r->fetch_assoc()) {
    $name = $tgt->real_escape_string($row['name']);
    $r2 = $src->query("SELECT id FROM brands WHERE name = '$name' LIMIT 1");
    $src_row = $r2->fetch_assoc();
    $src_id = $src_row ? $src_row['id'] : 'NOT IN SOURCE';
    echo "  TGT id={$row['id']} name='{$row['name']}' → SRC id=$src_id\n";
}

// ── 7. Kesimpulan: apakah safe INSERT dengan id lama? ───────────────
echo "\n=== KESIMPULAN ID OVERLAP ===\n";
if (count($collisions) > 0) {
    echo "⚠️  ADA ID COLLISION: $first_id — $last_id\n";
    echo "   Artinya: SOURCE dan TARGET menggunakan ID yang sama untuk brand BERBEDA.\n";
    echo "   INSERT langsung dengan ID lama = TIDAK BISA karena akan konflik PK.\n";
    echo "   Strategi yang diperlukan: INSERT tanpa id (let auto_increment), tanpa child table remap\n";
    echo "   (karena brand-brand baru ini id 8595-8660 belum ada child di TARGET)\n\n";
    
    // Cek apakah 66 brands yang akan diinsert PUNYA child di target child tables
    $new_src_ids = [];
    $r = $src->query("SELECT id FROM brands WHERE id NOT IN (SELECT id FROM {$tgt_db}.brands)");
    while ($row = $r->fetch_assoc()) $new_src_ids[] = $row['id'];
    echo "   IDs yang akan diinsert (ada di SOURCE, tidak di TARGET): " . implode(',', $new_src_ids) . "\n\n";

    // Check child tables
    $child_tables_cols = [
        'brand_creators'       => 'brand_id',
        'brand_products'       => 'brand_id',
        'brand_contact_queue'  => 'brand_id',
        'brand_search_queue'   => 'brand_id',
        'creator_scouting'     => 'brand_id',
        'sample_requests'      => 'brand_id',
        'whatsapp_logs'        => 'brand_id',
        'bd_task_progress'     => 'brand_id',
        'creators'             => 'brand_id',
    ];

    $id_list = implode(',', $new_src_ids);
    echo "   Cek child records di SOURCE untuk id-id tersebut:\n";
    $has_children = false;
    foreach ($child_tables_cols as $ct => $col) {
        // check col exists
        $col_check = $src->query("SELECT COUNT(*) as c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$src_db' AND TABLE_NAME='$ct' AND COLUMN_NAME='$col'");
        if ($col_check->fetch_assoc()['c'] == 0) continue;
        $r2 = $src->query("SELECT COUNT(*) as c FROM `$ct` WHERE `$col` IN ($id_list)");
        $c2 = $r2->fetch_assoc()['c'];
        if ($c2 > 0) {
            echo "   ⚠️  $ct.$col: $c2 records\n";
            $has_children = true;
        } else {
            echo "   ✓  $ct.$col: 0 records\n";
        }
    }
    if (!$has_children) {
        echo "\n   ✅ Brands baru (8595-8660) TIDAK punya child records di SOURCE.\n";
        echo "   → INSERT IGNORE dengan ID original AMAN karena:\n";
        echo "      - id 8595-8660 tidak ada di TARGET (TARGET AUTO_INCREMENT masih 8595)\n";
        echo "      - Tidak ada child records yang butuh FK remap\n";
    }
}
