<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Kegiatan;
use App\Models\Konfirmasi;
use App\Models\Wilayah;
use App\Models\Alokasi;
use App\Models\Anomali;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Pengguna Bawaan
        User::firstOrCreate(
            ['email' => 'admin@lakoni.id'],
            [
                'name' => 'Administrator LAKONI',
                'password' => bcrypt('password123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'petugas@lakoni.id'],
            [
                'name' => 'Petugas Anomali',
                'password' => bcrypt('password123'),
                'role' => 'petugas',
                'email_verified_at' => now(),
            ]
        );

        // 2. Master Wilayah Contoh
        $wilayah1 = Wilayah::firstOrCreate(
            ['nama' => 'SLS 001 RT 01'],
            ['kode' => '320101001', 'tipe' => 'sls']
        );
        $wilayah2 = Wilayah::firstOrCreate(
            ['nama' => 'Desa Sukamaju'],
            ['kode' => '320101', 'tipe' => 'desa']
        );

        // 3. Kegiatan Contoh (Aktif)
        $kegiatan = Kegiatan::firstOrCreate(
            ['kode' => 'SP2026'],
            [
                'nama' => 'Sensus Pertanian 2026 Tahap II',
                'mulai' => now()->subDays(5)->format('Y-m-d'),
                'selesai' => now()->addDays(25)->format('Y-m-d'),
                'kolom' => ['NKS', 'Nama Responden', 'Komoditas', 'Luas Panen (Ha)', 'Produksi (Ton)'],
            ]
        );

        // 4. Template Konfirmasi Berisi Pertanyaan JSON
        $konfirmasi = Konfirmasi::firstOrCreate(
            ['nama' => 'Konfirmasi Anomali Produksi Pertanian'],
            [
                'pertanyaan' => [
                    [
                        'teks' => 'Apakah hasil produksi komoditas melebihi batas kewajaran rata-rata daerah ini?',
                        'tipe' => 'pilihan',
                        'opsi' => ['Sesuai / Riil di Lapangan', 'Tidak Sesuai (Perlu Revisi Angka)', 'Responden Salah Estimasi'],
                    ],
                    [
                        'teks' => 'Tuliskan catatan penjelasan hasil pengecekan langsung ke responden:',
                        'tipe' => 'terbuka',
                        'opsi' => [],
                    ]
                ],
                'jawaban' => 0,
                'sesuai' => 0, // Opsi index 0 = 'Sesuai / Riil di Lapangan'
            ]
        );

        // 5. Alokasi Petugas
        $alokasi1 = Alokasi::firstOrCreate(
            [
                'id_kegiatan' => $kegiatan->id,
                'nama_ppl' => 'Budi Santoso',
                'nama_pml' => 'Siti Rahmawati',
            ],
            [
                'id_wilayah' => $wilayah1->id,
            ]
        );

        // 6. Contoh Data Anomali
        Anomali::firstOrCreate(
            [
                'id_kegiatan' => $kegiatan->id,
                'id_konfirmasi' => $konfirmasi->id,
                'detail' => [
                    'NKS' => '100234',
                    'Nama Responden' => 'Haji Mansyur',
                    'Komoditas' => 'Padi Sawah Irigasi',
                    'Luas Panen (Ha)' => '2.5',
                    'Produksi (Ton)' => '35.0 (Anomali: Sangat Tinggi)',
                ],
            ],
            [
                'id_alokasi' => $alokasi1->id,
                'kolom' => ['NKS', 'Nama Responden', 'Komoditas', 'Luas Panen (Ha)', 'Produksi (Ton)'],
                'status' => 'belum',
            ]
        );
    }
}
