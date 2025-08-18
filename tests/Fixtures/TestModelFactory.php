<?php

namespace Hetbo\Shelf\Tests\Fixtures;

use Illuminate\Database\Eloquent\Factories\Factory;

class TestModelFactory extends Factory
{
    protected $model = TestModel::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
        ];
    }
}