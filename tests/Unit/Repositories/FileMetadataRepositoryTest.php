<?php

namespace Hetbo\Shelf\Tests\Unit\Repositories;

use Hetbo\Shelf\Models\File;
use Hetbo\Shelf\Models\FileMetadata;
use Hetbo\Shelf\Repositories\FileMetadataRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Hetbo\Shelf\Tests\TestCase;

class FileMetadataRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private FileMetadataRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new FileMetadataRepository();
    }

    public function test_set_metadata(): void
    {
        $file = File::factory()->create();

        $metadata = $this->repository->setMetadata($file->id, 'alt_text', 'Test image');

        $this->assertInstanceOf(FileMetadata::class, $metadata);
        $this->assertEquals($file->id, $metadata->file_id);
        $this->assertEquals('alt_text', $metadata->key);
        $this->assertEquals('Test image', $metadata->value);
    }

    public function test_set_metadata_with_array_value(): void
    {
        $file = File::factory()->create();
        $arrayValue = ['width' => 1920, 'height' => 1080];

        $metadata = $this->repository->setMetadata($file->id, 'dimensions', $arrayValue);

        $this->assertEquals(json_encode($arrayValue), $metadata->value);
    }

    public function test_set_existing_metadata_updates(): void
    {
        $file = File::factory()->create();
        $this->repository->setMetadata($file->id, 'alt_text', 'Old text');

        $metadata = $this->repository->setMetadata($file->id, 'alt_text', 'New text');

        $this->assertEquals('New text', $metadata->value);
        $this->assertEquals(1, FileMetadata::count()); // Should not create duplicate
    }

    public function test_get_metadata(): void
    {
        $file = File::factory()->create();
        FileMetadata::create([
            'file_id' => $file->id,
            'key' => 'alt_text',
            'value' => 'Test image'
        ]);

        $value = $this->repository->getMetadata($file->id, 'alt_text');

        $this->assertEquals('Test image', $value);
    }

    public function test_get_metadata_with_json_value(): void
    {
        $file = File::factory()->create();
        $arrayValue = ['width' => 1920, 'height' => 1080];
        FileMetadata::create([
            'file_id' => $file->id,
            'key' => 'dimensions',
            'value' => json_encode($arrayValue)
        ]);

        $value = $this->repository->getMetadata($file->id, 'dimensions');

        $this->assertEquals($arrayValue, $value);
    }

    public function test_get_nonexistent_metadata_returns_null(): void
    {
        $file = File::factory()->create();

        $value = $this->repository->getMetadata($file->id, 'nonexistent');

        $this->assertNull($value);
    }

    public function test_get_all_metadata(): void
    {
        $file = File::factory()->create();
        FileMetadata::create(['file_id' => $file->id, 'key' => 'alt_text', 'value' => 'Test']);
        FileMetadata::create(['file_id' => $file->id, 'key' => 'caption', 'value' => 'Caption']);

        $metadata = $this->repository->getAllMetadata($file->id);

        $this->assertCount(2, $metadata);
        $this->assertEquals(['alt_text', 'caption'], $metadata->pluck('key')->sort()->values()->toArray());
    }

    public function test_update_metadata(): void
    {
        $file = File::factory()->create();
        FileMetadata::create([
            'file_id' => $file->id,
            'key' => 'alt_text',
            'value' => 'Old text'
        ]);

        $updated = $this->repository->updateMetadata($file->id, 'alt_text', 'New text');

        $this->assertTrue($updated);
        $this->assertEquals('New text', $this->repository->getMetadata($file->id, 'alt_text'));
    }

    public function test_update_nonexistent_metadata_returns_false(): void
    {
        $file = File::factory()->create();

        $updated = $this->repository->updateMetadata($file->id, 'nonexistent', 'value');

        $this->assertFalse($updated);
    }

    public function test_delete_metadata(): void
    {
        $file = File::factory()->create();
        FileMetadata::create([
            'file_id' => $file->id,
            'key' => 'alt_text',
            'value' => 'Test'
        ]);

        $deleted = $this->repository->deleteMetadata($file->id, 'alt_text');

        $this->assertTrue($deleted);
        $this->assertEquals(0, FileMetadata::count());
    }

    public function test_delete_nonexistent_metadata_returns_false(): void
    {
        $file = File::factory()->create();

        $deleted = $this->repository->deleteMetadata($file->id, 'nonexistent');

        $this->assertFalse($deleted);
    }

    public function test_delete_all_metadata(): void
    {
        $file = File::factory()->create();
        FileMetadata::create(['file_id' => $file->id, 'key' => 'alt_text', 'value' => 'Test']);
        FileMetadata::create(['file_id' => $file->id, 'key' => 'caption', 'value' => 'Caption']);

        $deleted = $this->repository->deleteAllMetadata($file->id);

        $this->assertTrue($deleted);
        $this->assertEquals(0, FileMetadata::count());
    }

    public function test_has_metadata(): void
    {
        $file = File::factory()->create();
        FileMetadata::create([
            'file_id' => $file->id,
            'key' => 'alt_text',
            'value' => 'Test'
        ]);

        $hasMetadata = $this->repository->hasMetadata($file->id, 'alt_text');
        $doesNotHave = $this->repository->hasMetadata($file->id, 'nonexistent');

        $this->assertTrue($hasMetadata);
        $this->assertFalse($doesNotHave);
    }

    public function test_set_multiple(): void
    {
        $file = File::factory()->create();
        $metadata = [
            'alt_text' => 'Test image',
            'caption' => 'Image caption',
            'dimensions' => ['width' => 1920, 'height' => 1080]
        ];

        $results = $this->repository->setMultiple($file->id, $metadata);

        $this->assertCount(3, $results);
        $this->assertEquals('Test image', $this->repository->getMetadata($file->id, 'alt_text'));
        $this->assertEquals('Image caption', $this->repository->getMetadata($file->id, 'caption'));
        $this->assertEquals(['width' => 1920, 'height' => 1080], $this->repository->getMetadata($file->id, 'dimensions'));
    }

    public function test_get_multiple(): void
    {
        $file = File::factory()->create();
        FileMetadata::create(['file_id' => $file->id, 'key' => 'alt_text', 'value' => 'Test']);
        FileMetadata::create(['file_id' => $file->id, 'key' => 'caption', 'value' => 'Caption']);
        FileMetadata::create(['file_id' => $file->id, 'key' => 'other', 'value' => 'Other']);

        $metadata = $this->repository->getMultiple($file->id, ['alt_text', 'caption']);

        $this->assertCount(2, $metadata);
        $this->assertEquals(['alt_text', 'caption'], $metadata->pluck('key')->sort()->values()->toArray());
    }

    public function test_metadata_ordering(): void
    {
        $file = File::factory()->create();
        FileMetadata::create(['file_id' => $file->id, 'key' => 'z_key', 'value' => 'Z']);
        FileMetadata::create(['file_id' => $file->id, 'key' => 'a_key', 'value' => 'A']);
        FileMetadata::create(['file_id' => $file->id, 'key' => 'm_key', 'value' => 'M']);

        $metadata = $this->repository->getAllMetadata($file->id);

        $keys = $metadata->pluck('key')->toArray();
        $this->assertEquals(['a_key', 'm_key', 'z_key'], $keys);
    }
}