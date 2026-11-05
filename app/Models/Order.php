<?php
declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Order extends Model
{
    public function items() { return $this->hasMany(OrderItem::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    protected $fillable = ['order_number', 'branch_id', 'customer_id', 'created_by', 'status', 'subtotal', 'discount', 'tax', 'total', 'idempotency_key'];
}
