<?php

namespace App\Policies;

use App\Models\LogKeluarMasuk;
use App\Models\User;
use App\Policies\Concerns\HandlesSantriAuthorization;

class LogKeluarMasukPolicy
{
    use HandlesSantriAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->isStaff($user) || $user->role === \App\Enum\Role::SANTRI || $user->role === \App\Enum\Role::WALI;
    }

    public function view(User $user, LogKeluarMasuk $log): bool
    {
        return $this->canViewSantriRecord($user, $log->santri_id);
    }

    public function create(User $user): bool
    {
        return $user->role === \App\Enum\Role::SANTRI;
    }

    public function update(User $user, LogKeluarMasuk $log): bool
    {
        return $this->ownsSantriRecord($user, $log->santri_id);
    }

    public function delete(User $user, LogKeluarMasuk $log): bool
    {
        return $this->ownsSantriRecord($user, $log->santri_id);
    }
}
