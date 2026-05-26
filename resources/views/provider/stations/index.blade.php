@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h4><i class="bi bi-ev-station-fill me-2 text-success"></i>สถานีของฉัน</h4>
            <p class="text-muted mb-0">จัดการสถานีชาร์จทั้งหมดของคุณ</p>
        </div>
        <a href="{{ route('provider.stations.create') }}" class="btn-ev-primary">
            <i class="bi bi-plus-circle me-1"></i>เพิ่มสถานี
        </a>
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
                        <th>ที่อยู่</th>
                        <th>เวลาเปิด-ปิด</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stations as $station)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $station->name }}</strong></td>
                        <td class="text-muted">{{ Str::limit($station->address, 50) }}</td>
                        <td>
                            <i class="bi bi-clock me-1 text-success"></i>
                            {{ $station->open_time ?? '-' }} — {{ $station->close_time ?? '-' }}
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('provider.stations.connectors.index', $station) }}"
                                   class="btn btn-sm btn-ev-outline">
                                    <i class="bi bi-plug me-1"></i>หัวชาร์จ
                                </a>
                                <a href="{{ route('provider.stations.edit', $station) }}"
                                   class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil me-1"></i>แก้ไข
                                </a>
                                <form action="{{ route('provider.stations.destroy', $station) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash me-1"></i>ลบ
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            ยังไม่มีสถานี กดเพิ่มสถานีได้เลยครับ
                        </td>
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
            emptyTable: "ยังไม่มีสถานีครับ",
            zeroRecords: "ไม่พบข้อมูลที่ค้นหาครับ",
        },
        columnDefs: [{ orderable: false, targets: [4] }]
    });
</script>
@endpush