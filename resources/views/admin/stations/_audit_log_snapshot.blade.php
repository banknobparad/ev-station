@php
    $data = $data ?? [];
    $title = $title ?? 'ข้อมูลสถานี';
    $changedFields = $changedFields ?? [];
    $facilityIds = collect($data['facilities'] ?? [])->filter()->unique();
    $connectors = collect($data['connectors'] ?? []);
    $gallery = collect($data['gallery_images'] ?? [])->filter()->unique();

    $fieldChanged = fn (string $key) => in_array($key, $changedFields, true);
    $rowClass = fn (string $key) => $fieldChanged($key) ? 'audit-diff-row rounded px-2 py-2 mb-1' : 'mb-1';
    $labelClass = fn (string $key) => $fieldChanged($key) ? 'audit-diff-label' : '';
    $valueClass = fn (string $key) => $fieldChanged($key) ? 'audit-diff-value' : '';
@endphp

@once
    <style>
        .audit-diff-row {
            background: rgba(255, 193, 7, 0.18);
            border-left: 3px solid #ffc107;
        }
        .audit-diff-label {
            font-weight: 600;
            color: #856404;
        }
        .audit-diff-value {
            font-weight: 500;
        }
        .audit-diff-badge {
            font-size: 0.65rem;
            vertical-align: middle;
        }
    </style>
@endonce

<div class="border rounded p-3 bg-light h-100">
    <div class="fw-bold mb-3">{{ $title }}</div>

    <div class="small">
        <div class="row {{ $rowClass('name') }}">
            <div class="col-sm-4 {{ $labelClass('name') }}">
                ชื่อสถานี
                @if($fieldChanged('name'))
                    <span class="badge bg-warning text-dark audit-diff-badge ms-1">แก้ไข</span>
                @endif
            </div>
            <div class="col-sm-8 {{ $valueClass('name') }}">{{ $data['name'] ?? '-' }}</div>
        </div>

        <div class="row {{ $rowClass('address') }}">
            <div class="col-sm-4 {{ $labelClass('address') }}">
                ที่อยู่
                @if($fieldChanged('address'))
                    <span class="badge bg-warning text-dark audit-diff-badge ms-1">แก้ไข</span>
                @endif
            </div>
            <div class="col-sm-8 {{ $valueClass('address') }}">{{ $data['address'] ?? '-' }}</div>
        </div>

        <div class="row {{ $rowClass('coordinates') }}">
            <div class="col-sm-4 {{ $labelClass('coordinates') }}">
                พิกัด
                @if($fieldChanged('coordinates'))
                    <span class="badge bg-warning text-dark audit-diff-badge ms-1">แก้ไข</span>
                @endif
            </div>
            <div class="col-sm-8 {{ $valueClass('coordinates') }}">{{ $data['lat'] ?? '-' }}, {{ $data['lng'] ?? '-' }}</div>
        </div>

        <div class="row {{ $rowClass('hours') }}">
            <div class="col-sm-4 {{ $labelClass('hours') }}">
                เวลาเปิด-ปิด
                @if($fieldChanged('hours'))
                    <span class="badge bg-warning text-dark audit-diff-badge ms-1">แก้ไข</span>
                @endif
            </div>
            <div class="col-sm-8 {{ $valueClass('hours') }}">{{ $data['open_time'] ?? '-' }} – {{ $data['close_time'] ?? '-' }}</div>
        </div>

        <div class="row {{ $rowClass('connectors') }}">
            <div class="col-sm-4 {{ $labelClass('connectors') }}">
                หัวชาร์จ
                @if($fieldChanged('connectors'))
                    <span class="badge bg-warning text-dark audit-diff-badge ms-1">แก้ไข</span>
                @endif
            </div>
            <div class="col-sm-8 {{ $valueClass('connectors') }}">
                @if($connectors->isEmpty())
                    -
                @else
                    {{ $connectors->map(fn ($c) => ($c['type'] ?? '-') . ' × ' . ($c['total'] ?? '-'))->implode(', ') }}
                @endif
            </div>
        </div>

        <div class="row {{ $rowClass('facilities') }}">
            <div class="col-sm-4 {{ $labelClass('facilities') }}">
                สิ่งอำนวยความสะดวก
                @if($fieldChanged('facilities'))
                    <span class="badge bg-warning text-dark audit-diff-badge ms-1">แก้ไข</span>
                @endif
            </div>
            <div class="col-sm-8 {{ $valueClass('facilities') }}">
                @if($facilityIds->isEmpty())
                    -
                @else
                    {{ $facilityIds->map(fn ($id) => $facilitiesById[$id] ?? ('#' . $id))->implode(', ') }}
                @endif
            </div>
        </div>

        <div class="row {{ $rowClass('images') }}">
            <div class="col-sm-4 {{ $labelClass('images') }}">
                รูปภาพ
                @if($fieldChanged('images'))
                    <span class="badge bg-warning text-dark audit-diff-badge ms-1">แก้ไข</span>
                @endif
            </div>
            <div class="col-sm-8 {{ $valueClass('images') }}">
                @if(empty($data['image']) && $gallery->isEmpty())
                    <span class="text-muted">ไม่มีรูปภาพ</span>
                @else
                    <div class="d-flex flex-wrap gap-2">
                        @if(!empty($data['image']))
                            <div style="width:100px;">
                                <img src="{{ asset('storage/' . $data['image']) }}" class="rounded w-100 {{ $fieldChanged('images') ? 'border border-warning border-2' : '' }}" style="height:80px;object-fit:cover;" alt="">
                            </div>
                        @endif
                        @foreach($gallery as $img)
                            <div style="width:100px;">
                                <img src="{{ asset('storage/' . $img) }}" class="rounded w-100 {{ $fieldChanged('images') ? 'border border-warning border-2' : '' }}" style="height:80px;object-fit:cover;" alt="">
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
