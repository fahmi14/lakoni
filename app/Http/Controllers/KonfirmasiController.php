<?php

namespace App\Http\Controllers;

use App\Models\Konfirmasi;
use Illuminate\Http\Request;

class KonfirmasiController extends Controller
{
    public function index(Request $request)
    {
        $query = Konfirmasi::withCount('anomali');
        if ($request->filled('search')) {
            $query->where('nama', 'like', "%{$request->search}%");
        }
        $konfirmasis = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();
        return view('konfirmasi.index', compact('konfirmasis'));
    }

    public function create()
    {
        return view('konfirmasi.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'questions' => 'required|array|min:1',
            'questions.*.teks' => 'required|string',
            'questions.*.tipe' => 'required|in:pilihan,terbuka',
            'questions.*.opsi' => 'nullable|string', // comma-separated if pilihan
            'jawaban_default' => 'nullable|integer',
            'sesuai' => 'nullable|integer',
        ]);

        $pertanyaanJson = [];
        foreach ($request->questions as $q) {
            $opsiArray = [];
            if ($q['tipe'] === 'pilihan' && !empty($q['opsi'])) {
                $opsiArray = array_values(array_filter(array_map('trim', explode(',', $q['opsi']))));
            }
            $pertanyaanJson[] = [
                'teks' => $q['teks'],
                'tipe' => $q['tipe'],
                'opsi' => $opsiArray,
            ];
        }

        Konfirmasi::create([
            'nama' => $request->nama,
            'pertanyaan' => $pertanyaanJson,
            'jawaban' => $request->filled('jawaban_default') ? (int)$request->jawaban_default : null,
            'sesuai' => $request->filled('sesuai') ? (int)$request->sesuai : null,
        ]);

        return redirect()->route('konfirmasi.index')->with('success', 'Template konfirmasi berhasil disimpan.');
    }

    public function edit(Konfirmasi $konfirmasi)
    {
        return view('konfirmasi.edit', compact('konfirmasi'));
    }

    public function update(Request $request, Konfirmasi $konfirmasi)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'questions' => 'required|array|min:1',
            'questions.*.teks' => 'required|string',
            'questions.*.tipe' => 'required|in:pilihan,terbuka',
            'questions.*.opsi' => 'nullable|string',
            'jawaban_default' => 'nullable|integer',
            'sesuai' => 'nullable|integer',
        ]);

        $pertanyaanJson = [];
        foreach ($request->questions as $q) {
            $opsiArray = [];
            if ($q['tipe'] === 'pilihan' && !empty($q['opsi'])) {
                $opsiArray = array_values(array_filter(array_map('trim', explode(',', $q['opsi']))));
            }
            $pertanyaanJson[] = [
                'teks' => $q['teks'],
                'tipe' => $q['tipe'],
                'opsi' => $opsiArray,
            ];
        }

        $konfirmasi->update([
            'nama' => $request->nama,
            'pertanyaan' => $pertanyaanJson,
            'jawaban' => $request->filled('jawaban_default') ? (int)$request->jawaban_default : null,
            'sesuai' => $request->filled('sesuai') ? (int)$request->sesuai : null,
        ]);

        return redirect()->route('konfirmasi.index')->with('success', 'Template konfirmasi berhasil diperbarui.');
    }

    public function destroy(Konfirmasi $konfirmasi)
    {
        $konfirmasi->delete();
        return redirect()->route('konfirmasi.index')->with('success', 'Template konfirmasi berhasil dihapus.');
    }
}
