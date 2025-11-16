<?php

namespace App\Policies;

use App\Models\ProgressKeilmuan;
use App\Models\User;
use App\Policies\Concerns\HandlesSantriAuthorization;

class ProgressKeilmuanPolicy
{
    use HandlesSantriAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->isStaff($user) || $user->role === \App\Enum\Role::SANTRI || $user->role === \App\Enum\Role::WALI;
    }

    public function view(User $user, ProgressKeilmuan $progress): bool
    {
        return $this->canViewSantriRecord($user, $progress->santri_id);
    }

    public function create(User $user): bool
    {
        return $this->isStaff($user);
    }

    public function update(User $user, ProgressKeilmuan $progress): bool
    {
        return $this->isStaff($user);
    }

    public function delete(User $user, ProgressKeilmuan $progress): bool
    {
        return $this->isStaff($user);
    }
}
