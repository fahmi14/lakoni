<?php

namespace App\Http\Controllers;

use App\Models\Wilayah;
use Illuminate\Http\Request;

class WilayahController extends Controller
{
    public function index(Request $request)
    {
        $query = Wilayah::query();
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('nama', 'like', "%{$s}%")->orWhere('kode', 'like', "%{$s}%");
        }
        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }
        $wilayahs = $query->orderBy('nama')->paginate(15)->withQueryString();
        return view('wilayah.index', compact('wilayahs'));
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
        Wilayah::create($validated);
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
        $wilayah->update($validated);
        return redirect()->route('wilayah.index')->with('success', 'Data wilayah berhasil diperbarui.');
    }

    public function destroy(Wilayah $wilayah)
    {
        $wilayah->delete();
        return redirect()->route('wilayah.index')->with('success', 'Data wilayah berhasil dihapus.');
    }
}
