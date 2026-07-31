<!-- file: application/views/is/creators.php -->
<style>
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
    
    .date-filter {
        display: flex;
        align-items: center;
        gap: 8px;
        background: var(--bg-elevated);
        padding: 4px 12px;
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
        padding: 6px 12px;
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
        padding: 16px 20px;
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
        background: linear-gradient(135deg, #4ade80, #22c55e);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }
    
    .stat-label {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 4px;
    }
    
    .stat-period {
        font-size: 9px;
        color: var(--text-muted);
        margin-top: 4px;
    }
    
    .search-filter-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 20px;
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
        width: 250px;
    }
    
    .filter-select {
        background: var(--bg-elevated);
        border: 1px solid var(--border);
        padding: 8px 16px;
        border-radius: 40px;
        color: var(--text-secondary);
        font-size: 12px;
        cursor: pointer;
    }
    
    .creators-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 20px;
    }
    
    .creator-card {
        background: var(--bg-elevated);
        border-radius: 20px;
        border: 1px solid var(--border-light);
        overflow: hidden;
        transition: all 0.2s;
        cursor: pointer;
    }
    
    .creator-card:hover {
        border-color: var(--purple);
        transform: translateY(-3px);
        box-shadow: var(--glow-purple);
    }
    
    .creator-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px;
        background: linear-gradient(135deg, var(--bg-card), var(--bg-elevated));
        border-bottom: 1px solid var(--border-light);
    }
    
    .creator-avatar {
        width: 55px;
        height: 55px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--purple);
    }
    
    .creator-avatar-placeholder {
        width: 55px;
        height: 55px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--purple), var(--blue));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
    }
    
    .creator-info h3 {
        font-size: 15px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 4px;
    }
    
    .creator-info .username {
        font-size: 11px;
        color: var(--text-muted);
    }
    
    .creator-stats {
        display: flex;
        justify-content: space-around;
        padding: 12px 16px;
        border-bottom: 1px solid var(--border-light);
    }
    
    .stat-item {
        text-align: center;
    }
    
    .stat-item .stat-number {
        font-size: 18px;
        font-weight: 700;
        color: #4ade80;
    }
    
    .stat-item .stat-text {
        font-size: 10px;
        color: var(--text-muted);
    }
    
    .creator-campaigns {
        padding: 12px 16px;
        max-height: 180px;
        overflow-y: auto;
    }
    
    .campaign-badge {
        background: var(--bg-card);
        border-radius: 12px;
        padding: 8px 10px;
        margin-bottom: 8px;
        font-size: 11px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .campaign-name {
        color: var(--purple);
        font-weight: 500;
    }
    
    .campaign-gmv {
        color: #4ade80;
    }
    
    .badge-status {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 600;
    }
    
    .badge-active {
        background: rgba(16, 185, 129, 0.15);
        color: #10b981;
    }
    
    .badge-pending {
        background: rgba(245, 158, 11, 0.15);
        color: #f59e0b;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-muted);
    }
    
    .empty-state i {
        font-size: 48px;
        color: var(--purple);
        margin-bottom: 16px;
        display: block;
    }
    
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
        transition: all 0.2s;
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
    
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.7);
        align-items: center;
        justify-content: center;
    }
    
    .modal-content {
        background: var(--bg-card);
        width: 90%;
        max-width: 700px;
        max-height: 80vh;
        overflow-y: auto;
        border-radius: 24px;
        border: 1px solid var(--border);
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
        position: sticky;
        top: 0;
        background: var(--bg-card);
        z-index: 10;
    }
    
    .modal-header h3 {
        color: var(--text-primary);
        margin: 0;
    }
    
    .close {
        color: var(--text-muted);
        font-size: 28px;
        cursor: pointer;
    }
    
    .close:hover {
        color: var(--text-primary);
    }
    
    .modal-body {
        padding: 20px;
    }
    
    .creator-profile {
        display: flex;
        gap: 20px;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border);
    }
    
    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
    }
    
    .profile-info h4 {
        font-size: 18px;
        margin-bottom: 4px;
    }
    
    .profile-stats {
        display: flex;
        gap: 20px;
        margin-top: 8px;
    }
    
    .detail-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 12px;
        margin-bottom: 20px;
    }
    
    .detail-stat-card {
        background: var(--bg-elevated);
        padding: 12px;
        border-radius: 16px;
        text-align: center;
    }
    
    .detail-stat-value {
        font-size: 20px;
        font-weight: 700;
        color: #4ade80;
    }
    
    .campaign-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 12px;
    }
    
    .campaign-table th,
    .campaign-table td {
        padding: 10px 8px;
        text-align: left;
        border-bottom: 1px solid var(--border);
        font-size: 12px;
    }
    
    .campaign-table th {
        color: var(--purple);
        font-weight: 600;
    }
    
    .daily-chart {
        margin-top: 20px;
        overflow-x: auto;
    }
    
    .chart-bars {
        display: flex;
        gap: 4px;
        align-items: flex-end;
        min-height: 150px;
        padding: 10px 0;
    }
    
    .chart-bar {
        flex: 1;
        background: linear-gradient(180deg, var(--purple), var(--blue));
        border-radius: 4px 4px 0 0;
        min-width: 30px;
        transition: height 0.3s;
        position: relative;
    }
    
    .chart-label {
        text-align: center;
        font-size: 9px;
        color: var(--text-muted);
        margin-top: 4px;
    }
    
    @media (max-width: 768px) {
        .creators-grid {
            grid-template-columns: 1fr;
        }
        .stats-summary {
            grid-template-columns: 1fr 1fr;
        }
        .search-filter-bar {
            flex-direction: column;
            align-items: stretch;
        }
        .search-box input {
            width: 100%;
        }
    }
</style>

<div class="page-header">
    <div>
     
        <h1 class="page-title"><i class="fas fa-users"></i> Creators Management</h1>
        <p class="page-subtitle">Data dari Campaign Creator Performance & Realtime Orders</p>
    </div>
    <div class="date-filter">
        <input type="date" id="startDateFilter" class="date-input" value="<?= $start_date ?>">
        <span style="color:var(--text-muted);">s/d</span>
        <input type="date" id="endDateFilter" class="date-input" value="<?= $end_date ?>">
        <button id="applyDateFilterBtn" class="btn-filter">
            <i class="fas fa-calendar-alt"></i> Filter
        </button>
    </div>
</div>

<div class="stats-summary">
    <div class="stat-card">
        <div class="stat-value" id="totalCreatorsStat"><?= number_format($summary['total_creators'] ?? 0) ?></div>
        <div class="stat-label">Total Creators</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" id="totalGMVStat">Rp <?= number_format($summary['total_gmv'] ?? 0, 0, ',', '.') ?></div>
        <div class="stat-label">Total GMV</div>
        <div class="stat-period">Periode: <?= date('d M Y', strtotime($start_date)) ?> - <?= date('d M Y', strtotime($end_date)) ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value" id="totalOrdersStat"><?= number_format($summary['total_orders'] ?? 0) ?></div>
        <div class="stat-label">Total Orders</div>
    </div>
    <!--<div class="stat-card">-->
    <!--    <div class="stat-value" id="totalCommissionStat">Rp <?= number_format($summary['total_commission'] ?? 0, 0, ',', '.') ?></div>-->
    <!--    <div class="stat-label">Total Commission</div>-->
    <!--</div>-->
</div>

<div class="search-filter-bar">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchCreatorInput" placeholder="Cari creator username...">
    </div>
    <select id="statusFilter" class="filter-select">
        <option value="all">All Status</option>
        <option value="active">Active (GMV > 0)</option>
        <option value="inactive">Inactive (GMV = 0)</option>
    </select>
</div>

<div id="creatorsGrid" class="creators-grid">
    <div class="loading" style="text-align:center; padding:40px;">
        <i class="fas fa-spinner fa-pulse fa-2x" style="color:var(--purple);"></i>
        <p>Loading creators...</p>
    </div>
</div>

<div class="pagination-container" id="paginationContainer">
    <button id="prevPageBtn" disabled><i class="fas fa-chevron-left"></i> Prev</button>
    <span class="page-info" id="pageInfo">Page 1</span>
    <button id="nextPageBtn">Next <i class="fas fa-chevron-right"></i></button>
</div>

<!-- Modal Detail Creator -->
<div id="creatorDetailModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fab fa-tiktok"></i> Creator Detail</h3>
            <span class="close" onclick="closeCreatorModal()">&times;</span>
        </div>
        <div class="modal-body" id="modalBody">
            <div class="loading">Loading...</div>
        </div>
    </div>
</div>

<script>
// ========== DATA ==========
let allCreators = [];
let currentPage = 1;
let itemsPerPage = 12;
let filteredCreators = [];

// ========== HELPER FUNCTIONS ==========
function formatNumber(num) {
    if (num === undefined || num === null) return '0';
    return Number(num).toLocaleString('id-ID');
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

function formatCurrency(num) {
    return 'Rp ' + formatNumber(num);
}

// ========== RENDER CREATORS ==========
function renderCreators() {
    const start = (currentPage - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const pageCreators = filteredCreators.slice(start, end);
    const grid = document.getElementById('creatorsGrid');
    
    if (!grid) return;
    
    if (pageCreators.length === 0) {
        grid.innerHTML = '<div class="empty-state"><i class="fas fa-users-slash"></i><p>No creators found matching your filters.</p></div>';
        document.getElementById('paginationContainer').style.display = 'none';
        return;
    }
    
    let html = '';
    for (let creator of pageCreators) {
        const avatarHtml = creator.avatar 
            ? `<img src="${escapeHtml(creator.avatar)}" class="creator-avatar" onerror="this.src=\'https://ui-avatars.com/api/?name=${encodeURIComponent(creator.username)}&background=8b5cf6&color=fff\'">`
            : `<div class="creator-avatar-placeholder"><i class="fab fa-tiktok"></i></div>`;
        
        const statusClass = creator.total_gmv > 0 ? 'badge-active' : 'badge-pending';
        const statusText = creator.total_gmv > 0 ? 'ACTIVE' : 'INACTIVE';
        
        html += `
            <div class="creator-card" onclick="viewCreatorDetail('${escapeHtml(creator.username)}')">
                <div class="creator-header">
                    ${avatarHtml}
                    <div class="creator-info">
                        <h3>${escapeHtml(creator.full_name || creator.username)}</h3>
                        <div class="username">@${escapeHtml(creator.username)}</div>
                        <span class="badge-status ${statusClass}" style="margin-top:4px; display:inline-block;">
                            <i class="fas ${creator.total_gmv > 0 ? 'fa-check-circle' : 'fa-clock'}"></i> ${statusText}
                        </span>
                    </div>
                </div>
                <div class="creator-stats">
                    <div class="stat-item">
                        <div class="stat-number">${formatNumber(creator.total_gmv)}</div>
                        <div class="stat-text">GMV</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">${formatNumber(creator.total_orders)}</div>
                        <div class="stat-text">Orders</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">${formatNumber(creator.follower_count)}</div>
                        <div class="stat-text">Followers</div>
                    </div>
                </div>
                <div class="creator-campaigns">
                    <div style="font-size:10px; color:var(--text-muted); margin-bottom:8px;">
                        <i class="fas fa-chart-line"></i> Active Campaigns: ${creator.active_campaigns}
                    </div>
                    ${creator.campaigns.slice(0, 2).map(camp => `
                        <div class="campaign-badge">
                            <span class="campaign-name">${escapeHtml(camp.campaign_name || camp.product_name || 'Campaign')}</span>
                            <span class="campaign-gmv">${formatCurrency(camp.paid_amount)}</span>
                        </div>
                    `).join('')}
                    ${creator.campaigns.length > 2 ? `<div style="font-size:10px; color:var(--text-muted); text-align:center;">+${creator.campaigns.length - 2} more campaigns</div>` : ''}
                </div>
            </div>
        `;
    }
    grid.innerHTML = html;
    
    // Update pagination
    const totalPages = Math.ceil(filteredCreators.length / itemsPerPage);
    document.getElementById('pageInfo').innerText = `Page ${currentPage} of ${totalPages || 1}`;
    document.getElementById('prevPageBtn').disabled = currentPage === 1;
    document.getElementById('nextPageBtn').disabled = currentPage === totalPages || totalPages === 0;
    document.getElementById('paginationContainer').style.display = filteredCreators.length > itemsPerPage ? 'flex' : 'none';
}

// ========== FILTERS ==========
function applyFilters() {
    const searchTerm = document.getElementById('searchCreatorInput')?.value.toLowerCase() || '';
    const statusFilter = document.getElementById('statusFilter')?.value || 'all';
    
    filteredCreators = allCreators.filter(creator => {
        const matchesSearch = (creator.username || '').toLowerCase().includes(searchTerm) ||
                              (creator.full_name || '').toLowerCase().includes(searchTerm);
        
        let matchesStatus = true;
        if (statusFilter === 'active') {
            matchesStatus = creator.total_gmv > 0;
        } else if (statusFilter === 'inactive') {
            matchesStatus = creator.total_gmv === 0;
        }
        
        return matchesSearch && matchesStatus;
    });
    
    currentPage = 1;
    renderCreators();
}

// ========== VIEW CREATOR DETAIL ==========
async function viewCreatorDetail(username) {
    const modal = document.getElementById('creatorDetailModal');
    const modalBody = document.getElementById('modalBody');
    
    modal.style.display = 'flex';
    modalBody.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-pulse"></i> Loading creator details...</div>';
    
    const startDate = document.getElementById('startDateFilter').value;
    const endDate = document.getElementById('endDateFilter').value;
    
    try {
        const response = await fetch('<?= base_url("is/get_creator_detail") ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `creator_username=${encodeURIComponent(username)}&start_date=${startDate}&end_date=${endDate}`
        });
        const result = await response.json();
        
        if (!result.success) {
            modalBody.innerHTML = `<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>${result.message || 'Failed to load data'}</p></div>`;
            return;
        }
        
        const data = result.data;
        const avatarHtml = data.avatar 
            ? `<img src="${escapeHtml(data.avatar)}" class="profile-avatar" onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(data.username)}&background=8b5cf6&color=fff'">`
            : `<div class="profile-avatar" style="background:linear-gradient(135deg,var(--purple),var(--blue)); display:flex; align-items:center; justify-content:center; font-size:32px;"><i class="fab fa-tiktok"></i></div>`;
        
        let campaignsHtml = '';
        if (data.campaigns && data.campaigns.length > 0) {
            campaignsHtml = `
                <table class="campaign-table">
                    <thead>
                        <tr><th>Campaign</th><th>Product</th><th>Paid Amount</th><th>Videos</th></tr>
                    </thead>
                    <tbody>
                        ${data.campaigns.map(camp => `
                            <tr>
                                <td>${escapeHtml(camp.campaign_name || '-')}</td>
                                <td>${escapeHtml(camp.product_name || '-')}</td>
                               
                                <td class="campaign-gmv">${formatCurrency(camp.paid_amount)}</td>
                                <td>${camp.video_count}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
        } else {
            campaignsHtml = '<div class="empty-state">No campaign data found</div>';
        }
        
        // Daily performance chart
        let chartHtml = '';
        if (data.daily_performance && data.daily_performance.length > 0) {
            const maxGmv = Math.max(...data.daily_performance.map(d => d.daily_gmv), 1);
            chartHtml = `
                <div class="daily-chart">
                    <div style="font-size:12px; margin-bottom:8px;"><i class="fas fa-chart-line"></i> Daily GMV Trend</div>
                    <div class="chart-bars">
                        ${data.daily_performance.map(day => {
                            const height = (day.daily_gmv / maxGmv) * 100;
                            return `
                                <div style="flex:1; text-align:center;">
                                    <div class="chart-bar" style="height: ${height}px; min-width:0;"></div>
                                    <div class="chart-label">${day.date.slice(5)}</div>
                                </div>
                            `;
                        }).join('')}
                    </div>
                </div>
            `;
        }
        
        modalBody.innerHTML = `
            <div class="creator-profile">
                ${avatarHtml}
                <div class="profile-info">
                    <h4>${escapeHtml(data.nick_name || data.username)}</h4>
                    <div>@${escapeHtml(data.username)}</div>
                    <div class="profile-stats">
                        <span><i class="fas fa-users"></i> ${formatNumber(data.follower_count)} Followers</span>
                        <span><i class="fas fa-video"></i> ${formatNumber(data.total_videos)} Videos</span>
                    </div>
                </div>
            </div>
            
            <div class="detail-stats">
                <div class="detail-stat-card">
                    <div class="detail-stat-value">${formatCurrency(data.total_gmv)}</div>
                    <div>Total GMV</div>
                </div>
                <div class="detail-stat-card">
                    <div class="detail-stat-value">${formatNumber(data.total_orders)}</div>
                    <div>Total Orders</div>
                </div>
              
            </div>
            
            ${chartHtml}
            
            <div style="margin-top:20px;">
                <div style="font-size:14px; font-weight:600; margin-bottom:12px;">
                    <i class="fas fa-rocket"></i> Campaign Performance
                </div>
                ${campaignsHtml}
            </div>
        `;
        
    } catch (error) {
        modalBody.innerHTML = `<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Network error: ${error.message}</p></div>`;
    }
}

function closeCreatorModal() {
    document.getElementById('creatorDetailModal').style.display = 'none';
}

// ========== DATE FILTER ==========
function applyDateFilter() {
    const startDate = document.getElementById('startDateFilter').value;
    const endDate = document.getElementById('endDateFilter').value;
    
    if (!startDate || !endDate) {
        showToastGlobal('Pilih tanggal terlebih dahulu', 'error');
        return;
    }
    
    if (startDate > endDate) {
        showToastGlobal('Tanggal mulai tidak boleh lebih besar dari tanggal akhir', 'error');
        return;
    }
    
    window.location.href = baseUrlIS + `is/creators?start_date=${startDate}&end_date=${endDate}`;
}

// ========== EVENT LISTENERS ==========
document.getElementById('searchCreatorInput')?.addEventListener('keyup', applyFilters);
document.getElementById('statusFilter')?.addEventListener('change', applyFilters);
document.getElementById('applyDateFilterBtn')?.addEventListener('click', applyDateFilter);

document.getElementById('prevPageBtn')?.addEventListener('click', () => {
    if (currentPage > 1) {
        currentPage--;
        renderCreators();
    }
});

document.getElementById('nextPageBtn')?.addEventListener('click', () => {
    const totalPages = Math.ceil(filteredCreators.length / itemsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        renderCreators();
    }
});

// Close modal on outside click
window.onclick = function(event) {
    const modal = document.getElementById('creatorDetailModal');
    if (event.target === modal) {
        closeCreatorModal();
    }
}

// ========== LOAD INITIAL DATA ==========
<?php if (!empty($creators)): ?>
allCreators = <?= json_encode(array_map(function($c) {
    return [
        'username' => $c->username,
        'full_name' => $c->full_name,
        'avatar' => $c->avatar ?? '',
        'follower_count' => intval($c->follower_count ?? 0),
        'total_gmv' => floatval($c->total_gmv ?? 0),
        'total_orders' => intval($c->total_orders ?? 0),
        'total_commission' => floatval($c->total_commission ?? 0),
        'active_campaigns' => intval($c->active_campaigns ?? 0),
        'campaigns' => array_map(function($camp) {
            return [
                'campaign_name' => $camp->campaign_name ?? '',
                'product_name' => $camp->product_name ?? '',
                'paid_amount' => floatval($camp->paid_amount ?? 0),
                'video_count' => intval($camp->video_count ?? 0),
                'commission' => intval($camp->commission ?? 0)
            ];
        }, $c->campaigns ?? [])
    ];
}, $creators)) ?>;
filteredCreators = [...allCreators];
renderCreators();
<?php endif; ?>
</script>