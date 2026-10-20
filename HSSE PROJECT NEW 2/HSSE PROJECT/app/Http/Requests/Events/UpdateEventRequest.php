<?php

namespace App\Http\Requests\Events;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:100'],
            'event_date' => ['required', 'date'],
            'start_time' => ['nullable'],
            'end_time' => ['nullable'],
            'location' => ['required', 'string', 'max:255'],
            'participants' => ['nullable', 'integer', 'min:0'],
            'pic' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:Direncanakan,Berlangsung,Selesai,Dibatalkan'],
            'description' => ['nullable', 'string'],
            'documentation_files.*' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul kegiatan wajib diisi.',
            'category.required' => 'Kategori kegiatan wajib dipilih.',
            'unit.required' => 'Komponen / unit wajib diisi.',
            'event_date.required' => 'Tanggal kegiatan wajib diisi.',
            'event_date.date' => 'Format tanggal kegiatan tidak valid.',
            'location.required' => 'Lokasi kegiatan wajib diisi.',
            'status.required' => 'Status kegiatan wajib dipilih.',
            'documentation_files.*.max' => 'Ukuran file dokumentasi maksimal 10 MB per file.',
            'documentation_files.*.mimes' => 'Format file yang didukung: JPG, PNG, WEBP, PDF, DOC, DOCX.',
        ];
    }
}

