<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaliSantriRelasi extends Model
{
    use HasFactory;

    protected $table = 'wali_santri_relasi';

    protected $fillable = [
        'id_wali',
        'id_santri',
        'hubungan',
    ];

    public function wali()
    {
        return $this->belongsTo(User::class, 'id_wali');
    }

    public function santri()
    {
        return $this->belongsTo(User::class, 'id_santri');
    }
}
