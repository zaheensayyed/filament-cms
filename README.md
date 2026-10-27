# Filament CMS

Pages, multi-level menus, galleries, SEO tags, site settings and a contact form for Laravel
sites, managed in a [Filament](https://filamentphp.com) panel and rendered with plain Blade.

> **Note:** this README describes the upcoming **v1.1.0** (see [Unreleased](CHANGELOG.md#unreleased)).
> For the current release, read the [v1.0.6 README](https://github.com/zaheensayyed/filament-cms/blob/v1.0.6/README.md).

| PHP | Laravel | Filament |
| --- | --- | --- |
| 8.1+ | 10.x | 3.x |

## Features

- **Pages** with a rich-text editor, a description, a Heroicon and per-page SEO fields
- **Blog**: posts with drafts, scheduled publishing, cover images and SEO; `/blog` routes and `FilamentCms::getPosts()` ([docs](docs/blog.md))
- **Menus**: a Main Menu and a Footer Menu with 2 levels, linking to pages, galleries or custom URLs, plus static (non-clickable) labels; Heroicon icons on child items; cached
- **Catch-all route** that serves pages and galleries by slug (works with `route:cache`)
- **Galleries** with image uploads on any filesystem disk
- **Clients** as cards (logo, title, description) on one `/clients` page; featured clients for the home page ([docs](docs/clients.md))
- **SEO component**: title, description, canonical, robots, Open Graph, Twitter, JSON-LD
- **Settings** page with Company Info, SEO Defaults and Contact Form tabs, extensible; read with `FilamentCms::setting()` ([key reference](docs/settings.md#key-reference))
- **Users & roles** with Filament Shield: `admin` and `content_manager` roles out of the box ([docs](docs/roles.md))
- **Contact form** (`<x-filament-cms::contact-form />`, route `filament-cms.contact.submit`) with SMTP delivery through your mailer, spam protection and a submission log in the panel

## Installation

```bash
composer require zaheensayyed/filament-cms
```

Add `use Spatie\Permission\Traits\HasRoles;` to your `User` model, then:

```bash
php artisan filament-cms:install
php artisan filament-cms:roles --admin=you@example.com
php artisan storage:link
```

Register the plugin in your panel provider:

```php
use zaheensayyed\FilamentCms\FilamentCmsPlugin;

$panel->plugins([
    FilamentCmsPlugin::make(),
]);
```

Serve CMS pages by slug on the frontend by adding `FILAMENT_CMS_ROUTES=true` to `.env`, and
the blog at `/blog` with `FILAMENT_CMS_BLOG_ROUTES=true` and clients at `/clients` with
`FILAMENT_CMS_CLIENT_ROUTES=true`.

**Upgrading from 1.0.x?** Menus, routing and relations changed: follow [UPGRADE.md](UPGRADE.md).

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
FilamentCms::getPosts();                  // published blog posts, paginated
FilamentCms::featuredClients();           // clients marked Featured, in the panel's order
FilamentCms::setting('company.email');    // settings value, cached
```

## Documentation

Full guide in [docs/](docs/README.md): [installation](docs/installation.md) ·
[concepts](docs/concepts.md) · [menus](docs/menus.md) ·
[routing & pages](docs/routing-and-pages.md) · [blog](docs/blog.md) · [galleries](docs/galleries.md) · [clients](docs/clients.md) ·
[SEO](docs/seo.md) · [contact form](docs/contact-form.md) · [settings](docs/settings.md) ·
[roles](docs/roles.md) · [customization](docs/customization.md)

**Upgrading?** Read [UPGRADE.md](UPGRADE.md). Release notes: [CHANGELOG.md](CHANGELOG.md).

## License

MIT, see [LICENSE.md](LICENSE.md).
