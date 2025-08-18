<?php

namespace Hetbo\Shelf\Contracts;

use Hetbo\Shelf\Models\File;
use Hetbo\Shelf\Models\Fileable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface FileableRepositoryInterface
{
    public function attach(Model $model, File $file, string $role, array $metadata = []): Fileable;

    public function detach(Model $model, File $file, string $role): bool;

    public function detachAll(Model $model, ?string $role = null): bool;

    public function getAttachments(string $fileableType, int $fileableId, ?string $role = null): Collection;

    public function getFileAttachments(int $fileId): Collection;

    public function updateAttachment(int $id, array $data): bool;

    public function updateOrder(array $attachmentIds): bool;

    public function hasAttachment(Model $model, File $file, ?string $role = null): bool;

    public function getByRole(Model $model, string $role): Collection;

    public function syncFiles(Model $model, array $fileIds, string $role): void;
}