<?php

namespace Hetbo\Shelf\Http\Requests;

use Hetbo\Shelf\Models\File;
use Hetbo\Shelf\Models\Folder;
use Illuminate\Foundation\Http\FormRequest;

class BulkFileMetadataRequest extends FormRequest
{
    public function authorize(): bool
    {
//        return $this->route('file')->user_id === auth()->id();
        $fileId = $this->route('file');
        return File::where('id', $fileId)->value('user_id') === auth()->id();

    }

    public function rules(): array
    {
        return [
            'metadata' => ['required', 'array'],
            'metadata.*' => ['required'],
        ];
    }
}