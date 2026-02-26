<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sesi extends Model
{
    use HasFactory;

    protected $table = 'sesi';

    protected $fillable = [
        'kegiatan_id',
        'tanggal',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function kelas(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'sesi_kelas', 'sesi_id', 'kelas_id')->withTimestamps();
    }

    public function presensis(): HasMany
    {
        return $this->hasMany(Presensi::class);
    }

    public function santriTerdaftar(): Builder
    {
        $kelasIds = $this->kelas()->pluck('kelas.id');
        return Santri::query()->whereIn('kelas_id', $kelasIds);
    }
}
