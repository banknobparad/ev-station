@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">

    {{-- ช่องค้นหา --}}
    <div class="p-3 bg-white shadow-sm">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <input type="text" id="search" class="form-control" placeholder="ค้นหาสถานที่...">
                    <div id="search-results" class="list-group mt-1 position-absolute" style="z-index:9999; width:60%"></div>
                </div>
                <div class="col-md-4 mt-2 mt-md-0">
                    <button class="btn btn-primary w-100" onclick="goToMyLocation()">
                        📍 ตำแหน่งของฉัน
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- แผนที่ --}}
    <div id="map" style="height: calc(100vh - 130px);"></div>

</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // ข้อมูลสถานีจาก Laravel
    const stations = @json($stations);

    const map = L.map('map').setView([13.7563, 100.5018], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Icon สีตามสถานะ
    function getIcon(station) {
        const connectors = station.connectors;
        const hasAvailable = connectors.some(c => c.status === 'available');
        const allMaintenance = connectors.every(c => c.status === 'maintenance');

        let color = 'green';
        if (allMaintenance) color = 'red';
        else if (!hasAvailable) color = 'orange';

        return L.divIcon({
            className: '',
            html: `<div style="
                background:${color};
                width:16px; height:16px;
                border-radius:50%;
                border:2px solid white;
                box-shadow:0 0 4px rgba(0,0,0,0.4)">
            </div>`,
            iconSize: [16, 16],
            iconAnchor: [8, 8],
        });
    }

    // วางหมุดสถานีทั้งหมด
    stations.forEach(station => {
        const marker = L.marker([station.lat, station.lng], { icon: getIcon(station) }).addTo(map);

        // Popup
        const available = station.connectors.filter(c => c.status === 'available').length;
        const total = station.connectors.length;

        marker.bindPopup(`
            <div style="min-width:200px">
                <strong>${station.name}</strong><br>
                <small class="text-muted">${station.address}</small><br><br>
                <span>🔌 หัวชาร์จว่าง: ${available}/${total}</span><br>
                <span>🕐 ${station.open_time ?? '-'} - ${station.close_time ?? '-'}</span><br><br>
                <a href="/station/${station.id}" class="btn btn-sm btn-primary w-100">ดูรายละเอียด</a>
            </div>
        `);
    });

    // ปุ่มตำแหน่งของฉัน
    function goToMyLocation() {
        if (!navigator.geolocation) return alert('เบราว์เซอร์ไม่รองรับ GPS ครับ');
        navigator.geolocation.getCurrentPosition(pos => {
            const { latitude: lat, longitude: lng } = pos.coords;
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
        });
    }

    // ค้นหาสถานที่
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
                headers: { 'User-Agent': 'ev-station-app' }
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
                        map.setView([parseFloat(item.lat), parseFloat(item.lon)], 15);
                        document.getElementById('search').value = item.display_name;
                        container.innerHTML = '';
                    });
                    container.appendChild(btn);
                });
            });
        }, 600);
    });

    // ไปตำแหน่งปัจจุบันตอนเปิดหน้า
    goToMyLocation();
</script>
@endsection