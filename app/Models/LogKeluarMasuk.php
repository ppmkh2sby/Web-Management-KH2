<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogKeluarMasuk extends Model
{
    use HasFactory;

    public const STATUSES = ['disetujui', 'proses', 'tercatat', 'ditolak'];

    protected $fillable = [
        'santri_id',
        'tanggal_pengajuan',
        'jenis',
        'rentang',
        'status',
        'petugas',
        'catatan',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'date',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }
}
