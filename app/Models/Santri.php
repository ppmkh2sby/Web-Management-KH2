<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Santri extends Model
{
    protected  $fillable = ['user_id', 'code', 'nama_lengkap'];

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
}
