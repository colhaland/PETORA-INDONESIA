<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'movement_no', 'warehouse_id', 'product_id', 'batch_id', 'type', 'quantity', 'unit_cost',
        'reference_type', 'reference_id', 'notes', 'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
    ];
}
