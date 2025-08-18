<?php

namespace Hetbo\Shelf\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Storage;

class DiskExists implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!in_array($value, array_keys(config('filesystems.disks', [])))) {
            $fail('The selected disk does not exist in filesystem configuration.');
        }
    }
}