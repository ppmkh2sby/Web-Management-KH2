<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProgressKeilmuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'santri_id' => ['sometimes', 'exists:santris,id'],
            'judul' => ['sometimes', 'string', 'max:255'],
            'target' => ['sometimes', 'integer', 'min:0'],
            'capaian' => ['sometimes', 'integer', 'min:0'],
            'satuan' => ['nullable', 'string', 'max:30'],
            'level' => ['nullable', 'string', 'max:50'],
            'pembimbing' => ['nullable', 'string', 'max:100'],
            'catatan' => ['nullable', 'string'],
            'terakhir_setor' => ['nullable', 'date'],
        ];
    }
}
