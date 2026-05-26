<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $reviewCount = $user->reviews()->count();
        $visitedCount = $user->reviews()->select('station_id')->distinct()->count();
        $recentComments = $user->reviews()->with('station')->latest()->limit(5)->get();

        return view('driver.account', compact('user', 'reviewCount', 'visitedCount', 'recentComments'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
        ], [
            'name.required' => 'กรุณากรอกชื่อของคุณ',
            'name.max' => 'ชื่อต้องไม่เกิน 255 ตัวอักษร',
            'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
            'email.unique' => 'อีเมลนี้ถูกใช้งานแล้ว',
        ]);

        $user->update([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
        ]);

        return redirect()->route('driver.account')->with('success', 'บันทึกข้อมูลส่วนตัวเรียบร้อยแล้ว');
    }
}
