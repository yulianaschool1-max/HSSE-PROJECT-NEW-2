<?php

namespace App\Http\Requests;

use App\Models\IssueFollowUp;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIssueFollowUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $routeParam = $this->route('issue_follow_up');
        $existing = $routeParam instanceof IssueFollowUp
            ? $routeParam
            : ($routeParam ? IssueFollowUp::find($routeParam) : null);

        if ($existing) {
            $this->merge([
                'hsse_division' => $this->input('hsse_division', $existing->hsse_division),
                'reported_date' => $this->input('reported_date', $existing->reported_date ? $existing->reported_date->format('Y-m-d') : null),
                'title' => $this->input('title', $existing->title),
                'category' => $this->input('category', $existing->category),
                'unit' => $this->input('unit', $existing->unit),
                'description' => $this->input('description', $existing->description),
                'pic' => $this->input('pic', $existing->pic),
            ]);
        }

        if ($this->has('hsse_division') && $this->input('hsse_division') !== null) {
            $raw = strtoupper(trim((string) $this->input('hsse_division')));
            $normalized = match ($raw) {
                'H', 'HUMAN SAFETY', 'H — HUMAN SAFETY', 'H - HUMAN SAFETY', 'HEALTH', 'H — HEALTH', 'H - HEALTH', 'HEALTH & SAVETY', 'HEALTH AND SAVETY', 'H — HEALTH & SAVETY', 'H - HEALTH & SAVETY' => 'H',
                'S_SOCIAL', 'S', 'SOSIAL', 'SOCIAL', 'S — SOSIAL', 'S - SOSIAL', 'S — SOCIAL', 'S - SOCIAL' => 'S_SOCIAL',
                'S_SECURITY', 'SCURITY', 'SECURITY', 'S — SCURITY', 'S - SCURITY', 'S — SECURITY', 'S - SECURITY' => 'S_SECURITY',
                'E', 'EMVIRONMENT', 'ENVIRONMENT', 'E — EMVIRONMENT', 'E - EMVIRONMENT', 'E — ENVIRONMENT', 'E - ENVIRONMENT' => 'E',
                default => $raw,
            };
            $this->merge(['hsse_division' => $normalized]);
        }
    }

    public function rules(): array
    {
        return [
            'hsse_division' => ['required', 'string', Rule::in(array_keys(IssueFollowUp::HSSE_DIVISIONS))],
            'reported_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:50'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'action_plan' => ['nullable', 'string'],
            'pic' => ['required', 'string', 'max:255'],
            'due_date' => ['nullable', 'date', 'after_or_equal:reported_date'],
            'priority' => ['nullable', 'string', Rule::in(IssueFollowUp::PRIORITIES)],
            'status' => ['nullable', 'string', Rule::in(['Open', 'In Progress', 'Closed', 'Close'])],
            'notes' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'hsse_division.required' => 'Divisi HSSE (H — Health & Savety, S — Sosial, S — Scurity, atau E — Emvironment) wajib dipilih.',
            'hsse_division.in' => 'Divisi HSSE tidak valid. Pilih salah satu dari H — Health & Savety, S — Sosial, S — Scurity, atau E — Emvironment.',
            'reported_date.required' => 'Tanggal temuan wajib diisi.',
            'reported_date.date' => 'Format tanggal temuan tidak valid.',
            'title.required' => 'Judul temuan wajib diisi.',
            'title.max' => 'Judul temuan maksimal 255 karakter.',
            'category.required' => 'Kategori temuan wajib dipilih.',
            'unit.required' => 'Unit / Area wajib dipilih.',
            'description.required' => 'Deskripsi temuan wajib diisi.',
            'pic.required' => 'Penanggung jawab (PIC) wajib diisi.',
            'due_date.date' => 'Format tanggal tenggat tidak valid.',
            'due_date.after_or_equal' => 'Tanggal tenggat tidak boleh lebih awal dari tanggal temuan.',
            'priority.in' => 'Tingkat risiko / prioritas tidak valid.',
            'status.in' => 'Status temuan tidak valid.',
            'attachment.mimes' => 'Lampiran harus berupa gambar (JPG, PNG, WEBP) atau dokumen (PDF, DOC, DOCX).',
            'attachment.max' => 'Ukuran file lampiran maksimal 5 MB.',
        ];
    }
}

