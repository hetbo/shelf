<?php

namespace Hetbo\Shelf;

use Hetbo\Shelf\Contracts\FileMetadataRepositoryInterface;
use Hetbo\Shelf\Contracts\FileRepositoryInterface;
use Hetbo\Shelf\Contracts\FileableRepositoryInterface;
use Hetbo\Shelf\Repositories\FileMetadataRepository;
use Hetbo\Shelf\Repositories\FileRepository;
use Hetbo\Shelf\Repositories\FileableRepository;
use Illuminate\Support\ServiceProvider;

class ShelfServiceProvider extends ServiceProvider {
    public function register() {
        $this->mergeConfigFrom(
            __DIR__.'/../config/shelf.php', 'shelf'
        );

        // Register repositories
        $this->app->bind(FileRepositoryInterface::class, FileRepository::class);
        $this->app->bind(FileableRepositoryInterface::class, FileableRepository::class);
        $this->app->bind(FileMetadataRepositoryInterface::class, FileMetadataRepository::class);
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
    }
}