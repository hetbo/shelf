<?php

namespace Hetbo\Shelf\Services;

use Hetbo\Shelf\Contracts\FileMetadataRepositoryInterface;
use Hetbo\Shelf\DTOs\FileMetadataDTO;
use Illuminate\Support\Collection;

class FileMetadataService
{
    public function __construct(
        private FileMetadataRepositoryInterface $metadataRepository
    ) {}

    public function setMetadata(int $fileId, string $key, mixed $value): FileMetadataDTO
    {
        $metadata = $this->metadataRepository->setMetadata($fileId, $key, $value);
        return FileMetadataDTO::fromArray($metadata->toArray());
    }

    public function getMetadata(int $fileId, string $key): mixed
    {
        return $this->metadataRepository->getMetadata($fileId, $key);
    }

    public function getAllMetadata(int $fileId): Collection
    {
        return $this->metadataRepository->getAllMetadata($fileId);
    }

    public function updateMetadata(int $fileId, string $key, mixed $value): bool
    {
        return $this->metadataRepository->updateMetadata($fileId, $key, $value);
    }

    public function deleteMetadata(int $fileId, string $key): bool
    {
        return $this->metadataRepository->deleteMetadata($fileId, $key);
    }

    public function deleteAllMetadata(int $fileId): bool
    {
        return $this->metadataRepository->deleteAllMetadata($fileId);
    }

    public function hasMetadata(int $fileId, string $key): bool
    {
        return $this->metadataRepository->hasMetadata($fileId, $key);
    }

    public function setMultiple(int $fileId, array $metadata): Collection
    {
        return $this->metadataRepository->setMultiple($fileId, $metadata);
    }

    public function getMultiple(int $fileId, array $keys): Collection
    {
        return $this->metadataRepository->getMultiple($fileId, $keys);
    }
}