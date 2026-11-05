<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class BranchAuthorization
{
    public static function assertCanAccess(int $branchId): void
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        if ($user->hasRole('Super Admin') || $user->can('branches.view_all')) {
            abort_unless(Branch::query()->whereKey($branchId)->exists(), 404);

            return;
        }

        abort_unless(in_array($branchId, array_map('intval', $user->branchIds()), true), 403);
    }

    public static function currentId(): int
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        $allowedIds = array_map('intval', $user->branchIds());
        $activeId = session('active_branch_id');

        if ($activeId === null && ($user->hasRole('Super Admin') || $user->can('branches.view_all'))) {
            $activeId = Branch::query()->where('status', 'active')->orderBy('id')->value('id');
        }

        if ($activeId === null && count($allowedIds) === 1) {
            $activeId = $allowedIds[0];
        }

        abort_unless(is_numeric($activeId), 403, 'Pilih cabang yang diizinkan sebelum melanjutkan.');
        $branchId = (int) $activeId;
        self::assertCanAccess($branchId);

        return $branchId;
    }
}
