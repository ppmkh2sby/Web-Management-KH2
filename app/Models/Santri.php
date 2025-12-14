<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $code
 * @property string $nama_lengkap
 * @property string|null $tim
 */
class Santri extends Model
{
    protected  $fillable = ['user_id', 'code', 'nama_lengkap', 'tim'];

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
}
