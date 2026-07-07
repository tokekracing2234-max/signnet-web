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
    }
    
    .dashboard-wrapper, 
    .main-content,
    h1, h3, h4, p, span, i {
        transition: background-color 0.15s ease-out, 
                    color 0.15s ease-out;
        will-change: background-color, color;
    }
    
    .glass { 
        background: var(--glass-bg); 
        border: 1px solid var(--glass-border); 
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
        
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        
        transition: background-color 0.1s ease-out, border-color 0.1s ease-out;
    }

    .hover-lift {
        transition: transform 0.2s ease-out, box-shadow 0.2s ease-out;
    }
    .hover-lift:hover { 
        transform: translateY(-2px); 
    }
    
    .hover-lift-indigo:hover { box-shadow: 0 12px 20px -8px rgba(79, 70, 229, 0.2); }
    .hover-lift-emerald:hover { box-shadow: 0 12px 20px -8px rgba(16, 185, 129, 0.2); }
    .hover-lift-amber:hover { box-shadow: 0 12px 20px -8px rgba(245, 158, 11, 0.2); }

    :root, html, html[data-theme="dark"] {
        --bg-main: #020617;
        --bg-radial: radial-gradient(circle at 50% -20%, #1e1b4b 0%, #020617 80%);
        --glass-bg: rgba(15, 23, 42, 0.65);
        --glass-border: rgba(255, 255, 255, 0.06);
        --select-bg: #1e293b;
        --text-base: #f8fafc;
        
        --card-indigo: rgba(30, 27, 75, 0.4);
        --card-emerald: rgba(6, 78, 59, 0.3);
        --card-amber: rgba(120, 53, 4, 0.3);
        --border-indigo: rgba(99, 102, 241, 0.15);
        --border-emerald: rgba(16, 185, 129, 0.15);
        --border-amber: rgba(245, 158, 11, 0.15);
    }

    html[data-theme="light"] {
        --bg-main: #f8fafc;
        --bg-radial: radial-gradient(circle at 50% -20%, #e0e7ff 0%, #f8fafc 100%);
        --glass-bg: rgba(255, 255, 255, 0.85);
        --glass-border: rgba(99, 102, 241, 0.08);
        --select-bg: #ffffff;
        --text-base: #0f172a;

        --card-indigo: rgba(240, 244, 255, 0.9);
        --card-emerald: rgba(240, 253, 244, 0.9);
        --card-amber: rgba(254, 243, 199, 0.9);
        --border-indigo: rgba(165, 180, 252, 0.3);
        --border-emerald: rgba(110, 231, 183, 0.3);
        --border-amber: rgba(252, 211, 77, 0.3);
    }

    .dashboard-wrapper { 
        font-family: 'Plus Jakarta Sans', sans-serif; 
        background: var(--bg-main); 
        background-image: var(--bg-radial);
        min-height: 100vh;
        width: 100%;
    }

    .card-dynamic-indigo { background: var(--card-indigo); border: 1px solid var(--border-indigo); }
    .card-dynamic-emerald { background: var(--card-emerald); border: 1px solid var(--border-emerald); }
    .card-dynamic-amber { background: var(--card-amber); border: 1px solid var(--border-amber); }

    .logo-gradient {
        background: linear-gradient(135deg, #4f46e5, #06b6d4);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    html[data-theme="dark"] .logo-gradient {
        background: linear-gradient(135deg, #6366f1, #38bdf8);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .app-layout-grid {
        display: grid;
        grid-template-columns: 5rem 1fr;
        width: 100%; min-height: 100vh;
    }

    @media (min-width: 768px) { .app-layout-grid { grid-template-columns: 6rem 1fr; } }
    .app-layout-grid:has(#sidebar:hover) { grid-template-columns: 16rem 1fr; }
    .main-content { grid-column: 2; overflow-y: auto; min-width: 0; }

    @keyframes simpleFade { 
        from { opacity: 0; transform: translateY(8px); } 
        to { opacity: 1; transform: translateY(0); } 
    }
    
    .animate-card { 
        animation: simpleFade 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; 
        opacity: 0; 
    }

    .delay-1 { animation-delay: 0.05s; }
    .delay-2 { animation-delay: 0.12s; }
    .delay-3 { animation-delay: 0.19s; }
    .delay-4 { animation-delay: 0.26s; }
    .delay-5 { animation-delay: 0.33s; }
    .delay-6 { animation-delay: 0.40s; }
    .delay-7 { animation-delay: 0.47s; }

    .custom-scroll::-webkit-scrollbar { width: 6px; }
    .custom-scroll::-webkit-scrollbar-thumb { background: rgba(99, 102, 241, 0.3); border-radius: 4px; }

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
    
    @media (max-width: 639px) {
        .table-overflow { 
            overflow-x: hidden !important; 
            overflow-y: hidden !important;
            position: relative;
            z-index: 10;
            padding-bottom: 0.5rem;
        }
        
        #dataset-table-body { 
            display: grid; 
            grid-template-columns: 1fr; 
            gap: 1rem; 
            padding: 1rem; 
        }
        
        table, thead, tbody, th, td, tr { 
            display: block; 
            width: 100%; 
        }
        
        thead { 
            display: none; 
        }
        
        #dataset-table-body tr {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 1.25rem;
            padding: 1rem;
            position: relative;
            z-index: 10;
            backface-visibility: hidden;
            will-change: transform, opacity;
        }
        
        #dataset-table-body td {
            display: flex; 
            align-items: center; 
            justify-content: space-between;
            padding: 0.5rem 0; 
            border-bottom: 1px dashed rgba(99, 102, 241, 0.08); 
            text-align: right;
        }
        
        #dataset-table-body td:last-child { 
            border-bottom: none; 
            padding-bottom: 0; 
            margin-top: 0.5rem; 
        }
        
        #dataset-table-body td:first-child { 
            padding-top: 0; 
        }
        
        #dataset-table-body td::before {
            content: attr(data-label); 
            float: left; 
            font-weight: 800; 
            text-transform: uppercase;
            font-size: 10px; 
            letter-spacing: 0.05em; 
            color: #4f46e5; 
            text-align: left;
        }
        
        html[data-theme="dark"] #dataset-table-body td::before { 
            color: #818cf8; 
        }
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