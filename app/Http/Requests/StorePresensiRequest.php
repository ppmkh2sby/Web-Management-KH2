<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Presensi;
use App\Models\Kegiatan;

class StorePresensiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'santri_id' => ['required', 'exists:santris,id'],
            'nama' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(Presensi::STATUS)],
            'kategori' => ['required', Rule::in(Kegiatan::KATEGORI)],
            'waktu' => ['required', Rule::in(Presensi::WAKTU)],
            'catatan' => ['nullable', 'string'],
        ];
    }
}
