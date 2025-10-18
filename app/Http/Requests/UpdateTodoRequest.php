<?php

namespace App\Http\Requests;

use App\Enums\TodoStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTodoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'min:1', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'string', Rule::in(TodoStatus::values())],
            'category_name' => ['sometimes', 'string', 'min:1', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('title')) {
            $data['title'] = trim((string) $this->input('title'));
        }

        if ($this->has('category_name')) {
            $data['category_name'] = trim((string) $this->input('category_name'));
        }

        if ($data !== []) {
            $this->merge($data);
        }
    }
}
