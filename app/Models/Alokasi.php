<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alokasi extends Model
{
    use HasFactory;

    protected $table = 'alokasi';

    protected $fillable = [
        'id_kegiatan',
        'nama_ppl',
        'nama_pml',
        'id_wilayah',
    ];

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class, 'id_kegiatan');
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'id_wilayah');
    }

    public function anomali()
    {
        return $this->hasMany(Anomali::class, 'id_alokasi');
    }
}

