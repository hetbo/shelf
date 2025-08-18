<?php

namespace Hetbo\Shelf\Contracts;

use Hetbo\Shelf\Models\Folder;
use Illuminate\Database\Eloquent\Collection;

interface FolderRepositoryInterface
{
    public function create(array $data): Folder;

    public function find(int $id): ?Folder;

    public function findOrFail(int $id): Folder;

    public function update(int $id, array $data): Folder;

    public function delete(int $id): bool;

    public function getAllByUser(int $userId): Collection;

    public function getChildren(int $parentId): Collection;

    public function getRootFolders(int $userId): Collection;

    public function getFolderTree(int $userId): Collection;
}
