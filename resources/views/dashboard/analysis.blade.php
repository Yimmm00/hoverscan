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
                        <option value="mold">mold</option>
                        <option value="rust">rust</option>
                        <option value="staining">staining</option>
                        <option value="peeling">peeling</option>
                        <option value="bridge joint">bridge joint</option>
                        <option value="road bleeding">road bleeding</option>
                        <option value="vegetation">vegetation</option>
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

                <div class="xl:col-span-1 border border-slate-200/60 dark:border-white/5 rounded-3xl p-5 bg-slate-50/50 dark:bg-[#080a0f]/50 h-[450px] flex flex-col shadow-sm">
                    <div>
                        <h5 class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-4 border-b border-slate-200 dark:border-white/5 pb-2">
                            Target Defect Classes
                        </h5>
                        
                        <div id="ai-defects-list-tray" class="space-y-2.5 text-xs font-bold uppercase text-slate-400 max-h-[370px] overflow-y-auto pr-1.5 custom-scrollbar">
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
    // ⚡ BYPASS GLOBAL TRUSTED TYPES DIRECTIVES ON SYSTEM RUNTIMES
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
    let startX = 0;
    let startY = 0;
    let crosshairBoxEl = null;
    let base64ImageStringCache = null;

    // 1. AI Inference Handling Pipeline Loop with Integrated GPU Cache Engine
    document.getElementById('ai-inference-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('submit-btn');
        const placeholder = document.getElementById('viewport-placeholder');
        const imgWrapper = document.getElementById('image-viewport-wrapper');
        const outputImg = document.getElementById('processed-output-img');
        const overlay = document.getElementById('bbox-overlay-wrapper');
        
        const fileInput = form.querySelector('input[type="file"]');
        if (!fileInput.files || !fileInput.files[0]) return;

        const selectedBridge = form.querySelector('select[name="bridge_name"]').value;
        const targetFile = fileInput.files[0];

        // ⚡ NEW CACHE CORE: Generate a unique fingerprint for this specific frame context
        const cacheFingerprintKey = `hoverscan_cache_${btoa(selectedBridge)}_${targetFile.name}_${targetFile.size}`;

        // Check if this image vector has already been evaluated during this session
        const cachedTelemetryData = sessionStorage.getItem(cacheFingerprintKey);
        
        if (cachedTelemetryData) {
            console.log("⚡ [Cache Hit] Serving frame coordinates instantly from session memory matrix.");
            const cachedResult = JSON.parse(cachedTelemetryData);
            
            // Render interface frame from cache parameters directly without touching port 8001
            placeholder.classList.add('hidden');
            outputImg.src = cachedResult.base64Img;
            imgWrapper.classList.remove('hidden');

            outputImg.onload = function() {
                activeDetectionsCollection = cachedResult.detections;
                base64ImageStringCache = cachedResult.base64Img;
                renderInterfaceOverlayMatrix();
            };
            return;
        }

        // ⚡ [Cache Miss] Proceed with standard deep-learning pipeline processing
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<i data-lucide="refresh-cw" class="w-4 h-4 animate-spin"></i> Running GPU Inference...`;
        if (typeof lucide !== 'undefined') lucide.createIcons();
        
        placeholder.classList.remove('hidden');
        imgWrapper.classList.add('hidden');
        overlay.innerHTML = '';
        activeDetectionsCollection = [];
        base64ImageStringCache = null;

        const reader = new FileReader();
        reader.onload = async function(event) {
            base64ImageStringCache = event.target.result;

            try {
                const response = await fetch('http://127.0.0.1:8001/analyze', { method: 'POST', body: new FormData(form) });
                if (!response.ok) throw new Error("Inference pipeline failure.");
                const result = await response.json();
                
                placeholder.classList.add('hidden');
                outputImg.src = base64ImageStringCache; 
                imgWrapper.classList.remove('hidden');

            outputImg.onload = async function() {
                if (result.all_detections && result.all_detections.length > 0) {
                    activeDetectionsCollection = result.all_detections.map(d => ({
                        type: d.type,
                        bbox: d.bbox,
                        confidence: d.confidence,
                        isManual: false
                    }));
                } else {
                    activeDetectionsCollection = [];
                }
                renderInterfaceOverlayMatrix();

                // ⚡ COMMIT TO CACHE
                const telemetryCachePayload = {
                    detections: activeDetectionsCollection,
                    base64Img: base64ImageStringCache
                };
                sessionStorage.setItem(cacheFingerprintKey, JSON.stringify(telemetryCachePayload));

                // ⚡ NEW: PERSIST AI DETECTIONS TO DATABASE
                const formElement = document.getElementById('ai-inference-form');
                const selectedBridge = formElement.querySelector('select[name="bridge_name"]').value;
                const tempVal = formElement.querySelector('input[name="temperature"]').value;
                const humidVal = formElement.querySelector('input[name="humidity"]').value;

                for (const det of activeDetectionsCollection) {
                    let mappedSeverity = 'Medium';
                    const lowerType = det.type.toLowerCase().trim();

                    if (['potholes', 'pothole', 'crack', 'concrete spalling', 'road bleeding'].includes(lowerType)) {
                        mappedSeverity = 'High';
                    } else if (lowerType === 'spalling expose rebar') {
                        mappedSeverity = 'Critical';
                    } else if (['mold', 'staining', 'peeling', 'rust', 'vegetation', 'bridge joint'].includes(lowerType)) {
                        mappedSeverity = ['mold', 'staining', 'peeling'].includes(lowerType) ? 'Low' : 'Medium';
                    }

                    // Live UI update
                    document.dispatchEvent(new CustomEvent('hoverscan:telemetry-update', {
                        detail: { bridgeName: selectedBridge, addedCount: 1, defectClass: lowerType, severity: mappedSeverity }
                    }));

                    // Send payload to backend database
                    try {
                        await fetch('/web-api/defects/save-annotation', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                            },
                            body: JSON.stringify({
                                bridge_name: selectedBridge,
                                defect_class: lowerType,
                                severity: mappedSeverity,
                                temperature: tempVal,
                                humidity: humidVal,
                                image_path: base64ImageStringCache,
                                bbox_coordinates: det.bbox
                            })
                        });
                    } catch (err) {
                        console.error("AI auto-save sync failure:", err);
                    }
                }
            };

            } catch (err) {
                placeholder.classList.remove('hidden');
                placeholder.innerHTML = `<i data-lucide="x-circle" class="w-12 h-12 text-rose-500 mb-2"></i><p class="text-[10px] uppercase font-black text-rose-500">Pipeline Execution Error.</p>`;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = `<i data-lucide="cpu" class="w-4 h-4"></i> Execute Core Inference`;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        };
        reader.readAsDataURL(targetFile);
    });

    // 2. MASTER RENDERING ENGINE FOR THE SCREEN LABELS & PRINT MIRRORS
    function renderInterfaceOverlayMatrix() {
        const overlay = document.getElementById('bbox-overlay-wrapper');
        const printOverlay = document.getElementById('print-bbox-overlay');
        const listTray = document.getElementById('ai-defects-list-tray');
        const evidenceContainer = document.getElementById('print-evidence-logs-container');
        
        if (overlay) overlay.innerHTML = '';
        if (listTray) listTray.innerHTML = '';
        if (printOverlay) printOverlay.innerHTML = '';
        if (evidenceContainer) evidenceContainer.innerHTML = ''; 
        
        const actionButtonContainer = document.getElementById('print-action-button-container');
        if (actionButtonContainer) actionButtonContainer.classList.remove('hidden');

        const printOutputImg = document.getElementById('print-output-img');
        if (printOutputImg && base64ImageStringCache) printOutputImg.src = base64ImageStringCache;

        activeDetectionsCollection.forEach((det, idx) => {
            const [x1, y1, x2, y2] = det.bbox;
            const colorClass = det.isManual ? '#f59e0b' : '#7c3aed';
            const bgAlpha = det.isManual ? 'rgba(245, 158, 11, 0.05)' : 'rgba(124, 58, 237, 0.05)';

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
            if (overlay) overlay.appendChild(box);

            if (printOverlay) {
                const printBox = document.createElement('div');
                printBox.className = 'absolute border-2 rounded';
                printBox.style.left = (x1 * 100).toFixed(2) + '%';
                printBox.style.top = (y1 * 100).toFixed(2) + '%';
                printBox.style.width = ((x2 - x1) * 100).toFixed(2) + '%';
                printBox.style.height = ((y2 - y1) * 100).toFixed(2) + '%';
                printBox.style.borderColor = colorClass;
                printBox.style.backgroundColor = 'transparent';
                printOverlay.appendChild(printBox);
            }

            if (evidenceContainer) {
                const evidenceCard = document.createElement('div');
                evidenceCard.style.border = '1px solid #cbd5e1'; 
                evidenceCard.style.borderRadius = '6px'; 
                evidenceCard.style.padding = '6px'; 
                evidenceCard.style.backgroundColor = '#f8fafc';
                evidenceCard.style.display = 'flex';
                evidenceCard.style.flexDirection = 'column';
                evidenceCard.style.gap = '6px';
                evidenceCard.style.breakInside = 'avoid';
                evidenceCard.style.pageBreakInside = 'avoid';

                const boxW = x2 - x1;
                const boxH = y2 - y1;
                const pctW = boxW > 0 ? (100 / boxW).toFixed(1) : 100;
                const pctH = boxH > 0 ? (100 / boxH).toFixed(1) : 100;

                evidenceCard.innerHTML = `
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; text-align: left;">
                        <strong style="text-transform: uppercase; color: ${det.isManual ? '#d97706' : '#2563eb'}; font-weight:900;">
                            #${idx + 1} — ${det.type} ${det.isManual ? '(Manual)' : '(AI Log)'}
                        </strong>
                        <span style="color: #64748b; font-family: monospace;">
                            ${det.confidence ? 'Conf: ' + Math.round(det.confidence * 100) + '%' : '100% CONFIRMED'}
                        </span>
                    </div>
                    <div style="width: 100%; height: 110px; position: relative; overflow: hidden; border-radius: 4px; background-color: #000000;">
                        <img 
                            src="${base64ImageStringCache}" 
                            alt="Defect ${idx + 1}" 
                            style="position: absolute; width: ${pctW}%; height: ${pctH}%; left: -${(x1 * pctW).toFixed(1)}%; top: -${(y1 * pctH).toFixed(1)}%; object-fit: contain; transform: scale(1.3); transform-origin: ${(x1 + x2) * 50}% ${(y1 + y2) * 50}%;"
                        />
                        <div style="position: absolute; border: ${det.isManual ? '2px dashed #d97706' : '2px solid #2563eb'}; left: ${x1 * 100}%; top: ${y1 * 100}%; width: ${(x2 - x1) * 100}%; height: ${(y2 - y1) * 100}%; pointer-events: none; box-shadow: 0 0 0 4000px rgba(0, 0, 0, 0.4);" />
                    </div>
                    <div style="font-size: 7px; color: #475569; font-family: monospace; margin-top: 2px; text-align: left;">
                        Metric type: Surface Area Footprint — <strong>Estimated Structural Vulnerability</strong>
                    </div>
                `;
                evidenceContainer.appendChild(evidenceCard);
            }

            const itemCard = document.createElement('div');
            itemCard.className = 'p-3 rounded-xl border border-slate-200 dark:border-white/5 bg-white dark:bg-white/5 flex items-center justify-between shadow-sm';
            itemCard.innerHTML = `
                <div class="space-y-0.5 select-none text-left">
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
            if (listTray) listTray.appendChild(itemCard);
        });

        const printBridge = document.getElementById('print-bridge-name');
        const selectBridge = document.querySelector('select[name="bridge_name"]');
        if (printBridge && selectBridge) printBridge.innerText = selectBridge.value.toUpperCase();

        const printTemp = document.getElementById('print-temp-val');
        const inputTemp = document.querySelector('input[name="temperature"]');
        if (printTemp && inputTemp) printTemp.innerText = inputTemp.value;

        const printHumidity = document.getElementById('print-humidity-val');
        const inputHumidity = document.querySelector('input[name="humidity"]');
        if (printHumidity && inputHumidity) printHumidity.innerText = inputHumidity.value;

        const printTotal = document.getElementById('print-total-count');
        if (printTotal) printTotal.innerText = activeDetectionsCollection.length;

        const printHash = document.getElementById('print-dataset-hash');
        if (printHash && printHash.innerText === 'N/A') {
            printHash.innerText = 'AST-' + Math.random().toString(16).substr(2, 8).toUpperCase();
        }

        const tableBody = document.getElementById('print-distribution-table-body');
        if (tableBody) {
            tableBody.innerHTML = '';
            const classCounts = {};
            activeDetectionsCollection.forEach(d => { classCounts[d.type] = (classCounts[d.type] || 0) + 1; });

            const loggedClasses = Object.keys(classCounts);
            if (loggedClasses.length > 0) {
                loggedClasses.forEach(cls => {
                    const tr = document.createElement('tr');
                    tr.style.borderBottom = '1px solid #e2e8f0';
                    tr.innerHTML = `<td style="padding: 6px 0; text-transform: uppercase; font-family: monospace; color: #1e293b; font-weight: 700; text-align: left;">${cls}</td><td style="padding: 6px 0; text-align: right; font-weight: 900; color: #000000; font-family: monospace; font-size: 11px;">${classCounts[cls]}</td>`;
                    tableBody.appendChild(tr);
                });
            } else {
                tableBody.innerHTML = `<tr style="border-bottom: 1px solid #e2e8f0;"><td style="padding: 12px 0; color: #64748b; font-style: italic; text-transform: uppercase; font-weight: 500; text-align: left;" colSpan="2">No structural defect vulnerabilities registered by YOLO loop context.</td></tr>`;
            }
        }
    }

    window.removeAnnotationNode = async function(index) {
        const targetDet = activeDetectionsCollection[index];
        if (!targetDet) return;

        // If it's a manual annotation box, delete it from the database first
        if (targetDet.isManual) {
            const form = document.getElementById('ai-inference-form');
            const selectedBridge = form.querySelector('select[name="bridge_name"]').value;
            const outputImg = document.getElementById('processed-output-img').src;

            try {
                const response = await fetch('/web-api/defects/delete-annotation', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({
                        bridge_name: selectedBridge,
                        defect_class: targetDet.type,
                        image_path: outputImg
                    })
                });

                const result = await response.json();
                if (!response.ok) throw new Error(result.message);

                console.log("⚡ [Sync Delete] Record cleanly stripped from backend SQL tables.");

                // Broadcast a negative count update to decrements dashboard metric metrics smoothly
                const liveDecrementEvent = new CustomEvent('hoverscan:telemetry-update', {
                    detail: { 
                        bridgeName: selectedBridge, 
                        addedCount: -1 // ⚡ Subtracts 1 from counters and chart bars!
                    }
                });
                document.dispatchEvent(liveDecrementEvent);

            } catch (err) {
                console.error("Database deletion failed:", err);
                alert("Warning: Could not remove record from database.");
                return; // Stop execution to keep UI and DB in sync
            }
        }

        // Remove from the local array and re-render the viewport layout context
        activeDetectionsCollection.splice(index, 1);
        renderInterfaceOverlayMatrix();
    };

    window.toggleManualDrawMode = function() {
        manualDrawActiveFlag = !manualDrawActiveFlag;
        const btn = document.getElementById('manual-draw-btn');
        const selector = document.getElementById('manual-class-select');
        const text = document.getElementById('draw-btn-text');
        
        if (manualDrawActiveFlag) {
            btn.classList.replace('bg-slate-50', 'bg-amber-500');
            btn.classList.add('text-white');
            if (selector) selector.classList.remove('hidden');
            text.innerText = 'Exit Drawing Mode';
        } else {
            btn.classList.replace('bg-amber-500', 'bg-slate-50');
            btn.classList.remove('text-white');
            if (selector) selector.classList.add('hidden');
            text.innerText = 'Add Manual Box';
        }
        renderInterfaceOverlayMatrix(); 
    };

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

    document.addEventListener('mouseup', async function(e) {
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
            const form = document.getElementById('ai-inference-form');
            const targetClass = document.getElementById('manual-class-select').value;
            const selectedBridge = form.querySelector('select[name="bridge_name"]').value;
            const tempVal = form.querySelector('input[name="temperature"]').value;
            const humidVal = form.querySelector('input[name="humidity"]').value;

            // Determine severity matching structural hierarchy limits
            let mappedSeverity = 'Low';
            if (['potholes', 'crack', 'concrete spalling', 'road bleeding'].includes(targetClass)) mappedSeverity = 'High';
            if (targetClass === 'spalling expose rebar') mappedSeverity = 'Critical';
            if (['rust', 'vegetation', 'bridge joint'].includes(targetClass)) mappedSeverity = 'Medium';

            // Append temporary UI canvas box frame locally
            activeDetectionsCollection.push({
                type: targetClass,
                bbox: [x1, y1, x2, y2],
                confidence: null,
                isManual: true
            });

            try {
                const syncResponse = await fetch('/web-api/defects/save-annotation', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({
                        bridge_name: selectedBridge,
                        defect_class: targetClass,
                        severity: mappedSeverity,
                        temperature: tempVal,
                        humidity: humidVal,
                        image_path: document.getElementById('processed-output-img').src,
                        // ⚡ FIXED: Send the coordinates array so it passes model validation rules
                        bbox_coordinates: [x1, y1, x2, y2] 
                    })
                });

                // Inside your AI analysis upload loop / manual drawings listeners inside analysis.blade.php:
                const liveUpdateEvent = new CustomEvent('hoverscan:telemetry-update', {
                    detail: {
                        bridgeName: selectedBridge,
                        addedCount: 1, // (or -1 inside window.removeAnnotationNode)
                        defectClass: targetClass, // e.g. 'vegetation'
                        severity: mappedSeverity // e.g. 'Medium'
                    }
                });
                document.dispatchEvent(liveUpdateEvent);

            } catch (syncErr) {
                console.error("Database connection failure context:", syncErr);
                alert("Warning: Canvas drawn locally but failed writing to master SQL log sequences.");
            }
        }
        
        if (crosshairBoxEl) {
            crosshairBoxEl.remove();
            crosshairBoxEl = null;
        }
        renderInterfaceOverlayMatrix();
    });

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
                activeDetectionsCollection[index].bbox = [newX1, newY1, newX1 + boxWidth, newY1 + boxHeight];
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

   // 7. 🚀 UPGRADED SEAMLESS BACKDROP IFRAME PRINTER ENGINE (FIXED LOGO & PERFECT PAGE BREAKS)
    function executeReportPrint() {
        const reportContent = document.getElementById('hoverscan-print-template').innerHTML;
        
        // 1. Remove any old leftover frame instances from the DOM
        const existingFrame = document.getElementById('hoverscan-silent-print-frame');
        if (existingFrame) existingFrame.remove();

        // 2. Create a completely invisible iframe hidden safely from view
        const iframe = document.createElement('iframe');
        iframe.id = 'hoverscan-silent-print-frame';
        iframe.style.position = 'fixed';
        iframe.style.right = '0';
        iframe.style.bottom = '0';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = '0';
        iframe.style.margin = '0';
        iframe.style.padding = '0';
        iframe.style.opacity = '0';
        iframe.style.pointerEvents = 'none';

        document.body.appendChild(iframe);

        const frameDoc = iframe.contentWindow.document || iframe.contentDocument;
        
        frameDoc.open();
        frameDoc.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Hoverscan Inspection Report</title>
                <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800;900&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
                <script src="https://cdn.tailwindcss.com"><\/script>
                <style>
                    body { 
                        font-family: 'Plus Jakarta Sans', sans-serif; 
                        background-color: #ffffff !important; 
                        color: #000000 !important; 
                        padding: 20px; 
                        margin: 0;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    .font-mono { font-family: 'JetBrains Mono', monospace !important; }
                    
                    /* ⚡ PROFESSIONAL PRINT PAGE LAYOUT BREAK BALANCING CONTROLS */
                    @media print {
                        body { padding: 0; margin: 0; }
                        @page { 
                            size: A4 portrait; 
                            margin: 20mm 15mm 20mm 15mm; 
                        }
                        
                        /* Forces entire main sections to avoid slicing in half */
                        h3, table, .grid {
                            break-inside: avoid !important;
                            page-break-inside: avoid !important;
                        }
                    }
                    img { max-width: 100%; object-fit: contain; }
                </style>
            </head>
            <body>
                <div style="width: 100%; max-w-[190mm]; margin: 0 auto;">
                    ${reportContent}
                </div>
                <script>
                    // ⚡ ASYNC LOGO RECOVERY REPAIR ENGINE
                    const logoImg = document.querySelector('img[alt="Hoverscan Logo"]');
                    if (logoImg) {
                        // Re-route the path explicitly to verify local directory asset mapping lines
                        logoImg.src = window.location.origin + '/hoverscanimg.png';
                        
                        // Hide broken image frames gracefully if the asset file itself is missing
                        logoImg.onerror = function() {
                            this.style.display = 'none';
                        };
                    }

                    window.onload = function() {
                        // Allow Tailwind CDN parsing engine to map responsive vectors cleanly in background thread memory
                        setTimeout(() => {
                            window.focus();
                            window.print();
                        }, 600);
                    };
                <\/script>
            </body>
            </html>
        `);
        frameDoc.close();
    }
</script>
@endpush