<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'description', 'quantity', 'unit_price', 'discount', 'total',
        'inventory_managed', 'track_expiry',
    ];

    protected $casts = ['inventory_managed' => 'boolean', 'track_expiry' => 'boolean'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
