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

        // Cek jika ada intended URL (misal dari direct link konfirmasi anomali)
        $intended = session()->pull('url.intended');
        if ($intended) {
            return redirect()->to($intended);
        }

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
            ->where(function ($q) use ($alokasiIds, $nama, $tipe) {
                if ($alokasiIds->isNotEmpty()) {
                    $q->whereIn('id_alokasi', $alokasiIds);
                } else {
                    // Safe fallback: hanya cocokkan jika nama petugas muncul di atribut kunci petugas (PPL / PML / Pencacah / Pengawas)
                    $targetKeys = ($tipe === 'pml') 
                        ? ['pml', 'pengawas', 'pemeriksa', 'nama_pml', 'nama pml'] 
                        : ['ppl', 'pencacah', 'petugas', 'nama_ppl', 'nama ppl'];

                    $q->where(function ($subQ) use ($nama, $targetKeys) {
                        foreach ($targetKeys as $k) {
                            $subQ->orWhere("detail->{$k}", 'like', "%{$nama}%");
                        }
                    });
                }
            });

        // Hitung count untuk tab status (semua, belum konfirmasi, sudah konfirmasi)
        $countSemua = (clone $query)->count();
        $countBelum = (clone $query)->where(function ($q) {
            $q->where('status', 'belum')->orWhereNull('status');
        })->count();
        $countSudah = (clone $query)->whereNotIn('status', ['belum'])->whereNotNull('status')->count();

        // Filter status
        if ($request->filled('status')) {
            if ($request->status === 'belum') {
                $query->where(function ($q) {
                    $q->where('status', 'belum')->orWhereNull('status');
                });
            } elseif ($request->status === 'sudah') {
                $query->whereNotIn('status', ['belum'])->whereNotNull('status');
            } else {
                $query->where('status', $request->status);
            }
        }

        // Filter Template Konfirmasi
        if ($request->filled('id_konfirmasi')) {
            $query->where('id_konfirmasi', $request->id_konfirmasi);
        }

        // Custom Search: Mencari di semua variabel (detail JSON, nama konfirmasi, status, dll.)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('detail', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhereHas('konfirmasi', function ($kq) use ($search) {
                      $kq->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        // Ambil daftar template konfirmasi yang relevan dengan anomali kegiatan ini
        $availableKonfirmasis = Konfirmasi::whereHas('anomali', function ($aq) use ($kegiatanId) {
            $aq->where('id_kegiatan', $kegiatanId);
        })->get();

        // Ambil semua keys variabel detail JSON yang ada pada kegiatan ini
        $detailKeys = [];
        if (!empty($kegiatan->kolom) && is_array($kegiatan->kolom)) {
            $detailKeys = $kegiatan->kolom;
        } else {
            $sampleAnomali = Anomali::where('id_kegiatan', $kegiatanId)->first();
            $detailKeys = ($sampleAnomali && is_array($sampleAnomali->detail)) ? array_keys($sampleAnomali->detail) : [];
        }

        // Kolom yang dipilih untuk ditampilkan (default: jika ada di request atau ambil 4-5 pertama)
        $selectedColumns = $request->input('columns', []);
        if (empty($selectedColumns) && !empty($detailKeys)) {
            // Default tampilkan hingga 4 kolom pertama
            $selectedColumns = array_slice($detailKeys, 0, 4);
        }

        $anomalis = $query->latest('id')->paginate(15)->withQueryString();

        return view('petugas.anomali-list', compact(
            'kegiatan',
            'anomalis',
            'tipe',
            'nama',
            'countSemua',
            'countBelum',
            'countSudah',
            'availableKonfirmasis',
            'detailKeys',
            'selectedColumns'
        ));
    }

    /**
     * Langkah 4: Tampilkan Form Konfirmasi Pertanyaan (JSON)
     */
    public function showKonfirmasi(Anomali $anomali)
    {
        $kegiatanId = session('petugas_kegiatan_id');
        $nama = session('petugas_nama');

        if (!$kegiatanId || !$nama) {
            // Simpan intended URL agar setelah login/pilih petugas langsung diarahkan ke anomali ini
            session(['url.intended' => route('petugas.konfirmasi', $anomali)]);
            return redirect()->route('petugas.kegiatan')
                ->with('info', 'Silakan pilih kegiatan dan identitas petugas Anda untuk melanjutkan konfirmasi.');
        }

        $konfirmasi = $anomali->konfirmasi;
        if (!$konfirmasi) {
            return redirect()->route('petugas.anomali.list')
                ->with('error', 'Template konfirmasi untuk lembar anomali ini tidak ditemukan.');
        }

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
