<?php

namespace Hetbo\Shelf\DTOs;

class FileMetadataDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $file_id,
        public readonly string $key,
        public readonly string $value,
        public readonly ?string $created_at = null,
        public readonly ?string $updated_at = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            file_id: $data['file_id'],
            key: $data['key'],
            value: $data['value'],
            created_at: $data['created_at'] ?? null,
            updated_at: $data['updated_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'file_id' => $this->file_id,
            'key' => $this->key,
            'value' => $this->value,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}