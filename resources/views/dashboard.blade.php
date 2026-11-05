@extends('layouts.app')

@section('title', 'Dashboard Administrator — LAKONI')

@section('content')
<!-- Page Title Header Lega & Kalem -->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
    <div>
        <h3 class="fw-bold brand-font text-dark mb-1">Dashboard Ikhtisar</h3>
        <p class="text-muted small mb-0">Pemantauan & pengawasan data anomali kegiatan lapangan.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('anomali.import') }}" class="btn btn-pastel-primary">
            <i class="fa-solid fa-file-excel me-1"></i> Import Anomali
        </a>
        <a href="{{ route('kegiatan.create') }}" class="btn btn-pastel-soft">
            <i class="fa-solid fa-plus me-1"></i> Kegiatan Baru
        </a>
    </div>
</div>

<!-- Pastel Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-clean card-clean-hover p-4 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Total Kegiatan</div>
                    <div class="fs-1 fw-bold text-dark mt-2 lh-1">{{ $totalKegiatan }}</div>
                </div>
                <div class="rounded-4 p-3 d-flex align-items-center justify-content-center" style="background-color: var(--lk-primary-soft); color: var(--lk-primary); width: 54px; height: 54px;">
                    <i class="fa-solid fa-calendar-days fs-4"></i>
                </div>
            </div>
            <div class="small text-muted mt-3 pt-2 border-top">
                <i class="fa-solid fa-circle-check text-success me-1"></i> Kegiatan survei aktif
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-clean card-clean-hover p-4 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Total Anomali</div>
                    <div class="fs-1 fw-bold text-dark mt-2 lh-1">{{ $totalAnomali }}</div>
                </div>
                <div class="rounded-4 p-3 d-flex align-items-center justify-content-center" style="background-color: var(--lk-danger-soft); color: var(--lk-danger); width: 54px; height: 54px;">
                    <i class="fa-solid fa-triangle-exclamation fs-4"></i>
                </div>
            </div>
            <div class="small text-muted mt-3 pt-2 border-top">
                <span class="text-danger fw-bold">{{ $anomaliBelum }}</span> belum dikonfirmasi
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-clean card-clean-hover p-4 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Dikonfirmasi</div>
                    <div class="fs-1 fw-bold text-success mt-2 lh-1">{{ $anomaliSelesai }}</div>
                </div>
                <div class="rounded-4 p-3 d-flex align-items-center justify-content-center" style="background-color: var(--lk-success-soft); color: var(--lk-success); width: 54px; height: 54px;">
                    <i class="fa-solid fa-circle-check fs-4"></i>
                </div>
            </div>
            <div class="small text-muted mt-3 pt-2 border-top">
                <i class="fa-solid fa-arrow-trend-up text-success me-1"></i> Respon petugas lapangan
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-clean card-clean-hover p-4 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Alokasi Petugas</div>
                    <div class="fs-1 fw-bold text-dark mt-2 lh-1">{{ $totalAlokasi }}</div>
                </div>
                <div class="rounded-4 p-3 d-flex align-items-center justify-content-center" style="background-color: var(--lk-teal-soft); color: var(--lk-teal); width: 54px; height: 54px;">
                    <i class="fa-solid fa-users fs-4"></i>
                </div>
            </div>
            <div class="small text-muted mt-3 pt-2 border-top">
                PML & PPL terdaftar
            </div>
        </div>
    </div>
</div>

<!-- Table Card & Guide Card -->
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card card-clean p-0 overflow-hidden">
            <div class="card-header d-flex justify-content-between align-items-center bg-white py-3 px-4">
                <span class="fw-bold text-dark"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Catatan Anomali Terbaru</span>
                <a href="{{ route('anomali.index') }}" class="small fw-semibold text-primary text-decoration-none">Kelola Semua &rarr;</a>
            </div>
            <div class="table-responsive">
                <table class="table table-clean align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Kegiatan</th>
                            <th>Template Konfirmasi</th>
                            <th>Status</th>
                            <th>Pengonfirmasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAnomalis as $ra)
                        <tr>
                            <td class="ps-4">
                                <span class="badge-pastel-primary">{{ $ra->kegiatan->kode ?? '-' }}</span>
                            </td>
                            <td class="fw-semibold text-dark">{{ $ra->konfirmasi->nama ?? '-' }}</td>
                            <td>
                                @if($ra->status === 'sesuai')
                                    <span class="badge-pastel-success">Sesuai</span>
                                @elseif($ra->status === 'tidak_sesuai')
                                    <span class="badge-pastel-danger">Perlu Revisi</span>
                                @elseif($ra->status === 'dikonfirmasi')
                                    <span class="badge-pastel-primary">Dikonfirmasi</span>
                                @else
                                    <span class="badge-pastel-warning">Belum</span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $ra->petugas_pengonfirmasi ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">Belum ada data anomali yang diimpor.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-clean p-4">
            <h5 class="fw-bold brand-font text-dark mb-3"><i class="fa-solid fa-compass text-primary me-2"></i>Alur Singkat LAKONI</h5>
            <div class="d-flex flex-column gap-3 small text-secondary">
                <div class="p-3 rounded-3" style="background-color: var(--lk-primary-soft);">
                    <strong class="text-dark d-block mb-1">1. Template Pertanyaan (JSON)</strong>
                    Atur pertanyaan (pilihan ganda / teks terbuka) di menu <a href="{{ route('konfirmasi.index') }}" class="text-primary fw-bold">Template Konfirmasi</a>.
                </div>
                <div class="p-3 rounded-3" style="background-color: var(--lk-teal-soft);">
                    <strong class="text-dark d-block mb-1">2. Import Alokasi</strong>
                    Unggah daftar nama PPL & PML via Excel di menu <a href="{{ route('alokasi.index') }}" class="text-teal fw-bold">Alokasi Petugas</a>.
                </div>
                <div class="p-3 rounded-3" style="background-color: var(--lk-purple-soft);">
                    <strong class="text-dark d-block mb-1">3. Import Data Anomali</strong>
                    Upload file anomali, preview tab sheet & pilih kolom simpan di menu <a href="{{ route('anomali.index') }}" class="text-purple fw-bold">Manajemen Anomali</a>.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
