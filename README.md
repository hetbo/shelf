# shelf https://img.shields.io/badge/status-in__development-FFA500?style=for-the-badge&logo=git&logoColor=white
Streamlined Handling Engine for Laravel Files
---

# Laravel Shelf - File Management Package

A comprehensive Laravel package for managing files and their relationships with polymorphic attachments, metadata, and flexible organization.

## Features

- **Polymorphic File Attachments**: Attach files to any model with customizable roles
- **File Metadata**: Store and retrieve metadata for files
- **Folder Organization**: Organize files in hierarchical folder structures
- **File Deduplication**: Automatic detection of duplicate files using hashes
- **Flexible Ordering**: Order file attachments within roles
- **Soft Deletion**: Safe file deletion with recovery options
- **Repository Pattern**: Clean, testable architecture with interfaces
- **Comprehensive Testing**: Full test coverage for all components

## Installation

```bash
composer require hetbo/shelf
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="shelf-migrations"
php artisan migrate
```

Optionally, publish the configuration file:

```bash
php artisan vendor:publish --tag="shelf-config"
```

## Basic Usage

### 1. Add the HasFiles Trait

Add the `HasFiles` trait to any model that should support file attachments:

```php
<?php

namespace App\Models;

use Hetbo\Shelf\Traits\HasFiles;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFiles;
    
    // ... your model code
}
```

### 2. File Upload and Storage

```php
use Hetbo\Shelf\Contracts\FileRepositoryInterface;

class FileUploadController extends Controller
{
    public function store(Request $request, FileRepositoryInterface $fileRepository)
    {
        $uploadedFile = $request->file('upload');
        $path = $uploadedFile->store('uploads', 'public');
        
        // Check for duplicates
        $hash = hash_file('sha256', $uploadedFile->getRealPath());
        $existingFile = $fileRepository->findByHash($hash);
        
        if ($existingFile) {
            // File already exists, use the existing one
            $file = $existingFile;
        } else {
            // Create new file record
            $file = $fileRepository->create([
                'filename' => $uploadedFile->getClientOriginalName(),
                'path' => $path,
                'disk' => 'public',
                'mime_type' => $uploadedFile->getMimeType(),
                'size' => $uploadedFile->getSize(),
                'hash' => $hash,
                'user_id' => auth()->id(),
            ]);
        }
        
        return response()->json(['file' => $file]);
    }
}
```

### 3. Attaching Files to Models

```php
$post = Post::find(1);
$file = File::find(1);

// Attach as featured image
$post->attachFile($file, 'featured', ['alt' => 'Featured image']);

// Attach to gallery
$post->attachFile($file, 'gallery', ['caption' => 'Gallery image']);

// Check if file is attached
if ($post->hasFile($file, 'featured')) {
    // File is attached with featured role
}
```

### 4. Retrieving Files

```php
$post = Post::find(1);

// Get files by role
$featuredImages = $post->getFilesByRole('featured');
$galleryImages = $post->getFilesByRole('gallery');

// Get first file by role
$featuredImage = $post->getFirstFileByRole('featured');

// Get all files with metadata
$filesWithMetadata = $post->getFilesWithMetadata('gallery');

// Get all file roles for this model
$roles = $post->getFileRoles();
```

### 5. File Metadata

```php
use Hetbo\Shelf\Contracts\FileMetadataRepositoryInterface;

$metadataRepository = app(FileMetadataRepositoryInterface::class);

// Set metadata
$metadataRepository->setMetadata($file->id, 'alt_text', 'Beautiful landscape');
$metadataRepository->setMetadata($file->id, 'dimensions', ['width' => 1920, 'height' => 1080]);

// Get metadata
$altText = $metadataRepository->getMetadata($file->id, 'alt_text');
$dimensions = $metadataRepository->getMetadata($file->id, 'dimensions');

// Set multiple metadata at once
$metadataRepository->setMultiple($file->id, [
    'photographer' => 'John Doe',
    'location' => 'Paris',
    'camera' => 'Canon EOS R5'
]);

// Get all metadata
$allMetadata = $metadataRepository->getAllMetadata($file->id);
```

## Advanced Usage

### File Organization with Folders

```php
use Hetbo\Shelf\Models\Folder;

// Create folder
$folder = Folder::create([
    'name' => 'Documents',
    'user_id' => auth()->id(),
]);

// Create subfolder
$subfolder = Folder::create([
    'name' => 'Contracts',
    'parent_id' => $folder->id,
    'user_id' => auth()->id(),
]);

// Create file in folder
$file = $fileRepository->create([
    'filename' => 'contract.pdf',
    'path' => 'uploads/contract.pdf',
    'folder_id' => $subfolder->id,
    // ... other attributes
]);

// Get files in folder
$filesInFolder = $fileRepository->getAllByFolder($folder->id);
```

### Bulk Operations

```php
$post = Post::find(1);

// Sync files (replaces existing files in role)
$post->syncFiles([1, 2, 3], 'gallery');

// Reorder files
$post->reorderFiles('gallery', [3, 1, 2]); // New order by file IDs

// Detach all files in role
$post->detachAllFiles('gallery');

// Detach all files
$post->detachAllFiles();
```

### Repository Usage

```php
use Hetbo\Shelf\Contracts\FileRepositoryInterface;
use Hetbo\Shelf\Contracts\FileableRepositoryInterface;

class PostService
{
    public function __construct(
        private FileRepositoryInterface $fileRepository,
        private FileableRepositoryInterface $fileableRepository
    ) {}
    
    public function attachFeaturedImage(Post $post, int $fileId): void
    {
        $file = $this->fileRepository->findOrFail($fileId);
        $this->fileableRepository->attach($post, $file, 'featured');
    }
    
    public function searchFiles(string $query, int $userId): Collection
    {
        return $this->fileRepository->search($query, ['user_id' => $userId]);
    }
}
```

### Pagination and Filtering

```php
// Paginate files with filters
$files = $fileRepository->paginate(15, [
    'user_id' => auth()->id(),
    'mime_type' => 'image',
    'search' => 'vacation'
]);

// Get files by mime type
$images = $fileRepository->findByMimeType('image');
$videos = $fileRepository->findByMimeType('video');
```

## Configuration

The configuration file allows you to customize various aspects of the package:

```php
// config/shelf.php
return [
    'default_disk' => 'public',
    
    'upload' => [
        'max_file_size' => 10485760, // 10MB
        'allowed_mimes' => [
            'image/jpeg',
            'image/png',
            // ... more types
        ],
    ],
    
    'roles' => [
        'featured' => 'Featured Image',
        'gallery' => 'Gallery Images',
        'attachment' => 'Attachment',
        // ... custom roles
    ],
    
    'security' => [
        'sanitize_filenames' => true,
        'check_file_extensions' => true,
    ],
];
```

## File Model Methods

The `File` model includes several helpful methods:

```php
$file = File::find(1);

// Get file URL
$url = $file->getUrl();

// Check if file exists on disk
if ($file->exists()) {
    $contents = $file->getContents();
}

// File type checks
if ($file->isImage()) {
    // Handle image
}

if ($file->isPdf()) {
    // Handle PDF
}

// Get human readable size
$size = $file->getHumanReadableSize(); // "2.5 MB"
```

## Testing

Run the package tests:

```bash
composer test
```

The package includes comprehensive tests covering:

- Repository functionality
- Model relationships
- File attachment operations
- Metadata management
- Integration scenarios

## API Reference

### FileRepositoryInterface

- `create(array $data): File`
- `find(int $id): ?File`
- `findOrFail(int $id): File`
- `update(int $id, array $data): File`
- `delete(int $id): bool`
- `findByHash(string $hash): ?File`
- `getAllByUser(int $userId): Collection`
- `getAllByFolder(?int $folderId): Collection`
- `paginate(int $perPage, array $filters): LengthAwarePaginator`
- `search(string $query, array $filters): Collection`

### FileableRepositoryInterface

- `attach(Model $model, File $file, string $role, array $metadata = []): Fileable`
- `detach(Model $model, File $file, string $role): bool`
- `detachAll(Model $model, ?string $role = null): bool`
- `getAttachments(string $fileableType, int $fileableId, ?string $role = null): Collection`
- `hasAttachment(Model $model, File $file, ?string $role = null): bool`
- `syncFiles(Model $model, array $fileIds, string $role): void`

### FileMetadataRepositoryInterface

- `setMetadata(int $fileId, string $key, mixed $value): FileMetadata`
- `getMetadata(int $fileId, string $key): mixed`
- `getAllMetadata(int $fileId): Collection`
- `updateMetadata(int $fileId, string $key, mixed $value): bool`
- `deleteMetadata(int $fileId, string $key): bool`
- `setMultiple(int $fileId, array $metadata): Collection`

### HasFiles Trait Methods

- `attachFile(File $file, string $role, array $metadata = []): Fileable`
- `detachFile(File $file, string $role): bool`
- `getFilesByRole(string $role): Collection`
- `hasFile(File $file, ?string $role = null): bool`
- `syncFiles(array $fileIds, string $role): void`
- `reorderFiles(string $role, array $fileIds): void`
- `getFirstFileByRole(string $role): ?File`

## License

MIT License