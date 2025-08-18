<?php

namespace Hetbo\Shelf\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileMetadata extends Model
{
    protected $table = 'shelf_file_metadata';

    protected $fillable = [
        'file_id',
        'key',
        'value',
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function getValue(): mixed
    {
        // Try to decode JSON, fall back to string value
        $decoded = json_decode($this->value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $this->value;
    }

    public function setValue(mixed $value): void
    {
        $this->value = is_string($value) ? $value : json_encode($value);
    }
}