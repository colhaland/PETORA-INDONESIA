<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use BelongsToBranch;

    protected $fillable = ['branch_id', 'category', 'amount', 'description', 'status', 'created_by'];
}
