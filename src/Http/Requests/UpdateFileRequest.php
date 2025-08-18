<?php

namespace Hetbo\Shelf\Http\Requests;

use Hetbo\Shelf\Models\File;
use Hetbo\Shelf\Rules\FolderBelongsToUser;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        $fileId = $this->route('file');
        return File::where('id', $fileId)->value('user_id') === auth()->id();
//        return $this->route('file')->user_id === auth()->id();
    }

    public function rules(): array
    {
        return [
            'filename' => ['sometimes', 'string', 'max:255'],
            'folder_id' => ['nullable', 'integer', 'exists:shelf_folders,id', new FolderBelongsToUser(auth()->id())],
        ];
    }
}