<?php

namespace Hetbo\Shelf\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderFilesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'string', 'max:50'],
            'file_ids' => ['required', 'array'],
            'file_ids.*' => ['integer', 'exists:shelf_files,id'],
        ];
    }
}