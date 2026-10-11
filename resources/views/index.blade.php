@extends('admin.layout')
@section('title', 'Rooms')

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Rooms</h1>
        <p class="text-muted mb-0">Manage all cat hotel rooms</p>
    </div>
    <a href="{{ route('admin.rooms.create') }}" class="btn btn-primary">+ Add room</a>
</div>

<div class="card mb-4">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr><th>Room</th><th>Type</th><th>Price/night</th><th>Max cats</th><th>Status</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @forelse ($rooms as $room)
                    <tr>
                        <td class="fw-semibold">Room {{ $room->room_number }}</td>
                        <td>{{ $room->roomType?->label }}</td>
                        <td class="fw-bold text-primary">฿{{ number_format($room->roomType?->price_per_night ?? 0) }}</td>
                        <td>{{ $room->roomType?->capacity }}</td>
                        <td>@include('admin.partials.badge', ['type' => 'room', 'value' => $room->status])</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('admin.rooms.toggle', $room) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <a href="{{ route('admin.rooms.edit', $room) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                @if ($room->status === 'maintenance')
                                    <button class="btn btn-sm btn-outline-success">Enable</button>
                                @else
                                    <button class="btn btn-sm btn-outline-danger" @disabled($room->status === 'occupied')>Disable</button>
                                @endif
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No rooms yet. Add the first room.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<h2 class="h5 fw-bold mb-3">Room types</h2>
<div class="row g-3">
    @foreach ($types as $type)
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="h6 fw-bold mb-0">{{ $type->label }}</h3>
                        <span class="badge text-bg-light border">{{ $type->rooms_count }} rooms</span>
                    </div>
                    <form method="POST" action="{{ route('admin.room-types.update', $type) }}">
                        @csrf @method('PUT')
                        <div class="mb-2">
                            <label class="form-label small text-muted mb-1">Price per night (฿)</label>
                            <input type="number" step="0.01" min="0" name="price_per_night" value="{{ $type->price_per_night }}" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small text-muted mb-1">Max cats</label>
                            <input type="number" min="1" max="10" name="capacity" value="{{ $type->capacity }}" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted mb-1">Description</label>
                            <textarea name="description" rows="3" maxlength="255" class="form-control form-control-sm">{{ $type->description }}</textarea>
                        </div>
                        <button class="btn btn-sm btn-primary">Save changes</button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
