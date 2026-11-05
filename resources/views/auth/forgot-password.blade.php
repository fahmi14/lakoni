@extends('layouts.app')

@section('title', 'Lupa Password - LAKONI')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 75vh;">
    <div class="col-12 col-sm-10 col-md-8 col-lg-5">
        
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-warning text-white rounded-circle shadow mb-2" style="width: 60px; height: 60px;">
                <i class="fa-solid fa-key fs-2"></i>
            </div>
            <h2 class="fw-bold text-primary mb-1">Reset Password</h2>
            <p class="text-muted small">Masukkan email yang terdaftar untuk menerima link reset</p>
        </div>

        <div class="card card-custom p-4 p-md-5">
            <form action="{{ route('password.email') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label for="email" class="form-label fw-semibold">Alamat Email Terdaftar</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" 
                               name="email" 
                               id="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email') }}" 
                               placeholder="nama@lakoni.id" 
                               required 
                               autofocus>
                    </div>
                    @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary-lakoni w-100 py-2 fw-semibold mb-3">
                    <i class="fa-solid fa-paper-plane me-2"></i> Kirim Tautan Reset Password
                </button>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-decoration-none small text-secondary">
                        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Halaman Login
                    </a>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection

