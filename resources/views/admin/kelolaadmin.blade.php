<x-app-layout>
    <x-kelolaadmin.styles />

    <div class="dashboard-wrapper antialiased text-slate-900 dark:text-white relative min-h-screen bg-white dark:bg-slate-950 transition-colors duration-200">
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
                                    Manajemen Admin
                                </span>
                                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight mt-1 whitespace-nowrap">
                                    <span class="logo-gradient">Kelola Akun Admin</span>
                                </h1>
                            </div>

                            <div class="flex items-center gap-3 shrink-0 justify-end">
                                <button type="button" onclick="openAddModal()" 
                                    class="hidden sm:flex items-center justify-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 rounded-xl font-bold text-[11px] tracking-widest transition-all text-white active:scale-95 shadow-lg shadow-indigo-600/20">
                                    <i class="fa-solid fa-user-plus mr-2 text-[10px]"></i>TAMBAH ADMIN BARU
                                </button>

                                <button id="mobileSidebarToggle" type="button" class="md:hidden flex items-center justify-center w-11 h-11 bg-slate-100 dark:bg-slate-900/80 text-slate-800 dark:text-white rounded-2xl border border-slate-200 dark:border-white/10 shadow-lg shadow-indigo-500/5 active:scale-95 transition-all duration-200 cursor-pointer">
                                    <i class="fa-solid fa-bars text-xl"></i>
                                </button>
                            </div>
                        </header>

                        <div class="block sm:hidden animate-card delay-1">
                            <button type="button" onclick="openAddModal()" 
                                class="flex items-center justify-center w-full px-5 py-3 bg-indigo-600 hover:bg-indigo-500 rounded-xl font-bold text-[11px] tracking-widest transition-all text-white active:scale-95 shadow-lg shadow-indigo-600/20">
                                <i class="fa-solid fa-user-plus mr-2 text-[10px]"></i>TAMBAH ADMIN BARU
                            </button>
                        </div>

                        <div class="animate-card delay-2 text-[11px] text-slate-600 dark:text-slate-400 leading-relaxed flex items-start gap-2.5 bg-indigo-500/5 dark:bg-indigo-500/5 p-4 rounded-[1.5rem] border border-indigo-500/10 shadow-sm">
                            <i class="fa-solid fa-circle-info text-indigo-500 dark:text-indigo-400 mt-0.5 text-xs"></i>
                            <div class="text-justify">
                                <span class="font-bold text-slate-800 dark:text-slate-200 block mb-0.5">Panduan & Keamanan Panel Manajemen Admin:</span>
                                Pendaftaran atau perubahan data akun wajib divalidasi dengan benar. Demi menjaga kedaulatan hak akses, sistem memantau setiap aktivitas perubahan secara real-time berdasarkan riwayat jejak digital perangkat resmi Anda. Akun administrator utama (<code class="bg-indigo-500/10 px-1.5 py-0.5 rounded text-indigo-600 dark:text-indigo-400 font-mono font-bold">admin</code>) dilindungi secara mutlak by sistem backend.
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                            <div class="glass card-stat-blue p-6 rounded-[2rem] relative overflow-hidden animate-card delay-1 border">
                                <span class="text-[10px] font-black uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Total Administrator</span>
                                <div class="flex items-end gap-2 mt-2">
                                    <span class="text-4xl font-black text-indigo-600 dark:text-indigo-400" id="total-admins">0</span>
                                    <span class="text-indigo-500/70 text-[10px] font-bold mb-1">Pengguna</span>
                                </div>
                                <div class="absolute -right-2 -bottom-4 text-indigo-500/20 text-6xl rotate-12">
                                    <i class="fa-solid fa-user-shield"></i>
                                </div>
                            </div>
                            
                            <div class="glass card-stat-green p-6 rounded-[2rem] relative overflow-hidden animate-card delay-2 border">
                                <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400">Admin Baru (Bulan Ini)</span>
                                <div class="flex items-end gap-2 mt-2">
                                    <span class="text-4xl font-black text-emerald-600 dark:text-emerald-400" id="new-admins">0</span>
                                    <span class="text-emerald-500/70 text-[10px] font-bold mb-1">Terdaftar</span>
                                </div>
                                <div class="absolute -right-2 -bottom-4 text-emerald-500/20 text-6xl rotate-12">
                                    <i class="fa-solid fa-calendar-plus"></i>
                                </div>
                            </div>
                            
                            <div class="glass card-stat-amber p-6 rounded-[2rem] relative overflow-hidden animate-card delay-3 border">
                                <span class="text-[10px] font-black uppercase tracking-widest text-amber-600 dark:text-amber-400">Admin Profil Aktif</span>
                                <div class="flex items-end gap-2 mt-2">
                                    <span class="text-4xl font-black text-amber-600 dark:text-amber-400" id="active-admins">0</span>
                                    <span class="text-amber-500/70 text-[10px] font-bold mb-1">Online / Ready</span>
                                </div>
                                <div class="absolute -right-2 -bottom-4 text-amber-500/20 text-6xl rotate-12">
                                    <i class="fa-solid fa-circle-check"></i>
                                </div>
                            </div>
                        </div>

                        <div class="glass rounded-[2rem] overflow-hidden animate-card delay-3 border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900/40 flex flex-col justify-between max-w-full">
                            <div class="p-6 border-b border-slate-200 dark:border-white/5 flex justify-between items-center bg-indigo-50/30 dark:bg-white/5">
                                <h3 class="text-xs font-black tracking-widest uppercase text-indigo-600 dark:text-indigo-400">Daftar Akun Admin</h3>
                                <button onclick="fetchAdminData()" class="bg-white dark:bg-white/5 p-2 rounded-lg text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white border border-slate-200 dark:border-none transition-colors">
                                    <i class="fa-solid fa-rotate text-xs"></i>
                                </button>
                            </div>
                            
                            <div class="table-overflow overflow-x-auto hidden md:block">
                                <table class="w-full text-left border-collapse min-w-full sm:min-w-[750px]">
                                    <thead>
                                        <tr class="text-[10px] font-black uppercase tracking-widest text-indigo-600/80 dark:text-indigo-400/70 border-b border-slate-200 dark:border-white/5 bg-slate-50/50 dark:bg-transparent">
                                            <th class="px-6 py-4 text-center w-16">No.</th>
                                            <th class="px-6 py-4 text-center">Username</th>
                                            <th class="px-8 py-4 text-center">Email</th>
                                            <th class="px-8 py-4 text-center">Terdaftar Pada</th>
                                            <th class="px-8 py-4 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="admin-table-body" class="text-sm text-slate-700 dark:text-slate-300"></tbody>
                                </table>
                            </div>

                            <div class="block md:hidden p-4 w-full overflow-hidden">
                                <div id="admin-cards-container" class="grid grid-cols-1 gap-4 w-full overflow-hidden">
                                </div>
                            </div>

                            <div class="px-6 py-4 border-t border-slate-200 dark:border-white/5 flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-50/30 dark:bg-slate-900/10 text-xs font-medium text-slate-500 dark:text-slate-400 mt-auto">
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
                    </div>

                    <footer class="footer-bottom pt-8 pb-2 text-center animate-card delay-3">
                        <p class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 tracking-wider">
                            © 2026 SignNet Project • Admin Management Panel.
                        </p>
                    </footer>
                </div>
            </main>
        </div>
    </div>

    <div id="admin-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-slate-950/60 backdrop-blur-md animate__animated">
        <div id="modal-card" class="glass w-full max-w-md rounded-[2.5rem] p-8 space-y-5 shadow-2xl relative border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-950/90 text-slate-900 dark:text-white animate__animated">
            
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2.5">
                    <div class="p-2.5 bg-indigo-500/10 rounded-xl text-indigo-600 dark:text-indigo-400 text-xs">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <h3 id="modal-title" class="text-base font-extrabold tracking-tight uppercase">Tambah Admin</h3>
                </div>
                <button onclick="closeModal()" class="text-slate-400 hover:text-rose-500 transition-colors p-1.5 hover:bg-slate-100 dark:hover:bg-white/5 rounded-lg cursor-pointer">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <div class="text-[10px] text-slate-600 dark:text-slate-400 leading-relaxed bg-indigo-500/5 p-3 rounded-xl border border-indigo-500/10 flex gap-2">
                <i class="fa-solid fa-shield-halved text-indigo-600 dark:text-indigo-400 mt-0.5 text-xs"></i>
                <span>Pastikan data yang dimasukkan valid. Autentikasi dan integritas login dipantau secara real-time dari sidik jari digital perangkat resmi Anda.</span>
            </div>

            <form id="admin-form" onsubmit="handleFormSubmit(event)" class="space-y-4 text-left" novalidate>
                <input type="hidden" id="admin-id">
                
                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase tracking-widest text-indigo-600 dark:text-indigo-400 block ml-1">Username</label>
                    <input type="text" id="username" autocomplete="off" oninput="validateField('username')"
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-white/10 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-medium text-sm text-slate-900 dark:text-white transition-all">
                    <span id="error-username" class="text-[10px] font-bold text-rose-500 hidden items-center gap-1 pl-1 pt-0.5 uppercase tracking-wide">
                        <i class="fa-solid fa-circle-exclamation text-[9px]"></i> Kolom username wajib diisi!
                    </span>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase tracking-widest text-indigo-600 dark:text-indigo-400 block ml-1">Alamat Email</label>
                    <input type="email" id="email" autocomplete="off" oninput="validateField('email')"
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-white/10 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-medium text-sm text-slate-900 dark:text-white transition-all">
                    <span id="error-email" class="text-[10px] font-bold text-rose-500 hidden items-center gap-1 pl-1 pt-0.5 uppercase tracking-wide">
                        <i class="fa-solid fa-circle-exclamation text-[9px]"></i> Email tidak valid atau kosong!
                    </span>
                </div>

                <div id="password-section" class="space-y-4">
                    <div id="create-password-wrapper" class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-indigo-600 dark:text-indigo-400 block ml-1">Password</label>
                        <div class="relative w-full">
                            <input type="password" id="password" autocomplete="new-password" oninput="validateField('password')"
                                class="w-full pl-4 pr-12 py-3 bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-white/10 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-medium text-sm text-slate-900 dark:text-white transition-all">
                            <button type="button" onclick="togglePasswordVisibility('password', this)" 
                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors cursor-pointer">
                                <i class="fa-solid fa-eye text-xs"></i>
                            </button>
                        </div>
                        <span id="error-password" class="text-[10px] font-bold text-rose-500 hidden items-center gap-1 pl-1 pt-0.5 uppercase tracking-wide">
                            <i class="fa-solid fa-circle-exclamation text-[9px]"></i> Password minimal berisi 6 karakter!
                        </span>
                    </div>

                    <div id="edit-password-group" class="hidden space-y-3">
                        <p id="password-help" class="text-[9px] text-slate-400 dark:text-slate-500 uppercase tracking-wider font-semibold pl-1">*Kosongkan kelompok password jika tidak ingin mengubah password.</p>
                        
                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase tracking-widest text-indigo-600 dark:text-indigo-400 block ml-1">Password Lama</label>
                            <div class="relative w-full">
                                <input type="password" id="old_password" oninput="validateField('old_password')"
                                    class="w-full pl-4 pr-12 py-3 bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-white/10 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-medium text-sm text-slate-900 dark:text-white transition-all">
                                <button type="button" onclick="togglePasswordVisibility('old_password', this)" 
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors cursor-pointer">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                </button>
                            </div>
                            <span id="error-old_password" class="text-[10px] font-bold text-rose-500 hidden items-center gap-1 pl-1 pt-0.5 uppercase tracking-wide">
                                <i class="fa-solid fa-circle-exclamation text-[9px]"></i> Password lama wajib diisi untuk verifikasi!
                            </span>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase tracking-widest text-indigo-600 dark:text-indigo-400 block ml-1">Password Baru</label>
                            <div class="relative w-full">
                                <input type="password" id="new_password" oninput="validateField('new_password')"
                                    class="w-full pl-4 pr-12 py-3 bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-white/10 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-medium text-sm text-slate-900 dark:text-white transition-all">
                                <button type="button" onclick="togglePasswordVisibility('new_password', this)" 
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors cursor-pointer">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                </button>
                            </div>
                            <span id="error-new_password" class="text-[10px] font-bold text-rose-500 hidden items-center gap-1 pl-1 pt-0.5 uppercase tracking-wide">
                                <i class="fa-solid fa-circle-exclamation text-[9px]"></i> Password baru minimal 6 karakter!
                            </span>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase tracking-widest text-indigo-600 dark:text-indigo-400 block ml-1">Konfirmasi Password Baru</label>
                            <div class="relative w-full">
                                <input type="password" id="confirm_password" oninput="validateField('confirm_password')"
                                    class="w-full pl-4 pr-12 py-3 bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-white/10 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-medium text-sm text-slate-900 dark:text-white transition-all">
                                <button type="button" onclick="togglePasswordVisibility('confirm_password', this)" 
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors cursor-pointer">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                </button>
                            </div>
                            <span id="error-confirm_password" class="text-[10px] font-bold text-rose-500 hidden items-center gap-1 pl-1 pt-0.5 uppercase tracking-wide">
                                <i class="fa-solid fa-circle-exclamation text-[9px]"></i> Konfirmasi password tidak cocok!
                            </span>
                        </div>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" id="submit-btn"
                        class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-500 hover:-translate-y-0.5 text-white font-bold text-[11px] tracking-widest rounded-xl transition-all active:scale-95 shadow-lg shadow-indigo-600/20 uppercase cursor-pointer">
                        SIMPAN DATA ADMINISTRATOR
                    </button>
                </div>
            </form>
        </div>
    </div>

    <x-kelolaadmin.scripts />
</x-app-layout>