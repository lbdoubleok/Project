<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    public const LABELS = [
        'standard' => 'Standard',
        'deluxe'   => 'Deluxe',
        'vip'      => 'VIP',
    ];

    protected $table      = 'room_types';
    protected $primaryKey = 'room_type_id';

    protected $fillable = ['type_name', 'capacity', 'price_per_night', 'description'];

    protected function casts(): array
    {
        return [
            'capacity'        => 'integer',
            'price_per_night' => 'decimal:2',
        ];
    }

    protected function label(): Attribute
    {
        return Attribute::get(fn () => self::LABELS[$this->type_name] ?? ucfirst((string) $this->type_name));
    }

    public function rooms()
    {
        return $this->hasMany(Room::class, 'room_type_id', 'room_type_id');
    }
}