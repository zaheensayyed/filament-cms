<?php

use Filament\Forms\Components\Repeater;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Livewire\Livewire;
use zaheensayyed\FilamentCms\Facades\FilamentCms;
use zaheensayyed\FilamentCms\Models\Navigation;
use zaheensayyed\FilamentCms\Models\NavigationItem;
use zaheensayyed\FilamentCms\Models\Page;
use zaheensayyed\FilamentCms\Resources\NavigationResource\Pages\EditNavigation;
use zaheensayyed\FilamentCms\Resources\NavigationResource\RelationManagers\ItemsRelationManager;
use zaheensayyed\FilamentCms\Support\Heroicons;

function iconsMenu(): array
{
    $page = Page::create(['title' => 'Services', 'slug' => 'services', 'body' => '<p>-</p>', 'created_by' => 1]);
    $navigation = Navigation::create(['key' => 'main-menu', 'name' => 'Main Menu', 'description' => '-', 'created_by' => 1]);

    return [$navigation, $page];
}

function childItemData(Page $page, array $attributes = []): array
{
    return [
        'child_name' => 'CCTV',
        'child_slug' => 'cctv',
        'child_type' => 'page',
        'child_type_id' => $page->id,
        ...$attributes,
    ];
}

describe('Heroicons list', function () {
    it('includes every Heroicon SVG in all four styles', function () {
        $files = glob(Heroicons::path() . '/*.svg');

        expect($files)->not->toBeEmpty()
            ->and(Heroicons::options())->toHaveCount(count($files))
            ->and(Heroicons::options())->toHaveKeys([
                'heroicon-o-home', 'heroicon-s-home', 'heroicon-m-home', 'heroicon-c-home',
            ])
            ->and(Heroicons::options()['heroicon-o-home'])->toBe('Home (Outline)')
            ->and(Heroicons::options()['heroicon-c-arrow-right'])->toBe('Arrow Right (Micro)');
    });

    it('only lists icons that render', function () {
        foreach (Heroicons::names() as $icon) {
            expect(svg($icon)->toHtml())->toContain('<svg');
        }
    });

    it('searches by every word of the query', function () {
        expect(Heroicons::search('home'))->toHaveKeys(['heroicon-o-home', 'heroicon-s-home'])
            ->and(array_keys(Heroicons::search('home solid')))->toContain('heroicon-s-home')->not->toContain('heroicon-o-home')
            ->and(Heroicons::search('no-such-icon'))->toBe([])
            ->and(Heroicons::search(null, 10))->toHaveCount(10);
    });

    it('builds a preview label with the SVG', function () {
        expect(Heroicons::previewLabel('heroicon-o-home'))->toContain('<svg')->toContain('Home (Outline)')
            ->and(Heroicons::previewLabel('heroicon-o-not-real'))->toBeNull()
            ->and(Heroicons::previewLabel(null))->toBeNull();
    });
});

describe('child navigation item icons', function () {
    beforeEach(fn () => $this->undoRepeaterFake = Repeater::fake());
    afterEach(fn () => ($this->undoRepeaterFake)());

    it('saves the icon chosen for a child item', function () {
        [$navigation, $page] = iconsMenu();

        Livewire::test(ItemsRelationManager::class, ['ownerRecord' => $navigation, 'pageClass' => EditNavigation::class])
            ->callTableAction(CreateAction::class, data: [
                'name' => 'Services', 'slug' => 'services', 'type' => 'page', 'type_id' => $page->id,
                'child_menu_items' => [childItemData($page, ['child_icon' => 'heroicon-o-video-camera'])],
            ])
            ->assertHasNoTableActionErrors();

        expect(NavigationItem::where('level', 2)->sole()->icon)->toBe('heroicon-o-video-camera');
    });

    it('leaves the icon empty when none is chosen', function () {
        [$navigation, $page] = iconsMenu();

        Livewire::test(ItemsRelationManager::class, ['ownerRecord' => $navigation, 'pageClass' => EditNavigation::class])
            ->callTableAction(CreateAction::class, data: [
                'name' => 'Services', 'slug' => 'services', 'type' => 'page', 'type_id' => $page->id,
                'child_menu_items' => [childItemData($page)],
            ])
            ->assertHasNoTableActionErrors();

        expect(NavigationItem::where('level', 2)->sole()->icon)->toBeNull();
    });

    it('rejects names that are not Heroicons', function () {
        [$navigation, $page] = iconsMenu();

        Livewire::test(ItemsRelationManager::class, ['ownerRecord' => $navigation, 'pageClass' => EditNavigation::class])
            ->callTableAction(CreateAction::class, data: [
                'name' => 'Services', 'slug' => 'services', 'type' => 'page', 'type_id' => $page->id,
                'child_menu_items' => [childItemData($page, ['child_icon' => 'heroicon-o-not-real'])],
            ])
            ->assertHasTableActionErrors();

        expect(NavigationItem::count())->toBe(0);
    });

    it('fills and updates the icon when editing', function () {
        [$navigation, $page] = iconsMenu();

        $parent = NavigationItem::create(['navigation_id' => $navigation->id, 'name' => 'Services', 'slug' => 'services', 'level' => 1, 'type' => 'page', 'type_id' => $page->id, 'created_by' => 1]);
        $child = NavigationItem::create(['navigation_id' => $navigation->id, 'parent_id' => $parent->id, 'name' => 'CCTV', 'slug' => 'services/cctv', 'level' => 2, 'type' => 'page', 'type_id' => $page->id, 'icon' => 'heroicon-o-home', 'created_by' => 1]);

        Livewire::test(ItemsRelationManager::class, ['ownerRecord' => $navigation, 'pageClass' => EditNavigation::class])
            ->mountTableAction(EditAction::class, $parent)
            ->assertTableActionDataSet(fn (array $data) => data_get($data, 'child_menu_items.0.child_icon') === 'heroicon-o-home')
            ->setTableActionData(['child_menu_items' => [childItemData($page, ['child_id' => $child->id, 'child_icon' => 'heroicon-s-camera'])]])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        expect($child->refresh()->icon)->toBe('heroicon-s-camera');
    });

    it('exposes the icon on menu items', function () {
        [$navigation, $page] = iconsMenu();

        $parent = NavigationItem::create(['navigation_id' => $navigation->id, 'name' => 'Services', 'slug' => 'services', 'level' => 1, 'type' => 'page', 'type_id' => $page->id, 'created_by' => 1]);
        NavigationItem::create(['navigation_id' => $navigation->id, 'parent_id' => $parent->id, 'name' => 'CCTV', 'slug' => 'services/cctv', 'level' => 2, 'type' => 'page', 'type_id' => $page->id, 'icon' => 'heroicon-o-video-camera', 'created_by' => 1]);

        $child = FilamentCms::getMenu('main-menu')->first()->childItems->first();

        expect($child->icon)->toBe('heroicon-o-video-camera')
            ->and(svg($child->icon)->toHtml())->toContain('<svg');
    });
});
