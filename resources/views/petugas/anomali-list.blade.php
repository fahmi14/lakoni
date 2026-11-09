@extends('layouts.app')

@section('title', 'Daftar Anomali — LAKONI')

@push('styles')
<style>
    /* Styling Filter Tab Button Pastel & Kalem 1 Tema */
    .filter-tab-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1.15rem;
        border-radius: 12px;
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1.5px solid #e2e8f0;
        background-color: #ffffff;
        color: #64748b;
    }
    .filter-tab-btn:hover {
        background-color: #f8fafc;
        border-color: #cbd5e1;
        color: #334155;
        transform: translateY(-1px);
    }
    .filter-tab-btn .badge-pill-count {
        padding: 0.2rem 0.55rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        background: rgba(148, 163, 184, 0.15);
        color: inherit;
    }

    /* Active State: 1 Tema Modern Pastel Soft */
    .filter-tab-btn.active-semua {
        background: #4f6ff6 !important;
        border-color: #4f6ff6 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 14px rgba(79, 111, 246, 0.28);
    }
    .filter-tab-btn.active-semua .badge-pill-count {
        background: rgba(255, 255, 255, 0.25) !important;
        color: #ffffff !important;
    }

    .filter-tab-btn.active-belum {
        background: #f59e0b !important;
        border-color: #f59e0b !important;
        color: #ffffff !important;
        box-shadow: 0 4px 14px rgba(245, 158, 11, 0.28);
    }
    .filter-tab-btn.active-belum .badge-pill-count {
        background: rgba(255, 255, 255, 0.25) !important;
        color: #ffffff !important;
    }

    .filter-tab-btn.active-sudah {
        background: #10b981 !important;
        border-color: #10b981 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.28);
    }
    .filter-tab-btn.active-sudah .badge-pill-count {
        background: rgba(255, 255, 255, 0.25) !important;
        color: #ffffff !important;
    }

    /* Dropdown Atur Kolom */
    .column-selector-menu {
        max-height: 320px;
        overflow-y: auto;
        min-width: 260px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        border: 1px solid #e2e8f0;
    }

    /* Input & Button Proporsional Height 42px Pastel */
    .petugas-filter-group .input-group-text {
        height: 42px !important;
        border-radius: 12px 0 0 12px !important;
        border: 1.5px solid #e2e8f0 !important;
        border-right: none !important;
    }
    .petugas-filter-group .form-control,
    .petugas-filter-group .form-select {
        height: 42px !important;
        border-radius: 0 12px 12px 0 !important;
        border: 1.5px solid #e2e8f0 !important;
        font-size: 0.9rem !important;
    }
    .btn-petugas-filter {
        height: 42px !important;
        border-radius: 12px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-weight: 600 !important;
        font-size: 0.875rem !important;
        transition: all 0.2s ease !important;
    }
</style>
@endpush

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-7">
        <h3 class="fw-bold brand-font text-dark mb-1">Daftar Lembar Anomali</h3>
        <p class="text-muted mb-0">
            Petugas: <strong>{{ $nama }}</strong> (<span class="badge-pastel-primary text-uppercase">{{ $tipe }}</span>) &bull; 
            Kegiatan: <span class="badge-pastel-neutral">[{{ $kegiatan->kode }}] {{ $kegiatan->nama }}</span>
        </p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <a href="{{ route('petugas.select-petugas') }}" class="btn btn-sm btn-pastel-soft me-1">
            <i class="fa-solid fa-arrows-rotate me-1"></i> Ganti Nama
        </a>
        <a href="{{ route('home') }}" class="btn btn-sm btn-light border text-muted">
            <i class="fa-solid fa-barcode me-1"></i> Ganti Kode
        </a>
    </div>
</div>

<!-- 3 Tombol Filter Status 1 Tema Pastel & Kalem -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div class="d-flex flex-wrap gap-2">
        <!-- 1. Semua -->
        <a href="{{ route('petugas.anomali.list', array_merge(request()->except(['status', 'page']))) }}" 
           class="filter-tab-btn {{ (!request()->has('status') || request('status') === '') ? 'active-semua' : '' }}">
            <i class="fa-solid fa-layer-group"></i>
            <span>Semua</span>
            <span class="badge-pill-count">{{ $countSemua }}</span>
        </a>

        <!-- 2. Belum Konfirmasi -->
        <a href="{{ route('petugas.anomali.list', array_merge(request()->except(['status', 'page']), ['status' => 'belum'])) }}" 
           class="filter-tab-btn {{ request('status') === 'belum' ? 'active-belum' : '' }}">
            <i class="fa-solid fa-clock"></i>
            <span>Belum Konfirmasi</span>
            <span class="badge-pill-count">{{ $countBelum }}</span>
        </a>

        <!-- 3. Sudah Konfirmasi -->
        <a href="{{ route('petugas.anomali.list', array_merge(request()->except(['status', 'page']), ['status' => 'sudah'])) }}" 
           class="filter-tab-btn {{ request('status') === 'sudah' ? 'active-sudah' : '' }}">
            <i class="fa-solid fa-circle-check"></i>
            <span>Sudah Konfirmasi</span>
            <span class="badge-pill-count">{{ $countSudah }}</span>
        </a>
    </div>

    <!-- Tombol Atur Kolom Tabel (Extract JSON ke Kolom Sendiri) -->
    @if(!empty($detailKeys))
    <div class="dropdown">
        <button class="btn btn-sm btn-white border shadow-xs dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
            <i class="fa-solid fa-table-columns text-primary"></i>
            <span>Atur Kolom Detail</span>
            <span class="badge bg-primary-subtle text-primary border rounded-pill">{{ count($selectedColumns) }}</span>
        </button>
        <div class="dropdown-menu dropdown-menu-end p-3 column-selector-menu">
            <div class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom">
                <span class="small fw-bold text-dark">Pilih Kolom Ekstrak:</span>
                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none small" id="selectAllCols">Pilih Semua</button>
            </div>
            <form id="columnFilterForm" action="{{ route('petugas.anomali.list') }}" method="GET">
                <!-- Pertahankan parameter query aktif -->
                @if(request()->filled('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                @if(request()->filled('id_konfirmasi')) <input type="hidden" name="id_konfirmasi" value="{{ request('id_konfirmasi') }}"> @endif
                @if(request()->filled('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif

                <div class="d-flex flex-column gap-2 mb-3">
                    @foreach($detailKeys as $dk)
                        <label class="form-check form-check-sm d-flex align-items-center gap-2 mb-0 cursor-pointer">
                            <input class="form-check-input col-checkbox" type="checkbox" name="columns[]" value="{{ $dk }}" 
                                   {{ in_array($dk, $selectedColumns) ? 'checked' : '' }}>
                            <span class="form-check-label small text-dark text-truncate" title="{{ $dk }}">{{ $dk }}</span>
                        </label>
                    @endforeach
                </div>
                <button type="submit" class="btn btn-sm btn-pastel-primary w-100 py-1">Terapkan Kolom</button>
            </form>
        </div>
    </div>
    @endif
</div>

<!-- Filter & Custom Search Box -->
<div class="card card-clean p-3 mb-4">
    <form action="{{ route('petugas.anomali.list') }}" method="GET" class="row g-2 align-items-center">
        <!-- Simpan status & kolom yang sedang aktif -->
        @if(request()->filled('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif
        @foreach($selectedColumns as $sc)
            <input type="hidden" name="columns[]" value="{{ $sc }}">
        @endforeach

        <!-- Filter Template Konfirmasi Kegiatan -->
        <div class="col-12 col-md-4">
            <div class="input-group petugas-filter-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-clipboard-question"></i></span>
                <select name="id_konfirmasi" class="form-select border-start-0" onchange="this.form.submit()">
                    <option value="">-- Semua Template Konfirmasi --</option>
                    @foreach($availableKonfirmasis as $konf)
                        <option value="{{ $konf->id }}" {{ (string)request('id_konfirmasi') === (string)$konf->id ? 'selected' : '' }}>
                            {{ $konf->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Custom Search Semua Variabel & Detail JSON -->
        <div class="col-12 col-md-6">
            <div class="input-group petugas-filter-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" name="search" class="form-control border-start-0" 
                       placeholder="Cari kata kunci pada semua variabel / kolom detail JSON..." 
                       value="{{ request('search') }}">
            </div>
        </div>

        <div class="col-12 col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-pastel-primary btn-petugas-filter flex-grow-1">
                <i class="fa-solid fa-search me-1"></i> Cari
            </button>
            @if(request()->filled('search') || request()->filled('id_konfirmasi'))
                <a href="{{ route('petugas.anomali.list', request()->only(['status', 'columns'])) }}" class="btn btn-light border btn-petugas-filter px-3" title="Reset filter & pencarian">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Card Anomali List Lega & Bersih dengan Kolom Hasil Ekstrak JSON -->
<div class="card card-clean p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-clean align-middle mb-0" style="table-layout: auto;">
            <thead>
                <tr>
                    <th class="ps-4" style="width: 50px; white-space: nowrap;">No</th>
                    <th style="min-width: 160px; max-width: 220px; white-space: normal;">Judul Anomali</th>

                    <!-- Kolom Hasil Ekstrak JSON Dinamis -->
                    @foreach($selectedColumns as $sc)
                        <th class="text-uppercase" style="font-size: 0.73rem; letter-spacing: 0.05em; background: #f8fafc; color: #475569; min-width: 140px; max-width: 320px; white-space: normal; line-height: 1.35; word-break: break-word;">
                            {{ $sc }}
                        </th>
                    @endforeach

                    <th style="min-width: 140px; white-space: nowrap;">Status</th>
                    <th class="text-center pe-4" style="width: 170px; white-space: nowrap;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($anomalis as $index => $a)
                @php
                    $searchKeyword = strtolower(request('search', ''));
                @endphp
                <tr>
                    <td class="ps-4 text-muted small" style="white-space: nowrap;">{{ $anomalis->firstItem() + $index }}</td>
                    <td style="white-space: normal; min-width: 160px;">
                        <div class="fw-bold text-dark fs-6" style="word-break: break-word;">{{ $a->konfirmasi->nama ?? 'Konfirmasi Anomali' }}</div>
                        <span class="badge-pastel-neutral" style="font-size: 0.7rem;">ID: #{{ $a->id }}</span>
                    </td>

                    <!-- Nilai masing-masing kolom JSON hasil ekstrak (Wrap Text Lega) -->
                    @foreach($selectedColumns as $sc)
                        @php
                            $val = $a->detail[$sc] ?? '-';
                            $valStr = is_array($val) ? json_encode($val) : (string)$val;
                            $isMatched = $searchKeyword !== '' && str_contains(strtolower($valStr), $searchKeyword);
                            
                            // Deteksi apakah kolom ini adalah LINK atau berisi tag HTML/URL
                            $isLinkCol = (stripos($sc, 'link') !== false || stripos($valStr, '<a ') !== false || filter_var($valStr, FILTER_VALIDATE_URL));
                            $urlTarget = null;
                            $linkText = 'Buka Tautan';
                            if ($isLinkCol) {
                                if (preg_match('/href=[\'"]([^\'"]+)[\'"]/', $valStr, $m)) {
                                    $urlTarget = $m[1];
                                    if (preg_match('/>([^<]+)<\/a>/', $valStr, $lm)) {
                                        $linkText = trim($lm[1]);
                                    }
                                } elseif (filter_var($valStr, FILTER_VALIDATE_URL)) {
                                    $urlTarget = $valStr;
                                    $linkText = 'Buka Tautan';
                                }
                            }
                        @endphp
                        <td class="{{ $isMatched ? 'bg-primary-subtle text-primary fw-bold' : '' }}" style="white-space: normal; min-width: 140px; max-width: 340px; vertical-align: middle;">
                            @if($urlTarget)
                                <a href="{{ $urlTarget }}" target="_blank" class="btn btn-sm btn-pastel-soft text-primary d-inline-flex align-items-center gap-1 text-decoration-none py-1 px-2 fw-semibold" style="border-radius: 6px; font-size: 0.8rem;" title="Buka tautan di tab baru">
                                    <i class="fa-solid fa-arrow-up-right-from-square small"></i>
                                    <span>{{ $linkText }}</span>
                                </a>
                            @elseif(stripos($valStr, '<') !== false && stripos($valStr, '>') !== false)
                                <span class="small text-dark" style="word-break: break-word; line-height: 1.45; display: block;">
                                    {!! $valStr !!}
                                </span>
                            @else
                                <span class="small text-dark" style="word-break: break-word; line-height: 1.45; display: block;">
                                    {{ $valStr }}
                                </span>
                            @endif
                        </td>
                    @endforeach

                    <td style="white-space: nowrap;">
                        @if($a->status === 'sesuai')
                            <span class="badge-pastel-success"><i class="fa-solid fa-check-double me-1"></i> Sesuai</span>
                        @elseif($a->status === 'tidak_sesuai')
                            <span class="badge-pastel-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> Revisi</span>
                        @elseif($a->status === 'dikonfirmasi')
                            <span class="badge-pastel-primary"><i class="fa-solid fa-check me-1"></i> Sudah Dikonfirmasi</span>
                        @else
                            <span class="badge-pastel-warning"><i class="fa-solid fa-clock me-1"></i> Belum Konfirmasi</span>
                        @endif
                    </td>
                    <td class="text-center pe-4" style="white-space: nowrap;">
                        <a href="{{ route('petugas.konfirmasi', $a) }}" class="btn btn-sm {{ $a->status === 'belum' ? 'btn-pastel-primary' : 'btn-pastel-soft' }}">
                            <i class="fa-solid fa-clipboard-check me-1"></i>
                            {{ $a->status === 'belum' ? 'Konfirmasi Sekarang' : 'Lihat / Edit' }}
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ 4 + count($selectedColumns) }}" class="text-center py-5 text-muted">
                        <div class="py-4">
                            <i class="fa-solid fa-clipboard-check fs-1 text-primary opacity-50 mb-3 d-block"></i>
                            <h6 class="fw-bold text-dark">Tidak Ada Lembar Anomali</h6>
                            <p class="small text-muted mb-0">
                                @if(request()->filled('search') || request()->filled('status') || request()->filled('id_konfirmasi'))
                                    Tidak ada data anomali yang sesuai dengan filter atau kata kunci pencarian.
                                @else
                                    Semua anomali Anda untuk kegiatan ini telah selesai diproses atau belum dialokasikan.
                                @endif
                            </p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($anomalis->hasPages())
    <div class="p-3 border-top d-flex justify-content-end bg-white">
        {{ $anomalis->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllBtn = document.getElementById('selectAllCols');
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const checkboxes = document.querySelectorAll('.col-checkbox');
                const allChecked = Array.from(checkboxes).every(c => c.checked);
                checkboxes.forEach(c => c.checked = !allChecked);
                this.textContent = allChecked ? 'Pilih Semua' : 'Batal Pilih Semua';
            });
        }
    });
</script>
@endpush
