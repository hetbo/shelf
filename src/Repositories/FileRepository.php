<?php

namespace Hetbo\Shelf\Repositories;

use Hetbo\Shelf\Contracts\FileRepositoryInterface;
use Hetbo\Shelf\Models\File;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

class FileRepository implements FileRepositoryInterface
{
    public function create(array $data): File
    {
        return File::create($data);
    }

    public function find(int $id): ?File
    {
        return File::find($id);
    }

    public function findOrFail(int $id): File
    {
        return File::findOrFail($id);
    }

    public function update(int $id, array $data): File
    {
        $file = $this->findOrFail($id);
        $file->update($data);

        return $file->fresh();
    }

    public function delete(int $id): bool
    {
        $file = $this->findOrFail($id);

        return $file->delete();
    }

    public function findByHash(string $hash): ?File
    {
        return File::where('hash', $hash)->first();
    }

    public function getAllByUser(int $userId): Collection
    {
        return File::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getAllByFolder(?int $folderId = null): Collection
    {
        return File::where('folder_id', $folderId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = File::query();

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['folder_id'])) {
            $query->where('folder_id', $filters['folder_id']);
        }

        if (isset($filters['mime_type'])) {
            $query->where('mime_type', 'like', $filters['mime_type'] . '%');
        }

        if (isset($filters['search'])) {
            $query->where('filename', 'like', '%' . $filters['search'] . '%');
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function findByMimeType(string $mimeType): Collection
    {
        return File::where('mime_type', 'like', $mimeType . '%')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function search(string $query, array $filters = []): Collection
    {
        $builder = File::where('filename', 'like', '%' . $query . '%');

        if (isset($filters['user_id'])) {
            $builder->where('user_id', $filters['user_id']);
        }

        if (isset($filters['mime_type'])) {
            $builder->where('mime_type', 'like', $filters['mime_type'] . '%');
        }

        return $builder->orderBy('created_at', 'desc')->get();
    }
}