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
            $firstChildCode = $user->relationLoaded('waliOf')
                ? collect($user->waliOf)->sortBy('nama_lengkap')->first()?->code
                : $user->waliOf()
                    ->orderBy('santris.nama_lengkap')
                    ->value('santris.code');

            if (filled($firstChildCode)) {
                return route('wali.anak.overview', ['santriCode' => $firstChildCode]);
            }

            return route('profile.edit');
        }

        // Semua role inti (santri, pengurus, dewan guru) diarahkan ke santri/home sebagai beranda utama.
        if (in_array($user->role, [Role::SANTRI, Role::DEWAN_GURU, Role::PENGURUS], true)) {
            return route('santri.home');
        }

        return match ($user->role) {
            Role::ADMIN => route('admin.users.index'),
            default => route('profile.edit'),
        };
    }
}
