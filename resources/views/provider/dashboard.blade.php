@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4">Provider Dashboard</h4>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">สถานีของฉัน</h5>
                    <h2>{{ $totalStations }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">จัดการสถานีชาร์จ</h5>
                    <p class="card-text">เพิ่ม / แก้ไข / ลบ สถานีของคุณ</p>
                    <a href="{{ route('provider.stations.index') }}" class="btn btn-success">จัดการ</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
