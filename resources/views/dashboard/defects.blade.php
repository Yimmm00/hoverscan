<div id="view-panel-defects" class="space-y-10 tab-panel-node hidden animate-fade-in">
    <div>
        <h3 class="text-xl font-black uppercase italic tracking-tight text-slate-900 dark:text-white">Defect Classification Library</h3>
        <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-1">Explore structural damage categories matching your YOLOv8 classes</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
        @php
            $classes = [
                'potholes' => ['severity' => 'High', 'desc' => 'Hazardous surface depressions.'],
                'concrete spalling' => ['severity' => 'High', 'desc' => 'Early-stage concrete degradation and flaking.'],
                'crack' => ['severity' => 'High', 'desc' => 'Monitor for width and structural depth.'],
                'spalling expose rebar' => ['severity' => 'Critical', 'desc' => 'Structural steel exposure.'],
                'mold' => ['severity' => 'Low', 'desc' => 'Biological growth on damp surfaces.'],
                'rust' => ['severity' => 'Medium', 'desc' => 'Oxidation of steel components.'],
                'staining' => ['severity' => 'Low', 'desc' => 'Discoloration from water seepage.'],
                'peeling' => ['severity' => 'Low', 'desc' => 'Surface coating degradation.'],
                'bridge joint' => ['severity' => 'Medium', 'desc' => 'Check for debris and expansion spacing.']
            ];
        @endphp

        @foreach($classes as $name => $meta)
            <div data-defect="{{ $name }}" class="defect-trigger-card p-8 rounded-[2.5rem] border border-slate-200/80 dark:border-white/5 bg-white dark:bg-[#0c0e14] relative overflow-hidden group transition-all duration-300 cursor-pointer hover:border-blue-500/30 shadow-sm active:scale-[0.99]">
                <div class="flex justify-between items-start mb-6">
                    <span class="text-[9px] font-mono font-black text-blue-600 dark:text-blue-400 px-3 py-1 bg-blue-500/10 rounded-full">YOLO CLASS</span>
                    <span class="text-[8px] font-black uppercase tracking-widest px-2 py-1 rounded-md border {{ $meta['severity'] === 'Critical' ? 'border-rose-500/30 text-rose-500 bg-rose-500/5' : 'border-blue-500/30 text-blue-600 dark:text-blue-500 bg-blue-500/5' }}">
                        {{ $meta['severity'] }}
                    </span>
                </div>
                <h4 class="text-lg font-black uppercase italic tracking-tight mb-2 text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ $name }}</h4>
                <p class="text-[10px] text-slate-400 dark:text-slate-500 font-bold leading-relaxed max-w-[90%] uppercase">{{ $meta['desc'] }}</p>
            </div>
        @endforeach
    </div>

    <div id="defect-gallery-modal" class="fixed inset-0 bg-black/40 dark:bg-black/80 backdrop-blur-md z-[999] flex items-center justify-center p-6 hidden animate-fade-in">
        <div class="w-full max-w-5xl h-[85vh] rounded-[3.5rem] flex flex-col overflow-hidden border border-slate-200 dark:border-white/10 bg-white dark:bg-[#0c0e14] shadow-2xl">
            <div class="p-8 border-b border-slate-100 dark:border-white/5 bg-slate-50/50 dark:bg-black/20 flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-black uppercase italic tracking-tight flex items-center gap-2 text-slate-900 dark:text-white">
                        <i data-lucide="image" class="text-blue-600 dark:text-blue-500 w-5 h-5"></i> <span id="modal-title-class-name">Defect</span> Image Explorer
                    </h3>
                    <p class="text-[10px] font-black uppercase text-slate-400 dark:text-slate-500 tracking-wider mt-1">Live Database Inspection Stream Analysis</p>
                </div>
                <button type="button" id="close-gallery-btn" class="p-3 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-white/5 dark:hover:bg-rose-500/20 text-slate-500 dark:text-slate-400 dark:hover:text-rose-400 transition-all cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-8 custom-scrollbar bg-slate-50/30 dark:bg-[#080a0f]/30" id="modal-gallery-grid-content"></div>
        </div>
    </div>
</div>