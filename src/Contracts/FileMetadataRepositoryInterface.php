<?php

namespace Hetbo\Shelf\Contracts;

use Hetbo\Shelf\Models\FileMetadata;
use Illuminate\Support\Collection;

interface FileMetadataRepositoryInterface
{
    public function setMetadata(int $fileId, string $key, mixed $value): FileMetadata;

    public function getMetadata(int $fileId, string $key): mixed;

    public function getAllMetadata(int $fileId): Collection;

    public function updateMetadata(int $fileId, string $key, mixed $value): bool;

    public function deleteMetadata(int $fileId, string $key): bool;

    public function deleteAllMetadata(int $fileId): bool;

    public function hasMetadata(int $fileId, string $key): bool;

    public function setMultiple(int $fileId, array $metadata): Collection;

    public function getMultiple(int $fileId, array $keys): Collection;
}