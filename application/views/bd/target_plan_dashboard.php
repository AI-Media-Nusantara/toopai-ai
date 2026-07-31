<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
    /* Gunakan style yang sama seperti di IS */
    .target-plan-dashboard {
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .dashboard-header-target {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }
    
    .page-title-target {
        font-size: 24px;
        font-weight: 700;
        background: linear-gradient(135deg, var(--purple), var(--cyan));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }
    
    /* Stats Cards */
    .stats-grid-target {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }
    
    .stat-card-target {
        background: var(--bg-card);
        border-radius: 16px;
        padding: 20px;
        border: 1px solid var(--border);
        transition: all 0.3s ease;
    }
    
    .stat-card-target:hover {
        transform: translateY(-2px);
        border-color: var(--purple);
    }
    
    .stat-icon-target {
        width: 48px;
        height: 48px;
        background: rgba(139,92,246,0.15);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
    }
    
    .stat-icon-target i {
        font-size: 24px;
        color: var(--purple);
    }
    
    .stat-value-target {
        font-size: 28px;
        font-weight: 700;
        color: var(--text-primary);
    }
    
    .stat-label-target {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 4px;
    }
    
    .tabs-target {
        display: flex;
        gap: 8px;
        margin-bottom: 20px;
        border-bottom: 1px solid var(--border);
        padding-bottom: 12px;
        flex-wrap: wrap;
    }
    
    .tab-target {
        background: transparent;
        border: none;
        padding: 8px 20px;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-muted);
        cursor: pointer;
        border-radius: 40px;
        transition: all 0.2s;
    }
    
    .tab-target.active {
        background: linear-gradient(135deg, var(--purple-glow), rgba(59, 130, 246, 0.1));
        color: var(--purple);
        border: 1px solid rgba(139, 92, 246, 0.4);
    }
    
    .filter-btn {
        background: transparent;
        border: none;
        padding: 6px 16px;
        border-radius: 40px;
        font-size: 12px;
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .filter-btn.active {
        background: linear-gradient(135deg, var(--purple-glow), rgba(59, 130, 246, 0.1));
        color: var(--purple);
        border: 1px solid rgba(139, 92, 246, 0.4);
    }
    
    .request-card-target {
        background: var(--bg-card);
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 16px;
        border: 1px solid var(--border);
        transition: all 0.2s ease;
    }
    
    .request-card-target:hover {
        border-color: var(--purple);
        transform: translateX(3px);
    }
    
    .request-header-target {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--border);
    }
    
    .request-code-target {
        font-family: monospace;
        font-size: 14px;
        font-weight: 600;
        color: #4ade80;
    }
    
    .status-badge-target {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    
    .status-is_approved { background: rgba(139,92,246,0.15); color: #8b5cf6; }
    .status-bd_approved { background: rgba(74,222,128,0.15); color: #4ade80; }
    .status-bd_rejected { background: rgba(239,68,68,0.15); color: #ef4444; }
    
    .request-body-target {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 16px;
    }
    
    .info-group-target {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    
    .info-group-target label {
        font-size: 10px;
        color: var(--text-muted);
    }
    
    .info-group-target value {
        font-size: 13px;
        font-weight: 500;
    }
    
    .products-list-target {
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid var(--border);
    }
    
    .product-tag-target {
        display: inline-block;
        background: var(--bg-elevated);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        margin: 4px 8px 4px 0;
    }
    
    .reason-box {
        margin-top: 12px;
        padding: 10px 12px;
        background: rgba(245,158,11,0.08);
        border-radius: 8px;
        font-size: 11px;
        border-left: 3px solid #f59e0b;
    }
    
    .generated-links-target {
        margin-top: 16px;
        background: #0a0e1a;
        border-radius: 12px;
        padding: 12px;
    }
    
    .link-item-target {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        border-bottom: 1px solid #2a3346;
        font-size: 11px;
    }
    
    .link-item-target:last-child {
        border-bottom: none;
    }
    
    .copy-link-target {
        background: #1e293b;
        border: 1px solid #4ade80;
        color: #4ade80;
        padding: 4px 12px;
        border-radius: 20px;
        cursor: pointer;
        font-size: 10px;
    }
    
    .action-buttons-target {
        display: flex;
        gap: 12px;
        margin-top: 16px;
        justify-content: flex-end;
    }
    
    .btn-approve-target {
        background: #4ade80;
        color: #0a0e17;
        border: none;
        padding: 8px 20px;
        border-radius: 40px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-approve-target:hover {
        transform: translateY(-2px);
    }
    
    .btn-reject-target {
        background: transparent;
        border: 1px solid #ef4444;
        color: #ef4444;
        padding: 8px 20px;
        border-radius: 40px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-reject-target:hover {
        background: rgba(239,68,68,0.1);
    }
    
    .reject-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        visibility: hidden;
        opacity: 0;
        transition: 0.2s;
    }
    
    .reject-modal.active {
        visibility: visible;
        opacity: 1;
    }
    
    .empty-state-target {
        text-align: center;
        padding: 60px;
        color: var(--text-muted);
    }
    
    .empty-state-target i {
        font-size: 48px;
        margin-bottom: 16px;
        display: block;
        opacity: 0.5;
    }
</style>

<div class="target-plan-dashboard">
    <div class="dashboard-header-target">
        <div>
            <h1 class="page-title-target"><i class="fas fa-bullseye"></i> Target Plan (BD)</h1>
            <p class="sub" style="margin-top: 4px;">Approve request komisi dari IS dan generate link afiliasi</p>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="stats-grid-target" id="statsContainer">
        <div class="stat-card-target">
            <div class="stat-icon-target"><i class="fas fa-clock"></i></div>
            <div class="stat-value-target" id="statPending">0</div>
            <div class="stat-label-target">Menunggu Approve</div>
        </div>
        <div class="stat-card-target">
            <div class="stat-icon-target"><i class="fas fa-check-circle" style="color: #4ade80;"></i></div>
            <div class="stat-value-target" id="statApproved">0</div>
            <div class="stat-label-target">Disetujui</div>
        </div>
        <div class="stat-card-target">
            <div class="stat-icon-target"><i class="fas fa-times-circle" style="color: #ef4444;"></i></div>
            <div class="stat-value-target" id="statRejected">0</div>
            <div class="stat-label-target">Ditolak</div>
        </div>
    </div>
    
    <!-- Filter Status -->
    <div style="display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap;">
        <button class="filter-btn active" data-filter="all">Semua</button>
        <button class="filter-btn" data-filter="IS_APPROVED">⏳ Menunggu Approve</button>
        <button class="filter-btn" data-filter="BD_APPROVED">✅ Disetujui</button>
        <button class="filter-btn" data-filter="BD_REJECTED">❌ Ditolak</button>
    </div>
    
    <!-- Requests List -->
    <div id="requestsListTarget" class="requests-container-target">
        <div class="empty-state-target">
            <i class="fas fa-spinner fa-pulse fa-2x"></i>
            <p>Loading target plan requests...</p>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="reject-modal">
    <div class="modal-glass-dashboard" style="max-width: 450px;">
        <div class="modal-header-dashboard">
            <h3><i class="fas fa-times-circle"></i> Tolak Request</h3>
            <span class="modal-close-dashboard" onclick="closeRejectModal()">&times;</span>
        </div>
        <div class="modal-body">
            <label>Alasan Penolakan</label>
            <textarea id="rejectReason" rows="4" style="width:100%; padding:12px; background:#0f1420; border:1px solid #2a3346; border-radius:12px; color:#e2f0e8;" placeholder="Masukkan alasan penolakan..."></textarea>
            <div class="flex-buttons" style="margin-top:20px;">
                <button id="confirmRejectBtn" style="background:#ef4444; color:white;">Konfirmasi Tolak</button>
                <button onclick="closeRejectModal()" style="background:#1e293b;">Batal</button>
            </div>
        </div>
    </div>
</div>

<script>
const baseUrlBD = '<?= base_url() ?>';
let currentRejectId = null;
let currentFilter = 'all';

async function loadTargetPlanStats() {
    try {
        const response = await fetch(baseUrlBD + 'bd/get_target_requests_bd', {
            method: 'POST',
            body: new URLSearchParams({ status: 'all' })
        });
        const result = await response.json();
        
        if (result.success) {
            const requests = result.requests;
            const stats = {
                pending: requests.filter(r => r.status === 'IS_APPROVED').length,
                approved: requests.filter(r => r.status === 'BD_APPROVED').length,
                rejected: requests.filter(r => r.status === 'BD_REJECTED').length
            };
            
            document.getElementById('statPending').innerText = stats.pending;
            document.getElementById('statApproved').innerText = stats.approved;
            document.getElementById('statRejected').innerText = stats.rejected;
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

async function loadTargetPlanRequests() {
    const container = document.getElementById('requestsListTarget');
    container.innerHTML = '<div class="empty-state-target"><i class="fas fa-spinner fa-pulse fa-2x"></i><p>Loading target plan requests...</p></div>';
    
    try {
        const response = await fetch(baseUrlBD + 'bd/get_target_requests_bd', {
            method: 'POST',
            body: new URLSearchParams({ status: currentFilter })
        });
        const result = await response.json();
        
        if (result.success && result.requests.length > 0) {
            renderRequestsList(result.requests);
        } else {
            container.innerHTML = `
                <div class="empty-state-target">
                    <i class="fas fa-inbox"></i>
                    <p>Belum ada target plan request</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error:', error);
        container.innerHTML = `
            <div class="empty-state-target" style="color: #ef4444;">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Error: ${error.message}</p>
            </div>
        `;
    }
}

function renderRequestsList(requests) {
    const container = document.getElementById('requestsListTarget');
    container.innerHTML = '';
    
    requests.forEach(req => {
        const products = req.products || [];
        const generatedLinks = req.generated_links || [];
        const statusClass = req.status === 'IS_APPROVED' ? 'status-is_approved' : (req.status === 'BD_APPROVED' ? 'status-bd_approved' : 'status-bd_rejected');
        const statusText = req.status === 'IS_APPROVED' ? '⏳ Menunggu Approve BD' : (req.status === 'BD_APPROVED' ? '✅ Disetujui BD' : '❌ Ditolak BD');
        
        const card = document.createElement('div');
        card.className = 'request-card-target';
        card.innerHTML = `
            <div class="request-header-target">
                <div>
                    <span class="request-code-target">${escapeHtml(req.request_code)}</span>
                    <div style="font-size: 10px; color: var(--text-muted); margin-top: 4px;">${new Date(req.created_at).toLocaleString('id-ID')}</div>
                </div>
                <span class="status-badge-target ${statusClass}">${statusText}</span>
            </div>
            
            <div class="request-body-target">
                <div class="info-group-target">
                    <label><i class="fab fa-tiktok"></i> Creator</label>
                    <value>@${escapeHtml(req.creator_username)}</value>
                    <value style="font-size: 11px;">${escapeHtml(req.creator_name || '')}</value>
                    <value style="font-size: 10px;">📞 ${escapeHtml(req.creator_phone || '-')}</value>
                </div>
                <div class="info-group-target">
                    <label><i class="fas fa-bullhorn"></i> Campaign</label>
                    <value>${escapeHtml(req.campaign_name || req.campaign_id)}</value>
                </div>
                <div class="info-group-target">
                    <label><i class="fas fa-percent"></i> Request Komisi</label>
                    <value style="color: #fbbf24; font-weight: 600;">${req.requested_commission}%</value>
                    <label style="font-size: 9px;">Dari: ${req.current_commission || 6}%</label>
                </div>
                <div class="info-group-target">
                    <label><i class="fas fa-box"></i> Produk</label>
                    <value>${products.length} produk</value>
                </div>
            </div>
            
            <div class="products-list-target">
                <label style="font-size: 10px; color: var(--text-muted);">Produk yang diminta:</label>
                <div>
                    ${products.map(p => `<span class="product-tag-target">${escapeHtml(p.product_name).substring(0, 45)}${p.product_name.length > 45 ? '...' : ''}</span>`).join('')}
                </div>
            </div>
            
            ${req.reason ? `
            <div class="reason-box">
                <i class="fas fa-comment"></i> <strong>Alasan:</strong> ${escapeHtml(req.reason)}
            </div>
            ` : ''}
            
            ${req.approved_by_is ? `
            <div style="margin-top: 8px; font-size: 10px; color: #4ade80;">
                <i class="fas fa-check-circle"></i> Disetujui IS: ${escapeHtml(req.approved_by_is)} (${new Date(req.approved_at_is).toLocaleString('id-ID')})
            </div>
            ` : ''}
            
            ${generatedLinks.length > 0 ? `
            <div class="generated-links-target">
                <label style="font-size: 11px; color: #4ade80;"><i class="fas fa-link"></i> Generated Links:</label>
                ${generatedLinks.map(link => `
                    <div class="link-item-target">
                        <div style="flex: 1;">
                            <div>${escapeHtml(link.product_name)}</div>
                            <div style="font-size: 9px; color: #fbbf24;">Komisi: ${link.commission_rate}%</div>
                            <div style="font-size: 9px; color: #8b5cf6; word-break: break-all;">${escapeHtml(link.affiliate_link)}</div>
                        </div>
                        <button class="copy-link-target" data-link="${escapeHtml(link.affiliate_link)}" data-product="${escapeHtml(link.product_name)}">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                `).join('')}
            </div>
            ` : ''}
            
            ${req.status === 'IS_APPROVED' ? `
            <div class="action-buttons-target">
                <button class="btn-approve-target" data-id="${req.id}" data-commission="${req.requested_commission}" data-campaign="${req.campaign_id}">
                    <i class="fas fa-check"></i> Approve & Generate Link
                </button>
                <button class="btn-reject-target" data-id="${req.id}">
                    <i class="fas fa-times"></i> Reject
                </button>
            </div>
            ` : ''}
        `;
        
        container.appendChild(card);
    });
    
    // Attach copy link events
    document.querySelectorAll('.copy-link-target').forEach(btn => {
        btn.addEventListener('click', () => {
            const link = btn.getAttribute('data-link');
            const product = btn.getAttribute('data-product');
            navigator.clipboard.writeText(link);
            showToastGlobal(`Link untuk "${product}" dicopy!`, 'success');
        });
    });
    
    // Attach approve events
    document.querySelectorAll('.btn-approve-target').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.getAttribute('data-id');
            const commission = btn.getAttribute('data-commission');
            
            if (confirm(`Approve request ini dengan komisi ${commission}%?`)) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Processing...';
                
                const response = await fetch(baseUrlBD + 'bd/approve_target_request_bd', {
                    method: 'POST',
                    body: new URLSearchParams({ request_id: id })
                });
                const result = await response.json();
                
                if (result.success) {
                    showToastGlobal('✅ Request approved! Links generated.', 'success');
                    loadTargetPlanStats();
                    loadTargetPlanRequests();
                } else {
                    showToastGlobal(result.message || 'Failed', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check"></i> Approve & Generate Link';
                }
            }
        });
    });
    
    // Attach reject events
    document.querySelectorAll('.btn-reject-target').forEach(btn => {
        btn.addEventListener('click', () => {
            currentRejectId = btn.getAttribute('data-id');
            document.getElementById('rejectModal').classList.add('active');
        });
    });
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.remove('active');
    currentRejectId = null;
    document.getElementById('rejectReason').value = '';
}

document.getElementById('confirmRejectBtn')?.addEventListener('click', async () => {
    const reason = document.getElementById('rejectReason').value.trim();
    if (!reason) {
        showToastGlobal('Alasan penolakan harus diisi!', 'error');
        return;
    }
    
    const response = await fetch(baseUrlBD + 'bd/reject_target_request_bd', {
        method: 'POST',
        body: new URLSearchParams({
            request_id: currentRejectId,
            reject_reason: reason
        })
    });
    const result = await response.json();
    
    if (result.success) {
        showToastGlobal('Request ditolak', 'warning');
        closeRejectModal();
        loadTargetPlanStats();
        loadTargetPlanRequests();
    } else {
        showToastGlobal(result.message || 'Gagal', 'error');
    }
});

// Filter clicks
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentFilter = btn.getAttribute('data-filter');
        loadTargetPlanRequests();
    });
});

// Initial load
loadTargetPlanStats();
loadTargetPlanRequests();
</script>