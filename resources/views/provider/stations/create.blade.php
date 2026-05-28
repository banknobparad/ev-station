@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">เพิ่มสถานีใหม่</h4>

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- 📍 Card สำหรับแผนที่ (ย้ายมาไว้ด้านบน) --}}
    <div class="card mb-4" style="clear: both; position: relative; z-index: 1;">
        <div class="card-header">
            <h5 class="mb-0">ปักหมุดบนแผนที่ <small class="text-muted">(คลิกบนแผนที่เพื่อเลือกตำแหน่ง)</small></h5>
        </div>
        <div class="card-body p-0">
            <div id="map" style="height: 400px; border-radius: 0 0 8px 8px; position: relative; z-index: 1;"></div>
        </div>
    </div>

    {{-- 📝 ฟอร์มกรอกข้อมูล (อยู่ด้านล่างแผนที่อย่างเป็นระเบียบ) --}}
    <form action="{{ route('provider.stations.store') }}" method="POST" enctype="multipart/form-data" style="position: relative; z-index: 10;">
        @csrf
        <div class="mb-3">
            <label class="form-label">ชื่อสถานี</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>

        {{-- ช่องค้นหาสถานที่ --}}
        <div class="mb-3">
            <label class="form-label">ค้นหาสถานที่</label>
            <input type="text" id="search" class="form-control" placeholder="พิมพ์ชื่อสถานที่...">
            <div id="search-results" class="list-group mt-1" style="position: absolute; width: 100%; z-index: 1050;"></div>
        </div>

        <div class="mb-3">
            <label class="form-label">ที่อยู่</label>
            <textarea name="address" id="address" class="form-control" required>{{ old('address') }}</textarea>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Latitude</label>
                <input type="text" name="lat" id="lat" class="form-control" value="{{ old('lat') }}" required readonly>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Longitude</label>
                <input type="text" name="lng" id="lng" class="form-control" value="{{ old('lng') }}" required readonly>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">เวลาเปิด</label>
                <input type="time" name="open_time" class="form-control" value="{{ old('open_time') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">เวลาปิด</label>
                <input type="time" name="close_time" class="form-control" value="{{ old('close_time') }}">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">รูปภาพสถานี</label>
            <input type="file" name="image" class="form-control" accept="image/*">
        </div>

        <div class="mb-3">
            <label class="form-label">สิ่งอำนวยความสะดวก</label>
            <div class="row">
                @foreach($facilities as $facility)
                    <div class="col-md-6 col-lg-4 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="facilities[]"
                                   value="{{ $facility->id }}" id="facility_{{ $facility->id }}">
                            <label class="form-check-label" for="facility_{{ $facility->id }}">
                                {{ $facility->name }}
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <button type="submit" class="btn btn-success">บันทึก</button>
        <a href="{{ route('provider.stations.index') }}" class="btn btn-secondary">ยกเลิก</a>
    </form>
</div>

{{-- Leaflet CSS & JS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    const map = L.map('map').setView([13.7563, 100.5018], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // 🔥 บั๊กแก้แผนที่ลอยทับ/เบี้ยว: สั่งให้รีเซ็ตขนาดแผนที่หลังจากโหลดหน้าเว็บเสร็จทันที
    setTimeout(function() {
        map.invalidateSize();
    }, 200);

    let marker = null;

    // 📍 ระบุตำแหน่งปัจจุบัน
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(pos) {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            map.setView([lat, lng], 15);
            placeMarker(lat, lng);
            fetchAddress(lat, lng);
            map.invalidateSize(); // รีเซ็ตขนาดอีกครั้งหลังได้ตำแหน่ง
        }, function() {
            console.log('ไม่สามารถดึงตำแหน่งได้');
        });
    }

    // คลิกบนแผนที่
    map.on('click', function(e) {
        placeMarker(e.latlng.lat, e.latlng.lng);
        fetchAddress(e.latlng.lat, e.latlng.lng);
    });

    function placeMarker(lat, lng) {
        if (marker) map.removeLayer(marker);
        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        document.getElementById('lat').value = lat.toFixed(7);
        document.getElementById('lng').value = lng.toFixed(7);

        marker.on('dragend', function(e) {
            const pos = e.target.getLatLng();
            document.getElementById('lat').value = pos.lat.toFixed(7);
            document.getElementById('lng').value = pos.lng.toFixed(7);
            fetchAddress(pos.lat, pos.lng);
        });
    }

    // Reverse Geocoding — ดึงที่อยู่จาก lat/lng
    function fetchAddress(lat, lng) {
        fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`, {
            headers: { 'Accept-Language': 'th', 'User-Agent': 'ev-station-app' }
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('address').value = data.display_name || '';
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

                if (results.length === 0) {
                    container.innerHTML = '<div class="list-group-item text-muted">ไม่พบสถานที่ครับ</div>';
                    return;
                }

                results.forEach(item => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'list-group-item list-group-item-action';
                    btn.textContent = item.display_name;
                    btn.addEventListener('click', () => {
                        const lat = parseFloat(item.lat);
                        const lng = parseFloat(item.lon);
                        map.setView([lat, lng], 16);
                        placeMarker(lat, lng);
                        document.getElementById('address').value = item.display_name;
                        document.getElementById('search').value = item.display_name;
                        container.innerHTML = '';
                        setTimeout(() => map.invalidateSize(), 100);
                    });
                    container.appendChild(btn);
                });
            })
            .catch(() => {
                document.getElementById('search-results').innerHTML =
                    '<div class="list-group-item text-danger">เกิดข้อผิดพลาด ลองใหม่อีกครั้งครับ</div>';
            });
        }, 600);
    });
</script>
@endsection
