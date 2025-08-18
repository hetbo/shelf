<?php

namespace Hetbo\Shelf\Repositories;

use Hetbo\Shelf\Contracts\FolderRepositoryInterface;
use Hetbo\Shelf\Models\Folder;
use Illuminate\Database\Eloquent\Collection;

class FolderRepository implements FolderRepositoryInterface
{
    public function create(array $data): Folder
    {
        return Folder::create($data);
    }

    public function find(int $id): ?Folder
    {
        return Folder::find($id);
    }

    public function findOrFail(int $id): Folder
    {
        return Folder::findOrFail($id);
    }

    public function update(int $id, array $data): Folder
    {
        $folder = $this->findOrFail($id);
        $folder->update($data);
        return $folder->fresh();
    }

    public function delete(int $id): bool
    {
        $folder = $this->findOrFail($id);
        return $folder->delete();
    }

    public function getAllByUser(int $userId): Collection
    {
        return Folder::where('user_id', $userId)->get();
    }

    public function getChildren(int $parentId): Collection
    {
        return Folder::where('parent_id', $parentId)->get();
    }

    public function getRootFolders(int $userId): Collection
    {
        return Folder::where('user_id', $userId)
            ->whereNull('parent_id')
            ->get();
    }

    public function getFolderTree(int $userId): Collection
    {
        return Folder::where('user_id', $userId)
            ->with(['children' => function ($query) {
                $query->orderBy('name');
            }])
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();
    }
}