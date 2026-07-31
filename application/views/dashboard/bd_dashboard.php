<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title><?= $title ?? 'BD Dashboard - Toopai' ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, sans-serif;
        }
        body {
            background: #0a0e17;
            min-height: 100vh;
            padding: 24px 28px;
        }
        .dashboard {
            max-width: 1600px;
            margin: 0 auto;
        }
        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            margin-bottom: 28px;
            gap: 20px;
        }
        .title h1 {
            font-size: 28px;
            font-weight: 600;
            background: linear-gradient(135deg, #c0ffb0, #4ade80);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .title .sub {
            color: #8e9eae;
            font-size: 14px;
            margin-top: 6px;
        }
        .stat-cards {
            display: flex;
            gap: 28px;
            background: rgba(12, 20, 30, 0.7);
            backdrop-filter: blur(12px);
            padding: 12px 28px;
            border-radius: 40px;
            border: 1px solid rgba(74, 222, 128, 0.25);
        }
        .stat-item {
            text-align: right;
        }
        .stat-label {
            font-size: 12px;
            color: #9aaebe;
        }
        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #e2f0e8;
        }
        /* Tabs */
        .tabs-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            border-bottom: 1px solid rgba(74, 222, 128, 0.3);
            padding-bottom: 4px;
            flex-wrap: wrap;
            gap: 16px;
        }
        .tabs {
            display: flex;
            gap: 12px;
        }
        .tab-btn {
            background: transparent;
            border: none;
            padding: 12px 28px;
            font-size: 16px;
            font-weight: 500;
            color: #9aaebe;
            cursor: pointer;
            transition: 0.2s;
            border-radius: 40px;
        }
        .tab-btn.active {
            background: rgba(74, 222, 128, 0.15);
            color: #4ade80;
        }
        .scout-btn {
            background: rgba(74, 222, 128, 0.15);
            border: 1px solid #4ade80;
            padding: 8px 24px;
            border-radius: 40px;
            color: #4ade80;
            font-weight: 500;
            cursor: pointer;
            font-size: 14px;
            transition: 0.2s;
        }
        .scout-btn:hover {
            background: #4ade80;
            color: #0a0e17;
        }
        .tab-content {
            display: none;
            animation: fade 0.2s ease;
        }
        .tab-content.active {
            display: block;
        }
        @keyframes fade { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

        /* Horizontal scroll stages */
        .stages-scroll {
            overflow-x: auto;
            white-space: nowrap;
            padding-bottom: 20px;
            margin-bottom: 32px;
            scroll-behavior: smooth;
        }
        .stages-container {
            display: inline-flex;
            gap: 20px;
        }
        .stage-card {
            background: rgba(18, 25, 40, 0.7);
            backdrop-filter: blur(8px);
            border-radius: 28px;
            width: 340px;
            white-space: normal;
            padding: 20px;
            border: 1px solid rgba(74, 222, 128, 0.2);
            transition: 0.2s;
        }
        .stage-card.completed {
            border-color: #4ade80;
            opacity: 0.8;
        }
        .stage-title {
            font-weight: 700;
            font-size: 18px;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid rgba(74,222,128,0.3);
            color: #bdf2c0;
        }
        .stage-item {
            background: rgba(0,0,0,0.25);
            border-radius: 20px;
            padding: 12px;
            margin-bottom: 12px;
        }
        .stage-item strong {
            display: block;
            margin-bottom: 6px;
            color: #d1dbe8;
            font-weight: 500;
        }
        .stage-item div {
            color: #9aaebe;
            font-size: 12px;
        }
        .badge {
            display: inline-block;
            background: rgba(74,222,128,0.2);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            color: #4ade80;
            margin-top: 6px;
        }
        .task-btn {
            margin-top: 16px;
            width: 100%;
            padding: 10px;
            border-radius: 40px;
            border: none;
            background: rgba(74,222,128,0.2);
            color: #bdf2c0;
            font-weight: 500;
            cursor: pointer;
            transition: 0.2s;
        }
        .task-btn:hover:not(:disabled) {
            background: #4ade80;
            color: #0a0e17;
        }
        .task-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        /* Recent orders */
        .recent-section {
            background: rgba(12, 20, 30, 0.6);
            backdrop-filter: blur(12px);
            border-radius: 32px;
            padding: 20px;
            margin-top: 20px;
            border: 1px solid rgba(74,222,128,0.2);
        }
        .recent-table {
            width: 100%;
            border-collapse: collapse;
            color: #cbd5e6;
        }
        .recent-table th, .recent-table td {
            text-align: left;
            padding: 12px 8px;
            border-bottom: 1px solid rgba(74,222,128,0.15);
            font-size: 13px;
        }
        .recent-table th {
            color: #8e9eae;
            font-weight: 500;
        }
        .pagination button {
            background: rgba(30,40,55,0.8);
            border: 1px solid #2d6a4f;
            color: #bdf2c0;
            padding: 6px 16px;
            border-radius: 30px;
            cursor: pointer;
        }
        /* Brand Status */
        .brands-grid {
            display: flex;
            gap: 28px;
            flex-wrap: wrap;
        }
        .brand-card-glass {
            flex: 1;
            background: rgba(18, 25, 40, 0.6);
            backdrop-filter: blur(10px);
            border-radius: 32px;
            padding: 22px;
            border: 1px solid rgba(74,222,128,0.2);
        }
        .brand-item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid rgba(74,222,128,0.15);
            color: #d1dbe8;
        }
        /* Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.85);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            visibility: hidden;
            opacity: 0;
            transition: 0.2s;
        }
        .modal-overlay.active {
            visibility: visible;
            opacity: 1;
        }
        .modal-glass {
            background: rgba(18, 25, 40, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 32px;
            width: 550px;
            max-width: 90vw;
            padding: 28px;
            border: 1px solid #4ade80;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(74,222,128,0.3);
            padding-bottom: 12px;
        }
        .modal-header h3 {
            color: #eef4ff;
        }
        .modal-close {
            font-size: 28px;
            cursor: pointer;
            color: #9aaebe;
        }
        .modal-body {
            color: #d1dbe8;
        }
        .modal-body input, .modal-body select, .modal-body textarea {
            width: 100%;
            padding: 12px;
            margin: 8px 0 16px;
            background: rgba(0,0,0,0.4);
            border: 1px solid rgba(74,222,128,0.3);
            border-radius: 20px;
            color: #e2e8f0;
        }
        .modal-body button {
            background: #4ade80;
            color: #0a0e17;
            border: none;
            padding: 12px;
            border-radius: 40px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
        }
        .flex-buttons {
            display: flex;
            gap: 12px;
            margin-top: 16px;
        }
        .flex-buttons button {
            flex: 1;
        }
        /* Sidebar leaderboard */
        .sidebar {
            background: rgba(10, 18, 28, 0.98);
            backdrop-filter: blur(20px);
            width: 460px;
            max-width: 90vw;
            height: 100%;
            border-left: 1px solid #4ade80;
            padding: 28px;
            overflow-y: auto;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            position: fixed;
            right: 0;
            top: 0;
        }
        .brand-card-popup {
            background: rgba(18,25,40,0.6);
            border-radius: 24px;
            padding: 16px;
            margin-bottom: 16px;
            border: 1px solid rgba(74,222,128,0.2);
        }
        .brand-card-popup > div:first-child {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .brand-card-popup > div:last-child {
            display: flex;
            justify-content: space-between;
            margin-top: 12px;
        }
        @keyframes fadeInOut {
            0% { opacity: 0; transform: translateY(20px); }
            15% { opacity: 1; transform: translateY(0); }
            85% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(20px); }
        }
        ::-webkit-scrollbar {
            height: 6px;
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #1a2538;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: #4ade80;
            border-radius: 10px;
        }
    </style>
</head>
<body>
<div class="dashboard">
    <!-- Header -->
    <div class="header">
        <div class="title">
            <h1>Toopai Brand Agent OS</h1>
            <div class="sub">Managing <?= $total_brands ?> Brand Partners · Real-time API</div>
        </div>
        <div class="stat-cards">
            <div class="stat-item">
                <div class="stat-label">ACTIVE CAMPAIGN GMV</div>
                <div class="stat-value">Rp <?= number_format($total_gmv, 0, ',', '.') ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-label">DEAL BONUS (0.15%)</div>
                <div class="stat-value">Rp <?= number_format($deal_bonus_amount, 0, ',', '.') ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-label">STATUS</div>
                <div class="stat-value" style="font-size:16px;">🟢 LIVE</div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs-bar">
        <div class="tabs">
         <button class="tab-btn active" data-tab="tabTask">
    <i class="fas fa-clipboard-list"></i> Task
</button>

<button class="tab-btn" data-tab="tabBrandStatus">
    <i class="fas fa-tags"></i> Brand Status
</button>

<button class="tab-btn" data-tab="tabLeaderboard">
    <i class="fas fa-trophy"></i> Top Brand Leaderboard
</button>
        </div>
        <button class="scout-btn" id="scoutBtn">🔍 Scout & Match Brand</button>
    </div>

    <!-- TAB 1: TASK -->
    <div id="tabTask" class="tab-content active">
        <div class="stages-scroll">
            <div class="stages-container">
                <!-- Stage 1: HUNTING -->
                <div class="stage-card" data-stage="1">
                    <div class="stage-title">1. HUNTING</div>
                    <?php if (!empty($scouting_items)): ?>
                        <?php foreach (array_slice($scouting_items, 0, 2) as $item): ?>
                        <div class="stage-item" data-product-id="<?= $item->id ?? '' ?>">
                            <strong><?= htmlspecialchars($item->shop_name ?? $item->name ?? 'Unknown Brand') ?></strong>
                            <div><?= htmlspecialchars(substr($item->name ?? '', 0, 40)) ?>...</div>
                            <div>Target Comm: <?= $item->commission_rate ?? 5 ?>% → <?= ($item->commission_rate ?? 5) + 5 ?>%</div>
                            <span class="badge">AI Scouted</span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="stage-item">
                            <strong>No products scouted yet</strong>
                            <div>Click "Scout & Match Brand" to find brands</div>
                        </div>
                    <?php endif; ?>
                    <button class="task-btn" data-action="hunting">📧 Brand Outreach Generation</button>
                </div>

                <!-- Stage 2: OUTREACH -->
                <div class="stage-card" data-stage="2">
                    <div class="stage-title">2. OUTREACH</div>
                    <?php if (!empty($outreach_items)): ?>
                        <?php foreach ($outreach_items as $item): ?>
                        <div class="stage-item" data-brand-id="<?= $item->id ?>">
                            <strong><?= htmlspecialchars($item->name) ?></strong>
                            <div>Email Sent · Reply Received</div>
                            <div>Proposed: <?= $item->proposed_commission ?? 15 ?>%</div>
                            <span class="badge">Negotiate</span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="stage-item">
                            <strong>No active outreach</strong>
                            <div>Complete Hunting stage first</div>
                        </div>
                    <?php endif; ?>
                    <button class="task-btn" data-action="outreach">🤝 Negotiate</button>
                </div>

                <!-- Stage 3: DEAL & TERMS -->
                <div class="stage-card" data-stage="3">
                    <div class="stage-title">3. DEAL & TERMS</div>
                    <?php if (!empty($deal_items)): ?>
                        <?php foreach ($deal_items as $item): ?>
                        <div class="stage-item" data-brand-id="<?= $item->id ?>">
                            <strong><?= htmlspecialchars($item->name) ?></strong>
                            <div>Counter-offer: <?= $item->counter_commission ?? 12 ?>% + <?= $item->counter_samples ?? 50 ?> samples</div>
                            <span class="badge">Pending Review</span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="stage-item">
                            <strong>No active deals</strong>
                            <div>Complete Outreach stage first</div>
                        </div>
                    <?php endif; ?>
                    <button class="task-btn" data-action="deal">📝 Review Brand Counter-Offer</button>
                </div>

                <!-- Stage 4: ONBOARDING -->
                <div class="stage-card" data-stage="4">
                    <div class="stage-title">4. ONBOARDING</div>
                    <?php if (!empty($onboarding_items)): ?>
                        <?php foreach ($onboarding_items as $item): ?>
                        <div class="stage-item" data-brand-id="<?= $item->id ?>">
                            <strong><?= htmlspecialchars($item->name) ?></strong>
                            <div>API Status: <?= $item->api_status ?? 'Pending' ?></div>
                            <div>Samples: <?= $item->samples_allocated ?? 0 ?> pcs</div>
                            <span class="badge">Ready</span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="stage-item">
                            <strong>No onboarding pending</strong>
                            <div>Complete Deal stage first</div>
                        </div>
                    <?php endif; ?>
                    <button class="task-btn" data-action="sample">📦 Form Request Samples</button>
                    <button class="task-btn" data-action="verify" style="margin-top:8px;">🔌 Verify API & Move to Campaign</button>
                </div>

                <!-- Stage 5: CAMPAIGN SETUP -->
                <div class="stage-card" data-stage="5">
                    <div class="stage-title">5. CAMPAIGN SETUP</div>
                    <?php if (!empty($campaign_setup_items)): ?>
                        <?php foreach ($campaign_setup_items as $item): ?>
                        <div class="stage-item" data-campaign-id="<?= $item->id ?>">
                            <strong><?= htmlspecialchars($item->name) ?></strong>
                            <div>GMV Rp <?= number_format($item->total_gmv ?? 0,0,',','.') ?></div>
                            <div>Creators: <?= $item->creators_matched ?? 0 ?>/<?= $item->creators_target ?? 50 ?></div>
                            <span class="badge">Ready to Launch</span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="stage-item">
                            <strong>No campaigns ready</strong>
                            <div>Complete Onboarding stage first</div>
                        </div>
                    <?php endif; ?>
                    <button class="task-btn" data-action="launch">🚀 Launch to Creator Network</button>
                </div>

                <!-- Stage 6: RETENTION & UPSELL -->
                <div class="stage-card" data-stage="6">
                    <div class="stage-title">6. RETENTION & UPSELL</div>
                    <?php if (!empty($retention_items)): ?>
                        <?php foreach ($retention_items as $item): ?>
                        <div class="stage-item" data-campaign-id="<?= $item->id ?>">
                            <strong><?= htmlspecialchars($item->name) ?></strong>
                            <div>GMV Rp <?= number_format($item->total_gmv ?? 0,0,',','.') ?></div>
                            <div>ROAS: <?= $item->roas ?? 0 ?>x</div>
                            <span class="badge">Active</span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="stage-item">
                            <strong>No active campaigns</strong>
                            <div>Launch a campaign first</div>
                        </div>
                    <?php endif; ?>
                    <button class="task-btn" data-action="retention">📊 Generate Report & Upsell</button>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="recent-section">
            <div style="display:flex; justify-content:space-between; margin-bottom:16px;">
                <h3 style="color:#eef4ff;">📦 Recent Orders (last 30 days)</h3>
                <div><span id="orderRange">1-10</span> / <span id="totalOrders"><?= count($orders) ?></span></div>
            </div>
            <div style="overflow-x:auto">
                <table class="recent-table">
                    <thead>
                        <tr><th>Date</th><th>Product</th><th>Creator</th><th>GMV</th><th>Partner Comm</th>
                    </thead>
                    <tbody id="recentOrdersBody"></tbody>
                </table>
            </div>
            <div class="pagination">
                <button id="prevPageBtn" disabled>← Previous</button>
                <button id="nextPageBtn">Next →</button>
            </div>
        </div>
    </div>

    <!-- TAB 2: BRAND STATUS -->
    <div id="tabBrandStatus" class="tab-content">
        <div class="brands-grid">
            <div class="brand-card-glass">
                <h3>🟢 Activated Brands <span style="font-size:13px;">(generating sales)</span></h3>
                <?php if (!empty($activated_brands)): ?>
                    <?php foreach ($activated_brands as $brand): ?>
                    <div class="brand-item-row">
                        <div><strong><?= htmlspecialchars($brand->name) ?></strong><br><span style="font-size:12px;">GMV Rp <?= number_format($brand->total_gmv,0,',','.') ?></span></div>
                        <div>ROAS: <?= $brand->roas ?>x</div>
                        <span class="badge">Upsell →</span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="brand-item-row">No activated brands yet</div>
                <?php endif; ?>
            </div>
            <div class="brand-card-glass">
                <h3>⚪ Unactivated Brands <span style="font-size:13px;">(pipeline, zero sales)</span></h3>
                <?php if (!empty($unactivated_brands)): ?>
                    <?php foreach ($unactivated_brands as $brand): ?>
                    <div class="brand-item-row">
                        <div><strong><?= htmlspecialchars($brand->name) ?></strong><br><span style="font-size:12px;">Status: <?= $brand->status ?></span></div>
                        <span class="badge">View Pipeline →</span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="brand-item-row">All brands activated 🎉</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- TAB 3: TOP BRAND LEADERBOARD -->
    <div id="tabLeaderboard" class="tab-content">
        <div style="text-align: center; padding: 60px 20px;">
            <button id="openLeaderboardBtn" class="scout-btn" style="font-size: 18px; padding: 12px 28px;">🏆 Lihat Top Brands Leaderboard →</button>
        </div>
    </div>
</div>

<!-- Popup Sidebar Top Brands -->
<div id="leaderboardSidebar" class="modal-overlay" style="justify-content: flex-end;">
    <div class="sidebar">
        <span class="modal-close" id="closeSidebar">&times;</span>
        <h2 style="color:#eef4ff;">🏆 Top Performing Brands</h2>
        <div id="leaderboardList" style="margin-top: 24px;">
            <?php $rank = 1; foreach ($top_brands as $brand): ?>
            <div class="brand-card-popup">
                <div>
                    <strong style="color:#eef4ff;"><?= htmlspecialchars($brand->name) ?></strong>
                    <span style="color:#4ade80;">+<?= rand(5,15) ?>%</span>
                </div>
                <div style="font-size:12px; color:#9aaebe;">#<?= $rank++ ?> · <?= $brand->category ?? 'GENERAL' ?></div>
                <div>
                    <div><span style="color:#8e9eae;">GMV</span><br><strong>Rp <?= number_format($brand->total_gmv,0,',','.') ?></strong></div>
                    <div><span style="color:#8e9eae;">ROAS</span><br><strong><?= $brand->roas ?>x</strong></div>
                    <div><span style="color:#8e9eae;">Creators</span><br><strong><?= $brand->creator_count ?? 0 ?></strong></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Modal untuk Task -->
<div id="taskModal" class="modal-overlay">
    <div class="modal-glass">
        <div class="modal-header">
            <h3 id="modalTitle">Task</h3>
            <span class="modal-close" id="closeTaskModal">&times;</span>
        </div>
        <div class="modal-body" id="modalBody"></div>
    </div>
</div>

<script>
// ========== STAGE COMPLETION TRACKING ==========
let completedStages = JSON.parse(localStorage.getItem('bd_completed_stages')) || [];

function saveCompletedStages() {
    localStorage.setItem('bd_completed_stages', JSON.stringify(completedStages));
}

function isStageCompleted(stageNum) {
    return completedStages.includes(stageNum);
}

function updateStageUI() {
    document.querySelectorAll('.stage-card').forEach(card => {
        const stageNum = parseInt(card.getAttribute('data-stage'));
        const btns = card.querySelectorAll('.task-btn');
        
        if (isStageCompleted(stageNum)) {
            card.classList.add('completed');
            btns.forEach(btn => {
                btn.disabled = true;
                btn.textContent = '✓ Completed';
                btn.style.background = '#2d6a4f';
            });
        } else if (stageNum > 1 && !isStageCompleted(stageNum - 1)) {
            btns.forEach(btn => {
                btn.disabled = true;
                btn.textContent = '🔒 Complete previous stage';
                btn.style.background = '#1e293b';
            });
        } else {
            card.classList.remove('completed');
            btns.forEach(btn => {
                btn.disabled = false;
                const action = btn.getAttribute('data-action');
                const texts = {
                    'hunting': '📧 Brand Outreach Generation',
                    'outreach': '🤝 Negotiate',
                    'deal': '📝 Review Brand Counter-Offer',
                    'sample': '📦 Form Request Samples',
                    'verify': '🔌 Verify API & Move to Campaign',
                    'launch': '🚀 Launch to Creator Network',
                    'retention': '📊 Generate Report & Upsell'
                };
                btn.textContent = texts[action] || 'Complete';
                btn.style.background = 'rgba(74,222,128,0.2)';
            });
        }
    });
}

function completeStage(stageNum) {
    if (!completedStages.includes(stageNum)) {
        completedStages.push(stageNum);
        saveCompletedStages();
        updateStageUI();
        
        const nextStage = stageNum + 1;
        if (nextStage <= 6) {
            const nextCard = document.querySelector(`.stage-card[data-stage='${nextStage}']`);
            if (nextCard) nextCard.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });
            showToast(`✅ Stage ${stageNum} completed! Moving to stage ${nextStage}.`);
        } else {
            showToast('🎉 Congratulations! All stages completed!');
        }
    }
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.textContent = message;
    toast.style.cssText = 'position:fixed; bottom:20px; right:20px; background:#4ade80; color:#0a0e17; padding:12px 24px; border-radius:40px; z-index:9999; animation:fadeInOut 3s;';
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// ========== MODAL HANDLER ==========
const taskModal = document.getElementById('taskModal');
const modalTitle = document.getElementById('modalTitle');
const modalBody = document.getElementById('modalBody');
const closeTaskModal = document.getElementById('closeTaskModal');

function closeModal() { taskModal.classList.remove('active'); }
closeTaskModal.addEventListener('click', closeModal);
taskModal.addEventListener('click', (e) => { if (e.target === taskModal) closeModal(); });

// ========== TASK MODAL FUNCTIONS ==========
function showHuntingModal(stageNum) {
    const stageCard = document.querySelector(`.stage-card[data-stage='${stageNum}']`);
    const firstItem = stageCard.querySelector('.stage-item');
    const brandName = firstItem?.querySelector('strong')?.textContent || 'Brand';
    
    modalTitle.innerText = '🤖 Brand Outreach Generation';
    modalBody.innerHTML = `
        <p><strong>TARGET BRAND</strong><br>${brandName}</p>
        <p><strong>AI Generated Cold Email:</strong></p>
        <div style="background:rgba(0,0,0,0.3); padding:16px; border-radius:20px; margin:12px 0;" id="emailPreview">Generating email...</div>
        <button id="sendEmailBtn">📧 Send Email & Move to Outreach</button>
    `;
    taskModal.classList.add('active');
    
    fetch('<?= base_url("bd/task_hunting") ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ brand_name: brandName })
    })
    .then(res => res.json())
    .then(result => {
        const preview = document.getElementById('emailPreview');
        if (preview) preview.innerHTML = (result.email_content || 'Email ready to send.').replace(/\n/g, '<br>');
    })
    .catch(() => {});
    
    document.getElementById('sendEmailBtn').addEventListener('click', () => { closeModal(); completeStage(stageNum); }, { once: true });
}

function showOutreachModal(stageNum) {
    modalTitle.innerText = '🤝 Deal Negotiation';
    modalBody.innerHTML = `
        <p><strong>Brand Offer:</strong><br>12% Comm + 50 Free Samples</p>
        <p><strong>AI ANALYSIS:</strong><br>Offer is reasonable, high sample quota guarantees content volume.</p>
        <div class="flex-buttons">
            <button id="counterOfferBtn">🔄 Counter Offer 15% + 30 samples</button>
            <button id="acceptBtn">✅ Accept Deal & Sign</button>
        </div>
    `;
    taskModal.classList.add('active');
    document.getElementById('acceptBtn')?.addEventListener('click', () => { closeModal(); completeStage(stageNum); }, { once: true });
    document.getElementById('counterOfferBtn')?.addEventListener('click', () => { closeModal(); completeStage(stageNum); }, { once: true });
}

function showDealModal(stageNum) {
    modalTitle.innerText = '📝 Review Brand Counter-Offer';
    modalBody.innerHTML = `
        <p><strong>Final Offer:</strong><br>15% Comm + 100 Free Samples</p>
        <p><strong>AI ANALYSIS:</strong><br>Strong deal. High commission will attract top creators.</p>
        <button id="acceptDealBtn">✅ Accept Deal & Sign Contract</button>
    `;
    taskModal.classList.add('active');
    document.getElementById('acceptDealBtn')?.addEventListener('click', () => { closeModal(); completeStage(stageNum); }, { once: true });
}

function showSampleModal(stageNum) {
    modalTitle.innerText = '📦 Request Sample Fulfillment';
    modalBody.innerHTML = `
        <p><strong>APPROVED QUOTA</strong><br>100 Units</p>
        <p><strong>SHIPPING ADDRESS:</strong><br>Toopai Fulfillment Center<br>Jl. Jend. Sudirman Kav 52-53<br>Jakarta Selatan 12190</p>
        <button id="sendFulfillmentBtn">📦 Send Fulfillment Request</button>
    `;
    taskModal.classList.add('active');
    document.getElementById('sendFulfillmentBtn')?.addEventListener('click', () => {
        closeModal();
        showToast('Sample request sent successfully!');
    }, { once: true });
}

function verifyApiAndMove(stageNum) {
    modalTitle.innerText = '🔌 Verify API & Move to Campaign';
    modalBody.innerHTML = `
        <p>Verify API integration for <strong>Brand Partner</strong></p>
        <p>API Status: <span style="color:#4ade80;">Ready to Connect</span></p>
        <button id="verifyApiBtn">✅ Verify & Launch Campaign</button>
    `;
    taskModal.classList.add('active');
    document.getElementById('verifyApiBtn')?.addEventListener('click', () => { closeModal(); completeStage(stageNum); }, { once: true });
}

function showLaunchModal(stageNum) {
    modalTitle.innerText = '🚀 Launch to Creator Network';
    modalBody.innerHTML = `
        <p>Launch campaign to creator network?</p>
        <p>Target: <strong>50 creators</strong><br>Est. GMV: Rp 125,000,000</p>
        <button id="launchBtn">🚀 Confirm Launch</button>
    `;
    taskModal.classList.add('active');
    document.getElementById('launchBtn')?.addEventListener('click', () => { closeModal(); completeStage(stageNum); }, { once: true });
}

function showRetentionModal(stageNum) {
    modalTitle.innerText = '📊 Generate Report & Upsell';
    modalBody.innerHTML = `
        <p><strong>Campaign Performance Report</strong></p>
        <div style="background:rgba(0,0,0,0.3); padding:16px; border-radius:20px; margin:12px 0;">
            <strong>Total GMV:</strong> Rp <?= number_format($total_gmv, 0, ',', '.') ?><br>
            <strong>ROAS:</strong> <?= $roas ?>x<br>
            <strong>Creators Engaged:</strong> 45<br>
            <strong>Upsell Opportunity:</strong> Increase commission to 20%
        </div>
        <button id="completeBtn">✅ Complete & Finish</button>
    `;
    taskModal.classList.add('active');
    document.getElementById('completeBtn')?.addEventListener('click', () => { closeModal(); completeStage(stageNum); }, { once: true });
}

// ========== BIND TASK BUTTONS ==========
function bindTaskButtons() {
    document.querySelectorAll('.task-btn').forEach(btn => {
        btn.removeEventListener('click', btn._listener);
        const handler = (e) => {
            e.preventDefault();
            const action = btn.getAttribute('data-action');
            const stageNum = parseInt(btn.closest('.stage-card').getAttribute('data-stage'));
            
            if (stageNum > 1 && !isStageCompleted(stageNum - 1)) {
                showToast(`Please complete Stage ${stageNum - 1} first!`);
                return;
            }
            
            const handlers = {
                'hunting': () => showHuntingModal(stageNum),
                'outreach': () => showOutreachModal(stageNum),
                'deal': () => showDealModal(stageNum),
                'sample': () => showSampleModal(stageNum),
                'verify': () => verifyApiAndMove(stageNum),
                'launch': () => showLaunchModal(stageNum),
                'retention': () => showRetentionModal(stageNum)
            };
            (handlers[action] || (() => showToast('Task coming soon!')))();
        };
        btn._listener = handler;
        btn.addEventListener('click', handler);
    });
}

// ========== TAB SWITCHING ==========
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const tabId = btn.getAttribute('data-tab');
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById(tabId).classList.add('active');
        btn.classList.add('active');
    });
});

// ========== SIDEBAR LEADERBOARD ==========
const openLeaderboard = document.getElementById('openLeaderboardBtn');
const leaderboardSidebar = document.getElementById('leaderboardSidebar');
const closeSidebar = document.getElementById('closeSidebar');

if (openLeaderboard) {
    openLeaderboard.addEventListener('click', () => {
        leaderboardSidebar.style.visibility = 'visible';
        leaderboardSidebar.style.opacity = '1';
        document.querySelector('#leaderboardSidebar .sidebar').style.transform = 'translateX(0)';
    });
}
if (closeSidebar) {
    closeSidebar.addEventListener('click', () => {
        leaderboardSidebar.style.visibility = 'hidden';
        leaderboardSidebar.style.opacity = '0';
        document.querySelector('#leaderboardSidebar .sidebar').style.transform = 'translateX(100%)';
    });
}
if (leaderboardSidebar) {
    leaderboardSidebar.addEventListener('click', (e) => {
        if (e.target === leaderboardSidebar) {
            leaderboardSidebar.style.visibility = 'hidden';
            leaderboardSidebar.style.opacity = '0';
            document.querySelector('#leaderboardSidebar .sidebar').style.transform = 'translateX(100%)';
        }
    });
}

// ========== SCOUT MODAL ==========
function showScoutModal() {
    modalTitle.innerText = '🤖 AI Scout & Match Brand';
    modalBody.innerHTML = `
        <p>Enter a brand target to find the best products for our creators.</p>
        <label>BRAND NAME</label>
        <input type="text" id="scoutBrand" placeholder="e.g., Erha Skincare">
        <label>TARGET CATEGORY</label>
        <select id="scoutCategory">
            <option>Electronics</option><option>Beauty</option><option>Fashion</option><option>Food</option><option>Audio</option><option>Home & Living</option>
        </select>
        <label>SHOPEE / TIKTOK SHOP LINK (OPTIONAL)</label>
        <input type="url" id="scoutLink" placeholder="https://">
        <button id="initiateScanBtn">🚀 Initiate AI Scan → Start Task</button>
    `;
    taskModal.classList.add('active');
    
    document.getElementById('initiateScanBtn')?.addEventListener('click', async () => {
        const brand = document.getElementById('scoutBrand').value;
        if (!brand) { alert("Please enter brand name"); return; }
        
        const btn = document.getElementById('initiateScanBtn');
        btn.disabled = true;
        btn.textContent = 'Scanning...';
        
        try {
            const response = await fetch('<?= base_url("bd/scout_match_brand") ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    brand_name: brand,
                    category: document.getElementById('scoutCategory').value,
                    shop_link: document.getElementById('scoutLink').value
                })
            });
            const result = await response.json();
            
            if (result.success) {
                closeModal();
                showToast(`✅ Brand ${brand} added to Hunting stage!`);
                setTimeout(() => location.reload(), 1500);
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            alert('Failed to scan brand. Please try again.');
        } finally {
            btn.disabled = false;
        }
    }, { once: true });
}

document.getElementById('scoutBtn')?.addEventListener('click', showScoutModal);

// ========== RECENT ORDERS PAGINATION ==========
const allOrders = <?= json_encode(array_slice(array_reverse($orders), 0, 100)) ?>;
let currentPage = 0;
const perPage = 10;
const tbody = document.getElementById('recentOrdersBody');
const prevBtn = document.getElementById('prevPageBtn');
const nextBtn = document.getElementById('nextPageBtn');
const orderRangeSpan = document.getElementById('orderRange');

function formatNumber(num) { return num.toLocaleString('id-ID'); }
function escapeHtml(str) { if (!str) return ''; return str.replace(/[&<>]/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;'}[m])); }

function renderOrders() {
    const start = currentPage * perPage;
    const end = start + perPage;
    const pageOrders = allOrders.slice(start, end);
    tbody.innerHTML = '';
    pageOrders.forEach(order => {
        tbody.innerHTML += `<tr>
            <td>${order.create_date_local || '-'}</td>
            <td>${escapeHtml(order.product_name?.substring(0, 45) || '-')}</td>
            <td>${escapeHtml(order.creator_username || 'Unknown')}</td>
            <td>Rp ${formatNumber(order.affiliate_gmv)}</td>
            <td>Rp ${formatNumber(order.actual_affiliate_commission)}</td>
        </tr>`;
    });
    orderRangeSpan.innerText = `${start+1}-${Math.min(end, allOrders.length)}`;
    prevBtn.disabled = currentPage === 0;
    nextBtn.disabled = end >= allOrders.length;
}

if (prevBtn) prevBtn.addEventListener('click', () => { if (currentPage > 0) { currentPage--; renderOrders(); } });
if (nextBtn) nextBtn.addEventListener('click', () => { if ((currentPage+1)*perPage < allOrders.length) { currentPage++; renderOrders(); } });
renderOrders();

// ========== INITIALIZE ==========
document.addEventListener('DOMContentLoaded', () => {
    bindTaskButtons();
    updateStageUI();
});
</script>
</body>
</html>