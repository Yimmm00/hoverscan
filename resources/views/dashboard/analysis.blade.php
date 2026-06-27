<div id="view-panel-analysis" class="space-y-8 tab-panel-node hidden animate-fade-in">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2">
        <div>
            <h3 class="text-xl font-black uppercase italic tracking-tight text-slate-900 dark:text-white">Analysis Interface Matrix</h3>
            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-1">Execute core neural model inferences & review historical telemetry scans</p>
        </div>

        <div class="flex p-1 bg-slate-100 dark:bg-white/5 rounded-xl self-start sm:self-center">
            <button type="button" id="toggle-scan-mode-btn" class="px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg transition-all bg-white dark:bg-[#0c0e14] text-blue-600 dark:text-blue-400 shadow-sm">
                Run New Scan
            </button>
            <button type="button" id="toggle-history-mode-btn" class="px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg transition-all text-slate-400 dark:text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
                Inference History
            </button>
        </div>
    </div>

    <div id="analysis-workspace-scan" class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        
        <div class="xl:col-span-1 p-8 rounded-[2.5rem] border border-slate-200/80 dark:border-white/5 bg-white dark:bg-[#0c0e14] h-fit shadow-sm">
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
                    <div id="drop-zone-canvas" onclick="triggerFilePicker()" class="p-6 border-2 border-dashed border-slate-200 dark:border-white/10 rounded-2xl bg-slate-50 dark:bg-[#080a0f] text-center group cursor-pointer transition-all hover:border-blue-500 flex flex-col items-center justify-center gap-2 mb-2">
                        <i data-lucide="upload-cloud" class="w-5 h-5 text-slate-400 group-hover:text-blue-500 transition-colors"></i>
                        <span id="dropzone-text-hint" class="text-[10px] text-slate-400 uppercase font-black tracking-wider">Drag/Drop or Click to Browse</span>
                    </div>
                    <input type="file" id="inference-file-picker" name="file" accept="image/jpeg,image/png,video/mp4,video/mpeg,video/quicktime,video/x-msvideo" onchange="syncFileHint(this)" required class="hidden">
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

                <button type="button" id="report-print-btn" onclick="executeReportPrint()" class="px-4 py-2.5 bg-slate-900 text-white dark:bg-white dark:text-slate-900 rounded-xl text-xs font-black uppercase tracking-widest hover:opacity-90 transition-all flex items-center gap-2 shadow cursor-pointer">
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
                        
                        <video id="processed-output-video" class="max-w-full max-h-[400px] object-contain rounded-xl hidden shadow-sm" controls autoplay loop></video>
                        
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

    <div id="analysis-workspace-history" class="hidden border border-slate-200/80 dark:border-white/5 rounded-[2.5rem] overflow-hidden bg-white dark:bg-[#0c0e14] h-[550px] flex flex-col shadow-sm animate-fade-in">
        <div class="overflow-y-auto custom-scrollbar w-full flex-1">
            <table class="w-full text-left border-collapse">
                <thead class="sticky top-0 z-10">
                    <tr class="border-b border-slate-200 dark:border-white/5 text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-[#0c0e14]">
                        <th class="py-5 px-8">Sequence Target ID</th>
                        <th class="py-5 px-6">Structure Name</th>
                        <th class="py-5 px-6">Env Metrics</th>
                        <th class="py-5 px-6">Execution Timestamp</th>
                        <th class="py-5 px-6 text-center">Status Matrix</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5 text-xs font-bold text-slate-600 dark:text-slate-300" id="inference-history-table-body">
                    <tr class="opacity-60">
                        <td class="py-5 px-8 font-mono text-blue-500 font-bold">#AST-4D8E9A2B</td>
                        <td class="py-5 px-6 font-black uppercase italic text-slate-900 dark:text-white">DARUL HANA S-BRIDGE</td>
                        <td class="py-5 px-6 font-mono text-[11px] text-slate-400">31°C / 78% RH</td>
                        <td class="py-5 px-6 text-slate-400">2026-06-25 10:14</td>
                        <td class="py-5 px-6 text-center">
                            <span class="px-2.5 py-1 rounded-md text-[9px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-500">Processed</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('view-scripts')
<script>
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

    function triggerFilePicker() {
        document.getElementById('inference-file-picker').click();
    }

    function syncFileHint(inputElement) {
        const textHint = document.getElementById('dropzone-text-hint');
        if (inputElement.files && inputElement.files[0]) {
            textHint.innerText = inputElement.files[0].name.toUpperCase();
        } else {
            textHint.innerText = "DRAG/DROP OR CLICK TO BROWSE";
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const dropZone = document.getElementById('drop-zone-canvas');
        const filePicker = document.getElementById('inference-file-picker');
        const hintText = document.getElementById('dropzone-text-hint');

        const scanBtn = document.getElementById('toggle-scan-mode-btn');
        const historyBtn = document.getElementById('toggle-history-mode-btn');
        const scanPane = document.getElementById('analysis-workspace-scan');
        const historyPane = document.getElementById('analysis-workspace-history');

        scanBtn.addEventListener('click', () => {
            toggleModeStyle(scanBtn, historyBtn);
            scanPane.classList.remove('hidden');
            historyPane.classList.add('hidden');
        });

        historyBtn.addEventListener('click', () => {
            toggleModeStyle(historyBtn, scanBtn);
            scanPane.classList.add('hidden');
            historyPane.classList.remove('hidden');
            loadInferenceHistoryGrid();
        });

        function toggleModeStyle(active, inactive) {
            active.className = "px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg transition-all bg-white dark:bg-[#0c0e14] text-blue-600 dark:text-blue-400 shadow-sm";
            inactive.className = "px-4 py-2 text-[10px] font-black uppercase tracking-wider rounded-lg transition-all text-slate-400 dark:text-slate-500 hover:text-slate-700 dark:hover:text-slate-300";
        }

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('border-blue-500', 'bg-blue-500/[0.02]');
        });

        ['dragleave', 'drop'].forEach(evName => {
            dropZone.addEventListener(evName, () => {
                dropZone.classList.remove('border-blue-500', 'bg-blue-500/[0.02]');
            });
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            if (e.dataTransfer.files.length) {
                filePicker.files = e.dataTransfer.files;
                hintText.innerText = e.dataTransfer.files[0].name.toUpperCase();
            }
        });
    });

    async function loadInferenceHistoryGrid() {
        const tableBody = document.getElementById('inference-history-table-body');
        tableBody.innerHTML = `<tr><td colspan="5" class="py-12 text-center text-xs font-black tracking-widest text-blue-500 uppercase"><div class="flex items-center justify-center gap-2"><i data-lucide="refresh-cw" class="w-4 h-4 animate-spin"></i> Syncing Historical Execution Ledger Matrices...</div></td></tr>`;
        if (typeof lucide !== 'undefined') lucide.createIcons();

        try {
            const response = await fetch('/web-api/dashboard/filter?range=all');
            const result = await response.json();
            
            if (result && result.success && result.logs.length > 0) {
                tableBody.innerHTML = result.logs.map((log, idx) => `
                    <tr class="hover:bg-slate-50 dark:hover:bg-white/[0.01] transition-colors duration-150">
                        <td class="py-5 px-8 font-mono text-blue-500 font-bold uppercase">${log.dataset_id || '#AST-UNKNOWN'}</td>
                        <td class="py-5 px-6 font-black uppercase italic text-slate-900 dark:text-white">${log.bridge_name}</td>
                        <td class="py-5 px-6 font-mono text-[11px] text-slate-400 dark:text-slate-500">${log.temperature}°C / ${log.humidity}% RH</td>
                        <td class="py-5 px-6 text-slate-400 dark:text-slate-500">${log.created_at}</td>
                        <td class="py-5 px-6 text-center">
                            <span class="px-2.5 py-1 rounded-md text-[9px] font-black uppercase tracking-wider ${log.confidence_score ? 'bg-emerald-500/10 text-emerald-500' : 'bg-amber-500/10 text-amber-500'}">
                                ${log.confidence_score ? 'AI Processed' : 'Manual Log'}
                            </span>
                        </td>
                    </tr>
                `).join('');
            } else {
                tableBody.innerHTML = `<tr><td colspan="5" class="py-12 text-center text-xs uppercase font-bold text-slate-400 tracking-widest">No previous executions found in active ledger maps.</td></tr>`;
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
        } catch (err) {
            console.error(err);
            tableBody.innerHTML = `<tr><td colspan="5" class="py-12 text-center text-xs font-bold text-rose-500 uppercase">Error parsing automated matrix sequence trails.</td></tr>`;
        }
    }

    // Unified AI Inference Handling Pipeline Loop (Supports Images & Real-time Video Tracking Stream)
    document.getElementById('ai-inference-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('submit-btn');
        const placeholder = document.getElementById('viewport-placeholder');
        const imgWrapper = document.getElementById('image-viewport-wrapper');
        const outputImg = document.getElementById('processed-output-img');
        const overlay = document.getElementById('bbox-overlay-wrapper');
        
        const fileInput = document.getElementById('inference-file-picker');
        if (!fileInput.files || !fileInput.files[0]) return;

        const selectedBridge = form.querySelector('select[name="bridge_name"]').value;
        const targetFile = fileInput.files[0];
        const isVideoFile = targetFile.type.startsWith('video/');

        const cacheFingerprintKey = `hoverscan_cache_${btoa(selectedBridge)}_${targetFile.name}_${targetFile.size}`;
        const cachedTelemetryData = sessionStorage.getItem(cacheFingerprintKey);
        
        overlay.innerHTML = '';
        activeDetectionsCollection = [];
        base64ImageStringCache = null;

        const actionButtonContainer = document.getElementById('print-action-button-container');
        if (actionButtonContainer) actionButtonContainer.classList.add('hidden');

        if (!isVideoFile && cachedTelemetryData) {
            const cachedResult = JSON.parse(cachedTelemetryData);
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

        // 🎥 PIPELINE A: PROGRESSIVE VIDEO ANALYTICS ROUTINE (SSE)
        if (isVideoFile) {
            submitBtn.disabled = true;
            placeholder.classList.remove('hidden');
            imgWrapper.classList.add('hidden');
            
            placeholder.innerHTML = `
                <i data-lucide="video" class="w-12 h-12 text-blue-500 animate-pulse mb-3"></i>
                <p class="text-[10px] uppercase font-black tracking-widest text-blue-500">Initializing Video Pipeline...</p>
            `;
            if (typeof lucide !== 'undefined') lucide.createIcons();

            const videoPayloadData = new FormData(form);
            if(!videoPayloadData.has('humidity')) videoPayloadData.append('humidity', form.querySelector('input[name="humidity"]').value);
            if(!videoPayloadData.has('temperature')) videoPayloadData.append('temperature', form.querySelector('input[name="temperature"]').value);

            try {
                const response = await fetch('http://127.0.0.1:8001/analyze-video', {
                    method: 'POST',
                    body: videoPayloadData
                });

                if (!response.ok) throw new Error("Video inference pipeline encountered an internal thread failure.");
                
                const streamReader = response.body.getReader();
                const stringDecoder = new TextDecoder("utf-8");
                let internalBufferStr = "";

                while (true) {
                    const { value, done } = await streamReader.read();
                    if (done) break;
                    
                    internalBufferStr += stringDecoder.decode(value, { stream: true });
                    const eventLines = internalBufferStr.split('\n');
                    internalBufferStr = eventLines.pop(); 
                    
                    for (const eventLine of eventLines) {
                        const cleanLineStr = eventLine.trim();
                        if (!cleanLineStr.startsWith('data: ')) continue;
                        
                        const jsonRawString = cleanLineStr.replace('data: ', '').trim();
                        if (!jsonRawString) continue;

                        try {
                            const ssePayload = JSON.parse(jsonRawString);
                            
                            if (ssePayload.status === 'processing') {
                                submitBtn.innerHTML = `<span class="flex items-center gap-2 justify-center"><i data-lucide="refresh-cw" class="w-4 h-4 animate-spin"></i> PROCESSING ${ssePayload.progress}%</span>`;
                                placeholder.innerHTML = `
                                    <i data-lucide="activity" class="w-12 h-12 text-blue-500 animate-bounce mb-3"></i>
                                    <p class="text-[10px] uppercase font-black tracking-widest text-slate-800 dark:text-white">Analyzing Frame Sequences...</p>
                                    <p class="text-[9px] font-mono font-bold text-blue-500 mt-1">${ssePayload.progress}% COMPLETE</p>
                                `;
                                if (typeof lucide !== 'undefined') lucide.createIcons();
                            }
                            
                            if (ssePayload.status === 'complete') {
                                placeholder.classList.add('hidden');
                                imgWrapper.classList.remove('hidden');
                                
                                const outputVideo = document.getElementById('processed-output-video');
                                const outputImg = document.getElementById('processed-output-img');
                                
                                outputImg.classList.add('hidden');
                                outputVideo.classList.remove('hidden');
                                
                                outputVideo.src = `http://127.0.0.1:8001${ssePayload.video_url}`;
                                outputVideo.load();
                                outputVideo.play();
                                
                                base64ImageStringCache = `http://127.0.0.1:8001${ssePayload.snapshot_url}`; 
                                
                                activeDetectionsCollection = ssePayload.all_detections.map(d => ({
                                    type: d.type,
                                    bbox: d.bbox,
                                    confidence: d.confidence,
                                    isManual: false
                                }));
                                
                                renderInterfaceOverlayMatrix();
                                
                                activeDetectionsCollection.forEach(det => {
                                    let mappedSeverity = 'Medium';
                                    const lowerType = det.type.toLowerCase().trim();
                                    if (['potholes', 'pothole', 'crack', 'concrete spalling'].includes(lowerType)) mappedSeverity = 'High';
                                    if (lowerType === 'spalling expose rebar') mappedSeverity = 'Critical';
                                    
                                    document.dispatchEvent(new CustomEvent('hoverscan:telemetry-update', {
                                        detail: { bridgeName: selectedBridge, addedCount: 1, defectClass: lowerType, severity: mappedSeverity }
                                    }));
                                });
                                
                                alert("Success! High-resolution video stream tracked and logged cleanly across SQL tables.");
                            }
                            
                            if (ssePayload.status === 'error') {
                                throw new Error(ssePayload.message);
                            }
                        } catch (jsonErr) {
                            console.debug("Parsing chunk buffer break:", jsonErr);
                        }
                    }
                }
            } catch (err) {
                console.error(err);
                placeholder.classList.remove('hidden');
                placeholder.innerHTML = `<i data-lucide="alert-triangle" class="w-12 h-12 text-rose-500 mb-2"></i><p class="text-[10px] uppercase font-black text-rose-500">Video Tracking Fault.</p>`;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = `<i data-lucide="cpu" class="w-4 h-4"></i> Execute Core Inference`;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
            return;
        }

        // 🖼️ PIPELINE B: ORIGINAL IMAGE INFERENCE ROUTINE
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<i data-lucide="refresh-cw" class="w-4 h-4 animate-spin"></i> Running GPU Inference...`;
        if (typeof lucide !== 'undefined') lucide.createIcons();
        
        placeholder.classList.remove('hidden');
        imgWrapper.classList.add('hidden');

        document.getElementById('processed-output-img').classList.remove('hidden');
        document.getElementById('processed-output-video').classList.add('hidden');

        const imageReaderInstance = new FileReader();
        imageReaderInstance.onload = async function(event) {
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

                    const telemetryCachePayload = {
                        detections: activeDetectionsCollection,
                        base64Img: base64ImageStringCache
                    };
                    sessionStorage.setItem(cacheFingerprintKey, JSON.stringify(telemetryCachePayload));

                    const tempVal = form.querySelector('input[name="temperature"]').value;
                    const humidVal = form.querySelector('input[name="humidity"]').value;

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

                        document.dispatchEvent(new CustomEvent('hoverscan:telemetry-update', {
                            detail: { bridgeName: selectedBridge, addedCount: 1, defectClass: lowerType, severity: mappedSeverity }
                        }));

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
                                    bbox_coordinates: det.bbox,
                                    confidence_score: det.confidence
                                })
                            });
                        } catch (err) {
                            console.error("AI auto-save sync failure:", err);
                        }
                    }
                };

            } catch (err) {
                console.error(err);
                placeholder.classList.remove('hidden');
                placeholder.innerHTML = `<i data-lucide="x-circle" class="w-12 h-12 text-rose-500 mb-2"></i><p class="text-[10px] uppercase font-black text-rose-500">Pipeline Execution Error.</p>`;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = `<i data-lucide="cpu" class="w-4 h-4"></i> Execute Core Inference`;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        };
        imageReaderInstance.readAsDataURL(targetFile);
    });

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

        const outputVideo = document.getElementById('processed-output-video');
        const isVideoActive = outputVideo && !outputVideo.classList.contains('hidden');

        if (overlay) {
            if (isVideoActive && !manualDrawActiveFlag) {
                overlay.style.pointerEvents = 'none';
            } else {
                overlay.style.pointerEvents = 'auto';
            }
        }

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

            if (overlay && (!isVideoActive || det.isManual)) {
                overlay.appendChild(box);
            }

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
                e.stopPropagation();
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

                const liveDecrementEvent = new CustomEvent('hoverscan:telemetry-update', {
                    detail: { 
                        bridgeName: selectedBridge, 
                        addedCount: -1 
                    }
                });
                document.dispatchEvent(liveDecrementEvent);

            } catch (err) {
                console.error("Database deletion failed:", err);
                alert("Warning: Could not remove record from database.");
                return;
            }
        }

        activeDetectionsCollection.splice(index, 1);
        renderInterfaceOverlayMatrix();
    };

    window.toggleManualDrawMode = function() {
        manualDrawActiveFlag = !manualDrawActiveFlag;
        const btn = document.getElementById('manual-draw-btn');
        const selector = document.getElementById('manual-class-select');
        const text = document.getElementById('draw-btn-text');
        const overlayWrapper = document.getElementById('bbox-overlay-wrapper');
        
        if (manualDrawActiveFlag) {
            btn.classList.replace('bg-slate-50', 'bg-amber-500');
            btn.classList.add('text-white');
            if (selector) selector.classList.remove('hidden');
            text.innerText = 'Exit Drawing Mode';
            
            if (overlayWrapper) overlayWrapper.style.pointerEvents = 'auto';
        } else {
            btn.classList.replace('bg-amber-500', 'bg-slate-50');
            btn.classList.remove('text-white');
            if (selector) selector.classList.add('hidden');
            text.innerText = 'Add Manual Box';
            
            const outputVideo = document.getElementById('processed-output-video');
            if (outputVideo && !outputVideo.classList.contains('hidden')) {
                if (overlayWrapper) overlayWrapper.style.pointerEvents = 'none';
            }
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

            let mappedSeverity = 'Low';
            if (['potholes', 'crack', 'concrete spalling', 'road bleeding'].includes(targetClass)) mappedSeverity = 'High';
            if (targetClass === 'spalling expose rebar') mappedSeverity = 'Critical';
            if (['rust', 'vegetation', 'bridge joint'].includes(targetClass)) mappedSeverity = 'Medium';

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
                        bbox_coordinates: [x1, y1, x2, y2] 
                    })
                });

                const liveUpdateEvent = new CustomEvent('hoverscan:telemetry-update', {
                    detail: {
                        bridgeName: selectedBridge,
                        addedCount: 1,
                        defectClass: targetClass,
                        severity: mappedSeverity
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

    // ⚡ FIXED PRINT REPORT ENGINE: Bypasses frameDoc.write Trusted Types assignment restrictions
    function executeReportPrint() {
        const reportContentContainer = document.getElementById('hoverscan-print-template');
        if (!reportContentContainer) return;

        const existingFrame = document.getElementById('hoverscan-silent-print-frame');
        if (existingFrame) existingFrame.remove();

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
        const frameWindow = iframe.contentWindow;
        const frameDoc = frameWindow.document;
        
        // Setup internal printing document architecture securely using nodes properties manipulation
        const printBaseStyle = frameDoc.createElement('style');
        printBaseStyle.textContent = `
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
            @media print {
                body { padding: 0; margin: 0; }
                @page { size: A4 portrait; margin: 20mm 15mm 20mm 15mm; }
                h3, table, .grid { break-inside: avoid !important; page-break-inside: avoid !important; }
            }
            img { max-width: 100%; object-fit: contain; }
        `;
        
        const printTailwindScript = frameDoc.createElement('script');
        printTailwindScript.src = "https://cdn.tailwindcss.com";

        const printMasterWrapper = frameDoc.createElement('div');
        printMasterWrapper.style.width = "100%";
        printMasterWrapper.style.maxWidth = "190mm";
        printMasterWrapper.style.margin = "0 auto";
        printMasterWrapper.innerHTML = reportContentContainer.innerHTML;

        // Correct Logo asset paths directly on iframe children
        const logoImgElement = printMasterWrapper.querySelector('img[alt="Hoverscan Logo"]');
        if (logoImgElement) {
            logoImgElement.src = window.location.origin + '/hoverscanimg.png';
            logoImgElement.onerror = function() { this.style.display = 'none'; };
        }

        frameDoc.head.appendChild(printBaseStyle);
        frameDoc.head.appendChild(printTailwindScript);
        frameDoc.body.appendChild(printMasterWrapper);

        // Await Tailwind styling compilation loop threads configuration rules
        setTimeout(() => {
            frameWindow.focus();
            frameWindow.print();
        }, 650);
    }
</script>
@endpush