<?php

namespace Hetbo\Shelf\Tests\Unit\Traits;

use Hetbo\Shelf\Models\File;
use Hetbo\Shelf\Models\Fileable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Hetbo\Shelf\Tests\TestCase;
use Hetbo\Shelf\Tests\Fixtures\TestModel;

class HasFilesTraitTest extends TestCase
{
    use RefreshDatabase;

    public function test_files_relationship(): void
    {
        $model = TestModel::factory()->create();
        $file = File::factory()->create();

        $model->attachFile($file, 'featured');

        $files = $model->files;

        $this->assertCount(1, $files);
        $this->assertEquals($file->id, $files->first()->id);
        $this->assertEquals('featured', $files->first()->pivot->role);
    }

    public function test_fileables_relationship(): void
    {
        $model = TestModel::factory()->create();
        $file = File::factory()->create();

        $model->attachFile($file, 'featured');

        $fileables = $model->fileables;

        $this->assertCount(1, $fileables);
        $this->assertEquals($file->id, $fileables->first()->file_id);
    }

    public function test_attach_file(): void
    {
        $model = TestModel::factory()->create();
        $file = File::factory()->create();
        $metadata = ['alt' => 'Test image'];

        $fileable = $model->attachFile($file, 'featured', $metadata);

        $this->assertInstanceOf(Fileable::class, $fileable);
        $this->assertEquals('featured', $fileable->role);
        $this->assertEquals($metadata, $fileable->metadata);
        $this->assertEquals(1, $fileable->order);
    }

    public function test_attach_file_sets_correct_order(): void
    {
        $model = TestModel::factory()->create();
        $file1 = File::factory()->create();
        $file2 = File::factory()->create();

        $fileable1 = $model->attachFile($file1, 'gallery');
        $fileable2 = $model->attachFile($file2, 'gallery');

        $this->assertEquals(1, $fileable1->order);
        $this->assertEquals(2, $fileable2->order);
    }

    public function test_attach_existing_file_updates_metadata(): void
    {
        $model = TestModel::factory()->create();
        $file = File::factory()->create();

        $model->attachFile($file, 'featured', ['alt' => 'Old']);
        $fileable = $model->attachFile($file, 'featured', ['alt' => 'New']);

        $this->assertEquals(['alt' => 'New'], $fileable->metadata);
        $this->assertEquals(1, Fileable::count()); // Should not create duplicate
    }

    public function test_detach_file(): void
    {
        $model = TestModel::factory()->create();
        $file = File::factory()->create();

        $model->attachFile($file, 'featured');
        $detached = $model->detachFile($file, 'featured');

        $this->assertTrue($detached);
        $this->assertEquals(0, $model->fileables()->count());
    }

    public function test_detach_nonexistent_file(): void
    {
        $model = TestModel::factory()->create();
        $file = File::factory()->create();

        $detached = $model->detachFile($file, 'featured');

        $this->assertFalse($detached);
    }

    public function test_detach_all_files(): void
    {
        $model = TestModel::factory()->create();
        $file1 = File::factory()->create();
        $file2 = File::factory()->create();

        $model->attachFile($file1, 'featured');
        $model->attachFile($file2, 'gallery');

        $detached = $model->detachAllFiles();

        $this->assertTrue($detached);
        $this->assertEquals(0, $model->fileables()->count());
    }

    public function test_detach_all_files_by_role(): void
    {
        $model = TestModel::factory()->create();
        $file1 = File::factory()->create();
        $file2 = File::factory()->create();

        $model->attachFile($file1, 'featured');
        $model->attachFile($file2, 'gallery');

        $detached = $model->detachAllFiles('featured');

        $this->assertTrue($detached);
        $this->assertEquals(1, $model->fileables()->count());
        $this->assertEquals('gallery', $model->fileables()->first()->role);
    }

    public function test_get_files_by_role(): void
    {
        $model = TestModel::factory()->create();
        $file1 = File::factory()->create();
        $file2 = File::factory()->create();
        $file3 = File::factory()->create();

        $model->attachFile($file1, 'featured');
        $model->attachFile($file2, 'gallery');
        $model->attachFile($file3, 'gallery');

        $featuredFiles = $model->getFilesByRole('featured');
        $galleryFiles = $model->getFilesByRole('gallery');

        $this->assertCount(1, $featuredFiles);
        $this->assertCount(2, $galleryFiles);
        $this->assertEquals($file1->id, $featuredFiles->first()->id);
    }

    public function test_has_file(): void
    {
        $model = TestModel::factory()->create();
        $file = File::factory()->create();

        $model->attachFile($file, 'featured');

        $hasFeatured = $model->hasFile($file, 'featured');
        $hasGallery = $model->hasFile($file, 'gallery');
        $hasAnyRole = $model->hasFile($file);

        $this->assertTrue($hasFeatured);
        $this->assertFalse($hasGallery);
        $this->assertTrue($hasAnyRole);
    }

    public function test_sync_files(): void
    {
        $model = TestModel::factory()->create();
        $file1 = File::factory()->create();
        $file2 = File::factory()->create();
        $file3 = File::factory()->create();

        // Initial attachments
        $model->attachFile($file1, 'gallery');
        $model->attachFile($file2, 'gallery');

        // Sync with new files
        $model->syncFiles([$file2->id, $file3->id], 'gallery');

        $galleryFiles = $model->getFilesByRole('gallery');
        $this->assertCount(2, $galleryFiles);
        $this->assertTrue($galleryFiles->pluck('id')->contains($file2->id));
        $this->assertTrue($galleryFiles->pluck('id')->contains($file3->id));
        $this->assertFalse($galleryFiles->pluck('id')->contains($file1->id));
    }

    public function test_reorder_files(): void
    {
        $model = TestModel::factory()->create();
        $file1 = File::factory()->create();
        $file2 = File::factory()->create();
        $file3 = File::factory()->create();

        $model->attachFile($file1, 'gallery');
        $model->attachFile($file2, 'gallery');
        $model->attachFile($file3, 'gallery');

        // Reorder: 3, 1, 2
        $model->reorderFiles('gallery', [$file3->id, $file1->id, $file2->id]);

        $orderedFiles = $model->getFilesByRole('gallery');
        $this->assertEquals($file3->id, $orderedFiles->get(0)->id);
        $this->assertEquals($file1->id, $orderedFiles->get(1)->id);
        $this->assertEquals($file2->id, $orderedFiles->get(2)->id);
    }

    public function test_get_first_file_by_role(): void
    {
        $model = TestModel::factory()->create();
        $file1 = File::factory()->create();
        $file2 = File::factory()->create();

        $model->attachFile($file1, 'gallery');
        $model->attachFile($file2, 'gallery');

        $firstFile = $model->getFirstFileByRole('gallery');
        $nonexistentRole = $model->getFirstFileByRole('nonexistent');

        $this->assertEquals($file1->id, $firstFile->id);
        $this->assertNull($nonexistentRole);
    }

    public function test_get_file_roles(): void
    {
        $model = TestModel::factory()->create();
        $file1 = File::factory()->create();
        $file2 = File::factory()->create();

        $model->attachFile($file1, 'featured');
        $model->attachFile($file2, 'gallery');

        $roles = $model->getFileRoles();

        $this->assertCount(2, $roles);
        $this->assertTrue($roles->contains('featured'));
        $this->assertTrue($roles->contains('gallery'));
    }

    public function test_get_files_with_metadata(): void
    {
        $model = TestModel::factory()->create();
        $file1 = File::factory()->create();
        $file2 = File::factory()->create();

        $model->attachFile($file1, 'gallery', ['alt' => 'Image 1']);
        $model->attachFile($file2, 'gallery', ['alt' => 'Image 2']);

        $filesWithMetadata = $model->getFilesWithMetadata('gallery');

        $this->assertCount(2, $filesWithMetadata);
        $this->assertEquals(['alt' => 'Image 1'], $filesWithMetadata->first()->metadata);
        $this->assertNotNull($filesWithMetadata->first()->file);
    }

    public function test_files_ordered_by_pivot_order(): void
    {
        $model = TestModel::factory()->create();
        $file1 = File::factory()->create();
        $file2 = File::factory()->create();
        $file3 = File::factory()->create();

        // Attach files in order
        $model->attachFile($file1, 'gallery');
        $model->attachFile($file2, 'gallery');
        $model->attachFile($file3, 'gallery');

        $files = $model->files()->wherePivot('role', 'gallery')->get();

        $this->assertEquals(1, $files->get(0)->pivot->order);
        $this->assertEquals(2, $files->get(1)->pivot->order);
        $this->assertEquals(3, $files->get(2)->pivot->order);
    }

    public function test_sync_files_preserves_order(): void
    {
        $model = TestModel::factory()->create();
        $file1 = File::factory()->create();
        $file2 = File::factory()->create();
        $file3 = File::factory()->create();

        $model->syncFiles([$file3->id, $file1->id, $file2->id], 'gallery');

        $fileables = $model->fileables()->where('role', 'gallery')->orderBy('order')->get();

        $this->assertEquals($file3->id, $fileables->get(0)->file_id);
        $this->assertEquals($file1->id, $fileables->get(1)->file_id);
        $this->assertEquals($file2->id, $fileables->get(2)->file_id);

        $this->assertEquals(1, $fileables->get(0)->order);
        $this->assertEquals(2, $fileables->get(1)->order);
        $this->assertEquals(3, $fileables->get(2)->order);
    }
}
