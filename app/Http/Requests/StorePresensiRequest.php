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
            'tanggal' => ['required', 'date'],
            'kategori' => ['required', Rule::in(Kegiatan::KATEGORI)],
            'waktu' => ['required', Rule::in(Kegiatan::WAKTU)],
            'catatan' => ['nullable', 'string', 'max:500'],
            'gender_scope' => ['nullable', Rule::in(['putra', 'putri', 'all'])],

            // opsional untuk ketertiban, dipakai untuk degur (kelas gabungan)
            'kelas_ids' => ['nullable', 'array'],
            'kelas_ids.*' => ['integer', 'exists:kelas,id'],

            // [santri_id => status], status kosong akan default alpha di controller
            'presensi' => ['nullable', 'array'],
            'presensi.*' => ['nullable', Rule::in(Presensi::STATUS)],

            // fallback mode lama (single input) tetap diterima
            'santri_id' => ['sometimes', 'exists:santris,id'],
            'status' => ['sometimes', Rule::in(Presensi::STATUS)],
            'nama' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
