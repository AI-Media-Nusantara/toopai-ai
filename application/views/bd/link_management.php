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
    
    .btn-create {
        background: linear-gradient(135deg, var(--purple), var(--blue));
        border: none;
        padding: 10px 20px;
        border-radius: 40px;
        color: white;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        transition: var(--transition);
    }
    
    .btn-create:hover {
        transform: translateY(-2px);
        box-shadow: var(--glow-purple);
    }
    
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
    }
    
    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: #4ade80;
    }
    
    /* Campaign Tabs */
    .campaign-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 24px;
        border-bottom: 1px solid var(--border);
        padding-bottom: 12px;
        overflow-x: auto;
    }
    
    .campaign-tab {
        background: transparent;
        border: none;
        padding: 10px 20px;
        border-radius: 40px;
        color: var(--text-muted);
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.2s;
        white-space: nowrap;
    }
    
    .campaign-tab:hover {
        background: rgba(74, 222, 128, 0.1);
        color: #4ade80;
    }
    
    .campaign-tab.active {
        background: linear-gradient(135deg, #4ade80, #22c55e);
        color: #0a0e17;
    }
    
    /* Search Section */
    .search-section {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
        flex-wrap: wrap;
        align-items: center;
    }
    
    .search-type {
        display: flex;
        gap: 8px;
        background: var(--bg-elevated);
        border-radius: 40px;
        padding: 4px;
    }
    
    .search-type-btn {
        background: transparent;
        border: none;
        padding: 8px 16px;
        border-radius: 40px;
        color: var(--text-muted);
        cursor: pointer;
        font-size: 12px;
        transition: all 0.2s;
    }
    
    .search-type-btn.active {
        background: linear-gradient(135deg, var(--purple), var(--blue));
        color: white;
    }
    
    .search-input-group {
        flex: 1;
        display: flex;
        gap: 10px;
        min-width: 250px;
    }
    
    .search-input-group input {
        flex: 1;
        padding: 10px 16px;
        background: var(--bg-elevated);
        border: 1px solid var(--border);
        border-radius: 40px;
        color: var(--text-primary);
        font-size: 13px;
    }
    
    .search-input-group button {
        background: linear-gradient(135deg, var(--purple), var(--blue));
        border: none;
        padding: 8px 20px;
        border-radius: 40px;
        color: white;
        cursor: pointer;
    }
    
    /* Table */
    .links-table {
        width: 100%;
        border-collapse: collapse;
        background: var(--bg-card);
        border-radius: 20px;
        overflow: hidden;
    }
    
    .links-table th,
    .links-table td {
        padding: 14px 12px;
        text-align: left;
        border-bottom: 1px solid var(--border);
        font-size: 12px;
    }
    
    .links-table th {
        background: var(--bg-elevated);
        color: var(--purple);
        font-weight: 600;
    }
    
    .links-table tr:hover {
        background: var(--bg-elevated);
    }
    
    /* Product Cell with Image */
    .product-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .product-img {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        overflow: hidden;
        background: #1e293b;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .product-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .product-img i {
        font-size: 20px;
        color: var(--text-muted);
    }
    
    .product-info {
        flex: 1;
    }
    
    .product-name {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 13px;
        margin-bottom: 4px;
    }
    
    .product-shop {
        font-size: 11px;
        color: #4ade80;
    }
    
    .badge-active {
        background: rgba(16, 185, 129, 0.15);
        color: #10b981;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 10px;
    }
    
    .badge-inactive {
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 10px;
    }
    
    .btn-icon {
        background: transparent;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 8px;
        transition: var(--transition);
    }
    
    .btn-icon:hover {
        background: var(--purple-glow);
        color: var(--purple);
    }
    
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
        max-width: 600px;
        border-radius: 20px;
        border: 1px solid var(--border);
        max-height: 85vh;
        overflow-y: auto;
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
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
    
    .modal-body {
        padding: 20px;
    }
    
    .form-group {
        margin-bottom: 16px;
    }
    
    .form-group label {
        display: block;
        color: var(--text-secondary);
        font-size: 12px;
        margin-bottom: 6px;
    }
    
    .form-control {
        width: 100%;
        padding: 10px 12px;
        background: var(--bg-elevated);
        border: 1px solid var(--border);
        border-radius: 12px;
        color: var(--text-primary);
        font-size: 13px;
    }
    
    .search-result {
        max-height: 400px;
        overflow-y: auto;
        background: var(--bg-elevated);
        border-radius: 12px;
        margin-top: 8px;
        border: 1px solid var(--border);
    }
    
    .search-result-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        border-bottom: 1px solid var(--border);
        cursor: pointer;
        transition: var(--transition);
    }
    
    .search-result-item:hover {
        background: var(--purple-glow);
    }
    
    .search-result-img {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        overflow: hidden;
        background: #1e293b;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .search-result-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .search-result-img i {
        font-size: 24px;
        color: var(--text-muted);
    }
    
    .search-result-info {
        flex: 1;
    }
    
    .search-result-name {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 13px;
        margin-bottom: 4px;
    }
    
    .search-result-details {
        display: flex;
        gap: 12px;
        font-size: 11px;
        color: var(--text-muted);
        flex-wrap: wrap;
    }
    
    .search-result-price {
        color: #4ade80;
        font-weight: 600;
    }
    
    .search-result-commission {
        color: #fbbf24;
    }
    
    .link-preview {
        background: #0a0e1a;
        padding: 12px;
        border-radius: 12px;
        margin-top: 12px;
        word-break: break-all;
    }
    
    .copy-link {
        background: #1e293b;
        border: none;
        color: #4ade80;
        padding: 4px 12px;
        border-radius: 20px;
        cursor: pointer;
        font-size: 11px;
    }
    
    .campaign-badge {
        background: rgba(139, 92, 246, 0.15);
        color: #a78bfa;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 10px;
        display: inline-block;
    }
    
    .text-center {
        text-align: center;
    }
    
    @media (max-width: 768px) {
        .links-table {
            font-size: 10px;
        }
        .links-table th,
        .links-table td {
            padding: 8px 6px;
        }
        .product-img {
            width: 36px;
            height: 36px;
        }
    }
</style>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-link"></i> Link Management</h1>
        <p class="page-subtitle">Manage all affiliate links generated by BD</p>
    </div>
    <button class="btn-create" id="createLinkBtn">
        <i class="fas fa-plus"></i> Create New Link
    </button>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?= number_format($total_links) ?></div>
        <div class="stat-label">Total Links</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">Rp <?= number_format($total_gmv, 0, ',', '.') ?></div>
        <div class="stat-label">Total GMV</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= number_format($total_orders) ?></div>
        <div class="stat-label">Total Orders</div>
    </div>
</div>

<!-- Campaign Tabs -->
<div class="campaign-tabs" id="campaignTabs">
    <button class="campaign-tab <?= ($campaign_filter === 'all') ? 'active' : '' ?>" data-campaign-id="all">All Campaigns</button>
    <?php foreach ($campaigns as $camp): ?>
    <button class="campaign-tab <?= ($campaign_filter === $camp->campaign_id) ? 'active' : '' ?>" data-campaign-id="<?= $camp->campaign_id ?>" data-campaign-name="<?= htmlspecialchars($camp->campaign_name) ?>">
        <?= htmlspecialchars(substr($camp->campaign_name, 0, 30)) ?>
    </button>
    <?php endforeach; ?>
</div>

<!-- Search Section -->
<div class="search-section">
    <div class="search-type" id="searchType">
        <button class="search-type-btn <?= ($search_type === 'shop') ? 'active' : '' ?>" data-type="shop">Shop Name</button>
        <button class="search-type-btn <?= ($search_type === 'product_id') ? 'active' : '' ?>" data-type="product_id">Product ID</button>
    </div>
    <div class="search-input-group">
        <input type="text" id="searchInput" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="<?= ($search_type === 'shop') ? 'Search by shop name...' : 'Search by product ID...' ?>">
        <button id="searchBtn"><i class="fas fa-search"></i> Search</button>
    </div>
</div>

<div style="overflow-x: auto;">
    <table class="links-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Shop Name</th>
                <th>Campaign</th>
                <th>Commission</th>
                <th>Link</th>
                <th>Orders</th>
                <th>GMV</th>
                <th>Status</th>
                <th>Created</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="linksTableBody">
            <?php if (!empty($links)): ?>
                <?php foreach ($links as $link): ?>
                <tr data-campaign-id="<?= $link->campaign_id ?>" data-shop-name="<?= htmlspecialchars($link->shop_name ?? '') ?>">
                   <td>
    <div class="product-cell">
        <div class="product-img">
            <?php if (!empty($link->image_url)): ?>
                <img src="<?= $link->image_url ?>" alt="<?= htmlspecialchars($link->product_name ?? '') ?>" onerror="this.src=''; this.onerror=null; this.parentElement.innerHTML='<i class=\'fas fa-box\'></i>'">
            <?php else: ?>
                <i class="fas fa-box"></i>
            <?php endif; ?>
        </div>
        <div class="product-info">
            <div class="product-name"><?= htmlspecialchars($link->product_name ?? substr($link->product_id, 0, 30)) ?></div>
            <div class="product-id" style="font-size: 10px; color: #6b7280; margin-bottom: 2px;">
                Product ID: <?= htmlspecialchars($link->product_id) ?>
            </div>
            <div class="product-shop">
                <i class="fas fa-store"></i> <?= htmlspecialchars($link->shop_name ?? '-') ?>
            </div>
        </div>
    </div>
</td>
                    <td><strong><?= htmlspecialchars($link->shop_name ?? '-') ?></strong></td>
                    <td><span class="campaign-badge"><?= htmlspecialchars($link->campaign_name ?? $link->campaign_id) ?></span></td>
                   <td>
    <?= $link->commission_rate ?>%
    <?php if ($link->special_case ?? 0): ?>
        <span class="badge-special" style="background: #fbbf24; color: #0a0e17; padding: 2px 6px; border-radius: 10px; font-size: 9px; margin-left: 5px;">
            <i class="fas fa-star"></i> Special
        </span>
    <?php endif; ?>
</td>
                    <td>
                        <a href="<?= $link->affiliate_link ?>" target="_blank" style="color: #4ade80; text-decoration: none;">
                            <?= substr($link->affiliate_link, 0, 40) ?>...
                        </a>
                        <button class="copy-link" data-link="<?= $link->affiliate_link ?>" style="margin-left: 5px;">Copy</button>
                    </td>
                    <td class="gmv-cell"><?= number_format($link->total_orders ?? 0) ?></td>
                    <td class="gmv-cell">Rp <?= number_format($link->total_gmv ?? 0, 0, ',', '.') ?></td>
                    <td>
                        <span class="badge-<?= strtolower($link->status) ?>">
                            <?= $link->status ?>
                        </span>
                    </td>
                    <td><?= date('d M Y', strtotime($link->created_at)) ?></td>
                    <td>
                        <button class="btn-icon edit-link" data-id="<?= $link->id ?>" data-commission="<?= $link->commission_rate ?>" data-status="<?= $link->status ?>" data-notes="<?= htmlspecialchars($link->notes ?? '') ?>">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-icon delete-link" data-id="<?= $link->id ?>">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                        <button class="btn-icon stats-link" data-id="<?= $link->id ?>">
                            <i class="fas fa-chart-line"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" class="text-center" style="padding: 40px;">Belum ada link. Klik "Create New Link" untuk membuat link afiliasi.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Custom Pagination Styling & Markup -->
<style>
.pagination {
    display: flex;
    padding-left: 0;
    list-style: none;
    border-radius: 8px;
    gap: 5px;
}
.pagination li a {
    display: block;
    padding: 8px 16px;
    color: #9aaebe;
    background-color: #0f1420;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 8px;
    text-decoration: none;
    font-size: 13px;
    transition: all 0.2s ease;
}
.pagination li a:hover {
    color: #4ade80;
    border-color: #4ade80;
    background-color: rgba(74, 222, 128, 0.05);
}
.pagination li.active a {
    color: #0c101b !important;
    background-color: #4ade80 !important;
    border-color: #4ade80 !important;
    font-weight: 600;
}
.pagination li.disabled a {
    color: #4b5563;
    pointer-events: none;
    background-color: transparent;
    border-color: rgba(255,255,255,0.06);
}
</style>
<div class="pagination-wrapper" style="margin-top: 25px; display: flex; justify-content: center; width: 100%;">
    <?= $pagination_links ?>
</div>

<!-- Modal Create Link -->
<div id="createLinkModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-plus"></i> Create New Affiliate Link</h3>
            <span class="close" onclick="closeCreateModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="createLinkForm">
                <div class="form-group">
                    <label>Campaign *</label>
                    <select id="campaignId" class="form-control" required>
                        <option value="">-- Select Campaign --</option>
                        <?php foreach ($campaigns as $camp): ?>
                        <option value="<?= $camp->campaign_id ?>"><?= htmlspecialchars($camp->campaign_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Search By</label>
                    <div class="search-type" id="modalSearchType">
                        <button type="button" class="search-type-btn active" data-type="shop">Shop Name</button>
                        <button type="button" class="search-type-btn" data-type="product_id">Product ID</button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Cari Produk</label>
                    <input type="text" id="productSearch" class="form-control" placeholder="Ketik nama shop atau product ID...">
                    <div id="searchResults" class="search-result" style="display:none;"></div>
                </div>
                <div class="form-group" style="display:none;">
                    <label>Product ID</label>
                    <input type="text" id="productId" class="form-control" readonly>
                </div>
                <div class="form-group" style="display:none;">
                    <label>Product Name</label>
                    <input type="text" id="productName" class="form-control" readonly>
                </div>
                <div class="form-group">
    <label>Commission Rate (%) *</label>
    <input type="number" id="commissionRate" class="form-control" value="10" step="0.5" min="1" max="50" required>
    <small style="color: #9aaebe; font-size: 10px;">
        <i class="fas fa-info-circle"></i> 
        Rekomendasi: Open Plan Commission + 1% (bisa diubah manual)
    </small>
</div>
<div class="form-group">
    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
        <input type="checkbox" id="specialCase" value="1" style="width: 18px; height: 18px; cursor: pointer;">
        <span><i class="fas fa-star"></i> Special Case</span>
        <small style="color: #fbbf24; font-size: 10px; margin-left: 8px;">
            (Centang jika ini adalah link khusus untuk creator tertentu atau komisi spesial)
        </small>
    </label>
</div>
                <div class="form-group">
                    <label>Notes (Optional)</label>
                    <textarea id="notes" class="form-control" rows="2" placeholder="Add notes about this link..."></textarea>
                </div>
                <div id="selectedProductPreview" style="margin-top:12px;"></div>
                <div id="linkPreview" class="link-preview" style="display:none;">
                    <strong>Generated Link:</strong>
                    <div id="previewLink"></div>
                    <button type="button" class="copy-link" id="copyPreviewLink">Copy</button>
                </div>
                <button type="submit" class="btn-create" style="width:100%; margin-top:16px;" id="submitBtn">
                    <i class="fas fa-link"></i> Generate Link
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Link -->
<div id="editLinkModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Edit Link</h3>
            <span class="close" onclick="closeEditModal()">&times;</span>
        </div>
        <div class="modal-body">
            <input type="hidden" id="editLinkId">
            <div class="form-group">
                <label>Commission Rate (%)</label>
                <input type="number" id="editCommissionRate" class="form-control" step="0.5" min="1" max="50">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select id="editStatus" class="form-control">
                    <option value="ACTIVE">Active</option>
                    <option value="INACTIVE">Inactive</option>
                </select>
            </div>
            <div class="form-group">
                <label>Notes</label>
                <textarea id="editNotes" class="form-control" rows="2"></textarea>
            </div>
            <button id="updateLinkBtn" class="btn-create" style="width:100%;">
                <i class="fas fa-save"></i> Update Link
            </button>
        </div>
    </div>
</div>

<!-- Modal Stats -->
<div id="statsModal" class="modal">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h3><i class="fas fa-chart-line"></i> Link Statistics</h3>
            <span class="close" onclick="closeStatsModal()">&times;</span>
        </div>
        <div class="modal-body" id="statsContent">
            <div class="loading">Loading...</div>
        </div>
    </div>
</div>

<script>
const baseUrl = '<?= base_url() ?>';
let searchTimeout;
let selectedProducts = [];
let currentSelectedCampaignId = '<?= $campaign_filter ?>';
let currentSearchType = '<?= $search_type ?>';

// ========== CAMPAIGN TABS ==========
document.querySelectorAll('.campaign-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        const campaignId = tab.getAttribute('data-campaign-id');
        const url = new URL(window.location.href);
        url.searchParams.set('campaign_id', campaignId);
        url.searchParams.delete('page'); // Reset to page 0
        window.location.href = url.toString();
    });
});

// ========== SEARCH FUNCTION ==========
document.querySelectorAll('#searchType .search-type-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('#searchType .search-type-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentSearchType = btn.getAttribute('data-type');
        
        const placeholder = currentSearchType === 'shop' ? 'Search by shop name...' : 'Search by product ID...';
        document.getElementById('searchInput').placeholder = placeholder;
    });
});

document.getElementById('searchBtn')?.addEventListener('click', () => {
    performSearch();
});

document.getElementById('searchInput')?.addEventListener('keyup', (e) => {
    if (e.key === 'Enter') {
        performSearch();
    }
});

function performSearch() {
    const keyword = document.getElementById('searchInput').value.trim();
    const url = new URL(window.location.href);
    url.searchParams.set('search', keyword);
    url.searchParams.set('search_type', currentSearchType);
    url.searchParams.delete('page'); // Reset to page 0
    window.location.href = url.toString();
}




async function checkAndSetCommissionAccess() {
    try {
        const response = await fetch(baseUrl + 'link_management/can_generate_link');
        const data = await response.json();
        
        const commissionInput = document.getElementById('commissionRate');
        const submitBtn = document.getElementById('submitBtn');
        const generateBtn = document.getElementById('submitBtn');
        
        if (!data.can_generate) {
            // Non-Tiffany: tidak bisa generate link, hanya bisa lihat
            if (commissionInput) {
                commissionInput.disabled = true;
                commissionInput.readonly = true;
                commissionInput.style.backgroundColor = '#1e293b';
                commissionInput.style.color = '#9aaebe';
            }
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.5';
                submitBtn.style.cursor = 'not-allowed';
                submitBtn.title = 'Hanya Tiffany yang dapat generate link';
            }
            if (generateBtn) {
                generateBtn.disabled = true;
                generateBtn.style.opacity = '0.5';
                generateBtn.style.cursor = 'not-allowed';
            }
            
            // Tambahkan informasi warning jika belum ada
            if (!document.getElementById('accessWarning')) {
                const modalBody = document.querySelector('#createLinkModal .modal-body');
                if (modalBody) {
                    const infoDiv = document.createElement('div');
                    infoDiv.id = 'accessWarning';
                    infoDiv.style.cssText = 'background: rgba(239,68,68,0.15); border-radius: 12px; padding: 12px; margin-bottom: 16px; border-left: 3px solid #ef4444;';
                    infoDiv.innerHTML = `
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-lock" style="color: #fbbf24; font-size: 18px;"></i>
                            <div>
                                <div style="color: #fbbf24; font-size: 12px; font-weight: 500;">Akses Terbatas!</div>
                                <div style="color: #9aaebe; font-size: 11px; margin-top: 2px;">
                                    Hanya <strong>Tiffany</strong> yang dapat generate link afiliasi baru. Anda hanya dapat melihat dan menggunakan link yang sudah ada.
                                </div>
                            </div>
                        </div>
                    `;
                    // Taruh di awal modal body, setelah header
                    const firstChild = modalBody.firstChild;
                    modalBody.insertBefore(infoDiv, firstChild);
                }
            }
        } else {
            // Tiffany: bisa generate link
            if (commissionInput) {
                commissionInput.disabled = false;
                commissionInput.readonly = false;
                commissionInput.style.backgroundColor = '';
                commissionInput.style.color = '';
            }
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
                submitBtn.style.cursor = 'pointer';
            }
            
            // Remove warning if exists
            const warning = document.getElementById('accessWarning');
            if (warning) warning.remove();
        }
        
        return data.can_generate;
    } catch (error) {
        console.error('Error checking access:', error);
        return false;
    }
}
// ========== CREATE MODAL ==========
document.getElementById('createLinkBtn')?.addEventListener('click', () => {
    document.getElementById('createLinkModal').style.display = 'flex';
});

function closeCreateModal() {
    const modal = document.getElementById('createLinkModal');
    if (modal) modal.style.display = 'none';
    
    // Reset form
    const form = document.getElementById('createLinkForm');
    if (form) form.reset();
    
    // Clear search results
    const searchResults = document.getElementById('searchResults');
    if (searchResults) searchResults.style.display = 'none';
    
    // Clear preview
    const preview = document.getElementById('selectedProductPreview');
    if (preview) preview.innerHTML = '';
    
    // Reset values
    const productId = document.getElementById('productId');
    const productName = document.getElementById('productName');
    const productSearch = document.getElementById('productSearch');
    const commissionRate = document.getElementById('commissionRate');
    
    if (productId) productId.value = '';
    if (productName) productName.value = '';
    if (productSearch) productSearch.value = '';
    if (commissionRate) commissionRate.value = 10;
    
    selectedProducts = [];
}

// ========== MODAL SEARCH TYPE ==========
document.querySelectorAll('#modalSearchType .search-type-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('#modalSearchType .search-type-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        
        // Clear search results and input
        document.getElementById('productSearch').value = '';
        document.getElementById('searchResults').style.display = 'none';
        document.getElementById('searchResults').innerHTML = '';
    });
});

// Listen to campaign select change
document.getElementById('campaignId')?.addEventListener('change', function() {
    currentSelectedCampaignId = this.value;
    
    // Clear search results when campaign changes
    document.getElementById('searchResults').style.display = 'none';
    document.getElementById('productSearch').value = '';
    document.getElementById('productId').value = '';
    document.getElementById('productName').value = '';
    
    const preview = document.getElementById('selectedProductPreview');
    if (preview) preview.innerHTML = '';
});

// Product search with Shop Name or Product ID
document.getElementById('productSearch')?.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const keyword = this.value.trim();
    const modalSearchType = document.querySelector('#modalSearchType .search-type-btn.active')?.getAttribute('data-type') || 'shop';
    const campaignId = document.getElementById('campaignId').value;
    
    if (!campaignId) {
        showToastGlobal('Pilih campaign terlebih dahulu', 'error');
        return;
    }
    
    if (keyword.length < 2) {
        document.getElementById('searchResults').style.display = 'none';
        return;
    }
    
    searchTimeout = setTimeout(async () => {
        const searchResultsDiv = document.getElementById('searchResults');
        if (!searchResultsDiv) return;
        
        searchResultsDiv.innerHTML = '<div class="search-result-item" style="justify-content:center;">Searching... <i class="fas fa-spinner fa-pulse"></i></div>';
        searchResultsDiv.style.display = 'block';
        
        try {
            const response = await fetch(baseUrl + 'link_management/search_product', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ 
                    keyword: keyword,
                    campaign_id: campaignId,
                    search_type: modalSearchType
                })
            });
            const data = await response.json();
            
            if (data.success && data.products && data.products.length > 0) {
                let html = '';
                data.products.forEach(p => {
                    const imageUrl = p.image_url || '';
                    const hasImage = imageUrl && imageUrl !== '';
                    
                    html += `
    <div class="search-result-item" 
         data-id="${p.product_id}" 
         data-name="${escapeHtml(p.product_name)}" 
         data-price="${p.sale_price_min || p.price_min}" 
         data-commission="${p.commission_rate || 0}" 
         data-open-commission="${p.open_commission_rate || 0}"
         data-image="${escapeHtml(p.image_url)}"
         data-shop="${escapeHtml(p.shop_name || '')}"
         data-stock="${p.stock || 0}"
         data-sold="${p.items_sold || 0}"
         data-rating="${p.rating || 0}"
         data-top-selling="${p.is_top_selling}">
        <div class="search-result-img">
            ${hasImage ? `<img src="${escapeHtml(imageUrl)}" alt="${escapeHtml(p.product_name)}" onerror="this.src=''; this.onerror=null; this.parentElement.innerHTML='<i class=\\'fas fa-box\\'></i>'">` : '<i class="fas fa-box"></i>'}
            ${p.is_top_selling ? '<div class="top-selling-badge">🔥 Top</div>' : ''}
            ${p.is_trending ? '<div class="trending-badge">📈 Trending</div>' : ''}
        </div>
        <div class="search-result-info">
            <div class="search-result-name">
                ${escapeHtml(p.product_name)}
                ${p.is_top_selling ? '<span class="badge-top">Top Selling</span>' : ''}
                ${p.is_trending ? '<span class="badge-trending">Trending</span>' : ''}
            </div>
            <div class="search-result-details">
                <span class="product-id-label"><i class="fas fa-barcode"></i> ID: ${escapeHtml(p.product_id)}</span>
            </div>
            <div class="search-result-details">
                <span><i class="fas fa-store"></i> ${escapeHtml(p.shop_name || '-')}</span>
                <span class="search-result-price"><i class="fas fa-money-bill-wave"></i> Rp ${formatNumber(p.sale_price_min || p.price_min)}</span>
                ${p.price_max > p.price_min ? `<span>- Rp ${formatNumber(p.price_max)}</span>` : ''}
            </div>
            <div class="search-result-details">
                <span><i class="fas fa-chart-line"></i> Sold: ${formatNumber(p.items_sold || 0)}</span>
                ${p.items_sold_percent > 0 ? `<span class="trend-up">↑ ${p.items_sold_percent}%</span>` : ''}
                <span><i class="fas fa-boxes"></i> Stock: ${formatNumber(p.stock || 0)}</span>
                <span><i class="fas fa-star" style="color:#fbbf24;"></i> ${p.rating || 0}</span>
            </div>
            <div class="search-result-details">
                <span class="commission-info"><i class="fas fa-percent"></i> Open: ${p.open_commission_rate || 0}%</span>
                <span class="commission-info-total"><i class="fas fa-percent"></i> Total: ${p.commission_rate || 0}%</span>
                <span><i class="fas fa-ad"></i> Shop Ads: ${p.shop_ads_commission_rate || 6}%</span>
            </div>
        </div>
        <div style="color:#4ade80;">
            <i class="fas fa-chevron-right"></i>
        </div>
    </div>
`;
                });
                searchResultsDiv.innerHTML = html;
                
                document.querySelectorAll('.search-result-item').forEach(item => {
                    item.addEventListener('click', () => {
                        const productId = item.getAttribute('data-id');
                        const productName = item.getAttribute('data-name');
                        const productPrice = item.getAttribute('data-price');
                        const productImage = item.getAttribute('data-image');
                        let openCommissionRaw = parseFloat(item.getAttribute('data-open-commission') || 0);
                        const shopName = item.getAttribute('data-shop');
                        
                        // Konversi cents ke persen
                        let openCommissionPercent;
                        if (openCommissionRaw > 20) {
                            openCommissionPercent = openCommissionRaw / 100;
                        } else {
                            openCommissionPercent = openCommissionRaw;
                        }
                        
                        // Cek apakah produk sudah ada di keranjang pilihan
                        if (selectedProducts.some(p => p.id === productId)) {
                            showToastGlobal('Produk sudah dipilih', 'info');
                            return;
                        }
                        
                        // Tambahkan ke array pilihan
                        selectedProducts.push({
                            id: productId,
                            name: productName,
                            price: productPrice,
                            image: productImage,
                            openCommission: openCommissionPercent,
                            shopName: shopName
                        });
                        
                        // Tampilkan preview daftar produk terpilih
                        renderSelectedProducts();
                        
                        // Set default commission rate input based on recommended commission of first product (if not set)
                        const commissionInput = document.getElementById('commissionRate');
                        if (commissionInput && (!commissionInput.value || commissionInput.value === '10' || selectedProducts.length === 1)) {
                            commissionInput.value = openCommissionPercent + 1; // Open Plan + 1%
                        }
                        
                        searchResultsDiv.style.display = 'none';
                        document.getElementById('productSearch').value = '';
                    });
                });
            } else {
                searchResultsDiv.innerHTML = '<div class="search-result-item" style="justify-content:center;">Tidak ada produk ditemukan dalam campaign ini</div>';
            }
        } catch (error) {
            console.error('Search error:', error);
            searchResultsDiv.innerHTML = '<div class="search-result-item" style="justify-content:center; color:#ef4444;">Error searching products</div>';
        }
    }, 500);
});

function renderSelectedProducts() {
    const previewContainer = document.getElementById('selectedProductPreview');
    if (!previewContainer) return;
    
    if (selectedProducts.length === 0) {
        previewContainer.innerHTML = '';
        return;
    }
    
    let html = `
        <div style="margin-top:15px; display:flex; flex-direction:column; gap:10px;">
            <div style="font-size:11px; font-weight:700; color:var(--text-secondary); display:flex; justify-content:space-between; align-items:center;">
                <span>PRODUK YANG DIPILIH (${selectedProducts.length})</span>
                <button type="button" onclick="clearAllSelectedProducts()" style="background:transparent; border:none; color:#ef4444; cursor:pointer; font-size:11px; font-weight:600;">
                    <i class="fas fa-trash"></i> Hapus Semua
                </button>
            </div>
    `;
    
    selectedProducts.forEach((p, idx) => {
        html += `
            <div style="display:flex; align-items:center; gap:12px; padding:10px; background:#0f1420; border-radius:12px; border:1px solid rgba(74, 222, 128, 0.25);">
                <div style="width:48px; height:48px; background:#1e293b; border-radius:8px; display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0;">
                    ${p.image && p.image !== '' && p.image !== 'null' ? `<img src="${escapeHtml(p.image)}" style="width:100%; height:100%; object-fit:cover;" onerror="this.src=''; this.onerror=null; this.parentElement.innerHTML='<i class=\\'fas fa-box\\' style=\\'color:#4ade80;\\'></i>'">` : '<i class="fas fa-box" style="color:#4ade80;"></i>'}
                </div>
                <div style="flex:1; min-width:0;">
                    <div style="color:#e2f0e8; font-size:12px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${escapeHtml(p.name)}</div>
                    <div style="color:#4ade80; font-size:11px; font-weight:500;">Rp ${formatNumber(p.price)}</div>
                    <div style="color:var(--text-secondary); font-size:10px; margin-top:2px;">
                        Open Plan: ${p.openCommission}% ${p.shopName ? `| Toko: ${escapeHtml(p.shopName)}` : ''}
                    </div>
                </div>
                <button type="button" onclick="removeSelectedProduct(${idx})" style="background:transparent; border:none; color:#ef4444; cursor:pointer; padding:6px; flex-shrink:0;">
                    <i class="fas fa-times-circle"></i>
                </button>
            </div>
        `;
    });
    
    html += `</div>`;
    previewContainer.innerHTML = html;
}

window.removeSelectedProduct = function(index) {
    selectedProducts.splice(index, 1);
    renderSelectedProducts();
};

window.clearAllSelectedProducts = function() {
    selectedProducts = [];
    renderSelectedProducts();
    document.getElementById('productSearch').value = '';
};
document.getElementById('createLinkForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const canGenerateRes = await fetch(baseUrl + 'link_management/can_generate_link');
    const canGenerateData = await canGenerateRes.json();
    
    if (!canGenerateData.can_generate) {
        showToastGlobal(canGenerateData.message || 'Anda tidak memiliki akses untuk membuat link afiliasi. Hanya Head BA yang dapat generate link.', 'error');
        return;
    }
    
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Generating...';
    
    if (selectedProducts.length === 0) {
        showToastGlobal('Pilih minimal satu produk terlebih dahulu', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-link"></i> Generate Link';
        return;
    }
    
    const specialCaseChecked = document.getElementById('specialCase')?.checked ? 1 : 0;
    
    const response = await fetch(baseUrl + 'link_management/create_link', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            campaign_id: document.getElementById('campaignId').value,
            product_ids: JSON.stringify(selectedProducts.map(p => p.id)),
            commission_rate: document.getElementById('commissionRate').value,
            notes: document.getElementById('notes').value,
            special_case: specialCaseChecked
        })
    });
    
    const data = await response.json();
    
    if (data.success) {
        document.getElementById('linkPreview').style.display = 'block';
        document.getElementById('previewLink').innerHTML = data.link;
        showToastGlobal('Link created successfully!', 'success');
        setTimeout(() => location.reload(), 2000);
    } else {
        showToastGlobal(data.message, 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-link"></i> Generate Link';
    }
});

// ========== EDIT MODAL ==========
let currentEditId = null;

document.querySelectorAll('.edit-link').forEach(btn => {
    btn.addEventListener('click', () => {
        currentEditId = btn.getAttribute('data-id');
        document.getElementById('editLinkId').value = currentEditId;
        document.getElementById('editCommissionRate').value = btn.getAttribute('data-commission');
        document.getElementById('editStatus').value = btn.getAttribute('data-status');
        document.getElementById('editNotes').value = btn.getAttribute('data-notes') || '';
        document.getElementById('editLinkModal').style.display = 'flex';
    });
});

function closeEditModal() {
    document.getElementById('editLinkModal').style.display = 'none';
}

document.getElementById('updateLinkBtn')?.addEventListener('click', async () => {
    const response = await fetch(baseUrl + 'link_management/update_link', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            link_id: document.getElementById('editLinkId').value,
            commission_rate: document.getElementById('editCommissionRate').value,
            status: document.getElementById('editStatus').value,
            notes: document.getElementById('editNotes').value
        })
    });
    
    const data = await response.json();
    
    if (data.success) {
        showToastGlobal('Link updated successfully!', 'success');
        closeEditModal();
        setTimeout(() => location.reload(), 1500);
    } else {
        showToastGlobal(data.message, 'error');
    }
});

// ========== DELETE LINK ==========
document.querySelectorAll('.delete-link').forEach(btn => {
    btn.addEventListener('click', async () => {
        if (confirm('Are you sure you want to disable this link?')) {
            const response = await fetch(baseUrl + 'link_management/delete_link', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ link_id: btn.getAttribute('data-id') })
            });
            const data = await response.json();
            if (data.success) {
                showToastGlobal('Link disabled successfully!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToastGlobal(data.message, 'error');
            }
        }
    });
});

// ========== STATS MODAL ==========
document.querySelectorAll('.stats-link').forEach(btn => {
    btn.addEventListener('click', async () => {
        const linkId = btn.getAttribute('data-id');
        document.getElementById('statsModal').style.display = 'flex';
        document.getElementById('statsContent').innerHTML = '<div class="loading">Loading statistics...</div>';
        
        const response = await fetch(baseUrl + 'link_management/get_link_stats', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ link_id: linkId })
        });
        const data = await response.json();
        
        if (data.success) {
            let dailyHtml = '';
            (data.daily_stats || []).forEach(day => {
                dailyHtml += `<tr><td>${day.date}</td><td>${formatNumber(day.daily_orders)}<\/td><td class="gmv-cell">Rp ${formatNumber(day.daily_gmv)}<\/td><\/tr>`;
            });
            
            document.getElementById('statsContent').innerHTML = `
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:20px;">
                    <div style="background:#0f1420; padding:12px; border-radius:12px; text-align:center;">
                        <div style="color:#4ade80; font-size:20px;">Rp ${formatNumber(data.stats?.total_gmv)}</div>
                        <div style="color:#9aaebe; font-size:11px;">Total GMV</div>
                    </div>
                    <div style="background:#0f1420; padding:12px; border-radius:12px; text-align:center;">
                        <div style="color:#4ade80; font-size:20px;">${formatNumber(data.stats?.total_orders)}</div>
                        <div style="color:#9aaebe; font-size:11px;">Total Orders</div>
                    </div>
                    <div style="background:#0f1420; padding:12px; border-radius:12px; text-align:center;">
                        <div style="color:#fbbf24; font-size:20px;">Rp ${formatNumber(data.stats?.total_commission)}</div>
                        <div style="color:#9aaebe; font-size:11px;">Total Commission</div>
                    </div>
                    <div style="background:#0f1420; padding:12px; border-radius:12px; text-align:center;">
                        <div style="color:#4ade80; font-size:20px;">${formatNumber(data.stats?.total_creators)}</div>
                        <div style="color:#9aaebe; font-size:11px;">Creators</div>
                    </div>
                </div>
                <div style="margin-top:16px;">
                    <h4 style="color:#e2f0e8;">Product Details</h4>
                    <div style="background:#0f1420; padding:12px; border-radius:12px;">
                        <div class="product-cell" style="margin-bottom:12px;">
                            <div class="product-img">
                                ${data.link?.image_url ? `<img src="${escapeHtml(data.link.image_url)}" onerror="this.src=''; this.onerror=null; this.parentElement.innerHTML='<i class=\\'fas fa-box\\'></i>'">` : '<i class="fas fa-box"></i>'}
                            </div>
                            <div>
                                <div><strong>${escapeHtml(data.link?.product_name)}</strong></div>
                                <div class="product-shop"><i class="fas fa-store"></i> ${escapeHtml(data.link?.shop_name || '-')}</div>
                            </div>
                        </div>
                        <div><strong>Campaign:</strong> ${escapeHtml(data.link?.campaign_id)}</div>
                        <div class="mt-2"><strong>Link:</strong> <a href="${data.link?.affiliate_link}" target="_blank" style="color:#4ade80;">${data.link?.affiliate_link}</a></div>
                        <div class="mt-2"><strong>Commission:</strong> ${data.link?.commission_rate}%</div>
                        <div class="mt-2"><strong>Status:</strong> ${data.link?.status}</div>
                    </div>
                </div>
                <div style="margin-top:16px;">
                    <h4 style="color:#e2f0e8;">Daily Performance (Last 30 days)</h4>
                    <div style="overflow-x:auto;">
                        <table class="links-table" style="width:100%;">
                            <thead><tr><th>Date</th><th>Orders</th><th>GMV</th></tr></thead>
                            <tbody>${dailyHtml || '<td><td colspan="3" class="text-center">No data<\/td><\/tr>'}</tbody>
                        </table>
                    </div>
                </div>
            `;
        } else {
            document.getElementById('statsContent').innerHTML = '<div class="loading">Failed to load statistics</div>';
        }
    });
});

function closeStatsModal() {
    document.getElementById('statsModal').style.display = 'none';
}

// ========== COPY LINK ==========
document.querySelectorAll('.copy-link').forEach(btn => {
    btn.addEventListener('click', () => {
        const link = btn.getAttribute('data-link');
        navigator.clipboard.writeText(link);
        showToastGlobal('Link copied!', 'success');
    });
});

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
        `;
        document.body.appendChild(toast);
    }
    toast.textContent = message;
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 3000);
}

// Close modals when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

// Initialize
setTimeout(() => {
    // Client-side initialization no-op (server-side pagination active)
}, 200);
</script>