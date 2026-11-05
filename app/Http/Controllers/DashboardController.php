<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Kegiatan;
use App\Models\Anomali;
use App\Models\Alokasi;
use App\Models\Konfirmasi;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'petugas') {
            return redirect()->route('petugas.kegiatan');
        }

        $totalUsers = User::count();
        $totalKegiatan = Kegiatan::count();
        $totalKonfirmasi = Konfirmasi::count();
        $totalAlokasi = Alokasi::count();
        $totalAnomali = Anomali::count();
        $anomaliSelesai = Anomali::whereIn('status', ['sesuai', 'tidak_sesuai', 'dikonfirmasi'])->count();
        $anomaliBelum = Anomali::where('status', 'belum')->count();

        $recentAnomalis = Anomali::with(['kegiatan', 'konfirmasi'])->latest()->take(5)->get();

        return view('dashboard', compact(
            'totalUsers',
            'totalKegiatan',
            'totalKonfirmasi',
            'totalAlokasi',
            'totalAnomali',
            'anomaliSelesai',
            'anomaliBelum',
            'recentAnomalis'
        ));
    }
}
