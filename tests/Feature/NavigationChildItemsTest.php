<?php

use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Livewire\Livewire;
use zaheensayyed\FilamentCms\Facades\FilamentCms;
use zaheensayyed\FilamentCms\Models\Navigation;
use zaheensayyed\FilamentCms\Models\NavigationItem;
use zaheensayyed\FilamentCms\Models\Page;
use zaheensayyed\FilamentCms\Resources\NavigationResource\Pages\EditNavigation;
use zaheensayyed\FilamentCms\Resources\NavigationResource\RelationManagers\ItemsRelationManager;

/**
 * A "Products" item with two child items, as the panel saves them.
 *
 * @return array{0: Navigation, 1: NavigationItem, 2: NavigationItem, 3: NavigationItem}
 */
function menuWithChildren(): array
{
    $page = Page::create(['title' => 'Products', 'slug' => 'products', 'created_by' => 1]);
    $navigation = Navigation::create(['key' => 'main-menu', 'name' => 'Main Menu', 'description' => '-', 'created_by' => 1]);

    $parent = NavigationItem::create(['navigation_id' => $navigation->id, 'name' => 'Products', 'slug' => 'products', 'level' => 1, 'type' => 'page', 'type_id' => $page->id, 'created_by' => 1]);
    $child = fn (string $name, string $slug) => NavigationItem::create(['navigation_id' => $navigation->id, 'parent_id' => $parent->id, 'name' => $name, 'slug' => "products/{$slug}", 'level' => 2, 'type' => 'page', 'type_id' => $page->id, 'created_by' => 1]);

    return [$navigation, $parent, $child('CCTV', 'cctv'), $child('Fire Alarm', 'fire-alarm')];
}

function itemsTable(Navigation $navigation)
{
    return Livewire::test(ItemsRelationManager::class, ['ownerRecord' => $navigation, 'pageClass' => EditNavigation::class]);
}

it('deletes a child item removed from the parent form', function () {
    [$navigation, $parent, $cctv, $fire] = menuWithChildren();

    $table = itemsTable($navigation)->mountTableAction(EditAction::class, $parent);

    // Remove "CCTV" from the repeater, as the trash button in the panel does.
    $state = $table->get('mountedTableActionsData.0.child_menu_items');
    $key = collect($state)->search(fn (array $item) => $item['child_name'] === 'CCTV');
    unset($state[$key]);

    $table->set('mountedTableActionsData.0.child_menu_items', $state)
        ->callMountedTableAction()
        ->assertHasNoTableActionErrors();

    expect(NavigationItem::where('parent_id', $parent->id)->pluck('name')->all())->toBe(['Fire Alarm'])
        ->and(NavigationItem::find($cctv->id))->toBeNull()
        ->and(FilamentCms::getMenu('main-menu')->sole()->childItems->pluck('name')->all())->toBe(['Fire Alarm']);
});

it('keeps the child items as they are when the parent is saved unchanged', function () {
    [$navigation, $parent, $cctv, $fire] = menuWithChildren();

    itemsTable($navigation)
        ->mountTableAction(EditAction::class, $parent)
        ->callMountedTableAction()
        ->assertHasNoTableActionErrors();

    expect(NavigationItem::where('parent_id', $parent->id)->orderBy('id')->pluck('id')->all())->toBe([$cctv->id, $fire->id]);
});

it('deletes every child item when all are removed', function () {
    [$navigation, $parent] = menuWithChildren();

    itemsTable($navigation)
        ->mountTableAction(EditAction::class, $parent)
        ->set('mountedTableActionsData.0.child_menu_items', [])
        ->callMountedTableAction()
        ->assertHasNoTableActionErrors();

    expect(NavigationItem::where('parent_id', $parent->id)->count())->toBe(0);
});

it('deletes the child items with their parent', function () {
    [$navigation, $parent] = menuWithChildren();

    itemsTable($navigation)->callTableAction(DeleteAction::class, $parent);

    expect(NavigationItem::count())->toBe(0);
});

it('deletes the child items of parents deleted in bulk', function () {
    [$navigation, $parent] = menuWithChildren();

    itemsTable($navigation)->callTableBulkAction(DeleteBulkAction::class, [$parent]);

    expect(NavigationItem::count())->toBe(0);
});
