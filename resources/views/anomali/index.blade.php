@extends('layouts.app')

@section('title', 'Manajemen Anomali — LAKONI')

@push('styles')
    <!-- DataTables Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <style>
        .column-selector-menu {
            max-height: 340px;
            overflow-y: auto;
            min-width: 270px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
            border: 1px solid #e2e8f0;
        }
        .filter-input-control {
            height: 42px !important;
            border-radius: 12px !important;
            border: 1.5px solid #e2e8f0 !important;
            font-size: 0.88rem !important;
            background-color: #ffffff;
        }
        .filter-input-group .input-group-text {
            height: 42px !important;
            border-radius: 12px 0 0 12px !important;
            border: 1.5px solid #e2e8f0 !important;
            border-right: none !important;
            background-color: #f8fafc;
        }
        .filter-input-group .form-control,
        .filter-input-group .form-select {
            height: 42px !important;
            border-radius: 0 12px 12px 0 !important;
            border: 1.5px solid #e2e8f0 !important;
            font-size: 0.88rem !important;
            background-color: #ffffff;
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

        /* DataTables Custom Polish */
        .dataTables_wrapper .dataTables_paginate .paginate_button.current, 
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: var(--lk-primary) !important;
            border-color: var(--lk-primary) !important;
            color: #ffffff !important;
            border-radius: 8px !important;
        }
        .dataTables_wrapper .dataTables_length select {
            border-radius: 10px;
            border: 1.5px solid #cbd5e1;
            padding: 0.45rem 2rem 0.45rem 0.85rem;
            font-size: 0.875rem;
            outline: none;
            cursor: pointer;
            background-color: #ffffff;
        }
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 10px;
            border: 1.5px solid #cbd5e1;
            padding: 0.45rem 0.85rem;
            font-size: 0.875rem;
            outline: none;
            background-color: #ffffff;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--lk-primary);
            box-shadow: 0 0 0 3px rgba(79, 111, 246, 0.12);
        }
    </style>
@endpush

@section('content')
@php
    $hasSelectedKegiatan = request()->filled('id_kegiatan');
@endphp

<div class="row mb-4 align-items-center">
    <div class="col-md-5">
        <h3 class="fw-bold brand-font text-dark mb-1">Manajemen Anomali</h3>
        <p class="text-muted mb-0">Daftar temuan anomali data lapangan yang perlu dikonfirmasi</p>
    </div>
    <div class="col-md-7 text-md-end mt-3 mt-md-0 d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
        @if($hasSelectedKegiatan)
            <!-- Tombol Sinkron Alokasi Petugas ke Anomali -->
            <form action="{{ route('anomali.sync') }}" method="POST" class="d-inline">
                @csrf
                @if(request('id_kegiatan') !== 'all')
                    <input type="hidden" name="id_kegiatan" value="{{ request('id_kegiatan') }}">
                @endif
                <button type="submit" class="btn btn-pastel-warning shadow-sm d-inline-flex align-items-center justify-content-center text-center px-3 py-2" title="Sinkronkan pemetaan petugas terbaru ke anomali yang belum terpetakan">
                    <i class="fa-solid fa-arrows-rotate fs-6 me-2"></i>
                    <span>Sinkron Alokasi Petugas</span>
                </button>
            </form>

            <!-- Tombol Download Respon Excel (Hanya aktif/jelas jika filter kegiatan & konfirmasi dipilih) -->
            <a href="{{ route('anomali.download-respon', request()->query()) }}" id="btnDownloadResponExcel" class="btn btn-pastel-success shadow-sm d-inline-flex align-items-center justify-content-center text-center px-3 py-2" title="Unduh respon konfirmasi dalam format spreadsheet">
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
        @endif
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
            <span id="badgeInfoDownload" class="badge-pastel-warning px-3 py-1" style="font-size: 0.75rem;">
                <i class="fa-solid fa-circle-info me-1"></i> Pilih Kegiatan & Template untuk unduh Excel
            </span>
        </div>
    </div>

    <form id="filterAnomaliForm" class="row g-3 align-items-end">
        <div class="col-12 col-md-3">
            <label class="form-label small fw-bold text-secondary mb-1">Pilih Kegiatan <span class="text-danger">*</span></label>
            <div class="input-group filter-input-group">
                <span class="input-group-text">
                    <i class="fa-solid fa-calendar-check small"></i>
                </span>
                <select name="id_kegiatan" class="form-select" id="filter_kegiatan" required>
                    <option value="">-- Pilih Kegiatan Terlebih Dahulu --</option>
                    <option value="all" {{ request('id_kegiatan') === 'all' ? 'selected' : '' }}>-- Tampilkan Semua Kegiatan --</option>
                    @foreach($kegiatans as $k)
                        <option value="{{ $k->id }}" {{ (string)request('id_kegiatan') === (string)$k->id ? 'selected' : '' }}>
                            [{{ $k->kode }}] {{ $k->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <label class="form-label small fw-bold text-secondary mb-1">Template Konfirmasi</label>
            <div class="input-group filter-input-group">
                <span class="input-group-text">
                    <i class="fa-solid fa-circle-question small"></i>
                </span>
                <select name="id_konfirmasi" class="form-select" id="filter_konfirmasi" {{ !$hasSelectedKegiatan ? 'disabled' : '' }}>
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
            <select name="status" class="form-select filter-input-control" id="filter_status" {{ !$hasSelectedKegiatan ? 'disabled' : '' }}>
                <option value="">-- Semua Status --</option>
                <option value="belum" {{ request('status') === 'belum' ? 'selected' : '' }}>Belum Konfirmasi</option>
                <option value="sudah" {{ request('status') === 'sudah' ? 'selected' : '' }}>Sudah Konfirmasi</option>
            </select>
        </div>

        <div class="col-12 col-md-3">
            <label class="form-label small fw-bold text-secondary mb-1">Pencarian Variabel / Kata Kunci</label>
            <div class="input-group filter-input-group">
                <span class="input-group-text">
                    <i class="fa-solid fa-magnifying-glass small"></i>
                </span>
                <input type="text" id="filter_custom_search" class="form-control" 
                       placeholder="Cari kata kunci apapun..." 
                       value="{{ request('search') }}" {{ !$hasSelectedKegiatan ? 'disabled' : '' }}>
            </div>
        </div>

        <div class="col-12 col-md-1 d-flex gap-1">
            <button type="submit" class="btn btn-pastel-primary btn-filter-action flex-grow-1" title="Terapkan Filter" {{ !$hasSelectedKegiatan ? 'disabled' : '' }}>
                <i class="fa-solid fa-filter"></i>
            </button>
            @if($hasSelectedKegiatan)
                <button type="button" id="btnResetFilter" class="btn btn-light border btn-filter-action px-2" title="Reset filter" style="min-width: 42px;">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </button>
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
            Pilih salah satu kegiatan survei atau opsi <strong>Tampilkan Semua Kegiatan</strong> pada dropdown filter di atas untuk melihat tabel manajemen anomali dan membuka tombol aksi (import, download respon, tambah data).
        </p>
    </div>
@else
    <!-- Header Aksi & Atur Kolom Detail Ekstrak JSON -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div class="d-flex align-items-center gap-3">
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
                <form action="{{ route('anomali.index') }}" method="GET" id="columnSelectorForm">
                    <input type="hidden" name="id_kegiatan" value="{{ request('id_kegiatan') }}">
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

    <!-- Table Card Modern Yajra DataTables -->
    <div class="table-responsive-modern p-3">
        <table id="tableAnomaliYajra" class="table table-clean align-middle mb-0 w-100">
            <thead>
                <tr>
                    <th style="width: 40px;" class="ps-3 text-center">
                        <div class="form-check d-flex justify-content-center mb-0">
                            <input type="checkbox" id="checkAllAnomali" class="form-check-input" title="Pilih Semua Anomali">
                        </div>
                    </th>
                    <th style="width: 50px;" class="text-center">No</th>
                    <th style="min-width: 170px;">Kegiatan & Konfirmasi</th>

                    <!-- Kolom Hasil Ekstrak JSON Dinamis -->
                    @foreach($selectedColumns as $sc)
                        <th class="text-uppercase" style="font-size: 0.73rem; letter-spacing: 0.05em; background: #f8fafc; color: #475569; min-width: 140px; max-width: 320px; white-space: normal; line-height: 1.35; word-break: break-word;">
                            {{ $sc }}
                        </th>
                    @endforeach

                    <th style="min-width: 160px;">Petugas Terkait</th>
                    <th style="min-width: 130px;">Status</th>
                    <th class="text-center pe-3" style="width: 130px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <!-- Diisi Server-Side oleh Yajra DataTables -->
            </tbody>
        </table>
    </div>
@endif

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
    <!-- jQuery & DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <script>
    $(document).ready(function() {
        // Atur Kolom Checkbox All
        $('#selectAllAdminCols').on('click', function(e) {
            e.preventDefault();
            const checkboxes = $('.admin-col-checkbox');
            const allChecked = checkboxes.length === checkboxes.filter(':checked').length;
            checkboxes.prop('checked', !allChecked);
            $(this).text(allChecked ? 'Pilih Semua' : 'Batal Pilih Semua');
        });

        // Update indikator download respon excel
        function updateDownloadBadge() {
            const keg = $('#filter_kegiatan').val();
            const konf = $('#filter_konfirmasi').val();
            const badge = $('#badgeInfoDownload');
            if (keg && konf) {
                badge.removeClass('badge-pastel-warning').addClass('badge-pastel-success');
                badge.html('<i class="fa-solid fa-circle-check me-1"></i> Siap Diunduh: Detail dipecah per kolom kegiatan');
            } else {
                badge.removeClass('badge-pastel-success').addClass('badge-pastel-warning');
                badge.html('<i class="fa-solid fa-circle-info me-1"></i> Pilih Kegiatan & Template untuk unduh Excel');
            }
        }
        updateDownloadBadge();

        // Checklist State & Management untuk Anomali
        let selectedAnomaliIds = new Set();
        let currentRandomCode = '';

        function updateAnomaliBulkBar() {
            const count = selectedAnomaliIds.size;
            $('.selectedAnomaliCountText').text(count);

            if (count > 0) {
                $('#bulkAnomaliActionBar').addClass('show');
                $('#quickDeleteAnomaliHeaderBtnWrapper').removeClass('d-none');
            } else {
                $('#bulkAnomaliActionBar').removeClass('show');
                $('#quickDeleteAnomaliHeaderBtnWrapper').addClass('d-none');
            }
        }

        function syncCheckboxes() {
            $('.check-anomali-item').each(function() {
                const id = $(this).val();
                $(this).prop('checked', selectedAnomaliIds.has(id));
            });
            const totalOnPage = $('.check-anomali-item').length;
            const checkedOnPage = $('.check-anomali-item:checked').length;
            $('#checkAllAnomali').prop('checked', totalOnPage > 0 && totalOnPage === checkedOnPage);
        }

        // Inisialisasi DataTable Yajra dengan Server-Side & Atur Halaman (lengthMenu)
        const columnsConfig = [
            { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'text-center ps-3' },
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center text-muted small' },
            { data: 'kegiatan_info', name: 'kegiatan_info', orderable: false, searchable: false }
        ];

        // Tambahkan kolom ekstrak detail
        @foreach($selectedColumns as $sc)
            columnsConfig.push({
                data: '{{ "detail_" . md5($sc) }}',
                name: '{{ "detail_" . md5($sc) }}',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return '<span class="small text-dark" style="word-break: break-word; line-height: 1.45; display: block;">' + (data || '-') + '</span>';
                }
            });
        @endforeach

        columnsConfig.push(
            { data: 'petugas_info', name: 'petugas_info', orderable: false, searchable: false },
            { data: 'status_badge', name: 'status', orderable: true, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center pe-3' }
        );

        const table = $('#tableAnomaliYajra').DataTable({
            processing: true,
            serverSide: true,
            lengthMenu: [[10, 15, 25, 50, 100, 250], [10, 15, 25, 50, 100, 250]],
            pageLength: 15,
            stateSave: true, // Simpan halaman dan jumlah baris per halaman
            ajax: {
                url: "{{ route('anomali.index') }}",
                data: function(d) {
                    d.id_kegiatan = $('#filter_kegiatan').val();
                    d.id_konfirmasi = $('#filter_konfirmasi').val();
                    d.status = $('#filter_status').val();
                    d.custom_search = $('#filter_custom_search').val();
                }
            },
            columns: columnsConfig,
            order: [], // Default urutan server side latest id
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Cari cepat di tabel...",
                lengthMenu: "Tampilkan _MENU_ data per halaman",
                info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data anomali",
                infoEmpty: "Menampilkan 0 data",
                infoFiltered: "(disaring dari _MAX_ total anomali)",
                zeroRecords: "<div class='py-4 text-center text-muted'><i class='fa-solid fa-file-circle-check fs-2 text-primary opacity-50 mb-2 d-block'></i>Tidak ditemukan data anomali yang sesuai</div>",
                processing: '<div class="py-2 text-primary"><i class="fa-solid fa-spinner fa-spin me-2"></i>Memuat data anomali...</div>',
                paginate: {
                    first: '<i class="fa-solid fa-angles-left"></i>',
                    last: '<i class="fa-solid fa-angles-right"></i>',
                    next: '<i class="fa-solid fa-angle-right"></i>',
                    previous: '<i class="fa-solid fa-angle-left"></i>'
                }
            },
            drawCallback: function() {
                syncCheckboxes();
            }
        });

        // Filter Form Submit
        $('#filterAnomaliForm').on('submit', function(e) {
            e.preventDefault();
            const keg = $('#filter_kegiatan').val();
            if (!keg) {
                alert('Silakan pilih kegiatan terlebih dahulu.');
                return;
            }
            // Arahkan halaman dengan parameter kegiatan yang dipilih agar seluruh layout tersinkronisasi
            const params = new URLSearchParams({
                id_kegiatan: keg,
                id_konfirmasi: $('#filter_konfirmasi').val() || '',
                status: $('#filter_status').val() || '',
                search: $('#filter_custom_search').val() || ''
            });
            window.location.href = "{{ route('anomali.index') }}?" + params.toString();
        });

        // Trigger filter saat dropdown kegiatan berubah
        $('#filter_kegiatan').on('change', function() {
            const keg = $(this).val();
            if (keg) {
                window.location.href = "{{ route('anomali.index') }}?id_kegiatan=" + encodeURIComponent(keg);
            } else {
                window.location.href = "{{ route('anomali.index') }}";
            }
        });

        // Trigger filter saat dropdown konfirmasi atau status berubah
        $('#filter_konfirmasi, #filter_status').on('change', function() {
            updateDownloadBadge();
            if (typeof table !== 'undefined') table.draw();
        });

        // Reset Filter
        $('#btnResetFilter').on('click', function() {
            window.location.href = "{{ route('anomali.index') }}";
        });

        // Checklist All di Halaman Aktif
        $('#checkAllAnomali').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('.check-anomali-item').each(function() {
                const id = $(this).val();
                $(this).prop('checked', isChecked);
                if (isChecked) {
                    selectedAnomaliIds.add(id);
                } else {
                    selectedAnomaliIds.delete(id);
                }
            });
            updateAnomaliBulkBar();
        });

        // Checklist Single Item
        $(document).on('change', '.check-anomali-item', function() {
            const id = $(this).val();
            if ($(this).is(':checked')) {
                selectedAnomaliIds.add(id);
            } else {
                selectedAnomaliIds.delete(id);
                $('#checkAllAnomali').prop('checked', false);
            }
            const totalOnPage = $('.check-anomali-item').length;
            const checkedOnPage = $('.check-anomali-item:checked').length;
            $('#checkAllAnomali').prop('checked', totalOnPage > 0 && totalOnPage === checkedOnPage);
            updateAnomaliBulkBar();
        });

        // Batal Checklist
        $('#btnCancelAnomaliChecklist').on('click', function() {
            selectedAnomaliIds.clear();
            $('.check-anomali-item').prop('checked', false);
            $('#checkAllAnomali').prop('checked', false);
            updateAnomaliBulkBar();
        });

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
            $('#boxRandomCode').text(currentRandomCode);
            $('#textModalAnomaliCount').text(selectedAnomaliIds.size);
            
            const inputField = $('#inputConfirmCode');
            inputField.val('');
            $('#codeFeedbackError').addClass('d-none');
            inputField.removeClass('is-invalid');

            const modalEl = document.getElementById('modalConfirmCodeAnomali');
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();

            setTimeout(() => inputField.focus(), 300);
        }

        $('#btnExecuteBulkDeleteAnomali, #btnQuickDeleteAnomaliHeader').on('click', promptDeleteWithRandomCode);

        // Eksekusi Submit Penghapusan dengan Verifikasi Kode
        function doSubmitDelete() {
            const inputConfirmCode = $('#inputConfirmCode');
            const btnSubmitDelete = $('#btnSubmitDeleteWithCode');
            const userInput = inputConfirmCode.val().trim().toUpperCase();

            if (userInput !== currentRandomCode) {
                $('#codeFeedbackError').removeClass('d-none');
                inputConfirmCode.addClass('is-invalid');
                inputConfirmCode.focus();
                return;
            }

            $('#codeFeedbackError').addClass('d-none');
            inputConfirmCode.removeClass('is-invalid');

            btnSubmitDelete.prop('disabled', true);
            btnSubmitDelete.html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Menghapus...');

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
                try { json = await res.json(); } catch(e) {}
                if (!res.ok) throw new Error(json.message || 'Terjadi kesalahan (' + res.status + ') saat menghapus data.');
                return json;
            })
            .then(data => {
                if (data.success) {
                    const modalEl = document.getElementById('modalConfirmCodeAnomali');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    selectedAnomaliIds.clear();
                    updateAnomaliBulkBar();
                    table.draw(false); // Refresh data tanpa reload halaman
                } else {
                    alert(data.message || 'Gagal menghapus data anomali.');
                }
            })
            .catch(err => {
                alert(err.message || 'Terjadi kesalahan jaringan.');
            })
            .finally(() => {
                btnSubmitDelete.prop('disabled', false);
                btnSubmitDelete.html('<i class="fa-solid fa-trash-can me-1"></i> Verifikasi & Hapus');
            });
        }

        $('#btnSubmitDeleteWithCode').on('click', doSubmitDelete);

        $('#inputConfirmCode').on('keyup', function(e) {
            if (e.key === 'Enter') {
                doSubmitDelete();
            } else {
                $('#codeFeedbackError').addClass('d-none');
                $(this).removeClass('is-invalid');
            }
        });
    });
    </script>
@endpush
