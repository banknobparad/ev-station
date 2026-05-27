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
            <button type="button" class="sheet-icon-btn" aria-label="Info">
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

            <div class="d-flex align-items-center justify-content-between bg-light rounded-3 p-1 mb-2" style="font-size: 0.85rem;">
                <button type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 py-1"
                    onclick="window.location.reload();">
                    <i class="bi bi-arrow-clockwise"></i> อัพเดทข้อมูล
                </button>
                <div class="text-muted">
                    ข้อมูลล่าสุด ณ {{ now()->format('H:i') }} น.
                </div>
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

                                        <span class="station-distance" data-lat="{{ $station->lat }}" data-lng="{{ $station->lng }}">
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

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        const stations = @json($stations);

        const map = L.map('map', {
            zoomControl: false,
            attributionControl: false
        }).setView([13.7563, 100.5018], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: ''
        }).addTo(map);

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

        });

        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a =
                Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }

        // 🆕 ฟังก์ชันอัพเดทระยะทาง + สั่งเรียงลำดับการ์ดจากใกล้ไปไกล
        function updateAllStationDistancesAndSort(userLat, userLng) {
            const elements = document.querySelectorAll('.station-distance');

            // 1. คำนวณระยะทางและฝังค่าลงในการ์ดแต่ละใบก่อน
            elements.forEach(el => {
                const sLat = parseFloat(el.getAttribute('data-lat'));
                const sLng = parseFloat(el.getAttribute('data-lng'));
                const wrapper = el.closest('.station-item-wrapper'); // หาตัว div ครอบนอกสุดของการ์ดนั้นๆ

                if (!isNaN(sLat) && !isNaN(sLng)) {
                    const km = calculateDistance(userLat, userLng, sLat, sLng);

                    // บันทึกระยะทางจริงเก็บไว้ที่ตัว wrapper element
                    if (wrapper) {
                        wrapper.setAttribute('data-distance', km);
                    }

                    let distanceText = "";
                    let timeText = "";

                    if (km < 1) {
                        const meters = Math.round(km * 1000);
                        distanceText = `≈ ${meters} ม.`;
                        timeText = `(${Math.max(1, Math.round(meters / 300))} นาที)`;
                    } else {
                        distanceText = `≈ ${km.toFixed(1)} กม.`;
                        timeText = `(${Math.round(km * 2)} นาที)`;
                    }
                    el.innerHTML = `<i class="bi bi-geo-alt"></i> ${distanceText} ${timeText}`;
                }
            });

            // 2. 🆕 สั่งเรียงลำดับการ์ดใน Container ใหม่ (เอาใกล้สุดขึ้นก่อน)
            const container = document.getElementById('station-list-container');
            const items = Array.from(container.querySelectorAll('.station-item-wrapper'));

            items.sort((a, b) => {
                const distA = parseFloat(a.getAttribute('data-distance'));
                const distB = parseFloat(b.getAttribute('data-distance'));
                return distA - distB; // เรียงจากน้อยไปมาก (ใกล้ -> ไกล)
            });

            // เอารายการที่เรียงแล้วใส่กลับเข้าไปใน HTML Container
            items.forEach(item => container.appendChild(item));
        }

        function goToMyLocation() {
            if (!navigator.geolocation) return alert('เบราว์เซอร์ไม่รองรับ GPS ครับ');
            navigator.geolocation.getCurrentPosition(pos => {
                const {
                    latitude: lat,
                    longitude: lng
                } = pos.coords;
                map.setView([lat, lng], 15);
                L.marker([lat, lng], {
                    icon: L.divIcon({
                        className: '',
                        html: `<div style="
                        background:#4285F4;
                        width:16px; height:16px;
                        border-radius:50%;
                        border:3px solid white;
                        box-shadow:0 0 6px rgba(0,0,0,0.4)">
                    </div>`,
                        iconSize: [16, 16],
                        iconAnchor: [8, 8],
                    })
                }).addTo(map).bindPopup('คุณอยู่ที่นี่').openPopup();

                // 🆕 เรียกฟังก์ชันคำนวณระยะทางพร้อมเรียงลำดับการ์ดจากใกล้ไปไกล
                updateAllStationDistancesAndSort(lat, lng);
            });
        }

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
                    })
                    .then(res => res.json())
                    .then(results => {
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
    </script>
@endsection
