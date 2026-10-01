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
        <h1>{{ $page->title }}</h1>

        {!! $page->body !!}
    </main>
</body>
</html>
