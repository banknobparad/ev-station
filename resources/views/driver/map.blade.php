@extends('layouts.app')

@section('content')
    @php $driver = auth()->user(); @endphp
    <div class="driver-app-shell">
        <div id="map"></div>

        <div class="driver-overlay">
            <div class="search-row">
                <div class="driver-search-input compact">
                    <i class="bi bi-search"></i>
                    <input type="text" id="search" placeholder="ค้นหาสถานีหรือสถานที่..." autocomplete="off">
                </div>
                <button type="button" class="icon-btn" onclick="window.location.href='{{ route('driver.favorites') }}'"
                    aria-label="Favorites">
                    <i class="bi bi-heart-fill"></i>
                </button>
            </div>

            <div id="search-results" class="list-group mt-2"></div>
        </div>

        <div class="floating-actions" aria-hidden="false">
            <button type="button" class="sheet-icon-btn" id="btn-open-trip" aria-label="Info">
                <i class="bi bi-info-circle"></i>
            </button>
            <button type="button" class="sheet-icon-btn mt-2" onclick="goToMyLocation()" aria-label="Locate me">
                <i class="bi bi-geo-alt-fill"></i>
            </button>
        </div>

        <div class="driver-sheet">
            <div class="sheet-header d-flex align-items-center justify-content-between mb-2 p-1">
                <div class="flex-grow-1">
                    <div class="text-uppercase text-secondary small" style="font-weight: 500;">
                        รายชื่อสถานี ค้นเจอ <span class="text-primary fw-bold">{{ $stations->count() }}</span> สถานี
                    </div>
                </div>
                <a href="{{ route('driver.favorites') }}" class="text-decoration-none text-primary small align-self-start">
                    ดูทั้งหมด
                </a>
            </div>


            <div class="sheet-scroll" style="overflow-y: auto; flex-grow: 1; -webkit-overflow-scrolling: touch;">
                <div class="row g-2" id="station-list-container">
                    @foreach ($stations as $station)
                        <div class="col-12 station-item-wrapper" data-distance="999999">
                            <a href="{{ route('driver.station', $station) }}"
                                class="driver-card driver-station-card text-decoration-none text-dark d-flex align-items-start gap-3 p-3"
                                style="background: #fff; border-radius: 16px; border: 1px solid #f0f0f0; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">

                                <div class="flex-shrink-0">
                                    <img src="{{ $station->image ? asset('storage/' . $station->image) : 'https://placehold.co/60x60?text=EV' }}"
                                        alt="Station Logo" class="rounded-3"
                                        style="width: 40px; height: 40px; object-fit: cover; border: 1px solid #eaedf1;">
                                </div>

                                <div class="flex-grow-1" style="min-width: 0;">
                                    <h6 class="fw-bold mb-1 text-dark text-truncate" style="font-size: 1.05rem;">
                                        {{ $station->name }}</h6>

                                    <div class="text-muted small text-truncate mb-1" style="font-size: 0.85rem;">
                                        {{ \Illuminate\Support\Str::limit($station->address, 50) }}
                                    </div>

                                    <div class="d-flex align-items-center gap-2 text-muted small mb-2"
                                        style="font-size: 0.75rem;">
                                        <span class="text-primary fw-medium">
                                            เปิด
                                            {{ $station->open_time && $station->close_time && $station->open_time !== $station->close_time ? \Carbon\Carbon::parse($station->open_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($station->close_time)->format('H:i') : '24 ชั่วโมง' }}
                                        </span> <span class="text-secondary">|</span>

                                        <span class="station-distance" data-lat="{{ $station->lat }}"
                                            data-lng="{{ $station->lng }}">
                                            <i class="bi bi-geo-alt"></i> กำลังคำนวณ...
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ===== TRIP MODAL ===== --}}
    <div class="modal fade" id="tripModal" tabindex="-1" aria-labelledby="tripModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 480px;">
            <div class="modal-content trip-modal-content">
                <div class="modal-header trip-modal-header border-0 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <div class="trip-icon-badge">
                            <i class="bi bi-signpost-2-fill"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0" id="tripModalLabel">วางแผนการเดินทาง</h5>
                            <small class="text-muted">ค้นหาสถานีชาร์จตามเส้นทาง</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body trip-modal-body pt-3">
                    {{-- Origin --}}
                    <div class="trip-field-group mb-3">
                        <label class="trip-field-label">
                            <span class="trip-dot trip-dot-origin"></span>
                            ต้นทาง
                        </label>
                        <div class="trip-input-wrap">
                            <button type="button" id="btn-use-gps" class="trip-gps-btn">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span id="gps-label">ใช้ตำแหน่งปัจจุบัน</span>
                            </button>
                            <div class="trip-divider-or"><span>หรือ</span></div>
                            <input type="text" id="origin-input" class="trip-text-input"
                                placeholder="พิมพ์ต้นทาง เช่น ดอนเมือง กรุงเทพ...">
                            <div id="origin-results" class="trip-autocomplete"></div>
                        </div>
                    </div>

                    {{-- Arrow --}}
                    <div class="trip-arrow-connector">
                        <div class="trip-connector-line"></div>
                        <div class="trip-connector-icon"><i class="bi bi-arrow-down"></i></div>
                        <div class="trip-connector-line"></div>
                    </div>

                    {{-- Destination --}}
                    <div class="trip-field-group mb-4">
                        <label class="trip-field-label">
                            <span class="trip-dot trip-dot-dest"></span>
                            ปลายทาง
                        </label>
                        <div class="trip-input-wrap">
                            <input type="text" id="dest-input" class="trip-text-input"
                                placeholder="พิมพ์ปลายทาง เช่น แหลมทอง ระยอง...">
                            <div id="dest-results" class="trip-autocomplete"></div>
                        </div>
                    </div>

                    {{-- Buffer radius --}}
                    <div class="trip-option-row mb-4">
                        <label class="trip-field-label mb-2">
                            <i class="bi bi-lightning-charge-fill text-warning me-1"></i>
                            รัศมีค้นหาสถานีชาร์จ
                        </label>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="trip-radius-btn active" data-radius="5">5 กม.</button>
                            <button type="button" class="trip-radius-btn" data-radius="10">10 กม.</button>
                            <button type="button" class="trip-radius-btn" data-radius="20">20 กม.</button>
                            <button type="button" class="trip-radius-btn" data-radius="30">30 กม.</button>
                        </div>
                    </div>

                    {{-- Info box --}}
                    <div class="trip-info-box">
                        <i class="bi bi-info-circle-fill"></i>
                        <span>ระบบจะลากเส้นทางบนแผนที่และปักหมุดสถานีชาร์จที่อยู่ใกล้เส้นทาง</span>
                    </div>
                </div>

                <div class="modal-footer trip-modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary flex-shrink-0"
                        data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" id="btn-find-route" class="trip-find-btn flex-grow-1">
                        <i class="bi bi-signpost-2-fill me-2"></i>
                        ค้นหาเส้นทาง
                    </button>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        /* ===== TRIP MODAL STYLES ===== */
        .trip-modal-content {
            border: none;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.18);
        }

        .trip-modal-header {
            padding: 1.25rem 1.25rem 0.5rem;
            background: linear-gradient(135deg, #f0faf4 0%, #e8f5ff 100%);
        }

        .trip-modal-body {
            padding: 1rem 1.25rem;
        }

        .trip-modal-footer {
            padding: 0.75rem 1.25rem 1.25rem;
            background: #fafafa;
            gap: 0.75rem;
        }

        .trip-icon-badge {
            width: 42px;
            height: 42px;
            background: var(--ev-green);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.1rem;
            box-shadow: 0 4px 12px rgba(45, 198, 83, 0.35);
        }

        .trip-field-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .trip-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .trip-dot-origin {
            background: #22c55e;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);
        }

        .trip-dot-dest {
            background: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
        }

        .trip-input-wrap {
            position: relative;
        }

        .trip-gps-btn {
            width: 100%;
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border: 1.5px solid #bbf7d0;
            border-radius: 12px;
            padding: 0.6rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.88rem;
            font-weight: 600;
            color: #166534;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 0;
        }

        .trip-gps-btn:hover {
            background: linear-gradient(135deg, #dcfce7, #bbf7d0);
            border-color: #4ade80;
        }

        .trip-gps-btn.active {
            background: linear-gradient(135deg, #16a34a, #22c55e);
            border-color: #16a34a;
            color: white;
        }

        .trip-divider-or {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 0.5rem 0;
            color: #9ca3af;
            font-size: 0.75rem;
        }

        .trip-divider-or::before,
        .trip-divider-or::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }

        .trip-text-input {
            width: 100%;
            background: #f8fafc;
            border: 1.5px solid #e5e9ef;
            border-radius: 12px;
            padding: 0.65rem 1rem;
            font-size: 0.9rem;
            color: #1a1a2e;
            outline: none;
            transition: all 0.2s;
        }

        .trip-text-input:focus {
            border-color: var(--ev-green);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(45, 198, 83, 0.12);
        }

        .trip-autocomplete {
            position: absolute;
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #e5e9ef;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            z-index: 9999;
            overflow: hidden;
            display: none;
            margin-top: 4px;
        }

        .trip-autocomplete.show {
            display: block;
        }

        .trip-autocomplete-item {
            padding: 0.65rem 1rem;
            cursor: pointer;
            font-size: 0.85rem;
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.15s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .trip-autocomplete-item:last-child {
            border-bottom: none;
        }

        .trip-autocomplete-item:hover {
            background: #f0fdf4;
            color: #166534;
        }

        .trip-arrow-connector {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0 1rem;
            margin: 0.25rem 0;
            color: #d1d5db;
        }

        .trip-connector-line {
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }

        .trip-connector-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            color: #9ca3af;
        }

        .trip-radius-btn {
            background: #f3f4f6;
            border: 1.5px solid #e5e7eb;
            border-radius: 20px;
            padding: 0.3rem 0.85rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.2s;
        }

        .trip-radius-btn.active,
        .trip-radius-btn:hover {
            background: var(--ev-green);
            border-color: var(--ev-green);
            color: white;
            box-shadow: 0 2px 8px rgba(45, 198, 83, 0.3);
        }

        .trip-info-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            padding: 0.65rem 0.85rem;
            font-size: 0.8rem;
            color: #1e40af;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            line-height: 1.5;
        }

        .trip-find-btn {
            background: var(--ev-green);
            color: white;
            border: none;
            border-radius: 14px;
            padding: 0.75rem 1.5rem;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .trip-find-btn:hover {
            background: var(--ev-green-dark);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(45, 198, 83, 0.35);
        }

        .trip-find-btn:disabled {
            background: #d1d5db;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Route active banner */
        .route-active-banner {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, var(--ev-green), #16a34a);
            color: white;
            padding: 0.55rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 2000;
            font-size: 0.82rem;
            font-weight: 600;
            box-shadow: 0 2px 12px rgba(45, 198, 83, 0.4);
            transform: translateY(-100%);
            transition: transform 0.3s ease;
        }

        .route-active-banner.show {
            transform: translateY(0);
        }

        .route-banner-clear {
            background: rgba(255, 255, 255, 0.25);
            border: none;
            border-radius: 8px;
            color: white;
            padding: 0.25rem 0.65rem;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
        }

        /* Station count badge on map */
        .route-station-count {
            background: white;
            border: 2px solid var(--ev-green);
            border-radius: 20px;
            padding: 0.2rem 0.6rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--ev-green);
        }

        /* Route sheet station card */
        .route-station-card {
            background: #fff;
            border-radius: 16px;
            border: 1.5px solid #d1fae5 !important;
            box-shadow: 0 2px 8px rgba(34, 197, 94, 0.07) !important;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .route-station-card:hover {
            border-color: #6ee7b7 !important;
            box-shadow: 0 4px 16px rgba(34, 197, 94, 0.14) !important;
        }

        /* Sequence badge on card */
        .station-seq-badge {
            position: absolute;
            top: -6px;
            left: -6px;
            background: #22c55e;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
            box-shadow: 0 1px 4px rgba(34, 197, 94, 0.4);
        }
    </style>

    {{-- Route Active Banner --}}
    <div class="route-active-banner" id="route-banner">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-signpost-2-fill"></i>
            <span id="route-banner-text">กำลังแสดงเส้นทาง...</span>
        </div>
        <button class="route-banner-clear" onclick="clearRoute()">✕ ล้างเส้นทาง</button>
    </div>

    <script>
        const stations = @json($stations);

        const map = L.map('map', {
            zoomControl: false,
            attributionControl: false
        }).setView([13.7563, 100.5018], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: ''
        }).addTo(map);

        // ===== STATION MARKERS =====
        let stationMarkers = [];

        function getIcon(station) {
            return L.divIcon({
                className: '',
                html: `<div style="
                    background:#4285F4;
                    width:16px; height:16px;
                    border-radius:50%;
                    border:2px solid white;
                    box-shadow:0 0 4px rgba(0,0,0,0.4)">
                </div>`,
                iconSize: [16, 16],
                iconAnchor: [8, 8],
            });
        }

        function getRouteStationIcon() {
            return L.divIcon({
                className: '',
                html: `<div style="
                    background:#22c55e;
                    width:20px; height:20px;
                    border-radius:50%;
                    border:3px solid white;
                    box-shadow:0 0 8px rgba(34,197,94,0.6);
                    display:flex;align-items:center;justify-content:center;
                    font-size:10px;color:white;font-weight:bold">⚡</div>`,
                iconSize: [20, 20],
                iconAnchor: [10, 10],
            });
        }

        // Add all station markers initially
        stations.forEach(station => {
            const marker = L.marker([station.lat, station.lng], {
                icon: getIcon(station)
            }).addTo(map);
            marker.bindPopup(`
                <div style="min-width:200px">
                    <strong>${station.name}</strong><br>
                    <small class="text-muted">${station.address}</small><br><br>
                    <span>🕐 ${station.open_time ?? '-'} - ${station.close_time ?? '-'}</span><br><br>
                    <a href="/station/${station.id}" class="btn btn-sm btn-primary w-100">ดูรายละเอียด</a>
                </div>
            `);
            stationMarkers.push(marker);
        });

        // ===== DISTANCE UTILS =====
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) ** 2 + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(
                dLon / 2) ** 2;
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }

        function updateAllStationDistancesAndSort(userLat, userLng) {
            const elements = document.querySelectorAll('.station-distance');
            elements.forEach(el => {
                const sLat = parseFloat(el.getAttribute('data-lat'));
                const sLng = parseFloat(el.getAttribute('data-lng'));
                const wrapper = el.closest('.station-item-wrapper');
                if (!isNaN(sLat) && !isNaN(sLng)) {
                    const km = calculateDistance(userLat, userLng, sLat, sLng);
                    if (wrapper) wrapper.setAttribute('data-distance', km);
                    let distanceText = km < 1 ? `≈ ${Math.round(km*1000)} ม.` : `≈ ${km.toFixed(1)} กม.`;
                    let timeText = km < 1 ? `(${Math.max(1, Math.round(km*1000/300))} นาที)` :
                        `(${Math.round(km*2)} นาที)`;
                    el.innerHTML = `<i class="bi bi-geo-alt"></i> ${distanceText} ${timeText}`;
                }
            });
            const container = document.getElementById('station-list-container');
            const items = Array.from(container.querySelectorAll('.station-item-wrapper'));
            items.sort((a, b) => parseFloat(a.getAttribute('data-distance')) - parseFloat(b.getAttribute('data-distance')));
            items.forEach(item => container.appendChild(item));
        }

        let userLat = null,
            userLng = null;
        let userMarker = null;

        function goToMyLocation() {
            if (!navigator.geolocation) return alert('เบราว์เซอร์ไม่รองรับ GPS ครับ');
            navigator.geolocation.getCurrentPosition(pos => {
                userLat = pos.coords.latitude;
                userLng = pos.coords.longitude;
                map.setView([userLat, userLng], 15);
                if (userMarker) map.removeLayer(userMarker);
                userMarker = L.marker([userLat, userLng], {
                    icon: L.divIcon({
                        className: '',
                        html: `<div style="background:#4285F4;width:16px;height:16px;border-radius:50%;border:3px solid white;box-shadow:0 0 6px rgba(0,0,0,0.4)"></div>`,
                        iconSize: [16, 16],
                        iconAnchor: [8, 8],
                    })
                }).addTo(map).bindPopup('คุณอยู่ที่นี่').openPopup();
                updateAllStationDistancesAndSort(userLat, userLng);
            });
        }

        // ===== SEARCH (main map) =====
        let searchTimeout = null;
        document.getElementById('search').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            if (query.length < 3) {
                document.getElementById('search-results').innerHTML = '';
                return;
            }
            searchTimeout = setTimeout(() => {
                fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&limit=5&countrycodes=th&accept-language=th`, {
                    headers: {
                        'User-Agent': 'ev-station-app'
                    }
                }).then(res => res.json()).then(results => {
                    const container = document.getElementById('search-results');
                    container.innerHTML = '';
                    results.forEach(item => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'list-group-item list-group-item-action';
                        btn.textContent = item.display_name;
                        btn.addEventListener('click', () => {
                            map.setView([parseFloat(item.lat), parseFloat(item
                                .lon)], 15);
                            document.getElementById('search').value = item
                                .display_name;
                            container.innerHTML = '';
                        });
                        container.appendChild(btn);
                    });
                });
            }, 600);
        });

        goToMyLocation();

        // ===== TRIP MODAL =====
        let selectedRadius = 5;
        let originCoords = null;
        let destCoords = null;
        let gpsActive = false;

        // Radius buttons
        document.querySelectorAll('.trip-radius-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.trip-radius-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                selectedRadius = parseInt(this.getAttribute('data-radius'));
            });
        });

        // GPS button
        document.getElementById('btn-use-gps').addEventListener('click', function() {
            if (!navigator.geolocation) {
                alert('เบราว์เซอร์ไม่รองรับ GPS ครับ');
                return;
            }
            this.innerHTML = '<i class="bi bi-arrow-repeat spin-icon"></i> <span>กำลังดึงตำแหน่ง...</span>';
            navigator.geolocation.getCurrentPosition(pos => {
                userLat = pos.coords.latitude;
                userLng = pos.coords.longitude;
                originCoords = {
                    lat: userLat,
                    lng: userLng,
                    name: 'ตำแหน่งปัจจุบันของคุณ'
                };
                gpsActive = true;
                this.innerHTML = `<i class="bi bi-geo-alt-fill"></i> <span>📍 ${originCoords.name}</span>`;
                this.classList.add('active');
                document.getElementById('origin-input').value = '';
                document.getElementById('origin-input').placeholder = 'ใช้ GPS แล้ว';
            }, () => {
                this.innerHTML = '<i class="bi bi-geo-alt-fill"></i> <span>ใช้ตำแหน่งปัจจุบัน</span>';
                alert('ไม่สามารถดึงตำแหน่ง GPS ได้ครับ');
            });
        });

        // ===== Autocomplete helper =====
        function setupAutocomplete(inputId, resultsId, onSelect) {
            const input = document.getElementById(inputId);
            const results = document.getElementById(resultsId);
            let t = null;
            input.addEventListener('input', function() {
                clearTimeout(t);
                const q = this.value.trim();
                if (q.length < 2) {
                    results.classList.remove('show');
                    results.innerHTML = '';
                    return;
                }
                t = setTimeout(() => {
                    fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(q)}&format=json&limit=5&countrycodes=th&accept-language=th`, {
                        headers: {
                            'User-Agent': 'ev-station-app'
                        }
                    }).then(r => r.json()).then(data => {
                        results.innerHTML = '';
                        if (!data.length) {
                            results.classList.remove('show');
                            return;
                        }
                        data.forEach(item => {
                            const div = document.createElement('div');
                            div.className = 'trip-autocomplete-item';
                            div.innerHTML =
                                `<i class="bi bi-geo-alt text-muted"></i> <span>${item.display_name.substring(0,60)}${item.display_name.length>60?'...':''}</span>`;
                            div.addEventListener('click', () => {
                                onSelect({
                                    lat: parseFloat(item.lat),
                                    lng: parseFloat(item.lon),
                                    name: item.display_name
                                });
                                input.value = item.display_name.substring(0, 60);
                                results.classList.remove('show');
                                results.innerHTML = '';
                            });
                            results.appendChild(div);
                        });
                        results.classList.add('show');
                    });
                }, 500);
            });
            document.addEventListener('click', e => {
                if (!e.target.closest(`#${inputId}`) && !e.target.closest(`#${resultsId}`)) {
                    results.classList.remove('show');
                }
            });
        }

        setupAutocomplete('origin-input', 'origin-results', (coords) => {
            originCoords = coords;
            gpsActive = false;
            document.getElementById('btn-use-gps').classList.remove('active');
            document.getElementById('btn-use-gps').innerHTML =
                '<i class="bi bi-geo-alt-fill"></i> <span>ใช้ตำแหน่งปัจจุบัน</span>';
        });

        setupAutocomplete('dest-input', 'dest-results', (coords) => {
            destCoords = coords;
        });

        // Open trip modal button
        document.getElementById('btn-open-trip').addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('tripModal'));
            modal.show();
        });

        document.getElementById('tripModal').addEventListener('hide.bs.modal', function() {
            document.activeElement?.blur();
        });

        // ===== ROUTE DRAWING =====
        let routeLayer = null;
        let routeStationMarkers = [];

        // Point-to-segment distance (km)
        function distancePointToSegment(lat, lng, lat1, lng1, lat2, lng2) {
            const R = 6371;
            const dx = lat2 - lat1,
                dy = lng2 - lng1;
            if (dx === 0 && dy === 0) return calculateDistance(lat, lng, lat1, lng1);
            const t = Math.max(0, Math.min(1, ((lat - lat1) * dx + (lng - lng1) * dy) / (dx * dx + dy * dy)));
            return calculateDistance(lat, lng, lat1 + t * dx, lng1 + t * dy);
        }

        function findStationsNearRoute(routePoints, radiusKm) {
            return stations.filter(station => {
                for (let i = 0; i < routePoints.length - 1; i++) {
                    const d = distancePointToSegment(
                        station.lat, station.lng,
                        routePoints[i][0], routePoints[i][1],
                        routePoints[i + 1][0], routePoints[i + 1][1]
                    );
                    if (d <= radiusKm) return true;
                }
                return false;
            });
        }

        // ===== PROJECT station onto route → get "progress" t (0.0–1.0) =====
        function projectOnRoute(station, routePoints) {
            let bestT = 0,
                bestDist = Infinity;
            const segLens = [];
            let totalLen = 0;

            for (let i = 0; i < routePoints.length - 1; i++) {
                const d = calculateDistance(
                    routePoints[i][0], routePoints[i][1],
                    routePoints[i + 1][0], routePoints[i + 1][1]
                );
                segLens.push(d);
                totalLen += d;
            }

            let walked = 0;
            for (let i = 0; i < routePoints.length - 1; i++) {
                const [lat1, lng1] = routePoints[i];
                const [lat2, lng2] = routePoints[i + 1];
                const dx = lat2 - lat1,
                    dy = lng2 - lng1;
                const lenSq = dx * dx + dy * dy;
                const tSeg = lenSq === 0 ? 0 : Math.max(0, Math.min(1,
                    ((station.lat - lat1) * dx + (station.lng - lng1) * dy) / lenSq
                ));
                const projLat = lat1 + tSeg * dx;
                const projLng = lng1 + tSeg * dy;
                const d = calculateDistance(station.lat, station.lng, projLat, projLng);
                if (d < bestDist) {
                    bestDist = d;
                    bestT = totalLen > 0 ? (walked + tSeg * segLens[i]) / totalLen : 0;
                }
                walked += segLens[i];
            }
            return bestT;
        }

        // ===== SHEET STATE =====
        let originalSheetHTML = null;

        function updateSheetForRoute(sortedStations, origin) {
            const sheet = document.querySelector('.driver-sheet');
            // Save original content only once
            if (!originalSheetHTML) originalSheetHTML = sheet.innerHTML;

            const driverLat = userLat ?? origin.lat;
            const driverLng = userLng ?? origin.lng;

            let cardsHTML = '';
            sortedStations.forEach((station, idx) => {
                const km = calculateDistance(driverLat, driverLng, station.lat, station.lng);
                const distText = km < 1 ?
                    `≈ ${Math.round(km * 1000)} ม.` :
                    `≈ ${km.toFixed(1)} กม.`;
                const timeText = km < 1 ?
                    `(${Math.max(1, Math.round(km * 1000 / 300))} นาที)` :
                    `(${Math.round(km * 2)} นาที)`;
                const openLabel = station.open_time && station.close_time && station.open_time !== station
                    .close_time ?
                    `${station.open_time.substring(0, 5)} - ${station.close_time.substring(0, 5)}` :
                    '24 ชั่วโมง';
                const imgSrc = station.image ?
                    `/storage/${station.image}` :
                    'https://placehold.co/60x60?text=EV';
                const addressShort = station.address.length > 50 ?
                    station.address.substring(0, 50) + '...' :
                    station.address;

                cardsHTML += `
                <div class="col-12">
                    <a href="/driver/stations/${station.id}"
                       class="driver-card driver-station-card route-station-card text-decoration-none text-dark d-flex align-items-start gap-3 p-3">
                        <div class="flex-shrink-0" style="position:relative;">
                            <img src="${imgSrc}"
                                 alt="Station Logo" class="rounded-3"
                                 style="width:40px;height:40px;object-fit:cover;border:1px solid #eaedf1;">
                            <span class="station-seq-badge">${idx + 1}</span>
                        </div>
                        <div class="flex-grow-1" style="min-width:0;">
                            <h6 class="fw-bold mb-1 text-dark text-truncate" style="font-size:1.05rem;">${station.name}</h6>
                            <div class="text-muted small text-truncate mb-1" style="font-size:0.85rem;">${addressShort}</div>
                            <div class="d-flex align-items-center gap-2 small" style="font-size:0.75rem;">
                                <span class="text-primary fw-medium">เปิด ${openLabel}</span>
                                <span class="text-secondary">|</span>
                                <span style="color:#16a34a;font-weight:600;">
                                    <i class="bi bi-geo-alt-fill"></i> ${distText} ${timeText}
                                </span>
                            </div>
                        </div>
                    </a>
                </div>`;
            });

            const emptyHTML = `
                <div class="col-12 text-center py-5">
                    <div style="font-size:2rem;">⚡</div>
                    <div class="text-muted mt-2" style="font-size:0.9rem;">ไม่พบสถานีในรัศมีที่เลือก</div>
                    <div class="text-muted" style="font-size:0.8rem;">ลองเพิ่มรัศมีค้นหาครับ</div>
                </div>`;

            sheet.innerHTML = `
                <div class="sheet-header d-flex align-items-center justify-content-between mb-2 p-1">
                    <div class="flex-grow-1">
                        <div class="text-uppercase text-secondary small" style="font-weight:500;">
                            ⚡ สถานีในเส้นทาง
                            <span class="fw-bold" style="color:#16a34a;">${sortedStations.length}</span> สถานี
                            · เรียงจากใกล้คุณ
                        </div>
                    </div>
                    <button onclick="clearRoute()"
                            class="btn btn-sm btn-outline-danger py-0 px-2"
                            style="font-size:0.72rem;border-radius:8px;">
                        ✕ ล้างเส้นทาง
                    </button>
                </div>
                <div class="sheet-scroll" style="overflow-y:auto;flex-grow:1;-webkit-overflow-scrolling:touch;">
                    <div class="row g-2">
                        ${cardsHTML.length ? cardsHTML : emptyHTML}
                    </div>
                </div>`;
        }

        function resetSheet() {
            if (originalSheetHTML) {
                document.querySelector('.driver-sheet').innerHTML = originalSheetHTML;
                originalSheetHTML = null;
                // Re-run distance sort if we have GPS
                if (userLat && userLng) updateAllStationDistancesAndSort(userLat, userLng);
            }
        }

        function clearRoute() {
            if (routeLayer) {
                map.removeLayer(routeLayer);
                routeLayer = null;
            }
            routeStationMarkers.forEach(m => map.removeLayer(m));
            routeStationMarkers = [];
            // Restore all station markers
            stationMarkers.forEach(m => {
                if (!map.hasLayer(m)) m.addTo(map);
            });
            document.getElementById('route-banner').classList.remove('show');
            resetSheet();
        }

        document.getElementById('btn-find-route').addEventListener('click', async function() {
            if (!originCoords && !gpsActive) {
                alert('กรุณาระบุต้นทางครับ');
                return;
            }
            if (!destCoords) {
                alert('กรุณาระบุปลายทางครับ');
                return;
            }

            this.disabled = true;
            this.innerHTML = '<i class="bi bi-arrow-repeat spin-icon me-2"></i>กำลังค้นหา...';

            try {
                const origin = originCoords;
                const dest = destCoords;
                const url =
                    `https://router.project-osrm.org/route/v1/driving/${origin.lng},${origin.lat};${dest.lng},${dest.lat}?overview=full&geometries=geojson`;

                const res = await fetch(url);
                const data = await res.json();

                if (!data.routes || !data.routes.length) {
                    alert('ไม่พบเส้นทางครับ ลองเปลี่ยนจุดต้นทาง/ปลายทางใหม่');
                    return;
                }

                clearRoute();

                const coords = data.routes[0].geometry.coordinates;
                // GeoJSON coords are [lng, lat], convert to [lat, lng] for Leaflet
                const routePoints = coords.map(c => [c[1], c[0]]);
                const distanceKm = (data.routes[0].distance / 1000).toFixed(1);
                const durationMin = Math.round(data.routes[0].duration / 60);

                // Draw route polyline
                routeLayer = L.polyline(routePoints, {
                    color: '#2DC653',
                    weight: 5,
                    opacity: 0.85,
                    lineCap: 'round',
                    lineJoin: 'round'
                }).addTo(map);

                // Fit map to route
                map.fitBounds(routeLayer.getBounds(), {
                    padding: [60, 60]
                });

                // Origin marker (A)
                const originIcon = L.divIcon({
                    className: '',
                    html: `<div style="background:#1d4ed8;width:20px;height:20px;border-radius:50%;border:3px solid white;box-shadow:0 0 8px rgba(29,78,216,0.5);display:flex;align-items:center;justify-content:center;font-size:10px;color:white">A</div>`,
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                });
                // Destination marker (B)
                const destIcon = L.divIcon({
                    className: '',
                    html: `<div style="background:#dc2626;width:20px;height:20px;border-radius:50%;border:3px solid white;box-shadow:0 0 8px rgba(220,38,38,0.5);display:flex;align-items:center;justify-content:center;font-size:10px;color:white">B</div>`,
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                });

                const originMarker = L.marker([origin.lat, origin.lng], {
                        icon: originIcon
                    })
                    .addTo(map)
                    .bindPopup(`<strong>ต้นทาง</strong><br>${origin.name.substring(0,60)}`);
                const destMarker = L.marker([dest.lat, dest.lng], {
                        icon: destIcon
                    })
                    .addTo(map)
                    .bindPopup(`<strong>ปลายทาง</strong><br>${dest.name.substring(0,60)}`);
                routeStationMarkers.push(originMarker, destMarker);

                // Find stations near the route
                const nearbyStations = findStationsNearRoute(routePoints, selectedRadius);

                // ===== Sort stations by route progress from origin,
                //       tie-break by distance from driver position =====
                const driverLat = userLat ?? origin.lat;
                const driverLng = userLng ?? origin.lng;

                const sortedNearby = [...nearbyStations].sort((a, b) => {
                    const tA = projectOnRoute(a, routePoints);
                    const tB = projectOnRoute(b, routePoints);
                    // Within first ~15% of route, sort by distance from driver
                    if (Math.abs(tA - tB) < 0.15) {
                        const dA = calculateDistance(driverLat, driverLng, a.lat, a.lng);
                        const dB = calculateDistance(driverLat, driverLng, b.lat, b.lng);
                        return dA - dB;
                    }
                    return tA - tB;
                });

                // Hide all default markers, show only nearby with special icon + numbered popup
                stationMarkers.forEach(m => map.removeLayer(m));

                sortedNearby.forEach((station, idx) => {
                    const m = L.marker([station.lat, station.lng], {
                            icon: getRouteStationIcon()
                        })
                        .addTo(map)
                        .bindPopup(`
                            <div style="min-width:180px">
                                <strong>⚡ #${idx + 1} ${station.name}</strong><br>
                                <small style="color:#6b7280">${station.address.substring(0,50)}</small><br><br>
                                <span>🕐 ${station.open_time ?? '-'} - ${station.close_time ?? '-'}</span><br><br>
                                <a href="/driver/stations/${station.id}" style="display:block;text-align:center;background:#22c55e;color:white;padding:0.4rem;border-radius:8px;text-decoration:none;font-weight:600;font-size:0.85rem;">ดูรายละเอียด</a>
                            </div>
                        `);
                    routeStationMarkers.push(m);
                });

                // Update driver-sheet to show route stations sorted
                updateSheetForRoute(sortedNearby, origin);

                // Show banner
                const banner = document.getElementById('route-banner');
                document.getElementById('route-banner-text').textContent =
                    `📍 ${distanceKm} กม. · ${durationMin} นาที · ⚡ ${sortedNearby.length} สถานีชาร์จในเส้นทาง`;
                banner.classList.add('show');

                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('tripModal')).hide();

            } catch (e) {
                console.error(e);
                alert('เกิดข้อผิดพลาดในการค้นหาเส้นทาง กรุณาลองใหม่ครับ');
            } finally {
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-signpost-2-fill me-2"></i>ค้นหาเส้นทาง';
            }
        });
    </script>

    <style>
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .spin-icon {
            display: inline-block;
            animation: spin 0.8s linear infinite;
        }
    </style>
@endsection
