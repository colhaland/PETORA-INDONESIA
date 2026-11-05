<?php
declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StockMovement extends Model
{
    protected $fillable = ['warehouse_id', 'product_id', 'type', 'quantity', 'reference_type', 'reference_id', 'notes', 'created_by'];
}
