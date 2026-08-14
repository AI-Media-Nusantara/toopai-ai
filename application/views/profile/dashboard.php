<div class="profile-dashboard-container">
    <!-- SIDEBAR -->
    <div class="profile-sidebar">
        <div class="sidebar-header">
            <div class="sidebar-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="sidebar-user-info">
                <h4><?= htmlspecialchars($user->full_name ?: $user->username) ?></h4>
                <span class="role-badge <?= strtolower($role) ?>"><?= $role ?></span>
            </div>
        </div>
        <div class="sidebar-menu-list">
            <a href="javascript:void(0)" onclick="switchTab('profile-tab', this)" class="sidebar-menu-item active">
                <i class="fas fa-user-cog"></i> Profile settings
            </a>
            <?php if ($role === 'ADMIN' || $role === 'IS' || $role === 'BD'): ?>
            <a href="javascript:void(0)" onclick="switchTab('users-tab', this)" class="sidebar-menu-item">
                <i class="fas fa-users-cog"></i> User Management
            </a>
            <?php endif; ?>
            <a href="javascript:void(0)" onclick="switchTab('monitoring-tab', this)" class="sidebar-menu-item">
                <i class="fas fa-tv"></i> Monitoring Creator
            </a>
            <a href="javascript:void(0)" onclick="switchTab('samples-tab', this)" class="sidebar-menu-item">
                <i class="fas fa-box-open"></i> Manage Sample Creator
            </a>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="profile-content-area">
        <!-- PROFILE TAB -->
        <div id="profile-tab" class="profile-tab-content active">
            <div class="content-header">
                <h2><i class="fas fa-user-cog"></i> Profile settings</h2>
                <p>Kelola informasi akun Anda dan ganti kata sandi di sini.</p>
            </div>
            <div class="profile-grid">
                <!-- Edit Profile Form -->
                <div class="profile-card">
                    <h3>Detail Akun</h3>
                    <form id="editProfileForm" onsubmit="saveProfile(event)">
                        <div class="form-group-custom">
                            <label for="fullNameInput">Nama Lengkap</label>
                            <input type="text" id="fullNameInput" name="full_name" class="form-control-custom" value="<?= htmlspecialchars($user->full_name) ?>" required>
                        </div>
                        <div class="form-group-custom">
                            <label for="emailInput">Email</label>
                            <input type="email" id="emailInput" name="email" class="form-control-custom" value="<?= htmlspecialchars($user->email) ?>" required>
                        </div>
                        <div class="form-group-custom">
                            <label for="passwordInput">Password Baru (Kosongkan jika tidak diganti)</label>
                            <input type="password" id="passwordInput" name="password" class="form-control-custom" placeholder="Minimal 6 karakter">
                        </div>
                        <button type="submit" class="btn-primary-custom"><i class="fas fa-save"></i> Simpan Perubahan</button>
                    </form>
                </div>

                <!-- Account Status Info -->
                <div class="profile-card info-card">
                    <h3>Status Akun</h3>
                    <div class="info-item">
                        <span class="info-label">Username:</span>
                        <span class="info-value">@<?= htmlspecialchars($user->username) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Role Akses:</span>
                        <span class="info-value"><span class="role-badge <?= strtolower($role) ?>"><?= $role ?></span></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Terdaftar Sejak:</span>
                        <span class="info-value"><?= date('d M Y H:i', strtotime($user->created_at)) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Login Terakhir:</span>
                        <span class="info-value"><?= $user->last_login ? date('d M Y H:i', strtotime($user->last_login)) : '-' ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- USER MANAGEMENT TAB -->
        <?php if ($role === 'ADMIN' || $role === 'IS' || $role === 'BD'): ?>
        <div id="users-tab" class="profile-tab-content">
            <div class="content-header flex-header">
                <div>
                    <h2><i class="fas fa-users-cog"></i> User Management</h2>
                    <p>Kelola anggota tim atau user bawahan dengan role <strong><?= $role ?></strong>.</p>
                </div>
                <button class="btn-primary-custom" onclick="openAddUserModalCustom()"><i class="fas fa-user-plus"></i> Tambah User</button>
            </div>
            <div class="profile-card">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Dibuat</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($managed_users)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada user bawahan yang dibuat.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($managed_users as $u): ?>
                                <tr>
                                    <td>
                                        <div class="user-cell">
                                            <i class="fas fa-user-circle"></i>
                                            <div>
                                                <strong><?= htmlspecialchars($u->full_name ?: $u->username) ?></strong>
                                                <span class="user-sub text-muted">@<?= htmlspecialchars($u->username) ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($u->email) ?></td>
                                    <td><span class="role-badge <?= strtolower($u->role) ?>"><?= $u->role ?></span></td>
                                    <td><?= date('d M Y', strtotime($u->created_at)) ?></td>
                                    <td>
                                        <span class="status-indicator <?= $u->is_active ? 'active' : 'inactive' ?>">
                                            <?= $u->is_active ? 'Aktif' : 'Non-aktif' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn-toggle <?= $u->is_active ? 'deactivate' : 'activate' ?>" onclick="toggleUserStatus(<?= $u->id ?>, <?= $u->is_active ? 0 : 1 ?>)">
                                            <?= $u->is_active ? '<i class="fas fa-user-slash"></i> Nonaktifkan' : '<i class="fas fa-user-check"></i> Aktifkan' ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- MANAGE SAMPLE TAB -->
        <div id="samples-tab" class="profile-tab-content">
            <div class="content-header">
                <h2><i class="fas fa-box-open"></i> Manage Sample Creator</h2>
                <p>Pantau dan kelola pengiriman sampel produk gratis ke kreator affiliate langsung dari TAP API.</p>
            </div>

            <!-- Status Tabs (Mirrors TikTok Partner Center) -->
            <div class="tap-sample-tabs-container">
                <div class="tap-sample-tab active" data-status="" onclick="selectTapStatus(this)">
                    Semua <span class="tab-count" id="tapCount-all">0</span>
                </div>
                <div class="tap-sample-tab" data-status="PENDING" onclick="selectTapStatus(this)">
                    Perlu ditinjau <span class="tab-count" id="tapCount-pending">0</span>
                </div>
                <div class="tap-sample-tab" data-status="APPROVED" onclick="selectTapStatus(this)">
                    Siap dikirim <span class="tab-count" id="tapCount-approved">0</span>
                </div>
                <div class="tap-sample-tab" data-status="SHIPPED" onclick="selectTapStatus(this)">
                    Dikirim <span class="tab-count" id="tapCount-shipped">0</span>
                </div>
                <div class="tap-sample-tab" data-status="DELIVERED" onclick="selectTapStatus(this)">
                    Konten tertunda <span class="tab-count" id="tapCount-delivered">0</span>
                </div>
                <div class="tap-sample-tab" data-status="COMPLETED" onclick="selectTapStatus(this)">
                    Selesai <span class="tab-count" id="tapCount-completed">0</span>
                </div>
                <div class="tap-sample-tab" data-status="CANCELLED" onclick="selectTapStatus(this)">
                    Dibatalkan <span class="tab-count" id="tapCount-cancelled">0</span>
                </div>
            </div>

            <!-- Filters (Mirrors TikTok Partner Center) -->
            <div class="tap-sample-filters">
                <div class="filter-group">
                    <label for="tapFilterProductId">ID Produk</label>
                    <input type="text" id="tapFilterProductId" class="form-control-custom" placeholder="Cari ID Produk...">
                </div>
                <div class="filter-group">
                    <label for="tapFilterCampaignId">ID Campaign</label>
                    <input type="text" id="tapFilterCampaignId" class="form-control-custom" placeholder="Cari ID Campaign...">
                </div>
                <div class="filter-group">
                    <label for="tapFilterUsername">Nama Pengguna Kreator</label>
                    <input type="text" id="tapFilterUsername" class="form-control-custom" placeholder="Cari username kreator...">
                </div>
                <div class="filter-group button-group">
                    <button class="btn-primary-custom" style="border-radius: var(--radius-sm);" onclick="loadTapSampleRequests()"><i class="fas fa-search"></i> Cari</button>
                    <button class="btn-secondary-custom" style="height: 48px;" onclick="resetTapFilters()"><i class="fas fa-undo"></i> Reset</button>
                </div>
            </div>

            <!-- Sample Requests Card -->
            <div class="profile-card">
                <div style="overflow-x: auto;">
                    <table class="table-custom" id="tapSampleTable">
                        <thead>
                            <tr>
                                <th>Produk & Campaign</th>
                                <th>Kreator</th>
                                <th>Qty</th>
                                <th>Status</th>
                                <th>Sisa Waktu / Tanggal</th>
                                <th style="text-align: right;">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="tap-pagination-container" id="tapPagination" style="display: none;">
                    <div class="tap-pagination-info" id="tapPaginationInfo">Menampilkan 0 - 0 data</div>
                    <div class="tap-pagination-buttons">
                        <button class="tap-pagination-btn" id="tapPrevBtn" onclick="tapChangePage(-1)"><i class="fas fa-chevron-left"></i> Sebelum</button>
                        <button class="tap-pagination-btn" id="tapNextBtn" onclick="tapChangePage(1)">Berikut <i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MONITORING CREATOR TAB -->
        <div id="monitoring-tab" class="profile-tab-content">
            <div class="content-header">
                <h2><i class="fas fa-tv"></i> Monitoring Creator</h2>
                <p>Pantau metrik penjualan (GMV), video, dan pengiriman sample creator Anda.</p>
            </div>
            
            <!-- Filters -->
            <div class="profile-card" style="margin-bottom: 20px; padding: 20px;">
                <div class="filters-flex-custom">
                    <input type="text" class="form-control-custom" id="profMonSearchBox" placeholder="🔍 Cari creator, brand, kategori..." oninput="filterCreators()" style="max-width: 320px;">
                    <select class="form-control-custom" id="profMonFilterStatus" onchange="filterCreators()" style="max-width: 200px;">
                        <option value="">Semua Status</option>
                        <option value="ACTIVE">ACTIVE</option>
                        <option value="SAMPLE_SENT">SAMPLE_SENT</option>
                    </select>
                    <select class="form-control-custom" id="profMonFilterOrder" onchange="filterCreators()" style="max-width: 200px;">
                        <option value="">Semua Transaksi</option>
                        <option value="with_order">Ada Transaksi</option>
                        <option value="no_order">Belum Transaksi</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="profile-card">
                <table class="table-custom" id="profileCreatorTable">
                    <thead>
                        <tr>
                            <th>Creator</th>
                            <th>Status</th>
                            <th>GMV 30d</th>
                            <th>Keranjang</th>
                            <th>Sample</th>
                            <th>Video</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Rendered by JS -->
                    </tbody>
                </table>
                
                <!-- Pagination Controls -->
                <div class="pagination-container-custom">
                    <div class="page-size-selector">
                        <label>Tampilkan: 
                            <select id="profMonPageSize" onchange="changePageSize(this.value)" class="form-control-custom" style="width: auto; height: 36px; padding: 0 10px; display: inline-block;">
                                <option value="10" selected>10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </label>
                    </div>
                    <div class="page-navigation">
                        <button id="profMonPrevBtn" onclick="prevPage()" class="btn-secondary-custom" style="height: 36px; padding: 0 16px; font-size: 12px; margin-right: 8px;">Sebelumnya</button>
                        <span id="profMonPageInfo" style="font-size: 13px; font-weight: 600; color: var(--muted-2);">Halaman 1 dari 1</span>
                        <button id="profMonNextBtn" onclick="nextPage()" class="btn-secondary-custom" style="height: 36px; padding: 0 16px; font-size: 12px; margin-left: 8px;">Berikutnya</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- POPUP MODAL TAMBAH USER CUSTOM -->
<div id="addUserModalCustom" class="modal-custom">
    <div class="modal-content-custom">
        <div class="modal-header-custom">
            <h3><i class="fas fa-user-plus"></i> Tambah User Bawahan</h3>
            <span class="close-custom" onclick="closeAddUserModalCustom()">&times;</span>
        </div>
        <div class="modal-body-custom">
            <form id="addUserFormCustom" onsubmit="submitAddUserCustom(event)">
                <div class="form-group-custom">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control-custom" required>
                </div>
                <div class="form-group-custom">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control-custom" required placeholder="Minimal 6 karakter">
                </div>
                <div class="form-group-custom">
                    <label>Nama Lengkap</label>
                    <input type="text" name="full_name" class="form-control-custom" required>
                </div>
                <div class="form-group-custom">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control-custom" required>
                </div>
                <div class="form-group-custom">
                    <label>Role</label>
                    <select name="role" class="form-control-custom" required>
                        <?php if ($role === 'ADMIN'): ?>
                            <option value="BD">BD (Business Development)</option>
                            <option value="IS">IS (Influencer Specialist)</option>
                            <option value="ADMIN">ADMIN</option>
                        <?php else: ?>
                            <option value="<?= $role ?>"><?= $role ?></option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="flex-buttons-custom">
                    <button type="submit" class="btn-primary-custom"><i class="fas fa-save"></i> Simpan User</button>
                    <button type="button" class="btn-secondary-custom" onclick="closeAddUserModalCustom()">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* CSS DASHBOARD PROFILE */
.profile-dashboard-container {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 30px;
    min-height: calc(100vh - 120px);
    padding: 30px;
    max-width: 1600px;
    margin: 0 auto;
}

.profile-sidebar {
    background: var(--panel);
    border: 1px solid var(--stroke);
    border-radius: var(--radius-lg);
    padding: 30px 20px;
    display: flex;
    flex-direction: column;
    gap: 25px;
    backdrop-filter: blur(14px);
    height: fit-content;
}

.sidebar-header {
    display: flex;
    align-items: center;
    gap: 15px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--stroke);
}

.sidebar-avatar i {
    font-size: 48px;
    color: var(--purple-2);
}

.sidebar-user-info h4 {
    font-size: 16px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 4px;
}

.role-badge {
    display: inline-block;
    padding: 2px 8px;
    font-size: 10px;
    font-weight: 800;
    border-radius: 6px;
    text-transform: uppercase;
}
.role-badge.admin { background: rgba(255, 79, 101, 0.15); color: var(--red); border: 1px solid rgba(255, 79, 101, 0.3); }
.role-badge.is { background: rgba(124, 60, 255, 0.15); color: var(--purple-2); border: 1px solid rgba(124, 60, 255, 0.3); }
.role-badge.bd { background: rgba(16, 223, 240, 0.15); color: var(--cyan); border: 1px solid rgba(16, 223, 240, 0.3); }

.sidebar-menu-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.sidebar-menu-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    color: var(--muted-2);
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    border-radius: var(--radius-sm);
    transition: all 0.2s ease;
}

.sidebar-menu-item i {
    font-size: 16px;
    transition: transform 0.2s ease;
}

.sidebar-menu-item:hover {
    background: rgba(255, 255, 255, 0.03);
    color: #fff;
}

.sidebar-menu-item:hover i {
    transform: scale(1.1);
}

.sidebar-menu-item.active {
    background: linear-gradient(135deg, rgba(124, 60, 255, 0.2), rgba(192, 44, 255, 0.15));
    border: 1px solid var(--stroke-strong);
    color: #fff;
}

.profile-content-area {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.profile-tab-content {
    display: none;
    animation: fadeIn 0.3s ease;
}

.profile-tab-content.active {
    display: block;
}

.content-header {
    margin-bottom: 25px;
}

.content-header h2 {
    font-size: 24px;
    font-weight: 800;
    margin-bottom: 6px;
}

.content-header p {
    color: var(--muted);
    font-size: 14px;
}

.flex-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.profile-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
}

.profile-card {
    background: var(--panel);
    border: 1px solid var(--stroke);
    border-radius: var(--radius-lg);
    padding: 30px;
    backdrop-filter: blur(14px);
    box-shadow: var(--shadow);
}

.profile-card h3 {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 20px;
    border-bottom: 1px solid rgba(112, 136, 185, 0.1);
    padding-bottom: 12px;
}

.form-group-custom {
    margin-bottom: 20px;
}

.form-group-custom label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: var(--muted-2);
    margin-bottom: 8px;
}

.form-control-custom {
    width: 100%;
    height: 48px;
    background: rgba(8, 18, 34, 0.72);
    border: 1px solid var(--stroke);
    border-radius: var(--radius-sm);
    color: #fff;
    padding: 0 16px;
    font-family: inherit;
    font-size: 14px;
    transition: all 0.2s ease;
}

.form-control-custom:focus {
    outline: none;
    border-color: var(--purple);
    box-shadow: 0 0 15px rgba(124, 60, 255, 0.15);
}

.btn-primary-custom {
    height: 48px;
    padding: 0 24px;
    background: linear-gradient(135deg, #5a25d8, #7f34ff);
    color: #fff;
    border: 0;
    border-radius: 999px;
    font-weight: 800;
    font-size: 14px;
    cursor: pointer;
    box-shadow: var(--glow-purple);
    transition: all 0.2s ease;
}

.btn-primary-custom:hover {
    transform: translateY(-1px);
    box-shadow: 0 0 25px rgba(124, 60, 255, 0.45);
}

.info-card {
    height: fit-content;
}

.info-item {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid rgba(112, 136, 185, 0.06);
    font-size: 13px;
}

.info-item:last-child {
    border-bottom: 0;
}

.info-label {
    color: var(--muted);
    font-weight: 600;
}

.info-value {
    color: #fff;
    font-weight: 700;
}

/* TABLE STYLING */
.table-custom {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.table-custom th {
    text-align: left;
    padding: 14px 18px;
    color: var(--muted);
    font-weight: 700;
    border-bottom: 1px solid var(--stroke);
}

.table-custom td {
    padding: 16px 18px;
    border-bottom: 1px solid rgba(112, 136, 185, 0.08);
}

.user-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-cell i {
    font-size: 32px;
    color: var(--purple);
}

.user-sub {
    display: block;
    font-size: 11px;
}

.product-cell {
    display: flex;
    align-items: center;
    gap: 12px;
    max-width: 320px;
}

.product-cell img {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-sm);
    object-fit: cover;
}

.product-placeholder {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-sm);
    background: rgba(255,255,255,0.05);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--muted);
}

.status-indicator {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-weight: 700;
}

.status-indicator:before {
    content: "";
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.status-indicator.active { color: var(--green); }
.status-indicator.active:before { background: var(--green); box-shadow: 0 0 8px var(--green); }
.status-indicator.inactive { color: var(--muted); }
.status-indicator.inactive:before { background: var(--muted); }

.btn-toggle {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-toggle.deactivate {
    border: 1px solid rgba(255, 79, 101, 0.3);
    background: rgba(255, 79, 101, 0.1);
    color: var(--red);
}

.btn-toggle.deactivate:hover {
    background: var(--red);
    color: #fff;
}

.btn-toggle.activate {
    border: 1px solid rgba(57, 240, 138, 0.3);
    background: rgba(57, 240, 138, 0.1);
    color: var(--green);
}

.btn-toggle.activate:hover {
    background: var(--green);
    color: #030914;
}

.status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 99px;
    font-size: 11px;
    font-weight: 800;
}
.status-badge.pending { background: rgba(245, 166, 35, 0.15); color: var(--orange); }
.status-badge.approved { background: rgba(57, 240, 138, 0.15); color: var(--green); }
.status-badge.shipped { background: rgba(16, 223, 240, 0.15); color: var(--cyan); }
.status-badge.willing { background: rgba(124, 60, 255, 0.15); color: var(--purple-2); }

/* CUSTOM MODAL FOR ADDING USER */
.modal-custom {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(0, 0, 0, 0.74);
    backdrop-filter: blur(10px);
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.modal-custom.show {
    display: flex;
}

.modal-content-custom {
    width: 92%;
    max-width: 480px;
    border-radius: 24px;
    border: 1px solid var(--stroke);
    background: linear-gradient(160deg, rgba(19, 33, 59, 0.98), rgba(6, 14, 27, 0.98));
    box-shadow: var(--shadow);
    overflow: hidden;
}

.modal-header-custom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid rgba(112, 136, 185, 0.14);
}

.close-custom {
    font-size: 28px;
    color: var(--muted);
    cursor: pointer;
}

.close-custom:hover {
    color: #fff;
}

.modal-body-custom {
    padding: 24px;
}

.flex-buttons-custom {
    display: flex;
    gap: 12px;
    margin-top: 24px;
}

.btn-secondary-custom {
    height: 48px;
    padding: 0 24px;
    border: 1px solid var(--stroke);
    background: rgba(8, 18, 34, 0.72);
    color: #fff;
    border-radius: 999px;
    font-weight: 700;
    cursor: pointer;
}

@media(max-width: 992px) {
    .profile-dashboard-container {
        grid-template-columns: 1fr;
        padding: 15px;
    }
    .profile-grid {
        grid-template-columns: 1fr;
    }
}
.filters-flex-custom { display: flex; gap: 15px; align-items: center; flex-wrap: wrap; }
.pagination-container-custom { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 15px; border-top: 1px solid rgba(112, 136, 185, 0.08); flex-wrap: wrap; gap: 15px; }
.mon-gmv-link-custom { color: var(--green); font-weight: 700; cursor: pointer; text-decoration: none; }
.mon-gmv-link-custom:hover { text-decoration: underline; }
.creator-avatar-img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
.creator-avatar-placeholder { width: 36px; height: 36px; border-radius: 50%; background: var(--purple); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; }
.action-flex { display: flex; gap: 8px; align-items: center; }
.red-badge { background: rgba(255, 79, 101, 0.15); color: var(--red); border: 1px solid rgba(255, 79, 101, 0.3); }
.stat-span { font-weight: 600; font-size: 13px; }

/* ==========================================================================
   TAP INTEGRATION STYLING
   ========================================================================== */
.tap-sample-tabs-container {
    display: flex;
    border-bottom: 2px solid var(--stroke);
    margin-bottom: 25px;
    gap: 15px;
    overflow-x: auto;
    padding-bottom: 5px;
}

.tap-sample-tab {
    padding: 12px 16px;
    color: var(--muted-2);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    border-bottom: 3px solid transparent;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.tap-sample-tab:hover {
    color: #fff;
}

.tap-sample-tab.active {
    color: var(--cyan);
    border-bottom-color: var(--cyan);
}

.tab-count {
    font-size: 10px;
    background: rgba(255, 255, 255, 0.08);
    padding: 2px 6px;
    border-radius: 999px;
    color: var(--muted-2);
}

.tap-sample-tab.active .tab-count {
    background: rgba(16, 223, 240, 0.15);
    color: var(--cyan);
}

.tap-sample-filters {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr auto;
    gap: 15px;
    align-items: flex-end;
    margin-bottom: 25px;
    background: rgba(8, 18, 34, 0.45);
    border: 1px solid var(--stroke);
    padding: 20px;
    border-radius: var(--radius-md);
}

.tap-sample-filters .filter-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.tap-sample-filters .filter-group label {
    font-size: 11px;
    font-weight: 700;
    color: var(--muted-2);
}

.tap-sample-filters .button-group {
    display: flex;
    flex-direction: row;
    gap: 8px;
}

.tap-sample-filters button {
    height: 48px;
}

.btn-sm-custom {
    padding: 8px 14px;
    font-size: 12px;
    border-radius: 8px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 0;
    transition: all 0.2s ease;
}

.btn-approve-tap {
    background: rgba(57, 240, 138, 0.12);
    color: var(--green);
    border: 1px solid rgba(57, 240, 138, 0.25);
}

.btn-approve-tap:hover {
    background: var(--green);
    color: #030914;
    transform: translateY(-1px);
    box-shadow: 0 0 12px rgba(57, 240, 138, 0.2);
}

.btn-reject-tap {
    background: rgba(255, 79, 101, 0.12);
    color: var(--red);
    border: 1px solid rgba(255, 79, 101, 0.25);
}

.btn-reject-tap:hover {
    background: var(--red);
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 0 12px rgba(255, 79, 101, 0.2);
}

.btn-logistics-tap {
    background: rgba(16, 223, 240, 0.12);
    color: var(--cyan);
    border: 1px solid rgba(16, 223, 240, 0.25);
}

.btn-logistics-tap:hover {
    background: var(--cyan);
    color: #030914;
    transform: translateY(-1px);
    box-shadow: 0 0 12px rgba(16, 223, 240, 0.2);
}

.status-badge.tap-pending { background: rgba(245, 166, 35, 0.15); color: var(--orange); border: 1px solid rgba(245, 166, 35, 0.3); }
.status-badge.tap-approved { background: rgba(124, 60, 255, 0.15); color: var(--purple-2); border: 1px solid rgba(124, 60, 255, 0.3); }
.status-badge.tap-shipped { background: rgba(16, 223, 240, 0.15); color: var(--cyan); border: 1px solid rgba(16, 223, 240, 0.3); }
.status-badge.tap-delivered { background: rgba(57, 240, 138, 0.15); color: var(--green); border: 1px solid rgba(57, 240, 138, 0.3); }
.status-badge.tap-completed { background: rgba(57, 240, 138, 0.15); color: var(--green); border: 1px solid rgba(57, 240, 138, 0.3); }
.status-badge.tap-rejected { background: rgba(255, 79, 101, 0.15); color: var(--red); border: 1px solid rgba(255, 79, 101, 0.3); }
.status-badge.tap-cancelled { background: rgba(142, 155, 182, 0.15); color: var(--muted); border: 1px solid rgba(142, 155, 182, 0.3); }

/* Logistics Timeline (Vertical) */
.logistics-timeline {
    position: relative;
    padding-left: 28px;
    margin: 15px 0;
}

.logistics-timeline::before {
    content: '';
    position: absolute;
    left: 7px;
    top: 6px;
    bottom: 6px;
    width: 2px;
    background: rgba(112, 136, 185, 0.15);
}

.timeline-item {
    position: relative;
    margin-bottom: 24px;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -25px;
    top: 5px;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--muted);
    border: 2px solid var(--bg-2);
    z-index: 2;
    transition: all 0.2s ease;
}

.timeline-item.active::before {
    background: var(--cyan);
    box-shadow: 0 0 10px var(--cyan);
    width: 10px;
    height: 10px;
    left: -26px;
}

.timeline-time {
    font-size: 11px;
    color: var(--muted);
    font-weight: 500;
    margin-bottom: 3px;
}

.timeline-status {
    font-size: 13px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 3px;
}

.timeline-desc {
    font-size: 12px;
    color: var(--muted-2);
    line-height: 1.4;
}

/* Pagination */
.tap-pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid rgba(112, 136, 185, 0.08);
}
.tap-pagination-info {
    font-size: 13px;
    color: var(--muted);
}
.tap-pagination-buttons {
    display: flex;
    gap: 10px;
}
.tap-pagination-btn {
    padding: 8px 16px;
    border-radius: 99px;
    border: 1px solid var(--stroke);
    background: rgba(8, 18, 34, 0.65);
    color: #fff;
    font-weight: 700;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.tap-pagination-btn:hover:not(:disabled) {
    border-color: var(--stroke-strong);
    background: rgba(124, 60, 255, 0.15);
}
.tap-pagination-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.toast-container {
    position: fixed;
    bottom: 25px;
    right: 25px;
    z-index: 10000;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.toast-custom {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 20px;
    border-radius: var(--radius-md);
    background: rgba(15, 29, 53, 0.95);
    border-left: 4px solid var(--purple);
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    box-shadow: var(--shadow);
    backdrop-filter: blur(12px);
    transform: translateY(20px);
    opacity: 0;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.toast-custom.show {
    transform: translateY(0);
    opacity: 1;
}

.toast-custom.success { border-left-color: var(--green); }
.toast-custom.error { border-left-color: var(--red); }
.toast-custom.info { border-left-color: var(--cyan); }

.toast-custom i {
    font-size: 16px;
}
.toast-custom.success i { color: var(--green); }
.toast-custom.error i { color: var(--red); }
.toast-custom.info i { color: var(--cyan); }
</style>

<script>
function switchTab(tabId, el) {
    // Sembunyikan semua tab content
    document.querySelectorAll('.profile-tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    // Tampilkan tab yang dipilih
    document.getElementById(tabId).classList.add('active');

    // Ubah status aktif menu item sidebar
    document.querySelectorAll('.sidebar-menu-item').forEach(item => {
        item.classList.remove('active');
    });
    el.classList.add('active');

    // JIKA pindah ke samples-tab, trigger loading data dari TAP
    if (tabId === 'samples-tab') {
        loadTapSampleRequests();
    }
}

function saveProfile(event) {
    event.preventDefault();
    const form = document.getElementById('editProfileForm');
    const formData = new FormData(form);

    fetch('<?= base_url('profile/save') ?>', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(e => {
        showToast('Gagal memperbarui profil: ' + e, 'error');
    });
}

function openAddUserModalCustom() {
    document.getElementById('addUserModalCustom').classList.add('show');
}

function closeAddUserModalCustom() {
    document.getElementById('addUserModalCustom').classList.remove('show');
}

function submitAddUserCustom(event) {
    event.preventDefault();
    const form = document.getElementById('addUserFormCustom');
    const formData = new FormData(form);

    fetch('<?= base_url('profile/add_managed_user') ?>', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeAddUserModalCustom();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(e => {
        showToast('Gagal menambahkan user: ' + e, 'error');
    });
}

function toggleUserStatus(userId, status) {
    const formData = new FormData();
    formData.append('user_id', userId);
    formData.append('is_active', status);

    fetch('<?= base_url('profile/toggle_status') ?>', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(e => {
        showToast('Gagal mengubah status: ' + e, 'error');
    });
}

// CLIENT-SIDE PAGINATION, SEARCH, FILTER UNTUK MONITORING CREATOR
let currentPage = 1;
let pageSize = 10;
let filteredCreators = [];
const creatorsData = <?php echo json_encode($creators); ?>;

function initCreatorTable() {
    filterCreators();
}

function filterCreators() {
    const searchVal = document.getElementById('profMonSearchBox').value.toLowerCase();
    const statusVal = document.getElementById('profMonFilterStatus').value;
    const orderVal = document.getElementById('profMonFilterOrder').value;

    filteredCreators = creatorsData.filter(c => {
        const matchSearch = c.username.toLowerCase().includes(searchVal) || 
                            (c.full_name && c.full_name.toLowerCase().includes(searchVal)) ||
                            (c.brand_name && c.brand_name.toLowerCase().includes(searchVal)) ||
                            (c.category && c.category.toLowerCase().includes(searchVal));
        const matchStatus = !statusVal || c.status === statusVal;
        const matchOrder = !orderVal || 
                           (orderVal === 'with_order' && c.has_orders) || 
                           (orderVal === 'no_order' && !c.has_orders);
        return matchSearch && matchStatus && matchOrder;
    });

    // Urutkan berdasarkan GMV terbesar
    filteredCreators.sort((a, b) => parseFloat(b.total_gmv_30d || 0) - parseFloat(a.total_gmv_30d || 0));

    currentPage = 1;
    renderCreatorTable();
}

function renderCreatorTable() {
    const tbody = document.querySelector('#profileCreatorTable tbody');
    const startIdx = (currentPage - 1) * pageSize;
    const endIdx = startIdx + pageSize;
    const pageData = filteredCreators.slice(startIdx, endIdx);

    if (pageData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Tidak ada creator ditemukan</td></tr>';
        document.getElementById('profMonPageInfo').textContent = 'Halaman 0 dari 0';
        document.getElementById('profMonPrevBtn').disabled = true;
        document.getElementById('profMonNextBtn').disabled = true;
        return;
    }

    tbody.innerHTML = pageData.map(c => {
        const avatarHtml = `
            <div class="mon-avatar">
                ${c.avatar_url ? `<img src="${c.avatar_url}" alt="">` : c.username.substr(0, 1).toUpperCase()}
            </div>
        `;
            
        const creatorCellHtml = `
            <div class="mon-creator-cell">
                ${avatarHtml}
                <div>
                    <div class="mon-creator-name">@${c.username}</div>
                    <div class="mon-creator-brand">
                        ${c.full_name || ''} ${c.brand_name ? ' · ' + c.brand_name : ''}
                    </div>
                </div>
            </div>
        `;
            
        const statusHtml = c.status === 'ACTIVE' ? 
            `<span class="status-badge approved">● ACTIVE</span>` : 
            `<span class="status-badge pending">📦 SAMPLE_SENT</span>`;
            
        const gmvHtml = c.total_gmv_30d > 0 ? 
            `<span class="mon-gmv-link" onclick="openGmvBreakdown(${c.id}, '${c.username}')">${fmtRpJs(c.total_gmv_30d)}</span>` : 
            `<span style="color:var(--mon-muted);font-size:11px">—</span>`;
            
        const orderHtml = c.has_orders ? 
            `<span class="status-badge approved">✅ Ada Transaksi</span>` : 
            `<span class="status-badge red-badge">⏳ Belum</span>`;
            
        const sampleHtml = `<span class="stat-span" style="color:var(--orange)">${c.sample_count} produk</span>`;
        const videoHtml = `<span class="stat-span" style="color:var(--purple-2)">${c.video_count} video</span>`;
        
        const actionBtn = c.has_orders && c.status === 'ACTIVE' ? 
            `<button class="mon-btn mon-btn-purple mon-btn-sm" onclick="openWillingModal(${c.id}, '${c.username}')"><i class="ri-gift-line"></i> Sample</button>` : '';

        const actionsHtml = `
            <div class="mon-action-btns" style="display:flex; gap:8px;">
                <button class="mon-btn mon-btn-outline mon-btn-sm" onclick="openMonitoringDetail(${c.id}, '${c.username}')"><i class="ri-bar-chart-line"></i> Detail</button>
                ${actionBtn}
            </div>
        `;

        return `
            <tr>
                <td>${creatorCellHtml}</td>
                <td>${statusHtml}</td>
                <td>${gmvHtml}</td>
                <td>${orderHtml}</td>
                <td>${sampleHtml}</td>
                <td>${videoHtml}</td>
                <td>${actionsHtml}</td>
            </tr>
        `;
    }).join('');

    const totalPages = Math.ceil(filteredCreators.length / pageSize);
    document.getElementById('profMonPageInfo').textContent = `Halaman ${currentPage} dari ${totalPages || 1}`;
    document.getElementById('profMonPrevBtn').disabled = currentPage === 1;
    document.getElementById('profMonNextBtn').disabled = currentPage === totalPages || totalPages === 0;
}

function changePageSize(size) {
    pageSize = parseInt(size);
    currentPage = 1;
    renderCreatorTable();
}

function prevPage() {
    if (currentPage > 1) {
        currentPage--;
        renderCreatorTable();
    }
}

function nextPage() {
    const totalPages = Math.ceil(filteredCreators.length / pageSize);
    if (currentPage < totalPages) {
        currentPage++;
        renderCreatorTable();
    }
}

function fmtRpJs(val) {
    val = parseFloat(val);
    if (val >= 1000000) return 'Rp ' + (val/1000000).toFixed(1) + 'Jt';
    if (val >= 1000) return 'Rp ' + (val/1000).toFixed(0) + 'K';
    return 'Rp ' + val.toFixed(0);
}


// ==========================================================================
// TAP SAMPLES INTEGRATION JAVASCRIPT
// ==========================================================================
let tapSamples = [];
let tapFilteredSamples = [];
let tapCurrentPage = 1;
let tapPageSize = 10;
let selectedTapStatus = ""; // Kosong berarti "SEMUA"

function showCustomToast(message, type = 'success') {
    let container = document.getElementById('tapToastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'tapToastContainer';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    
    const toast = document.createElement('div');
    toast.className = `toast-custom ${type}`;
    
    let iconClass = 'fa-check-circle';
    if (type === 'error') iconClass = 'fa-times-circle';
    if (type === 'info') iconClass = 'fa-info-circle';
    
    toast.innerHTML = `<i class="fas ${iconClass}"></i><span>${message}</span>`;
    container.appendChild(toast);
    
    // trigger reflow
    setTimeout(() => toast.classList.add('show'), 10);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

function loadTapSampleRequests() {
    const tbody = document.querySelector('#tapSampleTable tbody');
    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center" style="padding: 50px 0;">
                <i class="fas fa-spinner fa-spin" style="font-size: 32px; color: var(--cyan); margin-bottom: 15px;"></i>
                <div style="color: var(--muted); font-size: 13px;">Mengambil data dari TAP API...</div>
            </td>
        </tr>
    `;
    
    const username = document.getElementById('tapFilterUsername').value.trim();
    const product_id = document.getElementById('tapFilterProductId').value.trim();
    
    const formData = new FormData();
    if (selectedTapStatus) {
        formData.append('status', selectedTapStatus);
    }
    if (username) {
        formData.append('username', username);
    }
    if (product_id) {
        formData.append('product_id', product_id);
    }
    
    fetch('<?= base_url('profile/get_tap_sample_requests') ?>', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            tapSamples = res.data || [];
            
            // local filtering untuk ID Campaign
            const campaignFilter = document.getElementById('tapFilterCampaignId').value.trim();
            if (campaignFilter) {
                tapFilteredSamples = tapSamples.filter(s => 
                    (s.campaign_id && s.campaign_id.toLowerCase().includes(campaignFilter.toLowerCase())) ||
                    (s.campaign_name && s.campaign_name.toLowerCase().includes(campaignFilter.toLowerCase()))
                );
            } else {
                tapFilteredSamples = tapSamples;
            }
            
            // update counts untuk tabs
            updateTabCounts();
            
            tapCurrentPage = 1;
            renderTapTable();
        } else {
            const isExpired = (res.message && (
                res.message.toLowerCase().includes('expired') || 
                res.message.toLowerCase().includes('credential') || 
                res.message.toLowerCase().includes('token')
            ));
            
            if (isExpired) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 50px 0;">
                            <i class="fas fa-key" style="font-size: 32px; color: var(--orange); margin-bottom: 15px;"></i>
                            <div style="font-weight: 700; color: #fff; font-size: 15px; margin-bottom: 8px;">Akses Token TikTok Expired</div>
                            <div style="color: var(--muted); font-size: 13px; max-width: 400px; margin: 0 auto 20px auto; line-height: 1.5;">
                                Kredensial akun Seller TikTok Anda telah kedaluwarsa. Silakan lakukan otentikasi ulang untuk memulihkan koneksi API.
                            </div>
                            <a href="<?= base_url('tts/authorize_seller') ?>" class="btn-primary-custom" style="text-decoration:none; display:inline-flex; align-items:center; gap:8px; height:42px; line-height:42px; border-radius:8px; padding:0 20px; font-weight:800; font-size:13px; box-shadow: var(--glow-purple);">
                                <i class="fas fa-sign-in-alt"></i> Otentikasi Ulang Seller
                            </a>
                        </td>
                    </tr>
                `;
            } else {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center text-muted" style="padding: 40px 0;">
                            <i class="fas fa-exclamation-triangle" style="font-size: 28px; color: var(--red); margin-bottom: 12px;"></i>
                            <div style="font-weight:700; margin-bottom:6px;">Gagal Memuat Data</div>
                            <div>${res.message || 'Pastikan otentikasi Seller TikTok valid.'}</div>
                        </td>
                    </tr>
                `;
            }
            showCustomToast(res.message || 'Gagal memuat data dari TAP API.', 'error');
        }
    })
    .catch(err => {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted" style="padding: 40px 0;">
                    <i class="fas fa-wifi" style="font-size: 28px; color: var(--red); margin-bottom: 12px;"></i>
                    <div style="font-weight:700; margin-bottom:6px;">Kesalahan Koneksi</div>
                    <div>Gagal terhubung dengan server Toopai.</div>
                </td>
            </tr>
        `;
        showCustomToast('Koneksi bermasalah: ' + err, 'error');
    });
}

function updateTabCounts() {
    if (!selectedTapStatus) {
        const counts = {
            all: tapFilteredSamples.length,
            pending: 0,
            approved: 0,
            shipped: 0,
            delivered: 0,
            completed: 0,
            cancelled: 0
        };
        
        tapFilteredSamples.forEach(s => {
            const status = (s.status || '').toUpperCase();
            if (status === 'PENDING') counts.pending++;
            else if (status === 'APPROVED' || status === 'AWAITING_SHIPMENT') counts.approved++;
            else if (status === 'SHIPPED') counts.shipped++;
            else if (status === 'DELIVERED') counts.delivered++;
            else if (status === 'COMPLETED') counts.completed++;
            else if (status === 'CANCELLED' || status === 'REJECTED') counts.cancelled++;
        });
        
        document.getElementById('tapCount-all').textContent = counts.all;
        document.getElementById('tapCount-pending').textContent = counts.pending;
        document.getElementById('tapCount-approved').textContent = counts.approved;
        document.getElementById('tapCount-shipped').textContent = counts.shipped;
        document.getElementById('tapCount-delivered').textContent = counts.delivered;
        document.getElementById('tapCount-completed').textContent = counts.completed;
        document.getElementById('tapCount-cancelled').textContent = counts.cancelled;
    }
}

function selectTapStatus(el) {
    document.querySelectorAll('.tap-sample-tab').forEach(tab => tab.classList.remove('active'));
    el.classList.add('active');
    selectedTapStatus = el.getAttribute('data-status');
    loadTapSampleRequests();
}

function resetTapFilters() {
    document.getElementById('tapFilterProductId').value = '';
    document.getElementById('tapFilterCampaignId').value = '';
    document.getElementById('tapFilterUsername').value = '';
    selectedTapStatus = '';
    document.querySelectorAll('.tap-sample-tab').forEach(tab => tab.classList.remove('active'));
    document.querySelector('.tap-sample-tab[data-status=""]').classList.add('active');
    loadTapSampleRequests();
}

function renderTapTable() {
    const tbody = document.querySelector('#tapSampleTable tbody');
    const paginator = document.getElementById('tapPagination');
    
    if (tapFilteredSamples.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted" style="padding: 40px 0;">Tidak ada data pengiriman sampel.</td></tr>';
        paginator.style.display = 'none';
        return;
    }
    
    paginator.style.display = 'flex';
    
    const startIdx = (tapCurrentPage - 1) * tapPageSize;
    const endIdx = startIdx + tapPageSize;
    const pageData = tapFilteredSamples.slice(startIdx, endIdx);
    
    tbody.innerHTML = pageData.map(s => {
        const imgHtml = s.product_image ? 
            `<img src="${s.product_image}" alt="Product" style="width: 44px; height: 44px; border-radius: 8px; object-fit: cover;">` : 
            `<div class="product-placeholder" style="width: 44px; height: 44px; border-radius: 8px; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,0.06);"><i class="fas fa-image" style="color:var(--muted);"></i></div>`;
            
        const productHtml = `
            <div style="display: flex; align-items: flex-start; gap: 12px; max-width: 420px;">
                ${imgHtml}
                <div>
                    <div style="font-weight: 700; color: #fff; font-size: 13px; line-height: 1.4; margin-bottom: 4px;">${s.product_name}</div>
                    <div style="font-size: 11px; color: var(--muted-2); margin-bottom: 2px;">ID Produk: <span style="font-family: monospace;">${s.product_id}</span></div>
                    <div style="font-size: 11px; color: var(--purple-2); font-weight: 600;">Campaign: ${s.campaign_name || 'N/A'} (<span style="font-family: monospace;">${s.campaign_id || 'N/A'}</span>)</div>
                </div>
            </div>
        `;
        
        const creatorHtml = `
            <div>
                <strong style="color: #fff;">@${s.creator_username}</strong>
            </div>
        `;
        
        const statusLower = (s.status || '').toLowerCase();
        let badgeClass = 'tap-pending';
        if (statusLower === 'pending') badgeClass = 'tap-pending';
        else if (statusLower === 'approved' || statusLower === 'awaiting_shipment') badgeClass = 'tap-approved';
        else if (statusLower === 'shipped') badgeClass = 'tap-shipped';
        else if (statusLower === 'delivered') badgeClass = 'tap-delivered';
        else if (statusLower === 'completed') badgeClass = 'tap-completed';
        else if (statusLower === 'rejected') badgeClass = 'tap-rejected';
        else if (statusLower === 'cancelled') badgeClass = 'tap-cancelled';
        
        const statusHtml = `<span class="status-badge ${badgeClass}">${s.status || 'PENDING'}</span>`;
        
        const timeHtml = `
            <div>
                <div style="font-size: 12px; font-weight: 600; color: #fff;">${s.request_date_formatted}</div>
                <div style="font-size: 10px; color: var(--muted); margin-top: 2px;">Expired: ${s.expire_date_formatted}</div>
            </div>
        `;
        
        let actionsHtml = '';
        if (statusLower === 'pending') {
            actionsHtml = `
                <div class="action-flex" style="justify-content: flex-end;">
                    <button class="btn-sm-custom btn-approve-tap" onclick="approveTapSample('${s.sample_request_id}', '${s.campaign_id}', '${s.product_id}', '${s.creator_username}')"><i class="fas fa-check"></i> Setujui</button>
                    <button class="btn-sm-custom btn-reject-tap" onclick="rejectTapSample('${s.sample_request_id}', '${s.campaign_id}', '${s.product_id}', '${s.creator_username}')"><i class="fas fa-times"></i> Tolak</button>
                </div>
            `;
        } else if (['shipped', 'delivered', 'completed'].includes(statusLower)) {
            actionsHtml = `
                <div class="action-flex" style="justify-content: flex-end;">
                    <button class="btn-sm-custom btn-logistics-tap" onclick="viewLogistics('${s.tracking_number}', '${s.status}', '${s.creator_username}', ${s.request_date_raw})"><i class="fas fa-truck"></i> Lihat logistik</button>
                </div>
            `;
        } else {
            actionsHtml = `
                <div style="text-align: right; color: var(--muted); font-size: 12px; font-style: italic;">
                    Tidak ada tindakan
                </div>
            `;
        }
        
        return `
            <tr>
                <td>${productHtml}</td>
                <td>${creatorHtml}</td>
                <td style="font-weight: 700; color: #fff;">${s.available_samples || 1}</td>
                <td>${statusHtml}</td>
                <td>${timeHtml}</td>
                <td>${actionsHtml}</td>
            </tr>
        `;
    }).join('');
    
    const totalPages = Math.ceil(tapFilteredSamples.length / tapPageSize);
    document.getElementById('tapPaginationInfo').textContent = `Menampilkan ${startIdx + 1} - ${Math.min(endIdx, tapFilteredSamples.length)} dari ${tapFilteredSamples.length} data`;
    document.getElementById('tapPrevBtn').disabled = tapCurrentPage === 1;
    document.getElementById('tapNextBtn').disabled = tapCurrentPage === totalPages || totalPages === 0;
}

function tapChangePage(dir) {
    tapCurrentPage += dir;
    renderTapTable();
}

function approveTapSample(requestId, campaignId, productId, username) {
    if (!confirm(`Apakah Anda yakin ingin MENYETUJUI permintaan sampel dari @${username}?`)) return;
    
    showCustomToast('Sedang memproses persetujuan sampel...', 'info');
    
    const formData = new FormData();
    formData.append('sample_request_id', requestId);
    formData.append('campaign_id', campaignId);
    formData.append('product_id', productId);
    formData.append('creator_username', username);
    
    fetch('<?= base_url('profile/approve_sample') ?>', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            showCustomToast('Sampel berhasil disetujui!', 'success');
            loadTapSampleRequests();
        } else {
            showCustomToast('Gagal menyetujui sampel: ' + (res.message || 'Error API'), 'error');
        }
    })
    .catch(err => {
        showCustomToast('Kesalahan koneksi: ' + err, 'error');
    });
}

function rejectTapSample(requestId, campaignId, productId, username) {
    const reason = prompt(`Masukkan alasan penolakan untuk @${username}:`, 'Persediaan sampel habis');
    if (reason === null) return;
    
    showCustomToast('Sedang memproses penolakan sampel...', 'info');
    
    const formData = new FormData();
    formData.append('sample_request_id', requestId);
    formData.append('campaign_id', campaignId);
    formData.append('product_id', productId);
    formData.append('creator_username', username);
    formData.append('reason', reason);
    
    fetch('<?= base_url('profile/reject_sample') ?>', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            showCustomToast('Sampel berhasil ditolak.', 'success');
            loadTapSampleRequests();
        } else {
            showCustomToast('Gagal menolak sampel: ' + (res.message || 'Error API'), 'error');
        }
    })
    .catch(err => {
        showCustomToast('Kesalahan koneksi: ' + err, 'error');
    });
}

function viewLogistics(trackingNumber, status, username, requestDate) {
    document.getElementById('logisticsCourierName').textContent = 'Loading...';
    document.getElementById('logisticsTrackingNumber').textContent = trackingNumber || '-';
    document.getElementById('logisticsTimelineContent').innerHTML = `
        <div style="text-align:center; padding: 25px 0; color:var(--muted);">
            <i class="fas fa-spinner fa-spin" style="font-size:24px; color:var(--cyan); margin-bottom:10px;"></i>
            <div>Memuat data pengiriman...</div>
        </div>
    `;
    document.getElementById('tapLogisticsModal').classList.add('show');
    
    const formData = new FormData();
    if (trackingNumber) formData.append('tracking_number', trackingNumber);
    formData.append('status', status);
    formData.append('creator_username', username);
    if (requestDate) formData.append('request_date', requestDate);
    
    fetch('<?= base_url('profile/get_logistics_info') ?>', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            document.getElementById('logisticsCourierName').textContent = res.courier;
            document.getElementById('logisticsTrackingNumber').textContent = res.tracking_number;
            
            const timelineHtml = `
                <div class="logistics-timeline">
                    ${res.logs.map((log, idx) => `
                        <div class="timeline-item ${idx === 0 ? 'active' : ''}">
                            <div class="timeline-time">${log.time}</div>
                            <div class="timeline-status">${log.status}</div>
                            <div class="timeline-desc">${log.desc}</div>
                        </div>
                    `).join('')}
                </div>
            `;
            document.getElementById('logisticsTimelineContent').innerHTML = timelineHtml;
        } else {
            document.getElementById('logisticsTimelineContent').innerHTML = `
                <div style="text-align:center; padding:20px; color:var(--muted);">
                    <i class="fas fa-exclamation-circle" style="font-size:24px; color:var(--red); margin-bottom:10px;"></i>
                    <div>${res.message || 'Logistik belum tersedia.'}</div>
                </div>
            `;
        }
    })
    .catch(err => {
        document.getElementById('logisticsTimelineContent').innerHTML = `
            <div style="text-align:center; padding:20px; color:var(--muted);">
                <i class="fas fa-exclamation-triangle" style="font-size:24px; color:var(--red); margin-bottom:10px;"></i>
                <div>Kesalahan sistem memuat data pengiriman.</div>
            </div>
        `;
    });
}

function closeLogisticsModal() {
    document.getElementById('tapLogisticsModal').classList.remove('show');
}

function copyResi() {
    const resi = document.getElementById('logisticsTrackingNumber').textContent;
    if (resi && resi !== '-') {
        navigator.clipboard.writeText(resi)
        .then(() => showCustomToast('Nomor resi berhasil disalin!', 'success'))
        .catch(() => showCustomToast('Gagal menyalin resi.', 'error'));
    }
}

window.addEventListener('click', function(event) {
    const modal = document.getElementById('tapLogisticsModal');
    if (event.target === modal) {
        closeLogisticsModal();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    initCreatorTable();
});
</script>

<!-- MODAL LIHAT LOGISTIK (TAP INTEGRATION) -->
<div id="tapLogisticsModal" class="modal">
    <div class="modal-content" style="max-width: 520px;">
        <div class="modal-header">
            <h3><i class="fas fa-truck"></i> Pelacakan Logistik Sampel</h3>
            <span class="close" onclick="closeLogisticsModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="logisticsMetaInfo" style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(112,136,185,0.15); padding-bottom: 15px; margin-bottom: 15px;">
                <div>
                    <div style="font-size: 11px; color: var(--muted); font-weight:700; text-transform:uppercase;">Ekspedisi</div>
                    <div id="logisticsCourierName" style="font-weight: 800; font-size: 15px; color: #fff; margin-top:4px;">-</div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 11px; color: var(--muted); font-weight:700; text-transform:uppercase;">No. Resi (Salin)</div>
                    <div id="logisticsTrackingNumber" style="font-weight: 800; font-size: 15px; color: var(--cyan); cursor: pointer; margin-top:4px; text-decoration: underline;" onclick="copyResi()">-</div>
                </div>
            </div>
            
            <div id="logisticsTimelineContent">
                <!-- Timeline Dinamis -->
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('is/monitoring', ['hide_monitoring_main_content' => true]); ?>

