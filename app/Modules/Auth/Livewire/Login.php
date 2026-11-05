<?php

declare(strict_types=1);

namespace App\Modules\Auth\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Component;

class Login extends Component
{
    public string $login = '';
    public string $email = '';
    public string $password = '';

    public function authenticate(): void
    {
        $identifier = trim($this->login !== '' ? $this->login : $this->email);

        $this->validate([
            'password' => ['required', 'string'],
        ]);
        if ($identifier === '') {
            $this->addError('login', __('auth.login_required'));
            return;
        }

        $key = $this->throttleKey($identifier);
        $maxAttempts = (int) config('petora.security.lockout_attempts', 5);
        $decay = (int) config('petora.security.lockout_minutes', 15) * 60;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('login', __('auth.throttle', ['seconds' => $seconds]));
            return;
        }

        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $credentials = [$field => $identifier, 'password' => $this->password];

        if (! Auth::attempt($credentials, false)) {
            RateLimiter::hit($key, $decay);
            $this->addError('login', __('auth.failed'));
            return;
        }

        RateLimiter::clear($key);

        $user = Auth::user();
        if ($user && ($user->status ?? 'active') !== 'active') {
            Auth::logout();
            $this->addError('login', __('auth.inactive'));
            return;
        }

        session()->regenerate();
        $this->redirect(route('dashboard'), navigate: false);
    }

    protected function throttleKey(string $identifier): string
    {
        return Str::lower($identifier).'|'.request()->ip();
    }

    public function render()
    {
        return view('modules.auth.login');
    }
}
