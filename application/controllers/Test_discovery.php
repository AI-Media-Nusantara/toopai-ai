<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Test Discovery Controller
 * Discovery creator dari FastMoss dan simpan ke database
 */
class Test_discovery extends CI_Controller {
    
    private $default_is_id = 1; // Toopai
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('Fastmoss_model');
        $this->load->model('BrandCreator_model');
        $this->load->model('User_log_model');
    }
    
    /**
     * Index - Form input brand
     */
    public function index() {
        // ... (sama seperti sebelumnya, tidak berubah)
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Test Discovery Creator - FastMoss</title>
            <style>
                body { font-family: Arial, sans-serif; background: #0f0f1a; color: #e2f0e8; padding: 20px; max-width: 1200px; margin: 0 auto; }
                .box { background: #1a1f2e; border-radius: 12px; padding: 20px; margin-bottom: 20px; border: 1px solid #2a3346; }
                .success { color: #4ade80; }
                .error { color: #ef4444; }
                .warning { color: #f59e0b; }
                .info { color: #8b5cf6; }
                input[type=text] { padding: 8px 12px; background: #0f1420; border: 1px solid #2a3346; border-radius: 8px; color: #e2f0e8; width: 300px; }
                button { background: #4ade80; color: #0a0e17; padding: 8px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
                button:hover { background: #22c55e; }
                table { width: 100%; border-collapse: collapse; font-size: 13px; }
                th, td { padding: 10px; text-align: left; border-bottom: 1px solid #2a3346; }
                th { color: #8b5cf6; }
                .badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 11px; }
                .badge-success { background: rgba(74,222,128,0.15); color: #4ade80; }
                .badge-warning { background: rgba(245,158,11,0.15); color: #f59e0b; }
                .badge-info { background: rgba(139,92,246,0.15); color: #8b5cf6; }
                .step { padding: 10px 14px; margin: 8px 0; border-radius: 8px; background: #0f1420; }
                .step-success { border-left: 4px solid #4ade80; }
                .step-error { border-left: 4px solid #ef4444; }
                .step-info { border-left: 4px solid #8b5cf6; }
                .back-btn { display: inline-block; padding: 8px 16px; background: #8b5cf6; color: white; text-decoration: none; border-radius: 8px; }
                .back-btn:hover { background: #7c3aed; }
                .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin: 12px 0; }
                .summary-item { background: #0f1420; padding: 12px; border-radius: 8px; text-align: center; }
                .summary-item .number { font-size: 24px; font-weight: 700; }
                .summary-item .label { font-size: 11px; color: #6b7280; margin-top: 4px; }
            </style>
        </head>
        <body>
            <h1>🧪 Test Discovery Creator - FastMoss</h1>
            <p class='info'>Discovery creator dari FastMoss, simpan ke brand_creators dan creators (TASK 1)</p>
            
            <div class='box'>
                <h3>🔍 Input Brand</h3>
                <form method='POST' action='" . base_url('test_discovery/run') . "'>
                    <div style='display: flex; gap: 12px; align-items: center; flex-wrap: wrap;'>
                        <input type='text' name='brand_name' placeholder='Contoh: HONNETE, Scarlett, Hanasui' value='HONNETE' required>
                        <button type='submit'>▶️ Discover Creators</button>
                    </div>
                </form>
            </div>
            
            <div class='box'>
                <h3>🔍 Input Product ID Direct</h3>
                <form method='POST' action='" . base_url('test_discovery/run_product_direct') . "'>
                    <div style='display: flex; gap: 12px; align-items: center; flex-wrap: wrap;'>
                        <input type='text' name='product_id' placeholder='Product ID dari affiliate_products' value='1735421655417849149'>
                        <button type='submit'>▶️ Get Creators</button>
                    </div>
                </form>
            </div>
            
            <div class='box'>
                <h3>📋 Brand dengan Produk di affiliate_products</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Brand Name</th>
                            <th>Shop Name</th>
                            <th>Products</th>
                            <th>Creators</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>";
        
        // Ambil brand yang punya produk di affiliate_products
        $brands = $this->db->select('b.id, b.name, b.shop_name, COUNT(ap.product_id) as product_count')
            ->from('brands b')
            ->join('affiliate_products ap', 'b.shop_name = ap.shop_name', 'left')
            ->where('b.shop_name IS NOT NULL')
            ->where('b.shop_name !=', '')
            ->group_by('b.id')
            ->having('product_count >', 0)
            ->order_by('b.id', 'DESC')
            ->limit(20)
            ->get()
            ->result();
        
        if (!empty($brands)) {
            foreach ($brands as $brand) {
                $creator_count = $this->db->where('brand_id', $brand->id)
                    ->count_all_results('brand_creators');
                
                $creators_in_task = $this->db->where('brand_id', $brand->id)
                    ->where('status', 'APPROVED')
                    ->count_all_results('brand_creators');
                
                echo "<tr>
                    <td>{$brand->id}</td>
                    <td><strong>" . htmlspecialchars($brand->name) . "</strong></td>
                    <td>" . htmlspecialchars($brand->shop_name) . "</td>
                    <td>{$brand->product_count}</td>
                    <td>
                        <span class='badge badge-info'>Total: {$creator_count}</span>
                        <span class='badge badge-success'>Approved: {$creators_in_task}</span>
                    </td>
                    <td>
                        <form method='POST' action='" . base_url('test_discovery/run') . "' style='display:inline;'>
                            <input type='hidden' name='brand_id' value='{$brand->id}'>
                            <button type='submit' style='background:#8b5cf6; font-size:11px; padding:4px 12px;'>Discover</button>
                        </form>
                        <a href='" . base_url('test_discovery/view_creators/' . $brand->id) . "' style='color:#4ade80; font-size:11px; margin-left:8px;'>View</a>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='6' style='text-align:center; color:#6b7280;'>Belum ada brand dengan produk di affiliate_products</td></tr>";
        }
        
        echo "</tbody></table></div>";
        echo "</body></html>";
    }
    
    /**
     * Run discovery untuk 1 brand
     */
    public function run() {
        // ... (sama seperti sebelumnya)
        $brand_name = $this->input->post('brand_name');
        $brand_id = $this->input->post('brand_id');
        
        if (!$brand_name && !$brand_id) {
            show_error('Brand name or Brand ID required');
        }
        
        // Ambil brand dari database
        if ($brand_id) {
            $brand = $this->db->select('id, name, shop_name')
                ->where('id', $brand_id)
                ->get('brands')
                ->row();
        } else {
            $brand = $this->db->select('id, name, shop_name')
                ->where('LOWER(name)', strtolower($brand_name))
                ->or_where('LOWER(shop_name)', strtolower($brand_name))
                ->get('brands')
                ->row();
        }
        
        if (!$brand) {
            show_error('Brand not found: ' . $brand_name);
        }
        
        // Mulai proses
        $this->_display_header($brand);
        
        // STEP 1: Ambil product_id dari affiliate_products
        $products = $this->db->select('product_id, product_name')
            ->from('affiliate_products')
            ->where('LOWER(shop_name)', strtolower($brand->shop_name))
            ->where('review_status', 'APPROVED')
            ->order_by('sales_count', 'DESC')
            ->limit(5)
            ->get()
            ->result();
        
        if (empty($products)) {
            echo "<div class='step step-error'>";
            echo "<strong>❌ Tidak ada produk ditemukan untuk brand ini!</strong><br>";
            echo "<span class='warning'>💡 Pastikan brand sudah di-sync campaign</span>";
            echo "</div>";
            $this->_display_footer();
            return;
        }
        
        echo "<div class='step step-success'>";
        echo "<strong>✅ Ditemukan " . count($products) . " produk</strong><br>";
        echo "<div style='font-size:11px; color:#6b7280; margin-top:4px;'>";
        foreach ($products as $p) {
            echo "• " . htmlspecialchars($p->product_name) . " (ID: {$p->product_id})<br>";
        }
        echo "</div>";
        echo "</div>";
        
        // STEP 2: Panggil FastMoss untuk setiap produk
        $all_creators = [];
        $total_creators = 0;
        
        foreach ($products as $product) {
            echo "<div class='step step-info'>";
            echo "<strong>📦 Produk: " . htmlspecialchars($product->product_name) . "</strong><br>";
            echo "<span class='info'>Product ID: <code>{$product->product_id}</code></span><br>";
            echo "<span class='warning'>⏳ Mengambil data dari FastMoss...</span><br>";
            
            $result = $this->BrandCreator_model->get_product_creators($product->product_id);
            
            if ($result['status'] && !empty($result['creators'])) {
                $creators = $result['creators'];
                echo "<span class='success'>✅ Ditemukan " . count($creators) . " creator</span><br>";
                
                foreach ($creators as &$creator) {
                    $creator['product_id'] = $product->product_id;
                    $creator['product_name'] = $product->product_name;
                }
                
                $all_creators = array_merge($all_creators, $creators);
                $total_creators += count($creators);
            } else {
                echo "<span class='warning'>⚠️ Tidak ada creator ditemukan</span><br>";
            }
            
            echo "</div>";
        }
        
        // STEP 3: Simpan ke database
        if (!empty($all_creators)) {
            echo "<div class='step step-info'>";
            echo "<strong>💾 Menyimpan creator ke database...</strong><br>";
            
            $save_result = $this->BrandCreator_model->save_creators_from_fastmoss(
                $brand->id,
                $all_creators,
                $this->default_is_id
            );
            
            echo "<div class='summary-grid'>";
            echo "<div class='summary-item'><div class='number success'>" . $save_result['saved'] . "</div><div class='label'>Disimpan ke brand_creators</div></div>";
            echo "<div class='summary-item'><div class='number success'>" . $save_result['added_to_creators'] . "</div><div class='label'>Ditambahkan ke creators (TASK 1)</div></div>";
            echo "<div class='summary-item'><div class='number warning'>" . $save_result['skipped'] . "</div><div class='label'>Sudah ada (skip)</div></div>";
            echo "</div>";
            echo "</div>";
            
            // Tampilkan list creator
            $this->_display_creators($all_creators);
        } else {
            echo "<div class='step step-warning'>";
            echo "<strong>⚠️ Tidak ada creator yang ditemukan untuk brand ini</strong>";
            echo "</div>";
        }
        
        $this->_display_footer();
    }
    
    /**
     * Run product direct - test 1 produk saja
     */
    public function run_product_direct() {
        $product_id = $this->input->post('product_id');
        
        if (!$product_id) {
            show_error('Product ID required');
        }
        
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Product Creators - " . htmlspecialchars($product_id) . "</title>
            <style>
                body { font-family: Arial, sans-serif; background: #0f0f1a; color: #e2f0e8; padding: 20px; max-width: 1200px; margin: 0 auto; }
                .box { background: #1a1f2e; border-radius: 12px; padding: 20px; margin-bottom: 20px; border: 1px solid #2a3346; }
                .success { color: #4ade80; }
                .error { color: #ef4444; }
                .info { color: #8b5cf6; }
                .warning { color: #f59e0b; }
                table { width: 100%; border-collapse: collapse; font-size: 13px; }
                th, td { padding: 10px; text-align: left; border-bottom: 1px solid #2a3346; }
                th { color: #8b5cf6; }
                .badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 11px; }
                .badge-success { background: rgba(74,222,128,0.15); color: #4ade80; }
                .badge-warning { background: rgba(245,158,11,0.15); color: #f59e0b; }
                .badge-info { background: rgba(139,92,246,0.15); color: #8b5cf6; }
                .back-btn { display: inline-block; padding: 8px 16px; background: #8b5cf6; color: white; text-decoration: none; border-radius: 8px; }
                .back-btn:hover { background: #7c3aed; }
                .summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin: 12px 0; }
                .summary-item { background: #0f1420; padding: 12px; border-radius: 8px; text-align: center; }
                .summary-item .number { font-size: 24px; font-weight: 700; }
                .summary-item .label { font-size: 11px; color: #6b7280; margin-top: 4px; }
            </style>
        </head>
        <body>
            <div style='display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;'>
                <h1>📦 Product Creators - Direct Test</h1>
                <a href='" . base_url('test_discovery') . "' class='back-btn'>← Back</a>
            </div>";
        
        $result = $this->BrandCreator_model->get_product_creators($product_id);
        
        if ($result['status']) {
            echo "<div class='box'>";
            echo "<h2>📊 Product Info</h2>";
            echo "<div style='display:grid; grid-template-columns: 1fr 1fr 1fr; gap:12px;'>";
            echo "<div><span class='info'>Product:</span> " . htmlspecialchars($result['product_name'] ?? '-') . "</div>";
            echo "<div><span class='info'>Region:</span> " . htmlspecialchars($result['product_region'] ?? '-') . "</div>";
            echo "<div><span class='info'>Total GMV:</span> <span class='success'>" . ($result['product_gmv_total_show'] ?? 'Rp 0') . "</span></div>";
            echo "<div><span class='info'>Total Sold:</span> " . number_format($result['product_sold_total'] ?? 0) . "</div>";
            echo "<div><span class='info'>Total Creators:</span> <strong>" . ($result['total_creators'] ?? 0) . "</strong></div>";
            echo "</div>";
            echo "</div>";
            
            $creators = $result['creators'] ?? [];
            
            if (!empty($creators)) {
                // SIMPAN KE DATABASE
                echo "<div class='box'>";
                echo "<h3>💾 Saving to Database...</h3>";
                
                $product_info = $this->db->select('shop_name')
                    ->where('product_id', $product_id)
                    ->get('affiliate_products')
                    ->row();
                
                $brand = null;
                if ($product_info && $product_info->shop_name) {
                    $brand = $this->db->select('id, name, shop_name')
                        ->where('LOWER(shop_name)', strtolower($product_info->shop_name))
                        ->get('brands')
                        ->row();
                }
                
                if ($brand) {
                    $save_result = $this->BrandCreator_model->save_creators_from_fastmoss(
                        $brand->id,
                        $creators,
                        $this->default_is_id
                    );
                    
                    echo "<div class='summary-grid'>";
                    echo "<div class='summary-item'><div class='number success'>" . $save_result['saved'] . "</div><div class='label'>Disimpan ke brand_creators</div></div>";
                    echo "<div class='summary-item'><div class='number success'>" . $save_result['added_to_creators'] . "</div><div class='label'>Ditambahkan ke creators</div></div>";
                    echo "<div class='summary-item'><div class='number warning'>" . $save_result['skipped'] . "</div><div class='label'>Sudah ada (skip)</div></div>";
                    echo "</div>";
                } else {
                    echo "<div class='warning'>⚠️ Brand tidak ditemukan, hanya ditampilkan tanpa disimpan</div>";
                }
                
                echo "</div>";
                
                // Tampilkan list creator
                echo "<div class='box'>";
                echo "<h3>📋 Daftar Creator (" . count($creators) . ")</h3>";
                echo "<table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Creator</th>
                            <th>Username</th>
                            <th>Followers</th>
                            <th>GMV (produk ini)</th>
                            <th>Sold</th>
                            <th>Start Promoting</th>
                        </tr>
                    </thead>
                    <tbody>";
                
                $rank = 1;
                foreach ($creators as $creator) {
                    $followers_show = $creator['followers_show'] ?? number_format($creator['followers'] ?? 0);
                    $gmv_show = $creator['gmv_from_this_product_show'] ?? 'Rp ' . number_format($creator['gmv_from_this_product'] ?? 0);
                    
                    echo "<tr>
                        <td>{$rank}</td>
                        <td><strong>" . htmlspecialchars($creator['creator_name'] ?? $creator['creator_username'] ?? '-') . "</strong></td>
                        <td>@" . htmlspecialchars($creator['creator_username'] ?? '-') . "</td>
                        <td>{$followers_show}</td>
                        <td style='color:#4ade80;'>{$gmv_show}</td>
                        <td>" . number_format($creator['sold_from_this_product'] ?? 0) . "</td>
                        <td>" . ($creator['start_promoting'] ? date('d/m/Y', strtotime($creator['start_promoting'])) : '-') . "</td>
                    </tr>";
                    $rank++;
                }
                
                echo "</tbody></table>";
                echo "</div>";
            }
        } else {
            echo "<div class='box'>";
            echo "<div class='error'>❌ Gagal mengambil data dari FastMoss</div>";
            echo "</div>";
        }
        
        echo "</body></html>";
    }
    
    /**
     * Test cron page - menampilkan status produk
     */
  public function test_cron() {
    // Pastikan kolom ada
    $this->load->model('BrandCreator_model');
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Test Cron - Process Approved Products</title>
        <style>
            body { font-family: Arial, sans-serif; background: #0f0f1a; color: #e2f0e8; padding: 20px; max-width: 1200px; margin: 0 auto; }
            .box { background: #1a1f2e; border-radius: 12px; padding: 20px; margin-bottom: 20px; border: 1px solid #2a3346; }
            .success { color: #4ade80; }
            .error { color: #ef4444; }
            .warning { color: #f59e0b; }
            .info { color: #8b5cf6; }
            table { width: 100%; border-collapse: collapse; font-size: 13px; }
            th, td { padding: 10px; text-align: left; border-bottom: 1px solid #2a3346; }
            th { color: #8b5cf6; }
            .badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 11px; }
            .badge-success { background: rgba(74,222,128,0.15); color: #4ade80; }
            .badge-warning { background: rgba(245,158,11,0.15); color: #f59e0b; }
            .badge-info { background: rgba(139,92,246,0.15); color: #8b5cf6; }
            .back-btn { display: inline-block; padding: 8px 16px; background: #8b5cf6; color: white; text-decoration: none; border-radius: 8px; }
            .back-btn:hover { background: #7c3aed; }
            .btn-process { background: #8b5cf6; color: white; border: none; padding: 4px 12px; border-radius: 6px; cursor: pointer; font-size: 11px; }
            .btn-process:hover { background: #7c3aed; }
            .btn-cron { background: #4ade80; color: #0a0e17; padding: 8px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
            .btn-cron:hover { background: #22c55e; }
        </style>
    </head>
    <body>
        <div style='display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;'>
            <h1>🧪 Test Cron - Process Approved Products</h1>
            <a href='" . base_url('test_discovery') . "' class='back-btn'>← Back</a>
        </div>";
    
    // Ambil produk yang pending approval
    $pending_products = $this->db->select('product_id, product_name, shop_name, review_status')
        ->from('affiliate_products')
        ->where('review_status', 'PENDING')
        ->where('shop_name IS NOT NULL')
        ->where('shop_name !=', '')
        ->order_by('created_at', 'DESC')
        ->limit(10)
        ->get()
        ->result();
    
    echo "<div class='box'>";
    echo "<h3>📋 Pending Products (Belum di-approve)</h3>";
    if (!empty($pending_products)) {
        echo "<table>
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Product Name</th>
                    <th>Shop Name</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>";
        foreach ($pending_products as $p) {
            echo "<tr>
                <td><code>{$p->product_id}</code></td>
                <td>" . htmlspecialchars($p->product_name) . "</td>
                <td>" . htmlspecialchars($p->shop_name) . "</td>
                <td><span class='badge badge-warning'>PENDING</span></td>
            </tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<p class='info'>Tidak ada produk dengan status PENDING</p>";
    }
    echo "</div>";
    
    // Ambil produk yang sudah APPROVED
    $approved_products = $this->db->select('product_id, product_name, shop_name, review_status')
        ->from('affiliate_products')
        ->where('review_status', 'APPROVED')
        ->where('shop_name IS NOT NULL')
        ->where('shop_name !=', '')
        ->order_by('updated_at', 'DESC')
        ->limit(10)
        ->get()
        ->result();
    
    echo "<div class='box'>";
    echo "<h3>📋 Approved Products (10 terakhir)</h3>";
    if (!empty($approved_products)) {
        echo "<table>
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Product Name</th>
                    <th>Shop Name</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>";
        foreach ($approved_products as $p) {
            // Cek apakah sudah diproses - GUNAKAN TRY CATCH
            $processed = false;
            try {
                $processed = $this->BrandCreator_model->is_product_processed($p->product_id);
            } catch (Exception $e) {
                // Jika error, anggap belum diproses
                $processed = false;
            }
            
            $status_label = $processed ? '<span class="badge badge-success">✅ Processed</span>' : '<span class="badge badge-info">⏳ Pending</span>';
            
            echo "<tr>
                <td><code>{$p->product_id}</code></td>
                <td>" . htmlspecialchars($p->product_name) . "</td>
                <td>" . htmlspecialchars($p->shop_name) . "</td>
                <td><span class='badge badge-success'>APPROVED</span></td>
                <td>
                    {$status_label}
                    " . (!$processed ? "
                    <form method='POST' action='" . base_url('test_discovery/process_single_product') . "' style='display:inline;'>
                        <input type='hidden' name='product_id' value='{$p->product_id}'>
                        <button type='submit' class='btn-process'>Process</button>
                    </form>" : "") . "
                </td>
            </tr>";
        }
        echo "</tbody></table>";
        
        echo "<div style='margin-top:12px;'>";
        echo "<form method='POST' action='" . base_url('test_discovery/run_cron_manual') . "'>";
        echo "<button type='submit' class='btn-cron'>▶️ Run Cron Job Manual</button>";
        echo "</form>";
        echo "</div>";
    } else {
        echo "<p class='info'>Belum ada produk dengan status APPROVED</p>";
    }
    echo "</div>";
    
    echo "</body></html>";
}
    
    /**
     * Process single product (for testing)
     */
    public function process_single_product() {
        $product_id = $this->input->post('product_id');
        
        if (!$product_id) {
            show_error('Product ID required');
        }
        
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Process Single Product</title>
            <style>
                body { font-family: Arial, sans-serif; background: #0f0f1a; color: #e2f0e8; padding: 20px; max-width: 1200px; margin: 0 auto; }
                .box { background: #1a1f2e; border-radius: 12px; padding: 20px; margin-bottom: 20px; border: 1px solid #2a3346; }
                .success { color: #4ade80; }
                .error { color: #ef4444; }
                .warning { color: #f59e0b; }
                .info { color: #8b5cf6; }
                .step { padding: 10px 14px; margin: 8px 0; border-radius: 8px; background: #0f1420; }
                .back-btn { display: inline-block; padding: 8px 16px; background: #8b5cf6; color: white; text-decoration: none; border-radius: 8px; }
            </style>
        </head>
        <body>
            <div style='display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;'>
                <h1>🔍 Processing Product: " . htmlspecialchars($product_id) . "</h1>
                <a href='" . base_url('test_discovery/test_cron') . "' class='back-btn'>← Back</a>
            </div>";
        
        $this->load->model('BrandCreator_model');
        
        // Cek apakah sudah diproses
        if ($this->BrandCreator_model->is_product_processed($product_id)) {
            echo "<div class='box'><div class='warning'>⚠️ Product already processed!</div></div>";
            echo "</body></html>";
            return;
        }
        
        // Ambil product data
        $product = $this->db->select('ap.*, b.id as brand_id, b.name as brand_name, b.shop_name')
            ->from('affiliate_products ap')
            ->join('brands b', 'ap.shop_name = b.shop_name', 'left')
            ->where('ap.product_id', $product_id)
            ->get()
            ->row();
        
        if (!$product) {
            echo "<div class='box'><div class='error'>❌ Product not found!</div></div>";
            echo "</body></html>";
            return;
        }
        
        echo "<div class='box'>";
        echo "<h3>📦 Product Info</h3>";
        echo "<div style='display:grid; grid-template-columns: 1fr 1fr; gap:12px;'>";
        echo "<div><span class='info'>Product Name:</span> " . htmlspecialchars($product->product_name) . "</div>";
        echo "<div><span class='info'>Shop Name:</span> " . htmlspecialchars($product->shop_name) . "</div>";
        echo "<div><span class='info'>Brand ID:</span> " . ($product->brand_id ?? 'Tidak ada') . "</div>";
        echo "<div><span class='info'>Review Status:</span> " . $product->review_status . "</div>";
        echo "</div>";
        echo "</div>";
        
        // Proses
        echo "<div class='box'>";
        echo "<h3>📡 Fetching creators from FastMoss...</h3>";
        
        try {
            $result = $this->BrandCreator_model->get_product_creators($product_id);
            
            if (!$result['status'] || empty($result['creators'])) {
                echo "<div class='warning'>⚠️ No creators found</div>";
                $this->BrandCreator_model->mark_product_processed($product_id);
                echo "</div>";
                echo "</body></html>";
                return;
            }
            
            $creators = $result['creators'];
            echo "<div class='success'>✅ Found " . count($creators) . " creators</div>";
            
            // Simpan
            if ($product->brand_id) {
                $save_result = $this->BrandCreator_model->save_creators_from_fastmoss(
                    $product->brand_id,
                    $creators,
                    $this->default_is_id
                );
                
                echo "<div style='margin-top:12px;'>";
                echo "<div class='success'>💾 Saved: {$save_result['saved']} to brand_creators</div>";
                echo "<div class='success'>📋 Added: {$save_result['added_to_creators']} to creators (TASK 1)</div>";
                echo "<div class='warning'>⏭️ Skipped: {$save_result['skipped']} (already exists)</div>";
                echo "</div>";
            } else {
                echo "<div class='warning'>⚠️ No brand_id found, cannot save</div>";
            }
            
            // Mark as processed
            $this->BrandCreator_model->mark_product_processed($product_id);
            echo "<div class='success'>✅ Product marked as processed</div>";
            
        } catch (Exception $e) {
            echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
        }
        
        echo "</div>";
        echo "</body></html>";
    }
    
    /**
     * Run cron job manually (for testing)
     * Memanggil method di controller Bd
     */
public function run_cron_manual() {
    // Load model
    $this->load->model('BrandCreator_model');
    $this->load->model('User_log_model');
    
    set_time_limit(0);
    
    // AMBIL PRODUCT YANG BARU APPROVED
    $products = $this->BrandCreator_model->get_newly_approved_products(20);
    
    // Tampilkan hasil di browser (bukan redirect)
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Cron Job Manual Run</title>
        <style>
            body { font-family: monospace; background: #0f0f1a; color: #e2f0e8; padding: 20px; }
            .success { color: #4ade80; }
            .error { color: #ef4444; }
            .warning { color: #f59e0b; }
            .info { color: #8b5cf6; }
            .box { background: #1a1f2e; border-radius: 12px; padding: 20px; margin-bottom: 20px; border: 1px solid #2a3346; }
            .back-btn { display: inline-block; padding: 8px 16px; background: #8b5cf6; color: white; text-decoration: none; border-radius: 8px; }
            .back-btn:hover { background: #7c3aed; }
        </style>
    </head>
    <body>
        <div style='display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;'>
            <h1>🔄 Cron Job Manual Run</h1>
            <a href='" . base_url('test_discovery/test_cron') . "' class='back-btn'>← Back</a>
        </div>
        
        <div class='box'>";
    
    if (empty($products)) {
        echo "<div class='warning'>" . date('Y-m-d H:i:s') . " - No newly approved products found</div>";
        echo "</div></body></html>";
        return;
    }
    
    echo "<div class='info'>" . date('Y-m-d H:i:s') . " - Found " . count($products) . " newly approved products</div><br>";
    
    $total_creators = 0;
    $total_added = 0;
    $processed = 0;
    $errors = 0;
    
    foreach ($products as $product) {
        echo "<div style='margin-top:12px; padding:10px; background:#0f1420; border-radius:8px;'>";
        echo "<strong>📦 Processing: {$product->product_id}</strong><br>";
        echo "<span style='font-size:12px; color:#6b7280;'>" . htmlspecialchars($product->product_name) . "</span><br>";
        
        try {
            // CEK APAKAH SUDAH DIPROSES
            if ($this->BrandCreator_model->is_product_processed($product->product_id)) {
                echo "<span class='warning'>⚠️ Product already processed, skipping...</span><br>";
                continue;
            }
            
            // AMBIL CREATOR DARI FASTMOSS
            echo "<span class='info'>📡 Fetching creators from FastMoss...</span><br>";
            $result = $this->BrandCreator_model->get_product_creators($product->product_id);
            
            if (!$result['status'] || empty($result['creators'])) {
                echo "<span class='warning'>⚠️ No creators found</span><br>";
                $this->BrandCreator_model->mark_product_processed($product->product_id);
                $processed++;
                continue;
            }
            
            $creators = $result['creators'];
            echo "<span class='success'>✅ Found " . count($creators) . " creators</span><br>";
            
            // SIMPAN KE DATABASE
            $brand_id = $product->brand_id;
            if (!$brand_id) {
                echo "<span class='warning'>⚠️ No brand_id found for shop: {$product->shop_name}</span><br>";
                $this->BrandCreator_model->mark_product_processed($product->product_id);
                $processed++;
                continue;
            }
            
            $save_result = $this->BrandCreator_model->save_creators_from_fastmoss(
                $brand_id,
                $creators,
                1 // Toopai
            );
            
            $total_creators += count($creators);
            $total_added += $save_result['added_to_creators'];
            
            echo "<span class='success'>💾 Saved: {$save_result['saved']} | Added to creators: {$save_result['added_to_creators']} | Skipped: {$save_result['skipped']}</span><br>";
            
            // MARK AS PROCESSED
            $this->BrandCreator_model->mark_product_processed($product->product_id);
            $processed++;
            
        } catch (Exception $e) {
            $errors++;
            echo "<span class='error'>❌ Error: " . $e->getMessage() . "</span><br>";
        }
        
        echo "</div>";
    }
    
    // LOG ACTIVITY
    $this->User_log_model->log(
        1,
        'system',
        'SYSTEM',
        'CRON_PROCESS_APPROVED_PRODUCTS_MANUAL',
        "Processed {$processed} products, found {$total_creators} creators, added {$total_added} to creators, {$errors} errors"
    );
    
    echo "<br><div class='success'>✅ Summary: Processed {$processed} products, {$total_creators} creators found, {$total_added} added to creators, {$errors} errors</div>";
    
    echo "</div></body></html>";
}
    
    /**
     * Display creators
     */
    private function _display_creators($creators) {
        if (empty($creators)) return;
        
        echo "<div style='margin-top: 16px;'>";
        echo "<h4>📋 Detail Creator</h4>";
        
        $display_creators = array_slice($creators, 0, 20);
        
        echo "<table>";
        echo "<thead>
            <tr>
                <th>#</th>
                <th>Creator</th>
                <th>Username</th>
                <th>Followers</th>
                <th>GMV</th>
                <th>Sold</th>
                <th>Start Promoting</th>
            </tr>
        </thead>";
        echo "<tbody>";
        
        $rank = 1;
        foreach ($display_creators as $creator) {
            $followers_show = $creator['followers_show'] ?? number_format($creator['followers'] ?? 0);
            $gmv_show = $creator['gmv_from_this_product_show'] ?? 'Rp ' . number_format($creator['gmv_from_this_product'] ?? 0);
            
            echo "<tr>
                <td>{$rank}</td>
                <td><strong>" . htmlspecialchars($creator['creator_name'] ?? $creator['creator_username'] ?? '-') . "</strong></td>
                <td>@" . htmlspecialchars($creator['creator_username'] ?? '-') . "</td>
                <td>{$followers_show}</td>
                <td style='color:#4ade80;'>{$gmv_show}</td>
                <td>" . number_format($creator['sold_from_this_product'] ?? 0) . "</td>
                <td>" . ($creator['start_promoting'] ? date('d/m/Y', strtotime($creator['start_promoting'])) : '-') . "</td>
            </tr>";
            $rank++;
        }
        
        if (count($creators) > 20) {
            echo "<tr><td colspan='7' style='text-align:center; color:#6b7280;'>... dan " . (count($creators) - 20) . " creator lainnya</td></tr>";
        }
        
        echo "</tbody></table>";
        echo "</div>";
    }
    
    private function _display_header($brand) {
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Discovery Result - " . htmlspecialchars($brand->name) . "</title>
            <style>
                body { font-family: Arial, sans-serif; background: #0f0f1a; color: #e2f0e8; padding: 20px; max-width: 1200px; margin: 0 auto; }
                .box { background: #1a1f2e; border-radius: 12px; padding: 20px; margin-bottom: 20px; border: 1px solid #2a3346; }
                .success { color: #4ade80; }
                .error { color: #ef4444; }
                .warning { color: #f59e0b; }
                .info { color: #8b5cf6; }
                .step { padding: 10px 14px; margin: 8px 0; border-radius: 8px; background: #0f1420; }
                .step-success { border-left: 4px solid #4ade80; }
                .step-error { border-left: 4px solid #ef4444; }
                .step-info { border-left: 4px solid #8b5cf6; }
                .step-warning { border-left: 4px solid #f59e0b; }
                table { width: 100%; border-collapse: collapse; font-size: 13px; }
                th, td { padding: 10px; text-align: left; border-bottom: 1px solid #2a3346; }
                th { color: #8b5cf6; }
                .badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 11px; }
                .badge-success { background: rgba(74,222,128,0.15); color: #4ade80; }
                .badge-warning { background: rgba(245,158,11,0.15); color: #f59e0b; }
                .badge-info { background: rgba(139,92,246,0.15); color: #8b5cf6; }
                .back-btn { display: inline-block; padding: 8px 16px; background: #8b5cf6; color: white; text-decoration: none; border-radius: 8px; }
                .back-btn:hover { background: #7c3aed; }
                .summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin: 12px 0; }
                .summary-item { background: #0f1420; padding: 12px; border-radius: 8px; text-align: center; }
                .summary-item .number { font-size: 24px; font-weight: 700; }
                .summary-item .label { font-size: 11px; color: #6b7280; margin-top: 4px; }
            </style>
        </head>
        <body>
            <div style='display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;'>
                <h1>🔍 Discovery Result</h1>
                <a href='" . base_url('test_discovery') . "' class='back-btn'>← Back</a>
            </div>
            
            <div class='box'>
                <h2>🏷️ Brand: " . htmlspecialchars($brand->name) . "</h2>
                <div style='display:grid; grid-template-columns: 1fr 1fr; gap:12px;'>
                    <div><span class='info'>ID:</span> {$brand->id}</div>
                    <div><span class='info'>Shop Name:</span> " . htmlspecialchars($brand->shop_name ?? '-') . "</div>
                </div>
            </div>
            
            <div class='box'>
                <h3>📋 Proses Discovery</h3>";
    }
    
    private function _display_footer() {
        echo "</div>";
        echo "
        <div class='box'>
            <h3>📊 Summary</h3>
            <p>Proses discovery selesai. Data tersimpan di database.</p>
            <ul>
                <li>✅ Creator tersimpan di <strong>brand_creators</strong></li>
                <li>✅ Creator baru ditambahkan ke <strong>creators</strong> dengan status <strong>PENDING (TASK 1)</strong></li>
                <li>✅ Handler: <strong>Toopai</strong> (IS ID: 1)</li>
                <li>✅ Creator yang sudah ada di <strong>creators</strong> akan di-skip</li>
            </ul>
            <p><a href='" . base_url('test_discovery') . "' class='back-btn'>← Kembali ke Halaman Utama</a></p>
        </div>
        </body></html>";
    }
    
    /**
     * View creators for a specific brand
     */
    public function view_creators($brand_id) {
        $brand = $this->db->select('id, name, shop_name')
            ->where('id', $brand_id)
            ->get('brands')
            ->row();
        
        if (!$brand) {
            show_error('Brand not found');
        }
        
        $creators = $this->db->where('brand_id', $brand_id)
            ->order_by('total_gmv', 'DESC')
            ->get('brand_creators')
            ->result();
        
        foreach ($creators as $creator) {
            $exists = $this->db->where('username', $creator->creator_username)
                ->get('creators')
                ->row();
            $creator->in_creators = !empty($exists);
            $creator->creator_status = $exists->status ?? null;
        }
        
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Creators - " . htmlspecialchars($brand->name) . "</title>
            <style>
                body { font-family: Arial, sans-serif; background: #0f0f1a; color: #e2f0e8; padding: 20px; max-width: 1200px; margin: 0 auto; }
                .box { background: #1a1f2e; border-radius: 12px; padding: 20px; margin-bottom: 20px; border: 1px solid #2a3346; }
                .info { color: #8b5cf6; }
                table { width: 100%; border-collapse: collapse; font-size: 13px; }
                th, td { padding: 10px; text-align: left; border-bottom: 1px solid #2a3346; }
                th { color: #8b5cf6; }
                .badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 11px; }
                .badge-warning { background: rgba(245,158,11,0.15); color: #f59e0b; }
                .badge-success { background: rgba(74,222,128,0.15); color: #4ade80; }
                .badge-danger { background: rgba(239,68,68,0.15); color: #ef4444; }
                .badge-info { background: rgba(139,92,246,0.15); color: #8b5cf6; }
                .back-btn { display: inline-block; padding: 8px 16px; background: #8b5cf6; color: white; text-decoration: none; border-radius: 8px; }
                .back-btn:hover { background: #7c3aed; }
                .summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin: 12px 0; }
                .summary-item { background: #0f1420; padding: 12px; border-radius: 8px; text-align: center; }
                .summary-item .number { font-size: 24px; font-weight: 700; }
                .summary-item .label { font-size: 11px; color: #6b7280; margin-top: 4px; }
            </style>
        </head>
        <body>
            <div style='display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;'>
                <h1>📋 Creators for " . htmlspecialchars($brand->name) . "</h1>
                <a href='" . base_url('test_discovery') . "' class='back-btn'>← Back</a>
            </div>
            
            <div class='box'>
                <div style='display:grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap:12px;'>
                    <div><span class='info'>Brand:</span> " . htmlspecialchars($brand->name) . "</div>
                    <div><span class='info'>Shop Name:</span> " . htmlspecialchars($brand->shop_name) . "</div>
                    <div><span class='info'>Total Creators:</span> <strong>" . count($creators) . "</strong></div>
                    <div><span class='info'>In Task 1:</span> <strong>" . count(array_filter($creators, function($c) { return $c->in_creators; })) . "</strong></div>
                </div>
            </div>";
        
        if (!empty($creators)) {
            echo "<div class='box'>";
            echo "<table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Creator</th>
                        <th>Nickname</th>
                        <th>Followers</th>
                        <th>GMV</th>
                        <th>In Task 1?</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>";
            
            $rank = 1;
            foreach ($creators as $creator) {
                $followers_display = $creator->follower_count >= 1000000 ? number_format($creator->follower_count/1000000, 1) . 'M' : 
                                     ($creator->follower_count >= 1000 ? number_format($creator->follower_count/1000, 1) . 'K' : $creator->follower_count);
                
                $in_creators = $creator->in_creators;
                $badge = $in_creators ? 
                    '<span class="badge badge-success">✅ Yes (Status: ' . $creator->creator_status . ')</span>' : 
                    '<span class="badge badge-warning">⏳ PENDING</span>';
                
                echo "<tr>
                    <td>{$rank}</td>
                    <td><strong>@" . htmlspecialchars($creator->creator_username) . "</strong></td>
                    <td>" . htmlspecialchars($creator->creator_nickname) . "</td>
                    <td>{$followers_display}</td>
                    <td style='color:#4ade80;'>Rp " . number_format($creator->total_gmv) . "</td>
                    <td>{$badge}</td>
                    <td><span class='badge badge-info'>" . $creator->status . "</span></td>
                </tr>";
                $rank++;
            }
            
            echo "</tbody></table></div>";
        } else {
            echo "<div class='box'><p class='info'>Belum ada creator untuk brand ini. Lakukan discovery terlebih dahulu.</p></div>";
        }
        
        echo "</body></html>";
    }
}