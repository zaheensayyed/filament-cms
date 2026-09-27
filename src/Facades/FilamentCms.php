<?php

namespace zaheensayyed\FilamentCms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed getMenu(string $menu_name)
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
