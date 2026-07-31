<!-- file: application/views/bd/campaign_detail.php -->
<style>
    .detail-container {
        padding: 20px;
        animation: fadeIn 0.3s ease;
    }
    
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--purple);
        text-decoration: none;
        font-size: 12px;
        margin-bottom: 16px;
        transition: var(--transition);
    }
    
    .back-link:hover {
        color: var(--cyan);
        transform: translateX(-3px);
    }
    
    .page-title {
        font-size: 24px;
        font-weight: 700;
        background: linear-gradient(135deg, var(--purple), var(--cyan), var(--blue));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        margin-bottom: 8px;
    }
    
    .page-subtitle {
        color: var(--text-muted);
        font-size: 12px;
        margin-bottom: 24px;
    }
    
    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 28px;
    }
    
    .stat-card {
        background: var(--bg-card);
        border-radius: 20px;
        padding: 20px;
        text-align: center;
        border: 1px solid var(--border);
        transition: var(--transition);
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
        margin-top: 6px;
    }
    
    /* Section Card */
    .section-card {
        background: var(--bg-card);
        border-radius: 20px;
        padding: 20px;
        border: 1px solid var(--border);
        margin-bottom: 24px;
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
    }
    
    .section-title i {
        color: var(--purple);
    }
    
    /* Products Grid */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 16px;
    }
    
    .product-card {
        background: var(--bg-elevated);
        border-radius: 16px;
        padding: 16px;
        border: 1px solid var(--border-light);
        transition: var(--transition);
    }
    
    .product-card:hover {
        border-color: var(--purple);
        transform: translateY(-3px);
    }
    
    .product-name {
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 8px;
        font-size: 13px;
    }
    
    .product-price {
        font-size: 16px;
        font-weight: 700;
        color: #4ade80;
        margin-bottom: 8px;
    }
    
    .product-stats {
        display: flex;
        gap: 12px;
        font-size: 11px;
        color: var(--text-muted);
    }
    
    /* Data Table */
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .data-table th,
    .data-table td {
        padding: 12px 8px;
        text-align: left;
        border-bottom: 1px solid var(--border);
        font-size: 12px;
    }
    
    .data-table th {
        color: var(--purple);
        font-weight: 600;
        background: var(--bg-elevated);
    }
    
    .data-table tr:hover {
        background: var(--bg-elevated);
    }
    
    .gmv-cell {
        color: #4ade80;
        font-weight: 600;
    }
    
    .commission-cell {
        color: #fbbf24;
        font-weight: 600;
    }
    
    .order-status {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 600;
    }
    
    .order-status.completed {
        background: rgba(16, 185, 129, 0.15);
        color: #10b981;
    }
    
    .order-status.processing {
        background: rgba(245, 158, 11, 0.15);
        color: #f59e0b;
    }
    
    .order-status.shipped {
        background: rgba(59, 130, 246, 0.15);
        color: #3b82f6;
    }
    
    .badge-status {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 600;
    }
    
    .badge-status.ongoing {
        background: rgba(16, 185, 129, 0.15);
        color: #10b981;
    }
    
    .badge-status.completed {
        background: rgba(139, 92, 246, 0.15);
        color: #8b5cf6;
    }
    
    .empty-state {
        text-align: center;
        padding: 40px;
        color: var(--text-muted);
    }
    
    .empty-state i {
        font-size: 48px;
        margin-bottom: 12px;
        display: block;
    }
    
    @media (max-width: 768px) {
        .detail-container {
            padding: 12px;
        }
        
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        
        .products-grid {
            grid-template-columns: 1fr;
        }
        
        .data-table {
            font-size: 10px;
        }
        
        .data-table th,
        .data-table td {
            padding: 8px 4px;
        }
    }
    /* Filter Bar */
.filter-bar {
    margin-bottom: 20px;
    display: flex;
    justify-content: flex-end;
}

.date-filter {
    display: flex;
    align-items: center;
    gap: 8px;
    background: var(--bg-elevated);
    padding: 6px 16px;
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

.date-input:focus {
    border-color: var(--purple);
}

.btn-filter {
    background: var(--purple-glow);
    border: 1px solid var(--purple);
    color: var(--purple);
    padding: 4px 12px;
    border-radius: 40px;
    cursor: pointer;
    font-size: 11px;
    transition: var(--transition);
}

.btn-filter:hover {
    background: var(--purple);
    color: white;
}

.stat-period {
    font-size: 9px;
    color: var(--text-muted);
    margin-top: 4px;
}
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 16px;
}

.product-card {
    display: flex;
    align-items: center;
    gap: 12px;

    padding: 14px;
    border-radius: 14px;
    border: 1px solid var(--border-light);
    background: var(--bg-elevated);

    transition: all 0.2s ease;
}

.product-card:hover {
    border-color: var(--purple);
    transform: translateY(-2px);
}

/* Image */
.product-image {
    width: 60px;
    height: 60px;
    flex-shrink: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 12px;
    overflow: hidden;
    background: var(--bg-card);
}

.product-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-img-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 100%;
    height: 100%;

    font-size: 22px;
    color: var(--text-muted);
}

/* Info */
.product-info {
    flex: 1;
    min-width: 0;
}

.product-name {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);

    margin-bottom: 4px;

    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.product-price {
    font-size: 14px;
    font-weight: 700;
    color: #4ade80;

    margin-bottom: 6px;
}

.product-stats {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;

    font-size: 10px;
    color: var(--text-muted);
}

.product-stats i {
    color: var(--purple);
}
</style>

<div class="detail-container">
    <div>
        <a href="<?= base_url('bd/campaigns') ?>" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Campaigns
        </a>
        <h1 class="page-title"><?= htmlspecialchars($campaign->campaign_name ?? $campaign->name) ?></h1>
        <p class="page-subtitle">
            <i class="fas fa-chart-line"></i> Campaign Performance & Details
        </p>
    </div>
    <!-- ðŸ”¥ FILTER TANGGAL -->
    <div class="filter-bar">
        <div class="date-filter">
            <label style="color: var(--text-muted); font-size: 11px;">Periode:</label>
            <input type="date" id="startDateFilter" class="date-input" value="<?= $start_date ?>">
            <span style="color:var(--text-muted);">s/d</span>
            <input type="date" id="endDateFilter" class="date-input" value="<?= $end_date ?>">
            <button id="applyDateFilterBtn" class="btn-filter">
                <i class="fas fa-calendar-alt"></i> Terapkan
            </button>
            <button id="resetDateFilterBtn" class="btn-filter">
                <i class="fas fa-undo-alt"></i> Hari Ini
            </button>
        </div>
    </div>
    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">Rp <?= number_format($total_gmv, 0, ',', '.') ?></div>
            <div class="stat-label">Total GMV</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= number_format($total_orders) ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= number_format($total_creators) ?></div>
            <div class="stat-label">Active Creators</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">
                <span class="badge-status <?= strtolower($campaign->status ?? 'ongoing') ?>">
                    <?= $campaign->status ?? 'ONGOING' ?>
                </span>
            </div>
            <div class="stat-label">Status</div>
        </div>
    </div>
    
    <!-- Campaign Info -->
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-info-circle"></i> Campaign Information
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
            <div>
                <div style="color: var(--text-muted); font-size: 11px;">Campaign ID</div>
                <div style="color: var(--text-primary); font-size: 13px;"><?= htmlspecialchars($campaign->campaign_id) ?></div>
            </div>
            <div>
                <div style="color: var(--text-muted); font-size: 11px;">Periode</div>
                <div style="color: var(--text-primary); font-size: 13px;">
                    <?= $campaign->start_date ? date('d M Y', strtotime($campaign->start_date)) : '-' ?> - 
                    <?= $campaign->end_date ? date('d M Y', strtotime($campaign->end_date)) : '-' ?>
                </div>
            </div>
            <div>
                <div style="color: var(--text-muted); font-size: 11px;">Last Sync</div>
                <div style="color: var(--text-primary); font-size: 13px;"><?= $campaign->last_sync ? date('d M Y H:i:s', strtotime($campaign->last_sync)) : '-' ?></div>
            </div>
        </div>
    </div>
    

    <!-- Products Section -->
<div class="section-card">
    <div class="section-title">
        <i class="fas fa-box"></i> Products
        <span style="margin-left: auto; font-size: 11px; color: var(--text-muted);"><?= count($products) ?> products</span>
    </div>
    <?php if (!empty($products)): ?>
    <div class="products-grid">
        <?php 
        // 🔥 Urutkan produk berdasarkan GMV terbesar
        usort($products, function($a, $b) {
            return $b->gmv <=> $a->gmv;
        });
        
        foreach ($products as $product): 
        ?>
          <div class="product-card">
            
            <!-- Image -->
            <div class="product-image">
                <?php if (!empty($product->image_url)): ?>
                    <img src="<?= htmlspecialchars($product->image_url) ?>" 
                         alt="<?= htmlspecialchars($product->product_name) ?>" 
                         class="product-img">
                <?php else: ?>
                    <div class="product-img-placeholder">
                        <i class="fas fa-box"></i>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Info -->
            <div class="product-info">
                <div class="product-name">
                    <?= htmlspecialchars($product->product_name) ?>
                </div>

                <div class="product-price">
                    Rp <?= number_format($product->price, 0, ',', '.') ?>
                </div>

                <div class="product-stats">
                    <span>
                        <i class="fas fa-percent"></i>
                        <?= $product->commission_rate  ?>%
                    </span>
                    <span>
                        <i class="fas fa-chart-line"></i>
                        Rp <?= number_format($product->gmv, 0, ',', '.') ?>
                    </span>
                </div>
            </div>

        </div>
        <?php endforeach; ?>
    </div>

    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-box-open"></i>
        <p>No products found for this campaign</p>
    </div>
    <?php endif; ?>
</div>  </div>
        <?php if (!empty($orders)): ?>
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Product</th>
                        <th>Creator</th>
                        <th>GMV</th>
                        <th>Commission</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= htmlspecialchars($order->order_id) ?></td>
                        <td><?= htmlspecialchars(substr($order->product_name ?? '', 0, 40)) ?>...</td>
                        <td>@<?= htmlspecialchars($order->creator_username ?? '-') ?></td>
                        <td class="gmv-cell">Rp <?= number_format($order->gmv, 0, ',', '.') ?></td>
                        <td class="commission-cell">Rp <?= number_format($order->estimated_commission, 0, ',', '.') ?></td>
                        <td>
                            <span class="order-status <?= strtolower($order->order_status ?? 'pending') ?>">
                                <?= $order->order_status ?? 'PENDING' ?>
                            </span>
                        </td>
                        <td><?= $order->order_date_local ?? date('d M Y', strtotime($order->order_time)) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-shopping-cart"></i>
            <p>No orders found for this campaign</p>
        </div>
        <?php endif; ?>
    </div>
</div>
<script>



// ========== DATE FILTER FOR CAMPAIGN DETAIL ==========
const startDateInput = document.getElementById('startDateFilter');
const endDateInput = document.getElementById('endDateFilter');
const applyFilterBtn = document.getElementById('applyDateFilterBtn');
const resetFilterBtn = document.getElementById('resetDateFilterBtn');

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
    
    // Reload halaman dengan parameter tanggal
    const campaignId = '<?= $campaign->campaign_id ?>';
    window.location.href = baseUrlDashboard + `bd/campaign_detail/${campaignId}?start_date=${startDate}&end_date=${endDate}`;
}

function resetDateFilter() {
    // Reset ke hari ini
    const campaignId = '<?= $campaign->campaign_id ?>';
    window.location.href = baseUrlDashboard + `bd/campaign_detail/${campaignId}`;
}

if (applyFilterBtn) {
    applyFilterBtn.addEventListener('click', applyDateFilter);
}

if (resetFilterBtn) {
    resetFilterBtn.addEventListener('click', resetDateFilter);
}

</script>