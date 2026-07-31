<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport">
<title>TOOPAI Creator Registration | AI-Powered</title>
 <!-- FAVICON - Multiple formats for maximum compatibility -->
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
    opacity: 0;
    transform: translateY(12px);
    transition: opacity 0.6s cubic-bezier(0.2, 0.9, 0.4, 1.1), transform 0.6s ease;
  }
  .login-container.loaded {
    opacity: 1;
    transform: translateY(0);
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

  /* ========= KOLOM KANAN: REGISTRATION FORM ========= */
  .login-side {
    flex: 1;
    padding: 42px 44px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    background: rgba(6, 4, 26, 0.68);
    backdrop-filter: blur(10px);
  }

  /* BRAND HEADER DENGAN LOGO BESAR */
  .brand-header {
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
  }
  .brand-logo-form {
    width: 260px;
    height: 200px;
    background: url("<?= base_url('assets/logo/new_logo_toopai_web.png') ?>") center/contain no-repeat;
    filter: drop-shadow(0 0 20px rgba(110, 85, 255, 0.8));
    transition: all 0.2s ease;
  }
  @media (max-width: 1100px) {
    .brand-logo-form { width: 220px; height: 78px; }
  }
  @media (max-width: 600px) {
    .brand-logo-form { width: 200px; height: 70px; }
  }

  .login-card {
    width: 100%;
    max-width: 500px;
    margin: 0 auto;
  }

  .login-card h1 {
    font-size: 34px;
    font-weight: 800;
    letter-spacing: -0.03em;
    margin-bottom: 6px;
    background: linear-gradient(120deg, #fff, #e0c5ff);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
  }
  .login-sub {
    color: #b0bef0;
    font-size: 14px;
    margin-bottom: 28px;
    font-weight: 500;
    border-left: 3px solid #a35cff;
    padding-left: 12px;
  }

  .field {
    position: relative;
    margin-bottom: 18px;
  }
  .field input, .field select {
    width: 100%;
    height: 54px;
    background: rgba(8, 6, 30, 0.85);
    border: 1px solid rgba(130, 95, 255, 0.5);
    border-radius: 20px;
    padding: 0 16px 0 48px;
    font-size: 14px;
    font-weight: 500;
    color: #fff;
    transition: all 0.2s ease;
    font-family: 'Inter', sans-serif;
  }
  .field select {
    appearance: none;
    cursor: pointer;
    padding: 0 16px 0 48px;
  }
  .field input:focus, .field select:focus {
    outline: none;
    border-color: #a975ff;
    box-shadow: 0 0 0 4px rgba(130, 75, 255, 0.25);
    background: rgba(12, 9, 40, 0.95);
  }
  .field .fi {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 18px;
    opacity: 0.85;
    color: #ccc2ff;
    pointer-events: none;
  }

  .password-field {
    position: relative;
  }
  .eye-btn {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(200, 200, 255, 0.08);
    border: none;
    border-radius: 40px;
    width: 34px;
    height: 34px;
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

  .row-2col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
  }

  .btn-register {
    width: 100%;
    height: 56px;
    background: linear-gradient(96deg, #9a5cff, #2d9eff);
    border: none;
    border-radius: 30px;
    font-weight: 800;
    font-size: 16px;
    color: white;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    box-shadow: 0 14px 28px rgba(70, 80, 255, 0.35);
    margin-top: 8px;
  }
  .btn-register:hover {
    transform: translateY(-2px);
    filter: brightness(1.02);
    box-shadow: 0 20px 38px rgba(90, 75, 255, 0.45);
  }
  .btn-register.loading {
    pointer-events: none;
    opacity: 0.7;
    position: relative;
  }
  .btn-register.loading:after {
    content: "";
    position: absolute;
    width: 22px;
    height: 22px;
    top: 50%;
    left: 50%;
    margin-left: -11px;
    margin-top: -11px;
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
    font-size: 12px;
    margin: 20px 0 16px;
  }
  .divider:before, .divider:after {
    content: "";
    flex: 1;
    height: 1px;
    background: rgba(255, 255, 255, 0.2);
  }

  .social-login {
    display: flex;
    gap: 16px;
    justify-content: center;
  }
  .social-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    background: rgba(18, 22, 55, 0.85);
    border: 1px solid rgba(120, 90, 245, 0.6);
    border-radius: 40px;
    padding: 11px 0;
    font-weight: 700;
    font-size: 13px;
    color: white;
    cursor: pointer;
    transition: 0.2s;
    text-decoration: none;
  }
  .social-btn img {
    width: 20px;
    height: 20px;
    object-fit: contain;
  }
  .social-btn:hover {
    background: rgba(80, 60, 200, 0.8);
    transform: translateY(-2px);
    border-color: #c094ff;
  }

  .login-redirect {
    text-align: center;
    margin-top: 24px;
    color: #b5c4f0;
    font-size: 13px;
  }
  .login-redirect button {
    background: none;
    border: none;
    color: #e0baff;
    font-weight: 800;
    cursor: pointer;
    font-size: 13px;
  }
  .login-redirect button:hover {
    text-decoration: underline;
    color: white;
  }

  #errorMessage, #successMessage {
    border-radius: 18px;
    padding: 12px 16px;
    margin-bottom: 18px;
    font-size: 13px;
    font-weight: 500;
    display: none;
  }
  #errorMessage {
    background: rgba(255, 80, 80, 0.12);
    border: 1px solid #ff7a6e;
    color: #ffcfc7;
  }
  #successMessage {
    background: rgba(80, 255, 150, 0.12);
    border: 1px solid #6effb0;
    color: #ccffdd;
  }
  #errorMessage.show, #successMessage.show {
    display: block;
    animation: fadeSlide 0.25s ease;
  }

  /* PRELOADER STYLE */
  .toopai-preloader {
    position: fixed;
    inset: 0;
    z-index: 999999;
    display: flex;
    align-items: center;
    justify-content: center;
    background: radial-gradient(circle at 50% 30%, #160b3a, #020015);
    backdrop-filter: blur(12px);
    transition: opacity 0.5s ease, visibility 0.5s ease;
    opacity: 1;
    visibility: visible;
  }
  .toopai-preloader.fade-out {
    opacity: 0;
    visibility: hidden;
  }
  .preloader-card {
    text-align: center;
    animation: preloaderPop 0.5s ease forwards;
  }
  .preloader-mascot {
    width: 130px;
    margin: 0 auto 20px;
    animation: floatPreloader 1.8s infinite ease-in-out;
  }
  .preloader-mascot img {
    width: 100%;
    filter: drop-shadow(0 0 28px #b37dff);
  }
  .preloader-logo {
    font-size: 44px;
    font-weight: 900;
    letter-spacing: -0.03em;
    background: linear-gradient(125deg, #ffffff, #c9aaff);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    margin-bottom: 8px;
  }
  .preloader-text {
    color: #b8b2ef;
    font-weight: 600;
    letter-spacing: 4px;
    font-size: 12px;
  }

  /* CHAT WIDGET STYLES */
  .toopai-chat-widget {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9999;
  }
  .chat-bubble {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #9a5cff, #2d9eff);
    border-radius: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 10px 25px rgba(0,0,0,0.3);
    transition: transform 0.2s;
  }
  .chat-bubble:hover { transform: scale(1.05); }
  .chat-bubble svg { width: 32px; height: 32px; stroke: white; stroke-width: 1.8; }
  .toopai-chat-panel {
    position: fixed;
    bottom: 100px;
    right: 24px;
    width: 360px;
    height: 500px;
    background: rgba(12, 10, 35, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 28px;
    border: 1px solid rgba(150, 100, 255, 0.5);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0,0,0,0.4);
    transform: scale(0);
    opacity: 0;
    transition: transform 0.25s ease, opacity 0.25s ease;
    transform-origin: bottom right;
    pointer-events: none;
  }
  .toopai-chat-panel.active {
    transform: scale(1);
    opacity: 1;
    pointer-events: auto;
  }
  .chat-header {
    padding: 16px 20px;
    background: rgba(50, 35, 110, 0.7);
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255,255,255,0.1);
  }
  .chat-header h4 { font-size: 16px; font-weight: 700; color: white; }
  .chat-close { background: none; border: none; color: white; font-size: 24px; cursor: pointer; }
  .chat-messages {
    flex: 1;
    padding: 16px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
  }
  .chat-message {
    max-width: 85%;
    padding: 10px 14px;
    border-radius: 20px;
    font-size: 13px;
    line-height: 1.4;
  }
  .chat-message.user {
    align-self: flex-end;
    background: linear-gradient(135deg, #8f4eff, #2f9eff);
    color: white;
    border-bottom-right-radius: 4px;
  }
  .chat-message.bot {
    align-self: flex-start;
    background: rgba(255,255,255,0.1);
    color: #e0e4ff;
    border-bottom-left-radius: 4px;
  }
  .chat-input-area {
    display: flex;
    padding: 12px;
    border-top: 1px solid rgba(255,255,255,0.1);
    gap: 10px;
  }
  .chat-input-area input {
    flex: 1;
    background: rgba(0,0,0,0.4);
    border: 1px solid rgba(130,95,255,0.5);
    border-radius: 40px;
    padding: 10px 16px;
    color: white;
    outline: none;
  }
  .chat-input-area button {
    background: #7d4eff;
    border: none;
    border-radius: 40px;
    padding: 0 20px;
    color: white;
    font-weight: bold;
    cursor: pointer;
  }

  @keyframes fadeSlide {
    from { opacity: 0; transform: translateY(-6px); }
    to { opacity: 1; transform: translateY(0); }
  }
  @keyframes spin { to { transform: rotate(360deg); } }
  @keyframes floatMascot { 0%,100%{transform:translateY(0)}50%{transform:translateY(-14px)} }
  @keyframes floatPreloader { 0%,100%{transform:translateY(0)}50%{transform:translateY(-12px)} }
  @keyframes preloaderPop {
    0% { opacity: 0; transform: scale(0.94) translateY(20px); }
    100% { opacity: 1; transform: scale(1) translateY(0); }
  }

  /* RESPONSIVE */
  @media (max-width: 900px) {
    .mascot-side { display: none; }
    .login-side { flex: none; width: 100%; padding: 36px 24px; }
    .two-columns { min-height: auto; }
    .login-container { border-radius: 44px; }
  }
  @media (max-width: 550px) {
    .row-2col { grid-template-columns: 1fr; gap: 0; }
    .login-side { padding: 28px 20px; }
    .toopai-chat-panel { width: 300px; right: 16px; bottom: 90px; }
  }
  
  .avatars{
  display:flex;
  align-items:center;
}

.avatars img{
  width:44px;
  height:44px;
  border-radius:50%;
  object-fit:cover;
  border:3px solid rgba(255,255,255,.95);
  box-shadow:0 0 18px rgba(168,85,255,.35);
}

.avatars img:not(:first-child){
  margin-left:-14px;
}
@media(max-width:768px){

  .avatars img{
    width:48px;
    height:48px;
  }

}

.apple-panel-active,
.hero-wrap,
.pain-section,
.connection-section,
.workflow-section,
.comparison-section,
.trusted-section,
.section,
.cta-final {
  transform: none !important;
  filter: none !important;
  opacity: 1 !important;
}

.reveal {
  opacity: 1 !important;
  transform: none !important;
  filter: none !important;
  transition: none !important;
}
</style>
</head>
<body>

<div class="orb orb-1"></div><div class="orb orb-2"></div><div class="orb orb-3"></div>

<!-- PRELOADER -->
<div class="toopai-preloader" id="toopaiPreloader">
  <div class="preloader-card">
    <div class="preloader-mascot">
      <img src="<?= base_url('assets/logo/toopai-mascot.png') ?>" alt="TOOPAI" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'80\' height=\'80\' viewBox=\'0 0 40 40\'%3E%3Ccircle cx=\'20\' cy=\'20\' r=\'18\' fill=\'%238b5cf6\'/%3E%3C/svg%3E'">
    </div>
    <div class="preloader-logo">TOOPAI</div>
    <div class="preloader-text">AI CREATIVE SYSTEM</div>
  </div>
</div>

<div class="login-container" id="loginContainer">
  <div class="two-columns">
    <!-- Kiri: Maskot (desktop) -->
    <div class="mascot-side">
      <div class="mascot-wrapper">
        <img class="mascot-img" src="<?= base_url('assets/logo/toopai-mascot.png') ?>" alt="TOOPAI Mascot">
      </div>
      <h2>Join Creator<br>Network</h2>
      <p>AI-powered brand matching, real-time analytics, and global campaigns.</p>
     
     <div class="trust-badge">

 <div class="avatars">

  <img
    class="creator-avatar"
    src="<?= base_url('assets/creator/sarwendahofficial.webp') ?>"
    alt="sarwendahofficial"
  >

  <img
    class="creator-avatar"
    src="<?= base_url('assets/creator/arthurtynel.webp') ?>"
    alt="arthurtynel"
  >

  <img
    class="creator-avatar"
    src="<?= base_url('assets/creator/billytynel.webp') ?>"
    alt="billytynel"
  >

  <img
    class="creator-avatar"
    src="<?= base_url('assets/creator/mellywdy.webp') ?>"
    alt="mellywdy"
  >

</div>

  <div>
    <b>Trusted by 10K+ creators</b>
    <p>TikTok • Instagram • YouTube</p>
  </div>

</div>
     
    </div>

    <!-- Kanan: Form Registrasi -->
    <div class="login-side">
      <div class="brand-header">
        <div class="brand-logo-form" title="TOOPAI"></div>
      </div>
      <div class="login-card">
        <h1>Create account</h1>
        <div class="login-sub">Start your creator journey with Toopai AI</div>
        
        <div id="errorMessage"></div>
        <div id="successMessage"></div>

        <form id="registerForm">
          <div class="field">
            <span class="fi">👤</span>
            <input type="text" id="username" placeholder="Username" autocomplete="username" required>
          </div>
          <div class="field">
            <span class="fi">✉️</span>
            <input type="email" id="email" placeholder="Email Address (optional)" autocomplete="email" required>
          </div>
          <div class="row-2col">
            <div class="field">
              <span class="fi">📞*</span>
              <input type="tel" id="phone" placeholder="Phone ">
            </div>
            <div class="field">
              <span class="fi">👤</span>
              <input type="text" id="full_name" placeholder="Full Name">
            </div>
          </div>
          <div class="field password-field">
            <span class="fi">🔒</span>
            <input type="password" id="password" placeholder="Password" autocomplete="new-password" required>
            <button type="button" class="eye-btn" onclick="togglePasswordField('password', this)">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 12s3.3-5 9-5 9 5 9 5-3.3 5-9 5-9-5-9-5Z"/><path d="M7 12h10" stroke-linecap="round"/></svg>
            </button>
          </div>
          <div class="field password-field">
            <span class="fi">🔒</span>
            <input type="password" id="confirm_password" placeholder="Confirm Password" autocomplete="new-password" required>
            <button type="button" class="eye-btn" onclick="togglePasswordField('confirm_password', this)">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 12s3.3-5 9-5 9 5 9 5-3.3 5-9 5-9-5-9-5Z"/><path d="M7 12h10" stroke-linecap="round"/></svg>
            </button>
          </div>
          <div class="field">
            <span class="fi">🎯</span>
            <select id="category" required>
              <option value="" disabled selected>Select creator category</option>
              <option value="Beauty">Beauty</option>
              <option value="Fashion">Fashion</option>
              <option value="Technology">Technology</option>
              <option value="Lifestyle">Lifestyle</option>
              <option value="Gaming">Gaming</option>
              <option value="Food">Food</option>
              <option value="Sports">Sports</option>
              <option value="Education">Education</option>
            </select>
          </div>
          <label class="agree" style="display:flex; align-items:center; gap:8px; margin:16px 0; font-size:12px; color:#c7d1ef;">
            <input type="checkbox" required style="width:16px;height:16px;"> I agree to the <button type="button" class="legal-link" onclick="openLegal()" style="background:none;border:none;color:#c49eff;cursor:pointer;">Terms & Privacy</button>
          </label>
          <button type="submit" class="btn-register" id="registerBtn">Register Now →</button>
        </form>

        <div class="divider">or sign up with</div>
        <div class="social-login">
          <a href="https://www.tiktok.com/signup" class="social-btn" target="_blank">
            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23ffffff'%3E%3Cpath d='M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z'/%3E%3C/svg%3E" style="width:18px;"> TikTok
          </a>
       
        </div>
        <div class="login-redirect">
          Already have an account? <button type="button" id="gotoLoginBtn">Login here →</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- CHAT WIDGET -->
<div class="toopai-chat-widget">
  <div class="chat-bubble" id="chatBubble">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
  </div>
  <div class="toopai-chat-panel" id="chatPanel">
    <div class="chat-header"><h4>TOOPAI Assistant</h4><button class="chat-close" id="chatClose">×</button></div>
    <div class="chat-messages" id="chatMessages"><div class="chat-message bot"><span>👋 Hi! Need help with registration? Ask me anything!</span></div></div>
    <div class="chat-input-area"><input type="text" id="chatInput" placeholder="Type your question..."><button id="chatSend">Send</button></div>
  </div>
</div>

<script>
  // Toggle password
  window.togglePasswordField = function(id, btn) {
    const input = document.getElementById(id);
    if (!input) return;
    const isPass = input.type === 'password';
    input.type = isPass ? 'text' : 'password';
    btn.innerHTML = isPass ? `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 12s3.3-5 9-5 9 5 9 5-3.3 5-9 5-9-5-9-5Z"/><circle cx="12" cy="12" r="2.7"/><circle cx="12" cy="12" r="1" fill="currentColor"/></svg>` : `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 12s3.3-5 9-5 9 5 9 5-3.3 5-9 5-9-5-9-5Z"/><path d="M7 12h10" stroke-linecap="round"/></svg>`;
  };

  // Preloader fade
  window.addEventListener('load', () => {
    const preloader = document.getElementById('toopaiPreloader');
    const container = document.getElementById('loginContainer');
    if (preloader) {
      preloader.classList.add('fade-out');
      setTimeout(() => { if(preloader) preloader.style.display = 'none'; }, 600);
    }
    if (container) container.classList.add('loaded');
  });

  // Chat widget logic
  const chatBubble = document.getElementById('chatBubble');
  const chatPanel = document.getElementById('chatPanel');
  const chatClose = document.getElementById('chatClose');
  if (chatBubble && chatPanel) {
    chatBubble.addEventListener('click', () => chatPanel.classList.add('active'));
    chatClose.addEventListener('click', () => chatPanel.classList.remove('active'));
  }

  function addMessage(text, isUser = false) {
    const container = document.getElementById('chatMessages');
    if (!container) return;
    const div = document.createElement('div');
    div.className = `chat-message ${isUser ? 'user' : 'bot'}`;
    div.innerHTML = `<span>${text}</span>`;
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
  }

  async function sendToAI(question) {
    try {
      const response = await fetch('<?= base_url("creator_auth/chat_ai") ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ message: question })
      });
      const data = await response.json();
      return data.reply || "I'm here to help! Make sure all fields are filled correctly.";
    } catch (error) {
      return "Having trouble connecting. Please fill in the form correctly.";
    }
  }

  const chatSend = document.getElementById('chatSend');
  const chatInput = document.getElementById('chatInput');
  if (chatSend && chatInput) {
    chatSend.addEventListener('click', async () => {
      const msg = chatInput.value.trim();
      if (!msg) return;
      addMessage(msg, true);
      chatInput.value = '';
      const reply = await sendToAI(msg);
      addMessage(reply);
    });
    chatInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') chatSend.click(); });
  }

  // Registration handler
  const registerForm = document.getElementById('registerForm');
  const registerBtn = document.getElementById('registerBtn');

  function showError(msg) {
    const err = document.getElementById('errorMessage');
    err.textContent = msg;
    err.classList.add('show');
    setTimeout(() => err.classList.remove('show'), 4500);
  }
  function showSuccess(msg) {
    const suc = document.getElementById('successMessage');
    suc.textContent = msg;
    suc.classList.add('show');
    setTimeout(() => suc.classList.remove('show'), 3000);
  }

  if (registerForm) {
    registerForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const username = document.getElementById('username').value.trim();
      const email = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value;
      const confirm = document.getElementById('confirm_password').value;
      const fullName = document.getElementById('full_name').value;
      const phone = document.getElementById('phone').value;
      const category = document.getElementById('category').value;

      if (!username || !email || !password) { showError('Username, email, and password are required'); return; }
      if (password !== confirm) { showError('Password and confirm password do not match'); return; }
      if (password.length < 6) { showError('Password must be at least 6 characters'); return; }
      if (!category) { showError('Please select a category'); return; }

      if (registerBtn) { registerBtn.classList.add('loading'); registerBtn.textContent = 'Processing...'; }

      try {
        const response = await fetch('<?= base_url("creator_auth/do_register") ?>', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({
            username: username, email: email, password: password,
            confirm_password: confirm, full_name: fullName, phone: phone, category: category
          })
        });
        const data = await response.json();
        if (data.success) {
          showSuccess(data.message || 'Registration successful! Redirecting...');
          setTimeout(() => { window.location.href = data.redirect || '<?= base_url("creator/dashboard") ?>'; }, 1500);
        } else {
          showError(data.message || 'Registration failed');
          if (registerBtn) { registerBtn.classList.remove('loading'); registerBtn.textContent = 'Register Now →'; }
        }
      } catch (err) {
        showError('Network error: ' + err.message);
        if (registerBtn) { registerBtn.classList.remove('loading'); registerBtn.textContent = 'Register Now →'; }
      }
    });
  }

  // redirects
  document.getElementById('gotoLoginBtn')?.addEventListener('click', () => { window.location.href = 'login'; });
  function openLegal() { alert('TOOPAI Terms & Privacy: Data is secured and used for creator-brand matching.'); }
  
  // override image paths from assets (logo & mascot)
  (function() {
    const logoDiv = document.querySelector('.brand-logo-form');
    const mascotImgs = document.querySelectorAll('.mascot-img, .preloader-mascot img');
    if (logoDiv) logoDiv.style.backgroundImage = "url('<?= base_url('assets/logo/new_logo_toopai_web.png') ?>')";
    mascotImgs.forEach(img => { if(img && !img.src.includes('data:')) img.src = '<?= base_url('assets/logo/toopai-mascot.png') ?>'; });
  })();
</script>
</body>
</html>