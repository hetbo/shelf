<?php

namespace Hetbo\Shelf\Tests\Stubs;

use Hetbo\Shelf\Tests\Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{

    use HasFactory;

    protected $guarded = [];

    protected static function newFactory() {
        return UserFactory::new();
    }

}
