{{-- Default view for the filament-cms catch-all route. Publish the views or set filament-cms.routes.views.page to use your own layout. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-filament-cms::seo :page="$page" />
</head>
<body>
    <main>
        @if (\zaheensayyed\FilamentCms\Support\Heroicons::exists($page->icon))
            @svg($page->icon, '', ['style' => 'width: 2rem; height: 2rem;', 'aria-hidden' => 'true'])
        @endif

        <h1>{{ $page->title }}</h1>

        @if ($page->description)
            <p>{{ $page->description }}</p>
        @endif

        @if ($page->cover_image_url)
            <img src="{{ $page->cover_image_url }}" alt="{{ $page->title }}" style="max-width: 100%;">
        @endif

        {!! $page->body !!}
    </main>
</body>
</html>
