<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $userRole = strtolower(optional(Auth::user()->role)->nama_role ?? '');
        if ($userRole !== 'admin') {
            abort(403, 'Akses khusus admin.');
        }

        return $next($request);
    }
}
