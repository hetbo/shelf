<?php

namespace Hetbo\Shelf\Http\Requests;

use Hetbo\Shelf\Rules\FileBelongsToUser;
use Illuminate\Foundation\Http\FormRequest;

class AttachFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file_id' => ['required', 'integer', 'exists:shelf_files,id', new FileBelongsToUser(auth()->id())],
            'fileable_type' => ['required', 'string', 'max:255'],
            'fileable_id' => ['required', 'integer'],
            'role' => ['required', 'string', 'max:50'],
            'metadata' => ['sometimes', 'array'],
        ];
    }
}