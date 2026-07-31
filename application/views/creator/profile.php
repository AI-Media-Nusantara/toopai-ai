<!-- file: application/views/creator/profile.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title><?= $title ?? 'Profile' ?> - Toopai</title>
      <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/logo/favicon_new.png') ?>">
    <link rel="icon" type="image/svg+xml" href="<?= base_url('assets/logo/favicon_new.png') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/logo/favicon_new.png') ?>">
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
        
        /* Profile Header */
        .profile-header { text-align: center; padding: 24px 20px; }
        .avatar { width: 80px; height: 80px; background: linear-gradient(135deg, #8b5cf6, #06b6d4); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 600; margin: 0 auto 12px; }
        .name { font-size: 20px; font-weight: 700; margin-bottom: 4px; }
        .tier { font-size: 13px; color: #a78bfa; }
        
        /* Progress Card */
        .progress-card { background: #111827; margin: 0 20px 16px; border-radius: 20px; padding: 16px; border: 0.5px solid rgba(255,255,255,0.06); }
        .progress-title { font-size: 14px; font-weight: 600; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
        .progress-bar { background: #1a1a2e; border-radius: 30px; height: 6px; margin-bottom: 8px; }
        .progress-fill { background: linear-gradient(90deg, #8b5cf6, #06b6d4); border-radius: 30px; height: 100%; width: <?= $progress_percent ?>%; }
        .progress-percent { font-size: 12px; color: #9ca3af; margin-bottom: 16px; }
        .checklist { display: flex; flex-direction: column; gap: 10px; }
        .checklist-item { display: flex; align-items: center; gap: 10px; font-size: 13px; color: #9ca3af; }
        .checklist-item.completed { color: #34d399; }
        .checklist-item i { width: 18px; }
        
        /* Benefits Card */
        .benefits-card { background: #111827; margin: 0 20px 16px; border-radius: 20px; padding: 16px; border: 0.5px solid rgba(255,255,255,0.06); }
        .benefits-title { font-size: 14px; font-weight: 600; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
        .benefits-list { display: flex; flex-direction: column; gap: 10px; }
        .benefit-item { font-size: 13px; color: #9ca3af; display: flex; align-items: center; gap: 10px; }
        .benefit-item i { width: 18px; color: #a78bfa; }
        
        /* Menu Item */
        .menu-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px;
    cursor: pointer;
    transition: all 0.2s;
}

.menu-item:active {
    background: #1a1a2e;
}

.menu-item:first-child {
    border-top-left-radius: 20px;
    border-top-right-radius: 20px;
}

.menu-item:last-child {
    border-bottom-left-radius: 20px;
    border-bottom-right-radius: 20px;
}
        .menu-card { background: #111827; margin: 0 20px 16px; border-radius: 20px; border: 0.5px solid rgba(255,255,255,0.06); overflow: hidden; }
        .menu-item { display: flex; align-items: center; justify-content: space-between; padding: 16px; cursor: pointer; transition: all 0.2s; }
        .menu-item:active { background: #1a1a2e; }
        .menu-left { display: flex; align-items: center; gap: 12px; }
        .menu-left i { width: 24px; color: #a78bfa; font-size: 18px; }
        .menu-left span { font-size: 15px; font-weight: 500; }
        .menu-right { color: #6b7280; font-size: 12px; }
        
        /* Upgrade Card */
        .upgrade-card { background: linear-gradient(135deg, #1a1a2e, #0f0f1a); margin: 0 20px 16px; border-radius: 20px; padding: 16px; border: 0.5px solid rgba(139,92,246,0.3); display: flex; justify-content: space-between; align-items: center; }
        .upgrade-text h4 { font-size: 14px; font-weight: 600; margin-bottom: 2px; }
        .upgrade-text p { font-size: 11px; color: #9ca3af; }
        .btn-upgrade { background: #8b5cf6; border: none; padding: 8px 16px; border-radius: 30px; color: white; font-size: 12px; font-weight: 600; cursor: pointer; text-decoration: none; }
        
        /* Bottom Navigation */
        .bottom-nav { position: fixed; bottom: 0; left: 0; right: 0; background: rgba(17,24,39,0.95); backdrop-filter: blur(10px); border-top: 0.5px solid rgba(255,255,255,0.08); padding: 8px 20px 12px; z-index: 100; }
        .nav-container { max-width: 500px; margin: 0 auto; display: flex; justify-content: space-between; }
        .nav-item { display: flex; flex-direction: column; align-items: center; gap: 3px; color: #6b7280; text-decoration: none; font-size: 10px; font-weight: 500; padding: 6px 0; }
        .nav-item i { font-size: 22px; }
        .nav-item.active { color: #a78bfa; }
        
        /* Modal */
        .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(8px); z-index: 200; display: flex; align-items: center; justify-content: center; visibility: hidden; opacity: 0; transition: all 0.2s; }
        .modal-overlay.active { visibility: visible; opacity: 1; }
        .modal-content { background: #111827; border-radius: 28px; width: 90%; max-width: 340px; padding: 24px; border: 0.5px solid rgba(255,255,255,0.1); }
        .modal-title { font-size: 18px; font-weight: 600; margin-bottom: 16px; text-align: center; }
        .modal-input { width: 100%; padding: 14px; background: #0f1420; border: 0.5px solid rgba(255,255,255,0.08); border-radius: 14px; color: #fff; font-size: 15px; margin-bottom: 12px; }
        .modal-input:focus { outline: none; border-color: #8b5cf6; }
        .modal-buttons { display: flex; gap: 12px; margin-top: 8px; }
        .modal-btn { flex: 1; padding: 12px; border-radius: 30px; font-weight: 600; font-size: 14px; cursor: pointer; text-align: center; }
        .modal-btn-primary { background: linear-gradient(135deg, #8b5cf6, #06b6d4); color: white; border: none; }
        .modal-btn-secondary { background: transparent; border: 0.5px solid rgba(255,255,255,0.2); color: #9ca3af; }
        .close-btn { position: absolute; top: 16px; right: 16px; background: none; border: none; color: #9ca3af; font-size: 20px; cursor: pointer; }
        
        .toast-message { position: fixed; bottom: 80px; left: 50%; transform: translateX(-50%); background: #34d399; color: #0a0a0e; padding: 10px 20px; border-radius: 30px; font-size: 13px; font-weight: 500; z-index: 9999; white-space: nowrap; }
    </style>
</head>
<body>
    <div class="fixed-header">
        <div class="header-content">
            <button class="back-btn" onclick="history.back()"><i class="fas fa-arrow-left"></i></button>
            <span class="page-title">Profile</span>
            <div style="width: 32px;"></div>
        </div>
    </div>
    
    <div class="main-container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="avatar"><?= strtoupper(substr($creator->username, 0, 1)) ?></div>
            <div class="name">Hi, <?= htmlspecialchars($creator->full_name ?: $creator->username) ?></div>
            <div class="tier"><?= htmlspecialchars($creator->category ?? 'Verified Creator') ?></div>
        </div>
        
        <!-- Progress Card -->
        <div class="progress-card">
            <div class="progress-title"><i class="fas fa-chart-line"></i> Progress to VIP</div>
            <div class="progress-bar"><div class="progress-fill"></div></div>
            <div class="progress-percent"><?= $progress_percent ?>% Complete</div>
            <div class="checklist">
                <div class="checklist-item <?= $check_campaigns ? 'completed' : '' ?>"><i class="fas <?= $check_campaigns ? 'fa-check-circle' : 'fa-circle' ?>"></i> Join 3 Campaign</div>
                <div class="checklist-item <?= $check_proof ? 'completed' : '' ?>"><i class="fas <?= $check_proof ? 'fa-check-circle' : 'fa-circle' ?>"></i> Submit 2 Proof</div>
                <div class="checklist-item <?= $check_gmv ? 'completed' : '' ?>"><i class="fas <?= $check_gmv ? 'fa-check-circle' : 'fa-circle' ?>"></i> Need Rp <?= number_format($gmv_remaining, 0, ',', '.') ?> more GMV</div>
                <div class="checklist-item <?= $check_campaign_active ? 'completed' : '' ?>"><i class="fas <?= $check_campaign_active ? 'fa-check-circle' : 'fa-circle' ?>"></i> Need <?= $campaign_remaining ?> more active campaign</div>
            </div>
        </div>
        
        <!-- Benefits Card -->
        <div class="benefits-card">
            <div class="benefits-title"><i class="fas fa-crown"></i> VIP Benefits</div>
            <div class="benefits-list">
                <div class="benefit-item"><i class="fas fa-clock"></i> Early access Campaign</div>
                <div class="benefit-item"><i class="fas fa-gift"></i> Higher Bonus</div>
                <div class="benefit-item"><i class="fas fa-star"></i> Private Brand Campaign</div>
                <div class="benefit-item"><i class="fas fa-chart-line"></i> Better Product Recommendation</div>
            </div>
        </div>
        
        <!-- Menu Card -->
        <!-- Menu Card -->
<div class="menu-card">
    <div class="menu-item" onclick="openChangePasswordModal()">
        <div class="menu-left">
            <i class="fas fa-key"></i>
            <span>Ganti Password</span>
        </div>
        <div class="menu-right">
            <i class="fas fa-chevron-right"></i>
        </div>
    </div>
    <div class="menu-item" onclick="showToast('Fitur segera hadir')">
        <div class="menu-left">
            <i class="fas fa-bell"></i>
            <span>Notifikasi</span>
        </div>
        <div class="menu-right">
            <i class="fas fa-chevron-right"></i>
        </div>
    </div>
    <div class="menu-item" onclick="showToast('Fitur segera hadir')">
        <div class="menu-left">
            <i class="fas fa-shield-alt"></i>
            <span>Privasi & Keamanan</span>
        </div>
        <div class="menu-right">
            <i class="fas fa-chevron-right"></i>
        </div>
    </div>
    <div class="menu-item" onclick="confirmLogout()" style="border-top: 0.5px solid rgba(255,255,255,0.06); margin-top: 8px;">
        <div class="menu-left">
            <i class="fas fa-sign-out-alt" style="color: #ef4444;"></i>
            <span style="color: #ef4444;">Keluar</span>
        </div>
        <div class="menu-right">
            <i class="fas fa-chevron-right" style="color: #ef4444;"></i>
        </div>
    </div>
</div>
        
        <!-- Upgrade Card -->
        <div class="upgrade-card">
            <div class="upgrade-text">
                <h4>Upgrade</h4>
                <p>Connect TikTok Affiliate Data</p>
            </div>
            <?php if (empty($creator->creator_hid) || empty($creator->access_token)): ?>
            <a href="<?= base_url('creator_auth/authorize_tiktok') ?>" class="btn-upgrade">Connect →</a>
            <?php else: ?>
            <button class="btn-upgrade" onclick="showToast('Already connected!')">Connected ✓</button>
            <?php endif; ?>
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
    
    <!-- Modal Ganti Password -->
    <div id="passwordModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-title">Ganti Password</div>
            <input type="password" id="currentPassword" class="modal-input" placeholder="Password saat ini">
            <input type="password" id="newPassword" class="modal-input" placeholder="Password baru (min 6 karakter)">
            <input type="password" id="confirmPassword" class="modal-input" placeholder="Konfirmasi password baru">
            <div class="modal-buttons">
                <button class="modal-btn modal-btn-secondary" onclick="closePasswordModal()">Batal</button>
                <button class="modal-btn modal-btn-primary" onclick="changePassword()">Simpan</button>
            </div>
        </div>
    </div>
    
    <script>
        const baseUrl = '<?= base_url() ?>';
        
        function openChangePasswordModal() {
            document.getElementById('passwordModal').classList.add('active');
        }
        
        function closePasswordModal() {
            document.getElementById('passwordModal').classList.remove('active');
            document.getElementById('currentPassword').value = '';
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmPassword').value = '';
        }
        
        async function changePassword() {
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (!currentPassword || !newPassword || !confirmPassword) {
                showToast('Semua field harus diisi', 'error');
                return;
            }
            
            if (newPassword !== confirmPassword) {
                showToast('Password baru tidak sama', 'error');
                return;
            }
            
            if (newPassword.length < 6) {
                showToast('Password minimal 6 karakter', 'error');
                return;
            }
            
            const response = await fetch(baseUrl + 'creator/change_password', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    current_password: currentPassword,
                    new_password: newPassword,
                    confirm_password: confirmPassword
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showToast('Password berhasil diubah!');
                closePasswordModal();
            } else {
                showToast(data.message, 'error');
            }
        }
        
        function showToast(msg, type = 'success') {
            let t = document.querySelector('.toast-message');
            if (t) t.remove();
            t = document.createElement('div');
            t.className = 'toast-message';
            t.textContent = msg;
            if (type === 'error') t.style.background = '#ef4444';
            document.body.appendChild(t);
            setTimeout(() => t.remove(), 2000);
        }
        
        // Close modal when clicking outside
        document.getElementById('passwordModal').addEventListener('click', function(e) {
            if (e.target === this) closePasswordModal();
        });
        function confirmLogout() {
    if (confirm('Apakah Anda yakin ingin logout?')) {
        window.location.href = baseUrl + 'creator_auth/logout';
    }
}
    </script>
</body>
</html>