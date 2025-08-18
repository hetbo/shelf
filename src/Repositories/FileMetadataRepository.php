<?php

namespace Hetbo\Shelf\Repositories;

use Hetbo\Shelf\Contracts\FileMetadataRepositoryInterface;
use Hetbo\Shelf\Models\FileMetadata;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FileMetadataRepository implements FileMetadataRepositoryInterface
{
    public function setMetadata(int $fileId, string $key, mixed $value): FileMetadata
    {
        $existing = FileMetadata::where('file_id', $fileId)
            ->where('key', $key)
            ->first();

        $encodedValue = is_string($value) ? $value : json_encode($value);

        if ($existing) {
            $existing->update(['value' => $encodedValue]);
            return $existing->fresh();
        }

        return FileMetadata::create([
            'file_id' => $fileId,
            'key' => $key,
            'value' => $encodedValue,
        ]);
    }

    public function getMetadata(int $fileId, string $key): mixed
    {
        $metadata = FileMetadata::where('file_id', $fileId)
            ->where('key', $key)
            ->first();

        if (!$metadata) {
            return null;
        }

        return $metadata->getValue();
    }

    public function getAllMetadata(int $fileId): Collection
    {
        return FileMetadata::where('file_id', $fileId)
            ->orderBy('key')
            ->get();
    }

    public function updateMetadata(int $fileId, string $key, mixed $value): bool
    {
        $metadata = FileMetadata::where('file_id', $fileId)
            ->where('key', $key)
            ->first();

        if (!$metadata) {
            return false;
        }

        $encodedValue = is_string($value) ? $value : json_encode($value);
        return $metadata->update(['value' => $encodedValue]);
    }

    public function deleteMetadata(int $fileId, string $key): bool
    {
        return FileMetadata::where('file_id', $fileId)
                ->where('key', $key)
                ->delete() > 0;
    }

    public function deleteAllMetadata(int $fileId): bool
    {
        return FileMetadata::where('file_id', $fileId)->delete() > 0;
    }

    public function hasMetadata(int $fileId, string $key): bool
    {
        return FileMetadata::where('file_id', $fileId)
            ->where('key', $key)
            ->exists();
    }

    public function setMultiple(int $fileId, array $metadata): Collection
    {
        return DB::transaction(function () use ($fileId, $metadata) {
            $results = collect();

            foreach ($metadata as $key => $value) {
                $result = $this->setMetadata($fileId, $key, $value);
                $results->push($result);
            }

            return $results;
        });
    }

    public function getMultiple(int $fileId, array $keys): Collection
    {
        return FileMetadata::where('file_id', $fileId)
            ->whereIn('key', $keys)
            ->get();
    }
}