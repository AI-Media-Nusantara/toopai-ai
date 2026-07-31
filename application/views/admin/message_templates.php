<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Templates - Toopai Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(145deg, #020711 0%, #040a17 42%, #061021 100%);
            color: #f7fbff;
            min-height: 100vh;
        }
        .container { max-width: 1400px; margin: 0 auto; padding: 24px; }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(112,136,185,.16);
        }
        .header h1 {
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(135deg, #7c3cff, #10dff0);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .btn-primary {
            background: linear-gradient(135deg, #7c3cff, #3b82f6);
            border: none;
            padding: 10px 20px;
            border-radius: 40px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 0 15px rgba(124,60,255,.3); }
        .btn-secondary {
            background: #1e293b;
            border: 1px solid #2a3346;
            padding: 8px 16px;
            border-radius: 40px;
            color: #cbd5e6;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn-secondary:hover { background: #2a2f3e; }
        .btn-danger {
            background: rgba(239,68,68,0.15);
            border: 1px solid #ef4444;
            padding: 8px 16px;
            border-radius: 40px;
            color: #ef4444;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn-danger:hover { background: rgba(239,68,68,0.3); }
        .templates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 20px;
        }
        .template-card {
            background: linear-gradient(160deg, rgba(13,23,46,.90), rgba(6,12,25,.92));
            border: 1px solid rgba(112,136,185,.18);
            border-radius: 20px;
            overflow: hidden;
            transition: 0.2s;
        }
        .template-card:hover { border-color: #7c3cff; transform: translateY(-2px); }
        .template-header {
            background: rgba(124,60,255,.1);
            padding: 16px;
            border-bottom: 1px solid rgba(112,136,185,.18);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
        }
        .badge-bd { background: rgba(139,92,246,0.15); color: #8b5cf6; }
        .badge-is { background: rgba(16,185,129,0.15); color: #10b981; }
        .template-body { padding: 16px; }
        .banner-preview {
            background: #0f1420;
            border-radius: 12px;
            padding: 10px;
            margin-bottom: 12px;
            display: flex;
            gap: 12px;
            align-items: center;
        }
        .banner-preview img {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            object-fit: cover;
        }
        .template-message {
            background: #0f1420;
            border-radius: 12px;
            padding: 12px;
            font-size: 11px;
            color: #9aaebe;
            white-space: pre-wrap;
            margin-top: 12px;
            max-height: 150px;
            overflow-y: auto;
        }
        .template-footer {
            padding: 12px 16px;
            border-top: 1px solid rgba(112,136,185,.18);
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.95);
            backdrop-filter: blur(8px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal.active { display: flex; }
        .modal-content {
            background: #111827;
            border-radius: 24px;
            width: 90%;
            max-width: 650px;
            max-height: 85vh;
            overflow-y: auto;
            padding: 24px;
            border: 1px solid #4ade80;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #2a3346;
        }
        .modal-header h3 { font-size: 18px; }
        .close { font-size: 28px; cursor: pointer; color: #9aaebe; }
        .close:hover { color: #ef4444; }
        .form-group { margin-bottom: 16px; }
        .form-group label {
            display: block;
            color: #b7c1d6;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            background: #0f1420;
            border: 1px solid #2a3346;
            border-radius: 12px;
            color: #e2f0e8;
            font-size: 13px;
        }
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #4ade80;
        }
        .banner-upload-preview {
            background: #0f1420;
            border-radius: 12px;
            padding: 12px;
            margin-top: 8px;
            display: flex;
            gap: 12px;
            align-items: center;
        }
        .banner-upload-preview img {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            object-fit: cover;
        }
        .flex-buttons { display: flex; gap: 12px; margin-top: 20px; }
        .flex-buttons button { flex: 1; }
        .empty-state {
            text-align: center;
            padding: 60px;
            color: #9aaebe;
        }
        .loading {
            text-align: center;
            padding: 40px;
        }
        @media (max-width: 768px) {
            .templates-grid { grid-template-columns: 1fr; }
            .container { padding: 16px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-envelope-open-text"></i> Message Templates</h1>
            <button class="btn-primary" id="addTemplateBtn"><i class="fas fa-plus"></i> Tambah Template</button>
        </div>
        
        <div id="templatesContainer" class="templates-grid">
            <div class="loading">
                <i class="fas fa-spinner fa-pulse fa-2x"></i>
                <p style="margin-top: 12px;">Loading templates...</p>
            </div>
        </div>
    </div>

    <!-- Modal Add/Edit Template -->
    <div id="templateModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle"><i class="fas fa-edit"></i> Template Baru</h3>
                <span class="close" id="closeModalBtn">&times;</span>
            </div>
            <div class="modal-body">
                <form id="templateForm" enctype="multipart/form-data">
                    <input type="hidden" id="templateId" name="id">
                    <div class="form-group">
                        <label>Type <span style="color:#ef4444;">*</span></label>
                        <select id="templateType" name="type">
                            <option value="bd">BD (Business Development)</option>
                            <option value="is">IS (Influencer Specialist)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Task <span style="color:#ef4444;">*</span></label>
                        <select id="templateTask" name="task">
                            <option value="1">Task 1 - Hunting</option>
                            <option value="2">Task 2 - Follow Up</option>
                            <option value="3">Task 3 - Setup Campaign</option>
                            <option value="4">Task 4 - Monitoring</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Stage (Opsional)</label>
                        <input type="text" id="templateStage" name="stage" placeholder="hunting / followup / dll">
                    </div>
                    <div class="form-group">
                        <label>Judul Template <span style="color:#ef4444;">*</span></label>
                        <input type="text" id="templateTitle" name="title" placeholder="Contoh: Template WA Hunting Brand">
                    </div>
                   <div class="form-group">
    <label>Banner Image (Upload)</label>
    <input type="file" id="templateBannerFile" name="banner_file" accept="image/jpeg,image/png,image/gif,image/webp">
    <div id="bannerPreview" class="banner-upload-preview" style="display:none;"></div>
    <div style="margin-top: 8px;">
        <small style="color: #9aaebe; font-size: 10px;">
            <i class="fas fa-info-circle"></i> 
            Format yang didukung: <strong>JPG, JPEG, PNG, GIF, WEBP</strong>. Maksimal 5MB.
        </small>
    </div>
</div>
                    <div class="form-group">
                        <label>Banner Title</label>
                        <input type="text" id="templateBannerTitle" name="banner_title" placeholder="Judul banner">
                    </div>
                    <div class="form-group">
                        <label>Banner Description</label>
                        <textarea id="templateBannerDesc" name="banner_description" rows="2" placeholder="Deskripsi banner"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Pesan WhatsApp <span style="color:#ef4444;">*</span></label>
                        <textarea id="templateMessage" name="message_text" rows="8" placeholder="Tulis pesan... Gunakan {brand_name}, {commission}, {creator_name} untuk variabel dinamis"></textarea>
                        <div style="font-size: 10px; color: #9aaebe; margin-top: 4px;">
                            <i class="fas fa-info-circle"></i> Variabel: <code>{brand_name}</code> - <code>{commission}</code> - <code>{creator_name}</code>
                        </div>
                    </div>
                    <div class="flex-buttons">
                        <button type="button" id="saveTemplateBtn" class="btn-primary"><i class="fas fa-save"></i> Simpan</button>
                        <button type="button" id="cancelModalBtn" class="btn-secondary">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const baseUrl = '<?= base_url() ?>';
        let currentEditId = null;

        async function loadTemplates() {
            const container = document.getElementById('templatesContainer');
            container.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-pulse fa-2x"></i><p>Loading...</p></div>';
            
            try {
                const response = await fetch(baseUrl + 'message_template/get_all');
                const result = await response.json();
                
                if (result.success && result.data.length > 0) {
                    container.innerHTML = '';
                    result.data.forEach(template => {
                        const typeBadge = template.type === 'bd' ? 'badge-bd' : 'badge-is';
                        const typeLabel = template.type === 'bd' ? 'BD' : 'IS';
                        
                        const bannerHtml = template.banner_url ? `
                            <div class="banner-preview">
                                <img src="${escapeHtml(template.banner_url)}" onerror="this.style.display='none'">
                                <div>
                                    <div style="font-size: 11px; color: #e2f0e8;">${escapeHtml(template.banner_title || 'Banner')}</div>
                                    <div style="font-size: 9px; color: #9aaebe;">${escapeHtml(template.banner_description || '')}</div>
                                </div>
                            </div>
                        ` : '';
                        
                        const card = document.createElement('div');
                        card.className = 'template-card';
                        card.innerHTML = `
                            <div class="template-header">
                                <div>
                                    <span class="badge ${typeBadge}">${typeLabel}</span>
                                    <span class="badge" style="background:rgba(59,130,246,0.15); color:#3b82f6;">Task ${template.task}</span>
                                    ${template.stage ? `<span class="badge" style="background:rgba(16,185,129,0.15); color:#10b981;">${escapeHtml(template.stage)}</span>` : ''}
                                </div>
                                <div>
                                    ${template.is_active ? '<i class="fas fa-check-circle" style="color:#4ade80;"></i>' : '<i class="fas fa-ban" style="color:#ef4444;"></i>'}
                                </div>
                            </div>
                            <div class="template-body">
                                <strong style="color:#e2f0e8;">${escapeHtml(template.title)}</strong>
                                ${bannerHtml}
                                <div class="template-message">${escapeHtml(template.message_text.substring(0, 200))}${template.message_text.length > 200 ? '...' : ''}</div>
                            </div>
                            <div class="template-footer">
                                <button class="btn-secondary edit-template" data-id="${template.id}" style="padding: 6px 12px; font-size: 11px;">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn-danger delete-template" data-id="${template.id}" style="padding: 6px 12px; font-size: 11px;">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </div>
                        `;
                        container.appendChild(card);
                    });
                    
                    // Attach event listeners
                    document.querySelectorAll('.edit-template').forEach(btn => {
                        btn.addEventListener('click', (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            const id = btn.getAttribute('data-id');
                            editTemplate(id);
                        });
                    });
                    document.querySelectorAll('.delete-template').forEach(btn => {
                        btn.addEventListener('click', (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            const id = btn.getAttribute('data-id');
                            deleteTemplate(id);
                        });
                    });
                } else {
                    container.innerHTML = '<div class="empty-state"><i class="fas fa-envelope-open-text" style="font-size: 48px; margin-bottom: 16px; display: block; opacity: 0.5;"></i><p>Belum ada template. Klik "Tambah Template" untuk membuat.</p></div>';
                }
            } catch (error) {
                console.error('Error:', error);
                container.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 16px; display: block; color: #ef4444;"></i><p>Error loading templates</p></div>';
            }
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }

        async function editTemplate(id) {
            try {
                const response = await fetch(baseUrl + `message_template/get?id=${id}`);
                const result = await response.json();
                if (result.success && result.data) {
                    const template = result.data;
                    document.getElementById('templateId').value = template.id;
                    document.getElementById('templateType').value = template.type;
                    document.getElementById('templateTask').value = template.task;
                    document.getElementById('templateStage').value = template.stage || '';
                    document.getElementById('templateTitle').value = template.title;
                    document.getElementById('templateBannerTitle').value = template.banner_title || '';
                    document.getElementById('templateBannerDesc').value = template.banner_description || '';
                    document.getElementById('templateMessage').value = template.message_text;
                    
                    // Show existing banner preview
                    if (template.banner_file) {
                        const bannerPreview = document.getElementById('bannerPreview');
                        bannerPreview.innerHTML = `
                            <img src="${baseUrl + template.banner_file}" style="width:60px; height:60px; border-radius:12px; object-fit:cover;">
                            <div>
                                <div style="font-size:11px; color:#e2f0e8;">Banner saat ini</div>
                                <button type="button" id="removeBannerBtn" style="background:#ef4444; border:none; padding:2px 8px; border-radius:12px; color:white; font-size:10px; cursor:pointer; margin-top:4px;">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </div>
                        `;
                        bannerPreview.style.display = 'flex';
                        
                        document.getElementById('removeBannerBtn')?.addEventListener('click', async () => {
                            if (confirm('Hapus banner ini?')) {
                                await removeBanner(template.id);
                            }
                        });
                    } else {
                        document.getElementById('bannerPreview').style.display = 'none';
                    }
                    
                    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Edit Template';
                    document.getElementById('templateModal').classList.add('active');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Gagal memuat template');
            }
        }

        async function removeBanner(id) {
            try {
                const response = await fetch(baseUrl + 'message_template/remove_banner', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ id: id })
                });
                const result = await response.json();
                if (result.success) {
                    document.getElementById('bannerPreview').style.display = 'none';
                    alert('Banner berhasil dihapus');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        async function deleteTemplate(id) {
            if (!confirm('Yakin ingin menghapus template ini? Tindakan ini tidak dapat dibatalkan.')) return;
            
            try {
                const response = await fetch(baseUrl + 'message_template/delete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ id: id })
                });
                const result = await response.json();
                if (result.success) {
                    alert('Template berhasil dihapus');
                    loadTemplates();
                } else {
                    alert(result.message || 'Gagal menghapus template');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Gagal menghapus template');
            }
        }

        document.getElementById('addTemplateBtn')?.addEventListener('click', () => {
            document.getElementById('templateForm').reset();
            document.getElementById('templateId').value = '';
            document.getElementById('bannerPreview').style.display = 'none';
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus"></i> Tambah Template Baru';
            document.getElementById('templateModal').classList.add('active');
        });

        document.getElementById('closeModalBtn')?.addEventListener('click', () => {
            document.getElementById('templateModal').classList.remove('active');
        });

        document.getElementById('cancelModalBtn')?.addEventListener('click', () => {
            document.getElementById('templateModal').classList.remove('active');
        });

        document.getElementById('saveTemplateBtn')?.addEventListener('click', async () => {
            const form = document.getElementById('templateForm');
            const formData = new FormData(form);
            
            const saveBtn = document.getElementById('saveTemplateBtn');
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Menyimpan...';
            
            try {
                const response = await fetch(baseUrl + 'message_template/save', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    alert('Template berhasil disimpan');
                    document.getElementById('templateModal').classList.remove('active');
                    loadTemplates();
                } else {
                    alert(result.message || 'Gagal menyimpan template');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Gagal menyimpan template: ' + error.message);
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save"></i> Simpan';
            }
        });

        // Preview banner when file selected
        document.getElementById('templateBannerFile')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const bannerPreview = document.getElementById('bannerPreview');
                    bannerPreview.innerHTML = `
                        <img src="${event.target.result}" style="width:60px; height:60px; border-radius:12px; object-fit:cover;">
                        <div style="font-size:11px; color:#4ade80;">Banner baru akan diupload</div>
                    `;
                    bannerPreview.style.display = 'flex';
                };
                reader.readAsDataURL(file);
            }
        });

        // Load templates on page load
        loadTemplates();
        
        // Modal click outside to close
        window.onclick = function(event) {
            const modal = document.getElementById('templateModal');
            if (event.target === modal) {
                modal.classList.remove('active');
            }
        }
    </script>
</body>
</html>