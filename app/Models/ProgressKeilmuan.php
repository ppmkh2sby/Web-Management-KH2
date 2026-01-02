<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgressKeilmuan extends Model
{
    use HasFactory;

    public const LEVEL_QURAN = 'al-quran';
    public const LEVEL_HADITS = 'al-hadits';

    protected $fillable = [
        'santri_id',
        'judul',
        'target',
        'capaian',
        'satuan',
        'level',
        'catatan',
        'pembimbing',
        'terakhir_setor',
    ];

    protected $casts = [
        'terakhir_setor' => 'date',
    ];

    protected $appends = ['persentase'];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function getPersentaseAttribute(): int
    {
        if (!$this->target) {
            return 0;
        }

        return (int) min(100, round(($this->capaian / $this->target) * 100));
    }
}
