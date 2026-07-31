/**
 * TOOPAI VOICE ASSISTANT - FINAL STABLE
 * ======================================
 * - Always listening after user gesture
 * - JSON-safe backend handling
 * - Gemini process modal
 * - Speaks only after all data/AI is complete
 * - Every response shows modal
 * - Supports insight, chart, anomaly, table, metrics, generic answer
 */

(function () {
    'use strict';

    const BASE_URL = window.location.origin + '/';

    const TRIGGER_PHRASES = [
        'hi toopai',
        'hai toopai',
        'hello toopai',
        'hey toopai',
        'halo toopai',
        'toopai',
        'hei tupai',
        'hai tupai',
        'halo tupai',
        'assistant',
        'asisten'
    ];

    const ROLE = document.body.getAttribute('data-role') || 'admin';

    let isActive = false;
    let isListening = false;
    let isRestarting = false;
    let isProcessingQuery = false;

    let recognition = null;
    let synthesis = window.speechSynthesis;
    let maskotElement = null;
    let statusElement = null;
    let restartTimer = null;
    let wakeLockTimer = null;
    let conversationHistory = [];

    function init() {
        console.log('🤖 Toopai Voice Assistant Final Initializing...');

        createMaskot();

        if (('SpeechRecognition' in window) || ('webkitSpeechRecognition' in window)) {
            document.addEventListener('click', function unlockSpeech() {
                try {
                    const tempRec = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
                    tempRec.continuous = false;

                    tempRec.onstart = function () {
                        try { tempRec.stop(); } catch (e) {}
                        console.log('🔓 Speech recognition unlocked');
                    };

                    tempRec.onerror = function () {
                        console.log('🔓 Speech recognition unlock attempted');
                    };

                    tempRec.start();

                } catch (err) {
                    console.log('🔓 Unlock attempt:', err.message);
                }

                setTimeout(() => {
                    startPassiveListening();
                }, 500);

                document.removeEventListener('click', unlockSpeech);
            }, { once: true });

            setTimeout(() => {
                if (!isActive && !recognition) {
                    showClickHint();
                }
            }, 5000);

        } else {
            console.warn('⚠️ Speech Recognition not supported');
            createFallbackButton();
        }

        if (synthesis) {
            synthesis.onvoiceschanged = () => {
                console.log('🔊 Voices loaded:', synthesis.getVoices().length);
            };
        }

        document.addEventListener('visibilitychange', handleVisibilityChange);

        window.addEventListener('beforeunload', () => {
            if (recognition) {
                try { recognition.stop(); } catch (e) {}
            }
            if (synthesis) {
                synthesis.cancel();
            }
        });

        console.log('✅ Toopai Voice Assistant Ready');
    }

    function showClickHint() {
        if (document.getElementById('toopai-hint')) return;

        const hint = document.createElement('div');
        hint.id = 'toopai-hint';
        hint.style.cssText = `
            position:fixed;
            bottom:120px;
            right:30px;
            z-index:99998;
            background:rgba(0,0,0,.9);
            color:#fff;
            padding:12px 18px;
            border-radius:16px;
            font-size:12px;
            pointer-events:none;
            font-family:'Segoe UI',sans-serif;
        `;
        hint.textContent = '👆 Klik di mana saja untuk mengaktifkan asisten suara';
        document.body.appendChild(hint);

        setTimeout(() => {
            if (hint.parentNode) hint.remove();
        }, 5000);
    }

    function createMaskot() {
        maskotElement = document.createElement('div');
        maskotElement.id = 'toopai-maskot';

        maskotElement.innerHTML = `
            <div class="toopai-mascot-container">
                <div class="toopai-body">
                    <div class="toopai-ear left-ear"></div>
                    <div class="toopai-ear right-ear"></div>
                    <div class="toopai-face">
                        <div class="toopai-eyes">
                            <div class="toopai-eye left"></div>
                            <div class="toopai-eye right"></div>
                        </div>
                        <div class="toopai-nose"></div>
                        <div class="toopai-mouth"></div>
                    </div>
                </div>
                <div class="toopai-ring"></div>
                <div class="toopai-pulse"></div>
            </div>
            <div class="toopai-status" id="toopai-status">🎤 Siaga</div>
        `;

        document.body.appendChild(maskotElement);
        statusElement = document.getElementById('toopai-status');

        maskotElement.addEventListener('click', () => {
            if (!isActive) {
                activate();
            } else {
                deactivate();
            }
        });

        const style = document.createElement('style');
        style.textContent = getMaskotStyles();
        document.head.appendChild(style);
    }

    function getMaskotStyles() {
        return `
            #toopai-maskot {
                position:fixed;
                bottom:24px;
                right:24px;
                z-index:99999;
                cursor:pointer;
                transition:all .3s ease;
                user-select:none;
            }

            #toopai-maskot:hover {
                transform:scale(1.08);
            }

            .toopai-mascot-container {
                position:relative;
                width:90px;
                height:90px;
            }

            .toopai-body {
                width:64px;
                height:64px;
                background:linear-gradient(135deg,#8b39ff,#3b1ddb);
                border-radius:50%;
                position:absolute;
                top:50%;
                left:50%;
                transform:translate(-50%,-50%);
                display:flex;
                align-items:center;
                justify-content:center;
                box-shadow:0 8px 32px rgba(139,57,255,.5);
                z-index:2;
                transition:all .3s;
            }

            .toopai-ear {
                position:absolute;
                width:16px;
                height:20px;
                background:linear-gradient(135deg,#7f30ee,#2f15c0);
                border-radius:50% 50% 0 0;
                top:-8px;
                z-index:1;
            }

            .left-ear {
                left:10px;
                transform:rotate(-20deg);
            }

            .right-ear {
                right:10px;
                transform:rotate(20deg);
            }

            #toopai-maskot.listening .toopai-ear {
                animation:ear-wiggle .4s infinite;
            }

            .toopai-face {
                text-align:center;
                position:relative;
                z-index:3;
            }

            .toopai-eyes {
                display:flex;
                gap:12px;
                justify-content:center;
                margin-bottom:6px;
            }

            .toopai-eye {
                width:10px;
                height:14px;
                background:white;
                border-radius:50%;
                transition:all .2s;
            }

            #toopai-maskot.listening .toopai-eye {
                background:#4ade80;
                box-shadow:0 0 8px #4ade80;
            }

            #toopai-maskot.speaking .toopai-eye {
                background:#fbbf24;
                box-shadow:0 0 8px #fbbf24;
            }

            #toopai-maskot.processing .toopai-eye {
                background:#38bdf8;
                box-shadow:0 0 8px #38bdf8;
            }

            #toopai-maskot.error .toopai-eye {
                background:#ef4444;
                box-shadow:0 0 8px #ef4444;
            }

            .toopai-nose {
                width:6px;
                height:6px;
                background:#ff6b9d;
                border-radius:50%;
                margin:0 auto 4px;
            }

            .toopai-mouth {
                width:18px;
                height:4px;
                background:white;
                border-radius:2px;
                margin:0 auto;
                transition:all .2s;
            }

            #toopai-maskot.speaking .toopai-mouth {
                animation:mouth-talk .2s infinite;
            }

            #toopai-maskot.active .toopai-mouth {
                height:8px;
                border-radius:50%;
            }

            .toopai-ring {
                position:absolute;
                top:50%;
                left:50%;
                transform:translate(-50%,-50%);
                width:84px;
                height:84px;
                border-radius:50%;
                border:2px solid rgba(139,57,255,.3);
                z-index:1;
            }

            .toopai-pulse {
                position:absolute;
                top:50%;
                left:50%;
                transform:translate(-50%,-50%);
                width:100px;
                height:100px;
                border-radius:50%;
                background:rgba(139,57,255,.1);
                z-index:0;
                opacity:0;
            }

            #toopai-maskot.listening .toopai-pulse,
            #toopai-maskot.processing .toopai-pulse {
                animation:pulse-ring .6s ease-out infinite;
            }

            #toopai-maskot.speaking .toopai-pulse {
                animation:pulse-ring .4s ease-out infinite;
                background:rgba(251,191,36,.15);
            }

            .toopai-status {
                position:absolute;
                bottom:-28px;
                left:50%;
                transform:translateX(-50%);
                background:rgba(0,0,0,.85);
                color:white;
                padding:4px 10px;
                border-radius:10px;
                font-size:9px;
                white-space:nowrap;
                opacity:0;
                transition:opacity .3s;
            }

            #toopai-maskot:hover .toopai-status,
            #toopai-maskot.listening .toopai-status,
            #toopai-maskot.active .toopai-status,
            #toopai-maskot.processing .toopai-status,
            #toopai-maskot.speaking .toopai-status {
                opacity:1;
            }

            @keyframes ear-wiggle {
                0%,100% { transform:rotate(-20deg); }
                50% { transform:rotate(-32deg); }
            }

            @keyframes mouth-talk {
                0%,100% { height:4px; }
                50% { height:8px; }
            }

            @keyframes pulse-ring {
                0% { transform:translate(-50%,-50%) scale(.8); opacity:1; }
                100% { transform:translate(-50%,-50%) scale(1.4); opacity:0; }
            }
        `;
    }

    function startPassiveListening() {
        if (isRestarting || isProcessingQuery) {
            console.log('⏸️ Skip listening start');
            return;
        }

        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) return;

        isRestarting = true;

        if (recognition) {
            recognition.onend = null;
            recognition.onresult = null;
            recognition.onerror = null;
            try { recognition.abort(); } catch (e) {}
            recognition = null;
        }

        recognition = new SpeechRecognition();
        recognition.lang = 'id-ID';
        recognition.continuous = false;
        recognition.interimResults = true;
        recognition.maxAlternatives = 3;

        recognition.onresult = function (event) {
            let transcript = '';

            for (let i = event.resultIndex; i < event.results.length; i++) {
                transcript += event.results[i][0].transcript;
            }

            transcript = transcript.toLowerCase().trim();

            if (!transcript) return;

            console.log('👂 Heard:', transcript);

            const isTrigger = TRIGGER_PHRASES.some(phrase => transcript.includes(phrase));

            if (isTrigger && !isActive) {
                console.log('🎯 Trigger detected');
                activate();
                return;
            }

            const lastResult = event.results[event.results.length - 1];

            if (isActive && isListening && lastResult.isFinal) {
                console.log('🎤 Processing:', transcript);
                processQuery(transcript);
            }
        };

        recognition.onerror = function (event) {
            console.warn('🎤 Error:', event.error);

            if (event.error === 'aborted') {
                console.log('⏹️ Aborted normal');
                return;
            }

            if (event.error === 'no-speech') {
                console.log('🤫 No speech detected');

                isRestarting = false;

                setTimeout(() => {
                    if (isActive && !isProcessingQuery) {
                        startPassiveListening();
                    }
                }, 900);

                return;
            }

            if (event.error === 'audio-capture') {
                updateStatus('🎤 Mikrofon tidak ditemukan');
                return;
            }

            if (event.error === 'not-allowed') {
                updateStatus('🚫 Izin mikrofon ditolak');
                return;
            }

            isRestarting = false;

            setTimeout(() => {
                if (!isProcessingQuery) {
                    startPassiveListening();
                }
            }, 1500);
        };

        recognition.onend = function () {
            console.log('👂 Recognition ended');

            isRestarting = false;

            if (!isActive && !isProcessingQuery) {
                setTimeout(() => {
                    startPassiveListening();
                }, 1500);
            }
        };

        try {
            recognition.start();
            console.log('✅ Passive listening active');
            updateStatus(isActive ? '👂 Mendengarkan...' : '🎤 Siaga');
            maskotElement.classList.remove('error');

        } catch (e) {
            console.error('❌ Start failed:', e);
            isRestarting = false;

            setTimeout(() => {
                if (!isProcessingQuery) {
                    startPassiveListening();
                }
            }, 2000);
        }
    }

    function activate() {
        if (isActive) return;

        isActive = true;
        isListening = true;

        maskotElement.classList.add('active', 'listening');
        updateStatus('👂 Mendengarkan...');

        playBeep();

        if (recognition) {
            try { recognition.stop(); } catch (e) {}
            setTimeout(() => {
                if (!isProcessingQuery) {
                    startPassiveListening();
                }
            }, 300);
        }

        setTimeout(() => {
            if (isActive && !isProcessingQuery) {
                const hour = new Date().getHours();
                const greet = hour < 12 ? 'pagi' : (hour < 15 ? 'siang' : (hour < 19 ? 'sore' : 'malam'));
                speak(`Selamat ${greet}. Saya Toopai, asisten Anda. Ada yang bisa saya bantu?`);
            }
        }, 800);

        resetWakeLock();
    }

    function deactivate() {
        console.log('🔴 Deactivating');

        isActive = false;
        isListening = false;
        isRestarting = false;

        maskotElement.classList.remove('active', 'listening', 'speaking', 'processing');
        updateStatus('🎤 Siaga');

        if (wakeLockTimer) clearTimeout(wakeLockTimer);
        if (restartTimer) clearTimeout(restartTimer);

        if (recognition) {
            recognition.onend = null;
            recognition.onresult = null;
            recognition.onerror = null;
            try { recognition.abort(); } catch (e) {}
            recognition = null;
        }

        setTimeout(() => {
            if (!isProcessingQuery) {
                startPassiveListening();
            }
        }, 1500);
    }

    async function processQuery(transcript) {
        if (!transcript || !transcript.trim()) return;

        if (isProcessingQuery) {
            console.log('⏳ Query still processing, skipped:', transcript);
            return;
        }

        isProcessingQuery = true;
        isListening = false;
        isRestarting = false;

        if (recognition) {
            recognition.onend = null;
            recognition.onresult = null;
            recognition.onerror = null;
            try { recognition.abort(); } catch (e) {}
        }

        const isAnalysisRequest = isAnalysisIntent(transcript);

        maskotElement.classList.remove('listening', 'speaking', 'error');
        maskotElement.classList.add('processing');
        updateStatus('🤔 Memproses...');

        showAssistantProcessModal(
            'searching',
            isAnalysisRequest
                ? 'Mencari data dari database Toopai...'
                : 'Memproses pertanyaan Anda...'
        );

        resetWakeLock();

        conversationHistory.push({
            role: 'user',
            text: transcript,
            time: new Date()
        });

        try {
            updateAssistantProcessModal('searching', 'Mengambil data yang relevan...');

            const response = await fetch(BASE_URL + 'voice_assistant/process_query', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    question: transcript,
                    role: ROLE,
                    context: JSON.stringify(conversationHistory.slice(-5))
                })
            });

            if (!response.ok) {
                throw new Error('Network error: ' + response.status);
            }

            updateAssistantProcessModal(
                isAnalysisRequest ? 'gemini' : 'analyzing',
                isAnalysisRequest
                    ? 'Data ditemukan. Gemini sedang menyusun analisa...'
                    : 'Menyiapkan jawaban...'
            );

            const rawText = await response.text();

            let result;
            try {
                result = JSON.parse(rawText);
            } catch (e) {
                console.error('Invalid JSON response:', rawText);

                closeAssistantProcessModal();

                showAssistantErrorModal(
                    'Response server tidak valid',
                    'Server mengembalikan HTML, bukan JSON. Cek error PHP atau session login.'
                );

                await speak('Maaf, server mengembalikan response tidak valid.');
                return;
            }

            if (!result || !result.success) {
                closeAssistantProcessModal();

                const errorAnswer = result?.answer || 'Maaf, saya belum bisa memahami permintaan itu.';
                showAssistantErrorModal('Toopai Error', errorAnswer);
                await speak(cleanTextForSpeech(errorAnswer));
                return;
            }

            conversationHistory.push({
                role: 'assistant',
                text: result.answer,
                time: new Date()
            });

            updateAssistantProcessModal('done', 'Selesai. Menampilkan hasil...');

            await delay(350);
            closeAssistantProcessModal();

            renderResponseModal(result);

            maskotElement.classList.remove('processing');
            maskotElement.classList.add('speaking');
            updateStatus('🔊 Membacakan hasil...');

            await speak(cleanTextForSpeech(result.answer));

        } catch (error) {
            console.error('Query error:', error);

            closeAssistantProcessModal();

            maskotElement.classList.remove('processing');
            maskotElement.classList.add('error');

            showAssistantErrorModal(
                'Terjadi Kesalahan',
                'Koneksi atau proses analisa gagal. Silakan coba lagi.'
            );

            await speak('Maaf, terjadi kesalahan koneksi atau proses analisa.');
            maskotElement.classList.remove('error');

        } finally {
            isProcessingQuery = false;
            resumeAssistantListening();
        }
    }

    function renderResponseModal(result) {
        const action = result.action || 'show_answer';

        if (action === 'show_chart' && result.chart) {
            showChartModal(result.chart, result.answer || '');
            return;
        }

        if (action === 'show_insight') {
            showInsightCard(result.data || {}, result.answer || '');
            return;
        }

        if (action === 'show_anomaly') {
            showAnomalyModal(result.data || {}, result.answer || '');
            return;
        }

        if (action === 'show_table') {
            showTableModal(result.data || {}, result.answer || '');
            return;
        }

        if (action === 'show_metric') {
            showMetricModal(result.data || {}, result.answer || '');
            return;
        }

        if (action === 'show_error') {
            showAssistantErrorModal('Toopai Error', result.answer || 'Terjadi kesalahan.');
            return;
        }

        showGenericAnswerModal('Jawaban Toopai', result.answer || '', result.data || {});
    }

    function isAnalysisIntent(text) {
        return /analisa|analisis|insight|kenapa|mengapa|grafik|chart|rekomendasi|anomali|anomaly|trend|performa|banding|turun|naik|detail|gemini|ai|lebih/i.test(text || '');
    }

    function resumeAssistantListening() {
        maskotElement.classList.remove('speaking', 'processing', 'error');
        maskotElement.classList.add('active', 'listening');

        isActive = true;
        isListening = true;
        isRestarting = false;

        updateStatus('👂 Mendengarkan...');
        resetWakeLock();

        setTimeout(() => {
            if (!isProcessingQuery) {
                startPassiveListening();
            }
        }, 700);
    }

    function showAssistantProcessModal(step = 'searching', detail = 'Memproses permintaan...') {
        let modal = document.getElementById('toopai-process-modal');

        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'toopai-process-modal';
            modal.style.cssText = `
                position:fixed;
                inset:0;
                z-index:100001;
                background:rgba(0,0,0,.72);
                backdrop-filter:blur(10px);
                display:flex;
                align-items:center;
                justify-content:center;
                font-family:'Segoe UI',system-ui,sans-serif;
                padding:18px;
            `;
            document.body.appendChild(modal);
        }

        updateAssistantProcessModal(step, detail);
    }

    function updateAssistantProcessModal(step = 'searching', detail = 'Memproses...') {
        const modal = document.getElementById('toopai-process-modal');
        if (!modal) return;

        const steps = {
            searching: {
                icon: '🔎',
                title: 'Mencari Data',
                desc: detail,
                width: '35%'
            },
            analyzing: {
                icon: '📊',
                title: 'Menganalisa Data',
                desc: detail,
                width: '60%'
            },
            gemini: {
                icon: '🧠',
                title: 'Gemini Sedang Bekerja',
                desc: detail,
                width: '82%'
            },
            done: {
                icon: '✅',
                title: 'Selesai',
                desc: detail,
                width: '100%'
            }
        };

        const current = steps[step] || steps.searching;

        modal.innerHTML = `
            <div style="
                width:92%;
                max-width:470px;
                border-radius:24px;
                background:linear-gradient(160deg,rgba(19,33,59,.98),rgba(6,14,27,.98));
                border:1px solid rgba(156,107,255,.38);
                box-shadow:0 24px 60px rgba(0,0,0,.45),0 0 35px rgba(124,60,255,.24);
                padding:26px;
                color:#fff;
                text-align:center;
            ">
                <div style="
                    width:76px;
                    height:76px;
                    border-radius:50%;
                    display:grid;
                    place-items:center;
                    margin:0 auto 18px;
                    background:linear-gradient(135deg,#8b39ff,#3b1ddb);
                    box-shadow:0 0 34px rgba(139,57,255,.45);
                    font-size:34px;
                    animation:toopaiPulse 1.15s ease-in-out infinite;
                ">
                    ${current.icon}
                </div>

                <h3 style="margin:0 0 8px;font-size:20px;">
                    ${escapeHtml(current.title)}
                </h3>

                <p style="margin:0;color:#b7c1d6;font-size:14px;line-height:1.6;">
                    ${escapeHtml(current.desc)}
                </p>

                <div style="
                    margin-top:20px;
                    height:6px;
                    border-radius:999px;
                    overflow:hidden;
                    background:rgba(255,255,255,.08);
                ">
                    <div style="
                        height:100%;
                        width:${current.width};
                        border-radius:999px;
                        background:linear-gradient(90deg,#7c3cff,#10dff0);
                        transition:width .35s ease;
                    "></div>
                </div>

                <small style="display:block;margin-top:12px;color:#8e9bb6;">
                    Asisten akan berbicara setelah semua proses selesai.
                </small>
            </div>

            <style>
                @keyframes toopaiPulse {
                    0%,100% { transform:scale(1); }
                    50% { transform:scale(1.08); }
                }
            </style>
        `;

        modal.style.display = 'flex';
    }

    function closeAssistantProcessModal() {
        const modal = document.getElementById('toopai-process-modal');
        if (modal) modal.remove();
    }

    function showInsightCard(data = {}, answer = '') {
        const metrics = [
            ['GMV Hari Ini', formatRupiah(data.today_gmv || 0)],
            ['GMV 7 Hari', formatRupiah(data.weekly_gmv || 0)],
            ['Order Hari Ini', formatNumber(data.total_orders_today || 0)],
            ['Creator Aktif Hari Ini', formatNumber(data.today_active_creators || 0)],
            ['Total Creator Aktif', formatNumber(data.active_creators || 0)],
            ['Campaign Aktif', formatNumber(data.active_campaigns || 0)],
            ['Growth GMV', `${Number(data.gmv_growth_percent || 0)}%`]
        ];

        let extraHtml = '';

        if (Array.isArray(data.top_creators) && data.top_creators.length) {
            extraHtml += buildSmallList('Top Creator 30 Hari', data.top_creators.map(c => [
                '@' + (c.creator_username || '-'),
                formatRupiah(c.gmv || c.total_gmv || 0)
            ]));
        }

        if (Array.isArray(data.top_products) && data.top_products.length) {
            extraHtml += `
<div class="top-section">
<h4>🏆 Top Creator 30 Hari</h4>

${data.top_creators.map((c,index)=>`
<div class="creator-row">
    <div>
        <strong>${index+1}. @${c.creator_username}</strong>
        <div>Orders : ${formatNumber(c.orders || 0)}</div>
    </div>

    <div>
        ${formatRupiah(c.gmv || c.total_gmv || 0)}
    </div>
</div>
`).join('')}
</div>
`;
        }

        showBaseModal({
            title: '🧠 AI Insight Toopai',
            answer,
            bodyHtml: buildMetricGrid(metrics) + extraHtml
        });
    }

    function showAnomalyModal(data = {}, answer = '') {
        const anomalies = Array.isArray(data.anomalies) ? data.anomalies : [];

        let bodyHtml = buildMetricGrid([
            ['Average GMV', formatRupiah(data.average_gmv || 0)],
            ['Std Deviasi', formatRupiah(data.std_dev || 0)],
            ['Total Anomali', formatNumber(anomalies.length)]
        ]);

        bodyHtml += `
            <div style="background:#0f172a;border-radius:14px;overflow:hidden;border:1px solid #1e293b;margin-top:14px;">
                ${
                    anomalies.length === 0
                        ? `<div style="padding:16px;color:#94a3b8;">✅ Tidak ada anomali signifikan.</div>`
                        : anomalies.map(item => `
                            <div style="
                                display:grid;
                                grid-template-columns:1fr auto;
                                gap:10px;
                                padding:13px 14px;
                                border-bottom:1px solid #1e293b;
                            ">
                                <div>
                                    <strong style="color:${item.type === 'high' ? '#4ade80' : '#f87171'};">
                                        ${item.type === 'high' ? '🟢' : '🔴'} ${escapeHtml(item.label || 'Anomali')}
                                    </strong>
                                    <div style="color:#94a3b8;font-size:12px;margin-top:3px;">
                                        ${escapeHtml(formatToopaiDate(item.date))}
                                    </div>
                                </div>
                                <strong style="color:#fff;">
                                    ${formatRupiah(item.gmv || 0)}
                                </strong>
                            </div>
                        `).join('')
                }
            </div>
        `;

        showBaseModal({
            title: '🔍 Anomaly Detection',
            answer,
            bodyHtml
        });
    }

    function showChartModal(chartData = {}, answer = '') {
        const canvasId = 'chart-' + Date.now();

        showBaseModal({
            title: '📊 ' + escapeHtml(chartData.title || 'Chart Toopai'),
            answer,
            bodyHtml: `<canvas id="${canvasId}" height="300"></canvas>`,
            maxWidth: 760
        });

        setTimeout(() => {
            const ctx = document.getElementById(canvasId);

            if (ctx && typeof Chart !== 'undefined') {
                new Chart(ctx, {
                    type: chartData.type || 'line',
                    data: {
                        labels: chartData.labels || [],
                        datasets: chartData.datasets || []
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                labels: { color: '#94a3b8' }
                            }
                        },
                        scales: chartData.type === 'doughnut' ? {} : {
                            y: {
                                ticks: {
                                    color: '#94a3b8',
                                    callback: v => Number(v) >= 1000000 ? 'Rp ' + (v / 1000000).toFixed(1) + 'M' : v
                                },
                                grid: { color: '#1e293b' }
                            },
                            x: {
                                ticks: { color: '#94a3b8' },
                                grid: { color: '#1e293b' }
                            }
                        }
                    }
                });
            }
        }, 300);
    }

    function showTableModal(data = {}, answer = '') {
        const columns = Array.isArray(data.columns) ? data.columns : [];
        const rows = Array.isArray(data.rows) ? data.rows : [];

        const tableHtml = `
            <div style="overflow:auto;background:#0f172a;border-radius:14px;border:1px solid #1e293b;">
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr>
                            ${columns.map(col => `
                                <th style="text-align:left;padding:10px;color:#94a3b8;border-bottom:1px solid #1e293b;">
                                    ${escapeHtml(col)}
                                </th>
                            `).join('')}
                        </tr>
                    </thead>
                    <tbody>
                        ${
                            rows.length === 0
                                ? `<tr><td colspan="${columns.length || 1}" style="padding:14px;color:#94a3b8;">Tidak ada data.</td></tr>`
                                : rows.map(row => `
                                    <tr>
                                        ${row.map(cell => `
                                            <td style="padding:10px;color:#e2f0e8;border-bottom:1px solid #1e293b;">
                                                ${escapeHtml(cell)}
                                            </td>
                                        `).join('')}
                                    </tr>
                                `).join('')
                        }
                    </tbody>
                </table>
            </div>
        `;

        showBaseModal({
            title: data.title || 'Data Toopai',
            answer,
            bodyHtml: tableHtml,
            maxWidth: 760
        });
    }

    function showMetricModal(data = {}, answer = '') {
        const metrics = Array.isArray(data.metrics)
            ? data.metrics.map(m => [m.label, m.value])
            : [];

        showBaseModal({
            title: data.title || 'Metric Toopai',
            answer,
            bodyHtml: buildMetricGrid(metrics)
        });
    }

    function showGenericAnswerModal(title = 'Jawaban Toopai', answer = '', data = {}) {
        showBaseModal({
            title,
            answer,
            bodyHtml: ''
        });
    }

    function showAssistantErrorModal(title, message) {
        showBaseModal({
            title: '⚠️ ' + title,
            answer: message,
            bodyHtml: ''
        });
    }

    function showBaseModal({ title, answer = '', bodyHtml = '', maxWidth = 680 }) {
        let modal = document.getElementById('toopai-modal');

        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'toopai-modal';
            document.body.appendChild(modal);
        }

        modal.style.cssText = `
            position:fixed;
            inset:0;
            z-index:100000;
            background:rgba(0,0,0,.72);
            backdrop-filter:blur(10px);
            display:flex;
            align-items:center;
            justify-content:center;
            padding:18px;
        `;

        modal.onclick = (e) => {
            if (e.target === modal) modal.remove();
        };

        modal.innerHTML = `
            <div style="
                width:92%;
                max-width:${maxWidth}px;
                max-height:84vh;
                overflow:auto;
                border-radius:22px;
                background:#111827;
                border:1px solid #2a3346;
                color:#e2f0e8;
                padding:24px;
                font-family:'Segoe UI',system-ui,sans-serif;
                box-shadow:0 24px 60px rgba(0,0,0,.45);
            ">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:16px;">
                    <h3 style="margin:0;font-size:18px;">${escapeHtml(title || 'Toopai')}</h3>
                    <button onclick="this.closest('#toopai-modal').remove()" style="
                        background:transparent;
                        border:none;
                        color:#fff;
                        font-size:22px;
                        cursor:pointer;
                        line-height:1;
                    ">✕</button>
                </div>

                ${
                    answer
                        ? `
                            <div style="
                                background:rgba(124,60,255,.12);
                                border:1px solid rgba(156,107,255,.28);
                                border-radius:16px;
                                padding:16px;
                                color:#dbeafe;
                                line-height:1.7;
                                font-size:14px;
                                margin-bottom:16px;
                                white-space:pre-line;
                            ">
                                ${escapeHtml(answer)}
                            </div>
                        `
                        : ''
                }

                ${bodyHtml || ''}

                <button onclick="this.closest('#toopai-modal').remove()" style="
                    margin-top:18px;
                    width:100%;
                    background:#1e293b;
                    border:1px solid #2a3346;
                    color:#fff;
                    padding:12px;
                    border-radius:12px;
                    cursor:pointer;
                    font-size:14px;
                    font-weight:700;
                ">
                    Tutup
                </button>
            </div>
        `;

        modal.style.display = 'flex';
    }

    function buildMetricGrid(metrics) {
        if (!Array.isArray(metrics) || metrics.length === 0) return '';

        return `
            <div style="
                display:grid;
                grid-template-columns:repeat(2,minmax(0,1fr));
                gap:12px;
            ">
                ${metrics.map(([label, value]) => `
                    <div style="background:#0f172a;padding:14px;border-radius:14px;border:1px solid #1e293b;">
                        <small style="display:block;color:#94a3b8;font-size:12px;margin-bottom:6px;">
                            ${escapeHtml(label)}
                        </small>
                        <strong style="display:block;color:#fff;font-size:16px;">
                            ${escapeHtml(value)}
                        </strong>
                    </div>
                `).join('')}
            </div>
        `;
    }

    function buildSmallList(title, items) {
        return `
            <div style="margin-top:14px;background:#0f172a;border-radius:14px;border:1px solid #1e293b;overflow:hidden;">
                <div style="padding:12px 14px;color:#fff;font-weight:700;border-bottom:1px solid #1e293b;">
                    ${escapeHtml(title)}
                </div>
                ${items.map(item => `
                    <div style="
                        display:grid;
                        grid-template-columns:1fr auto;
                        gap:10px;
                        padding:10px 14px;
                        border-bottom:1px solid #1e293b;
                        color:#dbeafe;
                        font-size:13px;
                    ">
                        <span>${escapeHtml(item[0])}</span>
                        <strong>${escapeHtml(item[1])}</strong>
                    </div>
                `).join('')}
            </div>
        `;
    }

    function speak(text) {
        return new Promise((resolve) => {
            if (!synthesis) {
                resolve();
                return;
            }

            const cleanText = cleanTextForSpeech(text);

            if (!cleanText) {
                resolve();
                return;
            }

            synthesis.cancel();

            const utterance = new SpeechSynthesisUtterance(cleanText);
            utterance.lang = 'id-ID';
            utterance.rate = 1.0;
            utterance.pitch = 1.05;
            utterance.volume = 1;

            const voices = synthesis.getVoices();
            const idVoice = voices.find(v => v.lang && (v.lang.startsWith('id') || v.lang === 'id-ID'));

            if (idVoice) {
                utterance.voice = idVoice;
            }

            utterance.onend = () => {
                maskotElement.classList.remove('speaking');
                resolve();
            };

            utterance.onerror = () => {
                maskotElement.classList.remove('speaking');
                resolve();
            };

            synthesis.speak(utterance);
        });
    }

    function cleanTextForSpeech(text) {
        return String(text || '')
            .replace(/\*\*/g, '')
            .replace(/\*/g, '')
            .replace(/#/g, '')
            .replace(/•/g, '')
            .replace(/[_`~]/g, '')
            .replace(/\n+/g, '. ')
            .replace(/\s+/g, ' ')
            .replace(/Rp\s+/g, 'Rupiah ')
            .trim();
    }

    function resetWakeLock() {
        if (wakeLockTimer) clearTimeout(wakeLockTimer);

        wakeLockTimer = setTimeout(() => {
            if (isActive && !isProcessingQuery) {
                speak('Saya akan tetap mendengarkan. Silakan lanjutkan pertanyaan.');
            }
        }, 30000);
    }

    function updateStatus(text) {
        if (statusElement) {
            statusElement.textContent = text;
        }
    }

    function playBeep() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.connect(gain);
            gain.connect(ctx.destination);

            osc.frequency.value = 800;
            gain.gain.value = 0.08;

            osc.start();
            osc.stop(ctx.currentTime + 0.12);
        } catch (e) {}
    }

    function handleVisibilityChange() {
        if (document.hidden) {
            if (recognition) {
                try { recognition.stop(); } catch (e) {}
            }
        } else {
            if (!isActive && !isProcessingQuery) {
                startPassiveListening();
            }
        }
    }

    function createFallbackButton() {
        if (document.getElementById('toopai-fallback-btn')) return;

        const btn = document.createElement('button');
        btn.id = 'toopai-fallback-btn';
        btn.innerHTML = '🎤';
        btn.style.cssText = `
            position:fixed;
            bottom:30px;
            right:30px;
            z-index:99999;
            width:60px;
            height:60px;
            border-radius:50%;
            background:linear-gradient(135deg,#8b39ff,#3b1ddb);
            border:none;
            color:white;
            font-size:24px;
            cursor:pointer;
            box-shadow:0 8px 24px rgba(139,57,255,.4);
            transition:all .3s ease;
        `;

        btn.onclick = () => isActive ? deactivate() : activate();
        btn.onmouseover = () => btn.style.transform = 'scale(1.1)';
        btn.onmouseout = () => btn.style.transform = 'scale(1)';

        document.body.appendChild(btn);
        updateStatus('📱 Ketuk untuk mulai');
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatNumber(value) {
        return Number(value || 0).toLocaleString('id-ID');
    }

    function formatRupiah(value) {
        return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
    }

    function formatToopaiDate(value) {
        if (!value) return '-';

        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return value;

        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
    }

    function delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();