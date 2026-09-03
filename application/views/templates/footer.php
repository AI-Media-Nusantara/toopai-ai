    </div> <!-- close container -->
    
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
                    <p class="powered">Powered by <span>Toopai.ai</span></p>
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
    
    <div id="globalToastContainer" style="position: fixed; bottom: 20px; right: 20px; left: 20px; z-index: 9999; pointer-events: none;"></div>
    <?php 
$current_role = $this->session->userdata('role');
$dashboard_url = '';
if ($current_role == 'IS') {
    $dashboard_url = base_url('is/dashboard');
} elseif ($current_role == 'BD') {
    $dashboard_url = base_url('bd/dashboard');
} elseif ($current_role == 'admin') {
    $dashboard_url = base_url('admin/dashboard');
}
?>

<?php if (!empty($dashboard_url) && $this->uri->segment(1) != $current_role): ?>
<div style="position: fixed; bottom: 20px; left: 20px; z-index: 999;">
    <a href="<?= $dashboard_url ?>" 
       style="display: flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #8b5cf6, #3b82f6); color: white; padding: 10px 20px; border-radius: 40px; text-decoration: none; font-size: 13px; font-weight: 600; box-shadow: 0 4px 12px rgba(0,0,0,0.3); transition: all 0.3s ease;"
       onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.4)';"
       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.3)';">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Dashboard</span>
    </a>
</div>
<?php endif; ?>

    <style>
        .global-toast {
            background: linear-gradient(135deg, var(--purple), var(--blue));
            color: white;
            padding: 12px 20px;
            border-radius: 60px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 10px;
            animation: slideUp 0.3s ease, fadeOut 0.3s ease 2.7s;
            box-shadow: var(--glow-purple);
            pointer-events: auto;
            max-width: 380px;
            margin-left: auto;
        }
        
        @keyframes slideUp {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes fadeOut {
            to { opacity: 0; visibility: hidden; }
        }
        
        @media (max-width: 768px) {
            .global-toast { max-width: 100%; text-align: center; margin-left: 0; }
        }
    </style>
    
    <script>
        function formatNumber(num) {
            if (num === undefined || num === null) return '0';
            return Number(num).toLocaleString('id-ID');
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
        
        function showToastGlobal(message, type = 'success') {
            const container = document.getElementById('globalToastContainer');
            if (!container) return;
            const toast = document.createElement('div');
            toast.className = 'global-toast';
            toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
            container.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
        
        let confettiCanvas = null;
        let confettiParticles = [];
        let confettiRunning = false;
        
        function initConfetti() {
            if (document.getElementById('confetti-canvas')) return;
            confettiCanvas = document.createElement('canvas');
            confettiCanvas.id = 'confetti-canvas';
            confettiCanvas.style.cssText = 'position:fixed; top:0; left:0; width:100%; height:100%; pointer-events:none; z-index:10000;';
            document.body.appendChild(confettiCanvas);
        }
        
        function startConfetti() {
            initConfetti();
            confettiCanvas = document.getElementById('confetti-canvas');
            const ctx = confettiCanvas.getContext('2d');
            confettiCanvas.width = window.innerWidth;
            confettiCanvas.height = window.innerHeight;
            confettiParticles = [];
            confettiRunning = true;
            
            const colors = ['#8b5cf6', '#3b82f6', '#06b6d4', '#10b981'];
            for (let i = 0; i < 150; i++) {
                confettiParticles.push({
                    x: confettiCanvas.width / 2,
                    y: confettiCanvas.height / 2,
                    vx: (Math.random() - 0.5) * 15,
                    vy: (Math.random() - 0.5) * 15 - 8,
                    size: Math.random() * 6 + 2,
                    color: colors[Math.floor(Math.random() * colors.length)],
                    gravity: 0.3,
                    life: 1,
                    decay: 0.02
                });
            }
            
            function animate() {
                if (!confettiRunning) {
                    ctx.clearRect(0, 0, confettiCanvas.width, confettiCanvas.height);
                    return;
                }
                ctx.clearRect(0, 0, confettiCanvas.width, confettiCanvas.height);
                let allDead = true;
                for (let p of confettiParticles) {
                    if (p.life <= 0) continue;
                    allDead = false;
                    p.x += p.vx;
                    p.y += p.vy;
                    p.vy += p.gravity;
                    p.life -= p.decay;
                    ctx.globalAlpha = p.life;
                    ctx.fillStyle = p.color;
                    ctx.fillRect(p.x, p.y, p.size, p.size);
                }
                if (allDead) confettiRunning = false;
                else requestAnimationFrame(animate);
            }
            animate();
            setTimeout(() => { confettiRunning = false; }, 3000);
        }
        
        window.addEventListener('resize', () => {
            if (confettiCanvas) {
                confettiCanvas.width = window.innerWidth;
                confettiCanvas.height = window.innerHeight;
            }
        });
    </script>
    
<script>
const baseUrlDashboard = '<?= base_url() ?>';
// ========== CHANGE PASSWORD ==========
function showChangePasswordModal() {
    document.getElementById('changePasswordModal').classList.add('show');
}

function closeChangePasswordModal() {
    document.getElementById('changePasswordModal').classList.remove('show');
    document.getElementById('currentPassword').value = '';
    document.getElementById('newPassword').value = '';
    document.getElementById('confirmPassword').value = '';
}

document.getElementById('savePasswordBtn')?.addEventListener('click', async () => {
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    if (!currentPassword || !newPassword || !confirmPassword) {
        showToastGlobal('Semua field harus diisi', 'error');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        showToastGlobal('Password baru tidak sama', 'error');
        return;
    }
    
    if (newPassword.length < 6) {
        showToastGlobal('Password minimal 6 karakter', 'error');
        return;
    }
    
    const response = await fetch(baseUrlDashboard + 'auth/change_password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            current_password: currentPassword,
            new_password: newPassword,
            confirm_password: confirmPassword
        })
    });
    
    const data = await response.json();
    
    if (data.success) {
        showToastGlobal('Password berhasil diubah!');
        closeChangePasswordModal();
    } else {
        showToastGlobal(data.message, 'error');
    }
});

// ========== ADD USER ==========
let currentUserRole = '<?= $this->session->userdata('role') ?>';

function showAddUserModal() {
    const roleLabel = document.getElementById('addUserRoleLabel');
    if (currentUserRole === 'BD') {
        roleLabel.innerText = '(BD)';
    } else if (currentUserRole === 'IS') {
        roleLabel.innerText = '(IS)';
    }
    document.getElementById('addUserModal').classList.add('show');
}

function closeAddUserModal() {
    document.getElementById('addUserModal').classList.remove('show');
    document.getElementById('newUsername').value = '';
    document.getElementById('newUserPassword').value = '';
    document.getElementById('newUserFullname').value = '';
    document.getElementById('newUserEmail').value = '';
}

document.getElementById('saveUserBtn')?.addEventListener('click', async () => {
    const username = document.getElementById('newUsername').value;
    const password = document.getElementById('newUserPassword').value;
    const fullname = document.getElementById('newUserFullname').value;
    const email = document.getElementById('newUserEmail').value;
    
    if (!username || !password) {
        showToastGlobal('Username dan password harus diisi', 'error');
        return;
    }
    
    if (password.length < 6) {
        showToastGlobal('Password minimal 6 karakter', 'error');
        return;
    }
    
    const response = await fetch(baseUrlDashboard + 'auth/add_user', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            username: username,
            password: password,
            full_name: fullname,
            email: email,
            role: currentUserRole
        })
    });
    
    const data = await response.json();
    
    if (data.success) {
        showToastGlobal(data.message);
        closeAddUserModal();
        setTimeout(() => location.reload(), 1500);
    } else {
        showToastGlobal(data.message, 'error');
    }
});

// ========== ACTIVITY LOG ==========
async function showActivityLogModal() {
    document.getElementById('activityLogModal').classList.add('show');
    document.getElementById('activityLogContent').innerHTML = '<div class="loading">Loading logs...</div>';
    
    const response = await fetch(baseUrlDashboard + 'auth/get_user_logs');
    const data = await response.json();
    
    if (data.success && data.logs.length > 0) {
        let html = '';
        data.logs.forEach(log => {
            html += `
                <div class="log-item">
                    <div class="log-time">${log.created_at}</div>
                    <div class="log-action">${log.action}</div>
                    <div class="log-description">${log.description || '-'}</div>
                    <div class="log-ip" style="font-size: 10px; color: var(--text-muted); margin-top: 4px;">IP: ${log.ip_address}</div>
                </div>
            `;
        });
        document.getElementById('activityLogContent').innerHTML = html;
    } else {
        document.getElementById('activityLogContent').innerHTML = '<div class="empty-state">Belum ada aktivitas</div>';
    }
}

function closeActivityLogModal() {
    document.getElementById('activityLogModal').classList.remove('show');
}



</script>

</body>
</html>