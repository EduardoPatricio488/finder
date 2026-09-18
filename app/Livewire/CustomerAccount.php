<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.customer')]
class CustomerAccount extends Component
{
    public string $name = '';
    public string $email = '';
    public string $currentPassword = '';
    public string $newPassword = '';
    public string $newPassword_confirmation = '';

    public function mount(): void
    {
        $user = auth()->user();

        $this->name = (string) $user->name;
        $this->email = (string) $user->email;
    }

    public function saveProfile(): void
    {
        $user = auth()->user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $emailChanged = $validated['email'] !== $user->email;

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        $user->save();

        session()->flash('account-status', 'Dados da conta atualizados com sucesso.');
    }

    public function changePassword(): void
    {
        $this->validate([
            'currentPassword' => ['required', 'current_password'],
            'newPassword' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'currentPassword.current_password' => 'A palavra-passe atual está incorreta.',
            'newPassword.confirmed' => 'A confirmação da nova palavra-passe não coincide.',
        ]);

        auth()->user()->update([
            'password' => Hash::make($this->newPassword),
        ]);

        $this->reset(['currentPassword', 'newPassword', 'newPassword_confirmation']);

        session()->flash('password-status', 'Palavra-passe alterada com sucesso.');
    }

    public function logoutOtherSessions(): void
    {
        if (method_exists(Auth::getProvider(), 'retrieveById')) {
            Auth::logoutOtherDevices($this->currentPassword);
            $this->reset('currentPassword');
            session()->flash('security-status', 'As outras sessões foram terminadas.');
        }
    }

    public function render(): mixed
    {
        return view('livewire.customer-account', [
            'user' => auth()->user(),
        ]);
    }
}
