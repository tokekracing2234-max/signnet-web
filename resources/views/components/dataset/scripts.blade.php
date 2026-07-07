<script>
    const htmlEl = document.documentElement;
    const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    let rawDatasetStats = [];
    let cachedTotalRows = 0;
    let currentPage = 1;
    const rowsPerPage = 10;

    @if(session('error'))
        document.addEventListener('DOMContentLoaded', () => {
            AppAlert.fire('error', 'GAGAL EKSPOR', "{{ session('error') }}");
        });
    @endif

    function handleThemeSync(theme) {
        htmlEl.setAttribute('data-theme', theme);
        if (theme === 'dark') {
            htmlEl.classList.add('dark');
        } else {
            htmlEl.classList.remove('dark');
        }
    }

    window.addEventListener('themeChanged', (e) => {
        handleThemeSync(e.detail.theme);
    });

    handleThemeSync(localStorage.getItem('theme') || 'dark');
    
    function triggerDownload() {
        const totalRows = parseInt(document.getElementById('total-rows').innerText.replace(/,/g, '')) || 0;
        
        if (totalRows <= 0) {
            AppAlert.fire('error', 'GAGAL', 'Dataset kosong! Tidak ada data untuk diunduh.');
            return;
        }

        window.location.href = "{{ route('admin.dataset.download') }}";
    }

    async function loadDatasetStats() {
        const tbody = document.getElementById('dataset-table-body');
        const downloadBtn = document.getElementById('download-btn'); 

        tbody.innerHTML = '<tr class="sm:border-none"><td colspan="5" class="px-8 py-10 text-center text-slate-400 dark:text-slate-500 italic">Syncing...</td></tr>';
        
        try {
            const response = await fetch("{{ route('admin.dataset.stats') }}");
            const data = await response.json();
            
            if (data && data.stats) {
                rawDatasetStats = Object.entries(data.stats).map(([label, count]) => ({ label, count }));
                cachedTotalRows = data.total || 0;
                currentPage = 1;
                renderPaginatedTable();

                if (data.total === 0) {
                    downloadBtn.classList.add('opacity-50', 'pointer-events-none');
                    downloadBtn.setAttribute('aria-disabled', 'true');
                } else {
                    downloadBtn.classList.remove('opacity-50', 'pointer-events-none');
                    downloadBtn.removeAttribute('aria-disabled');
                }
            }
        } catch (err) {
            tbody.innerHTML = '<tr class="sm:border-none"><td colspan="5" class="px-8 py-10 text-center text-rose-500">Gagal memuat data</td></tr>';
        }
    }

    function animateValue(id, start, end, duration) {
        const obj = document.getElementById(id);
        if (!obj) return;
        if (start === end) {
            obj.innerHTML = end.toLocaleString();
            return;
        }
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            obj.innerHTML = Math.floor(progress * (end - start) + start).toLocaleString();
            if (progress < 1) window.requestAnimationFrame(step);
        };
        window.requestAnimationFrame(step);
    }

    function renderPaginatedTable() {
    const tbody = document.getElementById('dataset-table-body');
    const oldTotalRows = parseInt(document.getElementById('total-rows').innerText.replace(/,/g, '')) || 0;
    const oldTotalLabels = parseInt(document.getElementById('total-labels').innerText) || 0;

    animateValue('total-rows', oldTotalRows, cachedTotalRows, 1000);
    animateValue('total-labels', oldTotalLabels, rawDatasetStats.length, 800);

    if (rawDatasetStats.length === 0) {
        tbody.innerHTML = '<tr class="sm:border-none"><td colspan="5" class="px-8 py-16 text-center text-slate-400 dark:text-slate-500 italic">Database kosong.</td></tr>';
        updatePaginationControls(0, 0, 0);
        return;
    }

    // Kalkulasi index slice data array
    const startIndex = (currentPage - 1) * rowsPerPage;
    const endIndex = Math.min(startIndex + rowsPerPage, rawDatasetStats.length);
    const paginatedData = rawDatasetStats.slice(startIndex, endIndex);

    // Render struktur baris tabel data sesuai potongan slice
    tbody.innerHTML = paginatedData.map((item, index) => {
        const globalIndex = startIndex + index + 1;
        const percent = cachedTotalRows > 0 ? ((item.count / cachedTotalRows) * 100).toFixed(1) : 0;
        const rowDelay = index * 40; 
        const safeLabel = btoa(encodeURIComponent(item.label));

        return `
            <tr class="border-b border-slate-100 dark:border-white/5 hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors animate__animated animate__fadeInUp text-slate-800 dark:text-slate-200" style="animation-delay: ${rowDelay}ms">
                <td class="px-6 py-5 text-center font-bold text-slate-400 dark:text-slate-500 font-mono" data-label="No.">${globalIndex}</td>
                <td class="px-8 py-5 text-center" data-label="Label Nama">
                    <span class="bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 px-3 py-1.5 rounded-lg font-bold text-[10px] border border-indigo-200 dark:border-indigo-500/10">${item.label}</span>
                </td>
                <td class="px-8 py-5 font-mono font-bold text-left sm:text-center" data-label="Jumlah">${item.count.toLocaleString()}</td>
                <td class="px-8 py-5" data-label="Balance">
                    <div class="flex items-center gap-3 w-full sm:w-auto justify-end sm:justify-start">
                        <div class="hidden sm:block flex-1 min-w-[100px] bg-slate-200 dark:bg-white/5 h-1.5 rounded-full overflow-hidden">
                            <div class="bg-indigo-600 dark:bg-indigo-500 h-full transition-all duration-1000 ease-out" 
                                 style="width: 0%;" 
                                 id="bar-${index}"></div>
                        </div>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold bg-slate-100 dark:bg-white/5 px-2 py-0.5 rounded sm:bg-transparent sm:p-0">${percent}%</span>
                    </div>
                </td>
                <td class="px-8 py-5 text-right flex sm:table-cell items-center justify-between sm:justify-end" data-label="Aksi">
                    <button onclick="deleteLabel('${safeLabel}', '${encodeURIComponent(item.label)}')" class="text-slate-400 hover:text-rose-500 dark:hover:text-rose-400 p-1 transition-colors">
                        <i class="fa-solid fa-trash-can text-sm sm:text-xs"></i>
                    </button>
                </td>
            </tr>`;
    }).join('');

    setTimeout(() => {
        paginatedData.forEach((item, index) => {
            const bar = document.getElementById(`bar-${index}`);
            if (bar) {
                const percent = cachedTotalRows > 0 ? ((item.count / cachedTotalRows) * 100).toFixed(1) : 0;
                bar.style.width = percent + "%";
            }
        });
    }, 50);

    updatePaginationControls(startIndex + 1, endIndex, rawDatasetStats.length);
}

function updatePaginationControls(start, end, total) {
    document.getElementById('pagination-start').innerText = start;
    document.getElementById('pagination-end').innerText = end;
    document.getElementById('pagination-total').innerText = total;

    const totalPages = Math.ceil(total / rowsPerPage);
    const prevBtn = document.getElementById('pagination-prev');
    const nextBtn = document.getElementById('pagination-next');
    const pagesContainer = document.getElementById('pagination-pages');

    prevBtn.disabled = currentPage === 1;
    nextBtn.disabled = currentPage === totalPages || totalPages === 0;

    prevBtn.onclick = () => { if(currentPage > 1) { currentPage--; renderPaginatedTable(); } };
    nextBtn.onclick = () => { if(currentPage < totalPages) { currentPage++; renderPaginatedTable(); } };

    let pagesHtml = '';
    for (let i = 1; i <= totalPages; i++) {
        if (i === currentPage) {
            pagesHtml += `<button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-xs bg-indigo-600 text-white border border-indigo-600 shadow-sm transition-transform cursor-pointer">${i}</button>`;
        } else {
            pagesHtml += `<button type="button" onclick="goToPage(${i})" class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-xs border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:border-indigo-600 dark:hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-white transition-all cursor-pointer">${i}</button>`;
        }
    }
    pagesContainer.innerHTML = pagesHtml;
}

function goToPage(pageNumber) {
    currentPage = pageNumber;
    renderPaginatedTable();
}

function getCustomSwalConfig() {
    return {
        background: htmlEl.getAttribute('data-theme') === 'dark' ? '#0f172a' : '#fff',
        color: htmlEl.getAttribute('data-theme') === 'dark' ? '#f8fafc' : '#0f172a'
    };
}

    function triggerImportClick() {
        document.getElementById('import-file-input').click();
    }

    function handleFileImport(event) {
        const file = event.target.files[0];
        if (!file) return;

        if (file.type !== "application/json" && !file.name.endsWith('.json')) {
            AppAlert.fire('error', 'FORMAT SALAH', 'File harus berupa dokumen format JSON.');
            event.target.value = ''; 
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const jsonData = JSON.parse(e.target.result);
                confirmImport(jsonData, event.target);
            } catch (err) {
                AppAlert.fire('error', 'JSON CACAT', 'Gagal membaca file. Pastikan struktur JSON valid.');
                event.target.value = '';
            }
        };
        reader.readAsText(file);
    }

    function confirmImport(data, inputElement) {
        const themeConfig = getCustomSwalConfig();

        Swal.fire({
            title: 'IMPORT DATASET',
            html: `
                <div class="p-2 text-center">
                    <div class="swal-custom-icon flex items-center justify-center" style="border-color: #10b981">
                        <svg class="w-10 h-10" fill="none" stroke="#10b981" stroke-width="4" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                    </div>
                    <p class="text-[10px] font-black tracking-[0.3em] text-slate-400 dark:text-slate-500 uppercase mb-3">System Status: <span style="color: #10b981">READY</span></p>
                    <p class="text-sm text-slate-600 dark:text-slate-300 font-medium px-4 leading-relaxed">Apakah Anda yakin ingin mengimpor data dari file JSON ini ke dalam database?</p>
                </div>
            `,
            background: themeConfig.background,
            color: themeConfig.color,
            buttonsStyling: false,
            showCancelButton: true,
            confirmButtonText: 'YA, IMPORT',
            cancelButtonText: 'BATAL',
            customClass: {
                popup: `rounded-[2.5rem] border border-slate-200 dark:border-slate-800/80 shadow-2xl p-6`,
                title: `text-xl font-black tracking-tighter pt-4 text-emerald-500`,
                confirmButton: `px-5 py-2.5 mr-2 rounded-xl font-bold text-[11px] uppercase tracking-widest text-white bg-emerald-600 active:scale-95 transition-all mb-4 cursor-pointer`,
                cancelButton: `px-5 py-2.5 rounded-xl font-bold text-[11px] uppercase tracking-widest text-slate-400 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 active:scale-95 transition-all mb-4 cursor-pointer`
            }
        }).then(async (result) => {
            if (result.isConfirmed) {
                const tbody = document.getElementById('dataset-table-body');
                tbody.innerHTML = '<tr class="sm:border-none"><td colspan="5" class="px-8 py-10 text-center text-emerald-500 italic">Processing import...</td></tr>';

                try {
                    const response = await fetch("{{ route('admin.dataset.import') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': getCsrfToken(),
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ dataset: data })
                    });
                    
                    const res = await response.json();
                    
                    if (res.status === 'success') {
                        AppAlert.fire('success', 'IMPORT BERHASIL', res.message || 'Dataset berhasil diperbarui.');
                    } else {
                        AppAlert.fire('error', 'IMPORT GAGAL', res.message || 'Terjadi kesalahan internal server.');
                    }
                } catch (err) {
                    AppAlert.fire('error', 'GAGAL', 'Terjadi kesalahan jaringan saat mengimpor data.');
                } finally {
                    loadDatasetStats();
                }
            }
            inputElement.value = '';
        });
    }

    function deleteLabel(encodedLabel, displayLabel) {
        const labelName = decodeURIComponent(displayLabel);
        const themeConfig = getCustomSwalConfig();
        
        Swal.fire({
            title: 'HAPUS DATA',
            html: `
                <div class="p-2 text-center">
                    <div class="swal-custom-icon flex items-center justify-center" style="border-color: #ef4444">
                        <svg class="w-10 h-10" fill="none" stroke="#ef4444" stroke-width="4" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <p class="text-[10px] font-black tracking-[0.3em] text-slate-400 dark:text-slate-500 uppercase mb-3">System Status: <span style="color: #ef4444">WARNING</span></p>
                    <p class="text-sm text-slate-600 dark:text-slate-300 font-medium px-4 leading-relaxed">Hapus semua sampel data untuk label "<span class="font-bold text-rose-500">${labelName}</span>"?</p>
                </div>
            `,
            background: themeConfig.background,
            color: themeConfig.color,
            buttonsStyling: false,
            showCancelButton: true,
            confirmButtonText: 'YA, HAPUS',
            cancelButtonText: 'BATAL',
            customClass: {
                popup: `rounded-[2.5rem] border border-slate-200 dark:border-slate-800/80 shadow-2xl p-6`,
                title: `text-xl font-black tracking-tighter pt-4 text-rose-500`,
                confirmButton: `px-5 py-2.5 mr-2 rounded-xl font-bold text-[11px] uppercase tracking-widest text-white bg-rose-500 active:scale-95 transition-all mb-4 cursor-pointer`,
                cancelButton: `px-5 py-2.5 rounded-xl font-bold text-[11px] uppercase tracking-widest text-slate-400 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 active:scale-95 transition-all mb-4 cursor-pointer`
            }
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const url = "{{ route('admin.dataset.delete-label', ':label') }}".replace(':label', encodeURIComponent(labelName));
                    const response = await fetch(url, { 
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': getCsrfToken(), 'Content-Type': 'application/json' }
                    });
                    const res = await response.json();
                    if (res.status === 'success') {
                        AppAlert.fire('success', 'BERHASIL', `Label ${labelName} telah dihapus.`);
                        loadDatasetStats();
                    } else {
                        AppAlert.fire('error', 'GAGAL', res.message || 'Gagal menghapus label.');
                    }
                } catch (err) {
                    AppAlert.fire('error', 'GAGAL', 'Terjadi kesalahan saat menghapus data.');
                }
            }
        });
    }

    function clearDataset() {
        const themeConfig = getCustomSwalConfig();

        Swal.fire({
            title: 'RESET TOTAL',
            html: `
                <div class="p-2 text-center">
                    <div class="swal-custom-icon flex items-center justify-center" style="border-color: #f59e0b">
                        <svg class="w-10 h-10" fill="none" stroke="#f59e0b" stroke-width="4" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <p class="text-[10px] font-black tracking-[0.3em] text-slate-400 dark:text-slate-500 uppercase mb-3">System Status: <span style="color: #f59e0b">DANGER ZONE</span></p>
                    <p class="text-sm text-slate-600 dark:text-slate-300 font-medium px-4 leading-relaxed">Semua data koordinat isyarat akan dihapus secara permanen dari database!</p>
                </div>
            `,
            background: themeConfig.background,
            color: themeConfig.color,
            buttonsStyling: false,
            showCancelButton: true,
            confirmButtonText: 'YA, RESET SEMUA',
            cancelButtonText: 'BATAL',
            customClass: {
                popup: `rounded-[2.5rem] border border-slate-200 dark:border-slate-800/80 shadow-2xl p-6`,
                title: `text-xl font-black tracking-tighter pt-4 text-amber-500`,
                confirmButton: `px-5 py-2.5 mr-2 rounded-xl font-bold text-[11px] uppercase tracking-widest text-white bg-amber-500 active:scale-95 transition-all mb-4 cursor-pointer`,
                cancelButton: `px-5 py-2.5 rounded-xl font-bold text-[11px] uppercase tracking-widest text-slate-400 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 active:scale-95 transition-all mb-4 cursor-pointer`
            }
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const response = await fetch("{{ route('admin.dataset.clear') }}", { 
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': getCsrfToken(), 'Content-Type': 'application/json' }
                    });
                    const res = await response.json();
                    if (res.status === 'success') {
                        AppAlert.fire('success', 'SISTEM RESET', 'Seluruh dataset telah dibersihkan.');
                        loadDatasetStats();
                    } else {
                        AppAlert.fire('error', 'GAGAL', res.message || 'Gagal membersihkan database.');
                    }
                } catch (err) {
                    AppAlert.fire('error', 'GAGAL', 'Terjadi kesalahan saat meriset data.');
                }
            }
        });
    }

    window.onload = loadDatasetStats;
</script>