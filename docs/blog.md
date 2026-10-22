# Blog

Write posts in the panel under **Blog Posts** and show them on the frontend with the package's
blog routes or the `FilamentCms` facade.

## Writing posts

Each post has:

| Field | Notes |
| --- | --- |
| Title, slug | The slug follows the title while you create the post, then stays put so the URL doesn't change. Unique, letters, numbers, `-` and `_`. |
| Excerpt | Optional summary for the blog list and the meta description. Empty uses the first 200 characters of the body. |
| Body | Rich text; images attached in the editor go to the `blog/` folder of the default disk. |
| Status | **Draft** or **Published**. |
| Publish date | Empty publishes now. A future date **schedules** the post: it appears by itself once the date passes. |
| Cover image | Shown on the list and the post, and used as the share image. |
| SEO | Same fields as pages ([SEO](seo.md)). |

The list shows each post as **Published**, **Scheduled** or **Draft**, can be filtered by status,
and has a **View** action that opens the post on the site. Drafts and scheduled posts open as a
**preview** for users who can edit them; everyone else gets a 404.

Who can do what is set by the `*_post` permissions ([roles](roles.md#permissions)). Both default
roles can manage posts.

## Frontend routes

Turn them on in `.env`:

```dotenv
FILAMENT_CMS_BLOG_ROUTES=true
```

| URL | Route name | View receives |
| --- | --- | --- |
| `/blog` | `filament-cms.blog.index` | `$posts`: paginator of published posts, newest first |
| `/blog/{slug}` | `filament-cms.blog.show` | `$post` |

They are ordinary routes, so they win over the catch-all page route and work with
`route:cache`. Options in `config/filament-cms.php`:

```php
'blog' => [
    'routes' => env('FILAMENT_CMS_BLOG_ROUTES', false),
    'prefix' => 'blog',              // e.g. "news" for /news and /news/{slug}
    'middleware' => ['web'],
    'per_page' => 9,
    'views' => [
        'index' => 'filament-cms::blog.index',
        'show' => 'filament-cms::blog.show',
    ],
],
```

The default views are bare HTML. Point `views` at your own to use your layout.

## Blade

A blog list in your layout (`resources/views/blog/index.blade.php`, set as `views.index`):

```blade
@extends('layouts.app')

@section('content')
    @foreach ($posts as $post)
        <article>
            @if ($post->cover_image_url)
                <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}">
            @endif
            <h2><a href="{{ $post->url }}">{{ $post->title }}</a></h2>
            <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('M j, Y') }}</time>
            · {{ $post->reading_time }} min read
            <p>{{ $post->summary }}</p>
        </article>
    @endforeach

    {{ $posts->links() }}
@endsection
```

One post (`views.show`). Put the SEO component in the layout's `<head>` with `:post="$post"`:

```blade
<x-filament-cms::seo :post="$post ?? null" />
```

```blade
<article>
    @unless ($post->is_published)
        <p>Preview: not published yet.</p>
    @endunless
    <h1>{{ $post->title }}</h1>
    {!! str($post->body)->sanitizeHtml() !!}
</article>
```

## API

```php
FilamentCms::getPosts();          // paginator of published posts (per_page from config)
FilamentCms::getPosts(6);         // 6 per page
FilamentCms::latestPosts(3);      // Collection, e.g. "Latest from the blog" on the home page
FilamentCms::getPost('my-post');  // ?Post, published only
FilamentCms::blogUrl();           // URL of the list
FilamentCms::postUrl($post);      // URL of a post (also $post->url)
```

`Post` attributes for views:

| Attribute | Value |
| --- | --- |
| `url` | Frontend URL (`FilamentCms::postUrl()`) |
| `cover_image_url` | Absolute URL of the cover image on the default disk, or `null` |
| `summary` | The excerpt, or the start of the body as plain text |
| `reading_time` | Minutes at 200 words a minute, at least 1 |
| `is_published` | `true` once published and the publish date has passed |
| `createdBy` | The author (user model) |

`Post::published()` is the query scope behind all of these.

To link to the blog from a menu, add a **Custom URL** item with `/blog`.

## SEO

`<x-filament-cms::seo :post="$post" />` works like it does for pages, plus:

- the description falls back to the **excerpt**, and the share image to the **cover image**,
  before the SEO Defaults
- the canonical URL is the post URL
- `article:published_time` is added
- a post without its own JSON-LD gets a schema.org `BlogPosting` (headline, description,
  image, dates, author, publisher)
