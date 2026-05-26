@extends('layouts.app')

@section('content')
@php
    $registerStep = request()->query('step') ?: session('register_step'); // null | otp
    $registerPhone = request()->query('phone') ?: session('register_phone');
@endphp
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    สมัครสมาชิก (Driver) ด้วยเบอร์โทร
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

                    <form method="POST" action="{{ route('register.phone') }}">
                        @csrf

                        {{-- STEP 1: กรอกเบอร์โทร --}}
                        @if ($registerStep !== 'otp')
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

                            <button type="submit" class="btn btn-success w-100">
                                ยืนยันเบอร์โทร
                            </button>

                            <div class="text-muted small mt-3">
                                หลังจากกดยืนยัน ระบบจะให้คุณกรอก OTP ในหน้าเดียวกัน (OTP เป็น mock)
                            </div>
                        @endif

                        {{-- STEP 2: กรอก OTP --}}
                        @if ($registerStep === 'otp')
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <div class="fw-semibold">ยืนยัน OTP</div>
                                    <div class="text-muted small">เบอร์: {{ $registerPhone }}</div>
                                </div>
                                <a href="{{ route('register.phone') }}" class="btn btn-outline-secondary btn-sm">
                                    เปลี่ยนเบอร์
                                </a>
                            </div>

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

                            <input type="hidden" name="phone" value="{{ $registerPhone }}">

                            <button type="submit" class="btn btn-primary w-100">
                                ยืนยันและเข้าสู่ระบบ
                            </button>

                            <div class="text-muted small mt-3">
                                OTP ในโปรเจคนี้เป็นแบบ mock (ใส่เลข 6 หลักได้เลย)
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

