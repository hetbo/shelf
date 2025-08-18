<?php

namespace Hetbo\Shelf\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidMimeType implements ValidationRule
{
    public function __construct(private array $allowedTypes = []) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($this->allowedTypes)) return;

        if (!in_array($value, $this->allowedTypes)) {
            $fail('The file type is not allowed.');
        }
    }
}
