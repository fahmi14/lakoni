@extends('layouts.app')

@section('title', 'Edit Alokasi Petugas — LAKONI')

@section('content')
<div class="row justify-content-center py-2">
    <div class="col-12 col-md-8 col-lg-7">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold brand-font text-dark mb-1">Edit Alokasi Petugas</h3>
                <p class="text-muted small mb-0">Perbarui penugasan PPL, PML, atau wilayah tugas kegiatan</p>
            </div>
            <a href="{{ route('alokasi.index') }}" class="btn btn-sm btn-pastel-soft">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>

        <div class="card card-clean p-4 p-md-5">
            <form action="{{ route('alokasi.update', $alokasi) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Pilih Kegiatan -->
                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Pilih Kegiatan <span class="text-danger">*</span></label>
                    <select name="id_kegiatan" class="form-select" required>
                        @foreach($kegiatans as $k)
                            <option value="{{ $k->id }}" {{ old('id_kegiatan', $alokasi->id_kegiatan) == $k->id ? 'selected' : '' }}>
                                [{{ $k->kode }}] {{ $k->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Nama Petugas PPL & PML -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Nama Petugas PPL (Pencacah) <span class="text-danger">*</span></label>
                        <input type="text" name="nama_ppl" class="form-control" value="{{ old('nama_ppl', $alokasi->nama_ppl) }}" placeholder="Contoh: Ahmad Subarjo" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Nama Pengawas PML <span class="text-danger">*</span></label>
                        <input type="text" name="nama_pml" class="form-control" value="{{ old('nama_pml', $alokasi->nama_pml) }}" placeholder="Contoh: Siti Rahmawati" required>
                    </div>
                </div>

                <!-- Wilayah Tugas -->
                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Wilayah Tugas (Opsional)</label>
                    <select name="id_wilayah" class="form-select">
                        <option value="">-- Tanpa Wilayah / Pilih Wilayah --</option>
                        @foreach($wilayahs as $w)
                            <option value="{{ $w->id }}" {{ old('id_wilayah', $alokasi->id_wilayah) == $w->id ? 'selected' : '' }}>
                                {{ $w->nama }} ({{ $w->tipe }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text small text-muted mt-2">Dapat dikosongkan jika alokasi mencakup satu wilayah kegiatan penuh.</div>
                </div>

                <!-- Action Buttons: Simpan & Batal Pastel -->
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="{{ route('alokasi.index') }}" class="btn btn-light border px-4 py-2 text-secondary">
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
