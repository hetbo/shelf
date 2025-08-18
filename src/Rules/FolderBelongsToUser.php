<?php

namespace Hetbo\Shelf\Rules;

use Closure;
use Hetbo\Shelf\Models\Folder;
use Illuminate\Contracts\Validation\ValidationRule;

class FolderBelongsToUser implements ValidationRule
{
    public function __construct(private int $userId) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value) return;

        if (!Folder::where('id', $value)->where('user_id', $this->userId)->exists()) {
            $fail('The selected folder does not belong to the authenticated user.');
        }
    }
}