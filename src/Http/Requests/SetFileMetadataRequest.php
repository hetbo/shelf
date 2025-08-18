<?php

namespace Hetbo\Shelf\Http\Requests;

use Hetbo\Shelf\Models\File;
use Illuminate\Foundation\Http\FormRequest;

class SetFileMetadataRequest extends FormRequest
{
    public function authorize(): bool
    {
//        return $this->route('file')->user_id === auth()->id();
        $fileId = $this->route('file');
        return File::where('id', $fileId)->value('user_id') === auth()->id();

    }

    protected function prepareForValidation(): void
    {
        $key = $this->route('key');

        if ($key !== null) {
            $this->merge(['key' => (string) $key]);
        }
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:255'],
            'value' => ['required'],
        ];
    }
}