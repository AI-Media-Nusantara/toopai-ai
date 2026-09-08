#!/usr/bin/env php
<?php
/**
 * DB Analyze Script
 * Tahap 1 & 2: Analisis schema dan data antara SOURCE dan TARGET
 */
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', 0);
ini_set('memory_limit', '512M');
set_time_limit(0);

$env = parse_ini_file('.env.merge');

$src_db = $env['SOURCE_DB'];
$tgt_db = $env['TARGET_DB'];

$src = new mysqli($env['SOURCE_HOST'], $env['SOURCE_USER'], $env['SOURCE_PASS'], $src_db);
$tgt = new mysqli($env['TARGET_HOST'], $env['TARGET_USER'], $env['TARGET_PASS'], $tgt_db);

if ($src->connect_error) die("SOURCE connect failed: " . $src->connect_error . "\n");
if ($tgt->connect_error) die("TARGET connect failed: " . $tgt->connect_error . "\n");

$src->set_charset('utf8mb4');
$tgt->set_charset('utf8mb4');

$report = [];
$report[] = "# DB Merge Analysis Report";
$report[] = "Generated: " . date('Y-m-d H:i:s');
$report[] = "Source: $src_db";
$report[] = "Target: $tgt_db";
$report[] = "";

// ====================================================================
// TAHAP 1A — DAFTAR TABEL
// ====================================================================
$report[] = "---";
$report[] = "## Tahap 1A — Perbandingan Daftar Tabel";
$report[] = "";

function getTables($conn) {
    $r = $conn->query("SHOW TABLES");
    $t = [];
    while ($row = $r->fetch_array()) $t[] = $row[0];
    return $t;
}

$src_tables = getTables($src);
$tgt_tables = getTables($tgt);
sort($src_tables);
sort($tgt_tables);

$only_src  = array_diff($src_tables, $tgt_tables);
$only_tgt  = array_diff($tgt_tables, $src_tables);
$both      = array_intersect($src_tables, $tgt_tables);

$report[] = "### Tabel hanya di SOURCE (dev) — perlu CREATE di production";
$report[] = "```";
if (empty($only_src)) { $report[] = "(tidak ada)"; }
else { foreach ($only_src as $t) $report[] = "  + $t"; }
$report[] = "```";
$report[] = "";

$report[] = "### Tabel hanya di TARGET (prod) — TIDAK disentuh";
$report[] = "```";
if (empty($only_tgt)) { $report[] = "(tidak ada)"; }
else { foreach ($only_tgt as $t) $report[] = "  ! $t"; }
$report[] = "```";
$report[] = "";

$report[] = "### Tabel ada di keduanya: " . count($both) . " tabel";
$report[] = "";

// ====================================================================
// TAHAP 1B — PERBEDAAN KOLOM PER TABEL
// ====================================================================
$report[] = "---";
$report[] = "## Tahap 1B — Perbedaan Kolom per Tabel";
$report[] = "";

$schema_diff = []; // [table => [new_columns, modified_columns]]

function getColumns($conn, $db, $table) {
    $sql = "SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT,
                   EXTRA, COLUMN_KEY, COLLATION_NAME, ORDINAL_POSITION
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
            ORDER BY ORDINAL_POSITION";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $db, $table);
    $stmt->execute();
    $res = $stmt->get_result();
    $cols = [];
    while ($row = $res->fetch_assoc()) {
        $cols[$row['COLUMN_NAME']] = $row;
    }
    return $cols;
}

foreach ($both as $table) {
    $src_cols = getColumns($src, $src_db, $table);
    $tgt_cols = getColumns($tgt, $tgt_db, $table);

    $new_cols  = array_diff_key($src_cols, $tgt_cols);
    $drop_cols = array_diff_key($tgt_cols, $src_cols);
    $modified  = [];

    foreach ($src_cols as $col => $def) {
        if (!isset($tgt_cols[$col])) continue;
        $t = $tgt_cols[$col];
        if ($def['COLUMN_TYPE'] !== $t['COLUMN_TYPE'] ||
            $def['IS_NULLABLE'] !== $t['IS_NULLABLE'] ||
            $def['COLUMN_DEFAULT'] !== $t['COLUMN_DEFAULT']) {
            $modified[$col] = ['src' => $def, 'tgt' => $t];
        }
    }

    if (!empty($new_cols) || !empty($modified) || !empty($drop_cols)) {
        $schema_diff[$table] = [
            'new_cols'  => $new_cols,
            'modified'  => $modified,
            'drop_cols' => $drop_cols,
        ];

        $report[] = "### Tabel: `$table`";
        $report[] = "";

        if (!empty($new_cols)) {
            $report[] = "**Kolom BARU di SOURCE (belum ada di production):**";
            $report[] = "| Kolom | Tipe | Nullable | Default | Extra |";
            $report[] = "|-------|------|----------|---------|-------|";
            foreach ($new_cols as $col => $def) {
                $nullable = $def['IS_NULLABLE'] === 'YES' ? 'YES' : 'NO';
                $default  = $def['COLUMN_DEFAULT'] ?? 'NULL';
                $extra    = $def['EXTRA'];
                $report[] = "| `$col` | `{$def['COLUMN_TYPE']}` | $nullable | `$default` | $extra |";
            }
            $report[] = "";
        }

        if (!empty($modified)) {
            $report[] = "**Kolom BERBEDA definisi (source vs target) — hanya laporan, tidak auto-modify:**";
            $report[] = "| Kolom | Source Type | Target Type | Source Null | Target Null | Source Default | Target Default |";
            $report[] = "|-------|------------|------------|-------------|-------------|----------------|----------------|";
            foreach ($modified as $col => $diff) {
                $s = $diff['src'];
                $t = $diff['tgt'];
                $report[] = "| `$col` | `{$s['COLUMN_TYPE']}` | `{$t['COLUMN_TYPE']}` | {$s['IS_NULLABLE']} | {$t['IS_NULLABLE']} | `{$s['COLUMN_DEFAULT']}` | `{$t['COLUMN_DEFAULT']}` |";
            }
            $report[] = "";
        }

        if (!empty($drop_cols)) {
            $report[] = "**Kolom hanya di TARGET (tidak disentuh):**";
            $report[] = "```";
            foreach ($drop_cols as $col => $def) {
                $report[] = "  (safe) $col {$def['COLUMN_TYPE']}";
            }
            $report[] = "```";
            $report[] = "";
        }
    }
}

if (empty($schema_diff)) {
    $report[] = "(Tidak ada perbedaan kolom pada tabel yang ada di keduanya)";
    $report[] = "";
}

// ====================================================================
// TAHAP 1C — INDEX & UNIQUE KEY
// ====================================================================
$report[] = "---";
$report[] = "## Tahap 1C — Perbedaan Index";
$report[] = "";

$index_diff = [];

function getIndexes($conn, $db, $table) {
    $sql = "SELECT INDEX_NAME, NON_UNIQUE, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) as COLS
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
            GROUP BY INDEX_NAME, NON_UNIQUE";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $db, $table);
    $stmt->execute();
    $res = $stmt->get_result();
    $idx = [];
    while ($row = $res->fetch_assoc()) {
        $idx[$row['INDEX_NAME']] = $row;
    }
    return $idx;
}

$has_idx_diff = false;
foreach ($both as $table) {
    $src_idx = getIndexes($src, $src_db, $table);
    $tgt_idx = getIndexes($tgt, $tgt_db, $table);
    $new_idx = array_diff_key($src_idx, $tgt_idx);

    if (!empty($new_idx)) {
        $has_idx_diff = true;
        $index_diff[$table] = $new_idx;
        $report[] = "### `$table`";
        $report[] = "| Index Name | Unique | Columns |";
        $report[] = "|-----------|--------|---------|";
        foreach ($new_idx as $idx_name => $idx) {
            $unique = $idx['NON_UNIQUE'] == 0 ? 'YES' : 'NO';
            $report[] = "| `$idx_name` | $unique | `{$idx['COLS']}` |";
        }
        $report[] = "";
    }
}
if (!$has_idx_diff) {
    $report[] = "(Tidak ada perbedaan index)";
    $report[] = "";
}

// ====================================================================
// TAHAP 2A — COUNT PER TABEL
// ====================================================================
$report[] = "---";
$report[] = "## Tahap 2A — Perbandingan COUNT(*) per Tabel";
$report[] = "";
$report[] = "| Tabel | Dev (source) | Prod (target) | Selisih |";
$report[] = "|-------|-------------|---------------|---------|";

$count_diff = []; // tables with more rows in source
foreach ($both as $table) {
    $sc = $src->query("SELECT COUNT(*) as c FROM `$table`")->fetch_assoc()['c'];
    $tc = $tgt->query("SELECT COUNT(*) as c FROM `$table`")->fetch_assoc()['c'];
    $diff = $sc - $tc;
    $flag = $diff > 0 ? " ← **+$diff**" : ($diff < 0 ? " ← prod punya lebih" : "");
    $report[] = "| `$table` | $sc | $tc | $diff$flag |";
    if ($diff > 0) {
        $count_diff[$table] = ['src' => $sc, 'tgt' => $tc, 'diff' => $diff];
    }
}
$report[] = "";

// ====================================================================
// TAHAP 2B — KUNCI PEMBANDING & BARIS BEDA
// ====================================================================
$report[] = "---";
$report[] = "## Tahap 2B — Identifikasi Baris Beda (Source punya lebih)";
$report[] = "";

// Get primary key info
function getPrimaryKeys($conn, $db, $table) {
    $sql = "SELECT COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = 'PRIMARY'
            ORDER BY ORDINAL_POSITION";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $db, $table);
    $stmt->execute();
    $res = $stmt->get_result();
    $pks = [];
    while ($row = $res->fetch_assoc()) $pks[] = $row['COLUMN_NAME'];
    return $pks;
}

// Get unique keys info
function getUniqueKeys($conn, $db, $table) {
    $sql = "SELECT INDEX_NAME, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) as COLS
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
            AND NON_UNIQUE = 0 AND INDEX_NAME != 'PRIMARY'
            GROUP BY INDEX_NAME LIMIT 5";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $db, $table);
    $stmt->execute();
    $res = $stmt->get_result();
    $uk = [];
    while ($row = $res->fetch_assoc()) $uk[$row['INDEX_NAME']] = $row['COLS'];
    return $uk;
}

// Tables with count diff + tables only in source
$tables_to_check = array_keys($count_diff);
foreach ($only_src as $t) $tables_to_check[] = $t;

$migration_plan = []; // per table: rows to insert, key used

// Business-key override per table (when auto-increment PK is unreliable)
$business_keys = [
    'brands'               => ['slug'],
    'creators'             => ['username'],
    'users'                => ['email'],
    'categories'           => ['slug'],
    'affiliate_campaigns'  => ['slug'],
    'affiliate_products'   => ['affiliate_id'],
    'products'             => ['sku'],
    'app_config'           => ['config_key'],
    'roles'                => ['slug'],
];

foreach ($count_diff as $table => $counts) {
    $report[] = "### `$table` — source: {$counts['src']}, target: {$counts['tgt']}, selisih: +{$counts['diff']}";
    $report[] = "";

    $pks = getPrimaryKeys($src, $src_db, $table);
    $uks = getUniqueKeys($src, $src_db, $table);

    // Determine comparison key
    if (isset($business_keys[$table])) {
        $key_cols = $business_keys[$table];
        $key_reason = "Business key (override manual — PK auto-increment tidak konsisten antar DB)";
    } elseif (!empty($uks)) {
        $first_uk = reset($uks);
        $key_cols = explode(',', $first_uk);
        $key_reason = "Unique key dari index: " . key($uks);
    } elseif (!empty($pks)) {
        // Check if PK is auto_increment
        $pk = $pks[0];
        $col_info_r = $src->query("SELECT EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$src_db' AND TABLE_NAME='$table' AND COLUMN_NAME='$pk'");
        $col_info = $col_info_r->fetch_assoc();
        if (strpos($col_info['EXTRA'], 'auto_increment') !== false && count($pks) === 1) {
            $key_cols = $pks;
            $key_reason = "PK auto-increment — ⚠️ perlu mapping ID jika ada FK yang merujuk ke tabel ini";
        } else {
            $key_cols = $pks;
            $key_reason = "Primary Key (composite atau non-auto)";
        }
    } else {
        $key_cols = null;
        $key_reason = "⚠️ TIDAK ADA PK/UNIQUE — tidak bisa auto-deduplicate, perlu review manual";
    }

    $report[] = "**Kunci pembanding:** `" . implode(', ', $key_cols ?? ['?']) . "`";
    $report[] = "**Alasan:** $key_reason";
    $report[] = "";

    if ($key_cols) {
        // Verify key cols actually exist in TARGET too
        $tgt_cols_check = getColumns($tgt, $tgt_db, $table);
        $valid_keys = array_filter($key_cols, fn($c) => isset($tgt_cols_check[$c]));
        if (count($valid_keys) !== count($key_cols)) {
            // Fallback to PK
            $pks2 = getPrimaryKeys($tgt, $tgt_db, $table);
            $key_cols = !empty($pks2) ? $pks2 : null;
            if ($key_cols) {
                $key_reason .= " [FALLBACK ke PK karena business key tidak ada di target]";
                $report[] = "⚠️ Business key tidak ada di TARGET, fallback ke PK: `" . implode(', ', $key_cols) . "`";
                $report[] = "";
            }
        }
    }

    if ($key_cols) {
        // Count rows in src not in tgt
        $join_cond = implode(' AND ', array_map(function($c) use ($table) {
            return "s.`$c` = t.`$c`";
        }, $key_cols));

        $where_null = "t.`{$key_cols[0]}` IS NULL";

        $count_missing_r = $src->query("
            SELECT COUNT(*) as c FROM `$table` s
            LEFT JOIN `{$tgt_db}`.`$table` t ON $join_cond
            WHERE $where_null
        ");

        if ($count_missing_r) {
            $count_missing = $count_missing_r->fetch_assoc()['c'];
            $report[] = "**Baris di source yang BELUM ada di production:** $count_missing baris";

            // Sample first 10
            if ($count_missing > 0) {
                $sample_cols = implode(', ', array_map(fn($c) => "s.`$c`", $key_cols));
                $sample_r = $src->query("
                    SELECT $sample_cols FROM `$table` s
                    LEFT JOIN `{$tgt_db}`.`$table` t ON $join_cond
                    WHERE $where_null
                    LIMIT 10
                ");
                if ($sample_r && $sample_r->num_rows > 0) {
                    $report[] = "Contoh (max 10):";
                    $report[] = "```";
                    while ($row = $sample_r->fetch_assoc()) {
                        $report[] = "  " . json_encode($row, JSON_UNESCAPED_UNICODE);
                    }
                    $report[] = "```";
                }
            }

            $migration_plan[$table] = [
                'key_cols' => $key_cols,
                'key_reason' => $key_reason,
                'missing' => $count_missing,
                'src_count' => $counts['src'],
                'tgt_count' => $counts['tgt'],
            ];
        } else {
            $report[] = "⚠️ Query error: " . $src->error;
        }
    }
    $report[] = "";
}

// ====================================================================
// TAHAP 2C — RINGKASAN MIGRASI
// ====================================================================
$report[] = "---";
$report[] = "## Tahap 2C — Ringkasan Rencana Migrasi";
$report[] = "";
$report[] = "| Tabel | Baris baru dari dev | Baris production aman | Kunci |";
$report[] = "|-------|--------------------|-----------------------|-------|";
foreach ($migration_plan as $table => $plan) {
    $safe = $plan['tgt_count'];
    $key  = implode('+', $plan['key_cols']);
    $report[] = "| `$table` | **{$plan['missing']}** | $safe | `$key` |";
}
$report[] = "";
$report[] = "**Total baris akan diinsert:** " . array_sum(array_column($migration_plan, 'missing'));
$report[] = "";

// Tabel only in source
if (!empty($only_src)) {
    $report[] = "### Tabel baru (hanya di source) — akan di-CREATE + INSERT semua baris";
    $report[] = "| Tabel | Jumlah baris |";
    $report[] = "|-------|-------------|";
    foreach ($only_src as $t) {
        $c = $src->query("SELECT COUNT(*) as c FROM `$t`")->fetch_assoc()['c'];
        $report[] = "| `$t` | $c |";
    }
    $report[] = "";
}

// Save report
$output = implode("\n", $report);
file_put_contents('merge_analysis_report.md', $output);
echo $output;
echo "\n\n[Analysis saved to merge_analysis_report.md]\n";
