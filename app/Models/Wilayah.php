<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wilayah extends Model
{
    use HasFactory;

    protected $table = 'wilayah';

    protected $fillable = [
        'nama',
        'kode',
        'tipe',
    ];

    public function alokasi()
    {
        return $this->hasMany(Alokasi::class, 'id_wilayah');
    }
}

