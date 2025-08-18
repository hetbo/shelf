<?php

namespace Hetbo\Shelf\Rules;

use Closure;
use Hetbo\Shelf\Models\File;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class UniqueFileHash implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) return;

        $hash = hash_file('sha256', $value->getPathname());
        if (File::where('hash', $hash)->exists()) {
            $fail('This file already exists in the system.');
        }
    }
}