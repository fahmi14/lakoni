<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use Illuminate\Http\Request;

class KegiatanController extends Controller
{
    public function index(Request $request)
    {
        $query = Kegiatan::withCount(['anomali', 'alokasi']);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('nama', 'like', "%{$s}%")->orWhere('kode', 'like', "%{$s}%");
        }
        $kegiatans = $query->orderBy('mulai', 'desc')->paginate(10)->withQueryString();
        return view('kegiatan.index', compact('kegiatans'));
    }

    public function create()
    {
        return view('kegiatan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:50|unique:kegiatan,kode',
            'nama' => 'required|string|max:255',
            'mulai' => 'required|date',
            'selesai' => 'required|date|after_or_equal:mulai',
            'kolom_input' => 'nullable|string',
            'template_wa' => 'nullable|string',
        ]);

        $kolom = [];
        if (!empty($validated['kolom_input'])) {
            $kolom = array_filter(array_map('trim', explode(',', $validated['kolom_input'])));
        }

        Kegiatan::create([
            'kode' => $validated['kode'],
            'nama' => $validated['nama'],
            'mulai' => $validated['mulai'],
            'selesai' => $validated['selesai'],
            'kolom' => array_values($kolom),
            'template_wa' => $validated['template_wa'] ?? null,
        ]);

        return redirect()->route('kegiatan.index')->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    public function edit(Kegiatan $kegiatan)
    {
        return view('kegiatan.edit', compact('kegiatan'));
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:50|unique:kegiatan,kode,' . $kegiatan->id,
            'nama' => 'required|string|max:255',
            'mulai' => 'required|date',
            'selesai' => 'required|date|after_or_equal:mulai',
            'kolom_input' => 'nullable|string',
            'template_wa' => 'nullable|string',
        ]);

        $kolom = [];
        if (!empty($validated['kolom_input'])) {
            $kolom = array_filter(array_map('trim', explode(',', $validated['kolom_input'])));
        }

        $kegiatan->update([
            'kode' => $validated['kode'],
            'nama' => $validated['nama'],
            'mulai' => $validated['mulai'],
            'selesai' => $validated['selesai'],
            'kolom' => array_values($kolom),
            'template_wa' => $validated['template_wa'] ?? null,
        ]);

        return redirect()->route('kegiatan.index')->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroy(Kegiatan $kegiatan)
    {
        \DB::transaction(function () use ($kegiatan) {
            $kegiatan->anomali()->delete();
            $kegiatan->alokasi()->delete();
            $kegiatan->delete();
        });

        return redirect()->route('kegiatan.index')->with('success', 'Kegiatan beserta seluruh alokasi petugas dan data anomalinya berhasil dihapus.');
    }
}
