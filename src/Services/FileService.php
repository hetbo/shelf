<?php

namespace Hetbo\Shelf\Services;

use Hetbo\Shelf\Contracts\FileRepositoryInterface;
use Hetbo\Shelf\DTOs\FileDTO;
use Hetbo\Shelf\Models\File;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    public function __construct(
        private FileRepositoryInterface $fileRepository
    ) {}

    public function create(array $data): FileDTO
    {
        $file = $this->fileRepository->create($data);
        return FileDTO::fromArray($file->toArray());
    }

    public function find(int $id): ?FileDTO
    {
        $file = $this->fileRepository->find($id);
        return $file ? FileDTO::fromArray($file->toArray()) : null;
    }

    public function findOrFail(int $id): FileDTO
    {
        $file = $this->fileRepository->findOrFail($id);
        return FileDTO::fromArray($file->toArray());
    }

    public function update(int $id, array $data): FileDTO
    {
        $file = $this->fileRepository->update($id, $data);
        return FileDTO::fromArray($file->toArray());
    }

    public function delete(int $id): bool
    {
        $file = $this->fileRepository->findOrFail($id);

        // Delete physical file
        if ($file->exists()) {
            Storage::disk($file->disk)->delete($file->path);
        }

        return $this->fileRepository->delete($id);
    }

    public function upload(UploadedFile $uploadedFile, ?int $folderId = null, ?string $disk = null): FileDTO
    {
        $disk = $disk ?? config('filesystems.default');
        $filename = $uploadedFile->getClientOriginalName();
        $hash = hash_file('sha256', $uploadedFile->getPathname());

        // Check for duplicate
        $existingFile = $this->fileRepository->findByHash($hash);
        if ($existingFile) {
            return FileDTO::fromArray($existingFile->toArray());
        }

        $path = $uploadedFile->store('files/' . date('Y/m'), $disk);

        $data = [
            'filename' => $filename,
            'path' => $path,
            'disk' => $disk,
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
            'hash' => $hash,
            'user_id' => auth()->id(),
            'folder_id' => $folderId,
        ];

        return $this->create($data);
    }

    public function duplicate(int $id): FileDTO
    {
        $originalFile = $this->fileRepository->findOrFail($id);

        $newPath = $this->generateUniqueFilename($originalFile->path, $originalFile->disk);
        Storage::disk($originalFile->disk)->copy($originalFile->path, $newPath);

        $data = $originalFile->toArray();
        unset($data['id']);
        $data['path'] = $newPath;
        $data['filename'] = $this->generateUniqueBasename($originalFile->filename);
        $data['hash'] = null; // Will be generated on save

        return $this->create($data);
    }

    public function move(int $id, ?int $folderId): FileDTO
    {
        return $this->update($id, ['folder_id' => $folderId]);
    }

    public function bulkDelete(array $fileIds): int
    {
        $deleted = 0;
        foreach ($fileIds as $fileId) {
            if ($this->delete($fileId)) {
                $deleted++;
            }
        }
        return $deleted;
    }

    public function bulkMove(array $fileIds, ?int $folderId): int
    {
        $moved = 0;
        foreach ($fileIds as $fileId) {
            $this->move($fileId, $folderId);
            $moved++;
        }
        return $moved;
    }

    public function getUserFiles(int $userId): Collection
    {
        return $this->fileRepository->getAllByUser($userId);
    }

    public function getFolderFiles(?int $folderId = null): Collection
    {
        return $this->fileRepository->getAllByFolder($folderId);
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->fileRepository->paginate($perPage, $filters);
    }

    public function search(string $query, array $filters = []): Collection
    {
        return $this->fileRepository->search($query, $filters);
    }

    public function getDownloadUrl(int $id): string
    {
        $file = $this->fileRepository->findOrFail($id);
        return Storage::disk($file->disk)->url($file->path);
    }

    public function getFileContents(int $id): string
    {
        $file = $this->fileRepository->findOrFail($id);
        return Storage::disk($file->disk)->get($file->path);
    }

    private function generateUniqueFilename(string $originalPath, string $disk): string
    {
        $pathInfo = pathinfo($originalPath);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        $extension = $pathInfo['extension'] ?? '';

        $counter = 1;
        do {
            $newFilename = $filename . '_copy_' . $counter;
            $newPath = $directory . '/' . $newFilename . ($extension ? '.' . $extension : '');
            $counter++;
        } while (Storage::disk($disk)->exists($newPath));

        return $newPath;
    }

    private function generateUniqueBasename(string $originalFilename): string
    {
        $pathInfo = pathinfo($originalFilename);
        $filename = $pathInfo['filename'];
        $extension = $pathInfo['extension'] ?? '';

        return $filename . '_copy_' . Str::random(4) . ($extension ? '.' . $extension : '');
    }
}