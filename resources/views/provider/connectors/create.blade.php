@extends('layouts.app')

@section('content')
    <div class="container">
        <h4 class="mb-3">เพิ่มหัวชาร์จ — {{ $station->name }}</h4>

        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('provider.stations.connectors.store', $station) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">ประเภทหัวชาร์จ</label>
                <select name="type" class="form-select" required>
                    <option value="">-- เลือกประเภท --</option>
                    <option value="CCS2">CCS2</option>
                    <option value="CHAdeMO">CHAdeMO</option>
                    <option value="Type2">Type 2</option>
                    <option value="GB/T">GB/T</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">จำนวนหัวชาร์จ</label>
                <input type="number" name="total" class="form-control" min="1" value="1" required>
            </div>
            <div class="mb-3">
                <label class="form-label">สถานะเริ่มต้น</label>
                <select name="status" class="form-select" required>
                    <option value="available">Available</option>
                    <option value="busy">Busy</option>
                    <option value="maintenance">Maintenance</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">บันทึก</button>
            <a href="{{ route('provider.stations.connectors.index', $station) }}" class="btn btn-secondary">ยกเลิก</a>
        </form>
    </div>
@endsection
