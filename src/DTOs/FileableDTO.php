<?php

namespace Hetbo\Shelf\DTOs;

class FileableDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $file_id,
        public readonly string $fileable_type,
        public readonly int $fileable_id,
        public readonly string $role,
        public readonly int $order,
        public readonly ?array $metadata = null,
        public readonly ?string $created_at = null,
        public readonly ?string $updated_at = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            file_id: $data['file_id'],
            fileable_type: $data['fileable_type'],
            fileable_id: $data['fileable_id'],
            role: $data['role'],
            order: $data['order'],
            metadata: $data['metadata'] ?? null,
            created_at: $data['created_at'] ?? null,
            updated_at: $data['updated_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'file_id' => $this->file_id,
            'fileable_type' => $this->fileable_type,
            'fileable_id' => $this->fileable_id,
            'role' => $this->role,
            'order' => $this->order,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}