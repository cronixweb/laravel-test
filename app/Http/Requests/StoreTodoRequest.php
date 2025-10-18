<?php

namespace App\Http\Requests;

use App\Enums\TodoStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTodoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:1', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'string', Rule::in(TodoStatus::values())],
            'category_name' => ['required', 'string', 'min:1', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => $this->title !== null ? trim((string) $this->title) : null,
            'category_name' => $this->category_name !== null ? trim((string) $this->category_name) : null,
        ]);
    }
}
