<?php

namespace App\Http\Controllers;

use App\Models\Anomali;
use App\Models\Kegiatan;
use App\Models\Konfirmasi;
use App\Models\Alokasi;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Storage;

class AnomaliController extends Controller
{
    public function index(Request $request)
    {
        $kegiatans = Kegiatan::orderBy('nama')->get();
        $konfirmasis = Konfirmasi::orderBy('nama')->get();

        $query = Anomali::with(['kegiatan', 'konfirmasi', 'alokasi']);

        if ($request->filled('id_kegiatan')) {
            $query->where('id_kegiatan', $request->id_kegiatan);
        }
        if ($request->filled('id_konfirmasi')) {
            $query->where('id_konfirmasi', $request->id_konfirmasi);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $anomalis = $query->latest()->paginate(15)->withQueryString();

        return view('anomali.index', compact('anomalis', 'kegiatans', 'konfirmasis'));
    }

    public function create()
    {
        $kegiatans = Kegiatan::orderBy('nama')->get();
        $konfirmasis = Konfirmasi::orderBy('nama')->get();
        $alokasis = Alokasi::orderBy('nama_ppl')->get();
        return view('anomali.create', compact('kegiatans', 'konfirmasis', 'alokasis'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_kegiatan' => 'required|exists:kegiatan,id',
            'id_konfirmasi' => 'required|exists:konfirmasi,id',
            'id_alokasi' => 'nullable|exists:alokasi,id',
            'detail_json' => 'required|string',
        ]);

        $detail = json_decode($validated['detail_json'], true);
        if (!$detail) {
            return back()->with('error', 'Format detail JSON tidak valid.')->withInput();
        }

        Anomali::create([
            'id_kegiatan' => $validated['id_kegiatan'],
            'id_konfirmasi' => $validated['id_konfirmasi'],
            'id_alokasi' => $validated['id_alokasi'],
            'kolom' => array_keys($detail),
            'detail' => $detail,
            'status' => 'belum',
        ]);

        return redirect()->route('anomali.index')->with('success', 'Data anomali berhasil dibuat.');
    }

    public function show(Anomali $anomali)
    {
        return view('anomali.show', compact('anomali'));
    }

    public function destroy(Anomali $anomali)
    {
        $anomali->delete();
        return back()->with('success', 'Data anomali berhasil dihapus.');
    }

    /**
     * Download Template Excel untuk Import Anomali
     */
    public function downloadTemplate(Request $request)
    {
        $kegiatan = null;
        if ($request->filled('id_kegiatan')) {
            $kegiatan = Kegiatan::find($request->id_kegiatan);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Anomali');

        if ($kegiatan && !empty($kegiatan->kolom)) {
            $headers = $kegiatan->kolom;
            if (!in_array('no_hp', array_map('strtolower', $headers))) {
                $headers[] = 'no_hp';
            }
        } else {
            $headers = ['NKS', 'Nama Responden', 'Komoditas', 'Luas Lahan', 'Nilai Produksi', 'Nama PPL', 'Nama PML', 'Wilayah', 'no_hp', 'Catatan Anomali'];
        }

        $sheet->fromArray([$headers], null, 'A1');

        $sampleRow = [
            '100234', 'Haji Mansyur', 'Padi Sawah', '2.5 Ha', '35 Ton (Tinggi)', 'Budi Santoso', 'Siti Rahmawati', 'SLS 001 RT 01', '081234567890', 'Produksi tinggi perlu dicek lapangan'
        ];
        $sampleRows = [
            array_slice(array_pad($sampleRow, count($headers), '-'), 0, count($headers))
        ];
        $sheet->fromArray($sampleRows, null, 'A2');

        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '1E293B']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'EDF2FE']
            ],
        ];
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray($headerStyle);
        for ($i = 1; $i <= count($headers); $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Template_Import_Anomali_LAKONI.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Download Seluruh Respon / Hasil Konfirmasi Petugas dalam Bentuk Excel
     * Detail JSON dipecah menjadi masing-masing kolom Excel mandiri.
     * Wajib memilih Kegiatan dan Template Konfirmasi terlebih dahulu.
     */
    public function downloadRespon(Request $request)
    {
        // Validasi wajib pilih kegiatan dan template konfirmasi
        if (!$request->filled('id_kegiatan') || !$request->filled('id_konfirmasi')) {
            return redirect()->route('anomali.index', $request->query())
                ->with('error', 'Silakan pilih Kegiatan dan Template Konfirmasi pada filter terlebih dahulu sebelum mengunduh respon Excel.');
        }

        $kegiatan = Kegiatan::findOrFail($request->id_kegiatan);
        $konfirmasi = Konfirmasi::findOrFail($request->id_konfirmasi);

        $query = Anomali::with(['kegiatan', 'konfirmasi', 'alokasi'])
            ->where('id_kegiatan', $kegiatan->id)
            ->where('id_konfirmasi', $konfirmasi->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $anomalis = $query->latest()->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Respon Konfirmasi');

        // 1. Kumpulkan seluruh kolom detail JSON secara unik untuk kegiatan & konfirmasi ini
        $detailKeys = [];
        if (!empty($kegiatan->kolom)) {
            $detailKeys = $kegiatan->kolom;
        }

        foreach ($anomalis as $a) {
            if (is_array($a->detail)) {
                foreach (array_keys($a->detail) as $k) {
                    if (!in_array($k, $detailKeys)) {
                        $detailKeys[] = $k;
                    }
                }
            }
        }

        // 2. Kumpulkan pertanyaan dari template konfirmasi terpilih
        $pertanyaanHeaders = [];
        if (is_array($konfirmasi->pertanyaan)) {
            foreach ($konfirmasi->pertanyaan as $qIdx => $q) {
                $qTeks = $q['teks'] ?? "Pertanyaan #".($qIdx + 1);
                $pertanyaanHeaders[] = $qTeks;
            }
        }
        if (empty($pertanyaanHeaders)) {
            $pertanyaanHeaders = ['Hasil Jawaban Konfirmasi'];
        }

        // 3. Susun Header Spreadsheet
        $baseHeaders = [
            'ID Anomali',
            'Kode Kegiatan',
            'Nama Kegiatan',
            'Template Konfirmasi',
            'Nama PPL',
            'Nama PML',
            'Status Konfirmasi',
            'Petugas Pengonfirmasi',
            'Dikonfirmasi Pada',
        ];

        // Gabungkan Base Headers + Kolom Detail JSON (Pecah per kolom) + Kolom Pertanyaan
        $allHeaders = array_merge($baseHeaders, $detailKeys, $pertanyaanHeaders);
        $sheet->fromArray([$allHeaders], null, 'A1');

        // 4. Susun Baris Data
        $rowsData = [];
        foreach ($anomalis as $a) {
            $statusText = match ($a->status) {
                'sesuai' => 'Sesuai (Valid)',
                'tidak_sesuai' => 'Tidak Sesuai (Perlu Revisi)',
                'dikonfirmasi' => 'Telah Dikonfirmasi',
                default => 'Menunggu Konfirmasi',
            };

            $row = [
                $a->id,
                $kegiatan->kode,
                $kegiatan->nama,
                $konfirmasi->nama,
                $a->alokasi->nama_ppl ?? ($a->detail['Nama PPL'] ?? ($a->detail['PPL'] ?? '-')),
                $a->alokasi->nama_pml ?? ($a->detail['Nama PML'] ?? ($a->detail['PML'] ?? '-')),
                $statusText,
                $a->petugas_pengonfirmasi ?? '-',
                $a->dikonfirmasi_pada ? $a->dikonfirmasi_pada->format('d/m/Y H:i:s') : '-',
            ];

            // Masukkan nilai kolom detail JSON per kolom mandiri
            $detail = is_array($a->detail) ? $a->detail : [];
            foreach ($detailKeys as $dKey) {
                $row[] = isset($detail[$dKey]) ? (string)$detail[$dKey] : '';
            }

            // Masukkan jawaban untuk setiap pertanyaan konfirmasi
            $hasilKonf = is_array($a->hasil_konfirmasi) ? $a->hasil_konfirmasi : [];
            $qList = $konfirmasi->pertanyaan ?? [];

            $qAnswerMap = [];
            foreach ($qList as $qIdx => $qItem) {
                $qTeks = $qItem['teks'] ?? "Pertanyaan #".($qIdx + 1);
                $ansVal = $hasilKonf[$qIdx] ?? '';
                if ($ansVal !== '' && isset($qItem['tipe']) && $qItem['tipe'] === 'pilihan') {
                    $ansVal = $qItem['opsi'][$ansVal] ?? $ansVal;
                }
                $qAnswerMap[$qTeks] = $ansVal;
            }

            foreach ($pertanyaanHeaders as $pH) {
                $row[] = isset($qAnswerMap[$pH]) ? (string)$qAnswerMap[$pH] : '';
            }

            $rowsData[] = $row;
        }

        if (!empty($rowsData)) {
            $sheet->fromArray($rowsData, null, 'A2');
        }

        // 5. Penataan Gaya Header Spreadsheet Kalem & Modern
        $totalCols = count($allHeaders);
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

        // Header Base Color (Biru Utama #4F6FF6)
        $baseCount = count($baseHeaders);
        $baseLastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($baseCount);
        $sheet->getStyle("A1:{$baseLastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F6FF6']
            ],
            'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
        ]);

        // Header Detail JSON yang Dipecah (Aksen Teal Kalem #0284C7)
        if (!empty($detailKeys)) {
            $startDetailCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($baseCount + 1);
            $endDetailCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($baseCount + count($detailKeys));
            $sheet->getStyle("{$startDetailCol}1:{$endDetailCol}1")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0284C7']
                ],
                'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            ]);
        }

        // Header Hasil Pertanyaan Konfirmasi (Hijau Zamrud Kalem #059669)
        if (!empty($pertanyaanHeaders)) {
            $startQCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($baseCount + count($detailKeys) + 1);
            $sheet->getStyle("{$startQCol}1:{$lastCol}1")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '059669']
                ],
                'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            ]);
        }

        $sheet->getRowDimension(1)->setRowHeight(30);

        // AutoSize seluruh kolom
        for ($i = 1; $i <= $totalCols; $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $cleanKegiatanName = preg_replace('/[^A-Za-z0-9_-]/', '_', $kegiatan->kode);
        $fileName = 'Respon_Konfirmasi_' . $cleanKegiatanName . '_' . date('Ymd_His') . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Tampilan Langkah 1 Import File Excel/CSV (Mendukung > 1 File)
     */
    public function showImport()
    {
        $kegiatans = Kegiatan::orderBy('nama')->get();
        $konfirmasis = Konfirmasi::orderBy('nama')->get();
        return view('anomali.import', compact('kegiatans', 'konfirmasis'));
    }

    /**
     * Langkah 2: Upload Multi-file & Tampilkan Preview Sheet / Tabs & Kolom
     */
    public function previewImport(Request $request)
    {
        $request->validate([
            'id_kegiatan' => 'required|exists:kegiatan,id',
            'id_konfirmasi' => 'required|exists:konfirmasi,id',
            'files' => 'required|array|min:1',
            'files.*' => 'required|file|mimes:xlsx,xls,csv,txt|max:20480',
        ]);

        $kegiatan = Kegiatan::findOrFail($request->id_kegiatan);
        $konfirmasi = Konfirmasi::findOrFail($request->id_konfirmasi);

        $filePaths = [];
        $sheetsData = [];
        $allHeadersSet = [];

        foreach ($request->file('files') as $file) {
            $origName = $file->getClientOriginalName();
            $path = $file->store('temp_imports');
            $filePaths[] = $path;

            $fullPath = Storage::path($path);
            $spreadsheet = IOFactory::load($fullPath);

            foreach ($spreadsheet->getSheetNames() as $sheetIndex => $sheetName) {
                $sheet = $spreadsheet->getSheet($sheetIndex);
                $rows = $sheet->toArray();
                if (empty($rows)) continue;

                $headers = array_filter(array_map('trim', (array)$rows[0]));
                $previewRows = array_slice($rows, 1, 5);

                foreach ($headers as $h) {
                    $allHeadersSet[$h] = true;
                }

                $sheetsData[] = [
                    'fileName' => $origName,
                    'name' => $sheetName,
                    'headers' => $headers,
                    'previewRows' => $previewRows,
                    'totalRows' => count($rows) - 1,
                ];
            }
        }

        $allHeaders = array_keys($allHeadersSet);

        return view('anomali.preview', compact('kegiatan', 'konfirmasi', 'filePaths', 'sheetsData', 'allHeaders'));
    }

    /**
     * Langkah 3: Eksekusi Import dari Banyak File dengan Pemetaan Wilayah
     */
    public function executeImport(Request $request)
    {
        $request->validate([
            'id_kegiatan' => 'required|exists:kegiatan,id',
            'id_konfirmasi' => 'required|exists:konfirmasi,id',
            'file_paths' => 'required|array|min:1',
            'selected_columns' => 'required|array|min:1',
            'kolom_wilayah' => 'nullable|string',
        ]);

        $kegiatan = Kegiatan::findOrFail($request->id_kegiatan);
        $konfirmasi = Konfirmasi::findOrFail($request->id_konfirmasi);

        $selectedColumns = $request->selected_columns;
        $kolomWilayah = $request->kolom_wilayah;

        if (empty($kegiatan->kolom)) {
            $kegiatan->update(['kolom' => array_values($selectedColumns)]);
        }

        $alokasiList = Alokasi::with('wilayah')->where('id_kegiatan', $kegiatan->id)->get();
        $wilayahList = Wilayah::all();

        $totalInserted = 0;
        $totalDuplicate = 0;

        foreach ($request->file_paths as $storedPath) {
            $filePath = Storage::path($storedPath);
            if (!file_exists($filePath)) continue;

            $spreadsheet = IOFactory::load($filePath);

            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $rows = $sheet->toArray();
                if (count($rows) < 2) continue;

                $headers = $rows[0];
                $headerMap = [];
                $wilayahColIdx = -1;

                foreach ($headers as $idx => $hName) {
                    $trimmed = trim((string)$hName);
                    if (in_array($trimmed, $selectedColumns)) {
                        $headerMap[$idx] = $trimmed;
                    }
                    if ($kolomWilayah && $trimmed === $kolomWilayah) {
                        $wilayahColIdx = $idx;
                    }
                }

                for ($i = 1; $i < count($rows); $i++) {
                    $row = $rows[$i];
                    $detailData = [];
                    $hasContent = false;

                    foreach ($headerMap as $idx => $colName) {
                        $val = isset($row[$idx]) ? trim((string)$row[$idx]) : '';
                        if ($val !== '') $hasContent = true;
                        $detailData[$colName] = $val;
                    }

                    if (!$hasContent) continue;

                    $exists = Anomali::where('id_kegiatan', $kegiatan->id)
                        ->where('id_konfirmasi', $konfirmasi->id)
                        ->where('detail', json_encode($detailData))
                        ->exists();

                    if ($exists) {
                        $totalDuplicate++;
                        continue;
                    }

                    $idAlokasi = null;

                    if ($wilayahColIdx !== -1 && isset($row[$wilayahColIdx])) {
                        $nilaiWilayah = trim((string)$row[$wilayahColIdx]);
                        if (!empty($nilaiWilayah)) {
                            $matchedAlokasi = $alokasiList->first(function ($al) use ($nilaiWilayah) {
                                if ($al->wilayah) {
                                    return strcasecmp($al->wilayah->nama, $nilaiWilayah) === 0 ||
                                           strcasecmp($al->wilayah->kode ?? '', $nilaiWilayah) === 0 ||
                                           str_contains(strtolower($al->wilayah->nama), strtolower($nilaiWilayah));
                                }
                                return false;
                            });

                            if ($matchedAlokasi) {
                                $idAlokasi = $matchedAlokasi->id;
                            }
                        }
                    }

                    if (!$idAlokasi) {
                        foreach ($detailData as $k => $v) {
                            if (empty($v)) continue;
                            if (str_contains(strtolower($k), 'ppl')) {
                                $found = $alokasiList->first(fn($a) => stripos($a->nama_ppl, $v) !== false);
                                if ($found) { $idAlokasi = $found->id; break; }
                            } elseif (str_contains(strtolower($k), 'pml')) {
                                $found = $alokasiList->first(fn($a) => stripos($a->nama_pml, $v) !== false);
                                if ($found) { $idAlokasi = $found->id; break; }
                            }
                        }
                    }

                    Anomali::create([
                        'id_kegiatan' => $kegiatan->id,
                        'id_konfirmasi' => $konfirmasi->id,
                        'id_alokasi' => $idAlokasi,
                        'kolom' => array_values($selectedColumns),
                        'detail' => $detailData,
                        'status' => 'belum',
                    ]);

                    $totalInserted++;
                }
            }

            @unlink($filePath);
        }

        return redirect()->route('anomali.index', ['id_kegiatan' => $kegiatan->id, 'id_konfirmasi' => $konfirmasi->id])
            ->with('success', "Proses import multi-file selesai! Berhasil menyimpan {$totalInserted} data anomali baru ({$totalDuplicate} data dilewati karena sudah ada).");
    }
}
