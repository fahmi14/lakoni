@extends('layouts.app')

@section('title', 'Manajemen Pengguna — LAKONI')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold brand-font text-dark mb-1">Manajemen Pengguna</h3>
        <p class="text-muted mb-0">Kelola akun Admin dan Petugas untuk sistem LAKONI</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="{{ route('users.create') }}" class="btn btn-pastel-primary shadow-sm">
            <i class="fa-solid fa-user-plus me-1"></i> Tambah Pengguna Baru
        </a>
    </div>
</div>

<!-- Filter Box Kalem -->
<div class="card card-clean p-4 mb-4">
    <form action="{{ route('users.index') }}" method="GET" class="row g-3 align-items-center">
        <div class="col-md-6">
            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Pencarian User</label>
            <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan nama atau email..." value="{{ request('search') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Peran (Role)</label>
            <select name="role" class="form-select">
                <option value="">Semua Peran (Role)</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="petugas" {{ request('role') === 'petugas' ? 'selected' : '' }}>Petugas</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2 align-items-end pt-md-4">
            <button type="submit" class="btn btn-pastel-soft flex-grow-1"><i class="fa-solid fa-filter me-1"></i> Filter</button>
            @if(request()->filled('search') || request()->filled('role'))
                <a href="{{ route('users.index') }}" class="btn btn-light border" title="Reset"><i class="fa-solid fa-arrows-rotate"></i></a>
            @endif
        </div>
    </form>
</div>

<!-- Modern Users Table -->
<div class="table-responsive-modern">
    <table class="table table-clean align-middle mb-0">
        <thead>
            <tr>
                <th class="ps-4">No</th>
                <th>Nama Pengguna</th>
                <th>Alamat Email</th>
                <th>Peran</th>
                <th>Terdaftar Sejak</th>
                <th class="text-center pe-4">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $index => $user)
            <tr>
                <td class="ps-4 text-muted small">{{ $users->firstItem() + $index }}</td>
                <td>
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex justify-content-center align-items-center fw-bold shadow-sm" style="width: 40px; height: 40px; background: linear-gradient(135deg, #4f6ff6 0%, #0ea5e9 100%); color: #ffffff;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <span class="fw-bold text-dark fs-6">{{ $user->name }}</span>
                            @if(auth()->id() === $user->id)
                                <span class="badge-pastel-success ms-1" style="font-size: 0.65rem;">Anda</span>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="text-secondary">{{ $user->email }}</td>
                <td>
                    @if($user->role === 'admin')
                        <span class="badge-pastel-danger"><i class="fa-solid fa-shield-halved me-1"></i> Admin</span>
                    @else
                        <span class="badge-pastel-primary"><i class="fa-solid fa-user me-1"></i> Petugas</span>
                    @endif
                </td>
                <td class="text-muted small">
                    {{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : '-' }}
                </td>
                <td class="text-center pe-4">
                    <div class="d-inline-flex gap-1">
                        <a href="{{ route('users.show', $user) }}" class="btn-table-action" title="Lihat Detail">
                            <i class="fa-solid fa-eye text-primary"></i>
                        </a>
                        <a href="{{ route('users.edit', $user) }}" class="btn-table-action" title="Edit Pengguna">
                            <i class="fa-solid fa-pen-to-square text-secondary"></i>
                        </a>
                        @if(auth()->id() !== $user->id)
                        <button type="button" class="btn-table-action text-danger" title="Hapus Pengguna" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $user->id }}">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                        @endif
                    </div>

                    <!-- Modal Konfirmasi Hapus Kalem -->
                    @if(auth()->id() !== $user->id)
                    <div class="modal fade" id="deleteModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg" style="border-radius: var(--lk-radius);">
                                <div class="modal-header border-0 pt-4 px-4 pb-0">
                                    <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-triangle-exclamation text-danger me-2"></i>Konfirmasi Hapus</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body text-start py-3 px-4">
                                    <p class="mb-2 text-muted">Apakah Anda yakin ingin menghapus akun pengguna berikut?</p>
                                    <div class="p-3 bg-light rounded-3 border">
                                        <div class="fw-bold text-dark">{{ $user->name }}</div>
                                        <div class="small text-muted">{{ $user->email }} &bull; Role: {{ $user->role }}</div>
                                    </div>
                                    <div class="small text-danger mt-2">
                                        <i class="fa-solid fa-circle-exclamation me-1"></i> Tindakan ini tidak dapat dibatalkan.
                                    </div>
                                </div>
                                <div class="modal-footer border-0 px-4 pb-4">
                                    <button type="button" class="btn btn-light border px-3" data-bs-dismiss="modal">Batal</button>
                                    <form action="{{ route('users.destroy', $user) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-pastel-danger px-3">Hapus Akun</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-5 text-muted">
                    <div class="py-4">
                        <i class="fa-solid fa-user-slash fs-1 text-primary opacity-50 mb-3 d-block"></i>
                        <h6 class="fw-bold text-dark">Tidak Ada Pengguna Ditemukan</h6>
                        <p class="small text-muted mb-0">Sesuaikan kata kunci pencarian atau filter Anda.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($users->hasPages())
    <div class="p-3 border-top d-flex justify-content-end bg-white">
        {{ $users->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
