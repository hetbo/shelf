<?php

namespace Hetbo\Shelf\Contracts;

use Hetbo\Shelf\Models\File;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface FileRepositoryInterface
{
    public function create(array $data): File;

    public function find(int $id): ?File;

    public function findOrFail(int $id): File;

    public function update(int $id, array $data): File;

    public function delete(int $id): bool;

    public function findByHash(string $hash): ?File;

    public function getAllByUser(int $userId): Collection;

    public function getAllByFolder(?int $folderId = null): Collection;

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function findByMimeType(string $mimeType): Collection;

    public function search(string $query, array $filters = []): Collection;
}