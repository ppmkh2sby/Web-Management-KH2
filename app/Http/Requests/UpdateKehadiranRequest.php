<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Kehadiran;

class UpdateKehadiranRequest extends FormRequest
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
            'tanggal' => ['sometimes', 'date'],
            'status' => ['sometimes', Rule::in(Kehadiran::STATUSES)],
            'kegiatan' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
        ];
    }
}
