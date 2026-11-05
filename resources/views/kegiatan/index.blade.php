@extends('layouts.app')

@section('title', 'Manajemen Kegiatan — LAKONI')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold brand-font text-dark mb-1">Manajemen Kegiatan</h3>
        <p class="text-muted mb-0">Kelola jadwal kegiatan survei / sensus dan kolom utama</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="{{ route('kegiatan.create') }}" class="btn btn-pastel-primary shadow-sm">
            <i class="fa-solid fa-plus me-1"></i> Tambah Kegiatan
        </a>
    </div>
</div>

<div class="table-responsive-modern">
    <table class="table table-clean align-middle mb-0">
        <thead>
            <tr>
                <th class="ps-4">Kode</th>
                <th>Nama Kegiatan</th>
                <th>Periode Aktif</th>
                <th>Status Jadwal</th>
                <th>Total Alokasi</th>
                <th>Total Anomali</th>
                <th class="text-center pe-4">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kegiatans as $k)
            <tr>
                <td class="ps-4"><span class="badge-pastel-primary fs-6">{{ $k->kode }}</span></td>
                <td class="fw-bold text-dark fs-6">{{ $k->nama }}</td>
                <td class="small text-muted">
                    <i class="fa-regular fa-calendar me-1"></i> {{ $k->mulai->format('d M Y') }} &mdash; {{ $k->selesai->format('d M Y') }}
                </td>
                <td>
                    @if($k->isAktif())
                        <span class="badge-pastel-success"><i class="fa-solid fa-circle-check me-1"></i> Aktif</span>
                    @else
                        <span class="badge-pastel-neutral"><i class="fa-solid fa-clock me-1"></i> Di Luar Jadwal</span>
                    @endif
                </td>
                <td><span class="badge-pastel-primary">{{ $k->alokasi_count }} alokasi</span></td>
                <td><span class="badge-pastel-danger">{{ $k->anomali_count }} anomali</span></td>
                <td class="text-center pe-4">
                    <div class="d-inline-flex gap-1">
                        <a href="{{ route('kegiatan.edit', $k) }}" class="btn-table-action" title="Edit Kegiatan">
                            <i class="fa-solid fa-pen-to-square text-primary"></i>
                        </a>
                        <form action="{{ route('kegiatan.destroy', $k) }}" method="POST" onsubmit="return confirm('Hapus kegiatan ini beserta seluruh data alokasi dan anomalinya?')" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-table-action text-danger" title="Hapus Kegiatan">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-5 text-muted">
                    <div class="py-4">
                        <i class="fa-solid fa-calendar-xmark fs-1 text-primary opacity-50 mb-3 d-block"></i>
                        <h6 class="fw-bold text-dark">Belum Ada Data Kegiatan</h6>
                        <p class="small text-muted mb-0">Klik tombol "Tambah Kegiatan" untuk memulai.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($kegiatans->hasPages())
    <div class="p-3 border-top d-flex justify-content-end bg-white">
        {{ $kegiatans->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
