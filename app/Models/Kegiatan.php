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
        $today = now()->startOfDay();
        return $today->between($this->mulai->startOfDay(), $this->selesai->endOfDay());
    }
}
