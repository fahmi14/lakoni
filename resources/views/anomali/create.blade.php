@extends('layouts.app')

@section('title', 'Tambah Anomali Manual - LAKONI')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-0">Tambah Anomali Manual</h3>
                <p class="text-muted small mb-0">Input data anomali langsung dengan format detail JSON</p>
            </div>
            <a href="{{ route('anomali.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="card card-custom p-4">
            <form action="{{ route('anomali.store') }}" method="POST">
                @csrf

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Pilih Kegiatan <span class="text-danger">*</span></label>
                        <select name="id_kegiatan" class="form-select" required>
                            <option value="">-- Pilih Kegiatan --</option>
                            @foreach($kegiatans as $k)
                                <option value="{{ $k->id }}">[{{ $k->kode }}] {{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Template Konfirmasi <span class="text-danger">*</span></label>
                        <select name="id_konfirmasi" class="form-select" required>
                            <option value="">-- Pilih Template Konfirmasi --</option>
                            @foreach($konfirmasis as $konf)
                                <option value="{{ $konf->id }}">{{ $konf->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Alokasi Petugas Terkait (Opsional)</label>
                    <select name="id_alokasi" class="form-select">
                        <option value="">-- Belum Dialokasikan --</option>
                        @foreach($alokasis as $al)
                            <option value="{{ $al->id }}">PPL: {{ $al->nama_ppl }} | PML: {{ $al->nama_pml }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Detail Data Anomali (Format JSON) <span class="text-danger">*</span></label>
                    <textarea name="detail_json" class="form-control font-monospace" rows="6" required placeholder='{"NKS": "12345", "Nama Responden": "Budi Santoso", "Luas Panen": "100 Ha", "Produksi": "500 Ton"}'>{{ old('detail_json', "{\n  \"NKS\": \"10001\",\n  \"Nama Responden\": \"Petani Maju\",\n  \"Luas Panen\": \"25 Ha\",\n  \"Produksi\": \"150 Ton\"\n}") }}</textarea>
                    <div class="form-text small">Masukkan objek JSON yang berisi pasangan nama kolom dan nilainya.</div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('anomali.index') }}" class="btn btn-light border">Batal</a>
                    <button type="submit" class="btn btn-primary-lakoni">
                        <i class="fa-solid fa-save me-1"></i> Simpan Anomali
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

