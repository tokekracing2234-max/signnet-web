<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-chart-matrix@2.0.1/dist/chartjs-chart-matrix.min.js"></script>
<script>
    const htmlEl = document.documentElement;
    let globalClassificationReport = null;
    let selectedLabelValue = "";
    let currentMatrixFilterMode = 'all';

    function getAdaptiveColor() { return (htmlEl.getAttribute('data-theme') || 'dark') === 'light' ? '#0f172a' : '#818cf8'; }
    function getGridColor() { return (htmlEl.getAttribute('data-theme') || 'dark') === 'light' ? 'rgba(79, 70, 229, 0.08)' : 'rgba(255, 255, 255, 0.05)'; }
    function getMatrixBorderColor() { return (htmlEl.getAttribute('data-theme') || 'dark') === 'light' ? 'rgba(79, 70, 229, 0.15)' : 'rgba(255, 255, 255, 0.05)'; }

    // DROPDOWN MATRIKS KONFUSI
    function toggleMatrixDropdown(event) {
        event.stopPropagation();
        const optionsList = document.getElementById('matrix-select-options');
        const arrow = document.getElementById('matrix-select-arrow');

        document.getElementById('custom-select-options').classList.add('hidden');
        document.getElementById('custom-select-arrow').classList.remove('rotate-180');

        if (optionsList.classList.contains('hidden')) {
            optionsList.classList.remove('hidden');
            arrow.classList.add('rotate-180');
        } else {
            optionsList.classList.add('hidden');
            arrow.classList.remove('rotate-180');
        }
    }

    function selectMatrixOption(value, text) {
        document.getElementById('matrix-select-text').textContent = text;
        currentMatrixFilterMode = value;
        document.getElementById('matrix-select-options').classList.add('hidden');
        document.getElementById('matrix-select-arrow').classList.remove('rotate-180');
        filterMatrix();
    }

    // DROPDOWN EVALUASI PER LABEL
    function toggleCustomDropdown(event) {
        event.stopPropagation();
        const optionsList = document.getElementById('custom-select-options');
        const arrow = document.getElementById('custom-select-arrow');

        document.getElementById('matrix-select-options').classList.add('hidden');
        document.getElementById('matrix-select-arrow').classList.remove('rotate-180');

        if (optionsList.classList.contains('hidden')) {
            optionsList.classList.remove('hidden');
            arrow.classList.add('rotate-180');
        } else {
            optionsList.classList.add('hidden');
            arrow.classList.remove('rotate-180');
        }
    }

    function selectCustomOption(value, text) {
        document.getElementById('custom-select-text').textContent = text;
        selectedLabelValue = value;
        document.getElementById('custom-select-options').classList.add('hidden');
        document.getElementById('custom-select-arrow').classList.remove('rotate-180');
        updateLabelEvaluation(); 
    }

    document.addEventListener('click', function(e) {
        const matrixTrigger = document.getElementById('matrix-select-trigger');
        const matrixOptions = document.getElementById('matrix-select-options');
        if (matrixTrigger && !matrixTrigger.contains(e.target) && !matrixOptions.contains(e.target)) {
            matrixOptions.classList.add('hidden');
            document.getElementById('matrix-select-arrow').classList.remove('rotate-180');
        }

        const labelTrigger = document.getElementById('custom-select-trigger');
        const labelOptions = document.getElementById('custom-select-options');
        if (labelTrigger && !labelTrigger.contains(e.target) && !labelOptions.contains(e.target)) {
            labelOptions.classList.add('hidden');
            document.getElementById('custom-select-arrow').classList.remove('rotate-180');
        }
    });

    // MODAL DESKRIPSI
    function openInfoModal(type) {
        const titleEl = document.getElementById('modalTitle');
        const bodyEl = document.getElementById('modalBody');
        if (!titleEl || !bodyEl) return;
            
        if (type === 'matrix') {
            titleEl.innerHTML = '<i class="fa-solid fa-th"></i> Analisis Detail Matriks Konfusi (Confusion Matrix)';
            bodyEl.innerHTML = `
                <p class="font-medium text-slate-800 dark:text-slate-200"><strong>Apa itu Matriks Konfusi?</strong></p>
                <p>Matriks Konfusi adalah instrumen evaluasi matematis berbentuk tabel yang memetakan efektivitas kinerja model klasifikasi secara komprehensif. Tabel ini membandingkan data aktual (fakta lapangan) dengan data hasil prediksi yang dihasilkan oleh algoritma kecerdasan buatan.</p>
                    
                <div class="p-3.5 bg-indigo-500/5 rounded-2xl border border-indigo-500/10 space-y-1">
                    <p class="font-semibold text-indigo-500"><i class="fa-solid fa-graduation-cap"></i> Cara Membaca Grafik Matrix:</p>
                    <ul class="list-disc list-inside space-y-1 text-[11px]">
                        <li><strong>Sisi Diagonal Utama (Kiri Atas ke Kanan Bawah):</strong> Representasi seberapa sering model berhasil menebak data dengan benar (*True Positive* & *True Negative*). Semakin cerah warnanya, semakin sempurna tingkat keakuratannya.</li>
                        <li><strong>Sisi Luar Diagonal:</strong> Titik terjadinya salah klasifikasi (*Error / Misclassification*), di mana model mendeteksi suatu isyarat sebagai kelas/huruf lain yang keliru.</li>
                    </ul>
                </div>
                <p class="text-[11px] italic">Manfaat: Membantu developer mendeteksi ambiguitas fitur data. Misal, jika huruf "M" sering salah diprediksi sebagai "N" karena kemiripan bentuk fisik hand landmark.</p>
            `;
        } else if (type === 'distribution') {
            titleEl.innerHTML = '<i class="fa-solid fa-chart-bar"></i> Analisis Distribusi Dataset & Proporsi Data';
            bodyEl.innerHTML = `
                <p class="font-medium text-slate-800 dark:text-slate-200"><strong>Apa itu Distribusi Dataset?</strong></p>
                <p>Distribusi Dataset menampilkan visualisasi grafik batang yang merepresentasikan total volume sampel data (nilai *Support*) yang dialokasikan pada setiap label kelas klasifikasi, baik berupa huruf (A-Z) maupun angka (0-9).</p>
                    
                <div class="p-3.5 bg-purple-500/5 rounded-2xl border border-purple-500/10 space-y-1">
                    <p class="font-semibold text-purple-500"><i class="fa-solid fa-scale-balanced"></i> Pentingnya Keseimbangan Data (Data Balance):</p>
                    <p class="text-[11px]">Kuantitas data yang seimbang pada tiap grafik memastikan model belajar secara adil tanpa kecenderungan memihak (*bias*). Jika salah satu batang grafik terlalu tinggi dibandingkan yang lain, model akan cenderung mahir menebak kelas mayoritas tersebut namun lemah dalam mengenali kelas minoritas.</p>
                </div>
                <p class="text-[11px] italic">Catatan Profesional: Grafik ini diambil langsung dari parameter statistika hasil ekstraksi frame landmark tangan Anda, membantu memantau konsistensi proses data gathering.</p>
            `;
        }
        toggleModal(true);
    }

    function onAnimationEndHandler(e) {
        const modal = document.getElementById('infoModal');
        const content = document.getElementById('modalContent');
        if (!modal || !content) return;

        if (e.target === modal) {
            modal.classList.remove('flex', 'animate__animated', 'animate__fadeOut');
            modal.classList.add('hidden');
            content.classList.remove('animate__animated', 'animate__zoomOut');
            modal.removeEventListener('animationend', onAnimationEndHandler);
        }
    }

    function toggleModal(show) {
        if (window.event) window.event.stopPropagation();

        const modal = document.getElementById('infoModal');
        const content = document.getElementById('modalContent');
        if (!modal || !content) return;
            
        if (show) {
            modal.removeEventListener('animationend', onAnimationEndHandler);
            modal.classList.remove('hidden', 'animate__fadeOut');
            content.classList.remove('animate__zoomOut');
            modal.classList.add('flex', 'animate__animated', 'animate__fadeIn', 'animate__faster');
            content.classList.add('animate__animated', 'animate__zoomIn', 'animate__faster');
        } else {
            modal.classList.remove('animate__fadeIn');
            modal.classList.add('animate__fadeOut');
            content.classList.remove('animate__zoomIn');
            content.classList.add('animate__zoomOut');

            modal.removeEventListener('animationend', onAnimationEndHandler);
            modal.addEventListener('animationend', onAnimationEndHandler);
        }
    }

    function applyThemeToCharts(theme) {
        htmlEl.setAttribute('data-theme', theme);
        theme === 'dark' ? htmlEl.classList.add('dark') : htmlEl.classList.remove('dark');
        const activeColor = getAdaptiveColor();
        if (window.matrixChartInst) {
            window.matrixChartInst.options.scales.x.ticks.color = activeColor;
            window.matrixChartInst.options.scales.y.ticks.color = activeColor;
            window.matrixChartInst.data.datasets[0].borderColor = getMatrixBorderColor();
            window.matrixChartInst.update('none'); 
        }
        if (window.distChartInst) {
            window.distChartInst.options.scales.x.ticks.color = activeColor;
            window.distChartInst.options.scales.y.ticks.color = activeColor;
            window.distChartInst.options.scales.y.grid.color = getGridColor();
            window.distChartInst.update('none');
        }
    }

    Chart.defaults.color = getAdaptiveColor();
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
    Chart.defaults.font.weight = '700';
    window.matrixChartInst = null; window.distChartInst = null; let rawMatrixData = null;

    document.addEventListener('DOMContentLoaded', () => {
        window.addEventListener('themeChanged', (e) => applyThemeToCharts(e.detail.theme));
        applyThemeToCharts(localStorage.getItem('theme') || 'dark');

        const mainContent = document.querySelector('.main-content');
        if (mainContent) {
            let resizeTimeout;
            const resizeObserver = new ResizeObserver(() => {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(() => {
                    if(window.matrixChartInst) window.matrixChartInst.resize();
                    if(window.distChartInst) window.distChartInst.resize();
                }, 260); 
            });
            resizeObserver.observe(mainContent);
        }

        setTimeout(async () => {
            try {
                const response = await fetch('/api/dashboard-stats'); 
                const data = await response.json();
                if (data && data.status === 'success') { 
                    updateDashboard(data); if(data.logs) renderLogs(data.logs);
                }
            } catch (err) { console.error("Gagal memuat statistik:", err); }
        }, 100);
    });

    function filterMatrix() {
        if (!rawMatrixData) return;
        const mode = currentMatrixFilterMode;
        let filteredData = {}; const allLabels = Object.keys(rawMatrixData);
        let targetLabels = mode === 'numbers' ? allLabels.filter(l => !isNaN(l)) : mode === 'letters' ? allLabels.filter(l => isNaN(l)) : allLabels;
        targetLabels.forEach(y => { filteredData[y] = {}; targetLabels.forEach(x => { filteredData[y][x] = rawMatrixData[y][x] || 0; }); });
        renderMatrix(filteredData);
    }

    function animateValue(obj, start, end, duration, isPercentage = false) {
        if (!obj) return;
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const currentVal = progress * (end - start) + start;
            
            obj.innerHTML = isPercentage ? currentVal.toFixed(0) + "%" : Math.floor(currentVal).toLocaleString();
            if (progress < 1) window.requestAnimationFrame(step);
        };
        window.requestAnimationFrame(step);
    }

    function updateDashboard(data) {
        if (document.getElementById('total-data')) animateValue(document.getElementById('total-data'), 0, data.total_data || 0, 500, false);
        if (document.getElementById('accuracy-val')) animateValue(document.getElementById('accuracy-val'), 0, (data.accuracy || 0) * 100, 600, true);
        if (document.getElementById('total-labels')) animateValue(document.getElementById('total-labels'), 0, data.total_labels || 0, 400, false);
        if (data.confusion_matrix) { rawMatrixData = data.confusion_matrix; filterMatrix(); }
        if (data.classification_report) { globalClassificationReport = data.classification_report; populateLabelSelect(data.classification_report); renderDistribution(data.classification_report); }
    }

    function populateLabelSelect(reportData) {
        const listContainer = document.getElementById('custom-select-options'); 
        if (!listContainer) return;
        listContainer.innerHTML = '';
        
        const defaultLi = document.createElement('li');
        defaultLi.className = "px-3 py-2 text-[10px] font-bold text-slate-400 dark:text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700/50 cursor-pointer border-b border-slate-100 dark:border-slate-700/40";
        defaultLi.textContent = "Pilih Label";
        defaultLi.onclick = () => selectCustomOption("", "Pilih Label");
        listContainer.appendChild(defaultLi);

        Object.keys(reportData)
            .filter(k => {
                const cleanKey = k.toLowerCase().trim();
                return !['accuracy', 'macro avg', 'weighted avg'].includes(cleanKey);
            })
            .sort((a, b) => a.localeCompare(b, undefined, { numeric: true }))
            .forEach(label => {
                const li = document.createElement('li');
                li.className = "px-3 py-1.5 text-[10px] font-bold text-slate-700 dark:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 hover:text-indigo-600 dark:hover:text-indigo-200 cursor-pointer transition-colors";
                li.textContent = `Kelas ${label}`;
                li.onclick = () => selectCustomOption(label, `Kelas ${label}`);
                listContainer.appendChild(li);
            });
    }

    function updateLabelEvaluation() {
        const label = selectedLabelValue;
        const pEl = document.getElementById('eval-precision'); 
        const rEl = document.getElementById('eval-recall'); 
        const fEl = document.getElementById('eval-f1');
        
        if(!label || !globalClassificationReport || !globalClassificationReport[label]) { 
            animateValue(pEl, parseInt(pEl.textContent) || 0, 0, 400, true);
            animateValue(rEl, parseInt(rEl.textContent) || 0, 0, 400, true);
            animateValue(fEl, parseInt(fEl.textContent) || 0, 0, 400, true);
            return; 
        }
        
        const metrics = globalClassificationReport[label];
        const f1Value = metrics.f1_score !== undefined ? metrics.f1_score : (metrics['f1-score'] !== undefined ? metrics['f1-score'] : 0);
        
        const currentPrecision = parseInt(pEl.textContent) || 0;
        const currentRecall = parseInt(rEl.textContent) || 0;
        const currentF1 = parseInt(fEl.textContent) || 0;

        const targetPrecision = (metrics.precision || 0) * 100;
        const targetRecall = (metrics.recall || 0) * 100;
        const targetF1 = f1Value * 100;

        animateValue(pEl, currentPrecision, targetPrecision, 450, true);
        animateValue(rEl, currentRecall, targetRecall, 450, true);
        animateValue(fEl, currentF1, targetF1, 450, true);
    }

    function renderLogs(logs) {
        const container = document.getElementById('logs-container');
        if(!container) return;
        if(!logs || logs.length === 0) { container.innerHTML = '<div class="text-xs text-center py-4 text-custom-muted">Tidak ada riwayat aktivitas.</div>'; return; }
        container.innerHTML = logs.map(log => `
            <div class="p-3 rounded-xl bg-slate-500/5 border border-slate-500/10 text-xs space-y-1">
                <div class="flex items-center justify-between text-[10px] text-custom-muted">
                    <span class="font-bold text-indigo-500"><i class="fa-solid fa-user-shield"></i> Admin ID: ${log.admin_id}</span>
                    <span>${new Date(log.created_at).toLocaleDateString('id-ID')}</span>
                </div>
                <p class="font-medium text-slate-700 dark:text-slate-300">${log.activity}</p>
            </div>
        `).join('');
    }

    function renderMatrix(matrixData) {
        const canvas = document.getElementById('matrixChart'); 
        if (!canvas) return; 
        const ctx = canvas.getContext('2d');
        if (window.matrixChartInst) window.matrixChartInst.destroy();
        
        const labels = Object.keys(matrixData); 
        const values = [];
        let grandTotal = 0;

        labels.forEach((y) => { 
            labels.forEach((x) => { 
                const val = matrixData[y][x] || 0;
                grandTotal += val;
                values.push({ x: x, y: y, v: val }); 
            }); 
        });
        const maxVal = Math.max(...values.map(d => d.v)) || 1;

        window.matrixChartInst = new Chart(ctx, {
            type: 'matrix',
            data: {
                datasets: [{
                    label: 'Confusion Matrix', 
                    data: values,
                    backgroundColor(c) {
                        if (!c || c.dataIndex === undefined || !c.dataset.data[c.dataIndex]) return 'transparent';
                        const val = c.dataset.data[c.dataIndex].v; 
                        if (val === 0) return 'rgba(255, 255, 255, 0.01)';
                        return `rgba(99, 102, 241, ${0.2 + ((val / maxVal) * 0.8)})`;
                    },
                    borderColor: typeof getMatrixBorderColor === 'function' ? getMatrixBorderColor() : 'rgba(255, 255, 255, 0.03)', 
                    borderWidth: 0.5,
                    width: ({chart}) => chart.chartArea ? (chart.chartArea.width / labels.length) - 0.2 : 15,
                    height: ({chart}) => chart.chartArea ? (chart.chartArea.height / labels.length) - 0.2 : 15
                }]
            },
            options: {
                maintainAspectRatio: false, 
                responsive: true,
                animation: false,
                events: ['click', 'mousemove', 'mouseout'],
                scales: {
                    x: { type: 'category', labels: labels, grid: { display: false }, ticks: { font: { size: 8 }, color: '#94a3b8' } },
                    y: { type: 'category', labels: labels, grid: { display: false }, offset: true, ticks: { font: { size: 8 }, color: '#94a3b8' } }
                },
                plugins: {
                    legend: false,
                    tooltip: {
                        enabled: true,
                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                        titleColor: '#818cf8',
                        bodyColor: '#f8fafc',
                        borderColor: 'rgba(99, 102, 241, 0.3)',
                        borderWidth: 1,
                        padding: 10,
                        bodyFont: { family: "'Plus Jakarta Sans', sans-serif", weight: '600', size: 10 },
                        titleFont: { family: "'Plus Jakarta Sans', sans-serif", weight: '800', size: 11 },
                        callbacks: {
                            title() { return "Detail Akurasi Koordinat"; },
                            label(context) {
                                const point = context.raw;
                                const pct = grandTotal > 0 ? ((point.v / grandTotal) * 100).toFixed(1) : 0;
                                return [
                                    `Actual Class    : Kelas ${point.y}`,
                                    `Predicted Class : Kelas ${point.x}`,
                                    `Total Sampel    : ${point.v} sampel`,
                                    `Persentase      : ${pct}% dari total`
                                ];
                            }
                        }
                    }
                }
            }
        });
    }

    function renderDistribution(reportData) {
        const canvas = document.getElementById('distChart'); 
        if (!canvas) return; 
        const ctx = canvas.getContext('2d');
        if (window.distChartInst) window.distChartInst.destroy();
        
        const labels = Object.keys(reportData)
        .filter(k => {
            const cleanKey = k.toLowerCase().trim();
            return !['accuracy', 'macro avg', 'weighted avg'].includes(cleanKey);
        })
        .sort((a, b) => a.localeCompare(b, undefined, { numeric: true }));

        const counts = labels.map(l => {
            const dataTarget = reportData[l] || reportData[l.toUpperCase()] || reportData[l.toLowerCase()];
            return dataTarget ? (dataTarget.support || 0) : 0;
        });
        const gradient = ctx.createLinearGradient(0, 0, 0, 400); 
        gradient.addColorStop(0, '#6366f1'); gradient.addColorStop(1, '#a855f7');

        window.distChartInst = new Chart(ctx, {
            type: 'bar', 
            data: { labels: labels, datasets: [{ label: 'Sampel', data: counts, backgroundColor: gradient, borderRadius: 8 }] },
            options: {
                maintainAspectRatio: false, responsive: true,
                plugins: { legend: { display: false } },
                animation: { duration: 2000, easing: 'easeOutQuart' },
                scales: {
                    y: { grid: { color: 'rgba(255,255,255,0.05)', drawBorder: false }, beginAtZero: true, ticks: { color: '#64748b', font: { size: 9 } } },
                    x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 10 } } }
                }
            }
        });
    }
</script>