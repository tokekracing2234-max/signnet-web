<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>SignNet - Ubah Gestur Menjadi Huruf dan Angka</title>
        
        <script>
            if (localStorage.getItem('theme') === 'light') {
                document.documentElement.classList.remove('dark');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        </script>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .chat-animate-in { animation: slideIn 0.3s ease-out forwards; }
            .chat-animate-out { animation: slideOut 0.3s ease-in forwards; }
            @keyframes slideIn { from { opacity: 0; transform: translateY(20px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
            @keyframes slideOut { from { opacity: 1; transform: translateY(0) scale(1); } to { opacity: 0; transform: translateY(20px) scale(0.95); } }
            #chat-content::-webkit-scrollbar { width: 5px; }
            #chat-content::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
            .animate-fade-in { animation: fadeIn 0.2s ease-in; }
            @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

            .custom-scrollbar::-webkit-scrollbar { width: 4px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
            .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
            
            /* Custom CSS Loader */
            .custom-loader {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                background: conic-gradient(#0000 10%, #3b82f6);
                -webkit-mask: radial-gradient(farthest-side, #0000 calc(100% - 8px), #000 0);
                animation: spin 0.8s infinite linear;
            }

            @keyframes spin { to { transform: rotate(1turn); } }
            .swal2-container { z-index: 999999 !important; }
            .swal-custom-icon { position: relative; width: 80px; height: 80px; margin: 0 auto 20px; border: 4px solid; border-radius: 50%; }
            .swal-icon-wrapper { width: 80px; height: 80px; margin: 10px auto 20px; }
            .ft-green-tick { width: 80px; height: 80px; border-radius: 50%; display: block; stroke-width: 2; stroke: #22c55e; stroke-miterlimit: 10; box-shadow: inset 0px 0px 0px #22c55e; animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both; }
            .ft-green-tick-circle { stroke-dasharray: 166; stroke-dashoffset: 166; stroke-width: 4; stroke-miterlimit: 10; stroke: #22c55e; fill: none; animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards; }
            .ft-green-tick-check { transform-origin: 50% 50%; stroke-dasharray: 48; stroke-dashoffset: 48; animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards; }
            
            @keyframes stroke { 100% { stroke-dashoffset: 0; } }
            @keyframes scale { 0%, 100% { transform: none; } 50% { transform: scale3d(1.1, 1.1, 1); } }
            @keyframes fill { 100% { box-shadow: inset 0px 0px 0px 40px rgba(34, 197, 94, 0.1); } }
            .animate-x-mark { animation: swal-animate-x-mark 0.5s; }
            @keyframes swal-animate-x-mark { 0% { transform: scale(0.4); opacity: 0; } 50% { transform: scale(1.03); } 80% { transform: scale(0.98); } 100% { transform: scale(1); opacity: 1; } }
        </style>
    </head>
    <body class="antialiased bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors duration-200" style="font-family: 'Plus Jakarta Sans', sans-serif;">
        
        <div id="global-loader" class="fixed inset-0 z-[999999] hidden flex-col items-center justify-center bg-slate-950/80 backdrop-blur-sm transition-all duration-300">
            <div class="flex flex-col items-center p-8 rounded-3xl bg-slate-900 border border-white/10 shadow-2xl">
                <div class="custom-loader mb-4"></div>
                <p class="text-xs font-black tracking-[0.2em] text-blue-500 uppercase animate-pulse">Memproses Data...</p>
                <p class="text-[10px] text-slate-400 mt-1">Mohon tunggu sebentar</p>
            </div>
        </div>

        <div class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors duration-200">
            @isset($header)
                <header class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-white/5 shadow-sm">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main>
                {{ $slot }}
            </main>
        </div>

        <script>
            const getSwalTheme = () => ({
                isDark: document.documentElement.classList.contains('dark'),
                background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
                color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#000'
            });

            const AppAlert = {
                fire(type, title, message) {
                    const loader = document.getElementById('global-loader');
                    if (loader) {
                        loader.classList.remove('flex');
                        loader.classList.add('hidden');
                    }

                    const theme = getSwalTheme();
                    const config = {
                        success: { 
                            color: '#22c55e', label: 'SUCCESS',
                            iconHtml: `
                                <div class="swal-icon-wrapper">
                                    <svg class="ft-green-tick" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                                        <circle class="ft-green-tick-circle" cx="26" cy="26" r="25" fill="none" stroke="#22c55e" stroke-width="4"/>
                                        <path class="ft-green-tick-check" fill="none" stroke="#22c55e" stroke-width="6" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                                    </svg>
                                </div>` 
                        },
                        error: { 
                            color: '#ef4444', label: 'SYSTEM ERROR',
                            iconHtml: `
                                <div class="swal-custom-icon animate-x-mark flex items-center justify-center" style="border-color: #ef4444">
                                    <svg class="w-10 h-10" fill="none" stroke="#ef4444" stroke-width="4" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>` 
                        },
                        warning: { 
                            color: '#f59e0b', label: 'WARNING',
                            iconHtml: `
                                <div class="swal-custom-icon flex items-center justify-center" style="border-color: #f59e0b">
                                    <svg class="w-10 h-10 animate-bounce" fill="none" stroke="#f59e0b" stroke-width="4" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>` 
                        },
                        info: { 
                            color: '#3b82f6', label: 'INFORMATION',
                            iconHtml: `
                                <div class="swal-custom-icon flex items-center justify-center" style="border-color: #3b82f6">
                                    <svg class="w-10 h-10 animate-pulse" fill="none" stroke="#3b82f6" stroke-width="4" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>` 
                        }
                    }[type] || { color: '#3b82f6', label: 'NOTIFICATION', iconHtml: '' };

                    return Swal.fire({
                        title: title.toUpperCase(),
                        html: `
                            <div class="p-2 text-center">
                                ${config.iconHtml}
                                <p class="text-[10px] font-black tracking-[0.3em] text-slate-500 uppercase mb-3">System Status: <span style="color: ${config.color}">${config.label}</span></p>
                                <p class="text-sm text-slate-300 font-medium px-4 leading-relaxed">${message}</p>
                            </div>
                        `,
                        buttonsStyling: false,
                        background: theme.background,
                        color: theme.color,
                        timer: 4000,
                        timerProgressBar: true,
                        showConfirmButton: true,
                        confirmButtonText: 'MENGERTI',
                        customClass: {
                            popup: `rounded-[2.5rem] border border-slate-800/80 shadow-2xl p-6`,
                            title: `text-xl font-black tracking-tighter pt-4`,
                            confirmButton: `px-10 py-3.5 rounded-2xl font-black text-[11px] uppercase tracking-widest active:scale-95 transition-all mb-4`
                        },
                        didOpen: (el) => {
                            el.querySelector('.swal2-title').style.color = config.color;
                            
                            const btn = el.querySelector('.swal2-confirm');
                            if (btn) {
                                btn.style.setProperty('background-color', config.color, 'important');
                                btn.style.setProperty('color', '#ffffff', 'important');
                            }

                            const progressBar = el.querySelector('.swal2-timer-progress-bar');
                            if (progressBar) {
                                progressBar.style.backgroundColor = config.color;
                            }
                        }
                    });
                }
            };

            document.addEventListener('DOMContentLoaded', function() {
                @if(session('success')) AppAlert.fire('success', 'BERHASIL', "{{ session('success') }}"); @endif
                @if(session('error') || $errors->any()) AppAlert.fire('error', 'GAGAL', "{{ session('error') ?? $errors->first() }}"); @endif
                @if(session('info')) AppAlert.fire('info', 'INFO', "{{ session('info') }}"); @endif

                const loader = document.getElementById('global-loader');
                const showLoader = () => { 
                    loader.classList.remove('hidden'); 
                    loader.classList.add('flex');
                };
                const hideLoader = () => {
                    loader.classList.remove('flex');
                    loader.classList.add('hidden');
                };

                document.addEventListener('submit', function (e) {
                    if (e.target && e.target.id === 'logout-form') {
                        if (!e.target.hasAttribute('data-confirmed')) {
                            e.preventDefault();
                            
                            const theme = getSwalTheme();
                            Swal.fire({
                                title: 'KELUAR APLIKASI',
                                html: `
                                    <div class="p-2 text-center">
                                        <div class="swal-custom-icon flex items-center justify-center" style="border-color: #ef4444">
                                            <svg class="w-10 h-10" fill="none" stroke="#ef4444" stroke-width="4" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"></path>
                                            </svg>
                                        </div>
                                        <p class="text-[10px] font-black tracking-[0.3em] text-slate-500 uppercase mb-3">Sesi: <span class="text-rose-500">LOGOUT</span></p>
                                        <p class="text-sm text-slate-300 font-medium px-4 leading-relaxed">Apakah Anda yakin ingin keluar dari sistem SignNet?</p>
                                    </div>
                                `,
                                background: theme.background,
                                color: theme.color,
                                buttonsStyling: false,
                                showCancelButton: true,
                                confirmButtonText: 'YA, LOGOUT',
                                cancelButtonText: 'BATAL',
                                customClass: {
                                    popup: `rounded-[2.5rem] border border-slate-800/80 shadow-2xl p-6`,
                                    title: `text-xl font-black tracking-tighter pt-4 text-rose-500`,
                                    confirmButton: `px-6 py-3.5 rounded-2xl font-black text-[11px] uppercase tracking-widest active:scale-95 transition-all mb-4 mr-2`,
                                    cancelButton: `px-6 py-3.5 rounded-2xl font-black text-[11px] uppercase tracking-widest active:scale-95 transition-all mb-4 bg-slate-800 text-slate-400 hover:text-white`
                                },
                                didOpen: (el) => {
                                    const confirmBtn = el.querySelector('.swal2-confirm');
                                    if (confirmBtn) {
                                        confirmBtn.style.setProperty('background-color', '#ef4444', 'important');
                                        confirmBtn.style.setProperty('color', '#ffffff', 'important');
                                    }
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    e.target.setAttribute('data-confirmed', 'true');
                                    showLoader();
                                    e.target.submit();
                                }
                            });
                            return;
                        }
                    }

                    if (e.target.target !== '_blank') showLoader();
                }, true);

                window.addEventListener('pageshow', hideLoader);
                document.addEventListener('invalid', function(e) {
                    hideLoader();
                }, true);

                document.addEventListener('click', function (e) {
                    const link = e.target.closest('a');
                    if (link && link.href && 
                        !link.href.includes('#') && 
                        !link.href.startsWith('javascript') && 
                        link.target !== '_blank' &&
                        !link.hasAttribute('download') &&
                        !link.getAttribute('href').includes('logout')) { 
                        showLoader();
                    }
                }, true);

                window.addEventListener('themeChanged', (e) => {
                    if (e.detail.theme === 'light') {
                        document.documentElement.classList.remove('dark');
                    } else {
                        document.documentElement.classList.add('dark');
                    }
                });
            });
        </script>
        @stack('modals')
    </body>
</html>