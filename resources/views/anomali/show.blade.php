@extends('layouts.app')

@section('title', 'Detail Lembar Anomali — LAKONI')

@section('content')
@php
    // Cari nomor HP dari detail JSON hanya jika petugas sudah teralokasi ($anomali->alokasi)
    $noHp = null;
    if ($anomali->alokasi && is_array($anomali->detail)) {
        foreach ($anomali->detail as $k => $v) {
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
        $template = $anomali->kegiatan->template_wa ?? "Hai {nama_ppl}, mohon konfirmasi untuk anomali {nama_anomali} di kegiatan {nama_kegiatan}. Terima kasih.";
        $namaPpl = $anomali->alokasi->nama_ppl ?? ($anomali->detail['Nama PPL'] ?? ($anomali->detail['PPL'] ?? 'Petugas'));
        $namaPml = $anomali->alokasi->nama_pml ?? ($anomali->detail['Nama PML'] ?? ($anomali->detail['PML'] ?? 'Pengawas'));
        $replacements = [
            '{nama_ppl}' => $namaPpl,
            '{nama_pml}' => $namaPml,
            '{nama_anomali}' => $anomali->konfirmasi->nama ?? 'Anomali',
            '{nama_kegiatan}' => $anomali->kegiatan->nama ?? 'Survei',
            '{kode_kegiatan}' => $anomali->kegiatan->kode ?? '',
            '{status}' => $anomali->status,
        ];
        if (is_array($anomali->detail)) {
            foreach ($anomali->detail as $dk => $dv) {
                $replacements['{' . $dk . '}'] = (string)$dv;
            }
        }
        $pesanWa = strtr($template, $replacements);
        $waLink = "https://wa.me/{$noHp}?text=" . urlencode($pesanWa);
    }
@endphp

<div class="row justify-content-center py-2">
    <div class="col-12 col-xl-10">
        
        <!-- Header Halaman Lega & Kalem -->
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge-pastel-primary fs-6">ID #{{ $anomali->id }}</span>
                    <span class="badge-pastel-neutral">[{{ $anomali->kegiatan->kode ?? '-' }}] {{ $anomali->kegiatan->nama ?? '-' }}</span>
                </div>
                <h3 class="fw-bold brand-font text-dark mb-0">{{ $anomali->konfirmasi->nama ?? 'Detail Lembar Anomali' }}</h3>
            </div>
            <div class="d-flex gap-2">
                @if($noHp)
                    <a href="{{ $waLink }}" target="_blank" class="btn btn-pastel-success">
                        <i class="fa-brands fa-whatsapp me-1 fs-6"></i> Hubungi Petugas via WA
                    </a>
                @endif
                <a href="{{ route('anomali.index') }}" class="btn btn-sm btn-pastel-soft">
                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>
        </div>

        <!-- Kartu Ikhtisar Status & Petugas (Split Grid Pastel) -->
        <div class="row g-4 mb-4">
            <!-- Kolom Status Verifikasi -->
            <div class="col-12 col-md-6">
                <div class="card card-clean p-4 h-100">
                    <span class="text-secondary small fw-bold text-uppercase mb-2 d-block" style="letter-spacing: 0.05em;">Status Verifikasi Konfirmasi</span>
                    <div class="d-flex align-items-center gap-3 mt-1">
                        @if($anomali->status === 'sesuai')
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: var(--lk-success-soft); color: var(--lk-success);">
                                <i class="fa-solid fa-check-double fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-success mb-0">SESUAI (VALID)</h5>
                                <div class="small text-muted">Data telah dikonfirmasi dan sesuai kondisi lapangan</div>
                            </div>
                        @elseif($anomali->status === 'tidak_sesuai')
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: var(--lk-danger-soft); color: var(--lk-danger);">
                                <i class="fa-solid fa-triangle-exclamation fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-danger mb-0">PERLU REVISI</h5>
                                <div class="small text-muted">Terdapat ketidaksesuaian yang memerlukan perbaikan data</div>
                            </div>
                        @elseif($anomali->status === 'dikonfirmasi')
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: var(--lk-primary-soft); color: var(--lk-primary);">
                                <i class="fa-solid fa-check fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-primary mb-0">TELAH DIKONFIRMASI</h5>
                                <div class="small text-muted">Petugas telah mengisi jawaban konfirmasi</div>
                            </div>
                        @else
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: var(--lk-warning-soft); color: #b45309;">
                                <i class="fa-solid fa-clock fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-warning mb-0">MENUNGGU KONFIRMASI</h5>
                                <div class="small text-muted">Belum ada respon jawaban dari petugas lapangan</div>
                            </div>
                        @endif
                    </div>

                    @if($anomali->petugas_pengonfirmasi)
                        <div class="mt-3 pt-3 border-top small text-muted">
                            <i class="fa-regular fa-user me-1 text-primary"></i> Dikonfirmasi oleh: <strong>{{ $anomali->petugas_pengonfirmasi }}</strong>
                            <span class="mx-1">&bull;</span>
                            <i class="fa-regular fa-calendar-check me-1 text-primary"></i> {{ $anomali->dikonfirmasi_pada ? $anomali->dikonfirmasi_pada->format('d/m/Y H:i') : '-' }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Kolom Alokasi Petugas & Kontak -->
            <div class="col-12 col-md-6">
                <div class="card card-clean p-4 h-100">
                    <span class="text-secondary small fw-bold text-uppercase mb-2 d-block" style="letter-spacing: 0.05em;">Alokasi Petugas & Wilayah</span>
                    <div class="d-flex flex-column gap-2 mt-1">
                        <div class="d-flex justify-content-between align-items-center p-2 rounded-3 bg-light">
                            <span class="small text-muted"><i class="fa-solid fa-user-pen text-primary me-2"></i>Petugas PPL:</span>
                            <strong class="text-dark">{{ $anomali->alokasi->nama_ppl ?? ($anomali->detail['Nama PPL'] ?? ($anomali->detail['PPL'] ?? '-')) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2 rounded-3 bg-light">
                            <span class="small text-muted"><i class="fa-solid fa-user-tie text-teal me-2"></i>Pengawas PML:</span>
                            <strong class="text-dark">{{ $anomali->alokasi->nama_pml ?? ($anomali->detail['Nama PML'] ?? ($anomali->detail['PML'] ?? '-')) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2 rounded-3 bg-light">
                            <span class="small text-muted"><i class="fa-solid fa-map-pin text-danger me-2"></i>Wilayah:</span>
                            <span class="badge-pastel-neutral">
                                {{ $anomali->alokasi->wilayah->nama ?? ($anomali->detail['Wilayah'] ?? ($anomali->detail['Desa'] ?? ($anomali->detail['SLS'] ?? '-'))) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Jawaban Hasil Konfirmasi (Jika Sudah Ada) -->
        @if(!empty($anomali->hasil_konfirmasi))
        <div class="card card-clean p-4 p-md-5 mb-4">
            <h5 class="fw-bold brand-font text-dark mb-3">
                <i class="fa-solid fa-comment-dots text-primary me-2"></i>Hasil Respon Konfirmasi Petugas
            </h5>
            <div class="d-flex flex-column gap-3">
                @php
                    $pertanyaanList = $anomali->konfirmasi->pertanyaan ?? [];
                @endphp
                @foreach($anomali->hasil_konfirmasi as $idx => $ans)
                    <div class="p-4 rounded-3 border bg-light bg-opacity-50">
                        <div class="small fw-bold text-secondary mb-1">
                            <span class="badge-pastel-primary me-1 px-2 py-0">Q{{ $idx + 1 }}</span>
                            {{ $pertanyaanList[$idx]['teks'] ?? 'Pertanyaan #'.($idx+1) }}
                        </div>
                        <div class="fs-6 fw-bold text-dark mt-2 ps-1 border-start border-3 border-primary ps-3">
                            @if(isset($pertanyaanList[$idx]['tipe']) && $pertanyaanList[$idx]['tipe'] === 'pilihan')
                                {{ $pertanyaanList[$idx]['opsi'][$ans] ?? $ans }}
                            @else
                                {{ $ans }}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Kartu Rincian Variabel Data Anomali (JSON Detail) -->
        <div class="card card-clean p-0 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark"><i class="fa-solid fa-database text-primary me-2"></i>Rincian Nilai Variabel Lapangan (Detail Anomali)</span>
                <span class="badge-pastel-neutral">{{ is_array($anomali->detail) ? count($anomali->detail) : 0 }} Variabel</span>
            </div>
            <div class="table-responsive">
                <table class="table table-clean mb-0">
                    <thead>
                        <tr>
                            <th style="width: 35%;" class="ps-4">Nama Kolom / Variabel</th>
                            <th class="pe-4">Nilai Data Tercatat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(is_array($anomali->detail))
                            @foreach($anomali->detail as $kolom => $nilai)
                            <tr>
                                <td class="ps-4 fw-bold text-secondary font-monospace small">
                                    <i class="fa-regular fa-circle-dot text-primary me-2" style="font-size: 0.65rem;"></i>{{ $kolom }}
                                </td>
                                <td class="pe-4 text-dark fw-semibold fs-6">
                                    @php
                                        $valStr = is_array($nilai) ? json_encode($nilai) : (string)$nilai;
                                        $hasHtml = (stripos($valStr, '<a ') !== false || stripos($valStr, '<button') !== false || stripos($valStr, '<span') !== false);
                                    @endphp
                                    @if($hasHtml)
                                        {!! $valStr !!}
                                    @else
                                        {{ $valStr }}
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="2" class="p-4 text-muted">{{ $anomali->detail }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
