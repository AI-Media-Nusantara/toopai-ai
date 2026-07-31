<div class="dashboard-container">
    <div class="page-header">
        <h1 class="page-title">Admin Dashboard</h1>
        <p class="page-subtitle">Overview of Toopai Affiliate Platform</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-info">
                <div class="stat-value"><?= number_format($total_campaigns) ?></div>
                <div class="stat-label">Total Campaigns</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🏢</div>
            <div class="stat-info">
                <div class="stat-value"><?= number_format($total_brands) ?></div>
                <div class="stat-label">Total Brands</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-info">
                <div class="stat-value"><?= number_format($total_creators) ?></div>
                <div class="stat-label">Total Creators</div>
            </div>
        </div>
    </div>

    <!-- Users Section -->
    <div class="section-card">
        <h2 class="section-title">👑 User Management</h2>
        
        <!-- BD Users -->
        <div class="user-group">
            <h3>Brand Development (BD)</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Full Name</th>
                        <th>Status</th>
                        <th>Last Login</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bd_users)): ?>
                        <?php foreach ($bd_users as $user): ?>
                        <tr>
                            <td><?= $user->id ?></td>
                            <td><?= htmlspecialchars($user->username) ?></td>
                            <td><?= htmlspecialchars($user->email) ?></td>
                            <td><?= htmlspecialchars($user->full_name ?? '-') ?></td>
                            <td><span class="badge badge-success">Active</span></td>
                            <td><?= $user->last_login ? date('d M Y H:i', strtotime($user->last_login)) : '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">No BD users found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- IS Users -->
        <div class="user-group" style="margin-top: 32px;">
            <h3>Influencer Success (IS)</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Full Name</th>
                        <th>Status</th>
                        <th>Last Login</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($is_users)): ?>
                        <?php foreach ($is_users as $user): ?>
                        <tr>
                            <td><?= $user->id ?></td>
                            <td><?= htmlspecialchars($user->username) ?></td>
                            <td><?= htmlspecialchars($user->email) ?></td>
                            <td><?= htmlspecialchars($user->full_name ?? '-') ?></td>
                            <td><span class="badge badge-success">Active</span></td>
                            <td><?= $user->last_login ? date('d M Y H:i', strtotime($user->last_login)) : '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">No IS users found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.dashboard-container {
    animation: fadeIn 0.3s ease;
}
.page-header {
    margin-bottom: 28px;
}
.page-title {
    font-size: 28px;
    font-weight: 700;
    color: #e2f0e8;
    margin-bottom: 6px;
}
.page-subtitle {
    color: #9aaebe;
    font-size: 14px;
}
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}
.stat-card {
    background: #111827;
    border-radius: 24px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    border: 1px solid #2a3346;
    transition: 0.2s;
}
.stat-card:hover {
    border-color: #4ade80;
}
.stat-icon {
    font-size: 48px;
}
.stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #e2f0e8;
}
.stat-label {
    color: #9aaebe;
    font-size: 13px;
}
.section-card {
    background: #111827;
    border-radius: 24px;
    padding: 24px;
    border: 1px solid #2a3346;
}
.section-title {
    font-size: 20px;
    font-weight: 600;
    color: #e2f0e8;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 1px solid #2a3346;
}
.user-group h3 {
    color: #bdf2c0;
    font-size: 16px;
    margin-bottom: 16px;
}
.data-table {
    width: 100%;
    border-collapse: collapse;
}
.data-table th,
.data-table td {
    padding: 12px 8px;
    text-align: left;
    border-bottom: 1px solid #2a3346;
    color: #cbd5e6;
    font-size: 13px;
}
.data-table th {
    color: #8e9eae;
    font-weight: 500;
}
.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 500;
}
.badge-success {
    background: rgba(74, 222, 128, 0.2);
    color: #4ade80;
}
.text-center {
    text-align: center;
}
</style>