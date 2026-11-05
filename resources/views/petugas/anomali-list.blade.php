@extends('layouts.app')

@section('title', 'Daftar Anomali — LAKONI')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-7">
        <h3 class="fw-bold brand-font text-dark mb-1">Daftar Lembar Anomali</h3>
        <p class="text-muted mb-0">
            Petugas: <strong>{{ $nama }}</strong> (<span class="badge-pastel-primary text-uppercase">{{ $tipe }}</span>) &bull; 
            Kegiatan: <span class="badge-pastel-neutral">[{{ $kegiatan->kode }}] {{ $kegiatan->nama }}</span>
        </p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <a href="{{ route('petugas.select-petugas') }}" class="btn btn-sm btn-pastel-soft me-1">
            <i class="fa-solid fa-arrows-rotate me-1"></i> Ganti Nama
        </a>
        <a href="{{ route('home') }}" class="btn btn-sm btn-light border text-muted">
            <i class="fa-solid fa-barcode me-1"></i> Ganti Kode
        </a>
    </div>
</div>

<!-- Filter Tabs Kalem -->
<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="{{ route('petugas.anomali.list') }}" class="btn btn-sm {{ !request()->has('status') ? 'btn-pastel-primary' : 'btn-light border text-secondary' }}">
        Semua ({{ $anomalis->total() }})
    </a>
    <a href="{{ route('petugas.anomali.list', ['status' => 'belum']) }}" class="btn btn-sm {{ request('status') === 'belum' ? 'btn-warning text-dark fw-bold' : 'btn-light border text-secondary' }}">
        <i class="fa-solid fa-clock me-1"></i> Menunggu Konfirmasi
    </a>
    <a href="{{ route('petugas.anomali.list', ['status' => 'sesuai']) }}" class="btn btn-sm {{ request('status') === 'sesuai' ? 'btn-success fw-bold' : 'btn-light border text-secondary' }}">
        <i class="fa-solid fa-check-double me-1"></i> Sesuai / Valid
    </a>
    <a href="{{ route('petugas.anomali.list', ['status' => 'tidak_sesuai']) }}" class="btn btn-sm {{ request('status') === 'tidak_sesuai' ? 'btn-danger fw-bold' : 'btn-light border text-secondary' }}">
        <i class="fa-solid fa-triangle-exclamation me-1"></i> Perlu Revisi
    </a>
</div>

<!-- Card Anomali List Lega & Bersih -->
<div class="card card-clean p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-clean align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">No</th>
                    <th>Judul Anomali / Konfirmasi</th>
                    <th>Detail Isian Lapangan</th>
                    <th>Status</th>
                    <th class="text-center pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($anomalis as $index => $a)
                <tr>
                    <td class="ps-4 text-muted small">{{ $anomalis->firstItem() + $index }}</td>
                    <td>
                        <div class="fw-bold text-dark fs-6">{{ $a->konfirmasi->nama ?? 'Konfirmasi Anomali' }}</div>
                        <span class="badge-pastel-neutral" style="font-size: 0.7rem;">ID: #{{ $a->id }}</span>
                    </td>
                    <td>
                        @if(is_array($a->detail))
                            <div class="d-flex flex-wrap gap-1" style="max-width: 520px;">
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
                        @if($a->status === 'sesuai')
                            <span class="badge-pastel-success"><i class="fa-solid fa-check-double me-1"></i> Sesuai</span>
                        @elseif($a->status === 'tidak_sesuai')
                            <span class="badge-pastel-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> Revisi</span>
                        @elseif($a->status === 'dikonfirmasi')
                            <span class="badge-pastel-primary"><i class="fa-solid fa-check me-1"></i> Selesai</span>
                        @else
                            <span class="badge-pastel-warning"><i class="fa-solid fa-pen me-1"></i> Menunggu Konfirmasi</span>
                        @endif
                    </td>
                    <td class="text-center pe-4">
                        <a href="{{ route('petugas.konfirmasi', $a) }}" class="btn btn-sm {{ $a->status === 'belum' ? 'btn-pastel-primary' : 'btn-pastel-soft' }}">
                            <i class="fa-solid fa-clipboard-check me-1"></i>
                            {{ $a->status === 'belum' ? 'Konfirmasi Sekarang' : 'Lihat / Edit Jawaban' }}
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <div class="py-4">
                            <i class="fa-solid fa-circle-check fs-1 text-success opacity-75 mb-3 d-block"></i>
                            <h6 class="fw-bold text-dark">Semua Data Telah Dikonfirmasi</h6>
                            <p class="small text-muted mb-0">Tidak ada catatan anomali yang perlu dikonfirmasi untuk Anda saat ini.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($anomalis->hasPages())
    <div class="p-3 border-top d-flex justify-content-end bg-white">
        {{ $anomalis->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
