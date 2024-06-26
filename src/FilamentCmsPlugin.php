<?php

namespace zaheensayyed\FilamentCms;

use Filament\Contracts\Plugin;
use Filament\Panel;
use zaheensayyed\FilamentCms\Resources\GalleryResource;
use zaheensayyed\FilamentCms\Resources\NavigationResource;
use zaheensayyed\FilamentCms\Resources\PageResource;

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
                PageResource::class,
                GalleryResource::class,
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
