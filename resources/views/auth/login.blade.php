@extends('layouts.guest')

@section('title', 'Login')

@section('content')
<div class="card shadow-sm">
    <div class="card-body p-4">
        <h4 class="mb-1 text-center fw-bold">JARA</h4>
        <p class="text-center text-muted small">Advanced To-Do List</p>

        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" name="remember" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">Ingat saya</label>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>

        <p class="text-muted small mt-3 mb-0">
            Hubungi Admin jika belum memiliki akun.
        </p>
    </div>
</div>
@endsection
