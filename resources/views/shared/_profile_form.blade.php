@php
    $isProvider = auth()->user()->role === 'provider';
    $birthValue = old('birth_date');
    if ($birthValue === null && $user->birth_date) {
        $birthValue = $user->birth_date instanceof \DateTimeInterface
            ? $user->birth_date->format('Y-m-d')
            : \Illuminate\Support\Str::before((string) $user->birth_date, ' ');
    }
@endphp

<form method="POST" action="{{ $updateRoute }}">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label class="form-label fw-medium">ชื่อ-นามสกุล</label>
        <input type="text" name="name"
            class="form-control rounded-3 @error('name') is-invalid @enderror"
            value="{{ old('name', $user->name) }}" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label fw-medium">อีเมล</label>
        <input type="email" name="email"
            class="form-control rounded-3 @error('email') is-invalid @enderror"
            value="{{ old('email', $user->email) }}"
            {{ $isProvider ? 'required' : '' }}>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @unless($isProvider)
            <div class="form-text">ไม่บังคับสำหรับคนขับ</div>
        @endunless
    </div>

    <div class="mb-3">
        <label class="form-label fw-medium">เบอร์โทร</label>
        <input type="text" name="phone"
            class="form-control rounded-3 @error('phone') is-invalid @enderror"
            value="{{ old('phone', $user->phone) }}"
            maxlength="10" inputmode="numeric" required>
        @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label fw-medium">เลขบัตรประชาชน</label>
        <input type="text" name="citizen_id"
            class="form-control rounded-3 @error('citizen_id') is-invalid @enderror"
            value="{{ old('citizen_id', $user->citizen_id) }}"
            maxlength="13" inputmode="numeric" placeholder="13 หลัก">
        @error('citizen_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <label class="form-label fw-medium">วันเกิด</label>
        <input type="date" name="birth_date"
            class="form-control rounded-3 @error('birth_date') is-invalid @enderror"
            value="{{ $birthValue }}">
        @error('birth_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary rounded-3">
            <i class="bi bi-check-lg me-1"></i>บันทึก
        </button>
        <a href="{{ $backRoute }}" class="btn btn-light rounded-3">ยกเลิก</a>
    </div>
</form>
