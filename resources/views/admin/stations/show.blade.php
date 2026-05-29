@extends('layouts.app')

@section('content')
<div class="container py-4">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
        <div>
            <h4><i class="bi bi-gear-fill me-2 text-success"></i>จัดการสถานี: {{ $station->name }}</h4>
            <p class="text-muted mb-0">ผู้ส่ง: {{ $station->user->name ?? '-' }} • {{ $station->address }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap justify-content-end">
            <a href="{{ route('admin.stations.index') }}" class="btn btn-secondary">← กลับ</a>
            <form action="{{ route('admin.stations.destroy', $station) }}" method="POST" onsubmit="return confirm('ลบสถานีนี้พร้อมข้อมูลทั้งหมดหรือไม่?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger"><i class="bi bi-trash me-1"></i>ลบสถานี</button>
            </form>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-header bg-transparent fw-bold">แก้ไขข้อมูลสถานี</div>
                <div class="card-body">
                    <form action="{{ route('admin.stations.update', $station) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ชื่อสถานี</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $station->name) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ที่อยู่</label>
                                <input type="text" name="address" class="form-control" value="{{ old('address', $station->address) }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Latitude</label>
                                <input type="text" name="lat" class="form-control" value="{{ old('lat', $station->lat) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Longitude</label>
                                <input type="text" name="lng" class="form-control" value="{{ old('lng', $station->lng) }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">เวลาเปิด</label>
                                <input type="time" name="open_time" class="form-control" value="{{ old('open_time', $station->open_time) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">เวลาปิด</label>
                                <input type="time" name="close_time" class="form-control" value="{{ old('close_time', $station->close_time) }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">รูปภาพหลัก</label>
                            @if($station->image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $station->image) }}" style="max-width:220px" class="rounded">
                                </div>
                                <form action="{{ route('admin.stations.delete_station_image', $station) }}" method="POST" class="mb-3">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger" type="submit">ลบรูปภาพหลัก</button>
                                </form>
                            @endif
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">รูปภาพเพิ่มเติม (แกลลอรี)</label>
                            @php($galleryImages = collect($station->gallery_images ?? [])
                                ->filter()
                                ->unique()
                                ->values())
                            @if($galleryImages->isNotEmpty())
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    @foreach($galleryImages as $img)
                                        <div style="width:110px; position:relative;">
                                            <img src="{{ asset('storage/' . $img) }}" class="rounded w-100" style="height:90px; object-fit:cover;">
                                            <form action="{{ route('admin.stations.delete_gallery_image', $station) }}" method="POST" style="position:absolute; top:0; right:0;">
                                                @csrf
                                                <input type="hidden" name="image_path" value="{{ $img }}">
                                                <button class="btn btn-sm btn-danger" style="padding:2px 6px" type="submit" title="ลบรูป">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-muted">ไม่มีรูปภาพในแกลลอรี</div>
                            @endif

                            <input type="file" name="gallery_images[]" class="form-control" accept="image/*" multiple>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">สิ่งอำนวยความสะดวก</label>
                            @php($facilitiesAll = \App\Models\Facility::all())
                            <div class="row">
                                @foreach($facilitiesAll as $facility)
                                    <div class="col-md-6 col-lg-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="facilities[]" value="{{ $facility->id }}"
                                                id="facility_{{ $facility->id }}" {{ $station->facilities->contains($facility->id) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="facility_{{ $facility->id }}">{{ $facility->name }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <button class="btn btn-ev-primary" type="submit"><i class="bi bi-save me-1"></i>บันทึกการแก้ไข</button>
                    </form>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-transparent fw-bold">หัวชาร์จ (Connectors)</div>
                <div class="card-body">
                    <div class="row g-2 mb-4">
                        <div class="col-md-5">
                            <form action="{{ route('admin.stations.connectors.add', $station) }}" method="POST" class="row g-2">
                                @csrf
                                <div class="col-12">
                                    <label class="form-label">ประเภทหัวชาร์จ</label>
                                    <select class="form-select" name="type" required>
                                        @foreach(['CCS2','CHAdeMO','Type2','GB/T'] as $t)
                                            <option value="{{ $t }}">{{ $t }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">จำนวน</label>
                                    <input type="number" min="1" name="total" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-ev-primary" type="submit"><i class="bi bi-plus-circle me-1"></i>เพิ่มหัวชาร์จ</button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-7">
                            <div class="text-muted">* ปัจจุบัน Admin สามารถแก้ประเภท/จำนวน และลบหัวชาร์จได้</div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>ประเภท</th>
                                    <th>จำนวน</th>
                                    <th>แก้ไข</th>
                                    <th>ลบ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($station->connectors as $connector)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $connector->type }}</span>
                                        </td>
                                        <td>{{ $connector->total }}</td>
                                        <td>
                                            <form action="{{ route('admin.stations.connectors.update', [$station, $connector]) }}" method="POST" class="row g-2 align-items-center">
                                                @csrf
                                                <div class="col-12">
                                                    <select class="form-select" name="type" required>
                                                        @foreach(['CCS2','CHAdeMO','Type2','GB/T'] as $t)
                                                            <option value="{{ $t }}" {{ $connector->type === $t ? 'selected' : '' }}>{{ $t }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <input type="number" min="1" name="total" class="form-control" value="{{ $connector->total }}" required>
                                                </div>
                                                <div class="col-12">
                                                    <button class="btn btn-sm btn-warning" type="submit">บันทึก</button>
                                                </div>
                                            </form>
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.stations.connectors.destroy', [$station, $connector]) }}" method="POST" onsubmit="return confirm('ลบหัวชาร์จนี้?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger" type="submit"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">ไม่มีหัวชาร์จ</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-header bg-transparent fw-bold">คอมเมนต์ (Reviews)</div>
                <div class="card-body">
                    @forelse($station->reviews as $review)
                        <div class="card mb-2">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <strong>{{ $review->user->name ?? '-' }}</strong>
                                        <div class="text-warning mt-1">
                                            {{ str_repeat('★', $review->star) }}{{ str_repeat('☆', 5 - $review->star) }}
                                        </div>
                                        <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                                        <p class="mt-2 mb-0">{{ $review->comment ?? '' }}</p>
                                    </div>
                                    <form action="{{ route('admin.stations.reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('ลบคอมเมนต์นี้?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted">ยังไม่มีคอมเมนต์</div>
                    @endforelse
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-transparent fw-bold">รายละเอียดเพิ่มเติม</div>
                <div class="card-body">
                    @include('admin.stations._station_detail_rows', ['station' => $station])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

