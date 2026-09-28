<?php

namespace zaheensayyed\FilamentCms;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Colors\Color;

/**
 * Optional panel look: DM Sans font and the CMS colour palette.
 *
 * ->plugins([FilamentCmsPlugin::make(), FilamentCmsTheme::make()])
 */
class FilamentCmsTheme implements Plugin
{
    public function getId(): string
    {
        return 'filament-cms-theme';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->font('DM Sans')
            ->colors([
                'primary' => Color::Amber,
                'gray' => Color::Gray,
                'warning' => Color::Amber,
                'danger' => Color::Rose,
                'success' => Color::Green,
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
}
