<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoverscan Structural AI - Dashboard</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Configure Tailwind to toggle theme variants via class names strategy flags
        tailwind.config = {
            darkMode: 'class',
        }

        // Apply theme instantly on initialization to prevent blinding layout flashes
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,600;0,800;1,800&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css" rel="stylesheet" />
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #1e293b; }
        @media print {
            /* 1. Completely drop the interactive dashboard layout out of browser printing memory */
            #master-web-chassis, aside, header, form, button, .tab-panel-node {
                display: none !important;
                visibility: hidden !important;
            }
            
            /* 2. Format HTML and Body as a natural black-and-white paper canvas template */
            html, body {
                background-color: #ffffff !important;
                color: #0f172a !important;
                margin: 0mm !important;
                padding: 0mm !important;
                display: block !important;
                width: 100% !important;
                height: auto !important;
                visibility: visible !important;
            }

            /* 3. Re-align your print blueprint to center fluidly on standard A4 layout grid */
            #hoverscan-print-template {
                display: block !important;
                visibility: visible !important;
                position: relative !important;
                width: 180mm !important; /* Perfect standard text boundary alignment spacing */
                margin: 15mm auto !important; /* Centers the clean sheet perfectly on screen */
                padding: 0px !important;
                background-color: #ffffff !important;
                box-sizing: border-box !important;
            }
            
            #hoverscan-print-template * {
                visibility: visible !important;
            }

            /* 4. Strictly maintain formatting page break rules splits */
            .page-break-after-always { 
                page-break-after: always !important; 
                break-after: page !important; 
            }
            .page-break-inside-avoid { 
                page-break-inside: avoid !important; 
                break-inside: avoid !important; 
            }

            @page {
                size: A4 portrait;
                margin: 0mm; /* Completely strips browser header/footer text URL stamping */
            }
        }
    </style>
</head>
<!-- resources/views/layouts/app.blade.php -->
<body class="bg-[#f8fafc] text-slate-700 dark:bg-[#05060a] dark:text-slate-300 overflow-hidden antialiased transition-colors duration-200">

    <!-- ⚡ ADD THIS WRAPPER TO DECOUPLE INTERACTIVE SYSTEM SCREEN ELEMENTS -->
    <div id="master-web-chassis" class="w-full h-full">
        @yield('content')
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js"></script>
    <script>lucide.createIcons();</script>
</body>
</html>