<?php

namespace App\Policies\Concerns;

use App\Enum\Role;
use App\Models\User;

trait HandlesSantriAuthorization
{
    protected function isStaff(User $user): bool
    {
        return $user->role === Role::ADMIN || in_array($user->role->value, Role::staff(), true);
    }

    protected function ownsSantriRecord(User $user, int $santriId): bool
    {
        return $user->role === Role::SANTRI && optional($user->santri)->id === $santriId;
    }

    protected function guardianOfSantri(User $user, int $santriId): bool
    {
        return $user->role === Role::WALI && $user->waliOf()->where('santri_id', $santriId)->exists();
    }

    protected function canViewSantriRecord(User $user, int $santriId): bool
    {
        return $this->isStaff($user)
            || $this->ownsSantriRecord($user, $santriId)
            || $this->guardianOfSantri($user, $santriId);
    }
}
