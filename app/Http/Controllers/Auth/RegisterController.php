<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    // ===== REGISTER (Driver: สมัครด้วยเบอร์โทรอย่างเดียว) =====

    // ขั้นที่ 1: กรอกเบอร์โทร (และจะโชว์ OTP ภายในหน้าเดียวเมื่อถึงขั้น confirm)
    public function registerStep1(Request $request)
    {
        // ใช้ query param เป็นตัวสลับขั้นแทน (เพื่อไม่พึ่ง session มากเกินไป)
        return view('auth.register-phone');
    }

    public function registerStep1Post(Request $request)
    {
        // ถ้ามี `otp` ใน request = อยู่ขั้นยืนยัน OTP
        if ($request->filled('otp')) {
            $request->validate([
                'phone' => 'required|digits:10|unique:users,phone',
                'otp'   => 'required|digits:6',
            ], [
                'phone.required' => 'กรุณายืนยันเบอร์โทรอีกครั้งครับ',
                'phone.digits'   => 'เบอร์โทรต้องเป็นตัวเลข 10 หลักครับ',
                'phone.unique'   => 'เบอร์โทรนี้มีในระบบแล้วครับ',
                'otp.required'   => 'กรุณากรอก OTP ครับ',
                'otp.digits'     => 'OTP ต้องเป็นตัวเลข 6 หลักครับ',
            ]);

            $phone = $request->input('phone');

            $user = User::create([
                'name'     => 'Driver ' . $phone,
                'email'    => null,
                'phone'    => $phone,
                'role'     => 'driver',
                'status'   => 'active',
                'password' => Hash::make($phone),
            ]);

            Auth::login($user);
            return redirect()->route('driver.map');
        }

        // ===== Step phone: กรอกเบอร์ -> สลับไปขั้น OTP (ในหน้าเดียวกัน) =====
        $request->validate([
            'phone' => 'required|digits:10|unique:users,phone',
        ], [
            'phone.required' => 'กรุณากรอกเบอร์โทรศัพท์ครับ',
            'phone.digits'   => 'เบอร์โทรต้องเป็นตัวเลข 10 หลักครับ',
            'phone.unique'   => 'เบอร์โทรนี้มีในระบบแล้วครับ',
        ]);

        $phone = $request->input('phone');
        return redirect()->route('register.phone', ['step' => 'otp', 'phone' => $phone]);
    }

    // ===== LOGIN (หน้าเดียวแบ่ง section ตาม session) =====

    // ขั้นที่ 1: กรอกเบอร์โทร
    public function loginStep1()
    {
        return view('auth.login');
    }

    public function loginStep1Post(Request $request)
    {
        $request->validate([
            'phone' => 'required|digits:10',
        ], [
            'phone.required' => 'กรุณากรอกเบอร์โทรศัพท์ครับ',
            'phone.digits'   => 'เบอร์โทรต้องเป็นตัวเลข 10 หลักครับ',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return back()->withErrors(['phone' => 'ไม่พบเบอร์โทรนี้ในระบบครับ']);
        }

        // Driver → ไป OTP
        if ($user->role === 'driver') {
            return redirect()->route('login', [
                'step'  => 'otp',
                'phone' => $request->phone,
                'role'  => 'driver',
            ]);
        }

        // Admin/Provider → ไปกรอก Email + Password
        return redirect()->route('login', [
            'step'  => 'password',
            'phone' => $request->phone,
            'role'  => $user->role,
        ]);
    }

    // (GET) สำหรับเข้ามาหน้า OTP โดยตรง
    public function loginOtp()
    {
        // flow หลักจะใช้ query param ในหน้าเดียวอยู่แล้ว
        return redirect()->route('login');
    }

    // (POST) OTP mock เพื่อเข้าสู่ระบบ
    public function loginOtpPost(Request $request)
    {
        $request->validate([
            'phone' => 'required|digits:10',
            'otp'   => 'required|digits:6',
        ], [
            'phone.required' => 'กรุณายืนยันเบอร์โทรอีกครั้งครับ',
            'phone.digits'   => 'เบอร์โทรต้องเป็นตัวเลข 10 หลักครับ',
            'otp.required' => 'กรุณากรอก OTP ครับ',
            'otp.digits'   => 'OTP ต้องเป็นตัวเลข 6 หลักครับ',
        ]);

        $user = User::where('phone', $request->input('phone'))->first();

        if (!$user) {
            return redirect()->route('login')->withErrors(['phone' => 'ไม่พบผู้ใช้ครับ']);
        }

        if ($user->role !== 'driver') {
            return back()->withErrors(['phone' => 'บัญชีนี้ไม่ใช่ driver ครับ']);
        }

        Auth::login($user);

        return redirect()->route('driver.map');
    }

    // (GET) สำหรับเข้ามาหน้า password โดยตรง
    public function loginPassword()
    {
        return redirect()->route('login');
    }

    public function loginPasswordPost(Request $request)
    {
        $request->validate([
            'phone' => 'required|digits:10',
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('phone', $request->input('phone'))
            ->where('email', $request->email)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['email' => 'อีเมลหรือรหัสผ่านไม่ถูกต้องครับ']);
        }

        Auth::login($user);

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('provider.dashboard');
    }

    // เปลี่ยนเบอร์/เริ่ม login ใหม่
    public function resetLogin()
    {
        return redirect()->route('login');
    }
}
