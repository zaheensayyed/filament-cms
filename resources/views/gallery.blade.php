{{-- Default view for the filament-cms catch-all route. Publish the views or set filament-cms.routes.views.gallery to use your own layout. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-filament-cms::seo />
</head>
<body>
    <main>
        <h1>{{ $gallery->name }}</h1>

        @if ($gallery->description)
            <p>{{ $gallery->description }}</p>
        @endif

        @foreach ($gallery->images as $image)
            <figure>
                <img src="{{ $image->image_url }}" alt="{{ $image->image_caption ?? $gallery->name }}" loading="lazy">
                @if ($image->image_caption)
                    <figcaption>{{ $image->image_caption }}</figcaption>
                @endif
            </figure>
        @endforeach
    </main>
</body>
</html>
