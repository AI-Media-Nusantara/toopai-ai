<div class="page-header">
    <h1 class="page-title"><i class="fas fa-key"></i> TikTok API Authorization</h1>
    <p class="page-subtitle">Manage API credentials for Creator, Seller & Affiliate Partner</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
        <div class="stat-info">
            <div class="stat-label">App Key</div>
            <div class="stat-value"><?= $app_key ?></div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-building"></i></div>
        <div class="stat-info">
            <div class="stat-label">Service ID</div>
            <div class="stat-value"><?= $service_id ?></div>
        </div>
    </div>

    <?php if (isset($api_type)): ?>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-globe"></i></div>
        <div class="stat-info">
            <div class="stat-label">API Type</div>
            <div class="stat-value"><?= $api_type ?></div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="section-card">
    <h2 class="section-title"><i class="fas fa-star"></i> Creator Authorization (TikTok Alliance)</h2>
    <p class="section-desc">For managing creator affiliate links and performance data.</p>
    <?php if ($creator_token): ?>
        <div class="status-success">
            <div class="status-main"><i class="fas fa-check-circle"></i> Authorized</div>
            <div class="status-detail">Token expires: <?= date('Y-m-d H:i:s', $creator_token->access_token_expire) ?></div>
        </div>
    <?php else: ?>
        <div class="status-warning">
            <div class="status-main"><i class="fas fa-exclamation-triangle"></i> Not Authorized</div>
            <div class="status-detail">Authorize to access creator data from TikTok Alliance.</div>
        </div>
        <div class="action-area">
            <a href="<?= base_url('tts/authorize_creator') ?>" class="btn-primary">
                <i class="fab fa-tiktok"></i> Authorize as Creator
            </a>
        </div>
    <?php endif; ?>
</div>

<div class="section-card">
    <h2 class="section-title"><i class="fas fa-store"></i> Seller Authorization (TikTok Shop)</h2>
    <p class="section-desc">For sample requests, product management, and order data.</p>
    <?php if ($seller_token): ?>
        <div class="status-success">
            <div class="status-main"><i class="fas fa-check-circle"></i> Authorized</div>
            <div class="status-detail">Token expires: <?= date('Y-m-d H:i:s', $seller_token->access_token_expire) ?></div>
        </div>
    <?php else: ?>
        <div class="status-warning">
            <div class="status-main"><i class="fas fa-exclamation-triangle"></i> Not Authorized</div>
            <div class="status-detail">Authorize to access seller features.</div>
        </div>
        <div class="action-area">
            <a href="<?= base_url('tts/authorize_seller') ?>" class="btn-primary">
                <i class="fab fa-tiktok"></i> Authorize as Seller
            </a>
        </div>
    <?php endif; ?>
</div>

<div class="section-card">
    <h2 class="section-title"><i class="fas fa-handshake"></i> Affiliate Partner Authorization</h2>
    <p class="section-desc">For campaign management and affiliate orders.</p>
    <?php if ($affiliate_token): ?>
        <div class="status-success">
            <div class="status-main"><i class="fas fa-check-circle"></i> Authorized</div>
            <div class="status-detail">Token expires: <?= date('Y-m-d H:i:s', $affiliate_token->access_token_expire) ?></div>
        </div>
    <?php else: ?>
        <div class="status-warning">
            <div class="status-main"><i class="fas fa-exclamation-triangle"></i> Not Authorized</div>
            <div class="status-detail">Authorize to access affiliate features.</div>
        </div>
        <div class="action-area">
            <a href="<?= base_url('tts/authorize_affiliate') ?>" class="btn-primary">
                <i class="fab fa-tiktok"></i> Authorize as Affiliate Partner
            </a>
        </div>
    <?php endif; ?>
</div>

<div class="section-card danger-border">
    <h2 class="section-title danger-text"><i class="fas fa-trash-alt"></i> Danger Zone</h2>
    <p class="section-desc">Clear all authorization tokens and re-authorize.</p>
    <div class="action-area">
        <a href="<?= base_url('tts/refresh') ?>" class="btn-danger" onclick="return confirm('Are you sure?')">
            <i class="fas fa-sync-alt"></i> Clear All & Re-authorize
        </a>
    </div>
</div>

<style>
/* --- General Colors (Anti-Black Text) --- */
:root {
    --bg-main: #0a0e17;
    --card-bg: #111827;
    --border-color: #2a3346;
    --text-bright: #ffffff;
    --text-muted: #9aaebe;
    --accent-green: #4ade80;
    --accent-yellow: #fbbf24;
    --accent-red: #ef4444;
}

/* --- Layout --- */
.page-title { color: var(--text-bright); font-size: 24px; margin-bottom: 8px; }
.page-subtitle { color: var(--text-muted); font-size: 14px; margin-bottom: 30px; }

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 16px;
    margin-bottom: 30px;
}

.stat-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 20px;
    display: flex;
    align-items: center;
}

.stat-icon { font-size: 22px; color: var(--accent-green); margin-right: 16px; width: 30px; }
.stat-label { color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
.stat-value { color: var(--text-bright); font-size: 15px; font-weight: 600; word-break: break-all; }

/* --- Section Card --- */
.section-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    padding: 24px;
    margin-bottom: 20px;
}

.section-title { font-size: 18px; color: #e2f0e8; margin-bottom: 6px; display: flex; align-items: center; gap: 10px; }
.section-desc { font-size: 13px; color: var(--text-muted); margin-bottom: 20px; }

/* --- Status Boxes --- */
.status-success {
    background: rgba(74, 222, 128, 0.1);
    border: 1px solid var(--accent-green);
    border-radius: 12px;
    padding: 16px;
}
.status-warning {
    background: rgba(251, 191, 36, 0.1);
    border: 1px solid var(--accent-yellow);
    border-radius: 12px;
    padding: 16px;
}
.status-main { font-weight: 600; display: flex; align-items: center; gap: 8px; margin-bottom: 4px; }
.status-success .status-main { color: var(--accent-green); }
.status-warning .status-main { color: var(--accent-yellow); }
.status-detail { font-size: 12px; color: var(--text-muted); font-weight: 400; }

/* --- Buttons --- */
.action-area { margin-top: 16px; }
.btn-primary {
    background: var(--accent-green);
    color: #064e3b;
    padding: 12px 24px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 700;
    display: inline-block;
    transition: 0.2s;
}
.btn-primary:hover { transform: translateY(-2px); filter: brightness(1.1); }

.btn-danger {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid var(--accent-red);
    color: var(--accent-red);
    padding: 10px 20px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    display: inline-block;
    transition: 0.2s;
}
.btn-danger:hover { background: var(--accent-red); color: white; }

/* --- Utility --- */
.danger-border { border-color: var(--accent-red); }
.danger-text { color: var(--accent-red) !important; }
</style>