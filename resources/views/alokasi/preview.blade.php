@extends('layouts.app')

@section('title', 'Preview & Pemetaan Kolom Alokasi — LAKONI')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h3 class="fw-bold brand-font text-dark mb-1">Preview & Pemetaan Kolom Alokasi Petugas</h3>
        <p class="text-muted mb-0">
            Kegiatan: <strong>[{{ $kegiatan->kode }}] {{ $kegiatan->nama }}</strong> &bull; Total Baris Terbaca: <strong>{{ $totalRows }} data</strong>
        </p>
    </div>
    <div class="col-md-4 text-md-end">
        <a href="{{ route('alokasi.import') }}" class="btn btn-sm btn-pastel-soft">
            <i class="fa-solid fa-arrow-left me-1"></i> Batal / Upload Ulang
        </a>
    </div>
</div>

<form action="{{ route('alokasi.execute-import') }}" method="POST">
    @csrf
    <input type="hidden" name="id_kegiatan" value="{{ $kegiatan->id }}">
    <input type="hidden" name="file_path" value="{{ $filePath }}">

    <!-- Kartu Pemilihan Kolom: PML, PPL, dan Wilayah -->
    <div class="card card-clean p-4 mb-4">
        <div class="d-flex align-items-center gap-2 mb-2">
            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: rgba(79, 111, 246, 0.1); color: var(--lk-primary);">
                <i class="fa-solid fa-users-gear fs-6"></i>
            </div>
            <div>
                <h5 class="fw-bold brand-font text-dark mb-0">Tentukan Kolom Petugas & Wilayah</h5>
                <span class="small text-muted">Pilih header kolom dari file spreadsheet yang sesuai untuk data alokasi</span>
            </div>
        </div>

        <div class="row g-3 p-3 rounded-3 bg-light border mt-2">
            <!-- 1. Kolom Nama PML -->
            <div class="col-12 col-md-4">
                <label class="form-label fw-bold small text-uppercase text-secondary mb-1">
                    Kolom Nama PML (Pengawas) <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-primary">
                        <i class="fa-solid fa-user-tie"></i>
                    </span>
                    <select name="kolom_pml" class="form-select border-start-0" required>
                        <option value="">-- Pilih Kolom PML --</option>
                        @foreach($headers as $h)
                            @php
                                $hLower = strtolower($h);
                                $isPml = str_contains($hLower, 'pml') || str_contains($hLower, 'pengawas') || str_contains($hLower, 'pemeriksa');
                            @endphp
                            <option value="{{ $h }}" {{ $isPml ? 'selected' : '' }}>
                                {{ $h }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- 2. Kolom Nama PPL -->
            <div class="col-12 col-md-4">
                <label class="form-label fw-bold small text-uppercase text-secondary mb-1">
                    Kolom Nama PPL (Pencacah) <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-primary">
                        <i class="fa-solid fa-user-pen"></i>
                    </span>
                    <select name="kolom_ppl" class="form-select border-start-0" required>
                        <option value="">-- Pilih Kolom PPL --</option>
                        @foreach($headers as $h)
                            @php
                                $hLower = strtolower($h);
                                $isPpl = str_contains($hLower, 'ppl') || str_contains($hLower, 'pencacah') || str_contains($hLower, 'petugas');
                            @endphp
                            <option value="{{ $h }}" {{ $isPpl ? 'selected' : '' }}>
                                {{ $h }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- 3. Kolom Wilayah Tugas -->
            <div class="col-12 col-md-4">
                <label class="form-label fw-bold small text-uppercase text-secondary mb-1">
                    Kolom Wilayah Tugas <span class="text-secondary">(Opsional)</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-primary">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </span>
                    <select name="kolom_wilayah" class="form-select border-start-0">
                        <option value="">-- Tanpa Kolom Wilayah --</option>
                        @foreach($headers as $h)
                            @php
                                $hLower = strtolower($h);
                                $isWilayah = str_contains($hLower, 'wilayah') || str_contains($hLower, 'desa') || str_contains($hLower, 'sls') || str_contains($hLower, 'nks') || str_contains($hLower, 'kec');
                            @endphp
                            <option value="{{ $h }}" {{ $isWilayah ? 'selected' : '' }}>
                                {{ $h }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Kartu Preview 5 Baris Data Teratas dari File Spreadsheet -->
    <div class="card card-clean p-0 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
            <div>
                <span class="fw-bold text-dark"><i class="fa-solid fa-table text-primary me-2"></i>Preview 5 Baris Data Teratas dari File</span>
                <span class="text-muted small ms-2">(Total {{ $totalRows }} baris siap diimpor)</span>
            </div>
            <span class="badge-pastel-neutral">{{ count($headers) }} Kolom</span>
        </div>
        <div class="table-responsive">
            <table class="table table-clean mb-0 small">
                <thead>
                    <tr>
                        <th class="ps-4" style="width: 50px;">#</th>
                        @foreach($headers as $h)
                            <th class="text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.05em; background: #f8fafc; color: #475569;">
                                {{ $h }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($previewRows as $index => $row)
                        <tr>
                            <td class="ps-4 text-muted fw-bold">{{ $index + 1 }}</td>
                            @foreach($headers as $h)
                                <td>{{ $row[$h] ?? '-' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tombol Simpan & Eksekusi Import -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <a href="{{ route('alokasi.import') }}" class="btn btn-light border px-4">
            <i class="fa-solid fa-arrow-left me-1"></i> Batal / Pilih File Lain
        </a>
        <button type="submit" class="btn btn-pastel-success px-4 py-2 fw-semibold shadow-sm">
            <i class="fa-solid fa-circle-check me-1"></i> Konfirmasi & Simpan Alokasi Petugas
        </button>
    </div>
</form>
@endsection

