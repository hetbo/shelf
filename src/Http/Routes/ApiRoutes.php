<?php

namespace Hetbo\Shelf\Http\Routes;

use Hetbo\Shelf\Http\Controllers\Api\FileController;
use Hetbo\Shelf\Http\Controllers\Api\FileMetadataController;
use Hetbo\Shelf\Http\Controllers\Api\FileableController;
use Hetbo\Shelf\Http\Controllers\Api\FolderController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

class ApiRoutes
{
    public static function register(): void
    {
        Route::group([
            'prefix' => 'api/shelf',
            'middleware' => ['web', 'auth'],
        ], function () {

            Route::withoutMiddleware(VerifyCsrfToken::class)->group(function () {
                Route::post('/check', function () {
                    return [
                        'here' => 'we are',
                        'user id' => auth()->id()
                    ];
                });



            // File Routes
            Route::apiResource('files', FileController::class);
            Route::post('files/bulk', [FileController::class, 'bulk'])->name('files.bulk');
            Route::post('files/{file}/move', [FileController::class, 'move'])->name('files.move');
            Route::post('files/{file}/duplicate', [FileController::class, 'duplicate'])->name('files.duplicate');
            Route::get('files/{file}/download', [FileController::class, 'download'])->name('files.download');
            Route::get('files/{file}/contents', [FileController::class, 'contents'])->name('files.contents');

            // Folder Routes
            Route::apiResource('folders', FolderController::class);
            Route::post('folders/{folder}/move', [FolderController::class, 'move'])->name('folders.move');
            Route::get('folders/{folder}/path', [FolderController::class, 'path'])->name('folders.path');
            Route::get('folders/{folder}/children', [FolderController::class, 'children'])->name('folders.children');

            // File Attachment Routes
            Route::get('fileables', [FileableController::class, 'index'])->name('fileables.index');
            Route::post('fileables/attach', [FileableController::class, 'attach'])->name('fileables.attach');
            Route::delete('fileables/detach', [FileableController::class, 'detach'])->name('fileables.detach');
            Route::post('fileables/reorder', [FileableController::class, 'reorder'])->name('fileables.reorder');
            Route::post('fileables/sync', [FileableController::class, 'sync'])->name('fileables.sync');
            Route::get('fileables/file/{file}', [FileableController::class, 'fileAttachments'])->name('fileables.file-attachments');

            // File Metadata Routes
            Route::get('files/{file}/metadata', [FileMetadataController::class, 'index'])->name('files.metadata.index');
            Route::post('files/{file}/metadata', [FileMetadataController::class, 'store'])->name('files.metadata.store');
            Route::post('files/{file}/metadata/bulk', [FileMetadataController::class, 'bulkStore'])->name('files.metadata.bulk-store');
            Route::get('files/{file}/metadata/bulk', [FileMetadataController::class, 'bulkShow'])->name('files.metadata.bulk-show');
            Route::get('files/{file}/metadata/{key}', [FileMetadataController::class, 'show'])->name('files.metadata.show');
            Route::put('files/{file}/metadata/{key}', [FileMetadataController::class, 'update'])->name('files.metadata.update');
            Route::delete('files/{file}/metadata/{key}', [FileMetadataController::class, 'destroy'])->name('files.metadata.destroy');
            Route::delete('files/{file}/metadata', [FileMetadataController::class, 'destroyAll'])->name('files.metadata.destroy-all');



            });

        });
    }
}