<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;

class GroomingSession extends Model
{
    use BelongsToBranch;

    protected $fillable = ['branch_id', 'pet_id', 'grooming_service_id', 'groomer_id', 'status', 'started_at', 'completed_at'];
}
