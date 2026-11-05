@extends('layouts.app')

@section('title', 'Lembar Konfirmasi Anomali — LAKONI')

@section('content')
<div class="row justify-content-center py-2">
    <div class="col-12 col-lg-8">
        
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold brand-font text-dark mb-1">Lembar Konfirmasi Anomali #{{ $anomali->id }}</h3>
                <p class="text-muted small mb-0">Petugas: <strong class="text-dark">{{ $nama }}</strong></p>
            </div>
            <a href="{{ route('petugas.anomali.list') }}" class="btn btn-sm btn-pastel-soft">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>

        <!-- Detail Data Anomali yang Diperiksa -->
        <div class="card card-clean p-4 mb-4">
            <h5 class="fw-bold text-dark mb-3">
                <i class="fa-solid fa-file-invoice text-primary me-2"></i>Data Lapangan Yang Diperiksa
            </h5>
            <div class="table-responsive rounded-3 border">
                <table class="table table-clean mb-0">
                    <thead>
                        <tr>
                            <th style="width: 38%;">Variabel / Kolom</th>
                            <th>Nilai Tercatat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(is_array($anomali->detail))
                            @foreach($anomali->detail as $col => $val)
                            <tr>
                                <td class="fw-semibold text-secondary">{{ $col }}</td>
                                <td class="text-dark fw-medium">{{ $val }}</td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Form Pertanyaan Konfirmasi (Berdasarkan JSON) -->
        <div class="card card-clean p-4 p-md-5 mb-4">
            <div class="mb-4">
                <span class="badge-pastel-primary mb-2 d-inline-block">Template Konfirmasi</span>
                <h4 class="fw-bold text-dark mb-1">{{ $konfirmasi->nama }}</h4>
                <p class="text-muted small">Jawab pertanyaan berikut berdasarkan hasil klarifikasi ke responden atau lapangan.</p>
            </div>

            <form action="{{ route('petugas.konfirmasi.save', $anomali) }}" method="POST">
                @csrf

                @php
                    $savedAnswers = $anomali->hasil_konfirmasi ?? [];
                @endphp

                @forelse($pertanyaanList as $idx => $q)
                <div class="p-4 mb-4 rounded-3 border bg-white shadow-sm" style="border-color: #f1f5f9 !important;">
                    <label class="fw-bold text-dark mb-3 d-flex align-items-center">
                        <span class="badge-pastel-primary me-2 px-2 py-1 fs-6">{{ $idx + 1 }}</span> 
                        <span class="fs-6">{{ $q['teks'] }}</span>
                    </label>

                    @if(($q['tipe'] ?? 'pilihan') === 'pilihan')
                        <!-- Pertanyaan Pilihan Ganda -->
                        <div class="mt-2 ps-1">
                            @foreach($q['opsi'] ?? [] as $optIdx => $opsiText)
                                <div class="form-check p-3 mb-2 rounded-3 border bg-light bg-opacity-50">
                                    <input class="form-check-input ms-0 me-3" 
                                           type="radio" 
                                           name="answers[{{ $idx }}]" 
                                           id="ans_{{ $idx }}_{{ $optIdx }}" 
                                           value="{{ $optIdx }}"
                                           {{ (isset($savedAnswers[$idx]) && (string)$savedAnswers[$idx] === (string)$optIdx) ? 'checked' : ($optIdx === 0 && !isset($savedAnswers[$idx]) ? 'checked' : '') }}
                                           required>
                                    <label class="form-check-label text-dark fw-medium" for="ans_{{ $idx }}_{{ $optIdx }}">
                                        {{ $opsiText }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <!-- Pertanyaan Jawaban Terbuka -->
                        <div class="mt-2">
                            <textarea name="answers[{{ $idx }}]" 
                                      class="form-control" 
                                      rows="4" 
                                      placeholder="Tuliskan keterangan / catatan hasil konfirmasi langsung di lapangan..." 
                                      required>{{ $savedAnswers[$idx] ?? '' }}</textarea>
                        </div>
                    @endif
                </div>
                @empty
                <div class="alert alert-warning border-0" style="background-color: var(--lk-warning-soft); color: #b45309;">
                    Tidak ada pertanyaan konfirmasi yang dikonfigurasikan pada template ini.
                </div>
                @endforelse

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('petugas.anomali.list') }}" class="btn btn-light border px-4 py-2">Batal</a>
                    <button type="submit" class="btn btn-pastel-success px-4 py-2 fw-semibold">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Simpan Hasil Konfirmasi
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
