<?php

namespace App\Policies;

use App\Models\Intake;
use App\Models\User;

class IntakePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Intake $intake): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Intake $intake): bool
    {
        return true;
    }

    public function delete(User $user, Intake $intake): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Intake $intake): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Intake $intake): bool
    {
        return $user->isAdmin();
    }

    public function viewMachinePassword(User $user, Intake $intake): bool
    {
        return true;
    }

    public function downloadPdf(User $user, Intake $intake): bool
    {
        return true;
    }

    public function sendClientNotification(User $user, Intake $intake): bool
    {
        return true;
    }
}
