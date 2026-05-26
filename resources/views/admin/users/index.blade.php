@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h4><i class="bi bi-people-fill me-2 text-success"></i>จัดการ Provider</h4>
            <p class="text-muted mb-0">บัญชีผู้ประกอบการสถานีชาร์จทั้งหมด</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn-ev-primary">
            <i class="bi bi-plus-circle me-1"></i>เพิ่ม Provider
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <table id="providerTable" class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ชื่อ</th>
                        <th>Email</th>
                        <th>สถานะ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($providers as $provider)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $provider->name }}</strong></td>
                        <td class="text-muted">{{ $provider->email }}</td>
                        <td><span class="badge bg-success">{{ $provider->status }}</span></td>
                        <td>
                            <form action="{{ route('admin.users.destroy', $provider) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash me-1"></i>ลบ
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $('#providerTable').DataTable({
        language: {
            search: "ค้นหา:",
            lengthMenu: "แสดง _MENU_ รายการ",
            info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
            paginate: { previous: "ก่อนหน้า", next: "ถัดไป" },
            emptyTable: "ยังไม่มี Provider ครับ",
            zeroRecords: "ไม่พบข้อมูลที่ค้นหาครับ",
        },
        columnDefs: [{ orderable: false, targets: [4] }]
    });
</script>
@endpush