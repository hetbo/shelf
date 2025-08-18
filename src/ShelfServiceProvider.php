<?php

namespace Hetbo\Shelf;

use Hetbo\Shelf\Contracts\FileableRepositoryInterface;
use Hetbo\Shelf\Contracts\FileMetadataRepositoryInterface;
use Hetbo\Shelf\Contracts\FileRepositoryInterface;
use Hetbo\Shelf\Contracts\FolderRepositoryInterface;
use Hetbo\Shelf\Http\Routes\ApiRoutes;
use Hetbo\Shelf\Repositories\FileableRepository;
use Hetbo\Shelf\Repositories\FileMetadataRepository;
use Hetbo\Shelf\Repositories\FileRepository;
use Hetbo\Shelf\Repositories\FolderRepository;
use Hetbo\Shelf\Services\FileableService;
use Hetbo\Shelf\Services\FileMetadataService;
use Hetbo\Shelf\Services\FileService;
use Hetbo\Shelf\Services\FolderService;
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

        ApiRoutes::register();
    }
}