@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h4><i class="bi bi-plug-fill me-2 text-success"></i>หัวชาร์จของสถานี</h4>
            <p class="text-muted mb-0">{{ $station->name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('provider.stations.connectors.create', $station) }}" class="btn-ev-primary">
                <i class="bi bi-plus-circle me-1"></i>เพิ่มหัวชาร์จ
            </a>
            <a href="{{ route('provider.stations.index') }}" class="btn-ev-outline">
                <i class="bi bi-arrow-left me-1"></i>กลับ
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <table id="connectorTable" class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ประเภทหัวชาร์จ</th>
                        <th>จำนวน</th>
                        <th>ลบ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($connectors as $connector)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <span class="badge bg-primary">
                                <i class="bi bi-plug me-1"></i>{{ $connector->type }}
                            </span>
                        </td>
                        <td>{{ $connector->total }} หัว</td>
                        <td>
                            <form action="{{ route('provider.stations.connectors.destroy', [$station, $connector]) }}"
                                  method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            ยังไม่มีหัวชาร์จ กดเพิ่มได้เลยครับ
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
    $('#connectorTable').DataTable({
        language: {
            search: "ค้นหา:",
            lengthMenu: "แสดง _MENU_ รายการ",
            info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
            paginate: { previous: "ก่อนหน้า", next: "ถัดไป" },
            emptyTable: "ยังไม่มีหัวชาร์จครับ",
            zeroRecords: "ไม่พบข้อมูลที่ค้นหาครับ",
        },
        columnDefs: [{ orderable: false, targets: [4, 5] }]
    });
</script>
@endpush
