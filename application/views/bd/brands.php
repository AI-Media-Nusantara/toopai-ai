<!-- file: application/views/bd/brands.php -->
<?php
/**
 * BD Brands Management View
 * Data source: affiliate_products (approved products with shop_name)
 * Last updated: 2026-05-08
 */
?>
<style>
    /* ========== GLOBAL VARIABLES (match dashboard) ========== */
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
    
    .header-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: center;
    }
    
    /* ========== DATE FILTER ========== */
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
    
    .date-input:focus {
        border-color: var(--purple);
    }
    
    .btn-filter {
        background: var(--purple-glow);
        border: 1px solid var(--purple);
        color: var(--purple);
        padding: 6px 12px;
        border-radius: 40px;
        cursor: pointer;
        font-size: 11px;
        transition: var(--transition);
    }
    
    .btn-filter:hover {
        background: var(--purple);
        color: white;
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
        cursor: pointer;
    }
    
    .btn-sync:hover {
        background: var(--purple);
        color: white;
        box-shadow: var(--glow-purple);
    }
    
    /* ========== STATS GRID ========== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 28px;
    }
    
    .stat-card {
        background: var(--bg-card);
        border-radius: 20px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        border: 1px solid var(--border);
        transition: var(--transition);
    }
    
    .stat-card:hover {
        border-color: var(--purple);
        transform: translateY(-2px);
    }
    
    .stat-icon {
        font-size: 44px;
    }
    
    .stat-info {
        flex: 1;
    }
    
    .stat-value {
        font-size: 26px;
        font-weight: 700;
        background: linear-gradient(135deg, #4ade80, #22c55e);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }
    
    .stat-label {
        color: var(--text-muted);
        font-size: 12px;
        margin-top: 4px;
    }
    
    .stat-period {
        font-size: 9px;
        color: var(--text-muted);
        margin-top: 4px;
    }
    
    /* ========== TABS ========== */
    .brands-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 20px;
        border-bottom: 1px solid var(--border);
        padding-bottom: 0;
        flex-wrap: wrap;
    }
    
    .tab-btn {
        padding: 10px 20px;
        background: transparent;
        border: none;
        color: var(--text-secondary);
        cursor: pointer;
        font-size: 13px;
        font-weight: 500;
        border-radius: 40px 40px 0 0;
        transition: var(--transition);
    }
    
    .tab-btn.active {
        background: linear-gradient(135deg, var(--purple), var(--blue));
        color: white;
    }
    
    .tab-btn:hover:not(.active) {
        color: var(--purple);
        background: rgba(139, 92, 246, 0.1);
    }
    
    /* ========== SECTION CARD ========== */
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
        margin: 0;
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
        padding: 6px 12px;
        border-radius: 40px;
        color: var(--text-secondary);
        font-size: 12px;
        cursor: pointer;
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
    
    /* ========== BRANDS GRID ========== */
    .brands-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 16px;
    }
    
    .brand-card {
        background: var(--bg-elevated);
        border-radius: 18px;
        padding: 18px;
        text-decoration: none;
        display: block;
        border: 1px solid var(--border-light);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }
    
    .brand-card:hover {
        border-color: var(--purple);
        transform: translateY(-3px);
        box-shadow: var(--glow-purple);
    }
    
    .brand-card::before {
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
    
    .brand-card:hover::before {
        opacity: 1;
    }
    
    .brand-name {
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 12px;
        font-size: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .brand-name i {
        color: var(--purple);
        font-size: 18px;
    }
    
    .brand-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        font-size: 11px;
        color: var(--text-secondary);
        margin-bottom: 12px;
    }
    
    .brand-stats span {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    .brand-stats i {
        color: var(--purple);
        font-size: 11px;
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
    
    .badge-status.discovered {
        background: rgba(139, 92, 246, 0.15);
        color: #8b5cf6;
    }
    
    .badge-status.pending {
        background: rgba(245, 158, 11, 0.15);
        color: #f59e0b;
    }
    
    .badge-status.negotiating {
        background: rgba(59, 130, 246, 0.15);
        color: #3b82f6;
    }
    
    .badge-status.active {
        background: rgba(16, 185, 129, 0.15);
        color: #10b981;
    }
    
    .source-badge {
        font-size: 8px;
        padding: 2px 6px;
        border-radius: 10px;
    }
    
    .source-badge.discovered {
        background: rgba(139, 92, 246, 0.2);
        color: #8b5cf6;
    }
    
    .source-badge.manual {
        background: rgba(16, 185, 129, 0.2);
        color: #10b981;
    }
    
    /* ========== BRAND ACTIONS ========== */
    .brand-actions {
        display: flex;
        gap: 10px;
        margin-top: 16px;
        padding-top: 12px;
        border-top: 1px solid var(--border-light);
    }
    
    .btn-brand-action {
        background: transparent;
        border: 1px solid var(--purple);
        color: var(--purple);
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 11px;
        cursor: pointer;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    .btn-brand-action:hover {
        background: var(--purple);
        color: white;
    }
    
    .btn-brand-action.primary {
        background: var(--purple);
        color: white;
    }
    
    .btn-brand-action.primary:hover {
        background: #7c3aed;
        transform: translateY(-1px);
    }
    
    /* ========== EMPTY STATE & LOADING ========== */
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
    
    .loading {
        text-align: center;
        color: var(--text-muted);
        padding: 40px;
    }
    
    .loading i {
        font-size: 32px;
        color: var(--purple);
        margin-bottom: 12px;
        display: inline-block;
    }
    
    /* ========== PAGINATION ========== */
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
    
    /* ========== MODAL STYLES ========== */
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
        max-width: 800px;
        max-height: 85vh;
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
    
    /* ========== MODAL DETAIL STYLES ========== */
    .detail-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    
    .detail-stats .stat-item {
        background: var(--bg-elevated);
        padding: 16px;
        border-radius: 16px;
        text-align: center;
    }
    
    .detail-stats .stat-value {
        font-size: 20px;
        font-weight: 700;
        background: linear-gradient(135deg, #4ade80, #22c55e);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }
    
    .detail-section {
        margin-bottom: 20px;
    }
    
    .detail-section h4 {
        color: var(--text-primary);
        margin-bottom: 12px;
        font-size: 14px;
    }
    
    .detail-section h4 i {
        color: var(--purple);
        margin-right: 6px;
    }
    
    .products-list, .creators-list {
        max-height: 250px;
        overflow-y: auto;
    }
    
    .product-item, .creator-item {
        background: var(--bg-elevated);
        padding: 10px 12px;
        border-radius: 12px;
        margin-bottom: 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .product-name, .creator-name {
        font-size: 13px;
        font-weight: 500;
        color: var(--text-primary);
    }
    
    .product-gmv, .creator-gmv {
        font-size: 12px;
        color: #4ade80;
        font-weight: 600;
    }
    
    .product-commission {
        font-size: 10px;
        color: var(--text-muted);
        margin-left: 8px;
    }
    
    .product-image {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        object-fit: cover;
        margin-right: 10px;
    }
    
    .product-info {
        display: flex;
        align-items: center;
        flex: 1;
    }
    
    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 24px;
        padding-top: 16px;
        border-top: 1px solid var(--border);
    }
    
    /* ========== FORM STYLES ========== */
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
    
    .form-control:focus {
        outline: none;
        border-color: var(--purple);
    }
    
    .btn-submit {
        background: linear-gradient(135deg, var(--purple), var(--blue));
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 40px;
        cursor: pointer;
        width: 100%;
        font-size: 13px;
        font-weight: 600;
        margin-top: 8px;
        transition: var(--transition);
    }
    
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: var(--glow-purple);
    }
    
    /* ========== SYNC LOADING OVERLAY ========== */
    #syncLoading {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.7);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }
    
    .sync-loading-content {
        background: var(--bg-card);
        padding: 30px;
        border-radius: 20px;
        text-align: center;
        border: 1px solid var(--purple);
    }
    
    .sync-loading-content i {
        font-size: 48px;
        color: var(--purple);
        margin-bottom: 16px;
    }
    
    /* ========== RESPONSIVE ========== */
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }
        
        .brands-grid {
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
        
        .header-actions {
            width: 100%;
            justify-content: space-between;
        }
        
        .date-filter {
            flex-wrap: wrap;
        }
    }
</style>

<!-- ========== PAGE CONTENT ========== -->
<div class="page-header">
    <div>
        
        <h1 class="page-title"><i class="fas fa-building"></i> Brands Management</h1>
        <p class="page-subtitle">
            <i class="fas fa-database"></i> Data from affiliate_products (approved products) | 
            <i class="fas fa-sync-alt"></i> Auto-synced every 5 minutes by cron
        </p>
    </div>
    <div class="header-actions">
        <div class="date-filter">
            <input type="date" id="startDateFilter" class="date-input" value="<?= $start_date ?>">
            <span style="color:var(--text-muted);">→</span>
            <input type="date" id="endDateFilter" class="date-input" value="<?= $end_date ?>">
            <button id="applyDateFilterBtn" class="btn-filter">
                <i class="fas fa-calendar-alt"></i> Filter
            </button>
            <button id="resetDateFilterBtn" class="btn-filter">
                <i class="fas fa-undo-alt"></i> Reset
            </button>
        </div>
        <button class="btn-sync" id="syncBrandsBtn">
            <i class="fas fa-sync-alt"></i> Sync from API
        </button>
    </div>
</div>

<!-- ========== STATISTICS CARDS ========== -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">🏢</div>
        <div class="stat-info">
            <div class="stat-value" id="totalBrandsStat"><?= number_format($total_brands ?? 0) ?></div>
            <div class="stat-label">Total Brands Discovered</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-info">
            <div class="stat-value" id="totalGMVStat">Rp <?= number_format($total_gmv ?? 0, 0, ',', '.') ?></div>
            <div class="stat-label">Total GMV</div>
            <div class="stat-period" id="statPeriod">
                Periode: <?= date('d M Y', strtotime($start_date)) ?> - <?= date('d M Y', strtotime($end_date)) ?>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-info">
            <div class="stat-value" id="totalOrdersStat"><?= number_format($total_orders ?? 0) ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-info">
            <div class="stat-value" id="totalCreatorsStat"><?= number_format($total_creators ?? 0) ?></div>
            <div class="stat-label">Total Unique Creators</div>
        </div>
    </div>
</div>

<!-- ========== BRANDS LIST SECTION ========== -->
<div class="section-card">
    <div class="brands-tabs">
        <button class="tab-btn active" data-tab="all">
            <i class="fas fa-globe"></i> All Brands
        </button>
        <button class="tab-btn" data-tab="discovered">
            <i class="fas fa-search"></i> Discovered from API
        </button>
        <button class="tab-btn" data-tab="my_brands">
            <i class="fas fa-store"></i> My Portfolio
        </button>
    </div>
    
    <div class="section-header">
        <h3><i class="fas fa-list"></i> <span id="tabTitle">All Brands</span></h3>
        <div class="filter-group">
            <select id="statusFilter" class="filter-select">
                <option value="all">All Status</option>
                <option value="discovered">Discovered (Not Added)</option>
                <option value="pending">Pending</option>
                <option value="negotiating">Negotiating</option>
                <option value="active">Active</option>
            </select>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchBrandInput" placeholder="Cari brand name...">
            </div>
        </div>
    </div>
    
    <div id="brandsGrid" class="brands-grid">
        <div class="loading">
            <i class="fas fa-spinner fa-pulse"></i>
            <p>Loading brands from database...</p>
        </div>
    </div>
    
    <div class="pagination-container" id="paginationContainer">
        <button id="prevPageBtn" disabled><i class="fas fa-chevron-left"></i> Prev</button>
        <span class="page-info" id="pageInfo">Page 1</span>
        <button id="nextPageBtn">Next <i class="fas fa-chevron-right"></i></button>
    </div>
</div>

<!-- ========== MODAL: ADD BRAND ========== -->
<div id="addBrandModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-plus-circle"></i> Add Brand to Portfolio</h3>
            <span class="close" onclick="closeAddBrandModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label><i class="fas fa-store"></i> Brand Name *</label>
                <input type="text" id="brandName" class="form-control" placeholder="e.g., Somethinc, Scarlett, Avoskin">
            </div>
            <div class="form-group">
                <label><i class="fas fa-tag"></i> Category</label>
                <select id="brandCategory" class="form-control">
                    <option value="SKINCARE">🧴 Skincare</option>
                    <option value="MAKEUP">💄 Makeup</option>
                    <option value="FASHION">👗 Fashion</option>
                    <option value="FOOD">🍔 Food & Beverages</option>
                    <option value="ELECTRONICS">📱 Electronics</option>
                    <option value="HOME">🏠 Home & Living</option>
                    <option value="HEALTH">💊 Health & Wellness</option>
                    <option value="OTHER">✨ Other</option>
                </select>
            </div>
            <div class="form-group">
                <label><i class="fab fa-whatsapp"></i> WhatsApp Number</label>
                <input type="tel" id="brandWhatsapp" class="form-control" placeholder="08123456789">
            </div>
            <div class="form-group">
                <label><i class="fas fa-percent"></i> Proposed Commission (%)</label>
                <input type="number" id="brandCommission" class="form-control" value="10" step="0.5">
            </div>
            <button class="btn-submit" id="saveBrandBtn">
                <i class="fas fa-save"></i> Add to Portfolio
            </button>
        </div>
    </div>
</div>

<!-- ========== MODAL: DISCOVERED BRAND DETAIL ========== -->
<div id="discoveredBrandModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-store"></i> <span id="discoveredBrandName"></span></h3>
            <span class="close" onclick="closeDiscoveredModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="detail-stats">
                <div class="stat-item">
                    <div class="stat-label">Total GMV</div>
                    <div class="stat-value" id="detailGmv">Rp 0</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Total Orders</div>
                    <div class="stat-value" id="detailOrders">0</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Total Creators</div>
                    <div class="stat-value" id="detailCreators">0</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Category</div>
                    <div class="stat-value" id="detailCategory">-</div>
                </div>
            </div>
            
            <div class="detail-section">
                <h4><i class="fas fa-box"></i> Top Products</h4>
                <div id="detailProducts" class="products-list">
                    <div class="loading"><i class="fas fa-spinner fa-pulse"></i> Loading products...</div>
                </div>
            </div>
            
            <div class="detail-section">
                <h4><i class="fas fa-users"></i> Top Creators</h4>
                <div id="detailCreatorsList" class="creators-list">
                    <div class="loading"><i class="fas fa-spinner fa-pulse"></i> Loading creators...</div>
                </div>
            </div>
            
            <div class="modal-actions">
                <button class="btn-brand-action primary" id="addFromModalBtn">
                    <i class="fas fa-plus"></i> Add to Portfolio
                </button>
                <button class="btn-brand-action" onclick="closeDiscoveredModal()">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========== SYNC LOADING OVERLAY ========== -->
<div id="syncLoading">
    <div class="sync-loading-content">
        <i class="fas fa-spinner fa-pulse fa-3x"></i>
        <p style="margin-top: 16px; color: var(--text-primary);">Syncing brands from TikTok API...</p>
        <p style="font-size: 11px; color: var(--text-muted);">This may take a few moments</p>
    </div>
</div>

<script>
// ========== BASE URL ==========
const baseUrlBD = '<?= base_url() ?>';

// ========== DATA STORE ==========
let allBrands = [];
let currentPage = 1;
let itemsPerPage = 12;
let filteredBrands = [];
let currentTab = 'all';
let currentDiscoveredBrand = null;

// ========== HELPER FUNCTIONS ==========
function formatNumber(num) {
    if (num === undefined || num === null) return '0';
    return Number(num).toLocaleString('id-ID');
}

function formatCurrency(num) {
    return 'Rp ' + formatNumber(num);
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

function showToast(message, type = 'success') {
    // Check if toast container exists
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

// ========== RENDER BRANDS GRID ==========
function renderBrands() {
    const start = (currentPage - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const pageBrands = filteredBrands.slice(start, end);
    const grid = document.getElementById('brandsGrid');
    
    if (!grid) return;
    
    if (pageBrands.length === 0) {
        grid.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-store-slash"></i>
                <p>No brands found matching your filters.</p>
                <p style="font-size:11px; margin-top:8px;">Try changing the search term or filter.</p>
            </div>
        `;
        document.getElementById('paginationContainer').style.display = 'none';
        return;
    }
    
    let html = '';
    for (let brand of pageBrands) {
        const sourceBadge = brand.source === 'api' 
            ? '<span class="source-badge discovered"><i class="fas fa-globe"></i> Discovered</span>' 
            : '<span class="source-badge manual"><i class="fas fa-check-circle"></i> My Brand</span>';
        
        let statusClass = 'discovered';
        let statusIcon = 'search';
        let statusText = 'DISCOVERED';
        
        if (brand.source === 'manual') {
            const status = (brand.status || 'PENDING').toLowerCase();
            if (status === 'active') {
                statusClass = 'active';
                statusIcon = 'check-circle';
                statusText = 'ACTIVE';
            } else if (status === 'negotiating') {
                statusClass = 'negotiating';
                statusIcon = 'comment-dots';
                statusText = 'NEGOTIATING';
            } else if (status === 'pending') {
                statusClass = 'pending';
                statusIcon = 'clock';
                statusText = 'PENDING';
            } else {
                statusClass = status;
                statusIcon = 'store';
                statusText = status.toUpperCase();
            }
        }
        
        if (brand.source === 'api') {
            // Discovered brand card (clickable for details)
            html += `
                <div class="brand-card" data-brand-name="${escapeHtml(brand.name)}" data-brand-category="${escapeHtml(brand.category || '')}" data-brand-data='${JSON.stringify(brand)}'>
                    <div class="brand-name">
                        <i class="fas fa-globe"></i>
                        ${escapeHtml(brand.name)}
                        ${sourceBadge}
                    </div>
                    <div class="brand-stats">
                        <span><i class="fas fa-money-bill-wave"></i> ${formatCurrency(brand.total_gmv)}</span>
                        <span><i class="fas fa-box"></i> ${formatNumber(brand.total_orders)} orders</span>
                        <span><i class="fas fa-users"></i> ${formatNumber(brand.total_creators)} creators</span>
                    </div>
                    <div class="brand-actions">
                        <button class="btn-brand-action view-details" data-name="${escapeHtml(brand.name)}" data-category="${escapeHtml(brand.category || '')}">
                            <i class="fas fa-info-circle"></i> View Details
                        </button>
                        <button class="btn-brand-action primary add-portfolio" data-name="${escapeHtml(brand.name)}" data-category="${escapeHtml(brand.category || '')}">
                            <i class="fas fa-plus"></i> Add to Portfolio
                        </button>
                    </div>
                </div>
            `;
        } else {
            // Manual brand card (link to detail page)
            html += `
                <a href="<?= base_url('bd/brand_detail/') ?>${brand.id}" class="brand-card">
                    <div class="brand-name">
                        <i class="fas fa-store"></i>
                        ${escapeHtml(brand.name)}
                        ${sourceBadge}
                        <span class="badge-status ${statusClass}" style="margin-left:auto;">
                            <i class="fas fa-${statusIcon}"></i> ${statusText}
                        </span>
                    </div>
                    <div class="brand-stats">
                        <span><i class="fas fa-money-bill-wave"></i> ${formatCurrency(brand.total_gmv)}</span>
                        <span><i class="fas fa-box"></i> ${formatNumber(brand.total_orders)} orders</span>
                        <span><i class="fas fa-users"></i> ${formatNumber(brand.total_creators)} creators</span>
                    </div>
                </a>
            `;
        }
    }
    grid.innerHTML = html;
    
    // Update pagination
    const totalPages = Math.max(1, Math.ceil(filteredBrands.length / itemsPerPage));
    document.getElementById('pageInfo').innerText = `Page ${currentPage} of ${totalPages}`;
    document.getElementById('prevPageBtn').disabled = currentPage === 1;
    document.getElementById('nextPageBtn').disabled = currentPage === totalPages || totalPages === 0;
    document.getElementById('paginationContainer').style.display = filteredBrands.length > itemsPerPage ? 'flex' : 'none';
}

// ========== APPLY FILTERS ==========
function applyFilters() {
    const searchTerm = document.getElementById('searchBrandInput')?.value.toLowerCase() || '';
    const statusFilter = document.getElementById('statusFilter')?.value || 'all';
    
    filteredBrands = allBrands.filter(brand => {
        let matchesTab = true;
        if (currentTab === 'my_brands') {
            matchesTab = brand.source === 'manual';
        } else if (currentTab === 'discovered') {
            matchesTab = brand.source === 'api';
        }
        
        const matchesSearch = (brand.name || '').toLowerCase().includes(searchTerm);
        
        let matchesStatus = true;
        if (statusFilter !== 'all') {
            if (statusFilter === 'discovered') {
                matchesStatus = brand.source === 'api';
            } else if (statusFilter === 'pending') {
                matchesStatus = brand.status === 'PENDING' && brand.source === 'manual';
            } else if (statusFilter === 'negotiating') {
                matchesStatus = brand.status === 'NEGOTIATING';
            } else if (statusFilter === 'active') {
                matchesStatus = brand.status === 'ACTIVE';
            }
        }
        
        return matchesTab && matchesSearch && matchesStatus;
    });
    
    currentPage = 1;
    renderBrands();
}

// ========== TAB HANDLERS ==========
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        currentTab = this.getAttribute('data-tab');
        const titles = {
            'all': 'All Brands',
            'discovered': 'Discovered from API',
            'my_brands': 'My Portfolio'
        };
        const tabTitleElem = document.getElementById('tabTitle');
        if (tabTitleElem) tabTitleElem.innerText = titles[currentTab] || 'Brands';
        applyFilters();
    });
});

// ========== SEARCH & FILTER EVENT LISTENERS ==========
document.getElementById('searchBrandInput')?.addEventListener('keyup', applyFilters);
document.getElementById('statusFilter')?.addEventListener('change', applyFilters);

// ========== PAGINATION ==========
document.getElementById('prevPageBtn')?.addEventListener('click', () => {
    if (currentPage > 1) {
        currentPage--;
        renderBrands();
    }
});

document.getElementById('nextPageBtn')?.addEventListener('click', () => {
    const totalPages = Math.ceil(filteredBrands.length / itemsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        renderBrands();
    }
});

// ========== ADD BRAND MODAL ==========
function showAddBrandModal() {
    const modal = document.getElementById('addBrandModal');
    if (modal) modal.style.display = 'flex';
}

function closeAddBrandModal() {
    const modal = document.getElementById('addBrandModal');
    if (modal) modal.style.display = 'none';
    document.getElementById('brandName').value = '';
    document.getElementById('brandWhatsapp').value = '';
    document.getElementById('brandCommission').value = '10';
}

document.getElementById('addBrandBtn')?.addEventListener('click', showAddBrandModal);

// ========== SAVE BRAND TO PORTFOLIO ==========
document.getElementById('saveBrandBtn')?.addEventListener('click', async () => {
    const name = document.getElementById('brandName').value.trim();
    const category = document.getElementById('brandCategory').value;
    const whatsapp = document.getElementById('brandWhatsapp').value;
    const commission = document.getElementById('brandCommission').value;
    
    if (!name) {
        showToast('Brand name is required', 'error');
        return;
    }
    
    try {
        const response = await fetch('<?= base_url("bd/scout_match_brand") ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `brand_name=${encodeURIComponent(name)}&category=${category}&whatsapp_number=${whatsapp}&commission=${commission}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Brand added successfully!');
            closeAddBrandModal();
            setTimeout(() => location.reload(), 1500);
        } else if (data.already_exists) {
            showToast('Brand already exists in your portfolio!', 'error');
        } else {
            showToast('Error: ' + (data.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        showToast('Network error: ' + error.message, 'error');
    }
});

// ========== SYNC BRANDS FROM API ==========
document.getElementById('syncBrandsBtn')?.addEventListener('click', async () => {
    const loadingDiv = document.getElementById('syncLoading');
    loadingDiv.style.display = 'flex';
    
    try {
        const response = await fetch('<?= base_url("bd/sync") ?>', {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json();
        loadingDiv.style.display = 'none';
        
        if (data.success) {
            showToast('Brands synced successfully from TikTok API!');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast('Error: ' + (data.message || 'Sync failed'), 'error');
        }
    } catch (error) {
        loadingDiv.style.display = 'none';
        showToast('Network error: ' + error.message, 'error');
    }
});

// ========== DISCOVERED BRAND DETAIL FUNCTIONS ==========
async function showDiscoveredBrandDetail(brandName, category, brandData) {
    currentDiscoveredBrand = { name: brandName, category: category, data: brandData };
    
    const modal = document.getElementById('discoveredBrandModal');
    const nameElem = document.getElementById('discoveredBrandName');
    const categoryElem = document.getElementById('detailCategory');
    const gmvElem = document.getElementById('detailGmv');
    const ordersElem = document.getElementById('detailOrders');
    const creatorsElem = document.getElementById('detailCreators');
    
    if (nameElem) nameElem.innerText = brandName;
    if (categoryElem) categoryElem.innerText = category || 'Unknown';
    if (gmvElem) gmvElem.innerHTML = formatCurrency(brandData?.total_gmv || 0);
    if (ordersElem) ordersElem.innerHTML = formatNumber(brandData?.total_orders || 0);
    if (creatorsElem) creatorsElem.innerHTML = formatNumber(brandData?.total_creators || 0);
    
    if (modal) modal.style.display = 'flex';
    
    // Load products
    const productsContainer = document.getElementById('detailProducts');
    if (productsContainer) {
        productsContainer.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-pulse"></i> Loading products...</div>';
        
        try {
            const response = await fetch('<?= base_url("bd/get_discovered_brand_products") ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `shop_name=${encodeURIComponent(brandName)}`
            });
            const data = await response.json();
            
            if (data.success && data.products && data.products.length > 0) {
                let html = '';
                for (let p of data.products) {
                    html += `
                        <div class="product-item">
                            <div class="product-info">
                                ${p.image_url ? `<img src="${escapeHtml(p.image_url)}" class="product-image" onerror="this.style.display='none'">` : ''}
                                <div class="product-name">${escapeHtml(p.product_name)}</div>
                                ${p.commission_rate ? `<div class="product-commission">${p.commission_rate}%</div>` : ''}
                            </div>
                            <div class="product-gmv">${formatCurrency(p.gmv)}</div>
                        </div>
                    `;
                }
                productsContainer.innerHTML = html;
            } else {
                productsContainer.innerHTML = '<div class="empty-state"><i class="fas fa-box-open"></i><p>No products found for this brand</p></div>';
            }
        } catch (error) {
            productsContainer.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading products</p></div>';
        }
    }
    
    // Load creators
    const creatorsContainer = document.getElementById('detailCreatorsList');
    if (creatorsContainer) {
        creatorsContainer.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-pulse"></i> Loading creators...</div>';
        
        try {
            const response = await fetch('<?= base_url("bd/get_discovered_brand_creators") ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `shop_name=${encodeURIComponent(brandName)}`
            });
            const data = await response.json();
            
            if (data.success && data.creators && data.creators.length > 0) {
                let html = '';
                for (let c of data.creators) {
                    html += `
                        <div class="creator-item">
                            <div class="creator-name">@${escapeHtml(c.creator_username)}</div>
                            <div class="creator-gmv">${formatCurrency(c.total_gmv)}</div>
                        </div>
                    `;
                }
                creatorsContainer.innerHTML = html;
            } else {
                creatorsContainer.innerHTML = '<div class="empty-state"><i class="fas fa-user-slash"></i><p>No creators found for this brand</p></div>';
            }
        } catch (error) {
            creatorsContainer.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading creators</p></div>';
        }
    }
}

function closeDiscoveredModal() {
    const modal = document.getElementById('discoveredBrandModal');
    if (modal) modal.style.display = 'none';
    currentDiscoveredBrand = null;
}

async function addDiscoveredBrand(brandName, category) {
    if (!confirm(`Add "${brandName}" to your brand portfolio?`)) return;
    
    const commission = prompt('Enter proposed commission rate (%)', '10');
    if (commission === null) return;
    
    try {
        const response = await fetch('<?= base_url("bd/add_discovered_brand") ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `brand_name=${encodeURIComponent(brandName)}&category=${encodeURIComponent(category)}&commission=${commission}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Brand added successfully!');
            if (data.redirect) {
                setTimeout(() => window.location.href = data.redirect, 1500);
            } else {
                setTimeout(() => location.reload(), 1500);
            }
        } else if (data.already_exists) {
            showToast('Brand already exists in your portfolio!', 'error');
        } else {
            showToast('Error: ' + (data.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        showToast('Network error: ' + error.message, 'error');
    }
}

// ========== EVENT DELEGATION FOR DYNAMIC BUTTONS ==========
document.getElementById('brandsGrid')?.addEventListener('click', (e) => {
    const viewBtn = e.target.closest('.view-details');
    if (viewBtn) {
        e.preventDefault();
        e.stopPropagation();
        const name = viewBtn.getAttribute('data-name');
        const category = viewBtn.getAttribute('data-category');
        const brandData = allBrands.find(b => b.name === name);
        showDiscoveredBrandDetail(name, category, brandData);
        return;
    }
    
    const addBtn = e.target.closest('.add-portfolio');
    if (addBtn) {
        e.preventDefault();
        e.stopPropagation();
        const name = addBtn.getAttribute('data-name');
        const category = addBtn.getAttribute('data-category');
        addDiscoveredBrand(name, category);
        return;
    }
});

document.getElementById('addFromModalBtn')?.addEventListener('click', () => {
    if (currentDiscoveredBrand) {
        addDiscoveredBrand(currentDiscoveredBrand.name, currentDiscoveredBrand.category);
        closeDiscoveredModal();
    }
});

// ========== DATE FILTER ==========
function applyDateFilter() {
    const startDate = document.getElementById('startDateFilter').value;
    const endDate = document.getElementById('endDateFilter').value;
    
    if (!startDate || !endDate) {
        showToast('Please select both start and end dates', 'error');
        return;
    }
    
    if (startDate > endDate) {
        showToast('Start date cannot be after end date', 'error');
        return;
    }
    
    window.location.href = baseUrlBD + `bd/brands?start_date=${startDate}&end_date=${endDate}`;
}

function resetDateFilter() {
    const today = new Date().toISOString().split('T')[0];
    window.location.href = baseUrlBD + `bd/brands?start_date=${today}&end_date=${today}`;
}

document.getElementById('applyDateFilterBtn')?.addEventListener('click', applyDateFilter);
document.getElementById('resetDateFilterBtn')?.addEventListener('click', resetDateFilter);

// ========== LOAD INITIAL DATA FROM PHP ==========
<?php if (!empty($brands)): ?>
allBrands = <?= json_encode(array_map(function($b) {
    return [
        'id' => $b['id'] ?? '',
        'name' => $b['name'] ?? '',
        'total_gmv' => floatval($b['total_gmv'] ?? 0),
        'total_orders' => intval($b['total_orders'] ?? 0),
        'total_creators' => intval($b['total_creators'] ?? 0),
        'status' => $b['status'] ?? 'DISCOVERED',
        'source' => $b['source'] ?? 'api',
        'category' => $b['category'] ?? ''
    ];
}, $brands)) ?>;
filteredBrands = [...allBrands];
renderBrands();
<?php else: ?>
// Jika tidak ada data, tampilkan pesan
document.getElementById('brandsGrid').innerHTML = `
    <div class="empty-state">
        <i class="fas fa-database"></i>
        <p>No brands found. Click "Sync from API" to load brands from TikTok.</p>
    </div>
`;
<?php endif; ?>

// Set default dates if not set
const startInput = document.getElementById('startDateFilter');
const endInput = document.getElementById('endDateFilter');
if (startInput && !startInput.value) {
    const today = new Date().toISOString().split('T')[0];
    startInput.value = today;
    endInput.value = today;
}
</script>

<!-- Toast notification styles -->
<style>
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
</style>