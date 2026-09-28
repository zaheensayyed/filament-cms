# Installation & panel setup

## Requirements

| | Supported |
| --- | --- |
| PHP | 8.1, 8.2, 8.3 |
| Laravel | 10.x |
| Filament | 3.x (tested with 3.3) |
| Database | MySQL / MariaDB, SQLite |

You need a working Filament panel. If the app doesn't have one yet:

```bash
composer require filament/filament:"^3.3" -W
php artisan filament:install --panels
php artisan make:filament-user
```

## 1. Install the package

```bash
composer require zaheensayyed/filament-cms
php artisan filament-cms:install
```

The install command publishes `config/filament-cms.php` and offers to run the migrations
(pages, navigations, galleries, settings, contact submissions). Answer **yes**, or run
`php artisan migrate` yourself later.

Galleries and share images are stored on Filament's default disk (`public`), so link it once:

```bash
php artisan storage:link
```

## 2. Register the plugin in your panel

`app/Providers/Filament/AdminPanelProvider.php`:

```php
use zaheensayyed\FilamentCms\FilamentCmsPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...your existing panel setup
        ->plugins([
            FilamentCmsPlugin::make(),
        ]);
}
```

The panel now has **Pages**, **Navigations**, **Galleries**, **Contact Submissions** and a
**Settings** page.

### Optional: CMS theme

`FilamentCmsTheme` gives the panel the DM Sans font and an amber colour palette:

```php
use zaheensayyed\FilamentCms\FilamentCmsPlugin;
use zaheensayyed\FilamentCms\FilamentCmsTheme;

->plugins([
    FilamentCmsPlugin::make(),
    FilamentCmsTheme::make(),
])
```

## 3. Serve CMS pages on the frontend

Turn on the package's catch-all route so pages and galleries are reachable by their slug
(details in [Routing & rendering pages](routing-and-pages.md)):

```dotenv
# .env
FILAMENT_CMS_ROUTES=true
```

## Next steps

- [Concepts](concepts.md): what pages, navigations, galleries and settings are
- [Rendering menus in Blade](menus.md)
- [Routing & rendering pages](routing-and-pages.md)
