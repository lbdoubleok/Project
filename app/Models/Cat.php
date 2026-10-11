<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cat extends Model
{
    use SoftDeletes;

    protected $table      = 'cats';
    protected $primaryKey = 'cat_id';

    protected $fillable = ['user_id', 'name', 'breed', 'sex', 'age', 'weight', 'photo_url', 'note'];

    protected function casts(): array
    {
        return [
            'age'    => 'integer',
            'weight' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'user_id');
    }

    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_cats', 'cat_id', 'booking_id')
            ->withPivot('created_at');
    }
}