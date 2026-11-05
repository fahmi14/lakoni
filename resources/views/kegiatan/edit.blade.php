@extends('layouts.app')

@section('title', 'Edit Kegiatan — LAKONI')

@section('content')
<div class="row justify-content-center py-2">
    <div class="col-12 col-md-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold brand-font text-dark mb-1">Edit Kegiatan</h3>
                <p class="text-muted small mb-0">Perbarui periode, kolom acuan, atau format pesan WhatsApp</p>
            </div>
            <a href="{{ route('kegiatan.index') }}" class="btn btn-sm btn-pastel-soft">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>

        <div class="card card-clean p-4 p-md-5">
            <form action="{{ route('kegiatan.update', $kegiatan) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Kode Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" name="kode" class="form-control" value="{{ old('kode', $kegiatan->kode) }}" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Nama Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" value="{{ old('nama', $kegiatan->nama) }}" required>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input type="date" name="mulai" class="form-control" value="{{ old('mulai', $kegiatan->mulai->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Tanggal Selesai <span class="text-danger">*</span></label>
                        <input type="date" name="selesai" class="form-control" value="{{ old('selesai', $kegiatan->selesai->format('Y-m-d')) }}" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Kolom Acuan / Utama (Pisahkan dengan koma)</label>
                    <input type="text" name="kolom_input" class="form-control" value="{{ old('kolom_input', is_array($kegiatan->kolom) ? implode(', ', $kegiatan->kolom) : '') }}">
                </div>

                <!-- Pengaturan Template WhatsApp -->
                <div class="mb-4 p-4 rounded-3 border bg-light">
                    <label class="form-label fw-bold small text-uppercase text-secondary mb-1">
                        <i class="fa-brands fa-whatsapp text-success me-1 fs-5"></i> Pengaturan Template Pesan WhatsApp
                    </label>
                    <p class="small text-muted mb-2">
                        Gunakan placeholder variabel seperti <code>{nama_ppl}</code>, <code>{nama_pml}</code>, atau <code>{nama_kolom}</code> sesuai data di file detail anomali (misal: <code>{Nama Responden}</code>, <code>{Komoditas}</code>, <code>{NKS}</code>).
                    </p>
                    <textarea name="template_wa" class="form-control" rows="4" placeholder="Hai {nama_ppl}, mohon konfirmasi untuk anomali pada responden {Nama Responden} ({Komoditas}). Terima kasih.">{{ old('template_wa', $kegiatan->template_wa ?? "Hai {nama_ppl}, mohon segera melakukan konfirmasi data anomali untuk responden {Nama Responden} di kegiatan ini. Terima kasih.") }}</textarea>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="{{ route('kegiatan.index') }}" class="btn btn-light border px-4 py-2 text-secondary">Batal</a>
                    <button type="submit" class="btn btn-pastel-primary px-4 py-2 fw-semibold">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
