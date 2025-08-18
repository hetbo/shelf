<?php

namespace Hetbo\Shelf\Database\Factories;

use Hetbo\Shelf\Models\File;
use Hetbo\Shelf\Models\Folder;
use Illuminate\Database\Eloquent\Factories\Factory;

class FileFactory extends Factory
{
    protected $model = File::class;

    public function definition(): array
    {
        $filename = $this->faker->word() . '.' . $this->faker->randomElement(['jpg', 'png', 'pdf', 'docx', 'txt']);

        return [
            'filename' => $filename,
            'path' => 'uploads/' . $filename,
            'disk' => 'public',
            'mime_type' => $this->getMimeTypeForExtension(pathinfo($filename, PATHINFO_EXTENSION)),
            'size' => $this->faker->numberBetween(1024, 5242880), // 1KB to 5MB
            'hash' => $this->faker->sha256(),
            'user_id' => $this->faker->numberBetween(1, 10),
            'folder_id' => null,
        ];
    }

    public function inFolder(Folder $folder): static
    {
        return $this->state([
            'folder_id' => $folder->id,
        ]);
    }

    public function image(): static
    {
        return $this->state([
            'filename' => $this->faker->word() . '.jpg',
            'mime_type' => 'image/jpeg',
        ]);
    }

    public function pdf(): static
    {
        return $this->state([
            'filename' => $this->faker->word() . '.pdf',
            'mime_type' => 'application/pdf',
        ]);
    }

    public function withUser(int $userId): static
    {
        return $this->state([
            'user_id' => $userId,
        ]);
    }

    private function getMimeTypeForExtension(string $extension): string
    {
        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'txt' => 'text/plain',
            'mp4' => 'video/mp4',
            'mp3' => 'audio/mpeg',
            default => 'application/octet-stream',
        };
    }
}