<?php

namespace App\Http\Controllers;

use App\Models\Anomali;
use App\Models\Kegiatan;
use App\Models\Konfirmasi;
use App\Models\Alokasi;
use App\Models\Wilayah;
use App\Services\AnomaliSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\LakoniHelper;

class AnomaliController extends Controller
{
    public function index(Request $request)
    {
        $kegiatans = Kegiatan::orderBy('nama')->get();
        $konfirmasis = Konfirmasi::orderBy('nama')->get();
        $selectedKegiatanId = $request->input('id_kegiatan');

        // 1. Tentukan detailKeys & selectedColumns
        $detailKeys = [];
        if ($request->filled('id_kegiatan') && $request->id_kegiatan !== 'all') {
            $selectedKegiatan = $kegiatans->firstWhere('id', $request->id_kegiatan);
            if ($selectedKegiatan && !empty($selectedKegiatan->kolom) && is_array($selectedKegiatan->kolom)) {
                $detailKeys = $selectedKegiatan->kolom;
            }
        }

        if (empty($detailKeys)) {
            $sampleAnomali = Anomali::whereNotNull('detail')->first();
            $detailKeys = ($sampleAnomali && is_array($sampleAnomali->detail)) ? array_keys($sampleAnomali->detail) : [];
        }

        // Kolom terpilih: Hanya ambil jika request('columns') berupa array string (bukan internal DataTables columns parameter)
        $idKegiatanKey = $request->filled('id_kegiatan') ? $request->input('id_kegiatan') : 'all';
        $sessionKey = 'anomali_selected_columns_' . $idKegiatanKey;

        $rawColumnsReq = $request->input('columns');
        if (!empty($rawColumnsReq) && is_array($rawColumnsReq) && is_string(reset($rawColumnsReq))) {
            $selectedColumns = array_values(array_filter($rawColumnsReq, fn($item) => is_string($item)));
            session([$sessionKey => $selectedColumns]);
        } elseif (session()->has($sessionKey)) {
            $saved = session($sessionKey, []);
            if (is_array($saved) && (empty($saved) || is_string(reset($saved)))) {
                $selectedColumns = array_values(array_filter($saved, fn($item) => is_string($item)));
            } else {
                $selectedColumns = [];
            }
        } else {
            $selectedColumns = [];
        }

        if (empty($selectedColumns) && !empty($detailKeys)) {
            $selectedColumns = array_slice($detailKeys, 0, 5);
            session([$sessionKey => $selectedColumns]);
        }

        // 2. Jika Request AJAX dari Yajra DataTables
        if ($request->ajax()) {
            // Jika kegiatan belum dipilih sama sekali, kembalikan data kosong
            if (!$request->has('id_kegiatan') || $request->id_kegiatan === '') {
                return DataTables::of(collect())->make(true);
            }

            $query = Anomali::with(['kegiatan', 'konfirmasi', 'alokasi.wilayah']);

            if ($request->filled('id_kegiatan') && $request->id_kegiatan !== 'all') {
                $query->where('id_kegiatan', $request->id_kegiatan);
            }
            if ($request->filled('id_konfirmasi')) {
                $query->where('id_konfirmasi', $request->id_konfirmasi);
            }
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

            // Custom search keyword
            if ($request->filled('custom_search')) {
                $search = trim($request->custom_search);
                $query->where(function ($q) use ($search) {
                    $q->where('detail', 'like', "%{$search}%")
                      ->orWhere('petugas_pengonfirmasi', 'like', "%{$search}%")
                      ->orWhereHas('kegiatan', function ($kq) use ($search) {
                          $kq->where('nama', 'like', "%{$search}%")->orWhere('kode', 'like', "%{$search}%");
                      })
                      ->orWhereHas('konfirmasi', function ($kq) use ($search) {
                          $kq->where('nama', 'like', "%{$search}%");
                      })
                      ->orWhereHas('alokasi', function ($aq) use ($search) {
                          $aq->where('nama_ppl', 'like', "%{$search}%")
                             ->orWhere('nama_pml', 'like', "%{$search}%")
                             ->orWhereHas('wilayah', function ($wq) use ($search) {
                                 $wq->where('nama', 'like', "%{$search}%")->orWhere('kode', 'like', "%{$search}%");
                             });
                      });
                });
            }

            $rawColumnsList = ['checkbox', 'kegiatan_info', 'petugas_info', 'status_badge', 'action'];

            $dataTable = DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($a) {
                    return '<div class="form-check d-flex justify-content-center mb-0">
                                <input type="checkbox" class="form-check-input check-anomali-item" value="' . $a->id . '">
                            </div>';
                })
                ->addColumn('kegiatan_info', function ($a) {
                    $kodeKeg = e($a->kegiatan->kode ?? '-');
                    $namaKonf = e($a->konfirmasi->nama ?? '-');
                    return '<span class="badge-pastel-primary mb-1">' . $kodeKeg . '</span>' .
                           '<div class="fw-bold text-dark fs-6" style="word-break: break-word;">' . $namaKonf . '</div>';
                })
                ->addColumn('petugas_info', function ($a) {
                    if ($a->alokasi) {
                        return '<div class="small mb-1 leading-tight">
                                    <div><span class="text-muted">PPL:</span> <strong class="text-dark">' . e($a->alokasi->nama_ppl) . '</strong></div>
                                    <div><span class="text-muted">PML:</span> <strong class="text-dark">' . e($a->alokasi->nama_pml) . '</strong></div>
                                </div>';
                    }
                    return '<span class="badge-pastel-neutral mb-1">Belum terpetakan</span>';
                })
                ->addColumn('status_badge', function ($a) {
                    $html = '';
                    if ($a->status === 'sesuai') {
                        $html = '<span class="badge-pastel-success"><i class="fa-solid fa-check-double me-1"></i> Sesuai</span>';
                    } elseif ($a->status === 'tidak_sesuai') {
                        $html = '<span class="badge-pastel-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> Perlu Revisi</span>';
                    } elseif ($a->status === 'dikonfirmasi') {
                        $html = '<span class="badge-pastel-primary"><i class="fa-solid fa-check me-1"></i> Dikonfirmasi</span>';
                    } else {
                        $html = '<span class="badge-pastel-warning"><i class="fa-solid fa-clock me-1"></i> Menunggu</span>';
                    }

                    if ($a->petugas_pengonfirmasi) {
                        $html .= '<div class="small text-muted mt-1"><i class="fa-solid fa-user-check me-1"></i> ' . e($a->petugas_pengonfirmasi) . '</div>';
                    }
                    return $html;
                })
                ->addColumn('action', function ($a) {
                    $detailUrl = route('anomali.show', $a);
                    $deleteUrl = route('anomali.destroy', $a);
                    $token = csrf_field();
                    $method = method_field('DELETE');

                    // Tombol WA jika ada no HP
                    $waBtn = '';
                    $noHp = null;
                    if ($a->alokasi && is_array($a->detail)) {
                        foreach ($a->detail as $k => $v) {
                            $keyLower = strtolower(str_replace([' ', '_', '-'], '', $k));
                            if (in_array($keyLower, ['nohp', 'hp', 'telepon', 'notelp', 'telp', 'wa', 'whatsapp', 'nomorhp'])) {
                                $cleanDigits = preg_replace('/[^0-9]/', '', (string)$v);
                                if (strlen($cleanDigits) >= 9 && strlen($cleanDigits) <= 15) {
                                    $noHp = $cleanDigits;
                                    break;
                                }
                            }
                        }
                    }

                    if ($noHp) {
                        if (str_starts_with($noHp, '0')) $noHp = '62' . substr($noHp, 1);
                        elseif (str_starts_with($noHp, '8')) $noHp = '62' . $noHp;
                        
                        $template = $a->kegiatan->template_wa ?? "Hai {nama_ppl}, mohon konfirmasi untuk anomali {nama_anomali} di kegiatan {nama_kegiatan}. Terima kasih.";
                        $namaPpl = $a->alokasi->nama_ppl ?? ($a->detail['Nama PPL'] ?? ($a->detail['PPL'] ?? 'Petugas'));
                        $namaPml = $a->alokasi->nama_pml ?? ($a->detail['Nama PML'] ?? ($a->detail['PML'] ?? 'Pengawas'));
                        $namaAnomali = $a->konfirmasi->nama ?? 'Anomali';
                        $namaKegiatan = $a->kegiatan->nama ?? 'Survei';

                        $replacements = [
                            '{nama_ppl}' => $namaPpl,
                            '{nama_pml}' => $namaPml,
                            '{nama_anomali}' => $namaAnomali,
                            '{nama_kegiatan}' => $namaKegiatan,
                            '{kode_kegiatan}' => $a->kegiatan->kode ?? '',
                            '{status}' => $a->status,
                        ];

                        if (is_array($a->detail)) {
                            foreach ($a->detail as $dk => $dv) {
                                $replacements['{' . $dk . '}'] = (string)$dv;
                            }
                        }

                        $pesanWa = strtr($template, $replacements);
                        $waLink = "https://wa.me/{$noHp}?text=" . urlencode($pesanWa);
                        $waBtn = '<a href="' . $waLink . '" target="_blank" class="btn-table-action text-success" title="Chat WhatsApp"><i class="fa-brands fa-whatsapp fs-5"></i></a>';
                    }

                    return '
                    <div class="d-inline-flex gap-1 justify-content-center align-items-center">
                        ' . $waBtn . '
                        <a href="' . $detailUrl . '" class="btn-table-action" title="Lihat Detail & Jawaban">
                            <i class="fa-solid fa-eye text-primary"></i>
                        </a>
                        <form action="' . $deleteUrl . '" method="POST" onsubmit="return confirmPastelAction(this, { title: \'Hapus Data Anomali\', message: \'Hapus data anomali #' . $a->id . ' ini dari sistem?\' })" class="d-inline">
                            ' . $token . '
                            ' . $method . '
                            <button type="submit" class="btn-table-action text-danger" title="Hapus Anomali">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>';
                });

            // Tambahkan kolom detail JSON dinamis
            foreach ($selectedColumns as $sc) {
                if (!is_string($sc)) continue;
                $colKey = 'detail_' . md5($sc);
                $rawColumnsList[] = $colKey;
                $dataTable->addColumn($colKey, function ($a) use ($sc) {
                    $val = $a->detail[$sc] ?? '-';
                    return LakoniHelper::formatCellContent($val, $sc);
                });
            }

            return $dataTable
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->input('search.value'))) {
                        $search = trim($request->input('search.value'));
                        $query->where(function ($q) use ($search) {
                            $q->where('detail', 'like', "%{$search}%")
                              ->orWhere('petugas_pengonfirmasi', 'like', "%{$search}%")
                              ->orWhereHas('kegiatan', function ($kq) use ($search) {
                                  $kq->where('nama', 'like', "%{$search}%")->orWhere('kode', 'like', "%{$search}%");
                              })
                              ->orWhereHas('konfirmasi', function ($kq) use ($search) {
                                  $kq->where('nama', 'like', "%{$search}%");
                              })
                              ->orWhereHas('alokasi', function ($aq) use ($search) {
                                  $aq->where('nama_ppl', 'like', "%{$search}%")
                                     ->orWhere('nama_pml', 'like', "%{$search}%");
                              });
                        });
                    }
                })
                ->rawColumns($rawColumnsList)
                ->make(true);
        }

        // Hitung total anomali untuk info badge
        $totalAnomali = Anomali::count();

        return view('anomali.index', compact('kegiatans', 'konfirmasis', 'detailKeys', 'selectedColumns', 'totalAnomali', 'selectedKegiatanId'));
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
     * Hapus Checklist Masal (Bulk Delete) Anomali Terpilih dengan Validasi Kode Konfirmasi
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:anomali,id',
            'confirm_code' => 'required|string',
            'expected_code' => 'required|string',
        ]);

        if (trim(strtoupper($request->confirm_code)) !== trim(strtoupper($request->expected_code))) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode konfirmasi tidak cocok. Penghapusan anomali dibatalkan demi keamanan data.',
                ], 422);
            }
            return back()->with('error', 'Kode konfirmasi tidak cocok. Penghapusan anomali dibatalkan.');
        }

        $ids = $request->ids;
        $count = count($ids);

        Anomali::whereIn('id', $ids)->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$count} data temuan anomali terpilih.",
            ]);
        }

        return redirect()->route('anomali.index', $request->query())
            ->with('success', "Berhasil menghapus {$count} data temuan anomali terpilih.");
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

        // 2. Kumpulkan pertanyaan dari template konfirmasi terpilih (dengan prefix agar tidak bentrok dengan kolom detail)
        $pertanyaanHeaders = [];
        $pertanyaanKeysMap = [];
        if (is_array($konfirmasi->pertanyaan)) {
            foreach ($konfirmasi->pertanyaan as $qIdx => $q) {
                $qTeks = $q['teks'] ?? "Pertanyaan #".($qIdx + 1);
                $headerLabel = "[Konfirmasi] " . $qTeks;
                $pertanyaanHeaders[] = $headerLabel;
                $pertanyaanKeysMap[$headerLabel] = $qIdx;
            }
        }
        if (empty($pertanyaanHeaders)) {
            $pertanyaanHeaders = ['[Konfirmasi] Hasil Jawaban'];
            $pertanyaanKeysMap['[Konfirmasi] Hasil Jawaban'] = 0;
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

            foreach ($pertanyaanHeaders as $pH) {
                $qIdx = $pertanyaanKeysMap[$pH] ?? null;
                $valAns = '';
                if ($qIdx !== null && isset($hasilKonf[$qIdx])) {
                    $rawVal = $hasilKonf[$qIdx];
                    $qItem = $qList[$qIdx] ?? null;
                    if ($qItem && isset($qItem['tipe']) && $qItem['tipe'] === 'pilihan' && is_numeric($rawVal)) {
                        $valAns = $qItem['opsi'][$rawVal] ?? $rawVal;
                    } else {
                        $valAns = (string)$rawVal;
                    }
                }
                $row[] = $valAns;
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
     * Membaca baris-baris file Excel/CSV dengan penanganan khusus quote CSV ganda/tidak standar
     */
    protected function parseUploadedFileRows(string $filePath): array
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // Jika CSV atau TXT, gunakan pembaca CSV robust yang menormalkan double-quotes pembungkus
        if (in_array($ext, ['csv', 'txt'])) {
            $rawContent = file_get_contents($filePath);
            if ($rawContent === false || trim($rawContent) === '') {
                return [];
            }

            // Normalisasi line ending
            $rawContent = str_replace(["\r\n", "\r"], "\n", $rawContent);
            $lines = explode("\n", $rawContent);
            $rows = [];

            // Deteksi delimiter (; atau ,)
            $firstLine = $lines[0] ?? '';
            $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

            foreach ($lines as $line) {
                if (trim($line) === '') continue;

                // Normalisasi double double-quotes di awal/akhir kolom:
                // misal: ""Status perkotaan, tetapi..."" -> "Status perkotaan, tetapi..."
                // dan """"Link"""" -> "Link"
                $cleanedLine = preg_replace('/(?<=^|' . preg_quote($delimiter, '/') . ')""(.*?)""(?=' . preg_quote($delimiter, '/') . '|$)/s', '"$1"', $line);

                $parsedRow = str_getcsv($cleanedLine, $delimiter, '"');

                // Bersihkan quote tersisa dan unescape double quotes internal
                $parsedRow = array_map(function ($val) {
                    $v = trim((string)$val);
                    $v = str_replace('""""', '"', $v);
                    $v = str_replace('""', '"', $v);
                    return $v;
                }, $parsedRow);

                $rows[] = $parsedRow;
            }

            return $rows;
        }

        // Jika XLSX atau XLS, gunakan PhpSpreadsheet IOFactory
        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            return $sheet ? $sheet->toArray() : [];
        } catch (\Throwable $e) {
            return [];
        }
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
            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

            if (in_array($ext, ['csv', 'txt'])) {
                $rows = $this->parseUploadedFileRows($fullPath);
                if (empty($rows)) continue;

                $headers = array_filter(array_map('trim', (array)$rows[0]));
                $previewRows = array_slice($rows, 1, 5);

                foreach ($headers as $h) {
                    $allHeadersSet[$h] = true;
                }

                $sheetsData[] = [
                    'fileName' => $origName,
                    'name' => 'Data CSV',
                    'headers' => $headers,
                    'previewRows' => $previewRows,
                    'totalRows' => count($rows) - 1,
                ];
            } else {
                try {
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
                } catch (\Throwable $e) {
                    continue; // Lewati file excel yang korup / tidak dapat dibaca
                }
            }
        }

        if (empty($sheetsData)) {
            return back()->with('error', 'Semua file yang diunggah tidak memiliki data yang dapat dibaca atau formatnya tidak valid.');
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

            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $sheetGroups = [];

            if (in_array($ext, ['csv', 'txt'])) {
                $sheetGroups[] = $this->parseUploadedFileRows($filePath);
            } else {
                try {
                    $spreadsheet = IOFactory::load($filePath);
                    foreach ($spreadsheet->getAllSheets() as $sheet) {
                        $sheetGroups[] = $sheet->toArray();
                    }
                } catch (\Throwable $e) {
                    continue;
                }
            }

            foreach ($sheetGroups as $rows) {
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

    /**
     * Sinkronisasi pemetaan alokasi petugas ke data anomali dari halaman Anomali
     */
    public function sync(Request $request)
    {
        $idKegiatan = $request->filled('id_kegiatan') ? (int)$request->id_kegiatan : null;
        $result = AnomaliSyncService::syncAlokasi($idKegiatan);

        $msg = "Sinkronisasi berhasil! {$result['updated']} dari {$result['total']} data anomali berhasil diperbarui pemetaan petugasnya.";
        return back()->with('success', $msg);
    }
}
