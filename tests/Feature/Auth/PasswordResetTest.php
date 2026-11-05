<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_reset_changes_the_password_without_disabling_two_factor(): void
    {
        $user = User::factory()->create(['email' => 'staff@example.test']);
        $user->forceFill([
            'two_factor_secret' => encrypt('existing-encrypted-secret'),
            'two_factor_confirmed_at' => now(),
            'two_factor_last_counter' => 123,
        ])->save();
        $token = Password::createToken($user);

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))->assertOk();
        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewSecurePassword123',
            'password_confirmation' => 'NewSecurePassword123',
        ])->assertRedirect(route('login'));

        $user->refresh();
        $this->assertTrue(Hash::check('NewSecurePassword123', $user->password));
        $this->assertNotNull($user->two_factor_confirmed_at);
        $this->assertSame(123, $user->two_factor_last_counter);
    }

    public function test_password_reset_rejects_weak_passwords(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->from(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->post(route('password.update'), [
                'token' => $token,
                'email' => $user->email,
                'password' => 'weak',
                'password_confirmation' => 'weak',
            ])->assertSessionHasErrors('password');
    }
}
