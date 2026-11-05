<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_loads(): void
    {
        $user = User::factory()->create();
        $this->seed(PermissionSeeder::class);
        $user->givePermissionTo('settings.view');
        $this->actingAs($user)->get('/settings')->assertStatus(200);
    }
}
