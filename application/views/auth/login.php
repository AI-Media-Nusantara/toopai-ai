<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Toopai Affiliate Platform</title>
   <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/logo/favicon_new.png') ?>">
    <link rel="icon" type="image/svg+xml" href="<?= base_url('assets/logo/favicon_new.png') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/logo/favicon_new.png') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/logo/toopai_logo_2.png') ?>">
    <link rel="icon" type="image/svg+xml" href="<?= base_url('toopai_logo_2.png') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('toopai_logo_2.png') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Animated Background Decorations */
        .bg-decoration {
            position: fixed;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(74, 222, 128, 0.08) 0%, transparent 70%);
            border-radius: 50%;
            z-index: 0;
            pointer-events: none;
        }
        .decoration-1 {
            top: -200px;
            left: -200px;
            animation: float 20s ease-in-out infinite;
        }
        .decoration-2 {
            bottom: -200px;
            right: -200px;
            animation: float 15s ease-in-out infinite reverse;
        }
        .decoration-3 {
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(74, 222, 128, 0.04) 0%, transparent 70%);
            animation: pulse 10s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(30px, 30px); }
        }
        
        @keyframes pulse {
            0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.5; }
            50% { transform: translate(-50%, -50%) scale(1.1); opacity: 0.8; }
        }
        
        .login-container {
            position: relative;
            z-index: 1;
            background: rgba(17, 24, 39, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 32px;
            padding: 48px;
            width: 460px;
            max-width: 90vw;
            border: 1px solid rgba(74, 222, 128, 0.2);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            transition: transform 0.3s ease;
        }
        
        .login-container:hover {
            transform: translateY(-5px);
        }
        
        /* Logo Area */
        .logo-area {
            text-align: center;
            margin-bottom: 0px;
        }
        
        .logo-image {
            width: 180px;
            height: auto;
            margin: 0 auto 16px;
        }
        
        .logo-image img {
            width: 100%;
            height: auto;
            object-fit: contain;
            filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.2));
        }
        
        .tagline {
            color: #8e9eae;
            font-size: 13px;
            letter-spacing: 0.5px;
            margin-top: -8px;
        }
        
        .subtitle {
            text-align: center;
            color: #8e9eae;
            font-size: 14px;
            margin-bottom: 32px;
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(74, 222, 128, 0.15);
        }
        
        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            color: #bdf2c0;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 13px;
        }
        
        input {
            width: 100%;
            padding: 14px 16px;
            background: #0f1420;
            border: 1px solid #2a3346;
            border-radius: 16px;
            color: #e2e8f0;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        
        input:focus {
            outline: none;
            border-color: #4ade80;
            box-shadow: 0 0 0 3px rgba(74, 222, 128, 0.1);
        }
        
        input::placeholder {
            color: #4a5568;
        }
        
        /* Button Styles */
        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #4ade80, #22c55e);
            border: none;
            border-radius: 40px;
            color: #0a0e17;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            margin-top: 24px;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }
        
        button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }
        
        button:hover::before {
            left: 100%;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(74, 222, 128, 0.4);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        /* Error Message */
        .error {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 13px;
            border: 1px solid rgba(239, 68, 68, 0.3);
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                padding: 32px 24px;
            }
            .logo-image {
                width: 140px;
            }
            button {
                font-size: 14px;
            }
        }
        
        /* Loading State */
        button.loading {
            background: linear-gradient(135deg, #3a9e5e, #1a8a46);
            pointer-events: none;
        }
        
        button.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Demo Info */
        .demo-info {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid rgba(74, 222, 128, 0.15);
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }
        
        .demo-info p {
            margin: 4px 0;
        }
        
        .demo-info strong {
            color: #4ade80;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Background Decorations -->
    <div class="bg-decoration decoration-1"></div>
    <div class="bg-decoration decoration-2"></div>
    <div class="bg-decoration decoration-3"></div>
    
    <div class="login-container">
        <!-- Logo Area -->
        <div class="logo-area">
            <div class="logo-image">
                <img src="<?= base_url('assets/logo/new_logo_toopai_web.png') ?>" alt="Toopai Logo">
            </div>
             <div class="subtitle">
            <i class="tagline">Affiliate Partner Platform</i>
            </div>
        </div>
        
       
        
        <?php if ($this->session->flashdata('error')): ?>
        <div class="error"><?= $this->session->flashdata('error') ?></div>
        <?php endif; ?>
        
        <form action="<?= base_url('auth/login') ?>" method="POST" id="loginForm">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required placeholder="admin@toopai.com" autocomplete="email">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="••••••••" autocomplete="current-password">
            </div>
            <button type="submit" id="loginBtn">Login →</button>
        </form>
        
       
    </div>
    
    <script>
        // Loading effect on form submit
        document.getElementById('loginForm')?.addEventListener('submit', function(e) {
            const btn = document.getElementById('loginBtn');
            btn.classList.add('loading');
            btn.textContent = 'Loading...';
        });
    </script>
</body>
</html>