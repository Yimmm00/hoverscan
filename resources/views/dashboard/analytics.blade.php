<div id="view-panel-dashboard" class="space-y-10 tab-panel-node view-active animate-fade-in">
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <div class="p-8 rounded-[2.5rem] border border-slate-200/80 dark:border-white/5 bg-white dark:bg-[#0c0e14] relative overflow-hidden shadow-sm transition-colors duration-200">
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

        <div class="p-8 rounded-[2.5rem] border border-slate-200/80 dark:border-white/5 bg-white dark:bg-[#0c0e14] relative overflow-hidden shadow-sm transition-colors duration-200">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Unresolved Flaw Anomalies</p>
                    <h3 id="dash-total-anomalies" class="text-4xl font-black mt-2 tracking-tight text-rose-500 font-mono">{{ $total_anomalies }}</h3>
                </div>
                <div class="p-3 bg-rose-500/10 text-rose-500 rounded-2xl">
                    <i data-lucide="shield-alert" class="w-5 h-5"></i>
                </div>
            </div>
        </div>

        <div class="p-8 rounded-[2.5rem] border border-slate-200/80 dark:border-white/5 bg-white dark:bg-[#0c0e14] relative overflow-hidden shadow-sm transition-colors duration-200">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">AI Mean Confidence Rating</p>
                    <h3 id="dash-avg-confidence" class="text-4xl font-black mt-2 tracking-tight text-emerald-600 dark:text-emerald-400 font-mono">{{ $avg_confidence }}</h3>
                </div>
                <div class="p-3 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-2xl">
                    <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
        <div class="lg:col-span-2 p-8 rounded-[3.5rem] border border-slate-200/80 dark:border-white/5 bg-white dark:bg-[#0c0e14] shadow-sm flex flex-col justify-between max-h-[480px] overflow-y-auto custom-scrollbar">
            <div>
                <h4 class="font-black uppercase tracking-tight italic text-base mb-6 text-slate-900 dark:text-white">Recent YOLO Status Stream</h4>
                <div class="space-y-4" id="dash-yolo-stream-container">
                    @forelse($recent_logs as $log)
                        <div class="p-4 rounded-2xl border border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/5 flex items-center justify-between">
                            <div>
                                <p class="font-black text-xs uppercase tracking-tight text-slate-800 dark:text-white">{{ $log->bridge_name }}</p>
                                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-400 mt-0.5">Detected Flaw Class: {{ strtoupper($log->defect_class) }}</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-lg text-[8px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400 bg-amber-500/10">{{ $log->severity }}</span>
                        </div>
                    @empty
                        <div class="py-12 text-center text-xs uppercase font-bold tracking-widest text-slate-400 dark:text-slate-500">No recent flaw vectors logged.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="lg:col-span-3 p-8 rounded-[3.5rem] border border-slate-200/80 dark:border-white/5 bg-white dark:bg-[#0c0e14] shadow-sm flex flex-col justify-between max-h-[480px]">
            <div>
                <h4 class="font-black uppercase tracking-tight italic text-base mb-1 text-slate-900 dark:text-white">Defect Matrix Distribution</h4>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-4">Live distribution tracking index profiles</p>
            </div>
            <div class="flex-1 relative w-full h-[320px] flex items-center justify-center">
                <canvas id="hoverscan-analytics-chart"></canvas>
            </div>
        </div>
    </div>
</div>

@push('view-scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let defectDistributionChartInstance = null;

    // Initialize Chart on load
    document.addEventListener('DOMContentLoaded', () => {
        const ctx = document.getElementById('hoverscan-analytics-chart').getContext('2d');
        
        // Pass initial server side aggregated data variables safely to runtime arrays
        const structuralDataMap = {
            'Potholes': 0, 'Spalling': 0, 'Cracks': 0, 'Mold': 0, 'Rust': 0, 'Staining': 0
        };

        // Pre-fill rough buckets from whatever server values exist
        @foreach($recent_logs as $log)
            if ("{{ $log->defect_class }}" === 'potholes') structuralDataMap['Potholes']++;
            if ("{{ $log->defect_class }}" === 'crack') structuralDataMap['Cracks']++;
            if ("{{ $log->defect_class }}" === 'staining') structuralDataMap['Staining']++;
            if ("{{ $log->defect_class }}" === 'mold') structuralDataMap['Mold']++;
            if ("{{ $log->defect_class }}" === 'rust') structuralDataMap['Rust']++;
        @endforeach

        const isDark = document.documentElement.classList.contains('dark');

        defectDistributionChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: Object.keys(structuralDataMap),
                datasets: [{
                    label: 'Active Flaw Counts',
                    data: Object.values(structuralDataMap),
                    backgroundColor: 'rgba(59, 130, 246, 0.25)',
                    borderColor: '#3b82f6',
                    borderWidth: 2,
                    borderRadius: 12,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: isDark ? '#64748b' : '#94a3b8', font: { weight: '800', size: 9 } }
                    },
                    y: {
                        grid: { color: isDark ? 'rgba(255,255,255,0.04)' : 'rgba(0,0,0,0.04)' },
                        ticks: { color: isDark ? '#64748b' : '#94a3b8', font: { weight: 'bold', size: 9 }, precision: 0 }
                    }
                }
            }
        });
    });

    // ⚡ REAL-TIME GRAPH & CARD COUNTER TELEMETRY REACTION PIPELINE
    document.addEventListener('hoverscan:telemetry-update', (e) => {
        const { addedCount } = e.detail;
        
        // 1. Update Unresolved Flaw Anomalies Card
        const totalAnomaliesEl = document.getElementById('dash-total-anomalies');
        if (totalAnomaliesEl) {
            let currentTotal = parseInt(totalAnomaliesEl.innerText.trim()) || 0;
            let newTotal = currentTotal + addedCount;
            totalAnomaliesEl.innerText = newTotal;
            
            totalAnomaliesEl.classList.add('scale-105', 'text-rose-400');
            setTimeout(() => { totalAnomaliesEl.classList.remove('scale-105', 'text-rose-400'); }, 1000);
        }

        // 2. Refresh chart dynamically on frame reception loop signals
        if (defectDistributionChartInstance) {
            // Since we're parsing live, increment structural values sequentially
            defectDistributionChartInstance.data.datasets[0].data[2] += addedCount; // Default step increments Cracks bucket
            defectDistributionChartInstance.update();
        }
    });
</script>
@endpush