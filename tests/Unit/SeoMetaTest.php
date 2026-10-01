<?php

use Illuminate\Support\Facades\Storage;
use zaheensayyed\FilamentCms\Facades\FilamentCms;
use zaheensayyed\FilamentCms\Models\Page;
use zaheensayyed\FilamentCms\Seo\SeoMeta;

function seoPage(array $attributes = []): Page
{
    return Page::create([
        'title' => 'About Us',
        'slug' => 'about-us',
        'created_by' => 1,
        ...$attributes,
    ]);
}

function seoDefaults(array $values = []): void
{
    FilamentCms::saveSettings(['seo' => [
        'site_name' => 'KBI',
        'title_pattern' => '{title} — {site_name}',
        'default_description' => 'Default description',
        'default_og_image' => 'seo/default.jpg',
        'default_robots' => 'noindex,follow',
        ...$values,
    ]]);
}

afterEach(fn () => SeoMeta::resolvePageUrlUsing(null));

describe('title pattern', function () {
    it('replaces both placeholders', function () {
        expect(SeoMeta::applyTitlePattern('{title} | {site_name}', 'About', 'KBI'))->toBe('About | KBI')
            ->and(SeoMeta::applyTitlePattern('{site_name}: {title}', 'About', 'KBI'))->toBe('KBI: About');
    });

    it('returns the bare title when there is no site name', function () {
        expect(SeoMeta::applyTitlePattern('{title} | {site_name}', 'About', null))->toBe('About')
            ->and(SeoMeta::applyTitlePattern('{title} | {site_name}', 'About', ''))->toBe('About');
    });
});

describe('page values win', function () {
    it('uses every field set on the page', function () {
        seoDefaults();

        $seo = new SeoMeta(seoPage([
            'meta_title' => 'Custom title',
            'meta_description' => 'Custom description',
            'canonical_url' => 'https://kbi.test/canonical',
            'robots' => 'index,nofollow',
            'og_title' => 'OG title',
            'og_description' => 'OG description',
            'og_image' => 'https://cdn.kbi.test/og.jpg',
        ]));

        expect($seo->title())->toBe('Custom title')
            ->and($seo->description())->toBe('Custom description')
            ->and($seo->canonical())->toBe('https://kbi.test/canonical')
            ->and($seo->robots())->toBe('index,nofollow')
            ->and($seo->ogTitle())->toBe('OG title')
            ->and($seo->ogDescription())->toBe('OG description')
            ->and($seo->ogImage())->toBe('https://cdn.kbi.test/og.jpg')
            ->and($seo->ogType())->toBe('article');
    });
});

describe('fallbacks to settings', function () {
    it('falls back to the SEO Defaults tab', function () {
        seoDefaults();

        $seo = new SeoMeta(seoPage());

        expect($seo->title())->toBe('About Us — KBI')
            ->and($seo->description())->toBe('Default description')
            ->and($seo->canonical())->toBe(url('about-us'))
            ->and($seo->robots())->toBe('noindex,follow')
            ->and($seo->ogTitle())->toBe('About Us')
            ->and($seo->ogDescription())->toBe('Default description')
            ->and($seo->ogImage())->toBe(url(Storage::disk('public')->url('seo/default.jpg')))
            ->and($seo->siteName())->toBe('KBI');
    });

    it('chains og values through the meta values', function () {
        seoDefaults();

        $seo = new SeoMeta(seoPage(['meta_title' => 'Meta', 'meta_description' => 'Meta description']));

        expect($seo->ogTitle())->toBe('Meta')
            ->and($seo->ogDescription())->toBe('Meta description');
    });

    it('treats empty page fields as missing', function () {
        seoDefaults();

        $seo = new SeoMeta(seoPage(['meta_title' => '', 'robots' => '']));

        expect($seo->title())->toBe('About Us — KBI')
            ->and($seo->robots())->toBe('noindex,follow');
    });

    it('uses a custom page url resolver for the canonical', function () {
        SeoMeta::resolvePageUrlUsing(fn (Page $page) => "https://kbi.test/pages/{$page->slug}");

        expect((new SeoMeta(seoPage()))->canonical())->toBe('https://kbi.test/pages/about-us');
    });
});

describe('hardcoded fallbacks', function () {
    it('works with no settings saved at all', function () {
        config()->set('app.name', 'KBI App');

        $seo = new SeoMeta(seoPage());

        expect($seo->title())->toBe('About Us | KBI App')
            ->and($seo->description())->toBeNull()
            ->and($seo->robots())->toBe('index,follow')
            ->and($seo->ogImage())->toBeNull()
            ->and($seo->siteName())->toBe('KBI App')
            ->and($seo->twitterCard())->toBe('summary_large_image');
    });

    it('emits defaults only for non-CMS routes', function () {
        seoDefaults();

        $seo = new SeoMeta(null);

        expect($seo->title())->toBe('KBI')
            ->and($seo->ogTitle())->toBe('KBI')
            ->and($seo->description())->toBe('Default description')
            ->and($seo->canonical())->toBe(url()->current())
            ->and($seo->ogType())->toBe('website')
            ->and($seo->structuredData())->toBeNull();
    });
});

describe('structured data', function () {
    it('re-encodes valid JSON so it cannot close the script tag', function () {
        $seo = new SeoMeta(seoPage([
            'structured_data' => '{"@context":"https://schema.org","name":"</script><script>alert(1)</script>"}',
        ]));

        expect($seo->structuredData())
            ->toContain('"@context":"https://schema.org"')
            ->not->toContain('</script>');
    });

    it('ignores invalid JSON', function () {
        expect((new SeoMeta(seoPage(['structured_data' => '{not json'])))->structuredData())->toBeNull();
    });
});
