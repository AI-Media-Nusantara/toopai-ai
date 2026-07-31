<div class="admin-dashboard">
    <div class="dashboard-header">
        <h1 class="dashboard-title">Dashboard Realtime</h1>
        <div class="dashboard-actions">
            <button class="btn-refresh" onclick="refreshData()">
                🔄 Refresh Data
            </button>
            <button class="btn-sync" onclick="triggerSync()">
                🔄 Sync Now
            </button>
            <div class="last-sync">
                Last sync: <span id="lastSyncTime"><?= isset($realtime['last_sync']) ? date('H:i:s', strtotime($realtime['last_sync'])) : 'Never' ?></span>
                <br><small>Server: <span id="serverTime"><?= $realtime['server_time'] ?? date('Y-m-d H:i:s') ?></span></small>
            </div>
        </div>
    </div>

    <!-- Stats Cards - Row 1: GMV & Commission -->
    <div class="stats-grid">
        <!-- Card 1: Total GMV -->
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-content">
                <div class="stat-value" id="totalGmv">Rp <?= number_format($realtime['today_gmv'] ?? 0, 0, ',', '.') ?></div>
                <div class="stat-label">GMV Hari Ini</div>
                <div class="stat-yesterday">Kemarin (s/d <?= date('H:i') ?>): Rp <?= number_format($realtime['yesterday_gmv'] ?? 0, 0, ',', '.') ?></div>
                <div class="stat-change <?= ($realtime['gmv_growth'] ?? 0) >= 0 ? 'positive' : 'negative' ?>">
                    <?php if (($realtime['gmv_growth'] ?? 0) > 0): ?>
                        ▲ <?= $realtime['gmv_growth'] ?>% vs kemarin
                    <?php elseif (($realtime['gmv_growth'] ?? 0) < 0): ?>
                        ▼ <?= abs($realtime['gmv_growth']) ?>% vs kemarin
                    <?php else: ?>
                        — 0% vs kemarin
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Orders -->
        <div class="stat-card">
            <div class="stat-icon">📦</div>
            <div class="stat-content">
                <div class="stat-value" id="totalOrders"><?= number_format($realtime['today_orders'] ?? 0) ?></div>
                <div class="stat-label">Orders Hari Ini</div>
                <div class="stat-yesterday">Kemarin (s/d <?= date('H:i') ?>): <?= number_format($realtime['yesterday_orders'] ?? 0) ?> orders</div>
                <div class="stat-change <?= ($realtime['order_growth'] ?? 0) >= 0 ? 'positive' : 'negative' ?>">
                    <?php if (($realtime['order_growth'] ?? 0) > 0): ?>
                        ▲ <?= $realtime['order_growth'] ?>% vs kemarin
                    <?php elseif (($realtime['order_growth'] ?? 0) < 0): ?>
                        ▼ <?= abs($realtime['order_growth']) ?>% vs kemarin
                    <?php else: ?>
                        — 0% vs kemarin
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Card 3: Estimated Commission -->
        <div class="stat-card">
            <div class="stat-icon">💵</div>
            <div class="stat-content">
                <div class="stat-value" id="totalEstimatedCommission">Rp <?= number_format($realtime['today_estimated_commission'] ?? 0, 0, ',', '.') ?></div>
                <div class="stat-label">Est. Commission Hari Ini</div>
                <div class="stat-yesterday">Kemarin (s/d <?= date('H:i') ?>): Rp <?= number_format($realtime['yesterday_estimated_commission'] ?? 0, 0, ',', '.') ?></div>
                <div class="stat-change <?= ($realtime['commission_growth'] ?? 0) >= 0 ? 'positive' : 'negative' ?>">
                    <?php if (($realtime['commission_growth'] ?? 0) > 0): ?>
                        ▲ <?= $realtime['commission_growth'] ?>% vs kemarin
                    <?php elseif (($realtime['commission_growth'] ?? 0) < 0): ?>
                        ▼ <?= abs($realtime['commission_growth']) ?>% vs kemarin
                    <?php else: ?>
                        — 0% vs kemarin
                    <?php endif; ?>
                </div>
            </div>
        </div>

       
      

        <!-- Card 5: Active Creators -->
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <div class="stat-value" id="activeCreators"><?= number_format($realtime['today_creators'] ?? 0) ?></div>
                <div class="stat-label">Active Creators Hari Ini</div>
                <div class="stat-yesterday">Kemarin (s/d <?= date('H:i') ?>): <?= number_format($realtime['yesterday_creators'] ?? 0) ?> creators</div>
                <div class="stat-change <?= ($realtime['creator_growth'] ?? 0) >= 0 ? 'positive' : 'negative' ?>">
                    <?php if (($realtime['creator_growth'] ?? 0) > 0): ?>
                        ▲ <?= $realtime['creator_growth'] ?>% vs kemarin
                    <?php elseif (($realtime['creator_growth'] ?? 0) < 0): ?>
                        ▼ <?= abs($realtime['creator_growth']) ?>% vs kemarin
                    <?php else: ?>
                        — 0% vs kemarin
                    <?php endif; ?>
                </div>
            </div>
        </div>

       
    </div>

    <!-- Row 2: Brand & Creator Metrics -->
  <div class="stats-grid-second">
    <!-- Card: Brand Masuk -->
    <div class="stat-card">
        <div class="stat-icon">🏢</div>
        <div class="stat-content">
            <div class="stat-value" id="brandsJoined"><?= number_format($realtime['brands_joined_today'] ?? 0) ?></div>
            <div class="stat-label">Brand Masuk Hari Ini</div>
            <div class="stat-yesterday">Kemarin (s/d <?= date('H:i') ?>): <?= number_format($realtime['brands_joined_yesterday'] ?? 0) ?></div>
            <div class="stat-change <?= ($realtime['brand_growth'] ?? 0) >= 0 ? 'positive' : 'negative' ?>">
                <?php if (($realtime['brand_growth'] ?? 0) > 0): ?>
                    ▲ <?= $realtime['brand_growth'] ?>% vs kemarin
                <?php elseif (($realtime['brand_growth'] ?? 0) < 0): ?>
                    ▼ <?= abs($realtime['brand_growth']) ?>% vs kemarin
                <?php else: ?>
                    — 0% vs kemarin
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Card: Creator Dapat Link -->
    <div class="stat-card">
        <div class="stat-icon">🔗</div>
        <div class="stat-content">
            <div class="stat-value" id="creatorsWithLinks"><?= number_format($realtime['creators_with_links_today'] ?? 0) ?></div>
            <div class="stat-label">Link Terkirim</div>
            <div class="stat-yesterday">Kemarin (s/d <?= date('H:i') ?>): <?= number_format($realtime['creators_with_links_yesterday'] ?? 0) ?></div>
            <div class="stat-change <?= ($realtime['links_growth'] ?? 0) >= 0 ? 'positive' : 'negative' ?>">
                <?php if (($realtime['links_growth'] ?? 0) > 0): ?>
                    ▲ <?= $realtime['links_growth'] ?>% vs kemarin
                <?php elseif (($realtime['links_growth'] ?? 0) < 0): ?>
                    ▼ <?= abs($realtime['links_growth']) ?>% vs kemarin
                <?php else: ?>
                    — 0% vs kemarin
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Card: Creator Aktifkan Link -->
    <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-content">
            <div class="stat-value" id="creatorsActivated"><?= number_format($realtime['creators_activated_today'] ?? 0) ?></div>
            <div class="stat-label">Creator Aktifkan Link</div>
            <div class="stat-yesterday">Kemarin (s/d <?= date('H:i') ?>): <?= number_format($realtime['creators_activated_yesterday'] ?? 0) ?></div>
            <div class="stat-change <?= ($realtime['activated_growth'] ?? 0) >= 0 ? 'positive' : 'negative' ?>">
                <?php if (($realtime['activated_growth'] ?? 0) > 0): ?>
                    ▲ <?= $realtime['activated_growth'] ?>% vs kemarin
                <?php elseif (($realtime['activated_growth'] ?? 0) < 0): ?>
                    ▼ <?= abs($realtime['activated_growth']) ?>% vs kemarin
                <?php else: ?>
                    — 0% vs kemarin
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Card: Creator Posting Konten -->
    <div class="stat-card">
        <div class="stat-icon">📹</div>
        <div class="stat-content">
            <div class="stat-value" id="creatorsWithContent"><?= number_format($realtime['creators_with_content_today'] ?? 0) ?></div>
            <div class="stat-label">Creator Posting</div>
            <div class="stat-yesterday">Kemarin (s/d <?= date('H:i') ?>): <?= number_format($realtime['creators_with_content_yesterday'] ?? 0) ?></div>
            <div class="stat-change <?= ($realtime['content_growth'] ?? 0) >= 0 ? 'positive' : 'negative' ?>">
                <?php if (($realtime['content_growth'] ?? 0) > 0): ?>
                    ▲ <?= $realtime['content_growth'] ?>% vs kemarin
                <?php elseif (($realtime['content_growth'] ?? 0) < 0): ?>
                    ▼ <?= abs($realtime['content_growth']) ?>% vs kemarin
                <?php else: ?>
                    — 0% vs kemarin
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

    <!-- Two Column Layout -->
    <div class="two-columns">
        <!-- Left Column: Charts -->
        <div class="left-column">
            <div class="section-card">
                <h2 class="section-title">📊 GMV Trend (Last 30 Days)</h2>
                <canvas id="gmvChart" height="250"></canvas>
            </div>

            <div class="section-card">
                <h2 class="section-title">🏆 Top Performing Creators</h2>
                <div class="creators-list">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Creator</th>
                                <th>GMV</th>
                                <th>Orders</th>
                                <th>Commission</th>
                            </tr>
                        </thead>
                        <tbody id="topCreatorsTable">
                            <?php foreach ($realtime['top_creators'] ?? [] as $creator): ?>
                            <tr>
                                <td class="creator-cell">@<?= htmlspecialchars($creator->creator_username) ?></td>
                                <td class="gmv-cell">Rp <?= number_format($creator->total_gmv ?? 0, 0, ',', '.') ?></td>
                                <td><?= number_format($creator->total_orders ?? 0) ?></td>
                                <td class="commission-cell">Rp <?= number_format($creator->total_estimated_commission ?? 0, 0, ',', '.') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Recent Orders -->
        <div class="right-column">
            <div class="section-card">
                <h2 class="section-title">🕒 Recent Valid Orders (7 Hari)</h2>
                <div class="orders-list" id="recentOrders">
                    <?php foreach ($realtime['recent_orders'] ?? [] as $order): ?>
                    <div class="order-item">
                        <div class="order-info">
                            <div class="order-product"><?= htmlspecialchars(substr($order->product_name ?? 'Unknown', 0, 50)) ?></div>
                            <div class="order-creator">by @<?= htmlspecialchars($order->creator_username ?? 'Unknown') ?></div>
                            <div class="order-time"><?= date('d M H:i', strtotime($order->order_time)) ?></div>
                        </div>
                        <div class="order-amount">
                            <div class="order-gmv">Rp <?= number_format($order->gmv ?? 0, 0, ',', '.') ?></div>
                            <div class="order-commission">+Rp <?= number_format($order->estimated_commission ?? 0, 0, ',', '.') ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Campaigns Section -->
             <div class="section-header">
        <h2 class="section-title">🎯 Active Campaigns today</h2>
        <div class="section-totals">
            <?php 
            $total_campaign_gmv = 0;
$total_campaign_orders = 0;
$total_campaign_creators = 0;
foreach ($realtime['campaigns'] ?? [] as $camp) {
    $total_campaign_gmv += $camp->actual_gmv ?? 0;
    $total_campaign_orders += $camp->actual_orders ?? 0;
    $total_campaign_creators += $camp->actual_creators ?? 0;  // 🔥 PERBAIKI
            }
            ?>
            <div class="total-item">
                <span class="total-value">Rp <?= number_format($total_campaign_gmv, 0, ',', '.') ?></span>
                <span class="total-label">Total GMV</span>
            </div>
            <div class="total-item">
                <span class="total-value"><?= number_format($total_campaign_orders) ?></span>
                <span class="total-label">Orders</span>
            </div>
            <div class="total-item">
                <span class="total-value"><?= number_format($total_campaign_creators) ?></span>
                <span class="total-label">Creators</span>
            </div>
        </div>
    </div>
    <div class="campaigns-grid" id="campaignsGrid">
        <?php foreach ($realtime['campaigns'] ?? [] as $camp): ?>
        <div class="campaign-card" data-campaign-id="<?= htmlspecialchars($camp->campaign_id) ?>" onclick="showCampaignDetail('<?= htmlspecialchars($camp->campaign_id) ?>')">
            <div class="campaign-header">
                <h4 class="campaign-name"><?= htmlspecialchars($camp->campaign_name) ?></h4>
                <span class="campaign-status <?= strtolower($camp->status ?? 'ongoing') ?>"><?= $camp->status ?? 'ONGOING' ?></span>
            </div>
            <div class="campaign-stats">
                <div class="campaign-stat">
                    <span class="stat-label">GMV</span>
                    <span class="stat-number">Rp <?= number_format($camp->actual_gmv ?? 0, 0, ',', '.') ?></span>
                </div>
                <div class="campaign-stat">
                    <span class="stat-label">Orders</span>
                    <span class="stat-number"><?= number_format($camp->actual_orders ?? 0) ?></span>
                </div>
                <div class="campaign-stat">
                    <span class="stat-label">Creators</span>
                    <span class="stat-number"><?= number_format($camp->actual_creators ?? 0) ?></span>
                </div>
            </div>
            <div class="campaign-footer">
                <span class="last-sync-badge"><?= $camp->last_sync ? date('H:i', strtotime($camp->last_sync)) : 'Never' ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>


            <!-- Sync Status Card -->
            <div class="section-card">
                <h2 class="section-title">🔄 Sync Status</h2>
                <div class="sync-status" id="syncStatus">
                    <div class="sync-item">
                        <span class="sync-label">Last Full Sync:</span>
                        <span class="sync-value" id="lastFullSync"><?= $realtime['last_sync'] ?? 'Never' ?></span>
                    </div>
                    <div class="sync-item">
                        <span class="sync-label">Queue Status:</span>
                        <span class="sync-value <?= ($realtime['queue_pending'] ?? 0) > 0 ? 'warning' : 'success' ?>">
                            <?= ($realtime['queue_pending'] ?? 0) > 0 ? $realtime['queue_pending'] . ' pending' : 'All clear' ?>
                        </span>
                    </div>
                    <div class="sync-item">
                        <span class="sync-label">Server Time:</span>
                        <span class="sync-value"><?= date('Y-m-d H:i:s') ?> (WIB)</span>
                    </div>
                </div>
                <button class="btn-sync-small" onclick="manualSync()">🔄 Trigger Manual Sync</button>
            </div>
        </div>
    </div>
</div>

<!-- Campaign Detail Modal -->
<div id="campaignModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Campaign Details</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body" id="modalBody">
            <div class="loading">Loading...</div>
        </div>
    </div>
</div>

<style>
/* ========================================
   ADMIN DASHBOARD STYLES
   ======================================== */

.admin-dashboard {
    padding: 24px;
    background: linear-gradient(135deg, #0a0e1a 0%, #0f1420 100%);
    min-height: calc(100vh - 60px);
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 16px;
}

.dashboard-title {
    font-size: 28px;
    font-weight: 700;
    color: #ffffff;
    margin: 0;
}

.dashboard-actions {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.btn-refresh, .btn-sync {
    background: #1e293b;
    border: 1px solid #2a3346;
    color: #e2e8f0;
    padding: 8px 20px;
    border-radius: 40px;
    cursor: pointer;
    font-size: 13px;
    transition: all 0.2s;
}

.btn-refresh:hover, .btn-sync:hover {
    background: #2a3346;
    border-color: #4ade80;
    color: #4ade80;
}

.last-sync {
    background: #111827;
    padding: 8px 16px;
    border-radius: 12px;
    font-size: 12px;
    color: #94a3b8;
    text-align: right;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 28px;
}

.stats-grid-second {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 28px;
}

.stat-card {
    background: #111827;
    border-radius: 16px;
    padding: 20px;
    display: flex;
    align-items: flex-start;
    gap: 16px;
    border: 1px solid #2a3346;
    transition: all 0.2s;
    min-height: 110px;
}

.stat-card:hover {
    border-color: #4ade80;
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
}

.stat-icon {
    font-size: 36px;
    line-height: 1;
    flex-shrink: 0;
}

.stat-content {
    flex: 1;
    min-width: 0;
}

.stat-value {
    font-size: 26px;
    font-weight: 700;
    color: #4ade80;
    line-height: 1.2;
}

.stat-label {
    color: #9aaebe;
    font-size: 13px;
    margin-top: 2px;
}

.stat-yesterday {
    color: #64748b;
    font-size: 12px;
    margin-top: 4px;
    line-height: 1.3;
}

.stat-change {
    font-size: 11px;
    font-weight: 500;
    margin-top: 6px;
    padding: 2px 0;
}

.stat-change.positive { color: #4ade80; }
.stat-change.negative { color: #f87171; }
.stat-change.neutral { color: #94a3b8; }

.stat-note {
    font-size: 10px;
    color: #64748b;
    margin-top: 4px;
}

/* Section Card */
.section-card {
    background: #111827;
    border-radius: 20px;
    padding: 20px;
    border: 1px solid #2a3346;
    margin-bottom: 24px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 12px;
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    color: #ffffff;
    margin: 0;
}

/* Two Columns */
.two-columns {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 24px;
}

/* Campaigns Grid */
.campaigns-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
}

.campaign-card {
    background: #0a0e1a;
    border-radius: 16px;
    padding: 16px;
    border: 1px solid #2a3346;
    cursor: pointer;
    transition: all 0.2s;
}

.campaign-card:hover {
    border-color: #4ade80;
    transform: translateY(-2px);
}

.campaign-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.campaign-name {
    font-size: 14px;
    font-weight: 600;
    color: #ffffff;
    margin: 0;
}

.campaign-status {
    font-size: 10px;
    padding: 3px 8px;
    border-radius: 20px;
}

.campaign-status.ongoing {
    background: rgba(74, 222, 128, 0.2);
    color: #4ade80;
}

.campaign-stats {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
}

.campaign-stat {
    text-align: center;
    flex: 1;
}

.campaign-stat .stat-label {
    font-size: 10px;
    color: #64748b;
    display: block;
}

.campaign-stat .stat-number {
    font-size: 14px;
    font-weight: 600;
    color: #e2e8f0;
}

.campaign-footer {
    text-align: right;
    border-top: 1px solid #2a3346;
    padding-top: 10px;
}

.last-sync-badge {
    font-size: 10px;
    color: #64748b;
}

/* Data Table */
.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    text-align: left;
    padding: 12px 8px;
    color: #94a3b8;
    font-size: 12px;
    font-weight: 500;
    border-bottom: 1px solid #2a3346;
}

.data-table td {
    padding: 12px 8px;
    color: #e2e8f0;
    font-size: 13px;
    border-bottom: 1px solid #1e293b;
}

.creator-cell { font-weight: 500; }
.gmv-cell, .commission-cell { color: #4ade80; font-weight: 500; }

/* Orders List */
.orders-list {
    max-height: 400px;
    overflow-y: auto;
}

.order-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #1e293b;
}

.order-item:last-child { border-bottom: none; }

.order-product {
    font-weight: 500;
    color: #ffffff;
    font-size: 13px;
}

.order-creator { font-size: 11px; color: #94a3b8; margin-top: 2px; }
.order-time { font-size: 10px; color: #64748b; margin-top: 2px; }

.order-gmv {
    font-weight: 600;
    color: #4ade80;
    font-size: 14px;
    text-align: right;
}

.order-commission { font-size: 11px; color: #94a3b8; text-align: right; }

/* Sync Status */
.sync-status { margin-bottom: 16px; }

.sync-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #1e293b;
}

.sync-label { color: #94a3b8; font-size: 13px; }
.sync-value { color: #e2e8f0; font-size: 13px; font-weight: 500; }
.sync-value.warning { color: #fbbf24; }
.sync-value.success { color: #4ade80; }

.btn-sync-small {
    background: transparent;
    border: 1px solid #4ade80;
    color: #4ade80;
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 12px;
    width: 100%;
    transition: all 0.2s;
}

.btn-sync-small:hover { background: rgba(74, 222, 128, 0.1); }

/* Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.7);
}

.modal-content {
    background: #111827;
    margin: 5% auto;
    padding: 0;
    width: 90%;
    max-width: 800px;
    border-radius: 20px;
    border: 1px solid #2a3346;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #2a3346;
}

.modal-header h2 { color: #ffffff; margin: 0; font-size: 20px; }
.close { color: #94a3b8; font-size: 28px; font-weight: bold; cursor: pointer; }
.close:hover { color: #ffffff; }

.modal-body { padding: 20px; max-height: 70vh; overflow-y: auto; }
.loading { text-align: center; padding: 40px; color: #94a3b8; }

/* Responsive */
@media (max-width: 1024px) {
    .two-columns { grid-template-columns: 1fr; }
    .stats-grid { grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }
}

@media (max-width: 768px) {
    .admin-dashboard { padding: 16px; }
    .dashboard-header { flex-direction: column; align-items: flex-start; }
    .campaigns-grid { grid-template-columns: 1fr; }
    .stat-value { font-size: 22px; }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let gmvChart = null;

document.addEventListener('DOMContentLoaded', function() {
    initChart();
    startAutoRefresh();
});

function initChart() {
    const ctx = document.getElementById('gmvChart').getContext('2d');
    const breakdownData = <?= json_encode($realtime['gmv_breakdown'] ?? []) ?>;
    
    const dailyData = {};
    breakdownData.forEach(item => {
        if (!dailyData[item.date]) dailyData[item.date] = 0;
        dailyData[item.date] += parseFloat(item.daily_gmv);
    });
    
    const dates = Object.keys(dailyData).sort();
    const values = dates.map(date => dailyData[date]);
    
    gmvChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: dates.map(d => d.substring(5)),
            datasets: [{
                label: 'GMV (Rp)',
                data: values,
                borderColor: '#4ade80',
                backgroundColor: 'rgba(74, 222, 128, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#4ade80',
                pointBorderColor: '#0a0e1a',
                pointRadius: 3,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { labels: { color: '#94a3b8' } },
                tooltip: { callbacks: { label: ctx => 'Rp ' + ctx.raw.toLocaleString('id-ID') } }
            },
            scales: {
                y: {
                    ticks: { color: '#94a3b8', callback: v => 'Rp ' + (v / 1000000).toFixed(1) + 'M' },
                    grid: { color: '#1e293b' }
                },
                x: {
                    ticks: { color: '#94a3b8' },
                    grid: { color: '#1e293b' }
                }
            }
        }
    });
}

let refreshInterval;
function startAutoRefresh() { refreshInterval = setInterval(refreshData, 30000); }

function refreshData() {
    fetch('<?= base_url("dashboard/ajax_realtime_data") ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('totalGmv').innerHTML = 'Rp ' + formatNumber(data.total_gmv || data.today_gmv);
                document.getElementById('totalOrders').innerHTML = formatNumber(data.total_orders || data.today_orders);
                document.getElementById('totalEstimatedCommission').innerHTML = 'Rp ' + formatNumber(data.total_estimated_commission || data.today_estimated_commission);
                document.getElementById('activeCreators').innerHTML = formatNumber(data.active_creators || data.today_creators);
                document.getElementById('activeCampaigns').innerHTML = formatNumber(data.active_campaigns);
                document.getElementById('brandsJoined').innerHTML = formatNumber(data.brands_joined_today || 0);
                document.getElementById('creatorsWithLinks').innerHTML = formatNumber(data.creators_with_links_today || 0);
                document.getElementById('creatorsActivated').innerHTML = formatNumber(data.creators_activated_today || 0);
                document.getElementById('creatorsWithContent').innerHTML = formatNumber(data.creators_with_content_today || 0);
                document.getElementById('lastSyncTime').innerHTML = data.last_sync ? new Date(data.last_sync).toLocaleTimeString() : 'Never';
                document.getElementById('serverTime').innerHTML = data.server_time;
                
                updateTopCreators(data.top_creators);
                updateRecentOrders(data.recent_orders);
                updateCampaignsGrid(data.campaigns);
                updateChart(data.gmv_breakdown);
            }
        })
        .catch(error => console.error('Error:', error));
}

function formatNumber(num) {
    if (num === undefined || num === null) return '0';
    const n = parseFloat(num);
    return isNaN(n) ? '0' : n.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}

function updateTopCreators(creators) {
    const tbody = document.getElementById('topCreatorsTable');
    if (!tbody) return;
    tbody.innerHTML = '';
    (creators || []).forEach(c => {
        tbody.innerHTML += `<tr>
            <td class="creator-cell">@${escapeHtml(c.creator_username)}</td>
            <td class="gmv-cell">Rp ${formatNumber(c.total_gmv)}</td>
            <td>${formatNumber(c.total_orders)}</td>
            <td class="commission-cell">Rp ${formatNumber(c.total_estimated_commission)}</td>
        </tr>`;
    });
}

function updateRecentOrders(orders) {
    const container = document.getElementById('recentOrders');
    if (!container) return;
    container.innerHTML = '';
    (orders || []).forEach(o => {
        const comm = o.estimated_commission > 0 ? o.estimated_commission : (o.actual_commission || 0);
        container.innerHTML += `<div class="order-item">
            <div class="order-info">
                <div class="order-product">${escapeHtml((o.product_name || 'Unknown').substring(0, 50))}</div>
                <div class="order-creator">by @${escapeHtml(o.creator_username || 'Unknown')}</div>
                <div class="order-time">${formatDate(o.order_time)}</div>
            </div>
            <div class="order-amount">
                <div class="order-gmv">Rp ${formatNumber(o.gmv)}</div>
                <div class="order-commission">+Rp ${formatNumber(comm)}</div>
            </div>
        </div>`;
    });
}

function updateCampaignsGrid(campaigns) {
    const grid = document.getElementById('campaignsGrid');
    if (!grid) return;
    if (!campaigns || campaigns.length === 0) {
        grid.innerHTML = '<div class="empty-state">No active campaigns</div>';
        return;
    }
    grid.innerHTML = '';
    campaigns.forEach(c => {
        grid.innerHTML += `<div class="campaign-card" onclick="showCampaignDetail('${escapeHtml(c.campaign_id)}')">
            <div class="campaign-header">
                <h4 class="campaign-name">${escapeHtml(c.campaign_name || 'Unknown')}</h4>
                <span class="campaign-status ${(c.status || 'ongoing').toLowerCase()}">${c.status || 'ONGOING'}</span>
            </div>
            <div class="campaign-stats">
                <div class="campaign-stat"><span class="stat-label">GMV</span><span class="stat-number">Rp ${formatNumber(c.actual_gmv)}</span></div>
                <div class="campaign-stat"><span class="stat-label">Orders</span><span class="stat-number">${formatNumber(c.actual_orders)}</span></div>
                <div class="campaign-stat"><span class="stat-label">Creators</span><span class="stat-number">${formatNumber(c.actual_creators)}</span></div>
            </div>
            <div class="campaign-footer"><span class="last-sync-badge">${c.last_sync ? new Date(c.last_sync).toLocaleTimeString() : 'Never'}</span></div>
        </div>`;
    });
}

function updateChart(breakdownData) {
    if (!gmvChart) return;
    const dailyData = {};
    (breakdownData || []).forEach(item => {
        if (!dailyData[item.date]) dailyData[item.date] = 0;
        dailyData[item.date] += parseFloat(item.daily_gmv);
    });
    const dates = Object.keys(dailyData).sort();
    gmvChart.data.labels = dates.map(d => d.substring(5));
    gmvChart.data.datasets[0].data = dates.map(d => dailyData[d]);
    gmvChart.update();
}

function showCampaignDetail(campaignId) {
    const modal = document.getElementById('campaignModal');
    const modalBody = document.getElementById('modalBody');
    modal.style.display = 'block';
    modalBody.innerHTML = '<div class="loading">Loading...</div>';
    
    fetch(`<?= base_url('dashboard/ajax_campaign_detail/') ?>${encodeURIComponent(campaignId)}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) throw new Error(data.message);
            const c = data.campaign;
            let creatorsHtml = '';
            (data.top_creators || []).forEach(cr => {
                creatorsHtml += `<tr>
                    <td class="creator-cell">@${escapeHtml(cr.creator_username)}</td>
                    <td class="gmv-cell">Rp ${formatNumber(cr.total_gmv)}</td>
                    <td>${formatNumber(cr.total_orders)}</td>
                    <td class="commission-cell">Rp ${formatNumber(cr.total_commission)}</td>
                </tr>`;
            });
            modalBody.innerHTML = `<div class="campaign-detail">
                <div class="detail-section">
                    <h3>Campaign Overview</h3>
                    <div class="detail-grid">
                        <div><strong>ID:</strong> ${escapeHtml(c.campaign_id)}</div>
                        <div><strong>Status:</strong> <span class="campaign-status ${(c.status||'ongoing').toLowerCase()}">${c.status||'ONGOING'}</span></div>
                        <div><strong>GMV:</strong> <span class="gmv-cell">Rp ${formatNumber(c.total_gmv)}</span></div>
                        <div><strong>Orders:</strong> ${formatNumber(c.total_orders)}</div>
                        <div><strong>Creators:</strong> ${formatNumber(c.total_creators)}</div>
                    </div>
                </div>
                <div class="detail-section">
                    <h3>Top Creators</h3>
                    <table class="data-table"><thead><tr><th>Creator</th><th>GMV</th><th>Orders</th><th>Commission</th></tr></thead><tbody>${creatorsHtml || '<tr><td colspan="4">No data</td></tr>'}</tbody></table>
                </div>
            </div>`;
        })
        .catch(err => { modalBody.innerHTML = `<div class="error-state">⚠️ ${err.message}</div>`; });
}

function closeModal() { document.getElementById('campaignModal').style.display = 'none'; }

function triggerSync() {
    if (confirm('Trigger manual sync?')) {
        const btn = document.querySelector('.btn-sync');
        btn.textContent = '🔄 Syncing...'; btn.disabled = true;
        fetch('<?= base_url("dashboard/trigger_sync") ?>', { method: 'POST' })
            .then(r => r.json())
            .then(d => { alert(d.message); setTimeout(refreshData, 5000); })
            .catch(e => alert('Failed: ' + e))
            .finally(() => { btn.textContent = '🔄 Sync Now'; btn.disabled = false; });
    }
}

function manualSync() { triggerSync(); }

function formatDate(d) {
    if (!d) return '';
    return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
}

function escapeHtml(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

window.onclick = function(e) { if (e.target === document.getElementById('campaignModal')) closeModal(); };
</script>