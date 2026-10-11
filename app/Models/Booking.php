<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Booking extends Model
{
    use SoftDeletes;

    public const STATUS_LABELS = [
        'pending'    => 'รอยืนยัน',
        'confirmed'  => 'ยืนยันแล้ว',
        'checked_in' => 'เช็คอินแล้ว',
        'completed'  => 'เสร็จสิ้น',
        'cancelled'  => 'ยกเลิก',
    ];

    protected $table      = 'bookings';
    protected $primaryKey = 'booking_id';

    protected $fillable = [
        'user_id', 'room_id', 'check_in', 'check_out',
        'total_price', 'status', 'cancel_reason',
    ];

    protected function casts(): array
    {
        return [
            'check_in'    => 'date',
            'check_out'   => 'date',
            'total_price' => 'decimal:2',
        ];
    }

    protected function bookingCode(): Attribute
    {
        return Attribute::get(function () {
            $year = ($this->created_at ?? now())->format('Y');
            return 'BK-' . $year . str_pad((string) $this->booking_id, 4, '0', STR_PAD_LEFT);
        });
    }

    protected function nights(): Attribute
    {
        return Attribute::get(fn () => $this->check_in && $this->check_out
            ? $this->check_in->diffInDays($this->check_out)
            : 0);
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn () => self::STATUS_LABELS[$this->status] ?? $this->status);
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'user_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }

    public function cats()
    {
        return $this->belongsToMany(Cat::class, 'booking_cats', 'booking_id', 'cat_id')
            ->withPivot('created_at');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'booking_id', 'booking_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'booking_id', 'booking_id')->latestOfMany('payment_id');
    }

    public function changeStatus(string $status, ?string $cancelReason = null): void
    {
        DB::transaction(function () use ($status, $cancelReason) {
            $attrs = ['status' => $status];

            if ($status === 'cancelled') {
                $attrs['cancel_reason'] = $cancelReason;
            }

            $this->update($attrs);

            $room = $this->room;
            if (! $room) {
                return;
            }

            if ($status === 'checked_in') {
                if ($room->status !== 'maintenance') {
                    $room->update(['status' => 'occupied']);
                }
            } elseif (in_array($status, ['completed', 'cancelled'], true) && $room->status === 'occupied') {
                $stillOccupied = $room->bookings()
                    ->where('status', 'checked_in')
                    ->where('booking_id', '!=', $this->booking_id)
                    ->exists();

                if (! $stillOccupied) {
                    $room->update(['status' => 'available']);
                }
            }
        });
    }
}