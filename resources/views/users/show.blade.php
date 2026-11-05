@extends('layouts.app')

@section('title', 'Detail Pengguna - LAKONI')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-0">Detail Pengguna</h3>
                <p class="text-muted small mb-0">Informasi akun pengguna sistem LAKONI</p>
            </div>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="card card-custom p-4">
            <div class="text-center mb-4">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-inline-flex justify-content-center align-items-center mb-2" style="width: 72px; height: 72px; font-size: 2rem;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <h4 class="fw-bold text-dark mb-1">{{ $user->name }}</h4>
                <span class="{{ $user->role === 'admin' ? 'badge-role-admin' : 'badge-role-petugas' }} fs-6">
                    {{ strtoupper($user->role) }}
                </span>
            </div>

            <ul class="list-group list-group-flush mb-4">
                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span class="text-muted"><i class="fa-solid fa-envelope me-2"></i>Alamat Email</span>
                    <span class="fw-semibold text-dark">{{ $user->email }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span class="text-muted"><i class="fa-solid fa-shield-halved me-2"></i>Peran (Role)</span>
                    <span class="fw-semibold">{{ ucfirst($user->role) }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span class="text-muted"><i class="fa-solid fa-calendar-check me-2"></i>Terdaftar Sejak</span>
                    <span class="fw-semibold">{{ $user->created_at ? $user->created_at->format('d F Y, H:i') : '-' }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span class="text-muted"><i class="fa-solid fa-clock-rotate-left me-2"></i>Terakhir Diperbarui</span>
                    <span class="fw-semibold">{{ $user->updated_at ? $user->updated_at->format('d F Y, H:i') : '-' }}</span>
                </li>
            </ul>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('users.edit', $user) }}" class="btn btn-warning text-dark fw-semibold">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit Pengguna
                </a>
            </div>
        </div>

    </div>
</div>
@endsection

