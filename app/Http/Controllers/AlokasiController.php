<?php

namespace App\Http\Controllers;

use App\Models\Alokasi;
use App\Models\Kegiatan;
use App\Models\Wilayah;
use App\Services\AnomaliSyncService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AlokasiController extends Controller
{
    public function index(Request $request)
    {
        $kegiatans = Kegiatan::orderBy('nama')->get();
        $tipeWilayahs = Wilayah::select('tipe')->distinct()->pluck('tipe');
        $query = Alokasi::with(['kegiatan', 'wilayah']);

        if ($request->filled('id_kegiatan')) {
            $query->where('id_kegiatan', $request->id_kegiatan);
        }
        if ($request->filled('tipe_wilayah')) {
            $query->whereHas('wilayah', function ($q) use ($request) {
                $q->where('tipe', $request->tipe_wilayah);
            });
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama_ppl', 'like', "%{$s}%")
                  ->orWhere('nama_pml', 'like', "%{$s}%")
                  ->orWhereHas('wilayah', function ($wq) use ($s) {
                      $wq->where('nama', 'like', "%{$s}%")
                         ->orWhere('kode', 'like', "%{$s}%");
                  });
            });
        }

        $alokasis = $query->paginate(15)->withQueryString();
        return view('alokasi.index', compact('alokasis', 'kegiatans', 'tipeWilayahs'));
    }

    public function create()
    {
        $kegiatans = Kegiatan::orderBy('nama')->get();
        $wilayahs = Wilayah::orderBy('nama')->get(['id', 'nama', 'kode', 'tipe']);
        return view('alokasi.create', compact('kegiatans', 'wilayahs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_kegiatan' => 'required|exists:kegiatan,id',
            'nama_ppl' => 'required|string|max:255',
            'nama_pml' => 'required|string|max:255',
            'nama_wilayah' => 'nullable|string|max:255',
            'tipe_wilayah' => 'nullable|string|max:50',
        ]);

        $wilayahId = null;
        if (!empty($validated['nama_wilayah'])) {
            $namaWilayah = trim($validated['nama_wilayah']);
            $tipeWilayah = !empty($validated['tipe_wilayah']) ? strtolower(trim($validated['tipe_wilayah'])) : 'sls';

            // Cek jika wilayah dengan nama dan tipe yang sama persis sudah ada
            $wilayah = Wilayah::whereRaw('LOWER(TRIM(nama)) = ?', [strtolower($namaWilayah)])
                ->where('tipe', $tipeWilayah)
                ->first();

            // Jika belum ada nama + tipe yang sama, buat wilayah baru
            if (!$wilayah) {
                $wilayah = Wilayah::create([
                    'nama' => $namaWilayah,
                    'tipe' => $tipeWilayah,
                ]);
            }

            $wilayahId = $wilayah->id;
        }

        Alokasi::create([
            'id_kegiatan' => $validated['id_kegiatan'],
            'nama_ppl' => $validated['nama_ppl'],
            'nama_pml' => $validated['nama_pml'],
            'id_wilayah' => $wilayahId,
        ]);

        return redirect()->route('alokasi.index', ['id_kegiatan' => $request->id_kegiatan])
            ->with('success', 'Data alokasi berhasil disimpan.');
    }

    public function edit(Alokasi $alokasi)
    {
        $kegiatans = Kegiatan::orderBy('nama')->get();
        $wilayahs = Wilayah::orderBy('nama')->get(['id', 'nama', 'kode', 'tipe']);
        return view('alokasi.edit', compact('alokasi', 'kegiatans', 'wilayahs'));
    }

    public function update(Request $request, Alokasi $alokasi)
    {
        $validated = $request->validate([
            'id_kegiatan' => 'required|exists:kegiatan,id',
            'nama_ppl' => 'required|string|max:255',
            'nama_pml' => 'required|string|max:255',
            'nama_wilayah' => 'nullable|string|max:255',
            'tipe_wilayah' => 'nullable|string|max:50',
        ]);

        $wilayahId = null;
        if (!empty($validated['nama_wilayah'])) {
            $namaWilayah = trim($validated['nama_wilayah']);
            $tipeWilayah = !empty($validated['tipe_wilayah']) ? strtolower(trim($validated['tipe_wilayah'])) : 'sls';

            // Cek jika wilayah dengan nama dan tipe yang sama persis sudah ada
            $wilayah = Wilayah::whereRaw('LOWER(TRIM(nama)) = ?', [strtolower($namaWilayah)])
                ->where('tipe', $tipeWilayah)
                ->first();

            if (!$wilayah) {
                $wilayah = Wilayah::create([
                    'nama' => $namaWilayah,
                    'tipe' => $tipeWilayah,
                ]);
            }

            $wilayahId = $wilayah->id;
        }

        $alokasi->update([
            'id_kegiatan' => $validated['id_kegiatan'],
            'nama_ppl' => $validated['nama_ppl'],
            'nama_pml' => $validated['nama_pml'],
            'id_wilayah' => $wilayahId,
        ]);

        return redirect()->route('alokasi.index', ['id_kegiatan' => $request->id_kegiatan])
            ->with('success', 'Data alokasi berhasil diperbarui.');
    }

    public function destroy(Alokasi $alokasi)
    {
        $alokasi->delete();
        return back()->with('success', 'Data alokasi berhasil dihapus.');
    }

    /**
     * Tampilkan form import alokasi via Excel/CSV
     */
    public function showImport()
    {
        $kegiatans = Kegiatan::orderBy('nama')->get();
        return view('alokasi.import', compact('kegiatans'));
    }

    /**
     * Download template Excel untuk Alokasi Petugas
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Alokasi');

        // Header
        $headers = ['nama_ppl', 'nama_pml', 'wilayah'];
        $sheet->fromArray([$headers], null, 'A1');

        // Contoh Data
        $sampleData = [
            ['Budi Santoso', 'Siti Rahmawati', 'SLS 001 RT 01'],
            ['Ahmad Fauzi', 'Siti Rahmawati', 'SLS 002 RT 02'],
            ['Dewi Lestari', 'Joko Prayitno', 'Desa Sukamaju'],
        ];
        $sheet->fromArray($sampleData, null, 'A2');

        // Styling Header Kalem
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '1E293B']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'EDF2FE']
            ],
        ];
        $sheet->getStyle('A1:C1')->applyFromArray($headerStyle);
        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Template_Alokasi_Petugas_LAKONI.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Eksekusi proses import
     */
    public function import(Request $request)
    {
        $request->validate([
            'id_kegiatan' => 'required|exists:kegiatan,id',
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
        ]);

        $file = $request->file('file');
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet ? $worksheet->toArray() : [];
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca file alokasi. Pastikan format file Excel/CSV valid dan tidak dikunci/rusak.');
        }

        if (count($rows) < 2) {
            return back()->with('error', 'File tidak memiliki baris data yang cukup.');
        }

        $header = array_map(function ($h) {
            return strtolower(trim((string)$h));
        }, $rows[0]);

        $colPpl = -1;
        $colPml = -1;
        $colWilayah = -1;

        foreach ($header as $idx => $name) {
            if (str_contains($name, 'ppl')) $colPpl = $idx;
            elseif (str_contains($name, 'pml')) $colPml = $idx;
            elseif (str_contains($name, 'wilayah') || str_contains($name, 'desa') || str_contains($name, 'sls')) $colWilayah = $idx;
        }

        if ($colPpl === -1 || $colPml === -1) {
            return back()->with('error', 'Kolom nama PPL dan PML tidak terdeteksi pada header file. Pastikan ada kolom "nama_ppl" dan "nama_pml".');
        }

        $count = 0;
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $ppl = isset($row[$colPpl]) ? trim((string)$row[$colPpl]) : '';
            $pml = isset($row[$colPml]) ? trim((string)$row[$colPml]) : '';
            if (empty($ppl) && empty($pml)) continue;

            $wilayahId = null;
            if ($colWilayah !== -1 && !empty($row[$colWilayah])) {
                $wilayahName = trim((string)$row[$colWilayah]);
                $wilayah = Wilayah::firstOrCreate(
                    ['nama' => $wilayahName],
                    ['tipe' => 'sls']
                );
                $wilayahId = $wilayah->id;
            }

            Alokasi::create([
                'id_kegiatan' => $request->id_kegiatan,
                'nama_ppl' => $ppl,
                'nama_pml' => $pml,
                'id_wilayah' => $wilayahId,
            ]);
            $count++;
        }

        return redirect()->route('alokasi.index', ['id_kegiatan' => $request->id_kegiatan])
            ->with('success', "Berhasil mengimpor {$count} data alokasi petugas.");
    }

    /**
     * Sinkronisasi pemetaan alokasi petugas ke data anomali
     */
    public function sync(Request $request)
    {
        $idKegiatan = $request->filled('id_kegiatan') ? (int)$request->id_kegiatan : null;
        $result = AnomaliSyncService::syncAlokasi($idKegiatan);

        $msg = "Sinkronisasi berhasil! {$result['updated']} dari {$result['total']} data anomali berhasil diperbarui pemetaan petugasnya.";
        return back()->with('success', $msg);
    }

    /**
     * Hapus alokasi secara masal berdasarkan filter kegiatan dan jenis wilayah
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'id_kegiatan' => 'required|exists:kegiatan,id',
            'tipe_wilayah' => 'nullable|string',
        ]);

        $query = Alokasi::where('id_kegiatan', $request->id_kegiatan);

        if ($request->filled('tipe_wilayah') && $request->tipe_wilayah !== 'all') {
            $query->whereHas('wilayah', function ($q) use ($request) {
                $q->where('tipe', $request->tipe_wilayah);
            });
        }

        $deletedCount = $query->count();
        if ($deletedCount === 0) {
            return back()->with('error', 'Tidak ada data alokasi yang sesuai dengan kriteria untuk dihapus.');
        }

        $query->delete();

        // Otomatis sinkronkan anomali kegiatan tersebut agar anomali me-reset/menyesuaikan alokasinya
        AnomaliSyncService::syncAlokasi((int)$request->id_kegiatan);

        return redirect()->route('alokasi.index', ['id_kegiatan' => $request->id_kegiatan])
            ->with('success', "Berhasil menghapus {$deletedCount} data alokasi petugas dan memperbarui data anomali.");
    }
}
