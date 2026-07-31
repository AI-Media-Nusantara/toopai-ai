<div class="dashboard-container">
    <div class="page-header">
        <h1 class="page-title">IS Dashboard</h1>
        <p class="page-subtitle">Influencer Success · Manage Creators & Performance</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
                <div class="stat-value">Rp <?= number_format($total_gmv, 0, ',', '.') ?></div>
                <div class="stat-label">Total GMV</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-info">
                <div class="stat-value"><?= $total_creators ?></div>
                <div class="stat-label">Creators Managed</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🎯</div>
            <div class="stat-info">
                <div class="stat-value">Rp <?= number_format($est_commission, 0, ',', '.') ?></div>
                <div class="stat-label">Est. Commission</div>
            </div>
        </div>
    </div>

    <!-- 6 Stage Pipeline -->
    <div class="stages-scroll">
        <div class="stages-container">
            <!-- Stage 1: SCOUTING -->
            <div class="stage-card">
                <div class="stage-title">1. SCOUTING (BRAND MATCH)</div>
                <?php foreach (array_slice($scouting_items, 0, 2) as $creator): ?>
                <div class="stage-item">
                    <strong><?= htmlspecialchars($creator->username ?? $creator->full_name ?? 'Creator') ?></strong>
                    <div>Category: <?= htmlspecialchars($creator->category ?? 'General') ?></div>
                    <div>GMV: Rp <?= number_format($creator->total_gmv ?? 0, 0, ',', '.') ?></div>
                </div>
                <?php endforeach; ?>
                <button class="task-btn" data-action="scouting">🔍 Scout & Match Brand</button>
            </div>

            <!-- Stage 2: LINK SWAPPING -->
            <div class="stage-card">
                <div class="stage-title">2. LINK SWAPPING</div>
                <?php foreach (array_slice($link_swapping_items, 0, 2) as $item): ?>
                <div class="stage-item">
                    <strong><?= htmlspecialchars($item['creator'] ?? 'Creator') ?></strong>
                    <div>Status: <?= $item['status'] ?? 'Pending' ?></div>
                </div>
                <?php endforeach; ?>
                <button class="task-btn" data-action="swap">🔗 Verify Swap</button>
            </div>

            <!-- Stage 3: SENDING SAMPLE -->
            <div class="stage-card">
                <div class="stage-title">3. SENDING SAMPLE</div>
                <?php foreach (array_slice($sending_sample_items, 0, 2) as $item): ?>
                <div class="stage-item">
                    <strong><?= htmlspecialchars($item['creator'] ?? 'Creator') ?></strong>
                    <div>Product: <?= htmlspecialchars($item['product'] ?? '-') ?></div>
                </div>
                <?php endforeach; ?>
                <button class="task-btn" data-action="sample">📦 Send Request Link</button>
            </div>

            <!-- Stage 4: CONTENT SCRIPT -->
            <div class="stage-card">
                <div class="stage-title">4. CONTENT SCRIPT</div>
                <?php foreach (array_slice($content_script_items, 0, 2) as $item): ?>
                <div class="stage-item">
                    <strong><?= htmlspecialchars($item['creator'] ?? 'Creator') ?></strong>
                    <div>Script: <?= $item['script_version'] ?? 'v1.0' ?></div>
                </div>
                <?php endforeach; ?>
                <button class="task-btn" data-action="script">📝 Send Script</button>
            </div>

            <!-- Stage 5: VIDEO POSTING -->
            <div class="stage-card">
                <div class="stage-title">5. VIDEO POSTING</div>
                <?php foreach (array_slice($video_posting_items, 0, 2) as $item): ?>
                <div class="stage-item">
                    <strong><?= htmlspecialchars($item['creator'] ?? 'Creator') ?></strong>
                    <div>GMV: Rp <?= number_format($item['gmv'] ?? 0, 0, ',', '.') ?></div>
                </div>
                <?php endforeach; ?>
                <button class="task-btn" data-action="video">🎬 Verify Video Post</button>
            </div>

            <!-- Stage 6: EAT GMV -->
            <div class="stage-card">
                <div class="stage-title">6. EAT GMV</div>
                <?php foreach ($eat_gmv_items as $idx => $creator): ?>
                <div class="stage-item">
                    <strong>#<?= $idx+1 ?> <?= htmlspecialchars($creator->username ?? $creator->full_name) ?></strong>
                    <div>GMV: Rp <?= number_format($creator->total_gmv ?? 0, 0, ',', '.') ?></div>
                </div>
                <?php endforeach; ?>
                <button class="task-btn" data-action="reward">🎁 Give Bonus Reward</button>
            </div>
        </div>
    </div>

    <!-- Top Creators Leaderboard -->
    <div class="section-card">
        <h2 class="section-title">🏆 Top Performing Creators</h2>
        <div class="creators-grid">
            <?php foreach ($top_creators as $idx => $creator): ?>
            <div class="creator-card">
                <div class="creator-rank">#<?= $idx+1 ?></div>
                <div class="creator-info">
                    <div class="creator-name"><?= htmlspecialchars($creator->username ?? $creator->full_name) ?></div>
                    <div class="creator-stats">
                        <span>💰 Rp <?= number_format($creator->total_gmv ?? 0, 0, ',', '.') ?></span>
                        <span>📦 <?= number_format($creator->total_orders ?? 0) ?> orders</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($top_creators)): ?>
            <div class="empty-state">No creator data yet</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.dashboard-container {
    animation: fadeIn 0.3s ease;
}
.page-header {
    margin-bottom: 28px;
}
.page-title {
    font-size: 28px;
    font-weight: 700;
    color: #e2f0e8;
    margin-bottom: 6px;
}
.page-subtitle {
    color: #9aaebe;
    font-size: 14px;
}
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}
.stat-card {
    background: #111827;
    border-radius: 24px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    border: 1px solid #2a3346;
}
.stat-icon {
    font-size: 48px;
}
.stat-value {
    font-size: 28px;
    font-weight: 700;
    color: #e2f0e8;
}
.stat-label {
    color: #9aaebe;
    font-size: 13px;
}
.stages-scroll {
    overflow-x: auto;
    white-space: nowrap;
    padding-bottom: 20px;
    margin-bottom: 32px;
}
.stages-container {
    display: inline-flex;
    gap: 20px;
}
.stage-card {
    background: #111827;
    border-radius: 24px;
    width: 320px;
    white-space: normal;
    padding: 20px;
    border: 1px solid #2a3346;
}
.stage-title {
    font-weight: 700;
    font-size: 16px;
    margin-bottom: 16px;
    padding-bottom: 8px;
    border-bottom: 1px solid #2a3346;
    color: #e2f0e8;
}
.stage-item {
    background: #0f1420;
    border-radius: 16px;
    padding: 12px;
    margin-bottom: 12px;
}
.stage-item strong {
    display: block;
    margin-bottom: 6px;
    color: #ffffff;
    font-size: 13px;
}
.stage-item div {
    color: #9aaebe;
    font-size: 11px;
}
.task-btn {
    margin-top: 16px;
    width: 100%;
    padding: 10px;
    border-radius: 40px;
    border: none;
    background: #1e293b;
    color: #cbd5e6;
    font-weight: 500;
    cursor: pointer;
    font-size: 12px;
}
.task-btn:hover {
    background: #4ade80;
    color: #0a0e17;
}
.section-card {
    background: #111827;
    border-radius: 24px;
    padding: 24px;
    border: 1px solid #2a3346;
}
.section-title {
    font-size: 20px;
    font-weight: 600;
    color: #e2f0e8;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 1px solid #2a3346;
}
.creators-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
}
.creator-card {
    background: #0f1420;
    border-radius: 16px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 16px;
    border: 1px solid #2a3346;
}
.creator-rank {
    width: 40px;
    height: 40px;
    background: #4ade80;
    border-radius: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: #0a0e17;
}
.creator-name {
    font-weight: 600;
    color: #ffffff;
    margin-bottom: 6px;
}
.creator-stats {
    display: flex;
    gap: 16px;
    font-size: 12px;
    color: #9aaebe;
}
.empty-state {
    text-align: center;
    color: #9aaebe;
    padding: 40px;
}
</style>

<script>
document.querySelectorAll('.task-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const action = this.getAttribute('data-action');
        alert(`Task "${action}" initiated. Complete your action in the modal.`);
    });
});
</script>