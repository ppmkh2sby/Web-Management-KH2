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
            $role = $user?->role;

            if (in_array($role, [Role::SANTRI, Role::PENGURUS, Role::DEWAN_GURU], true)) {
                $user?->loadMissing(['santri:id,user_id,code,nama_lengkap,tim,gender']);
            }

            if ($role === Role::WALI) {
                $user?->loadMissing(['waliOf:id,code,nama_lengkap,tim,gender']);
            }
        }

        return $next($request);
    }
}
