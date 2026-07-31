<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport">
    <title>TOOPAI Creator Login</title>
    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/logo/favicon_new.png') ?>">
    <link rel="icon" type="image/svg+xml" href="<?= base_url('assets/logo/favicon_new.png') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/logo/favicon_new.png') ?>">
    
<link href="https://fonts.googleapis.com" rel="preconnect">
<link crossorigin href="https://fonts.gstatic.com" rel="preconnect">
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
<style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: radial-gradient(circle at 8% 12%, rgba(110, 55, 255, 0.45), transparent 48%),
                radial-gradient(circle at 92% 84%, rgba(0, 180, 255, 0.38), transparent 52%),
                linear-gradient(128deg, #03021a 0%, #0b082f 65%, #130b35 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    position: relative;
  }

  /* floating orbs */
  .orb {
    position: fixed;
    border-radius: 50%;
    filter: blur(90px);
    opacity: 0.45;
    pointer-events: none;
    z-index: 0;
  }
  .orb-1 { width: 340px; height: 340px; background: #9733ff; top: -90px; left: -70px; }
  .orb-2 { width: 440px; height: 440px; background: #0080ff; bottom: -110px; right: -80px; }
  .orb-3 { width: 280px; height: 280px; background: #ff3bcb; top: 35%; right: 12%; opacity: 0.28; }

  /* main container */
  .login-container {
    width: 100%;
    max-width: 1280px;
    margin: 0 auto;
    background: rgba(12, 9, 38, 0.52);
    backdrop-filter: blur(6px);
    border-radius: 52px;
    box-shadow: 0 32px 80px rgba(0, 0, 0, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(140, 100, 255, 0.4);
    overflow: hidden;
    position: relative;
    z-index: 2;
  }

  .two-columns {
    display: flex;
    flex-wrap: wrap;
    min-height: 700px;
  }

  /* ========= KOLOM KIRI: MASKOT & BRANDING (DESKTOP ONLY) ========= */
  .mascot-side {
    flex: 1;
    background: linear-gradient(135deg, rgba(38, 28, 110, 0.55) 0%, rgba(12, 8, 48, 0.6) 100%);
    backdrop-filter: blur(5px);
    padding: 48px 32px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    border-right: 1px solid rgba(150, 115, 255, 0.4);
    transition: all 0.2s ease;
  }

  .mascot-wrapper {
    max-width: 360px;
    margin-bottom: 28px;
    animation: floatMascot 4.8s ease-in-out infinite;
  }

  .mascot-img {
    width: 100%;
    filter: drop-shadow(0 0 32px rgba(120, 70, 255, 0.8)) drop-shadow(0 12px 28px rgba(0,0,0,0.25));
    display: block;
  }

  .mascot-side h2 {
    font-size: 34px;
    font-weight: 800;
    background: linear-gradient(125deg, #FFFFFF, #d9bdff);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    letter-spacing: -0.02em;
    margin-bottom: 14px;
  }

  .mascot-side p {
    color: #cddcff;
    font-size: 16px;
    line-height: 1.5;
    max-width: 330px;
    margin: 0 auto;
    font-weight: 500;
  }

  .trust-badge {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 14px;
    margin-top: 36px;
    padding: 12px 24px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 140px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(200, 170, 255, 0.25);
  }
  .avatars {
    display: flex;
  }
  .avatars span {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: linear-gradient(145deg, #c99aff, #8555ff);
    border: 2px solid #fff;
    margin-left: -12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
  }
  .avatars span:first-child { margin-left: 0; }
  .trust-badge div b { color: white; font-size: 15px; }
  .trust-badge div p { font-size: 12px; margin: 0; opacity: 0.9; }

  /* ========= KOLOM KANAN: LOGIN FORM ========= */
  .login-side {
    flex: 1;
    padding: 52px 48px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    background: rgba(6, 4, 26, 0.68);
    backdrop-filter: blur(10px);
  }

  /* === BRAND HEADER DENGAN LOGO LEBIH BESAR === */
  .brand-header {
    display: flex;
    justify-content: center;
    margin-bottom: 28px;
  }
  .brand-logo-form {
    width: 260px;      /* DIPERBESAR dari 190px */
    height: 200px;      /* DIPERBESAR dari 68px */
    background: url("<?= base_url('assets/logo/new_logo_toopai_web.png') ?>") center/contain no-repeat;
    filter: drop-shadow(0 0 20px rgba(110, 85, 255, 0.8));
    transition: all 0.2s ease;
  }
  /* Untuk layar medium tetap proporsional */
  @media (max-width: 1100px) {
    .brand-logo-form {
      width: 220px;
      height: 78px;
    }
  }
  @media (max-width: 600px) {
    .brand-logo-form {
      width: 200px;
      height: 70px;
    }
  }

  .login-card {
    width: 100%;
    max-width: 460px;
    margin: 0 auto;
  }

  .login-card h1 {
    font-size: 38px;
    font-weight: 800;
    letter-spacing: -0.03em;
    margin-bottom: 8px;
    background: linear-gradient(120deg, #fff, #e0c5ff);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
  }
  .login-sub {
    color: #b0bef0;
    font-size: 15px;
    margin-bottom: 34px;
    font-weight: 500;
    border-left: 3px solid #a35cff;
    padding-left: 12px;
  }

  .field {
    position: relative;
    margin-bottom: 22px;
  }
  .field input {
    width: 100%;
    height: 58px;
    background: rgba(8, 6, 30, 0.85);
    border: 1px solid rgba(130, 95, 255, 0.5);
    border-radius: 24px;
    padding: 0 20px 0 52px;
    font-size: 15px;
    font-weight: 500;
    color: #fff;
    transition: all 0.2s ease;
    font-family: 'Inter', sans-serif;
  }
  .field input:focus {
    outline: none;
    border-color: #a975ff;
    box-shadow: 0 0 0 4px rgba(130, 75, 255, 0.25);
    background: rgba(12, 9, 40, 0.95);
  }
  .field .fi {
    position: absolute;
    left: 22px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 20px;
    opacity: 0.85;
    color: #ccc2ff;
  }

  .password-field {
    position: relative;
  }
  .eye-btn {
    position: absolute;
    right: 18px;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(200, 200, 255, 0.08);
    border: none;
    border-radius: 40px;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #cdd6ff;
    transition: 0.2s;
  }
  .eye-btn:hover {
    background: rgba(135, 85, 255, 0.4);
    color: white;
  }
  .forgot-row {
    text-align: right;
    margin: -8px 0 26px 0;
  }
  .forgot-row button {
    background: none;
    border: none;
    color: #b2ceff;
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
    transition: 0.2s;
  }
  .forgot-row button:hover {
    color: white;
    text-decoration: underline;
  }

  .btn-login {
    width: 100%;
    height: 58px;
    background: linear-gradient(96deg, #9a5cff, #2d9eff);
    border: none;
    border-radius: 32px;
    font-weight: 800;
    font-size: 16px;
    color: white;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    box-shadow: 0 14px 28px rgba(70, 80, 255, 0.35);
    margin-bottom: 24px;
  }
  .btn-login:hover {
    transform: translateY(-2px);
    filter: brightness(1.02);
    box-shadow: 0 20px 38px rgba(90, 75, 255, 0.45);
  }
  .btn-login.loading {
    pointer-events: none;
    opacity: 0.75;
    position: relative;
  }
  .btn-login.loading:after {
    content: "";
    position: absolute;
    width: 24px;
    height: 24px;
    top: 50%;
    left: 50%;
    margin-left: -12px;
    margin-top: -12px;
    border: 2px solid #fff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 0.8s linear infinite;
  }

  .divider {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #7c89bc;
    font-size: 13px;
    margin: 18px 0 18px;
  }
  .divider:before, .divider:after {
    content: "";
    flex: 1;
    height: 1px;
    background: rgba(255, 255, 255, 0.2);
  }

  .social-login {
    display: flex;
    gap: 18px;
    justify-content: center;
  }
  .social-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    background: rgba(18, 22, 55, 0.85);
    border: 1px solid rgba(120, 90, 245, 0.6);
    border-radius: 44px;
    padding: 12px 0;
    font-weight: 700;
    font-size: 14px;
    color: white;
    cursor: pointer;
    transition: 0.2s;
    text-decoration: none;
  }
  .social-btn img {
    width: 22px;
    height: 22px;
    object-fit: contain;
  }
  .social-btn:hover {
    background: rgba(80, 60, 200, 0.8);
    transform: translateY(-2px);
    border-color: #c094ff;
  }

  .register-redirect {
    text-align: center;
    margin-top: 28px;
    color: #b5c4f0;
    font-size: 14px;
  }
  .register-redirect button {
    background: none;
    border: none;
    color: #e0baff;
    font-weight: 800;
    cursor: pointer;
    font-size: 14px;
  }
  .register-redirect button:hover {
    text-decoration: underline;
    color: white;
  }

  #errorMessage {
    background: rgba(255, 80, 80, 0.12);
    border: 1px solid #ff7a6e;
    border-radius: 20px;
    padding: 12px 18px;
    margin-bottom: 24px;
    font-size: 13px;
    font-weight: 500;
    display: none;
    color: #ffcfc7;
  }
  #errorMessage.show {
    display: block;
    animation: fadeSlide 0.25s ease;
  }

  @keyframes fadeSlide {
    from { opacity: 0; transform: translateY(-6px); }
    to { opacity: 1; transform: translateY(0); }
  }
  @keyframes spin {
    to { transform: rotate(360deg); }
  }
  @keyframes floatMascot {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-14px); }
  }

  /* ===== RESPONSIVE: Pada mobile (≤ 900px) HIDE kolom maskot ===== */
  @media (max-width: 900px) {
    .mascot-side {
      display: none; /* maskot tidak ditampilkan di mobile & tablet sempit */
    }
    .login-side {
      flex: none;
      width: 100%;
      padding: 44px 28px;
      border-radius: 0;
    }
    .two-columns {
      min-height: auto;
    }
    .login-container {
      border-radius: 44px;
    }
    .login-card h1 {
      font-size: 32px;
    }
  }

  /* Untuk tablet landscape atau desktop kecil tetap tampil kolom maskot (default: tampil) */
  @media (min-width: 901px) and (max-width: 1100px) {
    .mascot-side {
      padding: 32px 20px;
    }
    .mascot-side h2 {
      font-size: 26px;
    }
    .mascot-wrapper {
      max-width: 260px;
    }
    .login-side {
      padding: 40px 28px;
    }
  }

  @media (max-width: 480px) {
    .login-side {
      padding: 32px 20px;
    }
    .social-login {
      flex-direction: column;
      gap: 12px;
    }
    .btn-login {
      height: 52px;
    }
  }

  /* loading overlay style */
  .toopai-loading-overlay {
    position: fixed;
    inset: 0;
    z-index: 99999;
    display: none;
    place-items: center;
    background: radial-gradient(circle at 50% 30%, rgba(75, 40, 190, 0.95), #030012);
    backdrop-filter: blur(20px);
  }
  .toopai-loading-overlay.show {
    display: grid;
    animation: fadeInLoad 0.3s ease;
  }
  .loading-card {
    text-align: center;
  }
  .loading-mascot {
    width: 170px;
    filter: drop-shadow(0 0 28px #b77eff);
    animation: floatMascot 1.2s infinite;
  }
  .loading-title {
    font-size: 32px;
    font-weight: 800;
    color: white;
    margin-top: 16px;
  }
  @keyframes fadeInLoad {
    from { opacity: 0; }
    to { opacity: 1; }
  }
</style>
</head>
<body>

<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<div class="login-container">
  <div class="two-columns">
    <!-- KOLOM KIRI: MASKOT & BRANDING (hanya muncul di web/desktop, mobile di-sembunyikan via media query) -->
    <div class="mascot-side">
      <div class="mascot-wrapper">
        <img class="mascot-img" src="<?= base_url('assets/logo/toopai-mascot.png') ?>" alt="TOOPAI Mascot" onerror="this.src='https://placehold.co/500x500?text=Toopai+Character'">
      </div>
      <h2>Creator<br>Powerhouse</h2>
      <p>AI-driven brand matching, advanced analytics, and global campaigns.</p>
      <div class="trust-badge">
        <div class="avatars">
          <span></span><span></span><span></span><span></span>
        </div>
        <div>
          <b>Trusted by 10K+ creators</b>
          <p>From TikTok, Instagram & YouTube</p>
        </div>
      </div>
    </div>

    <!-- KOLOM KANAN: LOGIN FORM (Tampil di semua device) dengan LOGO LEBIH BESAR -->
    <div class="login-side">
      <div class="brand-header">
        <div class="brand-logo-form" title="TOOPAI"></div>
      </div>
      <div class="login-card">
        <h1>Welcome back</h1>
        <div class="login-sub">Sign in to access your creator dashboard</div>
        
        <div id="errorMessage"></div>

        <form id="loginTwoColForm">
          <div class="field">
            <span class="fi">📧</span>
            <input type="text" id="emailInput" placeholder="Email or Username" autocomplete="username" required>
          </div>
          <div class="field password-field">
            <span class="fi">🔒</span>
            <input type="password" id="passwordInput" placeholder="Password" autocomplete="current-password" required>
            <button type="button" class="eye-btn" onclick="togglePasswordField('passwordInput', this)">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M3 12s3.3-5 9-5 9 5 9 5-3.3 5-9 5-9-5-9-5Z"/>
                <path d="M7 12h10" stroke-linecap="round"/>
              </svg>
            </button>
          </div>
          <div class="forgot-row">
            <button type="button" id="forgotBtnTrigger">Forgot password?</button>
          </div>
          <button type="submit" class="btn-login" id="submitLoginBtn">Login →</button>
        </form>

        <div class="divider">or continue with</div>
        <div class="social-login">
          <a href="<?= base_url('creator_auth/authorize_tiktok') ?>" class="social-btn" target="_blank">
            <img src="<?= base_url('assets/logo/logo_tiktok.png') ?>" alt="TikTok">
            TikTok
          </a>
          <div class="social-btn" onclick="alert('Google OAuth ready for production integration')">
            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 48 48'%3E%3Cpath fill='%23FFC107' d='M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z'/%3E%3Cpath fill='%23FF3D00' d='M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z'/%3E%3Cpath fill='%234CAF50' d='M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z'/%3E%3Cpath fill='%231976D2' d='M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z'/%3E%3C/svg%3E" style="width:20px;" alt="Google">
            Google
          </div>
        </div>

        <div class="register-redirect">
          Don’t have an account? <button type="button" id="showRegisterBtn">Register now →</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Loading Overlay Global -->
<div id="toopaiLoadingOverlay" class="toopai-loading-overlay">
  <div class="loading-card">
    <img class="loading-mascot" src="<?= base_url('assets/logo/toopai-mascot.png') ?>" alt="loading mascot">
    <div class="loading-title">Entering Toopai</div>
    <div style="color:#cdbefa; margin-top:8px;">Preparing creator hub</div>
  </div>
</div>

<script>
  // Toggle password visibility
  window.togglePasswordField = function(inputId, btnElement) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    if (btnElement) {
      btnElement.innerHTML = isPassword ? 
        `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 12s3.3-5 9-5 9 5 9 5-3.3 5-9 5-9-5-9-5Z"/><circle cx="12" cy="12" r="2.7"/><circle cx="12" cy="12" r="1" fill="currentColor"/></svg>` :
        `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 12s3.3-5 9-5 9 5 9 5-3.3 5-9 5-9-5-9-5Z"/><path d="M7 12h10" stroke-linecap="round"/></svg>`;
    }
  };

  function showFullLoader() {
    const loader = document.getElementById('toopaiLoadingOverlay');
    if (loader) loader.classList.add('show');
  }

  function hideLoader() {
    const loader = document.getElementById('toopaiLoadingOverlay');
    if (loader) loader.classList.remove('show');
  }

  function showErrorMessage(msg) {
    const errDiv = document.getElementById('errorMessage');
    if (errDiv) {
      errDiv.textContent = msg;
      errDiv.classList.add('show');
      setTimeout(() => errDiv.classList.remove('show'), 4800);
    }
  }

  // LOGIN FORM SUBMIT
  const loginForm = document.getElementById('loginTwoColForm');
  const loginBtn = document.getElementById('submitLoginBtn');

  if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const email = document.getElementById('emailInput').value.trim();
      const password = document.getElementById('passwordInput').value;

      if (!email || !password) {
        showErrorMessage('Please enter email/username and password');
        return;
      }

      if (loginBtn) {
        loginBtn.classList.add('loading');
        loginBtn.textContent = 'Signing In';
      }

      try {
        const response = await fetch('<?= base_url("creator_auth/do_login") ?>', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ email: email, password: password })
        });

        const data = await response.json();

        if (data.success) {
          showFullLoader();
          setTimeout(() => {
            window.location.href = data.redirect || '<?= base_url("creator/dashboard") ?>';
          }, 1800);
        } else {
          if (loginBtn) {
            loginBtn.classList.remove('loading');
            loginBtn.textContent = 'Login →';
          }
          showErrorMessage(data.message || 'Invalid credentials. Please try again.');
        }
      } catch (err) {
        if (loginBtn) {
          loginBtn.classList.remove('loading');
          loginBtn.textContent = 'Login →';
        }
        showErrorMessage('Network error: ' + err.message);
      }
    });
  }

  // Forgot password & register redirects
  const forgotTrigger = document.getElementById('forgotBtnTrigger');
  if (forgotTrigger) {
    // forgotTrigger.addEventListener('click', () => {
    //   window.location.href = '<?= base_url("creator_auth/forgot_password") ?>';
    // });
  }

  const registerBtn = document.getElementById('showRegisterBtn');
  if (registerBtn) {
    registerBtn.addEventListener('click', () => {
      window.location.href = '<?= base_url("creator_auth/register") ?>';
    });
  }

  // Set dynamic images for logo & mascot (fallback consistency)
  (function setCustomAssets() {
    const logoElement = document.querySelector('.brand-logo-form');
    const mascotImgElem = document.querySelector('.mascot-img');
    const loadingMascotElem = document.querySelector('.loading-mascot');
    const baseLogo = "<?= base_url('assets/logo/new_logo_toopai_web.png') ?>";
    const baseMascot = "<?= base_url('assets/logo/toopai-mascot.png') ?>";
    if (logoElement && baseLogo) logoElement.style.backgroundImage = `url("${baseLogo}")`;
    if (mascotImgElem && baseMascot) mascotImgElem.src = baseMascot;
    if (loadingMascotElem && baseMascot) loadingMascotElem.src = baseMascot;
  })();

  window.addEventListener('load', () => {
    hideLoader();
  });
</script>
</body>
</html>