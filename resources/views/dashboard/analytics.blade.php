<div id="view-panel-dashboard" class="space-y-10 tab-panel-node view-active animate-fade-in">
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <div class="p-8 rounded-[2.5rem] border border-slate-200/80 dark:border-white/5 bg-white dark:bg-[#0c0e14] shadow-sm transition-all duration-200 hover:shadow-md">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Total Bridges Registered</p>
                    <h3 class="text-4xl font-black mt-2 tracking-tight text-slate-900 dark:text-white font-mono">{{ $total_bridges }}</h3>
                </div>
                <div class="p-3 bg-blue-500/10 text-blue-600 dark:text-blue-500 rounded-2xl">
                    <i data-lucide="database" class="w-5 h-5"></i>
                </div>
            </div>
        </div>

        <div class="p-8 rounded-[2.5rem] border border-slate-200/80 dark:border-white/5 bg-white dark:bg-[#0c0e14] shadow-sm transition-all duration-200 hover:shadow-md">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Unresolved Flaw Anomalies</p>
                    <h3 id="dash-total-anomalies" class="text-4xl font-black mt-2 tracking-tight text-rose-500 font-mono transition-transform duration-300">{{ $total_anomalies }}</h3>
                </div>
                <div class="p-3 bg-rose-500/10 text-rose-500 rounded-2xl">
                    <i data-lucide="shield-alert" class="w-5 h-5"></i>
                </div>
            </div>
        </div>

        <div class="p-8 rounded-[2.5rem] border border-slate-200/80 dark:border-white/5 bg-white dark:bg-[#0c0e14] shadow-sm transition-all duration-200 hover:shadow-md">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">AI Mean Confidence Rating</p>
                    @php
                        $confFloat = floatval(str_replace('%', '', $avg_confidence));
                        $badgeThemeClass = 'text-emerald-600 dark:text-emerald-400';
                        $bgIconClass = 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400';
                        if($confFloat < 40) {
                            $badgeThemeClass = 'text-amber-500 dark:text-amber-400';
                            $bgIconClass = 'bg-amber-500/10 text-amber-500 dark:text-amber-400';
                        }
                    @endphp
                    <h3 id="dash-avg-confidence" class="text-4xl font-black mt-2 tracking-tight font-mono {{ $badgeThemeClass }}">{{ $avg_confidence }}</h3>
                </div>
                <div class="p-3 rounded-2xl {{ $bgIconClass }}">
                    <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
        <div class="lg:col-span-2 p-8 rounded-[3.5rem] border border-slate-200/80 dark:border-white/5 bg-white dark:bg-[#0c0e14] shadow-sm flex flex-col max-h-[480px]">
            <div class="flex justify-between items-center mb-6">
                <h4 class="font-black uppercase tracking-tight italic text-base text-slate-900 dark:text-white">Recent YOLO Status Stream</h4>
                <select id="dash-date-filter" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-[#080a0f] text-slate-900 dark:text-white text-[10px] font-black uppercase tracking-wider outline-none cursor-pointer hover:border-slate-300 dark:hover:border-white/20 transition-all">
                    <option value="all">All Telemetry</option>
                    <option value="today">Today</option>
                    <option value="7days">Last 7 Days</option>
                    <option value="month">This Month</option>
                </select>
            </div>
            
            <div class="space-y-3 flex-1 overflow-y-auto pr-1 custom-scrollbar" id="dash-yolo-stream-container">
                @forelse($recent_logs as $log)
                    <div class="p-4 rounded-2xl border border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/5 flex items-center justify-between cursor-pointer transition-all hover:bg-slate-100/70 dark:hover:bg-white/10 group" onclick="inspectAnomalyStream('{{ $log->id ?? 0 }}', '{{ $log->bridge_name }}', '{{ $log->defect_class }}', '{{ $log->severity }}')">
                        <div>
                            <p class="font-black text-xs uppercase tracking-tight text-slate-800 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ $log->bridge_name }}</p>
                            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-400 mt-0.5">Detected Flaw Class: <span class="font-mono text-slate-600 dark:text-slate-300">{{ strtoupper($log->defect_class) }}</span></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-lg text-[8px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400 bg-amber-500/10">{{ $log->severity }}</span>
                            <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300 dark:text-slate-600 opacity-0 group-hover:opacity-100 transition-all transform translate-x-[-4px] group-hover:translate-x-0"></i>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-xs uppercase font-bold tracking-widest text-slate-400 dark:text-slate-500" id="dash-stream-empty-text">
                        No recent flaw vectors logged.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="lg:col-span-3 p-8 rounded-[3.5rem] border border-slate-200/80 dark:border-white/5 bg-white dark:bg-[#0c0e14] shadow-sm flex flex-col justify-between max-h-[480px]">
            <div>
                <h4 class="font-black uppercase tracking-tight italic text-base mb-1 text-slate-900 dark:text-white">Defect Matrix Distribution</h4>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-4">Live distribution tracking index profiles (Scroll horizontally to view all)</p>
            </div>
            
            <div class="flex-1 w-full overflow-x-auto custom-scrollbar flex items-center min-h-[320px]">
                <div class="h-[290px] min-w-[900px] w-full relative">
                    <canvas id="hoverscan-analytics-chart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="anomaly-inspect-modal" class="fixed inset-0 z-50 flex justify-end bg-slate-900/40 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300">
    <div class="w-full max-w-md h-full bg-white dark:bg-[#080a0f] border-l border-slate-200 dark:border-white/5 p-8 flex flex-col justify-between shadow-2xl transform translate-x-full transition-transform duration-300 custom-scrollbar overflow-y-auto">
        <div>
            <div class="flex items-center justify-between pb-6 border-b border-slate-100 dark:border-white/5">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Telemetry Inspector</span>
                </div>
                <button onclick="closeAnomalyInspector()" class="p-2 rounded-xl bg-slate-50 dark:bg-white/5 hover:bg-slate-100 dark:hover:bg-white/10 text-slate-400 transition-all">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="mt-8 space-y-6">
                <div>
                    <label class="text-[9px] uppercase tracking-wider font-black text-slate-400 block mb-1">Target Node Context</label>
                    <h4 id="inspect-bridge-title" class="text-xl font-black uppercase italic text-slate-900 dark:text-white">---</h4>
                </div>

                <div class="p-5 rounded-2xl bg-slate-50 dark:bg-white/[0.02] border border-slate-100 dark:border-white/5 space-y-4">
                    <div class="flex justify-between items-center text-xs font-bold uppercase">
                        <span class="text-slate-400">Defect Classification</span>
                        <span id="inspect-defect-badge" class="font-mono text-slate-900 dark:text-white">---</span>
                    </div>
                    <div class="flex justify-between items-center text-xs font-bold uppercase">
                        <span class="text-slate-400">Severity Threat Threshold</span>
                        <span id="inspect-severity-badge" class="px-2 py-0.5 rounded text-[9px] font-black">---</span>
                    </div>
                </div>

                <div>
                    <label class="text-[9px] uppercase tracking-wider font-black text-slate-400 block mb-2">Live Computer Vision Frame Target</label>
                    <div class="relative aspect-video rounded-2xl bg-slate-900 border border-slate-200 dark:border-white/5 overflow-hidden flex items-center justify-center text-slate-500 font-mono text-[10px]">
                        <i data-lucide="image" class="w-8 h-8 absolute opacity-10"></i>
                        <span class="relative z-10 uppercase font-black tracking-widest">YOLO AI Overlay Blueprint Frame</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-slate-100 dark:border-white/5 mt-8 space-y-3">
            <button onclick="commitAnomalyResolution()" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-lg shadow-emerald-600/10 flex items-center justify-center gap-2">
                <i data-lucide="check" class="w-4 h-4"></i> Resolve Structural Anomaly
            </button>
        </div>
    </div>
</div>

@push('view-scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let defectDistributionChartInstance = null;
    let selectedInspectionId = null;
    const isDark = document.documentElement.classList.contains('dark');

    const chartLabelsIndex = [
        'Potholes', 'Concrete Spalling', 'Cracks', 'Spalling Expose Rebar', 
        'Mold', 'Rust', 'Staining', 'Peeling', 'Bridge Joint', 'Road Bleeding', 'Vegetation'
    ];

    // Modal UI Actions Engine
    function inspectAnomalyStream(id, bridgeName, defectClass, severity) {
        // Store the real database ID globally for the resolution trigger
        selectedInspectionId = id; 
        
        document.getElementById('inspect-bridge-title').innerText = bridgeName;
        document.getElementById('inspect-defect-badge').innerText = defectClass.toUpperCase();
        
        const sevBadge = document.getElementById('inspect-severity-badge');
        sevBadge.innerText = severity.toUpperCase();
        sevBadge.className = "px-2 py-0.5 rounded text-[9px] font-black bg-amber-500/10 text-amber-500";
        if(severity.toLowerCase() === 'high' || severity.toLowerCase() === 'critical') {
            sevBadge.className = "px-2 py-0.5 rounded text-[9px] font-black bg-rose-500/10 text-rose-500";
        }

        const modal = document.getElementById('anomaly-inspect-modal');
        const innerChassis = modal.querySelector('div');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            innerChassis.classList.remove('translate-x-full');
        }, 50);
    }

    function closeAnomalyInspector() {
        const modal = document.getElementById('anomaly-inspect-modal');
        const innerChassis = modal.querySelector('div');
        modal.classList.add('opacity-0');
        innerChassis.classList.add('translate-x-full');
        setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }

    async function commitAnomalyResolution() {
        if (!selectedInspectionId || selectedInspectionId == 0) {
            alert("Invalid anomaly sequence target.");
            return;
        }

        if (!confirm("Securely log this vector out of active matrix alerts? Status shifts to resolved.")) return;

        try {
            const response = await fetch('/web-api/defects/resolve', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ id: selectedInspectionId })
            });

            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || "Failed execution.");

            // Dispatch live state change event across open interface tabs
            const event = new CustomEvent('hoverscan:telemetry-update', {
                detail: {
                    bridgeName: document.getElementById('inspect-bridge-title').innerText,
                    addedCount: -1,
                    defectClass: document.getElementById('inspect-defect-badge').innerText.toLowerCase()
                }
            });
            document.dispatchEvent(event);
            
            // Re-fetch current timeline state to cleanly update both chart counts and telemetry rows automatically
            fetchFilteredAnalytics();
            closeAnomalyInspector();
            
        } catch (err) {
            console.error(err);
            alert("Error updating ledger: " + err.message);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const ctx = document.getElementById('hoverscan-analytics-chart').getContext('2d');
        const structuralDataMap = {};
        chartLabelsIndex.forEach(label => { structuralDataMap[label] = 0; });

        @if(isset($all_chart_records))
            @foreach($all_chart_records as $log)
                @php
                    $normalizedClass = match(strtolower($log->defect_class)) {
                        'potholes', 'pothole'   => 'Potholes',
                        'concrete spalling'     => 'Concrete Spalling',
                        'crack', 'cracks'       => 'Cracks',
                        'spalling expose rebar' => 'Spalling Expose Rebar',
                        'mold'                  => 'Mold',
                        'rust'                  => 'Rust',
                        'staining'              => 'Staining',
                        'peeling'               => 'Peeling',
                        'bridge joint'          => 'Bridge Joint',
                        'road bleeding'         => 'Road Bleeding',
                        'vegetation'            => 'Vegetation',
                        default                 => null
                    };
                @endphp
                @if($normalizedClass)
                    structuralDataMap['{{ $normalizedClass }}']++;
                @endif
            @endforeach
        @endif

        const emptyStatePlugin = {
            id: 'emptyState',
            afterDraw: (chart) => {
                const amtDatasets = chart.data.datasets[0].data;
                const totalActiveSum = amtDatasets.reduce((sum, val) => sum + val, 0);

                if (totalActiveSum === 0) {
                    const { ctx, width, height } = chart;
                    chart.clear();
                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.font = '800 10px "Plus Jakarta Sans", sans-serif';
                    ctx.fillStyle = isDark ? '#475569' : '#94a3b8';
                    ctx.fillText('NO ACTIVE DEFECT VECTORS INDEXED FOR THIS ENVIRONMENT PROFILE', width / 2, height / 2);
                    ctx.restore();
                }
            }
        };

        defectDistributionChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartLabelsIndex,
                datasets: [{
                    label: 'Active Flaw Counts',
                    data: Object.values(structuralDataMap),
                    backgroundColor: isDark ? 'rgba(59, 130, 246, 0.15)' : 'rgba(59, 130, 246, 0.08)',
                    borderColor: '#3b82f6',
                    borderWidth: 2,
                    borderRadius: 8,
                    hoverBackgroundColor: '#3b82f6',
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, 
                plugins: { legend: { display: false } },
                layout: { padding: { left: 10, right: 15, top: 10, bottom: 0 } },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { 
                            color: isDark ? '#475569' : '#94a3b8', 
                            font: { weight: '800', size: 9 },
                            maxRotation: 0, minRotation: 0, autoSkip: false 
                        }
                    },
                    y: {
                        grid: { color: isDark ? 'rgba(255,255,255,0.03)' : 'rgba(0,0,0,0.03)' },
                        ticks: { 
                            color: isDark ? '#475569' : '#94a3b8', 
                            font: { weight: 'bold', size: 9 }, 
                            precision: 0, suggestedMax: 5 
                        }
                    }
                }
            },
            plugins: [emptyStatePlugin]
        });

        document.getElementById('dash-date-filter').addEventListener('change', fetchFilteredAnalytics);
    });

    async function fetchFilteredAnalytics() {
        const rangeValue = document.getElementById('dash-date-filter').value;
        const streamContainer = document.getElementById('dash-yolo-stream-container');

        streamContainer.innerHTML = `
            <div class="py-12 text-center text-xs uppercase font-black tracking-widest text-blue-500 flex items-center justify-center gap-2">
                <i data-lucide="refresh-cw" class="w-4 h-4 animate-spin"></i> Filtering Metric Matrix...
            </div>
        `;
        if (typeof lucide !== 'undefined') lucide.createIcons();

        try {
            const response = await fetch(`/web-api/dashboard/filter?range=${rangeValue}`);
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error("Filter request failed.");

            if (result.logs.length === 0) {
                streamContainer.innerHTML = `
                    <div class="py-12 text-center text-xs uppercase font-bold tracking-widest text-slate-400 dark:text-slate-500" id="dash-stream-empty-text">
                        No recent flaw vectors logged within this timeframe.
                    </div>
                `;
            } else {
                streamContainer.innerHTML = result.logs.map(log => `
                    <div class="p-4 rounded-2xl border border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/5 flex items-center justify-between transition-all hover:bg-slate-100 dark:hover:bg-white/10" onclick="inspectAnomalyStream('${log.id || 0}', '${log.bridge_name}', '${log.defect_class}', '${log.severity}')">
                        <div>
                            <p class="font-black text-xs uppercase tracking-tight text-slate-800 dark:text-white">${log.bridge_name}</p>
                            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-400 mt-0.5">Detected Flaw Class: <span class="font-mono text-slate-600 dark:text-slate-300">${log.defect_class.toUpperCase()}</span></p>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg text-[8px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400 bg-amber-500/10">${log.severity}</span>
                    </div>
                `).join('');
            }

            if (result.chart) {
                defectDistributionChartInstance.data.datasets[0].data = [
                    result.chart['Potholes'] || 0, result.chart['Concrete Spalling'] || 0,
                    result.chart['Cracks'] || 0, result.chart['Spalling Expose Rebar'] || 0,
                    result.chart['Mold'] || 0, result.chart['Rust'] || 0,
                    result.chart['Staining'] || 0, result.chart['Peeling'] || 0,
                    result.chart['Bridge Joint'] || 0, result.chart['Road Bleeding'] || 0, 
                    result.chart['Vegetation'] || 0
                ];
                defectDistributionChartInstance.update();
            }
        } catch (err) {
            console.error(err);
            streamContainer.innerHTML = `<div class="py-12 text-center text-xs font-bold text-rose-500 uppercase">Error updating analytics filter timeline.</div>`;
        }
    }

    document.addEventListener('hoverscan:telemetry-update', (e) => {
        const { bridgeName, addedCount, defectClass, severity } = e.detail;
        const totalAnomaliesEl = document.getElementById('dash-total-anomalies');
        
        if (totalAnomaliesEl) {
            let currentTotal = parseInt(totalAnomaliesEl.innerText.trim()) || 0;
            let newTotal = currentTotal + addedCount;
            totalAnomaliesEl.innerText = newTotal >= 0 ? newTotal : 0;
            totalAnomaliesEl.classList.add('scale-105', 'text-rose-400');
            setTimeout(() => { totalAnomaliesEl.classList.remove('scale-105', 'text-rose-400'); }, 1000);
        }

        if (defectDistributionChartInstance && defectClass) {
            const labelMap = {
                'potholes': 'Potholes', 'pothole': 'Potholes',
                'concrete spalling': 'Concrete Spalling', 'crack': 'Cracks', 'cracks': 'Cracks',
                'spalling expose rebar': 'Spalling Expose Rebar', 'mold': 'Mold', 'rust': 'Rust',
                'staining': 'Staining', 'peeling': 'Peeling', 'bridge joint': 'Bridge Joint',
                'road bleeding': 'Road Bleeding', 'vegetation': 'Vegetation'
            };
            const targetLabel = labelMap[defectClass.toLowerCase()];
            const chartIndex = chartLabelsIndex.indexOf(targetLabel);
            if (chartIndex !== -1) {
                let currentVal = defectDistributionChartInstance.data.datasets[0].data[chartIndex] || 0;
                defectDistributionChartInstance.data.datasets[0].data[chartIndex] = Math.max(0, currentVal + addedCount);
                defectDistributionChartInstance.update();
            }
        }
    });
</script>
@endpush