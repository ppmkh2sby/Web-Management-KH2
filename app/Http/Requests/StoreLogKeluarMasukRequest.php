<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\LogKeluarMasuk;

class StoreLogKeluarMasukRequest extends FormRequest
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
            'tanggal_pengajuan' => ['required', 'date'],
            'jenis' => ['required', 'string', 'max:255'],
            'rentang' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(LogKeluarMasuk::STATUSES)],
            'petugas' => ['nullable', 'string', 'max:100'],
            'catatan' => ['nullable', 'string'],
        ];
    }
}
