<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Enum\Role;
use App\Models\Santri;

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

        $tim = trim((string) ($this->santri?->tim ?? ''));

        if ($tim === '') {
            // Fallback to direct lookup if relasi belum termuat
            $tim = trim((string) 
                
                
                optional(Santri::where('user_id', $this->id)->first())->tim
            );
        }

        return strcasecmp($tim, 'ketertiban') === 0;
    }

    public function hasRole(Role|string $role): bool {
        return $this->role === ($role instanceof Role ? $role : Role::from($role));
    }

    public function scopeRole($q, Role|string $role)
    {
        $val = $role instanceof Role ? $role->value : (string) $role;
        return $q->where('role', $val);
    }

}
