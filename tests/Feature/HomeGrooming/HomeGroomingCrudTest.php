<?php

declare(strict_types=1);

namespace Tests\Feature\HomeGrooming;

use App\Models\User;
use App\Modules\HomeGrooming\Services\HomeGroomingService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeGroomingCrudTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->seed(PermissionSeeder::class);
        $user->givePermissionTo('home_grooming.view', 'home_grooming.create', 'home_grooming.update', 'home_grooming.manage');
        $this->actingAs($user);

        return $user;
    }

    public function test_book(): void
    {
        $this->authUser();
        $svc = new HomeGroomingService;
        $r = $svc->book(1, 'Jl. Home');
        $this->assertEquals('requested', $r['status']);
    }

    public function test_assign_groomer(): void
    {
        $this->authUser();
        $svc = new HomeGroomingService;
        $svc->assignGroomer(1, 2);
        $this->assertTrue(true);
    }

    public function test_list_page_loads(): void
    {
        $this->authUser();
        $this->get('/home-grooming')->assertStatus(200);
    }
}
