<?php

declare(strict_types=1);

namespace App\Modules\Auth\Livewire;

use App\Models\User;
use App\Modules\Auth\Services\TwoFactorService;
use App\Support\AuditService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class TwoFactorSetup extends Component
{
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 900;

    public string $code = '';

    public string $secret = '';

    public string $provisioningUri = '';

    public array $recoveryCodes = [];

    public function mount(TwoFactorService $twoFactor): void
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->hasAnyRole(['Admin', 'Super Admin']), 403);

        if ($user->two_factor_confirmed_at) {
            return;
        }

        $this->secret = (string) session('auth.2fa_enrollment_secret', '');
        if ($this->secret === '') {
            $this->secret = $twoFactor->generateSecret();
            session(['auth.2fa_enrollment_secret' => $this->secret]);
        }

        $this->provisioningUri = $twoFactor->provisioningUri($this->secret, $user->email);
    }

    public function confirm(TwoFactorService $twoFactor): void
    {
        $this->validate(['code' => ['required', 'regex:/^\d{6}$/']]);

        $user = Auth::user();
        abort_unless($user instanceof User && $user->hasAnyRole(['Admin', 'Super Admin']), 403);
        abort_if($user->two_factor_confirmed_at, 403);

        $key = 'auth.2fa.enrollment|'.$user->getKey();
        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $this->addError('code', __('auth.throttle', ['seconds' => RateLimiter::availableIn($key)]));

            return;
        }

        $secret = session('auth.2fa_enrollment_secret');
        abort_unless(is_string($secret) && $secret !== '', 403);

        $counter = $twoFactor->verifyNewCode($secret, $this->code, null);
        if ($counter === null) {
            RateLimiter::hit($key, self::DECAY_SECONDS);
            $this->addError('code', __('Kode verifikasi tidak valid.'));

            return;
        }

        $this->recoveryCodes = collect(range(1, 8))
            ->map(fn () => strtoupper(bin2hex(random_bytes(5))))
            ->all();

        DB::transaction(function () use ($user, $secret, $counter): void {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            abort_if($lockedUser->two_factor_confirmed_at, 403);
            $lockedUser->forceFill([
                'two_factor_secret' => Crypt::encryptString($secret),
                'two_factor_confirmed_at' => now(),
                'two_factor_last_counter' => $counter,
            ])->save();

            foreach ($this->recoveryCodes as $code) {
                $lockedUser->twoFactorRecoveryCodes()->create(['code_hash' => hash('sha256', $code)]);
            }
        });

        session()->forget('auth.2fa_enrollment_secret');
        RateLimiter::clear($key);
        session(['auth.2fa_verified' => true]);
        session()->regenerate();
        AuditService::log('auth.2fa_enrolled', User::class, $user->id);
        $this->secret = '';
        $this->provisioningUri = '';
    }

    public function render()
    {
        return view('modules.auth.two-factor')->layout('layouts.app');
    }
}
