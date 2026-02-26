<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Kelas extends Model
{
    protected $table = 'kelas'; // sesuaikan kalau nama tabelnya lain
    protected $fillable = ['nama']; // tambah kolom lain sesuai kebutuhan

    public function santris()
    {
        return $this->hasMany(Santri::class, 'kelas_id');
    }

    public function degurs(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'degur_kelas', 'kelas_id', 'user_id')->withTimestamps();
    }

    public function sesis(): BelongsToMany
    {
        return $this->belongsToMany(Sesi::class, 'sesi_kelas', 'kelas_id', 'sesi_id')->withTimestamps();
    }
}
