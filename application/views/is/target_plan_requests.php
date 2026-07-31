<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
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
        grid-template-columns: repeat(4, 1fr);
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
    
    /* Action Buttons */
    .action-buttons-target {
        display: flex;
        gap: 16px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    
    .btn-create-request {
        background: linear-gradient(135deg, var(--purple), var(--blue));
        color: white;
        padding: 12px 24px;
        border-radius: 40px;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }
    
    .btn-create-request:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(139,92,246,0.4);
        color: white;
    }
    
    /* Tabs inside page */
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
    
    .tab-target:hover:not(.active) {
        background: rgba(139, 92, 246, 0.1);
        color: var(--purple);
    }
    
    /* Request Cards */
    .requests-container-target {
        min-height: 400px;
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
    
    .status-pending { background: rgba(245,158,11,0.15); color: #f59e0b; }
    .status-is_approved { background: rgba(74,222,128,0.15); color: #4ade80; }
    .status-is_rejected { background: rgba(239,68,68,0.15); color: #ef4444; }
    .status-bd_approved { background: rgba(139,92,246,0.15); color: #8b5cf6; }
    .status-bd_rejected { background: rgba(239,68,68,0.15); color: #ef4444; }
    .status-completed { background: rgba(16,185,129,0.15); color: #10b981; }
    
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
    
    .reject-reason-box {
        margin-top: 12px;
        padding: 10px 12px;
        background: rgba(239,68,68,0.08);
        border-radius: 8px;
        font-size: 11px;
        border-left: 3px solid #ef4444;
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
    
    .btn-send-wa {
        width: 100%;
        background: #25D366;
        color: white;
        border: none;
        padding: 10px;
        border-radius: 40px;
        cursor: pointer;
        font-weight: 600;
        margin-top: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.3s ease;
    }
    
    .btn-send-wa:hover {
        transform: translateY(-2px);
        opacity: 0.9;
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
    
    @media (max-width: 768px) {
        .stats-grid-target {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .request-body-target {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="target-plan-dashboard">
    <div class="dashboard-header-target">
        <div>
            <h1 class="page-title-target"><i class="fas fa-bullseye"></i> Target Plan</h1>
            <p class="sub" style="margin-top: 4px;">Ajukan dan kelola request komisi lebih tinggi untuk creator</p>
        </div>
        <a href="<?= base_url('is/target_plan') ?>" class="btn-create-request">
            <i class="fas fa-plus"></i> Buat Request Baru
        </a>
    </div>
    
    <!-- Stats Cards -->
    <div class="stats-grid-target" id="statsContainer">
        <div class="stat-card-target">
            <div class="stat-icon-target"><i class="fas fa-clock"></i></div>
            <div class="stat-value-target" id="statPending">0</div>
            <div class="stat-label-target">Menunggu Approve IS</div>
        </div>
        <div class="stat-card-target">
            <div class="stat-icon-target"><i class="fas fa-check-circle" style="color: #4ade80;"></i></div>
            <div class="stat-value-target" id="statIsApproved">0</div>
            <div class="stat-label-target">Disetujui IS</div>
        </div>
        <div class="stat-card-target">
            <div class="stat-icon-target"><i class="fas fa-gem" style="color: #8b5cf6;"></i></div>
            <div class="stat-value-target" id="statBdApproved">0</div>
            <div class="stat-label-target">Disetujui BD</div>
        </div>
        <div class="stat-card-target">
            <div class="stat-icon-target"><i class="fas fa-check-double" style="color: #10b981;"></i></div>
            <div class="stat-value-target" id="statCompleted">0</div>
            <div class="stat-label-target">Selesai</div>
        </div>
    </div>
    
    <!-- Tabs -->
    <div class="tabs-target">
        <button class="tab-target active" data-tab="all">Semua Request</button>
        <button class="tab-target" data-tab="my_requests">Request Saya</button>
    </div>
    
    <!-- Filter Status -->
    <div style="display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap;">
        <button class="filter-btn active" data-filter="all" style="background: transparent; border: none; padding: 6px 16px; border-radius: 40px; font-size: 12px; color: var(--text-muted); cursor: pointer; transition: all 0.2s;">
            Semua
        </button>
        <button class="filter-btn" data-filter="PENDING" style="background: transparent; border: none; padding: 6px 16px; border-radius: 40px; font-size: 12px; color: var(--text-muted); cursor: pointer; transition: all 0.2s;">
            ⏳ Pending
        </button>
        <button class="filter-btn" data-filter="IS_APPROVED" style="background: transparent; border: none; padding: 6px 16px; border-radius: 40px; font-size: 12px; color: var(--text-muted); cursor: pointer; transition: all 0.2s;">
            ✅ Disetujui IS
        </button>
        <button class="filter-btn" data-filter="IS_REJECTED" style="background: transparent; border: none; padding: 6px 16px; border-radius: 40px; font-size: 12px; color: var(--text-muted); cursor: pointer; transition: all 0.2s;">
            ❌ Ditolak IS
        </button>
        <button class="filter-btn" data-filter="BD_APPROVED" style="background: transparent; border: none; padding: 6px 16px; border-radius: 40px; font-size: 12px; color: var(--text-muted); cursor: pointer; transition: all 0.2s;">
            ✅ Disetujui BD
        </button>
        <button class="filter-btn" data-filter="BD_REJECTED" style="background: transparent; border: none; padding: 6px 16px; border-radius: 40px; font-size: 12px; color: var(--text-muted); cursor: pointer; transition: all 0.2s;">
            ❌ Ditolak BD
        </button>
        <button class="filter-btn" data-filter="COMPLETED" style="background: transparent; border: none; padding: 6px 16px; border-radius: 40px; font-size: 12px; color: var(--text-muted); cursor: pointer; transition: all 0.2s;">
            🎉 Selesai
        </button>
    </div>
    
    <!-- Requests List -->
    <div id="requestsListTarget" class="requests-container-target">
        <div class="empty-state-target">
            <i class="fas fa-spinner fa-pulse fa-2x"></i>
            <p>Loading target plan requests...</p>
        </div>
    </div>
</div>

<script>
const baseUrlIS = '<?= base_url() ?>';
// ========== TARGET PLAN DASHBOARD ==========
let currentTab = 'all';
let currentFilter = 'all';

async function loadTargetPlanStats() {
    try {
        const response = await fetch(baseUrlIS + 'is/get_target_requests', {
            method: 'POST',
            body: new URLSearchParams({ status: 'all' })
        });
        const result = await response.json();
        
        if (result.success) {
            const requests = result.requests;
            const stats = {
                PENDING: requests.filter(r => r.status === 'PENDING').length,
                IS_APPROVED: requests.filter(r => r.status === 'IS_APPROVED').length,
                BD_APPROVED: requests.filter(r => r.status === 'BD_APPROVED').length,
                COMPLETED: requests.filter(r => r.status === 'COMPLETED').length
            };
            
            document.getElementById('statPending').innerText = stats.PENDING;
            document.getElementById('statIsApproved').innerText = stats.IS_APPROVED;
            document.getElementById('statBdApproved').innerText = stats.BD_APPROVED;
            document.getElementById('statCompleted').innerText = stats.COMPLETED;
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

async function loadTargetPlanRequests() {
    const container = document.getElementById('requestsListTarget');
    container.innerHTML = '<div class="empty-state-target"><i class="fas fa-spinner fa-pulse fa-2x"></i><p>Loading target plan requests...</p></div>';
    
    try {
        let url = baseUrlIS + 'is/get_target_requests';
        let body = new URLSearchParams({ status: currentFilter });
        
        if (currentTab === 'my_requests') {
            body.append('my_only', 'true');
        }
        
        const response = await fetch(url, {
            method: 'POST',
            body: body
        });
        const result = await response.json();
        
        if (result.success && result.requests.length > 0) {
            renderRequestsList(result.requests);
        } else {
            container.innerHTML = `
                <div class="empty-state-target">
                    <i class="fas fa-inbox"></i>
                    <p>Belum ada target plan request</p>
                    <p style="font-size: 11px; margin-top: 8px;">Klik "Buat Request Baru" untuk mengajukan request komisi lebih tinggi</p>
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
        const statusClass = getStatusClass(req.status);
        const statusText = getStatusText(req.status);
        
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
                </div>
                <div class="info-group-target">
                    <label><i class="fas fa-bullhorn"></i> Campaign</label>
                    <value>${escapeHtml(req.campaign_name || req.campaign_id)}</value>
                </div>
                <div class="info-group-target">
                    <label><i class="fas fa-percent"></i> Komisi</label>
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
            
            ${req.reject_reason ? `
            <div class="reject-reason-box">
                <i class="fas fa-exclamation-triangle"></i> <strong>Alasan Ditolak:</strong> ${escapeHtml(req.reject_reason)}
                <div style="font-size: 10px; margin-top: 4px;">Ditolak oleh: ${escapeHtml(req.rejected_by)}</div>
            </div>
            ` : ''}
            
            ${req.approved_by_is ? `
            <div style="margin-top: 8px; font-size: 10px; color: #4ade80;">
                <i class="fas fa-check-circle"></i> Disetujui IS: ${escapeHtml(req.approved_by_is)} (${new Date(req.approved_at_is).toLocaleString('id-ID')})
            </div>
            ` : ''}
            
            ${req.approved_by_bd ? `
            <div style="margin-top: 4px; font-size: 10px; color: #8b5cf6;">
                <i class="fas fa-check-circle"></i> Disetujui BD: ${escapeHtml(req.approved_by_bd)} (${new Date(req.approved_at_bd).toLocaleString('id-ID')})
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
            
            ${req.status === 'BD_APPROVED' && !req.is_sent ? `
            <button class="btn-send-wa" data-request-id="${req.id}" 
                    data-creator-name="${escapeHtml(req.creator_username)}"
                    data-creator-phone="${escapeHtml(req.creator_phone || '')}"
                    data-campaign="${escapeHtml(req.campaign_name)}"
                    data-commission="${req.requested_commission}"
                    data-links='${JSON.stringify(generatedLinks)}'>
                <i class="fab fa-whatsapp"></i> Kirim Link ke Creator
            </button>
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
    
    // Attach send WA events
    document.querySelectorAll('.btn-send-wa').forEach(btn => {
        btn.addEventListener('click', async () => {
            const requestId = btn.getAttribute('data-request-id');
            const creatorName = btn.getAttribute('data-creator-name');
            const creatorPhone = btn.getAttribute('data-creator-phone');
            const campaignName = btn.getAttribute('data-campaign');
            const commission = btn.getAttribute('data-commission');
            const generatedLinks = JSON.parse(btn.getAttribute('data-links'));
            
            if (generatedLinks.length === 0) {
                showToastGlobal('Belum ada link yang digenerate', 'error');
                return;
            }
            
            let message = `Halo @${creatorName}! 👋\n\n`;
            message += `Target Plan request Anda telah disetujui! Berikut adalah link afiliasi dengan komisi khusus:\n\n`;
            message += `📢 *Campaign:* ${campaignName}\n\n`;
            message += `*🔗 Link Afiliasi (Komisi ${commission}%):*\n`;
            
            generatedLinks.forEach((link, idx) => {
                message += `${idx + 1}. *${link.product_name}*\n`;
                message += `   ${link.affiliate_link}\n\n`;
            });
            
            message += `\n✨ Selamat berpromosi! 🚀\n\nBest regards,\nToopai Team 💜`;
            
            if (creatorPhone && creatorPhone !== '-') {
                let phone = creatorPhone.replace(/[^0-9+]/g, '');
                if (phone.startsWith('0')) phone = '62' + phone.substring(1);
                else if (phone.startsWith('+')) phone = phone.substring(1);
                
                window.open(`https://wa.me/${phone}?text=${encodeURIComponent(message)}`, '_blank');
                
                await fetch(baseUrlIS + 'is/mark_target_request_sent', {
                    method: 'POST',
                    body: new URLSearchParams({ request_id: requestId })
                });
                
                showToastGlobal('✅ Link dikirim!', 'success');
                setTimeout(() => {
                    loadTargetPlanStats();
                    loadTargetPlanRequests();
                }, 1000);
            } else {
                showToastGlobal('❌ Creator tidak memiliki nomor WhatsApp!', 'error');
            }
        });
    });
}

function getStatusClass(status) {
    const classes = {
        'PENDING': 'status-pending',
        'IS_APPROVED': 'status-is_approved',
        'IS_REJECTED': 'status-is_rejected',
        'BD_APPROVED': 'status-bd_approved',
        'BD_REJECTED': 'status-bd_rejected',
        'COMPLETED': 'status-completed'
    };
    return classes[status] || 'status-pending';
}

function getStatusText(status) {
    const texts = {
        'PENDING': '⏳ Menunggu Approve IS',
        'IS_APPROVED': '✅ Disetujui IS',
        'IS_REJECTED': '❌ Ditolak IS',
        'BD_APPROVED': '✅ Disetujui BD',
        'BD_REJECTED': '❌ Ditolak BD',
        'COMPLETED': '🎉 Selesai'
    };
    return texts[status] || status;
}

// Tab clicks
document.querySelectorAll('.tab-target').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.tab-target').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        currentTab = tab.getAttribute('data-tab');
        loadTargetPlanRequests();
    });
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