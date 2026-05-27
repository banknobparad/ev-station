@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h4 class="mb-4">❤️ สถานีที่ฉันชื่นชอบ</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        @forelse($favorites as $fav)
        <div class="col-md-4 mb-3">
            <div class="card h-100">
                @if($fav->station->image)
                    <img src="{{ asset('storage/' . $fav->station->image) }}" class="card-img-top" style="height:150px; object-fit:cover;">
                @endif
                <div class="card-body">
                    <h6 class="card-title">{{ $fav->station->name }}</h6>
                    <p class="text-muted small">📍 {{ Str::limit($fav->station->address, 60) }}</p>



                    <div class="mt-2">
                        <a href="{{ route('driver.station', $fav->station) }}" class="btn btn-primary btn-sm">ดูรายละเอียด</a>

                        <form action="{{ route('driver.favorite.toggle', $fav->station) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-outline-danger btn-sm">เอาออก</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <p class="text-muted">ยังไม่มีสถานีที่ชื่นชอบครับ ลองกดหัวใจที่หน้าสถานีได้เลย!</p>
            <a href="{{ route('driver.map') }}" class="btn btn-primary">ไปดูแผนที่</a>
        </div>
        @endforelse
    </div>
</div>
@endsection
