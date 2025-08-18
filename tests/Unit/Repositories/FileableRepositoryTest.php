<?php

namespace Hetbo\Shelf\Tests\Unit\Repositories;

use Hetbo\Shelf\Models\File;
use Hetbo\Shelf\Models\Fileable;
use Hetbo\Shelf\Repositories\FileableRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Hetbo\Shelf\Tests\TestCase;
use Hetbo\Shelf\Tests\Fixtures\TestModel;

class FileableRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private FileableRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new FileableRepository();
    }

    public function test_attach_file(): void
    {
        $model = TestModel::factory()->create();
        $file = File::factory()->create();
        $metadata = ['alt' => 'Test image'];

        $fileable = $this->repository->attach($model, $file, 'featured', $metadata);

        $this->assertInstanceOf(Fileable::class, $fileable);
        $this->assertEquals($file->id, $fileable->file_id);
        $this->assertEquals(get_class($model), $fileable->fileable_type);
        $this->assertEquals($model->id, $fileable->fileable_id);
        $this->assertEquals('featured', $fileable->role);
        $this->assertEquals($metadata, $fileable->metadata);
        $this->assertEquals(1, $fileable->order);
    }

    public function test_attach_existing_file_updates_metadata(): void
    {
        $model = TestModel::factory()->create();
        $file = File::factory()->create();

        // First attachment
        $this->repository->attach($model, $file, 'featured', ['alt' => 'Old alt text']);

        // Second attachment with new metadata
        $fileable = $this->repository->attach($model, $file, 'featured', ['alt' => 'New alt text']);

        $this->assertEquals(['alt' => 'New alt text'], $fileable->metadata);
        $this->assertEquals(1, Fileable::count()); // Should not create duplicate
    }

    public function test_detach_file(): void
    {
        $model = TestModel::factory()->create();
        $file = File::factory()->create();
        $this->repository->attach($model, $file, 'featured');

        $detached = $this->repository->detach($model, $file, 'featured');

        $this->assertTrue($detached);
        $this->assertEquals(0, Fileable::count());
    }

    public function test_detach_nonexistent_file_returns_false(): void
    {
        $model = TestModel::factory()->create();
        $file = File::factory()->create();

        $detached = $this->repository->detach($model, $file, 'featured');

        $this->assertFalse($detached);
    }

    public function test_detach_all_files(): void
    {
        $model = TestModel::factory()->create();
        $file1 = File::factory()->create();
        $file2 = File::factory()->create();

        $this->repository->attach($model, $file1, 'featured');
        $this->repository->attach($model, $file2, 'gallery');

        $detached = $this->repository->detachAll($model);

        $this->assertTrue($detached);
        $this->assertEquals(0, Fileable::count());
    }

    public function test_detach_all_files_by_role(): void
    {
        $model = TestModel::factory()->create();
        $file1 = File::factory()->create();
        $file2 = File::factory()->create();

        $this->repository->attach($model, $file1, 'featured');
        $this->repository->attach($model, $file2, 'gallery');

        $detached = $this->repository->detachAll($model, 'featured');

        $this->assertTrue($detached);
        $this->assertEquals(1, Fileable::count()); // Gallery attachment should remain
    }

    public function test_get_attachments(): void
    {
        $model = TestModel::factory()->create();
        $file1 = File::factory()->create();
        $file2 = File::factory()->create();

        $this->repository->attach($model, $file1, 'featured');
        $this->repository->attach($model, $file2, 'gallery');

        $attachments = $this->repository->getAttachments(get_class($model), $model->id);

        $this->assertCount(2, $attachments);
    }

    public function test_get_attachments_by_role(): void
    {
        $model = TestModel::factory()->create();
        $file1 = File::factory()->create();
        $file2 = File::factory()->create();

        $this->repository->attach($model, $file1, 'featured');
        $this->repository->attach($model, $file2, 'gallery');

        $attachments = $this->repository->getAttachments(get_class($model), $model->id, 'featured');

        $this->assertCount(1, $attachments);
        $this->assertEquals('featured', $attachments->first()->role);
    }

    public function test_get_file_attachments(): void
    {
        $file = File::factory()->create();
        $model1 = TestModel::factory()->create();
        $model2 = TestModel::factory()->create();

        $this->repository->attach($model1, $file, 'featured');
        $this->repository->attach($model2, $file, 'gallery');

        $attachments = $this->repository->getFileAttachments($file->id);

        $this->assertCount(2, $attachments);
    }

    public function test_update_attachment(): void
    {
        $model = TestModel::factory()->create();
        $file = File::factory()->create();
        $fileable = $this->repository->attach($model, $file, 'featured');

        $updated = $this->repository->updateAttachment($fileable->id, [
            'metadata' => ['alt' => 'Updated alt text']
        ]);

        $this->assertTrue($updated);
        $fileable->refresh();
        $this->assertEquals(['alt' => 'Updated alt text'], $fileable->metadata);
    }

    public function test_update_order(): void
    {
        $model = TestModel::factory()->create();
        $file1 = File::factory()->create();
        $file2 = File::factory()->create();
        $file3 = File::factory()->create();

        $attachment1 = $this->repository->attach($model, $file1, 'gallery');
        $attachment2 = $this->repository->attach($model, $file2, 'gallery');
        $attachment3 = $this->repository->attach($model, $file3, 'gallery');

        // Reorder: 3, 1, 2
        $updated = $this->repository->updateOrder([
            $attachment3->id,
            $attachment1->id,
            $attachment2->id,
        ]);

        $this->assertTrue($updated);

        $attachment1->refresh();
        $attachment2->refresh();
        $attachment3->refresh();

        $this->assertEquals(2, $attachment1->order);
        $this->assertEquals(3, $attachment2->order);
        $this->assertEquals(1, $attachment3->order);
    }

    public function test_has_attachment(): void
    {
        $model = TestModel::factory()->create();
        $file = File::factory()->create();
        $this->repository->attach($model, $file, 'featured');

        $hasAttachment = $this->repository->hasAttachment($model, $file, 'featured');
        $hasWrongRole = $this->repository->hasAttachment($model, $file, 'gallery');

        $this->assertTrue($hasAttachment);
        $this->assertFalse($hasWrongRole);
    }

    public function test_get_by_role(): void
    {
        $model = TestModel::factory()->create();
        $file1 = File::factory()->create();
        $file2 = File::factory()->create();

        $this->repository->attach($model, $file1, 'featured');
        $this->repository->attach($model, $file2, 'gallery');

        $featured = $this->repository->getByRole($model, 'featured');

        $this->assertCount(1, $featured);
        $this->assertEquals('featured', $featured->first()->role);
    }

    public function test_sync_files(): void
    {
        $model = TestModel::factory()->create();
        $file1 = File::factory()->create();
        $file2 = File::factory()->create();
        $file3 = File::factory()->create();

        // Initial attachments
        $this->repository->attach($model, $file1, 'gallery');
        $this->repository->attach($model, $file2, 'gallery');

        // Sync with new files
        $this->repository->syncFiles($model, [$file2->id, $file3->id], 'gallery');

        $attachments = $this->repository->getByRole($model, 'gallery');
        $this->assertCount(2, $attachments);
        $this->assertTrue($attachments->pluck('file_id')->contains($file2->id));
        $this->assertTrue($attachments->pluck('file_id')->contains($file3->id));
        $this->assertFalse($attachments->pluck('file_id')->contains($file1->id));
    }

    public function test_attachment_ordering(): void
    {
        $model = TestModel::factory()->create();
        $file1 = File::factory()->create();
        $file2 = File::factory()->create();
        $file3 = File::factory()->create();

        $attachment1 = $this->repository->attach($model, $file1, 'gallery');
        $attachment2 = $this->repository->attach($model, $file2, 'gallery');
        $attachment3 = $this->repository->attach($model, $file3, 'gallery');

        $this->assertEquals(1, $attachment1->order);
        $this->assertEquals(2, $attachment2->order);
        $this->assertEquals(3, $attachment3->order);

        $attachments = $this->repository->getByRole($model, 'gallery');
        $this->assertEquals($file1->id, $attachments->first()->file_id);
        $this->assertEquals($file3->id, $attachments->last()->file_id);
    }
}