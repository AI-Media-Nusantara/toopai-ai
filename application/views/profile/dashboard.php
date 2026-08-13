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
                <p>Pantau riwayat dan pengiriman produk sample ke creator affiliate Anda.</p>
            </div>
            <div class="profile-card">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Creator</th>
                            <th>Produk</th>
                            <th>Qty</th>
                            <th>Status</th>
                            <th>Tanggal Request</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sample_requests)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada data pengiriman sample.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sample_requests as $s): ?>
                                <tr>
                                    <td><strong>@<?= htmlspecialchars($s->creator_username) ?></strong></td>
                                    <td>
                                        <div class="product-cell">
                                            <?php if ($s->image_url): ?>
                                                <img src="<?= htmlspecialchars($s->image_url) ?>" alt="Product">
                                            <?php else: ?>
                                                <div class="product-placeholder"><i class="fas fa-image"></i></div>
                                            <?php endif; ?>
                                            <span><?= htmlspecialchars($s->product_name ?: 'Willingness Confirmation Only') ?></span>
                                        </div>
                                    </td>
                                    <td><?= (int)$s->quantity ?></td>
                                    <td>
                                        <span class="status-badge <?= strtolower($s->status) ?>">
                                            <?= $s->status ?>
                                        </span>
                                    </td>
                                    <td><?= date('d M Y H:i', strtotime($s->requested_at)) ?></td>
                                    <td class="text-muted italic"><?= htmlspecialchars($s->notes ?: '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
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
.filters-flex-custom { display: flex; gap: 15px; align-items: center; flex-wrap: wrap; }
.pagination-container-custom { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 15px; border-top: 1px solid rgba(112, 136, 185, 0.08); flex-wrap: wrap; gap: 15px; }
.mon-gmv-link-custom { color: var(--green); font-weight: 700; cursor: pointer; text-decoration: none; }
.mon-gmv-link-custom:hover { text-decoration: underline; }
.creator-avatar-img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
.creator-avatar-placeholder { width: 36px; height: 36px; border-radius: 50%; background: var(--purple); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; }
.action-flex { display: flex; gap: 8px; align-items: center; }
.red-badge { background: rgba(255, 79, 101, 0.15); color: var(--red); border: 1px solid rgba(255, 79, 101, 0.3); }
.stat-span { font-weight: 600; font-size: 13px; }
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

document.addEventListener('DOMContentLoaded', () => {
    initCreatorTable();
});
</script>

<?php $this->load->view('is/monitoring', ['hide_monitoring_main_content' => true]); ?>
