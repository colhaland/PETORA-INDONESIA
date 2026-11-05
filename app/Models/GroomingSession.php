<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroomingSession extends Model
{
    protected $fillable = ['branch_id', 'pet_id', 'grooming_service_id', 'groomer_id', 'status', 'started_at', 'completed_at'];
}
