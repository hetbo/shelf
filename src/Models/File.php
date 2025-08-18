<?php

namespace Hetbo\Shelf\Models;

use Hetbo\Shelf\Database\Factories\FileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'shelf_files';

    protected $fillable = [
        'filename',
        'path',
        'disk',
        'mime_type',
        'size',
        'hash',
        'user_id',
        'folder_id',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'folder_id');
    }

    public function metadata(): HasMany
    {
        return $this->hasMany(FileMetadata::class);
    }

    public function fileables(): HasMany
    {
        return $this->hasMany(Fileable::class);
    }

    public function getUrl(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function exists(): bool
    {
        return Storage::disk($this->disk)->exists($this->path);
    }

    public function getContents(): string
    {
        return Storage::disk($this->disk)->get($this->path);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'video/');
    }

    public function isAudio(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'audio/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function getHumanReadableSize(): string
    {
        if (!$this->size) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = $this->size;
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    protected static function newFactory()
    {
        return FileFactory::new();
    }
}