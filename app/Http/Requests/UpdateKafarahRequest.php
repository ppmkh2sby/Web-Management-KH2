<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Kafarah;

class UpdateKafarahRequest extends FormRequest
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
            'jenis_pelanggaran' => ['sometimes', 'string'],
            'kafarah' => ['nullable', 'string'],
            'jumlah_setor' => ['nullable', 'integer'],
            'tanggungan' => ['nullable', 'integer'],
            'tenggat' => ['nullable', 'string'],
        ];
    }
}
