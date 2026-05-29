@php
    $connectors = $station->connectors ?? collect();
    $facilities = $station->facilities ?? collect();
@endphp

<div class="mb-3">
    <div class="fw-bold">สถานี</div>
    <div class="text-muted">{{ $station->name }} • {{ $station->address }}</div>
    <div class="text-muted small">
        เปิดเวลา: {{ $station->open_time ?? '-' }} - ปิดเวลา: {{ $station->close_time ?? '-' }}
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="border rounded p-3 bg-light">
            <div class="fw-bold mb-2">📍 ตำแหน่ง (พิกัด)</div>
            @if(!empty($station->lat) && !empty($station->lng))
                <div class="mb-3">
                    <div class="text-muted">Lat: <strong>{{ $station->lat }}</strong></div>
                    <div class="text-muted">Lng: <strong>{{ $station->lng }}</strong></div>
                </div>
                <a class="btn btn-sm btn-outline-primary" target="_blank"
                   href="https://www.google.com/maps?q={{ $station->lat }},{{ $station->lng }}&z=15">
                    <i class="bi bi-map"></i> เปิดใน Google Maps
                </a>
            @else
                <div class="text-muted">ไม่มีข้อมูลตำแหน่ง</div>
            @endif
        </div>
    </div>

    <div class="col-lg-6">
        <div class="border rounded p-3 bg-light">
            <div class="fw-bold mb-2">📷 รูปภาพ</div>

            @php
                $galleryImages = collect($station->gallery_images ?? [])
                    ->filter()
                    ->unique()
                    ->values();
                $hasMainImage = !empty($station->image);
            @endphp

            <div class="d-flex flex-wrap gap-2">
                @if($hasMainImage)
                    <div style="width:110px;">
                        <img src="{{ asset('storage/' . $station->image) }}" class="rounded w-100" style="height:90px; object-fit:cover;" />
                    </div>
                @endif

                @if($galleryImages->isNotEmpty())
                    @foreach($galleryImages as $img)
                        <div style="width:110px;">
                            <img src="{{ asset('storage/' . $img) }}" class="rounded w-100" style="height:90px; object-fit:cover;" />
                        </div>
                    @endforeach
                @endif

                @if(!$hasMainImage && $galleryImages->isEmpty())
                    <div class="text-muted">ไม่มีรูปภาพ</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-md-6">
        <div class="border rounded p-3 bg-light mt-0">
            <div class="fw-bold mb-2">หัวชาร์จ</div>
            @if($connectors->isEmpty())
                <div class="text-muted">ไม่มีข้อมูลหัวชาร์จ</div>
            @else
                <ul class="mb-0 ps-3">
                    @foreach($connectors as $connector)
                        <li>
                            {{ $connector->type }} : {{ $connector->total }} หัว
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="col-md-6">
        <div class="border rounded p-3 bg-light mt-0">
            <div class="fw-bold mb-2">สิ่งอำนวยความสะดวก</div>
            @if($facilities->isEmpty())
                <div class="text-muted">ไม่มีข้อมูลสิ่งอำนวยความสะดวก</div>
            @else
                <div class="d-flex flex-wrap gap-2">
                    @foreach($facilities as $facility)
                        <span class="badge bg-secondary">{{ $facility->name }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>


