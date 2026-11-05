@extends('layouts.app')

@section('title', 'Edit Template Konfirmasi — LAKONI')

@section('content')
<div class="row justify-content-center py-2">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold brand-font text-dark mb-1">Edit Template Konfirmasi</h3>
                <p class="text-muted small mb-0">Perbarui struktur pertanyaan berformat JSON dinamis</p>
            </div>
            <a href="{{ route('konfirmasi.index') }}" class="btn btn-sm btn-pastel-soft">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>

        <div class="card card-clean p-4 p-md-5">
            <form action="{{ route('konfirmasi.update', $konfirmasi) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Nama Template -->
                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Nama Konfirmasi / Judul Induk <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control" required value="{{ old('nama', $konfirmasi->nama) }}">
                </div>

                <!-- Bagian Daftar Pertanyaan Dinamis (JSON) -->
                <div class="mb-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                        <div>
                            <label class="form-label fw-bold small text-uppercase text-secondary mb-0">Daftar Pertanyaan (JSON)</label>
                            <div class="text-muted small">Susun pertanyaan pilihan ganda atau jawaban terbuka untuk petugas lapangan</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-pastel-soft" id="btn-add-question">
                            <i class="fa-solid fa-plus me-1 text-primary"></i> Tambah Pertanyaan
                        </button>
                    </div>

                    <div id="questions-container" class="d-flex flex-column gap-3">
                        @php
                            $questions = is_array($konfirmasi->pertanyaan) ? $konfirmasi->pertanyaan : [];
                        @endphp
                        @foreach($questions as $i => $q)
                        <div class="question-item p-4 rounded-3 border bg-white shadow-sm" style="border-color: #f1f5f9 !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge-pastel-primary fs-6 fw-bold">Pertanyaan #{{ $i + 1 }}</span>
                                @if($i > 0)
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-q border-0 text-danger bg-danger bg-opacity-10 py-1 px-3 rounded-pill">
                                        <i class="fa-solid fa-trash-can me-1"></i> Hapus
                                    </button>
                                @endif
                            </div>
                            <div class="mb-3">
                                <label class="small fw-bold text-secondary mb-1">Kalimat / String Pertanyaan <span class="text-danger">*</span></label>
                                <input type="text" name="questions[{{ $i }}][teks]" class="form-control" required value="{{ $q['teks'] ?? '' }}" placeholder="Ketik pertanyaan...">
                            </div>
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="small fw-bold text-secondary mb-1">Tipe Jawaban</label>
                                    <select name="questions[{{ $i }}][tipe]" class="form-select q-type-select">
                                        <option value="pilihan" {{ ($q['tipe'] ?? '') === 'pilihan' ? 'selected' : '' }}>Pilihan Jawaban (Multiple Choice)</option>
                                        <option value="terbuka" {{ ($q['tipe'] ?? '') === 'terbuka' ? 'selected' : '' }}>Jawaban Terbuka (Teks Bebas)</option>
                                    </select>
                                </div>
                                <div class="col-md-7 q-opsi-box" style="{{ ($q['tipe'] ?? '') === 'terbuka' ? 'display: none;' : '' }}">
                                    <label class="small fw-bold text-secondary mb-1">Pilihan Jawaban (Pisahkan dengan koma)</label>
                                    <input type="text" name="questions[{{ $i }}][opsi]" class="form-control" value="{{ isset($q['opsi']) && is_array($q['opsi']) ? implode(', ', $q['opsi']) : '' }}" placeholder="Contoh: Sesuai, Tidak Sesuai">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Pengaturan Nilai Kriteria Sesuai -->
                <div class="row g-3 mb-4 p-3 rounded-3 bg-light border">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase text-secondary mb-1">Index Opsi Yang Dianggap "Sesuai"</label>
                        <input type="number" name="sesuai" class="form-control" value="{{ old('sesuai', $konfirmasi->sesuai) }}">
                        <div class="form-text small text-muted">Misal <code>0</code> jika opsi pilihan pertama berarti data valid/sesuai.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase text-secondary mb-1">Index Pilihan Bawaan (Default)</label>
                        <input type="number" name="jawaban_default" class="form-control" value="{{ old('jawaban_default', $konfirmasi->jawaban) }}">
                        <div class="form-text small text-muted">Opsional, index default tercentang saat petugas membuka form.</div>
                    </div>
                </div>

                <!-- Action Buttons: Simpan & Batal Pastel -->
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="{{ route('konfirmasi.index') }}" class="btn btn-light border px-4 py-2 text-secondary">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-pastel-primary px-4 py-2 fw-semibold">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let qIndex = {{ count($questions) }};
    document.getElementById('btn-add-question').addEventListener('click', function() {
        const container = document.getElementById('questions-container');
        const div = document.createElement('div');
        div.className = 'question-item p-4 rounded-3 border bg-white shadow-sm';
        div.style.borderColor = '#f1f5f9';
        div.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="badge-pastel-primary fs-6 fw-bold">Pertanyaan #${qIndex + 1}</span>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-q border-0 text-danger bg-danger bg-opacity-10 py-1 px-3 rounded-pill">
                    <i class="fa-solid fa-trash-can me-1"></i> Hapus
                </button>
            </div>
            <div class="mb-3">
                <label class="small fw-bold text-secondary mb-1">Kalimat / String Pertanyaan <span class="text-danger">*</span></label>
                <input type="text" name="questions[${qIndex}][teks]" class="form-control" placeholder="Ketik pertanyaan konfirmasi..." required>
            </div>
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="small fw-bold text-secondary mb-1">Tipe Jawaban</label>
                    <select name="questions[${qIndex}][tipe]" class="form-select q-type-select">
                        <option value="pilihan">Pilihan Jawaban (Multiple Choice)</option>
                        <option value="terbuka">Jawaban Terbuka (Teks Bebas)</option>
                    </select>
                </div>
                <div class="col-md-7 q-opsi-box">
                    <label class="small fw-bold text-secondary mb-1">Pilihan Jawaban (Pisahkan dengan koma)</label>
                    <input type="text" name="questions[${qIndex}][opsi]" class="form-control" placeholder="Contoh: Sesuai, Perlu Revisi">
                </div>
            </div>
        `;
        container.appendChild(div);
        qIndex++;

        div.querySelector('.btn-remove-q').addEventListener('click', function() { div.remove(); });
        div.querySelector('.q-type-select').addEventListener('change', function(e) {
            div.querySelector('.q-opsi-box').style.display = e.target.value === 'terbuka' ? 'none' : 'block';
        });
    });

    document.querySelectorAll('.btn-remove-q').forEach(btn => {
        btn.addEventListener('click', function() {
            btn.closest('.question-item').remove();
        });
    });

    document.querySelectorAll('.q-type-select').forEach(sel => {
        sel.addEventListener('change', function(e) {
            const parent = e.target.closest('.question-item');
            parent.querySelector('.q-opsi-box').style.display = e.target.value === 'terbuka' ? 'none' : 'block';
        });
    });
</script>
@endpush
@endsection
