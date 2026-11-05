<?php
declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CashShift extends Model
{
    protected $fillable = ['branch_id', 'user_id', 'status', 'opening_amount', 'closing_amount', 'opened_at', 'closed_at'];
}
