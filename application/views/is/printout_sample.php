<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Request - <?= $printout['request_code'] ?? 'PRINTOUT' ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            background: #0f0f1a;
            color: #e2f0e8;
            padding: 20px;
            min-height: 100vh;
        }
        .print-container {
            max-width: 800px;
            margin: 0 auto;
            background: #1a1f2e;
            border-radius: 16px;
            padding: 30px;
            border: 1px solid #2a3346;
        }
        .print-header {
            text-align: center;
            border-bottom: 2px solid #4ade80;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .print-header h1 {
            color: #4ade80;
            font-size: 24px;
            letter-spacing: 2px;
        }
        .print-header .code {
            color: #fbbf24;
            font-size: 14px;
            margin-top: 4px;
        }
        .print-header .date {
            color: #9aaebe;
            font-size: 12px;
        }
        
        .section {
            margin-bottom: 24px;
            border: 1px solid #2a3346;
            border-radius: 12px;
            padding: 16px;
            background: #0f1420;
        }
        .section-title {
            color: #8b5cf6;
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #2a3346;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 16px;
        }
        .info-grid .label {
            color: #9aaebe;
            font-size: 11px;
        }
        .info-grid .value {
            color: #e2f0e8;
            font-size: 13px;
            font-weight: 500;
        }
        .info-grid .full-width {
            grid-column: 1 / -1;
        }
        
        .product-list {
            margin-top: 8px;
        }
        .product-item {
            display: flex;
            flex-direction: column;
            padding: 10px 12px;
            border-bottom: 1px solid #2a3346;
            background: #1a1f2e;
            border-radius: 8px;
            margin-bottom: 8px;
        }
        .product-item:last-child { border-bottom: none; margin-bottom: 0; }
        .product-item .product-name {
            color: #4ade80;
            font-weight: 600;
            font-size: 13px;
        }
        .product-item .product-detail {
            display: flex;
            gap: 16px;
            margin-top: 4px;
            flex-wrap: wrap;
        }
        .product-item .product-detail span {
            font-size: 11px;
            color: #9aaebe;
        }
        .product-item .product-detail .varian {
            color: #fbbf24;
        }
        .product-item .product-detail .notes {
            color: #8b5cf6;
        }
        
        .address-box {
            background: #0a0e17;
            border-radius: 8px;
            padding: 12px;
            border-left: 3px solid #4ade80;
            margin-top: 8px;
            white-space: pre-wrap;
            font-size: 13px;
            line-height: 1.6;
        }
        
        .footer-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid #2a3346;
        }
        .btn {
            padding: 10px 24px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.2s;
            font-family: inherit;
        }
        .btn-print {
            background: #4ade80;
            color: #0a0e17;
        }
        .btn-print:hover {
            background: #22c55e;
            transform: translateY(-2px);
        }
        .btn-back {
            background: #1e293b;
            color: #e2f0e8;
            border: 1px solid #2a3346;
        }
        .btn-back:hover {
            background: #2a3346;
        }
        
        /* Brand Group Styling */
        .brand-group {
            border: 1px solid #2a3346;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 12px;
            background: #0f1420;
        }
        .brand-group-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 8px;
            border-bottom: 1px solid #2a3346;
            margin-bottom: 8px;
        }
        .brand-group-header .brand-name {
            color: #8b5cf6;
            font-weight: 600;
            font-size: 14px;
        }
        .brand-group-header .sample-type {
            font-size: 10px;
            padding: 2px 10px;
            border-radius: 12px;
        }
        .sample-type-manual {
            background: rgba(74,222,128,0.2);
            color: #4ade80;
        }
        .sample-type-auto {
            background: rgba(139,92,246,0.2);
            color: #8b5cf6;
        }
        
        @media print {
            body { background: white; padding: 0; }
            .print-container { 
                background: white; 
                border: none; 
                padding: 20px;
                border-radius: 0;
                max-width: 100%;
            }
            .print-container * { color: #111 !important; }
            .print-header h1 { color: #059669 !important; }
            .section { border-color: #ddd; background: #f9fafb; }
            .section-title { color: #7c3cff !important; }
            .product-item { background: #f3f4f6; border-color: #ddd; }
            .address-box { background: #f0fdf4; border-color: #059669; }
            .brand-group { border-color: #ddd; background: #f9fafb; }
            .brand-group-header .brand-name { color: #7c3cff !important; }
            .btn-print, .btn-back { display: none !important; }
            .footer-actions { display: none !important; }
            .info-grid .value { color: #111 !important; }
            .product-item .product-detail span { color: #4b5563 !important; }
            .product-item .product-name { color: #059669 !important; }
        }
        
        @media (max-width: 600px) {
            .info-grid { grid-template-columns: 1fr; }
            .print-container { padding: 16px; }
            .product-item .product-detail { flex-direction: column; gap: 4px; }
        }
    </style>
</head>
<body>
    <div class="print-container" id="printArea">
        <!-- HEADER -->
        <div class="print-header">
            <h1>SAMPLE REQUEST</h1>
            <div class="code">Kode: <?= $printout['request_code'] ?? '-' ?></div>
            <div class="date">Tanggal: <?= isset($printout['request_date']) ? date('d F Y H:i', strtotime($printout['request_date'])) : '-' ?></div>
        </div>
        
        <!-- CREATOR INFO (PENERIMA) -->
        <div class="section">
            <div class="section-title">👤 Creator / Penerima</div>
            <div class="info-grid">
                <div>
                    <div class="label">Username</div>
                    <div class="value">@<?= htmlspecialchars($printout['creator']['username'] ?? '-') ?></div>
                </div>
                <div>
                    <div class="label">Nama Lengkap</div>
                    <div class="value"><?= htmlspecialchars($printout['creator']['full_name'] ?? '-') ?></div>
                </div>
                <div>
                    <div class="label">WhatsApp</div>
                    <div class="value"><?= htmlspecialchars($printout['creator']['phone'] ?? '-') ?></div>
                </div>
                <div>
                    <div class="label">Email</div>
                    <div class="value"><?= htmlspecialchars($printout['creator']['email'] ?? '-') ?></div>
                </div>
                <div class="full-width">
                    <div class="label">Alamat Pengiriman (Creator)</div>
                    <div class="address-box"><?= htmlspecialchars($printout['creator']['alamat'] ?? '-') ?></div>
                </div>
            </div>
        </div>
        
        <!-- BRAND INFO (PENGIRIM) - MULTIPLE BRAND -->
        <div class="section">
            <div class="section-title"> Brand / Pengirim</div>
            
            <?php if (!empty($printout['brand_groups'])): ?>
                <?php foreach ($printout['brand_groups'] as $index => $group): ?>
                <div class="brand-group">
                    <div class="brand-group-header">
                        <span class="brand-name">
                            <i class="fas fa-store"></i> 
                            <?= htmlspecialchars($group['brand_name'] ?? 'Brand Tidak Diketahui') ?>
                            <?php if (!empty($group['brand_shop_name'])): ?>
                            <span style="font-size:11px; color:#9aaebe; font-weight:normal;">
                                (<?= htmlspecialchars($group['brand_shop_name']) ?>)
                            </span>
                            <?php endif; ?>
                        </span>
                        <span class="sample-type <?= ($group['sample_type'] ?? 'manual') == 'auto' ? 'sample-type-auto' : 'sample-type-manual' ?>">
                            <?= ($group['sample_type'] ?? 'manual') == 'auto' ? ' Auto' : 'Manual' ?>
                        </span>
                    </div>
                    <div class="info-grid">
                        <?php if (!empty($group['brand_whatsapp'])): ?>
                        <div>
                            <div class="label">WhatsApp Brand</div>
                            <div class="value" style="color:#4ade80;"><?= htmlspecialchars($group['brand_whatsapp']) ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($group['brand_email'])): ?>
                        <div>
                            <div class="label">Email Brand</div>
                            <div class="value"><?= htmlspecialchars($group['brand_email']) ?></div>
                        </div>
                        <?php endif; ?>
                        <div class="full-width">
                            <div class="label">Alamat Brand</div>
                            <div class="address-box" style="border-left-color:#8b5cf6;">
                                <?= htmlspecialchars($group['brand_address'] ?? 'Alamat brand tidak tersedia') ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Products per Brand -->
                    <?php if (!empty($group['products'])): ?>
                    <div style="margin-top:12px;">
                        <div style="color:#9aaebe; font-size:11px; margin-bottom:8px;">
                            <i class="fas fa-box"></i> Daftar Produk:
                        </div>
                        <?php foreach ($group['products'] as $product): ?>
                        <div class="product-item">
                            <div class="product-name">
                                <?= htmlspecialchars($product['product_name'] ?? '-') ?>
                                <span style="font-size:10px; color:#fbbf24; margin-left:8px;">
                                    <?= $product['commission_rate'] ?? 0 ?>%
                                </span>
                            </div>
                            <div class="product-detail">
                                <?php if (!empty($product['varian'])): ?>
                                <span class="varian"> Varian: <?= htmlspecialchars($product['varian']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($product['product_notes'])): ?>
                                <span class="notes"> Catatan: <?= htmlspecialchars($product['product_notes']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                
                <!-- Total -->
                <div style="margin-top:12px; text-align:right; color:#9aaebe; font-size:11px;">
                    Total Brand: <strong><?= count($printout['brand_groups']) ?></strong> | 
                    Total Produk: <strong><?= count($printout['products'] ?? []) ?></strong>
                </div>
                
            <?php else: ?>
                <div style="color:#ef4444; text-align:center; padding:20px;">
                    <i class="fas fa-exclamation-triangle"></i> Tidak ada data brand
                </div>
            <?php endif; ?>
        </div>
        
        <!-- FOOTER NOTES -->
        <div class="section">
            <div class="section-title"> Catatan Pengiriman</div>
            <div style="font-size: 12px; color: #9aaebe; line-height: 1.6;">
                <p>1. Sample dikirim sesuai dengan varian yang tertera di atas.</p>
                <p>2. Harap konfirmasi setelah sample diterima.</p>
                <p>3. Sample ini adalah untuk keperluan review / konten creator.</p>
                <p>4. Jika ada kendala, hubungi PIC brand yang tertera.</p>
            </div>
            <div style="margin-top: 8px; font-size: 11px; color: #4ade80; text-align: center;">
                Dikirim oleh: Toopai System
            </div>
        </div>
        
        <!-- ACTIONS -->
        <div class="footer-actions">
            <button class="btn btn-print" onclick="window.print()">
                <i class="fas fa-print"></i> ️ Print / PDF
            </button>
            <button class="btn btn-back" onclick="window.close()">
                <i class="fas fa-arrow-left"></i> Tutup
            </button>
        </div>
    </div>
    
    <script>
        // Auto print jika parameter print=1
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('print') === '1') {
            setTimeout(() => window.print(), 500);
        }
        
        // Keyboard shortcut Ctrl+P
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>