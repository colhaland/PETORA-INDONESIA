<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;

class MedicalVisit extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id', 'pet_id', 'doctor_id', 'status', 'subjective', 'objective', 'assessment', 'plan',
        'visible_to_owner', 'finalized_at',
    ];

    protected $casts = [
        'subjective' => 'encrypted',
        'objective' => 'encrypted',
        'assessment' => 'encrypted',
        'plan' => 'encrypted',
        'visible_to_owner' => 'boolean',
        'finalized_at' => 'datetime',
    ];
}
