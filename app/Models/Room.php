<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use SoftDeletes;

    public const STATUSES = ['available', 'occupied', 'maintenance'];

    protected $table      = 'rooms';
    protected $primaryKey = 'room_id';

    protected $fillable = ['room_number', 'room_type_id', 'status'];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'room_type_id', 'room_type_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'room_id', 'room_id');
    }

    public function cameras()
    {
        return $this->hasMany(Camera::class, 'room_id', 'room_id');
    }
}