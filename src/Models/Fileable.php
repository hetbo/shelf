<?php

namespace Hetbo\Shelf\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Fileable extends Model
{
    protected $table = 'shelf_fileables';

    protected $fillable = [
        'file_id',
        'fileable_type',
        'fileable_id',
        'role',
        'order',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'order' => 'integer',
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getMetadata(string $key, mixed $default = null): mixed
    {
        return data_get($this->metadata, $key, $default);
    }

    public function setMetadata(string $key, mixed $value): void
    {
        $metadata = $this->metadata ?? [];
        data_set($metadata, $key, $value);
        $this->metadata = $metadata;
    }

    public function hasMetadata(string $key): bool
    {
        return data_get($this->metadata, $key) !== null;
    }
}