<?php

declare(strict_types=1);

namespace App\Modules\Auth\Livewire;

use App\Models\User;
use App\Modules\Auth\Services\TwoFactorService;
use App\Support\AuditService;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class TwoFactorChallenge extends Component
{
    public string $code = '';

    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 900;

    public function mount(): void
    {
        if (! session()->has('auth.2fa_pending_user_id')) {
            $this->redirect(route('login'), navigate: false);
        }
    }

    public function verify(TwoFactorService $twoFactor): void
    {
        $this->validate(['code' => ['required', 'string', 'regex:/^(?:\d{6}|[A-Fa-f0-9]{10})$/']]);
        $id = session('auth.2fa_pending_user_id');
        $user = $id ? User::query()->find($id) : null;
        if (! $user || empty($user->two_factor_secret) || ! $user->two_factor_confirmed_at) {
            session()->forget('auth.2fa_pending_user_id');
            $this->redirect(route('login'), navigate: false);

            return;
        }

        $key = $this->rateLimitKey($user);
        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $this->addError('code', __('auth.throttle', ['seconds' => RateLimiter::availableIn($key)]));

            return;
        }

        $success = DB::transaction(function () use ($user, $twoFactor): bool {
            $lockedUser = User::query()->lockForUpdate()->find($user->id);
            if (! $lockedUser || ! $lockedUser->two_factor_secret || ! $lockedUser->two_factor_confirmed_at) {
                return false;
            }

            try {
                $secret = $twoFactor->decryptSecret($lockedUser->two_factor_secret);
            } catch (DecryptException) {
                $lockedUser->forceFill([
                    'two_factor_secret' => null,
                    'two_factor_confirmed_at' => null,
                    'two_factor_last_counter' => null,
                ])->save();
                $lockedUser->twoFactorRecoveryCodes()->delete();

                return false;
            }

            $normalizedCode = strtoupper(trim($this->code));
            if (preg_match('/^\d{6}$/', $normalizedCode) === 1) {
                $counter = $twoFactor->verifyNewCode($secret, $normalizedCode, $lockedUser->two_factor_last_counter);
                if ($counter === null) {
                    return false;
                }

                $lockedUser->forceFill([
                    'two_factor_last_counter' => $counter,
                    'last_login_at' => now(),
                ])->save();

                return true;
            }

            $codeHash = hash('sha256', $normalizedCode);
            $recoveryCode = $lockedUser->twoFactorRecoveryCodes()
                ->where('code_hash', $codeHash)
                ->whereNull('used_at')
                ->lockForUpdate()
                ->first();

            if (! $recoveryCode) {
                return false;
            }

            $recoveryCode->forceFill(['used_at' => now()])->save();
            $lockedUser->forceFill(['last_login_at' => now()])->save();

            return true;
        });

        if (! $success) {
            RateLimiter::hit($key, self::DECAY_SECONDS);
            AuditService::log('auth.2fa_failed', User::class, $user->id);
            $this->addError('code', __('Kode verifikasi tidak valid.'));

            return;
        }

        RateLimiter::clear($key);
        session()->forget(['auth.2fa_pending_user_id', 'auth.2fa_enrollment_secret']);
        session(['auth.2fa_verified' => true]);
        Auth::login($user);
        session()->regenerate();
        AuditService::log('auth.2fa_passed', User::class, $user->id);
        $this->redirect(route('dashboard'), navigate: false);
    }

    private function rateLimitKey(User $user): string
    {
        return 'auth.2fa.user|'.$user->getKey();
    }

    public function render()
    {
        return view('modules.auth.two-factor-challenge');
    }
}
