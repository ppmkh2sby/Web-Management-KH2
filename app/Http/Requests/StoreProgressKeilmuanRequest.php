<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProgressKeilmuanRequest extends FormRequest
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
            'santri_id' => ['required', 'exists:santris,id'],
            'judul' => ['required', 'string', 'max:255'],
            'target' => ['required', 'integer', 'min:0'],
            'capaian' => ['required', 'integer', 'min:0'],
            'satuan' => ['nullable', 'string', 'max:30'],
            'level' => ['nullable', 'string', 'max:50'],
            'pembimbing' => ['nullable', 'string', 'max:100'],
            'catatan' => ['nullable', 'string'],
            'terakhir_setor' => ['nullable', 'date'],
        ];
    }
}
