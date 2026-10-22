<?php

namespace zaheensayyed\FilamentCms\Seo;

use Closure;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use zaheensayyed\FilamentCms\FilamentCms;
use zaheensayyed\FilamentCms\Models\Page;
use zaheensayyed\FilamentCms\Models\Post;

/**
 * Resolves every SEO value for a page or blog post (or for a non-CMS route when $page is null).
 *
 * Each value follows the same chain: page/post field → "SEO Defaults" setting → hardcoded fallback.
 * Blog posts also fall back to their excerpt (description) and cover image (share image).
 */
class SeoMeta
{
    public const DEFAULT_TITLE_PATTERN = '{title} | {site_name}';

    public const DEFAULT_ROBOTS = 'index,follow';

    public const TWITTER_CARD = 'summary_large_image';

    public const ROBOTS_OPTIONS = [
        'index,follow' => 'index, follow',
        'noindex,follow' => 'noindex, follow',
        'index,nofollow' => 'index, nofollow',
        'noindex,nofollow' => 'noindex, nofollow',
    ];

    protected static ?Closure $pageUrlResolver = null;

    public function __construct(protected Page | Post | null $page = null) {}

    /**
     * Tell the package where CMS pages live on the frontend, e.g.
     * SeoMeta::resolvePageUrlUsing(fn (Page $page) => route('pages.show', $page->slug)).
     * Defaults to FilamentCms::url($page->slug) (the catch-all route when enabled).
     */
    public static function resolvePageUrlUsing(?Closure $callback): void
    {
        static::$pageUrlResolver = $callback;
    }

    public static function applyTitlePattern(string $pattern, string $title, ?string $siteName): string
    {
        if (blank($siteName)) {
            return $title;
        }

        return trim(strtr($pattern, [
            '{title}' => $title,
            '{site_name}' => $siteName,
        ]));
    }

    public function siteName(): string
    {
        return $this->setting('site_name') ?? (string) config('app.name');
    }

    public function title(): string
    {
        if ($metaTitle = $this->pageValue('meta_title')) {
            return $metaTitle;
        }

        $title = $this->pageValue('title');

        if ($title === null) {
            return $this->siteName();
        }

        return static::applyTitlePattern(
            $this->setting('title_pattern') ?? static::DEFAULT_TITLE_PATTERN,
            $title,
            $this->siteName(),
        );
    }

    public function description(): ?string
    {
        return $this->pageValue('meta_description')
            ?? ($this->page instanceof Post ? $this->pageValue('excerpt') : null)
            ?? $this->setting('default_description');
    }

    public function canonical(): string
    {
        if ($canonical = $this->pageValue('canonical_url')) {
            return $canonical;
        }

        if ($this->page === null) {
            return url()->current();
        }

        if ($this->page instanceof Post) {
            return FilamentCms::postUrl($this->page);
        }

        return static::$pageUrlResolver
            ? (string) call_user_func(static::$pageUrlResolver, $this->page)
            : FilamentCms::url($this->page->slug);
    }

    public function robots(): string
    {
        return $this->pageValue('robots') ?? $this->setting('default_robots') ?? static::DEFAULT_ROBOTS;
    }

    public function ogTitle(): string
    {
        return $this->pageValue('og_title')
            ?? $this->pageValue('meta_title')
            ?? $this->pageValue('title')
            ?? $this->siteName();
    }

    public function ogDescription(): ?string
    {
        return $this->pageValue('og_description') ?? $this->description();
    }

    public function ogImage(): ?string
    {
        $path = $this->pageValue('og_image')
            ?? ($this->page instanceof Post ? $this->pageValue('cover_image') : null)
            ?? $this->setting('default_og_image');

        if ($path === null) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return url(Storage::disk(config('filament.default_filesystem_disk'))->url($path));
    }

    public function ogType(): string
    {
        return $this->page ? 'article' : 'website';
    }

    /**
     * ISO 8601 publish date of a blog post (article:published_time), null for pages.
     */
    public function publishedTime(): ?string
    {
        return $this->page instanceof Post ? $this->page->published_at?->toIso8601String() : null;
    }

    public function twitterCard(): string
    {
        return static::TWITTER_CARD;
    }

    /**
     * Re-encoded JSON-LD that is safe to print inside a <script> tag, or null when missing/invalid.
     * A blog post without its own gets a schema.org BlogPosting.
     */
    public function structuredData(): ?string
    {
        $json = $this->pageValue('structured_data');

        if ($json === null && $this->page instanceof Post) {
            $json = json_encode($this->blogPosting());
        }

        if ($json === null) {
            return null;
        }

        $data = json_decode($json);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return json_encode(
            $data,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function blogPosting(): array
    {
        /** @var Post $post */
        $post = $this->page;

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $this->pageValue('title'),
            'description' => $this->description(),
            'image' => $this->ogImage(),
            'datePublished' => $this->publishedTime(),
            'dateModified' => $post->updated_at?->toIso8601String(),
            'author' => ($name = $post->createdBy?->getAttribute('name')) ? ['@type' => 'Person', 'name' => $name] : null,
            'publisher' => ['@type' => 'Organization', 'name' => $this->siteName()],
            'mainEntityOfPage' => $this->canonical(),
        ]);
    }

    protected function pageValue(string $attribute): ?string
    {
        $value = $this->page?->getAttribute($attribute);

        return filled($value) ? (string) $value : null;
    }

    protected function setting(string $key): ?string
    {
        $value = FilamentCms::setting("seo.{$key}");

        return filled($value) ? (string) $value : null;
    }
}
