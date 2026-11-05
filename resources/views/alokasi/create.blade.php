@extends('layouts.app')

@section('title', 'Tambah Alokasi Petugas — LAKONI')

@section('content')
<div class="row justify-content-center py-2">
    <div class="col-12 col-md-8 col-lg-7">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold brand-font text-dark mb-1">Tambah Alokasi Manual</h3>
                <p class="text-muted small mb-0">Tentukan nama PPL, PML, dan wilayah pada kegiatan tertentu</p>
            </div>
            <a href="{{ route('alokasi.index') }}" class="btn btn-sm btn-pastel-soft">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>

        <div class="card card-clean p-4 p-md-5">
            <form action="{{ route('alokasi.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Pilih Kegiatan <span class="text-danger">*</span></label>
                    <select name="id_kegiatan" class="form-select" required>
                        <option value="">-- Pilih Kegiatan --</option>
                        @foreach($kegiatans as $k)
                            <option value="{{ $k->id }}">[{{ $k->kode }}] {{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Nama Petugas PPL (Pencacah) <span class="text-danger">*</span></label>
                        <input type="text" name="nama_ppl" class="form-control" placeholder="Contoh: Ahmad Subarjo" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Nama Pengawas PML <span class="text-danger">*</span></label>
                        <input type="text" name="nama_pml" class="form-control" placeholder="Contoh: Siti Rahmawati" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Wilayah Tugas (Opsional)</label>
                    <select name="id_wilayah" class="form-select">
                        <option value="">-- Tanpa Wilayah / Pilih Wilayah --</option>
                        @foreach($wilayahs as $w)
                            <option value="{{ $w->id }}">{{ $w->nama }} ({{ $w->tipe }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="{{ route('alokasi.index') }}" class="btn btn-light border px-4 py-2 text-secondary">Batal</a>
                    <button type="submit" class="btn btn-pastel-primary px-4 py-2 fw-semibold">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Alokasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
