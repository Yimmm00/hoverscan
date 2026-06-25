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
                    <h3 class="text-4xl font-black mt-2 tracking-tight text-rose-500 font-mono">{{ $total_anomalies }}</h3>
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
                    <h3 class="text-4xl font-black mt-2 tracking-tight text-emerald-600 dark:text-emerald-400 font-mono">{{ $avg_confidence }}</h3>
                </div>
                <div class="p-3 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-2xl">
                    <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="p-8 rounded-[3.5rem] border border-slate-200/80 dark:border-white/5 bg-white dark:bg-[#0c0e14] shadow-sm transition-colors duration-200">
        <h4 class="font-black uppercase tracking-tight italic text-lg mb-6 text-slate-900 dark:text-white">Recent YOLO Status Stream</h4>
        <div class="space-y-4">
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