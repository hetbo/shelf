<?php

namespace Hetbo\Shelf\Http\Routes;


use Hetbo\Shelf\Http\Controllers\ReactController;
use Illuminate\Support\Facades\Route;

class WebRoutes {
    public static function register(): void
    {
        Route::group([
            'prefix' => 'shelf',
            'as' => 'shelf.',
            'middleware' => ['web' , 'auth'],
        ], function () {

                Route::get('/', [ReactController::class, 'index'])->name('zero');
                Route::get('/api/files', [ReactController::class, 'getFiles']);
                Route::post('/api/upload', [ReactController::class, 'upload']);
                Route::delete('/api/files/{file}', [ReactController::class, 'delete']);

        });
    }
}