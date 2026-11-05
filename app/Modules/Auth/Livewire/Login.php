<?php

declare(strict_types=1);

namespace App\Modules\Auth\Livewire;

use Livewire\Component;

class Login extends Component
{
    public string $email = '';
    public string $password = '';

    public function authenticate(): void
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! auth()->attempt(['email' => $this->email, 'password' => $this->password])) {
            $this->addError('email', 'Kredensial tidak valid.');
            return;
        }

        session()->regenerate();
        $this->redirect(route('dashboard'));
    }

    public function render()
    {
        return view('modules.auth.login');
    }
}
