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