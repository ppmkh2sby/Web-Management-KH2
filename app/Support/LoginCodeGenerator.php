<?php

namespace App\Support;

use App\Enum\Role;
use App\Models\User;
use Illuminate\Support\Str;

class LoginCodeGenerator
{
    public static function generate(Role|string $role): string
    {
        $role = $role instanceof Role ? $role : Role::from($role);

        $prefix = match ($role) {
            Role::SANTRI => 'S',
            Role::WALI => 'W',
            Role::PENGURUS => 'P',
            Role::DEWAN_GURU => 'DG',
            Role::ADMIN => 'ADM',
            default => 'USR',
        };

        do {
            $code = sprintf('%s-%s', $prefix, strtoupper(Str::random(8)));
            $exists = User::where('login_code', $code)->exists();

            if ($role === Role::SANTRI && ! $exists) {
                $exists = \App\Models\Santri::where('code', $code)->exists();
            }
        } while ($exists);

        return $code;
    }
}
