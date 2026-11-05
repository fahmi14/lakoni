<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Konfirmasi extends Model
{
    use HasFactory;

    protected $table = 'konfirmasi';

    protected $fillable = [
        'nama',
        'pertanyaan',
        'jawaban',
        'sesuai',
    ];

    protected $casts = [
        'pertanyaan' => 'array',
        'jawaban' => 'integer',
        'sesuai' => 'integer',
    ];

    public function anomali()
    {
        return $this->hasMany(Anomali::class, 'id_konfirmasi');
    }
}

