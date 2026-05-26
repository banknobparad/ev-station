@extends('layouts.app')

@section('content')
<div class="container py-4">

    {{-- แจ้งเตือน --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- รูปภาพและข้อมูลหลัก --}}
    <div class="card mb-4">
        @if($station->image)
            <img src="{{ asset('storage/' . $station->image) }}" class="card-img-top" style="height:250px; object-fit:cover;">
        @endif
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h4>{{ $station->name }}</h4>
                    <p class="text-muted">📍 {{ $station->address }}</p>
                    <p>🕐 เปิด {{ $station->open_time ?? '-' }} - {{ $station->close_time ?? '-' }}</p>
                </div>
                {{-- ปุ่ม Favorite --}}
                <form action="{{ route('driver.favorite.toggle', $station) }}" method="POST">
                    @csrf
                    @php
                        $isFav = auth()->user()->favorites->where('station_id', $station->id)->count() > 0;
                    @endphp
                    <button class="btn {{ $isFav ? 'btn-danger' : 'btn-outline-danger' }} btn-lg">
                        {{ $isFav ? '❤️ Favorited' : '🤍 Favorite' }}
                    </button>
                </form>
            </div>

            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $station->lat }},{{ $station->lng }}"
               target="_blank" class="btn btn-primary">
                🗺️ นำทางไปสถานีนี้
            </a>
            <a href="{{ route('driver.map') }}" class="btn btn-secondary">← กลับแผนที่</a>
        </div>
    </div>

    {{-- หัวชาร์จ --}}
    <div class="card mb-4">
        <div class="card-header"><strong>🔌 หัวชาร์จ</strong></div>
        <div class="card-body">
            @forelse($station->connectors as $connector)
            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                <div>
                    <span class="badge bg-primary">{{ $connector->type }}</span>
                    <span class="ms-2">จำนวน {{ $connector->total }} หัว</span>
                </div>
                @if($connector->status === 'available')
                    <span class="badge bg-success">Available</span>
                @elseif($connector->status === 'busy')
                    <span class="badge bg-warning text-dark">Busy</span>
                @else
                    <span class="badge bg-danger">Maintenance</span>
                @endif
            </div>
            @empty
            <p class="text-muted">ยังไม่มีข้อมูลหัวชาร์จครับ</p>
            @endforelse
        </div>
    </div>

    {{-- ฟอร์มรีวิว --}}
    @php
        $myReview = $station->reviews->where('user_id', auth()->id())->first();
    @endphp

    @if(!$myReview)
    <div class="card mb-4">
        <div class="card-header"><strong>✍️ เขียนรีวิว</strong></div>
        <div class="card-body">
            <form action="{{ route('driver.review.store', $station) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">คะแนน</label>
                    <div class="d-flex gap-2">
                        @for($i = 1; $i <= 5; $i++)
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="star" value="{{ $i }}" id="star{{ $i }}" required>
                            <label class="form-check-label" for="star{{ $i }}">{{ $i }} ⭐</label>
                        </div>
                        @endfor
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">คอมเมนต์</label>
                    <textarea name="comment" class="form-control" rows="3" placeholder="เช่น หัวชาร์จ CCS2 ว่างครับ ชาร์จเร็วดี"></textarea>
                </div>
                <button type="submit" class="btn btn-success">ส่งรีวิว</button>
            </form>
        </div>
    </div>
    @endif

    {{-- รายการรีวิว --}}
    <div class="card">
        <div class="card-header"><strong>⭐ รีวิวทั้งหมด ({{ $station->reviews->count() }})</strong></div>
        <div class="card-body">
            @forelse($station->reviews as $review)
            <div class="border-bottom pb-2 mb-2">
                <div class="d-flex justify-content-between">
                    <div>
                        <strong>{{ $review->user->name }}</strong>
                        <span class="text-warning ms-2">
                            {{ str_repeat('★', $review->star) }}{{ str_repeat('☆', 5 - $review->star) }}
                        </span>
                    </div>
                    {{-- ลบได้เฉพาะรีวิวตัวเอง --}}
                    @if($review->user_id === auth()->id())
                    <form action="{{ route('driver.review.destroy', $review) }}" method="POST"
                          onsubmit="return confirm('ลบรีวิวนี้?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">ลบ</button>
                    </form>
                    @endif
                </div>
                <p class="mb-0 mt-1">{{ $review->comment }}</p>
                <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
            </div>
            @empty
            <p class="text-muted">ยังไม่มีรีวิวครับ เป็นคนแรกได้เลย!</p>
            @endforelse
        </div>
    </div>

</div>
@endsection