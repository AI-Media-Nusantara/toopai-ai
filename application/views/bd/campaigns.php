<!-- file: application/views/bd/campaigns.php -->
<style>
    /* Campaigns page styles - konsisten dengan tema ungu + biru + hitam */
    .page-header {
        margin-bottom: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
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
    
    .btn-sync {
        background: linear-gradient(135deg, var(--purple-glow), rgba(59, 130, 246, 0.1));
        border: 1px solid var(--purple);
        padding: 8px 20px;
        border-radius: 40px;
        color: var(--purple);
        text-decoration: none;
        font-size: 12px;
        font-weight: 500;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-sync:hover {
        background: var(--purple);
        color: white;
        box-shadow: var(--glow-purple);
    }
    
    /* Stats Row */
    .stats-row {
        display: flex;
        gap: 20px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    
    .stat-badge {
        background: var(--bg-card);
        border-radius: 60px;
        padding: 8px 20px;
        border: 1px solid var(--border);
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    
    .stat-badge i {
        color: var(--purple);
        font-size: 14px;
    }
    
    .stat-badge span {
        color: var(--text-secondary);
        font-size: 12px;
    }
    
    .stat-badge strong {
        color: var(--text-primary);
        font-size: 14px;
        margin-left: 5px;
    }
    
    /* Section Card */
    .section-card {
        background: var(--bg-card);
        border-radius: 24px;
        padding: 20px;
        border: 1px solid var(--border);
    }
    
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 12px;
    }
    
    .section-header h3 {
        color: var(--text-primary);
        font-size: 16px;
        font-weight: 600;
    }
    
    .section-header h3 i {
        color: var(--purple);
        margin-right: 8px;
    }
    
    .filter-group {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .filter-select {
        background: var(--bg-elevated);
        border: 1px solid var(--border);
        padding: 8px 16px;
        border-radius: 40px;
        color: var(--text-secondary);
        font-size: 12px;
        cursor: pointer;
        transition: var(--transition);
    }
    
    .filter-select:hover {
        border-color: var(--purple);
    }
    
    .search-box {
        display: flex;
        align-items: center;
        background: var(--bg-elevated);
        border: 1px solid var(--border);
        border-radius: 40px;
        padding: 6px 16px;
    }
    
    .search-box i {
        color: var(--text-muted);
        font-size: 12px;
    }
    
    .search-box input {
        background: transparent;
        border: none;
        padding: 8px 12px;
        color: var(--text-primary);
        font-size: 13px;
        outline: none;
        width: 200px;
    }
    
    .search-box input::placeholder {
        color: var(--text-muted);
    }
    
    /* Campaigns Grid */
    .campaigns-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 16px;
    }
    
    .campaign-card {
        background: var(--bg-elevated);
        border-radius: 18px;
        padding: 18px;
        text-decoration: none;
        display: block;
        border: 1px solid var(--border-light);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }
    
    .campaign-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--purple), var(--cyan));
        opacity: 0;
        transition: var(--transition);
    }
    
    .campaign-card:hover {
        border-color: var(--purple);
        transform: translateY(-3px);
        box-shadow: var(--glow-purple);
    }
    
    .campaign-card:hover::before {
        opacity: 1;
    }
    
    .campaign-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .campaign-name {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .campaign-name i {
        color: var(--purple);
        font-size: 16px;
    }
    
    .campaign-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        font-size: 11px;
        color: var(--text-secondary);
        margin-bottom: 12px;
    }
    
    .campaign-stats span {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    .campaign-stats i {
        color: var(--purple);
        font-size: 11px;
    }
    
    .campaign-dates {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        font-size: 10px;
        color: var(--text-muted);
        margin-bottom: 12px;
        padding-top: 8px;
        border-top: 1px solid var(--border-light);
    }
    
    .campaign-dates i {
        color: var(--cyan);
        font-size: 10px;
    }
    
    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 600;
    }
    
    .badge-status.ongoing {
        background: rgba(16, 185, 129, 0.15);
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.3);
    }
    
    .badge-status.active {
        background: rgba(16, 185, 129, 0.15);
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.3);
    }
    
    .badge-status.completed {
        background: rgba(139, 92, 246, 0.15);
        color: #8b5cf6;
        border: 1px solid rgba(139, 92, 246, 0.3);
    }
    
    .badge-status.draft {
        background: rgba(245, 158, 11, 0.15);
        color: #f59e0b;
        border: 1px solid rgba(245, 158, 11, 0.3);
    }
    
    .badge-status.paused {
        background: rgba(59, 130, 246, 0.15);
        color: #3b82f6;
        border: 1px solid rgba(59, 130, 246, 0.3);
    }
    
    .empty-state {
        text-align: center;
        color: var(--text-muted);
        padding: 60px 20px;
    }
    
    .empty-state i {
        font-size: 48px;
        color: var(--purple);
        margin-bottom: 16px;
        display: block;
    }
    
    .empty-state a {
        color: var(--purple);
        text-decoration: none;
    }
    
    /* Pagination */
    .pagination-container {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 24px;
        flex-wrap: wrap;
    }
    
    .pagination-container button {
        background: var(--bg-elevated);
        border: 1px solid var(--border);
        color: var(--text-secondary);
        padding: 8px 14px;
        border-radius: 40px;
        cursor: pointer;
        transition: var(--transition);
        font-size: 12px;
    }
    
    .pagination-container button:hover:not(:disabled) {
        background: var(--purple);
        color: white;
        border-color: var(--purple);
    }
    
    .pagination-container button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .page-info {
        color: var(--text-muted);
        font-size: 12px;
        padding: 8px 12px;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .campaigns-grid {
            grid-template-columns: 1fr;
        }
        
        .section-card {
            padding: 16px;
        }
        
        .section-header {
            flex-direction: column;
            align-items: stretch;
        }
        
        .filter-group {
            justify-content: space-between;
        }
        
        .search-box input {
            width: 150px;
        }
        
        .stats-row {
            justify-content: center;
        }
    }
    
    /* Di bagian style, tambahkan */
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

</style>

<div class="page-header">
    <div>
       
        <h1 class="page-title"><i class="fas fa-bullhorn"></i> Campaigns Management</h1>
        <p class="page-subtitle"><i class="fas fa-chart-line"></i> Manage all affiliate campaigns from TikTok</p>
    </div>
    <button class="btn-sync" id="syncCampaignsBtn">
        <i class="fas fa-sync-alt"></i> Sync Data By API
    </button>
    <div id="syncLoading" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.7); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:#1e293b; padding:30px; border-radius:16px; text-align:center;">
            <i class="fas fa-spinner fa-pulse fa-3x" style="color:#4ade80;"></i>
            <p style="margin-top:16px; color:white;">Syncing campaigns from TikTok API...</p>
        </div>
    </div>
</div>

<div class="stats-row">
    <div class="stat-badge">
        <i class="fas fa-chart-line"></i>
        <span>Total Campaigns:</span>
        <strong id="totalCampaignsStat"><?= count($campaigns) ?></strong>
    </div>
    <div class="stat-badge">
        <i class="fas fa-play-circle"></i>
        <span>Ongoing:</span>
        <strong id="ongoingCountStat"><?= $ongoing_count ?? 0 ?></strong>
    </div>
    <div class="stat-badge">
        <i class="fas fa-check-circle"></i>
        <span>Completed:</span>
        <strong id="completedCountStat"><?= $completed_count ?? 0 ?></strong>
    </div>
</div>

<div class="section-card">
    <div class="section-header">
        <h3><i class="fas fa-list"></i> All Campaigns</h3>
        <div class="filter-group">
            <select id="statusFilter" class="filter-select" onchange="filterCampaigns()">
                <option value="all">All Status</option>
                <option value="ongoing">Ongoing / Active</option>
                <option value="completed">Completed</option>
            </select>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchCampaignInput" placeholder="Cari campaign..." onkeyup="filterCampaigns()">
            </div>
        </div>
    </div>
    
    <div id="campaignsContainer">
        <div class="campaigns-grid" id="campaignsGrid">
    <?php if (!empty($campaigns)): ?>
        <?php foreach ($campaigns as $camp): ?>
        <a href="<?= base_url('bd/campaign_detail/'.$camp->campaign_id) ?>" class="campaign-card" 
           data-campaign-name="<?= strtolower(htmlspecialchars($camp->campaign_name ?? '')) ?>"
           data-campaign-status="<?= strtolower($camp->status ?? '') ?>">
            
            <!-- 🔥 TAMBAHKAN GAMBAR CAMPAIGN -->
            <?php if (!empty($camp->campaign_image)): ?>
            <div class="campaign-image">
                <img src="<?= $camp->campaign_image ?>" alt="<?= htmlspecialchars($camp->campaign_name ?? '') ?>" class="campaign-img">
            </div>
            <?php else: ?>
            <div class="campaign-image-placeholder">
                <i class="fas fa-bullhorn"></i>
            </div>
            <?php endif; ?>
            
            <div class="campaign-header">
                <div class="campaign-name">
                    <i class="fas fa-bullhorn"></i>
                    <?= htmlspecialchars($camp->campaign_name ?? 'Unknown Campaign') ?>
                </div>
                <span class="badge-status <?= strtolower($camp->status ?? 'ongoing') ?>">
                    <i class="fas fa-<?= ($camp->status == 'ONGOING' || $camp->status == 'ACTIVE') ? 'play' : (($camp->status == 'COMPLETED') ? 'check' : 'pause') ?>"></i>
                    <?= $camp->status ?? 'ONGOING' ?>
                </span>
            </div>
            <div class="campaign-stats">
                <span><i class="fas fa-box"></i> <?= number_format($camp->total_products ?? 0) ?> products</span>
                <span><i class="fas fa-money-bill-wave"></i> Rp <?= number_format($camp->total_gmv ?? 0, 0, ',', '.') ?></span>
                <span><i class="fas fa-users"></i> <?= number_format($camp->total_creators ?? 0) ?> creators</span>
            </div>
            <div class="campaign-dates">
                <span><i class="fas fa-calendar-alt"></i> Start: <?= $camp->start_date ? date('d M Y', strtotime($camp->start_date)) : '-' ?></span>
                <span><i class="fas fa-calendar-check"></i> End: <?= $camp->end_date ? date('d M Y', strtotime($camp->end_date)) : '-' ?></span>
            </div>
        </a>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-bullhorn"></i>
            <p>No campaigns found.</p>
            <button class="btn-sync" onclick="syncCampaigns()">Sync from API</button>
        </div>
    <?php endif; ?>
</div>
    </div>
    
    <!-- Pagination -->
    <div class="pagination-container" id="paginationContainer" style="<?= (count($campaigns) <= 10) ? 'display:none;' : '' ?>">
        <button id="prevPageBtn" onclick="changePage(-1)" disabled><i class="fas fa-chevron-left"></i> Prev</button>
        <span class="page-info" id="pageInfo">Page 1</span>
        <button id="nextPageBtn" onclick="changePage(1)">Next <i class="fas fa-chevron-right"></i></button>
    </div>
</div>

<script>
// ========== DATA ==========
let allCampaigns = [];
let currentPage = 1;
let itemsPerPage = 10;
let filteredCampaigns = [];

// Ambil data campaign dari PHP ke JavaScript - menggunakan affiliate_campaigns structure
<?php if (!empty($campaigns)): ?>
allCampaigns = <?= json_encode(array_map(function($c) {
    return [
        'id' => $c->campaign_id,  // ← gunakan campaign_id sebagai identifier
        'name' => $c->campaign_name ?? $c->name ?? 'Unknown',
        'status' => $c->status ?? 'ONGOING',
        'total_products' => $c->total_products ?? 0,
        'total_gmv' => floatval($c->total_gmv ?? 0),
        'total_orders' => intval($c->total_orders ?? 0),
        'total_creators' => intval($c->total_creators ?? 0),
        'start_date' => $c->start_date ?? null,
        'end_date' => $c->end_date ?? null
    ];
}, $campaigns)) ?>;
filteredCampaigns = [...allCampaigns];
<?php endif; ?>

// Update stat counts
function updateStats() {
    const ongoing = allCampaigns.filter(c => c.status === 'ONGOING' || c.status === 'ACTIVE').length;
    const completed = allCampaigns.filter(c => c.status === 'COMPLETED').length;
    
    const ongoingElem = document.getElementById('ongoingCountStat');
    const completedElem = document.getElementById('completedCountStat');
    const totalElem = document.getElementById('totalCampaignsStat');
    
    if (ongoingElem) ongoingElem.innerText = ongoing;
    if (completedElem) completedElem.innerText = completed;
    if (totalElem) totalElem.innerText = allCampaigns.length;
}

function renderCampaigns() {
    const start = (currentPage - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const pageCampaigns = filteredCampaigns.slice(start, end);
    const grid = document.getElementById('campaignsGrid');
    
    if (!grid) return;
    
    if (pageCampaigns.length === 0) {
        grid.innerHTML = `<div class="empty-state">
            <i class="fas fa-bullhorn"></i>
            <p>No campaigns found matching your filters.</p>
        </div>`;
        const paginationContainer = document.getElementById('paginationContainer');
        if (paginationContainer) paginationContainer.style.display = 'none';
        return;
    }
    
    let html = '';
    for (let campaign of pageCampaigns) {
        let statusIcon = 'play';
        let statusClass = (campaign.status || 'ongoing').toLowerCase();
        if (campaign.status === 'COMPLETED') {
            statusIcon = 'check';
            statusClass = 'completed';
        } else if (campaign.status === 'DRAFT') {
            statusIcon = 'edit';
            statusClass = 'draft';
        } else if (campaign.status === 'PAUSED') {
            statusIcon = 'pause';
            statusClass = 'paused';
        } else if (campaign.status === 'ONGOING' || campaign.status === 'ACTIVE') {
            statusIcon = 'play';
            statusClass = 'ongoing';
        }
        
        const startDate = campaign.start_date ? new Date(campaign.start_date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
        const endDate = campaign.end_date ? new Date(campaign.end_date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
        
        html += `
            <a href="<?= base_url('bd/campaign_detail/') ?>${escapeHtml(campaign.id)}" class="campaign-card">
                <div class="campaign-header">
                    <div class="campaign-name">
                        <i class="fas fa-bullhorn"></i>
                        ${escapeHtml(campaign.name)}
                    </div>
                    <span class="badge-status ${statusClass}">
                        <i class="fas fa-${statusIcon}"></i>
                        ${campaign.status || 'ONGOING'}
                    </span>
                </div>
                <div class="campaign-stats">
                    <span><i class="fas fa-box"></i> ${formatNumber(campaign.total_products)} products</span>
                    <span><i class="fas fa-money-bill-wave"></i> Rp ${formatNumber(campaign.total_gmv)}</span>
                    <span><i class="fas fa-users"></i> ${formatNumber(campaign.total_creators)} creators</span>
                    <span><i class="fas fa-shopping-cart"></i> ${formatNumber(campaign.total_orders)} orders</span>
                </div>
                <div class="campaign-dates">
                    <span><i class="fas fa-calendar-alt"></i> Start: ${startDate}</span>
                    <span><i class="fas fa-calendar-check"></i> End: ${endDate}</span>
                </div>
            </a>
        `;
    }
    grid.innerHTML = html;
    
    // Update pagination info
    const totalPages = Math.ceil(filteredCampaigns.length / itemsPerPage);
    const pageInfo = document.getElementById('pageInfo');
    if (pageInfo) pageInfo.innerText = `Page ${currentPage} of ${totalPages || 1}`;
    
    const prevBtn = document.getElementById('prevPageBtn');
    const nextBtn = document.getElementById('nextPageBtn');
    if (prevBtn) prevBtn.disabled = currentPage === 1;
    if (nextBtn) nextBtn.disabled = currentPage === totalPages || totalPages === 0;
    
    const paginationContainer = document.getElementById('paginationContainer');
    if (paginationContainer) {
        paginationContainer.style.display = filteredCampaigns.length > itemsPerPage ? 'flex' : 'none';
    }
}

function filterCampaigns() {
    const searchTerm = document.getElementById('searchCampaignInput')?.value.toLowerCase() || '';
    const statusFilter = document.getElementById('statusFilter')?.value || 'all';
    
    filteredCampaigns = allCampaigns.filter(campaign => {
        // Filter by search term
        const matchesSearch = (campaign.name || '').toLowerCase().includes(searchTerm);
        
        // Filter by status
        let matchesStatus = true;
        if (statusFilter !== 'all') {
            if (statusFilter === 'ongoing') {
                matchesStatus = (campaign.status === 'ONGOING' || campaign.status === 'ACTIVE');
            } else {
                matchesStatus = (campaign.status || '').toLowerCase() === statusFilter;
            }
        }
        
        return matchesSearch && matchesStatus;
    });
    
    currentPage = 1;
    renderCampaigns();
}

function changePage(direction) {
    const totalPages = Math.ceil(filteredCampaigns.length / itemsPerPage);
    const newPage = currentPage + direction;
    if (newPage >= 1 && newPage <= totalPages) {
        currentPage = newPage;
        renderCampaigns();
    }
}

// Helper functions
function formatNumber(num) {
    if (num === undefined || num === null) return '0';
    return Number(num).toLocaleString('id-ID');
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    if (allCampaigns.length > 0) {
        filteredCampaigns = [...allCampaigns];
        updateStats();
        renderCampaigns();
    }
});

// ========== SYNC CAMPAIGNS ==========
const syncBtn = document.getElementById('syncCampaignsBtn');
const loadingDiv = document.getElementById('syncLoading');
const syncProgress = document.getElementById('syncProgress');

if (syncBtn) {
    syncBtn.addEventListener('click', async function(e) {
        e.preventDefault();
        
        // Show loading
        if (loadingDiv) {
            loadingDiv.style.display = 'flex';
            if (syncProgress) syncProgress.innerText = 'Fetching campaigns from API...';
        }
        
        try {
            // Step 1: Sync campaigns
            const campaignResponse = await fetch('<?= base_url("bd/sync_campaigns_api") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({ type: 'campaigns' })
            });
            
            const campaignResult = await campaignResponse.json();
            
            if (!campaignResult.success) {
                throw new Error(campaignResult.message || 'Failed to sync campaigns');
            }
            
            if (syncProgress) syncProgress.innerText = `Synced ${campaignResult.campaigns_count || 0} campaigns. Fetching products...`;
            
            // Step 2: Sync products for each campaign
            const campaigns = campaignResult.campaigns || [];
            let totalProducts = 0;
            
            for (let i = 0; i < campaigns.length; i++) {
                const campaign = campaigns[i];
                if (syncProgress) syncProgress.innerText = `Syncing products for campaign ${i+1}/${campaigns.length}: ${campaign.name.substring(0, 40)}...`;
                
                const productResponse = await fetch('<?= base_url("bd/sync_campaigns_api") ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams({ 
                        type: 'products', 
                        campaign_id: campaign.campaign_id,
                        campaign_name: campaign.name
                    })
                });
                
                const productResult = await productResponse.json();
                if (productResult.success) {
                    totalProducts += productResult.products_count || 0;
                }
                
                // Small delay to avoid rate limiting
                await new Promise(resolve => setTimeout(resolve, 500));
            }
            
            if (syncProgress) syncProgress.innerText = `Sync completed! ${campaignResult.campaigns_count} campaigns, ${totalProducts} products synced.`;
            
            showToastGlobal(`✅ Sync completed! ${campaignResult.campaigns_count} campaigns, ${totalProducts} products synced.`, 'success');
            
            // Reload page after 2 seconds
            setTimeout(() => {
                location.reload();
            }, 2000);
            
        } catch (error) {
            console.error('Sync error:', error);
            if (syncProgress) syncProgress.innerText = 'Error: ' + error.message;
            showToastGlobal('Sync failed: ' + error.message, 'error');
            
            setTimeout(() => {
                if (loadingDiv) loadingDiv.style.display = 'none';
            }, 3000);
        }
    });
}

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
    }
    
    toast.textContent = message;
    toast.style.background = type === 'success' ? '#10b981' : '#ef4444';
    toast.style.display = 'block';
    
    setTimeout(() => {
        toast.style.display = 'none';
    }, 3000);
}
</script>