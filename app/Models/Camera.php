<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Camera extends Model
{
    protected $table      = 'cameras';
    protected $primaryKey = 'camera_id';

    protected $fillable = ['room_id', 'camera_code'];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }
}