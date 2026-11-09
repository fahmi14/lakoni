<?php

namespace App\Http\Controllers;

use App\Models\Wilayah;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class WilayahController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Wilayah::query()->withCount('alokasi');

            if ($request->filled('tipe')) {
                $query->where('tipe', $request->tipe);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($w) {
                    return '<div class="form-check d-flex justify-content-center mb-0">
                                <input type="checkbox" class="form-check-input check-wilayah-item" value="' . $w->id . '">
                            </div>';
                })
                ->addColumn('tipe_badge', function ($w) {
                    $badges = [
                        'desa' => '<span class="badge-pastel-success text-uppercase"><i class="fa-solid fa-tree me-1"></i> Desa</span>',
                        'sls' => '<span class="badge-pastel-primary text-uppercase"><i class="fa-solid fa-map-pin me-1"></i> SLS</span>',
                        'kecamatan' => '<span class="badge-pastel-warning text-uppercase"><i class="fa-solid fa-landmark me-1"></i> Kecamatan</span>',
                        'nks' => '<span class="badge-pastel-purple text-uppercase"><i class="fa-solid fa-hashtag me-1"></i> NKS</span>',
                    ];
                    return $badges[$w->tipe] ?? '<span class="badge-pastel-neutral text-uppercase">' . e($w->tipe) . '</span>';
                })
                ->addColumn('alokasi_count', function ($w) {
                    if ($w->alokasi_count > 0) {
                        return '<span class="badge bg-light text-primary border fw-semibold"><i class="fa-solid fa-users me-1"></i> ' . $w->alokasi_count . ' Alokasi</span>';
                    }
                    return '<span class="text-muted small">-</span>';
                })
                ->addColumn('action', function ($w) {
                    $editUrl = route('wilayah.edit', $w);
                    $deleteUrl = route('wilayah.destroy', $w);
                    $token = csrf_field();
                    $method = method_field('DELETE');
                    $safeNama = addslashes($w->nama);
                    $safeTipe = strtoupper($w->tipe);

                    return '
                    <div class="d-inline-flex gap-1 justify-content-center">
                        <a href="' . $editUrl . '" class="btn-table-action" title="Edit Wilayah">
                            <i class="fa-solid fa-pen-to-square text-primary"></i>
                        </a>
                        <form action="' . $deleteUrl . '" method="POST" onsubmit="return confirmPastelAction(this, { title: \'Hapus Master Wilayah\', message: \'Hapus master wilayah ' . $safeNama . ' (' . $safeTipe . ')?\' })" class="d-inline">
                            ' . $token . '
                            ' . $method . '
                            <button type="submit" class="btn-table-action text-danger" title="Hapus Wilayah">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>';
                })
                ->rawColumns(['checkbox', 'tipe_badge', 'alokasi_count', 'action'])
                ->make(true);
        }

        $totalWilayah = Wilayah::count();
        $countDesa = Wilayah::where('tipe', 'desa')->count();
        $countSls = Wilayah::where('tipe', 'sls')->count();
        $countKecamatan = Wilayah::where('tipe', 'kecamatan')->count();
        $countNks = Wilayah::where('tipe', 'nks')->count();

        return view('wilayah.index', compact('totalWilayah', 'countDesa', 'countSls', 'countKecamatan', 'countNks'));
    }

    public function create()
    {
        return view('wilayah.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'nullable|string|max:50',
            'tipe' => 'required|in:kecamatan,desa,sls,nks',
        ]);

        $namaTrim = trim($validated['nama']);
        $tipeTrim = strtolower(trim($validated['tipe']));

        // Cek jika wilayah dengan nama dan tipe yang sama persis sudah ada
        $exists = Wilayah::whereRaw('LOWER(TRIM(nama)) = ?', [strtolower($namaTrim)])
            ->where('tipe', $tipeTrim)
            ->first();

        if ($exists) {
            return redirect()->route('wilayah.index')
                ->with('info', "Wilayah \"{$namaTrim}\" dengan tipe \"{$tipeTrim}\" sudah ada dalam sistem, data tidak diduplikasi.");
        }

        Wilayah::create([
            'nama' => $namaTrim,
            'kode' => !empty($validated['kode']) ? trim($validated['kode']) : null,
            'tipe' => $tipeTrim,
        ]);

        return redirect()->route('wilayah.index')->with('success', 'Data wilayah berhasil ditambahkan.');
    }

    public function edit(Wilayah $wilayah)
    {
        return view('wilayah.edit', compact('wilayah'));
    }

    public function update(Request $request, Wilayah $wilayah)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'nullable|string|max:50',
            'tipe' => 'required|in:kecamatan,desa,sls,nks',
        ]);

        $namaTrim = trim($validated['nama']);
        $tipeTrim = strtolower(trim($validated['tipe']));

        // Cek jika wilayah lain sudah menggunakan nama dan tipe yang sama persis
        $duplicate = Wilayah::where('id', '!=', $wilayah->id)
            ->whereRaw('LOWER(TRIM(nama)) = ?', [strtolower($namaTrim)])
            ->where('tipe', $tipeTrim)
            ->first();

        if ($duplicate) {
            return back()->withInput()->with('error', "Gagal memperbarui: Wilayah \"{$namaTrim}\" dengan tipe \"{$tipeTrim}\" sudah ada di database.");
        }

        $wilayah->update([
            'nama' => $namaTrim,
            'kode' => !empty($validated['kode']) ? trim($validated['kode']) : null,
            'tipe' => $tipeTrim,
        ]);

        return redirect()->route('wilayah.index')->with('success', 'Data wilayah berhasil diperbarui.');
    }

    public function destroy(Wilayah $wilayah)
    {
        // Validasi: Cek apakah wilayah ini sedang digunakan pada alokasi petugas
        $jumlahAlokasi = $wilayah->alokasi()->count();
        if ($jumlahAlokasi > 0) {
            return redirect()->route('wilayah.index')
                ->with('error', "Wilayah \"{$wilayah->nama}\" ({$wilayah->tipe}) tidak dapat dihapus karena sedang digunakan oleh {$jumlahAlokasi} data alokasi petugas.");
        }

        $wilayah->delete();
        return redirect()->route('wilayah.index')->with('success', 'Data wilayah berhasil dihapus.');
    }

    /**
     * Hapus Checklist Masal (Bulk Delete) Wilayah Terpilih
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:wilayah,id',
        ]);

        $ids = $request->ids;

        // Cek wilayah yang sedang digunakan pada alokasi petugas
        $usedWilayahs = Wilayah::whereIn('id', $ids)
            ->has('alokasi')
            ->pluck('nama')
            ->toArray();

        if (!empty($usedWilayahs)) {
            $namaList = implode(', ', array_slice($usedWilayahs, 0, 3));
            if (count($usedWilayahs) > 3) {
                $namaList .= ' dan ' . (count($usedWilayahs) - 3) . ' wilayah lainnya';
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => "Tidak dapat menghapus! Wilayah ({$namaList}) sedang digunakan pada alokasi petugas. Hapus atau pindahkan alokasi terkait terlebih dahulu.",
                ], 422);
            }

            return redirect()->route('wilayah.index')
                ->with('error', "Tidak dapat menghapus! Wilayah ({$namaList}) sedang digunakan pada alokasi petugas. Hapus atau pindahkan alokasi terkait terlebih dahulu.");
        }

        $count = count($ids);
        Wilayah::whereIn('id', $ids)->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$count} data master wilayah terpilih.",
            ]);
        }

        return redirect()->route('wilayah.index')
            ->with('success', "Berhasil menghapus {$count} data master wilayah terpilih.");
    }

    /**
     * Endpoint API Autocomplete / Suggestion Wilayah
     */
    public function searchAjax(Request $request)
    {
        $term = trim($request->get('term', ''));
        $query = Wilayah::query();

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('nama', 'like', "%{$term}%")
                  ->orWhere('kode', 'like', "%{$term}%");
            });
        }

        $results = $query->orderBy('nama')
            ->limit(20)
            ->get(['id', 'nama', 'kode', 'tipe']);

        return response()->json($results);
    }
}
