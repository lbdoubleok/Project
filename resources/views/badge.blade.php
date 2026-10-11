@php
    $colors = [
        'booking' => ['pending' => 'warning', 'confirmed' => 'success', 'checked_in' => 'primary', 'completed' => 'secondary', 'cancelled' => 'danger'],
        'payment' => ['pending' => 'warning', 'verified' => 'success', 'rejected' => 'danger'],
        'room'    => ['available' => 'success', 'occupied' => 'primary', 'maintenance' => 'secondary'],
    ];
    $labels = [
        'booking' => \App\Models\Booking::STATUS_LABELS,
        'payment' => ['pending' => 'Pending', 'verified' => 'Verified', 'rejected' => 'Rejected'],
        'room'    => ['available' => 'Available', 'occupied' => 'Occupied', 'maintenance' => 'Disabled'],
    ];
@endphp
@if ($value)
    <span class="badge text-bg-{{ $colors[$type][$value] ?? 'secondary' }}">{{ $labels[$type][$value] ?? $value }}</span>
@else
    <span class="badge text-bg-light border text-muted">None</span>
@endif
