<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'sku', 'barcode', 'category', 'brand', 'description', 'unit', 'cost_price', 'sell_price', 'min_price', 'track_batch', 'track_expiry', 'status'];

    protected $casts = ['track_batch' => 'boolean', 'track_expiry' => 'boolean'];
}
