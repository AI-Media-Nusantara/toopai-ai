/* Script block 1  */
const agents=[{num:"01",title:"Content Strategist Agent",desc:"AI that analyzes trends, creates content calendar, and generates viral hooks tailored for your niche.",list:["Predictive content planning","SEO & Hashtag intelligence","Viral hook generator"],mockTitle:'Content Plan: "Viral Hooks Pack"',rows:['🔥 "3 AI tools that doubled my engagement in 7 days"',"📈 The psychology behind purple & gold in thumbnails",'💡 "Why your content is not going viral"'],btn:"Generate Strategy →"},{num:"02",title:"Image Creator Agent",desc:"Generate branded thumbnails, campaign visuals, and product images with a clean futuristic look.",list:["Brand-safe image generation","Thumbnail style system","Campaign visual packs"],mockTitle:"Visual Campaign Preview",rows:["🎨 Purple neon thumbnail concept","🖼 Product hero image variation","✨ Social post layout ready"],btn:"Generate Visuals →"},{num:"03",title:"Video Editing Agent",desc:"Auto-sync beats, dynamic captions, and B-roll suggestions — all in one click.",list:["AI-powered auto-editing","Smart captions & subtitles","B-roll suggestion engine"],mockTitle:"AI Video Editor Mockup",rows:["🎬 Auto cut synced to music","💬 Captions generated in seconds","⚡ Export reels, shorts, TikTok"],btn:"Edit Video →"},{num:"04",title:"Social Manager Agent",desc:"Plan, schedule, and optimize social content across platforms with smart timing.",list:["Content calendar automation","Best posting time","Engagement optimization"],mockTitle:"Social Calendar",rows:["📅 Monday: Hook video","📌 Wednesday: Brand campaign","📈 Friday: Performance recap"],btn:"Schedule Content →"},{num:"05",title:"Translator Agent",desc:"Break language barriers and localize campaign content for global audiences.",list:["Multi-language campaign copy","Tone-safe translation","Regional content adaptation"],mockTitle:"Localization Pack",rows:["🌍 English to Indonesian","🗣 Brand voice preserved","🧠 Cultural nuance detected"],btn:"Translate Campaign →"},{num:"06",title:"Marketing Automation Agent",desc:"Intelligent campaign optimization, cross-channel orchestration, and real-time budget allocation.",list:["Cross-channel automation","Smart budget allocation","Real-time performance tracking"],mockTitle:"Campaign Analytics",rows:["📊 Impressions: 1.2M","🚀 ROAS: 4.6×","🎯 Optimization Score: 96%"],btn:"Optimize Campaign →"}];
function showAgent(index){const a=agents[index];document.getElementById("agent-num").textContent=a.num;document.getElementById("agent-title").textContent=a.title;document.getElementById("agent-desc").textContent=a.desc;document.getElementById("agent-list").innerHTML=a.list.map(item=>`<li>${item}</li>`).join("");document.getElementById("mock-title").textContent=a.mockTitle;document.getElementById("mock-1").textContent=a.rows[0];document.getElementById("mock-2").textContent=a.rows[1];document.getElementById("mock-3").textContent=a.rows[2];document.getElementById("mock-btn").textContent=a.btn;document.querySelectorAll(".agent-card").forEach((card,i)=>card.classList.toggle("active",i===index));const resultBox=document.getElementById("strategy-result"); if(resultBox){resultBox.classList.remove("show"); resultBox.textContent="";} document.getElementById("agent-detail").scrollIntoView({behavior:"smooth",block:"center"});}
const counters=document.querySelectorAll(".counter");let started=false;function runCounters(){if(started)return;started=true;counters.forEach(counter=>{const target=+counter.dataset.target;const duration=1800;const start=performance.now();function update(now){const progress=Math.min((now-start)/duration,1);const value=Math.floor(progress*target);counter.textContent=value.toLocaleString("en-US");if(progress<1)requestAnimationFrame(update);}requestAnimationFrame(update);});}
const observer=new IntersectionObserver(entries=>{entries.forEach(e=>{if(e.isIntersecting)runCounters();})},{threshold:.4});observer.observe(document.querySelector(".stats"));

/* Script block 2  */
function generateStrategy(){
  const result = document.getElementById("strategy-result");
  const title = document.getElementById("agent-title")?.textContent || "AI Agent";
  const outputs = {
    "Content Strategist Agent": "✅ Strategy generated: 7-day content calendar, 12 viral hooks, SEO hashtags, and campaign angle are ready.",
    "Image Creator Agent": "✅ Visual pack generated: 5 thumbnail concepts, 3 campaign hero images, and brand-safe color direction are ready.",
    "Video Editing Agent": "✅ Video workflow generated: auto-cut timeline, captions, hook opening, and B-roll suggestions are ready.",
    "Social Manager Agent": "✅ Social plan generated: posting schedule, best time slots, captions, and engagement prompts are ready.",
    "Translator Agent": "✅ Localization generated: translated campaign copy with brand tone and cultural nuance preserved.",
    "Marketing Automation Agent": "✅ Campaign optimization generated: budget allocation, ROAS prediction, and audience targeting are ready."
  };
  result.textContent = outputs[title] || "✅ AI output generated successfully.";
  result.classList.add("show");
}

/* Script block 3  */
(function(){
  const navLinks = document.querySelector(".nav-links") || document.querySelector(".navlinks");
  if(!navLinks) return;

  const links = Array.from(navLinks.querySelectorAll("a"));
  if(!links.length) return;

  const toggle = document.createElement("span");
  toggle.className = "liquid-toggle";
  navLinks.prepend(toggle);

  function moveTo(link, animate=true){
    const parentRect = navLinks.getBoundingClientRect();
    const rect = link.getBoundingClientRect();
    toggle.style.left = (rect.left - parentRect.left - 8) + "px";
    toggle.style.width = (rect.width + 16) + "px";
    toggle.style.top = (rect.top - parentRect.top + rect.height/2 - 26) + "px";

    links.forEach(a => a.classList.remove("active"));
    link.classList.add("active");

    if(animate){
      toggle.classList.remove("pop");
      void toggle.offsetWidth;
      toggle.classList.add("pop");
    }
  }

  links.forEach(link => {
    link.addEventListener("click", () => moveTo(link));
    link.addEventListener("mouseenter", () => moveTo(link, false));
  });

  navLinks.addEventListener("mouseleave", () => {
    const active = links.find(a => a.classList.contains("active")) || links[0];
    moveTo(active, false);
  });

  window.addEventListener("resize", () => {
    const active = links.find(a => a.classList.contains("active")) || links[0];
    moveTo(active, false);
  });

  setTimeout(() => moveTo(links[0], false), 120);
})();

/* Script block 4  */
(function(){
  const orb = document.getElementById("cursorOrb");
  const dot = document.getElementById("cursorDot");
  let x = window.innerWidth / 2, y = window.innerHeight / 2;
  let ox = x, oy = y, dx = x, dy = y;

  window.addEventListener("mousemove", (e) => {
    x = e.clientX;
    y = e.clientY;
    if(orb){ orb.style.opacity = "1"; }
    if(dot){ dot.style.opacity = "1"; }
  });

  window.addEventListener("scroll", () => {
    if(orb){ orb.style.opacity = "1"; }
    setTimeout(() => { if(orb) orb.style.opacity = ".72"; }, 160);
  }, { passive:true });

  function animateCursor(){
    ox += (x - ox) * 0.10;
    oy += (y - oy) * 0.10;
    dx += (x - dx) * 0.34;
    dy += (y - dy) * 0.34;

    if(orb){
      orb.style.left = ox + "px";
      orb.style.top = oy + "px";
    }
    if(dot){
      dot.style.left = dx + "px";
      dot.style.top = dy + "px";
    }
    requestAnimationFrame(animateCursor);
  }
  animateCursor();

  const widget = document.getElementById("toopaiChat");
  const toggle = document.getElementById("chatToggle");
  const input = document.getElementById("chatInput");
  const send = document.getElementById("chatSend");
  const body = document.getElementById("chatBody");

  if(toggle && widget){
    toggle.addEventListener("click", () => widget.classList.toggle("open"));
  }

  function botAnswer(question){
    const q = question.toLowerCase();
    if(q.includes("harga") || q.includes("pricing") || q.includes("pro")){
      return "Creator Pro mulai dari Rp 99.000/bulan. Cocok untuk creator yang ingin scale konten dengan AI agents.";
    }
    if(q.includes("agent") || q.includes("pilih")){
      return "Untuk konten harian pilih Content Strategist. Untuk short video pilih Video Editing Agent. Untuk brand campaign pilih Marketing Automation.";
    }
    if(q.includes("brand") || q.includes("campaign")){
      return "TOOPAI membantu brand menemukan creator, membuat campaign brief, memantau performa, dan mengoptimalkan konten.";
    }
    if(q.includes("toopai")){
      return "TOOPAI adalah AI ecosystem yang menghubungkan creator dan brand lewat agent untuk content, video, analytics, matching, dan campaign.";
    }
    return "Bisa! TOOPAI bisa bantu ide konten, pilih agent, strategi campaign, video editing, dan pricing. Coba tanya lebih spesifik.";
  }

  function addMessage(text, type){
    const bubble = document.createElement("div");
    bubble.className = "chat-bubble " + type;
    bubble.textContent = text;
    body.appendChild(bubble);
    body.scrollTop = body.scrollHeight;
  }

  function sendMessage(text){
    const val = (text || input.value || "").trim();
    if(!val) return;
    addMessage(val, "user");
    if(input) input.value = "";
    setTimeout(() => addMessage(botAnswer(val), "bot"), 450);
  }

  if(send) send.addEventListener("click", () => sendMessage());
  if(input) input.addEventListener("keydown", e => {
    if(e.key === "Enter") sendMessage();
  });

  document.querySelectorAll(".quick-asks button").forEach(btn => {
    btn.addEventListener("click", () => sendMessage(btn.dataset.q));
  });
})();

/* Script block 5  */
(function(){
  const toggle = document.getElementById("themeToggle");
  if(!toggle) return;

  const saved = localStorage.getItem("toopai-theme");
  if(saved === "light"){
    document.body.classList.add("light-mode");
    toggle.textContent = "☾";
  }

  toggle.addEventListener("click", () => {
    document.body.classList.toggle("light-mode");
    const isLight = document.body.classList.contains("light-mode");
    toggle.textContent = isLight ? "☾" : "☀";
    localStorage.setItem("toopai-theme", isLight ? "light" : "dark");
  });
})();

/* Script block 6  */
(function(){
  const translations = {
    id: {
      "Product":"Product","Agents":"Agents","Solutions":"Solutions","Case Study":"Case Study","Log In":"Log In","Sign Up":"Sign Up",
      "Get Started →":"Mulai →","⊙ Watch Demo":"⊙ Lihat Demo","Explore Agents →":"Jelajahi Agents →","Generate Strategy →":"Buat Strategi →",
      "AI Agents for":"AI Agents untuk","Creators &":"Creator &","Brands":"Brand",
      "AI Agents for Creators & Brands":"AI Agents untuk Creator & Brand",
      "Build content faster. Grow smarter. Scale automatically.":"Buat konten lebih cepat. Tumbuh lebih cerdas. Scale otomatis.",
      "Projects":"Proyek","PROJECTS":"PROYEK","BRANDS":"BRAND","AI GENERATED":"AI DIHASILKAN",
      "TRUSTED BY INNOVATORS":"DIPERCAYA OLEH INOVATOR",
      "CONNECTED AI SYSTEM":"SISTEM AI TERHUBUNG","TOOPAI connects every agent":"TOOPAI menghubungkan setiap agent",
      "6 specialized agents working in sync for your brand":"6 agent khusus yang bekerja sinkron untuk brand kamu",
      "POWERFUL AI AGENTS":"AI AGENTS YANG KUAT","An AI Agent for every":"AI Agent untuk setiap","need":"kebutuhan",
      "Specialized AI agents that work together to bring your ideas to life, from campaign planning to final content delivery.":"AI agent khusus yang bekerja bersama untuk mewujudkan ide kamu, dari perencanaan campaign sampai pengiriman konten final.",
      "Content Writer":"Content Writer","Image Creator":"Image Creator","Video Producer":"Video Producer","Social Manager":"Social Manager","Translator":"Translator","Marketing AI":"Marketing AI",
      "Create blogs, scripts, and engaging content in seconds.":"Buat blog, script, dan konten menarik dalam hitungan detik.",
      "Generate stunning brand visuals for every campaign.":"Buat visual brand yang menarik untuk setiap campaign.",
      "Create videos, reels, shorts, and social content.":"Buat video, reels, shorts, dan konten sosial.",
      "Plan, schedule, and optimize your social media.":"Rencanakan, jadwalkan, dan optimalkan media sosial kamu.",
      "Break language barriers and reach global audiences.":"Tembus batas bahasa dan jangkau audiens global.",
      "Automate budget, optimization, and campaign insight.":"Otomatisasi budget, optimasi, dan insight campaign.",
      "Content Strategist Agent":"Content Strategist Agent",
      "AI that analyzes trends, creates content calendar, and generates viral hooks tailored for your niche.":"AI yang menganalisis tren, membuat kalender konten, dan menghasilkan hook viral sesuai niche kamu.",
      "Predictive content planning":"Perencanaan konten prediktif","SEO & Hashtag intelligence":"SEO & kecerdasan hashtag","Viral hook generator":"Generator hook viral",
      "Video Editing Agent":"Video Editing Agent","Simple TikTok-style editor for reels, shorts, captions, music sync, and B-roll suggestions.":"Editor sederhana bergaya TikTok untuk reels, shorts, caption, sinkron musik, dan saran B-roll.",
      "Auto-cut video highlight":"Auto-cut highlight video","Smart captions & subtitles":"Caption & subtitle pintar","Music sync and hook preview":"Sinkron musik dan preview hook","Try Video Agent →":"Coba Video Agent →",
      "WHY TOOPAI?":"KENAPA TOOPAI?","Built for the New Creative Era":"Dibuat untuk Era Kreatif Baru","Faster Workflow":"Workflow Lebih Cepat","AI Collaboration":"Kolaborasi AI","Enterprise Security":"Keamanan Enterprise",
      "Solutions for Every Need":"Solusi untuk Setiap Kebutuhan","Creators":"Creator","Agencies":"Agency","E-Commerce":"E-Commerce",
      "Real Results, Real Impact":"Hasil Nyata, Dampak Nyata","What":"Apa Kata","Creators Say":"Creator","Frequently Asked":"Pertanyaan yang Sering","Questions":"Ditanyakan",
      "What is Toopai?":"Apa itu Toopai?","How does the AI agent work?":"Bagaimana cara kerja AI agent?","Is there a free trial?":"Apakah ada free trial?","Can I cancel anytime?":"Bisa cancel kapan saja?",
      "PRICING":"HARGA","Choose your AI workflow":"Pilih workflow AI kamu","Starter":"Starter","Free":"Gratis","Creator Pro":"Creator Pro","Brand":"Brand","Custom":"Custom",
      "Untuk coba fitur dasar Toopai.":"Untuk coba fitur dasar Toopai.","Untuk creator yang ingin scale konten.":"Untuk creator yang ingin scale konten.","Untuk brand dan agency.":"Untuk brand dan agency.",
      "Start Free":"Mulai Gratis","Get Pro":"Ambil Pro","Contact Sales":"Hubungi Sales",
      "READY TO BUILD THE FUTURE?":"SIAP MEMBANGUN MASA DEPAN?","Build Smarter":"Build Lebih Cerdas","with":"dengan","Toopai":"Toopai","Book Demo →":"Book Demo →",
      "Ask TOOPAI":"Tanya TOOPAI","Ask about AI agents, campaign, content, or pricing.":"Tanya tentang AI agent, campaign, konten, atau pricing.","Ask TOOPAI...":"Tanya TOOPAI..."
    },
    en: {
      "Mulai →":"Get Started →","Lihat Demo":"Watch Demo","AI Agents untuk":"AI Agents for","Creator &":"Creators &","Brand":"Brands"
    },
    zh: {
      "Product":"产品","Agents":"智能体","Solutions":"解决方案","Case Study":"案例","Log In":"登录","Sign Up":"注册",
      "Get Started →":"开始使用 →","⊙ Watch Demo":"⊙ 观看演示","Explore Agents →":"探索智能体 →","Generate Strategy →":"生成策略 →",
      "AI Agents for":"面向","Creators &":"创作者与","Brands":"品牌的 AI 智能体",
      "AI Agents for Creators & Brands":"面向创作者与品牌的 AI 智能体",
      "Build content faster. Grow smarter. Scale automatically.":"更快创作内容，更智能增长，自动化规模扩展。",
      "PROJECTS":"项目","BRANDS":"品牌","AI GENERATED":"AI 生成",
      "TRUSTED BY INNOVATORS":"受到创新者信赖",
      "CONNECTED AI SYSTEM":"互联 AI 系统","TOOPAI connects every agent":"TOOPAI 连接每一个智能体",
      "6 specialized agents working in sync for your brand":"6 个专业智能体为你的品牌协同工作",
      "POWERFUL AI AGENTS":"强大的 AI 智能体","An AI Agent for every":"每个需求都有","need":"AI 智能体",
      "Specialized AI agents that work together to bring your ideas to life, from campaign planning to final content delivery.":"专业 AI 智能体协同工作，从活动规划到最终内容交付，让你的创意落地。",
      "Content Writer":"内容写作","Image Creator":"图像创作","Video Producer":"视频制作","Social Manager":"社媒管理","Translator":"翻译","Marketing AI":"营销 AI",
      "Content Strategist Agent":"内容策略智能体","Video Editing Agent":"视频编辑智能体",
      "WHY TOOPAI?":"为什么选择 TOOPAI？","Built for the New Creative Era":"为新创意时代打造","Solutions for Every Need":"满足各种需求的解决方案",
      "Real Results, Real Impact":"真实结果，真实影响","Frequently Asked":"常见","Questions":"问题",
      "PRICING":"价格","Choose your AI workflow":"选择你的 AI 工作流","Starter":"入门版","Free":"免费","Creator Pro":"创作者 Pro","Brand":"品牌版","Custom":"定制",
      "Start Free":"免费开始","Get Pro":"获取 Pro","Contact Sales":"联系销售",
      "READY TO BUILD THE FUTURE?":"准备好构建未来了吗？","Build Smarter":"更智能地构建","with":"使用","Ask TOOPAI":"询问 TOOPAI","Ask TOOPAI...":"询问 TOOPAI..."
    },
    th: {
      "Product":"สินค้า","Agents":"เอเจนต์","Solutions":"โซลูชัน","Case Study":"เคสศึกษา","Log In":"เข้าสู่ระบบ","Sign Up":"สมัคร",
      "Get Started →":"เริ่มเลย →","⊙ Watch Demo":"⊙ ดูเดโม","Explore Agents →":"สำรวจเอเจนต์ →","Generate Strategy →":"สร้างกลยุทธ์ →",
      "AI Agents for":"AI Agents สำหรับ","Creators &":"ครีเอเตอร์และ","Brands":"แบรนด์",
      "Build content faster. Grow smarter. Scale automatically.":"สร้างคอนเทนต์เร็วขึ้น เติบโตฉลาดขึ้น และสเกลอัตโนมัติ",
      "PROJECTS":"โปรเจกต์","BRANDS":"แบรนด์","AI GENERATED":"สร้างด้วย AI",
      "TRUSTED BY INNOVATORS":"ได้รับความไว้วางใจจากนักนวัตกรรม",
      "CONNECTED AI SYSTEM":"ระบบ AI ที่เชื่อมต่อ","TOOPAI connects every agent":"TOOPAI เชื่อมต่อทุกเอเจนต์",
      "6 specialized agents working in sync for your brand":"6 เอเจนต์เฉพาะทางที่ทำงานร่วมกันเพื่อแบรนด์ของคุณ",
      "Content Writer":"นักเขียนคอนเทนต์","Image Creator":"สร้างภาพ","Video Producer":"ผลิตวิดีโอ","Social Manager":"จัดการโซเชียล","Translator":"นักแปล","Marketing AI":"AI การตลาด",
      "Content Strategist Agent":"เอเจนต์วางกลยุทธ์คอนเทนต์","Video Editing Agent":"เอเจนต์ตัดต่อวิดีโอ",
      "WHY TOOPAI?":"ทำไมต้อง TOOPAI?","Built for the New Creative Era":"สร้างมาเพื่อยุคครีเอทีฟใหม่","Solutions for Every Need":"โซลูชันสำหรับทุกความต้องการ",
      "Real Results, Real Impact":"ผลลัพธ์จริง ผลกระทบจริง","Frequently Asked":"คำถามที่พบบ่อย","Questions":"",
      "PRICING":"ราคา","Choose your AI workflow":"เลือกเวิร์กโฟลว์ AI ของคุณ","Starter":"เริ่มต้น","Free":"ฟรี","Creator Pro":"Creator Pro","Brand":"แบรนด์","Custom":"กำหนดเอง",
      "Start Free":"เริ่มฟรี","Get Pro":"ใช้ Pro","Contact Sales":"ติดต่อฝ่ายขาย",
      "READY TO BUILD THE FUTURE?":"พร้อมสร้างอนาคตหรือยัง?","Build Smarter":"สร้างอย่างฉลาดขึ้น","with":"กับ","Ask TOOPAI":"ถาม TOOPAI","Ask TOOPAI...":"ถาม TOOPAI..."
    },
    ja: {
      "Product":"プロダクト","Agents":"エージェント","Solutions":"ソリューション","Case Study":"導入事例","Log In":"ログイン","Sign Up":"登録",
      "Get Started →":"はじめる →","⊙ Watch Demo":"⊙ デモを見る","Explore Agents →":"エージェントを見る →","Generate Strategy →":"戦略を生成 →",
      "AI Agents for":"クリエイターと","Creators &":"ブランドのための","Brands":"AIエージェント",
      "Build content faster. Grow smarter. Scale automatically.":"より速く制作し、賢く成長し、自動でスケール。",
      "PROJECTS":"プロジェクト","BRANDS":"ブランド","AI GENERATED":"AI生成",
      "TRUSTED BY INNOVATORS":"イノベーターに信頼されています",
      "CONNECTED AI SYSTEM":"接続されたAIシステム","TOOPAI connects every agent":"TOOPAIがすべてのエージェントを接続",
      "6 specialized agents working in sync for your brand":"6つの専門エージェントがブランドのために連携",
      "Content Writer":"コンテンツ作成","Image Creator":"画像生成","Video Producer":"動画制作","Social Manager":"SNS管理","Translator":"翻訳","Marketing AI":"マーケティングAI",
      "Content Strategist Agent":"コンテンツ戦略エージェント","Video Editing Agent":"動画編集エージェント",
      "WHY TOOPAI?":"なぜTOOPAI？","Built for the New Creative Era":"新しいクリエイティブ時代のために","Solutions for Every Need":"あらゆるニーズに対応",
      "Real Results, Real Impact":"リアルな成果、リアルな影響","Frequently Asked":"よくある","Questions":"質問",
      "PRICING":"料金","Choose your AI workflow":"AIワークフローを選択","Starter":"スターター","Free":"無料","Creator Pro":"Creator Pro","Brand":"ブランド","Custom":"カスタム",
      "Start Free":"無料で開始","Get Pro":"Proを使う","Contact Sales":"営業に相談",
      "READY TO BUILD THE FUTURE?":"未来を作る準備はできましたか？","Build Smarter":"より賢く構築","with":"with","Ask TOOPAI":"TOOPAIに質問","Ask TOOPAI...":"TOOPAIに質問..."
    },
    ko: {
      "Product":"제품","Agents":"에이전트","Solutions":"솔루션","Case Study":"사례","Log In":"로그인","Sign Up":"가입",
      "Get Started →":"시작하기 →","⊙ Watch Demo":"⊙ 데모 보기","Explore Agents →":"에이전트 탐색 →","Generate Strategy →":"전략 생성 →",
      "AI Agents for":"크리에이터와","Creators &":"브랜드를 위한","Brands":"AI 에이전트",
      "Build content faster. Grow smarter. Scale automatically.":"콘텐츠를 더 빠르게 만들고, 더 똑똑하게 성장하며, 자동으로 확장하세요.",
      "PROJECTS":"프로젝트","BRANDS":"브랜드","AI GENERATED":"AI 생성",
      "TRUSTED BY INNOVATORS":"혁신가들이 신뢰합니다",
      "CONNECTED AI SYSTEM":"연결된 AI 시스템","TOOPAI connects every agent":"TOOPAI가 모든 에이전트를 연결합니다",
      "6 specialized agents working in sync for your brand":"브랜드를 위해 6개의 전문 에이전트가 함께 작동합니다",
      "Content Writer":"콘텐츠 작성","Image Creator":"이미지 생성","Video Producer":"영상 제작","Social Manager":"소셜 관리","Translator":"번역","Marketing AI":"마케팅 AI",
      "Content Strategist Agent":"콘텐츠 전략 에이전트","Video Editing Agent":"영상 편집 에이전트",
      "WHY TOOPAI?":"왜 TOOPAI인가요?","Built for the New Creative Era":"새로운 크리에이티브 시대를 위해","Solutions for Every Need":"모든 니즈를 위한 솔루션",
      "Real Results, Real Impact":"실제 결과, 실제 영향","Frequently Asked":"자주 묻는","Questions":"질문",
      "PRICING":"가격","Choose your AI workflow":"AI 워크플로우 선택","Starter":"스타터","Free":"무료","Creator Pro":"Creator Pro","Brand":"브랜드","Custom":"맞춤형",
      "Start Free":"무료 시작","Get Pro":"Pro 시작","Contact Sales":"영업 문의",
      "READY TO BUILD THE FUTURE?":"미래를 만들 준비가 되셨나요?","Build Smarter":"더 스마트하게 구축","with":"with","Ask TOOPAI":"TOOPAI에게 질문","Ask TOOPAI...":"TOOPAI에게 질문..."
    }
  };

  const originalText = new WeakMap();
  const originalPlaceholder = new WeakMap();

  function normalize(text){
    return (text || "").replace(/\s+/g, " ").trim();
  }

  function translateNode(node, lang){
    if(!node.childNodes || node.closest?.("script,style,svg")) return;

    node.childNodes.forEach(child => {
      if(child.nodeType === Node.TEXT_NODE){
        const text = normalize(child.textContent);
        if(!text) return;
        if(!originalText.has(child)) originalText.set(child, text);
        const key = originalText.get(child);
        const next = translations[lang]?.[key] || translations[lang]?.[text] || (lang === "en" ? key : null);
        if(next) child.textContent = child.textContent.replace(child.textContent.trim(), next);
      } else if(child.nodeType === Node.ELEMENT_NODE){
        translateNode(child, lang);
      }
    });

    if(node.placeholder !== undefined){
      if(!originalPlaceholder.has(node)) originalPlaceholder.set(node, node.placeholder);
      const key = originalPlaceholder.get(node);
      const next = translations[lang]?.[key] || (lang === "en" ? key : null);
      if(next) node.placeholder = next;
    }
  }

  function applyLanguage(lang){
    document.documentElement.lang = lang;
    translateNode(document.body, lang);
    localStorage.setItem("toopai-lang", lang);
  }

  window.TOOPAI_LANG = { applyLanguage };

  document.addEventListener("DOMContentLoaded", () => {
    const select = document.getElementById("langSelect");
    const saved = localStorage.getItem("toopai-lang") || "id";
    if(select) select.value = saved;
    applyLanguage(saved);
    if(select){
      select.addEventListener("change", () => applyLanguage(select.value));
    }
  });
})();

/* Script block 7  */
(function(){
  const pack = {
    id:{
      nav:["Product","Agents","Solutions","Case Study","Log In","Sign Up"],
      heroTitle:["AI Agents for","Creators &","Brands"],
      heroSub:"Build content faster. Grow smarter. Scale automatically with our autonomous AI workforce.",
      cta1:"Get Started →", cta2:"⊙ Watch Demo",
      stats:["PROJECTS","BRANDS","AI GENERATED"],
      trusted:"TRUSTED BY INNOVATORS",
      connectedEyebrow:"CONNECTED AI SYSTEM",
      connectedTitle:"TOOPAI connects every agent",
      connectedDesc:"6 specialized agents working in sync for your brand",
      agentEyebrow:"POWERFUL AI AGENTS",
      agentTitle1:"An AI Agent for every", agentTitle2:"need",
      agentDesc:"Specialized AI agents that work together to bring your ideas to life, from campaign planning to final content delivery.",
      explore:"Explore Agents →",
      cards:[
        ["Content Writer","Create blogs, scripts, and engaging content in seconds."],
        ["Image Creator","Generate stunning brand visuals for every campaign."],
        ["Video Producer","Create videos, reels, shorts, and social content."],
        ["Social Manager","Plan, schedule, and optimize your social media."],
        ["Translator","Break language barriers and reach global audiences."],
        ["Marketing AI","Automate budget, optimization, and campaign insight."]
      ],
      detailTitle:"Content Strategist Agent",
      detailDesc:"AI that analyzes trends, creates content calendar, and generates viral hooks tailored for your niche.",
      checks:["Predictive content planning","SEO & Hashtag intelligence","Viral hook generator"],
      videoTitle:"Video Editing Agent",
      videoDesc:"Simple TikTok-style editor for reels, shorts, captions, music sync, and B-roll suggestions.",
      videoChecks:["Auto-cut video highlight","Smart captions & subtitles","Music sync and hook preview"],
      why:"WHY TOOPAI?", whyTitle:"Built for the New Creative Era",
      whyCards:[
        ["Faster Workflow","Reduce production time by up to 70% with parallel AI agents."],
        ["AI Collaboration","AI works alongside you, learning your brand voice and style."],
        ["Enterprise Security","SOC2 compliant with private cloud options."]
      ],
      solutionsTitle:"Solutions for Every Need",
      solutions:[
        ["Creators","For individual creators"],["Brands","For companies & businesses"],["Agencies","For creative agencies"],["E-Commerce","For online stores"]
      ],
      resultsTitle:"Real Results, Real Impact",
      testimonialsTitle:"What Creators Say",
      faqTitle:"Frequently Asked Questions",
      faq:[
        ["What is Toopai?","Toopai is an AI-powered creative ecosystem that helps creators and brands produce content faster and grow smarter."],
        ["How does the AI agent work?","Our AI agents analyze your content style, learn your brand voice, and automate content creation, editing, and distribution."],
        ["Is there a free trial?","Yes! We offer a 14-day free trial on all plans. No credit card required."],
        ["Can I cancel anytime?","Absolutely. You can cancel or change your plan at any time with no hidden fees."]
      ],
      pricing:"PRICING", pricingTitle:"Choose your AI workflow",
      plans:[
        ["Starter","Free","Untuk coba fitur dasar Toopai.",["Basic AI agents","Creator profile","Campaign preview"],"Start Free"],
        ["Creator Pro","Rp 99.000 / bulan","Untuk creator yang ingin scale konten.",["Full AI agents","Smart matching","Content automation","Priority support"],"Get Pro"],
        ["Brand","Custom","Untuk brand dan agency.",["Campaign dashboard","Creator discovery","Advanced analytics","Custom integration"],"Contact Sales"]
      ],
      finalSmall:"READY TO BUILD THE FUTURE?", finalTitle:"Build Smarter with Toopai", finalBtns:["Get Started →","Book Demo →"],
      chatTitle:"Tanya TOOPAI", chatDesc:"Tanya tentang AI agent, campaign, konten, atau pricing.", chatPlaceholder:"Tanya TOOPAI..."
    },
    en:{
      nav:["Product","Agents","Solutions","Case Study","Log In","Sign Up"],
      heroTitle:["AI Agents for","Creators &","Brands"],
      heroSub:"Build content faster. Grow smarter. Scale automatically with our autonomous AI workforce.",
      cta1:"Get Started →", cta2:"⊙ Watch Demo",
      stats:["PROJECTS","BRANDS","AI GENERATED"],
      trusted:"TRUSTED BY INNOVATORS",
      connectedEyebrow:"CONNECTED AI SYSTEM",
      connectedTitle:"TOOPAI connects every agent",
      connectedDesc:"6 specialized agents working in sync for your brand",
      agentEyebrow:"POWERFUL AI AGENTS",
      agentTitle1:"An AI Agent for every", agentTitle2:"need",
      agentDesc:"Specialized AI agents that work together to bring your ideas to life, from campaign planning to final content delivery.",
      explore:"Explore Agents →",
      cards:[
        ["Content Writer","Create blogs, scripts, and engaging content in seconds."],
        ["Image Creator","Generate stunning brand visuals for every campaign."],
        ["Video Producer","Create videos, reels, shorts, and social content."],
        ["Social Manager","Plan, schedule, and optimize your social media."],
        ["Translator","Break language barriers and reach global audiences."],
        ["Marketing AI","Automate budget, optimization, and campaign insight."]
      ],
      detailTitle:"Content Strategist Agent",
      detailDesc:"AI that analyzes trends, creates content calendar, and generates viral hooks tailored for your niche.",
      checks:["Predictive content planning","SEO & Hashtag intelligence","Viral hook generator"],
      videoTitle:"Video Editing Agent",
      videoDesc:"Simple TikTok-style editor for reels, shorts, captions, music sync, and B-roll suggestions.",
      videoChecks:["Auto-cut video highlight","Smart captions & subtitles","Music sync and hook preview"],
      why:"WHY TOOPAI?", whyTitle:"Built for the New Creative Era",
      whyCards:[
        ["Faster Workflow","Reduce production time by up to 70% with parallel AI agents."],
        ["AI Collaboration","AI works alongside you, learning your brand voice and style."],
        ["Enterprise Security","SOC2 compliant with private cloud options."]
      ],
      solutionsTitle:"Solutions for Every Need",
      solutions:[
        ["Creators","For individual creators"],["Brands","For companies & businesses"],["Agencies","For creative agencies"],["E-Commerce","For online stores"]
      ],
      resultsTitle:"Real Results, Real Impact",
      testimonialsTitle:"What Creators Say",
      faqTitle:"Frequently Asked Questions",
      faq:[
        ["What is Toopai?","Toopai is an AI-powered creative ecosystem that helps creators and brands produce content faster and grow smarter."],
        ["How does the AI agent work?","Our AI agents analyze your content style, learn your brand voice, and automate content creation, editing, and distribution."],
        ["Is there a free trial?","Yes! We offer a 14-day free trial on all plans. No credit card required."],
        ["Can I cancel anytime?","Absolutely. You can cancel or change your plan at any time with no hidden fees."]
      ],
      pricing:"PRICING", pricingTitle:"Choose your AI workflow",
      plans:[
        ["Starter","Free","Try Toopai basic features.",["Basic AI agents","Creator profile","Campaign preview"],"Start Free"],
        ["Creator Pro","Rp 99.000 / month","For creators who want to scale content.",["Full AI agents","Smart matching","Content automation","Priority support"],"Get Pro"],
        ["Brand","Custom","For brands and agencies.",["Campaign dashboard","Creator discovery","Advanced analytics","Custom integration"],"Contact Sales"]
      ],
      finalSmall:"READY TO BUILD THE FUTURE?", finalTitle:"Build Smarter with Toopai", finalBtns:["Get Started →","Book Demo →"],
      chatTitle:"Ask TOOPAI", chatDesc:"Ask about AI agents, campaign, content, or pricing.", chatPlaceholder:"Ask TOOPAI..."
    },
    zh:{
      nav:["产品","智能体","解决方案","案例","登录","注册"],
      heroTitle:["面向","创作者与","品牌的 AI 智能体"],
      heroSub:"更快创作内容，更智能增长，并用 AI 自动化扩展规模。",
      cta1:"开始使用 →", cta2:"⊙ 观看演示",
      stats:["项目","品牌","AI 生成"],
      trusted:"受到创新者信赖",
      connectedEyebrow:"互联 AI 系统",
      connectedTitle:"TOOPAI 连接每一个智能体",
      connectedDesc:"6 个专业智能体为你的品牌协同工作",
      agentEyebrow:"强大的 AI 智能体",
      agentTitle1:"每个需求都有", agentTitle2:"AI 智能体",
      agentDesc:"专业 AI 智能体协同工作，从活动规划到最终内容交付，让你的创意落地。",
      explore:"探索智能体 →",
      cards:[
        ["内容写作","几秒内生成博客、脚本和高互动内容。"],
        ["图像创作","为每个活动生成出色的品牌视觉。"],
        ["视频制作","制作视频、Reels、Shorts 和社交内容。"],
        ["社媒管理","规划、排期并优化你的社交媒体。"],
        ["翻译","打破语言障碍，触达全球受众。"],
        ["营销 AI","自动化预算、优化和活动洞察。"]
      ],
      detailTitle:"内容策略智能体",
      detailDesc:"AI 分析趋势，创建内容日历，并为你的垂直领域生成病毒式 hook。",
      checks:["预测式内容规划","SEO 与标签智能","病毒式 hook 生成器"],
      videoTitle:"视频编辑智能体",
      videoDesc:"适用于 Reels、Shorts、字幕、音乐同步和 B-roll 建议的简洁 TikTok 风格编辑器。",
      videoChecks:["自动剪辑高光","智能字幕","音乐同步与开头预览"],
      why:"为什么选择 TOOPAI？", whyTitle:"为新创意时代打造",
      whyCards:[
        ["更快工作流","通过并行 AI 智能体将制作时间减少最多 70%。"],
        ["AI 协作","AI 与你一起工作，并学习你的品牌语气和风格。"],
        ["企业安全","SOC2 合规并支持私有云选项。"]
      ],
      solutionsTitle:"满足各种需求的解决方案",
      solutions:[
        ["创作者","适合个人创作者"],["品牌","适合公司和企业"],["机构","适合创意机构"],["电商","适合线上商店"]
      ],
      resultsTitle:"真实结果，真实影响",
      testimonialsTitle:"创作者怎么说",
      faqTitle:"常见问题",
      faq:[
        ["什么是 Toopai？","Toopai 是一个由 AI 驱动的创意生态系统，帮助创作者和品牌更快制作内容并更聪明地增长。"],
        ["AI 智能体如何工作？","我们的 AI 智能体会分析你的内容风格、学习品牌语气，并自动化内容创作、编辑和分发。"],
        ["有免费试用吗？","有！所有套餐提供 14 天免费试用，无需信用卡。"],
        ["可以随时取消吗？","当然。你可以随时取消或更改套餐，没有隐藏费用。"]
      ],
      pricing:"价格", pricingTitle:"选择你的 AI 工作流",
      plans:[
        ["入门版","免费","试用 Toopai 基础功能。",["基础 AI 智能体","创作者资料","活动预览"],"免费开始"],
        ["创作者 Pro","Rp 99.000 / 月","适合想要扩展内容的创作者。",["完整 AI 智能体","智能匹配","内容自动化","优先支持"],"获取 Pro"],
        ["品牌版","定制","适合品牌和机构。",["活动仪表板","创作者发现","高级分析","定制集成"],"联系销售"]
      ],
      finalSmall:"准备好构建未来了吗？", finalTitle:"用 Toopai 更智能地构建", finalBtns:["开始使用 →","预约演示 →"],
      chatTitle:"询问 TOOPAI", chatDesc:"询问 AI 智能体、活动、内容或价格。", chatPlaceholder:"询问 TOOPAI..."
    },
    th:{
      nav:["สินค้า","เอเจนต์","โซลูชัน","เคสศึกษา","เข้าสู่ระบบ","สมัคร"],
      heroTitle:["AI Agents สำหรับ","ครีเอเตอร์และ","แบรนด์"],
      heroSub:"สร้างคอนเทนต์เร็วขึ้น เติบโตฉลาดขึ้น และขยายผลอัตโนมัติด้วย AI",
      cta1:"เริ่มเลย →", cta2:"⊙ ดูเดโม",
      stats:["โปรเจกต์","แบรนด์","สร้างด้วย AI"],
      trusted:"ได้รับความไว้วางใจจากนักนวัตกรรม",
      connectedEyebrow:"ระบบ AI ที่เชื่อมต่อ",
      connectedTitle:"TOOPAI เชื่อมต่อทุกเอเจนต์",
      connectedDesc:"6 เอเจนต์เฉพาะทางทำงานร่วมกันเพื่อแบรนด์ของคุณ",
      agentEyebrow:"AI AGENTS ที่ทรงพลัง",
      agentTitle1:"ทุกความต้องการมี", agentTitle2:"AI Agent",
      agentDesc:"AI agent เฉพาะทางทำงานร่วมกัน ตั้งแต่วางแผนแคมเปญจนถึงส่งมอบคอนเทนต์สุดท้าย",
      explore:"สำรวจเอเจนต์ →",
      cards:[
        ["เขียนคอนเทนต์","สร้างบล็อก สคริปต์ และคอนเทนต์ที่น่าสนใจในไม่กี่วินาที"],
        ["สร้างภาพ","สร้างภาพแบรนด์ที่โดดเด่นสำหรับทุกแคมเปญ"],
        ["ผลิตวิดีโอ","สร้างวิดีโอ Reels Shorts และคอนเทนต์โซเชียล"],
        ["จัดการโซเชียล","วางแผน ตั้งเวลา และปรับปรุงโซเชียลมีเดีย"],
        ["แปลภาษา","ข้ามกำแพงภาษาและเข้าถึงผู้ชมทั่วโลก"],
        ["Marketing AI","อัตโนมัติงบประมาณ การปรับแต่ง และข้อมูลแคมเปญ"]
      ],
      detailTitle:"เอเจนต์กลยุทธ์คอนเทนต์",
      detailDesc:"AI วิเคราะห์เทรนด์ สร้างปฏิทินคอนเทนต์ และสร้าง hook ที่เหมาะกับ niche ของคุณ",
      checks:["วางแผนคอนเทนต์เชิงคาดการณ์","SEO และ Hashtag อัจฉริยะ","ตัวสร้าง hook ไวรัล"],
      videoTitle:"เอเจนต์ตัดต่อวิดีโอ",
      videoDesc:"ตัวตัดต่อสไตล์ TikTok สำหรับ reels, shorts, caption, sync เพลง และแนะนำ B-roll",
      videoChecks:["ตัดไฮไลต์อัตโนมัติ","caption และ subtitle อัจฉริยะ","sync เพลงและ preview hook"],
      why:"ทำไมต้อง TOOPAI?", whyTitle:"สร้างมาเพื่อยุคครีเอทีฟใหม่",
      whyCards:[
        ["Workflow เร็วขึ้น","ลดเวลาผลิตได้สูงสุด 70% ด้วย AI agents แบบขนาน"],
        ["ทำงานร่วมกับ AI","AI เรียนรู้เสียงและสไตล์แบรนด์ของคุณ"],
        ["ความปลอดภัยองค์กร","รองรับ SOC2 และตัวเลือก private cloud"]
      ],
      solutionsTitle:"โซลูชันสำหรับทุกความต้องการ",
      solutions:[
        ["ครีเอเตอร์","สำหรับครีเอเตอร์รายบุคคล"],["แบรนด์","สำหรับบริษัทและธุรกิจ"],["เอเจนซี","สำหรับเอเจนซีครีเอทีฟ"],["E-Commerce","สำหรับร้านค้าออนไลน์"]
      ],
      resultsTitle:"ผลลัพธ์จริง ผลกระทบจริง",
      testimonialsTitle:"สิ่งที่ครีเอเตอร์พูด",
      faqTitle:"คำถามที่พบบ่อย",
      faq:[
        ["Toopai คืออะไร?","Toopai คือระบบนิเวศครีเอทีฟที่ขับเคลื่อนด้วย AI ช่วยให้ครีเอเตอร์และแบรนด์สร้างคอนเทนต์เร็วขึ้นและเติบโตฉลาดขึ้น"],
        ["AI agent ทำงานอย่างไร?","AI agent วิเคราะห์สไตล์คอนเทนต์ เรียนรู้เสียงแบรนด์ และทำอัตโนมัติในการสร้าง แก้ไข และกระจายคอนเทนต์"],
        ["มีทดลองใช้ฟรีไหม?","มี! ทุกแพ็กเกจทดลองใช้ฟรี 14 วัน ไม่ต้องใช้บัตรเครดิต"],
        ["ยกเลิกได้ทุกเวลาไหม?","ได้แน่นอน คุณสามารถยกเลิกหรือเปลี่ยนแพ็กเกจได้ทุกเวลา ไม่มีค่าธรรมเนียมแอบแฝง"]
      ],
      pricing:"ราคา", pricingTitle:"เลือก workflow AI ของคุณ",
      plans:[
        ["Starter","ฟรี","ทดลองฟีเจอร์พื้นฐานของ Toopai",["Basic AI agents","โปรไฟล์ครีเอเตอร์","พรีวิวแคมเปญ"],"เริ่มฟรี"],
        ["Creator Pro","Rp 99.000 / เดือน","สำหรับครีเอเตอร์ที่อยาก scale คอนเทนต์",["AI agents ครบ","Smart matching","Content automation","Priority support"],"ใช้ Pro"],
        ["Brand","กำหนดเอง","สำหรับแบรนด์และเอเจนซี",["Campaign dashboard","Creator discovery","Advanced analytics","Custom integration"],"ติดต่อฝ่ายขาย"]
      ],
      finalSmall:"พร้อมสร้างอนาคตหรือยัง?", finalTitle:"สร้างฉลาดขึ้นกับ Toopai", finalBtns:["เริ่มเลย →","จองเดโม →"],
      chatTitle:"ถาม TOOPAI", chatDesc:"ถามเรื่อง AI agent, campaign, content หรือ pricing", chatPlaceholder:"ถาม TOOPAI..."
    },
    ja:{
      nav:["プロダクト","エージェント","ソリューション","事例","ログイン","登録"],
      heroTitle:["クリエイターと","ブランドのための","AIエージェント"],
      heroSub:"AIでコンテンツ制作を速くし、賢く成長し、自動でスケール。",
      cta1:"はじめる →", cta2:"⊙ デモを見る",
      stats:["プロジェクト","ブランド","AI生成"],
      trusted:"イノベーターに信頼されています",
      connectedEyebrow:"接続されたAIシステム",
      connectedTitle:"TOOPAIがすべてのエージェントを接続",
      connectedDesc:"6つの専門エージェントがブランドのために連携します",
      agentEyebrow:"強力なAIエージェント",
      agentTitle1:"すべてのニーズに", agentTitle2:"AIエージェント",
      agentDesc:"専門AIエージェントが連携し、キャンペーン計画から最終コンテンツ配信まで支援します。",
      explore:"エージェントを見る →",
      cards:[
        ["コンテンツ作成","ブログ、台本、魅力的なコンテンツを数秒で作成。"],
        ["画像生成","あらゆるキャンペーンに美しいブランドビジュアルを生成。"],
        ["動画制作","動画、リール、ショート、SNSコンテンツを制作。"],
        ["SNS管理","SNSを計画、予約、最適化。"],
        ["翻訳","言語の壁を越え、グローバルな視聴者にリーチ。"],
        ["マーケティングAI","予算、最適化、キャンペーン分析を自動化。"]
      ],
      detailTitle:"コンテンツ戦略エージェント",
      detailDesc:"AIがトレンドを分析し、コンテンツカレンダーを作成し、ニッチに合うバイラルhookを生成します。",
      checks:["予測型コンテンツ計画","SEOとハッシュタグ知能","バイラルhook生成"],
      videoTitle:"動画編集エージェント",
      videoDesc:"リール、ショート、字幕、音楽同期、B-roll提案に対応したシンプルなTikTok風エディター。",
      videoChecks:["動画ハイライト自動カット","スマート字幕","音楽同期とhookプレビュー"],
      why:"なぜTOOPAI？", whyTitle:"新しいクリエイティブ時代のために",
      whyCards:[
        ["高速ワークフロー","並列AIエージェントで制作時間を最大70%削減。"],
        ["AIコラボレーション","AIがあなたと並走し、ブランドの声とスタイルを学習。"],
        ["企業向けセキュリティ","SOC2準拠、プライベートクラウド対応。"]
      ],
      solutionsTitle:"あらゆるニーズに対応",
      solutions:[
        ["クリエイター","個人クリエイター向け"],["ブランド","企業・ビジネス向け"],["代理店","クリエイティブ代理店向け"],["Eコマース","オンラインストア向け"]
      ],
      resultsTitle:"リアルな成果、リアルな影響",
      testimonialsTitle:"クリエイターの声",
      faqTitle:"よくある質問",
      faq:[
        ["Toopaiとは？","ToopaiはAI搭載のクリエイティブエコシステムで、クリエイターとブランドがより速くコンテンツ制作し、賢く成長できるよう支援します。"],
        ["AIエージェントはどう動きますか？","AIエージェントはコンテンツスタイルを分析し、ブランドボイスを学習し、制作・編集・配信を自動化します。"],
        ["無料トライアルはありますか？","はい。すべてのプランで14日間無料トライアルが可能です。クレジットカード不要。"],
        ["いつでも解約できますか？","もちろんです。いつでもプラン変更や解約ができ、隠れた手数料はありません。"]
      ],
      pricing:"料金", pricingTitle:"AIワークフローを選択",
      plans:[
        ["スターター","無料","Toopaiの基本機能を試せます。",["基本AIエージェント","クリエイタープロフィール","キャンペーンプレビュー"],"無料で開始"],
        ["Creator Pro","Rp 99.000 / 月","コンテンツを拡大したいクリエイター向け。",["全AIエージェント","スマートマッチング","コンテンツ自動化","優先サポート"],"Proを使う"],
        ["ブランド","カスタム","ブランドと代理店向け。",["キャンペーンダッシュボード","クリエイター発見","高度な分析","カスタム連携"],"営業に相談"]
      ],
      finalSmall:"未来を作る準備はできましたか？", finalTitle:"Toopaiでより賢く構築", finalBtns:["はじめる →","デモ予約 →"],
      chatTitle:"TOOPAIに質問", chatDesc:"AIエージェント、キャンペーン、コンテンツ、料金について質問できます。", chatPlaceholder:"TOOPAIに質問..."
    },
    ko:{
      nav:["제품","에이전트","솔루션","사례","로그인","가입"],
      heroTitle:["크리에이터와","브랜드를 위한","AI 에이전트"],
      heroSub:"AI로 콘텐츠를 더 빠르게 만들고, 더 똑똑하게 성장하며, 자동으로 확장하세요.",
      cta1:"시작하기 →", cta2:"⊙ 데모 보기",
      stats:["프로젝트","브랜드","AI 생성"],
      trusted:"혁신가들이 신뢰합니다",
      connectedEyebrow:"연결된 AI 시스템",
      connectedTitle:"TOOPAI가 모든 에이전트를 연결합니다",
      connectedDesc:"6개의 전문 에이전트가 브랜드를 위해 함께 작동합니다",
      agentEyebrow:"강력한 AI 에이전트",
      agentTitle1:"모든 니즈를 위한", agentTitle2:"AI 에이전트",
      agentDesc:"전문 AI 에이전트가 캠페인 기획부터 최종 콘텐츠 전달까지 아이디어를 현실로 만듭니다.",
      explore:"에이전트 탐색 →",
      cards:[
        ["콘텐츠 작성","블로그, 스크립트, 참여도 높은 콘텐츠를 몇 초 만에 생성합니다."],
        ["이미지 생성","모든 캠페인에 맞는 멋진 브랜드 비주얼을 생성합니다."],
        ["영상 제작","영상, 릴스, 쇼츠, 소셜 콘텐츠를 제작합니다."],
        ["소셜 관리","소셜 미디어를 계획, 예약, 최적화합니다."],
        ["번역","언어 장벽을 넘어 글로벌 audience에 도달합니다."],
        ["마케팅 AI","예산, 최적화, 캠페인 인사이트를 자동화합니다."]
      ],
      detailTitle:"콘텐츠 전략 에이전트",
      detailDesc:"AI가 트렌드를 분석하고 콘텐츠 캘린더를 만들며 니치에 맞춘 바이럴 hook을 생성합니다.",
      checks:["예측 콘텐츠 기획","SEO 및 해시태그 인텔리전스","바이럴 hook 생성기"],
      videoTitle:"영상 편집 에이전트",
      videoDesc:"릴스, 쇼츠, 자막, 음악 싱크, B-roll 제안을 위한 간단한 TikTok 스타일 편집기.",
      videoChecks:["영상 하이라이트 자동 컷","스마트 캡션 및 자막","음악 싱크와 hook 미리보기"],
      why:"왜 TOOPAI인가요?", whyTitle:"새로운 크리에이티브 시대를 위해",
      whyCards:[
        ["더 빠른 워크플로우","병렬 AI 에이전트로 제작 시간을 최대 70% 단축합니다."],
        ["AI 협업","AI가 함께 작업하며 브랜드 보이스와 스타일을 학습합니다."],
        ["엔터프라이즈 보안","SOC2 준수 및 프라이빗 클라우드 옵션 제공."]
      ],
      solutionsTitle:"모든 니즈를 위한 솔루션",
      solutions:[
        ["크리에이터","개인 크리에이터용"],["브랜드","기업과 비즈니스용"],["에이전시","크리에이티브 에이전시용"],["E-Commerce","온라인 스토어용"]
      ],
      resultsTitle:"실제 결과, 실제 영향",
      testimonialsTitle:"크리에이터 후기",
      faqTitle:"자주 묻는 질문",
      faq:[
        ["Toopai란 무엇인가요?","Toopai는 크리에이터와 브랜드가 콘텐츠를 더 빠르게 만들고 더 똑똑하게 성장하도록 돕는 AI 기반 크리에이티브 생태계입니다."],
        ["AI 에이전트는 어떻게 작동하나요?","AI 에이전트는 콘텐츠 스타일을 분석하고 브랜드 보이스를 학습하며 콘텐츠 제작, 편집, 배포를 자동화합니다."],
        ["무료 체험이 있나요?","네! 모든 플랜에서 14일 무료 체험을 제공합니다. 신용카드는 필요 없습니다."],
        ["언제든 취소할 수 있나요?","물론입니다. 언제든 플랜을 변경하거나 취소할 수 있으며 숨겨진 수수료는 없습니다."]
      ],
      pricing:"가격", pricingTitle:"AI 워크플로우 선택",
      plans:[
        ["스타터","무료","Toopai 기본 기능을 체험하세요.",["기본 AI 에이전트","크리에이터 프로필","캠페인 미리보기"],"무료 시작"],
        ["Creator Pro","Rp 99.000 / 월","콘텐츠를 확장하고 싶은 크리에이터용.",["전체 AI 에이전트","스마트 매칭","콘텐츠 자동화","우선 지원"],"Pro 시작"],
        ["브랜드","맞춤형","브랜드와 에이전시용.",["캠페인 대시보드","크리에이터 발견","고급 분석","맞춤 연동"],"영업 문의"]
      ],
      finalSmall:"미래를 만들 준비가 되셨나요?", finalTitle:"Toopai로 더 스마트하게 구축", finalBtns:["시작하기 →","데모 예약 →"],
      chatTitle:"TOOPAI에게 질문", chatDesc:"AI 에이전트, 캠페인, 콘텐츠 또는 가격에 대해 질문하세요.", chatPlaceholder:"TOOPAI에게 질문..."
    }
  };

  function setText(el, text){ if(el && text !== undefined) el.textContent = text; }
  function setHTML(el, html){ if(el && html !== undefined) el.innerHTML = html; }

  function applyPack(lang){
    const t = pack[lang] || pack.id;

    const navs = document.querySelectorAll(".navlinks a");
    navs.forEach((a,i)=>setText(a,t.nav[i]));
    const navActions = document.querySelectorAll(".nav-actions a");
    if(navActions[0]) setText(navActions[0], t.nav[4]);
    if(navActions[1]) setText(navActions[1], t.nav[5]);

    const h1 = document.querySelector(".copy h1");
    if(h1) h1.innerHTML = `<span class="headline-white">${t.heroTitle[0]}</span><br><span class="headline-grad">${t.heroTitle[1]}</span><br><span class="headline-grad">${t.heroTitle[2]}</span>`;
    setText(document.querySelector(".intro-text"), t.heroSub);
    const heroBtns = document.querySelectorAll(".hero-actions-main .btn");
    setText(heroBtns[0], t.cta1); setText(heroBtns[1], t.cta2);

    document.querySelectorAll(".stat p").forEach((p,i)=>setText(p,t.stats[i]));
    const brandSmall = document.querySelector(".brand-strip small"); setText(brandSmall,t.trusted);

    const connected = document.querySelector(".circuit-section, .connected-system");
    if(connected){
      setText(connected.querySelector(".eyebrow, .connected-head small"), t.connectedEyebrow);
      setText(connected.querySelector("h2"), t.connectedTitle);
      const desc = connected.querySelector(".connected-head p") || connected.querySelector("p");
      setText(desc, t.connectedDesc);
    }

    const agents = document.querySelector("#agents");
    if(agents){
      setText(agents.querySelector(".eyebrow"), t.agentEyebrow);
      const at = agents.querySelector(".agents-overview h2");
      if(at) at.innerHTML = `${t.agentTitle1} <span class="grad">${t.agentTitle2}</span>`;
      setText(agents.querySelector(".agents-overview p"), t.agentDesc);
      const exp = agents.querySelector(".btn"); setText(exp,t.explore);
      agents.querySelectorAll(".agent-card").forEach((card,i)=>{
        if(t.cards[i]){
          setText(card.querySelector("h3"), t.cards[i][0]);
          setText(card.querySelector("p"), t.cards[i][1]);
        }
      });
      setText(document.getElementById("agent-title"), t.detailTitle);
      setText(document.getElementById("agent-desc"), t.detailDesc);
      const list = document.getElementById("agent-list");
      if(list) list.innerHTML = t.checks.map(x=>`<li>${x}</li>`).join("");
    }

    const video = document.querySelector("#video-editing");
    if(video){
      setText(video.querySelector("h2"), t.videoTitle);
      setText(video.querySelector("p"), t.videoDesc);
      const lis = video.querySelectorAll("li");
      lis.forEach((li,i)=>setText(li,t.videoChecks[i]));
    }

    document.querySelectorAll(".section-inner.center").forEach(sec=>{
      const h2 = sec.querySelector("h2");
      if(!h2) return;
      const current = h2.textContent.toLowerCase();
      if(current.includes("creative era") || current.includes("kreatif") || current.includes("创意") || current.includes("クリエイティブ") || current.includes("크리에이티브")){
        setText(sec.querySelector(".eyebrow"), t.why);
        setText(h2,t.whyTitle);
        sec.querySelectorAll(".info-card").forEach((c,i)=>{
          if(t.whyCards[i]){ setText(c.querySelector("h3"), t.whyCards[i][0]); setText(c.querySelector("p"), t.whyCards[i][1]);}
        });
      }
      if(current.includes("solutions") || current.includes("solusi") || current.includes("解决") || current.includes("ソリューション") || current.includes("솔루션")){
        setText(h2,t.solutionsTitle);
        sec.querySelectorAll(".info-card").forEach((c,i)=>{
          if(t.solutions[i]){ setText(c.querySelector("h3"), t.solutions[i][0]); setText(c.querySelector("p"), t.solutions[i][1]);}
        });
      }
      if(current.includes("real results") || current.includes("hasil") || current.includes("真实") || current.includes("成果") || current.includes("결과")){
        setText(h2,t.resultsTitle);
      }
    });

    const pricing = document.querySelector("#pricing .pricing-title");
    if(pricing){
      setText(pricing.querySelector("small"), t.pricing);
      setText(pricing.querySelector("h2"), t.pricingTitle);
      document.querySelectorAll("#pricing .price-card").forEach((card,i)=>{
        const p = t.plans[i]; if(!p) return;
        setText(card.querySelector("h3"), p[0]);
        setText(card.querySelector(".price"), p[1]);
        setText(card.querySelector("p"), p[2]);
        const ul = card.querySelector("ul");
        if(ul) ul.innerHTML = p[3].map(x=>`<li>${x}</li>`).join("");
        setText(card.querySelector(".price-btn"), p[4]);
      });
    }

    document.querySelectorAll("h2").forEach(h=>{
      const lower = h.textContent.toLowerCase();
      if(lower.includes("creators say") || lower.includes("creator") && lower.includes("say")) setText(h,t.testimonialsTitle);
      if(lower.includes("frequently") || lower.includes("questions") || lower.includes("pertanyaan")) setText(h,t.faqTitle);
    });

    const faqCards = document.querySelectorAll(".faq .info-card");
    faqCards.forEach((c,i)=>{
      if(t.faq[i]){
        setText(c.querySelector("h3"), "❔ " + t.faq[i][0]);
        setText(c.querySelector("p"), t.faq[i][1]);
      }
    });

    const cta = document.querySelector(".cta-final");
    if(cta){
      setText(cta.querySelector("small"), t.finalSmall);
      setText(cta.querySelector("h2"), t.finalTitle);
      const btns = cta.querySelectorAll(".btn");
      setText(btns[0], t.finalBtns[0]); setText(btns[1], t.finalBtns[1]);
    }

    const chat = document.querySelector(".chat-head");
    if(chat){
      setText(chat.querySelector("h3"), t.chatTitle);
      setText(chat.querySelector("p"), t.chatDesc);
      const input = document.getElementById("chatInput");
      if(input) input.placeholder = t.chatPlaceholder;
    }

    document.documentElement.lang = lang;
    localStorage.setItem("toopai-lang", lang);
  }

  window.applyToopaiLanguage = applyPack;

  document.addEventListener("DOMContentLoaded",()=>{
    const select = document.getElementById("langSelect");
    const saved = localStorage.getItem("toopai-lang") || "id";
    if(select) select.value = saved;
    applyPack(saved);
    if(select) select.addEventListener("change",()=>applyPack(select.value));
  });
})();

/* Script block 8  */
(function(){
  const preloader = document.getElementById("toopaiPreloader");
  if(preloader){
    document.body.classList.add("entering-site");
    const hide = () => {
      preloader.classList.add("hide");
      setTimeout(()=>preloader.remove(), 900);
    };
    window.addEventListener("load", () => setTimeout(hide, 10000));
    preloader.addEventListener("wheel", hide, {once:true});
    preloader.addEventListener("click", hide, {once:true});
    preloader.addEventListener("touchstart", hide, {once:true});
  }

  const widget = document.getElementById("toopaiChat");
  const toggle = document.getElementById("chatToggle");
  const nudge = document.getElementById("proactiveNudge");
  const input = document.getElementById("chatInput");
  const send = document.getElementById("chatSend");
  const body = document.getElementById("chatBody");

  if(!widget || !toggle || !body) return;

  // draggable chat icon
  let drag = false, moved = false, sx = 0, sy = 0, startRight = 22, startBottom = 24;

  function getPos(){
    const rect = widget.getBoundingClientRect();
    return {
      right: window.innerWidth - rect.right,
      bottom: window.innerHeight - rect.bottom
    };
  }

  function clamp(v,min,max){ return Math.max(min, Math.min(max, v)); }

  toggle.addEventListener("pointerdown", (e)=>{
    drag = true; moved = false;
    const pos = getPos();
    startRight = pos.right; startBottom = pos.bottom;
    sx = e.clientX; sy = e.clientY;
    widget.classList.add("dragging");
    toggle.setPointerCapture(e.pointerId);
  });

  toggle.addEventListener("pointermove", (e)=>{
    if(!drag) return;
    const dx = e.clientX - sx;
    const dy = e.clientY - sy;
    if(Math.abs(dx)+Math.abs(dy) > 6) moved = true;

    const rect = widget.getBoundingClientRect();
    const newRight = clamp(startRight - dx, 8, window.innerWidth - 86);
    const newBottom = clamp(startBottom - dy, 8, window.innerHeight - 86);

    widget.style.right = newRight + "px";
    widget.style.bottom = newBottom + "px";
  });

  toggle.addEventListener("pointerup", (e)=>{
    drag = false;
    widget.classList.remove("dragging");
    setTimeout(()=>{ moved = false; }, 80);
  });

  // prevent click toggle after drag
  toggle.addEventListener("click", (e)=>{
    if(moved){
      e.preventDefault();
      e.stopImmediatePropagation();
    }
  }, true);

  // proactive message after user stays/scrolls
  let nudgeShown = false;
  function showNudge(){
    if(nudgeShown || widget.classList.contains("open")) return;
    nudgeShown = true;
    nudge.classList.add("show");
    setTimeout(()=>nudge.classList.remove("show"), 8500);
  }
  setTimeout(showNudge, 11000);
  let scrollTimer;
  window.addEventListener("scroll", ()=>{
    clearTimeout(scrollTimer);
    scrollTimer = setTimeout(showNudge, 5200);
  }, {passive:true});

  if(nudge){
    nudge.addEventListener("click", ()=>{
      widget.classList.add("open");
      nudge.classList.remove("show");
      addMessage("Tenang, aku bantu jelasin pelan-pelan. Kamu lagi bingung tentang AI campaign, platform, atau fitur Pro?", "bot");
    });
  }

  function addMessage(text, type){
    const bubble = document.createElement("div");
    bubble.className = "chat-bubble " + type;
    bubble.textContent = text;
    body.appendChild(bubble);
    body.scrollTop = body.scrollHeight;
  }

  function answer(q){
    const s = q.toLowerCase();

    if(s.includes("sedih") || s.includes("gagal") || s.includes("capek") || s.includes("bingung")){
      return "Jangan sedih ya. Kamu sudah sampai sejauh ini, itu keren. Kita bisa pelan-pelan: mulai dari tujuan campaign, pilih platform, lalu TOOPAI bantu bikin ide konten dan strategi.";
    }

    if(s.includes("gratis") || s.includes("free")){
      return "Fitur gratis tetap bisa dipakai untuk akses basic AI agent, preview campaign, ide konten awal, dan creator profile. Cocok untuk coba dulu sebelum upgrade.";
    }

    if(s.includes("pro") || s.includes("99") || s.includes("bayar") || s.includes("pricing") || s.includes("harga")){
      return "Creator Pro Rp 99.000/bulan membuka akses lebih lengkap: full AI agents, smart matching creator-brand, content automation, video agent, analytics, dan priority support.";
    }

    if(s.includes("tiktok")){
      return "Untuk TikTok: fokus hook 3 detik pertama, caption pendek, format vertikal, tren audio, dan CTA jelas. TOOPAI bisa bantu bikin script, ide hook, editing style, dan jadwal posting.";
    }

    if(s.includes("instagram") || s.includes("ig") || s.includes("reels")){
      return "Untuk Instagram/Reels: gunakan visual kuat, carousel edukatif, reels 7-20 detik, caption storytelling, dan hashtag niche. TOOPAI bisa generate ide, caption, visual brief, dan kalender konten.";
    }

    if(s.includes("youtube")){
      return "Untuk YouTube: pisahkan strategi Shorts dan long-form. Shorts untuk awareness, long-form untuk trust. TOOPAI bisa bantu judul, thumbnail angle, script, chapter, dan content calendar.";
    }

    if(s.includes("shopee") || s.includes("tokopedia") || s.includes("ecommerce") || s.includes("marketplace")){
      return "Untuk marketplace seperti Shopee/Tokopedia: campaign perlu fokus produk, benefit, review, flash sale, live shopping, dan creator affiliate. TOOPAI bisa bantu pilih creator dan pesan promosi.";
    }

    if(s.includes("brand") || s.includes("campaign")){
      return "Campaign brand biasanya mulai dari objective: awareness, engagement, traffic, atau conversion. Setelah itu tentukan audience, platform, creator type, content format, budget, dan KPI. TOOPAI bisa bantu dari brief sampai tracking.";
    }

    if(s.includes("platform")){
      return "Platform bisa dipilih sesuai tujuan: TikTok untuk viral reach, Instagram untuk visual branding, YouTube untuk trust, marketplace untuk conversion, LinkedIn untuk B2B. TOOPAI bisa bantu rekomendasi platform.";
    }

    if(s.includes("fitur") || s.includes("agent")){
      return "TOOPAI punya Content Strategist, Image Creator, Video Editing, Social Manager, Translator, Marketing Automation, Analytics, dan Creator Matching. Kamu bisa mulai dari agent sesuai kebutuhan campaign.";
    }

    if(s.includes("mulai") || s.includes("start")){
      return "Mulai dari 3 langkah: 1) pilih tujuan campaign, 2) pilih platform utama, 3) buat 5 ide konten. Setelah itu TOOPAI bantu buat script, visual, jadwal, dan optimasi.";
    }

    return "Bisa. Ceritakan campaign kamu: produk/brand apa, target audience siapa, platform mana, dan tujuannya awareness atau sales? Nanti aku bantu pecah jadi strategi konten satu per satu.";
  }

  // Override old send behavior with smarter responses
  const newSend = (text)=>{
    const val = (text || input?.value || "").trim();
    if(!val) return;
    addMessage(val, "user");
    if(input) input.value = "";
    setTimeout(()=>addMessage(answer(val), "bot"), 420);
  };

  if(send){
    send.replaceWith(send.cloneNode(true));
  }
  const newBtn = document.getElementById("chatSend");
  if(newBtn) newBtn.addEventListener("click", ()=>newSend());
  if(input){
    input.addEventListener("keydown", e=>{
      if(e.key === "Enter"){
        e.preventDefault();
        newSend();
      }
    });
  }
  document.querySelectorAll(".quick-asks button").forEach(btn=>{
    btn.addEventListener("click", ()=>newSend(btn.dataset.q || btn.textContent));
  });
})();

/* Script block 9  */
(function(){
  const priceMap = {
    id: "Rp 99.000 / bulan",
    en: "$6.99 / month",
    zh: "¥49 / 月",
    ja: "¥980 / 月"
  };

  function updatePriceByLang(){
    const lang = document.getElementById("langSelect")?.value || localStorage.getItem("toopai-lang") || "id";
    const cards = document.querySelectorAll("#pricing .price-card");
    cards.forEach(card => {
      const title = card.querySelector("h3")?.textContent || "";
      if(title.includes("Creator") || title.includes("创作者") || title.includes("クリエイター")){
        const price = card.querySelector(".price");
        if(price) price.textContent = priceMap[lang] || priceMap.id;
      }
    });
  }

  document.addEventListener("DOMContentLoaded", () => {
    setTimeout(updatePriceByLang, 250);
    const select = document.getElementById("langSelect");
    if(select) select.addEventListener("change", () => setTimeout(updatePriceByLang, 120));
  });

  const oldApply = window.applyToopaiLanguage;
  if(typeof oldApply === "function"){
    window.applyToopaiLanguage = function(lang){
      oldApply(lang);
      setTimeout(updatePriceByLang, 80);
    };
  }
})();

/* Script block 10  */
(function(){
  const widget = document.getElementById("toopaiChat");
  const body = document.getElementById("chatBody");
  if(!widget || !body) return;

  function addBot(text){
    const bubble = document.createElement("div");
    bubble.className = "chat-bubble bot";
    bubble.textContent = text;
    body.appendChild(bubble);
    body.scrollTop = body.scrollHeight;
  }

  const questions = [
    "Halo! Kabarnya gimana, kamu sehat nggak hari ini?",
    "Lagi bingung soal AI campaign atau creator-brand matching?",
    "Mau join TOOPAI nggak? Kamu bisa mulai dari fitur gratis dulu.",
    "Kalau mau akses semua agent, Creator Pro tersedia Rp 99.000/bulan.",
    "Jangan sedih ya kalau masih bingung. Aku bantu jelasin pelan-pelan sampai paham."
  ];

  let qi = 0;
  let proactiveStarted = false;

  function askLoop(){
    if(!widget.classList.contains("open")) {
      const nudge = document.getElementById("proactiveNudge");
      if(nudge){
        nudge.innerHTML = "<b>TOOPAI:</b> " + questions[qi % questions.length];
        nudge.classList.add("show");
        setTimeout(()=>nudge.classList.remove("show"), 3600);
      }
    } else {
      addBot(questions[qi % questions.length]);
    }
    qi++;
    const nextDelay = 5000 + Math.floor(Math.random() * 3000);
    setTimeout(askLoop, nextDelay);
  }

  function startProactive(){
    if(proactiveStarted) return;
    proactiveStarted = true;
    setTimeout(askLoop, 5500);
  }

  setTimeout(startProactive, 5200);

  const explanations = [
    [".navlinks a[href*='product'], .navlinks a:nth-child(1)", "Product itu bagian untuk lihat gambaran utama TOOPAI dan cara platform ini membantu creator & brand."],
    [".navlinks a[href*='agents'], .navlinks a:nth-child(2)", "Agents berisi AI agent seperti Content Writer, Image Creator, Video Producer, Social Manager, Translator, dan Marketing AI."],
    [".navlinks a[href*='solutions'], .navlinks a:nth-child(3)", "Solutions menjelaskan solusi TOOPAI untuk creator, brand, agency, dan e-commerce."],
    [".navlinks a[href*='case'], .navlinks a:nth-child(4)", "Case Study menunjukkan contoh hasil, impact, dan bagaimana TOOPAI dipakai untuk campaign nyata."],
    ["#pricing, .price-card, .pricing-section", "Pricing menjelaskan paket gratis dan Creator Pro. Creator Pro membuka akses lebih lengkap ke semua agent."],
    [".agent-card", "Card agent ini bisa kamu pilih sesuai kebutuhan: konten, gambar, video, social media, translate, atau marketing automation."],
    [".btn, .price-btn", "Tombol ini adalah CTA. Klik untuk mulai, melihat fitur, masuk ke pricing, atau mencoba agent."],
    [".brand-logo, .brands span", "Brand row ini menunjukkan contoh brand/ekosistem yang bisa terhubung dengan campaign TOOPAI."],
    [".toopai-chat-button", "Ini TOOPAI AI assistant. Kamu bisa tanya strategi campaign, platform, fitur gratis, atau Creator Pro."]
  ];

  const hint = document.createElement("div");
  hint.className = "toopai-hotspot-hint";
  document.body.appendChild(hint);

  function showHint(text, target){
    const r = target.getBoundingClientRect();
    hint.textContent = text;
    hint.style.left = Math.min(window.innerWidth - 300, Math.max(14, r.left + r.width/2 - 140)) + "px";
    hint.style.top = Math.min(window.innerHeight - 120, Math.max(14, r.bottom + 12)) + "px";
    hint.classList.add("show");

    if(widget.classList.contains("open")){
      addBot(text);
    }
  }

  function hideHint(){
    hint.classList.remove("show");
  }

  explanations.forEach(([selector, text]) => {
    document.querySelectorAll(selector).forEach(el => {
      el.addEventListener("mouseenter", () => showHint(text, el));
      el.addEventListener("mouseleave", hideHint);
      el.addEventListener("focus", () => showHint(text, el));
      el.addEventListener("blur", hideHint);
      el.addEventListener("click", () => {
        if(widget.classList.contains("open")) addBot(text);
      });
    });
  });
})();

/* Script block 11  */
(function(){
  function resetCounters(){
    document.querySelectorAll(".counter").forEach(counter => {
      counter.textContent = "0";
      counter.dataset.counted = "false";
    });
  }

  function animateCountersAfterLoading(){
    const counters = document.querySelectorAll(".counter");
    if(!counters.length) return;

    counters.forEach(counter => {
      if(counter.dataset.counted === "true") return;
      counter.dataset.counted = "true";

      const target = Number(counter.dataset.target || counter.textContent.replace(/[^0-9]/g, "") || 0);
      const duration = 1700;
      const start = performance.now();

      function frame(now){
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        const value = Math.floor(eased * target);
        counter.textContent = value.toLocaleString("en-US");
        if(progress < 1) requestAnimationFrame(frame);
      }
      requestAnimationFrame(frame);
    });
  }

  resetCounters();

  const preloader = document.getElementById("toopaiPreloader");
  if(preloader){
    const obs = new MutationObserver(() => {
      if(preloader.classList.contains("hide")){
        setTimeout(animateCountersAfterLoading, 900);
        obs.disconnect();
      }
    });
    obs.observe(preloader, { attributes:true, attributeFilter:["class"] });

    setTimeout(() => {
      if(!document.body.contains(preloader)) animateCountersAfterLoading();
    }, 11200);
  } else {
    setTimeout(animateCountersAfterLoading, 600);
  }
})();

/* Script block 12  */
(function(){
  const brandsSection = document.querySelector(".brands-row, .brands, .trusted-brands");
  if(!brandsSection) return;

  const logos = brandsSection.innerHTML;

  brandsSection.innerHTML = `
    <div class="brand-marquee">
      <div class="brand-track">
        ${logos}
        ${logos}
      </div>
    </div>
  `;
})();

/* Script block 13  */
(function(){
  const brandContainer = document.querySelector(".brand-strip .brands, .brands-row, .trusted-brands");
  if(brandContainer && !brandContainer.dataset.marqueeFixed){
    let logos = brandContainer.querySelector(".brand-track") 
      ? brandContainer.querySelector(".brand-track").innerHTML 
      : brandContainer.innerHTML;

    // remove nested duplicate tracks if any
    logos = logos.replace(/<div class="brand-track">|<\/div>\s*$/g, "");

    brandContainer.dataset.marqueeFixed = "true";
    brandContainer.innerHTML = `<div class="brand-track">${logos}${logos}</div>`;
  }

  // Shorten UI explanations to 3-6 words
  const shortTips = [
    [".navlinks a[href*='product'], .navlinks a:nth-child(1)", "Lihat produk TOOPAI"],
    [".navlinks a[href*='agents'], .navlinks a:nth-child(2)", "Pilih AI agent"],
    [".navlinks a[href*='solutions'], .navlinks a:nth-child(3)", "Solusi tiap kebutuhan"],
    [".navlinks a[href*='case'], .navlinks a:nth-child(4)", "Lihat hasil nyata"],
    ["#pricing, .price-card, .pricing-section", "Pilih paket terbaik"],
    [".agent-card", "Klik untuk detail agent"],
    [".btn, .price-btn", "Mulai atau coba fitur"],
    [".brand-logo, .brands span", "Brand partner TOOPAI"],
    [".toopai-chat-button", "Tanya TOOPAI AI"]
  ];

  const hint = document.querySelector(".toopai-hotspot-hint");
  if(hint){
    const newHint = hint.cloneNode(false);
    hint.replaceWith(newHint);

    function show(text, el){
      const r = el.getBoundingClientRect();
      newHint.textContent = text;
      newHint.style.left = Math.min(window.innerWidth - 210, Math.max(12, r.left + r.width/2 - 95)) + "px";
      newHint.style.top = Math.min(window.innerHeight - 80, Math.max(12, r.bottom + 10)) + "px";
      newHint.classList.add("show");
    }
    function hide(){ newHint.classList.remove("show"); }

    shortTips.forEach(([selector,text])=>{
      document.querySelectorAll(selector).forEach(el=>{
        el.addEventListener("mouseenter",()=>show(text,el));
        el.addEventListener("mouseleave",hide);
        el.addEventListener("focus",()=>show(text,el));
        el.addEventListener("blur",hide);
      });
    });
  }

  // Get Started and Sign Up -> real TOOPAI register link
  const registerUrl = "https://toopai.ai/creator_auth/register";
  document.querySelectorAll("a, button").forEach(el=>{
    const text = (el.textContent || "").trim().toLowerCase();
    if(text.includes("get started") || text.includes("mulai") || text.includes("start free") || text.includes("sign up") || text.includes("register")){
      if(el.tagName.toLowerCase() === "a"){
        el.href = registerUrl;
        el.target = "_blank";
        el.rel = "noopener";
      } else {
        el.addEventListener("click",()=>window.open(registerUrl,"_blank","noopener"));
      }
    }
  });
})();

/* Script block 14  */
(function(){
  const moreBtn = document.getElementById('mobileMoreBtn');
  const panel = document.getElementById('mobileControlPanel');
  const desktopTheme = document.getElementById('themeToggle');
  const mobileTheme = document.getElementById('mobileThemeToggle');
  const desktopLang = document.getElementById('langSelect');
  const mobileLang = document.getElementById('mobileLangSelect');

  if(moreBtn && panel){
    moreBtn.addEventListener('click', () => {
      const show = !panel.classList.contains('show');
      panel.classList.toggle('show', show);
      moreBtn.classList.toggle('active', show);
      panel.setAttribute('aria-hidden', show ? 'false' : 'true');
    });
    document.addEventListener('click', (e) => {
      if(!panel.contains(e.target) && !moreBtn.contains(e.target)){
        panel.classList.remove('show');
        moreBtn.classList.remove('active');
        panel.setAttribute('aria-hidden','true');
      }
    });
  }

  if(mobileTheme && desktopTheme){
    mobileTheme.addEventListener('click', () => desktopTheme.click());
  }
  if(mobileLang && desktopLang){
    mobileLang.value = desktopLang.value;
    mobileLang.addEventListener('change', () => {
      desktopLang.value = mobileLang.value;
      desktopLang.dispatchEvent(new Event('change', {bubbles:true}));
    });
    desktopLang.addEventListener('change', () => { mobileLang.value = desktopLang.value; });
  }

  const items = document.querySelectorAll('.mobile-bottom-nav a[href^="#"]');
  items.forEach(item => item.addEventListener('click', () => {
    items.forEach(i => i.classList.remove('active'));
    item.classList.add('active');
    if(panel){panel.classList.remove('show');}
    if(moreBtn){moreBtn.classList.remove('active');}
  }));
})();

/* Script block 15 id="toopai-flow-fix-js" */
(function(){
  const auth=document.getElementById('toopaiAuthPage'); const reg=document.getElementById('registerCard'); const log=document.getElementById('loginCard'); const loader=document.getElementById('toopaiLoadingScreen');
  function showAuth(mode='register'){ auth.classList.add('show'); document.body.style.overflow='hidden'; if(mode==='login') showLogin(); else showRegister(); window.location.hash='auth'; }
  function hideAuth(){ auth.classList.remove('show'); document.body.style.overflow=''; history.replaceState(null,'',location.pathname+location.search+'#home'); document.getElementById('home')?.scrollIntoView({behavior:'smooth'}); }
  function showLogin(){ reg.classList.add('auth-hidden'); log.classList.remove('auth-hidden'); }
  function showRegister(){ log.classList.add('auth-hidden'); reg.classList.remove('auth-hidden'); }
  document.addEventListener('click',function(e){
    const a=e.target.closest('a,button'); if(!a) return;
    const txt=(a.textContent||'').trim().toLowerCase(); const href=a.getAttribute('href')||'';
    if(a.classList.contains('toopai-home-logo')||a.id==='authBackHome'){ e.preventDefault(); hideAuth(); return; }
    if(txt.includes('get started')||txt==='sign up'||href==='#auth'){ e.preventDefault(); showAuth('register'); return; }
    if(txt==='log in'||txt==='login here'){ e.preventDefault(); showAuth('login'); return; }
    if(href && href.startsWith('#') && href!=='#auth'){ const target=document.querySelector(href); if(target){ auth.classList.remove('show'); document.body.style.overflow=''; target.scrollIntoView({behavior:'smooth',block:'start'}); } }
  },true);
  document.getElementById('topLoginBtn')?.addEventListener('click',()=>showLogin());
  document.getElementById('switchToLogin')?.addEventListener('click',()=>showLogin());
  document.getElementById('switchToRegister')?.addEventListener('click',()=>showRegister());
  document.querySelectorAll('.auth-eye').forEach(btn=>btn.addEventListener('click',()=>{ const input=btn.parentElement.querySelector('input'); input.type=input.type==='password'?'text':'password'; }));
  document.getElementById('registerForm')?.addEventListener('submit',function(e){e.preventDefault(); alert('Register berhasil. Silakan login.'); showLogin();});
  document.getElementById('loginForm')?.addEventListener('submit',function(e){e.preventDefault(); auth.classList.remove('show'); loader.classList.add('show'); setTimeout(()=>{ window.location.href='https://toopai.ai/creator/dashboard'; },3000);});
  if(location.hash==='#auth') showAuth('register');
})();

/* Script block 16 id="toopai-nav-pin-functional-fix" */
(function(){
  const map={product:'product',agents:'agents',solutions:'solutions',case:'case',home:'home'};
  function showMain(){
    const auth=document.getElementById('toopaiAuthPage');
    if(auth) auth.classList.remove('show');
    document.body.style.overflow='';
  }
  function scrollToId(id){
    showMain();
    const el=document.getElementById(id);
    if(el){
      history.replaceState(null,'','#'+id);
      el.scrollIntoView({behavior:'smooth',block:'start'});
      setActive(id);
    }
  }
  function setActive(id){
    document.querySelectorAll('.navlinks a,.mobile-bottom-nav a').forEach(a=>{
      const h=(a.getAttribute('href')||'').replace('#','');
      a.classList.toggle('active',h===id);
    });
  }
  document.addEventListener('click',function(e){
    const link=e.target.closest('a[href^="#"]');
    if(!link) return;
    const id=(link.getAttribute('href')||'').slice(1);
    if(map[id]){ e.preventDefault(); e.stopPropagation(); scrollToId(id); }
  },true);
  const observer=new IntersectionObserver((entries)=>{
    const visible=entries.filter(x=>x.isIntersecting).sort((a,b)=>b.intersectionRatio-a.intersectionRatio)[0];
    if(visible) setActive(visible.target.id);
  },{threshold:[.25,.45,.65],rootMargin:'-90px 0px -45% 0px'});
  Object.keys(map).forEach(id=>{ const el=document.getElementById(id); if(el) observer.observe(el); });
})();

/* Script block 17 id="toopai-exact-auth-flow-js" */
(function(){
  const overlay=document.getElementById('exactAuthOverlay');
  const oldAuth=document.getElementById('toopaiAuthPage');
  const loader=document.getElementById('toopaiLoadingScreen');
  function showExactAuth(mode){
    if(oldAuth) oldAuth.classList.remove('show');
    if(loader) loader.classList.remove('show');
    overlay.classList.add('show');
    overlay.setAttribute('aria-hidden','false');
    document.body.style.overflow='hidden';
    // set mode inside iframe after ready
    const f=document.getElementById('exactAuthFrame');
    setTimeout(()=>{
      try{
        if(mode==='login' && f.contentWindow.showLogin) f.contentWindow.showLogin();
        if(mode==='register' && f.contentWindow.showRegister) f.contentWindow.showRegister();
      }catch(e){}
    },120);
  }
  function hideExactAuth(){
    overlay.classList.remove('show');
    overlay.setAttribute('aria-hidden','true');
    document.body.style.overflow='';
    if(oldAuth) oldAuth.classList.remove('show');
    history.replaceState(null,'',location.pathname+location.search+'#home');
    document.getElementById('home')?.scrollIntoView({behavior:'smooth',block:'start'});
  }
  window.addEventListener('message',function(ev){
    if(ev.data && ev.data.type==='toopai-home') hideExactAuth();
  });
  document.addEventListener('click',function(e){
    const el=e.target.closest('a,button'); if(!el) return;
    const text=(el.textContent||'').trim().toLowerCase();
    const href=el.getAttribute('href')||'';
    const isGetStarted=text.includes('get started');
    const isSignup=text==='sign up' || text.includes('register') || href.includes('creator_auth/register');
    const isLogin=text==='log in' || text==='login here' || href==='#login';
    const isLogo=el.classList.contains('toopai-home-logo') || el.classList.contains('logo') || el.closest('.logo');
    if(isLogo && overlay.classList.contains('show')){ e.preventDefault(); e.stopImmediatePropagation(); hideExactAuth(); return; }
    if(isGetStarted || isSignup){ e.preventDefault(); e.stopImmediatePropagation(); showExactAuth('register'); return; }
    if(isLogin){ e.preventDefault(); e.stopImmediatePropagation(); showExactAuth('login'); return; }
  },true);
  // expose for debugging
  window.showToopaiExactAuth=showExactAuth;
  window.hideToopaiExactAuth=hideExactAuth;
})();
