<?php

namespace Hetbo\Shelf\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class MaxFileSize implements ValidationRule
{
    public function __construct(private int $maxSizeInKb) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) return;

        if ($value->getSize() > ($this->maxSizeInKb * 1024)) {
            $fail("The file size cannot exceed {$this->maxSizeInKb}KB.");
        }
    }
}