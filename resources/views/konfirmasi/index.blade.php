@extends('layouts.app')

@section('title', 'Template Konfirmasi (JSON) — LAKONI')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold brand-font text-dark mb-1">Template Konfirmasi</h3>
        <p class="text-muted mb-0">Pertanyaan konfirmasi berformat JSON (pilihan ganda / jawaban terbuka)</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="{{ route('konfirmasi.create') }}" class="btn btn-pastel-primary shadow-sm">
            <i class="fa-solid fa-plus me-1"></i> Buat Template Baru
        </a>
    </div>
</div>

<div class="table-responsive-modern">
    <table class="table table-clean align-middle mb-0">
        <thead>
            <tr>
                <th class="ps-4">No</th>
                <th>Nama Template</th>
                <th>Struktur Pertanyaan (JSON)</th>
                <th>Nilai Kriteria Sesuai</th>
                <th>Total Anomali</th>
                <th class="text-center pe-4">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($konfirmasis as $index => $k)
            <tr>
                <td class="ps-4 text-muted small">{{ $konfirmasis->firstItem() + $index }}</td>
                <td class="fw-bold text-dark fs-6">{{ $k->nama }}</td>
                <td>
                    @if(is_array($k->pertanyaan))
                        <div class="d-flex flex-column gap-1" style="max-width: 480px;">
                            @foreach($k->pertanyaan as $idx => $q)
                                <div class="small text-secondary">
                                    <strong class="text-dark">Q{{ $idx + 1 }}:</strong> {{ $q['teks'] ?? '-' }}
                                    <span class="badge-pastel-neutral ms-1" style="font-size: 0.7rem;">
                                        {{ ($q['tipe'] ?? 'pilihan') === 'pilihan' ? 'Pilihan: ' . implode(' | ', $q['opsi'] ?? []) : 'Jawaban Terbuka' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <code class="small">{{ json_encode($k->pertanyaan) }}</code>
                    @endif
                </td>
                <td>
                    @if($k->sesuai !== null)
                        <span class="badge-pastel-success"><i class="fa-solid fa-check me-1"></i> Opsi Ke-{{ $k->sesuai + 1 }}</span>
                    @else
                        <span class="text-muted small">-</span>
                    @endif
                </td>
                <td>
                    <span class="badge-pastel-primary">{{ $k->anomali_count }} data</span>
                </td>
                <td class="text-center pe-4">
                    <div class="d-inline-flex gap-1">
                        <a href="{{ route('konfirmasi.edit', $k) }}" class="btn-table-action" title="Edit Template">
                            <i class="fa-solid fa-pen-to-square text-primary"></i>
                        </a>
                        <form action="{{ route('konfirmasi.destroy', $k) }}" method="POST" onsubmit="return confirmPastelAction(this, { title: 'Hapus Template Konfirmasi', message: 'Hapus template konfirmasi {{ addslashes($k->nama) }}? Template ini mungkin terhubung dengan data anomali.' })" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-table-action text-danger" title="Hapus Template">
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
                        <i class="fa-solid fa-circle-question fs-1 text-primary opacity-50 mb-3 d-block"></i>
                        <h6 class="fw-bold text-dark">Belum Ada Template Konfirmasi</h6>
                        <p class="small text-muted mb-0">Klik tombol "Buat Template Baru" untuk menambahkan pertanyaan.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($konfirmasis->hasPages())
    <div class="p-3 border-top d-flex justify-content-end bg-white">
        {{ $konfirmasis->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
