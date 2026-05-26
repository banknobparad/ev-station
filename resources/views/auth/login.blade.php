@extends('layouts.app')

@section('content')
@php
    $step = request()->query('step') ?: session('login_step'); // null | otp | password
    $role = request()->query('role') ?: session('login_role'); // driver | provider | admin | null
    $phone = request()->query('phone') ?: session('login_phone');
@endphp

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    เข้าสู่ระบบ
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- SECTION 1: กรอกเบอร์โทร --}}
                    @if (!$step)
                        <form method="POST" action="{{ route('login.phone') }}">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">เบอร์โทร (10 หลัก)</label>
                                <input
                                    type="text"
                                    name="phone"
                                    class="form-control @error('phone') is-invalid @enderror"
                                    value="{{ old('phone') }}"
                                    required
                                    inputmode="numeric"
                                    placeholder="เช่น 0912345678"
                                >
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                ต่อไป
                            </button>
                        </form>

                        <div class="text-muted small mt-3">
                            ระบบจะเช็คในฐานข้อมูลว่าเบอร์นี้เป็น role อะไร แล้วพาไปขั้นถัดไปอัตโนมัติ
                        </div>
                    @endif

                    {{-- SECTION 2: Driver OTP --}}
                    @if ($step === 'otp')
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <div class="fw-semibold">ยืนยัน OTP (Driver)</div>
                                <div class="text-muted small">เบอร์: {{ $phone }}</div>
                            </div>
                            <a href="{{ route('login.reset') }}" class="btn btn-outline-secondary btn-sm">
                                เปลี่ยนเบอร์
                            </a>
                        </div>

                        <form method="POST" action="{{ route('login.otp') }}">
                            @csrf

                            <input type="hidden" name="phone" value="{{ $phone }}">

                            <div class="mb-3">
                                <label class="form-label">OTP (6 หลัก)</label>
                                <input
                                    type="text"
                                    name="otp"
                                    class="form-control @error('otp') is-invalid @enderror"
                                    value="{{ old('otp') }}"
                                    required
                                    inputmode="numeric"
                                    placeholder="กรอกเลข 6 หลักอะไรก็ได้"
                                >
                                @error('otp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-success w-100">
                                เข้าสู่ระบบ
                            </button>
                        </form>

                        <div class="text-muted small mt-3">
                            OTP เป็นแบบ mock: ใส่เลข 6 หลักได้เลย
                        </div>
                    @endif

                    {{-- SECTION 3: Admin/Provider Email+Password --}}
                    @if ($step === 'password')
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <div class="fw-semibold">Email + Password ({{ $role ?? 'admin/provider' }})</div>
                                <div class="text-muted small">เบอร์: {{ $phone }}</div>
                            </div>
                            <a href="{{ route('login.reset') }}" class="btn btn-outline-secondary btn-sm">
                                เปลี่ยนเบอร์
                            </a>
                        </div>

                        <form method="POST" action="{{ route('login.password') }}">
                            @csrf

                            <input type="hidden" name="phone" value="{{ $phone }}">

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input
                                    type="email"
                                    name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}"
                                    required
                                    autocomplete="email"
                                >
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input
                                    type="password"
                                    name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    required
                                    autocomplete="current-password"
                                >
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                เข้าสู่ระบบ
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
