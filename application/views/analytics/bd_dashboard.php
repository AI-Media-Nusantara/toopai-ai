<style>
    .analytics-container { padding: 20px; }
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
    .filter-group { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
    .filter-select, .filter-input {
        background: var(--bg-elevated);
        border: 1px solid var(--border);
        padding: 8px 12px;
        border-radius: 12px;
        color: var(--text-primary);
        font-size: 12px;
    }
    .btn-filter {
        background: var(--purple-glow);
        border: 1px solid var(--purple);
        color: var(--purple);
        padding: 8px 20px;
        border-radius: 40px;
        cursor: pointer;
        font-size: 12px;
    }
    .btn-filter:hover { background: var(--purple); color: white; }
    .analytics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 24px;
        margin-bottom: 24px;
    }
    .analytics-card {
        background: var(--bg-card);
        border-radius: 20px;
        border: 1px solid var(--border);
        overflow: hidden;
    }
    .card-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .card-header h3 { color: var(--text-primary); font-size: 16px; margin: 0; }
    .card-header i { color: var(--purple); }
    .card-body { padding: 16px 20px; max-height: 600px; overflow-y: auto; }
    .ranking-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid var(--border);
        cursor: pointer;
        transition: background 0.2s;
    }
    .ranking-item:hover { background: rgba(139, 92, 246, 0.1); margin: 0 -8px; padding: 12px 8px; border-radius: 12px; }
    .ranking-number {
        width: 30px;
        height: 30px;
        background: var(--bg-elevated);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 12px;
        flex-shrink: 0;
    }
    .ranking-number.top-1 { background: linear-gradient(135deg, #ffd700, #ffb700); color: #0a0e1a; }
    .ranking-number.top-2 { background: linear-gradient(135deg, #c0c0c0, #a8a8a8); color: #0a0e1a; }
    .ranking-number.top-3 { background: linear-gradient(135deg, #cd7f32, #b8651a); color: white; }
    .ranking-info { flex: 1; }
    .ranking-name { font-weight: 600; color: var(--text-primary); font-size: 13px; margin-bottom: 4px; }
    .ranking-stats { display: flex; gap: 16px; font-size: 11px; color: var(--text-muted); flex-wrap: wrap; }
    .ranking-value { color: #4ade80; font-weight: 600; }
    .product-image { width: 50px; height: 50px; border-radius: 10px; background: #1e293b; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
    .creator-avatar { width: 40px; height: 40px; border-radius: 50%; background: #1e293b; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
    .brand-badge { background: rgba(74, 222, 128, 0.15); padding: 2px 8px; border-radius: 20px; font-size: 10px; color: #4ade80; display: inline-flex; align-items: center; gap: 4px; }
    .video-link { color: #4ade80; text-decoration: none; font-size: 10px; }
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
    .modal-overlay.active { visibility: visible; opacity: 1; }
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
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid #2a3346; }
    .modal-close { font-size: 28px; cursor: pointer; color: #9aaebe; }
    .modal-close:hover { color: #ef4444; }
    .sub-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
    .sub-section { background: #0f1420; border-radius: 16px; padding: 16px; }
    .sub-section h4 { margin: 0 0 12px 0; color: var(--purple); font-size: 14px; }
    .sub-item { padding: 10px; border-bottom: 1px solid #2a3346; cursor: pointer; transition: background 0.2s; }
    .sub-item:hover { background: rgba(139, 92, 246, 0.1); border-radius: 8px; }
    .loading-state, .empty-state { text-align: center; padding: 40px; color: var(--text-muted); }
    @media (max-width: 768px) {
        .analytics-grid { grid-template-columns: 1fr; }
        .filter-bar { flex-direction: column; align-items: stretch; }
        .filter-group { justify-content: space-between; }
        .sub-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="analytics-container">
    <div class="page-header">
        <div>
             <?php if ($this->session->userdata('role') == 'BD'): ?>
        <a href="<?= base_url('bd/dashboard') ?>" class="back-to-dashboard">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
        <?php else: ?>
        <a href="<?= base_url('is/dashboard') ?>" class="back-to-dashboard">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
        <?php endif; ?>
            <h1 class="page-title"><i class="fas fa-chart-line"></i> Analytics</h1>
            <p class="page-subtitle">Bestselling products, creators, and videos from TikTok Shop</p>
        </div>
    </div>
    
    <div class="filter-bar">
        <div class="filter-group">
            <label>Time Period:</label>
            <select id="timeSlot" class="filter-select">
                <option value="7D">Last 7 Days</option>
                <option value="30D">Last 30 Days</option>
                <option value="90D">Last 90 Days</option>
                <option value="CUSTOM">Custom Range</option>
            </select>
        </div>
        <div id="customDateRange" style="display: none;" class="filter-group">
            <input type="date" id="startDate" class="filter-input">
            <span>to</span>
            <input type="date" id="endDate" class="filter-input">
        </div>
        <div class="filter-group">
            <label>Category:</label>
            <select id="categoryFilter" class="filter-select">
                <option value="All">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <?php if ($cat != 'All'): ?>
                    <option value="<?= $cat ?>"><?= $cat ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <button id="applyFilterBtn" class="btn-filter"><i class="fas fa-calendar-alt"></i> Apply</button>
        </div>
    </div>
    
    <div class="analytics-grid">
        <div class="analytics-card">
            <div class="card-header">
                <h3><i class="fas fa-box"></i> Bestselling Products</h3>
                <i class="fas fa-chart-simple"></i>
            </div>
            <div class="card-body" id="productsList"><div class="loading-state"><i class="fas fa-spinner fa-pulse fa-2x"></i><p>Loading products...</p></div></div>
        </div>
        
        <div class="analytics-card">
            <div class="card-header">
                <h3><i class="fas fa-users"></i> Bestselling Creators</h3>
                <i class="fas fa-trophy"></i>
            </div>
            <div class="card-body" id="creatorsList"><div class="loading-state"><i class="fas fa-spinner fa-pulse fa-2x"></i><p>Loading creators...</p></div></div>
        </div>
        
        <div class="analytics-card">
            <div class="card-header">
                <h3><i class="fas fa-video"></i> Bestselling Videos</h3>
                <i class="fas fa-play-circle"></i>
            </div>
            <div class="card-body" id="videosList"><div class="loading-state"><i class="fas fa-spinner fa-pulse fa-2x"></i><p>Loading videos...</p></div></div>
        </div>
    </div>
</div>

<div id="productModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle"><i class="fas fa-box"></i> Product Details</h3>
            <span class="modal-close" id="closeModal">&times;</span>
        </div>
        <div id="modalBody"><div class="loading-state">Loading...</div></div>
    </div>
</div>

<script>
const baseUrl = '<?= base_url() ?>';
let currentProductId = null;

// ========== HELPER FUNCTIONS ==========
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

// ========== SHOW/HIDE CUSTOM DATE RANGE ==========
document.getElementById('timeSlot')?.addEventListener('change', function() {
    const customRange = document.getElementById('customDateRange');
    if (this.value === 'CUSTOM') {
        customRange.style.display = 'flex';
        const today = new Date();
        const weekAgo = new Date(today);
        weekAgo.setDate(today.getDate() - 7);
        document.getElementById('startDate').value = weekAgo.toISOString().split('T')[0];
        document.getElementById('endDate').value = today.toISOString().split('T')[0];
    } else {
        customRange.style.display = 'none';
    }
});

// Apply filter
document.getElementById('applyFilterBtn')?.addEventListener('click', () => {
    loadAllData();
});

// Load all data
async function loadAllData() {
    await loadBestsellingProducts();
    await loadBestsellingCreators();
    await loadBestsellingVideos();
}

// ========== LOAD BESTSELLING PRODUCTS ==========
async function loadBestsellingProducts() {
    const container = document.getElementById('productsList');
    container.innerHTML = '<div class="loading-state"><i class="fas fa-spinner fa-pulse fa-2x"></i><p>Loading products...</p></div>';
    
    const timeSlot = document.getElementById('timeSlot').value;
    let startDate = document.getElementById('startDate')?.value;
    let endDate = document.getElementById('endDate')?.value;
    const category = document.getElementById('categoryFilter')?.value || 'All';
    const latestAvailableDate = '2026-05-17';
    
    const formData = new URLSearchParams();
    formData.append('time_slot', timeSlot);
    if (timeSlot === 'CUSTOM' && startDate && endDate) {
        if (startDate > latestAvailableDate) startDate = latestAvailableDate;
        if (endDate > latestAvailableDate) endDate = latestAvailableDate;
        formData.append('start_date', startDate);
        formData.append('end_date', endDate);
    }
    formData.append('category', category);
    formData.append('limit', 50);
    
    try {
        const response = await fetch(baseUrl + 'analytics/get_bestselling_products', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData
        });
        const data = await response.json();
        
        if (data.success && data.data && data.data.length > 0) {
            let html = '';
            data.data.forEach((item, index) => {
                const rank = index + 1;
                let rankClass = rank === 1 ? 'top-1' : (rank === 2 ? 'top-2' : (rank === 3 ? 'top-3' : ''));
                let productName = item.product_name.length > 50 ? item.product_name.substring(0, 47) + '...' : item.product_name;
                
                html += `
                    <div class="ranking-item" onclick="showProductDetail('${item.product_id}', '${escapeHtml(item.product_name)}')">
                        <div class="ranking-number ${rankClass}">${rank}</div>
                        <div class="ranking-info">
                            <div class="ranking-name"><strong>${escapeHtml(productName)}</strong> ${item.shop_name ? `<span style="color:#9aaebe; font-size:10px;">🏪 ${escapeHtml(item.shop_name)}</span>` : ''}</div>
                            <div class="ranking-stats">
                                <span>💰 GMV: <span class="ranking-value">${formatCurrency(item.gmv_display)}</span></span>
                                <span>💸 Open Plan: ${(item.open_commission / 100).toFixed(2)}%</span>
                            </div>
                            ${item.gmv_range ? `<div style="font-size: 9px; color: var(--text-muted);">📊 Range: ${item.gmv_range}</div>` : ''}
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="empty-state"><i class="fas fa-box-open"></i><p>No products data available</p></div>';
        }
    } catch (error) {
        console.error('Error loading products:', error);
        container.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading data</p></div>';
    }
}

// ========== LOAD BESTSELLING CREATORS ==========
async function loadBestsellingCreators() {
    const container = document.getElementById('creatorsList');
    container.innerHTML = '<div class="loading-state"><i class="fas fa-spinner fa-pulse fa-2x"></i><p>Loading creators...</p></div>';
    
    const timeSlot = document.getElementById('timeSlot').value;
    let startDate = document.getElementById('startDate')?.value;
    let endDate = document.getElementById('endDate')?.value;
    const latestAvailableDate = '2026-05-17';
    
    const formData = new URLSearchParams();
    formData.append('time_slot', timeSlot);
    if (timeSlot === 'CUSTOM' && startDate && endDate) {
        if (startDate > latestAvailableDate) startDate = latestAvailableDate;
        if (endDate > latestAvailableDate) endDate = latestAvailableDate;
        formData.append('start_date', startDate);
        formData.append('end_date', endDate);
    }
    formData.append('limit', 30);
    formData.append('author_type', 'ALL');
    
    try {
        const response = await fetch(baseUrl + 'analytics/get_bestselling_creators', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData
        });
        const data = await response.json();
        
        if (data.success && data.data && data.data.length > 0) {
            let html = '';
            data.data.forEach((item, index) => {
                const rank = index + 1;
                let rankClass = rank === 1 ? 'top-1' : (rank === 2 ? 'top-2' : (rank === 3 ? 'top-3' : ''));
                const initial = item.creator_name ? item.creator_name.charAt(0).toUpperCase() : '?';
                
                html += `
                    <div class="ranking-item" onclick="showCreatorDetail('${escapeHtml(item.username)}', '${escapeHtml(item.creator_name)}', '${escapeHtml(item.open_id)}')">
                        <div class="creator-avatar" style="background: linear-gradient(135deg, #8b5cf6, #4ade80); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <span style="font-size: 18px; font-weight: bold; color: white;">${initial}</span>
                        </div>
                        <div class="ranking-number ${rankClass}">${rank}</div>
                        <div class="ranking-info">
                            <div class="ranking-name"><strong>${escapeHtml(item.creator_name)}</strong> <span style="color:#9aaebe; font-size:10px;">@${escapeHtml(item.username)}</span></div>
                            <div class="ranking-stats">
                                <span>👥 Followers: ${formatNumber(item.followers)}</span>
                                <span>❤️ Likes: ${formatNumber(item.likes)}</span>
                                <span>💰 GMV: ${formatCurrency(item.gmv_display)}</span>
                            </div>
                            ${item.gmv_range ? `<div style="font-size: 9px;">📊 GMV Range: ${item.gmv_range}</div>` : ''}
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="empty-state"><i class="fas fa-user-friends"></i><p>No creators data available</p></div>';
        }
    } catch (error) {
        console.error('Error loading creators:', error);
        container.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading data</p></div>';
    }
}

// ========== LOAD BESTSELLING VIDEOS ==========
async function loadBestsellingVideos() {
    const container = document.getElementById('videosList');
    container.innerHTML = '<div class="loading-state"><i class="fas fa-spinner fa-pulse fa-2x"></i><p>Loading videos...</p></div>';
    
    const timeSlot = document.getElementById('timeSlot').value;
    let startDate = document.getElementById('startDate')?.value;
    let endDate = document.getElementById('endDate')?.value;
    const latestAvailableDate = '2026-05-17';
    
    const formData = new URLSearchParams();
    formData.append('time_slot', timeSlot);
    if (timeSlot === 'CUSTOM' && startDate && endDate) {
        if (startDate > latestAvailableDate) startDate = latestAvailableDate;
        if (endDate > latestAvailableDate) endDate = latestAvailableDate;
        formData.append('start_date', startDate);
        formData.append('end_date', endDate);
    }
    formData.append('limit', 30);
    
    try {
        const response = await fetch(baseUrl + 'analytics/get_bestselling_videos', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData
        });
        const data = await response.json();
        
        if (data.success && data.data && data.data.length > 0) {
            let html = '';
            data.data.forEach((item, index) => {
                const rank = index + 1;
                let rankClass = rank === 1 ? 'top-1' : (rank === 2 ? 'top-2' : (rank === 3 ? 'top-3' : ''));
                
                html += `
                    <div class="ranking-item" onclick="showVideoDetail('${item.video_id}', '${escapeHtml(item.creator_name)}')">
                        <div class="ranking-number ${rankClass}">${rank}</div>
                        <div class="ranking-info">
                            <div class="ranking-name"><strong>${escapeHtml(item.creator_name)}</strong> ${item.shop_name ? `<span class="brand-badge">🏪 ${escapeHtml(item.shop_name)}</span>` : ''}</div>
                            <div class="ranking-stats">
                                <span>👁️ Views: ${formatNumber(item.views)}</span>
                                <span>❤️ Likes: ${formatNumber(item.likes)}</span>
                                <span>💰 GMV: ${formatCurrency(item.gmv_display)}</span>
                            </div>
                            <div class="ranking-stats">
                                <span>💬 Comments: ${formatNumber(item.comments)}</span>
                                <span>🔄 Shares: ${formatNumber(item.shares)}</span>
                                <span>📅 Published: ${item.publish_time || '-'}</span>
                            </div>
                            <div style="font-size: 10px; margin-top: 4px; color: #4ade80;">
                                <i class="fab fa-tiktok"></i> Click to watch
                            </div>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="empty-state"><i class="fas fa-video-slash"></i><p>No videos data available</p></div>';
        }
    } catch (error) {
        console.error('Error loading videos:', error);
        container.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading data</p></div>';
    }
}

// ========== SHOW PRODUCT DETAIL (DRILL-DOWN) ==========
async function showProductDetail(productId, productName) {
    if (!productId) return;
    
    const modal = document.getElementById('productModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    
    modalTitle.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Loading...';
    modalBody.innerHTML = '<div class="loading-state"><i class="fas fa-spinner fa-pulse fa-2x"></i><p>Loading product details...</p></div>';
    modal.classList.add('active');
    
    const formData = new URLSearchParams();
    formData.append('product_id', productId);
    formData.append('product_name', productName);
    
    try {
        const response = await fetch(baseUrl + 'analytics/get_product_detail', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData
        });
        const data = await response.json();
        
        console.log('Product detail response:', data);
        
        if (data.success) {
            modalTitle.innerHTML = `<i class="fas fa-box"></i> Product: ${escapeHtml(productName.substring(0, 50))}${productName.length > 50 ? '...' : ''}`;
            
            // ========== RELATED CREATORS ==========
            let creatorsHtml = '';
            if (data.creators && data.creators.length > 0) {
                creatorsHtml = '<div style="max-height: 400px; overflow-y: auto;">';
                data.creators.forEach(creator => {
                    const initial = creator.creator_name ? creator.creator_name.charAt(0).toUpperCase() : '?';
                    creatorsHtml += `
                        <div class="sub-item" onclick="showCreatorDetail('', '${escapeHtml(creator.creator_name)}', '${escapeHtml(creator.open_id)}')" style="cursor: pointer; padding: 12px; border-bottom: 1px solid #2a3346;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div class="creator-avatar" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #8b5cf6, #4ade80); display: flex; align-items: center; justify-content: center;">
                                    <span style="font-size: 18px; font-weight: bold; color: white;">${initial}</span>
                                </div>
                                <div style="flex: 1;">
                                    <div><strong>${escapeHtml(creator.creator_name)}</strong></div>
                                    <div style="font-size: 11px; margin-top: 4px;">
                                        <span>💰 ${formatCurrency(creator.gmv)}</span>
                                        <span style="margin-left: 12px;">📦 Sold: ${formatNumber(creator.items_sold)}</span>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right" style="color: #4ade80;"></i>
                            </div>
                        </div>
                    `;
                });
                creatorsHtml += '</div>';
            } else {
                creatorsHtml = '<div class="empty-state"><i class="fas fa-user-friends"></i><p>No creators found for this product</p></div>';
            }
            
            // ========== RELATED VIDEOS ==========
            let videosHtml = '';
            if (data.videos && data.videos.length > 0) {
                videosHtml = '<div style="max-height: 400px; overflow-y: auto;">';
                data.videos.forEach(video => {
                    videosHtml += `
                        <div class="sub-item" onclick="showVideoDetail('${video.id}', '${escapeHtml(video.nick_name)}')" style="cursor: pointer; padding: 12px; border-bottom: 1px solid #2a3346;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 50px; height: 70px; border-radius: 8px; background: linear-gradient(135deg, #1a1030, #13111f); display: flex; align-items: center; justify-content: center; position: relative;">
                                    <i class="fab fa-tiktok" style="font-size: 24px; color: #4ade80;"></i>
                                    <div style="position: absolute; bottom: 4px; right: 4px; background: rgba(0,0,0,0.7); border-radius: 4px; padding: 2px 4px; font-size: 8px;">${video.duration || 0}s</div>
                                </div>
                                <div style="flex: 1;">
                                    <div><strong>${escapeHtml(video.nick_name)}</strong></div>
                                    <div style="font-size: 10px; margin-top: 4px;">
                                        <span>👁️ ${formatNumber(video.views)}</span>
                                        <span style="margin-left: 12px;">❤️ ${formatNumber(video.likes)}</span>
                                        <span style="margin-left: 12px;">💰 ${formatCurrency(video.gmv_display)}</span>
                                    </div>
                                    <div style="font-size: 10px; margin-top: 4px; color: #4ade80;">
                                        <i class="fab fa-tiktok"></i> Click to watch
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right" style="color: #4ade80;"></i>
                            </div>
                        </div>
                    `;
                });
                videosHtml += '</div>';
            } else {
                videosHtml = '<div class="empty-state"><i class="fas fa-video-slash"></i><p>No videos found for this product</p></div>';
            }
            
            modalBody.innerHTML = `
                <div class="sub-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="sub-section" style="background: #0f1420; border-radius: 16px; padding: 16px;">
                        <h4 style="margin: 0 0 12px 0; color: #8b5cf6;"><i class="fas fa-users"></i> Related Creators (${data.total_creators || 0})</h4>
                        ${creatorsHtml}
                    </div>
                    <div class="sub-section" style="background: #0f1420; border-radius: 16px; padding: 16px;">
                        <h4 style="margin: 0 0 12px 0; color: #8b5cf6;"><i class="fas fa-video"></i> Related Videos (${data.total_videos || 0})</h4>
                        ${videosHtml}
                    </div>
                </div>
            `;
        } else {
            modalBody.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>' + (data.message || 'Failed to load product details') + '</p></div>';
        }
    } catch (error) {
        console.error('Error loading product detail:', error);
        modalBody.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading data</p></div>';
    }
}

// ========== SHOW CREATOR DETAIL ==========
async function showCreatorDetail(username, creatorName, openId) {
    console.log('showCreatorDetail called:', {username, creatorName, openId});
    
    if (!username && !creatorName && !openId) {
        console.error('No creator data provided');
        return;
    }
    
    const modal = document.getElementById('productModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    
    modalTitle.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Loading...';
    modalBody.innerHTML = '<div class="loading-state"><i class="fas fa-spinner fa-pulse fa-2x"></i><p>Loading creator details...</p></div>';
    modal.classList.add('active');
    
    const formData = new URLSearchParams();
    // Prioritaskan open_id jika ada
    if (openId && openId !== 'null' && openId !== 'undefined') {
        formData.append('open_id', openId);
    }
    if (creatorName && creatorName !== 'null' && creatorName !== 'undefined') {
        formData.append('creator_name', creatorName);
    }
    if (username && username !== 'null' && username !== 'undefined') {
        formData.append('creator_username', username);
    }
    
    console.log('Sending formData:', [...formData.entries()]);
    
    try {
        const response = await fetch(baseUrl + 'analytics/get_creator_detail', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData
        });
        const data = await response.json();
        
        console.log('Creator detail response:', data);
        
        if (data.success) {
            let creatorDisplayName = data.creator_detail?.nickname || creatorName || username;
            modalTitle.innerHTML = `<i class="fab fa-tiktok"></i> Creator: ${escapeHtml(creatorDisplayName)}`;
            
            // ========== CREATOR PROFILE ==========
            let profileHtml = '';
            if (data.creator_detail) {
                const avatarUrl = data.creator_detail.avatar_url;
                const avatarHtml = (avatarUrl && avatarUrl !== '') ? 
                    `<img src="${escapeHtml(avatarUrl)}" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid #4ade80;" onerror="this.style.display='none'">` : 
                    `<div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #8b5cf6, #4ade80); display: flex; align-items: center; justify-content: center;">
                        <i class="fab fa-tiktok" style="font-size: 40px; color: white;"></i>
                    </div>`;
                
                profileHtml = `
                    <div style="display: flex; gap: 20px; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #2a3346; flex-wrap: wrap;">
                        ${avatarHtml}
                        <div>
                            <h3 style="margin: 0 0 5px 0; color: #e2f0e8;">${escapeHtml(data.creator_detail.nickname)}</h3>
                            <div style="color: #9aaebe;">@${escapeHtml(data.creator_detail.username)}</div>
                            <div style="margin-top: 8px; display: flex; gap: 16px; flex-wrap: wrap;">
                                <span>👥 ${formatNumber(data.creator_detail.followers)} followers</span>
                                <span>💰 GMV: ${formatCurrency(data.creator_detail.gmv)}</span>
                                <span>📦 Products: ${formatNumber(data.creator_detail.promoted_product_num || 0)}</span>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                const initial = creatorName ? creatorName.charAt(0).toUpperCase() : (username ? username.charAt(0).toUpperCase() : '?');
                profileHtml = `
                    <div style="display: flex; gap: 20px; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #2a3346;">
                        <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #8b5cf6, #4ade80); display: flex; align-items: center; justify-content: center;">
                            <span style="font-size: 40px; font-weight: bold; color: white;">${initial}</span>
                        </div>
                        <div>
                            <h3 style="margin: 0 0 5px 0;">${escapeHtml(creatorName || username)}</h3>
                            <div style="color: #9aaebe;">@${escapeHtml(username)}</div>
                        </div>
                    </div>
                `;
            }
            
            // ========== PRODUCTS SECTION ==========
            let productsHtml = '';
            if (data.products && data.products.length > 0) {
                productsHtml = '<div style="max-height: 400px; overflow-y: auto;">';
                data.products.forEach(product => {
                    let productName = product.product_name;
                    if (productName.length > 50) {
                        productName = productName.substring(0, 47) + '...';
                    }
                    
                    productsHtml += `
                        <div class="sub-item" onclick="showProductDetail('${product.product_id}', '${escapeHtml(product.product_name)}')" style="cursor: pointer; padding: 12px; border-bottom: 1px solid #2a3346;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 40px; height: 40px; border-radius: 8px; background: #1e293b; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-box" style="color: #4ade80;"></i>
                                </div>
                                <div style="flex: 1;">
                                    <div><strong>${escapeHtml(productName)}</strong></div>
                                    ${product.shop_name ? `<div style="font-size: 10px; color: #9aaebe;">🏪 ${escapeHtml(product.shop_name)}</div>` : ''}
                                    <div style="margin-top: 4px; font-size: 10px;">💰 ${formatCurrency(product.gmv)}</div>
                                </div>
                                <i class="fas fa-chevron-right" style="color: #4ade80;"></i>
                            </div>
                        </div>
                    `;
                });
                productsHtml += '</div>';
            } else {
                productsHtml = '<div class="empty-state"><i class="fas fa-box-open"></i><p>No products found for this creator</p></div>';
            }
            
            // ========== VIDEOS SECTION ==========
            let videosHtml = '';
            if (data.videos && data.videos.length > 0) {
                videosHtml = '<div style="max-height: 400px; overflow-y: auto;">';
                data.videos.forEach(video => {
                    videosHtml += `
                        <div class="sub-item" onclick="showVideoDetail('${video.id}', '${escapeHtml(video.nick_name)}')" style="cursor: pointer; padding: 12px; border-bottom: 1px solid #2a3346;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 50px; height: 70px; border-radius: 8px; background: linear-gradient(135deg, #1a1030, #13111f); display: flex; align-items: center; justify-content: center; position: relative;">
                                    <i class="fab fa-tiktok" style="font-size: 24px; color: #4ade80;"></i>
                                    <div style="position: absolute; bottom: 4px; right: 4px; background: rgba(0,0,0,0.7); border-radius: 4px; padding: 2px 4px; font-size: 8px;">${video.duration || 0}s</div>
                                </div>
                                <div style="flex: 1;">
                                    <div><strong>${escapeHtml(video.nick_name)}</strong></div>
                                    <div style="font-size: 10px; margin-top: 4px;">
                                        <span>👁️ ${formatNumber(video.views)}</span>
                                        <span>❤️ ${formatNumber(video.likes)}</span>
                                        <span>💰 ${formatCurrency(video.gmv_display)}</span>
                                    </div>
                                    <div style="font-size: 10px; margin-top: 4px; color: #4ade80;">
                                        <i class="fab fa-tiktok"></i> Click to watch
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right" style="color: #4ade80;"></i>
                            </div>
                        </div>
                    `;
                });
                videosHtml += '</div>';
            } else {
                videosHtml = '<div class="empty-state"><i class="fas fa-video-slash"></i><p>No videos found for this creator</p></div>';
            }
            
            modalBody.innerHTML = `
                ${profileHtml}
                <div class="sub-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                    <div class="sub-section" style="background: #0f1420; border-radius: 16px; padding: 16px;">
                        <h4 style="margin: 0 0 12px 0; color: #8b5cf6;"><i class="fas fa-box"></i> Products (${data.total_products || 0})</h4>
                        ${productsHtml}
                    </div>
                    <div class="sub-section" style="background: #0f1420; border-radius: 16px; padding: 16px;">
                        <h4 style="margin: 0 0 12px 0; color: #8b5cf6;"><i class="fas fa-video"></i> Videos (${data.total_videos || 0})</h4>
                        ${videosHtml}
                    </div>
                </div>
            `;
        } else {
            modalBody.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>' + (data.message || 'Failed to load creator details') + '</p></div>';
        }
    } catch (error) {
        console.error('Error loading creator detail:', error);
        modalBody.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading data: ' + error.message + '</p></div>';
    }
}

// ========== SHOW VIDEO DETAIL ==========
async function showVideoDetail(videoId, creatorName) {
    if (!videoId && !creatorName) return;
    
    const modal = document.getElementById('productModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    
    modalTitle.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Loading...';
    modalBody.innerHTML = '<div class="loading-state"><i class="fas fa-spinner fa-pulse fa-2x"></i><p>Loading video details...</p></div>';
    modal.classList.add('active');
    
    const formData = new URLSearchParams();
    if (videoId && videoId !== 'null' && videoId !== 'undefined') {
        formData.append('video_id', videoId);
    }
    if (creatorName && creatorName !== 'null' && creatorName !== 'undefined') {
        formData.append('creator_name', creatorName);
    }
    
    try {
        const response = await fetch(baseUrl + 'analytics/get_video_detail', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData
        });
        const data = await response.json();
        
        console.log('Video detail response:', data);
        
        if (data.success) {
            modalTitle.innerHTML = `<i class="fas fa-video"></i> Video by ${escapeHtml(creatorName || 'Unknown')}`;
            
            // ========== VIDEO INFO ==========
            let videoInfoHtml = '';
            if (data.video_detail) {
                const video = data.video_detail;
                videoInfoHtml = `
                    <div style="background: rgba(74,222,128,0.1); border-radius: 14px; padding: 16px; margin-bottom: 20px;">
                        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                            <div><strong>👁️ Views:</strong> ${formatNumber(video.views)}</div>
                            <div><strong>❤️ Likes:</strong> ${formatNumber(video.likes)}</div>
                            <div><strong>💬 Comments:</strong> ${formatNumber(video.comments)}</div>
                            <div><strong>🔄 Shares:</strong> ${formatNumber(video.shares)}</div>
                            <div><strong>⏱️ Duration:</strong> ${video.duration}s</div>
                        </div>
                        ${video.shop_name ? `<div style="margin-top: 8px;"><strong>🏪 Brand:</strong> ${escapeHtml(video.shop_name)}</div>` : ''}
                        <div style="margin-top: 12px;">
                            <a href="${video.video_url}" target="_blank" class="video-link" style="color: #4ade80;">
                                <i class="fab fa-tiktok"></i> Watch on TikTok
                            </a>
                        </div>
                    </div>
                `;
            }
            
            // ========== PRODUCTS IN VIDEO ==========
            let productsHtml = '';
            if (data.products && data.products.length > 0) {
                productsHtml = '<div style="max-height: 500px; overflow-y: auto;">';
                data.products.forEach(product => {
                    let productName = product.product_name;
                    if (productName.length > 50) {
                        productName = productName.substring(0, 47) + '...';
                    }
                    
                    productsHtml += `
                        <div class="sub-item" onclick="showProductDetail('${product.product_id}', '${escapeHtml(product.product_name)}')" style="cursor: pointer; padding: 12px; border-bottom: 1px solid #2a3346;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 40px; height: 40px; border-radius: 8px; background: #1e293b; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-box" style="color: #4ade80;"></i>
                                </div>
                                <div style="flex: 1;">
                                    <div><strong>${escapeHtml(productName)}</strong></div>
                                    ${product.shop_name ? `<div style="font-size: 10px; color: #9aaebe;">🏪 ${escapeHtml(product.shop_name)}</div>` : ''}
                                    <div style="margin-top: 4px; font-size: 10px;">💰 ${formatCurrency(product.gmv_display)}</div>
                                </div>
                                <i class="fas fa-chevron-right" style="color: #4ade80;"></i>
                            </div>
                        </div>
                    `;
                });
                productsHtml += '</div>';
            } else {
                productsHtml = '<div class="empty-state"><i class="fas fa-box-open"></i><p>No products found in this video</p></div>';
            }
            
            modalBody.innerHTML = `
                ${videoInfoHtml}
                <div class="sub-section" style="background: #0f1420; border-radius: 16px; padding: 16px;">
                    <h4 style="margin: 0 0 12px 0; color: #8b5cf6;"><i class="fas fa-box"></i> Products in this Video (${data.total_products || 0})</h4>
                    ${productsHtml}
                </div>
            `;
        } else {
            modalBody.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>' + (data.message || 'Failed to load video details') + '</p></div>';
        }
    } catch (error) {
        console.error('Error loading video detail:', error);
        modalBody.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading data: ' + error.message + '</p></div>';
    }
}

// ========== LOAD CATEGORIES ==========
async function loadCategories() {
    try {
        const response = await fetch(baseUrl + 'analytics/get_categories');
        const data = await response.json();
        
        const categorySelect = document.getElementById('categoryFilter');
        if (!categorySelect) return;
        
        while (categorySelect.options.length > 1) {
            categorySelect.remove(1);
        }
        
        if (data.success && data.data && data.data.length > 0) {
            data.data.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat.name;
                option.textContent = cat.name;
                categorySelect.appendChild(option);
            });
            console.log('Categories loaded from API:', data.data.length);
        } else {
            const defaultCats = ['Beauty', 'Fashion', 'Electronics', 'Home & Living', 'Food & Beverage', 'Sports', 'Toys & Hobbies'];
            defaultCats.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat;
                option.textContent = cat;
                categorySelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading categories:', error);
        const defaultCats = ['Beauty', 'Fashion', 'Electronics', 'Home & Living', 'Food & Beverage', 'Sports', 'Toys & Hobbies'];
        const categorySelect = document.getElementById('categoryFilter');
        if (categorySelect) {
            defaultCats.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat;
                option.textContent = cat;
                categorySelect.appendChild(option);
            });
        }
    }
}

// ========== MODAL CLOSE ==========
document.getElementById('closeModal')?.addEventListener('click', () => {
    document.getElementById('productModal').classList.remove('active');
});

document.getElementById('productModal')?.addEventListener('click', (e) => {
    if (e.target === document.getElementById('productModal')) {
        document.getElementById('productModal').classList.remove('active');
    }
});

// Close modal with ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('productModal');
        if (modal && modal.classList.contains('active')) {
            modal.classList.remove('active');
        }
    }
});

// ========== INITIAL LOAD ==========
document.addEventListener('DOMContentLoaded', () => {
    const today = new Date();
    const weekAgo = new Date(today);
    weekAgo.setDate(today.getDate() - 7);
    document.getElementById('startDate').value = weekAgo.toISOString().split('T')[0];
    document.getElementById('endDate').value = today.toISOString().split('T')[0];
    
    loadCategories().then(() => {
        loadAllData();
    });
});
</script>