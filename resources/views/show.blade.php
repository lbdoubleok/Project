@extends('admin.layout')
@section('title', $booking->booking_code)

@section('content')
@php
    $payment = $booking->payments->first();
    $next = ['pending' => 'confirmed', 'confirmed' => 'checked_in', 'checked_in' => 'completed'][$booking->status] ?? null;
    $canCancel = ! in_array($booking->status, ['cancelled', 'completed'], true);
    $slip = $payment?->slip_image_url;
    $slipUrl = $slip ? (\Illuminate\Support\Str::startsWith($slip, ['http://', 'https://', '/']) ? $slip : asset('storage/' . $slip)) : null;
@endphp

<a href="{{ route('admin.bookings.index') }}" class="text-muted text-decoration-none">← Back to bookings</a>

<div class="d-flex justify-content-between align-items-start mt-2 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-0">Booking detail</h1>
        <div class="font-monospace text-muted">{{ $booking->booking_code }}</div>
    </div>
    <div class="fs-6">@include('admin.partials.badge', ['type' => 'booking', 'value' => $booking->status])</div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-body">
                <h2 class="h5 fw-bold mb-3">Booking information</h2>
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted fw-normal">Booking number</dt><dd class="col-sm-8 font-monospace">{{ $booking->booking_code }}</dd>
                    <dt class="col-sm-4 text-muted fw-normal">Customer</dt>
                    <dd class="col-sm-8">
                        @if ($booking->user)
                            <a href="{{ route('admin.customers.show', $booking->user) }}">{{ $booking->user->full_name }}</a>
                        @else — @endif
                    </dd>
                    <dt class="col-sm-4 text-muted fw-normal">Cat</dt><dd class="col-sm-8">{{ $booking->cats->pluck('name')->join(', ') ?: '—' }}</dd>
                    <dt class="col-sm-4 text-muted fw-normal">Room</dt><dd class="col-sm-8">Room {{ $booking->room?->room_number }} ({{ $booking->room?->roomType?->label }})</dd>
                    <dt class="col-sm-4 text-muted fw-normal">Check-in</dt><dd class="col-sm-8">{{ $booking->check_in->format('Y-m-d') }}</dd>
                    <dt class="col-sm-4 text-muted fw-normal">Check-out</dt><dd class="col-sm-8">{{ $booking->check_out->format('Y-m-d') }}</dd>
                    <dt class="col-sm-4 text-muted fw-normal">Nights</dt><dd class="col-sm-8">{{ $booking->nights }}</dd>
                    <dt class="col-sm-4 text-muted fw-normal">Price/night</dt><dd class="col-sm-8">฿{{ number_format($booking->room?->roomType?->price_per_night ?? 0) }}</dd>
                    <dt class="col-sm-4 text-muted fw-normal">Booked on</dt><dd class="col-sm-8">{{ $booking->created_at?->format('Y-m-d') }}</dd>
                    @if ($booking->status === 'cancelled' && $booking->cancel_reason)
                        <dt class="col-sm-4 text-muted fw-normal">Cancel reason</dt><dd class="col-sm-8">{{ $booking->cancel_reason }}</dd>
                    @endif
                </dl>
                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Total</span>
                    <span class="fs-4 fw-bold text-primary">฿{{ number_format($booking->total_price) }}</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h2 class="h5 fw-bold mb-3">Payment review</h2>
                @if ($payment)
                    <div class="d-flex justify-content-between align-items-center bg-light rounded p-3 mb-3">
                        <div>
                            <div class="fw-semibold">Bank transfer</div>
                            <div class="small text-muted">
                                @if ($payment->payment_date) Paid on {{ $payment->payment_date->format('Y-m-d') }} @else No payment date @endif
                            </div>
                        </div>
                        @include('admin.partials.badge', ['type' => 'payment', 'value' => $payment->status])
                    </div>

                    @if ($payment->status === 'rejected' && $payment->reject_reason)
                        <div class="alert alert-danger py-2">Rejected: {{ $payment->reject_reason }}</div>
                    @endif

                    @if ($slipUrl)
                        <a href="{{ $slipUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary mb-3">View slip</a>
                    @endif

                    @if ($payment->status === 'pending')
                        <div class="d-flex gap-2">
                            <form method="POST" action="{{ route('admin.payments.verify', $payment) }}">
                                @csrf
                                <button class="btn btn-success btn-sm">Verify payment</button>
                            </form>
                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">Reject slip</button>
                        </div>
                    @endif
                @else
                    <p class="text-muted mb-0">The customer hasn't uploaded a payment slip yet.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-body">
                <h2 class="h5 fw-bold mb-3">Update status</h2>
                <form method="POST" action="{{ route('admin.bookings.status', $booking) }}">
                    @csrf @method('PATCH')
                    <div class="d-grid gap-2 mb-3">
                        @foreach ($statuses as $key => $label)
                            <input type="radio" class="btn-check" name="status" id="st-{{ $key }}" value="{{ $key }}" @checked($booking->status === $key)>
                            <label class="btn btn-outline-primary text-start" for="st-{{ $key }}">{{ $label }}</label>
                        @endforeach
                    </div>
                    <button class="btn btn-primary w-100">Update status</button>
                </form>
            </div>
        </div>

        @if ($next || $canCancel)
            <div class="card">
                <div class="card-body">
                    <h2 class="h5 fw-bold mb-3">Quick actions</h2>
                    <div class="d-grid gap-2">
                        @if ($next)
                            <form method="POST" action="{{ route('admin.bookings.status', $booking) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="{{ $next }}">
                                <button class="btn btn-primary w-100">Mark as {{ $statuses[$next] }}</button>
                            </form>
                        @endif
                        @if ($canCancel)
                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">Cancel booking</button>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@if ($canCancel)
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.bookings.cancel', $booking) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h2 class="modal-title h5">Cancel booking {{ $booking->booking_code }}</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label for="cancel_reason" class="form-label">Reason (optional)</label>
                <textarea id="cancel_reason" name="cancel_reason" rows="3" maxlength="255" class="form-control"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Keep booking</button>
                <button class="btn btn-danger">Cancel booking</button>
            </div>
        </form>
    </div>
</div>
@endif

@if ($payment && $payment->status === 'pending')
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.payments.reject', $payment) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h2 class="modal-title h5">Reject payment slip</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label for="reject_reason" class="form-label">Reason</label>
                <textarea id="reject_reason" name="reject_reason" rows="3" maxlength="255" class="form-control" required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-danger">Reject slip</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
