<?php

namespace Hetbo\Shelf\Repositories;

use Hetbo\Shelf\Contracts\FileableRepositoryInterface;
use Hetbo\Shelf\Models\File;
use Hetbo\Shelf\Models\Fileable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FileableRepository implements FileableRepositoryInterface
{
    public function attach(Model $model, File $file, string $role, array $metadata = []): Fileable
    {
        // Check if attachment already exists
        $existing = Fileable::where([
            'file_id' => $file->id,
            'fileable_type' => get_class($model),
            'fileable_id' => $model->id,
            'role' => $role,
        ])->first();

        if ($existing) {
            // Update existing attachment
            $existing->update(['metadata' => $metadata]);
            return $existing->fresh();
        }

        // Get the next order for this role
        $maxOrder = Fileable::where([
            'fileable_type' => get_class($model),
            'fileable_id' => $model->id,
            'role' => $role,
        ])->max('order') ?? 0;

        return Fileable::create([
            'file_id' => $file->id,
            'fileable_type' => get_class($model),
            'fileable_id' => $model->id,
            'role' => $role,
            'order' => $maxOrder + 1,
            'metadata' => $metadata,
        ]);
    }

    public function detach(Model $model, File $file, string $role): bool
    {
        return Fileable::where([
                'file_id' => $file->id,
                'fileable_type' => get_class($model),
                'fileable_id' => $model->id,
                'role' => $role,
            ])->delete() > 0;
    }

    public function detachAll(Model $model, ?string $role = null): bool
    {
        $query = Fileable::where([
            'fileable_type' => get_class($model),
            'fileable_id' => $model->id,
        ]);

        if ($role) {
            $query->where('role', $role);
        }

        return $query->delete() > 0;
    }

    public function getAttachments(string $fileableType, int $fileableId, ?string $role = null): Collection
    {
        $query = Fileable::with('file')
            ->where('fileable_type', $fileableType)
            ->where('fileable_id', $fileableId);

        if ($role) {
            $query->where('role', $role);
        }

        return $query->orderBy('order')->get();
    }

    public function getFileAttachments(int $fileId): Collection
    {
        return Fileable::with('fileable')
            ->where('file_id', $fileId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function updateAttachment(int $id, array $data): bool
    {
        $fileable = Fileable::findOrFail($id);
        return $fileable->update($data);
    }


    public function updateOrder(array $attachmentIds): bool
    {
        return DB::transaction(function () use ($attachmentIds) {
            foreach (array_values($attachmentIds) as $index => $id) {
                Fileable::whereKey($id)->update(['order' => $index + 1]);
            }
            return true;
        });
    }


    /*    public function updateOrder(array $attachmentIds): bool
        {
            return DB::transaction(function () use ($attachmentIds) {
                foreach ($attachmentIds as $index => $id) {
                    Fileable::where('id', $id)->update(['order' => $index + 1]);
                }
                return true;
            });
        }*/

    public function hasAttachment(Model $model, File $file, ?string $role = null): bool
    {
        $query = Fileable::where([
            'file_id' => $file->id,
            'fileable_type' => get_class($model),
            'fileable_id' => $model->id,
        ]);

        if ($role) {
            $query->where('role', $role);
        }

        return $query->exists();
    }

    public function getByRole(Model $model, string $role): Collection
    {
        return Fileable::with('file')
            ->where([
                'fileable_type' => get_class($model),
                'fileable_id' => $model->id,
                'role' => $role,
            ])
            ->orderBy('order')
            ->get();
    }

    public function syncFiles(Model $model, array $fileIds, string $role): void
    {
        DB::transaction(function () use ($model, $fileIds, $role) {
            // Remove existing attachments for this role
            Fileable::where([
                'fileable_type' => get_class($model),
                'fileable_id' => $model->id,
                'role' => $role,
            ])->delete();

            // Add new attachments
            foreach ($fileIds as $index => $fileId) {
                Fileable::create([
                    'file_id' => $fileId,
                    'fileable_type' => get_class($model),
                    'fileable_id' => $model->id,
                    'role' => $role,
                    'order' => $index + 1,
                ]);
            }
        });
    }
}