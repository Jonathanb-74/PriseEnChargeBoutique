<?php

namespace App\Livewire\Users;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public string $role = 'technicien';

    public bool $is_assignable = true;

    public function mount(): void
    {
        $this->authorize('viewAny', User::class);
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingId)],
            'role' => ['required', Rule::enum(UserRole::class)],
            'is_assignable' => ['boolean'],
        ];
    }

    public function edit(User $user): void
    {
        $this->authorize('update', $user);

        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role->value;
        $this->is_assignable = $user->is_assignable;
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'name', 'email', 'role', 'is_assignable']);
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            $this->authorize('update', $user);

            // Le rôle a sa propre policy (interdit sur soi-même) : ne pas laisser le
            // formulaire d'édition la contourner.
            if (! auth()->user()->can('updateRole', $user)) {
                unset($data['role']);
            }

            $user->update($data);

            session()->flash('status', "Utilisateur {$user->name} mis à jour.");
        } else {
            $this->authorize('create', User::class);

            $user = User::create([
                ...$data,
                'password' => null,
                'email_verified_at' => null,
            ]);

            Password::sendResetLink(['email' => $user->email]);

            session()->flash('status', "Utilisateur {$user->name} créé. Un email lui a été envoyé pour définir son mot de passe.");
        }

        $this->cancelEdit();
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

    public function resetPassword(User $user): void
    {
        $this->authorize('resetPassword', $user);

        $user->forceFill(['password' => null])->save();

        Password::sendResetLink(['email' => $user->email]);

        session()->flash('status', "Email de réinitialisation du mot de passe envoyé à {$user->name}.");
    }

    public function resetTwoFactor(User $user): void
    {
        $this->authorize('resetTwoFactor', $user);

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        session()->flash('status', "2FA réinitialisée pour {$user->name}. Il devra la reconfigurer à sa prochaine connexion.");
    }

    public function render()
    {
        return view('livewire.users.index', [
            'users' => User::query()->orderBy('name')->get(),
            'roles' => UserRole::cases(),
        ]);
    }
}
