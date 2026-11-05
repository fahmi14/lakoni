@extends('layouts.app')

@section('title', 'Master Wilayah — LAKONI')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold brand-font text-dark mb-1">Master Wilayah</h3>
        <p class="text-muted mb-0">Wilayah administrasi survei: Kecamatan, Desa, SLS, atau NKS</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="{{ route('wilayah.create') }}" class="btn btn-pastel-primary shadow-sm">
            <i class="fa-solid fa-plus me-1"></i> Tambah Wilayah
        </a>
    </div>
</div>

<div class="table-responsive-modern">
    <table class="table table-clean align-middle mb-0">
        <thead>
            <tr>
                <th class="ps-4">No</th>
                <th>ID Wilayah</th>
                <th>Tipe Wilayah</th>
                <th class="text-center pe-4">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($wilayahs as $index => $w)
            <tr>
                <td class="ps-4 text-muted small">{{ $wilayahs->firstItem() + $index }}</td>
                <td class="fw-bold text-dark fs-6">{{ $w->nama }}</td>
                <td>
                    <span class="badge-pastel-purple text-uppercase">{{ $w->tipe }}</span>
                </td>
                <td class="text-center pe-4">
                    <div class="d-inline-flex gap-1">
                        <a href="{{ route('wilayah.edit', $w) }}" class="btn-table-action" title="Edit Wilayah">
                            <i class="fa-solid fa-pen-to-square text-primary"></i>
                        </a>
                        <form action="{{ route('wilayah.destroy', $w) }}" method="POST" onsubmit="return confirm('Hapus wilayah ini?')" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-table-action text-danger" title="Hapus Wilayah">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center py-5 text-muted">
                    <div class="py-4">
                        <i class="fa-solid fa-map-location-dot fs-1 text-primary opacity-50 mb-3 d-block"></i>
                        <h6 class="fw-bold text-dark">Belum Ada Master Data Wilayah</h6>
                        <p class="small text-muted mb-0">Klik tombol "Tambah Wilayah" untuk menambahkan data.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($wilayahs->hasPages())
    <div class="p-3 border-top d-flex justify-content-end bg-white">
        {{ $wilayahs->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
