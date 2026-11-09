@extends('layouts.app')

@section('title', 'Manajemen Alokasi Petugas — LAKONI')

@section('content')
@php
    $hasSelectedKegiatan = request()->has('id_kegiatan') && request('id_kegiatan') !== '';
@endphp

<div class="row mb-4 align-items-center">
    <div class="col-md-5">
        <h3 class="fw-bold brand-font text-dark mb-1">Manajemen Alokasi Petugas</h3>
        <p class="text-muted mb-0">Petugas PPL, PML dan Wilayah Tugas per Kegiatan</p>
    </div>
    <div class="col-md-7 text-md-end mt-3 mt-md-0 d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
        @if($hasSelectedKegiatan)
            <!-- Tombol Sinkron ke Anomali -->
            <form action="{{ route('alokasi.sync') }}" method="POST" class="d-inline">
                @csrf
                @if(request('id_kegiatan') !== 'all')
                    <input type="hidden" name="id_kegiatan" value="{{ request('id_kegiatan') }}">
                @endif
                <button type="submit" class="btn btn-pastel-warning shadow-sm" title="Sinkronkan pemetaan petugas yang baru diubah ke tabel anomali">
                    <i class="fa-solid fa-arrows-rotate me-1"></i> Sinkron ke Anomali
                </button>
            </form>

            <a href="{{ route('alokasi.template') }}" class="btn btn-pastel-soft shadow-sm" title="Unduh format spreadsheet">
                <i class="fa-solid fa-file-arrow-down me-1 text-primary"></i> Download Template
            </a>
            <a href="{{ route('alokasi.import') }}" class="btn btn-pastel-primary shadow-sm">
                <i class="fa-solid fa-file-excel me-1"></i> Import Excel / CSV
            </a>
            <a href="{{ route('alokasi.create') }}" class="btn btn-light border text-secondary">
                <i class="fa-solid fa-plus me-1"></i> Tambah Manual
            </a>
        @endif
    </div>
</div>

<!-- Filter Box Kalem (Wajib Pilih Kegiatan) -->
<div class="card card-clean p-4 mb-4">
    <form action="{{ route('alokasi.index') }}" method="GET" class="row g-3 align-items-center">
        <div class="col-md-5">
            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">
                Pilih Kegiatan <span class="text-danger">*</span>
            </label>
            <select name="id_kegiatan" class="form-select" id="filter_kegiatan" required onchange="this.form.submit()">
                <option value="">-- Pilih Kegiatan Terlebih Dahulu --</option>
                <option value="all" {{ request('id_kegiatan') === 'all' ? 'selected' : '' }}>-- Tampilkan Semua Kegiatan --</option>
                @foreach($kegiatans as $keg)
                    <option value="{{ $keg->id }}" {{ (string)request('id_kegiatan') === (string)$keg->id ? 'selected' : '' }}>
                        [{{ $keg->kode }}] {{ $keg->nama }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-5">
            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Pencarian Petugas / Wilayah</label>
            <input type="text" name="search" class="form-control" placeholder="Ketik nama PPL, PML, atau Wilayah..." value="{{ request('search') }}" {{ !$hasSelectedKegiatan ? 'disabled' : '' }}>
        </div>
        <div class="col-md-2 d-flex gap-2 align-items-end pt-md-4">
            <button type="submit" class="btn btn-pastel-primary flex-grow-1" {{ !$hasSelectedKegiatan ? 'disabled' : '' }}>
                <i class="fa-solid fa-filter me-1"></i> Cari
            </button>
            @if($hasSelectedKegiatan)
                <a href="{{ route('alokasi.index') }}" class="btn btn-light border" title="Reset filter"><i class="fa-solid fa-arrows-rotate"></i></a>
            @endif
        </div>
    </form>
</div>

@if(!$hasSelectedKegiatan)
    <!-- Banner Informasi Wajib Memilih Kegiatan -->
    <div class="card card-clean p-5 text-center my-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px; border-radius: 50%; background: rgba(79, 111, 246, 0.1); color: var(--lk-primary);">
            <i class="fa-solid fa-calendar-check fs-2"></i>
        </div>
        <h5 class="fw-bold text-dark mb-2">Silakan Pilih Kegiatan Terlebih Dahulu</h5>
        <p class="text-muted small mx-auto mb-3" style="max-width: 480px; line-height: 1.6;">
            Pilih salah satu kegiatan survei atau opsi <strong>Tampilkan Semua Kegiatan</strong> pada dropdown filter di atas untuk menampilkan daftar alokasi petugas dan tombol aksi (tambah manual, import, sinkron).
        </p>
    </div>
@else
    <!-- Bar Aksi Tambahan: Info Total & Tombol Hapus Masal (Bulk Delete by Kegiatan) -->
    <div class="d-flex flex-wrap align-items-center justify-content-between pb-2 mb-3 gap-2">
        <div class="text-muted small">
            Menampilkan <strong>{{ $alokasis->total() }}</strong> data alokasi petugas
        </div>

        <!-- Tombol Hapus Alokasi by Kegiatan (Desain Pastel & Elegan) -->
        <div>
            <button type="button" class="btn btn-sm d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill fw-semibold shadow-xs" 
                    style="background: #fff1f2; color: #e11d48; border: 1.5px solid #fecdd3; transition: all 0.2s;"
                    onmouseover="this.style.background='#ffe4e6'; this.style.borderColor='#fda4af'; this.style.transform='translateY(-1px)';"
                    onmouseout="this.style.background='#fff1f2'; this.style.borderColor='#fecdd3'; this.style.transform='translateY(0)';"
                    data-bs-toggle="modal" data-bs-target="#modalBulkDeleteAlokasi">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 22px; height: 22px; background: rgba(225, 29, 72, 0.15);">
                    <i class="fa-solid fa-trash-can" style="font-size: 0.72rem;"></i>
                </div>
                <span>Hapus Alokasi by Kegiatan</span>
            </button>
        </div>
    </div>

    <!-- Alokasi Modern Table -->
    <div class="table-responsive-modern">
        <table class="table table-clean align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">No</th>
                    <th>Kegiatan</th>
                    <th>Nama PPL (Pencacah)</th>
                    <th>Nama PML (Pengawas)</th>
                    <th>Wilayah Tugas</th>
                    <th class="text-center pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alokasis as $index => $a)
                <tr>
                    <td class="ps-4 text-muted small">{{ $alokasis->firstItem() + $index }}</td>
                    <td>
                        <span class="badge-pastel-primary mb-1">{{ $a->kegiatan->kode ?? '-' }}</span>
                        <div class="fw-semibold text-dark">{{ $a->kegiatan->nama ?? '-' }}</div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-primary" style="width: 32px; height: 32px; background: var(--lk-primary-soft);">
                                <i class="fa-solid fa-user-pen"></i>
                            </div>
                            <span class="fw-bold text-dark">{{ $a->nama_ppl }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-teal" style="width: 32px; height: 32px; background: var(--lk-teal-soft); color: #0284c7;">
                                <i class="fa-solid fa-user-tie"></i>
                            </div>
                            <span class="fw-bold text-dark">{{ $a->nama_pml }}</span>
                        </div>
                    </td>
                    <td>
                        @if($a->wilayah)
                            <span class="badge-pastel-neutral">{{ $a->wilayah->nama }} ({{ strtoupper($a->wilayah->tipe) }})</span>
                        @else
                            <span class="text-muted small">-</span>
                        @endif
                    </td>
                    <td class="text-center pe-4">
                        <div class="d-inline-flex gap-1">
                            <a href="{{ route('alokasi.edit', $a) }}" class="btn-table-action" title="Edit Alokasi">
                                <i class="fa-solid fa-pen-to-square text-primary"></i>
                            </a>
                            <form action="{{ route('alokasi.destroy', $a) }}" method="POST" onsubmit="return confirmPastelAction(this, { title: 'Hapus Alokasi Petugas', message: 'Hapus data alokasi PPL {{ addslashes($a->nama_ppl) }} pada kegiatan ini?' })" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-table-action text-danger" title="Hapus Alokasi">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <div class="py-4">
                            <i class="fa-solid fa-user-group fs-1 text-primary opacity-50 mb-3 d-block"></i>
                            <h6 class="fw-bold text-dark">Belum Ada Data Alokasi</h6>
                            <p class="small text-muted mb-0">Silakan unduh template lalu import data alokasi.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($alokasis->hasPages())
        <div class="p-3 border-top d-flex justify-content-end bg-white">
            {{ $alokasis->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
@endif

<!-- Modal Hapus Masal Alokasi by Kegiatan & Jenis Wilayah (Desain Pastel & Elegan) -->
<div class="modal fade" id="modalBulkDeleteAlokasi" tabindex="-1" aria-labelledby="modalBulkDeleteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 4px; overflow: hidden; animation: popUpModal 0.28s cubic-bezier(0.34, 1.56, 0.64, 1); background: #ffffff;">
            <form action="{{ route('alokasi.bulk-delete') }}" method="POST">
                @csrf
                <div class="p-4">
                    <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <div class="d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 4px; background: #fff1f2; color: #e11d48;">
                                <i class="fa-solid fa-trash-can"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-dark fs-6 mb-0" id="modalBulkDeleteLabel">Hapus Alokasi by Kegiatan</h5>
                                <span class="text-muted small" style="font-size: 0.76rem;">Hapus alokasi petugas secara masal</span>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <p class="text-muted small mb-3" style="line-height: 1.5;">
                        Pilih <strong>Kegiatan</strong> dan <strong>Jenis Wilayah</strong> yang ingin dihapus. Sistem akan otomatis me-reset relasi alokasi pada data anomali terkait.
                    </p>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Kegiatan <span class="text-danger">*</span></label>
                        <select name="id_kegiatan" class="form-select" style="border-radius: 4px;" required>
                            <option value="">-- Pilih Kegiatan --</option>
                            @foreach($kegiatans as $keg)
                                <option value="{{ $keg->id }}" {{ request('id_kegiatan') == $keg->id ? 'selected' : '' }}>
                                    [{{ $keg->kode }}] {{ $keg->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Jenis Wilayah</label>
                        <select name="tipe_wilayah" class="form-select" style="border-radius: 4px;">
                            <option value="all">Semua Jenis Wilayah pada Kegiatan Ini</option>
                            @if(isset($tipeWilayahs) && !empty($tipeWilayahs))
                                @foreach($tipeWilayahs as $tw)
                                    @if($tw)
                                        <option value="{{ $tw }}">{{ strtoupper($tw) }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                        <div class="form-text small text-muted">Pilih "Semua Jenis Wilayah" untuk mengosongkan seluruh alokasi kegiatan tersebut.</div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top px-4 py-3 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light border px-3 text-secondary fw-semibold" style="border-radius: 4px;" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger px-4 fw-semibold shadow-xs" style="border-radius: 4px;">
                        <i class="fa-solid fa-trash-can me-1"></i> Hapus Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
