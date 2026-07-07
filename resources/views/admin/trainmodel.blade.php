<x-app-layout>
    <x-trainmodel.styles />

    <div class="dashboard-wrapper antialiased text-custom-title relative min-h-screen bg-white dark:bg-slate-950 transition-colors duration-200">
        <div class="flex flex-col md:flex-row min-h-screen w-full">

            <div id="sidebar-container" class="z-[999] shrink-0">
                @include('layouts.sidebar')
            </div>

            {{-- Main Content Content Area --}}
            <main class="main-content flex-grow p-4 sm:p-6 md:p-8 lg:p-10 w-full overflow-y-auto h-screen custom-scroll">
                <div class="max-w-7xl mx-auto space-y-6 flex flex-col min-h-full justify-between">
                    
                    <div class="space-y-6 flex-grow">
                        <header class="flex items-center justify-between gap-4 pt-2 animate-card delay-1 w-full">
                            <div class="min-w-0">
                                <span class="text-indigo-600 dark:text-indigo-400 text-[10px] font-bold uppercase tracking-[0.3em] block">Akuisisi Dataset</span>
                                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight mt-1 whitespace-nowrap">
                                    <span class="logo-gradient">SignNet</span>
                                    <span class="text-slate-700 dark:text-white font-light italic text-xl md:text-2xl ml-1">Trainer</span>
                                </h1>
                            </div>

                            <div class="flex items-center gap-3 shrink-0 justify-end">
                                <button onclick="openModalGuide()" class="hidden sm:flex px-4 py-2 bg-indigo-600/10 hover:bg-indigo-600/20 text-indigo-700 dark:text-indigo-400 border border-indigo-500/30 rounded-xl text-xs font-bold tracking-wide transition-all items-center gap-2 shadow-sm">
                                    <i class="fa-solid fa-circle-info text-indigo-600 dark:text-indigo-400"></i> Panduan Fitur
                                </button>

                                <div class="flex bg-indigo-900/10 dark:bg-white/5 p-1 rounded-xl border border-indigo-500/20 dark:border-white/10 w-fit">
                                    <button onclick="switchMode('video')" id="btn-mode-video"
                                        class="px-4 py-1.5 rounded-lg text-[10px] font-black transition-all bg-indigo-600 text-white shadow-sm shadow-indigo-600/20">
                                        VIDEO
                                    </button>
                                    <button onclick="switchMode('photo')" id="btn-mode-photo"
                                        class="px-4 py-1.5 rounded-lg text-[10px] font-black transition-all text-indigo-900 dark:text-slate-400">
                                        FOTO
                                    </button>
                                </div>

                                {{-- Tombol Hamburger Menu --}}
                                <button id="mobileSidebarToggle" type="button" class="md:hidden flex items-center justify-center w-11 h-11 bg-slate-100 dark:bg-slate-900/80 text-slate-800 dark:text-white rounded-2xl border border-slate-200 dark:border-white/10 shadow-lg shadow-indigo-500/5 active:scale-95 transition-all duration-200 cursor-pointer">
                                    <i class="fa-solid fa-bars text-xl"></i>
                                </button>
                            </div>
                        </header>

                        {{-- Panduan Fitur --}}
                        <div class="block sm:hidden animate-card delay-1">
                            <button onclick="openModalGuide()" class="w-full flex px-4 py-3 bg-indigo-600/10 hover:bg-indigo-600/20 text-indigo-700 dark:text-indigo-400 border border-indigo-500/30 rounded-xl text-xs font-bold tracking-wide transition-all items-center justify-center gap-2 shadow-sm">
                                <i class="fa-solid fa-circle-info text-indigo-600 dark:text-indigo-400"></i> PANDUAN FITUR
                            </button>
                        </div>

                        {{-- Main Grid Content Panel --}}
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                            {{-- Camera Panel --}}
                            <div class="lg:col-span-7 xl:col-span-8 space-y-4 animate-card delay-2">
                                <div id="camera-container"
                                    class="relative rounded-[1.5rem] md:rounded-[2rem] overflow-hidden bg-black aspect-video camera-glow border border-indigo-500/30">
                                    <div id="timer-display" class="timer-overlay">5</div>
                                    <div id="flash-overlay" class="absolute inset-0 z-50 pointer-events-none"></div>

                                    <video id="input_video" class="hidden"></video>
                                    <canvas id="output_canvas" class="w-full h-full object-cover"></canvas>

                                    <div class="absolute top-4 left-4 z-10">
                                        <span class="glass px-3 py-1 rounded-full text-[10px] font-bold tracking-widest flex items-center gap-2 text-slate-800 dark:text-white">
                                            <div id="live-indicator" class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                                            <span id="live-text" class="text-indigo-900 dark:text-white">LIVE ON</span>
                                        </span>
                                    </div>
                                </div>

                                <div class="glass p-3 px-5 rounded-2xl flex items-center justify-between animate-card delay-3">
                                    <p id="mode-desc" class="text-[9px] text-indigo-950 dark:text-slate-400 uppercase tracking-widest font-black">
                                        Mode: Video (Timer 5dtk)
                                    </p>
                                    <span id="hand-count" class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 italic">
                                        Mencari tangan...
                                    </span>
                                </div>
                            </div>

                            {{-- Control Panel --}}
                            <div class="lg:col-span-5 xl:col-span-4 h-full animate-card delay-4">
                                <div class="panel-control p-6 md:p-8 rounded-[2rem] space-y-5 border border-indigo-500/20 shadow-xl flex flex-col h-full justify-between bg-white/40 dark:bg-slate-900/40 dashboard-card-glow">

                                    <div class="space-y-4">
                                        <div class="space-y-2">
                                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 ml-1">
                                                Label Gestur
                                            </label>
                                            <select id="label-select" class="w-full dynamic-select border border-indigo-500/30 dark:border-white/10 p-3.5 rounded-xl outline-none focus:border-indigo-500 transition-all font-bold text-sm shadow-sm bg-transparent">
                                                <option value="" disabled selected>-- Pilih Label --</option>
                                                <optgroup label="Huruf" id="alphabet-group"></optgroup>
                                                <optgroup label="Angka" id="digit-group"></optgroup>
                                            </select>
                                        </div>
                                        <input type="text" id="label-manual" placeholder="Ketik label..."
                                            class="hidden w-full bg-white dark:bg-white/5 border border-indigo-500/30 dark:border-white/10 p-3.5 rounded-xl outline-none focus:border-indigo-500 font-bold uppercase text-sm text-slate-900 dark:text-white shadow-inner">
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="bg-indigo-50/50 dark:bg-white/5 border border-slate-200 dark:border-white/5 p-4 rounded-2xl shadow-sm">
                                            <span class="block text-[8px] font-black text-indigo-700 dark:text-indigo-400 uppercase mb-1">
                                                Total Sampel
                                            </span>
                                            <span id="sample-count" class="text-3xl font-black text-indigo-950 dark:text-white">0</span>
                                        </div>
                                        
                                        <div class="bg-indigo-500/5 border border-indigo-500/20 dark:border-white/10 p-4 rounded-2xl shadow-sm">
                                            <span class="block text-[8px] font-black text-indigo-700 dark:text-indigo-400 uppercase mb-2">
                                                Statistik Label
                                            </span>
                                            <div id="label-stats-container" class="max-h-[35px] overflow-y-auto custom-scroll grid grid-cols-2 gap-1 text-[9px] font-bold text-slate-800 dark:text-slate-300">
                                                <span class="text-slate-500 dark:text-slate-600">Kosong</span>
                                            </div>
                                        </div>
                                    </div>

                                    <button id="main-action-btn" class="w-full py-4 bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 text-white rounded-xl font-black uppercase tracking-widest transition-all active:scale-95 flex items-center justify-center gap-3">
                                        <i id="btn-icon" class="fa-solid fa-stopwatch text-lg"></i>
                                        <span class="text-sm">Mulai Timer</span>
                                    </button>

                                    <div class="mt-2 pt-4 border-t border-indigo-500/20 dark:border-white/10">
                                        <button id="btn-train" onclick="prosesTraining()" class="w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-black text-xs tracking-widest transition-all flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/20">
                                            <i class="fa-solid fa-brain"></i>
                                            TRAIN MODEL SEKARANG
                                        </button>
                                        <p class="text-[8px] text-indigo-950/60 dark:text-slate-500 mt-2 text-center uppercase tracking-tighter font-semibold">
                                            Klik setelah menambah label atau data baru
                                        </p>
                                    </div>

                                    <div id="shortcut-container" class="grid grid-cols-3 gap-2 mt-2"></div>

                                    <div class="pt-4 border-t border-indigo-500/20 dark:border-white/10 flex-1 min-h-[100px]">
                                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-2 block">
                                            System Log
                                        </span>
                                        <div id="log-box" class="h-full max-h-[120px] overflow-y-auto custom-scroll space-y-1 text-[10px] font-mono text-indigo-700 dark:text-emerald-400/80 bg-white/50 dark:bg-black/20 p-2 rounded-xl border border-indigo-500/10">
                                            <div class="font-bold">&gt; Inisialisasi sistem...</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <footer class="footer-bottom pt-8 pb-2 text-center animate-card delay-6" style="animation-delay: 0.4s;">
                        <p class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 tracking-wider">
                            © 2026 SignNet Project • Bahasa Isyarat BISINDO.
                        </p>
                    </footer>

                </div>
            </main>
        </div>
    </div>

    {{-- MODAL PANDUAN --}}
    <div id="modal-guide" class="fixed inset-0 z-[120] items-center justify-center p-4 modal-blur-bg hidden">
        <div class="modal-content glass max-w-lg w-full rounded-[2.5rem] border border-indigo-500/30 shadow-2xl overflow-hidden flex flex-col max-h-[85vh]">
            <div class="p-6 border-b border-indigo-500/20 flex justify-between items-center bg-indigo-500/5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-md shadow-indigo-600/20">
                        <i class="fa-solid fa-book-open text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black logo-gradient tracking-tight">PANDUAN SISTEM</h3>
                        <p class="text-[9px] text-indigo-600 dark:text-slate-400 font-bold uppercase tracking-widest">3 Langkah Mudah Manajemen Dataset</p>
                    </div>
                </div>
                <button onclick="closeModalGuide()" class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-slate-200 dark:hover:bg-white/10 transition-all text-slate-400 hover:text-slate-600 dark:hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto custom-scroll space-y-5 text-sm">
                <div class="flex gap-4 p-3 rounded-2xl bg-indigo-500/5 dark:bg-slate-900/50 border border-indigo-500/20">
                    <div class="text-indigo-600 font-extrabold text-lg">01</div>
                    <div>
                        <h4 class="font-bold text-indigo-950 dark:text-white text-xs uppercase tracking-wide">Pilih atau Masukkan Label</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Tentukan gestur BISINDO yang ingin Anda rekam (Huruf A-Z, Angka 0-9).</p>
                    </div>
                </div>
                <div class="flex gap-4 p-3 rounded-2xl bg-indigo-500/5 dark:bg-slate-900/50 border border-indigo-500/20">
                    <div class="text-indigo-600 font-extrabold text-lg">02</div>
                    <div>
                        <h4 class="font-bold text-indigo-950 dark:text-white text-xs uppercase tracking-wide">Pilih Mode & Mulai Akuisisi</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Gunakan mode <b>FOTO</b> untuk tangkapan tunggal, atau mode <b>VIDEO</b> untuk merekam beberapa variasi gerakan secara sekuensial selama hitung mundur.</p>
                    </div>
                </div>
                <div class="flex gap-4 p-3 rounded-2xl bg-indigo-500/5 dark:bg-slate-900/50 border border-indigo-500/20">
                    <div class="text-indigo-600 font-extrabold text-lg">03</div>
                    <div>
                        <h4 class="font-bold text-indigo-950 dark:text-white text-xs uppercase tracking-wide">Train Model Artificial Intelligence</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Setelah sampel dirasa cukup, tekan tombol <span class="text-emerald-600 font-bold">"TRAIN MODEL SEKARANG"</span> untuk mengevaluasi akurasi jaringan SignNet.</p>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-slate-900/10 dark:bg-slate-950/50 border-t border-indigo-500/20 flex justify-end">
                <button onclick="closeModalGuide()" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl transition-all text-xs uppercase tracking-wider shadow-md">
                    Saya Mengerti
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL RESULT EVALUASI --}}
    <div id="modal-result" class="fixed inset-0 z-[110] items-center justify-center p-4 modal-blur-bg hidden">
        <div class="modal-content glass max-w-2xl w-full rounded-[2.5rem] border border-indigo-500/30 shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="p-6 border-b border-indigo-500/20 flex justify-between items-center bg-white/5">
                <div>
                    <h3 class="text-2xl font-black logo-gradient tracking-tight">EVALUASI MODEL</h3>
                    <p class="text-[10px] text-indigo-600 dark:text-slate-400 font-bold uppercase tracking-widest">Performansi Real-time AI</p>
                </div>
                <button onclick="closeModalResult()" class="w-10 h-10 rounded-full flex items-center justify-center hover:bg-slate-200 dark:hover:bg-white/10 transition-all text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <div class="p-6 md:p-8 overflow-y-auto custom-scroll space-y-8">
                <div class="relative overflow-hidden bg-indigo-600/20 border border-indigo-500/30 p-8 rounded-[2rem] text-center">
                    <div class="relative z-10">
                        <span class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-[0.3em]">Akurasi Keseluruhan</span>
                        <div id="accuracy-value" class="text-6xl md:text-7xl font-black text-slate-800 dark:text-white mt-2 mb-1">0%</div>
                        <p class="text-slate-500 dark:text-slate-400 text-xs italic">Dihitung berdasarkan Testing Set (80/20 split)</p>
                    </div>
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-indigo-500/20 blur-3xl rounded-full"></div>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="h-px flex-1 bg-indigo-500/20"></div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-indigo-600 dark:text-slate-500">Analisis F1-Score Per Label</span>
                        <div class="h-px flex-1 bg-indigo-500/20"></div>
                    </div>
                    <div id="report-table-container" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3"></div>
                </div>
            </div>

            <div class="p-6 bg-slate-900/10 dark:bg-slate-950/50 border-t border-indigo-500/20">
                <button onclick="closeModalResult()" class="w-full py-4 bg-indigo-600 hover:bg-indigo-500 text-white font-black rounded-2xl transition-all shadow-lg shadow-indigo-600/20 active:scale-[0.98] uppercase tracking-widest text-sm">
                    Selesai & Lanjutkan
                </button>
            </div>
        </div>
    </div>

    <x-trainmodel.scripts />
</x-app-layout>