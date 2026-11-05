@extends('layouts.app')

@section('title', 'Edit Wilayah — LAKONI')

@section('content')
<div class="row justify-content-center py-2">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold brand-font text-dark mb-1">Edit Master Wilayah</h3>
                <p class="text-muted small mb-0">Perbarui informasi nama, kode, atau tingkat wilayah survei</p>
            </div>
            <a href="{{ route('wilayah.index') }}" class="btn btn-sm btn-pastel-soft">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>

        <div class="card card-clean p-4 p-md-5">
            <form action="{{ route('wilayah.update', $wilayah) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Nama Wilayah -->
                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Nama Wilayah <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control" value="{{ old('nama', $wilayah->nama) }}" placeholder="Contoh: Sukamaju / SLS 001" required>
                </div>

                <!-- Kode Wilayah -->
                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Kode Wilayah</label>
                    <input type="text" name="kode" class="form-control" value="{{ old('kode', $wilayah->kode) }}" placeholder="Contoh: 320101">
                    <div class="form-text small text-muted mt-2">Kode BPS wilayah atau nomor urut satuan lingkungan setempat.</div>
                </div>

                <!-- Tipe Wilayah -->
                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Tipe Wilayah <span class="text-danger">*</span></label>
                    <select name="tipe" class="form-select" required>
                        <option value="kecamatan" {{ $wilayah->tipe === 'kecamatan' ? 'selected' : '' }}>Kecamatan</option>
                        <option value="desa" {{ $wilayah->tipe === 'desa' ? 'selected' : '' }}>Desa / Kelurahan</option>
                        <option value="sls" {{ $wilayah->tipe === 'sls' ? 'selected' : '' }}>SLS (Satuan Lingkungan Setempat)</option>
                        <option value="nks" {{ $wilayah->tipe === 'nks' ? 'selected' : '' }}>NKS (Nomor Kode Sampel)</option>
                    </select>
                </div>

                <!-- Action Buttons: Simpan & Batal Pastel -->
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="{{ route('wilayah.index') }}" class="btn btn-light border px-4 py-2 text-secondary">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-pastel-primary px-4 py-2 fw-semibold">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
