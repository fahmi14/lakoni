<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anomali extends Model
{
    use HasFactory;

    protected $table = 'anomali';

    protected $fillable = [
        'id_kegiatan',
        'id_konfirmasi',
        'id_alokasi',
        'kolom',
        'detail',
        'hasil_konfirmasi',
        'status',
        'petugas_pengonfirmasi',
        'dikonfirmasi_pada',
    ];

    protected $casts = [
        'kolom' => 'array',
        'detail' => 'array',
        'hasil_konfirmasi' => 'array',
        'dikonfirmasi_pada' => 'datetime',
    ];

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class, 'id_kegiatan');
    }

    public function konfirmasi()
    {
        return $this->belongsTo(Konfirmasi::class, 'id_konfirmasi');
    }

    public function alokasi()
    {
        return $this->belongsTo(Alokasi::class, 'id_alokasi');
    }
}

