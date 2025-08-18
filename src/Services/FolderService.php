<?php

namespace Hetbo\Shelf\Services;

use Hetbo\Shelf\Contracts\FolderRepositoryInterface;
use Hetbo\Shelf\DTOs\FolderDTO;
use Hetbo\Shelf\Models\Folder;
use Illuminate\Database\Eloquent\Collection;

class FolderService
{
    public function __construct(
        private FolderRepositoryInterface $folderRepository
    ) {}

    public function create(array $data): FolderDTO
    {
        $folder = $this->folderRepository->create($data);
        return FolderDTO::fromArray($folder->toArray());
    }

    public function find(int $id): ?FolderDTO
    {
        $folder = $this->folderRepository->find($id);
        return $folder ? FolderDTO::fromArray($folder->toArray()) : null;
    }

    public function findOrFail(int $id): FolderDTO
    {
        $folder = $this->folderRepository->findOrFail($id);
        return FolderDTO::fromArray($folder->toArray());
    }

    public function update(int $id, array $data): FolderDTO
    {
        $folder = $this->folderRepository->update($id, $data);
        return FolderDTO::fromArray($folder->toArray());
    }

    public function delete(int $id): bool
    {
        return $this->folderRepository->delete($id);
    }

    public function getUserFolders(int $userId): Collection
    {
        return $this->folderRepository->getAllByUser($userId);
    }

    public function getChildren(int $parentId): Collection
    {
        return $this->folderRepository->getChildren($parentId);
    }

    public function getRootFolders(int $userId): Collection
    {
        return $this->folderRepository->getRootFolders($userId);
    }

    public function getFolderTree(int $userId): Collection
    {
        return $this->folderRepository->getFolderTree($userId);
    }

    public function move(int $id, ?int $newParentId): FolderDTO
    {
        // Validate not moving to descendant
        if ($newParentId && $this->isDescendant($id, $newParentId)) {
            throw new \InvalidArgumentException('Cannot move folder to its own descendant.');
        }

        return $this->update($id, ['parent_id' => $newParentId]);
    }

    public function getFolderPath(int $id): array
    {
        $path = [];
        $folder = $this->folderRepository->find($id);

        while ($folder) {
            array_unshift($path, $folder);
            $folder = $folder->parent_id ? $this->folderRepository->find($folder->parent_id) : null;
        }

        return $path;
    }

    private function isDescendant(int $ancestorId, int $descendantId): bool
    {
        $folder = $this->folderRepository->find($descendantId);

        while ($folder && $folder->parent_id) {
            if ($folder->parent_id === $ancestorId) {
                return true;
            }
            $folder = $this->folderRepository->find($folder->parent_id);
        }

        return false;
    }
}