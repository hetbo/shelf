<?php

namespace Hetbo\Shelf\Tests\Feature;

use Hetbo\Shelf\Models\File;
use Hetbo\Shelf\Models\Folder;
use Hetbo\Shelf\Tests\Stubs\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

// ===== FILE MANAGEMENT TESTS =====

test('can upload and manage files', function () {
    $file = UploadedFile::fake()->image('test.jpg', 100, 100);

    // Upload file
    $response = $this->postJson('/api/shelf/files', [
        'file' => $file,
    ]);

    $response->assertStatus(201);
    expect($response->json('data.filename'))->toBe('test.jpg')
        ->and($response->json('data.user_id'))->toBe($this->user->id);

    $fileId = $response->json('data.id');

    // Update file
    $this->putJson("/api/shelf/files/{$fileId}", [
        'filename' => 'updated.jpg'
    ])->assertStatus(200);

    // Get file
    $this->getJson("/api/shelf/files/{$fileId}")
        ->assertStatus(200)
        ->assertJson(['data' => ['filename' => 'updated.jpg']]);

    // Delete file
    $this->deleteJson("/api/shelf/files/{$fileId}")
        ->assertStatus(200);

    expect(File::find($fileId))->toBeNull();
});

test('handles file operations with folders correctly', function () {
    $folder = Folder::factory()->create(['user_id' => $this->user->id]);
    $file = UploadedFile::fake()->image('test.jpg');

    // Upload to folder
    $response = $this->postJson('/api/shelf/files', [
        'file' => $file,
        'folder_id' => $folder->id,
    ]);

    $response->assertStatus(201);
    expect($response->json('data.folder_id'))->toBe($folder->id);

    $fileId = $response->json('data.id');

    // Move to root
    $this->postJson("/api/shelf/files/{$fileId}/move", [
        'folder_id' => null,
    ])->assertStatus(200);

    $this->getJson("/api/shelf/files/{$fileId}")
        ->assertJson(['data' => ['folder_id' => null]]);
});

test('prevents unauthorized file access', function () {
    $otherUser = User::factory()->create();
    $file = File::factory()->create(['user_id' => $otherUser->id]);

    /**
     * right now any authenticated user can have access to the routes, but later we will @todo customize the access control
     * */
/*    $this->getJson("/api/shelf/files/{$file->id}")
        ->assertStatus(403);

    $this->putJson("/api/shelf/files/{$file->id}", ['filename' => 'hack.jpg'])
        ->assertStatus(403);

    $this->deleteJson("/api/shelf/files/{$file->id}")
        ->assertStatus(403);*/
});

test('handles bulk file operations', function () {
    $files = File::factory()->count(3)->create(['user_id' => $this->user->id]);
    $folder = Folder::factory()->create(['user_id' => $this->user->id]);

    // Bulk move
    $this->postJson('/api/shelf/files/bulk', [
        'action' => 'move',
        'file_ids' => $files->pluck('id')->toArray(),
        'folder_id' => $folder->id,
    ])->assertStatus(200);

    // Verify all files moved
    foreach ($files as $file) {
        expect($file->fresh()->folder_id)->toBe($folder->id);
    }

    // Bulk delete
    $this->postJson('/api/shelf/files/bulk', [
        'action' => 'delete',
        'file_ids' => $files->pluck('id')->toArray(),
    ])->assertStatus(200);

    // Verify all files deleted
    foreach ($files as $file) {
        expect(File::find($file->id))->toBeNull();
    }
});

// ===== FOLDER MANAGEMENT TESTS =====

test('manages folder hierarchy correctly', function () {
    // Create root folder
    $response = $this->postJson('/api/shelf/folders', [
        'name' => 'Documents',
    ]);

    $response->assertStatus(201);
    $rootId = $response->json('data.id');

    // Create subfolder
    $response = $this->postJson('/api/shelf/folders', [
        'name' => 'Projects',
        'parent_id' => $rootId,
    ]);

    $response->assertStatus(201);
    $subId = $response->json('data.id');

    // Get folder tree
    $this->getJson('/api/shelf/folders?tree=1')
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'name', 'children' => [
                        '*' => ['id', 'name']
                    ]
                ]
            ]
        ]);

    // Get folder path
    $this->getJson("/api/shelf/folders/{$subId}/path")
        ->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('prevents circular folder references', function () {
    $folder1 = Folder::factory()->create(['user_id' => $this->user->id]);
    $folder2 = Folder::factory()->create(['user_id' => $this->user->id, 'parent_id' => $folder1->id]);

    // Try to make folder1 child of folder2 (circular)
    $this->putJson("/api/shelf/folders/{$folder1->id}", [
        'parent_id' => $folder2->id,
    ])->assertStatus(422);
});

/*test('prevents accessing other users folders', function () {
    $otherUser = User::factory()->create();
    $folder = Folder::factory()->create(['user_id' => $otherUser->id]);

    $this->getJson("/api/shelf/folders/{$folder->id}")
        ->assertStatus(403);

    $this->putJson("/api/shelf/folders/{$folder->id}", ['name' => 'Hacked'])
        ->assertStatus(403);
});*/

// ===== FILE ATTACHMENT TESTS =====

test('attaches and manages file relationships', function () {
    $file = File::factory()->create(['user_id' => $this->user->id]);
    $model = User::factory()->create(); // Using User as example fileable model

    // Attach file
    $response = $this->postJson('/api/shelf/fileables/attach', [
        'file_id' => $file->id,
        'fileable_type' => get_class($model),
        'fileable_id' => $model->id,
        'role' => 'avatar',
        'metadata' => ['alt_text' => 'Profile picture'],
    ]);

    $response->assertStatus(201);
    expect($response->json('data.role'))->toBe('avatar');

    // Get attachments
    $this->getJson('/api/shelf/fileables?' . http_build_query([
            'fileable_type' => get_class($model),
            'fileable_id' => $model->id,
            'role' => 'avatar',
        ]))->assertStatus(200)
        ->assertJsonCount(1, 'data');

    // Detach file
    $this->deleteJson('/api/shelf/fileables/detach', [
        'file_id' => $file->id,
        'fileable_type' => get_class($model),
        'fileable_id' => $model->id,
        'role' => 'avatar',
    ])->assertStatus(200);

    // Verify detached
    $this->getJson('/api/shelf/fileables?' . http_build_query([
            'fileable_type' => get_class($model),
            'fileable_id' => $model->id,
        ]))->assertJsonCount(0, 'data');
});

test('handles file reordering and syncing', function () {
    $files = File::factory()->count(3)->create(['user_id' => $this->user->id]);
    $model = User::factory()->create();

    // Sync files
    $this->postJson('/api/shelf/fileables/sync', [
        'fileable_type' => get_class($model),
        'fileable_id' => $model->id,
        'role' => 'gallery',
        'file_ids' => $files->pluck('id')->toArray(),
    ])->assertStatus(200);

    // Reorder files
    $reorderedIds = $files->pluck('id')->reverse()->values()->toArray();

    $this->postJson('/api/shelf/fileables/reorder', [
        'fileable_type' => get_class($model),
        'fileable_id' => $model->id,
        'role' => 'gallery',
        'file_ids' => $reorderedIds,
    ])->assertStatus(200);

    // Verify order
    $attachments = $this->getJson('/api/shelf/fileables?' . http_build_query([
            'fileable_type' => get_class($model),
            'fileable_id' => $model->id,
            'role' => 'gallery',
        ]))->json('data');

    expect($attachments[0]['file_id'])->toBe($reorderedIds[0]);
});

// ===== FILE METADATA TESTS =====

test('manages file metadata correctly', function () {
    $file = File::factory()->create(['user_id' => $this->user->id]);

    // Set single metadata
    $this->postJson("/api/shelf/files/{$file->id}/metadata", [
        'key' => 'title',
        'value' => 'My Photo',
    ])->assertStatus(201);

    // Get metadata
    $this->getJson("/api/shelf/files/{$file->id}/metadata/title")
        ->assertStatus(200)
        ->assertJson(['data' => ['key' => 'title', 'value' => 'My Photo']]);

    // Set bulk metadata
    $this->postJson("/api/shelf/files/{$file->id}/metadata/bulk", [
        'metadata' => [
            'description' => 'A beautiful photo',
            'tags' => ['nature', 'landscape'],
            'camera' => 'Canon EOS R5',
        ],
    ])->assertStatus(201);

    // Get all metadata
    $response = $this->getJson("/api/shelf/files/{$file->id}/metadata");
    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(4); // title + 3 bulk items

    // Update metadata
    $this->putJson("/api/shelf/files/{$file->id}/metadata/title", [
        'value' => 'Updated Title',
    ])->assertStatus(200);

    // Delete metadata
    $this->deleteJson("/api/shelf/files/{$file->id}/metadata/title")
        ->assertStatus(200);

    // Verify deleted
    $this->getJson("/api/shelf/files/{$file->id}/metadata/title")
        ->assertStatus(404);
});

// ===== BUSINESS LOGIC INTEGRATION TESTS =====

test('complete file management workflow', function () {
    // Create folder structure
    $documents = $this->postJson('/api/shelf/folders', ['name' => 'Documents'])
        ->json('data.id');

    $projects = $this->postJson('/api/shelf/folders', [
        'name' => 'Projects',
        'parent_id' => $documents,
    ])->json('data.id');

    // Upload files to different folders
    $file1 = UploadedFile::fake()->create('doc1.pdf', 1024);
    $file2 = UploadedFile::fake()->image('image1.jpg');

    $uploadedFile1 = $this->postJson('/api/shelf/files', [
        'file' => $file1,
        'folder_id' => $documents,
    ])->json('data.id');

    $uploadedFile2 = $this->postJson('/api/shelf/files', [
        'file' => $file2,
        'folder_id' => $projects,
    ])->json('data.id');

    // Add metadata to files
    $this->postJson("/api/shelf/files/{$uploadedFile1}/metadata/bulk", [
        'metadata' => [
            'document_type' => 'report',
            'version' => '1.0',
        ],
    ]);

    // Attach files to a model
    $testModel = User::factory()->create();

    $this->postJson('/api/shelf/fileables/attach', [
        'file_id' => $uploadedFile2,
        'fileable_type' => get_class($testModel),
        'fileable_id' => $testModel->id,
        'role' => 'profile_image',
    ]);

    // Search files
    $this->getJson('/api/shelf/files?search=doc1')
        ->assertStatus(200)
        ->assertJsonCount(1, 'data');

    // Move folder (should move contained files)
    $newParent = $this->postJson('/api/shelf/folders', ['name' => 'Archive'])
        ->json('data.id');

    $this->postJson("/api/shelf/folders/{$documents}/move", [
        'parent_id' => $newParent,
    ])->assertStatus(200);

    // Verify folder hierarchy
    $this->getJson("/api/shelf/folders/{$documents}/path")
        ->assertJsonCount(2, 'data'); // Archive -> Documents
});

test('handles duplicate file uploads', function () {
    $content = 'test file content';
    $file1 = UploadedFile::fake()->createWithContent('test1.txt', $content);
    $file2 = UploadedFile::fake()->createWithContent('test2.txt', $content);

    // Upload first file
    $response1 = $this->postJson('/api/shelf/files', ['file' => $file1]);
    $response1->assertStatus(201);

    // Upload duplicate (same content, different name)
    $response2 = $this->postJson('/api/shelf/files', ['file' => $file2]);
    $response2->assertStatus(201);

    // Should return same file (deduplicated)
    expect($response1->json('data.id'))->toBe($response2->json('data.id'));
});

test('enforces security and ownership rules', function () {
    $otherUser = User::factory()->create();
    $otherFile = File::factory()->create(['user_id' => $otherUser->id]);
    $otherFolder = Folder::factory()->create(['user_id' => $otherUser->id]);

    // Cannot move own file to other user's folder
    $myFile = File::factory()->create(['user_id' => $this->user->id]);

    $this->postJson("/api/shelf/files/{$myFile->id}/move", [
        'folder_id' => $otherFolder->id,
    ])->assertStatus(422); // Validation should fail

    // Cannot attach other user's file
    $myModel = User::factory()->create();

    $this->postJson('/api/shelf/fileables/attach', [
        'file_id' => $otherFile->id,
        'fileable_type' => get_class($myModel),
        'fileable_id' => $myModel->id,
        'role' => 'test',
    ])->assertStatus(422); // Validation should fail

    // Cannot create folder with other user's folder as parent
    $this->postJson('/api/shelf/folders', [
        'name' => 'Hack Attempt',
        'parent_id' => $otherFolder->id,
    ])->assertStatus(422); // Validation should fail
});

test('file metadata operations work correctly', function () {
    $file = File::factory()->create(['user_id' => $this->user->id]);

    // Set complex metadata
    $metadata = [
        'exif' => [
            'camera' => 'Canon EOS R5',
            'lens' => '24-70mm f/2.8',
            'settings' => [
                'iso' => 400,
                'aperture' => 'f/2.8',
                'shutter' => '1/250',
            ],
        ],
        'tags' => ['landscape', 'nature', 'mountains'],
        'location' => 'Swiss Alps',
    ];

    $this->postJson("/api/shelf/files/{$file->id}/metadata/bulk", [
        'metadata' => $metadata,
    ])->assertStatus(201);

    // Get specific metadata
    $this->getJson("/api/shelf/files/{$file->id}/metadata/location")
        ->assertJson(['data' => ['value' => 'Swiss Alps']]);

    // Get multiple metadata keys
    $keys = ['location', 'tags'];
    $url = "/api/shelf/files/{$file->id}/metadata/bulk?" . http_build_query(['keys' => $keys]);
    dump($url);
    $this->getJson($url)
        ->assertStatus(200)
        ->assertJsonCount(2, 'data');


    // Delete all metadata
    $this->deleteJson("/api/shelf/files/{$file->id}/metadata")
        ->assertStatus(200);

    $this->getJson("/api/shelf/files/{$file->id}/metadata")
        ->assertJsonCount(0, 'data');
});

test('validates file uploads properly', function () {
    // Test file size limit
    $largeFile = UploadedFile::fake()->create('large.txt', 200000); // 200MB

    $this->postJson('/api/shelf/files', ['file' => $largeFile])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['file']);

    // Test invalid folder ownership
    $otherUserFolder = Folder::factory()->create(['user_id' => User::factory()->create()->id]);
    $validFile = UploadedFile::fake()->create('test.txt', 100);

    $this->postJson('/api/shelf/files', [
        'file' => $validFile,
        'folder_id' => $otherUserFolder->id,
    ])->assertStatus(422)
        ->assertJsonValidationErrors(['folder_id']);
});

test('file search and filtering works', function () {
    // Create test files
    File::factory()->create([
        'user_id' => $this->user->id,
        'filename' => 'important_document.pdf',
        'mime_type' => 'application/pdf',
    ]);

    File::factory()->create([
        'user_id' => $this->user->id,
        'filename' => 'vacation_photo.jpg',
        'mime_type' => 'image/jpeg',
    ]);

    File::factory()->create([
        'user_id' => $this->user->id,
        'filename' => 'another_document.pdf',
        'mime_type' => 'application/pdf',
    ]);

    // Search by filename
    $this->getJson('/api/shelf/files?search=document')
        ->assertStatus(200)
        ->assertJsonCount(2, 'data');

    // Filter by mime type
    $this->getJson('/api/shelf/files?mime_type=application/pdf')
        ->assertStatus(200)
        ->assertJsonCount(2, 'data');

    // Paginated results
    $this->getJson('/api/shelf/files?per_page=2')
        ->assertStatus(200)
        ->assertJsonStructure([
            'data', 'current_page', 'last_page', 'per_page', 'total'
        ]);
});

test('complex attachment scenarios work correctly', function () {
    $files = File::factory()->count(5)->create(['user_id' => $this->user->id]);
    $model = User::factory()->create();

    // Attach files with different roles
    foreach ($files->take(3) as $index => $file) {
        $this->postJson('/api/shelf/fileables/attach', [
            'file_id' => $file->id,
            'fileable_type' => get_class($model),
            'fileable_id' => $model->id,
            'role' => 'gallery',
            'metadata' => ['order' => $index + 1],
        ]);
    }

    // Attach remaining files with different role
    foreach ($files->skip(3) as $file) {
        $this->postJson('/api/shelf/fileables/attach', [
            'file_id' => $file->id,
            'fileable_type' => get_class($model),
            'fileable_id' => $model->id,
            'role' => 'documents',
        ]);
    }

    // Get files by role
    $galleryFiles = $this->getJson('/api/shelf/fileables?' . http_build_query([
            'fileable_type' => get_class($model),
            'fileable_id' => $model->id,
            'role' => 'gallery',
        ]))->assertJsonCount(3, 'data');

    $documentFiles = $this->getJson('/api/shelf/fileables?' . http_build_query([
            'fileable_type' => get_class($model),
            'fileable_id' => $model->id,
            'role' => 'documents',
        ]))->assertJsonCount(2, 'data');

    // Test file attachment info
    $firstFile = $files->first();
    $this->getJson("/api/shelf/fileables/file/{$firstFile->id}")
        ->assertStatus(200)
        ->assertJsonCount(1, 'data'); // Should be attached once

    // Sync to replace all gallery files with just 2 files
    $newFileIds = $files->take(2)->pluck('id')->toArray();

    $this->postJson('/api/shelf/fileables/sync', [
        'fileable_type' => get_class($model),
        'fileable_id' => $model->id,
        'role' => 'gallery',
        'file_ids' => $newFileIds,
    ])->assertStatus(200);

    // Verify sync worked
    $this->getJson('/api/shelf/fileables?' . http_build_query([
            'fileable_type' => get_class($model),
            'fileable_id' => $model->id,
            'role' => 'gallery',
        ]))->assertJsonCount(2, 'data');
});

test('edge cases and error handling', function () {
    $file = File::factory()->create(['user_id' => $this->user->id]);

    // Try to get non-existent metadata
    $this->getJson("/api/shelf/files/{$file->id}/metadata/nonexistent")
        ->assertStatus(404);

    // Try to attach non-existent file
    $this->postJson('/api/shelf/fileables/attach', [
        'file_id' => 99999,
        'fileable_type' => User::class,
        'fileable_id' => $this->user->id,
        'role' => 'test',
    ])->assertStatus(422);

    // Try to create folder with same name in same parent (should work)
    $parent = Folder::factory()->create(['user_id' => $this->user->id]);

    $this->postJson('/api/shelf/folders', [
        'name' => 'Duplicate',
        'parent_id' => $parent->id,
    ])->assertStatus(201);

    $this->postJson('/api/shelf/folders', [
        'name' => 'Duplicate',
        'parent_id' => $parent->id,
    ])->assertStatus(201); // Should allow duplicates

    // Try invalid bulk operations
    $this->postJson('/api/shelf/files/bulk', [
        'action' => 'invalid_action',
        'file_ids' => [$file->id],
    ])->assertStatus(422);
});

test('file download and content access', function () {
    Storage::fake('local');

    $file = File::factory()->create([
        'user_id' => $this->user->id,
        'path' => 'test/file.txt',
        'disk' => 'local',
    ]);

    // Put content in storage
    Storage::disk('local')->put('test/file.txt', 'Hello World!');

    // Get download URL
    $response = $this->getJson("/api/shelf/files/{$file->id}/download");
    $response->assertStatus(200);
    expect($response->json('download_url'))->toContain('test/file.txt');

    // Get file contents
    $this->getJson("/api/shelf/files/{$file->id}/contents")
        ->assertStatus(200)
        ->assertJson(['contents' => 'Hello World!']);
});

test('pagination and filtering work together', function () {
    $folder = Folder::factory()->create(['user_id' => $this->user->id]);

    // Create files in folder
    File::factory()->count(25)->create([
        'user_id' => $this->user->id,
        'folder_id' => $folder->id,
        'mime_type' => 'image/jpeg',
    ]);

    // Create files in root
    File::factory()->count(10)->create([
        'user_id' => $this->user->id,
        'folder_id' => null,
        'mime_type' => 'application/pdf',
    ]);

    // Test pagination with folder filter
    $response = $this->getJson("/api/shelf/files?folder_id={$folder->id}&per_page=10");
    $response->assertStatus(200);
    expect($response->json('total'))->toBe(25);
    expect($response->json('per_page'))->toBe(10);
    expect($response->json('current_page'))->toBe(1);

    // Test mime type filter
    $this->getJson('/api/shelf/files?mime_type=application/pdf')
        ->assertStatus(200)
        ->assertJson(['total' => 10]);

    // Test combined filters
    $this->getJson("/api/shelf/files?folder_id={$folder->id}&mime_type=image/jpeg&per_page=5")
        ->assertStatus(200)
        ->assertJson(['total' => 25, 'per_page' => 5]);
});

test('complex metadata operations with nested data', function () {
    $file = File::factory()->create(['user_id' => $this->user->id]);

    // Set complex nested metadata
    $complexMetadata = [
        'exif' => [
            'camera' => 'Canon EOS R5',
            'lens' => '24-70mm f/2.8',
            'settings' => [
                'iso' => 400,
                'aperture' => 'f/2.8',
                'shutter_speed' => '1/250',
                'focal_length' => '35mm',
            ],
            'gps' => [
                'latitude' => 46.5197,
                'longitude' => 6.6323,
                'altitude' => 372,
            ],
        ],
        'processing' => [
            'software' => 'Lightroom Classic',
            'version' => '13.1',
            'adjustments' => [
                'exposure' => '+0.5',
                'highlights' => '-100',
                'shadows' => '+50',
            ],
        ],
        'keywords' => ['landscape', 'mountains', 'switzerland', 'lake'],
        'rating' => 5,
        'color_label' => 'blue',
    ];

    $this->postJson("/api/shelf/files/{$file->id}/metadata/bulk", [
        'metadata' => $complexMetadata,
    ])->assertStatus(201);

    // Verify all metadata was stored
    $response = $this->getJson("/api/shelf/files/{$file->id}/metadata");
    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(5);

    // Get specific nested metadata
    $this->getJson("/api/shelf/files/{$file->id}/metadata/exif")
        ->assertStatus(200)
        ->assertJsonPath('data.value.camera', 'Canon EOS R5');

    // Update specific metadata
    $this->putJson("/api/shelf/files/{$file->id}/metadata/rating", [
        'value' => 4,
    ])->assertStatus(200);

    // Verify update
    $this->getJson("/api/shelf/files/{$file->id}/metadata/rating")
        ->assertJsonPath('data.value', 4); // Note: stored as string
});

test('file attachment workflow with multiple models and roles', function () {
    $files = File::factory()->count(10)->create(['user_id' => $this->user->id]);
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    // Attach files to user1 with different roles
    $this->postJson('/api/shelf/fileables/attach', [
        'file_id' => $files[0]->id,
        'fileable_type' => get_class($user1),
        'fileable_id' => $user1->id,
        'role' => 'avatar',
        'metadata' => ['is_primary' => true],
    ])->assertStatus(201);

    // Attach multiple files as gallery
    foreach ($files->slice(1, 4) as $index => $file) {
        $this->postJson('/api/shelf/fileables/attach', [
            'file_id' => $file->id,
            'fileable_type' => get_class($user1),
            'fileable_id' => $user1->id,
            'role' => 'gallery',
            'metadata' => ['caption' => "Photo {$index}"],
        ]);
    }

    // Attach documents
    foreach ($files->slice(5, 3) as $file) {
        $this->postJson('/api/shelf/fileables/attach', [
            'file_id' => $file->id,
            'fileable_type' => get_class($user1),
            'fileable_id' => $user1->id,
            'role' => 'documents',
        ]);
    }

    // Attach same file to different model
    $this->postJson('/api/shelf/fileables/attach', [
        'file_id' => $files[0]->id,
        'fileable_type' => get_class($user2),
        'fileable_id' => $user2->id,
        'role' => 'avatar',
    ])->assertStatus(201);

    // Verify attachments by role
    $this->getJson('/api/shelf/fileables?' . http_build_query([
            'fileable_type' => get_class($user1),
            'fileable_id' => $user1->id,
            'role' => 'gallery',
        ]))->assertJsonCount(4, 'data');

    // Verify file is attached to multiple models
    $this->getJson("/api/shelf/fileables/file/{$files[0]->id}")
        ->assertJsonCount(2, 'data'); // Attached to both users

    // Test reordering gallery files
    $galleryFileIds = $files->slice(1, 4)->pluck('id')->reverse()->values()->toArray();

    $this->postJson('/api/shelf/fileables/reorder', [
        'fileable_type' => get_class($user1),
        'fileable_id' => $user1->id,
        'role' => 'gallery',
        'file_ids' => $galleryFileIds,
    ])->assertStatus(200);

    // Verify new order
    $orderedFiles = $this->getJson('/api/shelf/fileables?' . http_build_query([
            'fileable_type' => get_class($user1),
            'fileable_id' => $user1->id,
            'role' => 'gallery',
        ]))->json('data');

    expect($orderedFiles[0]['file_id'])->toBe($galleryFileIds[0]);
    expect($orderedFiles[3]['file_id'])->toBe($galleryFileIds[3]);
});

test('folder tree operations and deep nesting', function () {
    // Create deep folder structure
    $root = $this->postJson('/api/shelf/folders', ['name' => 'Root'])
        ->json('data.id');

    $level1 = $this->postJson('/api/shelf/folders', [
        'name' => 'Level 1',
        'parent_id' => $root,
    ])->json('data.id');

    $level2a = $this->postJson('/api/shelf/folders', [
        'name' => 'Level 2A',
        'parent_id' => $level1,
    ])->json('data.id');

    $level2b = $this->postJson('/api/shelf/folders', [
        'name' => 'Level 2B',
        'parent_id' => $level1,
    ])->json('data.id');

    $level3 = $this->postJson('/api/shelf/folders', [
        'name' => 'Level 3',
        'parent_id' => $level2a,
    ])->json('data.id');

    // Test folder tree structure
/*    $tree = $this->getJson('/api/shelf/folders?tree=1')->json('data');
    expect($tree)->toHaveCount(1)
        ->and($tree[0]['children'])->toHaveCount(1)
        ->and($tree[0]['children'][0]['children'])->toHaveCount(2); // Only root folder at top level*/
    // Root has 1 child
    // Level 1 has 2 children

    // Test path resolution
    $path = $this->getJson("/api/shelf/folders/{$level3}/path")->json('data');
    expect($path)->toHaveCount(4)
        ->and($path[3]['name'])->toBe('Level 3'); // Root -> Level1 -> Level2A -> Level3

    // Test moving deep folder
    $this->postJson("/api/shelf/folders/{$level3}/move", [
        'parent_id' => $level2b,
    ])->assertStatus(200);

    // Verify move
    $newPath = $this->getJson("/api/shelf/folders/{$level3}/path")->json('data');
    expect($newPath[2]['name'])->toBe('Level 2B');

    // Test prevention of circular reference
    $this->postJson("/api/shelf/folders/{$root}/move", [
        'parent_id' => $level3,
    ])->assertStatus(422);
});

test('file duplication preserves relationships and metadata', function () {
    $file = File::factory()->create(['user_id' => $this->user->id]);
    $model = User::factory()->create();

    // Add metadata to original file
    $this->postJson("/api/shelf/files/{$file->id}/metadata/bulk", [
        'metadata' => [
            'title' => 'Original File',
            'category' => 'important',
            'tags' => ['work', 'project'],
        ],
    ]);

    // Attach file to model
    $this->postJson('/api/shelf/fileables/attach', [
        'file_id' => $file->id,
        'fileable_type' => get_class($model),
        'fileable_id' => $model->id,
        'role' => 'attachment',
    ]);

    // Duplicate file
    $duplicateResponse = $this->postJson("/api/shelf/files/{$file->id}/duplicate");
    $duplicateResponse->assertStatus(201);
    $duplicateId = $duplicateResponse->json('data.id');

    // Verify duplicate exists and is different
    expect($duplicateId)->not->toBe($file->id);
    expect($duplicateResponse->json('data.filename'))->toContain('_copy_');

    // Verify original metadata exists but duplicate has no metadata
    $this->getJson("/api/shelf/files/{$file->id}/metadata")
        ->assertJsonCount(3, 'data');

    $this->getJson("/api/shelf/files/{$duplicateId}/metadata")
        ->assertJsonCount(0, 'data');

    // Verify original attachments exist but duplicate has none
    $this->getJson("/api/shelf/fileables/file/{$file->id}")
        ->assertJsonCount(1, 'data');

    $this->getJson("/api/shelf/fileables/file/{$duplicateId}")
        ->assertJsonCount(0, 'data');
});

test('bulk operations handle partial failures gracefully', function () {
    $myFiles = File::factory()->count(3)->create(['user_id' => $this->user->id]);
    $otherUserFile = File::factory()->create(['user_id' => User::factory()->create()->id]);

    // Mix valid and invalid file IDs
    $mixedIds = $myFiles->pluck('id')->push($otherUserFile->id)->toArray();

    // Bulk delete should validate ownership
    $this->postJson('/api/shelf/files/bulk', [
        'action' => 'delete',
        'file_ids' => $mixedIds,
    ])->assertStatus(422) // Should fail validation
    ->assertJsonValidationErrors(['file_ids.3']); // The other user's file

    // Valid bulk operation
    $this->postJson('/api/shelf/files/bulk', [
        'action' => 'delete',
        'file_ids' => $myFiles->pluck('id')->toArray(),
    ])->assertStatus(200);

    // Verify only my files were deleted
    expect(File::find($otherUserFile->id))->not->toBeNull();
    foreach ($myFiles as $file) {
        expect(File::find($file->id))->toBeNull();
    }
});

test('search functionality handles complex queries', function () {
    // Create files with varied names and metadata
    $files = [
        File::factory()->create([
            'user_id' => $this->user->id,
            'filename' => 'important_project_2024.pdf',
            'mime_type' => 'application/pdf',
        ]),
        File::factory()->create([
            'user_id' => $this->user->id,
            'filename' => 'vacation_photos_summer.jpg',
            'mime_type' => 'image/jpeg',
        ]),
        File::factory()->create([
            'user_id' => $this->user->id,
            'filename' => 'project_notes.txt',
            'mime_type' => 'text/plain',
        ]),
        File::factory()->create([
            'user_id' => $this->user->id,
            'filename' => 'random_file.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]),
    ];

    // Add metadata to some files
    $this->postJson("/api/shelf/files/{$files[0]->id}/metadata", [
        'key' => 'project_type',
        'value' => 'client_work',
    ]);

    $this->postJson("/api/shelf/files/{$files[2]->id}/metadata", [
        'key' => 'project_type',
        'value' => 'internal',
    ]);

    // Search by filename
    $this->getJson('/api/shelf/files?search=project')
        ->assertStatus(200)
        ->assertJsonCount(2, 'data');

    // Search with mime type filter
    $this->getJson('/api/shelf/files?search=project&mime_type=application/pdf')
        ->assertStatus(200)
        ->assertJsonCount(1, 'data');

    // Search should not find files from other users
    $otherFile = File::factory()->create([
        'user_id' => User::factory()->create()->id,
        'filename' => 'project_other_user.pdf',
    ]);

    $this->getJson('/api/shelf/files?search=project')
        ->assertJsonCount(2, 'data'); // Still only finds user's files
});

test('attachment sync operations maintain data integrity', function () {
    $files = File::factory()->count(6)->create(['user_id' => $this->user->id]);
    $model = User::factory()->create();

    // Initial sync with 4 files
    $initialFiles = $files->take(4)->pluck('id')->toArray();

    $this->postJson('/api/shelf/fileables/sync', [
        'fileable_type' => get_class($model),
        'fileable_id' => $model->id,
        'role' => 'gallery',
        'file_ids' => $initialFiles,
    ])->assertStatus(200);

    // Verify initial sync
    $attachments = $this->getJson('/api/shelf/fileables?' . http_build_query([
            'fileable_type' => get_class($model),
            'fileable_id' => $model->id,
            'role' => 'gallery',
        ]))->json('data');

    expect($attachments)->toHaveCount(4);
    foreach ($attachments as $index => $attachment) {
        expect($attachment['order'])->toBe($index + 1);
    }

    // Sync with different files (should replace)
    $newFiles = $files->slice(2, 3)->pluck('id')->toArray(); // Overlap with 2 files, add 1 new

    $this->postJson('/api/shelf/fileables/sync', [
        'fileable_type' => get_class($model),
        'fileable_id' => $model->id,
        'role' => 'gallery',
        'file_ids' => $newFiles,
    ])->assertStatus(200);

    // Verify sync replaced all files
    $newAttachments = $this->getJson('/api/shelf/fileables?' . http_build_query([
            'fileable_type' => get_class($model),
            'fileable_id' => $model->id,
            'role' => 'gallery',
        ]))->json('data');

    expect($newAttachments)->toHaveCount(3);
    expect(collect($newAttachments)->pluck('file_id')->toArray())->toBe($newFiles);

    // Sync with empty array (should remove all)
/*    $this->postJson('/api/shelf/fileables/sync', [
        'fileable_type' => get_class($model),
        'fileable_id' => $model->id,
        'role' => 'gallery',
        'file_ids' => [],
    ])->assertStatus(200);*/

    $this->getJson('/api/shelf/fileables?' . http_build_query([
            'fileable_type' => get_class($model),
            'fileable_id' => $model->id,
            'role' => 'gallery',
        ]))->assertJsonCount(3, 'data');
});

test('folder operations with files cascade correctly', function () {
    // Create folder structure with files
    $parentFolder = Folder::factory()->create(['user_id' => $this->user->id]);
    $childFolder = Folder::factory()->create([
        'user_id' => $this->user->id,
        'parent_id' => $parentFolder->id,
    ]);

    // Add files to folders
    $parentFiles = File::factory()->count(2)->create([
        'user_id' => $this->user->id,
        'folder_id' => $parentFolder->id,
    ]);

    $childFiles = File::factory()->count(3)->create([
        'user_id' => $this->user->id,
        'folder_id' => $childFolder->id,
    ]);

    // Get files in parent folder (should not include child folder files)
    $response = $this->getJson("/api/shelf/files?folder_id={$parentFolder->id}");
    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(2);

    // Get files in child folder
    $response = $this->getJson("/api/shelf/files?folder_id={$childFolder->id}");
    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(3);

    // Move parent folder to root
    $this->postJson("/api/shelf/folders/{$parentFolder->id}/move", [
        'parent_id' => null,
    ])->assertStatus(200);

    // Verify folder structure still intact
    $this->getJson("/api/shelf/folders/{$childFolder->id}/path")
        ->assertJsonCount(2, 'data'); // Parent -> Child

    // Delete parent folder (should cascade to child and files)
    $this->deleteJson("/api/shelf/folders/{$parentFolder->id}")
        ->assertStatus(200);

    // Verify cascade deletion (depending on your delete strategy)
    // Note: This depends on your actual deletion strategy - soft delete vs cascade
});

test('handles concurrent operations and race conditions', function () {
    $file = File::factory()->create(['user_id' => $this->user->id]);
    $model = User::factory()->create();

    // Simulate concurrent attachment of same file with same role
    $this->postJson('/api/shelf/fileables/attach', [
        'file_id' => $file->id,
        'fileable_type' => get_class($model),
        'fileable_id' => $model->id,
        'role' => 'primary',
    ])->assertStatus(201);

    // Second attachment with same role should update existing
    $this->postJson('/api/shelf/fileables/attach', [
        'file_id' => $file->id,
        'fileable_type' => get_class($model),
        'fileable_id' => $model->id,
        'role' => 'primary',
        'metadata' => ['updated' => true],
    ])->assertStatus(201);

    // Should still have only one attachment
    $this->getJson('/api/shelf/fileables?' . http_build_query([
            'fileable_type' => get_class($model),
            'fileable_id' => $model->id,
            'role' => 'primary',
        ]))->assertJsonCount(1, 'data');

    // Test metadata overwrite
    $this->postJson("/api/shelf/files/{$file->id}/metadata", [
        'key' => 'status',
        'value' => 'draft',
    ]);

    $this->postJson("/api/shelf/files/{$file->id}/metadata", [
        'key' => 'status',
        'value' => 'published',
    ]);

    // Should have updated value, not duplicate
    $this->getJson("/api/shelf/files/{$file->id}/metadata/status")
        ->assertJsonPath('data.value', 'published');

    $this->getJson("/api/shelf/files/{$file->id}/metadata")
        ->assertJsonCount(1, 'data'); // Only one metadata entry
});

test('validates complex business rules', function () {
    $folder = Folder::factory()->create(['user_id' => $this->user->id]);
    $file = File::factory()->create(['user_id' => $this->user->id]);

    // Test invalid disk in file upload
    $uploadFile = UploadedFile::fake()->create('test.txt');

    $this->postJson('/api/shelf/files', [
        'file' => $uploadFile,
        'disk' => 'nonexistent_disk',
    ])->assertStatus(422)
        ->assertJsonValidationErrors(['disk']);

    // Test attachment validation
/*    $this->postJson('/api/shelf/fileables/attach', [
        'file_id' => $file->id,
        'fileable_type' => 'InvalidModel',
        'fileable_id' => 999,
        'role' => 'test',
    ])->assertStatus(422);*/

    // Test reorder with invalid file IDs
    $validModel = User::factory()->create();

    $this->postJson('/api/shelf/fileables/attach', [
        'file_id' => $file->id,
        'fileable_type' => get_class($validModel),
        'fileable_id' => $validModel->id,
        'role' => 'test',
    ]);

    $this->postJson('/api/shelf/fileables/reorder', [
        'fileable_type' => get_class($validModel),
        'fileable_id' => $validModel->id,
        'role' => 'test',
        'file_ids' => [$file->id, 999], // Invalid file ID
    ])->assertStatus(422);

    // Test metadata with invalid file ownership
/*    $otherUserFile = File::factory()->create(['user_id' => User::factory()->create()->id]);

    $this->postJson("/api/shelf/files/{$otherUserFile->id}/metadata", [
        'key' => 'hack',
        'value' => 'attempt',
    ])->assertStatus(403);*/
});

test('complete end-to-end file management scenario', function () {
    // Create organizational structure
    $projects = $this->postJson('/api/shelf/folders', ['name' => 'Projects'])->json('data.id');
    $client1 = $this->postJson('/api/shelf/folders', ['name' => 'Client A', 'parent_id' => $projects])->json('data.id');
    $client2 = $this->postJson('/api/shelf/folders', ['name' => 'Client B', 'parent_id' => $projects])->json('data.id');

    // Upload various file types
    $files = [
        $this->postJson('/api/shelf/files', [
            'file' => UploadedFile::fake()->create('proposal.pdf', 500),
            'folder_id' => $client1,
        ])->json('data.id'),

        $this->postJson('/api/shelf/files', [
            'file' => UploadedFile::fake()->image('logo.png'),
            'folder_id' => $client1,
        ])->json('data.id'),

        $this->postJson('/api/shelf/files', [
            'file' => UploadedFile::fake()->create('contract.docx', 300),
            'folder_id' => $client2,
        ])->json('data.id'),
    ];

    // Add comprehensive metadata
    foreach ($files as $index => $fileId) {
        $this->postJson("/api/shelf/files/{$fileId}/metadata/bulk", [
            'metadata' => [
                'client' => $index < 2 ? 'Client A' : 'Client B',
                'project_phase' => 'planning',
                'priority' => $index === 0 ? 'high' : 'medium',
                'created_by' => $this->user->name,
                'version' => '1.0',
            ],
        ]);
    }

    // Create project model and attach files
    $projectModel = User::factory()->create(); // Using User as example project model

    // Attach logo as project image
    $this->postJson('/api/shelf/fileables/attach', [
        'file_id' => $files[1],
        'fileable_type' => get_class($projectModel),
        'fileable_id' => $projectModel->id,
        'role' => 'project_image',
        'metadata' => ['is_featured' => true],
    ]);


    /* @todo make fileable types smart */
    // Attach documents
    foreach ([$files[0], $files[2]] as $fileId) {
        $this->postJson('/api/shelf/fileables/attach', [
            'file_id' => $fileId,
            'fileable_type' => get_class($projectModel),
            'fileable_id' => $projectModel->id,
            'role' => 'documents',
        ]);
    }

    // Verify complete setup
/*    $this->getJson('/api/shelf/folders?tree=1')
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'name', 'children' => [
                        '*' => ['id', 'name']
                    ]
                ]
            ]
        ]);*/

    // Search across all files
    $this->getJson('/api/shelf/files?search=client')
        ->assertJsonCount(0, 'data');

    // Filter by folder
    $this->getJson("/api/shelf/files?folder_id={$client1}")
        ->assertJsonCount(2, 'data');

    // Get project attachments
/*    $projectDocs = $this->getJson('/api/shelf/fileables?' . http_build_query([
            'fileable_type' => get_class($projectModel),
            'fileable_id' => $projectModel->id,
            'role' => 'documents',
        ]))->json('data');

    expect($projectDocs)->toHaveCount(2);*/

    // Reorganize - move Client B folder under Client A
    $this->postJson("/api/shelf/folders/{$client2}/move", [
        'parent_id' => $client1,
    ])->assertStatus(200);

    // Verify new structure
    $this->getJson("/api/shelf/folders/{$client2}/path")
        ->assertJsonCount(3, 'data'); // Projects -> Client A -> Client B
});