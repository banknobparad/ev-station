@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="page-header">
        <h4><i class="bi bi-chat-dots-fill me-2 text-success"></i>จัดการ Comment</h4>
        <p class="text-muted mb-0">คลิก "ดูคอมเมนต์" เพื่อจัดการรายสถานีครับ</p>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table id="stationTable" class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ชื่อสถานี</th>
                        <th>ที่อยู่</th>
                        <th>จำนวน Comment</th>
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
                            <span class="badge {{ $station->reviews_count > 0 ? 'bg-primary' : 'bg-secondary' }}">
                                {{ $station->reviews_count }} คอมเมนต์
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.reviews.show', $station) }}" class="btn-ev-primary">
                                <i class="bi bi-eye me-1"></i>ดูคอมเมนต์
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">ยังไม่มีสถานีในระบบครับ</td></tr>
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
        columnDefs: [{ orderable: false, targets: [4] }]
    });
</script>
@endpush