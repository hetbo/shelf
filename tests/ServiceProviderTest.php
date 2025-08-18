<?php

namespace Hetbo\Shelf\Tests;

use Hetbo\Shelf\ShelfServiceProvider;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            ShelfServiceProvider::class,
        ];
    }

    #[Test]
    public function it_can_be_registered()
    {
        // Check if the service provider is registered
        $loadedProviders = $this->app->getLoadedProviders();

        // Fix the type issue by explicitly converting to string or using array_key_exists
        $this->assertArrayHasKey((string) ShelfServiceProvider::class, $loadedProviders);

        // Alternative approach:
        // $this->assertTrue(array_key_exists(ShelfServiceProvider::class, $loadedProviders));
    }

    #[Test]
    public function it_registers_the_package_configuration()
    {
        // Test that the config is merged correctly
        // This assumes you have a config/shelf.php file with some default values
        $configValue = config('shelf.default_disk');

        // Replace 'local' with whatever default value you have in your config file
        $this->assertEquals('public', $configValue);

        // Or test that the config key exists at all
        $this->assertTrue(config()->has('shelf'));
    }

    #[Test]
    public function it_merges_config_without_overriding_existing_values()
    {
        // Set a config value before the service provider runs
        config(['shelf.custom_key' => 'custom_value']);

        // Re-register the service provider to test merging
        $provider = new ShelfServiceProvider($this->app);
        $provider->register();

        // The custom value should still exist (not overridden)
        $this->assertEquals('custom_value', config('shelf.custom_key'));

        // And the default values should also be available
        $this->assertNotNull(config('shelf.default_disk'));
    }
}