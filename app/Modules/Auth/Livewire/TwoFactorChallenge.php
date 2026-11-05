<?php

declare(strict_types=1);

namespace App\Modules\Auth\Livewire;

use App\Models\User;
use App\Modules\Auth\Services\TwoFactorService;
use App\Support\AuditService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Livewire\Component;

class TwoFactorChallenge extends Component
{
    public string $code = '';

    public function mount(): void
    {
        if (! session()->has('auth.2fa_pending_user_id')) {
            $this->redirect(route('login'), navigate: false);
        }
    }

    public function verify(TwoFactorService $twoFactor): void
    {
        $this->validate(['code' => ['required', 'string']]);
        $id = session('auth.2fa_pending_user_id');
        $user = $id ? User::query()->find($id) : null;
        if (! $user || empty($user->two_factor_secret)) {
            session()->forget('auth.2fa_pending_user_id');
            $this->redirect(route('login'), navigate: false);

            return;
        }

        $secret = $user->two_factor_secret;
        try {
            $secret = Crypt::decryptString($secret);
        } catch (\Throwable) {
            // stored plaintext in legacy/dev
        }

        if (! $twoFactor->verify($secret, preg_replace('/\s+/', '', $this->code))) {
            AuditService::log('auth.2fa_failed', User::class, $user->id);
            $this->addError('code', __('Kode verifikasi tidak valid.'));

            return;
        }

        session()->forget('auth.2fa_pending_user_id');
        session(['auth.2fa_verified' => true]);
        Auth::login($user);
        session()->regenerate();
        AuditService::log('auth.2fa_passed', User::class, $user->id);
        $user->forceFill(['last_login_at' => now()])->save();
        $this->redirect(route('dashboard'), navigate: false);
    }

    public function render()
    {
        return view('modules.auth.two-factor-challenge');
    }
}
