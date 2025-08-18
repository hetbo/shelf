<?php

namespace Hetbo\Shelf\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchFilesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'query' => ['required', 'string', 'min:2'],
            'folder_id' => ['nullable', 'integer', 'exists:shelf_folders,id'],
            'mime_type' => ['nullable', 'string'],
            'user_id' => ['nullable', 'integer'],
            'per_page' => ['integer', 'min:1', 'max:100'],
        ];
    }
}