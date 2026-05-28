@extends('layouts.app')

@section('content')
    <div class="container py-4">

        {{-- แจ้งเตือน --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        {{-- ข้อมูลสถานี --}}
        <div class="card mb-4">

            @if ($station->image)
                <img src="{{ asset('storage/' . $station->image) }}" class="card-img-top"
                    style="height:250px; object-fit:cover;">
            @endif

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-start mb-3">

                    <div>
                        <h4 class="mb-2">
                            {{ $station->name }}
                        </h4>

                        <p class="text-muted mb-1">
                            <i class="bi bi-geo-alt-fill"></i>
                            {{ $station->address }}
                        </p>

                        <p class="text-muted mb-0">
                            <i class="bi bi-clock-fill"></i>
                            {{ $station->open_time ?? '-' }}
                            -
                            {{ $station->close_time ?? '-' }}
                        </p>
                    </div>

                    {{-- Favorite --}}
                    <form action="{{ route('driver.favorite.toggle', $station) }}" method="POST" class="ms-2">
                        @csrf

                        @php
                            $isFav = auth()->user()->favorites->where('station_id', $station->id)->count() > 0;
                        @endphp

                        <button type="submit"
                            class="btn btn-sm {{ $isFav ? 'btn-danger' : 'btn-outline-danger' }} rounded-pill">
                            <i class="bi bi-heart-fill"></i>
                        </button>

                    </form>

                </div>

                <div class="d-grid gap-2 d-sm-flex">


                    <a href="{{ route('driver.map') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i>
                        กลับ
                    </a>

                </div>

            </div>
        </div>

        @php

            $galleryImages = collect($reviewImages ?? [])
                ->merge($station->gallery_images ?? [])
                ->filter()
                ->unique()
                ->values();

            $galleryCount = $galleryImages->count();

        @endphp


        {{-- สิ่งอำนวยความสะดวก --}}
        @if ($station->facilities->count() > 0)
            <div class="card mb-4">

                <div class="card-header">
                    <strong>🏪 สิ่งอำนวยความสะดวก</strong>
                </div>

                <div class="card-body">

                    <div class="row">

                        @foreach ($station->facilities as $facility)
                            <div class="col-6 mb-3">

                                <div class="d-flex align-items-center">

                                    <div
                                        style="
                                font-size:1.5rem;
                                margin-right:10px;
                                min-width:30px;
                            ">

                                        @switch($facility->name)
                                            @case('ที่กินข้าว')
                                                <i class="fas fa-utensils" style="color:#ff6b6b;"></i>
                                            @break

                                            @case('ที่จอดรถ')
                                                <i class="fas fa-square" style="color:#4ecdc4;"></i>
                                            @break

                                            @case('ที่ชอปปิ้ง')
                                                <i class="fas fa-shopping-cart" style="color:#ffe66d;"></i>
                                            @break

                                            @case('ห้องน้ำ')
                                                <i class="fas fa-restroom" style="color:#95e1d3;"></i>
                                            @break

                                            @case('ร้านขายของชำ')
                                                <i class="fas fa-store" style="color:#ffa502;"></i>
                                            @break

                                            @case('ที่นั่งพัก')
                                                <i class="fas fa-chair" style="color:#a8d8ea;"></i>
                                            @break

                                            @case('WiFi')
                                                <i class="fas fa-wifi" style="color:#667eea;"></i>
                                            @break

                                            @case('สถานีอัดอากาศ')
                                                <i class="fas fa-wind" style="color:#74b9ff;"></i>
                                            @break

                                            @default
                                                <i class="fas fa-check-circle" style="color:#74b9ff;"></i>
                                        @endswitch

                                    </div>

                                    <span>
                                        {{ $facility->name }}
                                    </span>

                                </div>

                            </div>
                        @endforeach

                    </div>

                </div>

            </div>
        @endif


        {{-- รีวิว --}}
        @php
            $myReview = $myReview ?? null;
        @endphp

        @if (!$myReview)
            <div class="card mb-4">

                <div class="card-header">
                    <strong>✍️ เขียนรีวิว</strong>
                </div>

                <div class="card-body">

                    <form action="{{ route('driver.review.store', $station) }}" method="POST"
                        enctype="multipart/form-data">

                        @csrf

                        <div class="mb-3">

                            <label class="form-label">
                                คะแนน
                            </label>

                            <div class="d-flex gap-2">

                                @for ($i = 1; $i <= 5; $i++)
                                    <div class="form-check">

                                        <input class="form-check-input" type="radio" name="star"
                                            value="{{ $i }}" id="star{{ $i }}" required>

                                        <label class="form-check-label" for="star{{ $i }}">
                                            {{ $i }} ⭐
                                        </label>

                                    </div>
                                @endfor

                            </div>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                คอมเมนต์
                            </label>

                            <textarea name="comment" class="form-control" rows="3" placeholder="เช่น หัวชาร์จ CCS2 ว่างครับ ชาร์จเร็วดี"></textarea>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                แนบรูปภาพ (ถ้ามี)
                            </label>

                            <input type="file" name="images[]" class="form-control" accept="image/*" multiple>

                        </div>

                        <button type="submit" class="btn btn-success">
                            ส่งรีวิว
                        </button>

                    </form>

                </div>

            </div>
        @endif


        {{-- รีวิวทั้งหมด --}}
        <div class="card mb-4">

            <div class="card-header d-flex justify-content-between align-items-center">

                <strong>
                    ⭐ รีวิวทั้งหมด ({{ $totalReviews }})
                </strong>

                @if (!$showAllReviews && $totalReviews > 5)
                    <a href="{{ route('driver.station', ['station' => $station, 'view' => 'all']) }}"
                        class="btn btn-sm btn-outline-primary">
                        ดูทั้งหมด
                    </a>
                @endif

            </div>

            <div class="card-body">

                @forelse($station->reviews as $review)
                    <div class="border-bottom pb-2 mb-2">

                        <div class="d-flex justify-content-between">

                            <div>

                                <strong>
                                    {{ $review->user->name }}
                                </strong>

                                <span class="text-warning ms-2">
                                    {{ str_repeat('★', $review->star) }}
                                    {{ str_repeat('☆', 5 - $review->star) }}
                                </span>

                            </div>

                            @if ($review->user_id === auth()->id())
                                <form action="{{ route('driver.review.destroy', $review) }}" method="POST"
                                    onsubmit="return confirm('ลบรีวิวนี้?')">

                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-sm btn-outline-danger">
                                        ลบ
                                    </button>

                                </form>
                            @endif

                        </div>

                        <p class="mb-0 mt-1">
                            {{ $review->comment }}
                        </p>

                        @if (!empty($review->images))
                            <div class="mt-2 d-flex flex-wrap gap-2">

                                @foreach ($review->images as $reviewImage)
                                    <a href="{{ asset('storage/' . $reviewImage) }}" target="_blank">

                                        <img src="{{ asset('storage/' . $reviewImage) }}" class="rounded"
                                            style="
                                width:84px;
                                height:84px;
                                object-fit:cover;
                            ">

                                    </a>
                                @endforeach

                            </div>
                        @endif

                        <small class="text-muted">
                            {{ $review->created_at->diffForHumans() }}
                        </small>

                    </div>

                @empty

                    <p class="text-muted">
                        ยังไม่มีรีวิวครับ เป็นคนแรกได้เลย!
                    </p>
                @endforelse

            </div>

        </div>


        {{-- แกลเลอรี --}}
        @if ($galleryCount > 0)
            <div class="card mb-4">

                <div class="card-header">
                    <strong>📷 แกลเลอรีรูปภาพ</strong>
                </div>

                <div class="card-body">

                    <div class="row g-2">

                        @foreach ($galleryImages->take(3) as $index => $image)
                            <div class="col-4">

                                <div class="position-relative">

                                    {{-- รูป --}}
                                    <img src="{{ asset('storage/' . $image) }}" class="rounded w-100 gallery-preview"
                                        style="
                                height:120px;
                                object-fit:cover;
                                cursor:pointer;
                            "
                                        data-bs-toggle="modal" data-bs-target="#galleryModal"
                                        data-bs-slide-to="{{ $index }}">

                                    {{-- Overlay --}}
                                    @if ($index === 2 && $galleryCount > 3)
                                        <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center rounded"
                                            style="
                                background:rgba(0,0,0,.45);
                                color:white;
                                font-size:1.5rem;
                                font-weight:bold;
                                cursor:pointer;
                            "
                                            data-bs-toggle="modal" data-bs-target="#galleryModal"
                                            data-bs-slide-to="{{ $index }}">
                                            +{{ $galleryCount - 3 }}
                                        </div>
                                    @endif

                                </div>

                            </div>
                        @endforeach

                    </div>

                    <div class="text-muted mt-2" style="font-size:.9rem;">
                        กดรูปเพื่อดูภาพทั้งหมด
                    </div>

                </div>

            </div>


            {{-- Modal --}}
            <div class="modal fade" id="galleryModal" tabindex="-1" aria-hidden="true">

                <div class="modal-dialog modal-dialog-centered modal-xl">

                    <div class="modal-content bg-dark border-0">

                        {{-- ปิด --}}
                        <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                            data-bs-dismiss="modal" style="z-index:999;"></button>

                        {{-- Carousel --}}
                        <div id="galleryCarousel" class="carousel slide" data-bs-ride="false">

                            <div class="carousel-inner">

                                @foreach ($galleryImages as $index => $image)
                                    <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">

                                        <div class="d-flex justify-content-center align-items-center"
                                            style="height:80vh;">

                                            <img src="{{ asset('storage/' . $image) }}" class="img-fluid rounded"
                                                style="
                                        max-height:100%;
                                        object-fit:contain;
                                    ">

                                        </div>

                                    </div>
                                @endforeach

                            </div>

                            {{-- Prev --}}
                            <button class="carousel-control-prev" type="button" data-bs-target="#galleryCarousel"
                                data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>

                            {{-- Next --}}
                            <button class="carousel-control-next" type="button" data-bs-target="#galleryCarousel"
                                data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>

                        </div>

                    </div>

                </div>

            </div>
        @endif

    </div>


    {{-- Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const galleryModal = document.getElementById('galleryModal');

            if (galleryModal) {

                galleryModal.addEventListener('show.bs.modal', function(event) {

                    const trigger = event.relatedTarget;

                    const slideIndex = trigger.getAttribute('data-bs-slide-to');

                    const carousel = bootstrap.Carousel.getOrCreateInstance(
                        document.getElementById('galleryCarousel')
                    );

                    carousel.to(slideIndex);

                });

            }

        });
    </script>

@endsection
