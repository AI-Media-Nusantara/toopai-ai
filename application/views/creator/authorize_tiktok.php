<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title><?= $title ?? 'Authorize TikTok' ?> - Toopai</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-font-smoothing: antialiased; }
        body { font-family: -apple-system, 'SF Pro Text', Helvetica, Arial, sans-serif; background: #0a0a0e; color: #fff; font-size: 15px; }
        
        /* Fixed Header */
        .fixed-header { position: fixed; top: 0; left: 0; right: 0; background: rgba(10,10,14,0.95); backdrop-filter: blur(10px); z-index: 100; padding: 12px 20px; border-bottom: 0.5px solid rgba(255,255,255,0.08); }
        .header-content { max-width: 500px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; }
        .back-btn { background: none; border: none; color: #fff; font-size: 20px; cursor: pointer; }
        .page-title { font-size: 17px; font-weight: 600; }
        
        /* Main Container */
        .main-container { max-width: 500px; margin: 0 auto; padding: 60px 0 70px; }
        
        /* Auth Card */
        .auth-card { background: #111827; margin: 0 20px 16px; border-radius: 20px; padding: 24px; border: 0.5px solid rgba(255,255,255,0.06); }
        
        .tiktok-badge { background: linear-gradient(135deg, #000000, #25F4EE, #FE2C55); padding: 6px 14px; border-radius: 40px; display: inline-block; color: white; font-weight: 600; font-size: 12px; margin-bottom: 16px; }
        
        h2 { font-size: 24px; margin-bottom: 8px; background: linear-gradient(135deg, #fff, #8b5cf6); -webkit-background-clip: text; background-clip: text; color: transparent; }
        
        .subtitle { color: #9ca3af; margin-bottom: 24px; font-size: 14px; }
        
        /* Status Card */
        .status-card { background: rgba(0, 0, 0, 0.3); border-radius: 16px; padding: 16px; margin-bottom: 20px; border: 0.5px solid rgba(255,255,255,0.06); }
        .status-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
        .status-label { font-size: 13px; color: #9ca3af; }
        .status-value { font-size: 14px; font-weight: 600; }
        .status-connected { color: #34d399; }
        .status-expired { color: #ef4444; }
        .status-disconnected { color: #f59e0b; }
        
        /* Info Box */
        .info-box { background: rgba(139, 92, 246, 0.1); border-radius: 16px; padding: 16px; margin-bottom: 20px; border-left: 3px solid #8b5cf6; }
        .info-box ul { margin-top: 12px; padding-left: 20px; }
        .info-box li { margin: 8px 0; color: #9ca3af; font-size: 13px; }
        
        /* Buttons */
        .btn-group { display: flex; gap: 12px; margin-bottom: 20px; }
        .btn-tiktok { flex: 1; background: linear-gradient(135deg, #000000, #25F4EE, #FE2C55); border: none; padding: 14px; border-radius: 30px; color: white; font-weight: 600; font-size: 14px; cursor: pointer; text-decoration: none; text-align: center; display: inline-block; transition: all 0.2s; }
        .btn-tiktok:hover { transform: translateY(-2px); }
        .btn-outline { flex: 1; background: transparent; border: 0.5px solid rgba(255,255,255,0.2); padding: 14px; border-radius: 30px; color: #fff; font-weight: 500; cursor: pointer; transition: all 0.2s; }
        .btn-outline:hover { border-color: #8b5cf6; background: rgba(139, 92, 246, 0.1); }
        .btn-danger { border-color: #ef4444; color: #ef4444; }
        
        /* Profile Info */
        .profile-info { background: rgba(0, 0, 0, 0.3); border-radius: 16px; padding: 16px; margin-top: 16px; display: none; }
        .profile-header { display: flex; align-items: center; gap: 16px; }
        .profile-avatar { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; }
        .profile-avatar-placeholder { width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #8b5cf6, #06b6d4); display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold; }
        
        /* Bottom Navigation */
        .bottom-nav { position: fixed; bottom: 0; left: 0; right: 0; background: rgba(17,24,39,0.95); backdrop-filter: blur(10px); border-top: 0.5px solid rgba(255,255,255,0.08); padding: 8px 20px 12px; z-index: 100; }
        .nav-container { max-width: 500px; margin: 0 auto; display: flex; justify-content: space-between; }
        .nav-item { display: flex; flex-direction: column; align-items: center; gap: 3px; color: #6b7280; text-decoration: none; font-size: 10px; font-weight: 500; padding: 6px 0; }
        .nav-item i { font-size: 22px; }
        .nav-item.active { color: #a78bfa; }
        
        /* Toast */
        .toast-message { position: fixed; bottom: 80px; left: 50%; transform: translateX(-50%); background: #34d399; color: #0a0a0e; padding: 10px 20px; border-radius: 30px; font-size: 13px; font-weight: 500; z-index: 9999; white-space: nowrap; }
        .toast-message.error { background: #ef4444; color: white; }
    </style>
</head>
<body>
    <div class="fixed-header">
        <div class="header-content">
            <button class="back-btn" onclick="history.back()"><i class="fas fa-arrow-left"></i></button>
            <span class="page-title">Authorize TikTok</span>
            <div style="width: 32px;"></div>
        </div>
    </div>
    
    <div class="main-container">
        <div class="auth-card">
            <div class="tiktok-badge">
                <i class="fab fa-tiktok"></i> TikTok Integration
            </div>
            
            <h2>Connect TikTok Account</h2>
            <p class="subtitle">Link your TikTok to unlock creator features</p>
            
            <!-- Status Card -->
            <div class="status-card">
                <div class="status-header">
                    <span class="status-label"><i class="fas fa-plug"></i> Connection Status</span>
                    <?php if ($has_token && !$is_expired): ?>
                        <span class="status-value status-connected"><i class="fas fa-check-circle"></i> Connected</span>
                    <?php elseif ($has_token && $is_expired): ?>
                        <span class="status-value status-expired"><i class="fas fa-exclamation-triangle"></i> Expired</span>
                    <?php else: ?>
                        <span class="status-value status-disconnected"><i class="fas fa-times-circle"></i> Not Connected</span>
                    <?php endif; ?>
                </div>
                <?php if ($has_token): ?>
                <div style="font-size: 11px; color: #6b7280; margin-top: 8px;">
                    <i class="fas fa-clock"></i> Expires: <?= date('d/m/Y H:i:s', strtotime($token_expire)) ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Info Box -->
            <div class="info-box">
                <div style="display: flex; gap: 12px; align-items: flex-start;">
                    <i class="fab fa-tiktok" style="font-size: 24px; color: #8b5cf6;"></i>
                    <div>
                        <strong style="font-size: 14px;">Why connect TikTok?</strong>
                        <ul>
                            <li><i class="fas fa-chart-line"></i> Get video analytics & performance data</li>
                            <li><i class="fas fa-music"></i> Access trending music suggestions</li>
                            <li><i class="fas fa-link"></i> Auto-generate affiliate links</li>
                            <li><i class="fas fa-chart-simple"></i> Track your content metrics</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="btn-group">
                <?php if (!$has_token || $is_expired): ?>
                    <a href="<?= base_url('creator/do_authorize_tiktok') ?>" class="btn-tiktok">
                        <i class="fab fa-tiktok"></i> Connect TikTok
                    </a>
                <?php else: ?>
                    <button onclick="refreshToken()" class="btn-outline" id="refreshBtn">
                        <i class="fas fa-sync-alt"></i> Refresh Token
                    </button>
                    <button onclick="revokeToken()" class="btn-outline btn-danger" id="revokeBtn">
                        <i class="fas fa-trash-alt"></i> Revoke Access
                    </button>
                <?php endif; ?>
            </div>
            
            <!-- Profile Info Section -->
            <?php if ($has_token && !$is_expired): ?>
            <div style="margin-top: 8px;">
                <button onclick="getUserInfo()" class="btn-outline" id="userInfoBtn" style="width: 100%;">
                    <i class="fas fa-user"></i> Get TikTok Profile
                </button>
            </div>
            <?php endif; ?>
            
            <div id="profileInfo" class="profile-info"></div>
        </div>
    </div>
    
    <!-- Bottom Navigation -->
    <div class="bottom-nav">
        <div class="nav-container">
            <a href="<?= base_url('creator/dashboard') ?>" class="nav-item"><i class="fas fa-home"></i><span>Home</span></a>
            <a href="<?= base_url('creator/campaigns') ?>" class="nav-item"><i class="fas fa-bullhorn"></i><span>Campaign</span></a>
            <a href="<?= base_url('creator/leaderboard') ?>" class="nav-item"><i class="fas fa-trophy"></i><span>Leaderboard</span></a>
            <a href="<?= base_url('creator/profile') ?>" class="nav-item active"><i class="fas fa-user"></i><span>Profile</span></a>
        </div>
    </div>
    
    <script>
        const baseUrl = '<?= base_url() ?>';
        
        function showToast(msg, type = 'success') {
            let t = document.querySelector('.toast-message');
            if (t) t.remove();
            t = document.createElement('div');
            t.className = 'toast-message' + (type === 'error' ? ' error' : '');
            t.textContent = msg;
            document.body.appendChild(t);
            setTimeout(() => t.remove(), 3000);
        }
        
        async function refreshToken() {
            const btn = document.getElementById('refreshBtn');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Refreshing...';
            
            try {
                const response = await fetch(baseUrl + 'creator/refresh_tiktok_token', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                });
                const data = await response.json();
                
                if (data.success) {
                    showToast('✅ Token refreshed successfully!');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(data.message || 'Failed to refresh token', 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (error) {
                showToast('Error: ' + error.message, 'error');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
        
        async function revokeToken() {
            if (!confirm('Are you sure you want to revoke TikTok access? You will need to re-authorize to use TikTok features.')) return;
            
            const btn = document.getElementById('revokeBtn');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Revoking...';
            
            try {
                const response = await fetch(baseUrl + 'creator/revoke_tiktok_token', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                });
                const data = await response.json();
                
                if (data.success) {
                    showToast('✅ Token revoked successfully');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(data.message || 'Failed to revoke token', 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (error) {
                showToast('Error: ' + error.message, 'error');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
        
        async function getUserInfo() {
            const btn = document.getElementById('userInfoBtn');
            const profileDiv = document.getElementById('profileInfo');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Loading...';
            
            try {
                const response = await fetch(baseUrl + 'creator/get_tiktok_user_info', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                });
                const data = await response.json();
                
                if (data.success && data.data) {
                    const avatarHtml = data.data.avatar_url ? 
                        `<img src="${escapeHtml(data.data.avatar_url)}" class="profile-avatar" onerror="this.style.display='none'">` :
                        `<div class="profile-avatar-placeholder">
                            <i class="fab fa-tiktok"></i>
                        </div>`;
                    
                    profileDiv.innerHTML = `
                        <div class="profile-header">
                            ${avatarHtml}
                            <div>
                                <div><strong>${escapeHtml(data.data.display_name || data.data.username)}</strong></div>
                                <div style="font-size: 12px; color: #9ca3af;">@${escapeHtml(data.data.username)}</div>
                                <div style="font-size: 11px; margin-top: 4px;">
                                    <i class="fas fa-users"></i> ${formatNumber(data.data.follower_count)} followers
                                </div>
                            </div>
                        </div>
                    `;
                    profileDiv.style.display = 'block';
                    showToast('Profile loaded!', 'success');
                } else if (data.expired) {
                    showToast('Token expired. Please re-authorize.', 'error');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showToast(data.message || 'Failed to get user info', 'error');
                }
            } catch (error) {
                showToast('Error: ' + error.message, 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
        
        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }
        
        function formatNumber(num) {
            if (!num) return '0';
            return Number(num).toLocaleString('id-ID');
        }
    </script>
</body>
</html>