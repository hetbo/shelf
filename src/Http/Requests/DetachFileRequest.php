<?php

namespace Hetbo\Shelf\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DetachFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file_id' => ['required', 'integer', 'exists:shelf_files,id'],
            'role' => ['required', 'string', 'max:50'],
        ];
    }
}