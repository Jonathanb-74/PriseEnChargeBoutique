<?php

namespace App\Livewire\Users;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public function mount(): void
    {
        $this->authorize('viewAny', User::class);
    }

    public function updateRole(User $user, string $role): void
    {
        $this->authorize('updateRole', $user);

        $user->update(['role' => UserRole::from($role)]);

        session()->flash('status', "Rôle de {$user->name} mis à jour.");
    }

    public function toggleAssignable(User $user): void
    {
        $this->authorize('updateAssignable', $user);

        $user->refresh();
        $user->update(['is_assignable' => ! $user->is_assignable]);

        session()->flash('status', $user->is_assignable
            ? "{$user->name} peut de nouveau être affecté à des prises en charge."
            : "{$user->name} ne peut plus être affecté à des prises en charge.");
    }

    public function render()
    {
        return view('livewire.users.index', [
            'users' => User::query()->orderBy('name')->get(),
            'roles' => UserRole::cases(),
        ]);
    }
}
