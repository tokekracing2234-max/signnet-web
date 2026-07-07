<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = { darkMode: 'class' }
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght=400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    * { 
        touch-action: manipulation; 
        box-sizing: border-box; 
        transition-property: background-color, border-color, text-color, fill, stroke;
        transition-duration: 200ms;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    :root, html, html[data-theme="dark"] {
        --bg-main: #020617;
        --bg-radial: radial-gradient(circle at 50% -20%, #1e1b4b 0%, #020617 80%);
        --glass-bg: rgba(15, 23, 42, 0.6);
        --glass-border: rgba(255, 255, 255, 0.08);
        --select-bg: #1e293b;
        --text-base: #f8fafc;

        --stat-bg-blue: rgba(30, 27, 75, 0.45);
        --stat-border-blue: rgba(99, 102, 241, 0.2);
        --stat-bg-green: rgba(6, 78, 59, 0.25);
        --stat-border-green: rgba(16, 185, 129, 0.15);
        --stat-bg-amber: rgba(120, 53, 4, 0.2);
        --stat-border-amber: rgba(245, 158, 11, 0.15);
    }

    html[data-theme="light"] {
        --bg-main: #f8fafc;
        --bg-radial: radial-gradient(circle at 50% -20%, #e0e7ff 0%, #f8fafc 80%);
        --glass-bg: rgba(255, 255, 255, 0.7);
        --glass-border: rgba(99, 102, 241, 0.15);
        --select-bg: #ffffff;
        --text-base: #0f172a;

        --stat-bg-blue: #f0f4ff;
        --stat-border-blue: rgba(165, 180, 252, 0.4);
        --stat-bg-green: #f0fdf4;
        --stat-border-green: rgba(110, 231, 183, 0.4);
        --stat-bg-amber: #fef3c7;
        --stat-border-amber: rgba(252, 211, 77, 0.4);
    }

    .card-stat-blue { background-color: var(--stat-bg-blue) !important; border-color: var(--stat-border-blue) !important; }
    .card-stat-green { background-color: var(--stat-bg-green) !important; border-color: var(--stat-border-green) !important; }
    .card-stat-amber { background-color: var(--stat-bg-amber) !important; border-color: var(--stat-border-amber) !important; }
    .dashboard-wrapper { 
        font-family: 'Plus Jakarta Sans', sans-serif; 
        background: var(--bg-main); 
        background-image: var(--bg-radial);
        overflow-y: auto;
        min-height: 100vh;
        width: 100%;
        position: relative;
        z-index: 50;
    }

    .glass { 
        background: var(--glass-bg); 
        border: 1px solid var(--glass-border); 
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        will-change: background-color, border-color;
    }

    .logo-gradient {
        background: linear-gradient(135deg, #6366f1, #38bdf8);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .app-layout-grid {
        display: grid;
        grid-template-columns: 5rem 1fr;
        width: 100%; height: 100vh;
        transition: grid-template-columns 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        will-change: grid-template-columns;
    }

    @media (min-width: 768px) { .app-layout-grid { grid-template-columns: 6rem 1fr; } }
    .app-layout-grid:has(#sidebar:hover) { grid-template-columns: 16rem 1fr; }
    .main-content { grid-column: 2; overflow-y: auto; min-width: 0; }

    @keyframes simpleFade { 
        from { opacity: 0; transform: translate3d(0, 8px, 0); } 
        to { opacity: 1; transform: translate3d(0, 0, 0); } 
    }
    
    .animate-card { 
        animation: simpleFade 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards; 
        opacity: 0; 
        will-change: transform, opacity;
    }
    .delay-1 { animation-delay: 0.03s; }
    .delay-2 { animation-delay: 0.08s; }
    .delay-3 { animation-delay: 0.14s; }

    .custom-scroll::-webkit-scrollbar { width: 5px; }
    .custom-scroll::-webkit-scrollbar-thumb { background: rgba(99, 102, 241, 0.3); border-radius: 4px; }
    
    .table-overflow {
        width: 100%;
        overflow-x: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .table-overflow::-webkit-scrollbar { display: none; }

    .swal-custom-icon {
        width: 5rem;
        height: 5rem;
        border-width: 4px;
        border-style: solid;
        border-radius: 50%;
        margin: 1.25rem auto;
    }

    .pagination-arrow-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 2.25rem;
        padding-left: 0.75rem;
        padding-right: 0.75rem;
        border-radius: 0.75rem;
        background-color: #ffffff;
        color: #475569;
        border: 1px solid #e2e8f0;
        font-weight: 600;
        transition: all 0.15s ease-out;
        cursor: pointer;
    }
    html[data-theme="dark"] .pagination-arrow-btn {
        background-color: #0f172a;
        color: #cbd5e1;
        border-color: rgba(255, 255, 255, 0.1);
    }
    .pagination-arrow-btn:hover {
        border-color: #4f46e5;
        color: #4f46e5;
    }
    html[data-theme="dark"] .pagination-arrow-btn:hover {
        border-color: #818cf8;
        color: #818cf8;
    }
    .pagination-arrow-btn:disabled {
        opacity: 0.3;
        pointer-events: none;
    }
</style>

<script>
    (function initTheme() {
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
        if (savedTheme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    })();
</script>