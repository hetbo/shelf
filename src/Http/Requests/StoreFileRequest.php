<?php

namespace Hetbo\Shelf\Http\Requests;

use Hetbo\Shelf\Rules\DiskExists;
use Hetbo\Shelf\Rules\FolderBelongsToUser;
use Illuminate\Foundation\Http\FormRequest;

class StoreFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:102400'], // 100MB
            'filename' => ['sometimes', 'string', 'max:255'],
            'disk' => ['sometimes', 'string', new DiskExists()],
            'folder_id' => ['nullable', 'integer', 'exists:shelf_folders,id', new FolderBelongsToUser(auth()->id())],
        ];
    }
}
