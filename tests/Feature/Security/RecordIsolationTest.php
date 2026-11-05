<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\Customer;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecordIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_customer_detail_returns_404_for_authorized_user(): void
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo('customers.view');
        $this->actingAs($user)->get('/customers/999999')->assertNotFound();
    }
}
