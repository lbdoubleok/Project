<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Users extends Model
{
    use SoftDeletes;

    protected $table      = 'users';
    protected $primaryKey = 'user_id';

    protected $fillable = ['username', 'password', 'full_name', 'phone', 'email'];
    protected $hidden   = ['password'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function cats()
    {
        return $this->hasMany(Cat::class, 'user_id', 'user_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'user_id', 'user_id');
    }
}