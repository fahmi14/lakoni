@extends('layouts.app')

@section('title', 'Tambah Kegiatan — LAKONI')

@section('content')
<div class="row justify-content-center py-2">
    <div class="col-12 col-md-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold brand-font text-dark mb-1">Tambah Kegiatan Baru</h3>
                <p class="text-muted small mb-0">Input kode unik kegiatan, periode, dan pengaturan pesan WhatsApp</p>
            </div>
            <a href="{{ route('kegiatan.index') }}" class="btn btn-sm btn-pastel-soft">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>

        <div class="card card-clean p-4 p-md-5">
            <form action="{{ route('kegiatan.store') }}" method="POST">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Kode Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror" placeholder="Contoh: SP2026" value="{{ old('kode') }}" required>
                        @error('kode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Nama Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" placeholder="Contoh: Sensus Pertanian 2026 Tahap II" value="{{ old('nama') }}" required>
                        @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input type="date" name="mulai" class="form-control @error('mulai') is-invalid @enderror" value="{{ old('mulai', date('Y-m-d')) }}" required>
                        @error('mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Tanggal Selesai <span class="text-danger">*</span></label>
                        <input type="date" name="selesai" class="form-control @error('selesai') is-invalid @enderror" value="{{ old('selesai', date('Y-m-d', strtotime('+30 days'))) }}" required>
                        @error('selesai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Kolom Acuan / Utama (Pisahkan dengan koma)</label>
                    <input type="text" name="kolom_input" class="form-control" placeholder="Contoh: NKS, Nama Responden, Luas Panen, Produksi, no_hp, Alamat" value="{{ old('kolom_input') }}">
                    <div class="form-text small text-muted mt-2">Dapat otomatis terisi saat melakukan import file spreadsheet anomali.</div>
                </div>

                <!-- Pengaturan Template WhatsApp -->
                <div class="mb-4 p-4 rounded-3 border bg-light">
                    <label class="form-label fw-bold small text-uppercase text-secondary mb-1">
                        <i class="fa-brands fa-whatsapp text-success me-1 fs-5"></i> Pengaturan Template Pesan WhatsApp
                    </label>
                    <p class="small text-muted mb-3">
                        Gunakan placeholder variabel seperti <code>{nama_ppl}</code>, <code>{nama_pml}</code>, atau <code>{nama_kolom}</code> sesuai data di file detail anomali (misal: <code>{Nama Responden}</code>, <code>{Komoditas}</code>, <code>{NKS}</code>).
                    </p>
                    <textarea name="template_wa" class="form-control" rows="4" placeholder="Hai {nama_ppl}, mohon konfirmasi untuk anomali pada responden {Nama Responden} ({Komoditas}) dengan NKS {NKS}. Terima kasih.">{{ old('template_wa', "Hai {nama_ppl}, mohon segera melakukan konfirmasi data anomali untuk responden {Nama Responden} di kegiatan ini. Terima kasih.") }}</textarea>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="{{ route('kegiatan.index') }}" class="btn btn-light border px-4 py-2 text-secondary">Batal</a>
                    <button type="submit" class="btn btn-pastel-primary px-4 py-2 fw-semibold">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Kegiatan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
