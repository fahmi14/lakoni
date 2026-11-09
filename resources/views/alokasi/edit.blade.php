@extends('layouts.app')

@section('title', 'Edit Alokasi Petugas — LAKONI')

@section('content')
<div class="row justify-content-center py-2">
    <div class="col-12 col-md-8 col-lg-7">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold brand-font text-dark mb-1">Edit Alokasi Petugas</h3>
                <p class="text-muted small mb-0">Perbarui penugasan PPL, PML, atau wilayah tugas kegiatan</p>
            </div>
            <a href="{{ route('alokasi.index') }}" class="btn btn-sm btn-pastel-soft">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>

        <div class="card card-clean p-4 p-md-5">
            <form action="{{ route('alokasi.update', $alokasi) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Pilih Kegiatan -->
                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Pilih Kegiatan <span class="text-danger">*</span></label>
                    <select name="id_kegiatan" class="form-select" required>
                        @foreach($kegiatans as $k)
                            <option value="{{ $k->id }}" {{ old('id_kegiatan', $alokasi->id_kegiatan) == $k->id ? 'selected' : '' }}>
                                [{{ $k->kode }}] {{ $k->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Nama Petugas PPL & PML -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Nama Petugas PPL (Pencacah) <span class="text-danger">*</span></label>
                        <input type="text" name="nama_ppl" class="form-control" value="{{ old('nama_ppl', $alokasi->nama_ppl) }}" placeholder="Contoh: Ahmad Subarjo" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Nama Pengawas PML <span class="text-danger">*</span></label>
                        <input type="text" name="nama_pml" class="form-control" value="{{ old('nama_pml', $alokasi->nama_pml) }}" placeholder="Contoh: Siti Rahmawati" required>
                    </div>
                </div>

                <!-- Wilayah Tugas (Input Teks Bebas dengan Autocomplete & Pilihan Tipe Wilayah) -->
                <div class="row g-3 mb-4">
                    <div class="col-md-7 position-relative">
                        <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Wilayah Tugas (Input Teks / Autocomplete)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted">
                                <i class="fa-solid fa-map-location-dot"></i>
                            </span>
                            <input type="text" name="nama_wilayah" id="input_nama_wilayah" class="form-control border-start-0" 
                                   value="{{ old('nama_wilayah', $alokasi->wilayah->nama ?? '') }}" 
                                   placeholder="Ketik nama atau kode wilayah..." 
                                   autocomplete="off">
                        </div>
                        <!-- Suggestion Dropdown List -->
                        <div id="wilayah_suggest_box" class="list-group shadow-md position-absolute w-100 mt-1 d-none" 
                             style="z-index: 1050; max-height: 220px; overflow-y: auto; border-radius: 12px; border: 1.5px solid #cbd5e1;">
                        </div>
                        <div class="form-text small text-muted mt-1">
                            Pilih dari saran yang muncul atau ketik nama/kode wilayah baru secara bebas.
                        </div>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold small text-uppercase text-secondary mb-2">Tipe Wilayah</label>
                        @php
                            $currentTipe = old('tipe_wilayah', $alokasi->wilayah->tipe ?? 'sls');
                        @endphp
                        <select name="tipe_wilayah" id="select_tipe_wilayah" class="form-select">
                            <option value="desa" {{ $currentTipe === 'desa' ? 'selected' : '' }}>Desa / Kelurahan</option>
                            <option value="sls" {{ $currentTipe === 'sls' ? 'selected' : '' }}>SLS (Satuan Lingkungan Setempat)</option>
                            <option value="kecamatan" {{ $currentTipe === 'kecamatan' ? 'selected' : '' }}>Kecamatan</option>
                            <option value="nks" {{ $currentTipe === 'nks' ? 'selected' : '' }}>NKS</option>
                        </select>
                    </div>
                </div>

                <!-- Action Buttons: Simpan & Batal Pastel -->
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="{{ route('alokasi.index') }}" class="btn btn-light border px-4 py-2 text-secondary">
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
document.addEventListener('DOMContentLoaded', function() {
    const inputWilayah = document.getElementById('input_nama_wilayah');
    const suggestBox = document.getElementById('wilayah_suggest_box');
    const selectTipe = document.getElementById('select_tipe_wilayah');

    // Dataset wilayah dari database untuk instant autocomplete
    const masterWilayah = @json($wilayahs ?? []);
    let debounceTimer = null;

    function renderSuggestions(items) {
        if (!items || items.length === 0) {
            suggestBox.innerHTML = '';
            suggestBox.classList.add('d-none');
            return;
        }

        let html = '';
        items.forEach(item => {
            const tipeBadge = {
                'desa': '<span class="badge bg-success-subtle text-success border">Desa</span>',
                'sls': '<span class="badge bg-primary-subtle text-primary border">SLS</span>',
                'kecamatan': '<span class="badge bg-warning-subtle text-warning border">Kec</span>',
                'nks': '<span class="badge bg-info-subtle text-info border">NKS</span>'
            }[item.tipe] || `<span class="badge bg-light text-dark">${item.tipe}</span>`;

            const kodeText = item.kode ? `<span class="text-muted small ms-1">(${item.kode})</span>` : '';

            html += `
                <button type="button" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2 px-3 border-bottom suggest-item"
                        data-nama="${item.nama}" data-tipe="${item.tipe}">
                    <div>
                        <i class="fa-solid fa-location-dot text-primary me-2"></i>
                        <span class="fw-semibold text-dark">${item.nama}</span>
                        ${kodeText}
                    </div>
                    <div>${tipeBadge}</div>
                </button>
            `;
        });

        suggestBox.innerHTML = html;
        suggestBox.classList.remove('d-none');

        // Event listener saat item dipilih
        suggestBox.querySelectorAll('.suggest-item').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                inputWilayah.value = this.dataset.nama;
                if (this.dataset.tipe) {
                    selectTipe.value = this.dataset.tipe;
                }
                suggestBox.classList.add('d-none');
            });
        });
    }

    inputWilayah.addEventListener('input', function() {
        const val = this.value.trim().toLowerCase();
        clearTimeout(debounceTimer);

        if (val.length === 0) {
            suggestBox.classList.add('d-none');
            return;
        }

        debounceTimer = setTimeout(() => {
            // Filter dari dataset lokal terlebih dahulu
            let filtered = masterWilayah.filter(w => {
                const namaMatch = w.nama && w.nama.toLowerCase().includes(val);
                const kodeMatch = w.kode && w.kode.toLowerCase().includes(val);
                return namaMatch || kodeMatch;
            }).slice(0, 8);

            if (filtered.length > 0) {
                renderSuggestions(filtered);
            } else {
                // Ajax fallback jika ada lebih banyak data di server
                fetch(`{{ route('wilayah.search-ajax') }}?term=${encodeURIComponent(val)}`)
                    .then(res => res.json())
                    .then(data => {
                        renderSuggestions(data.slice(0, 8));
                    })
                    .catch(() => {
                        suggestBox.classList.add('d-none');
                    });
            }
        }, 150);
    });

    // Sembunyikan suggest saat klik di luar
    document.addEventListener('click', function(e) {
        if (!inputWilayah.contains(e.target) && !suggestBox.contains(e.target)) {
            suggestBox.classList.add('d-none');
        }
    });

    // Tampilkan suggest saat fokus jika sudah ada input
    inputWilayah.addEventListener('focus', function() {
        if (this.value.trim().length > 0) {
            this.dispatchEvent(new Event('input'));
        }
    });
});
</script>
@endpush
@endsection
