<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use BelongsToBranch;

    protected $fillable = ['po_number', 'branch_id', 'supplier_id', 'status', 'total', 'created_by'];
}
