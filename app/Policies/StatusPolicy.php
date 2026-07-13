<?php

namespace App\Policies;

use App\Models\Status;
use App\Models\User;

class StatusPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Status $status): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Status $status): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Status $status): bool
    {
        return $user->isAdmin();
    }
}
