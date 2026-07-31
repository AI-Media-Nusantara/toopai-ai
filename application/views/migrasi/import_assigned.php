<div class="admin-dashboard">
    <div class="dashboard-header">
        <h1 class="dashboard-title">📥 Migrasi Data Creator (CSV)</h1>
        <div class="dashboard-actions">
            <span style="color:#9aaebe; font-size:12px;">Import creator dengan lock ke IS</span>
        </div>
    </div>

    <div class="section-card">
        <h2 class="section-title">📋 Upload CSV File</h2>
        <p style="color:#9aaebe; font-size:12px; margin-bottom:16px;">
            Format CSV dengan kolom: <b>USERNAME CREATOR, FOLLOWERS, CATEGORY, BRAND, Created By</b><br>
            Delimiter: Tab (\t) atau Comma (,)
        </p>
        
        <!-- Upload Area -->
        <div id="uploadArea" style="border:2px dashed #2a3346; border-radius:16px; padding:40px; text-align:center; cursor:pointer; transition:all 0.2s;"
             onmouseover="this.style.borderColor='#4ade80'" onmouseout="this.style.borderColor='#2a3346'"
             onclick="document.getElementById('csvFileInput').click()">
            <i class="fas fa-cloud-upload-alt" style="font-size:48px; color:#4ade80; margin-bottom:16px; display:block;"></i>
            <p style="color:#e2f0e8; font-weight:500;">Klik untuk upload CSV</p>
            <p style="color:#9aaebe; font-size:11px;">atau drag & drop file di sini</p>
            <input type="file" id="csvFileInput" accept=".csv,.txt,.tsv" style="display:none;" onchange="handleFileSelect(event)">
        </div>
        
        <!-- Selected File Info -->
        <div id="fileInfo" style="display:none; margin-top:16px; padding:12px; background:#0f1420; border-radius:12px; border:1px solid #2a3346;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <span style="color:#4ade80;"><i class="fas fa-file-csv"></i> </span>
                    <span id="fileName" style="color:#e2f0e8;"></span>
                    <span id="fileSize" style="color:#9aaebe; font-size:11px; margin-left:8px;"></span>
                </div>
                <button onclick="clearFile()" style="background:transparent; border:none; color:#ef4444; cursor:pointer;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div style="margin-top:12px;">
                <label style="color:#9aaebe; font-size:11px;">Delimiter:</label>
                <select id="delimiterSelect" style="background:#1e293b; border:1px solid #2a3346; color:#e2f0e8; padding:6px 12px; border-radius:8px; margin-left:8px;">
                    <option value="tab">Tab (\\t)</option>
                    <option value="comma">Comma (,)</option>
                    <option value="semicolon">Semicolon (;)</option>
                    <option value="auto">Auto-detect</option>
                </select>
            </div>
            
            <button id="previewBtn" onclick="previewCSV()" style="margin-top:12px; background:#8b5cf6; color:white; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; width:100%;">
                <i class="fas fa-eye"></i> Preview Data
            </button>
        </div>
    </div>
    
    <!-- Preview Container -->
    <div id="previewContainer" style="display:none; margin-top:24px;"></div>
</div>

<style>
    /* ... styles sama seperti dashboard ... */
</style>

<script>
let selectedFile = null;
const baseUrlMigrasi = '<?= base_url("migrasi") ?>';

function handleFileSelect(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    selectedFile = file;
    document.getElementById('fileInfo').style.display = 'block';
    document.getElementById('fileName').textContent = file.name;
    document.getElementById('fileSize').textContent = formatFileSize(file.size);
}

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
}

function clearFile() {
    selectedFile = null;
    document.getElementById('fileInfo').style.display = 'none';
    document.getElementById('csvFileInput').value = '';
    document.getElementById('previewContainer').style.display = 'none';
}

async function previewCSV() {
    if (!selectedFile) {
        alert('Pilih file terlebih dahulu');
        return;
    }
    
    const formData = new FormData();
    formData.append('csv_file', selectedFile);
    formData.append('delimiter', document.getElementById('delimiterSelect').value);
    
    const previewBtn = document.getElementById('previewBtn');
    previewBtn.disabled = true;
    previewBtn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Loading...';
    
    try {
        const response = await fetch(baseUrlMigrasi + '/preview_csv', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        const container = document.getElementById('previewContainer');
        container.style.display = 'block';
        
        if (!result.success) {
            container.innerHTML = `<div style="color:#ef4444; padding:20px;">${result.message}</div>`;
            return;
        }
        
        // Render preview table
        let html = `
            <div class="section-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <h3 style="color:#e2f0e8; margin:0;">📋 Preview Data (${result.total} creators)</h3>
                    <button onclick="confirmImport()" style="background:#4ade80; color:#0a0e17; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-weight:600;">
                        <i class="fas fa-save"></i> Import ${result.total} Creators
                    </button>
                </div>
                
                <div style="background:#0f1420; border-radius:12px; padding:12px; margin-bottom:16px;">
                    <p style="color:#9aaebe; font-size:11px;">
                        <i class="fas fa-info-circle"></i> 
                        Status akan otomatis menjadi <b style="color:#4ade80;">LINK_SENT</b> dan di-assign ke IS berdasarkan kolom <b>Created By</b>
                    </p>
                </div>
                
                <div style="max-height:500px; overflow-y:auto;">
                    <table style="width:100%; font-size:11px; border-collapse:collapse;">
                        <thead>
                            <tr style="border-bottom:2px solid #2a3346; position:sticky; top:0; background:#111827;">
                                <th style="padding:8px; text-align:left;">#</th>`;
        
        result.headers.forEach(h => {
            html += `<th style="padding:8px; text-align:left;">${escapeHtml(h)}</th>`;
            
        });
        
        html += `
        th style="padding:8px; text-align:left;">Phone</th>
         <th style="padding:8px; text-align:left;">Penerima</th>
         <th style="padding:8px; text-align:left;">Alamat</th>
        <th style="padding:8px; text-align:left;">IS Match</th>
                            </tr>
                        </thead>
                        <tbody>`;
        
        result.data.forEach((row, idx) => {
            const createdBy = row['created_by'] || row['created by'] || '';
            html += `<tr style="border-bottom:1px solid #2a3346;">
                <td style="padding:8px;">${idx + 1}</td>`;
            
            result.headers.forEach(h => {
                const key = h.toLowerCase().trim();
                html += `<td style="padding:8px;">${escapeHtml(row[key] || '')}</td>`;
            });
            
html += `<td style="padding:8px;">${escapeHtml(row['phone'] || row['nomor_hp'] || row['no_hp'] || '-')}</td>`;
html += `<td style="padding:8px;">${escapeHtml(row['penerima'] || row['nama_penerima'] || '-')}</td>`;
html += `<td style="padding:8px; font-size:10px;">${escapeHtml(row['alamat'] || row['shipping_address'] || '-')}</td>`;
            html += `<td style="padding:8px; color:#8b5cf6;">${escapeHtml(createdBy)}</td>`;
            html += `</tr>`;
        });
        
        html += `</tbody></table></div>
                
                <div style="margin-top:16px;">
                    <button onclick="confirmImport()" style="background:#4ade80; color:#0a0e17; border:none; padding:12px 24px; border-radius:8px; cursor:pointer; font-weight:600; width:100%;">
                        <i class="fas fa-save"></i> Konfirmasi Import (${result.total} Creators)
                    </button>
                </div>
            </div>`;
        
        container.innerHTML = html;
        
        // Store data for import
        window._importData = result.data;
        
    } catch (error) {
        document.getElementById('previewContainer').innerHTML = 
            `<div style="color:#ef4444; padding:20px;">Error: ${error.message}</div>`;
    } finally {
        previewBtn.disabled = false;
        previewBtn.innerHTML = '<i class="fas fa-eye"></i> Preview Data';
    }
}

async function confirmImport() {
    if (!window._importData || window._importData.length === 0) {
        alert('Tidak ada data untuk diimport');
        return;
    }
    
    if (!confirm(`Import ${window._importData.length} creators? Status akan jadi LINK_SENT.`)) {
        return;
    }
    
    const confirmBtn = document.querySelector('#previewContainer button');
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Importing...';
    }
    
    try {
        const response = await fetch(baseUrlMigrasi + '/process_import', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ data: JSON.stringify(window._importData) })
        });
        const result = await response.json();
        
        if (result.success) {
            let message = result.message;
            if (result.errors && result.errors.length > 0) {
                message += '\n\n⚠️ Errors:\n' + result.errors.join('\n');
            }
            alert(message);
            
            // Reset
            clearFile();
            document.getElementById('previewContainer').style.display = 'none';
        } else {
            alert(result.message || 'Import gagal');
        }
    } catch (error) {
        alert('Error: ' + error.message);
    } finally {
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="fas fa-save"></i> Konfirmasi Import';
        }
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>