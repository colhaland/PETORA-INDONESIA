<?php

declare(strict_types=1);

namespace App\Traits;

use App\Scopes\BranchScope;

trait BelongsToBranch
{
    public static function bootBelongsToBranch(): void
    {
        static::addGlobalScope(new BranchScope());
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }
}
