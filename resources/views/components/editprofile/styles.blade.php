<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = { darkMode: 'class' }
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght=400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    * { 
        touch-action: manipulation; 
        box-sizing: border-box; 
    }

    .dashboard-wrapper, 
    .main-content,
    .glass-form-container h2,
    .glass-form-container p,
    .glass-form-container .text-sm,
    .glass-form-container label,
    span, i {
        transition: background-color 0.15s ease-out, color 0.15s ease-out;
        will-change: background-color, color;
    }

    :root, html, html[data-theme="dark"] {
        --bg-page: #020617;
        --bg-radial: radial-gradient(circle at 50% -20%, #1e1b4b 0%, #020617 80%);
        --bg-card: rgba(15, 23, 42, 0.6);
        --border-card: rgba(255, 255, 255, 0.08);
        --bg-input: rgba(30, 41, 59, 0.5);
        --text-primary: #f8fafc;
        --text-muted: #94a3b8;
        --text-label: #cbd5e1;
        --brand-blue: #6366f1;
        --accent: #4f46e5;
        --accent-hover: #4338ca;
        --border-focus: #818cf8;
   
        --bg-danger-zone: rgba(244, 63, 94, 0.04);
        --border-danger-zone: rgba(244, 63, 94, 0.25);
        --text-danger-title: #fda4af;
        --text-danger-muted: #f43f5e;
        --btn-danger: #e11d48;
        --btn-danger-hover: #f43f5e;
    }

    html[data-theme="light"] {
        --bg-page: #f8fafc;
        --bg-radial: radial-gradient(circle at 50% -20%, #eff6ff 0%, #f1f5f9 100%);
        --bg-card: rgba(255, 255, 255, 0.85);
        --border-card: rgba(99, 102, 241, 0.12);
        --bg-input: #f1f5f9;
        --text-primary: #0f172a;
        --text-muted: #64748b;
        --text-label: #334155;
        --brand-blue: #4f46e5;
        --accent: #6366f1;
        --accent-hover: #4f46e5;
        --border-focus: #4f46e5;

        --bg-danger-zone: #fff1f2;
        --border-danger-zone: rgba(244, 63, 94, 0.2);
        --text-danger-title: #9f1239;
        --text-danger-muted: #be123c;
        --btn-danger: #e11d48;
        --btn-danger-hover: #be123c;
    }

    .dashboard-wrapper { 
        font-family: 'Plus Jakarta Sans', sans-serif; 
        background: var(--bg-page); 
        background-image: var(--bg-radial);
        overflow: hidden;
        height: 100vh;
        width: 100%;
        position: absolute;
        top: 0; left: 0; z-index: 50;
    }

    .glass { 
        background: var(--bg-card); 
        border: 1px solid var(--border-card); 
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
        
        transition: background-color 0.1s ease-out, border-color 0.1s ease-out;
        will-change: background-color, border-color;
    }

    html[data-theme="light"] .glass {
        box-shadow: 0 20px 40px -15px rgba(99, 102, 241, 0.05);
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
        from { opacity: 0; transform: translateY(12px); } 
        to { opacity: 1; transform: translateY(0); } 
    }
    
    .animate-card { 
        animation: simpleFade 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; 
        opacity: 0; 
        will-change: transform, opacity;
    }
    .delay-1 { animation-delay: 0.05s; }
    .delay-2 { animation-delay: 0.12s; }
    .delay-3 { animation-delay: 0.19s; }
    .delay-4 { animation-delay: 0.26s; }

    .custom-scroll::-webkit-scrollbar { width: 5px; }
    .custom-scroll::-webkit-scrollbar-thumb { background: rgba(99, 102, 241, 0.3); border-radius: 4px; }

    .glass-form-container input[type="text"], 
    .glass-form-container input[type="email"], 
    .glass-form-container input[type="password"] {
        width: 100% !important;
        background-color: var(--bg-input) !important;
        border: 1px solid var(--border-card) !important;
        color: var(--text-primary) !important;
        border-radius: 14px !important;
        padding: 14px 18px !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        outline: none !important;
        box-shadow: none !important;
        
        transition: background-color 0.15s ease-out, border-color 0.15s ease-out, color 0.15s ease-out;
    }
    
    .glass-form-container input:focus {
        border-color: var(--border-focus) !important;
        background-color: transparent !important;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12) !important;
    }

    .glass-form-container label {
        color: var(--text-label) !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.06em !important;
        margin-bottom: 8px !important;
        display: inline-block;
    }

    .glass-form-container h2 {
        color: var(--text-primary) !important;
        font-size: 18px !important;
        font-weight: 800 !important;
        letter-spacing: -0.02em !important;
        margin-bottom: 6px !important;
    }

    .glass-form-container p, 
    .glass-form-container .text-sm {
        color: var(--text-muted) !important;
        font-size: 13px !important;
        line-height: 1.5 !important;
    }

    /* Tombol Utama Simpan */
    .glass-form-container button[type="submit"]:not(#custom-danger-zone button) {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: var(--accent) !important;
        color: #ffffff !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.1em !important;
        padding: 14px 28px !important;
        border-radius: 14px !important;
        border: none !important;
        cursor: pointer !important;
        box-shadow: 0 6px 20px rgba(99, 102, 241, 0.25) !important;
        transform: translateY(0);
        
        transition: background-color 0.15s ease-out, transform 0.2s ease-out, box-shadow 0.2s ease-out;
    }

    .glass-form-container button[type="submit"]:not(#custom-danger-zone button):hover {
        background: var(--accent-hover) !important;
        box-shadow: 0 10px 25px rgba(99, 102, 241, 0.4) !important;
        transform: translateY(-2px);
    }

    .glass-form-container button[type="submit"]:not(#custom-danger-zone button):active {
        transform: translateY(0);
    }

    #custom-danger-zone {
        background-color: var(--bg-danger-zone) !important;
        border: 1px solid var(--border-danger-zone) !important;
        
        transition: background-color 0.15s ease-out, border-color 0.15s ease-out;
    }

    #custom-danger-zone h2 {
        color: var(--text-danger-title) !important;
    }

    #custom-danger-zone p {
        color: var(--text-danger-muted) !important;
        font-weight: 500;
    }

    #custom-danger-zone button, 
    #btn-trigger-delete {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        background-color: var(--btn-danger) !important;
        color: #ffffff !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.1em !important;
        padding: 14px 28px !important;
        border-radius: 14px !important;
        border: none !important;
        cursor: pointer !important;
        box-shadow: 0 6px 20px rgba(225, 29, 72, 0.25) !important;
        transform: translateY(0);
        
        transition: background-color 0.15s ease-out, transform 0.2s ease-out, box-shadow 0.2s ease-out;
    }

    #custom-danger-zone button:hover,
    #btn-trigger-delete:hover {
        background-color: var(--btn-danger-hover) !important;
        box-shadow: 0 10px 25px rgba(225, 29, 72, 0.4) !important;
        transform: translateY(-2px) !important;
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