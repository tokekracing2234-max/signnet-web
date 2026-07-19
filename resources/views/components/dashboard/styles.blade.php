<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = { darkMode: 'class' }
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght=400;600;700;800&display=swap" rel="stylesheet">

<style>
    * { 
        touch-action: manipulation; 
        box-sizing: border-box; 
    }
    
    .dashboard-wrapper, 
    .main-content,
    .text-custom-title,
    .text-custom-muted,
    h1, h2, h3, h4, p, span, i, label {
        transition: background-color 0.1s ease-out, color 0.1s ease-out;
        will-change: background-color, color;
    }
    
    :root, html, html[data-theme="dark"] {
        --bg-main: #020617; 
        --bg-radial: radial-gradient(circle at 50% -20%, #1e1b4b 0%, #020617 80%);
        --glass-border: rgba(255, 255, 255, 0.08);
        --select-bg: #1e293b; 
        --select-border: rgba(255, 255, 255, 0.1); 
        --select-text: #818cf8;
        --text-muted: #94a3b8; 
        --text-title: #f8fafc; 
        --card-hover-shadow: rgba(99, 102, 241, 0.15);
        --hover-lift: translateY(-4px); 
        --hover-scale: scale(1.01);

        --stat-bg-blue: rgba(30, 27, 75, 0.45);
        --stat-border-blue: rgba(99, 102, 241, 0.2);
        --stat-bg-green: rgba(6, 78, 59, 0.25);
        --stat-border-green: rgba(16, 185, 129, 0.15);
        --stat-bg-rose: rgba(159, 18, 57, 0.25);
        --stat-border-rose: rgba(244, 63, 94, 0.2);
        --stat-bg-amber: rgba(120, 53, 4, 0.2);
        --stat-border-amber: rgba(245, 158, 11, 0.15);
    }
    
    html[data-theme="light"] {
        --bg-main: #f8fafc; 
        --bg-radial: radial-gradient(circle at 50% -20%, #e0e7ff 0%, #f8fafc 100%);
        --glass-border: rgba(99, 102, 241, 0.16);
        --select-bg: #ffffff; 
        --select-border: rgba(79, 70, 229, 0.3); 
        --select-text: #4f46e5;
        --text-muted: #475569; 
        --text-title: #0f172a; 
        --card-hover-shadow: rgba(79, 70, 229, 0.12);
        --hover-lift: translateY(-4px); 
        --hover-scale: scale(1.01); 

        --stat-bg-blue: #f0f4ff;
        --stat-border-blue: rgba(165, 180, 252, 0.4);
        --stat-bg-green: #f0fdf4;
        --stat-border-green: rgba(110, 231, 183, 0.4);
        --stat-bg-rose: #fff1f2;
        --stat-border-rose: rgba(251, 113, 133, 0.4);
        --stat-bg-amber: #fef3c7;
        --stat-border-amber: rgba(252, 211, 77, 0.4);
    }
    
    .dashboard-wrapper { 
        font-family: 'Plus Jakarta Sans', sans-serif; 
        background: var(--bg-main); 
        background-image: var(--bg-radial); 
        height: 100vh; 
        width: 100%; 
        overflow: hidden; 
        color: var(--text-base);
    }
    
    .glass-card { 
        border: 1px solid var(--glass-border); 
        backdrop-filter: blur(8px); 
        -webkit-backdrop-filter: blur(8px); 
        box-shadow: 0 4px 10px -1px rgba(0, 0, 0, 0.04); 
        transition: background-color 0.1s ease-out, border-color 0.1s ease-out; 
        will-change: background-color, border-color; 
        transform: translateZ(0);
    }
    
    .glass-card:not(.modal-spec-card) {
        transition: transform 0.15s ease-out, box-shadow 0.15s ease-out;
    }
    
    .glass-card:not(.modal-spec-card):hover { 
        transform: var(--hover-lift) var(--hover-scale); 
        box-shadow: 0 12px 20px -5px var(--card-hover-shadow); 
    }
    
    html[data-theme="light"] .glass-card:hover:not(.modal-spec-card) { 
        border-color: rgba(79, 70, 229, 0.3); 
    }
    
    html[data-theme="light"] .chart-card-spec { 
        box-shadow: 0 6px 15px -5px rgba(99, 102, 241, 0.06); 
        border: 1px solid rgba(99, 102, 241, 0.15); 
    }
    
    .c-bg-1 { background-color: var(--stat-bg-blue) !important; border-color: var(--stat-border-blue) !important; } 
    .c-bg-2 { background-color: var(--stat-bg-green) !important; border-color: var(--stat-border-green) !important; } 
    .c-bg-3 { background-color: var(--stat-bg-rose) !important; border-color: var(--stat-border-rose) !important; }
    .c-bg-4 { background-color: var(--stat-bg-amber) !important; border-color: var(--stat-border-amber) !important; } 
    .c-bg-chart { background: var(--bg-card-chart); }
    
    .modal-spec-card:hover { 
        transform: none !important; 
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important; 
        border-color: var(--glass-border) !important; 
    }

    .text-custom-title { color: var(--text-title); } 
    .text-custom-muted { color: var(--text-muted); }
    
    .logo-gradient { 
        background: linear-gradient(135deg, #4f46e5, #06b6d4); 
        -webkit-background-clip: text; 
        -webkit-text-fill-color: transparent; 
        background-clip: text; 
    }
    
    .app-layout-grid { 
        display: grid; 
        grid-template-columns: 5rem 1fr; 
        width: 100%; 
        height: 100vh; 
        transition: grid-template-columns 0.15s ease-out; 
        will-change: grid-template-columns; 
    }
    @media (min-width: 768px) { .app-layout-grid { grid-template-columns: 6rem 1fr; } }
    .app-layout-grid:has(#sidebar:hover) { grid-template-columns: 16rem 1fr; }
    
    .main-content { 
        grid-column: 2; 
        height: 100vh;
        overflow-y: auto; 
        min-width: 0; 
        contain: layout size style; 
    }

    @keyframes simpleFade { 
        from { opacity: 0; transform: translateY(6px); } 
        to { opacity: 1; transform: translateY(0); } 
    }
    
    .animate-card { 
        animation: simpleFade 0.3s ease-out forwards; 
        opacity: 0; 
        will-change: transform, opacity; 
    }
    .delay-1 { animation-delay: 0.02s; } 
    .delay-2 { animation-delay: 0.04s; } 
    .delay-3 { animation-delay: 0.06s; }
    .delay-4 { animation-delay: 0.08s; } 
    .delay-5 { animation-delay: 0.1s; } 
    .delay-6 { animation-delay: 0.12s; }
    
    .custom-scroll::-webkit-scrollbar { width: 4px; }
    .custom-scroll::-webkit-scrollbar-thumb { background: rgba(99, 102, 241, 0.4); border-radius: 4px; }
    
    .dynamic-select { 
        background-color: var(--select-bg) !important; 
        border-color: var(--select-border) !important; 
        color: var(--select-text) !important; 
        transition: border-color 0.1s ease-out, background-color 0.1s ease-out, color 0.1s ease-out; 
    }
    
    .chart-container { 
        content-visibility: auto; 
        contain-intrinsic-size: 380px; 
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