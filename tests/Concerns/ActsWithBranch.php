<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Models\Branch;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\PermissionSeeder;

trait ActsWithBranch
{
    protected function userWithBranch(array $permissions, int $branchId = 1): User
    {
        $this->seed(PermissionSeeder::class);
        if (Branch::query()->whereKey($branchId)->doesntExist()) {
            $this->seed(BranchSeeder::class);
        }
        $user = User::factory()->create();
        $user->givePermissionTo($permissions);
        $user->branches()->sync([$branchId]);
        $this->actingAs($user);

        return $user;
    }
}
