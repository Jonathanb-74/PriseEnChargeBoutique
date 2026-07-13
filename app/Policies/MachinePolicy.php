<?php

namespace App\Policies;

use App\Models\Machine;
use App\Models\User;

class MachinePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Machine $machine): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Machine $machine): bool
    {
        return true;
    }

    public function delete(User $user, Machine $machine): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Machine $machine): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Machine $machine): bool
    {
        return $user->isAdmin();
    }
}
