<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EV Station — ค้นหาสถานีชาร์จรถไฟฟ้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body { margin: 0; font-family: 'Inter', sans-serif; background: #fff; }

        /* NAVBAR */
        .home-nav {
            position: fixed; top: 0; left: 0; right: 0;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--ev-border);
            padding: 0.75rem 1.5rem;
            display: flex; align-items: center; justify-content: space-between;
            z-index: 1000;
            box-shadow: var(--ev-shadow);
        }

        .home-map {
            height: 100vh;
            margin-top: 0;
        }

        /* Search Box ลอยบนแผนที่ */
        .map-search-box {
            position: absolute;
            top: 80px; left: 50%; transform: translateX(-50%);
            z-index: 999;
            width: 90%; max-width: 500px;
        }

        .map-search-box input {
            border-radius: 50px;
            padding: 0.75rem 1.5rem;
            border: none;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            font-size: 0.95rem;
            width: 100%;
        }

        .map-search-box input:focus {
            outline: none;
            box-shadow: 0 4px 20px rgba(45,198,83,0.3);
        }

        /* Stat bar ลอยด้านล่าง */
        .map-stat-bar {
            position: absolute;
            bottom: 30px; left: 50%; transform: translateX(-50%);
            z-index: 999;
            background: white;
            border-radius: 20px;
            padding: 1rem 2rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
            display: flex; gap: 2rem; align-items: center;
        }

        .stat-item { text-align: center; }
        .stat-item .num { font-size: 1.5rem; font-weight: 700; color: var(--ev-green); }
        .stat-item .lbl { font-size: 0.75rem; color: var(--ev-muted); }

        /* CTA Buttons */
        .btn-ev-lg {
            padding: 0.7rem 1.8rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex; align-items: center; gap: 0.4rem;
        }

        .btn-ev-lg.primary {
            background: var(--ev-green);
            color: #fff;
            border: none;
        }

        .btn-ev-lg.primary:hover {
            background: var(--ev-green-dark);
            color: #fff;
            transform: translateY(-1px);
        }

        .btn-ev-lg.outline {
            background: transparent;
            color: var(--ev-green);
            border: 2px solid var(--ev-green);
        }

        .btn-ev-lg.outline:hover {
            background: var(--ev-green-light);
        }

        .search-results-dropdown {
            position: absolute;
            top: 100%; left: 0; right: 0;
            background: white;
            border-radius: 16px;
            margin-top: 8px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            overflow: hidden;
            z-index: 9999;
        }
    </style>
</head>
<body>

{{-- Navbar --}}
<nav class="home-nav">
    <a class="navbar-brand-ev" href="/">
        <i class="bi bi-lightning-charge-fill"></i> EV<span>Station</span>
    </a>

    @guest
        <div class="d-flex gap-2">
            <a href="{{ route('login') }}" class="btn-ev-lg outline">
                <i class="bi bi-person"></i> เข้าสู่ระบบ
            </a>
            <a href="{{ route('register.phone') }}" class="btn-ev-lg primary">
                <i class="bi bi-person-plus"></i> สมัครสมาชิก
            </a>
        </div>
    @else
        <div class="d-flex gap-2">
            @if(auth()->user()->role === 'driver')
                <a href="{{ route('driver.map') }}" class="btn-ev-lg outline">
                    <i class="bi bi-map"></i> แผนที่ของฉัน
                </a>
            @elseif(auth()->user()->role === 'provider')
                <a href="{{ route('provider.dashboard') }}" class="btn-ev-lg outline">
                    <i class="bi bi-speedometer2"></i> แดชบอร์ด
                </a>
            @elseif(auth()->user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="btn-ev-lg outline">
                    <i class="bi bi-grid"></i> แดชบอร์ด
                </a>
            @endif
        </div>
    @endguest
</nav>

{{-- แผนที่เต็มหน้า --}}
<div style="position:relative">
    <div id="map" class="home-map"></div>

    {{-- Search Box ลอยบนแผนที่ --}}
    <div class="map-search-box">
        <div style="position:relative">
            <input type="text" id="search" placeholder="🔍 ค้นหาสถานีหรือสถานที่...">
            <div id="search-results" class="search-results-dropdown" style="display:none"></div>
        </div>
    </div>

    {{-- Stat Bar --}}
    <div class="map-stat-bar">
        <div class="stat-item">
            <div class="num">{{ $totalStations }}</div>
            <div class="lbl">สถานีทั้งหมด</div>
        </div>
        <div style="width:1px; height:40px; background:#eee"></div>
        <div class="stat-item">
            <div class="num" style="color:#2563EB">
                {{ $stations->count() }}
            </div>
            <div class="lbl">สถานีมีหัวชาร์จ</div>

        </div>
        <div style="width:1px; height:40px; background:#eee"></div>
        <div class="stat-item">
            <div class="num" style="color:#D97706">-</div>
            <div class="lbl">กำลังใช้งาน (ตัดสถานะ)</div>

        </div>
        <div style="display:none" class="d-md-block">
            <a href="{{ route('register.phone') }}" class="btn-ev-lg primary">
                <i class="bi bi-lightning-charge-fill"></i> เริ่มใช้งาน
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const stations = @json($stations);

    const map = L.map('map', { zoomControl: false }).setView([13.7563, 100.5018], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    L.control.zoom({ position: 'bottomright' }).addTo(map);

    function getIcon(station) {
        return L.divIcon({


            className: '',
            html: `<div style="
                    background:#4285F4; color:white;

                width:36px; height:36px; border-radius:50%;
                display:flex; align-items:center; justify-content:center;
                border:3px solid white;
                box-shadow:0 2px 8px rgba(0,0,0,0.25);
                font-size:16px;">⚡</div>`,
            iconSize: [36, 36],
            iconAnchor: [18, 18],
        });
    }

    stations.forEach(station => {
        const marker = L.marker([station.lat, station.lng], { icon: getIcon(station) }).addTo(map);


        marker.bindPopup(`
            <div style="min-width:200px; font-family:Inter,sans-serif">
                <strong style="font-size:1rem">${station.name}</strong><br>
                <small style="color:#6B7280">${station.address}</small><br><br>

                <span>🕐 ${station.open_time ?? '-'} - ${station.close_time ?? '-'}</span><br><br>
                <a href="/login" style="
                    display:block; text-align:center;
                    background:#2DC653; color:white;
                    padding:0.5rem; border-radius:10px;
                    text-decoration:none; font-weight:600;">
                    เข้าสู่ระบบเพื่อดูรายละเอียด
                </a>
            </div>
        `);
    });

    // ไปตำแหน่งผู้ใช้
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
            map.setView([pos.coords.latitude, pos.coords.longitude], 14);
            L.marker([pos.coords.latitude, pos.coords.longitude], {
                icon: L.divIcon({
                    className: '',
                    html: `<div style="
                        background:#4285F4; width:16px; height:16px;
                        border-radius:50%; border:3px solid white;
                        box-shadow:0 0 6px rgba(0,0,0,0.4)"></div>`,
                    iconSize: [16, 16], iconAnchor: [8, 8],
                })
            }).addTo(map).bindPopup('คุณอยู่ที่นี่').openPopup();
        });
    }

    // ค้นหา
    let timeout = null;
    document.getElementById('search').addEventListener('input', function() {
        clearTimeout(timeout);
        const q = this.value.trim();
        const results = document.getElementById('search-results');

        if (q.length < 2) { results.style.display = 'none'; return; }

        // ค้นหาสถานีในระบบก่อน
        const matched = stations.filter(s =>
            s.name.toLowerCase().includes(q.toLowerCase()) ||
            s.address.toLowerCase().includes(q.toLowerCase())
        );

        timeout = setTimeout(() => {
            results.innerHTML = '';
            results.style.display = 'block';

            if (matched.length > 0) {
                matched.slice(0, 3).forEach(s => {
                    const item = document.createElement('div');
                    item.style.cssText = 'padding:0.75rem 1rem; cursor:pointer; border-bottom:1px solid #f0f0f0; display:flex; align-items:center; gap:0.5rem;';
                    item.innerHTML = `<span style="color:#2DC653">⚡</span><div><div style="font-weight:600">${s.name}</div><div style="font-size:0.8rem;color:#6B7280">${s.address.substring(0,50)}...</div></div>`;
                    item.addEventListener('click', () => {
                        map.setView([s.lat, s.lng], 16);
                        document.getElementById('search').value = s.name;
                        results.style.display = 'none';
                    });
                    results.appendChild(item);
                });
            }

            // ค้นหาสถานที่ด้วย Nominatim
            fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(q)}&format=json&limit=3&countrycodes=th`, {
                headers: { 'User-Agent': 'ev-station-app' }
            }).then(r => r.json()).then(data => {
                data.forEach(item => {
                    const div = document.createElement('div');
                    div.style.cssText = 'padding:0.75rem 1rem; cursor:pointer; border-bottom:1px solid #f0f0f0; display:flex; align-items:center; gap:0.5rem;';
                    div.innerHTML = `<span>📍</span><div style="font-size:0.85rem">${item.display_name.substring(0,60)}...</div>`;
                    div.addEventListener('click', () => {
                        map.setView([parseFloat(item.lat), parseFloat(item.lon)], 15);
                        document.getElementById('search').value = item.display_name;
                        results.style.display = 'none';
                    });
                    results.appendChild(div);
                });
            });
        }, 400);
    });

    document.addEventListener('click', e => {
        if (!e.target.closest('.map-search-box')) {
            document.getElementById('search-results').style.display = 'none';
        }
    });
</script>
</body>
</html>
