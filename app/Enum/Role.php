<?php 

namespace App\Enum;

enum Role: string 
{
    case ADMIN = 'admin';
    case SANTRI = 'santri';
    case DEWAN_GURU = 'degur';
    case PENGURUS = 'pengurus';
    case WALI = 'wali';

    public static function staff(): array
    {
        return [self::DEWAN_GURU->value,self::PENGURUS->value,];
    }
}