<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\Anomali;
use App\Models\Alokasi;
use App\Models\Konfirmasi;
use Illuminate\Http\Request;

class PetugasController extends Controller
{
    /**
     * Langkah 1: Input Kode Kegiatan & Validasi Rentang Waktu
     */
    public function showCheckKegiatan()
    {
        return view('petugas.check-kegiatan');
    }

    /**
     * Validasi Kode Kegiatan
     */
    public function verifyKegiatan(Request $request)
    {
        $request->validate([
            'kode' => 'required|string',
        ], [
            'kode.required' => 'Kode kegiatan wajib diisi.',
        ]);

        $kegiatan = Kegiatan::where('kode', trim($request->kode))->first();

        if (!$kegiatan) {
            return back()->with('error', 'Kode kegiatan tidak ditemukan.');
        }

        // Cek rentang waktu kegiatan
        if (!$kegiatan->isAktif()) {
            return back()->with('error', "Kegiatan '{$kegiatan->nama}' saat ini di luar rentang waktu aktif ({$kegiatan->mulai->format('d/m/Y')} s/d {$kegiatan->selesai->format('d/m/Y')}).");
        }

        // Simpan kegiatan ke session
        session(['petugas_kegiatan_id' => $kegiatan->id]);

        return redirect()->route('petugas.select-petugas');
    }

    /**
     * Langkah 2: Pilih Peran (PML / PPL) & Pilih Nama Petugas
     */
    public function showSelectPetugas(Request $request)
    {
        $kegiatanId = session('petugas_kegiatan_id');
        if (!$kegiatanId) {
            return redirect()->route('petugas.kegiatan')->with('error', 'Silakan masukkan kode kegiatan terlebih dahulu.');
        }

        $kegiatan = Kegiatan::findOrFail($kegiatanId);

        // Ambil list nama PML dan PPL unik dari tabel alokasi untuk kegiatan ini
        $alokasis = Alokasi::where('id_kegiatan', $kegiatanId)->get();

        $daftarPml = $alokasis->pluck('nama_pml')->filter()->unique()->values();
        $daftarPpl = $alokasis->pluck('nama_ppl')->filter()->unique()->values();

        return view('petugas.select-petugas', compact('kegiatan', 'daftarPml', 'daftarPpl'));
    }

    /**
     * Simpan pilihan nama & tipe petugas ke session
     */
    public function setPetugas(Request $request)
    {
        $request->validate([
            'tipe' => 'required|in:pml,ppl',
            'nama' => 'required|string',
        ]);

        session([
            'petugas_tipe' => $request->tipe,
            'petugas_nama' => $request->nama,
        ]);

        return redirect()->route('petugas.anomali.list');
    }

    /**
     * Langkah 3: Daftar Anomali untuk Petugas Tersebut
     */
    public function listAnomali(Request $request)
    {
        $kegiatanId = session('petugas_kegiatan_id');
        $tipe = session('petugas_tipe');
        $nama = session('petugas_nama');

        if (!$kegiatanId || !$nama) {
            return redirect()->route('petugas.kegiatan');
        }

        $kegiatan = Kegiatan::findOrFail($kegiatanId);

        // Cari alokasi ID yang sesuai dengan nama petugas ini
        $alokasiIds = Alokasi::where('id_kegiatan', $kegiatanId)
            ->where(function ($q) use ($tipe, $nama) {
                if ($tipe === 'pml') {
                    $q->where('nama_pml', $nama);
                } else {
                    $q->where('nama_ppl', $nama);
                }
            })->pluck('id');

        // Query anomali untuk kegiatan ini yang teralokasi ke petugas ini (atau jika alokasi dicocokkan via teks di detail)
        $query = Anomali::with(['konfirmasi', 'alokasi'])
            ->where('id_kegiatan', $kegiatanId)
            ->where(function ($q) use ($alokasiIds, $nama) {
                if ($alokasiIds->isNotEmpty()) {
                    $q->whereIn('id_alokasi', $alokasiIds);
                } else {
                    // fallback: jika tidak ada FK, cek teks nama di kolom JSON detail
                    $q->where('detail', 'like', "%{$nama}%");
                }
            });

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $anomalis = $query->paginate(15)->withQueryString();

        return view('petugas.anomali-list', compact('kegiatan', 'anomalis', 'tipe', 'nama'));
    }

    /**
     * Langkah 4: Tampilkan Form Konfirmasi Pertanyaan (JSON)
     */
    public function showKonfirmasi(Anomali $anomali)
    {
        $kegiatanId = session('petugas_kegiatan_id');
        $nama = session('petugas_nama');

        if (!$kegiatanId || !$nama) {
            return redirect()->route('petugas.kegiatan');
        }

        $konfirmasi = $anomali->konfirmasi;
        $pertanyaanList = $konfirmasi->pertanyaan ?? [];

        return view('petugas.konfirmasi-form', compact('anomali', 'konfirmasi', 'pertanyaanList', 'nama'));
    }

    /**
     * Langkah 5: Simpan Hasil Konfirmasi Petugas
     */
    public function saveKonfirmasi(Request $request, Anomali $anomali)
    {
        $nama = session('petugas_nama');
        $konfirmasi = $anomali->konfirmasi;
        $pertanyaanList = $konfirmasi->pertanyaan ?? [];

        $answers = $request->input('answers', []);
        
        // Cek kesesuaian: jika ada pertanyaan pilihan pertama, bandingkan dengan $konfirmasi->sesuai
        $status = 'dikonfirmasi';
        if ($konfirmasi->sesuai !== null && isset($answers[0])) {
            $firstAnswerIndex = (int)$answers[0];
            if ($firstAnswerIndex === (int)$konfirmasi->sesuai) {
                $status = 'sesuai';
            } else {
                $status = 'tidak_sesuai';
            }
        }

        $anomali->update([
            'hasil_konfirmasi' => $answers,
            'status' => $status,
            'petugas_pengonfirmasi' => $nama,
            'dikonfirmasi_pada' => now(),
        ]);

        return redirect()->route('petugas.anomali.list')
            ->with('success', 'Konfirmasi anomali berhasil disimpan.');
    }
}
