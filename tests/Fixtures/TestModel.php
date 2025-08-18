<?php

namespace Hetbo\Shelf\Tests\Fixtures;

use Hetbo\Shelf\Traits\HasFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    use HasFactory, HasFiles;

    protected $table = 'test_models';

    protected $fillable = [
        'name',
        'description',
    ];

    protected static function newFactory()
    {
        return TestModelFactory::new();
    }
}