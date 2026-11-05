<?php
declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Booking extends Model
{
    protected $fillable = ['booking_number', 'branch_id', 'customer_id', 'pet_id', 'type', 'scheduled_at', 'status', 'notes', 'created_by'];
}
