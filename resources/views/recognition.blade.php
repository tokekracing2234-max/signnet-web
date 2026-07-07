<!DOCTYPE html>
<html lang="id" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deteksi Isyarat - SignNet BISINDO</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/recognition.css') }}">

    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/hands/hands.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pako/2.1.0/pako.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/onnxruntime-web/dist/ort.min.js"></script>
</head>

<body>
    
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

    <div class="page">
        <div class="hero fade-item delay-1">
            <h1>Real-time <span>Recognition</span></h1>
            <p>
                Gunakan kedua tangan Anda untuk melakukan gestur bahasa isyarat BISINDO.
            </p>
        </div>

        <div class="recognition-grid">
            <div class="camera-card fade-item delay-2">
                <div class="camera-wrapper">
                    <video id="input_video" autoplay muted playsinline webkit-playsinline style="position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none;"></video>
                    <canvas id="output_canvas"></canvas>
                    <div id="status" class="status-indicator"><i class="fas fa-circle-notch fa-spin"></i>Menyiapkan Kamera AI...</div>
                    <div class="hand-legend">
                        <div class="legend-item">
                            <div class="legend-color" style="background:#10b981;"></div>Tangan Kanan
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background:#3b82f6;"></div>Tangan Kiri
                        </div>
                    </div>
                </div>
            </div>

            <div class="side-panel fade-item delay-3">
                <div>
                    <div class="mode-selector-card" style="background: var(--card2); backdrop-filter: blur(8px); padding: 12px 16px; border-radius: 12px; margin-bottom: 16px; border: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 12px; transition: background 0.4s ease, border-color 0.4s ease;">
                        <span style="font-weight: 700; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap; transition: color 0.4s ease;">
                            <i class="fas fa-sliders-h" style="color: var(--blue); margin-right: 4px; font-size: 0.8rem;"></i> Mode
                        </span>
                        
                        <div style="display: flex; background: var(--mode-bg, rgba(0, 0, 0, 0.1)); padding: 3px; border-radius: 8px; border: 1px solid var(--border); flex-grow: 1; max-width: 220px; transition: background 0.4s ease, border-color 0.4s ease;">
                            <button type="button" id="btnModeHuruf" onclick="triggerChangeMode('huruf')" 
                                style="flex: 1; padding: 6px 10px; border: none; border-radius: 6px; font-weight: 700; font-size: 0.8rem; letter-spacing: 0.02em; cursor: pointer; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); background: var(--blue); color: #fff; box-shadow: 0 2px 8px rgba(59, 130, 246, 0.35);">
                                HURUF
                            </button>
                            <button type="button" id="btnModeAngka" onclick="triggerChangeMode('angka')" 
                                style="flex: 1; padding: 6px 10px; border: none; border-radius: 6px; font-weight: 700; font-size: 0.8rem; letter-spacing: 0.02em; cursor: pointer; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); background: transparent; color: var(--text-muted);">
                                ANGKA
                            </button>
                        </div>
                    </div>

                    <div class="prediction-top">
                        <div class="prediction-label"><i class="fas fa-language"></i>HASIL PREDIKSI</div>
                        <div id="prediction-result" class="prediction-result"> - </div>
                    </div>

                    <div class="accuracy">
                        <div class="accuracy-head">
                            <span><i class="fas fa-bullseye"></i> Tingkat Keyakinan</span>
                            <span id="accuracy-value">0%</span>
                        </div>
                        <div class="accuracy-container" style="width: 100%; background: var(--mode-bg, rgba(0,0,0,0.1)); border: 1px solid var(--border); border-radius: 99px; height: 8px; overflow: hidden; margin-top: 8px;">
                            <div id="accuracy-bar" class="accuracy-fill" style="height: 100%; width: 0%; background: linear-gradient(90deg, var(--blue), var(--blue2)); transition: width 0.2s ease;"></div>
                        </div>
                    </div>

                    <div class="info-box">
                        <div class="info-content">
                            <i class="fas fa-lightbulb"></i>
                            <p>
                                Pastikan kedua tangan Anda berada dalam posisi yang terlihat jelas dengan pencahayaan yang optimal.
                            </p>
                        </div>
                    </div>
                </div>

                <button class="btn-home" onclick="window.location.href='{{ route('home') }}'">
                    <i class="fas fa-arrow-left"></i>&nbsp; Kembali ke Beranda
                </button>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="#beranda" class="logo">Sign<span>Net</span></a>
                <p>Mengubah gerakan tangan menjadi teks digital secara real-time. Membantu dunia menjadi tempat yang lebih inklusif.</p>
                <div class="socials">
                    <a href="#" class="social-btn github"><i class="fa-brands fa-github"></i></a>
                    <a href="#" class="social-btn linkedin"><i class="fa-brands fa-linkedin-in"></i></a>
                    <a href="#" class="social-btn instagram"><i class="fa-brands fa-instagram"></i></a>
                </div>
            </div>

            <div class="footer-col">
                <h4>Tautan Cepat</h4>
                <div class="underline-blue"></div>
                <ul>
                    <li><a href="#beranda"><i class="fa-solid fa-house-chimney" style="color: var(--blue);"></i> Beranda</a></li>
                    <li><a href="#tentang"><i class="fa-solid fa-circle-info" style="color: var(--blue);"></i> Tentang Kami</a></li>
                    <li><a href="#fitur"><i class="fa-solid fa-star" style="color: var(--blue);"></i> Fitur</a></li>
                    <li><a href="#daftar-isyarat"><i class="fa-solid fa-hand" style="color: var(--blue);"></i> Isyarat</a></li>
                    <li><a href="{{ route('recognition') }}"><i class="fa-solid fa-video" style="color: var(--blue);"></i> Deteksi</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Teknologi</h4>
                <div class="underline-blue"></div>
                <ul>
                    <li><a href="https://laravel.com/" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-laravel" style="color: #FF2D20;"></i> Laravel Framework</a></li>
                    <li><a href="https://flask.palletsprojects.com/" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-python" style="color: #3776AB;"></i> Python Flask</a></li>
                    <li><a href="https://ai.google.dev/edge/mediapipe/solutions/guide" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-hand-sparkles" style="color: #00cba9;"></i> Google MediaPipe</a></li>
                    <li><a href="https://scikit-learn.org/" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-tree" style="color: #F7931E;"></i> Random Forest</a></li>
                    <li><a href="https://onnx.ai/" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-circle-nodes" style="color: #005EA6;"></i> ONNX Runtime</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Hubungi Kami</h4>
                <div class="underline-blue"></div>
                <p class="contact-info"><i class="fa-solid fa-envelope" style="color: #ea4335; font-size: 1rem;"></i> support@signnet.id</p>
                <p class="contact-info"><i class="fa-solid fa-location-dot" style="color: #34a853; font-size: 1rem;"></i> Bandung, Jawa Barat</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2026 SignNet Project &bull; Bahasa Isyarat BISINDO.</p>
        </div>
    </footer>

    <script src="{{ asset('js/recognition.js') }}"></script>

    <script>
        // Set default global detection mode
        window.currentDetectionMode = window.currentDetectionMode || 'huruf';

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
            changeMode(window.currentDetectionMode);
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

        function triggerChangeMode(mode) {
            if (typeof window.changeMode === 'function') {
                window.changeMode(mode);
            } else {
                console.warn("⚠️ window.changeMode belum siap di recognition.js");
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

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                initResponsiveNavbar();
                initNavbarScroll();
                if (typeof window.updateButtonVisuals === 'function') {
                    window.updateButtonVisuals(window.currentDetectionMode);
                }
            });
        } else {
            initResponsiveNavbar();
            initNavbarScroll();
            if (typeof window.updateButtonVisuals === 'function') {
                window.updateButtonVisuals(window.currentDetectionMode);
            }
        }
    </script>
</body>

</html>