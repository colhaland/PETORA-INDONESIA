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
        if (auth()->check() && ! auth()->user()->hasPermissionTo('branches.view_all')) {
            $branchIds = auth()->user()->branchIds();
            $builder->whereIn($model->getTable().'.branch_id', $branchIds);
        }
    }
}
