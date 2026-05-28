@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Dashboard - ผู้ให้บริการ</h4>
    </div>

    <!-- KPI Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-2">สถานีของฉัน</h6>
                    <h3 class="text-success mb-0">{{ $totalStations }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-2">ทั้งหมด Reviews</h6>
                    <h3 class="text-primary mb-0">{{ $stations->sum(fn($s) => $s->reviews->count()) }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-2">ดาวเฉลี่ย</h6>
                    <h3 class="text-warning mb-0">
                        {{ $stations->count() > 0 ? number_format($stations->avg(fn($s) => $s->reviews->avg('star') ?? 0), 1) : 'N/A' }}
                        <small class="text-muted">/ 5.0</small>
                    </h3>
                </div>
            </div>
        </div>

    </div>

    <!-- Main Content Row -->
    <div class="row mb-4">
        <!-- Station Management Card -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">จัดการสถานีชาร์จ</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">เพิ่ม / แก้ไข / ลบ / ดูรายละเอียด สถานีของคุณ</p>
                    <a href="{{ route('provider.stations.index') }}" class="btn btn-success btn-lg btn-block">
                        <i class="fas fa-cog"></i> จัดการสถานี
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Reviews Summary -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">สรุปข้อมูลทั่วไป</h5>
                </div>
                <div class="card-body">
                    @php
                        $totalReviews = $stations->sum(fn($s) => $s->reviews->count());
                        $totalConnectors = $stations->sum(fn($s) => $s->connectors->count());
                        $avgRating = $stations->count() > 0 ? $stations->avg(fn($s) => $s->reviews->avg('star') ?? 0) : 0;
                    @endphp
                    <div class="row text-center">
                        <div class="col-4">
                            <h4 class="mb-0">{{ $totalReviews }}</h4>
                            <small class="text-muted">รีวิวทั้งหมด</small>
                        </div>
                        <div class="col-4 border-left border-right">
                            <h4 class="mb-0">{{ $totalConnectors }}</h4>
                            <small class="text-muted">ช่องชาร์จ</small>
                        </div>
                        <div class="col-4">
                            <h4 class="mb-0">{{ number_format($avgRating, 1) }}</h4>
                            <small class="text-muted">ดาวเฉลี่ย</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stations Grid Section -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="mb-3">สถานีของคุณ</h5>

            @if($stations->count() > 0)
                <div class="row">
                    @foreach($stations as $station)
                        <div class="col-lg-6 mb-3">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="card-title mb-1">
                                                <strong>{{ $station->name }}</strong>
                                            </h6>
                                            <p class="text-muted mb-1" style="font-size: 0.9rem;">
                                                <i class="fas fa-map-marker-alt"></i> {{ $station->address }}
                                            </p>
                                            <p class="text-muted mb-0" style="font-size: 0.9rem;">
                                                <i class="fas fa-clock"></i>
                                                {{ $station->open_time }} - {{ $station->close_time }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <h5 class="text-warning mb-0">
                                                @php $avgStationRating = $station->reviews->avg('star') ?? 0; @endphp
                                                <i class="fas fa-star"></i> {{ number_format($avgStationRating, 1) }}
                                            </h5>
                                            <small class="text-muted">{{ $station->reviews->count() }} รีวิว</small>
                                        </div>
                                    </div>

                                    <div class="mt-3 d-flex gap-2">
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#stationModal{{ $station->id }}">
                                            <i class="fas fa-eye"></i> ดูรายละเอียด
                                        </button>
                                        <a href="{{ route('provider.stations.edit', $station->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i> แก้ไข
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal for Station Details -->
                        <div class="modal fade" id="stationModal{{ $station->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-info text-white">
                                        <h5 class="modal-title">{{ $station->name }}</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <h6 class="mb-2"><strong>ข้อมูลสถานี</strong></h6>
                                            <p class="mb-1">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <strong>ที่อยู่:</strong> {{ $station->address }}
                                            </p>
                                            <p class="mb-1">
                                                <i class="fas fa-clock"></i>
                                                <strong>เวลาเปิด-ปิด:</strong> {{ $station->open_time }} - {{ $station->close_time }}
                                            </p>
                                            <p class="mb-1">
                                                <i class="fas fa-star"></i>
                                                <strong>ดาวเฉลี่ย:</strong>
                                                <span class="text-warning">{{ number_format($avgStationRating, 1) }}/5.0</span>
                                            </p>
                                            <p class="mb-0">
                                                <i class="fas fa-comments"></i>
                                                <strong>จำนวนรีวิว:</strong> {{ $station->reviews->count() }}
                                            </p>
                                        </div>

                                        @if($station->facilities->count() > 0)
                                        <div class="mb-3">
                                            <h6 class="mb-2"><strong><i class="fas fa-shopping-cart"></i> สิ่งอำนวยความสะดวก</strong></h6>
                                            <div class="row">
                                                @foreach($station->facilities as $facility)
                                                <div class="col-6 mb-2">
                                                    <div class="d-flex align-items-center">
                                                        <div style="font-size: 1.2rem; margin-right: 8px; min-width: 25px;">
                                                            @switch($facility->name)
                                                                @case('ที่กินข้าว')
                                                                    <i class="fas fa-utensils" style="color: #ff6b6b;"></i>
                                                                    @break
                                                                @case('ที่จอดรถ')
                                                                    <i class="fas fa-square" style="color: #4ecdc4;"></i>
                                                                    @break
                                                                @case('ที่ชอปปิ้ง')
                                                                    <i class="fas fa-shopping-cart" style="color: #ffe66d;"></i>
                                                                    @break
                                                                @case('ห้องน้ำ')
                                                                    <i class="fas fa-restroom" style="color: #95e1d3;"></i>
                                                                    @break
                                                                @case('ร้านขายของชำ')
                                                                    <i class="fas fa-store" style="color: #ffa502;"></i>
                                                                    @break
                                                                @case('ที่นั่งพัก')
                                                                    <i class="fas fa-chair" style="color: #a8d8ea;"></i>
                                                                    @break
                                                                @case('WiFi')
                                                                    <i class="fas fa-wifi" style="color: #667eea;"></i>
                                                                    @break
                                                                @case('สถานีอัดอากาศ')
                                                                    <i class="fas fa-wind" style="color: #74b9ff;"></i>
                                                                    @break
                                                                @default
                                                                    <i class="fas fa-check-circle" style="color: #74b9ff;"></i>
                                                            @endswitch
                                                        </div>
                                                        <span style="font-size: 0.9rem;">{{ $facility->name }}</span>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif

                                        <hr>

                                        <h6 class="mb-2"><strong><i class="fas fa-comments"></i> รีวิวและคอมเมนท์</strong></h6>
                                        @if($station->reviews->count() > 0)
                                            <div style="max-height: 400px; overflow-y: auto;">
                                                @foreach($station->reviews->sortByDesc('created_at') as $review)
                                                    <div class="mb-3 pb-3 border-bottom">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <div>
                                                                <strong>{{ $review->user->name ?? 'Unknown' }}</strong>
                                                                <div class="text-warning" style="font-size: 0.9rem;">
                                                                    @for($i = 0; $i < $review->star; $i++)
                                                                        <i class="fas fa-star"></i>
                                                                    @endfor
                                                                    @for($i = $review->star; $i < 5; $i++)
                                                                        <i class="far fa-star"></i>
                                                                    @endfor
                                                                    <span class="text-muted">({{ $review->star }} ดาว)</span>
                                                                </div>
                                                            </div>
                                                            <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                                                        </div>
                                                        <p class="mb-0 text-muted">
                                                            {{ $review->comment ?? 'ไม่มีคอมเมนท์' }}
                                                        </p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-muted text-center">
                                                <i class="fas fa-inbox"></i> ยังไม่มีรีวิว
                                            </p>
                                        @endif
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle"></i> คุณยังไม่มีสถานีชาร์จ
                    <a href="{{ route('provider.stations.create') }}" class="alert-link">สร้างสถานีใหม่</a>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .border-left-success {
        border-left: 4px solid #28a745;
    }
    .border-left-primary {
        border-left: 4px solid #007bff;
    }
    .border-left-warning {
        border-left: 4px solid #ffc107;
    }
    .border-left-info {
        border-left: 4px solid #17a2b8;
    }
    .btn-block {
        display: block;
        width: 100%;
    }
</style>
@endsection
