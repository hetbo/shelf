<?php

namespace Hetbo\Shelf\Rules;

use Closure;
use Hetbo\Shelf\Services\FolderService;
use Illuminate\Contracts\Validation\ValidationRule;

class NotSelfParent implements ValidationRule
{
    public function __construct(private ?int $folderId = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value || !$this->folderId) return;

        // 1. Check self
        if ($value == $this->folderId) {
            $fail('A folder cannot be its own parent.');
            return;
        }

        // 2. Check descendants
        $descendantIds = $this->getDescendantIds($this->folderId);

        if (in_array($value, $descendantIds)) {
            $fail('A folder cannot be assigned as a child of its own descendants.');
        }
    }

    /**
     * Recursively get all descendant folder IDs using FolderService
     */
    protected function getDescendantIds(int $parentId): array
    {
        $ids = [];
        $children = app(FolderService::class)->getChildren($parentId);

        foreach ($children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getDescendantIds($child->id));
        }

        return $ids;
    }
}