<?php

namespace Hetbo\Shelf\Http\Requests;

use Hetbo\Shelf\Rules\FileBelongsToUser;
use Illuminate\Foundation\Http\FormRequest;

class BulkFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file_ids' => ['required', 'array'],
            'file_ids.*' => ['integer', 'exists:shelf_files,id', new FileBelongsToUser(auth()->id())],
            'action' => ['required', 'string', 'in:delete,move'],
            'folder_id' => ['required_if:action,move', 'nullable', 'integer', 'exists:shelf_folders,id'],
        ];
    }
}
