<?php

namespace Hetbo\Shelf\Rules;

use Closure;
use Hetbo\Shelf\Models\File;
use Illuminate\Contracts\Validation\ValidationRule;

class FileBelongsToUser implements ValidationRule
{
    public function __construct(private int $userId) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!File::where('id', $value)->where('user_id', $this->userId)->exists()) {
            $fail('The selected file does not belong to the authenticated user.');
        }
    }
}