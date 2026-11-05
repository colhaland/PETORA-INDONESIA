<?php

declare(strict_types=1);

namespace Tests\Feature\Customers;

use App\Models\User;
use App\Modules\Customers\Services\CustomerService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_customer(): void
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo('customers.create');
        $this->actingAs($user);
        $svc = new CustomerService;
        $c = $svc->create(['name' => 'Budi']);
        $this->assertNotNull($c->customer_number);
    }
}
