<?php

namespace zaheensayyed\FilamentCms;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use zaheensayyed\FilamentCms\Models\Client;
use zaheensayyed\FilamentCms\Models\Gallery;
use zaheensayyed\FilamentCms\Models\NavigationItem;
use zaheensayyed\FilamentCms\Models\Page;
use zaheensayyed\FilamentCms\Models\Post;
use zaheensayyed\FilamentCms\Models\Setting;
use zaheensayyed\FilamentCms\Repositories\MenuRepository;

class FilamentCms
{
    public const SETTINGS_CACHE_KEY = 'filament-cms.settings';

    public const MENU_CACHE_PREFIX = 'filament-cms.menu.';

    public const MENU_CACHE_INDEX = 'filament-cms.menu-keys';

    public const ROUTE_NAME = 'filament-cms.page';

    public const BLOG_INDEX_ROUTE = 'filament-cms.blog.index';

    public const BLOG_SHOW_ROUTE = 'filament-cms.blog.show';

    public const CLIENTS_INDEX_ROUTE = 'filament-cms.clients.index';

    /**
     * Top-level items of a menu, looked up by its stable key (or, for backward
     * compatibility, its display name). Each item has childItems and its linked
     * page/gallery loaded, so use $item->title and $item->url freely.
     *
     * Costs 2 queries the first time, then 0 until a menu, item, page or gallery changes.
     *
     * @return Collection<int, NavigationItem>|array{} [] when no menu matches
     */
    public static function getMenu(string $key): Collection | array
    {
        $cacheKey = static::MENU_CACHE_PREFIX . $key;

        $menu = Cache::get($cacheKey);

        if ($menu === null) {
            // false = "no such menu", cached too so a missing menu doesn't query on every request.
            $menu = MenuRepository::build($key) ?? false;

            Cache::forever($cacheKey, $menu);
            Cache::forever(static::MENU_CACHE_INDEX, array_unique([...Cache::get(static::MENU_CACHE_INDEX, []), $key]));
        }

        return $menu === false ? [] : $menu;
    }

    public static function forgetMenuCache(): void
    {
        foreach (Cache::get(static::MENU_CACHE_INDEX, []) as $key) {
            Cache::forget(static::MENU_CACHE_PREFIX . $key);
        }

        Cache::forget(static::MENU_CACHE_INDEX);
    }

    public static function getPage(string $slug): ?Page
    {
        return Page::where('slug', trim($slug, '/'))->first();
    }

    public static function getGallery(string $slug): ?Gallery
    {
        return Gallery::with('images')->where('slug', trim($slug, '/'))->first();
    }

    /**
     * What a frontend URL points to: a menu item's slug (e.g. "services/cctv") resolves to
     * its linked page or gallery; otherwise a page or gallery with that exact slug.
     */
    public static function resolveSlug(string $slug): Page | Gallery | null
    {
        $slug = trim($slug, '/');

        $item = NavigationItem::query()
            ->where('slug', $slug)
            ->whereIn('type', [NavigationItem::TYPE_PAGE, NavigationItem::TYPE_GALLERY])
            ->first();

        $target = match ($item?->type) {
            NavigationItem::TYPE_PAGE => Page::find($item->type_id),
            NavigationItem::TYPE_GALLERY => Gallery::with('images')->find($item->type_id),
            default => null,
        };

        return $target ?? static::getPage($slug) ?? static::getGallery($slug);
    }

    /**
     * Frontend URL for a CMS slug: the package's catch-all route when it is enabled,
     * otherwise url($slug).
     */
    public static function url(?string $slug): string
    {
        $slug = trim((string) $slug, '/');

        if ($slug !== '' && Route::has(static::ROUTE_NAME)) {
            return route(static::ROUTE_NAME, ['slug' => $slug]);
        }

        return url($slug);
    }

    /**
     * Published blog posts, newest first, paginated (config "filament-cms.blog.per_page").
     *
     * @return LengthAwarePaginator<Post>
     */
    public static function getPosts(?int $perPage = null): LengthAwarePaginator
    {
        return Post::published()
            ->with('createdBy')
            ->latest('published_at')
            ->latest('id')
            ->paginate($perPage ?? (int) config('filament-cms.blog.per_page', 9));
    }

    /**
     * The newest published posts, e.g. for a "Latest from the blog" section.
     *
     * @return Collection<int, Post>
     */
    public static function latestPosts(int $limit = 3): Collection
    {
        return Post::published()->with('createdBy')->latest('published_at')->latest('id')->take($limit)->get();
    }

    /**
     * A published post by slug; drafts and scheduled posts return null.
     */
    public static function getPost(string $slug): ?Post
    {
        return Post::published()->with('createdBy')->where('slug', trim($slug, '/'))->first();
    }

    /**
     * Frontend URL of the blog: the package's blog route when it is enabled, otherwise url(prefix).
     */
    public static function blogUrl(): string
    {
        if (Route::has(static::BLOG_INDEX_ROUTE)) {
            return route(static::BLOG_INDEX_ROUTE);
        }

        return url(trim((string) config('filament-cms.blog.prefix', 'blog'), '/'));
    }

    /**
     * Frontend URL of a post: the package's blog route when it is enabled, otherwise
     * url("{prefix}/{slug}").
     */
    public static function postUrl(Post | string $post): string
    {
        $slug = $post instanceof Post ? $post->slug : trim($post, '/');

        if (Route::has(static::BLOG_SHOW_ROUTE)) {
            return route(static::BLOG_SHOW_ROUTE, ['slug' => $slug]);
        }

        return url(trim(config('filament-cms.blog.prefix', 'blog') . '/' . $slug, '/'));
    }

    /**
     * Published clients in the panel's order.
     *
     * @return Collection<int, Client>
     */
    public static function getClients(): Collection
    {
        return Client::published()->ordered()->get();
    }

    /**
     * Published clients marked as featured, e.g. for the home page.
     *
     * @return Collection<int, Client>
     */
    public static function featuredClients(?int $limit = null): Collection
    {
        return Client::published()->featured()->ordered()->when($limit, fn ($query) => $query->take($limit))->get();
    }

    /**
     * A published client by slug; unpublished clients return null.
     */
    public static function getClient(string $slug): ?Client
    {
        return Client::published()->where('slug', trim($slug, '/'))->first();
    }

    /**
     * Frontend URL of the clients list: the package's clients route when it is enabled,
     * otherwise url(prefix).
     */
    public static function clientsUrl(): string
    {
        if (Route::has(static::CLIENTS_INDEX_ROUTE)) {
            return route(static::CLIENTS_INDEX_ROUTE);
        }

        return url(trim((string) config('filament-cms.clients.prefix', 'clients'), '/'));
    }

    /**
     * Read one setting, e.g. setting('company.email', 'info@example.com').
     * Returns $default when the key has never been saved.
     */
    public static function setting(string $key, mixed $default = null): mixed
    {
        return Arr::get(static::settings(), $key, $default);
    }

    /**
     * All settings grouped as ['company' => ['email' => ...], ...].
     * Loaded with a single query and cached until the next save.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function settings(): array
    {
        return Cache::rememberForever(static::SETTINGS_CACHE_KEY, function () {
            $settings = [];

            foreach (Setting::all() as $setting) {
                $settings[$setting->group][$setting->key] = $setting->value;
            }

            return $settings;
        });
    }

    /**
     * @param  array<string, array<string, mixed>>  $groups  ['company' => ['email' => ...], ...]
     */
    public static function saveSettings(array $groups): void
    {
        DB::transaction(function () use ($groups) {
            foreach ($groups as $group => $values) {
                foreach ($values as $key => $value) {
                    Setting::updateOrCreate(
                        ['group' => $group, 'key' => $key],
                        ['value' => $value],
                    );
                }
            }
        });

        static::forgetSettingsCache();
    }

    public static function forgetSettingsCache(): void
    {
        Cache::forget(static::SETTINGS_CACHE_KEY);
    }
}
