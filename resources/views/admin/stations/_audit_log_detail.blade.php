@if($log->action === 'delete')
    @php $snapshot = $log->payload['snapshot'] ?? []; @endphp
    <div class="alert alert-danger d-flex align-items-center gap-2">
        <i class="bi bi-trash"></i>
        <span>คนขับขอ<strong>ลบสถานี</strong>นี้</span>
    </div>
    <div class="mb-3">
        <div class="fw-bold">เหตุผลที่ขอลบ</div>
        <div class="border rounded p-3 bg-light">{{ $log->reason ?: '-' }}</div>
    </div>
    @include('admin.stations._audit_log_snapshot', [
        'data' => $snapshot,
        'title' => 'ข้อมูลสถานีที่จะถูกลบ',
        'facilitiesById' => $facilitiesById,
    ])
@else
    @php
        $before = $log->payload['before'] ?? null;
        $after = $log->payload['after'] ?? $log->payload;

        $changedFields = [];
        if ($before && $after) {
            if (($before['name'] ?? '') !== ($after['name'] ?? '')) {
                $changedFields[] = 'name';
            }
            if (($before['address'] ?? '') !== ($after['address'] ?? '')) {
                $changedFields[] = 'address';
            }
            if ((string) ($before['lat'] ?? '') !== (string) ($after['lat'] ?? '')
                || (string) ($before['lng'] ?? '') !== (string) ($after['lng'] ?? '')) {
                $changedFields[] = 'coordinates';
            }
            if (($before['open_time'] ?? '') !== ($after['open_time'] ?? '')
                || ($before['close_time'] ?? '') !== ($after['close_time'] ?? '')) {
                $changedFields[] = 'hours';
            }

            $normalizeConnectors = fn ($list) => collect($list ?? [])
                ->map(fn ($c) => ['type' => $c['type'] ?? '', 'total' => (int) ($c['total'] ?? 0)])
                ->sortBy('type')
                ->values()
                ->all();
            if ($normalizeConnectors($before['connectors'] ?? []) !== $normalizeConnectors($after['connectors'] ?? [])) {
                $changedFields[] = 'connectors';
            }

            $normalizeIds = fn ($list) => collect($list ?? [])->map(fn ($id) => (int) $id)->sort()->values()->all();
            if ($normalizeIds($before['facilities'] ?? []) !== $normalizeIds($after['facilities'] ?? [])) {
                $changedFields[] = 'facilities';
            }

            $normalizeGallery = fn ($list) => collect($list ?? [])->filter()->sort()->values()->all();
            if (($before['image'] ?? '') !== ($after['image'] ?? '')
                || $normalizeGallery($before['gallery_images'] ?? []) !== $normalizeGallery($after['gallery_images'] ?? [])) {
                $changedFields[] = 'images';
            }
        }
    @endphp
    <div class="alert alert-primary d-flex align-items-center gap-2">
        <i class="bi bi-pencil-square"></i>
        <span>คนขับขอ<strong>แก้ไขสถานี</strong> — เปรียบเทียบข้อมูลเดิมกับที่ขอเปลี่ยน</span>
    </div>
    @if(!empty($changedFields))
        <div class="small text-muted mb-3">
            <span class="d-inline-block rounded px-2 py-1 audit-diff-row" style="border-left:3px solid #ffc107; background:rgba(255,193,7,0.18);">
                ไฮไลต์สีเหลือง = หัวข้อที่มีการเปลี่ยนแปลง
            </span>
            <span class="ms-2">({{ count($changedFields) }} รายการ)</span>
        </div>
    @endif
    @if($before)
        <div class="row g-3">
            <div class="col-lg-6">
                @include('admin.stations._audit_log_snapshot', [
                    'data' => $before,
                    'title' => 'ข้อมูลเดิม (ก่อนแก้ไข)',
                    'facilitiesById' => $facilitiesById,
                    'changedFields' => $changedFields,
                ])
            </div>
            <div class="col-lg-6">
                @include('admin.stations._audit_log_snapshot', [
                    'data' => $after,
                    'title' => 'ข้อมูลที่ขอแก้ไข',
                    'facilitiesById' => $facilitiesById,
                    'changedFields' => $changedFields,
                ])
            </div>
        </div>
    @else
        @include('admin.stations._audit_log_snapshot', [
            'data' => $after,
            'title' => 'ข้อมูลที่ขอแก้ไข',
            'facilitiesById' => $facilitiesById,
        ])
    @endif
@endif
