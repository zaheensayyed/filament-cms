{{-- Default view for the filament-cms blog list. Publish the views or set filament-cms.blog.views.index to use your own layout. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-filament-cms::seo :page="new \zaheensayyed\FilamentCms\Models\Page(['title' => 'Blog', 'slug' => config('filament-cms.blog.prefix', 'blog')])" />
</head>
<body>
    <main>
        <h1>Blog</h1>

        @forelse ($posts as $post)
            <article>
                @if ($post->cover_image_url)
                    <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" loading="lazy">
                @endif
                <h2><a href="{{ $post->url }}">{{ $post->title }}</a></h2>
                <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('M j, Y') }}</time>
                <p>{{ $post->summary }}</p>
            </article>
        @empty
            <p>No posts yet.</p>
        @endforelse

        {{ $posts->links() }}
    </main>
</body>
</html>
