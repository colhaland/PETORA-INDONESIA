<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA;
    }

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function decryptSecret(string $encryptedSecret): string
    {
        return Crypt::decryptString($encryptedSecret);
    }

    public function verifyNewCode(string $secret, string $code, ?int $lastCounter): ?int
    {
        $counter = $this->google2fa->verifyKeyNewer($secret, $code, $lastCounter ?? 0, 1);

        return is_int($counter) ? $counter : null;
    }

    public function provisioningUri(string $secret, string $account): string
    {
        return $this->google2fa->getQRCodeUrl(config('app.name'), $account, $secret);
    }
}
