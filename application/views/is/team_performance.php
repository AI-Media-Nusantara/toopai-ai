<!-- file: application/views/is/team_performance.php -->
<style>
    .page-header {
        margin-bottom: 24px;
    }
    
    .page-title {
        font-size: 22px;
        font-weight: 700;
        background: linear-gradient(135deg, var(--purple), var(--cyan), var(--blue));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        margin-bottom: 6px;
    }
    
    .back-to-dashboard {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--purple);
        text-decoration: none;
        font-size: 13px;
        margin-bottom: 12px;
        transition: all 0.2s;
    }
    
    .back-to-dashboard:hover {
        color: var(--cyan);
        transform: translateX(-3px);
    }
    
    .filter-bar {
        margin-bottom: 24px;
        display: flex;
        justify-content: flex-end;
    }
    
    .date-filter {
        display: flex;
        align-items: center;
        gap: 8px;
        background: var(--bg-card);
        padding: 8px 16px;
        border-radius: 40px;
        border: 1px solid var(--border);
    }
    
    .date-input {
        background: transparent;
        border: none;
        color: var(--text-primary);
        font-size: 12px;
        padding: 6px 8px;
        outline: none;
    }
    
    .btn-filter {
        background: var(--purple-glow);
        border: 1px solid var(--purple);
        color: var(--purple);
        padding: 4px 12px;
        border-radius: 40px;
        cursor: pointer;
        font-size: 11px;
        transition: all 0.2s;
    }
    
    .btn-filter:hover {
        background: var(--purple);
        color: white;
    }
    
    .stats-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    
    .stat-card {
        background: var(--bg-card);
        border-radius: 20px;
        padding: 16px;
        text-align: center;
        border: 1px solid var(--border);
        transition: all 0.2s;
    }
    
    .stat-card:hover {
        border-color: var(--purple);
        transform: translateY(-2px);
    }
    
    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: #4ade80;
    }
    
    .stat-label {
        color: var(--text-muted);
        font-size: 11px;
        margin-top: 4px;
    }
    
    .team-table {
        width: 100%;
        border-collapse: collapse;
        background: var(--bg-card);
        border-radius: 20px;
        overflow: hidden;
    }
    
    .team-table th,
    .team-table td {
        padding: 14px 12px;
        text-align: left;
        border-bottom: 1px solid var(--border);
        font-size: 13px;
    }
    
    .team-table th {
        background: var(--bg-elevated);
        color: var(--purple);
        font-weight: 600;
    }
    
    .team-table tr:hover {
        background: var(--bg-elevated);
    }
    
    .rank-badge {
        display: inline-block;
        width: 28px;
        height: 28px;
        line-height: 28px;
        text-align: center;
        border-radius: 50%;
        font-size: 12px;
        font-weight: 600;
    }
    
    .rank-1 { background: linear-gradient(135deg, #ffd700, #ffb700); color: #0a0e1a; }
    .rank-2 { background: linear-gradient(135deg, #c0c0c0, #a8a8a8); color: #0a0e1a; }
    .rank-3 { background: linear-gradient(135deg, #cd7f32, #b8651a); color: white; }
    
    .progress-bar {
        width: 80px;
        height: 6px;
        background: var(--border);
        border-radius: 3px;
        overflow: hidden;
    }
    
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--purple), var(--blue));
        border-radius: 3px;
    }
    
    .gmv-cell {
        color: #4ade80;
        font-weight: 600;
    }
    
    .task-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 500;
    }
    
    .task-badge.scouting { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
    .task-badge.link { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
    .task-badge.sample { background: rgba(139, 92, 246, 0.15); color: #8b5cf6; }
    .task-badge.monitoring { background: rgba(16, 185, 129, 0.15); color: #10b981; }
    
    @media (max-width: 768px) {
        .team-table {
            font-size: 11px;
        }
        .team-table th,
        .team-table td {
            padding: 8px 6px;
        }
        .stats-summary {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
    }
    
    .date-input {
    background-color: #ffffff; /* Mengubah latar belakang input menjadi putih */
    color: #333333;            /* Warna teks di dalam input menjadi gelap agar terbaca */
    border: 1px solid #ccc;
    padding: 5px 10px;
    border-radius: 4px;
}

/* Ikon kalender tidak perlu di-invert jika background input sudah putih */
.date-input::-webkit-calendar-picker-indicator {
    cursor: pointer;
}
</style>

<div class="page-header">
    <div>
      
        <h1 class="page-title"><i class="fas fa-users"></i> Team Performance</h1>
        <p class="page-subtitle">Monitor your team's performance and task completion</p>
    </div>
</div>

<div class="filter-bar">
    <div class="date-filter">
        <label style="color: var(--text-muted); font-size: 11px;">Periode:</label>
        <input type="date" id="startDateFilter" class="date-input" value="<?= $start_date ?>">
        <span>s/d</span>
        <input type="date" id="endDateFilter" class="date-input" value="<?= $end_date ?>">
        <button id="applyFilterBtn" class="btn-filter"><i class="fas fa-calendar-alt"></i> Terapkan</button>
        <button id="resetFilterBtn" class="btn-filter"><i class="fas fa-undo-alt"></i> Hari Ini</button>
        <button id="weekFilterBtn" class="btn-filter"><i class="fas fa-calendar-week"></i> 7 Hari</button>
        <button id="monthFilterBtn" class="btn-filter"><i class="fas fa-calendar-alt"></i> 30 Hari</button>
    </div>
</div>

<div class="stats-summary">
    <div class="stat-card">
        <div class="stat-value"><?= number_format($team_summary['total_members']) ?></div>
        <div class="stat-label">Team Members</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">Rp <?= number_format($team_summary['total_gmv'], 0, ',', '.') ?></div>
        <div class="stat-label">Total GMV</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= number_format($team_summary['total_orders']) ?></div>
        <div class="stat-label">Total Orders</div>
    </div>
    <!--<div class="stat-card">-->
    <!--    <div class="stat-value">Rp <?= number_format($team_summary['total_commission'], 0, ',', '.') ?></div>-->
    <!--    <div class="stat-label">Total Commission</div>-->
    <!--</div>-->
</div>

<div style="overflow-x: auto;">
    <table class="team-table">
        <thead>
            <tr>
                <th>Rank</th>
                <th>Member</th>
                <th>Creators</th>
                <th>Active</th>
                <th>GMV</th>
                <th>Orders</th>
                
                
                <th>Progress</th>
                <th>Scouting</th>
                <th>Link</th>
                <th>Sample</th>
                <th>Monitoring</th>
          
        </thead>
        <tbody>
    <?php 
    $rank = 1; 
    foreach ($team_members as $member): 
        $is_unassigned = isset($member->is_unassigned) && $member->is_unassigned === true;
    ?>
    <tr <?= $is_unassigned ? 'style="background: rgba(239, 68, 68, 0.08); border-left: 4px solid #ef4444;"' : '' ?>>
        <td>
            <?php if ($is_unassigned): ?>
                <span style="display: inline-block; width: 28px; height: 28px; line-height: 28px; text-align: center; border-radius: 50%; font-size: 12px; background: rgba(239, 68, 68, 0.2); color: #ef4444;">
                    <i class="fas fa-exclamation-triangle"></i>
                </span>
            <?php else: ?>
                <span class="rank-badge <?= $rank == 1 ? 'rank-1' : ($rank == 2 ? 'rank-2' : ($rank == 3 ? 'rank-3' : '')) ?>">
                    <?= $rank++ ?>
                </span>
            <?php endif; ?>
        </td>
        <td>
            <?php if ($is_unassigned): ?>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i>
                    <strong style="color: #ef4444;"><?= htmlspecialchars($member->full_name) ?></strong>
                </div>
                <div style="font-size: 10px; color: #f59e0b;">
                    <i class="fas fa-info-circle"></i> Creator tanpa CA - Perlu Assignment
                </div>
            <?php else: ?>
                <strong><?= htmlspecialchars($member->full_name ?: $member->username) ?></strong>
                <div style="font-size: 10px; color: var(--text-muted);">@<?= htmlspecialchars($member->username) ?></div>
            <?php endif; ?>
        </td>
        <td><?= number_format($member->total_creators) ?></td>
        <td><?= number_format($member->active_creators) ?></td>
        <td class="gmv-cell" style="<?= $is_unassigned ? 'color: #ef4444;' : '' ?>">
            Rp <?= number_format($member->total_gmv, 0, ',', '.') ?>
        </td>
        <td><?= number_format($member->total_orders) ?></td>
        <td>
            <?php if ($is_unassigned): ?>
                <span style="background: rgba(239, 68, 68, 0.15); padding: 2px 10px; border-radius: 12px; color: #ef4444; font-size: 10px;">
                    ⚠️ Perlu CA
                </span>
            <?php else: ?>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $member->progress ?>%"></div>
                </div>
                <div style="font-size: 10px; margin-top: 4px;"><?= $member->progress ?>%</div>
            <?php endif; ?>
        </td>
        <td><span class="task-badge scouting"><?= number_format($member->task_stats['scouting']) ?></span></td>
        <td><span class="task-badge link"><?= number_format($member->task_stats['link_swapping']) ?></span></td>
        <td><span class="task-badge sample"><?= number_format($member->task_stats['sample_sent']) ?></span></td>
        <td><span class="task-badge monitoring"><?= number_format($member->task_stats['monitoring']) ?></span></td>
    </tr>
    <?php endforeach; ?>
</tbody>
    </table>
</div>

<script>
const baseUrlIS = '<?= base_url() ?>';
const startDateInput = document.getElementById('startDateFilter');
const endDateInput = document.getElementById('endDateFilter');
const applyFilterBtn = document.getElementById('applyFilterBtn');
const resetFilterBtn = document.getElementById('resetFilterBtn');
const weekFilterBtn = document.getElementById('weekFilterBtn');
const monthFilterBtn = document.getElementById('monthFilterBtn');

function showToastGlobal(message, type = 'success') {
    let toast = document.getElementById('globalToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'globalToast';
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: ${type === 'success' ? '#10b981' : '#ef4444'};
            color: white;
            padding: 12px 20px;
            border-radius: 12px;
            font-size: 13px;
            z-index: 9999;
            animation: slideIn 0.3s ease;
        `;
        document.body.appendChild(toast);
        
        if (!document.querySelector('#toastStyle')) {
            const style = document.createElement('style');
            style.id = 'toastStyle';
            style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes slideOut {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    toast.textContent = message;
    toast.style.background = type === 'success' ? '#10b981' : (type === 'error' ? '#ef4444' : '#f59e0b');
    toast.style.display = 'block';
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            toast.style.display = 'none';
            toast.style.animation = 'slideIn 0.3s ease';
        }, 300);
    }, 3000);
}

function applyDateFilter() {
    const startDate = startDateInput.value;
    const endDate = endDateInput.value;
    
    if (!startDate || !endDate) {
        showToastGlobal('Pilih tanggal terlebih dahulu', 'error');
        return;
    }
    
    if (startDate > endDate) {
        showToastGlobal('Tanggal mulai tidak boleh lebih besar dari tanggal akhir', 'error');
        return;
    }
    
    window.location.href = baseUrlIS + `is/team_performance?start_date=${startDate}&end_date=${endDate}`;
}

function resetDateFilter() {
    const today = new Date().toISOString().split('T')[0];
    window.location.href = baseUrlIS + `is/team_performance?start_date=${today}&end_date=${today}`;
}

function setWeekFilter() {
    const today = new Date();
    const weekAgo = new Date(today);
    weekAgo.setDate(today.getDate() - 6);
    
    const endDate = today.toISOString().split('T')[0];
    const startDate = weekAgo.toISOString().split('T')[0];
    
    window.location.href = baseUrlIS + `is/team_performance?start_date=${startDate}&end_date=${endDate}`;
}

function setMonthFilter() {
    const today = new Date();
    const monthAgo = new Date(today);
    monthAgo.setDate(today.getDate() - 29);
    
    const endDate = today.toISOString().split('T')[0];
    const startDate = monthAgo.toISOString().split('T')[0];
    
    window.location.href = baseUrlIS + `is/team_performance?start_date=${startDate}&end_date=${endDate}`;
}

if (applyFilterBtn) applyFilterBtn.addEventListener('click', applyDateFilter);
if (resetFilterBtn) resetFilterBtn.addEventListener('click', resetDateFilter);
if (weekFilterBtn) weekFilterBtn.addEventListener('click', setWeekFilter);
if (monthFilterBtn) monthFilterBtn.addEventListener('click', setMonthFilter);
</script>