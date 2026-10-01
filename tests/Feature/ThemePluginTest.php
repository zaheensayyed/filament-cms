<?php

use Filament\Panel;
use Filament\Support\Colors\Color;
use zaheensayyed\FilamentCms\FilamentCmsPlugin;
use zaheensayyed\FilamentCms\FilamentCmsTheme;

it('registers next to the CMS plugin and sets the font and colours', function () {
    $panel = Panel::make()->id('themed')->plugins([
        FilamentCmsPlugin::make(),
        FilamentCmsTheme::make(),
    ]);

    expect($panel->getPlugin('filament-cms-theme'))->toBeInstanceOf(FilamentCmsTheme::class)
        ->and($panel->getPlugin('filament-cms'))->toBeInstanceOf(FilamentCmsPlugin::class)
        ->and($panel->getFontFamily())->toBe('DM Sans')
        ->and($panel->getColors()['primary'])->toBe(Color::Amber);
});
