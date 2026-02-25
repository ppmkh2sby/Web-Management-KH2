<?php

namespace App\Policies;

use App\Models\Kafarah;
use App\Models\User;
use App\Policies\Concerns\HandlesSantriAuthorization;

class KafarahPolicy
{
    use HandlesSantriAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->isStaff($user) || $user->role === \App\Enum\Role::SANTRI || $user->role === \App\Enum\Role::WALI;
    }

    public function view(User $user, Kafarah $kafarah): bool
    {
        return $this->canViewSantriRecord($user, (int) $kafarah->santri_id);
    }

    public function create(User $user): bool
    {
        return $this->isStaff($user) || $user->isKetertiban();
    }

    public function update(User $user, Kafarah $kafarah): bool
    {
        return $this->isStaff($user) || $user->isKetertiban();
    }

    public function delete(User $user, Kafarah $kafarah): bool
    {
        return $this->isStaff($user) || $user->isKetertiban();
    }
}
