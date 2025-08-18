<?php

namespace Hetbo\Shelf\DTOs;

class FileDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $filename,
        public readonly string $path,
        public readonly string $disk,
        public readonly ?string $mime_type,
        public readonly ?int $size,
        public readonly ?string $hash,
        public readonly int $user_id,
        public readonly ?int $folder_id,
        public readonly ?string $created_at = null,
        public readonly ?string $updated_at = null,
        public readonly ?string $deleted_at = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            filename: $data['filename'],
            path: $data['path'],
            disk: $data['disk'],
            mime_type: $data['mime_type'] ?? null,
            size: $data['size'] ?? null,
            hash: $data['hash'] ?? null,
            user_id: $data['user_id'],
            folder_id: $data['folder_id'] ?? null,
            created_at: $data['created_at'] ?? null,
            updated_at: $data['updated_at'] ?? null,
            deleted_at: $data['deleted_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'filename' => $this->filename,
            'path' => $this->path,
            'disk' => $this->disk,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'hash' => $this->hash,
            'user_id' => $this->user_id,
            'folder_id' => $this->folder_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}