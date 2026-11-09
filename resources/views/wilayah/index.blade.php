@extends('layouts.app')

@section('title', 'Master Wilayah — LAKONI')

@push('styles')
    <!-- DataTables Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <style>
        /* Pastel Stat Cards */
        .stat-card-wilayah {
            background: #ffffff;
            border-radius: var(--lk-radius);
            border: 1px solid var(--lk-border-card);
            padding: 1.15rem 1.4rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: var(--lk-shadow-sm);
            transition: all 0.2s ease;
        }
        .stat-card-wilayah:hover {
            transform: translateY(-2px);
            box-shadow: var(--lk-shadow-md);
        }
        .stat-icon-wilayah {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        /* Filter Tab Button Pastel */
        .filter-wilayah-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.55rem 1rem;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            border: 1.5px solid #e2e8f0;
            background-color: #ffffff;
            color: #64748b;
            cursor: pointer;
        }
        .filter-wilayah-btn:hover {
            background-color: #f8fafc;
            border-color: #cbd5e1;
            color: #334155;
            transform: translateY(-1px);
        }
        .filter-wilayah-btn.active {
            background: #4f6ff6 !important;
            border-color: #4f6ff6 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(79, 111, 246, 0.25);
        }
        .filter-wilayah-btn .badge-pill-count {
            padding: 0.18rem 0.5rem;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 700;
            background: rgba(148, 163, 184, 0.18);
            color: inherit;
        }
        .filter-wilayah-btn.active .badge-pill-count {
            background: rgba(255, 255, 255, 0.28) !important;
            color: #ffffff !important;
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
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 10px;
            border: 1.5px solid #cbd5e1;
            padding: 0.45rem 0.85rem;
            font-size: 0.875rem;
            outline: none;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--lk-primary);
            box-shadow: 0 0 0 3px rgba(79, 111, 246, 0.12);
        }
    </style>
@endpush

@section('content')
<!-- Header Page -->
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold brand-font text-dark mb-1">Master Wilayah</h3>
        <p class="text-muted mb-0">Kelola master wilayah survei BPS: Kecamatan, Desa, SLS, atau NKS</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end gap-2">
        <a href="{{ route('wilayah.create') }}" class="btn btn-pastel-primary shadow-sm px-3 py-2">
            <i class="fa-solid fa-plus me-1"></i> Tambah Wilayah
        </a>
    </div>
</div>

<!-- Statistik Ringkas Wilayah Pastel -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card-wilayah">
            <div class="stat-icon-wilayah bg-primary-subtle text-primary">
                <i class="fa-solid fa-map-location-dot"></i>
            </div>
            <div>
                <span class="text-muted small fw-medium d-block">Total Wilayah</span>
                <h4 class="fw-bold mb-0 text-dark">{{ number_format($totalWilayah) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card-wilayah">
            <div class="stat-icon-wilayah bg-success-subtle text-success">
                <i class="fa-solid fa-tree"></i>
            </div>
            <div>
                <span class="text-muted small fw-medium d-block">Desa / Kelurahan</span>
                <h4 class="fw-bold mb-0 text-dark">{{ number_format($countDesa) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card-wilayah">
            <div class="stat-icon-wilayah bg-info-subtle text-info">
                <i class="fa-solid fa-map-pin"></i>
            </div>
            <div>
                <span class="text-muted small fw-medium d-block">SLS</span>
                <h4 class="fw-bold mb-0 text-dark">{{ number_format($countSls) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card-wilayah">
            <div class="stat-icon-wilayah bg-warning-subtle text-warning">
                <i class="fa-solid fa-landmark"></i>
            </div>
            <div>
                <span class="text-muted small fw-medium d-block">Kecamatan & NKS</span>
                <h4 class="fw-bold mb-0 text-dark">{{ number_format($countKecamatan + $countNks) }}</h4>
            </div>
        </div>
    </div>
</div>

<!-- Filter Tabs Tipe Wilayah Pastel & Kalem -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div class="d-flex flex-wrap gap-2" id="filterTipeContainer">
        <button type="button" class="filter-wilayah-btn active" data-tipe="">
            <i class="fa-solid fa-layer-group"></i>
            <span>Semua Tipe</span>
            <span class="badge-pill-count">{{ $totalWilayah }}</span>
        </button>
        <button type="button" class="filter-wilayah-btn" data-tipe="desa">
            <i class="fa-solid fa-tree text-success"></i>
            <span>Desa / Kelurahan</span>
            <span class="badge-pill-count">{{ $countDesa }}</span>
        </button>
        <button type="button" class="filter-wilayah-btn" data-tipe="sls">
            <i class="fa-solid fa-map-pin text-primary"></i>
            <span>SLS</span>
            <span class="badge-pill-count">{{ $countSls }}</span>
        </button>
        <button type="button" class="filter-wilayah-btn" data-tipe="kecamatan">
            <i class="fa-solid fa-landmark text-warning"></i>
            <span>Kecamatan</span>
            <span class="badge-pill-count">{{ $countKecamatan }}</span>
        </button>
        <button type="button" class="filter-wilayah-btn" data-tipe="nks">
            <i class="fa-solid fa-hashtag text-purple"></i>
            <span>NKS</span>
            <span class="badge-pill-count">{{ $countNks }}</span>
        </button>
    </div>

    <!-- Tombol Hapus Terpilih Cepat di Header -->
    <div id="quickDeleteHeaderBtnWrapper" class="d-none">
        <button type="button" id="btnQuickDeleteHeader" class="btn btn-sm btn-pastel-danger shadow-sm">
            <i class="fa-solid fa-trash-can me-1"></i> Hapus (<span class="selectedCountText">0</span>) Terpilih
        </button>
    </div>
</div>

<!-- Card Yajra DataTable Wilayah Lega & Bersih -->
<div class="card card-clean p-0 overflow-hidden">
    <div class="p-3 bg-white border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-table text-primary fs-5"></i>
            <span class="fw-bold text-dark fs-6">Data Master Wilayah Administrasi</span>
        </div>
        <div class="text-muted small">
            Dikelola dengan server-side rendering Yajra
        </div>
    </div>

    <div class="table-responsive p-3">
        <table id="tableWilayahYajra" class="table table-clean align-middle mb-0 w-100">
            <thead>
                <tr>
                    <th style="width: 40px;" class="text-center">
                        <div class="form-check d-flex justify-content-center mb-0">
                            <input type="checkbox" id="checkAllWilayah" class="form-check-input" title="Pilih Semua di Halaman Ini">
                        </div>
                    </th>
                    <th style="width: 50px;" class="text-center">No</th>
                    <th>Nama Wilayah</th>
                    <!-- <th>Kode Wilayah</th> -->
                    <th>Tipe Wilayah</th>
                    <th>Penggunaan Alokasi</th>
                    <th style="width: 120px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data dimuat oleh Yajra DataTables via AJAX -->
            </tbody>
        </table>
    </div>
</div>

<!-- Floating Action Bar saat Checklist Terpilih (Desain Kotak, Pastel, Kalem) -->
<div id="bulkActionBar" class="bulk-action-bar">
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-primary-subtle text-primary border px-2 py-1" style="border-radius: 4px;"><span class="selectedCountText">0</span></span>
        <span class="small fw-bold text-dark">Wilayah Dipilih</span>
    </div>
    <div class="vr bg-secondary opacity-25" style="height: 24px;"></div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" id="btnCancelChecklist" class="btn btn-sm btn-light border px-3 py-1 text-secondary fw-semibold" style="border-radius: 4px;">
            Batal
        </button>
        <button type="button" id="btnExecuteBulkDelete" class="btn btn-sm btn-pastel-danger px-3 py-1 fw-bold shadow-sm" style="border-radius: 4px;">
            <i class="fa-solid fa-trash-can me-1"></i> Hapus Checklist
        </button>
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
        let selectedTipe = '';
        let selectedIds = new Set();

        const table = $('#tableWilayahYajra').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('wilayah.index') }}",
                data: function(d) {
                    d.tipe = selectedTipe;
                }
            },
            columns: [
                { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'text-center' },
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center text-muted small' },
                { 
                    data: 'nama', 
                    name: 'nama',
                    render: function(data, type, row) {
                        return '<span class="fw-bold text-dark fs-6">' + (data || '-') + '</span>';
                    }
                },
                // { 
                //     data: 'kode', 
                //     name: 'kode',
                //     render: function(data, type, row) {
                //         return data ? '<span class="font-monospace text-secondary small bg-light px-2 py-1 rounded border">' + data + '</span>' : '<span class="text-muted small">-</span>';
                //     }
                // },
                { data: 'tipe_badge', name: 'tipe', orderable: true, searchable: true },
                { data: 'alokasi_count', name: 'alokasi_count', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[2, 'asc']], // Urutkan berdasarkan Nama Wilayah secara default
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Cari nama atau kode wilayah...",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ wilayah",
                infoEmpty: "Menampilkan 0 data",
                infoFiltered: "(disaring dari _MAX_ total wilayah)",
                zeroRecords: "<div class='py-4 text-center text-muted'><i class='fa-solid fa-map-location-dot fs-2 text-primary opacity-50 mb-2 d-block'></i>Tidak ditemukan wilayah yang sesuai</div>",
                processing: '<div class="py-2 text-primary"><i class="fa-solid fa-spinner fa-spin me-2"></i>Memuat data wilayah...</div>',
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

        // Filter Tipe Wilayah Tab Click
        $('#filterTipeContainer .filter-wilayah-btn').on('click', function(e) {
            e.preventDefault();
            $('#filterTipeContainer .filter-wilayah-btn').removeClass('active');
            $(this).addClass('active');
            selectedTipe = $(this).data('tipe');
            table.draw();
        });

        // Check All di Halaman Aktif
        $('#checkAllWilayah').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('.check-wilayah-item').each(function() {
                const id = $(this).val();
                $(this).prop('checked', isChecked);
                if (isChecked) {
                    selectedIds.add(id);
                } else {
                    selectedIds.delete(id);
                }
            });
            updateBulkBar();
        });

        // Checklist Single Item
        $(document).on('change', '.check-wilayah-item', function() {
            const id = $(this).val();
            if ($(this).is(':checked')) {
                selectedIds.add(id);
            } else {
                selectedIds.delete(id);
                $('#checkAllWilayah').prop('checked', false);
            }
            updateBulkBar();
        });

        function syncCheckboxes() {
            let allChecked = true;
            let hasItems = false;
            $('.check-wilayah-item').each(function() {
                hasItems = true;
                const id = $(this).val();
                if (selectedIds.has(id)) {
                    $(this).prop('checked', true);
                } else {
                    $(this).prop('checked', false);
                    allChecked = false;
                }
            });
            $('#checkAllWilayah').prop('checked', hasItems && allChecked);
        }

        function updateBulkBar() {
            const count = selectedIds.size;
            $('.selectedCountText').text(count);
            if (count > 0) {
                $('#bulkActionBar').addClass('show');
                $('#quickDeleteHeaderBtnWrapper').removeClass('d-none');
            } else {
                $('#bulkActionBar').removeClass('show');
                $('#quickDeleteHeaderBtnWrapper').addClass('d-none');
            }
        }

        // Batal Checklist
        $('#btnCancelChecklist').on('click', function() {
            selectedIds.clear();
            $('.check-wilayah-item, #checkAllWilayah').prop('checked', false);
            updateBulkBar();
        });

        // Eksekusi Hapus Masal Checklist
        function triggerBulkDelete() {
            const idsArray = Array.from(selectedIds);
            if (idsArray.length === 0) return;

            showPastelDialog({
                title: 'Hapus Master Wilayah Terpilih',
                message: `Apakah Anda yakin ingin menghapus <strong>${idsArray.length}</strong> master wilayah yang dipilih?`,
                confirmText: 'Ya, Hapus Semua',
                confirmClass: 'btn-danger',
                onConfirm: function() {
                    $.ajax({
                        url: "{{ route('wilayah.bulk-delete') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            ids: idsArray
                        },
                        success: function(response) {
                            selectedIds.clear();
                            updateBulkBar();
                            table.ajax.reload(null, false);

                            showPastelDialog({
                                title: 'Berhasil',
                                message: response.message || 'Wilayah terpilih berhasil dihapus.',
                                confirmText: 'Tutup',
                                confirmClass: 'btn-pastel-primary',
                                iconClass: 'fa-solid fa-circle-check',
                                iconBg: 'rgba(16, 185, 129, 0.1)',
                                iconColor: '#10b981'
                            });
                        },
                        error: function(xhr) {
                            let errorMsg = 'Terjadi kesalahan saat menghapus data wilayah terpilih.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            showPastelDialog({
                                title: 'Tidak Dapat Dihapus',
                                message: errorMsg,
                                confirmText: 'Mengerti',
                                confirmClass: 'btn-pastel-primary',
                                iconClass: 'fa-solid fa-triangle-exclamation',
                                iconBg: 'rgba(239, 68, 68, 0.1)',
                                iconColor: '#ef4444'
                            });
                        }
                    });
                }
            });
        }

        $('#btnExecuteBulkDelete, #btnQuickDeleteHeader').on('click', function() {
            triggerBulkDelete();
        });
    });
    </script>
@endpush
