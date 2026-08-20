<!-- file: application/views/bd/dashboard.php -->
<?php
$hunting_items = $hunting_items ?? [];
$followup_items = $followup_items ?? [];
$setup_items = $setup_items ?? [];
$monitoring_items = $monitoring_items ?? [];
$orders = $orders ?? [];
$top_3_bd = $top_3_bd ?? [];
$inactive_brands = $inactive_brands ?? [];
$active_brands_list = $active_brands_list ?? [];
$leaderboard_brands = $leaderboard_brands ?? [];
$total_brands = $total_brands ?? 0;
$total_gmv = $total_gmv ?? 0;
$deal_bonus_amount = $deal_bonus_amount ?? 0;
$is_supervisor = $is_supervisor ?? false;
?>
<style>
    /* Dashboard styles */
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
    
    /* XP Bar */
    .xp-container-dashboard { 
        background: linear-gradient(135deg, var(--bg-card), var(--bg-elevated));
        border-radius: 60px; 
        padding: 8px 16px; 
        margin-bottom: 20px; 
        border: 1px solid var(--border);
    }
    
    .xp-header-dashboard { display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; }
    .xp-text-dashboard { font-size: 10px; color: var(--text-secondary); }
    .progress-bar-dashboard { height: 4px; background: var(--border); border-radius: 10px; overflow: hidden; }
    .progress-fill-dashboard { height: 100%; background: linear-gradient(90deg, var(--purple), var(--cyan)); border-radius: 10px; transition: width 0.5s ease; }
    
    /* Tabs */
    .tabs-bar-dashboard { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 10px; }
    .tabs-dashboard { display: flex; gap: 6px; flex-wrap: wrap; }
    .tab-btn-dashboard { background: transparent; border: none; padding: 6px 16px; font-size: 12px; font-weight: 600; color: var(--text-muted); cursor: pointer; border-radius: 40px; transition: var(--transition); }
    .tab-btn-dashboard.active { background: linear-gradient(135deg, var(--purple-glow), rgba(59, 130, 246, 0.1)); color: var(--purple); border: 1px solid rgba(139, 92, 246, 0.4); }
    .scout-btn-dashboard { background: linear-gradient(135deg, var(--purple-glow), rgba(6, 182, 212, 0.1)); border: 1px solid var(--purple); padding: 6px 16px; border-radius: 40px; color: var(--purple); font-weight: 600; cursor: pointer; font-size: 12px; transition: var(--transition); }
    .scout-btn-dashboard:hover { background: var(--purple); color: white; }
    
    .tab-content-dashboard { display: none; animation: fadeIn 0.3s ease; }
    .tab-content-dashboard.active { display: block; }
    
    /* Stages Container - Scroll Horizontal */
.stages-scroll-dashboard { 
    overflow-x: auto;
    overflow-y: visible;
    margin-bottom: 20px;
    padding-bottom: 12px;
    scrollbar-width: thin;
}

.stages-container-dashboard { 
    display: flex;
    flex-direction: row;
    gap: 20px;
    min-width: min-content;
    justify-content: flex-start;
}

/* 4 card dengan lebar yang konsisten */
.stage-card-dashboard { 
    flex: 0 0 280px; /* JANGAN pakai flex:1, pakai fixed width */
    width: 280px;
    min-width: 280px;
    max-width: 280px;
    border-radius: 20px;
    padding: 16px;
    transition: var(--transition);
    position: relative;
    overflow: hidden;
    border: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    height: 520px;
}

/* Untuk layar yang lebih besar, bisa lebih lebar */
@media (min-width: 1600px) {
    .stage-card-dashboard {
        flex: 0 0 320px;
        width: 320px;
        min-width: 320px;
        max-width: 320px;
    }
}

/* Untuk layar medium, scroll horizontal */
@media (max-width: 1300px) {
    .stages-container-dashboard {
        overflow-x: auto;
        padding-bottom: 8px;
    }
    .stage-card-dashboard {
        flex: 0 0 280px;
        width: 280px;
    }
}

/* Untuk mobile */
@media (max-width: 768px) {
    .stage-card-dashboard {
        flex: 0 0 260px;
        width: 260px;
    }
}
    
    /* Task 1: Ungu */
    .stage-card-dashboard[data-stage="1"] { background: linear-gradient(135deg, #1a1030, #13111f); border-top: 3px solid var(--purple); }
    /* Task 2: Biru */
    .stage-card-dashboard[data-stage="2"] { background: linear-gradient(135deg, #0f1a2e, #13111f); border-top: 3px solid var(--blue); }
    /* Task 3: Cyan */
    .stage-card-dashboard[data-stage="3"] { background: linear-gradient(135deg, #0a1a1f, #13111f); border-top: 3px solid var(--cyan); }
    /* Task 4: Hijau */
    .stage-card-dashboard[data-stage="4"] { background: linear-gradient(135deg, #0a1f15, #13111f); border-top: 3px solid var(--green); }
    
    .stage-card-dashboard.completed { opacity: 0.7; }
    
    .stage-title-dashboard { 
        font-weight: 700; 
        font-size: 14px; 
        margin-bottom: 14px; 
        padding-bottom: 8px; 
        border-bottom: 1px solid var(--border); 
        display: flex; 
        align-items: center; 
        gap: 10px; 
        flex-wrap: wrap; 
        justify-content: space-between; 
        color: var(--text-primary);
    }
    
    .stage-count-dashboard { background: var(--bg-elevated); padding: 2px 8px; border-radius: 40px; font-size: 10px; }
    
    /* Brand Items */
    .stage-item-dashboard { 
        background: var(--bg-elevated); 
        border-radius: 14px; 
        padding: 12px; 
        margin-bottom: 10px; 
        cursor: pointer; 
        transition: var(--transition); 
        border: 1px solid transparent;
    }
    .stage-item-dashboard:hover { border-color: var(--purple); transform: translateX(3px); }
    .stage-item-dashboard strong { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; color: var(--text-primary); font-size: 13px; }
    .brand-details-dashboard { display: flex; flex-wrap: wrap; gap: 10px; font-size: 10px; color: var(--text-secondary); margin-bottom: 6px; }
    .badge-dashboard { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 20px; font-size: 9px; font-weight: 600; }
    .badge-pending { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
    .badge-negotiating { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
    .badge-deal { background: rgba(139, 92, 246, 0.15); color: #8b5cf6; }
    .badge-active { background: rgba(16, 185, 129, 0.15); color: #10b981; }
    
    .task-btn-dashboard { 
        margin-top: 14px; 
        width: 100%; 
        padding: 8px; 
        border-radius: 40px; 
        border: none; 
        background: var(--bg-elevated);
        color: var(--text-secondary); 
        font-weight: 600; 
        cursor: pointer; 
        transition: var(--transition); 
        font-size: 12px;
    }
    .task-btn-dashboard:hover:not(:disabled) { background: linear-gradient(135deg, var(--purple), var(--blue)); color: white; }
    .task-btn-dashboard:disabled { opacity: 0.5; cursor: not-allowed; }
    
    /* Modal */
    .modal-overlay-dashboard { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; z-index: 2000; visibility: hidden; opacity: 0; transition: 0.2s; }
    .modal-overlay-dashboard.active { visibility: visible; opacity: 1; }
    .modal-glass-dashboard {
        background: #111827;
        border-radius: 28px;
        width: 95%;
        max-width: 550px;
        padding: 24px;
        border: 1px solid #4ade80;
        max-height: 85vh;
        overflow-y: auto;
        color: #e2f0e8;
    }
    .modal-header-dashboard { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #2a3346; padding-bottom: 10px; }
    .modal-header-dashboard h3 { color: #e2f0e8; font-size: 18px; }
    .modal-close-dashboard { font-size: 26px; cursor: pointer; color: #9aaebe; }
    .modal-body label {
        color: #bdf2c0;
        font-weight: 500;
        display: block;
        margin-top: 14px;
        margin-bottom: 5px;
        font-size: 13px;
    }
    .modal-body input, .modal-body select, .modal-body textarea {
        width: 100%;
        padding: 10px 12px;
        background: #0f1420;
        border: 1px solid #2a3346;
        border-radius: 14px;
        color: #e2f0e8;
        font-size: 13px;
    }
    .modal-body input:focus, .modal-body select:focus {
        outline: none;
        border-color: #4ade80;
    }
    .modal-body button {
        background: #4ade80;
        color: #0a0e17;
        border: none;
        padding: 10px 18px;
        border-radius: 40px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s;
        font-size: 13px;
    }
    .modal-body button:hover { background: #22c55e; }
    .flex-buttons { display: flex; gap: 10px; margin-top: 16px; }
    .flex-buttons button { flex: 1; }
    
    .product-item { background: #0f1420; border-radius: 12px; padding: 10px; margin-top: 8px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #2a3346; }
    .product-info { flex: 1; }
    .product-name { color: #ffffff; font-size: 12px; font-weight: 500; }
    .product-price { color: #4ade80; font-size: 11px; margin-top: 3px; }
    
    /* Recent Orders */
    .recent-section-dashboard { background: var(--bg-card); border-radius: 20px; padding: 16px; border: 1px solid var(--border); overflow-x: auto; margin-top: 20px; }
    .recent-table-dashboard { width: 100%; min-width: 550px; border-collapse: collapse; }
    .recent-table-dashboard th, .recent-table-dashboard td { padding: 8px 6px; text-align: left; border-bottom: 1px solid var(--border); font-size: 11px; color: var(--text-secondary); }
    .recent-table-dashboard th { color: var(--purple); font-weight: 600; }
    .pagination-dashboard { display: flex; gap: 8px; justify-content: center; margin-top: 12px; }
    .pagination-dashboard button { background: var(--bg-elevated); border: 1px solid var(--border); color: var(--text-secondary); padding: 5px 14px; border-radius: 30px; cursor: pointer; font-size: 11px; }
    .pagination-dashboard button:hover:not(:disabled) { background: var(--purple); color: white; }
    .pagination-dashboard button:disabled { opacity: 0.5; cursor: not-allowed; }
    
    /* Brands Grid */
    .brands-grid-dashboard { display: flex; flex-direction: column; gap: 16px; }
    .brand-card-dashboard { background: var(--bg-card); border-radius: 20px; padding: 16px; border: 1px solid var(--border); }
    .brand-item-row-dashboard { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--border); flex-wrap: wrap; gap: 6px; }
    
    /* Sidebar */
    .sidebar-dashboard { background: var(--bg-card); width: 350px; max-width: 85vw; height: 100%; position: fixed; right: 0; top: 0; padding: 20px; overflow-y: auto; transform: translateX(100%); transition: transform 0.3s ease; border-left: 1px solid var(--purple); z-index: 2100; }
    .sidebar-dashboard.active { transform: translateX(0); }
    .brand-card-popup-dashboard { background: var(--bg-elevated); border-radius: 14px; padding: 12px; margin-bottom: 12px; border: 1px solid var(--border-light); }
    
    /* Mobile Bottom Nav */
    .mobile-bottom-nav-dashboard { display: none; position: fixed; bottom: 0; left: 0; right: 0; background: var(--bg-card); border-top: 1px solid var(--border); padding: 6px 12px; justify-content: space-around; z-index: 100; }
    .mobile-nav-item-dashboard { display: flex; flex-direction: column; align-items: center; gap: 2px; color: var(--text-muted); text-decoration: none; font-size: 9px; padding: 6px; border-radius: 40px; }
    .mobile-nav-item-dashboard i { font-size: 16px; }
    .mobile-nav-item-dashboard.active { color: var(--purple); background: var(--purple-glow); }
    
    .mt-3 { margin-top: 8px; }
    .text-center { text-align: center; }
    .text-success { color: #10b981; }
    .text-warning { color: #f59e0b; }
    
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
    
    @media (min-width: 769px) {
        .desktop-menu { display: flex; }
    }
    
    .auto-badge {
    background: #4ade80;
    color: #0a0e17;
    font-size: 8px;
    padding: 2px 6px;
    border-radius: 10px;
    margin-left: 6px;
    white-space: nowrap;
}

.text-success {
    color: #4ade80;
}

.text-warning {
    color: #f59e0b;
}
/* Styling untuk input komisi di modal */
input[type="number"] {
    transition: all 0.2s ease;
}

input[type="number"]:focus {
    border-color: #4ade80;
    outline: none;
    box-shadow: 0 0 5px rgba(74, 222, 128, 0.3);
}

/* Label styling */
label {
    font-size: 12px;
    font-weight: 500;
    margin-bottom: 6px;
    display: block;
}
.shop-option:hover {
    background: #1a1f2e !important;
    border-color: #4ade80 !important;
    transform: translateX(4px);
}
/* Leaderboard Table Styles */
.leaderboard-container-dashboard {
    animation: fadeIn 0.3s ease;
}

.leaderboard-table-dashboard th {
    color: var(--purple);
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.leaderboard-table-dashboard td {
    font-size: 12px;
}

.leaderboard-table-dashboard tbody tr {
    transition: background 0.2s ease;
}

.leaderboard-table-dashboard tbody tr:hover {
    background: rgba(139, 92, 246, 0.05);
}

/* Responsive */
@media (max-width: 768px) {
    .leaderboard-table-dashboard th,
    .leaderboard-table-dashboard td {
        padding: 8px 4px;
        font-size: 10px;
    }
    
    .leaderboard-table-dashboard th:first-child,
    .leaderboard-table-dashboard td:first-child {
        width: 50px;
    }
    
    .leaderboard-table-dashboard th:nth-child(3),
    .leaderboard-table-dashboard td:nth-child(3),
    .leaderboard-table-dashboard th:nth-child(5),
    .leaderboard-table-dashboard td:nth-child(5),
    .leaderboard-table-dashboard th:nth-child(6),
    .leaderboard-table-dashboard td:nth-child(6) {
        display: none;
    }
}
/* Animasi scrolling teks dari kanan ke kiri */
@keyframes scrollBDText {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-50%);
    }
}

.bd-scroll-wrapper {
    mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent);
    -webkit-mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent);
}

.bd-scroll-wrapper:hover .bd-scroll-content {
    animation-play-state: paused;
}

#globalToast {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #10b981;
    color: white;
    padding: 12px 20px;
    border-radius: 12px;
    font-size: 13px;
    z-index: 9999;
    animation: slideIn 0.3s ease;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@keyframes slideOut {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}

/* Toast di dalam modal */
.modal-toast {
    position: sticky;
    top: 10px;
    background: #10b981;
    color: white;
    padding: 12px 16px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 16px;
    animation: slideInDown 0.3s ease;
    z-index: 100;
    backdrop-filter: blur(4px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    display: flex;
    align-items: center;
    gap: 10px;
}

.modal-toast.error {
    background: #ef4444;
}

.modal-toast.warning {
    background: #f59e0b;
}

.modal-toast.info {
    background: #3b82f6;
}

.modal-toast i {
    font-size: 16px;
}

@keyframes slideInDown {
    from {
        transform: translateY(-20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes slideOutUp {
    from {
        transform: translateY(0);
        opacity: 1;
    }
    to {
        transform: translateY(-20px);
        opacity: 0;
    }
}


/* ===== Dashboard BA redesign override - header/footer untouched ===== */
.dashboard{
    padding: 24px 28px 28px;
    max-width: 1920px;
    margin: 0 auto;
}
.desktop-menu{
    display:flex;
    align-items:center;
    gap:8px;
    padding: 0 28px;
    height: 52px;
    border-bottom: 1px solid rgba(112,136,185,.16);
    background: rgba(3,9,20,.28);
    overflow-x:auto;
    scrollbar-width:none;
}
.desktop-menu::-webkit-scrollbar{display:none;}
.desktop-menu a{
    display:inline-flex;
    align-items:center;
    gap:8px;
    height:36px;
    padding:0 16px;
    border-radius:10px;
    color:var(--text-muted, #8e9bb6);
    font-size:13px;
    font-weight:800;
    border:1px solid transparent;
    white-space:nowrap;
}
.desktop-menu a:hover,
.desktop-menu a.active{
    background:linear-gradient(135deg, rgba(124,60,255,.28), rgba(124,60,255,.12));
    border-color:rgba(124,60,255,.24);
    color:#fff;
    box-shadow:none;
}
.mobile-menu-bar{display:none;}
.dashboard-header{
    display:grid;
    grid-template-columns:minmax(260px, .9fr) minmax(720px, 1.9fr);
    gap:24px;
    align-items:center;
    padding: 18px 0 22px;
    margin:0;
    border:0;
    border-radius:0;
    background:transparent;
}
.dashboard-title h1{
    font-size:28px;
    line-height:1.15;
    letter-spacing:-.04em;
    background:none;
    color:var(--text,#f7fbff);
}
.dashboard-title .sub{
    margin-top:10px;
    font-size:14px;
    color:var(--muted-2,#b7c1d6);
}
.stat-cards-dashboard{
    display:grid;
    grid-template-columns:repeat(3, minmax(190px,1fr));
    gap:14px;
    background:transparent;
    border:0;
    border-radius:0;
    padding:0;
}
.stat-item-dashboard{
    position:relative;
    overflow:hidden;
    min-height:86px;
    padding:18px 18px 18px 76px;
    text-align:left;
    border-radius:16px;
    border:1px solid rgba(112,136,185,.20);
    background:linear-gradient(160deg, rgba(25,31,58,.78), rgba(7,15,31,.78));
    box-shadow:inset 0 1px 0 rgba(255,255,255,.04);
}
.stat-item-dashboard:before{
    content:"";
    position:absolute;
    left:18px;
    top:50%;
    width:42px;
    height:42px;
    transform:translateY(-50%);
    border-radius:50%;
    background:linear-gradient(135deg, var(--purple,#7c3cff), rgba(16,223,240,.72));
    box-shadow:0 0 26px rgba(124,60,255,.25);
}
.stat-item-dashboard:nth-child(2):before{background:linear-gradient(135deg,#4556ff,#7c3cff);}
.stat-item-dashboard:nth-child(3):before{background:linear-gradient(135deg,#10dff0,#39f08a);}
.stat-label-dashboard{
    font-size:12px;
    color:var(--muted-2,#b7c1d6);
    font-weight:700;
}
.stat-value-dashboard{
    margin-top:6px;
    font-size:24px;
    line-height:1;
    color:#fff;
    background:none;
    -webkit-background-clip:initial;
    background-clip:initial;
}
.tabs-bar-dashboard{
    display:grid;
    grid-template-columns: minmax(360px, 470px) minmax(360px, 1fr) auto;
    gap:16px;
    align-items:center;
    margin:0 0 22px;
    padding:12px 18px;
    border:1px solid rgba(112,136,185,.18);
    border-radius:18px;
    background:linear-gradient(160deg, rgba(9,17,34,.72), rgba(4,10,22,.86));
}
.tabs-dashboard{
    display:flex;
    gap:8px;
    padding:0;
    border:1px solid rgba(112,136,185,.14);
    border-radius:12px;
    background:rgba(4,10,22,.38);
    overflow:hidden;
}
.tab-btn-dashboard{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    height:42px;
    padding:0 22px;
    border-radius:10px;
    font-size:13px;
    color:var(--muted-2,#b7c1d6);
}
.tab-btn-dashboard.active{
    color:#fff;
    background:linear-gradient(135deg,#6226d8,#7c3cff);
    border:0;
}
.bd-leaderboard-mini{
    height:42px !important;
    width:100% !important;
    min-width:0 !important;
    flex-shrink:1 !important;
    padding:0 12px !important;
    gap:14px !important;
    border-radius:12px !important;
    border:1px solid rgba(112,136,185,.16) !important;
    background:rgba(10,18,36,.70) !important;
    overflow:hidden !important;
}
.bd-leaderboard-mini > div:first-child{
    min-width:max-content;
    padding-right:12px;
    border-right:1px solid rgba(112,136,185,.16);
}
.bd-leaderboard-mini > div:first-child span:nth-child(2){
    color:#fff !important;
    font-size:12px !important;
    font-weight:900 !important;
}
.bd-leaderboard-mini > div:first-child span:nth-child(3){
    color:var(--muted-2,#b7c1d6) !important;
    font-size:11px !important;
}
.bd-scroll-wrapper{width:100%; min-width:0; overflow:hidden;}
.bd-scroll-content{width:max-content; gap:20px !important; animation:scrollBDText 22s linear infinite !important;}
.bd-scroll-content > div{
    background:transparent !important;
    padding:0 4px !important;
    border-radius:0 !important;
}
.bd-scroll-content span{font-size:11px !important;}
.scout-btn-dashboard{
    height:42px;
    min-width:178px;
    padding:0 20px;
    border-radius:999px;
    font-size:13px;
    color:#d18aff;
    border:1px solid rgba(209,138,255,.86);
    background:rgba(124,60,255,.08);
}
.scout-btn-dashboard:hover{background:#7c3cff;color:#fff;}
.stages-scroll-dashboard{
    padding:18px;
    margin-bottom:18px;
    border:1px solid rgba(112,136,185,.18);
    border-radius:18px;
    background:linear-gradient(160deg, rgba(9,17,34,.72), rgba(4,10,22,.86));
    overflow-x:auto;
}
.stages-container-dashboard{
    display:grid;
    grid-template-columns:repeat(3, minmax(320px,1fr));
    gap:20px;
    width:100%;
    justify-content:stretch;
}
.stage-card-dashboard{
    width:auto !important;
    height:500px !important;
    padding:18px !important;
    border-radius:18px;
    border:1px solid rgba(112,136,185,.18);
    background:linear-gradient(160deg, rgba(15,23,42,.90), rgba(7,15,31,.86));
    box-shadow:inset 0 1px 0 rgba(255,255,255,.035);
}
.stage-card-dashboard[data-stage="1"]{border-top:2px solid var(--purple,#7c3cff); background:linear-gradient(160deg, rgba(31,20,55,.86), rgba(7,15,31,.88));}
.stage-card-dashboard[data-stage="3"]{border-top:2px solid #3b82f6; background:linear-gradient(160deg, rgba(13,30,62,.86), rgba(7,15,31,.88));}
.stage-card-dashboard[data-stage="4"]{border-top:2px solid #10b981; background:linear-gradient(160deg, rgba(9,46,34,.82), rgba(7,15,31,.88));}
.stage-title-dashboard{
    border-bottom:0;
    margin-bottom:12px;
    padding-bottom:0;
    font-size:16px;
    letter-spacing:-.02em;
}
.stage-count-dashboard{
    padding:5px 10px;
    border:1px solid rgba(255,255,255,.10);
    background:rgba(3,9,20,.38);
    font-size:12px;
    color:#fff;
}
.stage-card-dashboard input[type="text"]{
    height:38px;
    padding:0 14px !important;
    color:#fff;
    background:rgba(255,255,255,.055) !important;
    border:1px solid rgba(255,255,255,.08) !important;
    border-radius:10px !important;
    outline:none;
}
.stage-card-dashboard input[type="text"]::placeholder{color:rgba(183,193,214,.72);}
.stage-item-dashboard{
    display:block;
    padding:13px 14px;
    margin-bottom:8px;
    border-radius:12px;
    border:1px solid rgba(112,136,185,.14);
    background:rgba(9,17,34,.56);
}
.stage-item-dashboard:hover{
    transform:none;
    border-color:rgba(124,60,255,.42);
    background:rgba(16,29,55,.74);
}
.stage-item-dashboard strong{
    font-size:13px;
    margin-bottom:7px;
}
.brand-details-dashboard{
    gap:9px 12px;
    font-size:10px;
    color:var(--muted-2,#b7c1d6);
}
.badge-dashboard{
    padding:4px 9px;
    font-size:9px;
    font-weight:900;
    text-transform:uppercase;
}
.progress-bar-dashboard.mt-3{display:none;}
.task-btn-dashboard{
    height:42px;
    margin:10px 0 0 !important;
    border-radius:12px;
    border:1px solid rgba(124,60,255,.26);
    background:rgba(124,60,255,.10);
    color:#c084fc;
}
.task-btn-dashboard:disabled{
    border-color:rgba(112,136,185,.12);
    background:rgba(255,255,255,.035);
    color:var(--muted,#8e9bb6);
}
#huntingItemsContainerDashboard,
#setupItemsContainerDashboard,
#monitoringItemsContainerDashboard{
    scrollbar-width:thin;
    scrollbar-color:rgba(255,255,255,.25) transparent;
}
#huntingItemsContainerDashboard::-webkit-scrollbar,
#setupItemsContainerDashboard::-webkit-scrollbar,
#monitoringItemsContainerDashboard::-webkit-scrollbar{width:6px;}
#huntingItemsContainerDashboard::-webkit-scrollbar-thumb,
#setupItemsContainerDashboard::-webkit-scrollbar-thumb,
#monitoringItemsContainerDashboard::-webkit-scrollbar-thumb{background:rgba(255,255,255,.22);border-radius:999px;}
.recent-section-dashboard{
    margin-top:0;
    padding:20px 22px;
    border-radius:18px;
    border:1px solid rgba(112,136,185,.18);
    background:linear-gradient(160deg, rgba(9,17,34,.74), rgba(4,10,22,.88));
}
.recent-table-dashboard{
    min-width:760px;
    overflow:hidden;
    border:1px solid rgba(112,136,185,.14);
    border-radius:14px;
    border-collapse:separate;
    border-spacing:0;
}
.recent-table-dashboard th{
    padding:12px 16px;
    background:rgba(124,60,255,.10);
    color:#c084fc;
    font-size:12px;
}
.recent-table-dashboard td{
    padding:12px 16px;
    color:var(--muted-2,#b7c1d6);
    font-size:12px;
    border-bottom:1px solid rgba(112,136,185,.10);
}
.pagination-dashboard button{
    border-radius:10px;
    height:32px;
}
@keyframes scrollBDText{
    0%{transform:translateX(0);}
    100%{transform:translateX(-50%);}
}
@media(max-width:1200px){
    .dashboard-header{grid-template-columns:1fr;}
    .tabs-bar-dashboard{grid-template-columns:1fr;}
    .scout-btn-dashboard{width:100%;}
    .stages-container-dashboard{display:flex;}
    .stage-card-dashboard{width:360px !important;}
}
@media(max-width:767px){
    .dashboard{padding:16px 14px 76px;}
    .desktop-menu{display:none;}
    .mobile-menu-bar{display:flex; overflow-x:auto; gap:8px; padding:10px 14px;}
    .dashboard-header{padding-top:14px;}
    .stat-cards-dashboard{grid-template-columns:1fr;}
    .tabs-dashboard{width:100%; overflow-x:auto;}
    .tab-btn-dashboard{flex:1; min-width:max-content; padding:0 14px;}
    .stage-card-dashboard{width:300px !important;}
}


/* ===== FINAL VIEW POLISH: 4-task BA dashboard, header/footer untouched ===== */
:root{
    --db-bg-panel: rgba(8,16,34,.72);
    --db-bg-panel-2: rgba(13,24,48,.82);
    --db-border: rgba(112,136,185,.18);
    --db-border-strong: rgba(124,60,255,.34);
    --db-text: var(--text,#f7fbff);
    --db-muted: var(--muted,#8e9bb6);
    --db-muted-2: var(--muted-2,#b7c1d6);
    --db-purple: var(--purple,#7c3cff);
    --db-blue:#3b82f6;
    --db-cyan:#10dff0;
    --db-green:#10b981;
    --db-orange:#f59e0b;
}
.dashboard{padding:24px 28px 32px;}
.dashboard-header{
    grid-template-columns:minmax(280px,.72fr) minmax(760px,1.8fr) !important;
    gap:28px !important;
    padding:24px 8px 22px !important;
}
.dashboard-title h1{font-size:30px !important;font-weight:900 !important;letter-spacing:-.05em !important;}
.dashboard-title .sub{font-size:14px !important;color:var(--db-muted-2) !important;}
.stat-cards-dashboard{grid-template-columns:repeat(4,minmax(180px,1fr)) !important;gap:14px !important;}
.stat-item-dashboard{
    min-height:104px !important;
    padding:18px 18px 16px 84px !important;
    border-radius:18px !important;
    border:1px solid var(--db-border) !important;
    background:linear-gradient(160deg,rgba(20,27,54,.84),rgba(7,14,30,.88)) !important;
}
.stat-item-dashboard:before{width:50px !important;height:50px !important;left:18px !important;}
.stat-gmv:before{content:"\f201";font-family:"Font Awesome 6 Free";font-weight:900;display:grid;place-items:center;color:#fff;font-size:19px;background:linear-gradient(135deg,#7c3cff,#c02cff) !important;}
.stat-bonus:before{content:"\f06b";font-family:"Font Awesome 6 Free";font-weight:900;display:grid;place-items:center;color:#fff;font-size:19px;background:linear-gradient(135deg,#4556ff,#7c3cff) !important;}
.stat-brand:before{content:"\f54e";font-family:"Font Awesome 6 Free";font-weight:900;display:grid;place-items:center;color:#fff;font-size:18px;background:linear-gradient(135deg,#10dff0,#3b82f6) !important;}
.stat-active:before{content:"\f0c0";font-family:"Font Awesome 6 Free";font-weight:900;display:grid;place-items:center;color:#fff;font-size:18px;background:linear-gradient(135deg,#10b981,#39f08a) !important;}
.stat-label-dashboard{font-size:12px !important;font-weight:800 !important;color:var(--db-muted-2) !important;}
.stat-value-dashboard{font-size:24px !important;font-weight:900 !important;margin-top:7px !important;color:#fff !important;white-space:nowrap;}
.stat-caption-dashboard{margin-top:7px;font-size:11px;color:var(--db-muted);font-weight:700;}
.stat-caption-dashboard i{color:var(--db-green);font-size:7px;margin-right:5px;}
.tabs-bar-dashboard{
    grid-template-columns:minmax(330px,460px) minmax(420px,1fr) auto !important;
    gap:14px !important;
    padding:14px 18px !important;
    margin-bottom:18px !important;
}
.bd-leaderboard-mini{height:44px !important;}
.scout-btn-dashboard{height:44px !important;}
.stages-scroll-dashboard{padding:16px !important;border-radius:20px !important;}
.stages-container-dashboard{
    display:grid !important;
    grid-template-columns:repeat(4,minmax(250px,1fr)) !important;
    gap:16px !important;
    min-width:1180px !important;
}
.stage-card-dashboard{
    width:auto !important;min-width:0 !important;max-width:none !important;flex:none !important;
    height:520px !important;padding:16px !important;border-radius:18px !important;
    overflow:hidden !important;display:flex !important;flex-direction:column !important;
    background:linear-gradient(160deg,rgba(13,23,46,.90),rgba(6,12,25,.92)) !important;
    border:1px solid var(--db-border) !important;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.04) !important;
}
.stage-card-dashboard[data-stage="1"]{border-top:2px solid var(--db-purple) !important;background:linear-gradient(160deg,rgba(31,20,55,.86),rgba(7,14,30,.92)) !important;}
.stage-card-dashboard[data-stage="2"]{border-top:2px solid var(--db-orange) !important;background:linear-gradient(160deg,rgba(47,31,13,.74),rgba(7,14,30,.92)) !important;}
.stage-card-dashboard[data-stage="3"]{border-top:2px solid var(--db-blue) !important;background:linear-gradient(160deg,rgba(13,30,62,.82),rgba(7,14,30,.92)) !important;}
.stage-card-dashboard[data-stage="4"]{border-top:2px solid var(--db-green) !important;background:linear-gradient(160deg,rgba(9,46,34,.78),rgba(7,14,30,.92)) !important;}
.stage-title-dashboard{font-size:15px !important;min-height:32px;margin-bottom:10px !important;gap:8px !important;flex-wrap:nowrap !important;}
.stage-title-dashboard span:first-child{display:flex;align-items:center;gap:8px;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.stage-title-dashboard i{font-size:15px;}
.stage-count-dashboard{font-size:12px !important;padding:5px 10px !important;}
.stage-search{flex:0 0 auto;margin:0 0 12px;}
.stage-card-dashboard .stage-search input,
.stage-card-dashboard input[type="text"]{
    width:100% !important;height:40px !important;padding:0 14px !important;border-radius:11px !important;
    color:#fff !important;background:rgba(255,255,255,.055) !important;border:1px solid rgba(255,255,255,.08) !important;
}
.stage-items-container,
#huntingItemsContainerDashboard,
#followupItemsContainerDashboard,
#setupItemsContainerDashboard,
#monitoringItemsContainerDashboard{
    flex:1 1 auto !important;min-height:0 !important;overflow-y:auto !important;padding:0 4px 0 0 !important;
    scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.22) transparent;
}
.stage-items-container::-webkit-scrollbar{width:5px}.stage-items-container::-webkit-scrollbar-thumb{background:rgba(255,255,255,.22);border-radius:999px;}
.stage-item-dashboard{padding:12px !important;margin-bottom:8px !important;border-radius:13px !important;}
.stage-item-dashboard strong{font-size:12px !important;line-height:1.25 !important;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.brand-details-dashboard{font-size:9.5px !important;line-height:1.4 !important;gap:5px 9px !important;}
.badge-dashboard{font-size:8.5px !important;padding:4px 8px !important;}
.task-btn-dashboard{flex:0 0 42px !important;height:42px !important;margin-top:10px !important;}
.recent-section-dashboard{border-radius:20px !important;padding:20px 22px !important;}
.recent-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;gap:12px;flex-wrap:wrap;}
.recent-header h3{font-size:18px;color:#fff;margin:0;font-weight:900;}
.recent-info{display:inline-flex;align-items:center;gap:4px;border:1px solid rgba(124,60,255,.24);background:rgba(124,60,255,.10);border-radius:12px;padding:9px 14px;color:#c084fc;font-weight:900;font-size:12px;}
.recent-table-wrapper{overflow-x:auto;}
.recent-table-dashboard{border-collapse:separate !important;border-spacing:0 !important;border-radius:14px;overflow:hidden;}
.mobile-menu-bar{display:none;}
@media(max-width:1500px){
    .dashboard-header{grid-template-columns:1fr !important;}
    .stat-cards-dashboard{grid-template-columns:repeat(4,minmax(160px,1fr)) !important;}
}
@media(max-width:1200px){
    .tabs-bar-dashboard{grid-template-columns:1fr !important;}
    .scout-btn-dashboard{width:100%;}
    .stages-container-dashboard{display:flex !important;min-width:min-content !important;}
    .stage-card-dashboard{flex:0 0 310px !important;width:310px !important;min-width:310px !important;}
}
@media(max-width:900px){.stat-cards-dashboard{grid-template-columns:repeat(2,minmax(0,1fr)) !important;}}
@media(max-width:767px){
    .dashboard{padding:16px 14px 76px !important;}
    .dashboard-header{padding:12px 0 18px !important;}
    .stat-cards-dashboard{grid-template-columns:1fr !important;}
    .tabs-bar-dashboard{padding:12px !important;}
    .stage-card-dashboard{flex:0 0 294px !important;width:294px !important;min-width:294px !important;}
}
.desktop-menu-with-stats{
    height:auto !important;
    min-height:76px;
    justify-content:space-between;
    gap:18px;
    padding:10px 28px !important;
}

.desktop-menu-links{
    display:flex;
    align-items:center;
    gap:8px;
    flex-wrap:wrap;
}

.menu-stats-dashboard{
    display:flex !important;
    grid-template-columns:none !important;
    gap:10px !important;
    margin-left:auto;
}

.menu-stats-dashboard .stat-item-dashboard{
    min-height:54px !important;
    width:190px;
    padding:9px 12px 9px 52px !important;
    border-radius:14px !important;
}

.menu-stats-dashboard .stat-item-dashboard:before{
    width:32px !important;
    height:32px !important;
    left:12px !important;
    font-size:13px !important;
}

.menu-stats-dashboard .stat-label-dashboard{
    font-size:10px !important;
}

.menu-stats-dashboard .stat-value-dashboard{
    font-size:17px !important;
    margin-top:3px !important;
}

.menu-stats-dashboard .stat-caption-dashboard{
    font-size:9px !important;
    margin-top:3px !important;
}

.dashboard-header{
    display:none !important;
}

@media(max-width:1300px){
    .desktop-menu-with-stats{
        align-items:flex-start;
        flex-direction:column;
    }

    .menu-stats-dashboard{
        width:100%;
        margin-left:0;
    }

    .menu-stats-dashboard .stat-item-dashboard{
        flex:1;
        width:auto;
    }
}

/* Fix checkbox di modal */
.modal-glass-dashboard input[type="checkbox"] {
    width: 18px !important;
    height: 18px !important;
    min-width: 18px !important;
    min-height: 18px !important;
    margin: 0 !important;
    padding: 0 !important;
    cursor: pointer !important;
    accent-color: #4ade80 !important;
}

/* Flex alignment untuk baris produk */
.product-item-dashboard > div,
.recommendation-item-dashboard > div {
    align-items: center !important;
}

/* Tombol Approve */
.btn-approve {
    background: #10b981 !important;
    color: white !important;
    border: none !important;
    padding: 8px 20px !important;
    border-radius: 40px !important;
    font-weight: 600 !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
}
.btn-approve:hover:not(:disabled) {
    background: #059669 !important;
    transform: translateY(-1px);
}
.btn-approve:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Tombol Link */
.btn-single-link, .btn-multi-link {
    background: #8b5cf6 !important;
    color: white !important;
    border: none !important;
    padding: 8px 16px !important;
    border-radius: 40px !important;
    font-weight: 600 !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
}
.btn-single-link:hover, .btn-multi-link:hover {
    background: #7c3aed !important;
}

/* Container rekomendasi produk */
.recommendations-container {
    max-height: 300px;
    overflow-y: auto;
    margin-top: 12px;
    border-top: 1px solid #2a3346;
    padding-top: 12px;
}

.recommendation-item {
    background: #1a1f2e;
    border-radius: 10px;
    padding: 10px;
    margin-bottom: 8px;
    border: 1px solid #8b5cf6;
    cursor: pointer;
    transition: all 0.2s ease;
}
.recommendation-item:hover {
    background: #2a2f3e;
    transform: translateX(4px);
}
.recommendation-item.selected {
    border-color: #4ade80;
    background: rgba(74,222,128,0.1);
}
/* Batch Brand Modal */
#batchBrandModal .modal-glass-dashboard {
    max-width: 800px !important;
    width: 95% !important;
}

#batchBrandTableBody tr {
    transition: background 0.2s ease;
}

#batchBrandTableBody tr:hover {
    background: rgba(139,92,246,0.05);
}

.batch-row-input {
    background: transparent !important;
    border: none !important;
    color: #e2f0e8 !important;
    width: 100%;
    padding: 8px 4px;
}

.batch-row-input:focus {
    outline: none;
    border-bottom: 1px solid #4ade80 !important;
}

.batch-row-input::placeholder {
    color: #4a5568;
}

.batch-remove-btn {
    background: transparent;
    border: none;
    color: #ef4444;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 6px;
    transition: 0.2s;
}

.batch-remove-btn:hover {
    background: rgba(239,68,68,0.15);
}

.batch-row-success {
    background: rgba(74,222,128,0.1) !important;
}

.batch-row-error {
    background: rgba(239,68,68,0.1) !important;
}

/* Tombol Edit di Task 1 */
.edit-brand-btn {
    background: transparent;
    border: none;
    color: #fbbf24;
    cursor: pointer;
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 4px;
    transition: all 0.2s ease;
    z-index: 10 !important;
    position: relative !important;
    pointer-events: auto !important;
}

.edit-brand-btn:hover {
    background: rgba(251, 191, 36, 0.2);
    transform: scale(1.05);
}

.edit-brand-btn:active {
    transform: scale(0.95);
}

/* Modal Active Brands */
#modalActiveBrands .modal-glass-dashboard {
    max-width: 900px !important;
    width: 95% !important;
}

#modalActiveBrands .brand-item-active {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    border-bottom: 1px solid rgba(112, 136, 185, 0.1);
    transition: background 0.2s ease;
    cursor: default;
}

#modalActiveBrands .brand-item-active:hover {
    background: rgba(139, 92, 246, 0.05);
}

#modalActiveBrands .brand-item-active .brand-name {
    font-weight: 600;
    color: #e2f0e8;
    font-size: 13px;
}

#modalActiveBrands .brand-item-active .brand-info {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    font-size: 11px;
    color: #9aaebe;
}

#modalActiveBrands .brand-item-active .brand-info .gmv {
    color: #4ade80;
    font-weight: 600;
}

#modalActiveBrands .brand-item-active .brand-info .products {
    color: #fbbf24;
}

#modalActiveBrands .brand-item-active .brand-info .status-badge {
    padding: 2px 10px;
    border-radius: 20px;
    font-size: 9px;
    font-weight: 600;
}

#modalActiveBrands .brand-item-active .brand-info .status-badge.active {
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
}

#modalActiveBrands .brand-item-active .brand-info .status-badge.pending {
    background: rgba(245, 158, 11, 0.15);
    color: #f59e0b;
}

#modalActiveBrands .empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6b7280;
}

#modalActiveBrands .empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    display: block;
    opacity: 0.5;
}

/* Tombol Reject */
.btn-reject {
    background: #ef4444 !important;
    color: white !important;
    border: none !important;
    padding: 8px 20px !important;
    border-radius: 40px !important;
    font-weight: 600 !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
}

.btn-reject:hover:not(:disabled) {
    background: #dc2626 !important;
    transform: translateY(-1px);
}

.btn-reject:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
<!-- Desktop Menu - hanya muncul di desktop -->
<div class="desktop-menu desktop-menu-with-stats">
    <div class="desktop-menu-links">
        <a href="<?= base_url('bd/dashboard') ?>" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="<?= base_url('bd/brands') ?>"><i class="fas fa-building"></i> Brands</a>
        <a href="<?= base_url('bd/campaigns') ?>"><i class="fas fa-bullhorn"></i> Campaigns</a>
        <a href="<?= base_url('link_management') ?>"><i class="fas fa-link"></i> Link Management</a>
        <a href="<?= base_url('bd/team_performance') ?>"><i class="fas fa-users"></i> Team Performance</a>
        <a href="<?= base_url('tts/status') ?>"><i class="fas fa-sync"></i> Sync API</a>
        <a href="<?= base_url('analytics/bd') ?>"><i class="fas fa-chart-line"></i> Analytics</a>
        
       
        <a href="<?= base_url('message_template/admin') ?>" class="menu-item">
            <i class="fas fa-envelope-open-text"></i>
            <span>Message Templates</span>
        </a>
       
    </div>

    <div class="stat-cards-dashboard menu-stats-dashboard">
        <div class="stat-item-dashboard stat-gmv">
            <div class="stat-label-dashboard">Total GMV</div>
            <div class="stat-value-dashboard">Rp <?= number_format($total_gmv, 0, ',', '.') ?></div>
            <div class="stat-caption-dashboard">hari ini</div>
        </div>

        <div class="stat-item-dashboard stat-brand">
            <div class="stat-label-dashboard">Total Brand</div>
            <div class="stat-value-dashboard"><?= number_format($total_brands, 0, ',', '.') ?></div>
            <div class="stat-caption-dashboard">Semua pipeline</div>
        </div>
<div class="stat-item-dashboard stat-active" style="cursor: pointer;" onclick="openActiveBrandsModal()">
    <div class="stat-label-dashboard">Brand Aktif</div>
    <div class="stat-value-dashboard"><?= number_format(count($monitoring_items), 0, ',', '.') ?></div>
    <div class="stat-caption-dashboard"><i class="fas fa-circle"></i> LIVE</div>
</div>
        
        
    </div>
</div>

<!-- Mobile Menu Bar (horizontal scroll) -->
<div class="mobile-menu-bar">
    <a href="<?= base_url('bd/dashboard') ?>" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="<?= base_url('bd/brands') ?>"><i class="fas fa-building"></i> Brands</a>
    <a href="<?= base_url('bd/campaigns') ?>"><i class="fas fa-bullhorn"></i> Campaigns</a>
    <a href="<?= base_url('tts/status') ?>"><i class="fas fa-sync"></i> Sync API</a>
</div>

<div class="dashboard">
    <!-- Dashboard Header -->
<div class="dashboard-header">
    <input type="hidden" id="isSupervisorHidden" value="<?= $is_supervisor ? 'true' : 'false' ?>">
</div>
    
    <!-- Tabs -->
    <div class="tabs-bar-dashboard">
        <div class="tabs-dashboard">
            <button class="tab-btn-dashboard active" data-tab="tabTaskDashboard"><i class="fas fa-clipboard-list"></i> Task</button>
            <button class="tab-btn-dashboard" data-tab="tabBrandStatusDashboard"><i class="fas fa-tags"></i> Status Brand</button>
            <button class="tab-btn-dashboard" data-tab="tabLeaderboardDashboard"><i class="fas fa-trophy"></i> Leaderboard</button>
        </div>
        <!-- �9�7 LEADERBOARD BD BERJALAN -->
<div class="bd-leaderboard-mini" style="display: flex; align-items: center; gap: 8px; background: rgba(139, 92, 246, 0.08); padding: 4px 12px; border-radius: 40px; border: 1px solid rgba(139, 92, 246, 0.2); flex-shrink: 0;">
    <div style="display: flex; align-items: center; gap: 4px; flex-shrink: 0;">
        <i class="fas fa-trophy" style="color: #fbbf24; font-size: 11px;"></i>
        <span style="font-size: 9px; font-weight: 600; color: var(--purple);">BD TOP 3</span>
        <span style="font-size: 8px; color: var(--text-muted);">(7 hari)</span>
    </div>
    
    <div class="bd-scroll-wrapper" style="overflow: hidden; flex: 1; position: relative; min-width: 0;">
        <div class="bd-scroll-content" style="display: flex; gap: 16px; animation: scrollBDText 15s linear infinite; white-space: nowrap;">
            <?php foreach ($top_3_bd as $index => $bd): ?>
    <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(139, 92, 246, 0.1); padding: 4px 12px; border-radius: 30px;">
        <?php if ($index == 0): ?>
            <i class="fas fa-crown" style="color: #fbbf24; font-size: 10px;"></i>
        <?php elseif ($index == 1): ?>
            <i class="fas fa-medal" style="color: #c0c0c0; font-size: 10px;"></i>
        <?php else: ?>
            <i class="fas fa-medal" style="color: #cd7f32; font-size: 10px;"></i>
        <?php endif; ?>
        <span style="font-size: 10px; font-weight: 500; color: var(--text-primary);"><?= htmlspecialchars($bd->full_name ?: $bd->username) ?></span>
        <span style="font-size: 9px; color: #4ade80; font-weight: 600;">Rp <?= number_format($bd->total_gmv_7d, 0, ',', '.') ?></span>
    </div>
    <?php endforeach; ?>
            <?php foreach ($top_3_bd as $index => $bd): ?>
    <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(139, 92, 246, 0.1); padding: 4px 12px; border-radius: 30px;">
        <?php if ($index == 0): ?>
            <i class="fas fa-crown" style="color: #fbbf24; font-size: 10px;"></i>
        <?php elseif ($index == 1): ?>
            <i class="fas fa-medal" style="color: #c0c0c0; font-size: 10px;"></i>
        <?php else: ?>
            <i class="fas fa-medal" style="color: #cd7f32; font-size: 10px;"></i>
        <?php endif; ?>
        <span style="font-size: 10px; font-weight: 500; color: var(--text-primary);"><?= htmlspecialchars($bd->full_name ?: $bd->username) ?></span>
        <span style="font-size: 9px; color: #4ade80; font-weight: 600;">Rp <?= number_format($bd->total_gmv_7d, 0, ',', '.') ?></span>
    </div>
    <?php endforeach; ?>
        </div>
    </div>
</div>
        <button class="scout-btn-dashboard" id="scoutBtnDashboard"><i class="fas fa-search"></i> Cari Brand Baru</button>
    </div>

<!-- ==================== TAB 1: TASK ==================== -->
<div id="tabTaskDashboard" class="tab-content-dashboard active">
    <div class="stages-scroll-dashboard">
        <div class="stages-container-dashboard">
            
            <!-- TASK 1: HUNTING -->
<!-- TASK 1: HUNTING -->
<div class="stage-card-dashboard" data-stage="1" style="display: flex; flex-direction: column; height: 500px;">
    <div class="stage-title-dashboard" style="flex-shrink: 0;">
        <span><i class="fas fa-search"></i> 1. HUNTING</span>
        <span class="stage-count-dashboard" id="huntingCountDashboard"><?= $total_hunting ?? 0 ?></span>
    </div>
    <div style="flex-shrink: 0; padding: 8px 12px;">
        <input type="text" id="searchHuntingDashboard" placeholder="Cari brand..." style="width: 100%; padding: 6px 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 12px; background: #1a1f2e; color: white;">
    </div>
    <div id="huntingItemsContainerDashboard" style="flex: 1; overflow-y: auto; padding: 0 8px;">
        <?php if (!empty($hunting_items)): ?>
            <?php foreach ($hunting_items as $item): ?>
            <div class="stage-item-dashboard brand-item-dashboard" data-brand-id="<?= $item->id ?>" data-brand-name="<?= htmlspecialchars($item->name) ?>" data-stage="1">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <strong><i class="fas fa-building"></i> <?= htmlspecialchars($item->name) ?></strong>
                    <!-- 🔥 TOMBOL EDIT DENGAN ONCLICK LANGSUNG -->
                    <button class="edit-brand-btn" 
                            data-brand-id="<?= $item->id ?>" 
                            data-brand-name="<?= htmlspecialchars($item->name) ?>" 
                            data-whatsapp="<?= htmlspecialchars($item->whatsapp_number ?? '') ?>" 
                            data-commission="<?= $item->proposed_commission ?? 0 ?>" 
                            data-category="<?= htmlspecialchars($item->category ?? '') ?>"
                            data-email="<?= htmlspecialchars($item->email ?? '') ?>"
                            onclick="event.stopPropagation(); handleEditBrandClick(this);"
                            style="background: transparent; border: none; color: #fbbf24; cursor: pointer; font-size: 12px; padding: 4px 8px; border-radius: 4px; z-index: 10; position: relative;">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>
                <div class="brand-details-dashboard">
                    <span><i class="fab fa-whatsapp"></i> <?= $item->whatsapp_number ?? '-' ?></span>
                    <span><i class="fas fa-user"></i> Input: <?= $item->input_by ?? ($item->bd_username ?? '-') ?></span>
                    <?php if (!empty($item->category)): ?>
                    <span><i class="fas fa-tag"></i> <?= htmlspecialchars($item->category) ?></span>
                    <?php endif; ?>
                </div>
                <div class="brand-details-dashboard" style="margin-top: 4px; font-size: 9px; color: #6b7280;">
                    <span><i class="fas fa-calendar-alt"></i> Input: <?= date('d/m/Y H:i', strtotime($item->created_at ?? $item->updated_at ?? 'now')) ?></span>
                    <?php if (!empty($item->proposed_commission)): ?>
                    <span><i class="fas fa-percent"></i> Komisi: <?= $item->proposed_commission ?>%</span>
                    <?php endif; ?>
                </div>
                <span class="badge-dashboard badge-pending"><i class="fas fa-clock"></i> PENDING</span>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="stage-item-dashboard">
                <strong><i class="fas fa-info-circle"></i> Belum ada brand</strong>
                <div class="brand-details-dashboard">Klik "Input Brand Baru" untuk memulai</div>
            </div>
        <?php endif; ?>
    </div>
    <button class="task-btn-dashboard" data-action="hunting" style="flex-shrink: 0; margin: 8px;">
        <i class="fas fa-plus-circle"></i> Input Brand Baru
    </button>
</div>

           
            <!-- TASK 2: FOLLOW UP -->
<!-- TASK 2: FOLLOW UP -->
<div class="stage-card-dashboard" data-stage="2" style="display: flex; flex-direction: column; height: 500px; border-top: 3px solid #f59e0b;">
    <div class="stage-title-dashboard" style="flex-shrink: 0;">
        <span><i class="fas fa-handshake"></i> 2. FOLLOW UP</span>
         <span class="stage-count-dashboard" id="followupCountDashboard"><?= $total_followup ?? 0 ?></span>
    </div>
    <div style="flex-shrink: 0; padding: 8px 12px;">
        <input type="text" id="searchFollowupDashboard" placeholder="Cari brand..." style="width: 100%; padding: 6px 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 12px; background: #1a1f2e; color: white;">
    </div>
    <div id="followupItemsContainerDashboard" style="flex: 1; overflow-y: auto; padding: 0 8px;">
        <?php if (!empty($followup_items)): ?>
            <?php foreach ($followup_items as $item): ?>
            <?php
            $has_deal = !empty($item->deal_confirmed_at);
            $has_products = ($item->has_products ?? false);
            $click_count = intval($item->follow_up_click_count ?? 0);
            $whatsapp_count = intval($item->whatsapp_count ?? 0);
            
            // 🔥 TENTUKAN APAKAH BISA DIKLIK
            // - Jika sudah deal DAN sudah ada produk → Bisa klik (pindah ke Task 3)
            // - Jika sudah deal TAPI belum ada produk → TIDAK bisa klik (menunggu registrasi)
            // - Jika belum deal → Bisa klik (follow up)
            $is_clickable = true;
            if ($has_deal && !$has_products) {
                $is_clickable = false;
            }
            $cursor_style = $is_clickable ? 'cursor: pointer;' : 'cursor: not-allowed; opacity: 0.8;';
            
            // 🔥 STATUS BADGE
            if ($has_deal && $has_products) {
                $status_badge = '<span class="badge-dashboard" style="background: rgba(139, 92, 246, 0.15); color: #8b5cf6;"><i class="fas fa-rocket"></i> Siap Setup</span>';
            } elseif ($has_deal && !$has_products) {
                $status_badge = '<span class="badge-dashboard" style="background: rgba(74, 222, 128, 0.15); color: #4ade80;"><i class="fas fa-clock"></i> Menunggu Registrasi</span>';
            } else {
                $status_badge = '<span class="badge-dashboard" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;"><i class="fas fa-clock"></i> Waiting Deal</span>';
            }
            
            // 🔥 NOTIFIKASI BAWAH
            $bottom_notification = '';
            if ($has_deal && !$has_products) {
                // Sedang menunggu registrasi brand
                $bottom_notification = '
                    <div style="margin-top: 8px; padding: 6px 10px; background: rgba(74,222,128,0.1); border-radius: 8px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <span style="display: inline-block; width: 8px; height: 8px; background: #4ade80; border-radius: 50%; box-shadow: 0 0 4px #4ade80;"></span>
                        <span style="font-size: 10px; color: #4ade80;">Menunggu registrasi brand</span>
                        <button class="check-registration-btn" 
                                data-brand-id="<?= $item->id ?>" 
                                data-brand-name="<?= htmlspecialchars($item->name) ?>"
                                style="background: #8b5cf6; color: white; border: none; padding: 4px 14px; border-radius: 30px; font-size: 10px; cursor: pointer; margin-left: auto;">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>';
            } elseif ($has_deal && $has_products) {
                // Deal confirmed & ada produk
                $bottom_notification = '
                    <div style="margin-top: 8px; padding: 6px 10px; background: rgba(139,92,246,0.1); border-radius: 8px;">
                        <span style="font-size: 10px; color: #8b5cf6;">
                            <i class="fas fa-rocket"></i> Brand sudah registrasi! <strong>Klik untuk Setup Campaign</strong>
                        </span>
                    </div>';
            }
            ?>
            <div class="stage-item-dashboard brand-item-dashboard followup-item-dashboard" 
                 data-brand-id="<?= $item->id ?>" 
                 data-brand-name="<?= htmlspecialchars($item->name) ?>" 
                 data-stage="2"
                 data-click-count="<?= $click_count ?>"
                 data-is-clickable="<?= $is_clickable ? 'true' : 'false' ?>"
                 style="position: relative; <?= $cursor_style ?>">
                
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <strong><i class="fas fa-building"></i> <?= htmlspecialchars($item->name) ?></strong>
                    <?= $status_badge ?>
                </div>
                
                <div class="brand-details-dashboard">
                    <span><i class="fab fa-whatsapp"></i> <?= $item->whatsapp_number ?? '-' ?></span>
                    <span><i class="fas fa-user"></i> Input: <?= $item->input_by ?? ($item->bd_username ?? '-') ?></span>
                    <span><i class="fas fa-calendar"></i> Follow Up: <?= date('d/m/Y', strtotime($item->follow_up_at ?? $item->updated_at)) ?></span>
                </div>
                
                <div class="brand-details-dashboard" style="margin-top: 4px;">
                    <span><i class="fas fa-percent"></i> Komisi: <?= $item->proposed_commission ?? 0 ?>%</span>
                    <?php if ($whatsapp_count > 0): ?>
                    <span><i class="fab fa-whatsapp"></i> WA: <?= $whatsapp_count ?>x</span>
                    <?php endif; ?>
                    <?php if ($item->campaign_id): ?>
                    <span><i class="fas fa-bullhorn"></i> Campaign: <?= substr($item->campaign_id, -6) ?></span>
                    <?php endif; ?>
                </div>
                
                <?= $bottom_notification ?>
                
                <?php if ($has_deal): ?>
                    <span class="badge-dashboard" style="margin-top: 8px; background: rgba(74, 222, 128, 0.15); color: #4ade80;">
                        <i class="fas fa-check-circle"></i> Deal: <?= date('d/m/Y H:i', strtotime($item->deal_confirmed_at)) ?>
                    </span>
                <?php else: ?>
                    <span class="badge-dashboard" style="margin-top: 8px; background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                        <i class="fas fa-clock"></i> FOLLOW UP
                    </span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="stage-item-dashboard empty-state">
                <strong><i class="fas fa-info-circle"></i> Belum ada brand di tahap follow up</strong>
                <div class="brand-details-dashboard">Brand akan muncul setelah deal dari hunting</div>
            </div>
        <?php endif; ?>
    </div>
    <button class="task-btn-dashboard" data-action="followup" style="flex-shrink: 0; margin: 8px;" disabled>
        <i class="fas fa-handshake"></i> Menunggu Konfirmasi
    </button>
</div>

            <!-- TASK 3: SETUP CAMPAIGN -->
         <div class="stage-card-dashboard" data-stage="3">
    <div class="stage-title-dashboard">
        <span><i class="fas fa-rocket"></i> 3. SETUP CAMPAIGN</span>
        <span class="stage-count-dashboard" id="setupCountDashboard"><?= $total_setup ?? 0 ?></span>
    </div>
    <div class="stage-search">
        <input type="text" id="searchSetupDashboard" placeholder="Cari brand...">
    </div>
    <div id="setupItemsContainerDashboard" class="stage-items-container">
        <?php if (!empty($setup_items)): ?>
            <?php foreach ($setup_items as $item): ?>
            <?php
            $is_active_brand = ($item->status == 'ACTIVE' || ($item->is_active_with_pending ?? false));
            $has_requirements = ($item->has_requirements ?? false);
            $pending_count = intval($item->pending_products_count ?? 0);
            $approved_count = intval($item->approved_products_count ?? 0);
            $has_submitted = $item->has_submitted_products ?? false;
            
            // 🔥 STATUS BADGE
            if ($is_active_brand) {
                $status_badge = '<span class="badge-dashboard" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                    <i class="fas fa-check-circle"></i> ACTIVE
                </span>';
            } else {
                $status_badge = '<span class="badge-dashboard badge-deal">
                    <i class="fas fa-cog"></i> MENUNGGU APPROVAL
                </span>';
            }
            
            // 🔥 NOTIFIKASI
            $requirement_note = '';
            if ($is_active_brand && $has_requirements) {
                $requirement_note = '
                    <div style="margin-top: 6px; padding: 4px 8px; background: rgba(74,222,128,0.1); border-radius: 6px;">
                        <span style="font-size: 9px; color: #4ade80;">
                            <i class="fas fa-check-circle"></i> Requirement auto-fill dari sebelumnya
                        </span>
                    </div>
                ';
            } elseif (!$has_requirements && !$is_active_brand) {
                $requirement_note = '
                    <div style="margin-top: 6px; padding: 4px 8px; background: rgba(245,158,11,0.1); border-radius: 6px;">
                        <span style="font-size: 9px; color: #f59e0b;">
                            <i class="fas fa-exclamation-triangle"></i> Requirement belum diisi
                        </span>
                    </div>
                ';
            }
            
            // 🔥 TOMBOL CEK STATUS (jika sudah submit dan tidak ada pending)
            $checkStatusBtn = '';
            if ($has_submitted && $pending_count == 0 && $approved_count > 0 && $item->status != 'ACTIVE') {
                $checkStatusBtn = `
                    <button class="check-active-status-btn" 
                            data-brand-id="<?= $item->id ?>" 
                            data-brand-name="<?= htmlspecialchars($item->name) ?>"
                            style="margin-top: 6px; width: 100%; background: #8b5cf6; color: white; border: none; padding: 4px 12px; border-radius: 20px; font-size: 10px; cursor: pointer;">
                        <i class="fas fa-sync-alt"></i> Cek Status & Pindah ke Task 4
                    </button>
                `;
            }
            ?>
            <div class="stage-item-dashboard brand-item-dashboard" 
                 data-brand-id="<?= $item->id ?>" 
                 data-brand-name="<?= htmlspecialchars($item->name) ?>" 
                 data-stage="3"
                 data-has-requirements="<?= $has_requirements ? 'true' : 'false' ?>"
                 data-is-active="<?= $is_active_brand ? 'true' : 'false' ?>">
                
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <strong>
                        <i class="fas fa-building"></i> <?= htmlspecialchars($item->name) ?>
                        <?php if ($is_active_brand): ?>
                            <span style="font-size: 9px; color: #10b981; margin-left: 6px;">
                                <i class="fas fa-sync-alt"></i> Produk Baru
                            </span>
                        <?php endif; ?>
                    </strong>
                    <?= $status_badge ?>
                </div>
                
                <div class="brand-details-dashboard">
                    <span><i class="fab fa-whatsapp"></i> <?= $item->whatsapp_number ?? '-' ?></span>
                    <span><i class="fas fa-user"></i> Input: <?= $item->input_by ?? ($item->bd_username ?? '-') ?></span>
                    <span class="badge-dashboard" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                        <i class="fas fa-clock"></i> <?= $pending_count ?> produk pending
                    </span>
                    <?php if ($approved_count > 0): ?>
                    <span class="badge-dashboard" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                        <i class="fas fa-check-circle"></i> <?= $approved_count ?> approve
                    </span>
                    <?php endif; ?>
                </div>
                
                <?= $requirement_note ?>
                <?= $checkStatusBtn ?>
                
                <?php if ($is_active_brand && $has_requirements): ?>
                    <span class="badge-dashboard" style="margin-top: 6px; background: rgba(139, 92, 246, 0.15); color: #8b5cf6; font-size: 9px;">
                        <i class="fas fa-history"></i> Auto-fill requirement
                    </span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="stage-item-dashboard empty-state">
                <strong><i class="fas fa-info-circle"></i> Belum ada brand</strong>
                <div class="brand-details-dashboard">Brand akan muncul setelah follow up selesai</div>
            </div>
        <?php endif; ?>
    </div>
    <button class="task-btn-dashboard" data-action="setup">
        <i class="fas fa-plug"></i> Setup Campaign
    </button>
</div>

            <!-- TASK 4: MONITORING -->
            <div class="stage-card-dashboard" data-stage="4">
                <div class="stage-title-dashboard">
                    <span><i class="fas fa-chart-line"></i> 4. MONITORING</span>
                     <span class="stage-count-dashboard" id="monitoringCountDashboard"><?= $total_monitoring ?? 0 ?></span>
                </div>
                <div class="stage-search">
                    <input type="text" id="searchMonitoringDashboard" placeholder="Cari brand...">
                </div>
                <div id="monitoringItemsContainerDashboard" class="stage-items-container">
                    <?php if (!empty($monitoring_items)): ?>
                        <?php foreach ($monitoring_items as $item): ?>
                        <div class="stage-item-dashboard brand-item-dashboard" data-brand-id="<?= $item->id ?>" data-brand-name="<?= htmlspecialchars($item->name) ?>" data-stage="4">
                            <strong><i class="fas fa-chart-line"></i> <?= htmlspecialchars($item->name) ?></strong>
                            <div class="brand-details-dashboard">
                                <span><i class="fab fa-whatsapp"></i> <?= $item->whatsapp_number ?? '-' ?></span>
                                <span><i class="fas fa-user"></i> Input: <?= $item->input_by ?? ($item->bd_username ?? '-') ?></span>
                                <span><i class="fas fa-money-bill-wave"></i> GMV: Rp <?= number_format($item->total_gmv ?? 0, 0, ',', '.') ?></span>
                                <span><i class="fas fa-chart-line"></i> ROAS: <?= $item->roas ?? 0 ?>x</span>
                                <span class="badge-dashboard" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                                    <i class="fas fa-check-circle"></i> <?= $item->approved_products_count ?? 0 ?> produk approve
                                </span>
                            </div>
                            <span class="badge-dashboard badge-active">
                                <i class="fas fa-chart-simple"></i> AKTIF
                            </span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="stage-item-dashboard empty-state">
                            <strong><i class="fas fa-info-circle"></i> Belum ada brand aktif</strong>
                            <div class="brand-details-dashboard">Brand akan muncul saat campaign berjalan</div>
                        </div>
                    <?php endif; ?>
                </div>
                <button class="task-btn-dashboard" data-action="monitoring">
                    <i class="fas fa-chart-simple"></i> Lihat Laporan
                </button>
            </div>

        </div>
    </div>

    <!-- Recent Orders Section -->
    <div class="recent-section-dashboard">
        <div class="recent-header">
            <h3><i class="fas fa-box"></i> Pesanan Hari Ini (<?= date('d/m/Y') ?>)</h3>
            <div class="recent-info">
                <span id="orderRangeDashboard">1-10</span> / <span id="totalOrdersDashboard"><?= count($orders) ?></span>
            </div>
        </div>
        <div class="recent-table-wrapper">
            <table class="recent-table-dashboard">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Produk</th>
                        <th>Creator</th>
                        <th>GMV</th>
                        <th>Komisi</th>
                    </tr>
                </thead>
                <tbody id="recentOrdersBodyDashboard"></tbody>
            </table>
        </div>
        <div class="pagination-dashboard">
            <button id="prevPageBtnDashboard" disabled><i class="fas fa-chevron-left"></i> Sebelumnya</button>
            <button id="nextPageBtnDashboard">Selanjutnya <i class="fas fa-chevron-right"></i></button>
        </div>
    </div>
</div>
    <!-- ==================== TAB 2: STATUS BRAND ==================== -->
<!-- TAB STATUS BRAND -->
<div id="tabBrandStatusDashboard" class="tab-content-dashboard">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        
        <!-- KOLOM KIRI: BRAND TIDAK AKTIF -->
        <div class="brand-card-dashboard" style="background: var(--bg-card); border-radius: 20px; padding: 16px; border: 1px solid var(--border);">
            <h3 style="color: var(--text-primary); margin-bottom: 16px; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-clock" style="color: #f59e0b;"></i> 
                Brand Tidak Aktif
                <span class="badge-dashboard" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b; margin-left: 8px;">
                    <?= count($inactive_brands) ?>
                </span>
            </h3>
            <div style="max-height: 500px; overflow-y: auto;">
                <?php if (!empty($inactive_brands)): ?>
                    <?php foreach ($inactive_brands as $brand): ?>
                    <div class="brand-item-row-dashboard" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--border);">
                        <div>
                            <strong style="color: var(--text-primary); font-size: 13px;"><?= htmlspecialchars($brand->name) ?></strong>
                            <div style="display: flex; gap: 10px; margin-top: 4px; flex-wrap: wrap;">
                                <span style="font-size: 10px; color: var(--text-muted);">
                                    <i class="fas fa-user"></i> Input: <?= $brand->input_by ?? ($brand->bd_username ?? '-') ?>
                                </span>
                                <span class="badge-dashboard" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b; font-size: 9px;">
                                    <?= $brand->status ?>
                                </span>
                            </div>
                        </div>
                        <div>
                            <?php if ($brand->status == 'PENDING'): ?>
                                <span style="font-size: 11px; color: #f59e0b;"><i class="fas fa-hourglass-half"></i> Hunting</span>
                            <?php elseif ($brand->status == 'CAMPAIGN_READY'): ?>
                                <span style="font-size: 11px; color: #8b5cf6;"><i class="fas fa-rocket"></i> Setup</span>
                            <?php else: ?>
                                <span style="font-size: 11px; color: #6b7280;"><i class="fas fa-pause"></i> <?= $brand->status ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center" style="color: var(--text-muted); padding: 40px;">
                        <i class="fas fa-check-circle" style="font-size: 32px; margin-bottom: 12px; display: block;"></i>
                        <p>Semua brand sudah aktif!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- KOLOM KANAN: BRAND AKTIF -->
        <div class="brand-card-dashboard" style="background: var(--bg-card); border-radius: 20px; padding: 16px; border: 1px solid var(--border);">
            <h3 style="color: var(--text-primary); margin-bottom: 16px; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-chart-line" style="color: #10b981;"></i> 
                Brand Aktif
                <span class="badge-dashboard" style="background: rgba(16, 185, 129, 0.15); color: #10b981; margin-left: 8px;">
                    <?= count($active_brands_list) ?>
                </span>
            </h3>
            <div style="max-height: 500px; overflow-y: auto;">
                <?php if (!empty($active_brands_list)): ?>
                    <?php foreach ($active_brands_list as $brand): ?>
                    <div class="brand-item-row-dashboard" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--border);">
                        <div>
                            <strong style="color: var(--text-primary); font-size: 13px;"><?= htmlspecialchars($brand->name) ?></strong>

                            <div style="display: flex; gap: 10px; margin-top: 4px; flex-wrap: wrap;">
                                <span style="font-size: 10px; color: var(--text-muted);">
                                    <i class="fas fa-user"></i> Deal: <?= $brand->bd_name ?? $brand->bd_username ?? '-' ?>
                                </span>
                                <span style="font-size: 10px; color: #4ade80;">
                                    <i class="fas fa-chart-simple"></i> GMV: Rp <?= number_format($brand->total_gmv, 0, ',', '.') ?>
                                </span>
                            </div>
                        </div>
                        <div>
                            <span style="font-size: 11px; color: #10b981;">
                                <i class="fas fa-circle" style="font-size: 8px;"></i> ACTIVE
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center" style="color: var(--text-muted); padding: 40px;">
                        <i class="fas fa-store" style="font-size: 32px; margin-bottom: 12px; display: block;"></i>
                        <p>Belum ada brand aktif</p>
                        <p style="font-size: 11px; margin-top: 8px;">Selesaikan deal dengan brand untuk menampilkannya di sini.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
</div>
    <!-- ==================== TAB 3: LEADERBOARD ==================== -->
    <div id="tabLeaderboardDashboard" class="tab-content-dashboard">
        <div class="leaderboard-container-dashboard" style="background: var(--bg-card); border-radius: 20px; padding: 20px; border: 1px solid var(--border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h3 style="color: var(--text-primary); font-size: 16px; margin: 0;">
                        <i class="fas fa-trophy" style="color: var(--purple);"></i> Top 10 Brands by GMV
                    </h3>
                    <p style="font-size: 11px; color: var(--text-muted); margin-top: 5px;">
                        <i class="fas fa-calendar-alt"></i> Periode: <?= date('d/m/Y', strtotime('-7 days')) ?> - <?= date('d/m/Y') ?> (7 hari terakhir)
                    </p>
                </div>
                <button id="refreshLeaderboardBtn" class="btn-secondary" style="background: var(--bg-elevated); border: 1px solid var(--border); padding: 6px 14px; border-radius: 20px; cursor: pointer; font-size: 11px; color: var(--text-secondary);">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
            
            <div style="overflow-x: auto;">
                <table class="leaderboard-table-dashboard" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border);">
                            <th style="padding: 12px 8px; text-align: center; width: 70px;">Rank</th>
                            <th style="padding: 12px 8px; text-align: left;">Brand</th>
                            <th style="padding: 12px 8px; text-align: left;">Kategori</th>
                            <th style="padding: 12px 8px; text-align: right;">GMV (7 hari)</th>
                            <th style="padding: 12px 8px; text-align: center;">Orders</th>
                            <th style="padding: 12px 8px; text-align: center;">Creators</th>
                            <th style="padding: 12px 8px; text-align: center;">ROAS</th>
                            <th style="padding: 12px 8px; text-align: left;">Deal oleh</th>
                        </tr>
                    </thead>
                    <tbody id="leaderboardBodyDashboard">
                        <?php if (!empty($leaderboard_brands)): ?>
                            <?php foreach ($leaderboard_brands as $brand): ?>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 12px 8px; text-align: center;">
                                    <?php if ($brand['rank'] == 1): ?>
                                        <i class="fas fa-crown" style="color: #fbbf24; font-size: 24px;"></i>
                                    <?php elseif ($brand['rank'] == 2): ?>
                                        <i class="fas fa-medal" style="color: #c0c0c0; font-size: 24px;"></i>
                                    <?php elseif ($brand['rank'] == 3): ?>
                                        <i class="fas fa-medal" style="color: #cd7f32; font-size: 24px;"></i>
                                    <?php else: ?>
                                        <span style="font-weight: 700; color: var(--purple); font-size: 16px;">#<?= $brand['rank'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 8px; text-align: left;">
                                    <strong style="color: var(--text-primary); font-size: 14px;"><?= htmlspecialchars($brand['brand_name']) ?></strong>
                                </td>
                                <td style="padding: 12px 8px; text-align: left;">
                                    <span class="badge-dashboard" style="background: rgba(139, 92, 246, 0.15); color: #8b5cf6; font-size: 10px;">
                                        <?= htmlspecialchars($brand['category']) ?>
                                    </span>
                                </td>
                                <td style="padding: 12px 8px; text-align: right; color: #4ade80; font-weight: 600;">
                                    Rp <?= number_format($brand['total_gmv'], 0, ',', '.') ?>
                                </td>
                                <td style="padding: 12px 8px; text-align: center;">
                                    <?= number_format($brand['total_orders']) ?>
                                </td>
                                <td style="padding: 12px 8px; text-align: center;">
                                    <?= number_format($brand['total_creators']) ?>
                                </td>
                                <td style="padding: 12px 8px; text-align: center;">
                                    <span style="color: <?= $brand['roas'] >= 3 ? '#4ade80' : ($brand['roas'] >= 1 ? '#f59e0b' : '#ef4444') ?>; font-weight: 600;">
                                        <?= $brand['roas'] ?>x
                                    </span>
                                </td>
                                <td style="padding: 12px 8px; text-align: left;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="width: 28px; height: 28px; background: var(--purple-glow); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-user" style="color: var(--purple); font-size: 12px;"></i>
                                        </div>
                                        <div>
                                            <div style="font-size: 13px; font-weight: 500; color: var(--text-primary);">
                                                <?= htmlspecialchars($brand['bd_name'] != '-' ? $brand['bd_name'] : $brand['bd_username']) ?>
                                            </div>
                                            <?php if ($brand['input_by'] && $brand['input_by'] != '-'): ?>
                                            <div style="font-size: 9px; color: var(--text-muted);">
                                                <i class="fas fa-user-plus"></i> Input: <?= htmlspecialchars($brand['input_by']) ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="padding: 60px 20px; text-align: center; color: var(--text-muted);">
                                    <i class="fas fa-chart-line" style="font-size: 48px; margin-bottom: 16px; display: block; opacity: 0.5;"></i>
                                    <p style="font-size: 14px;">Belum ada data transaksi dalam 7 hari terakhir</p>
                                    <p style="font-size: 11px; margin-top: 8px;">Segera deal dengan brand dan dapatkan transaksi untuk masuk leaderboard!</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (!empty($leaderboard_brands)): ?>
            <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div style="font-size: 11px; color: var(--text-muted);">
                    <i class="fas fa-info-circle"></i> Berdasarkan data transaksi 7 hari terakhir (<?= date('d/m/Y', strtotime('-7 days')) ?> - <?= date('d/m/Y') ?>)
                    <span style="margin-left: 12px;">| Update: <?= date('d/m/Y H:i') ?></span>
                </div>
                <div style="display: flex; gap: 6px;">
                    <span style="font-size: 10px; display: inline-flex; align-items: center; gap: 4px;">
                        <span style="display: inline-block; width: 10px; height: 10px; background: #4ade80; border-radius: 2px;"></span> ROAS 3x
                    </span>
                    <span style="font-size: 10px; display: inline-flex; align-items: center; gap: 4px;">
                        <span style="display: inline-block; width: 10px; height: 10px; background: #f59e0b; border-radius: 2px;"></span> ROAS 1-3x
                    </span>
                    <span style="font-size: 10px; display: inline-flex; align-items: center; gap: 4px;">
                        <span style="display: inline-block; width: 10px; height: 10px; background: #ef4444; border-radius: 2px;"></span> ROAS &lt; 1x
                    </span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Task -->
<div id="taskModalDashboard" class="modal-overlay-dashboard">
    <div class="modal-glass-dashboard">
        <div class="modal-header-dashboard">
            <h3 id="modalTitleDashboard"><i class="fas fa-tasks"></i> Task</h3>
            <span class="modal-close-dashboard" id="closeTaskModalDashboard" onclick="document.getElementById('taskModalDashboard').classList.remove('active'); return false;" style="cursor: pointer; font-size: 26px;">&times;</span>
        </div>
        <div class="modal-body" id="modalBodyDashboard"></div>
    </div>
</div>

<!-- Mobile Bottom Navigation -->
<div class="mobile-bottom-nav-dashboard">
    <a href="#" class="mobile-nav-item-dashboard active" data-tab="tabTaskDashboard"><i class="fas fa-tasks"></i><span>Task</span></a>
    <a href="#" class="mobile-nav-item-dashboard" data-tab="tabBrandStatusDashboard"><i class="fas fa-building"></i><span>Brands</span></a>
    <a href="#" class="mobile-nav-item-dashboard" data-tab="tabLeaderboardDashboard"><i class="fas fa-trophy"></i><span>Top</span></a>
</div>

<!-- Modal Input Brand Batch -->
<div id="batchBrandModal" class="modal-overlay-dashboard">
    <div class="modal-glass-dashboard" style="max-width: 800px; width: 95%;">
        <div class="modal-header-dashboard">
            <h3><i class="fas fa-plus-circle"></i> Input Brand Baru (Batch)</h3>
            <span class="modal-close-dashboard" id="closeBatchBrandModal">&times;</span>
        </div>
        <div class="modal-body" id="batchBrandBody">
            <!-- Informasi -->
            <div style="background:rgba(74,222,128,0.1); border-radius:14px; padding:12px; margin-bottom:16px;">
                <p style="color:#4ade80; font-size:12px;">
                    <i class="fas fa-info-circle"></i> Input multiple brand sekaligus
                </p>
                <p style="color:#9aaebe; font-size:11px;">
                    Isi nama brand dan nomor WhatsApp, lalu klik <strong>Tambah Baris</strong> untuk menambah lebih banyak.
                </p>
            </div>

            <!-- Form Input -->
            <div style="display:flex; gap:10px; margin-bottom:12px;">
                <input type="text" id="batchBrandName" placeholder="Nama Brand (contoh: Lacera)" 
                       style="flex:2; padding:10px; background:#0f1420; border:1px solid #2a3346; border-radius:10px; color:#e2f0e8;">
                <input type="tel" id="batchBrandPhone" placeholder="WhatsApp (+62...)" 
                       style="flex:1; padding:10px; background:#0f1420; border:1px solid #2a3346; border-radius:10px; color:#e2f0e8;">
                <button id="addBatchRowBtn" style="background:#8b5cf6; color:white; border:none; padding:0 20px; border-radius:10px; cursor:pointer; font-weight:600; white-space:nowrap;">
                    <i class="fas fa-plus"></i> Tambah
                </button>
            </div>

            <!-- Daftar Brand yang Akan Diinput -->
            <div style="background:#0f1420; border-radius:12px; border:1px solid #2a3346; overflow:hidden;">
                <table style="width:100%; border-collapse:collapse; font-size:12px;">
                    <thead>
                        <tr style="background:#1a1f2e; border-bottom:1px solid #2a3346;">
                            <th style="padding:10px 12px; text-align:left; color:#9aaebe; width:50px;">#</th>
                            <th style="padding:10px 12px; text-align:left; color:#9aaebe;">Nama Brand</th>
                            <th style="padding:10px 12px; text-align:left; color:#9aaebe;">WhatsApp</th>
                            <th style="padding:10px 12px; text-align:center; color:#9aaebe; width:60px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="batchBrandTableBody">
                        <tr id="batchEmptyRow">
                            <td colspan="4" style="padding:30px; text-align:center; color:#6b7280;">
                                <i class="fas fa-plus-circle" style="font-size:24px; display:block; margin-bottom:8px;"></i>
                                Belum ada brand. Klik "Tambah" untuk menambahkan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Summary & Actions -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:16px; flex-wrap:wrap; gap:10px;">
                <div>
                    <span style="color:#9aaebe; font-size:12px;">Total brand: <strong id="batchTotalCount">0</strong></span>
                </div>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <button id="batchCopyAllBtn" style="background:#1e293b; color:#4ade80; border:1px solid #4ade80; padding:8px 16px; border-radius:40px; cursor:pointer; font-size:12px;">
                        <i class="fas fa-copy"></i> Copy Semua
                    </button>
                    <button id="batchClearAllBtn" style="background:#1e293b; color:#ef4444; border:1px solid #ef4444; padding:8px 16px; border-radius:40px; cursor:pointer; font-size:12px;">
                        <i class="fas fa-trash-alt"></i> Kosongkan
                    </button>
                    <button id="batchSaveAllBtn" style="background:#4ade80; color:#0a0e17; padding:8px 24px; border-radius:40px; border:none; cursor:pointer; font-weight:600; font-size:13px;">
                        <i class="fas fa-save"></i> Simpan Semua
                    </button>
                </div>
            </div>

            <!-- Progress / Result -->
            <div id="batchResultContainer" style="display:none; margin-top:16px; background:#0f1420; border-radius:12px; padding:12px; max-height:200px; overflow-y:auto;"></div>
        </div>
    </div>
</div>
<!-- ============================================================ -->
<!-- MODAL LIST BRAND AKTIF -->
<!-- ============================================================ -->
<div id="modalActiveBrands" class="modal-overlay-dashboard">
    <div class="modal-glass-dashboard" style="max-width: 900px; width: 95%;">
        <div class="modal-header-dashboard">
            <h3><i class="fas fa-check-circle" style="color: #10b981;"></i> Brand Aktif (LIVE)</h3>
            <span class="modal-close-dashboard" id="closeActiveBrandsModal" style="cursor: pointer; font-size: 26px; color: #9aaebe;">&times;</span>
        </div>
        <div class="modal-body" id="activeBrandsModalBody">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                <div>
                    <span style="color: #9aaebe; font-size: 12px;">Total Brand Aktif: <strong id="totalActiveBrandsCount" style="color: #4ade80;">0</strong></span>
                </div>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <input type="text" id="searchActiveBrands" placeholder="Cari brand..." style="padding: 8px 14px; background: #0f1420; border: 1px solid #2a3346; border-radius: 10px; color: #e2f0e8; font-size: 12px; width: 200px;">
                    <button id="refreshActiveBrandsBtn" style="background: #1e293b; border: 1px solid #4ade80; color: #4ade80; padding: 6px 14px; border-radius: 20px; cursor: pointer; font-size: 11px;">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
            <div id="activeBrandsListContainer" style="max-height: 500px; overflow-y: auto;">
                <div style="text-align: center; padding: 40px; color: #9aaebe;">
                    <i class="fas fa-spinner fa-pulse fa-2x"></i>
                    <p style="margin-top: 12px;">Memuat data brand aktif...</p>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
// ========== FILE: dashboard.js (FULL VERSION WITH ALL UPDATES) ==========
// ========== PASTE DI BAGIAN <script> DI dashboard.php ==========

// ========== VARIABLES ==========
let selectedProductsForReview = [];
let currentBrandIdForReview = null;
let currentBrandNameForReview = null;
let allPendingProducts = [];
let isLoadingProducts = false;
let searchDebounceTimer = null;
let currentSearchKeyword = '';
let currentSearchResults = { shops: [], products: [] };
let currentSelectedShop = null;
let currentSelectedProduct = null;
let isSearchingBrand = false;
let currentPageDashboard = 0;
let perPageDashboard = 10;

// ========== UTILITY FUNCTIONS ==========
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

function copyToClipboard(text, productName, productId, buttonElement) {
    if (!text) return;
    
    let copyText = '';
    if (productName && productId) {
        copyText = ` Product: ${productName}\n Product ID: ${productId}\n🔗 Link: ${text}`;
    } else {
        copyText = text;
    }
    
    navigator.clipboard.writeText(copyText).then(() => {
        showToastInModal(`Link untuk "${productName || 'produk'}" berhasil dicopy!`, 'success');
        
        if (buttonElement) {
            const originalHtml = buttonElement.innerHTML;
            const originalBg = buttonElement.style.background;
            const originalColor = buttonElement.style.color;
            buttonElement.innerHTML = '<i class="fas fa-check"></i> Copied!';
            buttonElement.style.background = '#4ade80';
            buttonElement.style.color = '#0a0e17';
            setTimeout(() => {
                buttonElement.innerHTML = originalHtml;
                buttonElement.style.background = originalBg;
                buttonElement.style.color = originalColor;
            }, 2000);
        }
    }).catch(() => {
        const textarea = document.createElement('textarea');
        textarea.value = copyText;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        showToastInModal(` Link untuk "${productName || 'produk'}" berhasil dicopy!`, 'success');
    });
}

function showToastGlobal(message, type = 'success') {
    let existingToast = document.getElementById('globalToast');
    if (existingToast) existingToast.remove();
    
    const toast = document.createElement('div');
    toast.id = 'globalToast';
    let bgColor = '#10b981';
    if (type === 'error') bgColor = '#ef4444';
    if (type === 'warning') bgColor = '#f59e0b';
    
    toast.style.cssText = `
        position: fixed; bottom: 30px; right: 30px; background: ${bgColor}; color: white;
        padding: 14px 24px; border-radius: 12px; font-size: 13px; font-weight: 500;
        z-index: 10001; animation: slideIn 0.3s ease; box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        backdrop-filter: blur(4px); pointer-events: none; max-width: 350px;
    `;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (toast && toast.parentNode) {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => { if (toast && toast.parentNode) toast.remove(); }, 300);
        }
    }, 3000);
}

function showToastInModal(message, type = 'success') {
    const activeModal = document.querySelector('#taskModalDashboard.active');
    if (!activeModal) {
        showToastGlobal(message, type);
        return;
    }
    
    const modalBody = activeModal.querySelector('.modal-body');
    if (!modalBody) {
        showToastGlobal(message, type);
        return;
    }
    
    const oldToast = modalBody.querySelector('.modal-toast');
    if (oldToast) oldToast.remove();
    
    const toast = document.createElement('div');
    toast.className = `modal-toast ${type}`;
    let icon = 'fa-check-circle';
    if (type === 'error') icon = 'fa-exclamation-circle';
    if (type === 'warning') icon = 'fa-exclamation-triangle';
    if (type === 'info') icon = 'fa-info-circle';
    
    toast.innerHTML = `<i class="fas ${icon}"></i> ${message}`;
    
    if (modalBody.firstChild) {
        modalBody.insertBefore(toast, modalBody.firstChild);
    } else {
        modalBody.appendChild(toast);
    }
    
    setTimeout(() => {
        if (toast && toast.parentNode) {
            toast.style.animation = 'slideOutUp 0.3s ease';
            setTimeout(() => { if (toast && toast.parentNode) toast.remove(); }, 300);
        }
    }, 3000);
}

function startConfetti() {
    if (typeof confetti === 'function') {
        confetti({ particleCount: 100, spread: 70, origin: { y: 0.6 } });
    }
}

function closeModalDashboard() { 
    const modal = document.getElementById('taskModalDashboard');
    if (modal) modal.classList.remove('active');
    isLoadingProducts = false;
}

function openModalDashboard() {
    document.getElementById('taskModalDashboard').classList.add('active');
    ensureCloseButtonWorks();
}

function ensureCloseButtonWorks() {
    const headerCloseBtn = document.getElementById('closeTaskModalDashboard');
    if (headerCloseBtn) {
        const newHeaderCloseBtn = headerCloseBtn.cloneNode(true);
        headerCloseBtn.parentNode.replaceChild(newHeaderCloseBtn, headerCloseBtn);
        newHeaderCloseBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeModalDashboard();
        });
    }
    
    const modalOverlay = document.getElementById('taskModalDashboard');
    if (modalOverlay) {
        const newOverlay = modalOverlay.cloneNode(true);
        modalOverlay.parentNode.replaceChild(newOverlay, modalOverlay);
        newOverlay.addEventListener('click', function(e) {
            if (e.target === newOverlay) closeModalDashboard();
        });
    }
}

// ========== STAGE COMPLETION ==========
let completedStagesDashboard = JSON.parse(localStorage.getItem('bd_completed_stages')) || [];

function saveCompletedStagesDashboard() { 
    localStorage.setItem('bd_completed_stages', JSON.stringify(completedStagesDashboard)); 
}

function isStageCompletedDashboard(stageNum) { 
    return completedStagesDashboard.includes(stageNum); 
}

function updateStageUIDashboard() {
    const stageOrder = [1, 2, 3, 4];
    
    document.querySelectorAll('.stage-card-dashboard').forEach(card => {
        const stageNum = parseInt(card.getAttribute('data-stage'));
        const btns = card.querySelectorAll('.task-btn-dashboard');
        
        if (isStageCompletedDashboard(stageNum)) {
            card.classList.add('completed');
            btns.forEach(btn => { 
                btn.disabled = true; 
                btn.innerHTML = '<i class="fas fa-check-circle"></i> Completed'; 
            });
        } else {
            let previousStage = null;
            if (stageNum === 2) previousStage = 1;
            if (stageNum === 3) previousStage = 2;
            if (stageNum === 4) previousStage = 3;
            
            if (previousStage && !isStageCompletedDashboard(previousStage)) {
                btns.forEach(btn => { 
                    btn.disabled = true; 
                    btn.innerHTML = '<i class="fas fa-lock"></i> Selesaikan stage sebelumnya'; 
                });
            } else {
                card.classList.remove('completed');
                btns.forEach(btn => btn.disabled = false);
            }
        }
    });
}

// ========== WHATSAPP FUNCTIONS ==========
function sendWhatsAppOnlyDashboard(brandId, phoneNumber, message) {
    let phone = phoneNumber.replace(/[^0-9+]/g, '');
    if (phone.startsWith('0')) phone = '+62' + phone.substring(1);
    else if (!phone.startsWith('+')) phone = '+' + phone;
    const cleanPhone = phone.replace(/^\+/, '');
    const whatsappUrl = `https://wa.me/${cleanPhone}?text=${encodeURIComponent(message)}`;
    
    fetch(baseUrlDashboard + 'bd/log_whatsapp_only', { 
        method: 'POST', 
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, 
        body: new URLSearchParams({ brand_id: brandId, phone_number: phone, message: message, stage: 1 }) 
    }).then(() => { 
        window.open(whatsappUrl, 'whatsapp_tab');
    }).catch(() => {
        window.open(whatsappUrl, 'whatsapp_tab');
    });
}



// ==================== FORMAT NOMOR WHATSAPP - PASTIKAN +62 ====================
function formatWhatsAppNumber(phone) {
    if (!phone) return '';
    
    // Hapus semua karakter non-digit (spasi, tanda hubung, titik, dll)
    let clean = phone.replace(/[^0-9]/g, '');
    
    // Jika kosong, return
    if (!clean) return '';
    
    // CASES:
    // 1. 8999 -> +628999
    // 2. 08999 -> +628999
    // 3. 628999 -> +628999
    // 4. +628999 -> +628999
    
    // Jika dimulai dengan 0, ganti 0 dengan 62
    if (clean.startsWith('0')) {
        clean = '62' + clean.substring(1);
    }
    // Jika dimulai dengan 62, pertahankan
    else if (clean.startsWith('62')) {
        // sudah benar, tidak diubah
    }
    // Jika tidak dimulai dengan 0 atau 62, tambahkan 62 di depan
    else {
        clean = '62' + clean;
    }
    
    // Kembalikan dengan format +62
    return '+' + clean;
}

// ==================== ALTERNATIF: TANPA + ====================
function formatWhatsAppNumberWithoutPlus(phone) {
    if (!phone) return '';
    
    let clean = phone.replace(/[^0-9]/g, '');
    if (!clean) return '';
    
    if (clean.startsWith('0')) {
        clean = '62' + clean.substring(1);
    } else if (!clean.startsWith('62')) {
        clean = '62' + clean;
    }
    
    return clean; // Kembalikan tanpa + (contoh: 628999)
}
// ==================== SEND WHATSAPP DEAL - AUTO +62 ====================
function sendWhatsAppDealDashboard(brandId, phoneNumber, message, bannerUrl, brandName, stage) {
    if (!phoneNumber) {
        showToastInModal('Nomor WhatsApp tidak tersedia!', 'error');
        return;
    }
    
    // 🔥 AUTO FORMAT +62
    let formattedPhone = formatWhatsAppNumber(phoneNumber);
    // Hapus + untuk URL (WA.me butuh tanpa +)
    let phoneForUrl = formattedPhone.replace('+', '');
    
    // 🔥 INSERT KE DATABASE WHATSAPP_LOGS
    const logData = {
        brand_id: brandId,
        brand_name: brandName || '',
        phone_number: formattedPhone, // Simpan dengan format +62
        original_phone: phoneNumber,
        message: message,
        banner_url: bannerUrl || '',
        stage: stage || 1,
        type: 'deal'
    };
    
    // Kirim ke server untuk insert
    fetch(baseUrlDashboard + 'bd/log_whatsapp_deal', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(logData)
    })
    .then(response => response.json())
    .then(result => {
        console.log('WhatsApp log saved:', result);
    })
    .catch(err => {
        console.error('Error saving WhatsApp log:', err);
    });
    
    // Buka WhatsApp
    const bannerInstruction = bannerUrl ? 
        "\n\n *Banner Kerjasama:* " + bannerUrl + "\n(Silakan download dan kirim gambar ini sebagai media terpisah)" : '';
    
    const finalMessage = message + bannerInstruction;
    const whatsappUrl = `https://wa.me/${phoneForUrl}?text=${encodeURIComponent(finalMessage)}`;
    
    console.log('WhatsApp URL:', whatsappUrl);
    console.log('Formatted phone:', formattedPhone, '→ URL:', phoneForUrl);
    
    window.open(whatsappUrl, 'whatsapp_tab');
    
    if (bannerUrl) {
        setTimeout(() => {
            showToastInModal('📸 Jangan lupa kirim gambar banner secara terpisah!', 'info');
        }, 1000);
    }
}

// ==================== SEND WHATSAPP DIRECT ====================
function sendWhatsAppDirect(brandId, phoneNumber, message, stage) {
    if (!phoneNumber) {
        showToastInModal('Nomor WhatsApp tidak tersedia!', 'error');
        return;
    }
    
    // 🔥 AUTO FORMAT +62
    let formattedPhone = formatWhatsAppNumber(phoneNumber);
    let phoneForUrl = formattedPhone.replace('+', '');
    
    // 🔥 INSERT KE DATABASE
    fetch(baseUrlDashboard + 'bd/log_whatsapp_deal', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            brand_id: brandId,
            phone_number: formattedPhone,
            original_phone: phoneNumber,
            message: message,
            stage: stage || 1,
            type: 'direct'
        })
    }).catch(() => {});
    
    const whatsappUrl = `https://wa.me/${phoneForUrl}?text=${encodeURIComponent(message)}`;
    window.open(whatsappUrl, 'whatsapp_tab');
}

// ==================== SEND WHATSAPP ONLY ====================
function sendWhatsAppOnlyDashboard(brandId, phoneNumber, message, stage) {
    if (!phoneNumber) {
        showToastInModal('Nomor WhatsApp tidak tersedia!', 'error');
        return;
    }
    
    // 🔥 AUTO FORMAT +62
    let formattedPhone = formatWhatsAppNumber(phoneNumber);
    let phoneForUrl = formattedPhone.replace('+', '');
    
    // 🔥 INSERT KE DATABASE
    fetch(baseUrlDashboard + 'bd/log_whatsapp_deal', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            brand_id: brandId,
            phone_number: formattedPhone,
            original_phone: phoneNumber,
            message: message,
            stage: stage || 1,
            type: 'whatsapp_only'
        })
    }).catch(() => {});
    
    const whatsappUrl = `https://wa.me/${phoneForUrl}?text=${encodeURIComponent(message)}`;
    window.open(whatsappUrl, 'whatsapp_tab');
}

// ========== TASK 1: HUNTING DETAIL ==========
async function getBrandDetailDashboard(brandId) {
    const response = await fetch(baseUrlDashboard + 'bd/get_brand_detail', { 
        method: 'POST', 
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, 
        body: new URLSearchParams({ brand_id: brandId }) 
    });
    const result = await response.json();
    return result.success ? result.data : null;
}



// ========== TASK 2: FOLLOW UP MODAL ==========

let availableCampaigns = [];

async function loadAvailableCampaigns() {
    try {
        const response = await fetch(baseUrlDashboard + 'bd/get_active_campaigns');
        const result = await response.json();
        if (result.success) {
            availableCampaigns = result.campaigns;
        }
    } catch (error) {
        console.error('Error loading campaigns:', error);
        availableCampaigns = [];
    }
}


async function showTask2FollowUpModal(brandId, brandName) {
    await loadAvailableCampaigns();
    
    const response = await fetch(baseUrlDashboard + 'bd/get_brand_followup_detail', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ brand_id: brandId })
    });
    
    const result = await response.json();
    
    if (!result.success) {
        showToastInModal('Gagal mengambil data brand', 'error');
        return;
    }
    
    const brand = result.brand;
    const openCommission = result.open_commission_rate;
    let recommendedMin = result.recommended_commission_min;
    let recommendedMax = result.recommended_commission_max;
    let currentCommission = result.current_commission;
    let maxCommission = result.max_commission || recommendedMax; // 🔥 MAX FLEKSIBEL
    
    // 🔥 SLIDER: MIN = recommendedMin, MAX = maxCommission (bisa diubah user)
    const sliderMin = recommendedMin;
    const sliderMax = Math.max(50, maxCommission + 10);
    
    // 🔥 CEK STATUS REGISTRASI BRAND
    const regResponse = await fetch(baseUrlDashboard + 'bd/check_brand_registration', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ brand_id: brandId })
    });
    const regResult = await regResponse.json();
    
    const isRegistered = regResult.is_registered;
    const productCount = regResult.product_count || 0;
    const pendingCount = regResult.pending_count || 0;
    const approvedCount = regResult.approved_count || 0;
    
    const modalTitleElem = document.getElementById('modalTitleDashboard');
    const modalBodyElem = document.getElementById('modalBodyDashboard');
    
    // 🔥 BUILD CAMPAIGN OPTIONS HTML
    let campaignOptionsHtml = '';
    if (availableCampaigns.length > 0) {
        campaignOptionsHtml = `
            <div style="margin-bottom:16px;">
                <label style="color:#e2f0e8; font-weight:500; margin-bottom:8px; display:block;">
                    <i class="fas fa-bullhorn"></i> Pilih Campaign *
                </label>
                <select id="campaignSelect" style="width:100%; padding:12px; background:#0f1420; border:1px solid #2a3346; border-radius:12px; color:#e2f0e8; margin-bottom:8px;">
                    <option value="">-- Pilih Campaign --</option>
                    ${availableCampaigns.map(camp => `
                        <option value="${camp.campaign_id}" 
                                data-campaign-name="${escapeHtml(camp.campaign_name)}"
                                data-campaign-link="${camp.campaign_link || 'https://partner.tiktokshop.com/campaign/' + camp.campaign_id}">
                            ${escapeHtml(camp.campaign_name)} (ID: ${camp.campaign_id})
                        </option>
                    `).join('')}
                </select>
                <div style="font-size:10px; color:#9aaebe;">
                    <i class="fas fa-info-circle"></i> Campaign aktif yang tersedia untuk TAP
                </div>
            </div>
        `;
    } else {
        campaignOptionsHtml = `
            <div style="background:rgba(245,158,11,0.1); border-radius:12px; padding:12px; margin-bottom:16px; border-left:3px solid #f59e0b;">
                <div style="color:#fbbf24; font-size:12px; margin-bottom:4px;">
                    <i class="fas fa-exclamation-triangle"></i> Belum ada campaign aktif!
                </div>
                <div style="color:#9aaebe; font-size:11px;">
                    Silakan buat campaign terlebih dahulu di menu Campaigns.
                </div>
            </div>
        `;
    }
    
    // 🔥 BUILD REGISTRATION STATUS HTML
    let registrationStatusHtml = '';
    if (isRegistered) {
        registrationStatusHtml = `
            <div style="background:rgba(74,222,128,0.1); border-radius:12px; padding:12px; margin-bottom:16px; border-left:3px solid #4ade80;">
                <div style="color:#4ade80; font-size:12px; margin-bottom:4px;">
                    <i class="fas fa-check-circle"></i> Brand SUDAH Registrasi!
                </div>
                <div style="color:#9aaebe; font-size:11px;">
                    Total produk: ${productCount} (Pending: ${pendingCount}, Approved: ${approvedCount})
                </div>
                <div style="color:#9aaebe; font-size:11px; margin-top:4px;">
                    Brand akan otomatis pindah ke Task 3 (Setup Campaign).
                </div>
            </div>
        `;
    } else {
        registrationStatusHtml = `
            <div style="background:rgba(245,158,11,0.1); border-radius:12px; padding:12px; margin-bottom:16px; border-left:3px solid #f59e0b;">
                <div style="color:#fbbf24; font-size:12px; margin-bottom:4px;">
                    <i class="fas fa-clock"></i> Menunggu Registrasi Brand
                </div>
                <div style="color:#9aaebe; font-size:11px;">
                    Brand belum registrasi campaign. Belum ada produk di affiliate_products.
                </div>
                <div style="color:#9aaebe; font-size:11px; margin-top:4px;">
                    Kirim link registrasi ke brand, setelah brand registrasi akan otomatis pindah ke Task 3.
                </div>
            </div>
        `;
    }
    
    modalTitleElem.innerHTML = `<i class="fas fa-handshake"></i> Follow Up & Deal Closing - ${brand.name}`;
    modalBodyElem.innerHTML = `
        <div style="background:rgba(139,92,246,0.1); border-radius:14px; padding:12px; margin-bottom:16px;">
            <p style="color:#8b5cf6; font-size:12px;"><i class="fas fa-info-circle"></i> Pilih campaign dan konfirmasi deal closing dengan brand</p>
        </div>
        
        ${campaignOptionsHtml}
        ${registrationStatusHtml}
        
        <div style="background:#0f1420; border-radius:12px; padding:12px; margin-bottom:16px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                <span style="color:#9aaebe;">Kategori:</span> <span>${escapeHtml(brand.category || '-')}</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                <span style="color:#9aaebe;">WhatsApp:</span> <span>${escapeHtml(brand.whatsapp_number || '-')}</span>
            </div>
            <div style="display:flex; justify-content:space-between;">
                <span style="color:#9aaebe;">Open Plan Komisi:</span> <span style="color:#4ade80; font-weight:600;">${openCommission}%</span>
            </div>
        </div>
        
         <label><i class="fas fa-percent"></i> Range Komisi yang Ditawarkan *</label>
        <div style="background:#1a1f2e; border-radius:10px; padding:8px 12px; margin-bottom:16px;">
            <div style="font-size:11px; color:#fbbf24; margin-bottom:6px;">
                <i class="fas fa-chart-line"></i> Rekomendasi: ${recommendedMin}% - ${recommendedMax}%
                <span style="font-size:9px; color:#9aaebe; margin-left:8px;">(max bisa diubah)</span>
            </div>
            <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                <!-- 🔥 MIN: Rekomendasi Min (tidak bisa diubah) -->
                <div style="display:flex; align-items:center; gap:4px;">
                    <span style="color:#9aaebe; font-size:12px;">Min:</span>
                    <span style="color:#4ade80; font-weight:700; font-size:16px;">${recommendedMin}%</span>
                </div>
                
                <span style="color:#2a3346;">—</span>
                
                <!-- 🔥 MAX: Bisa diubah dengan slider -->
                <div style="flex:1; display:flex; align-items:center; gap:8px;">
                    <span style="color:#9aaebe; font-size:12px;">Max:</span>
                    <input type="range" id="commissionSlider" min="${sliderMin}" max="${sliderMax}" step="0.5" value="${maxCommission}" 
                           style="flex:1; height:6px; border-radius:3px; background:linear-gradient(90deg, #4ade80, #8b5cf6);">
                    <input type="number" id="commissionValue" value="${maxCommission}" step="0.5" min="${sliderMin}"
                           style="width:90px; padding:6px 8px; background:#0f1420; border:1px solid #2a3346; border-radius:8px; color:#e2f0e8; text-align:center;">
                    <span style="color:#4ade80; font-weight:600;">%</span>
                </div>
            </div>
            <div style="margin-top:8px; display:flex; justify-content:space-between; font-size:10px; color:#9aaebe;">
                <span>Min: ${recommendedMin}%</span>
                <span>Rekomendasi: ${recommendedMin}% - ${recommendedMax}%</span>
                <span>Max: <span id="currentMaxDisplay">${maxCommission}</span>%</span>
            </div>
        </div>
        
        
        <label><i class="fas fa-file-alt"></i> Pesan Konfirmasi Deal</label>
        <textarea id="dealConfirmationMessage" rows="10" style="width:100%; padding:10px; background:#0f1420; border:1px solid #2a3346; border-radius:12px; color:#e2f0e8; font-size:12px; font-family:monospace;">Okay kak, bisa langsung regist ke campaign TAP kita ya kak.

Berikut detail campaign-nya:

 *Range Komisi:* ${currentCommission}% - ${recommendedMax}%
 *Campaign Name:* [Pilih campaign di atas]
 *Campaign ID:* [Akan terisi otomatis]
 *Link Campaign:* [Akan terisi otomatis]

Setelah regist, produk akan masuk ke approval dan kita lanjut ke tahap berikutnya.

Terima kasih! </textarea>
        
        <div class="flex-buttons" style="margin-top:20px;">
            <button id="confirmDealBtn" style="flex:1; background:#10b981; color:white; font-weight:600; padding:12px; border-radius:40px; border:none; cursor:pointer;" ${availableCampaigns.length === 0 ? 'disabled' : ''}>
                <i class="fas fa-check-circle"></i> Konfirmasi Deal & Kirim WA
            </button>
            <button id="cancelFollowUpBtn" style="flex:1; background:#1e293b; color:#cbd5e6; padding:12px; border-radius:40px; border:1px solid #2a3346; cursor:pointer;">
                Batal
            </button>
        </div>
        <div style="margin-top:8px; font-size:10px; color:#9aaebe; text-align:center;">
            <i class="fas fa-info-circle"></i> Pilih campaign terlebih dahulu, lalu konfirmasi deal.
            Setelah brand registrasi, akan otomatis pindah ke Task 3.
        </div>
    `;
    
    openModalDashboard();
    
    // 🔥 CAMPAIGN SELECT HANDLER
    const campaignSelect = document.getElementById('campaignSelect');
    const messageTextarea = document.getElementById('dealConfirmationMessage');
    const commissionSlider = document.getElementById('commissionSlider');
    const commissionValue = document.getElementById('commissionValue');
    const increaseBtn = document.getElementById('increaseCommissionBtn');
    const decreaseBtn = document.getElementById('decreaseCommissionBtn');
    
    // 🔥 UPDATE PESAN BERDASARKAN CAMPAIGN & KOMISI
function updateDealMessage() {
    const selectedOption = campaignSelect?.options[campaignSelect.selectedIndex];
    const campaignId = campaignSelect?.value || '';
    const campaignName = selectedOption?.getAttribute('data-campaign-name') || '[Pilih campaign di atas]';
    const campaignLink = selectedOption?.getAttribute('data-campaign-link') || `https://partner.tiktokshop.com/campaign/${campaignId}`;
    
    // 🔥 AMBIL NILAI MAX DARI SLIDER / INPUT
    const maxVal = document.getElementById('commissionValue')?.value || recommendedMax;
    const minVal = recommendedMin;
    
    // 🔥 PASTIKAN MIN < MAX
    const finalMin = Math.min(minVal, maxVal);
    const finalMax = Math.max(minVal, maxVal);
    
    let currentMessage = `Okay kak, bisa langsung regist ke campaign TAP kita ya kak.

Berikut detail campaign-nya:

 *Range Komisi:* ${finalMin}% - ${finalMax}%
 *Campaign Name:* ${campaignName}
 *Campaign ID:* ${campaignId}
 *Link Campaign:* ${campaignLink}

Setelah regist, produk akan masuk ke approval dan kita lanjut ke tahap berikutnya.

Terima kasih! `;
    
    messageTextarea.value = currentMessage;
    
    // Update display max
    const maxDisplay = document.getElementById('currentMaxDisplay');
    if (maxDisplay) maxDisplay.innerText = finalMax;
}

    // 🔥 CAMPAIGN CHANGE
    if (campaignSelect) {
        campaignSelect.addEventListener('change', updateDealMessage);
    }
    
    // 🔥 COMMISSION SLIDER
    if (commissionSlider && commissionValue) {
        commissionSlider.addEventListener('input', function() {
            commissionValue.value = this.value;
            updateDealMessage();
        });
        
        commissionValue.addEventListener('input', function() {
            let val = parseFloat(this.value);
            if (isNaN(val)) val = sliderMin;
            if (val < sliderMin) val = sliderMin;
            if (val > sliderMax) {
                // User bisa input lebih dari max slider
                // Update slider ke max, tapi value tetap
                commissionSlider.value = sliderMax;
            } else {
                commissionSlider.value = val;
            }
            updateDealMessage();
        });
        
        // Tombol + dan -
        if (increaseBtn) {
            increaseBtn.addEventListener('click', function() {
                let val = parseFloat(commissionValue.value) || sliderMin;
                val += 0.5;
                commissionValue.value = val;
                if (val <= sliderMax) {
                    commissionSlider.value = val;
                } else {
                    commissionSlider.value = sliderMax;
                }
                updateDealMessage();
            });
        }
        
        if (decreaseBtn) {
            decreaseBtn.addEventListener('click', function() {
                let val = parseFloat(commissionValue.value) || sliderMin;
                val -= 0.5;
                if (val < sliderMin) val = sliderMin;
                commissionValue.value = val;
                commissionSlider.value = val;
                updateDealMessage();
            });
        }
    }
    
    // 🔥 TOMBOL KONFIRMASI DEAL
    const confirmBtn = document.getElementById('confirmDealBtn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', async () => {
            const commission = document.getElementById('commissionValue').value;
            const campaignId = document.getElementById('campaignSelect')?.value;
            const message = document.getElementById('dealConfirmationMessage').value;
            const phoneNumber = brand.whatsapp_number;
            
            if (!campaignId) {
                showToastInModal('Pilih campaign terlebih dahulu!', 'error');
                return;
            }
            
            if (!phoneNumber) {
                showToastInModal('Nomor WhatsApp tidak tersedia!', 'error');
                return;
            }
            
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Memproses...';
            
            // 🔥 SAVE FOLLOW UP
            const saveResponse = await fetch(baseUrlDashboard + 'bd/save_follow_up', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    brand_id: brandId,
                    commission_min: commission,
                    commission_max: commission,
                    campaign_id: campaignId,
                    notes: message
                })
            });
            
            const saveResult = await saveResponse.json();
            
            if (saveResult.success) {
                // 🔥 BUKA WHATSAPP
                sendWhatsAppDealDashboard(brandId, phoneNumber, message);
                
                showToastInModal(saveResult.message, saveResult.has_products ? 'success' : 'warning');
                closeModalDashboard();
                
                if (saveResult.has_products) {
                    setTimeout(() => location.reload(), 2000);
                } else {
                    setTimeout(() => location.reload(), 3000);
                }
            } else {
                showToastInModal(saveResult.message, 'error');
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fas fa-check-circle"></i> Konfirmasi Deal & Kirim WA';
            }
        });
    }
    
    const cancelBtn = document.getElementById('cancelFollowUpBtn');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeModalDashboard);
    }
}


// ========== TASK 3: SETUP CAMPAIGN (LANDSCAPE MODAL) ==========
let approvedProductsList = [];
let selectedRecommendations = [];
let currentBrandIdForSetup = null;
let currentBrandNameForSetup = null;
let currentCampaignIdForSetup = null;
let currentCampaignNameForSetup = null;
let requirementsFilled = false;

async function showTask3SetupModalWithRecommendations(brandId, brandName) {
    currentBrandIdForSetup = brandId;
    currentBrandNameForSetup = brandName;
    approvedProductsList = [];
    selectedRecommendations = [];
    currentCampaignIdForSetup = null;
    currentCampaignNameForSetup = null;
    
    // 🔥 AMBIL DATA REQUIREMENT BRAND
    const brandReqResponse = await fetch(baseUrlDashboard + 'bd/get_brand_requirements', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ brand_id: brandId })
    });
    const brandReqResult = await brandReqResponse.json();
    
    const existingRequirements = brandReqResult.success ? brandReqResult.data : null;
    const isSupervisor = document.getElementById('isSupervisorHidden')?.value === 'true';
    
    // 🔥 CEK APAKAH REQUIREMENT SUDAH TERISI
    const hasRequirements = existingRequirements && 
        existingRequirements.creator_level && 
        existingRequirements.creator_gmv > 0 && 
        existingRequirements.content_type;
    
    // 🔥 CEK APAKAH BRAND SUDAH AKTIF
    const isActiveBrand = document.querySelector(`.brand-item-dashboard[data-brand-id="${brandId}"]`)?.getAttribute('data-is-active') === 'true';
    
    // 🔥 JIKA BRAND AKTIF DAN SUDAH PUNYA REQUIREMENT, AUTO-FILL
    let requirementsFilled = hasRequirements;
    
    // 🔥 AMBIL PRODUK PENDING
    const response = await fetch(baseUrlDashboard + 'bd/get_pending_products_with_recommendations', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ brand_id: brandId, brand_name: brandName })
    });
    
    const result = await response.json();
    
    if (!result.success) {
        showToastInModal('Gagal mengambil data produk', 'error');
        return;
    }
    
    const pendingProducts = result.pending_products || [];
    const recommendations = result.recommendations || [];
    const hasApproved = result.has_approved;
    
    // Ambil campaign_id dan campaign_name dari produk pertama
    if (pendingProducts.length > 0 && pendingProducts[0].campaign_id) {
        currentCampaignIdForSetup = pendingProducts[0].campaign_id;
        currentCampaignNameForSetup = pendingProducts[0].campaign_name || 'Campaign';
    }
      // 馃敟 CLAIM BRAND UI
    let claimHtml = '';
    let disableApproval = false;
    
    if (result.brand_status === 'NEED_CLAIM' || (result.owner_id === null && result.can_claim !== undefined && result.brand_status === 'NEED_CLAIM')) {
        if (result.can_claim) {
            // BA yang pernah kontak, tapi belum claim
            claimHtml = `
                <div style="background:rgba(239,68,68,0.1); border-radius:12px; padding:16px; margin-bottom:16px; border:1px solid #ef4444; display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <div style="color:#ef4444; font-size:14px; font-weight:bold; margin-bottom:4px;">
                            <i class="fas fa-hand-paper"></i> Brand Ini Membutuhkan Claim!
                        </div>
                        <div style="color:#9aaebe; font-size:12px;">
                            Sistem mendeteksi bahwa brand ini dihubungi oleh lebih dari 1 BA. Silakan claim jika ini brand Anda.
                        </div>
                        <div style="margin-top: 8px; font-size: 11px; color: #ef4444;">
                            <strong>Dihubungi oleh BA:</strong> ${result.contacted_bas ? result.contacted_bas.join(', ') : '-'}
                        </div>
                    </div>
                    <div>
                        <button id="claimBrandBtn" data-brand-id="${brandId}" style="background:#ef4444; color:white; padding:10px 20px; border-radius:40px; border:none; cursor:pointer; font-weight:bold;">
                            <i class="fas fa-lock"></i> Claim Brand
                        </button>
                    </div>
                </div>
            `;
            disableApproval = true;
        } else if (isSupervisor) {
            // Head BA (Admin) melihat warning
            claimHtml = `
                <div style="background:rgba(245,158,11,0.1); border-radius:12px; padding:16px; margin-bottom:16px; border:1px solid #f59e0b;">
                    <div style="color:#fbbf24; font-size:14px; font-weight:bold; margin-bottom:4px;">
                        <i class="fas fa-exclamation-triangle"></i> Menunggu Claim Ownership
                    </div>
                    <div style="color:#9aaebe; font-size:12px;">
                        Brand ini sedang diperebutkan oleh lebih dari 1 BA. Menunggu BA terkait untuk melakukan Claim. Proses Setup Campaign dikunci sementara.
                    </div>
                    <div style="margin-top: 8px; font-size: 11px; color: #fbbf24;">
                        <strong>Dihubungi oleh BA:</strong> ${result.contacted_bas ? result.contacted_bas.join(', ') : '-'}
                    </div>
                </div>
            `;
            disableApproval = true;
        }
    }
    
    // 馃敟 BUILD CAMPAIGN INFO HTML
    let campaignInfoHtml = '';
    if (currentCampaignIdForSetup) {
        campaignInfoHtml = `
            <div class="campaign-info-bar" style="background: linear-gradient(135deg, #1a1030, #13111f); border-radius: 16px; padding: 14px 20px; margin-bottom: 20px; border: 1px solid #8b5cf6;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <span style="color: #9aaebe; font-size: 11px;"><i class="fas fa-bullhorn"></i> Campaign</span>
                        <div style="color: #e2f0e8; font-size: 16px; font-weight: 700;">${escapeHtml(currentCampaignNameForSetup)}</div>
                        <div style="color: #8b5cf6; font-size: 11px; font-family: monospace;">ID: ${escapeHtml(currentCampaignIdForSetup)}</div>
                    </div>
                    <div>
                        <span style="color: #9aaebe; font-size: 11px;"><i class="fas fa-store"></i> Brand</span>
                        <div style="color: #4ade80; font-size: 16px; font-weight: 700;">${escapeHtml(brandName)}</div>
                        ${isActiveBrand ? `<div style="color: #10b981; font-size: 11px;"><i class="fas fa-check-circle"></i> ACTIVE - Produk Baru</div>` : ''}
                    </div>
                </div>
            </div>
        `;
    }
    
    const modalTitleElem = document.getElementById('modalTitleDashboard');
    const modalBodyElem = document.getElementById('modalBodyDashboard');
    
    // Ubah modal menjadi LANDSCAPE (lebar lebih besar)
    const modalGlass = document.querySelector('.modal-glass-dashboard');
    if (modalGlass) {
        modalGlass.style.maxWidth = '1200px';
        modalGlass.style.width = '95%';
    }
    
    modalTitleElem.innerHTML = `<i class="fas fa-rocket"></i> Setup Campaign - ${escapeHtml(brandName)}`;
    
    // 🔥 BUILD REQUIREMENT FORM HTML
    let requirementFormHtml = '';
    let requirementStatusHtml = '';
    
    // 🔥 JIKA BRAND AKTIF DAN SUDAH PUNYA REQUIREMENT → AUTO-FILL
    if (isActiveBrand && hasRequirements) {
        requirementFormHtml = `
            <div style="background:#1a1f2e; border-radius:12px; padding:16px; margin-bottom:16px; border:1px solid #4ade80;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <h4 style="color:#4ade80; margin:0; font-size:13px;">
                        <i class="fas fa-history"></i> Requirement Brand (Auto-fill dari sebelumnya)
                    </h4>
                    <span style="font-size:10px; padding:2px 8px; border-radius:20px; background:rgba(74,222,128,0.15); color:#4ade80;">
                        <i class="fas fa-check-circle"></i> Terisi otomatis
                    </span>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                    <div>
                        <label style="font-size:11px; color:#9aaebe;">Creator Level:</label>
                        <div style="color:#e2f0e8; padding:6px 0;">${getLevelLabel(existingRequirements?.creator_level)}</div>
                    </div>
                    <div>
                        <label style="font-size:11px; color:#9aaebe;">Creator GMV:</label>
                        <div style="color:#4ade80; padding:6px 0;">Rp ${formatNumber(existingRequirements?.creator_gmv)}</div>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                    <div>
                        <label style="font-size:11px; color:#9aaebe;">Type Content:</label>
                        <div style="color:#e2f0e8; padding:6px 0;">${getContentTypeLabel(existingRequirements?.content_type)}</div>
                    </div>
                    <div>
                        <label style="font-size:11px; color:#9aaebe;">Sample Method:</label>
                        <div style="color:#e2f0e8; padding:6px 0;">${existingRequirements?.sample_method === 'manual' ? 'Manual' : 'TAP'}</div>
                    </div>
                </div>
                ${existingRequirements?.campaign_notes ? `
                <div>
                    <label style="font-size:11px; color:#9aaebe;">Notes:</label>
                    <div style="color:#9aaebe; padding:6px 0; font-size:11px;">${escapeHtml(existingRequirements.campaign_notes)}</div>
                </div>
                ` : ''}
                <div style="margin-top:12px; padding:8px; background:rgba(74,222,128,0.05); border-radius:8px; border-left: 3px solid #4ade80;">
                    <span style="font-size:10px; color:#4ade80;">
                        <i class="fas fa-info-circle"></i> Requirement diambil dari data sebelumnya (brand sudah aktif)
                    </span>
                </div>
            </div>
        `;
        requirementsFilled = true;
    } 
    // 🔥 JIKA SUPERVISOR DAN REQUIREMENT SUDAH DIISI
    else if (isSupervisor && hasRequirements) {
        requirementFormHtml = `
            <div style="background:#1a1f2e; border-radius:12px; padding:16px; margin-bottom:16px; border:1px solid #4ade80;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <h4 style="color:#4ade80; margin:0; font-size:13px;">
                        <i class="fas fa-clipboard-list"></i> Requirement Brand (Sudah Diisi)
                    </h4>
                    <span style="font-size:10px; padding:2px 8px; border-radius:20px; background:rgba(74,222,128,0.15); color:#4ade80;">
                        Diisi oleh Staff
                    </span>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                    <div>
                        <label style="font-size:11px; color:#9aaebe;">Creator Level:</label>
                        <div style="color:#e2f0e8; padding:6px 0;">${getLevelLabel(existingRequirements?.creator_level)}</div>
                    </div>
                    <div>
                        <label style="font-size:11px; color:#9aaebe;">Creator GMV:</label>
                        <div style="color:#4ade80; padding:6px 0;">Rp ${formatNumber(existingRequirements?.creator_gmv)}</div>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                    <div>
                        <label style="font-size:11px; color:#9aaebe;">Type Content:</label>
                        <div style="color:#e2f0e8; padding:6px 0;">${getContentTypeLabel(existingRequirements?.content_type)}</div>
                    </div>
                    <div>
                        <label style="font-size:11px; color:#9aaebe;">Sample Method:</label>
                        <div style="color:#e2f0e8; padding:6px 0;">${existingRequirements?.sample_method === 'manual' ? 'Manual' : 'TAP'}</div>
                    </div>
                </div>
                ${existingRequirements?.campaign_notes ? `
                <div>
                    <label style="font-size:11px; color:#9aaebe;">Notes:</label>
                    <div style="color:#9aaebe; padding:6px 0; font-size:11px;">${escapeHtml(existingRequirements.campaign_notes)}</div>
                </div>
                ` : ''}
            </div>
        `;
        requirementsFilled = true;
    } 
    // 🔥 JIKA BUKAN SUPERVISOR DAN REQUIREMENT BELUM DIISI
    else if (!isSupervisor && !hasRequirements) {
        requirementFormHtml = `
            <div id="requirementSection" style="background:#1a1f2e; border-radius:12px; padding:16px; margin-bottom:16px; border:1px solid #8b5cf6;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <h4 style="color:#8b5cf6; margin:0; font-size:13px;">
                        <i class="fas fa-clipboard-list"></i> Requirement Brand (Wajib Diisi)
                    </h4>
                    <span id="requirementStatus" style="font-size:10px; padding:2px 8px; border-radius:20px; background:rgba(245,158,11,0.15); color:#f59e0b;">
                        ⚠ Belum diisi
                    </span>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                    <div>
                        <label style="font-size:11px; color:#9aaebe;">Creator Level *</label>
                        <select id="creatorLevel" style="width:100%; padding:8px; background:#0f1420; border:1px solid #2a3346; border-radius:8px; color:#e2f0e8;">
                            <option value="">-- Pilih Level --</option>
                            <option value="1">LEVEL 1</option>
                            <option value="2">LEVEL 2</option>
                            <option value="3">LEVEL 3</option>
                            <option value="4">LEVEL 4</option>
                            <option value="5">LEVEL 5</option>
                            <option value="6">LEVEL 6</option>
                            <option value="7">LEVEL 7</option>
                            <option value="8">LEVEL 8</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:11px; color:#9aaebe;">Creator GMV (Rp) *</label>
                        <input type="number" id="creatorGmv" value="${existingRequirements?.creator_gmv || ''}" placeholder="Minimal GMV" style="width:100%; padding:8px; background:#0f1420; border:1px solid #2a3346; border-radius:8px; color:#e2f0e8;">
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                    <div>
                        <label style="font-size:11px; color:#9aaebe;">Type Content *</label>
                        <select id="contentType" style="width:100%; padding:8px; background:#0f1420; border:1px solid #2a3346; border-radius:8px; color:#e2f0e8;">
                            <option value="">-- Pilih Type --</option>
                            <option value="LS">LS (Live Streaming)</option>
                            <option value="SV">SV (Short Video)</option>
                            <option value="BOTH">BOTH (LS + SV)</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:11px; color:#9aaebe;">Sample Method *</label>
                        <select id="sampleMethod" style="width:100%; padding:8px; background:#0f1420; border:1px solid #2a3346; border-radius:8px; color:#e2f0e8;">
                            <option value="">-- Pilih Method --</option>
                            <option value="manual">Manual (CA input di gsheet brand)</option>
                            <option value="auto">TAP (creator request by tiktok)</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label style="font-size:11px; color:#9aaebe;">Notes (Opsional)</label>
                    <textarea id="campaignNotes" rows="2" placeholder="Catatan tambahan untuk campaign..." style="width:100%; padding:8px; background:#0f1420; border:1px solid #2a3346; border-radius:8px; color:#e2f0e8;">${escapeHtml(existingRequirements?.campaign_notes || '')}</textarea>
                </div>
                <div class="flex-buttons" style="margin-top:12px;">
                    <button id="saveRequirementsBtn" style="background:#8b5cf6; color:white; flex:1; padding:12px; border-radius:40px; border:none; cursor:pointer; font-weight:600;">
                        <i class="fas fa-save"></i> Simpan Requirement
                    </button>
                </div>
            </div>
        `;
        requirementsFilled = false;
        
        requirementStatusHtml = `
            <div style="background:rgba(245,158,11,0.1); border-radius:12px; padding:12px; margin-bottom:16px; border-left:3px solid #f59e0b;">
                <div style="color:#fbbf24; font-size:12px; margin-bottom:4px;">
                    <i class="fas fa-exclamation-triangle"></i> Requirement Brand Belum Diisi!
                </div>
                <div style="color:#9aaebe; font-size:11px;">
                    Silakan isi requirement brand terlebih dahulu sebelum dapat melakukan approve produk.
                </div>
            </div>
        `;
    }
    // 🔥 JIKA SUPERVISOR TAPI REQUIREMENT BELUM DIISI
    else if (isSupervisor && !hasRequirements) {
        requirementFormHtml = `
            <div id="requirementSection" style="background:rgba(245,158,11,0.1); border-radius:12px; padding:16px; margin-bottom:16px; border:1px solid #f59e0b;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <h4 style="color:#fbbf24; margin:0; font-size:13px;">
                        <i class="fas fa-exclamation-triangle"></i> Requirement Brand (Belum Diisi)
                    </h4>
                    <span style="font-size:10px; padding:2px 8px; border-radius:20px; background:rgba(245,158,11,0.15); color:#fbbf24;">
                        Menunggu Staff
                    </span>
                </div>
                <div style="background:rgba(0,0,0,0.3); border-radius:8px; padding:12px; margin-bottom:12px;">
                    <p style="color:#9aaebe; font-size:11px; margin-bottom:8px;">
                        <i class="fas fa-info-circle"></i> Staff BA belum mengisi requirement brand. Silakan minta staff mengisi requirement terlebih dahulu.
                    </p>
                </div>
            </div>
        `;
        requirementsFilled = false;
    }
    
    // 🔥 APPROVE SECTION (hanya untuk supervisor)
    let approveSection = '';
    let linkSection = '';
    
    if (isSupervisor && requirementsFilled) {
        approveSection = `
            <div id="approveSection" style="background:#1a1f2e; border-radius:12px; padding:12px; margin-bottom:16px;">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:10px;">
                    <div>
                        <span id="selectedCountBadge" style="background:#4ade80; color:#0a0e17; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600;">0</span>
                        <span style="margin-left:8px; color:#9aaebe;">produk dipilih</span>
                    </div>
                    ${isActiveBrand ? `
                    <div style="display:flex; gap:8px;">
                        <span style="font-size:10px; color:#10b981;">
                            <i class="fas fa-check-circle"></i> Brand aktif - Approve produk baru
                        </span>
                    </div>
                    ` : ''}
                    <div style="display:flex; gap:8px;">
                        <button id="selectAllProductsBtn" class="btn-secondary" style="background:#1e293b; color:#cbd5e6; border:1px solid #2a3346; padding:6px 12px; border-radius:20px; font-size:11px; cursor:pointer;">
                            <i class="fas fa-check-double"></i> Select All
                        </button>
                        <button id="deselectAllProductsBtn" class="btn-secondary" style="background:#1e293b; color:#cbd5e6; border:1px solid #2a3346; padding:6px 12px; border-radius:20px; font-size:11px; cursor:pointer;">
                            <i class="fas fa-times"></i> Deselect All
                        </button>
                        <button id="approveSelectedBtn" class="btn-approve" disabled>
                            <i class="fas fa-check-circle"></i> Approve Selected (0)
                        </button>
                        
                            <button id="rejectSelectedBtn" class="btn-reject" disabled>
                            <i class="fas fa-times-circle"></i> Reject Selected (0)
                          </button>
                    </div>
                </div>
            </div>
        `;
        
        linkSection = `
            <div id="linkSection" style="display:none; background:#1a1f2e; border-radius:12px; padding:12px; margin-top:16px;">
                <div style="margin-bottom:12px;">
                    <h4 style="color:#4ade80; font-size:13px; margin-bottom:8px;"><i class="fas fa-link"></i> Generate Link Afiliasi</h4>
                    <p style="font-size:10px; color:#9aaebe;">Produk yang sudah diapprove: <span id="approvedCount">0</span></p>
                </div>
                <div style="display:flex; gap:12px; flex-wrap:wrap;">
                    <button id="generateSingleLinkBtn" class="btn-single-link">
                        <i class="fas fa-link"></i> Single Link
                    </button>
                    <button id="generateMultiLinkBtn" class="btn-multi-link">
                        <i class="fas fa-layer-group"></i> Multi Link
                    </button>
                </div>
                <div id="linkResult" style="display:none; margin-top:16px; padding:12px; background:#0f1420; border-radius:10px;">
                    <div id="linkResultContent"></div>
                </div>
            </div>
        `;
    }
    
    // 🔥 BUILD PRODUCT CARD HTML
    let productsHtml = '';
    if (pendingProducts.length > 0) {
        productsHtml = `
            <div style="margin-top: 16px;">
                <h4 style="color: #4ade80; font-size: 14px; margin-bottom: 16px;">
                    <i class="fas fa-box"></i> Daftar Produk (${pendingProducts.length})
                    ${isActiveBrand ? `<span style="font-size: 11px; color: #10b981; margin-left: 8px;">(Produk baru dari brand aktif)</span>` : ''}
                </h4>
                <div style="display: flex; flex-direction: column; gap: 16px;" id="productsGridContainer">
        `;
        
        pendingProducts.forEach((product, index) => {
            let totalCommissionRate = product.total_commission_rate || 0;
            let openCommissionRate = product.open_commission_rate || 0;
            let shopAdsRate = product.shop_ads_rate || 0;
            
            let priceDisplay = '';
            const lowestPrice = product.lowest_price || product.price;
            const highestPrice = product.highest_price || product.price;
            
            if (lowestPrice && highestPrice && lowestPrice !== highestPrice) {
                priceDisplay = `Rp ${formatNumber(lowestPrice)} - Rp ${formatNumber(highestPrice)}`;
            } else if (lowestPrice) {
                priceDisplay = `Rp ${formatNumber(lowestPrice)}`;
            } else {
                priceDisplay = '-';
            }
            
            const isNew = product.is_new === true;
            const newBadge = isNew ? '<span style="background: #fbbf24; color: #0a0e17; font-size: 9px; padding: 2px 8px; border-radius: 12px; margin-left: 8px;">[New]</span>' : '';
            
            const productImage = product.image_url || '';
            const hasImage = productImage && productImage !== '';
            
            productsHtml += `
                <div class="product-card-landscape" data-product-id="${product.product_id}" data-campaign-id="${product.campaign_id}" 
                     style="background: #0f1420; border-radius: 16px; border: 1px solid #2a3346; overflow: hidden;">
                    
                    <!-- Header Produk -->
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: #1a1f2e; border-bottom: 1px solid #2a3346;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            ${isSupervisor && requirementsFilled && !disableApproval ? `
                            <input type="checkbox" class="product-checkbox" 
                                   data-product-id="${product.product_id}" 
                                   data-campaign-id="${product.campaign_id}"
                                   data-product-name="${escapeHtml(product.product_name)}"
                                   data-open-commission="${openCommissionRate}"
                                   style="width: 20px; height: 20px; cursor: pointer; accent-color: #4ade80;">
                            ` : ''}
                            <div>
                                <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 6px;">
                                    <strong style="color: #e2f0e8; font-size: 14px;">${escapeHtml(product.product_name.substring(0, 50))}${product.product_name.length > 50 ? '...' : ''}</strong>
                                    ${newBadge}
                                </div>
                                <div style="color: #9aaebe; font-size: 10px; margin-top: 4px;">
                                    <i class="fas fa-fingerprint"></i> ID: ${escapeHtml(product.product_id)}
                                </div>
                            </div>
                        </div>
                        
                        ${isSupervisor && requirementsFilled && !disableApproval ? `
                        <div style="display: flex; gap: 8px;">
                            <button class="approve-single-btn" data-product-id="${product.product_id}" data-campaign-id="${product.campaign_id}" 
                                    data-product-name="${escapeHtml(product.product_name)}" data-open-commission="${openCommissionRate}"
                                    style="background: #10b981; color: white; border: none; padding: 6px 20px; border-radius: 40px; cursor: pointer; font-size: 12px; font-weight: 600;">
                                <i class="fas fa-check-circle"></i> Setujui
                            </button>
                            <button class="reject-single-btn" data-product-id="${product.product_id}" data-campaign-id="${product.campaign_id}"
                                    style="background: #ef4444; color: white; border: none; padding: 6px 20px; border-radius: 40px; cursor: pointer; font-size: 12px; font-weight: 600;">
                                <i class="fas fa-times-circle"></i> Tolak
                            </button>
                        </div>
                        ` : `
                        <div style="background: ${disableApproval ? 'rgba(239,68,68,0.15)' : 'rgba(245,158,11,0.15)'}; padding: 6px 12px; border-radius: 20px;">
                            <span style="color: ${disableApproval ? '#ef4444' : '#f59e0b'}; font-size: 11px;">
                                <i class="fas fa-lock"></i> ${disableApproval ? 'Approve terkunci, butuh claim kepemilikan' : 'Approve terkunci, isi requirement dulu'}
                            </span>
                        </div>
                        `}
                    </div>
                    
                    <!-- Body Produk - Grid dengan Foto -->
                    <div style="display: grid; grid-template-columns: 100px 1fr 1fr 1fr 1fr; gap: 12px; padding: 16px;">
                        <!-- Kolom Foto -->
                        <div style="background: #0a0e1a; border-radius: 12px; padding: 8px; display: flex; align-items: center; justify-content: center;">
                            ${hasImage ? 
                                `<img src="${escapeHtml(productImage)}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;" onerror="this.src=''; this.onerror=null; this.parentElement.innerHTML='<i class=\\'fas fa-box fa-2x\\' style=\\'color:#4ade80;\\'></i>'">` : 
                                `<i class="fas fa-box fa-2x" style="color: #4ade80;"></i>`
                            }
                        </div>
                        
                        <!-- Kolom 1: Total persentase komisi -->
                        <div style="background: #0a0e1a; border-radius: 12px; padding: 10px;">
                            <div style="color: #9aaebe; font-size: 10px; margin-bottom: 4px;">Total persentase komisi</div>
                            <div style="color: #fbbf24; font-size: 18px; font-weight: 700;">${totalCommissionRate.toFixed(1)}%</div>
                            <div style="color: #9aaebe; font-size: 9px; margin-top: 4px;">vs kolaborasi terbuka ${openCommissionRate.toFixed(1)}%</div>
                        </div>
                        
                        <!-- Kolom 3: Harga & Stok -->
                        <div style="background: #0a0e1a; border-radius: 12px; padding: 10px;">
                            <div style="color: #9aaebe; font-size: 10px; margin-bottom: 4px;">Harga jual</div>
                            <div style="color: #4ade80; font-size: 13px; font-weight: 600;">${priceDisplay}</div>
                            <div style="color: #9aaebe; font-size: 10px; margin-top: 8px;">Stok: ${formatNumber(product.inventory || 0)}</div>
                        </div>
                        
                        <!-- Kolom 4: Sample & Terjual -->
                        <div style="background: #0a0e1a; border-radius: 12px; padding: 10px;">
                            <div style="color: #9aaebe; font-size: 10px; margin-bottom: 4px;">Sample tersedia</div>
                            <div style="color: ${(product.sample_quota || 0) > 0 ? '#4ade80' : '#ef4444'}; font-size: 14px; font-weight: 600;">
                                ${(product.sample_quota || 0) > 0 ? product.sample_quota + ' pcs' : 'Tidak tersedia'}
                            </div>
                            <div style="color: #9aaebe; font-size: 10px; margin-top: 8px;">Produk terjual: ${formatNumber(product.product_sales || 0)}</div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        productsHtml += `</div></div>`;
    } else {
        productsHtml = `
            <div style="background:#1a1f2e; border-radius:16px; padding:40px; text-align:center; margin-top:16px;">
                <i class="fas fa-check-circle" style="color:#4ade80; font-size:48px; margin-bottom:16px; display:block;"></i>
                <p style="color:#e2f0e8;">${hasApproved ? 'Semua produk sudah diapprove!' : 'Belum ada pengajuan produk dari brand ini'}</p>
                ${isActiveBrand ? `<p style="color:#10b981; font-size:12px; margin-top:8px;">Brand aktif - Produk baru akan muncul di sini setelah registrasi</p>` : ''}
            </div>
        `;
    }
    
    // 🔥 RENDER MODAL
    // 馃敟 JIKA DISABLE APPROVAL (NEED CLAIM), KUNCI FORM & ACTIONS
    if (disableApproval) {
        requirementFormHtml = '';
        requirementStatusHtml = '';
        linkSection = '';
        approveSection = '';
    }

    modalBodyElem.innerHTML = `
        <div style="background:rgba(74,222,128,0.1); border-radius:14px; padding:12px; margin-bottom:16px;">
            <p style="color:#4ade80; font-size:12px;"><i class="fas fa-info-circle"></i> Setup Campaign - Approve produk untuk generate link afiliasi</p>
            <p style="color:#10b981; font-size:10px;"><i class="fas fa-arrow-right"></i> Komisi Afiliasi = Open Plan + 1% (otomatis)</p>
            ${isActiveBrand ? `<p style="color:#fbbf24; font-size:10px; margin-top:4px;"><i class="fas fa-history"></i> Brand aktif - Requirement auto-fill dari sebelumnya</p>` : ''}
        </div>
        
        ${claimHtml}
        ${campaignInfoHtml}
        ${requirementFormHtml}
        ${requirementStatusHtml}
        ${approveSection}
        ${productsHtml}
        ${linkSection}
        
        <div class="flex-buttons" style="margin-top:20px; display:flex; gap:10px;">
            <button id="closeSetupModalBtn" style="background:#1e293b; color:#cbd5e6; flex:1; padding:12px; border-radius:40px; border:1px solid #2a3346; cursor:pointer;">
                Tutup
            </button>
            <button id="rejectBrandBtn" style="background:#ef4444; color:white; flex:1; padding:12px; border-radius:40px; border:none; cursor:pointer; font-weight:600;">
                <i class="fas fa-times-circle"></i> Tolak Pendaftaran Brand
            </button>
        </div>
    `;
    
    openModalDashboard();
    
    // 🔥 SAVE REQUIREMENT BUTTON
    // 馃敟 CLAIM BRAND BUTTON
    const claimBrandBtn = document.getElementById('claimBrandBtn');
    if (claimBrandBtn) {
        claimBrandBtn.addEventListener('click', async () => {
            if (!confirm('Anda yakin ingin mengklaim brand ini? BA lain tidak akan bisa mengklaimnya setelah Anda.')) return;
            
            claimBrandBtn.disabled = true;
            claimBrandBtn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Claiming...';
            
            try {
                const claimResponse = await fetch(baseUrlDashboard + 'bd/claim_brand', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ brand_id: brandId })
                });
                const claimResult = await claimResponse.json();
                
                if (claimResult.success) {
                    showToastInModal(claimResult.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToastInModal(claimResult.message, 'error');
                    claimBrandBtn.disabled = false;
                    claimBrandBtn.innerHTML = '<i class="fas fa-lock"></i> Claim Brand';
                }
            } catch(e) {
                showToastInModal('Error claiming brand', 'error');
                claimBrandBtn.disabled = false;
                claimBrandBtn.innerHTML = '<i class="fas fa-lock"></i> Claim Brand';
            }
        });
    }

    // 馃敟 SAVE REQUIREMENT BUTTON
    const saveRequirementsBtn = document.getElementById('saveRequirementsBtn');
    if (saveRequirementsBtn) {
        saveRequirementsBtn.addEventListener('click', async () => {
            const creatorLevel = document.getElementById('creatorLevel')?.value;
            const creatorGmv = document.getElementById('creatorGmv')?.value;
            const contentType = document.getElementById('contentType')?.value;
            const sampleMethod = document.getElementById('sampleMethod')?.value;
            const campaignNotes = document.getElementById('campaignNotes')?.value;
            
            if (!creatorLevel || !creatorGmv || !contentType || !sampleMethod) {
                showToastInModal('Semua field requirement wajib diisi!', 'error');
                return;
            }
            
            saveRequirementsBtn.disabled = true;
            saveRequirementsBtn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Menyimpan...';
            
            const saveReqResponse = await fetch(baseUrlDashboard + 'bd/save_brand_requirements', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    brand_id: brandId,
                    creator_level: creatorLevel,
                    creator_gmv: creatorGmv,
                    content_type: contentType,
                    sample_method: sampleMethod,
                    campaign_notes: campaignNotes
                })
            });
            
            const saveReqResult = await saveReqResponse.json();
            
            if (saveReqResult.success) {
                showToastInModal('✅ Requirement berhasil disimpan!', 'success');
                setTimeout(() => {
                    showTask3SetupModalWithRecommendations(brandId, brandName);
                }, 1000);
            } else {
                showToastInModal(saveReqResult.message || 'Gagal menyimpan requirement', 'error');
                saveRequirementsBtn.disabled = false;
                saveRequirementsBtn.innerHTML = '<i class="fas fa-save"></i> Simpan Requirement';
            }
        });
    }
    
    // 🔥 SINGLE APPROVE BUTTON
    document.querySelectorAll('.approve-single-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const productId = btn.getAttribute('data-product-id');
            const campaignId = btn.getAttribute('data-campaign-id');
            const productName = btn.getAttribute('data-product-name');
            const openCommission = parseFloat(btn.getAttribute('data-open-commission'));
            const commissionRate = openCommission + 1;
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i>';
            
           const approveResponse = await fetch(baseUrlDashboard + 'bd/approve_product_with_commission', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({
        campaign_id: campaignId,
        product_id: productId,
        review_result: 'APPROVE',
        commission_rate: commissionRate,
        product_name: productName
    })
});

const approveResult = await approveResponse.json();

if (approveResult.success) {
    showToastInModal(`✅ Produk "${productName}" berhasil diapprove!`, 'success');
    const productCard = btn.closest('.product-card-landscape');
    if (productCard) productCard.remove();
    
    // 🔥 CEK APAKAH SEMUA PRODUK SUDAH APPROVE → PINDAH KE ACTIVE
    const remainingProducts = document.querySelectorAll('.product-card-landscape').length;
    
    if (remainingProducts === 0) {
        // 🔥 PANGGIL ENDPOINT UNTUK CEK DAN PINDAH KE ACTIVE
        try {
            const moveResponse = await fetch(baseUrlDashboard + 'bd/check_and_move_to_active', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ brand_id: brandId, brand_name: brandName })
            });
            const moveResult = await moveResponse.json();
            
            if (moveResult.moved_to_active) {
                showToastInModal('🎉 ' + moveResult.message, 'success');
                // 🔥 TUTUP MODAL DAN REFRESH HALAMAN
                setTimeout(() => {
                    closeModalDashboard();
                    location.reload();
                }, 1500);
                return;
            } else {
                // Masih ada pending atau belum submit
                showToastInModal(moveResult.message, 'info');
            }
        } catch (err) {
            console.error('Error checking move to active:', err);
        }
    }
    
    // Update count
    const setupCountSpan = document.getElementById('setupCountDashboard');
    if (setupCountSpan) {
        const currentCount = parseInt(setupCountSpan.innerText) || 0;
        setupCountSpan.innerText = Math.max(0, currentCount - 1);
    }
    
    // Tampilkan link section
    const linkSection = document.getElementById('linkSection');
    const approvedCountSpan = document.getElementById('approvedCount');
    if (linkSection) linkSection.style.display = 'block';
    if (approvedCountSpan) {
        const currentCount = parseInt(approvedCountSpan.innerText) || 0;
        approvedCountSpan.innerText = currentCount + 1;
    }
    
    approvedProductsList.push({
        product_id: productId,
        product_name: productName,
        affiliate_link: approveResult.affiliate_link,
        commission_rate: commissionRate
    });
            } else {
                showToastInModal(approveResult.message || 'Gagal approve produk', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle"></i> Setujui';
            }
        });
    });
    
    // 🔥 REJECT SINGLE BUTTON
    document.querySelectorAll('.reject-single-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const productId = btn.getAttribute('data-product-id');
            const campaignId = btn.getAttribute('data-campaign-id');
            const productName = btn.closest('.product-card-landscape')?.querySelector('strong')?.innerText || '';
            
            if (!confirm(`Tolak produk "${productName}"?`)) return;
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i>';
            
            const rejectResponse = await fetch(baseUrlDashboard + 'bd/approve_product_with_commission', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    campaign_id: campaignId,
                    product_id: productId,
                    review_result: 'REJECT'
                })
            });
            
            const rejectResult = await rejectResponse.json();
            
            if (rejectResult.success) {
                showToastInModal(`❌ Produk ditolak!`, 'warning');
                const productCard = btn.closest('.product-card-landscape');
                if (productCard) productCard.remove();
            } else {
                showToastInModal(rejectResult.message || 'Gagal tolak produk', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-times-circle"></i> Tolak';
            }
        });
    });
    
    // 🔥 APPROVE SECTION (batch) untuk supervisor
    if (isSupervisor && requirementsFilled) {
        let selectedProducts = [];
        
        function updateSelectedCount() {
            const checkboxes = document.querySelectorAll('.product-checkbox:checked');
            selectedProducts = Array.from(checkboxes).map(cb => ({
                product_id: cb.getAttribute('data-product-id'),
                campaign_id: cb.getAttribute('data-campaign-id'),
                product_name: cb.getAttribute('data-product-name'),
                open_commission: parseFloat(cb.getAttribute('data-open-commission'))
            }));
            const count = selectedProducts.length;
            const countSpan = document.getElementById('selectedCountBadge');
            const approveBtn = document.getElementById('approveSelectedBtn');
            const rejectBtn = document.getElementById('rejectSelectedBtn');
            
            if (countSpan) countSpan.innerText = count;
            if (approveBtn) {
                approveBtn.disabled = count === 0;
                approveBtn.innerHTML = `<i class="fas fa-check-circle"></i> Approve Selected (${count})`;
            }
            if (rejectBtn) {
                rejectBtn.disabled = count === 0;
                rejectBtn.innerHTML = `<i class="fas fa-times-circle"></i> Reject Selected (${count})`;
            }
        }
        
        document.querySelectorAll('.product-checkbox').forEach(cb => {
            cb.addEventListener('change', updateSelectedCount);
        });
        
        const selectAllBtn = document.getElementById('selectAllProductsBtn');
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', () => {
                const allCheckboxes = document.querySelectorAll('.product-checkbox');
                const anyUnchecked = Array.from(allCheckboxes).some(cb => !cb.checked);
                allCheckboxes.forEach(cb => cb.checked = anyUnchecked);
                updateSelectedCount();
            });
        }
        
        const deselectAllBtn = document.getElementById('deselectAllProductsBtn');
        if (deselectAllBtn) {
            deselectAllBtn.addEventListener('click', () => {
                document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = false);
                updateSelectedCount();
            });
        }
        
        const approveSelectedBtn = document.getElementById('approveSelectedBtn');
if (approveSelectedBtn) {
    approveSelectedBtn.addEventListener('click', async () => {
        if (selectedProducts.length === 0) {
            showToastInModal('Pilih produk yang akan diapprove', 'error');
            return;
        }
        
        approveSelectedBtn.disabled = true;
        approveSelectedBtn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Approving...';
        
        let successCount = 0;
        let failCount = 0;
        
        for (const product of selectedProducts) {
            const commissionRate = parseFloat(product.open_commission) + 1;
            
            const approveResponse = await fetch(baseUrlDashboard + 'bd/approve_product_with_commission', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    campaign_id: product.campaign_id,
                    product_id: product.product_id,
                    review_result: 'APPROVE',
                    commission_rate: commissionRate,
                    product_name: product.product_name
                })
            });
            
            const approveResult = await approveResponse.json();
            
            if (approveResult.success) {
                successCount++;
                approvedProductsList.push({
                    product_id: product.product_id,
                    product_name: product.product_name,
                    affiliate_link: approveResult.affiliate_link,
                    commission_rate: commissionRate
                });
                
                const checkbox = document.querySelector(`.product-checkbox[data-product-id="${product.product_id}"]`);
                if (checkbox) {
                    const productCard = checkbox.closest('.product-card-landscape');
                    if (productCard) productCard.remove();
                }
            } else {
                failCount++;
            }
        }
        
        showToastInModal(`✅ ${successCount} produk diapprove, ${failCount} gagal`, successCount > 0 ? 'success' : 'error');
        
        // 🔥 CEK APAKAH SEMUA PRODUK SUDAH APPROVE → PINDAH KE ACTIVE
        const remainingProducts = document.querySelectorAll('.product-card-landscape').length;
        
        if (remainingProducts === 0 && successCount > 0) {
            try {
                const moveResponse = await fetch(baseUrlDashboard + 'bd/check_and_move_to_active', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ brand_id: brandId, brand_name: brandName })
                });
                const moveResult = await moveResponse.json();
                
                if (moveResult.moved_to_active) {
                    showToastInModal('🎉 ' + moveResult.message, 'success');
                    setTimeout(() => {
                        closeModalDashboard();
                        location.reload();
                    }, 1500);
                    return;
                }
            } catch (err) {
                console.error('Error checking move to active:', err);
            }
        }
        
        // Update count
        const setupCountSpan = document.getElementById('setupCountDashboard');
        if (setupCountSpan) {
            const currentCount = parseInt(setupCountSpan.innerText) || 0;
            setupCountSpan.innerText = Math.max(0, currentCount - successCount);
        }
        
        if (successCount > 0) {
            const linkSection = document.getElementById('linkSection');
            const approvedCountSpan = document.getElementById('approvedCount');
            if (linkSection) linkSection.style.display = 'block';
            if (approvedCountSpan) {
                const currentCount = parseInt(approvedCountSpan.innerText) || 0;
                approvedCountSpan.innerText = currentCount + successCount;
            }
        }
        
        approveSelectedBtn.disabled = false;
        approveSelectedBtn.innerHTML = `<i class="fas fa-check-circle"></i> Approve Selected (${selectedProducts.length})`;
        updateSelectedCount();
    });
}


        
        // Generate Single Link
        const generateSingleLinkBtn = document.getElementById('generateSingleLinkBtn');
        if (generateSingleLinkBtn) {
            generateSingleLinkBtn.addEventListener('click', async () => {
                if (approvedProductsList.length === 0) {
                    showToastInModal('Tidak ada produk yang diapprove', 'error');
                    return;
                }
                
                generateSingleLinkBtn.disabled = true;
                generateSingleLinkBtn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Generating...';
                
                let linksHtml = '<div style="background:rgba(74,222,128,0.1); border-radius:10px; padding:12px;">';
                linksHtml += '<p style="color:#4ade80; margin-bottom:12px;"><i class="fas fa-link"></i> Single Link Afiliasi:</p>';
                
                for (const product of approvedProductsList) {
                    const linkResponse = await fetch(baseUrlDashboard + 'bd/generate_bd_affiliate_link', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            campaign_id: currentCampaignIdForSetup,
                            product_id: product.product_id,
                            product_name: product.product_name,
                            open_commission_rate: product.commission_rate - 1
                        })
                    });
                    
                    const linkResult = await linkResponse.json();
                    
                    if (linkResult.success) {
                        linksHtml += `
                            <div style="background:#0f1420; border-radius:8px; padding:10px; margin-bottom:10px;">
                                <div style="font-weight:500; margin-bottom:6px;">📦 ${escapeHtml(product.product_name)}</div>
                                <code style="font-size:10px; word-break:break-all;">${escapeHtml(linkResult.link)}</code>
                                <button class="copy-link-btn" data-link="${escapeHtml(linkResult.link)}" style="margin-top:6px; background:#1e293b; color:#4ade80; border:1px solid #4ade80; padding:4px 12px; border-radius:20px; font-size:10px; cursor:pointer;">
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                            </div>
                        `;
                    }
                }
                
                linksHtml += `<button id="copyAllSingleLinksBtn" style="margin-top:8px; background:#8b5cf6; color:white; padding:6px 12px; border-radius:20px; font-size:11px; cursor:pointer;">
                    <i class="fas fa-copy"></i> Copy Semua Link
                </button></div>`;
                
                const linkResultDiv = document.getElementById('linkResult');
                const linkResultContent = document.getElementById('linkResultContent');
                if (linkResultContent) linkResultContent.innerHTML = linksHtml;
                if (linkResultDiv) linkResultDiv.style.display = 'block';
                
                generateSingleLinkBtn.disabled = false;
                generateSingleLinkBtn.innerHTML = '<i class="fas fa-link"></i> Single Link';
                
                document.querySelectorAll('.copy-link-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        navigator.clipboard.writeText(btn.getAttribute('data-link'));
                        showToastInModal('Link dicopy!', 'success');
                    });
                });
                
                const copyAllBtn = document.getElementById('copyAllSingleLinksBtn');
                if (copyAllBtn) {
                    copyAllBtn.addEventListener('click', () => {
                        let allLinks = '';
                        approvedProductsList.forEach(p => {
                            allLinks += `${p.product_name}\n${p.affiliate_link}\n\n`;
                        });
                        navigator.clipboard.writeText(allLinks);
                        showToastInModal('Semua link dicopy!', 'success');
                    });
                }
            });
        }
    }
    
    // Tombol close
    const closeBtn = document.getElementById('closeSetupModalBtn');
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            if (modalGlass) {
                modalGlass.style.maxWidth = '550px';
            }
            closeModalDashboard();
        });
    }
    
    // Tombol Tolak Pendaftaran Brand
    const rejectBrandBtn = document.getElementById('rejectBrandBtn');
    if (rejectBrandBtn) {
        rejectBrandBtn.addEventListener('click', async () => {
            if (!confirm(`Apakah Anda yakin ingin menolak pendaftaran brand "${brandName}" dan mengembalikannya ke Step 2 (Follow Up)?\n\nSemua produk pending dari brand ini akan dihapus dari daftar pengajuan.`)) {
                return;
            }
            
            rejectBrandBtn.disabled = true;
            rejectBrandBtn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Menolak Brand...';
            
            try {
                const response = await fetch(baseUrlDashboard + 'bd/reject_brand', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ brand_id: brandId, brand_name: brandName })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToastInModal('Brand berhasil ditolak!', 'success');
                    setTimeout(() => {
                        closeModalDashboard();
                        location.reload();
                    }, 1500);
                } else {
                    showToastInModal(result.message || 'Gagal menolak brand', 'error');
                    rejectBrandBtn.disabled = false;
                    rejectBrandBtn.innerHTML = '<i class="fas fa-times-circle"></i> Tolak Pendaftaran Brand';
                }
            } catch (error) {
                console.error('Error rejecting brand:', error);
                showToastInModal('Terjadi kesalahan koneksi', 'error');
                rejectBrandBtn.disabled = false;
                rejectBrandBtn.innerHTML = '<i class="fas fa-times-circle"></i> Tolak Pendaftaran Brand';
            }
        });
    }
}


function getLevelLabel(level) {
    const levels = {
        '1': 'LEVEL 1',
        '2': 'LEVEL 2',
        '3': 'LEVEL 3',
        '4': 'LEVEL 4',
        '5': 'LEVEL 5',
        '6': 'LEVEL 6',
        '7': 'LEVEL 7',
        '8': 'LEVEL 8'
        
        
    };
    return levels[level] || level || '-';
}

function getContentTypeLabel(type) {
    const types = {
        'LS': 'LS (Live Streaming)',
        'SV': 'SV (Short Video)',
        'BOTH': 'BOTH (LS + SV)'
    };
    return types[type] || type || '-';
}
// ========== TASK 4: MONITORING MODAL ==========
async function showTask4MonitoringModalDashboard(brandId, brandName) {
    const today = new Date().toISOString().split('T')[0];
    let currentStartDate = today;
    let currentEndDate = today;
    
    const modalTitleElem = document.getElementById('modalTitleDashboard'); 
    const modalBodyElem = document.getElementById('modalBodyDashboard');
    
    async function loadPerformanceData(startDate, endDate) {
        modalBodyElem.innerHTML = `
            <div style="background:rgba(74,222,128,0.1); border-radius:14px; padding:12px; margin-bottom:16px;">
                <div class="filter-bar" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <p style="color:#4ade80; font-size:12px; margin:0;"><i class="fas fa-chart-simple"></i> Performa Campaign - ${brandName}</p>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <input type="date" id="perfStartDate" value="${startDate}" style="background:#0f1420; border:1px solid #2a3346; border-radius:8px; padding:4px 8px; color:#e2f0e8;">
                        <span style="color:#9aaebe;">s/d</span>
                        <input type="date" id="perfEndDate" value="${endDate}" style="background:#0f1420; border:1px solid #2a3346; border-radius:8px; padding:4px 8px; color:#e2f0e8;">
                        <button id="applyPerfFilter" style="background:#1e293b; border:1px solid #4ade80; color:#4ade80; padding:4px 12px; border-radius:20px; cursor:pointer;"><i class="fas fa-calendar-alt"></i> Filter</button>
                        <button id="resetPerfFilter" style="background:#1e293b; border:1px solid #f59e0b; color:#f59e0b; padding:4px 12px; border-radius:20px; cursor:pointer;"><i class="fas fa-undo-alt"></i> Hari Ini</button>
                    </div>
                </div>
            </div>
            <div id="performanceContent"><div class="loading" style="text-align:center; padding:40px;"><i class="fas fa-spinner fa-pulse fa-2x"></i><p>Loading performance data...</p></div></div>
            <div class="flex-buttons" style="margin-top:16px;"><button id="closeMonitoringBtnDashboard" style="background:#1e293b; color:#cbd5e6; padding:10px; border-radius:40px; cursor:pointer;">Tutup</button></div>
        `;
        openModalDashboard();
        
        try {
            const response = await fetch(baseUrlDashboard + 'bd/get_brand_performance', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ brand_id: brandId, start_date: startDate, end_date: endDate })
            });
            const perfResult = await response.json();
            
            if (perfResult.success) {
                const data = perfResult.data;
                let productsHtml = '';
                if (data.products && data.products.length > 0) {
                    data.products.forEach(p => {
                        productsHtml += `<div style="display:flex; justify-content:space-between; padding:10px; border-bottom:1px solid #2a3346;">
                            <div><div style="color:#e2f0e8; font-size:12px;">${escapeHtml(p.product_name)}</div><div style="color:#9aaebe; font-size:10px;">${p.sales_count || 0} sold</div></div>
                            <div style="color:#4ade80;">Rp ${formatNumber(p.gmv)}</div>
                        </div>`;
                    });
                } else {
                    productsHtml = '<div style="text-align:center; padding:20px; color:#9aaebe;">Belum ada produk terjual</div>';
                }
                
                let creatorsHtml = '';
                if (data.creators && data.creators.length > 0) {
                    data.creators.forEach(c => {
                        creatorsHtml += `<div style="display:flex; justify-content:space-between; padding:10px; border-bottom:1px solid #2a3346;">
                            <div><div style="color:#e2f0e8; font-size:12px;">@${escapeHtml(c.creator_username)}</div><div style="color:#9aaebe; font-size:10px;">${c.total_orders} orders</div></div>
                            <div style="color:#4ade80;">Rp ${formatNumber(c.total_gmv)}</div>
                        </div>`;
                    });
                } else {
                    creatorsHtml = '<div style="text-align:center; padding:20px; color:#9aaebe;">Belum ada creator</div>';
                }
                
                document.getElementById('performanceContent').innerHTML = `
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:20px;">
                        <div style="background:#0f1420; border-radius:12px; padding:12px; text-align:center;"><div style="color:#4ade80; font-size:20px; font-weight:700;">Rp ${formatNumber(data.total_gmv)}</div><div style="color:#9aaebe; font-size:10px;">Total GMV</div></div>
                        <div style="background:#0f1420; border-radius:12px; padding:12px; text-align:center;"><div style="color:#4ade80; font-size:20px; font-weight:700;">${formatNumber(data.total_orders)}</div><div style="color:#9aaebe; font-size:10px;">Total Orders</div></div>
                        <div style="background:#0f1420; border-radius:12px; padding:12px; text-align:center;"><div style="color:#fbbf24; font-size:20px; font-weight:700;">Rp ${formatNumber(data.total_commission)}</div><div style="color:#9aaebe; font-size:10px;">Commission</div></div>
                        <div style="background:#0f1420; border-radius:12px; padding:12px; text-align:center;"><div style="color:#4ade80; font-size:20px; font-weight:700;">${data.roas}x</div><div style="color:#9aaebe; font-size:10px;">ROAS</div></div>
                    </div>
                    <div style="margin-bottom:16px;"><label style="color:#e2f0e8; font-size:12px;"><i class="fas fa-box"></i> Top Products</label><div style="max-height:200px; overflow-y:auto; background:#0f1420; border-radius:12px;">${productsHtml}</div></div>
                    <div><label style="color:#e2f0e8; font-size:12px;"><i class="fas fa-users"></i> Top Creators</label><div style="max-height:150px; overflow-y:auto; background:#0f1420; border-radius:12px;">${creatorsHtml}</div></div>
                `;
            } else {
                document.getElementById('performanceContent').innerHTML = `<div class="empty-state" style="text-align:center; padding:40px; color:#ef4444;"><i class="fas fa-exclamation-triangle"></i><p>Error: ${perfResult.message}</p></div>`;
            }
        } catch (error) {
            document.getElementById('performanceContent').innerHTML = `<div class="empty-state" style="text-align:center; padding:40px; color:#ef4444;"><i class="fas fa-exclamation-triangle"></i><p>Error loading data</p></div>`;
        }
        
        document.getElementById('applyPerfFilter')?.addEventListener('click', () => {
            const newStart = document.getElementById('perfStartDate').value;
            const newEnd = document.getElementById('perfEndDate').value;
            if (newStart && newEnd) loadPerformanceData(newStart, newEnd);
        });
        document.getElementById('resetPerfFilter')?.addEventListener('click', () => {
            const todayDate = new Date().toISOString().split('T')[0];
            document.getElementById('perfStartDate').value = todayDate;
            document.getElementById('perfEndDate').value = todayDate;
            loadPerformanceData(todayDate, todayDate);
        });
        document.getElementById('closeMonitoringBtnDashboard')?.addEventListener('click', closeModalDashboard);
    }
    
    loadPerformanceData(currentStartDate, currentEndDate);
}


// ========== SEARCH FUNCTIONS (API BASED) ==========

// Debounce timer untuk search
let searchDebounceTimerTask = null;

function initSearchTaskWithAPI(searchInputId, containerId, countId, searchEndpoint) {
    const searchInput = document.getElementById(searchInputId);
    if (!searchInput) return;
    
    // Loading indicator
    const container = document.getElementById(containerId);
    const originalContent = container.innerHTML;
    
    // Simpan count awal
    const countSpanRef = document.getElementById(countId);
    const originalCount = countSpanRef ? countSpanRef.innerText : '';
    
    searchInput.addEventListener('keyup', function() {
        const keyword = this.value.trim();
        
        // Clear previous timer
        if (searchDebounceTimerTask) {
            clearTimeout(searchDebounceTimerTask);
        }
        
        // Jika keyword kosong, restore data awal tanpa reload halaman
        if (keyword.length === 0) {
            container.innerHTML = originalContent;
            if (countSpanRef) countSpanRef.innerText = originalCount;
            return;
        }
        
        // Minimal 2 karakter untuk search
        if (keyword.length < 2) return;
        
        // Set timer untuk debounce (500ms)
        searchDebounceTimerTask = setTimeout(async () => {
            // Tampilkan loading
            container.innerHTML = '<div class="stage-item-dashboard" style="text-align:center; padding:40px;"><i class="fas fa-spinner fa-pulse"></i> Mencari...</div>';
            
            try {
                const response = await fetch(baseUrlDashboard + 'bd/' + searchEndpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ keyword: keyword })
                });
                
                const result = await response.json();
                
                if (result.success && result.brands && result.brands.length > 0) {
                    // Render hasil search
                    renderSearchResults(containerId, result.brands, searchEndpoint);
                    
                    // Update count
                    const countSpan = document.getElementById(countId);
                    if (countSpan) countSpan.innerText = result.total;
                } else {
                    container.innerHTML = `
                        <div class="stage-item-dashboard empty-state" style="text-align:center; padding:40px;">
                            <i class="fas fa-search" style="font-size:32px; margin-bottom:12px; display:block; opacity:0.5;"></i>
                            <strong>Tidak ditemukan brand yang cocok</strong>
                            <div class="brand-details-dashboard">Coba kata kunci lain atau kosongkan pencarian</div>
                        </div>
                    `;
                    const countSpan = document.getElementById(countId);
                    if (countSpan) countSpan.innerText = '0';
                }
            } catch (error) {
                console.error('Search error:', error);
                container.innerHTML = `
                    <div class="stage-item-dashboard empty-state" style="text-align:center; padding:40px; color:#ef4444;">
                        <i class="fas fa-exclamation-triangle" style="font-size:32px; margin-bottom:12px; display:block;"></i>
                        <strong>Gagal mencari data</strong>
                        <div class="brand-details-dashboard">Silakan coba lagi</div>
                    </div>
                `;
            }
        }, 500);
    });
}

function renderSearchResults(containerId, brands, searchEndpoint) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    let html = '';
    const currentStage = getStageFromEndpoint(searchEndpoint);
    
    for (const brand of brands) {
        // 🔥 TASK 1: HUNTING
        if (searchEndpoint === 'search_hunting_brands') {
            html += `
                <div class="stage-item-dashboard brand-item-dashboard" 
                     data-brand-id="${brand.id}" 
                     data-brand-name="${escapeHtml(brand.name)}" 
                     data-stage="1">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <strong><i class="fas fa-building"></i> ${escapeHtml(brand.name)}</strong>
                        <!-- 🔥 TOMBOL EDIT DENGAN ONCLICK LANGSUNG -->
                        <button class="edit-brand-btn" 
                                data-brand-id="${brand.id}" 
                                data-brand-name="${escapeHtml(brand.name)}" 
                                data-whatsapp="${escapeHtml(brand.whatsapp_number || '')}" 
                                data-commission="${brand.proposed_commission || 0}" 
                                data-category="${escapeHtml(brand.category || '')}"
                                data-email="${escapeHtml(brand.email || '')}"
                                onclick="event.stopPropagation(); handleEditBrandClick(this);"
                                style="background: transparent; border: none; color: #fbbf24; cursor: pointer; font-size: 12px; padding: 4px 8px; border-radius: 4px; z-index: 10; position: relative;">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                    <div class="brand-details-dashboard">
                        <span><i class="fab fa-whatsapp"></i> ${brand.whatsapp_number || '-'}</span>
                        <span><i class="fas fa-user"></i> Input: ${brand.input_by || brand.bd_username || '-'}</span>
                        ${brand.category ? `<span><i class="fas fa-tag"></i> ${escapeHtml(brand.category)}</span>` : ''}
                    </div>
                    <div class="brand-details-dashboard" style="margin-top: 4px; font-size: 9px; color: #6b7280;">
                        <span><i class="fas fa-calendar-alt"></i> Input: ${brand.created_at ? new Date(brand.created_at).toLocaleDateString('id-ID') + ' ' + new Date(brand.created_at).toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit'}) : '-'}</span>
                        ${brand.proposed_commission ? `<span><i class="fas fa-percent"></i> Komisi: ${brand.proposed_commission}%</span>` : ''}
                    </div>
                    <span class="badge-dashboard badge-pending"><i class="fas fa-clock"></i> PENDING</span>
                </div>
            `;
        }
        // 🔥 TASK 2: FOLLOW UP
        else if (searchEndpoint === 'search_followup_brands') {
            const hasDeal = brand.deal_confirmed_at && brand.deal_confirmed_at !== null && brand.deal_confirmed_at !== '';
            const hasProducts = brand.has_products === true || brand.has_products === 1;
            const isClickable = !(hasDeal && !hasProducts);
            const cursorStyle = isClickable ? 'cursor: pointer;' : 'cursor: not-allowed; opacity: 0.8;';
            
            let statusBadge = '';
            if (hasDeal && hasProducts) {
                statusBadge = '<span class="badge-dashboard" style="background: rgba(139, 92, 246, 0.15); color: #8b5cf6;"><i class="fas fa-rocket"></i> Siap Setup</span>';
            } else if (hasDeal && !hasProducts) {
                statusBadge = '<span class="badge-dashboard" style="background: rgba(74, 222, 128, 0.15); color: #4ade80;"><i class="fas fa-clock"></i> Menunggu Registrasi</span>';
            } else {
                statusBadge = '<span class="badge-dashboard" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;"><i class="fas fa-clock"></i> Waiting Deal</span>';
            }
            
            let bottomNotification = '';
            if (hasDeal && !hasProducts) {
                bottomNotification = `
                    <div style="margin-top: 8px; padding: 6px 10px; background: rgba(74,222,128,0.1); border-radius: 8px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <span style="display: inline-block; width: 8px; height: 8px; background: #4ade80; border-radius: 50%; box-shadow: 0 0 4px #4ade80;"></span>
                        <span style="font-size: 10px; color: #4ade80;">Menunggu registrasi brand</span>
                        <button class="check-registration-btn" 
                                data-brand-id="${brand.id}" 
                                data-brand-name="${escapeHtml(brand.name)}"
                                style="background: #8b5cf6; color: white; border: none; padding: 4px 14px; border-radius: 30px; font-size: 10px; cursor: pointer; margin-left: auto;">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                `;
            } else if (hasDeal && hasProducts) {
                bottomNotification = `
                    <div style="margin-top: 8px; padding: 6px 10px; background: rgba(139,92,246,0.1); border-radius: 8px;">
                        <span style="font-size: 10px; color: #8b5cf6;">
                            <i class="fas fa-rocket"></i> Brand sudah registrasi! <strong>Klik untuk Setup Campaign</strong>
                        </span>
                    </div>
                `;
            }
            
            html += `
                <div class="stage-item-dashboard brand-item-dashboard followup-item-dashboard" 
                     data-brand-id="${brand.id}" 
                     data-brand-name="${escapeHtml(brand.name)}" 
                     data-stage="2"
                     data-click-count="${brand.follow_up_click_count || 0}"
                     data-is-clickable="${isClickable ? 'true' : 'false'}"
                     style="position: relative; ${cursorStyle}">
                    
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <strong><i class="fas fa-building"></i> ${escapeHtml(brand.name)}</strong>
                        ${statusBadge}
                    </div>
                    
                    <div class="brand-details-dashboard">
                        <span><i class="fab fa-whatsapp"></i> ${brand.whatsapp_number || '-'}</span>
                        <span><i class="fas fa-user"></i> Input: ${brand.input_by || brand.bd_username || '-'}</span>
                        <span><i class="fas fa-calendar"></i> Follow Up: ${brand.follow_up_at ? new Date(brand.follow_up_at).toLocaleDateString('id-ID') : '-'}</span>
                    </div>
                    
                    <div class="brand-details-dashboard" style="margin-top: 4px;">
                        <span><i class="fas fa-percent"></i> Komisi: ${brand.proposed_commission || 0}%</span>
                        ${brand.whatsapp_count ? `<span><i class="fab fa-whatsapp"></i> WA: ${brand.whatsapp_count}x</span>` : ''}
                        ${brand.campaign_id ? `<span><i class="fas fa-bullhorn"></i> Campaign: ${brand.campaign_id.substring(brand.campaign_id.length - 6)}</span>` : ''}
                    </div>
                    
                    ${bottomNotification}
                    
                    ${hasDeal ? `
                    <span class="badge-dashboard" style="margin-top: 8px; background: rgba(74, 222, 128, 0.15); color: #4ade80;">
                        <i class="fas fa-check-circle"></i> Deal: ${new Date(brand.deal_confirmed_at).toLocaleDateString('id-ID') + ' ' + new Date(brand.deal_confirmed_at).toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit'})}
                    </span>
                    ` : `
                    <span class="badge-dashboard" style="margin-top: 8px; background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                        <i class="fas fa-clock"></i> FOLLOW UP
                    </span>
                    `}
                </div>
            `;
        }
        // 🔥 TASK 3: SETUP CAMPAIGN
        else if (searchEndpoint === 'search_setup_brands') {
            const isActive = brand.status === 'ACTIVE' || brand.is_active_brand === true;
            const hasRequirements = brand.has_requirements === true;
            const pendingCount = brand.pending_products_count || 0;
            
            let statusBadge = '';
            if (isActive) {
                statusBadge = `<span class="badge-dashboard" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                    <i class="fas fa-check-circle"></i> ACTIVE
                </span>`;
            } else {
                statusBadge = `<span class="badge-dashboard badge-deal">
                    <i class="fas fa-cog"></i> MENUNGGU APPROVAL
                </span>`;
            }
            
            let requirementNote = '';
            if (isActive && hasRequirements) {
                requirementNote = `
                    <div style="margin-top: 6px; padding: 4px 8px; background: rgba(74,222,128,0.1); border-radius: 6px;">
                        <span style="font-size: 9px; color: #4ade80;">
                            <i class="fas fa-check-circle"></i> Requirement auto-fill dari sebelumnya
                        </span>
                    </div>
                `;
            } else if (!hasRequirements && !isActive) {
                requirementNote = `
                    <div style="margin-top: 6px; padding: 4px 8px; background: rgba(245,158,11,0.1); border-radius: 6px;">
                        <span style="font-size: 9px; color: #f59e0b;">
                            <i class="fas fa-exclamation-triangle"></i> Requirement belum diisi
                        </span>
                    </div>
                `;
            }
            
            html += `
                <div class="stage-item-dashboard brand-item-dashboard" 
                     data-brand-id="${brand.id}" 
                     data-brand-name="${escapeHtml(brand.name)}" 
                     data-stage="3"
                     data-has-requirements="${hasRequirements ? 'true' : 'false'}"
                     data-is-active="${isActive ? 'true' : 'false'}">
                    
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <strong>
                            <i class="fas fa-building"></i> ${escapeHtml(brand.name)}
                            ${isActive ? `<span style="font-size: 9px; color: #10b981; margin-left: 6px;">
                                <i class="fas fa-sync-alt"></i> Produk Baru
                            </span>` : ''}
                        </strong>
                        ${statusBadge}
                    </div>
                    
                    <div class="brand-details-dashboard">
                        <span><i class="fab fa-whatsapp"></i> ${brand.whatsapp_number || '-'}</span>
                        <span><i class="fas fa-user"></i> Input: ${brand.input_by || brand.bd_username || '-'}</span>
                        <span class="badge-dashboard" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                            <i class="fas fa-clock"></i> ${pendingCount} produk pending
                        </span>
                    </div>
                    
                    ${requirementNote}
                    
                    ${isActive && hasRequirements ? `
                        <span class="badge-dashboard" style="margin-top: 6px; background: rgba(139, 92, 246, 0.15); color: #8b5cf6; font-size: 9px;">
                            <i class="fas fa-history"></i> Auto-fill requirement
                        </span>
                    ` : ''}
                </div>
            `;
        }
        // 🔥 TASK 4: MONITORING
        else if (searchEndpoint === 'search_monitoring_brands') {
            html += `
                <div class="stage-item-dashboard brand-item-dashboard" 
                     data-brand-id="${brand.id}" 
                     data-brand-name="${escapeHtml(brand.name)}" 
                     data-stage="4">
                    <strong><i class="fas fa-chart-line"></i> ${escapeHtml(brand.name)}</strong>
                    <div class="brand-details-dashboard">
                        <span><i class="fab fa-whatsapp"></i> ${brand.whatsapp_number || '-'}</span>
                        <span><i class="fas fa-user"></i> Input: ${brand.input_by || brand.bd_username || '-'}</span>
                        <span><i class="fas fa-money-bill-wave"></i> GMV: Rp ${formatNumber(brand.today_gmv || 0)}</span>
                        <span><i class="fas fa-chart-line"></i> ROAS: ${brand.roas || 0}x</span>
                        <span class="badge-dashboard" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                            <i class="fas fa-check-circle"></i> ${brand.approved_products_count || 0} produk approve
                        </span>
                    </div>
                    <span class="badge-dashboard badge-active">
                        <i class="fas fa-chart-simple"></i> AKTIF
                    </span>
                </div>
            `;
        }
    }
    
   
    // 🔥 ATTACH EVENT LISTENER UNTUK TOMBOL EDIT (hasil search Task 1)
 container.innerHTML = html;
     document.querySelectorAll(`#${containerId} .edit-brand-btn`).forEach(btn => {
        // Jangan override onclick yang sudah ada
        if (!btn.getAttribute('onclick')) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                handleEditBrandClick(this);
            });
        }
    });
    // 🔥 ATTACH EVENT LISTENER UNTUK TOMBOL CHECK REGISTRATION (Task 2)
    document.querySelectorAll(`#${containerId} .check-registration-btn`).forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.stopPropagation();
            const brandId = this.getAttribute('data-brand-id');
            const brandName = this.getAttribute('data-brand-name');
            
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-pulse"></i>';
            
            try {
                const response = await fetch(baseUrlDashboard + 'bd/check_brand_registration', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ brand_id: brandId })
                });
                
                const result = await response.json();
                
                if (result.success && result.has_products) {
                    showToastInModal('✅ Brand ' + brandName + ' sudah registrasi! Memindahkan ke Task 3...', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToastInModal('⏳ Brand ' + brandName + ' belum registrasi. Silakan tunggu.', 'warning');
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
                }
            } catch (error) {
                showToastInModal('Gagal mengecek registrasi', 'error');
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
            }
        });
    });
    
    // 🔥 ATTACH EVENT LISTENER UNTUK BRAND ITEMS
    document.querySelectorAll(`#${containerId} .brand-item-dashboard`).forEach(item => {
        // Clone untuk menghindari double event listener
        const newItem = item.cloneNode(true);
        item.parentNode.replaceChild(newItem, item);
        
        newItem.addEventListener('click', async (e) => {
            // Jangan trigger jika klik tombol di dalam item
            if (e.target.closest('.check-registration-btn') || 
                e.target.closest('.edit-brand-btn') ||
                e.target.closest('.remove-existing-product-dashboard') || 
                e.target.closest('.remove-new-product-dashboard')) {
                return;
            }
            
            const brandId = newItem.getAttribute('data-brand-id');
            const brandName = newItem.getAttribute('data-brand-name');
            const stage = parseInt(newItem.getAttribute('data-stage'));
            
            if (stage === 1) {
                showTask1DetailDashboard(brandId, brandName);
            } else if (stage === 2) {
                const isClickable = newItem.getAttribute('data-is-clickable') === 'true';
                
                if (!isClickable) {
                    showToastInModal('⏳ Brand sedang menunggu registrasi campaign. Klik tombol "Refresh" untuk cek status.', 'warning');
                    return;
                }
                
                // 🔥 CEK ULANG STATUS REGISTRASI SEBELUM BUKA MODAL
                try {
                    const checkResponse = await fetch(baseUrlDashboard + 'bd/check_brand_registration', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ brand_id: brandId })
                    });
                    const checkResult = await checkResponse.json();
                    
                    if (checkResult.success && checkResult.has_products) {
                        showToastInModal('✅ Brand sudah registrasi! Memuat ulang halaman...', 'success');
                        setTimeout(() => location.reload(), 1000);
                        return;
                    }
                } catch (err) {
                    console.error('Error checking registration:', err);
                }
                
                showTask2FollowUpModal(brandId, brandName);
            } else if (stage === 3) {
                showTask3SetupModalWithRecommendations(brandId, brandName);
            } else if (stage === 4) {
                showTask4MonitoringModalDashboard(brandId, brandName);
            }
        });
    });
}
function getStageFromEndpoint(endpoint) {
    if (endpoint === 'search_hunting_brands') return 1;
    if (endpoint === 'search_followup_brands') return 2;
    if (endpoint === 'search_setup_brands') return 3;
    if (endpoint === 'search_monitoring_brands') return 4;
    return 0;
}
// ========== SCOUT & SEARCH BRAND MODAL (WITH SEARCH BUTTON) ==========

// Variabel untuk menyimpan hasil search terakhir
let lastSearchResults = { shops: [], products: [] };


// ========== SCOUT & SEARCH BRAND MODAL (FIXED - PROPER PRODUCT PARSING) ==========

async function searchBrandProducts(keyword) {
    if (!keyword || keyword.length < 2) {
        showToastInModal('Minimal 2 karakter untuk mencari', 'warning');
        return { shops: [], products: [], highest_commission: 0, avg_commission: 0 };
    }
    
    isSearchingBrand = true;
    
    try {
        // Panggil API search brand
        const response = await fetch(baseUrlDashboard + 'brand_crawler/test_search?keyword=' + encodeURIComponent(keyword));
        const result = await response.json();
        
        console.log('Search API response:', result);
        
        if (result.success === true) {
            let shops = [];
            let products = [];
            let highest_commission = 0;
            let avg_commission = 0;
            let highest_commission_product = null;
            
            // ========== BUAT DATA SHOP/TOKO ==========
            if (result.seller_id && result.shop_name) {
                // ========== AMBIL PRODUK LENGKAP DARI API ==========
                try {
                    const productResponse = await fetch(baseUrlDashboard + 'brand_crawler/get_brand_products?seller_id=' + encodeURIComponent(result.seller_id) + '&shop_name=' + encodeURIComponent(result.shop_name));
                    const productResult = await productResponse.json();
                    
                    console.log('Products API response:', productResult);
                    
                    if (productResult.success && productResult.products && productResult.products.length > 0) {
                        products = productResult.products;
                        highest_commission = productResult.highest_commission || 0;
                        avg_commission = productResult.avg_commission || 0;
                        highest_commission_product = productResult.highest_commission_product;
                    }
                } catch (err) {
                    console.error('Error fetching products:', err);
                }
                
                // Buat data shop dengan info komisi tertinggi
                shops.push({
                    type: 'shop',
                    id: result.seller_id,
                    name: result.shop_name,
                    seller_id: result.seller_id,
                    product_count: products.length,
                    contact_info: {
                        whatsapp: result.whatsapp || '',
                        email: result.email || ''
                    },
                    highest_commission: highest_commission,
                    highest_commission_percent: highest_commission + '%',
                    avg_commission: avg_commission,
                    total_products: products.length
                });
            }
            
            lastSearchResults = { shops, products, highest_commission, avg_commission };
            return { shops, products, highest_commission, avg_commission };
        }
        
        lastSearchResults = { shops: [], products: [], highest_commission: 0, avg_commission: 0 };
        return { shops: [], products: [], highest_commission: 0, avg_commission: 0 };
        
    } catch (error) {
        console.error('Error searching brand:', error);
        lastSearchResults = { shops: [], products: [], highest_commission: 0, avg_commission: 0 };
        return { shops: [], products: [], highest_commission: 0, avg_commission: 0 };
    } finally {
        isSearchingBrand = false;
    }
}


function displaySearchResultsInDropdown(results) {
    const dropdown = document.getElementById('searchResultsDropdown');
    if (!dropdown) return;
    
    const shops = results.shops || [];
    const products = results.products || [];
    const highest_commission = results.highest_commission || 0;
    
    console.log('Displaying results - shops:', shops.length, 'products:', products.length);
    console.log('Highest commission:', highest_commission);
    
    dropdown.style.display = 'block';
    
    if (shops.length === 0 && products.length === 0) {
        dropdown.innerHTML = `
            <div style="text-align:center; padding:20px; color:#ef4444;">
                <i class="fas fa-search" style="font-size:24px; margin-bottom:8px; display:block;"></i>
                Tidak ditemukan brand yang cocok
            </div>
            <div id="manualInputOption" style="padding:10px 12px; cursor:pointer; border-top:1px solid #2a3346; background:#1a1f2e; text-align:center;">
                <i class="fas fa-keyboard" style="color:#4ade80;"></i>
                <span style="color:#4ade80; margin-left:8px;">Atau input manual</span>
            </div>
        `;
        attachManualOptionEvent();
        return;
    }
    
    let html = '';
    
    // ========== TAMPILKAN TOKO DENGAN TOTAL KOMISI & RANGE KOMISI ==========
    if (shops.length > 0) {
        shops.forEach(shop => {
            // 🔥 HITUNG RANGE KOMISI (min - max) dari semua produk
            let minCommission = 100;
            let maxCommission = 0;
            let totalCommissionSum = 0;
            let productCount = shop.total_products || 0;
            
            // Cari produk dari shop ini untuk hitung range
            const shopProducts = products.filter(p => p.shop_name === shop.name);
            if (shopProducts.length > 0) {
                shopProducts.forEach(p => {
                    const comm = p.commission_rate || 0;
                    if (comm < minCommission) minCommission = comm;
                    if (comm > maxCommission) maxCommission = comm;
                    totalCommissionSum += comm;
                });
            }
            
            // Jika tidak ada produk, gunakan data dari shop
            if (minCommission === 100) minCommission = shop.highest_commission || 0;
            if (maxCommission === 0) maxCommission = shop.highest_commission || 0;
            
            const avgCommission = productCount > 0 ? (totalCommissionSum / productCount).toFixed(1) : shop.avg_commission || 0;
            const rangeDisplay = (minCommission === maxCommission) 
                ? `${minCommission}%` 
                : `${minCommission}% - ${maxCommission}%`;
            
            html += `
                <div style="margin-bottom:12px; border:1px solid #8b5cf6; border-radius:12px; overflow:hidden;">
                    <div style="padding:8px 12px; background:#1a1f2e; color:#8b5cf6; font-size:11px; font-weight:600;">
                        <i class="fas fa-store"></i> TOKO
                    </div>
                    <div class="search-result-item" data-type="shop" 
                         data-id="${shop.id}" 
                         data-name="${escapeHtml(shop.name)}" 
                         data-shop-id="${shop.id}"
                         data-shop-name="${escapeHtml(shop.name)}"
                         data-seller-id="${shop.seller_id || shop.id}"
                         data-highest-commission="${maxCommission}"
                         data-avg-commission="${avgCommission}"
                         data-min-commission="${minCommission}"
                         data-range-commission="${rangeDisplay}"
                         data-total-products="${productCount}"
                         data-contact-info='${JSON.stringify(shop.contact_info || {})}'
                         style="padding:12px; cursor:pointer; background: rgba(139,92,246,0.08);"
                         onmouseover="this.style.background='rgba(139,92,246,0.15)'" onmouseout="this.style.background='rgba(139,92,246,0.08)'">
                        <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                            <i class="fas fa-store" style="color:#8b5cf6; font-size:18px;"></i>
                            <div>
                                <div style="font-weight:700; color:#e2f0e8; font-size:14px;">${escapeHtml(shop.name)}</div>
                                <div style="font-size:10px; color:#4ade80;">Seller ID: ${shop.id}</div>
                            </div>
                        </div>
                        <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:8px; padding-top:8px; border-top:1px solid rgba(139,92,246,0.2);">
                            <span style="font-size:10px; color:#fbbf24;">
                                <i class="fas fa-chart-line"></i> Total Komisi: <strong style="color:#4ade80;">${maxCommission}%</strong>
                            </span>
                            <span style="font-size:10px; color:#8b5cf6;">
                                <i class="fas fa-chart-simple"></i> Range Komisi: ${rangeDisplay}
                            </span>
                            <span style="font-size:10px; color:#10b981;">
                                <i class="fas fa-box"></i> Total Produk: ${productCount}
                            </span>
                        </div>
                    </div>
                </div>
            `;
        });
    }
    
    // ========== TAMPILKAN PRODUK ==========
    if (products.length > 0) {
        const sortedProducts = [...products].sort((a, b) => (b.sales_numeric || 0) - (a.sales_numeric || 0));
        
        html += `<div style="padding:8px 12px; background:#1a1f2e; color:#4ade80; font-size:11px; font-weight:600; margin-top:8px; border-radius:8px;">
                    <i class="fas fa-box"></i> DAFTAR PRODUK (${products.length})
                    <span style="font-size:9px; color:#fbbf24; margin-left:8px;">Urut berdasarkan penjualan tertinggi</span>
                 </div>`;
        
        sortedProducts.forEach((product, index) => {
            const priceFormatted = product.price_formatted || `Rp ${formatNumber(product.price_amount)}`;
            const commissionPercentValue = product.commission_rate || 0;
            const commissionPercentDisplay = commissionPercentValue + '%';
            const salesDisplay = product.sales_display || formatNumber(product.sales_numeric) + ' sold';
            
            html += `
                <div class="search-result-item" data-type="product" 
                     data-id="${product.product_id}" 
                     data-shop-id="${product.seller_id}" 
                     data-shop-name="${escapeHtml(product.shop_name)}" 
                     data-name="${escapeHtml(product.title)}"
                     data-price="${product.price_amount}" 
                     data-price-formatted="${escapeHtml(priceFormatted)}"
                     data-image="${escapeHtml(product.image_url)}"
                     data-commission-rate="${commissionPercentValue}"
                     data-commission-percent="${commissionPercentDisplay}"
                     data-sales="${product.sales_numeric}"
                     data-sales-formatted="${escapeHtml(salesDisplay)}"
                     data-product-rating="${product.product_rating}"
                     data-contact-info='${JSON.stringify({ whatsapp: product.whatsapp, email: product.email })}'
                     style="padding:10px 12px; cursor:pointer; border-bottom:1px solid #2a3346;"
                     onmouseover="this.style.background='rgba(74,222,128,0.05)'" onmouseout="this.style.background='transparent'">
                    <div style="display:flex; gap:12px; align-items:center;">
                        ${product.image_url ? `<img src="${escapeHtml(product.image_url)}" style="width:50px; height:50px; object-fit:cover; border-radius:8px;" onerror="this.style.display='none'">` : '<div style="width:50px; height:50px; background:#1e293b; border-radius:8px; display:flex; align-items:center; justify-content:center;"><i class="fas fa-box"></i></div>'}
                        <div style="flex:1;">
                            <div style="font-weight:500; color:#e2f0e8; font-size:12px; margin-bottom:4px;">
                                ${escapeHtml(product.title.substring(0, 60))}${product.title.length > 60 ? '...' : ''}
                            </div>
                            <div style="font-size:10px; color:#4ade80; margin-bottom:4px;">${escapeHtml(product.shop_name)}</div>
                            <div style="display:flex; gap:12px; margin-top:4px; flex-wrap:wrap;">
                                <span style="font-size:10px; color:#fbbf24;"><i class="fas fa-tag"></i> ${priceFormatted}</span>
                                <span style="font-size:10px; color:#8b5cf6;"><i class="fas fa-percent"></i> Komisi: <strong>${commissionPercentDisplay}</strong></span>
                                <span style="font-size:10px; color:#10b981;"><i class="fas fa-chart-line"></i> Terjual: ${salesDisplay}</span>
                                ${product.product_rating > 0 ? `<span style="font-size:10px; color:#fbbf24;"><i class="fas fa-star"></i> Rating: ${product.product_rating}</span>` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
    }
    
    // ========== TAMPILKAN OPSI INPUT MANUAL ==========
    html += `
        <div id="manualInputOption" style="padding:10px 12px; cursor:pointer; border-top:1px solid #2a3346; background:#1a1f2e; text-align:center; margin-top:8px; border-radius:8px;"
             onmouseover="this.style.background='rgba(74,222,128,0.1)'" onmouseout="this.style.background='#1a1f2e'">
            <i class="fas fa-keyboard" style="color:#4ade80;"></i>
            <span style="color:#4ade80; margin-left:8px;">Atau input manual</span>
        </div>
    `;
    
    dropdown.innerHTML = html;
    
    // Attach event listeners
    attachSearchResultEvents();
    attachManualOptionEvent();
}

function attachSearchResultEvents() {
    document.querySelectorAll('.search-result-item').forEach(item => {
        const newItem = item.cloneNode(true);
        item.parentNode.replaceChild(newItem, item);
        
        newItem.addEventListener('click', async () => {
            const type = newItem.getAttribute('data-type');
            const name = newItem.getAttribute('data-name');
            const shopId = newItem.getAttribute('data-shop-id');
            const shopName = newItem.getAttribute('data-shop-name');
            const sellerId = newItem.getAttribute('data-seller-id') || shopId;
            const commissionRate = newItem.getAttribute('data-commission-rate');
            const commissionPercent = newItem.getAttribute('data-commission-percent');
            const highestCommission = newItem.getAttribute('data-highest-commission');
            const minCommission = newItem.getAttribute('data-min-commission');
            const rangeCommission = newItem.getAttribute('data-range-commission');
            const totalProducts = newItem.getAttribute('data-total-products');
            const salesFormatted = newItem.getAttribute('data-sales-formatted');
            const priceFormatted = newItem.getAttribute('data-price-formatted');
            
            console.log('Selected item:', { 
                type, name, shopId, shopName, sellerId, 
                commissionRate, commissionPercent, highestCommission, rangeCommission
            });
            
            let contactInfo = {};
            try {
                const contactInfoStr = newItem.getAttribute('data-contact-info');
                if (contactInfoStr && contactInfoStr !== 'null' && contactInfoStr !== 'undefined') {
                    contactInfo = JSON.parse(contactInfoStr);
                }
            } catch(e) {
                console.error('Parse contact info error:', e);
            }
            
            // Sembunyikan dropdown
            const dropdown = document.getElementById('searchResultsDropdown');
            if (dropdown) dropdown.style.display = 'none';
            
            // Tampilkan selected item
            const selectedDisplay = document.getElementById('selectedItemDisplay');
            const selectedName = document.getElementById('selectedItemName');
            const selectedType = document.getElementById('selectedItemType');
            const brandNameInput = document.getElementById('brandNameDashboard');
            const commissionInfo = document.getElementById('commissionInfo');
            const sellerIdHidden = document.getElementById('sellerIdHidden');
            const commissionRateHidden = document.getElementById('selectedCommissionRate');
            const salesCountHidden = document.getElementById('selectedSalesCount');
            const whatsappInput = document.getElementById('brandWhatsappDashboard');
            const emailInput = document.getElementById('brandEmailDashboard');
            const whatsappStatus = document.getElementById('whatsappStatus');
            const noShopWarning = document.getElementById('noShopSelectedWarning');
            
            if (dropdown) dropdown.style.display = 'none';
            if (noShopWarning) noShopWarning.style.display = 'none';
            
            if (selectedDisplay) selectedDisplay.style.display = 'block';
            if (selectedName) selectedName.innerText = name;
            
            if (type === 'shop') {
                if (selectedType) selectedType.innerHTML = ' Toko';
                if (brandNameInput) brandNameInput.value = shopName || name;
                
                // Tampilkan info komisi (Total Komisi & Range Komisi)
                if (commissionInfo && rangeCommission) {
                    commissionInfo.style.display = 'block';
                    commissionInfo.innerHTML = `<i class="fas fa-chart-line"></i> 🔥 Total Komisi: ${highestCommission}% | Range: ${rangeCommission} | Total Produk: ${totalProducts || '0'}`;
                    commissionInfo.style.color = '#fbbf24';
                }
                
                // Simpan seller_id
                if (sellerIdHidden && sellerId) {
                    sellerIdHidden.value = sellerId;
                    console.log('Seller ID saved:', sellerId);
                }
                
                // Simpan commission rate
                if (commissionRateHidden && highestCommission) {
                    commissionRateHidden.value = highestCommission;
                }
                
                // 🔥 Simpan range komisi ke hidden input
                let rangeHidden = document.getElementById('selectedRangeCommission');
                if (!rangeHidden) {
                    const newInput = document.createElement('input');
                    newInput.type = 'hidden';
                    newInput.id = 'selectedRangeCommission';
                    newInput.name = 'range_commission';
                    document.getElementById('modalBodyDashboard').appendChild(newInput);
                    rangeHidden = newInput;
                }
                if (rangeHidden && rangeCommission) {
                    rangeHidden.value = rangeCommission;
                }
                
                // Auto fetch kontak
                let whatsappNumber = contactInfo.whatsapp || '';
                let emailAddress = contactInfo.email || '';
                
                if (sellerId && sellerId !== '' && sellerId !== 'null' && sellerId !== 'undefined') {
                    if (!whatsappNumber || whatsappNumber.includes('*')) {
                        showToastInModal('🔍 Mencari kontak brand...', 'info');
                        try {
                            const contactResult = await fetchBrandContact(sellerId, shopName);
                            if (contactResult.success && contactResult.whatsapp && !contactResult.whatsapp.includes('*')) {
                                whatsappNumber = contactResult.whatsapp;
                                emailAddress = contactResult.email || '';
                            }
                        } catch (err) {
                            console.error('Error fetching contact:', err);
                        }
                    }
                    
                    if (whatsappInput) {
                        if (whatsappNumber && !whatsappNumber.includes('*')) {
                            whatsappInput.value = whatsappNumber;
                            if (whatsappStatus) {
                                whatsappStatus.innerHTML = '(Auto-ditemukan)';
                                whatsappStatus.style.color = '#4ade80';
                            }
                            showToastInModal(`Kontak ditemukan! WhatsApp: ${whatsappNumber}`, 'success');
                        } else {
                            if (whatsappStatus) {
                                whatsappStatus.innerHTML = ' (Tidak ditemukan - isi manual)';
                                whatsappStatus.style.color = '#fbbf24';
                            }
                        }
                    }
                    
                    if (emailInput && emailAddress) {
                        emailInput.value = emailAddress;
                    }
                }
            } else if (type === 'product') {
                if (selectedType) selectedType.innerHTML = ' Produk';
                if (brandNameInput) brandNameInput.value = shopName || name;
                
                if (commissionInfo && commissionPercent) {
                    commissionInfo.style.display = 'block';
                    commissionInfo.innerHTML = `<i class="fas fa-percent"></i> Komisi: ${commissionPercent} | Harga: ${priceFormatted} | Terjual: ${salesFormatted || '0'}`;
                    commissionInfo.style.color = '#fbbf24';
                }
                
                if (sellerIdHidden && sellerId) {
                    sellerIdHidden.value = sellerId;
                }
                
                if (commissionRateHidden && commissionRate) {
                    commissionRateHidden.value = commissionRate;
                }
                
                if (salesCountHidden && salesFormatted) {
                    salesCountHidden.value = salesFormatted;
                }
            }
            
            currentSelectedShop = { 
                id: sellerId, 
                name: shopName || name, 
                contact_info: contactInfo,
                commission_rate: highestCommission || commissionRate,
                range_commission: rangeCommission
            };
        });
    });
}

function attachSearchResultEvents() {
    document.querySelectorAll('.search-result-item').forEach(item => {
        const newItem = item.cloneNode(true);
        item.parentNode.replaceChild(newItem, item);
        
        newItem.addEventListener('click', async () => {
            //  AMBIL ATTRIBUTES
            const type = newItem.getAttribute('data-type');
            const name = newItem.getAttribute('data-name');
            const shopId = newItem.getAttribute('data-shop-id');
            const shopName = newItem.getAttribute('data-shop-name');
            const sellerId = newItem.getAttribute('data-seller-id') || shopId;
            const commissionRate = newItem.getAttribute('data-commission-rate');
            const commissionPercent = newItem.getAttribute('data-commission-percent');
            const highestCommission = newItem.getAttribute('data-highest-commission');
            const highestCommissionPercent = newItem.getAttribute('data-highest-commission-percent');
            const totalProducts = newItem.getAttribute('data-total-products');
            const salesFormatted = newItem.getAttribute('data-sales-formatted');
            const priceFormatted = newItem.getAttribute('data-price-formatted');
            
            console.log('Selected item:', { 
                type, name, shopId, shopName, sellerId, 
                commissionRate, commissionPercent, highestCommission, highestCommissionPercent
            });
            
            //  PARSING CONTACT INFO
            let contactInfo = {};
            try {
                const contactInfoStr = newItem.getAttribute('data-contact-info');
                if (contactInfoStr && contactInfoStr !== 'null' && contactInfoStr !== 'undefined') {
                    contactInfo = JSON.parse(contactInfoStr);
                }
            } catch(e) {
                console.error('Parse contact info error:', e);
            }
            
            //  AMBIL ELEMEN DOM
            const dropdown = document.getElementById('searchResultsDropdown');
            const selectedDisplay = document.getElementById('selectedItemDisplay');
            const selectedName = document.getElementById('selectedItemName');
            const selectedType = document.getElementById('selectedItemType');
            const brandNameInput = document.getElementById('brandNameDashboard');
            const commissionInfo = document.getElementById('commissionInfo');
            const sellerIdHidden = document.getElementById('sellerIdHidden');
            const commissionRateHidden = document.getElementById('selectedCommissionRate');
            const salesCountHidden = document.getElementById('selectedSalesCount');
            const whatsappInput = document.getElementById('brandWhatsappDashboard');
            const emailInput = document.getElementById('brandEmailDashboard');
            const whatsappStatus = document.getElementById('whatsappStatus');
            const noShopWarning = document.getElementById('noShopSelectedWarning');
            
            // Sembunyikan dropdown
            if (dropdown) dropdown.style.display = 'none';
            
            // Sembunyikan warning jika ada
            if (noShopWarning) noShopWarning.style.display = 'none';
            
            // Tampilkan selected item display
            if (selectedDisplay) selectedDisplay.style.display = 'block';
            if (selectedName) selectedName.innerText = name;
            
            //  HANDLE TYPE SHOP
            if (type === 'shop') {
                if (selectedType) selectedType.innerHTML = ' Toko';
                if (brandNameInput) brandNameInput.value = shopName || name;
                
                // Tampilkan info komisi tertinggi
                if (commissionInfo && highestCommissionPercent) {
                    commissionInfo.style.display = 'block';
                    commissionInfo.innerHTML = `<i class="fas fa-chart-line"></i>  Komisi Tertinggi: ${highestCommissionPercent} | Total Produk: ${totalProducts || '0'}`;
                    commissionInfo.style.color = '#fbbf24';
                }
                
                // Simpan seller_id
                if (sellerIdHidden && sellerId) {
                    sellerIdHidden.value = sellerId;
                    console.log('Seller ID saved to hidden input:', sellerId);
                }
                
                // 🔥 SIMPAN OPEN COMMISSION RATE (KOMISI TERTINGGI)
                if (commissionRateHidden && highestCommission) {
                    commissionRateHidden.value = highestCommission;
                    console.log('Open commission rate saved:', highestCommission);
                }
                
                // 🔥 TAMBAHKAN HIDDEN INPUT UNTUK OPEN COMMISSION RATE
                let openCommissionHidden = document.getElementById('selectedOpenCommission');
                if (!openCommissionHidden) {
                    // Buat hidden input jika belum ada
                    const newInput = document.createElement('input');
                    newInput.type = 'hidden';
                    newInput.id = 'selectedOpenCommission';
                    newInput.name = 'open_commission_rate';
                    document.getElementById('modalBodyDashboard').appendChild(newInput);
                    openCommissionHidden = newInput;
                }
                if (openCommissionHidden && highestCommission) {
                    openCommissionHidden.value = highestCommission;
                }
                
                //  AMBIL WHATSAPP DAN EMAIL (DEFINISIKAN DI SINI)
                let whatsappNumber = contactInfo.whatsapp || '';
                let emailAddress = contactInfo.email || '';
                
                console.log('Contact info from shop:', { whatsappNumber, emailAddress });
                
                // Auto fetch kontak menggunakan seller_id
                if (sellerId && sellerId !== '' && sellerId !== 'null' && sellerId !== 'undefined') {
                    if (!whatsappNumber || whatsappNumber.includes('*')) {
                        showToastInModal('🔍 Mencari kontak brand...', 'info');
                        
                        try {
                            const contactResult = await fetchBrandContact(sellerId, shopName);
                            if (contactResult.success && contactResult.whatsapp && !contactResult.whatsapp.includes('*')) {
                                whatsappNumber = contactResult.whatsapp;
                                emailAddress = contactResult.email || '';
                                console.log('Contact fetched from API:', { whatsappNumber, emailAddress });
                            }
                        } catch (err) {
                            console.error('Error fetching contact:', err);
                        }
                    }
                    
                    // Isi input WhatsApp
                    if (whatsappInput) {
                        if (whatsappNumber && !whatsappNumber.includes('*')) {
                            whatsappInput.value = whatsappNumber;
                            if (whatsappStatus) {
                                whatsappStatus.innerHTML = ' (Auto-ditemukan)';
                                whatsappStatus.style.color = '#4ade80';
                            }
                            showToastInModal(`Kontak ditemukan! WhatsApp: ${whatsappNumber}`, 'success');
                        } else {
                            if (whatsappStatus) {
                                whatsappStatus.innerHTML = ' (Tidak ditemukan - isi manual)';
                                whatsappStatus.style.color = '#fbbf24';
                            }
                        }
                    }
                    
                    // Isi input Email
                    if (emailInput && emailAddress) {
                        emailInput.value = emailAddress;
                    }
                } else {
                    if (whatsappStatus) {
                        whatsappStatus.innerHTML = ' (Tidak ada seller ID - isi manual)';
                        whatsappStatus.style.color = '#ef4444';
                    }
                }
            }
            
            //  HANDLE TYPE PRODUCT
            else if (type === 'product') {
                if (selectedType) selectedType.innerHTML = ' Produk';
                if (brandNameInput) brandNameInput.value = shopName || name;
                
                // Tampilkan info komisi produk
                if (commissionInfo && commissionPercent) {
                    commissionInfo.style.display = 'block';
                    commissionInfo.innerHTML = `<i class="fas fa-percent"></i> Komisi: ${commissionPercent} | Harga: ${priceFormatted} | Terjual: ${salesFormatted || '0'}`;
                    commissionInfo.style.color = '#fbbf24';
                } else if (commissionInfo && commissionRate) {
                    commissionInfo.style.display = 'block';
                    commissionInfo.innerHTML = `<i class="fas fa-percent"></i> Komisi: ${commissionRate}% | Harga: ${priceFormatted} | Terjual: ${salesFormatted || '0'}`;
                    commissionInfo.style.color = '#fbbf24';
                }
                
                // Simpan seller_id
                if (sellerIdHidden && sellerId) {
                    sellerIdHidden.value = sellerId;
                }
                
                // Simpan commission rate
                if (commissionRateHidden && commissionRate) {
                    commissionRateHidden.value = commissionRate;
                } else if (commissionRateHidden && commissionPercent) {
                    const percentNum = parseFloat(commissionPercent);
                    if (!isNaN(percentNum)) {
                        commissionRateHidden.value = percentNum;
                    }
                }
                
                if (salesCountHidden && salesFormatted) {
                    salesCountHidden.value = salesFormatted;
                }
                
                //  AMBIL WHATSAPP DAN EMAIL (DEFINISIKAN DI SINI)
                let whatsappNumber = contactInfo.whatsapp || '';
                let emailAddress = contactInfo.email || '';
                
                // Auto fetch kontak
                if (sellerId && sellerId !== '' && sellerId !== 'null' && sellerId !== 'undefined') {
                    if (!whatsappNumber || whatsappNumber.includes('*')) {
                        showToastInModal('🔍 Mencari kontak brand...', 'info');
                        
                        try {
                            const contactResult = await fetchBrandContact(sellerId, shopName);
                            if (contactResult.success && contactResult.whatsapp && !contactResult.whatsapp.includes('*')) {
                                whatsappNumber = contactResult.whatsapp;
                                emailAddress = contactResult.email || '';
                            }
                        } catch (err) {
                            console.error('Error fetching contact:', err);
                        }
                    }
                    
                    if (whatsappInput) {
                        if (whatsappNumber && !whatsappNumber.includes('*')) {
                            whatsappInput.value = whatsappNumber;
                            if (whatsappStatus) {
                                whatsappStatus.innerHTML = ' (Auto-ditemukan)';
                                whatsappStatus.style.color = '#4ade80';
                            }
                            showToastInModal(`Kontak ditemukan! WhatsApp: ${whatsappNumber}`, 'success');
                        } else {
                            if (whatsappStatus) {
                                whatsappStatus.innerHTML = ' (Tidak ditemukan - isi manual)';
                                whatsappStatus.style.color = '#fbbf24';
                            }
                        }
                    }
                    
                    if (emailInput && emailAddress) {
                        emailInput.value = emailAddress;
                    }
                }
            }
            
            // Simpan current selected shop
            currentSelectedShop = { 
                id: sellerId, 
                name: shopName || name, 
                contact_info: contactInfo,
                commission_rate: commissionRate,
                commission_percent: commissionPercent,
                sales: salesFormatted,
                highest_commission: highestCommission,
                highest_commission_percent: highestCommissionPercent
            };
        });
    });
}

function attachManualOptionEvent() {
    const manualOption = document.getElementById('manualInputOption');
    if (manualOption) {
        const newManualOption = manualOption.cloneNode(true);
        manualOption.parentNode.replaceChild(newManualOption, manualOption);
        
        newManualOption.addEventListener('click', () => {
            const dropdown = document.getElementById('searchResultsDropdown');
            if (dropdown) dropdown.style.display = 'none';
            
            // Kosongkan selected display
            const selectedDisplay = document.getElementById('selectedItemDisplay');
            if (selectedDisplay) selectedDisplay.style.display = 'none';
            
            const whatsappStatus = document.getElementById('whatsappStatus');
            if (whatsappStatus) whatsappStatus.innerHTML = '';
            
            const commissionInfo = document.getElementById('commissionInfo');
            if (commissionInfo) commissionInfo.style.display = 'none';
            
            // Fokus ke input nama brand
            const brandNameInput = document.getElementById('brandNameDashboard');
            if (brandNameInput) {
                brandNameInput.value = '';
                brandNameInput.focus();
                brandNameInput.placeholder = 'Masukkan nama brand secara manual...';
            }
            
            showToastInModal('Silakan isi data brand secara manual', 'info');
        });
    }
}

async function fetchBrandContact(sellerId, shopName) {
    if (!sellerId || sellerId === '' || sellerId === 'null' || sellerId === 'undefined') {
        console.log('No seller ID provided');
        return { success: false, whatsapp: '', email: '', message: 'No seller ID provided' };
    }
    
    try {
        console.log('Fetching contact for seller_id:', sellerId, 'shop:', shopName);
        
        const response = await fetch(baseUrlDashboard + 'brand_crawler/test_contact?seller_id=' + encodeURIComponent(sellerId));
        const result = await response.json();
        
        console.log('Contact API response:', result);
        
        // Response dari endpoint test_contact
        if (result.success === true) {
            let whatsapp = result.whatsapp || '';
            let email = result.email || '';
            
            if (whatsapp && whatsapp.includes('*')) {
                console.log('WhatsApp has masking, ignoring');
                whatsapp = '';
            }
            
            if (whatsapp || email) {
                return {
                    success: true,
                    whatsapp: whatsapp,
                    email: email,
                    message: 'Contact found'
                };
            }
        }
        
        // Cek struktur alternatif
        if (result.http_code === 200 && result.data?.contact_info) {
            let whatsapp = result.data.contact_info.whatsapp || '';
            let email = result.data.contact_info.email || '';
            
            if (whatsapp && !whatsapp.includes('*')) {
                return {
                    success: true,
                    whatsapp: whatsapp,
                    email: email,
                    message: 'Contact found'
                };
            }
        }
        
        return {
            success: false,
            whatsapp: '',
            email: '',
            message: 'No contact information available'
        };
        
    } catch (error) {
        console.error('Error fetching contact:', error);
        return {
            success: false,
            whatsapp: '',
            email: '',
            error: error.message,
            message: 'Failed to fetch contact'
        };
    }
}

function showNewBrandModal() {
    console.log('Opening new brand modal with search button');
    
    const modalTitleElem = document.getElementById('modalTitleDashboard'); 
    const modalBodyElem = document.getElementById('modalBodyDashboard');
    
    if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
    searchDebounceTimer = null;
    currentSearchKeyword = '';
    lastSearchResults = { shops: [], products: [] };
    currentSelectedShop = null;
    currentSelectedProduct = null;
    currentSelectedCreator = null;
    
    modalTitleElem.innerHTML = '<i class="fas fa-search"></i> Cari Brand Baru';
    modalBodyElem.innerHTML = `
        <div style="background:rgba(74,222,128,0.1); border-radius:14px; padding:12px; margin-bottom:16px;">
            <p style="color:#4ade80; font-size:12px;"><i class="fas fa-robot"></i> Cari brand dari TikTok Shop</p>
            <p style="color:#9aaebe; font-size:10px;">Masukkan nama brand  yang sesuai, dan masukan nomor telpon yang bisa di hubungi</p>
        </div>
        
        <!-- Peringatan jika belum pilih toko (hanya untuk hasil search) -->
        <div id="noShopSelectedWarning" style="display:none; background:rgba(239,68,68,0.1); border:1px solid #ef4444; border-radius:10px; padding:10px; margin-bottom:16px;">
            <i class="fas fa-exclamation-triangle" style="color:#ef4444;"></i>
            <span style="color:#ef4444; font-size:11px; margin-left:8px;">Silakan pilih TOKO dari hasil pencarian terlebih dahulu!</span>
        </div>
        
        <!-- Search Input dengan Tombol Cari-->
        <div style="display: flex; gap: 10px; margin-bottom: 12px;">
            <input type="text" id="brandSearchInput" placeholder="Contoh: lacera, hanasui, scarlett" 
                   style="flex: 1; padding:12px; background:#0f1420; border:1px solid #2a3346; border-radius:12px; color:#e2f0e8; font-size:14px;">
            <button id="searchBrandBtn" style="background:#8b5cf6; color:white; border:none; padding:0 20px; border-radius:12px; cursor:pointer; font-weight:600;" disabled>
                <i class="fas fa-search"></i> Cari
            </button>
        </div>
         
            
        <!-- Search Results Dropdown -->
        <div id="searchResultsDropdown" style="display:none; background:#0f1420; border:1px solid #2a3346; border-radius:12px; margin-bottom:16px; max-height:350px; overflow-y:auto;">
            <div style="text-align:center; padding:20px; color:#9aaebe;">Belum ada pencarian. Ketik nama brand lalu klik Cari.</div>
        </div>
        
        <div id="selectedItemDisplay" style="display:none; margin-top:16px; background:rgba(74,222,128,0.1); border-radius:12px; padding:12px; margin-bottom:16px;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <span style="color:#9aaebe; font-size:10px;">Dipilih:</span>
                    <div id="selectedItemName" style="color:#e2f0e8; font-weight:600; margin-top:4px;"></div>
                    <div id="selectedItemType" style="font-size:10px; color:#8b5cf6; margin-top:2px;"></div>
                    <div id="commissionInfo" style="font-size:10px; color:#fbbf24; margin-top:4px; display:none;"></div>
                </div>
                <button id="clearSelectionBtn" style="background:#1e293b; color:#ef4444; border:1px solid #ef4444; padding:4px 12px; border-radius:20px; cursor:pointer; font-size:11px;">
                    <i class="fas fa-times"></i> Hapus
                </button>
            </div>
        </div>
        
        <div>
            <label><i class="fas fa-store"></i> Nama Brand *</label>
            <input type="text" id="brandNameDashboard" placeholder="Nama brand akan otomatis terisi dari hasil pencarian atau bisa diisi manual" 
                   style="width:100%; padding:10px; background:#0f1420; border:1px solid #2a3346; border-radius:10px; color:#e2f0e8;">
            
            <label><i class="fas fa-tag"></i> Kategori</label>
            <select id="brandCategoryDashboard" style="width:100%; padding:10px; background:#0f1420; border:1px solid #2a3346; border-radius:10px; color:#e2f0e8;">
                <option value="BEAUTY">Beauty</option>
                <option value="ELECTRONICS">Elektronik</option>
                <option value="FASHION">Fashion</option>
                <option value="FOOD">Makanan</option>
                <option value="OTHER">Lainnya</option>
            </select>
            
            <label><i class="fab fa-whatsapp"></i> WhatsApp <span id="whatsappStatus" style="font-size:10px; color:#fbbf24; margin-left:8px;"></span></label>
            <div style="display:flex; gap:8px; align-items:center;">
                <input type="tel" id="brandWhatsappDashboard" placeholder="+62 812 3456 7890" 
                       style="flex:1; padding:10px; background:#0f1420; border:1px solid #2a3346; border-radius:10px; color:#e2f0e8;">
                <button type="button" id="refreshContactBtn" 
                        style="background:#8b5cf6; color:white; border:none; padding:8px 12px; border-radius:10px; cursor:pointer; font-size:11px;" 
                        title="Cari ulang kontak">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            
            <label><i class="fas fa-envelope"></i> Email</label>
            <input type="email" id="brandEmailDashboard" placeholder="Email brand (opsional)" 
                   style="width:100%; padding:10px; background:#0f1420; border:1px solid #2a3346; border-radius:10px; color:#e2f0e8;">
            
            <input type="hidden" id="sellerIdHidden" value="">
            <input type="hidden" id="selectedCommissionRate" value="">
            <input type="hidden" id="selectedSalesCount" value="">
        </div>
        
        <div style="margin-top:16px; padding:10px; background:#1a1f2e; border-radius:10px; font-size:11px; color:#9aaebe;">
            <i class="fas fa-info-circle"></i> <strong>Tips:</strong>
            <ul style="margin:5px 0 0 20px; padding:0;">
                <li>Masukkan nama brand lalu klik tombol <strong>"Cari"</strong> untuk mencari dari TikTok</li>
                <li>Pilih <strong>TOKO</strong> dari hasil pencarian untuk auto-fill data brand (komisi, dll)</li>
                <li>Atau <strong>isi manual langsung</strong> di form bawah tanpa perlu mencari</li>
                <li>Jika memilih manual, isi semua data lalu klik Simpan</li>
            </ul>
        </div>
        
        <div class="flex-buttons" style="margin-top:20px;">
            <button id="saveNewBrandBtnDashboard" style="background:#4ade80; color:#0a0e17; flex:1; padding:12px; border-radius:40px; border:none; cursor:pointer; font-weight:600;">
                <i class="fas fa-save"></i> Simpan Brand
            </button>
            <button id="cancelSearchBrandBtn" style="background:#1e293b; color:#cbd5e6; flex:1; padding:12px; border-radius:40px; border:1px solid #2a3346; cursor:pointer;">
                Batal
            </button>
        </div>
    `;
    
    openModalDashboard();
    
    // ========== GET DOM ELEMENTS ==========
    const searchInput = document.getElementById('brandSearchInput');
    const searchBtn = document.getElementById('searchBrandBtn');
    const dropdown = document.getElementById('searchResultsDropdown');
    
    if (!searchInput || !searchBtn) {
        console.error('Search elements not found');
        return;
    }
    
    // ========== SEARCH BUTTON CLICK ==========
    searchBtn.addEventListener('click', async () => {
        const keyword = searchInput.value.trim();
        
        if (keyword.length < 2) {
            showToastInModal('Minimal 2 karakter untuk mencari', 'error');
            return;
        }
        
        searchBtn.disabled = true;
        searchBtn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Mencari...';
        
        if (dropdown) {
            dropdown.style.display = 'block';
            dropdown.innerHTML = '<div style="text-align:center; padding:20px; color:#9aaebe;"><i class="fas fa-spinner fa-pulse"></i> Mencari data brand...</div>';
        }
        
        const results = await searchBrandProducts(keyword);
        
        searchBtn.disabled = false;
        searchBtn.innerHTML = '<i class="fas fa-search"></i> Cari';
        
        displaySearchResultsInDropdown(results);
    });
    
    // Enter key di input search juga bisa memicu search
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchBtn.click();
        }
    });
    
    // Hide dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (searchInput && dropdown && searchBtn && 
            !searchInput.contains(e.target) && 
            !dropdown.contains(e.target) && 
            !searchBtn.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
    
    // ========== CLEAR SELECTION ==========
    const clearBtn = document.getElementById('clearSelectionBtn');
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            const selectedDisplay = document.getElementById('selectedItemDisplay');
            const brandNameInput = document.getElementById('brandNameDashboard');
            const sellerIdHidden = document.getElementById('sellerIdHidden');
            const productIdHidden = document.getElementById('selectedProductIdHidden');
            const whatsappInput = document.getElementById('brandWhatsappDashboard');
            const emailInput = document.getElementById('brandEmailDashboard');
            const whatsappStatus = document.getElementById('whatsappStatus');
            const searchInput = document.getElementById('brandSearchInput');
            const commissionInfo = document.getElementById('commissionInfo');
            const noShopWarning = document.getElementById('noShopSelectedWarning');
            
            if (selectedDisplay) selectedDisplay.style.display = 'none';
            if (brandNameInput) brandNameInput.value = '';
            if (sellerIdHidden) sellerIdHidden.value = '';
            if (productIdHidden) productIdHidden.value = '';
            if (whatsappInput) whatsappInput.value = '';
            if (emailInput) emailInput.value = '';
            if (whatsappStatus) whatsappStatus.innerHTML = '';
            if (searchInput) searchInput.value = '';
            if (commissionInfo) commissionInfo.style.display = 'none';
            if (noShopWarning) noShopWarning.style.display = 'none';
            
            if (dropdown) {
                dropdown.innerHTML = '<div style="text-align:center; padding:20px; color:#9aaebe;">Belum ada pencarian. Ketik nama brand lalu klik Cari.</div>';
                dropdown.style.display = 'none';
            }
            
            currentSelectedShop = null;
            currentSelectedProduct = null;
            
            showToastInModal('Pilihan dihapus, silakan cari ulang atau input manual', 'info');
        });
    }
    
    // ========== REFRESH CONTACT BUTTON ==========
    const refreshBtn = document.getElementById('refreshContactBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', async () => {
            const sellerId = document.getElementById('sellerIdHidden')?.value;
            const brandName = document.getElementById('brandNameDashboard')?.value;
            
            if (!sellerId || sellerId === '') {
                showToastInModal('Pilih produk atau toko terlebih dahulu dari hasil pencarian', 'error');
                return;
            }
            
            refreshBtn.disabled = true;
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i>';
            
            showToastInModal('🔍 Mencari ulang kontak brand...', 'info');
            
            const contactResult = await fetchBrandContact(sellerId, brandName);
            
            const whatsappInput = document.getElementById('brandWhatsappDashboard');
            const emailInput = document.getElementById('brandEmailDashboard');
            const whatsappStatus = document.getElementById('whatsappStatus');
            
            if (contactResult.success && contactResult.whatsapp) {
                if (whatsappInput) whatsappInput.value = contactResult.whatsapp;
                if (emailInput) emailInput.value = contactResult.email || '';
                if (whatsappStatus) {
                    whatsappStatus.innerHTML = '(Auto-ditemukan)';
                    whatsappStatus.style.color = '#4ade80';
                }
                showToastInModal(`Kontak ditemukan! WhatsApp: ${contactResult.whatsapp}`, 'success');
            } else {
                if (whatsappStatus) {
                    whatsappStatus.innerHTML = ' (Tidak ditemukan - isi manual)';
                    whatsappStatus.style.color = '#fbbf24';
                }
                showToastInModal('Kontak tidak ditemukan, silakan isi manual', 'warning');
            }
            
            refreshBtn.disabled = false;
            refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i>';
        });
    }
    
    // ========== SAVE BUTTON (DENGAN VALIDASI MANUAL) ==========
    const saveBtn = document.getElementById('saveNewBrandBtnDashboard');
    if (saveBtn) {
        const newSaveBtn = saveBtn.cloneNode(true);
        saveBtn.parentNode.replaceChild(newSaveBtn, saveBtn);
        
        newSaveBtn.addEventListener('click', async () => {
            const brandName = document.getElementById('brandNameDashboard')?.value.trim();
            const category = document.getElementById('brandCategoryDashboard')?.value;
            const whatsappNumber = document.getElementById('brandWhatsappDashboard')?.value.trim();
            const email = document.getElementById('brandEmailDashboard')?.value.trim();
            const sellerId = document.getElementById('sellerIdHidden')?.value.trim();
            const commissionRate = document.getElementById('selectedCommissionRate')?.value;
            const openCommission = document.getElementById('selectedOpenCommission')?.value;
            
            console.log('Saving brand with data:', {
                brand_name: brandName,
                category: category,
                whatsapp_number: whatsappNumber,
                email: email,
                seller_id: sellerId,
                commission: commissionRate || 0,
                open_commission_rate: openCommission || 0
            });
            
            // Validasi dasar
            if (!brandName) { 
                showToastInModal('Nama brand harus diisi!', 'error'); 
                return; 
            }
            
            if (!category || category === '') { 
                showToastInModal('Kategori harus dipilih!', 'error'); 
                return; 
            }
            
            // 🔥 PERUBAHAN: Validasi seller_id hanya jika user memilih dari hasil pencarian
            // Jika brandName diisi manual dan sellerId kosong, tetap bisa simpan
            const warningDiv = document.getElementById('noShopSelectedWarning');
            
            // Cek apakah ada hasil pencarian yang dipilih (selectedDisplay terlihat)
            const selectedDisplay = document.getElementById('selectedItemDisplay');
            const isFromSearch = selectedDisplay && selectedDisplay.style.display === 'block';
            
            if (isFromSearch && (!sellerId || sellerId === '')) {
                if (warningDiv) warningDiv.style.display = 'block';
                showToastInModal('Silakan pilih TOKO dari hasil pencarian terlebih dahulu!', 'error');
                return;
            } else {
                if (warningDiv) warningDiv.style.display = 'none';
            }
            
            const btn = newSaveBtn;
            btn.disabled = true; 
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            
            try {
                const formData = new URLSearchParams();
                formData.append('brand_name', brandName);
                formData.append('category', category);
                formData.append('whatsapp_number', whatsappNumber || '');
                formData.append('email', email || '');
                formData.append('seller_id', sellerId || '');
                formData.append('commission', commissionRate || '0');
                formData.append('open_commission_rate', openCommission || '0');
                
                const response = await fetch(baseUrlDashboard + 'bd/scout_match_brand', { 
                    method: 'POST', 
                    headers: { 
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    }, 
                    body: formData.toString()
                });
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Server error response:', errorText);
                    throw new Error(`Server error: ${response.status}`);
                }
                
                const result = await response.json();
                
                if (result.success) { 
                    closeModalDashboard();
                    showToastInModal(result.message, result.warning ? 'warning' : 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showToastInModal(result.message || 'Gagal menyimpan brand', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save"></i> Simpan Brand';
                }
            } catch (err) {
                console.error('Error saving brand:', err);
                showToastInModal('Terjadi kesalahan: ' + err.message, 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save"></i> Simpan Brand';
            }
        });
    }
    
    // ========== CANCEL BUTTON ==========
    const cancelBtn = document.getElementById('cancelSearchBrandBtn');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeModalDashboard);
    }
}
// ========== RECENT ORDERS PAGINATION ==========
const allOrdersDashboard = <?= json_encode(array_slice(array_reverse($orders), 0, 100)) ?>;

function renderOrdersDashboard() {
    const tbody = document.getElementById('recentOrdersBodyDashboard');
    if (!tbody) return;
    const start = currentPageDashboard * perPageDashboard;
    const end = start + perPageDashboard;
    const pageOrders = allOrdersDashboard.slice(start, end);
    
    tbody.innerHTML = '';
    pageOrders.forEach(order => {
        const orderDate = order.order_date_local || (order.order_time ? new Date(order.order_time).toISOString().split('T')[0] : '-');
        tbody.innerHTML += `<tr><td>${orderDate}</td><td>${escapeHtml(order.product_name?.substring(0, 45) || '-')}</td><td>${escapeHtml(order.creator_username || 'Unknown')}</td><td class="gmv-cell">Rp ${formatNumber(order.gmv)}</td><td>Rp ${formatNumber(order.estimated_commission)}</td></tr>`;
    });
    
    const orderRangeSpan = document.getElementById('orderRangeDashboard');
    if (orderRangeSpan) orderRangeSpan.innerText = `${start+1}-${Math.min(end, allOrdersDashboard.length)}`;
    const prevBtn = document.getElementById('prevPageBtnDashboard');
    if (prevBtn) prevBtn.disabled = currentPageDashboard === 0;
    const nextBtn = document.getElementById('nextPageBtnDashboard');
    if (nextBtn) nextBtn.disabled = end >= allOrdersDashboard.length;
}

// ========== SEARCH FUNCTIONS ==========
function initSearchTask(searchInputId, containerId, countId) {
    const searchInput = document.getElementById(searchInputId);
    if (!searchInput) return;
    searchInput.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const items = document.querySelectorAll('#' + containerId + ' .brand-item-dashboard');
        let visibleCount = 0;
        items.forEach(item => {
            const brandName = item.getAttribute('data-brand-name') || '';
            if (brandName.toLowerCase().indexOf(searchTerm) > -1) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });
        const countSpan = document.getElementById(countId);
        if (countSpan) countSpan.innerText = visibleCount;
    });
}

// ========== EVENT LISTENERS ==========
document.addEventListener('DOMContentLoaded', () => {
    updateStageUIDashboard();
    
        // 🔥 SEARCH TASKS DENGAN API (mencari ke database)
    initSearchTaskWithAPI('searchHuntingDashboard', 'huntingItemsContainerDashboard', 'huntingCountDashboard', 'search_hunting_brands');
    initSearchTaskWithAPI('searchFollowupDashboard', 'followupItemsContainerDashboard', 'followupCountDashboard', 'search_followup_brands');
    initSearchTaskWithAPI('searchSetupDashboard', 'setupItemsContainerDashboard', 'setupCountDashboard', 'search_setup_brands');
    initSearchTaskWithAPI('searchMonitoringDashboard', 'monitoringItemsContainerDashboard', 'monitoringCountDashboard', 'search_monitoring_brands');
    
    // Brand item click
document.querySelectorAll('.brand-item-dashboard').forEach(item => {
    item.addEventListener('click', async (e) => {
        if (e.target.closest('.remove-existing-product-dashboard') || e.target.closest('.remove-new-product-dashboard')) return;
        
        const brandId = item.getAttribute('data-brand-id');
        const brandName = item.getAttribute('data-brand-name');
        const stage = parseInt(item.getAttribute('data-stage'));
        
        // 🔥 KHUSUS TASK 2: CEK APAKAH BISA DIKLIK
        if (stage === 2) {
            const isClickable = item.getAttribute('data-is-clickable') === 'true';
            if (!isClickable) {
                showToastInModal(' Brand sedang menunggu registrasi campaign. Tidak dapat difollow up sampai brand registrasi campaign terlebih dahulu.', 'warning');
                return; // ✅ LANGSUNG RETURN, TIDAK BUKA MODAL
            }
            showTask2FollowUpModal(brandId, brandName);
        } 
        else if (stage === 1) {
            showTask1DetailDashboard(brandId, brandName);
        }
        else if (stage === 3) {
            showTask3SetupModalWithRecommendations(brandId, brandName);
        }
        else if (stage === 4) {
            showTask4MonitoringModalDashboard(brandId, brandName);
        }
    });
});

    // Task buttons
    const huntingBtn = document.querySelector('.task-btn-dashboard[data-action="hunting"]');
    if (huntingBtn) {
        const newHuntingBtn = huntingBtn.cloneNode(true);
        huntingBtn.parentNode.replaceChild(newHuntingBtn, huntingBtn);
        newHuntingBtn.addEventListener('click', (e) => { e.preventDefault(); showNewBrandModal(); });
    }
    
    const setupBtn = document.querySelector('.task-btn-dashboard[data-action="setup"]');
    if (setupBtn) {
        const newSetupBtn = setupBtn.cloneNode(true);
        setupBtn.parentNode.replaceChild(newSetupBtn, setupBtn);
        newSetupBtn.addEventListener('click', () => {
            const firstItem = document.querySelector('#setupItemsContainerDashboard .brand-item-dashboard');
            if (firstItem) showTask3SetupModalWithRecommendations(firstItem.getAttribute('data-brand-id'), firstItem.getAttribute('data-brand-name'));
            else showToastInModal('Tidak ada brand yang siap setup', 'error');
        });
    }
    
    const monitoringBtn = document.querySelector('.task-btn-dashboard[data-action="monitoring"]');
    if (monitoringBtn) {
        const newMonitoringBtn = monitoringBtn.cloneNode(true);
        monitoringBtn.parentNode.replaceChild(newMonitoringBtn, monitoringBtn);
        newMonitoringBtn.addEventListener('click', () => {
            const firstItem = document.querySelector('#monitoringItemsContainerDashboard .brand-item-dashboard');
            if (firstItem) showTask4MonitoringModalDashboard(firstItem.getAttribute('data-brand-id'), firstItem.getAttribute('data-brand-name'));
            else showToastInModal('Tidak ada campaign aktif', 'error');
        });
    }
    
    // Scout button
    const scoutBtn = document.getElementById('scoutBtnDashboard');
    if (scoutBtn) {
        const newScoutBtn = scoutBtn.cloneNode(true);
        scoutBtn.parentNode.replaceChild(newScoutBtn, scoutBtn);
        newScoutBtn.addEventListener('click', (e) => { e.preventDefault(); showNewBrandModal(); });
    }
    
    // Tab switching
    document.querySelectorAll('.tab-btn-dashboard').forEach(btn => {
        btn.addEventListener('click', () => {
            const tabId = btn.getAttribute('data-tab');
            document.querySelectorAll('.tab-content-dashboard').forEach(c => c.classList.remove('active'));
            document.querySelectorAll('.tab-btn-dashboard').forEach(b => b.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            btn.classList.add('active');
            document.querySelectorAll('.mobile-nav-item-dashboard').forEach(nav => {
                if (nav.getAttribute('data-tab') === tabId) nav.classList.add('active');
                else nav.classList.remove('active');
            });
        });
    });
    
    // Mobile nav
    document.querySelectorAll('.mobile-nav-item-dashboard').forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const tabId = item.getAttribute('data-tab');
            const tabBtn = document.querySelector(`.tab-btn-dashboard[data-tab="${tabId}"]`);
            if (tabBtn) tabBtn.click();
        });
    });
    
    // Pagination
    const prevBtn = document.getElementById('prevPageBtnDashboard');
    if (prevBtn) prevBtn.addEventListener('click', () => { if (currentPageDashboard > 0) { currentPageDashboard--; renderOrdersDashboard(); } });
    const nextBtn = document.getElementById('nextPageBtnDashboard');
    if (nextBtn) nextBtn.addEventListener('click', () => { if ((currentPageDashboard+1)*perPageDashboard < allOrdersDashboard.length) { currentPageDashboard++; renderOrdersDashboard(); } });
    renderOrdersDashboard();
    
    // Search tasks
    initSearchTask('searchHuntingDashboard', 'huntingItemsContainerDashboard', 'huntingCountDashboard');
    initSearchTask('searchFollowupDashboard', 'followupItemsContainerDashboard', 'followupCountDashboard');
    initSearchTask('searchSetupDashboard', 'setupItemsContainerDashboard', 'setupCountDashboard');
    initSearchTask('searchMonitoringDashboard', 'monitoringItemsContainerDashboard', 'monitoringCountDashboard');
    
    // Leaderboard refresh
    const refreshLeaderboardBtn = document.getElementById('refreshLeaderboardBtn');
    if (refreshLeaderboardBtn) {
        refreshLeaderboardBtn.addEventListener('click', function() {
            this.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Refreshing...';
            this.disabled = true;
            fetch(baseUrlDashboard + 'bd/refresh_leaderboard', { method: 'POST', headers: { 'Content-Type': 'application/json' } })
                .then(response => response.json())
                .then(data => {
                    if (data.success) { showToastInModal('Leaderboard berhasil diperbarui!', 'success'); setTimeout(() => location.reload(), 1000); }
                    else { showToastInModal('Gagal refresh leaderboard', 'error'); this.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh'; this.disabled = false; }
                })
                .catch(() => { showToastInModal('Gagal refresh leaderboard', 'error'); this.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh'; this.disabled = false; });
        });
    }
});
// ========== TASK 1: HUNTING DETAIL (FIX WHATSAPP REDIRECT) ==========
let currentMessageTemplate = null;
let availableTemplates = [];
async function showTask1DetailDashboard(brandId, brandName) {
    const brand = await getBrandDetailDashboard(brandId);
    if (!brand) { showToastInModal('Gagal mengambil detail brand', 'error'); return; }
    
    // Ambil template
    await loadAvailableTemplates();
    
    let selectedTemplateId = null;
    let templateMessage = '';
    let selectedBannerUrl = '';
    let selectedBannerTitle = '';
    let selectedBannerDesc = '';
    let defaultMessage = `Hi ${brand.name} Team,

Kami dari Toopai ingin menawarkan kerjasama affiliate untuk produk Anda.

Tertarik untuk diskusi lebih lanjut?

Best regards,
Toopai Team`;
    
    // Filter template untuk Task 1 Hunting
    const huntingTemplates = availableTemplates.filter(t => t.type === 'bd' && t.task == 1);
    
    // Jika ada template, ambil yang pertama sebagai default
    if (huntingTemplates.length > 0) {
        const defaultTemplate = huntingTemplates[0];
        selectedTemplateId = defaultTemplate.id;
        templateMessage = defaultTemplate.message_text
            .replace(/{brand_name}/g, brand.name)
            .replace(/{commission}/g, brand.proposed_commission || '10');
        selectedBannerUrl = defaultTemplate.banner_url || '';
        selectedBannerTitle = defaultTemplate.banner_title || '';
        selectedBannerDesc = defaultTemplate.banner_description || '';
    } else {
        templateMessage = defaultMessage;
    }
    
    const modalTitleElem = document.getElementById('modalTitleDashboard'); 
    const modalBodyElem = document.getElementById('modalBodyDashboard');
    
    // Build banner options HTML
    let bannerOptionsHtml = '';
    if (huntingTemplates.length > 0) {
        bannerOptionsHtml = `
            <div style="margin-bottom: 16px;">
                <label style="color: #9aaebe; font-size: 11px; margin-bottom: 8px; display: block;">
                    <i class="fas fa-image"></i> Pilih Banner (Klik untuk mengganti)
                </label>
                <div id="bannerOptionsContainer" style="display: flex; gap: 12px; overflow-x: auto; padding-bottom: 8px; margin-bottom: 12px;">
                    ${huntingTemplates.map(tmpl => `
                        <div class="banner-option" 
                             data-template-id="${tmpl.id}"
                             data-message="${escapeHtml(tmpl.message_text)}"
                             data-banner-url="${tmpl.banner_url || ''}"
                             data-banner-title="${escapeHtml(tmpl.banner_title || '')}"
                             data-banner-desc="${escapeHtml(tmpl.banner_description || '')}"
                             style="min-width: 140px; background: ${selectedTemplateId == tmpl.id ? 'rgba(139,92,246,0.2)' : '#0f1420'}; border-radius: 12px; padding: 8px; text-align: center; cursor: pointer; border: 2px solid ${selectedTemplateId == tmpl.id ? '#8b5cf6' : '#2a3346'}; transition: all 0.2s;">
                            ${tmpl.banner_url ? 
                                `<img src="${tmpl.banner_url}" style="width: 100%; height: 80px; border-radius: 8px; object-fit: cover; margin-bottom: 6px;" onerror="this.style.display='none'">` : 
                                `<div style="width: 100%; height: 80px; background: #1e293b; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 6px;">
                                    <i class="fas fa-envelope" style="font-size: 32px; color: #8b5cf6;"></i>
                                </div>`
                            }
                            <div style="font-size: 10px; color: #e2f0e8; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                ${escapeHtml(tmpl.title || 'Template')}
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }
    
    // Build selected banner display
    let selectedBannerHtml = '';
    if (selectedBannerUrl) {
        selectedBannerHtml = `
            <div id="selectedBannerDisplay" style="background: linear-gradient(135deg, #1a1030, #13111f); border-radius: 14px; padding: 12px; margin-bottom: 12px; display: flex; gap: 12px; align-items: center; border: 1px solid #8b5cf6;">
                <img id="selectedBannerImage" src="${selectedBannerUrl}" style="width: 60px; height: 60px; border-radius: 12px; object-fit: cover;" onerror="this.style.display='none'">
                <div>
                    <div id="selectedBannerTitle" style="color: #e2f0e8; font-weight: 600; font-size: 13px;">${escapeHtml(selectedBannerTitle || ' Toopai Affiliate Program')}</div>
                    <div id="selectedBannerDesc" style="color: #9aaebe; font-size: 10px; margin-top: 4px;">${escapeHtml(selectedBannerDesc || 'Gabung dengan jaringan affiliate Toopai!')}</div>
                </div>
            </div>
        `;
    }
    
    modalTitleElem.innerHTML = `<i class="fas fa-building"></i> Detail Brand: ${brand.name}`;
    modalBodyElem.innerHTML = `
        <div style="display:grid; gap:10px; margin-bottom:16px;">
            <div style="display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #2a3346;">
                <span style="color:#9aaebe;">Kategori:</span> <span>${brand.category || '-'}</span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #2a3346;">
                <span style="color:#9aaebe;">WhatsApp:</span> 
                <span style="color: ${brand.whatsapp_number ? '#4ade80' : '#ef4444'};">
                    ${brand.whatsapp_number || 'Tidak tersedia'}
                </span>
                ${!brand.whatsapp_number ? `
                    <button id="editPhoneFromModal" class="btn-edit-wa" style="background: #fbbf24; color: #0a0e17; border: none; padding: 2px 8px; border-radius: 12px; cursor: pointer; font-size: 10px; margin-left: 8px;">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                ` : ''}
            </div>

            <div style="display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #2a3346;">
                <span style="color:#9aaebe;">Input oleh:</span> <span><i class="fas fa-user"></i> ${brand.bd_username || brand.input_by || '-'}</span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:6px 0;">
                <span style="color:#9aaebe;">Status:</span> <span class="badge-dashboard badge-pending">${brand.status}</span>
            </div>
        </div>
        
        ${bannerOptionsHtml}
        
        <div id="selectedBannerContainer" style="display: ${selectedBannerUrl ? 'block' : 'none'};">
            ${selectedBannerHtml}
        </div>
        
        <label><i class="fab fa-whatsapp"></i> Pesan ke Brand</label>
        <textarea id="huntingMessageDashboard" rows="8" style="width:100%; padding:10px; background:#0f1420; border:1px solid #2a3346; border-radius:12px; color:#e2f0e8; font-size:12px; font-family:monospace;">${escapeHtml(templateMessage)}</textarea>
        
        <div id="noWhatsAppWarning" style="display: ${brand.whatsapp_number ? 'none' : 'block'}; background: rgba(239,68,68,0.15); border-radius: 10px; padding: 10px; margin-top: 12px; border-left: 3px solid #ef4444;">
            <i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i>
            <span style="color: #ef4444; font-size: 11px; margin-left: 8px;">Nomor WhatsApp tidak tersedia! Silakan update nomor WhatsApp terlebih dahulu.</span>
        </div>
        
        <div style="display:flex; gap:10px; margin-top:16px; flex-wrap:wrap;">
            <button id="dealBtnDashboard" class="btn-deal" style="flex:1; background: #10b981; color:white; font-weight:600; padding:12px; border-radius:40px; border:none; cursor:pointer;" ${!brand.whatsapp_number ? 'disabled' : ''}>
                <i class="fas fa-handshake"></i>Lanjutkan
            </button>
        </div>
        <div style="margin-top:8px; font-size:10px; color:#9aaebe; text-align:center;">
            <i class="fas fa-info-circle"></i> Klik "Lanjutkan" akan membuka WhatsApp dan memindahkan brand ke FOLLOW UP.<br>
             Banner akan dikirim sebagai link, silakan download dan kirim manual ke brand.
        </div>
    `;
    
    openModalDashboard();
    ensureCloseButtonWorks();
    
    // ========== BANNER SELECTION HANDLER ==========
    document.querySelectorAll('.banner-option').forEach(option => {
        const newOption = option.cloneNode(true);
        option.parentNode.replaceChild(newOption, option);
        
        newOption.addEventListener('click', () => {
            const templateId = newOption.getAttribute('data-template-id');
            const messageText = newOption.getAttribute('data-message');
            const bannerUrl = newOption.getAttribute('data-banner-url');
            const bannerTitle = newOption.getAttribute('data-banner-title');
            const bannerDesc = newOption.getAttribute('data-banner-desc');
            const commission = document.getElementById('commissionInput')?.value || brand.proposed_commission || 10;
            
            // Update selected style
            document.querySelectorAll('.banner-option').forEach(opt => {
                opt.style.background = '#0f1420';
                opt.style.borderColor = '#2a3346';
            });
            newOption.style.background = 'rgba(139,92,246,0.2)';
            newOption.style.borderColor = '#8b5cf6';
            
            // Update message
            const finalMessage = messageText
                .replace(/{brand_name}/g, brand.name)
                .replace(/{commission}/g, commission);
            document.getElementById('huntingMessageDashboard').value = finalMessage;
            
            // Update banner display
            const selectedBannerContainer = document.getElementById('selectedBannerContainer');
            if (bannerUrl) {
                selectedBannerContainer.innerHTML = `
                    <div style="background: linear-gradient(135deg, #1a1030, #13111f); border-radius: 14px; padding: 12px; margin-bottom: 12px; display: flex; gap: 12px; align-items: center; border: 1px solid #8b5cf6;">
                        <img src="${bannerUrl}" style="width: 60px; height: 60px; border-radius: 12px; object-fit: cover;" onerror="this.style.display='none'">
                        <div>
                            <div style="color: #e2f0e8; font-weight: 600; font-size: 13px;">${escapeHtml(bannerTitle || ' Toopai Affiliate Program')}</div>
                            <div style="color: #9aaebe; font-size: 10px;">${escapeHtml(bannerDesc || 'Gabung dengan jaringan affiliate Toopai!')}</div>
                        </div>
                    </div>
                `;
                selectedBannerContainer.style.display = 'block';
            } else {
                selectedBannerContainer.style.display = 'none';
            }
            
            // Store selected banner URL for sending
            currentSelectedBannerUrl = bannerUrl;
        });
    });
    
    // Update komisi ketika berubah
    const commissionInput = document.getElementById('commissionInput');
    if (commissionInput) {
        commissionInput.addEventListener('change', async () => {
            const newCommission = commissionInput.value;
            const messageTextarea = document.getElementById('huntingMessageDashboard');
            const currentMessage = messageTextarea.value;
            const updatedMessage = currentMessage.replace(/\d+(\.\d+)?%/g, `${newCommission}%`);
            messageTextarea.value = updatedMessage;
            
            await fetch(baseUrlDashboard + 'bd/update_brand_data', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    brand_id: brand.id,
                    commission: newCommission
                })
            });
        });
    }
    
    // Edit phone
    const editPhoneBtn = document.getElementById('editPhoneFromModal');
    if (editPhoneBtn) {
        editPhoneBtn.addEventListener('click', () => {
            showQuickEditPhoneModal(brand.id, brand.name, brand.whatsapp_number);
        });
    }
    
    // DEAL BUTTON
    const dealBtn = document.getElementById('dealBtnDashboard');
    if (dealBtn) {
        const newDealBtn = dealBtn.cloneNode(true);
        dealBtn.parentNode.replaceChild(newDealBtn, dealBtn);
        
        newDealBtn.addEventListener('click', async () => {
            const message = document.getElementById('huntingMessageDashboard').value;
            const newCommission = document.getElementById('commissionInput')?.value;
            const selectedBanner = document.querySelector('.banner-option[style*="rgba(139,92,246,0.2)"]');
            const bannerUrl = selectedBanner?.getAttribute('data-banner-url') || '';
            
            if (!brand.whatsapp_number) {
                showToastInModal('Nomor WhatsApp tidak tersedia! Silakan update nomor WA terlebih dahulu.', 'error');
                return;
            }
            
            if (!confirm(`Anda yakin ingin deal dengan ${brand.name}?\n\nBrand akan pindah ke FOLLOW UP (Task 2).`)) return;
            
            // Update komisi jika berubah
            if (newCommission && newCommission != brand.proposed_commission) {
                await fetch(baseUrlDashboard + 'bd/update_brand_data', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        brand_id: brand.id,
                        commission: newCommission
                    })
                });
            }
            
            // Update status ke FOLLOW_UP
            const response = await fetch(baseUrlDashboard + 'bd/update_brand_status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    brand_id: brand.id,
                    status: 'FOLLOW_UP'
                })
            });
            const result = await response.json();
            
            if (result.success) {
                // Buka WhatsApp dengan pesan + link banner
                let finalMessage = message;
                if (bannerUrl) {
                    finalMessage += `\n\n *Banner Kerjasama:*\n${bannerUrl}\n(Silakan download gambar di atas untuk melihat banner)`;
                }
                
                sendWhatsAppDealDashboard(brand.id, brand.whatsapp_number, finalMessage, bannerUrl);
                closeModalDashboard();
                showToastInModal('Deal berhasil! Brand pindah ke FOLLOW UP (Task 2).', 'success');
                setTimeout(() => location.reload(), 2000);
            } else {
                showToastInModal(result.message || 'Gagal melakukan deal', 'error');
            }
        });
    }
}

// Variable untuk menyimpan banner yang dipilih
let currentSelectedBannerUrl = '';
// 🔥 LOAD AVAILABLE TEMPLATES
async function loadAvailableTemplates() {
    try {
        const response = await fetch(baseUrlDashboard + 'message_template/get_all?type=bd');
        const result = await response.json();
        if (result.success) {
            availableTemplates = result.data;
        }
    } catch (error) {
        console.error('Error loading templates:', error);
        availableTemplates = [];
    }
}

function showQuickEditPhoneModal(brandId, brandName, currentPhone) {
    const modalTitleElem = document.getElementById('modalTitleDashboard');
    const modalBodyElem = document.getElementById('modalBodyDashboard');
    
    modalTitleElem.innerHTML = `<i class="fab fa-whatsapp"></i> Update WhatsApp - ${escapeHtml(brandName)}`;
    modalBodyElem.innerHTML = `
        <div style="background:rgba(251,191,36,0.1); border-radius:14px; padding:12px; margin-bottom:16px; border-left:3px solid #fbbf24;">
            <p style="color:#fbbf24; font-size:12px;"><i class="fas fa-exclamation-triangle"></i> Nomor WhatsApp diperlukan untuk mengirim pesan deal ke brand</p>
        </div>
        
        <label><i class="fab fa-whatsapp"></i> Nomor WhatsApp</label>
        <input type="tel" id="quickEditPhone" placeholder="+62 812 3456 7890" value="${escapeHtml(currentPhone || '')}" 
            style="width:100%; padding:12px; background:#0f1420; border:2px solid #fbbf24; border-radius:12px; color:#e2f0e8; font-size:14px; margin-bottom:12px;">
        <div style="font-size:11px; color:#9aaebe; margin-bottom:16px;">
            <i class="fas fa-info-circle"></i> Format: +628123456789 atau 08123456789
        </div>
        
        <div class="flex-buttons">
            <button id="saveQuickPhoneBtn" style="background:#4ade80; color:#0a0e17; flex:1; padding:12px; border:none; border-radius:12px; cursor:pointer; font-weight:600;">
                <i class="fas fa-save"></i> Simpan
            </button>
            <button id="cancelQuickPhoneBtn" style="background:#1e293b; flex:1; padding:12px; border:1px solid #2a3346; border-radius:12px; cursor:pointer;">
                Batal
            </button>
        </div>
    `;
    openModalDashboard();
    
    document.getElementById('saveQuickPhoneBtn').addEventListener('click', async () => {
        let phone = document.getElementById('quickEditPhone').value.trim();
        
        if (!phone) {
            showToastGlobal('Nomor WhatsApp tidak boleh kosong', 'error');
            return;
        }
        
        // Format phone
        phone = phone.replace(/[^0-9+]/g, '');
        if (phone.startsWith('0')) {
            phone = '62' + phone.substring(1);
        } else if (phone.startsWith('+')) {
            phone = phone.substring(1);
        }
        
        const btn = document.getElementById('saveQuickPhoneBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Menyimpan...';
        
        try {
            const response = await fetch(baseUrlDashboard + 'bd/update_brand_data', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    brand_id: brandId,
                    whatsapp_number: phone
                })
            });
            const result = await response.json();
            
            if (result.success) {
                closeModalDashboard();
                showToastGlobal(`WhatsApp ${brandName} berhasil diupdate!`, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToastGlobal(result.message || 'Gagal update', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save"></i> Simpan';
            }
        } catch (error) {
            showToastGlobal('Error: ' + error.message, 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> Simpan';
        }
    });
    
    document.getElementById('cancelQuickPhoneBtn').addEventListener('click', closeModalDashboard);
}

// ========== EDIT BRAND DATA (dari list item) ==========
function showEditBrandModal(brandId, brandName, currentWhatsapp, currentCommission, currentCategory, currentEmail) {
    console.log('Opening edit modal for:', brandId, brandName);
    
    const modalTitleElem = document.getElementById('modalTitleDashboard');
    const modalBodyElem = document.getElementById('modalBodyDashboard');
    
    if (!modalTitleElem || !modalBodyElem) {
        console.error('Modal elements not found');
        showToastGlobal('Error: Modal tidak ditemukan', 'error');
        return;
    }
    
    modalTitleElem.innerHTML = `<i class="fas fa-edit"></i> Edit Data Brand - ${escapeHtml(brandName)}`;
    modalBodyElem.innerHTML = `
        <div style="background:rgba(139,92,246,0.1); border-radius:14px; padding:12px; margin-bottom:16px;">
            <p style="color:#8b5cf6; font-size:12px;">
                <i class="fas fa-info-circle"></i> Edit data brand yang sedang di-hunt
            </p>
            <p style="color:#9aaebe; font-size:10px; margin-top:4px;">
                Perubahan akan langsung tersimpan dan brand tetap di Task 1 (HUNTING)
            </p>
        </div>
        
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:12px;">
            <div>
                <label style="color:#e2f0e8; font-size:12px; font-weight:500; display:block; margin-bottom:6px;">
                    <i class="fas fa-building"></i> Nama Brand *
                </label>
                <input type="text" id="editBrandName" value="${escapeHtml(brandName)}" 
                    style="width:100%; padding:10px; background:#0f1420; border:1px solid #2a3346; border-radius:10px; color:#e2f0e8; font-size:13px;">
            </div>
            <div>
                <label style="color:#e2f0e8; font-size:12px; font-weight:500; display:block; margin-bottom:6px;">
                    <i class="fas fa-tag"></i> Kategori
                </label>
                <select id="editCategory" style="width:100%; padding:10px; background:#0f1420; border:1px solid #2a3346; border-radius:10px; color:#e2f0e8; font-size:13px;">
                    <option value="">-- Pilih Kategori --</option>
                    <option value="BEAUTY" ${currentCategory === 'BEAUTY' ? 'selected' : ''}>Beauty</option>
                    <option value="ELECTRONICS" ${currentCategory === 'ELECTRONICS' ? 'selected' : ''}>Elektronik</option>
                    <option value="FASHION" ${currentCategory === 'FASHION' ? 'selected' : ''}>Fashion</option>
                    <option value="FOOD" ${currentCategory === 'FOOD' ? 'selected' : ''}>Makanan</option>
                    <option value="HEALTH" ${currentCategory === 'HEALTH' ? 'selected' : ''}>Kesehatan</option>
                    <option value="HOME" ${currentCategory === 'HOME' ? 'selected' : ''}>Rumah Tangga</option>
                    <option value="OTHER" ${currentCategory === 'OTHER' ? 'selected' : ''}>Lainnya</option>
                </select>
            </div>
        </div>
        
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:12px;">
            <div>
                <label style="color:#e2f0e8; font-size:12px; font-weight:500; display:block; margin-bottom:6px;">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </label>
                <input type="tel" id="editWhatsapp" value="${escapeHtml(currentWhatsapp || '')}" 
                    placeholder="+62 812 3456 7890" 
                    style="width:100%; padding:10px; background:#0f1420; border:1px solid #2a3346; border-radius:10px; color:#e2f0e8; font-size:13px;">
            </div>
            <div>
                <label style="color:#e2f0e8; font-size:12px; font-weight:500; display:block; margin-bottom:6px;">
                    <i class="fas fa-percent"></i> Komisi (%)
                </label>
                <input type="number" id="editCommission" value="${currentCommission || 10}" 
                    step="0.5" min="1" max="50" 
                    style="width:100%; padding:10px; background:#0f1420; border:1px solid #2a3346; border-radius:10px; color:#e2f0e8; font-size:13px;">
            </div>
        </div>
        
        <div>
            <label style="color:#e2f0e8; font-size:12px; font-weight:500; display:block; margin-bottom:6px;">
                <i class="fas fa-envelope"></i> Email (Opsional)
            </label>
            <input type="email" id="editEmail" value="${escapeHtml(currentEmail || '')}" 
                placeholder="email@brand.com" 
                style="width:100%; padding:10px; background:#0f1420; border:1px solid #2a3346; border-radius:10px; color:#e2f0e8; font-size:13px;">
        </div>
        
        <div style="margin-top:16px; padding:10px; background:#1a1f2e; border-radius:10px; border-left:3px solid #fbbf24;">
            <div style="font-size:11px; color:#fbbf24;">
                <i class="fas fa-info-circle"></i> Tips:
            </div>
            <ul style="margin:5px 0 0 20px; padding:0; font-size:10px; color:#9aaebe;">
                <li>Nama brand wajib diisi</li>
                <li>Nomor WhatsApp gunakan format +62 atau 08</li>
                <li>Komisi akan digunakan untuk proposal ke brand</li>
            </ul>
        </div>
        
        <div class="flex-buttons" style="margin-top:20px;">
            <button id="saveEditBrandBtn" style="flex:1; background:#4ade80; color:#0a0e17; padding:12px; border-radius:40px; border:none; cursor:pointer; font-weight:600; transition:all 0.2s;">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
            <button id="cancelEditBrandBtn" style="flex:1; background:#1e293b; color:#cbd5e6; padding:12px; border-radius:40px; border:1px solid #2a3346; cursor:pointer; transition:all 0.2s;">
                Batal
            </button>
        </div>
    `;
    
    openModalDashboard();
    
    // ========== EVENT LISTENER SAVE ==========
    const saveBtn = document.getElementById('saveEditBrandBtn');
    if (saveBtn) {
        const newSaveBtn = saveBtn.cloneNode(true);
        saveBtn.parentNode.replaceChild(newSaveBtn, saveBtn);
        
        newSaveBtn.addEventListener('click', async () => {
            const newName = document.getElementById('editBrandName').value.trim();
            const newWhatsapp = document.getElementById('editWhatsapp').value.trim();
            const newCommission = document.getElementById('editCommission').value;
            const newCategory = document.getElementById('editCategory').value;
            const newEmail = document.getElementById('editEmail').value.trim();
            
            // Validasi
            if (!newName) {
                showToastInModal('Nama brand tidak boleh kosong!', 'error');
                document.getElementById('editBrandName').focus();
                return;
            }
            
            if (newWhatsapp && !/^[\+]?[0-9]{8,15}$/.test(newWhatsapp.replace(/[^0-9+]/g, ''))) {
                showToastInModal('Format nomor WhatsApp tidak valid! Gunakan +62 atau 08', 'error');
                document.getElementById('editWhatsapp').focus();
                return;
            }
            
            const btn = document.getElementById('saveEditBrandBtn');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Menyimpan...';
            
            try {
                const formData = new URLSearchParams();
                formData.append('brand_id', brandId);
                formData.append('name', newName);
                formData.append('whatsapp_number', newWhatsapp);
                formData.append('commission', newCommission);
                formData.append('category', newCategory);
                formData.append('email', newEmail);
                
                const response = await fetch(baseUrlDashboard + 'bd/update_brand_data', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData.toString()
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToastInModal(`✅ Data brand "${newName}" berhasil diupdate!`, 'success');
                    closeModalDashboard();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToastInModal(result.message || 'Gagal update data brand', 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (error) {
                console.error('Error updating brand:', error);
                showToastInModal('Terjadi kesalahan: ' + error.message, 'error');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    }
    
    // ========== EVENT LISTENER CANCEL ==========
    const cancelBtn = document.getElementById('cancelEditBrandBtn');
    if (cancelBtn) {
        const newCancelBtn = cancelBtn.cloneNode(true);
        cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
        
        newCancelBtn.addEventListener('click', () => {
            closeModalDashboard();
        });
    }
}

// 🔥 EDIT BRAND BUTTON (di list item)
document.querySelectorAll('.edit-brand-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const brandId = btn.getAttribute('data-brand-id');
        const brandName = btn.getAttribute('data-brand-name');
        const whatsapp = btn.getAttribute('data-whatsapp');
        const commission = btn.getAttribute('data-commission');
        showEditBrandModal(brandId, brandName, whatsapp, commission);
    });
});
// ========== MODAL EDIT TEMPLATE PESAN ==========
function showEditTemplateModal(type, task, stage) {
    const modalTitleElem = document.getElementById('modalTitleDashboard');
    const modalBodyElem = document.getElementById('modalBodyDashboard');
    
    modalTitleElem.innerHTML = '<i class="fas fa-edit"></i> Edit Template Pesan';
    modalBodyElem.innerHTML = `
        <div style="background:rgba(139,92,246,0.1); border-radius:14px; padding:12px; margin-bottom:16px;">
            <p style="color:#8b5cf6; font-size:12px;"><i class="fas fa-info-circle"></i> Edit template pesan untuk Task ${task}</p>
            <p style="color:#9aaebe; font-size:10px;">Gunakan variabel dinamis: <code>{brand_name}</code> untuk nama brand, <code>{commission}</code> untuk komisi</p>
        </div>
        
        <label>Judul Template</label>
        <input type="text" id="templateTitle" placeholder="Judul template" style="width:100%; padding:10px; background:#0f1420; border:1px solid #2a3346; border-radius:10px; color:#e2f0e8;">
        
        <label>Banner Image URL</label>
        <input type="text" id="bannerImage" placeholder="https://... (opsional)" style="width:100%; padding:10px; background:#0f1420; border:1px solid #2a3346; border-radius:10px; color:#e2f0e8;">
        
        <label>Banner Title</label>
        <input type="text" id="bannerTitle" placeholder="Judul banner" style="width:100%; padding:10px; background:#0f1420; border:1px solid #2a3346; border-radius:10px; color:#e2f0e8;">
        
        <label>Banner Description</label>
        <textarea id="bannerDescription" rows="2" placeholder="Deskripsi banner" style="width:100%; padding:10px; background:#0f1420; border:1px solid #2a3346; border-radius:10px; color:#e2f0e8;"></textarea>
        
        <label>Pesan WhatsApp</label>
        <textarea id="messageText" rows="8" placeholder="Tulis pesan di sini..." style="width:100%; padding:10px; background:#0f1420; border:1px solid #2a3346; border-radius:10px; color:#e2f0e8;"></textarea>
        
        <div style="background:#1a1f2e; border-radius:10px; padding:10px; margin-top:12px;">
            <p style="color:#fbbf24; font-size:11px; margin-bottom:6px;"><i class="fas fa-info-circle"></i> Preview:</p>
            <div id="previewBanner" style="display:none; background:rgba(139,92,246,0.1); border-radius:10px; padding:10px; margin-bottom:10px;"></div>
            <div id="previewMessage" style="background:#0f1420; border-radius:8px; padding:10px; font-size:11px; color:#9aaebe; white-space:pre-wrap;"></div>
        </div>
        
        <div class="flex-buttons" style="margin-top:16px;">
            <button id="saveTemplateBtn" style="background:#4ade80; color:#0a0e17;"><i class="fas fa-save"></i> Simpan Template</button>
            <button id="cancelTemplateBtn" style="background:#1e293b;">Batal</button>
        </div>
    `;
    openModalDashboard();
    
    // Load existing template
    fetch(baseUrlDashboard + `message_template/get?type=${type}&task=${task}&stage=${stage}`)
        .then(res => res.json())
        .then(result => {
            if (result.success && result.data) {
                document.getElementById('templateTitle').value = result.data.title || '';
                document.getElementById('bannerImage').value = result.data.banner_image || '';
                document.getElementById('bannerTitle').value = result.data.banner_title || '';
                document.getElementById('bannerDescription').value = result.data.banner_description || '';
                document.getElementById('messageText').value = result.data.message_text || '';
            }
            updatePreview();
        });
    
    function updatePreview() {
        const message = document.getElementById('messageText').value || '';
        const bannerImage = document.getElementById('bannerImage').value;
        const bannerTitle = document.getElementById('bannerTitle').value;
        const bannerDesc = document.getElementById('bannerDescription').value;
        
        const previewBanner = document.getElementById('previewBanner');
        if (bannerImage || bannerTitle) {
            previewBanner.style.display = 'flex';
            previewBanner.innerHTML = `
                <div style="display:flex; gap:10px; align-items:center; width:100%;">
                    ${bannerImage ? `<img src="${escapeHtml(bannerImage)}" style="width:50px; height:50px; border-radius:10px; object-fit:cover;">` : '<div style="width:50px; height:50px; background:#8b5cf6; border-radius:10px; display:flex; align-items:center; justify-content:center;"><i class="fas fa-gift"></i></div>'}
                    <div>
                        <div style="color:#e2f0e8; font-size:12px; font-weight:600;">${escapeHtml(bannerTitle || 'Toopai Affiliate Program')}</div>
                        <div style="color:#9aaebe; font-size:10px;">${escapeHtml(bannerDesc || 'Gabung dengan jaringan affiliate Toopai!')}</div>
                    </div>
                </div>
            `;
        } else {
            previewBanner.style.display = 'none';
        }
        
        document.getElementById('previewMessage').innerHTML = escapeHtml(message).replace(/\n/g, '<br>');
    }
    
    document.getElementById('messageText').addEventListener('input', updatePreview);
    document.getElementById('bannerImage').addEventListener('input', updatePreview);
    document.getElementById('bannerTitle').addEventListener('input', updatePreview);
    document.getElementById('bannerDescription').addEventListener('input', updatePreview);
    
    const saveBtn = document.getElementById('saveTemplateBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', async () => {
            const templateData = {
                type: type,
                task: task,
                stage: stage,
                title: document.getElementById('templateTitle').value,
                message_text: document.getElementById('messageText').value,
                banner_image: document.getElementById('bannerImage').value,
                banner_title: document.getElementById('bannerTitle').value,
                banner_description: document.getElementById('bannerDescription').value
            };
            
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Menyimpan...';
            
            const response = await fetch(baseUrlDashboard + 'message_template/save', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams(templateData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToastInModal('Template berhasil disimpan!', 'success');
                closeModalDashboard();
                setTimeout(() => {
                    // Refresh modal dengan template baru
                    const brandId = document.querySelector('.brand-item-dashboard[data-stage="1"]')?.getAttribute('data-brand-id');
                    const brandName = document.querySelector('.brand-item-dashboard[data-stage="1"]')?.getAttribute('data-brand-name');
                    if (brandId && brandName) {
                        showTask1DetailDashboard(brandId, brandName);
                    }
                }, 500);
            } else {
                showToastInModal(result.message || 'Gagal menyimpan template', 'error');
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save"></i> Simpan Template';
            }
        });
    }
    
    const cancelBtn = document.getElementById('cancelTemplateBtn');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeModalDashboard);
    }
}
// Fungsi send WhatsApp dengan redirect yang benar
function sendWhatsAppDirect(brandId, phoneNumber, message, stage) {
    let phone = phoneNumber.replace(/[^0-9+]/g, '');
    if (phone.startsWith('0')) phone = '+62' + phone.substring(1);
    else if (!phone.startsWith('+')) phone = '+' + phone;
    const cleanPhone = phone.replace(/^\+/, '');
    const whatsappUrl = `https://wa.me/${cleanPhone}?text=${encodeURIComponent(message)}`;
    
    // 🔥 PASTIKAN MEMBUKA TAB BARU
    window.open(whatsappUrl, '_blank');
}

function showUpdateWhatsappModal(brandId, brandName, currentNumber) {
    const modalTitle = document.getElementById('modalTitleDashboard');
    const modalBody = document.getElementById('modalBodyDashboard');
    
    modalTitle.innerHTML = `<i class="fab fa-whatsapp"></i> Update WhatsApp - ${brandName}`;
    modalBody.innerHTML = `
        <div style="background:rgba(251,191,36,0.1); border-radius:12px; padding:12px; margin-bottom:16px;">
            <p style="color:#fbbf24; font-size:12px;"><i class="fas fa-exclamation-triangle"></i> Nomor WhatsApp diperlukan untuk berkomunikasi dengan brand</p>
        </div>
        <label>Nomor WhatsApp</label>
        <input type="tel" id="whatsappNumber" value="${currentNumber || ''}" placeholder="+62 812 3456 7890" style="width:100%; padding:10px; background:#0f1420; border:1px solid #2a3346; border-radius:12px; color:#e2f0e8;">
        <div class="flex-buttons" style="margin-top:16px;">
            <button id="saveWhatsappBtn" style="background:#4ade80; color:#0a0e17;"><i class="fas fa-save"></i> Simpan</button>
            <button id="cancelWhatsappBtn" style="background:#1e293b;">Batal</button>
        </div>
    `;
    openModalDashboard();
    
    document.getElementById('saveWhatsappBtn').addEventListener('click', async () => {
        const phone = document.getElementById('whatsappNumber').value;
        if (!phone) {
            showToastInModal('Nomor WhatsApp harus diisi', 'error');
            return;
        }
        
        const response = await fetch(baseUrlDashboard + 'bd/update_brand_data', {
            method: 'POST',
            body: new URLSearchParams({ brand_id: brandId, whatsapp_number: phone })
        });
        const result = await response.json();
        
        if (result.success) {
            showToastInModal('WhatsApp berhasil diupdate!', 'success');
            closeModalDashboard();
            showTask1DetailDashboard(brandId, brandName);
        } else {
            showToastInModal('Gagal update WhatsApp', 'error');
        }
    });
    
    document.getElementById('cancelWhatsappBtn').addEventListener('click', () => {
        closeModalDashboard();
        showTask1DetailDashboard(brandId, brandName);
    });
}
// ========== PASTIKAN CLOSE MODAL BERFUNGSI ==========
document.addEventListener('DOMContentLoaded', function() {
    const closeX = document.getElementById('closeTaskModalDashboard');
    if (closeX) closeX.onclick = function() { document.getElementById('taskModalDashboard').classList.remove('active'); return false; };
    const modalOverlay = document.getElementById('taskModalDashboard');
    if (modalOverlay) modalOverlay.onclick = function(e) { if (e.target === modalOverlay) modalOverlay.classList.remove('active'); };
});

console.log('Dashboard JS loaded - 4 Tasks: Hunting, Follow Up, Setup Campaign, Monitoring');


// ========== BATCH BRAND MODAL ==========
let batchBrands = [];

function openBatchBrandModal() {
    batchBrands = [];
    document.getElementById('batchBrandModal').classList.add('active');
    renderBatchTable();
}

function closeBatchBrandModal() {
    document.getElementById('batchBrandModal').classList.remove('active');
}

function renderBatchTable() {
    const tbody = document.getElementById('batchBrandTableBody');
    const totalSpan = document.getElementById('batchTotalCount');
    
    if (batchBrands.length === 0) {
        tbody.innerHTML = `
            <tr id="batchEmptyRow">
                <td colspan="4" style="padding:30px; text-align:center; color:#6b7280;">
                    <i class="fas fa-plus-circle" style="font-size:24px; display:block; margin-bottom:8px;"></i>
                    Belum ada brand. Klik "Tambah" untuk menambahkan.
                </td>
            </tr>
        `;
        totalSpan.innerText = '0';
        return;
    }
    
    let html = '';
    batchBrands.forEach((item, index) => {
        html += `
            <tr data-index="${index}" class="${item.status === 'success' ? 'batch-row-success' : (item.status === 'error' ? 'batch-row-error' : '')}">
                <td style="padding:8px 12px; text-align:center; color:#6b7280;">${index + 1}</td>
                <td style="padding:8px 12px;">
                    <input type="text" class="batch-row-input" data-index="${index}" data-field="name" 
                           value="${escapeHtml(item.name || '')}" placeholder="Nama brand" 
                           style="background:transparent; border:none; color:#e2f0e8; width:100%; padding:4px 0;">
                </td>
                <td style="padding:8px 12px;">
                    <input type="tel" class="batch-row-input" data-index="${index}" data-field="phone" 
                           value="${escapeHtml(item.phone || '')}" placeholder="+62..." 
                           style="background:transparent; border:none; color:#e2f0e8; width:100%; padding:4px 0;">
                </td>
                <td style="padding:8px 12px; text-align:center;">
                    <button class="batch-remove-btn" data-index="${index}" title="Hapus baris">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    totalSpan.innerText = batchBrands.length;
    
    // Event listener untuk input di table
    document.querySelectorAll('.batch-row-input').forEach(input => {
        input.addEventListener('change', function() {
            const index = parseInt(this.getAttribute('data-index'));
            const field = this.getAttribute('data-field');
            if (batchBrands[index]) {
                batchBrands[index][field] = this.value.trim();
            }
        });
        
        // Enter untuk pindah ke field berikutnya
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const inputs = document.querySelectorAll('.batch-row-input');
                const currentIdx = Array.from(inputs).indexOf(this);
                if (currentIdx < inputs.length - 1) {
                    inputs[currentIdx + 1].focus();
                } else {
                    // Jika di field terakhir, tambah baris baru
                    document.getElementById('addBatchRowBtn').click();
                }
            }
        });
    });
    
    // Event listener untuk tombol hapus
    document.querySelectorAll('.batch-remove-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const index = parseInt(this.getAttribute('data-index'));
            batchBrands.splice(index, 1);
            renderBatchTable();
        });
    });
}

function addBatchRow() {
    const nameInput = document.getElementById('batchBrandName');
    const phoneInput = document.getElementById('batchBrandPhone');
    
    const name = nameInput.value.trim();
    const phone = phoneInput.value.trim();
    
    if (!name) {
        showToastInModal('Nama brand harus diisi!', 'error');
        nameInput.focus();
        return;
    }
    
    batchBrands.push({
        name: name,
        phone: phone,
        status: 'pending'
    });
    
    nameInput.value = '';
    phoneInput.value = '';
    nameInput.focus();
    
    renderBatchTable();
}

async function saveAllBatchBrands() {
    // Validasi semua data
    const emptyItems = batchBrands.filter(item => !item.name || item.name.trim() === '');
    if (emptyItems.length > 0) {
        showToastInModal(`Ada ${emptyItems.length} baris dengan nama brand kosong!`, 'error');
        return;
    }
    
    const btn = document.getElementById('batchSaveAllBtn');
    const resultContainer = document.getElementById('batchResultContainer');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Menyimpan...';
    resultContainer.style.display = 'block';
    resultContainer.innerHTML = '<div style="text-align:center; padding:20px; color:#9aaebe;"><i class="fas fa-spinner fa-pulse"></i> Memproses...</div>';
    
    let successCount = 0;
    let failCount = 0;
    let results = [];
    
    for (let i = 0; i < batchBrands.length; i++) {
        const item = batchBrands[i];
        
        try {
            const formData = new URLSearchParams();
            formData.append('brand_name', item.name);
            formData.append('category', 'BEAUTY'); // Default kategori
            formData.append('whatsapp_number', item.phone || '');
            formData.append('email', '');
            formData.append('seller_id', '');
            formData.append('commission', '0');
            formData.append('open_commission_rate', '0');
            
            const response = await fetch(baseUrlDashboard + 'bd/scout_match_brand', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData.toString()
            });
            
            const result = await response.json();
            
            if (result.success) {
                successCount++;
                batchBrands[i].status = 'success';
                results.push(`${item.name} - Berhasil`);
            } else {
                failCount++;
                batchBrands[i].status = 'error';
                batchBrands[i].error_message = result.message || 'Gagal';
                results.push(`{item.name} - ${result.message || 'Gagal'}`);
            }
        } catch (error) {
            failCount++;
            batchBrands[i].status = 'error';
            batchBrands[i].error_message = error.message;
            results.push(`{item.name} - Error: ${error.message}`);
        }
        
        // Update tabel setelah setiap item
        renderBatchTable();
    }
    
    // Tampilkan hasil
    let resultHtml = `
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <div>
                <span style="color:#4ade80;">${successCount} berhasil</span>
                <span style="color:#ef4444; margin-left:12px;">${failCount} gagal</span>
            </div>
            <button id="batchCopyResultBtn" style="background:#1e293b; color:#4ade80; border:1px solid #4ade80; padding:4px 12px; border-radius:20px; cursor:pointer; font-size:11px;">
                <i class="fas fa-copy"></i> Copy Hasil
            </button>
        </div>
        <div style="max-height:150px; overflow-y:auto; font-size:11px; font-family:monospace; background:#0a0e17; padding:8px; border-radius:8px;">
            ${results.map(r => `<div>${r}</div>`).join('')}
        </div>
    `;
    
    resultContainer.innerHTML = resultHtml;
    
    // Event copy hasil
    document.getElementById('batchCopyResultBtn')?.addEventListener('click', function() {
        const text = results.join('\n');
        navigator.clipboard.writeText(text).then(() => {
            showToastInModal('Hasil berhasil dicopy!', 'success');
        }).catch(() => {
            // Fallback
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            showToastInModal('Hasil berhasil dicopy!', 'success');
        });
    });
    
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-save"></i> Simpan Semua';
    
    if (successCount > 0) {
        showToastInModal(`${successCount} brand berhasil disimpan!`, 'success');
    }
    if (failCount > 0) {
        showToastInModal(`${failCount} brand gagal disimpan. Cek detail di bawah.`, 'error');
    }
}

function copyAllBatchData() {
    if (batchBrands.length === 0) {
        showToastInModal('Tidak ada data untuk dicopy', 'warning');
        return;
    }
    
    let text = 'Daftar Brand:\n\n';
    batchBrands.forEach((item, index) => {
        text += `${index + 1}. ${item.name}`;
        if (item.phone) text += ` | WhatsApp: ${item.phone}`;
        text += '\n';
    });
    
    navigator.clipboard.writeText(text).then(() => {
        showToastInModal(`${batchBrands.length} data brand berhasil dicopy!`, 'success');
    }).catch(() => {
        // Fallback
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        showToastInModal(`${batchBrands.length} data brand berhasil dicopy!`, 'success');
    });
}

function clearAllBatchData() {
    if (batchBrands.length === 0) return;
    if (!confirm(`Hapus semua ${batchBrands.length} data brand?`)) return;
    
    batchBrands = [];
    renderBatchTable();
    document.getElementById('batchResultContainer').style.display = 'none';
    showToastInModal('Semua data telah dikosongkan', 'info');
}

// ========== EVENT LISTENERS BATCH MODAL ==========
document.addEventListener('DOMContentLoaded', function() {
    // Tombol "Input Brand Baru" - buka modal batch
    const inputBrandBtn = document.querySelector('.task-btn-dashboard[data-action="hunting"]');
    if (inputBrandBtn) {
        // Clone untuk menghindari event listener ganda
        const newBtn = inputBrandBtn.cloneNode(true);
        inputBrandBtn.parentNode.replaceChild(newBtn, inputBrandBtn);
        newBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            openBatchBrandModal();
        });
    }
    
    // Tombol scout tetap untuk search brand
    const scoutBtn = document.getElementById('scoutBtnDashboard');
    if (scoutBtn) {
        const newScoutBtn = scoutBtn.cloneNode(true);
        scoutBtn.parentNode.replaceChild(newScoutBtn, scoutBtn);
        newScoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            showNewBrandModal(); // Search brand modal
        });
    }
    
    // Tombol "Input Brand Baru" di card (yang lain)
    const huntingBtns = document.querySelectorAll('.task-btn-dashboard[data-action="hunting"]');
    huntingBtns.forEach(btn => {
        if (!btn.closest('.stage-card-dashboard[data-stage="1"]')) return;
        const newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);
        newBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            openBatchBrandModal();
        });
    });
    
    // Batch modal events
    document.getElementById('addBatchRowBtn')?.addEventListener('click', addBatchRow);
    document.getElementById('batchSaveAllBtn')?.addEventListener('click', saveAllBatchBrands);
    document.getElementById('batchCopyAllBtn')?.addEventListener('click', copyAllBatchData);
    document.getElementById('batchClearAllBtn')?.addEventListener('click', clearAllBatchData);
    document.getElementById('closeBatchBrandModal')?.addEventListener('click', closeBatchBrandModal);
    
    // Enter key pada input form batch
    document.getElementById('batchBrandName')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('batchBrandPhone')?.focus();
        }
    });
    document.getElementById('batchBrandPhone')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('addBatchRowBtn')?.click();
        }
    });
    
    // Close modal ketika klik overlay
    document.getElementById('batchBrandModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeBatchBrandModal();
    });
});



document.addEventListener('click', async function(e) {
    const checkBtn = e.target.closest('.check-registration-btn');
    if (!checkBtn) return;
    
    const brandId = checkBtn.getAttribute('data-brand-id');
    const brandName = checkBtn.getAttribute('data-brand-name');
    const parentItem = checkBtn.closest('.stage-item-dashboard');
    
    // Disable tombol
    checkBtn.disabled = true;
    checkBtn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i>';
    
    try {
        const response = await fetch(baseUrlDashboard + 'bd/check_brand_registration', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ brand_id: brandId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            if (result.has_products) {
                // 🔥 BRAND SUDAH REGISTRASI - REFRESH HALAMAN
                showToastInModal('✅ Brand ' + brandName + ' sudah registrasi! Memindahkan ke Task 3...', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                // Belum registrasi
                showToastInModal('⏳ Brand ' + brandName + ' belum registrasi. Silakan tunggu.', 'warning');
                checkBtn.disabled = false;
                checkBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
            }
        } else {
            showToastInModal('Gagal mengecek registrasi: ' + (result.message || 'Unknown error'), 'error');
            checkBtn.disabled = false;
            checkBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
        }
    } catch (error) {
        console.error('Error checking registration:', error);
        showToastInModal('Gagal mengecek registrasi', 'error');
        checkBtn.disabled = false;
        checkBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
    }
});
// ============================================================
// REJECT SELECTED PRODUCTS (BATCH)
// ============================================================
document.addEventListener('click', async function(e) {
    const rejectBtn = e.target.closest('#rejectSelectedBtn');
    if (!rejectBtn) return;
    
    if (selectedProducts.length === 0) {
        showToastInModal('Pilih produk yang akan ditolak', 'error');
        return;
    }
    
    // 🔥 KONFIRMASI
    if (!confirm(`Anda yakin ingin MENOLAK ${selectedProducts.length} produk yang dipilih?`)) {
        return;
    }
    
    rejectBtn.disabled = true;
    rejectBtn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Rejecting...';
    
    let successCount = 0;
    let failCount = 0;
    
    for (const product of selectedProducts) {
        try {
            const rejectResponse = await fetch(baseUrlDashboard + 'bd/approve_product_with_commission', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    campaign_id: product.campaign_id,
                    product_id: product.product_id,
                    review_result: 'REJECT',
                    product_name: product.product_name
                })
            });
            
            const rejectResult = await rejectResponse.json();
            
            if (rejectResult.success) {
                successCount++;
                
                // Hapus checkbox dari tampilan
                const checkbox = document.querySelector(`.product-checkbox[data-product-id="${product.product_id}"]`);
                if (checkbox) {
                    const productCard = checkbox.closest('.product-card-landscape');
                    if (productCard) productCard.remove();
                }
            } else {
                failCount++;
                console.error('Reject failed for product:', product.product_id, rejectResult.message);
            }
        } catch (error) {
            failCount++;
            console.error('Error rejecting product:', product.product_id, error);
        }
    }
    
    // 🔥 TAMPILKAN HASIL
    if (successCount > 0) {
        showToastInModal(`✅ ${successCount} produk ditolak, ${failCount} gagal`, successCount > 0 ? 'warning' : 'error');
    } else {
        showToastInModal(`❌ Gagal menolak ${failCount} produk`, 'error');
    }
    
    // 🔥 UPDATE COUNT
    const remainingProducts = document.querySelectorAll('.product-card-landscape').length;
    const setupCountSpan = document.getElementById('setupCountDashboard');
    if (setupCountSpan) {
        const currentCount = parseInt(setupCountSpan.innerText) || 0;
        setupCountSpan.innerText = Math.max(0, currentCount - successCount);
    }
    
    // 🔥 CEK APAKAH SEMUA PRODUK SUDAH DIPROSES (APPROVE ATAU REJECT)
    if (remainingProducts === 0) {
        try {
            const moveResponse = await fetch(baseUrlDashboard + 'bd/check_and_move_to_active', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ 
                    brand_id: currentBrandIdForSetup, 
                    brand_name: currentBrandNameForSetup 
                })
            });
            const moveResult = await moveResponse.json();
            
            if (moveResult.moved_to_active) {
                showToastInModal('🎉 ' + moveResult.message, 'success');
                setTimeout(() => {
                    closeModalDashboard();
                    location.reload();
                }, 1500);
                return;
            }
        } catch (err) {
            console.error('Error checking move to active:', err);
        }
    }
    
    // 🔥 RESET BUTTON
    rejectBtn.disabled = false;
    rejectBtn.innerHTML = '<i class="fas fa-times-circle"></i> Reject Selected (0)';
    
    // 🔥 RESET SELECTED
    selectedProducts = [];
    updateSelectedCount();
});
// ========== OVERRIDE: CLICK HANDLER UNTUK FOLLOW UP ITEMS ==========
// Event listener untuk brand item di Task 2 sudah ada di bagian atas
// Kita hanya perlu memastikan logika is_clickable berjalan dengan benar
// Cari bagian ini di dalam document.addEventListener('DOMContentLoaded', function() {

// Perbarui logika click untuk stage 2:
document.querySelectorAll('.brand-item-dashboard').forEach(item => {
    // Hapus event listener lama (jika ada)
    const newItem = item.cloneNode(true);
    item.parentNode.replaceChild(newItem, item);
    
    newItem.addEventListener('click', async (e) => {
        // Jangan trigger jika klik tombol di dalam item
        if (e.target.closest('.check-registration-btn') || 
            e.target.closest('.remove-existing-product-dashboard') || 
            e.target.closest('.remove-new-product-dashboard')) {
            return;
        }
        
        const brandId = newItem.getAttribute('data-brand-id');
        const brandName = newItem.getAttribute('data-brand-name');
        const stage = parseInt(newItem.getAttribute('data-stage'));
        
        if (stage === 2) {
            const isClickable = newItem.getAttribute('data-is-clickable') === 'true';
            
            if (!isClickable) {
                showToastInModal('⏳ Brand sedang menunggu registrasi campaign. Klik tombol "Refresh" untuk cek status.', 'warning');
                return;
            }
            
            // 🔥 CEK ULANG STATUS REGISTRASI SEBELUM BUKA MODAL
            try {
                const checkResponse = await fetch(baseUrlDashboard + 'bd/check_brand_registration', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ brand_id: brandId })
                });
                const checkResult = await checkResponse.json();
                
                if (checkResult.success && checkResult.has_products) {
                    // Brand sudah registrasi, refresh halaman dulu
                    showToastInModal('✅ Brand sudah registrasi! Memuat ulang halaman...', 'success');
                    setTimeout(() => location.reload(), 1000);
                    return;
                }
            } catch (err) {
                // Gagal cek, lanjutkan ke modal
                console.error('Error checking registration:', err);
            }
            
            // Buka modal follow up
            showTask2FollowUpModal(brandId, brandName);
        } 
        else if (stage === 1) {
            showTask1DetailDashboard(brandId, brandName);
        }
        else if (stage === 3) {
            showTask3SetupModalWithRecommendations(brandId, brandName);
        }
        else if (stage === 4) {
            showTask4MonitoringModalDashboard(brandId, brandName);
        }
    });
});

// ========== TOMBOL CEK STATUS & PINDAH KE TASK 4 ==========
document.addEventListener('click', async function(e) {
    const checkBtn = e.target.closest('.check-active-status-btn');
    if (!checkBtn) return;
    
    const brandId = checkBtn.getAttribute('data-brand-id');
    const brandName = checkBtn.getAttribute('data-brand-name');
    const parentItem = checkBtn.closest('.stage-item-dashboard');
    
    checkBtn.disabled = true;
    checkBtn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Mengecek...';
    
    try {
        const response = await fetch(baseUrlDashboard + 'bd/check_and_move_to_active', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ brand_id: brandId, brand_name: brandName })
        });
        
        const result = await response.json();
        
        if (result.moved_to_active) {
            showToastInModal('🎉 ' + result.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToastInModal(result.message, 'info');
            checkBtn.disabled = false;
            checkBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Cek Status & Pindah ke Task 4';
        }
    } catch (error) {
        showToastInModal('Gagal mengecek status', 'error');
        checkBtn.disabled = false;
        checkBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Cek Status & Pindah ke Task 4';
    }
});


// ========== HANDLE EDIT BRAND CLICK ==========
function handleEditBrandClick(button) {
    // Stop propagation agar tidak trigger parent click
    if (event) {
        event.stopPropagation();
    }
    
    const brandId = button.getAttribute('data-brand-id');
    const brandName = button.getAttribute('data-brand-name');
    const whatsapp = button.getAttribute('data-whatsapp');
    const commission = button.getAttribute('data-commission');
    const category = button.getAttribute('data-category');
    const email = button.getAttribute('data-email');
    
    console.log('Edit button clicked:', brandId, brandName);
    
    // Panggil fungsi showEditBrandModal
    showEditBrandModal(brandId, brandName, whatsapp, commission, category, email);
}

// ============================================================
// MODAL LIST BRAND AKTIF
// ============================================================

// Buka modal ketika stat "Brand Aktif" di-klik
document.addEventListener('DOMContentLoaded', function() {
    // Event listener untuk stat Brand Aktif
    const statActive = document.querySelector('.stat-active');
    if (statActive) {
        statActive.style.cursor = 'pointer';
        statActive.addEventListener('click', function() {
            openActiveBrandsModal();
        });
    }
    
    // Close modal
    const closeBtn = document.getElementById('closeActiveBrandsModal');
    if (closeBtn) {
        closeBtn.addEventListener('click', closeActiveBrandsModal);
    }
    
    // Close modal when clicking overlay
    const modal = document.getElementById('modalActiveBrands');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeActiveBrandsModal();
            }
        });
    }
    
    // Search input
    const searchInput = document.getElementById('searchActiveBrands');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            filterActiveBrands(this.value);
        });
    }
    
    // Refresh button
    const refreshBtn = document.getElementById('refreshActiveBrandsBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            loadActiveBrands();
        });
    }
});

// Buka modal
function openActiveBrandsModal() {
    const modal = document.getElementById('modalActiveBrands');
    if (modal) {
        modal.classList.add('active');
        loadActiveBrands();
    }
}

// Tutup modal
function closeActiveBrandsModal() {
    const modal = document.getElementById('modalActiveBrands');
    if (modal) {
        modal.classList.remove('active');
    }
}

// Load data brand aktif dari server
async function loadActiveBrands() {
    const container = document.getElementById('activeBrandsListContainer');
    const totalSpan = document.getElementById('totalActiveBrandsCount');
    
    if (!container) return;
    
    // Tampilkan loading
    container.innerHTML = `
        <div style="text-align: center; padding: 40px; color: #9aaebe;">
            <i class="fas fa-spinner fa-pulse fa-2x"></i>
            <p style="margin-top: 12px;">Memuat data brand aktif...</p>
        </div>
    `;
    
    try {
        const response = await fetch(baseUrlDashboard + 'bd/get_active_brands_list', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            const brands = result.brands || [];
            const total = brands.length;
            
            if (totalSpan) totalSpan.innerText = total;
            
            if (brands.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-store"></i>
                        <p>Belum ada brand aktif</p>
                        <p style="font-size: 11px; margin-top: 8px;">Brand akan muncul setelah campaign berjalan</p>
                    </div>
                `;
                return;
            }
            
            // Render list brand
            let html = '';
            brands.forEach((brand, index) => {
                const hasPending = brand.has_pending || false;
                const statusBadge = hasPending 
                    ? '<span class="status-badge pending"><i class="fas fa-clock"></i> Ada Pending</span>'
                    : '<span class="status-badge active"><i class="fas fa-check-circle"></i> Aktif</span>';
                
                const gmv = brand.today_gmv || 0;
                const approvedCount = brand.approved_products_count || 0;
                const pendingCount = brand.pending_products_count || 0;
                
                html += `
                    <div class="brand-item-active" data-brand-id="${brand.id}" data-brand-name="${escapeHtml(brand.name)}">
                        <div>
                            <div class="brand-name">
                                <i class="fas fa-building" style="color: #8b5cf6; font-size: 12px; margin-right: 8px;"></i>
                                ${escapeHtml(brand.name)}
                            </div>
                            <div class="brand-info">
                                <span><i class="fas fa-user"></i> Handler: ${escapeHtml(brand.handler || brand.bd_username || '-')}</span>
                                <span><i class="fas fa-calendar-alt"></i> Deal: ${brand.deal_confirmed_at ? new Date(brand.deal_confirmed_at).toLocaleDateString('id-ID') : '-'}</span>
                                <span class="gmv"><i class="fas fa-money-bill-wave"></i> GMV: Rp ${formatNumber(gmv)}</span>
                                <span class="products"><i class="fas fa-box"></i> Produk: ${approvedCount} approve${pendingCount > 0 ? `, ${pendingCount} pending` : ''}</span>
                                ${statusBadge}
                            </div>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
            
            // Simpan data untuk filter
            container.dataset.brands = JSON.stringify(brands);
            
        } else {
            container.innerHTML = `
                <div class="empty-state" style="color: #ef4444;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>${result.message || 'Gagal memuat data'}</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading active brands:', error);
        container.innerHTML = `
            <div class="empty-state" style="color: #ef4444;">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Terjadi kesalahan: ${error.message}</p>
            </div>
        `;
    }
}

// Filter brand berdasarkan keyword
function filterActiveBrands(keyword) {
    const container = document.getElementById('activeBrandsListContainer');
    if (!container) return;
    
    const brands = JSON.parse(container.dataset.brands || '[]');
    const items = container.querySelectorAll('.brand-item-active');
    
    const searchTerm = keyword.toLowerCase().trim();
    
    items.forEach((item, index) => {
        const brand = brands[index];
        if (!brand) return;
        
        const brandName = (brand.name || '').toLowerCase();
        const handler = (brand.handler || brand.bd_username || '').toLowerCase();
        
        if (searchTerm === '' || brandName.includes(searchTerm) || handler.includes(searchTerm)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}
</script>