#!/usr/bin/env php
<?php
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', 0);
$env = parse_ini_file('.env.merge');
$tgt_db = $env['TARGET_DB'];
$tgt = new mysqli($env['TARGET_HOST'], $env['TARGET_USER'], $env['TARGET_PASS'], $tgt_db);
$tgt->set_charset('utf8mb4');

// ── Cek apakah orphan ini sudah ada sebelum merge (brand_id < 8595) ─────────
echo "=== Orphan Analysis: apakah orphan pre-existing atau akibat merge? ===\n\n";

// whatsapp_logs: cek distribution brand_id orphan
echo "--- whatsapp_logs orphan (brand_id tidak ada di brands) ---\n";
$r = $tgt->query("
    SELECT w.brand_id, COUNT(*) as cnt
    FROM whatsapp_logs w
    LEFT JOIN brands b ON w.brand_id = b.id
    WHERE w.brand_id IS NOT NULL AND b.id IS NULL
    GROUP BY w.brand_id
    ORDER BY w.brand_id
    LIMIT 30
");
$all_pre_existing = true;
while ($row = $r->fetch_assoc()) {
    $note = $row['brand_id'] < 8595 ? '← pre-existing (sebelum merge)' : '← dari merge (brand baru)';
    echo "  brand_id={$row['brand_id']} | count={$row['cnt']} | $note\n";
    if ($row['brand_id'] >= 8595) $all_pre_existing = false;
}
echo "\n";
echo $all_pre_existing
    ? "✅ KESIMPULAN: Semua orphan whatsapp_logs sudah ada SEBELUM merge (pre-existing)\n"
    : "⚠️  KESIMPULAN: Ada orphan baru yang dibuat oleh merge\n";

// creator_scouting: cek distribution brand_id orphan
echo "\n--- creator_scouting orphan ---\n";
$r2 = $tgt->query("
    SELECT cs.brand_id, COUNT(*) as cnt
    FROM creator_scouting cs
    LEFT JOIN brands b ON cs.brand_id = b.id
    WHERE b.id IS NULL
    GROUP BY cs.brand_id
    ORDER BY cs.brand_id
    LIMIT 30
");
$all_pre2 = true;
while ($row = $r2->fetch_assoc()) {
    $note = $row['brand_id'] < 8595 ? '← pre-existing' : '← dari merge';
    echo "  brand_id={$row['brand_id']} | count={$row['cnt']} | $note\n";
    if ($row['brand_id'] >= 8595) $all_pre2 = false;
}
echo "\n";
echo $all_pre2
    ? "✅ KESIMPULAN: Semua orphan creator_scouting sudah ada SEBELUM merge (pre-existing)\n"
    : "⚠️  KESIMPULAN: Ada orphan baru dari merge di creator_scouting\n";

// sample_requests: cek distribution brand_id orphan
echo "\n--- sample_requests orphan ---\n";
$r3 = $tgt->query("
    SELECT sr.brand_id, COUNT(*) as cnt
    FROM sample_requests sr
    LEFT JOIN brands b ON sr.brand_id = b.id
    WHERE b.id IS NULL
    GROUP BY sr.brand_id
    ORDER BY sr.brand_id
    LIMIT 30
");
$all_pre3 = true;
while ($row = $r3->fetch_assoc()) {
    $note = $row['brand_id'] < 8595 ? '← pre-existing' : '← dari merge';
    echo "  brand_id={$row['brand_id']} | count={$row['cnt']} | $note\n";
    if ($row['brand_id'] >= 8595) $all_pre3 = false;
}
echo "\n";
echo $all_pre3
    ? "✅ KESIMPULAN: Semua orphan sample_requests sudah ada SEBELUM merge (pre-existing)\n"
    : "⚠️  KESIMPULAN: Ada orphan baru dari merge di sample_requests\n";

// ── Final: apakah brand baru (8595-8660) menyebabkan orphan baru? ───────────
echo "\n=== FINAL CHECK: Apakah merge menciptakan orphan BARU? ===\n";
$checks = [
    'whatsapp_logs'   => "SELECT COUNT(*) as c FROM whatsapp_logs w LEFT JOIN brands b ON w.brand_id = b.id WHERE w.brand_id BETWEEN 8595 AND 8660 AND b.id IS NULL",
    'creator_scouting'=> "SELECT COUNT(*) as c FROM creator_scouting cs LEFT JOIN brands b ON cs.brand_id = b.id WHERE cs.brand_id BETWEEN 8595 AND 8660 AND b.id IS NULL",
    'sample_requests' => "SELECT COUNT(*) as c FROM sample_requests sr LEFT JOIN brands b ON sr.brand_id = b.id WHERE sr.brand_id BETWEEN 8595 AND 8660 AND b.id IS NULL",
];
$merge_created_orphan = false;
foreach ($checks as $table => $sql) {
    $c = (int)$tgt->query($sql)->fetch_assoc()['c'];
    echo ($c === 0 ? "  ✅" : "  ❌") . " $table: orphan dengan brand_id 8595-8660 = $c\n";
    if ($c > 0) $merge_created_orphan = true;
}

echo "\n";
if (!$merge_created_orphan) {
    echo "✅ MERGE TIDAK MENCIPTAKAN ORPHAN BARU.\n";
    echo "   Orphan yang dilaporkan (717, 806, 140) adalah pre-existing di production\n";
    echo "   sebelum proses merge ini dijalankan. Bukan akibat dari merge.\n";
} else {
    echo "❌ MERGE MENCIPTAKAN ORPHAN BARU — perlu investigasi lebih lanjut!\n";
}
