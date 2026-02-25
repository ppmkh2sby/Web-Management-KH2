<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $code
 * @property string $nama_lengkap
 * @property string|null $tim
 * @property string|null $kampus
 * @property string|null $jurusan
 * @property string|null $gender
 * @property int|null $kelas_id
 */
class Santri extends Model
{
    protected  $fillable = [
        'user_id',
        'code',
        'nama_lengkap',
        'tim',
        'kelas_id',
        'kampus',
        'jurusan',
        'gender',
    ];

    /**
     * Static fallback mapping untuk tim berdasarkan kode/NIS.
     */
    protected static array $TEAM_LOOKUP = [
        // PUTRA
        '022121013' => 'PH',
        '022222001' => 'Kebersihan',
        '022222006' => 'Sekben',
        '022323002' => 'KBM',
        '022323004' => 'PH',
        '022323006' => 'Acara',
        '022424001' => 'KTB',
        '022424006' => 'Sekben',
        '022424007' => 'Acara',
        '022424008' => 'KTB',
        '022424010' => 'Ukppt',
        '022424011' => 'KBM',
        '022424012' => 'Ukppt',
        '022423017' => 'Kebersihan',
        '022424019' => 'KBM',
        '022525004' => 'Kebersihan',
        '022525005' => 'Ukppt',
        '022525006' => 'Sekben',
        '022525007' => 'Acara',
        '022525013' => 'KTB',
        '022524015' => 'Acara',

        // PUTRI
        '022121007' => 'Ukppt',
        '022222004' => 'Acara',
        '022323001' => 'PH',
        '022323003' => 'Kebersihan',
        '022323005' => 'KBM',
        '022424002' => 'Acara',
        '022424003' => 'Ukppt',
        '022424004' => 'KBM',
        '022424005' => 'Kebersihan',
        '022424009' => 'Sekben',
        '022424013' => 'Kebersihan',
        '022424014' => 'PH',
        '022424015' => 'Acara',
        '022424016' => 'KTB',
        '022424018' => 'Sekben',
        '022525001' => 'Ukppt',
        '022525002' => 'Acara',
        '022525003' => 'KTB',
        '022525008' => 'KTB',
        '022525009' => 'Kebersihan',
        '022525010' => 'Acara',
        '022525011' => 'KBM',
        '022525012' => 'Ukppt',
        '022525014' => 'KBM',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function walis(){
        return $this->belongsToMany(User::class, 'santri_wali', 'santri_id','wali_user_id')->withTimestamps();
    }

    public function kelas() // <-- inilah yang dicari blade & controller
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function presensis()
    {
        return $this->hasMany(Presensi::class);
    }

    /**
     * Ambil mapping kode => tim (fallback).
     */
    public static function teamLookup(): array
    {
        return self::$TEAM_LOOKUP;
    }

    /**
     * Ambil tim dari mapping kode/NIS.
     */
    public static function teamFromCode(?string $code): ?string
    {
        if ($code && array_key_exists($code, self::$TEAM_LOOKUP)) {
            return self::$TEAM_LOOKUP[$code];
        }
        return null;
    }

    /**
     * Tim dengan fallback mapping jika kolom tim kosong.
     */
    public function getTimResolvedAttribute(): ?string
    {
        $code = $this->code ?? null;
        if ($code) {
            $mapped = self::teamFromCode($code);
            if ($mapped !== null && $mapped !== '') {
                return $mapped;
            }
        }

        $tim = trim((string) ($this->tim ?? ''));
        if ($tim !== '') {
            return $tim;
        }

        return null;
    }
}
