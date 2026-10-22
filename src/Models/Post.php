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
 * A blog post. Visible on the frontend once it is published and its publish date has passed.
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property ?string $excerpt
 * @property ?string $body
 * @property ?string $cover_image
 * @property string $status
 * @property ?Carbon $published_at
 * @property int $created_by
 * @property ?int $updated_by
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 * @property-read ?Model $createdBy
 * @property-read ?Model $updatedBy
 * @property-read bool $is_published
 * @property-read string $url
 * @property-read ?string $cover_image_url
 * @property-read string $summary
 * @property-read int $reading_time
 */
class Post extends Model
{
    use HasFactory;

    const STATUS_DRAFT = 'draft';

    const STATUS_PUBLISHED = 'published';

    const STATUSES = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_PUBLISHED => 'Published',
    ];

    protected $table = 'blog_posts';

    public $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'cover_image',
        'status',
        'published_at',
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
        'published_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_DRAFT,
    ];

    protected static function booted(): void
    {
        // Publishing without a date publishes now.
        static::saving(function (Post $post) {
            if ($post->status === self::STATUS_PUBLISHED && $post->published_at === null) {
                $post->published_at = now();
            }
        });
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
     * Posts the frontend shows: published, with a publish date that has passed.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function getIsPublishedAttribute(): bool
    {
        return $this->status === self::STATUS_PUBLISHED
            && $this->published_at !== null
            && $this->published_at->lte(now());
    }

    /**
     * Frontend URL of the post (see FilamentCms::postUrl()).
     */
    public function getUrlAttribute(): string
    {
        return FilamentCms::postUrl($this);
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

    /**
     * The excerpt, or the start of the body as plain text when there is none.
     */
    public function getSummaryAttribute(): string
    {
        if (filled($this->excerpt)) {
            return $this->excerpt;
        }

        return Str::limit(Str::squish(strip_tags((string) $this->body)), 200);
    }

    /**
     * Minutes to read the body at 200 words a minute (at least 1).
     */
    public function getReadingTimeAttribute(): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags((string) $this->body)) / 200));
    }
}
