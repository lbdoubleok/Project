<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use SoftDeletes;

    protected $table      = 'admins';
    protected $primaryKey = 'admin_id';

    protected $fillable = ['ad_username', 'ad_password'];
    protected $hidden   = ['ad_password'];

    protected function casts(): array
    {
        return [
            'ad_password' => 'hashed',
        ];
    }

    public function getAuthPassword(): string
    {
        return $this->ad_password;
    }

    public function getRememberTokenName(): string
    {
        return '';
    }

    public function verifiedPayments()
    {
        return $this->hasMany(Payment::class, 'verified_by', 'admin_id');
    }
}