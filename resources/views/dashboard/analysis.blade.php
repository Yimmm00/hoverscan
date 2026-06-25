<div id="view-panel-analysis" class="space-y-8 tab-panel-node hidden animate-fade-in">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-1 p-8 rounded-[2.5rem] border border-slate-200/80 dark:border-white/5 bg-white dark:bg-[#0c0e14] h-fit shadow-sm">
            <h4 class="font-black uppercase tracking-tight italic text-base mb-6 text-slate-900 dark:text-white">Upload New Scan Frame</h4>
            <form id="ai-inference-form" enctype="multipart/form-data" class="space-y-6 text-xs font-bold uppercase text-slate-600 dark:text-slate-400">
                @csrf
                <div>
                    <label class="text-[9px] text-slate-400 dark:text-slate-500 mb-1.5 block font-black">Target Structure Node</label>
                    <select name="bridge_name" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-[#080a0f] text-slate-900 dark:text-white outline-none cursor-pointer">
                        @foreach($bridges as $bridge)
                            <option value="{{ $bridge->name }}">{{ $bridge->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[9px] text-slate-400 dark:text-slate-500 mb-1.5 block font-black">Field Temp (°C)</label>
                        <input type="number" name="temperature" value="31" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-[#080a0f] text-slate-900 dark:text-white outline-none font-mono">
                    </div>
                    <div>
                        <label class="text-[9px] text-slate-400 dark:text-slate-500 mb-1.5 block font-black">Relative Humidity (%)</label>
                        <input type="number" name="humidity" value="78" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-[#080a0f] text-slate-900 dark:text-white outline-none font-mono">
                    </div>
                </div>
                <div>
                    <label class="text-[9px] text-slate-400 dark:text-slate-500 mb-1.5 block font-black">Select Capture Media Asset</label>
                    <input type="file" name="file" accept="image/jpeg,image/png" required class="w-full text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:uppercase file:bg-blue-600/10 file:text-blue-600 dark:file:text-blue-500 hover:file:bg-blue-600/20 file:cursor-pointer">
                </div>
                <button type="submit" id="submit-btn" class="w-full py-3.5 bg-blue-600 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-blue-500 transition-all shadow-xl shadow-blue-600/20 flex items-center justify-center gap-2">
                    <i data-lucide="cpu" class="w-4 h-4"></i> Execute Core Inference
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 p-8 rounded-[2.5rem] border border-slate-200/80 dark:border-white/5 bg-white dark:bg-[#0c0e14] flex flex-col justify-between min-h-[500px] shadow-sm">
            <div class="flex justify-between items-center mb-4 hidden" id="print-action-button-container">
                <div class="flex items-center gap-2">
                    <button type="button" id="manual-draw-btn" onclick="toggleManualDrawMode()" class="px-3.5 py-2 border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-slate-700 dark:text-slate-300 rounded-xl text-[11px] font-black uppercase tracking-wider hover:bg-slate-100 dark:hover:bg-white/10 transition-all flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i> <span id="draw-btn-text">Add Manual Box</span>
                    </button>
                    
                    <select id="manual-class-select" class="hidden px-3 py-2 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-[#080a0f] text-slate-900 dark:text-white font-bold uppercase text-[10px] outline-none cursor-pointer">
                        <option value="potholes">potholes</option>
                        <option value="concrete spalling">concrete spalling</option>
                        <option value="crack">crack</option>
                        <option value="spalling expose rebar">spalling expose rebar</option>
                        <option value="rust">rust</option>
                    </select>
                </div>

                <button type="button" onclick="executeReportPrint()" class="px-4 py-2.5 bg-slate-900 text-white dark:bg-white dark:text-slate-900 rounded-xl text-xs font-black uppercase tracking-widest hover:opacity-90 transition-all flex items-center gap-2 shadow cursor-pointer">
                    <i data-lucide="printer" class="w-4 h-4"></i> Export Inspection Report
                </button>
            </div>
            
            <div class="flex-1 grid grid-cols-1 xl:grid-cols-4 gap-6 w-full">
                <div id="viewport-display-frame" class="xl:col-span-3 flex flex-col items-center justify-center border border-dashed border-slate-200 dark:border-white/10 rounded-3xl relative overflow-hidden p-4 bg-slate-50 dark:bg-black/40 min-h-[380px]">
                    <div id="viewport-placeholder" class="text-center text-slate-400 dark:text-slate-600 flex flex-col items-center gap-4">
                        <i data-lucide="image" class="w-12 h-12 stroke-[1.5]"></i>
                        <p class="text-[10px] uppercase font-black tracking-widest">Awaiting Media Processing Frame Target</p>
                    </div>
                    <div id="image-viewport-wrapper" class="relative max-w-full max-h-[400px] hidden select-none crosshair-canvas-node">
                        <img id="processed-output-img" class="max-w-full max-h-[400px] object-contain rounded-xl block shadow-sm" src="" draggable="false">
                        <div id="bbox-overlay-wrapper" class="absolute inset-0 pointer-events-auto"></div>
                    </div>
                </div>

                <div class="xl:col-span-1 border border-slate-200/60 dark:border-white/5 rounded-3xl p-5 bg-slate-50/50 dark:bg-[#080a0f]/50 h-full overflow-y-auto custom-scrollbar flex flex-col justify-between">
                    <div>
                        <h5 class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-4 border-b border-slate-200 dark:border-white/5 pb-2">Target Defect Classes</h5>
                        <div id="ai-defects-list-tray" class="space-y-2.5 text-xs font-bold uppercase text-slate-400">
                            <p class="italic text-[10px] text-slate-400/70 lowercase py-4 text-center">no targets analyzed</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('view-scripts')
<script>
    // ⚡ FIX: Register a Trusted Types default policy to stop 'TrustedScript' assignment blockages
    if (window.trustedTypes && window.trustedTypes.createPolicy) {
        if (!window.trustedTypes.defaultPolicy) {
            window.trustedTypes.createPolicy('default', {
                createHTML: (string) => string,
                createScript: (string) => string,
                createScriptURL: (string) => string
            });
        }
    }

    let activeDetectionsCollection = [];
    let manualDrawActiveFlag = false;
    let isDrawingNode = false;
    let startX = 0, startY = 0;
    let crosshairBoxEl = null;
    let base64ImageStringCache = null; // Holds reliable base64 image data string

    // 1. AI Inference Handling Pipeline Loop
    document.getElementById('ai-inference-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('submit-btn');
        const placeholder = document.getElementById('viewport-placeholder');
        const imgWrapper = document.getElementById('image-viewport-wrapper');
        const outputImg = document.getElementById('processed-output-img');
        const overlay = document.getElementById('bbox-overlay-wrapper');
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<i data-lucide="refresh-cw" class="w-4 h-4 animate-spin"></i> Running GPU Inference...`;
        lucide.createIcons();
        
        placeholder.classList.remove('hidden');
        imgWrapper.classList.add('hidden');
        overlay.innerHTML = '';
        activeDetectionsCollection = [];
        base64ImageStringCache = null;

        const fileInput = form.querySelector('input[type="file"]');
        if (!fileInput.files || !fileInput.files[0]) return;

        // ⚡ ASYNC BASE64 TRANSLATION LAYER: Ensures printing engine retains accurate pixels
        const reader = new FileReader();
        reader.onload = async function(event) {
            base64ImageStringCache = event.target.result;

            try {
                const response = await fetch('http://127.0.0.1:8001/analyze', { method: 'POST', body: new FormData(form) });
                if (!response.ok) throw new Error("Inference pipeline failure.");
                const result = await response.json();
                
                if (result.all_detections && result.all_detections.length > 0) {
                    placeholder.classList.add('hidden');
                    outputImg.src = base64ImageStringCache; // Render raw base64 straight to viewport screen space
                    imgWrapper.classList.remove('hidden');
                    
                    outputImg.onload = function() {
                        activeDetectionsCollection = result.all_detections.map(d => ({
                            type: d.type,
                            bbox: d.bbox,
                            confidence: d.confidence,
                            isManual: false
                        }));
                        renderInterfaceOverlayMatrix();
                    };
                } else {
                    placeholder.classList.remove('hidden');
                    placeholder.innerHTML = `<i data-lucide="check-circle" class="w-12 h-12 text-emerald-500 mb-2"></i><p class="text-[10px] uppercase font-black text-emerald-600">No flaws structural indices found.</p>`;
                    lucide.createIcons();
                }
            } catch (err) {
                placeholder.classList.remove('hidden');
                placeholder.innerHTML = `<i data-lucide="x-circle" class="w-12 h-12 text-rose-500 mb-2"></i><p class="text-[10px] uppercase font-black text-rose-500">Pipeline Execution Error.</p>`;
                lucide.createIcons();
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = `<i data-lucide="cpu" class="w-4 h-4"></i> Execute Core Inference`;
                lucide.createIcons();
            }
        };
        reader.readAsDataURL(fileInput.files[0]);
    });

    // 2. MASTER RENDERING ENGINE FOR THE SCREEN LABELS & PRINT MIRRORS
    function renderInterfaceOverlayMatrix() {
        const overlay = document.getElementById('bbox-overlay-wrapper');
        const printOverlay = document.getElementById('print-bbox-overlay');
        const listTray = document.getElementById('ai-defects-list-tray');
        const evidenceContainer = document.getElementById('print-evidence-logs-container');
        
        overlay.innerHTML = '';
        printOverlay.innerHTML = '';
        listTray.innerHTML = '';
        evidenceContainer.innerHTML = ''; // Wipe separated crop elements layer cleanly
        
        document.getElementById('print-action-button-container').classList.remove('hidden');
        document.getElementById('print-output-img').src = base64ImageStringCache; // Secure print image payload reference

        activeDetectionsCollection.forEach((det, idx) => {
            const [x1, y1, x2, y2] = det.bbox;
            
            // Purple (#7c3aed) for AI detections, Amber (#f59e0b) for manual annotations
            const colorClass = det.isManual ? '#f59e0b' : '#7c3aed';
            const bgAlpha = det.isManual ? 'rgba(245, 158, 11, 0.05)' : 'rgba(124, 58, 237, 0.05)';

            // A. ON-SCREEN INTERACTIVE BOUNDING BOX CONTAINER
            const box = document.createElement('div');
            box.className = 'absolute border-2 rounded select-none transition-shadow';
            box.style.left = (x1 * 100).toFixed(2) + '%';
            box.style.top = (y1 * 100).toFixed(2) + '%';
            box.style.width = ((x2 - x1) * 100).toFixed(2) + '%';
            box.style.height = ((y2 - y1) * 100).toFixed(2) + '%';
            box.style.borderColor = colorClass;
            box.style.backgroundColor = bgAlpha;
            box.style.touchAction = 'none';
            box.style.pointerEvents = 'auto';

            const labelWrapper = document.createElement('div');
            labelWrapper.className = 'absolute text-white text-[8px] font-black uppercase px-1.5 py-0.5 rounded shadow whitespace-nowrap flex items-center gap-1 select-none';
            labelWrapper.style.backgroundColor = colorClass;
            labelWrapper.style.top = '-18px';
            labelWrapper.style.left = '-2px';
            labelWrapper.style.zIndex = '50';
            
            labelWrapper.innerHTML = `<span>${det.type} • ${det.confidence ? Math.round(det.confidence * 100) + '%' : 'MANUAL'}</span>`;
            
            const closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.className = 'ml-1 hover:text-black font-mono font-bold text-[10px] px-0.5 transition-colors cursor-pointer border-0 bg-transparent text-white';
            closeBtn.innerHTML = '×';
            
            closeBtn.addEventListener('mousedown', (e) => e.stopPropagation());
            closeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                removeAnnotationNode(idx);
            });
            labelWrapper.appendChild(closeBtn);
            box.appendChild(labelWrapper);

            if (det.isManual) {
                box.classList.add('cursor-move');
                const resizeHandle = document.createElement('div');
                resizeHandle.className = 'absolute bottom-0 right-0 w-3 h-3 rounded-tl-md cursor-se-resize z-50';
                resizeHandle.style.backgroundColor = colorClass;
                resizeHandle.style.pointerEvents = 'auto';
                box.appendChild(resizeHandle);

                setupBoxInteractionHandlers(box, resizeHandle, idx);
            }
            overlay.appendChild(box);

            // B. PRINT MIRROR CANVAS RENDERER
            const printBox = document.createElement('div');
            printBox.className = 'absolute border-2 rounded';
            printBox.style.left = (x1 * 100).toFixed(2) + '%';
            printBox.style.top = (y1 * 100).toFixed(2) + '%';
            printBox.style.width = ((x2 - x1) * 100).toFixed(2) + '%';
            printBox.style.height = ((y2 - y1) * 100).toFixed(2) + '%';
            printBox.style.borderColor = colorClass;
            printBox.style.backgroundColor = 'transparent'; // Keeps interior crystal clear
            printOverlay.appendChild(printBox);

            // C. ⚡ FIXED: ROBUST STABLE VIEWPORT OFFSETS WITHOUT SCALING DISTORTIONS
            const evidenceCard = document.createElement('div');
            evidenceCard.className = 'border border-slate-200 bg-white rounded-xl overflow-hidden shadow-sm flex flex-col justify-between p-3 page-break-inside-avoid';
            
            // Calculate exact width and height dimensions of the bounding box slice
            const boxW = x2 - x1;
            const boxH = y2 - y1;

            // Prevent dividing by zero if an invalid box is generated
            const pctW = boxW > 0 ? (100 / boxW).toFixed(2) : 100;
            const pctH = boxH > 0 ? (100 / boxH).toFixed(2) : 100;
            const pctX = boxW > 0 ? ((x1 / (1 - boxW)) * 100).toFixed(2) : 0;
            const pctY = boxH > 0 ? ((y1 / (1 - boxH)) * 100).toFixed(2) : 0;

            evidenceCard.innerHTML = `
                <div class="relative bg-slate-900 rounded-lg aspect-video mb-3 overflow-hidden border border-slate-100">
                    <div class="absolute inset-0 w-full h-full" style="pointer-events: none;">
                        <img src="${base64ImageStringCache}" 
                             class="absolute max-w-none" 
                             style="width: ${pctW}%; height: ${pctH}%; left: -${(x1 * pctW).toFixed(2)}%; top: -${(y1 * pctH).toFixed(2)}%; object-fit: contain;">
                    </div>
                    <div class="absolute inset-0 border-[3px]" style="border-color: ${colorClass}; z-index: 10;"></div>
                </div>
                <div class="space-y-1 text-[10px] font-bold text-slate-500 uppercase">
                    <div class="flex justify-between border-b pb-1">
                        <span class="font-black text-slate-900 font-mono">#${idx + 1} - ${det.type}</span>
                        <span class="px-2 py-0.5 rounded text-[8px] text-white" style="background-color: ${colorClass}; font-mono">${det.isManual ? 'MANUAL' : 'AI LOG'}</span>
                    </div>
                    <p class="pt-1">Pipeline Classification: <strong class="text-slate-800">${det.type}</strong></p>
                    <p>Metric Confidence Value: <strong class="text-slate-800">${det.confidence ? Math.round(det.confidence * 100) + '%' : '100% CONFIRMED'}</strong></p>
                </div>
            `;
            evidenceContainer.appendChild(evidenceCard);

            // D. METADATA SIDEBAR TRAY LIST INDEX ITEM
            const itemCard = document.createElement('div');
            itemCard.className = 'p-3 rounded-xl border border-slate-200 dark:border-white/5 bg-white dark:bg-white/5 flex items-center justify-between shadow-sm';
            itemCard.innerHTML = `
                <div class="space-y-0.5 select-none">
                    <p class="font-black text-[11px] text-slate-800 dark:text-white tracking-tight">${det.type}</p>
                    <p class="font-mono text-[9px] text-slate-400">${det.isManual ? 'MANUAL INJECTION' : 'CONF: ' + Math.round(det.confidence * 100) + '%'}</p>
                </div>
            `;
            
            const trayDeleteBtn = document.createElement('button');
            trayDeleteBtn.type = 'button';
            trayDeleteBtn.className = 'text-slate-400 hover:text-rose-500 transition-colors p-1 text-xs cursor-pointer bg-transparent border-0';
            trayDeleteBtn.innerHTML = '✕';
            trayDeleteBtn.addEventListener('click', (e) => {
                e.preventDefault();
                removeAnnotationNode(idx);
            });
            itemCard.appendChild(trayDeleteBtn);
            listTray.appendChild(itemCard);
        });

        // Update global printing data counts targets tracking values
        document.getElementById('print-bridge-name').innerText = document.querySelector('select[name="bridge_name"]').value.toUpperCase();
        document.getElementById('print-temp-val').innerText = document.querySelector('input[name="temperature"]').value;
        document.getElementById('print-humidity-val').innerText = document.querySelector('input[name="humidity"]').value;
        document.getElementById('print-total-count').innerText = activeDetectionsCollection.length;
        document.getElementById('print-highest-severity').innerText = activeDetectionsCollection.some(d => d.type === 'spalling expose rebar') ? 'CRITICAL REBAR EXPOSURE' : 'MEDIUM / HIGH';
        if (document.getElementById('print-dataset-hash').innerText === 'N/A') {
            document.getElementById('print-dataset-hash').innerText = 'AST-' + Math.random().toString(16).substr(2, 8).toUpperCase();
        }
    }

    // 3. REMOVE ANNOTATION MATRIX INDEX FUNCTION
    window.removeAnnotationNode = function(index) {
        activeDetectionsCollection.splice(index, 1);
        renderInterfaceOverlayMatrix();
    };

    // 4. TOGGLE MANUAL INJECTION PLOTTING CANVAS MODE
    window.toggleManualDrawMode = function() {
        manualDrawActiveFlag = !manualDrawActiveFlag;
        const btn = document.getElementById('manual-draw-btn');
        const selector = document.getElementById('manual-class-select');
        const text = document.getElementById('draw-btn-text');
        
        if (manualDrawActiveFlag) {
            btn.classList.replace('bg-slate-50', 'bg-amber-500');
            btn.classList.add('text-white');
            selector.classList.remove('hidden');
            text.innerText = 'Exit Drawing Mode';
        } else {
            btn.classList.replace('bg-amber-500', 'bg-slate-50');
            btn.classList.remove('text-white');
            selector.classList.add('hidden');
            text.innerText = 'Add Manual Box';
        }
        renderInterfaceOverlayMatrix(); 
    };

    // 5. CLICK-TO-DRAG MOUSE TRACKING OVER CANVAS WRAPPER OBJECT
    const canvasOverlay = document.getElementById('bbox-overlay-wrapper');

    canvasOverlay.addEventListener('mousedown', function(e) {
        if (!manualDrawActiveFlag || e.target !== canvasOverlay) return;
        e.preventDefault();
        isDrawingNode = true;
        
        const rect = canvasOverlay.getBoundingClientRect();
        startX = (e.clientX - rect.left) / rect.width;
        startY = (e.clientY - rect.top) / rect.height;

        crosshairBoxEl = document.createElement('div');
        crosshairBoxEl.className = 'absolute border-2 border-dashed border-amber-500 bg-amber-500/10 pointer-events-none';
        crosshairBoxEl.style.left = (startX * 100) + '%';
        crosshairBoxEl.style.top = (startY * 100) + '%';
        canvasOverlay.appendChild(crosshairBoxEl);
    });

    document.addEventListener('mousemove', function(e) {
        if (!isDrawingNode || !crosshairBoxEl) return;
        
        const rect = canvasOverlay.getBoundingClientRect();
        const currentX = (e.clientX - rect.left) / rect.width;
        const currentY = (e.clientY - rect.top) / rect.height;

        const x1 = Math.max(0, Math.min(1, Math.min(startX, currentX)));
        const y1 = Math.max(0, Math.min(1, Math.min(startY, currentY)));
        const x2 = Math.max(0, Math.min(1, Math.max(startX, currentX)));
        const y2 = Math.max(0, Math.min(1, Math.max(startY, currentY)));

        crosshairBoxEl.style.left = (x1 * 100) + '%';
        crosshairBoxEl.style.top = (y1 * 100) + '%';
        crosshairBoxEl.style.width = ((x2 - x1) * 100) + '%';
        crosshairBoxEl.style.height = ((y2 - y1) * 100) + '%';
    });

    document.addEventListener('mouseup', function(e) {
        if (!isDrawingNode) return;
        isDrawingNode = false;
        
        const rect = canvasOverlay.getBoundingClientRect();
        const currentX = (e.clientX - rect.left) / rect.width;
        const currentY = (e.clientY - rect.top) / rect.height;

        const x1 = Math.max(0, Math.min(1, Math.min(startX, currentX)));
        const y1 = Math.max(0, Math.min(1, Math.min(startY, currentY)));
        const x2 = Math.max(0, Math.min(1, Math.max(startX, currentX)));
        const y2 = Math.max(0, Math.min(1, Math.max(startY, currentY)));

        if ((x2 - x1) > 0.01 && (y2 - y1) > 0.01) {
            const targetClass = document.getElementById('manual-class-select').value;
            activeDetectionsCollection.push({
                type: targetClass,
                bbox: [x1, y1, x2, y2],
                confidence: null,
                isManual: true
            });
        }
        
        if (crosshairBoxEl) {
            crosshairBoxEl.remove();
            crosshairBoxEl = null;
        }
        renderInterfaceOverlayMatrix();
    });

    // 6. DRAG AND RESIZE REAL-TIME TRANSACTION ENGINE
    function setupBoxInteractionHandlers(boxEl, handleEl, index) {
        const overlay = document.getElementById('bbox-overlay-wrapper');
        
        boxEl.addEventListener('mousedown', function(e) {
            if (e.target.closest('button') || e.target === handleEl) return; 
            
            e.preventDefault();
            e.stopPropagation(); 
            
            const rect = overlay.getBoundingClientRect();
            let currentDet = activeDetectionsCollection[index];
            if (!currentDet) return;

            let boxWidth = currentDet.bbox[2] - currentDet.bbox[0];
            let boxHeight = currentDet.bbox[3] - currentDet.bbox[1];

            let clickOffsetX = ((e.clientX - rect.left) / rect.width) - currentDet.bbox[0];
            let clickOffsetY = ((e.clientY - rect.top) / rect.height) - currentDet.bbox[1];

            function onMouseMove(moveEvent) {
                let currentMouseX = (moveEvent.clientX - rect.left) / rect.width;
                let currentMouseY = (moveEvent.clientY - rect.top) / rect.height;

                let newX1 = Math.max(0, Math.min(1 - boxWidth, currentMouseX - clickOffsetX));
                let newY1 = Math.max(0, Math.min(1 - boxHeight, currentMouseY - clickOffsetY));

                boxEl.style.left = (newX1 * 100).toFixed(2) + '%';
                boxEl.style.top = (newY1 * 100).toFixed(2) + '%';

                activeDetectionsCollection[index].bbox = [
                    newX1, 
                    newY1, 
                    newX1 + boxWidth, 
                    newY1 + boxHeight
                ];
            }

            function onMouseUp() {
                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', onMouseUp);
                renderInterfaceOverlayMatrix(); 
            }

            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);
        });

        handleEl.addEventListener('mousedown', function(e) {
            e.preventDefault();
            e.stopPropagation(); 

            const rect = overlay.getBoundingClientRect();
            let currentDet = activeDetectionsCollection[index];
            if (!currentDet) return;

            function onMouseMove(moveEvent) {
                let currentMouseX = (moveEvent.clientX - rect.left) / rect.width;
                let currentMouseY = (moveEvent.clientY - rect.top) / rect.height;

                let newX2 = Math.max(currentDet.bbox[0] + 0.02, Math.min(1, currentMouseX));
                let newY2 = Math.max(currentDet.bbox[1] + 0.02, Math.min(1, currentMouseY));

                boxEl.style.width = ((newX2 - currentDet.bbox[0]) * 100).toFixed(2) + '%';
                boxEl.style.height = ((newY2 - currentDet.bbox[1]) * 100).toFixed(2) + '%';

                activeDetectionsCollection[index].bbox[2] = newX2;
                activeDetectionsCollection[index].bbox[3] = newY2;
            }

            function onMouseUp() {
                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', onMouseUp);
                renderInterfaceOverlayMatrix();
            }

            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);
        });
    }

    // 7. WINDOW EVALUATION PRINTER CORE ENGINE (WITH DYNAMIC BUFFER WAITING HOOKS)
    function executeReportPrint() {
        const printImg = document.getElementById('print-output-img');
        
        // Helper inline wrapper to fire the native dialog safely
        const triggerBrowserPrint = () => {
            // A tiny timeout gives the browser's layout engine one final tick to render vectors cleanly
            setTimeout(() => {
                window.print();
            }, 100);
        };

        // If the primary preview canvas image is already fully decoded, print immediately!
        if (printImg.complete) {
            triggerBrowserPrint();
        } else {
            // Otherwise, attach a one-time load listener to catch it the moment it finishes buffer streaming
            printImg.onload = function() {
                triggerBrowserPrint();
            };
        }
    }
</script>
@endpush