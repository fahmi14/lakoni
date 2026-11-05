@extends('layouts.app')

@section('title', 'Manajemen Alokasi Petugas — LAKONI')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold brand-font text-dark mb-1">Manajemen Alokasi Petugas</h3>
        <p class="text-muted mb-0">Petugas PPL, PML dan Wilayah Tugas per Kegiatan</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex flex-wrap gap-2 justify-content-md-end">
        <a href="{{ route('alokasi.template') }}" class="btn btn-pastel-soft shadow-sm" title="Unduh format spreadsheet">
            <i class="fa-solid fa-file-arrow-down me-1 text-primary"></i> Download Template
        </a>
        <a href="{{ route('alokasi.import') }}" class="btn btn-pastel-primary shadow-sm">
            <i class="fa-solid fa-file-excel me-1"></i> Import Excel / CSV
        </a>
        <a href="{{ route('alokasi.create') }}" class="btn btn-light border text-secondary">
            <i class="fa-solid fa-plus me-1"></i> Tambah Manual
        </a>
    </div>
</div>

<!-- Filter Box Kalem -->
<div class="card card-clean p-4 mb-4">
    <form action="{{ route('alokasi.index') }}" method="GET" class="row g-3 align-items-center">
        <div class="col-md-5">
            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Kegiatan</label>
            <select name="id_kegiatan" class="form-select">
                <option value="">-- Semua Kegiatan --</option>
                @foreach($kegiatans as $keg)
                    <option value="{{ $keg->id }}" {{ request('id_kegiatan') == $keg->id ? 'selected' : '' }}>
                        [{{ $keg->kode }}] {{ $keg->nama }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-5">
            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Pencarian Petugas</label>
            <input type="text" name="search" class="form-control" placeholder="Ketik nama PPL atau PML..." value="{{ request('search') }}">
        </div>
        <div class="col-md-2 d-flex gap-2 align-items-end pt-md-4">
            <button type="submit" class="btn btn-pastel-soft flex-grow-1"><i class="fa-solid fa-filter me-1"></i> Filter</button>
            @if(request()->has('id_kegiatan') || request()->has('search'))
                <a href="{{ route('alokasi.index') }}" class="btn btn-light border" title="Reset filter"><i class="fa-solid fa-arrows-rotate"></i></a>
            @endif
        </div>
    </form>
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
                        <span class="badge-pastel-neutral">{{ $a->wilayah->nama }} ({{ $a->wilayah->tipe }})</span>
                    @else
                        <span class="text-muted small">-</span>
                    @endif
                </td>
                <td class="text-center pe-4">
                    <div class="d-inline-flex gap-1">
                        <a href="{{ route('alokasi.edit', $a) }}" class="btn-table-action" title="Edit Alokasi">
                            <i class="fa-solid fa-pen-to-square text-primary"></i>
                        </a>
                        <form action="{{ route('alokasi.destroy', $a) }}" method="POST" onsubmit="return confirm('Hapus data alokasi ini?')" class="d-inline">
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
@endsection
