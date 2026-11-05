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
        if ($branchId === 1) {
            $branch = Branch::query()->where('code', 'PST')->first();

            if ($branch === null) {
                $this->seed(BranchSeeder::class);
                $branch = Branch::query()->where('code', 'PST')->firstOrFail();
            }

            $branchId = (int) $branch->getKey();
        }
        $user = User::factory()->create();
        $user->givePermissionTo($permissions);
        $user->branches()->sync([$branchId]);
        $this->actingAs($user);

        return $user;
    }
}
