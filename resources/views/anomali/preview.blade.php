@extends('layouts.app')

@section('title', 'Preview Kolom Import — LAKONI')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h3 class="fw-bold brand-font text-dark mb-1">Preview & Pemetaan Kolom Anomali</h3>
        <p class="text-muted mb-0">
            Kegiatan: <strong>[{{ $kegiatan->kode }}] {{ $kegiatan->nama }}</strong> &bull; Konfirmasi: <strong>{{ $konfirmasi->nama }}</strong> &bull; Total File: <strong>{{ count($filePaths) }} file</strong>
        </p>
    </div>
    <div class="col-md-4 text-md-end">
        <a href="{{ route('anomali.import') }}" class="btn btn-sm btn-pastel-soft">
            <i class="fa-solid fa-arrow-left me-1"></i> Batal / Upload Ulang
        </a>
    </div>
</div>

<form action="{{ route('anomali.execute-import') }}" method="POST">
    @csrf
    <input type="hidden" name="id_kegiatan" value="{{ $kegiatan->id }}">
    <input type="hidden" name="id_konfirmasi" value="{{ $konfirmasi->id }}">
    @foreach($filePaths as $fp)
        <input type="hidden" name="file_paths[]" value="{{ $fp }}">
    @endforeach

    <!-- Kartu Pengaturan Pemetaan Wilayah / Alokasi -->
    <div class="card card-clean p-4 mb-4">
        <h5 class="fw-bold brand-font text-dark mb-2">
            <i class="fa-solid fa-map-location-dot text-primary me-2"></i>Pengaturan Relasi / Kolom Wilayah
        </h5>
        <p class="small text-muted mb-3">
            Tentukan kolom mana dari file spreadsheet yang merupakan penunjuk <strong>Wilayah</strong> (Kecamatan / Desa / SLS / NKS / Kode Sampel). Data anomali akan otomatis dihubungkan dengan <strong>Alokasi Petugas (PPL & PML)</strong> yang bertugas di wilayah tersebut.
        </p>

        <div class="row g-3 align-items-center p-3 rounded-3 bg-light border">
            <div class="col-md-6">
                <label class="form-label fw-bold small text-uppercase text-secondary mb-1">Pilih Kolom Penunjuk Wilayah di File:</label>
                <select name="kolom_wilayah" class="form-select">
                    <option value="">-- Tanpa Kolom Wilayah Khusus (Cari Otomatis) --</option>
                    @foreach($allHeaders as $h)
                        @php
                            $hLower = strtolower($h);
                            $isSelected = str_contains($hLower, 'wilayah') || str_contains($hLower, 'desa') || str_contains($hLower, 'sls') || str_contains($hLower, 'nks');
                        @endphp
                        <option value="{{ $h }}" {{ $isSelected ? 'selected' : '' }}>
                            {{ $h }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text small text-muted">Sistem akan mencocokkan isian kolom ini dengan master wilayah atau nama wilayah di alokasi petugas.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold small text-uppercase text-secondary mb-1">Status Pencocokan Alokasi:</label>
                <div class="p-2 rounded bg-white border small text-secondary">
                    <i class="fa-solid fa-link text-success me-1"></i> Jika baris anomali cocok dengan nama wilayah di alokasi, anomali tersebut akan langsung <strong>muncul di akun Petugas PPL & PML</strong> yang bertugas di wilayah tersebut.
                </div>
            </div>
        </div>
    </div>

    <!-- Kartu Seleksi Kolom yang Disimpan -->
    <div class="card card-clean p-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <div>
                <h5 class="fw-bold brand-font text-dark mb-0">
                    <i class="fa-solid fa-table-columns text-primary me-2"></i>Pilih Kolom Yang Ingin Disimpan ke Database
                </h5>
                <span class="small text-muted">Centang kolom yang akan dimasukkan ke dalam JSON detail anomali</span>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-pastel-soft" id="btn-select-all">Pilih Semua</button>
                <button type="button" class="btn btn-sm btn-light border" id="btn-unselect-all">Batalkan Semua</button>
            </div>
        </div>

        <div class="row g-2 p-3 rounded-3 bg-light border mb-2">
            @foreach($allHeaders as $h)
            <div class="col-6 col-md-4 col-lg-3">
                <div class="form-check p-2 rounded bg-white border">
                    <input class="form-check-input col-checkbox ms-1 me-2" type="checkbox" name="selected_columns[]" value="{{ $h }}" id="col_{{ md5($h) }}" checked>
                    <label class="form-check-label fw-semibold text-dark small" for="col_{{ md5($h) }}">
                        {{ $h }}
                    </label>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Preview Tab Per Sheet & File -->
    <div class="card card-clean p-4 mb-4">
        <h5 class="fw-bold brand-font text-dark mb-3">
            <i class="fa-solid fa-file-lines text-teal me-2"></i>Pratinjau Data (Preview Per File / Sheet)
        </h5>

        <!-- Nav Tabs -->
        <ul class="nav nav-pills mb-3 gap-1" id="sheetTabs" role="tablist">
            @foreach($sheetsData as $idx => $s)
            <li class="nav-item" role="presentation">
                <button class="nav-link btn-sm {{ $idx === 0 ? 'active' : '' }}" id="tab-btn-{{ $idx }}" data-bs-toggle="pill" data-bs-target="#tab-content-{{ $idx }}" type="button" role="tab">
                    <i class="fa-solid fa-table me-1"></i> {{ $s['fileName'] }} &bull; {{ $s['name'] }} ({{ $s['totalRows'] }} baris)
                </button>
            </li>
            @endforeach
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="sheetTabsContent">
            @foreach($sheetsData as $idx => $s)
            <div class="tab-pane fade {{ $idx === 0 ? 'show active' : '' }}" id="tab-content-{{ $idx }}" role="tabpanel">
                <div class="table-responsive rounded-3 border">
                    <table class="table table-clean table-sm mb-0">
                        <thead>
                            <tr>
                                @foreach($s['headers'] as $head)
                                    <th>{{ $head }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($s['previewRows'] as $pRow)
                            <tr>
                                @foreach($pRow as $cell)
                                    <td>{{ $cell }}</td>
                                @endforeach
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ count($s['headers']) }}" class="text-center py-3 text-muted">Tidak ada baris data pada sheet ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="small text-muted mt-2 fst-italic">* Menampilkan 5 baris pertama dari {{ $s['totalRows'] }} total baris.</div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-5">
        <a href="{{ route('anomali.import') }}" class="btn btn-light border px-4 py-2 text-secondary">Batal</a>
        <button type="submit" class="btn btn-pastel-success px-4 py-2 fw-semibold">
            <i class="fa-solid fa-check-double me-2"></i> Eksekusi & Simpan Data ke Database
        </button>
    </div>
</form>

@push('scripts')
<script>
    document.getElementById('btn-select-all').addEventListener('click', function() {
        document.querySelectorAll('.col-checkbox').forEach(cb => cb.checked = true);
    });
    document.getElementById('btn-unselect-all').addEventListener('click', function() {
        document.querySelectorAll('.col-checkbox').forEach(cb => cb.checked = false);
    });
</script>
@endpush
@endsection
