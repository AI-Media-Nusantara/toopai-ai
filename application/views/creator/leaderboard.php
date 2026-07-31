<!-- file: application/views/creator/leaderboard.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>Leaderboard - Toopai</title>
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
        
        /* Fixed Header */
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
        
        /* Hero Banner */
        .hero-banner {
            background: linear-gradient(135deg, #1a1a2e, #0f0f1a);
            margin: 16px 20px;
            border-radius: 28px;
            padding: 24px 20px;
            text-align: center;
            border: 0.5px solid rgba(139, 92, 246, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .hero-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(139,92,246,0.1) 0%, transparent 70%);
            pointer-events: none;
        }
        
        .hero-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .hero-subtitle {
            font-size: 13px;
            color: #fbbf24;
            margin-bottom: 20px;
        }
        
        /* Countdown Timer */
        .countdown {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .countdown-item {
            text-align: center;
        }
        
        .countdown-number {
            font-size: 28px;
            font-weight: 700;
            background: rgba(255,255,255,0.1);
            padding: 8px 12px;
            border-radius: 16px;
            min-width: 60px;
        }
        
        .countdown-label {
            font-size: 10px;
            color: #9ca3af;
            margin-top: 6px;
        }
        
        /* Top 3 Podium */
        .podium {
            display: flex;
            justify-content: center;
            align-items: flex-end;
            gap: 12px;
            margin: 24px 20px 32px;
        }
        
        .podium-card {
            flex: 1;
            text-align: center;
            background: #111827;
            border-radius: 20px;
            padding: 16px 12px;
            position: relative;
            border: 0.5px solid rgba(255,255,255,0.06);
        }
        
        .podium-card.first {
            background: linear-gradient(135deg, #1a1a2e, #111827);
            border: 1px solid rgba(255, 215, 0, 0.3);
            transform: scale(1.02);
        }
        
        .podium-card.second {
            order: 1;
        }
        
        .podium-card.first {
            order: 2;
        }
        
        .podium-card.third {
            order: 3;
        }
        
        .podium-rank {
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .podium-rank.first { color: #ffd700; }
        .podium-rank.second { color: #c0c0c0; }
        .podium-rank.third { color: #cd7f32; }
        
        .podium-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #a78bfa, #06b6d4);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-size: 24px;
            font-weight: 600;
        }
        
        .podium-name {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 6px;
        }
        
        .podium-gmv {
            font-size: 14px;
            font-weight: 700;
            color: #34d399;
        }
        
        .crown {
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 28px;
        }
        
        /* Your Rank Card */
        .your-rank-card {
            background: linear-gradient(135deg, #1a1a2e, #0f0f1a);
            margin: 0 20px 24px;
            border-radius: 24px;
            padding: 20px;
            border: 0.5px solid rgba(139, 92, 246, 0.3);
        }
        
        .your-rank-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        
        .your-rank-label {
            font-size: 13px;
            color: #a78bfa;
            font-weight: 600;
        }
        
        .your-rank-number {
            font-size: 28px;
            font-weight: 700;
            color: #fbbf24;
        }
        
        .your-rank-stats {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 16px;
        }
        
        .your-rank-gmv {
            font-size: 20px;
            font-weight: 700;
            color: #34d399;
        }
        
        .your-rank-gap {
            font-size: 12px;
            color: #9ca3af;
        }
        
        .progress-container {
            background: #1a1a2e;
            border-radius: 30px;
            height: 8px;
            margin-bottom: 16px;
            overflow: hidden;
        }
        
        .progress-fill {
            background: linear-gradient(90deg, #a78bfa, #06b6d4);
            border-radius: 30px;
            height: 100%;
            width: 0%;
        }
        
        .btn-live {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border: none;
            padding: 12px;
            border-radius: 40px;
            color: white;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-live i {
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        /* Contenders Section */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            padding: 0 20px;
            margin: 20px 0 12px;
        }
        
        .section-title {
            font-size: 15px;
            font-weight: 600;
        }
        
        .contenders-list {
            margin: 0 20px;
            background: #111827;
            border-radius: 24px;
            overflow: hidden;
            border: 0.5px solid rgba(255, 255, 255, 0.06);
        }
        
        .contender-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border-bottom: 0.5px solid rgba(255, 255, 255, 0.06);
        }
        
        .contender-item:last-child {
            border-bottom: none;
        }
        
        .contender-rank {
            width: 36px;
            font-weight: 700;
            font-size: 14px;
            color: #fbbf24;
        }
        
        .contender-avatar {
            width: 40px;
            height: 40px;
            background: #1a1a2e;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
        }
        
        .contender-info {
            flex: 1;
        }
        
        .contender-name {
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 2px;
        }
        
        .contender-gmv {
            font-size: 12px;
            color: #34d399;
            font-weight: 500;
        }
        
        .contender-change {
            font-size: 11px;
            color: #10b981;
        }
        
        .contender-change.down {
            color: #ef4444;
        }
        
        /* Bottom Navigation */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(17, 24, 39, 0.96);
            backdrop-filter: blur(10px);
            border-top: 0.5px solid rgba(255, 255, 255, 0.08);
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
        
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #6b7280;
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
            font-weight: 500;
            z-index: 9999;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <!-- Fixed Header -->
    <div class="fixed-header">
        <div class="header-content">
            <button class="back-btn" onclick="history.back()"><i class="fas fa-arrow-left"></i></button>
            <span class="page-title">Leaderboard</span>
            <div style="width: 32px;"></div>
        </div>
    </div>
    
    <div class="main-container">
        <!-- Hero Banner -->
        <div class="hero-banner">
            <div class="hero-title">🏆 Live Leaderboard</div>
            <div class="hero-subtitle">Kejar GMV 10 JUTA! Hadiah eksklusif HANYA untuk Top 3 Kreator</div>
            
            <!-- Countdown Timer -->
            <div class="countdown" id="countdown">
                <div class="countdown-item">
                    <div class="countdown-number" id="days">03</div>
                    <div class="countdown-label">HARI</div>
                </div>
                <div class="countdown-item">
                    <div class="countdown-number" id="hours">14</div>
                    <div class="countdown-label">JAM</div>
                </div>
                <div class="countdown-item">
                    <div class="countdown-number" id="minutes">59</div>
                    <div class="countdown-label">MNT</div>
                </div>
                <div class="countdown-item">
                    <div class="countdown-number" id="seconds">00</div>
                    <div class="countdown-label">DETIK</div>
                </div>
            </div>
        </div>
        
        <!-- Top 3 Podium -->
   <div class="podium">
    <?php 
    // Ambil 3 teratas dengan urutan yang benar
    $first = isset($top_gmv[0]) ? $top_gmv[0] : null;
    $second = isset($top_gmv[1]) ? $top_gmv[1] : null;
    $third = isset($top_gmv[2]) ? $top_gmv[2] : null;
    ?>
    
    <!-- Podium 2 (Kiri) - Tempat peringkat 2 -->
    <div class="podium-card second">
        <div class="podium-rank second">#2</div>
        <div class="podium-avatar">
            <?= $second ? strtoupper(substr($second->creator_username, 0, 1)) : '?' ?>
        </div>
        <div class="podium-name"><?= $second ? '@' . htmlspecialchars($second->creator_username) : '—' ?></div>
        <div class="podium-gmv"><?= $second ? 'Rp ' . number_format($second->total_gmv, 0, ',', '.') : 'Rp 0' ?></div>
    </div>
    
    <!-- Podium 1 (Tengah) - Tempat peringkat 1 -->
    <div class="podium-card first">
        <?php if ($first): ?><div class="crown">👑</div><?php endif; ?>
        <div class="podium-rank first">#1</div>
        <div class="podium-avatar">
            <?= $first ? strtoupper(substr($first->creator_username, 0, 1)) : '?' ?>
        </div>
        <div class="podium-name"><?= $first ? '@' . htmlspecialchars($first->creator_username) : '—' ?></div>
        <div class="podium-gmv"><?= $first ? 'Rp ' . number_format($first->total_gmv, 0, ',', '.') : 'Rp 0' ?></div>
    </div>
    
    <!-- Podium 3 (Kanan) - Tempat peringkat 3 -->
    <div class="podium-card third">
        <div class="podium-rank third">#3</div>
        <div class="podium-avatar">
            <?= $third ? strtoupper(substr($third->creator_username, 0, 1)) : '?' ?>
        </div>
        <div class="podium-name"><?= $third ? '@' . htmlspecialchars($third->creator_username) : '—' ?></div>
        <div class="podium-gmv"><?= $third ? 'Rp ' . number_format($third->total_gmv, 0, ',', '.') : 'Rp 0' ?></div>
    </div>
</div>
        
        <!-- Your Rank Card -->
       <div class="your-rank-card">
    <div class="your-rank-header">
        <span class="your-rank-label">📊 PERINGKAT ANDA</span>
        <span class="your-rank-number">#<?= $current_rank > 0 ? $current_rank : '?' ?></span>
    </div>
    <div class="your-rank-stats">
        <span class="your-rank-gmv">Rp <?= number_format($current_gmv, 0, ',', '.') ?></span>
        <span class="your-rank-gap">Kurang Rp <?= number_format($gap_to_third, 0, ',', '.') ?> ke Peringkat 3</span>
    </div>
    <div class="progress-container">
        <div class="progress-fill" style="width: <?= $progress_percent ?>%"></div>
    </div>
    <div style="font-size: 11px; color: #9ca3af; margin-top: 8px; text-align: center;">
        GMV 30 hari: Rp <?= number_format($current_gmv_30d, 0, ',', '.') ?>
    </div>
    <button class="btn-live" onclick="window.location.href='<?= base_url('creator/campaigns') ?>'">
        <i class="fas fa-circle"></i> Gas Push Live Sekarang!
    </button>
</div>
        
        <!-- Penantang (Rank 4 - 50) -->
        <div class="section-header">
            <h3 class="section-title">🔥 Penantang (Rank 4 - 50)</h3>
            <span class="section-title" style="font-size: 11px; color: #fbbf24;">Live Race</span>
        </div>
        <div class="contenders-list" id="contendersList">
            <?php 
            $contenders = array_slice($top_gmv ?? [], 3, 10);
            if (!empty($contenders)):
                foreach ($contenders as $idx => $c):
                    $rank = $idx + 4;
            ?>
            <div class="contender-item">
                <div class="contender-rank">#<?= $rank ?></div>
                <div class="contender-avatar">
                    <?= strtoupper(substr($c->creator_username, 0, 1)) ?>
                </div>
                <div class="contender-info">
                    <div class="contender-name">@<?= htmlspecialchars($c->creator_username) ?></div>
                    <div class="contender-gmv">Rp <?= number_format($c->total_gmv, 0, ',', '.') ?></div>
                </div>
                <div class="contender-change">
                    <i class="fas fa-arrow-up"></i> 2
                </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <div class="empty-state">No contenders yet</div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Bottom Navigation -->
    <div class="bottom-nav">
        <div class="nav-container">
            <a href="<?= base_url('creator/dashboard') ?>" class="nav-item"><i class="fas fa-home"></i><span>Home</span></a>
            <a href="<?= base_url('creator/campaigns') ?>" class="nav-item"><i class="fas fa-bullhorn"></i><span>Campaign</span></a>
            <a href="<?= base_url('creator/leaderboard') ?>" class="nav-item active"><i class="fas fa-trophy"></i><span>Leaderboard</span></a>
            <a href="<?= base_url('creator/profile') ?>" class="nav-item"><i class="fas fa-user"></i><span>Profile</span></a>
        </div>
    </div>
    
    <script>
        // Countdown Timer (7 days from now)
        function updateCountdown() {
            const targetDate = new Date();
            targetDate.setDate(targetDate.getDate() + 7);
            targetDate.setHours(0, 0, 0, 0);
            
            const now = new Date();
            const diff = targetDate - now;
            
            if (diff <= 0) {
                document.getElementById('countdown').innerHTML = '<div class="hero-subtitle">Event Ended!</div>';
                return;
            }
            
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (86400000)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (3600000)) / (1000 * 60));
            const seconds = Math.floor((diff % (60000)) / 1000);
            
            document.getElementById('days').innerText = String(days).padStart(2, '0');
            document.getElementById('hours').innerText = String(hours).padStart(2, '0');
            document.getElementById('minutes').innerText = String(minutes).padStart(2, '0');
            document.getElementById('seconds').innerText = String(seconds).padStart(2, '0');
        }
        
        updateCountdown();
        setInterval(updateCountdown, 1000);
        
        // Load real data from server
        /*
        async function loadLeaderboardData() {
            try {
                const response = await fetch('<?= base_url("creator/get_leaderboard_data/today") ?>');
                const data = await response.json();
                
                if (data.success && data.top_gmv && data.top_gmv.length > 0) {
                    // Update Podium
                    const top3 = data.top_gmv.slice(0, 3);
                    const podiumCards = document.querySelectorAll('.podium-card');
                    
                    if (top3[0]) {
                        const firstCard = podiumCards[1];
                        if (firstCard) {
                            firstCard.querySelector('.podium-avatar').innerHTML = top3[0].creator_username.charAt(0).toUpperCase();
                            firstCard.querySelector('.podium-name').innerHTML = '@' + top3[0].creator_username;
                            firstCard.querySelector('.podium-gmv').innerHTML = 'Rp ' + formatNumber(top3[0].total_gmv);
                        }
                    }
                    
                    if (top3[1]) {
                        const secondCard = podiumCards[0];
                        if (secondCard) {
                            secondCard.querySelector('.podium-avatar').innerHTML = top3[1].creator_username.charAt(0).toUpperCase();
                            secondCard.querySelector('.podium-name').innerHTML = '@' + top3[1].creator_username;
                            secondCard.querySelector('.podium-gmv').innerHTML = 'Rp ' + formatNumber(top3[1].total_gmv);
                        }
                    }
                    
                    if (top3[2]) {
                        const thirdCard = podiumCards[2];
                        if (thirdCard) {
                            thirdCard.querySelector('.podium-avatar').innerHTML = top3[2].creator_username.charAt(0).toUpperCase();
                            thirdCard.querySelector('.podium-name').innerHTML = '@' + top3[2].creator_username;
                            thirdCard.querySelector('.podium-gmv').innerHTML = 'Rp ' + formatNumber(top3[2].total_gmv);
                        }
                    }
                    
                    // Update Contenders (Rank 4-50)
                    const contenders = data.top_gmv.slice(3, 15);
                    const contendersList = document.getElementById('contendersList');
                    
                    if (contenders && contenders.length > 0) {
                        contendersList.innerHTML = contenders.map((c, i) => {
                            const rank = i + 4;
                            return `
                                <div class="contender-item">
                                    <div class="contender-rank">#${rank}</div>
                                    <div class="contender-avatar">${c.creator_username.charAt(0).toUpperCase()}</div>
                                    <div class="contender-info">
                                        <div class="contender-name">@${escapeHtml(c.creator_username)}</div>
                                        <div class="contender-gmv">Rp ${formatNumber(c.total_gmv)}</div>
                                    </div>
                                    <div class="contender-change"><i class="fas fa-arrow-up"></i> ${Math.floor(Math.random() * 5) + 1}</div>
                                </div>
                            `;
                        }).join('');
                    } else {
                        contendersList.innerHTML = '<div class="empty-state">No contenders yet</div>';
                    }
                }
            } catch (error) {
                console.error('Error loading leaderboard:', error);
            }
        }
        */
        function formatNumber(num) {
            if (!num) return '0';
            return Number(num).toLocaleString('id-ID');
        }
        
        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/[&<>]/g, m => m === '&' ? '&amp;' : (m === '<' ? '&lt;' : '&gt;'));
        }
        
        // Load initial data
        loadLeaderboardData();
        
        function showToast(msg) {
            let toast = document.querySelector('.toast-message');
            if (toast) toast.remove();
            toast = document.createElement('div');
            toast.className = 'toast-message';
            toast.textContent = msg;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
    </script>
</body>
</html>