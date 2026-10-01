<?php

namespace zaheensayyed\FilamentCms\Tests\Fixtures;

use Filament\Panel;
use Filament\PanelProvider;
use zaheensayyed\FilamentCms\FilamentCmsPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->plugins([
                FilamentCmsPlugin::make(),
            ]);
    }
}
