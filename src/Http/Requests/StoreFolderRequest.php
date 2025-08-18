<?php

namespace Hetbo\Shelf\Http\Requests;

use Hetbo\Shelf\Rules\FolderBelongsToUser;
use Hetbo\Shelf\Rules\NotSelfParent;
use Illuminate\Foundation\Http\FormRequest;

class StoreFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:shelf_folders,id', new FolderBelongsToUser(auth()->id())],
        ];
    }
}
