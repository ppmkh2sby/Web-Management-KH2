<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $santri_id
 * @property string $nama
 * @property string $status
 * @property int $kegiatan_id
 * @property string|null $catatan
 * @property string $waktu
 * @property-read Santri $santri
 * @property-read Kegiatan $kegiatan
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Presensi extends Model
{
    use HasFactory;

    public const STATUS = ['hadir', 'izin', 'alpha', 'sakit'];
    public const WAKTU = ['subuh', 'pagi', 'siang', 'sore', 'malam'];

    protected $fillable = [
        'santri_id',
        'nama',
        'status',
        'kegiatan_id',
        'catatan',
        'waktu',
        'created_at',
        'updated_at',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class);
    }
}
