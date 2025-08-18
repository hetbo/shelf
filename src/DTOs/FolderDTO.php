<?php

namespace Hetbo\Shelf\DTOs;

class FolderDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly ?int $parent_id,
        public readonly int $user_id,
        public readonly ?string $created_at = null,
        public readonly ?string $updated_at = null,
        public readonly ?string $deleted_at = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            parent_id: $data['parent_id'] ?? null,
            user_id: $data['user_id'],
            created_at: $data['created_at'] ?? null,
            updated_at: $data['updated_at'] ?? null,
            deleted_at: $data['deleted_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'parent_id' => $this->parent_id,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}