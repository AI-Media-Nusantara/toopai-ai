<!-- file: application/views/creator/campaigns.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title><?= $title ?? 'Campaign' ?> - Toopai</title>
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
            background: #0a0a0e; 
            color: #ffffff;
            font-size: 15px;
            line-height: 1.4;
        }
        
        .fixed-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(10, 10, 14, 0.96);
            backdrop-filter: blur(10px);
            z-index: 100;
            padding: 12px 20px;
            border-bottom: 0.5px solid rgba(255, 255, 255, 0.08);
        }
        
        .header-content {
            max-width: 500px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .back-btn {
            background: none;
            border: none;
            color: #fff;
            font-size: 20px;
            cursor: pointer;
            padding: 4px;
        }
        
        .page-title {
            font-size: 17px;
            font-weight: 600;
        }
        
        .main-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 60px 0 70px;
        }
        
        /* Radar FYP */
        .radar-fyp {
            background: linear-gradient(135deg, #1a1a2e, #0f0f1a);
            margin: 16px 20px;
            border-radius: 24px;
            padding: 16px;
            border: 1px solid rgba(6, 182, 212, 0.3);
        }
        
        .radar-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
            font-size: 14px;
            font-weight: 700;
            color: #06b6d4;
        }
        
        .audio-viral {
            background: rgba(6, 182, 212, 0.1);
            border-radius: 16px;
            padding: 14px;
            margin-bottom: 12px;
            border: 1px solid rgba(6, 182, 212, 0.2);
        }
        
        .audio-label {
            font-size: 10px;
            color: #fbbf24;
            margin-bottom: 6px;
            font-weight: 600;
        }
        
        .audio-title {
            font-size: 16px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
        }
        
        .audio-title i {
            color: #06b6d4;
        }
        
        .audio-stats {
            font-size: 11px;
            color: #9ca3af;
        }
        
        .more-audio {
            display: flex;
            gap: 8px;
        }
        
        .audio-item {
            flex: 1;
            background: rgba(255,255,255,0.03);
            padding: 8px 10px;
            border-radius: 12px;
            font-size: 11px;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .audio-item:hover {
            background: rgba(6, 182, 212, 0.15);
        }
        
        /* AI Banner */
        .ai-banner {
            background: linear-gradient(135deg, #1a1a2e, #0f0f1a);
            margin: 16px 20px;
            border-radius: 24px;
            padding: 18px;
            border: 1px solid rgba(139, 92, 246, 0.4);
        }
        
        .ai-badge {
            display: inline-block;
            background: rgba(139, 92, 246, 0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 10px;
            color: #a78bfa;
            margin-bottom: 12px;
        }
        
        .ai-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .ai-description {
            font-size: 13px;
            color: #9ca3af;
            line-height: 1.5;
            margin-bottom: 16px;
        }
        
        .ai-trending {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 0;
        }
        
        .trend-tag {
            background: rgba(139, 92, 246, 0.15);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            color: #a78bfa;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        /* AI Music Card */
        .music-card {
            background: rgba(6, 182, 212, 0.08);
            border-radius: 18px;
            padding: 14px;
            margin-top: 12px;
            border: 1px solid rgba(6, 182, 212, 0.2);
        }
        
        .music-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }
        
        .music-header i {
            color: #06b6d4;
            font-size: 16px;
        }
        
        .music-title {
            font-size: 13px;
            font-weight: 600;
        }
        
        .music-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .music-item {
            background: rgba(6, 182, 212, 0.12);
            padding: 8px 14px;
            border-radius: 30px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        /* AI Hook Generator */
        .hook-generator {
            background: linear-gradient(135deg, #1a1a2e, #0f0f1a);
            margin: 16px 20px;
            border-radius: 24px;
            padding: 16px;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        
        .hook-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            font-size: 14px;
            font-weight: 600;
            color: #10b981;
            flex-wrap: wrap;
        }
        
        .ai-badge-sm {
            background: rgba(16, 185, 129, 0.15);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 9px;
            color: #10b981;
        }
        
        .hook-desc {
            font-size: 11px;
            color: #9ca3af;
            margin-bottom: 16px;
        }
        
        .hook-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 16px;
            max-height: 350px;
            overflow-y: auto;
        }
        
        .hook-card {
            background: rgba(255,255,255,0.03);
            border-radius: 14px;
            padding: 12px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid rgba(255,255,255,0.05);
        }
        
        .hook-card:hover {
            background: rgba(16, 185, 129, 0.1);
            border-color: #10b981;
        }
        
        .hook-card i:first-child {
            color: #10b981;
            font-size: 14px;
            margin-top: 2px;
        }
        
        .hook-text {
            flex: 1;
            font-size: 12px;
            line-height: 1.4;
        }
        
        .hook-copy {
            font-size: 11px;
            color: #10b981;
            opacity: 0.7;
        }
        
        .btn-generate-hook {
            width: 100%;
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.3);
            padding: 10px;
            border-radius: 30px;
            color: #10b981;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
        }
        
        /* Campaign Detail */
        .campaign-detail-card {
            background: #111827;
            margin: 16px 20px;
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.06);
        }
        
        .campaign-banner {
            height: 200px;
            background: linear-gradient(135deg, #1a1a2e, #0f0f1a);
            overflow: hidden;
        }
        
        .campaign-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .campaign-banner-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #a78bfa, #06b6d4);
        }
        
        .campaign-banner-placeholder i {
            font-size: 48px;
            color: rgba(255,255,255,0.3);
        }
        
        .campaign-content {
            padding: 20px;
        }
        
        .campaign-badge {
            background: rgba(139, 92, 246, 0.15);
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            color: #a78bfa;
            margin-bottom: 12px;
        }
        
        .campaign-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .campaign-subtitle {
            font-size: 13px;
            color: #9ca3af;
            margin-bottom: 20px;
        }
        
        .stats-row {
            display: flex;
            gap: 16px;
            padding: 16px 0;
            border-top: 1px solid rgba(255,255,255,0.06);
            border-bottom: 1px solid rgba(255,255,255,0.06);
            margin-bottom: 20px;
        }
        
        .stat-item {
            flex: 1;
            text-align: center;
        }
        
        .stat-value {
            font-size: 18px;
            font-weight: 700;
            color: #34d399;
        }
        
        .stat-label {
            font-size: 10px;
            color: #9ca3af;
            margin-top: 4px;
        }
        
        .bonus-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 20px;
        }
        
        .bonus-item {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: #fbbf24;
        }
        
        .howto-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .howto-step {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .step-number {
            width: 28px;
            height: 28px;
            background: #a78bfa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
        }
        
        .btn-primary {
            width: 100%;
            background: linear-gradient(135deg, #a78bfa, #06b6d4);
            border: none;
            padding: 14px;
            border-radius: 40px;
            color: white;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary.disabled {
            background: #374151;
            cursor: not-allowed;
        }
        
        /* Bottom Navigation */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(17, 24, 39, 0.96);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding: 8px 20px 12px;
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
            gap: 3px;
            color: #6b7280;
            text-decoration: none;
            font-size: 10px;
            padding: 6px 0;
        }
        
        .nav-item i {
            font-size: 22px;
        }
        
        .nav-item.active {
            color: #a78bfa;
        }
        
        /* Loading States */
        .loading-container {
            text-align: center;
            padding: 30px 20px;
            background: rgba(255,255,255,0.03);
            border-radius: 20px;
        }
        
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(139,92,246,0.3);
            border-top-color: #a78bfa;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 16px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .loading-progress {
            width: 100%;
            height: 4px;
            background: rgba(255,255,255,0.1);
            border-radius: 4px;
            margin: 16px 0;
            overflow: hidden;
        }
        
        .loading-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #a78bfa, #06b6d4);
            border-radius: 4px;
            transition: width 0.3s ease;
        }
        
        .loading-tip {
            font-size: 11px;
            color: #9ca3af;
            margin-top: 12px;
        }
        
        .error-container {
            text-align: center;
            padding: 30px;
            color: #9ca3af;
        }
        
        .quota-exceeded {
            text-align: center;
            padding: 24px;
            background: linear-gradient(135deg, #1a1a2e, #0f0f1a);
            border-radius: 20px;
            border: 1px solid rgba(239,68,68,0.3);
        }
        
        .quota-icon {
            font-size: 48px;
            margin-bottom: 12px;
        }
        
        .quota-title {
            font-size: 16px;
            font-weight: 700;
            color: #ef4444;
            margin-bottom: 8px;
        }
        
        .quota-subtitle {
            font-size: 12px;
            color: #9ca3af;
            margin-bottom: 16px;
        }
        
        .quota-upgrade-btn {
            background: linear-gradient(135deg, #a78bfa, #06b6d4);
            border: none;
            padding: 10px 20px;
            border-radius: 40px;
            color: white;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            width: 100%;
            margin-bottom: 12px;
        }
        
        .toast-message {
            position: fixed;
            bottom: 80px;
            left: 50%;
            transform: translateX(-50%);
            background: #34d399;
            color: #0a0a0e;
            padding: 10px 20px;
            border-radius: 30px;
            font-size: 13px;
            z-index: 9999;
            white-space: nowrap;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate {
            animation: fadeIn 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="fixed-header">
        <div class="header-content">
            <button class="back-btn" onclick="history.back()"><i class="fas fa-arrow-left"></i></button>
            <span class="page-title">Campaign</span>
            <div style="width: 32px;"></div>
        </div>
    </div>
    
    <div class="main-container">
        <?php if (!empty($campaign) && !empty($product_id)): ?>
        
        <!-- Radar FYP -->
        <div class="radar-fyp animate">
            <div class="radar-header">
                <i class="fas fa-chart-line"></i>
                <span>🎯 Radar FYP Hari Ini</span>
            </div>
            <div class="audio-viral">
                <div class="audio-label">🔥 AUDIO VIRAL #1</div>
                <div class="audio-title" id="audioViralTitle">
                    <i class="fas fa-music"></i> 
                    <?= is_array($trending_music) && isset($trending_music[0]) ? htmlspecialchars($trending_music[0]) : 'Loading...' ?>
                </div>
                <div class="audio-stats" id="audioViralStats">
                    <span><i class="fas fa-chart-simple"></i> Trending hari ini</span>
                </div>
            </div>
            <div class="more-audio" id="moreAudioList">
                <?php if (is_array($trending_music) && count($trending_music) > 1): ?>
                    <?php for ($i = 1; $i < min(3, count($trending_music)); $i++): ?>
                    <div class="audio-item" data-song="<?= htmlspecialchars($trending_music[$i]) ?>">
                        <i class="fas fa-headphones"></i> <?= htmlspecialchars($trending_music[$i]) ?>
                    </div>
                    <?php endfor; ?>
                <?php else: ?>
                    <div class="audio-item">Loading...</div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- AI Insights -->
        <div class="ai-banner animate">
            <div class="ai-badge">🤖 AI INSIGHT · Toopai.AI Powered</div>
            <div class="ai-title">📊 Kenapa Campaign Ini Cocok untuk Kamu Hari Ini?</div>
            <div class="ai-description">
                <?= htmlspecialchars($ai_insight) ?>
            </div>
            <div class="ai-trending" id="trendingTagsList">
                <?php if (is_array($trending_tags)): ?>
                    <?php foreach ($trending_tags as $tag): ?>
                    <span class="trend-tag"><?= htmlspecialchars($tag) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- AI Music Card -->
            <div class="music-card">
                <div class="music-header">
                    <i class="fas fa-music"></i>
                    <span class="music-title">🎧 AI Rekomendasi Musik Viral</span>
                </div>
                <div class="music-list" id="musicList">
                    <?php if (is_array($trending_music)): ?>
                        <?php foreach ($trending_music as $music): ?>
                        <div class="music-item" data-song="<?= htmlspecialchars($music) ?>">
                            <i class="fas fa-play"></i> <?= htmlspecialchars($music) ?>
                            <span style="font-size: 9px; margin-left: 4px; opacity: 0.6;">▶ Preview</span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- AI Hook Generator -->
        <div class="hook-generator animate">
            <div class="hook-header">
                <i class="fas fa-magic"></i>
                <span>💡 AI Hook Generator</span>
                <span class="ai-badge-sm">Powered by Toopai AI</span>
            </div>
            <p class="hook-desc">Kalimat pancingan detik pertama yang bikin viewer ga bisa scroll!</p>
            <div class="hook-list" id="aiHookList">
                <?php if (is_array($viral_hooks)): ?>
                    <?php foreach ($viral_hooks as $hook): ?>
                    <div class="hook-card" data-hook="<?= htmlspecialchars($hook) ?>">
                        <i class="fas fa-quote-left"></i>
                        <div class="hook-text">"<?= htmlspecialchars($hook) ?>"</div>
                        <div class="hook-copy"><i class="fas fa-copy"></i> Copy</div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <button class="btn-generate-hook" id="generateHookBtn">
                <i class="fas fa-sync-alt"></i> Generate New Hook
            </button>
        </div>
        
        <!-- Campaign Detail -->
        <div class="campaign-detail-card animate">
            <div class="campaign-banner">
                <?php if (!empty($campaign_banner)): ?>
                    <img src="<?= $campaign_banner ?>" alt="<?= htmlspecialchars($campaign->campaign_name) ?>">
                <?php else: ?>
                    <div class="campaign-banner-placeholder">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="campaign-content">
                <div class="campaign-badge">🔥 Primary Campaign</div>
                <div class="campaign-title"><?= htmlspecialchars(preg_replace('/\[.*?\]\s*/', '', $campaign->campaign_name)) ?></div>
                <div class="campaign-subtitle">Join waitlist • Limited slot</div>
                
                <div class="stats-row">
                    <div class="stat-item">
                        <div class="stat-value">Rp <?= number_format($campaign_gmv, 0, ',', '.') ?></div>
                        <div class="stat-label">Total GMV</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= number_format($campaign_orders) ?></div>
                        <div class="stat-label">Orders</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $campaign_commission ?>%</div>
                        <div class="stat-label">Komisi</div>
                    </div>
                </div>
                
                <div class="bonus-list">
                    <div class="bonus-item"><span>🎁 Top 1</span><span>Rp 500.000</span></div>
                    <div class="bonus-item"><span>🎁 Top 2-3</span><span>Rp 270.000</span></div>
                    <div class="bonus-item"><span>🎁 Top 4-10</span><span>Rp 150.000</span></div>
                </div>
                
                <div class="howto-list">
                    <div class="howto-step"><div class="step-number">1</div><div class="step-text">Ambil Link</div></div>
                    <div class="howto-step"><div class="step-number">2</div><div class="step-text">Pasang di Showcase/video</div></div>
                    <div class="howto-step"><div class="step-number">3</div><div class="step-text">Buat konten & Submit Proof</div></div>
                </div>
                
                <?php if ($campaign_link_available): ?>
                    <button class="btn-primary" id="ambilLinkBtn" data-product-id="<?= $product_id ?>" data-campaign-id="<?= $campaign_id ?>">
                        <i class="fas fa-link"></i> Ambil Link & Join Campaign
                    </button>
                <?php else: ?>
                    <button class="btn-primary disabled" disabled>
                        <i class="fas fa-clock"></i> Coming Soon
                    </button>
                <?php endif; ?>
            </div>
        </div>
        
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-bullhorn" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
            <p>No campaign available</p>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Bottom Navigation -->
    <div class="bottom-nav">
        <div class="nav-container">
            <a href="<?= base_url('creator/dashboard') ?>" class="nav-item">
                <i class="fas fa-home"></i><span>Home</span>
            </a>
            <a href="<?= base_url('creator/campaigns') ?>" class="nav-item active">
                <i class="fas fa-bullhorn"></i><span>Campaign</span>
            </a>
            <a href="<?= base_url('creator/leaderboard') ?>" class="nav-item">
                <i class="fas fa-trophy"></i><span>Leaderboard</span>
            </a>
            <a href="<?= base_url('creator/profile') ?>" class="nav-item">
                <i class="fas fa-user"></i><span>Profile</span>
            </a>
        </div>
    </div>
    
    <script>
    // ========== GLOBAL VARIABLES ==========
    const baseUrl = '<?= base_url() ?>';
    const productName = '<?= addslashes($product_name ?? "") ?>';
   const creatorCategory = <?= json_encode($creator->category ?? "Lifestyle") ?>;
const productIdForLink = <?= json_encode($product_id ?? "") ?>;
const campaignIdForLink = <?= json_encode($campaign_id ?? "") ?>;
    
    // ========== AMBIL LINK ==========
    function ambilLink() {
        const productId = productIdForLink;
        const campaignId = campaignIdForLink;
        
        if (!productId || !campaignId) {
            showToast('Produk tidak ditemukan', 'error');
            return;
        }
        showToast('Mengambil link...', 'info');
        
        fetch(baseUrl + 'creator/get_affiliate_link', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ product_id: productId, campaign_id: campaignId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.available) {
                navigator.clipboard.writeText(data.link);
                showToast('✅ Link berhasil disalin! Komisi ' + data.commission_rate + '%', 'success');
            } else {
                showToast(data.message || 'Link belum tersedia', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Gagal mengambil link', 'error');
        });
    }
    
    // ========== PLAY MUSIC ==========
    // ========== ALTERNATIVE: TIKTOK MUSIC PREVIEW (LINK ONLY) ==========
function playMusic(songTitle) {
    if (!songTitle) return;
    
    const searchQuery = encodeURIComponent(songTitle);
    const tiktokSearchUrl = `https://www.tiktok.com/search?q=${searchQuery}`;
    
    const modal = document.createElement('div');
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.95);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
    `;
    
    modal.innerHTML = `
        <div style="background: linear-gradient(135deg, #1a1a2e, #0f0f1a); border-radius: 32px; width: 85%; max-width: 350px; padding: 24px; text-align: center; border: 1px solid rgba(139,92,246,0.3);">
            <div style="font-size: 48px; margin-bottom: 16px;">🎵</div>
            <h3 style="font-size: 18px; margin-bottom: 8px;">${escapeHtml(songTitle)}</h3>
            <p style="font-size: 12px; color: #fbbf24; margin-bottom: 20px;">Lagi viral di TikTok!</p>
            
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <button class="open-tiktok-btn" style="background: linear-gradient(135deg, #010101, #1a1a2e); border: 1px solid #06b6d4; padding: 12px; border-radius: 40px; color: white; cursor: pointer; font-weight: 600;">
                    <i class="fab fa-tiktok"></i> Buka di TikTok
                </button>
                <button class="copy-song-btn" style="background: #1e293b; border: none; padding: 12px; border-radius: 40px; color: #a78bfa; cursor: pointer; font-weight: 600;">
                    <i class="fas fa-copy"></i> Copy Judul Lagu
                </button>
                <button class="close-modal-btn" style="background: none; border: none; color: #6b7280; cursor: pointer; font-size: 12px;">Tutup</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    modal.querySelector('.open-tiktok-btn').onclick = () => {
        window.open(tiktokSearchUrl, '_blank');
        modal.remove();
    };
    modal.querySelector('.copy-song-btn').onclick = () => {
        navigator.clipboard.writeText(songTitle);
        showToast('📋 Judul lagu disalin!', 'success');
        modal.remove();
    };
    modal.querySelector('.close-modal-btn').onclick = () => modal.remove();
    modal.onclick = (e) => { if (e.target === modal) modal.remove(); };
}
    
    // ========== GENERATE HOOK ==========
    async function generateNewHook() {
    const btn = document.getElementById('generateHookBtn');
    const hookList = document.getElementById('aiHookList');
    
    if (!btn || !hookList) return;
    
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> AI sedang memikirkan hook terbaik...';
    btn.disabled = true;
    
    hookList.innerHTML = `
        <div class="loading-container">
            <div class="loading-spinner"></div>
            <div class="loading-text">🧠 AI sedang menganalisis tren terbaru...</div>
            <div class="loading-progress">
                <div class="loading-progress-bar" style="width: 0%"></div>
            </div>
            <div class="loading-tip">💡 Tip: Hook yang bagus bikin viewer ga bisa scroll!</div>
        </div>
    `;
    
    try {
        const response = await fetch(baseUrl + 'creator/generate_hook', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                product_name: productName,
                category: creatorCategory,
                benefits: 'produk berkualitas, hasil maksimal'
            })
        });
        const data = await response.json();
        
        if (data.show_upgrade_popup) {
            // Tampilkan popup upgrade
            showUpgradePopup(data);
            hookList.innerHTML = `
                <div class="quota-exceeded">
                    <div class="quota-icon">🎉</div>
                    <div class="quota-title">Verifikasi Akun Yuk!</div>
                    <div class="quota-subtitle">Kamu sudah 2x generate. 1x lagi gratis!</div>
                    <button class="quota-upgrade-btn" onclick="window.location.href='${data.upgrade_url}'">🚀 Upgrade VIP</button>
                </div>
            `;
            showToast(data.message, 'info');
        } else if (data.success && data.hooks && data.hooks.length > 0) {
            hookList.innerHTML = data.hooks.map((hook, index) => `
                <div class="hook-card" style="animation-delay: ${index * 0.1}s">
                    <i class="fas fa-quote-left"></i>
                    <div class="hook-text">"${escapeHtml(hook)}"</div>
                    <div class="hook-copy"><i class="fas fa-copy"></i> Copy</div>
                </div>
            `).join('');
            
            // Attach copy event
            document.querySelectorAll('.hook-card').forEach(card => {
                card.onclick = () => {
                    const hookText = card.querySelector('.hook-text').innerText.replace(/^"|"$/g, '');
                    copyHook(hookText);
                };
            });
            
            const remainingMsg = data.remaining > 0 ? `Sisa ${data.remaining} hook gratis hari ini!` : 'Ini hook terakhirmu hari ini!';
            showToast(`✨ ${data.hooks.length} hook berhasil digenerate! ${remainingMsg}`, 'success');
            
            // Jika ini hook ke-2, beri semangat
            if (data.total_used === 2) {
                setTimeout(() => {
                    showToast('🔥 Mantap! Sekali lagi besok baru bisa. Upgrade VIP sekarang biar unlimited!', 'info');
                }, 3000);
            }
        } else if (data.error === 'QUOTA_EXCEEDED') {
            hookList.innerHTML = `
                <div class="quota-exceeded">
                    <div class="quota-icon">⛔</div>
                    <div class="quota-title">${data.message}</div>
                    <div class="quota-subtitle">${data.sub_message}</div>
                    <button class="quota-upgrade-btn" onclick="window.location.href='${data.upgrade_url}'">🚀 Upgrade VIP</button>
                </div>
            `;
            showToast(data.message, 'error');
        }
    } catch (error) {
        console.error('Error generating hook:', error);
        hookList.innerHTML = `
            <div class="error-container">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Yah, AI lagi sibuk nih! Coba lagi ya 🥺</p>
                <button class="btn-generate-hook" onclick="generateNewHook()" style="margin-top: 12px;">Coba Lagi</button>
            </div>
        `;
        showToast('AI sedang sibuk, coba lagi', 'error');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}
    
    // ========== COPY HOOK ==========
    function copyHook(text) {
        if (!text) return;
        navigator.clipboard.writeText(text).then(() => {
            showToast('📋 Hook copied! Langsung pakai buat kontenmu! 🔥', 'success');
        }).catch(() => {
            showToast('Gagal copy, coba manual ya', 'error');
        });
    }
    
    // ========== COPY TO CLIPBOARD ==========
    function copyToClipboard(text) {
        if (!text) return;
        navigator.clipboard.writeText(text).then(() => {
            showToast('📋 Copied!', 'success');
        }).catch(() => {
            showToast('Gagal copy', 'error');
        });
    }
    
    // ========== SHOW TOAST ==========
    function showToast(msg, type = 'success') {
        let toast = document.querySelector('.toast-message');
        if (toast) toast.remove();
        
        toast = document.createElement('div');
        toast.className = 'toast-message';
        toast.textContent = msg;
        if (type === 'error') toast.style.background = '#ef4444';
        if (type === 'info') toast.style.background = '#3b82f6';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
    
    // ========== ESCAPE HTML ==========
    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }
    
    // ========== EVENT LISTENERS ==========
    document.addEventListener('DOMContentLoaded', function() {
        // Ambil Link button
        const ambilLinkBtn = document.getElementById('ambilLinkBtn');
        if (ambilLinkBtn) {
            ambilLinkBtn.onclick = ambilLink;
        }
        
        // Generate Hook button
        const generateHookBtn = document.getElementById('generateHookBtn');
        if (generateHookBtn) {
            generateHookBtn.onclick = generateNewHook;
        }
        
        // Music items - play on click
        document.querySelectorAll('.music-item').forEach(item => {
            item.onclick = () => {
                const song = item.getAttribute('data-song');
                if (song) playMusic(song);
            };
        });
        
        // Audio items - play on click
        document.querySelectorAll('.audio-item').forEach(item => {
            item.onclick = () => {
                const song = item.getAttribute('data-song');
                if (song) playMusic(song);
            };
        });
        
        // Hook cards - copy on click
        document.querySelectorAll('.hook-card').forEach(card => {
            card.onclick = () => {
                const hookText = card.querySelector('.hook-text')?.innerText.replace(/^"|"$/g, '');
                if (hookText) copyHook(hookText);
            };
        });
        
        // Trending tags - copy on click
        document.querySelectorAll('.trend-tag').forEach(tag => {
            tag.onclick = () => {
                const tagText = tag.innerText.trim();
                if (tagText) copyToClipboard(tagText);
            };
        });
    });
    
    function showUpgradePopup(data) {
    const modal = document.createElement('div');
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.95);
        backdrop-filter: blur(8px);
        z-index: 20000;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.3s ease;
    `;
    
    let benefitsHtml = '';
    if (data.benefits) {
        benefitsHtml = data.benefits.map(b => `<div class="benefit-item"><i class="fas fa-check-circle"></i> ${b}</div>`).join('');
    }
    
    modal.innerHTML = `
        <div style="background: linear-gradient(135deg, #1a1a2e, #0f0f1a); border-radius: 32px; width: 90%; max-width: 400px; overflow: hidden; border: 1px solid rgba(139,92,246,0.5); position: relative;">
            <div style="position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(139,92,246,0.1) 0%, transparent 70%); pointer-events: none;"></div>
            
            <div style="padding: 24px; text-align: center;">
                <div style="font-size: 64px; margin-bottom: 16px;">🚀</div>
                <h2 style="font-size: 24px; font-weight: 700; margin-bottom: 8px; background: linear-gradient(135deg, #fbbf24, #ef4444); -webkit-background-clip: text; background-clip: text; color: transparent;">
                    ${data.message || 'YUK VERIFIKASI AKUN!'}
                </h2>
                <p style="color: #fbbf24; margin-bottom: 16px;">${data.sub_message || 'Kamu sudah 2x generate hook hari ini!'}</p>
                
                <div style="background: rgba(255,255,255,0.03); border-radius: 20px; padding: 16px; margin: 16px 0; text-align: left;">
                    <p style="color: #a78bfa; font-weight: 600; margin-bottom: 12px;">${data.upgrade_message || '✨ Upgrade ke VIP sekarang!'}</p>
                    ${benefitsHtml || `
                        <div class="benefit-item"><i class="fas fa-check-circle"></i> ✨ Unlimited AI Hook Generator</div>
                        <div class="benefit-item"><i class="fas fa-check-circle"></i> 🔥 Akses trending music realtime</div>
                        <div class="benefit-item"><i class="fas fa-check-circle"></i> 📊 Insight produk eksklusif</div>
                        <div class="benefit-item"><i class="fas fa-check-circle"></i> 💰 GMV 3-5x lebih besar!</div>
                    `}
                </div>
                
                <div style="background: linear-gradient(135deg, #f59e0b, #ef4444); border-radius: 16px; padding: 12px; margin-bottom: 20px;">
                    <p style="color: white; font-size: 12px;">${data.gmv_promise || 'Rata-rata creator VIP mendapatkan GMV 3-5x lipat!'}</p>
                </div>
                
                <button id="upgradeNowBtn" style="width: 100%; background: linear-gradient(135deg, #a78bfa, #06b6d4); border: none; padding: 14px; border-radius: 40px; color: white; font-weight: 700; font-size: 16px; cursor: pointer; margin-bottom: 12px;">
                    🚀 Upgrade Sekarang
                </button>
                <button id="upgradeLaterBtn" style="background: none; border: none; color: #6b7280; cursor: pointer; font-size: 13px;">
                    Nanti Saja, Saya Mau Coba Lagi
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    document.getElementById('upgradeNowBtn').onclick = () => {
        // window.location.href = data.upgrade_url || '<?= base_url("creator/#") ?>';
    };
    
    document.getElementById('upgradeLaterBtn').onclick = () => {
        modal.remove();
        showToast('Yuk upgrade kapan-kapan! Lebih untung jadi VIP! 💎', 'info');
    };
    
    modal.onclick = (e) => { if (e.target === modal) modal.remove(); };
}
    </script>
</body>
</html>