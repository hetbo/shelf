<?php

use Hetbo\Shelf\Models\ShelfFile;
use Hetbo\Shelf\Repositories\FileRepository;
use Hetbo\Shelf\Services\FileService;
use Hetbo\Shelf\Tests\Stubs\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->fileService = new FileService(new FileRepository());
    $this->user = User::factory()->create();
});

it('can upload a file', function () {
    $file = UploadedFile::fake()->image('test.jpg', 100, 100);

    $uploadedFile = $this->fileService->uploadFile($file, $this->user->id);

    expect($uploadedFile)->toBeInstanceOf(ShelfFile::class)
        ->and($uploadedFile->filename)->toBe('test.jpg')
        ->and($uploadedFile->mime_type)->toBe('image/jpeg')
        ->and($uploadedFile->user_id)->toBe($this->user->id);

    Storage::disk('public')->assertExists($uploadedFile->path);
});

it('prevents duplicate uploads', function () {
    $file = UploadedFile::fake()->image('test.jpg', 100, 100);

    $firstUpload = $this->fileService->uploadFile($file, $this->user->id);
    $secondUpload = $this->fileService->uploadFile($file, $this->user->id);

    expect($firstUpload->id)->toBe($secondUpload->id)
        ->and(ShelfFile::count())->toBe(1);
});

it('can get paginated files', function () {
    ShelfFile::factory()->count(20)->create();

    $files = $this->fileService->getFiles([], 10);

    expect($files->items())->toHaveCount(10)
        ->and($files->total())->toBe(20);
});

it('can filter files by mime type', function () {
    ShelfFile::factory()->create(['mime_type' => 'image/jpeg']);
    ShelfFile::factory()->create(['mime_type' => 'Hetbo\Shelflication/pdf']);

    $files = $this->fileService->getFiles(['mime_type' => 'image']);

    expect($files->items())->toHaveCount(1)
        ->and($files->items()[0]->mime_type)->toBe('image/jpeg');
});

it('can filter files by search term', function () {
    ShelfFile::factory()->create(['filename' => 'my-document.pdf']);
    ShelfFile::factory()->create(['filename' => 'photo.jpg']);

    $files = $this->fileService->getFiles(['search' => 'document']);

    expect($files->items())->toHaveCount(1)
        ->and($files->items()[0]->filename)->toBe('my-document.pdf');
});

it('can filter files by user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    ShelfFile::factory()->create(['user_id' => $user1->id]);
    ShelfFile::factory()->create(['user_id' => $user2->id]);

    $files = $this->fileService->getFiles(['user_id' => $user1->id]);

    expect($files->items())->toHaveCount(1)
        ->and($files->items()[0]->user_id)->toBe($user1->id);
});

it('can delete a file', function () {
    $file = UploadedFile::fake()->image('test.jpg');
    $uploadedFile = $this->fileService->uploadFile($file, $this->user->id);

    $result = $this->fileService->deleteFile($uploadedFile->id);

    expect($result)->toBeTrue();
    $this->assertSoftDeleted('shelf_files', ['id' => $uploadedFile->id]);
    Storage::disk('public')->assertMissing($uploadedFile->path);
});

it('returns false when deleting non-existent file', function () {
    $result = $this->fileService->deleteFile(999);

    expect($result)->toBeFalse();
});

it('can attach file to model', function () {
    $file = ShelfFile::factory()->create();

    $this->fileService->attachFileToModel(
        $file->id,
        $this->user,
        'avatar',
        1,
        ['alt' => 'User avatar']
    );

    $this->assertDatabaseHas('shelf_fileables', [
        'file_id' => $file->id,
        'fileable_type' => User::class,
        'fileable_id' => $this->user->id,
        'role' => 'avatar',
        'order' => 1,
    ]);
});

it('can attach file to model without role', function () {
    $file = ShelfFile::factory()->create();

    $this->fileService->attachFileToModel($file->id, $this->user);

    $this->assertDatabaseHas('shelf_fileables', [
        'file_id' => $file->id,
        'fileable_type' => User::class,
        'fileable_id' => $this->user->id,
        'role' => null,
    ]);
});

it('can detach file from model', function () {
    $file = ShelfFile::factory()->create();

    $this->fileService->attachFileToModel($file->id, $this->user, 'avatar');
    $this->fileService->detachFileFromModel($file->id, $this->user, 'avatar');

    $this->assertDatabaseMissing('shelf_fileables', [
        'file_id' => $file->id,
        'fileable_type' => User::class,
        'fileable_id' => $this->user->id,
        'role' => 'avatar',
    ]);
});

it('can detach all file attachments from model when no role specified', function () {
    $file = ShelfFile::factory()->create();

    $this->fileService->attachFileToModel($file->id, $this->user, 'avatar');
    $this->fileService->attachFileToModel($file->id, $this->user, 'banner');
    $this->fileService->detachFileFromModel($file->id, $this->user);

    $this->assertDatabaseMissing('shelf_fileables', [
        'file_id' => $file->id,
        'fileable_type' => User::class,
        'fileable_id' => $this->user->id,
    ]);
});

it('can replace a file', function () {
    $oldFile = UploadedFile::fake()->image('old.jpg');
    $newFile = UploadedFile::fake()->image('new.jpg');

    $uploadedOldFile = $this->fileService->uploadFile($oldFile, $this->user->id);
    $this->fileService->attachFileToModel($uploadedOldFile->id, $this->user, 'avatar');

    $replacedFile = $this->fileService->replaceFile($uploadedOldFile->id, $newFile, $this->user->id);

    expect($replacedFile)->not->toBeNull();
    expect($replacedFile->filename)->toBe('new.jpg');
    $this->assertSoftDeleted('shelf_files', ['id' => $uploadedOldFile->id]);

    // Check that attachment was transferred
    $this->assertDatabaseHas('shelf_fileables', [
        'file_id' => $replacedFile->id,
        'fileable_type' => User::class,
        'fileable_id' => $this->user->id,
        'role' => 'avatar',
    ]);
});

it('returns null when replacing non-existent file', function () {
    $newFile = UploadedFile::fake()->image('new.jpg');

    $result = $this->fileService->replaceFile(999, $newFile, $this->user->id);

    expect($result)->toBeNull();
});

it('can update file details', function () {
    $file = ShelfFile::factory()->create(['filename' => 'old-name.jpg']);

    $result = $this->fileService->updateFileDetails($file->id, [
        'filename' => 'new-name.jpg',
        'mime_type' => 'should-not-update', // This should be ignored
    ]);

    expect($result)->toBeTrue();

    $file->refresh();
    expect($file->filename)->toBe('new-name.jpg')
        ->and($file->mime_type)->not->toBe('should-not-update');
});

it('returns false when updating non-existent file', function () {
    $result = $this->fileService->updateFileDetails(999, ['filename' => 'new-name.jpg']);

    expect($result)->toBeFalse();
});

it('can get a single file by id', function () {
    $file = ShelfFile::factory()->create();

    $found = $this->fileService->getFile($file->id);

    expect($found)->toBeInstanceOf(ShelfFile::class)
        ->and($found->id)->toBe($file->id);
});

it('returns null when getting non-existent file', function () {
    $found = $this->fileService->getFile(999);

    expect($found)->toBeNull();
});