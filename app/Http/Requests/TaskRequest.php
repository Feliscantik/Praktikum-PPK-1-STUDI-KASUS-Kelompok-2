<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Otorisasi ditangani Policy di controller.
    }

    public function rules(): array
    {
        return [
            // SRS-004: judul wajib, deskripsi opsional.
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            // SRS-005: prioritas & tenggat waktu.
            'priority' => ['required', Rule::in(Task::PRIORITIES)],
            'due_date' => ['nullable', 'date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'judul tugas',
            'description' => 'deskripsi',
            'priority' => 'prioritas',
            'due_date' => 'tenggat waktu',
        ];
    }
}
