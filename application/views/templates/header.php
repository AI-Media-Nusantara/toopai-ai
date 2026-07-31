<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title><?= $title ?? 'Toopai Affiliate Platform' ?></title>
    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/logo/favicon_new.png') ?>">
    <link rel="icon" type="image/svg+xml" href="<?= base_url('assets/logo/favicon_new.png') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/logo/favicon_new.png') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --bg-primary: #0a0a0f;
            --bg-secondary: #0f0f1a;
            --bg-card: #13111f;
            --bg-elevated: #1a1730;
            --purple: #8b5cf6;
            --purple-dark: #6d28d9;
            --purple-glow: rgba(139, 92, 246, 0.2);
            --blue: #3b82f6;
            --cyan: #06b6d4;
            --green: #10b981;
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --border: #2a2745;
            --glow-purple: 0 0 20px rgba(139, 92, 246, 0.3);
            --transition: all 0.3s ease;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            background: var(--bg-primary);
            font-family: 'Inter', system-ui, sans-serif;
            color: var(--text-primary);
            min-height: 100vh;
        }
        
        /* Navbar Sederhana */
.navbar {
    background: var(--bg-secondary);
    border-bottom: 1px solid var(--border);
    padding: 0 20px;
    position: sticky;
    top: 0;
    z-index: 100;
}

/* Container */
.nav-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 1600px;
    margin: 0 auto;
    height: 56px; /* FIX tinggi navbar */
}

/* Logo */
.logo {
    display: flex;
    align-items: center;
    height: 100%;
}

.logo-img {
    height: 140px;   /* kecil & presisi */
    width: auto;
    display: block;
    margin: 0;
    padding: 0;
}

.logo h2 {
    background: linear-gradient(135deg, var(--purple), var(--cyan), var(--blue));
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    font-size: 18px;
    font-weight: 700;
    margin: 0;
    line-height: 1;
}

/* User Menu */
.user-menu {
    display: flex;
    align-items: center;
    gap: 12px;
    height: 100%;
}


.user-name {
    color: var(--text-secondary);
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.user-name i {
    color: var(--purple);
    font-size: 14px;
}

.user-actions {
    display: flex;
    gap: 8px;
}

.btn-icon {
    width: 32px;
    height: 32px;
}

.btn-icon:hover {
    background: var(--purple-glow);
    border-color: var(--purple);
    color: var(--purple);
}

.logout-btn {
    padding: 6px 14px;
    height: 32px;
}

.logout-btn:hover {
    background: linear-gradient(135deg, var(--purple), var(--blue));
    color: white;
    border-color: transparent;
}

/* Mobile responsive */
@media (max-width: 767px) {
    .navbar {
        padding: 4px 12px;  /* Kurangi padding mobile */
    }
    
    .logo-img {
        width: 80px;   /* Ukuran mobile lebih kecil */
        height: 35px;
    }
    
    .logo h2 {
        font-size: 12px;
    }
    
    .user-name span {
        display: none;
    }
    
    .user-name i {
        margin: 0;
        font-size: 16px;
    }
    
    .btn-icon {
        width: 30px;
        height: 30px;
    }
    
    .logout-btn span {
        display: none;
    }
    
    .logout-btn i {
        margin: 0;
    }
    
    .logout-btn {
        padding: 6px 10px;
    }
}
        
        .container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Mobile menu bar - untuk menu dashboard */
        .mobile-menu-bar {
            display: flex;
            gap: 8px;
            padding: 12px 20px;
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border);
            overflow-x: auto;
            white-space: nowrap;
            scrollbar-width: thin;
        }
        
        .mobile-menu-bar a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 40px;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .mobile-menu-bar a:hover, .mobile-menu-bar a.active {
            background: var(--purple-glow);
            color: var(--purple);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in { animation: fadeIn 0.3s ease; }
        
        @media (min-width: 768px) {
            .mobile-menu-bar { display: none; }
        }
        
       @media (max-width: 767px) {
    .navbar {
        padding: 0 12px;
    }

    .nav-container {
        height: 50px;
    }

    .logo-img {
        height: 24px;
    }

    .btn-icon {
        width: 28px;
        height: 28px;
    }
}
        
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: var(--bg-elevated); border-radius: 10px; }
        ::-webkit-scrollbar-thumb { background: var(--purple); border-radius: 10px; }
        
   
    </style>
</head>
<body>
<nav class="navbar">
    <div class="nav-container">
        <div class="logo">
            <img src="<?= base_url('assets/logo/new_logo_toopai_web.png') ?>" alt="Toopai Logo" class="logo-img">
           
        </div>
        <div class="user-menu">
            <span class="user-name">
                <i class="fas fa-user-circle"></i> 
                <span><?= htmlspecialchars($this->session->userdata('full_name') ?: $this->session->userdata('username')) ?></span>
            </span>
            <div class="user-actions">
                <button onclick="showChangePasswordModal()" class="btn-icon" title="Ganti Password">
                    <i class="fas fa-key"></i>
                </button>
                <button onclick="showAddUserModal()" class="btn-icon" title="Tambah User">
                    <i class="fas fa-user-plus"></i>
                </button>
                <button onclick="showActivityLogModal()" class="btn-icon" title="Log Aktivitas">
                    <i class="fas fa-history"></i>
                </button>
            </div>
            <a href="<?= base_url('auth/logout') ?>" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> 
                <span>Logout</span>
            </a>
        </div>
    </div>
</nav>

    
    <!-- Desktop Menu - akan muncul di dalam container dashboard -->
    <div class="container fade-in">
        
        
        
        
        
        
        <!-- Modal Ganti Password -->
<div id="changePasswordModal" class="modal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3><i class="fas fa-key"></i> Ganti Password</h3>
            <span class="close" onclick="closeChangePasswordModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Password Saat Ini</label>
                <input type="password" id="currentPassword" class="form-control" placeholder="Masukkan password saat ini">
            </div>
            <div class="form-group">
                <label>Password Baru</label>
                <input type="password" id="newPassword" class="form-control" placeholder="Minimal 6 karakter">
            </div>
            <div class="form-group">
                <label>Konfirmasi Password Baru</label>
                <input type="password" id="confirmPassword" class="form-control" placeholder="Ulangi password baru">
            </div>
            <div class="flex-buttons">
                <button id="savePasswordBtn" class="btn-primary"><i class="fas fa-save"></i> Simpan</button>
                <button onclick="closeChangePasswordModal()" class="btn-secondary">Batal</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah User -->
<div id="addUserModal" class="modal">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3><i class="fas fa-user-plus"></i> Tambah User <span id="addUserRoleLabel"></span></h3>
            <span class="close" onclick="closeAddUserModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Username</label>
                <input type="text" id="newUsername" class="form-control" placeholder="Username">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" id="newUserPassword" class="form-control" placeholder="Minimal 6 karakter">
            </div>
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" id="newUserFullname" class="form-control" placeholder="Nama lengkap">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="newUserEmail" class="form-control" placeholder="email@example.com">
            </div>
            <div class="flex-buttons">
                <button id="saveUserBtn" class="btn-primary"><i class="fas fa-save"></i> Simpan</button>
                <button onclick="closeAddUserModal()" class="btn-secondary">Batal</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Log Aktivitas -->
<div id="activityLogModal" class="modal">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h3><i class="fas fa-history"></i> Log Aktivitas</h3>
            <span class="close" onclick="closeActivityLogModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="activityLogContent">
                <div class="loading">Loading...</div>
            </div>
        </div>
    </div>
</div>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.8);
    align-items: center;
    justify-content: center;
}

.modal.show {
    display: flex;
}

.modal-content {
    background: var(--bg-card);
    border-radius: 24px;
    border: 1px solid var(--border);
    width: 90%;
    max-width: 500px;
    max-height: 80vh;
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

.form-control:focus {
    outline: none;
    border-color: var(--purple);
}

.btn-primary {
    background: linear-gradient(135deg, var(--purple), var(--blue));
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 40px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
}

.btn-secondary {
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    color: var(--text-secondary);
    padding: 10px 20px;
    border-radius: 40px;
    cursor: pointer;
    font-size: 13px;
}

.log-item {
    padding: 12px;
    border-bottom: 1px solid var(--border);
    font-size: 12px;
}

.log-time {
    color: var(--text-muted);
    font-size: 10px;
    margin-bottom: 4px;
}

.log-action {
    color: var(--purple);
    font-weight: 600;
}

.log-description {
    color: var(--text-secondary);
    margin-top: 4px;
}


.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 12px;
}

.section-totals {
    display: flex;
    gap: 20px;
    background: #0a0e1a;
    padding: 10px 18px;
    border-radius: 12px;
    border: 1px solid #2a3346;
}

.total-item {
    text-align: center;
    min-width: 80px;
}

.total-value {
    display: block;
    font-size: 16px;
    font-weight: 700;
    color: #4ade80;
    line-height: 1.2;
}

.total-label {
    display: block;
    font-size: 10px;
    color: #64748b;
    margin-top: 2px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

@media (max-width: 768px) {
    .section-totals {
        gap: 12px;
        padding: 8px 12px;
    }
    
    .total-value {
        font-size: 14px;
    }
}
</style>
