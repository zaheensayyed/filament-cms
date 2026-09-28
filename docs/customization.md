# Customization & upgrading

## Publishable files

| Tag | What you get | Where |
| --- | --- | --- |
| `filament-cms-config` | `filament-cms.php` (catch-all route, contact form endpoint) | `config/` |
| `filament-cms-views` | SEO and contact form components, email templates, default page/gallery views | `resources/views/vendor/filament-cms/` |
| `filament-cms-translations` | Language file | `lang/vendor/filament-cms/` |
| `filament-shield-config`, `permission-config` | Shield and spatie/laravel-permission config (published by the installer) | `config/` |

```bash
php artisan vendor:publish --tag=filament-cms-views
```

Only keep the published files you actually change; the rest keep updating with the package.

## Extension points

| Hook | Use it to |
| --- | --- |
| `FilamentCmsPlugin::make()->settingsGroups([...])` | Add tabs to the Settings page ([example](settings.md#adding-your-own-tab)) |
| `NavigationItem::resolveUrlUsing($type, $callback)` | Change where a menu item type links to ([menus](menus.md#changing-where-a-type-links-to)) |
| `SeoMeta::resolvePageUrlUsing($callback)` | Tell the SEO component where pages live ([SEO](seo.md#page-urls-in-canonicals)) |
| `config('filament-cms.routes.views')` | Render CMS pages and galleries with your own views |

## Upgrading

Breaking changes and step-by-step upgrade notes are in [UPGRADE.md](../UPGRADE.md), and every
release is listed in [CHANGELOG.md](../CHANGELOG.md). After updating the package, always run:

```bash
php artisan migrate
```
