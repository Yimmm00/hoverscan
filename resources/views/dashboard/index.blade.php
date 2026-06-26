@extends('layouts.app')

@section('content')
<div class="flex h-screen bg-[#f8fafc] text-slate-700 dark:bg-[#05060a] dark:text-slate-300 font-sans overflow-hidden transition-colors duration-200">
    
    <aside class="w-64 border-r border-slate-200/60 bg-white dark:border-white/5 dark:bg-[#080a0f] flex flex-col z-30">
        <div class="p-6 h-full flex flex-col justify-between">
            <div>
                <div class="flex flex-col items-center justify-center text-center w-full mb-10 pt-4 px-2">
                    <div class="mb-4 shrink-0">
                        <div class="w-20 h-20 bg-blue-600/10 border border-blue-500/20 rounded-2xl flex items-center justify-center text-blue-600 text-3xl font-black drop-shadow-[0_4px_12px_rgba(37,99,235,0.15)]">
                            H
                        </div>
                    </div>
                </div>

                <nav class="space-y-1.5" id="nav-tabs-container">
                    <button data-tab="dashboard" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-base font-semibold transition-all text-white bg-blue-600 shadow-xl shadow-blue-600/20">
                        <i data-lucide="layout-dashboard" class="w-[18px] h-[18px]"></i> Dashboard
                    </button>
                    <button data-tab="assets" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-base font-semibold transition-all text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5">
                        <i data-lucide="database" class="w-[18px] h-[18px]"></i> Asset Hub
                    </button>
                    <button data-tab="defects" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-base font-semibold transition-all text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5">
                        <i data-lucide="alert-triangle" class="w-[18px] h-[18px]"></i> Defect Classes
                    </button>
                    <button data-tab="analysis" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-base font-semibold transition-all text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5">
                        <i data-lucide="bar-chart-3" class="w-[18px] h-[18px]"></i> AI Analysis
                    </button>
                </nav>
            </div>

            <div class="pt-4 border-t border-slate-200/60 dark:border-white/5 mt-auto">
                <div class="text-[10px] uppercase font-black tracking-widest text-slate-400 dark:text-slate-600 px-4">
                    Platform Framework: v1.2
                </div>
            </div>
        </div>
    </aside>

    <main class="flex-1 flex flex-col min-w-0 bg-[#f8fafc] dark:bg-[#05060a] transition-colors duration-200">
        <header class="h-20 border-b border-slate-200/60 bg-white dark:border-white/5 dark:bg-[#080a0f]/50 backdrop-blur-xl z-10 flex items-center justify-between px-10">
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-600 dark:text-blue-500">System Pipeline</span>
                </div>
                <i data-lucide="chevron-right" class="w-3 h-3 text-slate-400"></i>
                <h2 id="current-view-header" class="font-bold text-sm uppercase tracking-widest italic text-slate-800 dark:text-white">Dashboard Overview</h2>
            </div>

            <button id="theme-toggle-btn" class="p-2.5 rounded-xl border border-slate-200 dark:border-white/5 bg-slate-50 dark:bg-white/5 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/10 cursor-pointer transition-all">
                <i id="theme-toggle-icon" data-lucide="sun" class="w-[18px] h-[18px]"></i>
            </button>
        </header>

        <div class="flex-1 overflow-y-auto p-10 custom-scrollbar relative bg-[#f8fafc] dark:bg-[#05060a]">
            @include('dashboard.analytics')
            @include('dashboard.assets')
            @include('dashboard.analysis')
            @include('dashboard.defects')
        </div>
    </main>
</div>

<div 
  id="hoverscan-print-template" 
  class="fixed inset-0 opacity-0 pointer-events-none bg-white text-black p-6 font-sans max-w-[190mm] mx-auto z-[-1] print:opacity-100 print:pointer-events-auto print:block print:relative print:z-[9999]"
  style="color: #000000; background-color: #ffffff; font-size: 11px; line-height: 1.4;"
>
  <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #000000; padding-bottom: 12px; margin-bottom: 16px;">
    <div style="display: flex; align-items: center; gap: 12px;">
      <img 
        src="/hoverscanimg.png" 
        alt="Hoverscan Logo" 
        style="height: 36px; width: 36px; object-fit: contain;" 
      />
      <div>
        <h1 style="font-size: 18px; font-weight: 900; letter-spacing: -0.025em; color: #000000; margin: 0; text-transform: uppercase;">HOVERSCAN INSPECTION REPORT</h1>
        <p style="font-size: 9px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.1em; margin: 2px 0 0 0;">AI Structural Guardian Analytics</p>
      </div>
    </div>

    <div style="text-align: right;">
      <p style="font-size: 11px; font-weight: 900; color: #4338ca; margin: 0; text-transform: uppercase;">Hoverscan AI Damage Detection</p>
      <p style="font-size: 9px; font-family: monospace; color: #64748b; margin: 2px 0 0 0;">Date: <span>{{ date('d/m/Y') }}</span></p>
    </div>
  </div>

  <div style="display: flex; gap: 24px; border-bottom: 1px solid #cbd5e1; padding-bottom: 12px; margin-bottom: 16px;">
    <div style="flex: 1; text-align: left;">
      <p style="font-weight: 700; color: #64748b; text-transform: uppercase; font-size: 8px; margin: 0 0 2px 0;">Target Structure</p>
      <p style="font-size: 14px; font-weight: 900; color: #000000; margin: 0;" id="print-bridge-name">DARUL HANA S-BRIDGE</p>
      <p style="font-family: monospace; font-size: 10px; color: #334155; margin: 2px 0 0 0;">Jurisdiction Division: SARAWAK</p>
    </div>
    <div style="flex: 1; text-align: left;">
      <p style="font-weight: 700; color: #64748b; text-transform: uppercase; font-size: 8px; margin: 0 0 2px 0;">Telemetry Metrics</p>
      <p style="margin: 0; font-size: 11px; color: #000000;">Environmental Temp: <strong id="print-temp-val">31</strong><strong>°C</strong></p>
      <p style="margin: 2px 0 0 0; font-size: 11px; color: #000000;">Humidity Status: <strong id="print-humidity-val">78</strong><strong>% RH</strong></p>
      <p style="margin: 2px 0 0 0; font-size: 11px; color: #000000;">Total Detections Logged: <strong id="print-total-count">0</strong></p>
    </div>
  </div>

  <div style="margin-bottom: 20px;">
    <h3 style="font-size: 9px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; border-bottom: 1px solid #94a3b8; padding-bottom: 4px; margin-bottom: 10px; color: #000000; text-align: left;">
      Isolated Defect Evidence Logs
    </h3>
    <div id="print-evidence-logs-container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
         </div>
  </div>

  <div>
    <h3 style="font-size: 9px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; border-bottom: 1px solid #94a3b8; padding-bottom: 4px; margin-bottom: 10px; color: #000000; text-align: left;">
      Distribution Analysis Breakdown (Detected Only)
    </h3>
    <table style="font-size: 10px; color: #000000; border-collapse: collapse; width: 100%;">
      <thead>
        <tr style="border-bottom: 1.5px solid #000000; font-weight: 900; text-transform: uppercase; color: #475569; font-size: 9px;">
          <th style="padding-bottom: 4px; text-align: left;">Defect Class Entry</th>
          <th style="padding-bottom: 4px; text-align: right;">Occurrence Instances</th>
        </tr>
      </thead>
      <tbody id="print-distribution-table-body">
           </tbody>
    </table>
  </div>
</div>

<script>
    // 1. Theme Configuration Toggling Engine System
    const toggleBtn = document.getElementById('theme-toggle-btn');
    const toggleIcon = document.getElementById('theme-toggle-icon');

    function updateToggleIcon() {
        toggleBtn.innerHTML = ''; 
        const newIcon = document.createElement('i');
        newIcon.id = 'theme-toggle-icon';
        newIcon.className = 'w-[18px] h-[18px] block animate-fade-in';
        
        if (document.documentElement.classList.contains('dark')) {
            newIcon.setAttribute('data-lucide', 'sun');
        } else {
            newIcon.setAttribute('data-lucide', 'moon');
        }
        toggleBtn.appendChild(newIcon);
        if (typeof lucide !== 'undefined') { lucide.createIcons(); }
    }

    document.addEventListener('DOMContentLoaded', () => { updateToggleIcon(); });

    toggleBtn.addEventListener('click', () => {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        }
        updateToggleIcon();
        
        if (typeof gisMapInstance !== 'undefined' && gisMapInstance && gisMapInstance.setStyle) {
            const isDark = document.documentElement.classList.contains('dark');
            const styleUrl = isDark
                ? 'https://basemaps.cartocdn.com/gl/dark-matter-gl-style/style.json'
                : 'https://basemaps.cartocdn.com/gl/positron-gl-style/style.json';
            gisMapInstance.setStyle(styleUrl);
        }
    });

    // 2. Tab Navigation Component Controls Toggle Manager
    document.getElementById('nav-tabs-container').addEventListener('click', function(e) {
        const btn = e.target.closest('button[data-tab]');
        if (!btn) return;
        document.querySelectorAll('#nav-tabs-container button').forEach(el => {
            el.className = "w-full flex items-center gap-3 px-4 py-3 rounded-xl text-base font-semibold transition-all text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5";
        });
        btn.className = "w-full flex items-center gap-3 px-4 py-3 rounded-xl text-base font-semibold transition-all text-white bg-blue-600 shadow-xl shadow-blue-600/20";
        const activeTab = btn.getAttribute('data-tab');
        document.querySelectorAll('.tab-panel-node').forEach(el => el.classList.add('hidden'));
        document.getElementById('view-panel-' + activeTab).classList.remove('hidden');
        document.getElementById('current-view-header').innerText = activeTab.toUpperCase() + " INTERFACE MATRIX";
    });

    // ⚡ INTER-TAB SELECTION ROUTER: Ties Asset maps/lists straight to AI inputs seamlessly
    window.routeToAnalysisTarget = function(bridgeName) {
        const selectElement = document.querySelector('select[name="bridge_name"]');
        if (selectElement) {
            selectElement.value = bridgeName;
            selectElement.dispatchEvent(new Event('change'));
        }
        const analysisTabBtn = document.querySelector('button[data-tab="analysis"]');
        if (analysisTabBtn) {
            analysisTabBtn.click();
        }
    };

    // 3. Dynamic Modal Library Triggers
    document.addEventListener('click', function(e) {
        const card = e.target.closest('.defect-trigger-card');
        if (card) {
            const className = card.getAttribute('data-defect');
            if (className) openDefectGallery(className);
        }
        if (e.target.closest('#close-gallery-btn')) closeDefectGallery();
    });

    async function openDefectGallery(className) {
        const modal = document.getElementById('defect-gallery-modal');
        const titleSpan = document.getElementById('modal-title-class-name');
        const gridContainer = document.getElementById('modal-gallery-grid-content');
        titleSpan.innerText = className.toUpperCase();
        gridContainer.innerHTML = `<div class="h-full min-h-[300px] flex flex-col items-center justify-center gap-3 text-slate-400 w-full"><i data-lucide="refresh-cw" class="w-8 h-8 animate-spin text-blue-500"></i><p class="text-[10px] font-black uppercase tracking-widest">Querying database rows records...</p></div>`;
        lucide.createIcons();
        modal.classList.remove('hidden');

        try {
            const response = await fetch(`/api/defect-class-records/${encodeURIComponent(className)}`);
            const payload = await response.json();
            gridContainer.innerHTML = '';

            if (payload.data && payload.data.length > 0) {
                const innerGrid = document.createElement('div');
                innerGrid.className = 'grid grid-cols-1 md:grid-cols-2 gap-6 w-full';
                payload.data.forEach(rec => {
                    const card = document.createElement('div');
                    card.className = 'border border-slate-200 dark:border-white/5 rounded-3xl overflow-hidden bg-white dark:bg-white/5 flex flex-col shadow-sm';
                    card.innerHTML = `
                        <div class="relative aspect-video bg-black flex items-center justify-center overflow-hidden border-b border-slate-200 dark:border-white/5"><img src="${rec.image_url}" class="w-full h-full object-cover"><span class="absolute top-4 left-4 text-[9px] font-mono font-black px-3 py-1 bg-white/90 dark:bg-black/70 text-slate-800 dark:text-blue-400 rounded-full border border-slate-200 dark:border-white/10 shadow">${rec.dataset_id}</span></div>
                        <div class="p-5 flex-1 flex flex-col justify-between gap-4"><div class="space-y-1"><h5 class="text-sm font-black uppercase italic text-slate-900 dark:text-white truncate">${rec.bridge_name}</h5><p class="text-[10px] font-bold text-slate-400 dark:text-slate-500">Timestamp: ${rec.date_logged}</p><div class="pt-2 flex gap-4 text-[10px] font-mono text-slate-400 uppercase font-black"><span>CONF: <strong class="text-emerald-500">${Math.round(rec.confidence_score * 100)}%</strong></span><span>ENV: <strong class="text-blue-500">${rec.temperature}°C / ${rec.humidity}% RH</strong></span></div></div></div>
                    `;
                    innerGrid.appendChild(card);
                });
                gridContainer.appendChild(innerGrid);
            } else {
                gridContainer.innerHTML = `<div class="h-full min-h-[300px] flex flex-col items-center justify-center text-center opacity-40 w-full text-slate-400"><i data-lucide="x-circle" class="w-12 h-12 mb-4"></i><p class="text-sm font-black uppercase tracking-widest">No damage frames cataloged</p></div>`;
            }
            lucide.createIcons();
        } catch (err) {
            gridContainer.innerHTML = `<div class="text-center py-20 text-rose-500 text-xs font-bold uppercase w-full">Failed loading stream records.</div>`;
        }
    }

    function closeDefectGallery() {
        document.getElementById('defect-gallery-modal').classList.add('hidden');
    }
</script>

@stack('view-scripts')
@endsection