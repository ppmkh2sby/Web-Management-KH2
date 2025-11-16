<?php

namespace App\Policies;

use App\Models\Kehadiran;
use App\Models\User;
use App\Policies\Concerns\HandlesSantriAuthorization;

class KehadiranPolicy
{
    use HandlesSantriAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->isStaff($user) || $user->role === \App\Enum\Role::SANTRI || $user->role === \App\Enum\Role::WALI;
    }

    public function view(User $user, Kehadiran $kehadiran): bool
    {
        return $this->canViewSantriRecord($user, $kehadiran->santri_id);
    }

    public function create(User $user): bool
    {
        return $this->isStaff($user);
    }

    public function update(User $user, Kehadiran $kehadiran): bool
    {
        return $this->isStaff($user);
    }

    public function delete(User $user, Kehadiran $kehadiran): bool
    {
        return $this->isStaff($user);
    }
}
