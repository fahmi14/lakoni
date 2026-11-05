@extends('layouts.app')

@section('title', 'Import File Anomali — LAKONI')

@section('content')
<div class="row justify-content-center py-2">
    <div class="col-12 col-md-8 col-lg-7">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold brand-font text-dark mb-1">Import Data Anomali (Langkah 1)</h3>
                <p class="text-muted small mb-0">Pilih kegiatan, template konfirmasi, dan unggah satu atau beberapa file Excel/CSV</p>
            </div>
            <a href="{{ route('anomali.index') }}" class="btn btn-sm btn-pastel-soft">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>

        <div class="card card-clean p-4 p-md-5">
            <!-- Box Download Template Anomali -->
            <div class="p-4 mb-4 rounded-3 border bg-light d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="fw-bold text-dark"><i class="fa-solid fa-file-excel text-success me-2"></i>Template File Anomali</div>
                    <div class="small text-muted">Unduh contoh format kolom file excel sebelum melakukan impor data.</div>
                </div>
                <a href="{{ route('anomali.template') }}" class="btn btn-pastel-soft btn-sm">
                    <i class="fa-solid fa-download me-1 text-primary"></i> Download Template Anomali
                </a>
            </div>

            <form action="{{ route('anomali.preview') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Pilih Kegiatan -->
                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Pilih Kegiatan <span class="text-danger">*</span></label>
                    <select name="id_kegiatan" class="form-select" required>
                        <option value="">-- Pilih Kegiatan --</option>
                        @foreach($kegiatans as $k)
                            <option value="{{ $k->id }}">[{{ $k->kode }}] {{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Template Konfirmasi -->
                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Pilih Template Konfirmasi <span class="text-danger">*</span></label>
                    <select name="id_konfirmasi" class="form-select" required>
                        <option value="">-- Pilih Template Konfirmasi --</option>
                        @foreach($konfirmasis as $konf)
                            <option value="{{ $konf->id }}">{{ $konf->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Multi-File Upload -->
                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase text-secondary mb-2">
                        File Spreadsheet (Excel .xlsx, .xls atau CSV) <span class="text-danger">*</span>
                    </label>
                    <input type="file" name="files[]" class="form-control" accept=".xlsx,.xls,.csv" multiple required>
                    <div class="form-text small mt-2 text-muted">
                        <i class="fa-solid fa-circle-check text-success me-1"></i> Anda dapat memilih <strong>lebih dari satu file sekaligus</strong> (tekan Ctrl / Shift saat memilih file).
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                    <a href="{{ route('anomali.index') }}" class="btn btn-light border px-4 py-2 text-secondary">Batal</a>
                    <button type="submit" class="btn btn-pastel-primary px-4 py-2 fw-semibold">
                        <i class="fa-solid fa-eye me-1"></i> Lanjut ke Preview & Pemetaan Kolom
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
