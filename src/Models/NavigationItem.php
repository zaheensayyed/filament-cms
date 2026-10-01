<?php

namespace zaheensayyed\FilamentCms\Models;

use Closure;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use zaheensayyed\FilamentCms\FilamentCms;

class NavigationItem extends Model
{
    use HasFactory;

    const TYPE_PAGE = 'page';

    const TYPE_CATEGORY_LIST = 'category_list';

    const TYPE_GALLERY = 'gallery';

    const TYPE_CUSTOM_URL = 'custom_url';

    const TYPE_STATIC = 'static';

    public $fillable = [
        'navigation_id',
        'parent_id',
        'name',
        'slug',
        'level',
        'type',
        'type_id',
        'custom_url',
        'icon',
        'created_by',
        'updated_by',
    ];

    /**
     * @var array<string, Closure(NavigationItem): ?string>
     */
    protected static array $urlResolvers = [];

    protected static function booted(): void
    {
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

    public function childItems()
    {
        return $this->hasMany(NavigationItem::class, 'parent_id')->orderBy('id');
    }

    /**
     * The linked page. Only meaningful when type is "page" — use linkedPage() to get
     * null for every other type.
     */
    public function typePage()
    {
        return $this->belongsTo(Page::class, 'type_id');
    }

    /**
     * The linked gallery. Only meaningful when type is "gallery" — use linkedGallery()
     * to get null for every other type.
     */
    public function typeGallery()
    {
        return $this->belongsTo(Gallery::class, 'type_id');
    }

    /**
     * @deprecated Use typePage(). Kept for backward compatibility.
     */
    public function page()
    {
        return $this->typePage();
    }

    /**
     * @deprecated Use typeGallery(). Kept for backward compatibility.
     */
    public function gallery()
    {
        return $this->typeGallery();
    }

    public function linkedPage(): ?Page
    {
        return $this->type === self::TYPE_PAGE ? $this->typePage : null;
    }

    public function linkedGallery(): ?Gallery
    {
        return $this->type === self::TYPE_GALLERY ? $this->typeGallery : null;
    }

    public static function hasOptions($type)
    {
        return ($type == self::TYPE_CUSTOM_URL) ? false : true;
    }

    public function getTitleAttribute()
    {
        return $this->linkedPage()?->title
            ?? $this->linkedGallery()?->name
            ?? $this->name;
    }

    /**
     * Frontend href for this item, whatever its type:
     * - custom_url: the stored custom_url (relative paths are made absolute)
     * - page / gallery / category_list: the CMS route for the item's slug
     * - static: url($slug), for routes the consumer app defines itself
     *
     * Override per type with NavigationItem::resolveUrlUsing().
     */
    public function getUrlAttribute(): ?string
    {
        if (isset(static::$urlResolvers[$this->type])) {
            return call_user_func(static::$urlResolvers[$this->type], $this);
        }

        return match ($this->type) {
            self::TYPE_CUSTOM_URL => static::normalizeUrl($this->custom_url),
            self::TYPE_STATIC => url($this->slug),
            default => FilamentCms::url($this->slug),
        };
    }

    /**
     * @param  Closure(NavigationItem): ?string  $callback
     */
    public static function resolveUrlUsing(string $type, ?Closure $callback): void
    {
        if ($callback === null) {
            unset(static::$urlResolvers[$type]);

            return;
        }

        static::$urlResolvers[$type] = $callback;
    }

    protected static function normalizeUrl(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        // Absolute URLs, protocol-relative URLs, anchors, mailto:, tel: etc. are used as-is.
        if (Str::startsWith($url, ['#', '//']) || preg_match('/^[a-z][a-z0-9+.-]*:/i', $url)) {
            return $url;
        }

        return url($url);
    }

    public static function getAllTypeOptions()
    {
        return [
            NavigationItem::TYPE_PAGE => 'Page',
            NavigationItem::TYPE_CUSTOM_URL => 'Custom URL',
            NavigationItem::TYPE_GALLERY => 'Photo Gallery',
            NavigationItem::TYPE_STATIC => 'Static',
        ];
    }
}
