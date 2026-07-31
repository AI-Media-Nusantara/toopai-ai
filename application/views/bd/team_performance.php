<!-- file: application/views/bd/team_performance.php -->
<style>
    /* ===== Team Performance - BA style refresh ===== */
    :root{
        --tp-panel: rgba(8,16,34,.78);
        --tp-panel-2: rgba(13,24,48,.86);
        --tp-border: rgba(112,136,185,.18);
        --tp-border-strong: rgba(124,60,255,.34);
        --tp-text: var(--text-primary,#f7fbff);
        --tp-muted: var(--text-muted,#8e9bb6);
        --tp-muted-2: var(--text-secondary,#b7c1d6);
        --tp-purple: var(--purple,#7c3cff);
        --tp-blue: var(--blue,#3b82f6);
        --tp-cyan: var(--cyan,#10dff0);
        --tp-green:#10b981;
        --tp-orange:#f59e0b;
    }

    .filter-bar{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:24px;
        margin-bottom:24px;
        flex-wrap:wrap;
    }

    .page-info{
        flex:1;
    }

    .page-title{
        margin:0 0 4px;
        font-size:24px;
        font-weight:800;
        background:linear-gradient(135deg, var(--tp-purple), var(--tp-cyan));
        -webkit-background-clip:text;
        background-clip:text;
        color:transparent;
    }

    .page-subtitle{
        margin:0;
        color:var(--tp-muted-2);
        font-size:13px;
    }

    .date-filter{
        display:flex;
        align-items:center;
        gap:10px;
        flex-shrink:0;
        background:linear-gradient(160deg, rgba(9,17,34,.72), rgba(4,10,22,.86));
        padding:8px 16px;
        border-radius:40px;
        border:1px solid var(--tp-border);
        flex-wrap:wrap;
    }

    .date-input{
        background:rgba(255,255,255,.05);
        border:1px solid rgba(255,255,255,.08);
        border-radius:8px;
        padding:8px 12px;
        color:#fff;
        font-size:12px;
        color-scheme:dark;
    }

    .date-input::-webkit-calendar-picker-indicator{
        filter:invert(100%);
        opacity:.9;
    }

    .btn-filter{
        height:36px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:6px;
        background:rgba(124,60,255,.10);
        border:1px solid rgba(124,60,255,.32);
        color:#c084fc;
        padding:0 14px;
        border-radius:999px;
        cursor:pointer;
        font-size:12px;
        font-weight:600;
        transition:.2s ease;
    }

    .btn-filter:hover{
        background:var(--tp-purple);
        color:#fff;
    }

    .stats-summary{
        display:grid;
        grid-template-columns:repeat(3,minmax(200px,1fr));
        gap:14px;
        margin-bottom:24px;
    }

    .stat-card{
        position:relative;
        min-height:92px;
        padding:18px 18px 16px 76px;
        text-align:left;
        border-radius:18px;
        border:1px solid var(--tp-border);
        background:linear-gradient(160deg,rgba(20,27,54,.84),rgba(7,14,30,.88));
        overflow:hidden;
    }

    .stat-card:before{
        content:"\f201";
        font-family:"Font Awesome 6 Free";
        font-weight:900;
        position:absolute;
        left:18px;
        top:50%;
        width:42px;
        height:42px;
        transform:translateY(-50%);
        display:grid;
        place-items:center;
        border-radius:50%;
        color:#fff;
        font-size:16px;
        background:linear-gradient(135deg,var(--tp-purple),var(--tp-blue));
    }

    .stat-card:nth-child(2):before{content:"\f291";background:linear-gradient(135deg,#10dff0,#3b82f6);}
    .stat-card:nth-child(3):before{content:"\f0c0";background:linear-gradient(135deg,#10b981,#39f08a);}

    .stat-value{
        font-size:23px;
        line-height:1;
        font-weight:900;
        color:#fff;
    }

    .stat-growth{
        font-size:11px;
        margin-top:6px;
        display:flex;
        align-items:center;
        gap:4px;
    }

    .stat-growth.positive{color:#4ade80;}
    .stat-growth.negative{color:#ef4444;}

    .stat-label{
        margin-top:8px;
        color:var(--tp-muted-2);
        font-size:11px;
        font-weight:700;
        text-transform:uppercase;
    }

    .stat-period{
        font-size:9px;
        color:var(--tp-muted);
        margin-top:4px;
    }

    .team-table-wrap{
        overflow-x:auto;
        border-radius:20px;
        background:linear-gradient(160deg, rgba(9,17,34,.74), rgba(4,10,22,.88));
        padding:4px;
    }

    .team-table{
        width:100%;
        min-width:1200px;
        border-collapse:collapse;
        border-radius:16px;
        overflow:hidden;
    }

    .team-table th,
    .team-table td{
        padding:12px 12px;
        text-align:left;
        border-bottom:1px solid rgba(112,136,185,.10);
        font-size:12px;
        color:var(--tp-muted-2);
    }

    .team-table th{
        background:rgba(124,60,255,.10);
        color:#c084fc;
        font-size:11px;
        font-weight:700;
        text-transform:uppercase;
    }

    .team-table tbody tr:hover{
        background:rgba(124,60,255,.05);
    }

    .team-table td strong{
        color:#fff;
        font-size:13px;
    }

    .rank-badge{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        width:30px;
        height:30px;
        border-radius:50%;
        font-size:12px;
        font-weight:900;
        background:rgba(255,255,255,.06);
        color:var(--tp-muted-2);
        border:1px solid rgba(255,255,255,.08);
    }

    .rank-1{background:linear-gradient(135deg,#ffd700,#ffb700); color:#0a0e1a; border:0;}
    .rank-2{background:linear-gradient(135deg,#c0c0c0,#a8a8a8); color:#0a0e1a; border:0;}
    .rank-3{background:linear-gradient(135deg,#cd7f32,#b8651a); color:white; border:0;}

    .progress-bar{
        width:80px;
        height:6px;
        background:rgba(255,255,255,.08);
        border-radius:999px;
        overflow:hidden;
    }

    .progress-fill{
        height:100%;
        background:linear-gradient(90deg,var(--tp-purple),var(--tp-blue));
        border-radius:999px;
    }

    .gmv-cell{
        color:#4ade80;
        font-weight:700;
    }

    .growth-positive{
        color:#4ade80;
        font-size:10px;
    }

    .growth-negative{
        color:#ef4444;
        font-size:10px;
    }

    .task-badge{
        display:inline-block;
        padding:2px 8px;
        border-radius:20px;
        font-size:10px;
        font-weight:600;
        text-align:center;
        min-width:40px;
    }

    .task-hunting{background:rgba(139,92,246,0.15); color:#8b5cf6;}
    .task-followup{background:rgba(245,158,11,0.15); color:#f59e0b;}
    .task-setup{background:rgba(59,130,246,0.15); color:#3b82f6;}
    .task-monitoring{background:rgba(16,185,129,0.15); color:#10b981;}

    @media(max-width:1100px){
        .stats-summary{grid-template-columns:repeat(2,1fr);}
        .filter-bar{flex-direction:column; align-items:flex-start;}
        .date-filter{width:100%;}
    }

    @media(max-width:768px){
        .stats-summary{grid-template-columns:1fr;}
        .team-table th,.team-table td{padding:8px;}
        .task-badge{min-width:30px; font-size:9px;}
    }
    
    .btn-back-dashboard {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #1e293b;
    color: #cbd5e6;
    padding: 8px 16px;
    border-radius: 40px;
    text-decoration: none;
    font-size: 12px;
    border: 1px solid #2a3346;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-back-dashboard {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #1e293b;
    color: #cbd5e6;
    padding: 8px 20px;
    border-radius: 40px;
    border: 1px solid #2a3346;
    cursor: pointer;
    font-size: 13px;
    transition: all 0.2s ease;
    min-width: 160px;
    text-decoration: none;
    font-family: inherit;
    user-select: none;
    -webkit-user-select: none;
}

.btn-back-dashboard:hover {
    background: #2a3346;
    color: #ffffff;
    border-color: #4a5a7a;
    transform: translateX(-2px);
}

.btn-back-dashboard:active {
    transform: scale(0.96);
}

.btn-back-dashboard i {
    font-size: 12px;
    pointer-events: none; /* Mencegah icon blocking click */
}
</style>

<div class="filter-bar">
<button onclick="goBackToDashboard()" 
        id="btnBackDashboard"
        class="btn-back-dashboard"
        style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: #1e293b; color: #cbd5e6; padding: 10px 24px; border-radius: 40px; border: 1px solid #2a3346; cursor: pointer; font-size: 13px; transition: all 0.2s ease; min-width: 170px; box-sizing: border-box; user-select: none;"
        onmouseover="this.style.background='#2a3346'; this.style.color='#ffffff'; this.style.borderColor='#4a5a7a';"
        onmouseout="this.style.background='#1e293b'; this.style.color='#cbd5e6'; this.style.borderColor='#2a3346';">
    <i class="fas fa-arrow-left" id="backIcon" style="font-size: 13px; pointer-events: none;"></i> 
    <span id="backText" style="pointer-events: none;">Back to Dashboard</span>
</button>
    <div class="page-info">

        <h2 class="page-title"><i class="fas fa-users"></i> Team Performance</h2>
        <p class="page-subtitle">
            <?php if ($is_supervisor): ?>
               
            <?php else: ?>
                Pantau performa Anda sebagai BD
            <?php endif; ?>
        </p>
    </div>

    <div class="date-filter">
        <label style="color: var(--text-muted); font-size: 11px;">Periode:</label>
        <input type="date" id="startDateFilter" class="date-input" value="<?= $start_date ?>">
        <span>s/d</span>
        <input type="date" id="endDateFilter" class="date-input" value="<?= $end_date ?>">
        <button id="applyFilterBtn" class="btn-filter"><i class="fas fa-calendar-alt"></i> Terapkan</button>
        <button id="resetFilterBtn" class="btn-filter"><i class="fas fa-undo-alt"></i> Hari Ini</button>
    </div>
</div>


<!-- ============================================================ -->
<!-- STATS SUMMARY -->
<!-- ============================================================ -->
<div class="stats-summary">
    <div class="stat-card">
        <div class="stat-value">Rp <?= number_format($team_summary['total_gmv'], 0, ',', '.') ?></div>
        <div class="stat-growth <?= $team_summary['total_gmv_growth'] >= 0 ? 'positive' : 'negative' ?>">
            <i class="fas fa-arrow-<?= $team_summary['total_gmv_growth'] >= 0 ? 'up' : 'down' ?>"></i>
            <?= abs($team_summary['total_gmv_growth']) ?>% dari periode sebelumnya
        </div>
        <div class="stat-label">Total GMV</div>
        <div class="stat-period">Periode: <?= date('d/m/Y', strtotime($start_date)) ?> - <?= date('d/m/Y', strtotime($end_date)) ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= number_format($team_summary['total_orders']) ?></div>
        <div class="stat-growth <?= $team_summary['total_orders_growth'] >= 0 ? 'positive' : 'negative' ?>">
            <i class="fas fa-arrow-<?= $team_summary['total_orders_growth'] >= 0 ? 'up' : 'down' ?>"></i>
            <?= abs($team_summary['total_orders_growth']) ?>% dari periode sebelumnya
        </div>
        <div class="stat-label">Total Orders</div>
        <div class="stat-period">Periode: <?= date('d/m/Y', strtotime($start_date)) ?> - <?= date('d/m/Y', strtotime($end_date)) ?></div>
    </div>
    <div class="stat-card">
        <!-- 🔥 PERBAIKAN: Brand dengan penjualan / total brand aktif -->
        <div class="stat-value"><?= number_format($team_summary['total_brands_with_sales']) ?> / <?= number_format($team_summary['total_active_brands']) ?></div>
        <div class="stat-growth">
            <?php 
            $active_percentage = $team_summary['total_active_brands'] > 0 
                ? round(($team_summary['total_brands_with_sales'] / $team_summary['total_active_brands']) * 100, 1) 
                : 0;
            ?>
            <i class="fas fa-chart-line"></i> <?= $active_percentage ?>% brand aktif memiliki penjualan
        </div>
        <div class="stat-label">Brand Aktif dengan Penjualan</div>
        <div class="stat-period">Total brand aktif: <?= number_format($team_summary['total_active_brands']) ?></div>
    </div>
</div>

<div class="team-table-wrap">
    <table class="team-table">
        <thead>
            <tr>
                <th>Rank</th>
                <th>Member</th>
                <th>Total Brands</th>
                <th>Brands<br><span style="font-size:9px;">(dengan sales)</span></th>
                <th>GMV</th>
                <th>Orders</th>
                <th>Commission</th>
                <th>Progress</th>
                <th>Hunting</th>
                <th>Follow Up</th>
                <th>Setup</th>
                <th>Monitoring</th>
            </tr>
        </thead>
        <tbody>
            <?php $rank = 1; foreach ($team_members as $member): ?>
            <tr>
                <td>
                    <span class="rank-badge <?= $rank == 1 ? 'rank-1' : ($rank == 2 ? 'rank-2' : ($rank == 3 ? 'rank-3' : '')) ?>">
                        <?= $rank++ ?>
                    </span>
                 </td>
                <td>
                    <strong><?= htmlspecialchars($member->full_name ?: $member->username) ?></strong>
                    <div style="font-size: 10px; color: var(--text-muted);">@<?= htmlspecialchars($member->username) ?></div>
                 </td>
                <td><?= number_format($member->total_brands ?? 0) ?></td>
                <td>
                    <?= number_format($member->brands_with_sales ?? 0) ?>
                    <?php if (($member->brands_with_sales ?? 0) > 0): ?>
                    <span style="font-size:9px; color:#4ade80;">(<?= round(($member->brands_with_sales / max($member->total_brands, 1)) * 100, 1) ?>%)</span>
                    <?php endif; ?>
                 </td>
                <td class="gmv-cell">
                    Rp <?= number_format($member->total_gmv ?? 0, 0, ',', '.') ?>
                    <?php if (($member->gmv_growth ?? 0) != 0): ?>
                    <div class="<?= ($member->gmv_growth ?? 0) >= 0 ? 'growth-positive' : 'growth-negative' ?>" style="font-size:9px;">
                        <i class="fas fa-arrow-<?= ($member->gmv_growth ?? 0) >= 0 ? 'up' : 'down' ?>"></i> <?= abs($member->gmv_growth ?? 0) ?>%
                    </div>
                    <?php endif; ?>
                 </td>
                <td>
                    <?= number_format($member->total_orders ?? 0) ?>
                    <?php if (($member->orders_growth ?? 0) != 0): ?>
                    <div class="<?= ($member->orders_growth ?? 0) >= 0 ? 'growth-positive' : 'growth-negative' ?>" style="font-size:9px;">
                        <i class="fas fa-arrow-<?= ($member->orders_growth ?? 0) >= 0 ? 'up' : 'down' ?>"></i> <?= abs($member->orders_growth ?? 0) ?>%
                    </div>
                    <?php endif; ?>
                 </td>
                <td>Rp <?= number_format($member->total_commission ?? 0, 0, ',', '.') ?></td>
                <td>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?= $member->progress ?? 0 ?>%"></div>
                    </div>
                    <div style="font-size: 10px; margin-top: 4px;"><?= $member->progress ?? 0 ?>%</div>
                 </td>
                <td><span class="task-badge task-hunting"><?= number_format($member->task_stats['hunting'] ?? 0) ?></span></td>
                <td><span class="task-badge task-followup"><?= number_format($member->task_stats['followup'] ?? 0) ?></span></td>
                <td><span class="task-badge task-setup"><?= number_format($member->task_stats['setup'] ?? 0) ?></span></td>
                <td><span class="task-badge task-monitoring"><?= number_format($member->task_stats['monitoring'] ?? 0) ?></span></td>
            </tr>
            <?php endforeach; ?>
            
            <?php if (empty($team_members)): ?>
            <tr>
                <td colspan="12" style="text-align: center; padding: 40px;">
                    <i class="fas fa-users" style="font-size: 48px; margin-bottom: 16px; display: block; opacity: 0.5;"></i>
                    <p>Belum ada data member</p>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
     </table>
</div>

<script>
const startDateInput = document.getElementById('startDateFilter');
const endDateInput = document.getElementById('endDateFilter');
const applyFilterBtn = document.getElementById('applyFilterBtn');
const resetFilterBtn = document.getElementById('resetFilterBtn');

function applyDateFilter() {
    const startDate = startDateInput.value;
    const endDate = endDateInput.value;
    
    if (startDate && endDate && startDate <= endDate) {
        window.location.href = baseUrlDashboard + `bd/team_performance?start_date=${startDate}&end_date=${endDate}`;
    } else {
        showToastGlobal('Tanggal tidak valid', 'error');
    }
}

function resetDateFilter() {
    const today = new Date().toISOString().split('T')[0];
    window.location.href = baseUrlDashboard + `bd/team_performance?start_date=${today}&end_date=${today}`;
}

if (applyFilterBtn) applyFilterBtn.addEventListener('click', applyDateFilter);
if (resetFilterBtn) resetFilterBtn.addEventListener('click', resetDateFilter);

function showToastGlobal(message, type) {
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed; bottom: 20px; right: 20px; 
        background: ${type === 'error' ? '#ef4444' : '#10b981'}; 
        color: white; padding: 12px 20px; 
        border-radius: 12px; font-size: 13px; 
        z-index: 9999; animation: slideIn 0.3s ease;
    `;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
function goBackToDashboard() {
    const btn = document.getElementById('btnBackDashboard');
    const icon = document.getElementById('backIcon');
    const text = document.getElementById('backText');
    
    // 🔥 CEK APAKAH SUDAH DALAM PROSES LOADING
    if (btn.disabled) {
        return; // Mencegah double click
    }
    
    // 🔥 DISABLE TOMBOL
    btn.disabled = true;
    btn.style.opacity = '0.7';
    btn.style.cursor = 'not-allowed';
    
    // 🔥 TAMPILKAN LOADING
    if (icon) {
        icon.className = 'fas fa-spinner fa-pulse';
        icon.style.pointerEvents = 'none';
    }
    if (text) {
        text.textContent = 'Loading...';
        text.style.pointerEvents = 'none';
    }
    
    // 🔥 TAMBAHKAN OVERLAY LOADING (opsional)
    showLoadingOverlay();
    
    // 🔥 REDIRECT KE DASHBOARD
    setTimeout(function() {
        if (typeof baseUrlDashboard !== 'undefined') {
            window.location.href = baseUrlDashboard + 'bd/dashboard';
        } else {
            window.location.href = '<?= base_url('bd/dashboard') ?>';
        }
    }, 300); // Delay 300ms agar loading terlihat
}

// ============================================================
// LOADING OVERLAY
// ============================================================
function showLoadingOverlay() {
    // Cek apakah overlay sudah ada
    let overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.style.display = 'flex';
        return;
    }
    
    // Buat overlay baru
    overlay = document.createElement('div');
    overlay.id = 'loadingOverlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.85);
        backdrop-filter: blur(8px);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 99999;
        transition: opacity 0.3s ease;
    `;
    
    overlay.innerHTML = `
        <div style="text-align: center;">
            <div style="width: 60px; height: 60px; border: 3px solid rgba(139, 92, 246, 0.2); border-top: 3px solid #8b5cf6; border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto;"></div>
            <p style="color: #e2f0e8; font-size: 16px; margin-top: 20px; font-weight: 500;">
                <i class="fas fa-arrow-left" style="color: #8b5cf6; margin-right: 10px;"></i>
                Kembali ke Dashboard...
            </p>
            <p style="color: #9aaebe; font-size: 12px; margin-top: 8px;">Mohon tunggu sebentar</p>
        </div>
        <style>
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>
    `;
    
    document.body.appendChild(overlay);
}

// ============================================================
// FUNGSI UNTUK MENUTUP LOADING OVERLAY (jika diperlukan)
// ============================================================
function hideLoadingOverlay() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
}

console.log(document.querySelector('.btn-back-dashboard'));
console.log(document.querySelector('.btn-back-dashboard')?.getAttribute('href'));

</script>