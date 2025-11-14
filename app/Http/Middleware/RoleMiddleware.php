<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            abort(403);
        }

        // user()->role bisa enum/string, ambil string-nya secara aman
        $r = auth()->user()->role;
        $userRole = $r instanceof \BackedEnum ? $r->value : (string) $r;

        if (!in_array($userRole, $roles, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
