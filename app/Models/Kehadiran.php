<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $santri_id
 * @property \Illuminate\Support\Carbon $tanggal
 * @property string $status
 * @property string|null $kegiatan
 * @property string|null $keterangan
 */
class Kehadiran extends Model
{
    use HasFactory;

    public const STATUSES = ['hadir', 'izin', 'alpa'];

    protected $fillable = [
        'santri_id',
        'tanggal',
        'status',
        'kegiatan',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function scopeStatus($query, ?string $status)
    {
        if ($status) {
            $query->where('status', $status);
        }
    }

    public function scopeForSantri($query, int $santriId)
    {
        $query->where('santri_id', $santriId);
    }
}
