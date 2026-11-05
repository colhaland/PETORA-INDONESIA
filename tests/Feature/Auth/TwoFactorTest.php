<?php
declare(strict_types=1);
namespace Tests\Feature\Auth;
use App\Modules\Auth\Services\TwoFactorService;
use Tests\TestCase;
class TwoFactorTest extends TestCase
{
    public function test_generates_secret(): void
    {
        $svc = new TwoFactorService();
        $secret = $svc->generateSecret();
        $this->assertNotEmpty($secret);
    }
}
