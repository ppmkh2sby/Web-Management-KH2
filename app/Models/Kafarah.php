<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $santri_id
 * @property \Illuminate\Support\Carbon $tanggal
 * @property string $jenis_pelanggaran
 * @property string $kafarah
 * @property int $jumlah_setor
 * @property int $tanggungan
 * @property string|null $tenggat
 */
class Kafarah extends Model
{
    use HasFactory;

    public const JENIS_PELANGGARAN = [
        'tidak_sholat_subuh_di_masjid' => 'Tidak sholat subuh di masjid',
        'tidak_sambung_pagi' => 'Tidak sambung pagi',
        'tidak_sambung_malam' => 'Tidak sambung malam',
        'tidak_apel_malam' => 'Tidak apel malam',
        'tidak_sholat_malam' => 'Tidak sholat malam',
        'terlambat_kembali_ke_ppm' => 'Terlambat kembali ke PPM',
        'tidak_asrama_sesi_pagi' => 'Tidak Asrama sesi pagi',
        'tidak_asrama_sesi_siang' => 'Tidak Asrama sesi siang',
        'tidak_asrama_sesi_sore' => 'Tidak Asrama sesi sore',
        'tidak_asrama_sesi_malam' => 'Tidak Aseama sesi malam',
    ];

    public const KAFARAH_MAPPING = [
        'tidak_sholat_subuh_di_masjid' => ['kafarah' => 'Istigfar 250', 'jumlah' => 250],
        'tidak_sambung_pagi' => ['kafarah' => 'Istigfar 150', 'jumlah' => 150],
        'tidak_sambung_malam' => ['kafarah' => 'Istigfar 150', 'jumlah' => 150],
        'tidak_apel_malam' => ['kafarah' => 'Istigfar 250', 'jumlah' => 250],
        'tidak_sholat_malam' => ['kafarah' => 'Istigfar 150', 'jumlah' => 150],
        'terlambat_kembali_ke_ppm' => ['kafarah' => 'Membayar 10K/15K/25K', 'jumlah' => 10000],
        'tidak_asrama_sesi_pagi' => ['kafarah' => 'Istigfar 150', 'jumlah' => 150],
        'tidak_asrama_sesi_siang' => ['kafarah' => 'Istigfar 150', 'jumlah' => 150],
        'tidak_asrama_sesi_sore' => ['kafarah' => 'Istigfar 150', 'jumlah' => 150],
        'tidak_asrama_sesi_malam' => ['kafarah' => 'Istigfar 150', 'jumlah' => 150],
    ];

    protected $fillable = [
        'santri_id',
        'tanggal',
        'jenis_pelanggaran',
        'kafarah',
        'jumlah_setor',
        'tanggungan',
        'tenggat',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah_setor' => 'integer',
        'tanggungan' => 'integer',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function scopeForSantri($query, int $santriId)
    {
        $query->where('santri_id', $santriId);
    }

    public static function getKafarahFromPelanggaran(string $jenisPelanggaran): array
    {
        return self::KAFARAH_MAPPING[$jenisPelanggaran] ?? ['kafarah' => 'Istigfar 150', 'jumlah' => 150];
    }

    /**
     * Get the label for jenis pelanggaran
     */
    public function getJenisPelanggaranLabelAttribute(): string
    {
        return self::JENIS_PELANGGARAN[$this->jenis_pelanggaran] ?? $this->jenis_pelanggaran;
    }
}
