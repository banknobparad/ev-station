@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h4><i class="bi bi-grid-fill me-2 text-success"></i>Admin Dashboard</h4>
            <p class="text-muted mb-0">ยินดีต้อนรับ, {{ auth()->user()->name }} ครับ</p>
        </div>
        <span class="text-muted small">{{ now()->format('d M Y') }}</span>
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:rgba(99,102,241,0.15)">👤</div>
                <div class="stat-number">{{ $totalDrivers }}</div>
                <div class="stat-label">EV Driver ทั้งหมด</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:rgba(245,158,11,0.15)">🏢</div>
                <div class="stat-number">{{ $totalProviders }}</div>
                <div class="stat-label">Provider ทั้งหมด</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:var(--ev-green-glow)">⚡</div>
                <div class="stat-number">{{ $totalStations }}</div>
                <div class="stat-label">สถานีชาร์จทั้งหมด</div>
            </div>
        </div>
    </div>

    {{-- Approval Card --}}
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                        <div>
                            <h5 class="mb-0">รออนุมัติสถานีจากคนขับ (Pending)</h5>
                            <small class="text-muted">อนุมัติ/ปฏิเสธเพื่อให้ขึ้นบนแผนที่</small>
                        </div>
                        <a href="{{ route('admin.stations.pending') }}" class="btn btn-ev-primary">
                            ดูทั้งหมด
                        </a>
                    </div>

                    @if($pendingStations->isEmpty())
                        <div class="text-muted">ไม่มีสถานีรออนุมัติครับ</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>สถานี</th>
                                        <th>ผู้ส่ง</th>
                                        <th>จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingStations as $station)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <strong>{{ $station->name }}</strong>
                                                <div class="text-muted small">{{ \Illuminate\Support\Str::limit($station->address, 50) }}</div>
                                            </td>
                                            <td>{{ $station->user->name ?? '-' }}</td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <form action="{{ route('admin.stations.approve', $station) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success">อนุมัติ</button>
                                                    </form>
                                                    <form action="{{ route('admin.stations.reject', $station) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('ปฏิเสธและลบสถานีนี้?')">ปฏิเสธ</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Menu Cards --}}
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="stat-icon mb-0" style="background:rgba(245,158,11,0.15)">🏢</div>
                        <div>
                            <h5 class="mb-0">จัดการ Provider</h5>
                            <small class="text-muted">เพิ่ม / ลบ บัญชีผู้ประกอบการ</small>
                        </div>
                    </div>
                    <a href="{{ route('admin.users.index') }}" class="btn-ev-primary">
                        <i class="bi bi-arrow-right-circle me-1"></i>จัดการ
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="stat-icon mb-0" style="background:rgba(239,68,68,0.15)">💬</div>
                        <div>
                            <h5 class="mb-0">จัดการ Comment</h5>
                            <small class="text-muted">ลบคอมเมนต์ที่ไม่เหมาะสม</small>
                        </div>
                    </div>
                    <a href="{{ route('admin.reviews.index') }}" class="btn-ev-primary">
                        <i class="bi bi-arrow-right-circle me-1"></i>จัดการ
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
