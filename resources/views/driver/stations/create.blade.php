@extends('layouts.app')

@section('content')
<div class="container">
    <div class="driver-page-header d-flex align-items-center justify-content-between mb-3">
        <a href="{{ route('driver.account') }}" class="driver-back-btn text-decoration-none">
            <i class="bi bi-chevron-left"></i>
        </a>
        <div class="flex-grow-1 text-center">
            <div class="driver-page-title">เพิ่มสถานีชาร์จ</div>
            <div class="driver-page-subtitle">รออนุมัติจาก Admin</div>
        </div>
        <div style="width:32px"></div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- 📍 แผนที่สำหรับเลือกตำแหน่ง (เหมือนฝั่ง provider แต่จัดให้ mobile-first) --}}
<div class="driver-card mb-4" style="clear: both; position: relative; z-index: 1;">
        <div class="driver-card-header">
            <h5 class="mb-0">ปักหมุดบนแผนที่ <small class="text-muted">(คลิกบนแผนที่เพื่อเลือกตำแหน่ง)</small></h5>
        </div>
        <div class="driver-card-body p-0">
            <div id="map" style="height: 320px; border-radius: 0 0 8px 8px; position: relative; z-index: 1;"></div>
        </div>
    </div>

<div class="driver-card driver-form-card mb-4">
        <div class="driver-card-body">
            <form action="{{ route('driver.stations.store') }}" method="POST" enctype="multipart/form-data" class="driver-form">
                @csrf


                <div class="mb-3">
                    <label class="form-label">ชื่อสถานี</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>

                {{-- ช่องค้นหาสถานที่ --}}
                <div class="mb-3">
                    <label class="form-label">ค้นหาสถานที่</label>
                    <input type="text" id="search" class="form-control" placeholder="พิมพ์ชื่อสถานที่..." autocomplete="off">
                    <div id="search-results" class="list-group mt-1" style="position: absolute; width: 100%; z-index: 1050;"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label">ที่อยู่</label>
                    <textarea name="address" id="address" class="form-control" required>{{ old('address') }}</textarea>
                </div>

<div class="driver-form-section">
                    <div class="mb-3">
                        <label class="form-label">Latitude</label>
                        <input type="text" name="lat" id="lat" class="form-control" value="{{ old('lat') }}" required readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Longitude</label>
                        <input type="text" name="lng" id="lng" class="form-control" value="{{ old('lng') }}" required readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">เวลาเปิด</label>
                        <input type="time" name="open_time" class="form-control" value="{{ old('open_time') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">เวลาปิด</label>
                        <input type="time" name="close_time" class="form-control" value="{{ old('close_time') }}">
                    </div>
                </div>


                <div class="mb-3">
                    <label class="form-label">รูปภาพสถานี</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>

                <div class="mb-3">
                    <label class="form-label">รูปภาพเพิ่มเติม</label>
                    <input type="file" name="gallery_images[]" class="form-control" accept="image/*" multiple>
                    <div class="form-text">สามารถเลือกได้หลายไฟล์ เพื่อให้แสดงในแกลเลอรีของสถานี</div>
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

                <div class="driver-form-actions">
                    <button type="submit" class="btn btn-primary w-100 driver-submit-btn">
                        <i class="bi bi-send me-2"></i>ส่งคำขอ
                    </button>
                    <a href="{{ route('driver.account') }}" class="btn btn-secondary w-100 driver-cancel-btn">
                        ยกเลิก
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- Leaflet CSS & JS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    const map = L.map('map').setView([13.7563, 100.5018], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // แก้ปัญหา map ขนาดเพี้ยนบนมือถือ
    setTimeout(function() {
        map.invalidateSize();
    }, 200);

    let marker = null;

    // 📍 เริ่มต้นด้วยตำแหน่งปัจจุบันถ้าได้
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(pos) {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            map.setView([lat, lng], 15);
            placeMarker(lat, lng);
            fetchAddress(lat, lng);
            map.invalidateSize();
        }, function() {
            // ignore
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


