<?php
declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Pet extends Model
{
    use SoftDeletes;
    protected $fillable = ['pet_code', 'customer_id', 'name', 'species', 'breed', 'gender', 'birth_date', 'weight', 'color', 'sterilized', 'allergies', 'status'];
    protected $casts = ['allergies' => 'array', 'sterilized' => 'boolean'];
}
