<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller: Migrasi_brands
 * 
 * Digunakan untuk insert data brand baru dari rekap tim BA ke database.
 * Dapat dijalankan melalui URL dengan token keamanan.
 * 
 * URL: /migrasi_brands/run?token=TOOPAI_MIGRASI_2026
 * 
 * PENTING: Hapus atau nonaktifkan controller ini setelah digunakan!
 */
class Migrasi_brands extends CI_Controller
{
    // Token keamanan — ganti jika perlu
    private $secret_token = 'TOOPAI_MIGRASI_2026';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Endpoint utama: jalankan proses insert brand
     * URL: /migrasi_brands/run?token=TOOPAI_MIGRASI_2026
     * Tambahkan &dry_run=1 untuk simulasi tanpa insert ke DB
     */
    public function run()
    {
        // ===== VALIDASI TOKEN =====
        $token    = $this->input->get('token');
        $dry_run  = $this->input->get('dry_run') == '1';

        if ($token !== $this->secret_token) {
            http_response_code(403);
            echo $this->_html_response('❌ Akses Ditolak', '<p style="color:red">Token tidak valid.</p>', 'error');
            return;
        }

        // ===== DATA BRAND DARI TIM BA =====
        $json_brands = $this->_get_brand_data();

        // ===== AMBIL SEMUA BRAND ACTIVE DARI DB =====
        $query = $this->db->select('id, name')
                          ->where('status', 'ACTIVE')
                          ->get('brands');

        $active_brands_db = [];
        foreach ($query->result() as $row) {
            $active_brands_db[mb_strtolower(trim($row->name))] = $row;
        }

        // ===== BANDINGKAN =====
        $to_insert      = [];
        $already_exists = [];

        foreach ($json_brands as $brand) {
            $name_lower = mb_strtolower(trim($brand['nama_brand']));
            if (isset($active_brands_db[$name_lower])) {
                $already_exists[] = [
                    'name'  => $brand['nama_brand'],
                    'db_id' => $active_brands_db[$name_lower]->id,
                ];
            } else {
                $to_insert[] = $brand;
            }
        }

        // ===== INSERT BRAND BARU =====
        $inserted = [];
        $failed   = [];
        $now      = date('Y-m-d H:i:s');

        if (!$dry_run) {
            foreach ($to_insert as $brand) {
                // proposed_commission di DB disimpan dalam PERSEN (9.00 = 9%)
                // Data JSON: 0.09 → ×100 → 9.00
                $commission = $brand['presentase_komisi_kreator'] !== null
                    ? round(floatval($brand['presentase_komisi_kreator']) * 100, 2)
                    : null;

                $data = [
                    'name'                => trim($brand['nama_brand']),
                    'proposed_commission' => $commission,
                    'status'              => 'ACTIVE',
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ];

                if ($this->db->insert('brands', $data)) {
                    $inserted[] = [
                        'id'         => $this->db->insert_id(),
                        'nama_brand' => $brand['nama_brand'],
                        'komisi'     => $commission !== null ? $commission . '%' : 'NULL',
                    ];
                } else {
                    $failed[] = [
                        'nama_brand' => $brand['nama_brand'],
                        'error'      => $this->db->error()['message'],
                    ];
                }
            }
        }

        // ===== RENDER HASIL =====
        $env   = ENVIRONMENT;
        $db    = $this->db->database;
        $title = $dry_run ? '🔍 DRY RUN — Simulasi Insert Brand' : '✅ Migrasi Brand Selesai';

        $content  = "<p><strong>Environment:</strong> <code>{$env}</code> &nbsp;|&nbsp; <strong>Database:</strong> <code>{$db}</code></p>";
        $content .= $dry_run
            ? '<p style="background:#fff3cd;padding:10px;border-radius:6px;"><strong>⚠ DRY RUN aktif</strong> — tidak ada data yang diinsert. Hapus <code>&dry_run=1</code> untuk insert sungguhan.</p>'
            : '';

        // Summary box
        $content .= '<table border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse;margin:12px 0;width:100%;">';
        $content .= '<tr style="background:#1e293b;color:white"><th colspan="2">SUMMARY</th></tr>';
        $content .= '<tr><td>Total dari JSON BA</td><td><strong>' . count($json_brands) . '</strong></td></tr>';
        $content .= '<tr><td>Sudah ada di DB (skip)</td><td><strong>' . count($already_exists) . '</strong></td></tr>';
        $content .= '<tr style="background:#d1fae5"><td>Berhasil diinsert</td><td><strong>' . count($inserted) . '</strong></td></tr>';
        $content .= '<tr><td>Akan diinsert (dry run)</td><td><strong>' . ($dry_run ? count($to_insert) : '-') . '</strong></td></tr>';
        $content .= '<tr style="background:#fee2e2"><td>Gagal</td><td><strong>' . count($failed) . '</strong></td></tr>';
        $content .= '</table>';

        // Tabel brand baru
        if (!$dry_run && !empty($inserted)) {
            $content .= '<h3 style="color:#10b981">✅ Brand Baru yang Berhasil Diinsert (' . count($inserted) . ')</h3>';
            $content .= '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%;font-size:13px;">';
            $content .= '<tr style="background:#1e293b;color:white"><th>#</th><th>ID</th><th>Nama Brand</th><th>Komisi</th></tr>';
            foreach ($inserted as $i => $b) {
                $content .= "<tr><td>" . ($i + 1) . "</td><td>{$b['id']}</td><td>{$b['nama_brand']}</td><td>{$b['komisi']}</td></tr>";
            }
            $content .= '</table>';
        }

        // Tabel dry run — yang akan diinsert
        if ($dry_run && !empty($to_insert)) {
            $content .= '<h3 style="color:#f59e0b">🔍 Brand yang AKAN Diinsert jika Dry Run dimatikan (' . count($to_insert) . ')</h3>';
            $content .= '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%;font-size:13px;">';
            $content .= '<tr style="background:#1e293b;color:white"><th>#</th><th>Nama Brand</th><th>Komisi</th></tr>';
            foreach ($to_insert as $i => $b) {
                $komisi = $b['presentase_komisi_kreator'] !== null ? (round($b['presentase_komisi_kreator'] * 100, 2)) . '%' : 'NULL';
                $content .= "<tr><td>" . ($i + 1) . "</td><td>{$b['nama_brand']}</td><td>{$komisi}</td></tr>";
            }
            $content .= '</table>';
        }

        // Tabel yang sudah ada (skip)
        if (!empty($already_exists)) {
            $content .= '<h3 style="color:#6b7280">⏭ Brand Sudah Ada di DB (Skip) (' . count($already_exists) . ')</h3>';
            $content .= '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%;font-size:13px;">';
            $content .= '<tr style="background:#374151;color:white"><th>#</th><th>ID</th><th>Nama Brand</th></tr>';
            foreach ($already_exists as $i => $b) {
                $content .= "<tr><td>" . ($i + 1) . "</td><td>{$b['db_id']}</td><td>{$b['name']}</td></tr>";
            }
            $content .= '</table>';
        }

        // Tabel gagal
        if (!empty($failed)) {
            $content .= '<h3 style="color:#ef4444">❌ Brand Gagal Diinsert (' . count($failed) . ')</h3>';
            $content .= '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%;font-size:13px;">';
            $content .= '<tr style="background:#7f1d1d;color:white"><th>#</th><th>Nama Brand</th><th>Error</th></tr>';
            foreach ($failed as $i => $b) {
                $content .= "<tr><td>" . ($i + 1) . "</td><td>{$b['nama_brand']}</td><td>{$b['error']}</td></tr>";
            }
            $content .= '</table>';
        }

        echo $this->_html_response($title, $content, $dry_run ? 'warning' : 'success');
    }

    // ===== HELPER: render HTML =====
    private function _html_response($title, $content, $type = 'success')
    {
        $colors = [
            'success' => '#10b981',
            'warning' => '#f59e0b',
            'error'   => '#ef4444',
        ];
        $color = $colors[$type] ?? '#10b981';

        return "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <title>{$title}</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #0f172a; color: #e2e8f0; margin: 0; padding: 24px; }
        h1 { color: {$color}; border-bottom: 2px solid {$color}; padding-bottom: 10px; }
        h3 { margin-top: 24px; }
        table { margin: 12px 0; }
        td, th { border-color: #334155; }
        tr:nth-child(even) { background: #1e293b; }
        code { background: #1e293b; padding: 2px 6px; border-radius: 4px; }
        .warning-box { background: #78350f; border: 1px solid #f59e0b; padding: 12px 16px; border-radius: 8px; margin: 16px 0; }
    </style>
</head>
<body>
    <h1>{$title}</h1>
    <div class='warning-box'>
        ⚠️ <strong>KEAMANAN:</strong> Hapus atau nonaktifkan controller <code>Migrasi_brands.php</code> setelah proses selesai!
    </div>
    {$content}
</body>
</html>";
    }

    // ===== DATA BRAND DARI TIM BA =====
    private function _get_brand_data()
    {
        return [
            ["nama_brand" => "Onlife Official",              "presentase_komisi_kreator" => 0.01],
            ["nama_brand" => "Bravhom Personal Care",         "presentase_komisi_kreator" => 0.01],
            ["nama_brand" => "Bravhom Official Indonesia",    "presentase_komisi_kreator" => 0.01],
            ["nama_brand" => "LeDingDing",                    "presentase_komisi_kreator" => null],
            ["nama_brand" => "Cuculemon Official Store",       "presentase_komisi_kreator" => null],
            ["nama_brand" => "POLKI INDONESIA",               "presentase_komisi_kreator" => null],
            ["nama_brand" => "Tataruma",                      "presentase_komisi_kreator" => null],
            ["nama_brand" => "Le Ding Ding LifeShop",         "presentase_komisi_kreator" => null],
            ["nama_brand" => "Le Ding Ding",                  "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "unikleen.id",                   "presentase_komisi_kreator" => null],
            ["nama_brand" => "JIB Indonesia",                 "presentase_komisi_kreator" => null],
            ["nama_brand" => "VIRALPRODUCK",                  "presentase_komisi_kreator" => 0.07],
            ["nama_brand" => "BAGSMART Indonesia",             "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "YPD.ID",                        "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "DasterBeauty",                  "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "Baellerry ID",                  "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "Carvil Shop",                   "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "Camile.id",                     "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "Hijabmoodbeauty",               "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "Cantiqu.id",                    "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "LuxusCollection",               "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "Polini",                        "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "By zamzam",                     "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "Sepatu SHOEAI",                 "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "Levieree Shop",                 "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "MOSSDOOM",                      "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "OPIOBAGS",                      "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "Asoka Fashion",                 "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "YouHave Store",                 "presentase_komisi_kreator" => 0.07],
            ["nama_brand" => "Sevine",                        "presentase_komisi_kreator" => 0.10],
            ["nama_brand" => "tweelyforbag",                  "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "PLCLXVII STORE",                "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "BRUNBRUN Watch",                "presentase_komisi_kreator" => null],
            ["nama_brand" => "Hefand",                        "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "KYAZA",                         "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "sophiemartinparis",             "presentase_komisi_kreator" => 0.08],
            ["nama_brand" => "EFHARIS",                       "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "BHI MALL",                     "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "BRUNBRUN Paris",                "presentase_komisi_kreator" => 0.08],
            ["nama_brand" => "Sweet Sally",                   "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "COROLLA Shoes Shop",            "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "GRENEY.Underwear.id",           "presentase_komisi_kreator" => 0.04],
            ["nama_brand" => "Lozy Hijab",                    "presentase_komisi_kreator" => null],
            ["nama_brand" => "Cerita Sipetek",                "presentase_komisi_kreator" => 0.08],
            ["nama_brand" => "Go-day Official",               "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "Yayang Dessert",                "presentase_komisi_kreator" => 0.07],
            ["nama_brand" => "Hasil Bumi12",                  "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "Captain Cemilan",               "presentase_komisi_kreator" => 0.08],
            ["nama_brand" => "Vitmaker.id",                   "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "PopENjoy",                      "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "Coolvita Indonesia",            "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "Drve store 1",                  "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "Dapoer Kuno",                   "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "Mercon Merah Putih",            "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "Bumboo",                        "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "Gehel Snack",                   "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "Swisse - Indonesia",            "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "Diamond choco",                 "presentase_komisi_kreator" => 0.13],
            ["nama_brand" => "Yummys Mom N Babe",             "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "SamBhojo Sambal Viral",         "presentase_komisi_kreator" => 0.07],
            ["nama_brand" => "KENAL Indonesian Roastery",     "presentase_komisi_kreator" => 0.08],
            ["nama_brand" => "Sajodo Snack & Food",           "presentase_komisi_kreator" => 0.08],
            ["nama_brand" => "DapurSR",                       "presentase_komisi_kreator" => 0.08],
            ["nama_brand" => "cimolbojot.aa",                 "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "Yili Pbrik Makanan",            "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "Magister Softlens",             "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "Hanasui",                       "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "MISTINE.ID",                    "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "raeccaid",                      "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "Reveline",                      "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "MeiJing Nails",                 "presentase_komisi_kreator" => 0.16],
            ["nama_brand" => "Pink Rabbit Lens",              "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "Braven Perfume",                "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "MlenDiaryID",                   "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "SikiCoolw IDN",                 "presentase_komisi_kreator" => 0.14],
            ["nama_brand" => "Evangeline",                    "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "PIPIWAYA",                      "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "Jacquelle_ID",                  "presentase_komisi_kreator" => 0.08],
            ["nama_brand" => "Alt Perfumery",                 "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "MYS Exquisite Woman",           "presentase_komisi_kreator" => 0.10],
            ["nama_brand" => "OILYOUNG ID",                   "presentase_komisi_kreator" => 0.08],
            ["nama_brand" => "MARIESKINLIAN Pusat",           "presentase_komisi_kreator" => 0.07],
            ["nama_brand" => "Purbasari Indonesia",           "presentase_komisi_kreator" => 0.08],
            ["nama_brand" => "LashBoss",                      "presentase_komisi_kreator" => 0.08],
            ["nama_brand" => "PINKFLASH STORE",               "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "KOJIS Bodycare",                "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "URBANX ID",                     "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "GLUGHAVA STORE",                "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "Pratista",                      "presentase_komisi_kreator" => 0.10],
            ["nama_brand" => "this is your",                  "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "True to Skin",                  "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "Ohmyskinid",                    "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "beauty.dovina",                 "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "KymmSkin",                      "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "Dettol Indonesia Store",        "presentase_komisi_kreator" => 0.07],
            ["nama_brand" => "Buds Organics Indonesia",       "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "Veet Indonesia Store",          "presentase_komisi_kreator" => 0.05],
            ["nama_brand" => "SYB",                           "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "usmile Indonesia Official Shop","presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "Pyary Store",                   "presentase_komisi_kreator" => 0.07],
            ["nama_brand" => "Syahila care officiall",        "presentase_komisi_kreator" => 0.07],
            ["nama_brand" => "Sohaki",                        "presentase_komisi_kreator" => 0.05],
            ["nama_brand" => "Newlab",                        "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "Shop name",                     "presentase_komisi_kreator" => null],
            ["nama_brand" => "Honnete Skin",                  "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "Dr Ekle's Skincare",            "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "Amaterasun",                    "presentase_komisi_kreator" => 0.07],
            ["nama_brand" => "Beeme Indonesia",               "presentase_komisi_kreator" => 0.08],
            ["nama_brand" => "Skin Game",                     "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "bonavie",                       "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "Everwhite.id",                  "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "barsten.id",                    "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "Everpure",                      "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "Whitelab_id",                   "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "Goute.id",                      "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "drbeemed.id",                   "presentase_komisi_kreator" => 0.16],
            ["nama_brand" => "Hiqween",                       "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "ERHASTORE OFFICIAL",            "presentase_komisi_kreator" => 0.08],
            ["nama_brand" => "GLOW FX",                       "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "Buttered",                      "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "FSMILE SHOP",                   "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "npureofficial",                 "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "MOELL OFFICIAL",                "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "Derma Express",                 "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "Louisse Choice Beauty",         "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "MR. epple",                     "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "LUMIWHITE",                     "presentase_komisi_kreator" => 0.08],
            ["nama_brand" => "Ultigar Health Store",          "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "Scarlett Whitening",            "presentase_komisi_kreator" => 0.05],
            ["nama_brand" => "LYDIMOON-IDN",                  "presentase_komisi_kreator" => 0.07],
            ["nama_brand" => "evermom.id",                    "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "White Story",                   "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "Sheeby Beauty",                 "presentase_komisi_kreator" => 0.10],
            ["nama_brand" => "JIERA",                         "presentase_komisi_kreator" => 0.21],
            ["nama_brand" => "Dorskin",                       "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "Elsheskin",                     "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "CANBE HANDSOME",                "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "Kime Skincare Shop",            "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "Aizen Dermalogy",               "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "feali.id",                      "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "Bhumi Official",                "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "MARLOVofficial",                "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "Eversense Perfumery",           "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "Ginza Beauty",                  "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "Morris Indonesia",              "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "sahabatmarina",                 "presentase_komisi_kreator" => 0.13],
            ["nama_brand" => "TONE",                          "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "MR SANYES",                     "presentase_komisi_kreator" => 0.23],
            ["nama_brand" => "Stacey Indonesia",              "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "Somethinc",                     "presentase_komisi_kreator" => 0.07],
            ["nama_brand" => "Pramy Indonesia",               "presentase_komisi_kreator" => 0.07],
            ["nama_brand" => "Bizarre Perfume",               "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "For Skin's Sake",               "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "Batik Handara",                 "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "Seagloca.id",                   "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "M231 Official",                 "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "namarithelabel.co",             "presentase_komisi_kreator" => 0.07],
            ["nama_brand" => "Aerostreet",                    "presentase_komisi_kreator" => 0.10],
            ["nama_brand" => "Mossèru",                       "presentase_komisi_kreator" => 0.02],
            ["nama_brand" => "Harletté",                      "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "ELVICTO",                       "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "Porto Footwear",                "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "Miniso Beauty ID",              "presentase_komisi_kreator" => 0.07],
            ["nama_brand" => "TOPTOY INDONESIA",              "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "Miniso Lifestyle ID",           "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "minisoshopid",                  "presentase_komisi_kreator" => 0.06],
            ["nama_brand" => "Zynexa Fiber ID",               "presentase_komisi_kreator" => 0.13],
            ["nama_brand" => "Adaya Store",                   "presentase_komisi_kreator" => 0.11],
            ["nama_brand" => "Herba.vita",                    "presentase_komisi_kreator" => 0.14],
            ["nama_brand" => "Implora Cosmetics",             "presentase_komisi_kreator" => 0.08],
            ["nama_brand" => "LUNAVEE INDONESIA",             "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "Vintage Story",                 "presentase_komisi_kreator" => 0.07],
            ["nama_brand" => "gaaborstore.id",                "presentase_komisi_kreator" => 0.05],
            ["nama_brand" => "YLwomen",                       "presentase_komisi_kreator" => 0.16],
            ["nama_brand" => "Bumbu Bunda Official Shop",     "presentase_komisi_kreator" => 0.16],
            ["nama_brand" => "See Light Store",               "presentase_komisi_kreator" => 0.09],
            ["nama_brand" => "TongRenTang ID T19",            "presentase_komisi_kreator" => 0.13],
            ["nama_brand" => "Wimiu",                         "presentase_komisi_kreator" => 0.07],
            ["nama_brand" => "CHICMORE Basic",                "presentase_komisi_kreator" => 0.05],
        ];
    }
}
