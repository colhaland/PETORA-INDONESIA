<?php

namespace Tests;

use App\Models\Branch;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function actingAsWithPermissions(array $permissions, ?int $branchId = null): User
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo($permissions);

        if ($branchId !== null) {
            if (Branch::query()->whereKey($branchId)->doesntExist()) {
                $this->seed(BranchSeeder::class);
            }
            $user->branches()->sync([$branchId]);
        }

        $this->actingAs($user);

        return $user;
    }
}
