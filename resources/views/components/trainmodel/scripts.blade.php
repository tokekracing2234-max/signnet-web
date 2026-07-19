<script src="https://cdn.jsdelivr.net/npm/@mediapipe/hands/hands.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.js"></script>

<script>
    function openModalGuide() {
        const el = document.getElementById('modal-guide');
        el.classList.add('active');
        el.querySelector('.modal-content').classList.remove('closing');
    }

    function closeModalGuide() {
        const el = document.getElementById('modal-guide');
        el.querySelector('.modal-content').classList.add('closing');
        setTimeout(() => el.classList.remove('active'), 280);
    }

    function openModalResult() {
        const el = document.getElementById('modal-result');
        el.classList.add('active');
        el.querySelector('.modal-content').classList.remove('closing');
    }

    function closeModalResult() {
        const el = document.getElementById('modal-result');
        const content = el.querySelector('.modal-content');
        content.classList.add('animate__animated', 'animate__fadeOutDown');
        setTimeout(() => {
            el.classList.remove('active');
            content.classList.remove('animate__animated', 'animate__fadeOutDown');
        }, 450);
    }

    // Tab switcher untuk modal hasil evaluasi (Huruf / Angka)
    function switchResultTab(kategori) {
        const isHuruf = kategori === 'huruf';

        document.getElementById('result-panel-huruf').classList.toggle('hidden', !isHuruf);
        document.getElementById('result-panel-angka').classList.toggle('hidden', isHuruf);

        document.getElementById('tab-btn-huruf').className = isHuruf
            ? 'px-5 py-1.5 rounded-lg text-[10px] font-black transition-all bg-indigo-600 text-white'
            : 'px-5 py-1.5 rounded-lg text-[10px] font-black transition-all text-indigo-900 dark:text-slate-400';
        document.getElementById('tab-btn-angka').className = !isHuruf
            ? 'px-5 py-1.5 rounded-lg text-[10px] font-black transition-all bg-indigo-600 text-white'
            : 'px-5 py-1.5 rounded-lg text-[10px] font-black transition-all text-indigo-900 dark:text-slate-400';
    }

    const htmlEl = document.documentElement;
    function applyTheme(theme) {
        htmlEl.setAttribute('data-theme', theme);
        theme === 'dark' ? htmlEl.classList.add('dark') : htmlEl.classList.remove('dark');
    }
    (function () { applyTheme(localStorage.getItem('theme') || 'dark'); })();
    document.addEventListener('DOMContentLoaded', () => {
        window.addEventListener('themeChanged', (e) => applyTheme(e.detail.theme));
    });

    let state = {
        mode: 'video',
        sampleCount: 0,
        sessionStats: {},
        isProcessing: false,
        isRecording: false,
        isPaused: false,
        handInFrame: false,
        lastResults: null
    };

    const DUA_TANGAN_LABELS = ['6','7','8','9','A','B','D','F','G','H','K','M','N','P','Q','S','T','W','X','Y'];

    const el = {
        alphabetGroup:   document.getElementById('alphabet-group'),
        digitGroup:      document.getElementById('digit-group'),
        logBox:          document.getElementById('log-box'),
        labelSelect:     document.getElementById('label-select'),
        labelManual:     document.getElementById('label-manual'),
        sampleCount:     document.getElementById('sample-count'),
        actionBtn:       document.getElementById('main-action-btn'),
        timerDisplay:    document.getElementById('timer-display'),
        statsContainer:  document.getElementById('label-stats-container'),
        modalResult:     document.getElementById('modal-result'),
        video:           document.getElementById('input_video'),
        canvas:          document.getElementById('output_canvas'),
        handCount:       document.getElementById('hand-count'),
        liveIndicator:   document.getElementById('live-indicator'),
        liveText:        document.getElementById('live-text'),
    };
    const ctx = el.canvas.getContext('2d');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    "ABCDEFGHIJKLMNOPQRSTUVWXYZ".split("").forEach(l =>
        el.alphabetGroup.innerHTML += `<option value="${l}">${l}</option>`);
    "0123456789".split("").forEach(d =>
        el.digitGroup.innerHTML += `<option value="${d}">${d}</option>`);

    function addLog(msg) {
        const time = new Date().toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
        el.logBox.insertAdjacentHTML('beforeend', `<div><span class="text-slate-600">[${time}]</span> > ${msg}</div>`);
        el.logBox.scrollTop = el.logBox.scrollHeight;
    }

    el.labelSelect.addEventListener('change', (e) => {
        el.labelManual.classList.toggle('hidden', e.target.value !== 'custom');
    });

    function switchMode(mode) {
        state.mode = mode;
        const isVideo = mode === 'video';
        document.getElementById('btn-mode-video').className = isVideo
            ? 'px-4 py-1.5 rounded-lg text-[10px] font-bold transition-all bg-indigo-600 text-white'
            : 'px-4 py-1.5 rounded-lg text-[10px] font-bold transition-all text-slate-400';
        document.getElementById('btn-mode-photo').className = !isVideo
            ? 'px-4 py-1.5 rounded-lg text-[10px] font-bold transition-all bg-indigo-600 text-white'
            : 'px-4 py-1.5 rounded-lg text-[10px] font-bold transition-all text-slate-400';
        document.getElementById('mode-desc').innerText = `Mode: ${isVideo ? 'Video' : 'Foto'} (Timer 5dtk)`;
        addLog(`Mode: ${mode.toUpperCase()}`);
    }

    function loadInitialData() {
        addLog("SISTEM: Menghubungkan ke database dataset...");
        fetch('/get-total-samples')
            .then(res => res.json())
            .then(data => {
                if (data.total !== undefined) {
                    state.sampleCount = data.total;
                    el.sampleCount.innerText = state.sampleCount;
                    addLog(`SISTEM: Berhasil memuat ${state.sampleCount} sampel.`);
                }
            })
            .catch(() => addLog("ERROR: Gagal sinkronisasi dataset awal."));
    }

    el.actionBtn.addEventListener('click', () => {
        if (state.isProcessing) return;
            let label = el.labelSelect.value === 'custom'
                ? el.labelManual.value.trim()
                : el.labelSelect.value;

        if (!label) {
            AppAlert.fire('warning', 'Label Kosong', 'Silakan pilih atau ketik label terlebih dahulu!');
            addLog("ERROR: Label belum ditentukan!");
            return;
        }

        if (!state.handInFrame) {
            AppAlert.fire('warning', 'Tangan Tidak Terdeteksi', 'Pastikan tangan terlihat di kamera sebelum merekam.');
            addLog("ERROR: Tangan tidak terlihat!");
            return;
        }
            jalankanProses(label.toUpperCase());
    });

    function jalankanProses(label) {
        state.isProcessing = true;
        el.timerDisplay.style.display = 'flex';
        let count = 5;
        el.timerDisplay.innerText = count;
        addLog(`SISTEM: Bersiap! Perekaman [${label}] dalam 5 detik...`);
            const countdown = setInterval(() => {
            count--;
            if (count <= 0) {
                clearInterval(countdown);
                el.timerDisplay.style.display = 'none';
                if (state.mode === 'photo') {
                    eksekusiSimpan(label);
                    state.isProcessing = false;
                } else {
                    mulaiAutoRecording(label);
                }
            } else {
                el.timerDisplay.innerText = count;
            }
        }, 1000);
    }

    function extractFeatures(results) {
        let features = new Array(126).fill(0);
        if (results.multiHandLandmarks && results.multiHandLandmarks.length > 0) {
            let handList = results.multiHandLandmarks.map((lm, i) => ({
                lm, label: results.multiHandedness[i].label
            })).sort((a, b) => a.label.localeCompare(b.label));

            const base_x = handList[0].lm[0].x;
            const base_y = handList[0].lm[0].y;
            const base_z = handList[0].lm[0].z;

            handList.forEach((hand, index) => {
                if (index < 2) {
                    let offset = index * 63;
                    hand.lm.forEach((lm, lmIdx) => {
                        let i = offset + (lmIdx * 3);
                        features[i]     = lm.x - base_x;
                        features[i + 1] = lm.y - base_y;
                        features[i + 2] = lm.z - base_z;
                    });
                }
            });
        }
        return features;
    }

    function updateLocalCount(label) {
        state.sampleCount++;
        state.sessionStats[label] = (state.sessionStats[label] || 0) + 1;
        el.sampleCount.innerText = state.sampleCount;
        updateUIStats();
    }

    function updateUIStats() {
        el.statsContainer.innerHTML = '';
        const sortedLabels = Object.keys(state.sessionStats).sort();
        if (sortedLabels.length === 0) {
            el.statsContainer.innerHTML = '<span class="text-slate-500 dark:text-slate-600">Kosong</span>';
            return;
        }
        el.statsContainer.innerHTML = sortedLabels.map(key => `
            <div class="flex justify-between bg-white/5 px-2 py-0.5 rounded border border-white/5 animate__animated animate__fadeIn">
                <span class="text-indigo-400">${key}:</span>
                <span class="text-white">${state.sessionStats[key]}</span>
            </div>
        `).join('');
    }

    function eksekusiSimpan(label) {
        if (!state.lastResults || !state.handInFrame) {
            AppAlert.fire('error', 'Gagal Menyimpan', 'Tangan tidak terdeteksi saat pengambilan gambar!');
            state.isProcessing = false;
            return;
        }

        const flash = document.getElementById('flash-overlay');
        flash.classList.add('flash-effect');
        setTimeout(() => flash.classList.remove('flash-effect'), 300);

        fetch('/collect-data', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ label, features: extractFeatures(state.lastResults) })
        })
        .then(res => {
            if (!res.ok) return res.json().then(d => Promise.reject(d));
            return res.json();
        })
        .then(() => {
            updateLocalCount(label);
            addLog(`SUKSES: [${label}] tersimpan ke database.`);
            AppAlert.fire('success', 'Data Tersimpan', `Sampel gestur <b>${label}</b> berhasil disimpan ke database.`);
            resetLabelSelection();
        })
        .catch((err) => {
            const msg = err?.message || 'Pastikan server Laravel berjalan dengan benar.';
            addLog(`ERROR: Gagal simpan — ${msg}`);
            AppAlert.fire('error', 'Gagal Menyimpan', msg);
        })
        .finally(() => { state.isProcessing = false; });
    }

    async function mulaiAutoRecording(label) {
        state.isRecording = true;
        state.isPaused    = false;
        let duration      = 30;  // detik
        let elapsed       = 0;
        let isAutoPaused  = false;

        const originalBtnHTML = el.actionBtn.innerHTML;
        const originalBtnClass = el.actionBtn.className;

        addLog(`RECORDING: Memulai sesi rekam [${label}]...`);

        const runCountdown = async (seconds) => {
            el.timerDisplay.style.display = 'flex';
            for (let i = seconds; i > 0; i--) {
                el.timerDisplay.innerText = i;
                await new Promise(res => setTimeout(res, 1000));
            }
            el.timerDisplay.style.display = 'none';
        };

        el.actionBtn.className = el.actionBtn.className
            .replace('bg-indigo-600', 'bg-rose-600')
            .replace('hover:bg-indigo-500', 'hover:bg-rose-500');
        el.actionBtn.innerHTML = `<i class="fa-solid fa-circle-dot animate-pulse"></i> <span class="text-sm">REKAMAN AKTIF...</span>`;

        el.actionBtn.onclick = async () => {
            if (!state.isPaused) {
                state.isPaused = true;
                el.actionBtn.innerHTML = `<i class="fa-solid fa-play"></i> <span class="text-sm">LANJUTKAN</span>`;
                el.actionBtn.className = el.actionBtn.className
                    .replace('bg-rose-600', 'bg-amber-500')
                    .replace('hover:bg-rose-500', 'hover:bg-amber-400');
                addLog("SISTEM: Jeda manual oleh pengguna.");
            } else {
                addLog("SISTEM: Bersiap melanjutkan rekaman...");
                await runCountdown(3);
                state.isPaused = false;
                el.actionBtn.innerHTML = `<i class="fa-solid fa-circle-dot animate-pulse"></i> <span class="text-sm">REKAMAN AKTIF...</span>`;
                el.actionBtn.className = el.actionBtn.className
                    .replace('bg-amber-500', 'bg-rose-600')
                    .replace('hover:bg-amber-400', 'hover:bg-rose-500');
            }
        };

        const recordLoop = setInterval(async () => {
            if (!state.isRecording) { clearInterval(recordLoop); return; }
            if (state.isPaused) return;

            const jmlTangan   = state.lastResults?.multiHandLandmarks?.length || 0;
            const butuhDua    = DUA_TANGAN_LABELS.includes(label);

            if ((butuhDua && jmlTangan < 2) || (!butuhDua && jmlTangan < 1)) {
                if (!isAutoPaused) {
                    isAutoPaused = true;
                    addLog(`<span class="text-rose-500">AUTO-PAUSE: Tangan tidak lengkap untuk label [${label}]!</span>`);
                    el.actionBtn.innerHTML = `<i class="fa-solid fa-hand"></i> <span class="text-sm">POSISIKAN TANGAN...</span>`;
                    el.actionBtn.className = el.actionBtn.className
                        .replace('bg-rose-600', 'bg-slate-500')
                        .replace('hover:bg-rose-500', '');
                }
                return;
            }

            if (isAutoPaused) {
                isAutoPaused = false;
                addLog("SISTEM: Tangan terdeteksi, melanjutkan rekaman...");
                state.isPaused = true;
                await runCountdown(3);
                state.isPaused = false;
                el.actionBtn.innerHTML = `<i class="fa-solid fa-circle-dot animate-pulse"></i> <span class="text-sm">REKAMAN AKTIF...</span>`;
                el.actionBtn.className = el.actionBtn.className
                    .replace('bg-slate-500', 'bg-rose-600');
                return;
            }

            if (state.lastResults && state.handInFrame) {
                fetch('/collect-data', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ label, features: extractFeatures(state.lastResults) })
                })
                .then(res => {
                    if (!res.ok) return res.json().then(d => Promise.reject(d));
                    return res.json();
                })
                .then(() => {
                    updateLocalCount(label);
                    if (state.sampleCount % 20 === 0)
                        addLog(`INFO: ${state.sampleCount} total sampel terkumpul...`);
                })
                .catch(err => {
                    addLog(`ERROR simpan frame: ${err?.message || 'unknown'}`);
                });

                elapsed += 500;
                if (elapsed >= duration * 1000) {
                    clearInterval(recordLoop);
                    finishRecording();
                }
            }
        }, 500);

        function finishRecording() {
            state.isRecording  = false;
            state.isProcessing = false;
            el.actionBtn.onclick  = null;
            el.actionBtn.className = originalBtnClass;
            el.actionBtn.innerHTML = originalBtnHTML;
            addLog(`SUKSES: Sesi rekam [${label}] selesai! Total sesi ini: ${state.sessionStats[label] || 0} sampel.`);
            AppAlert.fire('success', 'Rekam Selesai', `Sesi rekaman gestur <b>${label}</b> selesai. Data berhasil dikumpulkan.`);
            resetLabelSelection();
        }
    }

    function resetLabelSelection() {
        el.labelSelect.selectedIndex = 0;
        el.labelManual.classList.add('hidden');
        el.labelManual.value = '';
        addLog("UI: Label di-reset otomatis.");
    }

    function prosesTraining() {
        const btn = document.getElementById('btn-train');
        const originalHTML = btn.innerHTML;

        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 300000);

        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner animate-spin"></i> MEMPROSES TRAINING...`;
        addLog("SISTEM: Memulai proses training model AI (huruf & angka) di background thread...");

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            addLog("ERROR: CSRF Token tidak ditemukan. Refresh halaman.");
            btn.disabled = false;
            btn.innerHTML = originalHTML;
            return;
        }

        fetch('/train-model', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ trigger: 'train' }),
            signal: controller.signal
        })
        .then(res => {
            clearTimeout(timeoutId);
            if (!res.ok) {
                return res.json().then(errData => {
                    throw new Error(errData.message || `Server error: ${res.status}`);
                });
            }
            return res.json();
        })
        .then(data => {
            addLog("SISTEM: Training berhasil dipicu. Menunggu proses kalkulasi data & evaluasi model...");
            jalankanPollingEvaluasi(btn, originalHTML);
        })
        .catch(err => {
            clearTimeout(timeoutId);
            if (err.name === 'AbortError') {
                addLog("ERROR: Waktu tunggu (timeout) habis saat memicu training.");
                AppAlert.fire('error', 'Waktu Tunggu Habis', 'Proses training memakan waktu terlalu lama di server.');
            } else {
                const msg = err?.message || 'Terjadi kesalahan sistem.';
                addLog(`ERROR: ${msg}`);
                AppAlert.fire('error', 'Training Gagal', msg);
            }
            btn.disabled = false;
            btn.innerHTML = originalHTML;
        });
    }

    /**
     * Render 1 kategori (huruf / angka) ke dalam panel modal hasil evaluasi.
     * dataKategori = objek meta_model.json dari Laravel, atau null kalau
     * kategori itu belum/tidak selesai ditraining.
     */
    function renderKategoriResult(kategori, dataKategori) {
        const contentEl = document.getElementById(`result-content-${kategori}`);
        const emptyEl   = document.getElementById(`result-empty-${kategori}`);

        if (!dataKategori) {
            contentEl.classList.add('hidden');
            emptyEl.classList.remove('hidden');
            return;
        }

        contentEl.classList.remove('hidden');
        emptyEl.classList.add('hidden');

        const akurasi = ((dataKategori.accuracy || 0) * 100).toFixed(1);
        document.getElementById(`accuracy-value-${kategori}`).innerText = akurasi + '%';

        const fitStatusEl = document.getElementById(`fit-status-${kategori}`);
        if (fitStatusEl && dataKategori.model_fitting_status) {
            fitStatusEl.innerText = dataKategori.model_fitting_status;
        }

        const reportTableEl = document.getElementById(`report-table-container-${kategori}`);
        reportTableEl.innerHTML = '';

        const report = dataKategori.classification_report || {};
        const skip   = ['accuracy', 'macro avg', 'weighted avg'];
        let htmlBuffer = [];

        const isLightMode = document.documentElement.getAttribute('data-theme') === 'light' || !document.documentElement.classList.contains('dark');

        Object.keys(report).forEach(key => {
            if (skip.includes(key)) return;
            const metrics = report[key];
            const f1 = (metrics['f1-score'] * 100).toFixed(0);

            let hexColor = '#ef4444';
            let hexBorder = 'rgba(239, 68, 68, 0.3)';

            if (f1 >= 70) {
                hexColor = isLightMode ? '#059669' : '#34d399';
                hexBorder = isLightMode ? 'rgba(5, 150, 105, 0.3)' : 'rgba(52, 211, 153, 0.3)';
            } else if (f1 >= 40) {
                hexColor = isLightMode ? '#d97706' : '#fbbf24';
                hexBorder = isLightMode ? 'rgba(217, 119, 6, 0.3)' : 'rgba(251, 191, 36, 0.3)';
            } else {
                hexColor = isLightMode ? '#dc2626' : '#f87171';
            }

            const innerBorderColor = isLightMode ? 'rgba(0, 0, 0, 0.1)' : 'rgba(255, 255, 255, 0.08)';
            const innerTextColor = isLightMode ? 'text-slate-800' : 'text-white';

            htmlBuffer.push(`
                <div class="bg-slate-900/50 dark:bg-slate-900/50 border p-4 rounded-3xl animate__animated animate__fadeInUp" style="border-color: ${hexBorder};">
                    <div class="text-center mb-3">
                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Label: ${key}</span>
                        <div class="text-2xl font-black" style="color: ${hexColor};">${f1}% <span class="text-[8px] text-slate-500">F1</span></div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 pt-3" style="border-top: 1px solid ${innerBorderColor};">
                        <div class="text-center">
                            <p class="text-[8px] text-slate-500 uppercase font-bold">Precision</p>
                            <p class="text-xs font-bold ${innerTextColor}">${(metrics.precision * 100).toFixed(0)}%</p>
                        </div>
                        <div class="text-center" style="border-left: 1px solid ${innerBorderColor};">
                            <p class="text-[8px] text-slate-500 uppercase font-bold">Recall</p>
                            <p class="text-xs font-bold ${innerTextColor}">${(metrics.recall * 100).toFixed(0)}%</p>
                        </div>
                    </div>
                </div>
            `);
        });

        reportTableEl.innerHTML = htmlBuffer.join('');
    }

    function jalankanPollingEvaluasi(btn, originalHTML) {
        let waktuTungguMaksimal = 120; // ~10 menit (120 x 5 detik)
        let hitungCek = 0;

        const intervalCek = setInterval(() => {
            hitungCek++;

            fetch('/get-latest-evaluation')
            .then(res => {
                if (!res.ok) throw new Error();
                return res.json();
            })
            .then(response => {
                // status: "ready" (2 kategori selesai), "partial" (baru 1),
                // "pending" (belum ada). Kita tunggu sampai "ready" ATAU
                // waktu tunggu habis -- kalau timeout tapi sudah "partial",
                // tetap tampilkan apa yang ada supaya user tidak menunggu
                // kategori yang mungkin memang di-skip (data < 30 baris).
                if (response.status === 'ready' || (response.status === 'partial' && hitungCek >= waktuTungguMaksimal)) {
                    clearInterval(intervalCek);

                    const kategoriData = response.kategori || {};
                    renderKategoriResult('huruf', kategoriData.huruf || null);
                    renderKategoriResult('angka', kategoriData.angka || null);
                    switchResultTab('huruf');

                    const akurasiHuruf = kategoriData.huruf ? ((kategoriData.huruf.accuracy || 0) * 100).toFixed(1) : '-';
                    const akurasiAngka = kategoriData.angka ? ((kategoriData.angka.accuracy || 0) * 100).toFixed(1) : '-';
                    addLog(`SUKSES: Model diperbarui! Akurasi huruf: ${akurasiHuruf}% | Akurasi angka: ${akurasiAngka}%`);

                    if (typeof openModalResult === "function") {
                        openModalResult();
                    } else {
                        console.error("Fungsi openModalResult() tidak ditemukan di skrip blade Anda.");
                    }

                    if (typeof updateUIStats === "function") {
                        if (typeof state !== 'undefined') state.sessionStats = {};
                        updateUIStats();
                    }

                    AppAlert.fire('success', 'Training Selesai', `Model huruf: <b>${akurasiHuruf}%</b> &middot; Model angka: <b>${akurasiAngka}%</b>.`);

                    btn.disabled = false;
                    btn.innerHTML = originalHTML;
                }
            })
            .catch(() => {
                // Diamkan; endpoint memang bisa 404/error sesaat kalau file
                // metadata belum ada sama sekali di percobaan pertama.
            });

            if (hitungCek >= waktuTungguMaksimal) {
                clearInterval(intervalCek);
                addLog("ERROR: Waktu sinkronisasi habis. Server background memproses terlalu lama.");
                AppAlert.fire('error', 'Waktu Tunggu Habis', 'Proses sinkronisasi file evaluasi terputus. Silakan periksa status training di log server Anda.');
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
        }, 5000);
    }

    function drawGuide(w, h) {
        ctx.strokeStyle = "rgba(99, 102, 241, 0.4)";
        ctx.lineWidth   = 4;
        ctx.setLineDash([15, 10]);
        const rectW = w * 0.6, rectH = h * 0.7;
        const rectX = (w - rectW) / 2, rectY = (h - rectH) / 2;
        ctx.strokeRect(rectX, rectY, rectW, rectH);
        ctx.setLineDash([]);
        ctx.fillStyle   = "rgba(255, 255, 255, 0.8)";
        ctx.font        = "bold 14px Inter, sans-serif";
        ctx.textAlign   = "center";
        ctx.fillText("POSISIKAN TANGAN DI SINI", w / 2, rectY - 15);
    }

    function onResults(results) {
        state.lastResults = results;

        el.canvas.width  = el.video.videoWidth;
        el.canvas.height = el.video.videoHeight;

        ctx.save();
        ctx.clearRect(0, 0, el.canvas.width, el.canvas.height);

        ctx.translate(el.canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(results.image, 0, 0, el.canvas.width, el.canvas.height);

        ctx.save();
        ctx.scale(-1, 1);
        ctx.translate(-el.canvas.width, 0);
        drawGuide(el.canvas.width, el.canvas.height);
        ctx.restore();

        if (results.multiHandLandmarks && results.multiHandLandmarks.length > 0) {
            if (!state.handInFrame) {
                addLog("SISTEM: Tangan terdeteksi.");
                state.handInFrame = true;
            }
            el.handCount.innerText = `${results.multiHandLandmarks.length} Tangan Terdeteksi`;

            results.multiHandLandmarks.forEach((landmarks, index) => {
                const color = results.multiHandedness[index].label === 'Right' ? '#6366f1' : '#10b981';
                drawConnectors(ctx, landmarks, HAND_CONNECTIONS, { color, lineWidth: 4 });
                drawLandmarks(ctx, landmarks, { color: '#ffffff', lineWidth: 1.5, radius: 3 });
            });
        } else {
            if (state.handInFrame) {
                addLog("SISTEM: Tangan keluar area.");
                state.handInFrame = false;
            }
            el.handCount.innerText = `Tangan tidak terdeteksi`;
        }
        ctx.restore();
    }

    const hands = new Hands({
        locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/hands/${file}`
    });
    hands.setOptions({
        maxNumHands: 2,
        modelComplexity: 1,
        minDetectionConfidence: 0.6,
        minTrackingConfidence: 0.6
    });
    hands.onResults(onResults);

    const camera = new Camera(el.video, {
        onFrame: async () => {
            await hands.send({ image: el.video });
            el.liveIndicator.className = 'w-2 h-2 bg-emerald-500 rounded-full animate-pulse';
            el.liveText.innerText = 'LIVE';
        },
        width:  { ideal: 1280 },
        height: { ideal: 720 },
        facingMode: 'user'
    });
    camera.start();

    loadInitialData();
</script>