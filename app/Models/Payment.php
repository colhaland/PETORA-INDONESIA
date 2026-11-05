<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['order_id', 'method', 'amount'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
