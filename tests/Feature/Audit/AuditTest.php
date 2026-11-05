<?php

declare(strict_types=1);

namespace Tests\Feature\Audit;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_loads(): void
    {
        $user = User::factory()->create();
        $this->seed(PermissionSeeder::class);
        $user->givePermissionTo('audit.view');
        $this->actingAs($user)->get('/audit')->assertStatus(200);
    }
}
