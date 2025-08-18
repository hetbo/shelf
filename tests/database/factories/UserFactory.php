<?php

namespace Hetbo\Shelf\Tests\Database\Factories;

use Hetbo\Shelf\Tests\Stubs\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory {

    protected $model = User::class;
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
        ];
    }
}