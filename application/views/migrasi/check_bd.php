<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Data Migrasi - Toopai</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(135deg, #0a0e17 0%, #0f1420 100%); padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; }
        .header { background: rgba(17,24,39,0.95); border-radius: 24px; padding: 20px 30px; margin-bottom: 24px; border: 1px solid rgba(74,222,128,0.2); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; }
        .logo h1 { background: linear-gradient(135deg, #c0ffb0, #4ade80, #8b5cf6); -webkit-background-clip: text; background-clip: text; color: transparent; font-size: 24px; }
        .nav-links { display: flex; gap: 12px; }
        .nav-btn { background: rgba(139,92,246,0.1); border: 1px solid rgba(139,92,246,0.3); padding: 8px 20px; border-radius: 40px; color: #cbd5e1; text-decoration: none; font-size: 13px; transition: all 0.2s; }
        .nav-btn:hover, .nav-btn.active { background: linear-gradient(135deg, #8b5cf6, #3b82f6); color: white; border-color: transparent; }
        .card { background: rgba(17,24,39,0.95); border-radius: 20px; padding: 24px; border: 1px solid rgba(74,222,128,0.15); margin-bottom: 24px; }
        .card-title { font-size: 18px; font-weight: 600; color: #e2e8f0; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid #2a3346; padding-bottom: 12px; }
        .card-title i { color: #4ade80; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: rgba(0,0,0,0.3); border-radius: 16px; padding: 16px; text-align: center; }
        .stat-number { font-size: 28px; font-weight: 700; color: #4ade80; }
        .stat-label { font-size: 12px; color: #9aaebe; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #2a3346; font-size: 12px; }
        th { background: #1a1f2e; color: #4ade80; position: sticky; top: 0; }
        td { color: #cbd5e1; }
        .badge { display: inline-flex; padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: 500; }
        .badge-active { background: rgba(16,185,129,0.15); color: #10b981; }
        .badge-pending { background: rgba(245,158,11,0.15); color: #f59e0b; }
        .btn { padding: 8px 20px; border-radius: 40px; font-weight: 600; font-size: 12px; cursor: pointer; border: none; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-primary { background: linear-gradient(135deg, #4ade80, #22c55e); color: #0a0e17; }
        .btn-danger { background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #f87171; }
        .btn-secondary { background: #1e293b; border: 1px solid #2a3346; color: #cbd5e1; }
        .flex-between { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; }
        .table-wrapper { overflow-x: auto; max-height: 600px; overflow-y: auto; }
        .alert { padding: 12px 16px; border-radius: 12px; margin-bottom: 20px; }
        .alert-success { background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3); color: #10b981; }
        .alert-error { background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #f87171; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="logo">
            <h1><i class="fas fa-database"></i> Data Migrasi BD</h1>
            <p>Data brand yang sudah diimport dari sistem lama</p>
        </div>
        <div class="nav-links">
            <a href="<?= base_url('migrasi_bd') ?>" class="nav-btn">Import</a>
            <a href="<?= base_url('migrasi_bd/check') ?>" class="nav-btn active">Cek Data</a>
            <a href="<?= base_url('bd/dashboard') ?>" class="nav-btn">Ke Dashboard</a>
        </div>
    </div>
    
    <?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-error"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-title">
            <i class="fas fa-chart-line"></i>
            Statistik Migrasi
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?= $total ?></div>
                <div class="stat-label">Total Migrasi</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #10b981;"><?= $active_count ?></div>
                <div class="stat-label">Active (Task 3)</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #f59e0b;"><?= $pending_count ?></div>
                <div class="stat-label">Pending (Task 1)</div>
            </div>
        </div>
        
        <div class="flex-between" style="margin-bottom: 20px;">
            <div>
                <a href="<?= base_url('migrasi_bd') ?>" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Import Baru
                </a>
            </div>
            <?php if ($total > 0): ?>
            <button class="btn btn-danger" onclick="if(confirm('Hapus semua data migrasi?')) location.href='<?= base_url('migrasi_bd/rollback') ?>'">
                <i class="fas fa-trash-alt"></i> Hapus Semua Migrasi
            </button>
            <?php endif; ?>
        </div>
        
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Brand</th>
                        <th>Shop Name</th>
                        <th>Status</th>
                        <th>Task</th>
                        <th>BD ID</th>
                        <th>WhatsApp</th>
                        <th>Input By</th>
                        <th>Source</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($migrated as $b): ?>
                    <tr>
                        <td><?= $b->id ?></td>
                        <td><strong><?= htmlspecialchars($b->name) ?></strong></td>
                        <td><?= htmlspecialchars($b->shop_name) ?></td>
                        <td>
                            <span class="badge <?= $b->status == 'ACTIVE' ? 'badge-active' : 'badge-pending' ?>">
                                <?= $b->status ?>
                            </span>
                        </td>
                        <td>Task <?= $b->current_task ?></td>
                        <td><?= $b->bd_id ?? '-' ?></td>
                        <td><?= htmlspecialchars($b->whatsapp_number ?? '-') ?></td>
                        <td><?= htmlspecialchars($b->input_by ?? '-') ?></td>
                        <td><span class="badge badge-active"><?= $b->source ?></span></td>
                        <td><?= date('d/m/Y H:i', strtotime($b->created_at)) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($migrated)): ?>
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 60px;">
                            <i class="fas fa-inbox" style="font-size: 48px; color: #4a5568; margin-bottom: 16px; display: block;"></i>
                            <p style="color: #9aaebe;">Belum ada data migrasi</p>
                            <a href="<?= base_url('migrasi_bd') ?>" class="btn btn-primary" style="margin-top: 16px;">
                                <i class="fas fa-upload"></i> Import Sekarang
                            </a>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>