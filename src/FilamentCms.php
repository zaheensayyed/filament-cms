<?php

namespace zaheensayyed\FilamentCms;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use zaheensayyed\FilamentCms\Models\Gallery;
use zaheensayyed\FilamentCms\Models\NavigationItem;
use zaheensayyed\FilamentCms\Models\Page;
use zaheensayyed\FilamentCms\Models\Setting;
use zaheensayyed\FilamentCms\Repositories\MenuRepository;

class FilamentCms
{
    public const SETTINGS_CACHE_KEY = 'filament-cms.settings';

    public const MENU_CACHE_PREFIX = 'filament-cms.menu.';

    public const MENU_CACHE_INDEX = 'filament-cms.menu-keys';

    public const ROUTE_NAME = 'filament-cms.page';

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
