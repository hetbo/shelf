<?php

namespace Hetbo\Shelf\Traits;

use Hetbo\Shelf\Models\File;
use Hetbo\Shelf\Models\Fileable;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

trait HasFiles
{
    public function files(): MorphToMany
    {
        return $this->morphToMany(File::class, 'fileable', 'shelf_fileables')
            ->withPivot(['role', 'order', 'metadata'])
            ->withTimestamps()
            ->orderBy('shelf_fileables.order');
    }

    public function fileables(): MorphMany
    {
        return $this->morphMany(Fileable::class, 'fileable');
    }

    public function attachFile(File $file, string $role, array $metadata = []): Fileable
    {
        // Get the next order for this role
        $maxOrder = $this->fileables()
            ->where('role', $role)
            ->max('order') ?? 0;

        return $this->fileables()->updateOrCreate(
            [
                'file_id' => $file->id,
                'role' => $role,
            ],
            [
                'order' => $maxOrder + 1,
                'metadata' => $metadata,
            ]
        );
    }

    public function detachFile(File $file, string $role): bool
    {
        return $this->fileables()
                ->where('file_id', $file->id)
                ->where('role', $role)
                ->delete() > 0;
    }

    public function detachAllFiles(?string $role = null): bool
    {
        $query = $this->fileables();

        if ($role) {
            $query->where('role', $role);
        }

        return $query->delete() > 0;
    }

    public function getFilesByRole(string $role): Collection
    {
        return $this->files()
            ->wherePivot('role', $role)
            ->orderBy('shelf_fileables.order')
            ->get();
    }

    public function hasFile(File $file, ?string $role = null): bool
    {
        $query = $this->fileables()->where('file_id', $file->id);

        if ($role) {
            $query->where('role', $role);
        }

        return $query->exists();
    }

    public function syncFiles(array $fileIds, string $role): void
    {
        // Remove existing files for this role
        $this->fileables()->where('role', $role)->delete();

        // Add new files
        foreach ($fileIds as $index => $fileId) {
            $this->fileables()->create([
                'file_id' => $fileId,
                'role' => $role,
                'order' => $index + 1,
            ]);
        }
    }

    public function reorderFiles(string $role, array $fileIds): void
    {
        foreach ($fileIds as $index => $fileId) {
            $this->fileables()
                ->where('file_id', $fileId)
                ->where('role', $role)
                ->update(['order' => $index + 1]);
        }
    }

    public function getFirstFileByRole(string $role): ?File
    {
        return $this->getFilesByRole($role)->first();
    }

    public function getFileRoles(): Collection
    {
        return $this->fileables()
            ->select('role')
            ->distinct()
            ->pluck('role');
    }

    public function getFilesWithMetadata(string $role): Collection
    {
        return $this->fileables()
            ->with('file')
            ->where('role', $role)
            ->orderBy('order')
            ->get();
    }
}