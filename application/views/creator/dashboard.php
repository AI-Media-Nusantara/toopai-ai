<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>Toopai | Creator Hub</title>
     <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/logo/favicon_new.png') ?>">
    <link rel="icon" type="image/svg+xml" href="<?= base_url('assets/logo/favicon_new.png') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/logo/favicon_new.png') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-font-smoothing: antialiased;
        }

        body {
            font-family: -apple-system, 'SF Pro Text', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background: #07070a;
            color: #ffffff;
            font-size: 15px;
            line-height: 1.4;
        }

        /* Fixed header */
        .fixed-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(7, 7, 10, 0.92);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            z-index: 100;
            padding: 14px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .header-content {
            max-width: 500px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 22px;
            font-weight: 800;
            background: linear-gradient(135deg, #c084fc, #22d3ee);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            letter-spacing: -0.5px;
        }

        .notification-icon {
            width: 38px;
            height: 38px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .notification-icon:active { 
            background: rgba(255, 255, 255, 0.12);
            transform: scale(0.95);
        }

        .main-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 76px 0 100px;
        }

        /* Greeting card */
        .greeting-card {
            background: linear-gradient(135deg, #12131a, #0b0c10);
            margin: 12px 16px 20px;
            border-radius: 24px;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
        }
        .greeting-text h2 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(245, 158, 11, 0.12);
            padding: 5px 14px;
            border-radius: 40px;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        .status-badge span {
            font-size: 12px;
            color: #fbbf24;
            font-weight: 600;
        }
        .creator-badge {
            width: 8px;
            height: 8px;
            background: #f59e0b;
            border-radius: 50%;
            box-shadow: 0 0 8px #f59e0b;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(0.9); }
        }
        .greeting-avatar {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #8b5cf6, #06b6d4);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: 700;
            box-shadow: 0 0 12px rgba(139,92,246,0.4);
        }
        .greeting-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        /* TikTok connect banner - enhanced */
        .upgrade-card-mini {
            background: linear-gradient(135deg, #161224, #0b0c10);
            margin: 0 16px 20px;
            border-radius: 24px;
            padding: 16px 20px;
            border: 1px solid rgba(139, 92, 246, 0.4);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.2s;
        }
        .upgrade-card-mini:active { transform: scale(0.99); }
        .upgrade-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .tiktok-icon {
            width: 48px;
            height: 48px;
            background: #000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255,255,255,0.1);
            box-shadow: 1.5px 1.5px 0 #25F4EE, -1.5px -1.5px 0 #FE2C55;
        }
        .tiktok-icon i { font-size: 22px; color: white; }
        .upgrade-text h4 { font-size: 15px; font-weight: 600; margin-bottom: 4px; }
        .upgrade-text p { font-size: 11px; color: #9ca3af; }
        .btn-upgrade-mini {
            background: #ffffff;
            padding: 10px 22px;
            border-radius: 40px;
            color: #000;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-block;
        }
        .btn-upgrade-mini:active { transform: scale(0.95); background: #f0f0f0; }

        /* stat row - improved spacing */
        .stats-row {
            display: flex;
            gap: 12px;
            padding: 0 16px;
            margin-bottom: 24px;
        }
        .stat-box {
            flex: 1;
            background: #111217;
            border-radius: 20px;
            padding: 16px 8px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.04);
            transition: all 0.2s;
        }
        .stat-box:active { background: #161722; }
        .stat-value {
            font-size: 18px;
            font-weight: 800;
            white-space: nowrap;
            overflow-x: auto;
            scrollbar-width: none;
            letter-spacing: -0.3px;
        }
        .stat-value::-webkit-scrollbar { display: none; }
        .stat-box:nth-child(1) .stat-value { color: #4ade80; }
        .stat-box:nth-child(3) .stat-value { color: #c084fc; }
        .stat-label {
            font-size: 10px;
            color: #8b8f9e;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-top: 8px;
        }

        /* CAMPAIGN CARD — PERFECT BANNER (no crop) */
        .campaign-card {
            background: #111217;
            margin: 0 16px 24px;
            border-radius: 28px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.05);
            transition: transform 0.2s;
        }
        .campaign-card:active { transform: scale(0.99); }
        .campaign-banner-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 16 / 9;
            overflow: hidden;
            background: linear-gradient(135deg, #1a1a2e, #0f0f1a);
        }
        .campaign-banner-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
        }
        .campaign-banner-placeholder {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background: linear-gradient(135deg, #1c1d2b, #12131e);
        }
        .campaign-banner-placeholder i {
            font-size: 48px;
            color: rgba(139, 92, 246, 0.3);
        }
        .campaign-banner-placeholder p {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
        }
        .campaign-info {
            padding: 18px 20px;
            background: #0d0e12;
        }
        .campaign-tag {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            color: #a78bfa;
            background: rgba(167, 139, 250, 0.12);
            padding: 4px 12px;
            border-radius: 40px;
            display: inline-block;
            margin-bottom: 12px;
            letter-spacing: 0.5px;
        }
        .campaign-name {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 14px;
            line-height: 1.35;
        }
        .campaign-stats {
            display: flex;
            gap: 24px;
            font-size: 13px;
            color: #b9c0d4;
            border-top: 1px solid rgba(255,255,255,0.06);
            padding-top: 14px;
        }
        .campaign-stats span {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Best seller card - fixed text truncation */
        .bestseller-card {
            background: linear-gradient(135deg, #151622, #101116);
            margin: 0 16px 28px;
            border-radius: 28px;
            border: 1px solid rgba(139, 92, 246, 0.2);
            overflow: hidden;
        }
        .bestseller-header {
            padding: 18px 20px 0;
            font-size: 12px;
            font-weight: 800;
            color: #c084fc;
            display: flex;
            align-items: center;
            gap: 8px;
            letter-spacing: 0.8px;
        }
        .bestseller-content {
            display: flex;
            gap: 18px;
            padding: 14px 20px 24px;
        }
        .bestseller-image {
            width: 108px;
            height: 108px;
            background: linear-gradient(135deg, #1c1d28, #151622);
            border-radius: 20px;
            overflow: hidden;
            flex-shrink: 0;
            border: 1px solid rgba(255,255,255,0.06);
        }
        .bestseller-image img, .bestseller-image i {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .bestseller-image i {
            font-size: 42px;
            color: #5f6a8a;
        }
        .bestseller-details {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .bestseller-title {
            font-size: 14px;
            font-weight: 600;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 6px;
        }
        .bestseller-price {
            font-size: 18px;
            font-weight: 800;
            color: #4ade80;
            margin-bottom: 6px;
        }
        .bestseller-meta {
            display: flex;
            gap: 12px;
            font-size: 11px;
            color: #9ca3af;
            margin-bottom: 12px;
        }
        .btn-add {
            background: linear-gradient(95deg, #8b5cf6, #06b6d4);
            border: none;
            padding: 11px 0;
            border-radius: 44px;
            color: white;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            width: 100%;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 6px 14px rgba(139,92,246,0.3);
        }
        .btn-add:active { transform: scale(0.97); opacity: 0.9; }
        .btn-add.disabled {
            background: #2a2c38;
            color: #7e7f92;
            box-shadow: none;
            cursor: not-allowed;
        }
        .btn-add.loading {
            opacity: 0.7;
            cursor: wait;
            transform: none;
        }
        .btn-add.loading i {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* fast campaign horizontal scroll with fade indicator */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            padding: 0 20px;
            margin: 8px 0 14px;
        }
        .section-title { font-size: 17px; font-weight: 700; }
        .section-link { 
            font-size: 13px; 
            color: #c084fc; 
            font-weight: 600; 
            text-decoration: none;
            transition: opacity 0.2s;
        }
        .section-link:active { opacity: 0.7; }
        
        .scroll-container {
            position: relative;
            margin: 0 16px 24px;
        }
        .scroll-horizontal {
            overflow-x: auto;
            overflow-y: hidden;
            white-space: nowrap;
            padding: 0 4px 12px 4px;
            scrollbar-width: thin;
            scrollbar-color: #8b5cf6 #2a2c38;
        }
        .scroll-horizontal::-webkit-scrollbar {
            height: 4px;
        }
        .scroll-horizontal::-webkit-scrollbar-track {
            background: #2a2c38;
            border-radius: 10px;
        }
        .scroll-horizontal::-webkit-scrollbar-thumb {
            background: #8b5cf6;
            border-radius: 10px;
        }
        /* fade indicator for scrollable area */
        .scroll-container::after {
            content: '';
            position: absolute;
            right: 0;
            top: 0;
            bottom: 16px;
            width: 40px;
            background: linear-gradient(to right, transparent, #07070a);
            pointer-events: none;
            opacity: 0.8;
        }
        .fast-list {
            display: inline-flex;
            gap: 14px;
        }
        .fast-item {
            width: 142px;
            background: #111217;
            border-radius: 24px;
            padding: 14px 10px;
            border: 1px solid rgba(255, 255, 255, 0.04);
            transition: all 0.2s;
        }
        .fast-item:active { transform: scale(0.98); background: #161722; }
        .fast-image {
            width: 100%;
            aspect-ratio: 1 / 1;
            background: linear-gradient(135deg, #1c1d28, #151622);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            overflow: hidden;
        }
        .fast-image img, .fast-image i {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .fast-image i { font-size: 32px; color: #5a6785; }
        .fast-name {
            font-size: 12px;
            font-weight: 600;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 12px;
        }
        .btn-small {
            background: rgba(255,255,255,0.07);
            border: none;
            padding: 8px 0;
            border-radius: 40px;
            color: white;
            font-weight: 700;
            font-size: 12px;
            width: 100%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .btn-small:active { background: rgba(255,255,255,0.14); transform: scale(0.97); }
        .btn-small.disabled {
            background: #20212c;
            color: #6b6e82;
            cursor: default;
        }
        .btn-small.disabled:active { transform: none; }

        /* profile mini - improved */
        .profile-card {
            display: flex;
            align-items: center;
            gap: 14px;
            background: linear-gradient(135deg, #111217, #0d0e12);
            margin: 16px 16px 0;
            padding: 16px 20px;
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.04);
        }
        .profile-avatar-sm {
            width: 48px;
            height: 48px;
            background: linear-gradient(145deg, #a78bfa, #38bdf8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 700;
        }
        .profile-info { flex: 1; }
        .profile-name { font-size: 15px; font-weight: 700; margin-bottom: 4px; }
        .profile-tier { font-size: 12px; color: #9ca3af; }
        .qr-icon { 
            font-size: 24px; 
            color: #6c758e;
            transition: all 0.2s;
            cursor: pointer;
        }
        .qr-icon:active { transform: scale(0.9); opacity: 0.7; }

        /* bottom nav - improved active indicator */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(17, 18, 23, 0.95);
            backdrop-filter: blur(24px);
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            padding: 8px 20px calc(12px + env(safe-area-inset-bottom));
            z-index: 100;
        }
        .nav-container {
            max-width: 500px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
        }
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            color: #5d6985;
            text-decoration: none;
            font-size: 10px;
            font-weight: 600;
            padding: 8px 16px;
            transition: all 0.2s;
            border-radius: 40px;
            position: relative;
        }
        .nav-item i { font-size: 20px; }
        .nav-item.active { 
            color: #c084fc;
            background: rgba(139, 92, 246, 0.1);
        }
        .nav-item:active { 
            transform: scale(0.95);
            background: rgba(255,255,255,0.05);
        }

        /* Toast notification - fixed position better */
        .toast-message {
            position: fixed;
            bottom: 100px;
            left: 50%;
            transform: translateX(-50%);
            background: #1e1f2a;
            backdrop-filter: blur(16px);
            color: white;
            padding: 12px 24px;
            border-radius: 60px;
            font-size: 13px;
            font-weight: 600;
            z-index: 9999;
            box-shadow: 0 8px 24px rgba(0,0,0,0.4);
            white-space: nowrap;
            border: 1px solid rgba(255,255,255,0.1);
            animation: slideUp 0.3s ease;
        }
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }
        
        .empty-state { text-align: center; padding: 36px; color: #6f7893; font-size: 13px; }
    </style>
</head>
<body>
<div class="fixed-header">
    <div class="header-content">
        <div class="logo">Toopai</div>
        <div class="notification-icon"><i class="fa-regular fa-bell"></i></div>
    </div>
</div>

<div class="main-container">
    <!-- GREETING CARD -->
    <div class="greeting-card">
        <div class="greeting-text">
            <h2>Hi, <?= htmlspecialchars($creator->full_name ?: $creator->username) ?> 👋</h2>
            <div class="status-badge">
                <span class="creator-badge"></span>
                <span>Unverified Creator</span>
            </div>
        </div>
        <div class="greeting-avatar">
            <?php if (!empty($creator->avatar_url)): ?>
                <img src="<?= $creator->avatar_url ?>" alt="avatar">
            <?php else: ?>
                <?= strtoupper(substr($creator->username, 0, 1)) ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- TikTok Connect -->
    <?php if (empty($creator->creator_hid) || empty($creator->access_token)): ?>
    <div class="upgrade-card-mini">
        <div class="upgrade-left">
            <div class="tiktok-icon"><i class="fab fa-tiktok"></i></div>
            <div class="upgrade-text">
                <h4>Connect TikTok Account</h4>
                <p>Get affiliate links & analytics</p>
            </div>
        </div>
        <a href="<?= base_url('creator/authorize_tiktok') ?>" class="btn-upgrade-mini">Connect →</a>
    </div>
    <?php endif; ?>

    <!-- STATS ROW -->
    <div class="stats-row">
        <div class="stat-box">
            <div class="stat-value">Rp <?= number_format($total_gmv, 0, ',', '.') ?></div>
            <div class="stat-label">TOTAL GMV</div>
        </div>
        <div class="stat-box">
            <div class="stat-value"><?= number_format($total_orders) ?></div>
            <div class="stat-label">ORDERS</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">Rp <?= number_format($total_commission, 0, ',', '.') ?></div>
            <div class="stat-label">COMMISSION</div>
        </div>
    </div>

    <!-- ACTIVE CAMPAIGN - PERFECT BANNER (NO CROP) -->
    <?php if (!empty($top_campaign)): ?>
    <div class="campaign-card">
        <div class="campaign-banner-wrapper">
            <?php if (!empty($campaign_banner)): ?>
                <img class="campaign-banner-img" src="<?= $campaign_banner ?>" alt="campaign banner">
            <?php else: ?>
                <div class="campaign-banner-placeholder">
                    <i class="fas fa-image"></i>
                    <p>Campaign Image Coming Soon</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="campaign-info">
            <span class="campaign-tag">🔥 ACTIVE CAMPAIGN</span>
            <div class="campaign-name">
                <?= htmlspecialchars(preg_replace('/\[.*?\]\s*/', '', $top_campaign->campaign_name)) ?>
            </div>
            <div class="campaign-stats">
                <span><i class="fas fa-money-bill-wave"></i> Rp <?= number_format($top_campaign->total_gmv ?? 0, 0, ',', '.') ?></span>
                <span><i class="fas fa-shopping-cart"></i> <?= number_format($top_campaign->total_orders ?? 0) ?> orders</span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- BEST SELLER PRODUCT WITH TRUNCATED TEXT -->
    <?php if (!empty($product_name)): ?>
    <div class="bestseller-card">
        <div class="bestseller-header">
            <i class="fas fa-fire" style="color:#f97316"></i> 
            BEST SELLER PRODUCT
        </div>
        <div class="bestseller-content">
            <div class="bestseller-image">
                <?php if (!empty($product_image)): ?>
                    <img src="<?= $product_image ?>" alt="<?= htmlspecialchars($product_name) ?>">
                <?php else: ?>
                    <i class="fas fa-crown"></i>
                <?php endif; ?>
            </div>
            <div class="bestseller-details">
                <div>
                    <div class="bestseller-title"><?= htmlspecialchars($product_name) ?></div>
                    <div class="bestseller-price">Rp <?= number_format($product_price, 0, ',', '.') ?></div>
                    <div class="bestseller-meta">
                        <span><i class="fas fa-percent" style="color:#a78bfa"></i> <?= $product_commission ?>% Comm.</span>
                        <span><i class="fas fa-chart-line"></i> <?= number_format($product_sales) ?> sold</span>
                    </div>
                </div>
                <?php if ($campaign_link_available): ?>
                    <button class="btn-add" onclick="ambilLink('<?= $product_id ?>', '<?= $campaign_id ?>', this)">
                        <i class="fas fa-link"></i> Get Affiliate Link
                    </button>
                <?php else: ?>
                    <button class="btn-add disabled" disabled>
                        <i class="fas fa-clock"></i> Coming Soon
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- FAST CAMPAIGN SECTION WITH SCROLL INDICATOR -->
    <div class="section-header">
        <h3 class="section-title">⚡ Fast Campaign</h3>
        <a href="<?= base_url('creator/campaigns') ?>" class="section-link">View All →</a>
    </div>
    <div class="scroll-container">
        <div class="scroll-horizontal">
            <div class="fast-list">
                <?php if (!empty($fast_campaigns)): ?>
                    <?php foreach ($fast_campaigns as $campaign): ?>
                    <div class="fast-item">
                        <div class="fast-image">
                            <?php if (!empty($campaign->image_url)): ?>
                                <img src="<?= $campaign->image_url ?>" alt="product">
                            <?php else: ?>
                                <i class="fas fa-box"></i>
                            <?php endif; ?>
                        </div>
                        <div class="fast-name"><?= htmlspecialchars(substr($campaign->product_name ?? 'Product', 0, 18)) ?></div>
                        <?php if ($campaign->link_available): ?>
                            <button class="btn-small" onclick="event.stopPropagation(); ambilLink('<?= $campaign->product_id ?>', '<?= $campaign->campaign_id ?>', this)">
                                <i class="fas fa-plus"></i> Link
                            </button>
                        <?php else: ?>
                            <button class="btn-small disabled" disabled>Soon</button>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state"><p>✨ No campaigns available</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- PROFILE MINI CARD -->
    <div class="profile-card">
        <div class="profile-avatar-sm">
            <?= strtoupper(substr($creator->username, 0, 1)) ?>
        </div>
        <div class="profile-info">
            <div class="profile-name">@<?= htmlspecialchars($creator->username) ?></div>
            <div class="profile-tier"><?= htmlspecialchars($creator->category ?? 'Content Creator') ?></div>
        </div>
        <i class="fas fa-qrcode qr-icon"></i>
    </div>
</div>

<!-- BOTTOM NAVIGATION WITH IMPROVED ACTIVE STATE -->
<div class="bottom-nav">
    <div class="nav-container">
        <a href="<?= base_url('creator/dashboard') ?>" class="nav-item active">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="<?= base_url('creator/campaigns') ?>" class="nav-item">
            <i class="fas fa-bullhorn"></i>
            <span>Campaign</span>
        </a>
        <a href="<?= base_url('creator/leaderboard') ?>" class="nav-item">
            <i class="fas fa-trophy"></i>
            <span>Leaderboard</span>
        </a>
        <a href="<?= base_url('creator/profile') ?>" class="nav-item">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
    </div>
</div>

<script>
    async function ambilLink(productId, campaignId, buttonElement) {
        if (!productId || !campaignId) {
            showToast('Product not valid', 'error');
            return;
        }
        
        // Loading state
        const originalText = buttonElement.innerHTML;
        buttonElement.innerHTML = '<i class="fas fa-spinner"></i> Loading...';
        buttonElement.classList.add('loading');
        buttonElement.disabled = true;
        
        try {
            const response = await fetch('<?= base_url("creator/get_affiliate_link") ?>', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({ product_id: productId, campaign_id: campaignId })
            });
            const data = await response.json();
            
            if (data.success && data.available) {
                await navigator.clipboard.writeText(data.link);
                showToast(`✅ Link copied! ${data.commission_rate || ''}% commission`, 'success');
            } else {
                showToast(data.message || 'Link not available yet', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Failed to generate link', 'error');
        } finally {
            // Restore button
            buttonElement.innerHTML = originalText;
            buttonElement.classList.remove('loading');
            buttonElement.disabled = false;
        }
    }

    function showToast(msg, type = 'success') {
        let existing = document.querySelector('.toast-message');
        if (existing) existing.remove();
        
        let toast = document.createElement('div');
        toast.className = 'toast-message';
        toast.textContent = msg;
        
        if (type === 'error') toast.style.background = '#dc2626';
        else if (type === 'info') toast.style.background = '#2563eb';
        else toast.style.background = '#10b981';
        
        toast.style.color = 'white';
        document.body.appendChild(toast);
        
        setTimeout(() => {
            if (toast) toast.remove();
        }, 3000);
    }
</script>
</body>
</html>