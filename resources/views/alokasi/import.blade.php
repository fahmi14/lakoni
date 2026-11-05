@extends('layouts.app')

@section('title', 'Import Alokasi Petugas — LAKONI')

@section('content')
<div class="row justify-content-center py-2">
    <div class="col-12 col-md-7">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold brand-font text-dark mb-1">Import Alokasi Petugas</h3>
                <p class="text-muted small mb-0">Unggah file Excel (.xlsx) atau CSV berisikan daftar petugas</p>
            </div>
            <a href="{{ route('alokasi.index') }}" class="btn btn-sm btn-pastel-soft">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="card card-clean p-4 p-md-5">
            <!-- Box Panduan & Download Template -->
            <div class="p-4 mb-4 rounded-3 border bg-light d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="fw-bold text-dark"><i class="fa-solid fa-file-excel text-success me-2"></i>Format Spreadsheet</div>
                    <div class="small text-muted">Gunakan template resmi agar header kolom terbaca tepat.</div>
                </div>
                <a href="{{ route('alokasi.template') }}" class="btn btn-pastel-soft btn-sm">
                    <i class="fa-solid fa-download me-1 text-primary"></i> Download Template Alokasi
                </a>
            </div>

            <form action="{{ route('alokasi.import.process') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Pilih Kegiatan Tujuan <span class="text-danger">*</span></label>
                    <select name="id_kegiatan" class="form-select" required>
                        <option value="">-- Pilih Kegiatan --</option>
                        @foreach($kegiatans as $k)
                            <option value="{{ $k->id }}">[{{ $k->kode }}] {{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Pilih File Spreadsheet (.xlsx, .csv) <span class="text-danger">*</span></label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text small mt-2 text-muted">
                        Format kolom standar: <code>nama_ppl</code>, <code>nama_pml</code>, dan <code>wilayah</code>.
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-2">
                    <a href="{{ route('alokasi.index') }}" class="btn btn-light border px-4">Batal</a>
                    <button type="submit" class="btn btn-pastel-success px-4 fw-semibold">
                        <i class="fa-solid fa-file-import me-1"></i> Upload & Import Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
