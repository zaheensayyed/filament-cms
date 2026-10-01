# Routing & rendering pages

## Option A: the catch-all route (recommended)

Enable it in `.env`:

```dotenv
FILAMENT_CMS_ROUTES=true
```

or in `config/filament-cms.php` (publish it with `php artisan vendor:publish --tag=filament-cms-config`):

```php
'routes' => [
    'enabled' => true,
    'prefix' => '',              // e.g. 'pages' to serve CMS content at /pages/{slug}
    'middleware' => ['web'],
    'views' => [
        'page' => 'cms.page',        // your view, receives $page
        'gallery' => 'cms.gallery',  // your view, receives $gallery (images loaded)
    ],
],
```

The route (`GET /{slug}`, name `filament-cms.page`) resolves a slug in this order:

1. a **menu item** slug of type page or gallery (`about`, `services/cctv`) → its page or gallery
2. a **page** slug (`about-us`)
3. a **gallery** slug (`event-2017`)
4. otherwise **404**

It is registered as a **fallback** route, so every route your app defines wins, even ones
registered later. It uses a controller, so `php artisan route:cache` works and new pages
are reachable immediately, without clearing the route cache.

### The page view

`resources/views/cms/page.blade.php`:

```blade
@extends('layouts.app')

@section('head')
    <x-filament-cms::seo :page="$page" />
@endsection

@section('content')
    <article class="page">
        <h1>{{ $page->title }}</h1>

        <div class="page-body">
            {!! str($page->body)->sanitizeHtml() !!}
        </div>
    </article>
@endsection
```

The body is HTML written with the panel's rich editor, so it must be printed unescaped
with `{!! !!}`. `sanitizeHtml()` (a Filament string helper) strips scripts and event-handler
attributes first, so a compromised editor account can't inject JavaScript into your site.

The layout needs a `head` section inside `<head>`:

```blade
{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @hasSection('head')
        @yield('head')
    @else
        <x-filament-cms::seo />
    @endif
</head>
<body>
    @include('partials.navbar')

    <main>
        @yield('content')
    </main>
</body>
</html>
```

Without custom views the package renders minimal built-in ones (`filament-cms::page` and
`filament-cms::gallery`), handy for a first test.

## Option B: your own route with `resolveSlug()`

If you need your own URL scheme or extra data in the view, keep the catch-all off and call
`FilamentCms::resolveSlug()` from your own controller:

```php
// routes/web.php
use App\Http\Controllers\CmsController;

Route::get('/{slug}', CmsController::class)->where('slug', '.*')->fallback();
```

```php
// app/Http/Controllers/CmsController.php
namespace App\Http\Controllers;

use zaheensayyed\FilamentCms\Facades\FilamentCms;
use zaheensayyed\FilamentCms\Models\Gallery;

class CmsController extends Controller
{
    public function __invoke(string $slug)
    {
        $content = FilamentCms::resolveSlug($slug) ?? abort(404);

        return $content instanceof Gallery
            ? view('cms.gallery', ['gallery' => $content])
            : view('cms.page', ['page' => $content]);
    }
}
```

To fetch a specific page anywhere (a home page controller, a view composer):

```php
$page = FilamentCms::getPage('about-us'); // Page or null
```

> **Don't do this.** Registering one route per menu item in `routes/web.php` runs a database
> query on every request, and new pages 404 as soon as routes are cached:
>
> ```php
> // ❌ Old pattern: remove it
> foreach (NavigationItem::get() as $item) {
>     Route::get($item->slug, fn () => view('pages.page', ['item' => $item]));
> }
> ```
>
> Use the catch-all route or a single `resolveSlug()` route instead.

> **Performance notes:** resolving a slug costs **2 queries** for a menu item or page and
> **3–4** for a gallery (which also loads its images). It is not cached, since each URL is a
> different page. Your own routes never pay this cost; only URLs that no app route matched do.
