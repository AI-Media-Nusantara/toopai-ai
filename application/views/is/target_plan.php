<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
    .target-plan-container {
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }
    
    .page-title {
        font-size: 24px;
        font-weight: 700;
        background: linear-gradient(135deg, var(--purple), var(--cyan));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }
    
    .request-steps {
        display: flex;
        gap: 12px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    
    .step-card {
        flex: 1;
        background: var(--bg-card);
        border-radius: 16px;
        padding: 16px;
        border: 1px solid var(--border);
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }
    
    .step-card.active {
        border-color: var(--purple);
        background: linear-gradient(135deg, rgba(139,92,246,0.1), rgba(6,182,212,0.05));
    }
    
    .step-card.completed {
        border-color: #4ade80;
        background: rgba(74,222,128,0.05);
    }
    
    .step-number {
        width: 32px;
        height: 32px;
        background: var(--bg-elevated);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        margin-bottom: 12px;
        color: var(--text-secondary);
    }
    
    .step-card.active .step-number {
        background: var(--purple);
        color: white;
    }
    
    .step-card.completed .step-number {
        background: #4ade80;
        color: #0a0e17;
    }
    
    .step-title {
        font-weight: 600;
        margin-bottom: 4px;
    }
    
    .step-desc {
        font-size: 11px;
        color: var(--text-muted);
    }
    
    /* Search Creator Section */
    .search-creator-section {
        background: var(--bg-card);
        border-radius: 20px;
        padding: 20px;
        border: 1px solid var(--border);
        margin-bottom: 24px;
    }
    
    .search-input-group {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
    }
    
    .search-input-group input {
        flex: 1;
        padding: 12px 16px;
        background: var(--bg-elevated);
        border: 1px solid var(--border);
        border-radius: 12px;
        color: var(--text-primary);
        font-size: 14px;
    }
    
    .search-results {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .creator-result-card {
        background: var(--bg-elevated);
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 12px;
        border: 1px solid var(--border);
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        gap: 16px;
        align-items: center;
    }
    
    .creator-result-card:hover {
        border-color: var(--purple);
        transform: translateX(4px);
    }
    
    .creator-result-card.selected {
        border-color: #4ade80;
        background: rgba(74,222,128,0.05);
    }
    
    .creator-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        overflow: hidden;
        background: var(--bg-elevated);
        flex-shrink: 0;
    }
    
    .creator-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .creator-info {
        flex: 1;
    }
    
    .creator-name {
        font-weight: 600;
        margin-bottom: 4px;
    }
    
    .creator-username {
        font-size: 12px;
        color: var(--text-muted);
        margin-bottom: 4px;
    }
    
    .creator-stats {
        display: flex;
        gap: 16px;
        font-size: 11px;
    }
    
    .select-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    
    .select-badge.selected {
        background: #4ade80;
        color: #0a0e17;
    }
    
    /* Products Grid */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 16px;
        max-height: 500px;
        overflow-y: auto;
        padding: 8px;
    }
    
    .product-card {
        background: var(--bg-elevated);
        border-radius: 16px;
        padding: 12px;
        border: 1px solid var(--border);
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        gap: 12px;
    }
    
    .product-card:hover {
        border-color: var(--purple);
    }
    
    .product-card.selected {
        border-color: #4ade80;
        background: rgba(74,222,128,0.05);
    }
    
    .product-image {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        overflow: hidden;
        background: #1e293b;
        flex-shrink: 0;
    }
    
    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .product-info {
        flex: 1;
    }
    
    .product-name {
        font-size: 13px;
        font-weight: 500;
        margin-bottom: 4px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .product-price {
        color: #4ade80;
        font-size: 12px;
        font-weight: 600;
    }
    
    .product-commission {
        font-size: 10px;
        color: #fbbf24;
        margin-top: 4px;
    }
    
    /* Summary Section */
    .summary-section {
        background: linear-gradient(135deg, #1a1030, #13111f);
        border-radius: 20px;
        padding: 20px;
        border: 1px solid var(--border);
        margin-top: 24px;
    }
    
    .selected-products-list {
        max-height: 200px;
        overflow-y: auto;
        margin: 16px 0;
    }
    
    .selected-product-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        border-bottom: 1px solid var(--border);
    }
    
    .commission-input-group {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 16px 0;
    }
    
    .commission-input-group input {
        width: 100px;
        padding: 10px;
        background: var(--bg-elevated);
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text-primary);
        text-align: center;
    }
    
    .btn-submit {
        background: linear-gradient(135deg, var(--purple), var(--blue));
        color: white;
        border: none;
        padding: 14px 24px;
        border-radius: 40px;
        font-weight: 600;
        cursor: pointer;
        width: 100%;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    
    .btn-submit:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(139,92,246,0.4);
    }
    
    .btn-submit:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    /* Toast Custom */
    .toast-custom {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #10b981;
        color: white;
        padding: 12px 20px;
        border-radius: 12px;
        z-index: 9999;
        animation: slideIn 0.3s ease;
    }
    
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
</style>

<div class="target-plan-container">
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="fas fa-bullseye"></i> Target Plan Request</h1>
            <p class="sub">Ajukan request komisi lebih tinggi untuk creator</p>
        </div>
        <div class="stats-badge">
            <span class="badge-dashboard" style="background: rgba(139,92,246,0.15);">
                <i class="fas fa-info-circle"></i> Request komisi lebih tinggi dari sebelumnya
            </span>
        </div>
    </div>
    
    <!-- Steps -->
    <div class="request-steps">
        <div class="step-card" id="step1Card">
            <div class="step-number">1</div>
            <div class="step-title">Pilih Creator</div>
            <div class="step-desc">Cari dan pilih creator</div>
        </div>
        <div class="step-card" id="step2Card">
            <div class="step-number">2</div>
            <div class="step-title">Pilih Campaign & Produk</div>
            <div class="step-desc">Pilih campaign dan produk</div>
        </div>
        <div class="step-card" id="step3Card">
            <div class="step-number">3</div>
            <div class="step-title">Tentukan Komisi</div>
            <div class="step-desc">Request komisi baru</div>
        </div>
        <div class="step-card" id="step4Card">
            <div class="step-number">4</div>
            <div class="step-title">Submit Request</div>
            <div class="step-desc">Kirim ke Leader IS</div>
        </div>
    </div>
    
    <!-- STEP 1: Pilih Creator -->
    <div id="step1Content" class="search-creator-section">
        <h3 style="margin-bottom: 16px;"><i class="fab fa-tiktok"></i> Cari Creator</h3>
        <div class="search-input-group">
            <input type="text" id="searchCreatorInput" placeholder="Cari creator... (contoh: ilpiaaaberkahshop)">
            <button id="searchCreatorBtn" class="btn-secondary" style="padding: 12px 24px;">
                <i class="fas fa-search"></i> Cari
            </button>
        </div>
        <div id="creatorSearchResults" class="search-results">
            <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                <i class="fas fa-search fa-2x"></i>
                <p>Masukkan username creator untuk mencari</p>
            </div>
        </div>
    </div>
    
    <!-- STEP 2: Pilih Campaign & Produk -->
    <div id="step2Content" style="display: none;">
        <div class="search-creator-section">
            <h3><i class="fas fa-bullhorn"></i> Pilih Campaign</h3>
            <select id="campaignSelect" class="form-select" style="width:100%; padding:12px; background:var(--bg-elevated); border:1px solid var(--border); border-radius:12px; margin-bottom:20px;">
                <option value="">-- Pilih Campaign --</option>
                <?php foreach ($campaigns as $camp): ?>
                <option value="<?= $camp->campaign_id ?>"><?= htmlspecialchars($camp->campaign_name) ?></option>
                <?php endforeach; ?>
            </select>
            
            <div id="productsContainer" style="display: none;">
                <h3><i class="fas fa-box"></i> Pilih Produk (Bisa beberapa)</h3>
                <div id="productsGrid" class="products-grid">
                    <div style="text-align:center; padding:40px;">Pilih campaign terlebih dahulu</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- STEP 3: Tentukan Komisi -->
    <div id="step3Content" style="display: none;">
        <div class="search-creator-section">
            <h3><i class="fas fa-percent"></i> Request Komisi</h3>
            
            <div id="selectedCreatorInfo" style="background:rgba(139,92,246,0.1); border-radius:12px; padding:16px; margin-bottom:20px;">
                <div style="display:flex; gap:16px; align-items:center;">
                    <div id="selectedCreatorAvatar" style="width:50px; height:50px; border-radius:50%; overflow:hidden; background:#1e293b;"></div>
                    <div>
                        <div id="selectedCreatorName" style="font-weight:600;"></div>
                        <div id="selectedCreatorUsername" style="font-size:12px; color:var(--text-muted);"></div>
                    </div>
                </div>
            </div>
            
            <div id="selectedProductsInfo" style="margin-bottom:20px;">
                <h4>Produk Dipilih:</h4>
                <div id="selectedProductsList" class="selected-products-list"></div>
            </div>
            
            <div class="commission-input-group">
                <span style="color:var(--text-muted);">Komisi Saat Ini:</span>
                <span id="currentCommission" style="color:#fbbf24; font-weight:600;">0%</span>
                <span style="color:var(--text-muted); margin-left:20px;">Request Komisi:</span>
                <input type="number" id="requestedCommission" step="0.5" min="1" max="50" value="7">
                <span style="color:#4ade80;">%</span>
            </div>
            
            <div class="commission-note" style="background:rgba(245,158,11,0.1); border-radius:12px; padding:12px; margin-bottom:20px;">
                <i class="fas fa-info-circle" style="color:#f59e0b;"></i>
                <span style="font-size:12px;"> Request komisi minimal 1% di atas komisi saat ini</span>
            </div>
            
            <label>Alasan Request (Opsional)</label>
            <textarea id="requestReason" rows="3" style="width:100%; padding:12px; background:var(--bg-elevated); border:1px solid var(--border); border-radius:12px; color:var(--text-primary);" placeholder="Contoh: Creator memiliki performa tinggi, ingin meningkatkan motivasi..."></textarea>
        </div>
    </div>
    
    <!-- STEP 4: Submit -->
    <div id="step4Content" style="display: none;">
        <div class="summary-section">
            <h3><i class="fas fa-file-alt"></i> Ringkasan Request</h3>
            
            <div style="margin: 20px 0; padding: 16px; background: var(--bg-elevated); border-radius: 12px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span>Request Code:</span>
                    <span id="summaryRequestCode" style="font-family: monospace; color: #4ade80;">-</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span>Creator:</span>
                    <span id="summaryCreator">-</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span>Campaign:</span>
                    <span id="summaryCampaign">-</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span>Jumlah Produk:</span>
                    <span id="summaryProductCount">0</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span>Komisi Saat Ini:</span>
                    <span id="summaryCurrentCommission">0%</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span>Request Komisi:</span>
                    <span id="summaryRequestedCommission" style="color: #fbbf24; font-weight: 600;">0%</span>
                </div>
            </div>
            
            <button id="submitRequestBtn" class="btn-submit">
                <i class="fas fa-paper-plane"></i> Submit Request ke Leader IS
            </button>
        </div>
    </div>
</div>

<script>
// ========== TARGET PLAN REQUEST ==========
let selectedCreator = null;
let selectedCampaignId = null;
let selectedProducts = [];
let currentStep = 1;
let currentCreatorCommission = 0;
let searchResults = [];
let selectedCreatorAvatar = '';
let selectedCreatorPhone = '';
let selectedCreatorId = null;

// DOM Elements
const searchInput = document.getElementById('searchCreatorInput');
const searchBtn = document.getElementById('searchCreatorBtn');
const creatorResults = document.getElementById('creatorSearchResults');
const step1Content = document.getElementById('step1Content');
const step2Content = document.getElementById('step2Content');
const step3Content = document.getElementById('step3Content');
const step4Content = document.getElementById('step4Content');
const campaignSelect = document.getElementById('campaignSelect');
const productsContainer = document.getElementById('productsContainer');
const productsGrid = document.getElementById('productsGrid');

// Search creator
searchBtn.addEventListener('click', async () => {
    const keyword = searchInput.value.trim();
    if (!keyword) {
        showToastGlobal('Masukkan keyword pencarian', 'error');
        return;
    }
    
    creatorResults.innerHTML = '<div style="text-align:center; padding:40px;"><i class="fas fa-spinner fa-pulse"></i> Searching...</div>';
    
    try {
        const response = await fetch(baseUrlIS + 'is/search_creators_by_is', {
            method: 'POST',
            body: new URLSearchParams({ keyword: keyword })
        });
        const result = await response.json();
        
        if (result.success && result.creators.length > 0) {
            searchResults = result.creators;
            renderCreatorResults(searchResults);
        } else {
            creatorResults.innerHTML = '<div style="text-align:center; padding:40px; color:#ef4444;">Tidak ditemukan creator</div>';
        }
    } catch (error) {
        creatorResults.innerHTML = '<div style="text-align:center; padding:40px; color:#ef4444;">Error: ' + error.message + '</div>';
    }
});

// Enter key search
searchInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') searchBtn.click();
});

function renderCreatorResults(creators) {
    creatorResults.innerHTML = '';
    creators.forEach(creator => {
        const isSelected = selectedCreator && selectedCreator.username === creator.username;
        const avatarHtml = creator.avatar_url 
            ? `<img src="${escapeHtml(creator.avatar_url)}" onerror="this.src=''">`
            : '<i class="fas fa-user fa-2x" style="color:#8b5cf6;"></i>';
        
        const card = document.createElement('div');
        card.className = `creator-result-card ${isSelected ? 'selected' : ''}`;
        card.innerHTML = `
            <div class="creator-avatar">${avatarHtml}</div>
            <div class="creator-info">
                <div class="creator-name">${escapeHtml(creator.nickname || creator.username)}</div>
                <div class="creator-username">@${escapeHtml(creator.username)}</div>
                <div class="creator-stats">
                    <span><i class="fas fa-users"></i> ${formatNumberShort(creator.follower_count)}</span>
                    <span><i class="fas fa-chart-line"></i> Avg View: ${formatNumberShort(creator.avg_video_views || 0)}</span>
                </div>
            </div>
            <div class="select-badge ${isSelected ? 'selected' : ''}">
                ${isSelected ? '<i class="fas fa-check"></i> Dipilih' : '<i class="fas fa-plus"></i> Pilih'}
            </div>
        `;
        
        card.addEventListener('click', () => {
            // Deselect previous
            document.querySelectorAll('.creator-result-card').forEach(c => {
                c.classList.remove('selected');
                c.querySelector('.select-badge').classList.remove('selected');
                c.querySelector('.select-badge').innerHTML = '<i class="fas fa-plus"></i> Pilih';
            });
            
            // Select this
            card.classList.add('selected');
            card.querySelector('.select-badge').classList.add('selected');
            card.querySelector('.select-badge').innerHTML = '<i class="fas fa-check"></i> Dipilih';
            
            selectedCreator = creator;
            selectedCreatorId = creator.id;
            selectedCreatorAvatar = creator.avatar_url || '';
            selectedCreatorPhone = creator.phone || '';
            currentCreatorCommission = creator.current_commission || 6;
            
            // Auto go to step 2
            goToStep(2);
        });
        
        creatorResults.appendChild(card);
    });
}

// Campaign select
campaignSelect.addEventListener('change', async () => {
    selectedCampaignId = campaignSelect.value;
    if (!selectedCampaignId) {
        productsContainer.style.display = 'none';
        return;
    }
    
    productsContainer.style.display = 'block';
    productsGrid.innerHTML = '<div style="text-align:center; padding:40px;"><i class="fas fa-spinner fa-pulse"></i> Loading products...</div>';
    
    try {
        const response = await fetch(baseUrlIS + 'is/get_campaign_products', {
            method: 'POST',
            body: new URLSearchParams({
                campaign_id: selectedCampaignId,
                creator_category: selectedCreator?.category || ''
            })
        });
        const result = await response.json();
        
        if (result.success && result.products.length > 0) {
            renderProducts(result.products);
        } else {
            productsGrid.innerHTML = '<div style="text-align:center; padding:40px; color:#9aaebe;">Tidak ada produk tersedia</div>';
        }
    } catch (error) {
        productsGrid.innerHTML = '<div style="text-align:center; padding:40px; color:#ef4444;">Error loading products</div>';
    }
});

function renderProducts(products) {
    productsGrid.innerHTML = '';
    products.forEach(product => {
        const isSelected = selectedProducts.some(p => p.product_id === product.product_id);
        const imageUrl = product.image_url || '';
        const commissionPercent = (product.commission_rate / 100).toFixed(1);
        
        const card = document.createElement('div');
        card.className = `product-card ${isSelected ? 'selected' : ''}`;
        card.innerHTML = `
            <div class="product-image">
                ${imageUrl ? `<img src="${escapeHtml(imageUrl)}" onerror="this.src=''">` : '<i class="fas fa-box fa-2x" style="color:#4ade80;"></i>'}
            </div>
            <div class="product-info">
                <div class="product-name">${escapeHtml(product.product_name || 'Unknown')}</div>
                <div class="product-price">Rp ${formatNumber(product.price)}</div>
                <div class="product-commission"><i class="fas fa-percent"></i> Komisi: ${commissionPercent}%</div>
            </div>
            <div class="select-badge ${isSelected ? 'selected' : ''}">
                ${isSelected ? '<i class="fas fa-check"></i>' : '<i class="fas fa-plus"></i>'}
            </div>
        `;
        
        card.addEventListener('click', () => {
            const index = selectedProducts.findIndex(p => p.product_id === product.product_id);
            if (index !== -1) {
                selectedProducts.splice(index, 1);
                card.classList.remove('selected');
                card.querySelector('.select-badge').classList.remove('selected');
                card.querySelector('.select-badge').innerHTML = '<i class="fas fa-plus"></i>';
            } else {
                selectedProducts.push({
                    product_id: product.product_id,
                    product_name: product.product_name,
                    product_image: product.image_url,
                    current_commission: commissionPercent,
                    price: product.price
                });
                card.classList.add('selected');
                card.querySelector('.select-badge').classList.add('selected');
                card.querySelector('.select-badge').innerHTML = '<i class="fas fa-check"></i>';
            }
            
            updateSelectedProductsInfo();
        });
        
        productsGrid.appendChild(card);
    });
}

function updateSelectedProductsInfo() {
    const container = document.getElementById('selectedProductsList');
    if (!container) return;
    
    if (selectedProducts.length === 0) {
        container.innerHTML = '<div style="color:var(--text-muted); padding:20px; text-align:center;">Belum ada produk dipilih</div>';
        return;
    }
    
    let html = '';
    selectedProducts.forEach((product, idx) => {
        html += `
            <div class="selected-product-item">
                <div>
                    <strong>${idx + 1}. ${escapeHtml(product.product_name)}</strong>
                    <div style="font-size:11px; color:#4ade80;">Rp ${formatNumber(product.price)}</div>
                </div>
                <div style="color:#fbbf24;">Komisi saat ini: ${product.current_commission}%</div>
                <button class="remove-product-btn" data-idx="${idx}" style="background:transparent; border:none; color:#ef4444; cursor:pointer;">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        `;
    });
    container.innerHTML = html;
    
    document.querySelectorAll('.remove-product-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const idx = parseInt(btn.getAttribute('data-idx'));
            selectedProducts.splice(idx, 1);
            updateSelectedProductsInfo();
            // Refresh product grid selection
            if (productsGrid) {
                const productCards = productsGrid.querySelectorAll('.product-card');
                productCards.forEach((card, i) => {
                    const isSelected = selectedProducts.some(p => p.product_id === card.getAttribute('data-product-id'));
                    if (isSelected) {
                        card.classList.add('selected');
                    } else {
                        card.classList.remove('selected');
                    }
                });
            }
        });
    });
}

function goToStep(step) {
    currentStep = step;
    
    // Update step cards
    for (let i = 1; i <= 4; i++) {
        const card = document.getElementById(`step${i}Card`);
        if (i < step) {
            card.classList.add('completed');
            card.classList.remove('active');
        } else if (i === step) {
            card.classList.add('active');
            card.classList.remove('completed');
        } else {
            card.classList.remove('active', 'completed');
        }
    }
    
    // Show/hide content
    step1Content.style.display = step === 1 ? 'block' : 'none';
    step2Content.style.display = step === 2 ? 'block' : 'none';
    step3Content.style.display = step === 3 ? 'block' : 'none';
    step4Content.style.display = step === 4 ? 'block' : 'none';
    
    // Update step 3 info if coming from step 2
    if (step === 3 && selectedCreator && selectedProducts.length > 0) {
        updateStep3Info();
    }
    
    // Update step 4 summary
    if (step === 4) {
        updateStep4Summary();
    }
}

function updateStep3Info() {
    // Creator info
    document.getElementById('selectedCreatorAvatar').innerHTML = selectedCreatorAvatar 
        ? `<img src="${escapeHtml(selectedCreatorAvatar)}" style="width:100%; height:100%; object-fit:cover;">`
        : '<i class="fas fa-user fa-2x" style="color:#8b5cf6;"></i>';
    document.getElementById('selectedCreatorName').innerText = selectedCreator.nickname || selectedCreator.username;
    document.getElementById('selectedCreatorUsername').innerText = '@' + selectedCreator.username;
    
    // Products
    updateSelectedProductsInfo();
    
    // Commission
    document.getElementById('currentCommission').innerText = currentCreatorCommission + '%';
    
    // Validate commission input
    const commissionInput = document.getElementById('requestedCommission');
    commissionInput.min = currentCreatorCommission + 1;
    commissionInput.value = currentCreatorCommission + 1;
    
    commissionInput.addEventListener('change', () => {
        let val = parseFloat(commissionInput.value);
        if (val < currentCreatorCommission + 1) {
            commissionInput.value = currentCreatorCommission + 1;
            showToastGlobal(`Request komisi minimal ${currentCreatorCommission + 1}%`, 'warning');
        }
        if (val > 50) {
            commissionInput.value = 50;
            showToastGlobal('Komisi maksimal 50%', 'warning');
        }
    });
    
    // Enable next button if products selected
    const nextBtn = document.querySelector('#step3Content .btn-submit');
    if (nextBtn) {
        nextBtn.disabled = selectedProducts.length === 0;
    }
}

function updateStep4Summary() {
    const requestedCommission = parseFloat(document.getElementById('requestedCommission')?.value || 0);
    const campaignName = campaignSelect.options[campaignSelect.selectedIndex]?.text || '-';
    
    document.getElementById('summaryRequestCode').innerText = 'TP-' + new Date().toISOString().slice(0,10).replace(/-/g,'') + '-' + Math.random().toString(36).substring(2, 8).toUpperCase();
    document.getElementById('summaryCreator').innerText = '@' + (selectedCreator?.username || '-');
    document.getElementById('summaryCampaign').innerText = campaignName;
    document.getElementById('summaryProductCount').innerText = selectedProducts.length;
    document.getElementById('summaryCurrentCommission').innerText = currentCreatorCommission + '%';
    document.getElementById('summaryRequestedCommission').innerText = requestedCommission + '%';
}

// Step 2: Next button
function addStep2NextButton() {
    const nextBtn = document.createElement('button');
    nextBtn.className = 'btn-submit';
    nextBtn.innerHTML = '<i class="fas fa-arrow-right"></i> Lanjut ke Penentuan Komisi';
    nextBtn.style.marginTop = '20px';
    nextBtn.onclick = () => {
        if (selectedProducts.length === 0) {
            showToastGlobal('Pilih minimal 1 produk', 'error');
            return;
        }
        goToStep(3);
    };
    
    const step2Container = document.getElementById('step2Content');
    const existingBtn = step2Container.querySelector('.btn-submit');
    if (existingBtn) existingBtn.remove();
    step2Container.appendChild(nextBtn);
}

// Step 3: Next button
function addStep3NextButton() {
    const nextBtn = document.createElement('button');
    nextBtn.className = 'btn-submit';
    nextBtn.innerHTML = '<i class="fas fa-arrow-right"></i> Lanjut ke Ringkasan';
    nextBtn.style.marginTop = '20px';
    nextBtn.onclick = () => {
        const requestedCommission = parseFloat(document.getElementById('requestedCommission').value);
        if (isNaN(requestedCommission) || requestedCommission < currentCreatorCommission + 1) {
            showToastGlobal(`Request komisi minimal ${currentCreatorCommission + 1}%`, 'error');
            return;
        }
        if (selectedProducts.length === 0) {
            showToastGlobal('Pilih minimal 1 produk', 'error');
            return;
        }
        goToStep(4);
    };
    
    const step3Container = document.getElementById('step3Content');
    const existingBtn = step3Container.querySelector('.btn-submit');
    if (existingBtn) existingBtn.remove();
    step3Container.appendChild(nextBtn);
}

// Submit request
document.getElementById('submitRequestBtn')?.addEventListener('click', async () => {
    const requestedCommission = parseFloat(document.getElementById('requestedCommission').value);
    const reason = document.getElementById('requestReason').value;
    const campaignName = campaignSelect.options[campaignSelect.selectedIndex]?.text;
    
    const btn = document.getElementById('submitRequestBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Submitting...';
    
    try {
        const response = await fetch(baseUrlIS + 'is/submit_target_request', {
            method: 'POST',
            body: new URLSearchParams({
                creator_id: selectedCreatorId,
                creator_username: selectedCreator.username,
                creator_name: selectedCreator.nickname || selectedCreator.username,
                creator_phone: selectedCreatorPhone,
                campaign_id: selectedCampaignId,
                campaign_name: campaignName,
                products: JSON.stringify(selectedProducts),
                current_commission: currentCreatorCommission,
                requested_commission: requestedCommission,
                reason: reason
            })
        });
        const result = await response.json();
        
        if (result.success) {
            showToastGlobal('✅ Request berhasil dikirim ke Leader IS!', 'success');
            setTimeout(() => {
                window.location.href = baseUrlIS + 'is/target_plan_requests';
            }, 2000);
        } else {
            showToastGlobal(result.message || 'Gagal submit request', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Request ke Leader IS';
        }
    } catch (error) {
        showToastGlobal('Error: ' + error.message, 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Request ke Leader IS';
    }
});

// Initialize
addStep2NextButton();
addStep3NextButton();

// Step card clicks
document.getElementById('step1Card')?.addEventListener('click', () => goToStep(1));
document.getElementById('step2Card')?.addEventListener('click', () => {
    if (selectedCreator) goToStep(2);
    else showToastGlobal('Pilih creator terlebih dahulu', 'warning');
});
document.getElementById('step3Card')?.addEventListener('click', () => {
    if (selectedCreator && selectedProducts.length > 0) goToStep(3);
    else showToastGlobal('Selesaikan step 1 dan 2 terlebih dahulu', 'warning');
});
document.getElementById('step4Card')?.addEventListener('click', () => {
    if (selectedCreator && selectedProducts.length > 0 && document.getElementById('requestedCommission').value) {
        goToStep(4);
    } else {
        showToastGlobal('Selesaikan step sebelumnya terlebih dahulu', 'warning');
    }
});
</script>