<?php

namespace Hetbo\Shelf\Services;

use Hetbo\Shelf\Contracts\FileableRepositoryInterface;
use Hetbo\Shelf\DTOs\FileableDTO;
use Hetbo\Shelf\Models\File;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class FileableService
{
    public function __construct(
        private FileableRepositoryInterface $fileableRepository
    ) {}

    public function attach(Model $model, File $file, string $role, array $metadata = []): FileableDTO
    {
        $fileable = $this->fileableRepository->attach($model, $file, $role, $metadata);
        return FileableDTO::fromArray($fileable->toArray());
    }

    public function detach(Model $model, File $file, string $role): bool
    {
        return $this->fileableRepository->detach($model, $file, $role);
    }

    public function detachAll(Model $model, ?string $role = null): bool
    {
        return $this->fileableRepository->detachAll($model, $role);
    }

    public function getAttachments(string $fileableType, int $fileableId, ?string $role = null): Collection
    {
        return $this->fileableRepository->getAttachments($fileableType, $fileableId, $role);
    }

    public function getFileAttachments(int $fileId): Collection
    {
        return $this->fileableRepository->getFileAttachments($fileId);
    }

    public function updateAttachment(int $id, array $data): bool
    {
        return $this->fileableRepository->updateAttachment($id, $data);
    }

    public function reorderFiles(Model $model, string $role, array $fileIds): bool
    {
        // Validate all files belong to the model and role
        $existingAttachments = $this->fileableRepository->getByRole($model, $role);
        $existingFileIds = $existingAttachments->pluck('file_id')->toArray();

        if (array_diff($fileIds, $existingFileIds)) {
            throw new \InvalidArgumentException('Some files are not attached to this model with the specified role.');
        }

        return $this->fileableRepository->updateOrder($fileIds);
    }

    public function syncFiles(Model $model, array $fileIds, string $role): void
    {
        $this->fileableRepository->syncFiles($model, $fileIds, $role);
    }

    public function hasAttachment(Model $model, File $file, ?string $role = null): bool
    {
        return $this->fileableRepository->hasAttachment($model, $file, $role);
    }

    public function getByRole(Model $model, string $role): Collection
    {
        return $this->fileableRepository->getByRole($model, $role);
    }
}