<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use BelongsToBranch;

    protected $fillable = ['branch_id', 'code', 'name', 'address', 'is_main', 'status'];
}
