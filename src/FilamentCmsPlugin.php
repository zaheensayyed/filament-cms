<?php

namespace zaheensayyed\FilamentCms;

use Filament\Panel;
use Filament\Contracts\Plugin;
use zaheensayyed\FilamentCms\Resources\NavigationResource;

class FilamentCmsPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-cms';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                NavigationResource::class,
            ])
            ->pages([
                // Settings::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
