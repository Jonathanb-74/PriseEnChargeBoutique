<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $model): bool
    {
        return $user->isAdmin() && $model->usesLocalAuth();
    }

    public function updateRole(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->isNot($model);
    }

    public function updateAssignable(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    public function resetPassword(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->isNot($model) && $model->usesLocalAuth();
    }

    public function resetTwoFactor(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->isNot($model) && $model->usesLocalAuth();
    }
}
