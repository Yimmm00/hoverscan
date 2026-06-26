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
            'bridge joint' => ['severity' => 'Medium', 'desc' => 'Check for debris and expansion spacing.'],
            'road bleeding' => ['severity' => 'High', 'desc' => 'Excess asphalt binder migrating to the surface, reducing skid resistance.'],
            'vegetation' => ['severity' => 'Medium', 'desc' => 'Organic growth causing crack expansion and moisture retention.'],
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('defect-gallery-modal');
    const modalTitle = document.getElementById('modal-title-class-name');
    const galleryGrid = document.getElementById('modal-gallery-grid-content');
    const closeBtn = document.getElementById('close-gallery-btn');

    // Attach click listeners to all classification cards
    document.querySelectorAll('.defect-trigger-card').forEach(card => {
        card.addEventListener('click', async () => {
            const defectClass = card.getAttribute('data-defect');
            if (!defectClass) return;

            // Open modal structure immediately with a loading skeleton spinner
            modalTitle.innerText = defectClass.toUpperCase();
            galleryGrid.innerHTML = `
                <div class="col-span-full flex flex-col items-center justify-center py-20 text-slate-400">
                    <i data-lucide="refresh-cw" class="w-10 h-10 animate-spin text-blue-500 mb-3"></i>
                    <p class="text-xs uppercase font-black tracking-widest">Streaming Database Matrices...</p>
                </div>
            `;
            if (typeof lucide !== 'undefined') lucide.createIcons();
            modal.classList.remove('hidden');

            try {
                const response = await fetch(`/api/defects/gallery/${encodeURIComponent(defectClass)}`);
                const result = await response.json();

                if (!response.ok || !result.success) throw new Error("Failed fetching stream.");

                if (result.data.length === 0) {
                    galleryGrid.innerHTML = `
                        <div class="col-span-full text-center py-20 text-slate-400 font-bold uppercase text-[10px] tracking-widest">
                            No telemetry frames captured for this classification node yet.
                        </div>
                    `;
                    return;
                }

                // Render dynamic responsive grid layout rows
                galleryGrid.className = "grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6";
                galleryGrid.innerHTML = result.data.map(record => {
                    const isManual = record.confidence_score === null || record.confidence_score === 0;
                    const confPercentage = isManual ? 'MANUAL' : Math.round(record.confidence_score * 100) + '%';
                    
                    return `
                        <div class="border border-slate-100 dark:border-white/5 rounded-3xl overflow-hidden bg-white dark:bg-[#12141c] shadow-sm flex flex-col">
                            <div class="h-44 bg-slate-900 relative overflow-hidden flex items-center justify-center">
                                <img src="${record.image_path}" class="w-full h-full object-cover" alt="${record.defect_class}">
                                <span class="absolute top-4 right-4 px-2 py-1 rounded-md text-[8px] font-black uppercase tracking-wider ${
                                    record.severity === 'Critical' ? 'bg-rose-500 text-white' : 'bg-amber-500 text-white'
                                }">${record.severity}</span>
                            </div>
                            <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                                <div>
                                    <h5 class="font-black text-xs uppercase tracking-tight text-slate-900 dark:text-white italic">${record.bridge_name}</h5>
                                    <p class="text-[9px] font-mono text-slate-400 mt-1">LOGGED: ${record.created_at}</p>
                                </div>
                                <div class="pt-3 border-t border-slate-100 dark:border-white/5 flex justify-between items-center text-[10px] font-black uppercase tracking-wider">
                                    <span class="text-slate-400">Metrics Index:</span>
                                    <span class="text-blue-500 font-mono">${confPercentage}</span>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');

            } catch (err) {
                galleryGrid.innerHTML = `
                    <div class="col-span-full text-center py-20 text-rose-500 font-black uppercase text-xs">
                        Error reading image telemetry sequence.
                    </div>
                `;
            }
        });
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', () => modal.classList.add('hidden'));
    }
});
</script>
