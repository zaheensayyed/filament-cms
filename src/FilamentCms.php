<?php

namespace zaheensayyed\FilamentCms;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use zaheensayyed\FilamentCms\Models\Navigation;
use zaheensayyed\FilamentCms\Models\Setting;

class FilamentCms
{
    public const SETTINGS_CACHE_KEY = 'filament-cms.settings';

    public static function getMenu($menu_name)
    {
        $navigation = Navigation::where('name', $menu_name)->first();
        if ($navigation) {
            return $navigation->items;
        }

        return [];
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
