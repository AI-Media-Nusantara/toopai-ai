<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Data BD - Toopai</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #0a0e17 0%, #0f1420 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        /* Header */
        .header {
            background: rgba(17, 24, 39, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 20px 30px;
            margin-bottom: 24px;
            border: 1px solid rgba(74, 222, 128, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        
        .logo h1 {
            background: linear-gradient(135deg, #c0ffb0, #4ade80, #8b5cf6);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-size: 24px;
        }
        
        .logo p {
            color: #8e9eae;
            font-size: 12px;
            margin-top: 4px;
        }
        
        .nav-links {
            display: flex;
            gap: 12px;
        }
        
        .nav-btn {
            background: rgba(139, 92, 246, 0.1);
            border: 1px solid rgba(139, 92, 246, 0.3);
            padding: 8px 20px;
            border-radius: 40px;
            color: #cbd5e1;
            text-decoration: none;
            font-size: 13px;
            transition: all 0.2s;
        }
        
        .nav-btn:hover, .nav-btn.active {
            background: linear-gradient(135deg, #8b5cf6, #3b82f6);
            color: white;
            border-color: transparent;
        }
        
        /* Cards */
        .card {
            background: rgba(17, 24, 39, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid rgba(74, 222, 128, 0.15);
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #e2e8f0;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 12px;
            border-bottom: 1px solid #2a3346;
        }
        
        .card-title i {
            color: #4ade80;
        }
        
        /* Upload Area */
        .upload-area {
            border: 2px dashed rgba(74, 222, 128, 0.3);
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: rgba(0,0,0,0.2);
        }
        
        .upload-area:hover {
            border-color: #4ade80;
            background: rgba(74, 222, 128, 0.05);
        }
        
        .upload-area i {
            font-size: 48px;
            color: #4ade80;
            margin-bottom: 12px;
        }
        
        .upload-area p {
            color: #9aaebe;
            font-size: 14px;
        }
        
        .upload-area .small {
            font-size: 11px;
            color: #6b7280;
            margin-top: 8px;
        }
        
        /* Table */
        .table-wrapper {
            overflow-x: auto;
            max-height: 500px;
            overflow-y: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 10px;
            text-align: left;
            border-bottom: 1px solid #2a3346;
            font-size: 13px;
        }
        
        th {
            background: #1a1f2e;
            color: #4ade80;
            font-weight: 600;
            position: sticky;
            top: 0;
        }
        
        td {
            color: #cbd5e1;
        }
        
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }
        
        .badge-success {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
        }
        
        .badge-warning {
            background: rgba(245, 158, 11, 0.15);
            color: #f59e0b;
        }
        
        .badge-info {
            background: rgba(59, 130, 246, 0.15);
            color: #3b82f6;
        }
        
        .badge-error {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
        }
        
        /* Progress Bar */
        .progress-container {
            background: #1a1f2e;
            border-radius: 10px;
            height: 8px;
            overflow: hidden;
            margin: 16px 0;
        }
        
        .progress-bar {
            background: linear-gradient(90deg, #4ade80, #8b5cf6);
            height: 100%;
            width: 0%;
            transition: width 0.3s;
            border-radius: 10px;
        }
        
        /* Buttons */
        .btn {
            padding: 10px 24px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4ade80, #22c55e);
            color: #0a0e17;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(74, 222, 128, 0.3);
        }
        
        .btn-secondary {
            background: #1e293b;
            border: 1px solid #2a3346;
            color: #cbd5e1;
        }
        
        .btn-secondary:hover {
            background: #2a3346;
        }
        
        .btn-danger {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
        }
        
        .flex-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        
        .stat-card {
            background: rgba(0,0,0,0.3);
            border-radius: 16px;
            padding: 16px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #4ade80;
        }
        
        .stat-label {
            font-size: 12px;
            color: #9aaebe;
            margin-top: 4px;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            backdrop-filter: blur(8px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: #111827;
            border-radius: 24px;
            padding: 28px;
            max-width: 500px;
            width: 90%;
            border: 1px solid #4ade80;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid #2a3346;
        }
        
        .modal-header h3 {
            color: #e2e8f0;
        }
        
        .modal-close {
            font-size: 28px;
            cursor: pointer;
            color: #9aaebe;
        }
        
        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(4px);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        
        .loading-overlay.active {
            display: flex;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 3px solid rgba(74, 222, 128, 0.2);
            border-top-color: #4ade80;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Alert */
        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10b981;
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
        }
        
        .alert-warning {
            background: rgba(245, 158, 11, 0.15);
            border: 1px solid rgba(245, 158, 11, 0.3);
            color: #f59e0b;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            th, td {
                padding: 8px 6px;
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Header -->
    <div class="header">
        <div class="logo">
            <h1><i class="fas fa-database"></i> Import Data BD</h1>
            <p>Import data brand dari CSV ke sistem Toopai</p>
        </div>
        <div class="nav-links">
            <a href="<?= base_url('migrasi_bd') ?>" class="nav-btn active">Import</a>
            <a href="<?= base_url('migrasi_bd/check') ?>" class="nav-btn">Cek Data</a>
            <a href="<?= base_url('bd/dashboard') ?>" class="nav-btn">Ke Dashboard</a>
        </div>
    </div>
    
    <!-- Alert Session -->
    <?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-error"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>
    
    <!-- Upload Card -->
    <div class="card">
        <div class="card-title">
            <i class="fas fa-upload"></i>
            Upload File CSV
        </div>
        
        <form id="uploadForm" enctype="multipart/form-data">
            <div class="upload-area" id="uploadArea">
                <i class="fas fa-file-csv"></i>
                <p>Klik atau drag & drop file CSV di sini</p>
                <p class="small">Format: Handler, Nama Brand, Unit Sold, Contact, Status</p>
                <input type="file" name="csv_file" id="csvFile" accept=".csv" style="display: none;">
            </div>
        </form>
        
        <div style="margin-top: 20px; display: flex; gap: 12px; justify-content: flex-end;">
            <button class="btn btn-secondary" id="clearBtn" style="display: none;">
                <i class="fas fa-trash-alt"></i> Bersihkan
            </button>
            <button class="btn btn-primary" id="importBtn" disabled>
                <i class="fas fa-database"></i> Import ke Database
            </button>
        </div>
    </div>
    
    <!-- Preview Card -->
    <div class="card" id="previewCard" style="display: none;">
        <div class="card-title">
            <i class="fas fa-eye"></i>
            Preview Data
            <span style="margin-left: auto; font-size: 12px;" id="previewCount"></span>
        </div>
        <div class="stats-grid" id="previewStats" style="margin-bottom: 16px;"></div>
        <div class="table-wrapper">
            <table id="previewTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Handler</th>
                        <th>Nama Brand</th>
                        <th>Unit Sold</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>BD Assignment</th>
                    </tr>
                </thead>
                <tbody id="previewBody">
                    <tr><td colspan="7" style="text-align: center;">Belum ada data</td></tr>
                </tbody>
            </table>
        </div>
        
        <!-- Progress -->
        <div id="progressSection" style="display: none;">
            <div class="progress-container">
                <div class="progress-bar" id="progressBar"></div>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 12px;">
                <span id="progressText">Memproses...</span>
                <span id="progressCount">0 / 0</span>
            </div>
        </div>
    </div>
    
    <!-- Result Card -->
    <div class="card" id="resultCard" style="display: none;">
        <div class="card-title">
            <i class="fas fa-chart-bar"></i>
            Hasil Import
        </div>
        <div id="resultContent"></div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
    <p style="color: white; margin-top: 16px;">Memproses import data...</p>
</div>

<script>
let previewData = [];
let totalDataCount = 0;
let baseUrl = '<?= base_url() ?>';

// DOM Elements
const uploadArea = document.getElementById('uploadArea');
const fileInput = document.getElementById('csvFile');
const importBtn = document.getElementById('importBtn');
const clearBtn = document.getElementById('clearBtn');
const previewCard = document.getElementById('previewCard');
const previewBody = document.getElementById('previewBody');
const previewCount = document.getElementById('previewCount');
const previewStats = document.getElementById('previewStats');
const resultCard = document.getElementById('resultCard');
const loadingOverlay = document.getElementById('loadingOverlay');

// Upload Area Events
uploadArea.addEventListener('click', () => fileInput.click());
uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.style.borderColor = '#4ade80';
    uploadArea.style.background = 'rgba(74, 222, 128, 0.05)';
});
uploadArea.addEventListener('dragleave', () => {
    uploadArea.style.borderColor = 'rgba(74, 222, 128, 0.3)';
    uploadArea.style.background = 'rgba(0,0,0,0.2)';
});
uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.style.borderColor = 'rgba(74, 222, 128, 0.3)';
    uploadArea.style.background = 'rgba(0,0,0,0.2)';
    const file = e.dataTransfer.files[0];
    if (file && file.name.endsWith('.csv')) {
        uploadFile(file);
    } else {
        showToast('Harap upload file CSV', 'error');
    }
});

fileInput.addEventListener('change', (e) => {
    if (fileInput.files.length > 0) {
        uploadFile(fileInput.files[0]);
    }
});

function uploadFile(file) {
    const formData = new FormData();
    formData.append('csv_file', file);
    
    showLoading('Membaca file CSV...');
    
    fetch(baseUrl + 'migrasi_bd/import_csv', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            previewData = data.data;
            totalDataCount = data.total;
            renderPreview(data);
            previewCard.style.display = 'block';
            importBtn.disabled = false;
            clearBtn.style.display = 'inline-block';
            showToast(`Berhasil memuat ${data.total} data (preview 10 data pertama)`, 'success');
        } else {
            showToast(data.message || 'Gagal memuat file', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        showToast('Error: ' + error.message, 'error');
    });
}

function renderPreview(data) {
    // Update stats
    previewStats.innerHTML = `
        <div class="stat-card">
            <div class="stat-number">${formatNumber(data.total)}</div>
            <div class="stat-label">Total Data</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color: #10b981;">${formatNumber(data.stats.active)}</div>
            <div class="stat-label">Online (Task 4)</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color: #f59e0b;">${formatNumber(data.stats.pending)}</div>
            <div class="stat-label">Pending (Task 1)</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color: ${data.stats.no_bd > 0 ? '#ef4444' : '#4ade80'};">${formatNumber(data.stats.no_bd)}</div>
            <div class="stat-label">BD Tidak Ditemukan</div>
        </div>
    `;
    
    previewCount.innerText = `${formatNumber(data.total)} total data (menampilkan ${data.data.length} preview)`;
    
    const tbody = previewBody;
    tbody.innerHTML = '';
    
    if (data.data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center;">Tidak ada data preview</td></tr>';
        return;
    }
    
    data.data.forEach((item, idx) => {
        const statusClass = item.is_online ? 'badge-success' : 'badge-warning';
        const statusText = item.is_online ? 'ONLINE' : (item.status || 'PENDING');
        const bdClass = item.bd_id ? 'badge-info' : 'badge-error';
        const bdText = item.bd_name || 'Tidak ditemukan';
        
        tbody.innerHTML += `
            <tr>
                <td>${idx + 1}</td>
                <td>${escapeHtml(item.handler)}</td>
                <td><strong>${escapeHtml(item.brand_name)}</strong></td>
                <td>${escapeHtml(item.unit_sold)}</td>
                <td>${escapeHtml(item.contact)}</td>
                <td><span class="badge ${statusClass}">${statusText}</span></td>
                <td><span class="badge ${bdClass}">${escapeHtml(bdText)}</span></td>
            </tr>
        `;
    });
    
    // Tambahkan info jika total data lebih dari preview
    if (data.total > data.data.length) {
        tbody.innerHTML += `
            <tr style="background: rgba(74, 222, 128, 0.05);">
                <td colspan="7" style="text-align: center; color: #4ade80;">
                    <i class="fas fa-info-circle"></i> 
                    Dan ${formatNumber(data.total - data.data.length)} data lainnya (akan diproses saat import)
                </td>
            </tr>
        `;
    }
}

// Import ke database
importBtn.addEventListener('click', async () => {
    if (totalDataCount === 0) {
        showToast('Tidak ada data untuk diimport', 'error');
        return;
    }
    
    const confirmed = confirm(
        `⚠️ Konfirmasi Import\n\n` +
        `Total data: ${formatNumber(totalDataCount)}\n` +
        `Online (Task 4): ${formatNumber(58)}\n` +
        `Pending (Task 1): ${formatNumber(4146)}\n` +
        `Skip (no BD): 4\n\n` +
        `Proses ini akan memakan waktu beberapa menit.\n\n` +
        `Lanjutkan?`
    );
    
    if (!confirmed) return;
    
    // Disable button
    importBtn.disabled = true;
    importBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
    
    // Tampilkan progress
    const progressSection = document.getElementById('progressSection');
    progressSection.style.display = 'block';
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const progressCount = document.getElementById('progressCount');
    
    progressBar.style.width = '0%';
    progressText.innerText = 'Mengimport data ke database...';
    progressCount.innerText = `0 / ${formatNumber(totalDataCount)}`;
    
    // Animasi progress palsu sambil menunggu
    let fakeProgress = 0;
    const fakeInterval = setInterval(() => {
        if (fakeProgress < 90) {
            fakeProgress += Math.random() * 5;
            progressBar.style.width = Math.min(fakeProgress, 90) + '%';
        }
    }, 500);
    
    try {
        const response = await fetch(baseUrl + 'migrasi_bd/process_import', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        clearInterval(fakeInterval);
        
        // Cek apakah response OK
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const text = await response.text();
        
        // Coba parse JSON
        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error('Response text:', text.substring(0, 500));
            throw new Error('Server mengembalikan response yang tidak valid. Cek log error.');
        }
        
        progressBar.style.width = '100%';
        progressText.innerText = 'Selesai!';
        progressCount.innerText = `${formatNumber(totalDataCount)} / ${formatNumber(totalDataCount)}`;
        
        if (result.success) {
            showResult(result.results);
            showToast('Import selesai!', 'success');
        } else {
            showToast(result.message || 'Gagal import', 'error');
            importBtn.disabled = false;
            importBtn.innerHTML = '<i class="fas fa-database"></i> Import ke Database';
        }
    } catch (error) {
        clearInterval(fakeInterval);
        console.error('Import error:', error);
        showToast('Error: ' + error.message, 'error');
        importBtn.disabled = false;
        importBtn.innerHTML = '<i class="fas fa-database"></i> Import ke Database';
        progressSection.style.display = 'none';
    }
});

function showResult(results) {
    resultCard.style.display = 'block';
    const resultContent = document.getElementById('resultContent');
    
    resultContent.innerHTML = `
        <div class="stats-grid">
            <div class="stat-card" style="background: rgba(16,185,129,0.1);">
                <div class="stat-number" style="color: #10b981;">${formatNumber(results.success_count)}</div>
                <div class="stat-label">✅ Berhasil Import</div>
            </div>
            <div class="stat-card" style="background: rgba(245,158,11,0.1);">
                <div class="stat-number" style="color: #f59e0b;">${formatNumber(results.skip_count)}</div>
                <div class="stat-label">⏭️ Skip (Sudah Ada)</div>
            </div>
            <div class="stat-card" style="background: rgba(139,92,246,0.1);">
                <div class="stat-number" style="color: #a78bfa;">${formatNumber(results.duplicate_count)}</div>
                <div class="stat-label">🔄 Duplikat (BD Lain)</div>
            </div>
            <div class="stat-card" style="background: rgba(239,68,68,0.1);">
                <div class="stat-number" style="color: #ef4444;">${formatNumber(results.fail_count)}</div>
                <div class="stat-label">❌ Gagal</div>
            </div>
        </div>
        
        <div style="margin-bottom: 16px; background: #1a1f2e; border-radius: 16px; padding: 16px;">
            <h4 style="color: #e2e8f0; margin-bottom: 12px;">📊 Detail Import:</h4>
            <div style="display: flex; gap: 24px; flex-wrap: wrap;">
                <div><i class="fas fa-chart-line" style="color: #10b981;"></i> Active (Task 4): <strong>${formatNumber(results.active_count)}</strong></div>
                <div><i class="fas fa-clock" style="color: #f59e0b;"></i> Pending (Task 1): <strong>${formatNumber(results.pending_count)}</strong></div>
                <div><i class="fas fa-copy" style="color: #a78bfa;"></i> Duplicate Flag: <strong>${formatNumber(results.duplicate_count)}</strong></div>
            </div>
        </div>
        
        ${results.errors.length > 0 ? `
        <div style="margin-top: 16px;">
            <h4 style="color: #ef4444; margin-bottom: 8px;">⚠️ Error Details:</h4>
            <div style="max-height: 200px; overflow-y: auto; background: #0f1420; border-radius: 12px; padding: 12px;">
                ${results.errors.map(e => `<div style="font-size: 11px; color: #f87171; margin-bottom: 4px;">• ${escapeHtml(e)}</div>`).join('')}
                ${results.errors.length >= 20 ? '<div style="font-size: 11px; color: #6b7280;">... dan lainnya</div>' : ''}
            </div>
        </div>
        ` : ''}
        
        <div class="flex-buttons" style="margin-top: 24px;">
            <button class="btn btn-primary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Import Lagi
            </button>
            <a href="${baseUrl}migrasi_bd/check" class="btn btn-secondary">
                <i class="fas fa-database"></i> Lihat Data Migrasi
            </a>
            <a href="${baseUrl}bd/dashboard" class="btn btn-secondary">
                <i class="fas fa-tachometer-alt"></i> Ke Dashboard
            </a>
        </div>
    `;
    
    // Sembunyikan progress section
    document.getElementById('progressSection').style.display = 'none';
    
    // Enable import button with different text
    importBtn.disabled = true;
    importBtn.innerHTML = '<i class="fas fa-check"></i> Import Selesai';
}

clearBtn.addEventListener('click', () => {
    previewData = [];
    totalDataCount = 0;
    previewCard.style.display = 'none';
    resultCard.style.display = 'none';
    importBtn.disabled = true;
    importBtn.innerHTML = '<i class="fas fa-database"></i> Import ke Database';
    clearBtn.style.display = 'none';
    fileInput.value = '';
    showToast('Preview dibersihkan', 'info');
});

function showLoading(message) {
    const loadingOverlay = document.getElementById('loadingOverlay');
    const loadingText = document.querySelector('#loadingOverlay p');
    if (loadingText) loadingText.innerText = message;
    loadingOverlay.classList.add('active');
}

function hideLoading() {
    const loadingOverlay = document.getElementById('loadingOverlay');
    loadingOverlay.classList.remove('active');
}

function showToast(message, type = 'success') {
    let toast = document.getElementById('globalToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'globalToast';
        document.body.appendChild(toast);
    }
    
    const bgColor = type === 'success' ? '#10b981' : (type === 'error' ? '#ef4444' : '#f59e0b');
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: ${bgColor};
        color: white;
        padding: 12px 20px;
        border-radius: 12px;
        font-size: 13px;
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;
    toast.style.display = 'block';
    setTimeout(() => {
        toast.style.display = 'none';
    }, 3000);
}

function formatNumber(num) {
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(0) + 'K';
    return num.toString();
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}
</script>
</body>
</html>