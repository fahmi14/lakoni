@extends('layouts.app')

@section('title', 'Login Administrator — LAKONI')

@section('content')
<div class="row justify-content-center align-items-center py-4" style="min-height: 72vh;">
    <div class="col-12 col-md-8 col-lg-5">
        
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center text-white rounded-4 shadow-sm mb-3" style="width: 70px; height: 70px; background: linear-gradient(135deg, #4f6ff6 0%, #8b5cf6 100%);">
                <i class="fa-solid fa-user-shield fs-2"></i>
            </div>
            <h3 class="fw-bold brand-font text-dark mb-1">Login Administrator</h3>
            <p class="text-muted small">Panel Kontrol Manajemen Sistem LAKONI</p>
        </div>

        <div class="card card-clean p-4 p-md-5">
            <form action="{{ route('login.post') }}" method="POST">
                @csrf

                <!-- Email Input -->
                <div class="mb-3">
                    <label for="email" class="form-label fw-bold small text-uppercase text-secondary mb-2">Alamat Email Admin</label>
                    <div class="input-clean-group">
                        <span class="icon-slot"><i class="fa-solid fa-envelope text-primary"></i></span>
                        <input type="email" 
                               name="email" 
                               id="email" 
                               class="form-control form-control-clean @error('email') is-invalid @enderror" 
                               value="{{ old('email', 'admin@lakoni.id') }}" 
                               placeholder="admin@lakoni.id" 
                               required 
                               autofocus>
                    </div>
                    @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password Input -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label for="password" class="form-label fw-bold small text-uppercase text-secondary mb-0">Kata Sandi</label>
                        <a href="{{ route('password.request') }}" class="small text-decoration-none text-primary fw-semibold">
                            Lupa Password?
                        </a>
                    </div>
                    <div class="input-clean-group">
                        <span class="icon-slot"><i class="fa-solid fa-lock text-primary"></i></span>
                        <input type="password" 
                               name="password" 
                               id="password" 
                               class="form-control form-control-clean @error('password') is-invalid @enderror" 
                               placeholder="••••••••" 
                               value="password123"
                               required>
                    </div>
                    @error('password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="mb-4 form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember" checked>
                    <label class="form-check-label text-muted small" for="remember">Ingat Saya</label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-pastel-primary w-100 py-3 fw-bold mb-3">
                    <i class="fa-solid fa-right-to-bracket me-2"></i> Masuk Dashboard
                </button>

                <div class="text-center pt-3 border-top">
                    <a href="{{ route('home') }}" class="text-decoration-none small text-muted">
                        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Halaman Input Kode Petugas
                    </a>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
