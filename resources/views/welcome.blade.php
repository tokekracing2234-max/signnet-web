<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SignNet - Ubah Gestur Menjadi Huruf dan Angka</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

    <div id="navOverlay" class="nav-overlay"></div>

    <nav id="navbar">
        <a href="#beranda" class="logo">Sign<span>Net</span></a>
        <ul class="nav-links" id="navLinks">
            <li><a href="#beranda" class="nav-item">Beranda</a></li>
            <li><a href="#tentang" class="nav-item">Tentang</a></li>
            <li><a href="#fitur" class="nav-item">Fitur</a></li>
            <li><a href="#daftar-isyarat" class="nav-item">Daftar Isyarat</a></li>
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

    <section id="beranda">
        <div class="hero-split-grid">
            <div class="hero-text-side">
                <p class="hero-label animate-item">Future of Communication</p>
                <h1 class="hero-title animate-item">
                    Ubah <span class="highlight">Gestur</span> Menjadi<br>
                    <span class="highlight">Huruf</span> dan <span class="highlight">Angka</span>
                </h1>
                <p class="hero-desc animate-item">
                    Membangun inklusivitas melalui teknologi deteksi bahasa isyarat 
                    <strong>BISINDO</strong> yang akurat dan real-time.
                </p>
                <div class="animate-item">
                    <a href="{{ route('recognition') }}" class="btn-primary">
                        Mulai Deteksi Sekarang <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="hero-img-side animate-item">
                <div class="hero-img-container">
                    <img src="{{ asset('assets/img/bhs1.png') }}" alt="SignNet Illustration">
                </div>
            </div>
        </div>
    </section>

    <section id="tentang">
        <div class="about-grid">
            <div class="about-img-wrap animate-item">
                <img src="{{ asset('assets/img/bhs3.jpg') }}" alt="Bahasa Isyarat">
            </div>
            <div class="about-text">
                <h2 class="animate-item">Filosofi Sign<span>Net</span>.</h2>
                <p class="animate-item">
                    Nama <strong>SignNet</strong> lahir dari visi besar untuk menghubungkan dua dunia yang berbeda.
                    <strong>"Sign"</strong> merepresentasikan bahasa isyarat sebagai identitas, sementara 
                    <strong>"Net"</strong> melambangkan jaringan AI sebagai jembatan penghubung.
                </p>
                <p class="animate-item">
                    Kami percaya bahwa batasan bahasa tidak boleh menjadi penghalang sosial. Dengan menggabungkan
                    gerakan tangan manusia dan kecerdasan mesin, kami hadir untuk masa depan inklusif.
                </p>
            </div>
        </div>
    </section>

    <section id="fitur">
        <div class="section-header animate-item">
            <h2>Fitur Unggulan</h2>
            <p>Dikembangkan dengan standar teknologi modern.</p>
        </div>
        <div class="features-grid">
            <div class="feature-card animate-item">
                <div class="feature-icon" style="color: #ef4444;"><i class="fa-solid fa-microchip"></i></div>
                <h3>Random Forest Core</h3>
                <p>Menggunakan algoritma pembelajaran mesin untuk klasifikasi isyarat tangan yang presisi.</p>
            </div>
            <div class="feature-card animate-item">
                <div class="feature-icon" style="color: #10b981;"><i class="fa-solid fa-hand-sparkles"></i></div>
                <h3>21 Points Tracking</h3>
                <p>MediaPipe melacak setiap sendi jari Anda untuk memastikan huruf terbaca benar secara detail.</p>
            </div>
            <div class="feature-card animate-item">
                <div class="feature-icon" style="color: #f59e0b;"><i class="fa-solid fa-bolt"></i></div>
                <h3>Low Latency</h3>
                <p>Pengoptimalan kode memungkinkan deteksi berjalan lancar tanpa beban berat di sistem komputer Anda.</p>
            </div>
        </div>
    </section>

    <section id="daftar-isyarat">
        <div class="isyarat-wrapper">
            <div class="section-header animate-item">
                <h2>Daftar Isyarat Didukung</h2>
            </div>
            <div class="animate-item">
                <p class="sign-section-label">Alfabet A-Z</p>
            </div>
            <div class="sign-grid animate-item" id="alphabetGrid"></div>
            
            <div class="animate-item">
                <p class="sign-section-label">Angka 0-9</p>
            </div>
            <div class="sign-grid animate-item" id="numberGrid"></div>
        </div>
    </section>

    <section id="deteksi">
        <h2 class="animate-item" style="font-size:2.5rem; font-weight:900; margin-bottom:12px; position: relative; z-index: 2;">Siap Berkomunikasi?</h2>
        <p class="animate-item" style="color:var(--text-muted); margin-bottom:40px; max-width: 600px; margin-left: auto; margin-right: auto; position: relative; z-index: 2;">
            Arahkan kamera ke tangan Anda dan sistem akan mengenali isyarat secara real-time dengan akurasi tinggi.
        </p>
        <a href="{{ route('recognition') }}" class="btn-primary animate-item" style="position: relative; z-index: 2;">
            Mulai Deteksi Sekarang <i class="fa-solid fa-arrow-right"></i>
        </a>
    </section>

    <div class="modal-overlay" id="signModal">
        <div class="modal-box">
            <button class="modal-close" onclick="closeModal()"><i class="fa-solid fa-xmark"></i></button>
            <div class="modal-title" id="modalTitle">Isyarat: A</div>

            <div class="modal-img-container">
                <img id="modalImage" src="" alt="Gesture Image">
            </div>

            <a href="{{ route('recognition') }}" class="btn-green">
                Coba deteksi sekarang <i class="fa-solid fa-arrow-right"></i>
            </a>
            <p class="modal-ref">Referensi: https://meenta.net/belajar-bahasa-isyarat-dasar/</p>
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

    <button id="scrollTop" onclick="window.scrollTo({top:0,behavior:'smooth'})">
        <i class="fa-solid fa-arrow-up"></i>
    </button>

    <script src="{{ asset('js/script.js') }}"></script>

    <script>
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

        function initScrollAnimations() {
            const animatedItems = document.querySelectorAll('.animate-item');
            
            const observerOptions = {
                root: null,
                rootMargin: '0px 0px -50px 0px',
                threshold: 0.1
            };

            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('show');
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            animatedItems.forEach(item => {
                observer.observe(item);
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                initResponsiveNavbar();
                initNavbarScroll();
                initScrollAnimations();
            });
        } else {
            initResponsiveNavbar();
            initNavbarScroll();
            initScrollAnimations();
        }
    </script>
</body>
</html>