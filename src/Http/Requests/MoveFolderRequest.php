<?php

namespace Hetbo\Shelf\Http\Requests;

use Hetbo\Shelf\Models\File;
use Hetbo\Shelf\Models\Folder;
use Illuminate\Foundation\Http\FormRequest;

class MoveFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
//        return $this->route('folder')->user_id === auth()->id();
        $folderId = $this->route('folder');
        return Folder::where('id', $folderId)->value('user_id') === auth()->id();

    }

    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'integer', 'exists:shelf_folders,id'],
        ];
    }
}