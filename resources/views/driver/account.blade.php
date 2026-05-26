@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-column flex-md-row align-items-start justify-content-between gap-3 mb-4">
        <div>
            <h2>บัญชีผู้ขับขี่</h2>
            <p class="text-muted mb-0">จัดการข้อมูลส่วนตัวและดูสถิติการใช้งานของคุณ</p>
        </div>
        <div>
            <a href="{{ route('logout') }}" class="btn btn-outline-danger"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row gy-4">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div>
                            <div class="text-uppercase text-secondary small mb-2">ข้อมูลผู้ขับขี่</div>
                            <h4 class="mb-1">{{ $user->name }}</h4>
                            <div class="text-muted mb-2">เบอร์โทร: {{ $user->phone ?? '-' }}</div>
                            <div class="text-muted">อีเมล: {{ $user->email ?? '-' }}</div>
                        </div>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editAccountModal">
                            <i class="bi bi-pencil-square me-1"></i> แก้ไข
                        </button>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="card-title">สถิติส่วนตัว</h5>
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <div class="text-muted small">คอมเมนต์ทั้งหมด</div>
                            <div class="fs-3 fw-semibold">{{ $reviewCount }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">สถานีที่เคยใช้บริการ</div>
                            <div class="fs-3 fw-semibold">{{ $visitedCount }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">ข้อมูลการใช้งาน</h5>
                    <p class="text-muted">รายการล่าสุดที่คุณได้คอมเมนต์หรือใช้งานสถานี</p>

                    @if($recentComments->isEmpty())
                        <div class="text-muted">ยังไม่มีคอมเมนต์จากคุณ</div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($recentComments as $review)
                                <li class="list-group-item px-0 py-3">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div>
                                            <div class="fw-semibold">{{ $review->station->name ?? 'สถานีไม่ทราบ' }}</div>
                                            <div class="text-muted small">{{ Str::limit($review->comment, 70) }}</div>
                                        </div>
                                        <span class="badge bg-primary rounded-pill">{{ $review->star }} ⭐</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="editAccountModal" tabindex="-1" aria-labelledby="editAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAccountModalLabel">แก้ไขข้อมูลส่วนตัว</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('driver.account.update') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">ชื่อ</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">อีเมล</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $user->email) }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">เบอร์โทร</label>
                        <input type="text" class="form-control" value="{{ $user->phone ?? '-' }}" disabled>
                        <div class="form-text">เบอร์โทรไม่สามารถแก้ไขได้จากหน้านี้</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
