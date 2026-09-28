# Upgrade Guide

## Frontend API refactor (CMS-3)

Run the new migration after updating. It adds a nullable, unique `key` column to
`navigations` and fills it from each menu's name (`"Main Menu"` → `main-menu`):

```bash
php artisan migrate
```

### Breaking or behaviour changes

| Change | What to do |
| --- | --- |
| `FilamentCms::getMenu()` now requires a `string` and results are **cached forever** (flushed automatically when a navigation, menu item, page or gallery is saved or deleted). | Nothing, unless you change menu tables outside Eloquent (raw queries, imports). Then call `FilamentCms::forgetMenuCache()`. |
| Menu items are now ordered by `id` explicitly. | Previously the order was whatever the database returned, which was `id` in practice. |
| `NavigationItem::page()` / `gallery()` no longer switch their foreign key on `type`. They are deprecated aliases of `typePage()` / `typeGallery()`, which always use `type_id`. | Use `$item->linkedPage()` / `$item->linkedGallery()` (return `null` for other types) or `$item->title` / `$item->url`. Don't read `$item->page` on a gallery item: it now looks up a page with that id instead of returning `null`. |
| `GalleryImage::$image_url` now uses `Storage::disk(config('filament.default_filesystem_disk'))->url()`. On the `public` disk it is now an absolute URL (`http://app.test/storage/…`) instead of `/storage/…`. | Nothing for `<img src>`. If you prefix it yourself (`asset($image->image_url)`), drop the prefix. |
| `CommonResourceTrait` no longer overrides `mutateFormDataBeforeFill()`; it now uses `mutateFormDataBeforeSave()` to stamp `updated_by`. | Only matters if you extended the package's Create/Edit pages and relied on the old method. |

### Recommended: switch the consumer app to the new API

**1. Replace the per-item route loop** in `routes/web.php`, e.g.:

```php
// Delete this: it queries the database on every request and breaks route:cache.
foreach (NavigationItem::get() as $item) {
    Route::get($item->slug, ...);
}
```

and enable the package's catch-all route instead (publish the config with
`php artisan vendor:publish --tag=filament-cms-config`):

```php
// config/filament-cms.php
'routes' => [
    'enabled' => true,          // or FILAMENT_CMS_ROUTES=true in .env
    'prefix' => '',             // e.g. 'pages' → /pages/{slug}
    'middleware' => ['web'],
    'views' => [
        'page' => 'cms.page',       // receives $page
        'gallery' => 'cms.gallery', // receives $gallery with images loaded
    ],
],
```

It is a controller-based fallback route, so the app's own routes always win and
`php artisan route:cache` works. For custom routing, use
`FilamentCms::resolveSlug($slug)`, which returns the `Page`, `Gallery` or `null`.

**2. Look menus up by key** (set in the admin under Navigations → key):

```php
FilamentCms::getMenu('main-menu'); // the old display-name lookup still works
```

**3. Use `title` and `url` in the menu Blade** instead of building hrefs from the slug.
`url` is correct for every item type (custom URLs, pages, galleries, static):

```blade
@foreach (FilamentCms::getMenu('main-menu') as $item)
    <a href="{{ $item->url }}">{{ $item->title }}</a>

    @foreach ($item->childItems as $child)
        <a href="{{ $child->url }}">{{ $child->title }}</a>
    @endforeach
@endforeach
```

Static items link to `url($slug)` by default. Override any type with
`NavigationItem::resolveUrlUsing('static', fn ($item) => route($item->slug))`.

**4. Stop querying package models directly** (e.g. in a `HomeController`):

```php
FilamentCms::getPage('about-us');       // ?Page
FilamentCms::getGallery('our-work');    // ?Gallery, images eager loaded
```
