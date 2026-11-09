<?php

namespace App\Services;

use App\Models\Anomali;
use App\Models\Alokasi;
use App\Models\Kegiatan;

class AnomaliSyncService
{
    /**
     * Melakukan sinkronisasi pemetaan alokasi petugas ke tabel Anomali.
     * Jika $idKegiatan diberikan, hanya mensinkronkan anomali kegiatan tersebut.
     *
     * @param int|null $idKegiatan
     * @return array [updated => int, total => int]
     */
    public static function syncAlokasi(?int $idKegiatan = null): array
    {
        $query = Anomali::query();
        if ($idKegiatan) {
            $query->where('id_kegiatan', $idKegiatan);
        }

        $anomalis = $query->get();
        if ($anomalis->isEmpty()) {
            return ['updated' => 0, 'total' => 0];
        }

        // Cache alokasi per kegiatan
        $kegiatanIds = $anomalis->pluck('id_kegiatan')->unique();
        $alokasiByKegiatan = [];
        foreach ($kegiatanIds as $kId) {
            $alokasiByKegiatan[$kId] = Alokasi::with('wilayah')->where('id_kegiatan', $kId)->get();
        }

        $updatedCount = 0;

        foreach ($anomalis as $anomali) {
            $alokasiList = $alokasiByKegiatan[$anomali->id_kegiatan] ?? collect();
            if ($alokasiList->isEmpty()) {
                if ($anomali->id_alokasi !== null) {
                    $anomali->update(['id_alokasi' => null]);
                    $updatedCount++;
                }
                continue;
            }

            $detailData = is_array($anomali->detail) ? $anomali->detail : [];
            $newAlokasiId = null;

            // 1. Prioritas Pertama: Pencocokan Berdasarkan Wilayah
            // Cek semua nilai field detail JSON yang mungkin memuat nama/kode wilayah
            $wilayahMatches = [];
            foreach ($detailData as $k => $v) {
                if (empty($v) || !is_scalar($v)) continue;
                $vStr = trim((string)$v);
                $kLower = strtolower($k);

                // Cek jika field ini berkaitan dengan wilayah (desa, kec, sls, nks, wilayah, kode_desa, dll)
                if (str_contains($kLower, 'desa') || str_contains($kLower, 'kec') || str_contains($kLower, 'wilayah') || str_contains($kLower, 'sls') || str_contains($kLower, 'nks')) {
                    $matched = $alokasiList->first(function ($al) use ($vStr) {
                        if (!$al->wilayah) return false;
                        $wNama = trim($al->wilayah->nama);
                        $wKode = trim($al->wilayah->kode ?? '');
                        return strcasecmp($wNama, $vStr) === 0 ||
                               ($wKode !== '' && strcasecmp($wKode, $vStr) === 0);
                    });

                    if ($matched) {
                        $newAlokasiId = $matched->id;
                        break;
                    }
                }
            }

            // 2. Prioritas Kedua: Pencocokan Berdasarkan Nama Petugas (PPL / PML)
            if (!$newAlokasiId) {
                foreach ($detailData as $k => $v) {
                    if (empty($v) || !is_scalar($v)) continue;
                    $vStr = trim((string)$v);
                    $kLower = strtolower($k);

                    if (str_contains($kLower, 'ppl') || str_contains($kLower, 'pencacah')) {
                        $found = $alokasiList->first(fn($a) => stripos($a->nama_ppl, $vStr) !== false);
                        if ($found) { $newAlokasiId = $found->id; break; }
                    } elseif (str_contains($kLower, 'pml') || str_contains($kLower, 'pengawas') || str_contains($kLower, 'pemeriksa')) {
                        $found = $alokasiList->first(fn($a) => stripos($a->nama_pml, $vStr) !== false);
                        if ($found) { $newAlokasiId = $found->id; break; }
                    }
                }
            }

            // 3. Prioritas Ketiga: Pencarian Bebas Teks Wilayah di Seluruh Kolom Detail
            if (!$newAlokasiId) {
                foreach ($detailData as $k => $v) {
                    if (empty($v) || !is_scalar($v)) continue;
                    $vStr = trim((string)$v);
                    $matched = $alokasiList->first(function ($al) use ($vStr) {
                        if (!$al->wilayah) return false;
                        return strcasecmp(trim($al->wilayah->nama), $vStr) === 0;
                    });
                    if ($matched) {
                        $newAlokasiId = $matched->id;
                        break;
                    }
                }
            }

            // Update jika ada perubahan id_alokasi
            if ($anomali->id_alokasi !== $newAlokasiId) {
                $anomali->update(['id_alokasi' => $newAlokasiId]);
                $updatedCount++;
            }
        }

        return [
            'updated' => $updatedCount,
            'total' => $anomalis->count(),
        ];
    }
}

