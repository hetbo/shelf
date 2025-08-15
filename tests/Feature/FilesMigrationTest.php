<?php

use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    $this->artisan('migrate');
});

it('runs the migration [shelf_files table] successfully' , function () {

    expect(Schema::hasTable('shelf_files'))->toBeTrue()
        ->and(Schema::hasColumn('shelf_files', 'filename'))->toBeTrue();

});

it('runs the migration [shelf_fileables table] successfully', function () {

    expect(Schema::hasTable('shelf_fileables'))->toBeTrue()
        ->and(Schema::hasColumn('shelf_fileables', 'file_id'))->toBeTrue()
        ->and(Schema::hasColumn('shelf_fileables', 'fileable_id'))->toBeTrue()
        ->and(Schema::hasColumn('shelf_fileables', 'fileable_type'))->toBeTrue();

});

it('runs the migration [shelf_file_metadata table] successfully' , function () {

    expect(Schema::hasTable('shelf_file_metadata'))->toBeTrue()
        ->and(Schema::hasColumn('shelf_file_metadata', 'key'))->toBeTrue()
        ->and(Schema::hasColumn('shelf_file_metadata', 'value'))->toBeTrue();

});
