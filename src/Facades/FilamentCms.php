<?php

namespace zaheensayyed\FilamentCms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Database\Eloquent\Collection|array getMenu(string $key)
 * @method static void forgetMenuCache()
 * @method static \zaheensayyed\FilamentCms\Models\Page|null getPage(string $slug)
 * @method static \zaheensayyed\FilamentCms\Models\Gallery|null getGallery(string $slug)
 * @method static \zaheensayyed\FilamentCms\Models\Page|\zaheensayyed\FilamentCms\Models\Gallery|null resolveSlug(string $slug)
 * @method static string url(?string $slug)
 * @method static mixed setting(string $key, mixed $default = null)
 * @method static array settings()
 * @method static void saveSettings(array $groups)
 * @method static void forgetSettingsCache()
 *
 * @see \zaheensayyed\FilamentCms\FilamentCms
 */
class FilamentCms extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \zaheensayyed\FilamentCms\FilamentCms::class;
    }
}
