<x-guest-layout>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --blue: #3b82f6;
            --blue-light: #60a5fa;

            --dark-bg: linear-gradient(180deg, 
                #050914 0%,
                #0a122c 22%,
                #151233 48%,
                #0b1636 72%,
                #111c44 90%,
                #03050a 100%
            );
            
            --dark-card: linear-gradient(145deg, #0d1730 0%, #132247 100%);
            --dark-card2: linear-gradient(145deg, #111e3d 0%, #182c59 100%);
            --border: rgba(59, 130, 246, 0.25);
            --text: #ffffff;
            --text-muted: #94a3b8;
            --footer-bg: transparent; 
            --hero-overlay: linear-gradient(135deg, rgba(6, 11, 25, 0.85) 0%, rgba(17, 28, 68, 0.7) 100%);
            --nav-bg: rgba(6, 11, 25, 0.75);

            --bg-card: var(--dark-card);
            --border-login: var(--border);
            --border-focus: var(--blue-light);
            --text-primary: var(--text);
            --text-label: var(--text);
            --bg-input: rgba(13, 23, 48, 0.5);
            --accent: var(--blue);
            --accent-hover: var(--blue-light);
        }

        [data-theme="light"] {
            --blue: #2563eb;
            --blue-light: #3b82f6;

            --dark-bg: linear-gradient(180deg, 
                #f0f7ff 0%,
                #e0e7ff 20%,
                #e0f2fe 45%,
                #f0fdf4 70%,
                #fff1f2 88%,
                #f8fafc 100%
            );
            
            --dark-card: linear-gradient(145deg, #ffffff 0%, #f0fdf4 100%); 
            --dark-card2: linear-gradient(145deg, #f8fafc 0%, #e0f2fe 100%); 
            --border: rgba(37, 99, 235, 0.2);
            --text: #0f172a;
            --text-muted: #475569;
            --footer-bg: transparent; 
            --hero-overlay: linear-gradient(135deg, rgba(239, 246, 255, 0.9) 0%, rgba(224, 231, 255, 0.6) 100%);
            --nav-bg: rgba(239, 246, 255, 0.75);

            --bg-card: #ffffff;
            --border-login: var(--border);
            --border-focus: var(--blue);
            --text-primary: var(--text);
            --text-label: var(--text);
            --bg-input: #f8fafc;
            --accent: var(--blue);
            --accent-hover: var(--blue-light);
        }

        html, body {
            min-height: 100vh;
            height: 100%;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--dark-bg);
            background-attachment: fixed;
            background-size: cover;
            color: var(--text);
        }

        body::before { 
            content: ''; 
            position: fixed; 
            inset: 0;
            background: radial-gradient(
                ellipse 60% 50% at 50% 40%,
                rgba(37,99,235,.12) 0%,
                transparent 70%
            ); 
            pointer-events: none; 
            z-index: 0; 
            animation: bgFloat 8s ease-in-out infinite alternate; 
        }

        @keyframes bgFloat {
            from { transform: translateY(0); }
            to { transform: translateY(-15px); }
        }

        /* NAVBAR DESKTOP BASE */
        nav { 
            position: fixed; top: 0; left: 0; right: 0; z-index: 1030; 
            display: flex; align-items: center; justify-content: space-between; 
            padding: 18px 60px; background: transparent;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); 
        }
        .nav-drawer-footer {
            display: none;
        }
        nav.scrolled { 
            background: var(--nav-bg); 
            backdrop-filter: blur(16px); 
            -webkit-backdrop-filter: blur(16px);
            box-shadow: 0 4px 30px rgba(0,0,0,0.05);
            padding: 12px 60px;
        }
        .logo { font-size: 1.5rem; font-weight: 800; letter-spacing: -0.5px; color: var(--text); text-decoration: none; z-index: 1050; transition: color 0.4s ease;}
        .logo span { color: var(--blue); }

        .nav-links { position: absolute; left: 50%; transform: translateX(-50%); display: flex; gap: 36px; list-style: none; }
        .nav-links li { display: flex; align-items: center; }
        .nav-links a { 
            display: inline-block;
            color: var(--text-muted); 
            text-decoration: none; 
            font-size: 0.9rem; 
            font-weight: 500; 
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); 
            position: relative;
            padding: 4px 0;
        }
        .nav-links a:hover { 
            color: var(--text); 
            transform: scale(1.1); 
        }
        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--blue);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        .nav-links a:hover::after { width: 100%; }

        .nav-right { width: 100px; display: flex; justify-content: flex-end; align-items: center; gap: 12px; z-index: 1050; }
        .theme-toggle-btn { background: var(--dark-card2); border: 1px solid var(--border); color: var(--text); width: 40px; height: 40px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; transition: all 0.4s ease; }
        .theme-toggle-btn:hover { border-color: var(--blue); color: var(--blue); transform: scale(1.1) rotate(15deg); }

        /* BUTTON MENUS & LAYERS UTILITY */
        .menu-toggle-btn {
            display: none;
            background: var(--dark-card2);
            border: 1px solid var(--border);
            color: var(--text);
            width: 40px;
            height: 40px;
            border-radius: 10px;
            cursor: pointer;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: all 0.4s ease;
        }
        .menu-toggle-btn:hover { border-color: var(--blue); color: var(--blue); }

        .nav-overlay {
            position: fixed; 
            inset: 0; 
            background: rgba(6, 11, 25, 0.5); 
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 1010; 
            display: none; 
            opacity: 0; 
            transition: opacity 0.3s ease;
        }
        .nav-overlay.active {
            display: block;
            opacity: 1;
        }

        .login-page-wrapper { 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 100px 24px 40px;
            position: relative; 
            z-index: 1; 
        }
        .login-container { display: flex; flex-direction: column; align-items: center; width: 100%; }

        .card { 
            background: var(--bg-card); 
            border: 1px solid var(--border-login); 
            border-radius: 18px; 
            padding: 38px 32px 30px;
            width: 100%; 
            max-width: 400px; 
            position: relative;
            box-shadow:
                0 0 0 1px rgba(255,255,255,.04) inset,
                0 24px 60px rgba(0,0,0,.45); 
            animation: cardIn .6s cubic-bezier(.16, 1, .3, 1) both; 
            transition: transform 0.4s cubic-bezier(.16, 1, .3, 1), box-shadow 0.4s cubic-bezier(.16, 1, .3, 1); 
            will-change: transform, opacity, box-shadow;
        }

        .card:hover {
            transform: translateY(-6px);
            box-shadow:
                0 0 0 1px rgba(255,255,255,.06) inset,
                0 32px 70px rgba(0,0,0,.55);
        }

        @keyframes cardIn {
            from { 
                opacity: 0; 
                transform: translateY(30px) scale(0.98); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0) scale(1); 
            }
        }

        .brand { text-align: center; margin-bottom: 26px; animation: brandFloat 3s ease-in-out infinite; }

        @keyframes brandFloat {
            0%,100% { transform: translateY(0); }
            50% { transform: translateY(-4px); }
        }

        .brand-logo { font-size: 26px; font-weight: 800; letter-spacing: -.5px; color: var(--text-primary); }
        .brand-logo span { color: var(--blue); }
        .brand-sub { margin-top: 6px; font-size: 12px; font-weight: 600; color: var(--text-muted); }
        
        .field { margin-bottom: 16px; }
        .field label { display: block; font-size: 12px; font-weight: 600; color: var(--text-label); margin-bottom: 7px; }
        .input-wrap { position: relative; display: flex; align-items: center; }
        .input-icon { position: absolute; left: 14px; color: var(--text-muted); display: flex; align-items: center; pointer-events: none; }
        
        .input-wrap input { 
            width: 100%; 
            background: var(--bg-input); 
            border: 1px solid var(--border-login); 
            border-radius: 10px;
            color: var(--text-primary); 
            font-family: inherit; 
            font-size: 13px; 
            padding: 12px 42px; 
            outline: none;
            transition: border-color 0.2s ease;
        }
        .input-wrap input::placeholder { color: var(--text-muted); }
        .input-wrap input:focus { border-color: var(--border-focus); }
        
        .toggle-pw { position: absolute; right: 14px; background: none; border: none; cursor: pointer; color: var(--text-muted); display: flex;
            align-items: center; padding: 0; transition: all .2s ease; }
        .toggle-pw:hover { color: var(--text-primary); transform: scale(1.08); }
        
        .forgot-wrapper { display: flex; justify-content: flex-end; margin-bottom: 20px; }
        .forgot-link { font-size: 12px; color: var(--text-muted); text-decoration: none; transition: .2s ease; }
        .forgot-link:hover { color: var(--accent-hover); }

        .btn-submit { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; background: var(--accent);
            color: #fff; font-family: inherit; font-size: 13px; font-weight: 700; border: none; border-radius: 10px; padding: 13px;
            cursor: pointer; transition: all .25s ease; box-shadow: 0 4px 20px rgba(37,99,235,.28); }
        .btn-submit svg { flex-shrink: 0; transition: transform .25s ease; }
        .btn-submit:hover { background: var(--accent-hover); box-shadow: 0 8px 28px rgba(37,99,235,.45); transform: translateY(-2px); }
        .btn-submit:hover svg { transform: translateX(6px); }
        .btn-submit:active { transform: translateY(0); }

        .back-link { display: flex; align-items: center; justify-content: center; gap: 6px; margin-top: 16px; font-size: 12px;
            color: var(--text-muted); text-decoration: none; transition: all .2s ease; }
        .back-link:hover { color: var(--text-primary); transform: translateX(-4px); }
        
        .footer-bottom { margin-top: 16px; text-align: center; }
        .footer-bottom p { font-size: 11px; color: var(--text-muted); margin: 0; }
        
        @media (max-width: 900px) {
            nav { padding: 16px 24px; }
            nav.scrolled { padding: 12px 24px; }
        }

        @media (max-width: 768px) {
            nav { z-index: 1060 !important; position: fixed !important; transition: background 0.4s ease, border-color 0.4s ease; }
            nav:has(#navLinks.active) .logo { opacity: 0 !important; visibility: hidden !important; }
            .logo { transition: opacity 0.2s ease, visibility 0.2s ease, color 0.4s ease !important; }
            
            .menu-toggle-btn { display: flex !important; }
            .nav-right { width: auto !important; }
            
            .nav-links { 
                position: fixed !important; top: 0; right: -100%; 
                width: 210px; 
                max-width: 65vw; 
                height: 100vh; 
                background: var(--nav-bg) !important; 
                backdrop-filter: blur(16px) !important; 
                -webkit-backdrop-filter: blur(16px) !important;
                border-left: 1px solid var(--border); 
                flex-direction: column !important; justify-content: flex-start !important; align-items: flex-start !important; 
                padding: 100px 20px 40px !important; gap: 8px !important; 
                box-shadow: -15px 0 40px rgba(0,0,0,0.15); 
                z-index: 1040 !important; list-style: none !important; left: auto !important; transform: none !important; 
                transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1), background 0.4s ease, border-color 0.4s ease !important; 
            }
            .nav-links.active { right: 0 !important; }
            .nav-links li { width: 100% !important; display: block !important; }
            .nav-links a { 
                font-size: 0.95rem !important; font-weight: 600 !important; color: var(--text-muted); 
                width: 100% !important; display: block !important; padding: 12px 16px !important; 
                border-radius: 12px; transition: all 0.2s ease, color 0.4s ease !important; text-align: left !important; box-sizing: border-box !important; 
            }
            .nav-links a:hover { 
                color: var(--blue) !important; 
                background: var(--dark-card2) !important; 
                transform: translateX(6px) !important; 
            }
            .nav-links a::after { display: none !important; } 
        }

        /* PERBAIKAN MEDIA QUERY MOBILE */
        @media (max-width: 480px) {
            .login-page-wrapper { padding: 90px 16px 30px; }
            .card { max-width: 100%; padding: 32px 20px 24px; }
            .brand-logo { font-size: 24px; }
        }
    </style>

    <div id="navOverlay" class="nav-overlay"></div>

    <nav id="navbar">
        <a href="{{ route('home') }}#beranda" class="logo">Sign<span>Net</span></a>
        <ul class="nav-links" id="navLinks">
            <li><a href="{{ route('home') }}#beranda" class="nav-item">Beranda</a></li>
            <li><a href="{{ route('home') }}#tentang" class="nav-item">Tentang</a></li>
            <li><a href="{{ route('home') }}#fitur" class="nav-item">Fitur</a></li>
            <li><a href="{{ route('home') }}#daftar-isyarat" class="nav-item">Daftar Isyarat</a></li>
            <li><a href="{{ route('recognition') }}" class="nav-item">Deteksi</a></li>
            <li><a href="{{ route('login') }}" class="nav-item">Login</a></li>
        </ul>
        <div class="nav-right">
            <button id="themeToggle" class="theme-toggle-btn" title="Ubah Tema">
                <i class="fa-solid fa-moon"></i>
            </button>
            <button id="mobileNavToggle" class="menu-toggle-btn" aria-label="Toggle Navigation">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div> 
    </nav>

    <div class="login-page-wrapper">
        <div class="login-container">
            <div class="card">
                <div class="brand">
                    <div class="brand-logo">Sign<span>Net</span></div>
                    <div class="brand-sub">Portal Administrator</div>
                </div>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="field">
                        <label for="username">Username</label>
                        <div class="input-wrap">
                            <span class="input-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="12" cy="8" r="4"/>
                                    <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                                </svg>
                            </span>
                            <input id="username" type="text" name="username" value="{{ old('username') }}" placeholder="Masukkan username" required autofocus />
                        </div>
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <span class="input-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <rect x="3" y="11" width="18" height="11" rx="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                            </span>

                            <input id="password" type="password" name="password" placeholder="•••••••••" required />
                            <button type="button" class="toggle-pw" onclick="togglePassword()">
                                <svg id="icon-eye" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg id="icon-eye-off" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="display:none">
                                    <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20C5 20 1 12 1 12a21.68 21.68 0 0 1 5.06-5.94"/>
                                    <path d="M9.9 4.24A10.94 10.94 0 0 1 12 4c7 0 11 8 11 8a21.68 21.68 0 0 1-2.16 3.19"/>
                                    <path d="M14.12 14.12A3 3 0 0 1 9.88 9.88"/>
                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    @if (Route::has('password.request'))
                        <div class="forgot-wrapper">
                            <a class="forgot-link" href="{{ route('password.request') }}">
                                Forgot password?
                            </a>
                        </div>
                    @endif

                    <button type="submit" class="btn-submit">
                        Masuk Dashboard
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                            <polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </button>
                </form>

                <a href="{{ url('/') }}" class="back-link">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                    Kembali ke Beranda
                </a>
            </div>

            <div class="footer-bottom">
                <p>© 2026 SignNet Project &bull; Bahasa Isyarat BISINDO.</p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const eyeOn = document.getElementById('icon-eye');
            const eyeOff = document.getElementById('icon-eye-off');
            const isHidden = input.type === 'password';

            input.type = isHidden ? 'text' : 'password';
            eyeOn.style.display = isHidden ? 'none' : '';
            eyeOff.style.display = isHidden ? '' : 'none';
        }

        const themeToggleBtn = document.getElementById('themeToggle');
        const themeIcon = themeToggleBtn.querySelector('i');
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
        updateToggleIcon(savedTheme);

        themeToggleBtn.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateToggleIcon(newTheme);
        });

        function updateToggleIcon(theme) {
            if (theme === 'light') {
                themeIcon.className = 'fa-solid fa-sun';
                themeIcon.style.color = '#f59e0b';
            } else {
                themeIcon.className = 'fa-solid fa-moon';
                themeIcon.style.color = 'var(--text)';
            }
        }

        function initResponsiveNavbar() {
            const navToggle = document.getElementById('mobileNavToggle');
            const navLinks = document.getElementById('navLinks');
            const navOverlay = document.getElementById('navOverlay');
            const navItems = document.querySelectorAll('.nav-item');

            if (!navLinks || !navOverlay || !navToggle) return;

            function openMobileNav() {
                navLinks.classList.add('active');
                navOverlay.style.display = 'block';
                setTimeout(() => navOverlay.style.opacity = '1', 10);
            }

            function closeMobileNav() {
                navLinks.classList.remove('active');
                navOverlay.style.opacity = '0';
                setTimeout(() => navOverlay.style.display = 'none', 200);
            }

            navToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = navLinks.classList.contains('active');
                if (isOpen) closeMobileNav(); else openMobileNav();
            });

            navOverlay.addEventListener('click', closeMobileNav);
            navItems.forEach(item => item.addEventListener('click', closeMobileNav));

            window.addEventListener('resize', () => {
                if (window.innerWidth >= 769) {
                    closeMobileNav();
                }
            });
        }

        function initNavbarScroll() {
            const navbar = document.getElementById('navbar');
            if (!navbar) return;
            window.addEventListener('scroll', () => {
                if (window.scrollY > 50) navbar.classList.add('scrolled');
                else navbar.classList.remove('scrolled');
            });
        }

        // Execution on DOM Loaded
        document.addEventListener('DOMContentLoaded', () => {
            initResponsiveNavbar();
            initNavbarScroll();
        });
    </script>
</x-guest-layout>