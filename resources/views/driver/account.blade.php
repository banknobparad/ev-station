@extends('layouts.app')

@section('content')
    <div class="container py-4" style="max-width: 900px;">

        {{-- Header --}}
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-0">บัญชีของฉัน</h4>
                <p class="text-muted small mb-0">จัดการข้อมูลส่วนตัวและกิจกรรมของคุณ</p>
            </div>
            <a href="{{ route('logout') }}" class="btn btn-sm btn-outline-danger"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="bi bi-box-arrow-right me-1"></i>ออกจากระบบ
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Profile Card --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-4">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width:64px;height:64px;">
                        <i class="bi bi-person-fill text-primary fs-3"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                        <div class="text-muted small">
                            <i class="bi bi-telephone me-1"></i>{{ $user->phone ?? '-' }}
                            @if ($user->email)
                                <span class="mx-2">·</span>
                                <i class="bi bi-envelope me-1"></i>{{ $user->email }}
                            @endif
                        </div>
                        @if ($user->citizen_id || $user->birth_date)
                            <div class="text-muted small mt-1">
                                @if ($user->citizen_id)
                                    <span><i class="bi bi-card-text me-1"></i>{{ $user->citizen_id }}</span>
                                @endif
                                @if ($user->birth_date)
                                    <span class="ms-2">
                                        <i class="bi bi-calendar me-1"></i>
                                        {{ $user->birth_date instanceof \DateTimeInterface ? $user->birth_date->format('d/m/Y') : \Carbon\Carbon::parse($user->birth_date)->format('d/m/Y') }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                    <a href="{{ route('driver.profile.edit') }}" class="btn btn-outline-primary btn-sm rounded-3">
                        <i class="bi bi-pencil me-1"></i>แก้ไขโปรไฟล์
                    </a>
                </div>
                <hr class="my-3">

                <div class="row text-center g-0">
                    <div class="col-4 border-end">
                        <div class="fs-3 fw-bold text-primary">{{ $reviewCount }}</div>
                        <div class="text-muted small">รีวิวทั้งหมด</div>
                    </div>
                    <div class="col-4 border-end">
                        <div class="fs-3 fw-bold text-primary">{{ $myStations->count() }}</div>
                        <div class="text-muted small">สถานีที่เพิ่ม</div>
                    </div>
                    <div class="col-4">
                        <a href="{{ route('driver.favorites') }}" class="text-decoration-none d-block">
                            <div class="fs-3 fw-bold text-danger">
                                <i class="bi bi-heart-fill" style="font-size:1.6rem;"></i>
                            </div>
                            <div class="text-muted small">รายการโปรด</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <ul class="nav nav-tabs mb-3" id="accountTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-reviews" type="button">
                    <i class="bi bi-chat-left-text me-1"></i>รีวิวของฉัน
                    <span class="badge bg-primary ms-1">{{ $reviewCount }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-stations" type="button">
                    <i class="bi bi-ev-station me-1"></i>สถานีที่ฉันเพิ่ม
                    <span class="badge bg-secondary ms-1">{{ $myStations->count() }}</span>
                </button>
            </li>
        </ul>

        <div class="tab-content">

            {{-- Tab: Reviews --}}
            <div class="tab-pane fade show active" id="tab-reviews">
                @if ($recentComments->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-chat-left-text fs-1 d-block mb-2 opacity-25"></i>
                        ยังไม่มีรีวิวจากคุณ
                    </div>
                @else
                    <div class="d-flex flex-column gap-3">
                        @foreach ($recentComments as $review)
                            <div class="card border-0 shadow-sm rounded-4">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold mb-1">
                                                <i class="bi bi-lightning-charge-fill text-warning me-1"></i>
                                                {{ $review->station->name ?? 'สถานีไม่ทราบ' }}
                                            </div>
                                            <div class="mb-1">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <i
                                                        class="bi bi-star{{ $i <= $review->star ? '-fill text-warning' : ' text-muted' }} small"></i>
                                                @endfor
                                            </div>
                                            @if ($review->comment)
                                                <p class="text-muted small mb-0">{{ $review->comment }}</p>
                                            @else
                                                <p class="text-muted small fst-italic mb-0">ไม่มีความคิดเห็น</p>
                                            @endif
                                            <div class="text-muted" style="font-size:0.75rem; margin-top:4px;">
                                                {{ $review->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 flex-shrink-0">
                                            <button class="btn btn-sm btn-outline-secondary rounded-3"
                                                onclick="openEditModal({{ $review->id }}, {{ $review->star }}, @js($review->comment ?? ''))">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" action="{{ route('driver.review.destroy', $review) }}"
                                                onsubmit="return confirm('ยืนยันลบรีวิวนี้?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger rounded-3">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Tab: My Stations --}}
            <div class="tab-pane fade" id="tab-stations">
                <div class="d-flex justify-content-end mb-3">
                    <a href="{{ route('driver.stations.create') }}" class="btn btn-primary btn-sm rounded-3">
                        <i class="bi bi-plus-lg me-1"></i>เพิ่มสถานีใหม่
                    </a>
                </div>

                @if ($myStations->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-ev-station fs-1 d-block mb-2 opacity-25"></i>
                        คุณยังไม่ได้เพิ่มสถานีใดเลย
                    </div>
                @else
                    <div class="d-flex flex-column gap-3">
                        @foreach ($myStations as $station)
                            <div class="card border-0 shadow-sm rounded-4">
                                <div class="card-body p-3">
                                    <div class="d-flex gap-3 align-items-center">
                                        @if ($station->image)
                                            <img src="{{ asset('storage/' . $station->image) }}"
                                                class="rounded-3 object-fit-cover flex-shrink-0"
                                                style="width:64px;height:64px;" alt="">
                                        @else
                                            <div class="rounded-3 bg-light d-flex align-items-center justify-content-center flex-shrink-0"
                                                style="width:64px;height:64px;">
                                                <i class="bi bi-ev-station text-secondary fs-4"></i>
                                            </div>
                                        @endif
                                        <div class="flex-grow-1" style="min-width:0;">
                                            <div class="fw-semibold">{{ $station->name }}</div>
                                            <div class="text-muted small"
                                                style="overflow:hidden; white-space:nowrap; text-overflow:ellipsis; max-width:220px;">
                                                {{ $station->address }}</div>
                                            @if ($station->open_time && $station->close_time)
                                                <div class="text-muted small">
                                                    <i class="bi bi-clock me-1"></i>
                                                    {{ \Carbon\Carbon::parse($station->open_time)->format('H:i') }}
                                                    – {{ \Carbon\Carbon::parse($station->close_time)->format('H:i') }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-shrink-0">
                                            @php
                                                $status = $station->approval_status ?? 'pending';
                                                $pendingAudit = $pendingAuditsByStation->get($station->id);
                                            @endphp
                                            <div class="d-flex flex-column align-items-end gap-2">
                                                @if ($status === 'approved')
                                                    <span class="badge bg-success-subtle text-success rounded-pill">
                                                        <i class="bi bi-check-circle me-1"></i>อนุมัติแล้ว
                                                    </span>
                                                @elseif($status === 'rejected')
                                                    <span class="badge bg-danger-subtle text-danger rounded-pill">
                                                        <i class="bi bi-x-circle me-1"></i>ถูกปฏิเสธ
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning rounded-pill">
                                                        <i class="bi bi-hourglass-split me-1"></i>รอการอนุมัติ
                                                    </span>
                                                @endif

                                                @if ($pendingAudit)
                                                    @if ($pendingAudit->action === 'edit')
                                                        <span class="badge bg-primary-subtle text-primary rounded-pill">
                                                            <i class="bi bi-pencil me-1"></i>รออนุมัติการแก้ไข
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger-subtle text-danger rounded-pill">
                                                            <i class="bi bi-trash me-1"></i>รออนุมัติการลบ
                                                        </span>
                                                    @endif
                                                @endif

                                                <div class="d-flex gap-2">
                                                    @if ($pendingAudit)
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-primary rounded-3" disabled
                                                            title="มีคำขอรอ Admin อนุมัติอยู่แล้ว">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-danger rounded-3" disabled
                                                            title="มีคำขอรอ Admin อนุมัติอยู่แล้ว">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    @else
                                                        <a href="{{ route('driver.stations.edit', $station) }}"
                                                            class="btn btn-sm btn-outline-primary rounded-3"
                                                            title="แก้ไขสถานีของคุณ">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-danger rounded-3"
                                                            title="ลบสถานีของคุณ"
                                                            onclick="openDeleteModal({{ $station->id }}, {{ json_encode($station->name) }})">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- Modal: Edit Review --}}
    <div class="modal fade" id="editReviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">แก้ไขรีวิว</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editReviewForm">
                    @csrf @method('PUT')
                    <div class="modal-body pt-2">
                        <div class="mb-3">
                            <label class="form-label fw-medium">คะแนน</label>
                            <div class="d-flex gap-2" id="starSelector">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star fs-4 text-muted star-btn" data-value="{{ $i }}"
                                        style="cursor:pointer;"></i>
                                @endfor
                            </div>
                            <input type="hidden" name="star" id="starInput">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">ความคิดเห็น</label>
                            <textarea name="comment" id="editComment" class="form-control rounded-3" rows="3"
                                maxlength="500"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary rounded-3">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal: Delete Station (reason) --}}
    <div class="modal fade" id="deleteStationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">ลบสถานี</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="deleteStationForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body pt-2">
                        <input type="hidden" name="_station_id" id="deleteStationId">
                        <p class="text-muted mb-2" id="deleteStationName"></p>
                        <div class="mb-3">
                            <label class="form-label fw-medium">เหตุผลที่ลบ</label>
                            <textarea name="reason" id="deleteStationReason" class="form-control rounded-3" rows="4"
                                required maxlength="1000" placeholder="กรอกเหตุผลที่ต้องการลบ..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-danger rounded-3">
                            <i class="bi bi-trash me-1"></i>ยืนยันลบ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

{{-- @push must be OUTSIDE @section --}}
@push('scripts')
    <script>
        function openDeleteModal(stationId, stationName) {
            const form = document.getElementById('deleteStationForm');
            form.action = `/account/stations/${stationId}`;
            document.getElementById('deleteStationId').value = stationId;
            document.getElementById('deleteStationName').textContent = `สถานี: ${stationName}`;
            document.getElementById('deleteStationReason').value = '';
            new bootstrap.Modal(document.getElementById('deleteStationModal')).show();
        }

        function openEditModal(id, star, comment) {
            const form = document.getElementById('editReviewForm');
            form.action = `/review/${id}`;
            document.getElementById('editComment').value = comment;
            setStars(star);
            new bootstrap.Modal(document.getElementById('editReviewModal')).show();
        }

        function setStars(value) {
            document.getElementById('starInput').value = value;
            document.querySelectorAll('.star-btn').forEach(btn => {
                const v = parseInt(btn.dataset.value);
                btn.className =
                    `bi fs-4 star-btn ${v <= value ? 'bi-star-fill text-warning' : 'bi-star text-muted'}`;
                btn.style.cursor = 'pointer';
            });
        }

        document.querySelectorAll('.star-btn').forEach(btn => {
            btn.addEventListener('click', () => setStars(parseInt(btn.dataset.value)));
        });
    </script>
@endpush
