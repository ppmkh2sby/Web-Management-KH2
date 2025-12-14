<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Presensi;
use App\Models\Kegiatan;

class UpdatePresensiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'santri_id' => ['sometimes', 'exists:santris,id'],
            'nama' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', Rule::in(Presensi::STATUS)],
            'kategori' => ['sometimes', Rule::in(Kegiatan::KATEGORI)],
            'waktu' => ['sometimes', Rule::in(Presensi::WAKTU)],
            'catatan' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
