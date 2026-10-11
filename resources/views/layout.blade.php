<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') · PawsInn</title>
    @include('admin.partials.theme')
</head>
<body>
@php
    $nav = [
        ['admin.dashboards',      '📊', 'Dashboard', 'admin.dashboards'],
        ['admin.rooms.index',    '🏠', 'Rooms',     'admin.rooms.*'],
        ['admin.bookings.index', '📋', 'Bookings',  'admin.bookings.*'],
        ['admin.customers.index','👥', 'Customers', 'admin.customers.*'],
        ['admin.cats.index',     '🐱', 'Cats',      'admin.cats.*'],
        ['admin.payments.index', '💳', 'Payments',  'admin.payments.*'],
        ['admin.cameras.index',  '📹', 'CCTV',      'admin.cameras.*'],
    ];
    $admin = auth('admin')->user();
@endphp

<div class="d-flex">
    <aside class="admin-sidebar bg-white border-end d-flex flex-column p-3">
        <a href="{{ route('admin.dashboards') }}" class="text-decoration-none mb-4">
            <div class="fw-bold fs-5 text-primary">🐾 PawsInn</div>
            <small class="text-muted">Admin Panel</small>
        </a>

        <nav class="nav nav-pills flex-column gap-1">
            @foreach ($nav as [$route, $icon, $label, $pattern])
                <a class="nav-link {{ request()->routeIs($pattern) ? 'active' : 'text-dark' }}" href="{{ route($route) }}">
                    {{ $icon }} {{ $label }}
                </a>
            @endforeach
        </nav>

        <div class="mt-auto border-top pt-3">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="rounded-circle bg-primary text-white d-inline-flex justify-content-center align-items-center fw-bold" style="width:36px;height:36px">
                    {{ strtoupper(substr($admin->ad_username ?? 'A', 0, 1)) }}
                </span>
                <div class="fw-semibold">{{ $admin->ad_username ?? 'Admin' }}</div>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="btn btn-sm btn-outline-secondary w-100">Sign out</button>
            </form>
        </div>
    </aside>

    <main class="flex-grow-1 p-4" style="min-width:0">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>