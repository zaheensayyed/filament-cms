<?php

namespace zaheensayyed\FilamentCms\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use zaheensayyed\FilamentCms\FilamentCms;

/**
 * A client shown as a card on the clients page: logo, name and description. Featured clients
 * also appear on the home page. Hidden from the frontend while unpublished. There are no pages per
 * client; the slug is generated from the name and only identifies the client.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property ?string $description
 * @property ?string $logo
 * @property bool $is_featured
 * @property bool $is_published
 * @property int $sort_order
 * @property ?string $body
 * @property ?string $industry
 * @property ?string $location
 * @property ?string $website_url
 * @property int $created_by
 * @property ?int $updated_by
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 * @property-read ?Model $createdBy
 * @property-read ?Model $updatedBy
 * @property-read string $url
 * @property-read ?string $logo_url
 */
class Client extends Model
{
    use HasFactory;

    public $fillable = [
        'name',
        'slug',
        'description',
        'logo',
        'is_featured',
        'is_published',
        'sort_order',
        'body',
        'industry',
        'location',
        'website_url',
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

    protected $casts = [
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $attributes = [
        'is_featured' => false,
        'is_published' => true,
        'sort_order' => 0,
    ];

    protected static function booted(): void
    {
        static::saving(function (Client $client) {
            if (blank($client->slug)) {
                $client->slug = static::uniqueSlug($client->name);
            }
        });
    }

    /**
     * "Acme Towers" → "acme-towers", or "acme-towers-2" when that is taken.
     */
    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'client';
        $slug = $base;

        for ($i = 2; static::where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'updated_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * The panel's order (drag and drop), then by name.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * The clients page, where the client is one of the cards.
     */
    public function getUrlAttribute(): string
    {
        return FilamentCms::clientsUrl();
    }

    public function getLogoUrlAttribute(): ?string
    {
        return static::fileUrl($this->logo);
    }

    public static function fileUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return Storage::disk(config('filament.default_filesystem_disk'))->url($path);
    }
}
