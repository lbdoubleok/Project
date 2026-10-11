@extends('admin.layout')
@section('title', $room->exists ? 'Edit room' : 'Add room')

@section('content')
<a href="{{ route('admin.rooms.index') }}" class="text-muted text-decoration-none">← Back to rooms</a>
<h1 class="h3 fw-bold mt-2 mb-4">{{ $room->exists ? 'Edit room ' . $room->room_number : 'Add room' }}</h1>

<div class="card" style="max-width:560px">
    <div class="card-body p-4">
        <form method="POST" action="{{ $room->exists ? route('admin.rooms.update', $room) : route('admin.rooms.store') }}">
            @csrf
            @if ($room->exists) @method('PUT') @endif

            <div class="mb-3">
                <label for="room_number" class="form-label">Room number</label>
                <input type="text" id="room_number" name="room_number" maxlength="10"
                       value="{{ old('room_number', $room->room_number) }}"
                       class="form-control @error('room_number') is-invalid @enderror" placeholder="e.g. 204" required>
                @error('room_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="room_type_id" class="form-label">Room type</label>
                <select id="room_type_id" name="room_type_id" class="form-select @error('room_type_id') is-invalid @enderror" required>
                    <option value="" disabled @selected(! old('room_type_id', $room->room_type_id))>Choose a type</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->room_type_id }}" @selected(old('room_type_id', $room->room_type_id) == $type->room_type_id)>
                            {{ $type->label }} — ฿{{ number_format($type->price_per_night) }}/night, max {{ $type->capacity }} cat(s)
                        </option>
                    @endforeach
                </select>
                @error('room_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-4">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                    @foreach (\App\Models\Room::STATUSES as $status)
                        <option value="{{ $status }}" @selected(old('status', $room->status) === $status)>
                            {{ $status === 'maintenance' ? 'Disabled (maintenance)' : ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('admin.rooms.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button class="btn btn-primary">{{ $room->exists ? 'Save changes' : 'Add room' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
