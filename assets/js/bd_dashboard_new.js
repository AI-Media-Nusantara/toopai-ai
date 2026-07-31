// file: assets/js/bd_dashboard_new.js
// Konfetti Effect untuk Task Completion
class Confetti {
    constructor() {
        this.canvas = document.createElement('canvas');
        this.canvas.id = 'confetti-canvas';
        document.body.appendChild(this.canvas);
        this.ctx = this.canvas.getContext('2d');
        this.particles = [];
        this.running = false;
        
        window.addEventListener('resize', () => this.resize());
        this.resize();
    }
    
    resize() {
        this.canvas.width = window.innerWidth;
        this.canvas.height = window.innerHeight;
    }
    
    start() {
        if (this.running) return;
        this.running = true;
        this.particles = [];
        
        const colors = ['#8b5cf6', '#3b82f6', '#06b6d4', '#10b981', '#f59e0b'];
        
        for (let i = 0; i < 150; i++) {
            this.particles.push({
                x: this.canvas.width / 2,
                y: this.canvas.height / 2,
                vx: (Math.random() - 0.5) * 15,
                vy: (Math.random() - 0.5) * 15 - 8,
                size: Math.random() * 6 + 2,
                color: colors[Math.floor(Math.random() * colors.length)],
                gravity: 0.3,
                life: 1,
                decay: 0.02
            });
        }
        
        this.animate();
        
        setTimeout(() => {
            this.running = false;
        }, 3000);
    }
    
    animate() {
        if (!this.running) {
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            return;
        }
        
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        
        let allDead = true;
        
        for (let i = 0; i < this.particles.length; i++) {
            const p = this.particles[i];
            if (p.life <= 0) continue;
            
            allDead = false;
            p.x += p.vx;
            p.y += p.vy;
            p.vy += p.gravity;
            p.life -= p.decay;
            
            this.ctx.globalAlpha = p.life;
            this.ctx.fillStyle = p.color;
            this.ctx.fillRect(p.x, p.y, p.size, p.size);
        }
        
        if (allDead) {
            this.running = false;
        } else {
            requestAnimationFrame(() => this.animate());
        }
    }
}

// Toast Notification
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Loading Skeleton
function showSkeleton(containerId, count = 3) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    const skeleton = document.createElement('div');
    skeleton.className = 'skeleton-loading';
    skeleton.innerHTML = Array(count).fill(0).map(() => `
        <div class="skeleton-item">
            <div class="skeleton skeleton-title"></div>
            <div class="skeleton skeleton-text" style="width: 60%"></div>
            <div class="skeleton skeleton-text" style="width: 40%"></div>
        </div>
    `).join('');
    
    container.innerHTML = '';
    container.appendChild(skeleton);
}

// Update Progress Bar untuk Brand
function updateBrandProgress(brandId, stage) {
    const progress = stage * 25; // 4 stages = 100%
    const progressBar = document.querySelector(`.brand-item[data-brand-id="${brandId}"] .progress-fill`);
    if (progressBar) {
        progressBar.style.width = `${progress}%`;
    }
}

// Complete Stage dengan Konfetti
const confetti = new Confetti();

async function completeStage(stageNum, brandId = null) {
    if (completedStages.includes(stageNum)) return;
    
    // Tampilkan konfetti
    confetti.start();
    
    // Kirim ke server
    await fetch(baseUrl + 'bd/complete_task', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ stage: stageNum, brand_id: brandId })
    });
    
    completedStages.push(stageNum);
    saveCompletedStages();
    updateStageUI();
    
    // Show different messages per stage
    const messages = {
        1: '🎯 Hunting selesai! Saatnya negosiasi!',
        2: '🤝 Deal closed! Menuju setup campaign!',
        3: '🚀 Campaign siap! Waktunya monitor!',
        4: '🏆 Selamat! Semua stage selesai! Amazing work!'
    };
    
    showToast(messages[stageNum] || `✅ Stage ${stageNum} selesai!`);
    
    // Auto scroll ke next stage
    const nextStage = stageNum + 1;
    if (nextStage <= 4) {
        setTimeout(() => {
            const nextCard = document.querySelector(`.stage-card[data-stage='${nextStage}']`);
            if (nextCard) {
                nextCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, 500);
    }
}

// XP Level System
let userXP = parseInt(localStorage.getItem('bd_user_xp')) || 0;
let userLevel = parseInt(localStorage.getItem('bd_user_level')) || 1;

function addXP(amount) {
    userXP += amount;
    const xpNeeded = userLevel * 500;
    
    if (userXP >= xpNeeded) {
        userLevel++;
        userXP -= xpNeeded;
        showToast(`🎉 LEVEL UP! Anda sekarang Level ${userLevel} BD!`, 'success');
        confetti.start();
    }
    
    localStorage.setItem('bd_user_xp', userXP);
    localStorage.setItem('bd_user_level', userLevel);
    updateXPDisplay();
}

function updateXPDisplay() {
    const xpNeeded = userLevel * 500;
    const xpPercent = (userXP / xpNeeded) * 100;
    const xpBar = document.getElementById('xp-bar-fill');
    const xpText = document.getElementById('xp-text');
    
    if (xpBar) {
        xpBar.style.width = `${xpPercent}%`;
    }
    if (xpText) {
        xpText.innerText = `Level ${userLevel} BD · ${userXP}/${xpNeeded} XP`;
    }
}

// Tambahkan XP ketika complete stage
const originalCompleteStage = completeStage;
window.completeStage = async function(stageNum, brandId = null) {
    await originalCompleteStage(stageNum, brandId);
    addXP(100); // +100 XP per stage
    updateXPDisplay();
};

// Mobile Bottom Navigation Handler
function initMobileNav() {
    const navItems = document.querySelectorAll('.mobile-nav-item');
    const tabs = document.querySelectorAll('.tab-btn');
    
    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const targetTab = item.getAttribute('data-tab');
            
            // Update active state
            navItems.forEach(nav => nav.classList.remove('active'));
            item.classList.add('active');
            
            // Trigger tab click
            const tabBtn = document.querySelector(`.tab-btn[data-tab="${targetTab}"]`);
            if (tabBtn) {
                tabBtn.click();
            }
        });
    });
    
    // Sync mobile nav with tab changes
    const observer = new MutationObserver(() => {
        const activeTab = document.querySelector('.tab-content.active').id;
        navItems.forEach(nav => {
            if (nav.getAttribute('data-tab') === activeTab) {
                nav.classList.add('active');
            } else {
                nav.classList.remove('active');
            }
        });
    });
    
    observer.observe(document.querySelector('.tab-content.active')?.parentElement || document.body, {
        attributes: true,
        attributeFilter: ['class']
    });
}

// Smooth Scroll for Mobile
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    initMobileNav();
    initSmoothScroll();
    updateXPDisplay();
    
    // Add XP bar to header if not exists
    if (!document.getElementById('xp-bar')) {
        const header = document.querySelector('.header');
        if (header) {
            const xpHTML = `
                <div class="xp-container" style="margin-top: 12px; background: var(--bg-card); border-radius: 60px; padding: 6px 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <span id="xp-text" style="font-size: 10px; color: var(--text-secondary);">Level ${userLevel} BD · ${userXP}/${userLevel * 500} XP</span>
                        <i class="fas fa-star" style="color: var(--purple); font-size: 12px;"></i>
                    </div>
                    <div class="progress-bar" style="height: 4px;">
                        <div id="xp-bar-fill" class="progress-fill" style="width: ${(userXP / (userLevel * 500)) * 100}%"></div>
                    </div>
                </div>
            `;
            header.insertAdjacentHTML('afterend', xpHTML);
        }
    }
});