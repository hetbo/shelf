<?php

namespace Hetbo\Shelf;

use Illuminate\Support\ServiceProvider;

class ShelfServiceProvider extends ServiceProvider {
    public function register() {
        $this->mergeConfigFrom(__DIR__.'/../config/shelf.php', 'shelf');
    }

    public function boot() {

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../config/shelf.php' => config_path('shelf.php'),
        ], 'shelf-config');

        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations')
        ], 'shelf-migrations');

    }
}