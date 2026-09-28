# Galleries

```php
$gallery = FilamentCms::getGallery('event-2017'); // Gallery or null, images already loaded
```

A gallery linked from a menu, or opened by its slug, is also served by the
[catch-all route](routing-and-pages.md) with your `gallery` view.

`resources/views/cms/gallery.blade.php`:

```blade
@extends('layouts.app')

@section('content')
    <h1>{{ $gallery->name }}</h1>

    @if ($gallery->description)
        <p>{{ $gallery->description }}</p>
    @endif

    <div class="gallery-grid">
        @forelse ($gallery->images as $image)
            <a href="{{ $image->image_url }}">
                <img src="{{ $image->image_url }}"
                     alt="{{ $image->image_caption ?? $gallery->name }}"
                     loading="lazy">
            </a>
        @empty
            <p>No photos yet.</p>
        @endforelse
    </div>
@endsection
```

Listing several galleries? Load their images in the same query:

```php
use zaheensayyed\FilamentCms\Models\Gallery;

$galleries = Gallery::with('images')->latest()->take(12)->get();
```

## Where images are stored

Images are uploaded to **Filament's default disk**, `public` unless you set
`FILAMENT_FILESYSTEM_DISK` in `.env`. `$image->image_url` asks that disk for the URL, so it
works for local storage and for S3 alike.

- `public` disk: run `php artisan storage:link` once, and make sure `APP_URL` is your
  site's URL (the disk builds image URLs from it).
- S3 or another cloud disk: set `FILAMENT_FILESYSTEM_DISK=s3` and configure the disk in
  `config/filesystems.php`. Nothing changes in your views.

> **Performance notes:** `getGallery()` costs **2 queries** (gallery + all its images) and is
> not cached. `image_url` doesn't query. Always use `with('images')` when looping over
> several galleries, otherwise every gallery fires its own images query.
