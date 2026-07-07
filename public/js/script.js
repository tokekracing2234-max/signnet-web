// Theme Management
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

// Scroll Effects
window.addEventListener('scroll', () => {
    const navbar = document.getElementById('navbar');
    const scrollBtn = document.getElementById('scrollTop');
    
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
    
    scrollBtn.classList.toggle('visible', window.scrollY > 400);
});

const observerOptions = {
    root: null,
    rootMargin: '0px',
    threshold: 0.1
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('show');
        } else {
            const rect = entry.target.getBoundingClientRect();
            if (rect.top > 0 || rect.bottom < window.innerHeight) {
                 entry.target.classList.remove('show');
            }
        }
    });
}, observerOptions);

document.querySelectorAll('.animate-item').forEach((el) => {
    observer.observe(el);
});

const alphabetGrid = document.getElementById('alphabetGrid');
if (alphabetGrid) {
    for (let i = 65; i <= 90; i++) {
        const letter = String.fromCharCode(i);
        const badge = document.createElement('div');
        badge.className = 'sign-badge';
        badge.textContent = letter;
        badge.onclick = () => openModal(letter);
        alphabetGrid.appendChild(badge);
    }
}

const numberGrid = document.getElementById('numberGrid');
if (numberGrid) {
    for (let i = 0; i <= 9; i++) {
        const badge = document.createElement('div');
        badge.className = 'sign-badge';
        badge.textContent = i;
        badge.onclick = () => openModal(i.toString());
        numberGrid.appendChild(badge);
    }
}

// --- MODAL ---
function openModal(sign) {
    const modal = document.getElementById('signModal');
    const modalImg = document.getElementById('modalImage');
    
    document.getElementById('modalTitle').textContent = 'Isyarat: ' + sign;

    modalImg.src = `/assets/img/signs/${sign}.PNG`;

    modalImg.onerror = function() {
        this.src = "https://via.placeholder.com/300?text=Gambar+Isyarat+" + sign;
    };

    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const modal = document.getElementById('signModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

const signModal = document.getElementById('signModal');
if (signModal) {
    signModal.addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
}