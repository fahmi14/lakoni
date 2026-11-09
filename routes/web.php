<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\KonfirmasiController;
use App\Http\Controllers\AlokasiController;
use App\Http\Controllers\AnomaliController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\WilayahController;
use App\Http\Controllers\MonitoringController;

/*
|--------------------------------------------------------------------------
| Web Routes - Aplikasi LAKONI (Lembar Konfirmasi Anomali)
|--------------------------------------------------------------------------
*/

// Halaman Pertama Publik: Masukkan Kode Kegiatan (Khusus Petugas Tanpa Perlu Login)
Route::get('/', [PetugasController::class, 'showCheckKegiatan'])->name('home');
Route::post('/kegiatan/verify', [PetugasController::class, 'verifyKegiatan'])->name('petugas.kegiatan.verify');

// Alur Konfirmasi Petugas (Tanpa Login)
Route::prefix('petugas-area')->name('petugas.')->group(function () {
    Route::get('kegiatan', [PetugasController::class, 'showCheckKegiatan'])->name('kegiatan');
    Route::get('pilih-petugas', [PetugasController::class, 'showSelectPetugas'])->name('select-petugas');
    Route::post('pilih-petugas', [PetugasController::class, 'setPetugas'])->name('set-petugas');
    Route::get('daftar-anomali', [PetugasController::class, 'listAnomali'])->name('anomali.list');
    Route::get('konfirmasi/{anomali}', [PetugasController::class, 'showKonfirmasi'])->name('konfirmasi');
    Route::post('konfirmasi/{anomali}', [PetugasController::class, 'saveKonfirmasi'])->name('konfirmasi.save');
});

// Area Otentikasi Admin
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/admin/login', [AuthController::class, 'login'])->name('login.post');

    // Forgot Password
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');
});

// Area Khusus Admin (Wajib Login & Role Admin)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 1. User Management
    Route::resource('users', UserController::class);

    // 2. Master Wilayah
    Route::get('wilayah/search-ajax', [WilayahController::class, 'searchAjax'])->name('wilayah.search-ajax');
    Route::post('wilayah/bulk-delete', [WilayahController::class, 'bulkDelete'])->name('wilayah.bulk-delete');
    Route::resource('wilayah', WilayahController::class)->except(['show']);

    // 3. Manajemen Kegiatan
    Route::resource('kegiatan', KegiatanController::class);

    // 4. Manajemen Konfirmasi (JSON)
    Route::resource('konfirmasi', KonfirmasiController::class);

    // 5. Manajemen Alokasi Petugas & Template
    Route::get('alokasi/template', [AlokasiController::class, 'downloadTemplate'])->name('alokasi.template');
    Route::get('alokasi/import', [AlokasiController::class, 'showImport'])->name('alokasi.import');
    Route::post('alokasi/import', [AlokasiController::class, 'import'])->name('alokasi.import.process');
    Route::post('alokasi/sync', [AlokasiController::class, 'sync'])->name('alokasi.sync');
    Route::post('alokasi/bulk-delete', [AlokasiController::class, 'bulkDelete'])->name('alokasi.bulk-delete');
    Route::resource('alokasi', AlokasiController::class);

    // 6. Manajemen Anomali, Template & Download Respon
    Route::get('anomali/template', [AnomaliController::class, 'downloadTemplate'])->name('anomali.template');
    Route::get('anomali/download-respon', [AnomaliController::class, 'downloadRespon'])->name('anomali.download-respon');
    Route::get('anomali/import', [AnomaliController::class, 'showImport'])->name('anomali.import');
    Route::post('anomali/preview', [AnomaliController::class, 'previewImport'])->name('anomali.preview');
    Route::post('anomali/execute-import', [AnomaliController::class, 'executeImport'])->name('anomali.execute-import');
    Route::post('anomali/sync', [AnomaliController::class, 'sync'])->name('anomali.sync');
    Route::post('anomali/bulk-delete', [AnomaliController::class, 'bulkDelete'])->name('anomali.bulk-delete');
    Route::resource('anomali', AnomaliController::class);

    // 7. Monitoring Konfirmasi Anomali (Rekap Gabungan Petugas & Template)
    Route::get('monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('monitoring/rekap-data', [MonitoringController::class, 'getRekapData'])->name('monitoring.rekap-data');

    // 8. Panduan & Infografis
    Route::get('panduan-infografis', function () {
        return view('panduan.infografis');
    })->name('panduan.infografis');
});
