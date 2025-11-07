<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function view(User $authUser, User $target): bool
    {
        return $authUser->id === $target->id || $authUser->hasRole('admin');
    }

    public function update(User $authUser, User $target): bool
    {
        return $authUser->id === $target->id || $authUser->hasRole('admin');
    }

    public function delete(User $authUser, User $target): bool
    {
        return $authUser->id === $target->id || $authUser->hasRole('admin');
    }
}