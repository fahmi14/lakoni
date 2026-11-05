<?php

namespace App\Http\Controllers;

use App\Models\Anomali;
use App\Models\Kegiatan;
use App\Models\Konfirmasi;
use App\Models\Alokasi;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    /**
     * Tampilkan halaman monitoring rekap gabungan
     */
    public function index(Request $request)
    {
        // Ambil daftar tahun dari tanggal mulai kegiatan (kompatibel SQLite & MySQL)
        $years = Kegiatan::whereNotNull('mulai')
            ->get(['mulai'])
            ->map(function ($k) {
                return $k->mulai ? (int)$k->mulai->format('Y') : null;
            })
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        if ($years->isEmpty()) {
            $years = collect([(int)date('Y')]);
        }

        $kegiatans = Kegiatan::orderBy('nama')->get();
        $konfirmasis = Konfirmasi::orderBy('nama')->get();

        return view('monitoring.index', compact('years', 'kegiatans', 'konfirmasis'));
    }

    /**
     * Data Rekap Gabungan per Petugas beserta Rekap Isian Jawaban Template & Progres
     */
    public function getRekapData(Request $request)
    {
        // 1. Tentukan template konfirmasi yang aktif (default: ambil template pertama jika tidak disaring)
        $selectedKonfirmasiId = $request->id_konfirmasi;
        if (!$selectedKonfirmasiId) {
            $firstKonfirmasi = Konfirmasi::first();
            $selectedKonfirmasiId = $firstKonfirmasi ? $firstKonfirmasi->id : null;
        }

        $activeKonfirmasi = $selectedKonfirmasiId ? Konfirmasi::find($selectedKonfirmasiId) : null;
        $pertanyaanList = $activeKonfirmasi && is_array($activeKonfirmasi->pertanyaan) ? $activeKonfirmasi->pertanyaan : [];

        // 2. Format struktur header kolom pertanyaan & opsi
        $columnsPertanyaan = [];
        foreach ($pertanyaanList as $pIdx => $p) {
            $tipe = $p['tipe'] ?? 'terbuka';
            $teks = $p['teks'] ?? ('Pertanyaan ' . ($pIdx + 1));
            $opsi = $p['opsi'] ?? [];

            $subCols = [];
            if ($tipe === 'pilihan') {
                foreach ($opsi as $oIdx => $optText) {
                    $subCols[] = [
                        'opt_idx' => $oIdx,
                        'opt_label' => $optText,
                        'col_key' => "p_{$pIdx}_o_{$oIdx}",
                    ];
                }
            } else {
                // Tipe isian teks/terbuka: hitung yang terisi
                $subCols[] = [
                    'opt_idx' => null,
                    'opt_label' => 'Terisi',
                    'col_key' => "p_{$pIdx}_terisi",
                ];
            }

            $columnsPertanyaan[] = [
                'index' => $pIdx,
                'teks' => $teks,
                'tipe' => $tipe,
                'sub_columns' => $subCols,
            ];
        }

        // 3. Query anomali
        $anomaliQuery = Anomali::query()
            ->with(['alokasi.wilayah', 'kegiatan'])
            ->whereNotNull('id_alokasi');

        if ($request->filled('tahun')) {
            $tahun = (int)$request->tahun;
            $anomaliQuery->whereHas('kegiatan', function ($q) use ($tahun) {
                $q->whereBetween('mulai', ["{$tahun}-01-01", "{$tahun}-12-31"]);
            });
        }

        if ($request->filled('id_kegiatan')) {
            $anomaliQuery->where('id_kegiatan', $request->id_kegiatan);
        }

        if ($selectedKonfirmasiId) {
            $anomaliQuery->where('id_konfirmasi', $selectedKonfirmasiId);
        }

        $anomalis = $anomaliQuery->get();

        // 4. Kelompokkan anomali per Alokasi (Petugas & Wilayah)
        $grouped = $anomalis->groupBy('id_alokasi');

        $rows = [];
        $grandTotalAnomali = 0;
        $grandTotalSudah = 0;
        $grandTotalBelum = 0;
        $grandJawabanCounter = [];

        foreach ($grouped as $idAlokasi => $items) {
            $first = $items->first();
            $alokasi = $first->alokasi;
            $wilayah = $alokasi ? $alokasi->wilayah : null;
            $kegiatan = $first->kegiatan;

            $totalAnomali = $items->count();
            $sudahKonfirmasi = $items->whereNotIn('status', ['belum', 'pending'])->whereNotNull('status')->count();
            $belumKonfirmasi = $totalAnomali - $sudahKonfirmasi;
            $pctProgres = $totalAnomali > 0 ? round(($sudahKonfirmasi / $totalAnomali) * 100, 1) : 0;

            $grandTotalAnomali += $totalAnomali;
            $grandTotalSudah += $sudahKonfirmasi;
            $grandTotalBelum += $belumKonfirmasi;

            // Hitung distribusi jawaban per pertanyaan untuk petugas ini
            $jawabanCounts = [];
            foreach ($columnsPertanyaan as $cp) {
                $pIdx = $cp['index'];
                $tipe = $cp['tipe'];

                if ($tipe === 'pilihan') {
                    foreach ($cp['sub_columns'] as $sub) {
                        $colKey = $sub['col_key'];
                        $jawabanCounts[$colKey] = 0;
                        if (!isset($grandJawabanCounter[$colKey])) {
                            $grandJawabanCounter[$colKey] = 0;
                        }
                    }

                    foreach ($items as $item) {
                        if (is_array($item->hasil_konfirmasi) && isset($item->hasil_konfirmasi[$pIdx])) {
                            $ans = $item->hasil_konfirmasi[$pIdx];
                            if (is_numeric($ans)) {
                                $ansIdx = (int)$ans;
                                $targetKey = "p_{$pIdx}_o_{$ansIdx}";
                                if (isset($jawabanCounts[$targetKey])) {
                                    $jawabanCounts[$targetKey]++;
                                    $grandJawabanCounter[$targetKey]++;
                                }
                            }
                        }
                    }
                } else {
                    // Tipe terbuka
                    $colKey = "p_{$pIdx}_terisi";
                    $jawabanCounts[$colKey] = 0;
                    if (!isset($grandJawabanCounter[$colKey])) {
                        $grandJawabanCounter[$colKey] = 0;
                    }

                    foreach ($items as $item) {
                        if (is_array($item->hasil_konfirmasi) && !empty($item->hasil_konfirmasi[$pIdx])) {
                            if (trim((string)$item->hasil_konfirmasi[$pIdx]) !== '') {
                                $jawabanCounts[$colKey]++;
                                $grandJawabanCounter[$colKey]++;
                            }
                        }
                    }
                }
            }

            $rows[] = [
                'id_alokasi' => $idAlokasi,
                'nama_ppl' => $alokasi ? ($alokasi->nama_ppl ?? '-') : '-',
                'nama_pml' => $alokasi ? ($alokasi->nama_pml ?? '-') : '-',
                'kode_wilayah' => $wilayah ? $wilayah->kode : '',
                'nama_wilayah' => $wilayah ? $wilayah->nama : '-',
                'kegiatan_nama' => $kegiatan ? $kegiatan->nama : '-',
                'total_anomali' => $totalAnomali,
                'sudah_konfirmasi' => $sudahKonfirmasi,
                'belum_konfirmasi' => $belumKonfirmasi,
                'progres' => $pctProgres,
                'jawaban' => $jawabanCounts,
            ];
        }

        // Urutkan default: petugas dengan beban belum konfirmasi terbanyak di atas
        usort($rows, function ($a, $b) {
            return $b['belum_konfirmasi'] <=> $a['belum_konfirmasi'];
        });

        $grandProgres = $grandTotalAnomali > 0 ? round(($grandTotalSudah / $grandTotalAnomali) * 100, 1) : 0;

        return response()->json([
            'template' => [
                'id' => $activeKonfirmasi ? $activeKonfirmasi->id : null,
                'nama' => $activeKonfirmasi ? $activeKonfirmasi->nama : 'Semua Template',
                'columns' => $columnsPertanyaan,
            ],
            'summary' => [
                'total_anomali' => $grandTotalAnomali,
                'sudah_konfirmasi' => $grandTotalSudah,
                'belum_konfirmasi' => $grandTotalBelum,
                'progres' => $grandProgres,
                'grand_jawaban' => $grandJawabanCounter,
            ],
            'data' => $rows,
        ]);
    }
}
