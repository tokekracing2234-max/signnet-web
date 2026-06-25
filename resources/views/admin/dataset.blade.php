<x-app-layout>
    <x-dataset.styles />

    <div class="dashboard-wrapper antialiased text-custom-title relative min-h-screen">
        <div class="flex flex-col md:flex-row min-h-screen w-full">
            
            <div id="sidebar-container" class="z-[999] shrink-0">
                @include('layouts.sidebar')
            </div>

            <main class="main-content flex-grow p-4 sm:p-6 md:p-8 lg:p-10 w-full overflow-y-auto h-screen custom-scroll">
                <div class="max-w-7xl mx-auto space-y-6 flex flex-col min-h-full justify-between">
                    
                    <div class="space-y-6 flex-grow">
                        
                        <header class="flex items-center justify-between gap-4 pt-2 animate-card delay-1 w-full">
                            <div class="min-w-0">
                                <span class="text-indigo-600 dark:text-indigo-400 text-[10px] font-bold uppercase tracking-[0.3em] block">
                                    Data Management
                                </span>
                                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight mt-1 whitespace-nowrap">
                                    <span class="logo-gradient">Dataset Explorer</span>
                                </h1>
                            </div>

                            <div class="flex items-center gap-3 shrink-0 justify-end">
                                <input type="file" id="import-json-input" accept=".json" class="hidden" onchange="handleFileImport(event, 'json')">
                                <input type="file" id="import-sql-input" accept=".sql" class="hidden" onchange="handleFileImport(event, 'sql')">

                                <button type="button" onclick="openImportModal()" 
                                    class="hidden sm:flex items-center justify-center px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 rounded-xl font-bold text-[11px] tracking-widest text-white active:scale-95 shadow-md transition-colors cursor-pointer">
                                    <i class="fa-solid fa-file-import mr-2 text-[10px]"></i>IMPORT DATASET
                                </button>

                                <button type="button" id="download-btn" onclick="openDownloadModal()" 
                                    class="hidden sm:flex items-center justify-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 rounded-xl font-bold text-[11px] tracking-widest text-white active:scale-95 shadow-md transition-colors cursor-pointer">
                                    <i class="fa-solid fa-file-code mr-2 text-[10px]"></i>DOWNLOAD DATASET
                                </button>

                                <button id="mobileSidebarToggle" type="button" class="md:hidden flex items-center justify-center w-11 h-11 bg-slate-100 dark:bg-slate-900/80 text-slate-800 dark:text-white rounded-2xl border border-slate-200 dark:border-white/10 active:scale-95 transition-colors cursor-pointer">
                                    <i class="fa-solid fa-bars text-xl"></i>
                                </button>
                            </div>
                        </header>

                        <div class="block sm:hidden animate-card delay-2 space-y-3">
                            <button type="button" onclick="openImportModal()" 
                                class="flex items-center justify-center w-full px-5 py-3 bg-emerald-600 hover:bg-emerald-500 rounded-xl font-bold text-[11px] tracking-widest text-white active:scale-95 shadow-md transition-colors cursor-pointer">
                                <i class="fa-solid fa-file-import mr-2 text-[10px]"></i>IMPORT DATASET
                            </button>

                            <button type="button" onclick="openDownloadModal()" 
                                class="flex items-center justify-center w-full px-5 py-3 bg-indigo-600 hover:bg-indigo-500 rounded-xl font-bold text-[11px] tracking-widest text-white active:scale-95 shadow-md transition-colors cursor-pointer">
                                <i class="fa-solid fa-file-code mr-2 text-[10px]"></i>DOWNLOAD DATASET
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                            <x-dataset.stat-card 
                                type="indigo" 
                                delay="delay-3"
                                title="Total Seluruh Baris" 
                                valueId="total-rows" 
                                unit="Sampel" 
                                icon="calculator" 
                                bgIcon="database" 
                            />
                            
                            <x-dataset.stat-card 
                                type="emerald" 
                                delay="delay-4"
                                title="Unique Labels" 
                                valueId="total-labels" 
                                unit="Kelas" 
                                icon="layer-group" 
                                bgIcon="tags" 
                            />
                            
                            <div class="sm:col-span-2 md:col-span-1">
                                <x-dataset.stat-card 
                                    type="amber" 
                                    delay="delay-5"
                                    title="Penyimpanan" 
                                    valueText='<span class="text-sm tracking-wide">Database engine</span><span class="text-xs text-amber-700 dark:text-amber-400/80 font-semibold font-mono mt-0.5">sign_language_db</span>' 
                                    icon="hard-drive" 
                                    bgIcon="server" 
                                />
                            </div>
                        </div>

                        <div class="glass rounded-[2rem] overflow-hidden animate-card delay-6 border border-slate-200 dark:border-white/5 bg-white/40 dark:bg-slate-900/40">
                            <div class="p-6 border-b border-slate-200 dark:border-white/5 flex justify-between items-center bg-indigo-50/30 dark:bg-white/5">
                                <h3 class="text-xs font-black tracking-widest uppercase text-indigo-600 dark:text-indigo-400">Distribusi Label</h3>
                                <button onclick="loadDatasetStats()" class="bg-white dark:bg-white/5 p-2 rounded-lg text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white border border-slate-200 dark:border-none transition-colors cursor-pointer">
                                    <i class="fa-solid fa-rotate text-xs"></i>
                                </button>
                            </div>
                            
                            <div class="table-overflow overflow-x-auto">
                                <table class="w-full text-left border-collapse min-w-full sm:min-w-[550px]">
                                    <thead>
                                        <tr class="text-[10px] font-black uppercase tracking-widest text-indigo-600/80 dark:text-indigo-400/70 border-b border-slate-200 dark:border-white/5 bg-slate-50/50 dark:bg-transparent">
                                            <th class="px-6 py-4 w-16 text-center">No.</th>
                                            <th class="px-8 py-4 text-center">Label Nama</th>
                                            <th class="px-8 py-4 text-center">Jumlah</th>
                                            <th class="px-8 py-4 text-center">Balance</th>
                                            <th class="px-8 py-4 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="dataset-table-body" class="text-sm text-slate-700 dark:text-slate-300"></tbody>
                                </table>
                            </div>

                            <div class="relative z-30 px-6 py-4 border-t border-slate-200 dark:border-white/5 flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-50/30 dark:bg-slate-900/10 text-xs font-medium text-slate-500 dark:text-slate-400 mt-auto">
                                <div class="text-center sm:text-left tracking-wide">
                                    Showing <span id="pagination-start" class="font-bold text-indigo-600 dark:text-indigo-400">0</span> to <span id="pagination-end" class="font-bold text-indigo-600 dark:text-indigo-400">0</span> of <span id="pagination-total" class="font-bold text-slate-800 dark:text-white">0</span> entries
                                </div>
                                <div class="flex items-center justify-center gap-2 w-full sm:w-auto">
                                    <button type="button" id="pagination-prev" class="pagination-arrow-btn">
                                        <i class="fa-solid fa-chevron-left mr-1.5 text-[10px]"></i>Prev
                                    </button>

                                    <div id="pagination-pages" class="flex items-center gap-1.5"></div>

                                    <button type="button" id="pagination-next" class="pagination-arrow-btn">
                                        Next<i class="fa-solid fa-chevron-right ml-1.5 text-[10px]"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 rounded-[2rem] border bg-rose-500/5 dark:bg-rose-500/[0.02] flex flex-col md:flex-row items-center justify-between gap-6 animate-card delay-7 border-rose-500/50 transition-colors">
                            <div class="text-center md:text-left flex items-center gap-4 w-full md:w-auto">
                                <div class="p-3.5 rounded-2xl bg-rose-500/10 text-rose-600 hidden md:block shrink-0">
                                    <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-rose-600 dark:text-rose-500 font-bold text-sm">Reset Dataset</h4>
                                    <p class="text-slate-500 dark:text-slate-400 text-xs font-medium mt-1 leading-relaxed text-justify tracking-normal normal-case">
                                        Setelah dataset dihapus, seluruh data sampel dan riwayat pengumpulan di dalamnya akan hilang secara permanen. Sebelum melanjutkan, pastikan data penting telah diamankan.
                                    </p>
                                </div>
                            </div>
                            <button onclick="clearDataset()" class="w-full md:w-auto px-6 py-2.5 rounded-xl border border-rose-500/30 text-rose-600 dark:text-rose-500 bg-white dark:bg-transparent text-[11px] font-bold tracking-widest hover:bg-rose-600 hover:text-white dark:hover:bg-rose-500 dark:hover:text-white transition-colors active:scale-95 shrink-0 cursor-pointer">
                                HAPUS SEMUA DATA
                            </button>
                        </div>

                    </div>

                    <footer class="footer-bottom pt-8 pb-2 text-center animate-card delay-7">
                        <p class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 tracking-wider">
                            © 2026 SignNet Project • Bahasa Isyarat BISINDO.
                        </p>
                    </footer>

                </div>
            </main>
        </div>
    </div>

    <div id="importModal" class="fixed inset-0 z-[1000] hidden items-center justify-center p-4 antialiased animate__animated">
        <div class="absolute inset-0 bg-slate-950/40 dark:bg-slate-950/60 backdrop-blur-sm" onclick="closeImportModal()"></div>
        <div id="importModalCard" class="relative w-full max-w-md glass bg-white/90 dark:bg-slate-900/90 border border-slate-200 dark:border-white/10 rounded-[2rem] p-6 shadow-2xl overflow-hidden flex flex-col animate__animated">
            <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-white/5">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <i class="fa-solid fa-file-import text-sm"></i>
                    </div>
                    <h3 class="text-xs font-black tracking-widest uppercase text-slate-800 dark:text-white">Pilih Format Import</h3>
                </div>
                <button onclick="closeImportModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors cursor-pointer w-8 h-8 flex items-center justify-center rounded-xl hover:bg-slate-100 dark:hover:bg-white/5">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <p class="text-slate-500 dark:text-slate-400 text-xs font-medium my-4 leading-relaxed">
                Silakan pilih jenis file eksternal yang ingin diintegrasikan langsung ke dalam database <code class="text-indigo-600 dark:text-indigo-400 font-bold font-mono text-[11px] bg-slate-100 dark:bg-white/5 px-1.5 py-0.5 rounded">sign_language_db</code>.
            </p>

            <div class="space-y-3">
                <button type="button" onclick="selectFormat('sql')" 
                    class="w-full relative group flex items-start gap-4 p-4 rounded-2xl border-2 border-emerald-500/40 bg-emerald-500/[0.02] dark:bg-emerald-500/[0.01] hover:bg-emerald-500/10 transition-all text-left active:scale-[0.99] cursor-pointer">
                    <div class="p-3 rounded-xl bg-emerald-600 text-white shadow-md mt-0.5 shrink-0 flex items-center justify-center w-11 h-11">
                        <i class="fa-solid fa-database text-base"></i>
                    </div>
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-extrabold text-xs tracking-wide text-slate-800 dark:text-white uppercase">SQL Script (.sql)</span>
                            <span class="px-2 py-0.5 text-[8px] font-black tracking-widest uppercase bg-emerald-600 text-white rounded-md shadow-sm">REKOMENDASI</span>
                        </div>
                        <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium leading-relaxed block">Eksekusi query langsung ke engine database. Sangat cepat & efisien untuk data skala besar.</span>
                    </div>
                </button>

                <button type="button" onclick="selectFormat('json')" 
                    class="w-full group flex items-start gap-4 p-4 rounded-2xl border border-slate-200 dark:border-white/5 bg-slate-50/50 dark:bg-white/[0.02] hover:border-indigo-500/30 hover:bg-indigo-500/[0.02] transition-all text-left active:scale-[0.99] cursor-pointer">
                    <div class="p-3 rounded-xl bg-indigo-600 text-white shadow-md mt-0.5 shrink-0 group-hover:bg-indigo-500 transition-colors flex items-center justify-center w-11 h-11">
                        <i class="fa-solid fa-file-code text-base"></i>
                    </div>
                    <div class="space-y-1">
                        <span class="font-extrabold text-xs tracking-wide text-slate-800 dark:text-white uppercase block">JSON Object (.json)</span>
                        <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium leading-relaxed block">Memerlukan ekstraksi array dan parsing manual sebelum dipetakan ke dalam skema tabel.</span>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <div id="downloadModal" class="fixed inset-0 z-[1000] hidden items-center justify-center p-4 antialiased animate__animated">
        <div class="absolute inset-0 bg-slate-950/40 dark:bg-slate-950/60 backdrop-blur-sm" onclick="closeDownloadModal()"></div>
        <div id="downloadModalCard" class="relative w-full max-w-md glass bg-white/90 dark:bg-slate-900/90 border border-slate-200 dark:border-white/10 rounded-[2rem] p-6 shadow-2xl overflow-hidden flex flex-col animate__animated">
            <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-white/5">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                        <i class="fa-solid fa-file-arrow-down text-sm"></i>
                    </div>
                    <h3 class="text-xs font-black tracking-widest uppercase text-slate-800 dark:text-white">Pilih Format Download</h3>
                </div>
                <button onclick="closeDownloadModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors cursor-pointer w-8 h-8 flex items-center justify-center rounded-xl hover:bg-slate-100 dark:hover:bg-white/5">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <p class="text-slate-500 dark:text-slate-400 text-xs font-medium my-4 leading-relaxed">
                Silakan pilih format berkas unduhan untuk mencadangkan data tabel <code class="text-indigo-600 dark:text-indigo-400 font-bold font-mono text-[11px] bg-slate-100 dark:bg-white/5 px-1.5 py-0.5 rounded">datasets</code> Anda.
            </p>

            <div class="space-y-3">
                <button type="button" onclick="selectDownloadFormat('json')" 
                    class="w-full relative group flex items-start gap-4 p-4 rounded-2xl border-2 border-indigo-500/40 bg-indigo-500/[0.02] dark:bg-indigo-500/[0.01] hover:bg-indigo-500/10 transition-all text-left active:scale-[0.99] cursor-pointer">
                    <div class="p-3 rounded-xl bg-indigo-600 text-white shadow-md mt-0.5 shrink-0 flex items-center justify-center w-11 h-11">
                        <i class="fa-solid fa-file-code text-base"></i>
                    </div>
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-extrabold text-xs tracking-wide text-slate-800 dark:text-white uppercase">JSON Object (.json)</span>
                            <span class="px-2 py-0.5 text-[8px] font-black tracking-widest uppercase bg-indigo-600 text-white rounded-md shadow-sm">POPULER</span>
                        </div>
                        <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium leading-relaxed block">Sangat cocok untuk kebutuhan integrasi API, pengolahan python, atau aplikasi frontend.</span>
                    </div>
                </button>

                <button type="button" onclick="selectDownloadFormat('sql')" 
                    class="w-full group flex items-start gap-4 p-4 rounded-2xl border border-slate-200 dark:border-white/5 bg-slate-50/50 dark:bg-white/[0.02] hover:border-emerald-500/30 hover:bg-emerald-500/[0.02] transition-all text-left active:scale-[0.99] cursor-pointer">
                    <div class="p-3 rounded-xl bg-emerald-600 text-white shadow-md mt-0.5 shrink-0 group-hover:bg-emerald-500 transition-colors flex items-center justify-center w-11 h-11">
                        <i class="fa-solid fa-database text-base"></i>
                    </div>
                    <div class="space-y-1">
                        <span class="font-extrabold text-xs tracking-wide text-slate-800 dark:text-white uppercase block">SQL Script (.sql)</span>
                        <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium leading-relaxed block">Berisi baris dump `INSERT INTO`. Memudahkan restorasi langsung ke database engine lain.</span>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <x-dataset.scripts />

    <script>
        /* ==================== LOGIC MODAL IMPORT ==================== */
        function openImportModal() {
            const importModal = document.getElementById('importModal');
            const importModalCard = document.getElementById('importModalCard');
            if (!importModal || !importModalCard) return;

            importModal.style.setProperty('--animate-duration', '0.35s');
            importModalCard.style.setProperty('--animate-duration', '0.35s');
            importModal.classList.remove('hidden', 'animate__fadeOut');
            importModalCard.classList.remove('animate__zoomOut');
            importModal.classList.add('flex', 'animate__fadeIn');
            importModalCard.classList.add('animate__zoomIn');
        }

        function closeImportModal() {
            const importModal = document.getElementById('importModal');
            const importModalCard = document.getElementById('importModalCard');
            if (!importModal || !importModalCard) return;

            importModal.style.setProperty('--animate-duration', '0.35s');
            importModalCard.style.setProperty('--animate-duration', '0.35s');
            importModal.classList.remove('animate__fadeIn');
            importModal.classList.add('animate__fadeOut');
            importModalCard.classList.remove('animate__zoomIn');
            importModalCard.classList.add('animate__zoomOut');

            const animationHandler = () => {
                importModal.classList.remove('flex', 'animate__fadeOut');
                importModal.classList.add('hidden');
                importModal.removeEventListener('animationend', animationHandler);
            };
            importModal.addEventListener('animationend', animationHandler);
        }

        function selectFormat(format) {
            closeImportModal();
            if (format === 'json') {
                document.getElementById('import-json-input').click();
            } else if (format === 'sql') {
                document.getElementById('import-sql-input').click();
            }
        }

        /* ==================== LOGIC MODAL DOWNLOAD ==================== */
        function openDownloadModal() {
            const downloadModal = document.getElementById('downloadModal');
            const downloadModalCard = document.getElementById('downloadModalCard');
            if (!downloadModal || !downloadModalCard) return;

            downloadModal.style.setProperty('--animate-duration', '0.35s');
            downloadModalCard.style.setProperty('--animate-duration', '0.35s');
            downloadModal.classList.remove('hidden', 'animate__fadeOut');
            downloadModalCard.classList.remove('animate__zoomOut');
            downloadModal.classList.add('flex', 'animate__fadeIn');
            downloadModalCard.classList.add('animate__zoomIn');
        }

        function closeDownloadModal() {
            const downloadModal = document.getElementById('downloadModal');
            const downloadModalCard = document.getElementById('downloadModalCard');
            if (!downloadModal || !downloadModalCard) return;

            downloadModal.style.setProperty('--animate-duration', '0.35s');
            downloadModalCard.style.setProperty('--animate-duration', '0.35s');
            downloadModal.classList.remove('animate__fadeIn');
            downloadModal.classList.add('animate__fadeOut');
            downloadModalCard.classList.remove('animate__zoomIn');
            downloadModalCard.classList.add('animate__zoomOut');

            const animationHandler = () => {
                downloadModal.classList.remove('flex', 'animate__fadeOut');
                downloadModal.classList.add('hidden');
                downloadModal.removeEventListener('animationend', animationHandler);
            };
            downloadModal.addEventListener('animationend', animationHandler);
        }

        function selectDownloadFormat(format) {
            closeDownloadModal();
            // Menuju endpoint controller berdasarkan format yang dipilih pengguna
            window.location.href = "{{ route('admin.dataset.download') }}?format=" + format;
        }

        /* ==================== PROCESS FILE IMPORT ==================== */
        function handleFileImport(event, format) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();

            if (format === 'json') {
                reader.onload = function(e) {
                    try {
                        const jsonData = JSON.parse(e.target.result);
                        sendImportPayload('json', jsonData);
                    } catch (error) {
                        alert('Format struktur JSON tidak valid!');
                    }
                };
                reader.readAsText(file);
            } 
            else if (format === 'sql') {
                reader.onload = function(e) {
                    const sqlContent = e.target.result;
                    sendImportPayload('sql', sqlContent);
                };
                reader.readAsText(file);
            }

            event.target.value = '';
        }

        function sendImportPayload(formatType, contentData) {
            const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
            if (!csrfTokenElement) {
                alert('CSRF Token tidak ditemukan!');
                return;
            }

            fetch("{{ route('admin.dataset.import') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfTokenElement.getAttribute('content')
                },
                body: JSON.stringify({ 
                    format: formatType,
                    content: contentData 
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success || data.status === 'success') {
                    alert(data.message || 'Dataset berhasil diproses ke database!');
                    if (typeof loadDatasetStats === 'function') loadDatasetStats();
                } else {
                    alert('Gagal memproses file: ' + (data.message || 'Error internal backend'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan jaringan saat mentransfer payload dataset.');
            });
        }
    </script>
</x-app-layout>