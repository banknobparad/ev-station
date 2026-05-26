@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center mt-5">
            <h1>ยินดีต้อนรับสู่ EV Station</h1>
            <p class="lead">ระบบจัดการสถานีชาร์จรถยนต์ไฟฟ้า</p>
            <a href="{{ route('login') }}" class="btn btn-primary">เข้าสู่ระบบ</a>
        </div>
    </div>
</div>
@endsection