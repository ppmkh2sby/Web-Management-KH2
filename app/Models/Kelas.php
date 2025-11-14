<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas'; // sesuaikan kalau nama tabelnya lain
    protected $fillable = ['nama']; // tambah kolom lain sesuai kebutuhan

    public function santris()
    {
        return $this->hasMany(Santri::class, 'kelas_id');
    }
}
