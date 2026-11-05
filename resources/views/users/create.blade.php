@extends('layouts.app')

@section('title', 'Tambah Pengguna Baru — LAKONI')

@section('content')
<div class="row justify-content-center py-2">
    <div class="col-12 col-md-8 col-lg-6">
        
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold brand-font text-dark mb-1">Tambah Pengguna Baru</h3>
                <p class="text-muted small mb-0">Daftarkan akun Admin atau Petugas baru ke dalam sistem</p>
            </div>
            <a href="{{ route('users.index') }}" class="btn btn-sm btn-pastel-soft">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>

        <div class="card card-clean p-4 p-md-5">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf

                <!-- Nama Lengkap -->
                <div class="mb-4">
                    <label for="name" class="form-label fw-bold small text-uppercase text-secondary mb-2">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name') }}" 
                           placeholder="Contoh: Budi Santoso" 
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="form-label fw-bold small text-uppercase text-secondary mb-2">Alamat Email <span class="text-danger">*</span></label>
                    <input type="email" 
                           name="email" 
                           id="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           value="{{ old('email') }}" 
                           placeholder="budi@lakoni.id" 
                           required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Peran (Role) -->
                <div class="mb-4">
                    <label for="role" class="form-label fw-bold small text-uppercase text-secondary mb-2">Peran (Role) <span class="text-danger">*</span></label>
                    <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                        <option value="">-- Pilih Peran Akun --</option>
                        <option value="petugas" {{ old('role') === 'petugas' ? 'selected' : '' }}>Petugas Lapangan</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin (Administrator Sistem)</option>
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="form-label fw-bold small text-uppercase text-secondary mb-2">Kata Sandi (Password) <span class="text-danger">*</span></label>
                    <input type="password" 
                           name="password" 
                           id="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           placeholder="Minimal 6 karakter" 
                           required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="{{ route('users.index') }}" class="btn btn-light border px-4 py-2 text-secondary">Batal</a>
                    <button type="submit" class="btn btn-pastel-primary px-4 py-2 fw-semibold">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Pengguna
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
