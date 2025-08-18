<?php

namespace Hetbo\Shelf\Tests\Unit\Repositories;

use Hetbo\Shelf\Models\File;
use Hetbo\Shelf\Models\Folder;
use Hetbo\Shelf\Repositories\FileRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Hetbo\Shelf\Tests\TestCase;

class FileRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private FileRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new FileRepository();
    }

    public function test_create_file(): void
    {
        $data = [
            'filename' => 'test.jpg',
            'path' => 'uploads/test.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
            'hash' => 'abc123',
            'user_id' => 1,
        ];

        $file = $this->repository->create($data);

        $this->assertInstanceOf(File::class, $file);
        $this->assertEquals('test.jpg', $file->filename);
        $this->assertEquals('uploads/test.jpg', $file->path);
        $this->assertDatabaseHas('shelf_files', $data);
    }

    public function test_find_file(): void
    {
        $file = File::factory()->create();

        $found = $this->repository->find($file->id);

        $this->assertInstanceOf(File::class, $found);
        $this->assertEquals($file->id, $found->id);
    }

    public function test_find_nonexistent_file_returns_null(): void
    {
        $found = $this->repository->find(999);

        $this->assertNull($found);
    }

    public function test_find_or_fail_file(): void
    {
        $file = File::factory()->create();

        $found = $this->repository->findOrFail($file->id);

        $this->assertInstanceOf(File::class, $found);
        $this->assertEquals($file->id, $found->id);
    }

    public function test_find_or_fail_nonexistent_file_throws_exception(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->findOrFail(999);
    }

    public function test_update_file(): void
    {
        $file = File::factory()->create(['filename' => 'old.jpg']);

        $updated = $this->repository->update($file->id, ['filename' => 'new.jpg']);

        $this->assertEquals('new.jpg', $updated->filename);
        $this->assertDatabaseHas('shelf_files', [
            'id' => $file->id,
            'filename' => 'new.jpg',
        ]);
    }

    public function test_delete_file(): void
    {
        $file = File::factory()->create();

        $deleted = $this->repository->delete($file->id);

        $this->assertTrue($deleted);
        $this->assertSoftDeleted('shelf_files', ['id' => $file->id]);
    }

    public function test_find_by_hash(): void
    {
        $file = File::factory()->create(['hash' => 'unique_hash']);

        $found = $this->repository->findByHash('unique_hash');

        $this->assertInstanceOf(File::class, $found);
        $this->assertEquals($file->id, $found->id);
    }

    public function test_get_all_by_user(): void
    {
        $userId = 1;
        File::factory()->count(3)->create(['user_id' => $userId]);
        File::factory()->count(2)->create(['user_id' => 2]);

        $files = $this->repository->getAllByUser($userId);

        $this->assertCount(3, $files);
        $files->each(function ($file) use ($userId) {
            $this->assertEquals($userId, $file->user_id);
        });
    }

    public function test_get_all_by_folder(): void
    {
        $folder = Folder::factory()->create();
        File::factory()->count(2)->create(['folder_id' => $folder->id]);
        File::factory()->count(3)->create(['folder_id' => null]);

        $filesInFolder = $this->repository->getAllByFolder($folder->id);
        $filesInRoot = $this->repository->getAllByFolder(null);

        $this->assertCount(2, $filesInFolder);
        $this->assertCount(3, $filesInRoot);
    }

    public function test_paginate(): void
    {
        File::factory()->count(25)->create();

        $paginated = $this->repository->paginate(10);

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginated);
        $this->assertEquals(10, $paginated->perPage());
        $this->assertEquals(25, $paginated->total());
        $this->assertCount(10, $paginated->items());
    }

    public function test_paginate_with_filters(): void
    {
        $userId = 1;
        File::factory()->count(5)->create(['user_id' => $userId, 'mime_type' => 'image/jpeg']);
        File::factory()->count(3)->create(['user_id' => $userId, 'mime_type' => 'application/pdf']);
        File::factory()->count(2)->create(['user_id' => 2]);

        $paginated = $this->repository->paginate(10, [
            'user_id' => $userId,
            'mime_type' => 'image',
        ]);

        $this->assertEquals(5, $paginated->total());
    }

    public function test_find_by_mime_type(): void
    {
        File::factory()->count(3)->create(['mime_type' => 'image/jpeg']);
        File::factory()->count(2)->create(['mime_type' => 'image/png']);
        File::factory()->count(1)->create(['mime_type' => 'application/pdf']);

        $images = $this->repository->findByMimeType('image');

        $this->assertCount(5, $images);
    }

    public function test_search(): void
    {
        File::factory()->create(['filename' => 'document.pdf']);
        File::factory()->create(['filename' => 'photo.jpg']);
        File::factory()->create(['filename' => 'another_document.docx']);

        $results = $this->repository->search('document');

        $this->assertCount(2, $results);
        $results->each(function ($file) {
            $this->assertStringContainsString('document', strtolower($file->filename));
        });
    }

    public function test_search_with_filters(): void
    {
        $userId = 1;
        File::factory()->create([
            'filename' => 'test.jpg',
            'user_id' => $userId,
            'mime_type' => 'image/jpeg'
        ]);
        File::factory()->create([
            'filename' => 'test.pdf',
            'user_id' => $userId,
            'mime_type' => 'application/pdf'
        ]);
        File::factory()->create([
            'filename' => 'test.png',
            'user_id' => 2,
            'mime_type' => 'image/png'
        ]);

        $results = $this->repository->search('test', [
            'user_id' => $userId,
            'mime_type' => 'image'
        ]);

        $this->assertCount(1, $results);
        $this->assertEquals('test.jpg', $results->first()->filename);
    }
}