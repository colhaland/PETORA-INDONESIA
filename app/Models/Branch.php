<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'name', 'address', 'phone', 'email', 'status', 'main_warehouse_id'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'branch_user');
    }
}
