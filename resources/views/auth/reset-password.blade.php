@extends('layouts.app')

@section('title', 'Buat Password Baru - LAKONI')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 75vh;">
    <div class="col-12 col-sm-10 col-md-8 col-lg-5">
        
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle shadow mb-2" style="width: 60px; height: 60px;">
                <i class="fa-solid fa-lock-open fs-2"></i>
            </div>
            <h2 class="fw-bold text-primary mb-1">Setel Kata Sandi Baru</h2>
            <p class="text-muted small">Buat kata sandi baru yang aman untuk akun Anda</p>
        </div>

        <div class="card card-custom p-4 p-md-5">
            <form action="{{ route('password.update') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Alamat Email</label>
                    <input type="email" 
                           name="email" 
                           id="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           value="{{ $email ?? old('email') }}" 
                           required 
                           readonly>
                    @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password Baru -->
                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">Kata Sandi Baru</label>
                    <input type="password" 
                           name="password" 
                           id="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           placeholder="Minimal 6 karakter" 
                           required>
                    @error('password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Konfirmasi Password -->
                <div class="mb-4">
                    <label for="password_confirmation" class="form-label fw-semibold">Ulangi Kata Sandi Baru</label>
                    <input type="password" 
                           name="password_confirmation" 
                           id="password_confirmation" 
                           class="form-control" 
                           placeholder="Ulangi kata sandi" 
                           required>
                </div>

                <button type="submit" class="btn btn-primary-lakoni w-100 py-2 fw-semibold mb-3">
                    <i class="fa-solid fa-check me-2"></i> Simpan Password Baru
                </button>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-decoration-none small text-secondary">
                        <i class="fa-solid fa-arrow-left me-1"></i> Batal dan Kembali Login
                    </a>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection

