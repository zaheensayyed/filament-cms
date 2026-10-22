<?php

use Livewire\Livewire;
use zaheensayyed\FilamentCms\Models\Navigation;
use zaheensayyed\FilamentCms\Resources\NavigationResource;
use zaheensayyed\FilamentCms\Resources\NavigationResource\Pages\EditNavigation;
use zaheensayyed\FilamentCms\Resources\NavigationResource\Pages\ListNavigations;

it('creates the main and footer menus when the list opens', function () {
    Livewire::test(ListNavigations::class)->assertOk();

    expect(Navigation::orderBy('id')->pluck('name', 'key')->all())->toBe([
        'main-menu' => 'Main Menu',
        'footer-menu' => 'Footer Menu',
    ]);

    Livewire::test(ListNavigations::class);

    expect(Navigation::count())->toBe(2);
});

it('gives a navigation saved without a key its key instead of duplicating it', function () {
    $navigation = Navigation::create(['name' => 'Main Menu', 'description' => '-', 'created_by' => 1]);

    Navigation::ensureFixed();

    expect($navigation->fresh()->key)->toBe('main-menu')
        ->and(Navigation::count())->toBe(2);
});

it('does not let navigations be created or deleted', function () {
    $navigation = Navigation::create(['key' => 'main-menu', 'name' => 'Main Menu', 'description' => '-', 'created_by' => 1]);

    expect(NavigationResource::canCreate())->toBeFalse()
        ->and(NavigationResource::canDelete($navigation))->toBeFalse()
        ->and(NavigationResource::canDeleteAny())->toBeFalse()
        ->and(NavigationResource::getPages())->not->toHaveKey('create');

    Livewire::test(EditNavigation::class, ['record' => $navigation->getRouteKey()])
        ->assertActionDoesNotExist('delete');
});

it('reads the navigations from config', function () {
    config(['filament-cms.navigations' => ['header' => 'Header']]);

    Navigation::ensureFixed();

    expect(Navigation::pluck('name', 'key')->all())->toBe(['header' => 'Header']);
});
