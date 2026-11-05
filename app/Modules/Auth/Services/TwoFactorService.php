<?php
declare(strict_types=1);
namespace App\Modules\Auth\Services;
use PragmaRX\Google2FA\Google2FA;
class TwoFactorService
{
    protected Google2FA $google2fa;
    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }
    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }
    public function verify(string $secret, string $code): bool
    {
        return $this->google2fa->verifyKey($secret, $code);
    }
}
