@extends('layouts.app')

@section('title', 'Buat Template Konfirmasi — LAKONI')

@section('content')
<div class="row justify-content-center py-2">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold brand-font text-dark mb-1">Buat Template Konfirmasi Baru</h3>
                <p class="text-muted small mb-0">Pertanyaan akan disimpan dalam format JSON dinamis</p>
            </div>
            <a href="{{ route('konfirmasi.index') }}" class="btn btn-sm btn-pastel-soft">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>

        <div class="card card-clean p-4 p-md-5">
            <form action="{{ route('konfirmasi.store') }}" method="POST">
                @csrf

                <!-- Nama Template -->
                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Nama Konfirmasi / Judul Induk <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control" placeholder="Contoh: Konfirmasi Anomali Produksi Padi" required value="{{ old('nama') }}">
                </div>

                <!-- Bagian Daftar Pertanyaan Dinamis (JSON) -->
                <div class="mb-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                        <div>
                            <label class="form-label fw-bold small text-uppercase text-secondary mb-0">Daftar Pertanyaan (JSON)</label>
                            <div class="text-muted small">Tentukan pertanyaan pilihan ganda atau teks bebas untuk dijawab petugas</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-pastel-soft" id="btn-add-question">
                            <i class="fa-solid fa-plus me-1 text-primary"></i> Tambah Pertanyaan
                        </button>
                    </div>

                    <div id="questions-container" class="d-flex flex-column gap-3">
                        <!-- Pertanyaan 1 -->
                        <div class="question-item p-4 rounded-3 border bg-white shadow-sm" style="border-color: #f1f5f9 !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge-pastel-primary fs-6 fw-bold">Pertanyaan #1</span>
                            </div>
                            <div class="mb-3">
                                <label class="small fw-bold text-secondary mb-1">Kalimat / String Pertanyaan <span class="text-danger">*</span></label>
                                <input type="text" name="questions[0][teks]" class="form-control" placeholder="Contoh: Apakah data luas panen telah sesuai dengan kondisi riil di lapangan?" required>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="small fw-bold text-secondary mb-1">Tipe Jawaban</label>
                                    <select name="questions[0][tipe]" class="form-select q-type-select">
                                        <option value="pilihan">Pilihan Jawaban (Multiple Choice)</option>
                                        <option value="terbuka">Jawaban Terbuka (Teks Bebas)</option>
                                    </select>
                                </div>
                                <div class="col-md-7 q-opsi-box">
                                    <label class="small fw-bold text-secondary mb-1">Pilihan Jawaban (Pisahkan dengan koma)</label>
                                    <input type="text" name="questions[0][opsi]" class="form-control" placeholder="Contoh: Sesuai / Valid, Perlu Revisi" value="Sesuai / Valid, Perlu Revisi">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pengaturan Nilai Kriteria Sesuai -->
                <div class="row g-3 mb-4 p-3 rounded-3 bg-light border">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase text-secondary mb-1">Index Opsi Yang Dianggap "Sesuai"</label>
                        <input type="number" name="sesuai" class="form-control" placeholder="0 untuk opsi ke-1, 1 untuk opsi ke-2" value="0">
                        <div class="form-text small text-muted">Jika memilih opsi ini pada pertanyaan #1, status anomali otomatis menjadi 'Sesuai'.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase text-secondary mb-1">Index Pilihan Bawaan (Default)</label>
                        <input type="number" name="jawaban_default" class="form-control" placeholder="0">
                    </div>
                </div>

                <!-- Action Buttons: Simpan & Batal Pastel -->
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="{{ route('konfirmasi.index') }}" class="btn btn-light border px-4 py-2 text-secondary">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-pastel-primary px-4 py-2 fw-semibold">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let qIndex = 1;
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
                    <input type="text" name="questions[${qIndex}][opsi]" class="form-control" placeholder="Contoh: Ya, Tidak">
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

    document.querySelectorAll('.q-type-select').forEach(sel => {
        sel.addEventListener('change', function(e) {
            const parent = e.target.closest('.question-item');
            parent.querySelector('.q-opsi-box').style.display = e.target.value === 'terbuka' ? 'none' : 'block';
        });
    });
</script>
@endpush
@endsection
