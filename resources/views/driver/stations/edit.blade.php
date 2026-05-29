@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
@endpush

@section('content')
<div class="container">

    <div class="driver-page-header d-flex align-items-center justify-content-between mb-3">
        <a href="{{ route('driver.account') }}" class="driver-back-btn text-decoration-none">
            <i class="bi bi-chevron-left"></i>
        </a>
        <div class="flex-grow-1 text-center">
            <div class="driver-page-title">แก้ไขสถานีชาร์จ</div>
            <div class="driver-page-subtitle">ข้อมูลของคุณจะถูกส่งให้ Admin ตรวจสอบ</div>
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

    <div class="driver-card mb-4" style="clear:both;position:relative;z-index:1;">
        <div class="driver-card-header">
            <h5 class="mb-0">ปักหมุดบนแผนที่ <small class="text-muted">(คลิกบนแผนที่เพื่อเลือกตำแหน่ง)</small></h5>
        </div>
        <div class="driver-card-body p-0">
            <div id="map" style="height:320px;border-radius:0 0 8px 8px;position:relative;z-index:1;"></div>
        </div>
    </div>

    <div class="driver-card driver-form-card mb-4">
        <div class="driver-card-body">
            <form action="{{ route('driver.stations.update', $station) }}" method="POST"
                enctype="multipart/form-data" class="driver-form">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">ชื่อสถานี</label>
                    <input type="text" name="name" class="form-control"
                        value="{{ old('name', $station->name) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">ค้นหาสถานที่</label>
                    <input type="text" id="search" class="form-control"
                        placeholder="พิมพ์ชื่อสถานที่..." autocomplete="off">
                    <div id="search-results" class="list-group mt-1"
                        style="position:absolute;width:100%;z-index:1050;"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label">ที่อยู่</label>
                    <textarea name="address" id="address" class="form-control"
                        required>{{ old('address', $station->address) }}</textarea>
                </div>

                <div class="driver-form-section">
                    <div class="mb-3">
                        <label class="form-label">Latitude</label>
                        <input type="text" name="lat" id="lat" class="form-control"
                            value="{{ old('lat', $station->lat) }}" required readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Longitude</label>
                        <input type="text" name="lng" id="lng" class="form-control"
                            value="{{ old('lng', $station->lng) }}" required readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">เวลาเปิด</label>
                        <input type="time" name="open_time" class="form-control"
                            value="{{ old('open_time', $station->open_time) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">เวลาปิด</label>
                        <input type="time" name="close_time" class="form-control"
                            value="{{ old('close_time', $station->close_time) }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">รูปภาพสถานี</label>
                    @if($station->image)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $station->image) }}"
                                style="max-width:220px" class="rounded"/>
                        </div>
                    @endif
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <div class="form-text">อัปโหลดเพื่อแทนรูปเดิม (ถ้าไม่เลือกจะไม่เปลี่ยน)</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">รูปภาพเพิ่มเติม (แกลลอรี)</label>
                    <input type="file" name="gallery_images[]" class="form-control"
                        accept="image/*" multiple>
                    <div class="form-text">อัปโหลดเพื่อเพิ่มรูปในแกลลอรี (ไม่แทนของเดิม)</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">สิ่งอำนวยความสะดวก</label>
                    <div class="row">
                        @foreach($facilities as $facility)
                            @php
                                $checked = in_array(
                                    $facility->id,
                                    old('facilities', $station->facilities->pluck('id')->all())
                                );
                            @endphp
                            <div class="col-md-6 col-lg-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        name="facilities[]" value="{{ $facility->id }}"
                                        id="facility_{{ $facility->id }}"
                                        {{ $checked ? 'checked' : '' }}>
                                    <label class="form-check-label"
                                        for="facility_{{ $facility->id }}">{{ $facility->name }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                @include('driver.stations._connectors_fields', ['connectorRows' => $connectorRows])

                <div class="driver-form-actions">
                    <button type="submit" class="btn btn-primary w-100 driver-submit-btn">
                        <i class="bi bi-save me-2"></i>บันทึกการแก้ไข
                    </button>
                    <a href="{{ route('driver.account') }}"
                        class="btn btn-secondary w-100 driver-cancel-btn">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>

    {{-- inject PHP values as plain HTML data attributes so JS never touches Blade syntax --}}
    <div id="map-config"
        data-lat="{{ $station->lat }}"
        data-lng="{{ $station->lng }}"
        style="display:none;"></div>

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    var cfg    = document.getElementById('map-config');
    var initLat = parseFloat(cfg.dataset.lat);
    var initLng = parseFloat(cfg.dataset.lng);

    var map = L.map('map').setView([initLat, initLng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: 'OpenStreetMap contributors'
    }).addTo(map);
    setTimeout(function () { map.invalidateSize(); }, 200);

    var marker = L.marker([initLat, initLng], { draggable: true }).addTo(map);

    function syncLatLng(lat, lng) {
        document.getElementById('lat').value = lat.toFixed(7);
        document.getElementById('lng').value = lng.toFixed(7);
    }
    syncLatLng(initLat, initLng);

    function fetchAddress(lat, lng) {
        fetch('https://nominatim.openstreetmap.org/reverse?lat=' + lat + '&lon=' + lng + '&format=json', {
            headers: { 'Accept-Language': 'th', 'User-Agent': 'ev-station-app' }
        })
        .then(function (r) { return r.json(); })
        .then(function (d) { document.getElementById('address').value = d.display_name || ''; });
    }

    marker.on('dragend', function (e) {
        var p = e.target.getLatLng();
        syncLatLng(p.lat, p.lng);
        fetchAddress(p.lat, p.lng);
    });

    map.on('click', function (e) {
        if (marker) map.removeLayer(marker);
        marker = L.marker([e.latlng.lat, e.latlng.lng], { draggable: true }).addTo(map);
        syncLatLng(e.latlng.lat, e.latlng.lng);
        fetchAddress(e.latlng.lat, e.latlng.lng);
        marker.on('dragend', function (ev) {
            var p = ev.target.getLatLng();
            syncLatLng(p.lat, p.lng);
            fetchAddress(p.lat, p.lng);
        });
    });

    var searchTimeout = null;
    document.getElementById('search').addEventListener('input', function () {
        clearTimeout(searchTimeout);
        var query = this.value.trim();
        if (query.length < 3) {
            document.getElementById('search-results').innerHTML = '';
            return;
        }
        searchTimeout = setTimeout(function () {
            fetch('https://nominatim.openstreetmap.org/search?q=' + encodeURIComponent(query) + '&format=json&limit=5&countrycodes=th&accept-language=th', {
                headers: { 'User-Agent': 'ev-station-app' }
            })
            .then(function (r) { return r.json(); })
            .then(function (results) {
                var container = document.getElementById('search-results');
                container.innerHTML = '';
                if (!results.length) {
                    container.innerHTML = '<div class="list-group-item text-muted">ไม่พบสถานที่ครับ</div>';
                    return;
                }
                results.forEach(function (item) {
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'list-group-item list-group-item-action';
                    btn.textContent = item.display_name;
                    btn.addEventListener('click', function () {
                        var lat = parseFloat(item.lat);
                        var lng = parseFloat(item.lon);
                        map.setView([lat, lng], 16);
                        if (marker) map.removeLayer(marker);
                        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                        syncLatLng(lat, lng);
                        document.getElementById('address').value = item.display_name;
                        document.getElementById('search').value  = item.display_name;
                        container.innerHTML = '';
                        setTimeout(function () { map.invalidateSize(); }, 100);
                        marker.on('dragend', function (e) {
                            var p = e.target.getLatLng();
                            syncLatLng(p.lat, p.lng);
                            fetchAddress(p.lat, p.lng);
                        });
                    });
                    container.appendChild(btn);
                });
            })
            .catch(function () {
                document.getElementById('search-results').innerHTML =
                    '<div class="list-group-item text-danger">เกิดข้อผิดพลาด ลองใหม่อีกครั้งครับ</div>';
            });
        }, 600);
    });

}());
</script>
@endpush
