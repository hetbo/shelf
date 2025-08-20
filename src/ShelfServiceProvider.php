<?php

namespace Hetbo\Shelf;

use BladeUI\Icons\Factory;
use Hetbo\Shelf\Contracts\FileableRepositoryInterface;
use Hetbo\Shelf\Contracts\FileMetadataRepositoryInterface;
use Hetbo\Shelf\Contracts\FileRepositoryInterface;
use Hetbo\Shelf\Contracts\FolderRepositoryInterface;
use Hetbo\Shelf\Http\Routes\ApiRoutes;
use Hetbo\Shelf\Http\Routes\WebRoutes;
use Hetbo\Shelf\Repositories\FileableRepository;
use Hetbo\Shelf\Repositories\FileMetadataRepository;
use Hetbo\Shelf\Repositories\FileRepository;
use Hetbo\Shelf\Repositories\FolderRepository;
use Hetbo\Shelf\Services\FileableService;
use Hetbo\Shelf\Services\FileMetadataService;
use Hetbo\Shelf\Services\FileService;
use Hetbo\Shelf\Services\FolderService;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ShelfServiceProvider extends ServiceProvider {
    public function register() {
        $this->mergeConfigFrom(
            __DIR__.'/../config/shelf.php', 'shelf'
        );

        // Register repositories
        // Repository bindings
        $this->app->bind(FileRepositoryInterface::class, FileRepository::class);
        $this->app->bind(FolderRepositoryInterface::class, FolderRepository::class);
        $this->app->bind(FileableRepositoryInterface::class, FileableRepository::class);
        $this->app->bind(FileMetadataRepositoryInterface::class, FileMetadataRepository::class);

        // Service bindings
        $this->app->bind(FileService::class, function ($app) {
            return new FileService($app->make(FileRepositoryInterface::class));
        });

        $this->app->bind(FolderService::class, function ($app) {
            return new FolderService($app->make(FolderRepositoryInterface::class));
        });

        $this->app->bind(FileableService::class, function ($app) {
            return new FileableService($app->make(FileableRepositoryInterface::class));
        });

        $this->app->bind(FileMetadataService::class, function ($app) {
            return new FileMetadataService($app->make(FileMetadataRepositoryInterface::class));
        });

        $this->callAfterResolving(Factory::class, function (Factory $factory) {
            $factory->add('my', [
                'path' => __DIR__.'/../resources/svg',
                'prefix' => 'my',
            ]);
        });
    }

    public function boot() {
        if ($this->app->runningInConsole()) {
            // Publish migrations
            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'shelf-migrations');

            // Publish config
            $this->publishes([
                __DIR__.'/../config/shelf.php' => config_path('shelf.php'),
            ], 'shelf-config');
        }

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'shelf');

        Route::get('hetbo/shelf/{file}', function ($file) {
            $path = __DIR__ . '/../dist/' . $file;

            // Only allow .css or .cjs files
            if (!file_exists($path) ||
                (!str_ends_with($file, '.css') && !str_ends_with($file, '.cjs'))) {
                abort(404);
            }

            // Set the appropriate Content-Type
            $contentType = str_ends_with($file, '.css')
                ? 'text/css'
                : 'application/javascript';

            return Response::file($path, [
                'Content-Type' => $contentType,
                'Cache-Control' => 'public, max-age=86400', // cache 1 day
            ]);
        })->where('file', '.*\.(css|cjs)$');

        ApiRoutes::register();
        WebRoutes::register();

    }
}