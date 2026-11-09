<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    use HasFactory;

    protected $table = 'kegiatan';

    protected $fillable = [
        'kode',
        'nama',
        'mulai',
        'selesai',
        'kolom',
        'template_wa',
    ];

    protected $casts = [
        'mulai' => 'date',
        'selesai' => 'date',
        'kolom' => 'array',
    ];

    public function alokasi()
    {
        return $this->hasMany(Alokasi::class, 'id_kegiatan');
    }

    public function anomali()
    {
        return $this->hasMany(Anomali::class, 'id_kegiatan');
    }

    public function isAktif(): bool
    {
        if (!$this->mulai || !$this->selesai) {
            return false;
        }
        $today = now()->startOfDay();
        $start = min($this->mulai->startOfDay(), $this->selesai->startOfDay());
        $end = max($this->mulai->endOfDay(), $this->selesai->endOfDay());
        return $today->between($start, $end);
    }

    protected static function booted()
    {
        static::deleting(function ($kegiatan) {
            // Hapus semua anomali yang terkait dengan kegiatan ini
            $kegiatan->anomali()->delete();
            // Hapus semua alokasi yang terkait dengan kegiatan ini
            $kegiatan->alokasi()->delete();
        });
    }
}
