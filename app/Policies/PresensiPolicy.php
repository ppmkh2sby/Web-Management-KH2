<?php

namespace App\Policies;

use App\Models\Presensi;
use App\Models\User;
use App\Enum\Role;

class PresensiPolicy
{
    private function isKetertiban(User $user): bool
    {
        return $user->isKetertiban();
    }

    private function owns(User $user, Presensi $presensi): bool
    {
        return $user->role === Role::SANTRI
            && $user->santri
            && $presensi->santri_id === $user->santri->id;
    }

    private function team(User $user): string
    {
        return strtolower(trim((string) ($user->santri?->tim ?? '')));
    }

    public function viewAny(User $user): bool
    {
        return ($user->role === \App\Enum\Role::SANTRI && $user->santri)
            || in_array($user->role, [Role::PENGURUS, Role::DEWAN_GURU], true)
            || $this->isKetertiban($user);
    }

    public function view(User $user, Presensi $presensi): bool
    {
        return $this->isKetertiban($user)
            || $this->owns($user, $presensi)
            || in_array($user->role, [Role::PENGURUS, Role::DEWAN_GURU], true);
    }

    public function create(User $user): bool
    {
        if ($this->isKetertiban($user)) {
            return true;
        }

        if ($user->role === Role::DEWAN_GURU) {
            return $user->kelasAjar()->exists();
        }

        // Izinkan santri tim KTB
        return $user->role === Role::SANTRI
            && in_array(strtolower($user->teamName()), ['ketertiban', 'ktb'], true);
    }

    public function update(User $user, Presensi $presensi): bool
    {
        return $this->isKetertiban($user);
    }

    public function delete(User $user, Presensi $presensi): bool
    {
        return $this->isKetertiban($user);
    }
}
