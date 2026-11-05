@extends('layouts.app')

@section('title', 'LAKONI — Masukkan Kode Kegiatan')

@section('content')
<div class="row justify-content-center align-items-center py-4" style="min-height: 72vh;">
    <div class="col-12 col-md-8 col-lg-5">
        
        <!-- Header Brand Icon Kalem -->
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center text-white rounded-4 shadow-sm mb-3" style="width: 76px; height: 76px; background: linear-gradient(135deg, #4f6ff6 0%, #0ea5e9 100%);">
                <i class="fa-solid fa-clipboard-check fs-1"></i>
            </div>
            <h2 class="fw-bold brand-font text-dark mb-1">LAKONI</h2>
            <p class="text-muted small">Sistem Lembar Konfirmasi Anomali Lapangan</p>
        </div>

        <!-- Card Input Kode Kegiatan Bersih & Lega -->
        <div class="card card-clean p-4 p-md-5">
            <div class="text-center mb-4">
                <h4 class="fw-bold text-dark mb-1">Konfirmasi Kode Kegiatan</h4>
                <p class="text-muted small mb-0">Petugas lapangan dapat langsung memasukkan kode kegiatan tanpa login.</p>
            </div>

            <form action="{{ route('petugas.kegiatan.verify') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label for="kode" class="form-label fw-bold small text-uppercase text-secondary mb-2">Kode Kegiatan Survei / Sensus</label>
                    <div class="input-clean-group">
                        <span class="icon-slot"><i class="fa-solid fa-barcode text-primary"></i></span>
                        <input type="text" 
                               name="kode" 
                               id="kode" 
                               class="form-control form-control-clean @error('kode') is-invalid @enderror" 
                               placeholder="MISAL: SP2026" 
                               value="{{ old('kode') }}"
                               required 
                               autofocus>
                    </div>
                    <div class="form-text small mt-2 text-muted">
                        <i class="fa-solid fa-circle-info text-primary me-1"></i> Jadwal rentang waktu kegiatan akan divalidasi secara otomatis.
                    </div>
                    @error('kode')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-pastel-primary w-100 py-3 fw-bold fs-6 mb-3">
                    Periksa Kegiatan & Lanjutkan <i class="fa-solid fa-arrow-right ms-2"></i>
                </button>
            </form>

            <div class="border-top pt-3 mt-2 text-center">
                <span class="text-muted small me-2">Pengelola sistem?</span>
                <a href="{{ route('login') }}" class="text-decoration-none fw-bold text-primary small">
                    <i class="fa-solid fa-user-shield me-1"></i> Masuk Sebagai Admin
                </a>
            </div>
        </div>

        <!-- Info Kode Bawaan Kalem -->
        <div class="text-center mt-3">
            <span class="badge-pastel-neutral">Contoh Kode Kegiatan Aktif: <strong class="text-primary">SP2026</strong></span>
        </div>

    </div>
</div>
@endsection
