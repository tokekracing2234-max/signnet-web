<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = { darkMode: 'class' }
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght=400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    * { 
        touch-action: manipulation; 
        box-sizing: border-box; 
        transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }
    
    :root, html, html[data-theme="dark"] {
        --bg-main: #020617;
        --bg-radial: radial-gradient(circle at 50% -20%, #1e1b4b 0%, #020617 80%);
        --glass-bg: rgba(15, 23, 42, 0.8);
        --glass-border: rgba(255, 255, 255, 0.08);
        --select-bg: #1e293b;
        --primary: #2563eb;
        --accent: #7dd3fc;
        --grad-logo: linear-gradient(135deg, var(--primary), var(--accent));
        --text-base: #f8fafc;
        --text-muted: #94a3b8;
        --card-sub-bg: rgba(99, 102, 241, 0.1);
        --card-sub-border: rgba(99, 102, 241, 0.2);
        --panel-bg: rgba(15, 23, 42, 0.7);
    }

    html[data-theme="light"] {
        --bg-main: #f5f3ff;
        --bg-radial: radial-gradient(circle at 50% -20%, #ddd6fe 0%, #f5f3ff 80%);
        --glass-bg: rgba(255, 255, 255, 0.9);
        --glass-border: rgba(99, 102, 241, 0.25);
        --panel-bg: #ffffff;
        --select-bg: #ffffff;
        --primary: #4f46e5;
        --accent: #0ea5e9;
        --grad-logo: linear-gradient(135deg, #4f46e5, #0ea5e9);
        --text-base: #1e1b4b;
        --text-muted: #4f46e5;
        --card-sub-bg: #e0e7ff;
        --card-sub-border: #c7d2fe;
    }

    .dashboard-wrapper { 
        font-family: 'Plus Jakarta Sans', sans-serif; 
        background: var(--bg-main); 
        background-image: var(--bg-radial);
        overflow: hidden;
        height: 100vh;
        width: 100%;
        position: absolute;
        top: 0; left: 0; z-index: 50;
        color: var(--text-base);
        transform: translateZ(0);
        backface-visibility: hidden;
    }

    .glass { 
        background: var(--glass-bg); 
        border: 1px solid var(--glass-border); 
    }

    .panel-control {
        background: var(--panel-bg);
        border: 1px solid var(--glass-border);
    }

    .logo-gradient {
        background: var(--grad-logo);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        color: transparent;
    }
    
    .main-content { overflow-y: auto; min-width: 0; transform: translateZ(0); }

    @keyframes simpleFade { 
        from { opacity: 0; transform: translateY(8px); } 
        to { opacity: 1; transform: translateY(0); } 
    }
    
    .animate-card { 
        animation: simpleFade 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards; 
        opacity: 0; 
        will-change: transform, opacity;
    }
    .delay-1 { animation-delay: 0.02s; }
    .delay-2 { animation-delay: 0.04s; }
    .delay-3 { animation-delay: 0.06s; }
    .delay-4 { animation-delay: 0.08s; }
    .delay-5 { animation-delay: 0.10s; }
    .delay-6 { animation-delay: 0.12s; }

    .camera-glow { box-shadow: 0 10px 30px rgba(99, 102, 241, 0.1); }
    html[data-theme="light"] .camera-glow { box-shadow: 0 10px 30px rgba(99, 102, 241, 0.15); }

    .timer-overlay {
        background: rgba(99, 102, 241, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
        position: absolute;
        inset: 0;
        z-index: 40;
        font-size: 6rem;
        font-weight: 900;
        color: white;
        text-shadow: 0 0 30px rgba(0,0,0,0.5);
    }

    .custom-scroll::-webkit-scrollbar { width: 5px; }
    .custom-scroll::-webkit-scrollbar-thumb { background: rgba(99, 102, 241, 0.4); border-radius: 4px; }
    .dynamic-select { background-color: var(--select-bg) !important; color: var(--text-base) !important; }
    
    .flash-effect { animation: flash 0.3s ease-out; }
    @keyframes flash { 0% { background: white; opacity: 1; } 100% { opacity: 0; } }

    .modal-blur-bg {
        display: none;
        background: rgba(2, 6, 23, 0.6);
    }
    .modal-blur-bg.active { display: flex; }

    @keyframes slideUp {
        from { transform: translateY(15px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    .modal-content { animation: slideUp 0.25s ease-out; will-change: transform, opacity; }
    .modal-content.closing { animation: slideDown 0.2s ease-in forwards; }
    @keyframes slideDown { from { transform: translateY(0); opacity: 1; } to { transform: translateY(15px); opacity: 0; } }

    html[data-theme="light"] #report-table-container > div {
        background-color: #ffffff !important;
        background-image: none !important;
        border: 1px solid #c7d2fe !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important;
    }

    html[data-theme="light"] #report-table-container h3,
    html[data-theme="light"] #report-table-container .text-xl,
    html[data-theme="light"] #report-table-container .font-bold:not([class*="text-"]) {
        color: #1e1b4b !important; 
    }

    html[data-theme="light"] #report-table-container div.border-l,
    html[data-theme="light"] #report-table-container div.border-r,
    html[data-theme="light"] #report-table-container div.border-t,
    html[data-theme="light"] #report-table-container div.border-b,
    html[data-theme="light"] #report-table-container [class*="border-slate"],
    html[data-theme="light"] #report-table-container [class*="border-white/"] {
        border-color: #cbd5e1 !important;
        border-style: solid !important;
        opacity: 1 !important;
    }

    html[data-theme="light"] #report-table-container [class*="text-emerald"],
    html[data-theme="light"] #report-table-container [class*="text-green"] {
        color: #059669 !important; 
    }

    html[data-theme="light"] #report-table-container [class*="text-amber"],
    html[data-theme="light"] #report-table-container [class*="text-yellow"] {
        color: #d97706 !important; 
    }

    html[data-theme="light"] #report-table-container [class*="text-rose"],
    html[data-theme="light"] #report-table-container [class*="text-red"] {
        color: #dc2626 !important; 
    }
</style>