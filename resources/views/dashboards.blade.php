@extends('admin.layout')
@section('title', 'Dashboard')

@section('content')
<h1 class="h3 fw-bold mb-1">Dashboard</h1>
<p class="text-muted mb-4">Overview of PawsInn Cat Hotel operations</p>

<div class="row row-cols-1 row-cols-md-3 row-cols-xl-5 g-3 mb-4">
    @foreach ($stats as [$label, $value, $icon])
        <div class="col">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">{{ $label }}</div>
                        <div class="fs-3 fw-bold">{{ $value }}</div>
                    </div>
                    <div class="fs-2">{{ $icon }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h2 class="h5 fw-bold mb-0">Recent bookings</h2>
        <a href="{{ route('admin.bookings.index') }}">View all</a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Booking #</th><th>Customer</th><th>Cat</th><th>Room</th>
                    <th>Check-in</th><th>Check-out</th><th>Total</th><th>Status</th><th>Payment</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recent as $b)
                    <tr>
                        <td><a href="{{ route('admin.bookings.show', $b) }}" class="font-monospace small">{{ $b->booking_code }}</a></td>
                        <td>{{ $b->user?->full_name }}</td>
                        <td>{{ $b->cats->pluck('name')->join(', ') }}</td>
                        <td>Room {{ $b->room?->room_number }} <span class="text-muted small">({{ $b->room?->roomType?->label }})</span></td>
                        <td>{{ $b->check_in->format('Y-m-d') }}</td>
                        <td>{{ $b->check_out->format('Y-m-d') }}</td>
                        <td class="fw-bold">฿{{ number_format($b->total_price) }}</td>
                        <td>@include('admin.partials.badge', ['type' => 'booking', 'value' => $b->status])</td>
                        <td>@include('admin.partials.badge', ['type' => 'payment', 'value' => $b->payment?->status])</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No bookings yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection