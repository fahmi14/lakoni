@extends('layouts.app')

@section('title', 'Manajemen Anomali — LAKONI')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-5">
        <h3 class="fw-bold brand-font text-dark mb-1">Manajemen Anomali</h3>
        <p class="text-muted mb-0">Daftar temuan anomali data lapangan yang perlu dikonfirmasi</p>
    </div>
    <div class="col-md-7 text-md-end mt-3 mt-md-0 d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
        <!-- Tombol Download Respon Excel (Hanya aktif/jelas jika filter kegiatan & konfirmasi dipilih) -->
        <a href="{{ route('anomali.download-respon', request()->query()) }}" class="btn btn-pastel-success shadow-sm d-inline-flex align-items-center justify-content-center text-center px-3 py-2" title="Unduh respon konfirmasi dalam format spreadsheet (wajib pilih Kegiatan & Template Konfirmasi)">
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
    </div>
</div>

<!-- Filter Card Kalem -->
<div class="card card-clean p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="small fw-bold text-secondary text-uppercase"><i class="fa-solid fa-filter me-1 text-primary"></i> Filter Data Anomali & Ekspor Respon</span>
        @if(!request()->filled('id_kegiatan') || !request()->filled('id_konfirmasi'))
            <span class="badge-pastel-warning" style="font-size: 0.72rem;">
                <i class="fa-solid fa-circle-info me-1"></i> Pilih Kegiatan & Template untuk mengunduh respon Excel
            </span>
        @else
            <span class="badge-pastel-success" style="font-size: 0.72rem;">
                <i class="fa-solid fa-circle-check me-1"></i> Siap Diunduh: Detail dipecah per kolom kegiatan
            </span>
        @endif
    </div>

    <form action="{{ route('anomali.index') }}" method="GET" class="row g-3 align-items-center">
        <div class="col-md-4">
            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Kegiatan <span class="text-danger">*</span></label>
            <select name="id_kegiatan" class="form-select" id="filter_kegiatan">
                <option value="">-- Pilih Kegiatan --</option>
                @foreach($kegiatans as $k)
                    <option value="{{ $k->id }}" {{ request('id_kegiatan') == $k->id ? 'selected' : '' }}>
                        [{{ $k->kode }}] {{ $k->nama }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Template Konfirmasi <span class="text-danger">*</span></label>
            <select name="id_konfirmasi" class="form-select" id="filter_konfirmasi">
                <option value="">-- Pilih Template Konfirmasi --</option>
                @foreach($konfirmasis as $konf)
                    <option value="{{ $konf->id }}" {{ request('id_konfirmasi') == $konf->id ? 'selected' : '' }}>
                        {{ $konf->nama }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Status</label>
            <select name="status" class="form-select">
                <option value="">-- Semua Status --</option>
                <option value="belum" {{ request('status') === 'belum' ? 'selected' : '' }}>Menunggu</option>
                <option value="sesuai" {{ request('status') === 'sesuai' ? 'selected' : '' }}>Sesuai (Valid)</option>
                <option value="tidak_sesuai" {{ request('status') === 'tidak_sesuai' ? 'selected' : '' }}>Perlu Revisi</option>
                <option value="dikonfirmasi" {{ request('status') === 'dikonfirmasi' ? 'selected' : '' }}>Dikonfirmasi</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2 align-items-end pt-md-4">
            <button type="submit" class="btn btn-pastel-soft flex-grow-1"><i class="fa-solid fa-filter me-1"></i> Terapkan</button>
            @if(request()->has('id_kegiatan') || request()->has('id_konfirmasi') || request()->has('status'))
                <a href="{{ route('anomali.index') }}" class="btn btn-light border" title="Reset filter"><i class="fa-solid fa-arrows-rotate"></i></a>
            @endif
        </div>
    </form>
</div>

<!-- Modern, Lega, Pastel Anomali Table with WhatsApp Integration -->
<div class="table-responsive-modern">
    <table class="table table-clean align-middle mb-0">
        <thead>
            <tr>
                <th class="ps-4">No</th>
                <th>Kegiatan & Konfirmasi</th>
                <th>Detail Data (JSON)</th>
                <th>Petugas Terkait</th>
                <th>Status Konfirmasi</th>
                <th class="text-center pe-4">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($anomalis as $index => $a)
            @php
                // Cari nomor HP dari detail JSON
                $noHp = null;
                if (is_array($a->detail)) {
                    foreach ($a->detail as $k => $v) {
                        $keyLower = strtolower(str_replace([' ', '_', '-'], '', $k));
                        if (in_array($keyLower, ['nohp', 'hp', 'telepon', 'notelp', 'telp', 'wa', 'whatsapp', 'nomorhp'])) {
                            $noHp = preg_replace('/[^0-9]/', '', (string)$v);
                            break;
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
                    $template = $a->kegiatan->template_wa ?? "Hai {nama_ppl}, mohon konfirmasi untuk anomali {nama_anomali} di kegiatan {nama_kegiatan}. Terima kasih.";
                    
                    $namaPpl = $a->alokasi->nama_ppl ?? ($a->detail['Nama PPL'] ?? ($a->detail['PPL'] ?? 'Petugas'));
                    $namaPml = $a->alokasi->nama_pml ?? ($a->detail['Nama PML'] ?? ($a->detail['PML'] ?? 'Pengawas'));
                    $namaAnomali = $a->konfirmasi->nama ?? 'Anomali';
                    $namaKegiatan = $a->kegiatan->nama ?? 'Survei';

                    $replacements = [
                        '{nama_ppl}' => $namaPpl,
                        '{nama_pml}' => $namaPml,
                        '{nama_anomali}' => $namaAnomali,
                        '{nama_kegiatan}' => $namaKegiatan,
                        '{kode_kegiatan}' => $a->kegiatan->kode ?? '',
                        '{status}' => $a->status,
                    ];

                    if (is_array($a->detail)) {
                        foreach ($a->detail as $dk => $dv) {
                            $replacements['{' . $dk . '}'] = (string)$dv;
                        }
                    }

                    $pesanWa = strtr($template, $replacements);
                    $waLink = "https://wa.me/{$noHp}?text=" . urlencode($pesanWa);
                }
            @endphp
            <tr>
                <td class="ps-4 text-muted small">{{ $anomalis->firstItem() + $index }}</td>
                <td>
                    <span class="badge-pastel-primary mb-1">{{ $a->kegiatan->kode ?? '-' }}</span>
                    <div class="fw-bold text-dark fs-6">{{ $a->konfirmasi->nama ?? '-' }}</div>
                </td>
                <td>
                    @if(is_array($a->detail))
                        <div class="d-flex flex-wrap gap-1" style="max-width: 440px;">
                            @foreach(array_slice($a->detail, 0, 3) as $k => $v)
                                <span class="badge-pastel-neutral py-1 px-2">
                                    <strong class="text-dark">{{ $k }}:</strong> {{ $v }}
                                </span>
                            @endforeach
                            @if(count($a->detail) > 3)
                                <span class="badge-pastel-primary py-1 px-2">+{{ count($a->detail) - 3 }} variabel</span>
                            @endif
                        </div>
                    @endif
                </td>
                <td>
                    @if($a->alokasi)
                        <div class="small mb-1">
                            <div><span class="text-muted">PPL:</span> <strong class="text-dark">{{ $a->alokasi->nama_ppl }}</strong></div>
                            <div><span class="text-muted">PML:</span> <strong class="text-dark">{{ $a->alokasi->nama_pml }}</strong></div>
                        </div>
                    @else
                        <span class="badge-pastel-neutral mb-1">Belum terpetakan</span>
                    @endif

                    @if($noHp)
                        <div class="mt-1">
                            <a href="{{ $waLink }}" target="_blank" class="btn btn-sm btn-outline-success py-1 px-2 rounded-pill shadow-xs" title="Kirim Pesan WhatsApp">
                                <i class="fa-brands fa-whatsapp text-success me-1"></i> Chat WA ({{ $noHp }})
                            </a>
                        </div>
                    @endif
                </td>
                <td>
                    @if($a->status === 'sesuai')
                        <span class="badge-pastel-success"><i class="fa-solid fa-check-double me-1"></i> Sesuai</span>
                    @elseif($a->status === 'tidak_sesuai')
                        <span class="badge-pastel-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> Perlu Revisi</span>
                    @elseif($a->status === 'dikonfirmasi')
                        <span class="badge-pastel-primary"><i class="fa-solid fa-check me-1"></i> Dikonfirmasi</span>
                    @else
                        <span class="badge-pastel-warning"><i class="fa-solid fa-clock me-1"></i> Menunggu Konfirmasi</span>
                    @endif
                    @if($a->petugas_pengonfirmasi)
                        <div class="small text-muted mt-1"><i class="fa-solid fa-user-check me-1"></i> {{ $a->petugas_pengonfirmasi }}</div>
                    @endif
                </td>
                <td class="text-center pe-4">
                    <div class="d-inline-flex gap-1">
                        @if($noHp)
                            <a href="{{ $waLink }}" target="_blank" class="btn-table-action text-success" title="Chat WhatsApp">
                                <i class="fa-brands fa-whatsapp fs-5"></i>
                            </a>
                        @endif
                        <a href="{{ route('anomali.show', $a) }}" class="btn-table-action" title="Lihat Detail & Jawaban">
                            <i class="fa-solid fa-eye text-primary"></i>
                        </a>
                        <form action="{{ route('anomali.destroy', $a) }}" method="POST" onsubmit="return confirm('Hapus data anomali ini?')" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-table-action text-danger" title="Hapus Anomali">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-5 text-muted">
                    <div class="py-4">
                        <i class="fa-solid fa-file-circle-check fs-1 text-primary opacity-50 mb-3 d-block"></i>
                        <h6 class="fw-bold text-dark">Belum Ada Data Anomali</h6>
                        <p class="small text-muted mb-0">Silakan import data dari file spreadsheet anomali.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($anomalis->hasPages())
    <div class="p-3 border-top d-flex justify-content-end bg-white">
        {{ $anomalis->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
