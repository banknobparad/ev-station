@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4>คอมเมนต์ของสถานี</h4>
            <p class="text-muted mb-0">{{ $station->name }} — {{ $station->address }}</p>
        </div>
        <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">← กลับ</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @forelse($reviews as $review)
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>{{ $review->user->name }}</strong>
                    <span class="text-warning ms-2">
                        {{ str_repeat('★', $review->star) }}{{ str_repeat('☆', 5 - $review->star) }}
                    </span>
                    <small class="text-muted ms-2">{{ $review->created_at->diffForHumans() }}</small>
                    <p class="mt-2 mb-0">{{ $review->comment ?? 'ไม่มีคอมเมนต์' }}</p>
                </div>
                <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST"
                      onsubmit="return confirm('ยืนยันการลบคอมเมนต์นี้?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger btn-sm">ลบ</button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="alert alert-info">ยังไม่มีคอมเมนต์ในสถานีนี้ครับ</div>
    @endforelse
</div>
@endsection