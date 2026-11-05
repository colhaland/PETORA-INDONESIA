<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;

class CashShift extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id', 'user_id', 'opened_by', 'closed_by',
        'opening_amount', 'closing_amount', 'status', 'opened_at', 'closed_at',
    ];

    protected $casts = ['opened_at' => 'datetime', 'closed_at' => 'datetime'];
}
