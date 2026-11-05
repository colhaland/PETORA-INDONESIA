<?php

namespace Tests;

use App\Models\Branch;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    protected function actingAsWithPermissions(array $permissions, ?int $branchId = null): User
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo($permissions);

        if ($branchId !== null) {
            $branchId = $branchId === 1 ? $this->primaryBranchId() : $branchId;
            $user->branches()->sync([$branchId]);
        }

        $this->actingAs($user);

        return $user;
    }

    protected function primaryBranchId(): int
    {
        $branch = Branch::query()->where('code', 'PST')->first();

        if ($branch === null) {
            $this->seed(BranchSeeder::class);
            $branch = Branch::query()->where('code', 'PST')->firstOrFail();
        }

        return (int) $branch->getKey();
    }
}
