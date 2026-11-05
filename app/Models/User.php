<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'phone', 'password', 'status', 'two_factor_secret', 'two_factor_confirmed_at', 'locked_until', 'last_login_at'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret'])] class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    use HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function branches()
    {
        if (Schema::hasTable('user_branch_access')) {
            return $this->belongsToMany(Branch::class, 'user_branch_access')->withTimestamps();
        }

        return $this->belongsToMany(Branch::class, 'branch_user');
    }

    public function branchIds(): array
    {
        try {
            return $this->branches()->pluck('branches.id')->all();
        } catch (\Throwable) {
            return [];
        }
    }
}
