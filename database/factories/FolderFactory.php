<?php

namespace Hetbo\Shelf\Database\Factories;

use Hetbo\Shelf\Models\Folder;
use Illuminate\Database\Eloquent\Factories\Factory;

class FolderFactory extends Factory
{
    protected $model = Folder::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'parent_id' => null,
            'user_id' => $this->faker->numberBetween(1, 10),
        ];
    }

    public function withParent(Folder $parent): static
    {
        return $this->state([
            'parent_id' => $parent->id,
        ]);
    }

    public function withUser(int $userId): static
    {
        return $this->state([
            'user_id' => $userId,
        ]);
    }
}