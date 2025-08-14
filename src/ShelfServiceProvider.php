<?php

namespace Hetbo\Shelf;

use Illuminate\Support\ServiceProvider;

class ShelfServiceProvider extends ServiceProvider {
    public function register() {
        $this->mergeConfigFrom(__DIR__.'/../config/shelf.php', 'shelf');
    }

    public function boot() {

    }
}