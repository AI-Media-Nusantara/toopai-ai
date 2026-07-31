<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= $title ?? 'Toopai Dashboard' ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/logo/favicon_new.png') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php 
// Hanya muat Toopai Assistant untuk role Admin/Owner, bukan untuk BD
$user_role = $this->session->userdata('role');
if ($user_role !== 'bd' && $user_role !== 'BD' && $user_role !== 'IS'): 
?>
<script src="<?= base_url('assets/js/toopai-assistant.js') ?>" defer></script>
<?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root{--bg:#030914;--bg-2:#061021;--panel:rgba(11,22,42,.78);--panel-strong:rgba(15,29,53,.92);--stroke:rgba(112,136,185,.20);--stroke-strong:rgba(156,107,255,.38);--text:#f7fbff;--muted:#8e9bb6;--muted-2:#b7c1d6;--purple:#7c3cff;--purple-2:#c02cff;--cyan:#10dff0;--green:#39f08a;--red:#ff4f65;--orange:#f5a623;--radius-lg:20px;--radius-md:16px;--radius-sm:12px;--shadow:0 24px 60px rgba(0,0,0,.45);--glow-purple:0 0 35px rgba(124,60,255,.34);--font:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
        *{box-sizing:border-box;margin:0;padding:0} html,body{min-height:100%} body{font-family:var(--font);color:var(--text);background:radial-gradient(circle at 18% -10%,rgba(124,60,255,.17),transparent 28%),radial-gradient(circle at 88% 8%,rgba(16,223,240,.08),transparent 24%),linear-gradient(145deg,#020711 0%,#040a17 42%,#061021 100%);overflow-x:hidden} body:before{content:"";position:fixed;inset:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.02) 1px,transparent 1px);background-size:44px 44px;mask-image:radial-gradient(circle at 50% 8%,#000 0%,transparent 70%);opacity:.6}.navbar{position:sticky;top:0;z-index:1000;height:72px;background:rgba(3,9,20,.76);border-bottom:1px solid rgba(112,136,185,.16);backdrop-filter:blur(18px);padding:0 28px}.nav-container{height:100%;max-width:1920px;margin:auto;display:flex;align-items:center;justify-content:space-between;gap:20px}.logo{display:flex;align-items:center;gap:12px}
             .logo-img{height:150px;width:auto;display:block}.brand-text{font-size:24px;font-weight:900;letter-spacing:-.04em}.user-menu{display:flex;align-items:center;gap:12px}.user-name{height:42px;display:flex;align-items:center;gap:9px;padding:0 14px;color:var(--muted-2);border:1px solid var(--stroke);border-radius:999px;background:rgba(8,18,34,.65);font-size:13px;font-weight:700}.user-name i{color:var(--purple-2)}.user-actions{display:flex;align-items:center;gap:10px}.btn-icon,.logout-btn{width:42px;height:42px;display:inline-grid;place-items:center;border-radius:999px;border:1px solid var(--stroke);background:rgba(8,18,34,.65);color:#fff;text-decoration:none;cursor:pointer;box-shadow:inset 0 1px 0 rgba(255,255,255,.04);transition:.2s}.logout-btn{width:auto;padding:0 16px;display:inline-flex;gap:8px}.btn-icon:hover,.logout-btn:hover{border-color:rgba(168,126,255,.45);box-shadow:var(--glow-purple);transform:translateY(-1px)}.container{max-width:1920px;margin:0 auto;padding:0}.fade-in{animation:fadeIn .3s ease}@keyframes fadeIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}.modal{display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.74);backdrop-filter:blur(10px);align-items:center;justify-content:center;padding:20px}.modal.show{display:flex}.modal-content{width:92%;max-width:520px;max-height:86vh;overflow:auto;border-radius:24px;border:1px solid var(--stroke);background:linear-gradient(160deg,rgba(19,33,59,.96),rgba(6,14,27,.96));box-shadow:var(--shadow)}.modal-header{display:flex;align-items:center;justify-content:space-between;padding:20px 22px;border-bottom:1px solid rgba(112,136,185,.14)}.modal-header h3{font-size:18px}.close{font-size:28px;color:var(--muted);cursor:pointer}.close:hover{color:#fff}.modal-body{padding:22px}.form-group{margin-bottom:16px}.form-group label{display:block;color:var(--muted-2);font-size:12px;font-weight:700;margin-bottom:7px}.form-control{width:100%;height:46px;border-radius:14px;border:1px solid var(--stroke);background:rgba(8,18,34,.72);color:#fff;padding:0 14px}.form-control:focus{outline:none;border-color:var(--purple);box-shadow:0 0 18px rgba(124,60,255,.18)}.flex-buttons{display:flex;gap:10px;margin-top:20px}.btn-primary,.btn-secondary{height:44px;padding:0 18px;border-radius:999px;font-weight:800;cursor:pointer}.btn-primary{border:0;background:linear-gradient(135deg,#5a25d8,#7f34ff);color:#fff}.btn-secondary{border:1px solid var(--stroke);background:rgba(8,18,34,.72);color:#fff}.log-item{padding:12px;border-bottom:1px solid rgba(112,136,185,.12)}.log-time{font-size:11px;color:var(--muted)}.log-action{margin-top:4px;color:var(--purple-2);font-weight:800}.log-description{margin-top:4px;color:var(--muted-2);font-size:12px}.loading,.empty-state{padding:28px;text-align:center;color:var(--muted)}@media(max-width:768px){.navbar{height:62px;padding:0 14px}.logo-img{height:150px}.brand-text{font-size:20px}.user-name span,.logout-btn span{display:none}.user-name{width:40px;padding:0;justify-content:center}.btn-icon,.logout-btn{width:40px;height:40px}.container{padding:0}}
    </style>
</head>
<body data-role="<?= $this->session->userdata('role') ?? 'guest' ?>" 
      data-user="<?= $this->session->userdata('username') ?? '' ?>">
<nav class="navbar">
    <div class="nav-container">
        <div class="logo">
            <img src="<?= base_url('assets/logo/new_logo_toopai_web.png') ?>" alt="Toopai" class="logo-img" onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
            <span class="brand-text" style="display:none">Toopai</span>
        </div>
        <div class="user-menu">
            <span class="user-name"><i class="fas fa-user-circle"></i><span><?= htmlspecialchars($this->session->userdata('full_name') ?: $this->session->userdata('username')) ?></span></span>
            <div class="user-actions">
                <button onclick="showChangePasswordModal()" class="btn-icon" title="Ganti Password"><i class="fas fa-key"></i></button>
                <button onclick="showAddUserModal()" class="btn-icon" title="Tambah User"><i class="fas fa-user-plus"></i></button>
                <button onclick="showActivityLogModal()" class="btn-icon" title="Log Aktivitas"><i class="fas fa-history"></i></button>
            </div>
            <a href="<?= base_url('auth/logout') ?>" class="logout-btn"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </div>
    </div>
</nav>
<div class="container fade-in">
<div id="changePasswordModal" class="modal"><div class="modal-content"><div class="modal-header"><h3><i class="fas fa-key"></i> Ganti Password</h3><span class="close" onclick="closeChangePasswordModal()">&times;</span></div><div class="modal-body"><div class="form-group"><label>Password Saat Ini</label><input type="password" id="currentPassword" class="form-control"></div><div class="form-group"><label>Password Baru</label><input type="password" id="newPassword" class="form-control"></div><div class="form-group"><label>Konfirmasi Password Baru</label><input type="password" id="confirmPassword" class="form-control"></div><div class="flex-buttons"><button id="savePasswordBtn" class="btn-primary"><i class="fas fa-save"></i> Simpan</button><button onclick="closeChangePasswordModal()" class="btn-secondary">Batal</button></div></div></div></div>
<div id="addUserModal" class="modal"><div class="modal-content"><div class="modal-header"><h3><i class="fas fa-user-plus"></i> Tambah User <span id="addUserRoleLabel"></span></h3><span class="close" onclick="closeAddUserModal()">&times;</span></div><div class="modal-body"><div class="form-group"><label>Username</label><input type="text" id="newUsername" class="form-control"></div><div class="form-group"><label>Password</label><input type="password" id="newUserPassword" class="form-control"></div><div class="form-group"><label>Nama Lengkap</label><input type="text" id="newUserFullname" class="form-control"></div><div class="form-group"><label>Email</label><input type="email" id="newUserEmail" class="form-control"></div><div class="flex-buttons"><button id="saveUserBtn" class="btn-primary"><i class="fas fa-save"></i> Simpan</button><button onclick="closeAddUserModal()" class="btn-secondary">Batal</button></div></div></div></div>
<div id="activityLogModal" class="modal"><div class="modal-content" style="max-width:760px"><div class="modal-header"><h3><i class="fas fa-history"></i> Log Aktivitas</h3><span class="close" onclick="closeActivityLogModal()">&times;</span></div><div class="modal-body"><div id="activityLogContent"><div class="loading">Loading...</div></div></div></div></div>
