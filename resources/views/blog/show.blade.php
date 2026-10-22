{{-- Default view for one filament-cms blog post. Publish the views or set filament-cms.blog.views.show to use your own layout. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-filament-cms::seo :post="$post" />
</head>
<body>
    <main>
        <article>
            @unless ($post->is_published)
                <p><strong>Preview:</strong> this post is not published yet.</p>
            @endunless

            <h1>{{ $post->title }}</h1>

            @if ($post->published_at)
                <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('M j, Y') }}</time>
            @endif

            @if ($post->cover_image_url)
                <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}">
            @endif

            {!! $post->body !!}
        </article>

        <p><a href="{{ \zaheensayyed\FilamentCms\Facades\FilamentCms::blogUrl() }}">← All posts</a></p>
    </main>
</body>
</html>
