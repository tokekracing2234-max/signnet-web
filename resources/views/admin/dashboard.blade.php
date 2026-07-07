<x-app-layout>
    <x-dashboard.styles />

    <div class="dashboard-wrapper antialiased text-custom-title relative min-h-screen">
        <div class="flex flex-col md:flex-row min-h-screen w-full overflow-x-hidden">
            
            <div id="sidebar-container" class="z-[999]">
                @include('layouts.sidebar')
            </div>

            <main class="main-content flex-grow p-4 sm:p-6 md:p-8 lg:p-10 custom-scroll w-full">
                <div class="max-w-7xl mx-auto space-y-6 flex flex-col min-h-full justify-between">
                    
                    <div class="space-y-6 flex-grow">
                        <header class="flex items-center justify-between gap-4 pt-2 animate-card delay-1">
                            <div>
                                <span class="text-indigo-600 dark:text-indigo-400 text-[11px] font-black uppercase tracking-[0.3em]">Performa Sistem</span>
                                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight mt-1">
                                    <span class="logo-gradient">Analisis Model</span> 
                                </h1>
                            </div>

                            <button id="mobileSidebarToggle" type="button" class="md:hidden flex items-center justify-center w-11 h-11 bg-white/90 text-slate-800 dark:bg-slate-900/80 dark:text-white rounded-2xl border border-indigo-500/10 dark:border-white/10 shadow-lg shadow-indigo-500/5 active:scale-95 transition-all duration-200 cursor-pointer">
                                <i class="fa-solid fa-bars text-xl"></i>
                            </button>
                        </header>

                        {{-- Card-card Atas --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div class="glass-card c-bg-1 p-6 rounded-[2rem] relative overflow-hidden animate-card delay-2 border">
                                <span class="text-[10px] font-black uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Total Dataset</span>
                                <h2 id="total-data" class="text-4xl font-black mt-2 text-indigo-600 dark:text-indigo-400">-</h2>
                                <div class="absolute -right-2 -bottom-4 text-indigo-500/20 text-6xl rotate-12"><i class="fa-solid fa-database"></i></div>
                            </div>
                            <div class="glass-card c-bg-2 p-6 rounded-[2rem] relative overflow-hidden animate-card delay-3 border">
                                <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400">Akurasi</span>
                                <h2 id="accuracy-val" class="text-4xl font-black mt-2 text-emerald-600 dark:text-emerald-400">0%</h2>
                                <div class="absolute -right-2 -bottom-4 text-emerald-500/20 text-6xl rotate-12"><i class="fa-solid fa-bullseye"></i></div>
                            </div>
                            <div class="glass-card c-bg-3 p-6 rounded-[2rem] relative overflow-hidden sm:col-span-2 lg:col-span-1 animate-card delay-4 border">
                                <span class="text-[10px] font-black uppercase tracking-widest text-amber-600 dark:text-amber-400">Total Label</span>
                                <h2 id="total-labels" class="text-4xl font-black mt-2 text-amber-600 dark:text-amber-400">-</h2>
                                <div class="absolute -right-2 -bottom-4 text-amber-500/20 text-6xl rotate-12"><i class="fa-solid fa-tags"></i></div>
                            </div>
                        </div>

                        {{-- Grafik Area --}}
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                            {{-- Sisi Kiri: Matriks Konfusi & Distribusi --}}
                            <div class="lg:col-span-7 space-y-6 flex flex-col">
                                <div class="glass-card chart-card-spec c-bg-chart p-6 rounded-[2.5rem] space-y-4 animate-card delay-5 flex-grow">
                                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 relative">
                                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-indigo-400 flex items-center gap-2">
                                            Matriks Konfusi
                                        </h3>
                                        <div class="flex items-center gap-2">
                                            <div class="relative inline-block text-left z-50">
                                                <button id="matrix-select-trigger" onclick="toggleMatrixDropdown(event)" class="dynamic-select text-[10px] font-bold border rounded pl-3 pr-8 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm text-slate-800 dark:text-indigo-400 w-[130px] flex items-center justify-between cursor-pointer relative">
                                                    <span id="matrix-select-text">Semua</span>
                                                    <i class="fa-solid fa-chevron-down text-[8px] absolute right-3 pointer-events-none transition-transform duration-200" id="matrix-select-arrow"></i>
                                                </button>
                                                <ul id="matrix-select-options" class="hidden absolute right-0 mt-1 w-[130px] max-h-[160px] overflow-y-auto rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-lg custom-scroll">
                                                    <li class="px-3 py-1.5 text-[10px] font-bold text-slate-700 dark:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 cursor-pointer" onclick="selectMatrixOption('all', 'Semua')">Semua</li>
                                                    <li class="px-3 py-1.5 text-[10px] font-bold text-slate-700 dark:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 cursor-pointer" onclick="selectMatrixOption('numbers', 'Angka (0-9)')">Angka (0-9)</li>
                                                    <li class="px-3 py-1.5 text-[10px] font-bold text-slate-700 dark:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 cursor-pointer" onclick="selectMatrixOption('letters', 'Huruf (A-Z)')">Huruf (A-Z)</li>
                                                </ul>
                                            </div>
                                            
                                            <button onclick="openInfoModal('matrix')" class="text-slate-400 hover:text-indigo-500 dark:text-slate-500 dark:hover:text-indigo-400 transition-colors p-1">
                                                <i class="fa-solid fa-circle-question text-base"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="relative w-full h-[340px] sm:h-[380px] chart-container flex mt-2">
                                        <div class="flex items-center justify-center pr-2 select-none pointer-events-none">
                                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 [writing-mode:vertical-lr] rotate-180">
                                                ← Actual Class
                                            </span>
                                        </div>
                                        
                                        <div class="flex-grow h-[310px] sm:h-[350px] relative pb-6">
                                            <canvas id="matrixChart" class="w-full h-full"></canvas>
                                            <div class="absolute bottom-0 left-0 w-full text-center text-[9px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 select-none pointer-events-none">
                                                Predicted Class →
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="glass-card chart-card-spec c-bg-chart p-6 rounded-[2.5rem] space-y-4 animate-card delay-6">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-indigo-400">Distribusi Dataset</h3>
                                        <button onclick="openInfoModal('distribution')" class="text-slate-400 hover:text-indigo-500 dark:text-slate-500 dark:hover:text-indigo-400 transition-colors p-1">
                                            <i class="fa-solid fa-circle-question text-base"></i>
                                        </button>
                                    </div>
                                    <div class="relative h-[280px] w-full chart-container">
                                        <canvas id="distChart"></canvas>
                                    </div>
                                </div>
                            </div>

                            <div class="lg:col-span-5 space-y-6 flex flex-col items-start justify-start w-full">
                                <div class="glass-card chart-card-spec c-bg-chart p-6 rounded-[2.5rem] space-y-4 animate-card delay-6 w-full">
                                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 relative">
                                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-indigo-400 flex items-center gap-2">
                                            <i class="fa-solid fa-chart-radar text-indigo-500"></i> Evaluasi Per Label
                                        </h3>
                                        
                                        <div class="relative inline-block text-left z-50">
                                            <button id="custom-select-trigger" onclick="toggleCustomDropdown(event)" class="dynamic-select text-[10px] font-bold border rounded pl-3 pr-8 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm text-slate-800 dark:text-indigo-400 w-[115px] flex items-center justify-between cursor-pointer relative">
                                                <span id="custom-select-text">Pilih Label</span>
                                                <i class="fa-solid fa-chevron-down text-[8px] absolute right-3 pointer-events-none transition-transform duration-200" id="custom-select-arrow"></i>
                                            </button>
                                            <ul id="custom-select-options" class="hidden absolute right-0 mt-1 w-[115px] max-h-[160px] overflow-y-auto rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-lg custom-scroll">
                                                </ul>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-3 gap-3 pt-1">
                                        <div class="p-3 bg-indigo-500/5 dark:bg-indigo-500/10 rounded-2xl border border-indigo-500/10 dark:border-indigo-500/20 text-center">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Precision</span>
                                            <div id="eval-precision" class="text-xl font-black mt-1 text-indigo-600 dark:text-indigo-400">0%</div>
                                        </div>
                                        <div class="p-3 bg-emerald-500/5 dark:bg-emerald-500/10 rounded-2xl border border-emerald-500/10 dark:border-emerald-500/20 text-center">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Recall</span>
                                            <div id="eval-recall" class="text-xl font-black mt-1 text-emerald-600 dark:text-emerald-400">0%</div>
                                        </div>
                                        <div class="p-3 bg-amber-500/5 dark:bg-amber-500/10 rounded-2xl border border-amber-500/10 dark:border-amber-500/20 text-center">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">F1-Score</span>
                                            <div id="eval-f1" class="text-xl font-black mt-1 text-amber-600 dark:text-amber-400">0%</div>
                                        </div>
                                    </div>

                                    <div class="bg-indigo-500/[0.03] dark:bg-slate-900/50 p-4 rounded-2xl space-y-3 border border-indigo-500/10 dark:border-slate-800/40 text-xs">
                                        <div class="flex items-start gap-3">
                                            <div class="w-5 h-5 rounded-md bg-indigo-500/10 dark:bg-indigo-500/20 flex items-center justify-center shrink-0 mt-0.5">
                                                <i class="fa-solid fa-bullseye text-indigo-600 dark:text-indigo-400 text-[10px]"></i>
                                            </div>
                                            <p class="text-slate-600 dark:text-slate-400 leading-relaxed text-justify w-full">
                                                <strong class="text-slate-800 dark:text-slate-200 font-bold">Ketepatan (Precision):</strong> 
                                                Tingkat kebenaran prediksi model saat menebak label ini dari total seluruh tebakan.
                                            </p>
                                        </div>
                                        
                                        <div class="flex items-start gap-3">
                                            <div class="w-5 h-5 rounded-md bg-emerald-500/10 dark:bg-emerald-500/20 flex items-center justify-center shrink-0 mt-0.5">
                                                <i class="fa-solid fa-magnifying-glass text-emerald-600 dark:text-emerald-400 text-[10px]"></i>
                                            </div>
                                            <p class="text-slate-600 dark:text-slate-400 leading-relaxed text-justify w-full">
                                                <strong class="text-slate-800 dark:text-slate-200 font-bold">Daya Tangkap (Recall):</strong> 
                                                Rasio seberapa banyak sampel asli dari label ini yang berhasil dideteksi dengan benar oleh sistem.
                                            </p>
                                        </div>

                                        <div class="flex items-start gap-3">
                                            <div class="w-5 h-5 rounded-md bg-amber-500/10 dark:bg-amber-500/20 flex items-center justify-center shrink-0 mt-0.5">
                                                <i class="fa-solid fa-scale-balanced text-amber-600 dark:text-amber-400 text-[10px]"></i>
                                            </div>
                                            <p class="text-slate-600 dark:text-slate-400 leading-relaxed text-justify w-full">
                                                <strong class="text-slate-800 dark:text-slate-200 font-bold">F1-Score:</strong> 
                                                Nilai rata-rata harmonik rata tengah yang menyeimbangkan metrik Precision dan Recall. Cocok digunakan sebagai acuan performa jika distribusi data tidak seimbang.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <footer class="footer-bottom pt-8 pb-2 text-center animate-card delay-6" style="animation-delay: 0.5s;">
                        <p class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 tracking-wider">
                            © 2026 SignNet Project • Bahasa Isyarat BISINDO.
                        </p>
                    </footer>

                </div>
            </main>
        </div>
    </div>

    {{-- MODAL INFO --}}
    <div id="infoModal" class="fixed inset-0 z-[1000] hidden items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">  
        <div class="absolute inset-0" onclick="toggleModal(false)"></div>

        {{-- Konten Utama Modal --}}
        <div id="modalContent" class="glass-card modal-spec-card c-bg-chart w-full max-w-lg p-7 rounded-[2rem] shadow-2xl space-y-5 relative z-10">   
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 id="modalTitle" class="text-xs font-black uppercase tracking-widest text-indigo-600 dark:text-indigo-400 flex items-center gap-2.5"></h3>
                <button type="button" onclick="toggleModal(false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors p-1 rounded-lg cursor-pointer">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>
            <div id="modalBody" class="text-xs text-custom-muted leading-relaxed space-y-4 text-justify"></div>
        </div>
    </div>

    <x-dashboard.scripts />
</x-app-layout>