<!-- file: application/views/is/performance.php -->
<?php
/**
 * IS Performance Analytics View
 * Data source: campaign_creator_performance + affiliate_orders
 * Last updated: 2026-05-08
 */
?>
<style>
    /* ========== GLOBAL VARIABLES ========== */
    :root {
        --purple: #8b5cf6;
        --purple-glow: rgba(139, 92, 246, 0.1);
        --cyan: #06b6d4;
        --blue: #3b82f6;
        --bg-primary: #0f172a;
        --bg-card: #1e293b;
        --bg-elevated: #334155;
        --border: #334155;
        --border-light: #475569;
        --text-primary: #f1f5f9;
        --text-secondary: #cbd5e1;
        --text-muted: #94a3b8;
        --glow-purple: 0 0 20px rgba(139, 92, 246, 0.3);
        --transition: all 0.2s ease;
    }
    
    /* ========== PAGE HEADER ========== */
    .page-header {
        margin-bottom: 24px;
    }
    
    .back-to-dashboard {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--purple);
        text-decoration: none;
        font-size: 13px;
        margin-bottom: 12px;
        transition: var(--transition);
    }
    
    .back-to-dashboard:hover {
        color: var(--cyan);
        transform: translateX(-3px);
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
    
    .page-title i {
        background: none;
        -webkit-background-clip: unset;
        background-clip: unset;
        color: var(--purple);
    }
    
    .page-subtitle {
        color: var(--text-muted);
        font-size: 12px;
    }
    
    .info-badge {
        background: rgba(139, 92, 246, 0.15);
        border-radius: 40px;
        padding: 4px 12px;
        font-size: 10px;
        color: var(--purple);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-left: 12px;
    }
    
    /* ========== FILTER BAR ========== */
    .filter-bar {
        margin-bottom: 24px;
    }
    
    .date-filter-container {
        background: var(--bg-card);
        border-radius: 20px;
        padding: 12px 20px;
        border: 1px solid var(--border);
    }
    
    .date-filter {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    
    .date-filter label {
        color: var(--text-muted);
        font-size: 11px;
    }
    
    .date-input {
        background: var(--bg-elevated);
        border: 1px solid var(--border);
        color: var(--text-primary);
        font-size: 12px;
        padding: 8px 12px;
        border-radius: 8px;
        outline: none;
    }
    
    .date-input:focus {
        border-color: var(--purple);
    }
    
    .btn-filter {
        background: var(--purple-glow);
        border: 1px solid var(--purple);
        color: var(--purple);
        padding: 8px 16px;
        border-radius: 40px;
        cursor: pointer;
        font-size: 12px;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-filter:hover {
        background: var(--purple);
        color: white;
    }
    
    .btn-filter.primary {
        background: linear-gradient(135deg, var(--purple), var(--blue));
        color: white;
        border: none;
    }
    
    .btn-filter.primary:hover {
        transform: translateY(-2px);
        box-shadow: var(--glow-purple);
    }
    
    /* ========== STATS ROW ========== */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    
    .stat-card {
        background: var(--bg-card);
        border-radius: 20px;
        padding: 20px;
        text-align: center;
        border: 1px solid var(--border);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }
    
    .stat-card:hover {
        border-color: var(--purple);
        transform: translateY(-2px);
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--purple), var(--cyan));
    }
    
    .stat-icon {
        font-size: 32px;
        margin-bottom: 8px;
    }
    
    .stat-value {
        font-size: 28px;
        font-weight: 700;
        background: linear-gradient(135deg, #4ade80, #22c55e);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }
    
    .stat-label {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 6px;
    }
    
    .stat-growth {
        font-size: 10px;
        margin-top: 8px;
        padding: 4px 8px;
        border-radius: 20px;
        display: inline-block;
    }
    
    .stat-growth.positive {
        background: rgba(16, 185, 129, 0.15);
        color: #4ade80;
    }
    
    .stat-growth.negative {
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
    }
    
    .stat-period {
        font-size: 9px;
        color: var(--text-muted);
        margin-top: 8px;
    }
    
    /* ========== SECTION CARD ========== */
    .section-card {
        background: var(--bg-card);
        border-radius: 20px;
        padding: 20px;
        margin-bottom: 24px;
        border: 1px solid var(--border);
    }
    
    .section-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 16px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .section-title i {
        color: var(--purple);
    }
    
    .section-title .badge {
        background: var(--bg-elevated);
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 10px;
        color: var(--text-muted);
        margin-left: auto;
    }
    
    /* ========== CHART STYLES ========== */
    .chart-container {
        margin-top: 16px;
        overflow-x: auto;
    }
    
    .bar-chart {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        min-width: 600px;
        padding: 20px 0;
    }
    
    .bar-item {
        flex: 1;
        text-align: center;
        position: relative;
    }
    
    .bar {
        background: linear-gradient(180deg, var(--purple), var(--blue));
        border-radius: 8px 8px 4px 4px;
        min-height: 4px;
        transition: height 0.3s ease;
        cursor: pointer;
    }
    
    .bar:hover {
        opacity: 0.8;
    }
    
    .bar-label {
        font-size: 9px;
        color: var(--text-muted);
        margin-top: 8px;
    }
    
    .bar-value {
        font-size: 9px;
        color: #4ade80;
        margin-top: 4px;
    }
    
    .chart-tooltip {
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: #1e293b;
        color: #4ade80;
        padding: 4px 8px;
        border-radius: 8px;
        font-size: 10px;
        white-space: nowrap;
        z-index: 100;
        margin-bottom: 5px;
        border: 1px solid var(--purple);
        pointer-events: none;
    }
    
    /* ========== TABLE STYLES ========== */
    .performance-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .performance-table th,
    .performance-table td {
        padding: 12px 8px;
        text-align: left;
        border-bottom: 1px solid var(--border);
        font-size: 12px;
    }
    
    .performance-table th {
        color: var(--purple);
        font-weight: 600;
        background: var(--bg-elevated);
    }
    
    .performance-table tr:hover {
        background: rgba(139, 92, 246, 0.05);
    }
    
    .gmv-cell {
        color: #4ade80;
        font-weight: 600;
    }
    
    .commission-cell {
        color: #fbbf24;
        font-weight: 600;
    }
    
    .rank-badge {
        display: inline-block;
        width: 24px;
        height: 24px;
        line-height: 24px;
        text-align: center;
        border-radius: 50%;
        font-size: 12px;
        font-weight: 600;
    }
    
    .rank-badge.gold {
        background: linear-gradient(135deg, #ffd700, #ffb700);
        color: #0a0e1a;
    }
    
    .rank-badge.silver {
        background: linear-gradient(135deg, #c0c0c0, #a8a8a8);
        color: #0a0e1a;
    }
    
    .rank-badge.bronze {
        background: linear-gradient(135deg, #cd7f32, #b8651a);
        color: white;
    }
    
    /* ========== EMPTY STATE ========== */
    .empty-state {
        text-align: center;
        padding: 40px;
        color: var(--text-muted);
    }
    
    .empty-state i {
        font-size: 48px;
        margin-bottom: 12px;
        display: block;
        color: var(--purple);
    }
    
    /* ========== LOADING ========== */
    .loading {
        text-align: center;
        padding: 40px;
        color: var(--text-muted);
    }
    
    .loading i {
        font-size: 32px;
        color: var(--purple);
        margin-bottom: 12px;
        display: inline-block;
    }
    
    /* ========== PERIOD COMPARISON ========== */
    .comparison-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 16px;
    }
    
    .comparison-card {
        background: var(--bg-elevated);
        border-radius: 16px;
        padding: 16px;
        transition: var(--transition);
    }
    
    .comparison-card:hover {
        transform: translateY(-2px);
        border: 1px solid var(--purple);
    }
    
    .comparison-label {
        color: var(--text-muted);
        font-size: 11px;
        margin-bottom: 8px;
    }
    
    .comparison-value {
        font-size: 22px;
        font-weight: 700;
        color: #4ade80;
    }
    
    .comparison-change {
        font-size: 11px;
        margin-top: 6px;
    }
    
    .comparison-change.positive {
        color: #4ade80;
    }
    
    .comparison-change.negative {
        color: #ef4444;
    }
    
    .comparison-previous {
        font-size: 10px;
        color: var(--text-muted);
        margin-top: 4px;
    }
    
    /* ========== EXPORT BUTTON ========== */
    .export-btn {
        background: transparent;
        border: 1px solid var(--border);
        color: var(--text-secondary);
        padding: 6px 14px;
        border-radius: 40px;
        cursor: pointer;
        font-size: 11px;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    .export-btn:hover {
        background: var(--purple-glow);
        border-color: var(--purple);
        color: var(--purple);
    }
    
    /* ========== RESPONSIVE ========== */
    @media (max-width: 768px) {
        .stats-row {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        
        .performance-table {
            font-size: 10px;
        }
        
        .performance-table th,
        .performance-table td {
            padding: 8px 4px;
        }
        
        .date-filter {
            flex-direction: column;
            align-items: stretch;
        }
        
        .date-input {
            width: 100%;
        }
        
        .section-title {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .section-title .badge {
            margin-left: 0;
        }
    }
</style>

<!-- ========== PAGE HEADER ========== -->
<div class="page-header">
    <div>
        
        <h1 class="page-title">
            <i class="fas fa-chart-line"></i> Performance Analytics
            <span class="info-badge">
                <i class="fas fa-database"></i> Data from campaign_creator_performance
            </span>
        </h1>
        <p class="page-subtitle">
            <i class="fas fa-chart-simple"></i> Monitor affiliate performance | 
            <i class="fas fa-sync-alt"></i> Auto-synced every hour by cron
        </p>
    </div>
</div>

<!-- ========== FILTER BAR ========== -->
<div class="filter-bar">
    <div class="date-filter-container">
        <div class="date-filter">
            <label><i class="fas fa-calendar-alt"></i> Periode:</label>
            <input type="date" id="startDateFilter" class="date-input" value="<?= $start_date ?>">
            <span style="color:var(--text-muted);">→</span>
            <input type="date" id="endDateFilter" class="date-input" value="<?= $end_date ?>">
            <button id="applyDateFilterBtn" class="btn-filter primary">
                <i class="fas fa-check"></i> Terapkan
            </button>
            <button id="resetDateFilterBtn" class="btn-filter">
                <i class="fas fa-undo-alt"></i> Hari Ini
            </button>
            <button id="weekFilterBtn" class="btn-filter">
                <i class="fas fa-calendar-week"></i> 7 Hari
            </button>
            <button id="monthFilterBtn" class="btn-filter">
                <i class="fas fa-calendar-alt"></i> 30 Hari
            </button>
        </div>
    </div>
</div>

<!-- ========== STATS CARDS ========== -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-value" id="totalGMV">Rp <?= number_format($total_stats->total_gmv ?? 0, 0, ',', '.') ?></div>
        <div class="stat-label">Total GMV</div>
        <div class="stat-growth <?= $gmv_growth >= 0 ? 'positive' : 'negative' ?>" id="gmvGrowth">
            <i class="fas fa-<?= $gmv_growth >= 0 ? 'arrow-up' : 'arrow-down' ?>"></i>
            <?= number_format(abs($gmv_growth), 1) ?>% vs periode sebelumnya
        </div>
        <div class="stat-period" id="statPeriod">
            <i class="far fa-calendar-alt"></i> <?= date('d M Y', strtotime($start_date)) ?> - <?= date('d M Y', strtotime($end_date)) ?>
        </div>
    </div>
    <!--<div class="stat-card">-->
    <!--    <div class="stat-icon">💸</div>-->
    <!--    <div class="stat-value" id="totalCommission">Rp <?= number_format($total_stats->total_commission ?? 0, 0, ',', '.') ?></div>-->
    <!--    <div class="stat-label">Total Estimated Commission</div>-->
    <!--    <div class="stat-period">Based on order data</div>-->
    <!--</div>-->
    <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-value" id="totalOrders"><?= number_format($total_stats->total_orders ?? 0) ?></div>
        <div class="stat-label">Total Orders</div>
        <div class="stat-growth <?= $orders_growth >= 0 ? 'positive' : 'negative' ?>" id="ordersGrowth">
            <i class="fas fa-<?= $orders_growth >= 0 ? 'arrow-up' : 'arrow-down' ?>"></i>
            <?= number_format(abs($orders_growth), 1) ?>% vs periode sebelumnya
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-value" id="activeCreators"><?= number_format($creators_count ?? 0) ?></div>
        <div class="stat-label">Active Creators</div>
        <div class="stat-period">With orders in period</div>
    </div>
</div>

<!-- ========== DAILY PERFORMANCE CHART ========== -->
<div class="section-card">
    <div class="section-title">
        <i class="fas fa-chart-line"></i> Daily Performance Trend
        <span class="badge">Last 30 days</span>
        <button class="export-btn" id="exportDailyBtn" style="margin-left: auto;">
            <i class="fas fa-download"></i> Export
        </button>
    </div>
    <?php if (!empty($daily_performance)): ?>
    <div class="chart-container">
        <div class="bar-chart" id="dailyChart">
            <?php 
            $max_gmv = max(array_column($daily_performance, 'daily_gmv')) ?: 1;
            foreach ($daily_performance as $day): 
                $height = ($day->daily_gmv / $max_gmv) * 100;
                $height = max($height, 5);
                $date = date('d M', strtotime($day->date));
                $dayName = date('D', strtotime($day->date));
                $tooltip = "Rp " . number_format($day->daily_gmv, 0, ',', '.') . " | " . $day->daily_orders . " orders";
            ?>
            <div class="bar-item">
                <div class="bar" style="height: <?= $height ?>px;" title="<?= $tooltip ?>"></div>
                <div class="bar-label"><?= $date ?></div>
                <div class="bar-value"><?= number_format($day->daily_gmv / 1000, 0) ?>k</div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Summary Stats -->
    <?php 
    $last_7_days = array_slice($daily_performance, 0, min(7, count($daily_performance)));
    $total_7_days_gmv = array_sum(array_column($last_7_days, 'daily_gmv'));
    $total_7_days_orders = array_sum(array_column($last_7_days, 'daily_orders'));
    $avg_7_days_gmv = count($last_7_days) > 0 ? $total_7_days_gmv / count($last_7_days) : 0;
    ?>
    <div style="margin-top: 16px; padding-top: 12px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div>
            <span style="color: var(--text-muted);">Last 7 days:</span>
            <strong style="color: #4ade80; margin-left: 8px;">Rp <?= number_format($total_7_days_gmv, 0, ',', '.') ?></strong>
            <span style="color: var(--text-muted); margin-left: 12px;">(<?= number_format($total_7_days_orders) ?> orders)</span>
        </div>
        <div>
            <span style="color: var(--text-muted);">Average daily GMV:</span>
            <strong style="color: #fbbf24; margin-left: 8px;">Rp <?= number_format($avg_7_days_gmv, 0, ',', '.') ?></strong>
        </div>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-chart-line"></i>
        <p>No daily performance data available yet</p>
        <small>Data will appear after orders are synced from API</small>
    </div>
    <?php endif; ?>
</div>

<!-- ========== CAMPAIGN PERFORMANCE ========== -->
<div class="section-card">
    <div class="section-title">
        <i class="fas fa-bullhorn"></i> Campaign Performance
        <button class="export-btn" id="exportCampaignBtn" style="margin-left: auto;">
            <i class="fas fa-download"></i> Export
        </button>
    </div>
    <?php if (!empty($campaign_performance)): ?>
    <div style="overflow-x: auto;">
        <table class="performance-table" id="campaignTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Campaign Name</th>
                    <th>Creators</th>
                    <th>Orders</th>
                    <th>GMV</th>
                   
                    <th>ROAS</th>
                    <th>Avg GMV/Creator</th>
                </tr>
            </thead>
            <tbody>
                <?php $rank = 1; foreach ($campaign_performance as $camp): 
                    $avg_gmv_per_creator = $camp->total_creators > 0 ? $camp->total_gmv / $camp->total_creators : 0;
                    $roas = $camp->total_commission > 0 ? round($camp->total_gmv / $camp->total_commission, 2) : 0;
                ?>
                <tr>
                    <td><?= $rank++ ?></td>
                    <td><strong><?= htmlspecialchars($camp->campaign_name ?? $camp->campaign_id) ?></strong></td>
                    <td><?= number_format($camp->total_creators) ?></td>
                    <td><?= number_format($camp->total_orders) ?></td>
                    <td class="gmv-cell">Rp <?= number_format($camp->total_gmv, 0, ',', '.') ?></td>
                   
                    <td><?= $roas ?>x</td>
                    <td>Rp <?= number_format($avg_gmv_per_creator, 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-bullhorn"></i>
        <p>No campaign data available yet</p>
        <small>Campaigns will appear after orders are synced</small>
    </div>
    <?php endif; ?>
</div>

<!-- ========== TOP CREATOR PERFORMANCE ========== -->
<div class="section-card">
    <div class="section-title">
        <i class="fas fa-trophy"></i> Top Creator Performance
        <a href="<?= base_url('is/creators') ?>" class="btn-filter" style="margin-left: auto;">
            <i class="fas fa-users"></i> View All Creators
        </a>
    </div>
    <?php if (!empty($creator_performance)): ?>
    <div style="overflow-x: auto;">
        <table class="performance-table" id="creatorTable">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Creator</th>
                    <th>Active Campaigns</th>
                    <th>Orders</th>
                    <th>GMV</th>
                   
                    <th>AOV</th>
                </tr>
            </thead>
            <tbody>
                <?php $rank = 1; foreach ($creator_performance as $creator): 
                    $aov = $creator->total_orders > 0 ? $creator->total_gmv / $creator->total_orders : 0;
                    $rankClass = '';
                    if ($rank == 1) $rankClass = 'gold';
                    elseif ($rank == 2) $rankClass = 'silver';
                    elseif ($rank == 3) $rankClass = 'bronze';
                ?>
                <tr>
                    <td>
                        <?php if ($rankClass): ?>
                        <span class="rank-badge <?= $rankClass ?>"><?= $rank++ ?></span>
                        <?php else: ?>
                        <?= $rank++ ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong>@<?= htmlspecialchars($creator->creator_username) ?></strong>
                        <?php if ($creator->total_gmv > 10000000): ?>
                        <i class="fas fa-fire" style="color: #f59e0b; margin-left: 4px;" title="Hot Creator"></i>
                        <?php endif; ?>
                    </td>
                    <td><?= number_format($creator->total_campaigns) ?></td>
                    <td><?= number_format($creator->total_orders) ?></td>
                    <td class="gmv-cell">Rp <?= number_format($creator->total_gmv, 0, ',', '.') ?></td>
                   
                    <td>Rp <?= number_format($aov, 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-users"></i>
        <p>No creator performance data available yet</p>
        <small>Creators will appear after they generate sales</small>
    </div>
    <?php endif; ?>
</div>

<!-- ========== TOP PRODUCTS ========== -->
<div class="section-card">
    <div class="section-title">
        <i class="fas fa-box"></i> Top Selling Products
        <button class="export-btn" id="exportProductsBtn" style="margin-left: auto;">
            <i class="fas fa-download"></i> Export
        </button>
    </div>
    <?php if (!empty($top_products)): ?>
    <div style="overflow-x: auto;">
        <table class="performance-table" id="productsTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product Name</th>
                    <th>Creators</th>
                    <th>Orders</th>
                    <th>GMV</th>
                  
                </tr>
            </thead>
            <tbody>
                <?php $rank = 1; foreach ($top_products as $product): ?>
                <tr>
                    <td><?= $rank++ ?></td>
                    <td title="<?= htmlspecialchars($product->product_name ?? '') ?>">
                        <?= htmlspecialchars(substr($product->product_name ?? '', 0, 50)) ?>
                        <?php if (strlen($product->product_name ?? '') > 50): ?>...<?php endif; ?>
                    </td>
                    <td><?= number_format($product->total_creators) ?></td>
                    <td><?= number_format($product->total_orders) ?></td>
                    <td class="gmv-cell">Rp <?= number_format($product->total_gmv, 0, ',', '.') ?></td>
                   
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-box-open"></i>
        <p>No product data available yet</p>
        <small>Products will appear after orders are synced</small>
    </div>
    <?php endif; ?>
</div>

<!-- ========== PERIOD COMPARISON (30 days vs Previous) ========== -->
<div class="section-card">
    <div class="section-title">
        <i class="fas fa-chart-simple"></i> Period Comparison (Last 30 days vs Previous Period)
    </div>
    <?php 
    // Calculate current period (last 30 days)
    $current_period = $this->db->select('
            COALESCE(SUM(gmv), 0) as gmv,
            COUNT(DISTINCT order_id) as orders,
            COALESCE(SUM(estimated_commission), 0) as commission,
            COUNT(DISTINCT creator_username) as creators
        ')
        ->from('affiliate_orders')
        ->where('order_time >=', date('Y-m-d H:i:s', strtotime('-30 days')))
        ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
        ->get()
        ->row();
    
    // Calculate previous period (30-60 days ago)
    $previous_period = $this->db->select('
            COALESCE(SUM(gmv), 0) as gmv,
            COUNT(DISTINCT order_id) as orders,
            COALESCE(SUM(estimated_commission), 0) as commission,
            COUNT(DISTINCT creator_username) as creators
        ')
        ->from('affiliate_orders')
        ->where('order_time >=', date('Y-m-d H:i:s', strtotime('-60 days')))
        ->where('order_time <', date('Y-m-d H:i:s', strtotime('-30 days')))
        ->where('order_status NOT IN ("CANCELLED", "REFUNDED")')
        ->get()
        ->row();
    
    $gmv_growth_30 = $previous_period->gmv > 0 ? (($current_period->gmv - $previous_period->gmv) / $previous_period->gmv * 100) : 100;
    $orders_growth_30 = $previous_period->orders > 0 ? (($current_period->orders - $previous_period->orders) / $previous_period->orders * 100) : 100;
    $creators_growth_30 = $previous_period->creators > 0 ? (($current_period->creators - $previous_period->creators) / $previous_period->creators * 100) : 100;
    ?>
    <div class="comparison-grid">
        <div class="comparison-card">
            <div class="comparison-label"><i class="fas fa-chart-line"></i> GMV</div>
            <div class="comparison-value">Rp <?= number_format($current_period->gmv, 0, ',', '.') ?></div>
            <div class="comparison-change <?= $gmv_growth_30 >= 0 ? 'positive' : 'negative' ?>">
                <i class="fas fa-<?= $gmv_growth_30 >= 0 ? 'arrow-up' : 'arrow-down' ?>"></i>
                <?= number_format(abs($gmv_growth_30), 1) ?>% growth
            </div>
            <div class="comparison-previous">Previous: Rp <?= number_format($previous_period->gmv, 0, ',', '.') ?></div>
        </div>
        <div class="comparison-card">
            <div class="comparison-label"><i class="fas fa-shopping-cart"></i> Orders</div>
            <div class="comparison-value"><?= number_format($current_period->orders) ?></div>
            <div class="comparison-change <?= $orders_growth_30 >= 0 ? 'positive' : 'negative' ?>">
                <i class="fas fa-<?= $orders_growth_30 >= 0 ? 'arrow-up' : 'arrow-down' ?>"></i>
                <?= number_format(abs($orders_growth_30), 1) ?>% growth
            </div>
            <div class="comparison-previous">Previous: <?= number_format($previous_period->orders) ?> orders</div>
        </div>
        <div class="comparison-card">
            <div class="comparison-label"><i class="fas fa-users"></i> Active Creators</div>
            <div class="comparison-value"><?= number_format($current_period->creators) ?></div>
            <div class="comparison-change <?= $creators_growth_30 >= 0 ? 'positive' : 'negative' ?>">
                <i class="fas fa-<?= $creators_growth_30 >= 0 ? 'arrow-up' : 'arrow-down' ?>"></i>
                <?= number_format(abs($creators_growth_30), 1) ?>% growth
            </div>
            <div class="comparison-previous">Previous: <?= number_format($previous_period->creators) ?> creators</div>
        </div>
       
    </div>
</div>

<script>
// ========== BASE URL ==========
const baseUrlIS = '<?= base_url() ?>';

// ========== HELPER FUNCTIONS ==========
function formatNumber(num) {
    if (num === undefined || num === null) return '0';
    return Number(num).toLocaleString('id-ID');
}

function showToast(message, type = 'success') {
    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.style.cssText = 'position:fixed; bottom:20px; right:20px; z-index:10000;';
        document.body.appendChild(toastContainer);
    }
    
    const toast = document.createElement('div');
    toast.style.cssText = `
        background: ${type === 'success' ? '#10b981' : '#ef4444'};
        color: white;
        padding: 12px 20px;
        border-radius: 12px;
        margin-top: 10px;
        font-size: 13px;
        animation: slideIn 0.3s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        cursor: pointer;
    `;
    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
    toast.onclick = () => toast.remove();
    toastContainer.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}

// ========== CHART TOOLTIPS ==========
document.querySelectorAll('.bar').forEach(bar => {
    bar.addEventListener('mouseenter', function(e) {
        const value = this.getAttribute('title');
        if (value) {
            const tooltip = document.createElement('div');
            tooltip.className = 'chart-tooltip';
            tooltip.textContent = value;
            this.parentElement.appendChild(tooltip);
            
            this.addEventListener('mouseleave', () => tooltip.remove());
        }
    });
});

// ========== EXPORT FUNCTIONS ==========
function exportTableToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const rows = table.querySelectorAll('tr');
    let csv = [];
    
    for (let row of rows) {
        const cells = row.querySelectorAll('th, td');
        const rowData = [];
        for (let cell of cells) {
            let text = cell.innerText.replace(/,/g, ';').replace(/Rp /g, '');
            rowData.push(text);
        }
        csv.push(rowData.join(','));
    }
    
    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${filename}_${new Date().toISOString().split('T')[0]}.csv`;
    a.click();
    URL.revokeObjectURL(url);
    showToast(`Exported ${filename} successfully!`);
}

document.getElementById('exportDailyBtn')?.addEventListener('click', () => {
    exportTableToCSV('dailyChart', 'daily_performance');
});
document.getElementById('exportCampaignBtn')?.addEventListener('click', () => {
    exportTableToCSV('campaignTable', 'campaign_performance');
});
document.getElementById('exportProductsBtn')?.addEventListener('click', () => {
    exportTableToCSV('productsTable', 'top_products');
});

// ========== DATE FILTER FUNCTIONS ==========
const startDateInput = document.getElementById('startDateFilter');
const endDateInput = document.getElementById('endDateFilter');
const applyFilterBtn = document.getElementById('applyDateFilterBtn');
const resetFilterBtn = document.getElementById('resetDateFilterBtn');
const weekFilterBtn = document.getElementById('weekFilterBtn');
const monthFilterBtn = document.getElementById('monthFilterBtn');

function applyDateFilter() {
    const startDate = startDateInput.value;
    const endDate = endDateInput.value;
    
    if (!startDate || !endDate) {
        showToast('Please select both start and end dates', 'error');
        return;
    }
    
    if (startDate > endDate) {
        showToast('Start date cannot be after end date', 'error');
        return;
    }
    
    window.location.href = baseUrlIS + `is/performance?start_date=${startDate}&end_date=${endDate}`;
}

function resetDateFilter() {
    const today = new Date().toISOString().split('T')[0];
    window.location.href = baseUrlIS + `is/performance?start_date=${today}&end_date=${today}`;
}

function setWeekFilter() {
    const today = new Date();
    const weekAgo = new Date(today);
    weekAgo.setDate(today.getDate() - 6);
    
    const endDate = today.toISOString().split('T')[0];
    const startDate = weekAgo.toISOString().split('T')[0];
    
    window.location.href = baseUrlIS + `is/performance?start_date=${startDate}&end_date=${endDate}`;
}

function setMonthFilter() {
    const today = new Date();
    const monthAgo = new Date(today);
    monthAgo.setDate(today.getDate() - 29);
    
    const endDate = today.toISOString().split('T')[0];
    const startDate = monthAgo.toISOString().split('T')[0];
    
    window.location.href = baseUrlIS + `is/performance?start_date=${startDate}&end_date=${endDate}`;
}

if (applyFilterBtn) applyFilterBtn.addEventListener('click', applyDateFilter);
if (resetFilterBtn) resetFilterBtn.addEventListener('click', resetDateFilter);
if (weekFilterBtn) weekFilterBtn.addEventListener('click', setWeekFilter);
if (monthFilterBtn) monthFilterBtn.addEventListener('click', setMonthFilter);

// Set default date values
function setDefaultDates() {
    if (startDateInput && !startDateInput.value) {
        const today = new Date().toISOString().split('T')[0];
        startDateInput.value = today;
        endDateInput.value = today;
    }
}
setDefaultDates();

// Animation for slideIn
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
`;
document.head.appendChild(style);
</script>