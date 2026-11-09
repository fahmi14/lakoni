@extends('layouts.app')

@section('title', 'Manajemen Anomali — LAKONI')

@push('styles')
<style>
    .column-selector-menu {
        max-height: 320px;
        overflow-y: auto;
        min-width: 260px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        border: 1px solid #e2e8f0;
    }
    .filter-input-control {
        height: 42px !important;
        border-radius: 12px !important;
        border: 1.5px solid #e2e8f0 !important;
        font-size: 0.88rem !important;
    }
    .filter-input-group .input-group-text {
        height: 42px !important;
        border-radius: 12px 0 0 12px !important;
        border: 1.5px solid #e2e8f0 !important;
        border-right: none !important;
    }
    .filter-input-group .form-control,
    .filter-input-group .form-select {
        height: 42px !important;
        border-radius: 0 12px 12px 0 !important;
        border: 1.5px solid #e2e8f0 !important;
        font-size: 0.88rem !important;
    }
    .btn-filter-action {
        height: 42px !important;
        border-radius: 12px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-weight: 600 !important;
        font-size: 0.875rem !important;
        transition: all 0.2s ease !important;
    }

    /* Floating Action Bar Kotak, Pastel, Kalem & Elegan */
    .bulk-action-bar {
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%) translateY(100px);
        background: #ffffff;
        color: #1e293b;
        padding: 0.75rem 1.4rem;
        border-radius: 4px; /* Kotak */
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.12), 0 4px 12px -2px rgba(15, 23, 42, 0.08);
        display: flex;
        align-items: center;
        gap: 1.25rem;
        z-index: 1040;
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 0.3s;
        opacity: 0;
        pointer-events: none;
    }
    .bulk-action-bar.show {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
        pointer-events: auto;
    }
</style>
@endpush

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-5">
        <h3 class="fw-bold brand-font text-dark mb-1">Manajemen Anomali</h3>
        <p class="text-muted mb-0">Daftar temuan anomali data lapangan yang perlu dikonfirmasi</p>
    </div>
    <div class="col-md-7 text-md-end mt-3 mt-md-0 d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
        <!-- Tombol Sinkron Alokasi Petugas ke Anomali -->
        <form action="{{ route('anomali.sync') }}" method="POST" class="d-inline">
            @csrf
            @if(request()->filled('id_kegiatan'))
                <input type="hidden" name="id_kegiatan" value="{{ request('id_kegiatan') }}">
            @endif
            <button type="submit" class="btn btn-pastel-warning shadow-sm d-inline-flex align-items-center justify-content-center text-center px-3 py-2" title="Sinkronkan pemetaan petugas terbaru ke anomali yang belum terpetakan">
                <i class="fa-solid fa-arrows-rotate fs-6 me-2"></i>
                <span>Sinkron Alokasi Petugas</span>
            </button>
        </form>

        <!-- Tombol Download Respon Excel (Hanya aktif/jelas jika filter kegiatan & konfirmasi dipilih) -->
        <a href="{{ route('anomali.download-respon', request()->query()) }}" class="btn btn-pastel-success shadow-sm d-inline-flex align-items-center justify-content-center text-center px-3 py-2" title="Unduh respon konfirmasi dalam format spreadsheet (wajib pilih Kegiatan & Template Konfirmasi)">
            <i class="fa-solid fa-file-excel fs-6 me-2"></i>
            <span>Download Respon Excel</span>
        </a>
        <a href="{{ route('anomali.import') }}" class="btn btn-pastel-primary shadow-sm d-inline-flex align-items-center justify-content-center text-center px-3 py-2">
            <i class="fa-solid fa-file-import fs-6 me-2"></i>
            <span>Import Excel / CSV</span>
        </a>
        <a href="{{ route('anomali.create') }}" class="btn btn-light border text-secondary d-inline-flex align-items-center justify-content-center text-center px-3 py-2">
            <i class="fa-solid fa-plus fs-6 me-1"></i>
            <span>Tambah</span>
        </a>
    </div>
</div>

<!-- Filter Card Pastel & Modern -->
<div class="card card-clean p-4 mb-4" style="background: #ffffff; border: 1.5px solid var(--lk-border-card); border-radius: var(--lk-radius);">
    <div class="d-flex flex-wrap justify-content-between align-items-center pb-2 mb-3 border-bottom gap-2">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(79, 111, 246, 0.1); color: var(--lk-primary);">
                <i class="fa-solid fa-filter small"></i>
            </div>
            <span class="small fw-bold text-dark text-uppercase" style="letter-spacing: 0.05em;">Filter Anomali & Ekspor Respon</span>
        </div>
        <div>
            @if(!request()->filled('id_kegiatan') || !request()->filled('id_konfirmasi'))
                <span class="badge-pastel-warning px-3 py-1" style="font-size: 0.75rem;">
                    <i class="fa-solid fa-circle-info me-1"></i> Pilih Kegiatan & Template untuk unduh Excel
                </span>
            @else
                <span class="badge-pastel-success px-3 py-1" style="font-size: 0.75rem;">
                    <i class="fa-solid fa-circle-check me-1"></i> Siap Diunduh: Detail dipecah per kolom kegiatan
                </span>
            @endif
        </div>
    </div>

    <form action="{{ route('anomali.index') }}" method="GET" class="row g-3 align-items-end">
        <!-- Pertahankan pilihan kolom ekstrak -->
        @foreach($selectedColumns as $sc)
            <input type="hidden" name="columns[]" value="{{ $sc }}">
        @endforeach

        <div class="col-12 col-md-3">
            <label class="form-label small fw-bold text-secondary mb-1">Kegiatan <span class="text-danger">*</span></label>
            <div class="input-group filter-input-group">
                <span class="input-group-text bg-light border-end-0 text-muted">
                    <i class="fa-solid fa-calendar-check small"></i>
                </span>
                <select name="id_kegiatan" class="form-select" id="filter_kegiatan">
                    <option value="">-- Semua Kegiatan --</option>
                    @foreach($kegiatans as $k)
                        <option value="{{ $k->id }}" {{ request('id_kegiatan') == $k->id ? 'selected' : '' }}>
                            [{{ $k->kode }}] {{ $k->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <label class="form-label small fw-bold text-secondary mb-1">Template Konfirmasi <span class="text-danger">*</span></label>
            <div class="input-group filter-input-group">
                <span class="input-group-text bg-light border-end-0 text-muted">
                    <i class="fa-solid fa-circle-question small"></i>
                </span>
                <select name="id_konfirmasi" class="form-select" id="filter_konfirmasi">
                    <option value="">-- Semua Template --</option>
                    @foreach($konfirmasis as $konf)
                        <option value="{{ $konf->id }}" {{ request('id_konfirmasi') == $konf->id ? 'selected' : '' }}>
                            {{ $konf->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-12 col-md-2">
            <label class="form-label small fw-bold text-secondary mb-1">Status</label>
            <select name="status" class="form-select filter-input-control">
                <option value="">-- Semua Status --</option>
                <option value="belum" {{ request('status') === 'belum' ? 'selected' : '' }}>Belum Konfirmasi</option>
                <option value="sudah" {{ request('status') === 'sudah' ? 'selected' : '' }}>Sudah Konfirmasi</option>
            </select>
        </div>

        <div class="col-12 col-md-3">
            <label class="form-label small fw-bold text-secondary mb-1">Custom Search (Semua Variabel)</label>
            <div class="input-group filter-input-group">
                <span class="input-group-text bg-light border-end-0 text-muted">
                    <i class="fa-solid fa-magnifying-glass small"></i>
                </span>
                <input type="text" name="search" class="form-control border-start-0" 
                       placeholder="Cari kata kunci apapun..." 
                       value="{{ request('search') }}">
            </div>
        </div>

        <div class="col-12 col-md-1 d-flex gap-1">
            <button type="submit" class="btn btn-pastel-primary btn-filter-action flex-grow-1" title="Terapkan Filter & Pencarian">
                <i class="fa-solid fa-filter"></i>
            </button>
            @if(request()->has('id_kegiatan') || request()->has('id_konfirmasi') || request()->has('status') || request()->has('search'))
                <a href="{{ route('anomali.index') }}" class="btn btn-light border btn-filter-action px-2" title="Reset filter" style="min-width: 42px;">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Header Aksi & Atur Kolom Detail Ekstrak JSON -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div class="d-flex align-items-center gap-3">
        <div class="text-muted small">
            Menampilkan <strong>{{ $anomalis->total() }}</strong> data temuan anomali
        </div>
        <!-- Tombol Cepat Hapus Terpilih di Header -->
        <div id="quickDeleteAnomaliHeaderBtnWrapper" class="d-none">
            <button type="button" id="btnQuickDeleteAnomaliHeader" class="btn btn-sm btn-pastel-danger shadow-sm">
                <i class="fa-solid fa-trash-can me-1"></i> Hapus (<span class="selectedAnomaliCountText">0</span>) Anomali
            </button>
        </div>
    </div>

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
                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none small" id="selectAllAdminCols">Pilih Semua</button>
            </div>
            <form action="{{ route('anomali.index') }}" method="GET">
                @if(request()->filled('id_kegiatan')) <input type="hidden" name="id_kegiatan" value="{{ request('id_kegiatan') }}"> @endif
                @if(request()->filled('id_konfirmasi')) <input type="hidden" name="id_konfirmasi" value="{{ request('id_konfirmasi') }}"> @endif
                @if(request()->filled('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                @if(request()->filled('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif

                <div class="d-flex flex-column gap-2 mb-3">
                    @foreach($detailKeys as $dk)
                        <label class="form-check form-check-sm d-flex align-items-center gap-2 mb-0 cursor-pointer">
                            <input class="form-check-input admin-col-checkbox" type="checkbox" name="columns[]" value="{{ $dk }}" 
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

<!-- Modern, Lega, Pastel Anomali Table with Extracted JSON Columns (Wrap Text) -->
<div class="table-responsive-modern">
    <table class="table table-clean align-middle mb-0" style="table-layout: auto;">
        <thead>
            <tr>
                <th style="width: 40px;" class="ps-3 text-center">
                    <div class="form-check d-flex justify-content-center mb-0">
                        <input type="checkbox" id="checkAllAnomali" class="form-check-input" title="Pilih Semua Anomali di Halaman Ini">
                    </div>
                </th>
                <th style="width: 50px; white-space: nowrap;">No</th>
                <th style="min-width: 160px; max-width: 220px; white-space: normal;">Kegiatan & Konfirmasi</th>

                <!-- Kolom Hasil Ekstrak JSON Dinamis -->
                @foreach($selectedColumns as $sc)
                    <th class="text-uppercase" style="font-size: 0.73rem; letter-spacing: 0.05em; background: #f8fafc; color: #475569; min-width: 140px; max-width: 320px; white-space: normal; line-height: 1.35; word-break: break-word;">
                        {{ $sc }}
                    </th>
                @endforeach

                <th style="min-width: 160px; white-space: normal;">Petugas Terkait</th>
                <th style="min-width: 140px; white-space: nowrap;">Status</th>
                <th class="text-center pe-4" style="width: 140px; white-space: nowrap;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($anomalis as $index => $a)
            @php
                // Cari nomor HP dari detail JSON hanya jika petugas sudah terpetakan ($a->alokasi)
                $noHp = null;
                if ($a->alokasi && is_array($a->detail)) {
                    foreach ($a->detail as $k => $v) {
                        $keyLower = strtolower(str_replace([' ', '_', '-'], '', $k));
                        if (in_array($keyLower, ['nohp', 'hp', 'telepon', 'notelp', 'telp', 'wa', 'whatsapp', 'nomorhp'])) {
                            $cleanDigits = preg_replace('/[^0-9]/', '', (string)$v);
                            if (strlen($cleanDigits) >= 9 && strlen($cleanDigits) <= 15) {
                                $noHp = $cleanDigits;
                                break;
                            }
                        }
                    }
                }

                if ($noHp) {
                    if (str_starts_with($noHp, '0')) {
                        $noHp = '62' . substr($noHp, 1);
                    } elseif (str_starts_with($noHp, '8')) {
                        $noHp = '62' . $noHp;
                    }
                }

                $pesanWa = '';
                if ($noHp) {
                    $template = $a->kegiatan->template_wa ?? "Hai {nama_ppl}, mohon konfirmasi untuk anomali {nama_anomali} di kegiatan {nama_kegiatan}. Terima kasih.";
                    
                    $namaPpl = $a->alokasi->nama_ppl ?? ($a->detail['Nama PPL'] ?? ($a->detail['PPL'] ?? 'Petugas'));
                    $namaPml = $a->alokasi->nama_pml ?? ($a->detail['Nama PML'] ?? ($a->detail['PML'] ?? 'Pengawas'));
                    $namaAnomali = $a->konfirmasi->nama ?? 'Anomali';
                    $namaKegiatan = $a->kegiatan->nama ?? 'Survei';

                    $replacements = [
                        '{nama_ppl}' => $namaPpl,
                        '{nama_pml}' => $namaPml,
                        '{nama_anomali}' => $namaAnomali,
                        '{nama_kegiatan}' => $namaKegiatan,
                        '{kode_kegiatan}' => $a->kegiatan->kode ?? '',
                        '{status}' => $a->status,
                    ];

                    if (is_array($a->detail)) {
                        foreach ($a->detail as $dk => $dv) {
                            $replacements['{' . $dk . '}'] = (string)$dv;
                        }
                    }

                    $pesanWa = strtr($template, $replacements);
                    $waLink = "https://wa.me/{$noHp}?text=" . urlencode($pesanWa);
                }

                $searchKeyword = strtolower(request('search', ''));
            @endphp
            <tr>
                <td class="ps-3 text-center">
                    <div class="form-check d-flex justify-content-center mb-0">
                        <input type="checkbox" class="form-check-input check-anomali-item" value="{{ $a->id }}">
                    </div>
                </td>
                <td class="text-muted small" style="white-space: nowrap;">{{ $anomalis->firstItem() + $index }}</td>
                <td style="white-space: normal; min-width: 160px;">
                    <span class="badge-pastel-primary mb-1">{{ $a->kegiatan->kode ?? '-' }}</span>
                    <div class="fw-bold text-dark fs-6" style="word-break: break-word;">{{ $a->konfirmasi->nama ?? '-' }}</div>
                </td>

                <!-- Kolom Hasil Ekstrak JSON Dinamis (Wrap Text) -->
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

                <td style="white-space: normal; min-width: 160px;">
                    @if($a->alokasi)
                        <div class="small mb-1">
                            <div><span class="text-muted">PPL:</span> <strong class="text-dark">{{ $a->alokasi->nama_ppl }}</strong></div>
                            <div><span class="text-muted">PML:</span> <strong class="text-dark">{{ $a->alokasi->nama_pml }}</strong></div>
                        </div>
                    @else
                        <span class="badge-pastel-neutral mb-1">Belum terpetakan</span>
                    @endif
                </td>
                <td style="white-space: nowrap;">
                    @if($a->status === 'sesuai')
                        <span class="badge-pastel-success"><i class="fa-solid fa-check-double me-1"></i> Sesuai</span>
                    @elseif($a->status === 'tidak_sesuai')
                        <span class="badge-pastel-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> Perlu Revisi</span>
                    @elseif($a->status === 'dikonfirmasi')
                        <span class="badge-pastel-primary"><i class="fa-solid fa-check me-1"></i> Dikonfirmasi</span>
                    @else
                        <span class="badge-pastel-warning"><i class="fa-solid fa-clock me-1"></i> Menunggu</span>
                    @endif
                    @if($a->petugas_pengonfirmasi)
                        <div class="small text-muted mt-1"><i class="fa-solid fa-user-check me-1"></i> {{ $a->petugas_pengonfirmasi }}</div>
                    @endif
                </td>
                <td class="text-center pe-4" style="white-space: nowrap;">
                    <div class="d-inline-flex gap-1">
                        @if($noHp)
                            <a href="{{ $waLink }}" target="_blank" class="btn-table-action text-success" title="Chat WhatsApp">
                                <i class="fa-brands fa-whatsapp fs-5"></i>
                            </a>
                        @endif
                        <a href="{{ route('anomali.show', $a) }}" class="btn-table-action" title="Lihat Detail & Jawaban">
                            <i class="fa-solid fa-eye text-primary"></i>
                        </a>
                        <form action="{{ route('anomali.destroy', $a) }}" method="POST" onsubmit="return confirmPastelAction(this, { title: 'Hapus Data Anomali', message: 'Hapus data anomali #{{ $a->id }} ini dari sistem?' })" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-table-action text-danger" title="Hapus Anomali">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ 6 + count($selectedColumns) }}" class="text-center py-5 text-muted">
                    <div class="py-4">
                        <i class="fa-solid fa-file-circle-check fs-1 text-primary opacity-50 mb-3 d-block"></i>
                        <h6 class="fw-bold text-dark">Belum Ada Data Anomali</h6>
                        <p class="small text-muted mb-0">Silakan import data dari file spreadsheet anomali.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($anomalis->hasPages())
    <div class="p-3 border-top d-flex justify-content-end bg-white">
        {{ $anomalis->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

<!-- Floating Action Bar saat Checklist Anomali Terpilih (Kotak, Pastel, Kalem) -->
<div id="bulkAnomaliActionBar" class="bulk-action-bar">
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-primary-subtle text-primary border px-2 py-1" style="border-radius: 4px;"><span class="selectedAnomaliCountText">0</span></span>
        <span class="small fw-bold text-dark">Anomali Dipilih</span>
    </div>
    <div class="vr bg-secondary opacity-25" style="height: 24px;"></div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" id="btnCancelAnomaliChecklist" class="btn btn-sm btn-light border px-3 py-1 text-secondary fw-semibold" style="border-radius: 4px;">
            Batal
        </button>
        <button type="button" id="btnExecuteBulkDeleteAnomali" class="btn btn-sm btn-pastel-danger px-3 py-1 fw-bold shadow-sm" style="border-radius: 4px;">
            <i class="fa-solid fa-trash-can me-1"></i> Hapus Checklist
        </button>
    </div>
</div>

<!-- Modal Dialog Konfirmasi Khusus dengan Kode Acak (Kotak, Pastel, Kalem) -->
<div class="modal fade" id="modalConfirmCodeAnomali" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 4px; overflow: hidden; animation: popUpModal 0.28s cubic-bezier(0.34, 1.56, 0.64, 1); background: #ffffff;">
            <div class="p-4 text-center">
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; border-radius: 4px; background: rgba(239, 68, 68, 0.1); color: #ef4444; font-size: 1.35rem;">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h5 class="fw-bold brand-font text-dark mb-1">Konfirmasi Keamanan Hapus</h5>
                <p class="text-muted small mb-3" style="line-height: 1.5;">
                    Anda akan menghapus <strong id="textModalAnomaliCount" class="text-danger">0</strong> data anomali. Masukkan kode verifikasi acak di bawah ini untuk melanjutkan:
                </p>

                <!-- Box Kode Acak Yang Perlu Diketik -->
                <div class="py-2 px-3 mb-3 bg-light border text-center font-monospace fw-bold fs-4 text-dark letter-spacing-2 select-none" 
                     id="boxRandomCode" 
                     style="border-radius: 4px; letter-spacing: 0.25em; background: #f8fafc; border: 1.5px dashed #cbd5e1 !important;">
                    ------
                </div>

                <!-- Input Konfirmasi Kode -->
                <div class="mb-3 text-start">
                    <label class="form-label small fw-bold text-secondary mb-1">Ketik Ulang Kode Di Atas:</label>
                    <input type="text" id="inputConfirmCode" class="form-control text-center font-monospace fw-bold text-uppercase" 
                           placeholder="Ketik kode di sini..." 
                           autocomplete="off" 
                           style="height: 42px; border-radius: 4px; letter-spacing: 0.15em; font-size: 1.1rem; border: 1.5px solid #cbd5e1;">
                    <div id="codeFeedbackError" class="small text-danger mt-1 d-none">
                        <i class="fa-solid fa-circle-exclamation me-1"></i> Kode yang dimasukkan tidak cocok!
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-center pt-2">
                    <button type="button" class="btn btn-light border px-4 py-2 text-secondary fw-semibold" style="border-radius: 4px;" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="button" id="btnSubmitDeleteWithCode" class="btn btn-danger px-4 py-2 fw-semibold shadow-sm" style="border-radius: 4px;">
                        <i class="fa-solid fa-trash-can me-1"></i> Verifikasi & Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Atur Kolom Checkbox All
        const selectAllBtn = document.getElementById('selectAllAdminCols');
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const checkboxes = document.querySelectorAll('.admin-col-checkbox');
                const allChecked = Array.from(checkboxes).every(c => c.checked);
                checkboxes.forEach(c => c.checked = !allChecked);
                this.textContent = allChecked ? 'Pilih Semua' : 'Batal Pilih Semua';
            });
        }

        // Checklist State & Management untuk Anomali
        let selectedAnomaliIds = new Set();
        let currentRandomCode = '';

        const checkAllAnomali = document.getElementById('checkAllAnomali');
        const anomaliCheckboxes = document.querySelectorAll('.check-anomali-item');
        const bulkActionBar = document.getElementById('bulkAnomaliActionBar');
        const headerBtnWrapper = document.getElementById('quickDeleteAnomaliHeaderBtnWrapper');
        const countTexts = document.querySelectorAll('.selectedAnomaliCountText');

        function updateAnomaliBulkBar() {
            const count = selectedAnomaliIds.size;
            countTexts.forEach(el => el.textContent = count);

            if (count > 0) {
                bulkActionBar.classList.add('show');
                if (headerBtnWrapper) headerBtnWrapper.classList.remove('d-none');
            } else {
                bulkActionBar.classList.remove('show');
                if (headerBtnWrapper) headerBtnWrapper.classList.add('d-none');
            }
        }

        if (checkAllAnomali) {
            checkAllAnomali.addEventListener('change', function() {
                const isChecked = this.checked;
                anomaliCheckboxes.forEach(cb => {
                    cb.checked = isChecked;
                    if (isChecked) {
                        selectedAnomaliIds.add(cb.value);
                    } else {
                        selectedAnomaliIds.delete(cb.value);
                    }
                });
                updateAnomaliBulkBar();
            });
        }

        anomaliCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                if (this.checked) {
                    selectedAnomaliIds.add(this.value);
                } else {
                    selectedAnomaliIds.delete(this.value);
                    if (checkAllAnomali) checkAllAnomali.checked = false;
                }
                const allSelected = Array.from(anomaliCheckboxes).every(c => c.checked);
                if (checkAllAnomali) checkAllAnomali.checked = allSelected;
                updateAnomaliBulkBar();
            });
        });

        // Batal Checklist
        const btnCancel = document.getElementById('btnCancelAnomaliChecklist');
        if (btnCancel) {
            btnCancel.addEventListener('click', function() {
                selectedAnomaliIds.clear();
                anomaliCheckboxes.forEach(c => c.checked = false);
                if (checkAllAnomali) checkAllAnomali.checked = false;
                updateAnomaliBulkBar();
            });
        }

        // Generate Random Code (6 digit Alfanumerik Bebas Ambigu)
        function generateRandomCode(length = 6) {
            const chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
            let res = '';
            for (let i = 0; i < length; i++) {
                res += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return res;
        }

        // Buka Modal Konfirmasi dengan Kode Acak
        function promptDeleteWithRandomCode() {
            if (selectedAnomaliIds.size === 0) return;

            currentRandomCode = generateRandomCode(6);
            document.getElementById('boxRandomCode').textContent = currentRandomCode;
            document.getElementById('textModalAnomaliCount').textContent = selectedAnomaliIds.size;
            
            const inputField = document.getElementById('inputConfirmCode');
            inputField.value = '';
            document.getElementById('codeFeedbackError').classList.add('d-none');

            const modalEl = document.getElementById('modalConfirmCodeAnomali');
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();

            setTimeout(() => inputField.focus(), 300);
        }

        const btnExecute = document.getElementById('btnExecuteBulkDeleteAnomali');
        if (btnExecute) {
            btnExecute.addEventListener('click', promptDeleteWithRandomCode);
        }

        const btnHeader = document.getElementById('btnQuickDeleteAnomaliHeader');
        if (btnHeader) {
            btnHeader.addEventListener('click', promptDeleteWithRandomCode);
        }

        // Eksekusi Submit Penghapusan dengan Verifikasi Kode
        const btnSubmitDelete = document.getElementById('btnSubmitDeleteWithCode');
        const inputConfirmCode = document.getElementById('inputConfirmCode');

        function doSubmitDelete() {
            const userInput = inputConfirmCode.value.trim().toUpperCase();
            if (userInput !== currentRandomCode) {
                document.getElementById('codeFeedbackError').classList.remove('d-none');
                inputConfirmCode.classList.add('is-invalid');
                inputConfirmCode.focus();
                return;
            }

            document.getElementById('codeFeedbackError').classList.add('d-none');
            inputConfirmCode.classList.remove('is-invalid');

            btnSubmitDelete.disabled = true;
            btnSubmitDelete.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Menghapus...';

            // Kirim request penghapusan via Ajax jQuery / Fetch standar dengan proteksi error message
            fetch("{{ route('anomali.bulk-delete') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: JSON.stringify({
                    _token: "{{ csrf_token() }}",
                    ids: Array.from(selectedAnomaliIds),
                    confirm_code: userInput,
                    expected_code: currentRandomCode
                })
            })
            .then(async res => {
                let json = {};
                try {
                    json = await res.json();
                } catch(e) {}

                if (!res.ok) {
                    throw new Error(json.message || 'Terjadi kesalahan (' + res.status + ') saat menghapus data.');
                }
                return json;
            })
            .then(data => {
                if (data.success) {
                    const modalEl = document.getElementById('modalConfirmCodeAnomali');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    location.reload();
                } else {
                    alert(data.message || 'Gagal menghapus data anomali.');
                    btnSubmitDelete.disabled = false;
                    btnSubmitDelete.innerHTML = '<i class="fa-solid fa-trash-can me-1"></i> Verifikasi & Hapus';
                }
            })
            .catch(err => {
                alert(err.message || 'Terjadi kesalahan jaringan.');
                btnSubmitDelete.disabled = false;
                btnSubmitDelete.innerHTML = '<i class="fa-solid fa-trash-can me-1"></i> Verifikasi & Hapus';
            });
        }

        if (btnSubmitDelete) {
            btnSubmitDelete.addEventListener('click', doSubmitDelete);
        }

        if (inputConfirmCode) {
            inputConfirmCode.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    doSubmitDelete();
                } else {
                    document.getElementById('codeFeedbackError').classList.add('d-none');
                    this.classList.remove('is-invalid');
                }
            });
        }
    });
</script>
@endpush
