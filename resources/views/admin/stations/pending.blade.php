@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>สถานีรออนุมัติ</h4>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">กลับ</a>
    </div>

    @if($pendingStations->isEmpty())
        <div class="text-muted">ไม่มีสถานีรออนุมัติครับ</div>
    @else
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ชื่อสถานี</th>
                            <th>ผู้ส่ง</th>
                            <th>ที่อยู่</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingStations as $station)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ $station->name }}</strong></td>
                                <td>{{ $station->user->name ?? '-' }}</td>
                                <td class="text-muted">{{ \Illuminate\Support\Str::limit($station->address, 60) }}</td>
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
        </div>
    @endif
</div>
@endsection

