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
            // Batch mode
            'presensi' => ['sometimes', 'array'],
            'presensi.*' => ['required', Rule::in(Presensi::STATUS)],

            // Single mode
            'santri_id' => ['required_without:presensi', 'exists:santris,id'],
            'status' => ['required_without:presensi', Rule::in(Presensi::STATUS)],

            // Shared
            'nama' => ['nullable', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
            'kategori' => ['required', Rule::in(Kegiatan::KATEGORI)],
            'waktu' => ['required', Rule::in(Presensi::WAKTU)],
            'catatan' => ['nullable', 'string'],
        ];
    }
}
