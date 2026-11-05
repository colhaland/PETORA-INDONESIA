<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroomingService extends Model
{
    protected $fillable = ['name', 'status'];
}
