<?php

namespace Hetbo\Shelf\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Hetbo\Shelf\ShelfServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            ShelfServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        $migration = include __DIR__.'/database/migrations/0000_00_00_000002_create_test_models_table.php';
        $migration->up();
    }
}
