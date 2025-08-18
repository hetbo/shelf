<?php

namespace Hetbo\Shelf\Tests\Feature;

use Hetbo\Shelf\Contracts\FileRepositoryInterface;
use Hetbo\Shelf\Contracts\FileableRepositoryInterface;
use Hetbo\Shelf\Contracts\FileMetadataRepositoryInterface;
use Hetbo\Shelf\Models\File;
use Hetbo\Shelf\Models\FileMetadata;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Hetbo\Shelf\Tests\TestCase;
use Hetbo\Shelf\Tests\Fixtures\TestModel;

class FileServiceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private FileRepositoryInterface $fileRepository;
    private FileableRepositoryInterface $fileableRepository;
    private FileMetadataRepositoryInterface $metadataRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileRepository = app(FileRepositoryInterface::class);
        $this->fileableRepository = app(FileableRepositoryInterface::class);
        $this->metadataRepository = app(FileMetadataRepositoryInterface::class);

        Storage::fake('public');
    }

    public function test_complete_file_workflow(): void
    {
        // 1. Create a file
        $uploadedFile = UploadedFile::fake()->image('test.jpg', 800, 600);
        $path = $uploadedFile->store('uploads', 'public');

        $file = $this->fileRepository->create([
            'filename' => 'test.jpg',
            'path' => $path,
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'size' => $uploadedFile->getSize(),
            'hash' => hash_file('sha256', $uploadedFile->getRealPath()),
            'user_id' => 1,
        ]);

        $this->assertInstanceOf(File::class, $file);
        Storage::disk('public')->assertExists($path);

        // 2. Add metadata to the file
        $this->metadataRepository->setMetadata($file->id, 'alt_text', 'Test image');
        $this->metadataRepository->setMetadata($file->id, 'dimensions', [
            'width' => 800,
            'height' => 600,
        ]);

        $altText = $this->metadataRepository->getMetadata($file->id, 'alt_text');
        $dimensions = $this->metadataRepository->getMetadata($file->id, 'dimensions');

        $this->assertEquals('Test image', $altText);
        $this->assertEquals(['width' => 800, 'height' => 600], $dimensions);

        // 3. Attach file to a model
        $model = TestModel::factory()->create();
        $fileable = $this->fileableRepository->attach($model, $file, 'featured', [
            'caption' => 'Featured image for the model'
        ]);

        $this->assertEquals('featured', $fileable->role);
        $this->assertEquals(['caption' => 'Featured image for the model'], $fileable->metadata);

        // 4. Verify the attachment through the model
        $this->assertTrue($model->hasFile($file, 'featured'));
        $featuredFiles = $model->getFilesByRole('featured');
        $this->assertCount(1, $featuredFiles);

        // 5. Add multiple files to gallery
        $galleryFile1 = $this->fileRepository->create([
            'filename' => 'gallery1.jpg',
            'path' => 'uploads/gallery1.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
            'hash' => 'hash1',
            'user_id' => 1,
        ]);

        $galleryFile2 = $this->fileRepository->create([
            'filename' => 'gallery2.jpg',
            'path' => 'uploads/gallery2.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'size' => 2048,
            'hash' => 'hash2',
            'user_id' => 1,
        ]);

        $this->fileableRepository->attach($model, $galleryFile1, 'gallery');
        $this->fileableRepository->attach($model, $galleryFile2, 'gallery');

        $galleryFiles = $model->getFilesByRole('gallery');
        $this->assertCount(2, $galleryFiles);

        // 6. Test file search and filtering
        $searchResults = $this->fileRepository->search('gallery');
        $this->assertCount(2, $searchResults);

        $userFiles = $this->fileRepository->getAllByUser(1);
        $this->assertCount(3, $userFiles);

        $imageFiles = $this->fileRepository->findByMimeType('image');
        $this->assertCount(3, $imageFiles);

        // 7. Test metadata operations
        $allMetadata = $this->metadataRepository->getAllMetadata($file->id);
        $this->assertCount(2, $allMetadata);

        $multipleMetadata = $this->metadataRepository->setMultiple($file->id, [
            'photographer' => 'John Doe',
            'location' => 'New York',
        ]);
        $this->assertCount(2, $multipleMetadata);

        // 8. Test file detachment and cleanup
        $detached = $this->fileableRepository->detach($model, $galleryFile1, 'gallery');
        $this->assertTrue($detached);

        $remainingGallery = $model->getFilesByRole('gallery');
        $this->assertCount(1, $remainingGallery);

        // 9. Test file deletion
        $deleted = $this->fileRepository->delete($galleryFile1->id);
        $this->assertTrue($deleted);

        $deletedFile = $this->fileRepository->find($galleryFile1->id);
        $this->assertNull($deletedFile); // Should be soft deleted
    }

    public function test_file_duplication_detection(): void
    {
        $hash = 'unique_hash_123';

        // Create first file
        $file1 = $this->fileRepository->create([
            'filename' => 'original.jpg',
            'path' => 'uploads/original.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
            'hash' => $hash,
            'user_id' => 1,
        ]);

        // Try to find duplicate
        $duplicate = $this->fileRepository->findByHash($hash);

        $this->assertNotNull($duplicate);
        $this->assertEquals($file1->id, $duplicate->id);

        // Verify we can detect duplicates before creating
        $existingFile = $this->fileRepository->findByHash($hash);
        $this->assertInstanceOf(File::class, $existingFile);
    }

    public function test_complex_file_relationships(): void
    {
        $model1 = TestModel::factory()->create();
        $model2 = TestModel::factory()->create();
        $file = File::factory()->create();

        // Attach same file to multiple models with different roles
        $this->fileableRepository->attach($model1, $file, 'featured');
        $this->fileableRepository->attach($model2, $file, 'attachment');

        // Verify attachments
        $fileAttachments = $this->fileableRepository->getFileAttachments($file->id);
        $this->assertCount(2, $fileAttachments);

        $model1Attachments = $this->fileableRepository->getAttachments(
            get_class($model1),
            $model1->id
        );
        $this->assertCount(1, $model1Attachments);

        // Test sync operation
        $newFile1 = File::factory()->create();
        $newFile2 = File::factory()->create();

        $this->fileableRepository->syncFiles($model1, [$newFile1->id, $newFile2->id], 'gallery');

        $galleryFiles = $this->fileableRepository->getByRole($model1, 'gallery');
        $this->assertCount(2, $galleryFiles);
    }

    public function test_file_ordering_and_reordering(): void
    {
        $model = TestModel::factory()->create();
        $files = File::factory()->count(3)->create();

        // Attach files in order
        foreach ($files as $file) {
            $this->fileableRepository->attach($model, $file, 'gallery');
        }

        $attachments = $this->fileableRepository->getByRole($model, 'gallery');

        // Initial order checks
        $this->assertEquals([1, 2, 3], $attachments->pluck('order')->toArray());
        $this->assertEquals($files->pluck('id')->toArray(), $attachments->pluck('file_id')->toArray());

        // Reorder attachments (reverse attachment IDs)
        $attachmentIds = $attachments->pluck('id')->reverse()->toArray();
        $reordered = $this->fileableRepository->updateOrder($attachmentIds);

        $this->assertTrue($reordered);

        // Verify new order
        $reorderedAttachments = $this->fileableRepository->getByRole($model, 'gallery');

        // Assert attachment IDs are reversed
        $this->assertEquals(
            $attachments->pluck('id')->reverse()->values()->toArray(),
            $reorderedAttachments->pluck('id')->toArray()
        );

        // Assert file IDs still match the corresponding attachments
        $this->assertEquals(
            $files->pluck('id')->reverse()->values()->toArray(),
            $reorderedAttachments->pluck('file_id')->toArray()
        );
    }
}