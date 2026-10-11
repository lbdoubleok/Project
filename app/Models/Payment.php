<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    public const STATUSES = ['pending', 'verified', 'rejected'];

    protected $table      = 'payments';
    protected $primaryKey = 'payment_id';

    protected $fillable = [
        'booking_id', 'slip_image_url', 'payment_date', 'status',
        'verified_by', 'verified_at', 'reject_reason',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'verified_at'  => 'datetime',
        ];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }

    public function verifier()
    {
        return $this->belongsTo(Admin::class, 'verified_by', 'admin_id');
    }
}