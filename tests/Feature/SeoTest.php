<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use zaheensayyed\FilamentCms\Facades\FilamentCms;
use zaheensayyed\FilamentCms\Models\Page;
use zaheensayyed\FilamentCms\Pages\Settings;
use zaheensayyed\FilamentCms\Resources\PageResource\Pages\CreatePage;

beforeEach(function () {
    FilamentCms::saveSettings(['seo' => [
        'site_name' => 'KBI',
        'title_pattern' => '{title} | {site_name}',
        'default_description' => 'Default <b>description</b>',
        'default_og_image' => 'https://cdn.kbi.test/default.jpg',
        'default_robots' => 'index,follow',
    ]]);
});

it('renders a complete default-driven tag block for a page without overrides', function () {
    $page = Page::create(['title' => 'About Us', 'slug' => 'about-us', 'created_by' => 1]);

    $this->blade('<x-filament-cms::seo :page="$page" />', ['page' => $page])
        ->assertSee('<title>About Us | KBI</title>', false)
        ->assertSee('<meta name="description" content="Default &lt;b&gt;description&lt;/b&gt;">', false)
        ->assertSee('<link rel="canonical" href="' . url('about-us') . '">', false)
        ->assertSee('<meta name="robots" content="index,follow">', false)
        ->assertSee('<meta property="og:type" content="article">', false)
        ->assertSee('<meta property="og:site_name" content="KBI">', false)
        ->assertSee('<meta property="og:title" content="About Us">', false)
        ->assertSee('<meta property="og:url" content="' . url('about-us') . '">', false)
        ->assertSee('<meta property="og:image" content="https://cdn.kbi.test/default.jpg">', false)
        ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
        ->assertSee('<meta name="twitter:image" content="https://cdn.kbi.test/default.jpg">', false)
        ->assertDontSee('application/ld+json', false);
});

it('renders page overrides and escapes them', function () {
    $page = Page::create([
        'title' => 'About Us',
        'slug' => 'about-us',
        'created_by' => 1,
        'meta_title' => 'Tom & Jerry "quoted"',
        'meta_description' => 'Page description',
        'canonical_url' => 'https://kbi.test/about',
        'robots' => 'noindex,nofollow',
        'og_title' => 'Share title',
        'og_description' => 'Share description',
        'og_image' => 'https://cdn.kbi.test/about.jpg',
        'structured_data' => '{"@context":"https://schema.org","@type":"Organization","name":"KBI"}',
    ]);

    $this->blade('<x-filament-cms::seo :page="$page" />', ['page' => $page])
        ->assertSee('<title>Tom &amp; Jerry &quot;quoted&quot;</title>', false)
        ->assertSee('<meta name="description" content="Page description">', false)
        ->assertSee('<link rel="canonical" href="https://kbi.test/about">', false)
        ->assertSee('<meta name="robots" content="noindex,nofollow">', false)
        ->assertSee('<meta property="og:title" content="Share title">', false)
        ->assertSee('<meta property="og:description" content="Share description">', false)
        ->assertSee('<meta name="twitter:description" content="Share description">', false)
        ->assertSee('<meta property="og:image" content="https://cdn.kbi.test/about.jpg">', false)
        ->assertSee('<script type="application/ld+json">{"@context":"https://schema.org","@type":"Organization","name":"KBI"}</script>', false);
});

it('renders defaults only when there is no page', function () {
    $this->blade('<x-filament-cms::seo :page="null" />')
        ->assertSee('<title>KBI</title>', false)
        ->assertSee('<meta property="og:type" content="website">', false)
        ->assertSee('<meta name="robots" content="index,follow">', false);
});

it('saves SEO fields from the page form', function () {

    Livewire::test(CreatePage::class)
        ->fillForm([
            'title' => 'Contact',
            'slug' => 'contact',
            'meta_title' => 'Contact KBI',
            'robots' => 'noindex,follow',
            'structured_data' => '{"@type":"ContactPage"}',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Page::where('slug', 'contact')->first())
        ->meta_title->toBe('Contact KBI')
        ->robots->toBe('noindex,follow')
        ->meta_description->toBeNull();
});

it('validates canonical url and structured data on the page form', function () {

    Livewire::test(CreatePage::class)
        ->fillForm([
            'title' => 'Contact',
            'slug' => 'contact',
            'canonical_url' => 'not a url',
            'structured_data' => '{not json',
        ])
        ->call('create')
        ->assertHasFormErrors(['canonical_url' => 'url', 'structured_data' => 'json']);
});

it('saves the SEO Defaults tab including the default image', function () {
    Storage::fake('public');

    Livewire::test(Settings::class)
        ->fillForm([
            'company' => ['contact_no' => '123', 'email' => 'hello@kbi.test'],
            'seo' => [
                'site_name' => 'New Name',
                'default_og_image' => UploadedFile::fake()->image('og.jpg', 1200, 630),
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(FilamentCms::setting('seo.site_name'))->toBe('New Name')
        ->and(FilamentCms::setting('seo.default_og_image'))->toStartWith('seo/');

    Storage::disk('public')->assertExists(FilamentCms::setting('seo.default_og_image'));
});
