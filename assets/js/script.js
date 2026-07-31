const TOOPAI_DASHBOARD_URL='https://toopai.ai/creator/dashboard';function showProcessing(title='Processing account...',message='TOOPAI AI is preparing your creator access.'){const box=document.getElementById('processingScreen');if(!box)return;box.querySelector('h3').textContent=title;box.querySelector('p').textContent=message;const bar=box.querySelector('.progress-8bit span');if(bar){bar.style.animation='none';bar.offsetHeight;bar.style.animation='load8bit 1.25s steps(8,end) forwards'}box.classList.add('show')}function hideProcessing(){const box=document.getElementById('processingScreen');if(box)box.classList.remove('show')}function processRegister(event){event.preventDefault();const form=event.currentTarget;const password=document.getElementById('password');const confirm=document.getElementById('confirmPassword');if(password&&confirm&&password.value!==confirm.value){confirm.focus();alert('Confirm password harus sama dengan password.');return}const email=form.querySelector('input[type="email"]');if(email)localStorage.setItem('toopaiLastEmail',email.value);showProcessing('Registering creator...', 'Data creator sedang diproses. Setelah selesai kamu akan masuk ke halaman login.');setTimeout(()=>{hideProcessing();showLogin()},1300)}function goCreatorDashboard(event){event.preventDefault();showProcessing('Logging in...', 'Login berhasil. Mengarahkan ke TOOPAI Creator Dashboard.');setTimeout(()=>{window.location.href=TOOPAI_DASHBOARD_URL},900)}const app=document.getElementById('app');function setAuthState(state){app.classList.remove('auth-login','auth-forgot','auth-reset');if(state)app.classList.add(state);window.scrollTo({top:0,behavior:'smooth'})}function showLogin(){setAuthState('auth-login')}function showRegister(){setAuthState('')}function showForgot(){setAuthState('auth-forgot');const last=localStorage.getItem('toopaiLastEmail')||'';const field=document.getElementById('forgotEmail');if(field&&last)field.value=last}function showReset(){setAuthState('auth-reset')}document.querySelectorAll('.agree input').forEach(cb=>cb.addEventListener('change',e=>e.currentTarget.parentElement.style.color=e.currentTarget.checked?'#fff':'#c7d1ef'));
function sendResetDemo(e){e.preventDefault();const email=document.getElementById('forgotEmail').value.trim();if(!email)return;localStorage.setItem('toopaiLastEmail',email);const info=document.getElementById('forgotInfo');if(info)info.innerHTML='Reset password link has been sent to <b>'+email+'</b>. Demo frontend akan membuka halaman reset password.';setTimeout(showReset,850)}function saveNewPasswordDemo(e){e.preventDefault();const p=document.getElementById('newPassword').value,c=document.getElementById('confirmNewPassword').value;if(p!==c){alert('New password dan confirm password harus sama.');return}localStorage.setItem('toopaiPasswordUpdated','true');const ok=document.getElementById('resetSuccess');if(ok)ok.classList.add('show');setTimeout(showLogin,950)}
const eyeClosed=`<svg class="eye-closed" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 12s3.3-5 9-5 9 5 9 5-3.3 5-9 5-9-5-9-5Z" stroke="currentColor" stroke-width="1.8"/><path d="M7 12h10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>`;const eyeOpen=`<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 12s3.3-5 9-5 9 5 9 5-3.3 5-9 5-9-5-9-5Z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="2.6" stroke="currentColor" stroke-width="1.8"/></svg>`;function togglePassword(id,btn){const input=document.getElementById(id);if(!input)return;const show=input.type==='password';input.type=show?'text':'password';btn.innerHTML=show?eyeOpen:eyeClosed;btn.classList.toggle('is-open',show);btn.setAttribute('aria-label',show?'Hide password':'Show password');}
const passWordsA=['Nova','Pixel','Orbit','Creator','Toopai','Brand','Viral','Spark','Neon','Quantum','Aster','Lunar','Turbo','Cosmic','Cloud','Vector','Prism','Motion','Nexus','Echo','Aurora','Velvet','Falcon','River','Studio'];const passWordsB=['Tiger','Galaxy','Matrix','Canvas','Rocket','Lotus','Anchor','Comet','Sphere','Signal','Dragon','Panda','Monarch','Pulse','Kinetic','Creator','Brand','Horizon','Vision','Partner','Campaign','Insight','Studio','Flow'];function randInt(max){if(window.crypto&&crypto.getRandomValues){const a=new Uint32Array(1);crypto.getRandomValues(a);return a[0]%max}return Math.floor(Math.random()*max)}function makePassword(){const a=passWordsA[randInt(passWordsA.length)],b=passWordsB[randInt(passWordsB.length)],year=2026+randInt(9),num=10+randInt(89),sym=['!','@','#','$','%'][randInt(5)];return `${a}${b}${sym}${year}${num}`}
function showAiPasswords(){const pop=document.getElementById('aiPassPop'),list=document.getElementById('aiPassList');if(!pop||!list)return;const count=1+randInt(3);const set=new Set();while(set.size<count)set.add(makePassword());list.innerHTML=[...set].map(p=>`<button class="ai-pass-item" type="button" onclick="useAiPassword('${p}')">${p}</button>`).join('');pop.classList.add('show')}function hideAiPasswordsSoon(){setTimeout(()=>{const pop=document.getElementById('aiPassPop');if(pop&&!pop.matches(':hover'))pop.classList.remove('show')},220)}function useAiPassword(value){const p=document.getElementById('password'),c=document.getElementById('confirmPassword'),pop=document.getElementById('aiPassPop');if(p)p.value=value;if(c)c.value=value;if(pop)pop.classList.remove('show')}
const mainPass=document.getElementById('password');if(mainPass){mainPass.addEventListener('focus',showAiPasswords);mainPass.addEventListener('input',()=>{if(mainPass.value.length<2)showAiPasswords()});mainPass.addEventListener('blur',hideAiPasswordsSoon)}function openLegal(type){const modal=document.getElementById('legalPage'),title=document.getElementById('legalTitle'),body=document.getElementById('legalBody');if(!modal||!title||!body)return;title.textContent=type==='privacy'?'Privacy Policy':'Terms of Service';body.innerHTML=legalContent(type);modal.classList.add('show');}function closeLegal(){const modal=document.getElementById('legalPage');if(modal)modal.classList.remove('show')}function legalContent(type){if(type==='privacy')return `<span class="legal-badge">TOOPAI Data & Privacy Agreement</span><h3>English</h3><p>By using TOOPAI, you agree that TOOPAI may collect and process the information you submit for account registration, creator verification, campaign matching, communication, service improvement, and security purposes.</p><ul><li>TOOPAI will not disclose your personal information to unauthorized third parties.</li><li>TOOPAI does not know, read, or store your password in plain text.</li><li>You are responsible for keeping your email, account, and password secure.</li><li>Data may be used to help connect creators with suitable brands and campaigns.</li></ul><h3>Bahasa Indonesia</h3><p>Dengan menggunakan TOOPAI, Anda menyetujui bahwa TOOPAI dapat mengumpulkan dan memproses informasi yang Anda berikan untuk pendaftaran akun, verifikasi kreator, pencocokan campaign, komunikasi, peningkatan layanan, dan keamanan.</p><ul><li>TOOPAI tidak akan membocorkan informasi pribadi Anda kepada pihak ketiga yang tidak berwenang.</li><li>TOOPAI tidak mengetahui, membaca, atau menyimpan password Anda dalam bentuk asli.</li><li>Anda bertanggung jawab menjaga keamanan email, akun, dan password Anda.</li><li>Data dapat digunakan untuk membantu menghubungkan kreator dengan brand dan campaign yang sesuai.</li></ul>`;return `<span class="legal-badge">TOOPAI User Agreement</span><h3>English</h3><p>By creating an account, you agree to use TOOPAI responsibly and provide accurate information. You allow TOOPAI to process your registration and profile data to operate creator-brand matching, campaign access, verification, analytics, and user support.</p><ul><li>You may not misuse the platform, impersonate others, or submit false information.</li><li>TOOPAI may review account activity to protect creators, brands, and the platform.</li><li>TOOPAI will protect account credentials and will not know your actual password.</li><li>Continued use of the service means you accept these terms.</li></ul><h3>Bahasa Indonesia</h3><p>Dengan membuat akun, Anda setuju menggunakan TOOPAI secara bertanggung jawab dan memberikan informasi yang benar. Anda mengizinkan TOOPAI memproses data pendaftaran dan profil untuk menjalankan pencocokan kreator-brand, akses campaign, verifikasi, analitik, dan dukungan pengguna.</p><ul><li>Anda tidak boleh menyalahgunakan platform, meniru identitas orang lain, atau mengirim informasi palsu.</li><li>TOOPAI dapat meninjau aktivitas akun untuk melindungi kreator, brand, dan platform.</li><li>TOOPAI akan melindungi kredensial akun dan tidak mengetahui password asli Anda.</li><li>Penggunaan layanan secara berkelanjutan berarti Anda menerima ketentuan ini.</li></ul>`}



(function(){
  const oldShowLogin = window.showLogin;
  function esc(v){return String(v||'').replace(/[&<>'"]/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));}
  window.openEmailConfirm = function(email, mode){
    const box=document.getElementById('emailConfirmScreen');
    if(!box) return;
    const safe=esc(email || localStorage.getItem('toopaiLastEmail') || 'creator@email.com');
    document.getElementById('emailTarget').innerHTML=safe;
    document.getElementById('emailConfirmTitle').textContent = mode==='reset' ? 'Reset link sent by TOOPAI' : 'Confirm your TOOPAI email';
    document.getElementById('emailConfirmText').textContent = mode==='reset' ? 'Link reset password dikirim ke email ini. Di prototype ini kamu bisa lanjut langsung ke halaman reset password.' : 'TOOPAI mengirim email konfirmasi ke alamat ini. Klik confirm untuk aktivasi akun lalu masuk ke halaman login.';
    document.getElementById('mailPreview').innerHTML = mode==='reset' ? '<b>From:</b> TOOPAI Security<br><b>Subject:</b> Reset your TOOPAI password<br><br>Use this secure link to create a new password.' : '<b>From:</b> TOOPAI Creator Team<br><b>Subject:</b> Confirm your TOOPAI Creator account<br><br>Welcome to TOOPAI. Confirm your email to activate your creator account.';
    box.dataset.mode=mode||'register';
    box.classList.add('show');
  };
  window.closeEmailConfirm=function(){const box=document.getElementById('emailConfirmScreen'); if(box) box.classList.remove('show');};
  window.confirmEmailAndLogin=function(){
    const box=document.getElementById('emailConfirmScreen');
    const mode=box?.dataset.mode;
    closeEmailConfirm();
    if(mode==='reset'){ showReset(); return; }
    localStorage.setItem('toopaiEmailConfirmed','true');
    showProcessing('Email confirmed!', 'Akun TOOPAI aktif. Mengarahkan ke menu login.');
    setTimeout(()=>{hideProcessing(); oldShowLogin ? oldShowLogin() : showLogin();},900);
  };
  window.processRegister=function(event){
    event.preventDefault();
    const form=event.currentTarget;
    const password=document.getElementById('password');
    const confirm=document.getElementById('confirmPassword');
    if(password&&confirm&&password.value!==confirm.value){confirm.focus();alert('Confirm password harus sama dengan password.');return;}
    const email=form.querySelector('input[type="email"]');
    const emailValue=email ? email.value.trim() : '';
    localStorage.setItem('toopaiLastEmail',emailValue);
    showProcessing('Registering creator...', 'Membuat akun dan menyiapkan email konfirmasi TOOPAI.');
    setTimeout(()=>{hideProcessing(); openEmailConfirm(emailValue,'register');},1100);
  };
  window.sendResetDemo=function(e){
    e.preventDefault();
    const email=document.getElementById('forgotEmail').value.trim();
    if(!email)return;
    localStorage.setItem('toopaiLastEmail',email);
    const info=document.getElementById('forgotInfo');
    if(info)info.innerHTML='TOOPAI reset password email prepared for <b>'+esc(email)+'</b>.';
    showProcessing('Sending reset link...', 'TOOPAI Security sedang menyiapkan email reset password.');
    setTimeout(()=>{hideProcessing(); openEmailConfirm(email,'reset');},900);
  };
  window.continueWithGoogleAuth=function(mode){
    const fakeEmail='google.creator@toopai.demo';
    localStorage.setItem('toopaiLastEmail',fakeEmail);
    showProcessing(mode==='login'?'Connecting Google login...':'Connecting Google signup...', 'Menghubungkan akun Google dengan TOOPAI.');
    setTimeout(()=>{hideProcessing(); mode==='login' ? window.location.href=TOOPAI_DASHBOARD_URL : openEmailConfirm(fakeEmail,'register');},950);
  };
  document.addEventListener('DOMContentLoaded', function(){
    const registerForm=document.querySelector('form[onsubmit="processRegister(event)"]');
    const loginForm=document.querySelector('form[onsubmit="goCreatorDashboard(event)"]');
    if(registerForm && !registerForm.querySelector('.google-auth-btn')){
      const split=registerForm.querySelector('.split');
      const btn=document.createElement('button'); btn.type='button'; btn.className='google-auth-btn'; btn.textContent='Continue with Google + Email Confirm'; btn.onclick=()=>continueWithGoogleAuth('register');
      split?.insertAdjacentElement('afterend', btn);
    }
    if(loginForm && !loginForm.querySelector('.google-auth-btn')){
      const split=loginForm.querySelector('.split');
      const btn=document.createElement('button'); btn.type='button'; btn.className='google-auth-btn'; btn.textContent='Continue with Google'; btn.onclick=()=>continueWithGoogleAuth('login');
      split?.insertAdjacentElement('beforebegin', btn);
    }
  });
})();



(function(){
  const DASH_URL = window.TOOPAI_DASHBOARD_URL || 'https://toopai.ai/creator/dashboard';
  const loader = document.getElementById('toopaiDashboardLoader');
  const pass = document.getElementById('password');
  const pop = document.getElementById('aiPassPop');

  function hideAiPasswordPopupPermanently(){
    document.body.classList.add('password-ai-disabled');
    if(pop){ pop.classList.remove('show'); pop.classList.add('manual-hidden'); }
  }

  if(pass){
    pass.addEventListener('input', function(){
      if(this.value.trim().length > 0){
        hideAiPasswordPopupPermanently();
        setTimeout(hideAiPasswordPopupPermanently, 0);
        setTimeout(hideAiPasswordPopupPermanently, 80);
      }
    }, true);
  }

  window.startToopaiDashboardLoading = function(){
    const ms = Math.floor(1000 + Math.random() * 4000);
    if(loader){
      loader.style.setProperty('--load-time', ms + 'ms');
      const bar = loader.querySelector('.toopai-loader-bar span');
      if(bar){ bar.style.animation='none'; bar.offsetHeight; bar.style.animation='dashLoad '+ms+'ms steps(12,end) forwards'; }
      loader.classList.add('show');
    }
    setTimeout(function(){ window.location.href = DASH_URL; }, ms);
  };

  window.goCreatorDashboard = function(event){
    if(event) event.preventDefault();
    window.startToopaiDashboardLoading();
  };

  window.continueWithGoogleAuth = function(mode){
    const fakeEmail='creator.google@toopai.ai';
    if(mode==='login'){
      window.startToopaiDashboardLoading();
      return;
    }
    if(typeof showProcessing==='function') showProcessing('Connecting Google signup...', 'Menghubungkan akun Google dengan TOOPAI.');
    setTimeout(function(){
      if(typeof hideProcessing==='function') hideProcessing();
      if(typeof openEmailConfirm==='function') openEmailConfirm(fakeEmail,'register');
    },950);
  };
})();


/* ===== EXTRA PASSWORD EYE SUPPORT ===== */
document.addEventListener("click", function(e){
  const btn = e.target.closest(".password-toggle, .eye, .toggle-password, [data-toggle-password]");
  if(!btn) return;

  const targetId = btn.getAttribute("data-input") || btn.getAttribute("data-target") || btn.getAttribute("data-toggle-password");
  let input = targetId ? document.getElementById(targetId) : null;

  if(!input){
    const wrap = btn.closest(".password-wrap, .input-wrap, .field, label, div");
    input = wrap ? wrap.querySelector('input[type="password"], input[type="text"]') : null;
  }

  if(input && (input.type === "password" || input.type === "text")){
    input.type = input.type === "password" ? "text" : "password";
    btn.classList.toggle("is-visible", input.type === "text");
  }
});


/* ===== FINAL FIX: PASSWORD SHOW / HIDE, CAPTURE MODE ===== */
document.addEventListener("click", function(event){
  const btn = event.target.closest(".toopai-eye-toggle");
  if(!btn) return;

  event.preventDefault();
  event.stopPropagation();
  event.stopImmediatePropagation();

  const targetId = btn.getAttribute("data-password-target");
  const input = document.getElementById(targetId);

  if(!input) return;

  const willShow = input.type === "password";
  input.type = willShow ? "text" : "password";
  input.setAttribute("data-toopai-password", "true");
  btn.classList.toggle("is-visible", willShow);
  btn.textContent = willShow ? "🙈" : "👁";
  btn.setAttribute("aria-label", willShow ? "Hide password" : "Show password");
}, true);

/* remove google actions */
document.querySelectorAll(".google,.google-btn,.btn-google,[data-provider='google'],[id*='google' i],[class*='google' i]").forEach(el => el.remove());
