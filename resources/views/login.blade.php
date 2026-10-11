<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login · PawsInn</title>
    @include('admin.partials.theme')
</head>
<body>
<nav class="navbar bg-white border-bottom">
    <div class="container-fluid">
        <span class="navbar-brand fw-bold text-primary">🐾 PawsInn <small class="text-muted fw-normal">Admin</small></span>
    </div>
</nav>

<div class="container d-flex flex-column align-items-center justify-content-center" style="min-height:80vh">
    <div class="text-center mb-4">
        <div class="display-4">🔐</div>
        <h1 class="h3 fw-bold mt-2">Admin login</h1>
        <p class="text-muted mb-0">Sign in to the admin panel</p>
    </div>

    <div class="card shadow-sm w-100" style="max-width:420px">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.login.attempt') }}">
                @csrf

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" value="{{ old('username') }}"
                           class="form-control @error('username') is-invalid @enderror" placeholder="Admin username" autofocus required>
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password"
                           class="form-control @error('password') is-invalid @enderror" placeholder="Admin password" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button class="btn btn-primary w-100">Sign in to admin panel</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
