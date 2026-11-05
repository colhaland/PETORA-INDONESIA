<?php
declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Medicine extends Model
{
    protected $fillable = ['name', 'category', 'status'];
}
