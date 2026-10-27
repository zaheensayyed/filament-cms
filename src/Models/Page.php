<?php

namespace zaheensayyed\FilamentCms\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use zaheensayyed\FilamentCms\FilamentCms;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property ?string $description
 * @property ?string $icon
 * @property ?string $body
 * @property ?string $cover_image
 * @property ?string $meta_title
 * @property ?string $meta_description
 * @property ?string $canonical_url
 * @property ?string $robots
 * @property ?string $og_title
 * @property ?string $og_description
 * @property ?string $og_image
 * @property ?string $structured_data
 * @property int $created_by
 * @property ?int $updated_by
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 * @property-read ?Model $createdBy
 * @property-read ?Model $updatedBy
 * @property-read Collection<int, Gallery> $galleries
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
        // Its galleries stay (the foreign key does the same on databases that enforce it).
        static::deleted(fn (Page $page) => Gallery::where('page_id', $page->id)->update(['page_id' => null]));
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'updated_by');
    }

    /**
     * Galleries shown on this page (Galleries → Page in the panel), oldest first.
     */
    public function galleries(): HasMany
    {
        return $this->hasMany(Gallery::class, 'page_id')->oldest('id');
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
