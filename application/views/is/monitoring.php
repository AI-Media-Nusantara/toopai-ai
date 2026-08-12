<?php /* file: application/views/is/monitoring.php */ ?>
<style>
/* ===== MONITORING PAGE STYLES ===== */
:root {
    --mon-green: #4ade80;
    --mon-purple: #8b5cf6;
    --mon-blue: #3b82f6;
    --mon-yellow: #f59e0b;
    --mon-red: #ef4444;
    --mon-bg: #0f0f1a;
    --mon-card: #111827;
    --mon-elevated: #1e293b;
    --mon-border: rgba(255,255,255,0.08);
    --mon-text: #e2e8f0;
    --mon-muted: #94a3b8;
}

.mon-page { max-width: 1400px; margin: 0 auto; padding: 20px 16px 60px; }

/* Header */
.mon-header {
    background: linear-gradient(135deg, #13111f, #0f0f1a);
    border: 1px solid var(--mon-border);
    border-radius: 20px;
    padding: 20px 24px;
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}
.mon-header-title h1 {
    font-size: 20px;
    font-weight: 700;
    background: linear-gradient(135deg, var(--mon-green), var(--mon-blue));
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    margin: 0 0 4px;
}
.mon-header-title p { font-size: 12px; color: var(--mon-muted); margin: 0; }
.mon-header-actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
.mon-btn {
    padding: 8px 18px;
    border-radius: 40px;
    border: none;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.mon-btn-primary { background: linear-gradient(135deg, var(--mon-green), #22c55e); color: #0a0e17; }
.mon-btn-outline { background: transparent; border: 1px solid var(--mon-green); color: var(--mon-green); }
.mon-btn-outline:hover { background: rgba(74,222,128,0.1); }
.mon-btn-sm { padding: 5px 12px; font-size: 11px; }
.mon-btn-purple { background: linear-gradient(135deg, var(--mon-purple), #7c3aed); color: #fff; }
.mon-btn-blue { background: linear-gradient(135deg, var(--mon-blue), #2563eb); color: #fff; }

/* Filter Bar */
.mon-filter-bar {
    background: var(--mon-card);
    border: 1px solid var(--mon-border);
    border-radius: 16px;
    padding: 14px 20px;
    margin-bottom: 20px;
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}
.mon-search-box {
    flex: 1;
    min-width: 200px;
    background: var(--mon-elevated);
    border: 1px solid var(--mon-border);
    border-radius: 40px;
    padding: 8px 16px;
    color: var(--mon-text);
    font-size: 13px;
    outline: none;
}
.mon-search-box:focus { border-color: var(--mon-green); }
.mon-filter-select {
    background: var(--mon-elevated);
    border: 1px solid var(--mon-border);
    border-radius: 40px;
    padding: 8px 14px;
    color: var(--mon-text);
    font-size: 12px;
    outline: none;
    cursor: pointer;
}

/* Stats Row */
.mon-stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 14px;
    margin-bottom: 24px;
}
.mon-stat-card {
    background: var(--mon-card);
    border: 1px solid var(--mon-border);
    border-radius: 16px;
    padding: 16px 18px;
    transition: 0.2s;
}
.mon-stat-card:hover { border-color: var(--mon-green); transform: translateY(-2px); }
.mon-stat-label { font-size: 10px; color: var(--mon-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
.mon-stat-val { font-size: 22px; font-weight: 700; color: var(--mon-green); }
.mon-stat-sub { font-size: 10px; color: var(--mon-muted); margin-top: 2px; }

/* Creator Table */
.mon-table-wrap {
    background: var(--mon-card);
    border: 1px solid var(--mon-border);
    border-radius: 20px;
    overflow: hidden;
}
.mon-table-header {
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--mon-border);
    flex-wrap: wrap;
    gap: 10px;
}
.mon-table-header h3 { font-size: 14px; font-weight: 600; color: var(--mon-text); margin: 0; }
.mon-table { width: 100%; border-collapse: collapse; }
.mon-table th {
    padding: 10px 14px;
    text-align: left;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--mon-muted);
    background: rgba(255,255,255,0.03);
    border-bottom: 1px solid var(--mon-border);
}
.mon-table td {
    padding: 12px 14px;
    border-bottom: 1px solid var(--mon-border);
    font-size: 12px;
    color: var(--mon-text);
    vertical-align: middle;
}
.mon-table tr:last-child td { border-bottom: none; }
.mon-table tr:hover td { background: rgba(74,222,128,0.03); }

/* Creator Name Cell */
.mon-creator-cell { display: flex; align-items: center; gap: 10px; }
.mon-avatar {
    width: 36px; height: 36px; border-radius: 50%;
    background: linear-gradient(135deg, var(--mon-purple), var(--mon-blue));
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 13px; color: #fff; flex-shrink: 0;
    overflow: hidden;
}
.mon-avatar img { width: 100%; height: 100%; object-fit: cover; }
.mon-creator-name { font-weight: 600; font-size: 13px; color: var(--mon-text); }
.mon-creator-brand { font-size: 10px; color: var(--mon-muted); }

/* GMV Badge */
.mon-gmv-link {
    color: var(--mon-green);
    font-weight: 700;
    cursor: pointer;
    text-decoration: underline;
    font-size: 13px;
}
.mon-gmv-link:hover { color: #22c55e; }

/* Status Badge */
.mon-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 600;
}
.mon-badge-green { background: rgba(74,222,128,0.15); color: var(--mon-green); }
.mon-badge-yellow { background: rgba(245,158,11,0.15); color: var(--mon-yellow); }
.mon-badge-purple { background: rgba(139,92,246,0.15); color: var(--mon-purple); }
.mon-badge-red { background: rgba(239,68,68,0.15); color: var(--mon-red); }
.mon-badge-blue { background: rgba(59,130,246,0.15); color: var(--mon-blue); }

/* Action Buttons */
.mon-action-btns { display: flex; gap: 6px; flex-wrap: wrap; }

/* =================================================================
   MODAL / DRAWER — Detail Monitoring
   ================================================================= */
.mon-modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.85);
    backdrop-filter: blur(8px);
    z-index: 3000;
    display: flex; align-items: center; justify-content: center;
    visibility: hidden; opacity: 0;
    transition: 0.25s;
}
.mon-modal-overlay.active { visibility: visible; opacity: 1; }

.mon-modal {
    background: var(--mon-card);
    border: 1px solid var(--mon-border);
    border-radius: 24px;
    width: 95%;
    max-width: 860px;
    max-height: 90vh;
    overflow-y: auto;
    padding: 0;
    box-shadow: 0 25px 60px rgba(0,0,0,0.6);
}

.mon-modal-head {
    padding: 20px 24px 16px;
    border-bottom: 1px solid var(--mon-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    background: var(--mon-card);
    z-index: 1;
}
.mon-modal-head h3 { font-size: 16px; font-weight: 700; color: var(--mon-text); margin: 0; }
.mon-modal-close { background: none; border: none; color: var(--mon-muted); font-size: 22px; cursor: pointer; padding: 4px; }
.mon-modal-close:hover { color: var(--mon-text); }

/* Sub-tabs dalam modal */
.mon-subtabs {
    display: flex;
    gap: 4px;
    padding: 12px 24px 0;
    border-bottom: 1px solid var(--mon-border);
    overflow-x: auto;
}
.mon-subtab-btn {
    padding: 8px 16px;
    border: none; border-bottom: 2px solid transparent;
    background: transparent;
    color: var(--mon-muted);
    font-size: 12px; font-weight: 600;
    cursor: pointer;
    transition: 0.2s;
    white-space: nowrap;
}
.mon-subtab-btn.active { color: var(--mon-green); border-bottom-color: var(--mon-green); }
.mon-subtab-content { display: none; padding: 20px 24px; }
.mon-subtab-content.active { display: block; }

/* Inside modal: Mini table */
.mon-mini-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.mon-mini-table th { padding: 8px 10px; text-align: left; font-size: 10px; color: var(--mon-muted); background: rgba(255,255,255,0.03); border-bottom: 1px solid var(--mon-border); }
.mon-mini-table td { padding: 10px 10px; border-bottom: 1px solid var(--mon-border); color: var(--mon-text); vertical-align: middle; }
.mon-mini-table tr:last-child td { border-bottom: none; }
.mon-total-row td { font-weight: 700; color: var(--mon-green); background: rgba(74,222,128,0.05); }

/* Summary boxes in modal */
.mon-summary-boxes { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; margin-bottom: 20px; }
.mon-summary-box {
    background: var(--mon-elevated);
    border: 1px solid var(--mon-border);
    border-radius: 14px;
    padding: 14px;
    text-align: center;
}
.mon-summary-box .val { font-size: 24px; font-weight: 700; }
.mon-summary-box .lbl { font-size: 10px; color: var(--mon-muted); margin-top: 4px; }
.mon-summary-box.green .val { color: var(--mon-green); }
.mon-summary-box.yellow .val { color: var(--mon-yellow); }
.mon-summary-box.red .val { color: var(--mon-red); }

/* Sample willing modal */
.mon-willing-modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.9);
    z-index: 4000;
    display: flex; align-items: center; justify-content: center;
    visibility: hidden; opacity: 0;
    transition: 0.2s;
}
.mon-willing-modal-overlay.active { visibility: visible; opacity: 1; }
.mon-willing-modal {
    background: var(--mon-card);
    border: 1px solid rgba(74,222,128,0.3);
    border-radius: 24px;
    padding: 32px;
    width: 95%; max-width: 480px;
    text-align: center;
}
.mon-willing-modal h3 { font-size: 17px; font-weight: 700; margin-bottom: 12px; color: var(--mon-text); }
.mon-willing-modal p { font-size: 13px; color: var(--mon-muted); margin-bottom: 24px; }
.mon-willing-btns { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }

/* Sample Recommendation Modal */
.mon-rec-modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.9);
    z-index: 4000;
    display: flex; align-items: center; justify-content: center;
    visibility: hidden; opacity: 0;
    transition: 0.2s;
}
.mon-rec-modal-overlay.active { visibility: visible; opacity: 1; }
.mon-rec-modal {
    background: var(--mon-card);
    border: 1px solid rgba(139,92,246,0.3);
    border-radius: 24px;
    width: 95%; max-width: 720px;
    max-height: 85vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,0.5);
}
.mon-rec-modal-head {
    padding: 20px 24px 16px;
    border-bottom: 1px solid var(--mon-border);
    position: sticky; top: 0;
    background: var(--mon-card);
    z-index: 1;
}
.mon-rec-modal-head h3 { font-size: 16px; font-weight: 700; color: var(--mon-text); margin: 0 0 6px; }
.mon-rec-modal-head p { font-size: 12px; color: var(--mon-muted); margin: 0; }
.mon-rec-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 14px; padding: 20px 24px; }
.mon-rec-card {
    background: var(--mon-elevated);
    border: 1px solid var(--mon-border);
    border-radius: 16px;
    padding: 14px;
    cursor: pointer;
    transition: 0.2s;
    position: relative;
}
.mon-rec-card:hover { border-color: var(--mon-purple); transform: translateY(-2px); }
.mon-rec-card.selected { border-color: var(--mon-green); background: rgba(74,222,128,0.05); }
.mon-rec-card-img { width: 100%; height: 100px; object-fit: cover; border-radius: 10px; margin-bottom: 10px; background: #1e293b; }
.mon-rec-card-name { font-size: 12px; font-weight: 600; color: var(--mon-text); margin-bottom: 6px; line-height: 1.4; }
.mon-rec-card-brand { font-size: 10px; color: var(--mon-muted); margin-bottom: 6px; }
.mon-rec-card-meta { display: flex; justify-content: space-between; font-size: 10px; }
.mon-rec-check {
    position: absolute; top: 10px; right: 10px;
    width: 22px; height: 22px; border-radius: 50%;
    background: var(--mon-green);
    display: none; align-items: center; justify-content: center;
    color: #0a0e17; font-size: 12px; font-weight: 700;
}
.mon-rec-card.selected .mon-rec-check { display: flex; }
.mon-rec-actions {
    padding: 16px 24px;
    border-top: 1px solid var(--mon-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    bottom: 0;
    background: var(--mon-card);
    flex-wrap: wrap;
    gap: 10px;
}
.mon-selected-count { font-size: 12px; color: var(--mon-muted); }
.mon-selected-count strong { color: var(--mon-green); }

/* Video input form */
.mon-form-group { margin-bottom: 16px; }
.mon-form-label { font-size: 12px; font-weight: 600; color: var(--mon-muted); margin-bottom: 6px; display: block; }
.mon-form-input {
    width: 100%; padding: 10px 14px;
    background: var(--mon-elevated);
    border: 1px solid var(--mon-border);
    border-radius: 12px;
    color: var(--mon-text);
    font-size: 13px;
    outline: none;
    box-sizing: border-box;
}
.mon-form-input:focus { border-color: var(--mon-green); }

/* Video list items */
.mon-video-item {
    background: var(--mon-elevated);
    border: 1px solid var(--mon-border);
    border-radius: 14px;
    padding: 12px 16px;
    margin-bottom: 10px;
    display: flex;
    gap: 14px;
    align-items: flex-start;
}
.mon-video-thumb {
    width: 56px; height: 56px; border-radius: 10px;
    background: #1e293b;
    flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    color: var(--mon-muted); font-size: 20px;
}
.mon-video-info { flex: 1; }
.mon-video-title { font-size: 12px; font-weight: 600; color: var(--mon-text); margin-bottom: 4px; }
.mon-video-meta { font-size: 10px; color: var(--mon-muted); }
.mon-video-link { color: var(--mon-blue); font-size: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 3px; margin-top: 4px; }
.mon-video-link:hover { text-decoration: underline; }
.mon-badge-api { background: rgba(59,130,246,0.15); color: var(--mon-blue); font-size: 9px; padding: 1px 6px; border-radius: 10px; }
.mon-badge-manual { background: rgba(74,222,128,0.15); color: var(--mon-green); font-size: 9px; padding: 1px 6px; border-radius: 10px; }

/* Keranjang kuning items */
.mon-keranjang-item {
    background: var(--mon-elevated);
    border: 1px solid var(--mon-border);
    border-radius: 14px;
    padding: 12px 16px;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}
.mon-keranjang-name { font-size: 13px; font-weight: 600; color: var(--mon-text); }
.mon-keranjang-meta { font-size: 10px; color: var(--mon-muted); margin-top: 2px; }
.mon-keranjang-gmv { font-size: 14px; font-weight: 700; color: var(--mon-green); }

/* Empty state */
.mon-empty { text-align: center; padding: 40px 20px; color: var(--mon-muted); font-size: 13px; }
.mon-empty i { font-size: 36px; display: block; margin-bottom: 10px; }

/* Loading spinner */
.mon-loading { text-align: center; padding: 30px; }
.mon-spinner { display: inline-block; width: 28px; height: 28px; border: 3px solid rgba(74,222,128,0.2); border-top-color: var(--mon-green); border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Toast */
.mon-toast-wrap { position: fixed; bottom: 80px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 8px; pointer-events: none; }
.mon-toast {
    background: var(--mon-card);
    border: 1px solid var(--mon-border);
    border-radius: 14px;
    padding: 12px 18px;
    font-size: 13px;
    color: var(--mon-text);
    animation: slideIn 0.3s ease;
    pointer-events: auto;
    min-width: 240px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.4);
}
.mon-toast.success { border-color: var(--mon-green); }
.mon-toast.error { border-color: var(--mon-red); }
@keyframes slideIn { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }

@media (max-width: 768px) {
    .mon-table-wrap { overflow-x: auto; }
    .mon-table { min-width: 700px; }
    .mon-stats-row { grid-template-columns: repeat(2, 1fr); }
}
</style>

<?php
$total_gmv_all  = array_sum(array_column($creators, 'total_gmv_30d'));
$total_sample   = array_sum(array_column($creators, 'sample_count'));
$total_video    = array_sum(array_column($creators, 'video_count'));
$has_order_cnt  = count(array_filter($creators, fn($c) => $c->has_orders));

function fmt_rp($val) {
    if ($val >= 1000000) return 'Rp ' . number_format($val/1000000, 1) . 'Jt';
    if ($val >= 1000) return 'Rp ' . number_format($val/1000, 0) . 'K';
    return 'Rp ' . number_format($val, 0);
}
?>

<div class="mon-page">

    <!-- HEADER -->
    <div class="mon-header">
        <div class="mon-header-title">
            <h1>📊 Monitoring Creator</h1>
            <p><?= count($creators) ?> creator aktif <?= $is_supervisor ? '(Semua IS)' : '(Handler saya)' ?></p>
        </div>
        <div class="mon-header-actions">
            <a href="<?= base_url('is/dashboard') ?>" class="mon-btn mon-btn-outline">
                <i class="ri-arrow-left-line"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- STATS -->
    <div class="mon-stats-row">
        <div class="mon-stat-card">
            <div class="mon-stat-label">Total Creator</div>
            <div class="mon-stat-val"><?= count($creators) ?></div>
            <div class="mon-stat-sub">ACTIVE & SAMPLE_SENT</div>
        </div>
        <div class="mon-stat-card">
            <div class="mon-stat-label">GMV 30 Hari</div>
            <div class="mon-stat-val" style="font-size:18px"><?= fmt_rp($total_gmv_all) ?></div>
            <div class="mon-stat-sub">Semua creator</div>
        </div>
        <div class="mon-stat-card">
            <div class="mon-stat-label">Sample Terkirim</div>
            <div class="mon-stat-val" style="color:var(--mon-yellow)"><?= $total_sample ?></div>
            <div class="mon-stat-sub">Total produk sample</div>
        </div>
        <div class="mon-stat-card">
            <div class="mon-stat-label">Creator Punya Video</div>
            <div class="mon-stat-val" style="color:var(--mon-purple)"><?= $total_video > 0 ? $total_video : '-' ?></div>
            <div class="mon-stat-sub">Total konten</div>
        </div>
        <div class="mon-stat-card">
            <div class="mon-stat-label">Keranjang Kuning</div>
            <div class="mon-stat-val" style="color:var(--mon-blue)"><?= $has_order_cnt ?></div>
            <div class="mon-stat-sub">Creator sudah bertransaksi</div>
        </div>
    </div>

    <!-- FILTER -->
    <div class="mon-filter-bar">
        <input type="text" class="mon-search-box" id="monSearchBox" placeholder="🔍 Cari creator, brand, kategori...">
        <select class="mon-filter-select" id="monFilterStatus">
            <option value="">Semua Status</option>
            <option value="ACTIVE">ACTIVE</option>
            <option value="SAMPLE_SENT">SAMPLE_SENT</option>
        </select>
        <select class="mon-filter-select" id="monFilterOrder">
            <option value="">Semua</option>
            <option value="with_order">Ada Transaksi</option>
            <option value="no_order">Belum Transaksi</option>
        </select>
    </div>

    <!-- TABLE -->
    <div class="mon-table-wrap">
        <div class="mon-table-header">
            <h3>Daftar Creator</h3>
            <span style="font-size:11px;color:var(--mon-muted)"><?= count($creators) ?> creator</span>
        </div>
        <table class="mon-table" id="monCreatorTable">
            <thead>
                <tr>
                    <th>Creator</th>
                    <th>Status</th>
                    <th>GMV 30d</th>
                    <th>Keranjang</th>
                    <th>Sample</th>
                    <th>Video</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($creators)): ?>
                <tr><td colspan="7"><div class="mon-empty"><i class="ri-user-line"></i>Belum ada creator aktif</div></td></tr>
            <?php else: ?>
            <?php foreach ($creators as $c): ?>
            <tr class="mon-creator-row"
                data-username="<?= htmlspecialchars($c->username) ?>"
                data-brand="<?= htmlspecialchars($c->brand_name ?? '') ?>"
                data-status="<?= $c->status ?>"
                data-has-order="<?= $c->has_orders ? '1' : '0' ?>">
                <td>
                    <div class="mon-creator-cell">
                        <div class="mon-avatar">
                            <?php if (!empty($c->avatar_url)): ?>
                                <img src="<?= htmlspecialchars($c->avatar_url) ?>" alt="">
                            <?php else: ?>
                                <?= strtoupper(substr($c->username, 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div class="mon-creator-name">@<?= htmlspecialchars($c->username) ?></div>
                            <div class="mon-creator-brand">
                                <?= htmlspecialchars($c->full_name ?? '') ?>
                                <?php if ($c->brand_name): ?> · <?= htmlspecialchars($c->brand_name) ?><?php endif; ?>
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    <?php if ($c->status === 'ACTIVE'): ?>
                        <span class="mon-badge mon-badge-green">● ACTIVE</span>
                    <?php else: ?>
                        <span class="mon-badge mon-badge-yellow">📦 SAMPLE_SENT</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($c->total_gmv_30d > 0): ?>
                        <span class="mon-gmv-link" onclick="openGmvBreakdown(<?= $c->id ?>, '<?= htmlspecialchars($c->username) ?>')">
                            <?= fmt_rp($c->total_gmv_30d) ?>
                        </span>
                    <?php else: ?>
                        <span style="color:var(--mon-muted);font-size:11px">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($c->has_orders): ?>
                        <span class="mon-badge mon-badge-green">✅ Ada Transaksi</span>
                    <?php else: ?>
                        <span class="mon-badge mon-badge-red">⏳ Belum</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span style="font-weight:600;color:<?= $c->sample_count > 0 ? 'var(--mon-yellow)' : 'var(--mon-muted)' ?>">
                        <?= $c->sample_count ?> produk
                    </span>
                </td>
                <td>
                    <span style="font-weight:600;color:<?= $c->video_count > 0 ? 'var(--mon-purple)' : 'var(--mon-muted)' ?>">
                        <?= $c->video_count ?> video
                    </span>
                </td>
                <td>
                    <div class="mon-action-btns">
                        <button class="mon-btn mon-btn-outline mon-btn-sm"
                                onclick="openMonitoringDetail(<?= $c->id ?>, '<?= htmlspecialchars($c->username) ?>')">
                            <i class="ri-bar-chart-line"></i> Detail
                        </button>
                        <?php if ($c->has_orders && $c->status === 'ACTIVE'): ?>
                        <button class="mon-btn mon-btn-purple mon-btn-sm"
                                onclick="openWillingModal(<?= $c->id ?>, '<?= htmlspecialchars($c->username) ?>')">
                            <i class="ri-gift-line"></i> Sample
                        </button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</div><!-- .mon-page -->

<!-- ====================================================
     MODAL: DETAIL MONITORING
     ==================================================== -->
<div class="mon-modal-overlay" id="monDetailOverlay">
    <div class="mon-modal">
        <div class="mon-modal-head">
            <h3 id="monDetailTitle">Detail Monitoring</h3>
            <button class="mon-modal-close" onclick="closeMonitoringDetail()">✕</button>
        </div>
        <div class="mon-subtabs">
            <button class="mon-subtab-btn active" onclick="switchSubtab(this,'tab-video')">🎬 Video</button>
            <button class="mon-subtab-btn" onclick="switchSubtab(this,'tab-gmv')">💰 GMV</button>
            <button class="mon-subtab-btn" onclick="switchSubtab(this,'tab-keranjang')">🛒 Keranjang</button>
            <button class="mon-subtab-btn" onclick="switchSubtab(this,'tab-sample')">📦 Sample</button>
        </div>

        <!-- TAB: VIDEO -->
        <div class="mon-subtab-content active" id="tab-video">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px">
                <span style="font-size:12px;color:var(--mon-muted)">Video yang sudah diposting creator</span>
                <button class="mon-btn mon-btn-outline mon-btn-sm" onclick="openAddVideoForm()">
                    <i class="ri-add-line"></i> Tambah Video Manual
                </button>
            </div>
            <div id="videoListContent"><div class="mon-loading"><div class="mon-spinner"></div></div></div>
        </div>

        <!-- TAB: GMV -->
        <div class="mon-subtab-content" id="tab-gmv">
            <div id="gmvContent"><div class="mon-loading"><div class="mon-spinner"></div></div></div>
        </div>

        <!-- TAB: KERANJANG KUNING -->
        <div class="mon-subtab-content" id="tab-keranjang">
            <div id="keranjangContent"><div class="mon-loading"><div class="mon-spinner"></div></div></div>
        </div>

        <!-- TAB: SAMPLE MONITORING -->
        <div class="mon-subtab-content" id="tab-sample">
            <div id="sampleContent"><div class="mon-loading"><div class="mon-spinner"></div></div></div>
        </div>
    </div>
</div>

<!-- MODAL: GMV BREAKDOWN POPUP (dari klik GMV di tabel) -->
<div class="mon-modal-overlay" id="monGmvOverlay">
    <div class="mon-modal" style="max-width:600px">
        <div class="mon-modal-head">
            <h3 id="gmvPopupTitle">GMV Breakdown</h3>
            <button class="mon-modal-close" onclick="closeGmvPopup()">✕</button>
        </div>
        <div style="padding:20px 24px" id="gmvPopupContent">
            <div class="mon-loading"><div class="mon-spinner"></div></div>
        </div>
    </div>
</div>

<!-- MODAL: KONFIRMASI KESEDIAAN SAMPLE -->
<div class="mon-willing-modal-overlay" id="willingModalOverlay">
    <div class="mon-willing-modal">
        <div style="font-size:40px;margin-bottom:12px">🎁</div>
        <h3>Konfirmasi Kesediaan Sample</h3>
        <p>Apakah creator <strong id="willingCreatorName"></strong> bersedia menerima sample produk?</p>
        <div class="mon-willing-btns">
            <button class="mon-btn mon-btn-primary" onclick="submitWilling(1)">
                ✅ Ya, Bersedia
            </button>
            <button class="mon-btn mon-btn-outline" style="border-color:var(--mon-red);color:var(--mon-red)" onclick="submitWilling(0)">
                ❌ Tidak Bersedia
            </button>
        </div>
        <div style="margin-top:16px">
            <button class="mon-btn mon-btn-outline mon-btn-sm" onclick="closeWillingModal()">Batal</button>
        </div>
        <div style="margin-top:14px">
            <input type="text" class="mon-form-input" id="willingNotes" placeholder="Catatan (opsional)..." style="font-size:12px">
        </div>
    </div>
</div>

<!-- MODAL: REKOMENDASI PRODUK SAMPLE -->
<div class="mon-rec-modal-overlay" id="recModalOverlay">
    <div class="mon-rec-modal">
        <div class="mon-rec-modal-head">
            <div style="display:flex;justify-content:space-between;align-items:flex-start">
                <div>
                    <h3>🎯 Pilih Produk Sample</h3>
                    <p>Rekomendasi berdasarkan kategori creator, dari brand berbeda</p>
                </div>
                <button class="mon-modal-close" onclick="closeRecModal()" style="background:none;border:none;color:var(--mon-muted);font-size:22px;cursor:pointer">✕</button>
            </div>
            <div style="margin-top:12px;display:flex;gap:10px;flex-wrap:wrap">
                <span id="recCategoryBadge" style="font-size:11px;color:var(--mon-muted)"></span>
                <span id="recBrandBadge" style="font-size:11px;color:var(--mon-muted)"></span>
            </div>
        </div>
        <div id="recGridContent" class="mon-rec-grid">
            <div class="mon-loading" style="grid-column:1/-1"><div class="mon-spinner"></div></div>
        </div>
        <div class="mon-rec-actions">
            <span class="mon-selected-count">Dipilih: <strong id="recSelectedCount">0</strong> produk</span>
            <div style="display:flex;flex-direction:column;gap:10px;align-items:flex-end">
                <!-- Pilihan metode pengiriman -->
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                    <span style="font-size:11px;color:var(--mon-muted);font-weight:600;">Metode Pengiriman:</span>
                    <label style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--mon-text);cursor:pointer">
                        <input type="radio" name="recDeliveryMethod" value="manual" checked onchange="onDeliveryMethodChange(this.value)" style="accent-color:var(--mon-green)">
                        Manual
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--mon-text);cursor:pointer">
                        <input type="radio" name="recDeliveryMethod" value="system" onchange="onDeliveryMethodChange(this.value)" style="accent-color:var(--mon-blue)">
                        By System (TAP)
                    </label>
                </div>
                <!-- Input TAP Request ID — hanya muncul jika By System -->
                <div id="tapRequestIdWrap" style="display:none;width:100%">
                    <input type="text" id="tapRequestIdInput" class="mon-form-input"
                        placeholder="TAP Request ID (dari TAP Backend)"
                        style="font-size:12px;max-width:340px">
                    <div style="font-size:10px;color:var(--mon-muted);margin-top:4px">
                        ID pengajuan sample dari sistem TAP Backend
                    </div>
                </div>
                <div style="display:flex;gap:10px">
                    <button class="mon-btn mon-btn-outline mon-btn-sm" onclick="closeRecModal()">Batal</button>
                    <button class="mon-btn mon-btn-primary" onclick="confirmSampleDelivery()">
                        <i class="ri-send-plane-line"></i> Konfirmasi Pengiriman
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FORM: Tambah Video Manual (inline dalam modal detail) -->
<div class="mon-willing-modal-overlay" id="addVideoOverlay">
    <div class="mon-willing-modal" style="text-align:left;max-width:500px">
        <h3 style="margin-bottom:20px">🎬 Tambah Video Manual</h3>
        <div class="mon-form-group">
            <label class="mon-form-label">Link Video TikTok *</label>
            <input type="url" class="mon-form-input" id="videoUrlInput" placeholder="https://www.tiktok.com/@username/video/...">
        </div>
        <div class="mon-form-group">
            <label class="mon-form-label">Nama Produk (opsional)</label>
            <input type="text" class="mon-form-input" id="videoProdNameInput" placeholder="Nama produk yang dipromosikan">
        </div>
        <div class="mon-form-group">
            <label class="mon-form-label">Tanggal Post</label>
            <input type="date" class="mon-form-input" id="videoDateInput" value="<?= date('Y-m-d') ?>">
        </div>
        <div style="display:flex;gap:10px;margin-top:20px">
            <button class="mon-btn mon-btn-primary" onclick="submitVideoManual()">Simpan</button>
            <button class="mon-btn mon-btn-outline" onclick="closeAddVideoForm()">Batal</button>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="mon-toast-wrap" id="monToastWrap"></div>

<script>
/* ============================================================ */
/* MONITORING PAGE — JAVASCRIPT                                 */
/* ============================================================ */

const BASE_URL = '<?= base_url() ?>';

let currentCreatorId   = null;
let currentCreatorName = null;
let monitoringData     = null;
let selectedRecProducts = [];

// -------- FILTER TABLE --------
document.getElementById('monSearchBox').addEventListener('input', filterTable);
document.getElementById('monFilterStatus').addEventListener('change', filterTable);
document.getElementById('monFilterOrder').addEventListener('change', filterTable);

function filterTable() {
    const kw       = document.getElementById('monSearchBox').value.toLowerCase();
    const status   = document.getElementById('monFilterStatus').value;
    const orderF   = document.getElementById('monFilterOrder').value;
    const rows     = document.querySelectorAll('.mon-creator-row');
    rows.forEach(row => {
        const name    = (row.dataset.username + ' ' + row.dataset.brand).toLowerCase();
        const st      = row.dataset.status;
        const hasOrd  = row.dataset.hasOrder === '1';
        let show = true;
        if (kw && !name.includes(kw)) show = false;
        if (status && st !== status) show = false;
        if (orderF === 'with_order' && !hasOrd) show = false;
        if (orderF === 'no_order' && hasOrd) show = false;
        row.style.display = show ? '' : 'none';
    });
}

// -------- OPEN DETAIL MONITORING --------
function openMonitoringDetail(creatorId, username) {
    currentCreatorId   = creatorId;
    currentCreatorName = username;
    document.getElementById('monDetailTitle').textContent = '📊 @' + username;
    document.getElementById('monDetailOverlay').classList.add('active');
    loadMonitoringDetail();
}

function closeMonitoringDetail() {
    document.getElementById('monDetailOverlay').classList.remove('active');
}

function switchSubtab(btn, tabId) {
    document.querySelectorAll('.mon-subtab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.mon-subtab-content').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(tabId).classList.add('active');
}

function loadMonitoringDetail() {
    // Reset all tabs to loading
    ['videoListContent','gmvContent','keranjangContent','sampleContent'].forEach(id => {
        document.getElementById(id).innerHTML = '<div class="mon-loading"><div class="mon-spinner"></div></div>';
    });

    fetch(BASE_URL + 'is/get_monitoring_creator_detail', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'creator_id=' + currentCreatorId
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) { showToast(data.message, 'error'); return; }
        monitoringData = data;
        renderVideos(data.videos);
        renderGmv(data.gmv);
        renderKeranjang(data.keranjang);
        renderSample(data.sample_history, data.sample_summary);
    })
    .catch(e => showToast('Gagal memuat data: ' + e, 'error'));
}

// -------- RENDER VIDEOS --------
function renderVideos(videos) {
    const el = document.getElementById('videoListContent');
    if (!videos || videos.length === 0) {
        el.innerHTML = '<div class="mon-empty"><i class="ri-video-line"></i>Belum ada video</div>';
        return;
    }
    el.innerHTML = videos.map(v => `
        <div class="mon-video-item">
            <div class="mon-video-thumb"><i class="ri-play-circle-line"></i></div>
            <div class="mon-video-info">
                <div class="mon-video-title">${escHtml(v.title || v.product_name || 'Video')}</div>
                <div class="mon-video-meta">
                    👁 ${fmtNum(v.views)} views · ❤️ ${fmtNum(v.likes)} likes
                    ${v.posted_at ? ' · 📅 ' + v.posted_at.substring(0,10) : ''}
                    <span class="${v.source === 'api' ? 'mon-badge-api' : 'mon-badge-manual'} mon-badge" style="margin-left:6px">
                        ${v.source === 'api' ? '🤖 API' : '✍️ Manual'}
                    </span>
                </div>
                ${v.product_name ? '<div class="mon-video-meta">Produk: ' + escHtml(v.product_name) + '</div>' : ''}
                <a href="${escHtml(v.video_url)}" target="_blank" class="mon-video-link">
                    <i class="ri-external-link-line"></i> Buka Video
                </a>
            </div>
        </div>
    `).join('');
}

// -------- RENDER GMV --------
function renderGmv(gmv) {
    const el = document.getElementById('gmvContent');
    if (!gmv || !gmv.rows || gmv.rows.length === 0) {
        el.innerHTML = '<div class="mon-empty"><i class="ri-money-dollar-circle-line"></i>Belum ada GMV</div>';
        return;
    }
    const rows = gmv.rows.map(r => `
        <tr>
            <td>${escHtml(r.product_name || '-')}</td>
            <td style="text-align:right">${fmtNum(r.total_sold)} pcs</td>
            <td style="text-align:right;font-weight:600;color:var(--mon-green)">${fmtRp(r.gmv)}</td>
        </tr>
    `).join('');
    el.innerHTML = `
        <div class="mon-summary-boxes" style="margin-bottom:16px">
            <div class="mon-summary-box green"><div class="val">${fmtRp(gmv.total_gmv)}</div><div class="lbl">Total GMV</div></div>
            <div class="mon-summary-box yellow"><div class="val">${fmtNum(gmv.total_sold)}</div><div class="lbl">Total Terjual</div></div>
            <div class="mon-summary-box"><div class="val" style="color:var(--mon-blue)">${fmtNum(gmv.total_orders)}</div><div class="lbl">Total Order</div></div>
        </div>
        <table class="mon-mini-table">
            <thead><tr><th>Produk</th><th style="text-align:right">Terjual</th><th style="text-align:right">GMV</th></tr></thead>
            <tbody>
                ${rows}
                <tr class="mon-total-row">
                    <td>Total</td>
                    <td style="text-align:right">${fmtNum(gmv.total_sold)} pcs</td>
                    <td style="text-align:right">${fmtRp(gmv.total_gmv)}</td>
                </tr>
            </tbody>
        </table>
    `;
}

// -------- RENDER KERANJANG --------
function renderKeranjang(keranjang) {
    const el = document.getElementById('keranjangContent');
    if (!keranjang || keranjang.length === 0) {
        el.innerHTML = '<div class="mon-empty"><i class="ri-shopping-cart-line"></i>Belum ada produk di keranjang kuning</div>';
        return;
    }

    const rows = keranjang.map(k => `
        <tr>
            <td>
                <div style="font-weight:600;font-size:12px;color:var(--mon-text);margin-bottom:3px">
                    ${escHtml(k.product_name || '-')}
                </div>
                <div style="font-size:10px;color:var(--mon-muted)">
                    🏪 ${escHtml(k.shop_name || '-')} · 🏷 ${escHtml(k.category || '-')}
                </div>
                ${k.affiliate_link
                    ? `<a href="${escHtml(k.affiliate_link)}" target="_blank" class="mon-video-link" style="margin-top:4px;display:inline-flex">
                           <i class="ri-external-link-line"></i> Link Afiliasi
                       </a>`
                    : '<span style="font-size:10px;color:var(--mon-muted)">— Link tidak tersedia</span>'
                }
            </td>
            <td style="text-align:center">
                <span class="mon-badge mon-badge-green" style="font-size:10px">✅ Di Keranjang</span>
            </td>
            <td style="text-align:center;font-size:11px;color:var(--mon-text)">
                ${k.first_used ? k.first_used.substring(0,10) : '-'}
            </td>
            <td style="text-align:center;font-size:11px;color:var(--mon-muted)">
                ${k.last_used ? k.last_used.substring(0,10) : '-'}
            </td>
            <td style="text-align:right">
                <div style="font-weight:700;font-size:13px;color:var(--mon-green)">${fmtRp(k.total_gmv)}</div>
                <div style="font-size:10px;color:var(--mon-muted)">${fmtNum(k.total_orders)} order</div>
            </td>
        </tr>
    `).join('');

    el.innerHTML = `
        <div style="margin-bottom:12px;font-size:11px;color:var(--mon-muted)">
            ${keranjang.length} produk terdeteksi masuk keranjang kuning
        </div>
        <table class="mon-mini-table">
            <thead>
                <tr>
                    <th>Produk &amp; Link</th>
                    <th style="text-align:center">Status</th>
                    <th style="text-align:center">Masuk Keranjang</th>
                    <th style="text-align:center">Terakhir Digunakan</th>
                    <th style="text-align:right">GMV</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>
    `;
}

// -------- RENDER SAMPLE --------
function renderSample(history, summary) {
    const el = document.getElementById('sampleContent');
    const sumHtml = `
        <div class="mon-summary-boxes">
            <div class="mon-summary-box yellow"><div class="val">${summary.total_sent}</div><div class="lbl">Sample Dikirim</div></div>
            <div class="mon-summary-box green"><div class="val">${summary.has_video}</div><div class="lbl">Sudah Dibuat Video</div></div>
            <div class="mon-summary-box red"><div class="val">${summary.no_video}</div><div class="lbl">Belum Dibuat Video</div></div>
        </div>
    `;

    if (!history || history.length === 0) {
        el.innerHTML = sumHtml + '<div class="mon-empty"><i class="ri-gift-line"></i>Belum ada sample yang dikirim</div>';
        return;
    }

    const rows = history.map(s => `
        <tr>
            <td>${escHtml(s.ap_product_name || s.product_id || '-')}</td>
            <td>${escHtml(s.brand_name || '-')}</td>
            <td>${s.requested_at ? s.requested_at.substring(0,10) : '-'}</td>
            <td><span class="mon-badge ${s.delivery_method === 'system' ? 'mon-badge-blue' : 'mon-badge-purple'}">
                ${s.delivery_method === 'system' ? '🤖 System' : '📦 Manual'}
            </span></td>
            <td><span class="mon-badge mon-badge-${s.status === 'DELIVERED' || s.status === 'COMPLETED' ? 'green' : 'yellow'}">
                ${escHtml(s.status)}
            </span></td>
            <td>
                <span class="mon-badge ${s.video_status === 'has_video' ? 'mon-badge-green' : 'mon-badge-red'}">
                    ${s.video_status === 'has_video' ? '✅ Ada Video' : '⏳ Belum'}
                </span>
                ${s.video_status === 'no_video' ? `
                    <button class="mon-btn mon-btn-outline mon-btn-sm" style="margin-top:4px"
                        onclick="openUpdateVideoLink(${s.id})">+ Link Video</button>
                ` : (s.video_url ? `<a href="${escHtml(s.video_url)}" target="_blank" class="mon-video-link" style="display:block;margin-top:4px">Lihat</a>` : '')}
            </td>
        </tr>
    `).join('');

    el.innerHTML = sumHtml + `
        <table class="mon-mini-table">
            <thead>
                <tr>
                    <th>Produk</th><th>Brand</th><th>Tanggal</th>
                    <th>Metode</th><th>Status</th><th>Video</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>
    `;
}

// -------- GMV BREAKDOWN POPUP --------
function openGmvBreakdown(creatorId, username) {
    currentCreatorId = creatorId;
    document.getElementById('gmvPopupTitle').textContent = '💰 GMV Breakdown — @' + username;
    document.getElementById('gmvPopupContent').innerHTML = '<div class="mon-loading"><div class="mon-spinner"></div></div>';
    document.getElementById('monGmvOverlay').classList.add('active');

    fetch(BASE_URL + 'is/get_creator_gmv_breakdown', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'creator_id=' + creatorId
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            document.getElementById('gmvPopupContent').innerHTML = '<div class="mon-empty">Gagal memuat data</div>';
            return;
        }
        const rows = (data.products || []).map(p => `
            <tr>
                <td>${escHtml(p.product_name || '-')}</td>
                <td style="text-align:right">${fmtNum(p.total_sold)} pcs</td>
                <td style="text-align:right;font-weight:700;color:var(--mon-green)">${fmtRp(p.gmv)}</td>
            </tr>
        `).join('');
        document.getElementById('gmvPopupContent').innerHTML = `
            <div style="margin-bottom:16px;display:flex;gap:12px;flex-wrap:wrap">
                <div style="background:rgba(74,222,128,0.1);padding:10px 16px;border-radius:12px;border:1px solid rgba(74,222,128,0.2)">
                    <div style="font-size:10px;color:var(--mon-muted)">Total GMV</div>
                    <div style="font-size:18px;font-weight:700;color:var(--mon-green)">${fmtRp(data.total_gmv)}</div>
                </div>
                <div style="background:rgba(245,158,11,0.1);padding:10px 16px;border-radius:12px;border:1px solid rgba(245,158,11,0.2)">
                    <div style="font-size:10px;color:var(--mon-muted)">Total Terjual</div>
                    <div style="font-size:18px;font-weight:700;color:var(--mon-yellow)">${fmtNum(data.total_sold)} pcs</div>
                </div>
            </div>
            <table class="mon-mini-table">
                <thead><tr><th>Produk</th><th style="text-align:right">Produk Terjual</th><th style="text-align:right">GMV</th></tr></thead>
                <tbody>${rows}<tr class="mon-total-row"><td>Total</td><td style="text-align:right">${fmtNum(data.total_sold)} pcs</td><td style="text-align:right">${fmtRp(data.total_gmv)}</td></tr></tbody>
            </table>
        `;
    });
}
function closeGmvPopup() { document.getElementById('monGmvOverlay').classList.remove('active'); }

// -------- WILLING MODAL --------
function openWillingModal(creatorId, username) {
    currentCreatorId   = creatorId;
    currentCreatorName = username;
    document.getElementById('willingCreatorName').textContent = '@' + username;
    document.getElementById('willingNotes').value = '';
    document.getElementById('willingModalOverlay').classList.add('active');
}
function closeWillingModal() { document.getElementById('willingModalOverlay').classList.remove('active'); }

function submitWilling(willing) {
    const notes = document.getElementById('willingNotes').value;
    fetch(BASE_URL + 'is/confirm_sample_willingness', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `creator_id=${currentCreatorId}&willing=${willing}&notes=${encodeURIComponent(notes)}`
    })
    .then(r => r.json())
    .then(data => {
        closeWillingModal();
        if (!data.success) { showToast(data.message, 'error'); return; }
        showToast(data.message, 'success');
        if (willing) {
            // Buka rekomendasi produk
            setTimeout(() => openRecModal(), 400);
        } else {
            // Reload page setelah delay
            setTimeout(() => location.reload(), 1500);
        }
    })
    .catch(e => showToast('Error: ' + e, 'error'));
}

// -------- RECOMMENDATION MODAL --------
// Simpan semua data produk di sini — tidak di-embed ke HTML onclick
let recProductsMap = {};

function openRecModal() {
    selectedRecProducts = [];
    recProductsMap = {};
    document.getElementById('recSelectedCount').textContent = '0';
    // Reset metode ke Manual setiap kali modal dibuka
    document.querySelectorAll('input[name="recDeliveryMethod"]').forEach(r => { r.checked = r.value === 'manual'; });
    document.getElementById('tapRequestIdWrap').style.display = 'none';
    document.getElementById('tapRequestIdInput').value = '';
    document.getElementById('recGridContent').innerHTML = '<div class="mon-loading" style="grid-column:1/-1"><div class="mon-spinner"></div></div>';
    document.getElementById('recModalOverlay').classList.add('active');

    fetch(BASE_URL + 'is/get_sample_recommendations', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'creator_id=' + currentCreatorId
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success || !data.recommendations.length) {
            document.getElementById('recGridContent').innerHTML = '<div class="mon-empty" style="grid-column:1/-1"><i class="ri-gift-line"></i>Tidak ada rekomendasi produk ditemukan</div>';
            return;
        }
        document.getElementById('recCategoryBadge').textContent = '📂 Kategori: ' + (data.creator_categories.join(', ') || 'Semua');
        document.getElementById('recBrandBadge').textContent = '🏪 Brand creator: ' + (data.creator_brands.join(', ') || '-');

        // Simpan data produk ke Map menggunakan index sebagai key
        // Ini menghindari bug embed JSON di onclick attribute
        data.recommendations.forEach((p, i) => {
            recProductsMap[i] = p;
        });

        const grid = document.getElementById('recGridContent');
        grid.innerHTML = data.recommendations.map((p, i) => `
            <div class="mon-rec-card" data-rec-index="${i}">
                <div class="mon-rec-check">✓</div>
                ${p.image_url ? `<img src="${escHtml(p.image_url)}" class="mon-rec-card-img" onerror="this.style.display='none'">` : '<div class="mon-rec-card-img" style="display:flex;align-items:center;justify-content:center;color:var(--mon-muted)"><i class="ri-image-line" style="font-size:28px"></i></div>'}
                <div class="mon-rec-card-name">${escHtml(p.product_name || p.name || '-')}</div>
                <div class="mon-rec-card-brand">🏪 ${escHtml(p.shop_name || p.brand_display_name || '-')}</div>
                <div class="mon-rec-card-meta">
                    <span style="color:var(--mon-muted)">📦 ${escHtml(p.category || '-')}</span>
                    <span style="color:var(--mon-green);font-weight:600">${fmtRp(p.price || 0)}</span>
                </div>
            </div>
        `).join('');

        // Pasang event listener via JS, bukan onclick attribute
        // Ini menghindari masalah event bubbling dari child elements
        grid.querySelectorAll('.mon-rec-card').forEach(card => {
            card.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleRecProduct(this);
            });
        });
    });
}
function closeRecModal() {
    document.getElementById('recModalOverlay').classList.remove('active');
}

// Toggle visibility TAP Request ID input
function onDeliveryMethodChange(value) {
    document.getElementById('tapRequestIdWrap').style.display = value === 'system' ? 'block' : 'none';
}

function toggleRecProduct(card) {
    // Selalu gunakan card yang punya data-rec-index (card utama)
    // Jika yang diklik child element, naik ke parent yang punya attribute tersebut
    const targetCard = card.closest('[data-rec-index]');
    if (!targetCard) return;

    const idx = parseInt(targetCard.dataset.recIndex, 10);
    const product = recProductsMap[idx];
    if (!product) return;

    const key = String(idx);
    const existingIdx = selectedRecProducts.findIndex(p => p._recIdx === key);

    if (existingIdx >= 0) {
        // Sudah dipilih → deselect
        selectedRecProducts.splice(existingIdx, 1);
        targetCard.classList.remove('selected');
    } else {
        // Belum dipilih → select
        selectedRecProducts.push({ ...product, _recIdx: key });
        targetCard.classList.add('selected');
    }
    document.getElementById('recSelectedCount').textContent = selectedRecProducts.length;
}

function confirmSampleDelivery() {
    if (selectedRecProducts.length === 0) {
        showToast('Pilih minimal 1 produk sample', 'error');
        return;
    }

    const deliveryMethod = document.querySelector('input[name="recDeliveryMethod"]:checked')?.value || 'manual';
    const tapRequestId   = document.getElementById('tapRequestIdInput').value.trim();

    if (deliveryMethod === 'system' && !tapRequestId) {
        showToast('TAP Request ID wajib diisi untuk pengiriman By System', 'error');
        return;
    }

    const products = selectedRecProducts.map(p => ({
        product_id:  p.product_id,
        product_name:p.product_name || p.name,
        brand_id:    p.brand_db_id,
        brand_name:  p.brand_display_name || p.shop_name,
        quantity:    1,
    }));

    fetch(BASE_URL + 'is/save_sample_delivery', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `creator_id=${currentCreatorId}&products=${encodeURIComponent(JSON.stringify(products))}&delivery_method=${deliveryMethod}&tap_request_id=${encodeURIComponent(tapRequestId)}`
    })
    .then(r => r.json())
    .then(data => {
        closeRecModal();
        if (!data.success) { showToast(data.message, 'error'); return; }
        showToast(data.message, 'success');
        setTimeout(() => location.reload(), 1800);
    })
    .catch(e => showToast('Error: ' + e, 'error'));
}
// -------- ADD VIDEO MANUAL --------
function openAddVideoForm() { document.getElementById('addVideoOverlay').classList.add('active'); }
function closeAddVideoForm() { document.getElementById('addVideoOverlay').classList.remove('active'); }

function submitVideoManual() {
    const url   = document.getElementById('videoUrlInput').value.trim();
    const pname = document.getElementById('videoProdNameInput').value.trim();
    const date  = document.getElementById('videoDateInput').value;

    if (!url) { showToast('URL video wajib diisi', 'error'); return; }

    fetch(BASE_URL + 'is/add_creator_video', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `creator_id=${currentCreatorId}&video_url=${encodeURIComponent(url)}&product_name=${encodeURIComponent(pname)}&posted_at=${date}`
    })
    .then(r => r.json())
    .then(data => {
        closeAddVideoForm();
        if (!data.success) { showToast(data.message, 'error'); return; }
        showToast('Video berhasil ditambahkan', 'success');
        loadMonitoringDetail(); // Refresh
    });
}

// -------- UPDATE VIDEO LINK KE SAMPLE --------
function openUpdateVideoLink(sampleId) {
    const url = prompt('Masukkan link video TikTok untuk sample ini:');
    if (!url || !url.trim()) return;

    fetch(BASE_URL + 'is/update_sample_video_link', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `sample_id=${sampleId}&video_url=${encodeURIComponent(url.trim())}`
    })
    .then(r => r.json())
    .then(data => {
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) loadMonitoringDetail();
    });
}

// -------- HELPERS --------
function fmtRp(val) {
    const v = parseFloat(val) || 0;
    if (v >= 1e9) return 'Rp ' + (v/1e9).toFixed(1) + 'M';
    if (v >= 1e6) return 'Rp ' + (v/1e6).toFixed(1) + 'Jt';
    if (v >= 1e3) return 'Rp ' + (v/1e3).toFixed(0) + 'K';
    return 'Rp ' + v.toFixed(0);
}
function fmtNum(val) {
    const v = parseInt(val) || 0;
    if (v >= 1e6) return (v/1e6).toFixed(1) + 'M';
    if (v >= 1e3) return (v/1e3).toFixed(1) + 'K';
    return v.toString();
}
function escHtml(str) {
    return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function showToast(msg, type='success') {
    const wrap = document.getElementById('monToastWrap');
    const toast = document.createElement('div');
    toast.className = 'mon-toast ' + type;
    toast.innerHTML = (type === 'success' ? '✅ ' : '❌ ') + msg;
    wrap.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}

// Close overlay on background click
['monDetailOverlay','monGmvOverlay'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('active');
    });
});
</script>
