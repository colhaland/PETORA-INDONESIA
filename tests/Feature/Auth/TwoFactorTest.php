<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Modules\Auth\Livewire\TwoFactorChallenge;
use App\Modules\Auth\Livewire\TwoFactorSetup;
use App\Modules\Auth\Services\TwoFactorService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_secret(): void
    {
        $svc = new TwoFactorService;
        $secret = $svc->generateSecret();
        $this->assertNotEmpty($secret);
    }

    public function test_admin_can_enroll_and_recovery_codes_are_hashed_and_single_use(): void
    {
        $this->withoutVite();
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $this->actingAs($admin);

        $component = Livewire::test(TwoFactorSetup::class);
        $secret = $component->get('secret');
        $code = (new Google2FA)->getCurrentOtp($secret);

        $component->set('code', $code)->call('confirm')->assertHasNoErrors();

        $admin->refresh();
        $this->assertNotNull($admin->two_factor_confirmed_at);
        $this->assertSame($secret, app(TwoFactorService::class)->decryptSecret($admin->two_factor_secret));
        $this->assertCount(8, $admin->twoFactorRecoveryCodes);
        $codes = $component->get('recoveryCodes');
        $storedHashes = $admin->twoFactorRecoveryCodes->pluck('code_hash')->all();
        $this->assertEqualsCanonicalizing(array_map('hash', array_fill(0, count($codes), 'sha256'), $codes), $storedHashes);
        $this->assertNotContains($codes[0], $storedHashes);
        $this->assertNull(app(TwoFactorService::class)->verifyNewCode($secret, $code, $admin->two_factor_last_counter));

        auth()->logout();
        $this->withSession(['auth.2fa_pending_user_id' => $admin->id]);
        Livewire::test(TwoFactorChallenge::class)
            ->set('code', $codes[0])
            ->call('verify')
            ->assertRedirect(route('dashboard'));
        $this->assertNotNull($admin->twoFactorRecoveryCodes()->where('code_hash', hash('sha256', $codes[0]))->first()->used_at);

        auth()->logout();
        $this->withSession(['auth.2fa_pending_user_id' => $admin->id]);
        Livewire::test(TwoFactorChallenge::class)
            ->set('code', $codes[0])
            ->call('verify')
            ->assertHasErrors('code');
    }

    public function test_admin_is_redirected_to_two_factor_enrollment_until_confirmed(): void
    {
        $this->withoutVite();
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $this->actingAs($admin)->get('/')->assertRedirect(route('two-factor.setup'));
        $this->actingAs($admin)->get(route('two-factor.setup'))->assertOk();
    }

    public function test_two_factor_challenge_locks_after_five_failed_codes(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole('Admin');
        $secret = app(TwoFactorService::class)->generateSecret();
        $user->forceFill([
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_confirmed_at' => now(),
        ])->save();
        $this->withSession(['auth.2fa_pending_user_id' => $user->id]);
        $invalidCode = '000000';
        while (app(TwoFactorService::class)->verifyNewCode($secret, $invalidCode, null) !== null) {
            $invalidCode = (string) random_int(0, 999999);
            $invalidCode = str_pad($invalidCode, 6, '0', STR_PAD_LEFT);
        }

        $component = Livewire::test(TwoFactorChallenge::class);
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $component->set('code', $invalidCode)->call('verify')->assertHasErrors('code');
        }

        $component->set('code', $invalidCode)->call('verify')->assertHasErrors('code');
        $this->assertGuest();
    }
}
