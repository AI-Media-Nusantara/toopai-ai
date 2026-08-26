<!-- file: application/views/is/dashboard.php -->
<style>
    /* ============================================================ */
    /* CSS DARI FILE LAMA (YANG HILANG) */
    /* ============================================================ */
    
    /* Dashboard IS styles - konsisten dengan tema ungu + biru + hitam */
    .dashboard-header {
        background: linear-gradient(135deg, #0f0f1a 0%, #13111f 100%);
        border-radius: 20px;
        padding: 16px 20px;
        margin-bottom: 20px;
        border: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }
    
    .dashboard-title h1 { 
        font-size: 20px; 
        font-weight: 700; 
        background: linear-gradient(135deg, var(--purple), var(--cyan), var(--blue));
        -webkit-background-clip: text; 
        background-clip: text; 
        color: transparent;
    }
    
    .dashboard-title .sub { 
        font-size: 10px; 
        color: var(--text-secondary); 
        margin-top: 4px;
    }
    
    /* Desktop Menu */
    .desktop-menu {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .desktop-menu a {
        color: var(--text-muted);
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        padding: 6px 14px;
        border-radius: 40px;
        transition: var(--transition);
    }
    
    .desktop-menu a:hover, .desktop-menu a.active {
        background: var(--purple-glow);
        color: var(--purple);
    }
    
    .stat-cards-dashboard { 
        display: flex; 
        gap: 20px; 
        background: rgba(139, 92, 246, 0.1);
        padding: 6px 16px; 
        border-radius: 60px; 
        border: 1px solid var(--border);
    }
    
    .stat-item-dashboard { text-align: center; padding: 2px 6px; }
    .stat-label-dashboard { font-size: 9px; color: var(--text-muted); }
    .stat-value-dashboard { font-size: 18px; font-weight: 700; background: linear-gradient(135deg, var(--purple), var(--blue)); -webkit-background-clip: text; background-clip: text; color: transparent; }
    
    /* Tabs */
    .tabs-bar-dashboard { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 10px; }
    .tabs-dashboard { display: flex; gap: 6px; flex-wrap: wrap; }
    .tab-btn-dashboard { background: transparent; border: none; padding: 6px 16px; font-size: 12px; font-weight: 600; color: var(--text-muted); cursor: pointer; border-radius: 40px; transition: var(--transition); }
    .tab-btn-dashboard.active { background: linear-gradient(135deg, var(--purple-glow), rgba(59, 130, 246, 0.1)); color: var(--purple); border: 1px solid rgba(139, 92, 246, 0.4); }
    .scout-btn-dashboard { background: linear-gradient(135deg, var(--purple-glow), rgba(6, 182, 212, 0.1)); border: 1px solid var(--purple); padding: 6px 16px; border-radius: 40px; color: var(--purple); font-weight: 600; cursor: pointer; font-size: 12px; transition: var(--transition); }
    .scout-btn-dashboard:hover { background: var(--purple); color: white; }
    
    .tab-content-dashboard { display: none; animation: fadeIn 0.3s ease; }
    .tab-content-dashboard.active { display: block; }
    
    /* Stages Container */
    .stages-scroll-dashboard { overflow-x: auto; overflow-y: visible; margin-bottom: 20px; padding-bottom: 12px; }
    .stages-container-dashboard { display: flex; flex-direction: row; gap: 16px; min-width: min-content; }
    
    /* Warna berbeda untuk setiap stage card */
    .stage-card-dashboard { border-radius: 20px; padding: 16px; width: 340px; flex-shrink: 0; transition: var(--transition); border: 1px solid var(--border); }
    .stage-card-dashboard[data-stage="1"] { background: linear-gradient(135deg, #1a1030, #13111f); border-top: 3px solid var(--purple); }
    .stage-card-dashboard[data-stage="2"] { background: linear-gradient(135deg, #0f1a2e, #13111f); border-top: 3px solid var(--blue); }
    .stage-card-dashboard[data-stage="3"] { background: linear-gradient(135deg, #0a1a1f, #13111f); border-top: 3px solid var(--cyan); }
    .stage-card-dashboard[data-stage="4"] { background: linear-gradient(135deg, #0a1f15, #13111f); border-top: 3px solid var(--green); }
    
    .stage-card-dashboard.completed { opacity: 0.7; }
    .stage-title-dashboard { font-weight: 700; font-size: 14px; margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 10px; flex-wrap: wrap; justify-content: space-between; color: var(--text-primary); }
    .stage-count-dashboard { background: var(--bg-elevated); padding: 2px 8px; border-radius: 40px; font-size: 10px; }
    
    /* Items */
    .stage-item-dashboard { background: var(--bg-elevated); border-radius: 14px; padding: 12px; margin-bottom: 10px; cursor: pointer; transition: var(--transition); border: 1px solid transparent; }
    .stage-item-dashboard:hover { border-color: var(--purple); transform: translateX(3px); }
    .stage-item-dashboard strong { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; color: var(--text-primary); font-size: 13px; }
    .item-details-dashboard { display: flex; flex-wrap: wrap; gap: 10px; font-size: 10px; color: var(--text-secondary); margin-bottom: 6px; }
    .badge-dashboard { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 20px; font-size: 9px; font-weight: 600; }
    .badge-pending { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
    .badge-active { background: rgba(16, 185, 129, 0.15); color: #10b981; }
    .badge-approved { background: rgba(139, 92, 246, 0.15); color: #8b5cf6; }
    
    .task-btn-dashboard { margin-top: 14px; width: 100%; padding: 8px; border-radius: 40px; border: none; background: var(--bg-elevated); color: var(--text-secondary); font-weight: 600; cursor: pointer; transition: var(--transition); font-size: 12px; }
    .task-btn-dashboard:hover:not(:disabled) { background: linear-gradient(135deg, var(--purple), var(--blue)); color: white; }
    .task-btn-dashboard:disabled { opacity: 0.5; cursor: not-allowed; }
    
    /* Product item */
    .product-item-dashboard { background: #0f1420; border-radius: 12px; padding: 10px; margin-top: 8px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #2a3346; }
    .product-info-dashboard { flex: 1; }
    .product-name-dashboard { color: #ffffff; font-size: 12px; font-weight: 500; }
    .product-price-dashboard { color: #4ade80; font-size: 11px; margin-top: 3px; }
    .add-product-btn { background: #1e293b; border: 1px dashed #4ade80; border-radius: 12px; padding: 8px; text-align: center; color: #4ade80; cursor: pointer; margin-top: 8px; font-size: 12px; }
    
    /* Recent Orders */
    .recent-section-dashboard { background: var(--bg-card); border-radius: 20px; padding: 16px; border: 1px solid var(--border); overflow-x: auto; margin-top: 20px; }
    .recent-table-dashboard { width: 100%; min-width: 550px; border-collapse: collapse; }
    .recent-table-dashboard th, .recent-table-dashboard td { padding: 8px 6px; text-align: left; border-bottom: 1px solid var(--border); font-size: 11px; color: var(--text-secondary); }
    .recent-table-dashboard th { color: var(--purple); font-weight: 600; }
    
    /* Brands Grid */
    .brands-grid-dashboard { display: flex; flex-direction: column; gap: 16px; }
    .brand-card-dashboard { background: var(--bg-card); border-radius: 20px; padding: 16px; border: 1px solid var(--border); }
    .brand-item-row-dashboard { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--border); flex-wrap: wrap; gap: 6px; }
    
    /* Modal */
    .modal-overlay-dashboard { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; z-index: 2000; visibility: hidden; opacity: 0; transition: 0.2s; }
    .modal-overlay-dashboard.active { visibility: visible; opacity: 1; }
    .modal-glass-dashboard { background: #111827; border-radius: 28px; width: 95%; max-width: 550px; padding: 24px; border: 1px solid #4ade80; max-height: 85vh; overflow-y: auto; color: #e2f0e8; }
    .modal-header-dashboard { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #2a3346; padding-bottom: 10px; }
    .modal-header-dashboard h3 { color: #e2f0e8; font-size: 18px; }
    .modal-close-dashboard { font-size: 26px; cursor: pointer; color: #9aaebe; }
    .modal-body label { color: #bdf2c0; font-weight: 500; display: block; margin-top: 14px; margin-bottom: 5px; font-size: 13px; }
    .modal-body input, .modal-body select, .modal-body textarea { width: 100%; padding: 10px 12px; background: #0f1420; border: 1px solid #2a3346; border-radius: 14px; color: #e2f0e8; font-size: 13px; }
    .modal-body button { background: #4ade80; color: #0a0e17; border: none; padding: 10px 18px; border-radius: 40px; font-weight: 600; cursor: pointer; transition: 0.2s; font-size: 13px; }
    .modal-body button:hover { background: #22c55e; }
    .flex-buttons { display: flex; gap: 10px; margin-top: 16px; }
    .flex-buttons button { flex: 1; }
    
    /* Leaderboard */
    .leaderboard-card-dashboard { background: var(--bg-card); border-radius: 20px; padding: 20px; border: 1px solid var(--border); }
    .leaderboard-item-dashboard { padding: 12px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; }
    
    /* Mobile Bottom Nav */
    .mobile-bottom-nav-dashboard { display: none; position: fixed; bottom: 0; left: 0; right: 0; background: var(--bg-card); border-top: 1px solid var(--border); padding: 6px 12px; justify-content: space-around; z-index: 100; }
    .mobile-nav-item-dashboard { display: flex; flex-direction: column; align-items: center; gap: 2px; color: var(--text-muted); text-decoration: none; font-size: 9px; padding: 6px; border-radius: 40px; }
    .mobile-nav-item-dashboard i { font-size: 16px; }
    .mobile-nav-item-dashboard.active { color: var(--purple); background: var(--purple-glow); }
    
    .text-center { text-align: center; }
    .mt-2 { margin-top: 8px; }
    .mt-3 { margin-top: 12px; }
    .mb-2 { margin-bottom: 8px; }
    
    @media (min-width: 768px) {
        .mobile-bottom-nav-dashboard { display: none; }
        .desktop-menu { display: flex; }
        .stage-card-dashboard { width: 360px; }
    }
    
    @media (max-width: 767px) {
        .mobile-bottom-nav-dashboard { display: flex; }
        body { padding-bottom: 60px; }
        .dashboard-header { flex-direction: column; text-align: center; padding: 14px; }
        .stat-cards-dashboard { justify-content: center; flex-wrap: wrap; gap: 12px; }
        .desktop-menu { display: none; }
        .stage-card-dashboard { width: 300px; padding: 14px; }
        .modal-glass-dashboard { width: 95%; padding: 18px; }
        .dashboard-title h1 { font-size: 18px; }
    }
    
    /* Tambahkan CSS untuk selected product items */
    .selected-product-item {
        transition: all 0.2s ease;
    }
    .selected-product-item:hover {
        border-color: #4ade80;
    }
    .commission-input {
        transition: all 0.2s ease;
    }
    .commission-input:focus {
        outline: none;
        border-color: #4ade80;
        box-shadow: 0 0 5px rgba(74, 222, 128, 0.3);
    }
    .generate-link-btn {
        transition: all 0.2s ease;
    }
    .generate-link-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(74, 222, 128, 0.3);
    }
    .generated-link-container {
        animation: fadeIn 0.3s ease;
    }
    .copy-link-btn {
        transition: all 0.2s ease;
    }
    .copy-link-btn:hover {
        background: #4ade80 !important;
        color: #0a0e17 !important;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .order-status {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 12px;
        font-size: 9px;
        font-weight: 500;
    }
    .order-status.completed {
        background: rgba(16, 185, 129, 0.15);
        color: #10b981;
    }
    .order-status.processing {
        background: rgba(245, 158, 11, 0.15);
        color: #f59e0b;
    }
    .order-status.cancelled {
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
    }
    
    /* Brand badge style */
    .brand-badge {
        background: rgba(74, 222, 128, 0.15);
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 10px;
        color: #4ade80;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .brand-badge i {
        font-size: 9px;
    }
    
    /* Source badge */
    .source-badge-imported {
        color: #fbbf24;
        font-size: 9px;
    }
    .source-badge-manual {
        color: #4ade80;
        font-size: 9px;
    }
    
    /* Creator Status Tab Styles */
    .creator-tab-btn.active {
        background: linear-gradient(135deg, var(--purple-glow), rgba(59, 130, 246, 0.1));
        color: var(--purple);
        border: 1px solid rgba(139, 92, 246, 0.4);
    }
    .creator-tab-btn:hover:not(.active) {
        background: rgba(139, 92, 246, 0.1);
        color: var(--purple);
    }
    .view-creator-detail:hover {
        background: var(--purple) !important;
        color: white !important;
    }
    
    /* Responsive untuk mobile */
    @media (max-width: 767px) {
        .creator-list-container table,
        .creator-list-container thead,
        .creator-list-container tbody,
        .creator-list-container th,
        .creator-list-container td,
        .creator-list-container tr {
            display: block;
        }
        .creator-list-container thead {
            display: none;
        }
        .creator-list-container tr {
            margin-bottom: 12px;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px;
        }
        .creator-list-container td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0 !important;
            text-align: left !important;
        }
        .creator-list-container td:before {
            content: attr(data-label);
            font-weight: 600;
            color: #9aaebe;
            margin-right: 16px;
        }
    }
    
    /* Top Creator Tab Styles */
    .leaderboard-card-dashboard {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .leaderboard-card-dashboard:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.3);
    }
    .period-btn.active {
        background: linear-gradient(135deg, var(--purple-glow), rgba(59, 130, 246, 0.1));
        color: var(--purple);
        border-color: rgba(139, 92, 246, 0.4);
    }
    .period-btn:hover:not(.active) {
        background: rgba(139, 92, 246, 0.1);
        border-color: var(--purple);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        #tabLeaderboardDashboard > div:first-of-type {
            flex-direction: column;
            align-items: flex-start;
        }
        #tabLeaderboardDashboard > div:not(:first-of-type) {
            grid-template-columns: 1fr !important;
        }
        .leaderboard-item-dashboard {
            flex-wrap: wrap;
        }
        .leaderboard-item-dashboard > div:last-child {
            width: 100%;
            text-align: left;
            margin-top: 8px;
            padding-left: 48px;
        }
    }
    
    /* Icon SV dan LV styles */
    .icon-sv {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        background: linear-gradient(135deg, #8b5cf6, #a855f7);
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 9px;
        font-weight: 600;
        color: white;
        box-shadow: 0 0 8px rgba(139, 92, 246, 0.5);
    }
    .icon-lv {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        background: linear-gradient(135deg, #f97316, #ea580c);
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 9px;
        font-weight: 600;
        color: white;
        box-shadow: 0 0 8px rgba(249, 115, 22, 0.5);
    }
    .icon-sv i, .icon-lv i {
        font-size: 8px;
    }


    /* ===== IS Dashboard BA-style polish ===== */
    :root{
        --is-border: rgba(112,136,185,.18);
        --is-muted: var(--muted,#8e9bb6);
        --is-muted-2: var(--muted-2,#b7c1d6);
        --is-purple: var(--purple,#7c3cff);
        --is-blue:#3b82f6;
        --is-cyan:#10dff0;
        --is-green:#10b981;
    }
    .dashboard{padding:18px 28px 32px;max-width:1920px;margin:0 auto;}
    .desktop-menu-with-stats{height:auto !important;min-height:76px;display:flex !important;align-items:center;justify-content:space-between;gap:18px;padding:10px 28px !important;border-bottom:1px solid rgba(112,136,185,.16);background:rgba(3,9,20,.28);overflow-x:visible !important;}
    .desktop-menu-links{display:flex;align-items:center;gap:8px;flex-wrap:wrap;min-width:0;}
    .desktop-menu-with-stats a{display:inline-flex;align-items:center;gap:8px;height:36px;padding:0 14px;border-radius:10px;color:var(--is-muted);font-size:13px;font-weight:800;white-space:nowrap;border:1px solid transparent;text-decoration:none;}
    .desktop-menu-with-stats a:hover,.desktop-menu-with-stats a.active{background:linear-gradient(135deg,rgba(124,60,255,.28),rgba(124,60,255,.12));border-color:rgba(124,60,255,.24);color:#fff;box-shadow:none;}
    .menu-stats-dashboard{display:flex !important;grid-template-columns:none !important;gap:10px !important;margin-left:auto;padding:0 !important;border:0 !important;border-radius:0 !important;background:transparent !important;flex:0 0 auto;}
    .menu-stats-dashboard .stat-item-dashboard{position:relative;overflow:hidden;width:190px;min-height:54px !important;padding:9px 12px 9px 52px !important;text-align:left !important;border-radius:14px !important;border:1px solid var(--is-border) !important;background:linear-gradient(160deg,rgba(20,27,54,.84),rgba(7,14,30,.88)) !important;box-shadow:inset 0 1px 0 rgba(255,255,255,.04);}
    .menu-stats-dashboard .stat-item-dashboard:before{content:"";position:absolute;left:12px;top:50%;width:32px;height:32px;transform:translateY(-50%);border-radius:50%;display:grid;place-items:center;color:#fff;font-family:"Font Awesome 6 Free";font-weight:900;font-size:13px;box-shadow:0 0 18px rgba(124,60,255,.22);}
    .menu-stats-dashboard .stat-gmv:before{content:"\f201";background:linear-gradient(135deg,#7c3cff,#c02cff);}
    .menu-stats-dashboard .stat-orders:before{content:"\f291";background:linear-gradient(135deg,#10dff0,#3b82f6);}
    .menu-stats-dashboard .stat-active:before{content:"\f0e7";background:linear-gradient(135deg,#10b981,#39f08a);}
    .menu-stats-dashboard .stat-label-dashboard{font-size:10px !important;font-weight:800;color:var(--is-muted-2) !important;text-transform:none;}
    .menu-stats-dashboard .stat-value-dashboard{margin-top:3px !important;font-size:17px !important;line-height:1;color:#fff !important;background:none !important;-webkit-background-clip:initial !important;background-clip:initial !important;white-space:nowrap;}
    .menu-stats-dashboard .stat-active .stat-value-dashboard{font-size:14px !important;}
    .menu-stats-dashboard .stat-active .stat-value-dashboard i{color:var(--is-green);font-size:7px;margin-right:5px;}
    .menu-stats-dashboard .stat-caption-dashboard{margin-top:3px;font-size:9px;color:var(--is-muted);font-weight:700;}
    .dashboard-header-hidden{display:none !important;}
    .tabs-bar-dashboard{display:grid !important;grid-template-columns:minmax(330px,460px) auto;gap:16px !important;align-items:center;margin:0 0 18px !important;padding:14px 18px !important;border:1px solid var(--is-border) !important;border-radius:18px;background:linear-gradient(160deg,rgba(9,17,34,.72),rgba(4,10,22,.86));}
    .tabs-dashboard{display:flex;gap:8px;padding:0;border:1px solid rgba(112,136,185,.14);border-radius:12px;background:rgba(4,10,22,.38);overflow:hidden;}
    .tab-btn-dashboard{display:inline-flex;align-items:center;justify-content:center;gap:8px;height:42px;padding:0 22px !important;border-radius:10px !important;color:var(--is-muted-2) !important;font-size:13px !important;}
    .tab-btn-dashboard.active{color:#fff !important;background:linear-gradient(135deg,#6226d8,#7c3cff) !important;border:0 !important;}
    .scout-btn-dashboard{justify-self:end;height:42px;min-width:170px;padding:0 20px !important;border-radius:999px !important;color:#d18aff !important;border:1px solid rgba(209,138,255,.86) !important;background:rgba(124,60,255,.08) !important;}
    .scout-btn-dashboard:hover{background:#7c3cff !important;color:#fff !important;}
    .stages-scroll-dashboard{padding:16px;margin-bottom:18px;border:1px solid var(--is-border);border-radius:20px;background:linear-gradient(160deg,rgba(9,17,34,.72),rgba(4,10,22,.86));overflow-x:auto;}
    .stages-container-dashboard{display:grid !important;grid-template-columns:repeat(4,minmax(250px,1fr));gap:16px !important;width:100%;min-width:1180px !important;}
    .stage-card-dashboard{width:auto !important;min-width:0 !important;height:520px !important;padding:16px !important;border-radius:18px !important;overflow:hidden !important;display:flex !important;flex-direction:column !important;border:1px solid var(--is-border) !important;background:linear-gradient(160deg,rgba(13,23,46,.90),rgba(6,12,25,.92)) !important;box-shadow:inset 0 1px 0 rgba(255,255,255,.04);}
    .stage-card-dashboard[data-stage="1"]{border-top:2px solid var(--is-purple) !important;background:linear-gradient(160deg,rgba(31,20,55,.86),rgba(7,14,30,.92)) !important;}
    .stage-card-dashboard[data-stage="2"]{border-top:2px solid var(--is-blue) !important;background:linear-gradient(160deg,rgba(13,30,62,.82),rgba(7,14,30,.92)) !important;}
    .stage-card-dashboard[data-stage="3"]{border-top:2px solid var(--is-cyan) !important;background:linear-gradient(160deg,rgba(6,42,50,.78),rgba(7,14,30,.92)) !important;}
    .stage-card-dashboard[data-stage="4"]{border-top:2px solid var(--is-green) !important;background:linear-gradient(160deg,rgba(9,46,34,.78),rgba(7,14,30,.92)) !important;}
    .stage-title-dashboard{flex:0 0 auto !important;min-height:32px;margin-bottom:10px !important;padding-bottom:0 !important;border-bottom:0 !important;font-size:15px !important;letter-spacing:-.02em;flex-wrap:nowrap !important;}
    .stage-title-dashboard span:first-child{display:flex;align-items:center;gap:8px;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .stage-count-dashboard{padding:5px 10px !important;border:1px solid rgba(255,255,255,.10);background:rgba(3,9,20,.38) !important;font-size:12px !important;color:#fff;}
    .stage-card-dashboard input[type="text"]{width:100% !important;height:40px !important;padding:0 14px !important;color:#fff !important;background:rgba(255,255,255,.055) !important;border:1px solid rgba(255,255,255,.08) !important;border-radius:11px !important;outline:none;}
    .stage-card-dashboard input[type="text"]::placeholder{color:rgba(183,193,214,.72);}
    .stage-card-dashboard > div[style*="padding: 8px 12px"]{padding:0 0 12px !important;}
    #scoutingContainerDashboard,#linkSwappingContainerDashboard,#sendingSampleContainerDashboard,#monitoringContainerDashboard{flex:1 1 auto !important;min-height:0 !important;overflow-y:auto !important;padding:0 4px 0 0 !important;scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.22) transparent;}
    #scoutingContainerDashboard::-webkit-scrollbar,#linkSwappingContainerDashboard::-webkit-scrollbar,#sendingSampleContainerDashboard::-webkit-scrollbar,#monitoringContainerDashboard::-webkit-scrollbar{width:5px;}
    #scoutingContainerDashboard::-webkit-scrollbar-thumb,#linkSwappingContainerDashboard::-webkit-scrollbar-thumb,#sendingSampleContainerDashboard::-webkit-scrollbar-thumb,#monitoringContainerDashboard::-webkit-scrollbar-thumb{background:rgba(255,255,255,.22);border-radius:999px;}
    .stage-item-dashboard{padding:12px !important;margin-bottom:8px !important;border-radius:13px !important;border:1px solid rgba(112,136,185,.14) !important;background:rgba(9,17,34,.56) !important;}
    .stage-item-dashboard:hover{transform:none !important;border-color:rgba(124,60,255,.42) !important;background:rgba(16,29,55,.74) !important;}
    .stage-item-dashboard strong{font-size:12px !important;line-height:1.25 !important;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .item-details-dashboard{font-size:9.5px !important;line-height:1.4 !important;gap:5px 9px !important;color:var(--is-muted-2) !important;}
    .badge-dashboard{font-size:8.5px !important;padding:4px 8px !important;font-weight:900;text-transform:uppercase;}
    .task-btn-dashboard{height:42px !important;margin-top:10px !important;border-radius:12px !important;border:1px solid rgba(124,60,255,.26) !important;background:rgba(124,60,255,.10) !important;color:#c084fc !important;}
    .task-btn-dashboard:hover:not(:disabled){background:linear-gradient(135deg,#6226d8,#7c3cff) !important;color:#fff !important;}
    .brand-card-dashboard,.leaderboard-card-dashboard,.recent-section-dashboard,#tabLeaderboardDashboard > div[style*="background"]{border-radius:20px !important;border:1px solid var(--is-border) !important;background:linear-gradient(160deg,rgba(9,17,34,.74),rgba(4,10,22,.88)) !important;}
    @media(max-width:1500px){.desktop-menu-with-stats{align-items:flex-start;flex-direction:column;}.menu-stats-dashboard{width:100%;margin-left:0;}.menu-stats-dashboard .stat-item-dashboard{flex:1;width:auto;}}
    @media(max-width:1200px){.tabs-bar-dashboard{grid-template-columns:1fr !important;}.scout-btn-dashboard{justify-self:stretch;width:100%;}.stages-container-dashboard{display:flex !important;min-width:min-content !important;}.stage-card-dashboard{flex:0 0 310px !important;width:310px !important;min-width:310px !important;}}
    @media(max-width:767px){.dashboard{padding:16px 14px 76px !important;}.desktop-menu-with-stats{display:none !important;}.mobile-menu-bar{display:flex;overflow-x:auto;gap:8px;padding:10px 14px;}.menu-stats-dashboard{display:none !important;}.tabs-bar-dashboard{padding:12px !important;}.tabs-dashboard{width:100%;overflow-x:auto;}.tab-btn-dashboard{flex:1;min-width:max-content;padding:0 14px !important;}.stage-card-dashboard{flex:0 0 294px !important;width:294px !important;min-width:294px !important;}}
    .mobile-menu-bar{
        display:none !important;
    }
    @media(max-width:767px){
        .desktop-menu{
            display:none !important;
        }
        .mobile-menu-bar{
            display:flex !important;
            overflow-x:auto;
            gap:8px;
            padding:10px 14px;
        }
    }

    /* Assign Modal Table */
    .assign-modal-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }
    .assign-modal-table thead th {
        padding: 10px 12px;
        text-align: left;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #8b5cf6;
        background: #1a1f2e;
        border-bottom: 2px solid #2a3346;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .assign-modal-table tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #1e293b;
        vertical-align: middle;
    }
    .assign-modal-table tbody tr {
        transition: background 0.15s ease;
    }
    .assign-modal-table tbody tr:hover {
        background: rgba(139, 92, 246, 0.05);
    }
    .assign-modal-table tbody tr:last-child td {
        border-bottom: none;
    }
    .assign-modal-table .col-product { width: 40%; }
    .assign-modal-table .col-sales { width: 8%; text-align: center; }
    .assign-modal-table .col-commission { width: 12%; text-align: center; }
    .assign-modal-table .col-status { width: 18%; text-align: center; }
    .assign-modal-table .col-action { width: 22%; text-align: center; }

    /* Product cell dengan gambar */
    .product-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .product-cell .product-thumb {
        width: 36px;
        height: 36px;
        border-radius: 6px;
        background: #1e293b;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        overflow: hidden;
    }
    .product-cell .product-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .product-cell .product-thumb i {
        font-size: 14px;
        color: #6b7280;
    }
    .product-cell .product-info {
        min-width: 0;
    }
    .product-cell .product-name {
        font-weight: 500;
        color: #e2f0e8;
        font-size: 12px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .product-cell .product-meta {
        font-size: 10px;
        color: #6b7280;
        margin-top: 2px;
    }
    .product-cell .product-meta .shop-name {
        color: #8b5cf6;
    }
    .product-cell .product-meta .campaign-id {
        color: #4ade80;
    }

    /* Badge Status */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 600;
    }
    .status-badge.ready {
        background: rgba(74, 222, 128, 0.15);
        color: #4ade80;
    }
    .status-badge.assigned {
        background: rgba(139, 92, 246, 0.15);
        color: #8b5cf6;
    }
    .status-badge.nolink {
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
    }

    /* Tombol Action */
    .btn-action {
        padding: 4px 14px;
        border-radius: 16px;
        font-size: 10px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .btn-action.assign {
        background: #8b5cf6;
        color: white;
    }
    .btn-action.assign:hover:not(:disabled) {
        background: #7c3aed;
        transform: scale(1.02);
    }
    .btn-action.assign:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .btn-action.copy {
        background: transparent;
        color: #4ade80;
        border: 1px solid #4ade80;
    }
    .btn-action.copy:hover {
        background: #4ade80;
        color: #0a0e17;
    }
    .btn-action.ask-bd {
        color: #6b7280;
        font-size: 9px;
        cursor: default;
    }

    /* Summary Bar */
    .summary-bar {
        display: flex;
        gap: 20px;
        padding: 10px 14px;
        background: rgba(139, 92, 246, 0.08);
        border-radius: 8px;
        margin-bottom: 12px;
        flex-wrap: wrap;
        border: 1px solid rgba(139, 92, 246, 0.1);
    }
    .summary-bar .item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        color: #9aaebe;
    }
    .summary-bar .item .value {
        font-weight: 600;
        color: #e2f0e8;
    }
    .summary-bar .item .value.green { color: #4ade80; }
    .summary-bar .item .value.gold { color: #fbbf24; }
    .summary-bar .item .value.purple { color: #8b5cf6; }

    /* Container scroll */
    .table-scroll {
        max-height: 380px;
        overflow-y: auto;
        border-radius: 8px;
        border: 1px solid #1e293b;
    }
    .table-scroll::-webkit-scrollbar {
        width: 4px;
    }
    .table-scroll::-webkit-scrollbar-track {
        background: #0f1420;
    }
    .table-scroll::-webkit-scrollbar-thumb {
        background: #4ade80;
        border-radius: 4px;
    }

    /* ===== 3 TASK NEW STYLES ===== */
    .is-dashboard { padding: 20px 28px 32px; max-width: 1920px; margin: 0 auto; }
    .is-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--is-border);
    }
    .is-header h1 {
        font-size: 28px;
        font-weight: 800;
        background: linear-gradient(135deg, var(--is-purple), var(--is-blue));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        margin: 0;
    }
    .is-header .sub {
        font-size: 13px;
        color: var(--is-muted);
    }
    .is-stats {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }
    .is-stat {
        background: var(--is-card);
        border: 1px solid var(--is-border);
        border-radius: 12px;
        padding: 10px 18px;
        text-align: center;
        min-width: 100px;
    }
    .is-stat .label {
        font-size: 10px;
        color: var(--is-muted);
        text-transform: uppercase;
        font-weight: 600;
    }
    .is-stat .value {
        font-size: 20px;
        font-weight: 700;
        color: var(--is-text);
        margin-top: 4px;
    }
    .is-stat .value.green { color: var(--is-green); }
    .is-stat .value.purple { color: var(--is-purple); }
    .is-stat .value.orange { color: var(--is-orange); }
    .is-stat .value.blue { color: var(--is-blue); }
    .is-stat .growth {
        font-size: 10px;
        margin-top: 2px;
    }
    .is-stat .growth.positive { color: var(--is-green); }
    .is-stat .growth.negative { color: var(--is-red); }

    .is-tasks {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 20px;
        margin-top: 8px;
    }
    .is-task-card {
        background: linear-gradient(160deg, rgba(13,23,46,.90), rgba(6,12,25,.92));
        border: 1px solid var(--is-border);
        border-radius: 18px;
        padding: 16px;
        height: 560px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .is-task-card.task-1 { border-top: 3px solid var(--is-purple); }
    .is-task-card.task-2 { border-top: 3px solid var(--is-orange); }
    .is-task-card.task-3 { border-top: 3px solid var(--is-green); }
    .is-task-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        flex-shrink: 0;
    }
    .is-task-header h3 {
        font-size: 15px;
        font-weight: 700;
        color: var(--is-text);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .is-task-header h3 i {
        font-size: 14px;
    }
    .is-task-header h3 i.purple { color: var(--is-purple); }
    .is-task-header h3 i.orange { color: var(--is-orange); }
    .is-task-header h3 i.green { color: var(--is-green); }
    .is-task-badge {
        background: rgba(255,255,255,0.06);
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 11px;
        color: var(--is-muted);
    }
    .is-task-search {
        flex-shrink: 0;
        margin-bottom: 10px;
    }
    .is-task-search input {
        width: 100%;
        padding: 8px 12px;
        background: rgba(255,255,255,0.05);
        border: 1px solid var(--is-border);
        border-radius: 10px;
        color: var(--is-text);
        font-size: 12px;
    }
    .is-task-search input::placeholder {
        color: var(--is-muted);
        opacity: 0.6;
    }
    .is-task-items {
        flex: 1;
        overflow-y: auto;
        padding-right: 4px;
    }
    .is-task-items::-webkit-scrollbar {
        width: 4px;
    }
    .is-task-items::-webkit-scrollbar-track {
        background: transparent;
    }
    .is-task-items::-webkit-scrollbar-thumb {
        background: var(--is-border);
        border-radius: 4px;
    }

    .is-item {
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.05);
        border-radius: 12px;
        padding: 12px;
        margin-bottom: 8px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .is-item:hover {
        border-color: var(--is-purple);
        background: rgba(139,92,246,0.05);
    }
    .is-item-header {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .is-item-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        overflow: hidden;
        flex-shrink: 0;
        background: var(--is-border);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .is-item-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .is-item-avatar i {
        font-size: 18px;
        color: var(--is-muted);
    }
    .is-item-info {
        flex: 1;
        min-width: 0;
    }
    .is-item-name {
        font-weight: 600;
        color: var(--is-text);
        font-size: 13px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .is-item-name .badge-new {
        background: var(--is-green);
        color: #0a0e17;
        font-size: 8px;
        padding: 1px 6px;
        border-radius: 10px;
        margin-left: 6px;
        font-weight: 700;
    }
    .is-item-detail {
        font-size: 10px;
        color: var(--is-muted);
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 2px;
    }
    .is-item-detail .gmv {
        color: var(--is-green);
        font-weight: 600;
    }
    .is-item-detail .link-count {
        color: var(--is-purple);
    }
    .is-item-product {
        margin-top: 6px;
        font-size: 10px;
        color: var(--is-muted);
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .is-item-product .product-thumb {
        width: 24px;
        height: 24px;
        border-radius: 4px;
        overflow: hidden;
        flex-shrink: 0;
        background: var(--is-border);
    }
    .is-item-product .product-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .deal-ready {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: rgba(74,222,128,0.15);
        color: var(--is-green);
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 9px;
        font-weight: 600;
        animation: pulse-green 2s infinite;
    }
    @keyframes pulse-green {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }
    .deal-ready i {
        font-size: 7px;
    }
    .deal-claimed {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: rgba(139,92,246,0.15);
        color: var(--is-purple);
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 9px;
        font-weight: 600;
    }

    .is-item-actions {
        display: flex;
        gap: 6px;
        flex-shrink: 0;
    }
    .btn-claim {
        background: var(--is-green);
        color: #0a0e17;
        border: none;
        padding: 4px 14px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .btn-claim:hover {
        transform: scale(1.05);
        box-shadow: 0 0 20px rgba(74,222,128,0.3);
    }
    .btn-claim:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }
    .btn-detail {
        background: transparent;
        border: 1px solid var(--is-border);
        color: var(--is-muted);
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 9px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-detail:hover {
        border-color: var(--is-purple);
        color: var(--is-text);
    }

    .is-empty {
        text-align: center;
        padding: 40px 20px;
        color: var(--is-muted);
    }
    .is-empty i {
        font-size: 32px;
        margin-bottom: 12px;
        display: block;
        opacity: 0.5;
    }
    .is-empty p {
        font-size: 13px;
    }

    @media (max-width: 1200px) {
        .is-tasks {
            grid-template-columns: 1fr 1fr;
        }
    }
    @media (max-width: 768px) {
        .is-tasks {
            grid-template-columns: 1fr;
        }
        .is-header {
            flex-direction: column;
            align-items: flex-start;
        }
        .is-stats {
            width: 100%;
            justify-content: space-between;
        }
        .is-stat {
            flex: 1;
            min-width: 60px;
            padding: 8px 12px;
        }
        .is-stat .value {
            font-size: 16px;
        }
    }
    #creatorModal .modal-glass-dashboard {
        max-width: 800px !important;
        width: 95% !important;
    }
    @keyframes pulse-red {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.is-item[data-searchable] {
    /* Tidak ada efek visual */
}

/* ============================================================ */
/* FIX FORCE: MODAL TAMBAH CREATOR TASK 3 - PAKSA HITAM */
/* ============================================================ */

#addCreatorTask3Modal {
    display: none !important;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    background: rgba(0,0,0,0.85) !important;
    backdrop-filter: blur(8px) !important;
    -webkit-backdrop-filter: blur(8px) !important;
    z-index: 9999 !important;
    align-items: center !important;
    justify-content: center !important;
}

#addCreatorTask3Modal.active {
    display: flex !important;
}

#addCreatorTask3Modal .modal-glass-dashboard {
    background: #111827 !important; /* HITAM */
    border: 1px solid #4ade80 !important;
    border-radius: 28px !important;
    max-width: 550px !important;
    width: 95% !important;
    padding: 24px !important;
    max-height: 85vh !important;
    overflow-y: auto !important;
    color: #e2f0e8 !important;
    box-shadow: 0 20px 60px rgba(0,0,0,0.9) !important;
}

#addCreatorTask3Modal .modal-header-dashboard {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    margin-bottom: 16px !important;
    border-bottom: 1px solid #2a3346 !important;
    padding-bottom: 10px !important;
}

#addCreatorTask3Modal .modal-header-dashboard h3 {
    color: #e2f0e8 !important;
    font-size: 18px !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    margin: 0 !important;
}

#addCreatorTask3Modal .modal-header-dashboard h3 i {
    color: #10b981 !important;
}

#addCreatorTask3Modal .modal-close-dashboard {
    font-size: 26px !important;
    cursor: pointer !important;
    color: #9aaebe !important;
}

#addCreatorTask3Modal .modal-body > div:first-child {
    background: rgba(16,185,129,0.1) !important;
    border-radius: 12px !important;
    padding: 12px !important;
    margin-bottom: 16px !important;
    border-left: 4px solid #10b981 !important;
}

#addCreatorTask3Modal .modal-body > div:first-child p {
    color: #10b981 !important;
    font-size: 13px !important;
    margin: 0 !important;
}

#addCreatorTask3Modal .modal-body label {
    color: #e2f0e8 !important;
    font-weight: 500 !important;
    display: block !important;
    margin-top: 14px !important;
    margin-bottom: 5px !important;
    font-size: 13px !important;
}

#addCreatorTask3Modal .modal-body input,
#addCreatorTask3Modal .modal-body select,
#addCreatorTask3Modal .modal-body textarea {
    background: #0f1420 !important; /* HITAM PEKAT */
    border: 1px solid #2a3346 !important;
    border-radius: 12px !important;
    color: #e2f0e8 !important;
    padding: 10px 12px !important;
    width: 100% !important;
    font-size: 13px !important;
    outline: none !important;
    box-sizing: border-box !important;
}

#addCreatorTask3Modal .modal-body input:focus,
#addCreatorTask3Modal .modal-body select:focus {
    border-color: #4ade80 !important;
    box-shadow: 0 0 0 3px rgba(74,222,128,0.15) !important;
}

#addCreatorTask3Modal .modal-body input::placeholder {
    color: rgba(183,193,214,0.6) !important;
}

#addCreatorTask3Modal .modal-body select option {
    background: #111827 !important;
    color: #e2f0e8 !important;
}

#addCreatorTask3Modal .modal-body > form > div:first-of-type {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 12px !important;
}

#addCreatorTask3Modal .modal-body > form > div:first-of-type > div:first-child {
    grid-column: 1 / -1 !important;
}

#addCreatorTask3Modal .modal-body > form > div:last-of-type {
    display: flex !important;
    gap: 10px !important;
    margin-top: 20px !important;
    padding-top: 16px !important;
    border-top: 1px solid #2a3346 !important;
}

#addCreatorTask3Modal .modal-body button[type="button"] {
    flex: 1 !important;
    background: rgba(255,255,255,0.05) !important;
    color: #9aaebe !important;
    padding: 12px !important;
    border-radius: 40px !important;
    border: 1px solid #2a3346 !important;
    cursor: pointer !important;
    font-weight: 600 !important;
    font-size: 13px !important;
}

#addCreatorTask3Modal .modal-body button[type="button"]:hover {
    background: rgba(255,255,255,0.1) !important;
}

#addCreatorTask3Modal .modal-body button[type="submit"] {
    flex: 1 !important;
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: white !important;
    padding: 12px !important;
    border-radius: 40px !important;
    border: none !important;
    cursor: pointer !important;
    font-weight: 600 !important;
    font-size: 13px !important;
}

#addCreatorTask3Modal .modal-body button[type="submit"]:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 15px rgba(16,185,129,0.3) !important;
}

@media (max-width: 600px) {
    #addCreatorTask3Modal .modal-body > form > div:first-of-type {
        grid-template-columns: 1fr !important;
    }
    #addCreatorTask3Modal .modal-glass-dashboard {
        padding: 16px !important;
    }
}

/* ============================================================ */
/* FIX: MODAL TAMBAH CREATOR TASK 1 (SCOUTING) - JUGA HITAM */
/* ============================================================ */

#taskModalDashboard {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    background: rgba(0,0,0,0.85) !important;
    backdrop-filter: blur(8px) !important;
    -webkit-backdrop-filter: blur(8px) !important;
    z-index: 9999 !important;
    display: none !important;
    align-items: center !important;
    justify-content: center !important;
}

#taskModalDashboard.active {
    display: flex !important;
}

#taskModalDashboard .modal-glass-dashboard {
    background: #111827 !important;
    border: 1px solid #8b5cf6 !important;
    border-radius: 28px !important;
    max-width: 550px !important;
    width: 95% !important;
    padding: 24px !important;
    max-height: 85vh !important;
    overflow-y: auto !important;
    color: #e2f0e8 !important;
    box-shadow: 0 20px 60px rgba(0,0,0,0.9) !important;
}

#taskModalDashboard .modal-body input,
#taskModalDashboard .modal-body select {
    background: #0f1420 !important;
    border: 1px solid #2a3346 !important;
    border-radius: 12px !important;
    color: #e2f0e8 !important;
    padding: 10px 12px !important;
    width: 100% !important;
}

#taskModalDashboard .modal-body label {
    color: #e2f0e8 !important;
}

#taskModalDashboard .modal-header-dashboard h3 {
    color: #e2f0e8 !important;
}

.product-link-item.selected {
    border-color: #8b5cf6 !important;
    background: rgba(139,92,246,0.15) !important;
}

.product-link-item.selected {
    border-color: #8b5cf6 !important;
    background: rgba(139,92,246,0.15) !important;
    transition: all 0.2s ease;
}

.product-link-item .product-checkbox {
    cursor: pointer;
    accent-color: #8b5cf6;
}
/* Status badges untuk product */
.status-assigned {
    background: rgba(16, 185, 129, 0.2) !important;
    color: #10b981 !important;
    border: 1px solid rgba(16, 185, 129, 0.3) !important;
}

.status-available {
    background: rgba(74, 222, 128, 0.15) !important;
    color: #4ade80 !important;
    border: 1px solid rgba(74, 222, 128, 0.2) !important;
}

.status-unmatched {
    background: rgba(245, 158, 11, 0.15) !important;
    color: #f59e0b !important;
    border: 1px solid rgba(245, 158, 11, 0.2) !important;
}

/* Product item berdasarkan status */
.product-link-item.assigned {
    background: rgba(16, 185, 129, 0.08) !important;
    border-left: 3px solid #10b981 !important;
    border-color: rgba(16, 185, 129, 0.3) !important;
}

.product-link-item.available {
    background: rgba(74, 222, 128, 0.05) !important;
    border-left: 3px solid #4ade80 !important;
    border-color: rgba(74, 222, 128, 0.2) !important;
}

.product-link-item.unmatched {
    background: rgba(245, 158, 11, 0.03) !important;
    border-left: 3px solid #f59e0b !important;
    border-color: rgba(245, 158, 11, 0.15) !important;
    opacity: 0.8;
}
</style>

<!-- ============================================================ -->
<!-- DESKTOP MENU (SAMA DENGAN FILE LAMA) -->
<!-- ============================================================ -->
<div class="desktop-menu desktop-menu-with-stats">
    <div class="desktop-menu-links">
        <a href="<?= base_url('is/dashboard') ?>" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="<?= base_url('is/creators') ?>"><i class="fas fa-users"></i> Creators</a>
        <a href="<?= base_url('is/performance') ?>"><i class="fas fa-chart-line"></i> Performance</a>
        <a href="<?= base_url('performance') ?>"><i class="fas fa-chart-line"></i> Performance Creator</a>
        <a href="<?= base_url('is/team_performance') ?>"><i class="fas fa-users"></i> Team Performance</a>
        <a href="<?= base_url('analytics/bd') ?>"><i class="fas fa-chart-line"></i> Analytics</a>
        <a href="<?= base_url($this->session->userdata('role') == 'IS' ? 'is/target_plan_dashboard' : 'bd/target_plan_dashboard') ?>"><i class="fas fa-bullseye"></i> Target Plan</a>
    </div>

    <div class="stat-cards-dashboard menu-stats-dashboard">
        <div class="stat-item-dashboard stat-gmv">
            <div class="stat-label-dashboard">Total GMV</div>
            <div class="stat-value-dashboard">Rp <?= number_format($today_gmv ?? 0, 0, ',', '.') ?></div>
            <div class="stat-caption-dashboard">Hari ini</div>
        </div>
        <div class="stat-item-dashboard stat-orders">
            <div class="stat-label-dashboard">Total Orders</div>
            <div class="stat-value-dashboard"><?= number_format($today_orders ?? 0) ?></div>
            <div class="stat-caption-dashboard">Semua order</div>
        </div>
        <div class="stat-item-dashboard stat-active">
            <div class="stat-label-dashboard">Status</div>
            <div class="stat-value-dashboard"><i class="fas fa-circle"></i> LIVE</div>
            <div class="stat-caption-dashboard">Realtime API</div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MOBILE MENU BAR -->
<!-- ============================================================ -->
<div class="mobile-menu-bar">
    <a href="<?= base_url('is/dashboard') ?>" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="<?= base_url('is/creators') ?>"><i class="fas fa-users"></i> Creators</a>
    <a href="<?= base_url('is/performance') ?>"><i class="fas fa-chart-line"></i> Performance</a>
    <a href="<?= base_url('analytics/bd') ?>"><i class="fas fa-chart-line"></i> Analytics</a>
</div>

<!-- ============================================================ -->
<!-- DASHBOARD -->
<!-- ============================================================ -->
<div class="is-dashboard">
    
  

    <!-- ============================================================ -->
    <!-- AUTO CREATOR SCOUTING LIST -->
    <!-- ============================================================ -->
    <div id="autoScoutingSection" style="margin-bottom: 24px;">

        <!-- Header panel -->
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:14px;">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:36px;height:36px;background:linear-gradient(135deg,rgba(139,92,246,0.25),rgba(59,130,246,0.2));
                            border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-robot" style="color:#a78bfa;font-size:15px;"></i>
                </div>
                <div>
                    <h3 style="margin:0;font-size:14px;font-weight:700;color:var(--text-primary);">
                        Scouting List Otomatis
                        <span id="scoutingBadgeCount" style="background:rgba(139,92,246,0.2);color:#a78bfa;
                              font-size:11px;padding:2px 8px;border-radius:20px;margin-left:6px;font-weight:600;">0</span>
                    </h3>
                    <p style="margin:0;font-size:11px;color:var(--text-secondary);">
                        Creator yang terbukti pernah menjual produk dari brand aktif
                    </p>
                </div>
            </div>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <!-- Filter brand -->
                <select id="scoutingBrandFilter" onchange="loadScoutingList()"
                    style="padding:7px 12px;background:rgba(255,255,255,0.05);border:1px solid var(--border);
                           border-radius:10px;color:var(--text-primary);font-size:12px;outline:none;cursor:pointer;">
                    <option value="">Semua Brand</option>
                </select>
                <!-- Filter source -->
                <select id="scoutingSourceFilter" onchange="loadScoutingList()"
                    style="padding:7px 12px;background:rgba(255,255,255,0.05);border:1px solid var(--border);
                           border-radius:10px;color:var(--text-primary);font-size:12px;outline:none;cursor:pointer;">
                    <option value="">Semua Sumber</option>
                    <option value="affiliate_orders">Dari Order</option>
                    <option value="fastmoss">FastMoss</option>
                </select>
                <!-- Search -->
                <input type="text" id="scoutingSearch" placeholder=" Cari creator / produk..."
                    oninput="debounceScoutingSearch()"
                    style="padding:7px 12px;background:rgba(255,255,255,0.05);border:1px solid var(--border);
                           border-radius:10px;color:var(--text-primary);font-size:12px;outline:none;width:180px;">
                <!-- Refresh -->
                <button onclick="refreshScoutingList()" id="refreshScoutingBtn"
                    style="padding:7px 14px;background:rgba(139,92,246,0.15);color:#a78bfa;border:1px solid rgba(139,92,246,0.3);
                           border-radius:10px;cursor:pointer;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:5px;">
                    <i class="fas fa-sync-alt" id="refreshScoutingIcon"></i> Perbarui
                </button>
            </div>
        </div>

        <!-- Scrollable row creator -->
        <div id="scoutingScrollWrapper" style="position:relative;">
            <div id="scoutingListGrid"
                 style="display:flex; gap:12px; overflow-x:auto; overflow-y:hidden;
                        padding-bottom:10px; scroll-behavior:smooth;
                        scrollbar-width:thin; scrollbar-color:rgba(139,92,246,0.35) transparent;">
                <!-- diisi JS -->
                <div class="scouting-loading-placeholder"
                     style="width: 100%; text-align: center; padding: 40px 20px; color: var(--text-secondary);
                            display: flex; flex-direction: column; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-spinner fa-pulse fa-2x" style="color:rgba(139,92,246,0.4);margin-bottom:12px;display:block;"></i>
                    Memuat...
                </div>
            </div>
            <!-- Fade kanan -->
            <div id="scoutingFadeRight"
                 style="position:absolute;top:0;right:0;width:48px;height:100%;pointer-events:none;
                        background:linear-gradient(to right,transparent,var(--bg,#0a0e17));"></div>
        </div>

        <!-- Load more sentinel (invisible, trigger infinite scroll) -->
        <div id="scoutingLoadMore" style="display:none;"></div>

    </div>
    <!-- END AUTO CREATOR SCOUTING LIST -->

    <!-- ============================================================ -->
    <!-- 3 TASKS -->
    <!-- ============================================================ -->
    <div class="is-tasks">
        
     <!-- ============================================================ -->
<!-- TASK 1: SCOUTING & AUTO GENERATE LINK -->
<!-- ============================================================ -->
<div class="stage-card-dashboard" data-stage="1" style="display: flex; flex-direction: column; height: 500px;">
    <div class="stage-title-dashboard" style="flex-shrink: 0; display: flex; justify-content: space-between; align-items: center; width: 100%;">
        <span><i class="fas fa-search" style="color: var(--purple);"></i> 1. SCOUTING</span>
        <div style="display: flex; align-items: center; gap: 8px;">
            <button onclick="openCookieModal()" title="Update FastMoss Cookie" style="background: rgba(139,92,246,0.2); border: 1px solid rgba(139,92,246,0.3); color: #a78bfa; padding: 2px 8px; border-radius: 6px; font-size: 10px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; outline: none; transition: 0.2s;">
                <i class="fas fa-key" style="font-size: 9px;"></i> Cookie
            </button>
            <span class="stage-count-dashboard" id="scoutingCountDashboard"><?= count($task1_creators ?? []) ?></span>
        </div>
    </div>
    
    <!-- Search input -->
    <div style="flex-shrink: 0; padding: 8px 12px;">
        <input type="text" id="searchScoutingDashboard" placeholder=" Cari creator atau brand..." 
               style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 10px; font-size: 12px; background: rgba(255,255,255,0.05); color: var(--text-primary); outline: none; transition: var(--transition);">
    </div>
    
    <!-- Scrollable container -->
    <div id="scoutingContainerDashboard" style="flex: 1; overflow-y: auto; padding: 0 8px; scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.22) transparent;">
        <style>
            #scoutingContainerDashboard::-webkit-scrollbar { width: 5px; }
            #scoutingContainerDashboard::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.22); border-radius: 999px; }
            #scoutingContainerDashboard::-webkit-scrollbar-track { background: transparent; }
        </style>
        
        <?php if (!empty($task1_creators)): ?>
            <?php foreach ($task1_creators as $item): ?>
            <div class="stage-item-dashboard scouting-item-dashboard" 
                 data-creator-id="<?= $item->id ?>" 
                 data-creator-name="<?= htmlspecialchars($item->username) ?>"
                 data-creator-phone="<?= htmlspecialchars($item->phone ?? '') ?>"
                 data-no-phone="<?= empty($item->phone) ? '1' : '0' ?>"
                 data-searchable="<?= strtolower(htmlspecialchars($item->username . ' ' . ($item->shop_name ?? '') . ' ' . ($item->brand_name ?? ''))) ?>"
                 style="padding: 12px; margin-bottom: 8px; border-radius: 13px; border: 1px solid rgba(112,136,185,0.14); background: rgba(9,17,34,0.56); cursor: pointer; transition: var(--transition);">
                
                <div style="display:flex; justify-content:space-between; align-items:center; gap: 8px;">
                    <strong style="font-size: 12px; line-height: 1.25; color: var(--text-primary); display: flex; align-items: center; gap: 6px;">
                        <i class="fab fa-tiktok" style="color: #8b5cf6;"></i> 
                        <?= htmlspecialchars($item->username) ?>
                    </strong>
                    <div style="display:flex; gap:4px; align-items:center; flex-shrink: 0;">
                        <?php if (isset($item->follow_up_count) && $item->follow_up_count > 0): ?>
                        <span style="background:rgba(245,158,11,0.15); color:#f59e0b; font-size:8px; padding:2px 6px; border-radius:10px; display:inline-flex; align-items:center; gap:3px;">
                            <i class="fas fa-clock"></i> <?= $item->follow_up_count ?>x
                        </span>
                        <?php endif; ?>
                        <span class="badge-dashboard badge-pending" style="font-size:8px; padding:3px 8px; display:inline-flex; align-items:center; gap:3px;">
                            <i class="fas fa-clock"></i> <?= $item->status ?? 'PENDING' ?>
                        </span>
                    </div>
                </div>
                
                <!-- SHOP/BRAND NAME -->
                <div class="item-details-dashboard" style="margin-top: 4px; display: flex; flex-wrap: wrap; gap: 5px 9px; font-size: 9.5px; color: var(--is-muted-2);">
                    <?php if (!empty($item->shop_name) || !empty($item->brand_name)): ?>
                    <span class="brand-badge" style="background: rgba(74,222,128,0.15); padding: 2px 8px; border-radius: 20px; display: inline-flex; align-items: center; gap: 4px; font-size: 9.5px; color: #4ade80;">
                        <i class="fas fa-store" style="font-size: 8px;"></i> <?= htmlspecialchars($item->shop_name ?: $item->brand_name) ?>
                    </span>
                    <?php else: ?>
                    <span style="color: #9aaebe; font-size: 10px; display: inline-flex; align-items: center; gap: 4px;">
                        <i class="fas fa-store" style="font-size: 8px;"></i> Belum ada brand
                    </span>
                    <?php endif; ?>
                    
                    <?php if (!empty($item->category)): ?>
                    <span style="display: inline-flex; align-items: center; gap: 4px;">
                        <i class="fas fa-tag" style="font-size: 8px;"></i> <?= htmlspecialchars($item->category) ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <!-- WhatsApp & GMV -->
                <div class="item-details-dashboard" style="display: flex; flex-wrap: wrap; gap: 8px 16px; font-size: 9.5px; color: var(--is-muted-2); margin-top: 2px;">
                    <span id="phoneDisplay_<?= $item->id ?>" style="display: inline-flex; align-items: center; gap: 4px;">
                        <i class="fab fa-whatsapp" style="color: #25D366;"></i> 
                        <?php if (!empty($item->phone)): ?>
                            <?= htmlspecialchars($item->phone) ?>
                            <span onclick="event.stopPropagation(); window.openUpdatePhoneModal('<?= $item->id ?>', '<?= htmlspecialchars($item->username) ?>')"
                                  title="Edit nomor WA"
                                  style="cursor:pointer; color:#6b7280; font-size:8px; margin-left:2px;">
                                <i class="fas fa-pencil-alt"></i>
                            </span>
                        <?php else: ?>
                            <span style="color: #ef4444;">Tidak ada</span>
                        <?php endif; ?>
                    </span>
                    
                    <?php if (!empty($item->imported_gmv) && $item->imported_gmv > 0): ?>
                    <span style="color: #fbbf24; display: inline-flex; align-items: center; gap: 4px;">
                        <i class="fas fa-chart-line" style="font-size: 8px;"></i> GMV: Rp <?= number_format($item->imported_gmv, 0, ',', '.') ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <!-- Multiple Link -->
                <?php if (!empty($item->multi_links)): ?>
                <div class="item-details-dashboard" style="margin-top:4px; display: flex; gap: 4px;">
                    <span style="color:#8b5cf6; font-size:9px; display: inline-flex; align-items: center; gap: 4px;">
                        <i class="fas fa-layer-group"></i> <?= count($item->multi_links) ?> Multiple Link tersedia
                    </span>
                </div>
                <?php endif; ?>
                
                <!-- SUMBER DATA & TANGGAL INPUT -->
                <div class="item-details-dashboard" style="font-size: 9px; margin-top: 6px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 4px; color: var(--is-muted);">
                    <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                        <?php if (isset($item->source) && $item->source == 'imported'): ?>
                        <span style="color: #fbbf24; display: inline-flex; align-items: center; gap: 3px;">
                            <i class="fas fa-file-import" style="font-size: 8px;"></i> Imported
                        </span>
                        <?php else: ?>
                        <span style="color: #4ade80; display: inline-flex; align-items: center; gap: 3px;">
                            <i class="fas fa-user-plus" style="font-size: 8px;"></i> Manual
                        </span>
                        <?php endif; ?>
                        
                        <span style="display: inline-flex; align-items: center; gap: 3px;">
                            <i class="fas fa-calendar-alt" style="font-size: 8px;"></i> 
                            <?= !empty($item->created_at) ? date('d/m/Y H:i', strtotime($item->created_at)) : '-' ?>
                        </span>
                    </div>
                    
                    <!-- TOMBOL FETCH WA DARI TAP / INPUT MANUAL -->
                    <?php if (empty($item->phone) || $item->phone === 'no_phone'): ?>
                    <div class="action-buttons-wa-wrapper" style="display:inline-flex; gap:4px; align-items:center;">
                        <button class="resync-wa-btn"
                                data-creator-id="<?= $item->id ?>"
                                data-creator-name="<?= htmlspecialchars($item->username) ?>"
                                title="Ambil nomor WA dari TAP API"
                                style="background: linear-gradient(135deg,#0ea5e9,#2563eb); color:#fff; border:none; padding:2px 8px; border-radius:10px; cursor:pointer; font-size:9px; font-weight:600; transition:var(--transition); display:inline-flex; align-items:center; gap:3px;">
                            <i class="fab fa-tiktok" style="font-size:8px;"></i> Fetch TAP
                        </button>
                        <button onclick="event.stopPropagation(); window.openUpdatePhoneModal('<?= $item->id ?>', '<?= htmlspecialchars($item->username) ?>')"
                                title="Input nomor WA manual"
                                style="background:rgba(245,158,11,0.15); color:#f59e0b; border:1px solid rgba(245,158,11,0.3); padding:2px 8px; border-radius:10px; cursor:pointer; font-size:9px; font-weight:600; transition:var(--transition); display:inline-flex; align-items:center; gap:3px;">
                            <i class="fas fa-keyboard" style="font-size:8px;"></i> Manual
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- ACTION BUTTONS -->
                <div style="display:flex; gap:6px; margin-top:8px; flex-wrap:wrap;">
                    <button class="task1-detail-btn" data-creator-id="<?= $item->id ?>"
                            style="background: linear-gradient(135deg, var(--purple-glow), rgba(59,130,246,0.1)); color: var(--purple); border: 1px solid rgba(139,92,246,0.3); padding: 4px 12px; border-radius: 20px; cursor: pointer; font-size: 9px; font-weight: 600; transition: var(--transition); display: inline-flex; align-items: center; gap: 4px;">
                        <i class="fas fa-info-circle"></i> Detail
                    </button>
                    <button class="task1-send-link-btn" data-creator-id="<?= $item->id ?>"
                        style="background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; padding: 4px 12px; border-radius: 20px; cursor: pointer; font-size: 9px; font-weight: 600; transition: var(--transition); display: inline-flex; align-items: center; gap: 4px;">
                    <i class="fas fa-paper-plane"></i> Send Link
                </button>
                    <button class="task1-followup-btn" data-creator-id="<?= $item->id ?>"
                            style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white; border: none; padding: 4px 12px; border-radius: 20px; cursor: pointer; font-size: 9px; font-weight: 600; transition: var(--transition); display: inline-flex; align-items: center; gap: 4px;">
                        <i class="fas fa-comment"></i> Follow Up
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="stage-item-dashboard" style="padding: 30px 20px; text-align: center; border: 1px dashed rgba(139,92,246,0.3); border-radius: 13px; background: rgba(9,17,34,0.3);">
                <i class="fas fa-users" style="font-size: 32px; color: var(--purple); opacity: 0.5; display: block; margin-bottom: 12px;"></i>
                <strong style="color: var(--text-primary); font-size: 13px; display: block; margin-bottom: 6px;">
                    <i class="fas fa-info-circle"></i> Belum ada creator
                </strong>
                <div style="color: var(--text-secondary); font-size: 11px; line-height: 1.6;">
                    Klik <strong>"Tambah Creator"</strong> atau <strong>"Import Excel"</strong> untuk mulai
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Button group -->
    <div style="flex-shrink: 0; display: flex; gap: 8px; margin-top: 8px; padding-top: 8px; border-top: 1px solid var(--border);">
        <button class="task-btn-dashboard" id="addCreatorQuickBtnDashboard" 
                style="flex: 1; height: 42px; border-radius: 12px; border: 1px solid rgba(124,60,255,0.26); background: rgba(124,60,255,0.10); color: #c084fc; font-weight: 600; cursor: pointer; transition: var(--transition); display: inline-flex; align-items: center; justify-content: center; gap: 6px; font-size: 12px;">
            <i class="fas fa-plus-circle"></i> Tambah Creator
        </button>
        <button class="task-btn-dashboard" id="importCreatorBtnDashboard" 
                style="flex: 1; height: 42px; border-radius: 12px; border: none; background: linear-gradient(135deg, #8b5cf6, #3b82f6); color: white; font-weight: 600; cursor: pointer; transition: var(--transition); display: inline-flex; align-items: center; justify-content: center; gap: 6px; font-size: 12px;">
            <i class="fas fa-file-upload"></i> Import Excel
        </button>
    </div>
</div>
        <!-- ============================================================ -->
        <!-- TASK 2: WAITING HANDLER - DEAL READY (LAMPU HIJAU) -->
        <!-- ============================================================ -->
     <div class="is-task-card task-2">
    <div class="is-task-header">
        <h3>
            <i class="fas fa-handshake orange"></i>
            2. WAITING HANDLER
            <span style="font-size: 10px; background: rgba(239,68,68,0.2); color: #ef4444; padding: 2px 8px; border-radius: 12px; margin-left: 6px;">
                 
            </span>
        </h3>
        <span class="is-task-badge" id="task2Count"><?= $task2_count ?? 0 ?></span>
    </div>
<div class="is-task-search">
    <input type="text" id="searchTask2" placeholder=" Cari creator..." onkeyup="filterTaskAjax('task2', this.value)">
</div>
    <div class="is-task-items" id="task2Items">
        <?php if (!empty($task2_creators)): ?>
    <?php foreach ($task2_creators as $creator): ?>
    <div class="is-item" 
             data-creator-id="<?= $creator->id ?? '' ?>" 
             data-creator-username="<?= htmlspecialchars($creator->username ?? '') ?>"
             data-task="2">

        <div class="is-item-header">
            <div class="is-item-avatar">
                <?php if ($creator->source_type == 'unregistered'): ?>
                    <i class="fas fa-user-plus" style="color: #f59e0b;"></i>
                <?php elseif (!empty($creator->avatar_url)): ?>
                    <img src="<?= htmlspecialchars($creator->avatar_url) ?>" alt="<?= htmlspecialchars($creator->username) ?>" onerror="this.parentElement.innerHTML='<i class=\\'fas fa-user\\'></i>'">
                <?php else: ?>
                    <i class="fas fa-user"></i>
                <?php endif; ?>
            </div>
            <div class="is-item-info">
                <div class="is-item-name">
                    <?= htmlspecialchars($creator->username) ?>
                    <?php if ($creator->deal_status == 'ready'): ?>
                        <span class="deal-ready"><i class="fas fa-circle"></i> READY TO CLAIM</span>
                    <?php elseif ($creator->deal_status == 'no_handler'): ?>
                        <span class="deal-ready" style="background: rgba(239,68,68,0.2); color: #ef4444; animation: pulse-red 2s infinite;">
                            <i class="fas fa-circle"></i> NO HANDLER 
                        </span>
                        <?php if ($creator->source_type == 'unregistered'): ?>
                            <span style="background: rgba(245,158,11,0.2); color: #f59e0b; padding: 2px 8px; border-radius: 12px; font-size: 9px; margin-left: 4px;">
                                 NEW
                            </span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="is-item-detail">
                    <span><i class="fas fa-tag"></i> <?= htmlspecialchars($creator->category ?? '-') ?></span>
                    <span class="gmv"><i class="fas fa-chart-line"></i> Rp <?= number_format($creator->total_gmv_30d ?? 0, 0, ',', '.') ?></span>
                    <span class="link-count"><i class="fas fa-link"></i> <?= $creator->total_active_links ?? 0 ?> link aktif</span>
                    <?php if ($creator->source_type == 'unregistered'): ?>
                        <span style="color: #f59e0b; font-size: 9px;">
                            <i class="fas fa-exclamation-circle"></i> Belum terdaftar
                        </span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($creator->top_product)): ?>
                <div class="is-item-product">
                    <span>�0�6 <?= htmlspecialchars(substr($creator->top_product, 0, 40)) ?>...</span>
                </div>
                <?php endif; ?>
                <?php if ($creator->deal_status == 'no_handler'): ?>
                <div class="is-item-detail" style="font-size:9px; color: #ef4444;">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <?= $creator->source_type == 'unregistered' ? 'Creator belum terdaftar di sistem! Klik CLAIM untuk register otomatis.' : 'Creator belum punya handler! Siapa cepat dia dapat.' ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="is-item-actions">
                <?php if ($creator->deal_status == 'ready' || $creator->deal_status == 'no_handler'): ?>
                  <button class="btn-claim" onclick="claimDeal('<?= $creator->id ?? '' ?>', '<?= htmlspecialchars($creator->username ?? '') ?>')" 
                        style="background: <?= $creator->deal_status == 'no_handler' ? 'linear-gradient(135deg, #ef4444, #dc2626)' : 'var(--is-green)' ?>;">
                    <i class="fas fa-hand-holding-heart"></i> DEAL
                </button>
                <?php endif; ?>
                <button class="btn-detail" onclick="showCreatorDetail(<?= $creator->id ?>)">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="is-empty">
        <i class="fas fa-inbox"></i>
        <p>Belum ada creator siap claim</p>
        <span style="font-size: 11px; color: var(--is-muted);">Creator akan muncul di sini saat:</span>
        <ul style="text-align: left; font-size: 11px; color: var(--is-muted); margin-top: 8px;">
            <li>�7�3 Ada link aktif & belum di-claim</li>
            <li>�7�3 Ada order tapi belum punya handler</li>
            <li>�7�3 Ada creator baru dengan order</li>
        </ul>
    </div>
<?php endif; ?>
    </div>
</div>

        <!-- ============================================================ -->
        <!-- TASK 3: MONITORING -->
        <!-- ============================================================ -->
        <div class="is-task-card task-3">
            <div class="is-task-header">
                <h3>
                    <i class="fas fa-chart-line green"></i>
                    3. MONITORING
                </h3>
                <span class="is-task-badge" id="task3Count"><?= count($task3_creators ?? []) ?></span>
                
            <button class="btn-add-creator-task3" 
                    onclick="openAddCreatorTask3()"
                    style="background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; padding: 4px 12px; border-radius: 16px; cursor: pointer; font-size: 10px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; transition: var(--transition);">
                <i class="fas fa-plus-circle"></i> Tambah
            </button>

            </div>
           <div class="is-task-search">
    <input type="text" id="searchTask3" placeholder=" Cari creator..." onkeyup="filterTaskAjax('task3', this.value)">
</div>
            <div class="is-task-items" id="task3Items">
                <?php if (!empty($task3_creators)): ?>
                    <?php foreach ($task3_creators as $creator): ?>
                     <div class="is-item" 
             data-creator-id="<?= $creator->id ?? '' ?>" 
             data-creator-username="<?= htmlspecialchars($creator->username ?? '') ?>"
             data-task="3">
                        <div class="is-item-header">
                            <div class="is-item-avatar">
                                <?php if (!empty($creator->avatar_url)): ?>
                                    <img src="<?= htmlspecialchars($creator->avatar_url) ?>" alt="<?= htmlspecialchars($creator->username) ?>" onerror="this.parentElement.innerHTML='<i class=\\'fas fa-user\\'></i>'">
                                <?php else: ?>
                                    <i class="fas fa-user"></i>
                                <?php endif; ?>
                            </div>
                            <div class="is-item-info">
                                <div class="is-item-name">
                                    <?= htmlspecialchars($creator->username) ?>
                                    <?php if (!empty($creator->handler_name)): ?>
                                        <span style="font-size: 9px; color: var(--is-purple); margin-left: 6px;">
                                            <i class="fas fa-user-tie"></i> <?= htmlspecialchars($creator->handler_name) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="is-item-detail">
                                    <span><i class="fas fa-tag"></i> <?= htmlspecialchars($creator->category ?? '-') ?></span>
                                    <span class="gmv"><i class="fas fa-chart-line"></i> Rp <?= number_format($creator->total_gmv_30d ?? 0, 0, ',', '.') ?></span>
                                    <span><i class="fas fa-shopping-cart"></i> <?= number_format($creator->total_orders_30d ?? 0) ?></span>
                                    <span class="link-count"><i class="fas fa-link"></i> <?= $creator->total_links ?? 0 ?></span>
                                </div>
                                <?php if (!empty($creator->top_product)): ?>
                                <div class="is-item-product">
                                    <?php if (!empty($creator->top_product_image)): ?>
                                    <div class="product-thumb">
                                        <img src="<?= htmlspecialchars($creator->top_product_image) ?>" onerror="this.parentElement.style.display='none'">
                                    </div>
                                    <?php endif; ?>
                                    <span>�0�6 <?= htmlspecialchars(substr($creator->top_product, 0, 40)) ?>...</span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($creator->brand_name)): ?>
                                <div class="is-item-detail" style="font-size:9px; color: var(--is-muted);">
                                    <i class="fas fa-store"></i> Brand: <?= htmlspecialchars($creator->brand_name) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="is-item-actions">
                                <button class="btn-detail" onclick="showCreatorDetail(<?= $creator->id ?>)"
                                    title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if (($creator->total_orders_30d ?? 0) > 0): ?>
                                <button class="btn-detail"
                                    style="background:rgba(139,92,246,0.15);color:#8b5cf6;border:1px solid rgba(139,92,246,0.3)"
                                    onclick="openDashboardWillingModal(<?= $creator->id ?>, '<?= htmlspecialchars($creator->username) ?>')"
                                    title="Proses Pengiriman Sample">
                                    <i class="fas fa-gift"></i>
                                </button>
                                <?php endif; ?>
                                <a href="<?= base_url('is/monitoring') ?>" 
                                    class="btn-detail"
                                    style="background:rgba(16,185,129,0.15);color:#10b981;border:1px solid rgba(16,185,129,0.3);text-decoration:none;display:inline-flex;align-items:center;justify-content:center"
                                    title="Halaman Monitoring">
                                    <i class="fas fa-chart-bar"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="is-empty">
                        <i class="fas fa-inbox"></i>
                        <p>Belum ada creator aktif</p>
                        <span style="font-size: 11px; color: var(--is-muted);">Creator akan muncul di sini setelah di-claim</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
<!-- ============================================================ -->
<!-- MODAL DETAIL TASK 1 (SCOUTING) -->
<!-- ============================================================ -->
<div id="task1DetailModal" class="modal-overlay-dashboard" style="display:none;">
    <div class="modal-glass-dashboard" style="max-width: 900px; width: 95%;">
        <div class="modal-header-dashboard">
            <h3 id="task1ModalTitle"><i class="fas fa-user"></i> Creator Detail</h3>
            <span class="modal-close-dashboard" onclick="closeTask1DetailModal()">&times;</span>
        </div>
        <div class="modal-body" id="task1ModalBody">
            <div style="text-align:center; padding:40px;">
                <i class="fas fa-spinner fa-pulse fa-2x"></i>
                <p>Loading...</p>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL UPDATE FASTMOSS COOKIE -->
<!-- ============================================================ -->
<div id="fastmossCookieModal" class="modal-overlay-dashboard" style="display:none; z-index: 9999;">
    <div class="modal-glass-dashboard" style="max-width: 550px; width: 95%; background: linear-gradient(160deg, #1e1b4b 0%, #0f172a 100%); border: 1px solid rgba(124, 60, 255, 0.45); border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.95), 0 0 35px rgba(124, 60, 255, 0.2);">
        <div class="modal-header-dashboard" style="border-bottom:1px solid rgba(255,255,255,0.08); padding: 16px 20px;">
            <h3 style="margin:0; font-size:16px; color:#fff; display:flex; align-items:center; gap:8px;"><i class="fas fa-key" style="color:var(--purple, #7c3cff);"></i> Update FastMoss Cookie</h3>
            <span class="modal-close-dashboard" onclick="closeCookieModal()" style="cursor:pointer; font-size:20px; color:#94a3b8; transition: 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94a3b8'">&times;</span>
        </div>
        <div class="modal-body" style="padding: 20px;">
            <div style="background: rgba(139,92,246,0.1); padding: 12px 16px; border-radius: 12px; border: 1px solid rgba(139,92,246,0.15); font-size: 11.5px; color: #c084fc; margin-bottom: 16px; line-height: 1.5;">
                <i class="fas fa-info-circle"></i> Tempel (Paste) perintah cURL request <strong>baseinfo</strong> FastMoss Anda di bawah ini. Sistem akan otomatis mengekstrak data session cookie baru Anda secara instan.
            </div>
            <textarea id="fastmossCookieInput" rows="6" placeholder="Paste cURL command (curl 'https://www.fastmoss.com/api/author/v3/detail/baseInfo?...) di sini..." style="width:100%; background:rgba(15, 23, 42, 0.6); border:1px solid rgba(124, 60, 255, 0.25); border-radius:10px; padding:12px; color:#fff; font-size:11.5px; outline:none; font-family:monospace; resize:vertical; line-height: 1.4; box-sizing: border-box; transition: 0.2s;" onfocus="this.style.borderColor='rgba(124, 60, 255, 0.6)';" onblur="this.style.borderColor='rgba(124, 60, 255, 0.25)';"></textarea>
            <div style="margin-top: 20px; display:flex; justify-content:flex-end; gap:10px;">
                <button onclick="closeCookieModal()" style="padding: 8px 18px; background:rgba(255, 255, 255, 0.05); border:1px solid rgba(255, 255, 255, 0.12); border-radius:8px; color:#fff; cursor:pointer; font-size:12px; font-weight: 500; transition: 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.08)';" onmouseout="this.style.background='rgba(255, 255, 255, 0.05)';">Batal</button>
                <button onclick="saveFastmossCookie()" style="padding: 8px 22px; background:linear-gradient(135deg, var(--purple, #7c3cff) 0%, #9333ea 100%); border:none; border-radius:8px; color:#fff; cursor:pointer; font-size:12px; font-weight:600; box-shadow: 0 4px 12px rgba(124, 60, 255, 0.25); transition: 0.2s;" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 16px rgba(124, 60, 255, 0.45)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 12px rgba(124, 60, 255, 0.25)';">Simpan Cookie</button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL SEND LINK TASK 1 -->
<!-- ============================================================ -->
<div id="task1SendLinkModal" class="modal-overlay-dashboard" style="display:none;">
    <div class="modal-glass-dashboard" style="max-width: 700px; width: 95%;">
        <div class="modal-header-dashboard">
            <h3 id="sendLinkModalTitle"><i class="fas fa-paper-plane"></i> Kirim Link</h3>
            <span class="modal-close-dashboard" onclick="closeTask1SendLinkModal()">&times;</span>
        </div>
        <div class="modal-body" id="sendLinkModalBody">
            <div style="text-align:center; padding:40px;">
                <i class="fas fa-spinner fa-pulse fa-2x"></i>
                <p>Loading...</p>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL FOLLOW UP TASK 1 -->
<!-- ============================================================ -->
<div id="task1FollowUpModal" class="modal-overlay-dashboard" style="display:none;">
    <div class="modal-glass-dashboard" style="max-width: 700px; width: 95%;">
        <div class="modal-header-dashboard">
            <h3 id="followUpModalTitle"><i class="fas fa-comment"></i> Follow Up</h3>
            <span class="modal-close-dashboard" onclick="closeTask1FollowUpModal()">&times;</span>
        </div>
        <div class="modal-body" id="followUpModalBody">
            <div style="text-align:center; padding:40px;">
                <i class="fas fa-spinner fa-pulse fa-2x"></i>
                <p>Loading...</p>
            </div>
        </div>
    </div>
</div>
<!-- ============================================================ -->
<!-- MODAL CREATOR DETAIL -->
<!-- ============================================================ -->
<div id="creatorModal" class="modal-overlay-dashboard" style="display:none;">
    <div class="modal-glass-dashboard" style="max-width: 800px; width: 95%;">
        <div class="modal-header-dashboard">
            <h3 id="creatorModalTitle"><i class="fas fa-user"></i> Creator Detail</h3>
            <span class="modal-close-dashboard" onclick="closeCreatorModal()">&times;</span>
        </div>
        <div class="modal-body" id="creatorModalBody">
            <div style="text-align:center; padding:40px;">
                <i class="fas fa-spinner fa-pulse fa-2x"></i>
                <p>Loading...</p>
            </div>
        </div>
    </div>
</div>
<!-- ============================================================ -->
<!-- MODAL TAMBAH CREATOR TASK 3 (DASHBOARD) -->
<!-- ============================================================ -->
<div id="addCreatorTask3Modal" class="modal-overlay-dashboard" style="display:none;">
    <div class="modal-glass-dashboard" style="max-width: 550px; width: 95%;">
        <div class="modal-header-dashboard">
            <h3 id="addCreatorTask3Title" style="display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-user-plus" style="color: #10b981;"></i> 
                Tambah Creator ke Task 3
            </h3>
            <span class="modal-close-dashboard" onclick="closeAddCreatorTask3()" style="font-size: 26px; cursor: pointer; color: #9aaebe;">&times;</span>
        </div>
        <div class="modal-body">
            <div style="background: rgba(16,185,129,0.1); border-radius:12px; padding:12px; margin-bottom:16px; border-left: 4px solid #10b981;">
                <p style="color: #10b981; font-size:13px; margin:0;">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Creator akan langsung masuk ke Task 3 (Monitoring)</strong> dengan status <strong>ACTIVE</strong>
                </p>
            </div>
            
            <form id="addCreatorTask3Form">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div style="grid-column: 1 / -1;">
                        <label style="color: var(--text-primary); font-weight:500; display:block; margin-bottom:4px; font-size:13px;">
                            <i class="fab fa-tiktok" style="color: #8b5cf6;"></i> Username TikTok *
                        </label>
                        <input type="text" id="task3Username" placeholder="@username_tiktok" required
                               style="width:100%; padding:10px 12px; background:rgba(255,255,255,0.05); border:1px solid var(--border); border-radius:12px; color:var(--text-primary); font-size:13px; outline:none;">
                    </div>
                    
                    <div>
                        <label style="color: var(--text-primary); font-weight:500; display:block; margin-bottom:4px; font-size:13px;">
                            <i class="fas fa-user"></i> Nama Lengkap
                        </label>
                        <input type="text" id="task3FullName" placeholder="Nama creator"
                               style="width:100%; padding:10px 12px; background:rgba(255,255,255,0.05); border:1px solid var(--border); border-radius:12px; color:var(--text-primary); font-size:13px; outline:none;">
                    </div>
                    
                    <div>
                        <label style="color: var(--text-primary); font-weight:500; display:block; margin-bottom:4px; font-size:13px;">
                            <i class="fas fa-tag"></i> Kategori
                        </label>
                        <select id="task3Category" style="width:100%; padding:10px 12px; background:rgba(255,255,255,0.05); border:1px solid var(--border); border-radius:12px; color:var(--text-primary); font-size:13px; outline:none;">
                            <option value="Beauty">Beauty</option>
                            <option value="Fashion">Fashion</option>
                            <option value="Tech">Tech</option>
                            <option value="Lifestyle" selected>Lifestyle</option>
                            <option value="Gaming">Gaming</option>
                            <option value="Food">Food</option>
                            <option value="Travel">Travel</option>
                            <option value="Sports">Sports</option>
                            <option value="Home & Living">Home & Living</option>
                            <option value="Health">Health</option>
                        </select>
                    </div>
                    
                    <div>
                        <label style="color: var(--text-primary); font-weight:500; display:block; margin-bottom:4px; font-size:13px;">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </label>
                        <input type="tel" id="task3Phone" placeholder="+62 812 3456 7890"
                               style="width:100%; padding:10px 12px; background:rgba(255,255,255,0.05); border:1px solid var(--border); border-radius:12px; color:var(--text-primary); font-size:13px; outline:none;">
                    </div>
                    
                    <div>
                        <label style="color: var(--text-primary); font-weight:500; display:block; margin-bottom:4px; font-size:13px;">
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <input type="email" id="task3Email" placeholder="email@example.com"
                               style="width:100%; padding:10px 12px; background:rgba(255,255,255,0.05); border:1px solid var(--border); border-radius:12px; color:var(--text-primary); font-size:13px; outline:none;">
                    </div>
                    
                    <div>
                        <label style="color: var(--text-primary); font-weight:500; display:block; margin-bottom:4px; font-size:13px;">
                            <i class="fas fa-users"></i> Followers
                        </label>
                        <input type="number" id="task3Followers" placeholder="0"
                               style="width:100%; padding:10px 12px; background:rgba(255,255,255,0.05); border:1px solid var(--border); border-radius:12px; color:var(--text-primary); font-size:13px; outline:none;">
                    </div>
                    
                    <div style="grid-column: 1 / -1;">
                        <label style="color: var(--text-primary); font-weight:500; display:block; margin-bottom:4px; font-size:13px;">
                            <i class="fas fa-store"></i> Brand
                        </label>
                        <select id="task3Brand" style="width:100%; padding:10px 12px; background:rgba(255,255,255,0.05); border:1px solid var(--border); border-radius:12px; color:var(--text-primary); font-size:13px; outline:none;">
                            <option value="">-- Pilih Brand --</option>
                        </select>
                    </div>
                </div>
                
                <div style="display:flex; gap:10px; margin-top:20px; padding-top:16px; border-top:1px solid var(--border);">
                    <button type="button" onclick="closeAddCreatorTask3()" style="flex:1; background:rgba(255,255,255,0.05); color:var(--text-secondary); padding:12px; border-radius:40px; border:1px solid var(--border); cursor:pointer; font-weight:600; font-size:13px;">Batal</button>
                    <button type="submit" id="submitTask3Btn" style="flex:1; background:linear-gradient(135deg, #10b981, #059669); color:white; padding:12px; border-radius:40px; border:none; cursor:pointer; font-weight:600; font-size:13px;">
                        <i class="fas fa-save"></i> Tambah ke Task 3
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL TAMBAH CREATOR TASK 1 (SCOUTING) -->
<!-- ============================================================ -->
<div id="taskModalDashboard" class="modal-overlay-dashboard" style="display:none;">
    <div class="modal-glass-dashboard" style="max-width: 550px; width: 95%;">
        <div class="modal-header-dashboard">
            <h3 id="modalTitleDashboard" style="display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-user-plus" style="color: var(--purple);"></i> 
                Tambah Creator ke Task 1
            </h3>
            <span class="modal-close-dashboard" id="closeTaskModalDashboard" style="font-size: 26px; cursor: pointer; color: #9aaebe;">&times;</span>
        </div>
        <div class="modal-body" id="modalBodyDashboard">
            <div style="background: rgba(139,92,246,0.1); border-radius:12px; padding:12px; margin-bottom:16px; border-left: 4px solid var(--purple);">
                <p style="color: var(--purple); font-size:13px; margin:0;">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Creator akan masuk ke Task 1 (Scouting)</strong> dengan status <strong>PENDING</strong>
                </p>
            </div>
            
            <form id="addCreatorTask1Form">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div style="grid-column: 1 / -1;">
                        <label style="color: var(--text-primary); font-weight:500; display:block; margin-bottom:4px; font-size:13px;">
                            <i class="fab fa-tiktok" style="color: #8b5cf6;"></i> Username TikTok *
                        </label>
                        <input type="text" id="creatorUsernameIS" placeholder="@username_tiktok" required
                               style="width:100%; padding:10px 12px; background:rgba(255,255,255,0.05); border:1px solid var(--border); border-radius:12px; color:var(--text-primary); font-size:13px; outline:none;">
                    </div>
                    
                    <div>
                        <label style="color: var(--text-primary); font-weight:500; display:block; margin-bottom:4px; font-size:13px;">
                            <i class="fas fa-user"></i> Nama Lengkap
                        </label>
                        <input type="text" id="creatorNameIS" placeholder="Nama creator"
                               style="width:100%; padding:10px 12px; background:rgba(255,255,255,0.05); border:1px solid var(--border); border-radius:12px; color:var(--text-primary); font-size:13px; outline:none;">
                    </div>
                    
                    <div>
                        <label style="color: var(--text-primary); font-weight:500; display:block; margin-bottom:4px; font-size:13px;">
                            <i class="fas fa-tag"></i> Kategori
                        </label>
                        <select id="creatorCategoryIS" style="width:100%; padding:10px 12px; background:rgba(255,255,255,0.05); border:1px solid var(--border); border-radius:12px; color:var(--text-primary); font-size:13px; outline:none;">
                            <option value="Beauty">Beauty</option>
                            <option value="Fashion">Fashion</option>
                            <option value="Tech">Tech</option>
                            <option value="Lifestyle" selected>Lifestyle</option>
                            <option value="Gaming">Gaming</option>
                            <option value="Food">Food</option>
                            <option value="Travel">Travel</option>
                            <option value="Sports">Sports</option>
                            <option value="Home & Living">Home & Living</option>
                            <option value="Health">Health</option>
                        </select>
                    </div>
                    
                    <div>
                        <label style="color: var(--text-primary); font-weight:500; display:block; margin-bottom:4px; font-size:13px;">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </label>
                        <input type="tel" id="creatorPhoneIS" placeholder="+62 812 3456 7890"
                               style="width:100%; padding:10px 12px; background:rgba(255,255,255,0.05); border:1px solid var(--border); border-radius:12px; color:var(--text-primary); font-size:13px; outline:none;">
                    </div>
                    
                    <div>
                        <label style="color: var(--text-primary); font-weight:500; display:block; margin-bottom:4px; font-size:13px;">
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <input type="email" id="creatorEmailIS" placeholder="email@example.com"
                               style="width:100%; padding:10px 12px; background:rgba(255,255,255,0.05); border:1px solid var(--border); border-radius:12px; color:var(--text-primary); font-size:13px; outline:none;">
                    </div>
                    
                    <div>
                        <label style="color: var(--text-primary); font-weight:500; display:block; margin-bottom:4px; font-size:13px;">
                            <i class="fas fa-users"></i> Followers
                        </label>
                        <input type="number" id="creatorFollowersIS" placeholder="0"
                               style="width:100%; padding:10px 12px; background:rgba(255,255,255,0.05); border:1px solid var(--border); border-radius:12px; color:var(--text-primary); font-size:13px; outline:none;">
                    </div>
                    
                    <div style="grid-column: 1 / -1;">
                        <label style="color: var(--text-primary); font-weight:500; display:block; margin-bottom:4px; font-size:13px;">
                            <i class="fas fa-store"></i> Brand
                        </label>
                        <select id="creatorBrandIS" style="width:100%; padding:10px 12px; background:rgba(255,255,255,0.05); border:1px solid var(--border); border-radius:12px; color:var(--text-primary); font-size:13px; outline:none;">
                            <option value="">-- Pilih Brand --</option>
                        </select>
                    </div>
                </div>
                
                <div style="display:flex; gap:10px; margin-top:20px; padding-top:16px; border-top:1px solid var(--border);">
                    <button type="button" onclick="closeModalIS()" style="flex:1; background:rgba(255,255,255,0.05); color:var(--text-secondary); padding:12px; border-radius:40px; border:1px solid var(--border); cursor:pointer; font-weight:600; font-size:13px;">Batal</button>
                    <button type="submit" id="saveCreatorBtnIS" style="flex:1; background:linear-gradient(135deg, var(--purple), var(--blue)); color:white; padding:12px; border-radius:40px; border:none; cursor:pointer; font-weight:600; font-size:13px;">
                        <i class="fas fa-save"></i> Tambah ke Task 1
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL SEND LINK TASK 1 - MULTI SELECT -->
<!-- ============================================================ -->
<div id="task1SendLinkModal" class="modal-overlay-dashboard" style="display:none;">
    <div class="modal-glass-dashboard" style="max-width: 750px; width: 95%; max-height: 90vh;">
        <div class="modal-header-dashboard">
            <h3 id="sendLinkModalTitle"><i class="fas fa-paper-plane" style="color: #4ade80;"></i> Kirim Link ke Creator</h3>
            <span class="modal-close-dashboard" onclick="closeTask1SendLinkModal()">&times;</span>
        </div>
        <div class="modal-body" id="sendLinkModalBody" style="max-height: 70vh; overflow-y: auto;">
            <div style="text-align:center; padding:40px;">
                <i class="fas fa-spinner fa-pulse fa-2x"></i>
                <p>Loading...</p>
            </div>
        </div>
    </div>
</div>
<!-- ============================================================ -->
<!-- JAVASCRIPT -->
<!-- ============================================================ -->
<script>
// ============================================================
// BASE URL CONFIG
// ============================================================
if (typeof BASE_URL === 'undefined') {
    var BASE_URL = '<?= base_url() ?>';
}

// ============================================================
// HELPER FUNCTIONS - GLOBAL
// ============================================================

function formatNumber(num) {
    if (num === undefined || num === null || isNaN(num)) return '0';
    return Number(num).toLocaleString('id-ID');
}

function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function showToastGlobal(message, type = 'success') {
    const existing = document.getElementById('globalToast');
    if (existing) existing.remove();
    
    const toast = document.createElement('div');
    toast.id = 'globalToast';
    
    const colors = {
        success: 'linear-gradient(135deg, #10b981, #059669)',
        error: 'linear-gradient(135deg, #ef4444, #dc2626)',
        warning: 'linear-gradient(135deg, #f59e0b, #d97706)',
        info: 'linear-gradient(135deg, #3b82f6, #2563eb)'
    };
    
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    toast.style.cssText = `
        position: fixed; bottom: 30px; right: 30px;
        background: ${colors[type] || colors.success};
        color: white; padding: 14px 24px; border-radius: 12px;
        font-size: 13px; font-weight: 500;
        z-index: 10001; box-shadow: 0 8px 30px rgba(0,0,0,0.4);
        max-width: 400px;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideInUp 0.3s ease;
        border: 1px solid rgba(255,255,255,0.1);
    `;
    
    toast.innerHTML = `<i class="fas ${icons[type] || icons.success}"></i> ${message}`;
    document.body.appendChild(toast);
    
    if (!document.getElementById('toastStyles')) {
        const style = document.createElement('style');
        style.id = 'toastStyles';
        style.textContent = `
            @keyframes slideInUp {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            @keyframes slideOutDown {
                from { opacity: 1; transform: translateY(0); }
                to { opacity: 0; transform: translateY(20px); }
            }
        `;
        document.head.appendChild(style);
    }
    
    setTimeout(() => {
        toast.style.animation = 'slideOutDown 0.3s ease';
        setTimeout(() => {
            if (toast.parentNode) toast.remove();
        }, 300);
    }, 3000);
}

function fallbackCopy(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    textarea.style.left = '-9999px';
    document.body.appendChild(textarea);
    textarea.select();
    try {
        document.execCommand('copy');
        showToastGlobal('�7�3 Link copied!', 'success');
    } catch (err) {
        showToastGlobal('Gagal copy link', 'error');
    }
    document.body.removeChild(textarea);
}

function copyMultiLink(link) {
    if (!link) {
        showToastGlobal('Link tidak tersedia', 'error');
        return;
    }
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(link).then(() => {
            showToastGlobal('�7�3 Multi link copied!', 'success');
        }).catch(() => {
            fallbackCopy(link);
        });
    } else {
        fallbackCopy(link);
    }
}

// ============================================================
// FETCH WITH ERROR HANDLING
// ============================================================

async function fetchWithErrorHandling(url, options = {}) {
    try {
        const response = await fetch(url, options);
        
        if (!response.ok) {
            if (response.status === 403 || response.status === 401) {
                showToastGlobal('Session expired. Silakan refresh halaman.', 'error');
                setTimeout(() => location.reload(), 2000);
                return null;
            }
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Response bukan JSON:', text.substring(0, 500));
            throw new Error('Server returned non-JSON response. Cek error log.');
        }
        
        return await response.json();
        
    } catch (error) {
        console.error('Fetch error:', error);
        showToastGlobal('Error: ' + error.message, 'error');
        return null;
    }
}

// ============================================================
// PRODUCT SELECTION FUNCTIONS - GLOBAL
// ============================================================

window.toggleProductSelection = function(productId) {
    console.log('toggleProductSelection called for productId:', productId);
    
    const checkbox = document.querySelector(`.product-checkbox[data-product-id="${productId}"]`);
    if (!checkbox) {
        console.log('Checkbox not found for productId:', productId);
        return;
    }
    
    // Update UI berdasarkan status checkbox
    const item = checkbox.closest('.product-link-item');
    if (item) {
        if (checkbox.checked) {
            item.classList.add('selected');
            item.style.borderColor = '#8b5cf6';
            item.style.background = 'rgba(139,92,246,0.15)';
        } else {
            item.classList.remove('selected');
            item.style.borderColor = 'rgba(255,255,255,0.06)';
            item.style.background = 'rgba(255,255,255,0.03)';
        }
    }
    
    window.updateSelectedCount();
};

window.selectAllProducts = function() {
    document.querySelectorAll('.product-checkbox').forEach(function(cb) {
        cb.checked = true;
        const item = cb.closest('.product-link-item');
        if (item) {
            item.classList.add('selected');
            item.style.borderColor = '#8b5cf6';
            item.style.background = 'rgba(139,92,246,0.15)';
        }
    });
    window.updateSelectedCount();
};

window.deselectAllProducts = function() {
    document.querySelectorAll('.product-checkbox').forEach(function(cb) {
        cb.checked = false;
        const item = cb.closest('.product-link-item');
        if (item) {
            item.classList.remove('selected');
            item.style.borderColor = 'rgba(255,255,255,0.06)';
            item.style.background = 'rgba(255,255,255,0.03)';
        }
    });
    window.updateSelectedCount();
};

window.clearSelectedLinks = function() {
    document.querySelectorAll('.product-checkbox').forEach(function(cb) {
        cb.checked = false;
        const item = cb.closest('.product-link-item');
        if (item) {
            item.classList.remove('selected');
            item.style.borderColor = 'rgba(255,255,255,0.06)';
            item.style.background = 'rgba(255,255,255,0.03)';
        }
    });
    window.updateSelectedCount();
};

window.updateSelectedCount = function() {
    const checkboxes = document.querySelectorAll('.product-checkbox:checked');
    const count = checkboxes.length;
    
    const countDisplay = document.getElementById('selectedCount');
    if (countDisplay) countDisplay.textContent = count;
    
    const linksCount = document.getElementById('selectedLinksCount');
    if (linksCount) linksCount.textContent = count;
    
    const summary = document.getElementById('selectedLinksSummary');
    const list = document.getElementById('selectedLinksList');
    
    if (count > 0 && summary && list) {
        summary.style.display = 'block';
        const links = [];
        checkboxes.forEach(function(cb) {
            const link = cb.getAttribute('data-link');
            const item = cb.closest('.product-link-item');
            let name = item ? item.getAttribute('data-name') || '' : '';
            if (!name && item) {
                const nameSpan = item.querySelector('span[style*="font-weight:500"]');
                if (nameSpan) name = nameSpan.textContent.trim();
            }
            if (link) {
                links.push({ link: link, name: name });
            }
        });
        list.innerHTML = links.map((item, idx) => 
            `<div style="padding:2px 0; border-bottom:1px solid rgba(139,92,246,0.1); font-size:10px;">
                ${idx+1}. ${escapeHtml(item.name.substring(0, 30))}${item.name.length > 30 ? '...' : ''}
                <br><span style="color:#6b7280; font-size:9px; word-break:break-all;">${escapeHtml(item.link)}</span>
            </div>`
        ).join('');
    } else if (summary) {
        summary.style.display = 'none';
    }
    
    const sendBtn = document.getElementById('sendLinkConfirmBtn');
    if (sendBtn) {
        sendBtn.disabled = (count === 0);
        sendBtn.style.opacity = (count === 0) ? '0.5' : '1';
        sendBtn.style.cursor = (count === 0) ? 'not-allowed' : 'pointer';
    }
};

// ============================================================
// UPDATE PHONE MODAL FUNCTIONS - GLOBAL
// ============================================================

window.openUpdatePhoneModal = function(creatorId, username) {
    console.log('openUpdatePhoneModal called with:', creatorId, username);
    
    const modal = document.createElement('div');
    modal.id = 'updatePhoneModal';
    modal.style.cssText = `
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.85); backdrop-filter: blur(8px);
        display: flex; align-items: center; justify-content: center;
        z-index: 10001;
    `;
    
    modal.innerHTML = `
        <div style="background: #111827; border-radius: 24px; padding: 24px; max-width: 400px; width: 90%; border: 1px solid #4ade80;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3 style="color:#e2f0e8; margin:0;">
                    <i class="fab fa-whatsapp" style="color: #25D366;"></i> Update WhatsApp
                </h3>
                <span onclick="window.closeUpdatePhoneModal()" style="font-size:24px; cursor:pointer; color:#9aaebe;">&times;</span>
            </div>
            <div style="margin-bottom:12px;">
                <p style="color:#9aaebe; font-size:13px;">Update nomor WhatsApp untuk <strong style="color:#e2f0e8;">@${escapeHtml(username)}</strong></p>
                <input type="tel" id="newPhoneInput" placeholder="+62 812 3456 7890" 
                       style="width:100%; padding:10px 12px; background:#0f1420; border:1px solid #2a3346; border-radius:12px; color:#e2f0e8; font-size:14px; outline:none;">
                <div style="margin-top:6px; font-size:10px; color:#6b7280;">
                    <i class="fas fa-info-circle"></i> Format: 6281234567890 atau +6281234567890
                </div>
            </div>
            <div style="display:flex; gap:10px;">
                <button onclick="window.closeUpdatePhoneModal()" style="flex:1; background:rgba(255,255,255,0.05); color:#9aaebe; padding:10px; border-radius:40px; border:1px solid #2a3346; cursor:pointer; font-weight:600;">Batal</button>
                <button onclick="window.savePhoneNumber('${creatorId}')" style="flex:1; background:linear-gradient(135deg, #25D366, #128C7E); color:white; padding:10px; border-radius:40px; border:none; cursor:pointer; font-weight:600;">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    setTimeout(() => {
        const input = document.getElementById('newPhoneInput');
        if (input) input.focus();
    }, 100);
};

window.closeUpdatePhoneModal = function() {
    const modal = document.getElementById('updatePhoneModal');
    if (modal) modal.remove();
};

window.savePhoneNumber = async function(creatorId) {
    const phoneInput = document.getElementById('newPhoneInput');
    if (!phoneInput) {
        showToastGlobal('Input tidak ditemukan', 'error');
        return;
    }
    
    let phone = phoneInput.value.trim();
    
    if (!phone) {
        showToastGlobal('Nomor WhatsApp tidak boleh kosong', 'error');
        phoneInput.style.borderColor = '#ef4444';
        setTimeout(() => phoneInput.style.borderColor = '#2a3346', 2000);
        return;
    }
    
    phone = phone.replace(/[^0-9+]/g, '');
    if (phone.startsWith('0')) {
        phone = '62' + phone.substring(1);
    } else if (phone.startsWith('+')) {
        phone = phone.substring(1);
    }
    
    if (phone.length < 10) {
        showToastGlobal('Nomor WhatsApp minimal 10 digit', 'error');
        phoneInput.style.borderColor = '#ef4444';
        setTimeout(() => phoneInput.style.borderColor = '#2a3346', 2000);
        return;
    }
    
    try {
        const response = await fetch(BASE_URL + 'is/update_creator_phone_task1', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                creator_id: creatorId,
                phone: phone
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToastGlobal('�7�3 Nomor WhatsApp berhasil diupdate!', 'success');
            window.closeUpdatePhoneModal();
            
            const phoneDisplay = document.getElementById('phoneDisplaySendLink');
            if (phoneDisplay) {
                phoneDisplay.innerHTML = `<i class="fab fa-whatsapp" style="color: #25D366;"></i> ${escapeHtml(result.phone || phone)}`;
            }
            
            const sendBtn = document.getElementById('sendLinkConfirmBtn');
            if (sendBtn) {
                sendBtn.disabled = false;
                sendBtn.style.background = 'linear-gradient(135deg, #25D366, #128C7E)';
                sendBtn.style.cursor = 'pointer';
                sendBtn.style.opacity = '1';
            }
            
            const infoDiv = document.querySelector('#sendLinkModalBody .modal-body [style*="background:rgba(245,158,11"]');
            if (infoDiv) {
                const titleEl = document.getElementById('sendLinkModalTitle');
                const username = titleEl ? titleEl.textContent.replace('Kirim Link - @', '') : 'creator';
                infoDiv.innerHTML = `
                    <span style="color:#f59e0b; font-size:10px; display:flex; align-items:center; gap:6px;">
                        <i class="fas fa-info-circle"></i> Link akan dikirim via WhatsApp ke @${escapeHtml(username)}
                    </span>
                `;
            }
        } else {
            showToastGlobal(result.message || 'Gagal update nomor', 'error');
        }
    } catch (error) {
        console.error('Save phone error:', error);
        showToastGlobal('Error: ' + error.message, 'error');
    }
};

// ============================================================
// SEND LINK MODAL FUNCTIONS - GLOBAL
// ============================================================

function closeTask1SendLinkModal() {
    const modal = document.getElementById('task1SendLinkModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('active');
    }
    window.closeUpdatePhoneModal();
}

async function showTask1SendLinkModal(creatorId) {
    console.log('showTask1SendLinkModal called with creatorId:', creatorId);
    
    if (!creatorId) {
        showToastGlobal('Creator ID tidak valid', 'error');
        return;
    }
    
    const modal = document.getElementById('task1SendLinkModal');
    const body = document.getElementById('sendLinkModalBody');
    const title = document.getElementById('sendLinkModalTitle');
    
    if (!modal || !body || !title) {
        showToastGlobal('Modal tidak ditemukan', 'error');
        return;
    }
    
    modal.style.display = 'flex';
    modal.classList.add('active');
    body.innerHTML = `
        <div style="text-align:center; padding:40px;">
            <i class="fas fa-spinner fa-pulse fa-2x" style="color: var(--purple);"></i>
            <p style="margin-top: 12px; color: var(--text-secondary);">Loading products...</p>
        </div>
    `;
    
    try {
        const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : window.location.origin + '/';
        
        const response = await fetch(baseUrl + 'is/get_creator_products_with_links', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ creator_id: creatorId })
        });
        
        // �9�7 CEK RESPONSE STATUS
        if (!response.ok) {
            const text = await response.text();
            console.error('Response error:', text);
            
            if (text.includes('Login') || text.includes('login')) {
                showToastGlobal('Session expired. Silakan login ulang.', 'error');
                setTimeout(() => {
                    window.location.href = baseUrl + 'auth/login';
                }, 2000);
                return;
            }
            
            throw new Error('HTTP ' + response.status);
        }
        
        const result = await response.json();
        console.log('Products result:', result);
        
        // �9�7 CEK SESSION EXPIRED DARI RESPONSE
        if (!result.success && result.redirect) {
            showToastGlobal('Session expired. Silakan login ulang.', 'error');
            setTimeout(() => {
                window.location.href = result.redirect;
            }, 2000);
            return;
        }
        
        if (!result.success) {
            body.innerHTML = `
                <div style="text-align:center; padding:40px; color: #ef4444;">
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                    <p style="margin-top: 12px;">${escapeHtml(result.message || 'Gagal load produk')}</p>
                    <button onclick="closeTask1SendLinkModal()" style="margin-top:16px; padding: 8px 24px; background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 8px; color: var(--text-primary); cursor: pointer;">Close</button>
                </div>
            `;
            return;
        }
        
        const c = result.creator;
        const allProducts = result.products || [];
        const hasPhone = c.phone && c.phone !== '';
        const assignedCount = result.assigned_count || 0;
        const creatorCount = result.creator_count || 0;
        const recommendedCount = result.recommended_count || 0;
        
        title.innerHTML = `<i class="fas fa-paper-plane" style="color: #4ade80;"></i> Kirim Link - @${escapeHtml(c.username)}`;
        
        if (allProducts.length === 0) {
            body.innerHTML = `
                <div style="text-align:center; padding:40px; color: #f59e0b;">
                    <i class="fas fa-link" style="font-size: 32px; display: block; margin-bottom: 12px; opacity: 0.5;"></i>
                    <p style="font-size: 15px; font-weight: 600;">Belum ada link tersedia</p>
                    <p style="font-size: 12px; color: var(--text-muted);">Tidak ada product dengan link afiliasi yang cocok dengan kategori <strong>${escapeHtml(c.category || 'Lifestyle')}</strong></p>
                    <p style="font-size: 11px; color: var(--text-muted); margin-top: 8px;">Pastikan BD sudah membuat link di <strong>bd_affiliate_links</strong></p>
                    <button onclick="closeTask1SendLinkModal()" style="margin-top:16px; padding: 8px 24px; background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 8px; color: var(--text-primary); cursor: pointer;">Tutup</button>
                </div>
            `;
            return;
        }
        
        let html = `
            <div style="background:rgba(74,222,128,0.1); border-radius:14px; padding:12px; margin-bottom:16px; border: 1px solid rgba(74,222,128,0.2);">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap: 8px;">
                    <div>
                        <div style="color:var(--text-primary); font-weight:600; font-size:14px;">@${escapeHtml(c.username)}</div>
                        <div style="color:var(--text-muted); font-size:11px; display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                            <span><i class="fas fa-tag" style="color: var(--purple);"></i> ${escapeHtml(c.category || 'Lifestyle')}</span>
                            <span id="phoneDisplaySendLink" style="display:inline-flex; align-items:center; gap:4px;">
                                <i class="fab fa-whatsapp" style="color: ${hasPhone ? '#25D366' : '#ef4444'};"></i> 
                                ${hasPhone ? escapeHtml(c.phone) : '<span style="color:#ef4444;">Tidak ada nomor WhatsApp!</span>'}
                            </span>
                            ${!hasPhone ? `
                            <button onclick="window.openUpdatePhoneModal('${c.id}', '${escapeHtml(c.username)}')" 
                                    style="background: #f59e0b; color: #0a0e17; border: none; padding: 2px 12px; border-radius: 12px; cursor: pointer; font-size: 9px; font-weight: 600;">
                                <i class="fas fa-edit"></i> Update WA
                            </button>
                            ` : ''}
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div style="color:#4ade80; font-size:12px;">
                            ${allProducts.length} products
                            ${assignedCount > 0 ? `<span style="color:#10b981; margin-left:8px;">�7�7 ${assignedCount} assigned</span>` : ''}
                            ${creatorCount > 0 ? `<span style="color:#4ade80; margin-left:8px;">�� ${creatorCount} creator</span>` : ''}
                            ${recommendedCount > 0 ? `<span style="color:#8b5cf6; margin-left:8px;">�� ${recommendedCount} recommended</span>` : ''}
                        </div>
                        <div style="font-size:10px; color:var(--text-muted);">
                            <span id="selectedCount">0</span> selected
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="display:flex; gap:8px; margin-bottom:10px; flex-wrap:wrap;">
                <button onclick="window.selectAllProducts()" style="background:rgba(139,92,246,0.15); color:#8b5cf6; border:1px solid rgba(139,92,246,0.3); padding:4px 14px; border-radius:16px; cursor:pointer; font-size:10px; font-weight:600;">
                    <i class="fas fa-check-double"></i> Select All
                </button>
                <button onclick="window.deselectAllProducts()" style="background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.2); padding:4px 14px; border-radius:16px; cursor:pointer; font-size:10px; font-weight:600;">
                    <i class="fas fa-times"></i> Deselect All
                </button>
                <span style="font-size:10px; color:var(--text-muted); padding:4px 8px;">
                    <i class="fas fa-info-circle"></i> Pilih satu atau lebih link untuk dikirim
                </span>
            </div>
            
            <div id="productLinkList" style="max-height: 280px; overflow-y: auto; padding-right: 4px; margin-bottom: 12px; border: 1px solid var(--border); border-radius: 12px; padding: 8px;">
                ${(() => {
                    function renderProductRow(p) {
                        let sourceClass = '';
                        let sourceBadge = '';
                        let borderColor = 'rgba(255,255,255,0.06)';
                        let bgColor = 'rgba(255,255,255,0.03)';
                        let leftBorder = '';
                        
                        const isPalette = p.link_type === 'palette' || p.link_type === 'multi' || (p.product_id && p.product_id.startsWith('palette_'));
                        
                        if (isPalette) {
                            sourceClass = 'palette';
                            borderColor = 'rgba(236,72,153,0.4)';
                            bgColor = 'rgba(236,72,153,0.1)';
                            leftBorder = 'border-left: 3px solid #ec4899;';
                            sourceBadge = `<span style="background:rgba(236,72,153,0.2); color:#ec4899; font-size:7px; padding:2px 10px; border-radius:12px; font-weight:700; white-space:nowrap; border:1px solid rgba(236,72,153,0.3);">
                                <i class="fas fa-folder-open"></i> PALETTE LINK
                            </span>`;
                        } else if (p.is_assigned) {
                            sourceClass = 'assigned';
                            borderColor = 'rgba(16,185,129,0.4)';
                            bgColor = 'rgba(16,185,129,0.12)';
                            leftBorder = 'border-left: 3px solid #10b981;';
                            sourceBadge = `<span style="background:rgba(16,185,129,0.2); color:#10b981; font-size:7px; padding:2px 10px; border-radius:12px; font-weight:700; white-space:nowrap; border:1px solid rgba(16,185,129,0.3);">
                                <i class="fas fa-check-circle"></i> ASSIGNED
                            </span>`;
                        } else if (p.source === 'creator_product') {
                            sourceClass = 'available';
                            borderColor = 'rgba(74,222,128,0.3)';
                            bgColor = 'rgba(74,222,128,0.08)';
                            leftBorder = 'border-left: 3px solid #4ade80;';
                            sourceBadge = `<span style="background:rgba(74,222,128,0.15); color:#4ade80; font-size:7px; padding:2px 10px; border-radius:12px; font-weight:600; white-space:nowrap; border:1px solid rgba(74,222,128,0.2);">
                                <i class="fas fa-user"></i> FROM CREATOR
                            </span>`;
                        } else if (p.source === 'recommended') {
                            sourceClass = 'recommended';
                            borderColor = 'rgba(139,92,246,0.3)';
                            bgColor = 'rgba(139,92,246,0.08)';
                            leftBorder = 'border-left: 3px solid #8b5cf6;';
                            sourceBadge = `<span style="background:rgba(139,92,246,0.15); color:#8b5cf6; font-size:7px; padding:2px 10px; border-radius:12px; font-weight:600; white-space:nowrap; border:1px solid rgba(139,92,246,0.2);">
                                <i class="fas fa-star"></i> RECOMMENDED
                            </span>`;
                        } else {
                            sourceClass = 'unmatched';
                            borderColor = 'rgba(245,158,11,0.3)';
                            bgColor = 'rgba(245,158,11,0.05)';
                            leftBorder = 'border-left: 3px solid #f59e0b;';
                            sourceBadge = `<span style="background:rgba(245,158,11,0.15); color:#f59e0b; font-size:7px; padding:2px 10px; border-radius:12px; font-weight:600; white-space:nowrap; border:1px solid rgba(245,158,11,0.2);">
                                <i class="fas fa-clock"></i> UNMATCHED
                            </span>`;
                        }
                        
                        return `
                        <div class="product-link-item ${sourceClass}" 
                             data-product-id="${p.product_id}"
                             data-link="${escapeHtml(p.affiliate_link || '')}"
                             data-campaign="${escapeHtml(p.campaign_id || '')}"
                             data-name="${escapeHtml(p.product_name || '').toLowerCase()}"
                             data-shop="${escapeHtml(p.shop_name || '').toLowerCase()}"
                             style="background: ${bgColor}; 
                                    border: 1px solid ${borderColor};
                                    border-radius: 10px; 
                                    padding: 8px 10px; 
                                    margin-bottom: 6px; 
                                    transition: all 0.2s ease;
                                    display: flex;
                                    align-items: center;
                                    gap: 10px;
                                    cursor: pointer;
                                    ${leftBorder}">
                            
                            <input type="checkbox" class="product-checkbox" 
                                   data-product-id="${p.product_id}"
                                   data-link="${escapeHtml(p.affiliate_link || '')}"
                                   data-campaign="${escapeHtml(p.campaign_id || '')}"
                                   ${p.is_assigned ? 'checked' : ''}
                                   style="width:16px; height:16px; cursor:pointer; accent-color:#8b5cf6; flex-shrink:0; pointer-events:none;">
                            
                            ${isPalette ? `<div style="width:36px; height:36px; border-radius:6px; background:rgba(236,72,153,0.2); display:flex; align-items:center; justify-content:center; flex-shrink:0;"><i class="fas fa-folder-open" style="color:#ec4899;"></i></div>` : p.image_url ? `<img src="${escapeHtml(p.image_url)}" style="width:36px; height:36px; border-radius:6px; object-fit:cover; flex-shrink:0;" onerror="this.src=''; this.onerror=null; this.parentElement.innerHTML='<div style=\\'width:36px;height:36px;border-radius:6px;background:var(--bg-elevated);display:flex;align-items:center;justify-content:center;flex-shrink:0;\\'><i class=\\'fas fa-box\\' style=\\'color:var(--text-muted);\\'></i></div>'">` : '<div style="width:36px; height:36px; border-radius:6px; background:var(--bg-elevated); display:flex; align-items:center; justify-content:center; flex-shrink:0;"><i class="fas fa-box" style="color:var(--text-muted);"></i></div>'}
                            
                            <div style="flex:1; min-width:0;">
                                <div style="display:flex; align-items:center; gap:4px; flex-wrap:wrap;">
                                    <span style="font-weight:500; color:var(--text-primary); font-size:12px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:200px;">
                                        ${escapeHtml(p.product_name.substring(0, 50))}
                                    </span>
                                    ${sourceBadge}
                                    ${p.shop_name ? `<span style="color:var(--text-muted); font-size:9px; white-space:nowrap;"><i class="fas fa-store"></i> ${escapeHtml(p.shop_name)}</span>` : ''}
                                </div>
                                <div style="display:flex; gap:8px; margin-top:2px; font-size:9px; color:var(--text-muted); flex-wrap:wrap;">
                                    <span><i class="fas fa-tag"></i> ${p.commission_rate || 0}%</span>
                                    ${p.sales_count ? `<span><i class="fas fa-chart-line"></i> ${formatNumber(p.sales_count)} sold</span>` : ''}
                                    ${p.gmv ? `<span><i class="fas fa-dollar-sign"></i> Rp ${formatNumber(p.gmv)}</span>` : ''}
                                    ${p.bd_created_by ? `<span><i class="fas fa-user"></i> ${escapeHtml(p.bd_created_by)}</span>` : ''}
                                    ${p.is_matched ? `<span style="color:#4ade80;"><i class="fas fa-check"></i> Match</span>` : ''}
                                    ${p.is_assigned ? `<span style="color:#10b981;"><i class="fas fa-check-circle"></i> Assigned</span>` : ''}
                                </div>
                            </div>
                            
                            <div style="text-align:right; flex-shrink:0;">
                                <div style="font-size:10px; color:${isPalette ? '#ec4899' : p.is_assigned ? '#10b981' : p.source === 'creator_product' ? '#4ade80' : p.source === 'recommended' ? '#8b5cf6' : '#f59e0b'}; font-weight:600;">
                                    ${isPalette ? '<i class="fas fa-folder-open"></i>' : p.is_assigned ? '<i class="fas fa-check-circle"></i>' : p.source === 'creator_product' ? '<i class="fas fa-user"></i>' : p.source === 'recommended' ? '<i class="fas fa-star"></i>' : '<i class="fas fa-clock"></i>'}
                                </div>
                            </div>
                        </div>`;
                    }

                    const colProducts = allProducts.filter(p => p.source !== 'recommended');
                    const nonColProducts = allProducts.filter(p => p.source === 'recommended');

                    return `
                    <!-- Group 1: Brand Sudah Bekerja Sama (No Header) -->
                    <div id="collaboratedProductsList" style="margin-bottom: 12px;">
                        ${colProducts.length > 0 ? colProducts.map(p => renderProductRow(p)).join('') : `<div class="no-products-placeholder" style="padding:10px; text-align:center; color:var(--text-muted); font-size:10px; border:1px dashed rgba(255,255,255,0.06); border-radius:8px;">Tidak ada produk dari brand partner</div>`}
                    </div>

                    <!-- Search bar in the middle -->
                    <div id="searchBarMiddleWrapper" style="margin-top: 10px; margin-bottom: 10px;">
                        <input type="text" id="searchProductLink" placeholder="🔍 Cari produk..." 
                               style="width:100%; padding:8px 12px; background:rgba(255,255,255,0.05); border:1px solid var(--border); border-radius:10px; color:var(--text-primary); font-size:12px; outline:none;">
                    </div>

                    <!-- Group 2: Recommended Products -->
                    <div class="product-group-header non-collaborated-header" style="font-size: 11px; font-weight: 700; color: #8b5cf6; margin-top: 8px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                        <i class="fas fa-star"></i> Rekomendasi Produk (Recommended)
                    </div>
                    <div id="nonCollaboratedProductsList">
                        ${nonColProducts.length > 0 ? nonColProducts.map(p => renderProductRow(p)).join('') : `<div class="no-products-placeholder" style="padding:10px; text-align:center; color:var(--text-muted); font-size:10px; border:1px dashed rgba(255,255,255,0.06); border-radius:8px;">Tidak ada rekomendasi produk baru</div>`}
                    </div>
                    `;
                })()}
            </div>
            
            <div id="selectedLinksSummary" style="background:rgba(139,92,246,0.08); border-radius:10px; padding:10px 12px; margin-bottom:12px; border-left:3px solid #8b5cf6; display:none;">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:4px;">
                    <div style="font-size:10px; color:var(--text-muted);">
                        <i class="fas fa-link"></i> Selected Links: <span id="selectedLinksCount">0</span>
                    </div>
                    <button onclick="window.clearSelectedLinks()" style="background:rgba(239,68,68,0.1); color:#ef4444; border:none; padding:2px 10px; border-radius:12px; cursor:pointer; font-size:9px;">Clear All</button>
                </div>
                <div id="selectedLinksList" style="max-height:60px; overflow-y:auto; margin-top:4px; font-size:10px; color:#8b5cf6; word-break:break-all;">
                </div>
            </div>
            
            <label style="color:var(--text-primary); font-weight:500; display:block; margin-top:8px; margin-bottom:5px; font-size:13px;">
                <i class="fas fa-edit" style="color: #f59e0b;"></i> Pesan WhatsApp
            </label>
            <textarea id="sendLinkMessage" rows="3" style="width:100%; padding:10px 12px; background:rgba(255,255,255,0.05); border:1px solid var(--border); border-radius:12px; color:var(--text-primary); font-size:12px; outline: none; transition: var(--transition); resize: vertical; font-family: inherit;">
Halo! �9�9

Terima kasih telah bergabung dengan Toopai. 

Berikut adalah link afiliasi yang dapat Anda gunakan untuk mempromosikan produk:

[LINK]

Selamat berpromosi! �0�4

Tim Toopai
            </textarea>
            
            <div style="background:rgba(245,158,11,0.1); border-radius:10px; padding:10px 12px; margin-top:8px; border-left:3px solid #f59e0b;">
                <span style="color:#f59e0b; font-size:10px; display:flex; align-items:center; gap:6px;">
                    <i class="fas fa-info-circle"></i> 
                    ${hasPhone ? `Link akan dikirim via WhatsApp ke @${escapeHtml(c.username)}` : '�7�2�1�5 <strong>Nomor WhatsApp tidak tersedia!</strong> Silakan update nomor terlebih dahulu.'}
                </span>
            </div>
            
            <div style="display:flex; gap:10px; margin-top:16px;">
                <button id="sendLinkConfirmBtn" 
                        style="flex:1; background:${hasPhone ? 'linear-gradient(135deg, #25D366, #128C7E)' : '#6b7280'}; 
                               color:white; padding:12px; border-radius:40px; border:none; 
                               cursor:${hasPhone ? 'pointer' : 'not-allowed'}; 
                               font-weight:600; font-size:13px; 
                               transition: var(--transition); 
                               display:flex; align-items:center; justify-content:center; gap:8px;"
                        ${!hasPhone ? 'disabled' : ''}>
                    <i class="fab fa-whatsapp"></i> Kirim WA
                </button>
                <button onclick="closeTask1SendLinkModal()" style="flex:1; background:rgba(255,255,255,0.05); color:var(--text-secondary); padding:12px; border-radius:40px; border:1px solid var(--border); cursor:pointer; font-weight:600; font-size:13px; transition: var(--transition);">Batal</button>
            </div>
        `;
        
        body.innerHTML = html;
        
        // ============================================================
        // �9�7 EVENT LISTENER: SEARCH PRODUCT
        // ============================================================
        const searchInput = document.getElementById('searchProductLink');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const keyword = this.value.toLowerCase().trim();
                const items = document.querySelectorAll('.product-link-item');
                
                items.forEach(item => {
                    const name = item.getAttribute('data-name') || '';
                    const shop = item.getAttribute('data-shop') || '';
                    const match = keyword === '' || name.includes(keyword) || shop.includes(keyword);
                    item.style.display = match ? 'flex' : 'none';
                });

                // Toggle group header visibility based on search results
                const nonColList = document.getElementById('nonCollaboratedProductsList');
                if (nonColList) {
                    const visibleNonCol = Array.from(nonColList.querySelectorAll('.product-link-item')).some(el => el.style.display !== 'none');
                    const nonColHeader = document.querySelector('.non-collaborated-header');
                    if (nonColHeader) nonColHeader.style.display = visibleNonCol ? 'flex' : 'none';
                }
            });
        }
        
        // ============================================================
        // �9�7 EVENT DELEGATION: KLIK PADA PRODUCT ITEM
        // ============================================================
        const productList = document.getElementById('productLinkList');
        if (productList) {
            productList.addEventListener('click', function(e) {
                const target = e.target;
                const item = target.closest('.product-link-item');
                if (!item) return;
                
                const productId = item.getAttribute('data-product-id');
                if (!productId) return;
                
                if (target.tagName === 'INPUT' && target.type === 'checkbox') {
                    target.checked = !target.checked;
                    window.toggleProductSelection(productId);
                    e.stopPropagation();
                    return;
                }
                
                const checkbox = item.querySelector('.product-checkbox');
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    window.toggleProductSelection(productId);
                }
            });
            
            productList.addEventListener('change', function(e) {
                if (e.target.tagName === 'INPUT' && e.target.type === 'checkbox') {
                    const productId = e.target.getAttribute('data-product-id');
                    if (productId) {
                        const checkbox = e.target;
                        const item = checkbox.closest('.product-link-item');
                        if (item) {
                            if (checkbox.checked) {
                                item.classList.add('selected');
                                item.style.borderColor = '#8b5cf6';
                                item.style.background = 'rgba(139,92,246,0.15)';
                            } else {
                                item.classList.remove('selected');
                                item.style.borderColor = 'rgba(255,255,255,0.06)';
                                item.style.background = 'rgba(255,255,255,0.03)';
                            }
                        }
                        window.updateSelectedCount();
                    }
                }
            });
        }
        
        // ============================================================
        // �9�7 INITIAL COUNT
        // ============================================================
        window.updateSelectedCount();
        
        // ============================================================
        // �9�7 EVENT LISTENER: SEND BUTTON
        // ============================================================
        document.getElementById('sendLinkConfirmBtn').addEventListener('click', async function() {
            const selected = document.querySelectorAll('.product-checkbox:checked');
            
            if (selected.length === 0) {
                showToastGlobal('Pilih minimal satu produk', 'error');
                return;
            }
            
            const phoneDisplay = document.getElementById('phoneDisplaySendLink');
            const phoneText = phoneDisplay ? phoneDisplay.textContent.trim() : '';
            const hasPhoneNumber = phoneText && phoneText !== 'Tidak ada nomor WhatsApp!' && phoneText !== '';
            
            if (!hasPhoneNumber) {
                showToastGlobal('Nomor WhatsApp tidak tersedia! Silakan update nomor terlebih dahulu.', 'error');
                return;
            }
            
            const links = [];
            const productIds = [];
            const campaignIds = [];
            const productNames = [];
            
            selected.forEach(function(cb) {
                const link = cb.getAttribute('data-link');
                const productId = cb.getAttribute('data-product-id');
                const campaignId = cb.getAttribute('data-campaign');
                const item = cb.closest('.product-link-item');
                
                let name = item ? item.getAttribute('data-name') || '' : '';
                if (!name && item) {
                    const nameSpan = item.querySelector('span[style*="font-weight:500"]');
                    if (nameSpan) name = nameSpan.textContent.trim();
                }
                
                if (link) {
                    links.push(link);
                    productIds.push(productId);
                    campaignIds.push(campaignId);
                    productNames.push(name || 'Produk ' + (productIds.length + 1));
                }
            });
            
            if (links.length === 0) {
                showToastGlobal('Tidak ada link yang valid untuk produk yang dipilih', 'error');
                return;
            }
            
            let message = document.getElementById('sendLinkMessage').value;
            const linkText = links.map((l, i) => {
                const name = productNames[i] || 'Produk ' + (i+1);
                return `${i+1}. ${name}\n   ${l}`;
            }).join('\n');
            const finalMessage = message.replace('[LINK]', linkText).trim();
            
            if (!finalMessage || finalMessage === '') {
                showToastGlobal('Pesan tidak boleh kosong', 'error');
                return;
            }
            
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Mengirim...';
            
            try {
                const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : window.location.origin + '/';
                let successCount = 0;
                let failCount = 0;
                
                for (let i = 0; i < links.length; i++) {
                    const response = await fetch(baseUrl + 'is/send_link_task1', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            creator_id: creatorId,
                            product_id: productIds[i] || '',
                            link: links[i],
                            message: finalMessage,
                            campaign_id: campaignIds[i] || ''
                        })
                    });
                    
                    const result = await response.json();
                    if (result.success) {
                        successCount++;
                    } else {
                        failCount++;
                    }
                }
                
                if (successCount > 0) {
                    showToastGlobal(`�7�3 ${successCount} link berhasil dikirim!${failCount > 0 ? ` (${failCount} gagal)` : ''}`, 'success');
                    
                    const phone = phoneText.replace(/[^0-9]/g, '');
                    const waUrl = 'https://wa.me/' + phone + '?text=' + encodeURIComponent(finalMessage);
                    window.open(waUrl, '_blank');
                    
                    setTimeout(() => {
                        closeTask1SendLinkModal();
                        location.reload();
                    }, 2000);
                } else {
                    showToastGlobal('Gagal mengirim link', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fab fa-whatsapp"></i> Kirim WA';
                }
                
            } catch (error) {
                console.error('Send link error:', error);
                showToastGlobal('Error: ' + error.message, 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fab fa-whatsapp"></i> Kirim WA';
            }
        });
        
        // ============================================================
        // �9�7 CLOSE MODAL ON OVERLAY CLICK
        // ============================================================
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeTask1SendLinkModal();
            }
        });
        
        // ============================================================
        // �9�7 CLOSE MODAL ON ESC
        // ============================================================
        const escHandler = function(e) {
            if (e.key === 'Escape') {
                closeTask1SendLinkModal();
                document.removeEventListener('keydown', escHandler);
            }
        };
        document.addEventListener('keydown', escHandler);
        
    } catch (error) {
        console.error('Error:', error);
        body.innerHTML = `
            <div style="text-align:center; padding:40px; color: #ef4444;">
                <i class="fas fa-exclamation-triangle fa-2x"></i>
                <p style="margin-top: 12px;">Error: ${escapeHtml(error.message)}</p>
                <button onclick="closeTask1SendLinkModal()" style="margin-top:16px; padding: 8px 24px; background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 8px; color: var(--text-primary); cursor: pointer;">Close</button>
            </div>
        `;
    }
}

// ============================================================
// FOLLOW UP MODAL FUNCTIONS - GLOBAL
// ============================================================

function closeTask1FollowUpModal() {
    const modal = document.getElementById('task1FollowUpModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('active');
    }
}

async function showTask1FollowUpModal(creatorId) {
    const modal = document.getElementById('task1FollowUpModal');
    const body = document.getElementById('followUpModalBody');
    const title = document.getElementById('followUpModalTitle');
    
    if (!modal || !body || !title) {
        showToastGlobal('Modal tidak ditemukan', 'error');
        return;
    }
    
    modal.style.display = 'flex';
    modal.classList.add('active');
    body.innerHTML = `
        <div style="text-align:center; padding:40px;">
            <i class="fas fa-spinner fa-pulse fa-2x" style="color: var(--purple);"></i>
            <p style="margin-top: 12px; color: var(--text-secondary);">Loading...</p>
        </div>
    `;
    
    const result = await fetchWithErrorHandling(BASE_URL + 'is/get_creator_task1_detail', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ creator_id: creatorId })
    });
    
    if (!result) return;
    
    if (!result.success) {
        body.innerHTML = `
            <div style="text-align:center; padding:40px; color: #ef4444;">
                <i class="fas fa-exclamation-triangle fa-2x"></i>
                <p style="margin-top: 12px;">${escapeHtml(result.message)}</p>
                <button onclick="closeTask1FollowUpModal()" style="margin-top:16px; padding: 8px 24px; background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 8px; color: var(--text-primary); cursor: pointer;">Close</button>
            </div>
        `;
        return;
    }
    
    const c = result.creator;
    const logs = result.whatsapp_logs || [];
    const followUpCount = c.follow_up_count || 0;
    
    title.innerHTML = `<i class="fas fa-comment" style="color: #f59e0b;"></i> Follow Up - @${escapeHtml(c.username)}`;
    
    let html = `
        <div style="background:rgba(245,158,11,0.1); border-radius:14px; padding:12px; margin-bottom:16px; border: 1px solid rgba(245,158,11,0.2);">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap: 8px;">
                <div>
                    <div style="color:var(--text-primary); font-weight:600; font-size:14px;">@${escapeHtml(c.username)}</div>
                    <div style="color:var(--text-muted); font-size:11px;">
                        <i class="fab fa-whatsapp" style="color: #25D366;"></i> ${escapeHtml(c.phone || 'No WhatsApp')}
                        ${followUpCount > 0 ? `<span style="color:#f59e0b; margin-left:8px;"><i class="fas fa-clock"></i> Follow Up: ${followUpCount}x</span>` : ''}
                    </div>
                </div>
            </div>
        </div>
        
        ${logs.length > 0 ? `
        <div style="margin-bottom:12px;">
            <h4 style="color:var(--text-muted); font-size:11px; margin-bottom:6px;">�9�5 Riwayat Pesan Terakhir:</h4>
            <div style="max-height:80px; overflow-y:auto; background:var(--bg-elevated); border-radius:8px; padding:6px 10px; border:1px solid var(--border); font-size:10px;">
        ` : ''}
        
        ${logs.slice(0, 3).map(log => `
            <div style="display:flex; justify-content:space-between; padding:4px 0; border-bottom:1px solid var(--border);">
                <span style="color:var(--text-secondary); max-width:70%; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${escapeHtml(log.message.substring(0, 50))}</span>
                <span style="color:var(--text-muted); flex-shrink:0; margin-left:8px;">${new Date(log.sent_at).toLocaleDateString('id-ID')}</span>
            </div>
        `).join('')}
        
        ${logs.length > 0 ? `
            </div>
        </div>
        ` : ''}
        
        <label style="color:var(--text-primary); font-weight:500; display:block; margin-top:12px; margin-bottom:5px; font-size:13px;">
            <i class="fas fa-edit" style="color: #f59e0b;"></i> Pesan Follow Up
        </label>
        <textarea id="followUpMessage" rows="4" style="width:100%; padding:10px 12px; background:var(--bg-elevated); border:1px solid var(--border); border-radius:12px; color:var(--text-primary); font-size:12px; outline: none; transition: var(--transition); resize: vertical; font-family: inherit;">
Halo! �9�9

Kami dari Toopai ingin menanyakan apakah link afiliasi yang kami kirim sudah diterima?

Jangan ragu untuk menghubungi kami jika ada pertanyaan atau kendala.

Terima kasih! �0�5

Tim Toopai
        </textarea>
        
        <div style="background:rgba(245,158,11,0.1); border-radius:10px; padding:10px 12px; margin-top:8px; border-left:3px solid #f59e0b;">
            <span style="color:#f59e0b; font-size:10px; display:flex; align-items:center; gap:6px;">
                <i class="fas fa-info-circle"></i> Follow up akan dikirim via WhatsApp ke @${escapeHtml(c.username)}
            </span>
        </div>
        
        <div style="display:flex; gap:10px; margin-top:16px;">
            <button id="followUpConfirmBtn" style="flex:1; background:linear-gradient(135deg, #f59e0b, #d97706); color:white; padding:12px; border-radius:40px; border:none; cursor:pointer; font-weight:600; font-size:13px; transition: var(--transition); display:flex; align-items:center; justify-content:center; gap:8px;">
                <i class="fab fa-whatsapp"></i> Kirim Follow Up
            </button>
            <button onclick="closeTask1FollowUpModal()" style="flex:1; background:var(--bg-elevated); color:var(--text-secondary); padding:12px; border-radius:40px; border:1px solid var(--border); cursor:pointer; font-weight:600; font-size:13px; transition: var(--transition);">Batal</button>
        </div>
    `;
    
    body.innerHTML = html;
    
    document.getElementById('followUpConfirmBtn').addEventListener('click', async function() {
        const message = document.getElementById('followUpMessage').value;
        
        if (!message || message.trim() === '') {
            showToastGlobal('Pesan tidak boleh kosong', 'error');
            document.getElementById('followUpMessage').style.borderColor = '#ef4444';
            setTimeout(() => document.getElementById('followUpMessage').style.borderColor = '', 2000);
            return;
        }
        
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Mengirim...';
        
        try {
            const response = await fetch(BASE_URL + 'is/follow_up_creator', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    creator_id: creatorId,
                    message: message.trim()
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToastGlobal('�7�3 Follow up berhasil dikirim!', 'success');
                if (result.redirect_url) {
                    window.open(result.redirect_url, '_blank');
                }
                setTimeout(() => {
                    closeTask1FollowUpModal();
                    location.reload();
                }, 1500);
            } else {
                showToastGlobal(result.message || 'Gagal kirim follow up', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fab fa-whatsapp"></i> Kirim Follow Up';
            }
        } catch (error) {
            showToastGlobal('Error: ' + error.message, 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fab fa-whatsapp"></i> Kirim Follow Up';
        }
    });
}

// ============================================================
// DETAIL MODAL FUNCTIONS - GLOBAL
// ============================================================

function closeTask1DetailModal() {
    const modal = document.getElementById('task1DetailModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('active');
    }
}

async function showTask1DetailModal(creatorId) {
    console.log('showTask1DetailModal called with creatorId:', creatorId);
    
    if (!creatorId) {
        showToastGlobal('Creator ID tidak valid', 'error');
        return;
    }
    
    const modal = document.getElementById('task1DetailModal');
    const body = document.getElementById('task1ModalBody');
    const title = document.getElementById('task1ModalTitle');
    
    if (!modal || !body || !title) {
        showToastGlobal('Modal tidak ditemukan', 'error');
        return;
    }
    
    modal.style.display = 'flex';
    modal.classList.add('active');
    body.innerHTML = `
        <div style="text-align:center; padding:40px;">
            <i class="fas fa-spinner fa-pulse fa-2x" style="color: var(--purple);"></i>
            <p style="margin-top: 12px; color: var(--text-secondary);">Loading creator data...</p>
        </div>
    `;
    
    try {
        const formData = new FormData();
        formData.append('creator_id', creatorId);
        
        const response = await fetch(BASE_URL + 'is/get_creator_task1_detail', {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Error response:', errorText);
            throw new Error(`HTTP ${response.status}: ${errorText.substring(0, 200)}`);
        }
        
        const result = await response.json();
        console.log('Result:', result);
        
        if (!result.success) {
            body.innerHTML = `
                <div style="text-align:center; padding:40px; color: #ef4444;">
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                    <p style="margin-top: 12px;">${escapeHtml(result.message || 'Gagal load data')}</p>
                    <p style="font-size: 11px; color: var(--text-muted);">Creator ID: ${creatorId}</p>
                    <button onclick="closeTask1DetailModal()" style="margin-top:16px; padding: 8px 24px; background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 8px; color: var(--text-primary); cursor: pointer;">Close</button>
                </div>
            `;
            return;
        }
        
        const c = result.creator;
        const followUpCount = c.follow_up_count || 0;
        const brands = result.brands || [];
        const products = result.products || [];
        const multiLinks = result.multi_links || [];
        const whatsappLogs = result.whatsapp_logs || [];
        
        title.innerHTML = `<i class="fas fa-user" style="color: var(--purple);"></i> @${escapeHtml(c.username)} - Detail`;
        
        let html = `
            <div style="display:flex; gap:16px; margin-bottom:16px; background:rgba(139,92,246,0.05); padding:16px; border-radius:12px; border: 1px solid rgba(139,92,246,0.1);">
                <div style="width:64px; height:64px; border-radius:50%; overflow:hidden; flex-shrink:0; background:var(--bg-elevated); border: 2px solid var(--purple);">
                    ${c.avatar_url ? `<img src="${escapeHtml(c.avatar_url)}" style="width:100%; height:100%; object-fit:cover;" onerror="this.parentElement.innerHTML='<i class=\\'fas fa-user\\' style=\\'font-size:32px; display:flex; align-items:center; justify-content:center; height:100%; color: var(--text-muted);\\'></i>'">` : '<i class="fas fa-user" style="font-size:32px; display:flex; align-items:center; justify-content:center; height:100%; color: var(--text-muted);"></i>'}
                </div>
                <div style="flex:1; min-width:0;">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap: 8px;">
                        <div>
                            <div style="font-size:18px; font-weight:700; color:var(--text-primary);">${escapeHtml(c.full_name || c.username)}</div>
                            <div style="color:var(--purple); font-size:13px;">@${escapeHtml(c.username)}</div>
                        </div>
                        <div style="text-align:right; background: rgba(16,185,129,0.1); padding: 8px 16px; border-radius: 12px; border: 1px solid rgba(16,185,129,0.2);">
                            <div style="color:#10b981; font-size:16px; font-weight:700;">Rp ${formatNumber(result.total_gmv || 0)}</div>
                            <div style="font-size:10px; color:var(--text-muted);">Total GMV</div>
                        </div>
                    </div>
                    <div style="display:flex; gap:12px; margin-top:8px; flex-wrap:wrap; font-size:11px; color:var(--text-secondary);">
                        <span style="display:inline-flex; align-items:center; gap:4px;"><i class="fas fa-tag" style="color: var(--purple);"></i> ${escapeHtml(c.category || '-')}</span>
                        <span style="display:inline-flex; align-items:center; gap:4px;"><i class="fas fa-phone" style="color: #25D366;"></i> ${escapeHtml(c.phone || '-')}</span>
                        <span style="display:inline-flex; align-items:center; gap:4px;"><i class="fas fa-calendar" style="color: var(--text-muted);"></i> Since: ${new Date(c.created_at).toLocaleDateString('id-ID')}</span>
                        ${c.brand_name ? `<span style="display:inline-flex; align-items:center; gap:4px;"><i class="fas fa-store" style="color: #4ade80;"></i> ${escapeHtml(c.brand_name)}</span>` : ''}
                        <span style="display:inline-flex; align-items:center; gap:4px;"><i class="fas fa-box" style="color: #fbbf24;"></i> ${result.total_products || 0} products</span>
                        <span style="display:inline-flex; align-items:center; gap:4px;"><i class="fas fa-store" style="color: #8b5cf6;"></i> ${result.total_brands || 0} brands</span>
                    </div>
                </div>
            </div>
        `;
        
        // BRANDS — 2 kolom: Partner (sudah kerja sama) | Prospect (belum)
        if (brands.length > 0) {
            const partners  = brands.filter(b => b.is_partner);
            const prospects = brands.filter(b => !b.is_partner);

            // Helper: render satu baris brand
            function _brandRow(b, isPartner) {
                const accentColor = isPartner ? '#4ade80' : '#f59e0b';
                const logoBg      = isPartner ? 'rgba(74,222,128,0.12)' : 'rgba(245,158,11,0.12)';
                const logoIcon    = isPartner ? '#4ade80' : '#f59e0b';
                const logo = (b.shop_logo || b.img)
                    ? `<img src="${escapeHtml(b.shop_logo || b.img)}" alt=""
                             style="width:26px;height:26px;border-radius:6px;object-fit:cover;flex-shrink:0;"
                             onerror="this.style.display='none'">`
                    : `<div style="width:26px;height:26px;border-radius:6px;background:${logoBg};
                                   display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                           <i class="fas fa-store" style="color:${logoIcon};font-size:10px;"></i>
                       </div>`;
                return `
                <div style="background:var(--bg-elevated);border-radius:9px;padding:8px 10px;
                            border-left:3px solid ${accentColor};display:flex;align-items:center;gap:8px;
                            margin-bottom:6px;">
                    ${logo}
                    <div style="flex:1;min-width:0;">
                        <div style="color:var(--text-primary);font-size:11px;font-weight:600;
                                    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            ${escapeHtml(b.brand_name || b.shop_name || b.name || '-')}
                        </div>
                        <div style="color:var(--text-muted);font-size:9px;">${b.total_products || 0} produk</div>
                    </div>
                    <div style="color:${accentColor};font-size:11px;font-weight:700;flex-shrink:0;text-align:right;">
                        Rp ${formatNumber(b.total_gmv || 0)}
                    </div>
                </div>`;
            }

            html += `
                <div style="margin-bottom:16px;">
                    <!-- Header -->
                    <h4 style="color:var(--text-primary);font-size:13px;margin-bottom:10px;
                               display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-store" style="color:#4ade80;"></i>
                        Brands Collaborated
                        <span style="background:rgba(255,255,255,0.06);color:var(--text-secondary);
                                     font-size:10px;font-weight:500;padding:1px 7px;border-radius:10px;">
                            ${brands.length} total
                        </span>
                    </h4>

                    <!-- 2-column grid -->
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;align-items:start;">

                        <!-- Kolom KIRI: Partner -->
                        <div>
                            <div style="display:flex;align-items:center;gap:6px;margin-bottom:8px;">
                                <i class="fas fa-handshake" style="color:#4ade80;font-size:10px;"></i>
                                <span style="font-size:10px;font-weight:700;color:#4ade80;text-transform:uppercase;
                                             letter-spacing:.5px;">
                                    Sudah Bekerja Sama
                                </span>
                                <span style="background:rgba(74,222,128,0.15);color:#4ade80;
                                             font-size:9px;padding:1px 6px;border-radius:8px;font-weight:600;">
                                    ${partners.length}
                                </span>
                            </div>
                            ${partners.length > 0
                                ? partners.map(b => _brandRow(b, true)).join('')
                                : `<div style="text-align:center;padding:20px 8px;color:var(--text-muted);font-size:10px;
                                              border:1px dashed rgba(74,222,128,0.15);border-radius:8px;">
                                       Belum ada brand partner
                                   </div>`
                            }
                        </div>

                        <!-- Kolom KANAN: Prospect -->
                        <div>
                            <div style="display:flex;align-items:center;gap:6px;margin-bottom:8px;">
                                <i class="fas fa-bullseye" style="color:#f59e0b;font-size:10px;"></i>
                                <span style="font-size:10px;font-weight:700;color:#f59e0b;text-transform:uppercase;
                                             letter-spacing:.5px;">
                                    Belum Bekerja Sama
                                </span>
                                <span style="background:rgba(245,158,11,0.15);color:#f59e0b;
                                             font-size:9px;padding:1px 6px;border-radius:8px;font-weight:600;">
                                    ${prospects.length}
                                </span>
                            </div>
                            ${prospects.length > 0
                                ? prospects.map(b => _brandRow(b, false)).join('')
                                : `<div style="text-align:center;padding:20px 8px;color:var(--text-muted);font-size:10px;
                                              border:1px dashed rgba(245,158,11,0.15);border-radius:8px;">
                                       
                                   </div>`
                            }
                        </div>

                    </div>
                </div>
            `;
        } else {
            html += `
                <div style="margin-bottom:16px; background:rgba(139,92,246,0.05); border:1px solid rgba(139,92,246,0.1); border-radius:12px; padding:16px; text-align:center;">
                    <div style="color:#a78bfa; font-size:12px; font-weight:600; display:flex; align-items:center; justify-content:center; gap:6px; margin-bottom:6px;">
                        <i class="fas fa-exclamation-triangle"></i> Data Brand Tidak Ditemukan
                    </div>
                    <div style="color:var(--text-muted); font-size:10.5px; margin-bottom:12px; line-height:1.4;">
                        Data brand kolaborasi kosong. Creator mungkin belum ditemukan di FastMoss atau belum pernah ada order di sistem.
                    </div>
                </div>
            `;
        }
        
        // PRODUCTS
        if (products.length > 0) {
            html += `
                <div style="margin-bottom:16px;">
                    <h4 style="color:var(--text-primary); font-size:13px; margin-bottom:8px; display:flex; align-items:center; gap:8px;">
                        <i class="fas fa-box" style="color: #fbbf24;"></i> Products (${products.length})
                    </h4>
                    <div style="max-height:200px; overflow-y:auto; background:var(--bg-elevated); border-radius:8px; padding:8px; border: 1px solid var(--border);">
            `;
            
            products.forEach(p => {
                const priceFormatted = p.price ? 'Rp ' + formatNumber(p.price) : '-';
                html += `
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:6px 8px; border-bottom:1px solid var(--border); font-size:11px;">
                        <div style="flex:1; display:flex; align-items:center; gap:8px; min-width:0;">
                            ${p.image_url ? `<img src="${escapeHtml(p.image_url)}" style="width:30px; height:30px; border-radius:4px; object-fit:cover; flex-shrink:0;" onerror="this.style.display='none'">` : '<i class="fas fa-box" style="color:var(--text-muted); flex-shrink:0;"></i>'}
                            <div style="min-width:0;">
                                <div style="color:var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${escapeHtml(p.product_name.substring(0, 35))}</div>
                                <div style="color:var(--text-muted); font-size:9px;">${escapeHtml(p.shop_name || '-')}</div>
                            </div>
                        </div>
                        <div style="text-align:right; font-size:10px; flex-shrink:0; margin-left:8px;">
                            <div style="color:#fbbf24;">${p.commission_rate || 0}%</div>
                            <div style="color:#4ade80;">${priceFormatted}</div>
                        </div>
                    </div>
                `;
            });
            
            html += `
                    </div>
                </div>
            `;
        }
        
        // MULTI LINKS
        if (multiLinks.length > 0) {
            html += `
                <div style="margin-bottom:16px;">
                    <h4 style="color:var(--text-primary); font-size:13px; margin-bottom:8px; display:flex; align-items:center; gap:8px;">
                        <i class="fas fa-layer-group" style="color: #8b5cf6;"></i> Multiple Links (ALL SKU) (${multiLinks.length})
                    </h4>
                    <div style="display:flex; flex-wrap:wrap; gap:8px;">
            `;
            
            multiLinks.forEach(m => {
                html += `
                    <div style="background:var(--bg-elevated); border-radius:8px; padding:8px 12px; border:1px solid rgba(139,92,246,0.3); display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <span style="color:var(--text-primary); font-size:11px; font-weight:500;">${escapeHtml(m.brand_name)}</span>
                        <span style="color:var(--text-muted); font-size:9px;">${m.total_products || 0} SKU</span>
                        <button onclick="copyMultiLink('${escapeHtml(m.multi_link)}')" style="background:linear-gradient(135deg, #8b5cf6, #7c3aed); color:white; border:none; padding:3px 12px; border-radius:12px; cursor:pointer; font-size:9px; font-weight:600; transition: var(--transition);">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                `;
            });
            
            html += `
                    </div>
                </div>
            `;
        }
        
        // WHATSAPP LOGS
        if (whatsappLogs.length > 0) {
            html += `
                <div style="margin-bottom:16px;">
                    <h4 style="color:var(--text-primary); font-size:13px; margin-bottom:8px; display:flex; align-items:center; gap:8px;">
                        <i class="fab fa-whatsapp" style="color: #25D366;"></i> WhatsApp Logs (${whatsappLogs.length})
                    </h4>
                    <div style="max-height:150px; overflow-y:auto; background:var(--bg-elevated); border-radius:8px; padding:8px; border: 1px solid var(--border);">
            `;
            
            whatsappLogs.forEach(log => {
                const isFollowUp = log.link_type === 'follow_up';
                const icon = isFollowUp ? 'fa-comment' : 'fa-paper-plane';
                const color = isFollowUp ? '#f59e0b' : '#4ade80';
                const label = isFollowUp ? 'FOLLOW UP' : 'LINK SENT';
                html += `
                    <div style="display:flex; justify-content:space-between; padding:6px 8px; border-bottom:1px solid var(--border); font-size:10px;">
                        <div style="flex:1; min-width:0;">
                            <div style="color:${color}; font-weight:600; display:flex; align-items:center; gap:4px;">
                                <i class="fas ${icon}"></i> ${label}
                            </div>
                            <div style="color:var(--text-secondary); font-size:9px; max-height:30px; overflow:hidden; word-break:break-all;">${escapeHtml(log.message.substring(0, 100))}</div>
                        </div>
                        <div style="text-align:right; font-size:9px; color:var(--text-muted); flex-shrink:0; margin-left:8px;">
                            ${new Date(log.sent_at).toLocaleDateString('id-ID')}<br>
                            ${new Date(log.sent_at).toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit'})}
                        </div>
                    </div>
                `;
            });
            
            html += `
                    </div>
                </div>
            `;
        }
        
        html += `
            <div style="display:flex; gap:10px; margin-top:16px; padding-top:12px; border-top: 1px solid var(--border);">
                <button onclick="closeTask1DetailModal()" style="flex:1; background:var(--bg-elevated); color:var(--text-secondary); padding:10px; border-radius:40px; border:1px solid var(--border); cursor:pointer; font-weight:600; font-size:13px; transition: var(--transition);">Tutup</button>
            </div>
        `;
        
        body.innerHTML = html;
        
    } catch (error) {
        console.error('Fetch error:', error);
        body.innerHTML = `
            <div style="text-align:center; padding:40px; color: #ef4444;">
                <i class="fas fa-exclamation-triangle fa-2x"></i>
                <p style="margin-top: 12px;">Error: ${escapeHtml(error.message)}</p>
                <button onclick="closeTask1DetailModal()" style="margin-top:16px; padding: 8px 24px; background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 8px; color: var(--text-primary); cursor: pointer;">Close</button>
            </div>
        `;
    }
}

// ============================================================
// CLAIM DEAL FUNCTION - GLOBAL
// ============================================================

window.claimDeal = async function(creatorId, creatorUsername) {
    console.log('claimDeal called - ID:', creatorId, 'Username:', creatorUsername);
    
    const isUnregistered = !creatorId || creatorId === '0' || creatorId === 'null' || creatorId === '';
    
    if (isUnregistered && !creatorUsername) {
        showToastGlobal('Data creator tidak valid', 'error');
        return;
    }
    
    let confirmMessage = isUnregistered 
        ? `Claim dan register @${creatorUsername}?` 
        : `Claim @${creatorUsername}?`;
    
    if (!confirm(confirmMessage)) return;
    
    try {
        const formData = new FormData();
        if (!isUnregistered) {
            formData.append('creator_id', creatorId);
        }
        if (creatorUsername) {
            formData.append('creator_username', creatorUsername);
        }
        
        const response = await fetch(BASE_URL + 'is/claim_deal', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToastGlobal(result.message, 'success');
            setTimeout(function() {
                location.reload();
            }, 1500);
        } else {
            showToastGlobal(result.message || 'Gagal claim', 'error');
        }
    } catch (error) {
        console.error('Claim deal error:', error);
        showToastGlobal('Error: ' + error.message, 'error');
    }
};

// ============================================================
// SHOW CREATOR DETAIL - TASK 2 & 3
// ============================================================

window.closeCreatorModal = function() {
    const modal = document.getElementById('creatorModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('active');
    }
};

window.showCreatorDetail = async function(creatorId, creatorUsername, task) {
    console.log('showCreatorDetail called - ID:', creatorId, 'Username:', creatorUsername, 'Task:', task);
    
    if (!creatorId || creatorId === 'null' || creatorId === 'undefined' || creatorId === '') {
        const items = document.querySelectorAll(`.is-item[data-creator-username="${creatorUsername}"]`);
        if (items.length > 0) {
            const item = items[0];
            creatorId = item.getAttribute('data-creator-id') || '';
            task = parseInt(item.getAttribute('data-task')) || 2;
        }
    }
    
    const modal = document.getElementById('creatorModal');
    const body = document.getElementById('creatorModalBody');
    const title = document.getElementById('creatorModalTitle');
    
    if (!modal || !body || !title) {
        showToastGlobal('Modal tidak ditemukan', 'error');
        return;
    }
    
    modal.style.display = 'flex';
    modal.classList.add('active');
    body.innerHTML = `
        <div style="text-align:center; padding:40px;">
            <i class="fas fa-spinner fa-pulse fa-2x" style="color: var(--purple);"></i>
            <p style="margin-top: 12px; color: var(--text-secondary);">Loading creator data...</p>
        </div>
    `;
    
    try {
        if (task == 3 && creatorId && creatorId !== 'null' && creatorId !== '' && creatorId !== 0) {
            const formData = new FormData();
            formData.append('creator_id', creatorId);
            
            const response = await fetch(BASE_URL + 'is/get_creator_detail_for_is', {
                method: 'POST',
                body: formData
            });
            
            if (response.ok) {
                const result = await response.json();
                if (result.success && result.data) {
                    renderMonitoringDetail(result.data, title, body);
                    return;
                }
            }
            
            const item = document.querySelector(`.is-item[data-creator-id="${creatorId}"][data-task="3"]`);
            if (item) {
                const itemData = {
                    username: item.getAttribute('data-creator-username') || creatorUsername || 'Unknown',
                    full_name: item.getAttribute('data-creator-username') || creatorUsername || 'Unknown',
                    category: item.querySelector('.is-item-detail span:first-child')?.textContent?.replace(/[^a-zA-Z\s]/g, '').trim() || '-',
                    gmv: item.querySelector('.gmv')?.textContent || 'Rp 0',
                    orders: item.querySelector('.is-item-detail span:nth-child(3)')?.textContent || '0',
                    brand_name: item.querySelector('.is-item-detail .brand-name')?.textContent || '-',
                    top_product: item.querySelector('.is-item-product span')?.textContent || '-',
                    handler_name: item.querySelector('.is-item-name span[style*="color: var(--is-purple)"]')?.textContent || '-',
                    is_monitoring: true
                };
                renderMonitoringDetailFromItem(itemData, title, body);
                return;
            }
        }
        
        if (task == 2) {
            if (creatorId && creatorId !== 'null' && creatorId !== '' && creatorId !== 0) {
                const formData = new FormData();
                formData.append('creator_id', creatorId);
                
                const response = await fetch(BASE_URL + 'is/get_creator_detail_for_is', {
                    method: 'POST',
                    body: formData
                });
                
                if (response.ok) {
                    const result = await response.json();
                    if (result.success && result.data) {
                        renderWaitingHandlerDetail(result.data, title, body);
                        return;
                    }
                }
            }
            
            const selector = creatorId ? `[data-creator-id="${creatorId}"]` : `[data-creator-username="${creatorUsername}"]`;
            const item = document.querySelector(`.is-item${selector}[data-task="2"]`);
            if (item) {
                const itemData = {
                    username: item.getAttribute('data-creator-username') || creatorUsername || 'Unknown',
                    full_name: item.getAttribute('data-creator-username') || creatorUsername || 'Unknown',
                    category: item.querySelector('.is-item-detail span:first-child')?.textContent?.replace(/[^a-zA-Z\s]/g, '').trim() || '-',
                    phone: item.querySelector('.is-item-detail span:nth-child(2)')?.textContent?.trim() || '-',
                    gmv: item.querySelector('.gmv')?.textContent || 'Rp 0',
                    orders: item.querySelector('.is-item-detail span:last-child')?.textContent || '0',
                    brand_name: item.querySelector('.is-item-detail .brand-name')?.textContent || '-',
                    top_product: item.querySelector('.is-item-product span')?.textContent || '-',
                    is_unregistered: true
                };
                renderWaitingHandlerDetail(itemData, title, body);
                return;
            }
        }
        
        renderMinimalCreatorDetail(creatorUsername || 'Unknown', title, body, task);
        
    } catch (error) {
        console.error('Error fetching creator detail:', error);
        body.innerHTML = `
            <div style="text-align:center; padding:40px; color: #ef4444;">
                <i class="fas fa-exclamation-triangle fa-2x"></i>
                <p style="margin-top: 12px;">Gagal memuat detail creator</p>
                <p style="font-size: 11px; color: var(--text-muted);">${escapeHtml(error.message)}</p>
                <button onclick="closeCreatorModal()" style="margin-top:16px; padding: 8px 24px; background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 8px; color: var(--text-primary); cursor: pointer;">Tutup</button>
            </div>
        `;
    }
};

// ============================================================
// RENDER FUNCTIONS - CREATOR DETAIL
// ============================================================

function renderMonitoringDetailFromItem(data, title, body) {
    const c = data;
    
    title.innerHTML = `<i class="fas fa-chart-line" style="color: #10b981;"></i> @${escapeHtml(c.username || 'Unknown')} - Monitoring Detail`;
    
    let html = `
        <div style="display:flex; gap:16px; margin-bottom:16px; background:rgba(16,185,129,0.08); padding:16px; border-radius:12px; border: 1px solid rgba(16,185,129,0.2);">
            <div style="width:64px; height:64px; border-radius:50%; overflow:hidden; flex-shrink:0; background:var(--bg-elevated); border: 2px solid #10b981; display:flex; align-items:center; justify-content:center; font-size:32px; color: var(--text-muted);">
                <i class="fas fa-user"></i>
            </div>
            <div style="flex:1; min-width:0;">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap: 8px;">
                    <div>
                        <div style="font-size:18px; font-weight:700; color:var(--text-primary);">${escapeHtml(c.full_name || c.username || 'Unknown')}</div>
                        <div style="color:#10b981; font-size:13px;">@${escapeHtml(c.username || 'Unknown')}</div>
                    </div>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <span style="background:rgba(16,185,129,0.15); color:#10b981; padding:4px 12px; border-radius:20px; font-size:10px; font-weight:600;">
                            <i class="fas fa-circle" style="font-size:6px;"></i> MONITORING
                        </span>
                        <div style="text-align:right; background: rgba(16,185,129,0.1); padding: 8px 16px; border-radius: 12px; border: 1px solid rgba(16,185,129,0.2);">
                            <div style="color:#10b981; font-size:16px; font-weight:700;">${c.gmv || 'Rp 0'}</div>
                            <div style="font-size:10px; color:var(--text-muted);">Total GMV</div>
                        </div>
                    </div>
                </div>
                <div style="display:flex; gap:12px; margin-top:8px; flex-wrap:wrap; font-size:11px; color:var(--text-secondary);">
                    <span style="display:inline-flex; align-items:center; gap:4px;"><i class="fas fa-tag" style="color: var(--purple);"></i> ${escapeHtml(c.category || '-')}</span>
                    <span style="display:inline-flex; align-items:center; gap:4px;"><i class="fas fa-phone" style="color: #25D366;"></i> ${escapeHtml(c.phone || '-')}</span>
                    ${c.handler_name && c.handler_name != '-' ? `<span style="display:inline-flex; align-items:center; gap:4px;"><i class="fas fa-user-tie" style="color: #8b5cf6;"></i> Handler: ${escapeHtml(c.handler_name)}</span>` : ''}
                    ${c.brand_name && c.brand_name != '-' ? `<span style="display:inline-flex; align-items:center; gap:4px;"><i class="fas fa-store" style="color: #4ade80;"></i> ${escapeHtml(c.brand_name)}</span>` : ''}
                    <span style="display:inline-flex; align-items:center; gap:4px;"><i class="fas fa-box" style="color: #fbbf24;"></i> ${c.orders || 0} orders</span>
                </div>
                ${c.top_product && c.top_product != '-' ? `
                <div style="margin-top:6px; background:rgba(74,222,128,0.05); padding:6px 12px; border-radius:6px; font-size:11px; color:var(--text-muted);">
                    <i class="fas fa-shopping-bag" style="color: #4ade80;"></i> Top Product: ${escapeHtml(c.top_product)}
                </div>
                ` : ''}
                <div style="margin-top:6px; background:rgba(245,158,11,0.1); padding:4px 12px; border-radius:6px; font-size:10px; color:#f59e0b;">
                    <i class="fas fa-info-circle"></i> Data dari tampilan (detail lengkap mungkin tidak tersedia)
                </div>
            </div>
        </div>
        <div style="display:flex; gap:10px; margin-top:16px; padding-top:12px; border-top: 1px solid var(--border);">
            <button onclick="closeCreatorModal()" style="flex:1; background:var(--bg-elevated); color:var(--text-secondary); padding:10px; border-radius:40px; border:1px solid var(--border); cursor:pointer; font-weight:600; font-size:13px; transition: var(--transition);">Tutup</button>
        </div>
    `;
    
    body.innerHTML = html;
}

function renderMonitoringDetail(data, title, body) {
    const c = data;
    
    title.innerHTML = `<i class="fas fa-chart-line" style="color: #10b981;"></i> @${escapeHtml(c.username || 'Unknown')} - Monitoring Detail`;
    
    let html = `
        <div style="display:flex; gap:16px; margin-bottom:16px; background:rgba(16,185,129,0.08); padding:16px; border-radius:12px; border: 1px solid rgba(16,185,129,0.2);">
            <div style="width:64px; height:64px; border-radius:50%; overflow:hidden; flex-shrink:0; background:var(--bg-elevated); border: 2px solid #10b981; display:flex; align-items:center; justify-content:center;">
                ${c.avatar_url ? `<img src="${escapeHtml(c.avatar_url)}" style="width:100%; height:100%; object-fit:cover;" onerror="this.parentElement.innerHTML='<i class=\\'fas fa-user\\' style=\\'font-size:32px; color: var(--text-muted);\\'></i>'">` : '<i class="fas fa-user" style="font-size:32px; color: var(--text-muted);"></i>'}
            </div>
            <div style="flex:1; min-width:0;">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap: 8px;">
                    <div>
                        <div style="font-size:18px; font-weight:700; color:var(--text-primary);">${escapeHtml(c.full_name || c.username || 'Unknown')}</div>
                        <div style="color:#10b981; font-size:13px;">@${escapeHtml(c.username || 'Unknown')}</div>
                    </div>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <span style="background:rgba(16,185,129,0.15); color:#10b981; padding:4px 12px; border-radius:20px; font-size:10px; font-weight:600;">
                            <i class="fas fa-circle" style="font-size:6px;"></i> MONITORING
                        </span>
                        <div style="text-align:right; background: rgba(16,185,129,0.1); padding: 8px 16px; border-radius: 12px; border: 1px solid rgba(16,185,129,0.2);">
                            <div style="color:#10b981; font-size:16px; font-weight:700;">Rp ${formatNumber(c.total_gmv || c.imported_gmv || 0)}</div>
                            <div style="font-size:10px; color:var(--text-muted);">Total GMV</div>
                        </div>
                    </div>
                </div>
                <div style="display:flex; gap:12px; margin-top:8px; flex-wrap:wrap; font-size:11px; color:var(--text-secondary);">
                    <span style="display:inline-flex; align-items:center; gap:4px;"><i class="fas fa-tag" style="color: var(--purple);"></i> ${escapeHtml(c.category || '-')}</span>
                    <span style="display:inline-flex; align-items:center; gap:4px;"><i class="fas fa-phone" style="color: #25D366;"></i> ${escapeHtml(c.phone || '-')}</span>
                    <span style="display:inline-flex; align-items:center; gap:4px;"><i class="fas fa-calendar" style="color: var(--text-muted);"></i> ${c.created_at ? new Date(c.created_at).toLocaleDateString('id-ID') : '-'}</span>
                    ${c.brand_name ? `<span style="display:inline-flex; align-items:center; gap:4px;"><i class="fas fa-store" style="color: #4ade80;"></i> ${escapeHtml(c.brand_name)}</span>` : ''}
                    ${c.handler_name ? `<span style="display:inline-flex; align-items:center; gap:4px;"><i class="fas fa-user-tie" style="color: #8b5cf6;"></i> Handler: ${escapeHtml(c.handler_name)}</span>` : ''}
                </div>
            </div>
        </div>
    `;
    
    if (c.performance) {
        const perf = c.performance;
        html += `
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-bottom:16px;">
                <div style="background:#0f1420; border-radius:10px; padding:12px; text-align:center; border:1px solid var(--border);">
                    <div style="color:var(--is-green); font-size:18px; font-weight:700;">Rp ${formatNumber(perf.total_gmv || 0)}</div>
                    <div style="color:var(--text-muted); font-size:10px;">GMV 30 Hari</div>
                </div>
                <div style="background:#0f1420; border-radius:10px; padding:12px; text-align:center; border:1px solid var(--border);">
                    <div style="color:var(--is-purple); font-size:18px; font-weight:700;">${formatNumber(perf.total_orders || 0)}</div>
                    <div style="color:var(--text-muted); font-size:10px;">Total Orders</div>
                </div>
                <div style="background:#0f1420; border-radius:10px; padding:12px; text-align:center; border:1px solid var(--border);">
                    <div style="color:#f59e0b; font-size:18px; font-weight:700;">Rp ${formatNumber(perf.total_commission || 0)}</div>
                    <div style="color:var(--text-muted); font-size:10px;">Commission</div>
                </div>
            </div>
        `;
    }
    
    if (c.links && c.links.length > 0) {
        html += `
            <div style="margin-bottom:16px;">
                <h4 style="color:var(--text-primary); font-size:13px; margin-bottom:8px; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-link" style="color: #8b5cf6;"></i> Active Links (${c.links.length})
                </h4>
                <div style="max-height:150px; overflow-y:auto; background:var(--bg-elevated); border-radius:8px; padding:8px; border: 1px solid var(--border);">
        `;
        
        c.links.forEach(link => {
            html += `
                <div style="display:flex; justify-content:space-between; padding:6px 8px; border-bottom:1px solid var(--border); font-size:11px;">
                    <div style="flex:1; min-width:0;">
                        <div style="color:var(--text-primary); font-weight:500;">${escapeHtml(link.product_name || '-')}</div>
                        <div style="color:var(--text-muted); font-size:9px;">${escapeHtml(link.campaign_name || '')}</div>
                    </div>
                    <div style="text-align:right; flex-shrink:0; margin-left:8px;">
                        <div style="color:#fbbf24;">${link.commission_rate || 0}%</div>
                        <div style="color:var(--text-muted); font-size:9px;">${new Date(link.created_at).toLocaleDateString('id-ID')}</div>
                    </div>
                </div>
            `;
        });
        
        html += `
                </div>
            </div>
        `;
    }
    
    // Tombol Sample hanya muncul jika creator sudah ada transaksi (keranjang kuning terdeteksi)
    const hasOrders = (c.performance && (c.performance.total_orders > 0)) || (c.total_orders > 0) || false;
    const sampleBtn = hasOrders ? `
        <button onclick="closeCreatorModal(); setTimeout(() => openDashboardWillingModal(${c.id}, '${c.username ? c.username.replace(/'/g,"\'") : ''}'), 200)"
            style="flex:1; background:linear-gradient(135deg, rgba(139,92,246,0.3), rgba(124,58,237,0.3)); color:#a78bfa; padding:10px; border-radius:40px; border:1px solid rgba(139,92,246,0.4); cursor:pointer; font-weight:600; font-size:13px; transition: var(--transition);">
            <i class="fas fa-gift"></i> Proses Sample
        </button>
    ` : '';

    html += `
        <div style="display:flex; gap:10px; margin-top:16px; padding-top:12px; border-top: 1px solid var(--border);">
            <button onclick="closeCreatorModal()" style="flex:1; background:var(--bg-elevated); color:var(--text-secondary); padding:10px; border-radius:40px; border:1px solid var(--border); cursor:pointer; font-weight:600; font-size:13px; transition: var(--transition);">Tutup</button>
            ${sampleBtn}
            <button onclick="window.location.href='${BASE_URL}is/creators?creator=${c.id}'" style="flex:1; background:linear-gradient(135deg, #8b5cf6, #7c3aed); color:white; padding:10px; border-radius:40px; border:none; cursor:pointer; font-weight:600; font-size:13px; transition: var(--transition);">
                <i class="fas fa-external-link-alt"></i> Lihat Semua
            </button>
        </div>
    `;
    
    body.innerHTML = html;
}

function renderWaitingHandlerDetail(data, title, body) {
    const isUnregistered = data.is_unregistered || false;
    const c = data;
    
    title.innerHTML = `<i class="fas fa-handshake" style="color: #f59e0b;"></i> @${escapeHtml(c.username || 'Unknown')} - Waiting Handler`;
    
    let html = `
        <div style="display:flex; gap:16px; margin-bottom:16px; background:rgba(245,158,11,0.08); padding:16px; border-radius:12px; border: 1px solid rgba(245,158,11,0.2);">
            <div style="width:64px; height:64px; border-radius:50%; overflow:hidden; flex-shrink:0; background:var(--bg-elevated); border: 2px solid #f59e0b; display:flex; align-items:center; justify-content:center;">
                ${c.avatar_url ? `<img src="${escapeHtml(c.avatar_url)}" style="width:100%; height:100%; object-fit:cover;" onerror="this.parentElement.innerHTML='<i class=\\'fas fa-user\\' style=\\'font-size:32px; color: var(--text-muted);\\'></i>'">` : `<i class="fas ${isUnregistered ? 'fa-user-plus' : 'fa-user'}" style="font-size:32px; color: ${isUnregistered ? '#f59e0b' : 'var(--text-muted)'};"></i>`}
            </div>
            <div style="flex:1; min-width:0;">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap: 8px;">
                    <div>
                        <div style="font-size:18px; font-weight:700; color:var(--text-primary);">${escapeHtml(c.full_name || c.username || 'Unknown')}</div>
                        <div style="color:#f59e0b; font-size:13px;">@${escapeHtml(c.username || 'Unknown')}</div>
                    </div>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <span style="background:rgba(245,158,11,0.15); color:#f59e0b; padding:4px 12px; border-radius:20px; font-size:10px; font-weight:600;">
                            <i class="fas fa-clock" style="font-size:8px;"></i> WAITING HANDLER
                        </span>
                        ${isUnregistered ? `<span style="background:rgba(239,68,68,0.15); color:#ef4444; padding:4px 8px; border-radius:12px; font-size:9px; font-weight:600;"> NEW</span>` : ''}
                        <div style="text-align:right; background: rgba(245,158,11,0.1); padding: 8px 16px; border-radius: 12px; border: 1px solid rgba(245,158,11,0.2);">
                            <div style="color:#f59e0b; font-size:16px; font-weight:700;">${c.gmv || 'Rp 0'}</div>
                            <div style="font-size:10px; color:var(--text-muted);">Total GMV</div>
                        </div>
                    </div>
                </div>
                <div style="display:flex; gap:12px; margin-top:8px; flex-wrap:wrap; font-size:11px; color:var(--text-secondary);">
                    <span style="display:inline-flex; align-items:center; gap:4px;"><i class="fas fa-tag" style="color: var(--purple);"></i> ${escapeHtml(c.category || '-')}</span>
                    <span style="display:inline-flex; align-items:center; gap:4px;"><i class="fas fa-phone" style="color: #25D366;"></i> ${escapeHtml(c.phone || '-')}</span>
                    ${c.brand_name && c.brand_name != '-' ? `<span style="display:inline-flex; align-items:center; gap:4px;"><i class="fas fa-store" style="color: #4ade80;"></i> ${escapeHtml(c.brand_name)}</span>` : ''}
                    <span style="display:inline-flex; align-items:center; gap:4px;"><i class="fas fa-box" style="color: #fbbf24;"></i> ${c.orders || c.total_orders || 0} orders</span>
                </div>
                ${isUnregistered ? `
                <div style="margin-top:8px; background:rgba(239,68,68,0.1); padding:6px 12px; border-radius:8px; border-left:3px solid #ef4444; font-size:11px; color:#ef4444;">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Creator belum terdaftar!</strong> Klik CLAIM untuk mendaftarkan otomatis.
                </div>
                ` : `
                <div style="margin-top:8px; background:rgba(245,158,11,0.1); padding:6px 12px; border-radius:8px; border-left:3px solid #f59e0b; font-size:11px; color:#f59e0b;">
                    <i class="fas fa-info-circle"></i> 
                    Creator belum punya handler. Siapa cepat dia dapat!
                </div>
                `}
                ${c.top_product && c.top_product != '-' ? `
                <div style="margin-top:6px; background:rgba(74,222,128,0.05); padding:6px 12px; border-radius:6px; font-size:11px; color:var(--text-muted);">
                    <i class="fas fa-shopping-bag" style="color: #4ade80;"></i> Top Product: ${escapeHtml(c.top_product)}
                </div>
                ` : ''}
            </div>
        </div>
        
        <div style="display:flex; gap:10px; margin-top:16px; padding-top:12px; border-top: 1px solid var(--border);">
            <button onclick="closeCreatorModal()" style="flex:1; background:var(--bg-elevated); color:var(--text-secondary); padding:10px; border-radius:40px; border:1px solid var(--border); cursor:pointer; font-weight:600; font-size:13px; transition: var(--transition);">Tutup</button>
            <button onclick="claimDeal(${c.id || 0}, '${escapeHtml(c.username)}')" style="flex:1; background:linear-gradient(135deg, #f59e0b, #d97706); color:#0a0e17; padding:10px; border-radius:40px; border:none; cursor:pointer; font-weight:600; font-size:13px; transition: var(--transition);">
                <i class="fas fa-hand-holding-heart"></i> DEAL
            </button>
        </div>
    `;
    
    body.innerHTML = html;
}

function renderMinimalCreatorDetail(username, title, body, task) {
    const taskLabel = task == 3 ? 'MONITORING' : (task == 2 ? 'WAITING HANDLER' : 'Unknown');
    const taskColor = task == 3 ? '#10b981' : (task == 2 ? '#f59e0b' : 'var(--purple)');
    
    title.innerHTML = `<i class="fas fa-user" style="color: ${taskColor};"></i> @${escapeHtml(username)} - ${taskLabel}`;
    
    let html = `
        <div style="display:flex; gap:16px; margin-bottom:16px; background:rgba(139,92,246,0.05); padding:16px; border-radius:12px; border: 1px solid var(--border);">
            <div style="width:64px; height:64px; border-radius:50%; overflow:hidden; flex-shrink:0; background:var(--bg-elevated); border: 2px solid ${taskColor}; display:flex; align-items:center; justify-content:center; font-size:32px; color: var(--text-muted);">
                <i class="fas fa-user"></i>
            </div>
            <div style="flex:1; min-width:0;">
                <div>
                    <div style="font-size:18px; font-weight:700; color:var(--text-primary);">@${escapeHtml(username)}</div>
                    <div style="color:var(--text-muted); font-size:13px;">Task: ${taskLabel}</div>
                </div>
                <div style="margin-top:8px; background:rgba(239,68,68,0.1); padding:6px 12px; border-radius:8px; border-left:3px solid #ef4444; font-size:11px; color:#ef4444;">
                    <i class="fas fa-exclamation-triangle"></i> Data tidak tersedia. Silakan refresh halaman.
                </div>
            </div>
        </div>
        <div style="display:flex; gap:10px; margin-top:16px; padding-top:12px; border-top: 1px solid var(--border);">
            <button onclick="closeCreatorModal()" style="flex:1; background:var(--bg-elevated); color:var(--text-secondary); padding:10px; border-radius:40px; border:1px solid var(--border); cursor:pointer; font-weight:600; font-size:13px; transition: var(--transition);">Tutup</button>
        </div>
    `;
    
    body.innerHTML = html;
}

// ============================================================
// FILTER TASK FUNCTIONS - AJAX
// ============================================================

let searchTimer = null;

// Cache konten awal task containers agar bisa di-restore tanpa reload halaman
const _taskOriginalContent = {};
const _taskOriginalCount = {};
function _cacheTaskOriginals() {
    ['task2', 'task3'].forEach(function(task) {
        const container = document.getElementById(task + 'Items');
        const countBadge = document.getElementById(task + 'Count');
        if (container) _taskOriginalContent[task] = container.innerHTML;
        if (countBadge) _taskOriginalCount[task] = countBadge.textContent;
    });
}
document.addEventListener('DOMContentLoaded', _cacheTaskOriginals);

function filterTask(task, keyword) {
    const items = document.querySelectorAll(`#${task}Items .is-item`);
    const search = keyword.toLowerCase().trim();
    let count = 0;
    
    items.forEach(item => {
        const visibleText = item.textContent.toLowerCase();
        const matches = search === '' || visibleText.includes(search);
        item.style.display = matches ? '' : 'none';
        if (matches) count++;
    });
    
    const countBadge = document.getElementById(task + 'Count');
    if (countBadge) countBadge.textContent = count;
}

function filterTaskAjax(task, keyword) {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }
    
    // Jika keyword kosong, restore data awal tanpa reload halaman
    if (!keyword || keyword.trim() === '') {
        const container = document.getElementById(task + 'Items');
        const countBadge = document.getElementById(task + 'Count');
        if (container && _taskOriginalContent[task] !== undefined) {
            container.innerHTML = _taskOriginalContent[task];
        }
        if (countBadge && _taskOriginalCount[task] !== undefined) {
            countBadge.textContent = _taskOriginalCount[task];
        }
        return;
    }
    
    const container = document.getElementById(task + 'Items');
    if (container) {
        container.innerHTML = `
            <div style="text-align:center; padding:40px;">
                <i class="fas fa-spinner fa-pulse fa-2x" style="color: var(--purple);"></i>
                <p style="margin-top: 12px; color: var(--text-secondary); font-size: 12px;">Mencari...</p>
            </div>
        `;
    }
    
    searchTimer = setTimeout(function() {
        performSearch(task, keyword);
    }, 500);
}

async function performSearch(task, keyword) {
    const container = document.getElementById(task + 'Items');
    const countBadge = document.getElementById(task + 'Count');
    
    if (!container) return;
    
    try {
        console.log('Searching for:', keyword, 'in task:', task);
        
        const response = await fetch(BASE_URL + 'is/search_creators_by_task', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                task: task.replace('task', ''),
                keyword: keyword.trim(),
                limit: 50
            })
        });
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Error response:', errorText);
            throw new Error('HTTP ' + response.status);
        }
        
        const result = await response.json();
        console.log('Search result:', result);
        
        if (countBadge) {
            countBadge.textContent = result.total || 0;
        }
        
        if (result.success && result.data && result.data.length > 0) {
            renderSearchResults(task, result.data, container);
        } else {
            container.innerHTML = `
                <div class="is-empty">
                    <i class="fas fa-search"></i>
                    <p>Tidak ada creator yang cocok dengan "<strong>${escapeHtml(keyword)}</strong>"</p>
                    <span style="font-size: 11px; color: var(--is-muted);">Coba kata kunci lain</span>
                </div>
            `;
        }
        
    } catch (error) {
        console.error('Search error:', error);
        container.innerHTML = `
            <div class="is-empty">
                <i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i>
                <p style="color: #ef4444;">Gagal mencari data</p>
                <span style="font-size: 11px; color: var(--is-muted);">${escapeHtml(error.message)}</span>
                <button onclick="location.reload()" style="margin-top:12px; padding:6px 16px; background:var(--bg-elevated); border:1px solid var(--border); border-radius:8px; color:var(--text-primary); cursor:pointer;">Refresh</button>
            </div>
        `;
    }
}

function renderSearchResults(task, creators, container) {
    let html = '';
    const taskNum = parseInt(task.replace('task', ''));
    const isTask2 = (taskNum === 2);
    const isTask3 = (taskNum === 3);
    
    creators.forEach(function(creator) {
        const dealStatus = creator.deal_status || 'no_handler';
        const sourceType = creator.source_type || 'registered';
        
        html += `
            <div class="is-item" 
                 data-creator-id="${escapeHtml(creator.id || '')}" 
                 data-creator-username="${escapeHtml(creator.username || '')}"
                 data-task="${taskNum}">
                <div class="is-item-header">
                    <div class="is-item-avatar">
                        ${creator.avatar_url ? `<img src="${escapeHtml(creator.avatar_url)}" alt="${escapeHtml(creator.username)}" onerror="this.parentElement.innerHTML='<i class=\\'fas fa-user\\'></i>'">` : '<i class="fas fa-user"></i>'}
                    </div>
                    <div class="is-item-info">
                        <div class="is-item-name">
                            ${escapeHtml(creator.username)}
                            ${isTask2 ? `
                                ${dealStatus === 'ready' ? '<span class="deal-ready"><i class="fas fa-circle"></i> READY TO CLAIM</span>' : ''}
                                ${dealStatus === 'no_handler' ? '<span class="deal-ready" style="background: rgba(239,68,68,0.2); color: #ef4444; animation: pulse-red 2s infinite;"><i class="fas fa-circle"></i> NO HANDLER �9�7</span>' : ''}
                            ` : ''}
                            ${isTask3 && creator.handler_name ? `<span style="font-size: 9px; color: var(--is-purple); margin-left: 6px;"><i class="fas fa-user-tie"></i> ${escapeHtml(creator.handler_name)}</span>` : ''}
                        </div>
                        <div class="is-item-detail">
                            <span><i class="fas fa-tag"></i> ${escapeHtml(creator.category || '-')}</span>
                            <span class="gmv"><i class="fas fa-chart-line"></i> Rp ${formatNumber(creator.total_gmv_30d || 0)}</span>
                            ${isTask2 ? `<span class="link-count"><i class="fas fa-link"></i> ${creator.total_active_links || 0} link aktif</span>` : ''}
                            ${isTask3 ? `<span><i class="fas fa-shopping-cart"></i> ${formatNumber(creator.total_orders_30d || 0)}</span>` : ''}
                            ${isTask3 ? `<span class="link-count"><i class="fas fa-link"></i> ${creator.total_links || 0}</span>` : ''}
                            ${isTask2 && sourceType === 'unregistered' ? `<span style="color: #f59e0b; font-size: 9px;"><i class="fas fa-exclamation-circle"></i> Belum terdaftar</span>` : ''}
                        </div>
                        ${creator.top_product ? `
                        <div class="is-item-product">
                            ${creator.top_product_image ? `<div class="product-thumb"><img src="${escapeHtml(creator.top_product_image)}" onerror="this.parentElement.style.display='none'"></div>` : ''}
                            <span>�0�6 ${escapeHtml(creator.top_product.substring(0, 40))}...</span>
                        </div>
                        ` : ''}
                        ${creator.brand_name ? `
                        <div class="is-item-detail" style="font-size:9px; color: var(--is-muted);">
                            <i class="fas fa-store"></i> Brand: ${escapeHtml(creator.brand_name)}
                        </div>
                        ` : ''}
                        ${isTask2 && dealStatus === 'no_handler' ? `
                        <div class="is-item-detail" style="font-size:9px; color: #ef4444;">
                            <i class="fas fa-exclamation-triangle"></i> 
                            ${sourceType === 'unregistered' ? 'Creator belum terdaftar di sistem! Klik CLAIM untuk register otomatis.' : 'Creator belum punya handler! Siapa cepat dia dapat.'}
                        </div>
                        ` : ''}
                    </div>
                    <div class="is-item-actions">
                        ${isTask2 && (dealStatus === 'ready' || dealStatus === 'no_handler') ? `
                            <button class="btn-claim" onclick="claimDeal('${creator.id || ''}', '${escapeHtml(creator.username)}')" 
                                    style="background: ${dealStatus === 'no_handler' ? 'linear-gradient(135deg, #ef4444, #dc2626)' : 'var(--is-green)'};">
                                <i class="fas fa-hand-holding-heart"></i> CLAIM
                            </button>
                        ` : ''}
                        <button class="btn-detail" onclick="showCreatorDetail('${creator.id || ''}', '${escapeHtml(creator.username)}', ${taskNum})">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// ============================================================
// TAMBAH CREATOR MODALS - TASK 1 & 3
// ============================================================

function openAddCreatorTask3() {
    const modal = document.getElementById('addCreatorTask3Modal');
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('active');
        document.getElementById('addCreatorTask3Form').reset();
        loadBrandsForTask3();
    }
}

function closeAddCreatorTask3() {
    const modal = document.getElementById('addCreatorTask3Modal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('active');
    }
}

async function loadBrandsForTask3() {
    try {
        const response = await fetch(BASE_URL + 'is/get_brands_for_select');
        const result = await response.json();
        
        const select = document.getElementById('task3Brand');
        if (select && result.success && result.brands) {
            select.innerHTML = '<option value="">-- Pilih Brand --</option>';
            result.brands.forEach(function(brand) {
                const option = document.createElement('option');
                option.value = brand.id;
                option.textContent = brand.name + (brand.shop_name ? ' (' + brand.shop_name + ')' : '');
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading brands:', error);
    }
}

function showAddCreatorModalIS() {
    const modal = document.getElementById('taskModalDashboard');
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('active');
        document.getElementById('addCreatorTask1Form').reset();
        loadBrandsForTask1();
    }
}

function closeModalIS() {
    const modal = document.getElementById('taskModalDashboard');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('active');
    }
}

async function loadBrandsForTask1() {
    try {
        const response = await fetch(BASE_URL + 'is/get_brands_for_select');
        const result = await response.json();
        
        const select = document.getElementById('creatorBrandIS');
        if (select && result.success && result.brands) {
            select.innerHTML = '<option value="">-- Pilih Brand --</option>';
            result.brands.forEach(function(brand) {
                const option = document.createElement('option');
                option.value = brand.id;
                option.textContent = brand.name + (brand.shop_name ? ' (' + brand.shop_name + ')' : '');
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading brands:', error);
    }
}

// ============================================================
// DOMContentLoaded - EVENT LISTENERS
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    console.log('Dashboard DOM loaded');
    
    // ============================================================
    // 1. SEARCH / FILTER SCOUTING
    // ============================================================
    const searchInput = document.getElementById('searchScoutingDashboard');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const keyword = this.value.toLowerCase().trim();
            const items = document.querySelectorAll('#scoutingContainerDashboard .scouting-item-dashboard');
            let visibleCount = 0;
            
            items.forEach(item => {
                const searchable = item.getAttribute('data-searchable') || '';
                const matches = keyword === '' || searchable.includes(keyword);
                item.style.display = matches ? '' : 'none';
                if (matches) visibleCount++;
            });
            
            const countBadge = document.getElementById('scoutingCountDashboard');
            if (countBadge) {
                countBadge.textContent = visibleCount;
            }
            
            const container = document.getElementById('scoutingContainerDashboard');
            if (visibleCount === 0 && items.length > 0) {
                const existingEmpty = container.querySelector('.empty-search-result');
                if (!existingEmpty) {
                    const msg = document.createElement('div');
                    msg.className = 'empty-search-result';
                    msg.style.cssText = 'padding: 30px 20px; text-align: center; color: var(--text-secondary); font-size: 12px;';
                    msg.innerHTML = `
                        <i class="fas fa-search" style="font-size: 24px; opacity: 0.3; display: block; margin-bottom: 10px;"></i>
                        Tidak ada creator yang cocok dengan pencarian "${keyword}"
                    `;
                    container.appendChild(msg);
                }
            } else {
                const existingEmpty = container.querySelector('.empty-search-result');
                if (existingEmpty) existingEmpty.remove();
            }
        });
    }
    
    // ============================================================
    // 2. BUTTON DETAIL - TASK 2 & 3
    // ============================================================
    document.querySelectorAll('.btn-detail').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var item = this.closest('.is-item');
            if (!item) {
                showToastGlobal('Data creator tidak ditemukan', 'error');
                return;
            }
            
            var creatorId = item.getAttribute('data-creator-id') || '';
            var creatorUsername = item.getAttribute('data-creator-username') || '';
            var task = parseInt(item.getAttribute('data-task')) || 2;
            
            console.log('Detail button clicked - ID:', creatorId, 'Username:', creatorUsername, 'Task:', task);
            
            if (typeof window.showCreatorDetail === 'function') {
                window.showCreatorDetail(creatorId, creatorUsername, task);
            } else {
                showToastGlobal('Fungsi detail belum siap', 'error');
            }
        });
    });
    
    // Klik di area card creator Step 1 Scouting untuk membuka detail modal
    const scoutingContainer = document.getElementById('scoutingContainerDashboard');
    if (scoutingContainer) {
        scoutingContainer.addEventListener('click', function(e) {
            // Cari card terdekat
            const card = e.target.closest('.scouting-item-dashboard');
            if (!card) return;

            // Jika klik berasal dari elemen interaktif, abaikan agar tidak membuka detail modal
            if (
                e.target.closest('button') || 
                e.target.closest('a') || 
                e.target.closest('input') ||
                e.target.closest('select') ||
                e.target.closest('span[onclick]') ||
                e.target.classList.contains('fa-pencil-alt') ||
                e.target.closest('.action-buttons-wa-wrapper')
            ) {
                return;
            }

            const creatorId = card.getAttribute('data-creator-id');
            if (creatorId) {
                showTask1DetailModal(creatorId);
            }
        });
    }
    
    // ============================================================
    // 3. TASK 1 BUTTONS - DETAIL, SEND LINK, FOLLOW UP
    // ============================================================
    document.querySelectorAll('.task1-detail-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const creatorId = this.getAttribute('data-creator-id');
            console.log('Detail button clicked, creatorId:', creatorId);
            
            if (creatorId) {
                showTask1DetailModal(creatorId);
            } else {
                const parent = this.closest('.scouting-item-dashboard');
                if (parent) {
                    const id = parent.getAttribute('data-creator-id');
                    console.log('Fallback creatorId from parent:', id);
                    if (id) showTask1DetailModal(id);
                } else {
                    showToastGlobal('Creator ID tidak ditemukan', 'error');
                }
            }
        });
    });
    
    document.querySelectorAll('.task1-send-link-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const creatorId = this.getAttribute('data-creator-id');
            if (creatorId) {
                showTask1SendLinkModal(creatorId);
            } else {
                showToastGlobal('Creator ID tidak ditemukan', 'error');
            }
        });
    });
    
    document.querySelectorAll('.task1-followup-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const creatorId = this.getAttribute('data-creator-id');
            console.log('Follow Up button clicked, creatorId:', creatorId);
            
            if (creatorId) {
                showTask1FollowUpModal(creatorId);
            } else {
                const parent = this.closest('.scouting-item-dashboard');
                if (parent) {
                    const id = parent.getAttribute('data-creator-id');
                    if (id) showTask1FollowUpModal(id);
                } else {
                    showToastGlobal('Creator ID tidak ditemukan', 'error');
                }
            }
        });
    });
    
    // ============================================================
    // 4. RESYNC WA BUTTON (fetch dari TAP API secara individual)
    // ============================================================
    document.addEventListener('click', async function(e) {
        const btnEl = e.target.closest('.resync-wa-btn');
        if (!btnEl) return;

        e.preventDefault();
        e.stopPropagation(); // Mencegah card memicu click event lainnya

        const creatorId   = btnEl.getAttribute('data-creator-id');
        const creatorName = btnEl.getAttribute('data-creator-name');

        if (!creatorId) return;

        const originalText = btnEl.innerHTML;
        btnEl.disabled    = true;
        btnEl.innerHTML   = '<i class="fas fa-spinner fa-pulse"></i> Fetching...';

        const phoneDisplay = document.getElementById('phoneDisplay_' + creatorId);
        const card         = btnEl.closest('.scouting-item-dashboard');

        if (phoneDisplay) {
            phoneDisplay.innerHTML = '<i class="fas fa-spinner fa-pulse" style="color:#6b7280; font-size:8px;"></i> <span style="color:#6b7280; font-size:9px;">Mengambil...</span>';
        }

        try {
            const response = await fetch(BASE_URL + 'is/get_creator_phone_from_tap', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ creator_id: creatorId })
            });

            const result = await response.json();

            if (result.success && result.phone && result.phone !== 'no_phone') {
                showToastGlobal('✅ Nomor WA berhasil diambil dari TAP!', 'success');

                if (phoneDisplay) {
                    phoneDisplay.innerHTML = '<i class="fab fa-whatsapp" style="color: #25D366;"></i> ' 
                        + escapeHtml(result.phone)
                        + ' <span onclick="event.stopPropagation(); window.openUpdatePhoneModal(\'' + creatorId + '\', \'\')" title="Edit nomor WA" style="cursor:pointer; color:#6b7280; font-size:8px; margin-left:2px;"><i class="fas fa-pencil-alt"></i></span>';
                }

                // Sembunyikan pembungkus tombol karena nomor sudah ada
                const wrapper = btnEl.closest('.action-buttons-wa-wrapper');
                if (wrapper) wrapper.style.display = 'none';
                if (card) card.setAttribute('data-no-phone', '0');

            } else if (result.phone === 'no_phone' || (!result.success && result.phone === 'no_phone')) {
                showToastGlobal('⚠️ Creator tidak mencantumkan nomor WA. Tim CA harus mencari & input manual.', 'warning');

                if (phoneDisplay) {
                    phoneDisplay.innerHTML = '<i class="fab fa-whatsapp" style="color: #25D366;"></i> '
                        + '<span style="color: #d97706; font-size: 8.5px;" title="CA harus mencari nomor WA & input manual">Tidak mencantumkan nomor WA</span>'
                        + ' <span onclick="event.stopPropagation(); window.openUpdatePhoneModal(\'' + creatorId + '\', \'\')" title="Input nomor WA manual" style="cursor:pointer; color:#d97706; font-size:8px; margin-left:2px;"><i class="fas fa-pencil-alt"></i></span>';
                }

                btnEl.innerHTML  = originalText;
                btnEl.disabled   = false;
                if (card) card.setAttribute('data-no-phone', '0');

            } else {
                showToastGlobal(result.message || 'Gagal mengambil nomor WA dari TAP.', 'error');
                if (phoneDisplay) {
                    phoneDisplay.innerHTML = '<i class="fab fa-whatsapp" style="color: #25D366;"></i> <span style="color: #ef4444;">Tidak ada</span>';
                }
                btnEl.innerHTML  = originalText;
                btnEl.disabled   = false;
            }
        } catch (error) {
            showToastGlobal('Error: ' + error.message, 'error');
            if (phoneDisplay) {
                phoneDisplay.innerHTML = '<i class="fab fa-whatsapp" style="color: #25D366;"></i> <span style="color: #ef4444;">Tidak ada</span>';
            }
            btnEl.innerHTML = originalText;
            btnEl.disabled  = false;
        }
    });

    // ============================================================
    // 5. ADD CREATOR BUTTONS
    // ============================================================

    const addCreatorBtn = document.getElementById('addCreatorQuickBtnDashboard');
    if (addCreatorBtn) {
        const newAddBtn = addCreatorBtn.cloneNode(true);
        addCreatorBtn.parentNode.replaceChild(newAddBtn, addCreatorBtn);
        
        newAddBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            showAddCreatorModalIS();
        });
    }
    
    const importBtn = document.getElementById('importCreatorBtnDashboard');
    if (importBtn) {
        importBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const importModal = document.getElementById('importModalDashboard');
            if (importModal) {
                importModal.classList.add('active');
            } else {
                showToastGlobal('Fitur import belum tersedia', 'warning');
            }
        });
    }
    
    // ============================================================
    // 6. CLOSE MODALS ON OVERLAY CLICK
    // ============================================================
    document.querySelectorAll('.modal-overlay-dashboard').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
                this.classList.remove('active');
            }
        });
    });
    
    // ============================================================
    // 7. ESC KEY TO CLOSE MODALS
    // ============================================================
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay-dashboard.active').forEach(modal => {
                modal.style.display = 'none';
                modal.classList.remove('active');
            });
            closeAddCreatorTask3();
            closeModalIS();
        }
    });
    
    // ============================================================
    // 8. ADD CREATOR FORM - TASK 3
    // ============================================================
    const formTask3 = document.getElementById('addCreatorTask3Form');
    if (formTask3) {
        formTask3.addEventListener('submit', async function(e) {
            e.preventDefault();
            await submitCreatorForm('task3');
        });
    }
    
    // ============================================================
    // 9. ADD CREATOR FORM - TASK 1
    // ============================================================
    const formTask1 = document.getElementById('addCreatorTask1Form');
    if (formTask1) {
        formTask1.addEventListener('submit', async function(e) {
            e.preventDefault();
            await submitCreatorForm('task1');
        });
    }
    
    // ============================================================
    // 10. CLOSE MODALS ON OVERLAY CLICK - TASK 1 & 3 MODALS
    // ============================================================
    const modalTask3 = document.getElementById('addCreatorTask3Modal');
    if (modalTask3) {
        modalTask3.addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddCreatorTask3();
            }
        });
    }
    
    const modalTask1 = document.getElementById('taskModalDashboard');
    if (modalTask1) {
        modalTask1.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModalIS();
            }
        });
    }
    
    const closeBtnTask1 = document.getElementById('closeTaskModalDashboard');
    if (closeBtnTask1) {
        closeBtnTask1.addEventListener('click', function() {
            closeModalIS();
        });
    }
    
    console.log('Dashboard initialization complete');
});
</script>

<!-- ============================================================ -->
<!-- MODAL: KONFIRMASI DUPLIKAT NOMOR HP -->
<!-- ============================================================ -->
<div id="phoneDuplicateModal" style="
    display:none; position:fixed; inset:0; z-index:99999;
    background:rgba(0,0,0,0.7); backdrop-filter:blur(4px);
    align-items:center; justify-content:center;">
    <div style="
        background:#1a1f2e; border:1px solid rgba(139,92,246,0.3);
        border-radius:20px; padding:28px; max-width:480px; width:95%;
        box-shadow:0 20px 60px rgba(0,0,0,0.5);">

        <!-- Header -->
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:16px;">
            <div style="width:42px;height:42px;background:rgba(245,158,11,0.15);border-radius:12px;
                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-exclamation-triangle" style="color:#f59e0b;font-size:18px;"></i>
            </div>
            <div>
                <h4 style="color:#f59e0b;margin:0;font-size:15px;font-weight:700;">Nomor HP Sudah Terdaftar</h4>
                <p style="color:#9aaebe;margin:0;font-size:12px;">Ditemukan creator lain dengan nomor yang sama</p>
            </div>
        </div>

        <!-- List matches -->
        <div id="phoneDuplicateList" style="
            background:rgba(255,255,255,0.04); border-radius:12px;
            padding:12px; margin-bottom:16px; max-height:220px; overflow-y:auto;"></div>

        <!-- Pertanyaan konfirmasi -->
        <p style="color:#e2e8f0;font-size:13px;margin-bottom:20px;line-height:1.6;">
            Apakah kamu yakin ingin tetap menyimpan data ini?
        </p>

        <!-- Tombol -->
        <div style="display:flex;gap:10px;">
            <button onclick="closePhoduplicateModal()"
                style="flex:1;background:rgba(255,255,255,0.06);color:#9aaebe;
                       padding:11px;border-radius:40px;border:1px solid rgba(255,255,255,0.1);
                       cursor:pointer;font-weight:600;font-size:13px;">
                <i class="fas fa-times"></i> Batal
            </button>
            <button id="phoneDuplicateConfirmBtn"
                style="flex:1;background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;
                       padding:11px;border-radius:40px;border:none;
                       cursor:pointer;font-weight:600;font-size:13px;">
                <i class="fas fa-check"></i> Ya, Simpan Tetap
            </button>
        </div>
    </div>
</div>

<script>
// ============================================================
// PHONE DUPLICATE MODAL HELPERS
// ============================================================
const _statusLabel = {
    PENDING:       {text:'Scouting',   color:'#8b5cf6'},
    LINK_SENT:     {text:'Link Sent',  color:'#3b82f6'},
    LINK_SWAPPING: {text:'Swapping',   color:'#06b6d4'},
    APPROVED:      {text:'Approved',   color:'#10b981'},
    ACTIVE:        {text:'Monitoring', color:'#10b981'},
    REJECTED:      {text:'Rejected',   color:'#ef4444'},
};

function closePhoduplicateModal() {
    document.getElementById('phoneDuplicateModal').style.display = 'none';
}

// ============================================================
// SHARED SUBMIT FUNCTION (task1 & task3)
// ============================================================
async function submitCreatorForm(taskType, forceSave = false) {
    const isTask1 = taskType === 'task1';

    const usernameEl  = document.getElementById(isTask1 ? 'creatorUsernameIS' : 'task3Username');
    const fullNameEl  = document.getElementById(isTask1 ? 'creatorNameIS'     : 'task3FullName');
    const categoryEl  = document.getElementById(isTask1 ? 'creatorCategoryIS' : 'task3Category');
    const phoneEl     = document.getElementById(isTask1 ? 'creatorPhoneIS'    : 'task3Phone');
    const emailEl     = document.getElementById(isTask1 ? 'creatorEmailIS'    : 'task3Email');
    const followersEl = document.getElementById(isTask1 ? 'creatorFollowersIS': 'task3Followers');
    const brandEl     = document.getElementById(isTask1 ? 'creatorBrandIS'    : 'task3Brand');
    const btn         = document.getElementById(isTask1 ? 'saveCreatorBtnIS'  : 'submitTask3Btn');
    const endpoint    = isTask1 ? 'is/add_creator' : 'is/add_creator_task3';
    const closeFn     = isTask1 ? closeModalIS      : closeAddCreatorTask3;

    const username = usernameEl.value.trim();
    if (!username) {
        showToastGlobal('Username TikTok wajib diisi!', 'error');
        usernameEl.style.borderColor = '#ef4444';
        setTimeout(() => usernameEl.style.borderColor = '', 2000);
        return;
    }

    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Menambahkan...';

    try {
        const formData = new FormData();
        formData.append('username',       username.replace('@', ''));
        formData.append('full_name',      fullNameEl.value.trim());
        formData.append('category',       categoryEl.value);
        formData.append('phone',          phoneEl.value.trim());
        formData.append('email',          emailEl.value.trim());
        formData.append('follower_count', followersEl.value.trim() || 0);
        formData.append('brand_id',       brandEl.value);
        formData.append('force_save',     forceSave ? '1' : '0');

        if (brandEl.value) {
            formData.append('shop_name', brandEl.options[brandEl.selectedIndex]?.text || '');
        }
        if (isTask1) {
            const gmvEl = document.getElementById('creatorGmvIS');
            if (gmvEl) formData.append('gmv', gmvEl.value.trim());
        }

        const response = await fetch(BASE_URL + endpoint, { method: 'POST', body: formData });
        const result   = await response.json();

        // --- Phone duplicate: tampilkan modal konfirmasi ---
        if (!result.success && result.phone_duplicate) {
            btn.disabled = false;
            btn.innerHTML = originalText;

            // Render daftar creator yang memiliki nomor sama
            const list = document.getElementById('phoneDuplicateList');
            list.innerHTML = (result.matches || []).map(c => {
                const sl = _statusLabel[c.status] || {text: c.status, color: '#9aaebe'};
                return `
                <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid rgba(255,255,255,0.06);">
                    <div style="width:34px;height:34px;border-radius:50%;background:rgba(139,92,246,0.2);
                                display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fab fa-tiktok" style="color:#8b5cf6;font-size:14px;"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="color:#e2e8f0;font-weight:600;font-size:13px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            ${escapeHtml(c.full_name || c.username)}
                        </div>
                        <div style="color:#9aaebe;font-size:11px;">
                            @${escapeHtml(c.username)}
                            &nbsp;·&nbsp;
                            <i class="fab fa-whatsapp" style="color:#25d366;"></i>
                            ${escapeHtml(c.phone)}
                        </div>
                    </div>
                    <span style="background:rgba(255,255,255,0.05);color:${sl.color};
                                 padding:3px 8px;border-radius:20px;font-size:11px;font-weight:600;
                                 border:1px solid ${sl.color}33;white-space:nowrap;">
                        ${sl.text}
                    </span>
                </div>`;
            }).join('');

            // Daftarkan handler tombol "Ya, Simpan Tetap"
            const confirmBtn = document.getElementById('phoneDuplicateConfirmBtn');
            confirmBtn.onclick = async function() {
                closePhoduplicateModal();
                await submitCreatorForm(taskType, true); // force_save = true
            };

            document.getElementById('phoneDuplicateModal').style.display = 'flex';
            return;
        }

        // --- Sukses ---
        if (result.success) {
            showToastGlobal(result.message, 'success');
            setTimeout(() => { closeFn(); location.reload(); }, 1500);
        } else {
            showToastGlobal(result.message || 'Gagal menambahkan creator', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }

    } catch (error) {
        console.error('Error:', error);
        showToastGlobal('Error: ' + error.message, 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// Tutup phone duplicate modal saat klik overlay
document.getElementById('phoneDuplicateModal').addEventListener('click', function(e) {
    if (e.target === this) closePhoduplicateModal();
});
</script>

<!-- ============================================================ -->
<!-- AUTO SCOUTING JS -->
<!-- ============================================================ -->
<script>
(function() {
    // ============================================================
    // STATE
    // ============================================================
    let _scoutingOffset   = 0;
    const _scoutingLimit  = 50;
    let _scoutingTotal    = 0;
    let _scoutingTimer    = null;
    let _scoutingLoading  = false;
    let _scoutingObserver = null;

    // ============================================================
    // HELPERS
    // ============================================================
    function _fmtRp(val) {
        return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
    }
    function _fmtNum(val) {
        const n = Number(val || 0);
        if (n >= 1000000) return (n / 1000000).toFixed(1) + 'M';
        if (n >= 1000)    return (n / 1000).toFixed(1) + 'K';
        return String(n);
    }
    function _srcBadge(src) {
        if (src === 'fastmoss')   return {icon:'fas fa-database', color:'#f59e0b', label:'FastMoss'};
        if (src === 'tiktok_api') return {icon:'fab fa-tiktok',   color:'#e2e8f0', label:'TikTok API'};
        return                           {icon:'fas fa-chart-bar',color:'#34d399', label:'Dari Order'};
    }

    // ============================================================
    // RENDER SATU KARTU — lebar tetap agar scroll horizontal rapi
    // ============================================================
    function _renderCard(item) {
        const src   = _srcBadge(item.source);
        const phone = item.phone
            ? `<span style="color:#25d366;"><i class="fab fa-whatsapp"></i> ${escapeHtml(item.phone)}</span>`
            : `<span style="color:#ef4444;font-size:10px;"><i class="fas fa-exclamation-circle"></i> WA tidak tersedia</span>`;

        const avatar = item.avatar_url
            ? `<img src="${escapeHtml(item.avatar_url)}" alt=""
                    style="width:36px;height:36px;border-radius:50%;object-fit:cover;flex-shrink:0;"
                    onerror="this.outerHTML='<div style=\'width:36px;height:36px;border-radius:50%;background:rgba(139,92,246,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;\'><i class=\'fab fa-tiktok\' style=\'color:#8b5cf6;\'></i></div>'">`
            : `<div style="width:36px;height:36px;border-radius:50%;background:rgba(139,92,246,0.2);
                           display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                   <i class="fab fa-tiktok" style="color:#8b5cf6;"></i>
               </div>`;

        const prodImg = item.product_image
            ? `<img src="${escapeHtml(item.product_image)}" alt=""
                    style="width:26px;height:26px;border-radius:6px;object-fit:cover;flex-shrink:0;"
                    onerror="this.style.display='none'">`
            : '';

        return `
        <div data-scouting-id="${item.id}"
             style="flex:0 0 260px; width:260px;
                    background:rgba(9,17,34,0.75);border:1px solid rgba(112,136,185,0.14);
                    border-radius:14px;padding:12px;display:flex;flex-direction:column;gap:8px;
                    transition:border-color .2s, transform .15s;">

            <!-- Row 1: Avatar + Username + Source -->
            <div style="display:flex;align-items:center;gap:8px;">
                ${avatar}
                <div style="flex:1;min-width:0;">
                    <div style="font-size:12px;font-weight:700;color:var(--text-primary);
                                overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        <i class="fab fa-tiktok" style="color:#8b5cf6;font-size:10px;"></i>
                        @${escapeHtml(item.username)}
                    </div>
                    ${item.full_name && item.full_name !== item.username
                        ? `<div style="font-size:10px;color:var(--text-secondary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escapeHtml(item.full_name)}</div>`
                        : ''}
                </div>
                <span style="background:rgba(255,255,255,0.05);color:${src.color};
                             padding:2px 6px;border-radius:12px;font-size:9px;font-weight:600;
                             border:1px solid ${src.color}33;white-space:nowrap;flex-shrink:0;">
                    <i class="${src.icon}"></i>
                </span>
            </div>

            <!-- Row 2: Brand + Campaign badges -->
            <div style="display:flex;flex-wrap:wrap;gap:4px;">
                ${item.brand_name
                    ? `<span style="background:rgba(74,222,128,0.1);color:#4ade80;
                                   padding:2px 7px;border-radius:10px;font-size:9px;
                                   overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:120px;">
                           <i class="fas fa-store" style="font-size:8px;"></i> ${escapeHtml(item.brand_name)}
                       </span>`
                    : ''}
                ${item.campaign_name
                    ? `<span style="background:rgba(59,130,246,0.1);color:#60a5fa;
                                   padding:2px 7px;border-radius:10px;font-size:9px;
                                   overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:120px;">
                           <i class="fas fa-bullhorn" style="font-size:8px;"></i> ${escapeHtml(item.campaign_name)}
                       </span>`
                    : ''}
                ${item.status === 'contacted'
                    ? `<span style="background:rgba(139,92,246,0.1);color:#a78bfa;
                                   padding:2px 7px;border-radius:10px;font-size:9px;
                                   overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:240px;width:100%;display:inline-flex;align-items:center;gap:3px;" 
                                   title="Dihubungi oleh ${escapeHtml(item.contacted_by_name || 'CA')}">
                           <i class="fas fa-paper-plane" style="font-size:8px;"></i> Dihubungi: ${escapeHtml(item.contacted_by_name || 'CA')}
                       </span>`
                    : ''}
            </div>

            <!-- Row 3: Produk -->
            ${item.product_name ? `
            <div style="display:flex;align-items:center;gap:6px;
                        background:rgba(255,255,255,0.03);border-radius:8px;
                        padding:6px 8px;border:1px solid rgba(255,255,255,0.05);">
                ${prodImg}
                <div style="flex:1;min-width:0;">
                    <div style="font-size:9px;color:var(--text-secondary);">Produk dijual</div>
                    <div style="font-size:10px;font-weight:600;color:var(--text-primary);
                                overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        ${escapeHtml(item.product_name)}
                    </div>
                </div>
            </div>` : ''}

            <!-- Row 4: GMV + Sales -->
            <div style="display:flex;gap:6px;">
                <div style="flex:1;text-align:center;background:rgba(251,191,36,0.08);
                            border-radius:8px;padding:5px 2px;">
                    <div style="color:#fbbf24;font-weight:700;font-size:12px;
                                overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        ${_fmtRp(item.gmv)}
                    </div>
                    <div style="color:var(--text-secondary);font-size:9px;">GMV</div>
                </div>
                <div style="flex:1;text-align:center;background:rgba(59,130,246,0.08);
                            border-radius:8px;padding:5px 2px;">
                    <div style="color:#60a5fa;font-weight:700;font-size:12px;">${_fmtNum(item.sales_count)}</div>
                    <div style="color:var(--text-secondary);font-size:9px;">Terjual</div>
                </div>
                ${item.follower_count > 0 ? `
                <div style="flex:1;text-align:center;background:rgba(167,139,250,0.08);
                            border-radius:8px;padding:5px 2px;">
                    <div style="color:#a78bfa;font-weight:700;font-size:12px;">${_fmtNum(item.follower_count)}</div>
                    <div style="color:var(--text-secondary);font-size:9px;">Followers</div>
                </div>` : ''}
            </div>

            <!-- Row 5: WA -->
            <div style="font-size:10px;">${phone}</div>

            <!-- Row 6: Tombol aksi -->
            <div style="display:flex;gap:6px;margin-top:auto;">
                ${item.status === 'contacted' ? `
                    <button onclick="scoutingContact(${item.id})"
                        style="flex:1;padding:7px 4px;background:rgba(139,92,246,0.15);
                               color:#a78bfa;border:1px solid rgba(139,92,246,0.3);border-radius:16px;cursor:pointer;
                               font-size:11px;font-weight:600;display:inline-flex;align-items:center;
                               justify-content:center;gap:4px;">
                        <i class="fab fa-whatsapp"></i> Hubungi Lagi
                    </button>
                ` : `
                    <button onclick="scoutingContact(${item.id})"
                        style="flex:1;padding:7px 4px;background:linear-gradient(135deg,#8b5cf6,#3b82f6);
                               color:#fff;border:none;border-radius:16px;cursor:pointer;
                               font-size:11px;font-weight:600;display:inline-flex;align-items:center;
                               justify-content:center;gap:4px;">
                        <i class="fab fa-whatsapp"></i> Hubungi
                    </button>
                `}
                <button onclick="openScoutingCreatorDetail(${item.id}, '${escapeHtml(item.username)}')"
                    title="Lihat brand kolaborasi & GMV"
                    style="padding:7px 10px;background:rgba(251,191,36,0.1);color:#fbbf24;
                           border:1px solid rgba(251,191,36,0.25);border-radius:16px;
                           cursor:pointer;font-size:11px;">
                    <i class="fas fa-chart-bar"></i>
                </button>
                <button onclick="scoutingIgnore(${item.id})"
                    style="padding:7px 10px;background:rgba(239,68,68,0.1);color:#ef4444;
                           border:1px solid rgba(239,68,68,0.2);border-radius:16px;
                           cursor:pointer;font-size:11px;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>`;
    }

    // ============================================================
    // LOAD SCOUTING LIST
    // ============================================================
    window.loadScoutingList = function(reset = true) {
        if (_scoutingLoading) return;
        if (reset) _scoutingOffset = 0;

        const grid   = document.getElementById('scoutingListGrid');
        const brand  = document.getElementById('scoutingBrandFilter')?.value  || '';
        const source = document.getElementById('scoutingSourceFilter')?.value || '';
        const search = document.getElementById('scoutingSearch')?.value        || '';

        if (reset) {
            grid.innerHTML = `
            <div class="scouting-loading-placeholder"
                 style="width: 100%; text-align: center; padding: 40px 20px;
                        color: var(--text-secondary); display: flex; flex-direction: column;
                        align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-spinner fa-pulse fa-2x"
                   style="color:rgba(139,92,246,0.4);margin-bottom:12px;display:block;"></i>
                Memuat...
            </div>`;
        }

        _scoutingLoading = true;

        const params = new URLSearchParams({
            limit:  _scoutingLimit,
            offset: _scoutingOffset,
            ...(brand  && { brand_id: brand }),
            ...(source && { source }),
            ...(search && { search }),
        });

        fetch(BASE_URL + 'is/get_scouting_list?' + params.toString())
            .then(r => r.json())
            .then(res => {
                _scoutingLoading = false;
                console.log('[Scouting] Response:', res);
                if (!res.success) {
                    console.warn('[Scouting] success=false:', res);
                    return;
                }

                _scoutingTotal = res.total || 0;
                document.getElementById('scoutingBadgeCount').textContent = _scoutingTotal;

                // Populate brand filter dropdown (sekali saja)
                const bf = document.getElementById('scoutingBrandFilter');
                if (bf && bf.options.length <= 1 && res.brands?.length) {
                    res.brands.forEach(b => bf.appendChild(new Option(b.brand_name, b.brand_id)));
                }

                // Bersihkan loading placeholder
                if (reset) grid.innerHTML = '';

                if (!res.data?.length && reset) {
                    grid.innerHTML = `
                    <div style="width: 100%; display: flex; justify-content: center; padding: 10px 0;">
                        <div style="min-width:300px; text-align:center; padding:48px 24px;
                                    border:1px dashed rgba(139,92,246,0.2);border-radius:14px;flex-shrink:0;">
                            <i class="fas fa-robot"
                               style="font-size:32px;color:rgba(139,92,246,0.3);margin-bottom:10px;display:block;"></i>
                            <div style="color:var(--text-primary);font-weight:600;margin-bottom:4px;font-size:13px;">
                                Belum ada data scouting
                            </div>
                            <div style="color:var(--text-secondary);font-size:11px;">
                                Klik <strong>Perbarui</strong> untuk mengambil data creator dari riwayat order brand aktif.
                            </div>
                        </div>
                    </div>`;
                    _disconnectObserver();
                    return;
                }

                res.data.forEach(item => {
                    grid.insertAdjacentHTML('beforeend', _renderCard(item));
                });

                _scoutingOffset += res.data.length;

                // Tidak load lebih dari _scoutingLimit (50 teratas GMV sudah cukup)
                _disconnectObserver();

                // Tampilkan/sembunyikan fade kanan
                _updateFade();
            })
            .catch(() => { _scoutingLoading = false; });
    };

    // ============================================================
    // INFINITE SCROLL via IntersectionObserver
    // ============================================================
    function _setupObserver() {
        _disconnectObserver();
        const sentinel = document.getElementById('scoutingLoadMore');
        if (!sentinel) return;
        sentinel.style.display = 'block';

        _scoutingObserver = new IntersectionObserver(entries => {
            if (entries[0].isIntersecting && !_scoutingLoading) {
                loadScoutingList(false);
            }
        }, { threshold: 0.1 });

        _scoutingObserver.observe(sentinel);
    }

    function _disconnectObserver() {
        if (_scoutingObserver) { _scoutingObserver.disconnect(); _scoutingObserver = null; }
        const s = document.getElementById('scoutingLoadMore');
        if (s) s.style.display = 'none';
    }

    // Scroll horizontal juga trigger load-more saat hampir di ujung kanan
    function _onScrollGrid(e) {
        const el = e.target;
        if (el.scrollLeft + el.clientWidth >= el.scrollWidth - 80) {
            if (!_scoutingLoading && _scoutingOffset < _scoutingTotal) {
                loadScoutingList(false);
            }
        }
        _updateFade();
    }

    function _updateFade() {
        const grid  = document.getElementById('scoutingListGrid');
        const fade  = document.getElementById('scoutingFadeRight');
        if (!grid || !fade) return;
        const atEnd = grid.scrollLeft + grid.clientWidth >= grid.scrollWidth - 10;
        fade.style.opacity = atEnd ? '0' : '1';
    }

    // ============================================================
    // DEBOUNCE SEARCH
    // ============================================================
    window.debounceScoutingSearch = function() {
        clearTimeout(_scoutingTimer);
        _scoutingTimer = setTimeout(() => loadScoutingList(true), 400);
    };

    // ============================================================
    // REFRESH (manual populate dari server)
    // ============================================================
    window.refreshScoutingList = function() {
        const btn  = document.getElementById('refreshScoutingBtn');
        const icon = document.getElementById('refreshScoutingIcon');
        if (btn) { btn.disabled = true; icon.classList.add('fa-spin'); }

        fetch(BASE_URL + 'is/refresh_scouting_list', { method: 'POST' })
            .then(r => r.json())
            .then(res => {
                showToastGlobal(res.message || 'Selesai', res.success ? 'success' : 'error');
                if (res.success) loadScoutingList(true);
            })
            .catch(() => showToastGlobal('Gagal menghubungi server', 'error'))
            .finally(() => {
                if (btn) { btn.disabled = false; icon.classList.remove('fa-spin'); }
            });
    };

    // ============================================================
    // CONTACT CREATOR (WhatsApp Outreach & update status)
    // ============================================================
    window.scoutingContact = function(scoutingId) {
        const card = document.querySelector(`[data-scouting-id="${scoutingId}"]`);
        const btn  = card?.querySelector('button');
        const orig = btn?.innerHTML;
        
        // Cek jika nomor WA tidak tersedia (ditandai dengan text 'WA tidak tersedia' pada kartu)
        const hasPhone = card?.innerHTML.includes('fa-whatsapp') === true;
        
        let inputPhone = '';
        if (!hasPhone) {
            inputPhone = prompt('Nomor WhatsApp creator tidak tersedia. Silakan masukkan nomor WhatsApp creator (contoh: 08123456789):');
            if (inputPhone === null) return; // Klik Batal
            if (inputPhone.trim() === '') {
                showToastGlobal('Nomor WhatsApp wajib diisi!', 'error');
                return;
            }
        }

        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i>'; }

        const fd = new FormData();
        fd.append('scouting_id', scoutingId);
        if (inputPhone) {
            fd.append('phone', inputPhone.trim());
        }

        fetch(BASE_URL + 'is/get_scouting_contact_link', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    window.open(res.redirect_url, '_blank');
                    showToastGlobal('WhatsApp dibuka & status diperbarui!', 'success');
                    setTimeout(() => {
                        loadScoutingList(true);
                    }, 1000);
                } else {
                    showToastGlobal(res.message || 'Gagal', 'error');
                    if (btn) { btn.disabled = false; btn.innerHTML = orig; }
                }
            })
            .catch(() => {
                showToastGlobal('Gagal menghubungi server', 'error');
                if (btn) { btn.disabled = false; btn.innerHTML = orig; }
            });
    };

    // ============================================================
    // ONBOARD
    // ============================================================
    window.scoutingOnboard = function(scoutingId) {
        const card = document.querySelector(`[data-scouting-id="${scoutingId}"]`);
        const btn  = card?.querySelector('button');
        const orig = btn?.innerHTML;
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i>'; }

        const fd = new FormData();
        fd.append('scouting_id', scoutingId);

        fetch(BASE_URL + 'is/onboard_creator_from_scouting', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    showToastGlobal(res.message, 'success');
                    if (card) {
                        card.style.transition = 'opacity .35s, transform .35s';
                        card.style.opacity    = '0';
                        card.style.transform  = 'scale(.9)';
                        setTimeout(() => {
                            card.remove();
                            _scoutingTotal--;
                            document.getElementById('scoutingBadgeCount').textContent = _scoutingTotal;
                            // Tarik kartu baru jika row jadi pendek
                            if (_scoutingOffset < _scoutingTotal) loadScoutingList(false);
                        }, 350);
                    }
                } else {
                    showToastGlobal(res.message || 'Gagal', 'error');
                    if (btn) { btn.disabled = false; btn.innerHTML = orig; }
                }
            })
            .catch(() => {
                showToastGlobal('Gagal', 'error');
                if (btn) { btn.disabled = false; btn.innerHTML = orig; }
            });
    };

    // ============================================================
    // IGNORE
    // ============================================================
    window.scoutingIgnore = function(scoutingId) {
        const card = document.querySelector(`[data-scouting-id="${scoutingId}"]`);
        const fd   = new FormData();
        fd.append('scouting_id', scoutingId);

        fetch(BASE_URL + 'is/ignore_scouting_creator', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if (res.success && card) {
                    card.style.transition = 'opacity .3s';
                    card.style.opacity    = '0';
                    setTimeout(() => {
                        card.remove();
                        _scoutingTotal--;
                        document.getElementById('scoutingBadgeCount').textContent = _scoutingTotal;
                        if (_scoutingOffset < _scoutingTotal) loadScoutingList(false);
                    }, 300);
                }
            });
    };

    // ============================================================
    // INIT
    // ============================================================
    document.addEventListener('DOMContentLoaded', function() {
        loadScoutingList(true);

        // Pasang scroll listener setelah DOM ready
        setTimeout(() => {
            const grid = document.getElementById('scoutingListGrid');
            if (grid) grid.addEventListener('scroll', _onScrollGrid, { passive: true });
        }, 500);
    });

})();
</script>


<!-- ============================================================ -->
<!-- FITUR F: MODAL KONFIRMASI KESEDIAAN SAMPLE (dari Dashboard)  -->
<!-- ============================================================ -->
<style>
.db-sample-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.9); backdrop-filter: blur(8px);
    z-index: 5000; display: flex; align-items: center; justify-content: center;
    visibility: hidden; opacity: 0; transition: 0.2s;
}
.db-sample-overlay.active { visibility: visible; opacity: 1; }
.db-sample-modal {
    background: #111827; border: 1px solid rgba(139,92,246,0.4);
    border-radius: 24px; padding: 28px 32px; width: 95%; max-width: 460px;
    text-align: center; box-shadow: 0 20px 60px rgba(0,0,0,0.5);
}
.db-sample-modal h4 { font-size: 17px; font-weight: 700; color: #e2e8f0; margin: 0 0 10px; }
.db-sample-modal p { font-size: 13px; color: #94a3b8; margin: 0 0 22px; }
.db-sample-btns { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
.db-sample-btn { padding: 10px 22px; border-radius: 40px; border: none; font-size: 13px; font-weight: 600; cursor: pointer; transition: 0.2s; }
.db-sample-btn:hover { filter: brightness(1.1); }

/* Rec modal di dashboard */
.db-rec-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.9); backdrop-filter: blur(8px);
    z-index: 5001; display: flex; align-items: center; justify-content: center;
    visibility: hidden; opacity: 0; transition: 0.2s;
}
.db-rec-overlay.active { visibility: visible; opacity: 1; }
.db-rec-modal {
    background: #111827; border: 1px solid rgba(139,92,246,0.4);
    border-radius: 24px; width: 95%; max-width: 760px;
    max-height: 88vh; overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,0.5);
}
.db-rec-head {
    padding: 20px 24px 14px; border-bottom: 1px solid rgba(255,255,255,0.08);
    position: sticky; top: 0; background: #111827; z-index: 1;
    display: flex; justify-content: space-between; align-items: flex-start;
}
.db-rec-head h4 { font-size: 15px; font-weight: 700; color: #e2e8f0; margin: 0; }
.db-rec-head p { font-size: 11px; color: #94a3b8; margin: 4px 0 0; }
.db-rec-close { background: none; border: none; color: #94a3b8; font-size: 22px; cursor: pointer; }
.db-rec-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; padding: 18px 24px; }
.db-rec-item {
    background: #1e293b; border: 2px solid rgba(255,255,255,0.07);
    border-radius: 16px; padding: 12px; cursor: pointer; transition: 0.2s;
    position: relative;
}
.db-rec-item:hover { border-color: #8b5cf6; transform: translateY(-2px); }
.db-rec-item.sel { border-color: #4ade80; background: rgba(74,222,128,0.05); }
.db-rec-item .chk {
    position: absolute; top: 8px; right: 8px;
    width: 20px; height: 20px; border-radius: 50%;
    background: #4ade80; color: #0a0e17; font-size: 11px; font-weight: 700;
    display: none; align-items: center; justify-content: center;
}
.db-rec-item.sel .chk { display: flex; }
.db-rec-img { width: 100%; height: 90px; object-fit: cover; border-radius: 10px; background: #0f1420; margin-bottom: 8px; }
.db-rec-name { font-size: 11px; font-weight: 600; color: #e2e8f0; line-height: 1.4; margin-bottom: 4px; }
.db-rec-brand { font-size: 10px; color: #94a3b8; }
.db-rec-foot {
    padding: 14px 24px; border-top: 1px solid rgba(255,255,255,0.08);
    display: flex; justify-content: space-between; align-items: center;
    position: sticky; bottom: 0; background: #111827; flex-wrap: wrap; gap: 8px;
}
.db-rec-foot-count { font-size: 12px; color: #94a3b8; }
.db-rec-foot-count strong { color: #4ade80; }
</style>

<!-- Modal Konfirmasi Kesediaan -->
<div class="db-sample-overlay" id="dbWillingOverlay">
    <div class="db-sample-modal">
        <div style="font-size:38px;margin-bottom:10px">🎁</div>
        <h4>Konfirmasi Kesediaan Sample</h4>
        <p>Apakah creator <strong id="dbWillingName" style="color:#4ade80"></strong> bersedia menerima sample produk?</p>
        <div style="margin-bottom:18px">
            <input type="text" id="dbWillingNotes" placeholder="Catatan (opsional)..."
                style="width:100%;padding:9px 14px;background:#0f1420;border:1px solid rgba(255,255,255,0.1);border-radius:12px;color:#e2e8f0;font-size:12px;outline:none;box-sizing:border-box">
        </div>
        <div class="db-sample-btns">
            <button class="db-sample-btn" style="background:linear-gradient(135deg,#4ade80,#22c55e);color:#0a0e17" onclick="dbSubmitWilling(1)">
                ✅ Ya, Bersedia
            </button>
            <button class="db-sample-btn" style="background:rgba(239,68,68,0.2);color:#ef4444;border:1px solid rgba(239,68,68,0.3)" onclick="dbSubmitWilling(0)">
                ❌ Tidak Bersedia
            </button>
        </div>
        <button onclick="dbCloseWillingModal()"
            style="margin-top:14px;background:transparent;border:none;color:#94a3b8;font-size:12px;cursor:pointer">
            Batal
        </button>
    </div>
</div>

<!-- Modal Rekomendasi Produk -->
<div class="db-rec-overlay" id="dbRecOverlay">
    <div class="db-rec-modal">
        <div class="db-rec-head">
            <div>
                <h4>🎯 Pilih Produk Sample</h4>
                <p id="dbRecSubtitle">Rekomendasi berbasis kategori creator, brand berbeda</p>
            </div>
            <button class="db-rec-close" onclick="dbCloseRecModal()">✕</button>
        </div>
        <div id="dbRecGrid" class="db-rec-grid">
            <div style="grid-column:1/-1;text-align:center;padding:30px;color:#94a3b8">
                <i class="fas fa-spinner fa-pulse fa-2x"></i>
            </div>
        </div>
        <div class="db-rec-foot">
            <div style="display:flex;flex-direction:column;gap:4px">
                <span id="dbRecTotalMsg" style="font-size:11px;color:#94a3b8">Menampilkan 0 data produk sample</span>
                <span class="db-rec-foot-count">Dipilih: <strong id="dbRecCount">0</strong> produk</span>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px">
                <!-- Pilihan metode pengiriman -->
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                    <span style="font-size:10px;color:#94a3b8;font-weight:600;">Metode:</span>
                    <label style="display:flex;align-items:center;gap:5px;font-size:11px;color:#e2e8f0;cursor:pointer">
                        <input type="radio" name="dbDeliveryMethod" value="manual" checked onchange="dbOnMethodChange(this.value)" style="accent-color:#4ade80">
                        Manual
                    </label>
                    <label style="display:flex;align-items:center;gap:5px;font-size:11px;color:#e2e8f0;cursor:pointer">
                        <input type="radio" name="dbDeliveryMethod" value="system" onchange="dbOnMethodChange(this.value)" style="accent-color:#3b82f6">
                        By System (TAP)
                    </label>
                </div>
                <!-- Input TAP Request ID — muncul hanya jika By System -->
                <div id="dbTapIdWrap" style="display:none;width:100%">
                    <input type="text" id="dbTapRequestId"
                        placeholder="TAP Request ID (dari TAP Backend)"
                        style="width:100%;max-width:320px;padding:7px 12px;background:#0f1420;border:1px solid rgba(59,130,246,0.4);border-radius:10px;color:#e2e8f0;font-size:11px;outline:none;">
                    <div style="font-size:10px;color:#64748b;margin-top:3px">ID pengajuan sample dari TAP Backend</div>
                </div>
                <div style="display:flex;gap:8px">
                    <button onclick="dbCloseRecModal()"
                        style="padding:8px 18px;border-radius:40px;border:1px solid rgba(255,255,255,0.15);background:transparent;color:#94a3b8;font-size:12px;cursor:pointer">
                        Batal
                    </button>
                    <button onclick="dbConfirmSampleDelivery()"
                        style="padding:8px 18px;border-radius:40px;border:none;background:linear-gradient(135deg,#4ade80,#22c55e);color:#0a0e17;font-size:12px;font-weight:600;cursor:pointer">
                        <i class="fas fa-paper-plane"></i> Konfirmasi Pengiriman
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
/* =============================================
   FITUR F: JS untuk Dashboard (Konfirmasi Sample & Rekomendasi)
   ============================================= */
(function() {
    let _dbCreatorId   = null;
    let _dbCreatorName = null;
    let _dbSelProducts = [];

    // Buka modal konfirmasi kesediaan
    window.openDashboardWillingModal = function(creatorId, username) {
        _dbCreatorId   = creatorId;
        _dbCreatorName = username;
        _dbSelProducts = [];
        document.getElementById('dbWillingName').textContent = '@' + username;
        document.getElementById('dbWillingNotes').value = '';
        document.getElementById('dbWillingOverlay').classList.add('active');
    };

    window.dbCloseWillingModal = function() {
        document.getElementById('dbWillingOverlay').classList.remove('active');
    };

    window.dbSubmitWilling = function(willing) {
        const notes = document.getElementById('dbWillingNotes').value;
        fetch(BASE_URL + 'is/confirm_sample_willingness', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `creator_id=${_dbCreatorId}&willing=${willing}&notes=${encodeURIComponent(notes)}`
        })
        .then(r => r.json())
        .then(data => {
            dbCloseWillingModal();
            if (!data.success) { showToastGlobal(data.message, 'error'); return; }
            if (willing) {
                // Buka rekomendasi produk
                showToastGlobal('Creator bersedia. Memuat rekomendasi produk...', 'success');
                setTimeout(() => dbOpenRecModal(), 400);
            } else {
                showToastGlobal('Creator tidak bersedia. Dipindahkan ke Monitoring.', 'success');
                setTimeout(() => location.reload(), 1800);
            }
        })
        .catch(() => showToastGlobal('Gagal mengirim konfirmasi', 'error'));
    };

    function dbOpenRecModal() {
        _dbSelProducts = [];
        let _dbRecMap = {};
        document.getElementById('dbRecCount').textContent = '0';
        // Reset metode ke Manual setiap kali modal dibuka
        document.querySelectorAll('input[name="dbDeliveryMethod"]').forEach(r => { r.checked = r.value === 'manual'; });
        document.getElementById('dbTapIdWrap').style.display = 'none';
        document.getElementById('dbTapRequestId').value = '';
        document.getElementById('dbRecGrid').innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:30px;color:#94a3b8"><i class="fas fa-spinner fa-pulse fa-2x"></i></div>';
        document.getElementById('dbRecOverlay').classList.add('active');

        fetch(BASE_URL + 'is/get_sample_recommendations', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'creator_id=' + _dbCreatorId
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success || !data.recommendations || !data.recommendations.length) {
                document.getElementById('dbRecGrid').innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:30px;color:#94a3b8"><i class="fas fa-box-open fa-2x"></i><p>Tidak ada rekomendasi produk tersedia</p></div>';
                return;
            }
            document.getElementById('dbRecSubtitle').textContent =
                'Kategori: ' + (data.creator_categories.join(', ') || 'Semua') +
                ' | Brand creator: ' + (data.creator_brands.join(', ') || '-');

            // Simpan data ke Map — jangan embed JSON ke onclick attribute
            data.recommendations.forEach((p, i) => { _dbRecMap[i] = p; });

            document.getElementById('dbRecTotalMsg').textContent = `Menampilkan ${data.recommendations.length} data produk sample`;

            const grid = document.getElementById('dbRecGrid');
            grid.innerHTML = data.recommendations.map((p, i) => `
                <div class="db-rec-item" data-db-rec-index="${i}">
                    <div class="chk">✓</div>
                    ${p.image_url ? `<img src="${escHtmlDb(p.image_url)}" class="db-rec-img" onerror="this.style.display='none'">` : '<div class="db-rec-img" style="display:flex;align-items:center;justify-content:center;color:#64748b"><i class="fas fa-image fa-2x"></i></div>'}
                    <div class="db-rec-name">${escHtmlDb(p.product_name || p.name || '-')}</div>
                    <div class="db-rec-brand">🏪 ${escHtmlDb(p.shop_name || p.brand_display_name || '-')}</div>
                </div>`
            ).join('');

            // Pasang event listener — hindari bubbling seperti fix di monitoring.php
            grid.querySelectorAll('.db-rec-item').forEach(card => {
                card.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const targetCard = this.closest('[data-db-rec-index]');
                    if (!targetCard) return;
                    const idx = parseInt(targetCard.dataset.dbRecIndex, 10);
                    const product = _dbRecMap[idx];
                    if (!product) return;
                    const key = String(idx);
                    const existingIdx = _dbSelProducts.findIndex(p => p._recIdx === key);
                    if (existingIdx >= 0) {
                        _dbSelProducts.splice(existingIdx, 1);
                        targetCard.classList.remove('sel');
                    } else {
                        _dbSelProducts.push({ ...product, _recIdx: key });
                        targetCard.classList.add('sel');
                    }
                    document.getElementById('dbRecCount').textContent = _dbSelProducts.length;
                });
            });
        });
    }

    window.dbCloseRecModal = function() {
        document.getElementById('dbRecOverlay').classList.remove('active');
    };

    // Toggle TAP Request ID input berdasarkan metode yang dipilih
    window.dbOnMethodChange = function(value) {
        document.getElementById('dbTapIdWrap').style.display = value === 'system' ? 'block' : 'none';
    };

    window.dbConfirmSampleDelivery = function() {
        if (_dbSelProducts.length === 0) {
            showToastGlobal('Pilih minimal 1 produk sample', 'error');
            return;
        }
        const deliveryMethod = document.querySelector('input[name="dbDeliveryMethod"]:checked')?.value || 'manual';
        const tapRequestId   = document.getElementById('dbTapRequestId').value.trim();

        if (deliveryMethod === 'system' && !tapRequestId) {
            showToastGlobal('TAP Request ID wajib diisi untuk pengiriman By System', 'error');
            return;
        }

        const products = _dbSelProducts.map(p => ({
            product_id:  p.product_id,
            product_name: p.product_name || p.name,
            brand_id:    p.brand_db_id,
            brand_name:  p.brand_display_name || p.shop_name,
            quantity:    1,
        }));
        fetch(BASE_URL + 'is/save_sample_delivery', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `creator_id=${_dbCreatorId}&products=${encodeURIComponent(JSON.stringify(products))}&delivery_method=${deliveryMethod}&tap_request_id=${encodeURIComponent(tapRequestId)}`
        })
        .then(r => r.json())
        .then(data => {
            dbCloseRecModal();
            if (!data.success) { showToastGlobal(data.message, 'error'); return; }
            showToastGlobal(data.message, 'success');
            setTimeout(() => location.reload(), 1800);
        })
        .catch(() => showToastGlobal('Gagal menyimpan', 'error'));
    };

    function escHtmlDb(str) {
        return String(str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // Close overlay on background click
    document.addEventListener('DOMContentLoaded', function() {
        ['dbWillingOverlay','dbRecOverlay'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.addEventListener('click', function(e) {
                if (e.target === this) this.classList.remove('active');
            });
        });
    });

})();
</script>
<script>
/* FastMoss Cookie Modal — global scope agar bisa dipanggil dari onclick attribute */
window.openCookieModal = function() {
    var inp = document.getElementById('fastmossCookieInput');
    if (inp) inp.value = '';
    var modal = document.getElementById('fastmossCookieModal');
    if (modal) {
        modal.style.display = 'flex';
        // Paksa reflow agar transisi CSS berjalan
        void modal.offsetWidth;
        modal.classList.add('active');
    }
};
window.closeCookieModal = function() {
    var modal = document.getElementById('fastmossCookieModal');
    if (modal) {
        modal.classList.remove('active');
        setTimeout(function() {
            if (!modal.classList.contains('active')) modal.style.display = 'none';
        }, 220);
    }
};
window.saveFastmossCookie = function() {
    var inp = document.getElementById('fastmossCookieInput');
    var input = inp ? inp.value : '';
    if (!input.trim()) {
        if (typeof showToastGlobal === 'function') showToastGlobal('Silakan paste cURL / cookie terlebih dahulu', 'error');
        return;
    }
    fetch(BASE_URL + 'is/update_fastmoss_cookie', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'cookie_data=' + encodeURIComponent(input)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success) {
            if (typeof showToastGlobal === 'function') showToastGlobal(data.message, 'error');
            return;
        }
        if (typeof showToastGlobal === 'function') showToastGlobal(data.message, 'success');
        window.closeCookieModal();
        setTimeout(function() {
            var modal = document.getElementById('task1DetailModal');
            if (modal && modal.style.display === 'flex') {
                var titleEl = document.getElementById('task1ModalTitle');
                var titleText = titleEl ? titleEl.innerText : '';
                var unameMatch = titleText.match(/@([a-zA-Z0-9_.]+)/);
                if (unameMatch && unameMatch[1]) {
                    var card = document.querySelector('.scouting-item-dashboard[data-creator-name="' + unameMatch[1] + '"]');
                    if (card) { card.click(); } else { location.reload(); }
                } else { location.reload(); }
            } else { location.reload(); }
        }, 1000);
    })
    .catch(function() {
        if (typeof showToastGlobal === 'function') showToastGlobal('Gagal memperbarui cookie', 'error');
    });
};

    // ============================================================
    // SCOUTING CREATOR DETAIL MODAL — Brand Collaboration & GMV
    // ============================================================
    (function() {
        'use strict';

        // ── Inject modal HTML sekali saat DOM siap ──────────────
        function _injectModal() {
            if (document.getElementById('scoutingDetailModal')) return;

            const html = `
            <!-- ===== MODAL: SCOUTING CREATOR DETAIL ===== -->
            <div id="scoutingDetailModal"
                 style="display:none;position:fixed;inset:0;z-index:9999;
                        background:rgba(0,0,0,0.72);backdrop-filter:blur(4px);
                        align-items:center;justify-content:center;padding:16px;">

                <div style="background:#0d1526;border:1px solid rgba(112,136,185,0.18);
                            border-radius:20px;width:100%;max-width:560px;max-height:90vh;
                            display:flex;flex-direction:column;overflow:hidden;
                            box-shadow:0 24px 80px rgba(0,0,0,0.6);">

                    <!-- Header -->
                    <div style="display:flex;align-items:center;justify-content:space-between;
                                padding:18px 20px 14px;border-bottom:1px solid rgba(112,136,185,0.1);
                                flex-shrink:0;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div id="sdModalAvatar"
                                 style="width:40px;height:40px;border-radius:50%;
                                        background:rgba(139,92,246,0.2);display:flex;
                                        align-items:center;justify-content:center;
                                        overflow:hidden;flex-shrink:0;">
                                <i class="fab fa-tiktok" style="color:#8b5cf6;"></i>
                            </div>
                            <div>
                                <div id="sdModalTitle"
                                     style="font-size:14px;font-weight:700;color:#f1f5f9;">
                                    @creator
                                </div>
                                <div id="sdModalSub"
                                     style="font-size:11px;color:rgba(148,163,184,0.8);margin-top:1px;">
                                </div>
                            </div>
                        </div>
                        <!-- Total GMV badge -->
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div id="sdModalTotalGmvWrap"
                                 style="text-align:right;background:rgba(16,185,129,0.1);
                                        border:1px solid rgba(16,185,129,0.2);border-radius:12px;
                                        padding:6px 14px;display:none;">
                                <div id="sdModalTotalGmv"
                                     style="font-size:15px;font-weight:700;color:#10b981;
                                            white-space:nowrap;"></div>
                                <div style="font-size:9px;color:rgba(148,163,184,0.7);
                                            margin-top:1px;">Total GMV</div>
                            </div>
                            <button onclick="closeScoutingDetailModal()"
                                    style="background:rgba(255,255,255,0.06);border:none;
                                           color:rgba(148,163,184,0.8);width:30px;height:30px;
                                           border-radius:50%;cursor:pointer;font-size:14px;
                                           display:flex;align-items:center;justify-content:center;">
                                ✕
                            </button>
                        </div>
                    </div>

                    <!-- Body (scrollable) -->
                    <div id="sdModalBody"
                         style="flex:1;overflow-y:auto;padding:16px 20px 20px;
                                scrollbar-width:thin;
                                scrollbar-color:rgba(139,92,246,0.3) transparent;">

                        <!-- Loading state -->
                        <div id="sdModalLoading"
                             style="text-align:center;padding:48px 0;color:rgba(148,163,184,0.6);">
                            <i class="fas fa-spinner fa-pulse fa-2x"
                               style="color:rgba(139,92,246,0.5);display:block;margin-bottom:12px;"></i>
                            Mengambil data dari FastMoss...
                        </div>

                        <!-- Brands list -->
                        <div id="sdModalBrandsList" style="display:none;">
                            <div style="font-size:11px;font-weight:600;color:rgba(148,163,184,0.6);
                                        text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px;">
                                <i class="fas fa-store" style="margin-right:4px;"></i>
                                <span id="sdModalBrandsCount">Brands Collaborated (0)</span>
                            </div>
                            <div id="sdModalBrandsItems"></div>
                        </div>

                        <!-- Error state -->
                        <div id="sdModalError"
                             style="display:none;text-align:center;padding:40px 0;
                                    color:rgba(239,68,68,0.7);">
                            <i class="fas fa-exclamation-circle fa-2x"
                               style="display:block;margin-bottom:10px;"></i>
                            <div id="sdModalErrorMsg" style="font-size:12px;"></div>
                        </div>

                    </div>

                    <!-- Footer -->
                    <div style="flex-shrink:0;padding:12px 20px;
                                border-top:1px solid rgba(112,136,185,0.1);text-align:center;">
                        <button onclick="closeScoutingDetailModal()"
                                style="padding:9px 28px;background:rgba(255,255,255,0.06);
                                       color:rgba(148,163,184,0.8);border:1px solid rgba(112,136,185,0.15);
                                       border-radius:12px;cursor:pointer;font-size:12px;font-weight:600;">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>`;

            document.body.insertAdjacentHTML('beforeend', html);
        }

        // ── Helper: format Rupiah ────────────────────────────────
        function _fmtRpDetail(num) {
            const n = parseFloat(num) || 0;
            if (n >= 1e9)  return 'Rp ' + (n / 1e9).toFixed(1).replace('.', ',')  + 'M';
            if (n >= 1e6)  return 'Rp ' + (n / 1e6).toFixed(1).replace('.', ',')  + 'jt';
            if (n >= 1e3)  return 'Rp ' + (n / 1e3).toFixed(0) + 'rb';
            return 'Rp ' + n.toLocaleString('id-ID');
        }
        function _fmtNumDetail(n) {
            const v = parseInt(n) || 0;
            if (v >= 1e6) return (v / 1e6).toFixed(1) + 'jt';
            if (v >= 1e3) return (v / 1e3).toFixed(1) + 'rb';
            return v.toLocaleString('id-ID');
        }

        // ── Render satu baris brand ──────────────────────────────
        function _renderBrandRow(b, totalGmv, index) {
            const pct     = totalGmv > 0 ? Math.round((b.gmv / totalGmv) * 100) : 0;
            const logo    = b.shop_logo
                ? `<img src="${b.shop_logo}" alt=""
                         style="width:32px;height:32px;border-radius:8px;object-fit:cover;flex-shrink:0;"
                         onerror="this.outerHTML='<div style=\'width:32px;height:32px;border-radius:8px;background:rgba(74,222,128,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;\'><i class=\'fas fa-store\' style=\'color:#4ade80;font-size:13px;\'></i></div>'">`
                : `<div style="width:32px;height:32px;border-radius:8px;
                               background:rgba(74,222,128,0.12);display:flex;
                               align-items:center;justify-content:center;flex-shrink:0;">
                       <i class="fas fa-store" style="color:#4ade80;font-size:13px;"></i>
                   </div>`;

            const isLocal = b._source === 'local';
            const localBadge = isLocal
                ? `<span style="font-size:9px;background:rgba(251,191,36,0.1);color:#fbbf24;
                                padding:1px 5px;border-radius:6px;border:1px solid rgba(251,191,36,0.2);">
                       data lokal
                   </span>`
                : '';

            return `
            <div style="display:flex;align-items:flex-start;gap:10px;padding:10px 12px;
                        background:rgba(255,255,255,0.03);border:1px solid rgba(112,136,185,0.1);
                        border-radius:12px;margin-bottom:8px;
                        border-left:3px solid rgba(74,222,128,0.5);">
                ${logo}
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:6px;margin-bottom:3px;">
                        <span style="font-size:12px;font-weight:700;color:#f1f5f9;
                                     overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            ${b.shop_name || 'Brand'}
                        </span>
                        ${localBadge}
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <span style="font-size:10px;color:rgba(148,163,184,0.7);">
                            <i class="fas fa-box" style="font-size:9px;margin-right:2px;"></i>
                            ${_fmtNumDetail(b.product_count)} produk
                        </span>
                        ${b.sales_count > 0 ? `
                        <span style="font-size:10px;color:rgba(148,163,184,0.7);">
                            <i class="fas fa-shopping-cart" style="font-size:9px;margin-right:2px;"></i>
                            ${_fmtNumDetail(b.sales_count)} terjual
                        </span>` : ''}
                    </div>
                    <!-- Progress bar GMV -->
                    <div style="margin-top:6px;">
                        <div style="background:rgba(255,255,255,0.05);border-radius:4px;height:4px;overflow:hidden;">
                            <div style="width:${pct}%;height:100%;
                                        background:linear-gradient(90deg,#10b981,#34d399);
                                        border-radius:4px;transition:width .5s ease;"></div>
                        </div>
                    </div>
                </div>
                <div style="text-align:right;flex-shrink:0;">
                    <div style="font-size:13px;font-weight:700;color:#4ade80;">
                        ${_fmtRpDetail(b.gmv)}
                    </div>
                    ${pct > 0 ? `<div style="font-size:9px;color:rgba(148,163,184,0.5);margin-top:1px;">${pct}%</div>` : ''}
                </div>
            </div>`;
        }

        // ── Buka modal & fetch data ──────────────────────────────
        window.openScoutingCreatorDetail = function(scoutingId, username) {
            _injectModal();

            const modal       = document.getElementById('scoutingDetailModal');
            const loadingEl   = document.getElementById('sdModalLoading');
            const brandsEl    = document.getElementById('sdModalBrandsList');
            const errorEl     = document.getElementById('sdModalError');
            const titleEl     = document.getElementById('sdModalTitle');
            const subEl       = document.getElementById('sdModalSub');
            const avatarEl    = document.getElementById('sdModalAvatar');
            const totalWrap   = document.getElementById('sdModalTotalGmvWrap');
            const totalEl     = document.getElementById('sdModalTotalGmv');
            const countEl     = document.getElementById('sdModalBrandsCount');
            const itemsEl     = document.getElementById('sdModalBrandsItems');
            const errorMsgEl  = document.getElementById('sdModalErrorMsg');

            // Reset state
            loadingEl.style.display  = 'block';
            brandsEl.style.display   = 'none';
            errorEl.style.display    = 'none';
            totalWrap.style.display  = 'none';
            titleEl.textContent      = '@' + username;
            subEl.textContent        = '';
            avatarEl.innerHTML       = '<i class="fab fa-tiktok" style="color:#8b5cf6;"></i>';
            itemsEl.innerHTML        = '';

            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';

            // Fetch
            const fd = new FormData();
            fd.append('scouting_id', scoutingId);
            fd.append('username',    username);

            fetch(BASE_URL + 'is/get_scouting_creator_detail', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(res => {
                    loadingEl.style.display = 'none';

                    if (!res.success) {
                        errorMsgEl.textContent  = res.message || 'Gagal mengambil data';
                        errorEl.style.display   = 'block';
                        return;
                    }

                    const c         = res.creator  || {};
                    const brands    = res.brands   || [];
                    const totalGmv  = parseFloat(res.total_gmv) || 0;

                    // Update header
                    titleEl.textContent = '@' + (c.username || username);

                    const subParts = [];
                    if (c.full_name && c.full_name !== c.username) subParts.push(c.full_name);
                    if (c.category)                                 subParts.push(c.category);
                    if (c.phone)                                    subParts.push('📞 ' + c.phone);
                    if (c.follower_count > 0)                       subParts.push(_fmtNumDetail(c.follower_count) + ' followers');
                    subEl.textContent = subParts.join('  ·  ');

                    if (c.avatar_url) {
                        avatarEl.innerHTML = `<img src="${c.avatar_url}" alt=""
                            style="width:40px;height:40px;border-radius:50%;object-fit:cover;"
                            onerror="this.outerHTML='<i class=\\'fab fa-tiktok\\' style=\\'color:#8b5cf6;\\'></i>'">`;
                    }

                    // Total GMV
                    if (totalGmv > 0) {
                        totalEl.textContent     = _fmtRpDetail(totalGmv);
                        totalWrap.style.display = 'block';
                    }

                    // Brands list
                    if (brands.length > 0) {
                        countEl.textContent = 'Brands Collaborated (' + brands.length + ')';
                        brands.forEach(b => {
                            itemsEl.insertAdjacentHTML('beforeend', _renderBrandRow(b, totalGmv, 0));
                        });
                        brandsEl.style.display = 'block';
                    } else {
                        // Tidak ada data brand
                        const noData = res.has_fastmoss === false
                            ? 'Creator belum ditemukan di FastMoss. Pastikan username sesuai dengan akun TikTok-nya.'
                            : 'Tidak ada data brand kolaborasi yang ditemukan untuk creator ini.';
                        itemsEl.innerHTML = `
                            <div style="text-align:center;padding:32px 0;color:rgba(148,163,184,0.5);">
                                <i class="fas fa-store-slash" style="font-size:28px;display:block;margin-bottom:8px;
                                   color:rgba(148,163,184,0.25);"></i>
                                <div style="font-size:12px;">${noData}</div>
                            </div>`;
                        brandsEl.style.display = 'block';
                    }
                })
                .catch(err => {
                    loadingEl.style.display = 'none';
                    errorMsgEl.textContent  = 'Gagal menghubungi server';
                    errorEl.style.display   = 'block';
                    console.error('[ScoutingDetail]', err);
                });
        };

        // ── Tutup modal ──────────────────────────────────────────
        window.closeScoutingDetailModal = function() {
            const modal = document.getElementById('scoutingDetailModal');
            if (modal) modal.style.display = 'none';
            document.body.style.overflow = '';
        };

        // Tutup saat klik backdrop
        document.addEventListener('click', function(e) {
            const modal = document.getElementById('scoutingDetailModal');
            if (modal && e.target === modal) {
                closeScoutingDetailModal();
            }
        });

        // Tutup saat tekan Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeScoutingDetailModal();
        });

    })();
</script>
