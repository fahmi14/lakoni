@extends('layouts.app')

@section('title', 'Pilih Petugas — LAKONI')

@section('content')
<div class="row justify-content-center align-items-center py-4" style="min-height: 72vh;">
    <div class="col-12 col-md-8 col-lg-5">
        
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center text-white rounded-4 shadow-sm mb-3" style="width: 70px; height: 70px; background: linear-gradient(135deg, #0ea5e9 0%, #4f6ff6 100%);">
                <i class="fa-solid fa-id-badge fs-2"></i>
            </div>
            <h3 class="fw-bold brand-font text-dark mb-1">Identitas Petugas Lapangan</h3>
            <p class="text-muted small">
                Kegiatan: <span class="badge-pastel-primary fs-6">[{{ $kegiatan->kode }}] {{ $kegiatan->nama }}</span>
            </p>
        </div>

        <div class="card card-clean p-4 p-md-5">
            <form action="{{ route('petugas.set-petugas') }}" method="POST">
                @csrf

                <!-- Pilihan Tipe Petugas -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-secondary small text-uppercase mb-2">Pilih Peran Petugas</label>
                    <div class="row g-3">
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="tipe" id="tipe_ppl" value="ppl" checked>
                            <label class="btn btn-outline-primary w-100 py-3 rounded-3 fw-bold" for="tipe_ppl">
                                <i class="fa-solid fa-user-pen d-block fs-3 mb-1 text-primary"></i> PPL (Pencacah)
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="tipe" id="tipe_pml" value="pml">
                            <label class="btn btn-outline-primary w-100 py-3 rounded-3 fw-bold" for="tipe_pml">
                                <i class="fa-solid fa-user-tie d-block fs-3 mb-1 text-primary"></i> PML (Pengawas)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Pilihan Nama Petugas -->
                <div class="mb-4">
                    <label for="nama_select" class="form-label fw-bold text-secondary small text-uppercase mb-2">Nama Anda</label>
                    
                    <!-- Select untuk PPL -->
                    <select name="nama" id="select_ppl" class="form-select form-select-lg">
                        <option value="">-- Pilih Nama PPL --</option>
                        @foreach($daftarPpl as $ppl)
                            <option value="{{ $ppl }}">{{ $ppl }}</option>
                        @endforeach
                    </select>

                    <!-- Select untuk PML -->
                    <select id="select_pml" class="form-select form-select-lg" style="display: none;">
                        <option value="">-- Pilih Nama PML --</option>
                        @foreach($daftarPml as $pml)
                            <option value="{{ $pml }}">{{ $pml }}</option>
                        @endforeach
                    </select>

                    <div class="form-text small mt-2 text-muted">
                        <i class="fa-solid fa-circle-check text-success me-1"></i> Nama otomatis terdaftar berdasarkan data alokasi kegiatan.
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('home') }}" class="btn btn-light border py-2 px-3 text-muted" title="Kembali ke input kode">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                    <button type="submit" class="btn btn-pastel-primary flex-grow-1 py-3 fw-bold">
                        Buka Daftar Anomali <i class="fa-solid fa-arrow-right ms-1"></i>
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

@push('scripts')
<script>
    const radioPpl = document.getElementById('tipe_ppl');
    const radioPml = document.getElementById('tipe_pml');
    const selectPpl = document.getElementById('select_ppl');
    const selectPml = document.getElementById('select_pml');

    function updateSelect() {
        if (radioPpl.checked) {
            selectPpl.style.display = 'block';
            selectPpl.name = 'nama';
            selectPml.style.display = 'none';
            selectPml.name = '';
        } else {
            selectPml.style.display = 'block';
            selectPml.name = 'nama';
            selectPpl.style.display = 'none';
            selectPml.name = '';
        }
    }

    radioPpl.addEventListener('change', updateSelect);
    radioPml.addEventListener('change', updateSelect);
</script>
@endpush
@endsection
