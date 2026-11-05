<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class RecordFinder
{
    /**
     * Find a record or throw 404. Use for branch/customer-scoped resources to avoid enumeration (MTS 11.2).
     * When a BranchScope/global scope applies, a missing/forbidden record becomes 404.
     */
    public static function findOrFail(Builder $query, int|string $id): Model
    {
        $model = $query->find($id);
        if (! $model) {
            throw (new ModelNotFoundException)->setModel($query->getModel()::class, [$id]);
        }

        return $model;
    }

    public static function forClass(string $modelClass, int|string $id): Model
    {
        /** @var Builder $query */
        $query = $modelClass::query();

        return self::findOrFail($query, $id);
    }
}
