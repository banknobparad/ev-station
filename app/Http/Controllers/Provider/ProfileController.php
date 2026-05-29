<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('provider.profile', [
            'user' => Auth::user(),
        ]);
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = Auth::user();

        $user->update($request->only([
            'name',
            'email',
            'phone',
            'citizen_id',
            'birth_date',
        ]));

        return redirect()->route('provider.profile.edit')
            ->with('success', 'บันทึกโปรไฟล์เรียบร้อยแล้ว');
    }
}
