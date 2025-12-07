<?php

namespace App\Support;

use App\Enum\Role;
use App\Models\User;

class RedirectPath
{
    /**
     * Tentukan path redirect sesuai role user.
     */
    public static function forUser(?User $user): string
    {
        if (! $user) {
            return route('landing');
        }

        if ($user->role === Role::WALI) {
            $firstChild = $user->waliOf()->orderBy('santris.id')->first();

            if ($firstChild && $firstChild->code) {
                return route('wali.anak.overview', $firstChild->code);
            }

            return route('profile.edit');
        }

        return match ($user->role) {
            Role::SANTRI,
            Role::DEWAN_GURU,
            Role::PENGURUS => route('santri.home'),
            Role::ADMIN => route('admin.users.index'),
            default => route('profile.edit'),
        };
    }
}
