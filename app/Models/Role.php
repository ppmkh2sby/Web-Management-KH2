<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['nama_role', 'deskripsi'];

    public function users()
    {
        return $this->hasMany(User::class, 'id_role');
    }
}
