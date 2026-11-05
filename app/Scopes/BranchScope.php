<?php

declare(strict_types=1);

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BranchScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        if (method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo('branches.view_all')) {
            return;
        }

        if (method_exists($user, 'branchIds')) {
            $branchIds = array_filter((array) $user->branchIds());
            if ($branchIds !== []) {
                $builder->whereIn($model->getTable().'.branch_id', $branchIds);
            }
        }
    }
}
