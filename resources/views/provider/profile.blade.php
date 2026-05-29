@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 640px;">

    <div class="mb-4">
        <h4 class="fw-bold mb-1">โปรไฟล์</h4>
        <p class="text-muted small mb-0">แก้ไขข้อมูลบัญชีผู้ให้บริการ</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            @include('shared._profile_form', [
                'user' => $user,
                'updateRoute' => route('provider.profile.update'),
                'backRoute' => route('provider.dashboard'),
            ])
        </div>
    </div>
</div>
@endsection
