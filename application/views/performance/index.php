<style>
    .performance-container {
        padding: 20px;
    }
    
    .filter-bar {
        background: var(--bg-card);
        border-radius: 20px;
        padding: 16px 20px;
        margin-bottom: 24px;
        border: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }
    
    .filter-group {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .filter-select, .filter-input {
        background: var(--bg-elevated);
        border: 1px solid var(--border);
        padding: 8px 12px;
        border-radius: 12px;
        color: var(--text-primary);
        font-size: 12px;
        min-width: 200px;
    }
    
    .btn-filter {
        background: var(--purple-glow);
        border: 1px solid var(--purple);
        color: var(--purple);
        padding: 8px 20px;
        border-radius: 40px;
        cursor: pointer;
        font-size: 12px;
        transition: var(--transition);
    }
    
    .btn-filter:hover {
        background: var(--purple);
        color: white;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }
    
    .stat-card {
        background: var(--bg-card);
        border-radius: 20px;
        padding: 20px;
        border: 1px solid var(--border);
        cursor: pointer;
        transition: transform 0.2s;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        background: rgba(139, 92, 246, 0.05);
    }
    
    .stat-card .stat-value {
        font-size: 32px;
        font-weight: 700;
        color: #4ade80;
        margin-bottom: 8px;
    }
    
    .stat-card .stat-label {
        font-size: 13px;
        color: var(--text-muted);
        margin-bottom: 8px;
    }
    
    .stat-card .stat-growth {
        font-size: 11px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .stat-growth.positive { color: #4ade80; }
    .stat-growth.negative { color: #ef4444; }
    
    .chart-card {
        background: var(--bg-card);
        border-radius: 20px;
        padding: 20px;
        border: 1px solid var(--border);
        margin-bottom: 24px;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
    
    .table-card {
        background: var(--bg-card);
        border-radius: 20px;
        border: 1px solid var(--border);
        overflow: hidden;
        margin-bottom: 24px;
    }
    
    .table-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .table-header h3 {
        margin: 0;
        font-size: 16px;
        color: var(--text-primary);
    }
    
    .performance-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .performance-table th,
    .performance-table td {
        padding: 12px 16px;
        text-align: left;
        border-bottom: 1px solid var(--border);
        font-size: 13px;
    }
    
    .performance-table th {
        color: var(--text-muted);
        font-weight: 600;
        background: rgba(0,0,0,0.2);
    }
    
    .performance-table tr {
        cursor: pointer;
        transition: background 0.2s;
    }
    
    .performance-table tr:hover {
        background: rgba(139, 92, 246, 0.1);
    }
    
    .rank-badge {
        display: inline-block;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--bg-elevated);
        text-align: center;
        line-height: 28px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .rank-1 { background: linear-gradient(135deg, #ffd700, #ffb700); color: #0a0e1a; }
    .rank-2 { background: linear-gradient(135deg, #c0c0c0, #a8a8a8); color: #0a0e1a; }
    .rank-3 { background: linear-gradient(135deg, #cd7f32, #b8651a); color: white; }
    
    .badge-dashboard {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 500;
    }
    
    .badge-pending { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
    .badge-active { background: rgba(16, 185, 129, 0.15); color: #10b981; }
    .badge-completed { background: rgba(16, 185, 129, 0.15); color: #10b981; }
    .badge-cancelled { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
    
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.9);
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
    
    .modal-content {
        background: #111827;
        border-radius: 28px;
        width: 90%;
        max-width: 900px;
        max-height: 85vh;
        overflow-y: auto;
        padding: 24px;
        border: 1px solid #4ade80;
        color: #e2f0e8;
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 1px solid #2a3346;
    }
    
    .modal-close {
        font-size: 28px;
        cursor: pointer;
        color: #9aaebe;
    }
    
    .modal-close:hover {
        color: #ef4444;
    }
    
    .creator-avatar-large {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
    }
    
    .creator-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }
    
    .video-thumb {
        width: 60px;
        height: 80px;
        border-radius: 8px;
        object-fit: cover;
    }
    
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-top: 20px;
    }
    
    .detail-section {
        background: #0f1420;
        border-radius: 16px;
        padding: 16px;
    }
    
    .detail-section h4 {
        margin: 0 0 12px 0;
        color: #8b5cf6;
    }
    
    .loading-state, .empty-state {
        text-align: center;
        padding: 40px;
        color: var(--text-muted);
    }
    
    .cursor-pointer {
        cursor: pointer;
    }
    
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .filter-bar {
            flex-direction: column;
            align-items: stretch;
        }
        .detail-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="performance-container">
    <div class="page-header">
        <h1 class="page-title"><i class="fas fa-chart-line"></i> Performance</h1>
        <p class="page-subtitle">Creator performance analytics from affiliate campaign</p>
    </div>
    
    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="filter-group">
            <label><i class="fas fa-bullhorn"></i> Campaign:</label>
            <select id="campaignSelect" class="filter-select">
                <option value="">Loading campaigns...</option>
            </select>
        </div>
        <div class="filter-group">
            <button id="refreshBtn" class="btn-filter"><i class="fas fa-sync-alt"></i> Refresh</button>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card" data-type="posts">
            <div class="stat-value" id="creatorsWithPosts">-</div>
            <div class="stat-label"><i class="fas fa-video"></i> Creators with posts</div>
            <div class="stat-growth" id="postsGrowth">-</div>
        </div>
        <div class="stat-card" data-type="sales">
            <div class="stat-value" id="creatorsWithSales">-</div>
            <div class="stat-label"><i class="fas fa-shopping-cart"></i> Creators with sales</div>
            <div class="stat-growth" id="salesGrowth">-</div>
        </div>
        <div class="stat-card" data-type="both">
            <div class="stat-value" id="creatorsWithBoth">-</div>
            <div class="stat-label"><i class="fas fa-chart-line"></i> Creators with posts and sales</div>
            <div class="stat-growth" id="bothGrowth">-</div>
        </div>
        <div class="stat-card" data-type="total">
            <div class="stat-value" id="totalGMV">Rp 0</div>
            <div class="stat-label"><i class="fas fa-money-bill-wave"></i> Total GMV</div>
            <div class="stat-growth" id="gmvGrowth">-</div>
        </div>
    </div>
    
    <!-- Top Creators Table -->
    <div class="table-card">
        <div class="table-header">
            <h3><i class="fas fa-trophy"></i> Top Performing Creators</h3>
            <span style="color: var(--text-muted); font-size: 12px;">Click row to view details</span>
        </div>
        <div style="overflow-x: auto;">
            <table class="performance-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Creator</th>
                        <th>GMV</th>
                        <th>Orders/Videos</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="topCreatorsBody">
                    <tr><td colspan="5" class="loading-state">Select a campaign to view data</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Recent Orders Table -->
    <div class="table-card">
        <div class="table-header">
            <h3><i class="fas fa-clock"></i> Recent Orders</h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="performance-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Order ID</th>
                        <th>Product</th>
                        <th>Creator</th>
                        <th>GMV</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="recentOrdersBody">
                    <tr><td colspan="6" class="loading-state">Select a campaign to view data</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for Creator Detail -->
<div id="creatorModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle"><i class="fab fa-tiktok"></i> Creator Details</h3>
            <span class="modal-close" id="closeModal">&times;</span>
        </div>
        <div id="modalBody">
            <div class="loading-state">Loading...</div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const baseUrl = '<?= base_url() ?>';

// Helper functions
function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

function formatNumber(num) {
    if (num === undefined || num === null) return '0';
    return Number(num).toLocaleString('id-ID');
}

function formatCurrency(num) {
    if (num === undefined || num === null) return 'Rp 0';
    return 'Rp ' + Number(num).toLocaleString('id-ID');
}

// Load campaigns
async function loadCampaigns() {
    const campaignSelect = document.getElementById('campaignSelect');
    campaignSelect.innerHTML = '<option value="">Loading campaigns...</option>';
    
    try {
        const response = await fetch(baseUrl + 'performance/get_campaigns');
        const result = await response.json();
        
        if (result.success && result.data && result.data.length > 0) {
            campaignSelect.innerHTML = '<option value="">-- Select Campaign --</option>';
            result.data.forEach(campaign => {
                const option = document.createElement('option');
                option.value = campaign.id;
                option.textContent = campaign.name;
                campaignSelect.appendChild(option);
            });
        } else {
            campaignSelect.innerHTML = '<option value="">No active campaigns found</option>';
        }
    } catch (error) {
        console.error('Error loading campaigns:', error);
        campaignSelect.innerHTML = '<option value="">Error loading campaigns</option>';
    }
}

// Load performance data
async function loadPerformanceData() {
    const campaignId = document.getElementById('campaignSelect').value;
    if (!campaignId) {
        document.getElementById('topCreatorsBody').innerHTML = '<tr><td colspan="5" class="empty-state">Please select a campaign</td></tr>';
        document.getElementById('recentOrdersBody').innerHTML = '<tr><td colspan="6" class="empty-state">Please select a campaign</td></tr>';
        return;
    }
    
    // Show loading
    document.getElementById('topCreatorsBody').innerHTML = '<tr><td colspan="5" class="loading-state"><i class="fas fa-spinner fa-pulse"></i> Loading creators...</td></tr>';
    document.getElementById('recentOrdersBody').innerHTML = '<tr><td colspan="6" class="loading-state"><i class="fas fa-spinner fa-pulse"></i> Loading orders...</td></tr>';
    
    const formData = new URLSearchParams();
    formData.append('campaign_id', campaignId);
    
    try {
        const response = await fetch(baseUrl + 'performance/get_performance_data', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData
        });
        const result = await response.json();
        
        if (result.success && result.data) {
            updateStats(result.data.stats);
            updateTopCreators(result.data.top_creators);
            updateRecentOrders(result.data.recent_orders);
        } else {
            console.error('Failed to load data:', result.message);
            document.getElementById('topCreatorsBody').innerHTML = `<tr><td colspan="5" class="empty-state">${result.message || 'Failed to load data'}</td></tr>`;
            document.getElementById('recentOrdersBody').innerHTML = `<tr><td colspan="6" class="empty-state">${result.message || 'Failed to load data'}</td></tr>`;
        }
    } catch (error) {
        console.error('Error loading performance data:', error);
        document.getElementById('topCreatorsBody').innerHTML = '<tr><td colspan="5" class="empty-state">Error loading data</td></tr>';
        document.getElementById('recentOrdersBody').innerHTML = '<tr><td colspan="6" class="empty-state">Error loading data</td></tr>';
    }
}

function updateStats(stats) {
    if (!stats) return;
    
    document.getElementById('creatorsWithPosts').innerText = formatNumber(stats.creators_with_posts || 0);
    document.getElementById('creatorsWithSales').innerText = formatNumber(stats.creators_with_sales || 0);
    document.getElementById('creatorsWithBoth').innerText = formatNumber(stats.creators_with_both || 0);
    document.getElementById('totalGMV').innerHTML = formatCurrency(stats.total_gmv || 0);
    
    const growthClass = (stats.gmv_growth || 0) >= 0 ? 'positive' : 'negative';
    const growthIcon = (stats.gmv_growth || 0) >= 0 ? '↑' : '↓';
    document.getElementById('gmvGrowth').innerHTML = `<span class="${growthClass}">${growthIcon} ${Math.abs(stats.gmv_growth || 0)}% vs last period</span>`;
}

function updateTopCreators(creators) {
    const tbody = document.getElementById('topCreatorsBody');
    if (!creators || creators.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="empty-state">No creators found for this campaign</td></tr>';
        return;
    }
    
    let html = '';
    creators.forEach((creator, index) => {
        const rank = index + 1;
        let rankClass = '';
        if (rank === 1) rankClass = 'rank-1';
        else if (rank === 2) rankClass = 'rank-2';
        else if (rank === 3) rankClass = 'rank-3';
        
        const avatarHtml = creator.creator_avatar ? 
            `<img src="${escapeHtml(creator.creator_avatar)}" class="creator-avatar" style="width: 32px; height: 32px; border-radius: 50%;" onerror="this.style.display='none'">` :
            `<div class="creator-avatar" style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #8b5cf6, #4ade80); display: flex; align-items: center; justify-content: center;">
                <span style="font-size: 14px; font-weight: bold; color: white;">${(creator.creator_nick_name || '?').charAt(0).toUpperCase()}</span>
            </div>`;
        
        const statusBadge = creator.free_sample_status === 'AWAITING_COLLECTION' ? 
            '<span class="badge-dashboard badge-pending">Sample Requested</span>' :
            (creator.paid_amount > 0 ? '<span class="badge-dashboard badge-active">Active</span>' : '<span class="badge-dashboard badge-pending">Pending</span>');
        
        const videoCount = creator.video_count || 0;
        const roomCount = creator.room_count || 0;
        const contentText = videoCount > 0 ? `${videoCount} videos` : '';
        const liveText = roomCount > 0 ? `${roomCount} live` : '';
        const contentDisplay = [contentText, liveText].filter(t => t).join(' / ');
        
        html += `
            <tr onclick="showCreatorDetail('${escapeHtml(creator.creator_open_id)}', '${escapeHtml(creator.product_id || '')}', '${escapeHtml(creator.creator_nick_name)}', ${videoCount}, ${roomCount})">
                <td><span class="rank-badge ${rankClass}">${rank}</span></td>
                <td>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        ${avatarHtml}
                        <div>
                            <strong>${escapeHtml(creator.creator_nick_name || creator.creator_username)}</strong>
                            <div style="font-size: 11px; color: var(--text-muted);">@${escapeHtml(creator.creator_username)}</div>
                        </div>
                    </div>
                </td>
                <td>${formatCurrency(creator.paid_amount)}</td>
                <td>${contentDisplay || '-'}</td>
                <td>${statusBadge}</td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}
function updateRecentOrders(orders) {
    const tbody = document.getElementById('recentOrdersBody');
    if (!orders || orders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="empty-state">No orders found for this campaign</td></tr>';
        return;
    }
    
    let html = '';
    orders.forEach(order => {
        const statusClass = order.status ? order.status.toLowerCase() : 'pending';
        html += `
            <tr onclick="showOrderDetail('${order.order_id}')">
                <td>${order.create_time || '-'}</td>
                <td style="font-family: monospace;">${escapeHtml(order.order_id)}</td>
                <td>${escapeHtml(order.product_name ? order.product_name.substring(0, 40) : '-')}${order.product_name && order.product_name.length > 40 ? '...' : ''}</td>
                <td>@${escapeHtml(order.creator_username)}</td>
                <td>${formatCurrency(order.gmv)}</td>
                <td><span class="badge-dashboard badge-${statusClass}">${order.status || 'PENDING'}</span></td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

async function showCreatorDetail(creatorOpenId, productId, creatorName, videoCount, roomCount) {
    if (!creatorOpenId && !creatorName) {
        console.error('No creator identifier provided');
        return;
    }
    
    const modal = document.getElementById('creatorModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    
    modalTitle.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Loading...';
    modalBody.innerHTML = '<div class="loading-state"><i class="fas fa-spinner fa-pulse fa-2x"></i><p>Loading creator details...</p></div>';
    modal.classList.add('active');
    
    const campaignId = document.getElementById('campaignSelect').value;
    const formData = new URLSearchParams();
    if (creatorOpenId) formData.append('creator_open_id', creatorOpenId);
    if (campaignId) formData.append('campaign_id', campaignId);
    if (productId) formData.append('product_id', productId);
    if (creatorName) formData.append('creator_name', creatorName);
    if (videoCount) formData.append('video_count', videoCount);
    if (roomCount) formData.append('room_count', roomCount);
    
    try {
        const response = await fetch(baseUrl + 'performance/get_creator_detail', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData
        });
        const result = await response.json();
        
        if (result.success && result.data) {
            const data = result.data;
            const detail = data.creator_detail || {};
            const avatarUrl = detail.avatar_url;
            
            const avatarHtml = avatarUrl ? 
                `<img src="${escapeHtml(avatarUrl)}" class="creator-avatar-large" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;" onerror="this.style.display='none'">` :
                `<div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #8b5cf6, #4ade80); display: flex; align-items: center; justify-content: center;">
                    <i class="fab fa-tiktok" style="font-size: 40px; color: white;"></i>
                </div>`;
            
            modalTitle.innerHTML = `<i class="fab fa-tiktok"></i> Creator: ${escapeHtml(detail.nickname || creatorName)}`;
            
            const displayGmv = data.total_gmv || detail.gmv || 0;
            const displayVideos = data.total_videos || videoCount || 0;
            const displayRooms = roomCount || 0;
            
            modalBody.innerHTML = `
                <div style="display: flex; gap: 20px; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #2a3346; flex-wrap: wrap;">
                    ${avatarHtml}
                    <div>
                        <h3 style="margin: 0 0 5px 0;">${escapeHtml(detail.nickname || creatorName)}</h3>
                        <div style="color: #9aaebe;">@${escapeHtml(detail.username || creatorName)}</div>
                        <div style="margin-top: 8px; display: flex; gap: 16px; flex-wrap: wrap;">
                            <span>👥 ${formatNumber(detail.followers || 0)} followers</span>
                            <span>💰 GMV: ${formatCurrency(displayGmv)}</span>
                            <span>📦 Orders: ${formatNumber(data.total_orders || 0)}</span>
                            <span>📹 Videos: ${formatNumber(displayVideos)}</span>
                            ${displayRooms > 0 ? `<span>🎬 Live: ${formatNumber(displayRooms)}</span>` : ''}
                        </div>
                        ${detail.bio ? `<div style="margin-top: 8px; font-size: 12px; color: #9aaebe;">${escapeHtml(detail.bio)}</div>` : ''}
                    </div>
                </div>
                <div class="detail-grid">
                    <div class="detail-section">
                        <h4><i class="fas fa-video"></i> Content Statistics (${displayVideos})</h4>
                        ${renderContentStats(data.content_stats)}
                    </div>
                    <div class="detail-section">
                        <h4><i class="fas fa-clock"></i> Recent Orders (${data.total_orders || 0})</h4>
                        ${renderRecentOrders(data.recent_orders)}
                    </div>
                </div>
            `;
        } else {
            modalBody.innerHTML = `<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>${result.message || 'Failed to load creator details'}</p></div>`;
        }
    } catch (error) {
        console.error('Error loading creator detail:', error);
        modalBody.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading data</p></div>';
    }
}


function renderContentStats(stats) {
    if (!stats || stats.length === 0) {
        return '<div class="empty-state">No content statistics available</div>';
    }
    
    let html = '<div style="max-height: 350px; overflow-y: auto;">';
    stats.forEach(video => {
        html += `
            <div style="padding: 12px; border-bottom: 1px solid #2a3346; display: flex; gap: 12px;">
                ${video.cover_img_url ? 
                    `<img src="${escapeHtml(video.cover_img_url)}" style="width: 60px; height: 80px; object-fit: cover; border-radius: 8px;" onerror="this.style.display='none'">` : 
                    `<div style="width: 60px; height: 80px; background: #1e293b; border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i class="fab fa-tiktok" style="font-size: 24px; color: #4ade80;"></i></div>`
                }
                <div style="flex: 1;">
                    <div><strong>${video.content_type || 'VIDEO'}</strong></div>
                    <div style="font-size: 11px; margin-top: 4px;">
                        👁️ ${formatNumber(video.view_count)} views | ❤️ ${formatNumber(video.like_count)} likes
                    </div>
                    <div style="font-size: 11px;">💰 ${formatCurrency(video.paid_amount)} GMV | 📦 ${formatNumber(video.paid_order_num)} orders</div>
                    ${video.source_url ? `<div style="margin-top: 6px;"><a href="${video.source_url}" target="_blank" style="color: #4ade80; font-size: 11px;"><i class="fab fa-tiktok"></i> Watch Video</a></div>` : ''}
                </div>
            </div>
        `;
    });
    html += '</div>';
    return html;
}

function renderRecentOrders(orders) {
    if (!orders || orders.length === 0) {
        return '<div class="empty-state">No recent orders</div>';
    }
    
    let html = '<div style="max-height: 350px; overflow-y: auto;">';
    orders.forEach(order => {
        html += `
            <div style="padding: 10px; border-bottom: 1px solid #2a3346; cursor: pointer;" onclick="showOrderDetail('${order.order_id}')">
                <div><strong>${escapeHtml(order.product_name ? order.product_name.substring(0, 50) : '-')}</strong></div>
                <div style="font-size: 11px; margin-top: 4px;">💰 ${formatCurrency(order.gmv)} | 📅 ${order.create_time || '-'}</div>
            </div>
        `;
    });
    html += '</div>';
    return html;
}
async function showOrderDetail(orderId) {
    if (!orderId) return;
    
    const modal = document.getElementById('creatorModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    
    modalTitle.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Loading...';
    modalBody.innerHTML = '<div class="loading-state">Loading order details...</div>';
    modal.classList.add('active');
    
    try {
        const response = await fetch(baseUrl + 'performance/get_order_detail', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ order_id: orderId })
        });
        const result = await response.json();
        
        if (result.success && result.data) {
            const order = result.data;
            modalTitle.innerHTML = `<i class="fas fa-shopping-cart"></i> Order: ${orderId}`;
            
            modalBody.innerHTML = `
                <div style="background: #0f1420; border-radius: 16px; padding: 20px;">
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                        <div><strong>Order ID:</strong> ${escapeHtml(order.order_id)}</div>
                        <div><strong>Status:</strong> <span class="badge-dashboard badge-${(order.order_status || 'pending').toLowerCase()}">${order.order_status || 'PENDING'}</span></div>
                        <div><strong>Date:</strong> ${order.create_time_formatted || '-'}</div>
                        <div><strong>Creator:</strong> @${escapeHtml(order.creator_username)}</div>
                        <div><strong>Product:</strong> ${escapeHtml(order.product_name)}</div>
                        <div><strong>Quantity:</strong> ${order.quantity || 1}</div>
                        <div><strong>GMV:</strong> ${formatCurrency(order.affiliate_gmv)}</div>
                        <div><strong>Commission:</strong> ${formatCurrency(order.estimated_affiliate_commission)}</div>
                    </div>
                </div>
            `;
        } else {
            modalBody.innerHTML = `<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>${result.message || 'Failed to load order details'}</p></div>`;
        }
    } catch (error) {
        console.error('Error loading order detail:', error);
        modalBody.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading data</p></div>';
    }
}

// Modal close
document.getElementById('closeModal')?.addEventListener('click', () => {
    document.getElementById('creatorModal').classList.remove('active');
});

document.getElementById('creatorModal')?.addEventListener('click', (e) => {
    if (e.target === document.getElementById('creatorModal')) {
        document.getElementById('creatorModal').classList.remove('active');
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('creatorModal');
        if (modal && modal.classList.contains('active')) {
            modal.classList.remove('active');
        }
    }
});

// Refresh button
document.getElementById('refreshBtn')?.addEventListener('click', () => {
    loadPerformanceData();
});

// Campaign select change
document.getElementById('campaignSelect')?.addEventListener('change', () => {
    loadPerformanceData();
});

// Initial load
document.addEventListener('DOMContentLoaded', () => {
    loadCampaigns();
});
</script>