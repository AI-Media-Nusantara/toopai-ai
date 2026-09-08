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

// ── 1. SHOW CREATE TABLE brands kedua sisi ──────────────────────────
echo "=== SHOW CREATE TABLE brands (SOURCE) ===\n";
$r = $src->query("SHOW CREATE TABLE `brands`");
$row = $r->fetch_assoc();
echo $row['Create Table'] . "\n\n";

echo "=== SHOW CREATE TABLE brands (TARGET) ===\n";
$r = $tgt->query("SHOW CREATE TABLE `brands`");
$row = $r->fetch_assoc();
echo $row['Create Table'] . "\n\n";

// ── 2. Cek apakah kolom slug ada di kedua sisi ─────────────────────
echo "=== Kolom brands (SOURCE) ===\n";
$r = $src->query("SHOW COLUMNS FROM brands");
while ($row = $r->fetch_assoc()) {
    echo "  {$row['Field']} | {$row['Type']} | Null:{$row['Null']} | Default:{$row['Default']} | Extra:{$row['Extra']}\n";
}

echo "\n=== Kolom brands (TARGET) ===\n";
$r = $tgt->query("SHOW COLUMNS FROM brands");
while ($row = $r->fetch_assoc()) {
    echo "  {$row['Field']} | {$row['Type']} | Null:{$row['Null']} | Default:{$row['Default']} | Extra:{$row['Extra']}\n";
}

// ── 3. Cek kolom unik yang bisa jadi business key ─────────────────
echo "\n=== UNIQUE indexes brands (SOURCE) ===\n";
$r = $src->query("SHOW INDEX FROM brands WHERE Non_unique = 0");
while ($row = $r->fetch_assoc()) {
    echo "  {$row['Key_name']} | col:{$row['Column_name']} | seq:{$row['Seq_in_index']}\n";
}

echo "\n=== UNIQUE indexes brands (TARGET) ===\n";
$r = $tgt->query("SHOW INDEX FROM brands WHERE Non_unique = 0");
while ($row = $r->fetch_assoc()) {
    echo "  {$row['Key_name']} | col:{$row['Column_name']} | seq:{$row['Seq_in_index']}\n";
}

// ── 4. Range ID di masing-masing ────────────────────────────────────
echo "\n=== ID range brands ===\n";
$r = $src->query("SELECT MIN(id), MAX(id), COUNT(*) FROM brands");
$row = $r->fetch_row();
echo "SOURCE: min={$row[0]}, max={$row[1]}, count={$row[2]}\n";

$r = $tgt->query("SELECT MIN(id), MAX(id), COUNT(*) FROM brands");
$row = $r->fetch_row();
echo "TARGET: min={$row[0]}, max={$row[1]}, count={$row[2]}\n";

// ── 5. ID di SOURCE yang TIDAK ada di TARGET ──────────────────────
echo "\n=== Brands ID di SOURCE tapi TIDAK di TARGET (semua) ===\n";
$r = $src->query("
    SELECT s.id, s.name, s.email, s.status
    FROM brands s
    LEFT JOIN {$tgt_db}.brands t ON s.id = t.id
    WHERE t.id IS NULL
    ORDER BY s.id
    LIMIT 100
");
$missing_brands = [];
while ($row = $r->fetch_assoc()) {
    $missing_brands[] = $row;
    echo "  id={$row['id']} | name={$row['name']} | email={$row['email']} | status={$row['status']}\n";
}
echo "Total ditampilkan: " . count($missing_brands) . "\n";

// ── 6. Cek apakah ada ID collision (ID ada di keduanya tapi data beda) ─
echo "\n=== Cek ID collision di brands (id sama, name beda) ===\n";
$r = $src->query("
    SELECT s.id, s.name as src_name, t.name as tgt_name
    FROM brands s
    JOIN {$tgt_db}.brands t ON s.id = t.id
    WHERE s.name != t.name
    LIMIT 20
");
$count = 0;
while ($row = $r->fetch_assoc()) {
    echo "  id={$row['id']} | src_name={$row['src_name']} | tgt_name={$row['tgt_name']}\n";
    $count++;
}
if ($count === 0) echo "  (tidak ada — ID sama, name sama — aman)\n";

// ── 7. Cek FK: tabel apa saja yang referensikan brands.id ───────────
echo "\n=== FK yang merujuk ke brands.id (information_schema) ===\n";
$r = $src->query("
    SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = '{$src_db}'
    AND REFERENCED_TABLE_NAME = 'brands'
    AND REFERENCED_COLUMN_NAME = 'id'
");
$fk_tables = [];
while ($row = $r->fetch_assoc()) {
    $fk_tables[] = $row;
    echo "  {$row['TABLE_NAME']}.{$row['COLUMN_NAME']} (constraint: {$row['CONSTRAINT_NAME']})\n";
}
if (empty($fk_tables)) echo "  (tidak ada FK formal — cek manual brand_id)\n";

// ── 8. Cek tabel yang punya kolom brand_id (tanpa FK formal) ─────────
echo "\n=== Tabel dengan kolom brand_id (source) ===\n";
$r = $src->query("
    SELECT TABLE_NAME, COLUMN_NAME
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = '{$src_db}'
    AND COLUMN_NAME = 'brand_id'
");
while ($row = $r->fetch_assoc()) {
    echo "  {$row['TABLE_NAME']}.{$row['COLUMN_NAME']}\n";
}

// ── 9. Cek brands yang akan diinsert punya brand_id di child tables ─
if (!empty($missing_brands)) {
    $missing_ids = array_column($missing_brands, 'id');
    $id_list = implode(',', $missing_ids);

    echo "\n=== Child records untuk brands baru (id: $id_list) ===\n";
    $child_tables = ['brand_contacts', 'brand_creators', 'brand_products',
                     'brand_contact_queue', 'brand_search_queue',
                     'campaign_creator_performance', 'bd_affiliate_links'];
    foreach ($child_tables as $ct) {
        $r2 = $src->query("SELECT COUNT(*) as c FROM `$ct` WHERE brand_id IN ($id_list)");
        if ($r2) {
            $c = $r2->fetch_assoc()['c'];
            if ($c > 0) echo "  $ct: $c records terkait\n";
        }
    }
}

// ── 10. Full column diff brands SOURCE vs TARGET ────────────────────
echo "\n=== Kolom di SOURCE brands tapi TIDAK di TARGET ===\n";
$src_cols_r = $src->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$src_db' AND TABLE_NAME='brands'");
$src_cols = [];
while ($row = $src_cols_r->fetch_assoc()) $src_cols[] = $row['COLUMN_NAME'];

$tgt_cols_r = $tgt->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$tgt_db' AND TABLE_NAME='brands'");
$tgt_cols = [];
while ($row = $tgt_cols_r->fetch_assoc()) $tgt_cols[] = $row['COLUMN_NAME'];

$new_in_src = array_diff($src_cols, $tgt_cols);
$new_in_tgt = array_diff($tgt_cols, $src_cols);

if (empty($new_in_src)) echo "  (tidak ada kolom baru di source)\n";
else foreach ($new_in_src as $c) echo "  +SOURCE: $c\n";

echo "\n=== Kolom di TARGET brands tapi TIDAK di SOURCE ===\n";
if (empty($new_in_tgt)) echo "  (tidak ada kolom ekstra di target)\n";
else foreach ($new_in_tgt as $c) echo "  +TARGET: $c\n";

// ── 11. Cek status ENUM value brands yang akan diinsert ─────────────
echo "\n=== Status values dari brands baru (id yang akan diinsert) ===\n";
if (!empty($missing_brands)) {
    $id_list = implode(',', array_column($missing_brands, 'id'));
    $r = $src->query("SELECT status, COUNT(*) as c FROM brands WHERE id IN ($id_list) GROUP BY status");
    while ($row = $r->fetch_assoc()) {
        echo "  status='{$row['status']}': {$row['c']} brands\n";
    }
    // Check which of these status values exist in TARGET ENUM
    $r2 = $tgt->query("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$tgt_db' AND TABLE_NAME='brands' AND COLUMN_NAME='status'");
    $enum_def = $r2->fetch_assoc()['COLUMN_TYPE'];
    echo "  TARGET ENUM def: $enum_def\n";
}
