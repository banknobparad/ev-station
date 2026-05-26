<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        // เช็ค status ก่อนเลย
        if ($user->status === 'pending') {
            abort(403, 'บัญชีของคุณรอการอนุมัติจาก Admin ');
        }

        if ($user->status === 'rejected') {
            abort(403, 'บัญชีของคุณถูกปฏิเสธ กรุณาติดต่อ Admin');
        }

        if (!in_array($user->role, $roles)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
