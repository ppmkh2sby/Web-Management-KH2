<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Kafarah;

class StoreKafarahRequest extends FormRequest
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
            'santri_ids' => ['required', 'array', 'min:1'],
            'santri_ids.*' => ['exists:santris,id'],
            'tanggal' => ['required', 'date'],
            'jenis_pelanggaran' => ['required', 'string'],
        ];
    }
}
