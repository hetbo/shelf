<?php

namespace Hetbo\Shelf\Http\Routes;


use Hetbo\Shelf\Http\Controllers\Web\AssetController;
use Hetbo\Shelf\Http\Controllers\Web\LibraryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class WebRoutes {
    public static function register(): void
    {
        Route::group([
            'prefix' => 'shelf',
            'as' => 'shelf.',
            'middleware' => ['web'],
        ], function () {

            Route::get('/', [LibraryController::class, 'index'])->name('index');


            Route::get('/assets/main.js', [AssetController::class, 'js'])->name('assets.js');
            Route::get('/assets/main.css', [AssetController::class, 'css'])->name('assets.css');



        });
    }
}