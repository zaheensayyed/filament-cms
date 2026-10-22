{{-- Default view for the filament-cms clients page: every client as a card. Publish the views or set filament-cms.clients.views.index to use your own layout. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-filament-cms::seo :page="new \zaheensayyed\FilamentCms\Models\Page(['title' => 'Clients', 'slug' => config('filament-cms.clients.prefix', 'clients')])" />
</head>
<body>
    <main>
        <h1>Clients</h1>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(18rem, 1fr)); gap: 1.5rem;">
            @forelse ($clients as $client)
                <article>
                    @foreach ($client->image_urls as $url)
                        <img src="{{ $url }}" alt="{{ $client->name }}" loading="lazy" style="width: 100%; aspect-ratio: 3 / 2; object-fit: contain;">
                    @endforeach
                    <h2>{{ $client->name }}</h2>
                    @if ($client->description)
                        <p>{{ $client->description }}</p>
                    @endif
                </article>
            @empty
                <p>No clients yet.</p>
            @endforelse
        </div>
    </main>
</body>
</html>
