<?php

declare(strict_types=1);

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Schema;

class BranchScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        // Super Admin bypass handled via Gate::before in normal permission checks;
        // treat explicit all-branch permission as bypass for data scope.
        try {
            if (method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo('branches.view_all')) {
                return;
            }
            if (method_exists($user, 'hasRole') && $user->hasRole('Super Admin')) {
                return;
            }
        } catch (\Throwable) {
            // fail safe: do not over-expose by applying unknown bypass; continue to branch filter
        }

        if (! method_exists($user, 'branchIds')) {
            return;
        }

        $table = $model->getTable();
        try {
            if (! Schema::hasColumn($table, 'branch_id')) {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        $branchIds = array_values(array_filter((array) $user->branchIds(), static fn ($id) => $id !== null && $id !== ''));
        if ($branchIds === []) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $builder->whereIn($table.'.branch_id', $branchIds);
    }
}
