<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = ['po_number', 'branch_id', 'supplier_id', 'status', 'total', 'created_by'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
