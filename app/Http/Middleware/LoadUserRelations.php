<?php

namespace App\Http\Middleware;

use App\Enum\Role;
use Closure;
use Illuminate\Http\Request;

class LoadUserRelations
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $user = auth()->user();
            $user?->loadMissing('santri');

            if ($user?->role === Role::WALI) {
                $user->loadMissing('waliOf');
            }
        }

        return $next($request);
    }
}
