<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg: #090e17;
            --card-bg: rgba(255, 255, 255, 0.03);
            --card-border: rgba(255, 255, 255, 0.08);
            --text-primary: #e2f0e8;
            --text-secondary: #9aaebe;
            --tiktok-pink: #fe2c55;
            --tiktok-cyan: #25f4ee;
            --glow: rgba(254, 44, 85, 0.15);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg);
            color: var(--text-primary);
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            padding: 16px;
            background-image: radial-gradient(circle at 50% 0%, rgba(139, 92, 246, 0.15) 0%, transparent 60%);
        }

        .wrapper {
            max-width: 480px;
            width: 100%;
            margin: 0 auto;
        }

        /* Brand Header Card */
        .brand-header-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.05), rgba(255,255,255,0.01));
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 24px;
            text-align: center;
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }

        .brand-header-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--tiktok-pink), var(--tiktok-cyan));
        }

        .brand-logo-placeholder {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, rgba(254, 44, 85, 0.2), rgba(37, 244, 238, 0.2));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 28px;
            color: white;
            border: 2px solid rgba(255,255,255,0.1);
        }

        .brand-name {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-bottom: 6px;
            background: linear-gradient(135deg, #fff, var(--text-secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .campaign-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--card-border);
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 11px;
            color: var(--text-secondary);
            font-weight: 500;
            margin-top: 6px;
        }

        .campaign-badge i {
            color: var(--tiktok-pink);
        }

        .instructions {
            font-size: 13px;
            color: var(--text-secondary);
            margin-top: 16px;
            line-height: 1.5;
        }

        /* Products List */
        .products-container {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 30px;
        }

        .product-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 18px;
            padding: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-2px);
            border-color: rgba(255,255,255,0.15);
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }

        .product-img {
            width: 76px;
            height: 76px;
            border-radius: 12px;
            object-fit: cover;
            background: #151b26;
            flex-shrink: 0;
            border: 1px solid rgba(255,255,255,0.05);
        }

        .product-info {
            flex: 1;
            min-width: 0;
        }

        .product-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            line-height: 1.4;
            margin-bottom: 6px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .product-metrics {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            font-size: 11px;
            color: var(--text-secondary);
        }

        .metric-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .metric-item i {
            font-size: 10px;
        }

        .commission-badge {
            background: rgba(254, 44, 85, 0.1);
            border: 1px solid rgba(254, 44, 85, 0.2);
            color: var(--tiktok-pink);
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 30px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* Action Button */
        .btn-add {
            background: linear-gradient(135deg, var(--tiktok-pink), #e01e47);
            border: none;
            color: white;
            padding: 10px 14px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(254, 44, 85, 0.25);
            flex-shrink: 0;
            align-self: flex-end;
            margin-bottom: 6px;
        }

        .btn-add:hover {
            transform: scale(1.03);
            box-shadow: 0 6px 16px rgba(254, 44, 85, 0.4);
        }

        /* Footer */
        .footer-logo {
            text-align: center;
            margin-top: 40px;
            margin-bottom: 20px;
            opacity: 0.6;
        }

        .footer-logo img {
            height: 20px;
        }
        
        .footer-text {
            text-align: center;
            font-size: 11px;
            color: var(--text-secondary);
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Brand Header -->
        <div class="brand-header-card">
            <div class="brand-logo-placeholder">
                <i class="fas fa-store"></i>
            </div>
            <div class="brand-name"><?= htmlspecialchars($brand_name) ?></div>
            
            <?php if (!empty($palette->campaign_name)): ?>
            <div class="campaign-badge">
                <i class="fas fa-bullhorn"></i> <?= htmlspecialchars($palette->campaign_name) ?>
            </div>
            <?php endif; ?>
            
            <p class="instructions">
                Halo Kak! Pilih produk di bawah ini untuk ditambahkan ke <strong>Showcase TikTok</strong> Anda dan mulailah berkolaborasi.
            </p>
        </div>

        <!-- Products List -->
        <div class="products-container">
            <?php foreach ($products as $p): ?>
            <div class="product-card">
                <?php if (!empty($p->image_url)): ?>
                    <img src="<?= htmlspecialchars($p->image_url) ?>" class="product-img" alt="<?= htmlspecialchars($p->product_name) ?>" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'76\' height=\'76\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%239aaebe\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><rect x=\'3\' y=\'3\' width=\'18\' height=\'18\' rx=\'2\' ry=\'2\'></rect><polyline points=\'21 15 16 10 5 21\'></polyline></svg>';">
                <?php else: ?>
                    <div class="product-img" style="display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-box fa-2x" style="color:var(--text-secondary);"></i>
                    </div>
                <?php endif; ?>
                
                <div class="product-info">
                    <div class="product-title" title="<?= htmlspecialchars($p->product_name) ?>">
                        <?= htmlspecialchars($p->product_name) ?>
                    </div>
                    <div class="product-metrics">
                        <?php 
                            $price = $p->price ?? 0;
                            if ($price > 0): 
                        ?>
                        <div class="metric-item">
                            <i class="fas fa-tag"></i> Rp <?= number_format($price, 0, ',', '.') ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($p->sales_count)): ?>
                        <div class="metric-item">
                            <i class="fas fa-chart-line"></i> <?= number_format($p->sales_count, 0, ',', '.') ?> terjual
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="commission-badge">
                        <i class="fas fa-percent"></i> Komisi: <?= floatval($p->commission_rate) ?>%
                    </div>
                </div>

                <a href="<?= htmlspecialchars($p->affiliate_link) ?>" target="_blank" class="btn-add">
                    <i class="fab fa-tiktok"></i> Tambah
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Footer -->
        <div class="footer-logo">
            <h3 style="font-weight: 800; font-size: 16px; background: linear-gradient(135deg, var(--tiktok-pink), var(--tiktok-cyan)); -webkit-background-clip: text; background-clip: text; color: transparent; display: inline-block;">TOOPAI.AI</h3>
        </div>
        <div class="footer-text">
            &copy; <?= date('Y') ?> Toopai.ai. All rights reserved.
        </div>
    </div>
</body>
</html>
