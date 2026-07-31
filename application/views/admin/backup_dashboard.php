<div class="admin-dashboard">
    <div class="dashboard-header">
        <h1 class="dashboard-title">📊 Admin Dashboard</h1>
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
    
   
   <!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-content">
            <div class="stat-value" id="totalGmv">Rp <?= number_format($realtime['total_gmv'] ?? 0, 0, ',', '.') ?></div>
            <div class="stat-label">Total GMV</div>
            <div class="stat-change <?= ($realtime['gmv_growth'] ?? 0) >= 0 ? 'positive' : 'negative' ?>">
                <?= ($realtime['gmv_growth'] ?? 0) >= 0 ? '↑' : '↓' ?> <?= abs($realtime['gmv_growth'] ?? 0) ?>%
            </div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-content">
            <div class="stat-value" id="totalOrders"><?= number_format($realtime['total_orders'] ?? 0) ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
    </div>
    
    <!-- 🔥 PERUBAHAN: Total Estimated Commission -->
    <div class="stat-card">
        <div class="stat-icon">💸</div>
        <div class="stat-content">
            <div class="stat-value" id="totalEstimatedCommission">Rp <?= number_format($realtime['total_estimated_commission'] ?? 0, 0, ',', '.') ?></div>
            <div class="stat-label">Total Estimated Commission</div>
            <div class="stat-note">Estimated from orders</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">🎯</div>
        <div class="stat-content">
            <div class="stat-value" id="activeCampaigns"><?= number_format($realtime['active_campaigns'] ?? 0) ?></div>
            <div class="stat-label">Active Campaigns</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-content">
            <div class="stat-value" id="activeCreators"><?= number_format($realtime['active_creators'] ?? 0) ?></div>
            <div class="stat-label">Active Creators</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">⏳</div>
        <div class="stat-content">
            <div class="stat-value" id="queuePending"><?= number_format($realtime['queue_pending'] ?? 0) ?></div>
            <div class="stat-label">Queue Pending</div>
            <div class="stat-note">Awaiting retry</div>
        </div>
    </div>
</div>

    
    <!-- Campaign Performance Section -->
    <div class="section-card">
        <div class="section-header">
            <h2 class="section-title">📈 Campaign Performance</h2>
            <select id="campaignFilter" class="filter-select" onchange="filterCampaign()">
                <option value="all">All Campaigns</option>
                <?php foreach ($realtime['campaigns'] ?? [] as $camp): ?>
                <option value="<?= $camp->campaign_id ?>"><?= htmlspecialchars($camp->campaign_name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <!-- Campaigns Grid Section -->
<div class="campaigns-grid" id="campaignsGrid">
    <?php foreach ($realtime['campaigns'] ?? [] as $camp): 
        // Pastikan $camp adalah object dan memiliki properti yang diperlukan
        $campaign_id = $camp->campaign_id ?? '';
        $campaign_name = $camp->campaign_name ?? 'Unknown Campaign';
        $status = $camp->status ?? 'ONGOING';
        $actual_gmv = $camp->actual_gmv ?? 0;
        $actual_orders = $camp->actual_orders ?? 0;
        $total_creators = $camp->total_creators ?? 0;
        
        // Handle last_sync dengan aman
        $last_sync_time = null;
        if (isset($camp->last_sync)) {
            $last_sync_time = $camp->last_sync;
        } elseif (isset($camp->campaign_last_sync)) {
            $last_sync_time = $camp->campaign_last_sync;
        }
        $last_sync_display = $last_sync_time ? date('H:i', strtotime($last_sync_time)) : 'Never';
    ?>
    <div class="campaign-card" data-campaign-id="<?= htmlspecialchars($campaign_id) ?>" onclick="showCampaignDetail('<?= htmlspecialchars($campaign_id) ?>')">
        <div class="campaign-header">
            <h4 class="campaign-name"><?= htmlspecialchars($campaign_name) ?></h4>
            <span class="campaign-status <?= strtolower($status) ?>">
                <?= htmlspecialchars($status) ?>
            </span>
        </div>
        <div class="campaign-stats">
            <div class="campaign-stat">
                <span class="stat-label">GMV</span>
                <span class="stat-number">Rp <?= number_format($actual_gmv, 0, ',', '.') ?></span>
            </div>
            <div class="campaign-stat">
                <span class="stat-label">Orders</span>
                <span class="stat-number"><?= number_format($actual_orders) ?></span>
            </div>
            <div class="campaign-stat">
                <span class="stat-label">Creators</span>
                <span class="stat-number"><?= number_format($total_creators) ?></span>
            </div>
        </div>
        <div class="campaign-footer">
            <span class="last-sync-badge"><?= $last_sync_display ?></span>
        </div>
    </div>
    <?php endforeach; ?>
    
    <?php if (empty($realtime['campaigns'])): ?>
    <div class="empty-state">No active campaigns found</div>
    <?php endif; ?>
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
                                <td class="commission-cell">Rp <?= number_format($creator->total_commission ?? 0, 0, ',', '.') ?></td>
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
                <h2 class="section-title">🕒 Recent Orders</h2>
                <div class="orders-list" id="recentOrders">
                    <?php foreach ($realtime['recent_orders'] ?? [] as $order): ?>
                    <div class="order-item">
                        <div class="order-info">
                            <div class="order-product"><?= htmlspecialchars($order->product_name ?? 'Unknown') ?></div>
                            <div class="order-creator">by @<?= htmlspecialchars($order->creator_username ?? 'Unknown') ?></div>
                            <div class="order-time"><?= date('d M H:i', strtotime($order->order_time)) ?></div>
                        </div>
                        <div class="order-amount">
                            <div class="order-gmv">Rp <?= number_format($order->gmv ?? 0, 0, ',', '.') ?></div>
                            <div class="order-commission">+Rp <?= number_format($order->actual_commission ?? 0, 0, ',', '.') ?></div>
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
                <button class="btn-sync-small" onclick="manualSync()">
                    🔄 Trigger Manual Sync
                </button>
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
/* Admin Dashboard Styles */
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

.stat-card {
    background: #111827;
    border-radius: 20px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    border: 1px solid #2a3346;
    transition: all 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    border-color: #4ade80;
}

.stat-icon {
    font-size: 42px;
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 26px;
    font-weight: 700;
    color: #4ade80;
}

.stat-label {
    font-size: 12px;
    color: #94a3b8;
    margin-top: 4px;
}

.stat-change {
    font-size: 11px;
    margin-top: 4px;
}

.stat-change.positive {
    color: #4ade80;
}

.stat-change.negative {
    color: #ef4444;
}

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

.filter-select {
    background: #1e293b;
    border: 1px solid #2a3346;
    color: #e2e8f0;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 13px;
}

/* Campaigns Grid */
.campaigns-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
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
    font-size: 16px;
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

.campaign-status.completed {
    background: rgba(100, 116, 139, 0.2);
    color: #94a3b8;
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

/* Two Columns */
.two-columns {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 24px;
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

.creator-cell {
    font-weight: 500;
}

.gmv-cell, .commission-cell {
    color: #4ade80;
    font-weight: 500;
}

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

.order-item:last-child {
    border-bottom: none;
}

.order-product {
    font-weight: 500;
    color: #ffffff;
    font-size: 13px;
}

.order-creator {
    font-size: 11px;
    color: #94a3b8;
    margin-top: 2px;
}

.order-time {
    font-size: 10px;
    color: #64748b;
    margin-top: 2px;
}

.order-gmv {
    font-weight: 600;
    color: #4ade80;
    font-size: 14px;
    text-align: right;
}

.order-commission {
    font-size: 11px;
    color: #94a3b8;
    text-align: right;
}

/* Sync Status */
.sync-status {
    margin-bottom: 16px;
}

.sync-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #1e293b;
}

.sync-label {
    color: #94a3b8;
    font-size: 13px;
}

.sync-value {
    color: #e2e8f0;
    font-size: 13px;
    font-weight: 500;
}

.sync-value.warning {
    color: #fbbf24;
}

.sync-value.success {
    color: #4ade80;
}

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

.btn-sync-small:hover {
    background: rgba(74, 222, 128, 0.1);
}

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

.modal-header h2 {
    color: #ffffff;
    margin: 0;
    font-size: 20px;
}

.close {
    color: #94a3b8;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: #ffffff;
}

.modal-body {
    padding: 20px;
    max-height: 70vh;
    overflow-y: auto;
}

.loading {
    text-align: center;
    padding: 40px;
    color: #94a3b8;
}

/* Responsive */
@media (max-width: 1024px) {
    .two-columns {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    }
}

@media (max-width: 768px) {
    .admin-dashboard {
        padding: 16px;
    }
    
    .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .campaigns-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let gmvChart = null;

// Initialize chart on page load
document.addEventListener('DOMContentLoaded', function() {
    initChart();
    startAutoRefresh();
});

function initChart() {
    const ctx = document.getElementById('gmvChart').getContext('2d');
    
    // Get data from PHP
    const breakdownData = <?= json_encode($realtime['gmv_breakdown'] ?? []) ?>;
    
    // Group by date
    const dailyData = {};
    breakdownData.forEach(item => {
        if (!dailyData[item.date]) {
            dailyData[item.date] = 0;
        }
        dailyData[item.date] += parseFloat(item.daily_gmv);
    });
    
    const dates = Object.keys(dailyData).sort();
    const values = dates.map(date => dailyData[date]);
    
    gmvChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: dates.map(d => d.substring(5)), // MM-DD
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
                legend: {
                    labels: { color: '#94a3b8' }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Rp ' + context.raw.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                y: {
                    ticks: {
                        color: '#94a3b8',
                        callback: function(value) {
                            return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                        }
                    },
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

// Auto refresh every 30 seconds
let refreshInterval;
function startAutoRefresh() {
    refreshInterval = setInterval(refreshData, 30000);
}

function refreshData() {
    fetch('<?= base_url("dashboard/ajax_realtime_data") ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update stats
                document.getElementById('totalGmv').innerHTML = 'Rp ' + formatNumber(data.total_gmv);
                document.getElementById('totalOrders').innerHTML = formatNumber(data.total_orders);
                // 🔥 PERUBAHAN: Gunakan total_estimated_commission
                document.getElementById('totalEstimatedCommission').innerHTML = 'Rp ' + formatNumber(data.total_estimated_commission);
                document.getElementById('activeCampaigns').innerHTML = formatNumber(data.active_campaigns);
                document.getElementById('activeCreators').innerHTML = formatNumber(data.active_creators);
                document.getElementById('queuePending').innerHTML = formatNumber(data.queue_pending);
                document.getElementById('lastSyncTime').innerHTML = data.last_sync ? new Date(data.last_sync).toLocaleTimeString() : 'Never';
                document.getElementById('serverTime').innerHTML = data.server_time;
                
                // Update top creators table
                updateTopCreators(data.top_creators);
                
                // Update recent orders
                updateRecentOrders(data.recent_orders);
                
                // Update campaigns grid
                updateCampaignsGrid(data.campaigns);
                
                // Update chart
                updateChart(data.gmv_breakdown);
            }
        })
        .catch(error => console.error('Error refreshing data:', error));
}


function formatNumber(num) {
    if (num === undefined || num === null) return '0';
    const number = parseFloat(num);
    if (isNaN(number)) return '0';
    return number.toLocaleString('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
}

function updateTopCreators(creators) {
    const tbody = document.getElementById('topCreatorsTable');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    (creators || []).forEach(creator => {
        tbody.innerHTML += `
            <tr>
                <td class="creator-cell">@${escapeHtml(creator.creator_username)}</td>
                <td class="gmv-cell">Rp ${formatNumber(creator.total_gmv)}</td>
                <td>${formatNumber(creator.total_orders)}</td>
                <td class="commission-cell">Rp ${formatNumber(creator.total_estimated_commission)}</td>
            </tr>
        `;
    });
}

function updateRecentOrders(orders) {
    const container = document.getElementById('recentOrders');
    if (!container) return;
    
    container.innerHTML = '';
    (orders || []).forEach(order => {
        // Gunakan estimated_commission untuk tampilan
        const displayCommission = order.estimated_commission > 0 ? order.estimated_commission : (order.actual_commission || 0);
        container.innerHTML += `
            <div class="order-item">
                <div class="order-info">
                    <div class="order-product">${escapeHtml(order.product_name || 'Unknown')}</div>
                    <div class="order-creator">by @${escapeHtml(order.creator_username || 'Unknown')}</div>
                    <div class="order-time">${formatDate(order.order_time)}</div>
                </div>
                <div class="order-amount">
                    <div class="order-gmv">Rp ${formatNumber(order.gmv)}</div>
                    <div class="order-commission">+Rp ${formatNumber(displayCommission)}</div>
                </div>
            </div>
        `;
    });
}

function updateCampaignsGrid(campaigns) {
    const grid = document.getElementById('campaignsGrid');
    if (!grid) return;
    
    if (!campaigns || campaigns.length === 0) {
        grid.innerHTML = '<div class="empty-state">No active campaigns found</div>';
        return;
    }
    
    grid.innerHTML = '';
    campaigns.forEach(camp => {
        // Handle last_sync dengan aman
        let lastSyncDisplay = 'Never';
        if (camp.last_sync) {
            lastSyncDisplay = new Date(camp.last_sync).toLocaleTimeString();
        } else if (camp.campaign_last_sync) {
            lastSyncDisplay = new Date(camp.campaign_last_sync).toLocaleTimeString();
        }
        
        grid.innerHTML += `
            <div class="campaign-card" data-campaign-id="${escapeHtml(camp.campaign_id)}" onclick="showCampaignDetail('${escapeHtml(camp.campaign_id)}')">
                <div class="campaign-header">
                    <h4 class="campaign-name">${escapeHtml(camp.campaign_name || 'Unknown Campaign')}</h4>
                    <span class="campaign-status ${(camp.status || 'ONGOING').toLowerCase()}">${camp.status || 'ONGOING'}</span>
                </div>
                <div class="campaign-stats">
                    <div class="campaign-stat">
                        <span class="stat-label">GMV</span>
                        <span class="stat-number">Rp ${formatNumber(camp.actual_gmv || 0)}</span>
                    </div>
                    <div class="campaign-stat">
                        <span class="stat-label">Orders</span>
                        <span class="stat-number">${formatNumber(camp.actual_orders || 0)}</span>
                    </div>
                    <div class="campaign-stat">
                        <span class="stat-label">Creators</span>
                        <span class="stat-number">${formatNumber(camp.total_creators || 0)}</span>
                    </div>
                </div>
                <div class="campaign-footer">
                    <span class="last-sync-badge">${lastSyncDisplay}</span>
                </div>
            </div>
        `;
    });
}

function updateChart(breakdownData) {
    if (!gmvChart) return;
    
    const dailyData = {};
    (breakdownData || []).forEach(item => {
        if (!dailyData[item.date]) {
            dailyData[item.date] = 0;
        }
        dailyData[item.date] += parseFloat(item.daily_gmv);
    });
    
    const dates = Object.keys(dailyData).sort();
    const values = dates.map(date => dailyData[date]);
    
    gmvChart.data.labels = dates.map(d => d.substring(5));
    gmvChart.data.datasets[0].data = values;
    gmvChart.update();
}

function showCampaignDetail(campaignId) {
    const modal = document.getElementById('campaignModal');
    const modalBody = document.getElementById('modalBody');
    
    if (!modal || !modalBody) {
        console.error('Modal elements not found');
        return;
    }
    
    // Tampilkan loading state
    modal.style.display = 'block';
    modalBody.innerHTML = '<div class="loading">Loading campaign details...</div>';
    
    // Fetch data dengan error handling yang lebih baik
    fetch(`<?= base_url('dashboard/ajax_campaign_detail/') ?>${encodeURIComponent(campaignId)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Campaign detail response:', data);
            
            if (!data.success) {
                throw new Error(data.message || 'Failed to load campaign details');
            }
            
            displayCampaignDetail(data);
        })
        .catch(error => {
            console.error('Error fetching campaign detail:', error);
            modalBody.innerHTML = `
                <div class="error-state">
                    <div class="error-icon">⚠️</div>
                    <div class="error-message">Failed to load campaign details</div>
                    <div class="error-detail">${error.message}</div>
                    <button class="btn-retry" onclick="showCampaignDetail('${campaignId}')">Retry</button>
                </div>
            `;
        });
}

function displayCampaignDetail(data) {
    const modalBody = document.getElementById('modalBody');
    
    if (!modalBody) return;
    
    const campaign = data.campaign;
    
    // Format angka dengan aman
    const formatNumber = (num) => {
        if (num === undefined || num === null) return '0';
        const number = parseFloat(num);
        if (isNaN(number)) return '0';
        return number.toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    };
    
    const formatCurrency = (num) => {
        return 'Rp ' + formatNumber(num);
    };
    
    // 🔥 HAPUS BAGIAN TOP PRODUCTS - hanya tampilkan creators
    let creatorsHtml = '';
    if (data.top_creators && data.top_creators.length > 0) {
        data.top_creators.forEach(creator => {
            // Gunakan estimated_commission jika ada
            const commission = creator.total_commission || creator.total_estimated_commission || 0;
            creatorsHtml += `
                <tr>
                    <td class="creator-cell">@${escapeHtml(creator.creator_username)}</td>
                    <td class="gmv-cell">${formatCurrency(creator.total_gmv)}</td>
                    <td>${formatNumber(creator.total_orders)}</td>
                    <td class="commission-cell">${formatCurrency(commission)}</td>
                </tr>
            `;
        });
    } else {
        creatorsHtml = '<tr><td colspan="4" class="empty-state">No creators found</td></tr>';
    }
    
    modalBody.innerHTML = `
        <div class="campaign-detail">
            <div class="detail-section">
                <h3>Campaign Overview</h3>
                <div class="detail-grid">
                    <div><strong>Campaign ID:</strong> ${escapeHtml(campaign.campaign_id)}</div>
                    <div><strong>Status:</strong> <span class="campaign-status ${(campaign.status || 'ongoing').toLowerCase()}">${campaign.status || 'ONGOING'}</span></div>
                    <div><strong>Total GMV:</strong> <span class="gmv-cell">${formatCurrency(campaign.total_gmv)}</span></div>
                    <div><strong>Total Orders:</strong> ${formatNumber(campaign.total_orders)}</div>
                    <div><strong>Total Creators:</strong> ${formatNumber(campaign.total_creators)}</div>
                    <div><strong>Last Sync:</strong> ${campaign.last_sync || 'Never'}</div>
                </div>
            </div>
            
            <!-- 🔥 BAGIAN TOP PRODUCTS DIHAPUS -->
            
            <div class="detail-section">
                <h3>Top Creators</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Creator</th>
                            <th>GMV</th>
                            <th>Orders</th>
                            <th>Commission</th>
                        </tr>
                    </thead>
                    <tbody>${creatorsHtml}</tbody>
                </table>
            </div>
        </div>
    `;
}
function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function closeModal() {
    document.getElementById('campaignModal').style.display = 'none';
}

function triggerSync() {
    if (confirm('Trigger manual sync? This may take a few minutes.')) {
        const btn = document.querySelector('.btn-sync');
        btn.textContent = '🔄 Syncing...';
        btn.disabled = true;
        
        fetch('<?= base_url("dashboard/trigger_sync") ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message || 'Sync completed');
            setTimeout(refreshData, 5000);
        })
        .catch(error => alert('Sync failed: ' + error))
        .finally(() => {
            btn.textContent = '🔄 Sync Now';
            btn.disabled = false;
        });
    }
}

function manualSync() {
    triggerSync();
}

function filterCampaign() {
    const filter = document.getElementById('campaignFilter').value;
    const cards = document.querySelectorAll('.campaign-card');
    
    cards.forEach(card => {
        if (filter === 'all' || card.dataset.campaignId === filter) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('campaignModal');
    if (event.target === modal) {
        closeModal();
    }
}
</script>