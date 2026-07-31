<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>Authorize TikTok - Toopai</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-font-smoothing: antialiased;
        }
        
        body {
            font-family: -apple-system, 'SF Pro Text', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, #0a0e1a 0%, #0f1420 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Background decoration */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 50%, rgba(139, 92, 246, 0.08) 0%, transparent 50%);
            pointer-events: none;
        }
        
        body::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 80% 80%, rgba(6, 182, 212, 0.08) 0%, transparent 50%);
            pointer-events: none;
        }
        
        .auth-container {
            background: rgba(17, 24, 39, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 32px;
            border: 0.5px solid rgba(139, 92, 246, 0.3);
            width: 100%;
            max-width: 460px;
            padding: 40px 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.5s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .logo-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #8b5cf6, #06b6d4);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0.4); }
            70% { box-shadow: 0 0 0 15px rgba(139, 92, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0); }
        }
        
        .logo-icon i {
            font-size: 28px;
            color: white;
        }
        
        .logo h1 {
            background: linear-gradient(135deg, #ffffff, #8b5cf6, #06b6d4);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .title {
            font-size: 24px;
            font-weight: 700;
            color: #e2f0e8;
            text-align: center;
            margin-bottom: 8px;
        }
        
        .subtitle {
            font-size: 13px;
            color: #9aaebe;
            text-align: center;
            margin-bottom: 32px;
        }
        
        /* TikTok Button */
        .btn-tiktok {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            background: linear-gradient(135deg, #000000, #1a1a2e);
            border: 1.5px solid rgba(6, 182, 212, 0.5);
            padding: 14px 20px;
            border-radius: 60px;
            color: white;
            font-weight: 600;
            font-size: 15px;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .btn-tiktok::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s;
        }
        
        .btn-tiktok:hover::before {
            left: 100%;
        }
        
        .btn-tiktok:hover {
            border-color: #06b6d4;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(6, 182, 212, 0.3);
        }
        
        .btn-tiktok i {
            font-size: 22px;
            color: #06b6d4;
        }
        
        /* Back Button */
        .btn-back {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 20px;
            color: #9aaebe;
            font-size: 13px;
            text-decoration: none;
            transition: all 0.2s;
            padding: 8px;
            border-radius: 40px;
        }
        
        .btn-back:hover {
            color: #8b5cf6;
            background: rgba(139, 92, 246, 0.1);
        }
        
        /* Info Box */
        .info-box {
            background: rgba(139, 92, 246, 0.08);
            border-radius: 20px;
            padding: 20px;
            margin-top: 28px;
            border: 0.5px solid rgba(139, 92, 246, 0.2);
        }
        
        .info-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
        }
        
        .info-header i {
            font-size: 20px;
            color: #8b5cf6;
        }
        
        .info-header span {
            font-size: 13px;
            font-weight: 600;
            color: #e2f0e8;
        }
        
        .info-box ul {
            padding-left: 20px;
        }
        
        .info-box li {
            font-size: 12px;
            color: #9aaebe;
            margin: 8px 0;
            line-height: 1.4;
        }
        
        .info-box li i {
            width: 20px;
            color: #4ade80;
            margin-right: 8px;
        }
        
        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-top: 20px;
        }
        
        .feature-item {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 14px;
            padding: 12px;
            text-align: center;
            transition: all 0.2s;
        }
        
        .feature-item:hover {
            background: rgba(139, 92, 246, 0.1);
            transform: translateY(-2px);
        }
        
        .feature-item i {
            font-size: 24px;
            margin-bottom: 6px;
            display: block;
        }
        
        .feature-item .feature-title {
            font-size: 11px;
            font-weight: 600;
            color: #e2f0e8;
        }
        
        .feature-item .feature-desc {
            font-size: 9px;
            color: #9aaebe;
            margin-top: 4px;
        }
        
        /* Loading State */
        .loading {
            display: none;
            text-align: center;
            padding: 40px;
        }
        
        .loading i {
            font-size: 40px;
            color: #8b5cf6;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* Toast Message */
        .toast-message {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: #34d399;
            color: #0a0e1a;
            padding: 12px 24px;
            border-radius: 40px;
            font-size: 13px;
            font-weight: 500;
            z-index: 9999;
            animation: slideUp 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        .toast-message.error {
            background: #ef4444;
            color: white;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .auth-container {
                padding: 28px 20px;
            }
            .title {
                font-size: 22px;
            }
            .features-grid {
                grid-template-columns: 1fr;
            }
            .btn-tiktok {
                padding: 12px 16px;
                font-size: 14px;
            }
        }
        
        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 24px 0 16px;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 0.5px solid rgba(255, 255, 255, 0.1);
        }
        
        .divider span {
            padding: 0 12px;
            color: #6b7280;
            font-size: 11px;
        }
        /* Alert styles */
.alert {
    border-radius: 16px;
    padding: 14px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    animation: slideIn 0.3s ease;
}

.alert-error {
    background: rgba(239, 68, 68, 0.15);
    border: 1px solid #ef4444;
}

.alert-success {
    background: rgba(74, 222, 128, 0.15);
    border: 1px solid #4ade80;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
    </style>
</head>
<body>
    <!-- Di dalam auth-container, tambahkan alert box -->
<?php if (isset($error) && $error): ?>
<div class="alert alert-error" style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; border-radius: 16px; padding: 14px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
    <i class="fas fa-exclamation-triangle" style="color: #ef4444; font-size: 18px;"></i>
    <div style="flex: 1; font-size: 13px; color: #fca5a5;"><?= htmlspecialchars($error) ?></div>
    <button onclick="this.parentElement.remove()" style="background: none; border: none; color: #ef4444; cursor: pointer;"><i class="fas fa-times"></i></button>
</div>
<?php endif; ?>

<?php if (isset($success) && $success): ?>
<div class="alert alert-success" style="background: rgba(74, 222, 128, 0.15); border: 1px solid #4ade80; border-radius: 16px; padding: 14px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
    <i class="fas fa-check-circle" style="color: #4ade80; font-size: 18px;"></i>
    <div style="flex: 1; font-size: 13px; color: #86efac;"><?= htmlspecialchars($success) ?></div>
    <button onclick="this.parentElement.remove()" style="background: none; border: none; color: #4ade80; cursor: pointer;"><i class="fas fa-times"></i></button>
</div>
<?php endif; ?>
    <div class="auth-container">
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-crown"></i>
            </div>
            <h1>Toopai</h1>
        </div>
        
        <div class="title">Connect Your TikTok</div>
        <div class="subtitle">Sign up or login with TikTok account</div>
        
        <!-- TikTok Login Button -->
        <a href="<?= base_url('creator_auth/do_authorize_tiktok') ?>" class="btn-tiktok" id="tiktokLoginBtn">
            <i class="fab fa-tiktok"></i> Continue with TikTok
        </a>
        
        <div class="divider">
            <span>or</span>
        </div>
        
        <!-- Back to Login -->
        <a href="<?= base_url('creator_auth/login') ?>" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back to Login
        </a>
        
        <!-- Info Box -->
        <div class="info-box">
            <div class="info-header">
                <i class="fas fa-info-circle"></i>
                <span>Why connect TikTok?</span>
            </div>
            <ul>
                <li><i class="fas fa-bolt"></i> Quick registration with your TikTok account</li>
                <li><i class="fas fa-chart-line"></i> Get your content analytics & performance data</li>
                <li><i class="fas fa-link"></i> Access exclusive affiliate links</li>
                <li><i class="fas fa-money-bill-wave"></i> Track your earnings & commissions</li>
            </ul>
        </div>
        
        <!-- Features Grid -->
        <div class="features-grid">
            <div class="feature-item">
                <i class="fas fa-rocket" style="color: #8b5cf6;"></i>
                <div class="feature-title">Fast Registration</div>
                <div class="feature-desc">Sign up in seconds</div>
            </div>
            <div class="feature-item">
                <i class="fas fa-shield-alt" style="color: #4ade80;"></i>
                <div class="feature-title">Secure & Safe</div>
                <div class="feature-desc">Your data is protected</div>
            </div>
            <div class="feature-item">
                <i class="fas fa-chart-simple" style="color: #fbbf24;"></i>
                <div class="feature-title">Real-time Analytics</div>
                <div class="feature-desc">Track your performance</div>
            </div>
            <div class="feature-item">
                <i class="fas fa-headset" style="color: #06b6d4;"></i>
                <div class="feature-title">24/7 Support</div>
                <div class="feature-desc">We're here to help</div>
            </div>
        </div>
    </div>
    
    <div id="loadingOverlay" class="loading" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(8px); z-index: 1000; display: flex; align-items: center; justify-content: center; flex-direction: column;">
        <i class="fas fa-spinner fa-pulse fa-3x" style="color: #8b5cf6;"></i>
        <p style="margin-top: 16px; color: #e2f0e8;">Redirecting to TikTok...</p>
    </div>
    
    <script>
        const baseUrl = '<?= base_url() ?>';
        
        // Show loading on TikTok button click
        document.getElementById('tiktokLoginBtn')?.addEventListener('click', function(e) {
            const loadingDiv = document.getElementById('loadingOverlay');
            if (loadingDiv) {
                loadingDiv.style.display = 'flex';
            }
        });
        
        // Check for error message from URL
        const urlParams = new URLSearchParams(window.location.search);
        const error = urlParams.get('error');
        const errorDesc = urlParams.get('error_description');
        
        if (error) {
            showToast(errorDesc || error, 'error');
        }
        
        function showToast(message, type = 'success') {
            const existingToast = document.querySelector('.toast-message');
            if (existingToast) existingToast.remove();
            
            const toast = document.createElement('div');
            toast.className = 'toast-message' + (type === 'error' ? ' error' : '');
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(-50%) translateY(20px)';
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }
        
        // Remove loading overlay if page loads with error
        window.addEventListener('load', function() {
            const loadingDiv = document.getElementById('loadingOverlay');
            if (loadingDiv && loadingDiv.style.display === 'flex') {
                setTimeout(() => {
                    loadingDiv.style.display = 'none';
                }, 1000);
            }
        });
    </script>
</body>
</html>