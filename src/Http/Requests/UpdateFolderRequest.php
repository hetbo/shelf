<?php

namespace Hetbo\Shelf\Http\Requests;

use Hetbo\Shelf\Models\Folder;
use Hetbo\Shelf\Rules\FolderBelongsToUser;
use Hetbo\Shelf\Rules\NotSelfParent;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFolderRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'parent_id' => [
                'nullable',
                'integer',
                'exists:shelf_folders,id',
                new FolderBelongsToUser(auth()->id()),
                new NotSelfParent($this->route('folder'))
            ],
        ];
    }
}