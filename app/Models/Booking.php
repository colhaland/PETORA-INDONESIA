<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'booking_number', 'branch_id', 'customer_id', 'pet_id', 'resource_id', 'assigned_user_id',
        'type', 'scheduled_at', 'starts_at', 'ends_at', 'status', 'source', 'notes', 'cancelled_reason', 'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];
}
