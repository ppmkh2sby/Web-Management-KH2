<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Enum\Role;
use App\Models\Santri;
use App\Models\Santri as SantriModel;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property Role $role
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'login_code',
        'phone',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'role' => Role::class,
    ];

    /**
     * Static lookup tim berdasarkan kode/NIS (fallback bila kolom tim kosong).
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

    /**
     * Singkatan standar nama tim.
     * Key disimpan dalam format lowercase/normalized agar mudah dicocokkan.
     */
    protected static array $TEAM_ABBREVIATIONS = [
        'ketertiban' => 'KTB',
        'ktb' => 'KTB',
        'kebersihan' => 'KBS',
        'kbs' => 'KBS',
        'keilmuan' => 'KBM',
        'kbm' => 'KBM',
        'pengurus harian' => 'PH',
        'pengurusharian' => 'PH',
        'ph' => 'PH',
    ];

    public function santri(){
        return $this->hasOne(Santri::class);
    }

    
    public function waliOf(){
        return $this->belongsToMany(Santri::class, 'santri_wali', 'wali_user_id', 'santri_id')->withTimestamps();
    }

    /**
     * Cek apakah santri ini anggota tim Ketertiban.
     */
    public function isKetertiban(): bool
    {
        if ($this->role !== Role::SANTRI) {
            return false;
        }

        return self::teamAbbreviation($this->teamName()) === 'KTB';
    }

    public function hasRole(Role|string $role): bool {
        return $this->role === ($role instanceof Role ? $role : Role::from($role));
    }

    public function scopeRole($q, Role|string $role)
    {
        $val = $role instanceof Role ? $role->value : (string) $role;
        return $q->where('role', $val);
    }

    /**
     * Ambil nama tim dengan fallback berlapis.
     */
    public function teamName(): string
    {
        // 1) relasi santri yang sudah dimuat
        $team = trim((string) ($this->santri?->tim ?? ''));
        if ($team !== '') {
            return $team;
        }

        // 2) cari santri by user_id jika ada
        $byUser = optional(SantriModel::where('user_id', $this->id)->first())->tim ?? '';
        $byUser = trim((string) $byUser);
        if ($byUser !== '') {
            return $byUser;
        }

        // 3) fallback by code/login_code
        $code = $this->santri?->code ?? $this->login_code ?? null;
        if ($code) {
            $byCode = optional(SantriModel::where('code', $code)->first())->tim ?? '';
            $byCode = trim((string) $byCode);
            if ($byCode !== '') {
                return $byCode;
            }

            // 4) fallback ke lookup statis jika tim di DB kosong
            if (array_key_exists($code, self::$TEAM_LOOKUP)) {
                return (string) (self::$TEAM_LOOKUP[$code] ?? '');
            }
        }

        return '';
    }

    /**
     * Normalisasi nama tim ke singkatan yang dipakai UI.
     * Jika tim belum dipetakan, kembalikan nama aslinya.
     */
    public static function teamAbbreviation(?string $team): string
    {
        $teamRaw = trim((string) $team);
        if ($teamRaw === '') {
            return '';
        }

        $normalized = strtolower((string) preg_replace('/\s+/', ' ', $teamRaw));
        $compact = str_replace(' ', '', $normalized);

        return self::$TEAM_ABBREVIATIONS[$normalized]
            ?? self::$TEAM_ABBREVIATIONS[$compact]
            ?? $teamRaw;
    }

}
