@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4><i class="bi bi-ev-station-fill me-2 text-success"></i>จัดการสถานีทั้งหมด</h4>
            <p class="text-muted mb-0">กดเข้าไปจัดการข้อมูลของสถานีได้ทุกอย่าง</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <table id="stationTable" class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ชื่อสถานี</th>
                        <th>ผู้ส่ง</th>
                        <th>ที่อยู่</th>
                        <th>สถานะอนุมัติ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stations as $station)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><strong>{{ $station->name }}</strong></td>
                            <td class="text-muted">{{ $station->user->name ?? '-' }}</td>
                            <td class="text-muted">{{ \Illuminate\Support\Str::limit($station->address, 50) }}</td>
                            <td>
                                @if($station->approval_status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($station->approval_status === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @else
                                    <span class="badge bg-secondary">{{ $station->approval_status }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.stations.show', $station) }}" class="btn btn-sm btn-ev-primary">
                                    <i class="bi bi-eye me-1"></i>เปิด
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">ยังไม่มีสถานีครับ</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $('#stationTable').DataTable({
        language: {
            search: "ค้นหา:",
            lengthMenu: "แสดง _MENU_ รายการ",
            info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
            paginate: { previous: "ก่อนหน้า", next: "ถัดไป" },
            emptyTable: "ไม่มีข้อมูลครับ",
            zeroRecords: "ไม่พบข้อมูลที่ค้นหาครับ",
        },
        columnDefs: [{ orderable: false, targets: [5] }]
    });
</script>
@endpush

