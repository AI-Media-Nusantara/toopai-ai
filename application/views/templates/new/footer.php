</div>
    
    <style>
    /* TOOPAI MODERN FOOTER */
    .toopai-modern-footer {
        background-color: var(--bg-primary, #0a0a0f);
        color: var(--text-secondary, #94a3b8);
        padding: 40px 20px 20px;
        font-family: 'Inter', sans-serif;
        position: relative;
        overflow: hidden;
        margin-top: 50px;
        border-top: 1px solid rgba(255,255,255,0.03);
    }

    /* Subtle background curve */
    .toopai-modern-footer::before {
        content: '';
        position: absolute;
        top: -150px;
        left: 0;
        width: 100%;
        height: 300px;
        background: radial-gradient(ellipse at top, rgba(139, 92, 246, 0.05) 0%, transparent 70%);
        pointer-events: none;
    }

    .toopai-footer-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto 40px;
        flex-wrap: wrap;
        gap: 20px;
    }

    .footer-feature {
        display: flex;
        align-items: center;
        gap: 16px;
        flex: 1;
        min-width: 250px;
    }

    .footer-feature-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        background: rgba(139, 92, 246, 0.1);
        border: 1px solid rgba(139, 92, 246, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--purple, #8b5cf6);
        font-size: 20px;
        box-shadow: 0 0 15px rgba(139, 92, 246, 0.15);
    }

    .footer-feature-text h4 {
        color: #ffffff;
        font-size: 14px;
        font-weight: 600;
        margin: 0 0 4px 0;
        letter-spacing: 0.3px;
    }

    .footer-feature-text p {
        font-size: 12px;
        margin: 0;
        line-height: 1.5;
        color: rgba(255,255,255,0.5);
    }

    .footer-center-wrapper {
        flex: 1;
        min-width: 250px;
        display: flex;
        justify-content: center;
        border-left: 1px solid rgba(255,255,255,0.05);
        border-right: 1px solid rgba(255,255,255,0.05);
    }

    .footer-center-content {
        text-align: center;
    }

    .footer-center-content p.powered {
        font-size: 14px;
        color: #ffffff;
        margin: 0 0 6px 0;
        font-weight: 500;
    }

    .footer-center-content p.powered span {
        color: var(--purple, #8b5cf6);
        font-weight: 700;
    }

    .footer-center-content p.copyright {
        font-size: 11px;
        color: rgba(255,255,255,0.4);
        margin: 0;
    }

    .toopai-footer-bottom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto;
        padding-top: 25px;
        border-top: 1px solid rgba(255,255,255,0.06);
        flex-wrap: wrap;
        gap: 15px;
    }


    .footer-made-with {
        font-size: 12px;
        color: rgba(255,255,255,0.5);
    }
    
    .footer-made-with i {
        color: #ef4444;
        margin: 0 3px;
    }

    .footer-back-top {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 13px;
        color: #ffffff;
        cursor: pointer;
        font-weight: 500;
        transition: opacity 0.2s;
    }

    .footer-back-top:hover {
        opacity: 0.8;
    }

    .footer-back-top-btn {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(255,255,255,0.05);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: 0.3s;
        border: 1px solid rgba(255,255,255,0.1);
    }

    .footer-back-top:hover .footer-back-top-btn {
        background: rgba(255,255,255,0.1);
    }

    @media (max-width: 992px) {
        .footer-center-wrapper {
            border-left: none;
            border-right: none;
            order: -1;
            width: 100%;
            margin-bottom: 20px;
        }
        .toopai-footer-top {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .footer-feature {
            flex-direction: column;
            justify-content: center !important;
            text-align: center !important;
        }
        .toopai-footer-bottom {
            flex-direction: column;
            justify-content: center;
            text-align: center;
            gap: 20px;
        }
    }
    </style>

    <footer class="toopai-modern-footer">
        <div class="toopai-footer-top">
            
            <!-- Left Feature -->
            <div class="footer-feature">
                <div class="footer-feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="footer-feature-text">
                    <h4>Keamanan Terjamin</h4>
                    <p>Data Anda aman dengan enkripsi<br>berstandar enterprise-grade</p>
                </div>
            </div>
            
            <!-- Center Branding -->
            <div class="footer-center-wrapper">
                <div class="footer-center-content">
                    <p class="powered">Powered by <span>Toopai.ai</span> <span style="font-size:10px; background:rgba(139,92,246,0.2); color:#a78bfa; border:1px solid rgba(139,92,246,0.35); padding:1px 7px; border-radius:10px; font-weight:700; margin-left:4px; display:inline-flex; align-items:center;">v2.0</span></p>
                    <p class="copyright">&copy; 2026 Toopai.ai. All rights reserved.</p>
                </div>
            </div>
            
            <!-- Right Feature -->
            <div class="footer-feature" style="justify-content: flex-end; text-align: left;">
                <div class="footer-feature-icon">
                    <i class="fas fa-check-shield"></i>
                </div>
                <div class="footer-feature-text">
                    <h4>Enterprise Grade</h4>
                    <p>Sistem handal untuk skalabilitas<br>bisnis Anda</p>
                </div>
            </div>

        </div>

        <div class="toopai-footer-bottom">

            <!-- Center Side: Made with love -->
            <div class="footer-made-with">
                Dibuat dengan <i class="fas fa-heart"></i> untuk membantu bisnis Anda berkembang
            </div>
            
            <!-- Right Side: Back to top -->
            <div class="footer-back-top" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
                Kembali ke atas
                <div class="footer-back-top-btn">
                    <i class="fas fa-arrow-up"></i>
                </div>
            </div>
        </div>
    </footer>
<div id="globalToastContainer"></div>
<style>
#globalToastContainer{position:fixed;right:24px;bottom:24px;z-index:99999;pointer-events:none}.global-toast{min-width:320px;margin-top:12px;padding:15px 18px;border-radius:18px;background:linear-gradient(135deg,#7c3cff,#10dff0);color:#fff;font-size:13px;font-weight:800;box-shadow:0 0 28px rgba(124,60,255,.28);animation:toastIn .25s ease;pointer-events:auto}@keyframes toastIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}@media(max-width:768px){#globalToastContainer{left:14px;right:14px;bottom:14px}.global-toast{min-width:auto;width:100%}}
</style>
<script>
const baseUrlDashboard='<?= base_url() ?>';
function formatNumber(num){if(num===undefined||num===null)return'0';const n=parseFloat(num);return isNaN(n)?'0':n.toLocaleString('id-ID',{minimumFractionDigits:0,maximumFractionDigits:0});}
function escapeHtml(str){if(!str)return'';return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');}
function showToastGlobal(message,type='success'){const c=document.getElementById('globalToastContainer');if(!c)return;const t=document.createElement('div');t.className='global-toast';t.innerHTML=`<i class="fas fa-${type==='success'?'check-circle':'exclamation-circle'}"></i> ${message}`;c.appendChild(t);setTimeout(()=>t.remove(),3000);}
function showChangePasswordModal(){document.getElementById('changePasswordModal')?.classList.add('show');}
function closeChangePasswordModal(){document.getElementById('changePasswordModal')?.classList.remove('show');['currentPassword','newPassword','confirmPassword'].forEach(id=>{const el=document.getElementById(id);if(el)el.value='';});}
let currentUserRole='<?= $this->session->userdata('role') ?>';
function showAddUserModal(){const l=document.getElementById('addUserRoleLabel');if(l)l.innerText=currentUserRole?'('+currentUserRole+')':'';document.getElementById('addUserModal')?.classList.add('show');}
function closeAddUserModal(){document.getElementById('addUserModal')?.classList.remove('show');['newUsername','newUserPassword','newUserFullname','newUserEmail'].forEach(id=>{const el=document.getElementById(id);if(el)el.value='';});}
async function showActivityLogModal(){const modal=document.getElementById('activityLogModal'),content=document.getElementById('activityLogContent');modal?.classList.add('show');if(!content)return;content.innerHTML='<div class="loading">Loading logs...</div>';try{const r=await fetch(baseUrlDashboard+'auth/get_user_logs');const d=await r.json();if(d.success&&d.logs&&d.logs.length){content.innerHTML=d.logs.map(log=>`<div class="log-item"><div class="log-time">${escapeHtml(log.created_at)}</div><div class="log-action">${escapeHtml(log.action)}</div><div class="log-description">${escapeHtml(log.description||'-')}</div><div class="log-time">IP: ${escapeHtml(log.ip_address||'-')}</div></div>`).join('');}else content.innerHTML='<div class="empty-state">Belum ada aktivitas</div>';}catch(e){content.innerHTML='<div class="empty-state">Gagal memuat aktivitas</div>';}}
function closeActivityLogModal(){document.getElementById('activityLogModal')?.classList.remove('show');}
document.getElementById('savePasswordBtn')?.addEventListener('click',async()=>{const current_password=document.getElementById('currentPassword').value,new_password=document.getElementById('newPassword').value,confirm_password=document.getElementById('confirmPassword').value;if(!current_password||!new_password||!confirm_password)return showToastGlobal('Semua field harus diisi','error');if(new_password!==confirm_password)return showToastGlobal('Password baru tidak sama','error');if(new_password.length<6)return showToastGlobal('Password minimal 6 karakter','error');try{const r=await fetch(baseUrlDashboard+'auth/change_password',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({current_password,new_password,confirm_password})});const d=await r.json();if(d.success){showToastGlobal('Password berhasil diubah');closeChangePasswordModal();}else showToastGlobal(d.message||'Gagal mengubah password','error');}catch(e){showToastGlobal('Gagal mengubah password','error');}});
document.getElementById('saveUserBtn')?.addEventListener('click',async()=>{const username=document.getElementById('newUsername').value,password=document.getElementById('newUserPassword').value,full_name=document.getElementById('newUserFullname').value,email=document.getElementById('newUserEmail').value;if(!username||!password)return showToastGlobal('Username dan password harus diisi','error');if(password.length<6)return showToastGlobal('Password minimal 6 karakter','error');try{const r=await fetch(baseUrlDashboard+'auth/add_user',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({username,password,full_name,email,role:currentUserRole})});const d=await r.json();if(d.success){showToastGlobal(d.message||'User berhasil dibuat');closeAddUserModal();setTimeout(()=>location.reload(),1200);}else showToastGlobal(d.message||'Gagal membuat user','error');}catch(e){showToastGlobal('Gagal membuat user','error');}});
window.addEventListener('click',e=>{['changePasswordModal','addUserModal','activityLogModal','campaignModal'].forEach(id=>{const m=document.getElementById(id);if(e.target===m)m.classList.remove('show');});});
</script>
</body>
</html>
