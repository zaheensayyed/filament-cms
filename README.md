# Filament CMS

Pages, multi-level menus, galleries, SEO tags, site settings and a contact form for Laravel
sites, managed in a [Filament](https://filamentphp.com) panel and rendered with plain Blade.

| PHP | Laravel | Filament |
| --- | --- | --- |
| 8.1+ | 10.x | 3.x |

## Features

- **Pages** with a rich-text editor and per-page SEO fields
- **Menus** with 2 levels, linking to pages, galleries, custom URLs or your own routes; cached
- **Catch-all route** that serves pages and galleries by slug (works with `route:cache`)
- **Galleries** with image uploads on any filesystem disk
- **SEO component**: title, description, canonical, robots, Open Graph, Twitter, JSON-LD
- **Settings** page with Company Info, SEO Defaults and Contact Form tabs, extensible
- **Contact form** with SMTP delivery through your mailer, spam protection and a submission log

## Installation

```bash
composer require zaheensayyed/filament-cms
php artisan filament-cms:install
php artisan storage:link
```

Register the plugin in your panel provider:

```php
use zaheensayyed\FilamentCms\FilamentCmsPlugin;

$panel->plugins([
    FilamentCmsPlugin::make(),
]);
```

Serve CMS pages by slug on the frontend by adding `FILAMENT_CMS_ROUTES=true` to `.env`.

## Usage

```blade
<head>
    <x-filament-cms::seo :page="$page ?? null" />
</head>
<body>
    <nav>
        @foreach (FilamentCms::getMenu('main-menu') as $item)
            <a href="{{ $item->url }}">{{ $item->title }}</a>
        @endforeach
    </nav>

    <x-filament-cms::contact-form />

    <footer>{{ FilamentCms::setting('company.email') }}</footer>
</body>
```

```php
FilamentCms::getMenu('main-menu');        // cached menu items with url, title, childItems
FilamentCms::getPage('about-us');         // ?Page
FilamentCms::getGallery('event-2017');    // ?Gallery with images
FilamentCms::resolveSlug('about/team');   // Page, Gallery or null
FilamentCms::setting('company.email');    // settings value, cached
```

## Documentation

Full guide in [docs/](docs/README.md): [installation](docs/installation.md) ·
[concepts](docs/concepts.md) · [menus](docs/menus.md) ·
[routing & pages](docs/routing-and-pages.md) · [galleries](docs/galleries.md) ·
[SEO](docs/seo.md) · [contact form](docs/contact-form.md) · [settings](docs/settings.md) ·
[customization](docs/customization.md)

**Upgrading?** Read [UPGRADE.md](UPGRADE.md). Release notes: [CHANGELOG.md](CHANGELOG.md).

## License

MIT, see [LICENSE.md](LICENSE.md).
