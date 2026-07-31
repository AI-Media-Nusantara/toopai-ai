<div class="app">
    <section class="header-row">
        <div class="welcome"><h1>Welcome back, Mr.<?= htmlspecialchars($this->session->userdata('username') ?: 'Administrator') ?> </h1></div>
        <div class="controls">
            <button class="pill"><i class="fas fa-calendar"></i><?= date('F d, Y') ?></button>
            <button class="pill" onclick="refreshData()"><i class="fas fa-rotate"></i>Refresh</button>
            <button class="pill primary" onclick="triggerSync()"><i class="fas fa-arrows-rotate"></i>Sync Now</button>
            <div class="sync-info"><div>Last sync: <span id="lastSyncTime"><?= isset($realtime['last_sync']) ? date('H:i:s', strtotime($realtime['last_sync'])) : 'Never' ?></span></div><div>Server: <span id="serverTime"><?= $realtime['server_time'] ?? date('Y-m-d H:i:s') ?></span></div></div>
        </div>
    </section>

    <section class="dashboard-grid">
        <div class="top-metrics">
            <article class="card metric-card">
                <div class="icon-box icon-purple"><i class="fas fa-box"></i></div>
                <div class="metric-copy"><small>Orders Today</small><strong id="totalOrders"><?= number_format($realtime['today_orders'] ?? 0) ?></strong><div class="metric-sub">
    <span>
        Yesterday: <?= number_format($realtime['yesterday_orders'] ?? 0) ?>
    </span>

    <span class="delta <?= ($realtime['order_growth'] ?? 0) >= 0 ? 'up' : 'down' ?>">
        <?= ($realtime['order_growth'] ?? 0) >= 0 ? '↑' : '↓' ?>
        <?= abs($realtime['order_growth'] ?? 0) ?>%
    </span>
</div></div>
                <canvas id="ordersSpark" class="sparkline"></canvas>
            </article>
            <article class="card metric-card gmv">
                <div class="icon-box icon-money"><i class="fas fa-wallet"></i></div>
                <div class="metric-copy"><small>GMV Today</small><strong id="totalGmv">Rp <?= number_format($realtime['today_gmv'] ?? 0, 0, ',', '.') ?></strong><div class="metric-sub">
    <span>
        Yesterday: Rp <?= number_format($realtime['yesterday_gmv'] ?? 0, 0, ',', '.') ?>
    </span>

    <span class="delta <?= ($realtime['gmv_growth'] ?? 0) >= 0 ? 'up' : 'down' ?>">
        <?= ($realtime['gmv_growth'] ?? 0) >= 0 ? '↑' : '↓' ?>
        <?= abs($realtime['gmv_growth'] ?? 0) ?>%
    </span>
</div></div>
                <canvas id="gmvSpark" class="sparkline"></canvas>
            </article>
            <article class="card metric-card">
                <div class="icon-box icon-teal"><i class="fas fa-money-bill-wave"></i></div>
                <div class="metric-copy"><small>Estimated Commission Today</small><strong id="totalEstimatedCommission">Rp <?= number_format($realtime['today_estimated_commission'] ?? 0, 0, ',', '.') ?></strong><div class="metric-sub">
    <span>
        Yesterday: Rp <?= number_format($realtime['yesterday_estimated_commission'] ?? 0, 0, ',', '.') ?>
    </span>

    <span class="delta <?= ($realtime['commission_growth'] ?? 0) >= 0 ? 'up' : 'down' ?>">
        <?= ($realtime['commission_growth'] ?? 0) >= 0 ? '↑' : '↓' ?>
        <?= abs($realtime['commission_growth'] ?? 0) ?>%
    </span>
</div></div>
                <canvas id="commissionSpark" class="sparkline"></canvas>
            </article>
        </div>

        <div class="secondary-metrics">
            <article class="card secondary-card"><div class="icon-box icon-orange"><i class="fas fa-building"></i></div><div class="secondary-copy"><small>Joined Brand</small><strong id="brandsJoined"><?= number_format($realtime['brands_joined_today'] ?? 0) ?></strong><div class="secondary-meta">
    <span>
        Yesterday: <?= number_format($realtime['brands_joined_yesterday'] ?? 0) ?>
    </span>

    <span class="delta <?= ($realtime['brand_growth'] ?? 0) >= 0 ? 'up' : 'down' ?>">
        <?= ($realtime['brand_growth'] ?? 0) >= 0 ? '↑' : '↓' ?>
        <?= abs($realtime['brand_growth'] ?? 0) ?>%
    </span>
</div></div></article>
            <article class="card secondary-card"><div class="icon-box icon-blue"><i class="fas fa-paper-plane"></i></div><div class="secondary-copy"><small>Links Sent</small><strong id="creatorsWithLinks"><?= number_format($realtime['creators_with_links_today'] ?? 0) ?></strong><div class="secondary-meta">
    <span>
        Yesterday: <?= number_format($realtime['creators_with_links_yesterday'] ?? 0) ?>
    </span>

    <span class="delta <?= ($realtime['links_growth'] ?? 0) >= 0 ? 'up' : 'down' ?>">
        <?= ($realtime['links_growth'] ?? 0) >= 0 ? '↑' : '↓' ?>
        <?= abs($realtime['links_growth'] ?? 0) ?>%
    </span>
</div></div></article>
            <article class="card secondary-card"><div class="icon-box icon-green"><i class="fas fa-link"></i></div><div class="secondary-copy"><small>Joined Creator</small><strong id="creatorsActivated"><?= number_format($realtime['creators_activated_today'] ?? 0) ?></strong><div class="secondary-meta">
    <span>
        Yesterday: <?= number_format($realtime['creators_activated_yesterday'] ?? 0) ?>
    </span>

    <span class="delta <?= ($realtime['activated_growth'] ?? 0) >= 0 ? 'up' : 'down' ?>">
        <?= ($realtime['activated_growth'] ?? 0) >= 0 ? '↑' : '↓' ?>
        <?= abs($realtime['activated_growth'] ?? 0) ?>%
    </span>
</div></div></article>
            <article class="card secondary-card"><div class="icon-box icon-pink"><i class="fas fa-video"></i></div><div class="secondary-copy"><small>Creator with Posts</small><strong id="creatorsWithContent"><?= number_format($realtime['creators_with_content_today'] ?? 0) ?></strong><div class="secondary-meta">
    <span>
        Yesterday: <?= number_format($realtime['creators_with_content_yesterday'] ?? 0) ?>
    </span>

    <span class="delta <?= ($realtime['content_growth'] ?? 0) >= 0 ? 'up' : 'down' ?>">
        <?= ($realtime['content_growth'] ?? 0) >= 0 ? '↑' : '↓' ?>
        <?= abs($realtime['content_growth'] ?? 0) ?>%
    </span>
</div></div></article>
        </div>

        <div class="content-grid">
            <section class="card panel chart-card">
                <div class="panel-header"><div class="panel-title"><i class="fas fa-chart-line"></i>GMV Trend (Last 30 Days)</div><button class="mini-select">Last 30 Days</button></div>
                <div class="chart-shell"><canvas id="gmvChart"></canvas></div>
                <?php
$gmv_rows = $realtime['gmv_breakdown'] ?? [];

$daily_gmv = [];
foreach ($gmv_rows as $row) {
    $date = $row->date ?? null;
    if (!$date) continue;

    if (!isset($daily_gmv[$date])) {
        $daily_gmv[$date] = 0;
    }

    $daily_gmv[$date] += floatval($row->daily_gmv ?? 0);
}

$total_30_gmv = array_sum($daily_gmv);
$avg_30_gmv = count($daily_gmv) > 0 ? $total_30_gmv / count($daily_gmv) : 0;

$highest_date = null;
$lowest_date = null;
$highest_gmv = 0;
$lowest_gmv = 0;

if (!empty($daily_gmv)) {
    $highest_gmv = max($daily_gmv);
    $lowest_gmv = min($daily_gmv);
    $highest_date = array_search($highest_gmv, $daily_gmv);
    $lowest_date = array_search($lowest_gmv, $daily_gmv);
}
?>

<div class="summary-row">
    <div class="summary-card">
        <span class="summary-icon">
            <i class="fas fa-chart-line"></i>
        </span>

        <span class="summary-text">
            <span>Total GMV (30 Days)</span>
            <strong>Rp <?= number_format($total_30_gmv, 0, ',', '.') ?></strong>
            <small>Last 30 days</small>
        </span>
    </div>

    <div class="summary-card">
        <span class="summary-icon blue">
            <i class="fas fa-chart-simple"></i>
        </span>

        <span class="summary-text">
            <span>Daily Average GMV</span>
            <strong>Rp <?= number_format($avg_30_gmv, 0, ',', '.') ?></strong>
            <small>Average per active day</small>
        </span>
    </div>

    <div class="summary-card">
        <span class="summary-icon orange">
            <i class="fas fa-star"></i>
        </span>

        <span class="summary-text">
            <span>Highest Day</span>
            <strong>Rp <?= number_format($highest_gmv, 0, ',', '.') ?></strong>
            <small><?= $highest_date ? date('M d, Y', strtotime($highest_date)) : '-' ?></small>
        </span>
    </div>

    <div class="summary-card">
        <span class="summary-icon red">
            <i class="fas fa-calendar-arrow-down"></i>
        </span>

        <span class="summary-text">
            <span>Lowest Day</span>
            <strong>Rp <?= number_format($lowest_gmv, 0, ',', '.') ?></strong>
            <small><?= $lowest_date ? date('M d, Y', strtotime($lowest_date)) : '-' ?></small>
        </span>
    </div>
</div>
            </section>
            <section class="card panel orders-card">
                <div class="panel-header"><div class="panel-title"><i class="fas fa-cube"></i>Recent Valid Orders (Last 7 Days)</div><button class="view-btn">View All</button></div>
                <div class="orders-table-head"><span>Product & Creator</span><span>Amount</span></div>
                <div class="order-list" id="recentOrders">
                    <?php foreach ($realtime['recent_orders'] ?? [] as $order): ?>
                    <div class="order-item"><div class="thumb">
    <img 
        src="<?= !empty($order->image_url) ? htmlspecialchars($order->image_url) : base_url('assets/img/no-image.png') ?>" 
        alt="<?= htmlspecialchars($order->product_name ?? 'Product') ?>"
        loading="lazy"
        onerror="this.src='<?= base_url('assets/img/no-image.png') ?>'"
    >
</div>
<span class="order-main"><strong><?= htmlspecialchars(substr($order->product_name ?? 'Unknown', 0, 65)) ?></strong><span>by @<?= htmlspecialchars($order->creator_username ?? 'Unknown') ?> â€¢ <?= date('d M H:i', strtotime($order->order_time)) ?></span></span><span class="order-amount">Rp <?= number_format($order->gmv ?? 0, 0, ',', '.') ?><small>+Rp <?= number_format(($order->estimated_commission ?? $order->actual_commission ?? 0), 0, ',', '.') ?></small></span></div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>




<!-- ============================================================ -->
<!-- LEADERBOARD PETUGAS BD & CA -->
<!-- ============================================================ -->
<div class="leaderboard-grid" style="margin-bottom: 22px;">

    <!-- BD PERFORMANCE LEADERBOARD -->
    <section class="card leaderboard-card">

        <div class="leaderboard-header">
            <div class="leaderboard-title">
                <i class="fas fa-user-tie"></i>
                <span>BA Performance Leaderboard</span>
            </div>

            <button class="view-btn" onclick="window.location.href='<?= base_url('bd/team_performance') ?>'">
                View All
            </button>
        </div>

        <table class="leaderboard-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>BA</th>
                    <th>GMV (30 Days)</th>
                    <th>Orders</th>
                    <th>Brands</th>
                    <th>Conversion Rate</th>
                </tr>
            </thead>

            <tbody id="bdLeaderboardTable">
                <?php
                $rank = 1;
                foreach (($bd_performance ?? []) as $bd):
                    $conversion = $bd->conversion ?? 0;
                ?>
                <tr>
                    <td>
                        <div class="rank-badge rank-<?= $rank ?>">
                            <?= $rank ?>
                        </div>
                    </td>
                    <td class="creator-name">
                        <?= htmlspecialchars($bd->full_name ?: $bd->username) ?>
                        <div style="font-size: 10px; color: #8e9bb6;">@<?= htmlspecialchars($bd->username) ?></div>
                    </td>
                    <td>
                        Rp <?= number_format($bd->total_gmv ?? 0, 0, ',', '.') ?>
                    </td>
                    <td>
                        <?= number_format($bd->total_orders ?? 0) ?>
                    </td>
                    <td>
                        <?= number_format($bd->total_brands ?? 0) ?>
                    </td>
                    <td>
                        <div class="conversion-wrap">
                            <div class="conversion-bar">
                                <span style="width: <?= $conversion ?>%"></span>
                            </div>
                            <strong>
                                <?= number_format($conversion, 2) ?>%
                            </strong>
                        </div>
                    </td>
                </tr>
                <?php
                    $rank++;
                endforeach;
                ?>
            </tbody>
        </table>
    </section>

    <!-- CA PERFORMANCE LEADERBOARD -->
   <section class="card leaderboard-card">
    <div class="leaderboard-header">
        <div class="leaderboard-title">
            <i class="fas fa-user-cog"></i>
            <span>CA Performance Leaderboard</span>
        </div>
        <button class="view-btn">View All</button>
    </div>

    <table class="leaderboard-table">
        <thead>
            <tr>
                <th>Rank</th>
                <th>CA</th>
                <th>GMV (30 Days)</th>
                <th>Orders</th>
                <th>Brands</th>
                <th>Creators</th>
                <th>Conversion Rate</th>
            </tr>
        </thead>

        <tbody id="caLeaderboardTable">
            <?php
            $rank = 1;
            foreach (($ca_performance ?? []) as $ca):
                $conversion = $ca->conversion ?? 0;
            ?>
            <tr>
                <td>
                    <div class="rank-badge rank-<?= $rank ?>">
                        <?= $rank ?>
                    </div>
                </td>
                <td class="creator-name">
                    <?= htmlspecialchars($ca->full_name ?: $ca->username) ?>
                    <div style="font-size: 10px; color: #8e9bb6;">@<?= htmlspecialchars($ca->username) ?></div>
                </td>
                <td>
                    Rp <?= number_format($ca->total_gmv ?? 0, 0, ',', '.') ?>
                </td>
                <td>
                    <?= number_format($ca->total_orders ?? 0) ?>
                </td>
                <td>
                    <?= number_format($ca->total_brands ?? 0) ?>
                </td>
                <td>
                    <?= number_format($ca->total_creators ?? 0) ?>
                </td>
                <td>
                    <div class="conversion-wrap">
                        <div class="conversion-bar">
                            <span style="width: <?= $conversion ?>%"></span>
                        </div>
                        <strong>
                            <?= number_format($conversion, 2) ?>%
                        </strong>
                    </div>
                </td>
            </tr>
            <?php
                $rank++;
            endforeach;
            ?>
        </tbody>
    </table>
</section>


</div>


    <div class="leaderboard-grid">

    <!-- CREATOR LEADERBOARD -->
    <section class="card leaderboard-card">

        <div class="leaderboard-header">
            <div class="leaderboard-title">
                <i class="fas fa-user-astronaut"></i>
                <span>Creator Agent Leaderboard</span>
            </div>

            <button class="view-btn">
                View All
            </button>
        </div>

        <table class="leaderboard-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Creator</th>
                    <th>GMV (30 Days)</th>
                    <th>Orders</th>
                    <th>Conversion Rate</th>
                </tr>
            </thead>

            <tbody id="topCreatorsTable">
                <?php
                $rank = 1;
                foreach (($realtime['top_creators'] ?? []) as $creator):

                    $conversion = 0;

                    if (($creator->total_orders ?? 0) > 0) {
                        $conversion = min(
                            100,
                            round(($creator->total_orders / 100) * 1.2, 2)
                        );
                    }
                ?>

                <tr>
                    <td>
                        <div class="rank-badge rank-<?= $rank ?>">
                            <?= $rank ?>
                        </div>
                    </td>

                    <td class="creator-name">
                        @<?= htmlspecialchars($creator->creator_username) ?>
                    </td>

                    <td>
                        Rp <?= number_format($creator->total_gmv ?? 0, 0, ',', '.') ?>
                    </td>

                    <td>
                        <?= number_format($creator->total_orders ?? 0) ?>
                    </td>

                    <td>
                        <div class="conversion-wrap">
                            <div class="conversion-bar">
                                <span style="width: <?= $conversion ?>%"></span>
                            </div>

                            <strong>
                                <?= number_format($conversion, 2) ?>%
                            </strong>
                        </div>
                    </td>
                </tr>

                <?php
                    $rank++;
                endforeach;
                ?>
            </tbody>
        </table>
    </section>


    <!-- BRAND LEADERBOARD -->
    <section class="card leaderboard-card">

        <div class="leaderboard-header">
            <div class="leaderboard-title">
                <i class="fas fa-store"></i>
                <span>Brand Agent Leaderboard</span>
            </div>

            <button class="view-btn">
                View All
            </button>
        </div>

        <table class="leaderboard-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Brand</th>
                    <th>GMV (30 Days)</th>
                    <th>Orders</th>
                    <th>Conversion Rate</th>
                </tr>
            </thead>

            <tbody>

                <?php
                
$brands = array_slice($realtime['top_brands'] ?? [], 0, 5);

                $rank = 1;

                foreach ($brands as $brand):

                    $conversion = 0;

                    if (($brand->total_orders ?? 0) > 0) {

    $conversion = min(
        100,
        round((($brand->total_orders ?? 0) / 200) * 0.95, 2)
    );
}
                ?>

                <tr>

                    <td>
                        <div class="rank-badge rank-<?= $rank ?>">
                            <?= $rank ?>
                        </div>
                    </td>

                    <td class="creator-name">
                       <?= htmlspecialchars($brand->shop_name ?? '-') ?>
                    </td>

                    <td>
                        Rp<?= number_format($brand->total_gmv ?? 0, 0, ',', '.') ?>
                    </td>

                    <td>
                        <?= number_format($brand->total_orders ?? 0) ?>
                    </td>

                    <td>
                        <div class="conversion-wrap">

                            <div class="conversion-bar">
                                <span style="width: <?= $conversion ?>%"></span>
                            </div>

                            <strong>
                                <?= number_format($conversion, 2) ?>%
                            </strong>
                        </div>
                    </td>

                </tr>

                <?php
                    $rank++;
                endforeach;
                ?>

            </tbody>
        </table>
    </section>

</div>
   
   
   
    </section>
</div>

<div id="campaignModal" class="modal"><div class="modal-content" style="max-width:900px"><div class="modal-header"><h3 id="modalTitle">Campaign Details</h3><span class="close" onclick="closeModal()">&times;</span></div><div class="modal-body" id="modalBody"><div class="loading">Loading...</div></div></div></div>

<style>
.app{padding:26px 38px 32px;position:relative;z-index:1}.header-row{display:flex;align-items:flex-start;justify-content:space-between;gap:24px;margin-bottom:18px}.welcome h1{margin:0;font-size:clamp(24px,2.2vw,32px);letter-spacing:-.04em;line-height:1}.controls{display:flex;align-items:center;justify-content:flex-end;gap:16px;flex-wrap:wrap}.pill{height:46px;display:inline-flex;align-items:center;justify-content:center;gap:10px;padding:0 18px;color:var(--text);border:1px solid var(--stroke);border-radius:11px;background:rgba(7,17,32,.72);box-shadow:inset 0 1px 0 rgba(255,255,255,.04);font-weight:800;font-size:14px;white-space:nowrap;cursor:pointer}.pill.primary{background:linear-gradient(135deg,#5a25d8,#7f34ff);border-color:rgba(168,126,255,.45);box-shadow:var(--glow-purple),inset 0 1px 0 rgba(255,255,255,.14)}.sync-info{min-width:190px;color:var(--muted);text-align:right;font-size:13px;line-height:1.35}.dashboard-grid{display:grid;gap:16px}.card{border:1px solid var(--stroke);border-radius:var(--radius-lg);background:linear-gradient(160deg,rgba(19,33,59,.86),rgba(6,14,27,.78)),rgba(10,19,36,.75);box-shadow:var(--shadow),inset 0 1px 0 rgba(255,255,255,.035);position:relative;overflow:hidden}.card:after{content:"";position:absolute;inset:0;background:linear-gradient(120deg,rgba(255,255,255,.07),transparent 26%,transparent 78%,rgba(255,255,255,.02));pointer-events:none;opacity:.52}.top-metrics{display:grid;grid-template-columns:minmax(280px,1fr) minmax(390px,1.45fr) minmax(280px,1fr);gap:18px}.metric-card{min-height:150px;padding:26px;display:grid;grid-template-columns:auto 1fr minmax(130px,210px);align-items:center;gap:18px}.metric-card.gmv{border-color:rgba(154,80,255,.55);background:radial-gradient(circle at 25% 50%,rgba(139,58,255,.35),transparent 34%),linear-gradient(140deg,rgba(66,22,125,.96),rgba(18,25,51,.88) 58%,rgba(7,16,31,.92));box-shadow:0 0 44px rgba(124,60,255,.24),var(--shadow)}.metric-copy,.secondary-copy,.icon-box{position:relative;z-index:2}.metric-copy small,.secondary-copy small{color:var(--muted-2);display:block;font-weight:800;margin-bottom:6px}.metric-copy strong,.secondary-copy strong{display:block;font-size:clamp(28px,2.2vw,38px);letter-spacing:-.04em;line-height:1.08}.metric-sub,.secondary-meta{display:flex;gap:10px;align-items:center;margin-top:10px;color:var(--muted-2);font-size:14px;font-weight:700}.delta{font-weight:900;white-space:nowrap}.delta.up{color:var(--green)}.delta.down{color:var(--red)}.icon-box{width:64px;height:64px;display:grid;place-items:center;border-radius:18px;box-shadow:inset 0 1px 0 rgba(255,255,255,.14),0 18px 40px rgba(0,0,0,.33);font-size:28px}.icon-purple{background:linear-gradient(145deg,#8b39ff,#3b1ddb)}.icon-money{background:linear-gradient(145deg,#9c36ff,#5922e2)}.icon-teal{background:linear-gradient(145deg,#22d8b4,#077365)}.icon-orange{background:linear-gradient(145deg,#ffbd4a,#805019)}.icon-blue{background:linear-gradient(145deg,#19c1ff,#0b3975)}.icon-green{background:linear-gradient(145deg,#3df79a,#086b3c)}.icon-pink{background:linear-gradient(145deg,#9d37ff,#e74f77)}.sparkline{width:100%!important;height:76px!important;position:relative;z-index:2}.secondary-metrics{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}.secondary-card{min-height:124px;padding:22px 24px;display:flex;align-items:center;gap:18px}.secondary-copy strong{font-size:28px}.secondary-meta{font-size:13px;white-space:nowrap}.content-grid{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(430px,.95fr);gap:18px}.panel{padding:20px 22px 18px}.panel-header{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:12px;position:relative;z-index:2}.panel-title{display:flex;align-items:center;gap:10px;font-size:20px;font-weight:900;letter-spacing:-.03em}.panel-title i{color:var(--purple-2);filter:drop-shadow(0 0 10px rgba(192,44,255,.45))}.mini-select,.view-btn{min-height:36px;display:inline-flex;align-items:center;justify-content:center;padding:0 15px;border:1px solid var(--stroke);border-radius:10px;color:#eaf0ff;background:rgba(8,18,34,.64);font-size:13px;font-weight:800}.chart-card,.orders-card{min-height:445px}.chart-shell{position:relative;z-index:2;height:360px}#gmvChart{height:100%!important;width:100%!important}.orders-table-head{display:grid;grid-template-columns:1fr 110px;gap:12px;color:var(--muted-2);font-size:13px;font-weight:800;margin:0 10px 4px;position:relative;z-index:2}.orders-table-head span:last-child{text-align:right}.order-list{display:grid;position:relative;z-index:2;max-height:360px;overflow:auto}.order-item{display:grid;grid-template-columns:52px 1fr 106px;gap:14px;align-items:center;padding:10px 8px;border-top:1px solid rgba(122,145,185,.12)}.thumb{width:46px;height:46px;border-radius:9px;border:1px solid rgba(255,255,255,.22);background:linear-gradient(135deg,#ff6abc,#ffd6e9 45%,#ff9bc4);box-shadow:0 8px 20px rgba(0,0,0,.24)}.order-main strong{display:block;font-size:14px;line-height:1.25;margin-bottom:4px}.order-main span{color:var(--muted-2);display:block;font-size:12px}.order-amount{text-align:right;color:var(--green);font-weight:900;letter-spacing:-.02em;font-size:16px}.order-amount small{display:block;color:var(--muted-2);font-size:12px;margin-top:1px;font-weight:800}.leaderboard-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}.leader-card{padding:18px 20px 16px;min-height:252px}table{width:100%;border-collapse:collapse;position:relative;z-index:2}th,td{text-align:left;padding:10px 8px;border-top:1px solid rgba(122,145,185,.105);font-size:14px}th{color:var(--muted-2);font-size:13px;font-weight:800;border-top:none}td{color:#dce7fb;font-weight:700}td.num,th.num{text-align:right;font-variant-numeric:tabular-nums}.rank-badge{width:22px;height:22px;display:inline-grid;place-items:center;border-radius:50%;color:#05101f;font-size:12px;font-weight:900;background:#f3bf24}.rank-badge.r2{background:#cfd7e5}.rank-badge.r3{background:#ff8547}.rank-plain{font-weight:900;padding-left:7px}.campaigns-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:14px;position:relative;z-index:2}.campaign-card{border:1px solid rgba(115,138,181,.13);border-radius:16px;background:rgba(12,25,47,.72);padding:15px;cursor:pointer;transition:.2s}.campaign-card:hover{transform:translateY(-3px);border-color:rgba(168,126,255,.45);box-shadow:0 0 24px rgba(124,60,255,.18)}.campaign-header{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:14px}.campaign-name{font-size:14px;color:#fff}.campaign-status{font-size:10px;font-weight:900;padding:4px 9px;border-radius:999px;background:rgba(57,240,138,.13);color:var(--green)}.campaign-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}.campaign-stat{text-align:center}.campaign-stat span{display:block;color:var(--muted);font-size:10px;font-weight:800}.campaign-stat strong{display:block;font-size:12px;margin-top:4px;color:#fff}@media(max-width:1320px){.top-metrics,.content-grid,.leaderboard-grid{grid-template-columns:1fr}.top-metrics .gmv{order:-1}.secondary-metrics{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:860px){.app{padding:20px 14px}.header-row{flex-direction:column}.controls{width:100%;justify-content:flex-start}.sync-info{text-align:left}.metric-card{grid-template-columns:auto 1fr}.sparkline{grid-column:1/-1}.secondary-metrics{grid-template-columns:1fr}.order-item{grid-template-columns:48px 1fr}.order-amount{grid-column:2;text-align:left}th:nth-child(5),td:nth-child(5){display:none}}

.order-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 0;
    border-bottom: 1px solid rgba(255,255,255,.05);
}

.thumb {
    width: 58px;
    height: 58px;
    border-radius: 14px;
    overflow: hidden;
    flex-shrink: 0;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.06);
}

.thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.order-main {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
}

.order-main strong {
    font-size: 13px;
    color: #fff;
    line-height: 1.45;
    margin-bottom: 4px;
}

.order-main span {
    font-size: 11px;
    color: #8e9bb6;
}

.order-amount {
    text-align: right;
    font-weight: 700;
    color: #39f08a;
    font-size: 15px;
}

.order-amount small {
    display: block;
    margin-top: 4px;
    color: #8e9bb6;
    font-size: 11px;
}

.summary-row {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
    margin-top: 18px;
}

.summary-card {
    min-height: 82px;
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 12px;
    align-items: center;
    padding: 14px 16px;
    border-radius: 14px;
    background: rgba(12, 25, 47, 0.72);
    border: 1px solid rgba(115, 138, 181, 0.13);
}

.summary-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    background: linear-gradient(145deg, rgba(125,44,255,.85), rgba(45,16,116,.95));
    color: #fff;
}

.summary-icon.blue {
    background: linear-gradient(145deg, rgba(21,176,240,.9), rgba(16,58,125,.95));
}

.summary-icon.orange {
    background: linear-gradient(145deg, rgba(255,184,55,.9), rgba(121,77,11,.95));
}

.summary-icon.red {
    background: linear-gradient(145deg, rgba(255,81,91,.9), rgba(96,19,40,.95));
}

.summary-text span {
    display: block;
    color: #b7c1d6;
    font-size: 12px;
    font-weight: 700;
    margin-bottom: 3px;
}

.summary-text strong {
    display: block;
    color: #fff;
    font-size: 17px;
    letter-spacing: -0.03em;
}

.summary-text small {
    display: block;
    color: #8e9bb6;
    font-size: 11px;
    margin-top: 3px;
}

@media (max-width: 1200px) {
    .summary-row {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 640px) {
    .summary-row {
        grid-template-columns: 1fr;
    }
}

.leaderboard-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 22px;
}

.leaderboard-card {
    padding: 22px;
    border-radius: 26px;
    background:
        linear-gradient(
            115deg,
            rgba(16, 26, 48, 0.96),
            rgba(6, 14, 27, 0.98)
        );
    border: 1px solid rgba(115,138,181,.13);
}

.leaderboard-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 18px;
}

.leaderboard-title {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 18px;
    font-weight: 800;
    color: #fff;
}

.leaderboard-title i {
    color: #c42fff;
    font-size: 18px;
}

.view-btn {
    height: 42px;
    padding: 0 18px;
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,.08);
    background: rgba(255,255,255,.03);
    color: #fff;
    font-weight: 700;
    cursor: pointer;
}

.leaderboard-table {
    width: 100%;
    border-collapse: collapse;
}

.leaderboard-table th {
    padding: 14px 10px;
    text-align: left;
    color: #95a2bd;
    font-size: 13px;
    border-bottom: 1px solid rgba(255,255,255,.06);
}

.leaderboard-table td {
    padding: 16px 10px;
    border-bottom: 1px solid rgba(255,255,255,.04);
    color: #dfe7fb;
    font-weight: 600;
}

.rank-badge {
    width: 32px;
    height: 32px;
    border-radius: 999px;
    display: grid;
    place-items: center;
    font-weight: 800;
    color: #000;
}

.rank-1 {
    background: linear-gradient(145deg,#ffd84d,#ffbc00);
}

.rank-2 {
    background: linear-gradient(145deg,#f2f2f2,#bcc3d0);
}

.rank-3 {
    background: linear-gradient(145deg,#ffb37a,#ff7b22);
}

.rank-4,
.rank-5 {
    background: rgba(255,255,255,.08);
    color: #fff;
}

.creator-name {
    font-weight: 700;
}

.conversion-wrap {
    display: flex;
    align-items: center;
    gap: 14px;
}

.conversion-bar {
    width: 120px;
    height: 6px;
    border-radius: 999px;
    overflow: hidden;
    background: rgba(128,82,255,.14);
}

.conversion-bar span {
    display: block;
    height: 100%;
    border-radius: inherit;
    background: linear-gradient(90deg,#8d2dff,#d43cff);
}

@media (max-width: 1200px) {
    .leaderboard-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
let gmvChart=null,refreshInterval=null;
document.addEventListener('DOMContentLoaded',()=>{initChart();initSparkCharts();startAutoRefresh();});
function dashboardBreakdown(){return <?= json_encode($realtime['gmv_breakdown'] ?? []) ?>;}
function initSparkCharts(){makeSpark('ordersSpark',[26,31,40,55,49,64,82,71,58,69,88],'#9f45ff');makeSpark('gmvSpark',[22,28,42,51,48,69,78,74,55,36,28,32,44],'#b642ff');makeSpark('commissionSpark',[19,24,22,31,28,46,58,49,63,53,66],'#10dff0');}
function makeSpark(id,data,color){const el=document.getElementById(id);if(!el)return;new Chart(el,{type:'line',data:{labels:data.map((_,i)=>i),datasets:[{data,borderColor:color,backgroundColor:color+'22',fill:true,tension:.45,borderWidth:2,pointRadius:0}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{enabled:false}},scales:{x:{display:false},y:{display:false}}}});}
function initChart(){const el=document.getElementById('gmvChart');if(!el)return;const dailyData={};dashboardBreakdown().forEach(item=>{if(!dailyData[item.date])dailyData[item.date]=0;dailyData[item.date]+=parseFloat(item.daily_gmv||0);});const dates=Object.keys(dailyData).sort();const values=dates.map(d=>dailyData[d]);gmvChart=new Chart(el,{type:'line',data:{labels:dates.map(d=>d.substring(5)),datasets:[{label:'GMV (Rp)',data:values,borderColor:'#a132ff',backgroundColor:'rgba(161,50,255,.18)',borderWidth:4,fill:true,tension:.4,pointBackgroundColor:'#dba8ff',pointBorderColor:'#7d26ff',pointRadius:4,pointHoverRadius:6}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{labels:{color:'#b7c1d6'}},tooltip:{callbacks:{label:ctx=>'Rp '+formatNumber(ctx.raw)}}},scales:{y:{ticks:{color:'#acb8d0',callback:v=>'Rp '+(v/1000000).toFixed(1)+'M'},grid:{color:'rgba(130,152,195,.17)',borderDash:[4,6]}},x:{ticks:{color:'#acb8d0'},grid:{color:'rgba(130,152,195,.08)'}}}}});}
function startAutoRefresh(){refreshInterval=setInterval(refreshData,30000);}
function refreshData() {
    fetch('<?= base_url("dashboard/ajax_realtime_data") ?>')
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;

            setText('totalGmv', 'Rp ' + formatNumber(data.total_gmv || data.today_gmv || 0));
            setText('totalOrders', formatNumber(data.total_orders || data.today_orders || 0));
            setText('totalEstimatedCommission', 'Rp ' + formatNumber(data.total_estimated_commission || data.today_estimated_commission || 0));

            setText('brandsJoined', formatNumber(data.brands_joined_today || 0));
            setText('creatorsWithLinks', formatNumber(data.creators_with_links_today || 0));
            setText('creatorsActivated', formatNumber(data.creators_activated_today || 0));
            setText('creatorsWithContent', formatNumber(data.creators_with_content_today || 0));

            setText('yesterdayOrders', 'Yesterday: ' + formatNumber(data.yesterday_orders || 0));
            setText('yesterdayGmv', 'Yesterday: Rp ' + formatNumber(data.yesterday_gmv || 0));
            setText('yesterdayCommission', 'Yesterday: Rp ' + formatNumber(data.yesterday_estimated_commission || 0));
            setText('yesterdayBrands', 'Yesterday: ' + formatNumber(data.brands_joined_yesterday || 0));
            setText('yesterdayLinks', 'Yesterday: ' + formatNumber(data.creators_with_links_yesterday || 0));
            setText('yesterdayActivated', 'Yesterday: ' + formatNumber(data.creators_activated_yesterday || 0));
            setText('yesterdayContent', 'Yesterday: ' + formatNumber(data.creators_with_content_yesterday || 0));

            updateDelta('gmvGrowth', data.gmv_growth || 0);
            updateDelta('orderGrowth', data.order_growth || 0);
            updateDelta('commissionGrowth', data.commission_growth || 0);
            updateDelta('brandGrowth', data.brand_growth || 0);
            updateDelta('linksGrowth', data.links_growth || 0);
            updateDelta('activatedGrowth', data.activated_growth || 0);
            updateDelta('contentGrowth', data.content_growth || 0);

            setText(
                'lastSyncTime',
                data.last_sync ? new Date(data.last_sync).toLocaleTimeString('id-ID') : 'Never'
            );

            setText('serverTime', data.server_time || '');

            updateTopCreators(data.top_creators);
            updateRecentOrders(data.recent_orders);
            updateCampaignsGrid(data.campaigns);
            updateChart(data.gmv_breakdown);
        })
        .catch(console.error);
}

function setText(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
}

function updateDelta(id, value) {
    const el = document.getElementById(id);
    if (!el) return;

    const n = parseFloat(value || 0);
    el.classList.remove('up', 'down', 'neutral');

    if (n > 0) {
        el.classList.add('up');
        el.textContent = '↑ ' + Math.abs(n) + '%';
    } else if (n < 0) {
        el.classList.add('down');
        el.textContent = '↓ ' + Math.abs(n) + '%';
    } else {
        el.classList.add('neutral');
        el.textContent = '↑ 0%';
    }
}
function setText(id,val){const el=document.getElementById(id);if(el)el.innerHTML=val;}
function updateTopCreators(creators){const tbody=document.getElementById('topCreatorsTable');if(!tbody)return;tbody.innerHTML='';(creators||[]).forEach((c,i)=>{const r=i+1;tbody.innerHTML+=`<tr><td>${r<=3?`<span class="rank-badge r${r}">${r}</span>`:`<span class="rank-plain">${r}</span>`}</td><td>@${escapeHtml(c.creator_username)}</td><td class="num">Rp ${formatNumber(c.total_gmv)}</td><td class="num">${formatNumber(c.total_orders)}</td><td class="num">Rp ${formatNumber(c.total_estimated_commission)}</td></tr>`;});}
function updateRecentOrders(orders) {
    const c = document.getElementById('recentOrders');
    if (!c) return;

    c.innerHTML = '';

    (orders || []).forEach(o => {

        const comm = o.estimated_commission > 0
            ? o.estimated_commission
            : (o.actual_commission || 0);

        const imageHtml = o.image_url
            ? `
                <img
                    src="${o.image_url}"
                    alt="${escapeHtml(o.product_name || 'Product')}"
                    loading="lazy"
                    referrerpolicy="no-referrer"
                    onerror="this.parentElement.classList.add('no-image'); this.remove();"
                >
            `
            : `<i class="fas fa-box"></i>`;

        c.innerHTML += `
            <div class="order-item">

                <div class="thumb">
                    ${imageHtml}
                </div>

                <span class="order-main">
                    <strong>
                        ${escapeHtml((o.product_name || 'Unknown').substring(0, 65))}
                    </strong>

                    <span>
                        by @${escapeHtml(o.creator_username || 'Unknown')}
                        •
                        ${formatDate(o.order_time)}
                    </span>
                </span>

                <span class="order-amount">
                    Rp ${formatNumber(o.gmv)}

                    <small>
                        +Rp ${formatNumber(comm)}
                    </small>
                </span>
            </div>
        `;
    });
}
function updateCampaignsGrid(campaigns){const g=document.getElementById('campaignsGrid');if(!g)return;if(!campaigns||!campaigns.length){g.innerHTML='<div class="empty-state">No active campaigns</div>';return;}g.innerHTML='';campaigns.forEach(c=>{g.innerHTML+=`<div class="campaign-card" onclick="showCampaignDetail('${escapeHtml(c.campaign_id)}')"><div class="campaign-header"><h4 class="campaign-name">${escapeHtml(c.campaign_name||'Unknown')}</h4><span class="campaign-status ${(c.status||'ongoing').toLowerCase()}">${c.status||'ONGOING'}</span></div><div class="campaign-stats"><div class="campaign-stat"><span>GMV</span><strong>Rp ${formatNumber(c.actual_gmv)}</strong></div><div class="campaign-stat"><span>Orders</span><strong>${formatNumber(c.actual_orders)}</strong></div><div class="campaign-stat"><span>Creators</span><strong>${formatNumber(c.actual_creators)}</strong></div></div></div>`;});}
function updateChart(breakdownData){if(!gmvChart)return;const dailyData={};(breakdownData||[]).forEach(item=>{if(!dailyData[item.date])dailyData[item.date]=0;dailyData[item.date]+=parseFloat(item.daily_gmv||0);});const dates=Object.keys(dailyData).sort();gmvChart.data.labels=dates.map(d=>d.substring(5));gmvChart.data.datasets[0].data=dates.map(d=>dailyData[d]);gmvChart.update();}
function showCampaignDetail(campaignId){const m=document.getElementById('campaignModal'),body=document.getElementById('modalBody');m.classList.add('show');body.innerHTML='<div class="loading">Loading...</div>';fetch(`<?= base_url('dashboard/ajax_campaign_detail/') ?>${encodeURIComponent(campaignId)}`).then(r=>r.json()).then(data=>{if(!data.success)throw new Error(data.message||'Failed');const c=data.campaign||{};let rows='';(data.top_creators||[]).forEach(cr=>{rows+=`<tr><td>@${escapeHtml(cr.creator_username)}</td><td class="num">Rp ${formatNumber(cr.total_gmv)}</td><td class="num">${formatNumber(cr.total_orders)}</td><td class="num">Rp ${formatNumber(cr.total_commission)}</td></tr>`;});body.innerHTML=`<div class="campaign-detail"><h3>${escapeHtml(c.campaign_name||'Campaign')}</h3><br><table><tbody><tr><td>ID</td><td>${escapeHtml(c.campaign_id||campaignId)}</td></tr><tr><td>Status</td><td>${escapeHtml(c.status||'ONGOING')}</td></tr><tr><td>GMV</td><td>Rp ${formatNumber(c.total_gmv||c.actual_gmv)}</td></tr><tr><td>Orders</td><td>${formatNumber(c.total_orders||c.actual_orders)}</td></tr></tbody></table><br><h3>Top Creators</h3><table><thead><tr><th>Creator</th><th class="num">GMV</th><th class="num">Orders</th><th class="num">Commission</th></tr></thead><tbody>${rows||'<tr><td colspan="4">No data</td></tr>'}</tbody></table></div>`;}).catch(e=>{body.innerHTML=`<div class="empty-state">${escapeHtml(e.message)}</div>`;});}
function closeModal(){document.getElementById('campaignModal').classList.remove('show');}
function triggerSync(){if(!confirm('Trigger manual sync?'))return;fetch('<?= base_url("dashboard/trigger_sync") ?>',{method:'POST'}).then(r=>r.json()).then(d=>{showToastGlobal(d.message||'Sync triggered');setTimeout(refreshData,5000);}).catch(e=>showToastGlobal('Failed: '+e,'error'));}
function formatDate(d){if(!d)return'';return new Date(d).toLocaleDateString('id-ID',{day:'numeric',month:'short',hour:'2-digit',minute:'2-digit'});}
</script>
