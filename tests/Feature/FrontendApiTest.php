<?php

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use zaheensayyed\FilamentCms\Facades\FilamentCms;
use zaheensayyed\FilamentCms\Http\Controllers\CmsPageController;
use zaheensayyed\FilamentCms\Models\Gallery;
use zaheensayyed\FilamentCms\Models\GalleryImage;
use zaheensayyed\FilamentCms\Models\Navigation;
use zaheensayyed\FilamentCms\Models\NavigationItem;
use zaheensayyed\FilamentCms\Models\Page;
use zaheensayyed\FilamentCms\Resources\NavigationResource\Pages\EditNavigation;
use zaheensayyed\FilamentCms\Resources\PageResource\Pages\EditPage;

function makePage(string $title, string $slug): Page
{
    return Page::create(['title' => $title, 'slug' => $slug, 'body' => "<p>{$title} body</p>", 'created_by' => 1]);
}

function makeItem(Navigation $menu, array $attributes): NavigationItem
{
    return NavigationItem::create([
        'navigation_id' => $menu->id,
        'level' => isset($attributes['parent_id']) ? 2 : 1,
        'created_by' => 1,
        ...$attributes,
    ]);
}

/**
 * Main Menu: Home (page), Services (page) → [CCTV (page), Photos (gallery)], Blog (custom_url), Careers (static).
 */
function seedMainMenu(): array
{
    $home = makePage('Home', 'home');
    $services = makePage('Our Services', 'services');
    $cctv = makePage('CCTV Installation', 'cctv');
    $gallery = Gallery::create(['name' => 'Project Photos', 'slug' => 'project-photos', 'created_by' => 1]);
    GalleryImage::create(['gallery_id' => $gallery->id, 'image_name' => 'project-photos/one.jpg', 'created_by' => 1]);

    $menu = Navigation::create(['key' => 'main-menu', 'name' => 'Main Menu', 'description' => 'Header', 'created_by' => 1]);

    makeItem($menu, ['name' => 'Home', 'slug' => 'home', 'type' => 'page', 'type_id' => $home->id]);
    $parent = makeItem($menu, ['name' => 'Services', 'slug' => 'services', 'type' => 'page', 'type_id' => $services->id]);
    makeItem($menu, ['name' => 'CCTV', 'slug' => 'services/cctv', 'type' => 'page', 'type_id' => $cctv->id, 'parent_id' => $parent->id]);
    makeItem($menu, ['name' => 'Photos', 'slug' => 'services/photos', 'type' => 'gallery', 'type_id' => $gallery->id, 'parent_id' => $parent->id]);
    makeItem($menu, ['name' => 'Blog', 'slug' => 'blog', 'type' => 'custom_url', 'custom_url' => 'https://blog.kbi.test']);
    makeItem($menu, ['name' => 'Careers', 'slug' => 'careers', 'type' => 'static', 'type_id' => 'static']);

    return compact('menu', 'home', 'services', 'cctv', 'gallery');
}

/**
 * Touches everything a menu Blade partial would use.
 */
function renderMenu($items): array
{
    return collect($items)->map(fn (NavigationItem $item) => [
        'title' => $item->title,
        'url' => $item->url,
        'children' => $item->childItems->map(fn (NavigationItem $child) => [$child->title, $child->url])->all(),
    ])->all();
}

describe('getMenu', function () {
    it('renders a nested menu in at most 2 queries cold and 0 warm', function () {
        seedMainMenu();

        DB::enableQueryLog();
        $cold = renderMenu(FilamentCms::getMenu('main-menu'));
        expect(DB::getQueryLog())->toHaveCount(2);

        DB::flushQueryLog();
        $warm = renderMenu(FilamentCms::getMenu('main-menu'));
        expect(DB::getQueryLog())->toHaveCount(0)
            ->and($warm)->toEqual($cold);
    });

    it('returns titles, links and children', function () {
        seedMainMenu();

        expect(renderMenu(FilamentCms::getMenu('main-menu')))->toEqual([
            ['title' => 'Home', 'url' => url('home'), 'children' => []],
            ['title' => 'Our Services', 'url' => url('services'), 'children' => [
                ['CCTV Installation', url('services/cctv')],
                ['Project Photos', url('services/photos')],
            ]],
            ['title' => 'Blog', 'url' => 'https://blog.kbi.test', 'children' => []],
            ['title' => 'Careers', 'url' => url('careers'), 'children' => []],
        ]);
    });

    it('still finds a menu by its display name', function () {
        seedMainMenu();

        expect(FilamentCms::getMenu('Main Menu'))->toHaveCount(4);
    });

    it('returns an empty array for an unknown menu and caches the miss', function () {
        expect(FilamentCms::getMenu('nope'))->toBe([]);

        DB::enableQueryLog();
        expect(FilamentCms::getMenu('nope'))->toBe([])
            ->and(DB::getQueryLog())->toHaveCount(0);
    });

    it('rebuilds the cached menu when an item, page or navigation changes', function () {
        ['menu' => $menu, 'home' => $home] = seedMainMenu();
        FilamentCms::getMenu('main-menu');

        $home->update(['title' => 'Start']);
        expect(FilamentCms::getMenu('main-menu')->first()->title)->toBe('Start');

        makeItem($menu, ['name' => 'Contact', 'slug' => 'contact', 'type' => 'static', 'type_id' => 'static']);
        expect(FilamentCms::getMenu('main-menu'))->toHaveCount(5);

        NavigationItem::where('slug', 'blog')->first()->delete();
        expect(FilamentCms::getMenu('main-menu'))->toHaveCount(4);
    });
});

describe('NavigationItem', function () {
    it('builds hrefs for every type', function () {
        $menu = Navigation::create(['name' => 'Footer', 'description' => '-', 'created_by' => 1]);

        $href = fn (array $attributes) => makeItem($menu, ['name' => 'x', 'slug' => 'x', ...$attributes])->url;

        expect($href(['type' => 'custom_url', 'custom_url' => 'https://example.com/a']))->toBe('https://example.com/a')
            ->and($href(['type' => 'custom_url', 'custom_url' => '/downloads/brochure.pdf']))->toBe(url('downloads/brochure.pdf'))
            ->and($href(['type' => 'custom_url', 'custom_url' => 'mailto:hello@kbi.test']))->toBe('mailto:hello@kbi.test')
            ->and($href(['type' => 'custom_url', 'custom_url' => '#contact']))->toBe('#contact')
            ->and($href(['type' => 'custom_url', 'custom_url' => null]))->toBeNull()
            ->and($href(['type' => 'static', 'slug' => 'careers']))->toBe(url('careers'))
            ->and($href(['type' => 'page', 'slug' => 'about/team']))->toBe(url('about/team'));
    });

    it('lets the app override the href for a type', function () {
        NavigationItem::resolveUrlUsing('static', fn (NavigationItem $item) => "https://app.test/{$item->slug}");

        $menu = Navigation::create(['name' => 'Footer', 'description' => '-', 'created_by' => 1]);

        expect(makeItem($menu, ['name' => 'x', 'slug' => 'careers', 'type' => 'static'])->url)->toBe('https://app.test/careers');

        NavigationItem::resolveUrlUsing('static', null);
    });

    it('never returns a page or gallery for the wrong type', function () {
        $page = makePage('About', 'about');
        $gallery = Gallery::create(['name' => 'Photos', 'slug' => 'photos', 'created_by' => 1]);
        $menu = Navigation::create(['name' => 'Footer', 'description' => '-', 'created_by' => 1]);

        // Same type_id for both: the old conditional relations mixed these up.
        $pageItem = makeItem($menu, ['name' => 'P', 'slug' => 'p', 'type' => 'page', 'type_id' => $page->id]);
        $galleryItem = makeItem($menu, ['name' => 'G', 'slug' => 'g', 'type' => 'gallery', 'type_id' => $gallery->id]);

        $items = NavigationItem::with(['typePage', 'typeGallery'])->whereIn('id', [$pageItem->id, $galleryItem->id])->get()->keyBy('slug');

        expect($items['p']->linkedPage()?->is($page))->toBeTrue()
            ->and($items['p']->linkedGallery())->toBeNull()
            ->and($items['p']->title)->toBe('About')
            ->and($items['g']->linkedGallery()?->is($gallery))->toBeTrue()
            ->and($items['g']->linkedPage())->toBeNull()
            ->and($items['g']->title)->toBe('Photos');
    });
});

describe('content helpers', function () {
    it('fetches pages and galleries by slug', function () {
        ['gallery' => $gallery] = seedMainMenu();

        expect(FilamentCms::getPage('cctv')?->title)->toBe('CCTV Installation')
            ->and(FilamentCms::getPage('missing'))->toBeNull();

        $fetched = FilamentCms::getGallery('project-photos');

        expect($fetched->is($gallery))->toBeTrue()
            ->and($fetched->relationLoaded('images'))->toBeTrue()
            ->and($fetched->images)->toHaveCount(1);
    });

    it('resolves menu slugs, page slugs and gallery slugs', function () {
        ['cctv' => $cctv, 'gallery' => $gallery, 'services' => $services] = seedMainMenu();

        expect(FilamentCms::resolveSlug('services/cctv')?->is($cctv))->toBeTrue()
            ->and(FilamentCms::resolveSlug('/services/photos/')?->is($gallery))->toBeTrue()
            ->and(FilamentCms::resolveSlug('cctv')?->is($cctv))->toBeTrue()
            ->and(FilamentCms::resolveSlug('services')?->is($services))->toBeTrue()
            ->and(FilamentCms::resolveSlug('project-photos')?->is($gallery))->toBeTrue()
            ->and(FilamentCms::resolveSlug('blog'))->toBeNull()
            ->and(FilamentCms::resolveSlug('does-not-exist'))->toBeNull();
    });

    it('builds gallery image urls from the configured disk', function () {
        config()->set('filesystems.disks.cdn', ['driver' => 'local', 'root' => sys_get_temp_dir(), 'url' => 'https://cdn.kbi.test']);
        config()->set('filament.default_filesystem_disk', 'cdn');

        expect((new GalleryImage(['image_name' => 'photos/one.jpg']))->image_url)->toBe('https://cdn.kbi.test/photos/one.jpg');
    });
});

describe('catch-all route', function () {
    it('renders pages, nested menu slugs and galleries', function () {
        seedMainMenu();

        $this->get('/services/cctv')->assertOk()->assertSee('<h1>CCTV Installation</h1>', false);
        $this->get('/cctv')->assertOk()->assertSee('CCTV Installation body');
        $this->get('/services/photos')->assertOk()->assertSee('<h1>Project Photos</h1>', false)->assertSee('project-photos/one.jpg');
        $this->get('/nothing-here')->assertNotFound();
    });

    it('never shadows routes defined by the app', function () {
        seedMainMenu();

        Route::get('home', fn () => 'app home')->middleware('web');
        Route::getRoutes()->refreshNameLookups();

        $this->get('/home')->assertOk()->assertSee('app home');
    });

    it('uses a controller so the routes can be cached', function () {
        $route = Route::getRoutes()->getByName('filament-cms.page');

        expect($route->getActionName())->toBe(CmsPageController::class)
            ->and($route->isFallback)->toBeTrue();
    });
});

describe('audit columns', function () {
    it('stamps updated_by when a page is edited and keeps created_by', function () {
        $page = makePage('About', 'about');

        $this->actingAs((new User)->forceFill(['id' => 7]));

        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->fillForm(['title' => 'About KBI'])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($page->refresh())
            ->title->toBe('About KBI')
            ->created_by->toBe(1)
            ->updated_by->toBe(7);
    });

    it('stamps updated_by when a navigation is edited', function () {
        $menu = Navigation::create(['name' => 'Main', 'description' => '-', 'created_by' => 1]);

        $this->actingAs((new User)->forceFill(['id' => 9]));

        Livewire::test(EditNavigation::class, ['record' => $menu->getRouteKey()])
            ->fillForm(['description' => 'Header menu'])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($menu->refresh())
            ->created_by->toBe(1)
            ->updated_by->toBe(9);
    });
});
