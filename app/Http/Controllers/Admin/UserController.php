<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // แสดงรายการ Provider ทั้งหมด
    public function index()
    {
        $providers = User::where('role', 'provider')->get();
        return view('admin.users.index', compact('providers'));
    }

    // หน้าฟอร์มสร้าง Provider
    public function create()
    {
        return view('admin.users.create');
    }

    // บันทึก Provider ใหม่
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'phone'    => 'required|digits:10|unique:users,phone',
            'password' => 'required|min:8|confirmed',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
            'role'     => 'provider',
            'status'   => 'active',
        ]);

        return redirect()->route('admin.users.index')
                         ->with('success', 'สร้างบัญชี Provider สำเร็จแล้วครับ');
    }

    // ลบ Provider
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')
                         ->with('success', 'ลบบัญชีสำเร็จแล้วครับ');
    }
}