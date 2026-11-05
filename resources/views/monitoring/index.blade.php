@extends('layouts.app')

@section('title', 'Rekapitulasi Monitoring Petugas & Template Konfirmasi')

@push('styles')
    <!-- DataTables Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <style>
        /* Card Metric Pastel */
        .metric-card {
            background: #ffffff;
            border-radius: var(--lk-radius);
            border: 1px solid var(--lk-border-card);
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            box-shadow: var(--lk-shadow-sm);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--lk-shadow-md);
        }
        .metric-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        /* Filter Select Pastel */
        .filter-select {
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            padding: 0.55rem 1rem;
            font-size: 0.88rem;
            color: #334155;
            background-color: #ffffff;
            transition: all 0.2s;
        }
        .filter-select:focus {
            border-color: var(--lk-primary);
            box-shadow: 0 0 0 3px rgba(79, 111, 246, 0.12);
            outline: none;
        }

        /* Tabel Rekap Multi-Header Pastel */
        .table-rekap-wrapper {
            background: #ffffff;
            border-radius: var(--lk-radius);
            border: 1px solid var(--lk-border-card);
            box-shadow: var(--lk-shadow-sm);
        }
        .table-rekap {
            table-layout: auto;
        }
        .table-rekap thead th {
            vertical-align: middle;
            text-align: center;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
            padding: 0.85rem 0.75rem;
            border: 1px solid #e2e8f0;
            white-space: normal; /* Izinkan teks panjang wrap ke bawah agar kolom lebih lega */
            word-break: break-word;
            line-height: 1.4;
        }
        .table-rekap thead tr.header-top th {
            background-color: #f8fafc;
            color: #475569;
        }
        .table-rekap thead tr.header-sub th {
            background-color: #f1f5f9;
            color: #64748b;
            font-size: 0.72rem;
            white-space: normal;
            word-break: break-word;
            line-height: 1.3;
        }
        .table-rekap thead th.th-question {
            background-color: #eff6ff !important;
            color: #1e40af !important;
            border-bottom: 2px solid #bfdbfe;
            min-width: 220px;
            max-width: 340px;
        }
        .table-rekap thead th.th-sub-question {
            background-color: #f8faff !important;
            color: #3b82f6 !important;
            min-width: 110px;
            max-width: 180px;
        }
        .table-rekap thead th.th-progress {
            background-color: #fefce8 !important;
            color: #854d0e !important;
            min-width: 130px;
        }
        .table-rekap tbody td {
            padding: 0.85rem 0.85rem;
            border: 1px solid #f1f5f9;
            font-size: 0.88rem;
            color: #334155;
            vertical-align: middle;
            white-space: normal;
            word-break: break-word;
        }
        .table-rekap tbody tr:hover td {
            background-color: #f8faff;
        }
        .table-rekap tfoot td {
            background-color: #f8fafc;
            font-weight: 700;
            border: 1px solid #e2e8f0;
            padding: 0.95rem 0.85rem;
            font-size: 0.88rem;
            color: #0f172a;
            white-space: normal;
        }

        /* DataTables Controls */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border-radius: var(--lk-radius-sm);
            border: 1.5px solid #e2e8f0;
            padding: 0.4rem 0.85rem;
            font-size: 0.85rem;
            outline: none;
        }
        .dataTables_wrapper .pagination .page-item .page-link {
            border-radius: 8px !important;
            margin: 0 3px;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-size: 0.85rem;
            padding: 0.45rem 0.85rem;
        }
        .dataTables_wrapper .pagination .page-item.active .page-link {
            background-color: var(--lk-primary);
            border-color: var(--lk-primary);
            color: #ffffff;
        }
    </style>
@endpush

@section('content')
<!-- Header Page -->
<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="h3 fw-bold mb-1">Rekap Monitoring Petugas & Jawaban Konfirmasi</h2>
        <p class="text-muted mb-0">Tabel terpadu rekapitulasi progres petugas, perbandingan belum konfirmasi, dan rekap isian tiap pertanyaan template.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <a href="{{ route('anomali.download-respon') }}" class="btn-pastel-primary d-inline-flex align-items-center text-decoration-none">
            <i class="fa-solid fa-file-excel me-2"></i> Download Respon Excel
        </a>
    </div>
</div>

<!-- Filter Panel Kalem -->
<div class="card-modern p-4 mb-4" style="background: #ffffff; border: 1px solid var(--lk-border-card);">
    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(79, 111, 246, 0.1); color: var(--lk-primary);">
                <i class="fa-solid fa-filter small"></i>
            </div>
            <span class="fw-bold text-dark fs-6">Filter Rekapitulasi</span>
        </div>
        <button type="button" id="btnResetFilter" class="btn btn-sm btn-link text-decoration-none text-muted p-0">
            <i class="fa-solid fa-rotate-left me-1"></i> Reset Filter
        </button>
    </div>

    <form id="filterForm" class="row g-3">
        <!-- 1. Filter Tahun -->
        <div class="col-12 col-md-3">
            <label class="form-label small fw-bold text-secondary mb-1">Tahun Kegiatan</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 12px 0 0 12px; border: 1.5px solid #e2e8f0; border-right: none;">
                    <i class="fa-regular fa-calendar small"></i>
                </span>
                <select name="tahun" id="filterTahun" class="form-select filter-select" style="border-radius: 0 12px 12px 0;">
                    <option value="">Semua Tahun</option>
                    @foreach($years as $yr)
                        <option value="{{ $yr }}" {{ (string)$yr === (string)date('Y') ? 'selected' : '' }}>Tahun {{ $yr }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- 2. Filter Kegiatan -->
        <div class="col-12 col-md-5">
            <label class="form-label small fw-bold text-secondary mb-1">Kegiatan</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 12px 0 0 12px; border: 1.5px solid #e2e8f0; border-right: none;">
                    <i class="fa-solid fa-calendar-check small"></i>
                </span>
                <select name="id_kegiatan" id="filterKegiatan" class="form-select filter-select" style="border-radius: 0 12px 12px 0;">
                    <option value="">Semua Kegiatan</option>
                    @foreach($kegiatans as $kg)
                        <option value="{{ $kg->id }}">[{{ $kg->kode }}] {{ $kg->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- 3. Filter Template Konfirmasi -->
        <div class="col-12 col-md-4">
            <label class="form-label small fw-bold text-secondary mb-1">Template Konfirmasi</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 12px 0 0 12px; border: 1.5px solid #e2e8f0; border-right: none;">
                    <i class="fa-solid fa-circle-question small"></i>
                </span>
                <select name="id_konfirmasi" id="filterKonfirmasi" class="form-select filter-select" style="border-radius: 0 12px 12px 0;">
                    @foreach($konfirmasis as $index => $kf)
                        <option value="{{ $kf->id }}" {{ $index === 0 ? 'selected' : '' }}>{{ $kf->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>
</div>

<!-- 4 Cards Stat Summary Pastel -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-icon-box" style="background: rgba(79, 111, 246, 0.12); color: var(--lk-primary);">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <div>
                <div class="text-muted small fw-semibold">Total Beban Anomali</div>
                <div class="h3 fw-bold mb-0 text-dark" id="statTotal">-</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-icon-box" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="text-muted small fw-semibold">Sudah Dikonfirmasi</div>
                <div class="h3 fw-bold mb-0 text-success" id="statSudah">-</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-icon-box" style="background: rgba(239, 68, 68, 0.12); color: #ef4444;">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <div>
                <div class="text-muted small fw-semibold">Belum Dikonfirmasi</div>
                <div class="h3 fw-bold mb-0 text-danger" id="statBelum">-</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-icon-box" style="background: rgba(139, 92, 246, 0.12); color: #8b5cf6;">
                <i class="fa-solid fa-chart-pie"></i>
            </div>
            <div>
                <div class="text-muted small fw-semibold">Tingkat Progres</div>
                <div class="h3 fw-bold mb-0 text-primary" id="statProgres">-</div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Rekap Terpadu 1 Halaman -->
<div class="table-rekap-wrapper p-3 p-md-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between pb-3 mb-3 border-bottom gap-2">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge-pastel-primary"><i class="fa-solid fa-table-list me-1"></i>Tabel Rekapitulasi</span>
                <h5 class="fw-bold text-dark mb-0 fs-6" id="titleActiveTemplate">Rekap Petugas & Isian Konfirmasi</h5>
            </div>
            <small class="text-muted">Menampilkan rekapitulasi beban anomali, status perbandingan belum konfirmasi, dan rekap respon per butir pertanyaan template.</small>
        </div>
    </div>

    <!-- Spinner Loading -->
    <div id="loadingTable" class="text-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
        <div class="text-muted small mt-2">Menyusun rekapitulasi data petugas dan template...</div>
    </div>

    <!-- Container Table Dinamis -->
    <div id="tableContainer" class="table-responsive d-none">
        <table id="mainRekapTable" class="table table-rekap w-100 align-middle">
            <thead id="mainTableHeader">
                <!-- Disuntikkan secara dinamis berdasarkan pertanyaan template -->
            </thead>
            <tbody id="mainTableBody">
                <!-- Baris data petugas -->
            </tbody>
            <tfoot id="mainTableFooter">
                <!-- Baris Total Keseluruhan -->
            </tfoot>
        </table>
    </div>
</div>
@endsection

@push('scripts')
    <!-- jQuery & DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            var dataTableInstance = null;

            function loadRekapData() {
                $('#loadingTable').removeClass('d-none');
                $('#tableContainer').addClass('d-none');

                if (dataTableInstance) {
                    dataTableInstance.destroy();
                    dataTableInstance = null;
                }

                var params = {
                    tahun: $('#filterTahun').val(),
                    id_kegiatan: $('#filterKegiatan').val(),
                    id_konfirmasi: $('#filterKonfirmasi').val()
                };

                $.ajax({
                    url: "{{ route('monitoring.rekap-data') }}",
                    data: params,
                    method: 'GET',
                    success: function(res) {
                        // 1. Update Kartu Metrik
                        $('#statTotal').text(res.summary.total_anomali.toLocaleString('id-ID'));
                        $('#statSudah').text(res.summary.sudah_konfirmasi.toLocaleString('id-ID'));
                        $('#statBelum').text(res.summary.belum_konfirmasi.toLocaleString('id-ID'));
                        $('#statProgres').text(res.summary.progres + '%');
                        $('#titleActiveTemplate').text('Rekap Template: ' + res.template.nama);

                        // 2. Bangun Header Tabel Rekap (Dua Baris: Baris Utama & Baris Sub Opsi)
                        var questions = res.template.columns || [];
                        var trTop = '<tr class="header-top">';
                        var trSub = '<tr class="header-sub">';

                        // Kolom Tetap: No, Petugas, Beban & Belum Konfirmasi, Progres
                        trTop += '<th rowspan="2" style="width: 45px;">No</th>';
                        trTop += '<th rowspan="2" style="min-width: 170px; max-width: 240px;">Nama Petugas (PPL / PML)</th>';
                        trTop += '<th rowspan="2" class="text-center" style="width: 70px;">Total</th>';
                        trTop += '<th rowspan="2" class="text-center" style="width: 80px; color: #166534; background: #f0fdf4;">Sudah</th>';
                        trTop += '<th rowspan="2" class="text-center" style="width: 80px; color: #991b1b; background: #fef2f2;">Belum</th>';
                        trTop += '<th rowspan="2" class="th-progress text-center" style="width: 130px;">Progres (%)</th>';

                        // Kolom Dinamis Pertanyaan Template
                        questions.forEach(function(q, qIdx) {
                            var colspan = q.sub_columns.length || 1;
                            trTop += '<th colspan="' + colspan + '" class="th-question">' +
                                     '<div class="text-wrap" style="line-height: 1.35;">[P' + (qIdx + 1) + '] ' + escapeHtml(q.teks) + '</div>' +
                                     '</th>';

                            q.sub_columns.forEach(function(sub) {
                                trSub += '<th class="th-sub-question text-center text-wrap" style="line-height: 1.3;" title="' + escapeHtml(sub.opt_label) + '">' +
                                         escapeHtml(sub.opt_label) +
                                         '</th>';
                            });
                        });

                        trTop += '</tr>';
                        trSub += '</tr>';

                        var theadHtml = trTop;
                        if (questions.length > 0) {
                            theadHtml += trSub;
                        }
                        $('#mainTableHeader').html(theadHtml);

                        // 3. Bangun Body Tabel (Baris per Petugas)
                        var tbodyHtml = '';
                        var rows = res.data || [];

                        if (rows.length === 0) {
                            var totalColSpan = 6;
                            questions.forEach(function(q) { totalColSpan += (q.sub_columns.length || 1); });
                            tbodyHtml = '<tr><td colspan="' + totalColSpan + '" class="text-center py-5 text-muted">' +
                                        '<i class="fa-regular fa-folder-open fs-2 mb-2 d-block"></i>Tidak ada data anomali untuk filter yang dipilih.' +
                                        '</td></tr>';
                        } else {
                            rows.forEach(function(row, index) {
                                var pct = row.progres || 0;
                                var barColor = pct >= 100 ? 'bg-success' : (pct >= 50 ? 'bg-primary' : (pct > 0 ? 'bg-warning' : 'bg-secondary'));

                                tbodyHtml += '<tr>';
                                tbodyHtml += '<td class="text-center text-muted fw-semibold">' + (index + 1) + '</td>';
                                
                                // Petugas
                                tbodyHtml += '<td>' +
                                             '<div class="fw-bold text-dark"><i class="fa-solid fa-user-check me-1 text-primary"></i>' + escapeHtml(row.nama_ppl) + '</div>' +
                                             (row.nama_pml && row.nama_pml !== '-' ? '<div class="small text-muted"><i class="fa-solid fa-user-tie me-1"></i>PML: ' + escapeHtml(row.nama_pml) + '</div>' : '') +
                                             '</td>';

                                // Total
                                tbodyHtml += '<td class="text-center fw-bold">' + row.total_anomali + '</td>';

                                // Sudah
                                tbodyHtml += '<td class="text-center fw-semibold text-success" style="background: rgba(16, 185, 129, 0.04);">' + row.sudah_konfirmasi + '</td>';

                                // Belum (Highlight merah jika > 0)
                                var belumBadge = row.belum_konfirmasi > 0
                                    ? '<span class="badge bg-danger text-white px-2 py-1">' + row.belum_konfirmasi + '</span>'
                                    : '<span class="text-success small fw-semibold"><i class="fa-solid fa-check me-1"></i>0</span>';
                                tbodyHtml += '<td class="text-center" style="background: rgba(239, 68, 68, 0.04);">' + belumBadge + '</td>';

                                // Progres Bar
                                tbodyHtml += '<td>' +
                                             '<div class="d-flex align-items-center gap-2">' +
                                                '<div class="progress flex-grow-1" style="height: 8px; border-radius: 4px; background-color: #f1f5f9;">' +
                                                    '<div class="progress-bar ' + barColor + '" style="width: ' + pct + '%;"></div>' +
                                                '</div>' +
                                                '<span class="small fw-bold text-dark" style="min-width: 38px;">' + pct + '%</span>' +
                                             '</div>' +
                                             '</td>';

                                // Nilai Rekap Isian Per Opsi Pertanyaan
                                questions.forEach(function(q) {
                                    q.sub_columns.forEach(function(sub) {
                                        var val = (row.jawaban && row.jawaban[sub.col_key] !== undefined) ? row.jawaban[sub.col_key] : 0;
                                        var displayVal = val > 0 ? '<strong class="text-primary">' + val + '</strong>' : '<span class="text-muted small">0</span>';
                                        tbodyHtml += '<td class="text-center">' + displayVal + '</td>';
                                    });
                                });

                                tbodyHtml += '</tr>';
                            });
                        }
                        $('#mainTableBody').html(tbodyHtml);

                        // 4. Bangun Footer Total Keseluruhan
                        if (rows.length > 0) {
                            var grand = res.summary;
                            var tfootHtml = '<tr>';
                            tfootHtml += '<td colspan="2" class="text-center fw-bold">TOTAL KESELURUHAN</td>';
                            tfootHtml += '<td class="text-center fw-bold fs-6">' + grand.total_anomali + '</td>';
                            tfootHtml += '<td class="text-center fw-bold text-success fs-6">' + grand.sudah_konfirmasi + '</td>';
                            tfootHtml += '<td class="text-center fw-bold text-danger fs-6">' + grand.belum_konfirmasi + '</td>';
                            tfootHtml += '<td class="text-center fw-bold text-primary fs-6">' + grand.progres + '%</td>';

                            questions.forEach(function(q) {
                                q.sub_columns.forEach(function(sub) {
                                    var gVal = (grand.grand_jawaban && grand.grand_jawaban[sub.col_key] !== undefined) ? grand.grand_jawaban[sub.col_key] : 0;
                                    tfootHtml += '<td class="text-center fw-bold text-dark">' + gVal + '</td>';
                                });
                            });
                            tfootHtml += '</tr>';
                            $('#mainTableFooter').html(tfootHtml);
                        } else {
                            $('#mainTableFooter').empty();
                        }

                        $('#loadingTable').addClass('d-none');
                        $('#tableContainer').removeClass('d-none');

                        // 5. Inisialisasi DataTables untuk tabel yang sudah terbentuk
                        if (rows.length > 0) {
                            dataTableInstance = $('#mainRekapTable').DataTable({
                                paging: true,
                                searching: true,
                                ordering: true,
                                order: [[4, 'desc']], // Urutkan default berdasarkan kolom 'Belum Konfirmasi' terbanyak (index 4)
                                info: true,
                                responsive: false,
                                language: {
                                    search: "Cari Petugas:",
                                    searchPlaceholder: "Ketik nama petugas...",
                                    lengthMenu: "Tampilkan _MENU_ data",
                                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ petugas",
                                    infoEmpty: "Menampilkan 0 petugas",
                                    paginate: {
                                        first: "<i class='fa-solid fa-angles-left'></i>",
                                        last: "<i class='fa-solid fa-angles-right'></i>",
                                        next: "<i class='fa-solid fa-angle-right'></i>",
                                        previous: "<i class='fa-solid fa-angle-left'></i>"
                                    }
                                },
                                dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3"lf>rt<"d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-3"ip>'
                            });
                        }
                    },
                    error: function() {
                        $('#loadingTable').addClass('d-none');
                        $('#tableContainer').html('<div class="alert alert-danger">Gagal memuat rekapitulasi data. Silakan coba kembali.</div>').removeClass('d-none');
                    }
                });
            }

            // Inisialisasi load pertama
            loadRekapData();

            // Refresh saat filter berubah
            $('#filterTahun, #filterKegiatan, #filterKonfirmasi').on('change', function() {
                loadRekapData();
            });

            // Tombol Reset Filter
            $('#btnResetFilter').on('click', function() {
                $('#filterTahun').val('');
                $('#filterKegiatan').val('');
                // Kembalikan ke opsi pertama template konfirmasi
                $('#filterKonfirmasi').val($('#filterKonfirmasi option:first').val());
                loadRekapData();
            });

            // Sanitasi HTML / XSS Prevention
            function escapeHtml(text) {
                if (!text) return '';
                return String(text)
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }
        });
    </script>
@endpush
