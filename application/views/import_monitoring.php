<!DOCTYPE html>
<html>
<head>
    <title>Import Creator to Monitoring</title>
    <style>
        body { background: #0f0f1a; color: #e2f0e8; font-family: Arial, sans-serif; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: #1a1f2e; padding: 30px; border-radius: 16px; border: 1px solid #2a3346; }
        h1 { color: #4ade80; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #9aaebe; }
        input[type="file"] { background: #0f1420; padding: 12px; border: 1px solid #2a3346; border-radius: 8px; color: #e2f0e8; width: 100%; }
        button { background: #4ade80; color: #0a0e17; padding: 12px 30px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 16px; }
        button:hover { background: #22c55e; }
        .result { margin-top: 20px; padding: 16px; border-radius: 8px; display: none; }
        .result.success { background: rgba(74,222,128,0.15); border: 1px solid #4ade80; display: block; }
        .result.error { background: rgba(239,68,68,0.15); border: 1px solid #ef4444; display: block; }
        .note { background: rgba(245,158,11,0.1); border-left: 3px solid #f59e0b; padding: 12px 16px; margin-bottom: 20px; border-radius: 4px; font-size: 13px; color: #fbbf24; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 12px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #2a3346; }
        th { color: #8b5cf6; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📥 Import Creator ke Monitoring (Task 3)</h1>
        
        <div class="note">
            ⚠️ <strong>Catatan:</strong> Creator yang di-import akan langsung masuk ke <strong>Task 3 (MONITORING)</strong> dengan status <strong>ACTIVE</strong>.
        </div>
        
        <form id="importForm" enctype="multipart/form-data">
            <div class="form-group">
                <label>📁 Pilih File CSV</label>
                <input type="file" name="csv_file" accept=".csv" required>
                <p style="font-size: 11px; color: #6b7280; margin-top: 4px;">
                    Format: CSV dengan kolom: Date, Username, Whatsapp, id_is, is, Brand
                </p>
            </div>
            
            <button type="submit">🚀 Import ke Monitoring</button>
        </form>
        
        <div id="result" class="result"></div>
        
        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #2a3346;">
            <h3 style="color: #9aaebe; font-size: 14px;">📋 Contoh Format CSV</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Username</th>
                        <th>Whatsapp</th>
                        <th>id_is</th>
                        <th>is</th>
                        <th>Brand</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>2026-07-08</td>
                        <td>dewi.susanti324</td>
                        <td>85211130338</td>
                        <td>57</td>
                        <td>aziz</td>
                        <td>Hanasui</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        document.getElementById('importForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const resultDiv = document.getElementById('result');
            resultDiv.className = 'result';
            resultDiv.innerHTML = '<p>⏳ Mengimport data...</p>';
            resultDiv.style.display = 'block';
            
            try {
                const response = await fetch('/import_monitoring/process', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    resultDiv.className = 'result success';
                    resultDiv.innerHTML = `
                        <h3>✅ Import Berhasil!</h3>
                        <p>${result.message}</p>
                        <ul>
                            <li>✅ Insert/Update: ${result.inserted} creator</li>
                            <li>⏭️ Skipped: ${result.skipped}</li>
                            <li>❌ Errors: ${result.errors}</li>
                        </ul>
                    `;
                } else {
                    resultDiv.className = 'result error';
                    resultDiv.innerHTML = `<h3>❌ Import Gagal</h3><p>${result.message}</p>`;
                }
            } catch (error) {
                resultDiv.className = 'result error';
                resultDiv.innerHTML = `<h3>❌ Error</h3><p>${error.message}</p>`;
            }
        });
    </script>
</body>
</html>