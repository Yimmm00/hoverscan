<div id="view-panel-assets" class="space-y-8 tab-panel-node hidden animate-fade-in">
    <div>
        <h3 class="text-xl font-black uppercase italic tracking-tight text-slate-900 dark:text-white">Asset Inventory Hub</h3>
        <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-1">Manage physical infrastructure tracking logs</p>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">
        <div class="xl:col-span-1 p-8 rounded-[2.5rem] border border-slate-200/80 dark:border-white/5 bg-white dark:bg-[#0c0e14] h-fit shadow-sm">
            <h4 class="font-black uppercase tracking-tight italic text-base mb-6 text-slate-900 dark:text-white">Register New Node</h4>
            
            <form id="register-bridge-form" class="space-y-5 text-xs font-bold uppercase text-slate-600 dark:text-slate-400">
                @csrf
                <div>
                    <label class="text-[9px] text-slate-400 dark:text-slate-500 mb-1.5 block font-black">Structure Code Name</label>
                    <input type="text" name="name" required placeholder="e.g. Batang Rajang Bridge" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-[#080a0f] text-slate-900 dark:text-white outline-none focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
                </div>

                <div>
                    <label class="text-[9px] text-slate-400 dark:text-slate-500 mb-1.5 block font-black">Jurisdiction Division</label>
                    <select name="district" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-[#080a0f] text-slate-900 dark:text-white outline-none cursor-pointer hover:border-slate-300 dark:hover:border-white/20 transition-all">
                        <option value="KUCHING">KUCHING</option>
                        <option value="SAMARAHAN">SAMARAHAN</option>
                        <option value="MUKAH">MUKAH</option>
                        <option value="SIBU">SIBU</option>
                        <option value="BINTULU">BINTULU</option>
                        <option value="MIRI">MIRI</option>
                    </select>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label class="text-[9px] text-slate-400 dark:text-slate-500 block font-black">GPS Matrix (LAT, LNG)</label>
                        <span class="text-[8px] text-blue-500 normal-case font-black tracking-normal">Tip: Click on map to drop pins</span>
                    </div>
                    <input type="text" id="form-gps-matrix" name="location_coords" required placeholder="e.g. 2.5321, 111.8432" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-[#080a0f] text-slate-900 dark:text-white outline-none font-mono focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
                </div>

                <button type="submit" id="reg-submit-btn" class="w-full py-3.5 bg-blue-600 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-blue-500 transition-all shadow-xl shadow-blue-600/20 flex items-center justify-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i> Commit Registry Entry
                </button>
            </form>
        </div>

        <div class="xl:col-span-1 h-[550px] p-2 rounded-[2.5rem] border border-slate-200/80 dark:border-white/5 bg-white dark:bg-[#0c0e14] relative overflow-hidden shadow-sm">
            <div id="infrastructure-gis-map" class="w-full h-full rounded-[2rem] bg-slate-100 dark:bg-black/20"></div>
        </div>

        <div class="xl:col-span-2 border border-slate-200/80 dark:border-white/5 rounded-[2.5rem] overflow-hidden bg-white dark:bg-[#0c0e14] h-[550px] flex flex-col shadow-sm">
            <div class="overflow-y-auto custom-scrollbar w-full flex-1">
                <table class="w-full text-left border-collapse" id="bridges-inventory-table">
                    <thead class="sticky top-0 z-10">
                        <tr class="border-b border-slate-200 dark:border-white/5 text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-[#0c0e14]">
                            <th class="py-5 px-8">Structure Name</th>
                            <th class="py-5 px-6">Jurisdiction</th>
                            <th class="py-5 px-6">GPS Matrix</th>
                            <th class="py-5 px-6 text-center">Anomalies</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5 text-xs font-bold text-slate-600 dark:text-slate-300" id="bridges-table-body">
                        @foreach($bridges as $bridge)
                            @php
                                $maxSeverity = $bridge->defectRecords->first()->severity ?? 'None';
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-white/[0.01] cursor-pointer asset-row-node transition-colors duration-150" 
                                data-name="{{ $bridge->name }}" 
                                data-coords="{{ $bridge->location_coords }}"
                                data-max-severity="{{ $maxSeverity }}">
                                <td class="py-5 px-8 font-black uppercase italic text-slate-900 dark:text-white transition-colors duration-150">{{ $bridge->name }}</td>
                                <td class="py-5 px-6 text-blue-600 dark:text-blue-400 uppercase tracking-wider">{{ $bridge->district }}</td>
                                <td class="py-5 px-6 font-mono text-slate-400 dark:text-slate-500">{{ $bridge->location_coords }}</td>
                                <td class="py-5 px-6 text-center">
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-black bg-rose-500/10 text-rose-500 transition-all duration-300">
                                        {{ $bridge->defect_records_count ?? 0 }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('view-scripts')
<script>
    let gisMapInstance = null;
    let fallbackInteractionMarker = null;

    document.getElementById('nav-tabs-container').addEventListener('click', function(e) {
        const btn = e.target.closest('button[data-tab]');
        if (btn && btn.getAttribute('data-tab') === 'assets') {
            setTimeout(() => { initializeInfrastructureMap(); }, 150);
        }
    });

    function initializeInfrastructureMap() {
        if (gisMapInstance) {
            gisMapInstance.resize();
            return;
        }
        
        const initialStyle = document.documentElement.classList.contains('dark')
            ? 'https://basemaps.cartocdn.com/gl/dark-matter-gl-style/style.json'
            : 'https://basemaps.cartocdn.com/gl/positron-gl-style/style.json';

        gisMapInstance = new maplibregl.Map({
            container: 'infrastructure-gis-map',
            style: initialStyle, 
            center: [111.5000, 2.5000], 
            zoom: 6.2,
            attributionControl: false
        });
        
        gisMapInstance.addControl(new maplibregl.NavigationControl(), 'top-right');

        // Dynamic coordinate extraction handler on map click interface context
        gisMapInstance.on('click', (e) => {
            const { lng, lat } = e.lngLat;
            const precisionCoordsStr = `${lat.toFixed(4)}, ${lng.toFixed(4)}`;
            document.getElementById('form-gps-matrix').value = precisionCoordsStr;

            if (fallbackInteractionMarker) fallbackInteractionMarker.remove();

            const placeholderEl = document.createElement('div');
            placeholderEl.className = 'w-3 h-3 rounded-full bg-blue-500 animate-ping border border-white';
            
            fallbackInteractionMarker = new maplibregl.Marker({ element: placeholderEl })
                .setLngLat([lng, lat])
                .addTo(gisMapInstance);
        });

        document.querySelectorAll('.asset-row-node').forEach(row => {
            bindRowTelemetryMarker(row);
        });
    }

    function bindRowTelemetryMarker(row) {
        const name = row.getAttribute('data-name');
        const coordsString = row.getAttribute('data-coords');
        const maxSeverity = row.getAttribute('data-max-severity') || 'None';
        if (!coordsString) return;
        
        const [lat, lng] = coordsString.split(',').map(num => parseFloat(num.trim()));
        if (isNaN(lat) || isNaN(lng)) return;

        let markerColorClass = 'bg-blue-600'; 
        if (maxSeverity === 'Critical') {
            markerColorClass = 'bg-rose-500 animate-pulse ring-4 ring-rose-500/20';
        } else if (maxSeverity === 'High' || maxSeverity === 'Medium') {
            markerColorClass = 'bg-amber-500';
        }

        const markerEl = document.createElement('div');
        markerEl.className = `w-4 h-4 rounded-full ${markerColorClass} border-2 border-white shadow-lg cursor-pointer transition-transform hover:scale-125`;

        const popupContentHtml = `
            <div class="p-2 text-xs font-bold text-slate-900 uppercase min-w-[160px]">
                <h6 class="font-black border-b border-slate-200 pb-1 mb-1">${name}</h6>
                <p class="text-[9px] text-slate-400 font-mono mb-2">Coordinates: ${lat}, ${lng}</p>
                <button type="button" onclick="window.routeToAnalysisTarget('${name.replace(/'/g, "\\'")}')" class="w-full text-center px-2.5 py-1.5 bg-blue-600 hover:bg-blue-500 text-white text-[9px] font-black uppercase tracking-wider rounded-lg transition-all shadow-sm cursor-pointer border-0">Analyze Structure Node</button>
            </div>
        `;

        const marker = new maplibregl.Marker({ element: markerEl })
            .setLngLat([lng, lat])
            .setPopup(new maplibregl.Popup({ offset: 25 }).setHTML(popupContentHtml))
            .addTo(gisMapInstance);

        row.addEventListener('click', () => {
            document.querySelectorAll('.asset-row-node').forEach(r => r.classList.remove('bg-slate-50', 'dark:bg-white/5', 'border-l-4', 'border-blue-500'));
            row.classList.add(document.documentElement.classList.contains('dark') ? 'dark:bg-white/5' : 'bg-slate-50');
            gisMapInstance.flyTo({ center: [lng, lat], zoom: 12, speed: 1.2 });
            marker.togglePopup();
        });
    }

    document.getElementById('register-bridge-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('reg-submit-btn');
        
        const name = form.querySelector('input[name="name"]').value;
        const district = form.querySelector('select[name="district"]').value;
        const coordsString = form.querySelector('input[name="location_coords"]').value;

        submitBtn.disabled = true;
        submitBtn.innerHTML = `<i data-lucide="refresh-cw" class="w-4 h-4 animate-spin"></i> Registering Node...`;
        lucide.createIcons();

        try {
            const response = await fetch('/web-api/bridges/register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ name, district, location_coords: coordsString })
            });

            const result = await response.json();
            if (!response.ok) throw new Error(result.message || "Failed committing database transaction.");

            const tableBody = document.getElementById('bridges-table-body');
            const newRow = document.createElement('tr');
            newRow.className = 'hover:bg-slate-50 dark:hover:bg-white/[0.01] cursor-pointer asset-row-node transition-colors duration-150';
            newRow.setAttribute('data-name', name);
            newRow.setAttribute('data-coords', coordsString);
            newRow.setAttribute('data-max-severity', 'None');
            
            newRow.innerHTML = `
                <td class="py-5 px-8 font-black uppercase italic text-slate-900 dark:text-white">${name}</td>
                <td class="py-5 px-6 text-blue-600 dark:text-blue-400 uppercase">${district}</td>
                <td class="py-5 px-6 font-mono text-slate-400 dark:text-slate-400">${coordsString}</td>
                <td class="py-5 px-6 text-center"><span class="px-2.5 py-1 rounded-md text-[10px] font-black bg-rose-500/10 text-rose-500">0</span></td>
            `;

            tableBody.prepend(newRow);
            bindRowTelemetryMarker(newRow);
            
            if (fallbackInteractionMarker) { fallbackInteractionMarker.remove(); fallbackInteractionMarker = null; }

            const [lat, lng] = coordsString.split(',').map(num => parseFloat(num.trim()));
            if (gisMapInstance && !isNaN(lat) && !isNaN(lng)) {
                gisMapInstance.flyTo({ center: [lng, lat], zoom: 12, speed: 1.2 });
            }

            form.reset();
            alert("Success! Infrastructure Node securely indexed across Hoverscan dataset matrices.");
        } catch (err) {
            console.error(err);
            alert("Error committing entry: " + err.message);
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `<i data-lucide="plus" class="w-4 h-4"></i> Commit Registry Entry`;
            lucide.createIcons();
        }
    });

    document.addEventListener('hoverscan:telemetry-update', (e) => {
        const { bridgeName, addedCount } = e.detail;
        const targetRow = document.querySelector(`.asset-row-node[data-name="${bridgeName}"]`);
        if (!targetRow) return;

        const countBadge = targetRow.querySelector('td .rounded-md');
        if (countBadge) {
            let currentTotal = parseInt(countBadge.innerText.trim()) || 0;
            let nextComputedTotal = Math.max(0, currentTotal + addedCount);
            countBadge.innerText = nextComputedTotal;
            
            countBadge.classList.add('scale-110', 'bg-rose-500', 'text-white');
            setTimeout(() => { countBadge.classList.remove('scale-110', 'bg-rose-500', 'text-white'); }, 1500);
        }
    });
</script>
@endpush