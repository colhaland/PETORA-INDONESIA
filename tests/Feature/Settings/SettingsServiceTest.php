<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Models\User;
use App\Support\SettingsService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class SettingsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_set_and_get(): void
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo('settings.manage');
        $this->actingAs($user);

        SettingsService::set('tax.rate', '11', $user->id);
        $this->assertSame('11', SettingsService::get('tax.rate'));
    }

    public function test_settings_cannot_be_changed_with_view_permission_only(): void
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo('settings.view');
        $this->actingAs($user);

        $this->expectException(AuthorizationException::class);
        SettingsService::set('tax.rate', '12', $user->id);
    }

    public function test_unknown_setting_keys_are_rejected(): void
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo('settings.manage');
        $this->actingAs($user);

        $this->expectException(HttpException::class);
        SettingsService::set('unapproved.key', 'value', $user->id);
    }
}
