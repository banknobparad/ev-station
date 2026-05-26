@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">แก้ไขสถานี</h4>

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('provider.stations.update', $station) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">ชื่อสถานี</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $station->name) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">ที่อยู่</label>
            <textarea name="address" class="form-control" required>{{ old('address', $station->address) }}</textarea>
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
            <label class="form-label">รูปภาพสถานี</label>
            @if($station->image)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $station->image) }}" width="150" class="rounded">
                </div>
            @endif
            <input type="file" name="image" class="form-control" accept="image/*">
        </div>
        <button type="submit" class="btn btn-warning">บันทึกการแก้ไข</button>
        <a href="{{ route('provider.stations.index') }}" class="btn btn-secondary">ยกเลิก</a>
    </form>
</div>
@endsection