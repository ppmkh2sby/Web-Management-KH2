<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kegiatan extends Model
{
    use HasFactory;

    public const KATEGORI = ['asrama', 'sambung', 'keakraban'];
    public const WAKTU = ['subuh', 'pagi', 'siang', 'sore', 'malam'];

    protected $fillable = [
        'kategori',
        'waktu',
        'catatan',
    ];

    public function presensis(): HasMany
    {
        return $this->hasMany(Presensi::class);
    }

    public function sesis(): HasMany
    {
        return $this->hasMany(Sesi::class);
    }
}
