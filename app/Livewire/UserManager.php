<?php

namespace App\Livewire;

use App\Models\Site;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class UserManager extends Component
{
    public ?Site $site = null;

    public ?int $editingUserId = null;

    public string $name = '';

    public string $email = '';

    public string $role = 'vendedor';

    public string $search = '';

    public function edit(int $userId): void
    {
        $user = $this->usersQuery()->findOrFail($userId);

        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingUserId)],
            'role' => ['required', 'in:administrador,gestor,vendedor'],
        ]);

        $user = $this->usersQuery()->findOrFail($this->editingUserId);
        $user->update($validated);

        $this->resetForm();
        session()->flash('status', 'Utilizador atualizado com sucesso.');
    }

    public function delete(int $userId): void
    {
        if ($userId === auth()->id()) {
            session()->flash('error', 'Não pode excluir a sua própria conta.');
            return;
        }

        $this->usersQuery()->findOrFail($userId)->delete();
        session()->flash('status', 'Utilizador excluído com sucesso.');
    }

    public function resetForm(): void
    {
        $this->reset(['editingUserId', 'name', 'email', 'role']);
        $this->role = 'vendedor';
        $this->resetValidation();
    }

    public function render(): mixed
    {
        $users = $this->usersQuery()
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($query): void {
                    $query->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->latest()
            ->get();

        return view('livewire.user-manager', ['users' => $users]);
    }

    private function usersQuery()
    {
        if (! $this->site) {
            abort_unless(auth()->user()?->isAdministrator(), 403);
            return User::query();
        }

        abort_unless($this->site->isManageableBy(auth()->user()), 403);

        return User::query()->whereHas('sites', fn ($query) => $query->whereKey($this->site->id));
    }
}
