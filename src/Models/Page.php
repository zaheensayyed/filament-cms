<?php

namespace zaheensayyed\FilamentCms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use zaheensayyed\FilamentCms\FilamentCms;

/**
 * @property-read ?string $cover_image_url
 */
class Page extends Model
{
    use HasFactory;

    public $fillable = [
        'title',
        'slug',
        'description',
        'icon',
        'body',
        'cover_image',
        'meta_title',
        'meta_description',
        'canonical_url',
        'robots',
        'og_title',
        'og_description',
        'og_image',
        'structured_data',
        'created_by',
        'updated_by',
    ];

    protected static function booted(): void
    {
        // Menu titles/links come from pages and galleries, so cached menus must be rebuilt.
        static::saved(fn () => FilamentCms::forgetMenuCache());
        static::deleted(fn () => FilamentCms::forgetMenuCache());
    }

    public function createdBy()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'updated_by');
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        if (blank($this->cover_image)) {
            return null;
        }

        if (Str::startsWith($this->cover_image, ['http://', 'https://'])) {
            return $this->cover_image;
        }

        return Storage::disk(config('filament.default_filesystem_disk'))->url($this->cover_image);
    }
}
